<?php
/**
 * Utility Functions & Helper Library
 */

/**
 * Sensor Nomor Handphone untuk Perlindungan Privasi Publik
 * Contoh: 081234567890 -> 0812****7890
 */
function mask_phone($phone) {
    $clean = preg_replace('/[^0-9+]/', '', trim((string)$phone));
    $len = strlen($clean);
    if ($len <= 4) {
        return '****';
    }
    if ($len <= 7) {
        return substr($clean, 0, 2) . '****' . substr($clean, -2);
    }
    $prefix = substr($clean, 0, 4);
    $suffix = substr($clean, -4);
    return $prefix . '****' . $suffix;
}

/**
 * Format Nominal Rupiah Standar Indonesia
 */
function format_rupiah($nominal) {
    return 'Rp ' . number_format((float)$nominal, 0, ',', '.');
}

/**
 * Format Tanggal Indonesia
 */
function format_tanggal_indo($datetime) {
    try {
        $dt = new DateTime($datetime, new DateTimeZone('Asia/Jakarta'));
        $bulanIndo = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];
        $d = $dt->format('d');
        $m = (int)$dt->format('m');
        $y = $dt->format('Y');
        $time = $dt->format('H:i');
        return $d . ' ' . $bulanIndo[$m] . ' ' . $y . ', ' . $time . ' WIB';
    } catch (Exception $e) {
        return $datetime;
    }
}

/**
 * Hitung sisa hari dari sekarang ke deadline
 */
function get_remaining_days($deadline) {
    try {
        $now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
        $target = new DateTime($deadline, new DateTimeZone('Asia/Jakarta'));
        if ($now > $target) {
            return 0;
        }
        $diff = $now->diff($target);
        return $diff->days + ($diff->h > 0 ? 1 : 0);
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Sanitize String Input
 */
function sanitize_input($str) {
    return htmlspecialchars(trim((string)$str), ENT_QUOTES, 'UTF-8');
}
