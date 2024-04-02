<?php
session_start();
// if user is logged in and is an admin, if not redirect to login page
if (!isset($_SESSION['login_status']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'Database/db_config.php';

// get total sales by date
$query = $db->prepare("SELECT DATE(sale_date) as sale_date, SUM(total_price) as total_sales FROM sales GROUP BY DATE(sale_date)");
$query->execute();
$totalSalesData = $query->fetchAll(PDO::FETCH_ASSOC);

// get total sales by product
$query = $db->prepare("SELECT product_id, SUM(quantity_sold) as total_quantity_sold FROM sales GROUP BY product_id");
$query->execute();
$totalQuantitySoldData = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Admin Dashboard</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <!-- navbar content -->
        <a class="navbar-brand" href="#"><img src="http://localhost:4566/img-bucket/S3s/images/brand_logo.png" alt="Company Logo" class="logo" style="height: 30px;"></a>
        <a class="nav-link text-white bg-info" href="orderManagement.php" style="border-radius:5px; height: 40px;">Orders</a>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="#" data-toggle="modal" data-target="#userModal">
                    <i class="fas fa-user profile-icon"></i>                    
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- user profile modal -->
    <?php include 'userModal.php'; ?>

    <!-- Header for the admin dashboard -->
    <div class="container">
        <h1 class="mt-4 mb-4">Admin Dashboard</h1>
    </div>

    <div class="container" style="padding: 20px;">
        <div class="row">
            <div class="col-md-6">
                <canvas id="salesChart" width="400" height="200" style="margin-top: 20px; max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px;"></canvas>
            </div>
            <div class="col-md-6">
                <canvas id="productChart" width="400" height="200" style="margin-top: 20px; max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px;"></canvas>
            </div>
        </div>
    </div>
</body>
</html>

<script>
    // Sales Chart
    var salesCtx = document.getElementById('salesChart').getContext('2d');
    var salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: [
                <?php foreach ($totalSalesData as $data) { ?>
                    '<?php echo $data['sale_date']; ?>',
                <?php } ?>
            ],
            datasets: [{
                label: 'Total Sales',
                data: [
                    <?php foreach ($totalSalesData as $data) { ?>
                        <?php echo $data['total_sales']; ?>,
                    <?php } ?>
                ],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Product Chart
    var productCtx = document.getElementById('productChart').getContext('2d');
    var productChart = new Chart(productCtx, {
        type: 'bar',
        data: {
            labels: [
                <?php foreach ($totalQuantitySoldData as $data) { ?>
                    '<?php echo $data['product_id']; ?>',
                <?php } ?>
            ],
            datasets: [{
                label: 'Total Quantity Sold',
                data: [
                    <?php foreach ($totalQuantitySoldData as $data) { ?>
                        <?php echo $data['total_quantity_sold']; ?>,
                    <?php } ?>
                ],
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>