<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
define('BASE_URL', '/web-master/');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if ($user !== '' && $pass !== '') {
        $stmt = $db->prepare("SELECT id, nama, password, role FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row && password_verify($pass, $row['password'])) {
            $_SESSION['admin_id']   = $row['id'];
            $_SESSION['admin_nama'] = $row['nama'];
            $_SESSION['admin_role'] = $row['role'];
            $_SESSION['admin']      = true; // backward compat
            header('Location: ' . BASE_URL . 'admin/');
            exit;
        }
    }
    $error = 'Username atau password salah.';
}

if (!empty($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . 'admin/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | Master Computer Store</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/logo.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: url('../assets/bg.png') center center / cover no-repeat fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(7, 17, 31, 0.55);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 0;
        }
        .login-card {
            position: relative;
            z-index: 1;
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,.4);
        }
        .login-logo {
            width: 80px; height: 80px;
            border-radius: 16px;
            overflow: hidden;
            margin: 0 auto 20px;
            display: flex; align-items: center; justify-content: center;
            background: #f0f4f8;
            border: 2px solid #e0e5eb;
        }
        .login-logo img {
            width: 100%; height: 100%;
            object-fit: contain;
            padding: 6px;
        }
        .form-control { border-radius: 8px; padding: 11px 14px; border: 1.5px solid #e0e5eb; }
        .form-control:focus { border-color: #1565c0; box-shadow: 0 0 0 3px rgba(21,101,192,.12); }
        .btn-login {
            background: #1565c0; color: #fff; border: none;
            border-radius: 8px; padding: 12px; font-weight: 600;
            width: 100%; font-size: 15px; transition: background .2s;
        }
        .btn-login:hover { background: #0d47a1; }
        .back-link { color: #6c757d; font-size: 13px; text-decoration: none; }
        .back-link:hover { color: #1565c0; }
        .input-group-text { background: #f8f9fa; border: 1.5px solid #e0e5eb; border-radius: 8px 0 0 8px; }
        .input-group .form-control { border-radius: 0 8px 8px 0; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <img src="<?= BASE_URL ?>assets/logo.jpg" alt="Logo Master Computer Store">
        </div>
        <h4 class="text-center fw-bold mb-1">Admin Panel</h4>
        <p class="text-center text-muted mb-4" style="font-size:14px;">Master Computer Store</p>

        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2" style="font-size:14px;border-radius:8px;">
                <i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:14px;">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person text-muted"></i></span>
                    <input type="text" name="username" class="form-control"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           placeholder="Masukkan username" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold" style="font-size:14px;">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control"
                           placeholder="Masukkan password" required>
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk ke Admin Panel
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>" class="back-link">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Website
            </a>
        </div>
    </div>
</body>
</html>
