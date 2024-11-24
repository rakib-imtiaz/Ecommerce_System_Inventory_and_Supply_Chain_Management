<?php
require_once '../../config/database.php';
require_once '../../classes/Product.php';

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

// Get product ID from URL
$product_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');

try {
    // Attempt to delete the product
    if ($product->delete($product_id)) {
        header("Location: " . BASE_URL . "/modules/products/index.php");
    } else {
        throw new Exception("Unable to delete product");
    }
} catch (Exception $e) {
    // If there's an error, redirect back to the product list with an error message
    header("Location: " . BASE_URL . "/modules/products/index.php?error=" . urlencode($e->getMessage()));
}
exit();
?> 