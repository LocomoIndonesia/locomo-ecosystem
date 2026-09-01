<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Voting Muscab - Vanguard HPI Pessel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top right, #0d1226 0%, #06080f 100%);
        }
        .glass {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px rgba(255, 255, 255, 0.05) solid;
        }
        .gradient-text {
            background: linear-gradient(135deg, #e2e8f0 0%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-xl w-full glass rounded-3xl p-8 shadow-2xl relative overflow-hidden">
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-sky-500/10 blur-[100px] pointer-events-none"></div>

        <div class="text-center mb-8 relative">
            <span class="px-4 py-1.5 rounded-full text-xs font-semibold glass border-emerald-500/30 text-emerald-400 tracking-wider uppercase">Hak Suara Aktif</span>
            <h1 class="text-3xl font-extrabold mt-4 mb-2 gradient-text">E-Voting Muscab</h1>
            <p class="text-slate-400 text-sm">Pemilihan Ketua DPC HPI Pesisir Selatan 2026</p>
        </div>

        @if(session('error'))
            <div class="p-4 mb-6 bg-red-500/20 border border-red-500/30 rounded-xl text-red-300 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('vanguard.voting.submit') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Candidate selection -->
            <div>
                <label class="block text-sm font-semibold text-slate-300 mb-3">Daftar Calon Ketua Formatur</label>
                <div class="space-y-3">
                    @foreach($candidates as $id => $name)
                        <label class="relative flex items-center p-5 rounded-2xl glass cursor-pointer hover:border-sky-500/40 transition">
                            <input type="radio" name="candidate" value="{{ $id }}" class="absolute right-4 text-sky-500 focus:ring-0" required>
                            <div>
                                <span class="font-bold text-slate-200 block">Calon #{{ $id }}</span>
                                <span class="text-sm text-slate-300">{{ $name }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="p-4 glass rounded-2xl text-xs text-slate-400 space-y-1">
                <p class="font-semibold text-sky-400">🛡️ Jaminan Keamanan Sistem:</p>
                <p>Suara Anda dienkripsi secara asimetris menggunakan algoritma **AES-256** dan ditandatangani secara digital dengan **SHA-256** untuk menjamin kerahasiaan pilihan dan integritas rekapitulasi data suara.</p>
            </div>

            <div class="flex gap-4">
                <a href="{{ route('vanguard.dashboard') }}" class="w-1/3 py-4 text-center rounded-2xl glass text-slate-300 font-bold hover:bg-slate-800 transition">
                    Kembali
                </a>
                <button type="submit" class="w-2/3 py-4 rounded-2xl text-white font-bold bg-gradient-to-r from-sky-400 to-indigo-500 hover:opacity-90 transition shadow-lg shadow-sky-500/10">
                    Kirim Pilihan Suara
                </button>
            </div>
        </form>
    </div>
</body>
</html>
