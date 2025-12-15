<?php
// Database-based session system for reliable cross-subdomain authentication

// First, start a basic PHP session for this page
if (session_status() === PHP_SESSION_NONE) {
          session_start();
}

// Database connection function
function getSessionDB()
{
          static $pdo;
          if (!$pdo) {
                    require_once __DIR__ . '/../config/config.local.php';
                    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          }
          return $pdo;
}

// General database connection function (alias for compatibility)
function getDB()
{
          return getSessionDB();
}

// Create sessions table if it doesn't exist
function initSessionTable()
{
          $db = getSessionDB();
          $db->exec("CREATE TABLE IF NOT EXISTS myclubhub_sessions (
        id VARCHAR(64) PRIMARY KEY,
        user_id INT NULL,
        role_id INT NULL,
        data TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NULL,
        INDEX(user_id),
        INDEX(expires_at)
    )");
}

// Get session from database or URL parameter
function getMyClubHubSession()
{
          static $sessionData = null;

          if ($sessionData !== null) {
                    return $sessionData;
          }

          initSessionTable();

          $db = getSessionDB();
          $sessionId = null;

          // Try to get session ID from multiple sources (prefer explicit URL parameter)
          $sources = [
                    'URL myclubhub_session' => $_GET['myclubhub_session'] ?? null,
                    'POST myclubhub_session' => $_POST['myclubhub_session'] ?? null,
                    'Cookie MYCLUBHUB_SESSION' => $_COOKIE['MYCLUBHUB_SESSION'] ?? null,
                    'PHP Session myclubhub_session_id' => $_SESSION['myclubhub_session_id'] ?? null,
          ];

          foreach ($sources as $source => $value) {
                    if ($value && preg_match('/^[a-f0-9]{32}$/', $value)) {
                              $sessionId = $value;
                              break;
                    }
          }

          // If we got session from URL, clean the URL immediately
          if (isset($_GET['myclubhub_session']) && $sessionId) {
                    // Store in cookie and PHP session for future requests
                    setcookie('MYCLUBHUB_SESSION', $sessionId, time() + 86400, '/', '.myclubhub.co.uk', false, true);
                    $_SESSION['myclubhub_session_id'] = $sessionId;

                    // Redirect to clean URL if possible
                    $cleanUrl = strtok($_SERVER["REQUEST_URI"], '?');
                    if ($cleanUrl && $cleanUrl !== $_SERVER["REQUEST_URI"]) {
                              echo "<script>window.history.replaceState({}, document.title, '$cleanUrl');</script>";
                    }
          }

          if (!$sessionId) {
                    $sessionId = md5(uniqid(rand(), true));
          }

          // Clean up expired sessions
          $db->exec("DELETE FROM myclubhub_sessions WHERE expires_at < NOW()");

          // Try to load existing session
          $stmt = $db->prepare("SELECT * FROM myclubhub_sessions WHERE id = ? AND expires_at > NOW()");
          $stmt->execute([$sessionId]);
          $session = $stmt->fetch(PDO::FETCH_ASSOC);

          if ($session) {
                    $sessionData = [
                              'id' => $sessionId,
                              'user_id' => $session['user_id'],
                              'role_id' => $session['role_id'],
                              'data' => json_decode($session['data'] ?? '{}', true) ?: []
                    ];

                    // Update last activity
                    $db->prepare("UPDATE myclubhub_sessions SET last_activity = NOW() WHERE id = ?")
                              ->execute([$sessionId]);
          } else {
                    $sessionData = [
                              'id' => $sessionId,
                              'user_id' => null,
                              'role_id' => null,
                              'data' => []
                    ];

                    // Create new session record
                    $db->prepare("INSERT INTO myclubhub_sessions (id, expires_at) VALUES (?, DATE_ADD(NOW(), INTERVAL 24 HOUR))")
                              ->execute([$sessionId]);
          }

          // Store session ID in multiple places for persistence
          $_SESSION['myclubhub_session_id'] = $sessionId;
          setcookie('MYCLUBHUB_SESSION', $sessionId, time() + 86400, '/', '.myclubhub.co.uk', false, true);

          // Add invisible session bridge for cross-subdomain navigation
          echo "<script>
        (function () {
            const sessionId = '$sessionId';
            localStorage.setItem('myclubhub_session_id', sessionId);
            sessionStorage.removeItem('myclubhub_session_recovery_attempted');
            if ('$sessionData[user_id]') {
                console.debug('My Club Hub Session Sync', {
                    sessionId,
                    userId: '$sessionData[user_id]',
                    currentHost: window.location.host
                });
            }
            if (window.location.search.includes('auth_required=1')) {
                const cleanUrl = window.location.href.split('?')[0];
                window.history.replaceState({}, document.title, cleanUrl);
            }
        })();
        </script>";


          return $sessionData;
}

// Save session data to database
function saveMyClubHubSession($userData = null)
{
          $session = getMyClubHubSession();
          $db = getSessionDB();

          if ($userData) {
                    $session['user_id'] = $userData['user_id'] ?? null;
                    $session['role_id'] = $userData['role_id'] ?? null;
                    $session['data'] = array_merge($session['data'], $userData['data'] ?? []);
          }

          $stmt = $db->prepare("UPDATE myclubhub_sessions SET user_id = ?, role_id = ?, data = ?, last_activity = NOW() WHERE id = ?");
          $stmt->execute([
                    $session['user_id'],
                    $session['role_id'],
                    json_encode($session['data']),
                    $session['id']
          ]);
}

// Get session redirect URL with session ID
function getSessionURL($url)
{
          $session = getMyClubHubSession();
          $separator = (strpos($url, '?') !== false) ? '&' : '?';
          return $url . $separator . 'myclubhub_session=' . $session['id'];
}

// Initialize the session system
$GLOBALS['myclubhub_session'] = getMyClubHubSession();
