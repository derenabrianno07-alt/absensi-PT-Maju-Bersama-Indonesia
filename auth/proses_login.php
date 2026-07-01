<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status'=>'error','message'=>'Method not allowed']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($username) || empty($password)) {
    echo json_encode(['status'=>'error','message'=>'Username dan Password wajib diisi!']);
    exit;
}

$stmt = $conn->prepare("SELECT id, username, password, role, is_active FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status'=>'error','message'=>'Username tidak ditemukan!']);
    exit;
}

$user = $result->fetch_assoc();

if ($user['is_active'] == 0) {
    echo json_encode(['status'=>'error','message'=>'Akun Anda dinonaktifkan. Hubungi Admin.']);
    exit;
}

if (!password_verify($password, $user['password'])) {
    echo json_encode(['status'=>'error','message'=>'Password salah!']);
    exit;
}

// Set session
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

$nama = $user['username']; // default
$redirect = '../admin/index.php';

if ($user['role'] == 'pegawai') {
    $stmt2 = $conn->prepare("SELECT p.id, p.nama, p.nip, p.foto_profil, j.nama_jabatan, d.nama_divisi FROM pegawai p LEFT JOIN jabatan j ON p.jabatan_id=j.id LEFT JOIN divisi d ON p.divisi_id=d.id WHERE p.user_id = ?");
    $stmt2->bind_param("i", $user['id']);
    $stmt2->execute();
    $peg = $stmt2->get_result()->fetch_assoc();

    if (!$peg) {
        echo json_encode(['status'=>'error','message'=>'Profil pegawai tidak ditemukan!']);
        session_destroy();
        exit;
    }

    $_SESSION['pegawai_id'] = $peg['id'];
    $_SESSION['nama'] = $peg['nama'];
    $_SESSION['nip'] = $peg['nip'];
    $_SESSION['foto_profil'] = $peg['foto_profil'];
    $_SESSION['jabatan'] = $peg['nama_jabatan'];
    $_SESSION['divisi'] = $peg['nama_divisi'];
    $nama = $peg['nama'];
    $redirect = '../pegawai/index.php';
} else {
    $_SESSION['nama'] = 'Administrator';
}

echo json_encode(['status'=>'success','nama'=>$nama,'redirect'=>$redirect]);
?>
