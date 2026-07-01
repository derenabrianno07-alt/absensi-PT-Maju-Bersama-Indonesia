<?php
// auth/forgot-password.php
require_once '../config/database.php';
require_once '../config/helpers.php';
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Alamat email wajib diisi!']);
        exit;
    }
    
    // Check if email exists in pegawai table and join users
    $stmt = $conn->prepare("SELECT p.nama, p.user_id, u.username FROM pegawai p JOIN users u ON p.user_id = u.id WHERE p.email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        $pegawai = $res->fetch_assoc();
        $user_id = $pegawai['user_id'];
        $nama = $pegawai['nama'];
        
        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Expires in 1 hour
        
        // Update user record with token and expiry
        $stmt_tok = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
        $stmt_tok->bind_param("ssi", $token, $expiry, $user_id);
        $stmt_tok->execute();
        
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/absensi/auth/reset-password.php?token=" . $token;
        
        // Check if SMTP is configured (not using default values)
        if (defined('SMTP_USER') && SMTP_USER !== 'your_email@gmail.com' && SMTP_USER !== '') {
            // Send real email using PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = SMTP_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USER;
                $mail->Password   = SMTP_PASS;
                $mail->SMTPSecure = SMTP_SECURE;
                $mail->Port       = SMTP_PORT;
                
                // Recipients
                $mail->setFrom(SMTP_USER, NAMA_PERUSAHAAN);
                $mail->addAddress($email, $nama);
                
                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Reset Password Akun - ' . NAMA_SISTEM;
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 8px;'>
                        <h3 style='color: #4361ee;'>Halo, {$nama}</h3>
                        <p>Anda menerima email ini karena ada permintaan untuk mereset password akun absensi Anda di <strong>" . NAMA_PERUSAHAAN . "</strong>.</p>
                        <p>Silakan klik tautan di bawah ini untuk mereset password Anda:</p>
                        <p style='margin: 25px 0;'><a href='{$reset_link}' style='display:inline-block;padding:12px 24px;background:#4361ee;color:white;text-decoration:none;border-radius:6px;font-weight:bold;'>Reset Password Baru</a></p>
                        <p style='color: #888; font-size: 0.85rem;'>Link ini hanya berlaku selama 1 jam dari sekarang.</p>
                        <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                        <small style='color: #aaa;'>Jika Anda tidak meminta reset password ini, abaikan email ini.</small>
                    </div>
                ";
                
                $mail->send();
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Halo ' . $nama . ', link ganti password baru telah berhasil dikirimkan ke email Anda (' . $email . '). Silakan periksa inbox Anda.'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Gagal mengirim email: ' . $mail->ErrorInfo . '. Namun, link reset berhasil dibuat secara lokal: <br><br><a href="' . $reset_link . '" class="btn btn-outline-primary btn-sm">' . $reset_link . '</a>'
                ]);
            }
        } else {
            // Fallback: If not configured, simulate success (dummy mode) but provide the link in the UI
            echo json_encode([
                'status' => 'success',
                'message' => '[MODE SIMULASI] Halo ' . $nama . ', link reset berhasil dibuat! Karena SMTP belum diatur di Pengaturan, silakan klik link reset ini langsung untuk mencoba: <br><br><a href="' . $reset_link . '" class="btn btn-outline-primary btn-sm">' . $reset_link . '</a>'
            ]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Email tidak terdaftar di sistem pegawai!']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - <?= NAMA_SISTEM ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <div class="logo-icon"><i class="fas fa-key"></i></div>
            <h4>Lupa Password</h4>
            <p><?= NAMA_PERUSAHAAN ?></p>
        </div>
        <div class="login-body">
            <form id="formForgot">
                <div class="mb-4">
                    <label class="form-label fw-semibold">Alamat Email Pegawai</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email terdaftar" required autofocus>
                    </div>
                    <small class="text-muted mt-2 d-block">Masukkan alamat email akun pegawai Anda untuk menerima link reset password.</small>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2 mb-3"><i class="fas fa-paper-plane me-2"></i>Kirim Link Reset</button>
                
                <div class="text-center">
                    <a href="login.php" class="small text-decoration-none text-muted"><i class="fas fa-arrow-left me-1"></i> Kembali ke Login</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('formForgot').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    
    // Show loading state
    Swal.fire({
        title: 'Memproses...',
        text: 'Mengirim email reset password',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('forgot-password.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Reset Password',
                html: d.message,
                confirmButtonColor: '#4361ee'
            }).then(() => {
                // If not in simulation mode (which has a link to click), redirect to login
                if (!d.message.includes('[MODE SIMULASI]')) {
                    window.location.href = 'login.php';
                }
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
</script>
</body>
</html>
