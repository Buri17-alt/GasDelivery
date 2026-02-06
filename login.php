<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $userType = getCurrentUserType();
    switch ($userType) {
        case 'admin':
            header('Location: admin/dashboard.php');
            exit();
        case 'delivery':
            header('Location: delivery/dashboard.php');
            exit();
        case 'customer':
            header('Location: customer/dashboard.php');
            exit();
        case 'vendor':
            header('Location: vendor/dashboard.php');
            exit();
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $conn = getDatabaseConnection();
        
        $stmt = $conn->prepare("SELECT user_id, username, password, full_name, user_type, status FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if ($user['status'] !== 'active') {
                $error = 'Your account has been deactivated. Please contact support.';
            } elseif (verifyPassword($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // Redirect based on user type
                switch ($user['user_type']) {
                    case 'admin':
                        header('Location: admin/dashboard.php');
                        exit();
                    case 'delivery':
                        header('Location: delivery/dashboard.php');
                        exit();
                    case 'customer':
                        header('Location: customer/dashboard.php');
                        exit();
                    case 'vendor':
                        header('Location: vendor/dashboard.php');
                        exit();
                }
            } else {
                $error = 'Invalid username or password';
            }
        } else {
            $error = 'Invalid username or password';
        }
        
        closeDatabaseConnection($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gas Delivery System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1> GasDelivery</h1>
                <p>Login to your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
                <p style="margin-top: 1rem;"><a href="index.php">Back to Home</a></p>
            </div>
            
        </div>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>
