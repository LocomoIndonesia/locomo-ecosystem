import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { 
    Swords, 
    Compass, 
    Wrench, 
    Sparkles, 
    ArrowRight, 
    Leaf, 
    Users, 
    Cpu, 
    Zap, 
    ShieldCheck, 
    Award,
    CheckCircle2
} from 'lucide-react';

interface PathProps {
    user: {
        id: string;
        name: string;
        faction?: string;
    };
}

export default function Path({ user }: PathProps) {
    const [selectedPath, setSelectedPath] = useState<string>('PENDEKAR');
    const [loading, setLoading] = useState(false);

    const userFaction = (user.faction || 'NISNAS').toUpperCase();

    // Faction Skill Profiles
    const factionSkills: Record<string, {
        name: string;
        themeColor: string;
        icon: any;
        passives: { title: string; desc: string }[];
        specialAbility: { title: string; desc: string };
    }> = {
        NISNAS: {
            name: 'Bangsa Nisnas',
            themeColor: '#50C878',
            icon: Leaf,
            passives: [
                { title: 'Resonansi Alam Minang', desc: '+5% peluang drop Koin Emas & Pusaka Langka di setiap ekspedisi spot Gunung Mas.' },
                { title: 'Berkah Panen Komunitas', desc: 'Konsumsi Koin Perak saat staking misi berkurang 10% pada zona pedesaan/nagari.' },
                { title: 'Intuisi Spiritual', desc: 'Akses alokasi angpao koin per jam dengan multiplier koin ekstra.' }
            ],
            specialAbility: {
                title: 'Sentuhan Kemakmuran',
                desc: 'Mampu memulihkan 15% koin perak yang hangus jika misi ekspedisi mengalami kegagalan teknis.'
            }
        },
        HUMAN: {
            name: 'Bangsa Manusia',
            themeColor: '#4682B4',
            icon: Users,
            passives: [
                { title: 'Kepemimpinan Karavan', desc: '+10% efisiensi bonus emas dari jaringan downline tingkat 1 & tingkat 2.' },
                { title: 'Diplomasi Saudagar', desc: 'Diskon 5% biaya upgrade perlengkapan dan biaya sewa landing page UMKM di Unimart.' },
                { title: 'Solidaritas Warga', desc: 'Akses kuota misi survei merchant lebih banyak untuk membantu digitalisasi warung lokal.' }
            ],
            specialAbility: {
                title: 'Pakta Aliansi Niaga',
                desc: 'Memicu bonus dividen ekstra setiap kali downline karavan berhasil menambang koin emas.'
            }
        },
        ANDROID: {
            name: 'Bangsa Android',
            themeColor: '#DC143C',
            icon: Cpu,
            passives: [
                { title: 'Kalkulasi Arbitrase Cepat', desc: '+2% kecepatan pemrosesan dan efisiensi modal pada Bursa Lelang P2P.' },
                { title: 'Algoritma Runtuhan Presisi', desc: 'Analitik visual pola kombinasi kartu pada mini-game Slot KOA Minang.' },
                { title: 'Otomatisasi Ekspedisi', desc: 'Validasi otomatis penyelesaian misi berbasis AI Sentinel Locomo.' }
            ],
            specialAbility: {
                title: 'Overclock Likuiditas',
                desc: 'Mempercepat pencairan Kuota Emas ke Brankas dengan efisiensi pembakaran perak optimal.'
            }
        }
    };

    const currentFactionSkill = factionSkills[userFaction] || factionSkills.NISNAS;
    const FactionIcon = currentFactionSkill.icon;

    const paths = [
        {
            id: 'PENDEKAR',
            name: 'Pendekar Niaga',
            icon: Swords,
            subtitle: 'Garis Depan & Risiko Tinggi',
            desc: 'Pakar eksplorasi agresif. Memaksimalkan kemenangan jackpot di Slot KOA dan ekspedisi zona berbahaya Gunung Mas.',
            bonus: '+15% Multiplier Jackpot KOA'
        },
        {
            id: 'RANGER',
            name: 'Ranger Penjelajah',
            icon: Compass,
            subtitle: 'Pengintai & Mobilitas Cepat',
            desc: 'Ahli pemetaan rute wisata dan verifikasi spot tersembunyi di pesisir Mandeh dan kawasan Nagari.',
            bonus: '+20% Kecepatan Jelajah Misi'
        },
        {
            id: 'SPESIALIS',
            name: 'Spesialis Karavan',
            icon: Wrench,
            subtitle: 'Manajemen Logistik & Mitra UMKM',
            desc: 'Fokus pada digitalisasi toko fisik, integrasi inventaris Unimart, dan perbekalan alat ekspedisi.',
            bonus: '+10% Komisi Kemitraan UMKM'
        },
        {
            id: 'MENTALIST',
            name: 'Mentalist Strategi',
            icon: Sparkles,
            subtitle: 'Kalkulator Peluang & Edukasi',
            desc: 'Penganalisa pasar koin, perancang taktik jaringan downline, dan mentor kurikulum di WaliKelas.',
            bonus: '+15% Bonus Pengalaman (XP)'
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
        <div className="min-h-screen bg-obsidian-surface text-on-surface flex flex-col items-center justify-start font-body-md relative overflow-x-hidden pb-12">
            <Head title="Kemampuan Faksi & Jalur Keahlian - PITICOIN.ID" />

            {/* Ambient Background Glow */}
            <div 
                className="fixed inset-0 z-0 opacity-20 pointer-events-none transition-all duration-700" 
                style={{ background: `radial-gradient(circle at top center, ${currentFactionSkill.themeColor} 0%, transparent 60%)` }}
            />
            <div className="fixed inset-0 z-0 opacity-15 pointer-events-none mix-blend-overlay bg-[#111111]" />

            {/* Header Section */}
            <header className="w-full max-w-4xl px-margin-mobile md:px-margin-desktop pt-10 md:pt-14 text-center z-10">
                <div className="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/5 border border-white/10 text-xs font-bold tracking-widest uppercase mb-3 text-on-surface-variant">
                    <Award size={13} className="text-primary-container" />
                    <span>TAHAP 3 DARI 5: KEMAMPUAN & JALUR KEAHLIAN</span>
                </div>
                <h1 className="font-display-hero text-2xl md:text-4xl text-primary-container mb-2 font-bold tracking-wide" style={{ fontFamily: "'Playfair Display', serif" }}>
                    Keahlian & Jalur Perjuangan
                </h1>
                <p className="font-body-md text-xs md:text-sm text-on-surface-variant max-w-2xl mx-auto">
                    Karakter Anda telah dibangkitkan. Pelajari kemampuan bawaan faksi Anda dan tentukan spesialisasi peran dalam ekspedisi Gunung Mas.
                </p>
            </header>

            <main className="w-full max-w-4xl px-margin-mobile md:px-margin-desktop z-10 mt-6 space-y-6">
                {/* 1. FACTION SKILL SHOWCASE BANNER */}
                <section 
                    className="rounded-2xl p-5 md:p-6 relative overflow-hidden border backdrop-blur-xl"
                    style={{
                        backgroundColor: 'rgba(20, 20, 20, 0.85)',
                        borderColor: `${currentFactionSkill.themeColor}50`,
                        boxShadow: `0 8px 30px rgba(0,0,0,0.6), inset 0 1px 1px rgba(255,255,255,0.1)`
                    }}
                >
                    {/* Faction Header Badge */}
                    <div className="flex items-center justify-between border-b border-white/10 pb-4 mb-4">
                        <div className="flex items-center gap-3">
                            <div 
                                className="w-11 h-11 rounded-xl flex items-center justify-center shadow-lg"
                                style={{ backgroundColor: `${currentFactionSkill.themeColor}20`, border: `1px solid ${currentFactionSkill.themeColor}60` }}
                            >
                                <FactionIcon size={22} style={{ color: currentFactionSkill.themeColor }} />
                            </div>
                            <div>
                                <span className="text-[10px] uppercase font-bold tracking-widest text-on-surface-variant">
                                    FAKSI PILIHAN ANDA
                                </span>
                                <h2 className="text-lg md:text-xl font-bold" style={{ color: currentFactionSkill.themeColor, fontFamily: "'Playfair Display', serif" }}>
                                    {currentFactionSkill.name}
                                </h2>
                            </div>
                        </div>

                        <div className="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs font-semibold text-emerald-400">
                            <ShieldCheck size={14} />
                            <span>Karakter Aktif</span>
                        </div>
                    </div>

                    {/* Passive Skills List */}
                    <div className="space-y-2.5 mb-4">
                        <h3 className="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-1.5">
                            <Zap size={14} style={{ color: currentFactionSkill.themeColor }} />
                            <span>Kemampuan Pasif Bawaan Faksi:</span>
                        </h3>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-2.5">
                            {currentFactionSkill.passives.map((p, idx) => (
                                <div key={idx} className="p-3 rounded-xl bg-black/40 border border-white/5 space-y-1">
                                    <div className="text-xs font-bold text-white flex items-center gap-1.5">
                                        <CheckCircle2 size={13} style={{ color: currentFactionSkill.themeColor }} />
                                        <span>{p.title}</span>
                                    </div>
                                    <p className="text-[11px] text-on-surface-variant leading-relaxed">
                                        {p.desc}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Special Ability Banner */}
                    <div 
                        className="p-3 rounded-xl border flex items-center gap-3"
                        style={{ backgroundColor: `${currentFactionSkill.themeColor}10`, borderColor: `${currentFactionSkill.themeColor}30` }}
                    >
                        <Sparkles size={18} className="shrink-0" style={{ color: currentFactionSkill.themeColor }} />
                        <div className="text-xs">
                            <span className="font-bold text-white">Keahlian Khusus: {currentFactionSkill.specialAbility.title}</span>
                            <p className="text-on-surface-variant text-[11px] mt-0.5">{currentFactionSkill.specialAbility.desc}</p>
                        </div>
                    </div>
                </section>

                {/* 2. CHOOSE ROLE CLASS / PATH */}
                <section className="space-y-3">
                    <div className="text-left">
                        <h2 className="text-lg font-bold text-white tracking-wide" style={{ fontFamily: "'Playfair Display', serif" }}>
                            Pilih Spesialisasi Peran (Class Role)
                        </h2>
                        <p className="text-xs text-on-surface-variant">
                            Tentukan gaya bermain utama Anda dalam ekosistem perburuan harta karun PitiCoin.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        {paths.map((p) => {
                            const isSelected = selectedPath === p.id;
                            const PathIcon = p.icon;

                            return (
                                <div
                                    key={p.id}
                                    onClick={() => setSelectedPath(p.id)}
                                    className={`p-4 md:p-5 rounded-2xl relative overflow-hidden cursor-pointer transition-all duration-300 border flex flex-col justify-between ${
                                        isSelected 
                                            ? 'border-primary-container bg-primary-container/[0.08] shadow-[0_0_25px_rgba(255,215,0,0.25)] -translate-y-0.5' 
                                            : 'border-white/10 bg-[#161616]/80 hover:border-white/20 hover:bg-[#1a1a1a]'
                                    }`}
                                >
                                    <div>
                                        <div className="flex items-center justify-between mb-3">
                                            <div 
                                                className={`w-10 h-10 rounded-xl flex items-center justify-center transition-colors ${
                                                    isSelected 
                                                        ? 'bg-primary-container text-obsidian-surface' 
                                                        : 'bg-white/5 text-primary-container'
                                                }`}
                                            >
                                                <PathIcon size={20} />
                                            </div>

                                            <span 
                                                className={`text-[10px] font-bold tracking-wider px-2.5 py-1 rounded-full border ${
                                                    isSelected 
                                                        ? 'bg-primary-container/20 text-primary-container border-primary-container/40' 
                                                        : 'bg-white/5 text-on-surface-variant border-white/10'
                                                }`}
                                            >
                                                {p.bonus}
                                            </span>
                                        </div>

                                        <h3 
                                            className={`text-base md:text-lg font-bold mb-0.5 ${
                                                isSelected ? 'text-primary-container' : 'text-white'
                                            }`}
                                            style={{ fontFamily: "'Playfair Display', serif" }}
                                        >
                                            {p.name}
                                        </h3>
                                        <p className="text-[11px] font-semibold text-on-surface-variant/80 uppercase tracking-wider mb-2">
                                            {p.subtitle}
                                        </p>
                                        <p className="text-xs text-on-surface-variant leading-relaxed">
                                            {p.desc}
                                        </p>
                                    </div>

                                    <div className="mt-4 pt-3 border-t border-white/5 flex items-center justify-between text-xs">
                                        <span className={isSelected ? 'text-primary-container font-semibold' : 'text-on-surface-variant'}>
                                            {isSelected ? '✓ Terpilih Sebagai Peran Utama' : 'Ketuk untuk memilih'}
                                        </span>
                                        <div 
                                            className={`w-4 h-4 rounded-full border flex items-center justify-center transition-all ${
                                                isSelected 
                                                    ? 'border-primary-container bg-primary-container' 
                                                    : 'border-white/20'
                                            }`}
                                        >
                                            {isSelected && <div className="w-1.5 h-1.5 rounded-full bg-black" />}
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </section>

                {/* 3. CONFIRM & PROCEED TO PLEDGE BUTTON */}
                <div className="pt-4 flex flex-col items-center gap-3">
                    <button
                        type="button"
                        onClick={handleConfirm}
                        disabled={loading || !selectedPath}
                        className="w-full max-w-md py-4 rounded-xl bg-primary-container text-obsidian-surface font-label-sm text-sm font-bold uppercase tracking-widest shadow-[0_0_20px_rgba(255,215,0,0.3)] hover:shadow-[0_0_30px_rgba(255,215,0,0.5)] transition-all hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2.5 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span>{loading ? 'MEMPROSES SPESIALISASI...' : 'LANJUT PILIH PAKET STARTER KIT'}</span>
                        <ArrowRight size={16} />
                    </button>
                    <p className="text-[11px] text-on-surface-variant/70 text-center">
                        Setelah ini, Anda akan memilih paket bekal awal ekspedisi dan menandatangani kontrak kemitraan saudagar.
                    </p>
                </div>
            </main>
        </div>
    );
}
