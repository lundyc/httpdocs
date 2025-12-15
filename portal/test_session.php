<?php

/**
 * Simple test page to verify login session works
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Session Test Page</h2>";

try {
          require_once __DIR__ . "/auth/auth_functions.php";

          echo "<h3>Session Information:</h3>";
          echo "<p>Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? "Active" : "Inactive") . "</p>";
          echo "<p>Session ID: " . session_id() . "</p>";

          echo "<h3>Session Data:</h3>";
          echo "<pre>";
          print_r($_SESSION);
          echo "</pre>";

          if (isset($_SESSION['user_id'])) {
                    echo "<p>✅ User is logged in with ID: " . $_SESSION['user_id'] . "</p>";

                    // Try to get user info from database
                    $db = getDB();
                    $stmt = $db->prepare("SELECT name, email FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user) {
                              echo "<p>✅ User found in database: " . htmlspecialchars($user['name']) . " (" . htmlspecialchars($user['email']) . ")</p>";
                    } else {
                              echo "<p>❌ User not found in database</p>";
                    }
          } else {
                    echo "<p>❌ User is not logged in</p>";
          }
} catch (Exception $e) {
          echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
          echo "<p>Stack trace:</p><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

?>

<hr>
<p><a href="auth/login.php">Back to Login</a></p>
<p><a href="index.php">Go to Portal Home</a></p>