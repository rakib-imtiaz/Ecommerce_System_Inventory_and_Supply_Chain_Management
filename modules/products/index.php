<?php
require_once '../../config/database.php';
require_once '../../classes/Product.php';
require_once '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

// Get parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Items per page

// Get total products for pagination
$total_products = $product->getTotalProducts($search, $category);
$total_pages = ceil($total_products / $limit);

// Get products
$result = $product->getAllProducts($search, $category, $sort, $limit, $page);

// Get categories for filter
$query = "SELECT category_id, category_name FROM category ORDER BY category_name";
$categories = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-semibold">Products</h1>
    <a href="<?php echo BASE_URL; ?>/modules/products/add.php" 
       class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Add New Product</a>
</div>

<!-- Search and Filters -->
<div class="bg-white p-4 rounded-lg shadow mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                   placeholder="Search products...">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <select name="category" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
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
            <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
            <select name="sort" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="">Latest First</option>
                <option value="price_asc" <?php echo ($sort == 'price_asc') ? 'selected' : ''; ?>>Price (Low to High)</option>
                <option value="price_desc" <?php echo ($sort == 'price_desc') ? 'selected' : ''; ?>>Price (High to Low)</option>
                <option value="name_asc" <?php echo ($sort == 'name_asc') ? 'selected' : ''; ?>>Name (A-Z)</option>
                <option value="name_desc" <?php echo ($sort == 'name_desc') ? 'selected' : ''; ?>>Name (Z-A)</option>
                <option value="stock_asc" <?php echo ($sort == 'stock_asc') ? 'selected' : ''; ?>>Stock (Low to High)</option>
                <option value="stock_desc" <?php echo ($sort == 'stock_desc') ? 'selected' : ''; ?>>Stock (High to Low)</option>
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" 
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Apply Filters</button>
            <a href="<?php echo BASE_URL; ?>/modules/products/" 
               class="ml-2 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Reset</a>
        </div>
    </form>
</div>

<!-- Products Table -->
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php 
            if ($result->rowCount() > 0) {
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) { 
            ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['name']); ?></div>
                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($row['description'], 0, 50)) . '...'; ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($row['price'], 2); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?php echo $row['stock_level'] > 10 ? 'bg-green-100 text-green-800' : 
                                    ($row['stock_level'] > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                            <?php echo $row['stock_level']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="<?php echo BASE_URL; ?>/modules/products/edit.php?id=<?php echo $row['product_id']; ?>" 
                           class="text-blue-500 hover:text-blue-700 mr-4">Edit</a>
                        <a href="<?php echo BASE_URL; ?>/modules/products/delete.php?id=<?php echo $row['product_id']; ?>" 
                           class="text-red-500 hover:text-red-700"
                           onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                    </td>
                </tr>
            <?php 
                }
            } else { 
            ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No products found</td>
                </tr>
            <?php 
            } 
            ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div class="mt-4 flex justify-center">
    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo ($page-1); ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&sort=<?php echo urlencode($sort); ?>" 
               class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                Previous
            </a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&sort=<?php echo urlencode($sort); ?>" 
               class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo ($page == $i) ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50'; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo ($page+1); ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&sort=<?php echo urlencode($sort); ?>" 
               class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                Next
            </a>
        <?php endif; ?>
    </nav>
</div>
<?php endif; ?>

<?php
require_once '../../includes/footer.php';
?> 