<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: auth.php'); 
    exit();
}

require_once 'config/koneksi.php';

$pengaturan_query = mysqli_query($conn, "SELECT * FROM pengaturan");
$pengaturan = [];
while($row = mysqli_fetch_assoc($pengaturan_query)) {
    $pengaturan[$row['setting_nama']] = $row['setting_nilai'];
}

$waktu_sekarang_ts = time();
$waktu_buka_ts = strtotime($pengaturan['waktu_buka']);
$waktu_tutup_ts = strtotime($pengaturan['waktu_tutup']);
$akses_diizinkan = false;

if ($waktu_buka_ts <= $waktu_tutup_ts) {
    if ($waktu_sekarang_ts >= $waktu_buka_ts && $waktu_sekarang_ts <= $waktu_tutup_ts) {
        $akses_diizinkan = true;
    }
} else {
    if ($waktu_sekarang_ts >= $waktu_buka_ts || $waktu_sekarang_ts <= $waktu_tutup_ts) {
        $akses_diizinkan = true;
    }
}

if (!$akses_diizinkan) {
    echo "<div style='text-align: center; padding: 50px; font-family: sans-serif; font-size: 1.2em;'>";
    echo "<h2>Akses Ditutup</h2>";
    echo "<p>Sistem ini hanya dapat diakses antara jam <strong>" . htmlspecialchars($pengaturan['waktu_buka']) . "</strong> sampai <strong>" . htmlspecialchars($pengaturan['waktu_tutup']) . "</strong>.</p>";
    echo "</div>";
    exit();
}

require_once 'includes/header.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'form';

echo '<div class="content-wrapper">';

switch ($page) {

case 'riwayat':
    $user_id = $_SESSION['user_id'];
    ?>
    <div class="riwayat-container">
        <form class="search-bar" method="GET">
            <input type="hidden" name="page" value="riwayat">
            <input type="text" name="search" placeholder="Cari berdasarkan nomor, kategori, tanggal, perihal..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button type="submit">Cari <span class="search-icon">&#128269;</span></button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th><th>Nomor</th><th>Kategori</th><th>Perihal</th>
                    <th>Nama Pengaju</th><th>Konseptor</th><th>Surat Arsip</th><th>Lihat Surat</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // --- QUERY PENCARIAN DENGAN FILTER USER_ID ---
                $search = isset($_GET['search']) ? trim($_GET['search']) : '';
                
                // Query dasar sekarang selalu memfilter berdasarkan user_id
                $query = "SELECT * FROM surat WHERE user_id = ?";
                
                $params = [$user_id];
                $types = 'i';

                if (!empty($search)) {
                    $search_term_like = "%" . $search . "%";
                    
                    $text_fields_query = "(nomor_surat LIKE ? OR kategori LIKE ? OR perihal LIKE ? OR nama_pengaju LIKE ? OR konseptor LIKE ?)";
                    for($i=0; $i<5; $i++) {
                        $params[] = $search_term_like;
                        $types .= 's';
                    }

                    $date_obj = DateTime::createFromFormat('d/m/Y', $search);
                    if (!$date_obj) { $date_obj = DateTime::createFromFormat('Y-m-d', $search); }

                    if ($date_obj) {
                        $search_date = $date_obj->format('Y-m-d');
                        $query .= " AND ($text_fields_query OR DATE(tanggal_pengajuan) = ?)";
                        $params[] = $search_date;
                        $types .= 's';
                    } else {
                        $query .= " AND $text_fields_query";
                    }
                }
                
                $query .= " ORDER BY tanggal_pengajuan DESC";
                
                $stmt = mysqli_prepare($conn, $query);
                if (!empty($params)) {
                    mysqli_stmt_bind_param($stmt, $types, ...$params);
                }
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if(mysqli_num_rows($result) > 0):
                    while($row = mysqli_fetch_assoc($result)):
                ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($row['tanggal_pengajuan'])) ?></td>
                            <td><?= htmlspecialchars($row['nomor_surat']) ?></td>
                            <td><?= htmlspecialchars($row['kategori']) ?></td>
                            <td><?= htmlspecialchars($row['perihal']) ?></td>
                            <td><?= htmlspecialchars($row['nama_pengaju']) ?></td>
                            <td><?= htmlspecialchars($row['konseptor']) ?></td>
                            <td>
                                <?php if($row['file_arsip']): ?>
                                    <span class="status green">Sudah Upload</span>
                                <?php else: ?>
                                    <a href="upload_arsip_user.php?id=<?= $row['id'] ?>" class="btn-upload-small">Upload</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['file_arsip']): ?>
                                    <a href="uploads/<?= htmlspecialchars($row['file_arsip']) ?>" target="_blank" class="status green">Surat Tersedia</a>
                                <?php else: ?>
                                    <span class="status red">Surat Tidak Tersedia</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                <?php 
                    endwhile;
                else: ?>
                    <tr><td colspan="8" style="text-align: center;">Anda belum memiliki riwayat.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
    break;


