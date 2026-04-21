<?php
include 'db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $result = $conn->query("
    SELECT DATE(created_at) as date, SUM(total) as total
    FROM sales
    GROUP BY DATE(created_at)
    ORDER BY date ASC
    ");
    
    if(!$result){
        throw new Exception("Database query failed: " . escape($conn->error));
    }

    $data = [];
    $labels = [];

    while($row = $result->fetch_assoc()){
        $labels[] = $row['date'];
        $data[] = (float)($row['total'] ?? 0);
    }

    echo json_encode([
        "labels" => $labels,
        "data" => $data,
        "success" => true
    ], JSON_UNESCAPED_UNICODE);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage(), "success" => false]);
}
?>
