<?php
// admin/pengumuman.php
require_once 'layout/header.php';

$action = $_GET['action'] ?? 'list';

// Handle Delete
if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM pengumuman WHERE id = $id");
    set_alert('success', 'Berhasil!', 'Pengumuman berhasil dihapus.');
    header("Location: pengumuman.php");
    exit;
}

// Handle Insert/Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = sanitize($_POST['judul']);
    $isi = sanitize($_POST['isi']);
    $tanggal = sanitize($_POST['tanggal']);
    
    if (empty($judul) || empty($isi) || empty($tanggal)) {
        set_alert('warning', 'Peringatan!', 'Semua kolom formulir pengumuman wajib diisi.');
    } else {
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Update
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("UPDATE pengumuman SET judul=?, isi=?, tanggal=? WHERE id=?");
            $stmt->bind_param("sssi", $judul, $isi, $tanggal, $id);
            if ($stmt->execute()) {
                set_alert('success', 'Berhasil!', 'Pengumuman berhasil diubah.');
            } else {
                set_alert('error', 'Gagal!', 'Terjadi kesalahan saat mengubah data.');
            }
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO pengumuman (judul, isi, tanggal) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $judul, $isi, $tanggal);
            if ($stmt->execute()) {
                set_alert('success', 'Berhasil!', 'Pengumuman berhasil ditambahkan.');
            } else {
                set_alert('error', 'Gagal!', 'Terjadi kesalahan saat menyimpan data.');
            }
        }
        header("Location: pengumuman.php");
        exit;
    }
}
?>

<div class="page-content">
    <?php if ($action == 'list'): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Pengumuman Perusahaan</h4>
                <p class="text-muted small mb-0">Kelola pengumuman penting bagi seluruh pegawai.</p>
            </div>
            <a href="?action=add" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tambah Pengumuman</a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="10%">No</th>
                                <th>Judul Pengumuman</th>
                                <th>Isi Pengumuman</th>
                                <th>Tanggal Publikasi</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q = $conn->query("SELECT * FROM pengumuman ORDER BY tanggal DESC, id DESC");
                            $no = 1;
                            if ($q->num_rows > 0):
                                while ($row = $q->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong class="text-dark"><?= htmlspecialchars($row['judul']) ?></strong></td>
                                <td><span class="text-muted small"><?= nl2br(htmlspecialchars($row['isi'])) ?></span></td>
                                <td><?= tanggal_indo($row['tanggal']) ?></td>
                                <td>
                                    <a href="?action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-1 text-white" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="?action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus pengumuman ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Belum ada pengumuman terdaftar.</td>
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
            $res = $conn->query("SELECT * FROM pengumuman WHERE id = $id");
            $row = $res->fetch_assoc();
        }
    ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1"><?= $action == 'add' ? 'Tambah' : 'Edit' ?> Pengumuman</h4>
                <p class="text-muted small mb-0">Isi formulir untuk menyebarkan pengumuman kepada pegawai.</p>
            </div>
            <a href="pengumuman.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        </div>
        
        <div class="card" style="max-width: 800px;">
            <div class="card-body">
                <form action="pengumuman.php" method="POST">
                    <?php if($row): ?>
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Judul Pengumuman</label>
                        <input type="text" name="judul" class="form-control" placeholder="Contoh: Pengumuman Libur Hari Raya" value="<?= $row['judul'] ?? '' ?>" required autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Isi Pengumuman</label>
                        <textarea name="isi" class="form-control" rows="6" placeholder="Tulis isi pengumuman lengkap..." required><?= $row['isi'] ?? '' ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Tanggal Publikasi</label>
                        <input type="date" name="tanggal" class="form-control" value="<?= $row['tanggal'] ?? date('Y-m-d') ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'layout/footer.php'; ?>
