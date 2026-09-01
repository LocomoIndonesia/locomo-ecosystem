<x-maritime-layout>
    <x-slot name="title">Explorer | Mandeh.Online</x-slot>

    <!-- Background Layer -->
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center opacity-60 scale-105" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCpbyYKJ8_1HGV0u2IOW9-eqkf9cNJAZoUy_QeqPjLbwRsI9FvuPGTlmZLcGEGn3n5mQYlFTPk1HTvSwvhv99r2UBSzsP2IH1i8Jl8c6AeEcJEyF7flCpZkBa96Sy9upyfNEXTOTIKQptaivMygRl8dSzqtiHH8nzt3C14nKNN5jEF_C-oCeYhZ3i6XLav_DOXBRs9FiODHJ2qSoshCL80-VkjtCBugCxp7mkGrHH_VOF7IvYGITObKILJrmnHCJsLwMEVbko5kqQ');"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-background via-transparent to-background"></div>
    </div>

    <!-- Chat Canvas -->
    <main class="relative z-10 flex-1 overflow-y-auto w-full flex flex-col items-center pt-xl pb-[120px] px-md h-[calc(100vh-56px)] scrollbar-hide">
        <div class="w-full max-w-[800px] flex flex-col gap-sm">
            <!-- Greeting Bubble (System) -->
            <div class="flex flex-col gap-xs max-w-[85%] self-start">
                <div class="bg-surface-container-lowest/80 backdrop-blur-md text-primary font-body-md rounded-xl rounded-bl-[4px] p-md border border-outline-variant/20 shadow-premium">
                    <p>Welcome aboard. The maritime network is active and conditions are optimal. How can I assist you with your navigation or inquiries today?</p>
                </div>
            </div>

            <!-- Quick Chips -->
            <div class="flex flex-wrap gap-sm self-start mt-xs">
                <button class="px-md py-sm border border-secondary bg-surface/50 backdrop-blur-sm text-secondary font-label-md rounded-full hover:bg-surface-variant transition-colors flex items-center gap-xs">
                    <span class="material-symbols-outlined text-[16px]">restaurant</span>
                    Seafood Terdekat
                </button>
                <button class="px-md py-sm border border-secondary bg-surface/50 backdrop-blur-sm text-secondary font-label-md rounded-full hover:bg-surface-variant transition-colors flex items-center gap-xs">
                    <span class="material-symbols-outlined text-[16px]">beach_access</span>
                    Info Wisata
                </button>
                <button class="px-md py-sm border border-secondary bg-surface/50 backdrop-blur-sm text-secondary font-label-md rounded-full hover:bg-surface-variant transition-colors flex items-center gap-xs">
                    <span class="material-symbols-outlined text-[16px]">shopping_bag</span>
                    Produk Lokal
                </button>
                <button class="px-md py-sm border border-secondary bg-surface/50 backdrop-blur-sm text-secondary font-label-md rounded-full hover:bg-surface-variant transition-colors flex items-center gap-xs">
                    <span class="material-symbols-outlined text-[16px]">report</span>
                    Lapor Insiden
                </button>
            </div>

            <!-- User Message Example -->
            <div class="flex flex-col gap-xs max-w-[85%] self-end mt-lg">
                <div class="bg-primary text-on-primary font-body-md rounded-xl rounded-br-[4px] p-md shadow-premium">
                    <p>Provide the current live telemetry for the central sector.</p>
                </div>
            </div>

            <!-- Live Pulse Bubble (System Card) -->
            <div class="flex flex-col gap-xs max-w-[85%] self-start mt-sm">
                <div class="bg-surface-container-lowest/80 backdrop-blur-md text-primary rounded-xl rounded-bl-[4px] p-md border border-outline-variant/20 shadow-premium w-full min-w-[320px]">
                    <div class="flex justify-between items-center border-b border-outline-variant/20 pb-sm mb-md">
                        <div class="flex items-center gap-xs">
                            <span class="material-symbols-outlined text-secondary text-[20px] material-symbols-fill">sensors</span>
                            <span class="font-label-md text-secondary tracking-widest uppercase">Live Pulse</span>
                        </div>
                        <span class="font-label-md text-on-surface-variant flex items-center gap-xs">
                            <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                            Active
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-x-lg gap-y-md">
                        <div>
                            <p class="font-label-md text-on-surface-variant mb-xs">Wind Vector</p>
                            <p class="font-headline-md text-primary">14 kts NE</p>
                        </div>
                        <div>
                            <p class="font-label-md text-on-surface-variant mb-xs">Tide Phase</p>
                            <p class="font-headline-md text-primary">Incoming</p>
                        </div>
                        <div>
                            <p class="font-label-md text-on-surface-variant mb-xs">Swell Height</p>
                            <p class="font-body-lg text-primary">1.2m</p>
                        </div>
                        <div>
                            <p class="font-label-md text-on-surface-variant mb-xs">Visibility</p>
                            <p class="font-body-lg text-primary">Clear (10nm)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- BottomNavBar / Input Area -->
    <div class="fixed bottom-0 left-0 w-full z-50 flex justify-center items-center pb-lg px-md pointer-events-none" x-data="{ showTools: false }">
        <nav class="bg-surface/90 backdrop-blur-lg border border-outline-variant/20 shadow-modal rounded-full h-[64px] flex items-center px-sm gap-xs w-full max-w-[800px] pointer-events-auto transition-all relative">
            <!-- Desktop Tools -->
            <div class="hidden md:flex items-center gap-xs">
                <button aria-label="Scan QR Code" class="text-on-surface-variant p-xs hover:bg-surface-variant rounded-full transition-all flex items-center justify-center shrink-0 w-10 h-10">
                    <span class="material-symbols-outlined text-[24px]">qr_code_2</span>
                </button>
                <button aria-label="Voice Input" class="text-on-surface-variant p-xs hover:bg-surface-variant rounded-full transition-all flex items-center justify-center shrink-0 w-10 h-10">
                    <span class="material-symbols-outlined text-[24px]">mic</span>
                </button>
                <button aria-label="Camera" class="text-on-surface-variant p-xs hover:bg-surface-variant rounded-full transition-all flex items-center justify-center shrink-0 w-10 h-10">
                    <span class="material-symbols-outlined text-[24px]">photo_camera</span>
                </button>
            </div>

            <!-- Mobile Tool Toggle -->
            <div class="md:hidden flex items-center relative">
                <button @click="showTools = !showTools" aria-label="More Tools" class="text-on-surface-variant p-xs hover:bg-surface-variant rounded-full transition-all flex items-center justify-center shrink-0 w-10 h-10">
                    <span class="material-symbols-outlined text-[24px]" x-text="showTools ? 'close' : 'more_vert'">more_vert</span>
                </button>

                <!-- Tool Context Menu (Mobile) -->
                <div x-show="showTools" 
                     @click.away="showTools = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     class="absolute bottom-full left-0 mb-4 bg-surface/95 backdrop-blur-xl border border-outline-variant/20 shadow-modal rounded-2xl p-sm flex flex-col gap-1 z-[60] min-w-[140px]">
                    <button class="flex items-center gap-sm px-md py-3 hover:bg-surface-variant rounded-xl transition-colors text-primary">
                        <span class="material-symbols-outlined text-[22px]">qr_code_2</span>
                        <span class="text-label-md font-bold">Scan QR</span>
                    </button>
                    <button class="flex items-center gap-sm px-md py-3 hover:bg-surface-variant rounded-xl transition-colors text-primary">
                        <span class="material-symbols-outlined text-[22px]">mic</span>
                        <span class="text-label-md font-bold">Voice</span>
                    </button>
                    <button class="flex items-center gap-sm px-md py-3 hover:bg-surface-variant rounded-xl transition-colors text-primary">
                        <span class="material-symbols-outlined text-[22px]">photo_camera</span>
                        <span class="text-label-md font-bold">Camera</span>
                    </button>
                </div>
            </div>

            <div class="flex-1 h-full py-sm px-xs ml-1">
                <input class="w-full h-full bg-transparent border-none focus:ring-0 text-primary font-body-md placeholder:text-primary/40 outline-none" placeholder="Ask Mandeh Explorer..." type="text">
            </div>
            <button aria-label="Send Message" class="bg-primary text-on-primary rounded-full p-xs hover:scale-105 active:scale-95 transition-all flex items-center justify-center h-11 w-11 shrink-0 ml-xs shadow-premium">
                <span class="material-symbols-outlined text-[22px] material-symbols-fill">send</span>
            </button>
        </nav>
    </div>
</x-maritime-layout>
