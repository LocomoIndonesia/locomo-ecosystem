<x-guest-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $business->name }}</h1>
                <p class="text-lg text-gray-600 mb-8">{{ $business->description ?? 'Welcome to ' . $business->name }}</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-left">
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <h2 class="font-semibold text-blue-800 mb-2">Category</h2>
                        <p class="text-blue-900">{{ ucfirst($business->category) }}</p>
                    </div>
                    <div class="bg-green-50 p-6 rounded-lg">
                        <h2 class="font-semibold text-green-800 mb-2">Status</h2>
                        <p class="text-green-900">{{ ucfirst($business->verification_status) }}</p>
                    </div>
                </div>

                <div class="mt-12 p-8 border-t border-gray-100">
                    <p class="text-sm text-gray-400">Mandeh.Online QR System - Code: {{ $qrCode->code }}</p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
