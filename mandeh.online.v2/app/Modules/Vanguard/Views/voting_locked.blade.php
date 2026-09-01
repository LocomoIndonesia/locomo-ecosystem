<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Voting Terkunci - Vanguard HPI Pessel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top right, #1b1222 0%, #06050b 100%);
        }
        .glass {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px rgba(255, 255, 255, 0.05) solid;
        }
        .gradient-text {
            background: linear-gradient(135deg, #f43f5e 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full glass rounded-3xl p-8 text-center shadow-2xl relative overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-rose-500/10 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="text-6xl mb-6">🔒</div>
        <h1 class="text-3xl font-extrabold mb-3 gradient-text">E-Voting Terkunci</h1>
        <p class="text-slate-400 text-sm leading-relaxed mb-6">
            Akses pemungutan suara Muscab HPI Pesisir Selatan 2026 dikunci secara otomatis oleh sistem. Anda belum mencapai batas kelulusan **{{ $required_points }} Poin**.
        </p>

        <!-- Point Card -->
        <div class="p-5 glass rounded-2xl mb-6 flex justify-around items-center">
            <div>
                <span class="text-xs text-slate-400 block mb-1">Poin Anda</span>
                <span class="text-3xl font-black text-rose-400">{{ $participant->reputation_points }}</span>
            </div>
            <div class="h-8 w-px bg-slate-800"></div>
            <div>
                <span class="text-xs text-slate-400 block mb-1 font-semibold">Dibutuhkan</span>
                <span class="text-3xl font-black text-slate-200">{{ $required_points }}</span>
            </div>
        </div>

        <p class="text-xs text-slate-500 mb-6 italic">
            Silakan kembali ke dashboard, lakukan check-in harian, dan selesaikan misi praktis lapangan/siber untuk mengumpulkan poin reputasi.
        </p>

        <a href="{{ route('vanguard.dashboard') }}" class="w-full block py-3.5 bg-gradient-to-r from-rose-500 to-pink-500 text-white font-bold rounded-2xl hover:opacity-90 transition shadow-lg shadow-rose-500/10">
            Kembali ke Dashboard
        </a>
    </div>
</body>
</html>
