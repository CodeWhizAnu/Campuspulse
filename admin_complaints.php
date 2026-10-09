<?php
// admin_complaints.php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/auth.php';

require_admin();
$page_title = "Manage Complaints - " . APP_NAME;

// Handle Search and Filters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$severity_filter = $_GET['severity'] ?? '';

$query = "
    SELECT c.*, cat.name as category_name, l.name as location_name 
    FROM complaints c
    JOIN categories cat ON c.category_id = cat.id
    JOIN locations l ON c.location_id = l.id
    WHERE 1=1
";
$params = [];

if ($search) {
    $query .= " AND (c.title LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($status_filter) {
    $query .= " AND c.status = ?";
    $params[] = $status_filter;
}
if ($severity_filter) {
    $query .= " AND c.severity = ?";
    $params[] = $severity_filter;
}

$query .= " ORDER BY c.priority_score DESC, c.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$complaints = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/admin_sidebar.php';
?>
<style>
    .filter-form { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
    .table-container { overflow-x: auto; background: var(--card-bg); border-radius: 12px; border: 1px solid var(--border-color); }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { padding: 1rem; background: rgba(108, 99, 255, 0.05); color: var(--text-muted); font-weight: 600; border-bottom: 2px solid var(--border-color); }
    td { padding: 1rem; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
    tr:hover { background: rgba(0,0,0,0.01); }
</style>

<div class="main-content">
    <h1 style="margin-bottom: 1.5rem;">Manage Complaints 📋</h1>

    <form method="GET" action="" class="filter-form glass-panel" style="padding: 1.5rem;">
        <input type="text" name="search" class="form-control" placeholder="Search title or description..." value="<?php echo htmlspecialchars($search); ?>" style="flex-grow: 1; min-width: 250px;">
        
        <select name="status" class="form-control" style="width: 200px;">
            <option value="">All Statuses</option>
            <option value="Pending" <?php if($status_filter == 'Pending') echo 'selected'; ?>>Pending</option>
            <option value="Under Review" <?php if($status_filter == 'Under Review') echo 'selected'; ?>>Under Review</option>
            <option value="In Progress" <?php if($status_filter == 'In Progress') echo 'selected'; ?>>In Progress</option>
            <option value="Resolved" <?php if($status_filter == 'Resolved') echo 'selected'; ?>>Resolved</option>
        </select>
        
        <select name="severity" class="form-control" style="width: 200px;">
            <option value="">All Severities</option>
            <option value="Critical" <?php if($severity_filter == 'Critical') echo 'selected'; ?>>Critical</option>
            <option value="High" <?php if($severity_filter == 'High') echo 'selected'; ?>>High</option>
            <option value="Medium" <?php if($severity_filter == 'Medium') echo 'selected'; ?>>Medium</option>
            <option value="Low" <?php if($severity_filter == 'Low') echo 'selected'; ?>>Low</option>
        </select>
        
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="admin_complaints.php" class="btn btn-outline">Reset</a>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Problem Details</th>
                    <th>Location & Category</th>
                    <th>Score / Severity</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($complaints as $c): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($c['title']); ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo date('M d, Y', strtotime($c['created_at'])); ?></div>
                        </td>
                        <td>
                            <div>📍 <?php echo htmlspecialchars($c['location_name']); ?></div>
                            <div style="font-size: 0.875rem; color: var(--text-muted);">📂 <?php echo htmlspecialchars($c['category_name']); ?></div>
                        </td>
                        <td>
                            <div style="font-weight: bold; color: var(--primary); font-size: 1.1rem;"><?php echo $c['priority_score']; ?> <span style="font-size: 0.75rem; color: var(--text-muted);">/100</span></div>
                            <div class="badge badge-<?php echo strtolower($c['severity']); ?>" style="margin-top: 0.25rem; font-size: 0.7rem;"><?php echo $c['severity']; ?></div>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo strtolower($c['status'] === 'Resolved' ? 'resolved' : 'medium'); ?>"><?php echo $c['status']; ?></span>
                        </td>
                        <td>
                            <a href="admin_complaint_details.php?id=<?php echo $c['id']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Manage</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if(empty($complaints)): ?>
                    <tr><td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">No complaints match your criteria.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>