<?php
// session.php
session_start();

/**
 * Check if the user is logged in.
 * If not, redirect to login page.
 */
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Logout the user.
 */
function logout() {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

/**
 * Check if the user has a specific role (e.g., 'Admin').
 */
function checkRole($role) {
    if ($_SESSION['role'] !== $role) {
        // You might want to redirect to an "Access Denied" page or home.php
        header("Location: home.php?error=unauthorized");
        exit();
    }
}
?>