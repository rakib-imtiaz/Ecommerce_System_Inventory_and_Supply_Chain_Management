<?php
require_once 'config/database.php';

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM users WHERE username = :username AND password = :password LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        
        if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: ' . BASE_URL);
            exit();
        } else {
            $error = 'Invalid credentials. Try admin/admin123 or demo/demo123';
        }
    } catch (PDOException $e) {
        $error = 'Login error. Please try again.';
        error_log($e->getMessage());
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
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <i class="fas fa-shopping-cart text-4xl text-emerald-600"></i>
                <h2 class="mt-6 text-3xl font-bold text-gray-900">E-Commerce Management</h2>
                <p class="mt-2 text-sm text-gray-600">Demo System</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="mt-8 space-y-6">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <input name="username" type="text" required 
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 focus:z-10 sm:text-sm" 
                               placeholder="Username">
                    </div>
                    <div>
                        <input name="password" type="password" required 
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 focus:z-10 sm:text-sm" 
                               placeholder="Password">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                        Sign in
                    </button>
                </div>
            </form>

            <div class="text-center text-sm text-gray-600 space-y-1">
                <div class="font-semibold">Available Accounts:</div>
                <div class="bg-gray-100 p-3 rounded-lg">
                    <div class="font-medium text-emerald-600">Admin Account</div>
                    <div>Username: admin</div>
                    <div>Password: admin123</div>
                </div>
                <div class="bg-gray-100 p-3 rounded-lg mt-2">
                    <div class="font-medium text-blue-600">Demo Account</div>
                    <div>Username: demo</div>
                    <div>Password: demo123</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>