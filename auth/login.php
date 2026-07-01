<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: ../admin/index.php");
    } else {
        header("Location: ../pegawai/index.php");
    }
    exit;
}
require_once '../config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= NAMA_SISTEM ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <div class="logo-icon"><i class="fas fa-building"></i></div>
            <h4><?= NAMA_SISTEM ?></h4>
            <p><?= NAMA_PERUSAHAAN ?></p>
        </div>
        <div class="login-body">
            <form id="formLogin">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Username / NIP</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required autofocus>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePass"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember">
                        <label class="form-check-label small" for="remember">Ingat saya</label>
                    </div>
                    <a href="forgot-password.php" class="small text-primary">Lupa Password?</a>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2"><i class="fas fa-sign-in-alt me-2"></i>Login</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('togglePass').addEventListener('click', function() {
    const p = document.getElementById('password');
    const icon = this.querySelector('i');
    if (p.type === 'password') { p.type = 'text'; icon.classList.replace('fa-eye','fa-eye-slash'); }
    else { p.type = 'password'; icon.classList.replace('fa-eye-slash','fa-eye'); }
});

document.getElementById('formLogin').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch('proses_login.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            Swal.fire({icon:'success',title:'Login Berhasil!',text:'Selamat datang, '+d.nama,timer:1500,showConfirmButton:false}).then(()=>{
                window.location.href = d.redirect;
            });
        } else {
            Swal.fire({icon:'error',title:'Gagal Login',text:d.message});
        }
    });
});
</script>
</body>
</html>
