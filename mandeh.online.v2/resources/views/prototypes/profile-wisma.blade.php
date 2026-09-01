<x-maritime-layout>
    <x-slot name="title">Wisma Pantai Carocok | Mandeh.Online</x-slot>

    <div class="relative min-h-[calc(100vh-56px)] flex flex-col items-center justify-center overflow-hidden">
        <!-- Blurred Background Context (Chat Portal) -->
        <div class="absolute inset-0 z-0 bg-surface-container-low overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-primary/5 to-surface-variant/20 backdrop-blur-[2px]"></div>
            <div class="max-w-container-max mx-auto px-base py-lg opacity-40">
                <!-- Simulated Chat Bubbles -->
                <div class="flex flex-col gap-sm w-full max-w-2xl mx-auto">
                    <div class="self-end bg-primary text-on-primary rounded-[16px] rounded-br-[4px] p-md max-w-[80%] shadow-premium">
                        <p class="font-body-md">Can you recommend a place to stay near the beach?</p>
                    </div>
                    <div class="self-start bg-surface-container-lowest text-primary rounded-[16px] rounded-bl-[4px] p-md max-w-[80%] shadow-premium border border-outline-variant/20">
                        <p class="font-body-md">Here is a verified option right by the coast.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Sheet Container -->
        <div class="absolute inset-x-0 bottom-0 z-10 flex flex-col items-center pointer-events-none">
            <!-- Sheet Content -->
            <div class="bg-surface-container-lowest w-full max-w-[600px] rounded-t-[16px] shadow-modal pointer-events-auto flex flex-col max-h-[90vh] transition-transform duration-300">
                <!-- Drag Handle -->
                <div class="w-full flex justify-center py-base">
                    <div class="w-12 h-1 bg-outline-variant rounded-full"></div>
                </div>
                <!-- Header -->
                <div class="px-md pb-md flex justify-between items-start border-b border-outline-variant/10">
                    <div>
                        <h2 class="text-headline-md text-primary font-bold">Wisma Pantai Carocok</h2>
                        <div class="flex items-center gap-xs mt-base bg-secondary/10 px-sm py-xs rounded-full inline-flex">
                            <span class="material-symbols-outlined text-[16px] text-secondary material-symbols-fill">verified_user</span>
                            <span class="font-label-md text-secondary uppercase">Terverifikasi: Niniak Mamak & Pemda Pessel</span>
                        </div>
                    </div>
                    <button class="p-xs text-on-surface-variant hover:text-primary transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <!-- Scrollable Content -->
                <div class="overflow-y-auto px-md py-md flex flex-col gap-lg pb-xl scrollbar-hide">
                    <!-- Rating & Categories -->
                    <div class="flex flex-col gap-md">
                        <div class="flex items-center gap-sm">
                            <div class="flex text-secondary">
                                <span class="material-symbols-outlined text-[20px] material-symbols-fill">sailing</span>
                                <span class="material-symbols-outlined text-[20px] material-symbols-fill">sailing</span>
                                <span class="material-symbols-outlined text-[20px] material-symbols-fill">sailing</span>
                                <span class="material-symbols-outlined text-[20px] material-symbols-fill">sailing</span>
                                <span class="material-symbols-outlined text-[20px]">sailing</span>
                            </div>
                            <span class="font-headline-md text-primary font-bold">4.8/5</span>
                            <span class="font-body-sm text-on-surface-variant">(120 Ulasan)</span>
                        </div>
                        <div class="flex flex-wrap gap-xs">
                            <span class="bg-surface-variant text-on-surface-variant px-sm py-xs rounded-full font-label-md uppercase border border-outline-variant/20">Penginapan</span>
                            <span class="bg-surface-variant text-on-surface-variant px-sm py-xs rounded-full font-label-md uppercase border border-outline-variant/20">Cagar Budaya</span>
                        </div>
                    </div>
                    <!-- Location -->
                    <div class="bg-surface-container-low p-md rounded-[12px] border border-outline-variant/10 flex flex-col gap-md">
                        <div class="flex gap-sm">
                            <span class="material-symbols-outlined text-primary mt-xs">location_on</span>
                            <div>
                                <h4 class="font-body-md font-bold mb-xs">Alamat</h4>
                                <p class="font-body-sm text-on-surface-variant">Jl. Pantai Carocok, Painan, Pesisir Selatan</p>
                            </div>
                        </div>
                        <!-- Map Placeholder -->
                        <div class="w-full h-32 bg-surface-variant rounded-lg overflow-hidden relative border border-outline-variant/20">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <span class="material-symbols-outlined text-error text-[32px] drop-shadow-md material-symbols-fill">location_pin</span>
                            </div>
                        </div>
                        <button class="w-full bg-primary text-on-primary font-body-md font-bold py-sm rounded-lg flex items-center justify-center gap-sm hover:bg-primary/90 transition-colors shadow-sm">
                            <span class="material-symbols-outlined">explore</span>
                            Mulai Navigasi
                        </button>
                    </div>
                    <!-- Contact Person -->
                    <div class="flex flex-col gap-sm">
                        <h3 class="font-label-md text-on-surface-variant uppercase tracking-wider">Penanggung Jawab</h3>
                        <div class="flex items-center justify-between bg-surface-container-lowest p-md rounded-[12px] border border-outline-variant/20 shadow-premium">
                            <div class="flex items-center gap-sm">
                                <div class="w-10 h-10 bg-surface-variant rounded-full flex items-center justify-center">
                                    <span class="material-symbols-outlined text-primary">person</span>
                                </div>
                                <div>
                                    <p class="font-body-md font-bold text-on-background">Bpk. Rizal</p>
                                    <p class="font-body-sm text-on-surface-variant">Pengelola Wisma</p>
                                </div>
                            </div>
                            <div class="flex gap-sm">
                                <button class="w-10 h-10 rounded-full bg-secondary/10 text-secondary flex items-center justify-center hover:bg-secondary/20 transition-colors border border-secondary/20">
                                    <span class="material-symbols-outlined">forum</span>
                                </button>
                                <button class="w-10 h-10 rounded-full bg-surface-variant text-primary flex items-center justify-center hover:bg-surface-dim transition-colors border border-outline-variant/20">
                                    <span class="material-symbols-outlined">call</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Mitra & Sponsor -->
                    <div class="mt-md pt-md border-t border-outline-variant/10 flex flex-col items-center gap-md">
                        <p class="font-label-md text-outline-variant uppercase tracking-wider">Didukung Oleh</p>
                        <div class="flex justify-center items-center gap-md opacity-60 grayscale hover:grayscale-0 transition-all duration-300">
                            <span class="material-symbols-outlined text-[32px] text-on-surface-variant">account_balance</span>
                            <span class="material-symbols-outlined text-[32px] text-on-surface-variant">water_drop</span>
                            <span class="material-symbols-outlined text-[32px] text-on-surface-variant">eco</span>
                            <span class="material-symbols-outlined text-[32px] text-on-surface-variant">assured_workload</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-maritime-layout>
