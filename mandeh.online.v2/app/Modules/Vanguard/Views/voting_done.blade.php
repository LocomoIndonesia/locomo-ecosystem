<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Selesai - Vanguard HPI Pessel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top right, #0d1a1b 0%, #030808 100%);
        }
        .glass {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px rgba(255, 255, 255, 0.05) solid;
        }
        .gradient-text {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full glass rounded-3xl p-8 text-center shadow-2xl relative overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-emerald-500/10 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="text-6xl mb-6">✅</div>
        <h1 class="text-3xl font-extrabold mb-3 gradient-text font-bold text-emerald-400">Partisipasi Berhasil</h1>
        <p class="text-slate-400 text-sm leading-relaxed mb-6">
            Suara Anda untuk pemilihan ketua definitive HPI DPC Pesisir Selatan 2026 telah berhasil disalurkan secara aman.
        </p>

        <div class="p-5 glass border-emerald-500/10 rounded-2xl mb-6 space-y-2 text-left text-xs text-slate-400">
            <div class="flex justify-between">
                <span>Voter ID:</span>
                <span class="font-mono text-slate-300">{{ $participant->tracking_id }}</span>
            </div>
            <div class="flex justify-between">
                <span>Status Enkripsi:</span>
                <span class="text-emerald-400 font-semibold">Aktif (AES-256-CBC)</span>
            </div>
            <div class="flex justify-between">
                <span>Waktu Vote:</span>
                <span class="text-slate-300">{{ $participant->voted_at ? $participant->voted_at->format('d M Y H:i:s') : now()->format('d M Y H:i:s') }}</span>
            </div>
        </div>

        <p class="text-xs text-slate-500 mb-6">
            Terima kasih atas partisipasi aktif Anda dalam mengawal kedaulatan pariwisata digital nagari.
        </p>

        <a href="{{ route('vanguard.dashboard') }}" class="w-full block py-3.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold rounded-2xl hover:opacity-90 transition shadow-lg shadow-emerald-500/10">
            Kembali ke Dashboard
        </a>
    </div>
</body>
</html>
