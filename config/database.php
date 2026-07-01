<?php
// config/database.php
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');

$conn = new mysqli($host, $user, $pass, $db, (int)$port);
if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Jakarta');

// Jam batas masuk (08:00), toleransi 15 menit (08:15)
define('JAM_MASUK', '08:00:00');
define('JAM_TOLERANSI', '08:15:00');
define('JAM_PULANG', '17:00:00');
define('NAMA_PERUSAHAAN', 'PT Maju Bersama Indonesia');
define('NAMA_SISTEM', 'Sistem Absensi Pegawai');

// Konfigurasi SMTP Gmail (Ganti dengan akun asli untuk kirim email real)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USER', 'your_email@gmail.com');
define('SMTP_PASS', 'your_app_password'); // App Password 16 digit dari Google
?>
