<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireCustomer();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Gas Delivery System</title>
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
                    <li><a href="cart.php" class="btn btn-primary btn-sm active">
                         Cart <span id="cart-count" class="badge" style="background-color: #ef4444; margin-left: 0.25rem;">0</span>
                    </a></li>
                    <li><a href="../logout.php" class="btn btn-danger btn-sm">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Shopping Cart</h1>
            </div>

            <div class="row">
                <div class="col-8">
                    <div class="card">
                        <div class="card-header">Cart Items</div>
                        <div id="cart-items-container">
                            <div style="padding: 2rem; text-align: center; color: #6b7280;">
                                Your cart is empty
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-4">
                    <div class="card">
                        <div class="card-header">Order Summary</div>
                        <div class="card-body">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                                <span>Subtotal:</span>
                                <strong id="cart-subtotal">FCFA0.00</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                                <strong>Total:</strong>
                                <strong id="cart-total" style="color: #2563eb; font-size: 1.5rem;">FCFA0.00</strong>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="checkout.php" id="checkout-btn" class="btn btn-primary" style="width: 100%;">Proceed to Checkout</a>
                            <button onclick="clearCart(); location.reload();" class="btn btn-secondary" style="width: 100%; margin-top: 0.5rem;">Clear Cart</button>
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
        function displayCart() {
            const cart = getCart();
            const container = document.getElementById('cart-items-container');
            
            if (cart.length === 0) {
                container.innerHTML = '<div style="padding: 2rem; text-align: center; color: #6b7280;">Your cart is empty. <a href="products.php">Continue shopping</a></div>';
                document.getElementById('checkout-btn').disabled = true;
                return;
            }
            
            let html = '<table class="table"><thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th>Action</th></tr></thead><tbody>';
            
            cart.forEach(item => {
                const subtotal = item.price * item.quantity;
                html += `
                    <tr>
                        <td>${item.productName}</td>
                        <td>${formatCurrency(item.price)}</td>
                        <td>
                            <input type="number" value="${item.quantity}" min="1" 
                                   onchange="updateCartQuantity(${item.productId}, this.value); displayCart();" 
                                   style="width: 80px;" class="form-control">
                        </td>
                        <td>${formatCurrency(subtotal)}</td>
                        <td>
                            <button onclick="removeFromCart(${item.productId}); displayCart();" class="btn btn-sm btn-danger">Remove</button>
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            container.innerHTML = html;
            
            const total = getCartTotal();
            document.getElementById('cart-subtotal').textContent = formatCurrency(total);
            document.getElementById('cart-total').textContent = formatCurrency(total);
        }
        
        displayCart();
    </script>
</body>
</html>
