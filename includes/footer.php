    </div>
    <footer class="bg-gray-800 text-white mt-8">
        <div class="max-w-7xl mx-auto py-12 px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        E-Commerce
                    </h3>
                    <p class="text-gray-400 text-sm">
                        Streamline your e-commerce operations with our comprehensive management system.
                    </p>
                    <div class="mt-4 flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">Dashboard</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">Products</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">Orders</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">Customers</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Support</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">Help Center</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">Documentation</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">API Reference</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">Contact Us</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Newsletter</h4>
                    <p class="text-gray-400 text-sm mb-4">Stay updated with our latest features and releases.</p>
                    <form class="flex">
                        <input type="email" 
                               placeholder="Enter your email" 
                               class="px-4 py-2 rounded-l-lg w-full focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <button class="bg-emerald-600 px-4 py-2 rounded-r-lg hover:bg-emerald-700 transition-colors duration-200">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <hr class="border-gray-700 my-8">
            
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 text-sm">
                    © 2024 E-Commerce Management System. All rights reserved.
                </p>
                <div class="flex space-x-4 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors duration-200">Privacy Policy</a>
                    <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors duration-200">Terms of Service</a>
                    <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors duration-200">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html> 