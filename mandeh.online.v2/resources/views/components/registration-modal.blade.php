<!-- Registration Modal (Alpine.js) -->
<template x-teleport="body">
    <div x-show="showRegisterModal" 
         class="fixed inset-0 z-[99999] overflow-y-auto" 
         x-cloak>
        
        <!-- Backdrop -->
        <div x-show="showRegisterModal"
             x-transition:enter="transition opacity-0 ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition opacity-100 ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showRegisterModal = false"
             class="fixed inset-0 bg-black/60 backdrop-blur-md"></div>

        <!-- Modal Content Wrapper (for centering) -->
        <div class="relative min-h-screen flex items-center justify-center p-4 pointer-events-none">
            <!-- Modal Box -->
            <div x-show="showRegisterModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="relative bg-white rounded-[28px] shadow-2xl border border-gray-200 w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden pointer-events-auto">
                
                <!-- Header -->
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-10">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">Pilih Peran Anda</h2>
                        <p class="text-sm text-slate-500">Bergabunglah sesuai dengan kontribusi Anda di Mandeh.</p>
                    </div>
                    <button @click="showRegisterModal = false" class="p-2 hover:bg-slate-100 rounded-full transition-colors text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Grid Options -->
                <div class="flex-1 overflow-y-auto p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 bg-slate-50/30">
                    <!-- Option 1: Warga -->
                    <a href="{{ route('prototypes.register-user') }}" class="flex flex-col gap-3 p-5 rounded-2xl bg-white border border-gray-200 hover:border-blue-500 hover:shadow-lg hover:shadow-blue-500/10 transition-all group">
                        <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all">
                            <span class="material-symbols-outlined">home</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Warga</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">Penduduk lokal yang ingin berkontribusi menjaga Mandeh.</p>
                        </div>
                    </a>

                    <!-- Option 2: Wisatawan -->
                    <a href="{{ route('prototypes.register-user') }}" class="flex flex-col gap-3 p-5 rounded-2xl bg-white border border-gray-200 hover:border-blue-500 hover:shadow-lg hover:shadow-blue-500/10 transition-all group">
                        <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all">
                            <span class="material-symbols-outlined">luggage</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Wisatawan</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">Pengunjung yang ingin mendapatkan info & memberi ulasan.</p>
                        </div>
                    </a>

                    <!-- Option 3: Pemilik Usaha -->
                    <a href="{{ route('prototypes.register-business') }}" class="flex flex-col gap-3 p-5 rounded-2xl bg-white border border-gray-200 hover:border-blue-500 hover:shadow-lg hover:shadow-blue-500/10 transition-all group">
                        <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all">
                            <span class="material-symbols-outlined">storefront</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Pemilik Usaha</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">Kelola profil bisnis, menu, dan terima feedback jujur.</p>
                        </div>
                    </a>

                    <!-- Option 4: Komunitas -->
                    <a href="{{ route('prototypes.register-partner') }}" class="flex flex-col gap-3 p-5 rounded-2xl bg-white border border-gray-200 hover:border-blue-500 hover:shadow-lg hover:shadow-blue-500/10 transition-all group">
                        <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all">
                            <span class="material-symbols-outlined">groups</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Komunitas</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">Grup peduli lingkungan atau pegiat wisata Mandeh.</p>
                        </div>
                    </a>

                    <!-- Option 5: Sponsor -->
                    <a href="{{ route('prototypes.register-partner') }}" class="flex flex-col gap-3 p-5 rounded-2xl bg-white border border-gray-200 hover:border-blue-500 hover:shadow-lg hover:shadow-blue-500/10 transition-all group">
                        <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all">
                            <span class="material-symbols-outlined">handshake</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Sponsor</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">Dukung inisiatif pelestarian Mandeh melalui kemitraan.</p>
                        </div>
                    </a>

                    <!-- Option 6: Mitra Resmi -->
                    <a href="{{ route('prototypes.register-partner') }}" class="flex flex-col gap-3 p-5 rounded-2xl bg-white border border-gray-200 hover:border-blue-500 hover:shadow-lg hover:shadow-blue-500/10 transition-all group">
                        <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all">
                            <span class="material-symbols-outlined">verified</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Mitra Resmi</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">Pemerintah Nagari atau instansi berwenang lainnya.</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
