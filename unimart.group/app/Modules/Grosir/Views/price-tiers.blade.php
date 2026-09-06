<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Tier Harga Grosir - Unimart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: hsl(38, 95%, 55%);
            --background: hsl(210, 30%, 8%);
            --surface: hsl(210, 24%, 12%);
            --surface-accent: hsl(210, 24%, 18%);
            --text-main: hsl(210, 20%, 96%);
            --text-muted: hsl(210, 14%, 70%);
            --border: rgba(255, 255, 255, 0.08);
            --danger: hsl(0, 75%, 58%);
            --radius-md: 14px;
            --radius-sm: 8px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body { background: var(--background); color: var(--text-main); min-height: 100vh; }
        header { background: rgba(15, 23, 30, 0.85); border-bottom: 1px solid var(--border); padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        main { max-width: 1000px; margin: 0 auto; padding: 2rem 1.5rem; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 1.5rem; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem; }
        .form-control { width: 100%; background: var(--surface-accent); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.6rem 0.8rem; color: #fff; outline: none; }
        .btn-primary { background: var(--primary); color: #000; font-weight: 700; border: none; padding: 0.6rem 1.2rem; border-radius: var(--radius-sm); cursor: pointer; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: var(--surface-accent); padding: 0.8rem 1rem; font-size: 0.8rem; color: var(--text-muted); }
        td { padding: 0.9rem 1rem; border-top: 1px solid var(--border); font-size: 0.9rem; }
    </style>
</head>
<body>

    <header>
        <div style="font-size: 1.2rem; font-weight: 800; color: var(--primary);">Unimart Grosir: Aturan Tier Harga</div>
        <a href="{{ route('owner.dashboard') }}" style="color: var(--text-muted); text-decoration: none; font-size: 0.85rem;">← Kembali ke Dasbor</a>
    </header>

    <main>
        <div style="margin-bottom: 1.5rem;">
            <h1 style="font-size: 1.6rem; font-weight: 700;">🏷️ Aturan Tier Harga Grosir Bertingkat</h1>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.2rem;">Tentukan harga spesial per produk berdasarkan jumlah pembelian minimum (misal per karton/dus).</p>
        </div>

        @if(session('success'))
            <div style="padding: 0.8rem; background: rgba(34, 197, 94, 0.15); border: 1px solid #22c55e; border-radius: 6px; color: #22c55e; margin-bottom: 1rem; font-size: 0.85rem;">
                ✅ {{ session('success') }}
            </div>
        @endif

        <div class="card">
            <h3 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--primary);">+ Tambah Aturan Tier Baru</h3>
            <form action="{{ route('owner.grosir.tiers.store') }}" method="POST">
                @csrf
                <div class="form-grid">
                    <div>
                        <label style="display: block; font-size: 0.8rem; margin-bottom: 0.3rem; color: var(--text-muted);">Pilih Produk</label>
                        <select name="product_id" class="form-control" required>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} (Normal: Rp {{ number_format($p->price, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; margin-bottom: 0.3rem; color: var(--text-muted);">Minimal Beli (Qty)</label>
                        <input type="number" name="min_quantity" class="form-control" placeholder="Misal: 10" min="2" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; margin-bottom: 0.3rem; color: var(--text-muted);">Harga Grosir Satuan (Rp)</label>
                        <input type="number" name="price_per_unit" class="form-control" placeholder="Misal: 8500" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; margin-bottom: 0.3rem; color: var(--text-muted);">Nama Satuan</label>
                        <input type="text" name="unit_name" class="form-control" value="karton" placeholder="karton / dus / pcs" required>
                    </div>
                </div>
                <button type="submit" class="btn-primary">Simpan Aturan Tier</button>
            </form>
        </div>

        <div class="card">
            <h3 style="font-size: 1.1rem; margin-bottom: 1rem;">Daftar Aturan Tier Aktif</h3>
            @if($tiers->isEmpty())
                <div style="text-align: center; padding: 2rem; color: var(--text-muted); font-size: 0.9rem;">
                    Belum ada aturan tier harga grosir yang dibuat.
                </div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga Eceran Normal</th>
                            <th>Min. Pembelian</th>
                            <th>Harga Grosir</th>
                            <th>Satuan</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tiers as $tier)
                            <tr>
                                <td><strong style="color: #fff;">{{ $tier->product->name ?? '-' }}</strong></td>
                                <td>Rp {{ number_format($tier->product->price ?? 0, 0, ',', '.') }}</td>
                                <td><span style="font-weight: 700; color: var(--primary);">≥ {{ $tier->min_quantity }}</span></td>
                                <td><strong style="color: #22c55e;">Rp {{ number_format($tier->price_per_unit, 0, ',', '.') }}</strong></td>
                                <td>{{ $tier->unit_name }}</td>
                                <td style="text-align: right;">
                                    <form action="{{ route('owner.grosir.tiers.destroy', $tier->id) }}" method="POST" onsubmit="return confirm('Hapus aturan tier ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: transparent; border: 1px solid var(--danger); color: var(--danger); padding: 0.25rem 0.6rem; border-radius: 4px; cursor: pointer; font-size: 0.75rem;">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </main>

</body>
</html>
