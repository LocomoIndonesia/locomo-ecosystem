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
    PhoneCall,
    Package,
    ShoppingCart,
    GitBranch,
    BadgePercent,
    ArrowUpRight,
    Calendar,
    Receipt,
    PlusCircle,
    Edit3,
    Compass,
    Trash2,
    Plus,
    ToggleLeft,
    Info,
    Check,
    Target
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

interface ServiceItem {
    id: string;
    name: string;
    code: string;
    icon?: string;
    category: string;
    service_type: 'INTERNAL' | 'EXTERNAL';
    description?: string;
    price_gold: number;
    price_fiat: number;
    allows_installments: boolean;
    min_down_payment_gold?: number;
    min_down_payment_fiat?: number;
    commission_rules?: {
        depth?: number;
        levels?: Array<{ level: number; percentage: number }>;
    };
    is_active: boolean;
    orders_count: number;
    licenses_count: number;
    owner?: {
        id: string;
        name: string;
        username: string;
    };
}

interface OrderItem {
    id: string;
    order_number: string;
    service_id: string;
    user_id: string;
    registered_by_id?: string;
    payment_type: 'FULL' | 'INSTALLMENT';
    total_amount_gold: number;
    total_amount_fiat: number;
    paid_amount_gold: number;
    paid_amount_fiat: number;
    remaining_amount_gold: number;
    remaining_amount_fiat: number;
    status: string;
    created_at: string;
    service?: {
        name: string;
        code: string;
        service_type: string;
    };
    user?: {
        name: string;
        username: string;
        whatsapp: string;
    };
    registered_by?: {
        name: string;
        username: string;
    };
    installments?: Array<{
        id: string;
        installment_number: number;
        amount_gold: number;
        amount_fiat: number;
        currency: string;
        status: string;
        paid_at: string;
        notes?: string;
    }>;
}

interface CommissionItem {
    id: string;
    level: number;
    percentage: number;
    amount_gold: number;
    amount_fiat: number;
    currency: string;
    status: string;
    created_at: string;
    upline_user?: {
        name: string;
        username: string;
        whatsapp: string;
    };
    beneficiary_user?: {
        name: string;
        username: string;
    };
    service?: {
        name: string;
        code: string;
    };
}

