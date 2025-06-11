<?php
session_start();
include('server/connection.php');
if(!isset($_SESSION['logged_in'])){
  header('location: login.php');
  exit;
}

if(isset($_GET['logout'])){
  if(isset($_SESSION['logged_in'])){
    unset($_SESSION['logged_in']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_name']);
    header('location: login.php');
    exit;
  }
}

if(isset($_POST['change_password'])){
  $password=$_POST['password'];
  $confirmPassword=$_POST['confirmPassword'];
  $user_email=$_SESSION['user_email'];
  if ($password !== $confirmPassword) {
    header('location: account.php?error=passwords dont match');
    exit();
  } else if (strlen($password) < 6) {
    header('location: account.php?error=passwords must be at least 6 characters');
    exit();
  }else{
    $stmt=$conn->prepare("UPDATE users set user_password=? where user_email=?");
    $stmt->bind_param('ss',md5($password),$user_email);
    if($stmt->execute()){
      header('location: account.php?message=password has been updated successfully');
    }else{
      header('location: account.php?message=couldnt update password'); 
    }
  }
}

if(isset($_SESSION['logged_in'])){
  $user_id=$_SESSION['user_id'];
  // Fetch orders for the user
  $user_email = $_SESSION['user_email'];
  $stmt = $conn->prepare("SELECT * FROM user_orders WHERE user_email=?");
  $stmt->bind_param('s', $_SESSION['user_email']);
  $stmt->execute();
  $orders = $stmt->get_result();

  // Fetch the total number of orders
  $customer_orders = $conn->query("SELECT get_customer_orders(" . $user_id . ") AS order_count");
  $order_count = $customer_orders->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="assests/css/style.css"/>
</head>
<body>
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

    <!-- Account -->
    <section class="my-5 py-5">
        <div class="row container mx-auto">
            <div class="text-center mt-3 pt-5 col-lg-6 col-md-12 col-sm-12">
                <p class="text-center" style="color:green"><?php if(isset($_GET['register_success'])){echo $_GET['register_success'];}?></p>
                <p class="text-center" style="color:green"><?php if(isset($_GET['login_success'])){echo $_GET['login_success'];}?></p>
                <h3 class="font-weight-bold">Account info</h3>
                <hr class="mx-auto">
                <div class="account-info">
                    <p>Name <span><?php if(isset($_SESSION['user_name'])){echo $_SESSION['user_name'];}?></span></p>
                    <p>Email <span><?php if(isset($_SESSION['user_email'])){ echo $_SESSION['user_email'];}?></span> </p>
                    <p><a href="#orders" id="orders-btn">Your orders</a></p>
                    <p><a href="account.php?logout=1" id="logout-btn">Logout</a></p>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 col-sm-12">
                <form method="POST" action="account.php" id="account-form">
                    <p class="text-center" style="color:red"><?php if(isset($_GET['error'])){echo $_GET['error'];}?></p>
                    <p class="text-center" style="color:green"><?php if(isset($_GET['message'])){echo $_GET['message'];}?></p>
                    <h3>Change Password</h3>
                    <hr class="mx-auto">
                    <div class="form-group"><label for="">Password</label>
                    <input type="password" id="account-password" class="form-control" name="password" placeholder="Password" required></div>
                    <div class="form-group"><label for="">Confirm Password</label>
                        <input type="password" id="account-password-confirm" class="form-control" name="confirmPassword" placeholder="Password" required></div>
                    <div class="form-group">
                        <input type="submit" name="change_password" value="Change Password" class="btn" id="change-pass-btn">
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Display the total number of orders -->
    <section class="my-5 py-5">
        <div class="container">
            <h3 class="text-center">Total Orders: <?php echo $order_count['order_count']; ?></h3>
        </div>
    </section>

    <!-- Orders -->
    <section id="orders" class="orders container my-5 py-3">
        <div class="container mt-2">
            <h2 class="font-weight-bold text-center">Your Orders</h2>
            <hr class="mx-auto" style="border: none; height: 4px; background-color: darkmagenta; width: 100px; margin-left: 19px;" />
        </div>
        <table class="mt-5 pt-5">
            <tr>
                <th>Order id</th>
                <th>Order cost</th>
                <th>Order status</th>
                <th>Order Date</th>
                <th>Order details</th>
            </tr>
            <?php while($row = $orders->fetch_assoc()) { ?>
            <tr> 
                <td>
                    <span><?php echo $row['order_id']; ?></span>
                </td> 
                <td><span><?php echo $row['order_cost']; ?></span></td>
                <td><span><?php echo $row['order_status']; ?></span></td>
                <td><span><?php echo $row['order_date']; ?></span></td>
                <td>
                    <form method="POST" action="order_details.php">
                        <input name="order_id" type="hidden" value="<?php echo $row['order_id']; ?>"/>
                        <input class="btn order-details-btn" name="order_details_btn" value="details" type="submit">
                    </form>
                </td>
            </tr>
            <?php } ?>
        </table>
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
