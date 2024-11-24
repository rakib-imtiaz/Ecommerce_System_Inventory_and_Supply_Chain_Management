<?php
require_once '../../config/database.php';
require_once '../../classes/Inventory.php';
require_once '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$inventory = new Inventory($db);

// Get product ID if provided
$product_id = isset($_GET['id']) ? $_GET['id'] : '';

// Get product details if ID is provided
$product = null;
if ($product_id) {
    $query = "SELECT p.*, c.category_name, s.supplier_name 
             FROM product p
             LEFT JOIN category c ON p.category_id = c.category_id
             LEFT JOIN supplier s ON p.supplier_id = s.supplier_id
             WHERE p.product_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $quantity = (int)$_POST['quantity'];
        $type = $_POST['type'];
        $notes = $_POST['notes'];

        // Convert quantity to negative if it's a reduction
        if ($type === 'reduction') {
            $quantity = -$quantity;
        }

        $inventory->updateStock($_POST['product_id'], $quantity, $type, $notes);
        
        $success = "Stock updated successfully";
        
        // Refresh product details after update
        if ($product_id) {
            $stmt = $db->prepare($query);
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get products for dropdown if no specific product is selected
if (!$product_id) {
    $query = "SELECT product_id, name, sku, stock_level FROM product ORDER BY name";
    $products = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-semibold">Stock Update</h1>
    <a href="<?php echo BASE_URL; ?>/modules/inventory/" 
       class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Back to Inventory</a>
</div>

<?php if (isset($success)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?php echo $success; ?>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="bg-white shadow-md rounded-lg p-6">
    <form method="POST" class="space-y-6">
        <?php if ($product): ?>
            <!-- Single Product Update -->
            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-lg font-medium mb-4">Product Details</h2>
                    <dl class="grid grid-cols-2 gap-4">
                        <dt class="text-gray-600">Name</dt>
                        <dd><?php echo htmlspecialchars($product['name']); ?></dd>
                        
                        <dt class="text-gray-600">SKU</dt>
                        <dd><?php echo htmlspecialchars($product['sku']); ?></dd>
                        
                        <dt class="text-gray-600">Current Stock</dt>
                        <dd class="font-semibold"><?php echo $product['stock_level']; ?> units</dd>
                        
                        <dt class="text-gray-600">Reorder Level</dt>
                        <dd><?php echo $product['reorder_level']; ?> units</dd>
                    </dl>
                </div>
            </div>
        <?php else: ?>
            <!-- Product Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Product</label>
                <select name="product_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Choose a product...</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?php echo $p['product_id']; ?>">
                            <?php echo htmlspecialchars($p['name']); ?> 
                            (SKU: <?php echo htmlspecialchars($p['sku']); ?>, 
                            Current Stock: <?php echo $p['stock_level']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Update Type</label>
                <select name="type" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="addition">Stock Addition</option>
                    <option value="reduction">Stock Reduction</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                <input type="number" name="quantity" required min="1"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
            <textarea name="notes" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                      placeholder="Enter any additional notes..."></textarea>
        </div>

        <div class="flex justify-end">
            <button type="submit" 
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Update Stock
            </button>
        </div>
    </form>
</div>

<?php
require_once '../../includes/footer.php';
?> 