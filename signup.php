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
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    // Validate password
    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Check if email already exists
            $query = "SELECT customer_id FROM customeremail WHERE email = :email LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                $error = 'Email already registered. Please use a different email.';
            } else {
                $db->beginTransaction();
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert customer with password
                $query = "INSERT INTO customer (name, shipping_address, password) VALUES (:name, :address, :password)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':address', $address);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->execute();
                
                $customer_id = $db->lastInsertId();
                
                // Insert email
                $query = "INSERT INTO customeremail (customer_id, email) VALUES (:customer_id, :email)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':customer_id', $customer_id);
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                
                // Insert phone
                if (!empty($phone)) {
                    $query = "INSERT INTO customerphoneno (customer_id, phone_number) VALUES (:customer_id, :phone)";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':customer_id', $customer_id);
                    $stmt->bindParam(':phone', $phone);
                    $stmt->execute();
                }
                
                $db->commit();
                $success = 'Account created successfully! You can now login.';
            }
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'An error occurred. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - E-Commerce</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <i class="fas fa-shopping-cart text-4xl text-emerald-600"></i>
                <h2 class="mt-6 text-3xl font-bold text-gray-900">Create Account</h2>
                <p class="mt-2 text-sm text-gray-600">Sign up to start shopping</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="mt-8 space-y-6">
                <div class="rounded-md shadow-sm space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="name" required 
                               class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="John Doe">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" required 
                               class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="john@example.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" required 
                               class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="••••••••">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input type="password" name="confirm_password" required 
                               class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="••••••••">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone Number (Optional)</label>
                        <input type="tel" name="phone"
                               class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="123-456-7890">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Shipping Address</label>
                        <textarea name="address" required rows="3"
                                  class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500"
                                  placeholder="Enter your shipping address"></textarea>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                        Create Account
                    </button>
                </div>

                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Already have an account? 
                        <a href="login.php" class="font-medium text-emerald-600 hover:text-emerald-500">
                            Sign in
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 