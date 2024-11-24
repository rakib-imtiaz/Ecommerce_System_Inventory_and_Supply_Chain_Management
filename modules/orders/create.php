<?php
require_once '../../config/database.php';
require_once '../../classes/Order.php';
require_once '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Get customers for dropdown
$query = "SELECT customer_id, name FROM customer ORDER BY name";
$customers = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Get products for dropdown
$query = "SELECT product_id, name, price, stock_level FROM product WHERE stock_level > 0 ORDER BY name";
$products = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $order = new Order($db);
        $items = [];
        
        // Process order items
        foreach ($_POST['items'] as $item) {
            if (!empty($item['product_id']) && !empty($item['quantity'])) {
                $items[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price']
                ];
            }
        }

        if (empty($items)) {
            throw new Exception("Please add at least one item to the order");
        }

        $order_id = $order->create($_POST['customer_id'], $items);
        
        header("Location: " . BASE_URL . "/modules/orders/view.php?id=" . $order_id);
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="mb-6">
    <h1 class="text-3xl font-semibold">Create New Order</h1>
</div>

<?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="bg-white shadow-md rounded-lg p-6">
    <form action="" method="POST" id="orderForm">
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
            <select name="customer_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="">Select Customer</option>
                <?php foreach ($customers as $customer): ?>
                    <option value="<?php echo $customer['customer_id']; ?>">
                        <?php echo htmlspecialchars($customer['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-6">
            <h2 class="text-lg font-medium mb-2">Order Items</h2>
            <div id="orderItems">
                <!-- Order items will be added here -->
            </div>
            <button type="button" onclick="addOrderItem()"
                    class="mt-2 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Add Item
            </button>
        </div>

        <div class="mt-6 flex items-center justify-end">
            <a href="<?php echo BASE_URL; ?>/modules/orders/" 
               class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2">Cancel</a>
            <button type="submit" 
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Create Order</button>
        </div>
    </form>
</div>

<template id="orderItemTemplate">
    <div class="order-item bg-gray-50 p-4 rounded mb-2">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Product</label>
                <select name="items[{index}][product_id]" onchange="updatePrice(this)" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Select Product</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['product_id']; ?>" 
                                data-price="<?php echo $product['price']; ?>"
                                data-stock="<?php echo $product['stock_level']; ?>">
                            <?php echo htmlspecialchars($product['name']); ?> 
                            (Stock: <?php echo $product['stock_level']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                <input type="number" name="items[{index}][quantity]" min="1" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md"
                       onchange="updateSubtotal(this)">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                <input type="number" name="items[{index}][price]" step="0.01" readonly
                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Subtotal</label>
                <input type="text" readonly
                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
            </div>
        </div>
        <button type="button" onclick="removeOrderItem(this)" 
                class="mt-2 text-red-500 hover:text-red-700">Remove</button>
    </div>
</template>

<script>
let itemIndex = 0;

function addOrderItem() {
    const template = document.getElementById('orderItemTemplate');
    const orderItems = document.getElementById('orderItems');
    const clone = template.content.cloneNode(true);
    
    // Replace {index} placeholder with actual index
    clone.querySelectorAll('[name*="{index}"]').forEach(element => {
        element.name = element.name.replace('{index}', itemIndex);
    });
    
    orderItems.appendChild(clone);
    itemIndex++;
}

function removeOrderItem(button) {
    button.closest('.order-item').remove();
}

function updatePrice(select) {
    const option = select.options[select.selectedIndex];
    const priceInput = select.closest('.order-item').querySelector('[name$="[price]"]');
    const quantityInput = select.closest('.order-item').querySelector('[name$="[quantity]"]');
    
    if (option.value) {
        priceInput.value = option.dataset.price;
        quantityInput.max = option.dataset.stock;
        updateSubtotal(quantityInput);
    }
}

function updateSubtotal(input) {
    const orderItem = input.closest('.order-item');
    const price = orderItem.querySelector('[name$="[price]"]').value;
    const quantity = input.value;
    const subtotalInput = orderItem.querySelector('input[readonly]:last-of-type');
    
    if (price && quantity) {
        subtotalInput.value = '$' + (price * quantity).toFixed(2);
    }
}

// Add first item row by default
addOrderItem();
</script>

<?php
require_once '../../includes/footer.php';
?> 