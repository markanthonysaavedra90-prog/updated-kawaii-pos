<?php 
include 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit;
}

if($_SESSION['role'] != 'admin'){
    die("Access denied");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Products - Kawaii POS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="layout">

    <div class="sidebar">
        <h2>Kawaii POS</h2>
        <a href="pos.php">POS</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="product_manager.php" style="background: var(--primary); color: white;">Products</a>
        <a href="customer_manager.php">Customers</a>
        <a href="category_manager.php">Categories</a>
        <a href="reports.php">Reports</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main">

        <h1>Product Manager</h1>

        <!-- ADD PRODUCT FORM -->
        <div class="card" style="max-width: 500px; margin-bottom: 30px;">
            <h2>Add New Product</h2>

            <form method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="text" name="name" placeholder="Product Name" required>
                <input type="number" name="price" placeholder="Price" step="0.01" required>

                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php
                    $cats = $conn->query("SELECT * FROM categories");
                    while($c = $cats->fetch_assoc()){
                        echo "<option value='" . htmlspecialchars($c['id']) . "'>" . htmlspecialchars($c['name']) . "</option>";
                    }
                    ?>
                </select>

                <input type="file" name="image" accept="image/*" required>
                <input type="number" name="stock" placeholder="Initial Stock" min="0" required>

                <button type="submit" name="add" style="width: 100%; margin-top: 15px;">Add Product</button>
            </form>
        </div>

        <?php
        if(isset($_POST['add']) && verify_csrf_token($_POST['csrf_token'] ?? '')){
            $name = trim($_POST['name']);
            $price = (float)$_POST['price'];
            $cat = (int)$_POST['category_id'];
            $stock = (int)$_POST['stock'];
            
            // Validate inputs
            if(empty($name) || $price <= 0 || $cat <= 0 || $stock < 0) {
                echo "<div class='card' style='background: #ffcdd2; border-left: 5px solid #f44336; margin-bottom: 20px;'><p style='color: #c62828; font-weight: 700;'>❌ Invalid product data</p></div>";
            } else if(empty($_FILES['image']['name'])) {
                echo "<div class='card' style='background: #ffcdd2; border-left: 5px solid #f44336; margin-bottom: 20px;'><p style='color: #c62828; font-weight: 700;'>❌ Please select an image</p></div>";
            } else {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $tmp = $_FILES['image']['tmp_name'];
                $file_type = mime_content_type($tmp);
                $img_name = basename($_FILES['image']['name']);
                $ext = pathinfo($img_name, PATHINFO_EXTENSION);
                $safe_img_name = uniqid() . '.' . $ext;
                
                if(!in_array($file_type, $allowed_types)) {
                    echo "<div class='card' style='background: #ffcdd2; border-left: 5px solid #f44336; margin-bottom: 20px;'><p style='color: #c62828; font-weight: 700;'>❌ Invalid image format. Please upload JPEG, PNG, GIF, or WebP</p></div>";
                } else if(move_uploaded_file($tmp, "uploads/".$safe_img_name)){
                    $stmt = $conn->prepare("INSERT INTO products (name, image, price, category_id) VALUES (?, ?, ?, ?)");
                    if(!$stmt) {
                        echo "<div class='card' style='background: #ffcdd2; border-left: 5px solid #f44336; margin-bottom: 20px;'><p style='color: #c62828; font-weight: 700;'>❌ Database error</p></div>";
                        unlink("uploads/".$safe_img_name);
                    } else {
                        $stmt->bind_param("ssdi", $name, $safe_img_name, $price, $cat);
                        if(!$stmt->execute()) {
                            echo "<div class='card' style='background: #ffcdd2; border-left: 5px solid #f44336; margin-bottom: 20px;'><p style='color: #c62828; font-weight: 700;'>❌ Error adding product: " . $stmt->error . "</p></div>";
                            unlink("uploads/".$safe_img_name);
                        } else {
                            $product_id = $conn->insert_id;
                            
                            $stmt = $conn->prepare("INSERT INTO inventory (product_id, stock) VALUES (?, ?)");
                            if(!$stmt || !$stmt->bind_param("ii", $product_id, $stock) || !$stmt->execute()) {
                                echo "<div class='card' style='background: #ffcdd2; border-left: 5px solid #f44336; margin-bottom: 20px;'><p style='color: #c62828; font-weight: 700;'>❌ Error setting inventory</p></div>";
                            } else {
                                echo "<div class='card' style='background: #c8e6c9; border-left: 5px solid #66bb6a; margin-bottom: 20px;'><p style='color: #2e7d32; font-weight: 700;'>✅ Product added successfully!</p></div>";
                            }
                        }
                    }
                } else {
                    echo "<div class='card' style='background: #ffcdd2; border-left: 5px solid #f44336; margin-bottom: 20px;'><p style='color: #c62828; font-weight: 700;'>❌ Error uploading image</p></div>";
                }
            }
        }
        ?>

        <!-- PRODUCT LIST -->
        <div class="card">
            <h2>All Products</h2>
            <div class="product-grid">
                <?php
                $result = $conn->query("
                SELECT products.*, categories.name AS category
                FROM products
                INNER JOIN categories ON products.category_id = categories.id
                ");

                while($row = $result->fetch_assoc()){
                ?>
                <div class="product-card">
                    <?php $imgSrc = (strpos($row['image'], 'http') !== false) ? $row['image'] : 'uploads/' . htmlspecialchars($row['image']); ?>
                    <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" style="object-fit: cover;">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p style="color: #999; font-size: 0.9rem;">Category: <?php echo htmlspecialchars($row['category']); ?></p>
                    <div class="price">₱<?php echo number_format($row['price'], 2); ?></div>
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <a href="edit_product.php?id=<?php echo htmlspecialchars($row['id']); ?>" style="flex: 1; background: #2196F3; color: white; padding: 8px; text-align: center; border-radius: 4px; text-decoration: none; font-size: 0.9rem;">Edit</a>
                        <a href="delete_product.php?id=<?php echo htmlspecialchars($row['id']); ?>" onclick="return confirm('Delete this product?');" style="flex: 1; background: #f44336; color: white; padding: 8px; text-align: center; border-radius: 4px; text-decoration: none; font-size: 0.9rem;">Delete</a>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

    </div>

</div>

</body>
</html>

