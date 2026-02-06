<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

// Check if user is vendor
if (!isLoggedIn() || (!isVendor() && !isAdmin())) {
    header('Location: ../login.php');
    exit();
}

$conn = getDatabaseConnection();
$user_id = getCurrentUserId();

$success = '';
$errors = [];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    
    // Validation
    if (empty($full_name)) $errors[] = 'Full name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($phone)) $errors[] = 'Phone is required';
    
    // Check if email is already taken by another user
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Email is already taken';
        }
        $stmt->close();
    }
    
    // Update profile
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE user_id = ?");
        $stmt->bind_param("ssssi", $full_name, $email, $phone, $address, $user_id);
        
        if ($stmt->execute()) {
            $success = 'Profile updated successfully!';
            $_SESSION['user_name'] = $full_name;
        } else {
            $errors[] = 'Failed to update profile';
        }
        $stmt->close();
    }
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$pageTitle = 'Profile';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Gas Delivery System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="header">
<div class="container">
<nav class="navbar">
    <div class="navbar-brand">
        <h2>GasDelivery Vendor</h2>
    </div>
    <div class="nav-menu">
        <span class="user-info">
            <strong><?php echo htmlspecialchars(getCurrentUserName()); ?></strong>
            <span class="badge status-delivered">Vendor</span>
        </span>
        <a href="dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <span class="nav-text">Dashboard</span>
        </a>
        <a href="products.php" class="nav-link <?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
            <span class="nav-text">Products</span>
        </a>
        <a href="orders.php" class="nav-link <?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">
            <span class="nav-text">Orders</span>
        </a>
        <a href="profile.php" class="nav-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
            <span class="nav-text">Profile</span>
        </a>
        <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>
</div>
</header>

        <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>My Profile</h1>
            </div>
            
            <div class="card" style="max-width: 800px;">
                <div class="card-body">
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul style="margin: 0; padding-left: 20px;">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="username" class="form-label">Username:</label>
                            <input type="text" id="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <small class="text-muted">Username cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="full_name" class="form-label">Full Name:</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone:</label>
                            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="address" class="form-label">Address:</label>
                            <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Account Type:</label>
                            <input type="text" class="form-control" value="<?php echo ucfirst($user['user_type']); ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Account Status:</label>
                            <input type="text" class="form-control" value="<?php echo ucfirst($user['status']); ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Member Since:</label>
                            <input type="text" class="form-control" value="<?php echo date('F d, Y', strtotime($user['created_at'])); ?>" disabled>
                        </div>
                        
                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                            <a href="change-password.php" class="btn btn-secondary">Change Password</a>
                        </div>
                    </form>
                </div>
            </div>
            </div>
        </main>
    </div>
    
    <script src="../js/main.js"></script>
</body>
</html>

<?php closeDatabaseConnection($conn); ?>
