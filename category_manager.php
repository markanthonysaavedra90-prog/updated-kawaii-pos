<?php 
include 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit;
}

if($_SESSION['role'] != 'admin'){
    die("Access denied");
}

if(isset($_POST['add']) && verify_csrf_token($_POST['csrf_token'] ?? '')){
    $name = trim($_POST['name']);
    
    if(empty($name)) {
        $error = "Category name is required";
    } else {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        if(!$stmt) {
            $error = "Database error";
        } else {
            $stmt->bind_param("s", $name);
            if(!$stmt->execute()) {
                $error = "Error adding category: " . $stmt->error;
            } else {
                $success = "✅ Category added successfully!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Categories - Kawaii POS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="layout">

    <div class="sidebar">
        <h2>Kawaii POS</h2>
        <a href="pos.php">POS</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="product_manager.php">Products</a>
        <a href="customer_manager.php">Customers</a>
        <a href="category_manager.php" style="background: var(--primary); color: white;">Categories</a>
        <a href="reports.php">Reports</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main">

        <h1>Category Manager</h1>

        <?php if(isset($error)): ?>
        <div class="card" style="background: #ffcdd2; border-left: 5px solid #f44336; margin-bottom: 20px;">
            <p style="color: #c62828; font-weight: 700;">❌ <?php echo htmlspecialchars($error); ?></p>
        </div>
        <?php endif; ?>

        <?php if(isset($success)): ?>
        <div class="card" style="background: #c8e6c9; border-left: 5px solid #66bb6a; margin-bottom: 20px;">
            <p style="color: #2e7d32; font-weight: 700;"><?php echo $success; ?></p>
        </div>
        <?php endif; ?>

        <!-- ADD CATEGORY FORM -->
        <div class="card" style="max-width: 500px; margin-bottom: 30px;">
            <h2>Add New Category</h2>

            <form method="POST">
                <?php echo csrf_field(); ?>
                <input type="text" name="name" placeholder="Category Name" required>
                <button type="submit" name="add" style="width: 100%; margin-top: 15px;">Add Category</button>
            </form>
        </div>

        <!-- CATEGORY LIST -->
        <div class="card">
            <h2>All Categories</h2>
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="background: #f5f5f5;">
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Category Name</th>
                    <th style="padding: 12px; text-align: center; border-bottom: 2px solid #ddd;">Action</th>
                </tr>
                <?php
                $result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
                
                if($result && $result->num_rows > 0){
                    while($row = $result->fetch_assoc()){
                        echo "<tr style='border-bottom: 1px solid #eee;'>";
                        echo "<td style='padding: 12px;'>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td style='padding: 12px; text-align: center;'>
                                <a href='delete_category.php?id=" . htmlspecialchars($row['id']) . "' onclick=\"return confirm('Delete this category?');\" style='background: #f44336; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; font-size: 0.9rem;'>Delete</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='2' style='padding: 20px; text-align: center; color: #999;'>No categories yet</td></tr>";
                }
                ?>
            </table>
        </div>

    </div>

</div>

</body>
</html>
