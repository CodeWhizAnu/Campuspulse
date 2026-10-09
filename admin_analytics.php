<?php
// admin_analytics.php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/auth.php';

require_admin();
$page_title = "Analytics - " . APP_NAME;

// 1. Complaints by Category
$cat_stats = $pdo->query("
    SELECT cat.name, COUNT(c.id) as count 
    FROM categories cat 
    LEFT JOIN complaints c ON cat.id = c.category_id 
    GROUP BY cat.id 
    ORDER BY count DESC 
    LIMIT 6
")->fetchAll();

// 2. Problem Hotspots (Top Locations)
$loc_stats = $pdo->query("
    SELECT l.name, COUNT(c.id) as count 
    FROM locations l 
    LEFT JOIN complaints c ON l.id = c.location_id 
    GROUP BY l.id 
    ORDER BY count DESC 
    LIMIT 5
")->fetchAll();

// 3. Weekly Trends (Last 7 Days)
// Generating raw SQL for offline date generation and grouping
$trend_stats = $pdo->query("
    SELECT DATE(created_at) as report_date, COUNT(id) as count 
    FROM complaints 
    WHERE created_at >= DATE(NOW()) - INTERVAL 6 DAY
    GROUP BY DATE(created_at)
    ORDER BY report_date ASC
")->fetchAll();

// Format trends into an array indexed by day name
$days_map = [];
for($i=6; $i>=0; $i--) {
    $date_str = date('Y-m-d', strtotime("-$i days"));
    $days_map[date('D', strtotime($date_str))] = 0; // Initialize with 0
}
foreach($trend_stats as $ts) {
    $day_name = date('D', strtotime($ts['report_date']));
    if(isset($days_map[$day_name])) $days_map[$day_name] = $ts['count'];
}

// Find max values for CSS chart scaling
$max_cat = max(array_column($cat_stats, 'count') ?: [1]);
$max_loc = max(array_column($loc_stats, 'count') ?: [1]);
$max_trend = max(array_values($days_map) ?: [1]);

include 'includes/header.php';
include 'includes/admin_sidebar.php';
?>
<style>
    /* CSS Bar Chart Styles */
    .bar-chart-h { display: flex; flex-direction: column; gap: 1rem; }
    .bar-row { display: flex; align-items: center; gap: 1rem; }
    .bar-label { width: 120px; font-size: 0.875rem; font-weight: 500; text-align: right; }
    .bar-track { flex-grow: 1; background: var(--border-color); height: 24px; border-radius: 12px; overflow: hidden; }
    .bar-fill { height: 100%; background: var(--primary); border-radius: 12px; display: flex; align-items: center; padding: 0 0.5rem; color: white; font-size: 0.75rem; font-weight: bold; transition: width 1s ease-out; }
    
    .bar-chart-v { display: flex; align-items: flex-end; justify-content: space-around; height: 250px; padding-top: 2rem; border-bottom: 2px solid var(--border-color); margin-bottom: 1rem; }
    .v-bar-wrapper { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; width: 40px; }
    .v-bar-track { height: 200px; width: 100%; background: var(--border-color); border-radius: 6px 6px 0 0; position: relative; display: flex; align-items: flex-end; }
    .v-bar-fill { width: 100%; background: var(--accent); border-radius: 6px 6px 0 0; transition: height 1s ease-out; }
    .v-bar-val { position: absolute; top: -25px; width: 100%; text-align: center; font-size: 0.875rem; font-weight: bold; }
    .v-bar-label { font-size: 0.875rem; color: var(--text-muted); font-weight: 500; }
</style>

<div class="main-content">
    <h1 style="margin-bottom: 2rem;">Campus Analytics 📈</h1>

    <!-- Weekly Trend Chart -->
    <div class="glass-panel" style="padding: 2rem; margin-bottom: 2rem;">
        <h3 style="margin-bottom: 1rem;">Reports This Week</h3>
        <p style="color: var(--text-muted); margin-bottom: 1rem;">Volume of complaints submitted over the last 7 days.</p>
        
        <div class="bar-chart-v">
            <?php foreach($days_map as $day => $count): 
                $height_pct = ($max_trend > 0) ? ($count / $max_trend) * 100 : 0;
            ?>
                <div class="v-bar-wrapper">
                    <div class="v-bar-track">
                        <div class="v-bar-fill" style="height: <?php echo $height_pct; ?>%;">
                            <?php if($count > 0): ?><div class="v-bar-val"><?php echo $count; ?></div><?php endif; ?>
                        </div>
                    </div>
                    <div class="v-bar-label"><?php echo $day; ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem;">
        
        <!-- Category Distribution -->
        <div class="glass-panel" style="padding: 2rem;">
            <h3 style="margin-bottom: 1.5rem;">Top Problem Categories</h3>
            <div class="bar-chart-h">
                <?php foreach($cat_stats as $cat): 
                    $width_pct = ($max_cat > 0) ? ($cat['count'] / $max_cat) * 100 : 0;
                ?>
                <div class="bar-row">
                    <div class="bar-label"><?php echo htmlspecialchars($cat['name']); ?></div>
                    <div class="bar-track">
                        <div class="bar-fill" style="width: <?php echo max(5, $width_pct); ?>%; background: var(--primary);">
                            <?php echo $cat['count']; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Location Hotspots -->
        <div class="glass-panel" style="padding: 2rem;">
            <h3 style="margin-bottom: 1.5rem;">Problem Hotspots</h3>
            <div class="bar-chart-h">
                <?php foreach($loc_stats as $loc): 
                    $width_pct = ($max_loc > 0) ? ($loc['count'] / $max_loc) * 100 : 0;
                ?>
                <div class="bar-row">
                    <div class="bar-label"><?php echo htmlspecialchars($loc['name']); ?></div>
                    <div class="bar-track">
                        <div class="bar-fill" style="width: <?php echo max(5, $width_pct); ?>%; background: var(--danger);">
                            <?php echo $loc['count']; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</div>
</body>
</html>