<?php
require_once '../../config/database.php';
require_once '../../classes/Product.php';
require_once '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $result = $product->create(
            $_POST['name'],
            $_POST['description'],
            $_POST['price'],
            $_POST['stock_level'],
            $_POST['category_id'],
            $_POST['supplier_id']
        );

        if ($result) {
            header("Location: " . BASE_URL . "/modules/products/index.php");
            exit();
        }
    } catch (Exception $e) {
        $error = "Error creating product: " . $e->getMessage();
    }
}

// Get categories and suppliers for dropdowns
$query = "SELECT category_id, category_name FROM category ORDER BY category_name";
$categories = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT supplier_id, supplier_name FROM supplier ORDER BY supplier_name";
$suppliers = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="mb-6">
    <h1 class="text-3xl font-semibold">Add New Product</h1>
</div>

<?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="bg-white shadow-md rounded-lg p-6">
    <form action="" method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                <input type="text" name="name" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                <input type="number" name="price" step="0.01" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Stock Level</label>
                <input type="number" name="stock_level" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select name="category_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">Select Category</option>
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
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">Select Supplier</option>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?php echo $supplier['supplier_id']; ?>">
                            <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end">
            <a href="<?php echo BASE_URL; ?>/modules/products/"
                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2">Cancel</a>
            <button type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add Product</button>
        </div>
    </form>
</div>

<?php
require_once '../../includes/footer.php';
?>