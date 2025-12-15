<?php

/**
 * Simple PHP session initialization for My Club Hub
 * Replaces the complex cross-site session system
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
          // Set session cookie parameters to work across the entire site
          session_set_cookie_params([
                    'lifetime' => 0,  // Session cookie (expires when browser closes)
                    'path' => '/',    // Available for entire domain
                    'domain' => '',   // Current domain
                    'secure' => isset($_SERVER['HTTPS']), // Use HTTPS if available
                    'httponly' => true, // Prevent JavaScript access
                    'samesite' => 'Lax' // CSRF protection
          ]);

          session_start();

          // Debug session info
          error_log("Session started - ID: " . session_id());
          error_log("Session save path: " . session_save_path());
          error_log("Session cookie params: " . print_r(session_get_cookie_params(), true));
}

// Database connection function
function getDB()
{
          static $pdo;
          if (!$pdo) {
                    require_once __DIR__ . '/../config/config.local.php';
                    try {
                              $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                              $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    } catch (PDOException $e) {
                              die("Database connection failed: " . $e->getMessage());
                    }
          }
          return $pdo;
}

