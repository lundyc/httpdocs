<?php

/**
 * Login Debug Test Page
 * Use this to test login functionality step by step  
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/auth/auth_functions.php";

echo "<h2>Login Debug Test</h2>";

// Test form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $email = $_POST['email'] ?? '';
          $password = $_POST['password'] ?? '';

          echo "<h3>Form Submission Test</h3>";
          echo "<p>Email: " . htmlspecialchars($email) . "</p>";
          echo "<p>Password: " . (strlen($password) > 0 ? "[" . strlen($password) . " characters]" : "[empty]") . "</p>";

          echo "<h3>Database Connection Test</h3>";
          try {
                    $db = getDB();
                    echo "<p>✅ Database connection successful</p>";

                    // Check if user exists
                    $stmt = $db->prepare("SELECT id, name, email, status, password_hash FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user) {
                              echo "<p>✅ User found in database:</p>";
                              echo "<ul>";
                              echo "<li>ID: " . htmlspecialchars($user['id']) . "</li>";
                              echo "<li>Name: " . htmlspecialchars($user['name']) . "</li>";
                              echo "<li>Email: " . htmlspecialchars($user['email']) . "</li>";
                              echo "<li>Status: " . htmlspecialchars($user['status']) . "</li>";
                              echo "<li>Password hash starts with: " . htmlspecialchars(substr($user['password_hash'], 0, 10)) . "...</li>";
                              echo "</ul>";

                              // Test password verification
                              if (!empty($password)) {
                                        $passwordTest = password_verify($password, $user['password_hash']);
                                        echo "<p>Password verification: " . ($passwordTest ? "✅ Success" : "❌ Failed") . "</p>";

                                        if ($user['status'] !== 'active') {
                                                  echo "<p>❌ User status is not 'active': " . htmlspecialchars($user['status']) . "</p>";
                                        }
                              }
                    } else {
                              echo "<p>❌ No user found with email: " . htmlspecialchars($email) . "</p>";
                    }

                    // Test the actual login function
                    if (!empty($email) && !empty($password)) {
                              echo "<h3>Login Function Test</h3>";
                              echo "<p>Session before login:</p>";
                              echo "<pre>" . print_r($_SESSION, true) . "</pre>";

                              $loginResult = login($email, $password);
                              echo "<p>Login function result: " . ($loginResult ? "✅ Success" : "❌ Failed") . "</p>";

                              echo "<p>Session after login:</p>";
                              echo "<pre>" . print_r($_SESSION, true) . "</pre>";
                    }
          } catch (Exception $e) {
                    echo "<p>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
          }
}

?>

<form method="POST" style="border: 1px solid #12304f; padding: 20px; margin: 20px 0; background: #f6f8ff; color: #001d3d;">
          <h3>Test Login</h3>
          <p>
                    <label>Email:</label><br>
                    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? 'admin@myclubhub.co.uk') ?>" style="width: 300px; padding: 5px; border: 1px solid #12304f; background: #f6f8ff; color: #001d3d;">
          </p>
          <p>
                    <label>Password:</label><br>
                    <input type="password" name="password" placeholder="Enter password" style="width: 300px; padding: 5px; border: 1px solid #12304f; background: #f6f8ff; color: #001d3d;">
          </p>
          <p>
                    <button type="submit" style="padding: 10px 20px; background: #ffc300; color: #000814; border: none; font-weight: 700;">Test Login</button>
          </p>
</form>

<hr>
<p><a href="auth/login.php">Back to Real Login Page</a></p>
