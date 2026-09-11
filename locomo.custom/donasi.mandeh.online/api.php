<?php
/**
 * REST API Endpoint for donasi.mandeh.online
 */

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/CampaignRepository.php';
require_once __DIR__ . '/includes/DonationRepository.php';
require_once __DIR__ . '/includes/Auth.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$campaignRepo = new CampaignRepository();
$donationRepo = new DonationRepository();

try {
    switch ($action) {
        case 'campaigns':
            $campaigns = $campaignRepo->getAllActiveCampaigns();
            echo json_encode([
                'status' => 'success',
                'data' => $campaigns
            ]);
            break;

        case 'campaign_detail':
            $slug = $_GET['slug'] ?? DEFAULT_CAMPAIGN_SLUG;
            $campaign = $campaignRepo->getBySlug($slug);
            if (!$campaign) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Kampanye tidak ditemukan']);
                exit;
            }
            $donations = $donationRepo->getVerifiedDonationsByCampaign($slug, 20);
            echo json_encode([
                'status' => 'success',
                'campaign' => $campaign,
                'verified_donations' => $donations
            ]);
            break;

        case 'submit_donation':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
                exit;
            }

            // Validasi CSRF
            $csrf = $_POST['csrf_token'] ?? '';
            if (empty($csrf) || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Sesi kedaluwarsa. Silakan refresh halaman.']);
                exit;
            }

            $campaignSlug = sanitize_input($_POST['campaign_slug'] ?? DEFAULT_CAMPAIGN_SLUG);
            $nama = sanitize_input($_POST['nama'] ?? 'Hamba Allah');
            $noHp = sanitize_input($_POST['no_hp'] ?? '');
            $jumlah = (float)preg_replace('/[^0-9]/', '', $_POST['jumlah'] ?? '0');
            $pesan = sanitize_input($_POST['pesan'] ?? '');
            $isAnonim = !empty($_POST['is_anonim']);
            $metode = sanitize_input($_POST['metode_pembayaran'] ?? 'Transfer Bank BNI');

            if ($jumlah < 10000) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Nominal donasi minimal Rp 10.000']);
                exit;
            }
            if (empty($noHp) || strlen(preg_replace('/[^0-9]/', '', $noHp)) < 9) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Nomor WhatsApp tidak valid untuk verifikasi donasi.']);
                exit;
            }

            // Handle Upload Bukti Transfer
            if (empty($_FILES['bukti_transfer']) || $_FILES['bukti_transfer']['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Bukti transfer wajib diunggah.']);
                exit;
            }

            $file = $_FILES['bukti_transfer'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Format file tidak didukung. Harap unggah gambar (JPG, PNG, WEBP).']);
                exit;
            }
            if ($file['size'] > 5 * 1024 * 1024) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Ukuran file maksimal adalah 5MB.']);
                exit;
            }

            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (empty($ext)) {
                $ext = 'jpg'; // default backup fallback
            }
            $filename = 'bukti_' . uniqid() . '.' . $ext;
            $targetPath = $uploadDir . $filename;
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan bukti transfer di server.']);
                exit;
            }
            $proofImage = '/uploads/' . $filename;

            $donation = $donationRepo->createDonation([
                'campaign_slug' => $campaignSlug,
                'donor_name' => $nama,
                'donor_phone' => $noHp,
                'nominal' => $jumlah,
                'payment_method' => $metode,
                'message' => $pesan,
                'is_anonymous' => $isAnonim,
                'proof_image' => $proofImage
            ]);

            // Siapkan URL WhatsApp Konfirmasi ke Admin PSB
            $waMessage = "Halo Panitia PSB,%0A"
                . "Saya ingin konfirmasi donasi untuk program *" . urlencode($campaignSlug) . "*:%0A%0A"
                . "Kode Donasi: *" . $donation['transaction_code'] . "*%0A"
                . "Nama: *" . urlencode($nama) . "* (" . ($isAnonim ? "Tampil Anonim" : "Tampil Nama") . ")%0A"
                . "Nominal: *" . urlencode(format_rupiah($jumlah)) . "*%0A"
                . "Metode: *" . urlencode($metode) . "*%0A"
                . "Pesan/Doa: _" . urlencode($pesan ?: '-') . "_%0A%0A"
                . "Mohon diverifikasi setelah saya mengirimkan bukti transfer ini. Terima kasih.";

            $waUrl = "https://wa.me/" . CONFIG_WA_ADMIN_1 . "?text=" . $waMessage;

            echo json_encode([
                'status' => 'success',
                'message' => 'Donasi berhasil dicatat dengan status pending. Silakan lakukan transfer dan kirim bukti ke panitia.',
                'data' => [
                    'transaction_code' => $donation['transaction_code'],
                    'status' => 'pending',
                    'nominal' => $jumlah,
                    'formatted_nominal' => format_rupiah($jumlah),
                    'rek_nomor' => CONFIG_REK_NOMOR,
                    'rek_bank' => CONFIG_REK_BANK,
                    'rek_atas_nama' => CONFIG_REK_ATAS_NAMA,
                    'wa_url' => $waUrl
                ]
            ]);
            break;

        case 'verify_donation':
            if (!Auth::check()) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Silakan login sebagai admin.']);
                exit;
            }
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ID donasi tidak valid.']);
                exit;
            }
            $user = Auth::user();
            $success = $donationRepo->verifyDonation($id, $user['name'] ?? 'Admin PSB');
            echo json_encode([
                'status' => $success ? 'success' : 'error',
                'message' => $success ? 'Donasi berhasil diverifikasi dan dana telah teragregasi ke kampanye!' : 'Gagal memverifikasi donasi.'
            ]);
            break;

        case 'reject_donation':
            if (!Auth::check()) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Silakan login sebagai admin.']);
                exit;
            }
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ID donasi tidak valid.']);
                exit;
            }
            $user = Auth::user();
            $success = $donationRepo->rejectDonation($id, $user['name'] ?? 'Admin PSB');
            echo json_encode([
                'status' => $success ? 'success' : 'error',
                'message' => $success ? 'Donasi telah ditolak.' : 'Gagal menolak donasi.'
            ]);
            break;

        case 'stats':
            $stats = $campaignRepo->getGlobalStats();
            echo json_encode([
                'status' => 'success',
                'data' => $stats
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenali']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
