<?php
// duplicate_warning.php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/auth.php';

require_student();

if (!isset($_GET['similar_id']) || !isset($_SESSION['temp_complaint'])) {
    header("Location: " . BASE_URL . "report_problem.php");
    exit();
}

$similar_id = (int)$_GET['similar_id'];

// Fetch the existing similar complaint
$stmt = $pdo->prepare("
    SELECT c.*, l.name as location_name, 
           (SELECT COUNT(*) FROM complaint_votes WHERE complaint_id = c.id) as votes
    FROM complaints c
    JOIN locations l ON c.location_id = l.id
    WHERE c.id = ?
");
$stmt->execute([$similar_id]);
$similar = $stmt->fetch();

$page_title = "Similar Problem Found - " . APP_NAME;
include 'includes/header.php';
?>
<style>
    .warning-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        background: var(--bg-color);
    }
    .warning-card {
        max-width: 600px;
        width: 100%;
        padding: 2.5rem;
        text-align: center;
    }
    .existing-issue {
        background: rgba(108, 99, 255, 0.05);
        border: 1px solid var(--primary);
        border-radius: 12px;
        padding: 1.5rem;
        text-align: left;
        margin: 2rem 0;
    }
</style>

<div class="warning-container">
    <div class="warning-card glass-panel">
        <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
        <h1 style="margin-bottom: 0.5rem;">Wait! We found a similar problem.</h1>
        <p style="color: var(--text-muted);">Someone else has already reported an issue that looks very similar to yours. By supporting their report instead, you help increase its Smart Priority Score faster.</p>

        <div class="existing-issue">
            <h3 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($similar['title']); ?></h3>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">📍 <?php echo htmlspecialchars($similar['location_name']); ?></p>
            <p style="margin-bottom: 1rem;"><?php echo htmlspecialchars($similar['description']); ?></p>
            <span class="badge badge-high">👍 <?php echo $similar['votes']; ?> students are facing this</span>
            <span class="badge badge-medium">Status: <?php echo $similar['status']; ?></span>
        </div>

        <div style="display: flex; gap: 1rem; flex-direction: column;">
            <!-- Support existing issue -->
            <form action="actions/vote_action.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="complaint_id" value="<?php echo $similar['id']; ?>">
                <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.1rem;">
                    👍 I'm Facing This Too (Support Existing)
                </button>
            </form>

            <!-- Force submit new issue -->
            <form action="actions/complaint_action.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <!-- Re-populate fields from session implicitly, but we just pass a force flag to let the backend re-use temp data -->
                <?php foreach($_SESSION['temp_complaint'] as $key => $val): ?>
                    <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($val); ?>">
                <?php endforeach; ?>
                <input type="hidden" name="force_submit" value="1">
                <button type="submit" class="btn btn-outline" style="width: 100%;">
                    No, my issue is different (Submit as New)
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>