<x-maritime-layout>
    <x-slot name="title">Lapor Insiden | Mandeh.Online</x-slot>

    <div class="relative min-h-[calc(100vh-56px)] flex flex-col items-center justify-center overflow-hidden">
        <!-- Background Chat Canvas (Blurred) -->
        <div class="absolute inset-0 bg-surface/30 backdrop-blur-sm z-0 flex flex-col pointer-events-none opacity-40">
            <div class="flex-1 w-full max-w-container-max mx-auto p-md space-y-sm">
                <div class="flex justify-start">
                    <div class="bg-surface-container-lowest text-primary p-sm rounded-lg rounded-tl-sm border border-outline-variant/20 shadow-premium max-w-[80%]">
                        <p class="font-body-md">Sistem sedang memantau area pesisir.</p>
                    </div>
                </div>
                <div class="flex justify-end">
                    <div class="bg-primary text-on-primary p-sm rounded-lg rounded-tr-sm shadow-premium max-w-[80%]">
                        <p class="font-body-md">Tolong laporkan jika ada aktivitas mencurigakan.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scrim -->
        <div class="absolute inset-0 bg-on-background/20 z-40 cursor-pointer" onclick="window.history.back()"></div>

        <!-- Bottom Sheet -->
        <div class="absolute bottom-0 left-0 w-full h-[618px] bg-surface-container-lowest z-50 rounded-t-[24px] shadow-modal flex flex-col">
            <!-- Drag Handle -->
            <div class="w-full flex justify-center pt-sm pb-base">
                <div class="w-12 h-1 bg-outline-variant/50 rounded-full"></div>
            </div>
            <!-- Sheet Header -->
            <div class="px-md pb-md flex justify-between items-center border-b border-outline-variant/10">
                <h2 class="text-headline-md text-primary font-bold">Laporan Insiden</h2>
                <button class="p-base text-on-surface-variant hover:bg-surface-variant rounded-full transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Sheet Content -->
            <div class="flex-1 overflow-y-auto p-md space-y-lg scrollbar-hide pb-xl">
                <!-- Location Dropdown -->
                <div class="space-y-base">
                    <label class="font-label-md text-on-surface-variant uppercase block">Lokasi Insiden</label>
                    <div class="relative">
                        <select class="w-full bg-surface border border-outline-variant/20 rounded-DEFAULT px-md py-sm font-body-md text-on-surface appearance-none focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary transition-all shadow-sm">
                            <option disabled selected value="">Pilih Lokasi Pesisir...</option>
                            <option value="teluk-kabung">Teluk Kabung</option>
                            <option value="bungus">Bungus</option>
                            <option value="pulau-sikuai">Pulau Sikuai</option>
                            <option value="sungai-pisang">Sungai Pisang</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-sm pointer-events-none text-on-surface-variant">
                            <span class="material-symbols-outlined">expand_more</span>
                        </div>
                    </div>
                </div>
                <!-- Severity Area (Boat Icons) -->
                <div class="space-y-base">
                    <label class="font-label-md text-on-surface-variant uppercase block">Tingkat Keparahan (1-5)</label>
                    <div class="flex gap-base justify-start">
                        <button class="text-secondary hover:text-secondary-fixed transition-colors">
                            <span class="material-symbols-outlined text-[32px] material-symbols-fill">sailing</span>
                        </button>
                        <button class="text-secondary hover:text-secondary-fixed transition-colors">
                            <span class="material-symbols-outlined text-[32px] material-symbols-fill">sailing</span>
                        </button>
                        <button class="text-secondary hover:text-secondary-fixed transition-colors">
                            <span class="material-symbols-outlined text-[32px] material-symbols-fill">sailing</span>
                        </button>
                        <button class="text-outline-variant hover:text-secondary transition-colors">
                            <span class="material-symbols-outlined text-[32px]">sailing</span>
                        </button>
                        <button class="text-outline-variant hover:text-secondary transition-colors">
                            <span class="material-symbols-outlined text-[32px]">sailing</span>
                        </button>
                    </div>
                </div>
                <!-- Text Area -->
                <div class="space-y-base">
                    <label class="font-label-md text-on-surface-variant uppercase block">Detail Laporan</label>
                    <textarea class="w-full bg-surface border border-outline-variant/20 rounded-DEFAULT px-md py-sm font-body-md text-on-surface placeholder:text-primary/30 focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary transition-all shadow-sm resize-none" placeholder="Ceritakan singkat..." rows="4"></textarea>
                </div>
            </div>
            <!-- Sheet Footer -->
            <div class="p-md bg-surface-container-lowest border-t border-outline-variant/10">
                <button class="w-full bg-secondary hover:bg-secondary/90 text-on-secondary font-body-lg font-bold py-[14px] rounded-DEFAULT transition-colors shadow-premium">
                    Kirim Laporan Berdaulat
                </button>
            </div>
        </div>
    </div>
</x-maritime-layout>