case 'form':
default:
    ?>
    <div class="form-page-container">
        <div class="form-column">
            <div class="form-container"> 
                <form id="form-pengajuan" action="process/submit_request.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="kategori">Kategori Surat</label>
                        <select id="kategori" name="kategori" required>
                            <option value="Surat Perintah">Surat Perintah</option>
                            <option value="Biasa">Biasa</option>
                            <option value="Rahasia">Rahasia</option>
                            <option value="Surat Telegram">Surat Telegram</option>
                            <option value="Surat Telegram Rahasia">Surat Telegram Rahasia</option>
                            <option value="Nota Dinas">Nota Dinas</option>
                            <option value="Undangan">Undangan</option>
                            <option value="Keputusan">Keputusan</option>
                            <option value="Surat Pengantar Biasa">Surat Pengantar Biasa</option>
                            <option value="Surat Pengantar Rahasia">Surat Pengantar Rahasia</option>
                            <option value="Berita Acara">Berita Acara</option>
                            <option value="Surat Tugas">Surat Tugas</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="kepada">Kepada</label>
                        <textarea id="kepada" name="kepada" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="perihal">Perihal</label>
                        <textarea id="perihal" name="perihal" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="nama_pengaju">Nama Pengaju</label>
                        <input type="text" id="nama_pengaju" name="nama_pengaju" required>
                    </div>
                    <div class="form-group">
                        <label for="konseptor">Konseptor</label>
                        <select id="konseptor" name="konseptor" required>
                            <option value="OPS">OPS</option>
                            <option value="SDM">SDM</option>
                            <option value="REN">REN</option>
                            <option value="LOGISTIK">LOGISTIK</option>
                            <option value="RESKRIM">RESKRIM</option>
                            <option value="INTELKAM">INTELKAM</option>
                            <option value="LANTAS">LANTAS</option>
                            <option value="BINMAS">BINMAS</option>
                            <option value="NARKOBA">NARKOBA</option>
                            <option value="SAMAPTA">SAMAPTA</option>
                            <option value="TAHTI">TAHTI</option>
                            <option value="SPKT">SPKT</option>
                            <option value="PROPAM">PROPAM</option>
                            <option value="WAS">WAS</option>
                            <option value="TIPOL">TIPOL</option>
                            <option value="SIUM">SIUM</option>
                            <option value="KEUANGAN">KEUANGAN</option>
                            <option value="HUKUM">HUKUM</option>
                            <option value="HUMAS">HUMAS</option>
                            <option value="DOKKES">DOKKES</option>
                            <option value="PPK">PPK</option>
                            <option value="SPRI">SPRI</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="file_arsip">Upload Arsip (Opsional)</label>
                        <input type="file" id="file_arsip" name="file_arsip">
                    </div>
                    <button type="submit" class="btn-yellow">Bon Nomor</button>
                </form>
            </div>
        </div>

        <div class="result-column">
            <?php 
            if (isset($_SESSION['bon_nomor_result'])): 
                $result = $_SESSION['bon_nomor_result'];
                unset($_SESSION['bon_nomor_result']);
            ?>
            <div class="result-box">
                <h3>BON NOMOR SURAT</h3>
                <table>
                    <tr><td><strong>Nomor Surat</strong></td><td><strong>: <?= htmlspecialchars($result['nomor_surat_display']) ?></strong></td></tr>
                    <tr><td>Kategori Surat</td><td>: <?= htmlspecialchars($result['kategori']) ?></td></tr>
                    <tr><td>Kepada</td><td>: <?= htmlspecialchars($result['kepada']) ?></td></tr>
                    <tr><td>Perihal</td><td>: <?= htmlspecialchars($result['perihal']) ?></td></tr>
                    <tr><td>Tanggal</td><td>: <?= htmlspecialchars($result['tanggal']) ?></td></tr>
                    <tr><td>Nama Pengaju</td><td>: <?= htmlspecialchars($result['nama_pengaju']) ?></td></tr>
                    <tr><td>Konseptor</td><td>: <?= htmlspecialchars($result['konseptor']) ?></td></tr>
                    <tr><td>Pukul</td><td>: <?= htmlspecialchars($result['pukul']) ?></td></tr>
                </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="loading-spinner" class="loading-overlay" style="display: none;">
        <div class="spinner"></div>
    </div>
    <?php
    break;

}

echo '</div>';
require_once 'includes/footer.php';
?>