<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Meja & QR Code - Unimart.group</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Playfair+Display:ital,wght=0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- Alpine.js & QRCodeJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

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
            --transition: all 0.3s ease;
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

        /* Layout Grid - Mobile First: Stacked (1 Kolom) */
        .workspace-layout {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .content-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.2rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.6rem;
        }

        /* Meja Grid */
        .tables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
            gap: 0.8rem;
        }

        .table-card {
            background: var(--surface-accent);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            font-size: 1.05rem;
            font-weight: 800;
            transition: var(--transition);
        }

        .table-card:hover, .table-card.active {
            border-color: var(--primary);
            background: rgba(230, 115, 0, 0.1);
            color: var(--primary-light);
            transform: scale(1.03);
        }

        /* Detail/QR Panel */
        .qr-panel {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: var(--surface-accent);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            border: 1px solid var(--border);
            min-height: 280px;
        }

        .qr-placeholder {
            width: 100%;
            height: 180px;
            border: 2px dashed var(--border);
            border-radius: var(--radius-sm);
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        #qrcode {
            background: white;
            padding: 0.8rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1rem;
            display: inline-block;
            max-width: 100%;
        }

        #qrcode img {
            max-width: 100%;
            height: auto;
        }

        .btn-print {
            background: var(--primary);
            color: var(--background);
            border: none;
            width: 100%;
            padding: 0.65rem;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 0.8rem;
        }

        .btn-print:hover {
            background: var(--primary-light);
        }

        /* Form */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .form-input {
            width: 100%;
            padding: 0.55rem 0.8rem;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            background: var(--surface-accent);
            color: var(--text-main);
            font-size: 0.85rem;
        }

        .form-input:focus {
            border-color: var(--primary);
            outline: none;
        }

        .btn-submit {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 0.45rem 1rem;
            border-radius: var(--radius-sm);
            font-weight: 700;
            cursor: pointer;
            font-size: 0.8rem;
            transition: var(--transition);
        }

        .btn-submit:hover {
            background: var(--primary);
            color: var(--background);
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.15);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: #28a745;
            padding: 0.8rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.2rem;
            font-size: 0.85rem;
        }

        /* Tablet & Desktop Layouts (>= 768px) */
        @media (min-width: 768px) {
            header.dashboard-header {
                padding: 1.2rem 2rem;
            }

            .logo {
                font-size: 1.5rem;
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

            /* Berubah jadi grid 2 kolom di tablet/desktop */
            .workspace-layout {
                display: grid;
                grid-template-columns: 1.2fr 0.8fr;
                gap: 2rem;
            }

            .content-box {
                padding: 2rem;
            }

            .tables-grid {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
                gap: 1rem;
            }

            .qr-panel {
                padding: 2rem;
                min-height: 350px;
            }

            .qr-placeholder {
                height: 200px;
            }
        }
    </style>
</head>
<body x-data="tablesApp()">

    <!-- Header -->
    <header class="dashboard-header">
        <a href="/" class="logo">Unimart<span>dashboard</span></a>
        <div class="user-menu">
            <div class="badge-package">{{ $package }}</div>
            <form action="{{ route('owner.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">Keluar</button>
            </form>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="welcome-banner">
            <div class="welcome-text">
                <h2>Kelola Meja Kafe</h2>
                <p>Atur total meja terdaftar dan unduh QR Code meja untuk mempercepat proses check-in order tamu.</p>
            </div>
            <div class="nav-scroll-wrap" style="display: flex; gap: 0.5rem; overflow-x: auto; padding: 0.35rem 0.1rem; -webkit-overflow-scrolling: touch; scrollbar-width: none; margin-top: 1rem;">
                <a href="{{ route('owner.dashboard') }}" class="tab-link" style="border-radius: 30px; white-space: nowrap; flex-shrink: 0;">📊 Dasbor</a>
                <a href="{{ route('owner.menus') }}" class="tab-link" style="border-radius: 30px; white-space: nowrap; flex-shrink: 0;">🍽️ Produk & Menu</a>
                <a href="{{ route('owner.tables') }}" class="tab-link active" style="border-radius: 30px; white-space: nowrap; flex-shrink: 0;">🪑 Meja & QR</a>
                <a href="{{ route('owner.suppliers.index') }}" class="tab-link" style="border-radius: 30px; white-space: nowrap; flex-shrink: 0;">🚚 Pemasok</a>
                <a href="{{ route('owner.purchases.index') }}" class="tab-link" style="border-radius: 30px; white-space: nowrap; flex-shrink: 0;">📦 Kulakan</a>
                <a href="{{ route('owner.payables.index') }}" class="tab-link" style="border-radius: 30px; white-space: nowrap; flex-shrink: 0;">📕 Utang Toko</a>
                <a href="{{ route('owner.receivables.index') }}" class="tab-link" style="border-radius: 30px; white-space: nowrap; flex-shrink: 0;">📗 Piutang</a>
                <a href="{{ route('owner.reports.finance') }}" class="tab-link" style="border-radius: 30px; white-space: nowrap; flex-shrink: 0;">📈 Laporan P&L</a>
                <a href="{{ route('owner.team') }}" class="tab-link" style="border-radius: 30px; white-space: nowrap; flex-shrink: 0;">👥 Staf Kasir</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="workspace-layout">
            
            <!-- Denah Meja (Kiri / Atas di HP) -->
            <div class="content-box">
                <div class="content-header">
                    <h3 style="font-size: 1.15rem; font-weight: 700;">Denah Tata Letak</h3>
                </div>

                <div class="tables-grid" style="margin-bottom: 2rem;">
                    <template x-for="tbl in tables" :key="tbl.number">
                        <div class="table-card" 
                             :class="activeTable && activeTable.number === tbl.number ? 'active' : ''"
                             @click="selectTable(tbl)"
                             x-text="'M-' + tbl.number">
                        </div>
                    </template>
                </div>

                <div style="border-top: 1px solid var(--border); padding-top: 1.5rem;">
                    <h4 style="font-size: 0.95rem; margin-bottom: 0.8rem; font-weight: 700;">Atur Jumlah Total Meja</h4>
                    <form action="{{ route('owner.tables.update') }}" method="POST" style="display: flex; gap: 0.8rem; align-items: flex-end;">
                        @csrf
                        <div class="form-group" style="flex-grow: 1; margin-bottom: 0;">
                            <label class="form-label" for="tables_count">Total Meja Kafe (Maks 50)</label>
                            <input class="form-input" type="number" id="tables_count" name="tables_count" value="{{ count($tables) }}" min="1" max="50" required>
                        </div>
                        <button type="submit" class="btn-submit">Ubah</button>
                    </form>
                </div>
            </div>

            <!-- Panel QR Code (Kanan / Bawah di HP) -->
            <div class="content-box">
                <div class="content-header">
                    <h3 style="font-size: 1.15rem; font-weight: 700;">QR Code Meja</h3>
                </div>

                <div class="qr-panel">
                    <div x-show="!activeTable" class="qr-placeholder">
                        Pilih meja di sebelah kiri/atas untuk generate QR.
                    </div>

                    <div x-show="activeTable" style="display: none; width: 100%;">
                        <h4 style="font-size: 1.1rem; margin-bottom: 0.3rem;" x-text="'Meja ' + (activeTable ? activeTable.number : '')"></h4>
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 1.2rem; word-break: break-all;" x-text="activeTable ? activeTable.qr_url : ''"></p>
                        
                        <div style="display: flex; justify-content: center;">
                            <div id="qrcode"></div>
                        </div>

                        <button class="btn-print" @click="printQR()">Cetak QR Code</button>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Script Meja -->
    <script>
        function tablesApp() {
            return {
                tables: @json($tables),
                activeTable: null,
                qrGenerator: null,

                selectTable(tbl) {
                    this.activeTable = tbl;

                    const qrContainer = document.getElementById("qrcode");
                    qrContainer.innerHTML = "";

                    this.$nextTick(() => {
                        this.qrGenerator = new QRCode(qrContainer, {
                            text: tbl.qr_url,
                            width: 180,
                            height: 180,
                            colorDark : "#000000",
                            colorLight : "#ffffff",
                            correctLevel : QRCode.CorrectLevel.H
                        });
                    });
                },

                printQR() {
                    if (!this.activeTable) return;
                    
                    const qrImgSrc = document.querySelector("#qrcode img").src;
                    const printWindow = window.open('', '_blank');
                    printWindow.document.write(`
                        <html>
                        <head>
                            <title>Cetak QR Code Meja ${this.activeTable.number}</title>
                            <style>
                                body {
                                    font-family: 'Outfit', sans-serif;
                                    text-align: center;
                                    padding: 2rem;
                                }
                                .card {
                                    border: 3px solid #e67300;
                                    border-radius: 20px;
                                    padding: 2.5rem;
                                    display: inline-block;
                                    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                                }
                                h1 { color: #e67300; margin-bottom: 0.5rem; }
                                p { color: #555; margin-bottom: 2rem; font-size: 1.1rem; }
                                img { width: 250px; height: 250px; }
                            </style>
                        </head>
                        <body>
                            <div class="card">
                                <h1>${this.activeTable.qr_url.includes('kopi-kita') ? 'Kopi Kita' : 'Caffe'}</h1>
                                <p>Scan untuk memesan dari <strong>Meja ${this.activeTable.number}</strong></p>
                                <img src="${qrImgSrc}" />
                            </div>
                            <script>
                                window.onload = function() {
                                    window.print();
                                    window.close();
                                }
                            <\/script>
                        </body>
                        </html>
                    `);
                    printWindow.document.close();
                }
            }
        }
    </script>
</body>
</html>
