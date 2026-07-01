<?php
// admin/absensi.php
require_once 'layout/header.php';

// Filter
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');
$search = $_GET['search'] ?? '';

$where_clause = "MONTH(a.tanggal) = '$bulan' AND YEAR(a.tanggal) = '$tahun'";
if (!empty($search)) {
    $where_clause .= " AND p.nama LIKE '%$search%'";
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Find photo if exists
    $abs_res = $conn->query("SELECT foto_masuk, foto_pulang FROM absensi WHERE id = $id");
    if ($abs_res->num_rows > 0) {
        $abs = $abs_res->fetch_assoc();
        if ($abs['foto_masuk']) @unlink('../uploads/absen/' . $abs['foto_masuk']);
        if ($abs['foto_pulang']) @unlink('../uploads/absen/' . $abs['foto_pulang']);
    }
    
    $conn->query("DELETE FROM absensi WHERE id = $id");
    set_alert('success', 'Berhasil!', 'Data absensi berhasil dihapus.');
    header("Location: absensi.php");
    exit;
}
?>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Data Absensi Pegawai</h4>
            <p class="text-muted small mb-0">Kelola dan tinjau seluruh riwayat kehadiran pegawai.</p>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-body">
            <form action="absensi.php" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Cari Nama Pegawai</label>
                    <input type="text" name="search" class="form-control" placeholder="Nama..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
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
                <div class="col-md-3">
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
                <div class="col-md-3 d-flex align-items-end">
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
                            <th>Nama Pegawai</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Foto Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Foto Pulang</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT a.*, p.nama FROM absensi a JOIN pegawai p ON a.pegawai_id = p.id WHERE $where_clause ORDER BY a.tanggal DESC, a.id DESC";
                        $result = $conn->query($query);
                        if($result->num_rows > 0): 
                            $no=1; while($row = $result->fetch_assoc()): 
                                $bg = 'danger';
                                if ($row['status'] == 'Hadir') $bg = 'success';
                                elseif ($row['status'] == 'Terlambat') $bg = 'warning';
                                elseif ($row['status'] == 'Izin') $bg = 'info';
                                elseif ($row['status'] == 'Cuti') $bg = 'primary';
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong class="text-dark"><?= htmlspecialchars($row['nama']) ?></strong></td>
                                <td><?= tanggal_indo($row['tanggal']) ?></td>
                                <td><?= $row['jam_masuk'] ? format_jam($row['jam_masuk']) : '-' ?></td>
                                <td>
                                    <?php if($row['foto_masuk']): ?>
                                        <a href="../uploads/absen/<?= $row['foto_masuk'] ?>" target="_blank">
                                            <img src="../uploads/absen/<?= $row['foto_masuk'] ?>" class="rounded border" style="width: 50px; height: 38px; object-fit: cover;">
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $row['jam_pulang'] ? format_jam($row['jam_pulang']) : '-' ?></td>
                                <td>
                                    <?php if($row['foto_pulang']): ?>
                                        <a href="../uploads/absen/<?= $row['foto_pulang'] ?>" target="_blank">
                                            <img src="../uploads/absen/<?= $row['foto_pulang'] ?>" class="rounded border" style="width: 50px; height: 38px; object-fit: cover;">
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($row['latitude_masuk'] && $row['longitude_masuk']): ?>
                                        <a href="https://www.google.com/maps?q=<?= $row['latitude_masuk'] ?>,<?= $row['longitude_masuk'] ?>" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-1 mb-1 d-block text-nowrap" style="font-size: 0.75rem;" title="Lokasi Masuk">
                                            <i class="fas fa-map-marker-alt text-danger me-1"></i> Masuk
                                        </a>
                                    <?php endif; ?>
                                    <?php if($row['latitude_pulang'] && $row['longitude_pulang']): ?>
                                        <a href="https://www.google.com/maps?q=<?= $row['latitude_pulang'] ?>,<?= $row['longitude_pulang'] ?>" target="_blank" class="btn btn-xs btn-outline-success py-0 px-1 d-block text-nowrap" style="font-size: 0.75rem;" title="Lokasi Pulang">
                                            <i class="fas fa-map-marker-alt text-danger me-1"></i> Pulang
                                        </a>
                                    <?php endif; ?>
                                    <?php if(!$row['latitude_masuk'] && !$row['latitude_pulang']): ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $bg ?>"><?= $row['status'] ?></span>
                                </td>
                                <td>
                                    <a href="?action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data absensi ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">Tidak ada data absensi pada periode ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>
