<?php
// report_problem.php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/auth.php';

require_student();
$page_title = "Report a Problem - " . APP_NAME;

// Fetch Categories and Locations
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$locations = $pdo->query("SELECT * FROM locations ORDER BY name ASC")->fetchAll();

include 'includes/header.php';
include 'includes/student_sidebar.php';
?>

<div class="main-content">
    <div style="max-width: 800px; margin: 0 auto;">
        <h1 style="margin-bottom: 0.5rem;">Report a Campus Problem 🚨</h1>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Your report will be analyzed by our smart priority system.</p>

        <?php if(isset($_SESSION['error'])): ?>
            <div style="padding: 1rem; background: rgba(255, 77, 109, 0.1); color: var(--danger); border-radius: 8px; margin-bottom: 1.5rem;">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="actions/complaint_action.php" method="POST" enctype="multipart/form-data" class="glass-panel" style="padding: 2rem;">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label>Problem Title *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g., Water cooler not working" required maxlength="150">
            </div>

            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Provide details about the issue..." required></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Location *</label>
                    <select name="location_id" class="form-control" required>
                        <option value="">Select Location</option>
                        <?php foreach($locations as $loc): ?>
                            <option value="<?php echo $loc['id']; ?>"><?php echo htmlspecialchars($loc['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label>Severity *</label>
                    <select name="severity" class="form-control" required>
                        <option value="Low">🟢 Low (Minor inconvenience)</option>
                        <option value="Medium">🟡 Medium (Needs attention soon)</option>
                        <option value="High">🟠 High (Impacting daily activities)</option>
                        <option value="Critical">🔴 Critical (Safety hazard / Complete failure)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Upload Photo (Optional)</label>
                    <input type="file" name="complaint_photo" class="form-control" accept="image/jpeg, image/png, image/webp">
                </div>
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1rem;">
                <input type="checkbox" name="is_anonymous" id="is_anonymous" value="1" checked style="width: 1.2rem; height: 1.2rem;">
                <label for="is_anonymous" style="margin: 0; font-weight: normal;">Post this complaint anonymously (Identity hidden from public)</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem; font-size: 1.1rem;">Submit Problem</button>
        </form>
    </div>
</div>
</body>
</html>