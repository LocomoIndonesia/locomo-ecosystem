<x-maritime-layout>
    <x-slot name="title">Scanner | Mandeh.Online</x-slot>

    <div class="relative min-h-[calc(100vh-56px)] flex flex-col items-center justify-center overflow-hidden bg-background">
        <!-- Live Camera Background Simulation -->
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1559128010-7c1ad6e1b6a5?q=80&w=1200&auto=format&fit=crop" alt="Camera View Simulation" class="w-full h-full object-cover filter blur-sm">
        </div>

        <!-- Scanner Overlay -->
        <div class="absolute inset-0 z-10 pointer-events-none flex items-center justify-center">
            <div class="relative w-[280px] h-[280px] rounded-xl scanner-cutout">
                <!-- Emerald Coast Corner Indicators -->
                <div class="scanner-corner corner-tl"></div>
                <div class="scanner-corner corner-tr"></div>
                <div class="scanner-corner corner-bl"></div>
                <div class="scanner-corner corner-br"></div>
                <!-- Scanning Line Animation -->
                <div class="absolute left-0 right-0 top-1/2 h-[2px] bg-secondary opacity-80 shadow-[0_0_8px_#006c47] animate-pulse"></div>
            </div>
        </div>

        <!-- UI Layer -->
        <div class="absolute inset-0 z-20 w-full max-w-container-max mx-auto px-md pt-lg flex flex-col gap-lg pointer-events-none">
            <!-- Header Actions -->
            <div class="flex justify-between items-start w-full pointer-events-auto">
                <button class="w-10 h-10 rounded-full bg-surface/80 backdrop-blur-md flex items-center justify-center text-primary shadow-premium hover:bg-surface transition-colors border border-outline-variant/10">
                    <span class="material-symbols-outlined">close</span>
                </button>
                <div class="flex flex-col gap-sm">
                    <button class="w-10 h-10 rounded-full bg-surface/80 backdrop-blur-md flex items-center justify-center text-primary shadow-premium hover:bg-surface transition-colors border border-outline-variant/10">
                        <span class="material-symbols-outlined">flashlight_on</span>
                    </button>
                    <button class="w-10 h-10 rounded-full bg-surface/80 backdrop-blur-md flex items-center justify-center text-primary shadow-premium hover:bg-surface transition-colors border border-outline-variant/10">
                        <span class="material-symbols-outlined">photo_library</span>
                    </button>
                </div>
            </div>

            <!-- Chat Bubble Instruction -->
            <div class="flex justify-center w-full">
                <div class="bg-surface rounded-[16px] rounded-tl-[4px] px-md py-sm shadow-modal border border-outline-variant/10 max-w-[85%] pointer-events-auto">
                    <p class="font-body-md text-primary m-0">Mencari informasi lokasi... Silakan arahkan kamera ke QR Code.</p>
                </div>
            </div>
        </div>

        <!-- Bottom Input Bar (Disabled) -->
        <div class="absolute bottom-0 left-0 right-0 z-20 w-full pb-xl px-md flex justify-center pointer-events-none">
            <div class="w-full max-w-md bg-surface opacity-50 rounded-full border border-outline-variant/20 px-sm py-sm flex items-center gap-sm shadow-modal">
                <button class="text-on-surface-variant w-10 h-10 flex items-center justify-center rounded-full">
                    <span class="material-symbols-outlined">add</span>
                </button>
                <div class="flex-1 font-body-md text-on-surface-variant/50 truncate">
                    Portal is disabled during scan...
                </div>
                <button class="text-on-surface-variant w-10 h-10 flex items-center justify-center rounded-full">
                    <span class="material-symbols-outlined">send</span>
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <style>
        .scanner-cutout {
            box-shadow: 0 0 0 9999px rgba(0, 30, 64, 0.6);
        }
        .scanner-corner {
            position: absolute;
            width: 32px;
            height: 32px;
            border-color: #006c47;
            border-style: solid;
        }
        .corner-tl { top: -2px; left: -2px; border-width: 4px 0 0 4px; border-top-left-radius: 12px; }
        .corner-tr { top: -2px; right: -2px; border-width: 4px 4px 0 0; border-top-right-radius: 12px; }
        .corner-bl { bottom: -2px; left: -2px; border-width: 0 0 4px 4px; border-bottom-left-radius: 12px; }
        .corner-br { bottom: -2px; right: -2px; border-width: 0 4px 4px 0; border-bottom-right-radius: 12px; }
    </style>
    @endpush
</x-maritime-layout>
