<x-maritime-layout>
    <x-slot name="title">Menu | Mandeh.Online</x-slot>

    <div class="relative min-h-[calc(100vh-56px)] flex flex-col items-center justify-center overflow-hidden bg-background">
        <!-- Main Chat Canvas Area (Simulated Background) -->
        <main class="flex-grow flex flex-col px-4 py-6 gap-sm overflow-y-auto pb-[500px] opacity-40 blur-[1px]">
            <div class="self-end bg-primary text-on-primary rounded-[16px] rounded-br-[4px] px-4 py-3 max-w-[85%] shadow-premium">
                <p class="font-body-md">Tolong tampilkan menu dari RM. Salero Bundo terdekat.</p>
            </div>
            <div class="self-start bg-surface-container-lowest text-primary rounded-[16px] rounded-bl-[4px] px-4 py-3 max-w-[85%] shadow-premium border border-outline-variant/10">
                <p class="font-body-md">Tentu, ini menu dari RM. Salero Bundo - Cabang Padang Pariaman. Silakan pilih pesanan Anda.</p>
            </div>
        </main>

        <!-- Overlay / Scrim -->
        <div class="fixed inset-0 bg-primary/20 z-40 cursor-pointer" onclick="window.history.back()"></div>

        <!-- Bottom Sheet / Response Card -->
        <div class="fixed bottom-0 left-0 w-full z-50 bg-surface-container-lowest rounded-t-[24px] shadow-modal flex flex-col h-[80vh]">
            <!-- Drag Handle -->
            <div class="w-full flex justify-center pt-3 pb-1">
                <div class="w-12 h-1.5 bg-outline-variant/50 rounded-full"></div>
            </div>
            <!-- Sheet Header -->
            <div class="px-4 pb-2 pt-2 border-b border-outline-variant/10">
                <div class="flex items-center gap-xs mb-3">
                    <span class="material-symbols-outlined text-secondary text-[16px] material-symbols-fill">verified</span>
                    <span class="font-label-md text-secondary uppercase">Verified by Mitra Resmi</span>
                </div>
                <div class="flex items-center justify-between">
                    <h3 class="text-headline-md text-primary font-bold">RM. Salero Bundo</h3>
                    <div class="flex items-center gap-1 bg-surface-container-low px-2 py-1 rounded-md">
                        <span class="material-symbols-outlined text-secondary text-sm material-symbols-fill">star</span>
                        <span class="font-label-md text-primary">4.8</span>
                    </div>
                </div>
            </div>
            <!-- Tabs -->
            <div class="flex px-4 border-b border-outline-variant/10">
                <button class="flex-1 py-3 text-center font-label-md text-on-surface-variant">Info</button>
                <button class="flex-1 py-3 text-center font-label-md text-secondary border-b-2 border-secondary font-bold">Menu</button>
                <button class="flex-1 py-3 text-center font-label-md text-on-surface-variant">Laporan</button>
            </div>
            <!-- Menu List Area -->
            <div class="flex-1 overflow-y-auto px-4 py-4 flex flex-col gap-sm pb-xl scrollbar-hide">
                <!-- Menu Item 1 -->
                <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-xl p-3 flex gap-3 shadow-premium items-center">
                    <div class="w-20 h-20 rounded-full overflow-hidden flex-shrink-0 bg-surface-container border border-outline-variant/10">
                        <img src="https://images.unsplash.com/photo-1606787366850-de6330128bfc?q=80&w=200&h=200&auto=format&fit=crop" alt="Rendang Lokan" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="font-body-md text-primary font-bold truncate">Rendang Lokan</h4>
                            <div class="bg-secondary/10 text-secondary px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ml-2 flex-shrink-0">
                                Best Seller
                            </div>
                        </div>
                        <p class="font-body-sm text-on-surface-variant line-clamp-2 leading-tight mb-2">Kerang sungai segar dimasak lambat dengan bumbu rendang otentik khas Pesisir Selatan.</p>
                        <div class="flex items-center justify-between">
                            <span class="font-body-md text-primary font-bold">Rp 45.000</span>
                            <button class="bg-primary/5 hover:bg-primary/10 text-primary border border-primary/20 rounded-full px-3 py-1 font-label-md flex items-center gap-1 transition-colors">
                                <span class="material-symbols-outlined text-[16px]">add</span>
                                <span>Tambah</span>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Menu Item 2 -->
                <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-xl p-3 flex gap-3 shadow-premium items-center">
                    <div class="w-20 h-20 rounded-full overflow-hidden flex-shrink-0 bg-surface-container border border-outline-variant/10">
                        <img src="https://images.unsplash.com/photo-1544025162-d76694265947?q=80&w=200&h=200&auto=format&fit=crop" alt="Sate Padang" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="font-body-md text-primary font-bold truncate">Sate Padang Pariaman</h4>
                        </div>
                        <p class="font-body-sm text-on-surface-variant line-clamp-2 leading-tight mb-2">Sate daging sapi pilihan dengan kuah kuning kemerahan khas Pariaman, pedas dan gurih.</p>
                        <div class="flex items-center justify-between">
                            <span class="font-body-md text-primary font-bold">Rp 30.000</span>
                            <button class="bg-primary/5 hover:bg-primary/10 text-primary border border-primary/20 rounded-full px-3 py-1 font-label-md flex items-center gap-1 transition-colors">
                                <span class="material-symbols-outlined text-[16px]">add</span>
                                <span>Tambah</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-maritime-layout>
