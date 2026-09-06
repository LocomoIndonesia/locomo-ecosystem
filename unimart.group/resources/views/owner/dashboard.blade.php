<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasbor Pemilik Bisnis — Unimart.group</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,500&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --primary: hsl(28, 95%, 50%);
            --primary-hover: hsl(28, 100%, 46%);
            --primary-gradient: linear-gradient(135deg, hsl(28, 95%, 52%), hsl(22, 95%, 45%));
            --primary-soft: rgba(249, 115, 22, 0.12);
            --primary-ring: rgba(249, 115, 22, 0.25);
            --bg: hsl(220, 26%, 8%);
            --surface: hsl(220, 24%, 12%);
            --surface-accent: hsl(220, 22%, 16%);
            --surface-hover: hsl(220, 22%, 20%);
            --text-main: hsl(220, 20%, 96%);
            --text-muted: hsl(220, 14%, 68%);
            --text-dim: hsl(220, 12%, 48%);
            --border: rgba(255, 255, 255, 0.08);
            --border-glow: rgba(249, 115, 22, 0.4);
            --radius-xl: 20px;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
            --shadow-lg: 0 20px 40px -12px rgba(0, 0, 0, 0.5);
            --shadow-sm: 0 4px 12px rgba(0, 0, 0, 0.25);
            --success: hsl(150, 60%, 42%);
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 10% 0%, rgba(249, 115, 22, 0.07) 0%, transparent 40%),
                radial-gradient(circle at 90% 100%, rgba(249, 115, 22, 0.05) 0%, transparent 40%);
        }

        /* Sticky App Header */
        header.dashboard-header {
            background: rgba(11, 15, 23, 0.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            padding: 0.85rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        @media (min-width: 768px) {
            header.dashboard-header {
                padding: 1rem 2rem;
            }
        }

        .brand-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .brand-logo span.tag {
            font-family: 'Outfit', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            background: var(--primary-soft);
            color: var(--primary);
            border: 1px solid var(--border-glow);
            padding: 0.15rem 0.5rem;
            border-radius: 20px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .badge-package {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.25rem 0.65rem;
            border-radius: 20px;
            background: var(--primary-soft);
            color: var(--primary);
            border: 1px solid var(--border-glow);
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .badge-package.vip {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.15), rgba(255, 165, 0, 0.2));
            color: #ffd700;
            border-color: rgba(255, 215, 0, 0.4);
        }

        .btn-logout {
            background: transparent;
            color: #ff453a;
            border: 1px solid rgba(255, 69, 58, 0.25);
            padding: 0.35rem 0.75rem;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            font-size: 0.8rem;
            transition: var(--transition);
        }

        .btn-logout:hover {
            background: rgba(255, 69, 58, 0.1);
        }

        /* Workspace Container */
        main {
            padding: 1rem 1rem 2.5rem;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            flex-grow: 1;
        }

        @media (min-width: 768px) {
            main {
                padding: 1.75rem 2rem 3rem;
            }
        }

        /* Hero Welcome Banner */
        .hero-banner {
            background: linear-gradient(135deg, var(--surface), var(--surface-accent));
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            padding: 1.25rem 1.25rem 1.4rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .hero-top {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            margin-bottom: 0.85rem;
        }

        .hero-avatar {
            font-size: 2rem;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--surface-accent);
            border: 1.5px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .hero-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-main);
            line-height: 1.25;
        }

        .hero-subtitle {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 0.2rem;
        }

        .store-chips-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            margin-top: 0.75rem;
        }

        .store-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid var(--border);
            padding: 0.3rem 0.65rem;
            border-radius: 20px;
            font-size: 0.78rem;
            color: var(--text-main);
        }

        .store-chip.domain {
            color: var(--primary);
            font-weight: 600;
        }

        /* Horizontal Scrollable Navigation Tabs (Mobile-First Segmented Chips) */
        .nav-scroll-wrap {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding: 0.5rem 0.2rem;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            margin: 1.25rem 0 1.5rem;
        }

        .nav-scroll-wrap::-webkit-scrollbar {
            display: none;
        }

        .nav-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.65rem 1.1rem;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 30px;
            color: var(--text-muted);
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            transition: var(--transition);
            flex-shrink: 0;
        }

        .nav-pill:active {
            transform: scale(0.96);
        }

        .nav-pill.active {
            background: var(--primary-gradient);
            color: #ffffff;
            border-color: transparent;
            box-shadow: 0 4px 14px rgba(249, 115, 22, 0.35);
        }

        /* Quick Action Shortcuts */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin-bottom: 1.75rem;
        }

        @media (min-width: 640px) {
            .quick-actions-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .quick-action-btn {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 0.85rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--text-main);
            font-weight: 600;
            font-size: 0.85rem;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }

        .quick-action-btn:hover {
            border-color: var(--border-glow);
            transform: translateY(-2px);
            background: var(--surface-hover);
        }

        .quick-action-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .quick-action-icon.pos {
            background: rgba(249, 115, 22, 0.15);
            color: var(--primary);
        }

        .quick-action-icon.kds {
            background: rgba(52, 211, 153, 0.15);
            color: #34d399;
        }

        .quick-action-icon.stock {
            background: rgba(96, 165, 250, 0.15);
            color: #60a5fa;
        }

        /* Key Metrics / Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 1.25rem;
            }
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.1rem;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .stat-card-title {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.4rem;
        }

        .stat-card-value {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--primary);
            font-variant-numeric: tabular-nums;
            line-height: 1.2;
        }

        .stat-card-sub {
            font-size: 0.72rem;
            color: var(--text-dim);
            margin-top: 0.35rem;
        }

        /* Section Heading */
        .section-header-wrap {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.1rem;
        }

        .section-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-main);
        }

        /* Ecosystem Features Grid (Strictly 1 Column on Mobile to prevent clipping) */
        .features-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            min-width: 0;
            width: 100%;
        }

        @media (min-width: 768px) {
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.25rem;
            }
        }

        @media (min-width: 1100px) {
            .features-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 1.5rem;
            }
        }

        .feature-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.35rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            min-width: 0;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .feature-card:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 255, 255, 0.15);
        }

        .feature-card.full-span {
            grid-column: 1 / -1;
        }

        .feature-head h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 0.45rem;
            color: var(--text-main);
        }

        .feature-head p {
            color: var(--text-muted);
            font-size: 0.82rem;
            line-height: 1.45;
        }

        .feature-foot {
            margin-top: 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
        }

        .feature-action-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: var(--transition);
        }

        .feature-action-link:hover {
            color: var(--primary-hover);
        }

        .status-pill-active {
            font-size: 0.68rem;
            font-weight: 700;
            color: var(--success);
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            padding: 0.2rem 0.55rem;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        /* Locked Feature Overlay */
        .locked-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(11, 15, 23, 0.92);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1.25rem;
            text-align: center;
            z-index: 10;
        }

        .lock-badge {
            font-size: 1.8rem;
            margin-bottom: 0.35rem;
        }

        .lock-desc {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-bottom: 0.85rem;
            line-height: 1.35;
            max-width: 260px;
        }

        .btn-upgrade-pill {
            background: var(--primary-gradient);
            color: #ffffff;
            border: none;
            padding: 0.45rem 1.1rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.75rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.35);
            transition: var(--transition);
        }

        .btn-upgrade-pill:hover {
            transform: scale(1.03);
        }

        /* Personalization Form Layout */
        .personalize-form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.85rem;
            margin-top: 1rem;
        }

        @media (min-width: 640px) {
            .personalize-form-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .form-mini-group {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .form-mini-label {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .form-mini-input {
            width: 100%;
            height: 44px;
            padding: 0 0.85rem;
            background: var(--surface-accent);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: var(--text-main);
            font-size: 0.88rem;
            outline: none;
            transition: var(--transition);
        }

        .form-mini-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-ring);
        }

        textarea.form-mini-input {
            height: auto;
            min-height: 60px;
            padding: 0.65rem 0.85rem;
            resize: vertical;
        }

        .btn-save-personalize {
            width: 100%;
            height: 46px;
            margin-top: 0.5rem;
            background: var(--primary-gradient);
            color: #ffffff;
            font-weight: 700;
            font-size: 0.88rem;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
            transition: var(--transition);
        }

        .btn-save-personalize:active {
            transform: scale(0.98);
        }

        /* Floating AI Assistant */
        .floating-ai-btn {
            position: fixed;
            bottom: 1.5rem;
            right: 1.25rem;
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: var(--primary-gradient);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(249, 115, 22, 0.4);
            z-index: 999;
            transition: var(--transition);
        }

        .floating-ai-btn:active {
            transform: scale(0.92);
        }

        .ai-online-dot {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            background: var(--success);
            border: 2px solid var(--bg);
            border-radius: 50%;
        }

        .ai-chat-window {
            position: fixed;
            bottom: 80px;
            right: 1rem;
            width: min(350px, calc(100vw - 2rem));
            height: 440px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 1000;
        }

        .chat-messages::-webkit-scrollbar {
            width: 4px;
        }
        .chat-messages::-webkit-scrollbar-thumb {
            background: var(--surface-accent);
            border-radius: 4px;
        }

        .suggestion-chip {
            padding: 0.35rem 0.65rem;
            background: var(--surface-accent);
            border: 1px solid var(--border);
            color: var(--text-muted);
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            white-space: nowrap;
        }
        .suggestion-chip:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-soft);
        }
    </style>
