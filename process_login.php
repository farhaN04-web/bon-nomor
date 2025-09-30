<?php
session_start();
require 'config/koneksi.php'; // Pastikan path ini benar

// Ambil data dari form login
$username = $_POST['username'];
$password = $_POST['password'];

// Gunakan prepared statement untuk keamanan
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Cek apakah user ditemukan
if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    
    // Verifikasi password yang diinput dengan hash di database
    if (password_verify($password, $user['password'])) {
        // Jika password cocok, login berhasil
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['user_konseptor'] = $user['konseptor']; // Menggunakan 'konseptor'
        
        // Arahkan ke halaman dashboard utama
        header('Location: dashboard.php');
        exit();
    }
}

// Jika username tidak ditemukan atau password salah
$_SESSION['error_msg'] = "Username atau password salah.";
header('Location: auth.php'); // Arahkan kembali ke halaman login/register
exit();
?>