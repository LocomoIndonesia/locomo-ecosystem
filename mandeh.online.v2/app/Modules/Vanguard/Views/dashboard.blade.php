<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Vanguard - HPI Pessel 2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top right, #0d1226 0%, #06080f 100%);
        }
        .glass {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px rgba(255, 255, 255, 0.06) solid;
        }
        .gradient-text {
            background: linear-gradient(135deg, #e2e8f0 0%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="text-slate-100 min-h-screen p-4 md:p-8" x-data="globalState()">
    <div class="max-w-6xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="glass rounded-3xl p-6 md:p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden">
            <div class="absolute -top-40 -left-40 w-80 h-80 rounded-full bg-indigo-500/10 blur-[100px] pointer-events-none"></div>
            
            <div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold glass border-sky-500/30 text-sky-400 uppercase tracking-wider">
                    Armada {{ $participant->armada == 'darat_laut' ? 'Darat & Laut (Fisik)' : 'Udara (Siber)' }}
                </span>
                <h1 class="text-3xl font-extrabold mt-3 mb-1 text-slate-200">{{ $participant->name }}</h1>
                <p class="text-slate-400 text-sm">Tracking ID: <span class="text-sky-400 font-mono font-bold">{{ $participant->tracking_id }}</span> ({{ ucfirst($participant->origin) }})</p>
                @if($participant->is_inaugurated)
                    <span class="mt-2 inline-block px-3 py-1 rounded-lg text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        🌟 PENGURUS RESMI HPI
                    </span>
                @endif
            </div>
            
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('business.upline') }}" class="px-5 py-3 rounded-2xl glass border-emerald-400/20 text-emerald-300 font-semibold text-sm hover:bg-emerald-500/10 transition flex items-center gap-2">
                    💼 Bisnis Upline & Referral
                </a>
                <a href="{{ route('vanguard.tasks') }}" class="px-5 py-3 rounded-2xl glass border-indigo-400/20 text-indigo-300 font-semibold text-sm hover:bg-indigo-500/10 transition flex items-center gap-2">
                    📋 Daftar Tugas
                </a>
                @if($participant->is_inaugurated)
                    <a href="{{ route('vanguard.supervision') }}" class="px-5 py-3 rounded-2xl glass border-emerald-400/20 text-emerald-300 font-semibold text-sm hover:bg-emerald-500/10 transition flex items-center gap-2">
                        🛡️ Laporan Pengawasan
                    </a>
                @endif
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-5 py-3 rounded-2xl glass border-red-500/20 text-red-400 font-semibold text-sm hover:bg-red-500/10 transition">
                        Keluar
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-400 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-2xl text-red-400 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-2xl text-red-400 text-sm">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(!auth()->user()->is_verified)
            <div class="p-6 bg-amber-500/10 border border-amber-500/20 rounded-3xl flex flex-col md:flex-row items-start md:items-center gap-4 justify-between relative overflow-hidden mb-6">
                <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-amber-500/5 blur-[50px] pointer-events-none"></div>
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-amber-400 text-3xl mt-0.5">lock_open</span>
                    <div>
                        <h4 class="text-amber-400 font-bold text-base">STATUS BERKAS: DALAM PENINJAUAN ADMIN</h4>
                        <p class="text-slate-300 text-sm mt-1 leading-relaxed">
                            Pendaftaran Anda sebagai Vanguard HPI sedang dalam proses verifikasi. Akun Anda saat ini berjalan dalam batasan <strong>Guest Viewer</strong>. Fitur presensi harian, pengerjaan misi, dan voting muscab ditangguhkan sementara.
                        </p>
                    </div>
                </div>
                <span class="px-3.5 py-1.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 uppercase tracking-widest self-start md:self-center shrink-0">
                    Guest Viewer
                </span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Left Panel: Gamification Profile -->
            <div class="glass rounded-3xl p-6 flex flex-col justify-between relative overflow-hidden gap-6">
                <div class="absolute -bottom-40 -right-40 w-60 h-60 rounded-full bg-sky-500/10 blur-[80px] pointer-events-none"></div>
                
                <div>
                    <h2 class="text-lg font-bold mb-4 text-slate-300">Level & Reputasi</h2>
                    <div class="my-6 text-center">
                        <div class="text-xs text-sky-400 uppercase tracking-widest font-bold">Vanguard Level</div>
                        <div class="text-7xl font-extrabold text-sky-400 tracking-tight my-2">
                            {{ floor($participant->reputation_points / 50) + 1 }}
                        </div>
                        <div class="text-xs text-slate-400">Total: {{ $participant->reputation_points }} Poin</div>
                    </div>
                </div>

                <!-- Badges Showcase -->
                <div class="border-t border-slate-800 pt-4">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Lencana Penghargaan (Badges)</h3>
                    <div class="flex flex-wrap gap-2">
                        @if($participant->reputation_points >= 10)
                            <span class="px-2.5 py-1 bg-sky-500/10 border border-sky-500/20 rounded-lg text-[10px] text-sky-400 font-bold uppercase tracking-wider">⚓ Kader Pertama</span>
                        @endif
                        @if($participant->reputation_points >= 40)
                            <span class="px-2.5 py-1 bg-indigo-500/10 border border-indigo-500/20 rounded-lg text-[10px] text-indigo-400 font-bold uppercase tracking-wider">🔥 Aktif Lapangan</span>
                        @endif
                        @if($participant->is_vanguard_graduated)
                            <span class="px-2.5 py-1 bg-emerald-500/10 border border-emerald-500/20 rounded-lg text-[10px] text-emerald-400 font-bold uppercase tracking-wider">🎓 Lulusan Terbaik</span>
                        @endif
                        @if($participant->is_inaugurated)
                            <span class="px-2.5 py-1 bg-amber-500/10 border border-amber-500/20 rounded-lg text-[10px] text-amber-400 font-bold uppercase tracking-wider">🌟 Pengurus HPI</span>
                        @endif
                    </div>
                </div>

                <div class="space-y-4 border-t border-slate-800 pt-4">
                    <div>
                        <div class="flex justify-between text-xs text-slate-400 mb-1">
                            <span>Passing Grade Kelulusan LDK</span>
                            <span class="font-bold text-slate-200">{{ $participant->reputation_points }}/80 Poin</span>
                        </div>
                        <div class="w-full bg-slate-800/80 rounded-full h-2">
                            <div class="bg-gradient-to-r from-sky-400 to-indigo-500 h-2 rounded-full" style="width: {{ min(100, ($participant->reputation_points / 80) * 100) }}%"></div>
                        </div>
                    </div>

                    @if($participant->is_vanguard_graduated)
                        <div class="p-3.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-center">
                            <span class="text-emerald-400 font-bold text-sm">✅ LULUS LDK (Hak Suara Muscab Aktif)</span>
                        </div>
                    @else
                        <div class="p-3.5 bg-amber-500/10 border border-amber-500/20 rounded-xl text-center">
                            <span class="text-amber-400 font-bold text-xs">⚠️ Belum Lulus (Butuh {{ max(0, 80 - $participant->reputation_points) }} Poin lagi)</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Middle Panel: Attendance & Missions -->
            <div class="md:col-span-2 space-y-6">

                <!-- 💻 GENERATIVE UI COMMAND CONSOLE -->
                <div class="glass rounded-3xl p-6 relative">
                    <h2 class="text-lg font-bold mb-3 text-slate-300 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sky-400 text-xl font-bold">terminal</span>
                        Generative UI Command Console
                    </h2>
                    <p class="text-xs text-slate-400 mb-4">Ketik perintah singkat untuk memicu UI (e.g. /help, /destinasi, /list, /status, /show [slug])</p>
                    
                    <form @submit.prevent="executeCommand()" class="relative flex items-center">
                        <span class="absolute left-4 text-sky-400 font-mono font-bold">&gt;</span>
                        <input type="text" x-model="commandInput" placeholder="Ketik perintah..."
                               :disabled="!{{ auth()->user()->is_verified ? 'true' : 'false' }}"
                               class="w-full bg-slate-950/80 border border-slate-750/80 rounded-2xl pl-8 pr-16 py-3.5 text-sky-300 font-mono text-sm focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 disabled:opacity-40 disabled:cursor-not-allowed">
                        <button type="submit" :disabled="!{{ auth()->user()->is_verified ? 'true' : 'false' }}"
                                class="absolute right-2 px-4 py-2 bg-sky-500/10 hover:bg-sky-500/20 text-sky-400 font-bold font-mono text-xs rounded-xl border border-sky-500/30 transition disabled:opacity-30">
                            RUN
                        </button>
                    </form>
                    
                    @if(!auth()->user()->is_verified)
                        <div class="absolute inset-0 bg-slate-950/85 backdrop-blur-[1.5px] rounded-3xl flex items-center justify-center p-6 text-center">
                            <div class="space-y-2 max-w-sm">
                                <span class="material-symbols-outlined text-amber-500 text-3xl">lock</span>
                                <h4 class="font-bold text-amber-400 text-sm">COMMAND CONSOLE DIKUNCI</h4>
                                <p class="text-xs text-slate-400">Terminal dinamis hanya tersedia bagi Agen Terverifikasi.</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- 🎴 DYNAMIC GENERATIVE CARD CONTAINER -->
                <div x-show="generatedCard" x-transition class="glass rounded-3xl p-6 border border-sky-500/20 relative" x-cloak>
                    <!-- Close button -->
                    <button @click="generatedCard = null" class="absolute top-4 right-4 text-slate-400 hover:text-slate-200">
                        <span class="material-symbols-outlined">close</span>
                    </button>

                    <!-- Card Header -->
                    <div class="mb-4">
                        <span class="px-2.5 py-0.5 rounded bg-sky-500/10 text-sky-400 text-[10px] font-mono uppercase tracking-wider font-bold">Generative UI Response Card</span>
                    </div>

                    <!-- help -->
                    <div x-show="generatedCard === 'help'">
                        <h3 class="text-lg font-bold text-slate-200 mb-2">📋 Daftar Perintah Konsol</h3>
                        <div class="space-y-2 text-xs font-mono">
                            <div class="p-3 bg-slate-950/40 rounded-xl border border-slate-900 flex justify-between items-center">
                                <span class="text-sky-400">/help</span>
                                <span class="text-slate-400">Menampilkan bantuan perintah ini</span>
                            </div>
                            <div class="p-3 bg-slate-950/40 rounded-xl border border-slate-900 flex justify-between items-center">
                                <span class="text-sky-400">/destinasi</span>
                                <span class="text-slate-400">Generate Form Tambah Destinasi (Khusus Pengurus HPI)</span>
                            </div>
                            <div class="p-3 bg-slate-950/40 rounded-xl border border-slate-900 flex justify-between items-center">
                                <span class="text-sky-400">/list</span>
                                <span class="text-slate-400">Generate Daftar Destinasi Wisata Mandeh</span>
                            </div>
                            <div class="p-3 bg-slate-950/40 rounded-xl border border-slate-900 flex justify-between items-center">
                                <span class="text-sky-400">/status</span>
                                <span class="text-slate-400">Generate Laporan Reputasi & Integritas Agen</span>
                            </div>
                            <div class="p-3 bg-slate-950/40 rounded-xl border border-slate-900 flex justify-between items-center">
                                <span class="text-sky-400">/show [slug]</span>
                                <span class="text-slate-400">Generate detail destinasi berdasarkan slug</span>
                            </div>
                        </div>
                    </div>

                    <!-- create_destination -->
                    <div x-show="generatedCard === 'create_destination'">
                        @if(auth()->user()->role === 'layanan_publik' || auth()->user()->role === 'admin' || auth()->user()->role === 'admin_manager')
                            <h3 class="text-lg font-bold text-slate-200 mb-3">📍 Tambah Destinasi Wisata Baru</h3>
                            <form action="{{ route('destinasi.store') }}" method="POST" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nama Destinasi</label>
                                        <input type="text" name="name" required placeholder="Pulau Kapo-Kapo"
                                               class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl p-3 focus:outline-none focus:border-sky-500">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Rentang Biaya</label>
                                        <input type="text" name="price_range" placeholder="Free / Rp 15.000"
                                               class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl p-3 focus:outline-none focus:border-sky-500">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Latitude</label>
                                        <input type="number" step="any" name="lat" required placeholder="-1.2224"
                                               class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl p-3 focus:outline-none focus:border-sky-500">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Longitude</label>
                                        <input type="number" step="any" name="lng" required placeholder="100.4144"
                                               class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl p-3 focus:outline-none focus:border-sky-500">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Deskripsi</label>
                                    <textarea name="description" rows="3" required placeholder="Tuliskan info destinasi..."
                                              class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl p-3 focus:outline-none focus:border-sky-500"></textarea>
                                </div>
                                <button type="submit" class="w-full py-3 bg-gradient-to-r from-sky-400 to-indigo-500 text-white font-bold rounded-xl text-xs shadow-premium">
                                    Simpan Destinasi
                                </button>
                            </form>
                        @else
                            <div class="text-center p-6 space-y-2">
                                <span class="material-symbols-outlined text-red-400 text-4xl">gpp_maybe</span>
                                <h3 class="text-red-400 font-bold text-base">AKSES DITOLAK</h3>
                                <p class="text-xs text-slate-400 leading-relaxed font-body-md">
                                    Pembuatan destinasi baru hanya diperbolehkan untuk <strong>Pengurus DPC HPI</strong> (Pelantikan Vanguard resmi).
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- list_destinations -->
                    <div x-show="generatedCard === 'list_destinations'">
                        <h3 class="text-lg font-bold text-slate-200 mb-3">🏝️ Destinasi Pariwisata Mandeh</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-80 overflow-y-auto pr-1">
                            <template x-for="dest in localDestinations" :key="dest.id">
                                <div class="p-3 bg-slate-950/40 rounded-xl border border-slate-900 hover:border-sky-500/30 transition cursor-pointer"
                                     @click="showDestDetails(dest)">
                                    <h4 class="font-bold text-sky-400 text-xs" x-text="dest.name"></h4>
                                    <p class="text-slate-400 text-[10px] mt-1 line-clamp-2" x-text="dest.description"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- status_report -->
                    <div x-show="generatedCard === 'status_report'">
                        <h3 class="text-lg font-bold text-slate-200 mb-2">📊 Status Kredibilitas & Integritas Vanguard</h3>
                        <div class="space-y-2.5 text-xs">
                            <div class="flex justify-between p-3 bg-slate-950/30 rounded-xl border border-slate-900/60">
                                <span class="text-slate-400">Skor Integritas (Trust Score):</span>
                                <span class="font-bold text-sky-400">{{ auth()->user()->trust_score }} / 100</span>
                            </div>
                            <div class="flex justify-between p-3 bg-slate-950/30 rounded-xl border border-slate-900/60">
                                <span class="text-slate-400">Poin Reputasi LDK HPI:</span>
                                <span class="font-bold text-slate-200">{{ $participant->reputation_points }} XP</span>
                            </div>
                        </div>
                    </div>

                    <!-- detail_business -->
                    <div x-show="generatedCard === 'detail_business' && activeDestDetails">
                        <h3 class="text-xl font-bold text-sky-400" x-text="activeDestDetails.name"></h3>
                        <p class="text-slate-300 text-xs leading-relaxed my-3" x-text="activeDestDetails.description"></p>
                        <div class="text-[10px] text-slate-500 border-t border-slate-800/60 pt-2 flex justify-between">
                            <span x-text="'Koordinat: ' + activeDestDetails.lat + ', ' + activeDestDetails.lng"></span>
                            <span x-text="'Biaya: ' + (activeDestDetails.price_range || 'Free')"></span>
                        </div>
                    </div>

                    <!-- unknown -->
                    <div x-show="generatedCard === 'unknown'" class="text-center py-4">
                        <span class="material-symbols-outlined text-red-500 text-3xl">error</span>
                        <h4 class="font-bold text-red-400 text-sm mt-2">PERINTAH TIDAK DIKENAL</h4>
                        <p class="text-xs text-slate-400">Ketik <span class="text-sky-400 font-mono">/help</span> untuk melihat daftar perintah.</p>
                    </div>
                </div>

                <!-- Daily Check-in Card (with Geo verification for Darat & Laut) -->
                <div class="glass rounded-3xl p-6 relative" x-data="geoCheckin()">
                    <h2 class="text-lg font-bold mb-4 text-slate-300">Presensi Harian O2O</h2>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex-1">
                            <p class="text-slate-400 text-sm">Catat kehadiran Anda hari ini. Armada Darat & Laut diverifikasi dalam radius 100m dari Gedung TIC Mandeh.</p>
                            <span class="text-xs text-sky-400 mt-1 block">Mendapatkan +10 Poin Reputasi</span>
                            
                            @if($participant->armada == 'darat_laut')
                                <template x-if="latitude && longitude">
                                    <div class="text-[10px] text-slate-400 mt-2">
                                        Lokasi Terdeteksi: <span class="text-emerald-400" x-text="latitude + ', ' + longitude"></span>
                                    </div>
                                </template>
                            @endif
                        </div>
                        
                        @if($checkedInToday)
                            <button class="px-6 py-3 bg-emerald-500/10 text-emerald-400 font-bold rounded-2xl cursor-not-allowed border border-emerald-500/20 shrink-0" disabled>
                                Sudah Check-in Hari Ini
                            </button>
                        @else
                            <form action="{{ route('vanguard.checkin') }}" method="POST" class="flex flex-col gap-2 w-full sm:w-auto" @submit="onSubmit($event)">
                                @csrf
                                <input type="hidden" name="gps_lat" x-model="latitude">
                                <input type="hidden" name="gps_lng" x-model="longitude">
                                
                                <div class="flex gap-2">
                                    <select name="method" class="bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-2xl p-3 focus:outline-none focus:border-sky-500">
                                        @if($participant->armada == 'darat_laut')
                                            <option value="face_to_face">Tatap Muka (Gedung TIC)</option>
                                        @else
                                            <option value="livestream">Siaran Langsung / Virtual</option>
                                        @endif
                                    </select>
                                    <button type="submit" class="px-6 py-3 bg-sky-500 hover:bg-sky-600 text-white font-bold rounded-2xl transition shadow-premium">
                                        Check-in
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                    @if(!auth()->user()->is_verified)
                        <div class="absolute inset-0 bg-slate-950/85 backdrop-blur-[1px] rounded-3xl flex items-center justify-center p-6 text-center">
                            <div class="space-y-1">
                                <span class="material-symbols-outlined text-amber-500 text-2xl font-bold">lock</span>
                                <h4 class="font-bold text-amber-400 text-xs">PRESENSI DIKUNCI</h4>
                                <p class="text-[10px] text-slate-400">Fitur ini tersedia setelah akun Anda diverifikasi.</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Papan Misi & Kegiatan -->
                <div class="glass rounded-3xl p-6 relative">
                    <h2 class="text-lg font-bold mb-4 text-slate-300">Papan Misi & Kegiatan</h2>
                    <div class="space-y-3 max-h-60 overflow-y-auto pr-2">
                        @forelse($missions as $mission)
                            <div class="p-3.5 glass rounded-xl border border-slate-800 space-y-2">
                                <div class="flex justify-between items-start gap-2">
                                    <span class="font-bold text-slate-200 text-sm">{{ $mission->title }}</span>
                                    <span class="text-[9px] font-mono px-2 py-0.5 rounded
                                        @if($mission->is_active) bg-emerald-500/10 text-emerald-400
                                        @elseif($mission->not_started) bg-sky-500/10 text-sky-400
                                        @else bg-slate-800 text-slate-500 @endif">
                                        @if($mission->is_active) Aktif
                                        @elseif($mission->not_started) Scheduled
                                        @else Berakhir @endif
                                    </span>
                                </div>
                                <p class="text-slate-400 text-xs">{{ $mission->description }}</p>
                                
                                @if($mission->not_started)
                                    <div class="p-2 bg-slate-900/50 rounded-lg flex items-center justify-between text-slate-400 text-[10px]" x-data="countdown('{{ $mission->start_time->toIso8601String() }}')" x-init="start()">
                                        <span class="font-semibold">Mulai Dalam:</span>
                                        <span class="font-mono font-bold text-sky-400" x-text="timeString">Menghitung...</span>
                                    </div>
                                @elseif($mission->is_active && $mission->end_time)
                                    <div class="text-[9px] text-slate-500">
                                        Berakhir: {{ $mission->end_time->format('d M H:i') }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm text-center py-4">Belum ada misi yang ditugaskan.</p>
                        @endforelse
                    </div>
                    @if(!auth()->user()->is_verified)
                        <div class="absolute inset-0 bg-slate-950/85 backdrop-blur-[1px] rounded-3xl flex items-center justify-center p-6 text-center">
                            <div class="space-y-1">
                                <span class="material-symbols-outlined text-amber-500 text-2xl font-bold">lock</span>
                                <h4 class="font-bold text-amber-400 text-xs">PAPAN MISI DIKUNCI</h4>
                                <p class="text-[10px] text-slate-400">Misi dan kegiatan hanya tersedia bagi Agen Terverifikasi.</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Kirim Laporan Misi Aktif -->
                @php
                    $activeMissions = $missions->filter(fn($m) => $m->is_active);
                @endphp
                @if($activeMissions->count() > 0)
                    <div class="glass rounded-3xl p-6 relative">
                        <h2 class="text-lg font-bold mb-4 text-slate-300">Kirim Laporan Misi</h2>
                        <form action="{{ route('vanguard.mission.submit') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-1">Pilih Misi Aktif</label>
                                <select name="mission_id" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-2xl p-3 focus:outline-none focus:border-sky-500">
                                    @foreach($activeMissions as $mission)
                                        <option value="{{ $mission->id }}">{{ $mission->title }} (+{{ $mission->points_reward }} Poin)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-1">Laporan Pelaksanaan</label>
                                <textarea name="report_text" rows="3" placeholder="Tuliskan detail pekerjaan atau simulasi yang telah Anda lakukan..." class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-2xl p-3 focus:outline-none focus:border-sky-500" required></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-1">Link Bukti (Opsional)</label>
                                <input type="url" name="proof_url" placeholder="https://instagram.com/p/... atau link dokumentasi" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-2xl p-3 focus:outline-none focus:border-sky-500">
                            </div>
                            <button type="submit" class="w-full py-3 bg-gradient-to-r from-sky-400 to-indigo-500 text-white font-bold rounded-2xl hover:opacity-90 transition shadow-premium">
                                Kirim & Validasi Laporan
                            </button>
                        </form>
                        @if(!auth()->user()->is_verified)
                            <div class="absolute inset-0 bg-slate-950/85 backdrop-blur-[1px] rounded-3xl flex items-center justify-center p-6 text-center">
                                <div class="space-y-1">
                                    <span class="material-symbols-outlined text-amber-500 text-2xl font-bold">lock</span>
                                    <h4 class="font-bold text-amber-400 text-xs">PENGIRIMAN LAPORAN DIKUNCI</h4>
                                    <p class="text-[10px] text-slate-400">Gunakan kredensial terverifikasi untuk menyerahkan bukti misi.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Surveys & targeted digital voting (Smart-Citizen style countdown) -->
        <div class="glass rounded-3xl p-6 relative">
            <h2 class="text-xl font-bold mb-4 text-slate-300">🗳️ Jajak Pendapat & Pemilihan Muscab</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($surveys as $survey)
                    <div class="p-5 glass rounded-2xl border border-slate-800 flex flex-col justify-between gap-4">
                        <div>
                            <div class="flex justify-between items-start gap-2">
                                <h3 class="text-lg font-bold text-slate-200">{{ $survey->title }}</h3>
                                <span class="text-[9px] font-mono px-2 py-0.5 rounded
                                    @if($survey->is_ended) bg-red-500/10 text-red-400
                                    @elseif($survey->is_active) bg-emerald-500/10 text-emerald-400
                                    @else bg-sky-500/10 text-sky-400 @endif">
                                    @if($survey->is_ended) Berakhir
                                    @elseif($survey->is_active) Aktif
                                    @else Scheduled @endif
                                </span>
                            </div>
                            <p class="text-slate-400 text-sm mt-1">{{ $survey->description }}</p>
                            
                            <!-- target roles badges -->
                            <div class="mt-2 flex gap-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-500/10 text-sky-400 uppercase">Target: {{ implode(', ', $survey->target_roles ?? []) }}</span>
                            </div>
                        </div>

                        <!-- Turnout rate display (users can see it!) -->
                        <div class="p-3 bg-slate-950/40 rounded-xl border border-slate-900/60">
                            <div class="flex justify-between text-[10px] text-slate-400 font-semibold mb-1">
                                <span>Turnout Partisipasi: {{ $survey->participation_rate }}%</span>
                                <span>{{ $survey->voted_count }} / {{ $survey->eligible_count }} Pemilih</span>
                            </div>
                            <div class="w-full bg-slate-800 rounded-full h-1.5">
                                <div class="bg-gradient-to-r from-sky-400 to-indigo-500 h-1.5 rounded-full" style="width: {{ $survey->participation_rate }}%"></div>
                            </div>

                            <!-- Real-time temporary results chart -->
                            <div class="mt-3 space-y-2 border-t border-slate-900 pt-2">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Perolehan Suara Sementara:</label>
                                @foreach($survey->options ?? [] as $opt)
                                    @php
                                        $title = $opt['title'] ?? '';
                                        $count = $survey->vote_results[$title] ?? 0;
                                        $pct = $survey->voted_count > 0 ? round(($count / $survey->voted_count) * 100, 1) : 0;
                                    @endphp
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-[10px]">
                                            <span class="text-slate-300 font-medium">{{ $title }}</span>
                                            <span class="text-slate-400 font-mono">{{ $count }} suara ({{ $pct }}%)</span>
                                        </div>
                                        <div class="w-full bg-slate-900 rounded-full h-1">
                                            <div class="bg-sky-500 h-1 rounded-full" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- state check -->
                        @if($survey->not_started)
                            <div class="p-3 bg-slate-800/50 rounded-xl flex items-center justify-between text-slate-400" x-data="countdown('{{ $survey->start_time->toIso8601String() }}')" x-init="start()">
                                <span class="text-xs font-semibold">Mulai Dalam:</span>
                                <span class="text-xs font-mono font-bold text-sky-400" x-text="timeString">Menghitung...</span>
                            </div>
                        @elseif($survey->is_active)
                            @if(!$participant->is_vanguard_graduated)
                                <div class="p-3 bg-red-500/10 border border-red-500/20 rounded-xl text-center">
                                    <span class="text-red-400 text-xs font-bold">🔒 HAK SUARA DITUTUP (BUTUH LULUS LDK)</span>
                                </div>
                            @else
                                <form action="{{ route('vanguard.voting.submit') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="survey_id" value="{{ $survey->id }}">
                                    <div class="space-y-2">
                                        <label class="block text-xs font-semibold text-slate-400">Pilih Opsi/Kandidat Anda:</label>
                                        <div class="space-y-2">
                                            @foreach($survey->options ?? [] as $opt)
                                                @php
                                                    $title = $opt['title'] ?? '';
                                                    $description = $opt['description'] ?? '';
                                                    $profileUrl = $opt['profile_url'] ?? '';
                                                    $linkText = $opt['link_text'] ?? 'Lihat Bio';
                                                    $isSelected = $survey->user_choice === $title;
                                                @endphp
                                                <label class="relative flex items-center p-3.5 rounded-xl border {{ $isSelected ? 'border-sky-500/40 bg-sky-500/5' : 'border-slate-800 bg-slate-900/40' }} cursor-pointer hover:border-slate-700 transition">
                                                    <input type="radio" name="choice" value="{{ $title }}" {{ $isSelected ? 'checked' : '' }} class="absolute right-4 text-sky-500 focus:ring-0" required>
                                                    <div class="pr-8">
                                                        <span class="font-bold text-xs text-slate-200 block">{{ $title }}</span>
                                                        @if($description)
                                                            <span class="text-[10px] text-slate-400 block mt-0.5">{{ $description }}</span>
                                                        @endif
                                                        @if($profileUrl)
                                                            <a href="{{ $profileUrl }}" target="_blank" class="text-[9px] text-sky-400 font-semibold hover:underline mt-1 inline-block">
                                                                🔗 {{ $linkText }}
                                                            </a>
                                                        @endif
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-sky-400 to-indigo-500 text-white font-bold rounded-xl hover:opacity-90 transition shadow-premium text-xs">
                                        {{ $survey->has_voted ? 'Ganti Pilihan Suara Anda' : 'Kirim Pilihan Suara' }}
                                    </button>
                                </form>
                            @endif
                        @elseif($survey->is_ended)
                            <div class="p-3 bg-slate-800/75 rounded-xl text-center text-slate-500 text-xs font-bold uppercase">
                                ❌ PEMILIHAN TELAH BERAKHIR (TERKUNCI)
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="col-span-2 text-center py-6 text-slate-500 text-sm">
                        Tidak ada program voting/survey yang aktif saat ini.
                    </div>
                @endforelse
            </div>
            @if(!auth()->user()->is_verified)
                <div class="absolute inset-0 bg-slate-950/85 backdrop-blur-[1px] rounded-[32px] flex items-center justify-center p-6 text-center">
                    <div class="space-y-1">
                        <span class="material-symbols-outlined text-amber-500 text-3xl font-bold">lock</span>
                        <h4 class="font-bold text-amber-400 text-sm">VOTING & SURVEY DIKUNCI</h4>
                        <p class="text-xs text-slate-400">Partisipasi jajak pendapat dinonaktifkan sementara dalam mode Guest Viewer.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- History Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Check-in Log -->
            <div class="glass rounded-3xl p-6">
                <h2 class="text-lg font-bold mb-4 text-slate-300">Riwayat Check-in</h2>
                <div class="space-y-3 max-h-60 overflow-y-auto pr-2">
                    @forelse($checkins as $checkin)
                        <div class="flex justify-between items-center p-3.5 glass rounded-xl text-sm">
                            <span class="text-slate-300">{{ $checkin->checkin_date->format('d M Y') }}</span>
                            <span class="text-xs font-mono bg-sky-500/10 text-sky-400 px-2.5 py-1 rounded-full">
                                {{ $checkin->verified_method == 'face_to_face' ? 'Tatap Muka' : 'Live Stream' }}
                            </span>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm text-center py-4">Belum ada riwayat check-in.</p>
                    @endforelse
                </div>
            </div>

            <!-- Mission Reports Log -->
            <div class="glass rounded-3xl p-6">
                <h2 class="text-lg font-bold mb-4 text-slate-300">Riwayat Misi</h2>
                <div class="space-y-3 max-h-60 overflow-y-auto pr-2">
                    @forelse($reports as $report)
                        <div class="p-3.5 glass rounded-xl space-y-2">
                            <div class="flex justify-between items-start">
                                <span class="font-bold text-slate-200 text-sm">{{ $report->mission->title }}</span>
                                <span class="text-xs font-bold bg-emerald-500/15 text-emerald-400 px-2.5 py-0.5 rounded-full">
                                    +{{ $report->points_awarded }} Poin
                                </span>
                            </div>
                            <p class="text-slate-400 text-xs line-clamp-2">{{ $report->report_text }}</p>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm text-center py-4">Belum ada laporan misi yang dikirim.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        function globalState() {
            return {
                commandInput: '',
                generatedCard: null,
                unknownCommand: '',
                activeDestDetails: null,
                localDestinations: @json($destinations),
                executeCommand() {
                    const cmd = this.commandInput.trim();
                    this.commandInput = '';
                    if (!cmd) return;

                    const cleaned = cmd.toLowerCase();
                    if (cleaned === '/help' || cleaned === 'help' || cleaned === 'h') {
                        this.generatedCard = 'help';
                    } else if (cleaned === '/destinasi' || cleaned === 'destinasi' || cleaned === 'create destination') {
                        this.generatedCard = 'create_destination';
                    } else if (cleaned === '/list' || cleaned === 'list' || cleaned === 'list destinasi') {
                        this.generatedCard = 'list_destinations';
                    } else if (cleaned === '/status' || cleaned === 'status') {
                        this.generatedCard = 'status_report';
                    } else if (cleaned.startsWith('/show ') || cleaned.startsWith('show ')) {
                        const slug = cmd.substring(cleaned.startsWith('/show ') ? 6 : 5).trim();
                        const found = this.localDestinations.find(d => d.slug.toLowerCase() === slug.toLowerCase() || d.slug.toLowerCase().includes(slug.toLowerCase()));
                        if (found) {
                            this.activeDestDetails = found;
                            this.generatedCard = 'detail_business';
                        } else {
                            this.generatedCard = 'unknown';
                            this.unknownCommand = cmd;
                        }
                    } else {
                        this.generatedCard = 'unknown';
                        this.unknownCommand = cmd;
                    }
                },
                showDestDetails(dest) {
                    this.activeDestDetails = dest;
                    this.generatedCard = 'detail_business';
                }
            };
        }

        function geoCheckin() {
            return {
                latitude: null,
                longitude: null,
                isRequesting: false,
                init() {
                    // Automatically request geo location if Armada Darat & Laut
                    @if($participant->armada == 'darat_laut')
                        this.isRequesting = true;
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(
                                (position) => {
                                    this.latitude = position.coords.latitude;
                                    this.longitude = position.coords.longitude;
                                    this.isRequesting = false;
                                },
                                (err) => {
                                    console.error(err);
                                    alert("Presensi Darat & Laut butuh izin lokasi. Silahkan berikan izin GPS.");
                                    this.isRequesting = false;
                                }
                            );
                        } else {
                            alert("Geolocation tidak didukung oleh browser Anda.");
                            this.isRequesting = false;
                        }
                    @endif
                },
                onSubmit(e) {
                    @if($participant->armada == 'darat_laut')
                        if (!this.latitude || !this.longitude) {
                            e.preventDefault();
                            alert("Tunggu hingga koordinat lokasi GPS Anda terdeteksi!");
                        }
                    @endif
                }
            };
        }

        function countdown(targetTime) {
            return {
                target: new Date(targetTime).getTime(),
                timeString: '',
                timer: null,
                start() {
                    this.update();
                    this.timer = setInterval(() => this.update(), 1000);
                },
                update() {
                    let now = new Date().getTime();
                    let dist = this.target - now;
                    if (dist <= 0) {
                        this.timeString = 'Dimulai / Refresh Halaman';
                        clearInterval(this.timer);
                        return;
                    }
                    let days = Math.floor(dist / (1000 * 60 * 60 * 24));
                    let hours = Math.floor((dist % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    let minutes = Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60));
                    let seconds = Math.floor((dist % (1000 * 60)) / 1000);
                    
                    let parts = [];
                    if (days > 0) parts.push(`${days}h`);
                    if (hours > 0 || days > 0) parts.push(`${hours}j`);
                    parts.push(`${minutes}m`);
                    parts.push(`${seconds}d`);
                    
                    this.timeString = parts.join(' ');
                }
            }
        }
    </script>
</body>
</html>
