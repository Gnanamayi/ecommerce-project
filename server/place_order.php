<?php
session_start();
include('connection.php');

if(!isset($_SESSION['logged_in'])) {
    header('location: ../checkout.php?message=Please login/register to place an order');
    exit();
}

if (isset($_POST['place_order'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $city = $_POST['city'];
    $address = $_POST['address'];
    $order_status = "not paid";
    $user_id = $_SESSION['user_id'];
    $order_date = date('Y-m-d H:i:s');

    $order_cost = 0; 
    $stmt = $conn->prepare("INSERT INTO orders(order_cost,order_status,user_id,user_phone,user_city,user_address,order_date) 
                            VALUES(?,?,?,?,?,?,?)");
    $stmt->bind_param('isiisss', $order_cost, $order_status, $user_id, $phone, $city, $address, $order_date);
    $stmt_status = $stmt->execute();

    if (!$stmt_status) {
        header('location:index.php');
        exit;
    }

    $order_id = $stmt->insert_id;


    foreach ($_SESSION['cart'] as $product) {
        if (isset(
            $product['product_id'], 
            $product['product_name'], 
            $product['product_image'], 
            $product['product_price'], 
            $product['product_quantity']
        )) {
            $product_id = $product['product_id'];
            $product_name = $product['product_name'];
            $product_image = $product['product_image'];
            $product_price = $product['product_price'];
            $product_quantity = $product['product_quantity'];

            $stmt1 = $conn->prepare("INSERT INTO order_items(order_id, product_id, product_name, product_image, product_price, product_quantity, user_id, order_date)
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt1->bind_param('iissiiis', $order_id, $product_id, $product_name, $product_image, $product_price, $product_quantity, $user_id, $order_date);
            $stmt1->execute();
        }
    }

    $stmt2 = $conn->prepare("SELECT get_order_total(?) AS total");
    $stmt2->bind_param("i", $order_id);
    $stmt2->execute();
    $result = $stmt2->get_result()->fetch_assoc();
    $calculated_total = $result['total'];


    $stmt3 = $conn->prepare("UPDATE orders SET order_cost = ? WHERE order_id = ?");
    $stmt3->bind_param("di", $calculated_total, $order_id);
    $stmt3->execute();


    $_SESSION['total'] = $calculated_total;

    // Redirect to payment
    header("Location: ../payment.php?order_status=order+placed+successfully");
    exit();
}
?>
