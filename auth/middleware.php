<?php
require_once "auth_functions.php";

// Protect route - check if user is logged in
function requireLogin()
{
    if (empty($_SESSION['user_id'])) {
        // Build current URL for redirect back
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $currentUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $redirectUrl = BASE_URL . "/auth/login.php?redirect=" . urlencode($currentUrl);
        header("Location: " . $redirectUrl);
        exit;
    }
}

// Check if user has required role(s)
function checkRole($roles)
{
    if (empty($_SESSION['role_id']) || !in_array($_SESSION['role_id'], (array)$roles, true)) {
        // Build current URL for redirect back
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $currentUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $redirectUrl = BASE_URL . "/auth/login.php?redirect=" . urlencode($currentUrl);
        header("Location: " . $redirectUrl);
        exit;
    }
}
