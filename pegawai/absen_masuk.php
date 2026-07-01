<?php
// pegawai/absen_masuk.php
require_once 'layout/header.php';

$pegawai_id = $_SESSION['pegawai_id'];
$today = date('Y-m-d');

// Cek apakah sudah absen masuk hari ini
$q_cek = $conn->query("SELECT * FROM absensi WHERE pegawai_id = $pegawai_id AND tanggal = '$today'");
$sudah_absen = $q_cek->num_rows > 0;
$data_absen = $sudah_absen ? $q_cek->fetch_assoc() : null;

$jam_sekarang = date('H:i:s');
$belum_dibuka = $jam_sekarang < JAM_MASUK;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$sudah_absen && !$belum_dibuka) {
    $image = $_POST['image'] ?? '';
    $jam_masuk = date('H:i:s');
    $latitude = sanitize($_POST['latitude'] ?? '');
    $longitude = sanitize($_POST['longitude'] ?? '');
    
    // Tentukan status kehadiran berdasarkan JAM_TOLERANSI dari config
    $batas_waktu = strtotime(JAM_TOLERANSI);
    $waktu_absen = strtotime($jam_masuk);
    $status = ($waktu_absen > $batas_waktu) ? 'Terlambat' : 'Hadir';
    
    // Proses image (base64 dari webcam)
    $file_name = '';
    if (!empty($image)) {
        $image_parts = explode(";base64,", $image);
        if (count($image_parts) == 2) {
            $image_base64 = base64_decode($image_parts[1]);
            $file_name = 'masuk_' . $pegawai_id . '_' . time() . '.png';
            $file_path = '../uploads/absen/' . $file_name;
            
            if (!is_dir('../uploads/absen')) {
                mkdir('../uploads/absen', 0777, true);
            }
            file_put_contents($file_path, $image_base64);
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO absensi (pegawai_id, tanggal, jam_masuk, foto_masuk, status, latitude_masuk, longitude_masuk) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $pegawai_id, $today, $jam_masuk, $file_name, $status, $latitude, $longitude);
    
    if ($stmt->execute()) {
        set_alert('success', 'Absen Masuk Berhasil!', 'Anda tercatat hadir pada pukul ' . date('H:i') . ' WIB. Status: ' . $status);
    } else {
        set_alert('error', 'Gagal!', 'Terjadi kesalahan saat menyimpan data absensi.');
    }
    header("Location: index.php");
    exit;
}
?>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Absen Masuk</h4>
            <p class="text-muted small mb-0">Lakukan presensi harian dengan selfie kamera dan lokasi GPS.</p>
        </div>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
    </div>
    
    <?php if ($belum_dibuka): ?>
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <div class="card-body text-center py-5">
                <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                <h5 class="fw-bold text-dark">Absen Masuk Belum Dibuka</h5>
                <p class="text-muted mb-3">Absen masuk hari ini baru dibuka mulai pukul <strong><?= date('H:i', strtotime(JAM_MASUK)) ?> WIB</strong> sesuai dengan SOP perusahaan.</p>
                <p class="text-muted small mb-3">Jam saat ini: <strong id="currentTimeBelum" class="text-dark"></strong></p>
                <a href="index.php" class="btn btn-primary"><i class="fas fa-home me-1"></i> Kembali ke Dashboard</a>
            </div>
        </div>
        <script>
            function updateTimeBelum() {
                const now = new Date();
                const timeStr = String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0') + ':' + String(now.getSeconds()).padStart(2,'0') + ' WIB';
                const el = document.getElementById('currentTimeBelum');
                if (el) el.textContent = timeStr;
            }
            setInterval(updateTimeBelum, 1000);
            updateTimeBelum();
        </script>
    <?php elseif ($sudah_absen): ?>
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <div class="card-body text-center py-5">
                <?php if ($data_absen['status'] == 'Izin' || $data_absen['status'] == 'Cuti'): ?>
                    <i class="fas fa-calendar-minus fa-3x text-info mb-3"></i>
                    <h5 class="fw-bold text-dark">Hari Ini Anda Terdaftar Sedang <?= $data_absen['status'] ?></h5>
                    <p class="text-muted mb-3">Anda tidak perlu melakukan absensi masuk karena status Anda hari ini adalah <strong><?= $data_absen['status'] ?></strong>.</p>
                <?php else: ?>
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5 class="fw-bold text-dark">Anda Sudah Absen Masuk Hari Ini</h5>
                    <p class="text-muted mb-3">Tercatat pada jam <strong><?= $data_absen['jam_masuk'] ?></strong> dengan status 
                        <span class="badge bg-<?= $data_absen['status'] == 'Hadir' ? 'success' : 'warning' ?>"><?= $data_absen['status'] ?></span>
                    </p>
                <?php endif; ?>
                <a href="index.php" class="btn btn-primary"><i class="fas fa-home me-1"></i> Kembali ke Dashboard</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-semibold"><i class="fas fa-camera text-primary me-2"></i>Kamera Selfie Absensi</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <p class="text-muted small mb-2">Jam saat ini: <strong id="currentTime" class="text-dark"></strong></p>
                            <p class="text-muted small mb-3">Batas hadir tepat waktu: <strong class="text-primary"><?= JAM_TOLERANSI ?></strong></p>
                            <div id="locationStatus" class="alert alert-warning py-2 mb-0 small"><i class="fas fa-map-marker-alt me-1"></i> Sedang mendeteksi GPS lokasi Anda...</div>
                        </div>
                        <div id="my_camera" class="mx-auto mb-3" style="width: 320px; height: 240px; border: 2px dashed #ccc; border-radius: 12px; overflow: hidden; background: #f8f9fa;"></div>
                        <button type="button" class="btn btn-primary btn-lg" id="btnAbsen" onClick="take_snapshot()" disabled>
                            <i class="fas fa-camera me-2"></i> Ambil Foto & Absen Masuk
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <form method="POST" action="absen_masuk.php" id="formAbsen">
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
                    document.getElementById('longitudeInput'); // wait, let's make sure ID matches
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
                if(el) el.textContent = timeStr;
            }
            setInterval(updateTime, 1000);
            updateTime();
        </script>
    <?php endif; ?>
</div>

<?php require_once 'layout/footer.php'; ?>
