<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireDelivery();

$conn = getDatabaseConnection();
$user_id = getCurrentUserId();
$delivery_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $new_status = sanitizeInput($_POST['delivery_status']);
        $notes = sanitizeInput($_POST['notes']);
        
        // Update delivery status
        if ($new_status === 'picked_up') {
            $stmt = $conn->prepare("UPDATE deliveries SET delivery_status = ?, pickup_time = NOW(), notes = ? WHERE delivery_id = ?");
            $stmt->bind_param("ssi", $new_status, $notes, $delivery_id);
        } elseif ($new_status === 'delivered') {
            $stmt = $conn->prepare("UPDATE deliveries SET delivery_status = ?, delivery_time = NOW(), notes = ? WHERE delivery_id = ?");
            $stmt->bind_param("ssi", $new_status, $notes, $delivery_id);
            
            // Also update order status
            if ($stmt->execute()) {
                $order_query = "SELECT order_id FROM deliveries WHERE delivery_id = ?";
                $stmt2 = $conn->prepare($order_query);
                $stmt2->bind_param("i", $delivery_id);
                $stmt2->execute();
                $order_id = $stmt2->get_result()->fetch_assoc()['order_id'];
                
                $update_order = $conn->prepare("UPDATE orders SET order_status = 'delivered', delivery_date = NOW(), payment_status = 'paid' WHERE order_id = ?");
                $update_order->bind_param("i", $order_id);
                $update_order->execute();
            }
        } else {
            $stmt = $conn->prepare("UPDATE deliveries SET delivery_status = ?, notes = ? WHERE delivery_id = ?");
            $stmt->bind_param("ssi", $new_status, $notes, $delivery_id);
        }
        
        if ($stmt->execute()) {
            // Update order status based on delivery status
            if ($new_status === 'in_transit') {
                $order_query = "SELECT order_id FROM deliveries WHERE delivery_id = ?";
                $stmt2 = $conn->prepare($order_query);
                $stmt2->bind_param("i", $delivery_id);
                $stmt2->execute();
                $order_id = $stmt2->get_result()->fetch_assoc()['order_id'];
                
                $update_order = $conn->prepare("UPDATE orders SET order_status = 'in_transit' WHERE order_id = ?");
                $update_order->bind_param("i", $order_id);
                $update_order->execute();
            }
            
            redirectWithMessage("delivery-details.php?id=$delivery_id", 'Delivery status updated successfully!', 'success');
        } else {
            $error = 'Failed to update delivery status';
        }
    }
}

// Get delivery details (ensure it belongs to current delivery person)
$delivery_query = "SELECT d.*, o.order_id, o.total_amount, o.delivery_address, o.order_status, o.payment_method, o.notes as order_notes,
                   u.full_name as customer_name, u.phone as customer_phone, u.email as customer_email
                   FROM deliveries d
                   JOIN orders o ON d.order_id = o.order_id
                   JOIN users u ON o.customer_id = u.user_id
                   WHERE d.delivery_id = ? AND d.delivery_person_id = ?";
$stmt = $conn->prepare($delivery_query);
$stmt->bind_param("ii", $delivery_id, $user_id);
$stmt->execute();
$delivery_result = $stmt->get_result();

if ($delivery_result->num_rows === 0) {
    header('Location: deliveries.php');
    exit();
}

$delivery = $delivery_result->fetch_assoc();

// Get order items
$items_query = "SELECT oi.*, p.product_name, p.size 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.product_id 
                WHERE oi.order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $delivery['order_id']);
$stmt->execute();
$items = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Details - Gas Delivery System</title>
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
                    <li><a href="deliveries.php" class="active">My Deliveries</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="../logout.php" class="btn btn-danger btn-sm">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Delivery #<?php echo $delivery['delivery_id']; ?></h1>
                <a href="deliveries.php" class="btn btn-secondary">Back to Deliveries</a>
            </div>

            <?php displayFlashMessage(); ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-8">
                    <!-- Customer Information -->
                    <div class="card">
                        <div class="card-header">Customer Information</div>
                        <div class="card-body">
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($delivery['customer_name']); ?></p>
                            <p><strong>Phone:</strong> <a href="tel:<?php echo htmlspecialchars($delivery['customer_phone']); ?>"><?php echo htmlspecialchars($delivery['customer_phone']); ?></a></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($delivery['customer_email']); ?></p>
                            <p><strong>Delivery Address:</strong><br><?php echo nl2br(htmlspecialchars($delivery['delivery_address'])); ?></p>
                            <?php if ($delivery['order_notes']): ?>
                            <p><strong>Order Notes:</strong><br><?php echo nl2br(htmlspecialchars($delivery['order_notes'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

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
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = $items->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['size']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo formatCurrency($item['unit_price']); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Total Amount:</strong></td>
                                        <td><strong><?php echo formatCurrency($delivery['total_amount']); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Payment Method:</strong></td>
                                        <td><?php echo ucfirst($delivery['payment_method']); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php if ($delivery['notes']): ?>
                    <!-- Delivery Notes -->
                    <div class="card">
                        <div class="card-header">Delivery Notes</div>
                        <div class="card-body">
                            <p><?php echo nl2br(htmlspecialchars($delivery['notes'])); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-4">
                    <!-- Delivery Status -->
                    <div class="card">
                        <div class="card-header">Delivery Status</div>
                        <div class="card-body">
                            <p><strong>Current Status:</strong><br>
                                <span class="badge <?php echo getOrderStatusClass($delivery['delivery_status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $delivery['delivery_status'])); ?>
                                </span>
                            </p>
                            <p><strong>Assigned Date:</strong><br><?php echo formatDateTime($delivery['assigned_date']); ?></p>
                            <?php if ($delivery['pickup_time']): ?>
                            <p><strong>Pickup Time:</strong><br><?php echo formatDateTime($delivery['pickup_time']); ?></p>
                            <?php endif; ?>
                            <?php if ($delivery['delivery_time']): ?>
                            <p><strong>Delivery Time:</strong><br><?php echo formatDateTime($delivery['delivery_time']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Update Status -->
                    <?php if ($delivery['delivery_status'] !== 'delivered' && $delivery['delivery_status'] !== 'failed'): ?>
                    <div class="card">
                        <div class="card-header">Update Status</div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_status">
                                <div class="form-group">
                                    <label class="form-label">New Status</label>
                                    <select name="delivery_status" class="form-control" required>
                                        <?php if ($delivery['delivery_status'] === 'assigned'): ?>
                                        <option value="picked_up">Picked Up</option>
                                        <?php endif; ?>
                                        <?php if ($delivery['delivery_status'] === 'picked_up' || $delivery['delivery_status'] === 'assigned'): ?>
                                        <option value="in_transit">In Transit</option>
                                        <?php endif; ?>
                                        <?php if ($delivery['delivery_status'] !== 'assigned'): ?>
                                        <option value="delivered">Delivered</option>
                                        <?php endif; ?>
                                        <option value="failed">Failed</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Notes (Optional)</label>
                                    <textarea name="notes" class="form-control" placeholder="Add any notes..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary" style="width: 100%;">Update Status</button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
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
