<?php
session_start();
require 'config/koneksi.php';

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];

$query = "SELECT * FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    if (password_verify($password, $user['password'])) {
        // Login berhasil, simpan info user ke session
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