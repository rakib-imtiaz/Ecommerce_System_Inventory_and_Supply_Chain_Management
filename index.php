<?php
require_once 'config/database.php';
require_once 'includes/header.php';

// Get statistics for dashboard
$database = new Database();
$db = $database->getConnection();

// Fetch actual statistics from database
$stats = [
    'products' => $db->query("SELECT COUNT(*) FROM product")->fetchColumn(),
    'orders' => $db->query("SELECT COUNT(*) FROM ordertable")->fetchColumn(),
    'customers' => $db->query("SELECT COUNT(*) FROM customer")->fetchColumn(),
    'low_stock' => $db->query("SELECT COUNT(*) FROM product WHERE stock_level < 10")->fetchColumn()
];
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 animate__animated animate__fadeIn">
        <div class="stat-card p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="icon-wrapper p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-box text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Total Products</h3>
                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['products']); ?></p>
                </div>
            </div>
        </div>

        <div class="stat-card p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="icon-wrapper p-3 bg-emerald-100 rounded-lg">
                    <i class="fas fa-shopping-cart text-emerald-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Total Orders</h3>
                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['orders']); ?></p>
                </div>
            </div>
        </div>

        <div class="stat-card p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="icon-wrapper p-3 bg-indigo-100 rounded-lg">
                    <i class="fas fa-users text-indigo-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Customers</h3>
                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['customers']); ?></p>
                </div>
            </div>
        </div>

        <div class="stat-card p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="icon-wrapper p-3 bg-amber-100 rounded-lg">
                    <i class="fas fa-warehouse text-amber-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Low Stock Items</h3>
                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['low_stock']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Menu Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="card bg-white rounded-lg shadow-sm overflow-hidden animate__animated animate__fadeInUp">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Products</h2>
                    <div class="icon-wrapper p-3 bg-blue-100 rounded-lg">
                        <i class="fas fa-box text-blue-600"></i>
                    </div>
                </div>
                <p class="text-gray-600 mb-6">Manage your product catalog and inventory</p>
                <a href="modules/products/" class="block w-full text-center bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors duration-200">
                    View Products
                </a>
            </div>
        </div>

        <div class="card bg-white rounded-lg shadow-sm overflow-hidden animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Orders</h2>
                    <div class="icon-wrapper p-3 bg-emerald-100 rounded-lg">
                        <i class="fas fa-shopping-cart text-emerald-600"></i>
                    </div>
                </div>
                <p class="text-gray-600 mb-6">Track and manage customer orders</p>
                <a href="modules/orders/" class="block w-full text-center bg-emerald-600 text-white px-4 py-2 rounded-md hover:bg-emerald-700 transition-colors duration-200">
                    View Orders
                </a>
            </div>
        </div>

        <div class="card bg-white rounded-lg shadow-sm overflow-hidden animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Customers</h2>
                    <div class="icon-wrapper p-3 bg-indigo-100 rounded-lg">
                        <i class="fas fa-users text-indigo-600"></i>
                    </div>
                </div>
                <p class="text-gray-600 mb-6">Manage customer relationships</p>
                <a href="modules/customers/" class="block w-full text-center bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors duration-200">
                    View Customers
                </a>
            </div>
        </div>

        <div class="card bg-white rounded-lg shadow-sm overflow-hidden animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Inventory</h2>
                    <div class="icon-wrapper p-3 bg-amber-100 rounded-lg">
                        <i class="fas fa-warehouse text-amber-600"></i>
                    </div>
                </div>
                <p class="text-gray-600 mb-6">Monitor and manage stock levels</p>
                <a href="modules/inventory/" class="block w-full text-center bg-amber-600 text-white px-4 py-2 rounded-md hover:bg-amber-700 transition-colors duration-200">
                    View Inventory
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>