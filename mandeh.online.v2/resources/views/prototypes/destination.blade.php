<x-maritime-layout>
    <x-slot name="title">Rute Destinasi | Mandeh.Online</x-slot>

    <div class="relative min-h-[calc(100vh-56px)] flex flex-col items-center justify-center overflow-hidden">
        <!-- Main Conversational Canvas (Background) -->
        <main class="flex-1 w-full max-w-container-max mx-auto px-md py-lg flex flex-col gap-sm overflow-y-auto pb-[100px] opacity-40 blur-[1px]">
            <!-- User Message -->
            <div class="flex justify-end w-full">
                <div class="bg-primary text-on-primary rounded-[16px] rounded-tr-[4px] p-sm max-w-[80%] md:max-w-[60%] shadow-premium">
                    <p class="font-body-md">Tolong cari rumah makan seafood terdekat.</p>
                </div>
            </div>
            <!-- System Message -->
            <div class="flex justify-start w-full">
                <div class="bg-surface-container-lowest text-primary rounded-[16px] rounded-tl-[4px] p-sm border border-outline-variant/20 shadow-premium max-w-[95%] md:max-w-[70%] flex flex-col gap-md">
                    <p class="font-body-md">Ditemukan 1 lokasi yang sangat direkomendasikan di sekitar Anda:</p>
                    <!-- Business Card inside Chat -->
                    <div class="bg-surface-container-lowest border border-outline-variant/20 rounded-[12px] flex flex-col overflow-hidden shadow-sm">
                        <div class="p-sm flex justify-between items-start border-b border-outline-variant/10 bg-surface/50">
                            <h2 class="text-headline-md text-primary font-bold">RM Pesisir</h2>
                            <div class="bg-surface-container text-secondary rounded-full px-base py-xs flex items-center gap-xs border border-secondary/20">
                                <span class="material-symbols-outlined text-[14px] material-symbols-fill">verified</span>
                                <span class="font-label-md">Diverifikasi Nagari</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Bottom Sheet Overlay -->
        <div class="fixed inset-0 z-50 flex flex-col justify-end bg-primary/10 backdrop-blur-[2px]">
            <div class="flex-1 w-full cursor-pointer" onclick="window.history.back()"></div>
            <div class="w-full max-w-container-max mx-auto h-[80vh] bg-surface-container-lowest rounded-t-[24px] border-t border-outline-variant/20 flex flex-col shadow-modal transition-transform duration-300">
                <!-- Drag Handle -->
                <div class="w-full flex justify-center pt-sm pb-base">
                    <div class="w-[40px] h-[4px] bg-outline-variant rounded-full"></div>
                </div>
                <!-- Header & Tabs -->
                <div class="px-md pb-base border-b border-outline-variant/10">
                    <h2 class="text-display-xl text-primary mb-md font-bold">RM Pesisir</h2>
                    <div class="flex gap-lg">
                        <button class="font-label-md text-primary border-b-2 border-primary pb-xs px-xs">Info</button>
                        <button class="font-label-md text-on-surface-variant hover:text-primary pb-xs px-xs">Menu</button>
                        <button class="font-label-md text-on-surface-variant hover:text-primary pb-xs px-xs">Laporan</button>
                    </div>
                </div>
                <!-- Tab Content (Info) -->
                <div class="flex-1 overflow-y-auto p-md flex flex-col gap-lg scrollbar-hide">
                    <!-- Map Area -->
                    <div class="w-full h-[160px] rounded-[12px] overflow-hidden bg-surface-variant border border-outline-variant/20 relative shadow-premium">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                        <div class="absolute bottom-sm right-sm bg-surface-container-lowest rounded-full p-xs border border-outline-variant/20 shadow-premium">
                            <span class="material-symbols-outlined text-primary">directions</span>
                        </div>
                    </div>
                    <!-- Info List -->
                    <div class="flex flex-col gap-md">
                        <div class="flex items-start gap-md">
                            <span class="material-symbols-outlined text-on-surface-variant mt-xs">location_on</span>
                            <div>
                                <p class="font-body-md text-on-surface font-bold">Jl. Raya Lintas Barat Sumatera No.45, Pesisir Selatan</p>
                                <p class="font-body-sm text-on-surface-variant mt-xs">200m dari lokasi Anda saat ini</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-md">
                            <span class="material-symbols-outlined text-on-surface-variant mt-xs">schedule</span>
                            <div>
                                <p class="font-body-md text-secondary font-bold uppercase">Buka Sekarang</p>
                                <p class="font-body-sm text-on-surface-variant mt-xs">08:00 - 22:00 WIB</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-md">
                            <span class="material-symbols-outlined text-on-surface-variant mt-xs">call</span>
                            <div>
                                <p class="font-body-md text-on-surface font-bold">+62 812 3456 7890</p>
                            </div>
                        </div>
                    </div>
                    <!-- Action Button -->
                    <div class="mt-auto pt-md pb-xl">
                        <button class="w-full bg-primary text-on-primary font-body-lg font-bold py-sm rounded-lg hover:bg-primary/90 transition-colors flex justify-center items-center gap-sm shadow-premium">
                            <span class="material-symbols-outlined">map</span>
                            Mulai Navigasi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-maritime-layout>
