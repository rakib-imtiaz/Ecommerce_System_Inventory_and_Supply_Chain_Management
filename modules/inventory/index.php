<?php
require_once '../../config/database.php';
require_once '../../classes/Inventory.php';
require_once '../../includes/header.php';

// Debug point 1: Check if file is loaded
error_log("Debug: Inventory index.php loaded");

$database = new Database();
$db = $database->getConnection();

// Debug point 2: Check database connection
if ($db) {
    error_log("Debug: Database connection successful");
} else {
    error_log("Error: Database connection failed");
}

$inventory = new Inventory($db);

// Get parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$stock_status = isset($_GET['stock_status']) ? $_GET['stock_status'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;

// Debug point 3: Log parameters
error_log("Debug: Search: $search, Category: $category, Status: $stock_status, Sort: $sort, Page: $page");

// Get categories for filter
$query = "SELECT category_id, category_name FROM category ORDER BY category_name";
try {
    $categories = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    error_log("Debug: Categories fetched: " . count($categories));
} catch (PDOException $e) {
    error_log("Error: Failed to fetch categories: " . $e->getMessage());
}

// Get inventory items with debug
try {
    $result = $inventory->getAllInventory($search, $category, $stock_status, $sort, $limit, $page);
    error_log("Debug: Inventory query executed");
    error_log("Debug: Number of items found: " . $result->rowCount());
} catch (PDOException $e) {
    error_log("Error: Failed to fetch inventory: " . $e->getMessage());
}

// Get low stock alerts
$low_stock = $inventory->getLowStockProducts(5);
?>

<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-semibold">Inventory Management</h1>
    <a href="<?php echo BASE_URL; ?>/modules/inventory/update.php" 
       class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Stock Update</a>
</div>

<!-- Low Stock Alerts -->
<?php if ($low_stock->rowCount() > 0): ?>
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-yellow-800">Low Stock Alerts</h3>
            <div class="mt-2 text-sm text-yellow-700">
                <ul class="list-disc pl-5 space-y-1">
                    <?php while ($item = $low_stock->fetch(PDO::FETCH_ASSOC)): ?>
                        <li>
                            <?php echo htmlspecialchars($item['name']); ?> 
                            (<?php echo $item['stock_level']; ?> remaining)
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Search and Filters -->
<div class="bg-white p-4 rounded-lg shadow mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md"
                   placeholder="Search products...">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['category_id']; ?>" 
                            <?php echo ($category == $cat['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Stock Status</label>
            <select name="stock_status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option value="">All Status</option>
                <option value="in" <?php echo ($stock_status == 'in') ? 'selected' : ''; ?>>In Stock</option>
                <option value="low" <?php echo ($stock_status == 'low') ? 'selected' : ''; ?>>Low Stock</option>
                <option value="out" <?php echo ($stock_status == 'out') ? 'selected' : ''; ?>>Out of Stock</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
            <select name="sort" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option value="">Latest First</option>
                <option value="stock_asc" <?php echo ($sort == 'stock_asc') ? 'selected' : ''; ?>>Stock Level (Low to High)</option>
                <option value="stock_desc" <?php echo ($sort == 'stock_desc') ? 'selected' : ''; ?>>Stock Level (High to Low)</option>
                <option value="name_asc" <?php echo ($sort == 'name_asc') ? 'selected' : ''; ?>>Name (A-Z)</option>
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" 
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Apply Filters</button>
            <a href="<?php echo BASE_URL; ?>/modules/inventory/" 
               class="ml-2 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Reset</a>
        </div>
    </form>
</div>

<!-- Inventory Table -->
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock Level</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php 
            if ($result && $result->rowCount() > 0) {
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) { 
            ?>
                <tr>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($row['name']); ?>
                        </div>
                        <?php if (!empty($row['sku'])): ?>
                            <div class="text-sm text-gray-500">
                                SKU: <?php echo htmlspecialchars($row['sku']); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?php 
                            $stock_level = (int)$row['stock_level'];
                            $reorder_level = (int)($row['reorder_level'] ?? 10);
                            
                            if ($stock_level === 0) {
                                echo 'bg-red-100 text-red-800';
                            } elseif ($stock_level <= $reorder_level) {
                                echo 'bg-yellow-100 text-yellow-800';
                            } else {
                                echo 'bg-green-100 text-green-800';
                            }
                            ?>">
                            <?php echo $stock_level; ?> units
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <?php echo htmlspecialchars($row['supplier_name'] ?? 'No Supplier'); ?>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium">
                        <a href="update.php?id=<?php echo $row['product_id']; ?>" 
                           class="text-blue-600 hover:text-blue-900 mr-3">Update Stock</a>
                        <a href="#" onclick="viewHistory(<?php echo $row['product_id']; ?>)"
                           class="text-gray-600 hover:text-gray-900">History</a>
                    </td>
                </tr>
            <?php 
                }
            } else {
            ?>
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        No inventory items found
                    </td>
                </tr>
            <?php 
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Stock History Modal -->
<div id="historyModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium">Stock Movement History</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="historyContent"></div>
    </div>
</div>

<script>
function viewHistory(productId) {
    fetch(`${BASE_URL}/modules/inventory/get_history.php?id=${productId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('historyContent').innerHTML = html;
            document.getElementById('historyModal').classList.remove('hidden');
        });
}

function closeModal() {
    document.getElementById('historyModal').classList.add('hidden');
}
</script>

<?php
require_once '../../includes/footer.php';
?> 