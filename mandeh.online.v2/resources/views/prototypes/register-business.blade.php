<x-maritime-layout>
    <x-slot name="title">Daftar Usaha | Mandeh.Online</x-slot>

    <main class="flex-grow w-full max-w-container-max mx-auto px-6 py-lg flex flex-col gap-lg min-h-[calc(100vh-56px)]">
        <!-- Agent Bubble -->
        <div class="flex justify-start w-full">
            <div class="bg-surface-container-lowest text-primary rounded-2xl rounded-tl-sm p-4 shadow-premium border border-outline-variant/10 max-w-[85%]">
                <p class="font-body-md">Daftarkan usaha Anda agar pengunjung bisa scan dan beri nilai jujur.</p>
            </div>
        </div>

        <!-- Main Card: Application Form -->
        <div class="bg-surface-container-lowest rounded-[20px] shadow-modal border border-outline-variant/10 p-6 flex flex-col gap-md">
            <!-- Stepper -->
            <div class="flex justify-center items-center gap-2 mb-2">
                <div class="w-2 h-2 rounded-full bg-primary"></div>
                <div class="w-2 h-2 rounded-full bg-surface-variant"></div>
            </div>

            <!-- Form Content -->
            <div class="flex flex-col gap-6">
                <!-- Nama Usaha -->
                <div class="flex flex-col gap-2">
                    <label class="font-label-md text-on-surface-variant uppercase">Nama Usaha</label>
                    <input class="w-full bg-surface border-none rounded-lg p-3 font-body-md text-on-surface focus:ring-2 focus:ring-secondary placeholder:text-primary/30" placeholder="Contoh: Warung Nasi Amak" type="text">
                </div>

                <!-- Kategori -->
                <div class="flex flex-col gap-2">
                    <label class="font-label-md text-on-surface-variant uppercase">Kategori</label>
                    <div class="flex flex-wrap gap-2">
                        <button class="px-4 py-2 rounded-full border border-secondary text-secondary font-label-md bg-surface-container-lowest hover:bg-surface-variant transition-colors">Warung</button>
                        <button class="px-4 py-2 rounded-full border border-outline-variant text-on-surface-variant font-label-md bg-surface-container-lowest hover:bg-surface-variant transition-colors">Kapal</button>
                        <button class="px-4 py-2 rounded-full border border-outline-variant text-on-surface-variant font-label-md bg-surface-container-lowest hover:bg-surface-variant transition-colors">Parkir</button>
                        <button class="px-4 py-2 rounded-full border border-outline-variant text-on-surface-variant font-label-md bg-surface-container-lowest hover:bg-surface-variant transition-colors">Homestay</button>
                        <button class="px-4 py-2 rounded-full border border-outline-variant text-on-surface-variant font-label-md bg-surface-container-lowest hover:bg-surface-variant transition-colors">Toilet</button>
                        <button class="px-4 py-2 rounded-full border border-outline-variant text-on-surface-variant font-label-md bg-surface-container-lowest hover:bg-surface-variant transition-colors">Lainnya</button>
                    </div>
                </div>

                <!-- Lokasi -->
                <div class="flex flex-col gap-2">
                    <label class="font-label-md text-on-surface-variant uppercase">Lokasi</label>
                    <button class="w-full flex items-center justify-center gap-2 bg-surface-variant/50 border border-outline-variant/10 rounded-lg p-3 text-primary hover:bg-surface-variant transition-colors">
                        <span class="material-symbols-outlined">location_on</span>
                        <span class="font-body-md">Pin di Maps</span>
                    </button>
                </div>

                <!-- Nomor WA -->
                <div class="flex flex-col gap-2">
                    <label class="font-label-md text-on-surface-variant uppercase">Nomor WA Pemilik</label>
                    <div class="flex relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant font-body-md">+62</span>
                        <input class="w-full bg-surface border-none rounded-lg p-3 pl-12 font-body-md text-on-surface focus:ring-2 focus:ring-secondary placeholder:text-primary/30" placeholder="812 3456 7890" type="tel">
                    </div>
                </div>
            </div>

            <!-- Primary Action -->
            <button class="w-full mt-4 bg-secondary text-on-primary font-body-lg font-bold py-3 px-4 rounded-[12px] hover:opacity-90 transition-opacity">
                Ajukan QR Gratis
            </button>
        </div>

        <!-- Badge Preview -->
        <div class="flex justify-center w-full">
            <div class="bg-surface-variant/50 border border-outline-variant/30 rounded-full px-4 py-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-outline-variant text-[18px]">pending</span>
                <span class="font-label-md text-outline">Menunggu Verifikasi Nagari</span>
            </div>
        </div>
    </main>

    <!-- Bottom Input Bar (Disabled) -->
    <div class="w-full bg-surface-container-lowest border-t border-outline-variant/10 p-4 sticky bottom-0 z-40">
        <div class="max-w-container-max mx-auto relative">
            <input class="w-full bg-surface-variant/30 border-none rounded-full py-3 px-4 font-body-md text-on-surface-variant placeholder:text-on-surface-variant/50 cursor-not-allowed" disabled placeholder="Lengkapi form untuk mengajukan QR...">
            <button class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-on-surface-variant/50 cursor-not-allowed" disabled>
                <span class="material-symbols-outlined">send</span>
            </button>
        </div>
    </div>
</x-maritime-layout>
