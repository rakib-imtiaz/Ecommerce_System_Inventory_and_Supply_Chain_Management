<?php
require_once '../../config/database.php';
require_once '../../classes/Order.php';
require_once '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);

// Get order ID from URL
$order_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');

// Get order details
$order_data = $order->getOrder($order_id);
if (!$order_data) {
    header("Location: " . BASE_URL . "/modules/orders/");
    exit();
}

// Get order items
$order_items = $order->getOrderItems($order_id);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    try {
        $order->updateStatus($order_id, $_POST['status']);
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $order_id);
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-semibold">Order #<?php echo $order_id; ?></h1>
    <a href="<?php echo BASE_URL; ?>/modules/orders/" 
       class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Back to Orders</a>
</div>

<?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <!-- Order Details -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Order Details</h2>
        <dl class="grid grid-cols-2 gap-4">
            <dt class="text-gray-600">Order Date</dt>
            <dd><?php echo date('M d, Y', strtotime($order_data['order_date'])); ?></dd>
            
            <dt class="text-gray-600">Status</dt>
            <dd>
                <form method="POST" class="inline-block">
                    <select name="status" onchange="this.form.submit()"
                            class="px-2 py-1 border border-gray-300 rounded-md text-sm">
                        <option value="Pending" <?php echo ($order_data['order_status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="Processing" <?php echo ($order_data['order_status'] == 'Processing') ? 'selected' : ''; ?>>Processing</option>
                        <option value="Shipped" <?php echo ($order_data['order_status'] == 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
                        <option value="Completed" <?php echo ($order_data['order_status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                        <option value="Cancelled" <?php echo ($order_data['order_status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </form>
            </dd>
        </dl>
    </div>

    <!-- Customer Details -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Customer Details</h2>
        <dl class="grid grid-cols-2 gap-4">
            <dt class="text-gray-600">Name</dt>
            <dd><?php echo htmlspecialchars($order_data['customer_name']); ?></dd>
            
            <dt class="text-gray-600">Shipping Address</dt>
            <dd><?php echo nl2br(htmlspecialchars($order_data['shipping_address'])); ?></dd>
        </dl>
    </div>
</div>

<!-- Order Items -->
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <h2 class="text-xl font-semibold p-6 pb-0">Order Items</h2>
    <table class="min-w-full mt-4">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php 
            $total = 0;
            while ($item = $order_items->fetch(PDO::FETCH_ASSOC)) { 
                $subtotal = $item['quantity'] * $item['unit_price'];
                $total += $subtotal;
            ?>
                <tr>
                    <td class="px-6 py-4"><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td class="px-6 py-4"><?php echo $item['quantity']; ?></td>
                    <td class="px-6 py-4">$<?php echo number_format($item['unit_price'], 2); ?></td>
                    <td class="px-6 py-4">$<?php echo number_format($subtotal, 2); ?></td>
                </tr>
            <?php } ?>
            <tr class="bg-gray-50">
                <td colspan="3" class="px-6 py-4 text-right font-medium">Total:</td>
                <td class="px-6 py-4 font-medium">$<?php echo number_format($total, 2); ?></td>
            </tr>
        </tbody>
    </table>
</div>

<?php
require_once '../../includes/footer.php';
?> 