<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['admin'])){
    header("Location: admin-login.php");
    exit;
}

$adminName = $_SESSION['admin'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : "Admin Panel"; ?> - GreenTour</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Admin Header Specific Styles - Scoped to prevent conflicts */
        .admin-header-root {
            --primary-color: #2e7d32;
            --primary-dark: #1b5e20;
            --primary-light: #4caf50;
            --primary-lighter: #e8f5e9;
            --secondary-color: #f5f5f5;
            --text-color: #333;
            --text-light: #777;
            --white: #ffffff;
            --shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            --shadow-light: 0 2px 8px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
        }
        
        .admin-header-root * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
        }
        
        .admin-header-root {
            display: flex;
            min-height: 100vh;
            background: #f0f4f0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
            overflow-x: hidden;
        }

        /* Mobile Overlay */
        .admin-mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: var(--transition);
        }
        .admin-mobile-overlay.active {
            display: block;
            opacity: 1;
        }

        /* Sidebar */
        .admin-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(to bottom, var(--primary-color), var(--primary-dark));
            color: var(--white);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: var(--shadow);
            transition: var(--transition);
            z-index: 1000;
            left: 0;
        }
        
        .admin-sidebar.collapsed { 
            width: var(--sidebar-collapsed-width); 
        }
        
        .admin-logo-container { 
            padding: 20px 15px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 1px solid rgba(255, 255, 255, 0.1); 
        }
        
        .admin-logo { 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        
        .admin-logo i { 
            font-size: 24px; 
        }
        
        .admin-logo-text { 
            font-size: 20px; 
            font-weight: 600; 
            transition: var(--transition); 
            white-space: nowrap;
        }
        
        .admin-sidebar.collapsed .admin-logo-text { 
            display: none; 
        }
        
        .admin-toggle-btn { 
            background: none; 
            border: none; 
            color: var(--white); 
            cursor: pointer; 
            font-size: 18px; 
            transition: var(--transition); 
            padding: 5px; 
            border-radius: 4px; 
        }
        
        .admin-toggle-btn:hover { 
            background: rgba(255, 255, 255, 0.1); 
        }
        
        .admin-sidebar.collapsed .admin-toggle-btn { 
            transform: rotate(180deg); 
        }
        
        .admin-menu { 
            padding: 15px 0; 
        }
        
        .admin-menu-item { 
            display: flex; 
            align-items: center; 
            padding: 12px 20px; 
            color: white; 
            text-decoration: none; 
            transition: var(--transition); 
            border-left: 3px solid transparent; 
            white-space: nowrap;
        }
        
        .admin-menu-item i { 
            margin-right: 15px; 
            font-size: 16px; 
            width: 20px; 
            text-align: center; 
        }
        
        .admin-menu-item.active, 
        .admin-menu-item:hover { 
            background: rgba(255, 255, 255, 0.1); 
            border-left: 3px solid var(--white); 
        }
        
        .admin-sidebar.collapsed .admin-menu-item span { 
            display: none; 
        }
        
        .admin-sidebar.collapsed .admin-menu-item { 
            justify-content: center; 
            padding: 15px 0; 
        }
        
        .admin-sidebar.collapsed .admin-menu-item i { 
            margin-right: 0; 
        }

        /* Main Content */
        .admin-main-content { 
            flex: 1; 
            margin-left: var(--sidebar-width); 
            padding: 20px; 
            transition: var(--transition); 
            width: calc(100% - var(--sidebar-width));
            min-height: 100vh;
        }
        
        .admin-sidebar.collapsed ~ .admin-main-content { 
            margin-left: var(--sidebar-collapsed-width); 
            width: calc(100% - var(--sidebar-collapsed-width));
        }

        /* Topbar */
        .admin-topbar { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            background: var(--white); 
            padding: 15px 25px; 
            border-radius: 10px; 
            box-shadow: var(--shadow-light); 
            margin-bottom: 25px; 
        }
        
        .admin-topbar h1 { 
            color: var(--primary-color); 
            font-size: 22px; 
            font-weight: 600; 
            flex: 1;
        }
        
        .admin-user-info { 
            display: flex; 
            align-items: center; 
            gap: 15px; 
        }
        
        .admin-user-avatar { 
            width: 40px; 
            height: 40px; 
            border-radius: 50%; 
            background: var(--primary-light); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: var(--white); 
            font-weight: bold; 
            box-shadow: var(--shadow-light); 
        }
        
        .admin-logout-btn { 
            background: var(--primary-color); 
            color: var(--white); 
            border: none; 
            padding: 8px 15px; 
            border-radius: 5px; 
            cursor: pointer; 
            text-decoration: none; 
            transition: var(--transition); 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            font-size: 14px; 
        }
        
        .admin-logout-btn:hover { 
            background: var(--primary-dark); 
            transform: translateY(-2px); 
        }

        /* Mobile Toggle Button */
        .admin-mobile-toggle { 
            display: none; 
            font-size: 22px; 
            background: none; 
            border: none; 
            color: var(--primary-color); 
            cursor: pointer; 
            padding: 8px; 
            border-radius: 4px; 
            transition: var(--transition); 
        }
        
        .admin-mobile-toggle:hover { 
            background: var(--primary-lighter); 
        }

        /* Hamburger Animation */
        .admin-hamburger {
            width: 24px;
            height: 18px;
            position: relative;
            cursor: pointer;
        }

        .admin-hamburger span {
            display: block;
            position: absolute;
            height: 2px;
            width: 100%;
            background: var(--primary-color);
            border-radius: 2px;
            opacity: 1;
            left: 0;
            transform: rotate(0deg);
            transition: .25s ease-in-out;
        }

        .admin-hamburger span:nth-child(1) {
            top: 0px;
        }

        .admin-hamburger span:nth-child(2),
        .admin-hamburger span:nth-child(3) {
            top: 8px;
        }

        .admin-hamburger span:nth-child(4) {
            top: 16px;
        }

        .admin-hamburger.active span:nth-child(1) {
            top: 8px;
            width: 0%;
            left: 50%;
        }

        .admin-hamburger.active span:nth-child(2) {
            transform: rotate(45deg);
        }

        .admin-hamburger.active span:nth-child(3) {
            transform: rotate(-45deg);
        }

        .admin-hamburger.active span:nth-child(4) {
            top: 8px;
            width: 0%;
            left: 50%;
        }

        /* Breadcrumb */
        .admin-breadcrumb { 
            background: var(--white); 
            padding: 12px 20px; 
            border-radius: 8px; 
            box-shadow: var(--shadow-light); 
            margin-bottom: 20px; 
            font-size: 14px; 
            color: var(--text-light); 
        }
        
        .admin-breadcrumb a { 
            color: var(--primary-color); 
            text-decoration: none; 
        }
        
        .admin-breadcrumb a:hover { 
            text-decoration: underline; 
        }

        .admin-content-area { 
            background: var(--white); 
            border-radius: 10px; 
            padding: 25px; 
            box-shadow: var(--shadow-light); 
            min-height: 400px; 
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .admin-sidebar {
                width: var(--sidebar-collapsed-width);
            }
            .admin-sidebar .admin-logo-text,
            .admin-sidebar .admin-menu-item span {
                display: none;
            }
            .admin-sidebar .admin-menu-item {
                justify-content: center;
                padding: 15px 0;
            }
            .admin-sidebar .admin-menu-item i {
                margin-right: 0;
            }
            .admin-main-content {
                margin-left: var(--sidebar-collapsed-width);
                width: calc(100% - var(--sidebar-collapsed-width));
            }
            .admin-toggle-btn {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width);
            }
            
            .admin-sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .admin-main-content {
                margin-left: 0;
                width: 100%;
                padding: 15px;
            }
            
            .admin-sidebar.collapsed ~ .admin-main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .admin-mobile-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .admin-mobile-overlay {
                display: block;
            }
            
            .admin-topbar {
                padding: 15px 20px;
            }
            
            .admin-topbar h1 {
                font-size: 20px;
                margin-left: 15px;
            }
            
            .admin-user-info span {
                display: none;
            }
            
            .admin-logout-btn span {
                display: none;
            }
            
            .admin-logout-btn {
                padding: 10px;
                min-width: auto;
            }
            
            .admin-content-area {
                padding: 20px;
            }
            
            .admin-breadcrumb {
                padding: 10px 15px;
                font-size: 13px;
            }
        }

        @media (max-width: 480px) {
            .admin-main-content {
                padding: 10px;
            }
            
            .admin-topbar {
                padding: 12px 15px;
                flex-wrap: wrap;
                gap: 10px;
            }
            
            .admin-topbar h1 {
                font-size: 18px;
                margin-left: 10px;
                order: 2;
                flex: 1 1 100%;
                text-align: center;
                margin-top: 10px;
            }
            
            .admin-user-avatar {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }
            
            .admin-content-area {
                padding: 15px;
            }
            
            .admin-logout-btn {
                padding: 8px;
            }
            
            .admin-mobile-toggle {
                order: 1;
            }
            
            .admin-user-info {
                order: 3;
            }
        }

        /* Smooth scrollbar for sidebar */
        .admin-sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .admin-sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .admin-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }
        
        .admin-sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Ensure no conflicts with other pages */
        .admin-header-root body,
        .admin-header-root html {
            margin: 0;
            padding: 0;
            height: 100%;
        }
    </style>
