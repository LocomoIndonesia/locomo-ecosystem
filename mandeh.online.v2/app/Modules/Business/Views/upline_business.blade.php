<x-maritime-layout>
    <x-slot name="title">Kelola Bisnis Referral | Mandeh.Online</x-slot>

    <div class="relative z-10 w-full max-w-6xl mx-auto px-4 py-8 space-y-8">
        
        <!-- Header -->
        <div class="bg-surface/80 backdrop-blur-md rounded-3xl p-6 md:p-8 border border-outline-variant/30 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <h1 class="text-3xl font-extrabold text-primary mb-1">Manajemen Bisnis & Referral</h1>
                <p class="text-on-surface-variant text-sm">Daftarkan bisnis pelaku wisata lokal baru, generate QR Code otomatis, dan hubungkan sebagai downline Anda.</p>
            </div>
            <a href="{{ route('vanguard.dashboard') }}" class="px-5 py-3 rounded-2xl bg-secondary text-on-secondary font-bold text-sm hover:opacity-90 transition flex items-center gap-2 shadow-premium">
                <span class="material-symbols-outlined">dashboard</span> Kembali ke Dashboard
            </a>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-600 text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-error-container text-on-error-container rounded-2xl text-sm font-semibold">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- Left Grid: List of Businesses Didaftarkan & Dimiliki -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-surface/80 backdrop-blur-md rounded-3xl p-6 border border-outline-variant/30">
                    <h2 class="text-xl font-bold text-primary mb-4">Daftar Bisnis yang Terhubung</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse($businesses as $biz)
                            <div class="bg-surface-container-lowest border border-outline-variant/30 p-5 rounded-2xl flex flex-col justify-between gap-4 shadow-sm hover:shadow-md transition-all">
                                <div>
                                    <div class="flex justify-between items-start">
                                        <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-secondary/15 text-secondary uppercase">{{ $biz->type }}</span>
                                        <span class="text-xs font-semibold text-primary/60">Ref: {{ $biz->upline ? $biz->upline->name : 'System' }}</span>
                                    </div>
                                    <h3 class="text-lg font-bold text-primary mt-2">{{ $biz->name }}</h3>
                                    <p class="text-on-surface-variant text-xs line-clamp-2 mt-1">{{ $biz->description }}</p>
                                    
                                    <!-- Owner / Driver details -->
                                    <div class="mt-4 pt-3 border-t border-outline-variant/30 text-xs text-primary space-y-1">
                                        <p><b>Pemilik:</b> {{ $biz->owner->name }}</p>
                                        <p><b>Kontak:</b> {{ $biz->owner->phone_number }}</p>
                                        @if($biz->associations->count() > 0)
                                            <p><b>Asosiasi Staf:</b> {{ implode(', ', $biz->associations->map(fn($a) => $a->user->name . ' (' . $a->role_in_business . ')')->toArray()) }}</p>
                                        @endif
                                    </div>
                                </div>

                                <!-- QR Code Render -->
                                <div class="flex items-center gap-4 bg-surface-container-low/50 p-3 rounded-xl border border-outline-variant/20">
                                    @if($biz->qr_code_path)
                                        <img src="{{ asset('storage/' . $biz->qr_code_path) }}" alt="QR Code" class="w-16 h-16 rounded border border-outline bg-white object-contain">
                                    @else
                                        <div class="w-16 h-16 rounded bg-outline-variant flex items-center justify-center text-[10px] font-bold text-slate-400">NO QR</div>
                                    @endif
                                    <div class="flex-1">
                                        <p class="text-[10px] font-bold text-primary/40 uppercase">QR Token</p>
                                        <p class="text-xs font-mono font-bold text-primary break-all">{{ substr($biz->qr_token, 0, 16) }}...</p>
                                        @if($biz->qr_code_path)
                                            <a href="{{ asset('storage/' . $biz->qr_code_path) }}" download="{{ Str::slug($biz->name) }}-qr.png" class="text-xs text-secondary font-bold hover:underline flex items-center gap-1 mt-1">
                                                <span class="material-symbols-outlined text-[14px]">download</span> Unduh QR
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2 text-center py-12 text-on-surface-variant text-sm">
                                Belum ada bisnis yang didaftarkan. Gunakan formulir di sebelah kanan untuk menambahkan bisnis pertama.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Grid: Register New Business & Owner Form -->
            <div class="bg-surface/80 backdrop-blur-md rounded-3xl p-6 border border-outline-variant/30 space-y-6 shadow-premium">
                <h2 class="text-xl font-bold text-primary">Daftar Bisnis & Downline</h2>
                
                <form action="{{ route('business.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    <!-- Business Details -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold text-secondary uppercase tracking-wider">Detail Bisnis</h3>
                        
                        <div class="flex flex-col gap-1">
                            <label class="text-xs text-primary/70 font-semibold uppercase">Nama Bisnis / Armada</label>
                            <input name="name" required type="text" placeholder="Contoh: Boat Lancang Kuning" 
                                   class="px-4 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-lg text-primary font-body-md placeholder-primary/20 outline-none w-full focus:ring-1 focus:ring-secondary focus:border-secondary transition-colors">
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-xs text-primary/70 font-semibold uppercase">Kategori Bisnis</label>
                            <select name="type" class="px-4 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-lg text-primary font-body-md focus:ring-1 focus:ring-secondary focus:border-secondary outline-none w-full">
                                <option value="transportasi">Transportasi / Kapal Boat</option>
                                <option value="restoran">Restoran / Rumah Makan</option>
                                <option value="hotel">Hotel / Wisma</option>
                                <option value="tour_guide">Pemandu Wisata (Tour Guide)</option>
                                <option value="produk_lokal">Oleh-Oleh / Produk Lokal</option>
                                <option value="layanan_publik">Fasilitas Umum (Layanan Publik)</option>
                                <option value="destinasi">Destinasi Wisata</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-xs text-primary/70 font-semibold uppercase">Deskripsi Ringkas</label>
                            <textarea name="description" placeholder="Ceritakan singkat tentang layanan bisnis ini..." class="px-4 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-lg text-primary font-body-md placeholder-primary/20 outline-none w-full focus:ring-1 focus:ring-secondary focus:border-secondary transition-colors" rows="2"></textarea>
                        </div>
                    </div>

                    <!-- Downline (Owner/Driver) Details -->
                    <div class="space-y-3 pt-3 border-t border-outline-variant/30">
                        <h3 class="text-xs font-bold text-secondary uppercase tracking-wider">Identitas Pemilik / Pengemudi (Downline)</h3>
                        
                        <div class="flex flex-col gap-1">
                            <label class="text-xs text-primary/70 font-semibold uppercase">Peran dalam Bisnis</label>
                            <select name="role_in_business" class="px-4 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-lg text-primary font-body-md focus:ring-1 focus:ring-secondary focus:border-secondary outline-none w-full">
                                <option value="owner">Pemilik Utama (Owner)</option>
                                <option value="driver">Pengemudi / Nahkoda (Driver)</option>
                                <option value="manager">Pengelola (Manager)</option>
                                <option value="staff">Staf Pendukung</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-xs text-primary/70 font-semibold uppercase">Nama Lengkap</label>
                            <input name="owner_name" required type="text" placeholder="Nama Lengkap Downline" 
                                   class="px-4 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-lg text-primary font-body-md placeholder-primary/20 outline-none w-full focus:ring-1 focus:ring-secondary focus:border-secondary transition-colors">
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-xs text-primary/70 font-semibold uppercase">Alamat Email</label>
                            <input name="owner_email" required type="email" placeholder="email@downline.com" 
                                   class="px-4 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-lg text-primary font-body-md placeholder-primary/20 outline-none w-full focus:ring-1 focus:ring-secondary focus:border-secondary transition-colors">
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-xs text-primary/70 font-semibold uppercase">No. Handphone (WhatsApp)</label>
                            <input name="owner_phone" required type="text" placeholder="08123456789" 
                                   class="px-4 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-lg text-primary font-body-md placeholder-primary/20 outline-none w-full focus:ring-1 focus:ring-secondary focus:border-secondary transition-colors">
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-xs text-primary/70 font-semibold uppercase">Kata Sandi Akun Baru</label>
                            <input name="owner_password" required type="password" placeholder="Min 6 Karakter" 
                                   class="px-4 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-lg text-primary font-body-md placeholder-primary/20 outline-none w-full focus:ring-1 focus:ring-secondary focus:border-secondary transition-colors">
                        </div>
                    
                        <!-- Subscription Package -->
                        <div class="space-y-3 pt-3 border-t border-outline-variant/30">
                            <h3 class="text-xs font-bold text-secondary uppercase tracking-wider">Paket Layanan</h3>
                            
                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-primary/70 font-semibold uppercase">Pilih Paket</label>
                                <select id="subscription_package" name="subscription_package" required onchange="togglePaymentProof()"
                                        class="px-4 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-lg text-primary font-body-md focus:ring-1 focus:ring-secondary focus:border-secondary outline-none w-full">
                                    <option value="free">Free (Rp 0 - Direktori & Ulasan)</option>
                                    <option value="basic">Basic (Rp 30k/bln - Menu & QRIS)</option>
                                    <option value="bisnis">Bisnis (Rp 50k/bln - HP Order & Kurir)</option>
                                    <option value="vip">VIP (Rp 100k/bln - AI Gemini & Kasir)</option>
                                </select>
                            </div>

                            <div id="payment_proof_container" class="flex flex-col gap-1 hidden">
                                <label class="text-xs text-primary/70 font-semibold uppercase">Bukti Pembayaran (Iuran Pertama)</label>
                                <input type="file" name="payment_proof" id="payment_proof" accept="image/*,application/pdf"
                                       class="px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-primary text-xs outline-none w-full file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-on-primary hover:file:opacity-90">
                                <p class="text-[10px] text-on-surface-variant italic mt-1">Wajib diunggah untuk paket berbayar. Admin akan memverifikasi bukti pembayaran ini sebelum mengaktifkan bisnis.</p>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3 bg-primary text-on-primary font-bold rounded-2xl hover:opacity-90 transition shadow-premium">
                        Daftarkan Bisnis & Owner
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePaymentProof() {
            const pkgSelect = document.getElementById('subscription_package');
            const proofContainer = document.getElementById('payment_proof_container');
            const proofInput = document.getElementById('payment_proof');
            
            if (pkgSelect.value === 'free') {
                proofContainer.classList.add('hidden');
                proofInput.removeAttribute('required');
            } else {
                proofContainer.classList.remove('hidden');
                proofInput.setAttribute('required', 'required');
            }
        }
        
        // Initial run
        document.addEventListener('DOMContentLoaded', function() {
            togglePaymentProof();
        });
    </script>
</x-maritime-layout>
