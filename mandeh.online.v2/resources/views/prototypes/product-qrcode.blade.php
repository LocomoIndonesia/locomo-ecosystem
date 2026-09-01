<x-maritime-layout>
    <x-slot name="title">Katalog Produk | Mandeh.Online</x-slot>

    <div class="relative min-h-[calc(100vh-56px)] flex flex-col items-center justify-center overflow-hidden bg-background p-md">
        <!-- Background image for maritime vibe -->
        <div class="fixed inset-0 z-0 opacity-20 blur-sm pointer-events-none">
            <img src="https://images.unsplash.com/photo-1519046448245-267daa8b3da1?q=80&w=1200&auto=format&fit=crop" alt="Maritime background" class="w-full h-full object-cover">
        </div>

        <!-- Main Content Card -->
        <main class="relative z-10 w-full max-w-[400px] bg-surface-container-lowest rounded-[24px] shadow-modal border border-outline-variant/10 overflow-hidden flex flex-col h-[80vh]">
            <!-- Header Section -->
            <div class="p-md pb-0 border-b border-outline-variant/10">
                <!-- Tab Bar -->
                <div class="flex gap-md mt-sm">
                    <div class="pb-xs border-b-2 border-primary text-primary font-label-md cursor-pointer">
                        Catalog
                    </div>
                    <div class="pb-xs border-b-2 border-transparent text-on-surface-variant font-label-md cursor-pointer hover:text-primary transition-colors">
                        Details
                    </div>
                    <div class="ml-auto flex items-center cursor-pointer text-on-surface-variant hover:text-primary transition-colors">
                        <span class="material-symbols-outlined">shopping_cart</span>
                    </div>
                </div>
            </div>

            <div class="px-md py-sm border-b border-outline-variant/10 flex flex-col gap-sm bg-surface-container-low">
                <div class="flex items-center gap-sm">
                    <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-on-primary shrink-0">
                        <span class="material-symbols-outlined text-[32px]">store</span>
                    </div>
                    <div class="flex flex-col">
                        <h2 class="text-headline-md text-primary font-bold">Toko Kerajinan Mandeh</h2>
                        <div class="flex flex-wrap gap-xs mt-[2px]">
                            <div class="flex items-center gap-1 bg-secondary/10 text-secondary font-label-md px-2 py-[2px] rounded-full border border-secondary/20">
                                <span class="material-symbols-outlined text-[12px] material-symbols-fill">verified</span>
                                <span class="text-[10px]">Verifikasi Niniak Mamak</span>
                            </div>
                            <div class="flex items-center gap-1 bg-surface-variant text-on-surface-variant font-label-md px-2 py-[2px] rounded-full border border-outline-variant/10">
                                <span class="material-symbols-outlined text-[12px]">verified_user</span>
                                <span class="text-[10px]">Pemda Pesisir Selatan</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="p-md grid grid-cols-2 gap-sm overflow-y-auto scrollbar-hide pb-xl">
                <!-- Product Item 1 -->
                <div class="flex flex-col gap-xs group">
                    <div class="aspect-square rounded-lg overflow-hidden shadow-premium bg-surface-container mb-xs">
                        <img src="https://images.unsplash.com/photo-1590736962031-2947702f231e?q=80&w=200&h=200&auto=format&fit=crop" alt="Batik Tanah Liek" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </div>
                    <h3 class="font-body-md text-primary font-bold truncate">Batik Tanah Liek</h3>
                    <div class="flex items-center justify-between mt-auto">
                        <span class="font-label-md text-primary">IDR 250k</span>
                        <div class="flex items-center text-secondary">
                            <span class="material-symbols-outlined text-[14px] material-symbols-fill">star</span>
                            <span class="font-label-md ml-[2px]">4.9</span>
                        </div>
                    </div>
                </div>
                <!-- Product Item 2 -->
                <div class="flex flex-col gap-xs group">
                    <div class="aspect-square rounded-lg overflow-hidden shadow-premium bg-surface-container mb-xs">
                        <img src="https://images.unsplash.com/photo-1566478989037-eec170784d0b?q=80&w=200&h=200&auto=format&fit=crop" alt="Kerupuk Sanjai" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </div>
                    <h3 class="font-body-md text-primary font-bold truncate">Sanjai Balado</h3>
                    <div class="flex items-center justify-between mt-auto">
                        <span class="font-label-md text-primary">IDR 35k</span>
                        <div class="flex items-center text-secondary">
                            <span class="material-symbols-outlined text-[14px] material-symbols-fill">star</span>
                            <span class="font-label-md ml-[2px]">4.8</span>
                        </div>
                    </div>
                </div>
                <!-- Product Item 3 -->
                <div class="flex flex-col gap-xs group">
                    <div class="aspect-square rounded-lg overflow-hidden shadow-premium bg-surface-container mb-xs">
                        <img src="https://images.unsplash.com/photo-1605106702734-205df224ecce?q=80&w=200&h=200&auto=format&fit=crop" alt="Sulaman Bayang" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </div>
                    <h3 class="font-body-md text-primary font-bold truncate">Sulaman Bayang</h3>
                    <div class="flex items-center justify-between mt-auto">
                        <span class="font-label-md text-primary">IDR 150k</span>
                        <div class="flex items-center text-secondary">
                            <span class="material-symbols-outlined text-[14px] material-symbols-fill">star</span>
                            <span class="font-label-md ml-[2px]">5.0</span>
                        </div>
                    </div>
                </div>
                <!-- Product Item 4 -->
                <div class="flex flex-col gap-xs group">
                    <div class="aspect-square rounded-lg overflow-hidden shadow-premium bg-surface-container mb-xs">
                        <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?q=80&w=200&h=200&auto=format&fit=crop" alt="Rendang Lokan" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </div>
                    <h3 class="font-body-md text-primary font-bold truncate">Rendang Lokan</h3>
                    <div class="flex items-center justify-between mt-auto">
                        <span class="font-label-md text-primary">IDR 85k</span>
                        <div class="flex items-center text-secondary">
                            <span class="material-symbols-outlined text-[14px] material-symbols-fill">star</span>
                            <span class="font-label-md ml-[2px]">4.7</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Action -->
            <div class="p-md pt-sm border-t border-outline-variant/10 bg-surface-container-lowest mt-auto">
                <button class="w-full bg-primary text-on-primary font-body-lg font-bold rounded-lg py-sm flex items-center justify-center gap-xs hover:bg-primary/90 transition-colors shadow-premium">
                    <span class="material-symbols-outlined">shopping_bag</span>
                    Beli Sekarang
                </button>
            </div>
        </main>
    </div>
</x-maritime-layout>
