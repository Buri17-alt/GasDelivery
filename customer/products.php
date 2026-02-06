<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireCustomer();

$conn = getDatabaseConnection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Gas Delivery System</title>
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
                <li><a href="products.php" class="active">Products</a></li>
                <li><a href="orders.php">My Orders</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li>
                    <a href="cart.php" class="btn btn-primary btn-sm">
                        Cart <span id="cart-count" class="badge" style="background-color:#ef4444;margin-left:.25rem;">0</span>
                    </a>
                </li>
                <li><a href="../logout.php" class="btn btn-danger btn-sm">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Available Products</h1>
        </div>

        <?php displayFlashMessage(); ?>

        <div class="product-grid">
        <?php
        $products_result = $conn->query(" SELECT product_id, product_name, size, description, price, stock_quantity
            FROM products
            WHERE status = 'active'
            ORDER BY product_name 
            ");
        ?>

        <?php while ($product = $products_result->fetch_assoc()): ?>
            <div class="product-card">
                <div class="product-image"></div>
                <div class="product-body">
                    <h3 class="product-title">
                        <?php echo htmlspecialchars($product['product_name']); ?>
                    </h3>

                    <p class="product-size">
                        Size: <?php echo htmlspecialchars($product['size']); ?>
                    </p>

                    <?php if (!empty($product['description'])): ?>
                        <p style="color:#6b7280;font-size:.875rem;margin-bottom:1rem;">
                            <?php echo htmlspecialchars($product['description']); ?>
                        </p>
                    <?php endif; ?>

                    <p class="product-price">
                        <?php echo formatCurrency($product['price']); ?>
                    </p>

                    <p class="product-stock">
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <span class="badge status-delivered">
                                In Stock (<?php echo $product['stock_quantity']; ?>)
                            </span>
                        <?php else: ?>
                            <span class="badge status-cancelled">Out of Stock</span>
                        <?php endif; ?>
                    </p>

                    <?php if ($product['stock_quantity'] > 0): ?>
                        <div style="margin-bottom:1rem;">
                            <label style="display:block;margin-bottom:.5rem;font-size:.875rem;">
                                Quantity:
                            </label>
                            <input
                                type="number"
                                id="qty_<?php echo (int)$product['product_id']; ?>"
                                value="1"
                                min="1"
                                max="<?php echo (int)$product['stock_quantity']; ?>"
                                class="form-control"
                            >
                        </div>

                        <button
                            onclick='addToCartWithQty(
                                <?php echo (int)$product["product_id"]; ?>,
                                <?php echo json_encode($product["product_name"]); ?>,
                                <?php echo (float)$product["price"]; ?>
                            )'
                            class="btn btn-primary"
                            style="width:100%;">
                            Add to Cart
                        </button>
                    <?php else: ?>
                        <button class="btn btn-secondary" style="width:100%;" disabled>
                            Out of Stock
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
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
function addToCartWithQty(productId, productName, price) {
    const qtyInput = document.getElementById('qty_' + productId);
    const quantity = parseInt(qtyInput.value, 10) || 1;
    addToCart(productId, productName, price, quantity);
}
</script>
</body>
</html>

<?php closeDatabaseConnection($conn); ?>
