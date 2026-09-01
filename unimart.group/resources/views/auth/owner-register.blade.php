<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Pemilik Bisnis - Unimart.group</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom Vanilla CSS -->
    <style>
        :root {
            --primary: hsl(28, 90%, 50%);
            --primary-light: hsl(28, 95%, 62%);
            --background: hsl(210, 30%, 8%);
            --surface: hsl(210, 24%, 12%);
            --surface-accent: hsl(210, 24%, 18%);
            --text-main: hsl(210, 20%, 96%);
            --text-muted: hsl(210, 14%, 70%);
            --radius-lg: 24px;
            --radius-md: 16px;
            --border: rgba(255, 255, 255, 0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
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
            padding: 2rem 1.5rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Glow */
        .glow {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(230, 115, 0, 0.12) 0%, rgba(0,0,0,0) 70%);
            z-index: 1;
            pointer-events: none;
        }

        .container {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 750px;
            box-shadow: var(--shadow);
            position: relative;
            z-index: 2;
            transition: var(--transition);
        }

        @media (max-width: 600px) {
            body {
                padding: 1rem 0.75rem;
            }
            .container {
                padding: 2rem 1.25rem;
                border-radius: var(--radius-md);
            }
        }

        .logo-area {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo-area h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .logo-area h1 span {
            color: var(--text-main);
        }

        .logo-area p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        /* Stepper progress */
        .stepper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            position: relative;
            padding: 0 0.5rem;
        }

        .stepper::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--surface-accent);
            z-index: 1;
            transform: translateY(-50%);
        }

        .stepper-progress {
            position: absolute;
            top: 50%;
            left: 0;
            height: 2px;
            background: var(--primary);
            z-index: 1;
            transform: translateY(-50%);
            transition: var(--transition);
        }

        .step-item {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.4rem;
        }

        .step-circle {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--surface-accent);
            border: 2px solid var(--border);
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: 700;
            font-size: 0.88rem;
            color: var(--text-muted);
            transition: var(--transition);
        }

        .step-item.active .step-circle {
            background: var(--primary);
            border-color: var(--primary);
            color: var(--background);
            box-shadow: 0 0 15px rgba(230, 115, 0, 0.4);
        }

        .step-item.completed .step-circle {
            background: var(--surface);
            border-color: var(--primary);
            color: var(--primary);
        }

        .step-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: var(--transition);
        }

        @media (max-width: 600px) {
            .step-label {
                display: none;
            }
        }

        .step-item.active .step-label {
            color: var(--primary);
        }

        /* Error/Validation Alert */
        .alert-error {
            background: rgba(255, 59, 48, 0.15);
            border: 1px solid rgba(255, 59, 48, 0.3);
            color: #ff453a;
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
        }

        .form-input {
            width: 100%;
            padding: 0.9rem 1.1rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            background: var(--surface-accent);
            color: var(--text-main);
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .form-input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(230, 115, 0, 0.2);
        }

        textarea.form-input {
            resize: vertical;
        }

        .domain-input-wrapper {
            display: flex;
            align-items: center;
            background: var(--surface-accent);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            overflow: hidden;
            transition: var(--transition);
        }

        .domain-input-wrapper:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(230, 115, 0, 0.2);
        }

        .domain-input-wrapper input {
            border: none;
            background: transparent;
            padding: 0.9rem 1.1rem;
            width: 100%;
            color: var(--text-main);
            font-size: 0.95rem;
        }

        .domain-input-wrapper input:focus {
            outline: none;
        }

        .domain-suffix {
            background: rgba(255, 255, 255, 0.05);
            padding: 0.9rem 1.1rem;
            color: var(--text-muted);
            font-size: 0.9rem;
            border-left: 1px solid var(--border);
            white-space: nowrap;
        }

        /* Package Cards */
        .packages-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.2rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .packages-container {
                grid-template-columns: 1fr;
            }
        }

        .package-card {
            background: var(--surface-accent);
            border: 2px solid transparent;
            border-radius: var(--radius-md);
            padding: 1.5rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .package-card:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 255, 255, 0.15);
        }

        .package-card.selected {
            border-color: var(--primary);
            background: rgba(230, 115, 0, 0.05);
            box-shadow: 0 8px 24px rgba(230, 115, 0, 0.1);
        }

        .package-card.selected::after {
            content: '✓';
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 20px;
            height: 20px;
            background: var(--primary);
            color: var(--background);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: bold;
        }

        .package-card h3 {
            font-size: 1.25rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .package-card p {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }

        .package-features {
            list-style: none;
            font-size: 0.8rem;
            color: var(--text-muted);
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            margin-bottom: 1.5rem;
        }

        .package-features li {
            display: flex;
            align-items: flex-start;
            gap: 0.4rem;
        }

        .package-price {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--primary);
            border-top: 1px solid var(--border);
            padding-top: 1rem;
        }

        .package-price span {
            font-size: 0.8rem;
            font-weight: normal;
            color: var(--text-muted);
        }

        /* TOS Viewer style */
        .tos-container {
            border: 1px solid var(--border);
            background: var(--surface-accent);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            max-height: 250px;
            overflow-y: auto;
            margin-bottom: 1.5rem;
            font-size: 0.88rem;
            color: var(--text-muted);
            line-height: 1.6;
            scroll-behavior: smooth;
        }

        .tos-container::-webkit-scrollbar {
            width: 8px;
        }

        .tos-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .tos-container::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }

        .tos-container::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.15);
        }

        .tos-heading {
            color: var(--text-main);
            font-size: 1rem;
            font-weight: 700;
            margin-top: 1.2rem;
            margin-bottom: 0.4rem;
        }
        
        .tos-heading:first-of-type {
            margin-top: 0;
        }

        /* Transition content wrapper */
        .step-content {
            display: none;
        }

        .step-content.active {
            display: block;
            animation: slideIn 0.3s ease-out forwards;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .step-error-msg {
            color: #ff453a;
            font-size: 0.85rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(255, 59, 48, 0.1);
            padding: 0.6rem 0.8rem;
            border-radius: 8px;
            border-left: 3px solid #ff453a;
        }

        .step-error-info {
            color: var(--primary);
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(230, 115, 0, 0.1);
            padding: 0.6rem 0.8rem;
            border-radius: 8px;
            border-left: 3px solid var(--primary);
        }

        .step-header {
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.6rem;
        }

        .step-header h2 {
            font-size: 1.3rem;
            font-weight: 700;
        }

        .login-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-muted);
            text-decoration: none;
            transition: var(--transition);
        }

        .login-link:hover {
            color: var(--text-main);
        }

        .scroll-indicator {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: -1rem;
            margin-bottom: 1.5rem;
            padding: 0 0.5rem;
        }

        .scroll-indicator .badge {
            background: var(--border);
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-weight: bold;
        }

        .scroll-indicator .badge.unlocked {
            background: rgba(230, 115, 0, 0.15);
            color: var(--primary);
        }
    </style>
