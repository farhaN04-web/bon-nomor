<?php
session_start();
require '../config/koneksi.php';

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];

$query = "SELECT * FROM admin WHERE username = '$username'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $admin = mysqli_fetch_assoc($result);
    if (password_verify($password, $admin['password'])) {
        $lifetime = 86400;
        session_set_cookie_params($lifetime);
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: dashboard.php');
        exit();
    }
}

$_SESSION['login_error'] = 'Username atau password salah.';
header('Location: index.php');
exit();
?>