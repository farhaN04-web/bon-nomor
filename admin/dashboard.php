<?php
session_start();
// Timeout
$lifetime = 86400;

if (isset($_SESSION['admin_login_timestamp']) && (time() - $_SESSION['admin_login_timestamp']) > $timeout_duration) {
    session_unset();    // Hapus semua variabel sesi
    session_destroy();  // Hancurkan sesi
    header("Location: index.php?pesan=sesi_berakhir"); // Arahkan ke login dengan pesan
    exit();
}

// Keamanan: Jika belum login, masih ke halaman login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}
require '../config/koneksi.php';

// Proses update pengaturan jika ada form yang disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_pengaturan'])) {
    // Update Waktu
    $waktu_buka = $_POST['waktu_buka'];
    $waktu_tutup = $_POST['waktu_tutup'];
    mysqli_query($conn, "UPDATE pengaturan SET setting_nilai = '$waktu_buka' WHERE setting_nama = 'waktu_buka'");
    mysqli_query($conn, "UPDATE pengaturan SET setting_nilai = '$waktu_tutup' WHERE setting_nama = 'waktu_tutup'");

    // Update Nomor Awal per Kategori
    if (isset($_POST['nomor_awal']) && is_array($_POST['nomor_awal'])) {
        foreach ($_POST['nomor_awal'] as $kategori => $nomor) {
            $kategori_aman = mysqli_real_escape_string($conn, $kategori);
            $nomor_aman = (int)$nomor;
            
            $query_nomor = "INSERT INTO kategori_pengaturan (nama_kategori, nomor_awal) VALUES ('$kategori_aman', $nomor_aman)
                            ON DUPLICATE KEY UPDATE nomor_awal = $nomor_aman";
            mysqli_query($conn, $query_nomor);
        }
    }
    $pesan_sukses = "Pengaturan berhasil diperbarui.";
}

// Ambil data pengaturan waktu
$pengaturan_query = mysqli_query($conn, "SELECT * FROM pengaturan");
$pengaturan = [];
while($row = mysqli_fetch_assoc($pengaturan_query)) {
    $pengaturan[$row['setting_nama']] = $row['setting_nilai'];
}

