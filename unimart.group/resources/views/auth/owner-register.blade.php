<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Pemilik Bisnis — Unimart.group</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,500&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --primary: hsl(28, 95%, 50%);
            --primary-hover: hsl(28, 100%, 46%);
            --primary-soft: rgba(249, 115, 22, 0.12);
            --primary-ring: rgba(249, 115, 22, 0.25);
            --bg: hsl(220, 26%, 8%);
            --surface: hsl(220, 24%, 12%);
            --surface-accent: hsl(220, 22%, 16%);
            --surface-hover: hsl(220, 22%, 20%);
            --text-main: hsl(220, 20%, 96%);
            --text-muted: hsl(220, 14%, 68%);
            --text-dim: hsl(220, 12%, 48%);
            --border: rgba(255, 255, 255, 0.08);
            --border-active: rgba(249, 115, 22, 0.6);
            --radius-xl: 20px;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
            --shadow-lg: 0 24px 48px -12px rgba(0, 0, 0, 0.6);
            --shadow-sm: 0 4px 12px rgba(0, 0, 0, 0.2);
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0;
            position: relative;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 10% 10%, rgba(249, 115, 22, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 90% 90%, rgba(249, 115, 22, 0.06) 0%, transparent 40%);
        }

        /* Ambient Glow Effect */
        .ambient-glow {
            position: fixed;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.14) 0%, transparent 70%);
            pointer-events: none;
            z-index: 1;
        }

        /* Container: Edge-to-Edge on Mobile, Elegant Card on Desktop */
        .container {
            width: 100%;
            min-height: 100vh;
            background: var(--surface);
            padding: 1.5rem 1.25rem 2.5rem;
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        @media (min-width: 640px) {
            body {
                padding: 2.5rem 1.25rem;
            }
            .container {
                min-height: auto;
                max-width: 560px;
                border-radius: var(--radius-xl);
                border: 1px solid var(--border);
                padding: 2.5rem 2.25rem;
                box-shadow: var(--shadow-lg);
            }
        }

        /* Header & Branding */
        .brand-header {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            background: var(--primary-soft);
            border: 1px solid var(--border-active);
            color: var(--primary);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 0.6rem;
        }

        .brand-logo {
            font-family: 'Playfair Display', serif;
            font-size: 2.1rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: var(--primary);
            line-height: 1.1;
            margin-bottom: 0.4rem;
        }

        .brand-logo span {
            color: var(--text-main);
        }

        .brand-subtitle {
            color: var(--text-muted);
            font-size: 0.88rem;
            line-height: 1.4;
        }

        /* Modern Stepper */
        .stepper-wrap {
            margin-bottom: 2rem;
        }

        .step-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.9rem;
            font-size: 0.8rem;
        }

        .step-counter {
            font-weight: 700;
            color: var(--primary);
            background: var(--primary-soft);
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
        }

        .step-title-hint {
            color: var(--text-muted);
            font-weight: 600;
        }

        .stepper-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            padding: 0 4px;
        }

        /* Continuous Progress Line */
        .stepper-track-bg {
            position: absolute;
            top: 50%;
            left: 18px;
            right: 18px;
            height: 2px;
            background: var(--border);
            z-index: 1;
            transform: translateY(-50%);
        }

        .stepper-track-fill {
            position: absolute;
            top: 50%;
            left: 18px;
            height: 2px;
            background: var(--primary);
            z-index: 2;
            transform: translateY(-50%);
            transition: width 0.35s ease-in-out;
        }

        .step-node {
            position: relative;
            z-index: 3;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--surface-accent);
            border: 2px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-dim);
            transition: var(--transition);
        }

        .step-node.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #ffffff;
            box-shadow: 0 0 0 4px var(--primary-ring), 0 4px 12px rgba(249, 115, 22, 0.4);
            transform: scale(1.08);
        }

        .step-node.completed {
            background: var(--surface-accent);
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Step Card Content */
        .step-panel {
            display: none;
        }

        .step-panel.active {
            display: block;
            animation: fadeInStep 0.25s ease-out forwards;
        }

        @keyframes fadeInStep {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .panel-heading {
            margin-bottom: 1.5rem;
        }

        .panel-heading h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.25rem;
        }

        .panel-heading p {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 0.45rem;
        }

        .form-input {
            width: 100%;
            height: 50px;
            padding: 0 1rem;
            background: var(--surface-accent);
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            color: var(--text-main);
            font-size: 1rem; /* 16px to prevent mobile browser zoom */
            transition: var(--transition);
            outline: none;
        }

        .form-input:focus {
            border-color: var(--primary);
            background: hsl(220, 22%, 18%);
            box-shadow: 0 0 0 3px var(--primary-ring);
        }

        textarea.form-input {
            height: auto;
            min-height: 80px;
            padding: 0.85rem 1rem;
            resize: vertical;
        }

        select.form-input {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 2.5rem;
        }

        /* Domain Input with Suffix */
        .domain-input-box {
            display: flex;
            align-items: center;
            background: var(--surface-accent);
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            height: 50px;
            overflow: hidden;
            transition: var(--transition);
        }

        .domain-input-box:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-ring);
        }

        .domain-input-box input {
            flex: 1;
            height: 100%;
            background: transparent;
            border: none;
            padding: 0 0.85rem;
            color: var(--text-main);
            font-size: 1rem;
            min-width: 0;
            outline: none;
        }

        .domain-suffix-tag {
            height: 100%;
            display: flex;
            align-items: center;
            padding: 0 0.85rem;
            background: rgba(255, 255, 255, 0.04);
            border-left: 1px solid var(--border);
            color: var(--primary);
            font-size: 0.82rem;
            font-weight: 700;
            white-space: nowrap;
        }

        /* Google OAuth Button */
        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            height: 50px;
            background: #ffffff;
            color: #1f2937;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            border: 1px solid #e5e7eb;
        }

        .btn-google:active {
            transform: scale(0.98);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: var(--text-dim);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .divider span {
            padding: 0 0.75rem;
        }

        /* Package Cards */
        .packages-grid {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            margin-bottom: 1.25rem;
        }

        .package-item {
            background: var(--surface-accent);
            border: 2px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.15rem 1.25rem;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }

        .package-item:hover {
            border-color: rgba(255, 255, 255, 0.2);
            background: var(--surface-hover);
        }

        .package-item.selected {
            border-color: var(--primary);
            background: rgba(249, 115, 22, 0.08);
            box-shadow: 0 8px 24px rgba(249, 115, 22, 0.15);
        }

        .package-badge-popular {
            position: absolute;
            top: -10px;
            right: 14px;
            background: var(--primary);
            color: #ffffff;
            font-size: 0.65rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 2px 10px;
            border-radius: 20px;
            box-shadow: 0 2px 8px rgba(249, 115, 22, 0.4);
        }

        .package-header-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 0.4rem;
        }

        .package-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .package-price-tag {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--primary);
        }

        .package-price-tag span {
            font-size: 0.75rem;
            font-weight: 400;
            color: var(--text-muted);
        }

        .package-desc {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 0.6rem;
            line-height: 1.3;
        }

        .package-perks {
            list-style: none;
            font-size: 0.78rem;
            color: var(--text-muted);
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .package-perks li {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .package-perks li span {
            color: var(--primary);
            font-weight: 800;
        }

        /* TOS & SLA Viewer */
        .tos-box {
            background: var(--surface-accent);
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1.1rem;
            max-height: 220px;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            font-size: 0.82rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .tos-box h4 {
            color: var(--text-main);
            font-size: 0.9rem;
            margin: 0.8rem 0 0.3rem;
        }

        .tos-box h4:first-of-type {
            margin-top: 0;
        }

        .tos-scroll-hint {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
            padding: 0 0.25rem;
        }

        .tos-status-pill {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            background: var(--border);
            color: var(--text-muted);
        }

        .tos-status-pill.unlocked {
            background: rgba(52, 211, 153, 0.15);
            color: #34d399;
            border: 1px solid rgba(52, 211, 153, 0.3);
        }

        .tos-agree-container {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            background: rgba(255, 255, 255, 0.02);
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            padding: 0.85rem 1rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .tos-agree-container.active {
            border-color: var(--border-active);
            background: var(--primary-soft);
        }

        .tos-checkbox {
            width: 20px;
            height: 20px;
            accent-color: var(--primary);
            margin-top: 2px;
            cursor: pointer;
        }

        /* Precision Action Button Group (Mobile First) */
        .action-group {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        @media (min-width: 480px) {
            .action-group.multi {
                display: grid;
                grid-template-columns: 1fr 2fr;
            }
        }

        .btn-action {
            height: 52px;
            border-radius: var(--radius-md);
            font-size: 0.95rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            width: 100%;
            outline: none;
        }

        .btn-action:active {
            transform: scale(0.98);
        }

        .btn-primary {
            background: linear-gradient(135deg, hsl(28, 95%, 52%), hsl(22, 95%, 45%));
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(249, 115, 22, 0.35);
        }

        .btn-primary:hover {
            box-shadow: 0 6px 20px rgba(249, 115, 22, 0.45);
        }

        .btn-secondary {
            background: var(--surface-accent);
            color: var(--text-main);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--surface-hover);
        }

        .btn-disabled {
            opacity: 0.45;
            cursor: not-allowed;
            pointer-events: none;
            box-shadow: none !important;
        }

        /* Error Notification */
        .error-banner {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-left: 4px solid #ef4444;
            color: #fca5a5;
            padding: 0.75rem 0.9rem;
            border-radius: var(--radius-sm);
            font-size: 0.82rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Footer Link */
        .bottom-auth-link {
            text-align: center;
            margin-top: 1.75rem;
            font-size: 0.85rem;
            color: var(--text-muted);
            text-decoration: none;
            display: block;
            transition: var(--transition);
        }

        .bottom-auth-link strong {
            color: var(--primary);
            font-weight: 700;
        }

        .bottom-auth-link:hover {
            color: var(--text-main);
        }
    </style>
</head>
<body>

    <div class="ambient-glow" style="top: -60px; left: -60px;"></div>
    <div class="ambient-glow" style="bottom: -60px; right: -60px;"></div>

    <div class="container" x-data="registrationForm">
        <div>
            <!-- Header -->
            <div class="brand-header">
                <div class="brand-badge">✦ Registrasi Pemilik Bisnis</div>
                <h1 class="brand-logo">Unimart<span>.group</span></h1>
                <p class="brand-subtitle">Daftarkan usaha Anda dan kelola ekosistem toko secara cerdas</p>
            </div>

            <!-- Stepper Progress Tracker -->
            <div class="stepper-wrap">
                <div class="step-meta">
                    <span class="step-counter">Langkah <span x-text="step"></span> dari 5</span>
                    <span class="step-title-hint" x-text="['', 'Akun & Akses Masuk', 'Informasi Pemilik', 'Identitas Usaha', 'Pilihan Paket Layanan', 'Kontrak & Persetujuan'][step]"></span>
                </div>

                <div class="stepper-nav">
                    <div class="stepper-track-bg"></div>
                    <div class="stepper-track-fill" :style="'width: ' + ((step - 1) / 4 * 100) + '%'"></div>

                    <div class="step-node" :class="(isGoogleRegister || step > 1) ? 'completed' : (step === 1 ? 'active' : '')">
                        <span x-show="!isGoogleRegister && step <= 1">1</span>
                        <span x-show="isGoogleRegister || step > 1">✓</span>
                    </div>
                    <div class="step-node" :class="step > 2 ? 'completed' : (step === 2 ? 'active' : '')">
                        <span x-show="step <= 2">2</span>
                        <span x-show="step > 2">✓</span>
                    </div>
                    <div class="step-node" :class="step > 3 ? 'completed' : (step === 3 ? 'active' : '')">
                        <span x-show="step <= 3">3</span>
                        <span x-show="step > 3">✓</span>
                    </div>
                    <div class="step-node" :class="step > 4 ? 'completed' : (step === 4 ? 'active' : '')">
                        <span x-show="step <= 4">4</span>
                        <span x-show="step > 4">✓</span>
                    </div>
                    <div class="step-node" :class="step === 5 ? 'active' : ''">
                        <span>5</span>
                    </div>
                </div>
            </div>

            <!-- Server Alert -->
            @if ($errors->any())
                <div class="error-banner">
                    <div>
                        <strong>Pendaftaran Belum Lengkap:</strong>
                        <ul style="margin-left: 1rem; margin-top: 0.25rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if (session('info'))
                <div style="background: rgba(249, 115, 22, 0.12); border-left: 4px solid var(--primary); color: #fed7aa; padding: 0.75rem 0.9rem; border-radius: var(--radius-sm); font-size: 0.82rem; margin-bottom: 1.25rem;">
                    {{ session('info') }}
                </div>
            @endif

            <form action="{{ route('owner.register') }}" method="POST" id="registerForm">
                @csrf
                <input type="hidden" name="package" :value="selectedPackage">

                <!-- STEP 1: AKUN & AKSES MASUK -->
                <div class="step-panel" :class="step === 1 ? 'active' : ''">
                    <div class="panel-heading">
                        <h2>Akun & Akses Masuk</h2>
                        <p>Tentukan kredensial login pemilik untuk mengelola operasional usaha</p>
                    </div>

                    <!-- Google OAuth -->
                    <div>
                        <a href="{{ route('auth.google.redirect') }}" class="btn-google">
                            <svg style="width: 18px; height: 18px;" viewBox="0 0 24 24">
                                <path fill="#EA4335" d="M12.24 10.285V14.4h6.887c-.648 2.41-2.519 4.114-5.136 4.114-3.51 0-6.357-2.827-6.357-6.314s2.848-6.314 6.357-6.314c1.614 0 3.15.588 4.35 1.684l3.125-3.09C19.347 1.83 15.93.971 12.24.971 6.073.971.971 6.027.971 12s5.102 11.029 11.269 11.029c6.438 0 11.269-4.482 11.269-11.029 0-.745-.074-1.285-.224-1.714H12.24z"/>
                            </svg>
                            Daftar Cepat dengan Google (Gmail)
                        </a>

                        <div class="divider">
                            <span>ATAU DAFTAR MANUAL</span>
                        </div>
                    </div>

                    <template x-if="errors.step1">
                        <div class="error-banner">
                            <span x-text="errors.step1"></span>
                        </div>
                    </template>

                    <div class="form-group">
                        <label class="form-label" for="email">Alamat Email Bisnis</label>
                        <input class="form-input" type="email" id="email" name="email" x-model="email" placeholder="contoh: owner@caffekita.com" :required="!isGoogleRegister">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password Baru</label>
                        <input class="form-input" type="password" id="password" name="password" x-model="password" placeholder="Minimal 6 karakter" :required="!isGoogleRegister">
                    </div>

                    <div class="action-group">
                        <button type="button" @click="nextStep()" class="btn-action btn-primary">
                            <span>Lanjutkan ke Data Pemilik</span>
                            <span>→</span>
                        </button>
                    </div>
                </div>

                <!-- STEP 2: INFORMASI PEMILIK -->
                <div class="step-panel" :class="step === 2 ? 'active' : ''">
                    <div class="panel-heading">
                        <h2>Informasi Pemilik Usaha</h2>
                        <p>Data narahubung utama untuk verifikasi dan notifikasi sistem</p>
                    </div>

                    <template x-if="errors.step2">
                        <div class="error-banner">
                            <span x-text="errors.step2"></span>
                        </div>
                    </template>

                    <div class="form-group">
                        <label class="form-label" for="name">Nama Lengkap Pemilik</label>
                        <input class="form-input" type="text" id="name" name="name" x-model="name" placeholder="contoh: Budi Pratama" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">Nomor WhatsApp Aktif</label>
                        <input class="form-input" type="tel" id="phone" name="phone" x-model="phone" placeholder="contoh: 081234567890" required>
                    </div>

                    <div class="action-group multi">
                        <button type="button" @click="prevStep()" class="btn-action btn-secondary" x-show="!isGoogleRegister">
                            <span>← Kembali</span>
                        </button>
                        <button type="button" @click="nextStep()" class="btn-action btn-primary" :style="isGoogleRegister ? 'grid-column: span 2;' : ''">
                            <span>Lanjutkan ke Info Bisnis</span>
                            <span>→</span>
                        </button>
                    </div>
                </div>

                <!-- STEP 3: IDENTITAS USAHA -->
                <div class="step-panel" :class="step === 3 ? 'active' : ''">
                    <div class="panel-heading">
                        <h2>Identitas & Detail Bisnis</h2>
                        <p>Atur nama dan subdomain toko untuk pelanggan dan katalog online</p>
                    </div>

                    <template x-if="isGoogleRegister">
                        <div style="background: rgba(52, 211, 153, 0.1); border: 1px solid rgba(52, 211, 153, 0.3); color: #34d399; padding: 0.65rem 0.85rem; border-radius: var(--radius-sm); margin-bottom: 1.25rem; font-size: 0.8rem;">
                            ✓ Terverifikasi Google: <strong x-text="email"></strong>
                        </div>
                    </template>

                    <template x-if="errors.step3">
                        <div class="error-banner">
                            <span x-text="errors.step3"></span>
                        </div>
                    </template>

                    <div class="form-group">
                        <label class="form-label" for="business_name">Nama Toko / Resto / Bisnis</label>
                        <input class="form-input" type="text" id="business_name" name="business_name" x-model="business_name" placeholder="contoh: Kopi Kita Nusantara" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="domain">Subdomain Toko Unimart</label>
                        <div class="domain-input-box">
                            <input type="text" id="domain" name="domain" x-model="domain" placeholder="kopikita" required>
                            <span class="domain-suffix-tag">.unimart.group</span>
                        </div>
                        <p style="font-size: 0.72rem; color: var(--text-dim); margin-top: 0.35rem;">
                            Alamat ini menjadi URL QR Menu dan Toko Online Anda.
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="business_type">Kategori Industri</label>
                        <select class="form-input" id="business_type" name="business_type" x-model="business_type" required>
                            <option value="Kedai Kopi / Coffee Shop">☕ Kedai Kopi / Coffee Shop</option>
                            <option value="Kafe F&B / Restaurant">🍽️ Kafe & Restoran (F&B)</option>
                            <option value="Toko Grosir / Kelontong">📦 Toko Grosir & Kelontong</option>
                            <option value="Toko Roti / Bakery">🥐 Bakery & Toko Kue</option>
                            <option value="Warung Makan / Kedai">🍛 Warung Makan & Kedai</option>
                            <option value="Lainnya">✨ Kategori Usaha Lainnya</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="business_address">Alamat Operasional Bisnis</label>
                        <textarea class="form-input" id="business_address" name="business_address" x-model="business_address" rows="2" placeholder="contoh: Jl. Merdeka No. 88, Kota Padang" required></textarea>
                    </div>

                    <div class="action-group multi">
                        <button type="button" @click="prevStep()" class="btn-action btn-secondary">
                            <span>← Kembali</span>
                        </button>
                        <button type="button" @click="nextStep()" class="btn-action btn-primary">
                            <span>Pilih Paket Layanan</span>
                            <span>→</span>
                        </button>
                    </div>
                </div>

                <!-- STEP 4: PILIHAN PAKET LAYANAN -->
                <div class="step-panel" :class="step === 4 ? 'active' : ''">
                    <div class="panel-heading">
                        <h2>Pilih Paket Layanan</h2>
                        <p>Pilih paket langganan yang sesuai dengan skala dan kebutuhan toko</p>
                    </div>

                    <div class="packages-grid">
                        <!-- Basic -->
                        <div class="package-item" :class="selectedPackage === 'Basic' ? 'selected' : ''" @click="selectedPackage = 'Basic'">
                            <div class="package-header-row">
                                <span class="package-name">Basic Starter</span>
                                <span class="package-price-tag">Rp 50K<span>/bln</span></span>
                            </div>
                            <p class="package-desc">Untuk toko baru yang ingin mulai rapi & go-digital</p>
                            <ul class="package-perks">
                                <li><span>✓</span> Profil digital & QR Menu online</li>
                                <li><span>✓</span> Integrasi pembayaran QRIS resmi</li>
                                <li><span>✓</span> Laporan penjualan harian</li>
                            </ul>
                        </div>

                        <!-- Bisnis (Recommended) -->
                        <div class="package-item" :class="selectedPackage === 'Bisnis' ? 'selected' : ''" @click="selectedPackage = 'Bisnis'">
                            <span class="package-badge-popular">Paling Populer</span>
                            <div class="package-header-row">
                                <span class="package-name">Bisnis Pro</span>
                                <span class="package-price-tag">Rp 100K<span>/bln</span></span>
                            </div>
                            <p class="package-desc">Solusi lengkap mandiri meja bebas antre</p>
                            <ul class="package-perks">
                                <li><span>✓</span> Semua fitur Basic Starter</li>
                                <li><span>✓</span> Self-Order QR Meja + KDS Dapur</li>
                                <li><span>✓</span> Notifikasi pesanan instan WhatsApp</li>
                                <li><span>✓</span> Manajemen buku utang & piutang</li>
                            </ul>
                        </div>

                        <!-- Premium VIP -->
                        <div class="package-item" :class="selectedPackage === 'Premium VIP' ? 'selected' : ''" @click="selectedPackage = 'Premium VIP'">
                            <div class="package-header-row">
                                <span class="package-name">Enterprise VIP</span>
                                <span class="package-price-tag">Rp 200K<span>/bln</span></span>
                            </div>
                            <p class="package-desc">Bisnis autopilot dengan kecerdasan buatan (AI)</p>
                            <ul class="package-perks">
                                <li><span>✓</span> Semua fitur Bisnis Pro</li>
                                <li><span>✓</span> Full POS Kasir Multi-Karyawan</li>
                                <li><span>✓</span> AI Gemini Chatbot WhatsApp 24 Jam</li>
                                <li><span>✓</span> Integrasi Hotspot WiFi iZone</li>
                            </ul>
                        </div>
                    </div>

                    <div class="action-group multi">
                        <button type="button" @click="prevStep()" class="btn-action btn-secondary">
                            <span>← Kembali</span>
                        </button>
                        <button type="button" @click="nextStep()" class="btn-action btn-primary">
                            <span>Tinjau Kontrak</span>
                            <span>→</span>
                        </button>
                    </div>
                </div>

                <!-- STEP 5: KONTRAK KERJA SAMA & SYARAT -->
                <div class="step-panel" :class="step === 5 ? 'active' : ''">
                    <div class="panel-heading">
                        <h2>Kontrak & Perjanjian Layanan</h2>
                        <p>Tinjau standar Service Level Agreement (SLA) dan privasi Unimart</p>
                    </div>

                    <div class="tos-box" @scroll="checkScroll">
                        <h4>Syarat & Ketentuan Layanan (SLA) Unimart.group</h4>
                        <p>Selamat datang di <strong>Unimart.group</strong>. Layanan kami dirancang dengan tata kelola teknologi informasi bertaraf internasional yang mengacu pada standar <strong>ISO/IEC 27001 (Keamanan Informasi)</strong> dan <strong>ISO/IEC 20000 (Manajemen Layanan TI)</strong>.</p>
                        
                        <h4>1. Isolasi Database Multi-Tenant</h4>
                        <p>Data transaksi, menu, stok, pelanggan, dan kasir bisnis Anda diisolasi secara ketat dan terlindungi dari tenant lain. Sandi disimpan dengan enkripsi searah (bcrypt).</p>
                        
                        <h4>2. Jaminan Uptime 99.9% (SLA)</h4>
                        <p>Unimart.group menjamin Service Level Agreement waktu aktif sebesar 99.9% setiap bulan. Perawatan terjadwal dilakukan pada jam operasional terendah (02:00 - 04:00 WIB).</p>
                        
                        <h4>3. Kepemilikan Data Bisnis</h4>
                        <p>Seluruh basis data pelanggan, penjualan, dan riwayat utang-piutang adalah hak milik penuh mitra bisnis. Unimart tidak akan membagikan data kepada pihak ketiga.</p>
                        
                        <h4>4. Ketentuan Tagihan & Langganan</h4>
                        <p>Biaya langganan berlaku bulanan sesuai paket pilihan. Masa aktif dapat diperpanjang secara otomatis melalui dasbor pemilik usaha.</p>

                        <p style="margin-top: 0.8rem; font-style: italic; opacity: 0.7;">
                            Gulir hingga batas bawah untuk menyetujui kontrak ini.
                        </p>
                    </div>

                    <div class="tos-scroll-hint">
                        <span x-show="!hasScrolledToBottom">↓ Gulir kotak kontrak hingga ke bagian bawah</span>
                        <span x-show="hasScrolledToBottom" style="color: #34d399; font-weight: 700;">✓ Kontrak selesai dibaca</span>
                        <span class="tos-status-pill" :class="hasScrolledToBottom ? 'unlocked' : ''" x-text="hasScrolledToBottom ? 'TERBUKA' : 'TERKUNCI'"></span>
                    </div>

                    <div class="tos-agree-container" :class="agreeTerms ? 'active' : ''" @click="if(hasScrolledToBottom) agreeTerms = !agreeTerms">
                        <input type="checkbox" id="agree_terms" name="agree_terms" x-model="agreeTerms" :disabled="!hasScrolledToBottom" class="tos-checkbox" @click.stop>
                        <label for="agree_terms" style="font-size: 0.82rem; color: var(--text-main); line-height: 1.4; cursor: pointer;">
                            Saya menyetujui kontrak kerja sama, Syarat & Ketentuan Layanan (SLA), dan kebijakan perlindungan informasi Unimart.group.
                        </label>
                    </div>

                    <div class="action-group multi">
                        <button type="button" @click="prevStep()" class="btn-action btn-secondary">
                            <span>← Kembali</span>
                        </button>
                        <button type="submit" class="btn-action btn-primary" :class="(!agreeTerms || !hasScrolledToBottom) ? 'btn-disabled' : ''">
                            <span>Daftarkan Bisnis Sekarang 🚀</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <a href="{{ route('owner.login') }}" class="bottom-auth-link">
            Sudah memiliki akun bisnis? <strong>Masuk di sini</strong>
        </a>
    </div>

    <!-- Script Component Logic -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('registrationForm', () => ({
                isGoogleRegister: @json(session()->has('google_register')),
                step: @json(session()->has('google_register') ? 2 : 1),
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
                
                // Validation Error Messages
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
                    this.errors.step2 = '';
                    if (!this.name) {
                        this.errors.step2 = 'Nama lengkap pemilik wajib diisi.';
                        return false;
                    }
                    if (!this.phone) {
                        this.errors.step2 = 'Nomor WhatsApp wajib diisi.';
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
                        this.errors.step3 = 'Domain alias hanya boleh huruf, angka, dan dash (-).';
                        return false;
                    }
                    if (!this.business_address) {
                        this.errors.step3 = 'Alamat operasional wajib diisi.';
                        return false;
                    }
                    return true;
                },

                nextStep() {
                    if (this.step === 1 && !this.validateStep1()) return;
                    if (this.step === 2 && !this.validateStep2()) return;
                    if (this.step === 3 && !this.validateStep3()) return;
                    if (this.step < 5) {
                        this.step++;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                prevStep() {
                    if (this.isGoogleRegister) {
                        if (this.step > 2) {
                            this.step--;
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        }
                    } else {
                        if (this.step > 1) {
                            this.step--;
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        }
                    }
                },

                checkScroll(e) {
                    const element = e.target;
                    if (element.scrollHeight - element.scrollTop <= element.clientHeight + 20) {
                        this.hasScrolledToBottom = true;
                    }
                }
            }));
        });
    </script>

</body>
</html>
