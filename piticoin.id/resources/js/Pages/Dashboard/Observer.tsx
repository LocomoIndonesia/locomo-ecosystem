import { Head, Link } from '@inertiajs/react';

interface ObserverProps {
    user: {
        id: string;
        name: string;
        whatsapp: string;
        role: string;
        faction: string;
        role_class: string;
        level: number;
        experience_points: number;
    };
    wallet: {
        silver_balance: number;
        gold_balance: number;
    } | null;
}

export default function Observer({ user, wallet }: ObserverProps) {
    return (
        <div className="bg-background text-on-surface font-body-md min-h-screen relative overflow-x-hidden pb-24">
            <Head title="Markas Besar - The Observer" />

            {/* Dark overlay for readability */}
            <div className="fixed inset-0 w-full h-full bg-background/85 z-[-1] pointer-events-none"></div>

            {/* TopAppBar */}
            <header className="bg-transparent docked full-width top-0 flat no shadows flex justify-between items-center w-full px-margin-mobile pt-stack-md h-16 sticky z-40">
                <Link method="post" href="/logout" as="button" className="text-on-surface-variant hover:text-primary-fixed-dim transition-colors flex items-center justify-center p-2 rounded-full bg-obsidian-surface/40 backdrop-blur-md border border-glass-stroke">
                    <span className="material-symbols-outlined" style={{ fontVariationSettings: "'FILL' 0" }}>logout</span>
                </Link>
                <h1 className="font-headline-lg-mobile text-headline-lg-mobile font-bold text-primary-container tracking-widest text-center glow-text" style={{ fontFamily: "'Playfair Display', serif" }}>
                    PITICOIN.ID
                </h1>
                <div className="w-10 h-10 rounded-full bg-surface-container-high border-2 border-outline-variant flex items-center justify-center overflow-hidden grayscale opacity-70">
                    <span className="material-symbols-outlined text-on-surface-variant" style={{ fontVariationSettings: "'FILL' 1" }}>person</span>
                </div>
            </header>

            <main className="max-w-container-max mx-auto px-margin-mobile flex flex-col gap-stack-lg pt-4">
                
                {/* Section 1: The Ledger Pool */}
                <section className="flex flex-col items-center justify-center text-center relative z-10 animate-float">
                    <div className="absolute -top-4 w-full flex justify-center z-20">
                        <div className="bg-surface-container-high/90 backdrop-blur-md border border-primary-container/30 rounded-full px-4 py-2 flex items-center gap-2 shadow-[0_0_15px_rgba(255,215,0,0.15)]">
                            <span className="material-symbols-outlined text-primary-container" style={{ fontVariationSettings: "'FILL' 1" }}>local_fire_department</span>
                            <span className="font-label-sm text-label-sm text-primary-container">Selamat datang, {user.name}!</span>
                        </div>
                    </div>
                    <h2 className="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest mt-8 mb-2">
                        PITI COIN HARI INI
                    </h2>
                    <div className="font-display-hero text-display-hero text-primary-container glow-text bg-gradient-to-b from-primary-fixed to-primary-fixed-dim bg-clip-text text-transparent" style={{ fontFamily: "'Playfair Display', serif" }}>
                        Rp 45.230.500
                    </div>
                </section>

                {/* Section 2: Core Object (Mountain & Menus) */}
                <section className="flex flex-col gap-stack-md relative">
                    <div className="relative w-full h-64 md:h-80 rounded-xl overflow-hidden border border-glass-stroke shadow-2xl bg-surface-container-lowest">
                        <div className="absolute inset-0 bg-cover bg-center opacity-40 mix-blend-luminosity" style={{ backgroundImage: "url('https://lh3.googleusercontent.com/aida-public/AB6AXuD8CVndl4PnBK66gGKqIORjBNLIQgXsFLtawLsVFiDT92vzMwSRmsvFtKVtUaOtv1AtQshGgj0x7l7VL-b3SKfuah3bHWEyDxBmQ_BUBsE-IIkIHE0QSnCRlbMoTtcMxeeGcPE9txH0WHSF2FamuMhGSPVeJKIeDgOLjUvzCvmCkDbdCySny6JcKVt2nuZ63pAT0Pgc4NAgbaXgk57Xgg3xABseb01IVsvumiFgMh2z64hOjoaOd8g')" }}>
                        </div>
                        <div className="absolute inset-0 bg-gradient-to-t from-obsidian-surface to-transparent"></div>
                        <div className="absolute bottom-4 left-0 w-full flex justify-center items-center gap-4 px-4">
                            <button className="flex flex-col items-center gap-2 p-3 bg-obsidian-surface/60 backdrop-blur-xl border border-glass-stroke rounded-lg w-24 hover:border-primary-container/50 transition-colors opacity-50 cursor-not-allowed">
                                <span className="material-symbols-outlined text-primary-container">store</span>
                                <span className="font-label-sm text-label-sm text-on-surface">Toko</span>
                            </button>
                            <button className="flex flex-col items-center gap-2 p-3 bg-obsidian-surface/60 backdrop-blur-xl border border-glass-stroke rounded-lg w-24 hover:border-primary-container/50 transition-colors opacity-50 cursor-not-allowed">
                                <span className="material-symbols-outlined text-primary-container">forum</span>
                                <span className="font-label-sm text-label-sm text-on-surface">Ruang Informasi</span>
                            </button>
                            <button className="flex flex-col items-center gap-2 p-3 bg-obsidian-surface/60 backdrop-blur-xl border border-glass-stroke rounded-lg w-24 hover:border-primary-container/50 transition-colors opacity-50 cursor-not-allowed">
                                <span className="material-symbols-outlined text-primary-container">campaign</span>
                                <span className="font-label-sm text-label-sm text-on-surface">Pengumuman</span>
                            </button>
                        </div>
                    </div>
                </section>

                {/* Section 3: Campaign Board (KUNCI UTAMA) */}
                <section className="flex flex-col gap-4">
                    <div className="flex items-center gap-2">
                        <span className="material-symbols-outlined text-primary-container" style={{ fontVariationSettings: "'FILL' 1" }}>key</span>
                        <h3 className="font-headline-md text-headline-md text-on-surface" style={{ fontFamily: "'Playfair Display', serif" }}>KUNCI UTAMA</h3>
                    </div>
                    <div className="relative bg-obsidian-surface/80 backdrop-blur-xl border border-glass-stroke rounded-xl p-6 overflow-hidden">
                        {/* Dimmed Content */}
                        <div className="opacity-30 grayscale pointer-events-none blur-[2px] transition-all duration-300">
                            <div className="flex items-center gap-4 bg-surface-container-high/50 p-4 rounded-lg border border-glass-stroke mb-4">
                                <div className="w-16 h-16 bg-surface-container rounded flex items-center justify-center border border-primary-container/20">
                                    <span className="material-symbols-outlined text-primary-container text-3xl">map</span>
                                </div>
                                <div>
                                    <h4 className="font-headline-md text-headline-md text-primary-container mb-1" style={{ fontFamily: "'Playfair Display', serif" }}>Ekspedisi Lembah Pulsa</h4>
                                    <p className="font-body-md text-body-md text-on-surface-variant">Hadiah 50.000 Emas</p>
                                </div>
                            </div>
                        </div>
                        {/* Locked Overlay */}
                        <div className="absolute inset-0 flex flex-col items-center justify-center bg-obsidian-surface/50 backdrop-blur-[2px] z-10 p-4 text-center">
                            <span className="material-symbols-outlined text-6xl text-on-surface-variant mb-4 drop-shadow-lg" style={{ fontVariationSettings: "'FILL' 1" }}>lock</span>
                            <Link href="/onboarding/pledge" className="bg-faction-android text-primary-container font-label-sm text-label-sm px-6 py-3 rounded-lg border border-primary-container/50 shadow-[0_0_15px_rgba(255,215,0,0.2)] hover:bg-error-container transition-colors uppercase tracking-wider flex items-center gap-2">
                                <span className="material-symbols-outlined text-[18px]">shopping_cart</span>
                                TERKUNCI: Beli Lisensi
                            </Link>
                        </div>
                    </div>
                </section>

                {/* Section 4: Processing Deep (Mini Games) */}
                <section className="flex flex-col gap-4">
                    <h3 className="font-headline-md text-headline-md text-on-surface-variant" style={{ fontFamily: "'Playfair Display', serif" }}>Fasilitas Tambahan</h3>
                    <div className="grid grid-cols-2 gap-4">
                        <div className="bg-obsidian-surface/40 backdrop-blur-md border border-glass-stroke rounded-xl p-4 flex flex-col items-center text-center opacity-40 grayscale relative overflow-hidden">
                            <span className="material-symbols-outlined text-4xl text-primary-container mb-2">casino</span>
                            <h4 className="font-label-sm text-label-sm text-on-surface">Harta Karun Slot</h4>
                            <div className="absolute inset-0 bg-background/50 flex items-center justify-center">
                                <span className="material-symbols-outlined text-on-surface-variant" style={{ fontVariationSettings: "'FILL' 1" }}>lock</span>
                            </div>
                        </div>
                        <div className="bg-obsidian-surface/40 backdrop-blur-md border border-glass-stroke rounded-xl p-4 flex flex-col items-center text-center opacity-40 grayscale relative overflow-hidden">
                            <span className="material-symbols-outlined text-4xl text-primary-container mb-2">hardware</span>
                            <h4 className="font-label-sm text-label-sm text-on-surface">Lebur Koin & Lelang</h4>
                            <div className="absolute inset-0 bg-background/50 flex items-center justify-center">
                                <span className="material-symbols-outlined text-on-surface-variant" style={{ fontVariationSettings: "'FILL' 1" }}>lock</span>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Section 5: Footer Status (Musafir) */}
                <section className="mt-4 bg-obsidian-surface/90 backdrop-blur-2xl border-t border-b md:border-l md:border-r border-glass-stroke p-4 rounded-none md:rounded-xl shadow-[0_0_20px_rgba(0,0,0,0.5)]">
                    <div className="flex items-center gap-4">
                        <div className="w-12 h-12 rounded-full bg-surface-container border-2 border-outline-variant flex items-center justify-center overflow-hidden opacity-50">
                            <span className="material-symbols-outlined text-on-surface-variant text-2xl">person_off</span>
                        </div>
                        <div className="flex-1">
                            <div className="flex justify-between items-end mb-1">
                                <div>
                                    <p className="font-label-sm text-label-sm text-on-surface-variant uppercase">The Observer</p>
                                    <p className="font-headline-md text-headline-md text-on-surface leading-none mt-1" style={{ fontFamily: "'Playfair Display', serif" }}>
                                        Level 1 <span className="text-on-surface-variant text-sm font-body-md">(Musafir)</span>
                                    </p>
                                </div>
                                <p className="font-label-sm text-label-sm text-on-surface-variant">EXP: 0/100</p>
                            </div>
                            <div className="w-full h-2 bg-surface-container rounded-full overflow-hidden border border-glass-stroke">
                                <div className="w-[0%] h-full bg-primary-container shadow-[inset_0_0_5px_rgba(255,255,255,0.5)]"></div>
                            </div>
                        </div>
                    </div>
                    <div className="flex justify-between mt-4 pt-4 border-t border-glass-stroke">
                        <div className="flex items-center gap-2">
                            <div className="w-6 h-6 rounded-full bg-surface-variant flex items-center justify-center border border-outline">
                                <span className="text-[10px] text-on-surface-variant">S</span>
                            </div>
                            <span className="font-label-sm text-label-sm text-on-surface-variant">0 Perak</span>
                        </div>
                        <div className="flex items-center gap-2">
                            <div className="w-6 h-6 rounded-full bg-primary-container/20 flex items-center justify-center border border-primary-container opacity-50">
                                <span className="text-[10px] text-primary-container">G</span>
                            </div>
                            <span className="font-label-sm text-label-sm text-on-surface-variant">0 Emas</span>
                        </div>
                    </div>
                </section>

            </main>

            {/* BottomNavBar */}
            <nav className="bg-obsidian-surface/60 backdrop-blur-xl docked full-width bottom-0 rounded-t-xl border-t border-glass-stroke shadow-[0_-4px_20px_rgba(255,215,0,0.1)] fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-4 pb-6 pt-2">
                <div className="flex flex-col items-center justify-center text-primary-container scale-110 transition-transform cursor-pointer">
                    <span className="material-symbols-outlined" style={{ fontVariationSettings: "'FILL' 1" }}>explore</span>
                    <span className="font-label-sm text-label-sm mt-1">Quest</span>
                </div>
                <div className="flex flex-col items-center justify-center text-on-surface-variant/70 hover:text-primary transition-all duration-300 ease-out cursor-pointer">
                    <span className="material-symbols-outlined">payments</span>
                    <span className="font-label-sm text-label-sm mt-1">Vault</span>
                </div>
                <div className="flex flex-col items-center justify-center text-on-surface-variant/70 hover:text-primary transition-all duration-300 ease-out cursor-pointer">
                    <span className="material-symbols-outlined">map</span>
                    <span className="font-label-sm text-label-sm mt-1">Map</span>
                </div>
                <div className="flex flex-col items-center justify-center text-on-surface-variant/70 hover:text-primary transition-all duration-300 ease-out cursor-pointer">
                    <span className="material-symbols-outlined">person</span>
                    <span className="font-label-sm text-label-sm mt-1">Profile</span>
                </div>
            </nav>

        </div>
    );
}
