<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mandeh.ID SSO - Daftar</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #311042 100%);
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-highlight: rgba(255, 255, 255, 0.15);
            --primary: #8b5cf6;
            --primary-hover: #a78bfa;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --error-bg: rgba(239, 68, 68, 0.15);
            --error-border: rgba(239, 68, 68, 0.3);
            --error-text: #fca5a5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text-primary);
            overflow-x: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.3) 0%, transparent 70%);
            top: 15%;
            left: 20%;
            z-index: 0;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.2) 0%, transparent 70%);
            bottom: 10%;
            right: 15%;
            z-index: 0;
            pointer-events: none;
        }

        .login-container {
            width: 100%;
            max-width: 500px;
            z-index: 10;
            margin: 20px 0;
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .header {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-text {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(to right, #a78bfa, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -1px;
            margin-bottom: 8px;
            display: inline-block;
        }

        .subtitle {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 14px 16px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        
        .form-select option {
            background: #1e1b4b;
            color: var(--text-primary);
        }

        .form-input:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(to right, var(--primary) 0%, #7c3aed 100%);
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s ease, transform 0.1s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
            margin-top: 10px;
        }

        .btn-submit:hover {
            opacity: 0.95;
            box-shadow: 0 6px 16px rgba(124, 58, 237, 0.4);
        }

        .error-container {
            background: var(--error-bg);
            border: 1px solid var(--error-border);
            color: var(--error-text);
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
        }

        .footer {
            margin-top: 32px;
            text-align: center;
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .link-text {
            color: #a78bfa;
            text-decoration: none;
        }
        .link-text:hover {
            text-decoration: underline;
        }
        
        /* Responsive Styles */
        @media (max-width: 480px) {
            body { padding: 16px; }
            .glass-card { padding: 24px 20px; border-radius: 20px; }
            .logo-text { font-size: 24px; }
            .header { margin-bottom: 24px; }
            .form-input, .form-select { padding: 12px 14px; font-size: 13px; }
            .btn-submit { padding: 12px; font-size: 14px; }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="glass-card">
        <div class="header">
            <span class="logo-text">Mandeh.ID</span>
            <div class="subtitle">Buat akun untuk bergabung ke dalam Ekosistem.</div>
        </div>

        @if($errors->any())
            <div class="error-container">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ url('/register') }}">
            @csrf
            
            @if(isset($oauthParams))
                @foreach($oauthParams as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
            @endif

            <div class="form-group">
                <label for="name" class="form-label">Nama Lengkap</label>
                <input type="text" id="name" name="name" class="form-input" placeholder="Masukkan nama" required autofocus value="{{ old('name') }}">
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Alamat Email</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="contoh@mandeh.id" required value="{{ old('email') }}">
            </div>
            
            <div class="form-group">
                <label for="user_type" class="form-label">Tipe Akun</label>
                <select id="user_type" name="user_type" class="form-select" required>
                    <option value="warga" {{ old('user_type') == 'warga' ? 'selected' : '' }}>Warga</option>
                    <option value="wisatawan" {{ old('user_type') == 'wisatawan' ? 'selected' : '' }}>Wisatawan</option>
                    <option value="bisnis" {{ old('user_type') == 'bisnis' ? 'selected' : '' }}>Bisnis</option>
                    <option value="mitra" {{ old('user_type') == 'mitra' ? 'selected' : '' }}>Mitra</option>
                    <option value="vanguard" {{ old('user_type') == 'vanguard' ? 'selected' : '' }}>Vanguard (Tim Khusus)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Kata Sandi</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="Minimal 8 karakter" required>
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" placeholder="Ulangi kata sandi" required>
            </div>

            <button type="submit" class="btn-submit">Daftar Sekarang</button>
            
            <div style="margin-top: 15px; text-align: center; font-size: 13px;">
                <a href="{{ isset($oauthParams) && count($oauthParams) > 0 ? '/oauth/authorize?'.http_build_query($oauthParams) : '/' }}" class="link-text">Sudah punya akun? Masuk di sini</a>
            </div>
        </form>
    </div>

    <div class="footer">
        &copy; 2026 Mandeh Identity Ecosystem. Kedaulatan Data Komunitas.
    </div>
</div>

</body>
</html>
