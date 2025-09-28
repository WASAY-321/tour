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
  <title>Manage Users</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
      /* -------- CSS (detail) -------- */
      :root {
          --primary: #2e7d32;
          --primary-dark: #1b5e20;
          --light: #f8f9fa;
          --white: #fff;
          --shadow: 0 4px 12px rgba(0,0,0,0.1);
      }
      body {
          margin:0; font-family:Arial, sans-serif;
          background:#f4f7f4; color:#333;
      }
      .container {
          max-width:1200px; margin:20px auto; padding:20px;
      }
      .page-header {
          display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;
      }
      .page-header h2 { color:var(--primary); }
      .btn {
          padding:8px 15px; border:none; border-radius:5px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; text-decoration:none;
      }
      .btn-primary { background:var(--primary); color:#fff; }
      .btn-primary:hover { background:var(--primary-dark); }
      .btn-secondary { background:#6c757d; color:#fff; }
      .btn-secondary:hover { background:#545b62; }
      .alert {
          padding:10px 15px; margin-bottom:15px; border-radius:5px;
      }
      .alert-success { background:#d4edda; color:#155724; }
      .alert-error { background:#f8d7da; color:#721c24; }
      .search-container {
          background:var(--white); padding:15px; border-radius:8px; box-shadow:var(--shadow); margin-bottom:20px;
      }
      .search-form { display:flex; gap:10px; }
      .search-input {
          flex:1; padding:8px 12px; border:1px solid #ccc; border-radius:5px;
      }
      .search-btn {
          background:var(--primary); color:#fff; border:none; padding:8px 15px; border-radius:5px; cursor:pointer;
      }
      .search-btn:hover { background:var(--primary-dark); }
      .table-container {
          background:var(--white); border-radius:8px; box-shadow:var(--shadow); overflow:hidden;
      }
      table {
          width:100%; border-collapse:collapse;
      }
      thead { background:var(--primary); color:#fff; }
      th, td { padding:12px; text-align:left; }
      tbody tr:nth-child(even) { background:#f9f9f9; }
      tbody tr:hover { background:#f1f1f1; }
      .action-buttons { display:flex; gap:8px; }
      .btn-action {
          padding:5px 10px; border-radius:4px; font-size:13px; text-decoration:none; display:flex; align-items:center; gap:4px;
      }
      .btn-view { background:#7b1fa2; color:#fff; }
      .btn-edit { background:#0288d1; color:#fff; }
      .btn-delete { background:#c62828; color:#fff; }
      .modal {
          display:none; position:fixed; top:0; left:0; width:100%; height:100%;
          background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000;
      }
      .modal-content {
          background:#fff; padding:20px; border-radius:8px; width:400px; max-width:95%;
      }
      .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; }
      .form-group { margin-bottom:12px; }
      .form-control {
          width:100%; padding:8px 12px; border:1px solid #ccc; border-radius:5px;
      }
  </style>
</head>
<body>
<div class="container">

    <div class="page-header">
        <h2>Manage Users</h2>
        <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-user-plus"></i> Add User</button>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">User added successfully!</div>
    <?php endif; ?>
    <?php if(isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Search -->
    <div class="search-container">
        <form class="search-form" method="get">
            <input type="text" name="search" class="search-input" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
            <button class="search-btn"><i class="fas fa-search"></i></button>
            <?php if(!empty($search)): ?>
                <a href="manage-users.php" class="btn btn-secondary"><i class="fas fa-times"></i> Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Users Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Name</th><th>Email</th><th>Phone</th><th>Registered</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($users) > 0): ?>
                    <?php while($u = mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo htmlspecialchars($u['phone']); ?></td>
                            <td><?php echo date("M d, Y", strtotime($u['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="view-user.php?id=<?php echo $u['user_id']; ?>" class="btn-action btn-view"><i class="fas fa-eye"></i> View</a>
                                    <a href="edit-user.php?id=<?php echo $u['user_id']; ?>" class="btn-action btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="manage-users.php?delete_id=<?php echo $u['user_id']; ?>" onclick="return confirm('Delete this user?');" class="btn-action btn-delete"><i class="fas fa-trash"></i> Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No users found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- Modal -->
<div class="modal" id="userModal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Add User</h3>
      <button onclick="closeModal()">×</button>
    </div>
    <form method="post">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="full_name" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Phone</label>
        <input type="text" name="phone" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" name="add_user" class="btn btn-primary" style="width:100%;">Add User</button>
    </form>
  </div>
</div>

<script>
function openModal(){ document.getElementById("userModal").style.display="flex"; }
function closeModal(){ document.getElementById("userModal").style.display="none"; }
window.onclick=function(e){ if(e.target==document.getElementById("userModal")) closeModal(); }
</script>
</body>
</html>
