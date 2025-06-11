<?php include('layouts/header.php'); ?>

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
      <!---HOME-->
      <section id="home">
        <div class="container">
            <h5>NEW ARRIVALS</h5>
            <h1><span>Best Prices</span> This Season</h1>
            <p>Offers the best products for the most affordable prices</p>
            <button> Shop now</button>
        </div>
      </section>
      <!---Brand-->
      <section id="brand" class="container">
        <div class="row">
            <img class="img-fluid col-lg-3 col-md-6 col-sm-12" src="assests/images/brand5.jpeg/">
            <img class="img-fluid col-lg-3 col-md-6 col-sm-12" src="assests/images/brand2.jpeg"/>
            <img class="img-fluid col-lg-3 col-md-6 col-sm-12" src="assests/images/brand3.jpeg"/>
            <img class="img-fluid col-lg-3 col-md-6 col-sm-12" src="assests/images/brand4.jpeg"/>
        </div>
      </section>
      <!-- NEW -->
<section id="new" class="w-100">
  <div class="row p-0 m-0">
    <!-- ONE -->
    <div class="one col-lg-4 col-md-6 col-sm-12 p-2 text-center">
      <img class="product-img" src="assests/images/1.webp" alt="Shoes">
      <h2>Extremely awesome Shoes</h2>
      <button class="text-uppercase">Shop Now</button>
    </div>
    <!-- TWO -->
    <div class="one col-lg-4 col-md-6 col-sm-12 p-2 text-center">
      <img class="product-img" src="assests/images/2.jpg" alt="Jackets">
      <h2>Awesome Jackets</h2>
      <button class="text-uppercase">Shop Now</button>
    </div>
    <!-- THREE -->
    <div class="one col-lg-4 col-md-6 col-sm-12 p-2 text-center">
      <img class="product-img" src="assests/images/3.jpg" alt="Watches">
      <h2>50% OFF Watches</h2>
      <button class="text-uppercase">Shop Now</button>
    </div>
  </div>
</section>
<!-- Featured -->
<section id="featured" class="my-5 pb-5">
  <div class="container text-center mt-5 py-5">
    <h3>Our Featured</h3>
    <hr class="orange-line"/>
    <p>Here you can check out our Featured products</p>
  </div>
  
  <div class="row mx-auto container-fluid">
    <?php include('server/get_featured_products.php');?>

    <?php while($row=$featured_products->fetch_assoc()){ ?>
    <div class="product text-center col-md-4 col-sm-12">
      <img  class="img-fluid mb-3" src="assests/images/<?php echo $row['product_image'];?>"/>
      <div class="star">
        <i class="fas fa-star"></i>
        <i class="fas fa-star"></i>
        <i class="fas fa-star"></i>
        <i class="fas fa-star"></i>
        <i class="fas fa-star"></i>
      </div>
      <h5 class="p-name"><?php echo $row['product_name'];?></h5>
      <h4 class="p-price">₹<?php echo $row['product_price'];?></h4>
      <a href="<?php echo "single_product.php?product_id=". $row['product_id'];?>"><button class="buy-btn">Buy Now</button></a>
    </div>
    <?php } ?>
    
  </div>
 </section>

 <!-- Banner -->
  <section id="banner" class="my-5 py-5">
    <div class="container">
      <h4>MID SEASON SALE</h4>
      <h1>AUTUMN COLLECTION<br/> UPTO 30% OFF</h1>
      <button class="text-uppercase">shop now</button>
      
    </div>
  </section>

  <!-- Clothes -->
<section id="featured" class="my-5">
  <div class="container text-center mt-5 py-5">
    <h3>Dresses & Coats</h3>
    <hr class="orange-line"/>
    <p>Hre you can check out our Amazing Clothes</p>
  </div>
  <div class="row mx-auto container-fluid">
    <?php include('server/get_coats.php'); ?>
    <?php while($row=$coats_products->fetch_assoc()){?>
    <div class="product text-center col-md-4 col-sm-12">
      <img class="img-fluid mb-3" src="assests/images/<?php echo $row['product_image'];?>" />
      <div class="star">
        <i class="fas fa-star"></i>
        <i class="fas fa-star"></i>
        <i class="fas fa-star"></i>
        <i class="fas fa-star"></i>
        <i class="fas fa-star"></i>
      </div>
      <h5 class="p-name"><?php echo $row['product_name'];?></h5>
      <h4 class="p-price">₹<?php echo $row['product_price'];?></h4>
      <a href="<?php echo 'single_product.php?product_id=' . $row['product_id']; ?>"><button class="buy-btn">Buy Now</button></a>

    </div>
<?php } ?>
  </div>
 </section>
<?php include('layouts/footer.php'); ?>


