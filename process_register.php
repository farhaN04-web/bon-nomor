<?php
session_start();
require 'config/koneksi.php';

$username = $_POST['username'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
if ($password !== $confirm_password) {
    $_SESSION['error_msg'] = "Password tidak cocok. Silakan coba lagi.";
    header('Location: auth.php?form=register');
    exit();
}
$stmt_check = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt_check, "s", $username);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) > 0) {
    $_SESSION['error_msg'] = "Username sudah digunakan. Silakan pilih yang lain.";
    header('Location: auth.php?form=register');
    exit();
}
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$stmt_insert = mysqli_prepare($conn, "INSERT INTO users (username, password) VALUES (?, ?)");
mysqli_stmt_bind_param($stmt_insert, "ss", $username, $hashed_password);

if (mysqli_stmt_execute($stmt_insert)) {
    $_SESSION['success_msg'] = "Registrasi berhasil! Silakan login.";
    header('Location: auth.php');
} else {
    $_SESSION['error_msg'] = "Registrasi gagal. Silakan coba lagi.";
    header('Location: auth.php?form=register');
}

exit();
?>