<?php
// actions/status_action.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Security validation failed.";
        header("Location: " . BASE_URL . "admin_dashboard.php");
        exit();
    }

    $complaint_id = (int)$_POST['complaint_id'];
    $new_status = $_POST['status'];
    $notes = trim($_POST['notes']);
    $admin_id = $_SESSION['admin_id'];

    try {
        $pdo->beginTransaction();

        // Get current status
        $stmt = $pdo->prepare("SELECT status FROM complaints WHERE id = ? FOR UPDATE");
        $stmt->execute([$complaint_id]);
        $current = $stmt->fetch();

        if (!$current) {
            throw new Exception("Complaint not found.");
        }

        $old_status = $current['status'];

        // NEW LOGIC: Lock the status if it is already resolved
        if ($old_status === 'Resolved') {
            $pdo->rollBack();
            $_SESSION['error'] = "This complaint is already marked as Resolved and cannot be modified.";
            header("Location: " . BASE_URL . "admin_complaint_details.php?id=" . $complaint_id);
            exit();
        }

        if ($old_status !== $new_status) {
            // Update complaint
            $update_sql = "UPDATE complaints SET status = ?";
            if ($new_status === 'Resolved') {
                $update_sql .= ", resolved_at = CURRENT_TIMESTAMP";
            }
            $update_sql .= " WHERE id = ?";
            
            $pdo->prepare($update_sql)->execute([$new_status, $complaint_id]);

            // Insert into history
            $hist_stmt = $pdo->prepare("
                INSERT INTO complaint_status_history (complaint_id, admin_id, old_status, new_status, notes) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $hist_stmt->execute([$complaint_id, $admin_id, $old_status, $new_status, $notes]);
            
            $_SESSION['success'] = "Status updated to " . htmlspecialchars($new_status) . " successfully.";
        } else {
            $_SESSION['error'] = "Status is already set to " . htmlspecialchars($new_status) . ".";
        }

        $pdo->commit();
        header("Location: " . BASE_URL . "admin_complaint_details.php?id=" . $complaint_id);
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Failed to update status. Please try again.";
        header("Location: " . BASE_URL . "admin_complaint_details.php?id=" . $complaint_id);
        exit();
    }
}