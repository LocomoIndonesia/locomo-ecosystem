<x-maritime-layout>
    <x-slot name="title">Mitra Mandeh | Mandeh.Online</x-slot>

    <main x-data="{ 
        selectedRole: 'nagari',
        selectedAreas: ['Mandeh'],
        toggleArea(area) {
            if (this.selectedAreas.includes(area)) {
                this.selectedAreas = this.selectedAreas.filter(a => a !== area);
            } else {
                this.selectedAreas.push(area);
            }
        }
    }" class="flex-1 w-full px-4 md:px-8 pt-lg pb-xl flex flex-col gap-lg max-w-container-max mx-auto min-h-[calc(100vh-56px)]">
        <!-- Agent Bubble -->
        <div class="flex justify-start w-full">
            <div class="bg-surface-container-lowest text-primary px-md py-sm rounded-xl rounded-tl-sm max-w-[85%] border border-outline-variant/10 shadow-premium">
                <p class="font-body-md">Mitra adalah penjaga marwah. Pilih peran Anda.</p>
            </div>
        </div>

        <!-- Partner Type Selection -->
        <div class="flex flex-col gap-sm">
            <h3 class="font-label-md text-outline-variant uppercase ml-2">Pilih Peran Kemitraan</h3>
            <div class="flex gap-base overflow-x-auto pb-4 snap-x snap-mandatory scrollbar-hide">
                <!-- Komunitas Lokal -->
                <div @click="selectedRole = 'komunitas'" 
                     :class="selectedRole === 'komunitas' ? 'bg-surface-container-low border-2 border-primary shadow-modal' : 'bg-surface-container-lowest border border-outline-variant/20 shadow-premium'"
                     class="flex-none w-48 snap-center rounded-lg p-sm flex flex-col items-start gap-xs cursor-pointer hover:border-secondary transition-all relative overflow-hidden">
                    <template x-if="selectedRole === 'komunitas'">
                        <div class="absolute top-0 right-0 bg-primary text-on-primary font-label-md px-2 py-1 rounded-bl-DEFAULT">Dipilih</div>
                    </template>
                    <span class="material-symbols-outlined material-symbols-fill" :class="selectedRole === 'komunitas' ? 'text-primary' : 'text-secondary'">shield</span>
                    <span class="font-label-md font-bold" :class="selectedRole === 'komunitas' ? 'text-primary mt-2' : 'text-on-surface'">Komunitas Lokal</span>
                </div>

                <!-- Niniak Mamak / Wali Nagari -->
                <div @click="selectedRole = 'nagari'" 
                     :class="selectedRole === 'nagari' ? 'bg-surface-container-low border-2 border-primary shadow-modal' : 'bg-surface-container-lowest border border-outline-variant/20 shadow-premium'"
                     class="flex-none w-48 snap-center rounded-lg p-sm flex flex-col items-start gap-xs cursor-pointer hover:border-secondary transition-all relative overflow-hidden">
                    <template x-if="selectedRole === 'nagari'">
                        <div class="absolute top-0 right-0 bg-primary text-on-primary font-label-md px-2 py-1 rounded-bl-DEFAULT">Dipilih</div>
                    </template>
                    <span class="material-symbols-outlined material-symbols-fill" :class="selectedRole === 'nagari' ? 'text-primary' : 'text-secondary'">admin_panel_settings</span>
                    <span class="font-label-md font-bold" :class="selectedRole === 'nagari' ? 'text-primary mt-2' : 'text-on-surface'">Niniak Mamak / Wali Nagari</span>
                </div>

                <!-- Pemerintah Daerah -->
                <div @click="selectedRole = 'pemda'" 
                     :class="selectedRole === 'pemda' ? 'bg-surface-container-low border-2 border-primary shadow-modal' : 'bg-surface-container-lowest border border-outline-variant/20 shadow-premium'"
                     class="flex-none w-48 snap-center rounded-lg p-sm flex flex-col items-start gap-xs cursor-pointer hover:border-secondary transition-all relative overflow-hidden">
                    <template x-if="selectedRole === 'pemda'">
                        <div class="absolute top-0 right-0 bg-primary text-on-primary font-label-md px-2 py-1 rounded-bl-DEFAULT">Dipilih</div>
                    </template>
                    <span class="material-symbols-outlined material-symbols-fill" :class="selectedRole === 'pemda' ? 'text-primary' : 'text-secondary'">gavel</span>
                    <span class="font-label-md font-bold" :class="selectedRole === 'pemda' ? 'text-primary mt-2' : 'text-on-surface'">Pemerintah Daerah</span>
                </div>

                <!-- Sponsor / CSR -->
                <div @click="selectedRole = 'sponsor'" 
                     :class="selectedRole === 'sponsor' ? 'bg-surface-container-low border-2 border-primary shadow-modal' : 'bg-surface-container-lowest border border-outline-variant/20 shadow-premium'"
                     class="flex-none w-48 snap-center rounded-lg p-sm flex flex-col items-start gap-xs cursor-pointer hover:border-secondary transition-all relative overflow-hidden">
                    <template x-if="selectedRole === 'sponsor'">
                        <div class="absolute top-0 right-0 bg-primary text-on-primary font-label-md px-2 py-1 rounded-bl-DEFAULT">Dipilih</div>
                    </template>
                    <span class="material-symbols-outlined material-symbols-fill" :class="selectedRole === 'sponsor' ? 'text-primary' : 'text-secondary'">verified_user</span>
                    <span class="font-label-md font-bold" :class="selectedRole === 'sponsor' ? 'text-primary mt-2' : 'text-on-surface'">Sponsor / CSR</span>
                </div>
            </div>
        </div>

        <!-- Registration Form Card -->
        <div class="flex justify-start w-full">
            <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-xl p-md shadow-modal w-full max-w-[90%] md:max-w-[600px] flex flex-col gap-md">
                <div class="flex items-center gap-sm border-b border-outline-variant/10 pb-sm">
                    <span class="material-symbols-outlined text-primary material-symbols-fill">description</span>
                    <h4 class="text-headline-md text-primary font-bold">Formulir Kemitraan</h4>
                </div>
                <div class="flex flex-col gap-md">
                    <div class="flex flex-col gap-xs">
                        <label class="font-label-md text-on-surface-variant uppercase">Nama Lembaga</label>
                        <input class="w-full bg-surface border border-outline-variant/20 rounded-lg px-sm py-xs font-body-md text-on-surface focus:ring-1 focus:ring-secondary focus:border-secondary outline-none placeholder:text-outline-variant/50" placeholder="Masukkan nama lembaga...">
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="font-label-md text-on-surface-variant uppercase">Penanggung Jawab</label>
                        <input class="w-full bg-surface border border-outline-variant/20 rounded-lg px-sm py-xs font-body-md text-on-surface focus:ring-1 focus:ring-secondary focus:border-secondary outline-none placeholder:text-outline-variant/50" placeholder="Nama lengkap...">
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="font-label-md text-on-surface-variant uppercase">Email resmi</label>
                        <input class="w-full bg-surface border border-outline-variant/20 rounded-lg px-sm py-xs font-body-md text-on-surface focus:ring-1 focus:ring-secondary focus:border-secondary outline-none placeholder:text-outline-variant/50" placeholder="email@lembaga.id" type="email">
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="font-label-md text-on-surface-variant uppercase mb-1">Area yang dijaga</label>
                        <div class="flex flex-wrap gap-xs">
                            <!-- Area: Carocok -->
                            <button @click="toggleArea('Carocok')"
                                    :class="selectedAreas.includes('Carocok') ? 'bg-primary text-on-primary shadow-sm' : 'bg-surface-container-high text-primary border border-primary/20'"
                                    class="font-label-md px-sm py-1 rounded-full flex items-center gap-1 transition-all hover:scale-105 active:scale-95">
                                Carocok
                                <span x-show="selectedAreas.includes('Carocok')" class="material-symbols-outlined text-[14px]">close</span>
                            </button>

                            <!-- Area: Mandeh -->
                            <button @click="toggleArea('Mandeh')"
                                    :class="selectedAreas.includes('Mandeh') ? 'bg-primary text-on-primary shadow-sm' : 'bg-surface-container-high text-primary border border-primary/20'"
                                    class="font-label-md px-sm py-1 rounded-full flex items-center gap-1 transition-all hover:scale-105 active:scale-95">
                                Mandeh
                                <span x-show="selectedAreas.includes('Mandeh')" class="material-symbols-outlined text-[14px]">close</span>
                            </button>

                            <!-- Area: Tarusan -->
                            <button @click="toggleArea('Tarusan')"
                                    :class="selectedAreas.includes('Tarusan') ? 'bg-primary text-on-primary shadow-sm' : 'bg-surface-container-high text-primary border border-primary/20'"
                                    class="font-label-md px-sm py-1 rounded-full flex items-center gap-1 transition-all hover:scale-105 active:scale-95">
                                Tarusan
                                <span x-show="selectedAreas.includes('Tarusan')" class="material-symbols-outlined text-[14px]">close</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="pt-sm border-t border-outline-variant/10">
                    <button class="w-full bg-secondary text-on-secondary font-body-lg py-sm rounded-lg hover:bg-secondary/90 transition-colors shadow-sm flex justify-center items-center gap-sm">
                        <span class="material-symbols-outlined">send</span>
                        Ajukan Kemitraan
                    </button>
                </div>
                <p class="font-label-md text-outline-variant text-center mt-2 opacity-80">
                    Mitra terverifikasi mendapat dashboard khusus dan badge di setiap QR.
                </p>
            </div>
        </div>
    </main>

    <!-- Bottom Input Bar -->
    <div class="fixed bottom-0 left-0 md:left-[280px] w-full md:w-[calc(100%-280px)] flex justify-center bg-surface/90 backdrop-blur-lg border-t border-outline-variant/10 p-4 z-40 shadow-modal">
        <div class="w-full max-w-[800px] flex items-center gap-sm bg-surface border border-outline-variant rounded-full px-4 py-2 mx-auto transition-all"
             :class="selectedRole ? 'opacity-100' : 'opacity-60 cursor-not-allowed'">
            <span class="material-symbols-outlined text-secondary" :class="selectedRole ? '' : 'text-outline-variant'">add_circle</span>
            <input class="flex-1 bg-transparent border-none outline-none font-body-md text-on-surface placeholder:text-primary/30" 
                   :disabled="!selectedRole"
                   :placeholder="selectedRole ? 'Tulis pesan untuk admin Mandeh...' : 'Pilih peran mitra untuk melanjutkan...'">
            <span class="material-symbols-outlined text-secondary" :class="selectedRole ? '' : 'text-outline-variant'">mic</span>
        </div>
    </div>
</x-maritime-layout>
