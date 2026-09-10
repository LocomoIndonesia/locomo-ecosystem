import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { 
    Shield, 
    Users, 
    Clock, 
    Coins, 
    Award, 
    Star, 
    TrendingUp, 
    Send, 
    MessageSquare, 
    CheckCircle2, 
    Search, 
    ExternalLink, 
    RefreshCw, 
    AlertCircle, 
    Crown, 
    X,
    UserCheck,
    Flame,
    Zap,
    Layers,
    SlidersHorizontal,
    PhoneCall
} from 'lucide-react';

interface PendingUser {
    id: string;
    name: string;
    username: string | null;
    whatsapp: string;
    faction: string;
    role_class: string;
    license_type: string;
    package_name: string;
    package_price: string;
    silver_reward: number;
    registered_at: string;
    waiting_since: string;
}

interface ActiveSaudagar {
    id: string;
    name: string;
    username: string;
    whatsapp: string;
    faction: string;
    role_class: string;
    license_type: string;
    level: number;
    experience_points: number;
    silver_balance: number;
    gold_balance: number;
    downlines_l1: number;
    downlines_l2: number;
    total_downlines: number;
    scale_up_rate: number;
    rating: number;
    total_reviews: number;
    unread_consultations: number;
    joined_at: string;
}

interface AdminDashboardProps {
    adminUser: {
        id: string;
        name: string;
        username: string;
        role: string;
        is_root: boolean;
        rating: number;
    };
    pendingUsers: PendingUser[];
    activeSaudagars: ActiveSaudagar[];
    stats: {
        pending_count: number;
        active_count: number;
        total_silver: number;
        total_gold: number;
        fiat_in_bank: number;
    };
    flash?: {
        success?: string;
        error?: string;
    };
}

interface ChatMessage {
    id: string;
    sender_role: 'ADMIN' | 'SAUDAGAR';
    subject: string | null;
    message: string;
    is_read: boolean;
    created_at: string;
}

