<x-maritime-layout>
    <!-- Main Content Canvas -->
    <main class="flex-grow w-full max-w-[1280px] mx-auto px-gutter py-lg pb-[120px] md:pb-lg flex flex-col gap-lg">
        <!-- Page Header & Filters -->
        <section class="flex flex-col gap-sm">
            <div class="flex items-center gap-xs text-primary-container">
                <span class="material-symbols-outlined text-headline-md">water_lux</span>
                <h1 class="text-headline-lg text-primary">Coastal Services</h1>
            </div>
            <p class="text-body-lg text-on-surface-variant max-w-2xl">Verified services evaluated for safety, quality, and proximity to maritime activities.</p>
            
            <!-- Filters Bento Row -->
            <div class="flex flex-wrap gap-xs mt-4">
                <button class="px-4 py-2 bg-primary-container text-on-primary rounded-full text-label-md font-semibold flex items-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">tune</span>
                    All Filters
                </button>
                <button class="px-4 py-2 bg-surface-container text-primary-container border border-outline-variant rounded-full text-label-md font-semibold hover:bg-surface-container-high transition-colors">Top Rated</button>
                <button class="px-4 py-2 bg-surface-container text-primary-container border border-outline-variant rounded-full text-label-md font-semibold hover:bg-surface-container-high transition-colors">Near Port</button>
                <button class="px-4 py-2 bg-surface-container text-primary-container border border-outline-variant rounded-full text-label-md font-semibold hover:bg-surface-container-high transition-colors">Family Friendly</button>
            </div>
        </section>

        <!-- Bento Grid Feed -->
        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-md">
            @forelse($businesses as $business)
                <article class="card-premium group relative overflow-hidden">
                    @if($business->average_rating >= 4.5)
                        <div class="absolute top-4 right-4 z-10 bg-secondary/10 text-primary-container px-3 py-1 rounded-full text-label-md font-bold backdrop-blur-sm border border-secondary/20 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px] text-secondary" style="font-variation-settings: 'FILL' 1;">verified</span>
                            Top Rated
                        </div>
                    @endif
                    <div class="w-full h-56 rounded-lg bg-surface-container overflow-hidden">
                        <img src="{{ $business->images[0] ?? 'https://via.placeholder.com/400x300?text=Mandeh+Service' }}" alt="{{ $business->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    </div>
                    <div class="flex flex-col gap-1 mt-4">
                        <div class="flex justify-between items-start">
                            <h3 class="text-headline-md font-headline-md text-primary leading-tight">{{ $business->name }}</h3>
                            <div class="flex items-center gap-1 text-label-md font-bold text-primary bg-surface-container-high px-2 py-1 rounded-md">
                                <span class="material-symbols-outlined text-[16px] text-secondary" style="font-variation-settings: 'FILL' 1;">star</span>
                                {{ number_format($business->average_rating, 1) }}
                            </div>
                        </div>
                        <p class="text-body-md font-body-md text-on-surface-variant flex items-center gap-1">
                            <span class="material-symbols-outlined text-[18px]">location_on</span> {{ $business->address }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3 mt-auto pt-4 border-t border-surface-container-high text-body-md text-outline">
                        @php $amenities = $business->metadata['amenities'] ?? []; @endphp
                        @foreach(array_slice($amenities, 0, 3) as $amenity)
                            <span class="flex items-center gap-1 bg-surface-container/50 p-1.5 rounded-full" title="{{ $amenity }}">
                                <span class="material-symbols-outlined text-[20px]">{{ $amenity }}</span>
                            </span>
                        @endforeach
                        <span class="ml-auto text-label-md font-bold text-primary">IDR {{ number_format($business->metadata['price'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <a href="{{ route('business.show', $business->id) }}" class="absolute inset-0"></a>
                </article>
            @empty
                <div class="col-span-full py-12 text-center text-on-surface-variant">
                    <p>No services found yet. Check back later!</p>
                </div>
            @endforelse
        </section>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $businesses->links() }}
        </div>
    </main>
</x-maritime-layout>
