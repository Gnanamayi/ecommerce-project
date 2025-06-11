<?php
session_start();
include('server/connection.php');
// Display success message if exists
if(isset($_SESSION['message'])) {
  echo '<div class="alert alert-success">'.$_SESSION['message'].'</div>';
  unset($_SESSION['message']);
}
// Check if user is logged in and has the "seller" role

if (!isset($_SESSION['logged_in']) || $_SESSION['roles'] !== 'seller') {
    echo "Access denied.";
    exit();
}


// Get seller's products from the view
$seller_id = $_SESSION['user_id'];
$query = "SELECT * FROM seller_dashboard_products WHERE seller_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $seller_id);
$stmt->execute();
$products = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assests/css/style.css"/>
    <style>
        .product-card {
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .product-img {
            height: 200px;
            object-fit: cover;
        }
        .section-title {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
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
          <h1>WELCOME SELLER <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
        </div>
      </section>
      
      <section class="my-5 py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="section-title">Your Products</h2>
                    <a href="add_product.php" class="btn btn-primary mb-4">Add New Product</a>
                    
                    <div class="row">
                        <?php if($products->num_rows > 0) { ?>
                            <?php while($row = $products->fetch_assoc()) { ?>
                                <div class="col-md-4">
                                    <div class="card product-card">
                                        <img src="assests/images/<?php echo $row['product_image']; ?>" class="card-img-top product-img" alt="<?php echo $row['product_name']; ?>">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo $row['product_name']; ?></h5>
                                            <p class="card-text">
                                                <strong>Category:</strong> <?php echo $row['product_category']; ?><br>
                                                <strong>Price:</strong> Rs.<?php echo $row['product_price']; ?><br>
                                                <?php if($row['product_special_offer'] > 0) { ?>
                                                    <span class="text-danger"><strong>Special Offer:</strong> $<?php echo $row['product_special_offer']; ?></span><br>
                                                <?php } ?>
                                                <strong>Color:</strong> <?php echo $row['product_color']; ?>
                                            </p>
                                            <p class="card-text"><?php echo substr($row['product_description'], 0, 100); ?>...</p>
                                            <div class="d-flex justify-content-between">
                                                <a href="edit_product.php?id=<?php echo $row['product_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="delete_product.php?id=<?php echo $row['product_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <div class="col-12">
                                <div class="alert alert-info">You haven't added any products yet.</div>
                            </div>
                        <?php } ?>
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