<?php 
// Include auth system
require_once '../BackEnd/PHP-pages/session_auth.php';

// Require login for this page
requireLogin();

// Include user data
require_once '../BackEnd/PHP-pages/register.php';

// قائمة المستخدمين المسموح لهم بالوصول إلى لوحة التحكم
$allowedAdminUsers = [2320603, 2320598, 2320241];

// التحقق مما إذا كان المستخدم الحالي لديه صلاحيات الإدارة
$isAdmin = isset($_SESSION['user_id']) && in_array($_SESSION['user_id'], $allowedAdminUsers);
?>
<!DOCTYPE html>  
<html lang="en">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Smart Printer - User Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" 
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="user.css">
    <link rel="stylesheet" href="../Home_page/responsive.css">
    <style>
        .btn-admin {
            background-color: #d4af37;
            color: white;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }
        
        .btn-admin:hover {
            background-color: #c19d2c;
        }
    </style>
</head>  
<body>  
    <!-- Navigation Bar -->
    <div class="navbar">
        <div class="logo">
            <div class="logo-icon">AITP</div>
            <span>Smart Printer</span>
        </div>

        <div class="nav-links">
            <a href="../Home_page/home_page.php">Home</a>
            <a href="../Home_page/home_page.php#features">Features</a>
            <a href="../Home_page/home_page.php#how-it-works">How It Works</a>
            <a href="../Home_page/home_page.php#contact_us">Contact Us</a>
        </div>

        <div class="right-section">
            <a class="btn btn-print" href="../Home_page/Options_page.php">
                <i class="fa-solid fa-print"></i> Print Now
            </a>
            <?php if($isAdmin): ?>
            <a class="btn btn-admin" href="../BackEnd/Admin/index.php">
                <i class="fas fa-user-shield"></i> Admin Panel
            </a>
            <?php endif; ?>
            <a class="btn btn-logout" href="../BackEnd/PHP-pages/logout.php">
                <i class="fas fa-sign-out-alt"></i> Log out
            </a>
            <div class="menu-toggle" role="button" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>

    <!-- Mobile Sidebar -->
    <div class="sidebar" role="navigation" aria-label="Mobile navigation">
        <div class="sidebar-header">
            <div class="logo">
                <div class="logo-icon">AITP</div>
                <span>Smart Printer</span>
            </div>
            <div class="close-sidebar" role="button" aria-label="Close menu">×</div>
        </div>

        <div class="sidebar-nav">
            <a href="../Home_page/home_page.php">Home</a>
            <a href="../Home_page/home_page.php#features">Features</a>
            <a href="../Home_page/home_page.php#how-it-works">How It Works</a>
            <a href="../Home_page/Options_page.php">Print Now</a>
            <a href="../Home_page/home_page.php#contact_us">Contact Us</a>
            <?php if($isAdmin): ?>
            <a href="../admin/index.php"><i class="fas fa-user-shield"></i> Admin Panel</a>
            <?php endif; ?>
        </div>

        <div class="sidebar-footer">
            <a class="btn btn-logout" href="../BackEnd/PHP-pages/logout.php">
                <i class="fas fa-sign-out-alt"></i> Log out
            </a>
        </div>
    </div>

    <div class="overlay"></div>

    <!-- Payment Popup -->
    <div class="payment-popup" id="paymentPopup">  
        <div class="payment-box">  
            <h3>Confirm Your Payment</h3>  
            <p>Are you sure you want to proceed with the shipping?</p>  
            <button class="btn pay-btn" id="confirmPayBtn">Pay</button>  
            <button class="btn close-btn" id="cancelBtn">Cancel</button>
        </div>  
    </div>  

    <!-- Main Content -->
    <div class="profile-container">  
        <div class="user-info">  
            <div class="profile-header">  
                <div class="profile-picture-placeholder">  
                    <i class="fas fa-user user-icon"></i>  
                </div>
                <h2>User Profile</h2>  
            </div>  

            <div class="field-container">  
                <label class="field-label">Name</label>  
                <div class="field-value"><?php echo $users['name'] ?? $_SESSION['user_name']; ?></div>  
            </div>   

            <div class="field-container">  
                <label class="field-label">Email</label>  
                <div class="field-value"><?php echo $users['email'] ?? $_SESSION['user_email']; ?></div>  
            </div>  

            <div class="field-container">  
                <label class="field-label">ID</label>  
                <div class="field-value"><?php echo $users['id'] ?? $_SESSION['user_id']; ?></div>  
            </div>  
        </div>  

        <div class="balance-section">  
            <div class="balance-content">  
                <h2 class="balance-title">Your Balance</h2>  
                <div class="balance-amount">  
                    <img src="coin aitp.png" alt="Coin" class="coin-img">
                    <span><?php echo $users['balance'] ?? $_SESSION['user_balance']; ?></span>  
                </div>  
                <button class="btn" id="shippingBtn">Shipping</button>  
            </div>  
        </div>  
    </div>  

    <script>
    // Updated JavaScript for user.js
    document.addEventListener('DOMContentLoaded', function() {
        // Menu toggle for mobile sidebar
        const menuToggle = document.querySelector('.menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.overlay');
        const closeSidebar = document.querySelector('.close-sidebar');
        
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });
        
        closeSidebar.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
        
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
        
        // Payment popup functionality
        const shippingBtn = document.getElementById('shippingBtn');
        const paymentPopup = document.getElementById('paymentPopup');
        const confirmPayBtn = document.getElementById('confirmPayBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        
        if(shippingBtn) {
            shippingBtn.addEventListener('click', function() {
                paymentPopup.style.display = 'flex';
            });
        }
        
        if(cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                paymentPopup.style.display = 'none';
            });
        }
        
        if(confirmPayBtn) {
            confirmPayBtn.addEventListener('click', function() {
                // Add payment processing logic here
                alert('Payment processed successfully!');
                paymentPopup.style.display = 'none';
            });
        }
    });
    </script>
    <script src="../Home_page/responsive.js"></script>
</body>  
</html>