<?php
require_once 'config/config.php';
require_once 'config/database.php';

echo "<div style='font-family: sans-serif; padding: 2rem;'>";
echo "<h1>Fixing Map Coordinates... 🗺️</h1>";

$updates = [
    'Main Gate' => [50, 96],
    'Library' => [19, 23],
    'Canteen' => [81, 51],
    'Block A' => [47, 50],
    'Block B' => [20, 66],
    'Computer Lab' => [61, 51],
    'Parking Area' => [77, 86],
    'Sports Ground' => [81, 20],
    'Hostel' => [52, 15]
];

try {
    foreach ($updates as $name => $coords) {
        $stmt = $pdo->prepare("UPDATE locations SET map_x_coord = ?, map_y_coord = ? WHERE name LIKE ?");
        $stmt->execute([$coords[0], $coords[1], "%$name%"]);
        
        $affected = $stmt->rowCount();
        if ($affected > 0) {
            echo "<p style='color: green;'>✅ Updated <strong>$name</strong> to X: {$coords[0]}, Y: {$coords[1]}</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Could not find <strong>$name</strong> in the database (or it is already updated).</p>";
        }
    }
    echo "<br><a href='admin_heatmap.php' style='padding: 10px 20px; background: #6C63FF; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>View Heatmap Now &rarr;</a>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database Error: " . $e->getMessage() . "</p>";
}
echo "</div>";
?>