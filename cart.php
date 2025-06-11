<?php
session_start();
include('server/connection.php');

// Clean malformed entries from the cart
if (isset($_SESSION['cart']['product_id']) && !is_array($_SESSION['cart']['product_id'])) {
    unset($_SESSION['cart']['product_id']);
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['product_quantity'];
    
    // Check if product exists in database
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if ($product) {
        // Check stock availability
        if ($quantity > $product['stock']) {
            $_SESSION['error'] = "Only {$product['stock']} items available in stock!";
            header("Location: ".$_SERVER['HTTP_REFERER']);
            exit();
        }
        
        // Check if product already in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id) {
                $item['product_quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        // If not found, add new item
        if (!$found) {
            $_SESSION['cart'][] = array(
                'product_id' => $product_id,
                'product_name' => $product['product_name'],
                'product_price' => $product['product_price'],
                'product_image' => $product['product_image'],
                'product_quantity' => $quantity
            );
        }
    }
}

// Handle Remove Product
if (isset($_POST['remove_product'])) {
    $product_id = $_POST['product_id'];
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['product_id'] == $product_id) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }
    // Reindex array after removal
    $_SESSION['cart'] = array_values($_SESSION['cart']);
}

// Handle Edit Quantity
if (isset($_POST['edit_quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['product_quantity'];
    
    // Check stock availability
    $stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if ($quantity > $product['stock']) {
        $_SESSION['error'] = "Cannot update quantity! Only {$product['stock']} items available in stock.";
    } else {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id) {
                $item['product_quantity'] = $quantity;
                break;
            }
        }
    }
}

// Calculate total
function calculateTotalCart() {
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['product_price'] * $item['product_quantity'];
    }
    $_SESSION['total'] = $total;
}

calculateTotalCart();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <!-- Include your CSS files here -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="assests/css/style.css"/>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <!-- Navigation (same as other pages) -->
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
    <!-- Cart Section -->
    <section class="cart container my-5 py-5">
        <div class="container mt-5">
            <h2 class="font-weight-bold">Your Cart</h2>
            <hr style="border: none; height: 4px; background-color: darkmagenta; width: 100px; margin-left: 19px;">
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <table class="mt-5 pt-5">
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>

            <?php if (empty($_SESSION['cart'])): ?>
                <tr>
                    <td colspan="3" class="text-center">Your cart is empty</td>
                </tr>
            <?php else: ?>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <tr>
                        <td>
                            <div class="product-info">
                                <img src="assests/images/<?= $item['product_image'] ?>" alt="">
                                <div class="product-details">
                                    <p><?= $item['product_name'] ?></p>
                                    <small><span>₹</span><?= $item['product_price'] ?></small>
                                    <br>
                                    <form action="cart.php" method="POST">
                                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                        <input type="submit" name="remove_product" class="remove-btn" value="remove">
                                    </form>
                                </div>
                            </div>
                        </td>
                        <td>
                            <form method="POST" action="cart.php">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <input type="number" name="product_quantity" value="<?= $item['product_quantity'] ?>">
                                <input type="submit" class="edit-btn" value="edit" name="edit_quantity">
                            </form>
                        </td>
                        <td>
                            <span>₹</span>
                            <span class="product-price"><?= $item['product_price'] * $item['product_quantity'] ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>

        <div class="cart-total">
            <table>
                <tr>
                    <td>Total</td>
                    <td>₹<?= $_SESSION['total'] ?? 0 ?></td>
                </tr>
            </table>
        </div>

        <div class="checkout-container">
            <?php if (!empty($_SESSION['cart'])): ?>
                <form method="POST" action="checkout.php">
                    <input type="submit" name="checkout" value="Checkout" class="btn checkout-btn" style="background-color:#fb774b; color:white;">
                </form>
            <?php endif; ?>
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

    <!-- Footer (same as other pages) -->
</body>
</html>