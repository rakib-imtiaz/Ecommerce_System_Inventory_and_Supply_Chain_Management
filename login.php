<?php
require_once 'config/database.php';

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_type'])) {
    if ($_SESSION['user_type'] === 'admin') {
        header('Location: ' . BASE_URL . '/index.php');
    } else {
        header('Location: ' . BASE_URL . '/customer/dashboard.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $login_type = $_POST['login_type'] ?? 'customer';
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        if ($login_type === 'admin') {
            // Admin login
            $query = "SELECT * FROM users WHERE username = :username AND password = :password LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->execute();
            
            if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_type'] = 'admin';
                header('Location: ' . BASE_URL . '/index.php');
                exit();
            }
        } else {
            // Customer login
            $query = "SELECT c.*, ce.email, c.password
                     FROM customer c 
                     JOIN customeremail ce ON c.customer_id = ce.customer_id 
                     WHERE ce.email = :email LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $username);
            $stmt->execute();
            
            if ($customer = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (password_verify($password, $customer['password'])) {
                    $_SESSION['customer_id'] = $customer['customer_id'];
                    $_SESSION['customer_name'] = $customer['name'];
                    $_SESSION['customer_email'] = $customer['email'];
                    $_SESSION['user_type'] = 'customer';
                    header('Location: ' . BASE_URL . '/customer/dashboard.php');
                    exit();
                }
            }
            $error = 'Invalid email or password';
        }
        
        $error = 'Invalid credentials. Please try again.';
        
    } catch (PDOException $e) {
        $error = 'Login error. Please try again.';
        error_log("Database error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Commerce Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        .gradient-background {
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .input-field {
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .login-btn {
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(5, 150, 105, 0.4);
        }

        .floating-label {
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .input-field:focus + .floating-label,
        .input-field:not(:placeholder-shown) + .floating-label {
            transform: translateY(-2.5rem) scale(0.85);
            color: #059669;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .animated-bg {
            background: linear-gradient(-45deg, #047857, #059669, #0d9488, #0891b2);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }
    </style>
</head>
<body class="animated-bg min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 glass-effect p-8 rounded-xl animate__animated animate__fadeIn">
            <div class="text-center">
                <div class="inline-block p-4 rounded-full bg-emerald-100 animate__animated animate__bounceIn">
                    <i class="fas fa-shopping-cart text-4xl text-emerald-600"></i>
                </div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900 animate__animated animate__fadeInUp">
                    Welcome Back!
                </h2>
                <p class="mt-2 text-sm text-gray-600 animate__animated animate__fadeInUp animate__delay-1s">
                    Sign in to your account to continue
                </p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded animate__animated animate__shakeX" role="alert">
                    <p class="font-medium">Error</p>
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" class="mt-8 space-y-6 animate__animated animate__fadeInUp animate__delay-1s">
                <div class="space-y-6">
                    <div class="relative">
                        <select name="login_type" 
                                class="input-field appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
                            <option value="customer">Customer</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>

                    <div class="relative">
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                            <span class="customer-label">Email Address</span>
                            <span class="admin-label hidden">Username</span>
                        </label>
                        <input id="username" name="username" type="email" required 
                               class="input-field appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                               placeholder="Enter your email">
                    </div>

                    <div class="relative">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Password
                        </label>
                        <input id="password" name="password" type="password" required 
                               class="input-field appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                               placeholder="Enter your password">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="login-btn group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-sign-in-alt text-emerald-100"></i>
                        </span>
                        Sign in
                    </button>
                </div>

                <div class="flex items-center justify-between animate__animated animate__fadeIn animate__delay-2s">
                    <div class="text-sm">
                        <a href="#" class="font-medium text-emerald-600 hover:text-emerald-500 transition-colors">
                            Forgot your password?
                        </a>
                    </div>
                    <div class="text-sm">
                        <a href="signup.php" class="font-medium text-emerald-600 hover:text-emerald-500 transition-colors">
                            Create new account
                        </a>
                    </div>
                </div>

                <div class="mt-6 text-center text-sm text-gray-600 space-y-1 animate__animated animate__fadeIn animate__delay-2s">
                    <div class="font-semibold">Demo Accounts:</div>
                    <div class="bg-gray-50 p-3 rounded-lg shadow-sm">
                        <div class="font-medium text-emerald-600">Customer Account</div>
                        <div>Email: alice.johnson@example.com</div>
                        <div>Password: customer123</div>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg shadow-sm mt-2">
                        <div class="font-medium text-emerald-600">Admin Account</div>
                        <div>Username: admin</div>
                        <div>Password: admin123</div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.querySelector('select[name="login_type"]').addEventListener('change', function() {
        const isAdmin = this.value === 'admin';
        const usernameInput = document.getElementById('username');
        const usernameLabel = document.querySelector('label[for="username"]');
        
        if (isAdmin) {
            usernameInput.type = 'text';
            usernameLabel.textContent = 'Username';
        } else {
            usernameInput.type = 'email';
            usernameLabel.textContent = 'Email Address';
        }
    });
    </script>
</body>
</html>