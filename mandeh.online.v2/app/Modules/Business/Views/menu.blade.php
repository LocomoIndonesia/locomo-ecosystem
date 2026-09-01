<x-maritime-layout>
    <x-slot name="title">Menu | {{ $business->name }}</x-slot>

    <div class="relative min-h-[calc(100vh-56px)] flex flex-col bg-background">
        <!-- Header -->
        <div class="p-md bg-surface-container-lowest border-b border-outline-variant/10 flex items-center gap-md">
            <button onclick="window.history.back()" class="text-primary">
                <span class="material-symbols-outlined">arrow_back</span>
            </button>
            <h1 class="text-headline-md text-primary font-bold">Digital Menu</h1>
        </div>

        <div class="p-md flex flex-col gap-lg pb-xl">
            <!-- Business Info -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-headline-sm text-primary font-bold">{{ $business->name }}</h2>
                    <p class="text-body-sm text-on-surface-variant">Silakan pilih menu pesanan Anda.</p>
                </div>
                <div class="flex items-center gap-1 bg-secondary/10 px-sm py-1 rounded-full text-secondary">
                    <span class="material-symbols-outlined text-sm material-symbols-fill">verified</span>
                    <span class="text-label-md font-bold">Premium</span>
                </div>
            </div>

            <!-- Categories & Items -->
            <div class="flex flex-col gap-xl">
                @php $groupedMenu = $business->menuItems->groupBy('category'); @endphp
                
                @foreach($groupedMenu as $category => $items)
                <div class="flex flex-col gap-md">
                    <h3 class="text-label-md text-primary font-bold uppercase tracking-widest">{{ $category ?: 'Lainnya' }}</h3>
                    <div class="flex flex-col gap-md">
                        @foreach($items as $item)
                        <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-2xl p-sm flex gap-md shadow-premium items-center">
                            <div class="w-20 h-20 rounded-full overflow-hidden shrink-0 border border-outline-variant/10">
                                <img src="{{ $item->image ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?q=80&w=200' }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start">
                                    <h4 class="font-bold text-primary truncate">{{ $item->name }}</h4>
                                    @if($item->is_best_seller)
                                    <span class="bg-secondary/10 text-secondary text-[10px] font-bold px-2 py-0.5 rounded uppercase shrink-0">Best</span>
                                    @endif
                                </div>
                                <p class="text-body-sm text-on-surface-variant line-clamp-1 mt-1">{{ $item->description }}</p>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="font-bold text-primary">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                    <button class="bg-primary text-on-primary p-1.5 rounded-full hover:scale-110 transition-transform">
                                        <span class="material-symbols-outlined text-[18px]">add</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Floating Cart (Visual Only) -->
        <div class="fixed bottom-6 right-6 z-50">
            <button class="w-14 h-14 bg-secondary text-on-secondary rounded-full shadow-modal flex items-center justify-center hover:scale-105 transition-transform">
                <span class="material-symbols-outlined">shopping_cart</span>
            </button>
        </div>
    </div>
</x-maritime-layout>
