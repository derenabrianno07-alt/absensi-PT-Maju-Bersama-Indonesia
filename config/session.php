<?php
// config/session.php

// Secure session cookie settings
if (session_status() == PHP_SESSION_NONE) {
    // Determine if HTTPS is active
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    
    session_set_cookie_params([
        'lifetime' => 0, // Session cookie
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}

// Prevent session hijacking
if (isset($_SESSION['user_id'])) {
    if (!isset($_SESSION['user_ip']) || !isset($_SESSION['user_agent'])) {
        // First login setup
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    } else {
        // Validate session characteristics
        if ($_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR'] || $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            // Destroy session on mismatch (potential hijacking)
            session_unset();
            session_destroy();
            header("Location: " . '/absensi/auth/login.php?error=session_hijack');
            exit;
        }
    }
}

// Function to check if user is logged in
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /absensi/auth/login.php");
        exit;
    }
}

// Function to check if user is admin
function check_admin() {
    check_login();
    if ($_SESSION['role'] !== 'admin') {
        header("Location: /absensi/pegawai/index.php");
        exit;
    }
}

// Function to check if user is pegawai
function check_pegawai() {
    check_login();
    if ($_SESSION['role'] !== 'pegawai') {
        header("Location: /absensi/admin/index.php");
        exit;
    }
}
