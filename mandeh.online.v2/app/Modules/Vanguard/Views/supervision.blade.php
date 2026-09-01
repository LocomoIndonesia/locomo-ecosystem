<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pengawasan - HPI Pessel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top right, #0a1122 0%, #03060a 100%);
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
        .shadow-premium {
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.1);
        }
    </style>
</head>
<body class="text-slate-100 min-h-screen p-4 md:p-8">
    <div class="max-w-5xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="glass rounded-3xl p-6 md:p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative overflow-hidden">
            <div class="absolute -top-40 -left-40 w-80 h-80 rounded-full bg-emerald-500/10 pointer-events-none blur-[100px]"></div>
            <div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold glass border-emerald-500/30 text-emerald-400 uppercase tracking-wider">
                    Official HPI Supervision Page
                </span>
                <h1 class="text-3xl font-extrabold mt-3 mb-1 gradient-text">Laporan Pengawasan & Temuan</h1>
                <p class="text-slate-400 text-sm">Media pelaporan penemuan penting, kendala, atau info darurat dari lapangan</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('vanguard.tasks') }}" class="px-5 py-3 rounded-2xl glass border-slate-700 text-slate-300 font-semibold text-sm hover:bg-slate-800 transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">assignment</span>
                    Daftar Tugas
                </a>
                <a href="{{ route('vanguard.dashboard') }}" class="px-5 py-3 rounded-2xl glass border-slate-700 text-slate-300 font-semibold text-sm hover:bg-slate-800 transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">dashboard</span>
                    Dashboard
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-400 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column: Add Report Form -->
            <div class="glass rounded-3xl p-6 h-fit relative">
                <h2 class="text-lg font-bold mb-4 text-slate-300 flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-400">report_problem</span>
                    Buat Laporan Temuan
                </h2>
                <form action="{{ route('vanguard.supervision.submit') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase">Judul Temuan / Kejadian</label>
                        <input type="text" name="title" placeholder="Contoh: Kerusakan Fasilitas / Pencemaran" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-xl p-3 focus:outline-none focus:border-emerald-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase">Nama Pelapor / Divisi</label>
                        <input type="text" name="reporter_name" placeholder="Contoh: Randi (Armada Darat)" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-xl p-3 focus:outline-none focus:border-emerald-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase">Deskripsi Detail Temuan</label>
                        <textarea name="description" rows="4" placeholder="Jelaskan secara rinci lokasi, jenis kejadian, dan tindakan awal yang diperlukan..." class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-xl p-3 focus:outline-none focus:border-emerald-500" required></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase">Status / Prioritas Temuan</label>
                        <select name="status" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-xl p-3 focus:outline-none focus:border-emerald-500">
                            <option value="pending">Rendah (Menunggu Tindakan)</option>
                            <option value="in_progress">Sedang (Penyelidikan / Penanganan)</option>
                            <option value="completed">Tinggi (Selesai Ditangani)</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full py-3.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl transition shadow-premium text-sm flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">send</span>
                        Kirim Laporan
                    </button>
                </form>
            </div>

            <!-- Right Column: Status Logs List -->
            <div class="lg:col-span-2 glass rounded-3xl p-6 space-y-4">
                <h2 class="text-lg font-bold text-slate-300 flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-400">playlist_add_check</span>
                    Daftar Laporan Temuan Lapangan
                </h2>
                <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2">
                    @forelse($logs as $log)
                        <div class="p-4 glass rounded-2xl border-slate-800 space-y-3">
                            <div class="flex flex-wrap justify-between items-center gap-2">
                                <h3 class="font-bold text-slate-200 text-base flex items-center gap-2">
                                    <span class="material-symbols-outlined text-slate-400 text-sm">assignment_late</span>
                                    {{ $log->title }}
                                </h3>
                                <div>
                                    @if($log->status == 'completed')
                                        <span class="text-[10px] font-bold bg-emerald-500/10 text-emerald-400 px-3 py-1 rounded-full border border-emerald-500/20">Selesai Ditangani</span>
                                    @elseif($log->status == 'in_progress')
                                        <span class="text-[10px] font-bold bg-sky-500/10 text-sky-400 px-3 py-1 rounded-full border border-sky-500/20">Dalam Penanganan</span>
                                    @else
                                        <span class="text-[10px] font-bold bg-amber-500/10 text-amber-400 px-3 py-1 rounded-full border border-amber-500/20">Menunggu Tindakan</span>
                                    @endif
                                </div>
                            </div>
                            <p class="text-slate-400 text-xs leading-relaxed">{{ $log->description }}</p>
                            <div class="flex justify-between items-center text-[10px] text-slate-500 border-t border-slate-800/40 pt-2.5">
                                <span>Dilaporkan oleh: <strong class="text-slate-400">{{ $log->reporter_name }}</strong></span>
                                <span class="font-mono">{{ $log->created_at->format('d M Y, H:i') }} WIB</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm text-center py-8">Belum ada laporan pengawasan yang terkirim.</p>
                    @endforelse
                </div>
            </div>
            
        </div>
    </div>
</body>
</html>
