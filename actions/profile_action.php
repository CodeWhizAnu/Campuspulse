<?php
// actions/profile_action.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/auth.php';

require_student();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Security validation failed.";
        header("Location: " . BASE_URL . "student_profile.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $student_id = $_SESSION['student_id'];
    $action_type = $_POST['action_type'] ?? '';

    try {
        if ($action_type === 'update_details') {
            $name = trim($_POST['name']);
            $phone = trim($_POST['phone']);
            $course = trim($_POST['course']);
            $academic_year = $_POST['academic_year'];
            $division = trim($_POST['division']);

            $pdo->beginTransaction();
            
            // Update users table
            $stmt1 = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
            $stmt1->execute([$name, $user_id]);
            
            // Update students table
            $stmt2 = $pdo->prepare("UPDATE students SET phone = ?, course = ?, academic_year = ?, division = ? WHERE id = ?");
            $stmt2->execute([$phone, $course, $academic_year, $division, $student_id]);
            
            $pdo->commit();
            
            $_SESSION['name'] = $name; // Update session name variable
            $_SESSION['success'] = "Profile details updated successfully.";

        } elseif ($action_type === 'update_password') {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password !== $confirm_password) {
                $_SESSION['error'] = "New passwords do not match.";
                header("Location: " . BASE_URL . "student_profile.php");
                exit();
            }

            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if (password_verify($current_password, $user['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update->execute([$hashed_password, $user_id]);
                $_SESSION['success'] = "Password updated securely.";
            } else {
                $_SESSION['error'] = "Current password is incorrect.";
            }

        } elseif ($action_type === 'update_photo') {
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
                $file_tmp = $_FILES['profile_photo']['tmp_name'];
                $file_mime = mime_content_type($file_tmp);
                
                if (in_array($file_mime, $allowed_mimes) && $_FILES['profile_photo']['size'] <= 2097152) { // 2MB Max
                    // Get PRN for naming
                    $prn_stmt = $pdo->prepare("SELECT prn FROM students WHERE id = ?");
                    $prn_stmt->execute([$student_id]);
                    $prn = $prn_stmt->fetchColumn();

                    $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
                    $photo_name = $prn . '_' . time() . '.' . $ext;
                    $upload_path = '../uploads/profile/' . $photo_name;
                    
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        $update = $pdo->prepare("UPDATE students SET profile_photo = ? WHERE id = ?");
                        $update->execute([$photo_name, $student_id]);
                        $_SESSION['success'] = "Profile photo updated.";
                    } else {
                        $_SESSION['error'] = "Failed to save photo to disk.";
                    }
                } else {
                    $_SESSION['error'] = "Invalid file type or size exceeds 2MB.";
                }
            } else {
                $_SESSION['error'] = "No valid file uploaded.";
            }
        }

        header("Location: " . BASE_URL . "student_profile.php");
        exit();

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = "A system error occurred. Please try again.";
        header("Location: " . BASE_URL . "student_profile.php");
        exit();
    }
}