<?php
require_once 'config/database.php';
require_once 'includes/header.php';
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-2">Products</h2>
        <p class="text-gray-600">Manage your products</p>
        <a href="<?php echo BASE_URL; ?>/modules/products/" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">View Products</a>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-2">Orders</h2>
        <p class="text-gray-600">Track customer orders</p>
        <a href="<?php echo BASE_URL; ?>/modules/orders/" class="mt-4 inline-block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">View Orders</a>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-2">Customers</h2>
        <p class="text-gray-600">Manage customers</p>
        <a href="<?php echo BASE_URL; ?>/modules/customers/" class="mt-4 inline-block bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">View Customers</a>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-2">Inventory</h2>
        <p class="text-gray-600">Track inventory levels</p>
        <a href="<?php echo BASE_URL; ?>/modules/inventory/" class="mt-4 inline-block bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">View Inventory</a>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
