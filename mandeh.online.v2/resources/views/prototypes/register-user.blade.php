<x-maritime-layout>
    <x-slot name="title">Registration | Mandeh.Online</x-slot>

    <main class="flex-1 w-full max-w-md mx-auto px-4 py-lg flex flex-col gap-sm overflow-y-auto min-h-[calc(100vh-56px)]">
        <!-- Agent Bubble 1 -->
        <div class="flex w-full justify-start">
            <div class="bg-surface-lowest rounded-xl rounded-tl-[4px] p-4 max-w-[85%] border border-outline-variant/10 shadow-premium">
                <p class="text-primary font-body-md">Mau saya ingatkan kalau laporan Anda dibalas? Daftar 10 detik saja.</p>
            </div>
        </div>

        <!-- Registration Card -->
        <div class="flex w-full justify-center">
            <div class="bg-surface-lowest rounded-[20px] p-6 w-full border border-outline-variant/10 shadow-modal flex flex-col gap-md">
                <!-- Form Fields -->
                <div class="flex flex-col gap-4">
                    <!-- Input 1: Nama Panggilan -->
                    <div class="flex flex-col gap-xs">
                        <label class="font-label-md text-on-surface-variant uppercase" for="nama">Nama panggilan (opsional)</label>
                        <input class="w-full bg-surface-bright border-none rounded-lg px-4 py-3 font-body-md text-on-surface focus:ring-1 focus:ring-secondary placeholder:text-primary/30" id="nama" placeholder="Ketik nama Anda" type="text">
                    </div>

                    <!-- Input 2: Asal Daerah -->
                    <div class="flex flex-col gap-xs">
                        <label class="font-label-md text-on-surface-variant uppercase" for="daerah">Asal daerah</label>
                        <div class="relative">
                            <select class="w-full bg-surface-bright border-none rounded-lg px-4 py-3 font-body-md text-on-surface focus:ring-1 focus:ring-secondary appearance-none" id="daerah">
                                <option value="pesisir_selatan">Pesisir Selatan</option>
                                <option value="padang">Padang</option>
                                <option value="luar_sumbar">Luar Sumbar</option>
                                <option value="mancanegara">Mancanegara</option>
                            </select>
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none">expand_more</span>
                        </div>
                    </div>

                    <!-- Input 3: Nomor WA -->
                    <div class="flex flex-col gap-xs">
                        <label class="font-label-md text-on-surface-variant uppercase" for="wa">Nomor WA <span class="text-error">*</span></label>
                        <input class="w-full bg-surface-bright border-none rounded-lg px-4 py-3 font-body-md text-on-surface focus:ring-1 focus:ring-secondary placeholder:text-primary/30" id="wa" placeholder="Contoh: 0812..." required type="tel">
                        <p class="text-xs text-on-surface-variant mt-1">Wajib, untuk notifikasi</p>
                    </div>

                    <!-- Checkbox -->
                    <label class="flex items-start gap-3 cursor-pointer mt-2 group">
                        <div class="relative flex items-center justify-center mt-1">
                            <input class="peer h-5 w-5 rounded border-outline bg-surface-bright text-secondary focus:ring-secondary focus:ring-offset-background cursor-pointer transition-all" type="checkbox">
                        </div>
                        <span class="font-label-md text-on-surface-variant group-hover:text-on-surface transition-colors">Saya setuju data dipakai hanya untuk notifikasi Mandeh</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button class="w-full h-[56px] bg-[#00A86B] hover:bg-[#008f5a] text-white font-body-md font-bold rounded-lg transition-colors flex items-center justify-center shadow-sm" type="button">
                    Daftar sebagai Penjaga Mandeh
                </button>
            </div>
        </div>

        <!-- Agent Bubble 2 (Placeholder) -->
        <div class="flex w-full justify-start mt-2 opacity-50">
            <div class="bg-surface-lowest rounded-xl rounded-tl-[4px] p-4 max-w-[85%] border border-outline-variant/10 shadow-premium flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary text-sm">check</span>
                <p class="text-primary font-body-md">Terdaftar. Terima kasih sudah ikut menjaga.</p>
            </div>
        </div>
    </main>

    <!-- Bottom Input Bar -->
    <footer class="fixed bottom-0 left-0 w-full z-50 bg-surface/90 backdrop-blur-lg px-4 pb-6 pt-4 border-t border-outline-variant/10">
        <div class="max-w-md mx-auto">
            <div class="relative">
                <input class="w-full bg-surface-bright border-none rounded-full pl-4 pr-12 py-3 font-body-md text-on-surface-variant/50 placeholder:text-primary/30 cursor-not-allowed shadow-sm" disabled placeholder="Ketik di sini untuk bertanya..." type="text">
                <button class="absolute right-2 top-1/2 -translate-y-1/2 p-2 rounded-full text-on-surface-variant/30 cursor-not-allowed" disabled>
                    <span class="material-symbols-outlined material-symbols-fill">send</span>
                </button>
            </div>
        </div>
    </footer>
</x-maritime-layout>
