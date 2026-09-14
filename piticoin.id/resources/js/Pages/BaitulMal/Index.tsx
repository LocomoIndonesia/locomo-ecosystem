import { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { 
    Heart, 
    Sparkles, 
    ShieldCheck, 
    ArrowLeft, 
    Coins, 
    Users, 
    Send, 
    CheckCircle, 
    HelpCircle, 
    Calendar,
    ChevronDown,
    ChevronUp,
    Lock
} from 'lucide-react';
import CookieConsentBanner from '@/Components/CookieConsentBanner';

interface Donation {
    id: string;
    amount_gold: number;
    masked_donor_name: string;
    masked_donor_phone: string;
    prayer_message: string | null;
    is_anonymous: boolean;
    aamiin_count: number;
    created_at: string;
}

interface Distribution {
    id: string;
    masked_beneficiary_name: string;
    beneficiary_category: string;
    amount_gold: number;
    distribution_note: string | null;
    disbursed_at: string;
}

interface Period {
    id: string;
    period_name: string;
    distribution_date: string;
    total_gold_distributed: number;
    beneficiaries_count: number;
    status: string;
    notes: string | null;
    distributions: Distribution[];
}

interface BaitulMalProps {
    user: {
        id: string;
        name: string;
        whatsapp: string;
    } | null;
    wallet: {
        silver_balance: number;
        gold_balance: number;
    } | null;
    pool: {
        name: string;
        total_collected_gold: number;
        active_balance_gold: number;
        total_distributed_gold: number;
        total_donations_count: number;
        total_beneficiaries_count: number;
    };
    recent_donations: Donation[];
    periods: Period[];
}

export default function BaitulMalIndex({
    user,
    wallet,
    pool,
    recent_donations,
    periods,
}: BaitulMalProps) {
    const [donations, setDonations] = useState<Donation[]>(recent_donations);
    const [aamiinLoading, setAamiinLoading] = useState<string | null>(null);
    const [expandedPeriodId, setExpandedPeriodId] = useState<string | null>(periods[0]?.id || null);

    const { data, setData, post, processing, errors, reset, wasSuccessful } = useForm({
        amount_gold: 100,
        prayer_message: '',
        is_anonymous: false,
    });

    const presetAmounts = [50, 100, 250, 500, 1000, 5000];

    const handleSubmitDonation = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('baitul-mal.donate'), {
            onSuccess: () => {
                reset('prayer_message');
            },
        });
    };

    const handleAamiin = async (donationId: string) => {
        if (aamiinLoading) return;
        setAamiinLoading(donationId);
        try {
            const res = await fetch(route('baitul-mal.aamiin', donationId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                },
            });
            const result = await res.json();
            if (result.success) {
                setDonations(prev =>
                    prev.map(d => (d.id === donationId ? { ...d, aamiin_count: result.aamiin_count } : d))
                );
            }
        } catch (err) {
            console.error('Failed to aamiin', err);
        } finally {
            setAamiinLoading(null);
        }
    };

    const getCategoryLabel = (cat: string) => {
        switch (cat) {
            case 'PENJELAJAH_PEMULA':
                return { label: 'Penjelajah Pemula', color: 'text-amber-400 bg-amber-400/10 border-amber-400/30' };
            case 'DHUAFA_MITRA':
                return { label: 'Dhuafa Binaan Saudagar', color: 'text-emerald-400 bg-emerald-400/10 border-emerald-400/30' };
            case 'DANA_DARURAT':
                return { label: 'Bantuan Darurat Ekspedisi', color: 'text-rose-400 bg-rose-400/10 border-rose-400/30' };
            default:
                return { label: cat, color: 'text-blue-400 bg-blue-400/10 border-blue-400/30' };
        }
    };

    return (
        <div className="min-h-screen bg-[#0e0e11] text-[#e5e2e1] font-sans relative pb-28">
            <Head title="Baitul Mal & Kas Sosial Koin Emas - PitiCoin.id" />

            {/* Ambient Background Glows */}
            <div className="fixed top-0 left-1/2 -translate-x-1/2 w-[700px] h-[350px] bg-gradient-to-b from-amber-500/10 to-transparent blur-[120px] pointer-events-none z-0"></div>
            <div className="fixed bottom-0 right-0 w-[500px] h-[300px] bg-emerald-500/5 blur-[100px] pointer-events-none z-0"></div>

            {/* Top Navigation Bar */}
            <header className="sticky top-0 z-40 bg-[#131318]/90 backdrop-blur-xl border-b border-white/10 px-4 sm:px-8 py-3.5 flex items-center justify-between shadow-2xl">
                <div className="flex items-center gap-3">
                    <Link
                        href={route('dashboard')}
                        className="flex items-center justify-center w-9 h-9 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-white/80 hover:text-white transition-all active:scale-95"
                    >
                        <ArrowLeft size={18} />
                    </Link>
                    <div>
                        <div className="flex items-center gap-2">
                            <span className="text-amber-400 font-bold text-xs uppercase tracking-widest font-mono">Baitul Mal</span>
                            <span className="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">
                                UU PDP Terverifikasi
                            </span>
                        </div>
                        <h1 className="text-base sm:text-lg font-bold text-white tracking-wide" style={{ fontFamily: "'Playfair Display', serif" }}>
                            Kas Sosial & Sedekah Koin Emas
                        </h1>
                    </div>
                </div>

                <div className="flex items-center gap-3">
                    {wallet && (
                        <div className="bg-amber-400/10 border border-amber-400/30 rounded-xl px-3 py-1.5 flex items-center gap-2 shadow-[0_0_15px_rgba(255,215,0,0.15)]">
                            <Coins size={15} className="text-amber-400" />
                            <div className="text-right">
                                <span className="text-[9px] uppercase tracking-wider text-amber-200/70 block leading-none">Saldo Emas Anda</span>
                                <span className="text-xs sm:text-sm font-bold text-amber-400 font-mono">
                                    {Number(wallet.gold_balance).toLocaleString('id-ID')} Emas
                                </span>
                            </div>
                        </div>
                    )}
                </div>
            </header>

            <main className="max-w-6xl mx-auto px-4 sm:px-6 pt-6 relative z-10 space-y-8">
                
                {/* Hero Banner with Spiritual & Ethical Mission */}
                <section className="relative rounded-3xl overflow-hidden border border-amber-500/30 bg-gradient-to-br from-[#1c1b17] via-[#141419] to-[#0d1612] p-6 sm:p-8 shadow-2xl">
                    <div className="relative z-10 max-w-2xl space-y-3">
                        <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400/15 border border-amber-400/30 text-amber-300 text-xs font-semibold">
                            <Sparkles size={13} />
                            <span>Penyucian Harta Ekspedisi & Pemberdayaan Mustahik</span>
                        </div>
                        <h2 className="text-2xl sm:text-3xl font-bold text-white leading-tight" style={{ fontFamily: "'Playfair Display', serif" }}>
                            Transparansi Kas Sosial, Perlindungan Martabat Donatur & Mustahik
                        </h2>
                        <p className="text-xs sm:text-sm text-gray-400 leading-relaxed">
                            Setiap keping Koin Emas yang Anda sedekahkan disalurkan langsung secara berkala kepada penjelajah pemula yang membutuhkan modal, warga binaan Saudagar, dan bantuan darurat dengan prinsip <strong>Zero-Discrepancy Ledger</strong>.
                        </p>
                    </div>

                    {/* Subtle decorative heart watermarks */}
                    <Heart className="absolute -right-6 -bottom-6 w-48 h-48 text-amber-500/5 rotate-12 pointer-events-none" />
                </section>

                {/* 1. FINANCIAL TRANSPARENCY COUNTERS */}
                <section className="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <div className="bg-[#16161d]/80 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/10 hover:border-amber-400/30 transition-all shadow-lg">
                        <div className="flex items-center justify-between text-gray-400 mb-2">
                            <span className="text-[10px] sm:text-xs font-medium uppercase tracking-wider">Total Kas Terkumpul</span>
                            <Coins size={16} className="text-amber-400" />
                        </div>
                        <div className="text-lg sm:text-2xl font-bold text-amber-300 font-mono">
                            {Number(pool.total_collected_gold).toLocaleString('id-ID')}
                        </div>
                        <span className="text-[10px] text-gray-500 mt-1 block">Akumulasi Koin Emas All-Time</span>
                    </div>

                    <div className="bg-[#16161d]/80 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-amber-400/40 shadow-[0_0_20px_rgba(255,215,0,0.1)]">
                        <div className="flex items-center justify-between text-amber-200 mb-2">
                            <span className="text-[10px] sm:text-xs font-semibold uppercase tracking-wider">Kas Siap Salur</span>
                            <Sparkles size={16} className="text-amber-400 animate-pulse" />
                        </div>
                        <div className="text-lg sm:text-2xl font-bold text-amber-400 font-mono">
                            {Number(pool.active_balance_gold).toLocaleString('id-ID')}
                        </div>
                        <span className="text-[10px] text-emerald-400/80 mt-1 block">Saldo Aktif di Kas Sosial</span>
                    </div>

                    <div className="bg-[#16161d]/80 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/10 hover:border-emerald-400/30 transition-all shadow-lg">
                        <div className="flex items-center justify-between text-gray-400 mb-2">
                            <span className="text-[10px] sm:text-xs font-medium uppercase tracking-wider">Total Tersalurkan</span>
                            <CheckCircle size={16} className="text-emerald-400" />
                        </div>
                        <div className="text-lg sm:text-2xl font-bold text-emerald-400 font-mono">
                            {Number(pool.total_distributed_gold).toLocaleString('id-ID')}
                        </div>
                        <span className="text-[10px] text-gray-500 mt-1 block">Telah Dinikmati Mustahik</span>
                    </div>

                    <div className="bg-[#16161d]/80 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/10 hover:border-blue-400/30 transition-all shadow-lg">
                        <div className="flex items-center justify-between text-gray-400 mb-2">
                            <span className="text-[10px] sm:text-xs font-medium uppercase tracking-wider">Penerima Manfaat</span>
                            <Users size={16} className="text-blue-400" />
                        </div>
                        <div className="text-lg sm:text-2xl font-bold text-blue-300 font-mono">
                            {Number(pool.total_beneficiaries_count).toLocaleString('id-ID')} Orang
                        </div>
                        <span className="text-[10px] text-gray-500 mt-1 block">Dari {pool.total_donations_count} Transaksi Sedekah</span>
                    </div>
                </section>

                {/* 2. DUAL COLUMN: DONATION FORM & WALL OF MUHSININ */}
                <section className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    
                    {/* Left: Donation Form */}
                    <div className="lg:col-span-5 bg-[#16161e] border border-amber-500/25 rounded-3xl p-6 shadow-xl relative overflow-hidden">
                        <div className="flex items-center gap-2 mb-4">
                            <Heart size={18} className="text-rose-400" />
                            <h3 className="font-bold text-lg text-white" style={{ fontFamily: "'Playfair Display', serif" }}>
                                Salurkan Sedekah Koin Emas
                            </h3>
                        </div>

                        {wasSuccessful && (
                            <div className="mb-4 p-3.5 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2">
                                <CheckCircle size={16} className="shrink-0 text-emerald-400" />
                                <span>Alhamdulillah! Sedekah Anda berhasil disalurkan dan tercatat di buku besar kas sosial.</span>
                            </div>
                        )}

                        {(errors as any).error && (
                            <div className="mb-4 p-3.5 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs">
                                {(errors as any).error}
                            </div>
                        )}

                        <form onSubmit={handleSubmitDonation} className="space-y-4">
                            {/* Preset Nominal Grid */}
                            <div>
                                <label className="block text-xs font-medium text-gray-300 mb-2">
                                    Pilih Nominal Koin Emas:
                                </label>
                                <div className="grid grid-cols-3 gap-2">
                                    {presetAmounts.map((amt) => (
                                        <button
                                            key={amt}
                                            type="button"
                                            onClick={() => setData('amount_gold', amt)}
                                            className={`py-2 px-3 rounded-xl font-mono text-xs font-bold border transition-all ${
                                                data.amount_gold === amt
                                                    ? 'bg-amber-400 text-black border-amber-400 shadow-[0_0_12px_rgba(255,215,0,0.4)] scale-[1.02]'
                                                    : 'bg-white/5 border-white/10 text-gray-300 hover:bg-white/10'
                                            }`}
                                        >
                                            {amt.toLocaleString('id-ID')} Emas
                                        </button>
                                    ))}
                                </div>
                            </div>

                            {/* Custom Input */}
                            <div>
                                <label className="block text-xs font-medium text-gray-300 mb-1">
                                    Atau Nominal Kustom:
                                </label>
                                <div className="relative">
                                    <input
                                        type="number"
                                        min={1}
                                        value={data.amount_gold}
                                        onChange={(e) => setData('amount_gold', parseInt(e.target.value) || 0)}
                                        className="w-full bg-[#0d0d11] border border-white/15 rounded-xl px-4 py-2.5 text-sm font-mono text-amber-400 focus:border-amber-400 focus:outline-none pr-16"
                                        placeholder="Masukkan nominal emas..."
                                    />
                                    <span className="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-mono text-gray-400">
                                        Emas
                                    </span>
                                </div>
                            </div>

                            {/* Prayer Message */}
                            <div>
                                <label className="block text-xs font-medium text-gray-300 mb-1">
                                    Untaian Doa & Harapan (Ditampilkan di Ticker):
                                </label>
                                <textarea
                                    rows={3}
                                    maxLength={250}
                                    value={data.prayer_message}
                                    onChange={(e) => setData('prayer_message', e.target.value)}
                                    className="w-full bg-[#0d0d11] border border-white/15 rounded-xl p-3 text-xs text-gray-200 placeholder-gray-500 focus:border-amber-400 focus:outline-none resize-none leading-relaxed"
                                    placeholder="Contoh: Semoga berkah untuk kelancaran ekspedisi kita semua, sehat selalu keluarga..."
                                />
                            </div>

                            {/* Masking & Privacy Option */}
                            <div className="p-3.5 rounded-2xl bg-white/[0.03] border border-white/10 space-y-2">
                                <label className="flex items-center gap-2.5 cursor-pointer text-xs">
                                    <input
                                        type="checkbox"
                                        checked={data.is_anonymous}
                                        onChange={(e) => setData('is_anonymous', e.target.checked)}
                                        className="rounded bg-black border-white/20 text-amber-400 focus:ring-0 w-4 h-4 cursor-pointer"
                                    />
                                    <span className="font-semibold text-gray-200">Sembunyikan Penuh sebagai "Hamba Allah"</span>
                                </label>
                                <p className="text-[11px] text-gray-400 leading-normal pl-6">
                                    Jika tidak dicentang, nama Anda tetap disamarkan otomatis (contoh: <code>Ah*** Pr*****</code>) dan nomor WhatsApp disembunyikan demi privasi.
                                </p>
                            </div>

                            {/* Submit Button */}
                            <button
                                type="submit"
                                disabled={processing || !data.amount_gold || data.amount_gold < 1}
                                className="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-500 via-amber-400 to-amber-300 text-black font-bold text-xs uppercase tracking-wider hover:brightness-110 active:scale-95 transition-all shadow-[0_0_20px_rgba(255,215,0,0.3)] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            >
                                <Send size={15} />
                                <span>{processing ? 'Memproses Sedekah...' : `Ikhlaskan ${data.amount_gold.toLocaleString('id-ID')} Koin Emas`}</span>
                            </button>
                        </form>
                    </div>

                    {/* Right: Wall of Muhsinin (Live Feed Donatur) */}
                    <div className="lg:col-span-7 bg-[#16161e] border border-white/10 rounded-3xl p-6 shadow-xl space-y-4">
                        <div className="flex items-center justify-between border-b border-white/10 pb-3">
                            <div className="flex items-center gap-2">
                                <ShieldCheck size={18} className="text-emerald-400" />
                                <h3 className="font-bold text-lg text-white" style={{ fontFamily: "'Playfair Display', serif" }}>
                                    Wall of Muhsinin (Deretan Donatur)
                                </h3>
                            </div>
                            <span className="text-[10px] text-gray-400 uppercase tracking-widest font-mono">Privasi UU PDP Terlindungi</span>
                        </div>

                        {/* List of Donations */}
                        <div className="space-y-3 max-h-[460px] overflow-y-auto pr-1">
                            {donations.length === 0 ? (
                                <div className="text-center py-12 text-gray-500 text-xs">
                                    Belum ada catatan sedekah terbaru. Jadilah penjelajah pertama yang mengalirkan kebaikan!
                                </div>
                            ) : (
                                donations.map((donation) => (
                                    <div
                                        key={donation.id}
                                        className="p-4 rounded-2xl bg-white/[0.02] border border-white/5 hover:border-amber-400/20 transition-all space-y-2.5"
                                    >
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center gap-2">
                                                <div className="w-8 h-8 rounded-full bg-amber-400/10 border border-amber-400/30 flex items-center justify-center text-amber-400 font-mono text-xs font-bold">
                                                    {donation.is_anonymous ? 'HA' : donation.masked_donor_name.slice(0, 2).toUpperCase()}
                                                </div>
                                                <div>
                                                    <div className="flex items-center gap-2">
                                                        <strong className="text-xs font-semibold text-white">
                                                            {donation.masked_donor_name}
                                                        </strong>
                                                        <span className="text-[10px] text-gray-500 font-mono">
                                                            {donation.masked_donor_phone}
                                                        </span>
                                                    </div>
                                                    <span className="text-[10px] text-gray-500 block">
                                                        {new Date(donation.created_at).toLocaleDateString('id-ID', {
                                                            day: 'numeric',
                                                            month: 'short',
                                                            hour: '2-digit',
                                                            minute: '2-digit',
                                                        })}
                                                    </span>
                                                </div>
                                            </div>

                                            <div className="text-right">
                                                <span className="font-mono text-xs sm:text-sm font-bold text-amber-400">
                                                    +{Number(donation.amount_gold).toLocaleString('id-ID')} Emas
                                                </span>
                                            </div>
                                        </div>

                                        {donation.prayer_message && (
                                            <div className="p-2.5 rounded-xl bg-black/40 border border-white/5 text-xs text-gray-300 italic flex items-start gap-2">
                                                <span className="text-amber-400 font-serif text-base leading-none">“</span>
                                                <p className="flex-1 leading-relaxed text-[11px]">{donation.prayer_message}</p>
                                                <span className="text-amber-400 font-serif text-base leading-none self-end">”</span>
                                            </div>
                                        )}

                                        {/* Aamiin button */}
                                        <div className="flex items-center justify-end gap-2 pt-1">
                                            <button
                                                type="button"
                                                onClick={() => handleAamiin(donation.id)}
                                                disabled={aamiinLoading === donation.id}
                                                className="px-3 py-1 rounded-full bg-amber-400/10 hover:bg-amber-400/20 border border-amber-400/30 text-amber-300 text-[11px] font-medium flex items-center gap-1.5 transition-all active:scale-95"
                                            >
                                                <span>Aamiin-kan Doa</span>
                                                <span className="font-mono font-bold bg-amber-400/20 px-1.5 py-0.2 rounded-full text-[10px]">
                                                    {donation.aamiin_count}
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </section>

                {/* 3. BATCH DISTRIBUTION HISTORY & BENEFICIARY DETAILS */}
                <section className="bg-[#16161e] border border-white/10 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-white/10 pb-4">
                        <div>
                            <div className="flex items-center gap-2">
                                <Calendar size={18} className="text-blue-400" />
                                <h3 className="font-bold text-lg sm:text-xl text-white" style={{ fontFamily: "'Playfair Display', serif" }}>
                                    Histori Periode Distribusi & Rincian Penerima Manfaat
                                </h3>
                            </div>
                            <p className="text-xs text-gray-400 mt-1">
                                Laporan audit periodik penyaluran dana sosial Koin Emas secara transparan ke mustahik.
                            </p>
                        </div>
                        <div className="flex items-center gap-2 text-[11px] text-gray-400">
                            <Lock size={12} className="text-amber-400" />
                            <span>Identitas mustahik disamarkan demi kehormatan pribadi</span>
                        </div>
                    </div>

                    {/* Periods Accordion List */}
                    <div className="space-y-4">
                        {periods.length === 0 ? (
                            <div className="text-center py-12 text-gray-500 text-xs">
                                Belum ada batch periode distribusi yang selesai.
                            </div>
                        ) : (
                            periods.map((period) => {
                                const isExpanded = expandedPeriodId === period.id;
                                return (
                                    <div
                                        key={period.id}
                                        className="border border-white/10 rounded-2xl overflow-hidden bg-white/[0.01] transition-all"
                                    >
                                        {/* Header Accordion */}
                                        <button
                                            type="button"
                                            onClick={() => setExpandedPeriodId(isExpanded ? null : period.id)}
                                            className="w-full p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-left hover:bg-white/[0.02] transition-colors"
                                        >
                                            <div className="space-y-1">
                                                <div className="flex items-center gap-2">
                                                    <span className="font-bold text-sm text-white">{period.period_name}</span>
                                                    <span className="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono">
                                                        {period.status}
                                                    </span>
                                                </div>
                                                <div className="text-[11px] text-gray-400">
                                                    Tanggal Distribusi: {new Date(period.distribution_date).toLocaleDateString('id-ID', {
                                                        day: 'numeric',
                                                        month: 'long',
                                                        year: 'numeric',
                                                    })}
                                                </div>
                                            </div>

                                            <div className="flex items-center justify-between sm:justify-end gap-4 w-full sm:w-auto border-t sm:border-t-0 pt-2 sm:pt-0 border-white/5">
                                                <div className="text-left sm:text-right">
                                                    <span className="text-[10px] text-gray-500 uppercase tracking-wider block">Total Disalurkan</span>
                                                    <span className="font-mono text-sm font-bold text-amber-400">
                                                        {Number(period.total_gold_distributed).toLocaleString('id-ID')} Emas
                                                    </span>
                                                    <span className="text-[10px] text-gray-400 block font-mono">
                                                        ({period.beneficiaries_count} Mustahik)
                                                    </span>
                                                </div>
                                                <div className="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-gray-400">
                                                    {isExpanded ? <ChevronUp size={16} /> : <ChevronDown size={16} />}
                                                </div>
                                            </div>
                                        </button>

                                        {/* Expandable Table of Beneficiaries */}
                                        {isExpanded && (
                                            <div className="p-4 sm:p-5 border-t border-white/10 bg-black/30 space-y-3 animate-fade-in">
                                                {period.notes && (
                                                    <p className="text-xs text-gray-400 italic bg-white/[0.02] p-3 rounded-xl border border-white/5">
                                                        <strong>Catatan Panitia Kas:</strong> {period.notes}
                                                    </p>
                                                )}

                                                {/* Modern Responsive Beneficiary List (Pills with Badge & Info) */}
                                                <div className="space-y-2.5">
                                                    {period.distributions.length === 0 ? (
                                                        <div className="py-8 text-center text-gray-500 text-xs">
                                                            Belum ada data rincian untuk periode ini.
                                                        </div>
                                                    ) : (
                                                        period.distributions.map((item, idx) => {
                                                            const catInfo = getCategoryLabel(item.beneficiary_category);
                                                            return (
                                                                <div
                                                                    key={item.id}
                                                                    className="p-3 sm:p-4 rounded-xl bg-white/[0.03] hover:bg-white/[0.06] border border-white/5 hover:border-amber-500/20 transition-all duration-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm"
                                                                >
                                                                    {/* Left: Index + Masked Name + Category Badge + Notes */}
                                                                    <div className="flex items-start sm:items-center gap-3">
                                                                        <div className="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 font-mono font-bold text-xs flex items-center justify-center shrink-0 mt-0.5 sm:mt-0">
                                                                            {idx + 1}
                                                                        </div>
                                                                        <div className="space-y-1 min-w-0">
                                                                            <div className="flex items-center gap-2 flex-wrap">
                                                                                <span className="font-bold text-white text-xs sm:text-sm">
                                                                                    {item.masked_beneficiary_name}
                                                                                </span>
                                                                                <span className={`text-[10px] px-2 py-0.5 rounded-full border font-mono font-semibold ${catInfo.color}`}>
                                                                                    {catInfo.label}
                                                                                </span>
                                                                            </div>
                                                                            {item.distribution_note && (
                                                                                <p className="text-[11px] sm:text-xs text-gray-400 leading-relaxed">
                                                                                    {item.distribution_note}
                                                                                </p>
                                                                            )}
                                                                        </div>
                                                                    </div>

                                                                    {/* Right: Amount in Gold Pill */}
                                                                    <div className="flex items-center justify-between sm:justify-end gap-2 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-white/5">
                                                                        <span className="text-[10px] text-gray-500 font-medium sm:hidden uppercase tracking-wider">
                                                                            Santunan:
                                                                        </span>
                                                                        <div className="flex items-center gap-1.5 px-3 py-1 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-400 font-mono font-bold text-xs sm:text-sm shadow-inner">
                                                                            <Coins size={13} className="text-amber-400" />
                                                                            <span>+{Number(item.amount_gold).toLocaleString('id-ID')} Emas</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            );
                                                        })
                                                    )}
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                );
                            })
                        )}
                    </div>
                </section>
            </main>

            {/* Global Footer & Compliance Links */}
            <footer className="mt-16 pt-8 border-t border-white/10 text-center text-xs text-gray-500 space-y-3">
                <div className="flex justify-center items-center gap-4 flex-wrap">
                    <Link href={route('dashboard')} className="hover:text-amber-400 transition-colors">
                        Dasbor Utama
                    </Link>
                    <span>•</span>
                    <Link href={route('help.index')} className="hover:text-amber-400 transition-colors">
                        Pusat Bantuan & FAQ
                    </Link>
                    <span>•</span>
                    <Link href={route('legal.terms')} className="hover:text-amber-400 transition-colors">
                        Syarat & Ketentuan
                    </Link>
                    <span>•</span>
                    <Link href={route('legal.privacy')} className="hover:text-amber-400 transition-colors">
                        Kebijakan Privasi (UU PDP)
                    </Link>
                </div>
                <p>© 2026 PitiCoin.id — Kas Sosial Baitul Mal & Ekosistem Gunung Mas.</p>
            </footer>

            <CookieConsentBanner />
        </div>
    );
}
