<?php
require_once 'layout/header.php';
$pid = $_SESSION['pegawai_id'];
$today = date('Y-m-d');
$month = date('m');
$year = date('Y');

$absen = $conn->query("SELECT * FROM absensi WHERE pegawai_id=$pid AND tanggal='$today'")->fetch_assoc();
$hadir = $conn->query("SELECT COUNT(*) as t FROM absensi WHERE pegawai_id=$pid AND MONTH(tanggal)='$month' AND YEAR(tanggal)='$year' AND status='Hadir'")->fetch_assoc()['t'];
$telat = $conn->query("SELECT COUNT(*) as t FROM absensi WHERE pegawai_id=$pid AND MONTH(tanggal)='$month' AND YEAR(tanggal)='$year' AND status='Terlambat'")->fetch_assoc()['t'];
$izin_count = $conn->query("SELECT COUNT(*) as t FROM absensi WHERE pegawai_id=$pid AND MONTH(tanggal)='$month' AND YEAR(tanggal)='$year' AND status='Izin'")->fetch_assoc()['t'];
$cuti_count = $conn->query("SELECT COUNT(*) as t FROM absensi WHERE pegawai_id=$pid AND MONTH(tanggal)='$month' AND YEAR(tanggal)='$year' AND status='Cuti'")->fetch_assoc()['t'];
$days_passed = (int)date('d');
$alpha_count = max(0, $days_passed - ($hadir + $telat + $izin_count + $cuti_count));
$riwayat = $conn->query("SELECT * FROM absensi WHERE pegawai_id=$pid ORDER BY tanggal DESC LIMIT 5");
$pengumuman = $conn->query("SELECT * FROM pengumuman ORDER BY tanggal DESC LIMIT 3");
?>
<div class="page-content">
    <!-- Welcome Card -->
    <div class="card mb-4" style="background:linear-gradient(135deg,#4361ee,#4cc9f0);color:white;border:none;">
        <div class="card-body d-flex align-items-center p-4">
            <img src="../uploads/profil/<?= $_SESSION['foto_profil'] ?? 'default.png' ?>" class="rounded-circle me-4" style="width:80px;height:80px;object-fit:cover;border:3px solid rgba(255,255,255,0.5);" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama']) ?>&background=4361ee&color=fff&size=80'">
            <div>
                <h4 class="fw-bold mb-1">Halo, <?= htmlspecialchars($_SESSION['nama']) ?>!</h4>
                <p class="mb-0 opacity-75"><?= $_SESSION['jabatan'] ?? '-' ?> &bull; <?= $_SESSION['divisi'] ?? '-' ?> &bull; NIP: <?= $_SESSION['nip'] ?? '-' ?></p>
                <p class="mb-0 mt-1"><i class="fas fa-clock me-1"></i> <span id="jamDigital" class="fw-bold"></span> &bull; <?= date('l, d F Y') ?></p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <a href="absen_masuk.php" class="quick-action" style="background:#e8f5e9">
                <i class="fas fa-sign-in-alt" style="color:#198754"></i><span>Absen Masuk</span>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="absen_pulang.php" class="quick-action" style="background:#fff3e0">
                <i class="fas fa-sign-out-alt" style="color:#ff9800"></i><span>Absen Pulang</span>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="izin.php?action=add" class="quick-action" style="background:#eef1ff">
                <i class="fas fa-envelope" style="color:#4361ee"></i><span>Ajukan Izin</span>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="riwayat.php" class="quick-action" style="background:#fce4ec">
                <i class="fas fa-history" style="color:#dc3545"></i><span>Riwayat</span>
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Status Hari Ini -->
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">Status Hari Ini</div>
                <div class="card-body">
                    <?php if($absen): ?>
                        <p class="mb-2"><strong>Jam Masuk:</strong> <?= $absen['jam_masuk'] ?: '-' ?></p>
                        <p class="mb-2"><strong>Jam Pulang:</strong> <?= $absen['jam_pulang'] ?: '-' ?></p>
                        <p class="mb-0"><strong>Status:</strong> <span class="badge bg-<?= $absen['status']=='Hadir'?'success':($absen['status']=='Terlambat'?'warning':'secondary') ?>"><?= $absen['status'] ?></span></p>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-info-circle fa-2x text-warning mb-2"></i>
                            <p class="text-muted mb-2">Anda belum absen hari ini</p>
                            <a href="absen_masuk.php" class="btn btn-primary btn-sm">Absen Masuk Sekarang</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Statistik Bulan Ini -->
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">Statistik Bulan Ini (<?= date('F Y') ?>)</div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <h3 class="fw-bold text-success"><?= $hadir ?></h3><p class="small text-muted mb-0">Hadir</p>
                        </div>
                        <div class="col-3">
                            <h3 class="fw-bold text-warning"><?= $telat ?></h3><p class="small text-muted mb-0">Terlambat</p>
                        </div>
                        <div class="col-3">
                            <h3 class="fw-bold text-info"><?= $izin_count + $cuti_count ?></h3><p class="small text-muted mb-0">Izin/Cuti</p>
                        </div>
                        <div class="col-3">
                            <h3 class="fw-bold text-danger"><?= $alpha_count ?></h3><p class="small text-muted mb-0">Alpha</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Riwayat 5 Terakhir -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">5 Absensi Terakhir</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>Tanggal</th><th>Masuk</th><th>Pulang</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php while($r = $riwayat->fetch_assoc()): ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($r['tanggal'])) ?></td>
                                <td><?= $r['jam_masuk'] ?: '-' ?></td>
                                <td><?= $r['jam_pulang'] ?: '-' ?></td>
                                <td><span class="badge bg-<?= $r['status']=='Hadir'?'success':($r['status']=='Terlambat'?'warning':'secondary') ?>"><?= $r['status'] ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Pengumuman -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><i class="fas fa-bullhorn me-2 text-danger"></i>Pengumuman</div>
                <div class="card-body">
                    <?php if($pengumuman->num_rows > 0): while($p = $pengumuman->fetch_assoc()): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($p['judul']) ?></h6>
                            <p class="small text-muted mb-1"><?= date('d M Y', strtotime($p['tanggal'])) ?></p>
                            <p class="small mb-0"><?= htmlspecialchars($p['isi']) ?></p>
                        </div>
                    <?php endwhile; else: ?>
                        <p class="text-muted text-center">Belum ada pengumuman.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'layout/footer.php'; ?>
