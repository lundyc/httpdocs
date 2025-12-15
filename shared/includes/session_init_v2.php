<?php
// Cross-subdomain session management for My Club Hub

if (session_status() === PHP_SESSION_NONE) {
          // First attempt: Standard PHP sessions with .myclubhub.co.uk domain
          session_set_cookie_params(0, '/', '.myclubhub.co.uk', false, true);
          session_start();

          // If standard session cookie isn't working, use a manual approach
          if (!isset($_COOKIE[session_name()]) || empty($_COOKIE[session_name()])) {
                    // Try to find an existing session from another subdomain
                    $bridgeSessionId = $_COOKIE['MYCLUBHUB_SESSION'] ?? null;

                    if ($bridgeSessionId && preg_match('/^myclubhub_[a-zA-Z0-9]{26}$/', $bridgeSessionId)) {
                              // Extract the original session ID
                              $originalSessionId = substr($bridgeSessionId, 10); // Remove 'myclubhub_' prefix

                              // Try to resume the session
                              session_write_close();
                              session_id($originalSessionId);
                              session_start();
                    } else {
                              // Create a new bridge session
                              $bridgeSessionId = 'myclubhub_' . session_id();
                              $_SESSION['myclubhub_bridge'] = $bridgeSessionId;
                    }

                    // Set the bridge cookie
                    setcookie('MYCLUBHUB_SESSION', $bridgeSessionId, [
                              'expires' => 0,
                              'path' => '/',
                              'domain' => '.myclubhub.co.uk',
                              'secure' => false,
                              'httponly' => true,
                              'samesite' => 'Lax'
                    ]);
          }
}



