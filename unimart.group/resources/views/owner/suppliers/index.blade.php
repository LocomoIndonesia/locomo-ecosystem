<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pemasok (Suppliers) - Unimart</title>
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

        .stat-banner { background: linear-gradient(135deg, hsl(210, 24%, 15%), hsl(210, 24%, 11%)); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.2rem 1.5rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .stat-label { font-size: 0.85rem; color: var(--text-muted); font-weight: 500; }
        .stat-val { font-size: 1.6rem; font-weight: 800; color: var(--danger); margin-top: 0.3rem; }

        .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: var(--surface-accent); padding: 0.9rem 1.2rem; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 1rem 1.2rem; border-top: 1px solid var(--border); font-size: 0.9rem; }
        tr:hover td { background: rgba(255, 255, 255, 0.02); }
        .badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
        .badge-danger { background: rgba(239, 68, 68, 0.15); color: var(--danger); }
        .badge-success { background: rgba(34, 197, 94, 0.15); color: var(--success); }

        .modal-backdrop { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center; z-index: 50; padding: 1rem; }
        .modal { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); max-width: 500px; width: 100%; padding: 1.5rem; box-shadow: 0 20px 40px rgba(0,0,0,0.6); }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--text-muted); }
        .form-control { width: 100%; background: var(--surface-accent); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.6rem 0.8rem; color: #fff; font-size: 0.9rem; outline: none; }
        .form-control:focus { border-color: var(--primary); }
    </style>
</head>
<body x-data="{ showModal: false, editMode: false, currentSupplier: {} }">

    <header class="dashboard-header">
        <a href="/" class="logo">Unimart<span>supplier</span></a>
        <div style="font-size: 0.85rem; color: var(--text-muted);">Toko: <strong style="color: #fff;">{{ $tenant->name }}</strong></div>
    </header>

    <main>
        <!-- Tab Navigasi Toko -->
        <div class="nav-scroller">
            <a href="{{ route('owner.dashboard') }}" class="nav-tab">📊 Dasbor</a>
            <a href="{{ route('owner.menus') }}" class="nav-tab">🍛 Produk & Menu</a>
            <a href="{{ route('owner.tables') }}" class="nav-tab">🪑 Meja</a>
            <a href="{{ route('owner.suppliers.index') }}" class="nav-tab active">🚚 Pemasok (Supplier)</a>
            <a href="{{ route('owner.purchases.index') }}" class="nav-tab">📦 Kulakan (Purchases)</a>
            <a href="{{ route('owner.payables.index') }}" class="nav-tab">📕 Buku Utang Toko</a>
            <a href="{{ route('owner.receivables.index') }}" class="nav-tab">📗 Buku Piutang</a>
            <a href="{{ route('owner.reports.finance') }}" class="nav-tab">📈 Laporan Keuangan</a>
        </div>

        <div class="page-header">
            <div class="page-title">
                <h1>🚚 Manajemen Pemasok (Suppliers)</h1>
                <p>Kelola data mitra distributor tempat Anda kulakan barang dan pantau saldo utang usaha.</p>
            </div>
            <button @click="showModal = true; editMode = false; currentSupplier = {}" class="btn-primary">
                <span>+</span> Tambah Pemasok
            </button>
        </div>

        <!-- Alert Ringkasan Utang ke Supplier -->
        <div class="stat-banner">
            <div>
                <div class="stat-label">Total Utang Toko ke Seluruh Pemasok</div>
                <div class="stat-val">Rp {{ number_format($totalPayableDebt, 0, ',', '.') }}</div>
            </div>
            <a href="{{ route('owner.payables.index') }}" class="btn-primary" style="background: var(--surface-accent); color: #fff; border: 1px solid var(--border);">
                Buka Buku Utang →
            </a>
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

        <div class="card">
            @if($suppliers->isEmpty())
                <div style="padding: 3rem; text-align: center; color: var(--text-muted);">
                    <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🚚</div>
                    <h3 style="color: #fff; margin-bottom: 0.3rem;">Belum Ada Pemasok Terdaftar</h3>
                    <p style="font-size: 0.85rem;">Daftarkan supplier pertama Anda untuk mulai mencatat pengadaan dan kulakan barang.</p>
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Pemasok</th>
                                <th>Kontak Person</th>
                                <th>Telepon / WA</th>
                                <th>Rekening Bank</th>
                                <th>Total Transaksi</th>
                                <th>Saldo Utang</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($suppliers as $supplier)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #fff;">{{ $supplier->name }}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $supplier->address ?? '-' }}</div>
                                    </td>
                                    <td>{{ $supplier->contact_person ?? '-' }}</td>
                                    <td>{{ $supplier->phone }}</td>
                                    <td><span style="font-size: 0.8rem; font-family: monospace; color: var(--primary);">{{ $supplier->bank_account_info ?? '-' }}</span></td>
                                    <td><span class="badge" style="background: var(--surface-accent);">{{ $supplier->purchases_count }} Faktur</span></td>
                                    <td>
                                        @if($supplier->current_debt > 0)
                                            <span class="badge badge-danger">Rp {{ number_format($supplier->current_debt, 0, ',', '.') }}</span>
                                        @else
                                            <span class="badge badge-success">Lunas</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right;">
                                        <button @click="showModal = true; editMode = true; currentSupplier = {{ json_encode($supplier) }}" style="background: transparent; border: 1px solid var(--border); color: #fff; padding: 0.35rem 0.7rem; border-radius: 6px; cursor: pointer; font-size: 0.8rem;">
                                            ✏️ Edit
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Modal Tambah/Edit Supplier -->
        <div x-show="showModal" class="modal-backdrop" style="display: none;">
            <div class="modal" @click.outside="showModal = false">
                <h3 style="margin-bottom: 1rem; font-size: 1.2rem;" x-text="editMode ? 'Edit Data Pemasok' : 'Tambah Pemasok Baru'"></h3>
                <form :action="editMode ? '{{ url('/owner/suppliers') }}/' + currentSupplier.id : '{{ route('owner.suppliers.store') }}'" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Nama Pemasok / Distributor *</label>
                        <input type="text" name="name" class="form-control" required x-model="currentSupplier.name" placeholder="Contoh: CV Sumber Kopi Makmur">
                    </div>
                    <div class="form-group">
                        <label>Nama Kontak Person (Sales / PIC)</label>
                        <input type="text" name="contact_person" class="form-control" x-model="currentSupplier.contact_person" placeholder="Contoh: Pak Budi">
                    </div>
                    <div class="form-group">
                        <label>No. HP / WhatsApp *</label>
                        <input type="text" name="phone" class="form-control" required x-model="currentSupplier.phone" placeholder="Contoh: 081234567890">
                    </div>
                    <div class="form-group">
                        <label>Nomor Rekening Pembayaran</label>
                        <input type="text" name="bank_account_info" class="form-control" x-model="currentSupplier.bank_account_info" placeholder="Contoh: BCA 1234567890 a/n CV Sumber Kopi">
                    </div>
                    <div class="form-group">
                        <label>Alamat Gudang / Kantor</label>
                        <textarea name="address" class="form-control" rows="2" x-model="currentSupplier.address" placeholder="Alamat pemasok..."></textarea>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.5rem;">
                        <button type="button" @click="showModal = false" style="background: transparent; border: 1px solid var(--border); color: var(--text-muted); padding: 0.5rem 1rem; border-radius: var(--radius-sm); cursor: pointer;">
                            Batal
                        </button>
                        <button type="submit" class="btn-primary">Simpan Pemasok</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

</body>
</html>
