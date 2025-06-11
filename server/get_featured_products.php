<?php
include('connection.php');

$sql = "SELECT * FROM products WHERE product_image LIKE 'featured%'";
$featured_products = $conn->query($sql);
?>
