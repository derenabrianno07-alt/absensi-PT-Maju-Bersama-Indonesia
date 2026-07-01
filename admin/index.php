<?php
require_once 'layout/header.php';
$today = date('Y-m-d');
$month = date('m');
$year = date('Y');

// Statistics
$total_pegawai = $conn->query("SELECT COUNT(*) as t FROM pegawai")->fetch_assoc()['t'];
$total_hadir = $conn->query("SELECT COUNT(*) as t FROM absensi WHERE tanggal='$today' AND status='Hadir'")->fetch_assoc()['t'];
$total_terlambat = $conn->query("SELECT COUNT(*) as t FROM absensi WHERE tanggal='$today' AND status='Terlambat'")->fetch_assoc()['t'];
$total_izin_today = $conn->query("SELECT COUNT(*) as t FROM izin WHERE status='Disetujui' AND tanggal_mulai <= '$today' AND tanggal_selesai >= '$today'")->fetch_assoc()['t'];
$total_cuti_today = $conn->query("SELECT COUNT(*) as t FROM cuti WHERE status='Disetujui' AND tanggal_mulai <= '$today' AND tanggal_selesai >= '$today'")->fetch_assoc()['t'];
$total_alpha = $total_pegawai - ($total_hadir + $total_terlambat + $total_izin_today + $total_cuti_today);
if ($total_alpha < 0) $total_alpha = 0;

// Weekly chart data (last 7 days)
$weekly_labels = [];
$weekly_hadir = [];
$weekly_terlambat = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $weekly_labels[] = date('d/m', strtotime($d));
    $weekly_hadir[] = $conn->query("SELECT COUNT(*) as t FROM absensi WHERE tanggal='$d' AND status='Hadir'")->fetch_assoc()['t'];
    $weekly_terlambat[] = $conn->query("SELECT COUNT(*) as t FROM absensi WHERE tanggal='$d' AND status='Terlambat'")->fetch_assoc()['t'];
}
?>
<div class="page-content">
    <h4 class="fw-bold mb-1">Dashboard Admin</h4>
    <p class="text-muted mb-4">Selamat datang di panel administrasi <?= NAMA_PERUSAHAAN ?></p>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-lg-2 col-md-4 col-6">
            <div class="stat-card">
                <div class="icon-box" style="background:#4361ee"><i class="fas fa-users"></i></div>
                <div class="stat-info"><h3><?= $total_pegawai ?></h3><p>Total Pegawai</p></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="stat-card">
                <div class="icon-box" style="background:#198754"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info"><h3><?= $total_hadir ?></h3><p>Hadir</p></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="stat-card">
                <div class="icon-box" style="background:#ffc107"><i class="fas fa-clock"></i></div>
                <div class="stat-info"><h3><?= $total_terlambat ?></h3><p>Terlambat</p></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="stat-card">
                <div class="icon-box" style="background:#0dcaf0"><i class="fas fa-envelope"></i></div>
                <div class="stat-info"><h3><?= $total_izin_today ?></h3><p>Izin</p></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="stat-card">
                <div class="icon-box" style="background:#6f42c1"><i class="fas fa-plane"></i></div>
                <div class="stat-info"><h3><?= $total_cuti_today ?></h3><p>Cuti</p></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="stat-card">
                <div class="icon-box" style="background:#dc3545"><i class="fas fa-times-circle"></i></div>
                <div class="stat-info"><h3><?= $total_alpha ?></h3><p>Alpha</p></div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Weekly Chart -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Grafik Kehadiran 7 Hari Terakhir</span>
                </div>
                <div class="card-body">
                    <canvas id="weeklyChart" height="110"></canvas>
                </div>
            </div>
        </div>
        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">Quick Action</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6"><a href="pegawai.php?action=add" class="quick-action" style="background:#eef1ff"><i class="fas fa-user-plus" style="color:#4361ee"></i><span>Tambah Pegawai</span></a></div>
                        <div class="col-6"><a href="user.php?action=add" class="quick-action" style="background:#e8f5e9"><i class="fas fa-key" style="color:#198754"></i><span>Tambah User</span></a></div>
                        <div class="col-6"><a href="laporan.php" class="quick-action" style="background:#fff3e0"><i class="fas fa-file-pdf" style="color:#ffc107"></i><span>Cetak Laporan</span></a></div>
                        <div class="col-6"><a href="pengumuman.php?action=add" class="quick-action" style="background:#fce4ec"><i class="fas fa-bullhorn" style="color:#dc3545"></i><span>Pengumuman</span></a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Absensi Hari Ini -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">Absensi Hari Ini (<?= date('d M Y') ?>)</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>Nama</th><th>Masuk</th><th>Pulang</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php
                            $q = $conn->query("SELECT a.*, p.nama FROM absensi a JOIN pegawai p ON a.pegawai_id=p.id WHERE a.tanggal='$today' ORDER BY a.id DESC LIMIT 10");
                            if ($q->num_rows > 0):
                                while($r = $q->fetch_assoc()): 
                                    $badge_color = 'danger';
                                    if ($r['status'] == 'Hadir') $badge_color = 'success';
                                    elseif ($r['status'] == 'Terlambat') $badge_color = 'warning';
                                    elseif ($r['status'] == 'Izin') $badge_color = 'info';
                                    elseif ($r['status'] == 'Cuti') $badge_color = 'primary';
                                    ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['nama']) ?></td>
                                    <td><?= $r['jam_masuk'] ?: '-' ?></td>
                                    <td><?= $r['jam_pulang'] ?: '-' ?></td>
                                    <td><span class="badge bg-<?= $badge_color ?>"><?= $r['status'] ?></span></td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="4" class="text-center text-muted">Belum ada data absensi hari ini</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Izin Terbaru -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">Pengajuan Izin Terbaru</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>Nama</th><th>Jenis</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php
                            $qi = $conn->query("SELECT i.*, p.nama FROM izin i JOIN pegawai p ON i.pegawai_id=p.id ORDER BY i.id DESC LIMIT 5");
                            if ($qi->num_rows > 0):
                                while($r = $qi->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['nama']) ?></td>
                                    <td><?= $r['jenis'] ?></td>
                                    <td><span class="badge bg-<?= $r['status']=='Pending'?'warning':($r['status']=='Disetujui'?'success':'danger') ?>"><?= $r['status'] ?></span></td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="3" class="text-center text-muted">Belum ada pengajuan</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('weeklyChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($weekly_labels) ?>,
        datasets: [
            { label: 'Hadir', data: <?= json_encode($weekly_hadir) ?>, backgroundColor: '#198754', borderRadius: 5 },
            { label: 'Terlambat', data: <?= json_encode($weekly_terlambat) ?>, backgroundColor: '#ffc107', borderRadius: 5 }
        ]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
});
</script>

<?php require_once 'layout/footer.php'; ?>
