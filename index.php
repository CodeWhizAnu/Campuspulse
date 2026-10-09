<?php
// index.php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/auth.php';

// Fetch public statistics for the hero section
$stats_query = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM complaints) as total_reports,
    (SELECT COUNT(*) FROM complaints WHERE status = 'Resolved') as resolved,
    (SELECT COUNT(*) FROM locations) as areas
");
$stats = $stats_query->fetch();

// Fetch recent high-priority public complaints to showcase
$public_complaints = $pdo->query("
    SELECT c.*, cat.name as category_name, cat.icon, l.name as location_name,
           (SELECT COUNT(*) FROM complaint_votes WHERE complaint_id = c.id) as vote_count
    FROM complaints c
    JOIN categories cat ON c.category_id = cat.id
    JOIN locations l ON c.location_id = l.id
    WHERE c.status != 'Resolved' AND c.status != 'Rejected'
    ORDER BY c.priority_score DESC 
    LIMIT 3
")->fetchAll();

// Fetch recently resolved problems
$resolved_complaints = $pdo->query("
    SELECT c.*, cat.name as category_name, l.name as location_name,
           (SELECT COUNT(*) FROM complaint_votes WHERE complaint_id = c.id) as vote_count
    FROM complaints c
    JOIN categories cat ON c.category_id = cat.id
    JOIN locations l ON c.location_id = l.id
    WHERE c.status = 'Resolved'
    ORDER BY c.updated_at DESC 
    LIMIT 3
")->fetchAll();

$page_title = "Welcome to " . APP_NAME;
include 'includes/header.php';
include 'includes/navbar.php';
?>

<!-- Hero Section -->
<section id="home" class="hero">
    <div class="hero-content">
        <span class="hero-badge">SMART CAMPUS • REAL STUDENT VOICES</span>
        <h1>Make Your <span class="highlight">Campus Better.</span></h1>
        <p><?php echo APP_NAME; ?> gives students a simple and anonymous way to report campus problems while helping administrators discover what needs attention most.</p>
        
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <?php if(is_student()): ?>
                <a href="report_problem.php" class="btn btn-primary" style="font-size: 1.25rem;">Report a Problem</a>
            <?php else: ?>
                <button onclick="openModal('authModal')" class="btn btn-primary" style="font-size: 1.25rem;">Report a Problem</button>
            <?php endif; ?>
            <a href="#problems" class="btn btn-outline" style="color: white; border-color: white;">Explore Issues</a>
        </div>

        <div class="hero-stats">
            <div class="stat-item">
                <h3><?php echo $stats['total_reports']; ?>+</h3>
                <p>Student Reports</p>
            </div>
            <div class="stat-item">
                <h3><?php echo $stats['resolved']; ?>+</h3>
                <p>Problems Resolved</p>
            </div>
            <div class="stat-item">
                <h3><?php echo $stats['areas']; ?></h3>
                <p>Campus Areas</p>
            </div>
        </div>
    </div>
</section>

<!-- Active Problems Section -->
<section id="problems" class="section">
    <div class="section-header">
        <h2>What Students Are Reporting</h2>
        <p style="color: var(--text-muted);">Urgent issues highlighted by the community priority algorithm.</p>
    </div>
    
    <div class="cards-grid">
        <?php foreach($public_complaints as $comp): ?>
            <div class="complaint-card glass-panel">
                <div class="card-header">
                    <span class="badge badge-<?php echo strtolower($comp['severity']); ?>">
                        🔥 <?php echo $comp['severity']; ?> Priority
                    </span>
                    <span style="color: var(--text-muted); font-size: 0.875rem;">
                        <?php echo date('M d', strtotime($comp['created_at'])); ?>
                    </span>
                </div>
                <h3 class="card-title"><?php echo htmlspecialchars($comp['title']); ?></h3>
                <div class="card-meta">
                    <span>📍 <?php echo htmlspecialchars($comp['location_name']); ?></span>
                    <span>👍 <?php echo $comp['vote_count']; ?> students</span>
                </div>
                <p style="color: var(--text-muted); margin-bottom: 1.5rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    <?php echo htmlspecialchars($comp['description']); ?>
                </p>
                <div style="font-weight: 600; color: var(--text-muted); font-size: 0.875rem;">
                    Status: <span style="color: var(--primary);"><?php echo $comp['status']; ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- How It Works Section -->
<section id="how-it-works" class="section" style="background: var(--card-bg);">
    <div class="section-header">
        <h2>How <?php echo APP_NAME; ?> Works</h2>
        <p style="color: var(--text-muted);">A transparent workflow from reporting to resolution.</p>
    </div>
    
    <div class="steps-grid">
        <div class="step-card glass-panel">
            <div class="step-number">01</div>
            <h3>Report</h3>
            <p style="color: var(--text-muted); margin-top: 0.5rem;">Spot a problem? Tell us what's wrong anonymously.</p>
        </div>
        <div class="step-card glass-panel">
            <div class="step-number">02</div>
            <h3>Support</h3>
            <p style="color: var(--text-muted); margin-top: 0.5rem;">Others click "I'm facing this too" to boost priority.</p>
        </div>
        <div class="step-card glass-panel">
            <div class="step-number">03</div>
            <h3>Prioritize</h3>
            <p style="color: var(--text-muted); margin-top: 0.5rem;">Algorithm calculates Smart Priority for admins.</p>
        </div>
        <div class="step-card glass-panel">
            <div class="step-number">04</div>
            <h3>Resolve</h3>
            <p style="color: var(--text-muted); margin-top: 0.5rem;">Track live progress until the issue is fixed.</p>
        </div>
    </div>
</section>

<!-- Resolved Problems Section -->
<section id="resolved" class="section">
    <div class="section-header">
        <h2>Problems We've Solved 💚</h2>
        <p style="color: var(--text-muted);">See how the administration is actively improving the campus.</p>
    </div>
    
    <div class="cards-grid">
        <?php foreach($resolved_complaints as $comp): ?>
            <div class="complaint-card glass-panel" style="border-top: 4px solid var(--success);">
                <div class="card-header">
                    <span class="badge badge-resolved">
                        ✓ RESOLVED
                    </span>
                    <span style="color: var(--text-muted); font-size: 0.875rem;">
                        <?php echo date('M d', strtotime($comp['updated_at'])); ?>
                    </span>
                </div>
                <h3 class="card-title"><?php echo htmlspecialchars($comp['title']); ?></h3>
                <div class="card-meta">
                    <span>📍 <?php echo htmlspecialchars($comp['location_name']); ?></span>
                    <span>👍 <?php echo $comp['vote_count']; ?> students affected</span>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if(empty($resolved_complaints)): ?>
            <p style="text-align: center; color: var(--text-muted); grid-column: 1 / -1;">No problems resolved yet.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Floating Action Button -->
<?php if(!is_logged_in()): ?>
    <button class="fab" onclick="openModal('authModal')">
        <span>➕</span> Report Problem
    </button>
<?php else: ?>
    <a href="<?php echo is_student() ? 'report_problem.php' : 'admin_dashboard.php'; ?>" class="fab" style="text-decoration:none;">
        <span>➕</span> <?php echo is_student() ? 'Report Problem' : 'Dashboard'; ?>
    </a>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>