<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - GreenTour</title>
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
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --sidebar-width: 280px;
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
            transition: var(--transition);
        }
        
        .admin-logo-text { 
            font-size: 20px; 
            font-weight: 600; 
            transition: var(--transition); 
            white-space: nowrap;
        }
        
        .admin-sidebar.collapsed .admin-logo-text { 
            opacity: 0;
            width: 0;
            overflow: hidden;
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
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
        }
        
        .admin-toggle-btn:hover { 
            background: rgba(255, 255, 255, 0.1); 
            transform: rotate(180deg);
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
            position: relative;
            overflow: hidden;
        }
        
        .admin-menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }
        
        .admin-menu-item:hover::before {
            left: 100%;
        }
        
        .admin-menu-item i { 
            margin-right: 15px; 
            font-size: 16px; 
            width: 20px; 
            text-align: center; 
            transition: var(--transition);
        }
        
        .admin-menu-item.active, 
        .admin-menu-item:hover { 
            background: rgba(255, 255, 255, 0.1); 
            border-left: 3px solid var(--white); 
        }
        
        .admin-sidebar.collapsed .admin-menu-item span { 
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        .admin-sidebar.collapsed .admin-menu-item { 
            justify-content: center; 
            padding: 15px 0; 
        }
        
        .admin-sidebar.collapsed .admin-menu-item i { 
            margin-right: 0; 
            transform: scale(1.2);
        }

        /* Submenu Styles */
        .admin-menu-item.has-submenu {
            position: relative;
            cursor: pointer;
        }

        .admin-menu-item.has-submenu > .submenu-arrow {
            margin-left: auto;
            transition: transform 0.3s ease;
            font-size: 12px;
        }

     

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .admin-submenu a {
            padding: 10px 20px 10px 50px;
            display: block;
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: var(--transition);
            position: relative;
        }

        .admin-submenu a::before {
            content: '';
            position: absolute;
            left: 30px;
            top: 50%;
            transform: translateY(-50%);
            width: 6px;
            height: 6px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            transition: var(--transition);
        }

        .admin-submenu a.active,
        .admin-submenu a:hover {
            background: rgba(255, 255, 255, 0.1);
            border-left: 3px solid var(--white);
            padding-left: 55px;
        }

        .admin-submenu a.active::before,
        .admin-submenu a:hover::before {
            background: var(--white);
            transform: translateY(-50%) scale(1.2);
        }

        .admin-menu-item.has-submenu.active > .submenu-arrow {
            transform: rotate(180deg);
        }

        .admin-menu-item.has-submenu.active .admin-submenu {
            display: flex;
        }

        .admin-sidebar.collapsed .admin-submenu {
            position: absolute;
            left: 100%;
            top: 0;
            width: 200px;
            background: var(--primary-dark);
            border-radius: 0 8px 8px 0;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.2);
            z-index: 1001;
        }

        .admin-sidebar.collapsed .admin-menu-item.has-submenu:hover .admin-submenu {
            display: flex;
        }

        .admin-sidebar.collapsed .admin-submenu a {
            padding-left: 20px;
        }

        .admin-sidebar.collapsed .admin-submenu a::before {
            left: 8px;
        }

        .admin-sidebar.collapsed .admin-submenu a.active,
        .admin-sidebar.collapsed .admin-submenu a:hover {
            padding-left: 25px;
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
            animation: slideInDown 0.5s ease-out;
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            transition: var(--transition);
        }
        
        .admin-user-avatar:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
            transform: rotate(0deg);
            transition: .5s ease-in-out;
        }

        .admin-hamburger span {
            display: block;
            position: absolute;
            height: 3px;
            width: 100%;
            background: var(--primary-color);
            border-radius: 3px;
            opacity: 1;
            left: 0;
            transform: rotate(0deg);
            transition: .25s ease-in-out;
        }

        .admin-hamburger span:nth-child(1) {
            top: 0px;
            transform-origin: left center;
        }

        .admin-hamburger span:nth-child(2) {
            top: 8px;
            transform-origin: left center;
        }

        .admin-hamburger span:nth-child(3) {
            top: 16px;
            transform-origin: left center;
        }

        .admin-hamburger.active span:nth-child(1) {
            transform: rotate(45deg);
            top: -1px;
            left: 4px;
        }

        .admin-hamburger.active span:nth-child(2) {
            width: 0%;
            opacity: 0;
        }

        .admin-hamburger.active span:nth-child(3) {
            transform: rotate(-45deg);
            top: 17px;
            left: 4px;
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
            animation: fadeIn 0.5s ease-out 0.2s both;
        }
        
        .admin-breadcrumb a { 
            color: var(--primary-color); 
            text-decoration: none; 
            transition: var(--transition);
            position: relative;
        }
        
        .admin-breadcrumb a:hover { 
            color: var(--primary-dark);
            text-decoration: underline; 
        }
        
        .admin-breadcrumb a::after {
            content: '›';
            margin: 0 8px;
            color: var(--text-light);
        }
        
        .admin-breadcrumb span:last-child {
            color: var(--primary-dark);
            font-weight: 500;
        }

        .admin-content-area { 
            background: var(--white); 
            border-radius: 10px; 
            padding: 25px; 
            box-shadow: var(--shadow-light); 
            min-height: 400px; 
            animation: fadeInUp 0.5s ease-out 0.3s both;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .admin-sidebar {
                width: var(--sidebar-collapsed-width);
            }
            .admin-sidebar .admin-logo-text,
            .admin-sidebar .admin-menu-item span {
                opacity: 0;
                width: 0;
                overflow: hidden;
            }
            .admin-sidebar .admin-menu-item {
                justify-content: center;
                padding: 15px 0;
            }
            .admin-sidebar .admin-menu-item i {
                margin-right: 0;
                transform: scale(1.2);
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
        <a href="dashboard.php" class="admin-menu-item active">
            <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
        </a>

        <!-- Users -->
        <div class="admin-menu-item has-submenu">
            <i class="fas fa-users"></i><span>Users</span>
            <i class="fas fa-chevron-down submenu-arrow"></i>
            <div class="admin-submenu">
                <a href="manage-users.php" class="active">Show Users</a>
                <a href="add-user.php">Add User</a>
            </div>
        </div>

        <!-- Destinations -->
        <div class="admin-menu-item has-submenu">
            <i class="fas fa-map-marker-alt"></i><span>Destinations</span>
            <i class="fas fa-chevron-down submenu-arrow"></i>
            <div class="admin-submenu">
                <a href="manage-destinations.php">Show Destinations</a>
                <a href="add-destination.php">Add Destination</a>
            </div>
        </div>

        <!-- Hotels -->
        <div class="admin-menu-item has-submenu">
            <i class="fas fa-hotel"></i><span>Hotels</span>
            <i class="fas fa-chevron-down submenu-arrow"></i>
            <div class="admin-submenu">
                <a href="manage-hotels.php">Show Hotels</a>
                <a href="add-hotel.php">Add Hotel</a>
            </div>
        </div>

        <!-- Rooms -->
        <div class="admin-menu-item has-submenu">
            <i class="fas fa-bed"></i><span>Rooms</span>
            <i class="fas fa-chevron-down submenu-arrow"></i>
            <div class="admin-submenu">
                <a href="manage-rooms.php">Show Rooms</a>
                <a href="add-room.php">Add Room</a>
            </div>
        </div>

        <!-- Bookings -->
        <div class="admin-menu-item has-submenu">
            <i class="fas fa-calendar-check"></i><span>Bookings</span>
            <i class="fas fa-chevron-down submenu-arrow"></i>
            <div class="admin-submenu">
                <a href="manage-bookings.php">Show Bookings</a>
                <a href="add-booking.php">Add Booking</a>
            </div>
        </div>

        <!-- Payments -->
        <div class="admin-menu-item has-submenu">
            <i class="fas fa-credit-card"></i><span>Payments</span>
            <i class="fas fa-chevron-down submenu-arrow"></i>
            <div class="admin-submenu">
                <a href="manage-payments.php">Show Payments</a>
                <a href="add-payment.php">Add Payment</a>
            </div>
        </div>

        <!-- Reviews -->
        <div class="admin-menu-item has-submenu">
            <i class="fas fa-star"></i><span>Reviews</span>
            <i class="fas fa-chevron-down submenu-arrow"></i>
            <div class="admin-submenu">
                <a href="manage-reviews.php">Show Reviews</a>
                <a href="add-review.php">Add Review</a>
            </div>
        </div>

        <!-- Gallery -->
        <div class="admin-menu-item has-submenu">
            <i class="fas fa-images"></i><span>Gallery</span>
            <i class="fas fa-chevron-down submenu-arrow"></i>
            <div class="admin-submenu">
                <a href="manage-gallery.php">Show Gallery</a>
                <a href="add-gallery.php">Add Image</a>
            </div>
        </div>

        <!-- Transport -->
        <div class="admin-menu-item has-submenu">
            <i class="fas fa-bus"></i><span>Transport</span>
            <i class="fas fa-chevron-down submenu-arrow"></i>
            <div class="admin-submenu">
                <a href="manage-transport.php">Show Transport</a>
                <a href="add-transport.php">Add Transport</a>
            </div>
        </div>

        <!-- Blogs -->
        <div class="admin-menu-item has-submenu">
            <i class="fas fa-blog"></i><span>Blogs</span>
            <i class="fas fa-chevron-down submenu-arrow"></i>
            <div class="admin-submenu">
                <a href="manage-blogs.php">Show Blogs</a>
                <a href="add-blog.php">Add Blog</a>
            </div>
        </div>

        <!-- Settings -->
        <a href="settings.php" class="admin-menu-item">
            <i class="fas fa-cog"></i><span>Settings</span>
        </a>

        <!-- Logout -->
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
            </div>
        </button>
        <h1>Dashboard</h1>
        <div class="admin-user-info">
            <div class="admin-user-avatar">A</div>
            <span>Welcome, Admin</span>
            <a href="logout.php" class="admin-logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <div class="admin-breadcrumb">
        <a href="dashboard.php">Dashboard</a>
        <span>Overview</span>
    </div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Admin header specific JavaScript - scoped to prevent conflicts
        const adminSidebar = document.getElementById('adminSidebar');
        const adminToggleSidebar = document.getElementById('adminToggleSidebar');
        const adminMobileToggle = document.getElementById('adminMobileToggle');
        const adminHamburger = document.getElementById('adminHamburger');
        const adminMobileOverlay = document.getElementById('adminMobileOverlay');
        
        // Submenu toggle functionality
        const submenuItems = document.querySelectorAll('.admin-menu-item.has-submenu');
        submenuItems.forEach(item => {
            // Only add click event if it's not a collapsed sidebar on desktop
            if (window.innerWidth > 1024 || !adminSidebar.classList.contains('collapsed')) {
                item.addEventListener('click', function(e) {
                    // Don't toggle if clicking on a link inside the submenu
                    if (e.target.tagName.toLowerCase() === 'a') return;
                    
                    // Close other open submenus
                    submenuItems.forEach(otherItem => {
                        if (otherItem !== item && otherItem.classList.contains('active')) {
                            otherItem.classList.remove('active');
                        }
                    });
                    
                    // Toggle current submenu
                    this.classList.toggle('active');
                });
            }
        });

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

        // Close mobile menu when clicking menu item (for links)
        const adminMenuLinks = document.querySelectorAll('.admin-menu-item[href]');
        adminMenuLinks.forEach(link => {
            link.addEventListener('click', function() {
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
                // Desktop view - close mobile menu
                adminSidebar.classList.remove('mobile-open');
                adminHamburger.classList.remove('active');
                adminMobileOverlay.classList.remove('active');
                document.body.style.overflow = '';
                
                // Restore collapsed state if it was saved
                const adminSidebarCollapsed = localStorage.getItem('adminSidebarCollapsed');
                if (adminSidebarCollapsed === 'true') {
                    adminSidebar.classList.add('collapsed');
                } else {
                    adminSidebar.classList.remove('collapsed');
                }
            } else {
                // Mobile view - remove collapsed class
                adminSidebar.classList.remove('collapsed');
            }
            
            // Close all submenus on resize
            submenuItems.forEach(item => {
                item.classList.remove('active');
            });
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

        // Add hover effect for submenus in collapsed sidebar on desktop
        if (window.innerWidth > 1024) {
            const collapsedSubmenuItems = document.querySelectorAll('.admin-sidebar.collapsed .admin-menu-item.has-submenu');
            collapsedSubmenuItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.classList.add('active');
                });
                
                item.addEventListener('mouseleave', function() {
                    this.classList.remove('active');
                });
            });
        }
    });
</script>
</body>
</html>