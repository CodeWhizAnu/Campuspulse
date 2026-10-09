<?php
// admin_students.php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/auth.php';

require_admin();
$page_title = "Manage Students - " . APP_NAME;

// Handle Search
$search = $_GET['search'] ?? '';
$query = "
    SELECT u.name, u.email, u.created_at, s.prn, s.course, s.academic_year, s.division, s.phone 
    FROM users u
    JOIN students s ON u.id = s.user_id
    WHERE u.role = 'student'
";
$params = [];

if ($search) {
    $query .= " AND (u.name LIKE ? OR s.prn LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/admin_sidebar.php';
?>
<style>
    .table-container { overflow-x: auto; background: var(--card-bg); border-radius: 12px; border: 1px solid var(--border-color); }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { padding: 1rem; background: rgba(108, 99, 255, 0.05); color: var(--text-muted); font-weight: 600; border-bottom: 2px solid var(--border-color); }
    td { padding: 1rem; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
    tr:hover { background: rgba(0,0,0,0.01); }
</style>

<div class="main-content">
    <h1 style="margin-bottom: 1.5rem;">Registered Students 🎓</h1>

    <form method="GET" action="" class="glass-panel" style="padding: 1.5rem; margin-bottom: 2rem; display: flex; gap: 1rem;">
        <input type="text" name="search" class="form-control" placeholder="Search by Name, PRN, or Email..." value="<?php echo htmlspecialchars($search); ?>" style="flex-grow: 1;">
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="admin_students.php" class="btn btn-outline">Clear</a>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Student Name & Email</th>
                    <th>PRN</th>
                    <th>Course & Division</th>
                    <th>Phone</th>
                    <th>Registered On</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($students as $s): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($s['name']); ?></div>
                            <div style="font-size: 0.875rem; color: var(--text-muted);"><?php echo htmlspecialchars($s['email']); ?></div>
                        </td>
                        <td style="font-weight: bold; color: var(--primary);"><?php echo htmlspecialchars($s['prn']); ?></td>
                        <td>
                            <div><?php echo htmlspecialchars($s['course']); ?></div>
                            <div style="font-size: 0.875rem; color: var(--text-muted);">
                                <?php echo htmlspecialchars($s['academic_year']); ?> - Div <?php echo htmlspecialchars($s['division']); ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($s['phone'] ?: 'N/A'); ?></td>
                        <td style="font-size: 0.875rem; color: var(--text-muted);">
                            <?php echo date('M d, Y', strtotime($s['created_at'])); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if(empty($students)): ?>
                    <tr><td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">No students found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>