<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | CampusPulse</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #111827; color: white; padding: 30px; margin: 0; }
        h1 { color: #10B981; }
        table { width: 100%; border-collapse: collapse; background: #1F2937; border-radius: 8px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #374151; }
        th { background: #374151; font-weight: bold; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8em; font-weight: bold; }
        .high { background: #EF4444; color: white; }
        .med { background: #F59E0B; color: white; }
        .low { background: #10B981; color: white; }
    </style>
</head>
<body>

    <h1>📊 Administration Radar</h1>
    <p>Issues sorted dynamically by Priority Score: <code>(Upvotes × 1.5) + (Severity × 10)</code></p>

    <table>
        <thead>
            <tr>
                <th>Location</th>
                <th>Problem</th>
                <th>Category</th>
                <th>Upvotes</th>
                <th>Severity</th>
                <th>Priority Score</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Smart Priority Calculation directly in SQL
            $sql = "SELECT *, 
                           (upvotes * 1.5) + (severity * 10) AS priority_score 
                    FROM problems 
                    WHERE status != 'Resolved' 
                    ORDER BY priority_score DESC";
            
            $result = $conn->query($sql);
            
            while($row = $result->fetch_assoc()) {
                $sevText = "Low"; $sevClass = "low";
                if($row['severity'] == 2) { $sevText = "Medium"; $sevClass = "med"; }
                if($row['severity'] == 3) { $sevText = "High"; $sevClass = "high"; }
                
                echo "<tr>
                        <td>{$row['location']}</td>
                        <td><strong>{$row['title']}</strong></td>
                        <td>{$row['category']}</td>
                        <td>👍 {$row['upvotes']}</td>
                        <td><span class='badge $sevClass'>$sevText</span></td>
                        <td><strong style='color:#10B981; font-size:1.2em;'>{$row['priority_score']}</strong></td>
                        <td>{$row['status']}</td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>