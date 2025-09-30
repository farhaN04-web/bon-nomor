<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pengajuan Nomor Surat Online - Polresta Banyumas</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/img/logo.png">
</head>
<body>
    <header>
        <div class="header-content">
            <img src="assets/img/logo.png" alt="Logo Polresta Banyumas" class="logo">
            <h1>POLRESTA BANYUMAS</h1>
        </div>
    </header>
    <main>
    <nav>
        <?php $page = isset($_GET['page']) ? $_GET['page'] : 'form'; ?>
        <a href="dashboard.php?page=form" class="<?= $page == 'form' ? 'active' : '' ?>">FORM PENGAJUAN</a>
        <a href="dashboard.php?page=riwayat" class="<?= $page == 'riwayat' ? 'active' : '' ?>">RIWAYAT NOMOR</a>
        <a href="logout.php" class="nav-keluar">Keluar</a>
    </nav>