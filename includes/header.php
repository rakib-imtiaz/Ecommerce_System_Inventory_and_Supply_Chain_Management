<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between">
                <div class="flex space-x-7">
                    <div>
                        <a href="<?php echo BASE_URL; ?>" class="flex items-center py-4">
                            <span class="font-semibold text-gray-500 text-lg">E-Commerce Management</span>
                        </a>
                    </div>
                    <div class="hidden md:flex items-center space-x-1">
                        <a href="<?php echo BASE_URL; ?>/modules/products/" class="py-4 px-2 text-gray-500 hover:text-green-500">Products</a>
                        <a href="<?php echo BASE_URL; ?>/modules/orders/" class="py-4 px-2 text-gray-500 hover:text-green-500">Orders</a>
                        <a href="<?php echo BASE_URL; ?>/modules/customers/" class="py-4 px-2 text-gray-500 hover:text-green-500">Customers</a>
                        <a href="<?php echo BASE_URL; ?>/modules/inventory/" class="py-4 px-2 text-gray-500 hover:text-green-500">Inventory</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mx-auto px-4 py-8">
</body>
</html> 