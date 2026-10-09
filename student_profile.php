<?php
// student_profile.php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/auth.php';

require_student();

$user_id = $_SESSION['user_id'];
$student_id = $_SESSION['student_id'];

// Fetch current data
$stmt = $pdo->prepare("
    SELECT u.name, u.email, s.prn, s.course, s.academic_year, s.division, s.phone, s.profile_photo 
    FROM users u
    JOIN students s ON u.id = s.user_id
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

$page_title = "My Profile - " . APP_NAME;
include 'includes/header.php';
include 'includes/student_sidebar.php';
?>

<div class="main-content">
    <h1 style="margin-bottom: 2rem;">My Profile 👤</h1>

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

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
        
        <!-- Left Column: Photo & Static Info -->
        <div class="glass-panel" style="padding: 2rem; text-align: center; height: fit-content;">
            <div style="width: 150px; height: 150px; margin: 0 auto 1.5rem auto; border-radius: 50%; overflow: hidden; border: 4px solid var(--border-color);">
                <img src="<?php echo BASE_URL; ?>uploads/profile/<?php echo htmlspecialchars($profile['profile_photo']); ?>" 
                     alt="Profile Photo" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <h2 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($profile['name']); ?></h2>
            <p style="color: var(--text-muted); font-weight: 600; margin-bottom: 1rem;">PRN: <?php echo htmlspecialchars($profile['prn']); ?></p>
            
            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 1.5rem 0;">
            
            <form action="actions/profile_action.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action_type" value="update_photo">
                
                <div class="form-group" style="text-align: left;">
                    <label style="font-size: 0.875rem;">Update Profile Photo</label>
                    <input type="file" name="profile_photo" class="form-control" style="font-size: 0.875rem; padding: 0.5rem;" accept="image/jpeg, image/png, image/webp" required>
                </div>
                <button type="submit" class="btn btn-outline" style="width: 100%; padding: 0.5rem;">Upload Photo</button>
            </form>
        </div>

        <!-- Right Column: Editable Details & Password -->
        <div>
            <!-- Personal Details Form -->
            <form action="actions/profile_action.php" method="POST" class="glass-panel" style="padding: 2rem; margin-bottom: 2rem;">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action_type" value="update_details">
                
                <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Academic Details</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($profile['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email (Read Only)</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($profile['email']); ?>" readonly style="background: var(--bg-color); opacity: 0.7;">
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($profile['phone']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Course</label>
                        <input type="text" name="course" class="form-control" value="<?php echo htmlspecialchars($profile['course']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Academic Year</label>
                        <select name="academic_year" class="form-control" required>
                            <?php 
                            $years = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
                            foreach($years as $y) {
                                $sel = ($profile['academic_year'] == $y) ? 'selected' : '';
                                echo "<option value=\"$y\" $sel>$y</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Division</label>
                        <input type="text" name="division" class="form-control" value="<?php echo htmlspecialchars($profile['division']); ?>" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Save Changes</button>
            </form>

            <!-- Change Password Form -->
            <form action="actions/profile_action.php" method="POST" class="glass-panel" style="padding: 2rem;">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action_type" value="update_password">
                
                <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Security</h3>
                
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-outline" style="border-color: var(--primary);">Update Password</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>