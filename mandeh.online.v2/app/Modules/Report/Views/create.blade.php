<x-maritime-layout>
    <div class="relative w-full min-h-[calc(100vh-80px)] flex items-center justify-center p-md overflow-hidden">
        <!-- Background Context -->
        <div class="absolute inset-0 z-0 bg-cover bg-center" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCWX7tvD2t3E_tDs3P_9Ud8-Tib16IJ7Xc9I-Qnv22DtNPrRRZpmO56iJbMA-4dmCkEKGc4CMwqXSfN7eC_WFXBuiPVCAarwmZ8qAQW0c5sDJ4RIcCmcAtpONoEViJHhOHsW-WkGKpBdUk80drOXKGCnOtOTMoA2JSxTUEJ6UPn6-Ci6s6gVVpkTI9WyB-jNXS0BiMZfTZ3xbq70bCVwyt-GbIkzSaxhemWhde7CI7P8BNtVpL0Q4vZ9mqFlnEAzodDNjpFd-9MtA');">
            <div class="absolute inset-0 bg-primary/40 backdrop-blur-[4px]"></div>
        </div>

        <!-- Report Form Card -->
        <div class="relative z-10 w-full max-w-lg bg-surface-container-lowest rounded-xl shadow-modal flex flex-col overflow-hidden animate-in fade-in zoom-in duration-300">
            <!-- Header -->
            <div class="px-md pt-lg pb-md border-b border-surface-container-high">
                <div class="flex items-center gap-sm mb-xs">
                    <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">flag</span>
                    <h2 class="text-headline-md font-bold text-primary tracking-tight">Laporan Insiden</h2>
                </div>
                <p class="text-body-md text-on-surface-variant font-medium">Bantu kami menjaga keamanan dan kelestarian kawasan maritim dengan melaporkan pengamatan Anda.</p>
            </div>

            <!-- Form Body -->
            <form action="{{ route('report.store') }}" method="POST" class="p-md flex flex-col gap-md">
                @csrf
                
                @if(session('success'))
                    <div class="bg-secondary-container text-on-secondary-container p-4 rounded-lg text-sm mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Field 1: Location Dropdown -->
                <div class="flex flex-col gap-base">
                    <label class="text-label-md font-bold text-primary" for="location">Lokasi Kejadian</label>
                    <div class="relative">
                        <select name="location" class="w-full appearance-none bg-surface-bright border border-outline-variant text-body-md text-on-surface rounded-lg px-4 py-3 cursor-pointer focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all hover:bg-surface-container-low" id="location" required>
                            <option disabled selected value="">Pilih lokasi area</option>
                            <option value="Kawasan Mandeh Utara">Kawasan Mandeh Utara</option>
                            <option value="Pulau Cubadak & Sekitarnya">Pulau Cubadak & Sekitarnya</option>
                            <option value="Perairan Teluk Tarusan">Perairan Teluk Tarusan</option>
                            <option value="Lainnya">Lainnya (Perlu ditinjau)</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-outline pointer-events-none">expand_more</span>
                    </div>
                    @error('location') <p class="text-error text-xs">{{ $message }}</p> @enderror
                </div>

                <!-- Field 2: Urgency Selection -->
                <div class="flex flex-col gap-base">
                    <label class="text-label-md font-bold text-primary">Tingkat Urgensi</label>
                    <div class="flex items-center gap-md">
                        @foreach(['low', 'medium', 'high', 'critical'] as $level)
                            <label class="flex items-center gap-xs cursor-pointer group">
                                <input type="radio" name="urgency" value="{{ $level }}" class="hidden peer" {{ old('urgency', 'low') == $level ? 'checked' : '' }}>
                                <div class="w-10 h-10 rounded-full border-2 border-outline-variant flex items-center justify-center peer-checked:bg-secondary peer-checked:border-secondary transition-all">
                                    <span class="material-symbols-outlined text-[20px] text-outline-variant peer-checked:text-white" style="font-variation-settings: 'FILL' 1;">sailing</span>
                                </div>
                                <span class="text-xs font-bold capitalize text-on-surface-variant peer-checked:text-primary">{{ $level }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('urgency') <p class="text-error text-xs">{{ $message }}</p> @enderror
                </div>

                <!-- Field 3: Text Area -->
                <div class="flex flex-col gap-base">
                    <label class="text-label-md font-bold text-primary" for="details">Keterangan</label>
                    <textarea name="description" class="w-full bg-surface-bright border border-outline-variant text-body-md text-on-surface rounded-lg px-4 py-3 min-h-[140px] resize-y focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all hover:bg-surface-container-low placeholder:text-outline-variant" id="details" placeholder="Apa yang perlu kami ketahui?" required>{{ old('description') }}</textarea>
                    @error('description') <p class="text-error text-xs">{{ $message }}</p> @enderror
                </div>

                <!-- Footer / Action -->
                <div class="pt-sm bg-surface-container-lowest rounded-b-xl">
                    <button type="submit" class="w-full bg-secondary text-on-secondary text-label-md font-bold py-4 px-6 rounded-lg hover:bg-secondary-fixed hover:text-on-secondary-fixed hover:-translate-y-[1px] hover:shadow-premium-hover transition-all duration-200 flex items-center justify-center gap-xs focus:outline-none focus:ring-4 focus:ring-secondary/30">
                        Kirim Laporan Berdaulat
                        <span class="material-symbols-outlined text-[20px]">send</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-maritime-layout>
