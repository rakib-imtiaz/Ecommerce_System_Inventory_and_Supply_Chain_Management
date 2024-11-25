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
                // Validate unit price is not empty and is numeric
                if (empty($item['price']) || !is_numeric($item['price'])) {
                    throw new Exception("Invalid price for one or more items");
                }
                
                $items[] = [
                    'product_id' => (int)$item['product_id'],
                    'quantity' => (int)$item['quantity'],
                    'unit_price' => (float)$item['price']
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
    <div class="order-item grid grid-cols-4 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Product</label>
            <select name="items[{index}][product_id]" onchange="updatePrice(this)" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="">Select Product</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?php echo $product['product_id']; ?>" 
                            data-price="<?php echo $product['price']; ?>"
                            data-stock="<?php echo $product['stock_level']; ?>">
                        <?php echo htmlspecialchars($product['name']); ?> (Stock: <?php echo $product['stock_level']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
            <input type="number" name="items[{index}][quantity]" min="1" required
                   onchange="updateSubtotal(this)"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Unit Price</label>
            <input type="number" name="items[{index}][price]" step="0.01" readonly required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Subtotal</label>
            <input type="text" readonly
                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
        </div>
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
    updateTotalAmount();
}

function updatePrice(select) {
    const option = select.options[select.selectedIndex];
    const orderItem = select.closest('.order-item');
    const priceInput = orderItem.querySelector('[name$="[price]"]');
    const quantityInput = orderItem.querySelector('[name$="[quantity]"]');
    
    if (option.value) {
        const price = parseFloat(option.dataset.price);
        priceInput.value = price.toFixed(2);
        quantityInput.max = option.dataset.stock;
        quantityInput.value = 1; // Set default quantity
        updateSubtotal(quantityInput);
    } else {
        priceInput.value = '';
        quantityInput.value = '';
        quantityInput.max = '';
        updateSubtotal(quantityInput);
    }
}

function updateSubtotal(input) {
    const orderItem = input.closest('.order-item');
    const priceInput = orderItem.querySelector('[name$="[price]"]');
    const quantityInput = orderItem.querySelector('[name$="[quantity]"]');
    const subtotalInput = orderItem.querySelector('input[readonly]:last-of-type');
    
    const price = parseFloat(priceInput.value) || 0;
    const quantity = parseInt(quantityInput.value) || 0;
    
    const subtotal = price * quantity;
    subtotalInput.value = '$' + subtotal.toFixed(2);
    
    updateTotalAmount();
}

function updateTotalAmount() {
    const subtotalInputs = document.querySelectorAll('.order-item input[readonly]:last-of-type');
    let total = 0;
    
    subtotalInputs.forEach(input => {
        const value = parseFloat(input.value.replace('$', '')) || 0;
        total += value;
    });
    
    const totalElement = document.getElementById('totalAmount');
    if (totalElement) {
        totalElement.textContent = '$' + total.toFixed(2);
    }
}

// Validate form before submission
document.getElementById('orderForm').addEventListener('submit', function(e) {
    const items = document.querySelectorAll('.order-item');
    let valid = false;
    
    items.forEach(item => {
        const productSelect = item.querySelector('[name$="[product_id]"]');
        const quantity = item.querySelector('[name$="[quantity]"]');
        const price = item.querySelector('[name$="[price]"]');
        
        if (productSelect.value && quantity.value && price.value) {
            valid = true;
        }
    });
    
    if (!valid) {
        e.preventDefault();
        alert('Please add at least one valid item to the order');
    }
});

// Add first item row by default
addOrderItem();
</script>

<?php
require_once '../../includes/footer.php';
?> 