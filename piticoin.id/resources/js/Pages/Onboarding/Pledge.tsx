import { useState } from 'react';
import { Head, router } from '@inertiajs/react';

interface PledgeProps {
    user: {
        id: string;
        name: string;
    };
}

export default function Pledge({ user }: PledgeProps) {
    const [selectedLicense, setSelectedLicense] = useState<string | null>('PETUALANG');
    const [loading, setLoading] = useState(false);

    const licenses = [
        {
            id: 'PEMULA',
            name: 'Pemula',
            price: 'Rp 50.000',
            reward: '5.000 Koin Perak',
            icon: 'work',
            benefits: [
                'Akses Dasar Peta'
            ],
            recommended: false
        },
        {
            id: 'PETUALANG',
            name: 'Petualang',
            price: 'Rp 100.000',
            reward: '10.000 Koin Perak',
            icon: 'explore',
            benefits: [
                'Akses Penuh Peta',
                'Prioritas Misi Harian'
            ],
            recommended: true
        },
        {
            id: 'SAUDAGAR',
            name: 'Saudagar',
            price: 'Rp 200.000',
            reward: '20.000 Koin Perak',
            icon: 'diamond',
            benefits: [
                'Status VIP Eksklusif'
            ],
            recommended: false
        }
    ];

    const handleConfirm = () => {
        if (!selectedLicense) return;
        setLoading(true);
        router.post('/onboarding/pledge', { license_type: selectedLicense }, {
            onFinish: () => setLoading(false)
        });
    };

    return (
        <div className="bg-background text-on-surface min-h-screen font-body-md flex flex-col relative overflow-x-hidden">
            <Head title="The Pledge - PITICOIN.ID" />

            {/* Atmospheric Background */}
            <div className="fixed inset-0 z-0 pointer-events-none">
                <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-surface-container-high via-background to-background opacity-80"></div>
                <div className="absolute inset-0 bg-cover bg-center opacity-30 mix-blend-overlay" style={{ backgroundImage: "url('https://lh3.googleusercontent.com/aida-public/AB6AXuApEGRDHQdD2r2u1Ch-D589rol2umTBznqYHjDIlia8ZPDjU5UIpFS8liLn084mT7yJwMdcBKo1RTUbTrLFT4Vr4VPwr3KTxBpD2fKroHIVn9hE1TWR8Z7Z2xyd0zMkLd1Saooh_im3OIXw6dVRwK5H-ce9QBsF2EHrjMV9K8R2NC_3wXU2A92a7BdLiEpdyiyQjw20DGTtviLnutCNnIqr_oQtEOKwIJLKbHxsAEHQZlqLB5m1kAI')" }}></div>
            </div>

            {/* TopAppBar */}
            <header className="bg-transparent text-primary-container font-headline-md text-headline-md-mobile flex justify-between items-center w-full px-margin-mobile pt-stack-md h-16 docked full-width top-0 z-50 relative">
                <div className="text-on-surface-variant flex items-center justify-center p-2 rounded-full opacity-0 pointer-events-none">
                    <span className="material-symbols-outlined" style={{ fontVariationSettings: "'FILL' 0" }}>menu</span>
                </div>
                <div className="font-headline-lg-mobile text-[24px] md:text-headline-lg-mobile font-bold text-primary-container tracking-widest text-center" style={{ fontFamily: "'Playfair Display', serif" }}>
                    PITICOIN.ID
                </div>
                <div className="w-8 h-8 rounded-full overflow-hidden border border-glass-stroke">
                    <img alt="Explorer Rank Icon" className="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCmmiBPSSqw1usVIQgKXQvk9yBCx7BQKk82otyJw-ov0lwYkeR_dIOp9y_aZQaeHypTKkCEKU-_Z6_qnZRMuTDp1VS3jZYKWJ__4LH-xhQV1gUTWjXgTWlqzaxxc2lwXaTHNH4Yxc_L1Vq4pgXTcZnMdnjmMYJYbgCr7db2hS_4P6WqMQs_4K8UNXGhn4Nc-S9NTekDK6djGHQVMgoX4kYaHE6NCCxXUi0KlvvDk5UXRj1m1_sxOSk"/>
                </div>
            </header>

            {/* Main Content Canvas */}
            <main className="flex-grow flex flex-col items-center justify-center relative z-10 w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-stack-lg gap-stack-lg">
                <div className="text-center space-y-stack-sm mb-stack-md">
                    <h1 className="font-display-hero text-[32px] md:text-[48px] text-primary-container drop-shadow-md" style={{ fontFamily: "'Playfair Display', serif" }}>Perbekalan Ekspedisi</h1>
                    <p className="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mx-auto">
                        Pilih lisensi penjelajahan Anda untuk memulai perburuan harta karun. Setiap tingkatan memberikan bekal koin perak untuk memulai petualangan.
                    </p>
                </div>

                {/* Pricing Cards Container */}
                <div className="w-full grid grid-cols-1 md:grid-cols-3 gap-stack-md md:gap-gutter items-center">
                    {licenses.map((l) => {
                        const isSelected = selectedLicense === l.id;
                        return (
                            <div 
                                key={l.id}
                                onClick={() => setSelectedLicense(l.id)}
                                className={`glass-panel rounded-xl p-6 md:p-8 flex flex-col items-center text-center h-full justify-between cursor-pointer transition-all duration-300 relative ${isSelected ? 'scale-105 z-10 border-primary-container bg-gradient-to-b from-white/5 to-[#1a1a1a]/80 shadow-[0_0_30px_rgba(255,215,0,0.15)] -translate-y-1' : 'border-t-primary-container/30 border-x-white/10 border-b-white/5 bg-gradient-to-b from-white/5 to-[#1a1a1a]/80 hover:border-primary-container hover:shadow-[0_0_30px_rgba(255,215,0,0.15)] hover:-translate-y-1'}`}
                            >
                                {l.recommended && (
                                    <div className="absolute -top-4 bg-primary-container text-on-primary-container font-label-sm text-[10px] px-4 py-1 rounded-full shadow-[0_0_10px_rgba(255,215,0,0.5)] font-bold tracking-widest">
                                        PILIHAN UTAMA
                                    </div>
                                )}
                                
                                <div className="w-full">
                                    <div className={`w-16 h-16 md:w-20 md:h-20 mx-auto mb-4 ${isSelected ? 'text-primary-container drop-shadow-[0_0_15px_rgba(255,215,0,0.4)]' : 'text-on-surface-variant'}`}>
                                        <span className="material-symbols-outlined" style={{ fontSize: isSelected ? '80px' : '64px', fontVariationSettings: isSelected ? "'FILL' 1" : "'FILL' 0" }}>{l.icon}</span>
                                    </div>
                                    <h3 className={`font-headline-md text-[24px] mb-2 ${isSelected ? 'text-primary-container md:text-[32px]' : 'text-on-surface'}`} style={{ fontFamily: "'Playfair Display', serif" }}>{l.name}</h3>
                                    <div className={`font-body-lg text-body-lg mb-6 ${isSelected ? 'text-primary-container font-bold' : 'text-primary-container'}`}>{l.price}</div>
                                </div>
                                
                                <div className={`w-full pt-4 border-t ${isSelected ? 'border-glass-stroke/30' : 'border-glass-stroke'}`}>
                                    <ul className="text-left font-body-md text-sm md:text-body-md text-on-surface-variant space-y-3">
                                        <li className={`flex items-center gap-2 ${isSelected ? 'font-bold text-primary-container' : ''}`}>
                                            <span className="material-symbols-outlined text-primary-container text-sm" style={{ fontVariationSettings: "'FILL' 1" }}>toll</span>
                                            Reward: {l.reward}
                                        </li>
                                        {l.benefits.map((b, i) => (
                                            <li key={i} className={`flex items-center gap-2 ${isSelected ? 'text-on-surface' : ''}`}>
                                                <span className="material-symbols-outlined text-primary-container text-sm" style={{ fontVariationSettings: "'FILL' 1" }}>check_circle</span>
                                                {b}
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            </div>
                        );
                    })}
                </div>

                {/* Call to Action */}
                <div className="w-full max-w-md mt-stack-lg">
                    <button 
                        onClick={handleConfirm}
                        disabled={!selectedLicense || loading}
                        className="w-full bg-primary-container text-on-primary-container font-body-lg text-sm md:text-body-lg font-bold py-4 px-6 rounded-lg shadow-[0_0_15px_rgba(255,215,0,0.3)] hover:shadow-[0_0_25px_rgba(255,215,0,0.5)] flex items-center justify-center gap-3 transition-transform hover:scale-[1.02] active:scale-95 disabled:opacity-50 disabled:hover:scale-100 disabled:cursor-not-allowed uppercase tracking-wider"
                    >
                        <span className="material-symbols-outlined" style={{ fontVariationSettings: "'FILL' 1" }}>contract</span>
                        {loading ? 'MEMPROSES...' : 'Tandatangani Kontrak & Bayar (Xendit/QRIS)'}
                    </button>
                </div>
            </main>
        </div>
    );
}
