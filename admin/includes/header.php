<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> | Master Computer Store</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/logo.jpg">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- AdminLTE 3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">

    <style>
        :root {
            --primary: #1565c0;
            --primary-dark: #0d47a1;
            --sidebar-bg: #1a2035;
            --sidebar-hover: #1565c0;
        }
        body { font-family: 'Inter', sans-serif; }
        .main-sidebar { background: var(--sidebar-bg) !important; }
        .brand-link { background: var(--primary) !important; border-bottom: 1px solid rgba(255,255,255,.1) !important; }
        .brand-link:hover { background: var(--primary-dark) !important; }
        .nav-sidebar .nav-link { color: rgba(255,255,255,.75) !important; border-radius: 6px; margin: 2px 8px; }
        .nav-sidebar .nav-link:hover, .nav-sidebar .nav-link.active { background: var(--sidebar-hover) !important; color: #fff !important; }
        .nav-sidebar .nav-link .nav-icon { color: rgba(255,255,255,.6) !important; }
        .nav-sidebar .nav-link.active .nav-icon, .nav-sidebar .nav-link:hover .nav-icon { color: #fff !important; }
        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active { background: var(--primary) !important; }
        .content-wrapper { background: #f4f6f9; }
        .card { border: none; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .card-header { border-radius: 10px 10px 0 0 !important; border-bottom: 1px solid #eee; background: #fff; }
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }
        .main-header { border-bottom: 1px solid #e0e5eb !important; }
        .navbar-white { background: #fff !important; }
        .small-box { border-radius: 10px; overflow: hidden; }
        .small-box .inner h3 { font-weight: 700; }
        .table th { font-size: 12px; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; font-weight: 600; }
        .badge { font-size: 11px; }
        .nav-header { font-size: 11px; letter-spacing: 1px; color: rgba(255,255,255,.4) !important; padding: 10px 16px 4px; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="bi bi-list fs-5"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?= BASE_URL ?>" class="nav-link text-muted" target="_blank">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Lihat Website
                </a>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 pe-3" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div style="width:34px;height:34px;border-radius:50%;background:#1565c0;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-person-fill text-white" style="font-size:16px;"></i>
                    </div>
                    <div class="d-none d-sm-block" style="line-height:1.2;">
                        <div style="font-size:13px;font-weight:600;color:#202832;"><?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin') ?></div>
                        <div style="font-size:11px;color:#6c757d;"><?= ($_SESSION['admin_role'] ?? '') === 'superadmin' ? 'Super Admin' : 'Admin' ?></div>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow" style="border-radius:12px;border:1px solid #e0e5eb;min-width:200px;padding:8px;">
                    <li>
                        <div class="px-3 py-2 mb-1" style="border-bottom:1px solid #f0f0f0;">
                            <div style="font-size:13px;font-weight:600;color:#202832;"><?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin') ?></div>
                            <div style="font-size:12px;color:#6c757d;"><?= ($_SESSION['admin_role'] ?? '') === 'superadmin' ? 'Super Admin' : 'Admin' ?></div>
                        </div>
                    </li>
                    <li>
                        <a class="dropdown-item rounded-2 d-flex align-items-center gap-2" href="<?= BASE_URL ?>" target="_blank" style="font-size:13px;padding:9px 12px;">
                            <i class="bi bi-box-arrow-up-right text-muted"></i> Lihat Website
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item rounded-2 d-flex align-items-center gap-2 text-danger" href="<?= BASE_URL ?>admin/logout.php" style="font-size:13px;padding:9px 12px;">
                            <i class="bi bi-box-arrow-right"></i> Keluar
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-1">
        <a href="<?= BASE_URL ?>admin/" class="brand-link px-3 d-flex align-items-center gap-2">
            <img src="<?= BASE_URL ?>assets/logo.jpg" alt="Logo" style="width:36px;height:36px;object-fit:contain;border-radius:8px;background:#fff;padding:3px;flex-shrink:0;">
            <span class="brand-text fw-bold text-white">Master Computer</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-header">MENU UTAMA</li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>admin/" class="nav-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-speedometer2"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>admin/produk.php" class="nav-link <?= ($activePage ?? '') === 'produk' ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-box-seam"></i>
                            <p>Kelola Produk</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>admin/laporan.php" class="nav-link <?= ($activePage ?? '') === 'laporan' ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-flag"></i>
                            <p>Laporan Masalah
                                <?php
                                $jmlLaporan = $db->query("SELECT COUNT(*) FROM laporan")->fetch_row()[0] ?? 0;
                                if ($jmlLaporan > 0): ?>
                                    <span class="badge bg-danger ms-1"><?= $jmlLaporan ?></span>
                                <?php endif; ?>
                            </p>
                        </a>
                    </li>
                    <li class="nav-header">PENGATURAN</li>
                    <?php if (($_SESSION['admin_role'] ?? '') === 'superadmin'): ?>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>admin/users.php" class="nav-link <?= ($activePage ?? '') === 'users' ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-people"></i>
                            <p>Kelola Users</p>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>admin/pengaturan_landing.php" class="nav-link <?= ($activePage ?? '') === 'landing' ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-layout-text-window"></i>

                            <p>Kelola Landing Page</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>admin/pengaturan.php" class="nav-link <?= ($activePage ?? '') === 'pengaturan' ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-gear"></i>
                            <p>Pengaturan Toko</p>
                        </a>
                    </li>

                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 fw-semibold" style="font-size:1.4rem;"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/">Dashboard</a></li>
                            <?php if (isset($breadcrumb)): ?>
                                <li class="breadcrumb-item active"><?= htmlspecialchars($breadcrumb) ?></li>
                            <?php endif; ?>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">
