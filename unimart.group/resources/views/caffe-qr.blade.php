<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ucwords(str_replace('-', ' ', $alias ?? 'Demo Cafe')) }} - QR Menu & Self-Order</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Playfair+Display:ital,wght=0,600;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
    
    <!-- Custom Vanilla CSS -->
    <style>
        :root {
            --primary: hsl(28, 48%, 48%);
            --primary-light: hsl(28, 50%, 60%);
            --primary-dark: hsl(28, 50%, 35%);
            --background: hsl(210, 20%, 98%);
            --surface: hsl(0, 0%, 100%);
            --text-main: hsl(210, 24%, 12%);
            --text-muted: hsl(210, 16%, 46%);
            --accent: hsl(150, 40%, 30%);
            --radius-lg: 20px;
            --radius-md: 12px;
            --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Dark Mode Preferences */
        @media (prefers-color-scheme: dark) {
            :root {
                --background: hsl(210, 24%, 8%);
                --surface: hsl(210, 24%, 12%);
                --text-main: hsl(210, 20%, 95%);
                --text-muted: hsl(210, 14%, 65%);
                --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
                --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.4);
                --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
            }
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            scroll-behavior: smooth;
        }

        body {
            background-color: var(--background);
            color: var(--text-main);
            line-height: 1.6;
            transition: var(--transition);
        }

        header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: var(--transition);
        }

        @media (prefers-color-scheme: dark) {
            header {
                background: rgba(15, 23, 30, 0.8);
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            }
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo span {
            font-family: 'Outfit', sans-serif;
            font-size: 0.8rem;
            font-weight: 600;
            background: var(--primary);
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
        }

        .back-home {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            transition: var(--transition);
        }

        .back-home:hover {
            color: var(--primary);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .cart-trigger {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .cart-trigger:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .hero {
            padding: 4rem 2rem 2rem;
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .hero p {
            color: var(--text-muted);
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        .categories {
            display: flex;
            justify-content: center;
            gap: 0.8rem;
            margin-bottom: 3rem;
            overflow-x: auto;
            padding: 0.5rem;
            scrollbar-width: none;
        }

        .categories::-webkit-scrollbar {
            display: none;
        }

        .cat-btn {
            background: var(--surface);
            color: var(--text-main);
            border: 1px solid rgba(0, 0, 0, 0.05);
            padding: 0.6rem 1.5rem;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        @media (prefers-color-scheme: dark) {
            .cat-btn {
                border-color: rgba(255, 255, 255, 0.05);
            }
        }

        .cat-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            padding: 0 2rem 4rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .menu-item {
            background: var(--surface);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.02);
            display: flex;
            flex-direction: column;
        }

        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .menu-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background-color: #ddd;
        }

        .menu-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .menu-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .menu-desc {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }

        .menu-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .menu-price {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--primary);
        }

        .add-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .add-btn:hover {
            background: var(--primary-light);
        }

        /* Cart Sidebar Overlay */
        .cart-overlay {
            position: fixed;
            top: 0;
            right: -100%;
            width: 100%;
            max-width: 450px;
            height: 100%;
            background: var(--surface);
            box-shadow: var(--shadow-lg);
            z-index: 1000;
            transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            border-left: 1px solid rgba(0, 0, 0, 0.05);
        }

        @media (prefers-color-scheme: dark) {
            .cart-overlay {
                border-left-color: rgba(255, 255, 255, 0.05);
            }
        }

        .cart-overlay.open {
            right: 0;
        }

        .cart-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        @media (prefers-color-scheme: dark) {
            .cart-header {
                border-bottom-color: rgba(255, 255, 255, 0.05);
            }
        }

        .close-cart {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-main);
        }

        .cart-items {
            flex-grow: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.02);
        }

        .cart-item-info h4 {
            font-weight: 600;
            font-size: 1rem;
        }

        .cart-item-info span {
            color: var(--primary);
            font-weight: 600;
        }

        .qty-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .qty-btn {
            background: var(--background);
            color: var(--text-main);
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cart-summary {
            padding: 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            background: var(--background);
        }

        @media (prefers-color-scheme: dark) {
            .cart-summary {
                border-top-color: rgba(255, 255, 255, 0.05);
            }
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            font-weight: 600;
        }

        .total-row {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--primary);
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px dashed rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input {
            width: 100%;
            padding: 0.6rem;
            border-radius: var(--radius-md);
            border: 1px solid rgba(0, 0, 0, 0.1);
            background: var(--surface);
            color: var(--text-main);
            font-size: 1rem;
        }

        @media (prefers-color-scheme: dark) {
            .form-input {
                border-color: rgba(255, 255, 255, 0.1);
            }
        }

        .checkout-btn {
            width: 100%;
            background: var(--accent);
            color: white;
            border: none;
            padding: 0.8rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 1rem;
        }

        .checkout-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 999;
            display: none;
        }

        .backdrop.open {
            display: block;
        }

        /* Success Dialog */
        .modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.9);
            background: var(--surface);
            padding: 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            z-index: 1001;
            max-width: 400px;
            width: 90%;
            text-align: center;
            opacity: 0;
            pointer-events: none;
            transition: var(--transition);
        }

        .modal.open {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
            pointer-events: auto;
        }

        .modal-icon {
            font-size: 3.5rem;
            color: var(--accent);
            margin-bottom: 1rem;
        }

        .modal-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
            margin-top: 1.5rem;
            transition: var(--transition);
        }

        .modal-btn:hover {
            background: var(--primary-light);
        }

        .cart-badge {
            background: #ff3b30;
            color: white;
            font-size: 0.75rem;
            font-weight: 800;
            padding: 0.15rem 0.5rem;
            border-radius: 20px;
            display: inline-block;
            margin-left: 0.4rem;
            box-shadow: 0 0 10px rgba(255, 59, 48, 0.5);
            animation: bounceIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.1); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }

        .toast-notification {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translate(-50%, 40px);
            background: rgba(20, 20, 20, 0.9);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: white;
            padding: 0.75rem 1.6rem;
            border-radius: 40px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.08);
            z-index: 2000;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .toast-notification.show {
            transform: translate(-50%, 0);
            opacity: 1;
        }
    </style>
