<?php
// admin_heatmap.php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/auth.php';

require_admin();
$page_title = "Campus Heatmap - " . APP_NAME;

// Aggregate active complaints by location
$heatmap_data = $pdo->query("
    SELECT 
        l.id, l.name, l.map_x_coord, l.map_y_coord,
        COUNT(c.id) as total_problems,
        SUM(CASE WHEN c.severity = 'Critical' THEN 1 ELSE 0 END) as critical_count,
        SUM(CASE WHEN c.severity = 'High' THEN 1 ELSE 0 END) as high_count
    FROM locations l
    LEFT JOIN complaints c ON l.id = c.location_id AND c.status NOT IN ('Resolved', 'Rejected')
    GROUP BY l.id, l.name, l.map_x_coord, l.map_y_coord
    HAVING COUNT(c.id) > 0
")->fetchAll();

include 'includes/header.php';
include 'includes/admin_sidebar.php';
?>
<style>
    .heatmap-wrapper {
        position: relative; /* CRITICAL: Keeps dots inside the map */
        width: 100%;
        max-width: 1000px;
        margin: 0 auto;
        border-radius: 16px;
        border: 4px solid var(--border-color);
        background: #e2e8f0;
        box-shadow: var(--shadow-lg);
    }
    
    .campus-map-img {
        width: 100%;
        height: auto;
        display: block;
    }
    
    .hotspot {
        position: absolute; /* CRITICAL: Allows X/Y coordinates to work */
        width: 30px;
        height: 30px;
        background: var(--primary);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        cursor: pointer;
        box-shadow: 0 0 10px rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 0.75rem;
        transition: transform 0.2s;
        z-index: 5;
    }
    
    .hotspot::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }
    
    .hotspot:hover { 
        transform: translate(-50%, -50%) scale(1.2); 
        z-index: 10; 
    }
    
    .hotspot-critical { background: var(--danger); }
    .hotspot-critical::after { border: 2px solid var(--danger); }
    
    .hotspot-high { background: var(--accent); color: black; }
    .hotspot-high::after { border: 2px solid var(--accent); }
    
    .hotspot-medium { background: var(--warning); color: black; }
    .hotspot-medium::after { border: 2px solid var(--warning); }

    @keyframes pulse {
        0% { transform: scale(1); opacity: 0.8; }
        100% { transform: scale(2); opacity: 0; }
    }

    .tooltip {
        position: absolute;
        bottom: 120%;
        left: 50%;
        transform: translateX(-50%);
        background: var(--card-bg);
        color: var(--text-main);
        padding: 1rem;
        border-radius: 8px;
        box-shadow: var(--shadow-md);
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: 0.2s;
        border: 1px solid var(--border-color);
        z-index: 20;
    }
    
    .hotspot:hover .tooltip {
        opacity: 1;
        visibility: visible;
        bottom: 130%;
    }
</style>

<div class="main-content">
    <div style="margin-bottom: 2rem;">
        <h1 style="margin-bottom: 0.5rem;">Interactive Problem Heatmap 🗺️</h1>
        <p style="color: var(--text-muted);">Visualizing active problem hotspots across the college campus.</p>
    </div>

    <div class="heatmap-wrapper">
        <img src="<?php echo BASE_URL; ?>assets/images/campus-map.jpg" alt="Campus Map" class="campus-map-img">
        
        <?php foreach($heatmap_data as $spot): 
            $class = 'hotspot-medium';
            if ($spot['critical_count'] > 0) $class = 'hotspot-critical';
            elseif ($spot['high_count'] > 0) $class = 'hotspot-high';
            
            // Fallback to 50 if database somehow returns null
            $x = $spot['map_x_coord'] ?: 50;
            $y = $spot['map_y_coord'] ?: 50;
        ?>
            <div class="hotspot <?php echo $class; ?>" style="left: <?php echo $x; ?>%; top: <?php echo $y; ?>%;">
                <?php echo $spot['total_problems']; ?>
                
                <div class="tooltip glass-panel">
                    <h4 style="margin-bottom: 0.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.25rem;">
                        <?php echo htmlspecialchars($spot['name']); ?>
                    </h4>
                    <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.25rem;">Total Active: <strong><?php echo $spot['total_problems']; ?></strong></p>
                    <p style="font-size: 0.875rem; color: var(--danger); margin-bottom: 0.25rem;">Critical: <?php echo $spot['critical_count']; ?></p>
                    <p style="font-size: 0.875rem; color: var(--accent);">High: <?php echo $spot['high_count']; ?></p>
                    
                    <a href="admin_complaints.php?search=<?php echo urlencode($spot['name']); ?>" 
                       style="display: block; margin-top: 0.5rem; font-size: 0.75rem; color: var(--primary); font-weight: bold;">
                       View Problems &rarr;
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>