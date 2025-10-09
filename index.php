<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pengajuan Nomor Surat Online - Polresta Banyumas</title>
    <link rel="icon" href="assets/img/logo.png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap');
        body, html { height: 100%; margin: 0; font-family: 'Roboto', sans-serif; color: white; overflow: hidden; }
        .landing-container {
            height: 100%;
            background-image: url('assets/img/bg.png');
            background-size: cover; background-position: center;
            display: flex; justify-content: center; align-items: center; position: relative;
        }
        .landing-container::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.5); z-index: 1;
        }
        .landing-content { position: relative; z-index: 2; text-align: center; padding: 20px; }
        .logo-container { margin-bottom: 25px; display: flex; justify-content: center; align-items: center; gap: 20px; }
        .logo-container img { height: 100px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.4)); }
        .landing-content h1 { font-size: 2.8em; font-weight: 900; margin: 0; text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7); line-height: 1.2; }
        .landing-content p { font-size: 1.2em; margin-top: 10px; text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.7); }
        footer { position: absolute; bottom: 15px; width: 100%; text-align: center; z-index: 2; font-size: 0.9em; color: rgba(255, 255, 255, 0.8); }
        
        /*STYLE UNTUK TOMBOL LOGIN DAN REGISTER*/
        .top-right-buttons {
            position: absolute;
            top: 20px;
            right: 30px;
            z-index: 10;
            display: flex;
            gap: 15px;
        }
        .top-right-buttons a {
            text-decoration: none;
            color: white;
            padding: 10px 25px;
            border: 2px solid white;
            border-radius: 5px;
            font-weight: 700;
            transition: all 0.3s ease;
        }
        .top-right-buttons a.btn-register {
            background-color: #fecb00;
            border-color: #fecb00;
            color: #333;
        }
        .top-right-buttons a:hover {
            background-color: white;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <div class="top-right-buttons">
            <a href="auth.php" class="btn-login">Login</a>
            <a href="auth.php?form=register" class="btn-register">Register</a>
        </div>

        <div class="landing-content">
            <div class="logo-container">
                <img src="assets/img/logo_polri.png" alt="Logo POLRI">
                <img src="assets/img/logo.png" alt="Logo Polresta Banyumas">
            </div>
            <h1>SISTEM PENGAJUAN NOMOR SURAT<br>POLRESTA ONLINE</h1>
            <p>Akses untuk pengajuan nomor surat otomatis</p>
            </div>
        <footer>
            &copy; <?= date('Y') ?> Polresta Banyumas. Hak cipta dilindungi undang-undang.
        </footer>
    </div>
</body>
</html>