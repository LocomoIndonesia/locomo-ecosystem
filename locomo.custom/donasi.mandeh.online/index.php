<?php
/**
 * Platform Open Donasi PSB — Landing Page
 * Design Language: Minangkabau Premium (Mobile-First)
 * Default Featured: Aksi Sosial PSB Merdeka 2026
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/CampaignRepository.php';
require_once __DIR__ . '/includes/DonationRepository.php';

// Route /live/{slug} to campaign detail
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($requestUri, PHP_URL_PATH);
if (str_starts_with($path, '/live/')) {
    $slug = substr($path, 6);
    $_GET['slug'] = $slug;
    require __DIR__ . '/campaign.php';
    exit;
}

$campaignRepo = new CampaignRepository();
$donationRepo = new DonationRepository();

$featured = $campaignRepo->getFeaturedCampaign();
$allCampaigns = $campaignRepo->getAllActiveCampaigns();
$globalStats = $campaignRepo->getGlobalStats();
$recentDonations = $donationRepo->getRecentVerifiedGlobalDonations(8);

$deadlineJs = $featured ? date('c', strtotime($featured['deadline'])) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>PSB Open Donasi — Satu Hati Membangun Ranah Pasisia</title>
<meta name="description" content="Platform resmi penggalangan dana dan transparansi sosial terpadu Organisasi Pesisir Selatan Bersatu (PSB). Donasi untuk kemanusiaan, pendidikan, dan kesejahteraan nagari.">
<meta name="theme-color" content="#0B1620">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;0,9..144,900;1,9..144,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root{
    --maroon:#5C0A16;
    --maroon-2:#7A0F1F;
    --red:#C4192F;
    --gold:#C89B3C;
    --gold-light:#E8CD7B;
    --navy:#0B1620;
    --navy-2:#132131;
    --teal:#1E6E72;
    --teal-light:#4FA8A0;
    --cream:#F8F3E7;
    --cream-2:#EFE6D2;
    --ink:#241B14;
    --ink-soft:#4A3D30;
    --white:#FFFFFF;
    --font-display:'Fraunces', serif;
    --font-body:'Plus Jakarta Sans', sans-serif;
    --radius-s:10px;
    --radius-m:18px;
    --radius-l:28px;
    --shadow-card: 0 20px 45px -20px rgba(11,22,32,0.35);
    --shadow-soft: 0 10px 30px -12px rgba(92,10,22,0.25);
    --container-w: 1180px;
  }
  *,*::before,*::after{ box-sizing:border-box; }
  html{ scroll-behavior:smooth; }
  @media (prefers-reduced-motion: reduce){
    html{ scroll-behavior:auto; }
    *,*::before,*::after{ animation-duration:0.001ms !important; transition-duration:0.001ms !important; }
  }
  body{ margin:0; font-family:var(--font-body); color:var(--ink); background:var(--cream); -webkit-font-smoothing:antialiased; overflow-x:hidden; }
  img,svg{ display:block; max-width:100%; }
  a{ color:inherit; text-decoration:none; }
  button{ font-family:inherit; cursor:pointer; }
  h1,h2,h3{ font-family:var(--font-display); margin:0; line-height:1.08; }
  p{ margin:0; }
  .container{ width:100%; max-width:var(--container-w); margin:0 auto; padding:0 22px; }
  :focus-visible{ outline:3px solid var(--gold-light); outline-offset:3px; }

  .eyebrow{
    display:inline-flex; align-items:center; gap:8px;
    font-size:12.5px; font-weight:700; letter-spacing:.14em; text-transform:uppercase;
    padding:8px 16px; border-radius:999px;
  }

  /* BUTTONS */
  .btn{
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    padding:14px 26px; border-radius:999px; border:1px solid transparent;
    font-weight:700; font-size:15px; letter-spacing:.01em;
    transition:transform .18s ease, box-shadow .18s ease, background .18s ease; white-space:nowrap;
  }
  .btn:active{ transform:translateY(1px) scale(.99); }
  .btn--primary{ background:linear-gradient(135deg, var(--gold-light), var(--gold)); color:var(--maroon); box-shadow:0 14px 30px -12px rgba(200,155,60,0.55); }
  .btn--primary:hover{ box-shadow:0 18px 36px -12px rgba(200,155,60,0.7); transform:translateY(-1px); }
  .btn--dark{ background:var(--maroon); color:var(--cream); box-shadow:0 14px 30px -14px rgba(92,10,22,0.6); }
  .btn--dark:hover{ background:var(--maroon-2); }
  .btn--ghost{ background:rgba(92,10,22,0.06); color:var(--maroon); }
  .btn--ghost:hover{ background:rgba(92,10,22,0.12); }
  .btn--block{ width:100%; }
  .btn svg{ width:18px; height:18px; flex:none; }

  .roofline{ display:block; width:100%; height:34px; }
  @media (min-width:720px){ .roofline{ height:46px; } }

  /* HEADER */
  .site-header{
    position:sticky; top:0; z-index:40;
    background:rgba(11,22,32,0.86); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px);
  }
  .site-header__row{ display:flex; align-items:center; justify-content:space-between; padding:12px 0; }
  .brand{ display:flex; align-items:center; gap:10px; color:var(--cream); }
  .brand__mark{ width:38px; height:38px; flex:none; }
  .brand__text{ display:flex; flex-direction:column; line-height:1.15; }
  .brand__title{ font-family:var(--font-display); font-weight:700; font-size:15.5px; letter-spacing:.01em; }
  .brand__sub{ font-size:10.5px; letter-spacing:.12em; text-transform:uppercase; color:var(--gold-light); }

  /* HERO */
  .hero{
    position:relative;
    background:
      radial-gradient(60% 55% at 82% 8%, rgba(78,168,160,0.28), transparent 60%),
      radial-gradient(50% 45% at 8% 92%, rgba(200,155,60,0.16), transparent 60%),
      linear-gradient(160deg, var(--navy) 0%, var(--navy-2) 55%, var(--maroon) 130%);
    color:var(--cream); padding:60px 0 0; overflow:hidden;
  }
  .hero__content{ position:relative; z-index:2; max-width:720px; }
  .hero .eyebrow{ background:rgba(248,243,231,0.1); color:var(--gold-light); border:1px solid rgba(200,155,60,0.4); }
  .hero h1{ margin-top:20px; font-size:clamp(32px, 7vw, 56px); font-weight:900; color:var(--cream); }
  .hero h1 em{ font-style:normal; color:var(--gold-light); }
  .hero__lead{ margin-top:18px; font-size:16.5px; line-height:1.65; color:rgba(248,243,231,0.85); max-width:56ch; }
  .hero__cta{ display:flex; flex-wrap:wrap; gap:12px; margin-top:26px; }
  .hero__trust{ margin-top:22px; display:flex; flex-wrap:wrap; gap:16px; font-size:12.5px; color:rgba(248,243,231,0.65); }
  .hero__trust span{ display:inline-flex; align-items:center; gap:6px; }
  .hero__trust svg{ width:14px; height:14px; color:var(--gold-light); }
  .hero__roofline-wrap{ margin-top:48px; position:relative; z-index:2; }

  /* STATS CARD */
  .stats-section{ background:var(--cream); padding:0 0 60px; margin-top:-46px; position:relative; z-index:3; }
  .stats-card{
    background:var(--white); border-radius:var(--radius-l); box-shadow:var(--shadow-card);
    padding:28px 24px; border:1px solid rgba(92,10,22,0.06);
    display:grid; grid-template-columns:repeat(2,1fr); gap:16px;
  }
  @media (min-width:720px){ .stats-card{ grid-template-columns:repeat(4,1fr); } }
  .stat-col{ text-align:center; padding:14px 6px; }
  .stat-col + .stat-col{ border-left:1px solid var(--cream-2); }
  @media (max-width:719px){ .stat-col:nth-child(2){ border-left:1px solid var(--cream-2); } .stat-col:nth-child(3){ border-left:none; } }
  .stat-number{ font-family:var(--font-display); font-weight:800; font-size:22px; color:var(--maroon); font-variant-numeric:tabular-nums; }
  .stat-label{ font-size:11px; color:var(--ink-soft); margin-top:2px; letter-spacing:.02em; font-weight:600; text-transform:uppercase; }

  /* SECTIONS */
  .section{ padding:70px 0; }
  .section-head{ max-width:640px; }
  .section-head .eyebrow{ background:rgba(92,10,22,0.07); color:var(--maroon); }
  .section-head--dark .eyebrow{ background:rgba(200,155,60,0.14); color:var(--gold-light); }
  .section-head h2{ margin-top:14px; font-size:clamp(26px,4.5vw,38px); color:var(--ink); font-weight:700; }
  .section-head--dark h2{ color:var(--cream); }
  .section-head p{ margin-top:12px; font-size:15.5px; line-height:1.65; color:var(--ink-soft); }
  .section-head--dark p{ color:rgba(248,243,231,0.75); }

  /* FEATURED CAMPAIGN CARD */
  .featured-wrap{ margin-top:34px; }
  .featured-card{
    background:var(--white); border-radius:var(--radius-l); overflow:hidden;
    box-shadow:var(--shadow-card); border:2px solid rgba(200,155,60,0.3);
    display:grid; grid-template-columns:1fr;
  }
  @media (min-width:820px){ .featured-card{ grid-template-columns:1fr 1.2fr; } }
  .featured-media{ position:relative; min-height:260px; background:var(--navy); overflow:hidden; }
  .featured-media img{ width:100%; height:100%; object-fit:cover; }
  .featured-badge{
    position:absolute; top:18px; left:18px; z-index:2;
    display:inline-flex; align-items:center; gap:6px;
    background:var(--maroon); color:var(--gold-light); font-size:11px; font-weight:800;
    padding:7px 14px; border-radius:999px; border:1px solid var(--gold); letter-spacing:.08em; text-transform:uppercase;
  }
  .featured-badge svg{ width:14px; height:14px; }
  .featured-body{ padding:32px; display:flex; flex-direction:column; justify-content:space-between; }
  .featured-tag{ font-size:11.5px; letter-spacing:.1em; text-transform:uppercase; color:var(--maroon); font-weight:700; }
  .featured-title{ font-size:clamp(22px,3.5vw,28px); font-weight:800; color:var(--ink); line-height:1.25; margin-top:8px; }
  .featured-desc{ font-size:14px; color:var(--ink-soft); margin-top:10px; line-height:1.6; }

  .featured-progress{ margin-top:24px; }
  .featured-funds{ display:flex; justify-content:space-between; align-items:baseline; margin-bottom:8px; }
  .featured-funds__collected{ font-family:var(--font-display); font-weight:800; font-size:24px; color:var(--maroon); font-variant-numeric:tabular-nums; }
  .featured-funds__target{ font-size:13px; color:var(--ink-soft); }
  .progress-bar{ height:14px; border-radius:999px; background:var(--cream-2); overflow:hidden; }
  .progress-bar__fill{ height:100%; border-radius:999px; background:linear-gradient(90deg, var(--gold), var(--red)); transition:width 1s cubic-bezier(.2,.7,.3,1); }

  .featured-meta{ margin-top:16px; display:flex; flex-wrap:wrap; gap:16px; font-size:12.5px; color:var(--ink-soft); }
  .featured-meta span{ display:inline-flex; align-items:center; gap:5px; }
  .featured-meta svg{ width:14px; height:14px; color:var(--maroon); }

  .featured-cta{ margin-top:20px; }

  /* COUNTDOWN MINI */
  .countdown-mini{ margin-top:18px; display:flex; gap:10px; flex-wrap:wrap; }
  .cd-unit{ background:var(--cream); border:1px solid var(--cream-2); border-radius:var(--radius-s); padding:10px 14px; text-align:center; min-width:60px; }
  .cd-unit__val{ font-family:var(--font-body); font-weight:800; font-size:20px; color:var(--maroon); font-variant-numeric:tabular-nums; }
  .cd-unit__lbl{ font-size:9px; letter-spacing:.1em; text-transform:uppercase; color:var(--ink-soft); margin-top:2px; }

  /* CAMPAIGN GRID */
  .campaign-grid{ margin-top:34px; display:grid; gap:20px; grid-template-columns:1fr; }
  @media (min-width:640px){ .campaign-grid{ grid-template-columns:repeat(2,1fr); } }
  @media (min-width:960px){ .campaign-grid{ grid-template-columns:repeat(3,1fr); } }
  .campaign-card{
    background:var(--white); border-radius:var(--radius-m); overflow:hidden;
    box-shadow:var(--shadow-soft); border:1px solid rgba(92,10,22,0.06);
    transition:transform .25s ease, box-shadow .25s ease;
    display:flex; flex-direction:column;
  }
  .campaign-card:hover{ transform:translateY(-6px); box-shadow:var(--shadow-card); }
  .card-thumb{ height:180px; background:var(--navy-2); position:relative; overflow:hidden; }
  .card-thumb img{ width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
  .campaign-card:hover .card-thumb img{ transform:scale(1.05); }
  .card-tag{
    position:absolute; bottom:12px; left:12px;
    background:rgba(11,22,32,0.85); backdrop-filter:blur(4px);
    color:var(--gold-light); padding:4px 10px; border-radius:4px; font-size:11px; font-weight:700;
  }
  .card-content{ padding:22px; flex:1; display:flex; flex-direction:column; }
  .card-title{ font-size:17px; font-weight:700; color:var(--ink); margin-bottom:8px; line-height:1.3; }
  .card-desc{ font-size:13px; color:var(--ink-soft); line-height:1.5; margin-bottom:18px; flex:1; }

  .card-progress{ margin-top:auto; }
  .card-funds{ display:flex; justify-content:space-between; align-items:baseline; margin-bottom:6px; }
  .card-collected{ font-family:var(--font-display); font-weight:800; font-size:16px; color:var(--maroon); }
  .card-target-pct{ font-size:12px; font-weight:700; color:var(--ink-soft); }
  .card-bar{ height:8px; border-radius:999px; background:var(--cream-2); overflow:hidden; margin-bottom:14px; }
  .card-bar__fill{ height:100%; border-radius:999px; background:linear-gradient(90deg, var(--gold), var(--red)); }
  .card-meta{ display:flex; justify-content:space-between; font-size:12px; color:var(--ink-soft); margin-bottom:14px; }
  .card-meta span{ display:inline-flex; align-items:center; gap:4px; }
  .card-meta svg{ width:12px; height:12px; }

  /* LIVE FEED */
  .feed-section{ background:var(--navy); position:relative; overflow:hidden; }
  .feed-grid{ margin-top:28px; display:grid; gap:12px; grid-template-columns:1fr; }
  @media (min-width:640px){ .feed-grid{ grid-template-columns:repeat(2,1fr); } }
  @media (min-width:960px){ .feed-grid{ grid-template-columns:repeat(4,1fr); } }
  .feed-item{
    background:rgba(248,243,231,0.05); border:1px solid rgba(248,243,231,0.08);
    border-radius:var(--radius-s); padding:16px; transition:background .2s;
  }
  .feed-item:hover{ background:rgba(248,243,231,0.08); }
  .feed-name{ font-weight:700; font-size:14px; color:var(--cream); }
  .feed-amount{ font-family:var(--font-display); font-weight:800; color:var(--gold-light); font-size:16px; margin:4px 0; font-variant-numeric:tabular-nums; }
  .feed-campaign{ font-size:11px; color:rgba(248,243,231,0.5); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .feed-badge{ display:inline-flex; align-items:center; gap:4px; font-size:10px; font-weight:700; color:var(--teal-light); margin-top:6px; }
  .feed-badge svg{ width:12px; height:12px; }

  /* VALUES SECTION */
  .values-section{ background:var(--cream-2); }
  .values-grid{ margin-top:34px; display:grid; gap:18px; grid-template-columns:1fr; }
  @media (min-width:720px){ .values-grid{ grid-template-columns:repeat(3,1fr); } }
  .value-card{
    background:var(--white); border-radius:var(--radius-m); padding:26px;
    border:1px solid rgba(92,10,22,0.06); box-shadow:var(--shadow-soft);
    text-align:center;
  }
  .value-card__icon{
    width:56px; height:56px; border-radius:14px; margin:0 auto 16px;
    display:flex; align-items:center; justify-content:center;
    background:rgba(200,155,60,0.15); color:var(--maroon);
  }
  .value-card__icon svg{ width:26px; height:26px; }
  .value-card h3{ font-size:18px; font-weight:700; margin-bottom:8px; }
  .value-card p{ font-size:13.5px; color:var(--ink-soft); line-height:1.55; }

  /* FOOTER */
  .site-footer{ background:var(--navy); color:rgba(248,243,231,0.6); padding:40px 0 28px; }
  .footer-grid{ display:grid; gap:30px; grid-template-columns:1fr; }
  @media (min-width:720px){ .footer-grid{ grid-template-columns:1.5fr 1fr 1fr; } }
  .footer-brand{ display:flex; align-items:center; gap:10px; color:rgba(248,243,231,0.85); margin-bottom:12px; }
  .footer-brand .brand__mark{ width:32px; height:32px; }
  .footer-brand .brand__title{ font-family:var(--font-display); font-weight:700; font-size:16px; }
  .footer-link{ display:block; font-size:13px; color:rgba(248,243,231,0.5); margin-bottom:8px; transition:color .2s; }
  .footer-link:hover{ color:var(--gold-light); }
  .footer-bottom{ margin-top:30px; padding-top:20px; border-top:1px solid rgba(248,243,231,0.1); display:flex; flex-wrap:wrap; justify-content:space-between; gap:10px; font-size:12px; }

  /* REVEAL */
  .reveal{ opacity:0; transform:translateY(18px); transition:opacity .7s ease, transform .7s ease; }
  .reveal.is-visible{ opacity:1; transform:translateY(0); }
</style>
</head>
<body>

<!-- HEADER -->
<header class="site-header">
  <div class="container site-header__row">
    <a href="/" class="brand" aria-label="Beranda">
      <svg class="brand__mark" viewBox="0 0 48 48" fill="none" aria-hidden="true">
        <circle cx="24" cy="24" r="22" stroke="#C89B3C" stroke-width="2"/>
        <path d="M24 33c-6-4.2-10-7.8-10-12.4A5.6 5.6 0 0 1 24 17a5.6 5.6 0 0 1 10 3.6C34 25.2 30 28.8 24 33Z" fill="#C4192F"/>
        <path d="M17 16.5 20.5 22h-3l3.5 6-6-6.5h3L14.5 16.5Z" fill="#F8F3E7"/>
        <path d="M31 16.5 34.5 22h-3l3.5 6-6-6.5h3L28.5 16.5Z" fill="#F8F3E7"/>
      </svg>
      <span class="brand__text">
        <span class="brand__title">donasi.mandeh.online</span>
        <span class="brand__sub">Pesisir Selatan Bersatu</span>
      </span>
    </a>
    <a href="<?= $featured ? '/live/' . htmlspecialchars($featured['slug']) : '#' ?>" class="btn btn--primary" style="padding:10px 20px; font-size:13.5px;">Donasi Sekarang</a>
  </div>
</header>

<!-- HERO -->
<section class="hero">
  <div class="container" style="padding-bottom:0;">
    <div class="hero__content">
      <span class="eyebrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M12 21s-7-4.5-7-11a7 7 0 0 1 14 0c0 6.5-7 11-7 11z"/></svg>Platform Terpadu Donasi &amp; Kedermawanan Minangkabau</span>
      <h1>Satu Hati <em>Membangun</em> Ranah Pasisia</h1>
      <p class="hero__lead">
        <em>&ldquo;Barek Samo Dipikua, Ringan Samo Dijinjiang &mdash; Ka Mudiak Saantak Galah, Ka Ilia Sarangkuah Dayuang.&rdquo;</em><br><br>
        Gerakan sosial mandiri masyarakat dan perantau Pesisir Selatan untuk keadilan sosial, pendidikan, dan kesejahteraan nagari melalui gotong royong digital yang transparan.
      </p>
      <div class="hero__cta">
        <a href="<?= $featured ? '/live/' . htmlspecialchars($featured['slug']) : '#' ?>" class="btn btn--primary">Lihat Program Utama</a>
        <a href="#program" class="btn" style="background:transparent; border-color:rgba(248,243,231,0.55); color:var(--cream);">Jelajahi Semua Program</a>
      </div>
      <div class="hero__trust">
        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>100% Transparan</span>
        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7-4.5-7-11a7 7 0 0 1 14 0c0 6.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>15 Kecamatan</span>
        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>10 Tahun Mengabdi</span>
      </div>
    </div>
  </div>
  <div class="hero__roofline-wrap">
    <svg class="roofline" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true">
      <defs>
        <pattern id="gonjongHero" width="120" height="80" patternUnits="userSpaceOnUse">
          <path d="M0,80 C10,22 30,2 60,2 C90,2 110,22 120,80 Z" fill="#C89B3C" opacity="0.16"/>
          <path d="M0,80 C10,38 30,22 60,22 C90,22 110,38 120,80 Z" fill="#F8F3E7" opacity="1"/>
        </pattern>
      </defs>
      <rect width="1440" height="80" fill="url(#gonjongHero)"/>
    </svg>
  </div>
</section>

<!-- STATS BAR -->
<section class="stats-section">
  <div class="container">
    <div class="stats-card reveal">
      <div class="stat-col">
        <div class="stat-number"><?= format_rupiah($globalStats['total_verified_funds']) ?></div>
        <div class="stat-label">Total Donasi Terverifikasi</div>
      </div>
      <div class="stat-col">
        <div class="stat-number"><?= $globalStats['total_verified_donors'] ?></div>
        <div class="stat-label">Donatur Berpartisipasi</div>
      </div>
      <div class="stat-col">
        <div class="stat-number"><?= $globalStats['total_active_campaigns'] ?></div>
        <div class="stat-label">Program Kebaikan Aktif</div>
      </div>
      <div class="stat-col">
        <div class="stat-number">100%</div>
        <div class="stat-label">Transparansi</div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURED CAMPAIGN -->
<?php if ($featured): ?>
<section class="section" style="padding-top:0;">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.27 5.82 22 7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>Program Utama Berjalan</span>
      <h2>Inisiatif Prioritas PSB</h2>
      <p>Program kemanusiaan yang sedang berjalan dan membutuhkan partisipasi Anda saat ini.</p>
    </div>

    <div class="featured-wrap reveal">
      <div class="featured-card">
        <div class="featured-media">
          <img src="/docs.konsep/assets/Cover_Proposal.png" alt="<?= htmlspecialchars($featured['title']) ?>">
          <div class="featured-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" style="width:14px;height:14px;"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            SEDANG BERLANGSUNG
          </div>
        </div>
        <div class="featured-body">
          <div>
            <div class="featured-tag"><?= htmlspecialchars($featured['category']) ?></div>
            <h3 class="featured-title"><?= htmlspecialchars($featured['title']) ?></h3>
            <p class="featured-desc"><?= htmlspecialchars($featured['subtitle']) ?></p>
          </div>

          <div class="featured-progress">
            <div class="featured-funds">
              <div class="featured-funds__collected"><?= format_rupiah($featured['collected_amount']) ?></div>
              <div class="featured-funds__target">dari <?= format_rupiah($featured['target_amount']) ?></div>
            </div>
            <div class="progress-bar"><div class="progress-bar__fill" style="width:<?= $featured['progress_percent'] ?>%"></div></div>

            <div class="featured-meta">
              <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg><?= $featured['donors_count'] ?> Donatur</span>
              <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg><?= $featured['remaining_days'] ?> Hari Tersisa</span>
              <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg><?= $featured['progress_percent'] ?>% Tercapai</span>
            </div>

            <!-- Countdown Mini -->
            <div class="countdown-mini" id="countdownMini">
              <div class="cd-unit"><div class="cd-unit__val" id="cdDays">00</div><div class="cd-unit__lbl">Hari</div></div>
              <div class="cd-unit"><div class="cd-unit__val" id="cdHours">00</div><div class="cd-unit__lbl">Jam</div></div>
              <div class="cd-unit"><div class="cd-unit__val" id="cdMins">00</div><div class="cd-unit__lbl">Menit</div></div>
              <div class="cd-unit"><div class="cd-unit__val" id="cdSecs">00</div><div class="cd-unit__lbl">Detik</div></div>
            </div>

            <div class="featured-cta">
              <a href="/live/<?= htmlspecialchars($featured['slug']) ?>" class="btn btn--primary btn--block">Donasi Sekarang &amp; Lihat Detail</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ALL CAMPAIGNS -->
<section class="section" id="program" style="padding-top:0;">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Gerakan Kebaikan</span>
      <h2>Seluruh Program Donasi Aktif</h2>
      <p>Eksplorasi berbagai inisiatif pemberdayaan dan kesejahteraan masyarakat di Pesisir Selatan. Pilih program yang paling Anda ingin dukung.</p>
    </div>

    <div class="campaign-grid">
      <?php foreach ($allCampaigns as $c): ?>
        <a href="/live/<?= htmlspecialchars($c['slug']) ?>" class="campaign-card reveal">
          <div class="card-thumb">
            <img src="/docs.konsep/assets/<?= $c['is_featured'] ? 'Cover_Proposal.png' : '02_Gallery-Kegiatan-dan Pergerakan.png' ?>" alt="<?= htmlspecialchars($c['title']) ?>">
            <div class="card-tag"><?= htmlspecialchars($c['category']) ?></div>
          </div>
          <div class="card-content">
            <h3 class="card-title"><?= htmlspecialchars($c['title']) ?></h3>
            <p class="card-desc"><?= htmlspecialchars(mb_strimwidth($c['subtitle'] ?? '', 0, 100, '...')) ?></p>
            <div class="card-progress">
              <div class="card-funds">
                <div class="card-collected"><?= format_rupiah($c['collected_amount']) ?></div>
                <div class="card-target-pct"><?= $c['progress_percent'] ?>%</div>
              </div>
              <div class="card-bar"><div class="card-bar__fill" style="width:<?= $c['progress_percent'] ?>%"></div></div>
              <div class="card-meta">
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg><?= $c['donors_count'] ?> Donatur</span>
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg><?= $c['remaining_days'] ?> Hari</span>
              </div>
              <span class="btn btn--dark" style="font-size:13px; padding:12px 20px;">Buka Program</span>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- LIVE FEED TRANSPARANSI -->
<section class="section feed-section">
  <div class="container">
    <div class="section-head section-head--dark reveal">
      <span class="eyebrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>Transparansi Real-Time</span>
      <h2>Donasi Terverifikasi Terkini</h2>
      <p>Setiap donasi yang telah dikonfirmasi dan diverifikasi oleh Panitia PSB ditampilkan secara terbuka untuk akuntabilitas penuh.</p>
    </div>
    <div class="feed-grid">
      <?php foreach ($recentDonations as $d): ?>
        <div class="feed-item reveal">
          <div class="feed-name"><?= $d['display_name'] ?></div>
          <div class="feed-amount"><?= $d['formatted_nominal'] ?></div>
          <div class="feed-campaign"><?= htmlspecialchars($d['campaign_title']) ?></div>
          <div class="feed-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>Terverifikasi</div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($recentDonations)): ?>
        <div class="feed-item reveal" style="grid-column:1/-1; text-align:center; padding:40px 20px;">
          <p style="color:rgba(248,243,231,0.5);font-size:14px;">Belum ada donasi terverifikasi. Jadilah donatur pertama!</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- VALUES / PILAR PSB -->
<section class="section values-section">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Amanah</span>
      <h2>Pilar Integritas PSB</h2>
      <p>Komitmen kami untuk menjaga kepercayaan setiap donatur dengan prinsip keterbukaan, ketepatan, dan gotong royong terpadu.</p>
    </div>
    <div class="values-grid">
      <div class="value-card reveal">
        <div class="value-card__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg></div>
        <h3>Transparan 100%</h3>
        <p>Setiap rupiah donasi tercatat dan dilaporkan secara publik &mdash; termasuk rincian penyaluran hingga penerima manfaat akhir.</p>
      </div>
      <div class="value-card reveal">
        <div class="value-card__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7-4.5-7-11a7 7 0 0 1 14 0c0 6.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg></div>
        <h3>Door-to-Door</h3>
        <p>Penyaluran langsung ke tangan penerima manfaat di seluruh kecamatan Pesisir Selatan, tanpa perantara yang tidak perlu.</p>
      </div>
      <div class="value-card reveal">
        <div class="value-card__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
        <h3>Gotong Royong</h3>
        <p>Dikelola swadaya oleh relawan PSB yang bersedia mendedikasikan waktu dan tenaga demi kemajuan kampung halaman.</p>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-brand">
          <svg class="brand__mark" viewBox="0 0 48 48" fill="none" aria-hidden="true" style="width:32px;height:32px;">
            <circle cx="24" cy="24" r="22" stroke="#C89B3C" stroke-width="2"/>
            <path d="M24 33c-6-4.2-10-7.8-10-12.4A5.6 5.6 0 0 1 24 17a5.6 5.6 0 0 1 10 3.6C34 25.2 30 28.8 24 33Z" fill="#C4192F"/>
          </svg>
          <span class="brand__title" style="font-family:var(--font-display);font-weight:700;font-size:16px;color:rgba(248,243,231,0.85);">Pesisir Selatan Bersatu</span>
        </div>
        <p style="font-size:13px;line-height:1.65;color:rgba(248,243,231,0.5);max-width:40ch;">
          Organisasi sosial kepemudaan yang lahir dari keresahan kolektif anak muda Pesisir Selatan. Sejak 2016, konsisten menjadi garda terdepan aksi sosial.
        </p>
      </div>
      <div>
        <p style="font-size:12px;font-weight:700;color:var(--gold-light);margin-bottom:12px;letter-spacing:.08em;text-transform:uppercase;">Akuntabilitas</p>
        <p style="font-size:13px;color:rgba(248,243,231,0.6);line-height:1.7;">
          <strong style="color:rgba(248,243,231,0.8);"><?= CONFIG_REK_BANK ?></strong>: <?= CONFIG_REK_NOMOR ?><br>
          a.n. <?= CONFIG_REK_ATAS_NAMA ?><br>
          Ketua Pelaksana: 0812-6073-1173
        </p>
      </div>
      <div>
        <p style="font-size:12px;font-weight:700;color:var(--gold-light);margin-bottom:12px;letter-spacing:.08em;text-transform:uppercase;">Navigasi</p>
        <a href="/" class="footer-link">Beranda Platform</a>
        <a href="/live/aksi-sosial-psb-merdeka-2026" class="footer-link">Program Merdeka 2026</a>
        <a href="/admin.php" class="footer-link">Portal Admin PSB</a>
        <a href="mailto:pessel.bersatu@gmail.com" class="footer-link">pessel.bersatu@gmail.com</a>
      </div>
    </div>
    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> Pesisir Selatan Bersatu &middot; donasi.mandeh.online</span>
      <span>Powered by Locomo Federated Ecosystem</span>
    </div>
  </div>
</footer>

<script>
<?php if ($featured): ?>
/* Countdown */
const DEADLINE = '<?= $deadlineJs ?>';
function updateCountdown(){
  const diff = new Date(DEADLINE).getTime() - Date.now();
  if(diff <= 0){ document.querySelectorAll('.cd-unit__val').forEach(e => e.textContent='00'); return; }
  document.getElementById('cdDays').textContent = String(Math.floor(diff/86400000)).padStart(2,'0');
  document.getElementById('cdHours').textContent = String(Math.floor((diff%86400000)/3600000)).padStart(2,'0');
  document.getElementById('cdMins').textContent = String(Math.floor((diff%3600000)/60000)).padStart(2,'0');
  document.getElementById('cdSecs').textContent = String(Math.floor((diff%60000)/1000)).padStart(2,'0');
}
updateCountdown(); setInterval(updateCountdown, 1000);
<?php endif; ?>

/* Scroll reveal */
if('IntersectionObserver' in window){
  const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => { if(entry.isIntersecting){ entry.target.classList.add('is-visible'); io.unobserve(entry.target); } });
  }, { threshold: 0.1 });
  document.querySelectorAll('.reveal').forEach(el => io.observe(el));
} else {
  document.querySelectorAll('.reveal').forEach(el => el.classList.add('is-visible'));
}
</script>
</body>
</html>
