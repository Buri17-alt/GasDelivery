<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireCustomer();

$conn = getDatabaseConnection();
$user_id = getCurrentUserId();
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get order details (ensure it belongs to current customer)
$order_query = "SELECT * FROM orders WHERE order_id = ? AND customer_id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    header('Location: orders.php');
    exit();
}

$order = $order_result->fetch_assoc();

// Get order items
$items_query = "SELECT oi.*, p.product_name, p.size 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.product_id 
                WHERE oi.order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();

// Get delivery information if assigned
$delivery = null;
if ($order['delivery_person_id']) {
    $delivery_query = "SELECT d.*, u.full_name, u.phone 
                       FROM deliveries d 
                       JOIN users u ON d.delivery_person_id = u.user_id 
                       WHERE d.order_id = ?";
    $stmt = $conn->prepare($delivery_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $delivery_result = $stmt->get_result();
    if ($delivery_result->num_rows > 0) {
        $delivery = $delivery_result->fetch_assoc();
    }
}

$success_message = isset($_GET['success']) ? 'Order placed successfully! Your order is being processed.' : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Gas Delivery System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="dashboard.php" class="logo">
                    <span class="logo-icon"></span>
                    GasDelivery
                </a>
                <ul class="nav-menu">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="orders.php" class="active">My Orders</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="../logout.php" class="btn btn-danger btn-sm">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Order #<?php echo $order['order_id']; ?></h1>
                <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-8">
                    <!-- Order Items -->
                    <div class="card">
                        <div class="card-header">Order Items</div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Size</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = $items->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['size']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo formatCurrency($item['unit_price']); ?></td>
                                        <td><?php echo formatCurrency($item['subtotal']); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Total:</strong></td>
                                        <td><strong><?php echo formatCurrency($order['total_amount']); ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Delivery Address -->
                    <div class="card">
                        <div class="card-header">Delivery Address</div>
                        <div class="card-body">
                            <p><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
                            <?php if ($order['notes']): ?>
                            <p style="margin-top: 1rem;"><strong>Notes:</strong><br><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($delivery): ?>
                    <!-- Delivery Information -->
                    <div class="card">
                        <div class="card-header">Delivery Information</div>
                        <div class="card-body">
                            <p><strong>Delivery Person:</strong> <?php echo htmlspecialchars($delivery['full_name']); ?></p>
                            <p><strong>Contact:</strong> <?php echo htmlspecialchars($delivery['phone']); ?></p>
                            <p><strong>Delivery Status:</strong> 
                                <span class="badge <?php echo getOrderStatusClass($delivery['delivery_status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $delivery['delivery_status'])); ?>
                                </span>
                            </p>
                            <?php if ($delivery['pickup_time']): ?>
                            <p><strong>Picked Up:</strong> <?php echo formatDateTime($delivery['pickup_time']); ?></p>
                            <?php endif; ?>
                            <?php if ($delivery['delivery_time']): ?>
                            <p><strong>Delivered:</strong> <?php echo formatDateTime($delivery['delivery_time']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-4">
                    <!-- Order Status -->
                    <div class="card">
                        <div class="card-header">Order Status</div>
                        <div class="card-body">
                            <p><strong>Order Date:</strong><br><?php echo formatDateTime($order['order_date']); ?></p>
                            <p><strong>Order Status:</strong><br>
                                <span class="badge <?php echo getOrderStatusClass($order['order_status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                </span>
                            </p>
                            <p><strong>Payment Method:</strong><br><?php echo ucfirst($order['payment_method']); ?></p>
                            <p><strong>Payment Status:</strong><br>
                                <span class="badge <?php echo getPaymentStatusClass($order['payment_status']); ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </p>
                            <?php if ($order['delivery_date']): ?>
                            <p><strong>Delivered On:</strong><br><?php echo formatDateTime($order['delivery_date']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Order Timeline -->
                    <div class="card">
                        <div class="card-header">Order Timeline</div>
                        <div class="card-body">
                            <div style="position: relative; padding-left: 2rem;">
                                <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 2px; background-color: #e5e7eb;"></div>
                                
                                <div style="position: relative; margin-bottom: 1.5rem;">
                                    <div style="position: absolute; left: -2rem; width: 12px; height: 12px; border-radius: 50%; background-color: #10b981;"></div>
                                    <div style="font-weight: 600;">Order Placed</div>
                                    <div style="font-size: 0.875rem; color: #6b7280;"><?php echo formatDateTime($order['order_date']); ?></div>
                                </div>
                                
                                <?php if (in_array($order['order_status'], ['confirmed', 'in_transit', 'delivered'])): ?>
                                <div style="position: relative; margin-bottom: 1.5rem;">
                                    <div style="position: absolute; left: -2rem; width: 12px; height: 12px; border-radius: 50%; background-color: #10b981;"></div>
                                    <div style="font-weight: 600;">Order Confirmed</div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (in_array($order['order_status'], ['in_transit', 'delivered'])): ?>
                                <div style="position: relative; margin-bottom: 1.5rem;">
                                    <div style="position: absolute; left: -2rem; width: 12px; height: 12px; border-radius: 50%; background-color: #10b981;"></div>
                                    <div style="font-weight: 600;">Out for Delivery</div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($order['order_status'] === 'delivered'): ?>
                                <div style="position: relative; margin-bottom: 1.5rem;">
                                    <div style="position: absolute; left: -2rem; width: 12px; height: 12px; border-radius: 50%; background-color: #10b981;"></div>
                                    <div style="font-weight: 600;">Delivered</div>
                                    <?php if ($order['delivery_date']): ?>
                                    <div style="font-size: 0.875rem; color: #6b7280;"><?php echo formatDateTime($order['delivery_date']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($order['order_status'] === 'cancelled'): ?>
                                <div style="position: relative; margin-bottom: 1.5rem;">
                                    <div style="position: absolute; left: -2rem; width: 12px; height: 12px; border-radius: 50%; background-color: #ef4444;"></div>
                                    <div style="font-weight: 600;">Order Cancelled</div>
                                </div>
                                <?php endif; ?>
                            </div>
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
    <script>
        // Clear cart after successful order
        <?php if (isset($_GET['success'])): ?>
        clearCart();
        <?php endif; ?>
    </script>
</body>
</html>

<?php closeDatabaseConnection($conn); ?>
