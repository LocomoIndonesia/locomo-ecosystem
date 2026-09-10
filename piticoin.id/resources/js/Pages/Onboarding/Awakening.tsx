import { useState, useRef, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import { 
    Info, 
    X, 
    Sparkles, 
    Shield, 
    Cpu, 
    Leaf, 
    Check, 
    ArrowRight, 
    Zap, 
    Coins, 
    Users, 
    TrendingUp,
    ChevronRight
} from 'lucide-react';

interface AwakeningProps {
    user: {
        id: string;
        name: string;
    };
}

interface FactionDetail {
    id: string;
    name: string;
    sub: string;
    title: string;
    philosophy: string;
    gameplay: string;
    suitableFor: string;
    buff: string;
    buffs: string[];
    colorClass: string;
    colorHex: string;
    image: string;
    icon: any;
}

export default function Awakening({ user }: AwakeningProps) {
    const [selectedFaction, setSelectedFaction] = useState<string>('nisnas');
    const [loading, setLoading] = useState(false);
    const [showDetailModal, setShowDetailModal] = useState(false);
    const [modalFactionId, setModalFactionId] = useState<string>('nisnas');
    const carouselRef = useRef<HTMLDivElement>(null);

    const factions: FactionDetail[] = [
        {
            id: 'nisnas',
            name: 'Bangsa Nisnas',
            sub: 'Organic • Nature • Wisdom',
            title: 'Sang Penjaga Harmoni Alam & Panen Harta Karun',
            philosophy: 'Ras purba yang hidup selaras dengan energi tanah Minangkabau. Mengandalkan intuisi, berkah kesabaran, dan kearifan spiritual untuk menambang harta tersembunyi tanpa merusak alam.',
            gameplay: 'Farming berkala, pengumpulan pasif yang stabil, minim risiko, dan pemanfaatan berkah panen ekosistem.',
            suitableFor: 'Petualang penyabar, pengumpul dividen harian, pelaku pertanian/desa, dan pemerhati keberlanjutan nagari.',
            buff: '+5% Drop Rate Telur Langka & Koin Pusaka',
            buffs: [
                '+5% Peluang Drop Koin Emas & Pusaka pada Ekspedisi Gunung Mas',
                '+10% Ketahanan Staking Misi Alam & Misi Lingkungan Komunitas',
                'Prioritas Alokasi Angpao Koin Perak Harian',
                'Akses Khusus Produk Komoditas Alam & Herbal di Unimart.group'
            ],
            colorClass: 'faction-nisnas',
            colorHex: '#50C878',
            image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDkEGKpgd5NffNYnYFsTMmxzzLH5saRUFzBA6rBhHm1T0C9SXOuFf34tYwDDhzcPxq0Y8kDvFKabLswxdUnnEgVzKguGj4QtA2tNjsS8OCTU47bEmanaZunrrHCAp1MDyeigECBsHw58b-s9wDl65Fqbm8pNvWIkHoTs9GjeUog8iRXoHGmONDhMzpFbHDtZk9Dk3TeCePBhAPFsPhiwHxlW5sui0na9si9uJK5lzkxs_iNzKb2W94',
            icon: Leaf
        },
        {
            id: 'human',
            name: 'Bangsa Manusia',
            sub: 'Strategy • Mecha • Alliance',
            title: 'Arsitek Peradaban & Panglima Karavan Niaga',
            philosophy: 'Kaum pembangun yang menguasai seni berniaga, aliansi karavan saudagar, dan formasi taktis organisasi. Mengutamakan solidaritas antar-warga dan sinergi kolektif.',
            gameplay: 'Membangun jejaring karavan downline (L1 & L2), diplomasi bisnis, ekspansi merchant, dan efisiensi operasional modal.',
            suitableFor: 'Saudagar sejati, pemilik toko/warung UMKM, aktivis pemuda nagari, dan penggerak komunitas.',
            buff: '-5% Biaya Upgrade & +10% Sinergi Karavan',
            buffs: [
                '-5% Biaya Upgrade Perlengkapan Lisensi & Piagam Saudagar',
                '+10% Efisiensi Bonus Karavan Upline (Jaringan MLM L1 & L2)',
                'Diskon Biaya Langganan POS Kasir Pintar & Landing Page Unimart.group',
                'Akses Modul Demokrasi & Musyawarah di Smart-Citizen'
            ],
            colorClass: 'faction-human',
            colorHex: '#4682B4',
            image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDkvonJ1ptpKnsPz6eCQ9kGzsduUxThajP6v4A-Y6PzEmWLm90NJsfjb6233KLo366OMqdHmkJLHYOYK6CzKyfpXhnnUgmHR5tgYQFeOqzW9Bq6ImdeURfdLK_l7NDZ5bsaQ19n8eRMXZv-Uxi8Ko8sxAKf50e-3jfiVNlTLMkCoB9BuMptrXEGW-8g1S4J3ALC4tKOAYju5aevEGMA9gBEkLtD5v02iccc7Q42owuVSZTghMcM3jg',
            icon: Users
        },
        {
            id: 'android',
            name: 'Bangsa Android',
            sub: 'Cyber • Tech • Precision',
            title: 'Entitas Siber Deterministik & Eksekutor Kecepatan',
            philosophy: 'Kecerdasan masa depan yang bergerak berdasarkan kalkulasi matematis presisi, data analitik instan, dan otomatisasi tanpa kompromi.',
            gameplay: 'High-frequency action, arbitrase bursa lelang P2P, kalkulasi RNG Slot KOA optimal, dan eksekusi perputaran koin kilat.',
            suitableFor: 'Pemain analitis, trader koin, peminat teknologi siber, dan mereka yang mengejar kecepatan likuiditas tinggi.',
            buff: '+2% Kecepatan Pemrosesan Modal & Efisiensi Staking',
            buffs: [
                '+2% Kecepatan Eksekusi & Pemrosesan Modal di Bursa Lelang P2P',
                '+5% Efisiensi Algoritma Staking Misi Harian',
                'Analitik Peluang Pola Runtuhan Slot KOA Lebih Mendalam',
                'Integrasi API Otomatis ke Locomo Agent OS'
            ],
            colorClass: 'faction-android',
            colorHex: '#DC143C',
            image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDLFk2gBY9Jj9ZCztkpzlURL34laQBFevBy_v6MTEfIjteEdu0zbB5bKGGkzBe4HYO-6Lx_b3SwzZugnckOMOhsmqPriHk1EVCOkEdYMPk_bs54niXO-sxT2mvioOOMGDCWMIYdop4HDR7XF3_k87BdxhS9hLoqLWvu2-45d-Uc3wg8YvlVnYiDRhhQIhO2r_DKOSW6-jpdtNl_l1aE13J-PExThA7KmAMV_8Y8Y8C5Qatsj5_3zt8',
            icon: Cpu
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
        handleScroll();
    }, []);

    const handleSelectFaction = (factionId: string) => {
        setSelectedFaction(factionId);
        if (carouselRef.current) {
            const cards = carouselRef.current.querySelectorAll('.faction-card');
            const card = Array.from(cards).find(c => c.getAttribute('data-id') === factionId);
            if (card) {
                card.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }
        }
    };

    const handleConfirm = (factionToConfirm?: string) => {
        const target = factionToConfirm || selectedFaction;
        setLoading(true);
        router.post('/onboarding/awakening', { faction: target.toUpperCase() }, {
            onFinish: () => setLoading(false)
        });
    };

    const openDetailModal = (factionId: string, e?: React.MouseEvent) => {
        if (e) e.stopPropagation();
        setModalFactionId(factionId);
        setShowDetailModal(true);
    };

    const currentFaction = factions.find(f => f.id === selectedFaction) || factions[0];
    const modalFaction = factions.find(f => f.id === modalFactionId) || currentFaction;

    return (
        <div className="bg-obsidian-surface text-on-surface min-h-screen relative overflow-x-hidden font-body-md text-body-md flex flex-col">
            <Head title="The Awakening - Pilihan Faksi" />

            {/* Ambient Background Glow */}
            <div 
                className="fixed inset-0 z-0 opacity-25 pointer-events-none transition-all duration-700 ease-in-out" 
                style={{ background: `radial-gradient(circle at center 40%, ${currentFaction.colorHex} 0%, transparent 65%)` }}
            />
            <div className="fixed inset-0 z-0 opacity-20 pointer-events-none mix-blend-overlay bg-[#0e0e0e]" />
            
            {/* Header Area */}
            <header className="relative z-10 pt-10 md:pt-14 px-margin-mobile md:px-margin-desktop text-center flex-shrink-0">
                <div className="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-primary-container/10 border border-primary-container/30 text-primary-container text-xs font-bold tracking-widest uppercase mb-3">
                    <Sparkles size={13} className="animate-pulse" />
                    <span>TAHAP 2 DARI 5: KEBANGKITAN</span>
                </div>
                <h1 className="font-display-hero text-2xl md:text-4xl text-white tracking-widest mb-2 font-bold" style={{ fontFamily: "'Playfair Display', serif" }}>
                    THE AWAKENING
                </h1>
                <p className="font-body-md text-xs md:text-sm text-on-surface-variant max-w-xl mx-auto px-4">
                    Tentukan kesetiaan faksi Anda. Pilihan ini memberikan buff pasif permanen dalam ekspedisi perburuan harta karun.
                </p>

                {/* Compare All Factions Button */}
                <div className="mt-3">
                    <button
                        type="button"
                        onClick={() => openDetailModal(selectedFaction)}
                        className="inline-flex items-center gap-1.5 text-xs text-primary-container hover:text-white bg-white/5 hover:bg-white/10 px-3 py-1.5 rounded-full border border-primary-container/20 transition-all cursor-pointer shadow-[0_2px_10px_rgba(0,0,0,0.4)]"
                    >
                        <Info size={14} />
                        <span>Bandingkan & Baca Detail Faksi</span>
                    </button>
                </div>
            </header>

            {/* Faction Selection Carousel */}
            <main className="relative z-10 flex-1 flex flex-col justify-center py-4 my-auto">
                <div 
                    ref={carouselRef}
                    onScroll={handleScroll}
                    className="flex overflow-x-auto snap-x snap-mandatory scroll-smooth no-scrollbar gap-5 px-[7.5vw] md:px-[calc(50vw-200px)] py-4 items-center" 
                >
                    {factions.map((f) => {
                        const isActive = selectedFaction === f.id;
                        const IconComponent = f.icon;

                        return (
                            <article 
                                key={f.id}
                                className={`faction-card snap-center shrink-0 w-[85vw] max-w-[370px] rounded-2xl relative overflow-hidden flex flex-col transition-all duration-300 transform border cursor-pointer ${
                                    isActive 
                                        ? 'scale-100 opacity-100 shadow-[0_12px_40px_rgba(0,0,0,0.8)]' 
                                        : 'scale-95 opacity-60 hover:opacity-85'
                                }`}
                                style={{
                                    borderColor: isActive ? f.colorHex : 'rgba(255,255,255,0.1)',
                                    background: 'linear-gradient(180deg, rgba(30,30,30,0.85) 0%, rgba(15,15,15,0.95) 100%)',
                                    backdropFilter: 'blur(16px)',
                                }}
                                onClick={() => handleSelectFaction(f.id)}
                                data-id={f.id}
                            >
                                {/* Card Header Image & Overlay */}
                                <div className="h-[260px] md:h-[280px] relative overflow-hidden rounded-t-2xl">
                                    <img 
                                        className="w-full h-full object-cover transition-transform duration-700 hover:scale-105" 
                                        src={f.image} 
                                        alt={f.name} 
                                    />
                                    <div className="absolute inset-0 bg-gradient-to-t from-[#0f0f0f] via-transparent to-black/40" />

                                    {/* Top Faction Badge */}
                                    <div className="absolute top-3 left-3 right-3 flex items-center justify-between">
                                        <div 
                                            className="px-2.5 py-1 rounded-md text-[11px] font-bold tracking-wider uppercase flex items-center gap-1.5 shadow-md"
                                            style={{ backgroundColor: `${f.colorHex}25`, color: f.colorHex, border: `1px solid ${f.colorHex}40` }}
                                        >
                                            <IconComponent size={13} />
                                            <span>{f.id.toUpperCase()}</span>
                                        </div>

                                        {/* Quick Info Button on Card */}
                                        <button
                                            type="button"
                                            onClick={(e) => openDetailModal(f.id, e)}
                                            className="p-1.5 rounded-full bg-black/60 hover:bg-black/90 border border-white/20 text-white/80 hover:text-white transition-all shadow-md"
                                            title="Buka Penjelasan Detail"
                                        >
                                            <Info size={16} />
                                        </button>
                                    </div>
                                </div>
                                
                                {/* Card Body */}
                                <div className="p-5 flex flex-col items-center text-center flex-1">
                                    <h2 
                                        className="text-2xl font-bold mb-1 tracking-wide" 
                                        style={{ color: f.colorHex, fontFamily: "'Playfair Display', serif" }}
                                    >
                                        {f.name}
                                    </h2>
                                    <p className="text-xs text-on-surface-variant font-medium tracking-wider mb-3">
                                        {f.sub}
                                    </p>
                                    
                                    {/* Buff Highlight Box */}
                                    <div 
                                        className="w-full px-3 py-2 rounded-xl mb-3 flex items-center justify-center gap-2 border"
                                        style={{ 
                                            backgroundColor: `${f.colorHex}12`, 
                                            borderColor: `${f.colorHex}30` 
                                        }}
                                    >
                                        <Zap size={14} style={{ color: f.colorHex }} />
                                        <span className="text-xs font-bold tracking-wide" style={{ color: f.colorHex }}>
                                            {f.buff}
                                        </span>
                                    </div>

                                    {/* Detail Link Trigger */}
                                    <button
                                        type="button"
                                        onClick={(e) => openDetailModal(f.id, e)}
                                        className="text-[11px] text-on-surface-variant hover:text-white underline decoration-dotted underline-offset-4 mb-4 flex items-center gap-1 transition-colors"
                                    >
                                        <Info size={13} />
                                        <span>Pelajari filosofi & keuntungan faksi</span>
                                    </button>
                                    
                                    {/* Select Faction Action Button */}
                                    <button 
                                        type="button"
                                        onClick={() => {
                                            if (isActive) {
                                                handleConfirm();
                                            } else {
                                                handleSelectFaction(f.id);
                                            }
                                        }}
                                        disabled={loading}
                                        className="w-full py-3.5 rounded-xl font-label-sm text-xs font-bold uppercase tracking-widest transition-all duration-300 flex items-center justify-center gap-2 shadow-lg disabled:opacity-50"
                                        style={{
                                            backgroundColor: isActive ? f.colorHex : 'transparent',
                                            color: isActive ? '#0e0e0e' : f.colorHex,
                                            border: `2px solid ${f.colorHex}`,
                                            boxShadow: isActive ? `0 0 20px ${f.colorHex}40` : 'none',
                                        }}
                                    >
                                        {loading && isActive ? (
                                            <span>MEMPROSES...</span>
                                        ) : isActive ? (
                                            <>
                                                <span>PILIH KESETIAAN INI</span>
                                                <ArrowRight size={15} />
                                            </>
                                        ) : (
                                            <span>KETUK UNTUK MEMILIH</span>
                                        )}
                                    </button>
                                </div>
                            </article>
                        );
                    })}
                </div>

                {/* Mobile Dot Indicators */}
                <div className="flex justify-center items-center gap-2.5 mt-2">
                    {factions.map((f) => (
                        <button 
                            key={f.id}
                            type="button"
                            onClick={() => handleSelectFaction(f.id)}
                            className="h-2 rounded-full transition-all duration-300 focus:outline-none"
                            style={{
                                width: selectedFaction === f.id ? '28px' : '8px',
                                backgroundColor: selectedFaction === f.id ? f.colorHex : '#353534',
                            }}
                            aria-label={`Pilih ${f.name}`}
                        />
                    ))}
                </div>
            </main>

            {/* ============================================================ */}
            {/* POPUP MODAL: DETAIL FACTION EXPLANATION (MOBILE FIRST)        */}
            {/* ============================================================ */}
            {showDetailModal && (
                <div 
                    className="fixed inset-0 z-50 flex items-end md:items-center justify-center p-0 md:p-4 bg-black/80 backdrop-blur-md transition-opacity duration-300"
                    onClick={() => setShowDetailModal(false)}
                >
                    <div 
                        className="w-full md:max-w-lg bg-[#141414] border-t md:border border-white/10 rounded-t-3xl md:rounded-2xl p-6 md:p-8 max-h-[88vh] overflow-y-auto shadow-2xl relative animate-in slide-in-from-bottom-6 duration-300 text-left"
                        onClick={(e) => e.stopPropagation()}
                    >
                        {/* Modal Header */}
                        <div className="flex items-start justify-between pb-4 border-b border-white/10">
                            <div className="flex items-center gap-3">
                                <div 
                                    className="w-10 h-10 rounded-xl flex items-center justify-center shadow-lg"
                                    style={{ backgroundColor: `${modalFaction.colorHex}20`, border: `1px solid ${modalFaction.colorHex}50` }}
                                >
                                    {modalFaction.id === 'nisnas' && <Leaf size={20} style={{ color: modalFaction.colorHex }} />}
                                    {modalFaction.id === 'human' && <Users size={20} style={{ color: modalFaction.colorHex }} />}
                                    {modalFaction.id === 'android' && <Cpu size={20} style={{ color: modalFaction.colorHex }} />}
                                </div>
                                <div>
                                    <h3 
                                        className="text-xl font-bold leading-tight" 
                                        style={{ color: modalFaction.colorHex, fontFamily: "'Playfair Display', serif" }}
                                    >
                                        {modalFaction.name}
                                    </h3>
                                    <p className="text-xs text-on-surface-variant font-medium">{modalFaction.sub}</p>
                                </div>
                            </div>
                            <button 
                                type="button"
                                onClick={() => setShowDetailModal(false)}
                                className="p-2 rounded-full bg-white/5 hover:bg-white/15 text-white/70 hover:text-white transition-colors"
                                aria-label="Tutup Detail"
                            >
                                <X size={18} />
                            </button>
                        </div>

                        {/* Faction Tab Switcher in Modal */}
                        <div className="grid grid-cols-3 gap-2 mt-4 mb-5 p-1 bg-black/40 rounded-xl border border-white/5">
                            {factions.map((f) => (
                                <button
                                    key={f.id}
                                    type="button"
                                    onClick={() => setModalFactionId(f.id)}
                                    className={`py-2 px-1 text-center rounded-lg text-xs font-bold transition-all ${
                                        modalFactionId === f.id 
                                            ? 'text-white shadow-md' 
                                            : 'text-on-surface-variant/60 hover:text-white'
                                    }`}
                                    style={{
                                        backgroundColor: modalFactionId === f.id ? `${f.colorHex}35` : 'transparent',
                                        border: modalFactionId === f.id ? `1px solid ${f.colorHex}60` : '1px solid transparent',
                                    }}
                                >
                                    {f.name.replace('Bangsa ', '')}
                                </button>
                            ))}
                        </div>

                        {/* Title & Philosophy */}
                        <div className="space-y-4 text-xs md:text-sm">
                            <div className="p-3.5 rounded-xl bg-white/[0.03] border border-white/5">
                                <h4 className="text-white font-semibold text-xs tracking-wider uppercase mb-1 flex items-center gap-1.5">
                                    <Sparkles size={13} style={{ color: modalFaction.colorHex }} />
                                    <span>Gelar & Filosofi Faksi</span>
                                </h4>
                                <p className="font-semibold text-sm mb-1.5" style={{ color: modalFaction.colorHex }}>
                                    "{modalFaction.title}"
                                </p>
                                <p className="text-on-surface-variant/90 leading-relaxed text-xs">
                                    {modalFaction.philosophy}
                                </p>
                            </div>

                            {/* Pasif Buffs & Finansial Advantage */}
                            <div className="p-3.5 rounded-xl bg-white/[0.03] border border-white/5">
                                <h4 className="text-white font-semibold text-xs tracking-wider uppercase mb-2 flex items-center gap-1.5">
                                    <Zap size={13} style={{ color: modalFaction.colorHex }} />
                                    <span>Keuntungan Finansial & Buff Pasif</span>
                                </h4>
                                <ul className="space-y-2">
                                    {modalFaction.buffs.map((buff, idx) => (
                                        <li key={idx} className="flex items-start gap-2 text-xs text-on-surface leading-snug">
                                            <Check size={14} className="mt-0.5 shrink-0 stroke-[3]" style={{ color: modalFaction.colorHex }} />
                                            <span>{buff}</span>
                                        </li>
                                    ))}
                                </ul>
                            </div>

                            {/* Gaya Bermain & Rekomendasi */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                                <div className="p-3 rounded-xl bg-white/[0.02] border border-white/5">
                                    <h5 className="text-[11px] font-bold text-on-surface-variant tracking-wider uppercase mb-1 flex items-center gap-1">
                                        <TrendingUp size={12} style={{ color: modalFaction.colorHex }} />
                                        <span>Gaya Bermain</span>
                                    </h5>
                                    <p className="text-xs text-on-surface-variant/90 leading-relaxed">
                                        {modalFaction.gameplay}
                                    </p>
                                </div>
                                <div className="p-3 rounded-xl bg-white/[0.02] border border-white/5">
                                    <h5 className="text-[11px] font-bold text-on-surface-variant tracking-wider uppercase mb-1 flex items-center gap-1">
                                        <Users size={12} style={{ color: modalFaction.colorHex }} />
                                        <span>Sangat Cocok Untuk</span>
                                    </h5>
                                    <p className="text-xs text-on-surface-variant/90 leading-relaxed">
                                        {modalFaction.suitableFor}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Modal Action Buttons */}
                        <div className="mt-6 pt-4 border-t border-white/10 flex gap-3">
                            <button
                                type="button"
                                onClick={() => setShowDetailModal(false)}
                                className="w-1/3 py-3 rounded-xl bg-white/5 hover:bg-white/10 text-on-surface text-xs font-bold transition-colors"
                            >
                                Tutup
                            </button>
                            <button
                                type="button"
                                onClick={() => {
                                    handleSelectFaction(modalFaction.id);
                                    setShowDetailModal(false);
                                    handleConfirm(modalFaction.id);
                                }}
                                disabled={loading}
                                className="w-2/3 py-3 rounded-xl font-label-sm text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg transition-transform hover:scale-[1.02] active:scale-95 disabled:opacity-50"
                                style={{
                                    backgroundColor: modalFaction.colorHex,
                                    color: '#0e0e0e',
                                    boxShadow: `0 0 20px ${modalFaction.colorHex}50`
                                }}
                            >
                                <span>PILIH {modalFaction.name.toUpperCase()}</span>
                                <ArrowRight size={14} />
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
