<?php
include 'db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $sql = "SELECT id, name FROM customers ORDER BY name ASC";
    $result = $conn->query($sql);
    
    if(!$result){
        throw new Exception("Database query failed: " . $conn->error);
    }

    $data = [];
    
    while($row = $result->fetch_assoc()){
        $row['id'] = (int)$row['id'];
        $data[] = $row;
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
