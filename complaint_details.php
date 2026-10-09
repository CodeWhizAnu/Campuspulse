<?php
// complaint_details.php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/auth.php';

require_student();

if (!isset($_GET['id'])) {
    header("Location: " . BASE_URL . "student_dashboard.php");
    exit();
}

$complaint_id = (int)$_GET['id'];
$student_id = $_SESSION['student_id'];

// Fetch Complaint Details
$stmt = $pdo->prepare("
    SELECT c.*, cat.name as category_name, cat.icon, l.name as location_name,
           u.name as reporter_name, s.course, s.academic_year,
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
    header("Location: " . BASE_URL . "student_dashboard.php");
    exit();
}

// Check if current user has voted
$vote_check = $pdo->prepare("SELECT id FROM complaint_votes WHERE complaint_id = ? AND student_id = ?");
$vote_check->execute([$complaint_id, $student_id]);
$has_voted = $vote_check->fetch() ? true : false;

// Fetch Status History Timeline
$history_stmt = $pdo->prepare("
    SELECT ch.*, u.name as admin_name 
    FROM complaint_status_history ch
    LEFT JOIN admins a ON ch.admin_id = a.id
    LEFT JOIN users u ON a.user_id = u.id
    WHERE ch.complaint_id = ?
    ORDER BY ch.created_at DESC
");
$history_stmt->execute([$complaint_id]);
$history = $history_stmt->fetchAll();

$page_title = "Issue Details - " . APP_NAME;
include 'includes/header.php';
include 'includes/student_sidebar.php';
?>
<style>
    .timeline { border-left: 2px solid var(--border-color); padding-left: 1.5rem; margin-top: 1.5rem; }
    .timeline-item { position: relative; margin-bottom: 1.5rem; }
    .timeline-item::before {
        content: ''; position: absolute; left: -29px; top: 0;
        width: 14px; height: 14px; border-radius: 50%;
        background: var(--primary); border: 3px solid var(--card-bg);
    }
</style>

<div class="main-content">
    <a href="student_dashboard.php" style="color: var(--text-muted); display: inline-block; margin-bottom: 1rem;">&larr; Back to Dashboard</a>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div style="padding: 1rem; background: rgba(255, 77, 109, 0.1); color: var(--danger); border-radius: 8px; margin-bottom: 1.5rem;">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['success'])): ?>
        <div style="padding: 1rem; background: rgba(34, 197, 94, 0.1); color: var(--success); border-radius: 8px; margin-bottom: 1.5rem;">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        
        <!-- Main Details -->
        <div class="glass-panel" style="padding: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem;">
                <div>
                    <h1 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($complaint['title']); ?></h1>
                    <div style="display: flex; gap: 1rem; color: var(--text-muted); font-size: 0.875rem;">
                        <span>📍 <?php echo htmlspecialchars($complaint['location_name']); ?></span>
                        <span>📂 <?php echo htmlspecialchars($complaint['category_name']); ?></span>
                        <span>🗓️ <?php echo date('M d, Y', strtotime($complaint['created_at'])); ?></span>
                    </div>
                </div>
                <span class="badge badge-<?php echo strtolower($complaint['status'] === 'Resolved' ? 'resolved' : 'medium'); ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                    <?php echo $complaint['status']; ?>
                </span>
            </div>

            <div style="margin-bottom: 2rem; background: var(--bg-color); padding: 1.5rem; border-radius: 12px;">
                <h3 style="margin-bottom: 0.5rem;">Description</h3>
                <p style="white-space: pre-wrap;"><?php echo htmlspecialchars($complaint['description']); ?></p>
            </div>

            <?php if($complaint['photo']): ?>
                <div style="margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 0.5rem;">Attached Photo</h3>
                    <img src="<?php echo BASE_URL; ?>uploads/complaints/<?php echo htmlspecialchars($complaint['photo']); ?>" 
                         alt="Problem Image" style="max-width: 100%; border-radius: 12px; box-shadow: var(--shadow-sm);">
                </div>
            <?php endif; ?>
            
            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 2rem 0;">
            
            <!-- Voting Area -->
            <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(108, 99, 255, 0.05); padding: 1.5rem; border-radius: 12px; border: 1px solid rgba(108, 99, 255, 0.2);">
                <div>
                    <h3 style="color: var(--primary);">👍 <?php echo $complaint['vote_count']; ?> students affected</h3>
                    <p style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.25rem;">Upvoting increases the Smart Priority score.</p>
                </div>
                
                <?php if($complaint['student_id'] == $student_id): ?>
                    <!-- NEW LOGIC: Hide button if the student is the author -->
                    <button class="btn btn-outline" disabled style="opacity: 0.6; cursor: not-allowed; border-color: var(--text-muted); color: var(--text-muted);">You reported this</button>
                <?php elseif($complaint['status'] !== 'Resolved' && $complaint['status'] !== 'Rejected'): ?>
                    <form action="actions/vote_action.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
                        <?php if($has_voted): ?>
                            <button type="submit" class="btn btn-outline" style="border-color: var(--danger); color: var(--danger);">Remove Support</button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-primary">I'm Facing This Too</button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar Meta -->
        <div>
            <!-- Smart Priority Card -->
            <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h3 style="margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between;">
                    Smart Priority
                    <span style="font-size: 1.5rem;">🔥</span>
                </h3>
                <div style="font-size: 2.5rem; font-weight: 800; color: var(--primary); margin-bottom: 0.5rem;">
                    <?php echo $complaint['priority_score']; ?><span style="font-size: 1rem; color: var(--text-muted);">/100</span>
                </div>
                <div style="font-size: 0.875rem; color: var(--text-muted);">
                    <strong>Severity:</strong> <?php echo $complaint['severity']; ?>
                </div>
                <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem;">
                    <strong>Reported by:</strong> 
                    <?php echo $complaint['is_anonymous'] ? 'Anonymous Student' : htmlspecialchars($complaint['reporter_name']); ?>
                </div>
            </div>

            <!-- Resolution Timeline -->
            <div class="glass-panel" style="padding: 1.5rem;">
                <h3 style="margin-bottom: 1rem;">Status Timeline</h3>
                <div class="timeline">
                    <?php if(empty($history)): ?>
                        <p style="color: var(--text-muted); font-size: 0.875rem;">Waiting for administrative review.</p>
                    <?php else: ?>
                        <?php foreach($history as $record): ?>
                            <div class="timeline-item">
                                <strong><?php echo htmlspecialchars($record['new_status']); ?></strong>
                                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                                    <?php echo date('M d, Y - h:i A', strtotime($record['created_at'])); ?> 
                                    <?php if($record['admin_name']) echo "by " . explode(' ', $record['admin_name'])[0]; ?>
                                </p>
                                <?php if($record['notes']): ?>
                                    <div style="background: var(--bg-color); padding: 0.5rem; border-radius: 6px; font-size: 0.875rem; margin-top: 0.5rem;">
                                        <?php echo htmlspecialchars($record['notes']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <div class="timeline-item">
                        <strong>Problem Reported</strong>
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                            <?php echo date('M d, Y - h:i A', strtotime($complaint['created_at'])); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</body>
</html>