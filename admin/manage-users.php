<?php
session_start();
include "../includes/db.php";

// Agar admin login nahi hai to redirect
if(!isset($_SESSION['admin'])){
    header("Location: admin-login.php");
    exit;
}

// User delete karna
if(isset($_GET['delete_id'])){
    $delete_id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM users WHERE user_id=$delete_id");
    header("Location: manage-users.php");
    exit;
}

// User add karna
if(isset($_POST['add_user'])){
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check_email = mysqli_query($conn, "SELECT user_id FROM users WHERE email='$email'");
    if(mysqli_num_rows($check_email) > 0){
        $error = "Email already exists!";
    } else {
        $query = "INSERT INTO users (full_name, email, phone, password, created_at) 
                  VALUES ('$full_name', '$email', '$phone', '$password', NOW())";
        if(mysqli_query($conn, $query)){
            $success = "User added successfully!";
            header("Location: manage-users.php?success=1");
            exit;
        } else {
            $error = "Error adding user: " . mysqli_error($conn);
        }
    }
}

// Search functionality
$search = "";
$searchCondition = "";
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $searchCondition = "WHERE full_name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%'";
}

// Users fetch
$users = mysqli_query($conn, "SELECT * FROM users $searchCondition ORDER BY user_id DESC");
$totalUsers = mysqli_num_rows($users);
// In your admin pages:
$pageTitle = "Manage Users";
$breadcrumb = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php'],
    ['title' => 'Users']
];
include "admin-header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users - GreenTour Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
      /* =========================
         Enhanced Variables & Base Styles
      ========================= */
      :root {
          --primary-color: #2E8B57;
          --primary-dark: #1F6B45;
          --primary-light: #4CAF50;
          --primary-lighter: #E8F5E9;
          --primary-gradient: linear-gradient(135deg, #2E8B57 0%, #4CAF50 100%);
          --secondary-color: #FF9800;
          --secondary-dark: #F57C00;
          --text-color: #2C3E50;
          --text-light: #7F8C8D;
          --text-lighter: #95A5A6;
          --white: #FFFFFF;
          --gray-light: #ECF0F1;
          --gray-medium: #BDC3C7;
          --gray-dark: #34495E;
          --shadow-light: 0 2px 10px rgba(0,0,0,0.08);
          --shadow-medium: 0 4px 20px rgba(0,0,0,0.12);
          --shadow-heavy: 0 8px 30px rgba(0,0,0,0.18);
          --border-radius: 16px;
          --border-radius-sm: 10px;
          --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
          --transition-fast: all 0.2s ease;
          --danger-color: #E74C3C;
          --danger-dark: #C0392B;
          --success-color: #27AE60;
          --warning-color: #F39C12;
      }

      * {
          box-sizing: border-box;
          margin: 0;
          padding: 0;
      }

      body {
          font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
          background: linear-gradient(135deg, #f7f9fb 0%, #eef2f5 100%);
          color: var(--text-color);
          line-height: 1.6;
          min-height: 100vh;
          padding: 20px;
          animation: fadeInBody 1s ease-out forwards;
      }

      @keyframes fadeInBody {
          from { opacity: 0; }
          to { opacity: 1; }
      }

      /* =========================
         Main Container
      ========================= */
      .admin-container {
          max-width: 1400px;
          margin: 0 auto;
          background: var(--white);
          border-radius: var(--border-radius);
          box-shadow: var(--shadow-heavy);
          overflow: hidden;
          animation: slideInUp 0.6s ease-out forwards;
      }

      @keyframes slideInUp {
          from {
              opacity: 0;
              transform: translateY(30px);
          }
          to {
              opacity: 1;
              transform: translateY(0);
          }
      }

      /* =========================
         Header Styles
      ========================= */
      .admin-header {
          background: var(--primary-gradient);
          color: var(--white);
          padding: 25px 40px;
          display: flex;
          justify-content: space-between;
          align-items: center;
          box-shadow: var(--shadow-medium);
      }

      .page-title {
          font-size: 2.8rem;
          font-weight: 700;
          margin: 0;
          text-shadow: 0 2px 4px rgba(0,0,0,0.1);
      }

      .header-actions {
          display: flex;
          gap: 15px;
      }

      /* =========================
         Enhanced Buttons
      ========================= */
      .btn {
          padding: 14px 28px;
          border: none;
          border-radius: var(--border-radius-sm);
          cursor: pointer;
          font-size: 16px;
          font-weight: 600;
          transition: var(--transition);
          display: inline-flex;
          align-items: center;
          gap: 10px;
          text-decoration: none;
          position: relative;
          overflow: hidden;
          transform: translateY(0);
          box-shadow: var(--shadow-light);
      }

      .btn::before {
          content: '';
          position: absolute;
          top: 50%;
          left: 50%;
          width: 0;
          height: 0;
          background: rgba(255, 255, 255, 0.2);
          border-radius: 50%;
          transition: all 0.6s ease;
          transform: translate(-50%, -50%);
      }

      .btn:hover::before {
          width: 300px;
          height: 300px;
      }

      .btn:active {
          transform: translateY(2px);
          box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      }

      .btn i {
          font-size: 18px;
          transition: var(--transition-fast);
      }

      .btn-primary {
          background: var(--primary-gradient);
          color: var(--white);
          animation: pulse 2s infinite;
      }

      .btn-primary:hover {
          box-shadow: var(--shadow-medium);
          transform: translateY(-3px);
          animation: none;
      }

      .btn-primary:hover i {
          transform: translateX(3px);
      }

      .btn-secondary {
          background: var(--white);
          color: var(--text-color);
          border: 2px solid rgba(255,255,255,0.3);
          font-weight: 500;
      }

      .btn-secondary:hover {
          background: rgba(255,255,255,0.2);
          color: var(--white);
          transform: translateY(-2px);
      }

      /* =========================
         Main Content Area
      ========================= */
      .admin-main-content {
          padding: 40px;
          min-height: 80vh;
      }

      /* =========================
         Enhanced Alerts
      ========================= */
      .alert {
          padding: 20px 25px;
          border-radius: var(--border-radius-sm);
          margin-bottom: 30px;
          display: flex;
          align-items: center;
          gap: 15px;
          font-weight: 500;
          animation: slideInDown 0.5s ease forwards, bounceIn 0.8s ease;
          border-left: 5px solid;
          box-shadow: var(--shadow-light);
          transform-origin: top;
          font-size: 16px;
      }

      .alert-success {
          background: rgba(39, 174, 96, 0.1);
          color: var(--success-color);
          border-left-color: var(--success-color);
      }

      .alert-error {
          background: rgba(231, 76, 60, 0.1);
          color: var(--danger-color);
          border-left-color: var(--danger-color);
      }

      .alert i {
          font-size: 24px;
          animation: pulse 2s infinite;
      }

      @keyframes slideInDown {
          from {
              opacity: 0;
              transform: translateY(-30px);
          }
          to {
              opacity: 1;
              transform: translateY(0);
          }
      }

      @keyframes bounceIn {
          0% {transform: scale(0.3); opacity: 0;}
          50% {transform: scale(1.05);}
          70% {transform: scale(0.9);}
          100% {transform: scale(1); opacity: 1;}
      }

      @keyframes pulse {
          0% { transform: scale(1); }
          50% { transform: scale(1.05); }
          100% { transform: scale(1); }
      }

      /* =========================
         Search Container
      ========================= */
      .search-container {
          background: var(--white);
          padding: 25px;
          border-radius: var(--border-radius);
          box-shadow: var(--shadow-light);
          margin-bottom: 30px;
          animation: fadeIn 0.8s ease-out 0.2s forwards;
          opacity: 0;
          transform: translateY(20px);
      }

      .search-form {
          display: flex;
          gap: 15px;
          flex-wrap: wrap;
          align-items: center;
      }

      .search-input {
          flex: 1;
          min-width: 300px;
          padding: 16px 20px;
          border-radius: var(--border-radius-sm);
          border: 2px solid var(--gray-light);
          font-size: 16px;
          transition: var(--transition);
          background: var(--gray-light);
          position: relative;
      }

      .search-input:focus {
          outline: none;
          border-color: var(--primary-color);
          background: var(--white);
          box-shadow: 0 0 0 4px rgba(46, 139, 87, 0.1);
          transform: translateY(-2px);
      }

      .search-btn {
          padding: 16px 28px;
          background: var(--primary-gradient);
          color: var(--white);
          border-radius: var(--border-radius-sm);
          border: none;
          cursor: pointer;
          transition: var(--transition);
          font-weight: 600;
          position: relative;
          overflow: hidden;
      }

      .search-btn:hover {
          transform: translateY(-2px);
          box-shadow: var(--shadow-medium);
      }

      /* =========================
         Enhanced Table Styles
      ========================= */
      .table-container {
          background: var(--white);
          border-radius: var(--border-radius);
          overflow: hidden;
          box-shadow: var(--shadow-light);
          margin-bottom: 40px;
          animation: fadeIn 0.8s ease-out forwards;
          opacity: 0;
      }

      .table-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 25px;
          border-bottom: 1px solid var(--gray-light);
          background: var(--primary-lighter);
      }

      .table-title {
          font-size: 1.5rem;
          font-weight: 600;
          color: var(--primary-dark);
      }

      .table-count {
          color: var(--text-light);
          font-weight: 500;
          background: var(--white);
          padding: 8px 16px;
          border-radius: 20px;
          box-shadow: var(--shadow-light);
      }

      .table-container table {
          width: 100%;
          border-collapse: collapse;
          background: var(--white);
      }

      .table-container th {
          background: var(--primary-lighter);
          color: var(--primary-color);
          font-weight: 600;
          text-transform: uppercase;
          letter-spacing: 0.5px;
          font-size: 14px;
          padding: 20px;
          text-align: left;
          border-bottom: 2px solid var(--primary-color);
          position: relative;
          overflow: hidden;
      }

      .table-container th::after {
          content: '';
          position: absolute;
          bottom: 0;
          left: 0;
          width: 100%;
          height: 2px;
          background: var(--primary-gradient);
          transform: scaleX(0);
          transition: var(--transition);
      }

      .table-container th:hover::after {
          transform: scaleX(1);
      }

      .table-container td {
          padding: 20px;
          text-align: left;
          border-bottom: 1px solid var(--gray-light);
          transition: var(--transition-fast);
          animation: fadeInRow 0.5s ease-out forwards;
          opacity: 0;
          transform: translateX(-10px);
      }

      .table-container tr:nth-child(1) td { animation-delay: 0.1s; }
      .table-container tr:nth-child(2) td { animation-delay: 0.2s; }
      .table-container tr:nth-child(3) td { animation-delay: 0.3s; }
      .table-container tr:nth-child(4) td { animation-delay: 0.4s; }
      .table-container tr:nth-child(5) td { animation-delay: 0.5s; }

      @keyframes fadeInRow {
          from {
              opacity: 0;
              transform: translateX(-10px);
          }
          to {
              opacity: 1;
              transform: translateX(0);
          }
      }

      .table-container tr:hover td {
          background: rgba(46, 139, 87, 0.03);
          transform: translateX(5px);
      }

      .table-container tr:last-child td {
          border-bottom: none;
      }

      .user-info-cell {
          display: flex;
          align-items: center;
          gap: 15px;
      }

      .user-avatar {
          width: 50px;
          height: 50px;
          border-radius: 50%;
          background: var(--primary-gradient);
          display: flex;
          align-items: center;
          justify-content: center;
          color: var(--white);
          font-size: 20px;
          font-weight: 600;
          transition: var(--transition);
      }

      .table-container tr:hover .user-avatar {
          transform: scale(1.1) rotate(5deg);
      }

      .user-details .user-name {
          font-weight: 600;
          color: var(--text-color);
          margin-bottom: 4px;
          transition: var(--transition);
      }

      .table-container tr:hover .user-name {
          color: var(--primary-color);
      }

      .user-details .user-email {
          font-size: 13px;
          color: var(--text-light);
      }

      .action-buttons {
          display: flex;
          gap: 10px;
          flex-wrap: wrap;
      }

      .btn-action {
          padding: 8px 16px;
          border-radius: var(--border-radius-sm);
          font-size: 13px;
          font-weight: 500;
          text-decoration: none;
          display: inline-flex;
          align-items: center;
          gap: 6px;
          transition: var(--transition);
          border: 1px solid transparent;
          position: relative;
          overflow: hidden;
          transform: scale(1);
      }

      .btn-action i {
          font-size: 14px;
      }

      .btn-view { 
          background: rgba(255, 152, 0, 0.1);
          color: var(--secondary-color);
          border-color: rgba(255, 152, 0, 0.3);
      }

      .btn-view:hover { 
          background: var(--secondary-color);
          color: white;
          transform: translateY(-2px) scale(1.05);
          box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
      }

      .btn-edit { 
          background: rgba(3, 169, 244, 0.1);
          color: #03A9F4;
          border-color: rgba(3, 169, 244, 0.3);
      }

      .btn-edit:hover { 
          background: #03A9F4;
          color: white;
          transform: translateY(-2px) scale(1.05);
          box-shadow: 0 4px 12px rgba(3, 169, 244, 0.3);
      }

      .btn-delete { 
          background: rgba(231, 76, 60, 0.1);
          color: var(--danger-color);
          border-color: rgba(231, 76, 60, 0.3);
      }

      .btn-delete:hover { 
          background: var(--danger-color);
          color: white;
          transform: translateY(-2px) scale(1.05);
          box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
      }

      /* =========================
         Enhanced Modal Styles
      ========================= */
      .modal {
          display: none;
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background-color: rgba(0,0,0,0.7);
          z-index: 2000;
          justify-content: center;
          align-items: center;
          padding: 20px;
          animation: fadeIn 0.3s ease forwards;
          backdrop-filter: blur(5px);
      }

      .modal-content {
          background: var(--white);
          padding: 40px;
          border-radius: var(--border-radius);
          width: 100%;
          max-width: 500px;
          max-height: 90vh;
          overflow-y: auto;
          box-shadow: var(--shadow-heavy);
          transform: translateY(-50px) scale(0.9);
          opacity: 0;
          animation: modalSlideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
          position: relative;
      }

      .modal-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 30px;
          padding-bottom: 20px;
          border-bottom: 1px solid var(--gray-light);
      }

      .modal-title {
          font-size: 1.8rem;
          font-weight: 700;
          color: var(--text-color);
          margin: 0;
      }

      .close-modal {
          font-size: 28px;
          background: none;
          border: none;
          cursor: pointer;
          color: var(--text-light);
          transition: var(--transition);
          width: 40px;
          height: 40px;
          display: flex;
          align-items: center;
          justify-content: center;
          border-radius: 50%;
      }

      .close-modal:hover {
          background: var(--gray-light);
          color: var(--danger-color);
          transform: rotate(90deg);
      }

      /* =========================
         Enhanced Form Styles
      ========================= */
      .form-group {
          margin-bottom: 25px;
          animation: fadeInUp 0.5s ease-out forwards;
          opacity: 0;
          transform: translateY(20px);
      }

      .form-group:nth-child(1) { animation-delay: 0.1s; }
      .form-group:nth-child(2) { animation-delay: 0.2s; }
      .form-group:nth-child(3) { animation-delay: 0.3s; }
      .form-group:nth-child(4) { animation-delay: 0.4s; }

      @keyframes fadeInUp {
          from {
              opacity: 0;
              transform: translateY(30px);
          }
          to {
              opacity: 1;
              transform: translateY(0);
          }
      }

      .form-label {
          display: block;
          margin-bottom: 10px;
          font-weight: 600;
          color: var(--text-color);
          font-size: 16px;
          transition: var(--transition);
      }

      .form-group:focus-within .form-label {
          color: var(--primary-color);
      }

      .form-control {
          width: 100%;
          padding: 16px 20px;
          border: 2px solid var(--gray-light);
          border-radius: var(--border-radius-sm);
          font-size: 16px;
          transition: var(--transition);
          background: var(--white);
          font-family: inherit;
      }

      .form-control:focus {
          outline: none;
          border-color: var(--primary-color);
          box-shadow: 0 0 0 4px rgba(46, 139, 87, 0.1);
          transform: translateY(-2px);
      }

      .btn-submit {
          background: var(--primary-gradient);
          color: var(--white);
          padding: 18px 40px;
          font-size: 18px;
          border-radius: var(--border-radius-sm);
          border: none;
          cursor: pointer;
          transition: var(--transition);
          display: flex;
          align-items: center;
          gap: 12px;
          font-weight: 600;
          box-shadow: var(--shadow-medium);
          width: 100%;
          justify-content: center;
      }

      .btn-submit:hover {
          transform: translateY(-3px);
          box-shadow: var(--shadow-heavy);
      }

      .btn-submit i {
          font-size: 20px;
          transition: var(--transition);
      }

      .btn-submit:hover i {
          transform: translateX(5px);
      }

      /* =========================
         Empty State
      ========================= */
      .empty-state {
          text-align: center;
          padding: 60px 20px;
          color: var(--text-light);
          animation: fadeInUp 0.8s ease-out forwards;
      }

      .empty-state i {
          font-size: 4rem;
          margin-bottom: 20px;
          opacity: 0.5;
          animation: bounce 2s infinite;
      }

      .empty-state h3 {
          font-size: 1.5rem;
          margin-bottom: 10px;
          color: var(--text-color);
      }

      .empty-state p {
          font-size: 1.1rem;
          margin-bottom: 30px;
      }

      @keyframes bounce {
          0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
          40% {transform: translateY(-10px);}
          60% {transform: translateY(-5px);}
      }

      @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
      }

      @keyframes modalSlideIn {
          to {
              opacity: 1;
              transform: translateY(0) scale(1);
          }
      }

      /* =========================
         Responsive Design
      ========================= */
      @media (max-width: 1024px) {
          .admin-main-content {
              padding: 30px;
          }
          
          .action-buttons {
              flex-direction: column;
          }
      }

      @media (max-width: 768px) {
          body {
              padding: 10px;
          }
          
          .admin-header {
              padding: 20px;
              flex-direction: column;
              gap: 20px;
              text-align: center;
          }
          
          .page-title {
              font-size: 2.2rem;
          }
          
          .admin-main-content {
              padding: 20px;
          }
          
          .search-input {
              min-width: 100%;
          }
          
          .table-header {
              flex-direction: column;
              gap: 15px;
              align-items: flex-start;
          }
          
          .table-container {
              overflow-x: auto;
          }
          
          .modal-content {
              padding: 25px;
              margin: 10px;
          }
      }

      @media (max-width: 480px) {
          .page-title {
              font-size: 1.8rem;
          }
          
          .modal-content {
              padding: 20px;
          }
          
          .user-info-cell {
              flex-direction: column;
              align-items: flex-start;
              gap: 10px;
          }
      }
  </style>
