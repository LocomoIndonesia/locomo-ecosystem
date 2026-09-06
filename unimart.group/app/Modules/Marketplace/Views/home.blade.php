<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unimart Marketplace - Temukan Toko & Produk Pilihan di Sekitarmu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
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
        body { background: var(--background); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; }
        
        header.market-header { background: rgba(15, 23, 30, 0.85); backdrop-filter: blur(14px); border-bottom: 1px solid var(--border); padding: 1.2rem 2rem; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 50; }
        .logo { font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 800; color: var(--primary); text-decoration: none; }
        .logo span { font-family: 'Outfit', sans-serif; font-size: 0.75rem; background: var(--primary); color: #000; padding: 0.15rem 0.5rem; border-radius: 20px; font-weight: 700; margin-left: 0.3rem; }

        .hero-market { background: radial-gradient(circle at 50% 30%, hsl(210, 24%, 16%), hsl(210, 30%, 8%)); padding: 4rem 1.5rem 3rem; text-align: center; border-bottom: 1px solid var(--border); }
        .hero-market h1 { font-family: 'Playfair Display', serif; font-size: 2.8rem; font-weight: 800; margin-bottom: 0.8rem; }
        .hero-market p { font-size: 1.1rem; color: var(--text-muted); max-width: 650px; margin: 0 auto 2rem; }

        .search-bar-container { max-width: 750px; margin: 0 auto; background: var(--surface); border: 1px solid var(--border); border-radius: 40px; padding: 0.4rem 0.6rem; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .search-input { flex-grow: 1; background: transparent; border: none; padding: 0.8rem 1.2rem; color: #fff; font-size: 0.95rem; outline: none; }
        .btn-search { background: var(--primary); color: #000; font-weight: 700; border: none; padding: 0.8rem 1.8rem; border-radius: 30px; cursor: pointer; transition: var(--transition); }
        .btn-search:hover { background: var(--primary-light); }

        .container { max-width: 1250px; width: 100%; margin: 0 auto; padding: 2.5rem 1.5rem; flex-grow: 1; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .section-title { font-family: 'Playfair Display', serif; font-size: 1.8rem; font-weight: 700; }

        /* Stores Horizontal Carousel */
        .stores-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1.2rem; margin-bottom: 3rem; }
        .store-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.2rem; transition: var(--transition); text-decoration: none; color: inherit; display: block; }
        .store-card:hover { transform: translateY(-3px); border-color: var(--primary); }
        .store-type-badge { display: inline-block; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; padding: 0.2rem 0.5rem; border-radius: 10px; background: rgba(230, 115, 0, 0.15); color: var(--primary); margin-bottom: 0.5rem; }
        .store-name { font-size: 1.1rem; font-weight: 700; color: #fff; margin-bottom: 0.2rem; }

        /* Products Grid */
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.5rem; }
        .product-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; display: flex; flex-direction: column; transition: var(--transition); }
        .product-card:hover { transform: translateY(-4px); border-color: rgba(230, 115, 0, 0.4); }
        .product-img { width: 100%; height: 160px; object-fit: cover; background: var(--surface-accent); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; }
        .product-body { padding: 1.2rem; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
        .product-store { font-size: 0.75rem; color: var(--primary); font-weight: 600; margin-bottom: 0.3rem; }
        .product-name { font-size: 1.1rem; font-weight: 700; color: #fff; margin-bottom: 0.5rem; }
        .product-price { font-size: 1.2rem; font-weight: 800; color: #fff; }
        .btn-visit { background: var(--surface-accent); color: #fff; text-decoration: none; padding: 0.45rem 0.9rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; border: 1px solid var(--border); transition: var(--transition); }
        .btn-visit:hover { background: var(--primary); color: #000; border-color: var(--primary); }

        footer { background: var(--surface); border-top: 1px solid var(--border); padding: 2.5rem 1.5rem; text-align: center; font-size: 0.85rem; color: var(--text-muted); }
    </style>
</head>
<body>

    <header class="market-header">
        <a href="{{ route('marketplace.home') }}" class="logo">Unimart<span>marketplace</span></a>
        <div>
            <a href="{{ route('owner.register') }}" style="background: var(--primary); color: #000; text-decoration: none; padding: 0.5rem 1.2rem; border-radius: 20px; font-weight: 700; font-size: 0.85rem;">
                Daftarkan Toko Saya 🚀
            </a>
        </div>
    </header>

    <div class="hero-market">
        <h1>Temukan Segala Kebutuhan dari Toko & Kafe Pilihan</h1>
        <p>Jelajahi produk segar, menu kuliner kafe spesial, dan kebutuhan grosir langsung dari toko mitra terpercaya di sekitarmu.</p>

        <form action="{{ route('marketplace.home') }}" method="GET" class="search-bar-container">
            <input type="text" name="q" class="search-input" value="{{ $search }}" placeholder="Cari kopi, sembako, menu makanan, atau nama toko...">
            <select name="type" style="background: transparent; border: none; color: var(--text-muted); padding: 0 0.8rem; font-size: 0.85rem; outline: none; border-left: 1px solid var(--border);">
                <option value="" style="background: var(--surface); color: #fff;">Semua Industri</option>
                <option value="caffe" {{ $businessType === 'caffe' ? 'selected' : '' }} style="background: var(--surface); color: #fff;">☕ Kafe & F&B</option>
                <option value="grosiran" {{ $businessType === 'grosiran' ? 'selected' : '' }} style="background: var(--surface); color: #fff;">📦 Grosir</option>
                <option value="minimarket" {{ $businessType === 'minimarket' ? 'selected' : '' }} style="background: var(--surface); color: #fff;">🛒 Minimarket</option>
            </select>
            <button type="submit" class="btn-search">Cari Produk</button>
        </form>
    </div>

    <div class="container">
        <!-- Direktori Toko Unggulan -->
        <div class="section-header">
            <h2 class="section-title">🏬 Toko & Kafe Mitra Pilihan</h2>
            <div style="font-size: 0.85rem; color: var(--text-muted);">Menampilkan {{ $stores->count() }} Toko Aktif</div>
        </div>

        <div class="stores-grid">
            @foreach($stores as $store)
                <a href="{{ $store->business_type === 'caffe' ? route('caffe.qr', ['alias' => $store->domain]) : route('grosir.landing', ['alias' => $store->domain]) }}" class="store-card">
                    <span class="store-type-badge">{{ $store->business_type === 'caffe' ? '☕ Kafe & F&B' : '📦 Grosir' }}</span>
                    <h3 class="store-name">{{ $store->name }}</h3>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">{{ $store->city ?? 'Indonesia' }} • {{ $store->products_count }} Produk</p>
                    <div style="font-size: 0.8rem; color: var(--primary); font-weight: 600;">Kunjungi Toko →</div>
                </a>
            @endforeach
        </div>

        <!-- Katalog Produk Teragregasi Seluruh Platform -->
        <div class="section-header">
            <h2 class="section-title">🛍️ Jelajahi Seluruh Produk</h2>
            <div style="font-size: 0.85rem; color: var(--text-muted);">{{ $products->total() }} Produk Tersedia</div>
        </div>

        @if($products->isEmpty())
            <div style="text-align: center; padding: 4rem; color: var(--text-muted); background: var(--surface); border-radius: var(--radius-md); border: 1px solid var(--border);">
                <div style="font-size: 3rem; margin-bottom: 0.5rem;">🔍</div>
                <h3>Tidak Ada Produk yang Ditemukan</h3>
                <p>Coba gunakan kata kunci lain atau pilih industri yang berbeda.</p>
            </div>
        @else
            <div class="products-grid">
                @foreach($products as $prod)
                    <div class="product-card">
                        @if($prod->image_url)
                            <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}" class="product-img">
                        @else
                            <div class="product-img">🛍️</div>
                        @endif
                        <div class="product-body">
                            <div>
                                <div class="product-store">🏪 {{ $prod->tenant->name ?? 'Toko Mitra' }}</div>
                                <h4 class="product-name">{{ $prod->name }}</h4>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                                <div class="product-price">Rp {{ number_format($prod->price, 0, ',', '.') }}</div>
                                <a href="{{ $prod->tenant && $prod->tenant->business_type === 'caffe' ? route('caffe.qr', ['alias' => $prod->tenant->domain]) : route('grosir.landing', ['alias' => $prod->tenant->domain ?? 'default']) }}" class="btn-visit">
                                    Lihat Menu
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="margin-top: 2rem;">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <footer>
        <p>© {{ date('Y') }} <strong>Unimart.group</strong> — Platform Agregator Marketplace & Multi-Store SaaS Terpadu</p>
    </footer>

</body>
</html>
