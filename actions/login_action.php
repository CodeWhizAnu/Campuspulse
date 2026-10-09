<?php
// actions/login_action.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid security token. Please try again.";
        header("Location: " . BASE_URL . "login.php?role=" . $_POST['role']);
        exit();
    }

    $login_id = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($login_id) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: " . BASE_URL . "login.php?role=" . $role);
        exit();
    }

    try {
        if ($role === 'student') {
            // Student login allows email OR PRN (Using separate parameter names for strict mode)
            $stmt = $pdo->prepare("
                SELECT u.id, u.password, u.role, u.name, s.id as student_id 
                FROM users u 
                JOIN students s ON u.id = s.user_id 
                WHERE (u.email = :login_email OR s.prn = :login_prn) AND u.role = 'student'
            ");
            $stmt->execute([
                'login_email' => $login_id, 
                'login_prn' => $login_id
            ]);
        } else {
            // Admin login by email
            $stmt = $pdo->prepare("
                SELECT u.id, u.password, u.role, u.name, a.id as admin_id 
                FROM users u 
                JOIN admins a ON u.id = a.user_id 
                WHERE u.email = :login_email AND u.role = 'admin'
            ");
            $stmt->execute([
                'login_email' => $login_id
            ]);
        }
        
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Prevent Session Fixation
            session_regenerate_id(true);

            // Set Session Variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            if ($role === 'student') {
                $_SESSION['student_id'] = $user['student_id'];
                header("Location: " . BASE_URL . "student_dashboard.php");
            } else {
                $_SESSION['admin_id'] = $user['admin_id'];
                header("Location: " . BASE_URL . "admin_dashboard.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Invalid credentials. Please try again.";
            header("Location: " . BASE_URL . "login.php?role=" . $role);
            exit();
        }

    } catch (PDOException $e) {
        // Temporarily showing the exact error message if anything else fails
        $_SESSION['error'] = "Database Error: " . $e->getMessage();
        header("Location: " . BASE_URL . "login.php?role=" . $role);
        exit();
    }
} else {
    header("Location: " . BASE_URL . "index.php");
    exit();
}