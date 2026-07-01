<?php
// admin/pegawai.php
require_once 'layout/header.php';

$action = $_GET['action'] ?? 'list';

// Handle Delete
if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Get profile photo to delete it if not default
    $p_stmt = $conn->query("SELECT foto_profil FROM pegawai WHERE id = $id");
    if ($p_stmt->num_rows > 0) {
        $p = $p_stmt->fetch_assoc();
        if ($p['foto_profil'] && $p['foto_profil'] !== 'default.png') {
            @unlink('../uploads/profil/' . $p['foto_profil']);
        }
    }
    
    $conn->query("DELETE FROM pegawai WHERE id = $id");
    set_alert('success', 'Berhasil!', 'Data pegawai berhasil dihapus.');
    header("Location: pegawai.php");
    exit;
}

// Handle Insert/Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nip = sanitize($_POST['nip']);
    $user_id = (int)$_POST['user_id'];
    $jabatan_id = !empty($_POST['jabatan_id']) ? (int)$_POST['jabatan_id'] : null;
    $divisi_id = !empty($_POST['divisi_id']) ? (int)$_POST['divisi_id'] : null;
    $nama = sanitize($_POST['nama']);
    $email = sanitize($_POST['email']);
    $no_hp = sanitize($_POST['no_hp']);
    
    // Check if NIP already exists (exclude current ID if editing)
    $check_nip_query = $id > 0 ? "SELECT COUNT(*) as t FROM pegawai WHERE nip = '$nip' AND id != $id" : "SELECT COUNT(*) as t FROM pegawai WHERE nip = '$nip'";
    $check_nip = $conn->query($check_nip_query)->fetch_assoc()['t'];
    
    if ($check_nip > 0) {
        set_alert('error', 'Gagal!', 'NIP ' . $nip . ' sudah terdaftar oleh pegawai lain.');
    } else {
        // Handle Photo Upload
        $foto_profil = 'default.png';
        if ($id > 0) {
            // Get current photo
            $foto_profil = $conn->query("SELECT foto_profil FROM pegawai WHERE id = $id")->fetch_assoc()['foto_profil'];
        }
        
        if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
            $ext = pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION);
            $new_name = 'profil_' . time() . '_' . rand(100, 999) . '.' . $ext;
            
            if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], '../uploads/profil/' . $new_name)) {
                // Delete old photo if not default
                if ($foto_profil && $foto_profil !== 'default.png') {
                    @unlink('../uploads/profil/' . $foto_profil);
                }
                $foto_profil = $new_name;
            }
        }
        
        if ($id > 0) {
            // Update
            $stmt = $conn->prepare("UPDATE pegawai SET nip=?, user_id=?, jabatan_id=?, divisi_id=?, nama=?, email=?, no_hp=?, foto_profil=? WHERE id=?");
            $stmt->bind_param("siiissssi", $nip, $user_id, $jabatan_id, $divisi_id, $nama, $email, $no_hp, $foto_profil, $id);
            if ($stmt->execute()) {
                set_alert('success', 'Berhasil!', 'Data pegawai berhasil diubah.');
            } else {
                set_alert('error', 'Gagal!', 'Terjadi kesalahan saat mengubah data.');
            }
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO pegawai (nip, user_id, jabatan_id, divisi_id, nama, email, no_hp, foto_profil) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siiissss", $nip, $user_id, $jabatan_id, $divisi_id, $nama, $email, $no_hp, $foto_profil);
            if ($stmt->execute()) {
                set_alert('success', 'Berhasil!', 'Data pegawai berhasil ditambahkan.');
            } else {
                set_alert('error', 'Gagal!', 'Terjadi kesalahan saat menyimpan data.');
            }
        }
        header("Location: pegawai.php");
        exit;
    }
}

// Filter logic for list
$search = sanitize($_GET['search'] ?? '');
$filter_jabatan = isset($_GET['jabatan_id']) && $_GET['jabatan_id'] !== '' ? (int)$_GET['jabatan_id'] : '';
$filter_divisi = isset($_GET['divisi_id']) && $_GET['divisi_id'] !== '' ? (int)$_GET['divisi_id'] : '';

$where = [];
if (!empty($search)) {
    $where[] = "(p.nama LIKE '%$search%' OR p.nip LIKE '%$search%' OR p.email LIKE '%$search%')";
}
if ($filter_jabatan !== '') {
    $where[] = "p.jabatan_id = $filter_jabatan";
}
if ($filter_divisi !== '') {
    $where[] = "p.divisi_id = $filter_divisi";
}

$where_clause = '';
if (count($where) > 0) {
    $where_clause = 'WHERE ' . implode(' AND ', $where);
}
?>

