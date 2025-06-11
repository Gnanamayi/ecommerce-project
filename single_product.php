<?php 
session_start();
include('server/connection.php');

// Initialize variables
$stock_error = '';
$product_data = [];
$related_products_data = [];

if(isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
  
    // Fetch the current product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    if($stmt) {
        $stmt->bind_param("i", $product_id);
        if($stmt->execute()) {
            $product_result = $stmt->get_result();
            $product_data = $product_result->fetch_assoc();
            
            if(!$product_data) {
                // Product not found, redirect to shop page
                header('location: shop.php');
                exit();
            }
            
            // Fetch the category of the current product
            $stmt_category = $conn->prepare("SELECT product_category FROM products WHERE product_id = ?");
            if($stmt_category) {
                $stmt_category->bind_param("i", $product_id);
                if($stmt_category->execute()) {
                    $category_result = $stmt_category->get_result();
                    $category_row = $category_result->fetch_assoc();
                    $product_category = $category_row['product_category'];
                    
                    // Query for related products based on category
                    $related_stmt = $conn->prepare("SELECT * FROM products WHERE product_category = ? AND product_id != ? LIMIT 4");
                    if($related_stmt) {
                        $related_stmt->bind_param("si", $product_category, $product_id);
                        if($related_stmt->execute()) {
                            $related_result = $related_stmt->get_result();
                            while($row = $related_result->fetch_assoc()) {
                                $related_products_data[] = $row;
                            }
                        }
                        $related_stmt->close();
                    }
                }
                $stmt_category->close();
            }
        }
        $stmt->close();
    }
} else {
    header('location: index.php');
    exit();
}

// Handle Add to Cart
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['product_quantity'];
    
    // Get product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    if($stmt) {
        $stmt->bind_param("i", $product_id);
        if($stmt->execute()) {
            $product = $stmt->get_result()->fetch_assoc();
            
            if($quantity > $product['stock']) {
                $stock_error = "Error: Only {$product['stock']} items available in stock!";
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
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Head content remains the same -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>single_product</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="assests/css/style.css"/>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <!-- Navigation remains the same -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top py-3">
        <div class="container">
          <img src="assests/images/image.png"/>
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

    <!-- Single Product Section -->
    <section class="container single-product my-5 pt-5">
        <div class="row mt-5">
            <?php if(!empty($product_data)): ?>
                <div class="col-lg-5 col-md-6 col-sm-12">
                    <img class="img-fluid w-100 pb-1" src="assests/images/<?= htmlspecialchars($product_data['product_image']) ?>" id="mainImg" alt="<?= htmlspecialchars($product_data['product_name']) ?>">
                    <div class="small-img-group">
                        <div class="small-img-col">
                            <img src="assests/images/<?= htmlspecialchars($product_data['product_image']) ?>" width="100%" class="small-img">
                        </div>
                        <div class="small-img-col">
                            <img src="assests/images/<?= htmlspecialchars($product_data['product_image2']) ?>" width="100%" class="small-img">
                        </div>
                        <div class="small-img-col">
                            <img src="assests/images/<?= htmlspecialchars($product_data['product_image3']) ?>" width="100%" class="small-img">
                        </div>
                        <div class="small-img-col">
                            <img src="assests/images/<?= htmlspecialchars($product_data['product_image4']) ?>" width="100%" class="small-img">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 col-12">
                    
                    <h3 class="py-4"><?= htmlspecialchars($product_data['product_name']) ?></h3>
                    <h2>₹<?= htmlspecialchars($product_data['product_price']) ?></h2>
                    
                    <?php if(!empty($stock_error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($stock_error) ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_data['product_id']) ?>">
                        <input type="hidden" name="product_image" value="<?= htmlspecialchars($product_data['product_image']) ?>">
                        <input type="hidden" name="product_name" value="<?= htmlspecialchars($product_data['product_name']) ?>">
                        <input type="hidden" name="product_price" value="<?= htmlspecialchars($product_data['product_price']) ?>">
                        <p>Available Stock: <?= htmlspecialchars($product_data['stock']) ?></p>
                        <input type="number" name="product_quantity" value="1" min="1" max="<?= htmlspecialchars($product_data['stock']) ?>">
                        <button class="buy-btn" type="submit" name="add_to_cart">Add To Cart</button>
                    </form>
                    
                    <h4 class="mt-5 mb-5">Product details</h4>
                    <span><?= htmlspecialchars($product_data['product_description']) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Related Products Section -->
    <section id="related-products" class="my-5 pb-5">
        <div class="container text-center mt-5 py-5">
            <h3>Related Products</h3>
            <hr class="orange-line"/>
        </div>
        <div class="row mx-auto container-fluid">
            <?php foreach($related_products_data as $related): ?>
                <div class="product text-center col-md-3 col-sm-6">
                    <img src="assests/images/<?= htmlspecialchars($related['product_image']) ?>" class="img-fluid mb-3" alt="<?= htmlspecialchars($related['product_name']) ?>">
                    <div class="star">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <h5 class="p-name"><?= htmlspecialchars($related['product_name']) ?></h5>
                    <h4 class="p-price">₹<?= htmlspecialchars($related['product_price']) ?></h4>
                    <form method="POST" action="single_product.php?product_id=<?= htmlspecialchars($related['product_id']) ?>">
                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($related['product_id']) ?>">
                        <input type="hidden" name="product_image" value="<?= htmlspecialchars($related['product_image']) ?>">
                        <input type="hidden" name="product_name" value="<?= htmlspecialchars($related['product_name']) ?>">
                        <input type="hidden" name="product_price" value="<?= htmlspecialchars($related['product_price']) ?>">
                        <input type="hidden" name="product_quantity" value="1">
                        <button class="buy-btn" type="submit" name="add_to_cart">Add To Cart</button>
                    </form>
                    <a href="single_product.php?product_id=<?= htmlspecialchars($related['product_id']) ?>" class="btn btn-sm btn-outline-primary mt-2">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Footer remains the same -->
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