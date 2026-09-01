<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Verifikasi Pembayaran Bisnis') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Daftar Pendaftaran Bisnis Menunggu Verifikasi</h3>
                
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bisnis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemilik / Kontak</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paket Layanan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bukti Pembayaran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($businesses as $biz)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $biz->name }}</div>
                                <div class="text-xs text-gray-500 font-mono">{{ $biz->type }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $biz->owner->name }}</div>
                                <div class="text-xs text-gray-500">{{ $biz->owner->phone_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800 uppercase">{{ $biz->subscription_package }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($biz->payment_proof_path)
                                    <a href="{{ asset('storage/' . $biz->payment_proof_path) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs flex items-center gap-1">
                                        Lihat Bukti Bayar &rarr;
                                    </a>
                                @else
                                    <span class="text-xs text-red-500">Tidak ada bukti</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <form action="{{ route('admin.business.verification.verify', $biz->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="decision" value="approve">
                                    <button type="submit" class="text-green-600 hover:text-green-950 font-bold mr-4">Setujui</button>
                                </form>
                                <form action="{{ route('admin.business.verification.verify', $biz->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="decision" value="reject">
                                    <button type="submit" class="text-red-600 hover:text-red-950 font-bold">Tolak</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                Tidak ada pendaftaran bisnis yang menunggu verifikasi pembayaran.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $businesses->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
