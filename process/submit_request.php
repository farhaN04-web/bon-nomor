<?php
session_start();
// Keamanan: pastikan pengguna sudah login
if (!isset($_SESSION['user_logged_in']) || !isset($_SESSION['user_id'])) {
    die('Akses ditolak. Silakan login terlebih dahulu.');
}

require_once '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // AMBIL ID PENGGUNA DARI SESSION
    $user_id = $_SESSION['user_id'];

    // Ambil data form
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $kepada = mysqli_real_escape_string($conn, $_POST['kepada']);
    $perihal = mysqli_real_escape_string($conn, $_POST['perihal']);
    $nama_pengaju = mysqli_real_escape_string($conn, $_POST['nama_pengaju']);
    $konseptor = mysqli_real_escape_string($conn, $_POST['konseptor']);
    
    // Logika Penomoran Lengkap (per kategori)
    $year = date('Y');
    $month = date('n');
    $romanMonth = getRomanMonth($month);
    $stmt_nomor = mysqli_prepare($conn, "SELECT nomor_awal FROM kategori_pengaturan WHERE nama_kategori = ?");
    mysqli_stmt_bind_param($stmt_nomor, 's', $kategori);
    mysqli_stmt_execute($stmt_nomor);
    $result_nomor = mysqli_stmt_get_result($stmt_nomor);
    $nomor_awal_row = mysqli_fetch_assoc($result_nomor);
    $nomor_awal = $nomor_awal_row ? (int)$nomor_awal_row['nomor_awal'] : 1;
    $stmt_count = mysqli_prepare($conn, "SELECT COUNT(id) as count FROM surat WHERE kategori = ? AND YEAR(tanggal_pengajuan) = ?");
    mysqli_stmt_bind_param($stmt_count, 'ss', $kategori, $year);
    mysqli_stmt_execute($stmt_count);
    $result_count = mysqli_stmt_get_result($stmt_count);
    $row_count = mysqli_fetch_assoc($result_count);
    $next_number = $row_count['count'] + $nomor_awal;

    $prefix = '';
    switch ($kategori) {
        case 'Surat Perintah': $prefix = 'SPRIN'; break;
        case 'Biasa': $prefix = 'B'; break;
        case 'Rahasia': $prefix = 'R'; break;
        case 'Surat Telegram': $prefix = 'ST'; break;
        case 'Surat Telegram Rahasia': $prefix = 'STR'; break;
        case 'Nota Dinas': $prefix = 'B/ND'; break;
        case 'Undangan': $prefix = 'B/Und'; break;
        case 'Keputusan': $prefix = 'KEP'; break;
        case 'Surat Pengantar Biasa': $prefix = 'B/Speng'; break;
        case 'Surat Pengantar Rahasia': $prefix = 'R/Speng'; break;
        case 'Berita Acara': $prefix = 'BA'; break;
        case 'Surat Tugas': $prefix = 'Springas'; break;
        default: $prefix = 'B';
    }
    $nomor_surat_lengkap = sprintf("%s/%d/%s/%s", $prefix, $next_number, $romanMonth, $year);

    // Logika Upload File Opsional
    $arsipStatus = "belum";
    $namaFileUntukDB = null;
    if (isset($_FILES['file_arsip']) && $_FILES['file_arsip']['error'] == 0) {
        $file = $_FILES['file_arsip'];
        $fileName = basename($file['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx'];
        if (in_array($fileExt, $allowed)) {
            $namaFileUntukDB = "surat_" . $next_number . "_" . time() . "." . $fileExt;
            $destination = '../uploads/' . $namaFileUntukDB;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $arsipStatus = "Sudah Upload";
            }
        }
    }

    // SIMPAN KE DATABASE LOKAL DENGAN USER_ID
    $stmt_insert = mysqli_prepare($conn, "INSERT INTO surat (user_id, nomor_surat, kategori, kepada, perihal, nama_pengaju, konseptor, file_arsip) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt_insert, 'isssssss', $user_id, $nomor_surat_lengkap, $kategori, $kepada, $perihal, $nama_pengaju, $konseptor, $namaFileUntukDB);
    mysqli_stmt_execute($stmt_insert);
    
    // 5. Kirim data ke Google Sheets
    $webAppUrl = 'MASUKKAN_URL_GOOGLE_SCRIPT_APPS'; // Pastikan URL ini benar
    $postData = [
        'tanggal'      => date('d/m/Y'),
        'nomor_surat'  => $nomor_surat_lengkap,
        'kategori'     => $_POST['kategori'],
        'kepada'       => $_POST['kepada'],
        'perihal'      => $_POST['perihal'],
        'nama_pengaju' => $_POST['nama_pengaju'],
        'konseptor'    => $_POST['konseptor'],
        'arsip'        => $arsipStatus,
    ];
    $ch = curl_init($webAppUrl);
    curl_setopt_array($ch, [ CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_POSTFIELDS => http_build_query($postData) ]);
    curl_exec($ch);
    curl_close($ch);
    
    // Set session untuk ditampilkan di halaman form
    $_SESSION['bon_nomor_result'] = [
        'nomor_surat_display' => $next_number,
        'kategori' => $_POST['kategori'],
        'kepada' => $_POST['kepada'],
        'perihal' => $_POST['perihal'],
        'tanggal' => date('d F Y'),
        'nama_pengaju' => $_POST['nama_pengaju'],
        'konseptor' => $_POST['konseptor'],
        'pukul' => date('H:i')
    ];
}

header('Location: ../dashboard.php?page=form');
exit();
?>