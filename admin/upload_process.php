<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}
require '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_arsip']) && isset($_POST['surat_id'])) {
    
    $surat_id = (int)$_POST['surat_id'];
    $file = $_FILES['file_arsip'];

    if ($file['error'] === 0) {
        $fileName = basename($file['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx'];

        if (in_array($fileExt, $allowed)) {
            $newFileName = "surat_" . $surat_id . "_" . time() . "." . $fileExt;
            $destination = '../uploads/' . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // 1. Update database lokal
                $stmt = mysqli_prepare($conn, "UPDATE surat SET file_arsip = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'si', $newFileName, $surat_id);
                
                if(mysqli_stmt_execute($stmt)) {
                    // 2. AMBIL NOMOR SURAT LENGKAP UNTUK DIKIRIM KE GOOGLE
                    $res = mysqli_query($conn, "SELECT nomor_surat FROM surat WHERE id = $surat_id");
                    $surat_row = mysqli_fetch_assoc($res);
                    $nomor_surat_lengkap = $surat_row['nomor_surat'];

                    // 3. KIRIM PERMINTAAN UPDATE KE GOOGLE SHEETS
                    $webAppUrl = 'MASUKKAN_URL_GOOGLE_APPS_SCRIPT_YANG_SUDAH_DI_DEPLOY';
                    $updateParams = http_build_query([
                        'action'     => 'updateStatus',
                        'nomorSurat' => $nomor_surat_lengkap,
                        'status'     => 'Sudah Upload'
                    ]);
                    
                    // Gunakan file_get_contents untuk request GET sederhana
                    @file_get_contents($webAppUrl . '?' . $updateParams);

                    header('Location: dashboard.php?menu=riwayat&upload_sukses=1');
                    exit();
                }
            }
        } else {
            $_SESSION['upload_error'] = 'Tipe file tidak diizinkan.';
        }
    } else {
        $_SESSION['upload_error'] = 'Terjadi error saat mengunggah file.';
    }
    header('Location: upload_surat.php?id=' . $surat_id);
    exit();
}
?>