<x-maritime-layout>
    <x-slot name="title">{{ $business->name }} | Mandeh.Online</x-slot>

    <div class="relative min-h-[calc(100vh-56px)] flex flex-col" x-data="{ showQr: false }">
        <!-- Hero Section with Background Image -->
        <div class="relative h-[300px] w-full overflow-hidden">
            <img src="{{ $business->image ?? 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?q=80&w=1200&auto=format&fit=crop' }}" 
                 alt="{{ $business->name }}" 
                 class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-background via-background/20 to-transparent"></div>
            
            <!-- Back Button -->
            <button onclick="window.history.back()" class="absolute top-4 left-4 w-10 h-10 rounded-full bg-surface/80 backdrop-blur-md flex items-center justify-center text-primary shadow-premium">
                <span class="material-symbols-outlined">arrow_back</span>
            </button>
        </div>

        <!-- Content Card (Floating) -->
        <div class="relative -mt-20 px-4 pb-xl flex-1">
            <div class="bg-surface-container-lowest rounded-[32px] shadow-modal border border-outline-variant/10 p-md flex flex-col gap-lg">
                <!-- Header Info -->
                <div class="flex flex-col gap-sm">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center gap-xs">
                            <span class="material-symbols-outlined text-secondary text-sm material-symbols-fill">verified</span>
                            <span class="text-label-md text-secondary uppercase tracking-wider font-bold">Verified Business</span>
                        </div>
                        <!-- Mini QR Code -->
                        <div @click="showQr = true" class="w-12 h-12 bg-white p-1 rounded-lg border border-outline-variant/20 cursor-pointer hover:scale-105 transition-transform shadow-premium">
                            {!! QrCode::size(40)->generate(route('business.profile', $business->slug)) !!}
                        </div>
                    </div>
                    <div class="flex justify-between items-start">
                        <h1 class="text-display-sm text-primary font-bold leading-tight">{{ $business->name }}</h1>
                        <div class="flex items-center gap-1 bg-secondary/10 px-sm py-1 rounded-full border border-secondary/20">
                            <span class="material-symbols-outlined text-secondary text-sm material-symbols-fill">star</span>
                            <span class="font-bold text-primary">{{ number_format($business->average_rating, 1) }}</span>
                        </div>
                    </div>
                    <p class="text-body-md text-on-surface-variant">{{ $business->description }}</p>
                </div>

                <!-- Premium Features (Sponsor Only) -->
                @if($business->tier === 'sponsor')
                    <div class="flex flex-col gap-sm bg-primary/5 p-md rounded-2xl border border-primary/10">
                        <h3 class="text-label-md text-primary font-bold uppercase">Layanan Eksklusif</h3>
                        <div class="flex gap-md">
                            @if($business->category === 'Resto')
                                <a href="{{ route('business.menu', $business->slug) }}" class="flex-1 bg-surface-container-lowest p-sm rounded-xl border border-outline-variant/20 flex items-center gap-sm group">
                                    <div class="w-10 h-10 rounded-full bg-secondary/10 text-secondary flex items-center justify-center group-hover:bg-secondary group-hover:text-on-secondary transition-all">
                                        <span class="material-symbols-outlined">menu_book</span>
                                    </div>
                                    <span class="text-body-md font-bold text-primary">Digital Menu</span>
                                </a>
                            @endif
                            @if($business->category === 'Toko')
                                <a href="{{ route('business.products', $business->slug) }}" class="flex-1 bg-surface-container-lowest p-sm rounded-xl border border-outline-variant/20 flex items-center gap-sm group">
                                    <div class="w-10 h-10 rounded-full bg-primary/5 text-primary flex items-center justify-center group-hover:bg-primary group-hover:text-on-primary transition-all">
                                        <span class="material-symbols-outlined">shopping_bag</span>
                                    </div>
                                    <span class="text-body-md font-bold text-primary">Katalog Produk</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Quick Actions -->
                <div class="flex gap-md overflow-x-auto scrollbar-hide pb-2">
                    <a href="https://maps.google.com/?q={{ $business->geolocation['lat'] ?? 0 }},{{ $business->geolocation['lng'] ?? 0 }}" target="_blank" class="flex flex-col items-center gap-2 min-w-[80px]">
                        <div class="w-12 h-12 rounded-2xl bg-primary/5 flex items-center justify-center text-primary border border-primary/10">
                            <span class="material-symbols-outlined">directions</span>
                        </div>
                        <span class="text-label-md text-primary">Rute</span>
                    </a>
                    <a href="tel:{{ $business->contact['phone'] ?? '' }}" class="flex flex-col items-center gap-2 min-w-[80px]">
                        <div class="w-12 h-12 rounded-2xl bg-primary/5 flex items-center justify-center text-primary border border-primary/10">
                            <span class="material-symbols-outlined">call</span>
                        </div>
                        <span class="text-label-md text-primary">Telepon</span>
                    </a>
                    <button class="flex flex-col items-center gap-2 min-w-[80px]">
                        <div class="w-12 h-12 rounded-2xl bg-primary/5 flex items-center justify-center text-primary border border-primary/10">
                            <span class="material-symbols-outlined">share</span>
                        </div>
                        <span class="text-label-md text-primary">Bagikan</span>
                    </button>
                    <button class="flex flex-col items-center gap-2 min-w-[80px]">
                        <div class="w-12 h-12 rounded-2xl bg-primary/5 flex items-center justify-center text-primary border border-primary/10">
                            <span class="material-symbols-outlined">favorite</span>
                        </div>
                        <span class="text-label-md text-primary">Simpan</span>
                    </button>
                </div>

                <!-- Tabs (Simple Visual) -->
                <div class="flex border-b border-outline-variant/10">
                    <button class="px-md py-sm text-secondary border-b-2 border-secondary font-bold text-label-md">Info</button>
                    <button class="px-md py-sm text-on-surface-variant text-label-md">Galeri</button>
                    <button class="px-md py-sm text-on-surface-variant text-label-md">Ulasan</button>
                </div>

                <!-- Details Section -->
                <div class="flex flex-col gap-md">
                    <div class="flex items-start gap-md">
                        <span class="material-symbols-outlined text-on-surface-variant">location_on</span>
                        <div class="flex flex-col">
                            <span class="font-bold text-primary">Lokasi</span>
                            <span class="text-body-md text-on-surface-variant">{{ $business->geolocation['address'] ?? 'Pesisir Selatan, Sumatera Barat' }}</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-md">
                        <span class="material-symbols-outlined text-on-surface-variant">schedule</span>
                        <div class="flex flex-col">
                            <span class="font-bold text-primary">Jam Operasional</span>
                            <span class="text-body-md text-on-surface-variant">Buka · Tutup pukul 21:00</span>
                        </div>
                    </div>
                </div>

                <!-- Reviews Preview -->
                <div class="flex flex-col gap-md">
                    <div class="flex justify-between items-center">
                        <h3 class="text-headline-sm text-primary font-bold">Ulasan Terbaru</h3>
                        <button class="text-label-md text-secondary font-bold">Lihat Semua</button>
                    </div>
                    <div class="flex flex-col gap-sm">
                        @forelse($business->reviews->take(2) as $review)
                        <div class="p-sm bg-surface rounded-2xl border border-outline-variant/10 flex flex-col gap-xs">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-primary text-body-sm">{{ $review->user->name ?? 'Anonim' }}</span>
                                <div class="flex text-secondary scale-75 origin-right">
                                    @for($i=0; $i<$review->rating; $i++)
                                    <span class="material-symbols-outlined material-symbols-fill">star</span>
                                    @endfor
                                </div>
                            </div>
                            <p class="text-body-sm text-on-surface-variant">{{ $review->comment }}</p>
                        </div>
                        @empty
                        <p class="text-body-md text-outline text-center py-4 italic">Belum ada ulasan.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bottom Action Bar (Floating) -->
        <div class="fixed bottom-6 left-1/2 -translate-x-1/2 w-full max-w-[340px] z-50">
            <button class="w-full bg-primary text-on-primary font-body-lg font-bold py-4 rounded-full shadow-modal flex items-center justify-center gap-sm hover:scale-[1.02] transition-all">
                <span class="material-symbols-outlined">qr_code_scanner</span>
                Scan & Beri Rating
            </button>
        </div>

        <!-- QR Code Modal (Alpine.js) -->
        <div x-show="showQr" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90"
             class="fixed inset-0 z-[100] flex items-center justify-center p-md"
             style="display: none;">
            
            <div class="absolute inset-0 bg-primary/60 backdrop-blur-md" @click="showQr = false"></div>
            
            <div class="relative bg-surface-container-lowest rounded-[32px] p-xl flex flex-col items-center gap-lg shadow-modal w-full max-w-sm">
                <h2 class="text-headline-md text-primary font-bold text-center">QR Infrastruktur</h2>
                
                <div class="bg-white p-md rounded-[24px] shadow-premium border border-outline-variant/10">
                    {!! QrCode::size(240)->generate(route('business.profile', $business->slug)) !!}
                </div>

                <div class="flex flex-col items-center gap-xs">
                    <p class="font-bold text-primary">{{ $business->name }}</p>
                    <p class="text-body-sm text-on-surface-variant">Scan untuk akses kognitif & rating</p>
                </div>

                <div class="grid grid-cols-2 gap-md w-full">
                    <button onclick="window.print()" class="flex-1 py-3 bg-secondary/10 text-secondary rounded-xl font-bold flex items-center justify-center gap-xs">
                        <span class="material-symbols-outlined">print</span>
                        Cetak
                    </button>
                    <button class="flex-1 py-3 bg-primary text-on-primary rounded-xl font-bold flex items-center justify-center gap-xs">
                        <span class="material-symbols-outlined">share</span>
                        Share
                    </button>
                </div>

                <button @click="showQr = false" class="absolute top-4 right-4 text-on-surface-variant">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
        </div>
    </div>
</x-maritime-layout>
