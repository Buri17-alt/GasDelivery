<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/functions.php';

// Redirect based on user type
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

// If not logged in, show home page
$conn = getDatabaseConnection();

// Get featured products
$products_query = "SELECT * FROM products WHERE status = 'available' LIMIT 6";
$products_result = $conn->query($products_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gas Delivery Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="logo">
                    <span class="logo-icon"></span>
                    GasDelivery
                </a>
                <ul class="nav-menu">
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="login.php" class="btn btn-primary btn-sm">Login</a></li>
                    <li><a href="register.php" class="btn btn-outline btn-sm">Register</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section style="background: linear-gradient(135deg, #2563eb, #1e40af); color: white; padding: 4rem 0;">
        <div class="container">
            <div style="max-width: 600px; margin: 0 auto; text-align: center;">
                <h1 style="font-size: 3rem; margin-bottom: 1rem;">Fast & Reliable Gas Delivery</h1>
                <p style="font-size: 1.25rem; margin-bottom: 2rem; opacity: 0.9;">
                    Order your gas cylinders online and get them delivered to your doorstep quickly and safely.
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <a href="register.php" class="btn btn-lg" style="background-color: white; color: #2563eb;">Get Started</a>
                    
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section style="padding: 4rem 0;">
        <div class="container">
            <h2 style="text-align: center; font-size: 2.5rem; margin-bottom: 3rem;">Why Choose Us?</h2>
            <div class="stats-grid">
                <div class="card" style="text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;"></div>
                    <h3 style="margin-bottom: 0.5rem;">Fast Delivery</h3>
                    <p style="color: #6b7280;">Same day delivery available for urgent orders</p>
                </div>
                <div class="card" style="text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;"></div>
                    <h3 style="margin-bottom: 0.5rem;">Quality Products</h3>
                    <p style="color: #6b7280;">Certified gas cylinders from trusted suppliers</p>
                </div>
                <div class="card" style="text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;"></div>
                    <h3 style="margin-bottom: 0.5rem;">Best Prices</h3>
                    <p style="color: #6b7280;">Competitive pricing with no hidden charges</p>
                </div>
                <div class="card" style="text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;"></div>
                    <h3 style="margin-bottom: 0.5rem;">24/7 Support</h3>
                    <p style="color: #6b7280;">Round the clock customer support</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section style="padding: 4rem 0; background-color: #f9fafb;">
        <div class="container">
            <h2 style="text-align: center; font-size: 2.5rem; margin-bottom: 3rem;">Featured Products</h2>
            <div class="product-grid">
                <?php 
                 $products_result = $conn->query("SELECT product_name, size, price, stock_quantity FROM products");
                 while ($product = $products_result->fetch_assoc()): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="images/1.png" width="70px" height="70px" alt="">
                        </div>
                        <div class="product-body">
                            <h3 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                            <p class="product-size"><?php echo htmlspecialchars($product['size']); ?></p>
                            <p class="product-price"><?php echo formatCurrency($product['price']); ?></p>
                            <p class="product-stock">
                                <?php if ($product['stock_quantity'] > 0): ?>
                                    <span class="badge status-delivered">In Stock</span>
                                <?php else: ?>
                                    <span class="badge status-cancelled">Out of Stock</span>
                                <?php endif; ?>
                            </p>
                            <a href="login.php" class="btn btn-primary" style="width: 100%;">Order Now</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
           
        </div>
    </section>

    <!-- How It Works Section -->
    <section style="padding: 4rem 0;">
        <div class="container">
            <h2 style="text-align: center; font-size: 2.5rem; margin-bottom: 3rem;">How It Works</h2>
            <div class="row">
                <div class="col-3">
                    <div style="text-align: center; padding: 2rem;">
                        <div style="width: 80px; height: 80px; background-color: #2563eb; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1rem;">1</div>
                        <h3 style="margin-bottom: 0.5rem;">Register</h3>
                        <p style="color: #6b7280;">Create your account in minutes</p>
                    </div>
                </div>
                <div class="col-3">
                    <div style="text-align: center; padding: 2rem;">
                        <div style="width: 80px; height: 80px; background-color: #10b981; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1rem;">2</div>
                        <h3 style="margin-bottom: 0.5rem;">Order</h3>
                        <p style="color: #6b7280;">Choose your gas cylinder</p>
                    </div>
                </div>
                <div class="col-3">
                    <div style="text-align: center; padding: 2rem;">
                        <div style="width: 80px; height: 80px; background-color: #f59e0b; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1rem;">3</div>
                        <h3 style="margin-bottom: 0.5rem;">Track</h3>
                        <p style="color: #6b7280;">Monitor your delivery status</p>
                    </div>
                </div>
                <div class="col-3">
                    <div style="text-align: center; padding: 2rem;">
                        <div style="width: 80px; height: 80px; background-color: #ef4444; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1rem;">4</div>
                        <h3 style="margin-bottom: 0.5rem;">Receive</h3>
                        <p style="color: #6b7280;">Get it delivered at your doorstep</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Gas Delivery Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>

<?php closeDatabaseConnection($conn); ?>
