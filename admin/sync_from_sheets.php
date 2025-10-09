<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}
require '../config/koneksi.php';

$webAppUrl = 'MASUKKAN_URL_GOOGLE_APPS_SCRIPT';
$syncUrl = $webAppUrl . "?action=getAllData";
$jsonData = @file_get_contents($syncUrl);
$dataBySheet = json_decode($jsonData, true);

if (!is_array($dataBySheet)) {
    $_SESSION['sync_message'] = "Sinkronisasi gagal: Respon dari Google bukan data yang valid. (Pastikan Anda sudah Deploy Ulang Apps Script).";
    header('Location: dashboard.php?menu=riwayat');
    exit();
}

$new_records_count = 0;
$updated_records_count = 0;
$skipped_records_count = 0;

$stmt_insert = mysqli_prepare($conn, "INSERT INTO surat (nomor_surat, kategori, kepada, tanggal_pengajuan, perihal, nama_pengaju, konseptor, file_arsip, ttd_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt_update = mysqli_prepare($conn, "UPDATE surat SET file_arsip = ?, ttd_status = ?, konseptor = ?, kepada = ? WHERE nomor_surat = ?");

foreach ($dataBySheet as $kategori => $rows) {
    if (!is_array($rows)) continue;

    foreach ($rows as $row) {
        if (!is_array($row) || count($row) < 6 || empty(trim($row[1]))) continue; 
        $tanggal_str = trim($row[0]);
        $nomor_surat = trim($row[1]);
        $kepada = trim($row[2]);
        $perihal = trim($row[3]);
        $nama_pengaju = trim($row[4]);
        $konseptor = trim($row[5]);
        $status_arsip = trim($row[6]);
        $status_ttd = (isset($row[7])) ? trim($row[7]) : null;
        
        $stmt_check = mysqli_prepare($conn, "SELECT id FROM surat WHERE nomor_surat = ?");
        mysqli_stmt_bind_param($stmt_check, 's', $nomor_surat);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);

        $file_arsip_val = (strtolower($status_arsip) == 'sudah upload' || strtolower($status_arsip) == 'tersedia') ? 'diupload_dari_sheet' : null;
        $ttd_val = !empty($status_ttd) ? $status_ttd : null;

        if (mysqli_num_rows($result_check) == 0) {
            $datetime_obj = DateTime::createFromFormat('Y-m-d', $tanggal_str);
            if ($datetime_obj) {
                $mysql_datetime = $datetime_obj->format('Y-m-d H:i:s');
                mysqli_stmt_bind_param($stmt_insert, 'sssssssss', $nomor_surat, $kategori, $kepada, $mysql_datetime, $perihal, $nama_pengaju, $konseptor, $file_arsip_val, $ttd_val);
                mysqli_stmt_execute($stmt_insert);
                $new_records_count++;
            } else { $skipped_records_count++; }
        } else {
            mysqli_stmt_bind_param($stmt_update, 'sssss', $file_arsip_val, $ttd_val, $konseptor, $kepada, $nomor_surat);
            mysqli_stmt_execute($stmt_update);
            $updated_records_count++;
        }
    }
}

$message = "Sinkronisasi selesai! {$new_records_count} data baru ditambahkan, {$updated_records_count} data diperbarui.";
if ($skipped_records_count > 0) $message .= " {$skipped_records_count} baris dilewati (format tanggal salah).";

$_SESSION['sync_message'] = "Sinkronisasi selesai! {$new_records_count} data baru ditambahkan...";
header('Location: dashboard.php?menu=riwayat');
exit();
?>