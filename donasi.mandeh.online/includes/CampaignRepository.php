<?php
/**
 * Campaign Repository - PostgreSQL Query Handler
 */

require_once __DIR__ . '/Database.php';

class CampaignRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Ambil seluruh kampanye aktif dengan agregat dana terkumpul (verified only)
     */
    public function getAllActiveCampaigns(): array {
        $sql = "
            SELECT 
                c.*,
                COALESCE(SUM(d.nominal) FILTER (WHERE d.status = 'verified'), 0) AS collected_amount,
                COUNT(d.id) FILTER (WHERE d.status = 'verified') AS donors_count,
                COUNT(d.id) FILTER (WHERE d.status = 'pending') AS pending_donors_count
            FROM campaigns c
            LEFT JOIN donations d ON c.slug = d.campaign_slug
            WHERE c.is_active = TRUE
            GROUP BY c.id
            ORDER BY c.is_featured DESC, c.created_at DESC
        ";
        $stmt = $this->db->query($sql);
        $campaigns = $stmt->fetchAll();

        foreach ($campaigns as &$c) {
            $target = (float)$c['target_amount'];
            $collected = (float)$c['collected_amount'];
            $c['progress_percent'] = $target > 0 ? min(100, round(($collected / $target) * 100, 1)) : 0;
            $c['remaining_days'] = get_remaining_days($c['deadline']);
        }
        return $campaigns;
    }

    /**
     * Ambil kampanye berdasarkan slug
     */
    public function getBySlug(string $slug): ?array {
        $sql = "
            SELECT 
                c.*,
                COALESCE(SUM(d.nominal) FILTER (WHERE d.status = 'verified'), 0) AS collected_amount,
                COUNT(d.id) FILTER (WHERE d.status = 'verified') AS donors_count,
                COUNT(d.id) FILTER (WHERE d.status = 'pending') AS pending_donors_count
            FROM campaigns c
            LEFT JOIN donations d ON c.slug = d.campaign_slug
            WHERE c.slug = :slug
            GROUP BY c.id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        $campaign = $stmt->fetch();

        if (!$campaign) {
            return null;
        }

        $target = (float)$campaign['target_amount'];
        $collected = (float)$campaign['collected_amount'];
        $campaign['progress_percent'] = $target > 0 ? min(100, round(($collected / $target) * 100, 1)) : 0;
        $campaign['remaining_days'] = get_remaining_days($campaign['deadline']);

        return $campaign;
    }

    /**
     * Ambil default featured campaign (Aksi Sosial PSB Merdeka 2026)
     */
    public function getFeaturedCampaign(): ?array {
        $sql = "
            SELECT 
                c.*,
                COALESCE(SUM(d.nominal) FILTER (WHERE d.status = 'verified'), 0) AS collected_amount,
                COUNT(d.id) FILTER (WHERE d.status = 'verified') AS donors_count,
                COUNT(d.id) FILTER (WHERE d.status = 'pending') AS pending_donors_count
            FROM campaigns c
            LEFT JOIN donations d ON c.slug = d.campaign_slug
            WHERE c.is_featured = TRUE AND c.is_active = TRUE
            GROUP BY c.id
            LIMIT 1
        ";
        $stmt = $this->db->query($sql);
        $campaign = $stmt->fetch();

        if ($campaign) {
            $target = (float)$campaign['target_amount'];
            $collected = (float)$campaign['collected_amount'];
            $campaign['progress_percent'] = $target > 0 ? min(100, round(($collected / $target) * 100, 1)) : 0;
            $campaign['remaining_days'] = get_remaining_days($campaign['deadline']);
        }
        return $campaign ?: $this->getBySlug(DEFAULT_CAMPAIGN_SLUG);
    }

    /**
     * Ambil Statistik Agregat Seluruh Platform PSB
     */
    public function getGlobalStats(): array {
        $sql = "
            SELECT 
                COALESCE(SUM(nominal) FILTER (WHERE status = 'verified'), 0) AS total_verified_funds,
                COALESCE(SUM(nominal) FILTER (WHERE status = 'pending'), 0) AS total_pending_funds,
                COUNT(id) FILTER (WHERE status = 'verified') AS total_verified_donors,
                COUNT(id) FILTER (WHERE status = 'pending') AS total_pending_donors,
                (SELECT COUNT(*) FROM campaigns WHERE is_active = TRUE) AS total_active_campaigns
            FROM donations
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetch() ?: [
            'total_verified_funds' => 0,
            'total_pending_funds' => 0,
            'total_verified_donors' => 0,
            'total_pending_donors' => 0,
            'total_active_campaigns' => 0
        ];
    }
}
