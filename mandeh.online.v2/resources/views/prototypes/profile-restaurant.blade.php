<x-maritime-layout>
    <x-slot name="title">RM Selera Pantai | Mandeh.Online</x-slot>

    <div class="relative min-h-[calc(100vh-56px)] flex flex-col items-center justify-center overflow-hidden">
        <!-- Background Chat Context (Blurred) -->
        <div class="absolute inset-0 z-0 opacity-40 pointer-events-none flex flex-col p-md max-w-container-max mx-auto w-full">
            <div class="flex flex-col gap-sm w-full max-w-2xl mx-auto mt-lg">
                <div class="self-end bg-primary text-on-primary p-sm rounded-lg rounded-br-[4px] max-w-[80%] shadow-premium">
                    <p class="font-body-md">Can you recommend a good place for traditional Minang food nearby?</p>
                </div>
                <div class="self-start bg-surface-container-lowest text-primary p-sm rounded-lg rounded-bl-[4px] border border-outline-variant/20 shadow-premium max-w-[80%]">
                    <p class="font-body-md">Certainly. I found a highly rated restaurant specializing in coastal Minang cuisine.</p>
                </div>
            </div>
        </div>

        <!-- Bottom Sheet Profile Overlay -->
        <div class="z-50 w-full max-w-2xl mx-auto bg-surface-container-lowest rounded-t-xl shadow-modal border border-outline-variant/20 fixed bottom-0 left-0 right-0 max-h-[90vh] flex flex-col translate-y-0 transition-transform duration-500">
            <!-- Drag Handle / Header Area -->
            <div class="w-full flex flex-col items-center pt-sm pb-base border-b border-outline-variant/10">
                <div class="w-12 h-1.5 bg-outline-variant rounded-full mb-md"></div>
                <div class="flex items-center justify-between w-full px-md">
                    <div class="flex items-center gap-md">
                        <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-surface-container-highest flex-shrink-0">
                            <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?q=80&w=200&h=200&auto=format&fit=crop" alt="Restaurant Logo" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h2 class="text-headline-md text-primary font-bold">RM Selera Pantai</h2>
                            <div class="flex items-center gap-xs text-secondary mt-1">
                                <span class="material-symbols-outlined text-[16px] material-symbols-fill">verified</span>
                                <span class="font-label-md">Terverifikasi: Niniak Mamak</span>
                            </div>
                        </div>
                    </div>
                    <button class="w-8 h-8 flex items-center justify-center rounded-full bg-surface-variant text-on-surface-variant hover:bg-surface-container-highest transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex w-full border-b border-outline-variant/10 px-md pt-sm">
                <button class="flex-1 pb-sm border-b-2 border-primary text-primary font-label-md text-center">Info</button>
                <button class="flex-1 pb-sm border-b-2 border-transparent text-on-surface-variant hover:text-primary font-label-md text-center">Menu</button>
                <button class="flex-1 pb-sm border-b-2 border-transparent text-on-surface-variant hover:text-primary font-label-md text-center">Layanan Digital</button>
            </div>

            <!-- Tab Content Area -->
            <div class="flex-1 overflow-y-auto p-md flex flex-col gap-lg scrollbar-hide pb-xl">
                <!-- Rating & Quick Actions -->
                <div class="flex justify-between items-center bg-surface-container-low p-sm rounded-lg border border-outline-variant/10">
                    <div class="flex items-center gap-sm">
                        <span class="text-headline-md text-primary font-bold">4.8</span>
                        <div class="flex text-secondary">
                            <span class="material-symbols-outlined text-[20px] material-symbols-fill">star</span>
                            <span class="material-symbols-outlined text-[20px] material-symbols-fill">star</span>
                            <span class="material-symbols-outlined text-[20px] material-symbols-fill">star</span>
                            <span class="material-symbols-outlined text-[20px] material-symbols-fill">star</span>
                            <span class="material-symbols-outlined text-[20px] material-symbols-fill">star_half</span>
                        </div>
                        <span class="font-body-sm text-on-surface-variant">(128 Ulasan)</span>
                    </div>
                    <button class="bg-secondary text-on-secondary px-md py-sm rounded-full font-label-md flex items-center gap-xs hover:opacity-90 transition-opacity">
                        <span class="material-symbols-outlined text-[18px]">share</span> Bagikan
                    </button>
                </div>

                <!-- Location Section -->
                <div class="flex flex-col gap-sm">
                    <h3 class="text-headline-md text-primary font-bold">Lokasi</h3>
                    <div class="bg-surface-container-lowest rounded-lg border border-outline-variant/20 overflow-hidden shadow-premium">
                        <div class="h-32 bg-surface-variant w-full relative">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary text-4xl material-symbols-fill">location_on</span>
                            </div>
                        </div>
                        <div class="p-sm flex justify-between items-center bg-surface-container-lowest">
                            <div class="flex items-start gap-sm">
                                <span class="material-symbols-outlined text-secondary mt-1">location_on</span>
                                <div>
                                    <p class="font-body-md text-on-surface">Jl. Samudera No. 45, Purus</p>
                                    <p class="font-body-sm text-on-surface-variant">Padang Barat, Kota Padang</p>
                                </div>
                            </div>
                            <button class="border border-secondary text-secondary px-sm py-xs rounded-lg font-label-md hover:bg-surface-variant transition-colors flex items-center gap-xs">
                                <span class="material-symbols-outlined text-[16px]">navigation</span> Mulai Navigasi
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Contact & Hours -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                    <div class="flex flex-col gap-sm p-sm bg-surface-container-lowest rounded-lg border border-outline-variant/10">
                        <h3 class="font-label-md text-on-surface-variant uppercase">Jam Operasional</h3>
                        <div class="flex items-center gap-sm text-on-surface">
                            <span class="material-symbols-outlined text-primary text-[20px]">schedule</span>
                            <span class="font-body-md">Buka • 10:00 - 22:00 WIB</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-sm p-sm bg-surface-container-lowest rounded-lg border border-outline-variant/10">
                        <h3 class="font-label-md text-on-surface-variant uppercase">Kontak</h3>
                        <div class="flex items-center gap-sm text-on-surface">
                            <span class="material-symbols-outlined text-primary text-[20px]">call</span>
                            <span class="font-body-md">+62 812-3456-7890</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-maritime-layout>
