<?php
// actions/complaint_action.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/auth.php';

require_student();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Security validation failed.";
        header("Location: " . BASE_URL . "report_problem.php");
        exit();
    }

    $student_id = $_SESSION['student_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = (int)$_POST['category_id'];
    $location_id = (int)$_POST['location_id'];
    $severity = $_POST['severity'];
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    
    // --- SMART DUPLICATE DETECTION ALGORITHM ---
    if (!isset($_POST['force_submit'])) {
        $stmt = $pdo->prepare("
            SELECT id, title, description FROM complaints 
            WHERE category_id = ? AND location_id = ? AND status IN ('Pending', 'Under Review', 'In Progress')
        ");
        $stmt->execute([$category_id, $location_id]);
        $existing_complaints = $stmt->fetchAll();

        $highest_similarity = 0;
        $duplicate_id = null;
        $new_text = strtolower($title . " " . $description);

        foreach ($existing_complaints as $existing) {
            $existing_text = strtolower($existing['title'] . " " . $existing['description']);
            similar_text($new_text, $existing_text, $percent);
            
            if ($percent > $highest_similarity) {
                $highest_similarity = $percent;
                $duplicate_id = $existing['id'];
            }
        }

        if ($highest_similarity >= SIMILARITY_THRESHOLD) {
            $_SESSION['temp_complaint'] = $_POST;
            header("Location: " . BASE_URL . "duplicate_warning.php?similar_id=" . $duplicate_id);
            exit();
        }
    }

    // --- PRIORITY SCORE CALCULATION ---
    $priority_score = 0;
    switch($severity) {
        case 'Low': $priority_score += 10; break;
        case 'Medium': $priority_score += 30; break;
        case 'High': $priority_score += 60; break;
        case 'Critical': $priority_score += 100; break;
    }
    $priority_score += 5; 
    
    if ($priority_score > 100) $priority_score = 100;

    // --- PHOTO UPLOAD HANDLING ---
    $photo_name = null;
    if (isset($_FILES['complaint_photo']) && $_FILES['complaint_photo']['error'] === UPLOAD_ERR_OK) {
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
        $file_tmp = $_FILES['complaint_photo']['tmp_name'];
        $file_mime = mime_content_type($file_tmp);
        
        if (in_array($file_mime, $allowed_mimes) && $_FILES['complaint_photo']['size'] <= 5242880) { // 5MB Max
            $ext = pathinfo($_FILES['complaint_photo']['name'], PATHINFO_EXTENSION);
            $photo_name = 'comp_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            $upload_path = '../uploads/complaints/' . $photo_name;
            move_uploaded_file($file_tmp, $upload_path);
        }
    }

    // --- INSERT TO DATABASE ---
    try {
        if(isset($_SESSION['temp_complaint'])) unset($_SESSION['temp_complaint']);

        $stmt = $pdo->prepare("
            INSERT INTO complaints (student_id, category_id, location_id, title, description, photo, severity, priority_score, is_anonymous) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $student_id, $category_id, $location_id, $title, $description, 
            $photo_name, $severity, $priority_score, $is_anonymous
        ]);
        
        $new_complaint_id = $pdo->lastInsertId();

        // Note: The self-voting INSERT statement has been completely removed from here.

        $_SESSION['success'] = "Problem reported successfully!";
        header("Location: " . BASE_URL . "complaint_details.php?id=" . $new_complaint_id);
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Failed to submit report. Please try again.";
        header("Location: " . BASE_URL . "report_problem.php");
        exit();
    }
}