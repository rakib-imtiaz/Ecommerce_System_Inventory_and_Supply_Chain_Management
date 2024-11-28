<?php
require_once '../../config/database.php';
require_once '../../classes/Inventory.php';
require_once '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$inventory = new Inventory($db);

// Get categories and suppliers for dropdowns
$categories = $db->query("SELECT * FROM category ORDER BY category_name")->fetchAll();
$suppliers = $db->query("SELECT * FROM supplier ORDER BY supplier_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'price' => $_POST['price'],
            'stock_level' => $_POST['stock_level'],
            'category_id' => $_POST['category_id'],
            'supplier_id' => $_POST['supplier_id']
        ];

        if ($inventory->addProduct($data)) {
            $success = "Product added successfully";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-semibold">Add New Product</h1>
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Product Name</label>
                <input type="text" name="name" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select name="category_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Select Category...</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>">
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                <select name="supplier_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Select Supplier...</option>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?php echo $supplier['supplier_id']; ?>">
                            <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                <input type="number" name="price" required step="0.01" min="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Initial Stock Level</label>
                <input type="number" name="stock_level" required min="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea name="description" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
        </div>

        <div class="flex justify-end">
            <button type="submit" 
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Add Product
            </button>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?> 