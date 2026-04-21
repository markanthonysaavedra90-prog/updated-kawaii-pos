<?php
include 'db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $sql = "
    SELECT 
        products.id,
        products.name,
        products.image,
        products.price,
        categories.name AS category,
        inventory.stock
    FROM products
    INNER JOIN categories ON products.category_id = categories.id
    INNER JOIN inventory ON products.id = inventory.product_id
    ORDER BY products.name ASC
    ";

    $result = $conn->query($sql);
    
    if(!$result){
        throw new Exception("Database query failed: " . $conn->error);
    }

    $data = [];

    while($row = $result->fetch_assoc()){
        $stock_status = ($row['stock'] <= 10) ? 'LOW' : 'OK';
        $row['status'] = $stock_status;
        $row['price'] = (float)$row['price'];
        $row['stock'] = (int)$row['stock'];
        $data[] = $row;
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>