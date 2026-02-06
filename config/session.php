<?php
// Session management
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

// Check if user is admin
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_type'] === 'admin';
}

// Check if user is delivery personnel
function isDeliveryPerson() {
    return isLoggedIn() && $_SESSION['user_type'] === 'delivery';
}

// Check if user is customer
function isCustomer() {
    return isLoggedIn() && ($_SESSION['user_type'] === 'customer' || $_SESSION['user_type'] === 'vendor');
}

// Check if user is vendor
function isVendor() {
    return isLoggedIn() && $_SESSION['user_type'] === 'vendor';
}

// Get current user ID
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Get current user type
function getCurrentUserType() {
    return isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
}

// Get current user name
function getCurrentUserName() {
    return isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User';
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        $login_redirect = (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false || 
                          strpos($_SERVER['REQUEST_URI'], '/customer/') !== false || 
                          strpos($_SERVER['REQUEST_URI'], '/delivery/') !== false ||
                          strpos($_SERVER['REQUEST_URI'], '/vendor/') !== false) 
                          ? '../login.php' : 'login.php';
        header('Location: ' . $login_redirect);
        exit();
    }
}

// Require admin access
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $index_redirect = (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false || 
                          strpos($_SERVER['REQUEST_URI'], '/customer/') !== false || 
                          strpos($_SERVER['REQUEST_URI'], '/delivery/') !== false ||
                          strpos($_SERVER['REQUEST_URI'], '/vendor/') !== false) 
                          ? '../index.php' : 'index.php';
        header('Location: ' . $index_redirect);
        exit();
    }
}

// Require delivery person access
function requireDelivery() {
    requireLogin();
    if (!isDeliveryPerson()) {
        $index_redirect = (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false || 
                          strpos($_SERVER['REQUEST_URI'], '/customer/') !== false || 
                          strpos($_SERVER['REQUEST_URI'], '/delivery/') !== false ||
                          strpos($_SERVER['REQUEST_URI'], '/vendor/') !== false) 
                          ? '../index.php' : 'index.php';
        header('Location: ' . $index_redirect);
        exit();
    }
}

// Require customer access
function requireCustomer() {
    requireLogin();
    if (!isCustomer()) {
        $index_redirect = (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false || 
                          strpos($_SERVER['REQUEST_URI'], '/customer/') !== false || 
                          strpos($_SERVER['REQUEST_URI'], '/delivery/') !== false ||
                          strpos($_SERVER['REQUEST_URI'], '/vendor/') !== false) 
                          ? '../index.php' : 'index.php';
        header('Location: ' . $index_redirect);
        exit();
    }
}

// Logout user
function logout() {
    session_unset();
    session_destroy();
    // Determine the correct path based on current directory
    $logout_redirect = (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false || 
                        strpos($_SERVER['REQUEST_URI'], '/customer/') !== false || 
                        strpos($_SERVER['REQUEST_URI'], '/delivery/') !== false ||
                        strpos($_SERVER['REQUEST_URI'], '/vendor/') !== false) 
                        ? '../login.php' : 'login.php';
    header('Location: ' . $logout_redirect);
    exit();
}
?>
