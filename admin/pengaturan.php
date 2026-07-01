<?php
// admin/pengaturan.php
require_once 'layout/header.php';

// Handle Post
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jam_masuk = sanitize($_POST['jam_masuk']);
    $jam_toleransi = sanitize($_POST['jam_toleransi']);
    $jam_pulang = sanitize($_POST['jam_pulang']);
    $nama_perusahaan = sanitize($_POST['nama_perusahaan']);
    $nama_sistem = sanitize($_POST['nama_sistem']);
    
    global $db_host, $db_port, $db_user, $db_pass, $db_name;
    
    // Construct new file content for config/database.php
    $content = "<?php
ob_start();
// config/database.php
// =========================================================================
// PENGATURAN KREDENSIAL DATABASE (Silakan isi manual sesuai database Anda)
// =========================================================================
\$db_host = '$db_host';
\$db_port = '$db_port';
\$db_user = '$db_user';
\$db_pass = '$db_pass';
\$db_name = '$db_name';
// =========================================================================

class Database {
    private static \$mysqliConn = null;
    private static \$pdoConn = null;

    public static function getMysqliConnection() {
        global \$db_host, \$db_user, \$db_pass, \$db_name, \$db_port;
        if (self::\$mysqliConn === null) {
            self::\$mysqliConn = new mysqli(\$db_host, \$db_user, \$db_pass, \$db_name, (int)\$db_port);
            if (self::\$mysqliConn->connect_error) {
                die(\"Koneksi Database Gagal: \" . self::\$mysqliConn->connect_error);
            }
        }
        return self::\$mysqliConn;
    }

    public static function getPdoConnection() {
        global \$db_host, \$db_port, \$db_name, \$db_user, \$db_pass;
        if (self::\$pdoConn === null) {
            \$dsn = \"mysql:host=\$db_host;port=\$db_port;dbname=\$db_name;charset=utf8mb4\";

            try {
                self::\$pdoConn = new PDO(\$dsn, \$db_user, \$db_pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException \$e) {
                die(\"Koneksi database gagal: \" . \$e->getMessage());
            }
        }
        return self::\$pdoConn;
    }
}

// Inisialisasi koneksi MySQLi & PDO global
\$conn = Database::getMysqliConnection();
\$pdo = Database::getPdoConnection();

date_default_timezone_set('Asia/Jakarta');

// Jam batas masuk, toleransi, jam pulang, nama perusahaan & sistem
define('JAM_MASUK', '$jam_masuk');
define('JAM_TOLERANSI', '$jam_toleransi');
define('JAM_PULANG', '$jam_pulang');
define('NAMA_PERUSAHAAN', '$nama_perusahaan');
define('NAMA_SISTEM', '$nama_sistem');

// Konfigurasi SMTP Gmail (Mode Simulasi aktif secara bawaan)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USER', 'your_email@gmail.com');
define('SMTP_PASS', 'your_app_password');
?>";

    if (file_put_contents('../config/database.php', $content) !== false) {
        set_alert('success', 'Berhasil!', 'Pengaturan sistem berhasil diperbarui.');
        echo "<script>window.location='pengaturan.php';</script>";
        exit;
    } else {
        set_alert('error', 'Gagal!', 'Gagal memperbarui file pengaturan. Periksa permission file config/database.php.');
    }
}
?>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Pengaturan Sistem</h4>
            <p class="text-muted small mb-0">Konfigurasi batasan jam kerja dan identitas aplikasi.</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white"><h5 class="mb-0 fw-semibold text-primary"><i class="fas fa-cog me-2"></i>Identitas & Jam Kerja</h5></div>
                <div class="card-body">
                    <form action="pengaturan.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Perusahaan</label>
                            <input type="text" name="nama_perusahaan" class="form-control" value="<?= NAMA_PERUSAHAAN ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Sistem Absensi</label>
                            <input type="text" name="nama_sistem" class="form-control" value="<?= NAMA_SISTEM ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Jam Masuk (Mulai)</label>
                                <input type="time" name="jam_masuk" class="form-control" value="<?= JAM_MASUK ?>" required>
                                <small class="text-muted">Format 24 jam</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Batas Toleransi (Late)</label>
                                <input type="time" name="jam_toleransi" class="form-control" value="<?= JAM_TOLERANSI ?>" required>
                                <small class="text-muted">Absen di atas jam ini = Terlambat</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Jam Pulang (Mulai)</label>
                                <input type="time" name="jam_pulang" class="form-control" value="<?= JAM_PULANG ?>" required>
                                <small class="text-muted">Bisa absen pulang sejak jam ini</small>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary mt-2"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-5">
            <div class="card bg-light h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Panduan Pengaturan</h5>
                    <p class="small text-muted mb-2"><strong>Nama Perusahaan & Sistem:</strong> Digunakan sebagai brand/kop laporan, title tab browser, dan halaman login.</p>
                    <p class="small text-muted mb-2"><strong>Jam Masuk:</strong> Batas awal pegawai melakukan presensi harian tepat waktu sesuai SOP.</p>
                    <p class="small text-muted mb-2"><strong>Batas Toleransi:</strong> Jam batas akhir toleransi keterlambatan. Jika jam masuk diset 08:00 dan toleransi 08:15, maka pegawai yang absen pukul 08:16 akan berstatus 'Terlambat'.</p>
                    <p class="small text-muted mb-0"><strong>Jam Pulang:</strong> Pegawai baru diizinkan melakukan presensi pulang terhitung mulai dari jam ini.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>
