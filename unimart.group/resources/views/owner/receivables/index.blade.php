<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Piutang Pelanggan (Accounts Receivable / Bon) - Unimart</title>
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

        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.2rem; }
        .stat-card-title { font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; }
        .stat-card-val { font-size: 1.6rem; font-weight: 800; color: var(--warning); margin-top: 0.4rem; }

        .filter-bar { display: flex; gap: 0.5rem; margin-bottom: 1rem; }
        .filter-btn { padding: 0.4rem 0.9rem; border-radius: 20px; border: 1px solid var(--border); background: var(--surface); color: var(--text-muted); text-decoration: none; font-size: 0.8rem; font-weight: 600; }
        .filter-btn.active { background: var(--primary); color: #000; border-color: var(--primary); }

        .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: var(--surface-accent); padding: 0.9rem 1.2rem; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 1rem 1.2rem; border-top: 1px solid var(--border); font-size: 0.9rem; }
        tr:hover td { background: rgba(255, 255, 255, 0.02); }
        
        .badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
        .badge-danger { background: rgba(239, 68, 68, 0.15); color: var(--danger); }
        .badge-warning { background: rgba(234, 179, 8, 0.15); color: var(--warning); }
        .badge-success { background: rgba(34, 197, 94, 0.15); color: var(--success); }

        .btn-pay { background: var(--success); color: #fff; font-weight: 700; border: none; padding: 0.4rem 0.8rem; border-radius: 6px; cursor: pointer; font-size: 0.8rem; transition: var(--transition); }
        .btn-pay:hover { opacity: 0.9; }

        .modal-backdrop { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center; z-index: 50; padding: 1rem; }
        .modal { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); max-width: 480px; width: 100%; padding: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--text-muted); }
        .form-control { width: 100%; background: var(--surface-accent); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.6rem 0.8rem; color: #fff; font-size: 0.9rem; outline: none; }
    </style>
</head>
<body x-data="{ showPayModal: false, selectedReceivable: {} }">

    <header class="dashboard-header">
        <a href="/" class="logo">Unimart<span>receivables</span></a>
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
            <a href="{{ route('owner.receivables.index') }}" class="nav-tab active">📗 Buku Piutang</a>
            <a href="{{ route('owner.reports.finance') }}" class="nav-tab">📈 Laporan Keuangan</a>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <h1 style="font-size: 1.6rem; font-weight: 700;">📗 Buku Piutang Pelanggan (Accounts Receivable / Bon)</h1>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.2rem;">Daftar tagihan utang/bon pelanggan yang belum dibayar beserta pencatatan setoran cicilan.</p>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-card-title">Total Piutang Toko di Pelanggan</div>
                <div class="stat-card-val">Rp {{ number_format($totalRemainingDebt, 0, ',', '.') }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-title">Bon Lewat Jatuh Tempo</div>
                <div class="stat-card-val" style="color: {{ $overdueCount > 0 ? 'var(--danger)' : 'var(--success)' }};">
                    {{ $overdueCount }} Pelanggan
                </div>
            </div>
        </div>

        @if(session('success'))
            <div style="padding: 0.8rem 1rem; background: rgba(34, 197, 94, 0.15); border: 1px solid var(--success); border-radius: var(--radius-sm); color: var(--success); margin-bottom: 1.2rem; font-size: 0.9rem;">
                ✅ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="padding: 0.8rem 1rem; background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); border-radius: var(--radius-sm); color: var(--danger); margin-bottom: 1.2rem; font-size: 0.9rem;">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <div class="filter-bar">
            <a href="{{ route('owner.receivables.index', ['status' => 'unpaid']) }}" class="filter-btn {{ $status === 'unpaid' ? 'active' : '' }}">Belum Lunas</a>
            <a href="{{ route('owner.receivables.index', ['status' => 'paid']) }}" class="filter-btn {{ $status === 'paid' ? 'active' : '' }}">Sudah Lunas</a>
            <a href="{{ route('owner.receivables.index', ['status' => 'all']) }}" class="filter-btn {{ $status === 'all' ? 'active' : '' }}">Semua</a>
        </div>

        <div class="card">
            @if($receivables->isEmpty())
                <div style="padding: 3rem; text-align: center; color: var(--text-muted);">
                    <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🎉</div>
                    <h3 style="color: #fff; margin-bottom: 0.3rem;">Tidak Ada Piutang Tertunggak</h3>
                    <p style="font-size: 0.85rem;">Seluruh transaksi pelanggan telah lunas atau belum ada transaksi bon utang.</p>
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>No. Piutang / Nota</th>
                                <th>Pelanggan (Customer)</th>
                                <th>Total Bon</th>
                                <th>Sisa Piutang</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($receivables as $ar)
                                @php
                                    $isOverdue = $ar->due_date && $ar->due_date < now()->toDateString() && $ar->status !== 'paid';
                                @endphp
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #fff; font-family: monospace;">{{ $ar->receivable_number }}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">Pesanan: {{ $ar->order->order_number ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <strong style="color: #fff;">{{ $ar->customer->name ?? '-' }}</strong>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">HP: {{ $ar->customer->phone ?? '-' }}</div>
                                    </td>
                                    <td>Rp {{ number_format($ar->total_debt_amount, 0, ',', '.') }}</td>
                                    <td>
                                        <strong style="color: {{ $ar->remaining_amount > 0 ? 'var(--warning)' : 'var(--success)' }};">
                                            Rp {{ number_format($ar->remaining_amount, 0, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td>
                                        @if($ar->due_date)
                                            <span style="color: {{ $isOverdue ? 'var(--danger)' : 'var(--text-main)' }}; font-weight: {{ $isOverdue ? '700' : '400' }};">
                                                {{ \Carbon\Carbon::parse($ar->due_date)->format('d M Y') }}
                                            </span>
                                            @if($isOverdue)
                                                <span class="badge badge-danger" style="display: block; width: fit-content; margin-top: 0.2rem;">Lewat Tempo</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($ar->status === 'paid')
                                            <span class="badge badge-success">Lunas</span>
                                        @elseif($ar->status === 'partially_paid')
                                            <span class="badge badge-warning">Dicicil</span>
                                        @else
                                            <span class="badge badge-danger">Belum Bayar</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right;">
                                        @if($ar->remaining_amount > 0)
                                            <button @click="showPayModal = true; selectedReceivable = {{ json_encode($ar) }}" class="btn-pay">
                                                Catat Setoran / Lunas
                                            </button>
                                        @else
                                            <span style="font-size: 0.8rem; color: var(--success);">✓ Lunas</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding: 1rem 1.2rem; border-top: 1px solid var(--border);">
                    {{ $receivables->links() }}
                </div>
            @endif
        </div>

        <!-- Modal Catat Setoran Piutang Pelanggan -->
        <div x-show="showPayModal" class="modal-backdrop" style="display: none;">
            <div class="modal" @click.outside="showPayModal = false">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.2rem;">Terima Setoran Bon Pelanggan</h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.2rem;" x-text="'Pelanggan: ' + (selectedReceivable.customer ? selectedReceivable.customer.name : '')"></p>

                <form :action="'{{ url('/owner/receivables') }}/' + selectedReceivable.id + '/payment'" method="POST">
                    @csrf
                    <div style="background: var(--surface-accent); padding: 0.8rem; border-radius: var(--radius-sm); margin-bottom: 1rem;">
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Sisa Bon yang Belum Dibayar:</div>
                        <div style="font-size: 1.3rem; font-weight: 800; color: var(--warning);" x-text="'Rp ' + (selectedReceivable.remaining_amount ? Number(selectedReceivable.remaining_amount).toLocaleString('id-ID') : 0)"></div>
                    </div>

                    <div class="form-group">
                        <label>Nominal Setoran (Rp) *</label>
                        <input type="number" name="amount_paid" class="form-control" required min="1000" :max="selectedReceivable.remaining_amount" :value="selectedReceivable.remaining_amount">
                    </div>

                    <div class="form-group">
                        <label>Metode Pembayaran *</label>
                        <select name="payment_method" class="form-control" required>
                            <option value="cash">Tunai (Masuk Kas Laci Kasir Shift)</option>
                            <option value="qris_online">QRIS Online</option>
                            <option value="bank_transfer">Transfer Bank</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Catatan Setoran</label>
                        <input type="text" name="notes" class="form-control" placeholder="Contoh: Titip lewat kasir siang">
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.5rem;">
                        <button type="button" @click="showPayModal = false" style="background: transparent; border: 1px solid var(--border); color: var(--text-muted); padding: 0.5rem 1rem; border-radius: var(--radius-sm); cursor: pointer;">
                            Batal
                        </button>
                        <button type="submit" class="btn-pay" style="padding: 0.6rem 1.2rem; font-size: 0.9rem;">
                            Simpan Setoran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

</body>
</html>
