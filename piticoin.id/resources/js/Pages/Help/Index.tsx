import React, { useState, useMemo } from 'react';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Search,
    HelpCircle,
    ChevronDown,
    ChevronUp,
    MessageSquare,
    PhoneCall,
    ShieldAlert,
    ExternalLink,
    Compass,
    Users,
    Wallet,
    Key,
    Sparkles,
} from 'lucide-react';
import CookieConsentBanner from '@/Components/CookieConsentBanner';

interface FaqItem {
    q: string;
    a: string;
}

interface FaqCategory {
    category: string;
    items: FaqItem[];
}

interface HelpIndexProps {
    faqs: FaqCategory[];
    officialWhatsApp: string;
    supportEmail: string;
}

export default function HelpIndex({ faqs, officialWhatsApp, supportEmail }: HelpIndexProps) {
    const [searchQuery, setSearchQuery] = useState('');
    const [activeCategory, setActiveCategory] = useState<string>('Semua');
    const [expandedIndexes, setExpandedIndexes] = useState<Record<string, boolean>>({
        '0-0': true, // Open the first item by default
    });

    const categoryIcons: Record<string, React.ReactNode> = {
        'Perburuan Harta Karun & Staking': <Compass size={16} className="text-[#ffd700]" />,
        'Ekspansi Jaringan & Karavan': <Users size={16} className="text-[#ffd700]" />,
        'Dompet, Deposit & Pencairan': <Wallet size={16} className="text-[#ffd700]" />,
        'Akun, Lisensi & Keamanan': <Key size={16} className="text-[#ffd700]" />,
    };

    const toggleAccordion = (catIdx: number, itemIdx: number) => {
        const key = `${catIdx}-${itemIdx}`;
        setExpandedIndexes((prev) => ({
            ...prev,
            [key]: !prev[key],
        }));
    };

    // Filter FAQs by search query and category
    const filteredCategories = useMemo(() => {
        const q = searchQuery.toLowerCase().trim();

        return faqs
            .map((cat, catIdx) => {
                if (activeCategory !== 'Semua' && cat.category !== activeCategory) {
                    return null;
                }

                const matchedItems = cat.items.filter(
                    (item) =>
                        item.q.toLowerCase().includes(q) ||
                        item.a.toLowerCase().includes(q)
                );

                if (matchedItems.length === 0) return null;

                return {
                    ...cat,
                    originalCatIdx: catIdx,
                    items: matchedItems,
                };
            })
            .filter(Boolean) as (FaqCategory & { originalCatIdx: number })[];
    }, [faqs, searchQuery, activeCategory]);

    const categoriesList = ['Semua', ...faqs.map((f) => f.category)];

    return (
        <div className="min-h-screen bg-[#131313] text-[#e5e2e1] font-sans antialiased selection:bg-[#ffd700] selection:text-black">
            <Head title="Pusat Bantuan & Panduan Saudagar" />

            {/* Sticky Navigation Bar */}
            <nav className="sticky top-0 z-40 backdrop-blur-md bg-[#131313]/90 border-b border-white/10 px-4 sm:px-8 py-3.5 flex items-center justify-between">
                <div className="flex items-center gap-3">
                    <Link
                        href="/dashboard"
                        className="p-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white transition-all border border-white/10"
                    >
                        <ArrowLeft size={18} />
                    </Link>
                    <div>
                        <span className="text-xs tracking-wider uppercase text-[#ffd700] font-semibold font-mono">
                            PUSAT PANDUAN & FAQ
                        </span>
                        <h1 className="text-base sm:text-lg font-bold text-white leading-tight">
                            Pusat Bantuan Saudagar
                        </h1>
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <Link
                        href="/terms"
                        className="text-xs px-3 py-1.5 rounded-lg border border-white/10 hover:border-[#ffd700]/50 text-gray-300 hover:text-[#ffd700] transition-colors hidden sm:inline-block"
                    >
                        Syarat & Ketentuan
                    </Link>
                    <Link
                        href="/privacy"
                        className="text-xs px-3 py-1.5 rounded-lg border border-white/10 hover:border-[#ffd700]/50 text-gray-300 hover:text-[#ffd700] transition-colors hidden sm:inline-block"
                    >
                        Kebijakan Privasi
                    </Link>
                    <Link
                        href="/dashboard"
                        className="text-xs px-3 py-1.5 rounded-lg bg-yellow-500/10 border border-yellow-500/30 text-[#ffd700] hover:bg-yellow-500/20 transition-colors"
                    >
                        Markas Utama
                    </Link>
                </div>
            </nav>

            {/* Main Content Container */}
            <main className="max-w-4xl mx-auto px-4 sm:px-6 py-10 space-y-10">
                {/* Hero Header with Search Bar */}
                <header className="text-center space-y-5 pt-2 pb-4">
                    <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-yellow-500/10 border border-yellow-500/30 text-[#ffd700] text-xs font-semibold">
                        <Sparkles size={14} />
                        <span>Basis Pengetahuan & Panduan Ekspedisi</span>
                    </div>
                    <h1 className="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Bagaimana Kami Dapat Membantu Petualangan Anda?
                    </h1>
                    <p className="text-sm text-gray-400 max-w-xl mx-auto leading-relaxed">
                        Cari jawaban seputar staking perak, penarikan koin emas, pembentukan karavan, dan integrasi mitra UMKM.
                    </p>

                    {/* Interactive Search Bar */}
                    <div className="relative max-w-2xl mx-auto pt-2">
                        <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" size={18} />
                        <input
                            type="text"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            placeholder="Cari kata kunci (contoh: staking, escrow, koin emas, referral, withdrawal)..."
                            className="w-full pl-11 pr-4 py-3.5 rounded-2xl bg-black/50 border border-white/15 text-white placeholder-gray-500 focus:outline-none focus:border-[#ffd700] focus:ring-1 focus:ring-[#ffd700] transition-all text-sm shadow-inner"
                        />
                        {searchQuery && (
                            <button
                                onClick={() => setSearchQuery('')}
                                className="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-gray-400 hover:text-white px-2 py-1 rounded-md bg-white/10"
                            >
                                Reset
                            </button>
                        )}
                    </div>
                </header>

                {/* Category Selector Chips */}
                <div className="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                    {categoriesList.map((category) => (
                        <button
                            key={category}
                            onClick={() => setActiveCategory(category)}
                            className={`px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all ${
                                activeCategory === category
                                    ? 'bg-[#ffd700] text-black shadow-md bloom-gold'
                                    : 'bg-white/5 border border-white/10 text-gray-400 hover:text-white hover:bg-white/10'
                            }`}
                        >
                            {category}
                        </button>
                    ))}
                </div>

                {/* FAQ Accordion List */}
                <div className="space-y-6">
                    {filteredCategories.length === 0 ? (
                        <div className="text-center py-16 glass-panel rounded-2xl border border-white/10 space-y-3">
                            <HelpCircle size={40} className="mx-auto text-gray-500" />
                            <h3 className="text-base font-semibold text-white">Tidak ada artikel bantuan yang cocok</h3>
                            <p className="text-xs text-gray-400">
                                Coba gunakan kata kunci pencarian yang lebih umum atau hubungi CS langsung.
                            </p>
                        </div>
                    ) : (
                        filteredCategories.map((cat) => (
                            <div key={cat.category} className="space-y-3">
                                <h3 className="text-xs font-mono font-semibold uppercase tracking-wider text-gray-400 flex items-center gap-2 px-1">
                                    {categoryIcons[cat.category] || <HelpCircle size={15} />}
                                    <span>{cat.category}</span>
                                </h3>

                                <div className="space-y-2.5">
                                    {cat.items.map((item, itemIdx) => {
                                        const key = `${cat.originalCatIdx}-${itemIdx}`;
                                        const isOpen = !!expandedIndexes[key];

                                        return (
                                            <div
                                                key={item.q}
                                                className={`glass-panel rounded-2xl border transition-all overflow-hidden ${
                                                    isOpen
                                                        ? 'border-yellow-500/40 bg-[#1A1A1A]/80 shadow-lg'
                                                        : 'border-white/10 bg-[#1A1A1A]/40 hover:border-white/20'
                                                }`}
                                            >
                                                <button
                                                    onClick={() => toggleAccordion(cat.originalCatIdx, itemIdx)}
                                                    className="w-full p-4 sm:p-5 flex items-center justify-between text-left gap-4"
                                                >
                                                    <span className="font-semibold text-sm text-white">
                                                        {item.q}
                                                    </span>
                                                    <div className="p-1 rounded-lg bg-white/5 text-gray-400 shrink-0">
                                                        {isOpen ? <ChevronUp size={16} /> : <ChevronDown size={16} />}
                                                    </div>
                                                </button>

                                                {isOpen && (
                                                    <div className="px-4 sm:px-5 pb-5 pt-1 text-xs text-gray-300 leading-relaxed border-t border-white/5">
                                                        <p>{item.a}</p>
                                                    </div>
                                                )}
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        ))
                    )}
                </div>

                {/* Direct Escalation Support Cards */}
                <div className="pt-6 border-t border-white/10">
                    <div className="text-center mb-6 space-y-1">
                        <h3 className="text-lg font-bold text-white">Masih Butuh Bantuan Tambahan?</h3>
                        <p className="text-xs text-gray-400">Pilih saluran komunikasi resmi kami untuk penanganan langsung.</p>
                    </div>

                    <div className="grid sm:grid-cols-2 gap-4">
                        {/* Consultation Escalation */}
                        <div className="glass-panel p-5 rounded-2xl border border-yellow-500/30 bg-yellow-500/[0.02] flex flex-col justify-between space-y-4">
                            <div className="space-y-2">
                                <div className="flex items-center gap-2 text-[#ffd700]">
                                    <MessageSquare size={18} />
                                    <h4 className="font-bold text-sm">Konsultasi Saudagar Pembina</h4>
                                </div>
                                <p className="text-xs text-gray-300 leading-relaxed">
                                    Ajukan pertanyaan teknis seputar strategi perburuan dan panduan karavan langsung ke Saudagar Upline atau Admin Root melalui chat internal sistem.
                                </p>
                            </div>
                            <Link
                                href="/dashboard"
                                className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-yellow-500/15 hover:bg-yellow-500/25 border border-yellow-500/40 text-[#ffd700] text-xs font-semibold transition-all w-full text-center"
                            >
                                <MessageSquare size={14} />
                                <span>Buka Panel Konsultasi</span>
                            </Link>
                        </div>

                        {/* WhatsApp Support Escalation */}
                        <div className="glass-panel p-5 rounded-2xl border border-emerald-500/30 bg-emerald-500/[0.02] flex flex-col justify-between space-y-4">
                            <div className="space-y-2">
                                <div className="flex items-center gap-2 text-emerald-400">
                                    <PhoneCall size={18} />
                                    <h4 className="font-bold text-sm">WhatsApp Support Resmi</h4>
                                </div>
                                <p className="text-xs text-gray-300 leading-relaxed">
                                    Untuk kendala transaksi darurat, verifikasi identitas rekening, atau pelaporan kecurangan akun, hubungi Helpdesk Resmi PitiCoin melalui WhatsApp.
                                </p>
                            </div>
                            <a
                                href={`https://wa.me/${officialWhatsApp}?text=Halo%20Admin%20PitiCoin,%20saya%20membutuhkan%20bantuan%20mengenai%20akun%20saya.`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 border border-emerald-500/40 text-emerald-400 text-xs font-semibold transition-all w-full text-center"
                            >
                                <ExternalLink size={14} />
                                <span>Hubungi WhatsApp CS</span>
                            </a>
                        </div>
                    </div>
                </div>

                {/* Footer Quick Action */}
                <footer className="pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-400">
                    <p>© 2026 PitiCoin.id — Bantuan Terpadu Ekosistem Gunung Mas.</p>
                    <div className="flex items-center gap-4">
                        <Link href="/terms" className="hover:text-[#ffd700] transition-colors">
                            Syarat & Ketentuan
                        </Link>
                        <span>•</span>
                        <Link href="/privacy" className="hover:text-[#ffd700] transition-colors">
                            Kebijakan Privasi
                        </Link>
                    </div>
                </footer>
            </main>

            <CookieConsentBanner />
        </div>
    );
}
