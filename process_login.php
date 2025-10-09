<?php
session_start();
require 'config/koneksi.php';

$username = $_POST['username'];
$password = $_POST['password'];
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['user_konseptor'] = $user['konseptor'];
        header('Location: dashboard.php');
        exit();
    }
}
$_SESSION['error_msg'] = "Username atau password salah.";
header('Location: auth.php');
exit();
?>