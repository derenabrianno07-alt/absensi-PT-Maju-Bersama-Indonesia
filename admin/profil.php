<?php
// admin/profil.php
require_once 'layout/header.php';

$user_id = $_SESSION['user_id'];

// Get current profile
$q = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $q->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password_baru = $_POST['password'];
    
    // Check duplicate username
    $check = $conn->query("SELECT COUNT(*) as t FROM users WHERE username = '$username' AND id != $user_id")->fetch_assoc()['t'];
    if ($check > 0) {
        set_alert('error', 'Gagal!', 'Username "' . $username . '" sudah digunakan oleh user lain.');
    } else {
        // Update Username
        $stmt = $conn->prepare("UPDATE users SET username=? WHERE id=?");
        $stmt->bind_param("si", $username, $user_id);
        $stmt->execute();
        $_SESSION['username'] = $username; // Update session
        
        // Update Password jika diisi
        if (!empty($password_baru)) {
            $hash_pass = password_hash($password_baru, PASSWORD_DEFAULT);
            $stmt_u = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt_u->bind_param("si", $hash_pass, $user_id);
            $stmt_u->execute();
        }
        
        set_alert('success', 'Berhasil!', 'Profil Administrator berhasil diperbarui.');
        header("Location: profil.php");
        exit;
    }
}
?>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Profil Admin</h4>
            <p class="text-muted small mb-0">Kelola informasi kredensial akun login administrator Anda.</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card text-center">
                <div class="card-body py-5">
                    <img src="https://ui-avatars.com/api/?name=Admin&background=4361ee&color=fff&size=150" class="rounded-circle mb-3 border shadow-sm" alt="Admin Avatar">
                    <h4>Administrator</h4>
                    <p class="text-muted mb-1"><i class="fas fa-shield-alt text-primary"></i> Role: Admin Utama</p>
                    <p class="text-muted small"><i class="fas fa-user-circle"></i> Username: <?= htmlspecialchars($user['username']) ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold">Edit Kredensial Akun</h5>
                </div>
                <div class="card-body">
                    <form action="profil.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Username Admin</label>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                            <small class="text-muted">Gunakan username ini untuk login.</small>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Password Baru</label>
                            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                            <small class="text-muted">Isi kolom ini hanya jika Anda ingin mengganti password login.</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>
