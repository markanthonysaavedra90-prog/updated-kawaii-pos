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

if(isset($_POST['update']) && verify_csrf_token($_POST['csrf_token'] ?? '')){
    $name = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $cat = (int)$_POST['category_id'];
    $stock = (int)$_POST['stock'];

    if(empty($name) || $price <= 0 || $cat <= 0 || $stock < 0) {
        $error = "Invalid product data";
    } else {
        $stmt = $conn->prepare("UPDATE products SET name=?, price=?, category_id=? WHERE id=?");
        if(!$stmt) {
            $error = "Database error";
        } else {
            $stmt->bind_param("sdii", $name, $price, $cat, $product_id);
            if(!$stmt->execute()) {
                $error = "Error updating product: " . $stmt->error;
            } else {
                $stmt = $conn->prepare("UPDATE inventory SET stock=? WHERE product_id=?");
                if(!$stmt) {
                    $error = "Database error";
                } else {
                    $stmt->bind_param("ii", $stock, $product_id);
                    if(!$stmt->execute()) {
                        $error = "Error updating inventory: " . $stmt->error;
                    } else {
                        header("Location: product_manager.php?success=1");
                        exit;
                    }
                }
            }
        }
    }
}

// Get product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT stock FROM inventory WHERE product_id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$inventory = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product - Kawaii POS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="layout">

    <div class="sidebar">
        <h2>Kawaii POS</h2>
        <a href="pos.php">POS</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="product_manager.php">Products</a>
        <a href="reports.php">Reports</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main">

        <h1>Edit Product</h1>

        <?php if(isset($error)): ?>
        <div class="card" style="background: #ffcdd2; border-left: 5px solid #f44336; margin-bottom: 20px;">
            <p style="color: #c62828; font-weight: 700;">❌ <?php echo htmlspecialchars($error); ?></p>
        </div>
        <?php endif; ?>

        <div class="card" style="max-width: 500px;">
            <form method="POST">
                <?php echo csrf_field(); ?>
                <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>

                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php
                    $cats = $conn->query("SELECT * FROM categories");
                    while($c = $cats->fetch_assoc()){
                        $selected = ($c['id'] == $product['category_id']) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($c['id']) . "' $selected>" . htmlspecialchars($c['name']) . "</option>";
                    }
                    ?>
                </select>

                <input type="number" name="stock" value="<?php echo htmlspecialchars($inventory['stock']); ?>" min="0" required>

                <button type="submit" name="update" style="width: 100%; margin-top: 15px; background: #4CAF50;">Update Product</button>
            </form>
            <a href="product_manager.php" style="display: block; margin-top: 10px; text-align: center; color: #666;">Back to Products</a>
        </div>

    </div>

</div>

</body>
</html>
