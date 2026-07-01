<?php
// admin/izin.php
require_once 'layout/header.php';

// Handle Action (Approve/Reject)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = '';
    
    if ($_GET['action'] == 'approve') {
        $status = 'Disetujui';
    } else if ($_GET['action'] == 'reject') {
        $status = 'Ditolak';
    }
    
    if ($status != '') {
        $conn->query("UPDATE izin SET status = '$status' WHERE id = $id");
        
        // If approved, insert into absensi table for each date in range so they don't count as alpha
        if ($status == 'Disetujui') {
            // Get leave detail
            $izin_res = $conn->query("SELECT * FROM izin WHERE id = $id");
            if ($izin_res->num_rows > 0) {
                $izin = $izin_res->fetch_assoc();
                $peg_id = $izin['pegawai_id'];
                $start = new DateTime($izin['tanggal_mulai']);
                $end = new DateTime($izin['tanggal_selesai']);
                $end->modify('+1 day'); // Include end date
                
                $interval = new DateInterval('P1D');
                $daterange = new DatePeriod($start, $interval ,$end);
                
                foreach($daterange as $date){
                    $tgl = $date->format("Y-m-d");
                    // Check if already has record in absensi, if yes update status, if no insert
                    $check = $conn->query("SELECT id FROM absensi WHERE pegawai_id = $peg_id AND tanggal = '$tgl'");
                    if ($check->num_rows > 0) {
                        $conn->query("UPDATE absensi SET status = 'Izin' WHERE pegawai_id = $peg_id AND tanggal = '$tgl'");
                    } else {
                        $conn->query("INSERT INTO absensi (pegawai_id, tanggal, jam_masuk, jam_pulang, status) VALUES ($peg_id, '$tgl', NULL, NULL, 'Izin')");
                    }
                }
            }
        }
        
        set_alert('success', 'Berhasil!', 'Status pengajuan izin berhasil diubah menjadi ' . $status . '.');
        header("Location: izin.php");
        exit;
    }
}
?>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Data Pengajuan Izin & Sakit</h4>
            <p class="text-muted small mb-0">Kelola dan tinjau seluruh surat permohonan izin/sakit pegawai.</p>
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
                            <th>NIP</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Jenis</th>
                            <th>Alasan</th>
                            <th>Surat Bukti</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT i.*, p.nama, p.nip FROM izin i JOIN pegawai p ON i.pegawai_id = p.id ORDER BY i.id DESC";
                        $result = $conn->query($query);
                        if($result->num_rows > 0): 
                            $no=1; while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong class="text-dark"><?= htmlspecialchars($row['nama']) ?></strong></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['nip']) ?></span></td>
                                <td><?= tanggal_indo($row['tanggal_mulai']) ?></td>
                                <td><?= tanggal_indo($row['tanggal_selesai']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $row['jenis'] == 'Sakit' ? 'danger-light text-danger' : 'info-light text-info' ?>">
                                        <?= $row['jenis'] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($row['alasan']) ?></td>
                                <td>
                                    <?php if($row['file_surat']): ?>
                                        <a href="../uploads/izin/<?= $row['file_surat'] ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-download me-1"></i> Surat</a>
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
                                <td>
                                    <?php if($row['status'] == 'Pending'): ?>
                                        <a href="?action=approve&id=<?= $row['id'] ?>" class="btn btn-sm btn-success me-1" onclick="return confirm('Apakah Anda yakin menyetujui permohonan izin ini?')" title="Setujui"><i class="fas fa-check"></i></a>
                                        <a href="?action=reject&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin menolak permohonan izin ini?')" title="Tolak"><i class="fas fa-times"></i></a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">Belum ada pengajuan izin dari pegawai.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>
