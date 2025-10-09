<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'db_arsip';
date_default_timezone_set('Asia/Jakarta');

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

function getRomanMonth($month) {
    $romans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    return $romans[$month - 1];
}
?>