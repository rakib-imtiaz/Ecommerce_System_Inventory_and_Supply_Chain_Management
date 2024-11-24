<?php
require_once '../../config/database.php';
require_once '../../classes/Customer.php';
require_once '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$customer = new Customer($db);

// Get customer ID from URL
$customer_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');

// Get customer details
$customer_data = $customer->getCustomer($customer_id);
if (!$customer_data) {
    header("Location: " . BASE_URL . "/modules/customers/");
    exit();
}

// Get customer orders
$customer_orders = $customer->getCustomerOrders($customer_id);
?>

<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-semibold">Customer Profile</h1>
    <a href="<?php echo BASE_URL; ?>/modules/customers/" 
       class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Back to Customers</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <!-- Customer Details -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Customer Details</h2>
        <dl class="grid grid-cols-2 gap-4">
            <dt class="text-gray-600">Name</dt>
            <dd><?php echo htmlspecialchars($customer_data['name']); ?></dd>
            
            <dt class="text-gray-600">Email</dt>
            <dd><?php echo htmlspecialchars($customer_data['email']); ?></dd>
            
            <dt class="text-gray-600">Phone</dt>
            <dd><?php echo htmlspecialchars($customer_data['phone']); ?></dd>
            
            <dt class="text-gray-600">Shipping Address</dt>
            <dd><?php echo nl2br(htmlspecialchars($customer_data['shipping_address'])); ?></dd>
        </dl>
    </div>

    <!-- Customer Statistics -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Customer Statistics</h2>
        <dl class="grid grid-cols-2 gap-4">
            <dt class="text-gray-600">Total Orders</dt>
            <dd class="text-2xl font-bold"><?php echo $customer_data['total_orders']; ?></dd>
            
            <dt class="text-gray-600">Total Spent</dt>
            <dd class="text-2xl font-bold">$<?php echo number_format($customer_data['total_spent'], 2); ?></dd>
        </dl>
    </div>
</div>

<!-- Order History -->
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <h2 class="text-xl font-semibold p-6">Order History</h2>
    <table class="min-w-full">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php while ($order = $customer_orders->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td class="px-6 py-4">#<?php echo $order['order_id']; ?></td>
                    <td class="px-6 py-4"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                    <td class="px-6 py-4"><?php echo $order['item_count']; ?> items</td>
                    <td class="px-6 py-4">$<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td class="px-6 py-4">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?php echo $order['order_status'] == 'Completed' ? 'bg-green-100 text-green-800' : 
                                    ($order['order_status'] == 'Pending' ? 'bg-yellow-100 text-yellow-800' : 
                                    ($order['order_status'] == 'Cancelled' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')); ?>">
                            <?php echo $order['order_status']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="<?php echo BASE_URL; ?>/modules/orders/view.php?id=<?php echo $order['order_id']; ?>" 
                           class="text-blue-500 hover:text-blue-700">View Order</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php
require_once '../../includes/footer.php';
?> 