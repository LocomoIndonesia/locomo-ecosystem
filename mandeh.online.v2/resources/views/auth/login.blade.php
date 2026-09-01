<x-maritime-layout>
    <x-slot name="title">Masuk | Mandeh.Online</x-slot>

    <main class="flex-1 px-4 py-8 sm:py-16 flex flex-col items-center justify-center max-w-[1200px] mx-auto w-full min-h-[calc(100vh-56px)]">
        <div class="w-full max-w-md flex flex-col gap-6 mb-8">
            <div class="text-center mb-4">
                <span class="material-symbols-outlined text-primary text-6xl material-symbols-fill animate-pulse-slow">anchor</span>
                <h2 class="text-headline-md font-extrabold text-primary mt-2 tracking-tight">Mandeh.Online</h2>
                <p class="text-body-md text-on-surface-variant">Masuk ke Ekosistem Pariwisata Lokal</p>
            </div>

            <div class="w-full bg-surface rounded-2xl sm:rounded-[24px] p-5 sm:p-8 shadow-modal border border-outline-variant/30 flex flex-col gap-6">
                <h3 class="text-headline-sm text-primary font-bold text-center">Login Akun</h3>

                @if ($errors->any())
                    <div class="p-4 bg-error-container text-on-error-container rounded-xl text-body-sm font-medium">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ url('/login') }}" method="POST" class="flex flex-col gap-4">
                    @csrf
                    
                    <div class="flex flex-col gap-1">
                        <label class="font-label-md text-primary/70 uppercase">Email atau No. Handphone</label>
                        <input name="email_or_phone" required type="text" placeholder="name@email.com atau 0812..." 
                               class="px-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg text-primary font-body-md placeholder-primary/30 outline-none w-full focus:ring-1 focus:ring-secondary focus:border-secondary transition-colors">
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="font-label-md text-primary/70 uppercase">Kata Sandi</label>
                        <input name="password" required type="password" placeholder="••••••••" 
                               class="px-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg text-primary font-body-md placeholder-primary/30 outline-none w-full focus:ring-1 focus:ring-secondary focus:border-secondary transition-colors">
                    </div>

                    <button type="submit" class="w-full h-[56px] bg-secondary text-on-secondary font-body-lg font-bold rounded-lg flex items-center justify-center hover:opacity-90 active:scale-[0.98] transition-all mt-2 shadow-premium">
                        Masuk Sekarang
                    </button>
                </form>

                <div class="text-center text-label-md text-on-surface-variant">
                    Belum punya akun? <a href="{{ url('/register') }}" class="text-secondary font-bold hover:underline">Daftar Individu</a>
                </div>
            </div>
        </div>
    </main>
</x-maritime-layout>
