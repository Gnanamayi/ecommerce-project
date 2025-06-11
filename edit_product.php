<?php
session_start();
include('server/connection.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and has seller role
if (!isset($_SESSION['logged_in']) || $_SESSION['roles'] !== 'seller') {
    header('Location: login.php');
    exit();
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$seller_id = (int)$_SESSION['user_id'];

// Fetch product details
$product = [];
$error = '';

if ($product_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ? AND seller_id = ?");
    $stmt->bind_param('ii', $product_id, $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if (!$product) {
        $_SESSION['message'] = "Product not found or access denied";
        header('Location: seller.php');
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $new_price = (float)$_POST['product_price'];
    
    // Call your MySQL function
    $stmt = $conn->prepare("SELECT update_my_product_price(?, ?, ?) AS rows_updated");
    $stmt->bind_param('iid', $product_id, $seller_id, $new_price);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['rows_updated'] > 0) {
        $_SESSION['message'] = "Price updated successfully!";
        header('Location: seller.php');
        exit();
    } else {
        $error = "Failed to update price. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Edit Product Price</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if ($product): ?>
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Product Name</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($product['product_name']) ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Current Price</label>
                                <input type="text" class="form-control" value="Rs. <?= number_format($product['product_price'], 2) ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label for="product_price" class="form-label">New Price (Rs.)</label>
                                <input type="number" step="0.01" min="0" class="form-control" 
                                       name="product_price" value="<?= $product['product_price'] ?>" required>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="seller.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" name="update_product" class="btn btn-primary">Update Price</button>
                            </div>
                        </form>
                        <?php else: ?>
                            <div class="alert alert-warning">Product not found</div>
                            <a href="seller.php" class="btn btn-secondary">Back to Dashboard</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>