<?php
// admin/cuti.php
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
        if ($status == 'Disetujui') {
            // Get leave detail
            $cuti_res = $conn->query("SELECT * FROM cuti WHERE id = $id");
            if ($cuti_res->num_rows > 0) {
                $cuti = $cuti_res->fetch_assoc();
                $peg_id = $cuti['pegawai_id'];
                
                // Calculate requested days
                $start = new DateTime($cuti['tanggal_mulai']);
                $end = new DateTime($cuti['tanggal_selesai']);
                $durasi = $start->diff($end)->format("%a") + 1; // Number of days requested
                
                // Get sisa cuti BEFORE this request (which was stored in $cuti['sisa_cuti'] when submitted)
                $current_sisa_cuti = (int)$cuti['sisa_cuti'];
                $new_sisa_cuti = max(0, $current_sisa_cuti - $durasi);
                
                // Update cuti table: status and sisa_cuti
                $conn->query("UPDATE cuti SET status = 'Disetujui', sisa_cuti = $new_sisa_cuti WHERE id = $id");
                
                // Add to absensi table for each date in range so they are marked as Cuti
                $end_mod = new DateTime($cuti['tanggal_selesai']);
                $end_mod->modify('+1 day');
                $interval = new DateInterval('P1D');
                $daterange = new DatePeriod($start, $interval ,$end_mod);
                
                foreach($daterange as $date){
                    $tgl = $date->format("Y-m-d");
                    // Check if already has record in absensi, if yes update status, if no insert
                    $check = $conn->query("SELECT id FROM absensi WHERE pegawai_id = $peg_id AND tanggal = '$tgl'");
                    if ($check->num_rows > 0) {
                        $conn->query("UPDATE absensi SET status = 'Cuti' WHERE pegawai_id = $peg_id AND tanggal = '$tgl'");
                    } else {
                        $conn->query("INSERT INTO absensi (pegawai_id, tanggal, jam_masuk, jam_pulang, status) VALUES ($peg_id, '$tgl', NULL, NULL, 'Cuti')");
                    }
                }
                
                set_alert('success', 'Berhasil!', 'Permohonan cuti disetujui. Sisa cuti pegawai menjadi ' . $new_sisa_cuti . ' hari.');
            }
        } else {
            // Rejected
            $conn->query("UPDATE cuti SET status = 'Ditolak' WHERE id = $id");
            set_alert('success', 'Berhasil!', 'Permohonan cuti ditolak.');
        }
        
        header("Location: cuti.php");
        exit;
    }
}
?>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Data Pengajuan Cuti</h4>
            <p class="text-muted small mb-0">Kelola dan tinjau seluruh permohonan cuti tahunan pegawai.</p>
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
                            <th>Durasi</th>
                            <th>Jenis Cuti</th>
                            <th>Alasan</th>
                            <th>Sisa Kuota Cuti</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT c.*, p.nama, p.nip FROM cuti c JOIN pegawai p ON c.pegawai_id = p.id ORDER BY c.id DESC";
                        $result = $conn->query($query);
                        if($result->num_rows > 0): 
                            $no=1; while($row = $result->fetch_assoc()): 
                                $start = new DateTime($row['tanggal_mulai']);
                                $end = new DateTime($row['tanggal_selesai']);
                                $durasi = $start->diff($end)->format("%a") + 1;
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong class="text-dark"><?= htmlspecialchars($row['nama']) ?></strong></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['nip']) ?></span></td>
                                <td><?= tanggal_indo($row['tanggal_mulai']) ?></td>
                                <td><?= tanggal_indo($row['tanggal_selesai']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= $durasi ?> Hari</span></td>
                                <td><?= htmlspecialchars($row['jenis_cuti']) ?></td>
                                <td><?= htmlspecialchars($row['alasan']) ?></td>
                                <td><span class="fw-bold text-primary"><?= $row['sisa_cuti'] ?> Hari</span></td>
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
                                        <a href="?action=approve&id=<?= $row['id'] ?>" class="btn btn-sm btn-success me-1" onclick="return confirm('Apakah Anda yakin menyetujui permohonan cuti ini?')" title="Setujui"><i class="fas fa-check"></i></a>
                                        <a href="?action=reject&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin menolak permohonan cuti ini?')" title="Tolak"><i class="fas fa-times"></i></a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">Belum ada pengajuan cuti dari pegawai.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>
