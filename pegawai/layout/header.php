<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pegawai') {
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
    <title>Pegawai - <?= NAMA_SISTEM ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="wrapper">
    <nav id="sidebar">
        <div class="brand">
            <h5><i class="fas fa-building me-1"></i> <?= NAMA_PERUSAHAAN ?></h5>
            <small><?= NAMA_SISTEM ?></small>
        </div>
        <div class="nav-menu">
            <div class="nav-label">Menu Utama</div>
            <div class="nav-item <?= $current_page=='index.php'?'active':'' ?>">
                <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
            </div>
            <div class="nav-label">Absensi</div>
            <div class="nav-item <?= $current_page=='absen_masuk.php'?'active':'' ?>">
                <a href="absen_masuk.php"><i class="fas fa-sign-in-alt"></i> Absen Masuk</a>
            </div>
            <div class="nav-item <?= $current_page=='absen_pulang.php'?'active':'' ?>">
                <a href="absen_pulang.php"><i class="fas fa-sign-out-alt"></i> Absen Pulang</a>
            </div>
            <div class="nav-item <?= $current_page=='riwayat.php'?'active':'' ?>">
                <a href="riwayat.php"><i class="fas fa-history"></i> Riwayat Absensi</a>
            </div>
            <div class="nav-label">Perizinan</div>
            <div class="nav-item <?= $current_page=='izin.php'?'active':'' ?>">
                <a href="izin.php"><i class="fas fa-envelope-open-text"></i> Pengajuan Izin</a>
            </div>
            <div class="nav-item <?= $current_page=='cuti.php'?'active':'' ?>">
                <a href="cuti.php"><i class="fas fa-calendar-minus"></i> Pengajuan Cuti</a>
            </div>
            <div class="nav-label">Akun</div>
            <div class="nav-item <?= $current_page=='profil.php'?'active':'' ?>">
                <a href="profil.php"><i class="fas fa-user-edit"></i> Profil</a>
            </div>
            <div class="nav-item danger">
                <a href="../logout.php" onclick="return confirm('Yakin ingin logout?')"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>
    <div id="content">
        <div class="topbar">
            <button class="btn-toggle" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
            <div class="user-info">
                <span class="small fw-semibold"><?= htmlspecialchars($_SESSION['nama']) ?></span>
                <img src="../uploads/profil/<?= $_SESSION['foto_profil'] ?? 'default.png' ?>" alt="Foto" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama']) ?>&background=4361ee&color=fff'">
            </div>
        </div>
