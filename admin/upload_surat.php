<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}
require '../config/koneksi.php';

if (!isset($_GET['id'])) {
    die('ID surat tidak ditemukan.');
}

$id = (int)$_GET['id'];
$query = "SELECT nomor_surat, perihal FROM surat WHERE id = $id";
$result = mysqli_query($conn, $query);
$surat = mysqli_fetch_assoc($result);

if (!$surat) {
    die('Data surat tidak ditemukan.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Upload Arsip Surat</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background-color: #f4f4f4; }
        .upload-page { max-width: 600px; margin: 40px auto; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .surat-info { background: #f9f9f9; border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="upload-page">
        <h2>Upload Arsip untuk Surat</h2>
        <div class="surat-info">
            <strong>Nomor Surat:</strong> <?= htmlspecialchars($surat['nomor_surat']) ?><br>
            <strong>Perihal:</strong> <?= htmlspecialchars($surat['perihal']) ?>
        </div>

        <?php if(isset($_SESSION['upload_error'])): ?>
            <p style="color: red;"><?= $_SESSION['upload_error']; ?></p>
            <?php unset($_SESSION['upload_error']); ?>
        <?php endif; ?>

        <form action="upload_process.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="surat_id" value="<?= $id ?>">
            <div class="form-group">
                <label for="file_arsip">Pilih File (PDF, DOC, DOCX)</label>
                <input type="file" id="file_arsip" name="file_arsip" required class="form-control">
            </div>
            <button type="submit" class="btn-yellow">Upload dan Simpan</button>
            <a href="dashboard.php?menu=riwayat" style="margin-left: 10px;">Batal</a>
        </form>
    </div>
</body>
</html>