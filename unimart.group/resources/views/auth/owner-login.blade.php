<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pemilik Bisnis - Unimart.group</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Playfair+Display:ital,wght=0,600;0,700;1,400&display=swap" rel="stylesheet">
    
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
            --transition: all 0.3s ease;
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
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
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
            max-width: 440px;
            box-shadow: var(--shadow);
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .logo-area {
            margin-bottom: 2.5rem;
        }

        .logo-area h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
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

        /* Errors Alert */
        .alert-error {
            background: rgba(255, 59, 48, 0.15);
            border: 1px solid rgba(255, 59, 48, 0.3);
            color: #ff453a;
            padding: 0.8rem 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 2rem;
            text-align: left;
            font-size: 0.85rem;
        }

        .alert-error ul {
            list-style-position: inside;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
        }

        .form-input {
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            background: var(--surface-accent);
            color: var(--text-main);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(230, 115, 0, 0.2);
        }

        /* Button */
        .btn-submit {
            background: var(--primary);
            color: var(--background);
            border: none;
            width: 100%;
            padding: 0.9rem;
            border-radius: var(--radius-md);
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(230, 115, 0, 0.4);
            transition: var(--transition);
            margin-top: 1rem;
        }

        .btn-submit:hover {
            background: var(--primary-light);
            transform: translateY(-1px);
        }

        .links-area {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
            font-size: 0.9rem;
        }

        .links-area a {
            color: var(--text-muted);
            text-decoration: none;
            transition: var(--transition);
        }

        .links-area a:hover {
            color: var(--text-main);
        }
    </style>
</head>
<body>

    <div class="glow" style="top: -10%; left: -10%;"></div>
    <div class="glow" style="bottom: -10%; right: -10%;"></div>

    <div class="container">
        <div class="logo-area">
            <h1>Unimart<span>group</span></h1>
            <p>Masuk ke dashboard pemilik bisnis</p>
        </div>

        @if ($errors->any())
            <div class="alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Google Login Button -->
        <div style="margin-bottom: 1.5rem; text-align: center;">
            <a href="{{ route('auth.google.redirect') }}" style="display: flex; align-items: center; justify-content: center; gap: 0.8rem; background: #fff; color: #1f2937; border: 1px solid #d1d5db; padding: 0.8rem 1.5rem; border-radius: var(--radius-md); font-weight: 600; text-decoration: none; transition: var(--transition); box-shadow: 0 2px 4px rgba(0,0,0,0.05);" onmouseover="this.style.background='#f9fafb'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='#fff'; this.style.transform='none'">
                <!-- Google SVG Icon -->
                <svg style="width: 18px; height: 18px;" viewBox="0 0 24 24">
                    <path fill="#EA4335" d="M12.24 10.285V14.4h6.887c-.648 2.41-2.519 4.114-5.136 4.114-3.51 0-6.357-2.827-6.357-6.314s2.848-6.314 6.357-6.314c1.614 0 3.15.588 4.35 1.684l3.125-3.09C19.347 1.83 15.93.971 12.24.971 6.073.971.971 6.027.971 12s5.102 11.029 11.269 11.029c6.438 0 11.269-4.482 11.269-11.029 0-.745-.074-1.285-.224-1.714H12.24z"/>
                </svg>
                Masuk cepat dengan Google
            </a>
            
            <div style="display: flex; align-items: center; margin: 1.5rem 0; color: var(--text-muted); font-size: 0.85rem;">
                <div style="flex: 1; height: 1px; background: var(--border);"></div>
                <span style="padding: 0 1rem; font-weight: 600;">ATAU MASUK MANUAL</span>
                <div style="flex: 1; height: 1px; background: var(--border);"></div>
            </div>
        </div>

        <form action="{{ route('owner.login') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label class="form-label" for="email">Email Pemilik</label>
                <input class="form-input" type="email" id="email" name="email" value="{{ old('email') }}" placeholder="owner@caffekita.com" required autocomplete="username">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input class="form-input" type="password" id="password" name="password" placeholder="Masukkan password Anda" required autocomplete="current-password">
            </div>

            <button class="btn-submit" type="submit">Masuk Dashboard</button>
        </form>

        <div class="links-area">
            <a href="{{ route('owner.register') }}">Belum mendaftarkan bisnis? Daftar di sini</a>
            <a href="/">← Kembali ke Beranda Utama</a>
        </div>
    </div>

</body>
</html>
