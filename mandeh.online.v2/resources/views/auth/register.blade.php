<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Calon Anggota DPC HPI Pessel & On-Boarding Platform Mandeh.Online</title>
    <!-- SEO Meta Tags -->
    <meta name="description" content="Bergabunglah sebagai Calon Anggota DPC Himpunan Pramuwisata Indonesia (HPI) Kabupaten Pesisir Selatan. Daftarkan diri Anda secara resmi melalui Mandeh.Online untuk pengembangan kompetensi, sertifikasi, dan jaringan pemandu wisata profesional.">
    <meta name="keywords" content="HPI Pessel, Himpunan Pramuwisata Indonesia, Mandeh.Online, Pendaftaran HPI, Pemandu Wisata Pesisir Selatan, Onboarding Wisata">
    <meta name="author" content="DPC HPI Pessel & Mandeh.Online">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Pendaftaran Calon Anggota DPC HPI Pessel & On-Boarding Platform Mandeh.Online">
    <meta property="og:description" content="Bergabunglah sebagai Calon Anggota DPC Himpunan Pramuwisata Indonesia (HPI) Kabupaten Pesisir Selatan. Daftarkan diri Anda secara resmi melalui Mandeh.Online untuk pengembangan kompetensi, sertifikasi, dan jaringan pemandu wisata profesional.">
    <meta property="og:image" content="{{ asset('images/og-image.png') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="Pendaftaran Calon Anggota DPC HPI Pessel & On-Boarding Platform Mandeh.Online">
    <meta property="twitter:description" content="Bergabunglah sebagai Calon Anggota DPC Himpunan Pramuwisata Indonesia (HPI) Kabupaten Pesisir Selatan. Daftarkan diri Anda secara resmi melalui Mandeh.Online untuk pengembangan kompetensi, sertifikasi, dan jaringan pemandu wisata profesional.">
    <meta property="twitter:image" content="{{ asset('images/og-image.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link href="https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css" rel="stylesheet" />
    <script src="https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top right, #0a0e22 0%, #030408 100%);
        }
        .glass {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px rgba(255, 255, 255, 0.06) solid;
        }
        .glass-active {
            background: rgba(56, 189, 248, 0.06);
            border: 1px rgba(56, 189, 248, 0.3) solid;
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.1);
        }
        .gradient-text {
            background: linear-gradient(135deg, #f8fafc 0%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .glow-button {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            box-shadow: 0 0 15px rgba(2, 132, 199, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glow-button:hover:not(:disabled) {
            box-shadow: 0 0 25px rgba(56, 189, 248, 0.5);
            transform: translateY(-2px);
        }
        .glow-button:active:not(:disabled) {
            transform: translateY(0);
        }
        html, body {
            overflow-x: hidden;
            width: 100%;
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
<body class="text-slate-100 min-h-screen flex flex-col justify-start md:justify-center items-center py-8 px-4 overflow-x-hidden">
    <div class="max-w-2xl w-full mx-auto glass rounded-[32px] p-6 md:p-8 shadow-2xl relative" 
         x-data="registrationState()" x-init="initAddressData()">
        
        <!-- Interactive Terms & HPI Information Modal -->
        <div x-show="showTermsModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/90 backdrop-blur-md"
             x-cloak>
            
            <div class="max-w-xl w-full bg-slate-900 border border-slate-700/60 rounded-[28px] p-6 md:p-8 shadow-2xl relative overflow-hidden flex flex-col max-h-[90vh]">
                <!-- Glow effects inside modal -->
                <div class="absolute -top-20 -right-20 w-48 h-48 rounded-full bg-sky-500/10 blur-[60px] pointer-events-none"></div>
                <div class="absolute -bottom-20 -left-20 w-48 h-48 rounded-full bg-indigo-500/10 blur-[60px] pointer-events-none"></div>

                <!-- Modal Title -->
                <div class="text-center mb-6 shrink-0">
                    <span class="material-symbols-outlined text-sky-400 text-4xl mb-2">assignment_ind</span>
                    <h2 class="text-xl md:text-2xl font-extrabold text-slate-100 tracking-tight">Syarat & Ketentuan Penggunaan Layanan</h2>
                    <p class="text-xs text-sky-400 font-bold uppercase tracking-wider mt-1">Calon Anggota & Pengurus DPC HPI Pessel 2026-2031</p>
                </div>

                <!-- Modal Content Scrollable -->
                <div class="overflow-y-auto pr-2 space-y-5 text-slate-300 text-sm leading-relaxed mb-6 flex-1 modal-scroll">
                    <!-- Event Info Card -->
                    <div class="p-4 rounded-2xl bg-sky-950/20 border border-sky-500/20">
                        <h4 class="font-bold text-sky-300 text-xs uppercase tracking-wider mb-2 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">event</span>
                            Inisiasi Anggota & Muscab DPC HPI Pessel
                        </h4>
                        <p class="text-xs text-slate-300 mb-1">
                            Penyelenggaraan Muscab dan Inisiasi Anggota DPC HPI Pesisir Selatan secara resmi dilaksanakan di:
                        </p>
                        <div class="flex flex-col gap-1 text-[11px] text-slate-400 font-mono">
                            <div>📍 <strong>Lokasi:</strong> Mandeh, Tarusan, Pesisir Selatan</div>
                            <div>📅 <strong>Tanggal:</strong> 30 Juni s/d 2 Agustus 2026</div>
                        </div>
                    </div>

                    <!-- About HPI -->
                    <div>
                        <h4 class="font-bold text-slate-200 text-xs uppercase tracking-wider mb-1.5">Tentang HPI (Himpunan Pramuwisata Indonesia)</h4>
                        <p class="text-xs text-slate-400">
                            HPI adalah organisasi profesi non-politik yang mewadahi pemandu wisata di Indonesia. DPC HPI Pesisir Selatan berkomitmen penuh memajukan SDM pemanduan pariwisata lokal berkualitas global dengan pendekatan O2O (Online-to-Offline) di kawasan wisata bahari terpadu Mandeh.
                        </p>
                    </div>

                    <!-- Terms List -->
                    <div class="space-y-3">
                        <h4 class="font-bold text-slate-200 text-xs uppercase tracking-wider mb-1.5">Ketentuan & Komitmen Calon Anggota</h4>
                        <div class="space-y-2 text-xs text-slate-400">
                            <div class="flex gap-2">
                                <span class="text-sky-400 font-bold">1.</span>
                                <span>Menyetujui pendaftaran awal di platform Mandeh.Online dengan status <strong>Guest Viewer</strong> selama masa peninjauan berkas oleh Admin Manager.</span>
                            </div>
                            <div class="flex gap-2">
                                <span class="text-sky-400 font-bold">2.</span>
                                <span>Memilih Divisi Armada (Darat & Laut fisik atau Udara digital) sesuai minat dan bersedia mematuhi kuota maksimal masing-masing divisi.</span>
                            </div>
                            <div class="flex gap-2">
                                <span class="text-sky-400 font-bold">3.</span>
                                <span>Menjaga sportivitas, kode etik kepemanduan (Sapta Pesona), serta kelestarian ekosistem bahari Pesisir Selatan.</span>
                            </div>
                            <div class="flex gap-2">
                                <span class="text-sky-400 font-bold">4.</span>
                                <span>Menjamin semua informasi profil, asal keanggotaan, rating pengalaman, dan profesi yang diisi dalam kuisioner ini adalah benar adanya.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action button -->
                <div class="shrink-0">
                    <button type="button" @click="showTermsModal = false"
                            class="w-full py-3.5 bg-gradient-to-r from-sky-400 to-indigo-500 text-white font-bold rounded-2xl hover:opacity-90 transition shadow-premium text-sm flex items-center justify-center gap-2">
                        <span>Saya Setuju & Lanjutkan Registrasi</span>
                        <span class="material-symbols-outlined text-base">arrow_forward</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Glows wrapper to prevent horizontal scrollbar -->
        <div class="absolute inset-0 overflow-hidden rounded-[32px] pointer-events-none z-0">
            <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-sky-500/10 blur-[120px]"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full bg-indigo-500/10 blur-[120px]"></div>
        </div>

        <!-- Progress Bar -->
        <div class="w-full bg-slate-900/60 rounded-full h-1.5 mb-8 flex overflow-hidden border border-slate-800/30">
            <div class="bg-gradient-to-r from-sky-400 to-indigo-500 h-1.5 rounded-full transition-all duration-300"
                 :style="`width: ${(step / 8) * 100}%`"></div>
        </div>

        <!-- Header -->
        <div class="text-center mb-8 relative">
            <span class="px-3.5 py-1 rounded-full text-[10px] font-bold glass border-sky-500/20 text-sky-400 uppercase tracking-widest">
                Registrasi & Onboarding • Langkah <span x-text="step"></span> dari 8
            </span>
            <h1 class="text-2xl md:text-3xl font-extrabold mt-4 mb-1 gradient-text">Pendaftaran Anggota Baru</h1>
            <p class="text-slate-400 text-xs md:text-sm">Bergabunglah sebagai Warga / Pelaku Wisata / Calon Anggota DPC HPI Pessel</p>
        </div>

        @if($errors->any())
            <div class="p-4 mb-6 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>⚠️ {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ url('/register') }}" method="POST" id="registrationForm" enctype="multipart/form-data">
            @csrf

            <!-- Step 1: Account Information -->
            <div x-show="step === 1" x-transition.opacity>
                <h3 class="text-lg font-bold text-slate-200 mb-4 text-center">Informasi Akun Pengguna</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input name="name" type="text" x-model="formData.name" placeholder="Nama Lengkap Anda" 
                               class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-xl p-3.5 focus:outline-none focus:border-sky-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1.5">Alamat Email <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input name="email" type="email" x-model="formData.email" @blur="checkEmailUniqueness()" placeholder="name@email.com" 
                                   class="w-full bg-slate-900 border text-slate-200 text-sm rounded-xl p-3.5 pr-10 focus:outline-none"
                                   :class="emailError ? 'border-red-500' : (emailValid ? 'border-emerald-500' : 'border-slate-700 focus:border-sky-500')">
                            <div class="absolute right-3.5 top-3.5 flex items-center">
                                <template x-if="emailChecking">
                                    <svg class="animate-spin h-5 w-5 text-sky-400" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <template x-if="emailValid && !emailChecking">
                                    <span class="material-symbols-outlined text-emerald-400">check_circle</span>
                                </template>
                                <template x-if="emailError && !emailChecking">
                                    <span class="material-symbols-outlined text-red-400">error</span>
                                </template>
                            </div>
                        </div>
                        <p x-show="emailError" x-text="emailError" class="text-[11px] text-red-400 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1.5">No. Handphone (WhatsApp) <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input name="phone_number" type="text" x-model="formData.phone_number" @blur="checkPhoneUniqueness()" placeholder="08123456789" 
                                   class="w-full bg-slate-900 border text-slate-200 text-sm rounded-xl p-3.5 pr-10 focus:outline-none"
                                   :class="phoneError ? 'border-red-500' : (phoneValid ? 'border-emerald-500' : 'border-slate-700 focus:border-sky-500')">
                            <div class="absolute right-3.5 top-3.5 flex items-center">
                                <template x-if="phoneChecking">
                                    <svg class="animate-spin h-5 w-5 text-sky-400" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <template x-if="phoneValid && !phoneChecking">
                                    <span class="material-symbols-outlined text-emerald-400">check_circle</span>
                                </template>
                                <template x-if="phoneError && !phoneChecking">
                                    <span class="material-symbols-outlined text-red-400">error</span>
                                </template>
                            </div>
                        </div>
                        <p x-show="phoneError" x-text="phoneError" class="text-[11px] text-red-400 mt-1"></p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Password -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase mb-1.5">Kata Sandi <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input name="password" :type="showPassword ? 'text' : 'password'" x-model="formData.password" @input="calculatePasswordStrength()" placeholder="••••••••" 
                                       class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-xl p-3.5 pr-10 focus:outline-none focus:border-sky-500">
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-3.5 top-3.5 text-slate-400 hover:text-slate-200 flex items-center">
                                    <span class="material-symbols-outlined text-xl" x-text="showPassword ? 'visibility_off' : 'visibility'"></span>
                                </button>
                            </div>
                            <!-- Strength Indicator Bar -->
                            <div class="mt-2 space-y-1.5">
                                <div class="flex gap-1 h-1">
                                    <template x-for="i in 4" :key="i">
                                        <div class="flex-1 rounded-full h-full transition-all duration-300"
                                             :class="i <= strengthScore ? strengthColor : 'bg-slate-800'"></div>
                                    </template>
                                </div>
                                <div class="flex justify-between text-[10px]">
                                    <span class="text-slate-400">Kekuatan: <span class="font-bold text-slate-200" x-text="strengthLabel"></span></span>
                                    <span class="text-slate-500">Min. 6 Karakter</span>
                                </div>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase mb-1.5">Konfirmasi Kata Sandi <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input name="password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" x-model="formData.password_confirmation" placeholder="••••••••" 
                                       class="w-full bg-slate-900 border text-slate-200 text-sm rounded-xl p-3.5 pr-10 focus:outline-none"
                                       :class="formData.password_confirmation ? (formData.password === formData.password_confirmation ? 'border-emerald-500' : 'border-red-500') : 'border-slate-700 focus:border-sky-500'">
                                <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3.5 top-3.5 text-slate-400 hover:text-slate-200 flex items-center">
                                    <span class="material-symbols-outlined text-xl" x-text="showConfirmPassword ? 'visibility_off' : 'visibility'"></span>
                                </button>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-[10px]" x-show="formData.password_confirmation">
                                <span class="font-bold flex items-center gap-0.5"
                                      :class="formData.password === formData.password_confirmation ? 'text-emerald-400' : 'text-red-400'">
                                    <span class="material-symbols-outlined text-xs" x-text="formData.password === formData.password_confirmation ? 'check' : 'close'"></span>
                                    <span x-text="formData.password === formData.password_confirmation ? 'Kata sandi cocok' : 'Kata sandi tidak cocok'"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Data Kependudukan & Informasi Resmi HPI -->
            <div x-show="step === 2" x-transition.opacity>
                <h3 class="text-lg font-bold text-slate-200 mb-4 text-center">Data Kependudukan & Informasi HPI</h3>
                
                <div class="space-y-4">
                    <!-- Warning / Informative Banner -->
                    <div class="p-3.5 bg-amber-500/10 border border-amber-500/20 rounded-xl text-xs text-amber-400 leading-relaxed flex items-start gap-2.5">
                        <span class="material-symbols-outlined text-base shrink-0 mt-0.5">warning</span>
                        <div>
                            <strong>Penting:</strong> Data KTP, Selfie, Jenis Kelamin, dan Tanggal Lahir diperlukan secara hukum untuk memverifikasi keabsahan keanggotaan profesi Pramuwisata (HPI) sesuai regulasi pariwisata nasional, mencegah duplikasi akun, serta memastikan keaslian kepemilikan lisensi kepemanduan Anda.
                        </div>
                    </div>

                    <!-- HPI Pessel Platform Statement -->
                    <div class="p-3.5 bg-sky-500/10 border border-sky-500/20 rounded-xl text-xs text-sky-300 leading-relaxed flex items-start gap-2.5">
                        <span class="material-symbols-outlined text-base shrink-0 mt-0.5">gavel</span>
                        <div>
                            <strong>Platform Resmi:</strong> Mandeh.online merupakan platform resmi inisiasi DPC HPI Pessel, menggabungkan kekuatan pasukan Darat/Laut (fisik) dan Udara (siber). Menjawab kebutuhan zaman melalui digitalisasi dengan pola <strong>satu komando, satu tujuan</strong>.
                        </div>
                    </div>

                    <!-- Gender Input -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Jenis Kelamin <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="p-3 rounded-xl glass cursor-pointer text-center block transition hover:border-slate-700"
                                   :class="formData.gender === 'L' ? 'glass-active border-sky-500/30' : ''">
                                <input type="radio" name="gender" value="L" x-model="formData.gender" class="sr-only">
                                <span class="font-bold text-xs">Laki-laki</span>
                            </label>
                            <label class="p-3 rounded-xl glass cursor-pointer text-center block transition hover:border-slate-700"
                                   :class="formData.gender === 'P' ? 'glass-active border-sky-500/30' : ''">
                                <input type="radio" name="gender" value="P" x-model="formData.gender" class="sr-only">
                                <span class="font-bold text-xs">Perempuan</span>
                            </label>
                        </div>
                    </div>

                    <!-- Date of Birth Input -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1.5">Tanggal Lahir <span class="text-rose-500">*</span></label>
                        <input name="date_of_birth" type="date" x-model="formData.date_of_birth"
                               class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-xl p-3.5 focus:outline-none focus:border-sky-500">
                    </div>
                </div>
            </div>

            <!-- Step 3: HPI Membership & Division Selection -->
            <div x-show="step === 3" x-transition.opacity>
                <h3 class="text-lg font-bold text-slate-200 mb-4 text-center">Profil & Otoritas HPI</h3>
                
                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Kategori Profil Anda</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="p-4 rounded-xl glass cursor-pointer text-center block transition hover:border-slate-700"
                                   :class="formData.profile_type === 'warga' ? 'glass-active border-sky-500/30' : ''">
                                <input type="radio" name="profile_type" value="warga" x-model="formData.profile_type" class="sr-only">
                                <span class="font-bold text-sm block">Warga Lokal</span>
                            </label>
                            <label class="p-4 rounded-xl glass cursor-pointer text-center block transition hover:border-slate-700"
                                   :class="formData.profile_type === 'pelaku_wisata' ? 'glass-active border-sky-500/30' : ''">
                                <input type="radio" name="profile_type" value="pelaku_wisata" x-model="formData.profile_type" class="sr-only">
                                <span class="font-bold text-sm block">Pelaku Pariwisata</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Ajukan Diri Sebagai Calon Anggota HPI?</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="p-4 rounded-xl glass cursor-pointer text-center block transition hover:border-slate-700"
                                   :class="formData.is_hpi_candidate === '1' ? 'glass-active border-sky-500/30' : ''">
                                <input type="radio" name="is_hpi_candidate" value="1" x-model="formData.is_hpi_candidate" class="sr-only">
                                <span class="font-bold text-sm block">Ya, Daftar HPI</span>
                            </label>
                            <label class="p-4 rounded-xl glass cursor-pointer text-center block transition hover:border-slate-700"
                                   :class="formData.is_hpi_candidate === '0' ? 'glass-active border-sky-500/30' : ''">
                                <input type="radio" name="is_hpi_candidate" value="0" x-model="formData.is_hpi_candidate" class="sr-only">
                                <span class="font-bold text-sm block">Tidak, Profil Biasa</span>
                            </label>
                        </div>
                    </div>

                    <!-- Division Choice -->
                    <div x-show="formData.is_hpi_candidate === '1'" x-transition>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Pilih Divisi Armada Anda</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="p-4 rounded-xl glass cursor-pointer flex flex-col justify-between transition hover:border-slate-700"
                                   :class="formData.armada === 'darat_laut' ? 'glass-active border-sky-500/30' : ''">
                                <input type="radio" name="armada" value="darat_laut" x-model="formData.armada" class="sr-only">
                                <div>
                                    <span class="font-bold text-sm text-slate-200 block">Darat & Laut (Fisik)</span>
                                    <span class="text-[10px] text-slate-400 mt-1 block">Kader lapangan, nakhoda boat, pemanduan fisik (Maks. 50)</span>
                                </div>
                            </label>
                            <label class="p-4 rounded-xl glass cursor-pointer flex flex-col justify-between transition hover:border-slate-700"
                                   :class="formData.armada === 'udara' ? 'glass-active border-sky-500/30' : ''">
                                <input type="radio" name="armada" value="udara" x-model="formData.armada" class="sr-only">
                                <div>
                                    <span class="font-bold text-sm text-slate-200 block">Udara (Digital/Siber)</span>
                                    <span class="text-[10px] text-slate-400 mt-1 block">Pegiat medsos, content creator, WiFi Hub (Maks. 100)</span>
                                </div>
                            </label>
                        </div>

                        <!-- Dynamic Jobdesc Details -->
                        <div x-show="formData.armada" x-transition class="mt-4 p-4 rounded-2xl bg-sky-950/20 border border-sky-500/10 text-[11px] text-slate-300">
                            <h4 class="font-bold text-sky-400 mb-2 uppercase tracking-wider">Tugas & Tanggung Jawab (Jobdesc) Divisi</h4>
                            <div x-show="formData.armada === 'darat_laut'">
                                <ul class="list-disc pl-4 space-y-1 text-slate-400">
                                    <li>Memandu wisatawan secara langsung di pantai, pulau, geopark Mandeh.</li>
                                    <li>Mengemudikan boat wisata dengan protokol keselamatan pelampung & rescue.</li>
                                    <li>Menjaga kelestarian lingkungan pesisir dari polusi sampah.</li>
                                </ul>
                            </div>
                            <div x-show="formData.armada === 'udara'">
                                <ul class="list-disc pl-4 space-y-1 text-slate-400">
                                    <li>Mempromosikan keindahan alam Mandeh secara kreatif lewat foto/video siber.</li>
                                    <li>Mengelola hotspot WiFi Hub untuk kebutuhan akses informasi turis.</li>
                                    <li>Mengampanyekan kesadaran lingkungan secara online.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Detailed Address Information -->
            <div x-show="step === 4" x-transition.opacity>
                <h3 class="text-lg font-bold text-slate-200 mb-4 text-center">Detail Alamat Tempat Tinggal</h3>
                <div class="space-y-4 text-slate-300">
                    <!-- Provinsi -->
                    <div class="relative" @click.away="provOpen = false">
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1.5">Provinsi <span class="text-rose-500">*</span></label>
                        <button type="button" @click="provOpen = !provOpen"
                                class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-xl p-3.5 text-left flex justify-between items-center focus:outline-none focus:border-sky-500">
                            <span x-text="formData.provinsi || 'Pilih Provinsi...'"></span>
                            <span class="material-symbols-outlined transition-transform duration-200 text-slate-400" :class="provOpen ? 'rotate-180' : ''">expand_more</span>
                        </button>
                        
                        <div x-show="provOpen" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute z-30 w-full mt-2 bg-slate-950 border border-slate-800 rounded-xl shadow-2xl overflow-hidden max-h-60 flex flex-col"
                             x-cloak>
                            <div class="p-2 border-b border-slate-800 sticky top-0 bg-slate-950 z-10">
                                <div class="relative">
                                    <input type="text" x-model="provSearch" placeholder="Cari Provinsi..." 
                                           class="w-full bg-slate-900 border border-slate-800 text-slate-200 text-xs rounded-lg p-2 pl-8 focus:outline-none focus:border-sky-500">
                                    <span class="material-symbols-outlined absolute left-2.5 top-2 text-slate-500 text-sm">search</span>
                                </div>
                            </div>
                            <div class="overflow-y-auto flex-1 py-1">
                                <template x-for="p in filteredProvinces" :key="p.id">
                                    <button type="button" @click="selectProvince(p)"
                                            class="w-full text-left px-4 py-2.5 text-xs text-slate-300 hover:bg-sky-500/10 hover:text-sky-400 transition flex items-center justify-between">
                                        <span x-text="p.name"></span>
                                        <span class="material-symbols-outlined text-xs text-sky-400" x-show="p.id === selectedProvinceId">check</span>
                                    </button>
                                </template>
                                <div x-show="filteredProvinces.length === 0" class="px-4 py-3 text-xs text-slate-500 text-center">
                                    Provinsi tidak ditemukan
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="provinsi" :value="formData.provinsi">
                    </div>

                    <!-- Kabupaten / Kota -->
                    <div class="relative" @click.away="regOpen = false">
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1.5">Kabupaten / Kota <span class="text-rose-500">*</span></label>
                        <button type="button" @click="regOpen = !regOpen" :disabled="!selectedProvinceId"
                                class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-xl p-3.5 text-left flex justify-between items-center focus:outline-none focus:border-sky-500 disabled:opacity-40 disabled:cursor-not-allowed">
                            <span x-text="formData.kabupaten || 'Pilih Kabupaten / Kota...'"></span>
                            <span class="material-symbols-outlined transition-transform duration-200 text-slate-400" :class="regOpen ? 'rotate-180' : ''">expand_more</span>
                        </button>
                        
                        <div x-show="regOpen && selectedProvinceId" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute z-20 w-full mt-2 bg-slate-950 border border-slate-800 rounded-xl shadow-2xl overflow-hidden max-h-60 flex flex-col"
                             x-cloak>
                            <div class="p-2 border-b border-slate-800 sticky top-0 bg-slate-950 z-10">
                                <div class="relative">
                                    <input type="text" x-model="regSearch" placeholder="Cari Kabupaten/Kota..." 
                                           class="w-full bg-slate-900 border border-slate-800 text-slate-200 text-xs rounded-lg p-2 pl-8 focus:outline-none focus:border-sky-500">
                                    <span class="material-symbols-outlined absolute left-2.5 top-2 text-slate-500 text-sm">search</span>
                                </div>
                            </div>
                            <div class="overflow-y-auto flex-1 py-1">
                                <template x-for="r in filteredRegencies" :key="r.id">
                                    <button type="button" @click="selectRegency(r)"
                                            class="w-full text-left px-4 py-2.5 text-xs text-slate-300 hover:bg-sky-500/10 hover:text-sky-400 transition flex items-center justify-between">
                                        <span x-text="r.name"></span>
                                        <span class="material-symbols-outlined text-xs text-sky-400" x-show="r.id === selectedRegencyId">check</span>
                                    </button>
                                </template>
                                <div x-show="filteredRegencies.length === 0" class="px-4 py-3 text-xs text-slate-500 text-center">
                                    Kabupaten/Kota tidak ditemukan
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="kabupaten" :value="formData.kabupaten">
                    </div>

                    <!-- Kecamatan -->
                    <div class="relative" @click.away="distOpen = false">
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1.5">Kecamatan <span class="text-rose-500">*</span></label>
                        <button type="button" @click="distOpen = !distOpen" :disabled="!selectedRegencyId"
                                class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-xl p-3.5 text-left flex justify-between items-center focus:outline-none focus:border-sky-500 disabled:opacity-40 disabled:cursor-not-allowed">
                            <span x-text="formData.kecamatan || 'Pilih Kecamatan...'"></span>
                            <span class="material-symbols-outlined transition-transform duration-200 text-slate-400" :class="distOpen ? 'rotate-180' : ''">expand_more</span>
                        </button>
                        
                        <div x-show="distOpen && selectedRegencyId" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute z-10 w-full mt-2 bg-slate-950 border border-slate-800 rounded-xl shadow-2xl overflow-hidden max-h-60 flex flex-col"
                             x-cloak>
                            <div class="p-2 border-b border-slate-800 sticky top-0 bg-slate-950 z-10">
                                <div class="relative">
                                    <input type="text" x-model="distSearch" placeholder="Cari Kecamatan..." 
                                           class="w-full bg-slate-900 border border-slate-800 text-slate-200 text-xs rounded-lg p-2 pl-8 focus:outline-none focus:border-sky-500">
                                    <span class="material-symbols-outlined absolute left-2.5 top-2 text-slate-500 text-sm">search</span>
                                </div>
                            </div>
                            <div class="overflow-y-auto flex-1 py-1">
                                <template x-for="d in filteredDistricts" :key="d.id">
                                    <button type="button" @click="selectDistrict(d)"
                                            class="w-full text-left px-4 py-2.5 text-xs text-slate-300 hover:bg-sky-500/10 hover:text-sky-400 transition flex items-center justify-between">
                                        <span x-text="d.name"></span>
                                        <span class="material-symbols-outlined text-xs text-sky-400" x-show="d.id === selectedDistrictId">check</span>
                                    </button>
                                </template>
                                <div x-show="filteredDistricts.length === 0" class="px-4 py-3 text-xs text-slate-500 text-center">
                                    Kecamatan tidak ditemukan
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="kecamatan" :value="formData.kecamatan">
                    </div>

                    <!-- Nagari / Desa / Kelurahan -->
                    <div class="relative" @click.away="vilOpen = false">
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1.5">Nagari / Desa / Kelurahan <span class="text-rose-500">*</span></label>
                        <button type="button" @click="vilOpen = !vilOpen" :disabled="!selectedDistrictId"
                                class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-xl p-3.5 text-left flex justify-between items-center focus:outline-none focus:border-sky-500 disabled:opacity-40 disabled:cursor-not-allowed">
                            <span x-text="formData.nagari || 'Pilih Nagari / Desa...'"></span>
                            <span class="material-symbols-outlined transition-transform duration-200 text-slate-400" :class="vilOpen ? 'rotate-180' : ''">expand_more</span>
                        </button>
                        
                        <div x-show="vilOpen && selectedDistrictId" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute z-10 w-full mt-2 bg-slate-950 border border-slate-800 rounded-xl shadow-2xl overflow-hidden max-h-60 flex flex-col"
                             x-cloak>
                            <div class="p-2 border-b border-slate-800 sticky top-0 bg-slate-950 z-10">
                                <div class="relative">
                                    <input type="text" x-model="vilSearch" placeholder="Cari Nagari/Desa..." 
                                           class="w-full bg-slate-900 border border-slate-800 text-slate-200 text-xs rounded-lg p-2 pl-8 focus:outline-none focus:border-sky-500">
                                    <span class="material-symbols-outlined absolute left-2.5 top-2 text-slate-500 text-sm">search</span>
                                </div>
                            </div>
                            <div class="overflow-y-auto flex-1 py-1">
                                <template x-for="v in filteredVillages" :key="v.id">
                                    <button type="button" @click="selectVillage(v)"
                                            class="w-full text-left px-4 py-2.5 text-xs text-slate-300 hover:bg-sky-500/10 hover:text-sky-400 transition flex items-center justify-between">
                                        <span x-text="v.name"></span>
                                        <span class="material-symbols-outlined text-xs text-sky-400" x-show="v.id === selectedVillageId">check</span>
                                    </button>
                                </template>
                                <div x-show="filteredVillages.length === 0" class="px-4 py-3 text-xs text-slate-500 text-center">
                                    Nagari/Desa tidak ditemukan
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="nagari" :value="formData.nagari">
                    </div>

                    <!-- Kode Pos & Alamat Lengkap -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1.5">Kode Pos <span class="text-slate-500 text-[10px] lowercase italic">(opsional)</span></label>
                        <input name="postal_code" type="text" x-model="formData.postal_code" placeholder="Contoh: 25654" 
                               class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-xl p-3.5 focus:outline-none focus:border-sky-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1.5">Alamat Lengkap (Jalan, No. Rumah, RT/RW) <span class="text-rose-500">*</span></label>
                        <textarea name="full_address" x-model="formData.full_address" rows="3" placeholder="Tuliskan alamat lengkap domisili Anda..." 
                                  class="w-full bg-slate-950/40 border border-slate-700 text-slate-200 text-sm rounded-xl p-3.5 focus:outline-none focus:border-sky-500"></textarea>
                    </div>
                </div>
            </div>

            <!-- Step 5: GPS Location Map (Leaflet) -->
            <div x-show="step === 5" x-transition.opacity x-init="$watch('step', val => { if (val === 5) { initLeafletMap(); } })">
                <h3 class="text-lg font-bold text-slate-200 mb-2 text-center">Titik Koordinat GPS</h3>
                <p class="text-xs text-slate-400 mb-6 text-center">Geser penanda (marker) atau klik pada peta untuk menentukan lokasi presisi domisili Anda di kawasan Mandeh.</p>
                
                <div class="space-y-4">
                    <div id="map" class="w-full h-64 rounded-2xl border border-slate-700 bg-slate-950/80 shadow-inner z-10" style="min-height: 250px;"></div>
                    
                    <div class="flex flex-col items-center justify-center py-2">
                        <button type="button" @click="requestDeviceLocation()" class="flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white font-bold text-xs rounded-xl transition shadow-lg shadow-sky-500/20 w-full sm:w-auto">
                            <template x-if="gpsLoading">
                                <span class="material-symbols-outlined animate-spin text-sm">sync</span>
                            </template>
                            <template x-if="!gpsLoading">
                                <span class="material-symbols-outlined text-sm">my_location</span>
                            </template>
                            <span x-text="gpsLoading ? 'Menyinkronkan GPS...' : 'Sinkronkan GPS Handphone'"></span>
                        </button>
                        
                        <div x-show="gpsError" class="mt-3 text-rose-400 text-xs font-semibold text-center bg-rose-500/10 border border-rose-500/20 rounded-xl p-2.5 w-full" x-text="gpsError"></div>
                        <div x-show="gpsPermissionGranted && !gpsError" class="mt-3 text-emerald-400 text-xs font-semibold text-center bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-2.5 w-full">
                            ✓ Akses GPS Chrome Diizinkan. Lokasi Anda valid dan tersinkronisasi.
                        </div>
                        <div x-show="!gpsPermissionGranted && !gpsError" class="mt-3 text-amber-400 text-xs font-medium text-center bg-amber-500/10 border border-amber-500/20 rounded-xl p-2.5 w-full">
                            ⚠️ Anda HARUS mengizinkan akses GPS perangkat di browser Chrome agar dapat melanjutkan pendaftaran.
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Latitude (Lintang)</label>
                            <input type="text" name="latitude" x-model="formData.latitude" readonly required
                                   class="w-full bg-slate-955 border border-slate-800 text-slate-300 text-xs rounded-xl p-3.5 focus:outline-none font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Longitude (Bujur)</label>
                            <input type="text" name="longitude" x-model="formData.longitude" readonly required
                                   class="w-full bg-slate-955 border border-slate-800 text-slate-300 text-xs rounded-xl p-3.5 focus:outline-none font-mono">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 6: Origin, Profession & Experience Rating -->
            <div x-show="step === 6" x-transition.opacity>
                <h3 class="text-lg font-bold text-slate-200 mb-4 text-center">Asal Keanggotaan & Profesi</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Asal Keanggotaan</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="p-3 rounded-xl glass cursor-pointer text-center block transition hover:border-slate-700"
                                   :class="formData.origin === 'lokal' ? 'glass-active border-sky-500/30' : ''">
                                <input type="radio" name="origin" value="lokal" x-model="formData.origin" class="sr-only">
                                <span class="font-bold text-xs">Lokal (Domisili Mandeh)</span>
                            </label>
                            <label class="p-3 rounded-xl glass cursor-pointer text-center block transition hover:border-slate-700"
                                   :class="formData.origin === 'perantau' ? 'glass-active border-sky-500/30' : ''">
                                <input type="radio" name="origin" value="perantau" x-model="formData.origin" class="sr-only">
                                <span class="font-bold text-xs">Perantau (Diaspora Minang)</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Profesi Utama / Jabatan <span class="text-rose-500">*</span></label>
                        <select name="profession" x-model="formData.profession"
                                class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-xl p-3.5 focus:outline-none focus:border-sky-500">
                            <option value="">Pilih Profesi...</option>
                            <template x-if="formData.profile_type === 'warga'">
                                <optgroup label="Profesi Warga">
                                    <option value="Nelayan">Nelayan</option>
                                    <option value="Petani">Petani / Berkebun</option>
                                    <option value="Wiraswasta">Wiraswasta</option>
                                    <option value="Pegawai Swasta">Pegawai Swasta</option>
                                    <option value="Pegawai Negeri (PNS)">Pegawai Negeri (PNS)</option>
                                    <option value="Pelajar / Mahasiswa">Pelajar / Mahasiswa</option>
                                    <option value="Profesional / Konsultan">Profesional / Konsultan</option>
                                    <option value="Lainnya">Lainnya</option>
                                </optgroup>
                            </template>
                            <template x-if="formData.profile_type === 'pelaku_wisata'">
                                <optgroup label="Profesi Pariwisata">
                                    <option value="Sopir Boat Wisata">Sopir Boat Wisata</option>
                                    <option value="Tour Guide / Pemandu Wisata">Tour Guide / Pemandu Wisata</option>
                                    <option value="Pengelola Homestay / Penginapan">Pengelola Homestay / Penginapan</option>
                                    <option value="Staff / Pemilik Cafe & Restoran">Staff / Pemilik Cafe & Restoran</option>
                                    <option value="Pengrajin Produk Lokal">Pengrajin Produk Lokal</option>
                                    <option value="Instruktur Diving / Watersport">Instruktur Diving / Watersport</option>
                                    <option value="Profesional / Konsultan">Profesional / Konsultan</option>
                                    <option value="Lainnya">Lainnya</option>
                                </optgroup>
                            </template>
                        </select>
                        
                        <div class="mt-2" x-show="formData.profession === 'Lainnya'">
                            <input type="text" x-model="formData.profession_other" placeholder="Tuliskan profesi Anda..."
                                   class="w-full bg-slate-900 border border-slate-750 text-slate-200 text-sm rounded-xl p-3.5 focus:outline-none focus:border-sky-500">
                        </div>

                        <div class="mt-3">
                            <label class="block text-xs font-semibold text-slate-400 uppercase mb-1.5">Detail Pekerjaan / Nama Perusahaan <span class="text-slate-500 text-[10px] lowercase italic">(opsional)</span></label>
                            <input type="text" name="profession_detail" x-model="formData.profession_detail" placeholder="Contoh: Konsultan IT, PT. Maju Bersama" 
                                   class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-sm rounded-xl p-3.5 focus:outline-none focus:border-sky-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2 text-center">Tingkat Pengalaman Anda</label>
                        <div class="flex justify-center gap-3 py-2">
                            <template x-for="star in 5" :key="star">
                                <button type="button" @click="formData.initial_rating = star"
                                        class="text-3xl md:text-4xl transition duration-200 hover:scale-125 focus:outline-none"
                                        :class="star <= formData.initial_rating ? 'text-amber-400' : 'text-slate-700'">
                                    <span class="material-symbols-outlined text-[36px] md:text-[42px]"
                                          :style="`font-variation-settings: 'FILL' ${star <= formData.initial_rating ? 1 : 0};`"
                                          x-text="star <= formData.initial_rating ? 'star' : 'star_rate'"></span>
                                </button>
                            </template>
                        </div>
                        <div class="text-center p-3 rounded-xl bg-slate-950/40 border border-slate-900 text-xs text-slate-400 mt-1">
                            <span class="text-xs text-sky-400 font-bold uppercase tracking-wider block mb-1" x-text="getRatingLabel().title"></span>
                            <span x-text="getRatingLabel().description"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 7: Upload Selfie & KTP -->
            <div x-show="step === 7" x-transition.opacity>
                <h3 class="text-lg font-bold text-slate-200 mb-4 text-center">Unggah Dokumen Pendukung</h3>
                <p class="text-xs text-slate-400 mb-6 text-center">Silakan unggah foto selfie terbaru dan foto KTP Anda untuk keperluan validasi identitas.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Selfie Upload -->
                    <div class="p-5 rounded-2xl glass border border-slate-800 hover:border-slate-700 transition flex flex-col items-center gap-3">
                        <span class="material-symbols-outlined text-sky-400 text-3xl">photo_camera</span>
                        <span class="font-bold text-slate-200 text-sm">Foto Selfie</span>
                        <p class="text-[10px] text-slate-400 text-center">Unggah foto selfie wajah tegak, jelas, dan tanpa penutup wajah.</p>
                        
                        <label class="w-full py-2 bg-slate-900 border border-slate-700 hover:border-sky-500/50 text-slate-300 font-semibold text-xs rounded-xl text-center cursor-pointer transition">
                            Pilih Foto Selfie
                            <input type="file" name="selfie" accept="image/*" capture="user" @change="handleSelfieUpload($event)" class="hidden">
                        </label>
                        
                        <!-- Preview -->
                        <div x-show="selfiePreview" class="w-full h-32 mt-2 rounded-xl overflow-hidden border border-slate-800 bg-slate-950 flex items-center justify-center relative">
                            <img :src="selfiePreview" class="w-full h-full object-cover">
                            <button type="button" @click="selfieFile = null; selfiePreview = ''; selfieError = ''" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition flex items-center justify-center">
                                <span class="material-symbols-outlined text-xs">close</span>
                            </button>
                        </div>
                        
                        <!-- Error Message -->
                        <div x-show="selfieError" class="text-rose-500 text-xs font-semibold text-center mt-2 p-2.5 bg-rose-500/10 border border-rose-500/20 rounded-xl w-full" x-text="selfieError"></div>
                    </div>

                    <!-- KTP Upload -->
                    <div class="p-5 rounded-2xl glass border border-slate-800 hover:border-slate-700 transition flex flex-col items-center gap-3">
                        <span class="material-symbols-outlined text-sky-400 text-3xl">credit_card</span>
                        <span class="font-bold text-slate-200 text-sm">Foto KTP</span>
                        <p class="text-[10px] text-slate-400 text-center">Pastikan foto KTP terbaca dengan jelas (tidak buram/terpotong).</p>
                        
                        <label class="w-full py-2 bg-slate-900 border border-slate-700 hover:border-sky-500/50 text-slate-300 font-semibold text-xs rounded-xl text-center cursor-pointer transition">
                            Pilih Foto KTP
                            <input type="file" name="ktp" accept="image/*" capture="environment" @change="handleKtpUpload($event)" class="hidden">
                        </label>
                        
                        <!-- Preview -->
                        <div x-show="ktpPreview" class="w-full h-32 mt-2 rounded-xl overflow-hidden border border-slate-800 bg-slate-950 flex items-center justify-center relative">
                            <img :src="ktpPreview" class="w-full h-full object-cover">
                            <button type="button" @click="ktpFile = null; ktpPreview = ''; ktpError = ''" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition flex items-center justify-center">
                                <span class="material-symbols-outlined text-xs">close</span>
                            </button>
                        </div>
                        
                        <!-- Error Message -->
                        <div x-show="ktpError" class="text-rose-500 text-xs font-semibold text-center mt-2 p-2.5 bg-rose-500/10 border border-rose-500/20 rounded-xl w-full" x-text="ktpError"></div>
                    </div>
                </div>
            </div>

            <!-- Step 8: Commitment & Reason to Join -->
            <div x-show="step === 8" x-transition.opacity>
                <h3 class="text-lg font-bold text-slate-200 mb-4 text-center">Alasan & Komitmen Bergabung</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1.5">Alasan Mengajukan Diri Menjadi Anggota HPI <span class="text-rose-500">*</span></label>
                        <textarea name="join_reason" x-model="formData.join_reason" rows="3" placeholder="Mengapa Anda tertarik bergabung dengan organisasi DPC HPI Pessel?" 
                                  class="w-full bg-slate-955 border border-slate-750 text-slate-200 text-sm rounded-xl p-3.5 focus:outline-none focus:border-sky-500"></textarea>
                    </div>

                    <div class="space-y-2.5 p-4 rounded-xl bg-slate-950/40 border border-slate-900/60 text-xs text-slate-300">
                        <h4 class="font-bold text-slate-200 uppercase tracking-wider mb-1">Ringkasan Pendaftaran:</h4>
                        <div class="flex justify-between border-b border-slate-900 pb-1">
                            <span class="text-slate-400">Nama Lengkap:</span>
                            <span class="font-bold text-slate-200" x-text="formData.name"></span>
                        </div>
                        <div class="flex justify-between border-b border-slate-900 pb-1">
                            <span class="text-slate-400">Email:</span>
                            <span class="font-mono text-slate-200" x-text="formData.email"></span>
                        </div>
                        <div class="flex justify-between border-b border-slate-900 pb-1">
                            <span class="text-slate-400">Kategori Profil:</span>
                            <span class="font-bold text-sky-400 uppercase" x-text="formData.profile_type === 'warga' ? 'Warga Lokal' : 'Pelaku Wisata'"></span>
                        </div>
                        <div class="flex justify-between border-b border-slate-900 pb-1">
                            <span class="text-slate-400">Ajukan HPI:</span>
                            <span class="font-bold" :class="formData.is_hpi_candidate === '1' ? 'text-emerald-400' : 'text-slate-500'" x-text="formData.is_hpi_candidate === '1' ? 'Ya (Vanguard)' : 'Tidak'"></span>
                        </div>
                        <div class="flex justify-between border-b border-slate-900 pb-1" x-show="formData.is_hpi_candidate === '1'">
                            <span class="text-slate-400">Armada Divisi:</span>
                            <span class="font-bold text-slate-200" x-text="formData.armada === 'darat_laut' ? 'Darat & Laut (Fisik)' : 'Udara (Siber)'"></span>
                        </div>
                        <div class="flex justify-between border-b border-slate-900 pb-1" x-show="formData.latitude">
                            <span class="text-slate-400">Titik Koordinat:</span>
                            <span class="font-mono text-slate-200" x-text="formData.latitude + ', ' + formData.longitude"></span>
                        </div>
                        <!-- Uploaded files status -->
                        <div class="flex justify-between text-emerald-400">
                            <span>Dokumen Unggahan:</span>
                            <span class="font-bold">Selfie & KTP Siap</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="mt-8 flex justify-between gap-4 relative">
                <button type="button" @click="prevStep()" x-show="step > 1"
                        class="px-5 py-3 rounded-xl glass border-slate-700 text-slate-300 font-semibold text-sm hover:bg-slate-800 transition">
                    Kembali
                </button>
                
                <button type="button" @click="nextStep()" x-show="step < 8" :disabled="!isStepValid()"
                        class="ml-auto px-6 py-3 rounded-xl glow-button text-white font-bold text-sm transition disabled:opacity-40 disabled:cursor-not-allowed">
                    Lanjut
                </button>

                <button type="button" @click="submitRegistration()" x-show="step === 8" :disabled="!isStepValid()"
                        class="ml-auto px-8 py-3 rounded-xl glow-button text-white font-bold text-sm transition disabled:opacity-40">
                    Daftar Sekarang
                </button>
            </div>
        </form>

        <div class="mt-6 text-center text-xs text-slate-400">
            Sudah punya akun? <a href="{{ url('/login') }}" class="text-sky-400 font-bold hover:underline">Masuk</a>
        </div>
    </div>

    <script>
        function registrationState() {
            return {
                showTermsModal: true,
                step: 1,
                showPassword: false,
                showConfirmPassword: false,
                emailError: '',
                phoneError: '',
                emailChecking: false,
                phoneChecking: false,
                emailValid: false,
                phoneValid: false,
                strengthScore: 0,
                strengthLabel: 'Sangat Lemah',
                strengthColor: 'bg-red-500/20',
                provinces: [],
                regencies: [],
                districts: [],
                villages: [],
                selectedProvinceId: '',
                selectedRegencyId: '',
                selectedDistrictId: '',
                selectedVillageId: '',
                provOpen: false,
                provSearch: '',
                regOpen: false,
                regSearch: '',
                distOpen: false,
                distSearch: '',
                vilOpen: false,
                vilSearch: '',
                leafletMap: null,
                leafletMarker: null,
                gpsPermissionGranted: false,
                gpsLoading: false,
                gpsError: '',
                formData: {
                    name: '',
                    email: '',
                    phone_number: '',
                    password: '',
                    password_confirmation: '',
                    gender: '',
                    date_of_birth: '',
                    profile_type: 'warga',
                    is_hpi_candidate: '1',
                    armada: 'darat_laut',
                    origin: 'lokal',
                    nagari: '',
                    kecamatan: '',
                    kabupaten: '',
                    provinsi: '',
                    postal_code: '',
                    full_address: '',
                    profession: '',
                    profession_other: '',
                    profession_detail: '',
                    initial_rating: 3,
                    join_reason: '',
                    latitude: '',
                    longitude: ''
                },
                initAddressData() {
                    fetch('/api/wilayah/provinces')
                        .then(res => res.json())
                        .then(data => {
                            this.provinces = data;
                        })
                        .catch(err => console.error('Gagal mengambil data provinsi', err));
                },
                get filteredProvinces() {
                    if (!this.provSearch) return this.provinces;
                    return this.provinces.filter(p => p.name.toLowerCase().includes(this.provSearch.toLowerCase()));
                },
                get filteredRegencies() {
                    if (!this.regSearch) return this.regencies;
                    return this.regencies.filter(r => r.name.toLowerCase().includes(this.regSearch.toLowerCase()));
                },
                get filteredDistricts() {
                    if (!this.distSearch) return this.districts;
                    return this.districts.filter(d => d.name.toLowerCase().includes(this.distSearch.toLowerCase()));
                },
                get filteredVillages() {
                    if (!this.vilSearch) return this.villages;
                    return this.villages.filter(v => v.name.toLowerCase().includes(this.vilSearch.toLowerCase()));
                },
                selectProvince(p) {
                    this.selectedProvinceId = p.id;
                    this.provOpen = false;
                    this.provSearch = '';
                    this.onProvinceChange();
                },
                selectRegency(r) {
                    this.selectedRegencyId = r.id;
                    this.regOpen = false;
                    this.regSearch = '';
                    this.onRegencyChange();
                },
                selectDistrict(d) {
                    this.selectedDistrictId = d.id;
                    this.distOpen = false;
                    this.distSearch = '';
                    this.onDistrictChange();
                },
                selectVillage(v) {
                    this.selectedVillageId = v.id;
                    this.vilOpen = false;
                    this.vilSearch = '';
                    this.onVillageChange();
                },
                onProvinceChange() {
                    this.regencies = [];
                    this.districts = [];
                    this.villages = [];
                    this.selectedRegencyId = '';
                    this.selectedDistrictId = '';
                    this.selectedVillageId = '';
                    
                    this.formData.provinsi = '';
                    this.formData.kabupaten = '';
                    this.formData.kecamatan = '';
                    this.formData.nagari = '';

                    if (!this.selectedProvinceId) return;

                    const prov = this.provinces.find(p => p.id === this.selectedProvinceId);
                    if (prov) {
                        this.formData.provinsi = prov.name;
                    }

                    fetch(`/api/wilayah/regencies/${this.selectedProvinceId}`)
                        .then(res => res.json())
                        .then(data => {
                            this.regencies = data;
                        })
                        .catch(err => console.error('Gagal mengambil data kabupaten', err));
                },
                onRegencyChange() {
                    this.districts = [];
                    this.villages = [];
                    this.selectedDistrictId = '';
                    this.selectedVillageId = '';

                    this.formData.kabupaten = '';
                    this.formData.kecamatan = '';
                    this.formData.nagari = '';

                    if (!this.selectedRegencyId) return;

                    const reg = this.regencies.find(r => r.id === this.selectedRegencyId);
                    if (reg) {
                        this.formData.kabupaten = reg.name;
                    }

                    fetch(`/api/wilayah/districts/${this.selectedRegencyId}`)
                        .then(res => res.json())
                        .then(data => {
                            this.districts = data;
                        })
                        .catch(err => console.error('Gagal mengambil data kecamatan', err));
                },
                onDistrictChange() {
                    this.villages = [];
                    this.selectedVillageId = '';

                    this.formData.kecamatan = '';
                    this.formData.nagari = '';

                    if (!this.selectedDistrictId) return;

                    const dist = this.districts.find(d => d.id === this.selectedDistrictId);
                    if (dist) {
                        this.formData.kecamatan = dist.name;
                    }

                    fetch(`/api/wilayah/villages/${this.selectedDistrictId}`)
                        .then(res => res.json())
                        .then(data => {
                            this.villages = data;
                        })
                        .catch(err => console.error('Gagal mengambil data desa', err));
                },
                onVillageChange() {
                    this.formData.nagari = '';
                    if (!this.selectedVillageId) return;

                    const vil = this.villages.find(v => v.id === this.selectedVillageId);
                    if (vil) {
                        this.formData.nagari = vil.name;
                    }
                },
                calculatePasswordStrength() {
                    const pass = this.formData.password;
                    if (!pass) {
                        this.strengthScore = 0;
                        this.strengthLabel = 'Sangat Lemah';
                        this.strengthColor = 'bg-red-500/20';
                        return;
                    }
                    let score = 0;
                    if (pass.length >= 6) score++;
                    if (/[A-Z]/.test(pass)) score++;
                    if (/[0-9]/.test(pass)) score++;
                    if (/[^A-Za-z0-9]/.test(pass)) score++;

                    this.strengthScore = score;
                    if (score === 1) {
                        this.strengthLabel = 'Lemah';
                        this.strengthColor = 'bg-red-500';
                    } else if (score === 2) {
                        this.strengthLabel = 'Sedang';
                        this.strengthColor = 'bg-amber-500';
                    } else if (score === 3) {
                        this.strengthLabel = 'Kuat';
                        this.strengthColor = 'bg-emerald-500';
                    } else if (score === 4) {
                        this.strengthLabel = 'Sangat Kuat';
                        this.strengthColor = 'bg-sky-400';
                    } else {
                        this.strengthLabel = 'Sangat Lemah';
                        this.strengthColor = 'bg-red-500/20';
                    }
                },
                checkEmailUniqueness() {
                    const email = this.formData.email.trim();
                    if (!email || !email.includes('@')) {
                        this.emailError = 'Format email tidak valid';
                        this.emailValid = false;
                        return;
                    }
                    this.emailChecking = true;
                    this.emailError = '';
                    
                    fetch(`/check-uniqueness?email=${encodeURIComponent(email)}`)
                    .then(res => res.json())
                    .then(data => {
                        this.emailChecking = false;
                        if (data.email_exists) {
                            this.emailError = 'Email sudah terdaftar di sistem';
                            this.emailValid = false;
                        } else {
                            this.emailError = '';
                            this.emailValid = true;
                        }
                    })
                    .catch(err => {
                        this.emailChecking = false;
                    });
                },
                checkPhoneUniqueness() {
                    const phone = this.formData.phone_number.trim();
                    if (!phone || phone.length < 9) {
                        this.phoneError = 'Nomor HP minimal 9 karakter';
                        this.phoneValid = false;
                        return;
                    }
                    this.phoneChecking = true;
                    this.phoneError = '';

                    fetch(`/check-uniqueness?phone_number=${encodeURIComponent(phone)}`)
                    .then(res => res.json())
                    .then(data => {
                        this.phoneChecking = false;
                        if (data.phone_exists) {
                            this.phoneError = 'Nomor HP sudah terdaftar di sistem';
                            this.phoneValid = false;
                        } else {
                            this.phoneError = '';
                            this.phoneValid = true;
                        }
                    })
                    .catch(err => {
                        this.phoneChecking = false;
                    });
                },
                selfieFile: null,
                selfiePreview: '',
                selfieError: '',
                ktpFile: null,
                ktpPreview: '',
                ktpError: '',
                handleSelfieUpload(e) {
                    const file = e.target.files[0];
                    this.selfieError = '';
                    if (file) {
                        if (file.size > 10 * 1024 * 1024) {
                            this.selfieError = 'Ukuran foto selfie melebihi 10MB. Silakan gunakan resolusi kamera lebih rendah atau ukuran file lebih kecil.';
                            this.selfieFile = null;
                            this.selfiePreview = '';
                            e.target.value = '';
                            return;
                        }
                        this.selfieFile = file;
                        this.selfiePreview = URL.createObjectURL(file);
                    }
                },
                handleKtpUpload(e) {
                    const file = e.target.files[0];
                    this.ktpError = '';
                    if (file) {
                        if (file.size > 10 * 1024 * 1024) {
                            this.ktpError = 'Ukuran foto KTP melebihi 10MB. Silakan gunakan resolusi kamera lebih rendah atau ukuran file lebih kecil.';
                            this.ktpFile = null;
                            this.ktpPreview = '';
                            e.target.value = '';
                            return;
                        }
                        this.ktpFile = file;
                        this.ktpPreview = URL.createObjectURL(file);
                    }
                },
                isStepValid() {
                    if (this.step === 1) {
                        return this.formData.name.trim() && 
                               this.formData.email.trim() && 
                               this.emailValid &&
                               this.formData.phone_number.trim() && 
                               this.phoneValid &&
                               this.formData.password.length >= 6 && 
                               this.formData.password === this.formData.password_confirmation;
                    }
                    if (this.step === 2) {
                        return !!this.formData.gender && !!this.formData.date_of_birth;
                    }
                    if (this.step === 3) {
                        if (this.formData.is_hpi_candidate === '1') {
                            return !!this.formData.armada;
                        }
                        return true;
                    }
                    if (this.step === 4) {
                        return this.formData.nagari.trim() && 
                               this.formData.kecamatan.trim() && 
                               this.formData.kabupaten.trim() && 
                               this.formData.provinsi.trim() && 
                               this.formData.full_address.trim();
                    }
                    if (this.step === 5) {
                        return !!this.formData.latitude && !!this.formData.longitude && this.gpsPermissionGranted;
                    }
                    if (this.step === 6) {
                        if (!this.formData.origin) return false;
                        if (!this.formData.profession) return false;
                        if (this.formData.profession === 'Lainnya' && !this.formData.profession_other.trim()) return false;
                        return this.formData.initial_rating >= 1 && this.formData.initial_rating <= 5;
                    }
                    if (this.step === 7) {
                        return !!this.selfieFile && !!this.ktpFile;
                    }
                    if (this.step === 8) {
                        return this.formData.join_reason.trim();
                    }
                    return true;
                },
                nextStep() {
                    if (this.isStepValid() && this.step < 8) {
                        this.step++;
                    }
                },
                prevStep() {
                    if (this.step > 1) {
                        this.step--;
                    }
                },
                requestDeviceLocation() {
                    this.gpsLoading = true;
                    this.gpsError = '';
                    if (!navigator.geolocation) {
                        this.gpsError = 'Browser Anda tidak mendukung fitur GPS Geolocation.';
                        this.gpsPermissionGranted = false;
                        this.gpsLoading = false;
                        return;
                    }
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            this.formData.latitude = lat.toFixed(6);
                            this.formData.longitude = lng.toFixed(6);
                            this.gpsPermissionGranted = true;
                            this.gpsLoading = false;
                            this.gpsError = '';
                            
                            // update leaflet map view and marker
                            if (this.leafletMap) {
                                this.leafletMap.setView([lat, lng], 15);
                                if (this.leafletMarker) {
                                    this.leafletMarker.setLatLng([lat, lng]);
                                }
                            }
                        },
                        (error) => {
                            this.gpsLoading = false;
                            this.gpsPermissionGranted = false;
                            if (error.code === error.PERMISSION_DENIED) {
                                this.gpsError = 'Akses GPS ditolak. Anda HARUS mengizinkan akses lokasi di Chrome untuk melanjutkan pendaftaran.';
                            } else {
                                this.gpsError = 'Gagal mendapatkan lokasi GPS perangkat Anda. Pastikan GPS aktif.';
                            }
                        },
                        { enableHighAccuracy: true, timeout: 10000 }
                    );
                },
                initLeafletMap() {
                    this.$nextTick(() => {
                        const defaultLat = -1.2186;
                        const defaultLng = 100.4264;
                        
                        if (!this.formData.latitude) this.formData.latitude = defaultLat;
                        if (!this.formData.longitude) this.formData.longitude = defaultLng;

                        const lat = parseFloat(this.formData.latitude);
                        const lng = parseFloat(this.formData.longitude);

                        if (this.leafletMap) {
                            setTimeout(() => {
                                this.leafletMap.invalidateSize();
                                this.leafletMap.setView([lat, lng], 13);
                                if (this.leafletMarker) {
                                    this.leafletMarker.setLatLng([lat, lng]);
                                }
                            }, 100);
                            return;
                        }

                        setTimeout(() => {
                            this.leafletMap = L.map('map', { fullscreenControl: true }).setView([lat, lng], 13);

                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                            }).addTo(this.leafletMap);

                            this.leafletMarker = L.marker([lat, lng], {
                                draggable: true
                            }).addTo(this.leafletMap);

                            this.leafletMarker.on('dragend', (e) => {
                                const position = this.leafletMarker.getLatLng();
                                this.formData.latitude = position.lat.toFixed(6);
                                this.formData.longitude = position.lng.toFixed(6);
                            });

                            this.leafletMap.on('click', (e) => {
                                const latlng = e.latlng;
                                this.leafletMarker.setLatLng(latlng);
                                this.formData.latitude = latlng.lat.toFixed(6);
                                this.formData.longitude = latlng.lng.toFixed(6);
                            });

                            this.leafletMap.invalidateSize();
                            
                            if (!this.gpsPermissionGranted) {
                                this.requestDeviceLocation();
                            }
                        }, 100);
                    });
                },
                getRatingLabel() {
                    const ratings = {
                        1: { title: "Pemula (Newbie)", description: "Baru di dunia pariwisata, belum memiliki pengalaman pelayanan." },
                        2: { title: "Kader Pratama", description: "Paham dasar-dasar hospitality, butuh bimbingan lapangan." },
                        3: { title: "Pemandu Madya", description: "Memiliki pengalaman lokal dan sering memandu rombongan kecil." },
                        4: { title: "Pemandu Ahli", description: "Sangat mengenal kawasan, bersertifikat lokal, mandiri." },
                        5: { title: "Master Guiding", description: "Bisa memandu multibahasa, tanggap darurat, berwawasan luas." }
                    };
                    return ratings[this.formData.initial_rating] || { title: "", description: "" };
                },
                submitRegistration() {
                    if (!this.isStepValid()) return;
                    const form = document.getElementById('registrationForm');
                    
                    // Create/override dynamic fields to submit correctly
                    const fields = {
                        'profession': this.formData.profession === 'Lainnya' ? this.formData.profession_other : this.formData.profession,
                        'profession_detail': this.formData.profession_detail,
                        'initial_rating': this.formData.initial_rating,
                        'latitude': this.formData.latitude,
                        'longitude': this.formData.longitude
                    };

                    for (const [key, value] of Object.entries(fields)) {
                        let input = form.querySelector(`[name="${key}"]`);
                        if (input) {
                            input.value = value;
                        } else {
                            input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = value;
                            form.appendChild(input);
                        }
                    }

                    form.submit();
                }
            };
        }
    </script>
</body>
</html>
