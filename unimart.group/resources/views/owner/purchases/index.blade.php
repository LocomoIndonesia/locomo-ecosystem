<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pengadaan & Kulakan (Purchases) - Unimart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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

        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
        .page-title h1 { font-size: 1.6rem; font-weight: 700; }
        .page-title p { font-size: 0.85rem; color: var(--text-muted); margin-top: 0.2rem; }
        .btn-primary { background: var(--primary); color: #000; font-weight: 700; border: none; padding: 0.6rem 1.2rem; border-radius: var(--radius-sm); cursor: pointer; transition: var(--transition); display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none; font-size: 0.9rem; }
        .btn-primary:hover { background: var(--primary-light); transform: translateY(-2px); }

        .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: var(--surface-accent); padding: 0.9rem 1.2rem; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 1rem 1.2rem; border-top: 1px solid var(--border); font-size: 0.9rem; }
        tr:hover td { background: rgba(255, 255, 255, 0.02); }
        
        .badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
        .badge-paid { background: rgba(34, 197, 94, 0.15); color: var(--success); }
        .badge-debt { background: rgba(239, 68, 68, 0.15); color: var(--danger); }
        .badge-partial { background: rgba(234, 179, 8, 0.15); color: var(--warning); }
    </style>
</head>
<body>

    <header class="dashboard-header">
        <a href="/" class="logo">Unimart<span>purchases</span></a>
        <div style="font-size: 0.85rem; color: var(--text-muted);">Toko: <strong style="color: #fff;">{{ $tenant->name }}</strong></div>
    </header>

    <main>
        <!-- Tab Navigasi Toko -->
        <div class="nav-scroller">
            <a href="{{ route('owner.dashboard') }}" class="nav-tab">📊 Dasbor</a>
            <a href="{{ route('owner.menus') }}" class="nav-tab">🍛 Produk & Menu</a>
            <a href="{{ route('owner.tables') }}" class="nav-tab">🪑 Meja</a>
            <a href="{{ route('owner.suppliers.index') }}" class="nav-tab">🚚 Pemasok (Supplier)</a>
            <a href="{{ route('owner.purchases.index') }}" class="nav-tab active">📦 Kulakan (Purchases)</a>
            <a href="{{ route('owner.payables.index') }}" class="nav-tab">📕 Buku Utang Toko</a>
            <a href="{{ route('owner.receivables.index') }}" class="nav-tab">📗 Buku Piutang</a>
            <a href="{{ route('owner.reports.finance') }}" class="nav-tab">📈 Laporan Keuangan</a>
        </div>

        <div class="page-header">
            <div class="page-title">
                <h1>📦 Faktur Pengadaan Stok (Purchases)</h1>
                <p>Riwayat belanja modal barang dari supplier, penambahan stok gudang, dan kalkulasi HPP otomatis.</p>
            </div>
            <a href="{{ route('owner.purchases.create') }}" class="btn-primary">
                <span>+</span> Input Kulakan Baru
            </a>
        </div>

        @if(session('success'))
            <div style="padding: 0.8rem 1rem; background: rgba(34, 197, 94, 0.15); border: 1px solid var(--success); border-radius: var(--radius-sm); color: var(--success); margin-bottom: 1.2rem; font-size: 0.9rem;">
                ✅ {{ session('success') }}
            </div>
        @endif

        <div class="card">
            @if($purchases->isEmpty())
                <div style="padding: 3rem; text-align: center; color: var(--text-muted);">
                    <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📦</div>
                    <h3 style="color: #fff; margin-bottom: 0.3rem;">Belum Ada Faktur Pengadaan</h3>
                    <p style="font-size: 0.85rem; margin-bottom: 1rem;">Catat pembelian barang modal dari supplier untuk memperbarui stok dan HPP.</p>
                    <a href="{{ route('owner.purchases.create') }}" class="btn-primary">Input Kulakan Pertama</a>
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>No. Faktur</th>
                                <th>Tanggal</th>
                                <th>Pemasok (Supplier)</th>
                                <th>Item Barang</th>
                                <th>Total Biaya</th>
                                <th>Status Pembayaran</th>
                                <th>Metode</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchases as $purchase)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #fff; font-family: monospace;">{{ $purchase->purchase_number }}</div>
                                        @if($purchase->supplier_invoice_no)
                                            <div style="font-size: 0.75rem; color: var(--text-muted);">Ref: {{ $purchase->supplier_invoice_no }}</div>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y') }}</td>
                                    <td>
                                        <strong style="color: #fff;">{{ $purchase->supplier->name ?? '-' }}</strong>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.85rem;">
                                            @foreach($purchase->items->take(2) as $item)
                                                <div>• {{ $item->product->name ?? 'Produk' }} ({{ $item->quantity }}x)</div>
                                            @endforeach
                                            @if($purchase->items->count() > 2)
                                                <div style="font-size: 0.75rem; color: var(--text-muted);">+{{ $purchase->items->count() - 2 }} item lainnya</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <strong style="color: #fff;">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        @if($purchase->payment_status === 'paid')
                                            <span class="badge badge-paid">Lunas</span>
                                        @elseif($purchase->payment_status === 'debt')
                                            <span class="badge badge-debt">Tempo (Utang)</span>
                                            @if($purchase->due_date)
                                                <div style="font-size: 0.7rem; color: var(--danger); margin-top: 0.2rem;">
                                                    Jatuh tempo: {{ \Carbon\Carbon::parse($purchase->due_date)->format('d M Y') }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="badge badge-partial">Sebagian</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">{{ str_replace('_', ' ', $purchase->payment_method) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding: 1rem 1.2rem; border-top: 1px solid var(--border);">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>
    </main>

</body>
</html>
