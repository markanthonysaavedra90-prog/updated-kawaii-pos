<?php 
include 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit;
}

if($_SESSION['role'] != 'admin'){
    die("Access denied");
}

$category_id = (int)$_GET['id'];

// Delete category
$stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
$stmt->bind_param("i", $category_id);
$stmt->execute();

header("Location: category_manager.php");
exit;
?>
