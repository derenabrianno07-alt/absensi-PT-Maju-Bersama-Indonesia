<?php
// pegawai/cuti.php
require_once 'layout/header.php';

$pegawai_id = $_SESSION['pegawai_id'];
$action = $_GET['action'] ?? 'list';

// Get current sisa cuti: query the latest record, if none default is 12.
$last_cuti = $conn->query("SELECT sisa_cuti FROM cuti WHERE pegawai_id = $pegawai_id ORDER BY id DESC LIMIT 1")->fetch_assoc();
$current_sisa_cuti = $last_cuti ? (int)$last_cuti['sisa_cuti'] : 12;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'add') {
    $tanggal_mulai = sanitize($_POST['tanggal_mulai']);
    $tanggal_selesai = sanitize($_POST['tanggal_selesai']);
    $jenis_cuti = sanitize($_POST['jenis_cuti']);
    $alasan = sanitize($_POST['alasan']);
    
    // Calculate requested days
    $start = new DateTime($tanggal_mulai);
    $end = new DateTime($tanggal_selesai);
    $diff = $start->diff($end)->format("%a") + 1; // Number of days of cuti requested
    
    if ($diff > $current_sisa_cuti) {
        set_alert('warning', 'Kuota Cuti Kurang!', 'Jumlah hari cuti yang diajukan (' . $diff . ' hari) melebihi sisa cuti Anda (' . $current_sisa_cuti . ' hari).');
        header("Location: cuti.php?action=add");
        exit;
    }
    
    // Insert with the current sisa_cuti (before approval, sisa cuti is not decremented yet, it will decrement on admin approval)
    $stmt = $conn->prepare("INSERT INTO cuti (pegawai_id, tanggal_mulai, tanggal_selesai, jenis_cuti, alasan, sisa_cuti, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("issssi", $pegawai_id, $tanggal_mulai, $tanggal_selesai, $jenis_cuti, $alasan, $current_sisa_cuti);
    
    if ($stmt->execute()) {
        set_alert('success', 'Berhasil!', 'Pengajuan cuti berhasil dikirim dan menunggu persetujuan Admin.');
    } else {
        set_alert('error', 'Gagal!', 'Terjadi kesalahan saat menyimpan pengajuan.');
    }
    header("Location: cuti.php");
    exit;
}
?>

<div class="page-content">
    <?php if ($action == 'list'): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Pengajuan Cuti</h4>
                <p class="text-muted small mb-0">Kelola cuti tahunan dan khusus Anda.</p>
            </div>
            <a href="?action=add" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Ajukan Cuti</a>
        </div>
        
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white border-none p-3 h-100">
                    <h6 class="opacity-75 text-uppercase fw-semibold" style="font-size: 0.75rem;">Sisa Jatah Cuti Anda</h6>
                    <h1 class="fw-bold mb-0"><?= $current_sisa_cuti ?></h1>
                    <span class="small opacity-75">Hari tersisa di tahun ini</span>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-white"><h5 class="mb-0 fw-semibold">Riwayat Pengajuan Cuti</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Durasi</th>
                                <th>Jenis Cuti</th>
                                <th>Alasan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q = $conn->query("SELECT * FROM cuti WHERE pegawai_id = $pegawai_id ORDER BY id DESC");
                            $no = 1;
                            if ($q->num_rows > 0):
                                while ($row = $q->fetch_assoc()):
                                    $start = new DateTime($row['tanggal_mulai']);
                                    $end = new DateTime($row['tanggal_selesai']);
                                    $durasi = $start->diff($end)->format("%a") + 1;
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong class="text-dark"><?= tanggal_indo($row['tanggal_mulai']) ?></strong></td>
                                <td><strong class="text-dark"><?= tanggal_indo($row['tanggal_selesai']) ?></strong></td>
                                <td><span class="badge bg-light text-dark border"><?= $durasi ?> Hari</span></td>
                                <td><?= htmlspecialchars($row['jenis_cuti']) ?></td>
                                <td><?= htmlspecialchars($row['alasan']) ?></td>
                                <td>
                                    <?php
                                    $bg = 'warning';
                                    if ($row['status'] == 'Disetujui') $bg = 'success';
                                    else if ($row['status'] == 'Ditolak') $bg = 'danger';
                                    ?>
                                    <span class="badge bg-<?= $bg ?>"><?= $row['status'] ?></span>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada riwayat pengajuan cuti.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    
    <?php elseif ($action == 'add'): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Form Pengajuan Cuti</h4>
                <p class="text-muted small mb-0">Ajukan permohonan cuti Anda kepada manajemen.</p>
            </div>
            <a href="cuti.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        </div>
        
        <div class="card" style="max-width: 700px;">
            <div class="card-body">
                <form action="?action=add" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Tanggal Mulai Cuti</label>
                            <input type="date" name="tanggal_mulai" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Tanggal Selesai Cuti</label>
                            <input type="date" name="tanggal_selesai" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jenis Cuti</label>
                        <select name="jenis_cuti" class="form-select" required>
                            <option value="Cuti Tahunan">Cuti Tahunan</option>
                            <option value="Cuti Sakit Panjang">Cuti Sakit Panjang</option>
                            <option value="Cuti Melahirkan">Cuti Melahirkan</option>
                            <option value="Cuti Menikah">Cuti Menikah</option>
                            <option value="Cuti Keagamaan">Cuti Keagamaan</option>
                            <option value="Cuti Diluar Tanggungan">Cuti Di Luar Tanggungan</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Alasan</label>
                        <textarea name="alasan" class="form-control" rows="4" placeholder="Jelaskan alasan pengajuan cuti secara rinci..." required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Kirim Pengajuan Cuti</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'layout/footer.php'; ?>
