import { useState, useRef, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';

interface AwakeningProps {
    user: {
        id: string;
        name: string;
    };
}

export default function Awakening({ user }: AwakeningProps) {
    const [selectedFaction, setSelectedFaction] = useState<string>('nisnas');
    const [loading, setLoading] = useState(false);
    const carouselRef = useRef<HTMLDivElement>(null);

    const factions = [
        {
            id: 'nisnas',
            name: 'Bangsa Nisnas',
            sub: 'Organic • Nature',
            buff: '+5% Drop Rate Telur Langka',
            colorClass: 'faction-nisnas',
            colorHex: '#50C878',
            image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDkEGKpgd5NffNYnYFsTMmxzzLH5saRUFzBA6rBhHm1T0C9SXOuFf34tYwDDhzcPxq0Y8kDvFKabLswxdUnnEgVzKguGj4QtA2tNjsS8OCTU47bEmanaZunrrHCAp1MDyeigECBsHw58b-s9wDl65Fqbm8pNvWIkHoTs9GjeUog8iRXoHGmONDhMzpFbHDtZk9Dk3TeCePBhAPFsPhiwHxlW5sui0na9si9uJK5lzkxs_iNzKb2W94'
        },
        {
            id: 'human',
            name: 'Bangsa Manusia',
            sub: 'Strategy • Mecha',
            buff: '-5% Biaya Upgrade Iron Suit',
            colorClass: 'faction-human',
            colorHex: '#4682B4',
            image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDkvonJ1ptpKnsPz6eCQ9kGzsduUxThajP6v4A-Y6PzEmWLm90NJsfjb6233KLo366OMqdHmkJLHYOYK6CzKyfpXhnnUgmHR5tgYQFeOqzW9Bq6ImdeURfdLK_l7NDZ5bsaQ19n8eRMXZv-Uxi8Ko8sxAKf50e-3jfiVNlTLMkCoB9BuMptrXEGW-8g1S4J3ALC4tKOAYju5aevEGMA9gBEkLtD5v02iccc7Q42owuVSZTghMcM3jg'
        },
        {
            id: 'android',
            name: 'Bangsa Android',
            sub: 'Cyber • Tech',
            buff: '+2% Kecepatan Pemrosesan Modal',
            colorClass: 'faction-android',
            colorHex: '#DC143C',
            image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDLFk2gBY9Jj9ZCztkpzlURL34laQBFevBy_v6MTEfIjteEdu0zbB5bKGGkzBe4HYO-6Lx_b3SwzZugnckOMOhsmqPriHk1EVCOkEdYMPk_bs54niXO-sxT2mvioOOMGDCWMIYdop4HDR7XF3_k87BdxhS9hLoqLWvu2-45d-Uc3wg8YvlVnYiDRhhQIhO2r_DKOSW6-jpdtNl_l1aE13J-PExThA7KmAMV_8Y8Y8C5Qatsj5_3zt8'
        }
    ];

    const handleScroll = () => {
        if (!carouselRef.current) return;
        const carousel = carouselRef.current;
        const centerPoint = carousel.scrollLeft + (carousel.clientWidth / 2);
        let closestIndex = 0;
        let minDistance = Infinity;

        const cards = carousel.querySelectorAll('.faction-card');
        cards.forEach((card: any, index) => {
            const cardCenter = card.offsetLeft + (card.clientWidth / 2) - carousel.offsetLeft;
            const distance = Math.abs(centerPoint - cardCenter);
            if (distance < minDistance) {
                minDistance = distance;
                closestIndex = index;
            }
        });

        setSelectedFaction(factions[closestIndex].id);
    };

    useEffect(() => {
        handleScroll(); // initial state
    }, []);

    const handleConfirm = () => {
        setLoading(true);
        // Use uppercase for backward compatibility with existing backend logic
        router.post('/onboarding/awakening', { faction: selectedFaction.toUpperCase() }, {
            onFinish: () => setLoading(false)
        });
    };

    const currentFaction = factions.find(f => f.id === selectedFaction) || factions[0];

    return (
        <div className="bg-obsidian-surface text-on-surface min-h-screen relative overflow-hidden font-body-md text-body-md">
            <Head title="The Awakening - Faction Selection" />

            {/* Ambient Background Layer */}
            <div 
                className="absolute inset-0 z-0 opacity-30 pointer-events-none transition-all duration-800 ease-in-out" 
                style={{ background: `radial-gradient(circle at center, ${currentFaction.colorHex} 0%, transparent 60%)` }}
            ></div>
            <div className="absolute inset-0 z-0 opacity-20 pointer-events-none mix-blend-overlay" style={{ backgroundColor: '#131313' }}></div>
            
            {/* Header / Title Area */}
            <header className="relative z-10 pt-16 px-margin-mobile md:px-margin-desktop text-center">
                <h1 className="font-display-hero text-[28px] md:text-[48px] text-primary-container tracking-widest mb-stack-sm" style={{ fontFamily: "'Playfair Display', serif" }}>
                    THE AWAKENING
                </h1>
                <p className="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mx-auto">
                    Tentukan jalur strategimu. Pilihan faksi ini akan mengubah permainan finansialmu selamanya.
                </p>
            </header>

            {/* Faction Selection Carousel */}
            <main className="relative z-10 mt-stack-lg flex-1 flex flex-col justify-center h-[calc(100vh-200px)]">
                <div 
                    ref={carouselRef}
                    onScroll={handleScroll}
                    className="flex overflow-x-auto snap-x snap-mandatory scroll-smooth no-scrollbar gap-gutter px-margin-mobile md:px-margin-desktop pb-stack-lg h-[574px] items-center" 
                >
                    {factions.map((f) => {
                        const isActive = selectedFaction === f.id;
                        return (
                            <article 
                                key={f.id}
                                className={`faction-card snap-center shrink-0 w-[85vw] max-w-[400px] h-full rounded-xl glass-panel relative overflow-hidden flex flex-col group transition-all duration-300 transform border ${isActive ? `scale-100 opacity-100 border-${f.colorClass}/50` : `scale-95 opacity-50 border-glass-stroke hover:border-${f.colorClass}/30`}`}
                                onClick={() => {
                                    if(carouselRef.current) {
                                        const cards = carouselRef.current.querySelectorAll('.faction-card');
                                        const card = Array.from(cards).find(c => c.getAttribute('data-id') === f.id);
                                        if (card) {
                                            card.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                                        }
                                    }
                                }}
                                data-id={f.id}
                            >
                                <div className="absolute inset-0 bg-gradient-to-b from-[rgba(255,255,255,0.2)] to-transparent opacity-50 pointer-events-none"></div>
                                <div className={`absolute inset-0 bg-${f.colorClass}/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-xl shadow-[0_0_20px_${f.colorHex}33]`}></div>
                                
                                <div className="flex-1 relative overflow-hidden rounded-t-xl">
                                    <img className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" src={f.image} alt={f.name} />
                                    <div className="absolute inset-0 bg-gradient-to-t from-obsidian-surface to-transparent"></div>
                                </div>
                                
                                <div className="p-6 relative z-10 flex flex-col items-center text-center">
                                    <h2 className={`font-headline-md text-headline-md text-${f.colorClass} mb-2`} style={{ fontFamily: "'Playfair Display', serif" }}>{f.name}</h2>
                                    <p className="font-body-md text-body-md text-on-surface-variant mb-4">{f.sub}</p>
                                    
                                    <div className="bg-surface-container-low px-4 py-2 rounded-full border border-glass-stroke shadow-inner mb-6">
                                        <span className={`font-label-sm text-label-sm text-${f.colorClass}`}>{f.buff}</span>
                                    </div>
                                    
                                    <button 
                                        onClick={(e) => {
                                            if(isActive) {
                                                handleConfirm();
                                            }
                                        }}
                                        disabled={loading}
                                        className={`w-full py-3 rounded-lg border-2 border-${f.colorClass} text-${f.colorClass} font-label-sm text-label-sm uppercase tracking-wider hover:bg-${f.colorClass}/10 transition-colors ${isActive ? `shadow-[0_0_15px_${f.colorHex}1A]` : ''} group-hover:shadow-[0_0_20px_${f.colorHex}4D]`}
                                    >
                                        {loading && isActive ? 'MEMPROSES...' : (isActive ? 'PILIH KESETIAAN' : 'GULIR UNTUK MEMILIH')}
                                    </button>
                                </div>
                            </article>
                        );
                    })}
                    {/* Spacer for mobile scrolling */}
                    <div className="shrink-0 w-margin-mobile md:w-margin-desktop"></div>
                </div>

                {/* Indicators */}
                <div className="flex justify-center gap-2 mt-4">
                    {factions.map((f, i) => (
                        <div 
                            key={i} 
                            className={`h-2 rounded-full transition-all duration-300 ${selectedFaction === f.id ? 'w-8' : 'w-2 bg-surface-variant'}`}
                            style={{ backgroundColor: selectedFaction === f.id ? f.colorHex : undefined }}
                        ></div>
                    ))}
                </div>
            </main>
        </div>
    );
}
