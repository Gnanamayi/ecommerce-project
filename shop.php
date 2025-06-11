<?php 
session_start();
include('server/connection.php');

// Initialize variables
$category = '';
$price = 100; // Default price
$products = [];

// Handle search form submission
if(isset($_POST['search'])){
    $category = $_POST['category'];
    $price = $_POST['price'];

    // Use prepared statement to filter products
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_category = ? AND product_price <= ?");
    $stmt->bind_param("si", $category, $price);
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    // Default: show all products
    $stmt = $conn->prepare("SELECT * FROM products");
    $stmt->execute();
    $products = $stmt->get_result(); 
}

// Handle Add to Cart from shop page
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['product_quantity'];
    
    // Get product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if($quantity > $product['stock']) {
        $_SESSION['error'] = "Only {$product['stock']} items available in stock!";
        header("Location: shop.php");
        exit();
    } else {
        // Initialize cart if not exists
        if(!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }
        
        // Check if product already exists in cart
        $found = false;
        foreach($_SESSION['cart'] as &$item) {
            if($item['product_id'] == $product_id) {
                $item['product_quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        // If not found, add new item
        if(!$found) {
            $_SESSION['cart'][] = array(
                'product_id' => $product_id,
                'product_name' => $product['product_name'],
                'product_price' => $product['product_price'],
                'product_image' => $product['product_image'],
                'product_quantity' => $quantity
            );
        }
        
        // Redirect to cart page
        header("Location: cart.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SHOP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assests/css/style.css">
    <style>
        .pagination a {
            color: coral;
        }
        .pagination li:hover a {
            color: #fff;
            background-color: coral;
        }
        .buy-btn {
            background-color: #fb774b;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 4px;
        }
        .buy-btn:hover {
            background-color: #e5673b;
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

    <section id="shop-section" class="my-5 py-5">
        <div class="container mt-5 pt-5">
            <div class="row">
                <div class="col-md-3">
                    <h5>Search Products</h5>
                    <hr>
                    <form method="POST" action="shop.php">
                        <p>Category</p>
                        <div class="form-check">
                            <input type="radio" value="shoes" name="category" id="category_one" class="form-check-input" <?= ($category == 'shoes') ? 'checked' : '' ?>>
                            <label for="category_one" class="form-check-label">Shoes</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" value="coats" name="category" id="category_two" class="form-check-input" <?= ($category == 'coats') ? 'checked' : '' ?>>
                            <label for="category_two" class="form-check-label">Coats</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" value="watches" name="category" id="category_three" class="form-check-input" <?= ($category == 'watches') ? 'checked' : '' ?>>
                            <label for="category_three" class="form-check-label">Watches</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" value="bags" name="category" id="category_four" class="form-check-input" <?= ($category == 'bags') ? 'checked' : '' ?>>
                            <label for="category_four" class="form-check-label">Bags</label>
                        </div>

                        <p class="mt-4">Price</p>
                        <input type="range" min="1" name="price" max="2000" class="form-range w-100" value="<?= $price ?>">
                        <div class="d-flex justify-content-between">
                            <small>1</small>
                            <small>2000</small>
                        </div>

                        <button name="search" type="submit" class="btn btn-primary mt-3">Search</button>
                    </form>
                </div>

                <div class="col-md-9">
                    <h3>Our Products</h3>
                    <hr>
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <div class="row">
                        <?php while($row = $products->fetch_assoc()): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 text-center">
                                    <img src="assests/images/<?= $row['product_image'] ?>" class="card-img-top" alt="<?= $row['product_name'] ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= $row['product_name'] ?></h5>
                                        <p class="card-text">₹<?= $row['product_price'] ?></p>
                                        <div class="text-warning">
                                            ★★★★★
                                        </div>
                                        <form method="POST" action="shop.php">
                                            <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                                            <input type="hidden" name="product_image" value="<?= $row['product_image'] ?>">
                                            <input type="hidden" name="product_name" value="<?= $row['product_name'] ?>">
                                            <input type="hidden" name="product_price" value="<?= $row['product_price'] ?>">
                                            <input type="number" name="product_quantity" value="1" min="1" max="<?= $row['stock'] ?>">
                                            <button class="buy-btn mt-2" type="submit" name="add_to_cart">Add To Cart</button>
                                        </form>
                                        <a href="single_product.php?product_id=<?= $row['product_id'] ?>" class="btn btn-sm btn-outline-primary mt-2">View Details</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <nav aria-label="Page navigation example">
        <ul class="pagination mt-5 justify-content-center">
            <li class="page-item"><a class="page-link" href="#">Previous</a></li>
            <li class="page-item"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item"><a class="page-link" href="#">Next</a></li>
        </ul>
    </nav>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>