<?php
include 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit;
}

// TOTAL SALES
$totalSales = $conn->query("SELECT SUM(total) as total FROM sales")->fetch_assoc()['total'];

// TOTAL TRANSACTIONS
$totalTransactions = $conn->query("SELECT COUNT(*) as count FROM sales")->fetch_assoc()['count'];

// BEST SELLING PRODUCTS
$best = $conn->query("
SELECT 
    products.name,
    SUM(sales_details.quantity) as total_sold
FROM sales_details
INNER JOIN products ON sales_details.product_id = products.id
GROUP BY product_id
ORDER BY total_sold DESC
LIMIT 5
");

$bestProducts = [];
while($row = $best->fetch_assoc()){
    $bestProducts[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Kawaii POS</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="layout">

    <div class="sidebar">
        <h2>Kawaii POS</h2>
        <a href="pos.php">POS</a>
        <a href="dashboard.php" style="background: var(--primary); color: white;">Dashboard</a>
        <a href="product_manager.php">Products</a>
        <a href="customer_manager.php">Customers</a>
        <a href="category_manager.php">Categories</a>
        <a href="reports.php">Reports</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main">
        <h1>Dashboard</h1>

        <div class="grid">
            <div class="stat-card">
                <p>Total Sales</p>
                <h3>₱<?php echo $totalSales ? number_format($totalSales, 2) : '0.00'; ?></h3>
            </div>

            <div class="stat-card">
                <p>Total Transactions</p>
                <h3><?php echo $totalTransactions; ?></h3>
            </div>
        </div>

        <div class="card">
            <h2>Best Selling Products</h2>
            <table>
                <tr>
                    <th>Product</th>
                    <th>Units Sold</th>
                </tr>
                <?php foreach($bestProducts as $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><span class="badge badge-primary"><?php echo (int)$product['total_sold']; ?> units</span></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="card">
            <h2>Sales Chart</h2>
            <canvas id="salesChart" height="80"></canvas>
        </div>
    </div>

</div>

<script>
fetch("chart_data.php")
.then(res => {
    if(!res.ok) throw new Error("Failed to load chart data");
    return res.json();
})
.then(data => {
    if(!data.success) throw new Error(data.error || "Invalid chart data");
    
    const chartCanvas = document.getElementById("salesChart");
    if(!chartCanvas) return;
    
    new Chart(chartCanvas, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Daily Sales (₱)',
                data: data.data,
                borderColor: '#ff69b4',
                backgroundColor: 'rgba(255, 105, 180, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#ff69b4'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toFixed(0);
                        }
                    }
                }
            }
        }
    });
})
.catch(err => {
    console.error("Chart error:", err);
    const chartCanvas = document.getElementById("salesChart");
    if(chartCanvas){
        chartCanvas.style.display = 'none';
        const parent = chartCanvas.parentElement;
        if(parent){
            parent.innerHTML += '<p style="color: #999;">Unable to load sales chart</p>';
        }
    }
});
</script>

</body>
</html>