<?php
// config/database.php
// =========================================================================
// PENGATURAN KREDENSIAL DATABASE (Silakan isi manual sesuai database Anda)
// =========================================================================
$db_host = 'reseau.proxy.rlwy.net';
$db_port = '16932';
$db_user = 'root';
$db_pass = 'OfrKvuXMdjoynfVVEXpUNyRNOZiqRnrC';
$db_name = 'railway';
// =========================================================================

class Database {
    private static $mysqliConn = null;
    private static $pdoConn = null;

    public static function getMysqliConnection() {
        global $db_host, $db_user, $db_pass, $db_name, $db_port;
        if (self::$mysqliConn === null) {
            self::$mysqliConn = new mysqli($db_host, $db_user, $db_pass, $db_name, (int)$db_port);
            if (self::$mysqliConn->connect_error) {
                die("Koneksi Database Gagal: " . self::$mysqliConn->connect_error);
            }
        }
        return self::$mysqliConn;
    }

    public static function getPdoConnection() {
        global $db_host, $db_port, $db_name, $db_user, $db_pass;
        if (self::$pdoConn === null) {
            $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";

            try {
                self::$pdoConn = new PDO($dsn, $db_user, $db_pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                die("Koneksi database gagal: " . $e->getMessage());
            }
        }
        return self::$pdoConn;
    }
}

// Inisialisasi koneksi MySQLi & PDO global
$conn = Database::getMysqliConnection();
$pdo = Database::getPdoConnection();

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
