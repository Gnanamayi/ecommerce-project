-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Apr 25, 2025 at 07:27 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `php_project`
--

DELIMITER $$
--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `GetAllUsers` () RETURNS TEXT CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC BEGIN
    DECLARE result TEXT DEFAULT '';
    
    SELECT GROUP_CONCAT(
        CONCAT('ID: ', user_id, 
               ', Name: ', user_name, 
               ', Email: ', user_email,
               ', Role: ', roles) 
        SEPARATOR '\n'
    ) INTO result
    FROM users;
    
    RETURN result;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `GetTopSellingProducts` () RETURNS TEXT CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC BEGIN
    DECLARE result TEXT DEFAULT '';
    
    SELECT GROUP_CONCAT(
        CONCAT('Product ID: ', p.product_id, 
               ', Name: ', p.product_name, 
               ', Total Sold: ', IFNULL(SUM(oi.product_quantity), 0),
               ', Total Orders: ', COUNT(oi.item_id))
        SEPARATOR '\n'
    ) INTO result
    FROM products p
    LEFT JOIN order_items oi ON p.product_id = oi.product_id
    GROUP BY p.product_id, p.product_name
    ORDER BY COUNT(oi.item_id) DESC, SUM(oi.product_quantity) DESC
    LIMIT 3;
    
    RETURN result;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `get_customer_orders` (`customer_id` INT) RETURNS INT(11) DETERMINISTIC BEGIN
    DECLARE order_count INT;

    SELECT COUNT(*)
    INTO order_count
    FROM orders
    WHERE user_id = customer_id;

    RETURN order_count;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `get_order_total` (`o_id` INT) RETURNS DECIMAL(10,2) DETERMINISTIC BEGIN
    DECLARE total DECIMAL(10,2);

    SELECT SUM(oi.product_price * oi.product_quantity) 
    INTO total
    FROM order_items oi
    WHERE oi.order_id = o_id;

    RETURN total;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `match_filter` (`input_category` VARCHAR(255), `input_price` INT, `product_category` VARCHAR(255), `product_price` INT) RETURNS TINYINT(1) DETERMINISTIC BEGIN
  RETURN (product_category = input_category AND product_price <= input_price);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `update_my_product_price` (`pid` INT, `sid` INT, `new_price` DECIMAL(10,2)) RETURNS INT(11) DETERMINISTIC BEGIN
    UPDATE products
    SET product_price = new_price
    WHERE product_id = pid AND seller_id = sid;
    
    RETURN ROW_COUNT();
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_cost` decimal(10,2) NOT NULL,
  `order_status` varchar(50) DEFAULT 'not paid',
  `user_id` int(11) NOT NULL,
  `user_phone` varchar(20) DEFAULT NULL,
  `user_city` varchar(100) DEFAULT NULL,
  `user_address` text DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_cost`, `order_status`, `user_id`, `user_phone`, `user_city`, `user_address`, `order_date`) VALUES
(1, 299.00, 'not paid', 1, '92929', 'jdsxhsxn', ' xbd h', '2025-04-23 23:23:01'),
(2, 1598.00, 'not paid', 3, '92929', 'jcssjc', 'slcmsl', '2025-04-24 13:58:31'),
(3, 1598.00, 'not paid', 3, '1111', 'jcssjc', 'vdv', '2025-04-24 13:58:49'),
(4, 1200.00, 'not paid', 3, '92929', 'hsx', 'xdx', '2025-04-24 15:23:40'),
(5, 598.00, 'not paid', 3, '1111', 'jcssjc', 'dmcnejc', '2025-04-24 16:05:44'),
(6, 2392.00, 'not paid', 3, '11111', 'jcssjc', 'dmcnejc', '2025-04-24 19:04:24');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` datetime DEFAULT NULL,
  `product_price` decimal(10,2) DEFAULT NULL,
  `product_quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `product_name`, `product_image`, `user_id`, `order_date`, `product_price`, `product_quantity`) VALUES
(1, 1, 1, 'White Shoes ', 'featured1.jpeg', 1, '2025-04-23 23:23:01', 299.00, 1),
(2, 2, 4, 'Sports Bag', 'featured3.jpeg', 3, '2025-04-24 13:58:31', 799.00, 2),
(3, 3, 4, 'Sports Bag', 'featured3.jpeg', 3, '2025-04-24 13:58:49', 799.00, 2),
(4, 4, 5, 'Beige Coat', 'clothes1.jpeg', 3, '2025-04-24 15:23:40', 1200.00, 1),
(5, 5, 1, 'White Shoes ', 'featured1.jpeg', 3, '2025-04-24 16:05:44', 299.00, 2),
(6, 6, 1, 'White Shoes ', 'featured1.jpeg', 3, '2025-04-24 19:04:24', 299.00, 8);