</head>
<body>

    <div class="glow" style="top: -10%; left: -10%;"></div>
    <div class="glow" style="bottom: -10%; right: -10%;"></div>

    <div class="container" x-data="registrationForm">
        <div class="logo-area">
            <h1>Unimart<span>group</span></h1>
            <p>Daftarkan bisnis Anda dan kelola usaha secara pintar</p>
        </div>

        <!-- Progress Indicator -->
        <div class="stepper">
            <div class="stepper-progress" :style="'width: ' + ((step - 1) / 4 * 100) + '%'"></div>
            
            <div class="step-item" :class="(isGoogleRegister || step > 1) ? 'completed' : (step === 1 ? 'active' : '')">
                <div class="step-circle">1</div>
                <div class="step-label">Akses</div>
            </div>
            <div class="step-item" :class="(isGoogleRegister || step > 2) ? 'completed' : (step === 2 ? 'active' : '')">
                <div class="step-circle">2</div>
                <div class="step-label">Pemilik</div>
            </div>
            <div class="step-item" :class="step === 3 ? 'active' : (step > 3 ? 'completed' : '')">
                <div class="step-circle">3</div>
                <div class="step-label">Bisnis</div>
            </div>
            <div class="step-item" :class="step === 4 ? 'active' : (step > 4 ? 'completed' : '')">
                <div class="step-circle">4</div>
                <div class="step-label">Paket</div>
            </div>
            <div class="step-item" :class="step === 5 ? 'active' : (step > 5 ? 'completed' : '')">
                <div class="step-circle">5</div>
                <div class="step-label">Kontrak</div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert-error">
                <strong>Pendaftaran Gagal:</strong>
                <ul style="list-style-type: disc; margin-left: 1.2rem; margin-top: 0.3rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('info'))
            <div class="step-error-info">
                <span>{{ session('info') }}</span>
            </div>
        @endif

        <form action="{{ route('owner.register') }}" method="POST">
            @csrf

            <!-- Hidden input untuk paket terpilih -->
            <input type="hidden" name="package" :value="selectedPackage">

            <!-- STEP 1: AKSES MASUK -->
            <div class="step-content" :class="step === 1 ? 'active' : ''">
                <div class="step-header">
                    <h2>Langkah 1: Akun & Akses Masuk</h2>
                </div>

                <!-- Google OAuth Registration Button -->
                <div style="margin-bottom: 2rem; text-align: center;">
                    <a href="{{ route('auth.google.redirect') }}" style="display: flex; align-items: center; justify-content: center; gap: 0.8rem; background: #fff; color: #1f2937; border: 1px solid #d1d5db; padding: 0.8rem 1.5rem; border-radius: var(--radius-md); font-weight: 600; text-decoration: none; transition: var(--transition); box-shadow: 0 2px 4px rgba(0,0,0,0.05);" onmouseover="this.style.background='#f9fafb'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='#fff'; this.style.transform='none'">
                        <svg style="width: 18px; height: 18px;" viewBox="0 0 24 24">
                            <path fill="#EA4335" d="M12.24 10.285V14.4h6.887c-.648 2.41-2.519 4.114-5.136 4.114-3.51 0-6.357-2.827-6.357-6.314s2.848-6.314 6.357-6.314c1.614 0 3.15.588 4.35 1.684l3.125-3.09C19.347 1.83 15.93.971 12.24.971 6.073.971.971 6.027.971 12s5.102 11.029 11.269 11.029c6.438 0 11.269-4.482 11.269-11.029 0-.745-.074-1.285-.224-1.714H12.24z"/>
                        </svg>
                        Daftar cepat dengan Google (Gmail)
                    </a>
                    
                    <div style="display: flex; align-items: center; margin: 1.5rem 0; color: var(--text-muted); font-size: 0.85rem;">
                        <div style="flex: 1; height: 1px; background: var(--border);"></div>
                        <span style="padding: 0 1rem; font-weight: 600;">ATAU DAFTAR MANUAL</span>
                        <div style="flex: 1; height: 1px; background: var(--border);"></div>
                    </div>
                </div>
                
                <template x-if="errors.step1">
                    <div class="step-error-msg">
                        <span x-text="errors.step1"></span>
                    </div>
                </template>

                <div class="form-group">
                    <label class="form-label" for="email">Alamat Email</label>
                    <input class="form-input" type="email" id="email" name="email" x-model="email" placeholder="Contoh: owner@caffekita.com" :required="!isGoogleRegister">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password Baru</label>
                    <input class="form-input" type="password" id="password" name="password" x-model="password" placeholder="Minimal 6 karakter" :required="!isGoogleRegister">
                </div>
            </div>

            <!-- STEP 2: INFORMASI PEMILIK -->
            <div class="step-content" :class="step === 2 ? 'active' : ''">
                <div class="step-header">
                    <h2>Langkah 2: Informasi Pemilik</h2>
                </div>

                <template x-if="errors.step2">
                    <div class="step-error-msg">
                        <span x-text="errors.step2"></span>
                    </div>
                </template>

                <div class="form-group">
                    <label class="form-label" for="name">Nama Lengkap</label>
                    <input class="form-input" type="text" id="name" name="name" x-model="name" placeholder="Contoh: Ahmad Fauzi" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="phone">Nomor WhatsApp / Telepon</label>
                    <input class="form-input" type="tel" id="phone" name="phone" x-model="phone" placeholder="Contoh: 081234567890" required>
                </div>
            </div>

            <!-- STEP 3: DETAIL BISNIS -->
            <div class="step-content" :class="step === 3 ? 'active' : ''">
                <div class="step-header">
                    <h2>Langkah 3: Informasi Detail Bisnis</h2>
                </div>

                <!-- Google Account Verified Badge -->
                <template x-if="isGoogleRegister">
                    <div style="background: rgba(52, 211, 153, 0.1); border: 1px solid rgba(52, 211, 153, 0.3); color: #34d399; padding: 0.8rem 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; font-size: 0.88rem; display: flex; align-items: center; gap: 0.5rem;">
                        <svg style="width: 18px; height: 18px; fill: currentColor;" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 11.37a1 1 0 011.23-.746l4.237 1.21 7.234-9.043a1 1 0 111.566 1.252l-8 10a1 1 0 01-1.397.148l-5.12-3.84a1 1 0 01-.245-1.424z" clip-rule="evenodd"/>
                        </svg>
                        <span>Terverifikasi dengan Google: <strong><span x-text="email"></span></strong>. Langkah 1 & 2 telah dilewati secara aman.</span>
                    </div>
                </template>

                <template x-if="errors.step3">
                    <div class="step-error-msg">
                        <span x-text="errors.step3"></span>
                    </div>
                </template>

                <!-- Hidden inputs for read-only values if registered via Google -->
                <template x-if="isGoogleRegister">
                    <div>
                        <input type="hidden" name="email" :value="email">
                        <input type="hidden" name="name" :value="name">
                    </div>
                </template>

                <div class="form-group">
                    <label class="form-label" for="business_name">Nama Toko / Kafe</label>
                    <input class="form-input" type="text" id="business_name" name="business_name" x-model="business_name" placeholder="Contoh: Kopi Kita" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="domain">Domain Alias</label>
                    <div class="domain-input-wrapper">
                        <input type="text" id="domain" name="domain" x-model="domain" placeholder="kopi-kita" required>
                        <span class="domain-suffix">.unimart.group</span>
                    </div>
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.4rem;">Digunakan untuk akses QR Menu Dine-In dan iZone Hotspot Anda.</p>
                </div>

                <div class="form-group">
                    <label class="form-label" for="business_type">Jenis Bisnis</label>
                    <select class="form-input" id="business_type" name="business_type" x-model="business_type" required>
                        <option value="Kedai Kopi / Coffee Shop">Kedai Kopi / Coffee Shop</option>
                        <option value="Kafe F&B / Restaurant">Kafe F&B / Restaurant</option>
                        <option value="Toko Roti / Bakery">Toko Roti / Bakery</option>
                        <option value="Warung Makan / Kedai">Warung Makan / Kedai</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="business_address">Alamat Lengkap Bisnis</label>
                    <textarea class="form-input" id="business_address" name="business_address" x-model="business_address" rows="3" placeholder="Contoh: Jl. Sudirman No. 45, Jakarta Selatan" required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="business_description">Deskripsi Singkat Bisnis (Opsional)</label>
                    <textarea class="form-input" id="business_description" name="business_description" x-model="business_description" rows="2" placeholder="Ceritakan singkat tentang bisnis Anda..."></textarea>
                </div>
            </div>

            <!-- STEP 4: PAKET LAYANAN -->
            <div class="step-content" :class="step === 4 ? 'active' : ''">
                <div class="step-header">
                    <h2>Langkah 4: Pilih Paket Layanan</h2>
                </div>

                <div class="packages-container">
                    <!-- Paket Basic -->
                    <div class="package-card" :class="selectedPackage === 'Basic' ? 'selected' : ''" @click="selectedPackage = 'Basic'">
                        <div>
                            <h3>Basic</h3>
                            <p style="color: var(--primary); font-weight: 600; font-size: 0.78rem; margin-top: -0.2rem;">Langkah Awal Bisnis Rapi & Modern</p>
                            <ul class="package-features">
                                <li>✓ Profil bisnis digital (scan QR)</li>
                                <li>✓ Katalog / etalase menu online</li>
                                <li>✓ Integrasi pembayaran QRIS resmi</li>
                            </ul>
                        </div>
                        <div class="package-price">Rp 50K<span>/bln</span></div>
                    </div>
                    <!-- Paket Bisnis -->
                    <div class="package-card" :class="selectedPackage === 'Bisnis' ? 'selected' : ''" @click="selectedPackage = 'Bisnis'">
                        <div>
                            <h3>Bisnis</h3>
                            <p style="color: var(--primary); font-weight: 600; font-size: 0.78rem; margin-top: -0.2rem;">Pelayanan Cepat, Bebas Antre</p>
                            <ul class="package-features">
                                <li>✓ Semua fitur Basic, ditambah:</li>
                                <li>✓ Pemesanan mandiri dari meja via QR</li>
                                <li>✓ Notifikasi WhatsApp ke dapur/kasir</li>
                            </ul>
                        </div>
                        <div class="package-price">Rp 100K<span>/bln</span></div>
                    </div>
                    <!-- Paket Premium VIP -->
                    <div class="package-card" :class="selectedPackage === 'Premium VIP' ? 'selected' : ''" @click="selectedPackage = 'Premium VIP'">
                        <div>
                            <h3>Premium VIP</h3>
                            <p style="color: var(--primary); font-weight: 600; font-size: 0.78rem; margin-top: -0.2rem;">Bisnis Auto-Pilot, Buka 24 Jam</p>
                            <ul class="package-features">
                                <li>✓ Semua fitur Bisnis, ditambah:</li>
                                <li>✓ Aplikasi kasir digital (POS) penuh</li>
                                <li>✓ Asisten AI (Gemini) balas chat</li>
                            </ul>
                        </div>
                        <div class="package-price">Rp 200K<span>/bln</span></div>
                    </div>
                </div>
            </div>

            <!-- STEP 5: SYARAT & KETENTUAN (TOS) -->
            <div class="step-content" :class="step === 5 ? 'active' : ''">
                <div class="step-header">
                    <h2>Langkah 5: Kontrak Kerja Sama & Syarat Ketentuan</h2>
                </div>

                <div class="tos-container" @scroll="checkScroll">
                    <h3 class="tos-heading">Syarat & Ketentuan Layanan (SLA) Unimart.group</h3>
                    <p>Selamat datang di <strong>Unimart.group</strong>. Layanan kami disediakan dengan standar kepatuhan tata kelola teknologi informasi internasional yang mengacu pada standar <strong>ISO/IEC 27001 (Sistem Manajemen Keamanan Informasi)</strong> dan <strong>ISO/IEC 20000 (Sistem Manajemen Layanan TI)</strong>.</p>
                    
                    <h3 class="tos-heading">1. Kepatuhan ISO 27001 (Keamanan Informasi & Multi-Tenant)</h3>
                    <p>Kami menerapkan isolasi database multi-tenant yang ketat untuk menjamin data transaksi, menu, staf, dan data CRM WiFi iZone Anda tidak dapat diakses oleh pihak luar atau tenant lain. Seluruh data sensitif seperti password disimpan menggunakan enkripsi hashing tingkat tinggi (bcrypt/argon2).</p>
                    
                    <h3 class="tos-heading">2. Jaminan Ketersediaan Layanan (SLA - ISO 20000)</h3>
                    <p>Unimart.group menjamin Service Level Agreement (SLA) waktu aktif (uptime) sebesar <strong>99.9%</strong> setiap bulannya. Pemeliharaan sistem terjadwal akan diumumkan minimal 24 jam sebelum dilakukan dan diupayakan pada jam dengan aktivitas bisnis terendah (pukul 02:00 - 04:00 WIB).</p>
                    
                    <h3 class="tos-heading">3. Kebijakan Privasi Data & CRM (MikroTik iZone)</h3>
                    <p>Data pelanggan yang diperoleh melalui login captive portal iZone (Nama, Nomor WhatsApp, MAC Address) sepenuhnya adalah properti dari Tenant/Pemilik Bisnis. Kami tidak akan menjual, menyewakan, atau menyebarkan data tersebut kepada pihak ketiga mana pun tanpa persetujuan tertulis.</p>
                    
                    <h3 class="tos-heading">4. Hak Kepemilikan & Batasan Penggunaan</h3>
                    <p>Pengguna dilarang keras mencoba melakukan reverse engineering, eksploitasi celah keamanan, atau bypass otorisasi rute pada platform Unimart. Tindakan pelanggaran keamanan akan ditindak secara hukum berdasarkan Undang-Undang ITE yang berlaku.</p>

                    <h3 class="tos-heading">5. Kontrak Kerja Sama & Bagi Hasil</h3>
                    <p>Kerja sama penggunaan sistem ini berlaku sejak tanggal pendaftaran disetujui. Tagihan biaya bulanan sesuai paket yang dipilih akan diterbitkan secara otomatis setiap bulan. Pembayaran wajib diselesaikan selambat-lambatnya 5 hari setelah tanggal jatuh tempo untuk menghindari pembekuan akun sementara.</p>
                    
                    <p style="font-size: 0.8rem; font-style: italic; border-top: 1px solid var(--border); padding-top: 1rem; margin-top: 1rem;">Terakhir diperbarui: 2 Agustus 2026. Dengan mencentang persetujuan, Anda menyatakan tunduk pada dokumen tata kelola ini.</p>
                </div>

                <div class="scroll-indicator">
                    <span x-show="!hasScrolledToBottom">Mohon baca draf kontrak di atas dengan scroll ke bagian paling bawah.</span>
                    <span x-show="hasScrolledToBottom" style="color: var(--primary);">✓ Anda telah membaca draf kontrak.</span>
                    <span class="badge" :class="hasScrolledToBottom ? 'unlocked' : ''" x-text="hasScrolledToBottom ? 'TERBUKA' : 'TERKUNCI'"></span>
                </div>

                <!-- Terms Checkbox (Only interactable after scrolling to bottom) -->
                <div style="margin-top: 1rem; display: flex; align-items: flex-start; gap: 0.8rem; text-align: left;">
                    <input type="checkbox" id="agree_terms" name="agree_terms" x-model="agreeTerms" :disabled="!hasScrolledToBottom" style="width: 20px; height: 20px; accent-color: var(--primary); cursor: pointer; margin-top: 2px;">
                    <label for="agree_terms" :style="!hasScrolledToBottom ? 'opacity: 0.5; cursor: not-allowed;' : 'cursor: pointer;'" style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.4;">
                        Saya menyetujui draf kontrak kerja sama, Syarat & Ketentuan Layanan Unimart.group, serta kebijakan perlindungan informasi yang diatur di atas.
                    </label>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div style="display: flex; justify-content: space-between; margin-top: 2.5rem; gap: 1rem;">
                <button type="button" @click="prevStep()" class="form-input" x-show="step > (isGoogleRegister ? 3 : 1)" style="width: auto; background: var(--surface-accent); color: var(--text-main); font-weight: 600; cursor: pointer; padding: 0.8rem 1.8rem;">Sebelumnya</button>
                <div x-show="step === (isGoogleRegister ? 3 : 1)"></div>
                
                <button type="button" @click="nextStep()" class="form-input" x-show="step < 5" style="width: auto; background: var(--primary); color: var(--background); font-weight: 700; cursor: pointer; padding: 0.8rem 2.2rem; box-shadow: 0 4px 14px rgba(230, 115, 0, 0.3);">Lanjutkan</button>
                <button type="submit" class="form-input" x-show="step === 5" :disabled="!agreeTerms || !hasScrolledToBottom" style="width: auto; background: var(--primary); color: var(--background); font-weight: 700; cursor: pointer; padding: 0.8rem 2.2rem; box-shadow: 0 4px 14px rgba(230, 115, 0, 0.3);" :style="(!agreeTerms || !hasScrolledToBottom) ? 'opacity: 0.5; cursor: not-allowed; box-shadow: none;' : ''">Daftarkan Sekarang</button>
            </div>
        </form>

        <a href="{{ route('owner.login') }}" class="login-link">Sudah memiliki akun? Masuk di sini</a>
    </div>

    <!-- Script Component Definition to avoid HTML parsing issues inside x-data -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('registrationForm', () => ({
                isGoogleRegister: @json(session()->has('google_register')),
                step: @json(session()->has('google_register') ? 3 : 1),
                selectedPackage: 'Bisnis',
                hasScrolledToBottom: false,
                agreeTerms: false,
                
                // Data binding
                email: @json(old('email', session('google_register.email', ''))),
                password: '',
                name: @json(old('name', session('google_register.name', ''))),
                phone: @json(old('phone', '')),
                business_name: @json(old('business_name', '')),
                domain: @json(old('domain', '')),
                business_type: @json(old('business_type', 'Kedai Kopi / Coffee Shop')),
                business_address: @json(old('business_address', '')),
                business_description: @json(old('business_description', '')),
                
                // Validation Errors
                errors: {
                    step1: '',
                    step2: '',
                    step3: '',
                },

                validateStep1() {
                    if (this.isGoogleRegister) return true;
                    this.errors.step1 = '';
                    if (!this.email) {
                        this.errors.step1 = 'Email wajib diisi.';
                        return false;
                    }
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(this.email)) {
                        this.errors.step1 = 'Format email tidak valid.';
                        return false;
                    }
                    if (!this.password) {
                        this.errors.step1 = 'Password wajib diisi.';
                        return false;
                    }
                    if (this.password.length < 6) {
                        this.errors.step1 = 'Password minimal 6 karakter.';
                        return false;
                    }
                    return true;
                },

                validateStep2() {
                    if (this.isGoogleRegister) return true;
                    this.errors.step2 = '';
                    if (!this.name) {
                        this.errors.step2 = 'Nama lengkap wajib diisi.';
                        return false;
                    }
                    if (!this.phone) {
                        this.errors.step2 = 'Nomor telepon/WhatsApp wajib diisi.';
                        return false;
                    }
                    return true;
                },

                validateStep3() {
                    this.errors.step3 = '';
                    if (!this.business_name) {
                        this.errors.step3 = 'Nama toko/kafe wajib diisi.';
                        return false;
                    }
                    if (!this.domain) {
                        this.errors.step3 = 'Domain alias wajib diisi.';
                        return false;
                    }
                    const domainRegex = /^[a-zA-Z0-9-_]+$/;
                    if (!domainRegex.test(this.domain)) {
                        this.errors.step3 = 'Domain alias hanya boleh berisi huruf, angka, dash (-), dan underscore (_).';
                        return false;
                    }
                    if (!this.business_type) {
                        this.errors.step3 = 'Pilih salah satu jenis bisnis Anda.';
                        return false;
                    }
                    if (!this.business_address) {
                        this.errors.step3 = 'Alamat bisnis wajib diisi.';
                        return false;
                    }
                    return true;
                },

                nextStep() {
                    if (this.step === 1 && !this.validateStep1()) return;
                    if (this.step === 2 && !this.validateStep2()) return;
                    if (this.step === 3 && !this.validateStep3()) return;
                    if (this.step < 5) this.step++;
                },

                prevStep() {
                    if (this.isGoogleRegister) {
                        if (this.step > 3) this.step--;
                    } else {
                        if (this.step > 1) this.step--;
                    }
                },

                checkScroll(e) {
                    const element = e.target;
                    // check scroll position
                    if (element.scrollHeight - element.scrollTop <= element.clientHeight + 15) {
                        this.hasScrolledToBottom = true;
                    }
                }
            }));
        });
    </script>

</body>
</html>
