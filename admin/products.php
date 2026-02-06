<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireAdmin();

$conn = getDatabaseConnection();

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $product_name = sanitizeInput($_POST['product_name']);
            $product_type = sanitizeInput($_POST['product_type']);
            $size = sanitizeInput($_POST['size']);
            $price = floatval($_POST['price']);
            $stock_quantity = intval($_POST['stock_quantity']);
            $description = sanitizeInput($_POST['description']);
            $status = sanitizeInput($_POST['status']);
            
            $stmt = $conn->prepare("INSERT INTO products (product_name, product_type, size, price, stock_quantity, description, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdiis", $product_name, $product_type, $size, $price, $stock_quantity, $description, $status);
            
            if ($stmt->execute()) {
                redirectWithMessage('products.php', 'Product added successfully!', 'success');
            } else {
                redirectWithMessage('products.php', 'Failed to add product', 'danger');
            }
        } elseif ($_POST['action'] === 'edit') {
            $product_id = intval($_POST['product_id']);
            $product_name = sanitizeInput($_POST['product_name']);
            $product_type = sanitizeInput($_POST['product_type']);
            $size = sanitizeInput($_POST['size']);
            $price = floatval($_POST['price']);
            $stock_quantity = intval($_POST['stock_quantity']);
            $description = sanitizeInput($_POST['description']);
            $status = sanitizeInput($_POST['status']);
            
            $stmt = $conn->prepare("UPDATE products SET product_name=?, product_type=?, size=?, price=?, stock_quantity=?, description=?, status=? WHERE product_id=?");
            $stmt->bind_param("sssdissi", $product_name, $product_type, $size, $price, $stock_quantity, $description, $status, $product_id);
            
            if ($stmt->execute()) {
                redirectWithMessage('products.php', 'Product updated successfully!', 'success');
            } else {
                redirectWithMessage('products.php', 'Failed to update product', 'danger');
            }
        } elseif ($_POST['action'] === 'delete') {
            $product_id = intval($_POST['product_id']);
            
            $stmt = $conn->prepare("DELETE FROM products WHERE product_id=?");
            $stmt->bind_param("i", $product_id);
            
            if ($stmt->execute()) {
                redirectWithMessage('products.php', 'Product deleted successfully!', 'success');
            } else {
                redirectWithMessage('products.php', 'Failed to delete product', 'danger');
            }
        }
    }
}

// Get all products
$products_query = "SELECT * FROM products ORDER BY created_at DESC";
$products = $conn->query($products_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="dashboard.php" class="logo">
                    <span class="logo-icon"></span>
                    GasDelivery Admin
                </a>
                <ul class="nav-menu">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="products.php" class="active">Products</a></li>
                    <li><a href="orders.php">Orders</a></li>
                    <li><a href="users.php">Users</a></li>
                    <li><a href="deliveries.php">Deliveries</a></li>
                    <li><a href="../logout.php" class="btn btn-danger btn-sm">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Manage Products</h1>
                <button onclick="showModal('addProductModal')" class="btn btn-primary">Add New Product</button>
            </div>

            <?php displayFlashMessage(); ?>

            <div class="card">
                <div class="card-header">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search products..." 
                           onkeyup="searchTable('searchInput', 'productsTable')" style="max-width: 300px;">
                </div>
                <div class="table-responsive">
                    <table class="table" id="productsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                
                                <th>Size</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = $products->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $product['product_id']; ?></td>
                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                
                                <td><?php echo htmlspecialchars($product['size']); ?></td>
                                <td><?php echo formatCurrency($product['price']); ?></td>
                                <td>
                                    <span class="badge <?php echo $product['stock_quantity'] < 20 ? 'status-cancelled' : 'status-delivered'; ?>">
                                        <?php echo $product['stock_quantity']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $product['status'] === 'available' ? 'status-delivered' : 'status-cancelled'; ?>">
                                        <?php echo ucfirst($product['status']); ?>
                                    </span>
                                </td>
                                <td class="table-actions">
                                    <button onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)" class="btn btn-sm btn-warning">Edit</button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirmAction('Are you sure you want to delete this product?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="hideModal('addProductModal')">&times;</span>
            <div class="modal-header">Add New Product</div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="product_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Size</label>
                    <input type="text" name="size" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Price</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Stock Quantity</label>
                    <input type="number" name="stock_quantity" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Add Product</button>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="hideModal('editProductModal')">&times;</span>
            <div class="modal-header">Edit Product</div>
            <form method="POST" id="editProductForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="product_id" id="edit_product_id">
                <div class="form-group">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="product_name" id="edit_product_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Size</label>
                    <input type="text" name="size" id="edit_size" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Price</label>
                    <input type="number" step="0.01" name="price" id="edit_price" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Stock Quantity</label>
                    <input type="number" name="stock_quantity" id="edit_stock_quantity" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="edit_description" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="edit_status" class="form-control" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update Product</button>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Gas Delivery Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="../js/main.js"></script>
    <script>
        function editProduct(product) {
            document.getElementById('edit_product_id').value = product.product_id;
            document.getElementById('edit_product_name').value = product.product_name;
            document.getElementById('edit_product_type').value = product.product_type;
            document.getElementById('edit_size').value = product.size;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_stock_quantity').value = product.stock_quantity;
            document.getElementById('edit_description').value = product.description || '';
            document.getElementById('edit_status').value = product.status;
            showModal('editProductModal');
        }
    </script>
</body>
</html>

<?php closeDatabaseConnection($conn); ?>