--
-- Triggers `order_items`
--
DELIMITER $$
CREATE TRIGGER `prevent_negative_stock` BEFORE INSERT ON `order_items` FOR EACH ROW BEGIN
    DECLARE available INT;
    SELECT stock INTO available FROM products WHERE product_id = NEW.product_id;
    IF NEW.product_quantity > available THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Insufficient stock';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_product_stock` AFTER INSERT ON `order_items` FOR EACH ROW BEGIN
    UPDATE products
    SET stock = stock - NEW.product_quantity
    WHERE product_id = NEW.product_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_category` varchar(100) DEFAULT NULL,
  `product_description` text DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `product_image2` varchar(255) DEFAULT NULL,
  `product_image3` varchar(255) DEFAULT NULL,
  `product_image4` varchar(255) DEFAULT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_special_offer` varchar(100) DEFAULT NULL,
  `product_color` varchar(50) DEFAULT NULL,
  `stock` int(11) NOT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `low_stock` tinyint(1) DEFAULT 0
) ;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `product_category`, `product_description`, `product_image`, `product_image2`, `product_image3`, `product_image4`, `product_price`, `product_special_offer`, `product_color`, `stock`, `seller_id`, `low_stock`) VALUES
(1, 'White Shoes ', 'Shoes', 'awesome white shoes', 'featured1.jpeg', 'featured1.jpeg', 'featured1.jpeg', 'featured1.jpeg', 499.00, '0', 'White', 23, 7, 0),
(2, 'Zara Handbag', 'Bags', 'aesthetic handbags', 'featured2.jpeg', 'featured2.jpeg', 'featured2.jpeg', 'featured2.jpeg', 399.00, '0', 'White', 11, 7, 0),
(4, 'Sports Bag', 'Bags', 'Stylish Bags for everyday ', 'featured3.jpeg', 'featured3.jpeg', 'featured3.jpeg', 'featured3.jpeg', 799.00, '0', 'Purple', 1, 7, 1),
(5, 'Beige Coat', 'Coats', 'coats for men', 'clothes1.jpeg', 'clothes1.jpeg', 'clothes1.jpeg', 'clothes1.jpeg', 1200.00, '0', 'Beige', 13, 9, 0),
(6, 'H&M Black Coat', 'Coats', 'Hot and classy black coat for men', 'clothes2.jpeg', 'clothes2.jpeg', 'clothes2.jpeg', 'clothes2.jpeg', 1449.00, '0', 'Black', 14, 8, 0),
(7, 'Brown Shirt', 'Shirts', 'top notch shirts for men', 'clothes3.jpeg', 'clothes3.jpeg', 'clothes3.jpeg', 'clothes3.jpeg', 1100.00, '0', 'Brown', 15, 10, 0),
(8, 'Silver Watch', 'Watches', 'Timeless watches for men', '3.jpg', '3.jpg', '3.jpg', '3.jpg', 699.00, '0', 'Silver', 7, 2, 0),
(9, 'VJ Shoes', 'Shoes', 'Black stunning shoes for men', '97.jpg', '97.jpg', '97.jpg', '97.jpg', 899.00, '0', 'Black', 6, 2, 0),
(10, 'Pink Cool Bags', 'Bags', 'All purpose Bags', '91.webp', '91.webp', '91.webp', '91.webp', 479.00, '0', 'Pink', 5, 1, 0);

--
-- Triggers `products`
--
DELIMITER $$
CREATE TRIGGER `update_low_stock_flag` BEFORE UPDATE ON `products` FOR EACH ROW BEGIN
    IF NEW.stock< 10 THEN
        SET NEW.low_stock = 1;
    ELSE
        SET NEW.low_stock = 0;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `related_products_view`