</head>
<body>

    <!-- App Header Bar -->
    <header class="dashboard-header">
        <a href="/" class="brand-logo">
            Unimart<span class="tag">dasbor</span>
        </a>

        <div class="header-actions">
            <span class="badge-package @if($package === 'Premium VIP') vip @endif">
                {{ $package }}
            </span>
            <form action="{{ route('owner.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">Keluar</button>
            </form>
        </div>
    </header>

    <!-- Main Workspace -->
    <main>
        <!-- Hero Welcome Banner -->
        <div class="hero-banner">
            <div class="hero-top" x-data="{ greetingTime: '' }" x-init="const hrs = new Date().getHours(); greetingTime = (hrs >= 5 && hrs < 11) ? 'Pagi' : (hrs >= 11 && hrs < 15) ? 'Siang' : (hrs >= 15 && hrs < 18) ? 'Sore' : 'Malam'">
                <div class="hero-avatar">🤖</div>
                <div>
                    <h1 class="hero-title">Halo, Kak {{ Auth::user()->name }}! 👋</h1>
                    <p class="hero-subtitle">Selamat <span x-text="greetingTime">Hari</span>, siap pantau operasional bisnis hari ini.</p>
                </div>
            </div>

            <div class="store-chips-row">
                <span class="store-chip">🏪 <strong>{{ $tenant->name }}</strong></span>
                <span class="store-chip domain">🌐 {{ $tenant->full_domain }}</span>
            </div>
        </div>

        <!-- Horizontal Scrollable Navigation Segmented Tabs -->
        <div class="nav-scroll-wrap">
            <a href="{{ route('owner.dashboard') }}" class="nav-pill active">📊 Dasbor</a>
            <a href="{{ route('owner.menus') }}" class="nav-pill">🍽️ Produk & Menu</a>
            <a href="{{ route('owner.tables') }}" class="nav-pill">🪑 Meja & QR</a>
            <a href="{{ route('owner.suppliers.index') }}" class="nav-pill">🚚 Pemasok</a>
            <a href="{{ route('owner.purchases.index') }}" class="nav-pill">📦 Kulakan</a>
            <a href="{{ route('owner.payables.index') }}" class="nav-pill">📕 Utang Toko</a>
            <a href="{{ route('owner.receivables.index') }}" class="nav-pill">📗 Piutang</a>
            <a href="{{ route('owner.reports.finance') }}" class="nav-pill">📈 Laporan P&L</a>
            <a href="{{ route('owner.team') }}" class="nav-pill">👥 Staf Kasir</a>
        </div>

        <!-- Quick Actions Row -->
        <div class="quick-actions-grid">
            <a href="{{ route('owner.pos', ['alias' => $tenant->domain]) }}" class="quick-action-btn">
                <div class="quick-action-icon pos">⚡</div>
                <div>
                    <div>Buka Kasir POS</div>
                    <div style="font-size: 0.72rem; color: var(--text-dim); font-weight: normal;">Catat Pesanan & Bayar</div>
                </div>
            </a>
            <a href="{{ route('owner.kds', ['alias' => $tenant->domain]) }}" class="quick-action-btn">
                <div class="quick-action-icon kds">🍳</div>
                <div>
                    <div>Layar KDS Dapur</div>
                    <div style="font-size: 0.72rem; color: var(--text-dim); font-weight: normal;">Pantau Masakan Koki</div>
                </div>
            </a>
            <a href="{{ route('owner.purchases.create') }}" class="quick-action-btn">
                <div class="quick-action-icon stock">➕</div>
                <div>
                    <div>Input Kulakan</div>
                    <div style="font-size: 0.72rem; color: var(--text-dim); font-weight: normal;">Restock Barang Toko</div>
                </div>
            </a>
        </div>

        <!-- Key Business Metrics Grid (2x2 Mobile, 4x1 Desktop) -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-title">Penjualan Hari Ini</div>
                <div class="stat-card-value">{{ $stats['today_sales'] }}</div>
                <div class="stat-card-sub">Omset kasir & online</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-title">Transaksi Selesai</div>
                <div class="stat-card-value" style="color: var(--text-main);">{{ $stats['orders_count'] }}</div>
                <div class="stat-card-sub">Pesanan sukses</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-title">Meja Aktif</div>
                <div class="stat-card-value" style="color: var(--text-main);">{{ $stats['active_tables'] }}</div>
                <div class="stat-card-sub">Kapasitas terisi</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-title">Total Karyawan</div>
                <div class="stat-card-value" style="color: var(--text-main);">{{ $stats['team_count'] }}</div>
                <div class="stat-card-sub">Kasir, koki, kurir</div>
            </div>
        </div>

        <!-- Ecosystem Features Grid -->
        <div class="section-header-wrap">
            <h2 class="section-title">Modul & Layanan Ekosistem</h2>
        </div>

        <div class="features-grid">
            
            <!-- Modul 1: POS Kasir Dasar -->
            <div class="feature-card">
                <div class="feature-head">
                    <h3>💻 POS & Kasir Pintar</h3>
                    <p>Mencatat penjualan, memproses order di kasir, pembayaran tunai/QRIS, dan buku piutang pelanggan.</p>
                </div>
                <div class="feature-foot">
                    <a href="{{ route('owner.pos', ['alias' => $tenant->domain]) }}" class="feature-action-link">Buka Kasir →</a>
                    <span class="status-pill-active">Aktif</span>
                </div>
            </div>

            <!-- Modul 2: KDS Layar Dapur -->
            <div class="feature-card">
                @if($package === 'Basic')
                    <div class="locked-overlay">
                        <div class="lock-badge">🔒</div>
                        <div class="lock-desc">Modul Layar Dapur (KDS) & Manajemen Meja membutuhkan paket <strong>Bisnis</strong>.</div>
                        <button class="btn-upgrade-pill" onclick="alert('Silakan hubungi admin untuk upgrade paket!');">Upgrade ke Bisnis</button>
                    </div>
                @endif
                <div class="feature-head">
                    <h3>🍳 Tampilan Dapur (KDS)</h3>
                    <p>Hubungkan kasir dengan koki. Pantau antrean memasak secara real-time via WebSocket dengan alarm durasi SLA.</p>
                </div>
                <div class="feature-foot">
                    <a href="{{ route('owner.kds', ['alias' => $tenant->domain]) }}" class="feature-action-link">Buka KDS Dapur →</a>
                    <span class="status-pill-active">Aktif</span>
                </div>
            </div>

            <!-- Modul 3: Manajemen Meja & Menu -->
            <div class="feature-card">
                <div class="feature-head">
                    <h3>🪑 Meja & Katalog Menu</h3>
                    <p>Atur layout meja dan QR Code self-order, serta kelola stok produk makanan dan minuman.</p>
                </div>
                <div class="feature-foot">
                    <div style="display: flex; gap: 0.75rem;">
                        <a href="{{ route('owner.menus') }}" class="feature-action-link">Menu →</a>
                        <a href="{{ route('owner.tables') }}" class="feature-action-link">Meja →</a>
                    </div>
                    <span class="status-pill-active">Aktif</span>
                </div>
            </div>

            <!-- Modul 4: Pengadaan & Utang Piutang -->
            <div class="feature-card">
                <div class="feature-head">
                    <h3>📦 Kulakan & Utang Toko</h3>
                    <p>Catat faktur pembelian stok dari supplier, hitung HPP (Moving Average), dan kelola buku utang tempo.</p>
                </div>
                <div class="feature-foot">
                    <div style="display: flex; gap: 0.75rem;">
                        <a href="{{ route('owner.purchases.index') }}" class="feature-action-link">Kulakan →</a>
                        <a href="{{ route('owner.payables.index') }}" class="feature-action-link">Utang →</a>
                    </div>
                    <span class="status-pill-active">Aktif</span>
                </div>
            </div>

            <!-- Modul 5: iZone Hotspot WiFi -->
            <div class="feature-card">
                @if($package !== 'Premium VIP')
                    <div class="locked-overlay">
                        <div class="lock-badge">🔒</div>
                        <div class="lock-desc">Integrasi iZone Captive Portal WiFi MikroTik membutuhkan paket <strong>Enterprise VIP</strong>.</div>
                        <button class="btn-upgrade-pill" onclick="alert('Silakan hubungi admin untuk upgrade paket!');">Upgrade ke VIP</button>
                    </div>
                @endif
                <div class="feature-head">
                    <h3>🌐 iZone WiFi Captive</h3>
                    <p>Integrasikan router MikroTik Anda dengan captive portal cloud. Akuisisi data pengunjung dan arahkan ke katalog menu.</p>
                </div>
                <div class="feature-foot">
                    <a href="{{ route('hotspot.captive', ['alias' => $tenant->domain]) }}" class="feature-action-link" target="_blank">Lihat Portal →</a>
                    <span class="status-pill-active">Aktif</span>
                </div>
            </div>

            <!-- Modul 6: Personalisasi Landing Page Toko (Full Span) -->
            <div class="feature-card full-span">
                <div class="feature-head">
                    <h3>🎨 Personalisasi Toko Online</h3>
                    <p>Sesuaikan judul, kontak WhatsApp, dan profil landing page toko di subdomain <code>{{ $tenant->full_domain }}</code>.</p>
                </div>

                <form action="{{ route('owner.settings.update') }}" method="POST">
                    @csrf
                    <div class="personalize-form-grid">
                        <div class="form-mini-group">
                            <label class="form-mini-label">Judul Landing Toko</label>
                            <input type="text" name="landing_title" class="form-mini-input"
                                   value="{{ $tenant->settings['landing_title'] ?? $tenant->name }}" required>
                        </div>
                        <div class="form-mini-group">
                            <label class="form-mini-label">Nomor WhatsApp Pemesanan</label>
                            <input type="text" name="whatsapp_url" class="form-mini-input"
                                   placeholder="Contoh: 6281234567890" value="{{ $tenant->settings['whatsapp_url'] ?? '' }}">
                        </div>
                        <div class="form-mini-group">
                            <label class="form-mini-label">Instagram Profil URL</label>
                            <input type="text" name="instagram_url" class="form-mini-input"
                                   placeholder="https://instagram.com/kopikita" value="{{ $tenant->settings['instagram_url'] ?? '' }}">
                        </div>
                        <div class="form-mini-group">
                            <label class="form-mini-label">Deskripsi Profil Singkat</label>
                            <textarea name="landing_description" class="form-mini-input" rows="2" required>{{ $tenant->settings['landing_description'] ?? 'Menyediakan pilihan produk berkualitas premium dengan pelayanan ramah dan cepat.' }}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn-save-personalize">✓ Simpan Profil Toko Online</button>
                </form>
            </div>

        </div>
    </main>

    <!-- Floating AI Assistant Widget -->
    <div class="floating-ai-widget" x-data="aiAssistantWidget()">
        <button class="floating-ai-btn" @click="toggleChat()" aria-label="Buka Asisten AI">
            <span style="font-size: 1.5rem; color: #ffffff;">💬</span>
            <span class="ai-online-dot"></span>
        </button>

        <!-- Chat Panel Window -->
        <div class="ai-chat-window" x-show="isOpen" x-transition.scale.origin.bottom.right style="display: none;" x-cloak>
            <!-- Header -->
            <div style="padding: 0.85rem 1rem; background: var(--surface-accent); border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 1.25rem;">🤖</span>
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: #fff;">Asisten Kasir Pintar</div>
                        <span style="font-size: 0.65rem; color: var(--success); font-weight: bold;">● Online (Gemini AI)</span>
                    </div>
                </div>
                <button @click="isOpen = false" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.1rem; padding: 0.2rem;">✕</button>
            </div>

            <!-- Messages Area -->
            <div class="chat-messages" style="flex-grow: 1; padding: 0.85rem; overflow-y: auto; display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="align-self: flex-start; text-align: left; max-width: 85%; background: var(--surface-accent); padding: 0.65rem 0.85rem; border-radius: 12px 12px 12px 0; border: 1px solid var(--border); font-size: 0.8rem; line-height: 1.4;">
                    Halo Kak {{ Auth::user()->name }}! Ada yang bisa Asisten bantu untuk mengelola <strong>{{ $tenant->name }}</strong> hari ini? 😊
                </div>

                <template x-for="msg in messages">
                    <div :style="msg.sender === 'user' ? 'align-self: flex-end; text-align: right; max-width: 85%; background: var(--primary); color: #fff; padding: 0.65rem 0.85rem; border-radius: 12px 12px 0 12px; font-weight: 500;' : 'align-self: flex-start; text-align: left; max-width: 85%; background: var(--surface-accent); padding: 0.65rem 0.85rem; border-radius: 12px 12px 12px 0; border: 1px solid var(--border);'" 
                         style="font-size: 0.8rem; line-height: 1.4;" x-html="msg.text">
                    </div>
                </template>

                <div x-show="isTyping" style="align-self: flex-start; background: var(--surface-accent); padding: 0.5rem 0.8rem; border-radius: 12px; display: flex; gap: 0.25rem; align-items: center;">
                    <span style="width: 5px; height: 5px; background: var(--text-muted); border-radius: 50%;"></span>
                    <span style="width: 5px; height: 5px; background: var(--text-muted); border-radius: 50%;"></span>
                    <span style="width: 5px; height: 5px; background: var(--text-muted); border-radius: 50%;"></span>
                </div>
            </div>

            <!-- Quick Suggestions Chips -->
            <div x-show="messages.length === 0" style="padding: 0.45rem 0.75rem; display: flex; flex-wrap: wrap; gap: 0.35rem; border-top: 1px solid var(--border); background: rgba(0,0,0,0.15);">
                <span class="suggestion-chip" @click="askQuestion('Bagaimana cara meningkatkan penjualan?')">🚀 Naikkan Omset</span>
                <span class="suggestion-chip" @click="askQuestion('Bagaimana cara tutup shift kasir?')">⏱ Tutup Shift</span>
                <span class="suggestion-chip" @click="askQuestion('Bagaimana cara Koki menggunakan KDS?')">🍳 Alur KDS</span>
                <span class="suggestion-chip" @click="askQuestion('Berikan kata motivasi hari ini!')">✨ Motivasi</span>
            </div>

            <!-- Input Box -->
            <form @submit.prevent="sendMessage()" style="padding: 0.65rem 0.75rem; border-top: 1px solid var(--border); display: flex; gap: 0.45rem; background: var(--surface-accent);">
                <input type="text" x-model="userInput" placeholder="Tanya asisten kasir..." 
                       style="flex-grow: 1; height: 38px; padding: 0 0.75rem; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); color: #fff; font-size: 0.82rem; outline: none;">
                <button type="submit" style="background: var(--primary); border: none; padding: 0 0.85rem; border-radius: var(--radius-sm); color: #fff; font-weight: bold; cursor: pointer; font-size: 0.8rem;">Kirim</button>
            </form>
        </div>
    </div>

