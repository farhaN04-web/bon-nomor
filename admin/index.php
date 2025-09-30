<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f0f0f0; }
        .login-box { padding: 40px; background: white; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 350px; }
        .login-box h2 { text-align: center; margin-bottom: 20px; }
        .login-box .form-group { margin-bottom: 15px; }
        .login-box input { width: 100%; }
        .login-box button { width: 100%; }
        .error-msg { color: red; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login Admin</h2>
        <?php if(isset($_SESSION['login_error'])): ?>
            <p class="error-msg"><?= $_SESSION['login_error']; ?></p>
            <?php unset($_SESSION['login_error']); ?>
        <?php endif; ?>
        <form action="login_process.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-yellow">Masuk</button>
        </form>
    </div>
</body>
</html>