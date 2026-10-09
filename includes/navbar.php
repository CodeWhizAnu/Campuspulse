<?php
// includes/navbar.php

// Fetch the student's profile photo if logged in
$profile_img = 'default.png';
if (is_logged_in() && is_student()) {
    if (isset($pdo)) {
        $stmt = $pdo->prepare("SELECT profile_photo FROM students WHERE id = ?");
        $stmt->execute([$_SESSION['student_id']]);
        $res = $stmt->fetch();
        if ($res && $res['profile_photo']) {
            $profile_img = $res['profile_photo'];
        }
    }
}

// Extract just the First Name
$first_name = '';
if (is_logged_in()) {
    $first_name = explode(' ', trim($_SESSION['name']))[0];
}
?>
<style>
/* Navbar Specific Styles */
.navbar {
    position: fixed;
    top: 0; width: 100%;
    padding: 1rem 2rem;
    z-index: 1000;
    transition: 0.3s;
    background: transparent;
}
.navbar.scrolled {
    background: var(--glass-bg);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--glass-border);
    box-shadow: var(--shadow-sm);
}
.nav-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.logo {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--primary);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.nav-links {
    display: flex;
    gap: 2rem;
    align-items: center;
}
.nav-links a {
    color: var(--text-main);
    font-weight: 500;
}
.nav-links a:hover { color: var(--primary); }

/* New Profile Pill Button */
.nav-profile-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(108, 99, 255, 0.1);
    padding: 0.25rem 1rem 0.25rem 0.25rem;
    border-radius: 50px;
    text-decoration: none;
    color: var(--text-main) !important;
    font-weight: 600;
    transition: all 0.2s ease;
    border: 1px solid rgba(108, 99, 255, 0.2);
}
.nav-profile-btn:hover {
    background: rgba(108, 99, 255, 0.2);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 99, 255, 0.15);
}
.nav-profile-img {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--primary);
    background: var(--card-bg);
}
</style>

<nav class="navbar" id="navbar">
    <div class="nav-container">
        <a href="<?php echo BASE_URL; ?>" class="logo">🔥 <?php echo APP_NAME; ?></a>
        
        <div class="nav-links">
            <a href="<?php echo BASE_URL; ?>#home">Home</a>
            <a href="<?php echo BASE_URL; ?>#problems">Problems</a>
            <a href="<?php echo BASE_URL; ?>#how-it-works">How It Works</a>
            <a href="<?php echo BASE_URL; ?>#resolved">Resolved</a>
            
            <?php if(is_logged_in()): ?>
                <?php if(is_student()): ?>
                    <!-- Student Profile Pill (Replaces Dashboard Button) -->
                    <a href="<?php echo BASE_URL; ?>student_dashboard.php" class="nav-profile-btn">
                        <img src="<?php echo BASE_URL; ?>uploads/profile/<?php echo htmlspecialchars($profile_img); ?>" alt="Profile" class="nav-profile-img">
                        <?php echo htmlspecialchars($first_name); ?>
                    </a>
                <?php else: ?>
                    <!-- Admin Profile Pill (Since admins don't have photos in the schema) -->
                    <a href="<?php echo BASE_URL; ?>admin_dashboard.php" class="nav-profile-btn">
                        <div class="nav-profile-img" style="display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">🛡️</div>
                        Admin <?php echo htmlspecialchars($first_name); ?>
                    </a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-outline" style="padding: 0.5rem 1rem;">Logout</a>
            <?php else: ?>
                <button onclick="openModal('authModal')" class="btn btn-outline">Login / Register</button>
            <?php endif; ?>
            
            <button id="themeToggle" style="background:none;border:none;font-size:1.2rem;cursor:pointer;">🌙</button>
        </div>
    </div>
</nav>