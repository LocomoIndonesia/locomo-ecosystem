<x-maritime-layout>
    <x-slot name="title">Explorer | Mandeh.Online</x-slot>
    
    <!-- Background Layer -->
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center opacity-60 scale-105" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCpbyYKJ8_1HGV0u2IOW9-eqkf9cNJAZoUy_QeqPjLbwRsI9FvuPGTlmZLcGEGn3n5mQYlFTPk1HTvSwvhv99r2UBSzsP2IH1i8Jl8c6AeEcJEyF7flCpZkBa96Sy9upyfNEXTOTIKQptaivMygRl8dSzqtiHH8nzt3C14nKNN5jEF_C-oCeYhZ3i6XLav_DOXBRs9FiODHJ2qSoshCL80-VkjtCBugCxp7mkGrHH_VOF7IvYGITObKILJrmnHCJsLwMEVbko5kqQ');"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-background via-transparent to-background"></div>
    </div>


    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        function onScanSuccess(decodedText, decodedResult) {
            // Assume URL is something like https://mandeh.online/v/beach-carocok
            const parts = decodedText.split('/');
            const slug = parts[parts.length - 1];
            // Navigate to the entity detail page
            window.location.href = `/v/${encodeURIComponent(slug)}`;
        }

        function onScanFailure(error) {
            // silent fail for most frame errors
        }
    </script>
    @endpush

    <!-- Global Canvas State Manager -->
    <div x-data="{ 
            canvasState: 'zero', 
            searchQuery: '',
            isScanning: false,
            interactions: [],
            html5QrCode: null,
            entityDetail: null,
            userRating: 0,
            userReview: '',
            showReviewForm: false,
            startScanner() {
                this.isScanning = true;
                this.$nextTick(() => {
                    this.html5QrCode = new Html5Qrcode('qr-reader');
                    this.html5QrCode.start(
                        { facingMode: 'environment' }, 
                        { fps: 10, qrbox: { width: 250, height: 250 } },
                        onScanSuccess,
                        onScanFailure
                    ).catch(err => {
                        console.error('Unable to start scanning', err);
                    });
                });
            },
            isTyping: false,
            processQuery(query) {
                if (!query || !query.trim()) return;
                this.canvasState = 'chat';
                this.interactions.push({role: 'user', content: query});
                this.isTyping = true;
                this.searchQuery = '';

                // Simulate Semantic Analysis & Gen UI Response
                setTimeout(() => {
                    this.isTyping = false;
                    let response = { role: 'assistant', content: 'Menganalisis permintaan Anda...', type: 'text' };
                    
                    if (query.toLowerCase().includes('seafood')) {
                        response = { 
                            role: 'assistant', 
                            type: 'gen-ui',
                            component: 'restaurant-list',
                            data: [
                                { name: 'RM. Pesisir', slug: 'rm-pesisir', dist: '150m', rating: 4.8, tags: ['Halal', 'Seafood'], type: 'business', description: 'Rumah makan seafood terbaik di Carocok Mandeh dengan panorama laut.', phone: '0812-3456-7890', address: 'Jl. Raya Carocok, Tarusan' },
                                { name: 'Kedai Ombak', slug: 'kedai-ombak', dist: '400m', rating: 4.5, tags: ['Fresh Catch'], type: 'business', description: 'Kedai sajian laut segar langsung dari nelayan lokal.', phone: '0813-9876-5432', address: 'Dermaga Carocok, Tarusan' }
                            ]
                        };
                    } else if (query.toLowerCase().includes('pulau')) {
                        response = {
                            role: 'assistant',
                            type: 'gen-ui',
                            component: 'island-info',
                            title: 'Pulau Sikuai',
                            status: 'Open',
                            transport: 'Boat from Carocok (15 mins)'
                        };
                    }

                    this.interactions.push(response);
                }, 1500);
            },
            showEntityDetail(entity) {
                this.entityDetail = entity;
                this.userRating = 0;
                this.userReview = '';
                this.showReviewForm = false;
            },
            closeEntityDetail() {
                this.entityDetail = null;
            },
            setRating(star) {
                this.userRating = star;
            },
            submitReview() {
                if (this.userRating === 0) return;
                // Simulated submission
                this.interactions.push({
                    role: 'assistant', 
                    type: 'text', 
                    content: '✅ Ulasan Anda untuk ' + this.entityDetail.name + ' berhasil dikirim! Rating: ' + this.userRating + '/5'
                });
                this.showReviewForm = false;
                this.entityDetail = null;
            },
            stopScanner() {
                if (this.html5QrCode) {
                    this.html5QrCode.stop().then(() => {
                        this.isScanning = false;
                    });
                }
            }
         }" 
         class="relative z-10 flex-1 flex flex-col items-center w-full h-full">

        <!-- ZERO STATE: The Search-Chat Canvas -->
        <template x-if="canvasState === 'zero'">
            <div class="flex-1 flex flex-col items-center justify-center w-full max-w-[800px] px-4 sm:px-md text-center animate-fade-in">
                <div class="mb-8 sm:mb-xl pt-8 sm:pt-16 md:pt-24">
                    <span class="material-symbols-outlined text-primary text-6xl material-symbols-fill animate-pulse-slow">anchor</span>
                    <h2 class="text-headline-lg font-extrabold text-primary mt-2 sm:mt-md tracking-tight">Apa yang ingin Anda jelajahi di Mandeh?</h2>
                    <p class="text-body-lg text-on-surface-variant mt-sm">Gerbang Digital Berdaulat untuk Wisata Pesisir Selatan</p>
                </div>

                <!-- Central Command Line -->
                <div class="w-full relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-primary to-secondary rounded-full blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                    <div class="relative flex items-center bg-surface border border-outline-variant/30 shadow-modal rounded-full p-2 h-[60px] sm:h-[72px]">
                        <span class="material-symbols-outlined ml-4 sm:ml-md text-primary/40">search</span>
                        <input x-model="searchQuery" 
                               @keyup.enter="processQuery(searchQuery)"
                               type="text" 
                               placeholder="Cari atau scan QR Code..." 
                               class="flex-1 bg-transparent border-none focus:ring-0 text-base sm:text-headline-sm font-medium placeholder:text-primary/20 px-3 sm:px-md">
                        
                        <button @click="canvasState = 'chat'; startScanner()" 
                                 class="bg-primary/5 text-primary hover:bg-primary/10 transition-colors p-3 sm:p-md rounded-full mr-1 flex items-center gap-2 sm:gap-sm">
                            <span class="material-symbols-outlined">qr_code_scanner</span>
                            <span class="font-label-lg font-bold pr-sm hidden md:block">Scan QR</span>
                        </button>
                    </div>
                </div>

                <!-- Quick Actions Chips: Refined Grid Layout -->
                <div class="grid grid-cols-3 gap-3 sm:gap-md mt-6 sm:mt-xl w-full">
                    <button @click="processQuery('Seafood terdekat')" class="group relative overflow-hidden bg-surface-container-lowest/80 backdrop-blur-md border border-outline-variant/30 p-3 sm:p-lg rounded-2xl sm:rounded-3xl hover:border-secondary/40 transition-all flex flex-col items-center gap-2 sm:gap-sm shadow-premium hover:shadow-premium-hover hover:-translate-y-1">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-secondary/10 flex items-center justify-center text-secondary group-hover:scale-110 group-hover:bg-secondary/20 transition-all">
                            <span class="material-symbols-outlined text-2xl sm:text-3xl">restaurant</span>
                        </div>
                        <span class="text-xs sm:text-sm font-bold text-primary">Seafood</span>
                        <div class="absolute inset-0 bg-gradient-to-tr from-secondary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </button>

                    <button @click="processQuery('Info Pulau Sikuai')" class="group relative overflow-hidden bg-surface-container-lowest/80 backdrop-blur-md border border-outline-variant/30 p-3 sm:p-lg rounded-2xl sm:rounded-3xl hover:border-primary/40 transition-all flex flex-col items-center gap-2 sm:gap-sm shadow-premium hover:shadow-premium-hover hover:-translate-y-1">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-primary/10 flex items-center justify-center text-primary group-hover:scale-110 group-hover:bg-primary/20 transition-all">
                            <span class="material-symbols-outlined text-2xl sm:text-3xl">beach_access</span>
                        </div>
                        <span class="text-xs sm:text-sm font-bold text-primary">Info Pulau</span>
                        <div class="absolute inset-0 bg-gradient-to-tr from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </button>

                    <button @click="processQuery('Lapor Keluhan')" class="group relative overflow-hidden bg-surface-container-lowest/80 backdrop-blur-md border border-outline-variant/30 p-3 sm:p-lg rounded-2xl sm:rounded-3xl hover:border-error/40 transition-all flex flex-col items-center gap-2 sm:gap-sm shadow-premium hover:shadow-premium-hover hover:-translate-y-1">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-error/10 flex items-center justify-center text-error group-hover:scale-110 group-hover:bg-error/20 transition-all">
                            <span class="material-symbols-outlined text-2xl sm:text-3xl">report</span>
                        </div>
                        <span class="text-xs sm:text-sm font-bold text-primary">Lapor Insiden</span>
                        <div class="absolute inset-0 bg-gradient-to-tr from-error/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </button>
                </div>
                <!-- Mandeh Pulse: Live Interaction Stream -->
                <div class="mt-xl w-full text-left">
                    <div class="flex items-center justify-between mb-md">
                        <h3 class="text-label-lg font-bold text-primary uppercase tracking-widest flex items-center gap-sm">
                            <span class="material-symbols-outlined text-secondary material-symbols-fill animate-pulse">sensors</span>
                            Mandeh Pulse
                        </h3>
                        <span class="text-[10px] text-on-surface-variant font-bold uppercase">Live Updates</span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-sm">
                        <!-- Mock Pulse Item 1 -->
                        <div class="bg-surface-container-low/50 backdrop-blur-sm border border-outline-variant/10 p-md rounded-2xl flex gap-md items-center">
                            <div class="w-10 h-10 rounded-full bg-secondary/20 flex items-center justify-center text-secondary font-bold shrink-0">W</div>
                            <div>
                                <p class="text-body-sm text-primary font-medium line-clamp-1">Wisatawan baru saja memverifikasi kehadiran di <b>Puncak Mandeh</b></p>
                                <div class="flex items-center gap-xs mt-1">
                                    <span class="material-symbols-outlined text-[14px] text-secondary material-symbols-fill">verified</span>
                                    <span class="text-[10px] text-on-surface-variant uppercase">2 mins ago</span>
                                </div>
                            </div>
                        </div>
                        <!-- Mock Pulse Item 2 -->
                        <div class="bg-surface-container-low/50 backdrop-blur-sm border border-outline-variant/10 p-md rounded-2xl flex gap-md items-center">
                            <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold shrink-0">B</div>
                            <div>
                                <p class="text-body-sm text-primary font-medium line-clamp-1">Ulasan baru: "Ikan Bakarnya juara!" di <b>RM. Pesisir</b></p>
                                <div class="flex items-center gap-xs mt-1">
                                    <span class="material-symbols-outlined text-[14px] text-primary">star</span>
                                    <span class="text-[10px] text-on-surface-variant uppercase">15 mins ago</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- CHAT STATE: Evolution of Canvas -->
        <template x-if="canvasState === 'chat'">
            <div class="flex-1 w-full flex flex-col items-center pt-md pb-[120px] px-md overflow-y-auto scrollbar-hide animate-slide-up">
                <div class="w-full max-w-[800px] flex flex-col gap-sm">
                    <!-- System Greeting -->
                    <div class="flex flex-col gap-xs max-w-[85%] self-start">
                        <div class="bg-surface-container-lowest/80 backdrop-blur-md text-primary font-body-md rounded-xl rounded-bl-[4px] p-md border border-outline-variant/20 shadow-premium">
                            <p>Sistem aktif. Kami sedang menganalisis koordinat dan permintaan Anda...</p>
                        </div>
                    </div>

                    <!-- Dynamic Interactions Feed -->
                    <template x-for="(msg, index) in interactions" :key="index">
                        <div class="flex flex-col gap-xs w-full max-w-[85%] mb-md" :class="msg.role === 'user' ? 'self-end' : 'self-start'">
                            <!-- Standard Text Message -->
                            <template x-if="!msg.type || msg.type === 'text'">
                                <div class="p-md rounded-xl shadow-premium border border-outline-variant/10"
                                     :class="msg.role === 'user' ? 'bg-primary text-on-primary rounded-br-[4px]' : 'bg-surface-container-lowest text-primary rounded-bl-[4px]'">
                                    <p x-text="msg.content"></p>
                                </div>
                            </template>

                            <!-- GEN UI: Restaurant List -->
                            <template x-if="msg.type === 'gen-ui' && msg.component === 'restaurant-list'">
                                <div class="w-full bg-surface-container-lowest rounded-2xl p-md border border-outline-variant/20 shadow-premium flex flex-col gap-sm">
                                    <h4 class="text-label-lg font-bold text-secondary uppercase tracking-widest">Rekomendasi Seafood Terdekat</h4>
                                    <div class="grid grid-cols-1 gap-sm">
                                        <template x-for="item in msg.data">
                                            <div class="flex items-center justify-between p-sm hover:bg-surface-variant/10 rounded-xl transition-colors cursor-pointer group"
                                                 @click="showEntityDetail(item)">
                                                <div class="flex items-center gap-md">
                                                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary/20 transition-colors">
                                                        <span class="material-symbols-outlined">restaurant</span>
                                                    </div>
                                                    <div>
                                                        <p class="text-body-md font-bold text-primary group-hover:text-secondary transition-colors" x-text="item.name"></p>
                                                        <p class="text-label-sm text-on-surface-variant" x-text="item.dist"></p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-sm">
                                                    <div class="flex items-center gap-xs text-secondary font-bold">
                                                        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                                        <span x-text="item.rating"></span>
                                                    </div>
                                                    <span class="material-symbols-outlined text-[18px] text-on-surface-variant opacity-0 group-hover:opacity-100 transition-opacity">arrow_forward_ios</span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- GEN UI: Island Info -->
                            <template x-if="msg.type === 'gen-ui' && msg.component === 'island-info'">
                                <div class="w-full bg-primary/5 rounded-2xl p-lg border-2 border-primary/20 shadow-premium">
                                    <div class="flex items-center gap-md mb-md">
                                        <span class="material-symbols-outlined text-primary text-4xl">island</span>
                                        <h3 class="text-headline-sm font-extrabold text-primary" x-text="msg.title"></h3>
                                    </div>
                                    <div class="grid grid-cols-2 gap-md text-left">
                                        <div class="bg-surface/50 p-md rounded-xl">
                                            <p class="text-[10px] text-on-surface-variant font-bold uppercase">Status</p>
                                            <p class="text-body-md font-bold text-success" x-text="msg.status"></p>
                                        </div>
                                        <div class="bg-surface/50 p-md rounded-xl">
                                            <p class="text-[10px] text-on-surface-variant font-bold uppercase">Transport</p>
                                            <p class="text-body-md font-bold text-primary" x-text="msg.transport"></p>
                                        </div>
                                    </div>
                                    <button class="mt-md w-full py-md bg-primary text-on-primary rounded-xl font-bold flex items-center justify-center gap-sm">
                                        <span class="material-symbols-outlined">directions_boat</span>
                                        Pesan Kapal Sekarang
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Typing Indicator -->
                    <template x-if="isTyping">
                        <div class="self-start bg-surface-container-lowest/80 backdrop-blur-md p-md rounded-xl rounded-bl-[4px] border border-outline-variant/20 shadow-premium flex gap-xs items-center">
                            <div class="w-2 h-2 bg-primary/40 rounded-full animate-bounce"></div>
                            <div class="w-2 h-2 bg-primary/40 rounded-full animate-bounce [animation-delay:0.2s]"></div>
                            <div class="w-2 h-2 bg-primary/40 rounded-full animate-bounce [animation-delay:0.4s]"></div>
                        </div>
                    </template>

                    <!-- Mock System Pulse if Scanning -->
                    <template x-if="isScanning">
                        <div class="bg-surface-container-lowest/80 backdrop-blur-md text-primary rounded-2xl p-lg border-2 border-secondary/20 shadow-premium">
                            <div class="flex items-center gap-md mb-md">
                                <span class="material-symbols-outlined text-secondary text-3xl animate-spin-slow">qr_code_scanner</span>
                                <div>
                                    <h4 class="text-headline-sm font-bold">Mencari Koordinat Entity...</h4>
                                    <p class="text-label-md text-on-surface-variant">Kamera aktif, arahkan ke kode QR</p>
                                </div>
                            </div>
                            
                            <!-- Scanner Target -->
                            <div id="qr-reader" class="w-full rounded-xl overflow-hidden bg-black mb-md"></div>

                            <div class="h-2 bg-outline-variant/20 rounded-full overflow-hidden">
                                <div class="h-full bg-secondary w-1/3 animate-progress"></div>
                            </div>
                            <button @click="stopScanner(); canvasState = 'zero'" class="mt-md w-full py-2 text-error font-bold">Batalkan</button>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    
        <!-- Sticky Input Area (Only visible in chat state or always at bottom as fallback) -->
        <div class="fixed bottom-0 left-0 md:left-[280px] right-0 z-40 flex justify-center items-center pb-lg px-md pointer-events-none" 
             x-show="canvasState === 'chat'" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-10"
             x-transition:enter-end="opacity-100 translate-y-0">
            <nav class="bg-surface/80 backdrop-blur-xl border border-outline-variant/30 shadow-modal rounded-full h-[64px] flex items-center px-sm gap-xs w-full max-w-[800px] pointer-events-auto transition-all">
                <button @click="canvasState = 'zero'; isScanning = false; interactions = []" class="text-on-surface-variant p-xs hover:bg-surface-variant rounded-full transition-all flex items-center justify-center shrink-0 w-10 h-10 ml-2">
                    <span class="material-symbols-outlined text-[24px]">arrow_back</span>
                </button>
                <div class="flex-1 h-full py-sm px-xs">
                    <input class="w-full h-full bg-transparent border-none focus:ring-0 text-primary font-body-md placeholder:text-primary/40 outline-none" 
                           placeholder="Ada hal lain yang ingin ditanyakan?" 
                           type="text"
                           x-model="searchQuery"
                           @keyup.enter="processQuery(searchQuery)">
                </div>
                <button @click="processQuery(searchQuery)" class="bg-primary text-on-primary rounded-full p-xs hover:scale-105 active:scale-95 transition-all flex items-center justify-center h-11 w-11 shrink-0 ml-xs mr-2 shadow-premium">
                    <span class="material-symbols-outlined text-[22px] material-symbols-fill">send</span>
                </button>
            </nav>
        </div>

        <!-- ENTITY DETAIL OVERLAY MODAL -->
        <div x-show="entityDetail" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-md" 
             x-cloak>
            
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-primary/30 backdrop-blur-sm" @click="closeEntityDetail()"></div>

            <!-- Detail Card -->
            <div x-show="entityDetail"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-8 sm:scale-95"
                 class="relative bg-surface border border-outline-variant/20 rounded-t-3xl sm:rounded-3xl shadow-modal w-full max-w-[520px] max-h-[85vh] overflow-y-auto">
                
                <!-- Top Gradient Accent -->
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-primary via-secondary to-primary rounded-t-3xl"></div>

                <!-- Close Button -->
                <button @click="closeEntityDetail()" class="absolute top-4 right-4 z-10 p-1.5 rounded-full bg-surface-variant/50 hover:bg-surface-variant text-on-surface-variant transition-colors">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>

                <div class="p-lg pt-xl">
                    <!-- Entity Header -->
                    <div class="flex items-start gap-md mb-lg">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center text-primary shrink-0">
                            <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;"
                                  x-text="entityDetail?.type === 'business' ? 'storefront' : 'person'"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-headline-sm font-extrabold text-on-surface leading-tight" x-text="entityDetail?.name"></h3>
                            <div class="flex items-center gap-sm mt-1.5 flex-wrap">
                                <template x-if="entityDetail?.type === 'business'">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-secondary/10 text-secondary border border-secondary/20">Bisnis Lokal</span>
                                </template>
                                <template x-if="entityDetail?.type !== 'business'">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-primary/10 text-primary border border-primary/20">Profil Pribadi</span>
                                </template>
                                <template x-if="entityDetail?.dist">
                                    <span class="text-label-sm text-on-surface-variant flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">near_me</span>
                                        <span x-text="entityDetail?.dist"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Rating Display -->
                    <template x-if="entityDetail?.rating">
                        <div class="flex items-center gap-md mb-lg p-md bg-secondary/5 rounded-2xl border border-secondary/10">
                            <div class="text-center">
                                <div class="text-headline-lg font-extrabold text-secondary" x-text="entityDetail?.rating"></div>
                                <div class="text-[10px] text-on-surface-variant uppercase font-bold tracking-wider">Rating</div>
                            </div>
                            <div class="flex-1">
                                <div class="flex gap-0.5">
                                    <template x-for="s in 5">
                                        <span class="material-symbols-outlined text-[22px]" 
                                              :class="s <= Math.round(entityDetail?.rating || 0) ? 'text-secondary' : 'text-outline-variant'"
                                              :style="s <= Math.round(entityDetail?.rating || 0) ? 'font-variation-settings: \'FILL\' 1;' : ''">star</span>
                                    </template>
                                </div>
                                <template x-if="entityDetail?.tags">
                                    <div class="flex flex-wrap gap-1 mt-1.5">
                                        <template x-for="tag in entityDetail?.tags">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-primary/5 text-primary border border-primary/10" x-text="tag"></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Description -->
                    <template x-if="entityDetail?.description">
                        <div class="mb-lg">
                            <h4 class="text-label-lg font-bold text-on-surface mb-1.5">Tentang</h4>
                            <p class="text-body-md text-on-surface-variant leading-relaxed" x-text="entityDetail?.description"></p>
                        </div>
                    </template>

                    <!-- Contact & Location Info -->
                    <div class="space-y-2 mb-lg">
                        <template x-if="entityDetail?.address">
                            <div class="flex items-start gap-sm p-sm rounded-xl bg-surface-container-low/50">
                                <span class="material-symbols-outlined text-[20px] text-primary mt-0.5">location_on</span>
                                <span class="text-body-sm text-on-surface-variant" x-text="entityDetail?.address"></span>
                            </div>
                        </template>
                        <template x-if="entityDetail?.phone">
                            <div class="flex items-center gap-sm p-sm rounded-xl bg-surface-container-low/50">
                                <span class="material-symbols-outlined text-[20px] text-primary">call</span>
                                <span class="text-body-sm text-on-surface-variant" x-text="entityDetail?.phone"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Action Buttons: Check-in, Review, Rating -->
                    <div class="grid grid-cols-3 gap-sm mb-lg">
                        <!-- Check-in Button -->
                        <button class="flex flex-col items-center gap-1.5 p-md rounded-2xl bg-primary/5 hover:bg-primary/10 border border-primary/10 hover:border-primary/30 transition-all group"
                                @click="interactions.push({role: 'assistant', type: 'text', content: '📍 Check-in berhasil di ' + entityDetail.name + '! Terima kasih telah berkunjung.'}); closeEntityDetail();">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary/20 group-hover:scale-110 transition-all">
                                <span class="material-symbols-outlined text-[22px]">add_location_alt</span>
                            </div>
                            <span class="text-label-sm font-bold text-primary">Check-in</span>
                        </button>

                        <!-- Review Button -->
                        <button class="flex flex-col items-center gap-1.5 p-md rounded-2xl bg-secondary/5 hover:bg-secondary/10 border border-secondary/10 hover:border-secondary/30 transition-all group"
                                @click="showReviewForm = !showReviewForm">
                            <div class="w-10 h-10 rounded-full bg-secondary/10 flex items-center justify-center text-secondary group-hover:bg-secondary/20 group-hover:scale-110 transition-all">
                                <span class="material-symbols-outlined text-[22px]">rate_review</span>
                            </div>
                            <span class="text-label-sm font-bold text-secondary">Ulasan</span>
                        </button>

                        <!-- Rating Button -->
                        <button class="flex flex-col items-center gap-1.5 p-md rounded-2xl bg-warning/5 hover:bg-warning/10 border border-warning/10 hover:border-warning/30 transition-all group"
                                @click="showReviewForm = true">
                            <div class="w-10 h-10 rounded-full bg-warning/10 flex items-center justify-center text-warning group-hover:bg-warning/20 group-hover:scale-110 transition-all"
                                 style="color: #f59e0b;">
                                <span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' 1;">star</span>
                            </div>
                            <span class="text-label-sm font-bold" style="color: #f59e0b;">Rating</span>
                        </button>
                    </div>

                    <!-- Review Form (Expandable) -->
                    <div x-show="showReviewForm" x-transition class="mb-lg p-md bg-surface-container-low/50 rounded-2xl border border-outline-variant/10">
                        <h4 class="text-label-lg font-bold text-on-surface mb-md">Berikan Rating & Ulasan</h4>
                        
                        <!-- Star Rating Input -->
                        <div class="flex items-center gap-md mb-md">
                            <span class="text-label-md text-on-surface-variant">Rating:</span>
                            <div class="flex gap-1">
                                <template x-for="s in 5">
                                    <button @click="setRating(s)" class="transition-transform hover:scale-125 active:scale-95">
                                        <span class="material-symbols-outlined text-[28px]" 
                                              :class="s <= userRating ? 'text-secondary' : 'text-outline-variant hover:text-secondary/50'"
                                              :style="s <= userRating ? 'font-variation-settings: \'FILL\' 1;' : ''">star</span>
                                    </button>
                                </template>
                            </div>
                            <span class="text-label-sm text-on-surface-variant" x-show="userRating > 0" x-text="userRating + '/5'"></span>
                        </div>

                        <!-- Review Text -->
                        <textarea x-model="userReview" 
                                  rows="3" 
                                  class="w-full bg-surface border border-outline-variant/30 rounded-xl p-md text-body-md text-on-surface placeholder:text-on-surface-variant/50 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary resize-none"
                                  placeholder="Ceritakan pengalaman Anda..."></textarea>

                        <!-- Submit Review -->
                        <button @click="submitReview()" 
                                :disabled="userRating === 0"
                                class="mt-sm w-full py-3 bg-primary text-on-primary rounded-xl font-bold flex items-center justify-center gap-sm hover:opacity-90 transition-opacity disabled:opacity-30 disabled:cursor-not-allowed">
                            <span class="material-symbols-outlined text-[20px]">send</span>
                            Kirim Ulasan
                        </button>
                    </div>

                    <!-- Visit Profile Link -->
                    <template x-if="entityDetail?.slug">
                        <a :href="'/v/' + entityDetail.slug" class="block w-full py-3 text-center text-primary font-bold text-label-lg hover:bg-primary/5 rounded-xl transition-colors border border-primary/20">
                            Lihat Profil Lengkap →
                        </a>
                    </template>
                </div>
            </div>
        </div>

    </div>

    <style>
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        .animate-slide-up { animation: slideUp 0.5s ease-out forwards; }
        .animate-pulse-slow { animation: pulse 3s infinite; }
        .animate-spin-slow { animation: spin 4s linear infinite; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.98); } to { opacity: 1; transform: scale(1); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes progress { from { transform: translateX(-100%); } to { transform: translateX(300%); } }
        .animate-progress { animation: progress 2s infinite linear; }
    </style>
</x-maritime-layout>
