<?php
// student_dashboard.php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/auth.php';

require_student();
$page_title = "Student Dashboard - " . APP_NAME;

$student_id = $_SESSION['student_id'];

// Fetch Stats
$stats = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Pending' OR status = 'Under Review' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved
    FROM complaints WHERE student_id = ?
");
$stats->execute([$student_id]);
$my_stats = $stats->fetch();

// Fetch Student's OWN Complaints
$complaints = $pdo->prepare("
    SELECT c.*, cat.name as category_name, l.name as location_name 
    FROM complaints c
    JOIN categories cat ON c.category_id = cat.id
    JOIN locations l ON c.location_id = l.id
    WHERE c.student_id = ?
    ORDER BY c.created_at DESC
");
$complaints->execute([$student_id]);
$my_complaints = $complaints->fetchAll();

// Fetch ALL Active Campus Complaints (so students can vote on others)
$all_active = $pdo->query("
    SELECT c.*, cat.name as category_name, l.name as location_name,
           (SELECT COUNT(*) FROM complaint_votes WHERE complaint_id = c.id) as vote_count
    FROM complaints c
    JOIN categories cat ON c.category_id = cat.id
    JOIN locations l ON c.location_id = l.id
    WHERE c.status NOT IN ('Resolved', 'Rejected')
    ORDER BY c.priority_score DESC
")->fetchAll();

include 'includes/header.php';
include 'includes/student_sidebar.php';
?>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 2rem;">Good Morning, <?php echo htmlspecialchars($_SESSION['name']); ?> 👋</h1>
            <p style="color: var(--text-muted);">Here is the status of your campus reports.</p>
        </div>
        <a href="report_problem.php" class="btn btn-primary">➕ Report New Problem</a>
    </div>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <div class="glass-panel" style="padding: 1.5rem; text-align: center;">
            <h2 style="font-size: 2.5rem; color: var(--primary);"><?php echo $my_stats['total'] ?? 0; ?></h2>
            <p style="color: var(--text-muted); font-weight: 500;">Total Reports</p>
        </div>
        <div class="glass-panel" style="padding: 1.5rem; text-align: center;">
            <h2 style="font-size: 2.5rem; color: var(--accent);"><?php echo $my_stats['active'] ?? 0; ?></h2>
            <p style="color: var(--text-muted); font-weight: 500;">Active / Pending</p>
        </div>
        <div class="glass-panel" style="padding: 1.5rem; text-align: center;">
            <h2 style="font-size: 2.5rem; color: var(--success);"><?php echo $my_stats['resolved'] ?? 0; ?></h2>
            <p style="color: var(--text-muted); font-weight: 500;">Resolved</p>
        </div>
    </div>

    <h2 style="margin-bottom: 1.5rem;">My Personal Reports</h2>
    
    <?php if(empty($my_complaints)): ?>
        <div class="glass-panel" style="padding: 3rem; text-align: center; margin-bottom: 3rem;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">🌱</div>
            <h3>Your campus is looking good!</h3>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">You haven't reported any problems yet.</p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
            <?php foreach($my_complaints as $comp): ?>
                <div class="glass-panel" style="padding: 1.5rem; display: flex; flex-direction: column;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                        <span class="badge badge-<?php echo strtolower($comp['status'] === 'Resolved' ? 'resolved' : 'medium'); ?>">
                            <?php echo $comp['status']; ?>
                        </span>
                        <span style="color: var(--text-muted); font-size: 0.875rem;">
                            <?php echo date('M d, Y', strtotime($comp['created_at'])); ?>
                        </span>
                    </div>
                    <h3 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($comp['title']); ?></h3>
                    <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem; flex-grow: 1;">
                        📍 <?php echo htmlspecialchars($comp['location_name']); ?> | 📂 <?php echo htmlspecialchars($comp['category_name']); ?>
                    </p>
                    <a href="complaint_details.php?id=<?php echo $comp['id']; ?>" class="btn btn-outline" style="width: 100%; text-align: center;">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h2 style="margin-bottom: 1.5rem; color: var(--primary);">🌍 Active Campus Problems (Support Others)</h2>
    <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Click "View & Vote" on any existing issue to add your support instead of creating a duplicate report.</p>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
        <?php foreach($all_active as $ac): ?>
            <div class="glass-panel" style="padding: 1.5rem; display: flex; flex-direction: column; border-left: 4px solid var(--accent);">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span class="badge badge-<?php echo strtolower($ac['severity']); ?>">🔥 <?php echo $ac['severity']; ?></span>
                    <span style="font-size: 0.875rem; color: var(--text-muted);">👍 <?php echo $ac['vote_count']; ?> Votes</span>
                </div>
                <h3 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($ac['title']); ?></h3>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem; flex-grow: 1;">
                    📍 <?php echo htmlspecialchars($ac['location_name']); ?>
                </p>
                <a href="complaint_details.php?id=<?php echo $ac['id']; ?>" class="btn btn-primary" style="width: 100%; text-align: center;">View & Vote</a>
            </div>
        <?php endforeach; ?>
        <?php if(empty($all_active)): ?>
            <p style="color: var(--text-muted);">No active campus problems at the moment.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>