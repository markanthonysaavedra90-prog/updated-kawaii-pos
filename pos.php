<?php
include 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kawaii POS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="layout">

    <div class="sidebar">
        <h2>Kawaii POS</h2>
        <a href="pos.php" style="background: var(--primary); color: white;">POS</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="product_manager.php">Products</a>
        <a href="customer_manager.php">Customers</a>
        <a href="category_manager.php">Categories</a>
        <a href="reports.php">Reports</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main">

        <h1>Cashier Panel</h1>

        <div id="notif" style="margin-bottom: 20px;"></div>

        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 35px; margin-top: 30px;">

            <div class="card" style="padding-right: 0;">
                <h2 style="padding: 0 25px;">Available Products</h2>
                <div id="productTable" style="padding: 0 25px;"></div>
            </div>

            <div class="card" style="position: sticky; top: 20px; height: fit-content; background: linear-gradient(135deg, white, #fafafa);">
                <h2>Shopping Cart</h2>
                <select id="customer" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">Select Customer</option>
                </select>
                <table id="cartTable" style="margin-bottom: 25px; margin-top: 20px;"></table>
                <h3 id="total" style="text-align: center; margin: 20px 0; font-size: 2.2rem; color: var(--primary);">₱0.00</h3>
                <input type="number" id="payment" placeholder="Enter payment" style="width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px;">
                <h3 id="change">Change: 0</h3>
                <button onclick="checkout()" style="width: 100%; background: linear-gradient(135deg, var(--primary), var(--secondary)); padding: 16px; font-size: 1.1rem; margin-top: 20px; letter-spacing: 0.5px;">
                    Complete Checkout
                </button>
            </div>

        </div>

    </div>

</div>

<script src="script.js"></script>
</body>
</html>
