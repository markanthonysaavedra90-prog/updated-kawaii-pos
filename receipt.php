<?php
include 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit;
}

$sale_id = isset($_GET['sale_id']) ? (int)$_GET['sale_id'] : 0;

if($sale_id <= 0){
    die("<div style='padding: 20px; color: #f44336;'><h2>Error: Invalid Sale ID</h2></div>");
}

$stmt = $conn->prepare("SELECT * FROM sales WHERE id=?");
if(!$stmt){
    die("Prepare failed: " . escape($conn->error));
}

$stmt->bind_param("i", $sale_id);
if(!$stmt->execute()){
    die("Execute failed: " . escape($stmt->error));
}

$result = $stmt->get_result();
$sale = $result->fetch_assoc();

if(!$sale){
    die("<div style='padding: 20px; color: #f44336;'><h2>Error: Sale not found</h2></div>");
}

$stmt = $conn->prepare("
SELECT products.name, sales_details.quantity, sales_details.price
FROM sales_details
INNER JOIN products ON products.id = sales_details.product_id
WHERE sales_details.sale_id=?
");

if(!$stmt){
    die("Prepare failed: " . escape($conn->error));
}

$stmt->bind_param("i", $sale_id);
if(!$stmt->execute()){
    die("Execute failed: " . escape($stmt->error));
}

$items = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Receipt - Kawaii POS</title>
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
        <a href="reports.php">Reports</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main">
        <h1>Kawaii Receipt</h1>

        <div class="card">
            <h2>Sale ID: <?php echo htmlspecialchars($sale_id); ?></h2>
            <p style="color: #666; font-size: 0.9rem; margin-bottom: 20px;">
                Date: <?php echo isset($sale['created_at']) ? htmlspecialchars($sale['created_at']) : 'N/A'; ?>
            </p>

            <table style="width: 100%; border-collapse: collapse;">
                <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                    <th style="padding: 12px; text-align: left;">Product</th>
                    <th style="padding: 12px; text-align: center;">Qty</th>
                    <th style="padding: 12px; text-align: right;">Price</th>
                </tr>

                <?php 
                $hasItems = false;
                $totalAmount = 0;
                while($row=$items->fetch_assoc()){ 
                    $hasItems = true;
                    $subtotal = (float)$row['price'] * (int)$row['quantity'];
                    $totalAmount += $subtotal;
                ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px;"><?php echo htmlspecialchars($row['name']); ?></td>
                    <td style="padding: 12px; text-align: center;"><?php echo (int)$row['quantity']; ?></td>
                    <td style="padding: 12px; text-align: right;">₱<?php echo number_format((float)$row['price'], 2); ?></td>
                </tr>
                <?php } 
                if(!$hasItems){
                    echo "<tr><td colspan='3' style='padding: 20px; text-align: center; color: #999;'>No items in this sale</td></tr>";
                }
                ?>
            </table>

            <hr style="margin: 20px 0; border: none; border-top: 2px solid #ddd;">
            <div style="text-align: right; margin: 20px 0;">
                <h3 style="margin: 0; color: var(--primary);">Total: ₱<?php echo number_format($sale['total'] ?? 0, 2); ?></h3>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button onclick="printReceipt()" style="flex: 1; padding: 12px; background: #2196F3; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem;">Print Receipt</button>
                <a href="pdf_receipt.php?sale_id=<?php echo $sale_id; ?>" style="flex: 1; background: #4CAF50; color: white; padding: 12px; text-align: center; border-radius: 5px; text-decoration: none; border: none; cursor: pointer; font-size: 1rem; display: inline-block;">Download Receipt</a>
            </div>

            <script>
            function printReceipt(){
                let printWindow = window.open('', '', 'height=600,width=800');
                let receiptContent = document.querySelector('.card').innerHTML;
                printWindow.document.write('<html><head><title>Receipt</title>');
                printWindow.document.write('<link rel="stylesheet" href="style.css">');
                printWindow.document.write('<style>body { font-family: Arial; padding: 20px; } .card { all: unset; } button, a { display: none; }</style>');
                printWindow.document.write('</head><body>');
                printWindow.document.write(receiptContent);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.print();
            }
            </script>
            <a href="pos.php" style="display: block; margin-top: 10px; text-align: center;"><button style="width: 100%; padding: 12px;">Back to POS</button></a>
        </div>
    </div>

</div>

</body>
</html>