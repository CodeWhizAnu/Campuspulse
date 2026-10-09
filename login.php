<?php
// login.php
require_once 'config/config.php';
require_once 'config/auth.php';
redirect_if_logged_in();

$role = isset($_GET['role']) && $_GET['role'] === 'admin' ? 'admin' : 'student';
$page_title = ucfirst($role) . " Login - " . APP_NAME;
include 'includes/header.php';
?>
<style>
    .auth-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        /* Add the campus image behind the login card */
        background: url('assets/images/campus-hero.jpg') center/cover no-repeat;
        position: relative;
    }
    
    /* Dark overlay to make the glass card pop */
    .auth-container::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(17, 24, 39, 0.4); 
    }

    .auth-card {
        width: 100%;
        max-width: 450px;
        padding: 2.5rem;
        position: relative;
        z-index: 10;
        /* Enhance the glass effect */
        background: var(--glass-bg);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid var(--glass-border);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }
    
    .auth-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    .auth-header h1 {
        font-size: 1.75rem;
        color: var(--primary);
        margin-bottom: 0.5rem;
    }
    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        font-weight: 500;
        font-size: 0.875rem;
    }
    .alert-danger { background: rgba(255, 77, 109, 0.1); color: var(--danger); }
    .alert-success { background: rgba(34, 197, 94, 0.1); color: var(--success); }
    
    /* Eye Button CSS */
    .password-wrapper { position: relative; }
    .toggle-password {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: var(--text-muted);
        background: none;
        border: none;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .toggle-password:hover { color: var(--primary); }
</style>

<div class="auth-container">
    <div class="auth-card glass-panel">
        <a href="index.php" style="color: var(--text-muted); display: inline-block; margin-bottom: 1rem;">&larr; Back to Home</a>
        
        <div class="auth-header">
            <h1><?php echo $role === 'admin' ? '🛡️ Admin Login' : '🎓 Student Login'; ?></h1>
            <p style="color: var(--text-muted);">Welcome back! Please enter your details.</p>
        </div>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form action="actions/login_action.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="role" value="<?php echo $role; ?>">

            <div class="form-group">
                <label for="email"><?php echo $role === 'student' ? 'Email or PRN' : 'Email Address'; ?></label>
                <input type="text" id="email" name="email" class="form-control" required placeholder="<?php echo $role === 'student' ? 'Enter your email or PRN' : 'admin@campuspulse.edu'; ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••" style="padding-right: 2.5rem;">
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">👁️</button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Login</button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem;">
            <p style="color: var(--text-muted); font-size: 0.875rem;">
                Don't have an account? 
                <a href="register.php?role=<?php echo $role; ?>" style="font-weight: 600;">Register here</a>
            </p>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
    } else {
        input.type = "password";
    }
}
</script>
</body>
</html>