<script>
function aiAssistantWidget() {
    return {
        isOpen: false,
        userInput: '',
        isTyping: false,
        messages: [],
        toggleChat() {
            this.isOpen = !this.isOpen;
        },
        askQuestion(question) {
            this.userInput = question;
            this.sendMessage();
        },
        sendMessage() {
            if (!this.userInput.trim()) return;
            const userMsg = this.userInput;
            this.messages.push({ sender: 'user', text: userMsg });
            this.userInput = '';
            this.isTyping = true;

            setTimeout(() => {
                const el = document.querySelector('.chat-messages');
                if (el) el.scrollTop = el.scrollHeight;
            }, 50);

            setTimeout(() => {
                let reply = '';
                const query = userMsg.toLowerCase();
                if (query.includes('omset') || query.includes('penjualan') || query.includes('naik')) {
                    reply = "<b>Tips Meningkatkan Penjualan Kafe:</b><br>1. <b>Bundling Menu</b>: Gabungkan kopi terlaris dengan cemilan kecil dengan diskon 10%.<br>2. <b>Promo QR Menu</b>: Buat diskon khusus 5% bagi pelanggan yang melakukan <i>self-order</i> langsung dari meja.<br>3. <b>Upselling</b>: Latih kasir untuk menawarkan tambahan toping atau ukuran <i>large</i> saat mencatat pesanan.";
                } else if (query.includes('shift') || query.includes('tutup') || query.includes('laci')) {
                    reply = "<b>Cara Tutup Shift Kasir (Blind Close):</b><br>1. Masuk ke tab <b>Shift & Kas</b> di kasir POS.<br>2. Hitung uang tunai fisik yang ada di laci kasir secara manual.<br>3. Input nominal uang fisik tersebut di kolom input <i>Blind Close</i> lalu tekan tombol <b>Tutup Kasir</b>.<br>4. Sistem akan membandingkan uang fisik dengan hitungan sistem dan mencatat selisihnya demi menghindari kebocoran.";
                } else if (query.includes('kds') || query.includes('dapur') || query.includes('koki')) {
                    reply = "<b>Panduan Kitchen Display System (KDS):</b><br>1. Layar KDS akan berbunyi secara otomatis jika ada pesanan baru masuk.<br>2. Koki memantau antrean pesanan. Jika pesanan belum selesai dimasak dalam waktu <b>15 menit</b>, kartu pesanan akan berkedip merah (Over SLA).<br>3. Klik tombol <b>Selesai</b> untuk mengubah status makanan menjadi <i>Ready</i> agar pelayan bisa langsung mengantar.";
                } else if (query.includes('motivasi') || query.includes('semangat') || query.includes('kata')) {
                    const quotes = [
                        "<b>Kata Motivasi Hari Ini:</b><br>\"Kesuksesan bukan tentang seberapa banyak kopi yang kita buat, melainkan tentang senyum kebahagiaan pelanggan saat meminumnya. Semangat melayani hari ini!\" ☕✨",
                        "<b>Kata Motivasi Hari Ini:</b><br>\"Kerja keras kita hari ini adalah fondasi bagi perkembangan bisnis Unimart kita di masa depan. Mari layani pelanggan dengan sepenuh hati!\" 💪🌟",
                        "<b>Kata Motivasi Hari Ini:</b><br>\"Setiap tantangan di toko adalah kesempatan untuk belajar menjadi lebih baik. Tetap ceria dan beri pelayanan terbaik!\" 🚀🔥",
                        "<b>Kata Motivasi Hari Ini:</b><br>\"Toko yang ramai diawali dari tim dapur yang solid dan kasir yang ramah. Semangat melayani, Kak!\" 🍔😊"
                    ];
                    reply = quotes[Math.floor(Math.random() * quotes.length)];
                } else {
                    reply = "Asisten Kasir Pintar siap membantu! Kakak bisa bertanya seputar tips operasional POS kasir, manajemen shift, atau cara kerja layar KDS dapur. Tetap semangat melayani ya! 🌟";
                }
                this.messages.push({ sender: 'assistant', text: reply });
                this.isTyping = false;

                setTimeout(() => {
                    const el = document.querySelector('.chat-messages');
                    if (el) el.scrollTop = el.scrollHeight;
                }, 50);
            }, 800);
        }
    }
}
</script>
</body>
</html>
