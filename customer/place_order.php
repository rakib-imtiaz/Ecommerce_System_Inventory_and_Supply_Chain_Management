<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['customer_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['items'])) {
    header('Location: dashboard.php?error=no_items');
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $db->beginTransaction();
    
    // Create order
    $query = "INSERT INTO ordertable (customer_id, order_date, order_status) 
              VALUES (:customer_id, CURRENT_DATE, 'Pending')";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':customer_id', $_SESSION['customer_id']);
    $stmt->execute();
    
    $order_id = $db->lastInsertId();
    
    // Add order items and update stock
    foreach ($_POST['items'] as $item) {
        if (!empty($item['product_id']) && !empty($item['quantity'])) {
            // Verify stock availability
            $query = "SELECT price, stock_level FROM product WHERE product_id = ? FOR UPDATE";
            $stmt = $db->prepare($query);
            $stmt->execute([$item['product_id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product || $product['stock_level'] < $item['quantity']) {
                throw new Exception('Insufficient stock for one or more items');
            }
            
            // Insert order item
            $query = "INSERT INTO orderitem (order_id, product_id, quantity, unit_price) 
                      VALUES (:order_id, :product_id, :quantity, :price)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->bindParam(':product_id', $item['product_id']);
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':price', $product['price']);
            $stmt->execute();
            
            // Update stock level
            $query = "UPDATE product 
                     SET stock_level = stock_level - :quantity 
                     WHERE product_id = :product_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':product_id', $item['product_id']);
            $stmt->execute();
        }
    }
    
    $db->commit();
    header('Location: dashboard.php?success=order_placed');
    
} catch (Exception $e) {
    $db->rollBack();
    header('Location: dashboard.php?error=' . urlencode($e->getMessage()));
} 