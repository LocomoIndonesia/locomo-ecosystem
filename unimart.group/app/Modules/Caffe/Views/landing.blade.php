<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tenant->name }} - Kafe & Resto Pilihan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
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
            --radius-md: 16px;
            --radius-sm: 8px;
            --transition: all 0.3s ease;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body { background: var(--background); color: var(--text-main); min-height: 100vh; overflow-x: hidden; }

        header.cafe-header { background: rgba(15, 23, 30, 0.85); backdrop-filter: blur(14px); border-bottom: 1px solid var(--border); padding: 1.2rem 2rem; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 50; }
        .logo { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 800; color: var(--primary); text-decoration: none; }
        .logo span { font-family: 'Outfit', sans-serif; font-size: 0.75rem; background: var(--primary); color: #000; padding: 0.15rem 0.5rem; border-radius: 20px; font-weight: 700; margin-left: 0.3rem; }

        .hero { padding: 4rem 1.5rem; text-align: center; max-width: 900px; margin: 0 auto; }
        .hero-badge { display: inline-block; padding: 0.35rem 0.9rem; background: rgba(230, 115, 0, 0.12); border: 1px solid rgba(230, 115, 0, 0.3); color: var(--primary); border-radius: 30px; font-size: 0.85rem; font-weight: 700; margin-bottom: 1rem; }
        .hero h1 { font-family: 'Playfair Display', serif; font-size: 2.8rem; font-weight: 800; line-height: 1.2; margin-bottom: 1rem; }
        .hero p { font-size: 1.1rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 2rem; }
        .hero-actions { display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; }
        .btn-primary { background: var(--primary); color: #000; font-weight: 700; border: none; padding: 0.8rem 1.8rem; border-radius: 30px; text-decoration: none; font-size: 1rem; transition: var(--transition); display: inline-flex; align-items: center; gap: 0.5rem; }
        .btn-primary:hover { background: var(--primary-light); transform: translateY(-2px); }
        .btn-secondary { background: var(--surface-accent); color: #fff; border: 1px solid var(--border); padding: 0.8rem 1.6rem; border-radius: 30px; text-decoration: none; font-size: 1rem; font-weight: 600; }

        .menu-section { max-width: 1200px; margin: 0 auto; padding: 2rem 1.5rem 5rem; }
        .section-title { font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 0.5rem; text-align: center; }
        .section-desc { text-align: center; color: var(--text-muted); margin-bottom: 2.5rem; font-size: 0.95rem; }

        .menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
        .menu-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; transition: var(--transition); display: flex; flex-direction: column; }
        .menu-card:hover { transform: translateY(-4px); border-color: rgba(230, 115, 0, 0.4); }
        .menu-img { width: 100%; height: 180px; object-fit: cover; background: var(--surface-accent); display: flex; align-items: center; justify-content: center; font-size: 3rem; }
        .menu-body { padding: 1.2rem; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
        .menu-name { font-size: 1.2rem; font-weight: 700; color: #fff; margin-bottom: 0.3rem; }
        .menu-cat { font-size: 0.75rem; text-transform: uppercase; color: var(--primary); font-weight: 700; margin-bottom: 0.5rem; }
        .menu-price { font-size: 1.2rem; font-weight: 800; color: #fff; margin-top: 0.8rem; }

        footer { border-top: 1px solid var(--border); padding: 2.5rem 1.5rem; text-align: center; font-size: 0.85rem; color: var(--text-muted); background: var(--surface); }
    </style>
</head>
<body>

    <header class="cafe-header">
        <a href="#" class="logo">{{ $tenant->name }}<span>cafe</span></a>
        <div>
            <a href="{{ route('caffe.qr', ['alias' => $alias]) }}" class="btn-primary" style="padding: 0.5rem 1.2rem; font-size: 0.85rem;">
                📱 QR Menu Meja
            </a>
        </div>
    </header>

    <div class="hero">
        <div class="hero-badge">☕ {{ $tenant->business_type === 'caffe' ? 'Kafe & Resto Resmi' : 'Toko Mitra Unimart' }}</div>
        <h1>Selamat Datang di {{ $tenant->name }}</h1>
        <p>{{ $tenant->settings['landing_tagline'] ?? 'Nikmati sensasi racikan kopi spesial, hidangan lezat, dan suasana santai yang tak terlupakan bersama kami.' }}</p>
        <div class="hero-actions">
            <a href="#menu" class="btn-primary">Lihat Daftar Menu 📖</a>
            <a href="{{ route('caffe.qr', ['alias' => $alias]) }}" class="btn-secondary">Pesan dari Meja 📲</a>
        </div>
    </div>

    <section class="menu-section" id="menu">
        <h2 class="section-title">Daftar Menu Spesial</h2>
        <p class="section-desc">Pilihan menu terbaik yang diolah langsung oleh barista & koki kami hari ini.</p>

        @if($products->isEmpty())
            <div style="text-align: center; padding: 4rem; color: var(--text-muted);">
                <div style="font-size: 3rem; margin-bottom: 1rem;">☕</div>
                <h3>Menu sedang disiapkan oleh barista kami.</h3>
                <p>Silakan berkunjung kembali beberapa saat lagi.</p>
            </div>
        @else
            <div class="menu-grid">
                @foreach($products as $product)
                    <div class="menu-card">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="menu-img">
                        @else
                            <div class="menu-img">☕</div>
                        @endif
                        <div class="menu-body">
                            <div>
                                <div class="menu-cat">{{ $product->category ?? 'Spesial' }}</div>
                                <h3 class="menu-name">{{ $product->name }}</h3>
                                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.4;">{{ $product->description ?? 'Dibuat dengan bahan baku segar pilihan.' }}</p>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                                <div class="menu-price">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                                <a href="{{ route('caffe.qr', ['alias' => $alias]) }}" class="btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                    Pesan
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <footer>
        <p>© {{ date('Y') }} {{ $tenant->name }} — Didukung oleh <strong>Unimart.group Platform</strong></p>
    </footer>

</body>
</html>
