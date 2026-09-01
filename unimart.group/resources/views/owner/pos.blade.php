<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tenant->name }} - POS Kasir</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>

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
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            --radius-lg: 20px;
            --radius-md: 12px;
            --radius-sm: 8px;
            --accent: hsl(150, 60%, 35%);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }

        body {
            background-color: var(--background);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Header */
        header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
            height: 65px;
        }

        .logo-area { display: flex; align-items: center; gap: 0.8rem; }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.2rem;
        }

        .logo span {
            font-family: 'Outfit', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            background: var(--primary);
            color: var(--background);
            padding: 0.1rem 0.4rem;
            border-radius: 20px;
        }

        .header-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.85rem;
        }

        .status-badge {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.75rem;
            font-weight: 700;
            background: rgba(255,255,255,0.05);
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            border: 1px solid var(--border);
        }

        .status-dot { width: 7px; height: 7px; border-radius: 50%; background: #ff3b30; }
        .status-dot.online { background: #28a745; box-shadow: 0 0 6px #28a745; }

        /* Main POS Grid */
        .pos-container {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            position: relative;
        }

        /* Menu Section */
        .menu-section {
            padding: 1.2rem 1.5rem;
            flex-grow: 1;
            overflow-y: auto;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .product-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 0.8rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 120px;
        }

        .product-card:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .product-info h4 { font-size: 0.9rem; font-weight: 600; margin-bottom: 0.2rem; line-height: 1.3; }
        .product-price { color: var(--primary); font-weight: 700; font-size: 0.85rem; }
        .product-stock { font-size: 0.7rem; color: var(--text-muted); margin-top: 0.4rem; }
        .product-stock span.out { color: #ff3b30; font-weight: bold; }

        /* Cart Item */
        .cart-item-detail h5 { font-size: 0.85rem; font-weight: 600; }
        .cart-item-detail span { font-size: 0.75rem; color: var(--primary); font-weight: bold; }

        .qty-controls { display: flex; align-items: center; gap: 0.5rem; }

        .qty-btn {
            background: var(--surface);
            color: var(--text-main);
            border: 1px solid var(--border);
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .qty-btn:hover { border-color: var(--primary); }

        /* Cart Footer */
        .cart-footer {
            padding: 1.2rem 1.5rem;
            background: var(--surface-accent);
            border-top: 1px solid var(--border);
        }

        .form-group { margin-bottom: 0.8rem; }

        .form-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .form-input {
            width: 100%;
            padding: 0.5rem 0.8rem;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text-main);
            font-size: 0.85rem;
        }

        .form-input:focus { outline: none; border-color: var(--primary); }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            margin-bottom: 0.3rem;
            font-size: 0.85rem;
        }

        .total-row {
            font-size: 1.1rem;
            color: var(--primary);
            font-weight: 800;
            border-top: 1px dashed var(--border);
            padding-top: 0.4rem;
            margin-top: 0.4rem;
        }

        .btn-checkout {
            background: var(--primary);
            color: var(--background);
            border: none;
            width: 100%;
            padding: 0.75rem;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(230, 115, 0, 0.3);
            margin-top: 0.8rem;
        }

        .btn-checkout:hover { background: var(--primary-light); }
        .btn-checkout:disabled { opacity: 0.5; cursor: not-allowed; }

        /* Online Delivery */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.8rem;
        }

        .checkbox-group input[type="checkbox"] {
            accent-color: var(--primary);
            width: 16px;
            height: 16px;
        }

        .checkbox-group label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--primary);
        }

        .btn-gps {
            background: var(--surface);
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 0.4rem 0.8rem;
            border-radius: var(--radius-sm);
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 0.3rem;
        }

        .btn-gps:hover { background: var(--primary); color: var(--background); }

        .delivery-info {
            background: rgba(230,115,0,0.08);
            border: 1px solid rgba(230,115,0,0.2);
            border-radius: var(--radius-sm);
            padding: 0.5rem 0.8rem;
            margin-top: 0.5rem;
            font-size: 0.78rem;
        }

        .delivery-info span { color: var(--primary); font-weight: 700; }

        /* FAB Cart */
        .fab-cart {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            background: var(--primary);
            color: var(--background);
            border: none;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            font-size: 1.3rem;
            cursor: pointer;
            box-shadow: 0 5px 20px rgba(230, 115, 0, 0.5);
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            transition: var(--transition);
        }

        .fab-cart:hover { transform: scale(1.05); }

        .fab-cart .badge {
            background: var(--text-main);
            color: var(--background);
            font-size: 0.7rem;
            padding: 0.1rem 0.4rem;
            border-radius: 10px;
            position: absolute;
            top: -2px;
            right: -2px;
            border: 2px solid var(--primary);
        }

        /* Backdrop */
        .backdrop {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 45;
            display: none;
        }

        .backdrop.open { display: block; }

        /* Glassmorphic Dialog Modals */
        .dialog-backdrop {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            z-index: 999999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .dialog-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem 1.5rem;
            width: 90%;
            max-width: 380px;
            text-align: center;
            box-shadow: var(--shadow);
            animation: dialogSlideIn 0.3s ease;
        }

        @keyframes dialogSlideIn {
            from { opacity: 0; transform: translateY(20px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .dialog-icon { font-size: 2.5rem; margin-bottom: 0.8rem; }
        .dialog-title { font-size: 1.3rem; font-weight: 700; margin-bottom: 0.5rem; }
        .dialog-message { color: var(--text-muted); font-size: 0.85rem; line-height: 1.5; }

        .btn-dialog {
            background: var(--primary);
            color: var(--background);
            border: none;
            padding: 0.5rem 2rem;
            border-radius: var(--radius-md);
            font-weight: 700;
            cursor: pointer;
            margin-top: 1.2rem;
            transition: var(--transition);
        }

        .btn-dialog:hover { opacity: 0.9; }

        /* Confirm Dialog buttons */
        .dialog-actions {
            display: flex;
            gap: 0.8rem;
            justify-content: center;
            margin-top: 1.2rem;
        }

        .btn-dialog-cancel {
            background: var(--surface-accent);
            color: var(--text-muted);
            border: 1px solid var(--border);
            padding: 0.5rem 1.5rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-dialog-cancel:hover { color: var(--text-main); border-color: var(--text-muted); }

        /* Cart Sidebar */
        .cart-sidebar {
            position: fixed;
            bottom: 0; left: 0;
            width: 100%;
            height: 80vh;
            background: var(--surface);
            border-top: 2px solid var(--border);
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            z-index: 50;
            display: flex;
            flex-direction: column;
            transform: translateY(100%);
            transition: var(--transition);
            box-shadow: 0 -10px 30px rgba(0,0,0,0.6);
        }

        .cart-sidebar.open { transform: translateY(0); }

        .cart-header {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-items {
            flex-grow: 1;
            overflow-y: auto;
            padding: 1.2rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--surface-accent);
            padding: 0.7rem 1rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
        }

        /* Responsive */
        @media (min-width: 768px) {
            .pos-container {
                display: grid;
                grid-template-columns: 1fr 350px;
            }

            .menu-section { padding: 1.5rem 2rem; }
            .menu-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1.2rem; }

            .cart-sidebar {
                position: relative;
                transform: none;
                height: calc(100vh - 65px);
                border-top: none;
                border-left: 1px solid var(--border);
                border-radius: 0;
                box-shadow: none;
            }

            .fab-cart, .backdrop { display: none !important; }
            .close-cart { display: none !important; }
        }

        @media (min-width: 1200px) {
            .pos-container { grid-template-columns: 1.35fr 0.65fr; }
            .menu-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); }
        }
    </style>
</head>
<body x-data="posApp()">

    <!-- Header -->
    <header>
        <div class="logo-area">
            <a href="{{ auth()->user()->isOwner() ? route('owner.dashboard') : route('staff.dashboard') }}" class="logo">{{ $tenant->name }}<span>POS</span></a>
        </div>
        <div class="header-info">
            <span style="font-weight: 600;">Shift #{{ $shift->id }}</span>
            <div class="status-badge">
                <span class="status-dot" :class="socketConnected ? 'online' : ''"></span>
                <span x-text="socketConnected ? 'Real-time' : 'Offline'">Offline</span>
            </div>
            <a href="{{ auth()->user()->isOwner() ? route('owner.dashboard') : route('staff.dashboard') }}" style="color: var(--text-muted); text-decoration: none; font-size: 0.85rem;">⬅ Dasbor</a>
        </div>
    </header>

    <div class="pos-container">
        <!-- Menu (Kiri/Utama) -->
        <section class="menu-section">
            <h3 style="font-size: 1.15rem; font-weight: 700;">Daftar Katalog Menu</h3>
            <div class="menu-grid">
                @foreach($products as $product)
                    <div class="product-card" @click="addToCart({{ json_encode($product) }})">
                        <div class="product-info">
                            <h4>{{ $product->name }}</h4>
                            <span class="product-price">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                        </div>
                        <div class="product-stock">
                            Stok:
                            @if($product->stock <= 0)
                                <span class="out">Habis</span>
                            @else
                                <strong>{{ $product->stock }}</strong>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Backdrop Blur (HP Only) -->
        <div class="backdrop" :class="cartOpen ? 'open' : ''" @click="cartOpen = false"></div>

        <!-- Keranjang Belanja -->
        <section class="cart-sidebar" :class="cartOpen ? 'open' : ''">
            <div class="cart-header">
                <h3 style="font-size: 1.1rem; font-weight: 700;">Keranjang</h3>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span style="font-size: 0.8rem; color: var(--text-muted);" x-text="cart.length + ' item'"></span>
                    <button class="close-cart" style="background: none; border: none; font-size: 1.8rem; color: var(--text-muted); cursor: pointer;" @click="cartOpen = false">&times;</button>
                </div>
            </div>

            <!-- List Item -->
            <div class="cart-items">
                <template x-for="item in cart" :key="item.id">
                    <div class="cart-item">
                        <div class="cart-item-detail">
                            <h5 x-text="item.name"></h5>
                            <span x-text="'Rp ' + (item.price * item.qty).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="qty-controls">
                            <button class="qty-btn" @click="changeQty(item.id, -1)">-</button>
                            <span x-text="item.qty" style="font-weight: bold; font-size: 0.85rem; width: 15px; text-align: center;"></span>
                            <button class="qty-btn" @click="changeQty(item.id, 1)">+</button>
                        </div>
                    </div>
                </template>
                <div x-show="cart.length === 0" style="color: var(--text-muted); text-align: center; margin-top: 3rem; font-size: 0.85rem;">
                    Keranjang kosong. Pilih produk menu.
                </div>
            </div>

            <!-- Footer Kasir -->
            <div class="cart-footer">
                <!-- Nama Pelanggan -->
                <div class="form-group">
                    <label class="form-label" for="customer_name">Nama Pelanggan / Alias</label>
                    <input class="form-input" type="text" id="customer_name" x-model="customerName" placeholder="Nama pelanggan...">
                </div>

                <!-- Jenis Order -->
                <div class="form-group">
                    <label class="form-label" for="order_type">Jenis Order</label>
                    <select class="form-input" id="order_type" x-model="orderType">
                        <option value="dine_in">Dine In (Makan di Tempat)</option>
                        <option value="takeaway">Takeaway (Bawa Pulang)</option>
                    </select>
                </div>

                <!-- Checkbox Online Delivery -->
                <div class="checkbox-group">
                    <input type="checkbox" id="is_online" x-model="isOnline">
                    <label for="is_online">🛵 Pesan Online (Delivery)</label>
                </div>

                <!-- Pilih Meja (hanya jika dine_in DAN bukan online) -->
                <div class="form-group" x-show="orderType === 'dine_in' && !isOnline">
                    <label class="form-label" for="table_id">Pilih Meja (Available)</label>
                    <select class="form-input" id="table_id" x-model="tableId">
                        <option value="">-- Pilih Meja --</option>
                        @foreach($tables as $t)
                            @if($t->status === 'available')
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Delivery Fields (jika isOnline) -->
                <template x-if="isOnline">
                    <div>
                        <div class="form-group">
                            <label class="form-label">Alamat Pengiriman</label>
                            <input class="form-input" type="text" x-model="deliveryAddress" placeholder="Jl. Contoh No. 123...">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Koordinat GPS</label>
                            <input class="form-input" type="text" x-model="deliveryCoords" placeholder="Lat, Lng" readonly>
                            <button class="btn-gps" type="button" @click="detectGPS()">📍 Deteksi GPS</button>
                        </div>
                        <div class="delivery-info" x-show="deliveryDistance > 0">
                            <div>Jarak: <span x-text="deliveryDistance.toFixed(2) + ' km'"></span></div>
                            <div>Ongkir: <span x-text="'Rp ' + deliveryFee.toLocaleString('id-ID')"></span></div>
                        </div>
                    </div>
                </template>

                <!-- Pembayaran -->
                <div class="form-group">
                    <label class="form-label" for="payment_method">Pembayaran</label>
                    <select class="form-input" id="payment_method" x-model="paymentMethod">
                        <option value="cash">Tunai</option>
                        <option value="qris">QRIS Resmi Toko</option>
                        <option value="transfer">Transfer Manual</option>
                    </select>
                </div>

                <!-- Summary -->
                <div class="summary-row" style="margin-top: 0.8rem;">
                    <span>Subtotal</span>
                    <span x-text="'Rp ' + subtotal.toLocaleString('id-ID')"></span>
                </div>
                <div class="summary-row" x-show="isOnline && deliveryFee > 0">
                    <span>Ongkir</span>
                    <span x-text="'Rp ' + deliveryFee.toLocaleString('id-ID')"></span>
                </div>
                <div class="summary-row total-row">
                    <span>Total</span>
                    <span x-text="'Rp ' + grandTotal.toLocaleString('id-ID')"></span>
                </div>
                <button class="btn-checkout" @click="checkout()" :disabled="cart.length === 0 || loading">
                    <span x-show="!loading">Checkout & Kirim Dapur</span>
                    <span x-show="loading">Memproses...</span>
                </button>
            </div>
        </section>
    </div>

    <!-- FAB Cart (HP Only) -->
    <button class="fab-cart" @click="cartOpen = true" x-show="cart.length > 0">
        🛒 <span class="badge" x-text="cart.reduce((sum, item) => sum + item.qty, 0)"></span>
    </button>

    <!-- Success Dialog -->
    <div class="dialog-backdrop" x-show="dialogType === 'success'" x-transition.opacity style="display: none;">
        <div class="dialog-box">
            <div class="dialog-icon">✅</div>
            <h2 class="dialog-title">Sukses!</h2>
            <p class="dialog-message" x-text="dialogMessage"></p>
            <button class="btn-dialog" @click="closeDialog(true)">Tutup</button>
        </div>
    </div>

    <!-- Error/Warning Dialog -->
    <div class="dialog-backdrop" x-show="dialogType === 'error'" x-transition.opacity style="display: none;">
        <div class="dialog-box" style="border-color: rgba(255,59,48,0.3);">
            <div class="dialog-icon">⚠️</div>
            <h2 class="dialog-title" style="color: #ff3b30;">Perhatian</h2>
            <p class="dialog-message" x-text="dialogMessage"></p>
            <button class="btn-dialog" style="background: #ff3b30;" @click="closeDialog(false)">Tutup</button>
        </div>
    </div>

    <!-- Info Dialog (for order-ready notification) -->
    <div class="dialog-backdrop" x-show="dialogType === 'info'" x-transition.opacity style="display: none;">
        <div class="dialog-box" style="border-color: rgba(40,167,69,0.3);">
            <div class="dialog-icon">🍽️</div>
            <h2 class="dialog-title" style="color: var(--accent);">Pesanan Siap!</h2>
            <p class="dialog-message" x-text="dialogMessage"></p>
            <button class="btn-dialog" style="background: var(--accent);" @click="closeDialog(false)">Mengerti</button>
        </div>
    </div>

    <!-- Script POS -->
    <script>
        function posApp() {
            return {
                tenantId: {{ $tenant->id }},
                alias: '{{ $alias }}',
                tenantLat: {{ $tenant->settings['latitude'] ?? -6.200000 }},
                tenantLng: {{ $tenant->settings['longitude'] ?? 106.816666 }},
                cart: [],
                customerName: '',
                orderType: 'dine_in',
                tableId: '',
                paymentMethod: 'cash',
                isOnline: false,
                deliveryAddress: '',
                deliveryCoords: '',
                deliveryDistance: 0,
                deliveryFee: 0,
                loading: false,
                socketConnected: false,
                socket: null,
                cartOpen: false,

                // Dialog state (replaces all alert() calls)
                dialogType: '', // 'success', 'error', 'info', ''
                dialogMessage: '',

                init() {
                    this.socket = io('http://gateway.locomo.id.localhost');

                    this.socket.on('connect', () => {
                        this.socketConnected = true;
                        this.socket.emit('join', 'caffe-' + this.tenantId + '-kitchen');
                    });

                    this.socket.on('disconnect', () => {
                        this.socketConnected = false;
                    });

                    this.socket.on('order-ready', (data) => {
                        this.showDialog('info', `Pesanan ${data.order_number} untuk ${data.table_number} SIAP DIANTAR!`);
                    });
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
                },

                get grandTotal() {
                    return this.subtotal + (this.isOnline ? this.deliveryFee : 0);
                },

                showDialog(type, message) {
                    this.dialogType = type;
                    this.dialogMessage = message;
                },

                closeDialog(reload) {
                    this.dialogType = '';
                    this.dialogMessage = '';
                    if (reload) {
                        window.location.reload();
                    }
                },

                addToCart(product) {
                    if (product.stock <= 0) {
                        this.showDialog('error', `Stok menu "${product.name}" telah habis.`);
                        return;
                    }

                    const exist = this.cart.find(item => item.id === product.id);
                    if (exist) {
                        if (exist.qty >= product.stock) {
                            this.showDialog('error', `Tidak dapat menambah. Batas stok menu "${product.name}" tercapai.`);
                            return;
                        }
                        exist.qty++;
                    } else {
                        this.cart.push({
                            id: product.id,
                            name: product.name,
                            price: product.price,
                            qty: 1,
                            stock: product.stock
                        });
                    }
                },

                changeQty(id, delta) {
                    const item = this.cart.find(item => item.id === id);
                    if (!item) return;

                    item.qty += delta;

                    if (item.qty <= 0) {
                        this.cart = this.cart.filter(item => item.id !== id);
                        if (this.cart.length === 0) {
                            this.cartOpen = false;
                        }
                    } else if (item.qty > item.stock) {
                        item.qty = item.stock;
                        this.showDialog('error', `Batas stok menu "${item.name}" tercapai.`);
                    }
                },

                detectGPS() {
                    if (!navigator.geolocation) {
                        this.showDialog('error', 'Browser Anda tidak mendukung deteksi lokasi GPS.');
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            this.deliveryCoords = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;

                            // Haversine distance
                            this.deliveryDistance = this.calculateDistance(this.tenantLat, this.tenantLng, lat, lng);
                            this.deliveryFee = Math.max(5000, Math.round(this.deliveryDistance * 5000));
                        },
                        () => {
                            this.showDialog('error', 'Gagal mengakses GPS. Silakan izinkan akses lokasi di browser Anda.');
                        }
                    );
                },

                calculateDistance(lat1, lon1, lat2, lon2) {
                    const R = 6371;
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLon = (lon2 - lon1) * Math.PI / 180;
                    const a =
                        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                        Math.sin(dLon / 2) * Math.sin(dLon / 2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                    return R * c;
                },

                checkout() {
                    if (this.cart.length === 0) return;

                    if (!this.customerName.trim()) {
                        this.showDialog('error', 'Mohon isi nama pelanggan / alias sebelum checkout.');
                        return;
                    }

                    if (this.orderType === 'dine_in' && !this.isOnline && !this.tableId) {
                        this.showDialog('error', 'Mohon pilih meja terlebih dahulu untuk pesanan Dine In.');
                        return;
                    }

                    if (this.isOnline && !this.deliveryAddress.trim()) {
                        this.showDialog('error', 'Mohon isi alamat pengiriman untuk pesanan online.');
                        return;
                    }

                    this.loading = true;

                    const payload = {
                        items: this.cart.map(item => ({ id: item.id, qty: item.qty })),
                        order_type: this.isOnline ? 'delivery' : this.orderType,
                        table_id: (!this.isOnline && this.orderType === 'dine_in') ? this.tableId : null,
                        payment_method: this.paymentMethod,
                        customer_name: this.customerName.trim(),
                        is_online: this.isOnline,
                        delivery_address: this.isOnline ? this.deliveryAddress : null,
                        delivery_coords: this.isOnline ? this.deliveryCoords : null,
                        delivery_fee: this.isOnline ? this.deliveryFee : 0,
                        _token: '{{ csrf_token() }}'
                    };

                    fetch(`/caffe/${this.alias}/pos/checkout`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(async res => {
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.showDialog('success', `Pesanan ${data.order.order_number} a/n "${this.customerName}" berhasil dikirim ke dapur.`);
                            this.cart = [];
                            this.customerName = '';
                            this.orderType = 'dine_in';
                            this.tableId = '';
                            this.isOnline = false;
                            this.deliveryAddress = '';
                            this.deliveryCoords = '';
                            this.deliveryDistance = 0;
                            this.deliveryFee = 0;
                            this.cartOpen = false;
                        } else {
                            this.showDialog('error', data.message || 'Gagal memproses transaksi.');
                        }
                    })
                    .catch(() => {
                        this.showDialog('error', 'Terjadi kesalahan jaringan atau server.');
                    })
                    .finally(() => {
                        this.loading = false;
                    });
                }
            }
        }
    </script>
</body>
</html>
