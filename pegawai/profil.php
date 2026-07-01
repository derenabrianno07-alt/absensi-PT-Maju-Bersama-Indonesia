<?php
// pegawai/profil.php
require_once 'layout/header.php';

$pegawai_id = $_SESSION['pegawai_id'];
$user_id = $_SESSION['user_id'];

// Get current profile
$q = $conn->query("SELECT p.*, u.username, j.nama_jabatan, d.nama_divisi 
                    FROM pegawai p 
                    JOIN users u ON p.user_id = u.id 
                    LEFT JOIN jabatan j ON p.jabatan_id = j.id 
                    LEFT JOIN divisi d ON p.divisi_id = d.id 
                    WHERE p.id = $pegawai_id");
$profil = $q->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $no_hp = sanitize($_POST['no_hp']);
    $password_baru = $_POST['password'];
    
    // Update Email & No HP
    $stmt_p = $conn->prepare("UPDATE pegawai SET email=?, no_hp=? WHERE id=?");
    $stmt_p->bind_param("ssi", $email, $no_hp, $pegawai_id);
    $stmt_p->execute();

    // Update Password jika diisi
    if (!empty($password_baru)) {
        $hash_pass = password_hash($password_baru, PASSWORD_DEFAULT);
        $stmt_u = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt_u->bind_param("si", $hash_pass, $user_id);
        $stmt_u->execute();
    }
    
    // Update Foto jika ada file
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
        $ext = pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION);
        $foto_name = 'profil_' . $pegawai_id . '_' . time() . '.' . $ext;
        
        if (!is_dir('../uploads/profil')) {
            mkdir('../uploads/profil', 0777, true);
        }
        
        if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], '../uploads/profil/' . $foto_name)) {
            // Delete old photo if not default
            if ($profil['foto_profil'] && $profil['foto_profil'] !== 'default.png') {
                @unlink('../uploads/profil/' . $profil['foto_profil']);
            }
            $stmt_f = $conn->prepare("UPDATE pegawai SET foto_profil=? WHERE id=?");
            $stmt_f->bind_param("si", $foto_name, $pegawai_id);
            $stmt_f->execute();
            $_SESSION['foto_profil'] = $foto_name; // Update session
        }
    }
    
    set_alert('success', 'Berhasil!', 'Profil Anda berhasil diperbarui.');
    header("Location: profil.php");
    exit;
}
?>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Profil Saya</h4>
            <p class="text-muted small mb-0">Kelola informasi pribadi dan keamanan akun Anda.</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card text-center">
                <div class="card-body py-5">
                    <img src="../uploads/profil/<?= $profil['foto_profil'] ?: 'default.png' ?>" class="rounded-circle mb-3 border shadow-sm" style="width: 150px; height: 150px; object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($profil['nama']) ?>&background=4361ee&color=fff&size=150'" alt="Foto Profil">
                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($profil['nama']) ?></h5>
                    <p class="text-muted mb-1"><i class="fas fa-id-badge text-primary me-1"></i> NIP: <?= htmlspecialchars($profil['nip']) ?></p>
                    <p class="text-muted mb-1"><i class="fas fa-briefcase text-info me-1"></i> <?= htmlspecialchars($profil['nama_jabatan'] ?: '-') ?></p>
                    <p class="text-muted mb-0"><i class="fas fa-sitemap text-success me-1"></i> <?= htmlspecialchars($profil['nama_divisi'] ?: '-') ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold">Edit Profil</h5>
                </div>
                <div class="card-body">
                    <form action="profil.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($profil['email']) ?>" placeholder="Masukkan alamat email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nomor HP</label>
                            <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($profil['no_hp']) ?>" placeholder="Masukkan nomor HP">
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password Baru</label>
                            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                            <small class="text-muted">Isi kolom ini hanya jika Anda ingin mengganti password login.</small>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Ganti Foto Profil</label>
                            <input type="file" name="foto_profil" class="form-control" accept="image/*">
                            <small class="text-muted">Upload foto baru dalam format JPG/PNG (Maks. 2MB).</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>
