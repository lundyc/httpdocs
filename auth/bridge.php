<?php

/**
 * Simple redirect bridge - no longer using cross-site sessions
 * Just validates the target and redirects
 */
require_once __DIR__ . '/auth_functions.php';

$rawTarget = $_GET['target'] ?? '';
$target = filter_var($rawTarget, FILTER_SANITIZE_URL);

// If no target, redirect to portal home
if (!$target) {
          header('Location: /portal/index.php');
          exit;
}

$parsed = parse_url($target);

// Basic validation - ensure it's a local path or allowed domain
$allowedPaths = ['/admin/', '/pos/', '/public/', '/portal/'];
$isValidPath = false;

foreach ($allowedPaths as $allowedPath) {
          if (strpos($parsed['path'] ?? '', $allowedPath) === 0) {
                    $isValidPath = true;
                    break;
          }
}

if (!$isValidPath) {
          header('Location: /portal/index.php');
          exit;
}

$verbose = isset($_GET['debug']);

if ($verbose) {
          header('Content-Type: text/plain');
          echo "Bridge Debug Report\n";
          echo "===================\n";
          echo "Incoming target: " . $rawTarget . "\n";
          echo "Sanitized target: " . $target . "\n";
          echo "Parsed path: " . ($parsed['path'] ?? '') . "\n";
          echo "Is valid path: " . ($isValidPath ? 'Yes' : 'No') . "\n";
          echo "PHP Session ID: " . session_id() . "\n";
          echo "User ID: " . ($_SESSION['user_id'] ?? 'none') . "\n";
          exit;
}

// Simple redirect - PHP sessions will handle authentication
header('Location: ' . $target);
exit;
