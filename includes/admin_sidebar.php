<?php
// includes/admin_sidebar.php
?>
<style>
.sidebar {
    width: 250px;
    background: var(--card-bg);
    border-right: 1px solid var(--border-color);
    height: 100vh;
    position: fixed;
    top: 0; left: 0;
    padding: 2rem 1rem;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}
.sidebar-logo {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--primary);
    text-decoration: none;
    margin-bottom: 2rem;
    padding: 0 1rem;
}
.sidebar-menu {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 1rem;
    color: var(--text-main);
    border-radius: 8px;
    font-weight: 500;
    transition: 0.2s;
}
.sidebar-menu a:hover, .sidebar-menu a.active {
    background: rgba(108, 99, 255, 0.1);
    color: var(--primary);
}
.sidebar-heading {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-muted);
    margin: 1.5rem 0 0.5rem 1rem;
    font-weight: 700;
}
.main-content {
    margin-left: 250px;
    padding: 2rem;
    min-height: 100vh;
}
@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); z-index: 2000; }
    .main-content { margin-left: 0; }
}
</style>

<aside class="sidebar">
    <a href="<?php echo BASE_URL; ?>" class="sidebar-logo">🔥 <?php echo APP_NAME; ?> Admin</a>
    
    <ul class="sidebar-menu">
        <li class="sidebar-heading">Overview</li>
        <li><a href="admin_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">📊 Dashboard</a></li>
        <li><a href="admin_heatmap.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_heatmap.php' ? 'active' : ''; ?>">🗺️ Problem Heatmap</a></li>
        <li><a href="admin_analytics.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_analytics.php' ? 'active' : ''; ?>">📈 Analytics</a></li>
        
        <li class="sidebar-heading">Management</li>
        <li><a href="admin_complaints.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_complaints.php' ? 'active' : ''; ?>">📋 All Complaints</a></li>
        <li><a href="admin_students.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_students.php' ? 'active' : ''; ?>">🎓 Students</a></li>
        
        <li style="margin-top: 2rem;"><a href="logout.php" style="color: var(--danger);">🚪 Logout</a></li>
    </ul>
</aside>