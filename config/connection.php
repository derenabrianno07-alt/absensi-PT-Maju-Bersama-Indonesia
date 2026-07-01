<?php
// config/connection.php

$host = 'localhost';
$db   = 'db_absensi';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Set zona waktu ke WIB (Waktu Indonesia Barat) agar jam absen sesuai
date_default_timezone_set('Asia/Jakarta');

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // In production, log error and show a user-friendly message.
    // For academic tasks, showing connection error is fine.
    die("Koneksi database gagal: " . $e->getMessage());
}
