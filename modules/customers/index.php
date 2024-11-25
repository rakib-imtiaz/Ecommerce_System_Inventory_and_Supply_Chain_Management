<?php
require_once '../../config/database.php';
require_once '../../classes/Customer.php';
require_once '../../includes/header.php';

// Initialize Database and Customer
$database = new Database();
$db = $database->getConnection();
$customer = new Customer($db);

// Get parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;

// Get customers
$result = $customer->getAllCustomers($search, $sort, $limit, $page);
?>

<div class="mb-6">
    <h1 class="text-3xl font-semibold text-gray-800">Customers</h1>
    <p class="text-gray-600 mt-2">Manage and view customer information</p>
</div>

<!-- Search and Filters -->
<div class="bg-white p-4 rounded-lg shadow mb-6 fade-in">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input type="text" 
                   name="search" 
                   value="<?php echo htmlspecialchars($search); ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                   placeholder="Search by name, email, or phone...">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
            <select name="sort" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">Latest First</option>
                <option value="name_asc" <?php echo ($sort == 'name_asc') ? 'selected' : ''; ?>>Name (A-Z)</option>
                <option value="name_desc" <?php echo ($sort == 'name_desc') ? 'selected' : ''; ?>>Name (Z-A)</option>
                <option value="orders_desc" <?php echo ($sort == 'orders_desc') ? 'selected' : ''; ?>>Most Orders</option>
                <option value="spent_desc" <?php echo ($sort == 'spent_desc') ? 'selected' : ''; ?>>Highest Spent</option>
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" 
                    class="bg-emerald-500 text-white px-4 py-2 rounded-md hover:bg-emerald-600 transition-colors duration-200">
                <i class="fas fa-search mr-2"></i> Search
            </button>
        </div>
    </form>
</div>

<!-- Customers Table -->
<div class="bg-white shadow-md rounded-lg overflow-hidden fade-in">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Spent</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <img class="h-10 w-10 rounded-full" 
                                     src="https://ui-avatars.com/api/?name=<?php echo urlencode($row['name']); ?>&background=10B981&color=fff" 
                                     alt="<?php echo htmlspecialchars($row['name']); ?>">
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($row['shipping_address']); ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($row['email']); ?></div>
                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($row['phone']); ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800">
                            <?php echo $row['total_orders']; ?> orders
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        $<?php echo number_format($row['total_spent'], 2); ?>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium">
                        <a href="view.php?id=<?php echo $row['customer_id']; ?>" 
                           class="text-emerald-600 hover:text-emerald-900">View Details</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../../includes/footer.php';
?> 