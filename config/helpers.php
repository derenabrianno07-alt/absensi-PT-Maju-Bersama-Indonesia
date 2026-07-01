<?php
// config/helpers.php

// Base URL helper
if (!function_class_exists('base_url')) {
    function base_url($path = '') {
        // Adjust this if your project folder name is different
        return '/absensi/' . ltrim($path, '/');
    }
}

// Function checking to avoid errors if declared elsewhere
function function_class_exists($name) {
    return function_exists($name);
}

// Sanitasi Input (XSS Protection)
if (!function_exists('sanitize')) {
    function sanitize($data) {
        if (is_array($data)) {
            return array_map('sanitize', $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

// CSRF Token Generation
if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

// CSRF Field HTML generator
if (!function_exists('csrf_field')) {
    function csrf_field() {
        $token = generate_csrf_token();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}

// CSRF Token Validation
if (!function_exists('validate_csrf_token')) {
    function validate_csrf_token($token) {
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Format Tanggal Indonesia
if (!function_exists('tanggal_indo')) {
    function tanggal_indo($tanggal, $cetak_hari = false) {
        if (empty($tanggal) || $tanggal == '0000-00-00') return '-';
        
        $hari_array = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        
        $bulan_array = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        
        $timestamp = strtotime($tanggal);
        $tgl = date('j', $timestamp);
        $bln = $bulan_array[(int)date('n', $timestamp)];
        $thn = date('Y', $timestamp);
        
        $hasil = "$tgl $bln $thn";
        
        if ($cetak_hari) {
            $hari_eng = date('l', $timestamp);
            $hari = $hari_array[$hari_eng] ?? $hari_eng;
            $hasil = "$hari, $hasil";
        }
        
        return $hasil;
    }
}

// Format Jam (HH:MM)
if (!function_exists('format_jam')) {
    function format_jam($time) {
        if (empty($time)) return '-';
        return date('H:i', strtotime($time)) . ' WIB';
    }
}

// AJAX Response Helper
if (!function_exists('json_response')) {
    function json_response($status, $message, $data = []) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
}

// Display toast message alert
if (!function_exists('get_alert')) {
    function get_alert() {
        if (isset($_SESSION['alert'])) {
            $alert = $_SESSION['alert'];
            unset($_SESSION['alert']);
            return "
                <script>
                    Swal.fire({
                        icon: '{$alert['type']}',
                        title: '{$alert['title']}',
                        text: '{$alert['message']}',
                        timer: 3000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                </script>
            ";
        }
        return '';
    }
}

// Set toast message alert
if (!function_exists('set_alert')) {
    function set_alert($type, $title, $message) {
        $_SESSION['alert'] = [
            'type' => $type,
            'title' => $title,
            'message' => $message
        ];
    }
}
