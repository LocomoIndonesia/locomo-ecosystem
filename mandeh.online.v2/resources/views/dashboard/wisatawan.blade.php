<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Wisatawan Dashboard - Mandeh Explorer') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="mapComponent()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Peta Destinasi & Bisnis Lokal</h3>
                    <p class="text-sm text-gray-600">Temukan tempat terbaik di sekitar kawasan Mandeh.</p>
                </div>

                <!-- Leaflet Map Container -->
                <div id="map" class="h-96 w-full rounded-lg border border-gray-200"></div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <template x-for="business in businesses" :key="business.id">
                        <div class="p-4 border rounded-lg hover:shadow-md transition cursor-pointer" @click="focusOn(business)">
                            <h4 class="font-bold text-blue-600" x-text="business.name"></h4>
                            <p class="text-xs text-gray-500 uppercase tracking-widest" x-text="business.type"></p>
                            <p class="text-sm mt-2 text-gray-700" x-text="business.description"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        function mapComponent() {
            return {
                map: null,
                businesses: [],
                init() {
                    // Initialize Leaflet map centered on Mandeh
                    this.map = L.map('map').setView([-1.2224, 100.4144], 13);
                    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.map);

                    this.fetchBusinesses();
                },
                fetchBusinesses() {
                    // In real app, fetch from /api/businesses
                    // For now, mockup data
                    this.businesses = [
                        { id: 1, name: 'Pulau Kapo-Kapo', type: 'destinasi', lat: -1.2224, lng: 100.4144, description: 'Surga tersembunyi di Mandeh.' },
                        { id: 2, name: 'Rumah Makan Mandeh', type: 'restoran', lat: -1.2150, lng: 100.4200, description: 'Masakan Padang otentik.' },
                        { id: 3, name: 'Mandeh Resort', type: 'hotel', lat: -1.2300, lng: 100.4000, description: 'Penginapan mewah tepi pantai.' }
                    ];

                    this.businesses.forEach(b => {
                        L.marker([b.lat, b.lng])
                            .addTo(this.map)
                            .bindPopup(`<b>${b.name}</b><br>${b.type}`);
                    });
                },
                focusOn(business) {
                    this.map.setView([business.lat, business.lng], 15);
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
