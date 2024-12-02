<?php
require_once '../config/database.php';
session_start();

// Check if customer is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get customer's orders
$query = "SELECT o.*, 
         COUNT(oi.order_item_id) as item_count,
         SUM(oi.quantity * oi.unit_price) as total_amount
         FROM ordertable o
         LEFT JOIN orderitem oi ON o.order_id = oi.order_id
         WHERE o.customer_id = :customer_id
         GROUP BY o.order_id
         ORDER BY o.order_date DESC
         LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(':customer_id', $_SESSION['customer_id']);
$stmt->execute();
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get available products with categories and stock
$query = "SELECT p.*, c.category_name, 
          (SELECT SUM(quantity) FROM orderitem oi 
           JOIN ordertable o ON oi.order_id = o.order_id 
           WHERE oi.product_id = p.product_id 
           AND o.order_status != 'Cancelled') as total_ordered
          FROM product p 
          LEFT JOIN category c ON p.category_id = c.category_id 
          WHERE p.stock_level > 0 
          ORDER BY c.category_name, p.name";
$products = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Group products by category
$products_by_category = [];
foreach ($products as $product) {
    $products_by_category[$product['category_name']][] = $product;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Add Animation Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Add Custom Styles -->
    <style>
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        .slide-in {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .product-card {
            transition: all 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .cart-item {
            transition: all 0.3s ease;
        }

        .cart-item:hover {
            background-color: #f8fafc;
        }

        .btn-primary {
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(5, 150, 105, 0.4);
        }

        .stock-badge {
            transition: all 0.3s ease;
        }

        .category-header {
            position: relative;
            overflow: hidden;
        }

        .category-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, #059669 0%, transparent 100%);
        }

        /* Loading Animation */
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #059669;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Loading Overlay -->
    <div id="loading" class="loading hidden">
        <div class="loading-spinner"></div>
    </div>

    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-shopping-cart text-2xl text-emerald-600 animate__animated animate__bounceIn"></i>
                    <span class="text-lg font-semibold bg-gradient-to-r from-emerald-600 to-emerald-800 text-transparent bg-clip-text">
                        E-Commerce Store
                    </span>
                </div>
                <div class="flex items-center space-x-6">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-user-circle text-emerald-600"></i>
                        <span class="text-gray-700"><?php echo htmlspecialchars($_SESSION['customer_name']); ?></span>
                    </div>
                    <a href="logout.php" class="flex items-center space-x-1 text-red-600 hover:text-red-800 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Recent Orders -->
        <div class="mb-8 animate__animated animate__fadeIn">
            <h2 class="text-2xl font-bold mb-4 flex items-center space-x-2">
                <i class="fas fa-clock text-emerald-600"></i>
                <span>Recent Orders</span>
            </h2>
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <?php if (empty($recent_orders)): ?>
                    <div class="p-6 text-center text-gray-500">
                        <i class="fas fa-shopping-bag text-4xl mb-2"></i>
                        <p>No orders yet. Start shopping!</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">#<?php echo $order['order_id']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $order['item_count']; ?> items</td>
                                        <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php echo $order['order_status'] === 'Completed' ? 'bg-green-100 text-green-800' : 
                                                        ($order['order_status'] === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                        'bg-gray-100 text-gray-800'); ?>">
                                                <?php echo $order['order_status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- New Order Form -->
        <div class="bg-white shadow-lg rounded-lg p-6 animate__animated animate__fadeInUp">
            <h2 class="text-2xl font-bold mb-6 flex items-center space-x-2">
                <i class="fas fa-cart-plus text-emerald-600"></i>
                <span>Place New Order</span>
            </h2>

            <form action="place_order.php" method="POST" id="orderForm">
                <!-- Shopping Cart -->
                <div id="cart" class="mb-8">
                    <h3 class="text-lg font-semibold mb-4 flex items-center space-x-2">
                        <i class="fas fa-shopping-basket text-emerald-600"></i>
                        <span>Shopping Cart</span>
                    </h3>
                    <div id="cartItems" class="space-y-3">
                        <!-- Cart items will be displayed here -->
                    </div>
                    <div id="cartTotal" class="mt-4 text-right font-semibold text-lg text-emerald-600">
                        Total: $0.00
                    </div>
                </div>

                <!-- Products Grid -->
                <?php foreach ($products_by_category as $category => $category_products): ?>
                    <div class="mb-8 slide-in">
                        <h3 class="text-xl font-semibold mb-4 category-header">
                            <?php echo htmlspecialchars($category); ?>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($category_products as $product): ?>
                                <div class="product-card rounded-lg overflow-hidden border border-gray-200 hover:border-emerald-500">
                                    <div class="p-4">
                                        <h4 class="font-semibold text-lg mb-2"><?php echo htmlspecialchars($product['name']); ?></h4>
                                        <p class="text-gray-600 text-sm mb-3"><?php echo htmlspecialchars($product['description']); ?></p>
                                        
                                        <div class="flex justify-between items-center mb-3">
                                            <span class="text-2xl font-bold text-emerald-600">
                                                $<?php echo number_format($product['price'], 2); ?>
                                            </span>
                                            <span class="stock-badge px-3 py-1 rounded-full text-sm font-medium
                                                <?php echo $product['stock_level'] < 5 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'; ?>">
                                                <?php echo $product['stock_level']; ?> in stock
                                            </span>
                                        </div>
                                        
                                        <div class="flex items-center space-x-3">
                                            <input type="number" 
                                                   min="0" 
                                                   max="<?php echo $product['stock_level']; ?>" 
                                                   value="0"
                                                   data-product-id="<?php echo $product['product_id']; ?>"
                                                   class="w-20 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                                   onchange="updateCart(<?php echo $product['product_id']; ?>, this.value, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo $product['price']; ?>, <?php echo $product['stock_level']; ?>)">
                                            
                                            <button type="button" 
                                                    onclick="addToCart(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo $product['price']; ?>, <?php echo $product['stock_level']; ?>)"
                                                    class="btn-primary flex-1 py-2 px-4 rounded-lg text-white font-medium">
                                                <i class="fas fa-plus mr-2"></i> Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Place Order Button -->
                <button type="submit" 
                        class="btn-primary w-full py-4 rounded-lg text-white font-medium text-lg disabled:opacity-50 disabled:cursor-not-allowed mt-6"
                        id="submitOrder"
                        disabled>
                    <i class="fas fa-check-circle mr-2"></i> Place Order
                </button>
            </form>
        </div>
    </div>

    <script>
    let cart = {};
    let total = 0;

    function updateCartDisplay() {
        const cartItems = document.getElementById('cartItems');
        const cartTotal = document.getElementById('cartTotal');
        const submitButton = document.getElementById('submitOrder');
        
        cartItems.innerHTML = '';
        total = 0;

        for (const [productId, item] of Object.entries(cart)) {
            if (item.quantity > 0) {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;

                const cartItem = document.createElement('div');
                cartItem.className = 'cart-item flex justify-between items-center p-4 bg-gray-50 rounded-lg animate__animated animate__fadeIn';
                cartItem.innerHTML = `
                    <div class="flex-1">
                        <span class="font-medium">${item.name}</span>
                        <span class="text-sm text-gray-600 ml-2">
                            (${item.quantity} × $${item.price.toFixed(2)})
                        </span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="font-medium text-emerald-600">$${itemTotal.toFixed(2)}</span>
                        <button type="button" onclick="removeFromCart(${productId})" 
                                class="text-red-600 hover:text-red-800 transition-colors duration-200">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <input type="hidden" name="items[${productId}][product_id]" value="${productId}">
                    <input type="hidden" name="items[${productId}][quantity]" value="${item.quantity}">
                `;
                cartItems.appendChild(cartItem);
            }
        }

        cartTotal.textContent = `Total: $${total.toFixed(2)}`;
        submitButton.disabled = total === 0;
    }

    function updateCart(productId, quantity, name, price, maxStock) {
        quantity = parseInt(quantity);
        
        // Validate quantity against stock
        if (quantity > maxStock) {
            alert(`Only ${maxStock} items available in stock`);
            // Reset the input field to maximum available stock
            document.querySelector(`input[data-product-id="${productId}"]`).value = maxStock;
            quantity = maxStock;
        }
        
        if (quantity > 0) {
            cart[productId] = { quantity, name, price };
        } else {
            delete cart[productId];
        }
        updateCartDisplay();
    }

    function addToCart(productId, name, price, maxStock) {
        const currentQty = cart[productId]?.quantity || 0;
        const newQty = currentQty + 1;
        
        if (newQty <= maxStock) {
            cart[productId] = {
                quantity: newQty,
                name,
                price
            };
            // Update the input field
            const inputField = document.querySelector(`input[data-product-id="${productId}"]`);
            if (inputField) {
                inputField.value = newQty;
            }
            updateCartDisplay();
        } else {
            alert(`Cannot add more than ${maxStock} items (current stock limit)`);
        }
    }

    function removeFromCart(productId) {
        delete cart[productId];
        // Reset the input field
        const inputField = document.querySelector(`input[data-product-id="${productId}"]`);
        if (inputField) {
            inputField.value = 0;
        }
        updateCartDisplay();
    }

    // Add loading animation
    function showLoading() {
        document.getElementById('loading').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loading').classList.add('hidden');
    }

    // Add form submission handling with loading animation
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        if (Object.keys(cart).length === 0) {
            e.preventDefault();
            alert('Please add items to your cart before placing an order.');
            return;
        }
        showLoading();
    });

    // Initialize animations on page load
    document.addEventListener('DOMContentLoaded', function() {
        hideLoading();
    });
    </script>
</body>
</html> 