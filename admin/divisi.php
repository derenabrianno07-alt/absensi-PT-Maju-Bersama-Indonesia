<?php
// admin/divisi.php
require_once 'layout/header.php';

$action = $_GET['action'] ?? 'list';

// Handle Delete
if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Check if any employee is using this division
    $check = $conn->query("SELECT COUNT(*) as t FROM pegawai WHERE divisi_id = $id")->fetch_assoc()['t'];
    if ($check > 0) {
        set_alert('error', 'Gagal Hapus!', 'Divisi ini tidak bisa dihapus karena masih digunakan oleh pegawai.');
    } else {
        $conn->query("DELETE FROM divisi WHERE id = $id");
        set_alert('success', 'Berhasil!', 'Data divisi berhasil dihapus.');
    }
    header("Location: divisi.php");
    exit;
}

// Handle Insert/Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_divisi = sanitize($_POST['nama_divisi']);
    
    if (empty($nama_divisi)) {
        set_alert('warning', 'Peringatan!', 'Nama divisi wajib diisi.');
    } else {
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Update
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("UPDATE divisi SET nama_divisi = ? WHERE id = ?");
            $stmt->bind_param("si", $nama_divisi, $id);
            if ($stmt->execute()) {
                set_alert('success', 'Berhasil!', 'Data divisi berhasil diubah.');
            } else {
                set_alert('error', 'Gagal!', 'Terjadi kesalahan saat mengubah data.');
            }
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO divisi (nama_divisi) VALUES (?)");
            $stmt->bind_param("s", $nama_divisi);
            if ($stmt->execute()) {
                set_alert('success', 'Berhasil!', 'Data divisi berhasil ditambahkan.');
            } else {
                set_alert('error', 'Gagal!', 'Terjadi kesalahan saat menyimpan data.');
            }
        }
        header("Location: divisi.php");
        exit;
    }
}
?>

<div class="page-content">
    <?php if ($action == 'list'): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Data Divisi</h4>
                <p class="text-muted small mb-0">Kelola daftar divisi/departemen pegawai di perusahaan.</p>
            </div>
            <a href="?action=add" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tambah Divisi</a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="10%">No</th>
                                <th>Nama Divisi</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q = $conn->query("SELECT * FROM divisi ORDER BY id DESC");
                            $no = 1;
                            if ($q->num_rows > 0):
                                while ($row = $q->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong class="text-dark"><?= htmlspecialchars($row['nama_divisi']) ?></strong></td>
                                <td>
                                    <a href="?action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-1 text-white" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="?action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus divisi ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">Belum ada data divisi.</td>
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
            $res = $conn->query("SELECT * FROM divisi WHERE id = $id");
            $row = $res->fetch_assoc();
        }
    ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1"><?= $action == 'add' ? 'Tambah' : 'Edit' ?> Divisi</h4>
                <p class="text-muted small mb-0">Isi formulir di bawah ini untuk menyimpan data divisi.</p>
            </div>
            <a href="divisi.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        </div>
        
        <div class="card" style="max-width: 600px;">
            <div class="card-body">
                <form action="divisi.php" method="POST">
                    <?php if($row): ?>
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Nama Divisi</label>
                        <input type="text" name="nama_divisi" class="form-control" placeholder="Contoh: Human Resources Development (HRD)" value="<?= $row['nama_divisi'] ?? '' ?>" required autofocus>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'layout/footer.php'; ?>
