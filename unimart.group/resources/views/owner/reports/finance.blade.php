<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan & Laba Rugi (P&L) - Unimart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
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
            --danger: hsl(0, 75%, 58%);
            --success: hsl(150, 65%, 42%);
            --warning: hsl(38, 95%, 55%);
            --info: hsl(200, 85%, 55%);
            --radius-md: 14px;
            --radius-sm: 8px;
            --transition: all 0.25s ease;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body { background: var(--background); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; }
        header.dashboard-header { background: rgba(15, 23, 30, 0.85); backdrop-filter: blur(12px); border-bottom: 1px solid var(--border); padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 20; }
        .logo { font-family: 'Playfair Display', serif; font-size: 1.3rem; font-weight: 800; color: var(--primary); text-decoration: none; }
        .logo span { font-family: 'Outfit', sans-serif; font-size: 0.75rem; font-weight: 700; background: var(--primary); color: var(--background); padding: 0.1rem 0.4rem; border-radius: 20px; margin-left: 0.2rem; }
        main { padding: 1.5rem; max-width: 1200px; width: 100%; margin: 0 auto; flex-grow: 1; }
        
        .nav-scroller { display: flex; gap: 0.5rem; overflow-x: auto; padding-bottom: 1rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); }
        .nav-tab { padding: 0.5rem 1rem; border-radius: 20px; color: var(--text-muted); text-decoration: none; font-size: 0.85rem; font-weight: 600; background: var(--surface); white-space: nowrap; transition: var(--transition); }
        .nav-tab:hover, .nav-tab.active { background: var(--primary); color: #000; }

        .date-filter-bar { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1rem 1.2rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
        .date-inputs { display: flex; align-items: center; gap: 0.5rem; }
        .date-inputs input { background: var(--surface-accent); border: 1px solid var(--border); border-radius: 6px; padding: 0.4rem 0.7rem; color: #fff; font-size: 0.85rem; outline: none; }
        .btn-filter { background: var(--primary); color: #000; font-weight: 700; border: none; padding: 0.45rem 0.9rem; border-radius: 6px; cursor: pointer; font-size: 0.85rem; }

        .metric-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .metric-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.2rem; }
        .metric-title { font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; }
        .metric-val { font-size: 1.6rem; font-weight: 800; margin-top: 0.4rem; }
        .metric-sub { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem; }

        .pnl-container { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
        @media (max-width: 768px) { .pnl-container { grid-template-columns: 1fr; } }

        .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.5rem; }
        .card-header { font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; color: #fff; display: flex; justify-content: space-between; align-items: center; }

        .line-item { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--border); font-size: 0.9rem; }
        .line-item:last-child { border-bottom: none; }
        .line-bold { font-weight: 700; color: #fff; font-size: 1rem; }
    </style>
</head>
<body>

    <header class="dashboard-header">
        <a href="/" class="logo">Unimart<span>finance</span></a>
        <div style="font-size: 0.85rem; color: var(--text-muted);">Toko: <strong style="color: #fff;">{{ $tenant->name }}</strong></div>
    </header>

    <main>
        <!-- Tab Navigasi Toko -->
        <div class="nav-scroller">
            <a href="{{ route('owner.dashboard') }}" class="nav-tab">📊 Dasbor</a>
            <a href="{{ route('owner.menus') }}" class="nav-tab">🍛 Produk & Menu</a>
            <a href="{{ route('owner.tables') }}" class="nav-tab">🪑 Meja</a>
            <a href="{{ route('owner.suppliers.index') }}" class="nav-tab">🚚 Pemasok (Supplier)</a>
            <a href="{{ route('owner.purchases.index') }}" class="nav-tab">📦 Kulakan (Purchases)</a>
            <a href="{{ route('owner.payables.index') }}" class="nav-tab">📕 Buku Utang Toko</a>
            <a href="{{ route('owner.receivables.index') }}" class="nav-tab">📗 Buku Piutang</a>
            <a href="{{ route('owner.reports.finance') }}" class="nav-tab active">📈 Laporan Keuangan</a>
        </div>

        <div class="date-filter-bar">
            <div>
                <h2 style="font-size: 1.3rem; font-weight: 700;">📈 Laporan Keuangan & Laba Rugi</h2>
                <div style="font-size: 0.8rem; color: var(--text-muted);">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
            </div>
            <form action="{{ route('owner.reports.finance') }}" method="GET" class="date-inputs">
                <input type="date" name="start_date" value="{{ $startDate }}">
                <span style="color: var(--text-muted);">sampai</span>
                <input type="date" name="end_date" value="{{ $endDate }}">
                <button type="submit" class="btn-filter">Terapkan Filter</button>
            </form>
        </div>

        <!-- 4 Metrik Utama -->
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-title">Omset Penjualan (Revenue)</div>
                <div class="metric-val" style="color: var(--primary);">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                <div class="metric-sub">Tunai: Rp {{ number_format($cashRevenue, 0, ',', '.') }} | Bon: Rp {{ number_format($debtRevenue, 0, ',', '.') }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-title">Harga Pokok Penjualan (HPP)</div>
                <div class="metric-val" style="color: var(--text-muted);">Rp {{ number_format($totalCogs, 0, ',', '.') }}</div>
                <div class="metric-sub">Modal barang yang telah terjual</div>
            </div>
            <div class="metric-card">
                <div class="metric-title">Laba Kotor (Gross Profit)</div>
                <div class="metric-val" style="color: {{ $grossProfit >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                    Rp {{ number_format($grossProfit, 0, ',', '.') }}
                </div>
                <div class="metric-sub">Omset dikurangi HPP</div>
            </div>
            <div class="metric-card" style="border-color: rgba(34, 197, 94, 0.3);">
                <div class="metric-title">Laba Bersih (Net Profit)</div>
                <div class="metric-val" style="color: {{ $netProfit >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                    Rp {{ number_format($netProfit, 0, ',', '.') }}
                </div>
                <div class="metric-sub">Laba kotor setelah beban operasional</div>
            </div>
        </div>

        <!-- Dua Kolom: Laporan Laba Rugi vs Arus Kas Riil -->
        <div class="pnl-container">
            <!-- Kolom 1: Laba Rugi (P&L Statement) -->
            <div class="card">
                <div class="card-header">
                    <span>📊 Rincian Laba Rugi (P&L)</span>
                </div>
                <div class="line-item">
                    <span>Total Penjualan Kotor (Revenue)</span>
                    <strong style="color: #fff;">+ Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong>
                </div>
                <div class="line-item">
                    <span>Total Beban Pokok (HPP Barang Terjual)</span>
                    <strong style="color: var(--danger);">- Rp {{ number_format($totalCogs, 0, ',', '.') }}</strong>
                </div>
                <div class="line-item line-bold" style="background: rgba(255,255,255,0.02); padding: 0.75rem 0.5rem; border-radius: 6px;">
                    <span>Laba Kotor Toko</span>
                    <span style="color: {{ $grossProfit >= 0 ? 'var(--success)' : 'var(--danger)' }};">Rp {{ number_format($grossProfit, 0, ',', '.') }}</span>
                </div>
                <div class="line-item" style="margin-top: 0.5rem;">
                    <span>Beban Operasional Kasir (Petty Cash)</span>
                    <strong style="color: var(--danger);">- Rp {{ number_format($totalExpenses, 0, ',', '.') }}</strong>
                </div>
                <div class="line-item line-bold" style="background: rgba(34, 197, 94, 0.08); padding: 0.75rem 0.5rem; border-radius: 6px; margin-top: 0.5rem;">
                    <span>Laba Bersih Operasional (Net)</span>
                    <span style="color: {{ $netProfit >= 0 ? 'var(--success)' : 'var(--danger)' }};">Rp {{ number_format($netProfit, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Kolom 2: Arus Kas Riil (Cash Flow) -->
            <div class="card">
                <div class="card-header">
                    <span>💵 Arus Kas Riil (Cash Flow)</span>
                </div>
                <div class="line-item">
                    <span>Penjualan Tunai Masuk</span>
                    <strong style="color: var(--success);">+ Rp {{ number_format($cashRevenue, 0, ',', '.') }}</strong>
                </div>
                <div class="line-item">
                    <span>Pelunasan Piutang dari Pelanggan</span>
                    <strong style="color: var(--success);">+ Rp {{ number_format($totalCashIn - $cashRevenue, 0, ',', '.') }}</strong>
                </div>
                <div class="line-item">
                    <span>Pembelian Kulakan Tunai Keluar</span>
                    <strong style="color: var(--danger);">- Rp {{ number_format($totalCashOut - $totalExpenses, 0, ',', '.') }}</strong>
                </div>
                <div class="line-item">
                    <span>Pengeluaran Kas Operasional</span>
                    <strong style="color: var(--danger);">- Rp {{ number_format($totalExpenses, 0, ',', '.') }}</strong>
                </div>
                <div class="line-item line-bold" style="background: rgba(28, 90%, 50%, 0.08); padding: 0.75rem 0.5rem; border-radius: 6px; margin-top: 0.5rem;">
                    <span>Arus Kas Bersih (Net Cash Flow)</span>
                    <span style="color: {{ $netCashFlow >= 0 ? 'var(--primary)' : 'var(--danger)' }};">Rp {{ number_format($netCashFlow, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Kolom Snapshot Neraca Saldo Toko -->
        <div class="card">
            <div class="card-header">
                <span>⚖️ Saldo Neraca & Valuasi Aset Toko</span>
            </div>
            <div class="metric-grid" style="margin-bottom: 0;">
                <div style="background: var(--surface-accent); padding: 1rem; border-radius: var(--radius-sm);">
                    <div style="font-size: 0.8rem; color: var(--text-muted);">Piutang Usaha (Aset Berjalan di Pelanggan)</div>
                    <div style="font-size: 1.4rem; font-weight: 800; color: var(--warning); margin-top: 0.3rem;">
                        Rp {{ number_format($totalActiveReceivables, 0, ',', '.') }}
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem;">Belum disetorkan pelanggan</div>
                </div>
                <div style="background: var(--surface-accent); padding: 1rem; border-radius: var(--radius-sm);">
                    <div style="font-size: 0.8rem; color: var(--text-muted);">Utang Usaha (Kewajiban Tempo ke Supplier)</div>
                    <div style="font-size: 1.4rem; font-weight: 800; color: var(--danger); margin-top: 0.3rem;">
                        Rp {{ number_format($totalActivePayables, 0, ',', '.') }}
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem;">Belum dibayarkan ke supplier</div>
                </div>
                <div style="background: var(--surface-accent); padding: 1rem; border-radius: var(--radius-sm);">
                    <div style="font-size: 0.8rem; color: var(--text-muted);">Valuasi Nilai Inventaris / Stok Gudang</div>
                    <div style="font-size: 1.4rem; font-weight: 800; color: var(--info); margin-top: 0.3rem;">
                        Rp {{ number_format($inventoryValuation, 0, ',', '.') }}
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem;">Berdasarkan HPP x Total Stok</div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