export default function AdminDashboard({ adminUser, pendingUsers, activeSaudagars, stats }: AdminDashboardProps) {
    const [activeTab, setActiveTab] = useState<'pending' | 'active'>('pending');
    const [searchQuery, setSearchQuery] = useState('');
    const [approvingId, setApprovingId] = useState<string | null>(null);

    // Consultation / Q&A Modal States
    const [selectedSaudagar, setSelectedSaudagar] = useState<ActiveSaudagar | null>(null);
    const [chatMessages, setChatMessages] = useState<ChatMessage[]>([]);
    const [chatLoading, setChatLoading] = useState(false);
    const [replyText, setReplyText] = useState('');
    const [sendingMessage, setSendingMessage] = useState(false);

    // Filter Pending
    const filteredPending = pendingUsers.filter(u => 
        u.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        u.whatsapp.includes(searchQuery) ||
        u.package_name.toLowerCase().includes(searchQuery.toLowerCase())
    );

    // Filter Active Saudagars
    const filteredActive = activeSaudagars.filter(u =>
        u.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        u.username.toLowerCase().includes(searchQuery.toLowerCase()) ||
        u.whatsapp.includes(searchQuery) ||
        u.faction.toLowerCase().includes(searchQuery.toLowerCase()) ||
        u.role_class.toLowerCase().includes(searchQuery.toLowerCase())
    );

    // Handle Approve License
    const handleApprove = (userId: string, userName: string, packageName: string) => {
        if (confirm(`Konfirmasi aktivasi lisensi untuk "${userName}" (${packageName})?\nBekal Koin Perak akan langsung ditransfer ke dompet saudagar.`)) {
            setApprovingId(userId);
            router.post(route('admin.approve-license', userId), {}, {
                onFinish: () => setApprovingId(null)
            });
        }
    };

    // Open Consultation Modal
    const handleOpenConsultation = async (saudagar: ActiveSaudagar) => {
        setSelectedSaudagar(saudagar);
        setChatLoading(true);
        try {
            const res = await fetch(route('admin.consultations.get', saudagar.id));
            const data = await res.json();
            setChatMessages(data.messages || []);
        } catch (err) {
            console.error('Failed to load messages', err);
        } finally {
            setChatLoading(false);
        }
    };

    // Send Message
    const handleSendMessage = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!replyText.trim() || !selectedSaudagar) return;

        setSendingMessage(true);
        try {
            const res = await fetch(route('admin.messages.send'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                },
                body: JSON.stringify({
                    user_id: selectedSaudagar.id,
                    message: replyText.trim(),
                    subject: 'Konsultasi Admin & Layanan Saudagar',
                })
            });
            const data = await res.json();
            if (data.success && data.message) {
                setChatMessages(prev => [...prev, data.message]);
                setReplyText('');
            }
        } catch (err) {
            console.error('Error sending message', err);
        } finally {
            setSendingMessage(false);
        }
    };

    // Quick Reply Template helper
    const handleTemplateClick = (text: string) => {
        setReplyText(text);
    };

    // Faction Badge Styling helper
    const getFactionColor = (faction: string) => {
        switch (faction?.toUpperCase()) {
            case 'NISNAS':
                return 'text-amber-400 bg-amber-950/40 border-amber-500/40';
            case 'ANDROID':
                return 'text-cyan-400 bg-cyan-950/40 border-cyan-500/40';
            case 'HUMAN':
            default:
                return 'text-emerald-400 bg-emerald-950/40 border-emerald-500/40';
        }
    };

    return (
        <div className="bg-[#0a0a0c] text-white min-h-screen font-sans flex flex-col selection:bg-amber-500/30 selection:text-amber-200">
            <Head title="Root Control Center (Level 0) - Piticoin & Locomo" />

            {/* Ambient Lighting Overlay */}
            <div className="fixed inset-0 z-0 pointer-events-none">
                <div className="absolute top-0 left-1/4 w-96 h-96 bg-amber-500/5 rounded-full blur-3xl" />
                <div className="absolute top-1/3 right-1/4 w-96 h-96 bg-primary-container/5 rounded-full blur-3xl" />
            </div>

            {/* Top Navigation Bar */}
            <header className="relative z-10 border-b border-white/10 bg-[#0e0e12]/80 backdrop-blur-md px-4 lg:px-8 py-3.5 flex flex-wrap items-center justify-between gap-4">
                <div className="flex items-center gap-3">
                    <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 via-amber-600 to-yellow-700 flex items-center justify-center shadow-[0_0_20px_rgba(245,158,11,0.3)] border border-amber-400/40">
                        <Crown className="w-5 h-5 text-black font-extrabold" />
                    </div>
                    <div>
                        <div className="flex items-center gap-2">
                            <span className="font-bold tracking-wider text-base text-white">PITICOIN.ID</span>
                            <span className="text-[10px] font-mono px-2 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/40 font-bold uppercase">
                                [ROOT LEVEL 0]
                            </span>
                        </div>
                        <p className="text-xs text-gray-400 font-mono">Ekosistem Mandeh • Locomo Federated Network</p>
                    </div>
                </div>

                <div className="flex items-center gap-3">
                    {/* Admin Profile Chip */}
                    <div className="flex items-center gap-2.5 px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-xs">
                        <div className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
                        <span className="text-gray-300">Login: <strong className="text-amber-300 font-mono">@{adminUser.username}</strong></span>
                        <span className="text-[10px] bg-amber-500/20 text-amber-300 px-1.5 py-0.5 rounded font-bold">SUPERADMIN</span>
                    </div>

                    <Link
                        href={route('dashboard')}
                        className="px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white border border-white/10 text-xs transition-colors flex items-center gap-1.5"
                    >
                        <span>Lihat Dasbor Pengguna</span>
                        <ExternalLink size={13} />
                    </Link>

                    <Link
                        href={route('logout')}
                        method="post"
                        as="button"
                        className="px-3 py-1.5 rounded-lg bg-red-950/40 hover:bg-red-900/60 text-red-300 border border-red-800/40 text-xs transition-colors"
                    >
                        Keluar
                    </Link>
                </div>
            </header>

            {/* Metrics Overview Bar */}
            <section className="relative z-10 max-w-7xl w-full mx-auto px-4 lg:px-8 pt-6 pb-4">
                <div className="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                    {/* Metric 1: Pending */}
                    <div 
                        onClick={() => setActiveTab('pending')}
                        className={`cursor-pointer p-4 rounded-xl border transition-all ${
                            activeTab === 'pending'
                                ? 'bg-amber-950/20 border-amber-500/50 shadow-[0_0_20px_rgba(245,158,11,0.15)] ring-1 ring-amber-500/40'
                                : 'bg-[#121216] border-white/5 hover:border-white/15'
                        }`}
                    >
                        <div className="flex items-center justify-between text-xs text-gray-400 mb-1">
                            <span className="font-semibold uppercase tracking-wider text-[11px]">Pengguna Menunggu</span>
                            <Clock size={16} className="text-amber-400" />
                        </div>
                        <div className="flex items-baseline gap-2">
                            <span className="text-2xl lg:text-3xl font-bold font-mono text-amber-300">{stats.pending_count}</span>
                            <span className="text-[11px] text-amber-400/80 font-medium">butuh persetujuan</span>
                        </div>
                    </div>

                    {/* Metric 2: Active */}
                    <div 
                        onClick={() => setActiveTab('active')}
                        className={`cursor-pointer p-4 rounded-xl border transition-all ${
                            activeTab === 'active'
                                ? 'bg-emerald-950/20 border-emerald-500/50 shadow-[0_0_20px_rgba(16,185,129,0.15)] ring-1 ring-emerald-500/40'
                                : 'bg-[#121216] border-white/5 hover:border-white/15'
                        }`}
                    >
                        <div className="flex items-center justify-between text-xs text-gray-400 mb-1">
                            <span className="font-semibold uppercase tracking-wider text-[11px]">Saudagar Aktif</span>
                            <UserCheck size={16} className="text-emerald-400" />
                        </div>
                        <div className="flex items-baseline gap-2">
                            <span className="text-2xl lg:text-3xl font-bold font-mono text-emerald-300">{stats.active_count}</span>
                            <span className="text-[11px] text-emerald-400/80 font-medium">lisensi aktif</span>
                        </div>
                    </div>

                    {/* Metric 3: Total Perak */}
                    <div className="p-4 rounded-xl bg-[#121216] border border-white/5">
                        <div className="flex items-center justify-between text-xs text-gray-400 mb-1">
                            <span className="font-semibold uppercase tracking-wider text-[11px]">Total Koin Perak Beredar</span>
                            <Coins size={16} className="text-slate-300" />
                        </div>
                        <div className="flex items-baseline gap-2">
                            <span className="text-2xl lg:text-3xl font-bold font-mono text-slate-200">
                                {stats.total_silver.toLocaleString('id-ID')}
                            </span>
                            <span className="text-[11px] text-slate-400">Perak</span>
                        </div>
                    </div>

                    {/* Metric 4: Total Emas */}
                    <div className="p-4 rounded-xl bg-[#121216] border border-white/5">
                        <div className="flex items-center justify-between text-xs text-gray-400 mb-1">
                            <span className="font-semibold uppercase tracking-wider text-[11px]">Total Koin Emas Beredar</span>
                            <Flame size={16} className="text-amber-400" />
                        </div>
                        <div className="flex items-baseline gap-2">
                            <span className="text-2xl lg:text-3xl font-bold font-mono text-amber-400">
                                {stats.total_gold.toLocaleString('id-ID')}
                            </span>
                            <span className="text-[11px] text-amber-500">Emas</span>
                        </div>
                    </div>
                </div>
            </section>

            {/* Main Workspace */}
            <main className="relative z-10 max-w-7xl w-full mx-auto px-4 lg:px-8 py-4 flex-1 flex flex-col">
                {/* Search & Tabs Header */}
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-4 mb-6">
                    {/* Tabs */}
                    <div className="flex items-center gap-2 bg-[#121216] p-1 rounded-xl border border-white/10">
                        <button
                            onClick={() => setActiveTab('pending')}
                            className={`flex items-center gap-2 px-4 py-2 rounded-lg text-xs md:text-sm font-semibold transition-all ${
                                activeTab === 'pending'
                                    ? 'bg-amber-500 text-black shadow-md shadow-amber-500/20'
                                    : 'text-gray-400 hover:text-white'
                            }`}
                        >
                            <Clock size={15} />
                            <span>Pengguna Menunggu</span>
                            <span className={`px-1.5 py-0.2 rounded-full text-[10px] font-bold ${
                                activeTab === 'pending' ? 'bg-black/30 text-black' : 'bg-amber-500/20 text-amber-300'
                            }`}>
                                {stats.pending_count}
                            </span>
                        </button>

                        <button
                            onClick={() => setActiveTab('active')}
                            className={`flex items-center gap-2 px-4 py-2 rounded-lg text-xs md:text-sm font-semibold transition-all ${
                                activeTab === 'active'
                                    ? 'bg-amber-500 text-black shadow-md shadow-amber-500/20'
                                    : 'text-gray-400 hover:text-white'
                            }`}
                        >
                            <Users size={15} />
                            <span>Saudagar Aktif</span>
                            <span className={`px-1.5 py-0.2 rounded-full text-[10px] font-bold ${
                                activeTab === 'active' ? 'bg-black/30 text-black' : 'bg-emerald-500/20 text-emerald-300'
                            }`}>
                                {stats.active_count}
                            </span>
                        </button>
                    </div>

                    {/* Search Field */}
                    <div className="relative w-full sm:w-80">
                        <Search size={15} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500" />
                        <input
                            type="text"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            placeholder={activeTab === 'pending' ? 'Cari nama / WhatsApp / paket...' : 'Cari nama / username / faksi...'}
                            className="w-full bg-[#14141a] border border-white/10 rounded-xl pl-9 pr-4 py-2 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                        />
                        {searchQuery && (
                            <button
                                onClick={() => setSearchQuery('')}
                                className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300"
                            >
                                <X size={13} />
                            </button>
                        )}
                    </div>
                </div>

                {/* TAB 1: PENGGUNA MENUNGGU (PENDING) */}
                {activeTab === 'pending' && (
                    <div className="flex flex-col gap-4">
                        {filteredPending.length === 0 ? (
                            <div className="bg-[#121216] border border-white/5 rounded-2xl p-12 text-center flex flex-col items-center justify-center">
                                <div className="w-14 h-14 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mb-3">
                                    <CheckCircle2 className="w-7 h-7 text-emerald-400" />
                                </div>
                                <h3 className="text-base font-bold text-white mb-1">Semua Pembayaran Selesai Diverifikasi</h3>
                                <p className="text-xs text-gray-400 max-w-sm">
                                    Tidak ada antrean pengguna dengan status menunggu pembayaran saat ini.
                                </p>
                            </div>
                        ) : (
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {filteredPending.map((user) => (
                                    <div 
                                        key={user.id}
                                        className="bg-[#121216] border border-amber-500/20 rounded-2xl p-5 flex flex-col justify-between hover:border-amber-500/40 transition-all relative overflow-hidden group shadow-lg"
                                    >
                                        <div className="absolute top-0 right-0 w-24 h-24 bg-amber-500/5 rounded-full blur-xl pointer-events-none group-hover:bg-amber-500/10 transition-colors" />

                                        <div>
                                            {/* Header */}
                                            <div className="flex items-start justify-between gap-2 mb-3">
                                                <div>
                                                    <h4 className="font-bold text-base text-white group-hover:text-amber-300 transition-colors">{user.name}</h4>
                                                    <div className="flex items-center gap-1.5 mt-0.5">
                                                        <a 
                                                            href={`https://wa.me/${user.whatsapp.replace(/\D/g, '')}`}
                                                            target="_blank"
                                                            rel="noreferrer"
                                                            className="text-xs font-mono text-emerald-400 hover:underline flex items-center gap-1"
                                                        >
                                                            <span>+{user.whatsapp}</span>
                                                            <ExternalLink size={10} />
                                                        </a>
                                                    </div>
                                                </div>
                                                <span className="text-[10px] font-mono px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/40 font-semibold animate-pulse">
                                                    PENDING
                                                </span>
                                            </div>

                                            {/* Lore Badges */}
                                            <div className="flex items-center gap-2 mb-4">
                                                <span className={`text-[10px] font-bold px-2 py-0.5 rounded border ${getFactionColor(user.faction)}`}>
                                                    {user.faction}
                                                </span>
                                                <span className="text-[10px] font-bold px-2 py-0.5 rounded bg-white/5 border border-white/10 text-gray-300">
                                                    {user.role_class}
                                                </span>
                                            </div>

                                            {/* Package & Silver Details */}
                                            <div className="bg-[#17171d] rounded-xl p-3 border border-white/5 mb-4 text-xs flex flex-col gap-2">
                                                <div className="flex items-center justify-between">
                                                    <span className="text-gray-400">Pilihan Paket:</span>
                                                    <span className="font-bold text-amber-200">{user.package_name}</span>
                                                </div>
                                                <div className="flex items-center justify-between">
                                                    <span className="text-gray-400">Nominal Transfer:</span>
                                                    <span className="font-bold font-mono text-white">{user.package_price}</span>
                                                </div>
                                                <div className="flex items-center justify-between border-t border-white/5 pt-1.5">
                                                    <span className="text-gray-400 flex items-center gap-1">
                                                        <Coins size={12} className="text-slate-300" />
                                                        Injeksi Perak:
                                                    </span>
                                                    <span className="font-bold font-mono text-emerald-400">+{user.silver_reward.toLocaleString('id-ID')} Perak</span>
                                                </div>
                                            </div>

                                            <div className="text-[11px] text-gray-400 mb-4 flex items-center justify-between">
                                                <span>Waktu Pengajuan:</span>
                                                <span className="text-gray-300">{user.waiting_since}</span>
                                            </div>
                                        </div>

                                        {/* Action Button */}
                                        <button
                                            disabled={approvingId === user.id}
                                            onClick={() => handleApprove(user.id, user.name, user.package_name)}
                                            className="w-full py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-600 hover:from-amber-400 hover:to-yellow-500 text-black font-bold text-xs tracking-wider uppercase transition-all shadow-[0_4px_15px_rgba(245,158,11,0.25)] flex items-center justify-center gap-2 disabled:opacity-50"
                                        >
                                            {approvingId === user.id ? (
                                                <>
                                                    <RefreshCw size={14} className="animate-spin" />
                                                    <span>Memproses Aktivasi...</span>
                                                </>
                                            ) : (
                                                <>
                                                    <CheckCircle2 size={15} />
                                                    <span>Setujui & Aktivasi Lisensi</span>
                                                </>
                                            )}
                                        </button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                )}

                {/* TAB 2: SAUDAGAR AKTIF */}
                {activeTab === 'active' && (
                    <div className="flex flex-col gap-4">
                        {filteredActive.length === 0 ? (
                            <div className="bg-[#121216] border border-white/5 rounded-2xl p-12 text-center flex flex-col items-center justify-center">
                                <Users className="w-12 h-12 text-gray-600 mb-2" />
                                <h3 className="text-base font-bold text-white mb-1">Tidak Ada Saudagar Ditemukan</h3>
                                <p className="text-xs text-gray-400">
                                    Coba ubah kata kunci pencarian Anda.
                                </p>
                            </div>
                        ) : (
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {filteredActive.map((saudagar) => (
                                    <div 
                                        key={saudagar.id}
                                        className="bg-[#121216] border border-white/10 rounded-2xl p-5 flex flex-col justify-between hover:border-amber-500/40 transition-all relative overflow-hidden group shadow-lg"
                                    >
                                        <div>
                                            {/* Header Saudagar */}
                                            <div className="flex items-start justify-between gap-3 mb-3">
                                                <div>
                                                    <div className="flex items-center gap-2">
                                                        <h4 className="font-bold text-base text-white group-hover:text-amber-300 transition-colors">
                                                            {saudagar.name}
                                                        </h4>
                                                        {saudagar.unread_consultations > 0 && (
                                                            <span className="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping" title="Pesan baru" />
                                                        )}
                                                    </div>
                                                    <p className="text-xs text-gray-400 font-mono">
                                                        @{saudagar.username !== '-' ? saudagar.username : saudagar.whatsapp}
                                                    </p>
                                                </div>

                                                {/* Reputation Rating */}
                                                <div className="flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-bold font-mono">
                                                    <Star size={13} className="fill-amber-400 text-amber-400" />
                                                    <span>{saudagar.rating.toFixed(1)}</span>
                                                    <span className="text-[10px] text-gray-400 font-normal">({saudagar.total_reviews})</span>
                                                </div>
                                            </div>

                                            {/* Lore & License Badges */}
                                            <div className="flex flex-wrap items-center gap-1.5 mb-4">
                                                <span className={`text-[10px] font-bold px-2 py-0.5 rounded border ${getFactionColor(saudagar.faction)}`}>
                                                    {saudagar.faction}
                                                </span>
                                                <span className="text-[10px] font-bold px-2 py-0.5 rounded bg-white/5 border border-white/10 text-gray-300">
                                                    {saudagar.role_class}
                                                </span>
                                                <span className="text-[10px] font-bold px-2 py-0.5 rounded bg-purple-950/40 border border-purple-500/40 text-purple-300">
                                                    {saudagar.license_type}
                                                </span>
                                                <span className="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-950/30 border border-amber-600/30 text-amber-400 font-mono">
                                                    Lv.{saudagar.level}
                                                </span>
                                            </div>

                                            {/* Stats Grid: Coins & Downline */}
                                            <div className="grid grid-cols-2 gap-2 mb-3">
                                                {/* Koin Perak */}
                                                <div className="bg-[#16161c] p-2.5 rounded-xl border border-white/5">
                                                    <div className="flex items-center justify-between text-[10px] text-gray-400 mb-1">
                                                        <span>Koin Perak</span>
                                                        <Coins size={11} className="text-slate-400" />
                                                    </div>
                                                    <div className="font-bold font-mono text-sm text-slate-200">
                                                        {saudagar.silver_balance.toLocaleString('id-ID')}
                                                    </div>
                                                </div>

                                                {/* Koin Emas */}
                                                <div className="bg-[#16161c] p-2.5 rounded-xl border border-white/5">
                                                    <div className="flex items-center justify-between text-[10px] text-gray-400 mb-1">
                                                        <span>Koin Emas</span>
                                                        <Flame size={11} className="text-amber-400" />
                                                    </div>
                                                    <div className="font-bold font-mono text-sm text-amber-300">
                                                        {saudagar.gold_balance.toLocaleString('id-ID')}
                                                    </div>
                                                </div>
                                            </div>

                                            {/* Downlines & Scale Up Rate */}
                                            <div className="bg-gradient-to-br from-[#16161e] to-[#121218] p-3 rounded-xl border border-white/10 mb-4 text-xs">
                                                <div className="flex items-center justify-between mb-2">
                                                    <span className="text-gray-400 flex items-center gap-1 text-[11px]">
                                                        <Users size={12} className="text-primary-container" />
                                                        Downline (L1: {saudagar.downlines_l1} | L2: {saudagar.downlines_l2})
                                                    </span>
                                                    <span className="font-bold font-mono text-white">
                                                        {saudagar.total_downlines} Saudagar
                                                    </span>
                                                </div>

                                                {/* Scale Up Rate Pill */}
                                                <div className="flex items-center justify-between border-t border-white/5 pt-2">
                                                    <span className="text-gray-400 flex items-center gap-1 text-[11px]">
                                                        <TrendingUp size={12} className="text-emerald-400" />
                                                        Scale Up Rate:
                                                    </span>
                                                    <span className="font-bold font-mono text-emerald-400 bg-emerald-950/40 border border-emerald-500/30 px-2 py-0.5 rounded text-[11px]">
                                                        +{saudagar.scale_up_rate}% Profit Multiplier
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        {/* Action Button: Kirim Pesan & Konsultasi */}
                                        <button
                                            onClick={() => handleOpenConsultation(saudagar)}
                                            className="w-full py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-amber-300 hover:text-amber-200 border border-amber-500/30 font-semibold text-xs tracking-wide transition-all flex items-center justify-center gap-2 group-hover:border-amber-500/50"
                                        >
                                            <MessageSquare size={14} />
                                            <span>Kirim Pesan / Konsultasi (Q&A)</span>
                                            {saudagar.unread_consultations > 0 && (
                                                <span className="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-amber-500 text-black">
                                                    {saudagar.unread_consultations}
                                                </span>
                                            )}
                                        </button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                )}
            </main>

            {/* CONSULTATION & Q&A MODAL */}
            {selectedSaudagar && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="bg-[#121216] border border-amber-500/30 rounded-2xl w-full max-w-2xl max-h-[85vh] flex flex-col shadow-2xl overflow-hidden">
                        {/* Modal Header */}
                        <div className="p-4 border-b border-white/10 flex items-center justify-between bg-[#15151c]">
                            <div className="flex items-center gap-3">
                                <div className="w-10 h-10 rounded-full bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 font-bold">
                                    {selectedSaudagar.name.charAt(0)}
                                </div>
                                <div>
                                    <div className="flex items-center gap-2">
                                        <h3 className="font-bold text-sm text-white">{selectedSaudagar.name}</h3>
                                        <span className={`text-[10px] font-bold px-1.5 py-0.2 rounded border ${getFactionColor(selectedSaudagar.faction)}`}>
                                            {selectedSaudagar.faction}
                                        </span>
                                    </div>
                                    <p className="text-[11px] text-gray-400 font-mono">
                                        WhatsApp: +{selectedSaudagar.whatsapp} • Rating: ⭐ {selectedSaudagar.rating.toFixed(1)}
                                    </p>
                                </div>
                            </div>

                            <button
                                onClick={() => setSelectedSaudagar(null)}
                                className="text-gray-400 hover:text-white p-1 rounded-lg hover:bg-white/5 transition-colors"
                            >
                                <X size={18} />
                            </button>
                        </div>

                        {/* Quick Action Templates */}
                        <div className="p-2.5 bg-[#171720] border-b border-white/5 flex items-center gap-2 overflow-x-auto text-[11px]">
                            <span className="text-gray-400 whitespace-nowrap font-medium pl-1">Template:</span>
                            <button
                                type="button"
                                onClick={() => handleTemplateClick(`Halo Saudagar ${selectedSaudagar.name}, akun lisensi dan bekal Koin Perak Anda telah aktif. Selamat berpetualang!`)}
                                className="whitespace-nowrap px-2 py-1 rounded bg-white/5 hover:bg-white/10 text-amber-300 border border-white/10 transition-colors"
                            >
                                Aktivasi Sukses
                            </button>
                            <button
                                type="button"
                                onClick={() => handleTemplateClick(`Halo Saudagar ${selectedSaudagar.name}, bagaimana perkembangan ekspedisi dan strategi downline Anda di Gunung Mas?`)}
                                className="whitespace-nowrap px-2 py-1 rounded bg-white/5 hover:bg-white/10 text-gray-300 border border-white/10 transition-colors"
                            >
                                Tanya Progres
                            </button>
                            <button
                                type="button"
                                onClick={() => handleTemplateClick(`Mohon kirimkan ulang bukti transfer pembayaran Anda dengan foto yang jelas.`)}
                                className="whitespace-nowrap px-2 py-1 rounded bg-white/5 hover:bg-white/10 text-gray-300 border border-white/10 transition-colors"
                            >
                                Bukti Transfer Ulang
                            </button>
                        </div>

                        {/* Chat Messages Body */}
                        <div className="flex-1 p-4 overflow-y-auto space-y-3 min-h-[250px] max-h-[400px] bg-[#0d0d10]">
                            {chatLoading ? (
                                <div className="h-full flex items-center justify-center text-xs text-gray-400 gap-2">
                                    <RefreshCw size={14} className="animate-spin text-amber-400" />
                                    <span>Memuat percakapan konsultasi...</span>
                                </div>
                            ) : chatMessages.length === 0 ? (
                                <div className="h-full flex flex-col items-center justify-center text-center p-6 text-gray-500">
                                    <MessageSquare className="w-10 h-10 mb-2 opacity-40" />
                                    <p className="text-xs font-medium text-gray-400">Belum ada riwayat percakapan dengan saudagar ini.</p>
                                    <p className="text-[11px] text-gray-500">Kirimkan pesan private atau jawaban konsultasi pertama Anda di bawah.</p>
                                </div>
                            ) : (
                                chatMessages.map((msg) => {
                                    const isAdmin = msg.sender_role === 'ADMIN';
                                    return (
                                        <div
                                            key={msg.id}
                                            className={`flex flex-col ${isAdmin ? 'items-end' : 'items-start'}`}
                                        >
                                            <div className="flex items-center gap-1.5 text-[10px] text-gray-400 mb-1 px-1">
                                                <span className="font-semibold text-amber-300">
                                                    {isAdmin ? '👑 Admin Platform' : selectedSaudagar.name}
                                                </span>
                                                <span>•</span>
                                                <span>{new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}</span>
                                            </div>
                                            <div
                                                className={`max-w-[85%] rounded-2xl p-3 text-xs leading-relaxed ${
                                                    isAdmin
                                                        ? 'bg-gradient-to-r from-amber-600 to-amber-700 text-black font-medium rounded-tr-none shadow-md'
                                                        : 'bg-[#1a1a24] text-white border border-white/10 rounded-tl-none shadow-md'
                                                }`}
                                            >
                                                {msg.subject && (
                                                    <div className={`text-[11px] font-bold mb-1 ${isAdmin ? 'text-black/70' : 'text-amber-400'}`}>
                                                        {msg.subject}
                                                    </div>
                                                )}
                                                <p className="whitespace-pre-wrap">{msg.message}</p>
                                            </div>
                                        </div>
                                    );
                                })
                            )}
                        </div>

                        {/* Input Area */}
                        <form onSubmit={handleSendMessage} className="p-3 border-t border-white/10 bg-[#15151c] flex gap-2">
                            <input
                                type="text"
                                value={replyText}
                                onChange={(e) => setReplyText(e.target.value)}
                                placeholder="Ketik pesan konsultasi atau notifikasi private..."
                                className="flex-1 bg-[#0d0d10] border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                            />
                            <button
                                type="submit"
                                disabled={sendingMessage || !replyText.trim()}
                                className="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 disabled:opacity-40 text-black font-bold text-xs flex items-center gap-1.5 transition-colors shadow-md"
                            >
                                <Send size={14} />
                                <span>Kirim</span>
                            </button>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
