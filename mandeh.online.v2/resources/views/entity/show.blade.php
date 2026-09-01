<x-maritime-layout>
    <x-slot name="title">{{ $entity->name }} | Mandeh.Online</x-slot>

    <div x-data="{ 
            verifiedStatus: 'checking', 
            distance: null,
            userLat: null,
            userLng: null,
            entityLat: {{ $entity->lat }},
            entityLng: {{ $entity->lng }},
            
            calculateDistance(lat1, lon1, lat2, lon2) {
                const R = 6371e3; // meters
                const φ1 = lat1 * Math.PI/180;
                const φ2 = lat2 * Math.PI/180;
                const Δφ = (lat2-lat1) * Math.PI/180;
                const Δλ = (lon2-lon1) * Math.PI/180;

                const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                        Math.cos(φ1) * Math.cos(φ2) *
                        Math.sin(Δλ/2) * Math.sin(Δλ/2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

                return R * c; // in meters
            },

            checkPresence() {
                if (!navigator.geolocation) {
                    this.verifiedStatus = 'unverified';
                    return;
                }

                navigator.geolocation.getCurrentPosition((position) => {
                    this.userLat = position.coords.latitude;
                    this.userLng = position.coords.longitude;
                    this.distance = this.calculateDistance(this.userLat, this.userLng, this.entityLat, this.entityLng);
                    
                    if (this.distance <= 100) {
                        this.verifiedStatus = 'verified';
                    } else {
                        this.verifiedStatus = 'passing';
                    }
                }, (error) => {
                    this.verifiedStatus = 'unverified';
                });
            }
         }" 
         x-init="checkPresence()"
         class="min-h-full bg-background pb-20">

        <!-- Hero Section -->
        <div class="relative h-[300px] md:h-[400px] overflow-hidden">
            <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 hover:scale-105" 
                 style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCpbyYKJ8_1HGV0u2IOW9-eqkf9cNJAZoUy_QeqPjLbwRsI9FvuPGTlmZLcGEGn3n5mQYlFTPk1HTvSwvhv99r2UBSzsP2IH1i8Jl8c6AeEcJEyF7flCpZkBa96Sy9upyfNEXTOTIKQptaivMygRl8dSzqtiHH8nzt3C14nKNN5jEF_C-oCeYhZ3i6XLav_DOXBRs9FiODHJ2qSoshCL80-VkjtCBugCxp7mkGrHH_VOF7IvYGITObKILJrmnHCJsLwMEVbko5kqQ');">
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-background via-transparent to-black/20"></div>
            
            <!-- Floating Verified Badge -->
            <div class="absolute top-md right-md">
                <template x-if="verifiedStatus === 'verified'">
                    <div class="bg-secondary text-on-secondary px-md py-sm rounded-full flex items-center gap-sm shadow-premium animate-bounce-slow">
                        <span class="material-symbols-outlined text-[20px] material-symbols-fill">verified</span>
                        <span class="font-label-md font-bold uppercase tracking-widest">Verified Presence</span>
                    </div>
                </template>
                <template x-if="verifiedStatus === 'passing' || verifiedStatus === 'unverified'">
                    <div class="bg-surface/80 backdrop-blur-md text-on-surface-variant px-md py-sm rounded-full flex items-center gap-sm shadow-premium">
                        <span class="material-symbols-outlined text-[20px]">location_off</span>
                        <span class="font-label-md font-bold uppercase tracking-widest" x-text="verifiedStatus === 'passing' ? 'Passing By' : 'Location Hidden'"></span>
                    </div>
                </template>
            </div>
        </div>

        <!-- Entity Info Canvas -->
        <div class="relative -mt-xl px-gutter max-w-[1200px] mx-auto">
            <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-[32px] p-lg md:p-xl shadow-modal overflow-hidden relative">
                
                <!-- Partner Badge Decoration -->
                @if($isPartner)
                <div class="absolute top-0 right-0 p-4">
                    <span class="bg-primary/5 text-primary border border-primary/20 px-4 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase">Premium Partner</span>
                </div>
                @endif

                <div class="flex flex-col md:flex-row justify-between items-start gap-lg">
                    <div class="flex-1">
                        <div class="flex items-center gap-sm text-secondary font-label-md uppercase tracking-widest mb-sm">
                            <span class="material-symbols-outlined text-[18px]">
                                @if($entity->type === 'restoran') restaurant @elseif($entity->type === 'hotel') hotel @else deck @endif
                            </span>
                            {{ str_replace('_', ' ', $entity->type) }}
                        </div>
                        <h1 class="text-headline-large font-extrabold text-primary tracking-tight leading-tight">{{ $entity->name }}</h1>
                        <p class="text-body-lg text-on-surface-variant mt-md max-w-[600px]">{{ $entity->description }}</p>
                    </div>

                    <div class="flex flex-col gap-sm min-w-[200px]">
                        <a href="{{ $entity->google_maps_url }}" target="_blank" class="flex items-center justify-center gap-md bg-primary text-on-primary py-md px-lg rounded-2xl font-bold shadow-premium hover:scale-[1.02] transition-all">
                            <span class="material-symbols-outlined">directions</span>
                            Navigasi
                        </a>
                        <button class="flex items-center justify-center gap-md bg-surface border border-outline text-primary py-md px-lg rounded-2xl font-bold hover:bg-primary/5 transition-all">
                            <span class="material-symbols-outlined">share</span>
                            Bagikan
                        </button>
                    </div>
                </div>

                <!-- Metrics Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-md mt-xl border-t border-outline-variant/10 pt-xl">
                    <div class="flex flex-col gap-xs">
                        <span class="text-label-md text-on-surface-variant uppercase tracking-widest">Reputation</span>
                        <div class="flex items-center gap-xs">
                            <span class="text-headline-md font-bold text-primary">4.8</span>
                            <div class="flex text-secondary">
                                <span class="material-symbols-outlined text-[18px] material-symbols-fill">star</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-xs">
                        <span class="text-label-md text-on-surface-variant uppercase tracking-widest">Price Range</span>
                        <span class="text-headline-md font-bold text-primary">{{ $entity->price_range ?? 'Rp -' }}</span>
                    </div>
                    <div class="flex flex-col gap-xs">
                        <span class="text-label-md text-on-surface-variant uppercase tracking-widest">Transparency</span>
                        <span class="text-headline-md font-bold text-secondary">A+</span>
                    </div>
                    <div class="flex flex-col gap-xs">
                        <span class="text-label-md text-on-surface-variant uppercase tracking-widest">Distance</span>
                        <span class="text-headline-md font-bold text-primary" x-text="distance ? (distance < 1000 ? Math.round(distance) + 'm' : (distance/1000).toFixed(1) + 'km') : '--'"></span>
                    </div>
                </div>
            </div>

            <!-- Contextual Social Hub -->
            <div class="mt-xl grid grid-cols-1 lg:grid-cols-3 gap-xl">
                <!-- Main Feed (Pulse) -->
                <div class="lg:col-span-2 flex flex-col gap-md">
                    <h3 class="text-title-large font-bold text-primary flex items-center gap-sm">
                        <span class="material-symbols-outlined text-secondary material-symbols-fill">sensors</span>
                        Entity Pulse
                    </h3>
                    
                    @auth
                        <button @click="canvasState = 'zero'" class="w-full p-md bg-surface border border-outline-variant/20 rounded-2xl text-left text-on-surface-variant hover:border-primary/40 transition-all flex items-center justify-between">
                            <span>Bagikan pengalaman Anda di sini...</span>
                            <span class="material-symbols-outlined">add_circle</span>
                        </button>
                    @else
                        <div class="p-lg bg-primary/5 border border-primary/10 rounded-2xl">
                            <p class="text-body-md text-primary font-medium">Masuk untuk memberikan ulasan terverifikasi atau melaporkan keluhan.</p>
                            <button @click="window.location.href='/login'" class="mt-md text-label-lg font-bold text-primary underline">Login WhatsApp/Email</button>
                        </div>
                    @endauth

                    <!-- Interaction Cards -->
                    <div class="flex flex-col gap-md">
                        @forelse($entity->interactions as $interaction)
                            <div class="bg-surface-container-low border border-outline-variant/10 rounded-2xl p-md shadow-sm">
                                <div class="flex items-center gap-sm mb-md">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                                        {{ strtoupper(substr($interaction->user->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-xs">
                                            <span class="font-bold text-primary">{{ $interaction->user->name }}</span>
                                            @if($interaction->status === 'public')
                                                <span class="material-symbols-outlined text-secondary text-[16px] material-symbols-fill" title="Verified Presence">verified</span>
                                            @endif
                                        </div>
                                        <span class="text-[10px] text-on-surface-variant uppercase tracking-widest">{{ $interaction->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="flex text-secondary">
                                        @for($i=0; $i<$interaction->rating_score; $i++)
                                            <span class="material-symbols-outlined text-[14px] material-symbols-fill">star</span>
                                        @endfor
                                    </div>
                                </div>
                                <p class="text-body-md text-on-surface">{{ $interaction->content }}</p>
                                <div class="mt-md flex items-center gap-md">
                                    <button class="flex items-center gap-xs text-label-sm text-on-surface-variant hover:text-primary transition-colors">
                                        <span class="material-symbols-outlined text-[18px]">thumb_up</span>
                                        Bermanfaat
                                    </button>
                                    <button class="flex items-center gap-xs text-label-sm text-on-surface-variant hover:text-primary transition-colors">
                                        <span class="material-symbols-outlined text-[18px]">chat_bubble</span>
                                        Komentar
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="py-xl text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-4xl opacity-20">forum</span>
                                <p class="mt-sm">Belum ada aktivitas di sini. Jadilah yang pertama!</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Sidebar Info -->
                <div class="flex flex-col gap-lg">
                    @if($isPartner && $entity->businessMeta)
                        <div class="bg-surface-container-high border border-outline-variant/20 rounded-3xl p-lg shadow-premium">
                            <h4 class="text-label-lg font-bold text-primary uppercase tracking-widest mb-md">Special Offer</h4>
                            <div class="p-md bg-secondary/10 border border-secondary/20 rounded-2xl text-secondary">
                                <p class="font-bold">Diskon 10%</p>
                                <p class="text-sm">Tunjukkan bukti ulasan "Verified Presence" Anda ke kasir.</p>
                            </div>
                        </div>
                    @endif

                    <div class="bg-surface border border-outline-variant/10 rounded-3xl p-lg">
                        <h4 class="text-label-lg font-bold text-primary uppercase tracking-widest mb-md">Safety Status</h4>
                        <div class="flex items-center gap-md text-success">
                            <span class="material-symbols-outlined text-3xl">verified_user</span>
                            <div>
                                <p class="font-bold">Zero Incidents</p>
                                <p class="text-xs text-on-surface-variant">Terpantau aman oleh komunitas.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .animate-bounce-slow { animation: bounce 3s infinite; }
        @keyframes bounce { 0%, 100% { transform: translateY(-5%); } 50% { transform: translateY(0); } }
    </style>
</x-maritime-layout>
