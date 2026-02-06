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

$success = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = sanitizeInput($_POST['product_name']);
    $description = sanitizeInput($_POST['description']);
    $size = sanitizeInput($_POST['size']);
    $price = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $status = sanitizeInput($_POST['status']);
    
    // Validation
    if (empty($product_name)) $errors[] = 'Product name is required';
    if (empty($size)) $errors[] = 'Size is required';
    if ($price <= 0) $errors[] = 'Price must be greater than 0';
    if ($stock_quantity < 0) $errors[] = 'Stock quantity cannot be negative';
    if (empty($status)) $errors[] = 'Status is required';
    
    // Insert product
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO products (product_name, description, size, price, stock_quantity, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdis", $product_name, $description, $size, $price, $stock_quantity, $status);
        
        if ($stmt->execute()) {
            $success = 'Product added successfully!';
            $_POST = []; // Clear form
        } else {
            $errors[] = 'Failed to add product';
        }
        $stmt->close();
    }
}

$pageTitle = 'Add Product';
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
                <h1>Add New Product</h1>
                <a href="products.php" class="btn btn-secondary">← Back to Products</a>
            </div>
            
            <div class="card" style="max-width: 800px;">
                <div class="card-body">
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                            <a href="products.php" class="btn btn-sm btn-primary" style="margin-left: 1rem;">View All Products</a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul style="margin: 0; padding-left: 20px;">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="product_name" class="form-label">Product Name:</label>
                            <input type="text" id="product_name" name="product_name" class="form-control" 
                                   value="<?php echo isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="size" class="form-label">Size:</label>
                            <input type="text" id="size" name="size" class="form-control" 
                                   placeholder="e.g., 6kg, 12kg, 25kg"
                                   value="<?php echo isset($_POST['size']) ? htmlspecialchars($_POST['size']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="price" class="form-label">Price (FCFA)</label>
                            <input type="number" id="price" name="price" class="form-control" 
                                   step="0.01" min="0"
                                   value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="stock_quantity" class="form-label">Stock Quantity:</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" 
                                   min="0"
                                   value="<?php echo isset($_POST['stock_quantity']) ? htmlspecialchars($_POST['stock_quantity']) : '0'; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="status" class="form-label">Status:</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary">Add Product</button>
                            <a href="products.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            </div>
        </main>
    </div>
    
    <script src="../js/main.js"></script>
</body>
</html>

<?php closeDatabaseConnection($conn); ?>
