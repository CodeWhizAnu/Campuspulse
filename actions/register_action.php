<?php
// actions/register_action.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Security validation failed.";
        header("Location: " . BASE_URL . "register.php?role=" . $_POST['role']);
        exit();
    }

    $role = $_POST['role'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic Validation
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: " . BASE_URL . "register.php?role=" . $role);
        exit();
    }
    
    // Check Email uniqueness
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Email is already registered.";
        header("Location: " . BASE_URL . "register.php?role=" . $role);
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        // 1. Insert into base users table
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashed_password, $role]);
        $user_id = $pdo->lastInsertId();

        if ($role === 'student') {
            $prn = trim($_POST['prn']);
            $course = trim($_POST['course']);
            $academic_year = $_POST['academic_year'];
            $division = trim($_POST['division']);
            $phone = trim($_POST['phone']);

            // Check PRN uniqueness
            $stmt = $pdo->prepare("SELECT id FROM students WHERE prn = ?");
            $stmt->execute([$prn]);
            if ($stmt->fetch()) {
                $pdo->rollBack();
                $_SESSION['error'] = "PRN is already registered.";
                header("Location: " . BASE_URL . "register.php?role=student");
                exit();
            }

            // Handle Profile Photo Upload Offline
            $photo_name = 'default.png';
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
                $file_tmp = $_FILES['profile_photo']['tmp_name'];
                $file_mime = mime_content_type($file_tmp);
                
                if (in_array($file_mime, $allowed_mimes) && $_FILES['profile_photo']['size'] <= 2097152) { // 2MB Max
                    $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
                    $photo_name = $prn . '_' . time() . '.' . $ext;
                    $upload_path = '../uploads/profile/' . $photo_name;
                    move_uploaded_file($file_tmp, $upload_path);
                }
            }

            $stmt = $pdo->prepare("INSERT INTO students (user_id, prn, course, academic_year, division, phone, profile_photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $prn, $course, $academic_year, $division, $phone, $photo_name]);

        } elseif ($role === 'admin') {
            $employee_id = trim($_POST['employee_id']);
            $admin_key = $_POST['admin_key'];

            // Verify Admin Secret Key
            if ($admin_key !== ADMIN_REGISTRATION_KEY) {
                $pdo->rollBack();
                $_SESSION['error'] = "Invalid Admin Registration Key.";
                header("Location: " . BASE_URL . "register.php?role=admin");
                exit();
            }

            // Check Employee ID uniqueness
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE employee_id = ?");
            $stmt->execute([$employee_id]);
            if ($stmt->fetch()) {
                $pdo->rollBack();
                $_SESSION['error'] = "Employee ID is already registered.";
                header("Location: " . BASE_URL . "register.php?role=admin");
                exit();
            }

            $stmt = $pdo->prepare("INSERT INTO admins (user_id, employee_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $employee_id]);
        }

        $pdo->commit();
        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: " . BASE_URL . "login.php?role=" . $role);
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Registration failed. Database error.";
        header("Location: " . BASE_URL . "register.php?role=" . $role);
        exit();
    }
}