<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'config/koneksi.php';

// Keamanan dasar & ambil data surat
if (!isset($_GET['id'])) die('Error: ID Surat tidak ditemukan.');
$id = (int)$_GET['id'];
$query = "SELECT nomor_surat, perihal FROM surat WHERE id = $id AND file_arsip IS NULL";
$result = mysqli_query($conn, $query);
$surat = mysqli_fetch_assoc($result);
if (!$surat) die('Error: Surat sudah memiliki arsip atau tidak ditemukan.');

require 'includes/header.php';
?>

<div class="upload-container">
    <h2>Upload Arsip untuk Surat</h2>
    
    <div style="background: #f0f0f0; border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: left;">
        <strong>Nomor Surat:</strong> <?= htmlspecialchars($surat['nomor_surat']) ?><br>
        <strong>Perihal:</strong> <?= htmlspecialchars($surat['perihal']) ?>
    </div>

    <?php if (isset($_SESSION['upload_user_msg'])): ?>
        <p class="message <?= strpos($_SESSION['upload_user_msg'], 'gagal') !== false ? 'error' : 'success' ?>">
            <?= $_SESSION['upload_user_msg'] ?>
        </p>
        <?php unset($_SESSION['upload_user_msg']); ?>
    <?php endif; ?>

    <form action="process/upload_arsip_user_process.php" method="POST" enctype="multipart/form-data" class="upload-form">
        <input type="hidden" name="surat_id" value="<?= $id ?>">
        
        <label for="file_arsip" class="upload-box" style="padding: 60px 20px;">
            <input type="file" id="file_arsip" name="file_arsip" required>
            <span class="upload-icon">&#8679;</span>
            <span id="file-name">Pilih file (PDF, DOC, DOCX)</span>
        </label>
        
        <button type="submit" class="btn-primary">Upload File Arsip</button>
    </form>
</div>

<?php
require 'includes/footer.php';
?>