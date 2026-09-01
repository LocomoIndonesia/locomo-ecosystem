import { useState } from 'react';
import { Head, router } from '@inertiajs/react';

interface PathProps {
    user: {
        id: string;
        name: string;
    };
}

export default function Path({ user }: PathProps) {
    const [selectedPath, setSelectedPath] = useState<string | null>(null);
    const [loading, setLoading] = useState(false);

    const paths = [
        {
            id: 'PENDEKAR',
            name: 'Pendekar',
            icon: 'swords',
            desc: 'Pakar pertempuran jarak dekat. Kuat dan tangguh di garis depan.'
        },
        {
            id: 'RANGER',
            name: 'Ranger',
            icon: 'architecture',
            desc: 'Ahli serangan jarak jauh dan pengintaian yang mematikan.'
        },
        {
            id: 'SPESIALIS',
            name: 'Spesialis',
            icon: 'smart_toy',
            desc: 'Pengendali teknologi dan pendamping mekanik tingkat tinggi.'
        },
        {
            id: 'MENTALIST',
            name: 'Mentalist',
            icon: 'menu_book',
            desc: 'Pengguna sihir kuno dan manipulasi pikiran yang misterius.'
        }
    ];

    const handleConfirm = () => {
        if (!selectedPath) return;
        setLoading(true);
        router.post('/onboarding/path', { role_class: selectedPath }, {
            onFinish: () => setLoading(false)
        });
    };

    return (
        <div className="min-h-screen bg-background text-on-surface flex flex-col items-center justify-center font-body-md relative overflow-hidden">
            <Head title="PITICOIN.ID - The Path" />

            {/* Atmospheric Background Elements */}
            <div className="absolute top-0 left-0 w-full h-full pointer-events-none z-0 opacity-30">
                <div className="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-primary-container blur-[150px] opacity-10"></div>
                <div className="absolute bottom-[-10%] right-[-10%] w-[30%] h-[30%] rounded-full bg-faction-human blur-[150px] opacity-10"></div>
            </div>

            {/* Faint gold dust overlay simulation (using CSS background in a full-screen div) */}
            <div 
                className="absolute inset-0 pointer-events-none z-[-1]" 
                style={{
                    backgroundImage: 'radial-gradient(circle at 50% 50%, rgba(255, 215, 0, 0.03) 0%, transparent 100%), radial-gradient(circle at 10% 20%, rgba(255, 215, 0, 0.02) 0%, transparent 80%), radial-gradient(circle at 90% 80%, rgba(255, 215, 0, 0.02) 0%, transparent 80%)',
                    backgroundAttachment: 'fixed'
                }}
            ></div>

            {/* Main Container */}
            <main className="w-full max-w-container-max px-margin-mobile md:px-margin-desktop z-10 flex flex-col items-center py-10">
                
                {/* Header Section */}
                <div className="text-center mb-stack-lg w-full max-w-2xl">
                    <h1 className="font-headline-lg-mobile md:font-headline-lg text-[28px] md:text-[32px] text-primary-container mb-stack-sm tracking-wide" style={{ fontFamily: "'Playfair Display', serif" }}>
                        Tentukan Jalur Perjuangan
                    </h1>
                    <p className="font-body-md text-body-md text-on-surface-variant">
                        Pilih peran utamamu dalam memburu harta karun. Pilihan ini akan menentukan strategi dan keuntunganmu di masa depan.
                    </p>
                </div>

                {/* 2x2 Grid Selection */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-gutter w-full max-w-3xl mb-stack-lg">
                    {paths.map((p) => {
                        const isSelected = selectedPath === p.id;
                        return (
                            <div 
                                key={p.id}
                                onClick={() => setSelectedPath(p.id)}
                                className={`glass-panel rounded-xl p-6 flex flex-col items-center justify-center text-center aspect-[4/3] md:aspect-square relative overflow-hidden group cursor-pointer transition-all duration-300 ${isSelected ? 'border-primary-container bg-primary-container/5 shadow-[0_0_20px_rgba(255,215,0,0.3),inset_0_0_10px_rgba(255,215,0,0.1)] -translate-y-[2px]' : 'hover:border-primary-container/50 hover:shadow-[0_0_15px_rgba(255,215,0,0.1)] hover:-translate-y-[2px]'}`}
                                style={{ borderWidth: isSelected ? '2px' : '1px' }}
                            >
                                {/* Ambient glow inside card */}
                                <div className="absolute inset-0 bg-gradient-to-b from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                
                                <span 
                                    className={`material-symbols-outlined text-5xl mb-4 transition-colors duration-300 ${isSelected ? 'text-primary-container drop-shadow-[0_0_10px_rgba(255,215,0,0.5)]' : 'text-on-surface-variant'}`} 
                                    style={{ fontSize: '64px', fontVariationSettings: isSelected ? "'FILL' 1" : "'FILL' 0" }}
                                >
                                    {p.icon}
                                </span>
                                
                                <h3 className="font-headline-md text-headline-md text-primary mb-2" style={{ fontFamily: "'Playfair Display', serif" }}>
                                    {p.name}
                                </h3>
                                <p className="font-label-sm text-label-sm text-on-surface-variant/80 font-normal mt-2 px-4">
                                    {p.desc}
                                </p>
                            </div>
                        );
                    })}
                </div>

                {/* Action Area */}
                <div className="w-full max-w-sm">
                    <button 
                        onClick={handleConfirm}
                        disabled={!selectedPath || loading}
                        className="w-full py-4 rounded-lg font-body-md text-body-md font-bold uppercase tracking-wider flex items-center justify-center gap-2 transition-all duration-300 bg-primary-container text-background shadow-[0_0_15px_rgba(255,215,0,0.3)] hover:shadow-[0_0_25px_rgba(255,215,0,0.5)] hover:scale-[1.02] disabled:bg-surface-variant disabled:text-on-surface-variant disabled:shadow-none disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:scale-100"
                    >
                        <span>{loading ? 'MEMPROSES...' : 'KONFIRMASI JALUR'}</span>
                        <span className="material-symbols-outlined text-lg" style={{ fontVariationSettings: "'FILL' 1" }}>arrow_forward</span>
                    </button>
                </div>

            </main>
        </div>
    );
}