</head>
<body>

    <header>
        <a href="#" class="logo">{{ ucwords(str_replace('-', ' ', $alias ?? 'Demo Cafe')) }}<span>QR</span></a>
        <div class="header-actions">
            <a href="/" class="back-home">← Beranda Unimart</a>
            <button class="cart-trigger" onclick="toggleCart()">
                🛒 Keranjang<span id="cart-badge-container"></span>
            </button>
        </div>
    </header>

    <section class="hero">
        <h1>Cicipi Kelezatan Terbaik</h1>
        <p>Pesan langsung dari meja Anda dengan mudah, cepat, dan aman melalui QR Menu terintegrasi Unimart.group.</p>
        
        <div class="categories">
            <button class="cat-btn active" onclick="filterCategory('all', this)">Semua</button>
            <button class="cat-btn" onclick="filterCategory('makanan', this)">Makanan</button>
            <button class="cat-btn" onclick="filterCategory('minuman', this)">Minuman</button>
            <button class="cat-btn" onclick="filterCategory('dessert', this)">Dessert</button>
        </div>
    </section>

    <main class="menu-grid" id="menu-container">
        <!-- Javascript will populate the products here -->
    </main>

    <!-- Cart Sidebar -->
    <div class="backdrop" id="backdrop" onclick="toggleCart()"></div>
    <div class="cart-overlay" id="cart-sidebar">
        <div class="cart-header">
            <h3>Detail Pesanan</h3>
            <button class="close-cart" onclick="toggleCart()">&times;</button>
        </div>
        <div class="cart-items" id="cart-items-container">
            <!-- Cart items will populate here -->
        </div>
        <div class="cart-summary">
            <div class="form-group">
                <label class="form-label" for="customer-name">Nama / Alias Anda</label>
                <input class="form-input" type="text" id="customer-name" placeholder="Contoh: Budi, Susi, Rian" required>
            </div>
            
            <!-- Checkbox Pesan Online -->
            <div class="form-group" style="flex-direction: row; align-items: center; gap: 0.5rem; margin: 0.5rem 0;">
                <input type="checkbox" id="is-online-delivery" onchange="toggleOnlineDelivery(this)" style="width: 18px; height: 18px; accent-color: var(--primary);">
                <label for="is-online-delivery" style="font-weight: 700; color: var(--primary); cursor: pointer; font-size: 0.85rem;">🛵 Pesan Online (Kirim ke Rumah)</label>
            </div>

            <!-- Group Input Nomor Meja (default show) -->
            <div class="form-group" id="table-group">
                <label class="form-label" for="table-number">Nomor Meja</label>
                <select class="form-input" id="table-number" required>
                    <option value="">-- Pilih Meja --</option>
                    @foreach($tables ?? [] as $t)
                        <option value="{{ $t->name }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Group Input Alamat & Lokasi (default hide) -->
            <div id="delivery-group" style="display: none; flex-direction: column; gap: 0.8rem; border: 1px dashed rgba(230,115,0,0.2); padding: 0.8rem; border-radius: var(--radius-md); background: rgba(230,115,0,0.02); margin-bottom: 0.8rem;">
                <div class="form-group">
                    <label class="form-label" for="delivery-address">Alamat Lengkap Pengiriman</label>
                    <textarea class="form-input" id="delivery-address" placeholder="Masukkan alamat lengkap pengiriman Anda..." rows="2" style="resize: none; font-size: 0.82rem; padding: 0.45rem 0.7rem;"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Titik Lokasi (Koordinat GPS)</label>
                    <div style="display: flex; gap: 0.4rem;">
                        <input class="form-input" type="text" id="delivery-coords" placeholder="Belum dideteksi" readonly style="flex-grow: 1; font-size: 0.8rem; padding: 0.45rem 0.7rem;">
                        <button type="button" class="add-btn" onclick="detectLocation()" style="background: var(--accent); padding: 0 0.8rem; font-size: 0.75rem; white-space: nowrap;">📍 Deteksi</button>
                    </div>
                </div>
                <div id="delivery-info" style="display: none; font-size: 0.8rem; font-weight: 700; color: var(--accent); background: rgba(40,167,69,0.08); padding: 0.4rem; border-radius: 4px; text-align: center;">
                    Jarak: <span id="info-distance">0 km</span> | Ongkir: <span id="info-ongkir">Rp 0</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="payment-method">Metode Pembayaran</label>
                <select class="form-input" id="payment-method" required>
                    <option value="qris_official">QRIS Resmi Toko</option>
                    <option value="cash">Bayar di Kasir</option>
                </select>
            </div>
            <div class="summary-row">
                <span>Subtotal</span>
                <span id="cart-subtotal">Rp 0</span>
            </div>
            <div class="summary-row total-row">
                <span>Total</span>
                <span id="cart-total">Rp 0</span>
            </div>
            <button class="checkout-btn" onclick="checkout()">Pesan Sekarang</button>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal" id="success-modal">
        <div class="modal-icon">✓</div>
        <h2>Pesanan Terkirim!</h2>
        <div style="margin: 0.8rem 0;">
            <span id="tracking-status-badge" style="background: var(--warning); color: #fff; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.8rem; font-weight: 700; display: inline-block; box-shadow: 0 4px 10px rgba(230,115,0,0.2);">
                ⏳ Menunggu Antrean
            </span>
        </div>
        <p style="color: var(--text-muted); margin-top: 0.5rem;" id="modal-message">
            Silakan tunggu di meja Anda. Koki kami sedang menyiapkan hidangan istimewa untuk Anda.
        </p>
        <button class="modal-btn" onclick="closeSuccessModal()">Tutup</button>
    </div>

    <!-- Custom Interactive Notification Modal -->
    <div class="modal" id="custom-notification-modal">
        <div class="modal-icon" id="custom-modal-icon" style="font-size: 3.8rem; margin-bottom: 0.8rem;">⚠️</div>
        <h2 id="custom-modal-title" style="font-family: 'Outfit', sans-serif; font-size: 1.45rem; font-weight: 800;">Perhatian</h2>
        <p style="color: var(--text-muted); margin-top: 0.6rem; font-size: 0.9rem; line-height: 1.5;" id="custom-modal-message">
            Pemberitahuan penting untuk Anda.
        </p>
        <button class="modal-btn" onclick="closeCustomNotificationModal()" style="width: 100%; margin-top: 1.8rem; font-weight: 700; padding: 0.75rem 1.2rem;">Paham</button>
    </div>

    <!-- Menu & Cart Logic -->
    <script>
        let socket = null;
        let placedOrderId = null;

        function updateCustomerOrderStatus(status) {
            const statusBadge = document.getElementById('tracking-status-badge');
            if (statusBadge) {
                let statusText = '⏳ Menunggu Antrean';
                let statusColor = 'var(--warning)';
                if (status === 'cooking') {
                    statusText = '👨‍🍳 Sedang Dimasak';
                    statusColor = '#ff9500';
                } else if (status === 'ready') {
                    statusText = '✅ Siap Disajikan';
                    statusColor = 'var(--primary)';
                } else if (status === 'delivered') {
                    statusText = '🛵 Selesai Diantar';
                    statusColor = 'var(--success)';
                } else if (status === 'void') {
                    statusText = '❌ Dibatalkan';
                    statusColor = '#ff3b30';
                }
                statusBadge.textContent = statusText;
                statusBadge.style.backgroundColor = statusColor;
            }
        }

        // Setup Socket.io Gateway Client
        const tenantId = '{{ $tenant ? $tenant->id : 0 }}';
        if (tenantId !== '0') {
            try {
                const gatewayUrl = '{{ \App\Services\GatewayService::getClientSocketUrl() }}';
                socket = io(gatewayUrl);
                socket.on('connect', () => {
                    socket.emit('join', 'caffe-' + tenantId + '-kitchen');
                });
                socket.on('order-updated', (data) => {
                    if (placedOrderId && Number(data.id) === Number(placedOrderId)) {
                        updateCustomerOrderStatus(data.status);
                    }
                });
                socket.on('order-ready', (data) => {
                    if (placedOrderId && Number(data.id) === Number(placedOrderId)) {
                        updateCustomerOrderStatus('ready');
                    }
                });
            } catch (e) {
                console.error('Socket connection error:', e);
            }
        }

        function showNotification(title, message, type = 'warning') {
            const modal = document.getElementById('custom-notification-modal');
            const iconEl = document.getElementById('custom-modal-icon');
            const titleEl = document.getElementById('custom-modal-title');
            const messageEl = document.getElementById('custom-modal-message');
            const backdrop = document.getElementById('backdrop');

            if (type === 'success') {
                iconEl.textContent = '✓';
                iconEl.style.color = 'var(--accent)';
            } else if (type === 'error') {
                iconEl.textContent = '❌';
                iconEl.style.color = '#ff3b30';
            } else {
                iconEl.textContent = '⚠️';
                iconEl.style.color = 'var(--primary)';
            }

            titleEl.textContent = title;
            messageEl.innerHTML = message;

            modal.classList.add('open');
            if (backdrop) {
                backdrop.classList.add('open');
            }
        }

        function closeCustomNotificationModal() {
            document.getElementById('custom-notification-modal').classList.remove('open');
            const cartOpen = document.getElementById('cart-sidebar').classList.contains('open');
            const successOpen = document.getElementById('success-modal').classList.contains('open');
            if (!cartOpen && !successOpen) {
                document.getElementById('backdrop').classList.remove('open');
            }
        }

        // Ambil data produk dinamis dari database Laravel
        const products = @json($products);

        let cart = [];

        function renderMenu(items) {
            const container = document.getElementById('menu-container');
            container.innerHTML = '';
            
            items.forEach(p => {
                const card = document.createElement('article');
                card.className = 'menu-item';
                card.innerHTML = `
                    <img class="menu-img" src="${p.image_url || 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&q=80&w=400'}" alt="${p.name}">
                    <div class="menu-body">
                        <h3 class="menu-title">${p.name}</h3>
                        <p class="menu-desc" style="text-transform: capitalize;">Kategori: ${p.category}</p>
                        <div class="menu-footer">
                            <span class="menu-price">Rp ${p.price.toLocaleString('id-ID')}</span>
                            <button class="add-btn" onclick="addToCart(${p.id})">+ Tambah</button>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        function filterCategory(cat, btn) {
            document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            if (cat === 'all') {
                renderMenu(products);
            } else {
                renderMenu(products.filter(p => p.category === cat));
            }
        }

        function showToast(message) {
            let toast = document.getElementById('toast-notification');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'toast-notification';
                document.body.appendChild(toast);
            }
            toast.textContent = message;
            toast.className = 'toast-notification show';
            
            if (window.toastTimeout) {
                clearTimeout(window.toastTimeout);
            }
            window.toastTimeout = setTimeout(() => {
                toast.className = 'toast-notification';
            }, 2500);
        }

        let customerLat = null;
        let customerLng = null;
        const tenantLat = {{ $tenant->settings['latitude'] ?? -6.200000 }};
        const tenantLng = {{ $tenant->settings['longitude'] ?? 106.816666 }};
        let deliveryFee = 0;

        function toggleOnlineDelivery(checkbox) {
            const tableGroup = document.getElementById('table-group');
            const deliveryGroup = document.getElementById('delivery-group');
            if (checkbox.checked) {
                tableGroup.style.display = 'none';
                deliveryGroup.style.display = 'flex';
                document.getElementById('table-number').value = '';
                document.getElementById('table-number').required = false;
                document.getElementById('delivery-address').required = true;
                updateCartUI();
            } else {
                tableGroup.style.display = 'flex';
                deliveryGroup.style.display = 'none';
                document.getElementById('table-number').required = true;
                document.getElementById('delivery-address').required = false;
                deliveryFee = 0;
                updateCartUI();
            }
        }

        function detectLocation() {
            if (!navigator.geolocation) {
                showNotification('Tidak Didukung', 'Browser Anda tidak mendukung deteksi lokasi.', 'warning');
                return;
            }
            showToast('Sedang mendeteksi lokasi Anda...');
            navigator.geolocation.getCurrentPosition(position => {
                customerLat = position.coords.latitude;
                customerLng = position.coords.longitude;
                document.getElementById('delivery-coords').value = `${customerLat.toFixed(6)}, ${customerLng.toFixed(6)}`;
                
                // Hitung jarak & ongkir
                const dist = calculateDistance(tenantLat, tenantLng, customerLat, customerLng);
                deliveryFee = Math.round(dist * 5000); // Rp 5.000 / km
                if (deliveryFee < 5000) deliveryFee = 5000; // Minimal Rp 5.000
                
                document.getElementById('delivery-info').style.display = 'block';
                document.getElementById('info-distance').textContent = `${dist.toFixed(2)} km`;
                document.getElementById('info-ongkir').textContent = `Rp ${deliveryFee.toLocaleString('id-ID')}`;
                
                showToast('✓ Lokasi berhasil dideteksi!');
                updateCartUI();
            }, err => {
                showNotification('Deteksi Gagal', 'Gagal mengakses GPS. Silakan masukkan koordinat manual atau izinkan akses lokasi.', 'error');
            });
        }

        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Radius of the earth in km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = 
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
                Math.sin(dLon/2) * Math.sin(dLon/2); 
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
            const d = R * c; 
            return d;
        }

        function addToCart(productId) {
            const item = products.find(p => p.id === productId);
            if (item.stock <= 0) {
                showNotification('Stok Habis', `Maaf, stok menu <strong>${item.name}</strong> sedang habis.`, 'warning');
                return;
            }

            const exist = cart.find(c => c.id === productId);
            
            if (exist) {
                if (exist.qty >= item.stock) {
                    showNotification('Batas Stok', `Maaf, batas stok untuk menu <strong>${item.name}</strong> telah tercapai.`, 'warning');
                    return;
                }
                exist.qty++;
            } else {
                cart.push({ ...item, qty: 1 });
            }
            updateCartUI();
            showToast(`✓ ${item.name} berhasil ditambahkan ke keranjang!`);
        }

        function updateCartUI() {
            const count = cart.reduce((acc, c) => acc + c.qty, 0);
            const badgeContainer = document.getElementById('cart-badge-container');
            if (badgeContainer) {
                if (count > 0) {
                    badgeContainer.innerHTML = `<span class="cart-badge">${count}</span>`;
                } else {
                    badgeContainer.innerHTML = '';
                }
            }
            
            const container = document.getElementById('cart-items-container');
            container.innerHTML = '';
            
            let subtotal = 0;
            
            cart.forEach(c => {
                const priceTotal = c.price * c.qty;
                subtotal += priceTotal;
                
                const div = document.createElement('div');
                div.className = 'cart-item';
                div.innerHTML = `
                    <div class="cart-item-info">
                        <h4>${c.name}</h4>
                        <span>Rp ${c.price.toLocaleString('id-ID')}</span>
                    </div>
                    <div class="qty-controls">
                        <button class="qty-btn" onclick="changeQty(${c.id}, -1)">-</button>
                        <span>${c.qty}</span>
                        <button class="qty-btn" onclick="changeQty(${c.id}, 1)">+</button>
                    </div>
                `;
                container.appendChild(div);
            });
            
            document.getElementById('cart-subtotal').textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
            const total = subtotal + deliveryFee;
            document.getElementById('cart-total').textContent = `Rp ${total.toLocaleString('id-ID')}`;
        }

        function changeQty(id, delta) {
            const item = cart.find(c => c.id === id);
            if (!item) return;
            
            item.qty += delta;

            // Dapatkan limit stock asli dari katalog produk
            const catalogProduct = products.find(p => p.id === id);

            if (item.qty <= 0) {
                cart = cart.filter(c => c.id !== id);
            } else if (item.qty > catalogProduct.stock) {
                item.qty = catalogProduct.stock;
                showNotification('Batas Stok', `Batas stok menu <strong>${item.name}</strong> tercapai.`, 'warning');
            }
            updateCartUI();
        }

        function toggleCart() {
            document.getElementById('cart-sidebar').classList.toggle('open');
            document.getElementById('backdrop').classList.toggle('open');
        }

        let checkoutLoading = false;

        function checkout() {
            if (cart.length === 0) {
                showNotification('Keranjang Kosong', 'Keranjang belanja Anda masih kosong. Silakan pilih menu terlebih dahulu.', 'warning');
                return;
            }

            const customerName = document.getElementById('customer-name').value;
            if (!customerName || customerName.trim() === '') {
                showNotification('Nama Wajib Diisi', 'Silakan masukkan nama alias atau panggilan Anda terlebih dahulu.', 'warning');
                return;
            }
            
            const isOnline = document.getElementById('is-online-delivery').checked;
            let address = null;
            let table = null;

            if (isOnline) {
                address = document.getElementById('delivery-address').value;
                if (!address || address.trim() === '') {
                    showNotification('Alamat Wajib Diisi', 'Silakan masukkan alamat lengkap pengiriman.', 'warning');
                    return;
                }
                if (!customerLat || !customerLng) {
                    showNotification('Lokasi GPS Wajib', 'Silakan tekan tombol Deteksi lokasi Anda terlebih dahulu.', 'warning');
                    return;
                }
            } else {
                table = document.getElementById('table-number').value;
                if (!table) {
                    showNotification('Pilih Meja', 'Mohon pilih Nomor Meja Anda terlebih dahulu untuk memproses pesanan.', 'warning');
                    return;
                }
            }
            
            const paymentMethod = document.getElementById('payment-method').value;

            if (checkoutLoading) return;
            checkoutLoading = true;

            // Ambil tombol checkout untuk memberi feedback visual loading
            const checkoutBtn = document.querySelector('.checkout-btn');
            const originalText = checkoutBtn.textContent;
            checkoutBtn.textContent = 'Mengirim Pesanan...';
            checkoutBtn.disabled = true;

            const payload = {
                items: cart.map(item => ({ id: item.id, qty: item.qty })),
                table_number: table,
                payment_method: paymentMethod,
                customer_name: customerName,
                is_online: isOnline,
                delivery_address: address,
                delivery_latitude: customerLat,
                delivery_longitude: customerLng,
                delivery_fee: deliveryFee,
                _token: '{{ csrf_token() }}'
            };

            // Kirim request checkout pelanggan asli ke Laravel
            fetch(`/caffe/{{ $alias }}/checkout`, {
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
                    let methodLabel = "";
                    if (paymentMethod === 'qris_official') {
                        methodLabel = "melalui QRIS Resmi Toko";
                    } else {
                        methodLabel = "di Kasir";
                    }

                    const targetLabel = isOnline ? 'Alamat Antar' : `Meja ${table}`;
                    document.getElementById('modal-message').innerHTML = `
                        Pesanan <strong>${data.order.order_number}</strong> untuk <strong>${customerName} (${targetLabel})</strong> berhasil dikirim ke dapur. <br>
                        Silakan lakukan pembayaran <strong>${methodLabel}</strong>. <br>
                        Koki kami akan segera menyiapkan pesanan Anda.
                    `;

                    // Set order ID for real-time tracking badge
                    placedOrderId = data.order.id;
                    updateCustomerOrderStatus('pending');

                    // Clear cart
                    cart = [];
                    updateCartUI();
                    toggleCart();
                    
                    document.getElementById('success-modal').classList.add('open');
                    document.getElementById('backdrop').classList.add('open');
                } else {
                    showNotification('Gagal Memesan', data.message || 'Gagal memproses pesanan.', 'error');
                }
            })
            .catch(err => {
                showNotification('Masalah Koneksi', 'Terjadi kesalahan jaringan atau server. Silakan coba beberapa saat lagi.', 'error');
            })
            .finally(() => {
                checkoutLoading = false;
                checkoutBtn.textContent = originalText;
                checkoutBtn.disabled = false;
            });
        }

        function closeSuccessModal() {
            document.getElementById('success-modal').classList.remove('open');
            document.getElementById('backdrop').classList.remove('open');
            // Refresh halaman agar data stok terbaru terupdate secara visual untuk pelanggan
            window.location.reload();
        }

        // Initial Load & URL Parameter Check
        window.addEventListener('DOMContentLoaded', () => {
            renderMenu(products);

            // Deteksi parameter query ?table=xx
            const urlParams = new URLSearchParams(window.location.search);
            const tableParam = urlParams.get('table');
            if (tableParam) {
                const tableInput = document.getElementById('table-number');
                if (tableInput) {
                    let optionVal = tableParam;
                    if (!isNaN(tableParam) && !tableParam.startsWith('Meja')) {
                        optionVal = 'Meja ' + tableParam;
                    }
                    
                    let exists = false;
                    for (let i = 0; i < tableInput.options.length; i++) {
                        if (tableInput.options[i].value === optionVal || tableInput.options[i].value === tableParam) {
                            tableInput.value = tableInput.options[i].value;
                            exists = true;
                            break;
                        }
                    }
                    
                    if (!exists) {
                        const opt = document.createElement('option');
                        opt.value = optionVal;
                        opt.textContent = optionVal;
                        tableInput.appendChild(opt);
                        tableInput.value = optionVal;
                    }
                    
                    tableInput.disabled = true; // Kunci nomor meja agar tidak diubah tamu
                    tableInput.style.backgroundColor = 'rgba(255, 255, 255, 0.05)';
                    tableInput.style.color = '#777';
                    tableInput.style.cursor = 'not-allowed';
                }
            }
        });
    </script>
</body>
</html>
