<?php
// includes/footer.php
?>
<footer style="background: var(--card-bg); padding: 4rem 2rem; margin-top: 4rem; border-top: 1px solid var(--border-color);">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h3 style="color: var(--primary); font-size: 1.5rem; margin-bottom: 1rem;">🔥 <?php echo APP_NAME; ?></h3>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Built for better campuses. Developed for <?php echo COLLEGE_NAME; ?>.</p>
        <p style="color: var(--text-muted); font-size: 0.875rem;">&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved By Anushka Deshmukh</p>
    </div>
</footer>

<!-- Auth Modal (Handles routing to login/register pages) -->
<div class="modal-overlay" id="authModal">
    <div class="modal-content glass-panel">
        <button class="modal-close" onclick="closeModal('authModal')">&times;</button>
        <h2 style="text-align: center; margin-bottom: 1rem;">Welcome to <?php echo APP_NAME; ?> 👋</h2>
        <p style="text-align: center; color: var(--text-muted);">Choose your account type to continue</p>
        
        <div class="role-cards">
            <div class="role-card" onclick="window.location.href='<?php echo BASE_URL; ?>login.php?role=student'">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🎓</div>
                <h3>Student Portal</h3>
                <p style="color: var(--text-muted); font-size: 0.875rem;">Report problems and support issues.</p>
            </div>
            <div class="role-card" onclick="window.location.href='<?php echo BASE_URL; ?>login.php?role=admin'">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🛡️</div>
                <h3>Administration</h3>
                <p style="color: var(--text-muted); font-size: 0.875rem;">Manage campus operations.</p>
            </div>
        </div>
    </div>
</div>

<!-- Core JavaScript -->
<script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
</body>
</html>