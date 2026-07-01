<?php
// admin/jabatan.php
require_once 'layout/header.php';

$action = $_GET['action'] ?? 'list';

// Handle Delete
if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Check if any employee is using this position
    $check = $conn->query("SELECT COUNT(*) as t FROM pegawai WHERE jabatan_id = $id")->fetch_assoc()['t'];
    if ($check > 0) {
        set_alert('error', 'Gagal Hapus!', 'Jabatan ini tidak bisa dihapus karena masih digunakan oleh pegawai.');
    } else {
        $conn->query("DELETE FROM jabatan WHERE id = $id");
        set_alert('success', 'Berhasil!', 'Data jabatan berhasil dihapus.');
    }
    header("Location: jabatan.php");
    exit;
}

// Handle Insert/Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_jabatan = sanitize($_POST['nama_jabatan']);
    
    if (empty($nama_jabatan)) {
        set_alert('warning', 'Peringatan!', 'Nama jabatan wajib diisi.');
    } else {
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Update
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("UPDATE jabatan SET nama_jabatan = ? WHERE id = ?");
            $stmt->bind_param("si", $nama_jabatan, $id);
            if ($stmt->execute()) {
                set_alert('success', 'Berhasil!', 'Data jabatan berhasil diubah.');
            } else {
                set_alert('error', 'Gagal!', 'Terjadi kesalahan saat mengubah data.');
            }
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO jabatan (nama_jabatan) VALUES (?)");
            $stmt->bind_param("s", $nama_jabatan);
            if ($stmt->execute()) {
                set_alert('success', 'Berhasil!', 'Data jabatan berhasil ditambahkan.');
            } else {
                set_alert('error', 'Gagal!', 'Terjadi kesalahan saat menyimpan data.');
            }
        }
        header("Location: jabatan.php");
        exit;
    }
}
?>

<div class="page-content">
    <?php if ($action == 'list'): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Data Jabatan</h4>
                <p class="text-muted small mb-0">Kelola daftar jabatan/posisi pegawai di perusahaan.</p>
            </div>
            <a href="?action=add" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tambah Jabatan</a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="10%">No</th>
                                <th>Nama Jabatan</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q = $conn->query("SELECT * FROM jabatan ORDER BY id DESC");
                            $no = 1;
                            if ($q->num_rows > 0):
                                while ($row = $q->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong class="text-dark"><?= htmlspecialchars($row['nama_jabatan']) ?></strong></td>
                                <td>
                                    <a href="?action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-1 text-white" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="?action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus jabatan ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">Belum ada data jabatan.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    
    <?php elseif ($action == 'add' || $action == 'edit'): 
        $row = null;
        if ($action == 'edit' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $res = $conn->query("SELECT * FROM jabatan WHERE id = $id");
            $row = $res->fetch_assoc();
        }
    ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1"><?= $action == 'add' ? 'Tambah' : 'Edit' ?> Jabatan</h4>
                <p class="text-muted small mb-0">Isi formulir di bawah ini untuk menyimpan data jabatan.</p>
            </div>
            <a href="jabatan.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        </div>
        
        <div class="card" style="max-width: 600px;">
            <div class="card-body">
                <form action="jabatan.php" method="POST">
                    <?php if($row): ?>
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Nama Jabatan</label>
                        <input type="text" name="nama_jabatan" class="form-control" placeholder="Contoh: Manager IT" value="<?= $row['nama_jabatan'] ?? '' ?>" required autofocus>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'layout/footer.php'; ?>
