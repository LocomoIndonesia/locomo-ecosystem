<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - Unimart.group</title>
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
            --transition: all 0.3s ease;
            --shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            --radius-lg: 20px;
            --radius-md: 14px;
            --radius-sm: 8px;
            --secondary: hsl(150, 60%, 35%);
            --accent-red: #ff453a;
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

        /* Content Box */
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
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.8rem;
        }

        .btn-add {
            background: var(--primary);
            color: var(--background);
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: var(--radius-md);
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(230, 115, 0, 0.3);
            font-size: 0.85rem;
            transition: var(--transition);
        }

        .btn-add:hover {
            background: var(--primary-light);
        }

        /* Responsive Table & Card List (Mobile First) */
        .desktop-table-wrapper {
            display: none; /* Sembunyikan tabel di HP */
        }

        .mobile-card-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .menu-mobile-card {
            background: var(--surface-accent);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            position: relative;
        }

        .menu-mobile-img {
            width: 70px;
            height: 70px;
            border-radius: var(--radius-sm);
            object-fit: cover;
            border: 1px solid var(--border);
        }

        .menu-mobile-details {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .menu-mobile-details h4 {
            font-size: 0.95rem;
            font-weight: 700;
        }

        .menu-mobile-details .price {
            color: var(--primary-light);
            font-weight: 800;
            font-size: 0.9rem;
        }

        .menu-mobile-details .stock {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .menu-mobile-actions {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .btn-action {
            background: transparent;
            border: 1px solid var(--border);
            padding: 0.4rem 0.8rem;
            border-radius: var(--radius-sm);
            color: var(--text-main);
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: var(--transition);
        }

        .btn-action.edit {
            border-color: var(--primary);
            color: var(--primary-light);
        }

        .btn-action.delete {
            color: var(--accent-red);
            border-color: rgba(255, 69, 58, 0.2);
        }

        .btn-action.delete:hover {
            background: rgba(255, 69, 58, 0.1);
        }

        /* Desktop Table */
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            padding: 1rem;
            border-bottom: 2px solid var(--border);
            color: var(--text-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            font-weight: 700;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.95rem;
        }

        .product-name-area {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .product-img {
            width: 50px;
            height: 50px;
            border-radius: var(--radius-sm);
            object-fit: cover;
            border: 1px solid var(--border);
        }

        .badge-cat {
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.15rem 0.5rem;
            border-radius: 8px;
            text-transform: uppercase;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
        }

        /* Modal */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
            z-index: 100;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem 1.5rem;
            width: 92%;
            max-width: 480px;
            box-shadow: var(--shadow);
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-close {
            position: absolute;
            top: 1.2rem;
            right: 1.2rem;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-muted);
            cursor: pointer;
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
            padding: 0.65rem 0.9rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            background: var(--surface-accent);
            color: var(--text-main);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .form-input:focus {
            border-color: var(--primary);
            outline: none;
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

            .content-box {
                padding: 2rem;
            }

            /* Sembunyikan Mobile Cards, Tampilkan Desktop Table */
            .mobile-card-list {
                display: none;
            }

            .desktop-table-wrapper {
                display: block;
            }
        }
    </style>
</head>
<body x-data="{ showAdd: false, showEdit: false, editItem: {} }">

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
                <h2>Kelola Menu Kafe</h2>
                <p>Ubah harga, kategori, pantau stok bahan baku dan produk secara aman agar tidak terjadi oversell.</p>
            </div>
            <div class="nav-tabs">
                <a href="{{ route('owner.dashboard') }}" class="tab-link">Dasbor</a>
                <a href="{{ route('owner.menus') }}" class="tab-link active">Daftar Menu</a>
                <a href="{{ route('owner.tables') }}" class="tab-link">Kelola Meja</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="content-box">
            <div class="content-header">
                <h3 style="font-size: 1.15rem; font-weight: 700;">Menu Terdaftar</h3>
                <button class="btn-add" @click="showAdd = true">+ Tambah Menu</button>
            </div>

            @if($products->isEmpty())
                <p style="text-align: center; color: var(--text-muted); padding: 3rem 0; font-size: 0.9rem;">Belum ada menu terdaftar.</p>
            @else
                
                <!-- MOBILE VIEW: Card List -->
                <div class="mobile-card-list">
                    @foreach($products as $product)
                        <div class="menu-mobile-card">
                            <img class="menu-mobile-img" src="{{ $product->image_url }}" alt="{{ $product->name }}">
                            <div class="menu-mobile-details">
                                <h4>{{ $product->name }}</h4>
                                <span class="price">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                <span class="stock">
                                    Stok: 
                                    @if($product->stock <= 0)
                                        <span style="color: #ff453a; font-weight: bold;">Habis</span>
                                    @else
                                        <strong>{{ $product->stock }}</strong>
                                    @endif
                                </span>
                            </div>
                            <div class="menu-mobile-actions">
                                <button class="btn-action edit" @click="editItem = {{ json_encode($product) }}; showEdit = true;">Edit</button>
                                <form action="{{ route('owner.menus.delete', $product->id) }}" method="POST" onsubmit="return confirm('Hapus menu ini?');">
                                    @csrf
                                    <button type="submit" class="btn-action delete">Hapus</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- DESKTOP VIEW: Table -->
                <div class="desktop-table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Menu</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>
                                        <div class="product-name-area">
                                            <img class="product-img" src="{{ $product->image_url }}" alt="{{ $product->name }}">
                                            <strong>{{ $product->name }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-cat">{{ $product->category }}</span>
                                    </td>
                                    <td style="font-weight: 700; color: var(--primary);">
                                        Rp {{ number_format($product->price, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        @if($product->stock <= 0)
                                            <span style="color: #ff453a; font-weight: bold;">Habis</span>
                                        @else
                                            <strong>{{ $product->stock }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <button class="btn-action edit" @click="editItem = {{ json_encode($product) }}; showEdit = true;">Edit</button>
                                            <form action="{{ route('owner.menus.delete', $product->id) }}" method="POST" onsubmit="return confirm('Hapus menu ini?');">
                                                @csrf
                                                <button type="submit" class="btn-action delete">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @endif
        </div>
    </main>

    <!-- Modal Tambah Menu -->
    <div class="modal-backdrop" x-show="showAdd" x-transition.opacity style="display: none;">
        <div class="modal-box">
            <button class="modal-close" @click="showAdd = false">&times;</button>
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.2rem; border-bottom: 1px solid var(--border); padding-bottom: 0.4rem;">Tambah Menu Baru</h3>
            <form action="{{ route('owner.menus.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="add_name">Nama Menu</label>
                    <input class="form-input" type="text" id="add_name" name="name" placeholder="Contoh: Kopi Aren" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="add_category">Kategori</label>
                    <select class="form-input" id="add_category" name="category" required>
                        <option value="makanan">Makanan</option>
                        <option value="minuman" selected>Minuman</option>
                        <option value="dessert">Dessert</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="add_price">Harga (Rp)</label>
                    <input class="form-input" type="number" id="add_price" name="price" placeholder="24000" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="add_stock">Stok Awal</label>
                    <input class="form-input" type="number" id="add_stock" name="stock" placeholder="50" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="add_image_url">URL Gambar (Opsional)</label>
                    <input class="form-input" type="text" id="add_image_url" name="image_url" placeholder="Biarkan kosong untuk default">
                </div>
                <button class="btn-add" type="submit" style="width: 100%; margin-top: 1rem; padding: 0.7rem;">Simpan Menu</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit Menu -->
    <div class="modal-backdrop" x-show="showEdit" x-transition.opacity style="display: none;">
        <div class="modal-box">
            <button class="modal-close" @click="showEdit = false">&times;</button>
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.2rem; border-bottom: 1px solid var(--border); padding-bottom: 0.4rem;">Edit Menu</h3>
            <form :action="'/owner/menus/update/' + editItem.id" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="edit_name">Nama Menu</label>
                    <input class="form-input" type="text" id="edit_name" name="name" x-model="editItem.name" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_category">Kategori</label>
                    <select class="form-input" id="edit_category" name="category" x-model="editItem.category" required>
                        <option value="makanan">Makanan</option>
                        <option value="minuman">Minuman</option>
                        <option value="dessert">Dessert</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_price">Harga (Rp)</label>
                    <input class="form-input" type="number" id="edit_price" name="price" x-model="editItem.price" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_stock">Stok</label>
                    <input class="form-input" type="number" id="edit_stock" name="stock" x-model="editItem.stock" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_image_url">URL Gambar</label>
                    <input class="form-input" type="text" id="edit_image_url" name="image_url" x-model="editItem.image_url">
                </div>
                <button class="btn-add" type="submit" style="width: 100%; margin-top: 1rem; padding: 0.7rem;">Perbarui Menu</button>
            </form>
        </div>
    </div>

</body>
</html>
