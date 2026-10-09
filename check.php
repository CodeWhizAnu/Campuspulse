<?php
require_once 'config/config.php';
require_once 'config/database.php';

$locations = $pdo->query("SELECT name, map_x_coord, map_y_coord FROM locations")->fetchAll();

echo "<table border='1' cellpadding='10' style='border-collapse: collapse; font-family: sans-serif;'>";
echo "<tr style='background: #eee;'><th>Location Name</th><th>X Coordinate</th><th>Y Coordinate</th></tr>";
foreach ($locations as $loc) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($loc['name']) . "</td>";
    echo "<td><strong>" . $loc['map_x_coord'] . "</strong></td>";
    echo "<td><strong>" . $loc['map_y_coord'] . "</strong></td>";
    echo "</tr>";
}
echo "</table>";
?>