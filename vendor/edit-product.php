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
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header('Location: products.php');
    exit();
}

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
    
    // Update product
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE products SET product_name = ?, description = ?, size = ?, price = ?, stock_quantity = ?, status = ? WHERE product_id = ?");
        $stmt->bind_param("sssdisi", $product_name, $description, $size, $price, $stock_quantity, $status, $product_id);
        
        if ($stmt->execute()) {
            $success = 'Product updated successfully!';
        } else {
            $errors[] = 'Failed to update product';
        }
        $stmt->close();
    }
}

// Get product data
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: products.php');
    exit();
}

$pageTitle = 'Edit Product';
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
                <h1> Edit Product</h1>
                <a href="products.php" class="btn btn-secondary">← Back to Products</a>
            </div>
            
            <div class="card" style="max-width: 800px;">
                <div class="card-body">
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
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
                            <label for="product_id" class="form-label">Product ID</label>
                            <input type="text" id="product_id" class="form-control" value="#<?php echo $product['product_id']; ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label for="product_name" class="form-label">Product Name *</label>
                            <input type="text" id="product_name" name="product_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="size" class="form-label">Size *</label>
                            <input type="text" id="size" name="size" class="form-control" 
                                   placeholder="e.g., 6kg, 12kg, 25kg"
                                   value="<?php echo htmlspecialchars($product['size']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="price" class="form-label">Price (FCFA) *</label>
                            <input type="number" id="price" name="price" class="form-control" 
                                   step="0.01" min="0"
                                   value="<?php echo $product['price']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" 
                                   min="0"
                                   value="<?php echo $product['stock_quantity']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="status" class="form-label">Status *</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="active" <?php echo $product['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $product['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Created At</label>
                            <input type="text" class="form-control" value="<?php echo date('F d, Y H:i', strtotime($product['created_at'])); ?>" disabled>
                        </div>
                        
                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary">Update Product</button>
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
