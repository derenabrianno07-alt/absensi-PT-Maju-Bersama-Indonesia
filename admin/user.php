<?php
require_once 'layout/header.php';

$action = $_GET['action'] ?? 'list';

// Handle Delete
if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id = $id");
        set_alert('success', 'Berhasil!', 'Akun user berhasil dihapus.');
    } else {
        set_alert('error', 'Gagal!', 'Tidak bisa menghapus akun yang sedang digunakan login!');
    }
    header("Location: user.php");
    exit;
}

// Handle Insert/Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $role = sanitize($_POST['role']);
    $is_active = (int)$_POST['is_active'];
    
    // Check duplicate username
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $check_q = $id > 0 ? "SELECT COUNT(*) as t FROM users WHERE username='$username' AND id != $id" : "SELECT COUNT(*) as t FROM users WHERE username='$username'";
    $check = $conn->query($check_q)->fetch_assoc()['t'];
    
    if ($check > 0) {
        set_alert('error', 'Gagal!', 'Username "' . $username . '" sudah digunakan.');
    } else {
        if ($id > 0) {
            // Update
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username=?, password=?, role=?, is_active=? WHERE id=?");
                $stmt->bind_param("sssii", $username, $password, $role, $is_active, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username=?, role=?, is_active=? WHERE id=?");
                $stmt->bind_param("ssii", $username, $role, $is_active, $id);
            }
            if ($stmt->execute()) {
                set_alert('success', 'Berhasil!', 'Akun user berhasil diupdate.');
            } else {
                set_alert('error', 'Gagal!', 'Terjadi kesalahan saat mengupdate akun.');
            }
        } else {
            // Insert
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role, is_active) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $username, $password, $role, $is_active);
            if ($stmt->execute()) {
                set_alert('success', 'Berhasil!', 'Akun user berhasil ditambahkan.');
            } else {
                set_alert('error', 'Gagal!', 'Terjadi kesalahan saat membuat akun baru.');
            }
        }
        header("Location: user.php");
        exit;
    }
}
?>

<div class="container-fluid">
    <?php if ($action == 'list'): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Data User Akun</h3>
            <a href="?action=add" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Akun</a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status Aktif</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q = $conn->query("SELECT * FROM users ORDER BY id DESC");
                            $no = 1;
                            while ($row = $q->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $row['role'] == 'admin' ? 'primary' : 'success' ?>">
                                        <?= strtoupper($row['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($row['is_active'] == 1): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    <?php if($row['id'] != $_SESSION['user_id']): ?>
                                    <a href="?action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus akun?')"><i class="fas fa-trash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    
    <?php elseif ($action == 'add' || $action == 'edit'): 
        $row = null;
        if ($action == 'edit' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $res = $conn->query("SELECT * FROM users WHERE id = $id");
            $row = $res->fetch_assoc();
        }
    ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><?= $action == 'add' ? 'Tambah' : 'Edit' ?> Akun User</h3>
            <a href="user.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <form action="user.php" method="POST">
                    <?php if($row): ?>
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="<?= $row['username'] ?? '' ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password <?= $action == 'edit' ? '(Kosongkan jika tidak ingin mengubah)' : '' ?></label>
                        <input type="password" name="password" class="form-control" <?= $action == 'add' ? 'required' : '' ?>>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="pegawai" <?= ($row && $row['role'] == 'pegawai') ? 'selected' : '' ?>>Pegawai</option>
                            <option value="admin" <?= ($row && $row['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status Akun</label>
                        <select name="is_active" class="form-select" required>
                            <option value="1" <?= ($row && $row['is_active'] == '1') ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= ($row && $row['is_active'] == '0') ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'layout/footer.php'; ?>
