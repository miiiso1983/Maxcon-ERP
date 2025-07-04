<?php
require_once 'Session.php';
require_once 'User.php';

Session::init();

// Check if user is already logged in
if (User::isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// Redirect to login page
header('Location: login.php');
exit;
?>
