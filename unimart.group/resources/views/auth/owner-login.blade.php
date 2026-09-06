<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pemilik Bisnis — Unimart.group</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,500&display=swap" rel="stylesheet">

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

        .ambient-glow {
            position: fixed;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.14) 0%, transparent 70%);
            pointer-events: none;
            z-index: 1;
        }

        .container {
            width: 100%;
            min-height: 100vh;
            background: var(--surface);
            padding: 2rem 1.25rem 2.5rem;
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
                max-width: 460px;
                border-radius: var(--radius-xl);
                border: 1px solid var(--border);
                padding: 2.75rem 2.25rem;
                box-shadow: var(--shadow-lg);
            }
        }

        .brand-header {
            text-align: center;
            margin-bottom: 2rem;
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
            font-size: 2.2rem;
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
            margin-bottom: 1.5rem;
            text-align: left;
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

        /* Form Controls */
        .form-group {
            margin-bottom: 1.25rem;
            text-align: left;
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
            font-size: 1rem;
            transition: var(--transition);
            outline: none;
        }

        .form-input:focus {
            border-color: var(--primary);
            background: hsl(220, 22%, 18%);
            box-shadow: 0 0 0 3px var(--primary-ring);
        }

        .btn-submit {
            width: 100%;
            height: 52px;
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            outline: none;
            background: linear-gradient(135deg, hsl(28, 95%, 52%), hsl(22, 95%, 45%));
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(249, 115, 22, 0.35);
            margin-top: 1.75rem;
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        .bottom-links {
            text-align: center;
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            font-size: 0.85rem;
        }

        .bottom-links a {
            color: var(--text-muted);
            text-decoration: none;
            transition: var(--transition);
        }

        .bottom-links a strong {
            color: var(--primary);
        }

        .bottom-links a:hover {
            color: var(--text-main);
        }
    </style>
</head>
<body>

    <div class="ambient-glow" style="top: -60px; left: -60px;"></div>
    <div class="ambient-glow" style="bottom: -60px; right: -60px;"></div>

    <div class="container">
        <div>
            <div class="brand-header">
                <div class="brand-badge">✦ Ruang Kendali Pemilik</div>
                <h1 class="brand-logo">Unimart<span>.group</span></h1>
                <p class="brand-subtitle">Masuk untuk mengelola operasional toko & kasir pintar</p>
            </div>

            @if ($errors->any())
                <div class="error-banner">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Google Login -->
            <div>
                <a href="{{ route('auth.google.redirect') }}" class="btn-google">
                    <svg style="width: 18px; height: 18px;" viewBox="0 0 24 24">
                        <path fill="#EA4335" d="M12.24 10.285V14.4h6.887c-.648 2.41-2.519 4.114-5.136 4.114-3.51 0-6.357-2.827-6.357-6.314s2.848-6.314 6.357-6.314c1.614 0 3.15.588 4.35 1.684l3.125-3.09C19.347 1.83 15.93.971 12.24.971 6.073.971.971 6.027.971 12s5.102 11.029 11.269 11.029c6.438 0 11.269-4.482 11.269-11.029 0-.745-.074-1.285-.224-1.714H12.24z"/>
                    </svg>
                    Masuk Cepat dengan Google
                </a>

                <div class="divider">
                    <span>ATAU MASUK DENGAN EMAIL</span>
                </div>
            </div>

            <form action="{{ route('owner.login') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label class="form-label" for="email">Alamat Email Terdaftar</label>
                    <input class="form-input" type="email" id="email" name="email" value="{{ old('email') }}" placeholder="contoh: owner@caffekita.com" required autocomplete="username">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-input" type="password" id="password" name="password" placeholder="Masukkan password Anda" required autocomplete="current-password">
                </div>

                <button class="btn-submit" type="submit">
                    <span>Masuk ke Dasbor Pemilik</span>
                    <span>→</span>
                </button>
            </form>
        </div>

        <div class="bottom-links">
            <a href="{{ route('owner.register') }}">
                Belum punya akun bisnis? <strong>Daftar di sini</strong>
            </a>
            <a href="/">← Kembali ke Beranda Utama</a>
        </div>
    </div>

</body>
</html>
