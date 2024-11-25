<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    <style>
        .nav-link {
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #10B981;
            transition: width 0.3s ease-in-out;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }

        .dropdown-content {
            transform-origin: top;
            transform: scaleY(0);
            transition: transform 0.2s ease-in-out;
        }

        .dropdown:hover .dropdown-content {
            transform: scaleY(1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Top Bar -->
    <div class="bg-emerald-600 text-white py-2">
        <div class="max-w-7xl mx-auto px-4 flex justify-between items-center text-sm">
            <div class="flex items-center space-x-4">
                <span><i class="fas fa-phone-alt mr-2"></i> +1 234 567 890</span>
                <span><i class="fas fa-envelope mr-2"></i> support@ecommerce.com</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="#" class="hover:text-emerald-200 transition-colors duration-200">
                    <i class="fab fa-facebook"></i>
                </a>
                <a href="#" class="hover:text-emerald-200 transition-colors duration-200">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="hover:text-emerald-200 transition-colors duration-200">
                    <i class="fab fa-linkedin"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-8">
                    <a href="<?php echo BASE_URL; ?>" class="flex items-center space-x-2">
                        <i class="fas fa-shopping-cart text-2xl text-emerald-600"></i>
                        <span class="font-bold text-xl text-gray-800">E-Commerce</span>
                    </a>
                    <div class="hidden md:flex items-center space-x-4">
                        <?php
                        $nav_items = [
                            'products' => ['icon' => 'fas fa-box', 'color' => 'text-blue-600'],
                            'orders' => ['icon' => 'fas fa-shopping-bag', 'color' => 'text-emerald-600'],
                            'customers' => ['icon' => 'fas fa-users', 'color' => 'text-indigo-600'],
                            'inventory' => ['icon' => 'fas fa-warehouse', 'color' => 'text-amber-600']
                        ];
                        
                        foreach ($nav_items as $item => $details): ?>
                            <a href="<?php echo BASE_URL; ?>/modules/<?php echo $item; ?>/" 
                               class="nav-link flex items-center space-x-2 py-4 px-2 text-gray-500 hover:text-emerald-600 transition-colors duration-200">
                                <i class="<?php echo $details['icon'] . ' ' . $details['color']; ?>"></i>
                                <span><?php echo ucfirst($item); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <button class="bg-emerald-100 text-emerald-600 px-4 py-2 rounded-lg hover:bg-emerald-200 transition-colors duration-200">
                        <i class="fas fa-bell mr-2"></i>
                        <span class="hidden md:inline">Notifications</span>
                    </button>
                    <div class="relative dropdown">
                        <button class="flex items-center space-x-2 text-gray-700 hover:text-emerald-600 transition-colors duration-200">
                            <img src="https://ui-avatars.com/api/?name=Admin&background=10B981&color=fff" 
                                 class="w-8 h-8 rounded-full">
                            <span class="hidden md:inline">Admin</span>
                        </button>
                        <div class="dropdown-content absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2">
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-emerald-50 hover:text-emerald-600">
                                <i class="fas fa-user mr-2"></i> Profile
                            </a>
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-emerald-50 hover:text-emerald-600">
                                <i class="fas fa-cog mr-2"></i> Settings
                            </a>
                            <hr class="my-2">
                            <a href="#" class="block px-4 py-2 text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 fade-in">
</body>
</html> 