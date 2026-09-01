<x-maritime-layout>
    <x-slot name="title">UI Prototypes | Mandeh.Online</x-slot>

    <section class="py-xl px-md max-w-container-max mx-auto">
        <div class="mb-lg">
            <h1 class="text-display-xl text-primary mb-xs">UI Gallery</h1>
            <p class="text-body-lg text-on-surface-variant">Explore the Maritime Modernism design system across all platform modules.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-md">
            @php
                $prototypes = [
                    ['id' => 'landing', 'name' => 'Search Landing', 'icon' => 'explore', 'desc' => 'Primary interaction hub with cognitive search.'],
                    ['id' => 'login', 'name' => 'Login Portal', 'icon' => 'login', 'desc' => 'Secure entry point for all stakeholders.'],
                    ['id' => 'register-user', 'name' => 'User Registration', 'icon' => 'person_add', 'desc' => 'Regular visitor onboarding flow.'],
                    ['id' => 'register-business', 'name' => 'Business Onboarding', 'icon' => 'storefront', 'desc' => 'Local business owner registration.'],
                    ['id' => 'register-partner', 'name' => 'Official Partner', 'icon' => 'verified_user', 'desc' => 'Government & Official partner verification.'],
                    ['id' => 'profile-restaurant', 'name' => 'Restaurant Profile', 'icon' => 'restaurant', 'desc' => 'Detailed view for culinary services.'],
                    ['id' => 'profile-wisma', 'name' => 'Homestay Profile', 'icon' => 'home_work', 'desc' => 'Accommodation and lodging details.'],
                    ['id' => 'scanner', 'name' => 'QR Infrastructure', 'icon' => 'qr_code_scanner', 'desc' => 'Physical-to-Digital bridge system.'],
                    ['id' => 'wifi', 'name' => 'Captive Portal', 'icon' => 'wifi', 'desc' => 'Seamless connectivity for tourists.'],
                    ['id' => 'destination', 'name' => 'Smart Routing', 'icon' => 'near_me', 'desc' => 'Interactive navigation to destinations.'],
                    ['id' => 'report', 'name' => 'Incident Reporting', 'icon' => 'report', 'desc' => 'Public safety and quality feedback.'],
                    ['id' => 'menu-qrcode', 'name' => 'Digital Menu', 'icon' => 'menu_book', 'desc' => 'Contactless dining menu experience.'],
                    ['id' => 'product-qrcode', 'name' => 'Local Products', 'icon' => 'shopping_bag', 'desc' => 'E-commerce for local craft & goods.'],
                ];
            @endphp

            @foreach($prototypes as $item)
            <a href="{{ route('prototypes.' . $item['id']) }}" class="card-premium group">
                <div class="flex items-center justify-between mb-sm">
                    <div class="w-12 h-12 rounded-xl bg-primary/5 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-on-primary transition-all">
                        <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
                    </div>
                    <span class="material-symbols-outlined text-outline-variant group-hover:text-primary transition-all">arrow_forward</span>
                </div>
                <h3 class="text-headline-md text-primary">{{ $item['name'] }}</h3>
                <p class="text-body-md text-on-surface-variant">{{ $item['desc'] }}</p>
            </a>
            @endforeach
        </div>
    </section>
</x-maritime-layout>
