<?php
// pegawai/izin.php
require_once 'layout/header.php';

$pegawai_id = $_SESSION['pegawai_id'];
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'add') {
    $tanggal_mulai = sanitize($_POST['tanggal_mulai']);
    $tanggal_selesai = sanitize($_POST['tanggal_selesai']);
    $jenis = sanitize($_POST['jenis']);
    $alasan = sanitize($_POST['alasan']);
    
    // Upload surat (opsional / wajib untuk sakit)
    $file_surat = '';
    if (isset($_FILES['file_surat']) && $_FILES['file_surat']['error'] == 0) {
        $ext = pathinfo($_FILES['file_surat']['name'], PATHINFO_EXTENSION);
        $file_surat = 'surat_' . $pegawai_id . '_' . time() . '.' . $ext;
        if (!is_dir('../uploads/izin')) {
            mkdir('../uploads/izin', 0777, true);
        }
        move_uploaded_file($_FILES['file_surat']['tmp_name'], '../uploads/izin/' . $file_surat);
    }
    
    $stmt = $conn->prepare("INSERT INTO izin (pegawai_id, tanggal_mulai, tanggal_selesai, jenis, alasan, file_surat, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("issssss", $pegawai_id, $tanggal_mulai, $tanggal_selesai, $jenis, $alasan, $file_surat);
    
    if ($stmt->execute()) {
        set_alert('success', 'Berhasil!', 'Pengajuan izin berhasil dikirim.');
    } else {
        set_alert('error', 'Gagal!', 'Terjadi kesalahan saat menyimpan pengajuan.');
    }
    header("Location: izin.php");
    exit;
}
?>

<div class="page-content">
    <?php if ($action == 'list'): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Riwayat Izin & Sakit</h4>
                <p class="text-muted small mb-0">Lihat status pengajuan izin dan sakit Anda.</p>
            </div>
            <a href="?action=add" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Ajukan Izin / Sakit</a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Jenis</th>
                                <th>Alasan</th>
                                <th>Surat Bukti</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q = $conn->query("SELECT * FROM izin WHERE pegawai_id = $pegawai_id ORDER BY id DESC");
                            $no = 1;
                            if ($q->num_rows > 0):
                                while ($row = $q->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong class="text-dark"><?= tanggal_indo($row['tanggal_mulai']) ?></strong></td>
                                <td><strong class="text-dark"><?= tanggal_indo($row['tanggal_selesai']) ?></strong></td>
                                <td>
                                    <span class="badge bg-<?= $row['jenis'] == 'Sakit' ? 'danger-light text-danger' : 'info-light text-info' ?>">
                                        <?= $row['jenis'] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($row['alasan']) ?></td>
                                <td>
                                    <?php if($row['file_surat']): ?>
                                        <a href="../uploads/izin/<?= $row['file_surat'] ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-download me-1"></i> Lihat Surat</a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
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
                                <td colspan="7" class="text-center text-muted py-4">Belum ada riwayat pengajuan izin/sakit.</td>
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
                <h4 class="fw-bold mb-1">Form Pengajuan Izin / Sakit</h4>
                <p class="text-muted small mb-0">Isi detail ketidakhadiran Anda untuk disetujui Admin.</p>
            </div>
            <a href="izin.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        </div>
        
        <div class="card" style="max-width: 700px;">
            <div class="card-body">
                <form action="?action=add" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jenis Pengajuan</label>
                        <select name="jenis" class="form-select" required>
                            <option value="Izin">Izin (Keperluan Mendesak)</option>
                            <option value="Sakit">Sakit (Memerlukan Istirahat)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alasan</label>
                        <textarea name="alasan" class="form-control" rows="4" placeholder="Jelaskan alasan pengajuan secara singkat dan jelas..." required></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Upload Surat Keterangan / Bukti (Format PDF/JPG/PNG)</label>
                        <input type="file" name="file_surat" class="form-control" accept=".pdf,image/*">
                        <small class="text-muted">Disarankan mengunggah surat keterangan dokter jika mengajukan Sakit.</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Kirim Pengajuan</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'layout/footer.php'; ?>
