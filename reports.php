<?php 
include 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit;
}

if($_SESSION['role'] != 'admin'){
    die("Access denied");
}

// GET SALES DATA
$sales = $conn->query("SELECT SUM(total) as total FROM sales")->fetch_assoc()['total'];

// GET DAILY SALES
$daily_sales = $conn->query("
SELECT 
DATE(created_at) as date,
SUM(total) as total
FROM sales
GROUP BY DATE(created_at)
ORDER BY date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports - Kawaii POS</title>
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
        <a href="category_manager.php">Categories</a>
        <a href="reports.php" style="background: var(--primary); color: white;">Reports</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main">

        <h1>Reports</h1>

        <!-- SALES SUMMARY -->
        <div class="stat-card">
            <p>Total Sales Revenue</p>
            <h3>₱ <?php echo number_format($sales ?? 0, 2); ?></h3>
        </div>

        <!-- BEST SELLING PRODUCTS -->
        <div class="card">
            <h2>Best Selling Products</h2>

            <table>
                <tr>
                    <th>Product</th>
                    <th>Qty Sold</th>
                </tr>

                <?php
                $best = $conn->query("
                SELECT products.name, SUM(sales_details.quantity) as qty
                FROM sales_details
                INNER JOIN products ON products.id = sales_details.product_id
                GROUP BY sales_details.product_id
                ORDER BY qty DESC
                ");

                while($b = $best->fetch_assoc()){
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($b['name']); ?></td>
                    <td><span class="badge badge-success"><?php echo (int)$b['qty']; ?> units</span></td>
                </tr>
                <?php } ?>
            </table>
        </div>

        <!-- DAILY SALES REPORT -->
        <div class="card">
            <h2>Daily Sales Report</h2>

            <table>
                <tr>
                    <th>Date</th>
                    <th>Total Sales</th>
                </tr>

                <?php while($row = $daily_sales->fetch_assoc()){ ?>
                <tr>
                    <td><?php echo $row['date']; ?></td>
                    <td>₱ <?php echo number_format($row['total'], 2); ?></td>
                </tr>
                <?php } ?>
            </table>
        </div>

        <!-- INVENTORY REPORT -->
        <div class="card">
            <h2>Inventory Status</h2>

            <table>
                <tr>
                    <th>Product</th>
                    <th>Stock</th>
                    <th>Status</th>
                </tr>

                <?php
                $inv = $conn->query("
                SELECT products.name, inventory.stock
                FROM inventory
                INNER JOIN products ON products.id = inventory.product_id
                ");

                while($i = $inv->fetch_assoc()){

                    $status = ($i['stock'] <= 10) ? "LOW" : "OK";
                ?>
                <tr>
                    <td><?php echo $i['name']; ?></td>
                    <td><?php echo $i['stock']; ?></td>
                    <td style="color:<?php echo ($status=='LOW')?'#f7b7a6':'#98d8c8'; ?>; font-weight: 700;">
                        <?php echo $status; ?>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>

    </div>

</div>

</body>
</html>