interface QuestItem {
    id: string;
    title: string;
    description: string;
    quest_category: 'DEFAULT' | 'TIME_WINDOW' | 'PERSONA_SPECIFIC';
    time_window?: 'ALL' | 'MORNING' | 'AFTERNOON' | 'EVENING' | 'NIGHT';
    target_persona?: string;
    stake_amount_silver: number;
    reward_amount_gold: number;
    time_limit_hours: number;
    min_level: number;
    is_active: boolean;
    parameters?: {
        action_type?: string;
        target_threshold?: number;
    };
    active_participants_count?: number;
    total_attempts_count?: number;
    created_at?: string;
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
    services?: ServiceItem[];
    orders?: OrderItem[];
    recentCommissions?: CommissionItem[];
    quests?: QuestItem[];
    stats: {
        pending_count: number;
        active_count: number;
        services_count?: number;
        orders_count?: number;
        quests_count?: number;
        active_installments?: number;
        total_omzet_gold?: number;
        total_omzet_fiat?: number;
        total_payout_gold?: number;
        total_payout_fiat?: number;
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

export default function AdminDashboard({
    adminUser,
    pendingUsers = [],
    activeSaudagars = [],
    services = [],
    orders = [],
    recentCommissions = [],
    quests = [],
    stats,
}: AdminDashboardProps) {
    const [activeTab, setActiveTab] = useState<'pending' | 'active' | 'services' | 'orders' | 'commissions' | 'quests'>('pending');
    const [searchQuery, setSearchQuery] = useState('');
    const [processingId, setProcessingId] = useState<string | null>(null);

    // Quests Management State
    const [questList, setQuestList] = useState<QuestItem[]>(quests);
    const [selectedQuestCategory, setSelectedQuestCategory] = useState<'ALL' | 'DEFAULT' | 'TIME_WINDOW' | 'PERSONA_SPECIFIC'>('ALL');
    const [questModalOpen, setQuestModalOpen] = useState(false);
    const [editingQuest, setEditingQuest] = useState<QuestItem | null>(null);
    const [deleteQuestConfirm, setDeleteQuestConfirm] = useState<QuestItem | null>(null);
    const [questSubmitting, setQuestSubmitting] = useState(false);
    const [questError, setQuestError] = useState<string | null>(null);
    const [questFormData, setQuestFormData] = useState({
        title: '',
        description: '',
        quest_category: 'DEFAULT' as 'DEFAULT' | 'TIME_WINDOW' | 'PERSONA_SPECIFIC',
        time_window: 'ALL' as 'ALL' | 'MORNING' | 'AFTERNOON' | 'EVENING' | 'NIGHT',
        target_persona: 'ALL',
        stake_amount_silver: 0,
        reward_amount_gold: 10,
        time_limit_hours: 24,
        min_level: 1,
        is_active: true,
        parameters: {
            action_type: 'RECRUIT_MEMBERS',
            target_threshold: 5,
        },
    });

    // Q&A / Consultation Drawer State
    const [selectedSaudagar, setSelectedSaudagar] = useState<ActiveSaudagar | null>(null);
    const [chatMessages, setChatMessages] = useState<ChatMessage[]>([]);
    const [chatLoading, setChatLoading] = useState(false);
    const [replyText, setReplyText] = useState('');
    const [sendingMessage, setSendingMessage] = useState(false);

    // Order Installment Detail Modal
    const [selectedOrderModal, setSelectedOrderModal] = useState<OrderItem | null>(null);

    const formatRupiah = (val: number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(val || 0);
    };

    const formatDate = (dateString: string) => {
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        } catch {
            return dateString;
        }
    };

    // Filter Helpers
    const q = (searchQuery || '').toLowerCase().trim();

    const filteredPending = (pendingUsers || []).filter(u => 
        (u?.name || '').toLowerCase().includes(q) ||
        (u?.username || '').toLowerCase().includes(q) ||
        String(u?.whatsapp || '').includes(q)
    );

    const filteredActive = (activeSaudagars || []).filter(u => 
        (u?.name || '').toLowerCase().includes(q) ||
        (u?.username || '').toLowerCase().includes(q) ||
        String(u?.whatsapp || '').includes(q)
    );

    const filteredServices = (services || []).filter(s =>
        (s?.name || '').toLowerCase().includes(q) ||
        (s?.code || '').toLowerCase().includes(q) ||
        (s?.category || '').toLowerCase().includes(q)
    );

    const filteredOrders = (orders || []).filter(o => {
        const orderNum = o?.order_number || (o as any)?.license_code || '';
        const sName = o?.service?.name || '';
        const uName = o?.user?.name || '';
        return orderNum.toLowerCase().includes(q) || sName.toLowerCase().includes(q) || uName.toLowerCase().includes(q);
    });

    // Approve License Action
    const handleApprove = (userId: string, userName: string) => {
        if (!confirm(`Konfirmasi persetujuan verifikasi lisensi untuk Saudagar ${userName}? Hadiah Koin Perak akan langsung disalurkan.`)) {
            return;
        }

        setProcessingId(userId);
        router.post(`/admin/approve-license/${userId}`, {}, {
            preserveScroll: true,
            onFinish: () => setProcessingId(null)
        });
    };

    // Open Consultation Drawer
    const handleOpenConsultation = async (saudagar: ActiveSaudagar) => {
        setSelectedSaudagar(saudagar);
        setChatLoading(true);
        setChatMessages([]);
        try {
            const res = await fetch(`/admin/consultations/${saudagar.id}`);
            const data = await res.json();
            if (data.messages) {
                setChatMessages(data.messages);
            }
        } catch (err) {
            console.error('Error loading consultation', err);
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
            const res = await fetch('/admin/messages/send', {
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
            <Head title="Root Control Center (Level 0) - Piticoin & Marketing Hub" />

            {/* Ambient Lighting Overlay */}
            <div className="fixed inset-0 z-0 pointer-events-none">
                <div className="absolute top-0 left-1/4 w-96 h-96 bg-amber-500/5 rounded-full blur-3xl" />
                <div className="absolute top-1/3 right-1/4 w-96 h-96 bg-purple-500/5 rounded-full blur-3xl" />
            </div>

            {/* Top Navigation Bar */}
            <header className="relative z-10 border-b border-white/10 bg-[#0e0e12]/90 backdrop-blur-md px-4 lg:px-8 py-3.5 flex flex-wrap items-center justify-between gap-4">
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
                        <p className="text-xs text-gray-400 font-mono">Ekosistem Mandeh • Locomo Federated Network & Marketing Hub</p>
                    </div>
                </div>

                <div className="flex items-center gap-2.5 flex-wrap">
                    {/* Quick Access: Marketing Portal */}
                    <Link
                        href="/marketing"
                        className="px-3.5 py-1.5 rounded-lg bg-gradient-to-r from-purple-600 to-indigo-600 hover:brightness-110 text-white text-xs font-bold transition-all shadow-md flex items-center gap-1.5"
                    >
                        <BadgePercent size={14} />
                        <span>Portal Marketing</span>
                    </Link>

                    <Link
                        href="/marketing/services/create"
                        className="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-black text-xs font-bold transition-all shadow-md flex items-center gap-1.5"
                    >
                        <PlusCircle size={14} />
                        <span>+ Produk Baru</span>
                    </Link>

                    {/* Admin Profile Chip */}
                    <div className="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-xs">
                        <div className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
                        <span className="text-gray-300">Login: <strong className="text-amber-300 font-mono">@{adminUser.username}</strong></span>
                        <span className="text-[10px] bg-amber-500/20 text-amber-300 px-1.5 py-0.5 rounded font-bold">SUPERADMIN</span>
                    </div>

                    <Link
                        href="/dashboard"
                        className="px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white border border-white/10 text-xs transition-colors flex items-center gap-1.5"
                    >
                        <span>Dasbor User</span>
                        <ExternalLink size={13} />
                    </Link>

                    <Link
                        href="/logout"
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
                <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    {/* Metric 1: Pending */}
                    <div 
                        onClick={() => setActiveTab('pending')}
                        className={`cursor-pointer p-3.5 rounded-xl border transition-all ${
                            activeTab === 'pending'
                                ? 'bg-amber-950/20 border-amber-500/50 shadow-[0_0_20px_rgba(245,158,11,0.15)] ring-1 ring-amber-500/40'
                                : 'bg-[#121216] border-white/5 hover:border-white/15'
                        }`}
                    >
                        <div className="flex items-center justify-between text-xs text-gray-400 mb-1">
                            <span className="font-semibold uppercase tracking-wider text-[10px]">Pending Verif</span>
                            <Clock size={15} className="text-amber-400" />
                        </div>
                        <div className="flex items-baseline gap-1.5">
                            <span className="text-xl lg:text-2xl font-bold font-mono text-amber-300">{stats.pending_count}</span>
                            <span className="text-[10px] text-amber-400/80">antrean</span>
                        </div>
                    </div>

                    {/* Metric 2: Active */}
                    <div 
                        onClick={() => setActiveTab('active')}
                        className={`cursor-pointer p-3.5 rounded-xl border transition-all ${
                            activeTab === 'active'
                                ? 'bg-emerald-950/20 border-emerald-500/50 shadow-[0_0_20px_rgba(16,185,129,0.15)] ring-1 ring-emerald-500/40'
                                : 'bg-[#121216] border-white/5 hover:border-white/15'
                        }`}
                    >
                        <div className="flex items-center justify-between text-xs text-gray-400 mb-1">
                            <span className="font-semibold uppercase tracking-wider text-[10px]">Saudagar Aktif</span>
                            <UserCheck size={15} className="text-emerald-400" />
                        </div>
                        <div className="flex items-baseline gap-1.5">
                            <span className="text-xl lg:text-2xl font-bold font-mono text-emerald-300">{stats.active_count}</span>
                            <span className="text-[10px] text-emerald-400/80">lisensi</span>
                        </div>
                    </div>

                    {/* Metric 3: Omzet Layanan (Gold) */}
                    <div 
                        onClick={() => setActiveTab('orders')}
                        className={`cursor-pointer p-3.5 rounded-xl border transition-all ${
                            activeTab === 'orders'
                                ? 'bg-amber-950/20 border-amber-500/50 ring-1 ring-amber-500/40'
                                : 'bg-[#121216] border-white/5 hover:border-white/15'
                        }`}
                    >
                        <div className="flex items-center justify-between text-xs text-gray-400 mb-1">
                            <span className="font-semibold uppercase tracking-wider text-[10px]">Omzet Layanan</span>
                            <Flame size={15} className="text-amber-400" />
                        </div>
                        <div className="flex flex-col">
                            <span className="text-base lg:text-lg font-bold font-mono text-amber-400">
                                {Number(stats.total_omzet_gold || 0).toLocaleString('id-ID')} Emas
                            </span>
                            <span className="text-[10px] text-emerald-400 font-mono truncate">
                                {formatRupiah(Number(stats.total_omzet_fiat || 0))}
                            </span>
                        </div>
                    </div>

                    {/* Metric 4: Payout Komisi Afiliasi */}
                    <div 
                        onClick={() => setActiveTab('commissions')}
                        className={`cursor-pointer p-3.5 rounded-xl border transition-all ${
                            activeTab === 'commissions'
                                ? 'bg-purple-950/20 border-purple-500/50 ring-1 ring-purple-500/40'
                                : 'bg-[#121216] border-white/5 hover:border-white/15'
                        }`}
                    >
                        <div className="flex items-center justify-between text-xs text-gray-400 mb-1">
                            <span className="font-semibold uppercase tracking-wider text-[10px]">Payout Komisi</span>
                            <BadgePercent size={15} className="text-purple-400" />
                        </div>
                        <div className="flex flex-col">
                            <span className="text-base lg:text-lg font-bold font-mono text-purple-300">
                                {Number(stats.total_payout_gold || 0).toLocaleString('id-ID')} Emas
                            </span>
                            <span className="text-[10px] text-purple-400/80 font-mono truncate">
                                {formatRupiah(Number(stats.total_payout_fiat || 0))}
                            </span>
                        </div>
                    </div>

                    {/* Metric 5: Cicilan Berjalan */}
                    <div 
                        onClick={() => setActiveTab('orders')}
                        className="cursor-pointer p-3.5 rounded-xl bg-[#121216] border border-white/5 hover:border-white/15 transition-all"
                    >
                        <div className="flex items-center justify-between text-xs text-gray-400 mb-1">
                            <span className="font-semibold uppercase tracking-wider text-[10px]">Cicilan Aktif</span>
                            <Clock size={15} className="text-indigo-400" />
                        </div>
                        <div className="flex items-baseline gap-1.5">
                            <span className="text-xl lg:text-2xl font-bold font-mono text-indigo-300">
                                {stats.active_installments || 0}
                            </span>
                            <span className="text-[10px] text-indigo-400/80">kontrak</span>
                        </div>
                    </div>

                    {/* Metric 6: Total Perak */}
                    <div className="p-3.5 rounded-xl bg-[#121216] border border-white/5">
                        <div className="flex items-center justify-between text-xs text-gray-400 mb-1">
                            <span className="font-semibold uppercase tracking-wider text-[10px]">Perak Beredar</span>
                            <Coins size={15} className="text-slate-300" />
                        </div>
                        <div className="flex items-baseline gap-1.5">
                            <span className="text-base lg:text-lg font-bold font-mono text-slate-200 truncate">
                                {Number(stats?.total_silver || 0).toLocaleString('id-ID')}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            {/* Main Workspace */}
            <main className="relative z-10 max-w-7xl w-full mx-auto px-4 lg:px-8 py-4 flex-1 flex flex-col">
                {/* Search & Tabs Header */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-white/10 pb-4 mb-6">
                    {/* Tabs */}
                    <div className="flex items-center gap-1.5 bg-[#121216] p-1.5 rounded-xl border border-white/10 overflow-x-auto">
                        <button
                            onClick={() => setActiveTab('pending')}
                            className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all ${
                                activeTab === 'pending'
                                    ? 'bg-amber-500 text-black shadow-md shadow-amber-500/20'
                                    : 'text-gray-400 hover:text-white'
                            }`}
                        >
                            <Clock size={14} />
                            <span>Pengguna Menunggu</span>
                            <span className={`px-1.5 py-0.2 rounded-full text-[10px] font-bold ${
                                activeTab === 'pending' ? 'bg-black/30 text-black' : 'bg-amber-500/20 text-amber-300'
                            }`}>
                                {stats.pending_count}
                            </span>
                        </button>

                        <button
                            onClick={() => setActiveTab('active')}
                            className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all ${
                                activeTab === 'active'
                                    ? 'bg-emerald-500 text-black shadow-md shadow-emerald-500/20'
                                    : 'text-gray-400 hover:text-white'
                            }`}
                        >
                            <Users size={14} />
                            <span>Saudagar Aktif</span>
                            <span className={`px-1.5 py-0.2 rounded-full text-[10px] font-bold ${
                                activeTab === 'active' ? 'bg-black/30 text-black' : 'bg-emerald-500/20 text-emerald-300'
                            }`}>
                                {stats.active_count}
                            </span>
                        </button>

                        <button
                            onClick={() => setActiveTab('services')}
                            className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all ${
                                activeTab === 'services'
                                    ? 'bg-purple-600 text-white shadow-md shadow-purple-600/20'
                                    : 'text-gray-400 hover:text-white'
                            }`}
                        >
                            <Package size={14} />
                            <span>Produk & Afiliasi</span>
                            <span className={`px-1.5 py-0.2 rounded-full text-[10px] font-bold ${
                                activeTab === 'services' ? 'bg-black/30 text-white' : 'bg-purple-500/20 text-purple-300'
                            }`}>
                                {services.length}
                            </span>
                        </button>

                        <button
                            onClick={() => setActiveTab('orders')}
                            className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all ${
                                activeTab === 'orders'
                                    ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20'
                                    : 'text-gray-400 hover:text-white'
                            }`}
                        >
                            <ShoppingCart size={14} />
                            <span>Pesanan & Cicilan</span>
                            <span className={`px-1.5 py-0.2 rounded-full text-[10px] font-bold ${
                                activeTab === 'orders' ? 'bg-black/30 text-white' : 'bg-indigo-500/20 text-indigo-300'
                            }`}>
                                {orders.length}
                            </span>
                        </button>

                        <button
                            onClick={() => setActiveTab('commissions')}
                            className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all ${
                                activeTab === 'commissions'
                                    ? 'bg-amber-600 text-white shadow-md shadow-amber-600/20'
                                    : 'text-gray-400 hover:text-white'
                            }`}
                        >
                            <GitBranch size={14} />
                            <span>Payout Komisi</span>
                            <span className={`px-1.5 py-0.2 rounded-full text-[10px] font-bold ${
                                activeTab === 'commissions' ? 'bg-black/30 text-white' : 'bg-amber-500/20 text-amber-300'
                            }`}>
                                {recentCommissions.length}
                            </span>
                        </button>

                        <button
                            onClick={() => setActiveTab('quests')}
                            className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all ${
                                activeTab === 'quests'
                                    ? 'bg-amber-500 text-black shadow-md shadow-amber-500/20 font-bold'
                                    : 'text-gray-400 hover:text-white'
                            }`}
                        >
                            <Compass size={14} />
                            <span>Manajemen Misi</span>
                            <span className={`px-1.5 py-0.2 rounded-full text-[10px] font-bold ${
                                activeTab === 'quests' ? 'bg-black/30 text-black' : 'bg-amber-500/20 text-amber-300'
                            }`}>
                                {questList.length}
                            </span>
                        </button>
                    </div>

                    {/* Search Field */}
                    <div className="relative min-w-[260px]">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500" size={15} />
                        <input
                            type="text"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            placeholder="Cari nama, WhatsApp, order no..."
                            className="w-full pl-9 pr-4 py-2 bg-[#121216] border border-white/10 rounded-xl text-xs text-white placeholder-gray-500 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all"
                        />
                    </div>
                </div>

                {/* TAB 1: PENDING USERS (STARTER KIT) */}
                {activeTab === 'pending' && (
                    <div className="space-y-4">
                        {filteredPending.length === 0 ? (
                            <div className="p-12 text-center bg-[#121216] rounded-2xl border border-white/5 space-y-2">
                                <CheckCircle2 className="w-12 h-12 text-emerald-400/40 mx-auto" />
                                <h3 className="text-base font-semibold text-white">Semua Verifikasi Tuntas</h3>
                                <p className="text-xs text-gray-400 max-w-sm mx-auto">
                                    Tidak ada pengguna yang sedang menunggu persetujuan verifikasi Starter Kit.
                                </p>
                            </div>
                        ) : (
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {filteredPending.map((user) => (
                                    <div 
                                        key={user.id}
                                        className="bg-[#121216] border border-amber-500/30 hover:border-amber-500/60 rounded-2xl p-5 space-y-4 shadow-xl hover:shadow-amber-500/5 transition-all flex flex-col justify-between"
                                    >
                                        <div className="space-y-3">
                                            <div className="flex items-start justify-between gap-2">
                                                <div>
                                                    <h3 className="font-bold text-sm text-white">{user.name}</h3>
                                                    <span className="text-xs font-mono text-gray-400">@{user.username || user.whatsapp}</span>
                                                </div>
                                                <span className={`text-[10px] font-bold px-2 py-0.5 rounded border uppercase tracking-wider ${getFactionColor(user.faction)}`}>
                                                    {user.faction}
                                                </span>
                                            </div>

                                            <div className="p-3 bg-black/40 rounded-xl border border-white/5 space-y-1.5 text-xs font-mono">
                                                <div className="flex justify-between">
                                                    <span className="text-gray-400">Paket Lisensi:</span>
                                                    <strong className="text-amber-400">{user.package_name}</strong>
                                                </div>
                                                <div className="flex justify-between">
                                                    <span className="text-gray-400">Harga Verifikasi:</span>
                                                    <span className="text-emerald-400 font-bold">{user.package_price}</span>
                                                </div>
                                                <div className="flex justify-between border-t border-white/5 pt-1">
                                                    <span className="text-gray-400">Injeksi Perak:</span>
                                                    <strong className="text-slate-300">+{user.silver_reward} Perak</strong>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="pt-2 flex items-center gap-2">
                                            <button
                                                onClick={() => handleApprove(user.id, user.name)}
                                                disabled={processingId === user.id}
                                                className="flex-1 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-black font-bold text-xs tracking-wide transition-all shadow-lg flex items-center justify-center gap-1.5 cursor-pointer"
                                            >
                                                {processingId === user.id ? (
                                                    <RefreshCw size={14} className="animate-spin" />
                                                ) : (
                                                    <CheckCircle2 size={14} />
                                                )}
                                                <span>Setujui Lisensi</span>
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                )}

                {/* TAB 2: ACTIVE SAUDAGARS */}
                {activeTab === 'active' && (
                    <div className="space-y-4">
                        {filteredActive.length === 0 ? (
                            <div className="p-12 text-center bg-[#121216] rounded-2xl border border-white/5">
                                <Users className="w-12 h-12 text-gray-600 mx-auto mb-2" />
                                <p className="text-sm text-gray-400">Tidak ada saudagar yang sesuai pencarian.</p>
                            </div>
                        ) : (
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {filteredActive.map((saudagar) => (
                                    <div
                                        key={saudagar.id}
                                        className="bg-[#121216] border border-white/10 hover:border-emerald-500/40 rounded-2xl p-5 space-y-4 transition-all group"
                                    >
                                        <div className="flex items-start justify-between gap-2">
                                            <div>
                                                <h3 className="font-bold text-sm text-white group-hover:text-emerald-300 transition-colors">{saudagar.name}</h3>
                                                <span className="text-xs font-mono text-gray-400">@{saudagar.username || saudagar.whatsapp}</span>
                                            </div>
                                            <span className={`text-[10px] font-bold px-2 py-0.5 rounded border uppercase tracking-wider ${getFactionColor(saudagar.faction)}`}>
                                                {saudagar.faction}
                                            </span>
                                        </div>

                                        <div className="grid grid-cols-2 gap-2 text-xs font-mono">
                                            <div className="p-2.5 rounded-xl bg-black/30 border border-white/5">
                                                <span className="text-gray-400 text-[10px] block">Saldo Emas</span>
                                                <strong className="text-amber-400 text-sm">{saudagar.gold_balance}</strong>
                                            </div>
                                            <div className="p-2.5 rounded-xl bg-black/30 border border-white/5">
                                                <span className="text-gray-400 text-[10px] block">Total Downline</span>
                                                <strong className="text-emerald-400 text-sm">{saudagar.total_downlines} Mitra</strong>
                                            </div>
                                        </div>

                                        <button
                                            onClick={() => handleOpenConsultation(saudagar)}
                                            className="w-full py-2 rounded-xl bg-white/5 hover:bg-white/10 text-amber-300 border border-amber-500/30 text-xs font-semibold flex items-center justify-center gap-1.5 transition-all cursor-pointer"
                                        >
                                            <MessageSquare size={14} />
                                            <span>Konsultasi & Pesan</span>
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

                {/* TAB 3: PRODUK & LAYANAN AFILIASI (ROOT OWNER CATALOG) */}
                {activeTab === 'services' && (
                    <div className="space-y-4">
                        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#121216] p-4 rounded-2xl border border-white/10">
                            <div>
                                <h2 className="text-sm font-bold text-white flex items-center gap-2">
                                    <Package size={16} className="text-purple-400" />
                                    Manajemen Seluruh Produk & Layanan Ekosistem
                                </h2>
                                <p className="text-xs text-gray-400">
                                    Sebagai Root Owner, Anda dapat mengelola layanan internal (Umroh/Produk) maupun eksternal (Unimart SaaS).
                                </p>
                            </div>

                            <div className="flex items-center gap-2">
                                <Link
                                    href="/marketing"
                                    className="px-3.5 py-2 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-xs font-semibold text-gray-300 transition-colors"
                                >
                                    Buka Portal Marketing
                                </Link>
                                <Link
                                    href="/marketing/services/create"
                                    className="px-4 py-2 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:brightness-110 text-white text-xs font-bold transition-all shadow-md flex items-center gap-1.5"
                                >
                                    <PlusCircle size={14} />
                                    <span>Tambah Produk Baru</span>
                                </Link>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {filteredServices.map((svc) => {
                                const isInternal = svc.service_type === 'INTERNAL';
                                const totalMargin = (svc.commission_rules?.levels || []).reduce(
                                    (sum, l) => sum + (Number(l.percentage) || 0),
                                    0
                                );

                                return (
                                    <div
                                        key={svc.id}
                                        className="bg-[#121216] border border-white/10 hover:border-purple-500/40 rounded-2xl p-5 space-y-4 transition-all flex flex-col justify-between"
                                    >
                                        <div className="space-y-3">
                                            <div className="flex items-start justify-between gap-2">
                                                <div className="flex items-center gap-2.5">
                                                    <div className="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center text-purple-400">
                                                        <span className="material-symbols-outlined text-xl">{svc.icon || 'hub'}</span>
                                                    </div>
                                                    <div>
                                                        <h3 className="font-bold text-sm text-white">{svc.name}</h3>
                                                        <span className="text-[10px] font-mono text-gray-400">{svc.code}</span>
                                                    </div>
                                                </div>

                                                <span
                                                    className={`px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase ${
                                                        isInternal
                                                            ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30'
                                                            : 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30'
                                                    }`}
                                                >
                                                    {svc.service_type}
                                                </span>
                                            </div>

                                            <p className="text-xs text-gray-400 line-clamp-2 leading-relaxed">
                                                {svc.description}
                                            </p>

                                            <div className="p-3 bg-black/40 rounded-xl border border-white/5 space-y-2 text-xs font-mono">
                                                <div className="flex justify-between items-center">
                                                    <span className="text-gray-400">Harga Paket:</span>
                                                    <div className="text-right">
                                                        <span className="text-amber-400 font-bold block">{svc.price_gold} Emas</span>
                                                        <span className="text-[10px] text-emerald-400">{formatRupiah(svc.price_fiat)}</span>
                                                    </div>
                                                </div>

                                                <div className="flex justify-between border-t border-white/5 pt-1.5 text-[11px]">
                                                    <span className="text-gray-400">Skema Cicilan:</span>
                                                    <span className={svc.allows_installments ? 'text-indigo-400 font-bold' : 'text-gray-500'}>
                                                        {svc.allows_installments ? `Bisa Dicicil (Min DP: ${svc.min_down_payment_gold || 0} Emas)` : 'Bayar Lunas'}
                                                    </span>
                                                </div>

                                                <div className="flex justify-between border-t border-white/5 pt-1.5 text-[11px]">
                                                    <span className="text-gray-400">Afiliasi ({svc.commission_rules?.depth || 0} Lvl):</span>
                                                    <span className="text-purple-300 font-bold">
                                                        {totalMargin.toFixed(1)}% Total Margin
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="pt-2 flex items-center gap-2 border-t border-white/5">
                                            <div className="flex-1 text-[11px] font-mono text-gray-400">
                                                {svc.orders_count || 0} Pesanan • {svc.licenses_count || 0} Lisensi
                                            </div>

                                            <Link
                                                href={`/marketing/services/${svc.id}/edit`}
                                                className="px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-amber-300 border border-white/10 text-xs font-semibold transition-colors flex items-center gap-1"
                                            >
                                                <Edit3 size={13} />
                                                <span>Edit</span>
                                            </Link>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                )}

                {/* TAB 4: PESANAN & CICILAN BERJALAN */}
                {activeTab === 'orders' && (
                    <div className="space-y-4">
                        <div className="bg-[#121216] border border-white/10 rounded-2xl overflow-hidden shadow-2xl">
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-xs">
                                    <thead>
                                        <tr className="bg-black/40 text-gray-400 border-b border-white/10 font-mono uppercase text-[10px]">
                                            <th className="px-4 py-3.5">Order No / Waktu</th>
                                            <th className="px-4 py-3.5">Layanan / Produk</th>
                                            <th className="px-4 py-3.5">Peserta</th>
                                            <th className="px-4 py-3.5">Metode Bayar</th>
                                            <th className="px-4 py-3.5 text-right">Total Tagihan</th>
                                            <th className="px-4 py-3.5 text-right">Sudah Dibayar</th>
                                            <th className="px-4 py-3.5 text-center">Status</th>
                                            <th className="px-4 py-3.5 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-white/5">
                                        {filteredOrders.length === 0 ? (
                                            <tr>
                                                <td colSpan={8} className="px-4 py-12 text-center text-gray-500">
                                                    Belum ada data pesanan layanan.
                                                </td>
                                            </tr>
                                        ) : (
                                            filteredOrders.map((order) => {
                                                const isInstallment = order.payment_type === 'INSTALLMENT';
                                                const isCompleted = order.status === 'COMPLETED';

                                                return (
                                                    <tr key={order.id} className="hover:bg-white/[0.02] transition-colors">
                                                        <td className="px-4 py-3.5 font-mono">
                                                            <strong className="text-white block">{order.order_number}</strong>
                                                            <span className="text-[10px] text-gray-500">{formatDate(order.created_at)}</span>
                                                        </td>
                                                        <td className="px-4 py-3.5">
                                                            <strong className="text-white block">{order.service?.name || '-'}</strong>
                                                            <span className="text-[10px] font-mono text-purple-400">{order.service?.code}</span>
                                                        </td>
                                                        <td className="px-4 py-3.5">
                                                            <span className="font-semibold text-white block">{order.user?.name}</span>
                                                            <span className="text-[10px] text-gray-400 font-mono">@{order.user?.username || order.user?.whatsapp}</span>
                                                        </td>
                                                        <td className="px-4 py-3.5">
                                                            <span
                                                                className={`px-2 py-0.5 rounded text-[10px] font-bold font-mono ${
                                                                    isInstallment
                                                                        ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30'
                                                                        : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'
                                                                }`}
                                                            >
                                                                {isInstallment ? 'CICILAN' : 'LUNAS'}
                                                            </span>
                                                        </td>
                                                        <td className="px-4 py-3.5 text-right font-mono font-bold text-amber-400">
                                                            {Number(order.total_amount_gold).toLocaleString('id-ID')} Emas
                                                        </td>
                                                        <td className="px-4 py-3.5 text-right font-mono">
                                                            <strong className="text-white block">{Number(order.paid_amount_gold).toLocaleString('id-ID')} Emas</strong>
                                                            {isInstallment && !isCompleted && (
                                                                <span className="text-[10px] text-red-400">
                                                                    Sisa: {Number(order.remaining_amount_gold).toLocaleString('id-ID')} Emas
                                                                </span>
                                                            )}
                                                        </td>
                                                        <td className="px-4 py-3.5 text-center">
                                                            <span
                                                                className={`px-2 py-0.5 rounded-full text-[10px] font-bold font-mono uppercase ${
                                                                    isCompleted
                                                                        ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                                                                        : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
                                                                }`}
                                                            >
                                                                {order.status}
                                                            </span>
                                                        </td>
                                                        <td className="px-4 py-3.5 text-center">
                                                            <button
                                                                onClick={() => setSelectedOrderModal(order)}
                                                                className="px-2.5 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white border border-white/10 text-[11px] font-semibold transition-all cursor-pointer inline-flex items-center gap-1"
                                                            >
                                                                <Receipt size={13} />
                                                                Detail
                                                            </button>
                                                        </td>
                                                    </tr>
                                                );
                                            })
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                )}

                {/* TAB 5: AUDIT TRAIL DISTRIBUSI KOMISI AFILIASI */}
                {activeTab === 'commissions' && (
                    <div className="space-y-4">
                        <div className="bg-[#121216] border border-white/10 rounded-2xl overflow-hidden shadow-2xl">
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-xs">
                                    <thead>
                                        <tr className="bg-black/40 text-gray-400 border-b border-white/10 font-mono uppercase text-[10px]">
                                            <th className="px-4 py-3.5">Waktu Payout</th>
                                            <th className="px-4 py-3.5">Penerima Komisi (Upline)</th>
                                            <th className="px-4 py-3.5">Sumber Transaksi (Downline)</th>
                                            <th className="px-4 py-3.5">Layanan</th>
                                            <th className="px-4 py-3.5 text-center">Generasi</th>
                                            <th className="px-4 py-3.5 text-center">Rate (%)</th>
                                            <th className="px-4 py-3.5 text-right">Payout Komisi</th>
                                            <th className="px-4 py-3.5 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-white/5">
                                        {recentCommissions.length === 0 ? (
                                            <tr>
                                                <td colSpan={8} className="px-4 py-12 text-center text-gray-500">
                                                    Belum ada komisi afiliasi yang didistribusikan.
                                                </td>
                                            </tr>
                                        ) : (
                                            recentCommissions.map((comm) => (
                                                <tr key={comm.id} className="hover:bg-white/[0.02] transition-colors">
                                                    <td className="px-4 py-3.5 font-mono text-[11px] text-gray-400">
                                                        {formatDate(comm.created_at)}
                                                    </td>
                                                    <td className="px-4 py-3.5">
                                                        <strong className="text-white block">{comm.upline_user?.name || '-'}</strong>
                                                        <span className="text-[10px] text-gray-400 font-mono">@{comm.upline_user?.username || comm.upline_user?.whatsapp}</span>
                                                    </td>
                                                    <td className="px-4 py-3.5">
                                                        <span className="text-white block">{comm.beneficiary_user?.name || '-'}</span>
                                                        <span className="text-[10px] text-gray-400 font-mono">@{comm.beneficiary_user?.username}</span>
                                                    </td>
                                                    <td className="px-4 py-3.5 font-semibold text-white">
                                                        {comm.service?.name}
                                                    </td>
                                                    <td className="px-4 py-3.5 text-center">
                                                        <span className="px-2 py-0.5 rounded bg-purple-500/20 text-purple-300 font-mono font-bold text-[10px] border border-purple-500/30">
                                                            Level {comm.level}
                                                        </span>
                                                    </td>
                                                    <td className="px-4 py-3.5 text-center font-mono font-bold text-purple-300">
                                                        {comm.percentage}%
                                                    </td>
                                                    <td className="px-4 py-3.5 text-right font-mono font-bold text-amber-400">
                                                        +{Number(comm.amount_gold).toFixed(2)} Emas
                                                    </td>
                                                    <td className="px-4 py-3.5 text-center">
                                                        <span className="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                            {comm.status}
                                                        </span>
                                                    </td>
                                                </tr>
                                            ))
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                )}

                {/* TAB 6: MANAJEMEN MISI PENGGUNA (QUEST CRUD) */}
                {activeTab === 'quests' && (
                    <div className="space-y-6">
                        {/* Header Panel */}
                        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-[#121216] p-5 rounded-2xl border border-white/10">
                            <div>
                                <div className="flex items-center gap-2 mb-1">
                                    <Compass className="text-amber-400" size={18} />
                                    <h3 className="text-base font-bold text-white">Pusat Kendali Misi Pengguna (Quest Engine)</h3>
                                </div>
                                <p className="text-xs text-gray-400">
                                    Kelola seluruh misi harian, ekspedisi waktu, dan penugasan persona untuk para saudagar di dashboard pengguna.
                                </p>
                            </div>

                            <button
                                onClick={() => {
                                    setEditingQuest(null);
                                    setQuestFormData({
                                        title: '',
                                        description: '',
                                        quest_category: 'DEFAULT',
                                        time_window: 'ALL',
                                        target_persona: 'ALL',
                                        stake_amount_silver: 0,
                                        reward_amount_gold: 10,
                                        time_limit_hours: 24,
                                        min_level: 1,
                                        is_active: true,
                                        parameters: {
                                            action_type: 'RECRUIT_MEMBERS',
                                            target_threshold: 5,
                                        },
                                    });
                                    setQuestError(null);
                                    setQuestModalOpen(true);
                                }}
                                className="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-black font-bold text-xs flex items-center justify-center gap-2 transition-all shadow-lg shadow-amber-500/20 cursor-pointer"
                            >
                                <Plus size={15} />
                                <span>+ Buat Misi Baru</span>
                            </button>
                        </div>

                        {/* Category Filter Pills */}
                        <div className="flex items-center gap-2 overflow-x-auto pb-1">
                            <button
                                onClick={() => setSelectedQuestCategory('ALL')}
                                className={`px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all ${
                                    selectedQuestCategory === 'ALL'
                                        ? 'bg-white/15 text-white border border-white/20'
                                        : 'bg-[#121216] text-gray-400 hover:text-white border border-white/5'
                                }`}
                            >
                                Semua Misi ({questList.length})
                            </button>
                            <button
                                onClick={() => setSelectedQuestCategory('DEFAULT')}
                                className={`px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all ${
                                    selectedQuestCategory === 'DEFAULT'
                                        ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40'
                                        : 'bg-[#121216] text-gray-400 hover:text-white border border-white/5'
                                }`}
                            >
                                Tugas Default ({questList.filter(q => q.quest_category === 'DEFAULT').length})
                            </button>
                            <button
                                onClick={() => setSelectedQuestCategory('TIME_WINDOW')}
                                className={`px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all ${
                                    selectedQuestCategory === 'TIME_WINDOW'
                                        ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40'
                                        : 'bg-[#121216] text-gray-400 hover:text-white border border-white/5'
                                }`}
                            >
                                Horizon Waktu ({questList.filter(q => q.quest_category === 'TIME_WINDOW').length})
                            </button>
                            <button
                                onClick={() => setSelectedQuestCategory('PERSONA_SPECIFIC')}
                                className={`px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all ${
                                    selectedQuestCategory === 'PERSONA_SPECIFIC'
                                        ? 'bg-purple-500/20 text-purple-300 border border-purple-500/40'
                                        : 'bg-[#121216] text-gray-400 hover:text-white border border-white/5'
                                }`}
                            >
                                Spesifik Persona ({questList.filter(q => q.quest_category === 'PERSONA_SPECIFIC').length})
                            </button>
                        </div>

                        {/* Quests Grid */}
                        {questList
                            .filter(q => {
                                const matchesCategory = selectedQuestCategory === 'ALL' || q.quest_category === selectedQuestCategory;
                                const matchesSearch = !searchQuery || 
                                    q.title.toLowerCase().includes(searchQuery.toLowerCase()) || 
                                    q.description.toLowerCase().includes(searchQuery.toLowerCase()) ||
                                    (q.target_persona && q.target_persona.toLowerCase().includes(searchQuery.toLowerCase()));
                                return matchesCategory && matchesSearch;
                            }).length === 0 ? (
                            <div className="bg-[#121216] rounded-2xl border border-white/10 p-12 text-center">
                                <Compass className="mx-auto text-gray-600 mb-3" size={36} />
                                <h4 className="text-sm font-bold text-white mb-1">Tidak Ada Misi Ditemukan</h4>
                                <p className="text-xs text-gray-400 max-w-sm mx-auto">
                                    Belum ada misi yang terdaftar sesuai filter kategori atau kata kunci pencarian Anda.
                                </p>
                            </div>
                        ) : (
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {questList
                                    .filter(q => {
                                        const matchesCategory = selectedQuestCategory === 'ALL' || q.quest_category === selectedQuestCategory;
                                        const matchesSearch = !searchQuery || 
                                            q.title.toLowerCase().includes(searchQuery.toLowerCase()) || 
                                            q.description.toLowerCase().includes(searchQuery.toLowerCase()) ||
                                            (q.target_persona && q.target_persona.toLowerCase().includes(searchQuery.toLowerCase()));
                                        return matchesCategory && matchesSearch;
                                    })
                                    .map(q => {
                                        const isActivelyWorking = (q.active_participants_count || 0) > 0;
                                        return (
                                            <div
                                                key={q.id}
                                                className={`p-5 rounded-2xl border transition-all flex flex-col justify-between ${
                                                    q.is_active 
                                                        ? 'bg-[#121216] border-white/10 hover:border-amber-500/40 shadow-lg shadow-black/40' 
                                                        : 'bg-[#0f0f13] border-white/5 opacity-70'
                                                }`}
                                            >
                                                <div>
                                                    {/* Top Badges */}
                                                    <div className="flex items-center justify-between gap-2 mb-3">
                                                        <div className="flex items-center gap-1.5 flex-wrap">
                                                            <span className={`text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border ${
                                                                q.quest_category === 'DEFAULT'
                                                                    ? 'bg-blue-500/10 text-blue-300 border-blue-500/30'
                                                                    : q.quest_category === 'TIME_WINDOW'
                                                                    ? 'bg-amber-500/10 text-amber-300 border-amber-500/30'
                                                                    : 'bg-purple-500/10 text-purple-300 border-purple-500/30'
                                                            }`}>
                                                                {q.quest_category === 'DEFAULT' ? 'TUGAS HARIAN' : q.quest_category === 'TIME_WINDOW' ? `WAKTU: ${q.time_window}` : `PERSONA: ${q.target_persona}`}
                                                            </span>

                                                            <span className="text-[10px] font-mono px-1.5 py-0.5 rounded bg-white/5 border border-white/10 text-gray-400">
                                                                Min. Lv.{q.min_level || 1}
                                                            </span>
                                                        </div>

                                                        {/* Status Chip */}
                                                        <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full border ${
                                                            q.is_active
                                                                ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30'
                                                                : 'bg-gray-800 text-gray-400 border-gray-700'
                                                        }`}>
                                                            {q.is_active ? 'AKTIF' : 'NONAKTIF'}
                                                        </span>
                                                    </div>

                                                    {/* Title & Description */}
                                                    <h4 className="font-bold text-sm text-white leading-snug mb-1">
                                                        {q.title}
                                                    </h4>
                                                    <p className="text-xs text-gray-400 line-clamp-2 leading-relaxed mb-4">
                                                        {q.description}
                                                    </p>

                                                    {/* Parameters Box */}
                                                    <div className="grid grid-cols-2 gap-2 p-3 rounded-xl bg-black/40 border border-white/5 text-xs mb-4">
                                                        <div>
                                                            <span className="text-[10px] text-gray-500 block uppercase tracking-wider">Stake Perak</span>
                                                            <strong className="text-slate-200 font-mono text-xs">
                                                                {q.stake_amount_silver > 0 ? `${Number(q.stake_amount_silver).toLocaleString('id-ID')} P` : 'Tanpa Stake'}
                                                            </strong>
                                                        </div>
                                                        <div>
                                                            <span className="text-[10px] text-gray-500 block uppercase tracking-wider">Hadiah Emas</span>
                                                            <strong className="text-amber-400 font-mono text-xs">
                                                                +{q.reward_amount_gold} Emas
                                                            </strong>
                                                        </div>
                                                        <div>
                                                            <span className="text-[10px] text-gray-500 block uppercase tracking-wider">Limit Durasi</span>
                                                            <span className="text-gray-300 font-mono text-xs">
                                                                {q.time_limit_hours} Jam
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <span className="text-[10px] text-gray-500 block uppercase tracking-wider">Riwayat</span>
                                                            <span className="text-gray-300 font-mono text-xs">
                                                                {q.total_attempts_count || 0}x Diambil
                                                            </span>
                                                        </div>
                                                    </div>

                                                    {/* Target Parameter Sistem */}
                                                    <div className="px-3 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-between text-[11px] mb-4">
                                                        <span className="flex items-center gap-1.5 text-emerald-400 font-semibold">
                                                            <Target size={13} className="text-emerald-400" />
                                                            <span>Target Sistem</span>
                                                        </span>
                                                        <span className="font-bold text-emerald-300 font-mono">
                                                            {q.parameters?.action_type === 'RECRUIT_MEMBERS' ? `${q.parameters.target_threshold ?? 1} Referral Baru` :
                                                             q.parameters?.action_type === 'REGISTER_MERCHANT_UNIMART' ? `${q.parameters.target_threshold ?? 1} Toko Unimart` :
                                                             q.parameters?.action_type === 'REGISTER_WALIKELAS_TEACHER' ? `${q.parameters.target_threshold ?? 1} Guru WaliKelas` :
                                                             q.parameters?.action_type === 'DIGITAL_PPOB_TRANSACTION' ? `${q.parameters.target_threshold ?? 1} Transaksi PPOB` :
                                                             q.parameters?.action_type === 'DAILY_CHECKIN_STREAK' ? `${q.parameters.target_threshold ?? 1} Hari Check-in` :
                                                             q.parameters?.action_type === 'MANUAL_PROOF_SUBMISSION' ? 'Verifikasi Bukti' :
                                                             `${q.parameters?.target_threshold ?? 1} Aksi`}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div>
                                                    {/* Active Participants Alert Chip */}
                                                    <div className="mb-4">
                                                        {isActivelyWorking ? (
                                                            <div className="px-3 py-1.5 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-between text-xs animate-pulse">
                                                                <span className="flex items-center gap-1.5 text-amber-300 font-bold text-[11px]">
                                                                    <Flame size={13} className="text-amber-400" />
                                                                    <span>{q.active_participants_count} Pengguna Aktif</span>
                                                                </span>
                                                                <span className="text-[10px] text-amber-400/80 uppercase font-mono">Sedang Berjalan</span>
                                                            </div>
                                                        ) : (
                                                            <div className="px-3 py-1.5 rounded-xl bg-white/5 border border-white/5 flex items-center justify-between text-xs text-gray-500">
                                                                <span className="text-[11px]">0 Peserta Aktif</span>
                                                                <span className="text-[10px] text-emerald-400/70">Aman Dihapus</span>
                                                            </div>
                                                        )}
                                                    </div>

                                                    {/* Actions Row */}
                                                    <div className="flex items-center justify-between pt-3 border-t border-white/10 gap-2">
                                                        <button
                                                            onClick={async () => {
                                                                const newStatus = !q.is_active;
                                                                try {
                                                                    const res = await fetch(`/admin/quests/${q.id}`, {
                                                                        method: 'PUT',
                                                                        headers: {
                                                                            'Content-Type': 'application/json',
                                                                            'Accept': 'application/json',
                                                                            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                                                                        },
                                                                        body: JSON.stringify({
                                                                            title: q.title,
                                                                            description: q.description,
                                                                            quest_category: q.quest_category,
                                                                            time_window: q.time_window || 'ALL',
                                                                            target_persona: q.target_persona || 'ALL',
                                                                            stake_amount_silver: q.stake_amount_silver,
                                                                            reward_amount_gold: q.reward_amount_gold,
                                                                            time_limit_hours: q.time_limit_hours,
                                                                            min_level: q.min_level || 1,
                                                                            is_active: newStatus,
                                                                        }),
                                                                    });
                                                                    const data = await res.json();
                                                                    if (res.ok && data.success) {
                                                                        setQuestList(prev => prev.map(item => item.id === q.id ? { ...item, is_active: newStatus } : item));
                                                                    }
                                                                } catch (err) {
                                                                    console.error(err);
                                                                }
                                                            }}
                                                            className={`px-2.5 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1 transition-all ${
                                                                q.is_active 
                                                                    ? 'bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-300 border border-emerald-500/30'
                                                                    : 'bg-white/5 hover:bg-white/10 text-gray-400 border border-white/10'
                                                            }`}
                                                            title={q.is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan'}
                                                        >
                                                            {q.is_active ? <Check size={13} /> : <X size={13} />}
                                                            <span>{q.is_active ? 'Aktif' : 'Mati'}</span>
                                                        </button>

                                                        <div className="flex items-center gap-1.5">
                                                            <button
                                                                onClick={() => {
                                                                    setEditingQuest(q);
                                                                    setQuestFormData({
                                                                        title: q.title,
                                                                        description: q.description,
                                                                        quest_category: q.quest_category,
                                                                        time_window: q.time_window || 'ALL',
                                                                        target_persona: q.target_persona || 'ALL',
                                                                        stake_amount_silver: q.stake_amount_silver,
                                                                        reward_amount_gold: q.reward_amount_gold,
                                                                        time_limit_hours: q.time_limit_hours,
                                                                        min_level: q.min_level || 1,
                                                                        is_active: q.is_active,
                                                                        parameters: {
                                                                            action_type: q.parameters?.action_type || 'RECRUIT_MEMBERS',
                                                                            target_threshold: q.parameters?.target_threshold || 1,
                                                                        },
                                                                    });
                                                                    setQuestError(null);
                                                                    setQuestModalOpen(true);
                                                                }}
                                                                className="p-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white border border-white/10 transition-colors"
                                                                title="Edit Misi"
                                                            >
                                                                <Edit3 size={14} />
                                                            </button>

                                                            {isActivelyWorking ? (
                                                                <button
                                                                    type="button"
                                                                    onClick={() => alert(`Misi "${q.title}" TIDAK DAPAT DIHAPUS karena saat ini ada ${q.active_participants_count} pengguna yang sedang aktif mengerjakannya. Silakan tunggu hingga ekspedisi selesai atau nonaktifkan misi terlebih dahulu.`)}
                                                                    className="p-1.5 rounded-lg bg-red-950/20 text-red-500/40 border border-red-900/20 cursor-not-allowed"
                                                                    title={`Terkunci: Sedang dikerjakan oleh ${q.active_participants_count} pengguna`}
                                                                >
                                                                    <Trash2 size={14} />
                                                                </button>
                                                            ) : (
                                                                <button
                                                                    type="button"
                                                                    onClick={() => setDeleteQuestConfirm(q)}
                                                                    className="p-1.5 rounded-lg bg-red-950/30 hover:bg-red-900/50 text-red-300 border border-red-800/40 transition-colors cursor-pointer"
                                                                    title="Hapus Misi"
                                                                >
                                                                    <Trash2 size={14} />
                                                                </button>
                                                            )}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })}
                            </div>
                        )}
                    </div>
                )}
            </main>

            {/* MODAL: DETAIL RIWAYAT CICILAN */}
            {selectedOrderModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="bg-[#121216] border border-white/15 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5">
                        <div className="flex items-center justify-between border-b border-white/10 pb-3">
                            <div>
                                <h3 className="text-sm font-bold text-white flex items-center gap-2">
                                    <Receipt size={16} className="text-amber-400" />
                                    Riwayat Pembayaran Cicilan
                                </h3>
                                <p className="text-[11px] text-gray-400 font-mono">
                                    {selectedOrderModal.order_number} ({selectedOrderModal.service?.name})
                                </p>
                            </div>
                            <button
                                onClick={() => setSelectedOrderModal(null)}
                                className="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white flex items-center justify-center transition-colors cursor-pointer"
                            >
                                <X size={16} />
                            </button>
                        </div>

                        <div className="p-3.5 bg-black/40 border border-white/5 rounded-xl grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <span className="text-[10px] uppercase font-mono text-gray-500 block">Peserta</span>
                                <strong className="text-white block">{selectedOrderModal.user?.name}</strong>
                                <span className="text-[10px] text-gray-400 font-mono">+{selectedOrderModal.user?.whatsapp}</span>
                            </div>
                            <div className="text-right">
                                <span className="text-[10px] uppercase font-mono text-gray-500 block">Skema Pembayaran</span>
                                <span className="font-bold text-amber-400 block font-mono">
                                    {selectedOrderModal.payment_type === 'INSTALLMENT' ? 'BERTAHAP (CICILAN)' : 'LUNAS PENUH'}
                                </span>
                                <span className="text-[10px] font-mono text-emerald-400">
                                    Status: {selectedOrderModal.status}
                                </span>
                            </div>
                        </div>

                        <div>
                            <span className="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-2 font-mono">
                                Daftar Termin Terbayar ({selectedOrderModal.installments?.length || 0})
                            </span>
                            <div className="space-y-2 max-h-60 overflow-y-auto pr-1">
                                {(selectedOrderModal.installments || []).map((inst) => (
                                    <div
                                        key={inst.id}
                                        className="p-3 bg-white/[0.02] border border-white/5 rounded-xl flex items-center justify-between text-xs"
                                    >
                                        <div>
                                            <strong className="text-white block font-mono">Termin #{inst.installment_number}</strong>
                                            <span className="text-[10px] text-gray-500 font-mono">{formatDate(inst.paid_at)}</span>
                                        </div>
                                        <div className="text-right font-mono">
                                            <strong className="text-amber-400 block font-bold">
                                                {Number(inst.amount_gold).toLocaleString('id-ID')} Emas
                                            </strong>
                                            <span className="text-[10px] text-emerald-400">KOMISI DIBAGIKAN</span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="pt-2 flex justify-end">
                            <button
                                onClick={() => setSelectedOrderModal(null)}
                                className="px-5 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-white text-xs font-bold transition-all cursor-pointer"
                            >
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            )}

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
                                className="text-gray-400 hover:text-white p-1 rounded-lg hover:bg-white/5 transition-colors cursor-pointer"
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
                                className="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 disabled:opacity-40 text-black font-bold text-xs flex items-center gap-1.5 transition-colors shadow-md cursor-pointer"
                            >
                                <Send size={14} />
                                <span>Kirim</span>
                            </button>
                        </form>
                    </div>
                </div>
            )}
            {/* MODAL: CREATE / EDIT QUEST */}
            {questModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md animate-in fade-in duration-200">
                    <div className="bg-[#121216] border border-amber-500/40 rounded-2xl w-full max-w-xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
                        <div className="p-4 border-b border-white/10 flex items-center justify-between bg-[#16161c]">
                            <div className="flex items-center gap-2">
                                <Compass className="text-amber-400" size={18} />
                                <h3 className="font-bold text-sm text-white">
                                    {editingQuest ? 'Edit Data Misi Pengguna' : 'Tambah Misi Pengguna Baru'}
                                </h3>
                            </div>
                            <button
                                onClick={() => setQuestModalOpen(false)}
                                className="text-gray-400 hover:text-white transition-colors"
                            >
                                <X size={18} />
                            </button>
                        </div>

                        <form
                            onSubmit={async (e) => {
                                e.preventDefault();
                                setQuestSubmitting(true);
                                setQuestError(null);
                                try {
                                    const url = editingQuest ? `/admin/quests/${editingQuest.id}` : '/admin/quests';
                                    const method = editingQuest ? 'PUT' : 'POST';
                                    const res = await fetch(url, {
                                        method,
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                                        },
                                        body: JSON.stringify(questFormData),
                                    });
                                    const data = await res.json();
                                    if (res.ok && data.success) {
                                        if (editingQuest) {
                                            setQuestList(prev => prev.map(item => item.id === editingQuest.id ? { ...item, ...data.quest } : item));
                                        } else {
                                            setQuestList(prev => [data.quest, ...prev]);
                                        }
                                        setQuestModalOpen(false);
                                    } else {
                                        setQuestError(data.error || data.message || 'Gagal menyimpan misi.');
                                    }
                                } catch (err: any) {
                                    setQuestError('Terjadi kesalahan koneksi.');
                                } finally {
                                    setQuestSubmitting(false);
                                }
                            }}
                            className="p-5 space-y-4 overflow-y-auto text-xs"
                        >
                            {questError && (
                                <div className="p-3 rounded-xl bg-red-950/40 border border-red-500/40 text-red-300 text-xs flex items-center gap-2">
                                    <AlertCircle size={15} />
                                    <span>{questError}</span>
                                </div>
                            )}

                            <div>
                                <label className="block text-gray-300 font-semibold mb-1">Judul Misi *</label>
                                <input
                                    type="text"
                                    required
                                    value={questFormData.title}
                                    onChange={(e) => setQuestFormData({ ...questFormData, title: e.target.value })}
                                    placeholder="Contoh: Ekspedisi Survei Warung Sembako"
                                    className="w-full bg-[#0e0e12] border border-white/10 rounded-xl px-3.5 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-amber-500"
                                />
                            </div>

                            <div>
                                <label className="block text-gray-300 font-semibold mb-1">Deskripsi & Panduan Misi *</label>
                                <textarea
                                    required
                                    rows={3}
                                    value={questFormData.description}
                                    onChange={(e) => setQuestFormData({ ...questFormData, description: e.target.value })}
                                    placeholder="Jelaskan aktivitas yang perlu diselesaikan oleh saudagar..."
                                    className="w-full bg-[#0e0e12] border border-white/10 rounded-xl px-3.5 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-amber-500 resize-none"
                                />
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-gray-300 font-semibold mb-1">Kategori Misi *</label>
                                    <select
                                        value={questFormData.quest_category}
                                        onChange={(e) => setQuestFormData({ ...questFormData, quest_category: e.target.value as any })}
                                        className="w-full bg-[#0e0e12] border border-white/10 rounded-xl px-3 py-2.5 text-white focus:outline-none focus:border-amber-500"
                                    >
                                        <option value="DEFAULT">DEFAULT (Tugas Umum Seluruh Saudagar)</option>
                                        <option value="TIME_WINDOW">TIME_WINDOW (Horizon Waktu)</option>
                                        <option value="PERSONA_SPECIFIC">PERSONA_SPECIFIC (Persona Khusus)</option>
                                    </select>
                                </div>

                                {questFormData.quest_category === 'TIME_WINDOW' && (
                                    <div>
                                        <label className="block text-gray-300 font-semibold mb-1">Horizon Waktu Aktif</label>
                                        <select
                                            value={questFormData.time_window}
                                            onChange={(e) => setQuestFormData({ ...questFormData, time_window: e.target.value as any })}
                                            className="w-full bg-[#0e0e12] border border-white/10 rounded-xl px-3 py-2.5 text-white focus:outline-none focus:border-amber-500"
                                        >
                                            <option value="ALL">Semua Waktu (ALL)</option>
                                            <option value="MORNING">Fajar / Pagi (MORNING)</option>
                                            <option value="AFTERNOON">Siang / Panas Terik (AFTERNOON)</option>
                                            <option value="EVENING">Senja / Menjelang Maghrib (EVENING)</option>
                                            <option value="NIGHT">Malam / Pasca Isya (NIGHT)</option>
                                        </select>
                                    </div>
                                )}

                                {questFormData.quest_category === 'PERSONA_SPECIFIC' && (
                                    <div>
                                        <label className="block text-gray-300 font-semibold mb-1">Target Persona Saudagar</label>
                                        <input
                                            type="text"
                                            value={questFormData.target_persona}
                                            onChange={(e) => setQuestFormData({ ...questFormData, target_persona: e.target.value })}
                                            placeholder="Contoh: UMKM, TRADER, AGEN, KARAVAN"
                                            className="w-full bg-[#0e0e12] border border-white/10 rounded-xl px-3 py-2.5 text-white focus:outline-none focus:border-amber-500"
                                        />
                                    </div>
                                )}
                            </div>

                            {/* PARAMETER SISTEM (PENENTU KEMENANGAN OTOMATIS) */}
                            <div className="p-3.5 bg-amber-500/5 border border-amber-500/20 rounded-xl space-y-3">
                                <div className="flex items-center justify-between">
                                    <label className="text-amber-400 font-bold flex items-center gap-1.5 text-xs">
                                        <Target size={14} />
                                        <span>Parameter Target Kemenangan (Otomatis Sistem)</span>
                                    </label>
                                    <span className="text-[10px] text-gray-400 font-mono">Auto Evaluator</span>
                                </div>
                                
                                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div className="sm:col-span-2">
                                        <label className="block text-gray-300 text-[11px] mb-1">Aksi Sasaran Sistem</label>
                                        <select
                                            value={questFormData.parameters?.action_type || 'RECRUIT_MEMBERS'}
                                            onChange={(e) => setQuestFormData({
                                                ...questFormData,
                                                parameters: {
                                                    ...questFormData.parameters,
                                                    action_type: e.target.value,
                                                }
                                            })}
                                            className="w-full bg-[#0e0e12] border border-white/10 rounded-xl px-3 py-2 text-white text-xs focus:outline-none focus:border-amber-500"
                                        >
                                            <option value="RECRUIT_MEMBERS">Rekrut Anggota Baru (Referral Downline)</option>
                                            <option value="REGISTER_MERCHANT_UNIMART">Daftarkan Toko / Bisnis ke Unimart POS</option>
                                            <option value="REGISTER_WALIKELAS_TEACHER">Daftarkan Guru / Pendidik ke WaliKelas</option>
                                            <option value="DIGITAL_PPOB_TRANSACTION">Transaksi Produk Digital / PPOB</option>
                                            <option value="DAILY_CHECKIN_STREAK">Check-in Harian Berturut-turut</option>
                                            <option value="MANUAL_PROOF_SUBMISSION">Verifikasi Bukti Lapangan / Khusus</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-gray-300 text-[11px] mb-1">Target Sasaran</label>
                                        <input
                                            type="number"
                                            min="1"
                                            required
                                            value={questFormData.parameters?.target_threshold || 1}
                                            onChange={(e) => setQuestFormData({
                                                ...questFormData,
                                                parameters: {
                                                    ...questFormData.parameters,
                                                    target_threshold: Math.max(1, Number(e.target.value)),
                                                }
                                            })}
                                            className="w-full bg-[#0e0e12] border border-white/10 rounded-xl px-3 py-2 text-emerald-400 font-mono font-bold text-xs"
                                        />
                                    </div>
                                </div>

                                <p className="text-[11px] text-gray-400">
                                    {questFormData.parameters?.action_type === 'RECRUIT_MEMBERS' && `Sistem akan otomatis memvalidasi kemenangan jika pengguna berhasil merekrut ${questFormData.parameters?.target_threshold || 1} anggota baru selama batas waktu misi.`}
                                    {questFormData.parameters?.action_type === 'REGISTER_MERCHANT_UNIMART' && `Sistem akan memvalidasi kemenangan jika pengguna berhasil mendaftarkan ${questFormData.parameters?.target_threshold || 1} toko aktif ke Unimart POS.`}
                                    {questFormData.parameters?.action_type === 'REGISTER_WALIKELAS_TEACHER' && `Sistem akan memvalidasi kemenangan jika pengguna berhasil mendaftarkan ${questFormData.parameters?.target_threshold || 1} guru/pendidik ke WaliKelas.`}
                                    {questFormData.parameters?.action_type === 'DIGITAL_PPOB_TRANSACTION' && `Sistem akan memvalidasi kemenangan jika pengguna menyelesaikan ${questFormData.parameters?.target_threshold || 1} transaksi produk digital (pulsa/PLN).`}
                                    {questFormData.parameters?.action_type === 'DAILY_CHECKIN_STREAK' && `Sistem akan memvalidasi kemenangan jika pengguna melakukan check-in selama ${questFormData.parameters?.target_threshold || 1} hari.`}
                                    {questFormData.parameters?.action_type === 'MANUAL_PROOF_SUBMISSION' && `Misi ini membutuhkan verifikasi bukti foto/survei manual dari Admin atau Saudagar Pembina.`}
                                </p>
                            </div>

                            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div>
                                    <label className="block text-gray-300 font-semibold mb-1">Stake Perak</label>
                                    <input
                                        type="number"
                                        min="0"
                                        value={questFormData.stake_amount_silver}
                                        onChange={(e) => setQuestFormData({ ...questFormData, stake_amount_silver: Number(e.target.value) })}
                                        className="w-full bg-[#0e0e12] border border-white/10 rounded-xl px-3 py-2 text-white font-mono"
                                    />
                                    <span className="text-[10px] text-gray-500">0 = Gratis</span>
                                </div>

                                <div>
                                    <label className="block text-gray-300 font-semibold mb-1">Reward Emas *</label>
                                    <input
                                        type="number"
                                        min="1"
                                        required
                                        value={questFormData.reward_amount_gold}
                                        onChange={(e) => setQuestFormData({ ...questFormData, reward_amount_gold: Number(e.target.value) })}
                                        className="w-full bg-[#0e0e12] border border-white/10 rounded-xl px-3 py-2 text-amber-300 font-mono font-bold"
                                    />
                                </div>

                                <div>
                                    <label className="block text-gray-300 font-semibold mb-1">Batas Waktu *</label>
                                    <input
                                        type="number"
                                        min="1"
                                        max="720"
                                        required
                                        value={questFormData.time_limit_hours}
                                        onChange={(e) => setQuestFormData({ ...questFormData, time_limit_hours: Number(e.target.value) })}
                                        className="w-full bg-[#0e0e12] border border-white/10 rounded-xl px-3 py-2 text-white font-mono"
                                    />
                                    <span className="text-[10px] text-gray-500">Dalam Jam</span>
                                </div>

                                <div>
                                    <label className="block text-gray-300 font-semibold mb-1">Min. Level</label>
                                    <input
                                        type="number"
                                        min="1"
                                        value={questFormData.min_level}
                                        onChange={(e) => setQuestFormData({ ...questFormData, min_level: Number(e.target.value) })}
                                        className="w-full bg-[#0e0e12] border border-white/10 rounded-xl px-3 py-2 text-white font-mono"
                                    />
                                </div>
                            </div>

                            <div className="pt-2">
                                <label className="flex items-center gap-2 text-gray-300 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={questFormData.is_active}
                                        onChange={(e) => setQuestFormData({ ...questFormData, is_active: e.target.checked })}
                                        className="rounded border-gray-700 text-amber-500 focus:ring-amber-500"
                                    />
                                    <span>Aktifkan misi ini di dashboard pengguna sekarang</span>
                                </label>
                            </div>

                            <div className="pt-3 border-t border-white/10 flex items-center justify-end gap-2.5">
                                <button
                                    type="button"
                                    onClick={() => setQuestModalOpen(false)}
                                    disabled={questSubmitting}
                                    className="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 text-xs"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={questSubmitting}
                                    className="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-black font-bold text-xs flex items-center gap-1.5 transition-colors shadow"
                                >
                                    {questSubmitting && <RefreshCw size={13} className="animate-spin" />}
                                    <span>{editingQuest ? 'Simpan Perubahan' : 'Buat Misi'}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* MODAL: DELETE QUEST CONFIRMATION */}
            {deleteQuestConfirm && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md animate-in fade-in duration-200">
                    <div className="bg-[#121216] border border-red-500/40 rounded-2xl w-full max-w-md p-6 space-y-4 shadow-2xl">
                        <div className="flex items-center gap-2.5 text-red-400">
                            <AlertCircle size={20} />
                            <h3 className="font-bold text-sm text-white">Konfirmasi Penghapusan Misi</h3>
                        </div>

                        {(deleteQuestConfirm.active_participants_count || 0) > 0 ? (
                            <div className="p-3.5 rounded-xl bg-red-950/40 border border-red-500/40 text-red-300 text-xs space-y-2">
                                <strong className="block text-red-200">⚠️ PENGHAPUSAN DIBLOKIR SISTEM</strong>
                                <p className="leading-relaxed">
                                    Misi <strong>"{deleteQuestConfirm.title}"</strong> saat ini sedang dikerjakan secara aktif oleh{' '}
                                    <strong className="underline text-amber-300 font-mono">{deleteQuestConfirm.active_participants_count} pengguna</strong>!
                                </p>
                                <p className="text-[11px] text-red-400">
                                    Sistem melarang penghapusan misi yang sedang berjalan demi integritas koin komitmen escrow pengguna. Harap tunggu hingga ekspedisi berakhir atau nonaktifkan misi terlebih dahulu.
                                </p>
                            </div>
                        ) : (
                            <p className="text-xs text-gray-300 leading-relaxed">
                                Apakah Anda yakin ingin menghapus misi <strong>"{deleteQuestConfirm.title}"</strong> secara permanen?
                                Misi ini tidak memiliki peserta aktif saat ini dan aman untuk dihapus.
                            </p>
                        )}

                        <div className="flex items-center justify-end gap-2.5 pt-2">
                            <button
                                type="button"
                                onClick={() => setDeleteQuestConfirm(null)}
                                disabled={questSubmitting}
                                className="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 text-xs"
                            >
                                Batal
                            </button>

                            {(deleteQuestConfirm.active_participants_count || 0) === 0 && (
                                <button
                                    type="button"
                                    disabled={questSubmitting}
                                    onClick={async () => {
                                        setQuestSubmitting(true);
                                        try {
                                            const res = await fetch(`/admin/quests/${deleteQuestConfirm.id}`, {
                                                method: 'DELETE',
                                                headers: {
                                                    'Accept': 'application/json',
                                                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                                                },
                                            });
                                            const data = await res.json();
                                            if (res.ok && data.success) {
                                                setQuestList(prev => prev.filter(item => item.id !== deleteQuestConfirm.id));
                                                setDeleteQuestConfirm(null);
                                            } else {
                                                alert(data.error || 'Gagal menghapus misi.');
                                            }
                                        } catch (err) {
                                            alert('Terjadi kesalahan koneksi.');
                                        } finally {
                                            setQuestSubmitting(false);
                                        }
                                    }}
                                    className="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs flex items-center gap-1.5 transition-colors shadow-lg shadow-red-600/20"
                                >
                                    {questSubmitting && <RefreshCw size={13} className="animate-spin" />}
                                    <span>Hapus Permanen</span>
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
