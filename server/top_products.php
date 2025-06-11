<?php
session_start();
include('server/connection.php');

// Check admin access
if (!isset($_SESSION['logged_in']) || $_SESSION['roles'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Get top selling products using your function
$query = "SELECT GetTopSellingProducts() as top_products";
$result = $conn->query($query);
$products_data = $result->fetch_assoc()['top_products'];

// Parse the function's output
$products = [];
$lines = explode("\n", $products_data);
foreach ($lines as $line) {
    if (!empty($line)) {
        $parts = explode(', ', $line);
        $product = [
            'product_id' => str_replace('Product ID: ', '', $parts[0]),
            'product_name' => str_replace('Name: ', '', $parts[1]),
            'total_sold' => str_replace('Total Sold: ', '', $parts[2]),
            'total_orders' => str_replace('Total Orders: ', '', $parts[3])
        ];
        $products[] = $product;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Selling Products</title>
    <!-- Include all your CSS files from admin.php -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assests/css/style.css"/>
    <style>
        .section-title {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .table th {
            background-color: #343a40;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Same navbar as your admin.php -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top py-3">
        <!-- Your existing navbar code -->
    </nav>

    <section id="home1">
        <div class="container1">
            <h1>TOP SELLING PRODUCTS</h1>
        </div>
    </section>

    <section class="my-5 py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="section-title mb-0">Top 3 Selling Products</h2>
                        <a href="admin.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Admin Panel
                        </a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Rank</th>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Total Sold</th>
                                    <th>Total Orders</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($products)): ?>
                                    <?php foreach ($products as $index => $product): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($product['product_id']) ?></td>
                                            <td><?= htmlspecialchars($product['product_name']) ?></td>
                                            <td><?= htmlspecialchars($product['total_sold']) ?></td>
                                            <td><?= htmlspecialchars($product['total_orders']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No sales data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Same footer as your admin.php -->
    <footer>
        <!-- Your existing footer code -->
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>