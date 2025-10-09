<?php
require 'config/koneksi.php';

$username_baru = 'useradmin';
$password_baru = 'passwordadmin'; 
$hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
$stmt = mysqli_prepare($conn, "INSERT INTO admin (username, password) VALUES (?, ?)");
mysqli_stmt_bind_param($stmt, "ss", $username_baru, $hashed_password);

if (mysqli_stmt_execute($stmt)) {
    echo "Admin baru dengan username '<strong>" . htmlspecialchars($username_baru) . "</strong>' berhasil dibuat.";
} else {
    echo "Gagal membuat admin baru. Kemungkinan username sudah ada.";
}
?>