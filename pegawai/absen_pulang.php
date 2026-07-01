<?php
// pegawai/absen_pulang.php
require_once 'layout/header.php';

$pegawai_id = $_SESSION['pegawai_id'];
$today = date('Y-m-d');

// Cek absensi hari ini
$q_cek = $conn->query("SELECT * FROM absensi WHERE pegawai_id = $pegawai_id AND tanggal = '$today'");
$absen_hari_ini = $q_cek->fetch_assoc();

$bisa_absen_pulang = true;
$pesan = '';

if (!$absen_hari_ini) {
    $bisa_absen_pulang = false;
    $pesan = 'Anda belum melakukan absen masuk hari ini. Silakan absen masuk terlebih dahulu.';
} else if ($absen_hari_ini['status'] == 'Izin' || $absen_hari_ini['status'] == 'Cuti') {
    $bisa_absen_pulang = false;
    $pesan = 'Hari ini Anda terdaftar sedang <strong>' . $absen_hari_ini['status'] . '</strong>. Anda tidak perlu melakukan absensi pulang.';
} else if ($absen_hari_ini['jam_pulang'] != null) {
    $bisa_absen_pulang = false;
    $pesan = 'Anda sudah melakukan absen pulang hari ini pada jam <strong>' . $absen_hari_ini['jam_pulang'] . '</strong>.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $bisa_absen_pulang) {
    $image = $_POST['image'] ?? '';
    $jam_pulang = date('H:i:s');
    $latitude = sanitize($_POST['latitude'] ?? '');
    $longitude = sanitize($_POST['longitude'] ?? '');
    
    // Proses image (base64)
    $file_name = '';
    if (!empty($image)) {
        $image_parts = explode(";base64,", $image);
        if (count($image_parts) == 2) {
            $image_base64 = base64_decode($image_parts[1]);
            $file_name = 'pulang_' . $pegawai_id . '_' . time() . '.png';
            $file_path = '../uploads/absen/' . $file_name;
            
            if (!is_dir('../uploads/absen')) {
                mkdir('../uploads/absen', 0777, true);
            }
            file_put_contents($file_path, $image_base64);
        }
    }
    
    $stmt = $conn->prepare("UPDATE absensi SET jam_pulang = ?, foto_pulang = ?, latitude_pulang = ?, longitude_pulang = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $jam_pulang, $file_name, $latitude, $longitude, $absen_hari_ini['id']);
    
    if ($stmt->execute()) {
        set_alert('success', 'Absen Pulang Berhasil!', 'Anda tercatat pulang pada pukul ' . date('H:i') . ' WIB. Terima kasih atas kerja keras hari ini.');
    } else {
        set_alert('error', 'Gagal!', 'Terjadi kesalahan saat menyimpan data absensi pulang.');
    }
    header("Location: index.php");
    exit;
}
?>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Absen Pulang</h4>
            <p class="text-muted small mb-0">Lakukan presensi pulang dengan selfie kamera dan lokasi GPS.</p>
        </div>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
    </div>
    
    <?php if (!$bisa_absen_pulang): ?>
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <div class="card-body text-center py-5">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h5 class="fw-bold text-dark">Tidak Bisa Absen Pulang</h5>
                <p class="text-muted mb-3"><?= $pesan ?></p>
                <?php if (!$absen_hari_ini): ?>
                    <a href="absen_masuk.php" class="btn btn-primary"><i class="fas fa-sign-in-alt me-1"></i> Absen Masuk Dulu</a>
                <?php else: ?>
                    <a href="index.php" class="btn btn-primary"><i class="fas fa-home me-1"></i> Kembali ke Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-semibold"><i class="fas fa-camera text-danger me-2"></i>Kamera Selfie Absensi Pulang</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <p class="text-muted small mb-2">Jam saat ini: <strong id="currentTime" class="text-dark"></strong></p>
                            <p class="text-muted small mb-3">Jam masuk hari ini: <strong class="text-success"><?= $absen_hari_ini['jam_masuk'] ?></strong></p>
                            <div id="locationStatus" class="alert alert-warning py-2 mb-0 small"><i class="fas fa-map-marker-alt me-1"></i> Sedang mendeteksi GPS lokasi Anda...</div>
                        </div>
                        <div id="my_camera" class="mx-auto mb-3" style="width: 320px; height: 240px; border: 2px dashed #ccc; border-radius: 12px; overflow: hidden; background: #f8f9fa;"></div>
                        <button type="button" class="btn btn-danger btn-lg" id="btnAbsen" onClick="take_snapshot()" disabled>
                            <i class="fas fa-camera me-2"></i> Ambil Foto & Absen Pulang
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <form method="POST" action="absen_pulang.php" id="formAbsen">
            <input type="hidden" name="image" id="imageInput">
            <input type="hidden" name="latitude" id="latInput">
            <input type="hidden" name="longitude" id="lngInput">
        </form>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
        <script>
            Webcam.set({
                width: 320,
                height: 240,
                image_format: 'jpeg',
                jpeg_quality: 90
            });
            Webcam.attach('#my_camera');
            
            // Get Geolocation on load
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById('latInput').value = position.coords.latitude;
                    document.getElementById('lngInput').value = position.coords.longitude;
                    
                    const statusBox = document.getElementById('locationStatus');
                    statusBox.className = "alert alert-success py-2 mb-0 small";
                    statusBox.innerHTML = "<i class='fas fa-check-circle me-1'></i> Lokasi terdeteksi (" + position.coords.latitude.toFixed(5) + ", " + position.coords.longitude.toFixed(5) + ")";
                    document.getElementById('btnAbsen').disabled = false;
                }, function(error) {
                    const statusBox = document.getElementById('locationStatus');
                    statusBox.className = "alert alert-danger py-2 mb-0 small";
                    statusBox.innerHTML = "<i class='fas fa-exclamation-triangle me-1'></i> Gagal mendapat lokasi! Izinkan akses GPS untuk absen.";
                });
            } else {
                const statusBox = document.getElementById('locationStatus');
                statusBox.className = "alert alert-danger py-2 mb-0 small";
                statusBox.innerHTML = "<i class='fas fa-exclamation-triangle me-1'></i> GPS tidak didukung di browser ini.";
            }

            function take_snapshot() {
                Webcam.snap(function(data_uri) {
                    document.getElementById('imageInput').value = data_uri;
                    document.getElementById('formAbsen').submit();
                });
            }
            
            // Live clock
            function updateTime() {
                const now = new Date();
                const timeStr = String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0') + ':' + String(now.getSeconds()).padStart(2,'0') + ' WIB';
                const el = document.getElementById('currentTime');
                if (el) el.textContent = timeStr;
            }
            setInterval(updateTime, 1000);
            updateTime();
        </script>
    <?php endif; ?>
</div>

<?php require_once 'layout/footer.php'; ?>
