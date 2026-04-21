<?php 
include 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit;
}

if($_SESSION['role'] != 'admin'){
    die("Access denied");
}

$customer_id = (int)$_GET['id'];

// Delete customer
$stmt = $conn->prepare("DELETE FROM customers WHERE id=?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();

header("Location: customer_manager.php");
exit;
?>
