<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireCustomer();

$conn = getDatabaseConnection();
$user_id = getCurrentUserId();

// Get user details
$user = getUserById($conn, $user_id);

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_address = isset($_POST['delivery_address']) ? sanitizeInput($_POST['delivery_address']) : '';
    $payment_method = isset($_POST['payment_method']) ? sanitizeInput($_POST['payment_method']) : '';
    $notes = isset($_POST['notes']) ? sanitizeInput($_POST['notes']) : '';
    $cart_data = isset($_POST['cart_data']) ? json_decode($_POST['cart_data'], true) : [];
    
    if (!empty($cart_data) && !empty($delivery_address) && !empty($payment_method)) {
        // Calculate total
        $total_amount = 0;
        foreach ($cart_data as $item) {
            $total_amount += $item['price'] * $item['quantity'];
        }
        
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, delivery_address, total_amount, payment_method, notes) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdss", $user_id, $delivery_address, $total_amount, $payment_method, $notes);
        
        if ($stmt->execute()) {
            $order_id = $conn->insert_id;
            
            // Insert order items
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
            
            foreach ($cart_data as $item) {
                $product_id = $item['productId'];
                $quantity = $item['quantity'];
                $unit_price = $item['price'];
                $subtotal = $unit_price * $quantity;
                
                $stmt->bind_param("iiidd", $order_id, $product_id, $quantity, $unit_price, $subtotal);
                $stmt->execute();
                
                // Update stock
                $update_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
                $update_stock->bind_param("ii", $quantity, $product_id);
                $update_stock->execute();
            }
            
            // Redirect to order confirmation
            header("Location: order-details.php?id=$order_id&success=1");
            exit();
        } else {
            $error = "Failed to place order. Please try again.";
        }
    } else {
        $error = "Please fill in all required fields and ensure your cart is not empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Gas Delivery System</title>
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
                    <li><a href="orders.php">My Orders</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="../logout.php" class="btn btn-danger btn-sm">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Checkout</h1>
                <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" id="checkout-form">
                <div class="row">
                    <div class="col-8">
                        <!-- Delivery Information -->
                        <div class="card">
                            <div class="card-header">Delivery Information</div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Delivery Address *</label>
                                    <textarea name="delivery_address" class="form-control" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Order Notes (Optional)</label>
                                    <textarea name="notes" class="form-control" placeholder="Any special instructions..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="card">
                            <div class="card-header">Payment Method</div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label style="display: flex; align-items: center; padding: 1rem; border: 2px solid #e5e7eb; border-radius: 6px; margin-bottom: 0.5rem; cursor: pointer;">
                                        <input type="radio" name="payment_method" value="cash" checked style="margin-right: 0.5rem;">
                                        <span style="flex: 1;">
                                            <strong>Cash on Delivery</strong><br>
                                            <small style="color: #6b7280;">Pay when you receive your order</small>
                                        </span>
                                    </label>
                                    <label style="display: flex; align-items: center; padding: 1rem; border: 2px solid #e5e7eb; border-radius: 6px; margin-bottom: 0.5rem; cursor: pointer;">
                                        <input type="radio" name="payment_method" value="card" style="margin-right: 0.5rem;">
                                        <span style="flex: 1;">
                                            <strong>Card Payment</strong><br>
                                            <small style="color: #6b7280;">Pay securely with credit/debit card</small>
                                        </span>
                                    </label>
                                    <label style="display: flex; align-items: center; padding: 1rem; border: 2px solid #e5e7eb; border-radius: 6px; cursor: pointer;">
                                        <input type="radio" name="payment_method" value="online" style="margin-right: 0.5rem;">
                                        <span style="flex: 1;">
                                            <strong>Online Payment</strong><br>
                                            <small style="color: #6b7280;">Pay via UPI, Net Banking, Wallets</small>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-4">
                        <!-- Order Summary -->
                        <div class="card">
                            <div class="card-header">Order Summary</div>
                            <div class="card-body" id="order-summary">
                                <!-- Will be populated by JavaScript -->
                            </div>
                            <div class="card-footer">
                                <input type="hidden" name="cart_data" id="cart_data">
                                <button type="submit" class="btn btn-primary" style="width: 100%;">Place Order</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Gas Delivery Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="../js/main.js"></script>
    <script>
        const cart = getCart();
        
        if (cart.length === 0) {
            window.location.href = 'products.php';
        }
        
        // Populate order summary
        let html = '';
        let total = 0;
        
        cart.forEach(item => {
            const subtotal = item.price * item.quantity;
            total += subtotal;
            html += `
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid #e5e7eb;">
                    <div>
                        <div style="font-weight: 500;">${item.productName}</div>
                        <div style="color: #6b7280; font-size: 0.875rem;">Qty: ${item.quantity} × ${formatCurrency(item.price)}</div>
                    </div>
                    <div style="font-weight: 600;">${formatCurrency(subtotal)}</div>
                </div>
            `;
        });
        
        html += `
            <div style="display: flex; justify-content: space-between; padding-top: 1rem; border-top: 2px solid #e5e7eb;">
                <strong>Total:</strong>
                <strong style="color: #2563eb; font-size: 1.5rem;">${formatCurrency(total)}</strong>
            </div>
        `;
        
        document.getElementById('order-summary').innerHTML = html;
        document.getElementById('cart_data').value = JSON.stringify(cart);
    </script>
</body>
</html>

<?php closeDatabaseConnection($conn); ?>
