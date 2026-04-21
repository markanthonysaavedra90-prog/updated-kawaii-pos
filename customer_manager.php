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
    $contact = trim($_POST['contact']);
    
    if(empty($name)) {
        $error = "Customer name is required";
    } else {
        $stmt = $conn->prepare("INSERT INTO customers (name, contact) VALUES (?, ?)");
        if(!$stmt) {
            $error = "Database error";
        } else {
            $stmt->bind_param("ss", $name, $contact);
            if(!$stmt->execute()) {
                $error = "Error adding customer: " . $stmt->error;
            } else {
                $success = "✅ Customer added successfully!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customers - Kawaii POS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="layout">

    <div class="sidebar">
        <h2>Kawaii POS</h2>
        <a href="pos.php">POS</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="product_manager.php">Products</a>
        <a href="customer_manager.php" style="background: var(--primary); color: white;">Customers</a>
        <a href="category_manager.php">Categories</a>
        <a href="reports.php">Reports</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main">

        <h1>Customer Manager</h1>

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

        <!-- ADD CUSTOMER FORM -->
        <div class="card" style="max-width: 500px; margin-bottom: 30px;">
            <h2>Add New Customer</h2>

            <form method="POST">
                <?php echo csrf_field(); ?>
                <input type="text" name="name" placeholder="Customer Name" required>
                <input type="text" name="contact" placeholder="Contact Number">
                <button type="submit" name="add" style="width: 100%; margin-top: 15px;">Add Customer</button>
            </form>
        </div>

        <!-- CUSTOMER LIST -->
        <div class="card">
            <h2>All Customers</h2>
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="background: #f5f5f5;">
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Name</th>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Contact</th>
                    <th style="padding: 12px; text-align: center; border-bottom: 2px solid #ddd;">Action</th>
                </tr>
                <?php
                $result = $conn->query("SELECT * FROM customers ORDER BY name ASC");
                
                if($result && $result->num_rows > 0){
                    while($row = $result->fetch_assoc()){
                        echo "<tr style='border-bottom: 1px solid #eee;'>";
                        echo "<td style='padding: 12px;'>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td style='padding: 12px;'>" . htmlspecialchars($row['contact'] ?? 'N/A') . "</td>";
                        echo "<td style='padding: 12px; text-align: center;'>
                                <a href='delete_customer.php?id=" . htmlspecialchars($row['id']) . "' onclick=\"return confirm('Delete this customer?');\" style='background: #f44336; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; font-size: 0.9rem;'>Delete</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' style='padding: 20px; text-align: center; color: #999;'>No customers yet</td></tr>";
                }
                ?>
            </table>
        </div>

    </div>

</div>

</body>
</html>
