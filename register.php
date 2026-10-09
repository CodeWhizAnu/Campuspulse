<?php
// register.php
require_once 'config/config.php';
require_once 'config/auth.php';
redirect_if_logged_in();

$role = isset($_GET['role']) && $_GET['role'] === 'admin' ? 'admin' : 'student';
$page_title = ucfirst($role) . " Registration - " . APP_NAME;
include 'includes/header.php';
?>
<style>
    .auth-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4rem 2rem;
        /* Add the campus image behind the register card */
        background: url('assets/images/campus-hero.jpg') center/cover fixed;
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
        max-width: <?php echo $role === 'student' ? '700px' : '500px'; ?>;
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
    
    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
    
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
    
    @media (max-width: 600px) {
        .grid-2 { grid-template-columns: 1fr; }
    }
</style>

<div class="auth-container">
    <div class="auth-card glass-panel">
        <a href="login.php?role=<?php echo $role; ?>" style="color: var(--text-muted); display: inline-block; margin-bottom: 1rem;">&larr; Back to Login</a>
        
        <h1 style="color: var(--primary); margin-bottom: 1.5rem; text-align: center;">
            <?php echo $role === 'admin' ? '🛡️ Admin Registration' : '🎓 Student Registration'; ?>
        </h1>

        <?php if(isset($_SESSION['error'])): ?>
            <div style="padding: 1rem; background: rgba(255, 77, 109, 0.1); color: var(--danger); border-radius: 8px; margin-bottom: 1.5rem;">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="actions/register_action.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="role" value="<?php echo $role; ?>">

            <?php if($role === 'student'): ?>
                <!-- Student Form Fields -->
                <div class="grid-2">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>PRN (Permanent Registration Number) *</label>
                        <input type="text" name="prn" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Course *</label>
                        <input type="text" name="course" class="form-control" placeholder="e.g., B.Tech Computer Engineering" required>
                    </div>
                    <div class="form-group">
                        <label>Academic Year *</label>
                        <select name="academic_year" class="form-control" required>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Division *</label>
                        <input type="text" name="division" class="form-control" placeholder="e.g., A" required>
                    </div>
                    <div class="form-group">
                        <label>Profile Photo</label>
                        <input type="file" name="profile_photo" class="form-control" accept="image/jpeg, image/png, image/webp">
                    </div>
                    <div class="form-group">
                        <label>Password *</label>
                        <div class="password-wrapper">
                            <input type="password" id="student_pass" name="password" class="form-control" required style="padding-right: 2.5rem;">
                            <button type="button" class="toggle-password" onclick="togglePassword('student_pass')">👁️️</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password *</label>
                        <div class="password-wrapper">
                            <input type="password" id="student_conf" name="confirm_password" class="form-control" required style="padding-right: 2.5rem;">
                            <button type="button" class="toggle-password" onclick="togglePassword('student_conf')">👁️</button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Admin Form Fields -->
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Employee ID *</label>
                    <input type="text" name="employee_id" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Admin Registration Key *</label>
                    <input type="password" name="admin_key" class="form-control" placeholder="Provided by IT department" required>
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <div class="password-wrapper">
                        <input type="password" id="admin_pass" name="password" class="form-control" required style="padding-right: 2.5rem;">
                        <button type="button" class="toggle-password" onclick="togglePassword('admin_pass')">👁️</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Confirm Password *</label>
                    <div class="password-wrapper">
                        <input type="password" id="admin_conf" name="confirm_password" class="form-control" required style="padding-right: 2.5rem;">
                        <button type="button" class="toggle-password" onclick="togglePassword('admin_conf')">👁️</button>
                    </div>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Create Account</button>
        </form>
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