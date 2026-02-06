<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Gas Delivery System</title>
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
                    <li><a href="index.php">Home</a></li>
                 
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php" class="active">Contact</a></li>
                    <li><a href="login.php" class="btn btn-primary btn-sm">Login</a></li>
                    <li><a href="register.php" class="btn btn-outline btn-sm">Register</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Contact Us</h1>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">Get In Touch</div>
                        <div class="card-body">
                            <form>
                                <div class="form-group">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Subject</label>
                                    <input type="text" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Message</label>
                                    <textarea class="form-control" rows="5" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Send Message</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="card">
                        <div class="card-header">Contact Information</div>
                        <div class="card-body">
                            <div style="margin-bottom: 2rem;">
                                <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-size: 1.5rem; margin-right: 1rem;"></span>
                                    <strong>Address</strong>
                                </div>
                                <p style="margin-left: 2.5rem; color: #6b7280;">
                                Rue 756, Entree Simbock<br>
                                Yaounde, Cameroon
                                </p>
                            </div>

                            <div style="margin-bottom: 2rem;">
                                <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-size: 1.5rem; margin-right: 1rem;"></span>
                                    <strong>Phone</strong>
                                </div>
                                <p style="margin-left: 2.5rem; color: #6b7280;">
                                    +1 (234) 567-8900<br>
                                    +1 (234) 567-8901
                                </p>
                            </div>

                            <div style="margin-bottom: 2rem;">
                                <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-size: 1.5rem; margin-right: 1rem;"></span>
                                    <strong>Email</strong>
                                </div>
                                <p style="margin-left: 2.5rem; color: #6b7280;">
                                    info@gasdelivery.com<br>
                                    support@gasdelivery.com
                                </p>
                            </div>

                            <div>
                                <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-size: 1.5rem; margin-right: 1rem;"></span>
                                    <strong>Business Hours</strong>
                                </div>
                                <p style="margin-left: 2.5rem; color: #6b7280;">
                                    Monday - Saturday: 8:00 AM - 8:00 PM<br>
                                    Sunday: 9:00 AM - 6:00 PM<br>
                                    <span class="badge status-delivered">24/7 Emergency Support</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">Quick Links</div>
                        <div class="card-body">
                            <p><a href="register.php" style="color: #2563eb; text-decoration: none;">→ Create an Account</a></p>
                           
                            <p><a href="login.php" style="color: #2563eb; text-decoration: none;">→ Track Your Order</a></p>
                            <p><a href="about.php" style="color: #2563eb; text-decoration: none;">→ About Us</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Gas Delivery Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>
