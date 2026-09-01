<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelaku Wisata - Mandeh.Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
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
        .glow-green {
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.4);
            border: 1px rgba(16, 185, 129, 0.4) solid;
        }
        .glow-amber {
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.4);
            border: 1px rgba(245, 158, 11, 0.4) solid;
        }
        .gradient-text {
            background: linear-gradient(135deg, #e2e8f0 0%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .terminal-glow {
            box-shadow: inset 0 0 10px rgba(56, 189, 248, 0.05), 0 0 15px rgba(56, 189, 248, 0.05);
        }
    </style>
</head>
<body class="text-slate-100 min-h-screen p-4 md:p-8" x-data="dashboardState()">
    <div class="max-w-6xl mx-auto space-y-6">

        <!-- TOP BAR / HEADER -->
        <div class="glass rounded-3xl p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden">
            <div class="absolute -top-40 -left-40 w-80 h-80 rounded-full bg-indigo-500/10 blur-[100px] pointer-events-none"></div>
            
            <div class="flex items-center gap-4">
                <span class="material-symbols-outlined text-sky-400 text-4xl">anchor</span>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-100">mandeh.online</h1>
                    <p class="text-slate-400 text-xs">Dashboard Pelaku Wisata Lokal Terpadu</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="/" class="px-5 py-2.5 rounded-2xl glass border-slate-700/50 text-slate-300 font-semibold text-xs hover:bg-slate-800 transition">
                    🌐 Explorer Map
                </a>

                {{-- Avatar/Initials + First Name --}}
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-sky-400 to-indigo-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <span class="text-xs font-semibold text-slate-300 hidden sm:inline truncate max-w-[100px]">{{ explode(' ', $user->name)[0] }}</span>
                </div>

                {{-- Logout Button --}}
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="p-2 rounded-full text-slate-400 hover:bg-red-500/10 hover:text-red-400 transition-colors" title="Keluar">
                        <span class="material-symbols-outlined text-[20px]">logout</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- NOTIFICATION / GUEST VIEWER OVERLAY BANNER -->
        @if(!$user->is_verified)
            <div class="p-6 bg-amber-500/10 border border-amber-500/20 rounded-3xl flex flex-col md:flex-row items-start md:items-center gap-4 justify-between relative overflow-hidden">
                <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-amber-500/5 blur-[50px] pointer-events-none"></div>
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-amber-400 text-3xl mt-0.5">lock_open</span>
                    <div>
                        <h4 class="text-amber-400 font-bold text-base">STATUS BERKAS: DALAM PENINJAUAN ADMIN</h4>
                        <p class="text-slate-300 text-sm mt-1 leading-relaxed">
                            Pendaftaran Anda sebagai Pelaku Wisata sedang dalam proses verifikasi. Akun Anda saat ini berjalan dalam batasan <strong>Guest Viewer</strong>. Registrasi bisnis referral, check-in harian, dan lencana dinonaktifkan sementara.
                        </p>
                    </div>
                </div>
                <span class="px-3.5 py-1.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 uppercase tracking-widest self-start md:self-center shrink-0">
                    Guest Viewer
                </span>
            </div>
        @else
            <!-- VERIFIED BANNER -->
            <div class="p-6 bg-emerald-500/10 border border-emerald-500/20 rounded-3xl flex flex-col md:flex-row items-start md:items-center gap-4 justify-between relative overflow-hidden">
                <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-emerald-500/5 blur-[50px] pointer-events-none"></div>
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-emerald-400 text-3xl mt-0.5">verified_user</span>
                    <div>
                        <h4 class="text-emerald-400 font-bold text-base">AKSES AKTIF: PELAKU WISATA TERVERIFIKASI</h4>
                        <p class="text-slate-300 text-sm mt-1 leading-relaxed">
                            Selamat datang, Pelaku Wisata Terverifikasi. Akses penuh untuk manajemen bisnis, sistem referral kedaulatan, pencetakan QR Code, dan monitoring keluhan aktif sepenuhnya.
                        </p>
                    </div>
                </div>
                <span class="px-3.5 py-1.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase tracking-widest self-start md:self-center shrink-0">
                    Active Partner
                </span>
            </div>
        @endif

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
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- MAIN LAYOUT GRID -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- LEFT COLUMN: Profile info, level progression, badges, check-in history -->
            <div class="glass rounded-[32px] p-6 flex flex-col gap-6 relative overflow-hidden justify-between">
                <div class="absolute -bottom-40 -right-40 w-60 h-60 rounded-full bg-sky-500/10 blur-[80px] pointer-events-none"></div>

                <div>
                    <!-- Profile info -->
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-sky-400 to-indigo-500 flex items-center justify-center text-white font-bold text-2xl shadow-premium shrink-0">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div>
                            <span class="text-[10px] font-mono text-sky-400 font-bold uppercase tracking-wider block">ID AGEN</span>
                            <span class="text-sm font-mono font-bold text-slate-300">{{ $participant->tracking_id }}</span>
                            <h2 class="text-xl font-bold text-slate-100 mt-1 leading-tight">{{ $user->name }}</h2>
                        </div>
                    </div>

                    <!-- Verified Tag -->
                    <div class="mb-4">
                        @if($user->is_verified)
                            <div class="glow-green bg-emerald-500/5 rounded-2xl p-4 flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Otoritas</span>
                                <span class="text-xs font-extrabold text-emerald-400 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">verified</span> TERVERIFIKASI
                                </span>
                            </div>
                        @else
                            <div class="glow-amber bg-amber-500/5 rounded-2xl p-4 flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Otoritas</span>
                                <span class="text-xs font-extrabold text-amber-400 flex items-center gap-1 animate-pulse">
                                    <span class="material-symbols-outlined text-[14px]">history</span> MENUNGGU TINJAUAN
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Level Section -->
                    <div class="my-6 text-center">
                        <div class="text-xs text-sky-400 uppercase tracking-widest font-bold">Reputasi Pelaku Wisata</div>
                        <div class="text-5xl font-extrabold text-sky-400 tracking-tight my-2">
                            Level {{ floor($participant->reputation_points / 50) + 1 }}
                        </div>
                        <div class="flex justify-center gap-1 my-2 text-amber-400 text-lg">
                            {!! str_repeat('★', $participant->initial_rating) !!}{!! str_repeat('☆', 5 - $participant->initial_rating) !!}
                        </div>
                        <div class="text-xs text-slate-400">Total Pengalaman Poin: {{ $participant->reputation_points }} XP</div>
                    </div>

                    <!-- XP Progress Bar -->
                    <div class="space-y-4 border-t border-slate-800/60 pt-4">
                        <div>
                            <div class="flex justify-between text-xs text-slate-400 mb-1">
                                <span>Poin Menuju Level Berikutnya</span>
                                <span class="font-bold text-slate-200">{{ $participant->reputation_points % 50 }}/50 XP</span>
                            </div>
                            <div class="w-full bg-slate-800 rounded-full h-2">
                                <div class="bg-gradient-to-r from-sky-400 to-indigo-500 h-2 rounded-full transition-all duration-500" 
                                     style="width: {{ min(100, (($participant->reputation_points % 50) / 50) * 100) }}%"></div>
                            </div>
                        </div>

                        <!-- Details Box -->
                        <div class="space-y-2 text-xs text-slate-400 bg-slate-900/40 p-4 rounded-2xl border border-slate-800/50">
                            <div class="flex justify-between">
                                <span>Asal:</span>
                                <span class="font-bold text-slate-200 uppercase">{{ $participant->origin }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Kategori:</span>
                                <span class="font-bold text-slate-200 uppercase">Pelaku Wisata / Owner</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Profesi / Bidang:</span>
                                <span class="font-bold text-sky-400">{{ $participant->profession }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Badges Showcase -->
                <div class="border-t border-slate-800/60 pt-4">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Lencana Penghargaan (Badges)</h3>
                    <div class="flex flex-wrap gap-2">
                        @forelse($participant->badges as $badge)
                            <span class="px-2.5 py-1 bg-sky-500/10 border border-sky-500/20 rounded-lg text-[10px] text-sky-400 font-bold uppercase tracking-wider flex items-center gap-1" title="{{ $badge['desc'] }}">
                                <span class="material-symbols-outlined text-[12px]">{{ $badge['icon'] }}</span>
                                {{ $badge['name'] }}
                            </span>
                        @empty
                            <span class="text-xs text-slate-500 italic">Belum ada lencana yang diraih. Lakukan check-in dan daftarkan bisnis!</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- RIGHT / MIDDLE COLUMN: Dashboard tabs and main actions -->
            <div class="md:col-span-2 space-y-6">

                <!-- Navigation Tabs -->
                <div class="glass p-2 rounded-2xl flex gap-2">
                    <button @click="activeTab = 'business'" :class="activeTab === 'business' ? 'bg-sky-500 text-white font-bold' : 'text-slate-400 hover:text-slate-200'" class="flex-1 py-2.5 rounded-xl text-xs transition duration-200">
                        💼 Kelola Bisnis
                    </button>
                    <button @click="activeTab === 'checkin' ? activeTab = 'checkin' : activeTab = 'checkin'" :class="activeTab === 'checkin' ? 'bg-sky-500 text-white font-bold' : 'text-slate-400 hover:text-slate-200'" class="flex-1 py-2.5 rounded-xl text-xs transition duration-200">
                        🗓️ Presensi Harian
                    </button>
                    <button @click="activeTab = 'complaints'" :class="activeTab === 'complaints' ? 'bg-sky-500 text-white font-bold' : 'text-slate-400 hover:text-slate-200'" class="flex-1 py-2.5 rounded-xl text-xs transition duration-200 relative">
                        ⚠️ Keluhan Wisatawan
                        @if($complaints->count() > 0)
                            <span class="absolute top-1.5 right-1 w-2 h-2 bg-red-500 rounded-full animate-ping"></span>
                        @endif
                    </button>
                </div>

                <!-- TAB 1: KELOLA BISNIS & REFERRAL -->
                <div x-show="activeTab === 'business'" class="space-y-6">
                    <div class="glass rounded-[32px] p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-bold text-slate-200 flex items-center gap-2">
                                <span class="material-symbols-outlined text-sky-400">storefront</span>
                                Daftar Bisnis Anda
                            </h2>
                            <button @click="openModal()" :disabled="!{{ $user->is_verified ? 'true' : 'false' }}" class="px-4 py-2 bg-gradient-to-r from-sky-400 to-indigo-500 text-white text-xs font-bold rounded-xl hover:opacity-90 transition flex items-center gap-1 shadow-premium disabled:opacity-40">
                                <span class="material-symbols-outlined text-[16px]">add</span> Tambah Bisnis Baru
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[500px] overflow-y-auto pr-2">
                            @forelse($businesses as $biz)
                                <div class="p-4 bg-slate-950/40 rounded-2xl border border-slate-900 hover:border-sky-500/20 transition flex flex-col gap-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="px-2 py-0.5 rounded text-[8px] font-bold bg-sky-500/10 text-sky-400 uppercase tracking-widest">{{ $biz->type }}</span>
                                            <h3 class="font-bold text-slate-200 text-base mt-1.5">{{ $biz->name }}</h3>
                                        </div>
                                        <span class="text-[10px] text-slate-500">Ref: {{ $biz->upline_id === $user->id ? 'Saya' : $biz->upline->name }}</span>
                                    </div>

                                    <p class="text-slate-400 text-xs line-clamp-2">{{ $biz->description }}</p>

                                    <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-400 border-t border-slate-900/60 pt-3">
                                        <div><b>Pemilik:</b> {{ $biz->owner->name }}</div>
                                        <div><b>Kontak:</b> {{ $biz->owner->phone_number }}</div>
                                    </div>

                                    <!-- QR Code and Action Area -->
                                    <div class="flex items-center gap-4 bg-slate-900/40 p-3 rounded-xl border border-slate-800/60">
                                        @if($biz->qr_code_path)
                                            <img src="{{ asset('storage/' . $biz->qr_code_path) }}" alt="QR" class="w-16 h-16 bg-white p-1 rounded border border-slate-800">
                                        @else
                                            <div class="w-16 h-16 rounded bg-slate-950 flex items-center justify-center text-[10px] font-bold text-slate-600">NO QR</div>
                                        @endif
                                        <div class="flex-1 flex flex-col gap-1">
                                            <span class="text-[9px] font-mono text-slate-500 break-all">{{ substr($biz->qr_token, 0, 16) }}...</span>
                                            <div class="flex gap-2 mt-1">
                                                @if($biz->qr_code_path)
                                                    <a href="{{ asset('storage/' . $biz->qr_code_path) }}" download="{{ Str::slug($biz->name) }}-qr.png" class="px-3 py-1 bg-sky-500/10 border border-sky-500/20 text-sky-400 text-[10px] rounded-lg hover:bg-sky-500/20 transition flex items-center gap-1 font-bold">
                                                        <span class="material-symbols-outlined text-[12px]">download</span> Unduh
                                                    </a>
                                                    <a href="{{ route('business.qr.print', $biz->id) }}" target="_blank" class="px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] rounded-lg hover:bg-emerald-500/20 transition flex items-center gap-1 font-bold">
                                                        <span class="material-symbols-outlined text-[12px]">print</span> Cetak
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-2 text-center py-12 text-slate-500 text-xs">
                                    Belum ada bisnis yang terdaftar atau dirujuk. Gunakan tombol di atas untuk menambahkan bisnis.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- TAB 2: PRESENSI / CHECKIN HARIAN -->
                <div x-show="activeTab === 'checkin'" class="space-y-6">
                    <div class="glass rounded-[32px] p-6 relative" x-data="geoCheckin()">
                        <h2 class="text-lg font-bold mb-2 text-slate-200">Presensi Harian Pelaku Wisata</h2>
                        <p class="text-xs text-slate-400 mb-4">Catat kehadiran harian Anda untuk membangun reputasi dan menaikkan level di kawasan Mandeh.</p>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-850 pb-6 mb-6">
                            <div class="flex-1">
                                <span class="text-xs font-bold text-sky-400 block">+10 Poin Reputasi per hari</span>
                                @if($participant->armada == 'darat_laut')
                                    <template x-if="latitude && longitude">
                                        <div class="text-[10px] text-slate-450 mt-1.5">
                                            Koordinat GPS Terdeteksi: <span class="text-emerald-400" x-text="latitude + ', ' + longitude"></span>
                                        </div>
                                    </template>
                                @endif
                            </div>

                            @if($checkedInToday)
                                <button class="px-6 py-3 bg-emerald-500/10 text-emerald-400 font-bold rounded-2xl cursor-not-allowed border border-emerald-500/20 text-xs shrink-0" disabled>
                                    Sudah Check-in Hari Ini
                                </button>
                            @else
                                <form action="{{ route('vanguard.checkin') }}" method="POST" class="flex flex-col gap-2 w-full sm:w-auto" @submit="onSubmit($event)">
                                    @csrf
                                    <input type="hidden" name="gps_lat" x-model="latitude">
                                    <input type="hidden" name="gps_lng" x-model="longitude">
                                    
                                    <div class="flex gap-2">
                                        <select name="method" class="bg-slate-900 border border-slate-750 text-slate-200 text-xs rounded-2xl px-4 py-3 focus:outline-none focus:border-sky-500">
                                            @if($participant->armada == 'darat_laut')
                                                <option value="face_to_face">Presensi Fisik (Radius TIC)</option>
                                            @else
                                                <option value="livestream">Presensi Virtual</option>
                                            @endif
                                        </select>
                                        <button type="submit" class="px-6 py-3 bg-sky-500 hover:bg-sky-600 text-white font-bold rounded-2xl transition shadow-premium text-xs shrink-0">
                                            Kirim Check-in
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>

                        <!-- Check-in Log History -->
                        <div>
                            <h3 class="text-xs font-bold text-slate-450 uppercase tracking-wider mb-3">Riwayat Log Check-in</h3>
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-2">
                                @forelse($checkins as $checkin)
                                    <div class="flex justify-between items-center p-3 bg-slate-950/40 border border-slate-900 rounded-xl text-xs">
                                        <span class="text-slate-300 font-semibold">{{ $checkin->checkin_date->format('d M Y') }}</span>
                                        <span class="text-[9px] font-mono bg-sky-500/10 text-sky-400 px-2 py-0.5 rounded border border-sky-500/10 uppercase">
                                            {{ $checkin->verified_method == 'face_to_face' ? 'Fisik' : 'Virtual' }}
                                        </span>
                                    </div>
                                @empty
                                    <p class="text-slate-500 text-xs italic py-4">Belum ada riwayat presensi yang dicatat.</p>
                                @endforelse
                            </div>
                        </div>

                        @if(!$user->is_verified)
                            <div class="absolute inset-0 bg-slate-950/85 backdrop-blur-[1px] rounded-[32px] flex items-center justify-center p-6 text-center">
                                <div class="space-y-1">
                                    <span class="material-symbols-outlined text-amber-500 text-2xl font-bold">lock</span>
                                    <h4 class="font-bold text-amber-400 text-xs">PRESENSI DIKUNCI</h4>
                                    <p class="text-[10px] text-slate-400">Presensi aktif setelah akun Anda terverifikasi oleh Admin.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- TAB 3: KELUHAN WISATAWAN -->
                <div x-show="activeTab === 'complaints'" class="space-y-6">
                    <div class="glass rounded-[32px] p-6">
                        <h2 class="text-lg font-bold text-slate-200 mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-red-400">report_problem</span>
                            Keluhan Masuk dari Wisatawan
                        </h2>

                        <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2">
                            @forelse($complaints as $complaint)
                                <div class="p-4 bg-slate-950/40 border border-slate-900 rounded-2xl flex flex-col gap-3">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="text-xs font-bold text-slate-300">{{ $complaint->user->name }}</span>
                                            <span class="text-[10px] text-slate-500 block mt-0.5">Tujuan: <b>{{ $complaint->entity->name }}</b></span>
                                        </div>
                                        <div class="flex gap-1">
                                            <!-- Urgency Badge -->
                                            <span class="px-2 py-0.5 rounded text-[8px] font-bold uppercase
                                                @if($complaint->urgency === 'critical' || $complaint->urgency === 'high') bg-red-500/10 text-red-400 border border-red-500/20
                                                @elseif($complaint->urgency === 'medium') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                                @else bg-slate-800 text-slate-400 border border-slate-700/50 @endif">
                                                {{ $complaint->urgency }}
                                            </span>
                                            <!-- Status Badge -->
                                            <span class="px-2 py-0.5 rounded text-[8px] font-bold uppercase bg-sky-500/10 text-sky-400 border border-sky-500/20">
                                                {{ $complaint->status }}
                                            </span>
                                        </div>
                                    </div>

                                    <p class="text-slate-300 text-xs leading-relaxed bg-slate-900/30 p-3 rounded-xl border border-slate-900/60">{{ $complaint->content }}</p>

                                    <div class="flex justify-between items-center text-[10px] text-slate-500">
                                        <span>Diterima: {{ $complaint->created_at->format('d M Y H:i') }}</span>
                                        <button class="px-2.5 py-1 rounded bg-slate-800 text-slate-300 font-bold hover:bg-slate-750 transition">
                                            Respons Keluhan
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-12 text-slate-500 text-xs space-y-2">
                                    <span class="material-symbols-outlined text-slate-600 text-3xl block">checklist</span>
                                    <span>Tidak ada keluhan aktif dari wisatawan saat ini. Layanan berjalan dengan baik!</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- DESTINASI HUB (Static list for reference) -->
                <div class="glass rounded-[32px] p-6 relative">
                    <h2 class="text-lg font-bold mb-4 text-slate-300 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sky-400">beach_access</span>
                        Pusat Informasi Destinasi Wisata
                    </h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[300px] overflow-y-auto pr-2">
                        @forelse($destinations as $dest)
                            <div class="p-4 bg-slate-950/30 rounded-2xl border border-slate-800 flex flex-col justify-between gap-3">
                                <div>
                                    <div class="flex justify-between items-start gap-2">
                                        <h3 class="font-bold text-slate-200 text-sm">
                                            {{ $dest->name }}
                                        </h3>
                                        <span class="text-[9px] font-mono font-bold text-sky-400 bg-sky-500/10 px-2 py-0.5 rounded">
                                            {{ $dest->price_range ?? 'Free' }}
                                        </span>
                                    </div>
                                    <p class="text-slate-400 text-xs mt-2 line-clamp-2">{{ $dest->description }}</p>
                                </div>
                                <div class="flex items-center justify-between border-t border-slate-900 pt-2 text-[10px] text-slate-500">
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[12px]">location_on</span>
                                        {{ round($dest->lat, 4) }}, {{ round($dest->lng, 4) }}
                                    </span>
                                    <span class="font-mono text-sky-500/80">slug: {{ $dest->slug }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 text-slate-500 text-sm">
                                Belum ada destinasi wisata yang terdaftar.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- REGISTRATION MODAL DIALOG -->
    <div x-show="showRegModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm shadow-2xl" x-cloak x-transition>
        <div class="glass rounded-[32px] p-6 max-w-lg w-full max-h-[90vh] overflow-y-auto border border-sky-500/20 relative space-y-4" @click.away="showRegModal = false">
            <!-- Close button -->
            <button @click="showRegModal = false" class="absolute top-4 right-4 text-slate-450 hover:text-slate-250">
                <span class="material-symbols-outlined">close</span>
            </button>

            <div>
                <h3 class="text-xl font-bold text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sky-400">add_location_alt</span>
                    Daftarkan Bisnis Baru
                </h3>
                <p class="text-xs text-slate-400 mt-1">Lengkapi detail bisnis lokal atau kapal penyeberangan Anda di bawah ini.</p>
            </div>

            <form action="{{ route('business.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <label class="relative flex items-center gap-2 p-3.5 rounded-2xl bg-slate-900/60 border border-slate-800/80 cursor-pointer">
                    <input type="checkbox" name="register_for_self" value="1" x-model="registerForSelf" class="text-sky-500 focus:ring-0 rounded">
                    <span class="text-xs font-semibold text-slate-300">Daftarkan untuk Diri Sendiri</span>
                </label>

                <div class="space-y-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nama Bisnis / Armada</label>
                        <input type="text" name="name" required placeholder="RM Pesisir Indah" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl p-3 focus:outline-none focus:border-sky-500">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Kategori Layanan</label>
                        <select name="type" class="w-full bg-slate-900 border border-slate-700 text-slate-250 text-xs rounded-xl p-3 focus:outline-none focus:border-sky-500">
                            <option value="restoran">Restoran / Kuliner</option>
                            <option value="hotel">Hotel / Penginapan</option>
                            <option value="transportasi">Transportasi / Kapal Boat</option>
                            <option value="tour_guide">Pemandu Wisata</option>
                            <option value="produk_lokal">Oleh-Oleh / Souvenir</option>
                            <option value="layanan_publik">Layanan Publik</option>
                            <option value="destinasi">Destinasi Wisata</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Rentang Biaya / Harga Tiket</label>
                        <input type="text" name="price_range" placeholder="Contoh: Rp 50.000 - Rp 150.000" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl p-3 focus:outline-none focus:border-sky-500">
                    </div>

                    <!-- Map Location Picker Section -->
                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase">Tentukan Lokasi (Koordinat GPS)</label>
                            <button type="button" @click="detectLocation()" class="text-[10px] text-sky-400 font-bold hover:underline flex items-center gap-0.5">
                                <span class="material-symbols-outlined text-[12px]">my_location</span> Gunakan GPS Saya
                            </button>
                        </div>
                        
                        <!-- Peta Leaflet Picker -->
                        <div id="map-picker" class="h-48 w-full rounded-xl border border-slate-850 z-10" style="min-height: 180px;"></div>
                        <p class="text-[9px] text-slate-500">Geser penunjuk peta (marker) atau klik lokasi pada peta untuk memilih koordinat secara presisi.</p>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Latitude</label>
                                <input type="text" name="lat" required readonly x-model="latitudeInput" class="w-full bg-slate-950 border border-slate-800 text-slate-400 text-xs rounded-xl p-3 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Longitude</label>
                                <input type="text" name="lng" required readonly x-model="longitudeInput" class="w-full bg-slate-950 border border-slate-800 text-slate-400 text-xs rounded-xl p-3 focus:outline-none">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Deskripsi Ringkas</label>
                        <textarea name="description" rows="2" placeholder="Ceritakan keunggulan atau rincian layanan..." class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl p-3 focus:outline-none focus:border-sky-500"></textarea>
                    </div>
                </div>

                <!-- Owner Details Form (Hides if registerForSelf is checked) -->
                <div class="space-y-3 pt-3 border-t border-slate-800/60" x-show="!registerForSelf" x-transition>
                    <h3 class="text-xs font-bold text-sky-400 uppercase tracking-wider">Identitas Pemilik Baru</h3>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Hubungan Peran</label>
                        <select name="role_in_business" class="w-full bg-slate-900 border border-slate-700 text-slate-250 text-xs rounded-xl p-3 focus:outline-none focus:border-sky-500">
                            <option value="owner">Pemilik Utama (Owner)</option>
                            <option value="driver">Nahkoda / Pengemudi (Driver)</option>
                            <option value="manager">Pengelola (Manager)</option>
                            <option value="staff">Staf Pendukung</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nama Lengkap</label>
                        <input type="text" name="owner_name" :required="!registerForSelf" placeholder="Nama lengkap pemilik" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl p-3 focus:outline-none focus:border-sky-500">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Alamat Email</label>
                        <input type="email" name="owner_email" :required="!registerForSelf" placeholder="email@owner.com" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl p-3 focus:outline-none focus:border-sky-500">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nomor WhatsApp</label>
                        <input type="text" name="owner_phone" :required="!registerForSelf" placeholder="08..." class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl p-3 focus:outline-none focus:border-sky-500">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Kata Sandi Akun</label>
                        <input type="password" name="owner_password" :required="!registerForSelf" placeholder="Minimal 6 Karakter" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl p-3 focus:outline-none focus:border-sky-500">
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" @click="showRegModal = false" class="flex-1 py-3 bg-slate-800 text-slate-300 font-bold rounded-2xl hover:bg-slate-750 transition text-xs">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-sky-400 to-indigo-500 text-white font-bold rounded-2xl hover:opacity-90 transition shadow-premium text-xs">
                        Daftarkan Bisnis
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function dashboardState() {
            return {
                activeTab: 'business',
                registerForSelf: false,
                showRegModal: false,
                mapPicker: null,
                marker: null,
                latitudeInput: '-1.222400',
                longitudeInput: '100.414400',
                openModal() {
                    this.showRegModal = true;
                    this.$nextTick(() => {
                        this.initMapPicker();
                    });
                },
                initMapPicker() {
                    if (this.mapPicker) {
                        setTimeout(() => {
                            this.mapPicker.invalidateSize();
                        }, 100);
                        return;
                    }
                    setTimeout(() => {
                        this.mapPicker = L.map('map-picker').setView([-1.2224, 100.4144], 13);
                        
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(this.mapPicker);

                        this.marker = L.marker([-1.2224, 100.4144], { draggable: true }).addTo(this.mapPicker);

                        // Update coordinates on marker drag
                        this.marker.on('dragend', () => {
                            const latLng = this.marker.getLatLng();
                            this.latitudeInput = latLng.lat.toFixed(6);
                            this.longitudeInput = latLng.lng.toFixed(6);
                        });

                        // Update coordinates and marker on map click
                        this.mapPicker.on('click', (e) => {
                            this.marker.setLatLng(e.latlng);
                            this.latitudeInput = e.latlng.lat.toFixed(6);
                            this.longitudeInput = e.latlng.lng.toFixed(6);
                        });

                        this.latitudeInput = '-1.222400';
                        this.longitudeInput = '100.414400';
                        this.mapPicker.invalidateSize();
                    }, 200);
                },
                detectLocation() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                const lat = position.coords.latitude;
                                const lng = position.coords.longitude;
                                this.latitudeInput = lat.toFixed(6);
                                this.longitudeInput = lng.toFixed(6);
                                
                                if (this.mapPicker && this.marker) {
                                    const newLatLng = new L.LatLng(lat, lng);
                                    this.marker.setLatLng(newLatLng);
                                    this.mapPicker.setView(newLatLng, 15);
                                }
                            },
                            (err) => {
                                console.error(err);
                                alert("Gagal mendeteksi lokasi perangkat. Pastikan izin GPS aktif!");
                            }
                        );
                    } else {
                        alert("Geolocation tidak didukung oleh browser Anda.");
                    }
                }
            };
        }

        function geoCheckin() {
            return {
                latitude: null,
                longitude: null,
                isRequesting: false,
                init() {
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
                                    alert("Presensi Darat & Laut membutuhkan izin GPS lokasi.");
                                    this.isRequesting = false;
                                }
                            );
                        } else {
                            alert("Geolocation tidak didukung oleh browser.");
                            this.isRequesting = false;
                        }
                    @endif
                },
                onSubmit(e) {
                    @if($participant->armada == 'darat_laut')
                        if (!this.latitude || !this.longitude) {
                            e.preventDefault();
                            alert("Mohon tunggu hingga koordinat GPS Anda berhasil dideteksi!");
                        }
                    @endif
                }
            };
        }
    </script>
</body>
</html>