</head>
<body class="admin-header-root">

<!-- Mobile Overlay -->
<div class="admin-mobile-overlay" id="adminMobileOverlay"></div>

<!-- Sidebar -->
<div class="admin-sidebar" id="adminSidebar">
    <div class="admin-logo-container">
        <div class="admin-logo">
            <i class="fas fa-leaf"></i>
            <span class="admin-logo-text">GreenTour Admin</span>
        </div>
        <button class="admin-toggle-btn" id="adminToggleSidebar">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>
    <div class="admin-menu">
        <a href="dashboard.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
        </a>
        <a href="manage-users.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage-users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i><span>Users</span>
        </a>
        <a href="manage-destinations.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage-destinations.php' ? 'active' : ''; ?>">
            <i class="fas fa-map-marker-alt"></i><span>Destinations</span>
        </a>
        <a href="manage-hotels.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage-hotels.php' ? 'active' : ''; ?>">
            <i class="fas fa-hotel"></i><span>Hotels</span>
        </a>
        <a href="manage-rooms.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage-rooms.php' ? 'active' : ''; ?>">
            <i class="fas fa-bed"></i><span>Rooms</span>
        </a>
        <a href="manage-bookings.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage-bookings.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i><span>Bookings</span>
        </a>
        <a href="manage-payments.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage-payments.php' ? 'active' : ''; ?>">
            <i class="fas fa-credit-card"></i><span>Payments</span>
        </a>
        <a href="manage-reviews.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage-reviews.php' ? 'active' : ''; ?>">
            <i class="fas fa-star"></i><span>Reviews</span>
        </a>
        <a href="manage-gallery.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage-gallery.php' ? 'active' : ''; ?>">
            <i class="fas fa-images"></i><span>Gallery</span>
        </a>
        <a href="manage-transport.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage-transport.php' ? 'active' : ''; ?>">
            <i class="fas fa-bus"></i><span>Transport</span>
        </a>
        <a href="manage-blogs.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage-blogs.php' ? 'active' : ''; ?>">
            <i class="fas fa-blog"></i><span>Blogs</span>
        </a>
        <a href="settings.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i><span>Settings</span>
        </a>
        <a href="logout.php" class="admin-menu-item">
            <i class="fas fa-sign-out-alt"></i><span>Logout</span>
        </a>
    </div>
