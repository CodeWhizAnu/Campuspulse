<?php
// includes/student_sidebar.php
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
    gap: 0.5rem;
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
    <a href="<?php echo BASE_URL; ?>" class="sidebar-logo">🔥 <?php echo APP_NAME; ?></a>
    <ul class="sidebar-menu">
        <li><a href="student_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'student_dashboard.php' ? 'active' : ''; ?>">📊 Dashboard</a></li>
        <li><a href="report_problem.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'report_problem.php' ? 'active' : ''; ?>">➕ Report Problem</a></li>
        <li><a href="student_profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'student_profile.php' ? 'active' : ''; ?>">👤 My Profile</a></li>
        <li style="margin-top: auto;"><a href="logout.php" style="color: var(--danger);">🚪 Logout</a></li>
    </ul>
</aside>