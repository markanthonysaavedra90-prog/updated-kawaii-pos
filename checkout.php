<?php
include 'db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);
    
    if(!$data){
        throw new Exception("Invalid JSON data");
    }

    $items = $data['items'] ?? [];
    $payment = (float)($data['payment'] ?? 0);
    $total = (float)($data['total'] ?? 0);
    $customer_id = !empty($data['customer_id']) ? (int)$data['customer_id'] : null;
    $change = $payment - $total;
    
    if(empty($items)){
        throw new Exception("No items in cart");
    }
    
    if($payment <= 0){
        throw new Exception("Invalid payment amount");
    }

    $conn->begin_transaction();

    // create sale
    $stmt = $conn->prepare("INSERT INTO sales (customer_id, total, created_at) VALUES (?, ?, NOW())");
    if(!$stmt){
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("id", $customer_id, $total);
    
    if(!$stmt->execute()){
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $sale_id = $conn->insert_id;

    foreach($items as $item){

        $product_id = (int)$item['id'];
        $qty = (int)$item['qty'];
        $price = (float)$item['price'];

        // Check stock availability using prepared statement
        $check_stmt = $conn->prepare("SELECT stock FROM inventory WHERE product_id=?");
        if(!$check_stmt){
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $check_stmt->bind_param("i", $product_id);
        if(!$check_stmt->execute()){
            throw new Exception("Execute failed: " . $check_stmt->error);
        }
        $check = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();

        if(!$check || $check['stock'] < $qty){
            throw new Exception("Not enough stock for product ID $product_id");
        }

        // CALL STORED PROCEDURE (ADBMS REQUIREMENT) - using prepared statement
        $proc_stmt = $conn->prepare("CALL process_sale(?, ?)");
        if(!$proc_stmt){
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $proc_stmt->bind_param("ii", $product_id, $qty);
        if(!$proc_stmt->execute()){
            throw new Exception("Failed to process sale: " . $proc_stmt->error);
        }
        $proc_stmt->close();

        $stmt = $conn->prepare("
            INSERT INTO sales_details (sale_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");
        if(!$stmt){
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("iiid", $sale_id, $product_id, $qty, $price);
        if(!$stmt->execute()){
            throw new Exception("Execute failed: " . $stmt->error);
        }

        // Log stock change using prepared statement
        $log_stmt = $conn->prepare("
            INSERT INTO stock_logs (product_id, change_qty, action)
            VALUES (?, ?, 'SALE')
        ");
        if(!$log_stmt){
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $neg_qty = -$qty;
        $log_stmt->bind_param("ii", $product_id, $neg_qty);
        if(!$log_stmt->execute()){
            throw new Exception("Failed to log stock change: " . $log_stmt->error);
        }
        $log_stmt->close();
    }

    $action = "Sale completed ID: $sale_id - Total: ₱" . number_format($total, 2);
    $stmt = $conn->prepare("INSERT INTO audit_logs (action, created_at) VALUES (?, NOW())");
    if(!$stmt){
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $action);
    if(!$stmt->execute()){
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "Transaction Successful",
        "sale_id" => (int)$sale_id,
        "receipt_url" => "receipt.php?sale_id=$sale_id"
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if($conn->connect_errno === 0) {
        $conn->rollback();
    }
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Transaction Failed: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>