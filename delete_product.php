<?php 
include 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit;
}

if($_SESSION['role'] != 'admin'){
    die("Access denied");
}

$product_id = (int)$_GET['id'];

// Delete from inventory
$stmt = $conn->prepare("DELETE FROM inventory WHERE product_id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();

// Delete from sales_details if any
$stmt = $conn->prepare("DELETE FROM sales_details WHERE product_id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();

// Delete product
$stmt = $conn->prepare("DELETE FROM products WHERE id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();

header("Location: product_manager.php");
exit;
?>
