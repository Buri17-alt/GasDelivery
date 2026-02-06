<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireDelivery();

$conn = getDatabaseConnection();
$user_id = getCurrentUserId();

// Get all deliveries for this delivery person
$deliveries_query = "SELECT d.*, o.order_id, o.total_amount, o.delivery_address, o.order_status,
                     u.full_name as customer_name, u.phone as customer_phone, u.email as customer_email
                     FROM deliveries d
                     JOIN orders o ON d.order_id = o.order_id
                     JOIN users u ON o.customer_id = u.user_id
                     WHERE d.delivery_person_id = ?
                     ORDER BY d.assigned_at DESC";
$stmt = $conn->prepare($deliveries_query);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$deliveries = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Deliveries - Gas Delivery System</title>
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
                <h1 class="page-title">My Deliveries</h1>
            </div>

            <?php displayFlashMessage(); ?>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex gap-2">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search deliveries..." 
                               onkeyup="searchTable('searchInput', 'deliveriesTable')" style="max-width: 300px;">
                        <select id="statusFilter" class="form-control" onchange="filterTable('statusFilter', 'deliveriesTable', 4)" style="max-width: 200px;">
                            <option value="">All Status</option>
                            <option value="assigned">Assigned</option>
                            <option value="picked_up">Picked Up</option>
                            <option value="in_transit">In Transit</option>
                            <option value="delivered">Delivered</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table" id="deliveriesTable">
                        <thead>
                            <tr>
                                <th>Delivery ID</th>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Assigned Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($deliveries->num_rows > 0): ?>
                                <?php while ($delivery = $deliveries->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $delivery['delivery_id']; ?></td>
                                    <td>#<?php echo $delivery['order_id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($delivery['customer_name']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($delivery['customer_phone']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($delivery['delivery_address'], 0, 50)) . '...'; ?></td>
                                    <td>
                                        <span class="badge <?php echo getOrderStatusClass($delivery['delivery_status']); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $delivery['delivery_status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDateTime($delivery['assigned_date']); ?></td>
                                    <td>
                                        <a href="delivery-details.php?id=<?php echo $delivery['delivery_id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No deliveries assigned yet</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
