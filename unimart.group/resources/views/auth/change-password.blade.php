<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password - Unimart</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <!-- Custom Vanilla CSS -->
    <style>
        :root {
            --primary: hsl(28, 48%, 48%);
            --primary-light: hsl(28, 50%, 60%);
            --background: hsl(210, 24%, 8%);
            --surface: hsl(210, 24%, 12%);
            --text-main: hsl(210, 20%, 95%);
            --text-muted: hsl(210, 14%, 65%);
            --success: hsl(120, 50%, 40%);
            --error: hsl(0, 75%, 50%);
            --radius: 16px;
            --shadow: 0 10px 30px rgba(0,0,0,0.5);
            --transition: all 0.2s ease;
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

        .login-card {
            background: var(--surface);
            padding: 2.5rem 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 400px;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }

        .header p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            text-align: left;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--text-main);
            font-size: 0.95rem;
            outline: none;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(219, 114, 38, 0.2);
            background: rgba(255, 255, 255, 0.08);
        }

        .btn-submit {
            width: 100%;
            padding: 0.9rem;
            background: var(--primary);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
            transition: var(--transition);
        }

        .btn-submit:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-back {
            display: block;
            width: 100%;
            padding: 0.9rem;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            margin-top: 0.5rem;
            transition: var(--transition);
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.03);
            color: var(--text-main);
        }

        .alert-success {
            background: rgba(46, 117, 89, 0.15);
            border: 1px solid var(--success);
            color: var(--success);
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            text-align: center;
        }

        .error-message {
            color: var(--error);
            font-size: 0.85rem;
            margin-top: 1rem;
            font-weight: 600;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="header">
            <h1>Ubah Password</h1>
            <p>Perbarui kredensial akun Anda secara mandiri</p>
        </div>

        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('password.change') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="current_password">Password Saat Ini</label>
                <input type="password" name="current_password" id="current_password" class="form-control" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password Baru</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn-submit">Simpan Perubahan</button>
            <a href="javascript:history.back()" class="btn-back">Kembali</a>

            @if($errors->any())
                <div class="error-message">
                    {{ $errors->first() }}
                </div>
            @endif
        </form>
    </div>

</body>
</html>
