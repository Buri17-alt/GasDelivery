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
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    header('Location: orders.php');
    exit();
}

// Get order details
$stmt = $conn->prepare("SELECT o.*, u.full_name, u.email, u.phone, u.address as customer_address 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.user_id 
                        WHERE o.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Get order items
$stmt = $conn->prepare("SELECT oi.*, p.product_name, p.size 
                        FROM order_items oi 
                        JOIN products p ON oi.product_id = p.product_id 
                        WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$stmt->close();

// Get delivery info if exists
$stmt = $conn->prepare("SELECT d.*, u.full_name as delivery_person_name, u.phone as delivery_person_phone 
                        FROM deliveries d 
                        JOIN users u ON d.delivery_person_id = u.user_id 
                        WHERE d.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$delivery = $stmt->get_result()->fetch_assoc();
$stmt->close();

$pageTitle = 'Order Details';
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
    <?php include 'includes/header.php'; ?>
    
    <div class="dashboard-container">
        
        <main class="main-content">
            <div class="container">
            <div class="page-header">
                <h1>Order Details - #<?php echo $order_id; ?></h1>
                <a href="orders.php" class="btn btn-secondary">← Back to Orders</a>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <!-- Order Status Card -->
                <div class="card">
                    <div class="card-header">
                        <h3>Order Status</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Order Status:</strong> 
                            <span class="badge status-<?php echo $order['order_status']; ?>">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                        </p>
                        <p><strong>Payment Status:</strong> 
                            <span class="badge status-<?php echo $order['payment_status']; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </p>
                        <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                        <p><strong>Order Date:</strong> <?php echo date('F d, Y H:i', strtotime($order['created_at'])); ?></p>
                    </div>
                </div>
                
                <!-- Customer Info Card -->
                <div class="card">
                    <div class="card-header">
                        <h3>Customer Information</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                        <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($order['customer_address'])); ?></p>
                    </div>
                </div>
                
                <!-- Delivery Info Card -->
                <div class="card">
                    <div class="card-header">
                        <h3>Delivery Information</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Delivery Address:</strong></p>
                        <p><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
                        
                        <?php if ($delivery): ?>
                            <hr>
                            <p><strong>Delivery Status:</strong> 
                                <span class="badge status-<?php echo $delivery['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $delivery['status'])); ?>
                                </span>
                            </p>
                            <p><strong>Delivery Person:</strong> <?php echo htmlspecialchars($delivery['delivery_person_name']); ?></p>
                            <p><strong>Contact:</strong> <?php echo htmlspecialchars($delivery['delivery_person_phone']); ?></p>
                            <?php if ($delivery['delivered_at']): ?>
                                <p><strong>Delivered At:</strong> <?php echo date('F d, Y H:i', strtotime($delivery['delivered_at'])); ?></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-muted">No delivery assigned yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="card">
                <div class="card-header">
                    <h3>Order Items</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Size</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $items_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['size']); ?></td>
                                    <td><?php echo formatCurrency($item['price']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo formatCurrency($item['price'] * $item['quantity']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                                <tr style="font-weight: bold; border-top: 2px solid #ddd;">
                                    <td colspan="4" style="text-align: right;">Total Amount:</td>
                                    <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($order['notes']): ?>
                        <div style="margin-top: 1rem; padding: 1rem; background: #f9fafb; border-radius: 8px;">
                            <strong>Order Notes:</strong>
                            <p style="margin: 0.5rem 0 0 0;"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            </div>
        </main>
    </div>
    
    <script src="../js/main.js"></script>
</body>
</html>

<?php closeDatabaseConnection($conn); ?>
