<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
require_once '../config/database.php';
require_once '../config/helpers.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?= NAMA_SISTEM ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="brand">
            <h5><i class="fas fa-building me-1"></i> <?= NAMA_PERUSAHAAN ?></h5>
            <small><?= NAMA_SISTEM ?></small>
        </div>
        <div class="nav-menu">
            <div class="nav-label">Menu Utama</div>
            <div class="nav-item <?= $current_page=='index.php'?'active':'' ?>">
                <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </div>

            <div class="nav-label">Master Data</div>
            <div class="nav-item <?= $current_page=='pegawai.php'?'active':'' ?>">
                <a href="pegawai.php"><i class="fas fa-users"></i> Data Pegawai</a>
            </div>
            <div class="nav-item <?= $current_page=='user.php'?'active':'' ?>">
                <a href="user.php"><i class="fas fa-user-cog"></i> Data User</a>
            </div>
            <div class="nav-item <?= $current_page=='jabatan.php'?'active':'' ?>">
                <a href="jabatan.php"><i class="fas fa-briefcase"></i> Data Jabatan</a>
            </div>
            <div class="nav-item <?= $current_page=='divisi.php'?'active':'' ?>">
                <a href="divisi.php"><i class="fas fa-sitemap"></i> Data Divisi</a>
            </div>

            <div class="nav-label">Absensi</div>
            <div class="nav-item <?= $current_page=='absensi.php'?'active':'' ?>">
                <a href="absensi.php"><i class="fas fa-calendar-check"></i> Data Absensi</a>
            </div>

            <div class="nav-label">Perizinan</div>
            <div class="nav-item <?= $current_page=='izin.php'?'active':'' ?>">
                <a href="izin.php"><i class="fas fa-envelope-open-text"></i> Data Izin</a>
            </div>
            <div class="nav-item <?= $current_page=='cuti.php'?'active':'' ?>">
                <a href="cuti.php"><i class="fas fa-calendar-minus"></i> Data Cuti</a>
            </div>

            <div class="nav-label">Lainnya</div>
            <div class="nav-item <?= $current_page=='laporan.php'?'active':'' ?>">
                <a href="laporan.php"><i class="fas fa-file-alt"></i> Laporan</a>
            </div>
            <div class="nav-item <?= $current_page=='pengumuman.php'?'active':'' ?>">
                <a href="pengumuman.php"><i class="fas fa-bullhorn"></i> Pengumuman</a>
            </div>
            <div class="nav-item <?= $current_page=='pengaturan.php'?'active':'' ?>">
                <a href="pengaturan.php"><i class="fas fa-cog"></i> Pengaturan</a>
            </div>
            <div class="nav-item <?= $current_page=='profil.php'?'active':'' ?>">
                <a href="profil.php"><i class="fas fa-user"></i> Profil</a>
            </div>
            <div class="nav-item danger">
                <a href="../logout.php" onclick="return confirm('Yakin ingin logout?')"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div id="content">
        <!-- Topbar -->
        <div class="topbar">
            <div>
                <button class="btn-toggle" onclick="document.getElementById('sidebar').classList.toggle('show')">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="user-info">
                <span class="small fw-semibold">Administrator</span>
                <img src="../assets/img/admin.png" alt="Admin" onerror="this.src='https://ui-avatars.com/api/?name=Admin&background=4361ee&color=fff'">
            </div>
        </div>
