<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tenant->name }} - Pusat Grosir & Distributor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: hsl(38, 95%, 55%);
            --primary-light: hsl(38, 95%, 65%);
            --background: hsl(210, 30%, 8%);
            --surface: hsl(210, 24%, 12%);
            --surface-accent: hsl(210, 24%, 18%);
            --text-main: hsl(210, 20%, 96%);
            --text-muted: hsl(210, 14%, 70%);
            --border: rgba(255, 255, 255, 0.08);
            --radius-md: 14px;
            --radius-sm: 8px;
            --transition: all 0.25s ease;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body { background: var(--background); color: var(--text-main); min-height: 100vh; }
        header { background: rgba(15, 23, 30, 0.85); backdrop-filter: blur(12px); border-bottom: 1px solid var(--border); padding: 1.2rem 2rem; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 20; }
        .logo { font-family: 'Playfair Display', serif; font-size: 1.4rem; font-weight: 800; color: var(--primary); text-decoration: none; }
        .logo span { font-family: 'Outfit', sans-serif; font-size: 0.75rem; background: var(--primary); color: #000; padding: 0.15rem 0.5rem; border-radius: 20px; font-weight: 700; margin-left: 0.3rem; }
        main { max-width: 1200px; margin: 0 auto; padding: 2rem 1.5rem; }
        
        .hero { background: linear-gradient(135deg, hsl(210, 24%, 14%), hsl(210, 24%, 10%)); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 3rem 2rem; margin-bottom: 2.5rem; text-align: center; }
        .hero h1 { font-family: 'Playfair Display', serif; font-size: 2.4rem; margin-bottom: 0.8rem; }
        .hero p { font-size: 1rem; color: var(--text-muted); max-width: 700px; margin: 0 auto; }

        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.5rem; }
        .tier-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 12px; background: rgba(234, 179, 8, 0.15); color: var(--primary); font-size: 0.75rem; font-weight: 700; margin-right: 0.3rem; margin-bottom: 0.3rem; }
    </style>
</head>
<body>

    <header>
        <a href="#" class="logo">{{ $tenant->name }}<span>grosir</span></a>
        <div>
            <a href="{{ route('grosir.pos', ['alias' => $alias]) }}" style="background: var(--primary); color: #000; text-decoration: none; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 700; font-size: 0.85rem;">
                💻 Kasir Grosir
            </a>
        </div>
    </header>

    <main>
        <div class="hero">
            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📦</div>
            <h1>Pusat Grosir & Pasokan Toko: {{ $tenant->name }}</h1>
            <p>Melayani pembelian eceran, grosir karton, hingga partai besar dengan potongan harga bertingkat otomatis.</p>
        </div>

        <h2 style="font-size: 1.5rem; margin-bottom: 1.2rem; font-weight: 700;">Daftar Katalog & Skema Harga Grosir</h2>

        <div class="product-grid">
            @foreach($products as $prod)
                <div class="card">
                    <div style="font-size: 0.8rem; color: var(--primary); font-weight: 700; text-transform: uppercase;">{{ $prod->category ?? 'Barang Dagangan' }}</div>
                    <h3 style="font-size: 1.2rem; margin: 0.3rem 0; color: #fff;">{{ $prod->name }}</h3>
                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">Stok Tersedia: <strong>{{ $prod->stock }} unit</strong></div>

                    <div style="background: var(--surface-accent); padding: 0.8rem; border-radius: var(--radius-sm); margin-bottom: 1rem;">
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Harga Normal (Eceran):</div>
                        <div style="font-size: 1.2rem; font-weight: 800; color: #fff;">Rp {{ number_format($prod->price, 0, ',', '.') }} / unit</div>
                    </div>

                    @if(isset($tiers[$prod->id]) && $tiers[$prod->id]->isNotEmpty())
                        <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem;">Potongan Harga Grosir:</div>
                        <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                            @foreach($tiers[$prod->id] as $tier)
                                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; padding: 0.3rem 0.5rem; background: rgba(255,255,255,0.02); border-radius: 4px;">
                                    <span>Beli ≥ {{ $tier->min_quantity }} {{ $tier->unit_name }}:</span>
                                    <strong style="color: var(--primary);">Rp {{ number_format($tier->price_per_unit, 0, ',', '.') }} / {{ $tier->unit_name }}</strong>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">Belum ada tier grosir tambahan.</div>
                    @endif
                </div>
            @endforeach
        </div>
    </main>

</body>
</html>
