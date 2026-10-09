<?php
// admin_complaint_details.php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/auth.php';

require_admin();

if (!isset($_GET['id'])) {
    header("Location: " . BASE_URL . "admin_complaints.php");
    exit();
}

$complaint_id = (int)$_GET['id'];

// Fetch detailed data
$stmt = $pdo->prepare("
    SELECT c.*, cat.name as category_name, l.name as location_name,
           u.name as reporter_name, u.email as reporter_email, s.prn, s.phone,
           (SELECT COUNT(*) FROM complaint_votes WHERE complaint_id = c.id) as vote_count
    FROM complaints c
    JOIN categories cat ON c.category_id = cat.id
    JOIN locations l ON c.location_id = l.id
    JOIN students s ON c.student_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE c.id = ?
");
$stmt->execute([$complaint_id]);
$complaint = $stmt->fetch();

if (!$complaint) {
    header("Location: " . BASE_URL . "admin_complaints.php");
    exit();
}

// Fetch the list of students who voted
$voters_stmt = $pdo->prepare("
    SELECT u.name, s.prn 
    FROM complaint_votes cv
    JOIN students s ON cv.student_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE cv.complaint_id = ?
");
$voters_stmt->execute([$complaint_id]);
$voters = $voters_stmt->fetchAll();

$page_title = "Manage Issue - " . APP_NAME;
include 'includes/header.php';
include 'includes/admin_sidebar.php';
?>

<div class="main-content">
    <a href="admin_complaints.php" style="color: var(--text-muted); display: inline-block; margin-bottom: 1rem;">&larr; Back to Complaints</a>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div style="padding: 1rem; background: rgba(34, 197, 94, 0.1); color: var(--success); border-radius: 8px; margin-bottom: 1.5rem;">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div style="padding: 1rem; background: rgba(255, 77, 109, 0.1); color: var(--danger); border-radius: 8px; margin-bottom: 1.5rem;">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        
        <!-- Left: Issue Data -->
        <div class="glass-panel" style="padding: 2rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem;">
                <h1 style="font-size: 1.75rem;"><?php echo htmlspecialchars($complaint['title']); ?></h1>
                <span class="badge badge-<?php echo strtolower($complaint['status'] === 'Resolved' ? 'resolved' : 'medium'); ?>" style="font-size: 1rem; align-self: flex-start;">
                    Current: <?php echo $complaint['status']; ?>
                </span>
            </div>

            <p style="white-space: pre-wrap; margin-bottom: 2rem; color: var(--text-main); font-size: 1.1rem; line-height: 1.8;">
                <?php echo htmlspecialchars($complaint['description']); ?>
            </p>

            <?php if($complaint['photo']): ?>
                <div style="margin-bottom: 2rem;">
                    <img src="<?php echo BASE_URL; ?>uploads/complaints/<?php echo htmlspecialchars($complaint['photo']); ?>" 
                         alt="Problem Image" style="max-width: 100%; border-radius: 12px; border: 1px solid var(--border-color);">
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; background: var(--bg-color); padding: 1.5rem; border-radius: 12px;">
                <div>
                    <h4 style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Location & Category</h4>
                    <p>📍 <?php echo htmlspecialchars($complaint['location_name']); ?></p>
                    <p>📂 <?php echo htmlspecialchars($complaint['category_name']); ?></p>
                </div>
                <div>
                    <h4 style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Reporter Details (Hidden from Public)</h4>
                    <p>👤 <?php echo htmlspecialchars($complaint['reporter_name']); ?> (<?php echo htmlspecialchars($complaint['prn']); ?>)</p>
                    <p>📞 <?php echo htmlspecialchars($complaint['phone'] ?: 'N/A'); ?></p>
                    <p style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem;">
                        Student requested anonymity: <strong><?php echo $complaint['is_anonymous'] ? 'Yes' : 'No'; ?></strong>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right: Management Actions & Voters -->
        <div>
            <!-- Smart Priority Analysis -->
            <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 1.5rem; text-align: center; border: 2px solid rgba(108, 99, 255, 0.2);">
                <h3 style="color: var(--text-muted); font-size: 1rem; margin-bottom: 0.5rem;">Algorithm Priority</h3>
                <div style="font-size: 3rem; font-weight: 800; color: var(--primary);">
                    <?php echo $complaint['priority_score']; ?>
                </div>
                <div style="display: flex; justify-content: space-around; margin-top: 1rem; font-size: 0.875rem;">
                    <div>Severity<br><span style="font-weight: bold; color: var(--danger);"><?php echo $complaint['severity']; ?></span></div>
                    <div>Affected<br><span style="font-weight: bold; color: var(--accent);"><?php echo $complaint['vote_count']; ?> students</span></div>
                </div>
            </div>

            <!-- Conditional Status UI -->
            <?php if($complaint['status'] === 'Resolved'): ?>
                <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 1.5rem; text-align: center; border: 1px solid var(--success); background: rgba(34, 197, 94, 0.05);">
                    <h3 style="color: var(--success); margin-bottom: 0.5rem;">✅ Issue Resolved</h3>
                    <p style="color: var(--text-muted); font-size: 0.875rem;">This problem has been permanently marked as resolved and the status cannot be changed.</p>
                </div>
            <?php else: ?>
                <!-- Update Status Form -->
                <form action="actions/status_action.php" method="POST" class="glass-panel" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
                    
                    <h3 style="margin-bottom: 1rem;">Update Status</h3>
                    
                    <div class="form-group">
                        <label>Action</label>
                        <select name="status" class="form-control" required>
                            <option value="Pending" <?php if($complaint['status']=='Pending') echo 'selected'; ?>>🟡 Pending</option>
                            <option value="Under Review" <?php if($complaint['status']=='Under Review') echo 'selected'; ?>>🔵 Under Review</option>
                            <option value="In Progress" <?php if($complaint['status']=='In Progress') echo 'selected'; ?>>🟠 In Progress</option>
                            <option value="Resolved">🟢 Resolved</option>
                            <option value="Rejected" <?php if($complaint['status']=='Rejected') echo 'selected'; ?>>🔴 Rejected (Invalid)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Resolution Notes (Visible to Student)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="e.g., Plumber has been dispatched."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Apply Update</button>
                </form>
            <?php endif; ?>

            <!-- Affected Students List (Voters) -->
            <div class="glass-panel" style="padding: 1.5rem;">
                <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Supported By (<?php echo count($voters); ?>)</h3>
                <div style="max-height: 200px; overflow-y: auto; background: var(--bg-color); border-radius: 8px; padding: 0.5rem;">
                    <?php if(empty($voters)): ?>
                        <p style="color: var(--text-muted); font-size: 0.875rem; text-align: center; padding: 1rem 0;">No votes yet.</p>
                    <?php else: ?>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <?php foreach($voters as $voter): ?>
                                <li style="padding: 0.5rem; border-bottom: 1px solid var(--border-color); font-size: 0.875rem;">
                                    <strong><?php echo htmlspecialchars($voter['name']); ?></strong> 
                                    <span style="color: var(--text-muted);"> (<?php echo htmlspecialchars($voter['prn']); ?>)</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>