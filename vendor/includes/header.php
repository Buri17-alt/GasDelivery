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
