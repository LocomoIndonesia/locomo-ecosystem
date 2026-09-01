<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iZone Hotspot - Captive Portal</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <!-- Custom Vanilla CSS -->
    <style>
        :root {
            --primary: hsl(200, 80%, 40%);
            --primary-light: hsl(200, 80%, 55%);
            --background: hsl(210, 20%, 96%);
            --surface: hsl(0, 0%, 100%);
            --text-main: hsl(210, 24%, 12%);
            --text-muted: hsl(210, 16%, 46%);
            --accent: hsl(28, 48%, 48%); /* Unimart Orange */
            --radius: 16px;
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --background: hsl(210, 24%, 8%);
                --surface: hsl(210, 24%, 12%);
                --text-main: hsl(210, 20%, 95%);
                --text-muted: hsl(210, 14%, 65%);
            }
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
            justify-content: center;
            align-items: center;
            padding: 1.5rem;
        }

        .container {
            background: var(--surface);
            padding: 2.5rem 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 440px;
            text-align: center;
            transition: var(--transition);
        }

        .logo-area {
            margin-bottom: 2rem;
        }

        .logo-area h1 {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.5px;
        }

        .logo-area h1 span {
            color: var(--accent);
        }

        .logo-area p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-top: 0.2rem;
        }

        /* Step wizard container */
        .step {
            display: none;
        }

        .step.active {
            display: block;
            animation: fadeIn 0.4s ease forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        /* Internet Package Grid */
        .package-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .package-card {
            background: var(--background);
            border: 2px solid transparent;
            padding: 1.2rem;
            border-radius: var(--radius);
            cursor: pointer;
            text-align: left;
            transition: var(--transition);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .package-card:hover {
            transform: translateY(-2px);
        }

        .package-card.selected {
            border-color: var(--primary);
            background: rgba(0, 120, 200, 0.05);
        }

        .package-info h3 {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .package-info p {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .package-price {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--primary);
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 1.2rem;
            text-align: left;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
        }

        .form-input {
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: var(--radius);
            border: 1px solid rgba(0, 0, 0, 0.1);
            background: var(--background);
            color: var(--text-main);
            font-size: 1rem;
            transition: var(--transition);
        }

        @media (prefers-color-scheme: dark) {
            .form-input {
                border-color: rgba(255, 255, 255, 0.1);
            }
        }

        .form-input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 120, 200, 0.15);
        }

        /* Buttons */
        .btn {
            background: var(--primary);
            color: white;
            border: none;
            width: 100%;
            padding: 0.9rem;
            border-radius: var(--radius);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn:hover {
            background: var(--primary-light);
        }

        /* Ad Banner */
        .ad-banner {
            background: linear-gradient(135deg, var(--accent), #ff8833);
            color: white;
            padding: 1.5rem;
            border-radius: var(--radius);
            margin: 1.5rem 0;
            text-align: left;
            position: relative;
            overflow: hidden;
        }

        .ad-banner h4 {
            font-size: 1.2rem;
            margin-bottom: 0.3rem;
            z-index: 2;
            position: relative;
        }

        .ad-banner p {
            font-size: 0.9rem;
            opacity: 0.9;
            z-index: 2;
            position: relative;
        }

        /* Success screen */
        .success-icon {
            font-size: 4rem;
            color: hsl(150, 60%, 45%);
            margin-bottom: 1rem;
        }

        .destination-list {
            margin-top: 1.5rem;
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.8rem;
        }

        .dest-card {
            background: var(--background);
            border: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem;
            border-radius: var(--radius);
            text-decoration: none;
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 600;
            transition: var(--transition);
        }

        .dest-card:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .dest-card span {
            font-size: 0.8rem;
            background: rgba(0,0,0,0.05);
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
        }

        .dest-card:hover span {
            background: rgba(255,255,255,0.2);
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="logo-area">
            <h1>{{ ucwords(str_replace('-', ' ', $alias ?? 'iZone')) }}<span>Hotspot</span></h1>
            <p>Koneksi Cepat & Cerdas oleh Unimart.group</p>
        </div>

        <!-- Step 1: Package Selection -->
        <div class="step active" id="step-1">
            <h2 class="title">Pilih Paket Internet</h2>
            <div class="package-grid">
                <div class="package-card selected" onclick="selectPackage(this, 'free')">
                    <div class="package-info">
                        <h3>Free Access</h3>
                        <p>Durasi 1 Jam • Kecepatan Stabil</p>
                    </div>
                    <span class="package-price">GRATIS</span>
                </div>
                <div class="package-card" onclick="selectPackage(this, 'premium')">
                    <div class="package-info">
                        <h3>Premium Voucher</h3>
                        <p>Durasi 5 Jam • Prioritas Kecepatan</p>
                    </div>
                    <span class="package-price">Voucher</span>
                </div>
            </div>
            <button class="btn" onclick="nextStep(2)">Lanjutkan</button>
        </div>

        <!-- Step 2: Voucher Input / Registration -->
        <div class="step" id="step-2">
            <h2 class="title">Verifikasi Akses</h2>
            <div class="form-group" id="voucher-input-group" style="display: none;">
                <label class="form-label" for="voucher-code">Kode Voucher</label>
                <input class="form-input" type="text" id="voucher-code" placeholder="Contoh: IZ-998A-33B">
            </div>
            <div id="free-registration-group">
                <div class="form-group">
                    <label class="form-label" for="user-name">Nama Lengkap</label>
                    <input class="form-input" type="text" id="user-name" placeholder="Nama Anda">
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone-number">Nomor WhatsApp</label>
                    <input class="form-input" type="tel" id="phone-number" placeholder="Contoh: 08123456789">
                </div>
            </div>
            <button class="btn" onclick="connectInternet()">Koneksikan Sekarang</button>
        </div>

        <!-- Step 3: Advertising & Connecting -->
        <div class="step" id="step-3">
            <h2 class="title">Menghubungkan ke Jaringan...</h2>
            <div class="ad-banner">
                <h4>Promo Spesial Hari Ini!</h4>
                <p>Dapatkan diskon 20% untuk menu Fettuccine Carbonara khusus pembelian via QR Menu.</p>
            </div>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Menyiapkan sesi internet Anda dalam <span id="timer">3</span> detik...</p>
        </div>

        <!-- Step 4: Success & Navigation Destination -->
        <div class="step" id="step-4">
            <div class="success-icon">✓</div>
            <h2 class="title">Internet Berhasil Terkoneksi!</h2>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Pilih halaman pertama yang ingin Anda kunjungi:</p>
            
            <div class="destination-list">
                <a href="/caffe/{{ $alias ?? 'demo-caffe' }}" class="dest-card">
                    <div>
                        <strong>Menu Cafe (Self-Order)</strong>
                        <p style="font-size: 0.8rem; font-weight: normal; opacity: 0.8;">Pesan makanan & minuman langsung</p>
                    </div>
                    <span>Unimart</span>
                </a>
                <a href="http://piticoin.id.localhost" class="dest-card">
                    <div>
                        <strong>Buru Harta Karun & Games</strong>
                        <p style="font-size: 0.8rem; font-weight: normal; opacity: 0.8;">Kumpulkan koin gratis dari cafe</p>
                    </div>
                    <span>PitiCoin</span>
                </a>
                <a href="http://piticoin.id.localhost/ppob" class="dest-card">
                    <div>
                        <strong>Bayar Tagihan & PPOB</strong>
                        <p style="font-size: 0.8rem; font-weight: normal; opacity: 0.8;">Pulsa, Paket Data, Token Listrik</p>
                    </div>
                    <span>PPOB</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Captive Portal Script -->
    <script>
        let selectedType = 'free';

        function selectPackage(card, type) {
            document.querySelectorAll('.package-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            selectedType = type;
        }

        function nextStep(stepNum) {
            // Show/hide groups based on selection
            if (stepNum === 2) {
                if (selectedType === 'premium') {
                    document.getElementById('voucher-input-group').style.display = 'block';
                    document.getElementById('free-registration-group').style.display = 'none';
                } else {
                    document.getElementById('voucher-input-group').style.display = 'none';
                    document.getElementById('free-registration-group').style.display = 'block';
                }
            }

            document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
            document.getElementById('step-' + stepNum).classList.add('active');
        }

        function connectInternet() {
            // Validation
            if (selectedType === 'premium') {
                const voucher = document.getElementById('voucher-code').value;
                if (!voucher) {
                    alert('Mohon masukkan kode voucher internet Anda.');
                    return;
                }
            } else {
                const name = document.getElementById('user-name').value;
                const phone = document.getElementById('phone-number').value;
                if (!name || !phone) {
                    alert('Mohon isi nama dan nomor WhatsApp Anda.');
                    return;
                }
            }

            // Go to step 3 (Ad timer)
            nextStep(3);

            let timeLeft = 3;
            const interval = setInterval(() => {
                timeLeft--;
                document.getElementById('timer').textContent = timeLeft;
                if (timeLeft <= 0) {
                    clearInterval(interval);
                    nextStep(4);
                }
            }, 1000);
        }
    </script>
</body>
</html>