$menu = isset($_GET['menu']) ? $_GET['menu'] : 'riwayat';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-wrapper { display: flex; }
        .sidebar { width: 250px; background: #333; color: white; min-height: 100vh; flex-shrink: 0; }
        .sidebar h3 { text-align: center; padding: 20px; background: #222; margin: 0;}
        .sidebar a { display: block; padding: 15px 20px; color: white; text-decoration: none; border-bottom: 1px solid #444; }
        .sidebar a:hover, .sidebar a.active { background: var(--primary-yellow); color: var(--dark-grey); }
        .admin-content { flex-grow: 1; padding: 30px; background-color: #f4f7fa; }
        .admin-content h2 { border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 20px; }
        .admin-table { width: 100%; border-collapse: collapse; box-shadow: 0 2px 8px rgba(0,0,0,0.05); background: #fff; border-radius: 8px; overflow: hidden; }
        .admin-table th, .admin-table td { padding: 15px; text-align: left; border-bottom: 1px solid #e9ecef; }
        .admin-table thead th { background-color: #f8f9fa; font-weight: 600; color: #6c757d; font-size: 0.9em; text-transform: uppercase; }
        .admin-table tbody tr:nth-child(even) { background-color: #f9f9f9; }
        .admin-table tbody tr:hover { background-color: #f1f5f9; }
        .admin-table td { vertical-align: middle; }
        .btn-sm { padding: 5px 12px !important; font-size: 0.85em !important; font-weight: normal !important; }
        .admin-search-bar { display: flex; margin-bottom: 20px; }
        .admin-search-bar input[type="text"] { flex-grow: 1; padding: 12px; border: 1px solid #ddd; border-radius: 5px 0 0 5px; font-size: 1em; }
        .admin-search-bar button { border: none; background-color: var(--dark-grey); color: white; padding: 0 25px; cursor: pointer; border-radius: 0 5px 5px 0; font-weight: bold; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <h3>ADMIN PANEL</h3>
            <a href="dashboard.php?menu=riwayat" class="<?= $menu == 'riwayat' ? 'active' : '' ?>">Riwayat Bon Nomor</a>
            <a href="dashboard.php?menu=pengaturan" class="<?= $menu == 'pengaturan' ? 'active' : '' ?>">Pengaturan</a>
            <a href="logout.php">Logout</a>
        </aside>
        <main class="admin-content">
            <?php if ($menu == 'riwayat'): ?>
                <h2>Riwayat Bon Nomor Surat</h2>
                
                <?php if(isset($_SESSION['sync_message'])): ?>
                    <p style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;"><?= $_SESSION['sync_message']; ?></p>
                    <?php unset($_SESSION['sync_message']); endif; ?>

                <form class="admin-search-bar" method="GET">
                    <input type="hidden" name="menu" value="riwayat">
                    <input type="text" name="search" placeholder="Cari berdasarkan nomor, perihal, pengaju..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    <button type="submit">Cari</button>
                </form>

                <div style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="download_report.php" class="btn-yellow" style="display: inline-block;">Download Laporan Excel</a>
                    <a href="sync_from_sheets.php" class="btn-yellow" style="background-color: #17a2b8; color: white; display: inline-block;" onclick="return confirm('Apakah Anda yakin ingin menarik data dari Google Sheets?');">
                        Sinkronkan dari Google Sheets
                    </a>
                </div>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nomor Surat</th>
                            <th>Kategori</th>
                            <th>Kepada</th>
                            <th>Perihal</th>
                            <th>Pengaju</th>
                            <th>Konseptor</th>
                            <th>Status Arsip</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            // Pencarian Tanggal Admin
                            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
                            $query = "SELECT * FROM surat ";
                            $params = [];
                            $types = '';

                            if (!empty($search)) {
                                $where_clauses = [];
                                $search_term_like = "%" . $search . "%";
                                
                                $text_fields = ['nomor_surat', 'kategori', 'kepada', 'perihal', 'nama_pengaju', 'konseptor'];
                                foreach ($text_fields as $field) {
                                    $where_clauses[] = "`$field` LIKE ?";
                                    $params[] = $search_term_like;
                                    $types .= 's';
                                }

                                $date_obj = DateTime::createFromFormat('d/m/Y', $search);
                                if (!$date_obj) {
                                    $date_obj = DateTime::createFromFormat('Y-m-d', $search);
                                }

                                if ($date_obj) {
                                    $search_date = $date_obj->format('Y-m-d');
                                    $where_clauses[] = "DATE(tanggal_pengajuan) = ?";
                                    $params[] = $search_date;
                                    $types .= 's';
                                }
                                
                                if (!empty($where_clauses)) {
                                    $query .= "WHERE " . implode(' OR ', $where_clauses);
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
                                <td><?= htmlspecialchars($row['kepada']) ?></td>
                                <td><?= htmlspecialchars($row['perihal']) ?></td>
                                <td><?= htmlspecialchars($row['nama_pengaju']) ?></td>
                                <td><?= htmlspecialchars($row['konseptor']) ?></td>
                                <td>
                                    <?php if($row['file_arsip']): ?>
                                        <a href="../uploads/<?= htmlspecialchars($row['file_arsip']) ?>" target="_blank" class="status green">Tersedia</a>
                                    <?php else: ?>
                                        <span class="status red">Belum Ada</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else: ?>
                                <tr><td colspan="7" style="text-align: center;">Tidak ada data ditemukan.</td></tr>
                            <?php endif; ?>
                    </tbody>
                </table>

            <?php elseif ($menu == 'pengaturan'): ?>
                <h2>Pengaturan Website</h2>
                <?php if(isset($pesan_sukses)): ?>
                    <p style="color: green; font-weight: bold;"><?= $pesan_sukses; ?></p>
                <?php endif; ?>
                
                <form method="POST" style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                    <div class="form-group">
                        <label for="waktu_buka">Jam Buka Akses</label>
                        <input type="time" id="waktu_buka" name="waktu_buka" value="<?= htmlspecialchars($pengaturan['waktu_buka'] ?? '00:00') ?>">
                    </div>
                    <div class="form-group">
                        <label for="waktu_tutup">Jam Tutup Akses</label>
                        <input type="time" id="waktu_tutup" name="waktu_tutup" value="<?= htmlspecialchars($pengaturan['waktu_tutup'] ?? '23:59') ?>">
                    </div>
                    
                    <hr style="margin: 30px 0;">
                    <h4>Pengaturan Nomor Urut Awal</h4>
                    
                    <?php 
                        $kategori_query = mysqli_query($conn, "SELECT * FROM kategori_pengaturan");
                        while($kategori_row = mysqli_fetch_assoc($kategori_query)): 
                    ?>
                    <div class="form-group">
                        <label>Mulai Nomor Urut untuk <strong><?= htmlspecialchars($kategori_row['nama_kategori']) ?></strong></label>
                        <input type="number" name="nomor_awal[<?= htmlspecialchars($kategori_row['nama_kategori']) ?>]" 
                            value="<?= htmlspecialchars($kategori_row['nomor_awal']) ?>">
                    </div>
                    <?php endwhile; ?>
                    
                    <small>Nomor urut akan dimulai dari angka ini + jumlah surat yang sudah ada di tahun ini untuk kategori tersebut.</small>
                    <br><br>
                    <button type="submit" name="update_pengaturan" class="btn-yellow">Simpan Pengaturan</button>
                </form>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>