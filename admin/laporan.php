<?php
// admin/laporan.php
require_once 'layout/header.php';

$action = $_GET['action'] ?? 'form';

if ($action == 'print') {
    $jenis_laporan = sanitize($_GET['jenis_laporan'] ?? 'bulanan');
    $bulan = sanitize($_GET['bulan'] ?? date('m'));
    $tahun = sanitize($_GET['tahun'] ?? date('Y'));
    $tanggal = sanitize($_GET['tanggal'] ?? date('Y-m-d'));
    $tanggal_mulai = sanitize($_GET['tanggal_mulai'] ?? date('Y-m-d'));
    $tanggal_selesai = sanitize($_GET['tanggal_selesai'] ?? date('Y-m-d'));
    
    // Clear output buffer for printing
    ob_end_clean();
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Cetak Laporan Kehadiran Pegawai</title>
        <style>
            body { font-family: 'Arial', sans-serif; color: #333; margin: 30px; }
            .header { text-align: center; margin-bottom: 30px; }
            .header h2 { margin: 0 0 5px 0; color: #111; }
            .header h4 { margin: 0 0 5px 0; font-weight: normal; color: #666; }
            .header p { margin: 0; font-size: 0.85rem; color: #888; }
            hr { border: 0; border-top: 2px solid #333; margin-bottom: 20px; }
            .meta-info { margin-bottom: 20px; font-size: 0.95rem; }
            table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.9rem; }
            table, th, td { border: 1px solid #aaa; }
            th { padding: 10px 8px; background-color: #f2f2f2; font-weight: bold; text-align: center; }
            td { padding: 8px; text-align: left; }
            .text-center { text-align: center; }
            .badge-hadir { color: #198754; font-weight: bold; }
            .badge-terlambat { color: #ffc107; font-weight: bold; }
            .badge-izin { color: #0dcaf0; font-weight: bold; }
            .badge-cuti { color: #6f42c1; font-weight: bold; }
            .badge-alpha { color: #dc3545; font-weight: bold; }
            .ttd-box { width: 250px; float: right; margin-top: 40px; text-align: center; font-size: 0.95rem; }
            .ttd-space { height: 75px; }
        </style>
    </head>
    <body onload="window.print()">
        <div class="header">
            <h2><?= strtoupper(NAMA_PERUSAHAAN) ?></h2>
            <h4><?= NAMA_SISTEM ?></h4>
            <p>Laporan Kehadiran Pegawai Secara <?= ucfirst($jenis_laporan) ?></p>
        </div>
        <hr>
        
        <div class="meta-info">
            <?php if ($jenis_laporan == 'harian'): ?>
                <strong>Jenis Laporan:</strong> Laporan Harian<br>
                <strong>Tanggal:</strong> <?= tanggal_indo($tanggal, true) ?>
            <?php elseif ($jenis_laporan == 'mingguan'): ?>
                <strong>Jenis Laporan:</strong> Laporan Mingguan / Rentang Tanggal<br>
                <strong>Periode:</strong> <?= tanggal_indo($tanggal_mulai) ?> s/d <?= tanggal_indo($tanggal_selesai) ?>
            <?php else: ?>
                <strong>Jenis Laporan:</strong> Laporan Bulanan<br>
                <strong>Bulan / Tahun:</strong> <?= $bulan ?> / <?= $tahun ?>
            <?php endif; ?>
        </div>
        
        <?php if ($jenis_laporan == 'harian'): ?>
            <!-- Daily Report Table -->
            <table>
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>NIP</th>
                        <th>Nama Pegawai</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch all active employees
                    $peg_q = $conn->query("SELECT p.id, p.nip, p.nama FROM pegawai p ORDER BY p.nama ASC");
                    $no = 1;
                    while ($p = $peg_q->fetch_assoc()) {
                        $p_id = $p['id'];
                        // Get attendance record for this day
                        $abs_q = $conn->query("SELECT * FROM absensi WHERE pegawai_id = $p_id AND tanggal = '$tanggal'");
                        $abs = $abs_q->fetch_assoc();
                        
                        $jam_masuk = $abs ? ($abs['jam_masuk'] ?: '-') : '-';
                        $jam_pulang = $abs ? ($abs['jam_pulang'] ?: '-') : '-';
                        $status = $abs ? $abs['status'] : 'Alpha';
                        
                        $status_class = 'badge-alpha';
                        if ($status == 'Hadir') $status_class = 'badge-hadir';
                        elseif ($status == 'Terlambat') $status_class = 'badge-terlambat';
                        elseif ($status == 'Izin') $status_class = 'badge-izin';
                        elseif ($status == 'Cuti') $status_class = 'badge-cuti';
                        ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td class="text-center"><?= htmlspecialchars($p['nip']) ?></td>
                            <td><?= htmlspecialchars($p['nama']) ?></td>
                            <td class="text-center"><?= $jam_masuk ?></td>
                            <td class="text-center"><?= $jam_pulang ?></td>
                            <td class="text-center <span class='<?= $status_class ?>'><?= $status_class ?></span>"><strong><?= $status ?></strong></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        <?php else: 
            // Weekly / Monthly Recap Table
            ?>
            <table>
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>NIP</th>
                        <th>Nama Pegawai</th>
                        <th width="10%">Hadir</th>
                        <th width="10%">Terlambat</th>
                        <th width="10%">Izin</th>
                        <th width="10%">Cuti</th>
                        <th width="10%">Alpha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Set period variables
                    if ($jenis_laporan == 'mingguan') {
                        $start_date = $tanggal_mulai;
                        $end_date = $tanggal_selesai;
                        
                        $start_dt = new DateTime($start_date);
                        $end_dt = new DateTime($end_date);
                        $total_days = $start_dt->diff($end_dt)->format("%a") + 1;
                    } else {
                        // Monthly
                        $start_date = "$tahun-$bulan-01";
                        $end_date = date("Y-m-t", strtotime($start_date));
                        $total_days = (int)date("t", strtotime($start_date));
                    }
                    
                    // Fetch all active employees
                    $peg_q = $conn->query("SELECT p.id, p.nip, p.nama FROM pegawai p ORDER BY p.nama ASC");
                    $no = 1;
                    while ($p = $peg_q->fetch_assoc()) {
                        $p_id = $p['id'];
                        
                        // Count statuses
                        $cnt_hadir = $conn->query("SELECT COUNT(*) as t FROM absensi WHERE pegawai_id=$p_id AND tanggal BETWEEN '$start_date' AND '$end_date' AND status='Hadir'")->fetch_assoc()['t'];
                        $cnt_telat = $conn->query("SELECT COUNT(*) as t FROM absensi WHERE pegawai_id=$p_id AND tanggal BETWEEN '$start_date' AND '$end_date' AND status='Terlambat'")->fetch_assoc()['t'];
                        $cnt_izin = $conn->query("SELECT COUNT(*) as t FROM absensi WHERE pegawai_id=$p_id AND tanggal BETWEEN '$start_date' AND '$end_date' AND status='Izin'")->fetch_assoc()['t'];
                        $cnt_cuti = $conn->query("SELECT COUNT(*) as t FROM absensi WHERE pegawai_id=$p_id AND tanggal BETWEEN '$start_date' AND '$end_date' AND status='Cuti'")->fetch_assoc()['t'];
                        
                        $present_days = $conn->query("SELECT COUNT(*) as t FROM absensi WHERE pegawai_id=$p_id AND tanggal BETWEEN '$start_date' AND '$end_date'")->fetch_assoc()['t'];
                        $cnt_alpha = max(0, $total_days - $present_days);
                        ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td class="text-center"><?= htmlspecialchars($p['nip']) ?></td>
                            <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
                            <td class="text-center badge-hadir"><?= $cnt_hadir ?></td>
                            <td class="text-center badge-terlambat"><?= $cnt_telat ?></td>
                            <td class="text-center badge-izin"><?= $cnt_izin ?></td>
                            <td class="text-center badge-cuti"><?= $cnt_cuti ?></td>
                            <td class="text-center badge-alpha"><?= $cnt_alpha ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <div class="ttd-box">
            <p>Jakarta, <?= date('d M Y') ?></p>
            <p><strong>Admin / HRD</strong></p>
            <div class="ttd-space"></div>
            <p>___________________</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Cetak Laporan Kehadiran</h4>
            <p class="text-muted small mb-0">Hasilkan file cetak/PDF rekap absensi harian, mingguan, maupun bulanan.</p>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Daily Form -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-white"><h6 class="mb-0 fw-semibold text-primary"><i class="fas fa-calendar-day me-2"></i>Laporan Harian</h6></div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <p class="text-muted small">Mencetak daftar status kehadiran seluruh pegawai pada satu tanggal spesifik.</p>
                    <form action="laporan.php" method="GET" target="_blank">
                        <input type="hidden" name="action" value="print">
                        <input type="hidden" name="jenis_laporan" value="harian">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Pilih Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-print me-1"></i> Cetak Harian</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Weekly Form -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-white"><h6 class="mb-0 fw-semibold text-success"><i class="fas fa-calendar-week me-2"></i>Laporan Mingguan / Rentang</h6></div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <p class="text-muted small">Rekapitulasi jumlah status kehadiran pegawai dalam rentang tanggal tertentu.</p>
                    <form action="laporan.php" method="GET" target="_blank">
                        <input type="hidden" name="action" value="print">
                        <input type="hidden" name="jenis_laporan" value="mingguan">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="<?= date('Y-m-d', strtotime('-7 days')) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <button type="submit" class="btn btn-success text-white w-100"><i class="fas fa-print me-1"></i> Cetak Mingguan</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Monthly Form -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-white"><h6 class="mb-0 fw-semibold text-danger"><i class="fas fa-calendar-alt me-2"></i>Laporan Bulanan</h6></div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <p class="text-muted small">Rekapitulasi jumlah status kehadiran pegawai selama satu bulan penuh.</p>
                    <form action="laporan.php" method="GET" target="_blank">
                        <input type="hidden" name="action" value="print">
                        <input type="hidden" name="jenis_laporan" value="bulanan">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Bulan</label>
                                <select name="bulan" class="form-select">
                                    <?php
                                    $months = [
                                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                                        '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                                        '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                                        '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                    ];
                                    foreach ($months as $num => $name) {
                                        $selected = (date('m') == $num) ? 'selected' : '';
                                        echo "<option value='$num' $selected>$name</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Tahun</label>
                                <select name="tahun" class="form-select">
                                    <?php
                                    $current_year = date('Y');
                                    for ($y = $current_year; $y >= $current_year - 5; $y--) {
                                        echo "<option value='$y'>$y</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-danger w-100"><i class="fas fa-print me-1"></i> Cetak Bulanan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>
