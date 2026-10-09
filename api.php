<?php
include 'db.php';
header('Content-Type: application/json');

$q = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
$loc = isset($_GET['loc']) ? $conn->real_escape_string($_GET['loc']) : '';

if($q != '') {
    // Simple offline LIKE search for duplicate detection
    // In a production app, you can use MySQL FULLTEXT or Soundex here
    $query = "SELECT title, upvotes FROM problems WHERE location = '$loc' AND status != 'Resolved' AND title LIKE '%$q%' LIMIT 1";
    $result = $conn->query($query);
    
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(["found" => true, "title" => $row['title'], "upvotes" => $row['upvotes']]);
        exit;
    }
}
echo json_encode(["found" => false]);
?>