<div class="page-content">
    <?php if ($action == 'list'): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Data Pegawai</h4>
                <p class="text-muted small mb-0">Kelola informasi data seluruh pegawai perusahaan.</p>
            </div>
            <a href="?action=add" class="btn btn-primary"><i class="fas fa-user-plus me-1"></i> Tambah Pegawai</a>
        </div>
        
        <!-- Filter Card -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="pegawai.php" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Cari Nama / NIP</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted">Filter Jabatan</label>
                        <select name="jabatan_id" class="form-select">
                            <option value="">-- Semua Jabatan --</option>
                            <?php
                            $j_query = $conn->query("SELECT id, nama_jabatan FROM jabatan ORDER BY nama_jabatan ASC");
                            while($j = $j_query->fetch_assoc()) {
                                $sel = ($filter_jabatan === (int)$j['id']) ? 'selected' : '';
                                echo "<option value='{$j['id']}' $sel>{$j['nama_jabatan']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted">Filter Divisi</label>
                        <select name="divisi_id" class="form-select">
                            <option value="">-- Semua Divisi --</option>
                            <?php
                            $d_query = $conn->query("SELECT id, nama_divisi FROM divisi ORDER BY nama_divisi ASC");
                            while($d = $d_query->fetch_assoc()) {
                                $sel = ($filter_divisi === (int)$d['id']) ? 'selected' : '';
                                echo "<option value='{$d['id']}' $sel>{$d['nama_divisi']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
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
                                <th>Foto</th>
                                <th>NIP</th>
                                <th>Nama Lengkap</th>
                                <th>Jabatan</th>
                                <th>Divisi</th>
                                <th>Kontak</th>
                                <th>Akun User</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query_str = "SELECT p.*, u.username, j.nama_jabatan, d.nama_divisi 
                                          FROM pegawai p 
                                          LEFT JOIN users u ON p.user_id = u.id 
                                          LEFT JOIN jabatan j ON p.jabatan_id = j.id 
                                          LEFT JOIN divisi d ON p.divisi_id = d.id 
                                          $where_clause
                                          ORDER BY p.id DESC";
                            $q = $conn->query($query_str);
                            if ($q->num_rows > 0):
                                while ($row = $q->fetch_assoc()):
                            ?>
                            <tr>
                                <td>
                                    <img src="../uploads/profil/<?= $row['foto_profil'] ?: 'default.png' ?>" class="rounded-circle" style="width: 45px; height: 45px; object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($row['nama']) ?>&background=4361ee&color=fff'">
                                </td>
                                <td><span class="badge bg-light text-dark fw-bold border"><?= htmlspecialchars($row['nip']) ?></span></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama']) ?></div>
                                    <span class="text-muted small"><?= htmlspecialchars($row['email'] ?: '-') ?></span>
                                </td>
                                <td><?= htmlspecialchars($row['nama_jabatan'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($row['nama_divisi'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($row['no_hp'] ?: '-') ?></td>
                                <td>
                                    <?php if ($row['username']): ?>
                                        <span class="badge bg-success-light text-success"><i class="fas fa-user-check me-1"></i><?= htmlspecialchars($row['username']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-light text-danger"><i class="fas fa-user-times me-1"></i>Belum dikaitkan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?action=detail&id=<?= $row['id'] ?>" class="btn btn-sm btn-info text-white me-1" title="Detail"><i class="fas fa-eye"></i></a>
                                    <a href="?action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-warning text-white me-1" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="?action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data pegawai ini? Semua riwayat absensi, izin, dan cuti pegawai bersangkutan akan terhapus.')" title="Hapus"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Tidak ada data pegawai yang cocok.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    <?php elseif ($action == 'detail' && isset($_GET['id'])): 
        $id = (int)$_GET['id'];
        $res = $conn->query("SELECT p.*, u.username, u.is_active, j.nama_jabatan, d.nama_divisi 
                             FROM pegawai p 
                             LEFT JOIN users u ON p.user_id = u.id 
                             LEFT JOIN jabatan j ON p.jabatan_id = j.id 
                             LEFT JOIN divisi d ON p.divisi_id = d.id 
                             WHERE p.id = $id");
        $row = $res->fetch_assoc();
        if (!$row):
            echo "<script>window.location='pegawai.php';</script>";
            exit;
        endif;
    ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Detail Pegawai</h4>
                <p class="text-muted small mb-0">Informasi lengkap profil pegawai.</p>
            </div>
            <a href="pegawai.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        </div>
        
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card text-center">
                    <div class="card-body py-5">
                        <img src="../uploads/profil/<?= $row['foto_profil'] ?: 'default.png' ?>" class="rounded-circle mb-3 border shadow-sm" style="width: 150px; height: 150px; object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($row['nama']) ?>&background=4361ee&color=fff&size=150'">
                        <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($row['nama']) ?></h5>
                        <p class="text-muted mb-2"><?= htmlspecialchars($row['nama_jabatan'] ?: '-') ?></p>
                        <span class="badge bg-<?= $row['is_active'] == 1 ? 'success' : 'danger' ?>"><?= $row['is_active'] == 1 ? 'User Aktif' : 'User Nonaktif' ?></span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header bg-white"><h5 class="mb-0 fw-semibold">Informasi Pekerjaan & Pribadi</h5></div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%" class="text-muted">NIP</th>
                                <td class="fw-bold">: <?= htmlspecialchars($row['nip']) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Nama Lengkap</th>
                                <td>: <?= htmlspecialchars($row['nama']) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Jabatan</th>
                                <td>: <?= htmlspecialchars($row['nama_jabatan'] ?: '-') ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Divisi</th>
                                <td>: <?= htmlspecialchars($row['nama_divisi'] ?: '-') ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Email</th>
                                <td>: <?= htmlspecialchars($row['email'] ?: '-') ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Nomor HP</th>
                                <td>: <?= htmlspecialchars($row['no_hp'] ?: '-') ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Akun Username</th>
                                <td>: <?= htmlspecialchars($row['username'] ?: '-') ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Terdaftar Pada</th>
                                <td>: <?= tanggal_indo($row['created_at'], true) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    
    <?php elseif ($action == 'add' || $action == 'edit'): 
        $row = null;
        if ($action == 'edit' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $res = $conn->query("SELECT * FROM pegawai WHERE id = $id");
            $row = $res->fetch_assoc();
        }
    ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1"><?= $action == 'add' ? 'Tambah' : 'Edit' ?> Pegawai</h4>
                <p class="text-muted small mb-0">Isi formulir untuk melengkapi informasi profil pegawai.</p>
            </div>
            <a href="pegawai.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <form action="pegawai.php" method="POST" enctype="multipart/form-data">
                    <?php if($row): ?>
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">NIP (Nomor Induk Pegawai)</label>
                            <input type="text" name="nip" class="form-control" placeholder="Contoh: PG001" value="<?= $row['nip'] ?? '' ?>" required <?= $row ? 'readonly' : '' ?> autofocus>
                            <small class="text-muted">NIP harus bersifat unik.</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Akun User (Koneksi Login)</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">Pilih Akun Login Pegawai</option>
                                <?php
                                // Get users with role pegawai that are not linked to any pegawai yet (except the current user_id when editing)
                                $not_linked_condition = $row ? "OR u.id = {$row['user_id']}" : "";
                                $u_query = $conn->query("SELECT u.id, u.username FROM users u 
                                                         LEFT JOIN pegawai p ON p.user_id = u.id 
                                                         WHERE u.role = 'pegawai' AND (p.id IS NULL $not_linked_condition)");
                                while($u = $u_query->fetch_assoc()){
                                    $selected = ($row && $row['user_id'] == $u['id']) ? 'selected' : '';
                                    echo "<option value='{$u['id']}' $selected>{$u['username']}</option>";
                                }
                                ?>
                            </select>
                            <small class="text-muted">Setiap pegawai harus dikaitkan dengan satu akun user untuk dapat login.</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" value="<?= $row['nama'] ?? '' ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Jabatan</label>
                            <select name="jabatan_id" class="form-select" required>
                                <option value="">Pilih Jabatan</option>
                                <?php
                                $j_query = $conn->query("SELECT id, nama_jabatan FROM jabatan ORDER BY nama_jabatan ASC");
                                while($j = $j_query->fetch_assoc()){
                                    $selected = ($row && $row['jabatan_id'] == $j['id']) ? 'selected' : '';
                                    echo "<option value='{$j['id']}' $selected>{$j['nama_jabatan']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Divisi</label>
                            <select name="divisi_id" class="form-select" required>
                                <option value="">Pilih Divisi</option>
                                <?php
                                $d_query = $conn->query("SELECT id, nama_divisi FROM divisi ORDER BY nama_divisi ASC");
                                while($d = $d_query->fetch_assoc()){
                                    $selected = ($row && $row['divisi_id'] == $d['id']) ? 'selected' : '';
                                    echo "<option value='{$d['id']}' $selected>{$d['nama_divisi']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Contoh: budi@ptmaju.com" value="<?= $row['email'] ?? '' ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Nomor HP</label>
                            <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 08123456789" value="<?= $row['no_hp'] ?? '' ?>">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Foto Profil</label>
                        <input type="file" name="foto_profil" class="form-control" accept="image/*">
                        <small class="text-muted">Gunakan file gambar format JPG/PNG (Maksimal 2MB).</small>
                        <?php if($row && $row['foto_profil'] && $row['foto_profil'] !== 'default.png'): ?>
                            <div class="mt-2">
                                <img src="../uploads/profil/<?= $row['foto_profil'] ?>" class="img-thumbnail" style="max-height: 100px;">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Data Pegawai</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'layout/footer.php'; ?>
