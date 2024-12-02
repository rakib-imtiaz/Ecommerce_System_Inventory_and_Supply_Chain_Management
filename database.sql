-- Create the database
CREATE DATABASE IF NOT EXISTS `ecommerce_database`;
USE `ecommerce_database`;
-- ------------------------------
-- Table: category
-- ------------------------------
CREATE TABLE `category` (
  `category_id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_name` VARCHAR(255) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data into category
INSERT INTO `category` (`category_name`) VALUES
('Electronics'),
('Furniture'),
('Clothing'),
('Books'),
('Toys');
-- ------------------------------
-- Table: customer
-- ------------------------------
CREATE TABLE `customer` (
  `customer_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `password` VARCHAR(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data into customer
INSERT INTO `customer` (`name`, `shipping_address`, `password`) VALUES
('Alice Johnson', '742 Evergreen Terrace', 'alicepass123'),
('Bob Smith', '123 Elm Street', 'bobpass456'),
('Charlie Brown', '456 Oak Avenue', 'charliepass789'),
('Diana Prince', '789 Maple Drive', 'dianapass321'),
('Ethan Hunt', '321 Pine Lane', 'ethanpass654');

-- ------------------------------
-- Table: customeremail
-- ------------------------------
CREATE TABLE `customeremail` (
  `customer_id` INT NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`customer_id`, `email`),
  FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data into customeremail
INSERT INTO `customeremail` (`customer_id`, `email`) VALUES
(1, 'alice.johnson@example.com'),
(2, 'bob.smith@example.com'),
(3, 'charlie.brown@example.com'),
(4, 'diana.prince@example.com'),
(5, 'ethan.hunt@example.com');

-- ------------------------------
-- Table: customerphoneno
-- ------------------------------
CREATE TABLE `customerphoneno` (
  `customer_id` INT NOT NULL,
  `phone_number` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`customer_id`, `phone_number`),
  FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data into customerphoneno
INSERT INTO `customerphoneno` (`customer_id`, `phone_number`) VALUES
(1, '123-456-7890'),
(2, '234-567-8901'),
(3, '345-678-9012'),
(4, '456-789-0123'),
(5, '567-890-1234');

-- ------------------------------
-- Table: discount
-- ------------------------------
CREATE TABLE `discount` (
  `discount_code` VARCHAR(50) NOT NULL PRIMARY KEY,
  `discount_percentage` DECIMAL(5,2) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  CONSTRAINT `valid_percentage` CHECK (`discount_percentage` >= 0 AND `discount_percentage` <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data into discount
INSERT INTO `discount` (`discount_code`, `discount_percentage`, `start_date`, `end_date`) VALUES
('FREESHIP', 0.00, '2024-03-01', '2024-03-31'),
('HOLIDAY15', 15.00, '2024-12-01', '2024-12-31'),
('SAVE10', 10.00, '2024-01-01', '2024-12-31'),
('SUMMER5', 5.00, '2024-06-01', '2024-08-31'),
('WELCOME20', 20.00, '2024-01-01', '2024-06-30');

-- ------------------------------
-- Table: employee
-- ------------------------------
CREATE TABLE `employee` (
  `emp_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `dob` DATE NOT NULL,
  `hire_date` DATE NOT NULL,
  `works_in` VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data into employee
INSERT INTO `employee` (`name`, `dob`, `hire_date`, `works_in`) VALUES
('John Doe', '1990-05-15', '2020-03-01', 'HR'),
('Jane Smith', '1985-09-20', '2018-07-15', 'IT'),
('Robert Brown', '1992-11-10', '2021-01-25', 'Sales'),
('Emily Davis', '1988-03-05', '2019-10-10', 'Logistics'),
('Michael Wilson', '1995-06-18', '2022-04-20', 'Marketing');

-- ------------------------------
-- Table: empemail
-- ------------------------------
CREATE TABLE `empemail` (
  `emp_id` INT NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`emp_id`, `email`),
  FOREIGN KEY (`emp_id`) REFERENCES `employee` (`emp_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data into empemail
INSERT INTO `empemail` (`emp_id`, `email`) VALUES
(1, 'john.doe@example.com'),
(2, 'jane.smith@example.com'),
(3, 'robert.brown@example.com'),
(4, 'emily.davis@example.com'),
(5, 'michael.wilson@example.com');

-- ------------------------------
-- Table: supplier
-- ------------------------------
CREATE TABLE `supplier` (
  `supplier_id` INT AUTO_INCREMENT PRIMARY KEY,
  `supplier_name` VARCHAR(255) NOT NULL,
  `contact_info` TEXT,
  `address` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data into supplier
INSERT INTO `supplier` (`supplier_name`, `contact_info`, `address`) VALUES
('TechSupplies Inc.', 'techsupport@supplies.com', '123 Tech Park'),
('HomeGoods Ltd.', 'contact@homegoods.com', '456 Home Street'),
('StyleHub Co.', 'info@stylehub.com', '789 Fashion Avenue'),
('BookWorld', 'service@bookworld.com', '321 Book Lane'),
('ToyGalaxy', 'help@toygalaxy.com', '654 Toy Street');

-- ------------------------------
-- Table: product
-- ------------------------------
CREATE TABLE `product` (
  `product_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL CHECK (`price` >= 0),
  `stock_level` INT NOT NULL DEFAULT 0 CHECK (`stock_level` >= 0),
  `category_id` INT,
  `supplier_id` INT,
  FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data into product
INSERT INTO `product` (`name`, `description`, `price`, `stock_level`, `category_id`, `supplier_id`) VALUES
('Smartphone', 'Latest 5G smartphone', 699.99, 500, 1, 1),
('Sofa', 'Comfortable 3-seater sofa', 899.99, 150, 2, 2),
('T-shirt', 'Cotton crew neck T-shirt', 19.99, 1000, 3, 3),
('Fiction Novel', 'Bestselling fiction book', 14.99, 300, 4, 4),
('Action Figure', 'Superhero action figure', 24.99, 200, 5, 5);

-- ------------------------------
-- Table: warehouse
-- ------------------------------
CREATE TABLE `warehouse` (
  `warehouse_id` INT AUTO_INCREMENT PRIMARY KEY,
  `location` VARCHAR(255) NOT NULL,
  `capacity` INT NOT NULL CHECK (`capacity` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data into warehouse
INSERT INTO `warehouse` (`location`, `capacity`) VALUES
('New York', 10000),
('Los Angeles', 8000),
('Chicago', 12000),
('Houston', 5000),
('Phoenix', 7000);

-- ------------------------------
-- Table: store
-- ------------------------------
CREATE TABLE `store` (
  `store_id` INT AUTO_INCREMENT PRIMARY KEY,
  `store_location` VARCHAR(255) NOT NULL,
  `store_type` ENUM('Physical', 'Online') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data into store
INSERT INTO `store` (`store_location`, `store_type`) VALUES
('Downtown', 'Physical'),
('Online Only', 'Online'),
('Suburban', 'Physical'),
('Mall', 'Physical'),
('Warehouse', 'Physical');

-- ------------------------------
-- Table: inventory
-- ------------------------------
CREATE TABLE `inventory` (
  `inventory_id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `warehouse_id` INT DEFAULT NULL,
  `store_id` INT DEFAULT NULL,
  `quantity` INT NOT NULL CHECK (`quantity` >= 0),
  FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`warehouse_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`store_id`) REFERENCES `store` (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data into inventory
INSERT INTO `inventory` (`product_id`, `warehouse_id`, `store_id`, `quantity`) VALUES
(1, 1, NULL, 300),
(2, 2, 1, 100),
(3, 3, 3, 500),
(4, 4, NULL, 200),
(5, 5, 5, 50);

-- ------------------------------
-- Table: ordertable
-- ------------------------------
CREATE TABLE `ordertable` (
  `order_id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `order_date` DATE NOT NULL,
  `order_status` ENUM('Pending', 'Completed', 'Shipped', 'Cancelled') DEFAULT 'Pending',
  FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data into ordertable
INSERT INTO `ordertable` (`customer_id`, `order_date`, `order_status`) VALUES
(1, '2024-11-01', 'Completed'),
(2, '2024-11-05', 'Pending'),
(3, '2024-11-10', 'Shipped'),
(4, '2024-11-15', 'Cancelled'),
(5, '2024-11-18', 'Completed');

-- ------------------------------
-- Table: orderitem
-- ------------------------------
CREATE TABLE `orderitem` (
  `order_item_id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL CHECK (`quantity` > 0),
  `unit_price` DECIMAL(10,2) NOT NULL CHECK (`unit_price` >= 0),
  FOREIGN KEY (`order_id`) REFERENCES `ordertable` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data into orderitem
INSERT INTO `orderitem` (`order_id`, `product_id`, `quantity`, `unit_price`) VALUES
(1, 1, 1, 699.99),
(2, 2, 1, 899.99),
(3, 3, 2, 19.99),
(4, 4, 1, 14.99),
(5, 5, 3, 24.99);

-- ------------------------------
-- Table: payment
-- ------------------------------
CREATE TABLE `payment` (
  `payment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `payment_date` DATE NOT NULL,
  `payment_amount` DECIMAL(10,2) NOT NULL CHECK (`payment_amount` >= 0),
  `payment_status` ENUM('Pending', 'Paid', 'Refunded') DEFAULT 'Pending',
  FOREIGN KEY (`order_id`) REFERENCES `ordertable` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data into payment
INSERT INTO `payment` (`order_id`, `payment_date`, `payment_amount`, `payment_status`) VALUES
(1, '2024-11-01', 699.99, 'Paid'),
(2, '2024-11-05', 899.99, 'Pending'),
(3, '2024-11-10', 39.98, 'Paid'),
(4, '2024-11-15', 14.99, 'Refunded'),
(5, '2024-11-18', 74.97, 'Paid');

-- ------------------------------
-- Table: returntable
-- ------------------------------
CREATE TABLE `returntable` (
  `return_id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_item_id` INT NOT NULL,
  `return_date` DATE NOT NULL,
  `return_reason` TEXT,
  `return_status` ENUM('Pending', 'Approved', 'Rejected', 'Cancelled') DEFAULT 'Pending',
  FOREIGN KEY (`order_item_id`) REFERENCES `orderitem` (`order_item_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data into returntable
INSERT INTO `returntable` (`order_item_id`, `return_date`, `return_reason`, `return_status`) VALUES
(1, '2024-11-03', 'Defective item', 'Approved'),
(2, '2024-11-07', 'Wrong item sent', 'Pending'),
(3, '2024-11-12', 'Not as described', 'Rejected'),
(4, '2024-11-16', 'Cancelled order', 'Cancelled'),
(5, '2024-11-19', 'Changed mind', 'Approved');

-- ------------------------------
-- Table: review
-- ------------------------------
CREATE TABLE `review` (
  `review_id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `customer_id` INT NOT NULL,
  `rating` INT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `review_text` TEXT,
  `review_date` DATE NOT NULL,
  FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data into review
INSERT INTO `review` (`product_id`, `customer_id`, `rating`, `review_text`, `review_date`) VALUES
(1, 1, 5, 'Excellent product!', '2024-11-02'),
(2, 2, 4, 'Very comfortable.', '2024-11-06'),
(3, 3, 3, 'Okay for the price.', '2024-11-12'),
(4, 4, 4, 'Amazing read!', '2024-11-17'),
(5, 5, 4, 'Great for kids.', '2024-11-20');

-- ------------------------------
-- Table: shipment
-- ------------------------------
CREATE TABLE `shipment` (
  `shipment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `warehouse_id` INT NOT NULL,
  `shipping_date` DATE,
  `shipping_status` ENUM('Pending', 'Shipped', 'In Transit', 'Delivered', 'Cancelled') DEFAULT 'Pending',
  FOREIGN KEY (`order_id`) REFERENCES `ordertable` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`warehouse_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data into shipment
INSERT INTO `shipment` (`order_id`, `warehouse_id`, `shipping_date`, `shipping_status`) VALUES
(1, 1, '2024-11-02', 'Delivered'),
(2, 2, '2024-11-06', 'In Transit'),
(3, 3, '2024-11-11', 'Shipped'),
(4, 4, '2024-11-16', 'Cancelled'),
(5, 5, '2024-11-19', 'Pending');

-- ------------------------------
-- Table: supervisor
-- ------------------------------
CREATE TABLE `supervisor` (
  `supervisor_id` INT AUTO_INCREMENT PRIMARY KEY,
  `emp_id` INT NOT NULL,
  `from_date` DATE NOT NULL,
  `to_date` DATE,
  FOREIGN KEY (`supervisor_id`) REFERENCES `employee` (`emp_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`emp_id`) REFERENCES `employee` (`emp_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data into supervisor
INSERT INTO `supervisor` (`supervisor_id`, `emp_id`, `from_date`, `to_date`) VALUES
(3, 3, '2021-06-01', NULL),
(4, 4, '2020-02-01', NULL),
(1, 1, '2022-01-01', '2023-01-01'),
(2, 2, '2019-01-01', '2021-12-31'),
(5, 5, '2023-02-01', NULL);

-- ------------------------------
-- Table: supplierorder
-- ------------------------------
CREATE TABLE `supplierorder` (
  `sup_order_id` INT AUTO_INCREMENT PRIMARY KEY,
  `supplier_id` INT NOT NULL,
  `warehouse_id` INT NOT NULL,
  `order_date` DATE NOT NULL,
  `expected_del_date` DATE,
  `status` ENUM('Pending', 'In Transit', 'Delivered', 'Completed', 'Cancelled') DEFAULT 'Pending',
  FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`warehouse_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data into supplierorder
INSERT INTO `supplierorder` (`supplier_id`, `warehouse_id`, `order_date`, `expected_del_date`, `status`) VALUES
(1, 1, '2024-10-01', '2024-10-15', 'Delivered'),
(2, 2, '2024-10-05', '2024-10-20', 'Pending'),
(3, 3, '2024-10-10', '2024-10-25', 'In Transit'),
(4, 4, '2024-10-15', '2024-11-01', 'Cancelled'),
(5, 5, '2024-10-20', '2024-11-05', 'Completed');

-- ------------------------------
-- Table: users
-- ------------------------------
CREATE TABLE `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(50) NOT NULL,
  `role` ENUM('admin', 'user') DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert demo users (plain text passwords for demo)
INSERT INTO `users` (`username`, `password`, `role`) VALUES
('admin', 'admin123', 'admin'),
('demo', 'demo123', 'user');
