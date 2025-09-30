<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// Jika pengguna sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}

$form_type = isset($_GET['form']) && $_GET['form'] == 'register' ? 'register' : 'login';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= ($form_type == 'login') ? 'Login Pengguna' : 'Registrasi Pengguna'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            background-image: url('assets/img/sium.png'); 
            background-size: 500px; 
            background-position: center; 
            background-repeat: no-repeat;
        }
        body::before {
            background-image: none !important;
        }
        .auth-box { 
            padding: 40px; 
            background: rgba(255, 255, 255, 1); 
            border-radius: 8px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.2); 
            width: 400px; 
        }
        .auth-box h2 { text-align: center; margin-top: 0; color: var(--dark-grey); }
        .auth-box .form-group { margin-bottom: 15px; }
        .auth-box button { width: 100%; }
        .auth-box .auth-link { text-align: center; margin-top: 20px; font-size: 0.9em; }
        .error-msg, .success-msg { padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        .error-msg { background: #f8d7da; color: #721c24; }
        .success-msg { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="auth-box">
        <?php if ($form_type == 'login'): ?>
            <h2>Login Pengguna</h2>
            <?php if (isset($_SESSION['error_msg'])) { echo '<p class="error-msg">'.$_SESSION['error_msg'].'</p>'; unset($_SESSION['error_msg']); } ?>
            <?php if (isset($_SESSION['success_msg'])) { echo '<p class="success-msg">'.$_SESSION['success_msg'].'</p>'; unset($_SESSION['success_msg']); } ?>
            <form action="process_login.php" method="POST">
                <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
                <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
                <button type="submit" class="btn-yellow">Login</button>
            </form>
            <div class="auth-link">Belum punya akun? <a href="auth.php?form=register">Daftar di sini</a></div>
        <?php else: ?>
    <h2>Registrasi Akun</h2>
    <?php if (isset($_SESSION['error_msg'])) { echo '<p class="error-msg">'.$_SESSION['error_msg'].'</p>'; unset($_SESSION['error_msg']); } ?>
    <form action="process_register.php" method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Ketik Ulang Password</label>
            <input type="password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn-yellow">Daftar</button>
    </form>
    <div class="auth-link">Sudah punya akun? <a href="auth.php">Login di sini</a></div>
<?php endif; ?>
    </div>
</body>
</html>