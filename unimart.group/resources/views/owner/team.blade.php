<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Karyawan - Unimart.group</title>
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
            --border: rgba(255, 255, 255, 0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            --radius-lg: 20px;
            --radius-md: 14px;
            --radius-sm: 8px;
            --secondary: hsl(150, 60%, 35%); /* Emerald */
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
            background: rgba(15, 23, 30, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 1.2rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
        }

        .logo span {
            font-family: 'Outfit', sans-serif;
            font-size: 0.8rem;
            font-weight: 700;
            background: var(--primary);
            color: var(--background);
            padding: 0.1rem 0.5rem;
            border-radius: 20px;
            margin-left: 0.3rem;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .badge-package {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 700;
            background: var(--primary);
            color: var(--background);
            padding: 0.1rem 0.5rem;
            border-radius: 12px;
            margin-top: 0.2rem;
            text-transform: uppercase;
        }

        .badge-package.vip {
            background: linear-gradient(135deg, #ffd700, #ffa500);
            color: #000;
        }

        .badge-package.pro {
            background: hsl(200, 80%, 45%);
            color: #fff;
        }

        .btn-logout {
            background: transparent;
            color: #ff453a;
            border: 1px solid rgba(255, 69, 58, 0.2);
            padding: 0.4rem 1rem;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            transition: var(--transition);
        }

        .btn-logout:hover {
            background: rgba(255, 69, 58, 0.1);
            border-color: #ff453a;
        }

        /* Main Workspace */
        main {
            padding: 2.5rem 2rem;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            flex-grow: 1;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, var(--surface-accent), var(--surface));
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-bottom: 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        @media (max-width: 768px) {
            .welcome-banner {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }

        .welcome-text h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .welcome-text p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .nav-tabs {
            display: flex;
            gap: 1rem;
        }

        .tab-link {
            color: var(--text-main);
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            padding: 0.6rem 1.2rem;
            border-radius: var(--radius-md);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .tab-link:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .tab-link.active {
            background: var(--primary);
            color: var(--background);
            border-color: var(--primary);
        }

        /* Form & List Layout */
        .grid-layout {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2.5rem;
        }

        @media (max-width: 968px) {
            .grid-layout {
                grid-template-columns: 1fr;
            }
        }

        /* Staff Form Box */
        .staff-form-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow);
            height: fit-content;
        }

        .staff-form-box h3 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.4rem;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 1.2rem;
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

        .btn-submit {
            background: var(--primary);
            color: var(--background);
            border: none;
            width: 100%;
            padding: 0.9rem;
            border-radius: var(--radius-md);
            font-size: 1rem;
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

        /* Staff List Box */
        .staff-list-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow);
        }

        .staff-list-box h3 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.4rem;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            padding: 1rem;
            border-bottom: 2px solid var(--border);
            color: var(--text-muted);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.95rem;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .badge-role {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            text-transform: uppercase;
        }

        .badge-role.cashier {
            background: rgba(230, 115, 0, 0.15);
            color: var(--primary);
        }

        .badge-role.chef {
            background: rgba(0, 120, 200, 0.15);
            color: hsl(200, 80%, 55%);
        }

        .badge-role.waiter {
            background: rgba(40, 167, 69, 0.15);
            color: #28a745;
        }

        /* Success & Error Messages */
        .alert-success {
            background: rgba(40, 167, 69, 0.15);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: #28a745;
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .alert-error {
            background: rgba(255, 59, 48, 0.15);
            border: 1px solid rgba(255, 59, 48, 0.3);
            color: #ff453a;
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="dashboard-header">
        <div class="logo-area">
            <a href="/" class="logo">Unimart<span>dashboard</span></a>
        </div>
        <div class="user-menu">
            <div class="user-info">
                <div class="user-name">{{ Auth::user()->name }}</div>
                <div class="badge-package @if($package === 'Premium VIP') vip @elseif($package === 'Profesional') pro @endif">
                    Paket {{ $package }}
                </div>
            </div>
            <form action="{{ route('owner.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">Keluar</button>
            </form>
        </div>
    </header>

    <!-- Main Workspace -->
    <main>
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="welcome-text">
                <h2>Manajemen Karyawan</h2>
                <p>Tambah staff baru dan tentukan hak akses peran khusus untuk operasional laci kasir atau layar dapur.</p>
            </div>
            <div class="nav-tabs">
                <a href="{{ route('owner.dashboard') }}" class="tab-link">Dasbor</a>
                <a href="{{ route('owner.team') }}" class="tab-link active">Kelola Staf</a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                <strong>Gagal Menambahkan Karyawan:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid-layout">
            <!-- Form Tambah Staff (Kiri) -->
            <div class="staff-form-box">
                <h3>Tambah Karyawan Baru</h3>
                <form action="{{ route('owner.team.add') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label" for="name">Nama Lengkap</label>
                        <input class="form-input" type="text" id="name" name="name" placeholder="Contoh: Budi Santoso" value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="role">Posisi / Peran Pekerjaan</label>
                        <select class="form-input" id="role" name="role" required>
                            <option value="cashier" @if(old('role') === 'cashier') selected @endif>Kasir (Cashier)</option>
                            <option value="chef" @if(old('role') === 'chef') selected @endif>Koki / Dapur (Chef)</option>
                            <option value="waiter" @if(old('role') === 'waiter') selected @endif>Pelayan (Waiter)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="pin">PIN Akses Cepat</label>
                        <input class="form-input" type="text" id="pin" name="pin" placeholder="4 digit angka, misal: 4321" maxlength="4" pattern="\d{4}" value="{{ old('pin') }}" required>
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.4rem;">PIN 4 digit unik digunakan staf untuk masuk cepat di perangkat tablet kasir/dapur.</p>
                    </div>

                    <button class="btn-submit" type="submit">Simpan Data Staf</button>
                </form>
            </div>

            <!-- Daftar Staff (Kanan) -->
            <div class="staff-list-box">
                <h3>Daftar Karyawan Aktif</h3>
                @if($staff->isEmpty())
                    <p style="color: var(--text-muted); text-align: center; padding: 2rem 0;">Belum ada karyawan terdaftar. Silakan gunakan formulir di sebelah kiri untuk menambah staf baru.</p>
                @else
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Posisi / Role</th>
                                    <th style="text-align: center;">PIN Login</th>
                                    <th>Tanggal Bergabung</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($staff as $member)
                                    <tr>
                                        <td>
                                            <strong>{{ $member->name }}</strong><br>
                                            <span style="font-size: 0.8rem; color: var(--text-muted);">{{ $member->email }}</span>
                                        </td>
                                        <td>
                                            <span class="badge-role {{ $member->role }}">
                                                @if($member->role === 'cashier')
                                                    Kasir
                                                @elseif($member->role === 'chef')
                                                    Koki / Dapur
                                                @elseif($member->role === 'waiter')
                                                    Pelayan
                                                @else
                                                    {{ $member->role }}
                                                @endif
                                            </span>
                                        </td>
                                        <td style="text-align: center; font-family: monospace; font-size: 1.1rem; font-weight: bold; color: var(--primary);">
                                            {{ $member->pin }}
                                        </td>
                                        <td style="color: var(--text-muted); font-size: 0.85rem;">
                                            {{ $member->created_at->format('d M Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </main>

</body>
</html>
