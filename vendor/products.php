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

// Get all products
$products_query = "SELECT * FROM products ORDER BY created_at DESC";
$products_result = $conn->query($products_query);

$pageTitle = 'Products';
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
                <h1>Products</h1>
                <div>
                    <a href="add-product.php" class="btn btn-primary">+ Add New Product</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <?php if ($products_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Product Name</th>
                                        <th>Size</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($product = $products_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $product['product_id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($product['product_name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['size']); ?></td>
                                        <td><?php echo formatCurrency($product['price']); ?></td>
                                        <td>
                                            <?php if ($product['stock_quantity'] > 10): ?>
                                                <span class="badge status-delivered"><?php echo $product['stock_quantity']; ?> in stock</span>
                                            <?php elseif ($product['stock_quantity'] > 0): ?>
                                                <span class="badge status-pending"><?php echo $product['stock_quantity']; ?> low stock</span>
                                            <?php else: ?>
                                                <span class="badge status-cancelled">Out of stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge status-<?php echo $product['status']; ?>">
                                                <?php echo ucfirst($product['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                                        <td>
                                            <a href="edit-product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p class="text-muted">No products found.</p>
                            <a href="add-product.php" class="btn btn-primary">Add Your First Product</a>
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
