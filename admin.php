<?php
session_start();
include('server/connection.php');

// Check if user is logged in and has the "admin" role
if (!isset($_SESSION['logged_in']) || $_SESSION['roles'] !== 'admin') {
    echo "Access denied.";
    exit();
}

// Initialize variables
$users = [];
$top_products = [];
$error = '';

try {
    // Get all users
    $query = "SELECT user_id, user_name, user_email, roles FROM users";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->execute();
    $users = $stmt->get_result();
    
    if (!$users) {
        throw new Exception("Error fetching users: " . $conn->error);
    }
    
    // Get top selling products (using direct query - recommended)
    $query = "SELECT 
                p.product_id, 
                p.product_name, 
                IFNULL(SUM(oi.product_quantity), 0) as total_sold,
                COUNT(oi.item_id) as total_orders
              FROM products p
              LEFT JOIN order_items oi ON p.product_id = oi.product_id
              GROUP BY p.product_id, p.product_name
              ORDER BY total_orders DESC, total_sold DESC
              LIMIT 3";
    $top_products = $conn->query($query);
    
    if (!$top_products) {
        throw new Exception("Error fetching top products: " . $conn->error);
    }
    
} catch (Exception $e) {
    $error = $e->getMessage();
    $users = [];
    $top_products = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="assests/css/style.css"/>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
    .table td, .table th {
        vertical-align: middle;
    }
    .badge {
        font-size: 0.85em;
        padding: 5px 10px;
    }
        /* Add this to your existing styles */
        .section-title {
        margin-bottom: 0 !important; /* Remove default bottom margin */
    }
    .mb-4 {
        margin-bottom: 1.5rem !important; /* Add space below the heading/button row */
    }
    .top-products-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .top-products-header {
            background-color: #343a40;
            color: white;
            padding: 15px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .top-products-body {
            padding: 20px;
        }
        .product-rank {
            font-weight: bold;
            color: #343a40;
            width: 30px;
            display: inline-block;
        }
</style>


</head>
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top py-3">
        <div class="container">
          <img src="assests/images/image.jpeg"/>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse nav-buttons" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
              <li class="nav-item">
                <a class="nav-link" href="index.php">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="shop.php">Shop</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="contact.php">Contact Us</a>
              </li>
              <li class="nav-item d-flex align-items-center">
                <a href="cart.php"><i class="fa-solid fa-envelope"></i></a> 
               <a href="account.php"><i class="fa fa-user"></i></a> 
              </li>

            </ul>    
          </div>
        </div>
      </nav>
      <section id="home1">
      <div class="container1">
        <h1>WELCOME ADMIN</h1>
      </div>
    </section>
    <section class="my-5 py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <!-- User Management Section -->
                    <h2 class="section-title">User Management</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($users) && $users->num_rows > 0): ?>
                                    <?php while($user = $users->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                            <td><?php echo htmlspecialchars($user['user_name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['user_email']); ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?php echo $user['roles'] === 'admin' ? 'bg-danger' : 
                                                       ($user['roles'] === 'seller' ? 'bg-warning text-dark' : 'bg-primary'); ?>">
                                                    <?php echo htmlspecialchars($user['roles']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete_user.php?id=<?= $user['user_id'] ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No users found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Top Selling Products Card -->
                    <div class="top-products-card">
                        <div class="top-products-header">
                            <h4 class="mb-0"><i class="fas fa-chart-line"></i> Top Selling Products</h4>
                        </div>
                        <div class="top-products-body">
                            <?php if ($top_products && $top_products->num_rows > 0): ?>
                                <ol class="list-unstyled">
                                    <?php $rank = 1; ?>
                                    <?php while($product = $top_products->fetch_assoc()): ?>
                                        <li class="mb-3">
                                            <span class="product-rank"><?= $rank++; ?>.</span>
                                            <strong><?= htmlspecialchars($product['product_name']) ?></strong>
                                            <div class="text-muted small">
                                                Sold: <?= $product['total_sold'] ?> units<br>
                                                Orders: <?= $product['total_orders'] ?>
                                            </div>
                                        </li>
                                    <?php endwhile; ?>
                                </ol>
                            <?php else: ?>
                                <div class="alert alert-info mb-0">No sales data available</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Stats Card (optional) -->
                    <div class="card mt-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-tachometer-alt"></i> Quick Stats</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            // Example stats - you would replace with actual queries
                            $total_users = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
                            $total_products = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
                            ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Users:</span>
                                <strong><?= $total_users ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Total Products:</span>
                                <strong><?= $total_products ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
      <footer>
        <div class="footer-container">
          <div class="footer-section">
            <h4>About</h4>
            <ul>
              <li><a href="#">Contact Us</a></li>
              <li><a href="#">About Us</a></li>
              <li><a href="#">Careers</a></li>
              <li><a href="#">Flipkart Stories</a></li>
              <li><a href="#">Press</a></li>
            </ul>
          </div>
          <div class="footer-section">
            <h4>Help</h4>
            <ul>
              <li><a href="#">Payments</a></li>
              <li><a href="#">Shipping</a></li>
              <li><a href="#">Cancellation & Returns</a></li>
              <li><a href="#">FAQ</a></li>
              <li><a href="#">Report Infringement</a></li>
            </ul>
          </div>
          <div class="footer-section">
            <h4>Policy</h4>
            <ul>
              <li><a href="#">Return Policy</a></li>
              <li><a href="#">Terms Of Use</a></li>
              <li><a href="#">Security</a></li>
              <li><a href="#">Privacy</a></li>
              <li><a href="#">Sitemap</a></li>
            </ul>
          </div>
          <div class="footer-section">
            <h4>Social</h4>
            <ul>
              <li><a href="#">Facebook</a></li>
              <li><a href="#">Twitter</a></li>
              <li><a href="#">YouTube</a></li>
            </ul>
          </div>
        </div>
        <div class="footer-bottom">
          <p>&copy; 2023 AGJ.com</p>
        </div>
      </footer>
          <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>  
      </body>
      </html>