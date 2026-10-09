<?php
// actions/vote_action.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/auth.php';

require_student();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Security validation failed.";
        header("Location: " . BASE_URL . "student_dashboard.php");
        exit();
    }

    $student_id = $_SESSION['student_id'];
    $complaint_id = (int)$_POST['complaint_id'];
    $redirect_url = $_POST['redirect_url'] ?? (BASE_URL . "complaint_details.php?id=" . $complaint_id);

    try {
        // PREVENT AUTHOR FROM VOTING: Check if the user is the one who created it
        $owner_check = $pdo->prepare("SELECT student_id FROM complaints WHERE id = ?");
        $owner_check->execute([$complaint_id]);
        $owner_id = $owner_check->fetchColumn();

        if ($owner_id == $student_id) {
            $_SESSION['error'] = "You cannot vote for a problem you reported yourself.";
            header("Location: " . $redirect_url);
            exit();
        }

        // Check if vote already exists
        $stmt = $pdo->prepare("SELECT id FROM complaint_votes WHERE complaint_id = ? AND student_id = ?");
        $stmt->execute([$complaint_id, $student_id]);
        $existing_vote = $stmt->fetch();

        if ($existing_vote) {
            // Remove vote
            $del = $pdo->prepare("DELETE FROM complaint_votes WHERE complaint_id = ? AND student_id = ?");
            $del->execute([$complaint_id, $student_id]);
            
            // Decrease priority slightly when a vote is removed
            $pdo->prepare("UPDATE complaints SET priority_score = GREATEST(0, priority_score - 2) WHERE id = ?")->execute([$complaint_id]);
            
            $_SESSION['success'] = "You have removed your support for this issue.";
        } else {
            // Add vote
            $ins = $pdo->prepare("INSERT INTO complaint_votes (complaint_id, student_id) VALUES (?, ?)");
            $ins->execute([$complaint_id, $student_id]);
            
            // Increase priority score when voted
            $pdo->prepare("UPDATE complaints SET priority_score = LEAST(100, priority_score + 2) WHERE id = ?")->execute([$complaint_id]);
            
            $_SESSION['success'] = "You are now supporting this issue!";
        }

        header("Location: " . $redirect_url);
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error while processing your vote.";
        header("Location: " . $redirect_url);
        exit();
    }
}