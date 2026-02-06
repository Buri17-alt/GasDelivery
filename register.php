<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $user_type = sanitizeInput($_POST['user_type']);
    
    // Validation
    if (empty($username)) $errors[] = 'Username is required';
    if (empty($password)) $errors[] = 'Password is required';
    if (empty($confirm_password)) $errors[] = 'Confirm password is required';
    if (empty($full_name)) $errors[] = 'Full name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($phone)) $errors[] = 'Phone is required';
    if (empty($address)) $errors[] = 'Address is required';
    if (empty($user_type)) $errors[] = 'Role is required';
    if (!empty($user_type) && !in_array($user_type, ['customer', 'vendor', 'delivery'])) $errors[] = 'Invalid role selected';
    
    if (!empty($email) && !validateEmail($email)) {
        $errors[] = 'Invalid email format';
    }
    
    if (!empty($password) && strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    if (empty($errors)) {
        $conn = getDatabaseConnection();
        
        // Check if username exists
        if (usernameExists($conn, $username)) {
            $errors[] = 'Username already exists';
        }
        
        // Check if email exists
        if (emailExists($conn, $email)) {
            $errors[] = 'Email already exists';
        }
        
        if (empty($errors)) {
            $hashed_password = hashPassword($password);
            
            $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, phone, address, user_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $username, $hashed_password, $full_name, $email, $phone, $address, $user_type);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! You can now login.';
                // Clear form
                $_POST = [];
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
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
    <title>Register - Gas Delivery System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card" style="max-width: 500px;">
            <div class="auth-header">
                <h1> GasDelivery</h1>
                <p>Create your account</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 1.5rem;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username" class="form-label">Username:</label>
                    <input type="text" id="username" name="username" class="form-control" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="full_name" class="form-label">Full Name:</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required 
                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">Phone:</label>
                    <input type="tel" id="phone" name="phone" class="form-control" required 
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="address" class="form-label">Address:</label>
                    <textarea id="address" name="address" class="form-control" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="user_type" class="form-label">Register As</label>
                    <select id="user_type" name="user_type" class="form-control" required>
                        <option value="">Select Role</option>
                        <option value="customer" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'customer') ? 'selected' : ''; ?>>Customer (Purchase Products)</option>
                        <option value="vendor" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'vendor') ? 'selected' : ''; ?>>Vendor (Publish & Receive Orders)</option>
                        <option value="delivery" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'delivery') ? 'selected' : ''; ?>>Delivery Personnel (Deliver Orders)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
                <p style="margin-top: 1rem;"><a href="index.php">Back to Home</a></p>
            </div>
        </div>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>
