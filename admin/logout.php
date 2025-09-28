<?php
session_start();

// session destroy
session_destroy();

// browser cache prevent
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// redirect to login page
header("Location: admin-login.php");
exit;
