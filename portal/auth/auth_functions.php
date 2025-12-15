<?php
require_once __DIR__ . "/../../shared/includes/simple_session.php";
require_once __DIR__ . "/../../shared/config/config.local.php";

// Log action
function logAction($user_id, $action)
{
          $db = getDB();
          $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, ip_address) VALUES (?, ?, ?)");
          $stmt->execute([$user_id, $action, $_SERVER['REMOTE_ADDR'] ?? null]);
}

// Login
function login($email, $password)
{
          try {
                    $db = getDB();
                    $stmt = $db->prepare("SELECT * FROM users WHERE email=? AND status='active'");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Debug info (remove this after testing)
                    error_log("Login attempt for: " . $email);
                    error_log("User found: " . ($user ? 'Yes' : 'No'));
                    if ($user) {
                              error_log("User ID: " . $user['id']);
                              error_log("User status: " . $user['status']);
                              error_log("Password hash length: " . strlen($user['password_hash']));
                    }

                    if ($user && password_verify($password, $user['password_hash'])) {
                              // Set PHP session variables
                              $_SESSION['user_id'] = $user['id'];
                              $_SESSION['role_id'] = $user['role_id'];
                              $_SESSION['login_time'] = time();
                              $_SESSION['login_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';

                              // Debug: Check session immediately after setting
                              error_log("Session ID: " . session_id());
                              error_log("Session after setting: " . print_r($_SESSION, true));
                              error_log("Session save path: " . session_save_path());
                              error_log("Session cookie params: " . print_r(session_get_cookie_params(), true));

                              logAction($user['id'], "User logged in");
                              error_log("Login successful for user ID: " . $user['id']);
                              return true;
                    }

                    logAction(null, "Failed login for $email");
                    error_log("Login failed for: " . $email . " (password verify failed)");
                    return false;
          } catch (Exception $e) {
                    error_log("Login error: " . $e->getMessage());
                    return false;
          }
} // Logout
function logout()
{
          // Unset all session variables
          $_SESSION = array();

          // Delete the session cookie across all subdomains
          if (ini_get("session.use_cookies")) {
                    $params = session_get_cookie_params();
                    setcookie(
                              session_name(),
                              '',
                              time() - 42000,
                              $params["path"],
                              $params["domain"],
                              $params["secure"],
                              $params["httponly"]
                    );
          }

          // Destroy the session
          session_destroy();
}


// Create invite
function createInvite($email, $role_id)
{
          $db = getDB();
          $code = bin2hex(random_bytes(16));
          $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
          $stmt = $db->prepare("INSERT INTO invites (email, role_id, code, expires_at) VALUES (?, ?, ?, ?)");
          $stmt->execute([$email, $role_id, $code, $expires]);
          return $code; // email sending handled elsewhere
}

// Register via invite
function registerFromInvite($code, $name, $password)
{
          $db = getDB();
          $stmt = $db->prepare("SELECT * FROM invites WHERE code=? AND status='pending' AND expires_at > NOW()");
          $stmt->execute([$code]);
          $invite = $stmt->fetch(PDO::FETCH_ASSOC);

          if ($invite) {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("INSERT INTO users (role_id, name, email, password_hash) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$invite['role_id'], $name, $invite['email'], $hash]);
                    $user_id = $db->lastInsertId();

                    $db->prepare("UPDATE invites SET status='used' WHERE id=?")->execute([$invite['id']]);
                    logAction($user_id, "User registered via invite");
                    return true;
          }
          return false;
}

// Manual add (admin only)
function addUserManual($name, $email, $role_id, $password)
{
          $db = getDB();
          $hash = password_hash($password, PASSWORD_BCRYPT);
          $stmt = $db->prepare("INSERT INTO users (role_id, name, email, password_hash) VALUES (?, ?, ?, ?)");
          $stmt->execute([$role_id, $name, $email, $hash]);
          $user_id = $db->lastInsertId();
          logAction($user_id, "User manually created");
          return $user_id;
}
