<?php
include "../includes/db.php";
session_start();

// Redirect if already logged in
if(isset($_SESSION['admin'])){
    header("Location: dashboard.php");
    exit;
}

if(isset($_POST['login'])){
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Using prepared statement for security
    $query = "SELECT * FROM admins WHERE username=? AND password=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) == 1){
        $admin = mysqli_fetch_assoc($result);
        $_SESSION['admin'] = $username;
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password";
    }

    mysqli_stmt_close($stmt);
}

// Logout
if(isset($_GET['logout'])){
    session_destroy();
    header("Location: admin-login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - GreenTour</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
    --primary-color: #2e7d32;
    --primary-dark: #1b5e20;
    --white: #ffffff;
    --text-color: #333;
    --text-light: #777;
    --shadow: 0 4px 15px rgba(0,0,0,0.1);
    --transition: all 0.3s ease;
}
* {margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,sans-serif;}
body {background:linear-gradient(135deg,#e0f2e1 0%,#c8e6c9 100%);display:flex;justify-content:center;align-items:center;min-height:100vh;padding:20px;}
.login-container {display:flex;width:900px;max-width:100%;background-color:var(--white);border-radius:15px;overflow:hidden;box-shadow:var(--shadow);}
.login-image {flex:1;background:linear-gradient(rgba(46,125,50,0.8),rgba(27,94,32,0.9)), url('https://images.unsplash.com/photo-1523531294919-4bcd7c65e216?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80');background-size:cover;background-position:center;display:flex;flex-direction:column;justify-content:center;align-items:center;padding:40px;color:var(--white);text-align:center;}
.login-image h1 {font-size:32px;margin-bottom:10px;display:flex;align-items:center;gap:10px;}
.login-image p {font-size:16px;opacity:0.9;line-height:1.6;}
.login-form {flex:1;padding:50px 40px;display:flex;flex-direction:column;justify-content:center;}
.logo {display:flex;align-items:center;gap:10px;margin-bottom:30px;color:var(--primary-color);}
.logo i {font-size:28px;}
.logo-text {font-size:24px;font-weight:600;}
.login-form h2 {color:var(--primary-color);margin-bottom:10px;font-size:28px;}
.login-form p {color:var(--text-light);margin-bottom:30px;}
.form-group {margin-bottom:20px;position:relative;}
.form-group label {display:block;margin-bottom:8px;color:var(--text-color);font-weight:500;}
.input-with-icon {position:relative;}
.input-with-icon i {position:absolute;left:15px;top:50%;transform:translateY(-50%);color:var(--primary-color);}
.form-control {width:100%;padding:12px 15px 12px 45px;border:1px solid #ddd;border-radius:8px;font-size:16px;transition:var(--transition);}
.form-control:focus {outline:none;border-color:var(--primary-color);box-shadow:0 0 0 2px rgba(46,125,50,0.2);}
.btn {background-color:var(--primary-color);color:var(--white);border:none;padding:14px;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer;transition:var(--transition);display:flex;align-items:center;justify-content:center;gap:8px;}
.btn:hover {background-color:var(--primary-dark);transform:translateY(-2px);}
.btn:active {transform:translateY(0);}
.error-message {background-color:#ffebee;color:#c62828;padding:12px;border-radius:8px;margin-bottom:20px;display:flex;align-items:center;gap:10px;}
.remember-forgot {display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;font-size:14px;}
.remember-me {display:flex;align-items:center;gap:5px;}
.forgot-password {color:var(--primary-color);text-decoration:none;}
.forgot-password:hover {text-decoration:underline;}
.footer-links {margin-top:30px;text-align:center;font-size:14px;color:var(--text-light);}
.footer-links a {color:var(--primary-color);text-decoration:none;}
.footer-links a:hover {text-decoration:underline;}
@media (max-width:768px){.login-container{flex-direction:column;width:100%;}.login-image{padding:30px 20px;}.login-image h1{font-size:24px;}.login-form{padding:30px 20px;}}
@media (max-width:480px){.remember-forgot{flex-direction:column;gap:10px;align-items:flex-start;}.logo-text{font-size:20px;}.login-form h2{font-size:24px;}}
.password-toggle {position:absolute;right:15px;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--text-light);}
</style>
</head>
<body>
<div class="login-container">
    <div class="login-image">
        <h1><i class="fas fa-leaf"></i> GreenTour Admin</h1>
        <p>Manage your tour website with our powerful admin panel.</p>
    </div>
    
    <div class="login-form">
        <div class="logo">
            <i class="fas fa-leaf"></i>
            <span class="logo-text">GreenTour</span>
        </div>
        <h2>Admin Login</h2>
        <p>Enter your credentials to access the admin panel</p>
        <?php if(isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-with-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required autofocus>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                    <span class="password-toggle"><i class="fas fa-eye"></i></span>
                </div>
            </div>
            
            <div class="remember-forgot">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
                <a href="#" class="forgot-password">Forgot Password?</a>
            </div>
            
            <button type="submit" name="login" class="btn">
                <i class="fas fa-sign-in-alt"></i> Login to Dashboard
            </button>
        </form>
        
        <div class="footer-links">
            <p>© <?php echo date('Y'); ?> GreenTour Admin Panel. All rights reserved.</p>
        </div>
    </div>
</div>

<script>
const passwordInput = document.getElementById('password');
const toggle = document.querySelector('.password-toggle i');
document.querySelector('.password-toggle').addEventListener('click', function(){
    if(passwordInput.type === 'password'){
        passwordInput.type = 'text';
        toggle.classList.replace('fa-eye','fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggle.classList.replace('fa-eye-slash','fa-eye');
    }
});
</script>
</body>
</html>