-- (See below for the actual view)
--
CREATE TABLE `related_products_view` (
`product_id` int(11)
,`product_name` varchar(255)
,`product_category` varchar(100)
,`product_description` text
,`product_image` varchar(255)
,`product_price` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `seller_dashboard_products`
-- (See below for the actual view)
--
CREATE TABLE `seller_dashboard_products` (
`seller_id` int(11)
,`seller_name` varchar(100)
,`product_id` int(11)
,`product_name` varchar(255)
,`product_category` varchar(100)
,`product_price` decimal(10,2)
,`product_special_offer` varchar(100)
,`product_color` varchar(50)
,`product_image` varchar(255)
,`product_description` text
);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `roles` varchar(50) DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_name`, `user_email`, `user_password`, `roles`) VALUES
(1, 'ad', 'hi', '$2y$10$EgG4oTJY6p62k8Hg7TPl0.gTgc8tgrev.zxG7DbHUr9GN/h0xBtDy', 'customer'),
(2, 'Alice Johnson', 'p', '$2y$10$5EemYVGhhJn0ix4CSIS6SuV.fydLJFXIdhkUnAH969/aGDr/4k2FO', 'customer'),
(3, 'joydeep', 'joydeep@gmail.com', '$2y$10$.11BISFdNZ8uug6rsk9KPu.TJ.5HShnotCWDZlMpL9p0viSw8df7e', 'customer'),
(4, 'v', 'v', '$2y$10$QRY2kQ9rv.m8UyB1yn28zuZ5WMXAjBa24IHJEeUZCg5d2iLw/d0hi', 'customer'),
(5, 'an', 'an', '$2y$10$tXab8NV3JgQxLlsTy8k5Ze84ndor8.qvvaRBKbdOAAt/nXqWXcml2', 'customer'),
(6, 'a', 'aaa', '$2y$10$vq/L3FGr0ezdkvFCOZhvbe2qNNKACAa6rjYhi7y6YWk5iUkDDX3Ea', 'customer'),
(7, 'v', 'asm', '$2y$10$tx5Pi0Lgl3pLLFhKaS39ZevSPu1TI2EsBQvBbeh.6XfhTrV2SibVq', 'seller'),
(8, 'a', 'aa', '$2y$10$Aympd7IF8AQs.e76Iue6juRy.ZkfG52yRfF7vrxohic.jnEP9amK6', 'seller'),
(9, 'am s', 'ap@gmail.com', '$2y$10$Pwhb9CPCO/ja/qiQjP8v2.KaRAYzyX7nQZokAKdLWREVA.PGQsymK', 'seller'),
(10, 'ann', 'ann@gmail.com', '$2y$10$QFVzA4kQMzpcFeRi8kjLxeyevEe6S496MekCHDMkdKXlgFiiWgbVK', 'seller'),
(11, 'anju', 'anju@gmail.com', '$2y$10$2oCGADqlWeWMijZwsJmpsuskpostw94e1K7bxnhKPuxI9DIQCYYa2', 'admin'),
(13, 'anju1', 'anju1@gmail.com', '$2y$10$tRgkSaIqZHsxh0S4VREo2eTnav9S1DFPbpm02eKLHnSRbYGapzBVy', 'admin'),
(15, 'dean', 'dean@gmail.com', '$2y$10$vyjyJQ20/eY9yE2HAdlnHuZLBqhaH4UutQZksmQORQjYnNk4uuhAG', 'admin');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `prevent_user_delete` BEFORE DELETE ON `users` FOR EACH ROW BEGIN
   IF EXISTS (SELECT 1 FROM orders WHERE user_id = OLD.user_id) THEN
      SIGNAL SQLSTATE '45000' 
      SET MESSAGE_TEXT = 'Cannot delete user with existing orders';
   END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_orders`
-- (See below for the actual view)
--
CREATE TABLE `user_orders` (
`order_id` int(11)
,`order_cost` decimal(10,2)
,`order_status` varchar(50)
,`order_date` datetime
,`user_name` varchar(100)
,`user_email` varchar(100)
);

-- --------------------------------------------------------

--
-- Structure for view `related_products_view`
--
DROP TABLE IF EXISTS `related_products_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `related_products_view`  AS SELECT `products`.`product_id` AS `product_id`, `products`.`product_name` AS `product_name`, `products`.`product_category` AS `product_category`, `products`.`product_description` AS `product_description`, `products`.`product_image` AS `product_image`, `products`.`product_price` AS `product_price` FROM `products` ;

-- --------------------------------------------------------

--
-- Structure for view `seller_dashboard_products`
--
DROP TABLE IF EXISTS `seller_dashboard_products`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `seller_dashboard_products`  AS SELECT `u`.`user_id` AS `seller_id`, `u`.`user_name` AS `seller_name`, `p`.`product_id` AS `product_id`, `p`.`product_name` AS `product_name`, `p`.`product_category` AS `product_category`, `p`.`product_price` AS `product_price`, `p`.`product_special_offer` AS `product_special_offer`, `p`.`product_color` AS `product_color`, `p`.`product_image` AS `product_image`, `p`.`product_description` AS `product_description` FROM (`products` `p` join `users` `u` on(`p`.`seller_id` = `u`.`user_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `user_orders`
--
DROP TABLE IF EXISTS `user_orders`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `user_orders`  AS SELECT `o`.`order_id` AS `order_id`, `o`.`order_cost` AS `order_cost`, `o`.`order_status` AS `order_status`, `o`.`order_date` AS `order_date`, `u`.`user_name` AS `user_name`, `u`.`user_email` AS `user_email` FROM (`orders` `o` join `users` `u` on(`o`.`user_id` = `u`.`user_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_email` (`user_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
