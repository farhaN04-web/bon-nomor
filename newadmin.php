<?php
require 'config/koneksi.php';

$username = 'admin';
$password = 'admin123'; 

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$query = "INSERT INTO admin (username, password) VALUES ('$username', '$hashed_password')";

if (mysqli_query($conn, $query)) {
    echo "Admin berhasil dibuat dengan username: $username dan password: $password";
} else {
    echo "Gagal membuat admin: " . mysqli_error($conn);
}
?>