<?php
require_once "auth_functions.php";

// Protect route - check if user is logged in
function requireLogin()
{
    // Debug: Log session status
    error_log("requireLogin called - Session data: " . print_r($_SESSION, true));
    error_log("User ID in session: " . ($_SESSION['user_id'] ?? 'NONE'));

    if (empty($_SESSION['user_id'])) {
        // Simple relative redirect to login page
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
        $loginUrl = "/portal/auth/login.php";
        if ($currentUrl) {
            $loginUrl .= "?redirect=" . urlencode($currentUrl);
        }
        error_log("Redirecting to login: " . $loginUrl);
        header("Location: " . $loginUrl);
        exit;
    }

    error_log("User is logged in with ID: " . $_SESSION['user_id']);
}

// Check if user has required role(s)
function checkRole($roles)
{
    if (empty($_SESSION['role_id']) || !in_array($_SESSION['role_id'], (array)$roles, true)) {
        // Build current URL for redirect back
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $currentUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $redirectUrl = "/portal/auth/login.php?redirect=" . urlencode($currentUrl);
        header("Location: " . $redirectUrl);
        exit;
    }
}