</head>
<body>
<div class="admin-container">
    <!-- Header -->
    <div class="admin-header">
        <h1 class="page-title">Manage Users</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="openModal()">
                <i class="fas fa-user-plus"></i> Add New User
            </button>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="admin-main-content">
        <!-- Success/Error Messages -->
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> User added successfully!
            </div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Search Container -->
        <div class="search-container">
            <form class="search-form" method="get">
                <input type="text" name="search" class="search-input" placeholder="Search users by name, email, or phone..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Search
                </button>
                <?php if(!empty($search)): ?>
                    <a href="manage-users.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Users Table -->
        <div class="table-container">
            <div class="table-header">
                <div class="table-title">All Users</div>
                <div class="table-count">Total: <?php echo $totalUsers; ?> users</div>
            </div>
            
            <?php if(mysqli_num_rows($users) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Contact Info</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($u = mysqli_fetch_assoc($users)): ?>
                            <tr>
                                <td>
                                    <div class="user-info-cell">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($u['full_name'], 0, 1)); ?>
                                        </div>
                                        <div class="user-details">
                                            <div class="user-name"><?php echo htmlspecialchars($u['full_name']); ?></div>
                                            <div class="user-email">ID: <?php echo $u['user_id']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="margin-bottom: 8px;">
                                        <i class="fas fa-envelope" style="color: var(--primary-color); margin-right: 8px;"></i>
                                        <?php echo htmlspecialchars($u['email']); ?>
                                    </div>
                                    <div>
                                        <i class="fas fa-phone" style="color: var(--primary-color); margin-right: 8px;"></i>
                                        <?php echo htmlspecialchars($u['phone']); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php echo date("M d, Y", strtotime($u['created_at'])); ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="view-user.php?id=<?php echo $u['user_id']; ?>" class="btn-action btn-view">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="edit-user.php?id=<?php echo $u['user_id']; ?>" class="btn-action btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="manage-users.php?delete_id=<?php echo $u['user_id']; ?>" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');" class="btn-action btn-delete">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users fa-3x"></i>
                    <h3>No Users Found</h3>
                    <p><?php echo !empty($search) ? 'Try adjusting your search terms' : 'Get started by adding your first user'; ?></p>
                    <?php if(empty($search)): ?>
                        <button class="btn btn-primary" onclick="openModal()" style="margin-top: 15px;">
                            <i class="fas fa-user-plus"></i> Add New User
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal" id="userModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New User</h3>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        <form method="post">
            <div class="form-group">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" id="full_name" name="full_name" class="form-control" placeholder="Enter full name" required>
            </div>
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter email address" required>
            </div>
            <div class="form-group">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="text" id="phone" name="phone" class="form-control" placeholder="Enter phone number" required>
            </div>
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>
            <button type="submit" name="add_user" class="btn-submit">
                <i class="fas fa-user-plus"></i> Add User
            </button>
        </form>
    </div>
</div>

<script>
    // Modal Functions
    function openModal() {
        document.getElementById('userModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('userModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('userModal');
        if (event.target === modal) {
            closeModal();
        }
    }

    // Animation on scroll for table rows
    document.addEventListener('DOMContentLoaded', function() {
        const tableRows = document.querySelectorAll('.table-container tbody tr');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                }
            });
        }, { threshold: 0.1 });
        
        tableRows.forEach(row => {
            observer.observe(row);
        });
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        if (password.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long');
            document.getElementById('password').focus();
        }
    });
</script>
</body>
</html>