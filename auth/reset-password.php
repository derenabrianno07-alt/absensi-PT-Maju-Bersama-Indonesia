<?php
// auth/reset-password.php
require_once '../config/database.php';
require_once '../config/helpers.php';

$token = sanitize($_GET['token'] ?? '');
$valid = false;
$user = null;

if (!empty($token)) {
    // Check if token exists and is not expired
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        $valid = true;
        $user = $res->fetch_assoc();
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    header('Content-Type: application/json');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || strlen($password) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'Password minimal harus 6 karakter!']);
        exit;
    }
    
    if ($password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'Konfirmasi password tidak cocok!']);
        exit;
    }
    
    $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
    $user_id = $user['id'];
    
    // Update password and clear token
    $stmt_up = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
    $stmt_up->bind_param("si", $hashed_pass, $user_id);
    
    if ($stmt_up->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Password Anda berhasil diperbarui! Silakan login dengan password baru.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan sistem saat memperbarui password.']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?= NAMA_SISTEM ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <div class="logo-icon"><i class="fas fa-lock-open"></i></div>
            <h4>Reset Password Baru</h4>
            <p><?= NAMA_PERUSAHAAN ?></p>
        </div>
        <div class="login-body">
            <?php if (!$valid): ?>
                <div class="alert alert-danger border-0 rounded-3 text-center py-4 small mb-4">
                    <i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>
                    Link reset password tidak valid atau telah kedaluwarsa. Silakan ajukan permintaan reset password baru.
                </div>
                <div class="text-center">
                    <a href="forgot-password.php" class="btn btn-primary w-100 py-2"><i class="fas fa-arrow-left me-1"></i> Ajukan Kembali</a>
                </div>
            <?php else: ?>
                <form id="formReset">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 6 karakter" required autofocus>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-check-double"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Ulangi password baru" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2 mb-3"><i class="fas fa-save me-2"></i>Simpan Password Baru</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
<?php if ($valid): ?>
document.getElementById('formReset').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    
    fetch('reset-password.php?token=<?= $token ?>', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: d.message,
                confirmButtonColor: '#4361ee'
            }).then(() => {
                window.location.href = 'login.php';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: d.message,
                confirmButtonColor: '#dc3545'
            });
        }
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'Kesalahan Sistem',
            text: 'Terjadi kesalahan saat memproses permintaan.'
        });
    });
});
<?php endif; ?>
</script>
</body>
</html>
