<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireDelivery();

$conn = getDatabaseConnection();
$user_id = getCurrentUserId();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $full_name = sanitizeInput($_POST['full_name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $address = sanitizeInput($_POST['address']);
        
        if (!emailExists($conn, $email, $user_id)) {
            $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=?, address=? WHERE user_id=?");
            $stmt->bind_param("ssssi", $full_name, $email, $phone, $address, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['full_name'] = $full_name;
                redirectWithMessage('profile.php', 'Profile updated successfully!', 'success');
            } else {
                $error = 'Failed to update profile';
            }
        } else {
            $error = 'Email already exists';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        $user = getUserById($conn, $user_id);
        
        if (!verifyPassword($current_password, $user['password'])) {
            $error = 'Current password is incorrect';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            $hashed_password = hashPassword($new_password);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                redirectWithMessage('profile.php', 'Password changed successfully!', 'success');
            } else {
                $error = 'Failed to change password';
            }
        }
    }
}

// Get user details
$user = getUserById($conn, $user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Gas Delivery System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="dashboard.php" class="logo">
                    <span class="logo-icon"></span>
                    GasDelivery Delivery
                </a>
                <ul class="nav-menu">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="deliveries.php">My Deliveries</a></li>
                    <li><a href="profile.php" class="active">Profile</a></li>
                    <li><a href="../logout.php" class="btn btn-danger btn-sm">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">My Profile</h1>
            </div>

            <?php displayFlashMessage(); ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-6">
                    <!-- Update Profile -->
                    <div class="card">
                        <div class="card-header">Profile Information</div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_profile">
                                <div class="form-group">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-6">
                    <!-- Change Password -->
                    <div class="card">
                        <div class="card-header">Change Password</div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="change_password">
                                <div class="form-group">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Change Password</button>
                            </form>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="card">
                        <div class="card-header">Account Information</div>
                        <div class="card-body">
                            <p><strong>Account Type:</strong> Delivery Personnel</p>
                            <p><strong>Account Status:</strong> 
                                <span class="badge <?php echo $user['status'] === 'active' ? 'status-delivered' : 'status-cancelled'; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </p>
                            <p><strong>Member Since:</strong> <?php echo formatDate($user['created_at']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Gas Delivery Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="../js/main.js"></script>
</body>
</html>

<?php closeDatabaseConnection($conn); ?>
