<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin Manager - HPI Pessel 2026</title>
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
        .shadow-premium {
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        .modal-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(56, 189, 248, 0.2) rgba(255, 255, 255, 0.02);
            scroll-behavior: smooth;
        }
        .modal-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .modal-scroll::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 9999px;
        }
        .modal-scroll::-webkit-scrollbar-thumb {
            background-color: rgba(56, 189, 248, 0.2);
            border-radius: 9999px;
        }
        .modal-scroll::-webkit-scrollbar-thumb:hover {
            background-color: rgba(56, 189, 248, 0.4);
        }
    </style>
</head>
<body class="text-slate-100 min-h-screen p-4 md:p-8" x-data="{ activeTab: 'verification', showVerifyModal: false, selectedUser: null, showStatsModal: false, selectedCandidate: null }">
    <div class="max-w-6xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="glass rounded-3xl p-6 md:p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden">
            <div class="absolute -top-40 -left-40 w-80 h-80 rounded-full bg-indigo-500/10 blur-[100px] pointer-events-none"></div>
            
            <div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold glass border-emerald-500/30 text-emerald-400 uppercase tracking-wider">
                    Authorized Panel
                </span>
                <h1 class="text-3xl font-extrabold mt-3 mb-1 text-slate-200">Admin Manager Dashboard</h1>
                <p class="text-slate-400 text-sm">Orkestrasi ekosistem pariwisata Mandeh, kualifikasi Vanguard HPI, dan demokrasi Smart-Citizen.</p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <a href="/" class="px-5 py-3 rounded-2xl glass border-sky-400/20 text-sky-300 font-semibold text-sm hover:bg-sky-500/10 transition flex items-center gap-2">
                    🧭 Lihat Explorer
                </a>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-5 py-3 rounded-2xl glass border-red-500/20 text-red-400 font-semibold text-sm hover:bg-red-500/10 transition">
                        Keluar
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-400 text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-2xl text-red-400 text-sm font-semibold">
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

        <!-- Tab Controls Navigation -->
        <div class="flex flex-wrap gap-2 p-1.5 glass rounded-2xl border-slate-800">
            <button @click="activeTab = 'verification'" :class="activeTab === 'verification' ? 'bg-sky-500/15 border-sky-500/30 text-sky-400' : 'text-slate-400 hover:text-slate-200 border-transparent'" 
                    class="px-4 py-2.5 rounded-xl text-xs font-bold border transition duration-200 flex items-center gap-2">
                👤 Verifikasi & Pelantikan
            </button>
            <button @click="activeTab = 'missions'" :class="activeTab === 'missions' ? 'bg-sky-500/15 border-sky-500/30 text-sky-400' : 'text-slate-400 hover:text-slate-200 border-transparent'" 
                    class="px-4 py-2.5 rounded-xl text-xs font-bold border transition duration-200 flex items-center gap-2">
                📅 Kelola Kegiatan & Misi
            </button>
            <button @click="activeTab = 'reports'" :class="activeTab === 'reports' ? 'bg-sky-500/15 border-sky-500/30 text-sky-400' : 'text-slate-400 hover:text-slate-200 border-transparent'" 
                    class="px-4 py-2.5 rounded-xl text-xs font-bold border transition duration-200 flex items-center gap-2">
                📝 Peninjauan Laporan Tugas
                @if($pendingReports->count() > 0)
                    <span class="px-1.5 py-0.5 text-[10px] bg-sky-500 text-white rounded-full font-mono font-bold">{{ $pendingReports->count() }}</span>
                @endif
            </button>
            <button @click="activeTab = 'users'" :class="activeTab === 'users' ? 'bg-sky-500/15 border-sky-500/30 text-sky-400' : 'text-slate-400 hover:text-slate-200 border-transparent'" 
                    class="px-4 py-2.5 rounded-xl text-xs font-bold border transition duration-200 flex items-center gap-2">
                👥 Manajemen Group & Peran
            </button>
            <button @click="activeTab = 'voting'" :class="activeTab === 'voting' ? 'bg-sky-500/15 border-sky-500/30 text-sky-400' : 'text-slate-400 hover:text-slate-200 border-transparent'" 
                    class="px-4 py-2.5 rounded-xl text-xs font-bold border transition duration-200 flex items-center gap-2">
                🗳️ Jajak Pendapat & Voting
            </button>
        </div>

        <!-- TAB CONTENT AREA -->
        <div class="mt-4">

            <!-- TAB 1: Verification & Onboarding -->
            <div x-show="activeTab === 'verification'" class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <!-- User Verification Queue (BASKET 1) -->
                <div class="glass rounded-3xl p-6 relative overflow-hidden">
                    <h2 class="text-lg font-bold mb-4 text-slate-300 flex items-center gap-2">
                        <span>👤 Antrean Verifikasi Pengguna Baru</span>
                        <span class="px-2 py-0.5 text-xs bg-sky-500/20 text-sky-400 rounded-full font-mono font-bold">{{ $unverifiedUsers->count() }}</span>
                    </h2>
                    
                    <div class="space-y-3 max-h-[450px] overflow-y-auto pr-2 modal-scroll">
                        @forelse($unverifiedUsers as $user)
                            <div class="p-4 glass rounded-2xl border border-slate-800 flex flex-col gap-3 hover:border-slate-700 transition-all">
                                <div class="flex justify-between items-start gap-4">
                                    <div>
                                        <h3 class="font-bold text-slate-200">{{ $user->name }}</h3>
                                        <p class="text-slate-400 text-xs mt-0.5">{{ $user->email }}</p>
                                    </div>
                                    @if($user->vanguardParticipant)
                                        @if($user->vanguardParticipant->is_hpi_candidate)
                                            <span class="px-2 py-0.5 bg-sky-500/10 text-sky-400 text-[9px] font-bold rounded border border-sky-500/20">Calon HPI</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold rounded border border-emerald-500/20">Pelaku Wisata</span>
                                        @endif
                                    @else
                                        <span class="px-2 py-0.5 bg-slate-500/10 text-slate-400 text-[9px] font-bold rounded">Umum</span>
                                    @endif
                                </div>
                                <div class="flex justify-between items-center gap-2 pt-2 border-t border-slate-800/40">
                                    <span class="text-[10px] text-slate-500" x-text="'HP: ' + '{{ $user->phone_number }}'"></span>
                                    <button type="button" @click="selectedUser = {
                                        id: '{{ $user->id }}',
                                        name: '{{ addslashes($user->name) }}',
                                        email: '{{ $user->email }}',
                                        phone: '{{ $user->phone_number }}',
                                        role: '{{ $user->role }}',
                                        verify_url: '{{ route('vanguard.admin.verify', $user->id) }}',
                                        participant: {
                                            tracking_id: '{{ $user->vanguardParticipant ? $user->vanguardParticipant->tracking_id : '-' }}',
                                            origin: '{{ $user->vanguardParticipant ? $user->vanguardParticipant->origin : '-' }}',
                                            armada: '{{ $user->vanguardParticipant ? $user->vanguardParticipant->armada : '-' }}',
                                            profile_type: '{{ $user->vanguardParticipant ? $user->vanguardParticipant->profile_type : '-' }}',
                                            profession: '{{ $user->vanguardParticipant ? addslashes($user->vanguardParticipant->profession) : '-' }}',
                                            address: '{{ $user->vanguardParticipant ? addslashes(($user->vanguardParticipant->nagari ? $user->vanguardParticipant->nagari . ', ' : '') . ($user->vanguardParticipant->kecamatan ? $user->vanguardParticipant->kecamatan . ', ' : '') . ($user->vanguardParticipant->kabupaten ? $user->vanguardParticipant->kabupaten . ', ' : '') . ($user->vanguardParticipant->provinsi ? $user->vanguardParticipant->provinsi : '')) : '-' }}',
                                            full_address: '{{ $user->vanguardParticipant ? addslashes($user->vanguardParticipant->full_address) : '-' }}',
                                            join_reason: '{{ $user->vanguardParticipant ? addslashes(preg_replace('/\s+/', ' ', $user->vanguardParticipant->join_reason)) : '-' }}',
                                            selfie_url: '{{ $user->vanguardParticipant && $user->vanguardParticipant->selfie_path ? asset('storage/' . $user->vanguardParticipant->selfie_path) : '' }}',
                                            ktp_url: '{{ $user->vanguardParticipant && $user->vanguardParticipant->ktp_path ? asset('storage/' . $user->vanguardParticipant->ktp_path) : '' }}'
                                        }
                                    }; showVerifyModal = true;" class="px-3 py-1.5 bg-sky-500 hover:bg-sky-600 text-white font-bold text-xs rounded-xl transition shadow-premium">
                                        Verifikasi
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm text-center py-10">Tidak ada pengguna baru menunggu verifikasi.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Warga & Pelaku Wisata Reguler Terdaftar (BASKET 2) -->
                <div class="glass rounded-3xl p-6 relative overflow-hidden">
                    <h2 class="text-lg font-bold mb-4 text-slate-300 flex items-center gap-2">
                        <span>👥 Warga Terdaftar (Verified)</span>
                        <span class="px-2 py-0.5 text-xs bg-emerald-500/20 text-emerald-400 rounded-full font-mono font-bold">{{ $verifiedWarga->count() }}</span>
                    </h2>
                    
                    <div class="space-y-3 max-h-[450px] overflow-y-auto pr-2 modal-scroll">
                        @forelse($verifiedWarga as $user)
                            <div class="p-4 glass rounded-2xl border border-slate-800 flex flex-col gap-2 hover:border-slate-700 transition-all">
                                <div class="flex justify-between items-start gap-4">
                                    <div>
                                        <h3 class="font-bold text-slate-200">{{ $user->name }}</h3>
                                        <p class="text-slate-400 text-xs mt-0.5">{{ $user->email }}</p>
                                    </div>
                                    <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold rounded border border-emerald-500/20">Aktif</span>
                                </div>
                                @if($user->vanguardParticipant)
                                    <div class="pt-2 border-t border-slate-800/40 flex justify-between items-center text-[10px] text-slate-500">
                                        <span>Profesi: {{ $user->vanguardParticipant->profession }}</span>
                                        <span>Jenis: {{ $user->vanguardParticipant->profile_type === 'warga' ? 'Warga' : 'Wisata' }}</span>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm text-center py-10">Belum ada warga terdaftar saat ini.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Calon Anggota HPI Terverifikasi (BASKET 3) -->
                <div class="glass rounded-3xl p-6 relative overflow-hidden">
                    <h2 class="text-lg font-bold mb-4 text-slate-300 flex items-center gap-2">
                        <span>🎖️ Calon Anggota HPI</span>
                        <span class="px-2 py-0.5 text-xs bg-indigo-500/20 text-indigo-400 rounded-full font-mono font-bold">{{ $hpiCandidates->count() }}</span>
                    </h2>
                    
                    <div class="space-y-3 max-h-[450px] overflow-y-auto pr-2 modal-scroll">
                        @forelse($hpiCandidates as $index => $vg)
                            @php
                                $rank = $index + 1;
                                $borderClass = 'border-slate-800 hover:border-slate-700';
                                $bgClass = 'bg-slate-900/40';
                                $badgeHtml = '';
                                if ($rank === 1) {
                                    $borderClass = 'border-amber-400 shadow-[0_0_15px_rgba(251,191,36,0.15)]';
                                    $bgClass = 'bg-amber-950/10';
                                    $badgeHtml = '<span class="px-2 py-0.5 bg-amber-400/20 text-amber-300 text-[9px] font-bold rounded flex items-center gap-0.5 border border-amber-400/30">🏆 Top 1 Pimpinan</span>';
                                } elseif ($rank === 2) {
                                    $borderClass = 'border-slate-300 shadow-[0_0_15px_rgba(203,213,225,0.15)]';
                                    $bgClass = 'bg-slate-800/10';
                                    $badgeHtml = '<span class="px-2 py-0.5 bg-slate-300/20 text-slate-200 text-[9px] font-bold rounded flex items-center gap-0.5 border border-slate-300/30">🥈 Top 2 Elit</span>';
                                } elseif ($rank === 3) {
                                    $borderClass = 'border-amber-700 shadow-[0_0_15px_rgba(180,83,9,0.15)]';
                                    $bgClass = 'bg-amber-900/10';
                                    $badgeHtml = '<span class="px-2 py-0.5 bg-amber-700/20 text-amber-600 text-[9px] font-bold rounded flex items-center gap-0.5 border border-amber-700/30">🥉 Top 3 Unggulan</span>';
                                }
                            @endphp
                            
                            <div class="p-4 rounded-2xl border transition-all duration-300 {{ $borderClass }} {{ $bgClass }} flex flex-col gap-3">
                                <div class="flex justify-between items-start gap-4">
                                    <div class="flex items-center gap-3">
                                        <!-- Circular Selfie Avatar -->
                                        @if($vg->selfie_path)
                                            <div class="w-10 h-10 rounded-full overflow-hidden border border-slate-700 bg-slate-950 shrink-0">
                                                <img src="{{ asset('storage/' . $vg->selfie_path) }}" class="w-full h-full object-cover">
                                            </div>
                                        @else
                                            <div class="w-10 h-10 rounded-full border border-slate-800 bg-slate-950 flex items-center justify-center text-slate-600 shrink-0">
                                                <span class="material-symbols-outlined text-lg">account_circle</span>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <h3 class="font-bold text-slate-200">{{ $vg->name }}</h3>
                                                {!! $badgeHtml !!}
                                            </div>
                                            <p class="text-slate-400 text-xs mt-0.5">ID: {{ $vg->tracking_id }} | Poin: <span class="font-bold text-emerald-400">{{ $vg->reputation_points }}</span></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center gap-2 pt-2 border-t border-slate-800/40">
                                    <span class="text-[9px] uppercase tracking-wider font-extrabold text-slate-500 shrink-0">Divisi: {{ $vg->armada === 'darat_laut' ? 'Darat/Laut' : 'Udara' }}</span>
                                    <div class="flex gap-1.5">
                                        <button type="button" @click="selectedUser = {
                                            id: '{{ $vg->user_id }}',
                                            name: '{{ addslashes($vg->user->name) }}',
                                            email: '{{ $vg->user->email }}',
                                            phone: '{{ $vg->user->phone_number }}',
                                            role: '{{ $vg->user->role }}',
                                            verify_url: '',
                                            is_view_only: true,
                                            participant: {
                                                tracking_id: '{{ $vg->tracking_id }}',
                                                origin: '{{ $vg->origin }}',
                                                armada: '{{ $vg->armada }}',
                                                profile_type: '{{ $vg->profile_type }}',
                                                profession: '{{ addslashes($vg->profession) }}',
                                                address: '{{ addslashes(($vg->nagari ? $vg->nagari . ', ' : '') . ($vg->kecamatan ? $vg->kecamatan . ', ' : '') . ($vg->kabupaten ? $vg->kabupaten . ', ' : '') . ($vg->provinsi ? $vg->provinsi : '')) }}',
                                                full_address: '{{ addslashes($vg->full_address) }}',
                                                join_reason: '{{ addslashes(preg_replace('/\s+/', ' ', $vg->join_reason)) }}',
                                                selfie_url: '{{ $vg->selfie_path ? asset('storage/' . $vg->selfie_path) : '' }}',
                                                ktp_url: '{{ $vg->ktp_path ? asset('storage/' . $vg->ktp_path) : '' }}'
                                            }
                                        }; showVerifyModal = true;" class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700/60 font-bold text-[10px] rounded-xl transition flex items-center gap-1">
                                            <span class="material-symbols-outlined text-xs leading-none">account_circle</span>
                                            Profil
                                        </button>
                                        <button type="button" @click="selectedCandidate = {
                                            name: '{{ addslashes($vg->name) }}',
                                            tracking_id: '{{ $vg->tracking_id }}',
                                            armada: '{{ $vg->armada === 'darat_laut' ? 'Darat & Laut' : 'Udara' }}',
                                            profile_type: '{{ $vg->profile_type }}',
                                            profession: '{{ addslashes($vg->profession) }}',
                                            points: {{ $vg->reputation_points }},
                                            checkins_count: {{ $vg->checkins()->count() }},
                                            approved_missions_count: {{ $vg->missionReports()->where('status', 'approved')->count() }},
                                            selfie_url: '{{ $vg->selfie_path ? asset('storage/' . $vg->selfie_path) : '' }}',
                                            is_graduated: {{ $vg->is_vanguard_graduated ? 'true' : 'false' }}
                                        }; showStatsModal = true;" class="px-2.5 py-1.5 bg-sky-500/20 hover:bg-sky-500/35 text-sky-300 border border-sky-500/30 font-bold text-[10px] rounded-xl transition">
                                            Statistik & Penilaian
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm text-center py-10">Belum ada calon anggota HPI terverifikasi.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- TAB 2: Activities & Missions CRUD -->
            <div x-show="activeTab === 'missions'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Form Create Mission -->
                <div class="glass rounded-3xl p-6">
                    <h2 class="text-lg font-bold mb-4 text-slate-300">Buat Kegiatan / Misi Baru</h2>
                    <form action="{{ route('vanguard.admin.missions.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Judul Kegiatan</label>
                            <input name="title" required type="text" placeholder="Contoh: Aksi Bersih Pantai Carocok" 
                                   class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-2xl p-3 focus:outline-none focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Deskripsi & Tugas</label>
                            <textarea name="description" rows="3" required placeholder="Langkah pelaksanaan tugas..." class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-2xl p-3 focus:outline-none focus:border-sky-500"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-1">Target Armada</label>
                                <select name="armada_type" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-2xl p-3 focus:outline-none focus:border-sky-500">
                                    <option value="both">Semua Armada</option>
                                    <option value="darat_laut">Darat & Laut (TIC)</option>
                                    <option value="udara">Udara (Virtual)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-1">Hadiah Poin</label>
                                <input name="points_reward" required type="number" min="1" value="20" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-2xl p-3 focus:outline-none focus:border-sky-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-1">Bonus Poin</label>
                                <input name="bonus_points" type="number" min="0" value="0" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-2xl p-3 focus:outline-none focus:border-sky-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-1">Lencana (Badge ID)</label>
                                <input name="badge_id" type="text" placeholder="e.g. badge_penyelamat" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-2xl p-3 focus:outline-none focus:border-sky-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 mb-1">Waktu Mulai</label>
                                <input name="start_time" required type="datetime-local" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-2xl p-3 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 mb-1">Waktu Selesai</label>
                                <input name="end_time" required type="datetime-local" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-2xl p-3 focus:outline-none">
                            </div>
                        </div>

                        <button type="submit" class="w-full py-3 bg-gradient-to-r from-sky-400 to-indigo-500 text-white font-bold rounded-2xl hover:opacity-90 transition shadow-premium">
                            Jadwalkan Kegiatan
                        </button>
                    </form>
                </div>

                <!-- Missions list with edit forms -->
                <div class="lg:col-span-2 glass rounded-3xl p-6">
                    <h2 class="text-lg font-bold mb-4 text-slate-300">Daftar Kegiatan Terdaftar</h2>
                    <div class="space-y-3 max-h-[550px] overflow-y-auto pr-2">
                        @forelse($missions as $mission)
                            @php
                                $now = \Carbon\Carbon::now();
                                $isActive = $mission->start_time ? $now->between($mission->start_time, $mission->end_time) : true;
                                $notStarted = $mission->start_time ? $now->lessThan($mission->start_time) : false;
                                $isEnded = $mission->end_time ? $now->greaterThan($mission->end_time) : false;
                            @endphp
                            <div class="p-4 glass rounded-2xl border border-slate-800 space-y-3" x-data="{ editing: false }">
                                <div class="flex justify-between items-start gap-2">
                                    <div>
                                        <h3 class="font-bold text-slate-200 text-sm" x-show="!editing">{{ $mission->title }}</h3>
                                        <div class="flex flex-wrap gap-1 mt-1" x-show="!editing">
                                            <span class="px-2 py-0.5 bg-slate-800 text-slate-400 text-[9px] font-bold rounded">Poin: {{ $mission->points_reward }}</span>
                                            <span class="px-2 py-0.5 bg-sky-500/10 text-sky-400 text-[9px] font-bold rounded">Target: {{ str_replace('_', ' ', $mission->armada_type) }}</span>
                                            @if($mission->badge_id)
                                                <span class="px-2 py-0.5 bg-amber-500/10 text-amber-400 text-[9px] font-bold rounded">🏅 {{ $mission->badge_id }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="text-[9px] font-mono px-2 py-0.5 rounded
                                        @if($isActive) bg-emerald-500/10 text-emerald-400
                                        @elseif($notStarted) bg-sky-500/10 text-sky-400
                                        @else bg-slate-800 text-slate-500 @endif">
                                        @if($isActive) Aktif
                                        @elseif($notStarted) Scheduled
                                        @else Berakhir @endif
                                    </span>
                                </div>
                                <p class="text-slate-400 text-xs" x-show="!editing">{{ $mission->description }}</p>
                                
                                @if($mission->start_time && $mission->end_time)
                                    <div class="text-[10px] text-slate-500" x-show="!editing">
                                        Mulai: {{ $mission->start_time->format('d M Y H:i') }} | Selesai: {{ $mission->end_time->format('d M Y H:i') }}
                                    </div>
                                @endif

                                <!-- Edit form -->
                                @if($notStarted)
                                    <button @click="editing = !editing" class="text-xs text-sky-400 font-bold hover:underline" x-show="!editing">
                                        ✏️ Ubah Kegiatan
                                    </button>

                                    <form action="{{ route('vanguard.admin.missions.update', $mission->id) }}" method="POST" x-show="editing" class="space-y-3 pt-3 border-t border-slate-800">
                                        @csrf
                                        <div>
                                            <input type="text" name="title" value="{{ $mission->title }}" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl p-2 focus:outline-none">
                                        </div>
                                        <div>
                                            <textarea name="description" rows="2" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl p-2 focus:outline-none">{{ $mission->description }}</textarea>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <select name="armada_type" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl p-2 focus:outline-none">
                                                <option value="both" {{ $mission->armada_type == 'both' ? 'selected' : '' }}>Semua Armada</option>
                                                <option value="darat_laut" {{ $mission->armada_type == 'darat_laut' ? 'selected' : '' }}>Darat & Laut</option>
                                                <option value="udara" {{ $mission->armada_type == 'udara' ? 'selected' : '' }}>Udara</option>
                                            </select>
                                            <input type="number" name="points_reward" value="{{ $mission->points_reward }}" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl p-2 focus:outline-none">
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <input name="start_time" required type="datetime-local" value="{{ $mission->start_time ? $mission->start_time->format('Y-m-d\TH:i') : '' }}" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-[10px] rounded-xl p-2 focus:outline-none">
                                            <input name="end_time" required type="datetime-local" value="{{ $mission->end_time ? $mission->end_time->format('Y-m-d\TH:i') : '' }}" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-[10px] rounded-xl p-2 focus:outline-none">
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="submit" class="flex-1 py-1.5 bg-sky-500 text-white text-xs font-bold rounded-lg hover:opacity-90">Simpan Perubahan</button>
                                            <button type="button" @click="editing = false" class="py-1.5 px-3 bg-slate-800 text-slate-400 text-xs rounded-lg">Batal</button>
                                        </div>
                                    </form>

                                    <!-- Delete form -->
                                    <form action="{{ route('vanguard.admin.missions.delete', $mission->id) }}" method="POST" class="inline" x-show="!editing" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kegiatan ini?')">
                                        @csrf
                                        <button type="submit" class="text-xs text-red-400 font-bold hover:underline ml-3">
                                            🗑️ Hapus
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[10px] text-red-500 font-bold block">🔒 Terkunci (Sudah Berjalan / Berakhir)</span>
                                @endif
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm text-center py-10">Belum ada kegiatan/misi yang dijadwalkan.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- TAB 3: Task Reports Review -->
            <div x-show="activeTab === 'reports'" class="glass rounded-3xl p-6">
                <h2 class="text-lg font-bold mb-4 text-slate-300 flex items-center gap-2">
                    <span>📝 Peninjauan Laporan Tugas Vanguard</span>
                    <span class="px-2 py-0.5 text-xs bg-sky-500/20 text-sky-400 rounded-full font-mono font-bold">{{ $pendingReports->count() }}</span>
                </h2>

                <div class="space-y-4 max-h-[550px] overflow-y-auto pr-2">
                    @forelse($pendingReports as $report)
                        <div class="p-5 glass rounded-2xl border border-slate-800 space-y-3 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div class="space-y-1">
                                <span class="px-2 py-0.5 bg-sky-500/10 text-sky-400 text-[9px] font-bold rounded uppercase tracking-wider">
                                    {{ $report->participant->armada }}
                                </span>
                                <h3 class="font-bold text-slate-200 text-base">{{ $report->participant->name }} <span class="text-xs text-slate-500">({{ $report->participant->tracking_id }})</span></h3>
                                <p class="text-xs text-indigo-400 font-semibold">Tugas: {{ $report->mission->title }}</p>
                                <div class="bg-slate-950/40 p-3 rounded-xl border border-slate-900 text-xs text-slate-300 mt-2">
                                    {{ $report->report_text }}
                                </div>
                                @if($report->proof_url)
                                    <a href="{{ $report->proof_url }}" target="_blank" class="text-xs text-sky-400 hover:underline flex items-center gap-1 mt-1">
                                        🔗 Lihat Bukti Pelaksanaan
                                    </a>
                                @endif
                            </div>

                            <div class="flex gap-2 w-full md:w-auto shrink-0">
                                <form action="{{ route('vanguard.admin.reports.reject', $report->id) }}" method="POST" class="flex-1 md:flex-initial">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2.5 bg-red-500/20 hover:bg-red-500/30 text-red-400 font-bold text-xs rounded-xl border border-red-500/20 transition">
                                        Tolak Laporan
                                    </button>
                                </form>
                                <form action="{{ route('vanguard.admin.reports.approve', $report->id) }}" method="POST" class="flex-1 md:flex-initial">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl transition shadow-premium">
                                        Setujui Laporan (+{{ $report->mission->points_reward }} Poin)
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-slate-500 text-sm">
                            🎉 Semua laporan tugas telah diperiksa! Tidak ada peninjauan tertunda.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- TAB 4: User Groups & Role Management -->
            <div x-show="activeTab === 'users'" class="glass rounded-3xl p-6" x-data="{ roleFilter: 'all' }">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                    <h2 class="text-lg font-bold text-slate-300">👥 Manajemen Anggota & Hak Akses</h2>
                    <div>
                        <select x-model="roleFilter" class="bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl p-2.5 focus:outline-none">
                            <option value="all">Semua Peran</option>
                            <option value="wisatawan">Wisatawan</option>
                            <option value="warga">Warga</option>
                            <option value="vanguard">Vanguard (Kader)</option>
                            <option value="business_owner">Pelaku Bisnis</option>
                            <option value="layanan_publik">Pengurus HPI</option>
                            <option value="admin_manager">Admin Manager</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-[500px] overflow-y-auto pr-2">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
                                <th class="p-3">Nama</th>
                                <th class="p-3">Email</th>
                                <th class="p-3">Peran (Role)</th>
                                <th class="p-3">Poin</th>
                                <th class="p-3">Verifikasi</th>
                                <th class="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allUsers as $u)
                                @php
                                    $isVanguard = $u->vanguardParticipant !== null;
                                @endphp
                                <tr class="border-b border-slate-900 hover:bg-slate-900/20 transition-all" 
                                    x-show="roleFilter === 'all' || 
                                            (roleFilter === 'wisatawan' && '{{ $u->role }}' === 'wisatawan' && !{{ $isVanguard ? 'true' : 'false' }}) || 
                                            (roleFilter === 'vanguard' && {{ $isVanguard ? 'true' : 'false' }}) || 
                                            (roleFilter === '{{ $u->role }}')">
                                    <td class="p-3 font-semibold text-slate-200">
                                        {{ $u->name }}
                                        @if($isVanguard)
                                            <span class="block text-[9px] text-sky-400 font-mono">Vanguard ID: {{ $u->vanguardParticipant->tracking_id }} (Armada: {{ $u->vanguardParticipant->armada }})</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-slate-400 font-mono">{{ $u->email }}</td>
                                    <td class="p-3">
                                        @if($isVanguard)
                                            <span class="px-2 py-0.5 bg-sky-500/10 text-sky-400 rounded-md font-bold uppercase">Vanguard</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-slate-800 text-slate-300 rounded-md uppercase">{{ $u->role }}</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-slate-300 font-mono">
                                        @if($isVanguard)
                                            {{ $u->vanguardParticipant->reputation_points }} Poin
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        @if($u->is_verified)
                                            <span class="text-emerald-400 font-semibold">✓ Terverifikasi</span>
                                        @else
                                            <span class="text-red-400 font-semibold">✗ Belum</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-right">
                                        <form action="{{ route('vanguard.admin.users.role', $u->id) }}" method="POST" class="inline-flex gap-1">
                                            @csrf
                                            <select name="role" class="bg-slate-950 border border-slate-800 text-slate-300 text-[10px] rounded-lg p-1.5 focus:outline-none">
                                                <option value="wisatawan" {{ $u->role == 'wisatawan' ? 'selected' : '' }}>Wisatawan</option>
                                                <option value="warga" {{ $u->role == 'warga' ? 'selected' : '' }}>Warga</option>
                                                <option value="business_owner" {{ $u->role == 'business_owner' ? 'selected' : '' }}>Pelaku Bisnis</option>
                                                <option value="layanan_publik" {{ $u->role == 'layanan_publik' ? 'selected' : '' }}>Pengurus HPI</option>
                                                <option value="admin_manager" {{ $u->role == 'admin_manager' ? 'selected' : '' }}>Admin Manager</option>
                                            </select>
                                            <button type="submit" class="px-2.5 py-1.5 bg-sky-500 text-white font-bold text-[10px] rounded-lg hover:bg-sky-600 transition">
                                                Simpan
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 5: Surveys & Target Voting -->
            <div x-show="activeTab === 'voting'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Create Survey Form -->
                <div class="glass rounded-3xl p-6">
                    <h2 class="text-lg font-bold mb-4 text-slate-300">Buat Pemilihan/Survey Baru</h2>
                    <form action="{{ route('vanguard.admin.surveys.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Judul Pemilihan / Survei</label>
                            <input name="title" required type="text" placeholder="Contoh: Pemilihan Formatur Ketua HPI" 
                                   class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-2xl p-3 focus:outline-none focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Deskripsi & Petunjuk</label>
                            <textarea name="description" rows="2" placeholder="Detail petunjuk pengisian..." class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-2xl p-3 focus:outline-none focus:border-sky-500"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Sasaran Target Pengguna (Checklist)</label>
                            <div class="grid grid-cols-2 gap-2 mt-1">
                                <label class="flex items-center gap-2 text-xs text-slate-300">
                                    <input type="checkbox" name="target_roles[]" value="vanguard" checked class="rounded border-slate-700 bg-slate-950 text-sky-500"> Vanguard (Kader)
                                </label>
                                <label class="flex items-center gap-2 text-xs text-slate-300">
                                    <input type="checkbox" name="target_roles[]" value="warga" class="rounded border-slate-700 bg-slate-950 text-sky-500"> Warga Lokal
                                </label>
                                <label class="flex items-center gap-2 text-xs text-slate-300">
                                    <input type="checkbox" name="target_roles[]" value="wisatawan" class="rounded border-slate-700 bg-slate-950 text-sky-500"> Wisatawan
                                </label>
                                <label class="flex items-center gap-2 text-xs text-slate-300">
                                    <input type="checkbox" name="target_roles[]" value="business_owner" class="rounded border-slate-700 bg-slate-950 text-sky-500"> Pelaku Bisnis
                                </label>
                            </div>
                        </div>

                        <!-- Options Creator (Dynamic JSON) -->
                        <div x-data="{ options: [{ title: '', description: '', profile_url: '', link_text: '' }] }" class="space-y-2">
                            <label class="block text-xs font-semibold text-slate-400">Pilihan / Kandidat</label>
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                <template x-for="(opt, index) in options" :key="index">
                                    <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800 space-y-2 relative">
                                        <button type="button" @click="options.splice(index, 1)" class="absolute top-2 right-2 text-[10px] text-red-400 hover:underline" x-show="options.length > 1">Hapus</button>
                                        <div>
                                            <input type="text" :name="'options['+index+'][title]'" x-model="opt.title" placeholder="Nama Kandidat / Judul Opsi" required class="w-full bg-slate-900 border border-slate-800 text-slate-200 text-xs rounded-lg p-2 focus:outline-none">
                                        </div>
                                        <div>
                                            <input type="text" :name="'options['+index+'][description]'" x-model="opt.description" placeholder="Deskripsi pendek" class="w-full bg-slate-900 border border-slate-800 text-slate-200 text-[10px] rounded-lg p-1.5 focus:outline-none">
                                        </div>
                                        <div class="grid grid-cols-2 gap-1">
                                            <input type="url" :name="'options['+index+'][profile_url]'" x-model="opt.profile_url" placeholder="URL Profil" class="w-full bg-slate-900 border border-slate-800 text-slate-200 text-[9px] rounded-lg p-1.5 focus:outline-none">
                                            <input type="text" :name="'options['+index+'][link_text]'" x-model="opt.link_text" placeholder="Teks Link" class="w-full bg-slate-900 border border-slate-800 text-slate-200 text-[9px] rounded-lg p-1.5 focus:outline-none">
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="options.push({ title: '', description: '', profile_url: '', link_text: '' })" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs rounded-xl transition">
                                + Tambah Pilihan
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 mb-1">Waktu Mulai</label>
                                <input name="start_time" required type="datetime-local" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-2xl p-3 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 mb-1">Waktu Selesai</label>
                                <input name="end_time" required type="datetime-local" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-2xl p-3 focus:outline-none">
                            </div>
                        </div>

                        <button type="submit" class="w-full py-3 bg-gradient-to-r from-sky-400 to-indigo-500 text-white font-bold rounded-2xl hover:opacity-90 transition shadow-premium">
                            Jadwalkan & Siarkan
                        </button>
                    </form>
                </div>

                <!-- Existing Surveys list & real-time results -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="glass rounded-3xl p-6">
                        <h2 class="text-lg font-bold mb-4 text-slate-300">Daftar Jajak Pendapat & Perolehan Suara</h2>
                        <div class="space-y-4 max-h-[550px] overflow-y-auto pr-2">
                            @forelse($surveys as $survey)
                                <div class="p-4 glass rounded-2xl border border-slate-800 space-y-3" x-data="{ editing: false }">
                                    <div class="flex justify-between items-start gap-2">
                                        <div>
                                            <h3 class="font-bold text-slate-200 text-base" x-show="!editing">{{ $survey->title }}</h3>
                                            <span class="text-[9px] uppercase tracking-wider font-extrabold text-slate-500" x-show="!editing">Target: {{ implode(', ', $survey->target_roles ?? []) }}</span>
                                        </div>
                                        <span class="text-[9px] font-mono px-2 py-0.5 rounded
                                            @if($survey->is_closed) bg-red-500/10 text-red-400
                                            @elseif(Carbon\Carbon::now()->between($survey->start_time, $survey->end_time)) bg-emerald-500/10 text-emerald-400
                                            @else bg-sky-500/10 text-sky-400 @endif">
                                            @if($survey->is_closed) Ended/Closed
                                            @elseif(Carbon\Carbon::now()->between($survey->start_time, $survey->end_time)) Aktif
                                            @else Scheduled @endif
                                        </span>
                                    </div>
                                    <p class="text-slate-400 text-xs" x-show="!editing">{{ $survey->description }}</p>

                                    <!-- real-time progress -->
                                    <div class="space-y-2 p-3 bg-slate-950/40 rounded-xl border border-slate-900/60">
                                        <div class="flex justify-between text-[10px] text-slate-400 font-semibold mb-1">
                                            <span>Turnout Partisipasi: {{ $survey->participation_rate }}%</span>
                                            <span>{{ $survey->voted_count }} / {{ $survey->eligible_count }} Pemilih</span>
                                        </div>
                                        <div class="w-full bg-slate-800 rounded-full h-1.5">
                                            <div class="bg-gradient-to-r from-sky-400 to-indigo-500 h-1.5 rounded-full" style="width: {{ $survey->participation_rate }}%"></div>
                                        </div>

                                        <!-- Vote options results charts -->
                                        <div class="mt-3 space-y-2 border-t border-slate-900 pt-2">
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Perolehan Suara Sementara:</label>
                                            @foreach($survey->options ?? [] as $opt)
                                                @php
                                                    $title = $opt['title'] ?? '';
                                                    $count = $survey->vote_results[$title] ?? 0;
                                                    $pct = $survey->voted_count > 0 ? round(($count / $survey->voted_count) * 100, 1) : 0;
                                                @endphp
                                                <div class="space-y-1">
                                                    <div class="flex justify-between text-[11px]">
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

                                    @if(Carbon\Carbon::now()->lessThan($survey->start_time))
                                        <button @click="editing = !editing" class="text-xs text-sky-400 font-bold hover:underline" x-show="!editing">
                                            ✏️ Ubah Survey / Opsi
                                        </button>
                                        
                                        <form action="{{ route('vanguard.admin.surveys.update', $survey->id) }}" method="POST" x-show="editing" class="space-y-3 pt-3 border-t border-slate-800">
                                            @csrf
                                            <div>
                                                <input type="text" name="title" value="{{ $survey->title }}" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl p-2 focus:outline-none">
                                            </div>
                                            <div>
                                                <textarea name="description" rows="2" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl p-2 focus:outline-none">{{ $survey->description }}</textarea>
                                            </div>
                                            
                                            <!-- hidden target roles -->
                                            @foreach($survey->target_roles ?? [] as $r)
                                                <input type="hidden" name="target_roles[]" value="{{ $r }}">
                                            @endforeach

                                            <!-- Options editor initialized with existing data -->
                                            <div x-data="{ options: {{ json_encode($survey->options ?? [['title' => '', 'description' => '', 'profile_url' => '', 'link_text' => '']]) }} }" class="space-y-2">
                                                <label class="block text-[10px] font-bold text-slate-400">Pilihan / Kandidat</label>
                                                <div class="space-y-2">
                                                    <template x-for="(opt, index) in options" :key="index">
                                                        <div class="p-2 bg-slate-950/60 rounded-lg border border-slate-800 space-y-1 relative">
                                                            <button type="button" @click="options.splice(index, 1)" class="absolute top-1 right-1 text-[9px] text-red-400" x-show="options.length > 1">Hapus</button>
                                                            <input type="text" :name="'options['+index+'][title]'" x-model="opt.title" placeholder="Nama Kandidat" required class="w-full bg-slate-900 border border-slate-800 text-slate-200 text-[10px] rounded p-1 focus:outline-none">
                                                            <input type="text" :name="'options['+index+'][description]'" x-model="opt.description" placeholder="Deskripsi" class="w-full bg-slate-900 border border-slate-800 text-slate-200 text-[9px] rounded p-1 focus:outline-none">
                                                            <div class="grid grid-cols-2 gap-1">
                                                                <input type="url" :name="'options['+index+'][profile_url]'" x-model="opt.profile_url" placeholder="URL Profil" class="w-full bg-slate-900 border border-slate-800 text-slate-200 text-[8px] rounded p-1 focus:outline-none">
                                                                <input type="text" :name="'options['+index+'][link_text]'" x-model="opt.link_text" placeholder="Teks Link" class="w-full bg-slate-900 border border-slate-800 text-slate-200 text-[8px] rounded p-1 focus:outline-none">
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <button type="button" @click="options.push({ title: '', description: '', profile_url: '', link_text: '' })" class="px-2 py-1 bg-slate-850 text-slate-300 text-[10px] rounded">
                                                        + Tambah
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-1">
                                                <input name="start_time" required type="datetime-local" value="{{ $survey->start_time->format('Y-m-d\TH:i') }}" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-[10px] rounded-xl p-2 focus:outline-none">
                                                <input name="end_time" required type="datetime-local" value="{{ $survey->end_time->format('Y-m-d\TH:i') }}" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-[10px] rounded-xl p-2 focus:outline-none">
                                            </div>
                                            <div class="flex gap-2">
                                                <button type="submit" class="flex-1 py-1.5 bg-sky-500 text-white text-xs font-bold rounded-lg hover:opacity-90">Simpan</button>
                                                <button type="button" @click="editing = false" class="py-1.5 px-3 bg-slate-800 text-slate-400 text-xs rounded-lg">Batal</button>
                                            </div>
                                        </form>
                                    @else
                                        <span class="text-[10px] text-red-500 font-bold block">🔒 Terkunci (Sudah Berjalan / Berakhir)</span>
                                    @endif
                                </div>
                            @empty
                                <p class="text-slate-500 text-sm text-center py-10">Belum ada jajak pendapat terjadwal.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Verification Modal -->
    <div x-show="showVerifyModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/90 backdrop-blur-md"
         x-cloak>
        
        <div class="max-w-2xl w-full bg-slate-900 border border-slate-700/60 rounded-[28px] p-6 md:p-8 shadow-2xl relative overflow-hidden flex flex-col max-h-[90vh]">
            <div class="absolute -top-20 -right-20 w-48 h-48 rounded-full bg-sky-500/10 blur-[60px] pointer-events-none"></div>
            
            <div class="flex justify-between items-center border-b border-slate-800 pb-4 shrink-0">
                                <h2 class="text-xl font-extrabold text-slate-100 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sky-400">badge</span>
                                    <span x-text="selectedUser?.is_view_only ? 'Detail Profil Anggota' : 'Detail & Konfirmasi Verifikasi'"></span>
                                </h2>
                                <button type="button" @click="showVerifyModal = false" class="text-slate-400 hover:text-slate-200">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>
            
            <div class="overflow-y-auto pr-2 my-4 space-y-6 flex-1 text-sm text-slate-300 modal-scroll">
                <!-- Grid Detail Profil -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-3">
                        <h3 class="font-bold text-sky-400 uppercase text-xs tracking-wider">Informasi Akun</h3>
                        <div class="flex items-center gap-3">
                            <!-- Circular Selfie Thumbnail -->
                            <template x-if="selectedUser?.participant?.selfie_url">
                                <div class="w-12 h-12 rounded-full overflow-hidden border border-slate-700/60 bg-slate-950 shrink-0">
                                    <img :src="selectedUser.participant.selfie_url" class="w-full h-full object-cover">
                                </div>
                            </template>
                            <template x-if="!selectedUser?.participant?.selfie_url">
                                <div class="w-12 h-12 rounded-full border border-slate-800 bg-slate-950 flex items-center justify-center text-slate-600 shrink-0">
                                    <span class="material-symbols-outlined text-xl">account_circle</span>
                                </div>
                            </template>
                            <div>
                               <span class="text-xs text-slate-500 block">Nama Lengkap</span>
                               <span class="font-semibold text-slate-200" x-text="selectedUser?.name"></span>
                            </div>
                        </div>
                        <div>
                           <span class="text-xs text-slate-500 block">Email</span>
                           <span class="font-mono text-slate-300" x-text="selectedUser?.email"></span>
                        </div>
                        <div>
                           <span class="text-xs text-slate-500 block">Nomor Telepon</span>
                           <span class="font-semibold text-slate-200" x-text="selectedUser?.phone"></span>
                        </div>
                        <div>
                           <span class="text-xs text-slate-500 block">Tracking ID</span>
                           <span class="font-mono text-slate-300" x-text="selectedUser?.participant?.tracking_id"></span>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <h3 class="font-bold text-sky-400 uppercase text-xs tracking-wider">Kualifikasi & Domisili</h3>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                               <span class="text-xs text-slate-500 block">Kategori</span>
                               <span class="font-semibold text-slate-200" x-text="selectedUser?.participant?.profile_type === 'warga' ? 'Warga Lokal' : 'Pelaku Wisata'"></span>
                            </div>
                            <div>
                               <span class="text-xs text-slate-500 block">Divisi Armada</span>
                               <span class="font-semibold text-slate-200" x-text="selectedUser?.participant?.armada === 'darat_laut' ? 'Darat & Laut' : (selectedUser?.participant?.armada === 'udara' ? 'Udara' : '-')"></span>
                            </div>
                        </div>
                        <div>
                           <span class="text-xs text-slate-500 block">Asal Keanggotaan</span>
                           <span class="font-semibold text-slate-200 uppercase" x-text="selectedUser?.participant?.origin"></span>
                        </div>
                        <div>
                           <span class="text-xs text-slate-500 block">Profesi Utama</span>
                           <span class="font-semibold text-slate-200" x-text="selectedUser?.participant?.profession"></span>
                        </div>
                    </div>
                </div>

                <!-- Alamat & Alasan -->
                <div class="space-y-3 border-t border-slate-800 pt-4">
                    <div>
                       <span class="text-xs text-slate-500 block">Alamat Wilayah</span>
                       <span class="font-semibold text-slate-200" x-text="selectedUser?.participant?.address"></span>
                    </div>
                    <div>
                       <span class="text-xs text-slate-500 block">Alamat Lengkap</span>
                       <span class="font-semibold text-slate-200" x-text="selectedUser?.participant?.full_address"></span>
                    </div>
                    <div>
                       <span class="text-xs text-slate-500 block">Alasan Bergabung</span>
                       <p class="text-slate-300 italic text-xs leading-relaxed bg-slate-955/40 p-3 rounded-xl border border-slate-900" x-text="selectedUser?.participant?.join_reason"></p>
                    </div>
                </div>

                <!-- Foto KTP Dokumen -->
                <div class="space-y-3 border-t border-slate-800 pt-4">
                    <h3 class="font-bold text-sky-400 uppercase text-xs tracking-wider">Dokumen Lampiran</h3>
                    <div class="w-full max-w-lg">
                       <span class="text-xs text-slate-500 block mb-2">Foto KTP Warga</span>
                       <template x-if="selectedUser?.participant?.ktp_url">
                           <div class="w-full aspect-[1.586/1] rounded-2xl overflow-hidden border border-slate-800 bg-slate-955 flex items-center justify-center">
                               <img :src="selectedUser.participant.ktp_url" class="w-full h-full object-cover">
                           </div>
                       </template>
                       <template x-if="!selectedUser?.participant?.ktp_url">
                           <div class="w-full aspect-[1.586/1] rounded-2xl border border-slate-850 bg-slate-950 flex flex-col items-center justify-center text-slate-600">
                               <span class="material-symbols-outlined text-4xl">credit_card_off</span>
                               <span class="text-xs mt-1">Tidak ada foto KTP</span>
                           </div>
                       </template>
                    </div>
                </div>
            </div>

            <!-- Footer Konfirmasi -->
            <div class="border-t border-slate-800 pt-4 shrink-0 flex flex-col md:flex-row justify-between items-center gap-4 bg-slate-900">
                <template x-if="!selectedUser?.is_view_only">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4 w-full">
                        <div class="text-center md:text-left">
                            <p class="text-xs text-slate-400 font-semibold">Apakah Anda yakin ingin memverifikasi warga ini?</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Aksi ini akan merubah status pengguna menjadi terverifikasi.</p>
                        </div>
                        <div class="flex gap-2 w-full md:w-auto">
                            <button type="button" @click="showVerifyModal = false" class="flex-1 md:flex-initial px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-400 font-semibold text-xs rounded-xl transition">
                                Batal
                            </button>
                            <form :action="selectedUser?.verify_url" method="POST" class="flex-1 md:flex-initial">
                                @csrf
                                <button type="submit" class="w-full px-6 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl transition shadow-premium">
                                    Verifikasi Sekarang
                                </button>
                            </form>
                        </div>
                    </div>
                </template>
                <template x-if="selectedUser?.is_view_only">
                    <div class="flex justify-end w-full">
                        <button type="button" @click="showVerifyModal = false" class="px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs rounded-xl transition border border-slate-700">
                            Tutup
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Statistics Modal -->
    <div x-show="showStatsModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/90 backdrop-blur-md"
         x-cloak>
        
        <div class="max-w-xl w-full bg-slate-900 border border-slate-700/60 rounded-[28px] p-6 md:p-8 shadow-2xl relative overflow-hidden flex flex-col max-h-[90vh]">
            <div class="absolute -top-20 -right-20 w-48 h-48 rounded-full bg-indigo-500/10 blur-[60px] pointer-events-none"></div>
            
            <div class="flex justify-between items-center border-b border-slate-800 pb-4 shrink-0">
                <h2 class="text-xl font-extrabold text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-400">monitoring</span>
                    Statistik & Progress Kinerja
                </h2>
                <button type="button" @click="showStatsModal = false" class="text-slate-400 hover:text-slate-200">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="overflow-y-auto pr-2 my-4 space-y-6 flex-1 text-sm text-slate-300 modal-scroll">
                
                <!-- Profile Header Card inside Modal -->
                <div class="p-5 glass rounded-2xl border border-slate-800 flex items-center gap-4">
                    <template x-if="selectedCandidate?.selfie_url">
                        <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-indigo-500/50 bg-slate-950 shrink-0">
                            <img :src="selectedCandidate.selfie_url" class="w-full h-full object-cover">
                        </div>
                    </template>
                    <template x-if="!selectedCandidate?.selfie_url">
                        <div class="w-16 h-16 rounded-full border-2 border-slate-800 bg-slate-950 flex items-center justify-center text-slate-600 shrink-0">
                            <span class="material-symbols-outlined text-3xl">account_circle</span>
                        </div>
                    </template>
                    <div class="space-y-1">
                        <h3 class="text-lg font-bold text-slate-200" x-text="selectedCandidate?.name"></h3>
                        <p class="text-xs text-slate-400">ID: <span class="font-mono text-slate-300" x-text="selectedCandidate?.tracking_id"></span></p>
                        
                        <!-- Graduation Badge Status -->
                        <div>
                            <template x-if="selectedCandidate?.is_graduated">
                                <span class="px-2.5 py-1 bg-emerald-500/20 text-emerald-400 text-[10px] font-extrabold rounded-full border border-emerald-500/30 uppercase tracking-wider">
                                    ✓ Lulus (Siap Dilantik)
                                </span>
                            </template>
                            <template x-if="!selectedCandidate?.is_graduated">
                                <span class="px-2.5 py-1 bg-amber-500/20 text-amber-400 text-[10px] font-extrabold rounded-full border border-amber-500/30 uppercase tracking-wider">
                                    ⚡ Berproses (Poin &lt; 80)
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Division / Profession Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 glass rounded-2xl border border-slate-800/80">
                        <span class="text-xs text-slate-500 block">Armada / Divisi</span>
                        <span class="font-bold text-slate-200 text-sm" x-text="selectedCandidate?.armada"></span>
                    </div>
                    <div class="p-4 glass rounded-2xl border border-slate-800/80">
                        <span class="text-xs text-slate-500 block">Profesi Utama</span>
                        <span class="font-bold text-slate-200 text-sm" x-text="selectedCandidate?.profession"></span>
                    </div>
                </div>

                <!-- Metrics Counter Grid -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="p-4 bg-indigo-950/20 border border-indigo-500/20 rounded-2xl text-center">
                        <span class="material-symbols-outlined text-indigo-400 text-xl block mb-1">workspace_premium</span>
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Total Poin</span>
                        <span class="block text-2xl font-extrabold text-indigo-300 mt-1" x-text="selectedCandidate?.points"></span>
                    </div>
                    <div class="p-4 bg-sky-950/20 border border-sky-500/20 rounded-2xl text-center">
                        <span class="material-symbols-outlined text-sky-400 text-xl block mb-1">location_on</span>
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Check-ins</span>
                        <span class="block text-2xl font-extrabold text-sky-300 mt-1" x-text="selectedCandidate?.checkins_count"></span>
                    </div>
                    <div class="p-4 bg-emerald-950/20 border border-emerald-500/20 rounded-2xl text-center">
                        <span class="material-symbols-outlined text-emerald-400 text-xl block mb-1">task_alt</span>
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Misi Selesai</span>
                        <span class="block text-2xl font-extrabold text-emerald-300 mt-1" x-text="selectedCandidate?.approved_missions_count"></span>
                    </div>
                </div>

                <!-- Progress toward Graduation Gate (80 points) -->
                <div class="p-5 glass rounded-2xl border border-slate-800/80 space-y-3">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-slate-300">Target Kelulusan HPI</span>
                        <span class="font-bold text-slate-300" x-text="selectedCandidate ? Math.min(selectedCandidate.points, 80) + '/80 Poin' : '0/80 Poin'"></span>
                    </div>
                    
                    <!-- Progress Bar Component -->
                    <div class="w-full h-3 bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                        <div class="h-full bg-gradient-to-r from-indigo-500 to-emerald-400 transition-all duration-500" 
                             :style="selectedCandidate ? 'width: ' + Math.min((selectedCandidate.points / 80) * 100, 100) + '%' : 'width: 0%'">
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-[10px] text-slate-500 font-semibold">
                        <span>Pendaftaran</span>
                        <span x-text="selectedCandidate?.is_graduated ? 'Syarat Terpenuhi! ✓' : 'Kurang ' + (80 - selectedCandidate?.points) + ' poin lagi'"></span>
                    </div>
                </div>

            </div>

            <!-- Footer Modal -->
            <div class="border-t border-slate-800 pt-4 shrink-0 flex justify-end bg-slate-900">
                <button type="button" @click="showStatsModal = false" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow-premium">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</body>
</html>
