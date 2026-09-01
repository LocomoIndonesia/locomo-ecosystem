<x-maritime-layout>
    <x-slot name="title">Login | Mandeh.Online</x-slot>

    <main class="flex-1 px-4 py-lg flex flex-col items-center justify-center max-w-container-max mx-auto w-full min-h-[calc(100vh-56px)]">
        <!-- Chat Sequence Area -->
        <div class="w-full max-w-md flex flex-col gap-sm mb-lg">
            <!-- Agent Message Bubble -->
            <div class="flex items-end gap-2 self-start w-full max-w-[85%]">
                <div class="w-8 h-8 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center flex-shrink-0 shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">support_agent</span>
                </div>
                <div class="bg-surface text-primary rounded-xl rounded-bl-[4px] p-4 shadow-premium border border-surface-tint/5">
                    <p class="font-body-md">Salam kembali. Masuk untuk kelola laporan dan validasi Anda.</p>
                </div>
            </div>

            <!-- Interactive Login Card -->
            <div class="w-full bg-surface rounded-[20px] p-6 shadow-modal border border-surface-tint/5 flex flex-col gap-md mt-sm self-center">
                <h2 class="text-headline-md text-primary text-center mb-xs">Login</h2>
                
                <!-- Input Group: Phone Number -->
                <div class="flex flex-col gap-1">
                    <label class="font-label-md text-primary/70 uppercase">Nomor WhatsApp</label>
                    <div class="flex items-center border border-outline-variant rounded-lg bg-surface-container-lowest focus-within:ring-1 focus-within:ring-secondary focus-within:border-secondary transition-colors overflow-hidden">
                        <div class="px-3 py-3 bg-surface-variant text-on-surface-variant font-body-md border-r border-outline-variant flex items-center justify-center">
                            +62
                        </div>
                        <input class="flex-1 px-3 py-3 bg-transparent border-none focus:ring-0 text-primary font-body-md placeholder-primary/30 outline-none w-full" placeholder="812 3456 7890" type="tel">
                    </div>
                </div>

                <!-- Input Group: OTP (Disabled visual indicator) -->
                <div class="flex flex-col gap-1 opacity-50 pointer-events-none">
                    <label class="font-label-md text-primary/70 uppercase">Kode OTP (6 Digit)</label>
                    <div class="flex items-center border border-outline-variant rounded-lg bg-surface-container-lowest overflow-hidden">
                        <input class="flex-1 px-3 py-3 bg-transparent border-none text-center tracking-[0.5em] text-primary font-body-md placeholder-primary/30 outline-none w-full" disabled placeholder="• • • • • •" type="text">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col gap-sm mt-sm">
                    <button class="w-full h-[56px] bg-secondary text-on-secondary font-body-lg font-bold rounded-lg flex items-center justify-center hover:opacity-90 active:scale-[0.98] transition-all">
                        Kirim Kode via WhatsApp
                    </button>
                    <button class="w-full py-2 text-center text-primary font-label-md hover:text-secondary transition-colors bg-transparent border border-transparent">
                        Masuk sebagai tamu (tanpa simpan data)
                    </button>
                </div>
            </div>
            
            <p class="text-center text-outline font-label-md mt-xs">
                Kami tidak pakai password. Cukup WA.
            </p>
        </div>
    </main>

    <!-- Bottom Input Bar (Disabled State) -->
    <div class="fixed bottom-0 left-0 w-full z-50 px-4 py-3 pb-safe bg-surface/90 backdrop-blur-lg border-t border-surface-tint/5 shadow-modal">
        <div class="max-w-container-max mx-auto flex items-center gap-2">
            <div class="flex-1 bg-surface-container-lowest border border-outline-variant/50 rounded-full flex items-center px-4 py-2 opacity-60">
                <input class="flex-1 bg-transparent border-none focus:ring-0 text-primary font-body-md placeholder:text-primary/50 outline-none w-full py-1 cursor-not-allowed" disabled placeholder="Login dulu untuk balas chat..." type="text">
            </div>
            <button class="w-10 h-10 rounded-full bg-surface-variant text-on-surface-variant flex items-center justify-center opacity-50 cursor-not-allowed" disabled>
                <span class="material-symbols-outlined text-[20px]">send</span>
            </button>
        </div>
    </div>
</x-maritime-layout>
