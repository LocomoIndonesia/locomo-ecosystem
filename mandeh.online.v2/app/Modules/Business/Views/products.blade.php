<x-maritime-layout>
    <x-slot name="title">Katalog Produk | {{ $business->name }}</x-slot>

    <div class="relative min-h-[calc(100vh-56px)] flex flex-col bg-background">
        <!-- Header -->
        <div class="p-md bg-surface-container-lowest border-b border-outline-variant/10 flex items-center gap-md">
            <button onclick="window.history.back()" class="text-primary">
                <span class="material-symbols-outlined">arrow_back</span>
            </button>
            <h1 class="text-headline-md text-primary font-bold">Katalog Produk</h1>
        </div>

        <div class="p-md flex flex-col gap-lg pb-xl">
            <!-- Business Info -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-headline-sm text-primary font-bold">{{ $business->name }}</h2>
                    <p class="text-body-sm text-on-surface-variant">Produk lokal berkualitas dari Mandeh.</p>
                </div>
                <div class="flex items-center gap-1 bg-primary/5 px-sm py-1 rounded-full text-primary border border-primary/10">
                    <span class="material-symbols-outlined text-sm material-symbols-fill">verified</span>
                    <span class="text-label-md font-bold">Mitra UMKM</span>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-2 gap-md">
                @foreach($business->products as $product)
                <div class="flex flex-col gap-sm group">
                    <div class="aspect-square rounded-2xl overflow-hidden shadow-premium border border-outline-variant/10 bg-surface-container relative">
                        <img src="{{ $product->image ?? 'https://images.unsplash.com/photo-1590736962031-2947702f231e?q=80&w=200' }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @if(!$product->is_available)
                        <div class="absolute inset-0 bg-primary/40 backdrop-blur-sm flex items-center justify-center">
                            <span class="text-on-primary font-bold uppercase tracking-widest text-xs">Habis</span>
                        </div>
                        @endif
                    </div>
                    <div class="flex flex-col gap-1 px-1">
                        <h3 class="text-body-md font-bold text-primary line-clamp-1">{{ $product->name }}</h3>
                        <p class="text-label-md text-secondary font-bold">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                    </div>
                    <button class="w-full py-2 bg-primary/5 text-primary text-label-md font-bold rounded-xl border border-primary/10 hover:bg-primary hover:text-on-primary transition-all">
                        Tambah
                    </button>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Floating Cart (Visual Only) -->
        <div class="fixed bottom-6 right-6 z-50">
            <button class="w-14 h-14 bg-primary text-on-primary rounded-full shadow-modal flex items-center justify-center hover:scale-105 transition-transform">
                <span class="material-symbols-outlined">shopping_bag</span>
            </button>
        </div>
    </div>
</x-maritime-layout>
