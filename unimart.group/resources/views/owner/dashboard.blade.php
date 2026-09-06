<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasbor Pemilik Bisnis - Unimart.group</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Playfair+Display:ital,wght=0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom CSS (Mobile-First & Responsive) -->
    <style>
        :root {
            --primary: hsl(28, 90%, 50%);
            --primary-light: hsl(28, 95%, 62%);
            --background: hsl(210, 30%, 8%);
            --surface: hsl(210, 24%, 12%);
            --surface-accent: hsl(210, 24%, 18%);
            --text-main: hsl(210, 20%, 96%);
            --text-muted: hsl(210, 14%, 70%);
            --border: rgba(255, 255, 255, 0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            --radius-lg: 20px;
            --radius-md: 14px;
            --radius-sm: 8px;
            --secondary: hsl(150, 60%, 35%);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--background);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Header */
        header.dashboard-header {
            background: rgba(15, 23, 30, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 1rem 1.2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
        }

        .logo span {
            font-family: 'Outfit', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            background: var(--primary);
            color: var(--background);
            padding: 0.1rem 0.4rem;
            border-radius: 20px;
            margin-left: 0.2rem;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info {
            text-align: right;
            display: none; /* Sembunyikan nama di mobile untuk menghemat tempat */
        }

        .badge-package {
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 700;
            background: var(--primary);
            color: var(--background);
            padding: 0.1rem 0.4rem;
            border-radius: 10px;
            text-transform: uppercase;
        }

        .badge-package.vip {
            background: linear-gradient(135deg, #ffd700, #ffa500);
            color: #000;
        }

        .badge-package.pro {
            background: hsl(200, 80%, 45%);
            color: #fff;
        }

        .btn-logout {
            background: transparent;
            color: #ff453a;
            border: 1px solid rgba(255, 69, 58, 0.2);
            padding: 0.35rem 0.8rem;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            font-size: 0.8rem;
            transition: var(--transition);
        }

        .btn-logout:hover {
            background: rgba(255, 69, 58, 0.1);
        }

        /* Main Workspace */
        main {
            padding: 1.5rem 1.2rem;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            flex-grow: 1;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, var(--surface-accent), var(--surface));
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }

        .welcome-text h2 {
            font-size: 1.4rem;
            margin-bottom: 0.3rem;
        }

        .welcome-text p {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .nav-tabs {
            display: flex;
            gap: 0.5rem;
        }

        .tab-link {
            color: var(--text-main);
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.8rem;
            transition: var(--transition);
        }

        .tab-link.active {
            background: var(--primary);
            color: var(--background);
            border-color: var(--primary);
        }

        /* Stats Grid - Mobile First: 2 Kolom */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1.2rem;
            box-shadow: var(--shadow);
        }

        .stat-card-title {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.3rem;
        }

        .stat-card-value {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--primary);
        }

        /* Features Section - Mobile First: 1 Kolom */
        .section-title {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 1.2rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.3rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.2rem;
        }

        .feature-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            position: relative;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .feature-card:hover {
            transform: translateY(-2px);
        }

        .feature-card-header h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .feature-card-header p {
            color: var(--text-muted);
            font-size: 0.8rem;
            line-height: 1.4;
        }

        .feature-card-footer {
            margin-top: 1.2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .feature-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.2rem;
            transition: var(--transition);
        }

        .feature-link:hover {
            color: var(--primary-light);
        }

        /* Locked Overlay */
        .locked-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 30, 0.9);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1rem;
            text-align: center;
            z-index: 2;
        }

        .lock-icon {
            font-size: 1.6rem;
            margin-bottom: 0.4rem;
        }

        .locked-text {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 0.8rem;
            max-width: 260px;
            line-height: 1.3;
        }

        .btn-upgrade {
            background: linear-gradient(135deg, var(--primary), #ffa500);
            color: var(--background);
            border: none;
            padding: 0.35rem 1rem;
            border-radius: var(--radius-sm);
            font-weight: 700;
            font-size: 0.75rem;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 10px rgba(230, 115, 0, 0.3);
        }

        /* Tablet & Desktop Layouts (>= 768px) */
        @media (min-width: 768px) {
            header.dashboard-header {
                padding: 1.2rem 2rem;
            }

            .logo {
                font-size: 1.5rem;
            }

            .user-info {
                display: block; /* Tampilkan nama di layar lebar */
            }

            main {
                padding: 2.5rem 2rem;
            }

            .welcome-banner {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                padding: 2rem;
            }

            .welcome-text h2 {
                font-size: 1.8rem;
            }

            .welcome-text p {
                font-size: 0.95rem;
            }

            .tab-link {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }

            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 1.5rem;
            }

            .stat-card-value {
                font-size: 1.8rem;
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }
        }

        @media (min-width: 1200px) {
            .features-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 2rem;
            }
        }
        /* AI Assistant Welcome Banner & Chat Widget */
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
            background: rgba(230,115,0,0.08);
        }
        
        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1.0); }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="dashboard-header">
        <div class="logo-area">
            <a href="/" class="logo">Unimart<span>dashboard</span></a>
        </div>
        <div class="user-menu">
            <div class="user-info">
                <div class="user-name" style="font-size: 0.9rem; font-weight: 600;">{{ Auth::user()->name }}</div>
                <div class="badge-package @if($package === 'Premium VIP') vip @elseif($package === 'Profesional') pro @endif">
                    Paket {{ $package }}
                </div>
            </div>
            <div class="badge-package @if($package === 'Premium VIP') vip @elseif($package === 'Profesional') pro @endif" style="display: inline-block; font-size: 0.65rem; margin-right: 0.5rem; @media (min-width: 768px) { display: none; }">
                {{ $package }}
            </div>
            <form action="{{ route('owner.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">Keluar</button>
            </form>
        </div>
    </header>

    <!-- Main Workspace -->
    <main>
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="welcome-text" x-data="{ greetingTime: '' }" x-init="const hrs = new Date().getHours(); greetingTime = (hrs >= 5 && hrs < 11) ? 'Pagi' : (hrs >= 11 && hrs < 15) ? 'Siang' : (hrs >= 15 && hrs < 18) ? 'Sore' : 'Malam'">
                <h2 style="display: flex; align-items: center; gap: 0.5rem; font-size: 1.4rem;">
                    <span>🤖</span> Halo Kak {{ Auth::user()->name }}! Selamat <span x-text="greetingTime">Hari</span>! 🌟
                </h2>
                <p style="font-style: italic; margin-top: 0.35rem; font-size: 0.85rem; color: var(--text-muted); line-height: 1.4;">
                    "Asisten Kasir Pintar siap mendampingi Kakak di Ruang Kendali ini. Tetap semangat memimpin tim, semoga bisnis <strong>{{ $tenant->name }}</strong> makin sukses dan laris manis hari ini! 🚀"
                </p>
                <p style="margin-top: 0.5rem; font-size: 0.8rem; color: var(--text-muted);">Domain Toko: <code>{{ $tenant->full_domain }}</code></p>
            </div>
            <div class="nav-tabs" style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                <a href="{{ route('owner.dashboard') }}" class="tab-link active">Dasbor</a>
                <a href="{{ route('owner.menus') }}" class="tab-link">Produk & Menu</a>
                <a href="{{ route('owner.tables') }}" class="tab-link">Meja</a>
                <a href="{{ route('owner.suppliers.index') }}" class="tab-link">🚚 Pemasok</a>
                <a href="{{ route('owner.purchases.index') }}" class="tab-link">📦 Kulakan</a>
                <a href="{{ route('owner.payables.index') }}" class="tab-link">📕 Utang Toko</a>
                <a href="{{ route('owner.receivables.index') }}" class="tab-link">📗 Piutang</a>
                <a href="{{ route('owner.reports.finance') }}" class="tab-link">📈 Keuangan P&L</a>
                <a href="{{ route('owner.team') }}" class="tab-link">Kelola Staf</a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-title">Penjualan Hari Ini</div>
                <div class="stat-card-value">{{ $stats['today_sales'] }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-title">Transaksi Selesai</div>
                <div class="stat-card-value">{{ $stats['orders_count'] }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-title">Meja Aktif</div>
                <div class="stat-card-value">{{ $stats['active_tables'] }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-title">Total Staf</div>
                <div class="stat-card-value">{{ $stats['team_count'] }}</div>
            </div>
        </div>

        <!-- Features Section -->
        <h2 class="section-title">Modul & Fitur Unimart</h2>
        <div class="features-grid">
            
            <!-- Fitur 1: POS Kasir Dasar & Laporan (Milik Paket Bisnis ke atas) -->
            <div class="feature-card">
                <div class="feature-card-header">
                    <h3>💻 POS & Kasir Dasar</h3>
                    <p>Mencatat penjualan, memproses order di kasir, mencetak struk secara manual, dan melihat laporan penjualan harian sederhana.</p>
                </div>
                <div class="feature-card-footer">
                    <a href="{{ route('owner.pos', ['alias' => $tenant->domain]) }}" class="feature-link">Buka Kasir →</a>
                    <span style="font-size: 0.7rem; color: var(--secondary); font-weight: bold;">AKSES AKTIF</span>
                </div>
            </div>

            <!-- Fitur 2: KDS & Kitchen Management (Milik Paket Profesional ke atas) -->
            <div class="feature-card">
                <!-- Lock Overlay if package is Bisnis -->
                @if($package === 'Bisnis')
                    <div class="locked-overlay">
                        <div class="lock-icon">🔒</div>
                        <div class="locked-text">Modul Dapur (KDS) & Manajemen Inventaris membutuhkan paket <strong>Profesional</strong>.</div>
                        <button class="btn-upgrade">Upgrade ke Profesional</button>
                    </div>
                @endif
                <div class="feature-card-header">
                    <h3>🍳 Tampilan Dapur (KDS)</h3>
                    <p>Hubungkan kasir dengan dapur. Pantau antrean memasak koki secara real-time lengkap dengan sistem alarm SLA durasi 15 menit.</p>
                </div>
                <div class="feature-card-footer">
                    <a href="{{ route('owner.kds', ['alias' => $tenant->domain]) }}" class="feature-link">Buka KDS Dapur →</a>
                    <span style="font-size: 0.7rem; color: var(--secondary); font-weight: bold;">AKSES AKTIF</span>
                </div>
            </div>

            <!-- Fitur 3: Inventaris & Manajemen Meja (Milik Paket Profesional ke atas) -->
            <div class="feature-card">
                <!-- Lock Overlay if package is Bisnis -->
                @if($package === 'Bisnis')
                    <div class="locked-overlay">
                        <div class="lock-icon">🔒</div>
                        <div class="locked-text">Manajemen Meja & Inventaris Bahan Baku membutuhkan paket <strong>Profesional</strong>.</div>
                        <button class="btn-upgrade">Upgrade ke Profesional</button>
                    </div>
                @endif
                <div class="feature-card-header">
                    <h3>🪑 Meja & Menu</h3>
                    <p>Mengatur layout meja pelanggan, melacak stok produk/bahan baku secara *asynchronous* (DB lock) untuk mencegah overselling.</p>
                </div>
                <div class="feature-card-footer">
                    <div style="display: flex; gap: 0.8rem;">
                        <a href="{{ route('owner.menus') }}" class="feature-link">Bahan & Menu →</a>
                        <a href="{{ route('owner.tables') }}" class="feature-link">Layout Meja →</a>
                    </div>
                    <span style="font-size: 0.7rem; color: var(--secondary); font-weight: bold;">AKSES AKTIF</span>
                </div>
            </div>

            <!-- Fitur 4: iZone Hotspot Integration (Milik Paket Premium VIP) -->
            <div class="feature-card">
                <!-- Lock Overlay if package is Bisnis atau Profesional -->
                @if($package === 'Bisnis' || $package === 'Profesional')
                    <div class="locked-overlay">
                        <div class="lock-icon">🔒</div>
                        <div class="locked-text">Integrasi iZone Captive Portal WiFi membutuhkan paket <strong>Premium VIP</strong>.</div>
                        <button class="btn-upgrade">Upgrade ke Premium VIP</button>
                    </div>
                @endif
                <div class="feature-card-header">
                    <h3>🌐 iZone WiFi Captive</h3>
                    <p>Integrasikan router MikroTik Anda dengan captive portal cloud. Akuisisi data pengunjung dan arahkan otomatis ke QR Menu setelah konek.</p>
                </div>
                <div class="feature-card-footer">
                    <a href="{{ route('hotspot.captive', ['alias' => $tenant->domain]) }}" class="feature-link" target="_blank">Lihat Portal →</a>
                    <span style="font-size: 0.7rem; color: var(--secondary); font-weight: bold;">AKSES AKTIF</span>
                </div>
            </div>

            <!-- Fitur 5: WhatsApp AI Assistant (Milik Paket Premium VIP) -->
            <div class="feature-card">
                <!-- Lock Overlay if package is Bisnis atau Profesional -->
                @if($package === 'Bisnis' || $package === 'Profesional')
                    <div class="locked-overlay">
                        <div class="lock-icon">🔒</div>
                        <div class="locked-text">WhatsApp AI Assistant (Gemini) membutuhkan paket <strong>Premium VIP</strong>.</div>
                        <button class="btn-upgrade">Upgrade ke Premium VIP</button>
                    </div>
                @endif
                <div class="feature-card-header">
                    <h3>💬 WhatsApp AI Bot 24/7</h3>
                    <p>Aktifkan asisten kasir virtual bertenaga Google Gemini. Otomatis menjawab pertanyaan menu & ketersediaan stok pelanggan via WhatsApp.</p>
                </div>
                <div class="feature-card-footer">
                    <a href="#" class="feature-link" onclick="alert('WhatsApp AI Bot telah aktif di latar belakang (simulated mode). Menghubungkan Google Gemini...'); return false;">Pengaturan AI Bot →</a>
                    <span style="font-size: 0.7rem; color: var(--secondary); font-weight: bold;">AKSES AKTIF</span>
                </div>
            </div>

            <!-- Fitur 6: Personalisasi Landing Page (Owner/Admin) -->
            <div class="feature-card" style="grid-column: span 2; max-width: 100%;">
                <div class="feature-card-header">
                    <h3>🎨 Personalisasi Landing Page</h3>
                    <p>Ubah tampilan utama landing page toko Anda di subdomain <code>{{ $tenant->full_domain }}</code>.</p>
                    
                    <form action="{{ route('owner.settings.update') }}" method="POST" style="margin-top: 1.2rem; display: grid; gap: 0.8rem; text-align: left;">
                        @csrf
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem;">
                            <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                                <label style="font-size: 0.72rem; color: var(--text-muted); font-weight: bold;">Judul Landing Page</label>
                                <input type="text" name="landing_title" style="padding: 0.45rem; background: var(--surface-accent); border: 1px solid var(--border); border-radius: var(--radius-sm); color: var(--text-main); font-size: 0.8rem; outline: none; border-color: var(--border);"
                                       value="{{ $tenant->settings['landing_title'] ?? $tenant->name }}" required>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                                <label style="font-size: 0.72rem; color: var(--text-muted); font-weight: bold;">Nomor WhatsApp</label>
                                <input type="text" name="whatsapp_url" style="padding: 0.45rem; background: var(--surface-accent); border: 1px solid var(--border); border-radius: var(--radius-sm); color: var(--text-main); font-size: 0.8rem; outline: none; border-color: var(--border);"
                                       placeholder="Contoh: 6281234567890" value="{{ $tenant->settings['whatsapp_url'] ?? '' }}">
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem;">
                            <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                                <label style="font-size: 0.72rem; color: var(--text-muted); font-weight: bold;">Instagram URL</label>
                                <input type="text" name="instagram_url" style="padding: 0.45rem; background: var(--surface-accent); border: 1px solid var(--border); border-radius: var(--radius-sm); color: var(--text-main); font-size: 0.8rem; outline: none; border-color: var(--border);"
                                       placeholder="https://instagram.com/kopikita" value="{{ $tenant->settings['instagram_url'] ?? '' }}">
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                                <label style="font-size: 0.72rem; color: var(--text-muted); font-weight: bold;">Deskripsi Singkat Bisnis</label>
                                <textarea name="landing_description" rows="2" style="padding: 0.45rem; background: var(--surface-accent); border: 1px solid var(--border); border-radius: var(--radius-sm); color: var(--text-main); font-size: 0.8rem; outline: none; font-family: inherit; border-color: var(--border);" required>{{ $tenant->settings['landing_description'] ?? 'Menyediakan pilihan kopi berkualitas premium dan tempat nongkrong santai.' }}</textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn-upgrade" style="width: 100%; padding: 0.5rem; margin-top: 0.4rem; font-weight: bold;">✓ Simpan Personalisasi</button>
                    </form>
                </div>
            </div>

        </div>
    </main>

    <!-- Floating AI Chat Widget -->
    <div class="floating-ai-widget" x-data="aiAssistantWidget()" style="position: fixed; bottom: 2rem; right: 2rem; z-index: 9999;">
        <!-- Floating Trigger Button -->
        <button class="ai-trigger-btn" @click="toggleChat()"
                style="width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), #ffa500); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(230,115,0,0.4); transition: transform 0.2s; position: relative;">
            <span style="font-size: 1.6rem; color: var(--background);">💬</span>
            <span style="position: absolute; top: 0; right: 0; width: 12px; height: 12px; background: var(--secondary); border: 2px solid var(--background); border-radius: 50%; box-shadow: 0 0 6px var(--secondary);"></span>
        </button>

        <!-- Chat Panel Window -->
        <div class="ai-chat-panel" x-show="isOpen" x-transition.scale.origin.bottom.right style="display: none; position: absolute; bottom: 70px; right: 0; width: 340px; height: 420px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); box-shadow: var(--shadow); display: flex; flex-direction: column; overflow: hidden;" x-cloak>
            <!-- Header -->
            <div class="chat-header" style="padding: 0.9rem 1.2rem; background: var(--surface-accent); border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 1.3rem;">🤖</span>
                    <div style="text-align: left;">
                        <h4 style="font-size: 0.88rem; font-weight: 700; color: #fff;">Asisten Kasir Pintar</h4>
                        <span style="font-size: 0.65rem; color: var(--secondary); font-weight: bold;">● Online (Gemini AI)</span>
                    </div>
                </div>
                <button @click="isOpen = false" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.1rem;">✕</button>
            </div>

            <!-- Messages Area -->
            <div class="chat-messages" style="flex-grow: 1; padding: 1rem; overflow-y: auto; display: flex; flex-direction: column; gap: 0.8rem;">
                <div style="align-self: flex-start; text-align: left; max-width: 85%; background: var(--surface-accent); padding: 0.7rem 0.9rem; border-radius: 12px 12px 12px 0; border: 1px solid var(--border); font-size: 0.82rem; line-height: 1.4;">
                    Halo Kak {{ Auth::user()->name }}! Ada yang bisa Asisten bantu hari ini untuk mengelola bisnis <strong>{{ $tenant->name }}</strong>? 😊
                </div>

                <template x-for="msg in messages">
                    <div :style="msg.sender === 'user' ? 'align-self: flex-end; text-align: right; max-width: 85%; background: var(--primary); color: var(--background); padding: 0.7rem 0.9rem; border-radius: 12px 12px 0 12px; font-weight: 500;' : 'align-self: flex-start; text-align: left; max-width: 85%; background: var(--surface-accent); padding: 0.7rem 0.9rem; border-radius: 12px 12px 12px 0; border: 1px solid var(--border);'" 
                         style="font-size: 0.82rem; line-height: 1.4;" x-html="msg.text">
                    </div>
                </template>

                <div x-show="isTyping" style="align-self: flex-start; background: var(--surface-accent); padding: 0.6rem 0.9rem; border-radius: 12px; display: flex; gap: 0.3rem; align-items: center;">
                    <span style="width: 6px; height: 6px; background: var(--text-muted); border-radius: 50%; animation: bounce 1.4s infinite both;"></span>
                    <span style="width: 6px; height: 6px; background: var(--text-muted); border-radius: 50%; animation: bounce 1.4s infinite both 0.2s;"></span>
                    <span style="width: 6px; height: 6px; background: var(--text-muted); border-radius: 50%; animation: bounce 1.4s infinite both 0.4s;"></span>
                </div>
            </div>

            <!-- Quick Suggestions Chips -->
            <div class="chat-suggestions" x-show="messages.length === 0"
                 style="padding: 0.5rem 0.8rem; display: flex; flex-wrap: wrap; gap: 0.4rem; border-top: 1px solid var(--border); background: rgba(0,0,0,0.15);">
                <span class="suggestion-chip" @click="askQuestion('Bagaimana meningkatkan penjualan?')">🚀 Naikkan Omset</span>
                <span class="suggestion-chip" @click="askQuestion('Bagaimana cara tutup shift kasir?')">⏱ Cara Tutup Shift</span>
                <span class="suggestion-chip" @click="askQuestion('Bagaimana cara Koki menggunakan KDS dapur?')">🍳 Cara Kerja KDS</span>
                <span class="suggestion-chip" @click="askQuestion('Berikan saya kata motivasi hari ini!')">✨ Motivasi Hari Ini</span>
            </div>

            <!-- Input Box -->
            <form @submit.prevent="sendMessage()" style="padding: 0.8rem; border-top: 1px solid var(--border); display: flex; gap: 0.5rem; background: var(--surface-accent);">
                <input type="text" x-model="userInput" placeholder="Tanya sesuatu..." 
                       style="flex-grow: 1; padding: 0.45rem 0.8rem; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); color: #fff; font-size: 0.82rem; outline: none;">
                <button type="submit" style="background: var(--primary); border: none; padding: 0.45rem 0.8rem; border-radius: var(--radius-sm); color: var(--background); font-weight: bold; cursor: pointer; font-size: 0.82rem;">Kirim</button>
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
                    reply = "<b>Cara Tutup Shift Kasir (Blind Close):</b><br>1. Masuk ke tab <b>Shift & Kas</b> di dasbor.<br>2. Hitung uang tunai fisik yang ada di laci kasir secara manual.<br>3. Input nominal uang fisik tersebut di kolom input <i>Blind Close</i> lalu tekan tombol <b>Tutup Kasir</b>.<br>4. Sistem akan membandingkan uang fisik dengan hitungan sistem dan mencatat selisihnya demi menghindari kebocoran.";
                } else if (query.includes('kds') || query.includes('dapur') || query.includes('koki')) {
                    reply = "<b>Panduan Kitchen Display System (KDS):</b><br>1. Layar KDS akan berbunyi (beep) secara otomatis jika ada pesanan baru masuk.<br>2. Koki memantau antrean pesanan. Jika pesanan belum selesai dimasak dalam waktu <b>15 menit</b>, kartu pesanan akan berkedip merah (Over SLA).<br>3. Klik tombol <b>Selesai</b> untuk mengubah status makanan menjadi <i>Ready</i> agar pelayan bisa langsung mengantar.";
                } else if (query.includes('motivasi') || query.includes('semangat') || query.includes('kata')) {
                    const quotes = [
                        "<b>Kata Motivasi Hari Ini:</b><br>\"Kesuksesan bukan tentang seberapa banyak kopi yang kita buat, melainkan tentang senyum kebahagiaan pelanggan saat meminumnya. Semangat melayani hari ini!\" ☕✨",
                        "<b>Kata Motivasi Hari Ini:</b><br>\"Kerja keras kita hari ini adalah fondasi bagi perkembangan bisnis Unimart kita di masa depan. Mari layani pelanggan dengan sepenuh hati!\" 💪🌟",
                        "<b>Kata Motivasi Hari Ini:</b><br>\"Setiap tantangan di toko adalah kesempatan untuk belajar menjadi lebih baik. Tetap ceria dan beri pelayanan terbaik!\" 🚀🔥",
                        "<b>Kata Motivasi Hari Ini:</b><br>\"Kafe yang ramai diawali dari tim dapur yang solid dan kasir yang ramah. Semangat melayani, Kak!\" 🍔😊"
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
            }, 1000);
        }
    }
}
</script>
</body>
</html>
