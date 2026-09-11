<?php
/**
 * Web Admin Panel for PSB (donasi.mandeh.online)
 * Dashboard, Pending Verification List, Approve/Reject Actions, & Logout
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/CampaignRepository.php';
require_once __DIR__ . '/includes/DonationRepository.php';
require_once __DIR__ . '/includes/Auth.php';

$action = $_GET['action'] ?? '';

// Handle Login POST
if ($action === 'login_submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (Auth::login($username, $password)) {
        header('Location: /admin.php');
        exit;
    } else {
        $error = 'Username atau Password salah.';
        $action = 'login';
    }
}

// Handle Logout
if ($action === 'logout') {
    Auth::logout();
    header('Location: /admin.php?action=login');
    exit;
}

// Show Login Form if not logged in
if (!Auth::check() || $action === 'login') {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Web Admin PSB — donasi.mandeh.online</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
      :root{
        --maroon:#5C0A16;
        --navy:#0B1620;
        --border-color:#E2E8F0;
        --radius:12px;
      }
      *{box-sizing:border-box;margin:0;padding:0;}
      body{font-family:'Plus Jakarta Sans',sans-serif;background:#F8FAFC;color:#1E293B;display:flex;align-items:center;justify-content:center;min-height:100vh;}
      .login-container{background:#fff;border-radius:var(--radius);padding:40px;width:100%;max-width:400px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);border-top:5px solid var(--maroon);}
      .login-title{font-size:22px;font-weight:800;color:var(--navy);margin-bottom:6px;text-align:center;}
      .login-subtitle{font-size:13px;color:#64748B;margin-bottom:24px;text-align:center;}
      .form-group{margin-bottom:18px;}
      .form-label{display:block;font-size:13px;font-weight:700;margin-bottom:6px;}
      .form-input{width:100%;padding:12px;border:1.5px solid var(--border-color);border-radius:8px;font-size:14px;font-family:inherit;}
      .form-input:focus{outline:none;border-color:var(--maroon);box-shadow:0 0 0 3px rgba(92,10,22,0.1);}
      .btn-submit{display:block;width:100%;background:var(--maroon);color:#fff;padding:12px;border-radius:8px;font-weight:700;border:none;cursor:pointer;font-size:14px;transition:0.2s;}
      .btn-submit:hover{background:#7A0F1F;}
      .alert{background:#FEF2F2;border:1px solid #FCA5A5;color:#991B1B;padding:12px;border-radius:8px;font-size:13px;margin-bottom:18px;}
    </style>
    </head>
    <body>
      <div class="login-container">
        <h2 class="login-title">Web Admin PSB</h2>
        <p class="login-subtitle">Verifikasi & Pengelolaan Platform Donasi</p>
        
        <?php if (!empty($error)): ?>
          <div class="alert"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="/admin.php?action=login_submit" method="POST">
          <div class="form-group">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-input" placeholder="Masukkan username" required autofocus>
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-input" placeholder="Masukkan password" required>
          </div>
          <button type="submit" class="btn-submit">Masuk ke Dashboard</button>
        </form>
      </div>
    </body>
    </html>
    <?php
    exit;
}

// Logged in admin workflow
Auth::requireAuth();
$adminUser = Auth::user();
$campaignRepo = new CampaignRepository();
$donationRepo = new DonationRepository();

$globalStats = $campaignRepo->getGlobalStats();
$pendingDonations = $donationRepo->getAdminDonations('pending');
$allDonations = $donationRepo->getAdminDonations();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin PSB — donasi.mandeh.online</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
  :root{
    --maroon:#5C0A16;
    --navy:#0B1620;
    --border-color:#E2E8F0;
    --radius:16px;
    --radius-sm:8px;
  }
  *{box-sizing:border-box;margin:0;padding:0;}
  body{font-family:'Plus Jakarta Sans',sans-serif;background:#F8FAFC;color:#1E293B;display:flex;min-height:100vh;}
  
  /* Sidebar */
  .sidebar{width:260px;background:var(--navy);color:#fff;padding:24px;display:flex;flex-direction:column;}
  .sidebar-header{border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:20px;margin-bottom:20px;}
  .sidebar-logo{font-size:18px;font-weight:800;color:var(--gold-light);display:flex;align-items:center;gap:10px;}
  .menu-link{color:#94A3B8;text-decoration:none;padding:12px;border-radius:var(--radius-sm);display:flex;align-items:center;gap:10px;font-size:14px;font-weight:600;margin-bottom:6px;transition:0.2s;}
  .menu-link:hover,.menu-link.active{background:rgba(255,255,255,0.05);color:#fff;}
  .sidebar-footer{margin-top:auto;padding-top:20px;border-top:1px solid rgba(255,255,255,0.1);font-size:12px;color:#64748B;}

  /* Main Panel */
  .main-panel{flex:1;padding:32px;overflow-y:auto;}
  .panel-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:32px;}
  .panel-title{font-size:24px;font-weight:800;color:var(--navy);}

  /* Stat Cards */
  .stat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:32px;}
  .stat-card{background:#fff;padding:24px;border-radius:var(--radius);box-shadow:0 1px 3px rgba(0,0,0,0.05);border:1px solid var(--border-color);display:flex;align-items:center;gap:20px;}
  .stat-icon{width:54px;height:54px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;}
  .stat-info h4{font-size:24px;font-weight:800;color:var(--navy);}
  .stat-info p{font-size:12px;color:#64748B;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;}

  /* Content Cards */
  .card{background:#fff;border-radius:var(--radius);padding:24px;border:1px solid var(--border-color);box-shadow:0 1px 3px rgba(0,0,0,0.05);margin-bottom:32px;}
  .card-title{font-size:18px;font-weight:700;color:var(--navy);margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;}

  /* Tables */
  .table-responsive{width:100%;overflow-x:auto;}
  .table{width:100%;border-collapse:collapse;text-align:left;font-size:13px;}
  .table th{background:#F8FAFC;padding:12px;font-weight:700;color:#64748B;border-bottom:2px solid var(--border-color);}
  .table td{padding:14px 12px;border-bottom:1px solid var(--border-color);vertical-align:middle;}
  .table tr:hover{background:#F8FAFC;}

  /* Badges */
  .badge{display:inline-block;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:700;text-transform:uppercase;}
  .badge-pending{background:#FEF3C7;color:#92400E;}
  .badge-verified{background:#D1FAE5;color:#065F46;}
  .badge-rejected{background:#FEE2E2;color:#991B1B;}

  /* Buttons */
  .btn{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:var(--radius-sm);font-size:12px;font-weight:700;text-decoration:none;border:none;cursor:pointer;transition:0.2s;}
  .btn-verify{background:#10B981;color:#fff;}
  .btn-verify:hover{background:#059669;}
  .btn-reject{background:#EF4444;color:#fff;}
  .btn-reject:hover{background:#DC2626;}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-logo"><i class="fas fa-shield-halved"></i> PSB Admin Panel</div>
  </div>
  
  <a href="/admin.php" class="menu-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
  <a href="/" class="menu-link" target="_blank"><i class="fas fa-globe"></i> Lihat Web Publik</a>
  <a href="/admin.php?action=logout" class="menu-link" style="margin-top:auto;color:#EF4444;"><i class="fas fa-sign-out-alt"></i> Keluar</a>
  
  <div class="sidebar-footer">
    <p>Halo, <strong><?= htmlspecialchars($adminUser['name']) ?></strong></p>
    <p style="font-size:10px;margin-top:4px;">Secure OS Session</p>
  </div>
</div>

<!-- Main Panel -->
<div class="main-panel">
  <div class="panel-header">
    <div>
      <h1 class="panel-title">Dashboard Verifikasi</h1>
      <p style="font-size:13px;color:#64748B;">Kelola dana masuk dan konfirmasi donatur untuk Pesisir Selatan Bersatu.</p>
    </div>
  </div>

  <!-- Stats Grid -->
  <div class="stat-grid">
    <div class="stat-card">
      <div class="stat-icon" style="background:#ECFDF5;color:#10B981;"><i class="fas fa-hand-holding-dollar"></i></div>
      <div class="stat-info">
        <h4><?= format_rupiah($globalStats['total_verified_funds']) ?></h4>
        <p>Total Terverifikasi</p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:#FFFBEB;color:#F59E0B;"><i class="fas fa-hourglass-half"></i></div>
      <div class="stat-info">
        <h4><?= format_rupiah($globalStats['total_pending_funds']) ?></h4>
        <p>Dana Masuk Pending</p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:#EFF6FF;color:#3B82F6;"><i class="fas fa-users"></i></div>
      <div class="stat-info">
        <h4><?= $globalStats['total_verified_donors'] ?></h4>
        <p>Donatur Terverifikasi</p>
      </div>
    </div>
  </div>

  <!-- Pending Verification Table -->
  <div class="card">
    <h3 class="card-title">
      <span><i class="fas fa-clock" style="color:#F59E0B;"></i> Menunggu Verifikasi (<?= count($pendingDonations) ?>)</span>
    </h3>
    <div class="table-responsive">
       <table class="table">
         <thead>
           <tr>
             <th>Kode</th>
             <th>Program Kampanye</th>
             <th>Nama Donatur</th>
             <th>No WhatsApp</th>
             <th>Nominal</th>
             <th>Bukti Transfer</th>
             <th>Pesan/Doa</th>
             <th style="width: 180px; text-align: center;">Aksi Verifikasi</th>
           </tr>
         </thead>
         <tbody>
           <?php if (empty($pendingDonations)): ?>
             <tr>
               <td colspan="8" style="text-align:center;padding:24px;color:#64748B;">Tidak ada donasi pending yang perlu diverifikasi saat ini.</td>
             </tr>
           <?php else: ?>
             <?php foreach ($pendingDonations as $d): ?>
               <tr id="row-<?= $d['id'] ?>">
                 <td><strong><?= htmlspecialchars($d['transaction_code']) ?></strong></td>
                 <td><?= htmlspecialchars($d['campaign_title']) ?></td>
                 <td><?= htmlspecialchars($d['donor_name']) ?> <?= $d['is_anonymous'] ? '<span style="font-size:10px;color:#64748B;">(Anonim)</span>' : '' ?></td>
                 <td>
                   <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $d['donor_phone']) ?>" target="_blank" style="color:#059669;font-weight:700;text-decoration:none;">
                     <i class="fab fa-whatsapp"></i> <?= htmlspecialchars($d['donor_phone']) ?>
                   </a>
                 </td>
                 <td style="font-weight:800;color:var(--maroon);"><?= format_rupiah($d['nominal']) ?></td>
                 <td>
                   <?php if (!empty($d['proof_image'])): ?>
                     <a href="<?= htmlspecialchars($d['proof_image']) ?>" target="_blank" style="color:#3B82F6;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                       <i class="fas fa-image"></i> Lihat Bukti
                     </a>
                   <?php else: ?>
                     <span style="color:#94A3B8;">Tidak ada bukti</span>
                   <?php endif; ?>
                 </td>
                 <td style="font-style:italic;color:#475569;">"<?= htmlspecialchars($d['message'] ?: '-') ?>"</td>
                 <td style="text-align: center;">
                   <button class="btn btn-verify" onclick="verifyDonation(<?= $d['id'] ?>)">
                     <i class="fas fa-check"></i> Setujui
                   </button>
                   <button class="btn btn-reject" onclick="rejectDonation(<?= $d['id'] ?>)">
                     <i class="fas fa-times"></i> Tolak
                   </button>
                 </td>
               </tr>
             <?php endforeach; ?>
           <?php endif; ?>
         </tbody>
       </table>
    </div>
  </div>

  <!-- Historical Log Table -->
  <div class="card">
    <h3 class="card-title"><span><i class="fas fa-history"></i> Riwayat Transaksi Donasi</span></h3>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Kode</th>
            <th>Program Kampanye</th>
            <th>Nama Donatur</th>
            <th>Nominal</th>
            <th>Status</th>
            <th>Diverifikasi Oleh</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($allDonations)): ?>
            <tr>
              <td colspan="7" style="text-align:center;padding:24px;color:#64748B;">Belum ada data transaksi donasi.</td>
            </tr>
          <?php else: ?>
            <?php foreach (array_slice($allDonations, 0, 50) as $d): ?>
              <tr>
                <td><?= date('d M Y, H:i', strtotime($d['created_at'])) ?> WIB</td>
                <td><strong><?= htmlspecialchars($d['transaction_code']) ?></strong></td>
                <td><?= htmlspecialchars($d['campaign_title']) ?></td>
                <td><?= htmlspecialchars($d['donor_name']) ?></td>
                <td style="font-weight:800;"><?= format_rupiah($d['nominal']) ?></td>
                <td>
                  <?php if ($d['status'] === 'verified'): ?>
                    <span class="badge badge-verified"><i class="fas fa-check"></i> Verified</span>
                  <?php elseif ($d['status'] === 'pending'): ?>
                    <span class="badge badge-pending"><i class="fas fa-hourglass-half"></i> Pending</span>
                  <?php else: ?>
                    <span class="badge badge-rejected"><i class="fas fa-times"></i> Rejected</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($d['verified_by'] ?: '-') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
async function verifyDonation(id) {
  if (!confirm("Apakah Anda yakin menyetujui dan memverifikasi donasi ini?")) return;
  try {
    const formData = new FormData();
    formData.append('id', id);
    const res = await fetch('/api.php?action=verify_donation', {
      method: 'POST',
      body: formData
    });
    const result = await res.json();
    alert(result.message);
    if (result.status === 'success') {
      window.location.reload();
    }
  } catch (err) {
    alert("Koneksi gagal.");
  }
}

async function rejectDonation(id) {
  if (!confirm("Apakah Anda yakin menolak donasi ini?")) return;
  try {
    const formData = new FormData();
    formData.append('id', id);
    const res = await fetch('/api.php?action=reject_donation', {
      method: 'POST',
      body: formData
    });
    const result = await res.json();
    alert(result.message);
    if (result.status === 'success') {
      window.location.reload();
    }
  } catch (err) {
    alert("Koneksi gagal.");
  }
}
</script>
</body>
</html>
