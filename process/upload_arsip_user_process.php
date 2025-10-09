<?php
session_start();
require_once '../config/koneksi.php';

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
                $stmt = mysqli_prepare($conn, "UPDATE surat SET file_arsip = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'si', $newFileName, $surat_id);
                
                if(mysqli_stmt_execute($stmt)) {
                    $res = mysqli_query($conn, "SELECT nomor_surat FROM surat WHERE id = $surat_id");
                    $surat_row = mysqli_fetch_assoc($res);
                    $nomor_surat_lengkap = $surat_row['nomor_surat'];
                    $webAppUrl = 'MASUKKAN_URL_GOOGLE_APPS_SCRIPT';
                    $updateParams = http_build_query([
                        'action'     => 'updateStatus',
                        'nomorSurat' => $nomor_surat_lengkap,
                        'status'     => 'Sudah Upload'
                    ]);
                    @file_get_contents($webAppUrl . '?' . $updateParams);
                }
                header('Location: ../dashboard.php?page=riwayat');
                exit();
            }
        } else {
            $_SESSION['upload_user_msg'] = 'Upload file gagal: Tipe file tidak diizinkan.';
        }
    } else {
        $_SESSION['upload_user_msg'] = 'Upload file gagal: Terjadi error.';
    }
    header('Location: ../upload_arsip_user.php?id=' . $surat_id);
    exit();
}
?>