<?php
require_once '../../config/database.php';
require_once '../../classes/Order.php';
require_once '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);

// Get parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;

// Get orders
$result = $order->getAllOrders($search, $status, $sort, $limit, $page);
?>

<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-semibold">Orders</h1>
    <a href="<?php echo BASE_URL; ?>/modules/orders/create.php" 
       class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Create New Order</a>
</div>

<!-- Search and Filters -->
<div class="bg-white p-4 rounded-lg shadow mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md"
                   placeholder="Search orders...">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option value="">All Statuses</option>
                <option value="Pending" <?php echo ($status == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="Completed" <?php echo ($status == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                <option value="Shipped" <?php echo ($status == 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
                <option value="Cancelled" <?php echo ($status == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
            <select name="sort" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option value="date_desc" <?php echo ($sort == 'date_desc') ? 'selected' : ''; ?>>Latest First</option>
                <option value="date_asc" <?php echo ($sort == 'date_asc') ? 'selected' : ''; ?>>Oldest First</option>
                <option value="amount_desc" <?php echo ($sort == 'amount_desc') ? 'selected' : ''; ?>>Amount (High to Low)</option>
                <option value="amount_asc" <?php echo ($sort == 'amount_asc') ? 'selected' : ''; ?>>Amount (Low to High)</option>
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" 
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Apply Filters</button>
            <a href="<?php echo BASE_URL; ?>/modules/orders/" 
               class="ml-2 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Reset</a>
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">#<?php echo $row['order_id']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['item_count']; ?> items</td>
                    <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($row['total_amount'], 2); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?php echo $row['order_status'] == 'Completed' ? 'bg-green-100 text-green-800' : 
                                    ($row['order_status'] == 'Pending' ? 'bg-yellow-100 text-yellow-800' : 
                                    ($row['order_status'] == 'Cancelled' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')); ?>">
                            <?php echo $row['order_status']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="<?php echo BASE_URL; ?>/modules/orders/view.php?id=<?php echo $row['order_id']; ?>" 
                           class="text-blue-500 hover:text-blue-700">View</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php
require_once '../../includes/footer.php';
?> 