</div>

<!-- Main Content -->
<div class="admin-main-content">
    <div class="admin-topbar">
        <button class="admin-mobile-toggle" id="adminMobileToggle">
            <div class="admin-hamburger" id="adminHamburger">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>
        </button>
        <h1><?php echo isset($pageTitle) ? $pageTitle : "Admin Panel"; ?></h1>
        <div class="admin-user-info">
            <div class="admin-user-avatar"><?php echo strtoupper(substr($adminName,0,1)); ?></div>
            <span>Welcome, <?php echo $adminName; ?></span>
            <a href="logout.php" class="admin-logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <?php if(isset($breadcrumb)): ?>
    <div class="admin-breadcrumb">
        <a href="dashboard.php">Dashboard</a>
        <?php if(is_array($breadcrumb)): ?>
            <?php foreach($breadcrumb as $item): ?>
                &raquo; 
                <?php if(isset($item['url'])): ?>
                    <a href="<?php echo $item['url']; ?>"><?php echo $item['title']; ?></a>
                <?php else: ?>
                    <span><?php echo $item['title']; ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            &raquo; <span><?php echo $breadcrumb; ?></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="admin-content-area">

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Admin header specific JavaScript - scoped to prevent conflicts
    const adminSidebar = document.getElementById('adminSidebar');
    const adminToggleSidebar = document.getElementById('adminToggleSidebar');
    const adminMobileToggle = document.getElementById('adminMobileToggle');
    const adminHamburger = document.getElementById('adminHamburger');
    const adminMobileOverlay = document.getElementById('adminMobileOverlay');

    // Desktop sidebar toggle
    if (adminToggleSidebar) {
        adminToggleSidebar.addEventListener('click', function() {
            adminSidebar.classList.toggle('collapsed');
            localStorage.setItem('adminSidebarCollapsed', adminSidebar.classList.contains('collapsed'));
        });
    }

    // Mobile sidebar toggle
    if (adminMobileToggle) {
        adminMobileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            adminSidebar.classList.toggle('mobile-open');
            adminHamburger.classList.toggle('active');
            adminMobileOverlay.classList.toggle('active');
            document.body.style.overflow = adminSidebar.classList.contains('mobile-open') ? 'hidden' : '';
        });
    }

    // Close mobile menu when clicking overlay
    if (adminMobileOverlay) {
        adminMobileOverlay.addEventListener('click', function() {
            adminSidebar.classList.remove('mobile-open');
            adminHamburger.classList.remove('active');
            adminMobileOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    }

    // Close mobile menu when clicking menu item
    const adminMenuItems = document.querySelectorAll('.admin-menu-item');
    adminMenuItems.forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                adminSidebar.classList.remove('mobile-open');
                adminHamburger.classList.remove('active');
                adminMobileOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    // Restore sidebar state on desktop
    const adminSidebarCollapsed = localStorage.getItem('adminSidebarCollapsed');
    if (adminSidebarCollapsed === 'true' && window.innerWidth > 768) {
        adminSidebar.classList.add('collapsed');
    }

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            // Desktop view
            adminSidebar.classList.remove('mobile-open');
            adminHamburger.classList.remove('active');
            adminMobileOverlay.classList.remove('active');
            document.body.style.overflow = '';
            
            // Restore collapsed state if it was saved
            const adminSidebarCollapsed = localStorage.getItem('adminSidebarCollapsed');
            if (adminSidebarCollapsed === 'true') {
                adminSidebar.classList.add('collapsed');
            }
        } else {
            // Mobile view - remove collapsed class
            adminSidebar.classList.remove('collapsed');
        }
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && 
            adminSidebar.classList.contains('mobile-open') &&
            !adminSidebar.contains(e.target) && 
            !adminMobileToggle.contains(e.target)) {
            adminSidebar.classList.remove('mobile-open');
            adminHamburger.classList.remove('active');
            adminMobileOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});
</script>