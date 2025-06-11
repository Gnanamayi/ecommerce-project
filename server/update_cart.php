<?php
session_start();
include 'connection.php';

if (isset($_SESSION['user_id']) && isset($_POST['product_id']) && isset($_POST['product_quantity'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $product_quantity = $_POST['product_quantity'];

    $stmt = $conn->prepare("UPDATE cart SET product_quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("iii", $product_quantity, $user_id, $product_id);
    $stmt->execute();
}
?>
