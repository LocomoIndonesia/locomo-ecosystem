<?php
/**
 * Global Configuration for Platform Donasi PSB (donasi.mandeh.online)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inisialisasi CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database PostgreSQL Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'pgsql');
define('DB_PORT', getenv('DB_PORT') ?: '5432');
define('DB_NAME', getenv('DB_NAME') ?: 'donasi_mandeh');
define('DB_USER', getenv('DB_USER') ?: 'sail');
define('DB_PASS', getenv('DB_PASS') ?: 'password');

// Platform Metadata
define('APP_NAME', 'PSB Open Donasi');
define('APP_TAGLINE', 'Barek Samo Dipikua, Ringan Samo Dijinjiang');
define('APP_URL', 'http://donasi.mandeh.online.localhost');

// Rekening Resmi Organisasi PSB
define('CONFIG_REK_NOMOR', '1318662538');
define('CONFIG_REK_BANK', 'Bank BNI');
define('CONFIG_REK_ATAS_NAMA', 'Pesisir Selatan Bersatu');

// Kontak Narahubung WhatsApp Panitia / Admin PSB
define('CONFIG_WA_ADMIN_1', '6281260731173'); // Ilfan Maha Putra (Ketua Pelaksana)
define('CONFIG_WA_ADMIN_2', '62895329429118'); // Pangeran Indra Sakti (Sekretaris)

// Default Active Campaign Slug
define('DEFAULT_CAMPAIGN_SLUG', 'aksi-sosial-psb-merdeka-2026');
