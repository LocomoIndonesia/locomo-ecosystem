<x-maritime-layout>
    <x-slot name="title">Konek WiFi | Mandeh.Online</x-slot>

    <main class="flex-1 flex flex-col px-4 py-6 pb-24 max-w-2xl mx-auto w-full min-h-[calc(100vh-56px)]">
        <!-- Chat Context / Agent Bubble -->
        <div class="flex flex-col gap-sm mb-lg">
            <div class="flex items-end gap-2 self-start w-11/12 max-w-[400px]">
                <div class="w-8 h-8 rounded-full bg-surface-container-low flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-primary text-[20px]">robot_2</span>
                </div>
                <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-2xl rounded-bl-sm p-4 shadow-premium">
                    <p class="text-body-md text-primary">Anda memindai QR Premium di RM Pesisir. WiFi gratis tersedia untuk tamu.</p>
                </div>
            </div>
        </div>

        <!-- Main Action Card -->
        <div class="bg-surface-container-lowest rounded-[20px] p-6 border border-outline-variant/10 shadow-modal flex flex-col gap-6">
            <!-- Card Header -->
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-secondary/10 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-secondary text-[28px]">wifi</span>
                    </div>
                    <div>
                        <h2 class="text-headline-md text-primary font-bold">WiFi Tamu Mandeh</h2>
                        <p class="font-label-md text-outline-variant uppercase">Koneksi Premium</p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-secondary/10 border border-secondary/20">
                    <div class="w-2 h-2 rounded-full bg-secondary"></div>
                    <span class="font-label-md text-secondary uppercase">AKTIF</span>
                </div>
            </div>

            <!-- Details List -->
            <div class="flex flex-col gap-4">
                <div class="flex justify-between items-center py-2 border-b border-outline-variant/10">
                    <span class="font-label-md text-outline-variant uppercase">SSID</span>
                    <span class="font-body-md text-primary font-bold">Mandeh-Guest-RMPesisir</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-outline-variant/10">
                    <span class="font-label-md text-outline-variant uppercase">Password</span>
                    <div class="flex items-center gap-2">
                        <span class="font-body-md text-primary tracking-widest mt-1">••••••••</span>
                        <button class="text-outline-variant hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                        </button>
                    </div>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-outline-variant/10">
                    <span class="font-label-md text-outline-variant uppercase">Kecepatan</span>
                    <span class="font-body-md text-primary">Hingga 20 Mbps</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-outline-variant/10">
                    <span class="font-label-md text-outline-variant uppercase">Masa Aktif</span>
                    <span class="font-body-md text-primary">Selama di lokasi</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="font-label-md text-outline-variant uppercase">Verifikasi</span>
                    <div class="flex gap-2">
                        <span class="px-2 py-1 rounded-md bg-surface-variant text-primary text-[10px] font-bold tracking-wide">MITRA NAGARI</span>
                        <span class="px-2 py-1 rounded-md bg-surface-variant text-primary text-[10px] font-bold tracking-wide">TELKOMSEL PESISIR</span>
                    </div>
                </div>
            </div>

            <!-- Privacy Note -->
            <div class="flex items-center gap-2 text-outline-variant">
                <span class="material-symbols-outlined text-[16px]">shield_lock</span>
                <p class="text-[12px]">Koneksi aman. Kami tidak menyimpan data browsing Anda.</p>
            </div>

            <!-- Actions -->
            <div class="flex flex-col items-center gap-3 mt-2">
                <button class="w-full h-[56px] bg-secondary text-on-secondary rounded-xl text-body-lg font-bold flex items-center justify-center gap-2 hover:bg-secondary/90 transition-colors shadow-premium">
                    <span class="material-symbols-outlined material-symbols-fill">bolt</span>
                    Sambungkan WiFi Sekarang
                </button>
                <button class="font-label-md text-outline-variant hover:text-primary transition-colors underline underline-offset-4">
                    Salin Password Manual
                </button>
            </div>

            <!-- Trust Row -->
            <div class="pt-4 mt-2 border-t border-outline-variant/10 flex justify-between items-center">
                <div class="flex flex-col items-center gap-1">
                    <span class="material-symbols-outlined text-outline-variant text-[20px]">money_off</span>
                    <span class="text-[11px] text-outline-variant">Gratis</span>
                </div>
                <div class="flex flex-col items-center gap-1">
                    <span class="material-symbols-outlined text-outline-variant text-[20px]">no_accounts</span>
                    <span class="text-[11px] text-outline-variant">Tanpa Login</span>
                </div>
                <div class="flex flex-col items-center gap-1">
                    <span class="material-symbols-outlined text-outline-variant text-[20px]">handshake</span>
                    <span class="text-[11px] text-outline-variant">Didukung Nagari</span>
                </div>
            </div>
        </div>
    </main>

    <!-- Bottom Input Bar -->
    <div class="fixed bottom-0 left-0 w-full z-50 px-4 pb-safe bg-surface/90 backdrop-blur-lg border-t border-outline-variant/10 py-3 shadow-modal">
        <div class="max-w-[1200px] mx-auto flex items-center gap-2">
            <button class="w-10 h-10 rounded-full flex items-center justify-center text-outline-variant hover:text-primary transition-colors shrink-0">
                <span class="material-symbols-outlined">add_circle</span>
            </button>
            <div class="flex-1 relative">
                <input class="w-full bg-surface-container-lowest border border-outline-variant/20 rounded-full py-2.5 pl-4 pr-10 text-body-md font-body-md text-primary placeholder:text-primary/30 focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary transition-all" placeholder="Ada kendala koneksi? Ketik di sini...">
                <button class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full flex items-center justify-center text-secondary hover:bg-secondary/10 transition-colors">
                    <span class="material-symbols-outlined text-[20px] material-symbols-fill">send</span>
                </button>
            </div>
        </div>
    </div>
</x-maritime-layout>
