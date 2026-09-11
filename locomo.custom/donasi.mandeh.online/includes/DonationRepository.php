<?php
/**
 * Donation Repository - PostgreSQL Query Handler with Pending Verification Flow
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/functions.php';

class DonationRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Submit Donasi Baru oleh Donatur (Default: status 'pending')
     */
    public function createDonation(array $data): array {
        $transactionCode = 'PSB-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $campaignSlug = $data['campaign_slug'] ?? DEFAULT_CAMPAIGN_SLUG;
        $donorName = trim($data['donor_name'] ?? 'Hamba Allah');
        $donorPhone = trim($data['donor_phone'] ?? '');
        $nominal = (float)($data['nominal'] ?? 0);
        $paymentMethod = $data['payment_method'] ?? 'Transfer Bank BNI';
        $message = trim($data['message'] ?? '');
        $isAnonymous = !empty($data['is_anonymous']);
        $proofImage = $data['proof_image'] ?? null;

        if ($nominal <= 0) {
            throw new InvalidArgumentException("Nominal donasi tidak valid.");
        }
        if (empty($donorPhone)) {
            throw new InvalidArgumentException("Nomor WhatsApp wajib diisi untuk verifikasi panitia.");
        }

        $sql = "
            INSERT INTO donations (
                transaction_code, campaign_slug, donor_name, donor_phone, nominal,
                payment_method, message, is_anonymous, proof_image, status, created_at
            ) VALUES (
                :transaction_code, :campaign_slug, :donor_name, :donor_phone, :nominal,
                :payment_method, :message, :is_anonymous, :proof_image, 'pending', CURRENT_TIMESTAMP
            ) RETURNING *
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'transaction_code' => $transactionCode,
            'campaign_slug' => $campaignSlug,
            'donor_name' => $donorName,
            'donor_phone' => $donorPhone,
            'nominal' => $nominal,
            'payment_method' => $paymentMethod,
            'message' => $message,
            'is_anonymous' => $isAnonymous ? 'true' : 'false',
            'proof_image' => $proofImage
        ]);

        $created = $stmt->fetch();
        return $created ?: [];
    }

    /**
     * Ambil Donatur Publik Terverifikasi (status = 'verified')
     */
    public function getVerifiedDonationsByCampaign(string $campaignSlug, int $limit = 50): array {
        $sql = "
            SELECT 
                id, transaction_code, donor_name, donor_phone, nominal, 
                message, is_anonymous, verified_at, created_at
            FROM donations
            WHERE campaign_slug = :slug AND status = 'verified'
            ORDER BY verified_at DESC, created_at DESC
            LIMIT :limit
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':slug', $campaignSlug, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $donations = $stmt->fetchAll();

        foreach ($donations as &$d) {
            $d['formatted_nominal'] = format_rupiah($d['nominal']);
            $d['formatted_date'] = format_tanggal_indo($d['verified_at'] ?? $d['created_at']);
            $d['masked_phone'] = mask_phone($d['donor_phone']);
            $d['display_name'] = $d['is_anonymous'] ? 'Hamba Allah' : htmlspecialchars($d['donor_name']);
        }
        return $donations;
    }

    /**
     * Ambil Seluruh Donatur Terverifikasi Lintas Kampanye untuk Feed Transparansi
     */
    public function getRecentVerifiedGlobalDonations(int $limit = 10): array {
        $sql = "
            SELECT 
                d.*, c.title AS campaign_title
            FROM donations d
            JOIN campaigns c ON d.campaign_slug = c.slug
            WHERE d.status = 'verified'
            ORDER BY d.verified_at DESC
            LIMIT :limit
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $donations = $stmt->fetchAll();

        foreach ($donations as &$d) {
            $d['formatted_nominal'] = format_rupiah($d['nominal']);
            $d['formatted_date'] = format_tanggal_indo($d['verified_at'] ?? $d['created_at']);
            $d['masked_phone'] = mask_phone($d['donor_phone']);
            $d['display_name'] = $d['is_anonymous'] ? 'Hamba Allah' : htmlspecialchars($d['donor_name']);
        }
        return $donations;
    }

    /**
     * Ambil Donasi untuk Admin Panel (Bisa filter status: 'pending', 'verified', 'rejected', atau 'all')
     */
    public function getAdminDonations(?string $status = null, int $limit = 100): array {
        if ($status && in_array($status, ['pending', 'verified', 'rejected'])) {
            $sql = "
                SELECT d.*, c.title AS campaign_title
                FROM donations d
                JOIN campaigns c ON d.campaign_slug = c.slug
                WHERE d.status = :status
                ORDER BY d.created_at DESC
                LIMIT :limit
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $sql = "
                SELECT d.*, c.title AS campaign_title
                FROM donations d
                JOIN campaigns c ON d.campaign_slug = c.slug
                ORDER BY CASE WHEN d.status = 'pending' THEN 1 WHEN d.status = 'verified' THEN 2 ELSE 3 END, d.created_at DESC
                LIMIT :limit
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    /**
     * Verifikasi Donasi oleh Admin (Status: pending -> verified)
     */
    public function verifyDonation(int $id, string $adminName = 'Admin PSB'): bool {
        $sql = "
            UPDATE donations
            SET status = 'verified',
                verified_at = CURRENT_TIMESTAMP,
                verified_by = :admin_name
            WHERE id = :id
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id, 'admin_name' => $adminName]);
    }

    /**
     * Tolak Donasi oleh Admin (Status: pending -> rejected)
     */
    public function rejectDonation(int $id, string $adminName = 'Admin PSB'): bool {
        $sql = "
            UPDATE donations
            SET status = 'rejected',
                verified_at = CURRENT_TIMESTAMP,
                verified_by = :admin_name
            WHERE id = :id
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id, 'admin_name' => $adminName]);
    }

    /**
     * Ambil Laporan Pengeluaran / RAB untuk Transparansi
     */
    public function getExpensesByCampaign(string $campaignSlug): array {
        $sql = "
            SELECT * FROM expenses
            WHERE campaign_slug = :slug
            ORDER BY expense_date DESC, id DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['slug' => $campaignSlug]);
        return $stmt->fetchAll();
    }

    /**
     * Tambah Catatan Pengeluaran / Realisasi Dana
     */
    public function addExpense(array $data): bool {
        $sql = "
            INSERT INTO expenses (campaign_slug, item_name, category, qty, unit_price, total_price, proof_url, expense_date)
            VALUES (:slug, :item_name, :category, :qty, :unit_price, :total_price, :proof_url, :expense_date)
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'slug' => $data['campaign_slug'],
            'item_name' => $data['item_name'],
            'category' => $data['category'] ?? 'Logistik',
            'qty' => $data['qty'] ?? 1,
            'unit_price' => $data['unit_price'],
            'total_price' => $data['total_price'],
            'proof_url' => $data['proof_url'] ?? null,
            'expense_date' => $data['expense_date'] ?? date('Y-m-d')
        ]);
    }
}
