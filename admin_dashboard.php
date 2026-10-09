<?php
// admin_dashboard.php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/auth.php';

require_admin();
$page_title = "Admin Dashboard - " . APP_NAME;

// Fetch high-level statistics
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_reports,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved,
        SUM(CASE WHEN severity = 'Critical' AND status != 'Resolved' THEN 1 ELSE 0 END) as critical_active
    FROM complaints
")->fetch();

// Fetch 5 most recent complaints
$recent_complaints = $pdo->query("
    SELECT c.*, cat.name as category_name, l.name as location_name 
    FROM complaints c
    JOIN categories cat ON c.category_id = cat.id
    JOIN locations l ON c.location_id = l.id
    ORDER BY c.created_at DESC LIMIT 5
")->fetchAll();

// Fetch Top 3 Critical Issues based on Priority Score
$urgent_issues = $pdo->query("
    SELECT c.*, l.name as location_name 
    FROM complaints c
    JOIN locations l ON c.location_id = l.id
    WHERE c.status != 'Resolved' AND c.status != 'Rejected'
    ORDER BY c.priority_score DESC LIMIT 3
")->fetchAll();

include 'includes/header.php';
include 'includes/admin_sidebar.php';
?>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 2rem;">Campus Overview 🏫</h1>
            <p style="color: var(--text-muted);">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>.</p>
        </div>
        <a href="admin_heatmap.php" class="btn btn-primary">View Heatmap</a>
    </div>

    <!-- KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="glass-panel" style="padding: 1.5rem; border-left: 4px solid var(--primary);">
            <p style="color: var(--text-muted); font-size: 0.875rem; font-weight: 600;">Total Reports</p>
            <h2 style="font-size: 2rem; margin-top: 0.5rem;"><?php echo $stats['total_reports']; ?></h2>
        </div>
        <div class="glass-panel" style="padding: 1.5rem; border-left: 4px solid var(--accent);">
            <p style="color: var(--text-muted); font-size: 0.875rem; font-weight: 600;">Pending Review</p>
            <h2 style="font-size: 2rem; margin-top: 0.5rem;"><?php echo $stats['pending']; ?></h2>
        </div>
        <div class="glass-panel" style="padding: 1.5rem; border-left: 4px solid var(--warning);">
            <p style="color: var(--text-muted); font-size: 0.875rem; font-weight: 600;">In Progress</p>
            <h2 style="font-size: 2rem; margin-top: 0.5rem;"><?php echo $stats['in_progress']; ?></h2>
        </div>
        <div class="glass-panel" style="padding: 1.5rem; border-left: 4px solid var(--success);">
            <p style="color: var(--text-muted); font-size: 0.875rem; font-weight: 600;">Resolved</p>
            <h2 style="font-size: 2rem; margin-top: 0.5rem;"><?php echo $stats['resolved']; ?></h2>
        </div>
        <div class="glass-panel" style="padding: 1.5rem; border-left: 4px solid var(--danger); background: rgba(255, 77, 109, 0.05);">
            <p style="color: var(--danger); font-size: 0.875rem; font-weight: 600;">Critical Active</p>
            <h2 style="font-size: 2rem; margin-top: 0.5rem; color: var(--danger);"><?php echo $stats['critical_active']; ?></h2>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        
        <!-- Recent Activity Table -->
        <div class="glass-panel" style="padding: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3>Recent Reports</h3>
                <a href="admin_complaints.php" style="font-size: 0.875rem; font-weight: 600;">View All &rarr;</a>
            </div>
            
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-muted);">
                            <th style="padding: 0.75rem;">Problem</th>
                            <th style="padding: 0.75rem;">Location</th>
                            <th style="padding: 0.75rem;">Status</th>
                            <th style="padding: 0.75rem;">Priority</th>
                            <th style="padding: 0.75rem;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_complaints as $comp): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 1rem 0.75rem;">
                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($comp['title']); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($comp['category_name']); ?></div>
                                </td>
                                <td style="padding: 1rem 0.75rem;"><?php echo htmlspecialchars($comp['location_name']); ?></td>
                                <td style="padding: 1rem 0.75rem;">
                                    <span class="badge badge-<?php echo strtolower($comp['status'] === 'Resolved' ? 'resolved' : 'medium'); ?>">
                                        <?php echo $comp['status']; ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem 0.75rem; font-weight: 600; color: var(--primary);">
                                    <?php echo $comp['priority_score']; ?>
                                </td>
                                <td style="padding: 1rem 0.75rem;">
                                    <a href="admin_complaint_details.php?id=<?php echo $comp['id']; ?>" class="btn btn-outline" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;">Review</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if(empty($recent_complaints)): ?>
                            <tr><td colspan="5" style="padding: 1rem; text-align: center; color: var(--text-muted);">No complaints reported yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Priorities -->
        <div class="glass-panel" style="padding: 1.5rem;">
            <h3 style="margin-bottom: 1.5rem; color: var(--danger);">🔥 Requires Attention</h3>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach($urgent_issues as $urgent): ?>
                    <div style="background: var(--bg-color); border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span class="badge badge-high">Score: <?php echo $urgent['priority_score']; ?></span>
                            <span style="font-size: 0.75rem; color: var(--text-muted);"><?php echo date('M d', strtotime($urgent['created_at'])); ?></span>
                        </div>
                        <h4 style="margin-bottom: 0.25rem;"><?php echo htmlspecialchars($urgent['title']); ?></h4>
                        <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.75rem;">📍 <?php echo htmlspecialchars($urgent['location_name']); ?></p>
                        <a href="admin_complaint_details.php?id=<?php echo $urgent['id']; ?>" style="font-size: 0.875rem; font-weight: 600;">Resolve Issue &rarr;</a>
                    </div>
                <?php endforeach; ?>
                <?php if(empty($urgent_issues)): ?>
                    <p style="text-align: center; color: var(--text-muted);">No urgent issues right now. Great job!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>