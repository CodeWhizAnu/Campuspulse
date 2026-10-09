<?php
// config/auth.php
// Handles sessions, CSRF tokens, and Role-Based Access Control (RBAC)

session_start();

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to check if logged in user is a student
function is_student() {
    return is_logged_in() && $_SESSION['role'] === 'student';
}

// Function to check if logged in user is an admin
function is_admin() {
    return is_logged_in() && $_SESSION['role'] === 'admin';
}

// Enforce login requirement
function require_login() {
    if (!is_logged_in()) {
        header("Location: " . BASE_URL . "index.php?error=login_required");
        exit();
    }
}

// Enforce student role
function require_student() {
    require_login();
    if (!is_student()) {
        header("Location: " . BASE_URL . "index.php?error=unauthorized");
        exit();
    }
}

// Enforce admin role
function require_admin() {
    require_login();
    if (!is_admin()) {
        header("Location: " . BASE_URL . "index.php?error=unauthorized");
        exit();
    }
}

// Securely redirect logged-in users away from auth pages
function redirect_if_logged_in() {
    if (is_student()) {
        header("Location: " . BASE_URL . "student_dashboard.php");
        exit();
    } elseif (is_admin()) {
        header("Location: " . BASE_URL . "admin_dashboard.php");
        exit();
    }
}
?>