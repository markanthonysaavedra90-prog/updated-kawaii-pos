<?php
include 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit;
}

$sale_id = isset($_GET['sale_id']) ? (int)$_GET['sale_id'] : 0;

if($sale_id <= 0){
    die("Invalid Sale ID");
}

$stmt = $conn->prepare("SELECT * FROM sales WHERE id=?");
if(!$stmt){
    die("Database error: " . escape($conn->error));
}

$stmt->bind_param("i", $sale_id);
if(!$stmt->execute()){
    die("Query error: " . escape($stmt->error));
}

$result = $stmt->get_result();
$sale = $result->fetch_assoc();

if(!$sale){
    die("Sale not found");
}

$stmt = $conn->prepare("
SELECT products.name, sales_details.quantity, sales_details.price
FROM sales_details
INNER JOIN products ON products.id = sales_details.product_id
WHERE sales_details.sale_id=?
");

if(!$stmt){
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $sale_id);
if(!$stmt->execute()){
    die("Query error: " . $stmt->error);
}

$items = $stmt->get_result();

// Generate receipt text
$receipt = "=====================================\n";
$receipt .= "       KAWAII POS RECEIPT\n";
$receipt .= "=====================================\n\n";
$receipt .= "Sale ID: " . $sale_id . "\n";
$receipt .= "Date: " . ($sale['created_at'] ?? date('Y-m-d H:i:s')) . "\n";
$receipt .= "-------------------------------------\n\n";

$receipt .= "ITEMS:\n";
$receipt .= str_pad("Product", 30) . str_pad("Qty", 10, " ", STR_PAD_LEFT) . str_pad("Price", 15, " ", STR_PAD_LEFT) . "\n";
$receipt .= "-------------------------------------\n";

if($items->num_rows == 0){
    $receipt .= "No items in this sale\n";
}

while($row = $items->fetch_assoc()){
    $receipt .= str_pad(substr($row['name'], 0, 28), 30) . str_pad($row['quantity'], 10, " ", STR_PAD_LEFT) . str_pad("₱" . number_format((float)$row['price'], 2), 15, " ", STR_PAD_LEFT) . "\n";
}

$receipt .= "\n-------------------------------------\n";
$receipt .= "Total:    ₱" . number_format($sale['total'] ?? 0, 2) . "\n";
$receipt .= "-------------------------------------\n\n";
$receipt .= "    Thank you for your purchase!\n";
$receipt .= "         Kawaii POS System\n";
$receipt .= "=====================================\n";

// Send as download
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="receipt_' . $sale_id . '.txt"');
echo $receipt;
