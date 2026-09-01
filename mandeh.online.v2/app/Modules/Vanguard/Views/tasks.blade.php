<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tugas - HPI Pessel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.1);
        }
    </style>
</head>
<body class="text-slate-100 min-h-screen p-4 md:p-8">
    <div class="max-w-6xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="glass rounded-3xl p-6 md:p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative overflow-hidden">
            <div class="absolute -top-40 -left-40 w-80 h-80 rounded-full bg-indigo-500/10 pointer-events-none blur-[100px]"></div>
            <div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold glass border-sky-500/30 text-sky-400 uppercase tracking-wider">
                    Armada {{ $participant->armada == 'darat_laut' ? 'Darat & Laut' : 'Udara (Siber)' }}
                </span>
                <h1 class="text-3xl font-extrabold mt-3 mb-1 gradient-text">Daftar Tugas & Kegiatan</h1>
                <p class="text-slate-400 text-sm">Kelola absensi harian, agenda misi, dan checklist tugas resmi organisasi</p>
            </div>
            <div class="flex gap-2">
                @if($participant->is_inaugurated)
                    <a href="{{ route('vanguard.supervision') }}" class="px-5 py-3 rounded-2xl glass border-emerald-400/20 text-emerald-300 font-semibold text-sm hover:bg-emerald-500/10 transition flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">shield</span>
                        Laporan Pengawasan
                    </a>
                @endif
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

        @if($errors->any())
            <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-2xl text-red-400 text-sm">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>⚠️ {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column: Check-in & Checklist -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Daily Check-in Card (with Geo verification) -->
                <div class="glass rounded-3xl p-6 relative overflow-hidden" x-data="geoCheckin()">
                    <div class="absolute -top-10 -right-10 w-24 h-24 rounded-full bg-sky-500/10 pointer-events-none blur-[40px]"></div>
                    <h2 class="text-lg font-bold mb-4 text-slate-300 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sky-400">how_to_reg</span>
                        Presensi Harian O2O
                    </h2>
                    
                    <div class="space-y-4">
                        <p class="text-slate-400 text-xs leading-relaxed">Catat kehadiran Anda hari ini. Armada Darat & Laut diverifikasi dalam radius 100m dari Gedung TIC Mandeh.</p>
                        <span class="inline-block bg-sky-500/10 text-sky-400 text-[10px] font-bold px-2 py-1 rounded border border-sky-500/20">Mendapatkan +10 Poin Reputasi</span>
                        
                        @if($participant->armada == 'darat_laut')
                            <template x-if="latitude && longitude">
                                <div class="text-[10px] text-slate-400 p-2 rounded-lg bg-slate-900/40 border border-slate-800">
                                    📍 Koordinat GPS: <span class="text-emerald-400 font-mono font-bold" x-text="latitude + ', ' + longitude"></span>
                                </div>
                            </template>
                        @endif

                        @if($checkedInToday)
                            <div class="w-full p-3.5 bg-emerald-500/10 border border-emerald-500/25 rounded-2xl text-emerald-400 font-bold text-center text-sm flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-lg">check_circle</span>
                                Sudah Check-in Hari Ini
                            </div>
                        @else
                            <form action="{{ route('vanguard.checkin') }}" method="POST" class="space-y-3" @submit="onSubmit($event)">
                                @csrf
                                <input type="hidden" name="gps_lat" x-model="latitude">
                                <input type="hidden" name="gps_lng" x-model="longitude">
                                
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-semibold text-slate-400 uppercase">Metode Presensi</label>
                                    <select name="method" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-xl p-3 focus:outline-none focus:border-sky-500">
                                        @if($participant->armada == 'darat_laut')
                                            <option value="face_to_face">Tatap Muka (Gedung TIC)</option>
                                        @else
                                            <option value="livestream">Siaran Langsung / Virtual</option>
                                        @endif
                                    </select>
                                </div>
                                <button type="submit" class="w-full py-3 bg-sky-500 hover:bg-sky-600 text-white font-bold rounded-xl transition shadow-premium text-sm flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-lg">near_me</span>
                                    Lakukan Presensi
                                </button>
                            </form>
                        @endif
                    </div>

                    @if(!auth()->user()->is_verified)
                        <div class="absolute inset-0 bg-slate-950/90 backdrop-blur-[2px] rounded-3xl flex items-center justify-center p-6 text-center">
                            <div class="space-y-2">
                                <span class="material-symbols-outlined text-amber-500 text-3xl font-bold">lock</span>
                                <h4 class="font-bold text-amber-400 text-sm uppercase tracking-wider">PRESENSI DIKUNCI</h4>
                                <p class="text-xs text-slate-400 max-w-[200px] mx-auto">Fitur presensi tersedia setelah akun Anda diverifikasi oleh Admin Manager.</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Checklist Tugas Resmi Organisasi -->
                <div class="glass rounded-3xl p-6 relative overflow-hidden" x-data="{
                    tasks: [
                        { id: 1, text: 'Hadiri Rapat Persiapan Muscab DPC HPI Pessel di Tarusan', checked: false },
                        { id: 2, text: 'Validasi Data Anggota Baru Armada Darat/Laut/Udara', checked: false },
                        { id: 3, text: 'Ikuti Pelatihan Hospitality Pemanduan Mandeh O2O', checked: false },
                        { id: 4, text: 'Distribusi Kartu Tanda Anggota (KTA) HPI Sementara', checked: false },
                        { id: 5, text: 'Pelaksanaan Musyawarah Cabang (Muscab) - 30 Juni s/d 2 Agustus 2026', checked: false }
                    ],
                    toggleTask(id) {
                        this.tasks = this.tasks.map(t => t.id === id ? { ...t, checked: !t.checked } : t);
                        localStorage.setItem('hpi_tasks_checklist', JSON.stringify(this.tasks));
                    },
                    init() {
                        const saved = localStorage.getItem('hpi_tasks_checklist');
                        if (saved) {
                            this.tasks = JSON.parse(saved);
                        }
                    }
                }">
                    <div class="absolute -top-10 -right-10 w-24 h-24 rounded-full bg-emerald-500/10 pointer-events-none blur-[40px]"></div>
                    <h2 class="text-lg font-bold mb-4 text-slate-300 flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-400">rule</span>
                        Checklist Tugas Resmi
                    </h2>
                    <p class="text-slate-400 text-xs mb-4">Daftar kesiapan & tugas resmi dalam menyelenggarakan agenda Muscab DPC HPI Pessel 2026.</p>
                    
                    <div class="space-y-3">
                        <template x-for="t in tasks" :key="t.id">
                            <label class="flex items-start gap-3 p-3 rounded-2xl glass border-slate-800/40 cursor-pointer hover:bg-slate-900/30 transition">
                                <input type="checkbox" :checked="t.checked" @change="toggleTask(t.id)" class="mt-1 rounded bg-slate-900 border-slate-700 text-sky-500 focus:ring-sky-500">
                                <span class="text-xs text-slate-300 leading-normal" :class="t.checked ? 'line-through text-slate-500' : ''" x-text="t.text"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Right Column: Missions & Reports Form & Check-in History -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Papan Misi & Kegiatan -->
                <div class="glass rounded-3xl p-6 relative overflow-hidden">
                    <h2 class="text-lg font-bold mb-4 text-slate-300 flex items-center gap-2">
                        <span class="material-symbols-outlined text-indigo-400">explore</span>
                        Misi & Agenda Terjadwal
                    </h2>
                    <div class="space-y-3.5 max-h-[400px] overflow-y-auto pr-2">
                        @forelse($missions as $mission)
                            <div class="p-4 glass rounded-2xl border border-slate-850 space-y-3">
                                <div class="flex justify-between items-center gap-2">
                                    <h3 class="font-bold text-slate-200 text-sm">{{ $mission->title }}</h3>
                                    <span class="text-[9px] font-bold px-2 py-0.5 rounded-full border
                                        @if($mission->is_active) bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                                        @elseif($mission->not_started) bg-sky-500/10 text-sky-400 border-sky-500/20
                                        @else bg-slate-850 text-slate-500 border-slate-800 @endif">
                                        @if($mission->is_active) Aktif
                                        @elseif($mission->not_started) Terjadwal
                                        @else Berakhir @endif
                                    </span>
                                </div>
                                <p class="text-slate-400 text-xs leading-relaxed">{{ $mission->description }}</p>
                                
                                <div class="flex justify-between items-center text-[10px] text-slate-500 pt-1">
                                    <span class="flex items-center gap-1 font-semibold text-sky-400">
                                        <span class="material-symbols-outlined text-xs">monetization_on</span>
                                        +{{ $mission->points_reward }} Poin Reputasi
                                    </span>
                                    
                                    @if($mission->not_started)
                                        <div class="flex items-center gap-1 text-slate-400" x-data="countdown('{{ $mission->start_time->toIso8601String() }}')" x-init="start()">
                                            <span>Mulai:</span>
                                            <span class="font-mono font-bold text-sky-400" x-text="timeString">Menghitung...</span>
                                        </div>
                                    @elseif($mission->is_active && $mission->end_time)
                                        <span class="font-mono">Berakhir: {{ $mission->end_time->format('d M Y, H:i') }} WIB</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm text-center py-6">Belum ada misi yang ditugaskan untuk armada Anda.</p>
                        @endforelse
                    </div>

                    @if(!auth()->user()->is_verified)
                        <div class="absolute inset-0 bg-slate-950/90 backdrop-blur-[2px] rounded-3xl flex items-center justify-center p-6 text-center">
                            <div class="space-y-2">
                                <span class="material-symbols-outlined text-amber-500 text-3xl font-bold">lock</span>
                                <h4 class="font-bold text-amber-400 text-sm uppercase tracking-wider">PAPAN MISI DIKUNCI</h4>
                                <p class="text-xs text-slate-400 max-w-[240px] mx-auto">Misi dan pengerjaan kegiatan hanya dapat diakses oleh Agen Terverifikasi.</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Submit Mission Report Form -->
                @php
                    $activeMissions = $missions->filter(fn($m) => $m->is_active);
                @endphp
                @if($activeMissions->count() > 0 && auth()->user()->is_verified)
                    <div class="glass rounded-3xl p-6">
                        <h2 class="text-lg font-bold mb-4 text-slate-300 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sky-400">rate_review</span>
                            Kirim Laporan Misi
                        </h2>
                        <form action="{{ route('vanguard.mission.submit') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase">Pilih Misi Aktif</label>
                                <select name="mission_id" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-xl p-3 focus:outline-none focus:border-sky-500">
                                    @foreach($activeMissions as $mission)
                                        <option value="{{ $mission->id }}">{{ $mission->title }} (+{{ $mission->points_reward }} Poin)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase">Laporan Pelaksanaan Tugas</label>
                                <textarea name="report_text" rows="3" placeholder="Tuliskan secara ringkas dan detail apa saja yang telah Anda kerjakan untuk menyelesaikan misi ini..." class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-xl p-3 focus:outline-none focus:border-sky-500" required></textarea>
                            </div>
                            <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-sky-500 to-indigo-500 text-white font-bold rounded-xl hover:opacity-95 transition shadow-premium text-sm flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-base">send</span>
                                Kirim Laporan Tugas
                            </button>
                        </form>
                    </div>
                @endif

                <!-- Riwayat Presensi (Check-in History) -->
                <div class="glass rounded-3xl p-6">
                    <h2 class="text-lg font-bold mb-4 text-slate-300 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sky-400">history</span>
                        Riwayat Presensi Harian
                    </h2>
                    <div class="space-y-2.5 max-h-48 overflow-y-auto pr-2">
                        @forelse($checkins as $checkin)
                            <div class="p-3 glass rounded-xl border border-slate-850 flex justify-between items-center text-xs">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-emerald-400">check_circle</span>
                                    <span class="text-slate-300 font-medium">{{ $checkin->checkin_date->format('d M Y') }}</span>
                                </div>
                                <span class="px-2.5 py-1 rounded bg-slate-900 text-slate-400 font-semibold border border-slate-800">
                                    {{ $checkin->verified_method == 'face_to_face' ? 'Tatap Muka (Gedung TIC)' : 'Virtual / Live Stream' }}
                                </span>
                            </div>
                        @empty
                            <p class="text-slate-500 text-xs text-center py-6">Belum ada riwayat kehadiran.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
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
                    this.updateTime();
                    this.timer = setInterval(() => this.updateTime(), 1000);
                },
                updateTime() {
                    const now = new Date().getTime();
                    const diff = this.target - now;

                    if (diff <= 0) {
                        this.timeString = 'Mulai Sekarang';
                        clearInterval(this.timer);
                        return;
                    }

                    const days = Math.floor(diff / (1000 * 60 * 65 * 24));
                    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const secs = Math.floor((diff % (1000 * 60)) / 1000);

                    this.timeString = `${days}h ${hours}j ${mins}m ${secs}d`;
                }
            };
        }
    </script>
</body>
</html>
