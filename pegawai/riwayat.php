<?php
// pegawai/riwayat.php
require_once 'layout/header.php';

$pegawai_id = $_SESSION['pegawai_id'];

// Filter variables
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Query
$query = "SELECT * FROM absensi WHERE pegawai_id = $pegawai_id AND MONTH(tanggal) = '$bulan' AND YEAR(tanggal) = '$tahun' ORDER BY tanggal DESC";
$result = $conn->query($query);
?>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Riwayat Absensi</h4>
            <p class="text-muted small mb-0">Rekap kehadiran Anda berdasarkan bulan dan tahun.</p>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-body">
            <form action="riwayat.php" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted">Bulan</label>
                    <select name="bulan" class="form-select">
                        <?php
                        $months = [
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                        ];
                        foreach ($months as $num => $name) {
                            $selected = ($bulan == $num) ? 'selected' : '';
                            echo "<option value='$num' $selected>$name</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted">Tahun</label>
                    <select name="tahun" class="form-select">
                        <?php
                        $current_year = date('Y');
                        for ($y = $current_year; $y >= $current_year - 5; $y--) {
                            $selected = ($tahun == $y) ? 'selected' : '';
                            echo "<option value='$y' $selected>$y</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Tampilkan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result->num_rows > 0): ?>
                            <?php $no=1; while($row = $result->fetch_assoc()): 
                                $bg = 'danger';
                                if ($row['status'] == 'Hadir') $bg = 'success';
                                elseif ($row['status'] == 'Terlambat') $bg = 'warning';
                                elseif ($row['status'] == 'Izin') $bg = 'info';
                                elseif ($row['status'] == 'Cuti') $bg = 'primary';
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong class="text-dark"><?= tanggal_indo($row['tanggal'], true) ?></strong></td>
                                <td><?= $row['jam_masuk'] ? format_jam($row['jam_masuk']) : '-' ?></td>
                                <td><?= $row['jam_pulang'] ? format_jam($row['jam_pulang']) : '-' ?></td>
                                <td><span class="badge bg-<?= $bg ?>"><?= $row['status'] ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Tidak ada data absensi pada periode ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>
