import { useState, useEffect } from 'react';
import { Head, router, Link } from '@inertiajs/react';
import CookieConsentBanner from '@/Components/CookieConsentBanner';
import NotificationDrawer from '@/Components/NotificationDrawer';
import WebPushPermissionModal from '@/Components/WebPushPermissionModal';
import { Bell } from 'lucide-react';

interface ConquerorProps {
    user: {
        id: string;
        name: string;
        username?: string;
        whatsapp: string;
        role: string;
        faction: string;
        role_class: string;
        persona: string;
        subscription_tier: string;
        license_type: string;
        license_status: string;
        level: number;
        experience_points: number;
        reputation?: {
            total_score: number;
            current_status: string;
            network_score: number;
            transaction_score: number;
            checkin_score: number;
            mission_score: number;
        };
    };
    wallet: {
        silver_balance: number;
        gold_balance: number;
    } | null;
    quests: Array<{
        id: string;
        title: string;
        description: string;
        stake_amount_silver: number;
        reward_amount_gold: number;
        time_limit_hours: number;
        quest_category?: string;
        target_persona?: string;
        time_window?: string;
        min_level?: number;
        parameters?: any;
        is_active: boolean;
    }>;
    escrows: Array<{
        id: string;
        staked_silver: number;
        status: string;
        expires_at: string;
        created_at: string;
        quest: {
            title: string;
            reward_amount_gold: number;
            parameters?: {
                action_type?: string;
                target_threshold?: number;
            };
        };
    }>;
    checkin_status: {
        has_checked_in: boolean;
        today_checkin: any;
        streak_count: number;
        time_window: {
            window: string;
            label: string;
            time_range: string;
            buff: string;
            buff_badge: string;
            icon: string;
        };
    };
    time_details: {
        window: string;
        label: string;
        time_range: string;
        buff: string;
        buff_badge: string;
        icon: string;
    };
    global_pool: {
        total_fiat_in_bank: number;
        total_gold_in_circulation: number;
    } | null;
    downlines: {
        level_1: Array<{ id: string; name: string; whatsapp: string; role: string }>;
        level_2: Array<{ id: string; name: string; whatsapp: string; role: string }>;
    };
    passive_income: number;
    ledger_entries?: Array<{
        id: string;
        currency: string;
        type: 'DEBIT' | 'CREDIT';
        amount: number;
        created_at: string;
        ledger?: {
            description: string;
            reference_type?: string;
        };
    }>;
    ecosystem_services?: Array<{
        id: number;
        name: string;
        code: string;
        icon?: string;
        category?: string;
        description?: string;
        price_gold: number;
        price_fiat: number;
        service_url?: string;
        is_active: boolean;
    }>;
    my_licenses?: Array<{
        id: number;
        ecosystem_service_id: number;
        license_key: string;
        status: string;
        expires_at: string | null;
        service?: {
            name: string;
            code: string;
            icon?: string;
            service_url?: string;
        };
    }>;
}

export default function Conqueror({
    user,
    wallet: initialWallet,
    quests,
    escrows,
    checkin_status: initialCheckinStatus,
    time_details,
    global_pool,
    downlines,
    passive_income,
    ledger_entries = [],
    ecosystem_services = [],
    my_licenses = [],
}: ConquerorProps) {
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState<{ text: string; type: 'success' | 'error' } | null>(null);
    const [now, setNow] = useState(new Date());
    const [isMounted, setIsMounted] = useState(false);
    const [isScrolled, setIsScrolled] = useState(false);
    const [activeTab, setActiveTab] = useState<'QUESTS' | 'NETWORK' | 'SERVICES' | 'REWARDS'>('QUESTS');
    const [activeBottomNav, setActiveBottomNav] = useState<'HOME' | 'MUTASI' | 'WALLET' | 'PROFILE'>('HOME');
    const [showScanModal, setShowScanModal] = useState(false);
    const [scanTab, setScanTab] = useState<'SCANNER' | 'MY_QR'>('SCANNER');
    const [mutasiFilter, setMutasiFilter] = useState<'ALL' | 'SILVER' | 'GOLD' | 'CREDIT' | 'DEBIT'>('ALL');
    const [showNotificationDrawer, setShowNotificationDrawer] = useState(false);

    // Live state
    const [wallet, setWallet] = useState(initialWallet);
    const [currentUser, setCurrentUser] = useState(user);
    const [checkinStatus, setCheckinStatus] = useState(initialCheckinStatus);
    const [escrowList, setEscrowList] = useState(escrows);
    const [confirmingQuest, setConfirmingQuest] = useState<any>(null);
    const [isStaking, setIsStaking] = useState(false);
    const [animatedFiat, setAnimatedFiat] = useState<number>(
        Number(global_pool?.total_fiat_in_bank || 500000) - Number(global_pool?.total_gold_in_circulation || 0)
    );

    // Quest Claim & Real-Time Progress State
    const [claimingEscrowId, setClaimingEscrowId] = useState<string | null>(null);
    const [claimFeedback, setClaimFeedback] = useState<{ id: string; success: boolean; message: string } | null>(null);
    const [checkingEscrowId, setCheckingEscrowId] = useState<string | null>(null);
    const [escrowProgressData, setEscrowProgressData] = useState<Record<string, any>>({});

    const handleCheckProgress = async (escrowId: string) => {
        setCheckingEscrowId(escrowId);
        try {
            const res = await fetch(`/api/quests/progress/${escrowId}`, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (res.ok && data.success) {
                setEscrowProgressData(prev => ({ ...prev, [escrowId]: data.evaluation }));
            }
        } catch (e) {
            console.error(e);
        } finally {
            setCheckingEscrowId(null);
        }
    };

    const handleClaimReward = async (escrowId: string) => {
        setClaimingEscrowId(escrowId);
        setClaimFeedback(null);
        try {
            const res = await fetch('/api/quests/claim', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                },
                body: JSON.stringify({ escrow_id: escrowId }),
            });
            const data = await res.json();
            if (res.ok && data.success) {
                setClaimFeedback({ id: escrowId, success: true, message: data.message });
                setEscrowList(prev => prev.map(e => e.id === escrowId ? { ...e, status: 'SUCCESS' } : e));
                router.reload({ only: ['wallet', 'escrows'] });
            } else {
                setClaimFeedback({ id: escrowId, success: false, message: data.error || data.message || 'Klaim reward belum dapat diproses.' });
            }
        } catch (err) {
            setClaimFeedback({ id: escrowId, success: false, message: 'Terjadi kesalahan koneksi saat memproses klaim.' });
        } finally {
            setClaimingEscrowId(null);
        }
    };

    // Modals
    const [showUpgradeModal, setShowUpgradeModal] = useState(false);
    const [showPosModal, setShowPosModal] = useState(false);
    const [showMissionModal, setShowMissionModal] = useState(false);
    const [showSurveyModal, setShowSurveyModal] = useState(false);
    const [agentMission, setAgentMission] = useState<any>(null);

    // Business POS State removed as PitiCoin acts as Hub only
    
    // Survey lead state
    const [surveyData, setSurveyData] = useState({
        business_name: '',
        owner_name: '',
        owner_email: '',
        owner_phone: '',
        business_type: 'FnB/Café',
    });

    // Scroll listener for sticky user stat header
    useEffect(() => {
        setIsMounted(true);
        const handleScroll = () => {
            if (window.scrollY > 110) {
                setIsScrolled(true);
            } else {
                setIsScrolled(false);
            }
        };
        window.addEventListener('scroll', handleScroll);
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    // Interval clock
    useEffect(() => {
        const interval = setInterval(() => setNow(new Date()), 1000);
        return () => clearInterval(interval);
    }, []);

    // Fetch active agent mission
    useEffect(() => {
        fetch('/api/agent-missions/active')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.mission) {
                    setAgentMission(data.mission);
                }
            })
            .catch(err => console.error("Error fetching agent mission:", err));
    }, []);

    // Animated fiat counter
    const poolCapacity = Number(global_pool?.total_fiat_in_bank || 500000) - Number(global_pool?.total_gold_in_circulation || 0);
    useEffect(() => {
        if (animatedFiat === poolCapacity) return;
        const diff = poolCapacity - animatedFiat;
        const step = diff > 0 ? Math.ceil(diff / 15) : Math.floor(diff / 15);
        const timeout = setTimeout(() => {
            setAnimatedFiat((prev) => {
                const next = prev + step;
                if (diff > 0) {
                    return next >= poolCapacity ? poolCapacity : next;
                } else {
                    return next <= poolCapacity ? poolCapacity : next;
                }
            });
        }, 30);
        return () => clearTimeout(timeout);
    }, [poolCapacity, animatedFiat]);

    const showMsg = (text: string, type: 'success' | 'error' = 'success') => {
        setMessage({ text, type });
        setTimeout(() => setMessage(null), 5000);
    };

    const handleLogout = () => {
        router.post(route('logout'));
    };

    const formatRupiah = (val: number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(val);
    };

    const formatCoin = (val: number) => {
        return new Intl.NumberFormat('id-ID').format(val);
    };

    const xpThreshold = currentUser.level * 1000;
    const xpPercentage = Math.min((currentUser.experience_points / xpThreshold) * 100, 100);

    const getFactionColorClass = (fac: string) => {
        switch (fac) {
            case 'NISNAS': return 'text-faction-nisnas';
            case 'HUMAN': return 'text-faction-human';
            case 'ANDROID': return 'text-faction-android';
            default: return 'text-primary-container';
        }
    };
    const factionColorClass = getFactionColorClass(currentUser.faction);

    const totalDownlines = downlines.level_1.length + downlines.level_2.length;
    const transferUnlocked = totalDownlines >= 5;

    // Daily Check-in Handler
    const handleDailyCheckin = async () => {
        setLoading(true);
        try {
            const res = await fetch('/api/checkin', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
                }
            });
            const data = await res.json();
            if (data.success) {
                setCheckinStatus({
                    has_checked_in: true,
                    today_checkin: data.checkin,
                    streak_count: data.streak_count,
                    time_window: data.time_window,
                });
                if (data.wallet) {
                    setWallet(data.wallet);
                }
                if (data.user) {
                    setCurrentUser(prev => ({
                        ...prev,
                        level: data.user.level,
                        experience_points: data.user.experience_points,
                    }));
                }
                showMsg(data.message);
            } else {
                showMsg(data.message || 'Gagal melakukan check-in', 'error');
            }
        } catch (err) {
            showMsg('Terjadi kesalahan jaringan.', 'error');
        } finally {
            setLoading(false);
        }
    };

    // Persona Switcher Handler
    const handleSwitchPersona = async (newPersona: string) => {
        setLoading(true);
        try {
            const res = await fetch('/api/user/switch-persona', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
                },
                body: JSON.stringify({ persona: newPersona })
            });
            const data = await res.json();
            if (data.success) {
                setCurrentUser(prev => ({ ...prev, persona: data.persona }));
                showMsg(data.message);
            } else {
                showMsg(data.message || 'Gagal mengganti persona', 'error');
            }
        } catch (err) {
            showMsg('Terjadi kesalahan saat mengganti persona.', 'error');
        } finally {
            setLoading(false);
        }
    };

    // Quest Selection & Confirmation Handlers
    const handleSelectQuest = (quest: any) => {
        if (!checkinStatus.has_checked_in) {
            showMsg('Silakan lakukan check-in pada Kompas Harian terlebih dahulu untuk membuka misi.', 'error');
            return;
        }
        setConfirmingQuest(quest);
    };

    const handleConfirmStakeQuest = async () => {
        if (!confirmingQuest) return;
        setIsStaking(true);
        try {
            const res = await fetch('/api/quests/stake', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
                },
                body: JSON.stringify({ quest_id: confirmingQuest.id })
            });
            const data = await res.json();
            if (data.success && data.data) {
                if (data.data.wallet) {
                    setWallet(data.data.wallet);
                }
                if (data.data.escrow) {
                    setEscrowList(prev => [data.data.escrow, ...prev]);
                }
                setConfirmingQuest(null);
                showMsg(data.message || 'Misi berhasil diambil! Komitmen Koin Perak Anda terkunci di escrow.');
            } else {
                showMsg(data.error || 'Gagal mengambil misi', 'error');
            }
        } catch (err: any) {
            showMsg('Terjadi kesalahan saat memproses misi', 'error');
        } finally {
            setIsStaking(false);
        }
    };

    // Quick POS Sale Record Handler & Close Register Handler removed

    // Upgrade License Handler
    const handleUpgradeLicense = async (targetTier: string, method: string) => {
        setLoading(true);
        try {
            const res = await fetch('/api/user/upgrade-license', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
                },
                body: JSON.stringify({ target_tier: targetTier, payment_method: method })
            });
            const data = await res.json();
            if (data.success) {
                setCurrentUser(prev => ({ ...prev, license_type: data.license_type }));
                if (data.wallet) setWallet(data.wallet);
                setShowUpgradeModal(false);
                showMsg(data.message);
            } else {
                showMsg(data.message || 'Gagal upgrade lisensi', 'error');
            }
        } catch (err) {
            showMsg('Terjadi kesalahan saat memproses upgrade.', 'error');
        } finally {
            setLoading(false);
        }
    };

    // Remaining time helper
    const getRemainingTime = (expiresAtStr: string) => {
        const expiresAt = new Date(expiresAtStr);
        const diffMs = expiresAt.getTime() - now.getTime();
        if (diffMs <= 0) return { label: 'Kedaluwarsa', expired: true };
        const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
        const diffMins = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
        const diffSecs = Math.floor((diffMs % (1000 * 60)) / 1000);
        const pad = (n: number) => n.toString().padStart(2, '0');
        return { label: `${pad(diffHours)}:${pad(diffMins)}:${pad(diffSecs)}`, expired: false };
    };

    // Active quests filter
    const activePersona = currentUser.persona || 'UMKM';
    const currentTimeWindow = time_details?.window || 'MORNING';

    const defaultQuests = quests.filter(q => q.quest_category === 'DEFAULT');
    const timeWindowQuests = quests.filter(q => q.quest_category === 'TIME_WINDOW' && (q.time_window === currentTimeWindow || q.time_window === 'ALL'));
    const personaQuests = quests.filter(q => q.quest_category === 'PERSONA_SPECIFIC' && (q.target_persona === activePersona || q.target_persona === 'ALL'));

    const referralLink = `${window.location.origin}/register?ref=${currentUser.username || currentUser.whatsapp}`;
    const waShareText = `Assalamu'alaikum! Bergabunglah bersama Karavan Saudagar saya di PitiCoin.id - Gerbang Ekosistem Bisnis & Gamifikasi Gunung Mas. Dapatkan 50.000 Koin Perak dan penghasilan riil! Daftar di sini: ${referralLink}`;

    return (
        <div className="bg-background text-on-background min-h-screen relative overflow-x-hidden font-body-md text-body-md pb-[120px] select-none">
            <Head title="Markas Besar Saudagar - PitiCoin.id" />

            {/* ========================================================= */}
            {/* 1. SCROLLED VIEW: STICKY USER STAT HEADER (LAMPIRAN 2)    */}
            {/* Muncul mulus saat scroll > 110px. ZERO text wrapping!    */}
            {/* ========================================================= */}
            <header
                className={`fixed top-0 left-0 w-full z-50 transition-all duration-300 px-3 py-2 md:px-6 md:py-3 bg-obsidian-surface/95 backdrop-blur-xl border-b border-primary-container/20 shadow-[0_8px_30px_rgba(0,0,0,0.85)] ${
                    isScrolled ? 'translate-y-0 opacity-100 pointer-events-auto' : '-translate-y-full opacity-0 pointer-events-none'
                }`}
            >
                <div className="max-w-container-max mx-auto flex items-center justify-between gap-3">
                    {/* Left: Avatar with glowing golden rounded frame & circular Level badge */}
                    <div className="flex items-center gap-2.5 min-w-0 flex-1">
                        <div className="relative flex-shrink-0 w-11 h-11 md:w-12 md:h-12 rounded-lg border-2 border-primary-container p-0.5 shadow-[0_0_12px_rgba(233,196,0,0.6)] bg-surface">
                            <img
                                alt="User Avatar"
                                className="w-full h-full object-cover rounded-md"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuDi47Slkw4S4Y_KgVtZ0EISwFfTSIkSG4_2UXNIGz_TUpZPGsvQ9quu9zXxMlopsBUFeZOhn_yxJcMebklLuAHrdJXZOTxaanMZVSTn0-ghrNi4-HZtK5ZxRZPonkQg3bipaDJJjt0ghwB5GswyeN8IpGyxjzOyGGh3P6dz6mRoq-anKb_pf5frN99lP138Yfr7ElxVoaIt9Mds_TGIwKPmRA516YI1zc5MLi_Su07Z9ZLoMFO-KEQ"
                            />
                            <div className="absolute -bottom-1.5 -right-1.5 bg-obsidian-surface border border-primary-container text-primary-container font-mono text-[9px] font-bold w-5 h-5 flex items-center justify-center rounded-full shadow-[0_0_6px_rgba(255,215,0,0.7)]">
                                {currentUser.level}
                            </div>
                        </div>

                        {/* Middle: Name (yellow serif), Role, Green EXP bar, and ZERO-WRAPPED EXP text */}
                        <div className="flex flex-col min-w-0 flex-1 max-w-[170px] md:max-w-[260px]">
                            <h3
                                className="font-headline-md text-sm md:text-base font-bold text-primary-container truncate leading-tight"
                                style={{ fontFamily: "'Playfair Display', serif" }}
                            >
                                {currentUser.name}
                            </h3>
                            <div className="flex items-center gap-1.5 flex-wrap">
                                <span className="font-label-sm text-[9px] md:text-[10px] text-on-surface-variant uppercase tracking-wider truncate">
                                    {currentUser.role} • {currentUser.persona}
                                </span>
                                {currentUser.reputation && (
                                    <span className={`text-[8px] md:text-[9px] font-bold uppercase px-1.5 py-0.5 rounded border ${
                                        currentUser.reputation.current_status === 'DIASINGKAN' ? 'bg-red-500/20 border-red-500/40 text-red-400' :
                                        currentUser.reputation.current_status === 'DIBENCI' ? 'bg-orange-500/20 border-orange-500/40 text-orange-400' :
                                        currentUser.reputation.current_status === 'DICINTAI' ? 'bg-pink-500/20 border-pink-500/40 text-pink-400' :
                                        'bg-yellow-500/10 border-yellow-500/30 text-[#ffd700]'
                                    }`}>
                                        {currentUser.reputation.current_status} ({currentUser.reputation.total_score} RSP)
                                    </span>
                                )}
                            </div>

                            {/* Green EXP Bar */}
                            <div className="w-full h-1.5 bg-surface-container-highest rounded-full overflow-hidden mt-1 relative">
                                <div
                                    className="h-full bg-secondary relative shadow-[0_0_8px_rgba(102,221,139,0.8)] transition-all duration-500"
                                    style={{ width: `${xpPercentage}%` }}
                                ></div>
                            </div>

                            {/* ZERO TEXT-WRAPPING EXP LABEL (Single line guaranteed!) */}
                            <div className="flex items-center justify-between text-[9px] md:text-[10px] whitespace-nowrap font-mono text-on-surface-variant mt-0.5 leading-none">
                                <span className="text-secondary font-semibold">EXP:</span>
                                <span className="tracking-tight text-on-surface-variant/90">{currentUser.experience_points}/{xpThreshold}</span>
                            </div>
                        </div>
                    </div>

                    {/* Right: Balances (Silver & Gold) with glowing icons */}
                    <div className="flex flex-col gap-1.5 items-end flex-shrink-0">
                        {/* Silver Capsule */}
                        <div className="bg-surface-container-lowest/90 border border-glass-stroke rounded-lg px-2.5 py-0.5 flex items-center gap-1.5 shadow-inner">
                            <span className="font-mono text-xs md:text-sm font-bold text-on-surface whitespace-nowrap">
                                {wallet ? formatCoin(wallet.silver_balance) : 0}
                            </span>
                            <span className="material-symbols-outlined text-surface-variant text-[12px]" style={{ fontVariationSettings: "'FILL' 1" }}>circle</span>
                        </div>

                        {/* Gold Capsule with Gold $ Icon */}
                        <div className="bg-primary-container/15 border border-primary-container/50 rounded-lg px-2.5 py-0.5 flex items-center gap-1.5 shadow-[0_0_8px_rgba(255,215,0,0.15)]">
                            <span className="font-mono text-xs md:text-sm font-bold text-primary-fixed-dim whitespace-nowrap">
                                {wallet ? formatCoin(wallet.gold_balance) : 0}
                            </span>
                            <span className="material-symbols-outlined text-primary-container text-[14px]" style={{ fontVariationSettings: "'FILL' 1" }}>monetization_on</span>
                        </div>
                    </div>
                </div>
            </header>

            {/* ========================================================= */}
            {/* 2. DEFAULT TOP APP BAR (Normal Top View)                  */}
            {/* ========================================================= */}
            <header className="bg-transparent text-primary-container w-full px-margin-mobile pt-stack-md h-16 z-40 flex justify-between items-center transition-colors">
                {activeBottomNav === 'HOME' ? (
                    <button
                        onClick={handleLogout}
                        title="Keluar Akun"
                        className="text-on-surface-variant hover:text-primary-fixed-dim transition-colors flex items-center justify-center p-2 rounded-full hover:bg-white/5 active:scale-95 duration-200"
                    >
                        <span className="material-symbols-outlined text-xl" style={{ fontVariationSettings: "'FILL' 1" }}>logout</span>
                    </button>
                ) : (
                    <button
                        onClick={() => setActiveBottomNav('HOME')}
                        title="Kembali ke Beranda"
                        className="text-primary-container hover:text-white transition-colors flex items-center justify-center p-2 rounded-full hover:bg-white/5 active:scale-95 duration-200"
                    >
                        <span className="material-symbols-outlined text-2xl">arrow_back</span>
                    </button>
                )}
                <h1
                    className="font-headline-lg-mobile text-headline-lg-mobile md:font-headline-md md:text-headline-md font-bold text-primary-container tracking-widest text-center flex-1"
                    style={{ fontFamily: "'Playfair Display', serif" }}
                >
                    {activeBottomNav === 'HOME' && 'PITICOIN.ID'}
                    {activeBottomNav === 'MUTASI' && 'MUTASI SALDO'}
                    {activeBottomNav === 'WALLET' && 'DOMPET DIGITAL'}
                    {activeBottomNav === 'PROFILE' && 'PROFIL SAUDAGAR'}
                </h1>
                <div className="flex items-center gap-2">
                    <button
                        onClick={() => setShowNotificationDrawer(true)}
                        title="Pusat Notifikasi & WebPush"
                        className="text-amber-400 hover:text-white transition-colors p-2 rounded-full hover:bg-white/10 active:scale-95 duration-200"
                    >
                        <Bell size={20} />
                    </button>
                    <button
                        onClick={() => setShowUpgradeModal(true)}
                        className="text-on-surface-variant hover:text-primary-fixed-dim transition-colors w-10 h-10 rounded-full overflow-hidden border-2 border-primary-container shadow-[0_0_10px_rgba(255,215,0,0.4)] active:scale-95 duration-200"
                    >
                        <img
                            alt="Explorer Rank Icon"
                            className="w-full h-full object-cover"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuDtnNnM6GYAk06weL4J1x3GacDiwegweSKP84SJLeHNza0vvwlFwOUmt95Zp-S_X6zG6qkVVm-b90g6cINFPfuTa-nBO-3nCvwowsn452NwPmp6pNvXqVPhxwzPz2t4xwLoiP_EH1vm9bu1Q57t2jjgzGpaiXRk_NOuzg4GfBJ4rpt0dZrV5lN1gNXxvmoF9bCGXy8lMrSt6FfvwopiDszDU135FTdSvBO4XcYORkyfoUAviuDfWCA"
                        />
                    </button>
                </div>
            </header>

            <main className="relative z-10 w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop flex flex-col gap-5">
                {/* Notification toast */}
                {message && (
                    <div
                        className={`rounded-xl p-3 border text-xs md:text-sm font-bold tracking-wide animate-fade-in ${
                            message.type === 'error'
                                ? 'bg-error-container/20 border-error/50 text-error'
                                : 'bg-secondary-container/20 border-secondary/50 text-secondary'
                        }`}
                    >
                        {message.text}
                    </div>
                )}
                {/* ========================================================= */}
                {/* 3. HOME VIEW CONTAINER                                    */}
                {/* ========================================================= */}
                {activeBottomNav === 'HOME' && (
                    <>
                        {/* 3. HERO: THE LEDGER POOL (LAMPIRAN 1)                     */}
                        <section className="flex flex-col items-center justify-center text-center mt-1">
                            <p className="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest mb-1.5 flex items-center gap-2">
                                <span className="w-2 h-2 rounded-full bg-primary-container animate-pulse"></span>
                                THE LEDGER POOL
                            </p>
                            <h2
                                className="font-display-hero text-[38px] md:text-display-hero text-primary-fixed-dim drop-shadow-[0_0_18px_rgba(233,196,0,0.45)] transition-all duration-300 tracking-tight"
                                style={{ fontFamily: "'Playfair Display', serif" }}
                            >
                                {formatRupiah(animatedFiat)}
                            </h2>
                            <div className="mt-3 glass-panel rounded-full px-5 py-1.5 inline-flex items-center gap-2 border border-primary-container/30 shadow-[0_2px_12px_rgba(0,0,0,0.4)]">
                                <span className="material-symbols-outlined text-secondary-fixed-dim text-sm" style={{ fontVariationSettings: "'FILL' 1" }}>trending_up</span>
                                <p className="font-label-sm text-xs text-secondary-fixed-dim whitespace-nowrap">
                                    Likuiditas Kas Riil Terjamin Double-Entry Ledger
                                </p>
                            </div>
                        </section>

                        {/* 4. USER OVERVIEW & FACTION PROFILE */}
                        <section className="glass-panel rounded-2xl p-4 md:p-5 border border-glass-stroke space-y-4 shadow-lg">
                            <div className="flex items-center justify-between gap-3 border-b border-glass-stroke pb-3">
                                <div className="flex items-center gap-3">
                                    <div className="relative w-12 h-12 rounded-xl border border-primary-container/40 p-0.5 bg-surface overflow-hidden shadow">
                                        <img
                                            alt="Avatar"
                                            className="w-full h-full object-cover rounded-lg"
                                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuDi47Slkw4S4Y_KgVtZ0EISwFfTSIkSG4_2UXNIGz_TUpZPGsvQ9quu9zXxMlopsBUFeZOhn_yxJcMebklLuAHrdJXZOTxaanMZVSTn0-ghrNi4-HZtK5ZxRZPonkQg3bipaDJJjt0ghwB5GswyeN8IpGyxjzOyGGh3P6dz6mRoq-anKb_pf5frN99lP138Yfr7ElxVoaIt9Mds_TGIwKPmRA516YI1zc5MLi_Su07Z9ZLoMFO-KEQ"
                                        />
                                    </div>
                                    <div>
                                        <h3 className="font-bold text-sm text-on-surface flex items-center gap-1.5">
                                            {currentUser.name}
                                            <span className="text-[10px] px-1.5 py-0.2 rounded bg-primary-container/20 text-primary-container font-mono border border-primary-container/30">
                                                Lv.{currentUser.level}
                                            </span>
                                        </h3>
                                        <p className="text-xs text-on-surface-variant font-mono">
                                            {currentUser.role_class || 'Prajurit'} • <span className={factionColorClass}>{currentUser.faction}</span>
                                        </p>
                                    </div>
                                </div>

                                <div className="text-right">
                                    <span className="text-[10px] text-on-surface-variant uppercase tracking-wider block">Lisensi</span>
                                    <span className="text-xs font-bold text-primary-container font-mono">
                                        {currentUser.license_type || 'OBSERVER'}
                                    </span>
                                </div>
                            </div>

                            {/* EXP Bar */}
                            <div className="space-y-1">
                                <div className="flex justify-between text-[10px] font-mono text-on-surface-variant">
                                    <span>Kemajuan EXP</span>
                                    <span>{currentUser.experience_points} / {xpThreshold} EXP</span>
                                </div>
                                <div className="h-2 w-full bg-surface-container-lowest rounded-full overflow-hidden border border-glass-stroke">
                                    <div
                                        className="h-full bg-gradient-to-r from-secondary to-primary-container transition-all duration-500 rounded-full"
                                        style={{ width: `${xpPercentage}%` }}
                                    ></div>
                                </div>
                            </div>

                            {/* Balances */}
                            <div className="grid grid-cols-2 gap-2 pt-1 text-center font-mono">
                                <div className="p-2.5 rounded-xl bg-surface/50 border border-glass-stroke flex flex-col items-center">
                                    <span className="text-[10px] text-on-surface-variant uppercase tracking-wider block">Koin Perak</span>
                                    <strong className="text-sm md:text-base font-bold text-on-surface mt-0.5">
                                        {formatCoin(wallet?.silver_balance || 0)}
                                    </strong>
                                </div>
                                <div className="p-2.5 rounded-xl bg-primary-container/10 border border-primary-container/30 flex flex-col items-center shadow-[0_0_10px_rgba(255,215,0,0.1)]">
                                    <span className="text-[10px] text-primary-container uppercase tracking-wider block">Koin Emas</span>
                                    <strong className="text-sm md:text-base font-bold text-primary-container mt-0.5">
                                        {formatCoin(wallet?.gold_balance || 0)}
                                    </strong>
                                </div>
                            </div>
                        </section>

                        {/* QUICK ACCESS HUB MATRIX (PUSAT LAYANAN EKOSISTEM TERPADU) */}
                        <section className="space-y-2.5">
                            <div className="flex items-center justify-between px-1">
                                <span className="text-[10px] text-primary-container font-bold uppercase tracking-widest font-mono flex items-center gap-1.5">
                                    <span className="material-symbols-outlined text-xs">grid_view</span>
                                    Pusat Layanan & Navigasi Ekosistem
                                </span>
                                <span className="text-[9px] text-on-surface-variant font-mono">Pintu Masuk Terpadu</span>
                            </div>
                            <div className="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                {/* Card 1: Baitul Mal */}
                                <Link
                                    href={route('baitul-mal.index')}
                                    className="glass-panel p-3 rounded-2xl border border-amber-400/35 bg-gradient-to-br from-amber-500/10 via-transparent to-surface hover:border-amber-400/70 hover:scale-[1.02] active:scale-95 transition-all flex flex-col justify-between group shadow-[0_4px_20px_rgba(255,215,0,0.08)]"
                                >
                                    <div className="flex items-center justify-between mb-2">
                                        <div className="w-8 h-8 rounded-xl bg-amber-400/20 text-amber-400 flex items-center justify-center border border-amber-400/40">
                                            <span className="material-symbols-outlined text-base">volunteer_activism</span>
                                        </div>
                                        <span className="text-[9px] font-mono font-bold text-amber-400 bg-amber-400/15 px-1.5 py-0.5 rounded-md border border-amber-400/30">
                                            Kas Sosial
                                        </span>
                                    </div>
                                    <div>
                                        <strong className="text-xs font-bold text-white group-hover:text-amber-300 transition-colors block">
                                            Baitul Mal
                                        </strong>
                                        <span className="text-[10px] text-on-surface-variant block mt-0.5 leading-tight">
                                            Sedekah & Mustahik
                                        </span>
                                    </div>
                                </Link>

                                {/* Card 2: Pasar Lelang */}
                                <Link
                                    href={route('auction.index')}
                                    className="glass-panel p-3 rounded-2xl border border-glass-stroke hover:border-primary-container/50 hover:scale-[1.02] active:scale-95 transition-all flex flex-col justify-between group shadow"
                                >
                                    <div className="flex items-center justify-between mb-2">
                                        <div className="w-8 h-8 rounded-xl bg-primary-container/15 text-primary-container flex items-center justify-center border border-primary-container/30">
                                            <span className="material-symbols-outlined text-base">gavel</span>
                                        </div>
                                        <span className="text-[9px] font-mono font-bold text-on-surface-variant bg-surface/50 px-1.5 py-0.5 rounded-md border border-glass-stroke">
                                            Bursa Nilai
                                        </span>
                                    </div>
                                    <div>
                                        <strong className="text-xs font-bold text-white group-hover:text-primary-container transition-colors block">
                                            Pasar Lelang
                                        </strong>
                                        <span className="text-[10px] text-on-surface-variant block mt-0.5 leading-tight">
                                            Aset & Hak Misi
                                        </span>
                                    </div>
                                </Link>

                                {/* Card 3: Profil Persona & Avatar */}
                                <Link
                                    href={route('profile.edit')}
                                    className="glass-panel p-3 rounded-2xl border border-glass-stroke hover:border-primary-container/50 hover:scale-[1.02] active:scale-95 transition-all flex flex-col justify-between group shadow"
                                >
                                    <div className="flex items-center justify-between mb-2">
                                        <div className="w-8 h-8 rounded-xl bg-surface-container text-on-surface flex items-center justify-center border border-glass-stroke">
                                            <span className="material-symbols-outlined text-base">account_circle</span>
                                        </div>
                                        <span className="text-[9px] font-mono font-bold text-secondary bg-secondary/10 px-1.5 py-0.5 rounded-md border border-secondary/30">
                                            Persona
                                        </span>
                                    </div>
                                    <div>
                                        <strong className="text-xs font-bold text-white group-hover:text-primary-container transition-colors block">
                                            Profil Persona
                                        </strong>
                                        <span className="text-[10px] text-on-surface-variant block mt-0.5 leading-tight">
                                            Avatar & Sesi Login
                                        </span>
                                    </div>
                                </Link>

                                {/* Card 4: Pusat Bantuan & FAQ */}
                                <Link
                                    href={route('help.index')}
                                    className="glass-panel p-3 rounded-2xl border border-glass-stroke hover:border-blue-400/50 hover:scale-[1.02] active:scale-95 transition-all flex flex-col justify-between group shadow"
                                >
                                    <div className="flex items-center justify-between mb-2">
                                        <div className="w-8 h-8 rounded-xl bg-blue-500/15 text-blue-400 flex items-center justify-center border border-blue-500/30">
                                            <span className="material-symbols-outlined text-base">help</span>
                                        </div>
                                        <span className="text-[9px] font-mono font-bold text-blue-400 bg-blue-500/10 px-1.5 py-0.5 rounded-md border border-blue-500/30">
                                            FAQ & CS
                                        </span>
                                    </div>
                                    <div>
                                        <strong className="text-xs font-bold text-white group-hover:text-blue-300 transition-colors block">
                                            Pusat Bantuan
                                        </strong>
                                        <span className="text-[10px] text-on-surface-variant block mt-0.5 leading-tight">
                                            Panduan & Bantuan
                                        </span>
                                    </div>
                                </Link>
                            </div>
                        </section>

                        {/* 5. DAILY COMPASS CARD */}
                        <section className="glass-panel rounded-2xl p-4 md:p-5 border border-primary-container/30 space-y-3 bg-gradient-to-br from-obsidian-surface via-surface/60 to-primary-container/5 relative overflow-hidden">
                            <div className="flex justify-between items-start">
                                <div>
                                    <span className="text-[10px] text-primary-container uppercase tracking-widest font-bold block mb-0.5">
                                        {time_details?.label || 'Kompas Harian'}
                                    </span>
                                    <h4 className="font-bold text-sm text-on-surface">
                                        {time_details?.buff || 'Buff Komitmen Aktif'}
                                    </h4>
                                    <p className="text-xs text-on-surface-variant mt-0.5">
                                        {time_details?.time_range} • Streak: {checkinStatus.streak_count} Hari
                                    </p>
                                </div>
                                <span className="material-symbols-outlined text-3xl text-primary-container">
                                    {time_details?.icon || 'explore'}
                                </span>
                            </div>

                            {!checkinStatus.has_checked_in ? (
                                <button
                                    onClick={handleDailyCheckin}
                                    disabled={loading}
                                    className="w-full py-3 rounded-xl bg-gradient-to-r from-amber-500 via-primary-container to-amber-400 text-on-primary font-bold text-xs uppercase tracking-wider hover:brightness-110 active:scale-95 transition-all shadow-[0_0_15px_rgba(255,215,0,0.4)] flex items-center justify-center gap-1.5"
                                >
                                    <span className="material-symbols-outlined text-base">touch_app</span>
                                    <span>Aktivasi Kompas Hari Ini (+250 Perak)</span>
                                </button>
                            ) : (
                                <div className="grid grid-cols-3 gap-2 text-center text-[10px]">
                                    <div className="bg-surface/50 border border-glass-stroke rounded-lg p-2">
                                        <span className="text-on-surface-variant block mb-0.5">Hadiah Perak</span>
                                        <strong className="text-on-surface font-mono text-xs">+250 Perak</strong>
                                    </div>
                                    <div className="bg-surface/50 border border-glass-stroke rounded-lg p-2">
                                        <span className="text-on-surface-variant block mb-0.5">Poin EXP</span>
                                        <strong className="text-secondary font-mono text-xs">+25 EXP</strong>
                                    </div>
                                    <div className="bg-surface/50 border border-glass-stroke rounded-lg p-2">
                                        <span className="text-on-surface-variant block mb-0.5">Milestone 7d</span>
                                        <strong className="text-primary-container font-mono text-xs">+1 Emas</strong>
                                    </div>
                                </div>
                            )}
                        </section>

                        {/* ========================================================= */}
                        {/* 6. TAB SELECTOR NAVIGATION (4 TABS: MISI, JARINGAN, LAYANAN, REWARD) */}
                        {/* ========================================================= */}
                        <div className="grid grid-cols-4 gap-1.5 bg-surface-container-lowest/80 p-1.5 rounded-xl border border-glass-stroke">
                            {[
                                { id: 'QUESTS', label: 'Misi', icon: 'assignment' },
                                { id: 'NETWORK', label: 'Jaringan', icon: 'diversity_3' },
                                { id: 'SERVICES', label: 'Layanan', icon: 'apps' },
                                { id: 'REWARDS', label: 'Reward', icon: 'military_tech' },
                            ].map((tab) => (
                                <button
                                    key={tab.id}
                                    onClick={() => setActiveTab(tab.id as any)}
                                    className={`py-2 px-1 rounded-lg flex flex-col items-center justify-center gap-1 transition-all ${
                                        activeTab === tab.id
                                            ? 'bg-primary-container/20 border border-primary-container/50 text-primary-container shadow-[0_0_10px_rgba(255,215,0,0.2)]'
                                            : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5'
                                    }`}
                                >
                                    <span className="material-symbols-outlined text-base">{tab.icon}</span>
                                    <span className="font-label-sm text-[10px] font-bold uppercase">{tab.label}</span>
                                </button>
                            ))}
                        </div>

                {/* ========================================================= */}
                {/* TAB CONTENT 1: DAFTAR MISI DINAMIS (QUESTS)               */}
                {/* ========================================================= */}
                {activeTab === 'QUESTS' && (
                    <div className="space-y-5 animate-fade-in">
                        {/* Lock Warning if not checked in */}
                        {!checkinStatus.has_checked_in && (
                            <div className="glass-panel border border-amber-500/50 rounded-2xl p-4 text-center space-y-2 bg-amber-500/10">
                                <span className="material-symbols-outlined text-3xl text-primary-container animate-bounce">lock</span>
                                <h4 className="font-bold text-sm text-on-surface">Misi Harian Terkunci!</h4>
                                <p className="text-xs text-on-surface-variant max-w-xs mx-auto">
                                    Silakan lakukan check-in pada kartu <strong>Kompas Harian</strong> di atas untuk membuka seluruh misi dan mengklaim Koin Emas.
                                </p>
                            </div>
                        )}

                        {/* Active Escrow Campaign if any */}
                        {escrowList.filter(e => e.status === 'LOCKED').length > 0 && (
                            <div className="space-y-3">
                                <h3 className="font-bold text-xs uppercase tracking-wider text-primary-container flex items-center gap-1.5">
                                    <span className="w-2 h-2 rounded-full bg-primary-container animate-pulse"></span>
                                    Ekspedisi Berjalan (Escrow Terkunci)
                                </h3>
                                {escrowList.filter(e => e.status === 'LOCKED').map((escrow) => {
                                    const feedback = claimFeedback?.id === escrow.id ? claimFeedback : null;
                                    const progress = escrowProgressData[escrow.id];
                                    const params = escrow.quest?.parameters;

                                    return (
                                        <div key={escrow.id} className="glass-panel rounded-xl p-4 border border-primary-container/40 space-y-3">
                                            <div className="flex justify-between items-start">
                                                <div>
                                                    <h4 className="font-bold text-sm text-on-surface">{escrow.quest?.title || 'Misi Berjalan'}</h4>
                                                    {params?.action_type && (
                                                        <p className="text-[11px] text-secondary font-mono mt-0.5">
                                                            🎯 Sasaran: {params.action_type === 'RECRUIT_MEMBERS' ? `Ajak ${params.target_threshold ?? 1} Referral Baru` :
                                                                        params.action_type === 'REGISTER_MERCHANT_UNIMART' ? `Daftarkan ${params.target_threshold ?? 1} Toko ke Unimart` :
                                                                        params.action_type === 'REGISTER_WALIKELAS_TEACHER' ? `Daftarkan ${params.target_threshold ?? 1} Guru ke WaliKelas` :
                                                                        params.action_type === 'DIGITAL_PPOB_TRANSACTION' ? `Selesaikan ${params.target_threshold ?? 1} Transaksi PPOB` :
                                                                        params.action_type === 'DAILY_CHECKIN_STREAK' ? `Check-in ${params.target_threshold ?? 1} Hari` :
                                                                        `Target: ${params.target_threshold ?? 1}`}
                                                        </p>
                                                    )}
                                                </div>
                                                <span className="font-mono text-xs text-error font-bold whitespace-nowrap">
                                                    {isMounted ? getRemainingTime(escrow.expires_at).label : '--:--:--'}
                                                </span>
                                            </div>

                                            {/* Progress Bar (Jika sudah dicek) */}
                                            {progress && (
                                                <div className="space-y-1 bg-black/30 p-2.5 rounded-lg border border-glass-stroke">
                                                    <div className="flex justify-between text-[11px] font-mono">
                                                        <span className="text-on-surface-variant">Capaian Progres:</span>
                                                        <strong className="text-primary-container">{progress.current_progress} / {progress.target_threshold} ({progress.percentage}%)</strong>
                                                    </div>
                                                    <div className="w-full bg-white/10 rounded-full h-1.5 overflow-hidden">
                                                        <div 
                                                            className="bg-primary-container h-full rounded-full transition-all duration-300"
                                                            style={{ width: `${progress.percentage}%` }}
                                                        />
                                                    </div>
                                                </div>
                                            )}

                                            {feedback && (
                                                <div className={`p-2.5 rounded-lg text-xs flex items-center gap-2 ${feedback.success ? 'bg-emerald-950/40 border border-emerald-500/40 text-emerald-300' : 'bg-red-950/40 border border-red-500/40 text-red-300'}`}>
                                                    <span className="material-symbols-outlined text-sm">{feedback.success ? 'check_circle' : 'info'}</span>
                                                    <span>{feedback.message}</span>
                                                </div>
                                            )}

                                            <div className="flex justify-between items-center text-xs text-on-surface-variant pt-2 border-t border-glass-stroke">
                                                <div className="flex items-center gap-2">
                                                    <span>Staking: <strong>{formatCoin(escrow.staked_silver)} Perak</strong></span>
                                                    <span className="text-primary-container font-bold">+{formatCoin(escrow.quest?.reward_amount_gold || 0)} Emas</span>
                                                </div>

                                                <div className="flex items-center gap-1.5">
                                                    <button
                                                        type="button"
                                                        onClick={() => handleCheckProgress(escrow.id)}
                                                        disabled={checkingEscrowId === escrow.id}
                                                        className="px-2.5 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-on-surface-variant hover:text-white text-[11px] font-mono border border-glass-stroke transition-all cursor-pointer"
                                                    >
                                                        {checkingEscrowId === escrow.id ? 'Cek...' : 'Cek Progres'}
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => handleClaimReward(escrow.id)}
                                                        disabled={claimingEscrowId === escrow.id}
                                                        className="px-3 py-1 rounded-lg bg-primary-container hover:bg-primary-container/80 text-on-primary-container font-bold text-[11px] flex items-center gap-1 transition-all shadow cursor-pointer"
                                                    >
                                                        {claimingEscrowId === escrow.id && <span className="material-symbols-outlined text-xs animate-spin">refresh</span>}
                                                        <span>Klaim Hadiah</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        )}

                        {/* Group 1: Task Default (Standar Seluruh Pengguna) */}
                        <div className="space-y-3">
                            <div className="flex justify-between items-center">
                                <h4 className="font-bold text-xs text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                                    <span className="material-symbols-outlined text-sm text-secondary">verified</span>
                                    Tugas Default (Standar Seluruh Pengguna)
                                </h4>
                                <span className="text-[10px] text-on-surface-variant">5 Tugas Harian</span>
                            </div>

                            <div className="grid grid-cols-1 gap-2.5">
                                {defaultQuests.map((quest) => (
                                    <div
                                        key={quest.id}
                                        onClick={() => handleSelectQuest(quest)}
                                        className={`glass-panel rounded-xl p-3.5 border border-glass-stroke flex items-center justify-between gap-3 cursor-pointer hover:border-primary-container/60 hover:bg-white/[0.03] transition-all ${
                                            !checkinStatus.has_checked_in ? 'opacity-50 pointer-events-none' : ''
                                        }`}
                                    >
                                        <div className="min-w-0 flex-1">
                                            <h5 className="font-bold text-xs text-on-surface truncate">{quest.title}</h5>
                                            <p className="text-[11px] text-on-surface-variant truncate">{quest.description}</p>
                                            <div className="flex items-center gap-2 mt-1 font-mono text-[10px]">
                                                {quest.stake_amount_silver > 0 ? (
                                                    <span className="text-on-surface-variant">Stake: {formatCoin(quest.stake_amount_silver)} Perak</span>
                                                ) : (
                                                    <span className="text-secondary">Tanpa Stake</span>
                                                )}
                                                {quest.reward_amount_gold > 0 && (
                                                    <span className="text-primary-container font-bold">+{quest.reward_amount_gold} Emas</span>
                                                )}
                                            </div>
                                        </div>

                                        <button
                                            type="button"
                                            onClick={(e) => {
                                                e.stopPropagation();
                                                handleSelectQuest(quest);
                                            }}
                                            disabled={!checkinStatus.has_checked_in}
                                            className="px-3 py-1.5 rounded-lg bg-surface-container-high hover:bg-primary-container hover:text-on-primary border border-glass-stroke text-[11px] font-bold whitespace-nowrap transition-colors"
                                        >
                                            Ambil
                                        </button>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Group 2: Misi Horizon Waktu (Time-Dynamic) */}
                        <div className="space-y-3">
                            <div className="flex justify-between items-center">
                                <h4 className="font-bold text-xs text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                                    <span className="material-symbols-outlined text-sm text-primary-container">{time_details?.icon || 'schedule'}</span>
                                    Misi Waktu: {time_details?.label || 'Horizon Fajar'}
                                </h4>
                                <span className="font-mono text-[10px] text-primary-container bg-primary-container/10 px-2 py-0.5 rounded border border-primary-container/20">
                                    {time_details?.buff_badge || '+10% XP'}
                                </span>
                            </div>

                            <div className="grid grid-cols-1 gap-2.5">
                                {timeWindowQuests.map((quest) => (
                                    <div
                                        key={quest.id}
                                        onClick={() => handleSelectQuest(quest)}
                                        className={`glass-panel rounded-xl p-3.5 border border-primary-container/30 flex items-center justify-between gap-3 cursor-pointer hover:border-primary-container hover:bg-primary-container/[0.04] transition-all ${
                                            !checkinStatus.has_checked_in ? 'opacity-50 pointer-events-none' : ''
                                        }`}
                                    >
                                        <div className="min-w-0 flex-1">
                                            <h5 className="font-bold text-xs text-on-surface truncate">{quest.title}</h5>
                                            <p className="text-[11px] text-on-surface-variant truncate">{quest.description}</p>
                                            <div className="flex items-center gap-2 mt-1 font-mono text-[10px]">
                                                <span className="text-on-surface-variant">Stake: {formatCoin(quest.stake_amount_silver)} Perak</span>
                                                <span className="text-primary-container font-bold">+{quest.reward_amount_gold} Emas</span>
                                            </div>
                                        </div>

                                        <button
                                            type="button"
                                            onClick={(e) => {
                                                e.stopPropagation();
                                                handleSelectQuest(quest);
                                            }}
                                            disabled={!checkinStatus.has_checked_in}
                                            className="px-3.5 py-1.5 rounded-lg bg-primary-container text-on-primary font-bold text-[11px] whitespace-nowrap hover:scale-105 active:scale-95 transition-transform shadow-[0_0_8px_rgba(255,215,0,0.3)]"
                                        >
                                            Eksekusi
                                        </button>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Group 3: Misi Persona ({activePersona}) */}
                        <div className="space-y-3">
                            <div className="flex justify-between items-center">
                                <h4 className="font-bold text-xs text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                                    <span className="material-symbols-outlined text-sm text-secondary">psychology</span>
                                    Misi Spesifik Persona: {activePersona} (Level {currentUser.level})
                                </h4>
                            </div>

                            <div className="grid grid-cols-1 gap-2.5">
                                {personaQuests.map((quest) => (
                                    <div
                                        key={quest.id}
                                        onClick={() => handleSelectQuest(quest)}
                                        className={`glass-panel rounded-xl p-3.5 border border-glass-stroke flex items-center justify-between gap-3 cursor-pointer hover:border-primary-container/60 hover:bg-white/[0.03] transition-all ${
                                            !checkinStatus.has_checked_in ? 'opacity-50 pointer-events-none' : ''
                                        }`}
                                    >
                                        <div className="min-w-0 flex-1">
                                            <h5 className="font-bold text-xs text-on-surface truncate">{quest.title}</h5>
                                            <p className="text-[11px] text-on-surface-variant truncate">{quest.description}</p>
                                            <div className="flex items-center gap-2 mt-1 font-mono text-[10px]">
                                                <span className="text-on-surface-variant">Stake: {formatCoin(quest.stake_amount_silver)} Perak</span>
                                                <span className="text-primary-container font-bold">+{quest.reward_amount_gold} Emas</span>
                                            </div>
                                        </div>

                                        <button
                                            type="button"
                                            onClick={(e) => {
                                                e.stopPropagation();
                                                handleSelectQuest(quest);
                                            }}
                                            disabled={!checkinStatus.has_checked_in}
                                            className="px-3.5 py-1.5 rounded-lg bg-surface-container-high hover:bg-primary-container hover:text-on-primary border border-glass-stroke font-bold text-[11px] whitespace-nowrap transition-colors"
                                        >
                                            Ambil Misi
                                        </button>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                )}

                {/* ========================================================= */}
                {/* TAB CONTENT 2: RUANG KENDALI JARINGAN (KARAVAN MASTER)     */}
                {/* ========================================================= */}
                {activeTab === 'NETWORK' && (
                    <div className="space-y-4 animate-fade-in">
                        <div className="glass-panel rounded-2xl p-4 border border-primary-container/30 space-y-3">
                            <div className="flex justify-between items-center border-b border-glass-stroke pb-3">
                                <div>
                                    <h3 className="font-bold text-sm text-on-surface">Pusat Komando Karavan</h3>
                                    <p className="text-xs text-on-surface-variant">Kelola downline Level 1 & Level 2</p>
                                </div>
                                <span className="font-mono text-xs text-secondary font-bold bg-secondary/10 px-2 py-1 rounded">
                                    {totalDownlines} Anggota Pasukan
                                </span>
                            </div>

                            {/* Share Link WhatsApp Card */}
                            <div className="bg-surface-container-lowest/60 border border-glass-stroke rounded-xl p-3 space-y-2">
                                <span className="text-[10px] uppercase text-on-surface-variant block font-bold">Tautan Referral Karavan Anda:</span>
                                <div className="flex items-center gap-2">
                                    <input
                                        type="text"
                                        readOnly
                                        value={referralLink}
                                        className="w-full bg-surface border border-glass-stroke rounded-lg px-2.5 py-1.5 text-xs text-on-surface-variant font-mono"
                                    />
                                    <button
                                        onClick={() => {
                                            navigator.clipboard.writeText(referralLink);
                                            showMsg('Tautan referral berhasil disalin!');
                                        }}
                                        className="px-3 py-1.5 rounded-lg bg-surface-variant hover:bg-primary-container hover:text-on-primary text-xs font-bold whitespace-nowrap"
                                    >
                                        Salin
                                    </button>
                                </div>

                                <a
                                    href={`https://wa.me/?text=${encodeURIComponent(waShareText)}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="w-full mt-2 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center justify-center gap-2 transition-all shadow-[0_0_12px_rgba(16,185,129,0.3)]"
                                >
                                    <span className="material-symbols-outlined text-base">send</span>
                                    <span>Bagikan Undangan ke WhatsApp</span>
                                </a>
                            </div>

                            {/* Downline Level 1 & 2 Lists */}
                            <div className="grid grid-cols-2 gap-3 pt-2">
                                <div className="bg-surface/40 border border-glass-stroke rounded-xl p-3">
                                    <span className="text-[10px] uppercase text-primary-container block font-bold mb-1">Level 1 (Direct 25%)</span>
                                    <div className="font-mono text-lg font-bold text-on-surface">{downlines.level_1.length} Mitra</div>
                                    <p className="text-[10px] text-on-surface-variant mt-1">25% Reward Emas Otomatis</p>
                                </div>
                                <div className="bg-surface/40 border border-glass-stroke rounded-xl p-3">
                                    <span className="text-[10px] uppercase text-secondary block font-bold mb-1">Level 2 (Grand 10%)</span>
                                    <div className="font-mono text-lg font-bold text-on-surface">{downlines.level_2.length} Mitra</div>
                                    <p className="text-[10px] text-on-surface-variant mt-1">10% Reward Emas Pasif</p>
                                </div>
                            </div>
                        </div>

                        {/* Transfer Requirement Status */}
                        <div className="glass-panel rounded-xl p-4 border border-glass-stroke space-y-2">
                            <div className="flex justify-between items-center text-xs">
                                <span className="font-bold text-on-surface">Syarat Buka Fitur Transfer Antar-Saudagar</span>
                                <span className={transferUnlocked ? 'text-secondary font-bold' : 'text-error font-bold'}>
                                    {Math.min(totalDownlines, 5)}/5 Downline
                                </span>
                            </div>
                            <div className="w-full h-2 bg-surface-container-highest rounded-full overflow-hidden">
                                <div
                                    className="h-full bg-primary-container transition-all duration-500"
                                    style={{ width: `${Math.min((totalDownlines / 5) * 100, 100)}%` }}
                                ></div>
                            </div>
                        </div>
                    </div>
                )}

                {/* TAB CONTENT 3: KELOLA BISNIS & POS KASIR PINTAR REMOVED */}

                {/* ========================================================= */}
                {/* TAB CONTENT 3: LAYANAN & GERBANG EKOSISTEM               */}
                {/* ========================================================= */}
                {activeTab === 'SERVICES' && (
                    <div className="space-y-4 animate-fade-in">
                        {/* Golden Vault Banner */}
                        <div className="glass-panel rounded-2xl overflow-hidden border border-primary-container/40 relative min-h-[160px] p-5 flex flex-col justify-end">
                            <div className="absolute inset-0 bg-cover bg-center opacity-40 mix-blend-luminosity" style={{ backgroundImage: "url('https://lh3.googleusercontent.com/aida-public/AB6AXuBomh5nZA8r1Hn37i86UlZRJTJTTqOgkVNgNClIrvnNsVm4FLdwG9A0BDaTt6JAO5HC40cuBByuI0cZIKQlibhdE-wSEcUYxFpIVpnIE5oluyoS6p2D2oVA-bhdf7xwLMViQqzNhFLLWoMm1QYKM9ewl_zkzE9_BOmajzRCLR0LcohF_qm9Y0-9T2CHRM1TDxtTVc4bsCxF6IjL5tC0gVPDIoJPeWDOiCMaDwJIR03J4opa5EOpKgk')" }}></div>
                            <div className="absolute inset-0 bg-gradient-to-t from-obsidian-surface via-obsidian-surface/60 to-transparent"></div>
                            <div className="relative z-10 space-y-1">
                                <span className="font-label-sm text-[10px] text-primary-container font-bold tracking-widest uppercase bg-primary-container/20 px-2 py-0.5 rounded border border-primary-container/30">GERBANG EKOSISTEM</span>
                                <h3 className="font-headline-md text-lg font-bold text-on-surface" style={{ fontFamily: "'Playfair Display', serif" }}>Katalog Layanan & Kemitraan</h3>
                                <p className="text-xs text-on-surface-variant">Akses ke produk digital, bursa lelang P2P, game, dan layanan bisnis terintegrasi.</p>
                            </div>
                        </div>

                        {/* Grid Menu Links */}
                        <div className="grid grid-cols-2 gap-3">
                            <Link href="/auction" className="glass-panel rounded-xl p-4 flex flex-col items-center justify-center gap-2 hover:border-primary-container/50 transition-all active:scale-95 group">
                                <span className="material-symbols-outlined text-2xl text-on-surface-variant group-hover:text-primary-container" style={{ fontVariationSettings: "'FILL' 1" }}>storefront</span>
                                <span className="text-xs font-bold text-on-surface text-center">Bursa Lelang P2P</span>
                            </Link>

                            <Link href="/digital-goods" className="glass-panel rounded-xl p-4 flex flex-col items-center justify-center gap-2 hover:border-primary-container/50 transition-all active:scale-95 group">
                                <span className="material-symbols-outlined text-2xl text-on-surface-variant group-hover:text-primary-container" style={{ fontVariationSettings: "'FILL' 1" }}>phone_iphone</span>
                                <span className="text-xs font-bold text-on-surface text-center">Lebur Koin & Pulsa</span>
                            </Link>

                            <a href="/games/harta-karun/index.html" target="_blank" className="glass-panel rounded-xl p-4 flex flex-col items-center justify-center gap-2 hover:border-primary-container/50 transition-all active:scale-95 group">
                                <span className="material-symbols-outlined text-2xl text-on-surface-variant group-hover:text-primary-container" style={{ fontVariationSettings: "'FILL' 1" }}>casino</span>
                                <span className="text-xs font-bold text-on-surface text-center">Harta Karun Gunung Mas</span>
                            </a>

                            <button onClick={() => setShowMissionModal(true)} className="glass-panel rounded-xl p-4 flex flex-col items-center justify-center gap-2 hover:border-primary-container/50 transition-all active:scale-95 group">
                                <span className="material-symbols-outlined text-2xl text-on-surface-variant group-hover:text-primary-container" style={{ fontVariationSettings: "'FILL' 1" }}>assignment_ind</span>
                                <span className="text-xs font-bold text-on-surface text-center">Misi Agen Unimart</span>
                            </button>
                        </div>

                        {/* Active Licenses Owned by User */}
                        {my_licenses && my_licenses.length > 0 && (
                            <div className="glass-panel rounded-xl p-4 border border-emerald-500/30 bg-emerald-950/10 space-y-3">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <span className="material-symbols-outlined text-emerald-400 text-sm">verified_user</span>
                                        <h4 className="font-bold text-xs text-emerald-300 uppercase tracking-wider">Lisensi Layanan Saya</h4>
                                    </div>
                                    <span className="text-[10px] text-emerald-400/80 font-bold bg-emerald-500/20 px-2 py-0.5 rounded-full border border-emerald-500/30">
                                        {my_licenses.length} Aktif
                                    </span>
                                </div>
                                <div className="space-y-2">
                                    {my_licenses.map((lic) => (
                                        <div key={lic.id} className="p-3 rounded-lg bg-surface/70 border border-glass-stroke flex items-center justify-between gap-2">
                                            <div className="min-w-0 flex-1">
                                                <div className="flex items-center gap-2">
                                                    <strong className="text-xs text-on-surface truncate">{lic.service?.name || 'Layanan Ekosistem'}</strong>
                                                    <span className="text-[9px] bg-emerald-500/20 text-emerald-400 font-mono font-bold px-1.5 py-0.2 rounded border border-emerald-500/30">
                                                        {lic.status}
                                                    </span>
                                                </div>
                                                <div className="font-mono text-[10px] text-primary-container tracking-wider mt-0.5 select-all">
                                                    {lic.license_key}
                                                </div>
                                            </div>
                                            {lic.service?.service_url && (
                                                <a 
                                                    href={lic.service.service_url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="px-2.5 py-1.5 bg-surface-variant hover:bg-primary-container/20 text-on-surface hover:text-primary-container font-bold text-[10px] rounded-lg border border-glass-stroke transition-all flex items-center gap-1 shrink-0"
                                                >
                                                    <span>Buka</span>
                                                    <span className="material-symbols-outlined text-xs">open_in_new</span>
                                                </a>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Subscription Services Catalog Preview (Dynamic from DB) */}
                        <div className="glass-panel rounded-xl p-4 border border-glass-stroke space-y-3">
                            <div className="flex justify-between items-center">
                                <div>
                                    <h4 className="font-bold text-xs text-on-surface uppercase tracking-wider">Layanan Ekosistem Berlangganan</h4>
                                    <p className="text-[10px] text-on-surface-variant">Daftarkan diri Anda atau kembangkan jaringan saudagar Anda</p>
                                </div>
                                <span className="text-[10px] text-primary-container font-bold bg-primary-container/10 px-2 py-0.5 rounded border border-primary-container/20">
                                    Hemat 15% via Koin Emas
                                </span>
                            </div>

                            <div className="space-y-3">
                                {ecosystem_services.length === 0 ? (
                                    <div className="p-4 text-center text-xs text-on-surface-variant">
                                        Belum ada layanan ekosistem aktif saat ini.
                                    </div>
                                ) : (
                                    ecosystem_services.map((svc) => (
                                        <div 
                                            key={svc.id}
                                            className="p-3.5 rounded-xl bg-surface/40 border border-glass-stroke hover:border-primary-container/40 transition-all flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 group"
                                        >
                                            <div className="flex items-start gap-3">
                                                <div className="w-10 h-10 rounded-xl bg-primary-container/10 border border-primary-container/30 flex items-center justify-center text-primary-container shrink-0 group-hover:scale-105 transition-transform">
                                                    <span className="material-symbols-outlined text-xl" style={{ fontVariationSettings: "'FILL' 1" }}>
                                                        {svc.icon || 'hub'}
                                                    </span>
                                                </div>
                                                <div className="space-y-1">
                                                    <div className="flex items-center gap-2 flex-wrap">
                                                        <strong className="text-xs text-on-surface font-bold">{svc.name}</strong>
                                                        {svc.category && (
                                                            <span className="text-[9px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-surface-variant text-on-surface-variant">
                                                                {svc.category}
                                                            </span>
                                                        )}
                                                    </div>
                                                    <p className="text-[10px] text-on-surface-variant leading-relaxed line-clamp-2">
                                                        {svc.description}
                                                    </p>
                                                    <div className="flex items-center gap-2 pt-0.5">
                                                        <span className="text-[10px] font-bold text-primary-container">
                                                            {Number(svc.price_gold).toLocaleString('id-ID')} EMAS
                                                        </span>
                                                        <span className="text-[10px] text-outline">/</span>
                                                        <span className="text-[10px] text-on-surface-variant">
                                                            Rp {Number(svc.price_fiat).toLocaleString('id-ID')}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="w-full sm:w-auto flex items-center justify-end">
                                                <Link 
                                                    href={`/services/${svc.code}/onboard`}
                                                    className="w-full sm:w-auto px-3.5 py-2 bg-gradient-to-r from-primary-container to-amber-500 text-on-primary font-bold text-xs rounded-xl shadow-sm hover:brightness-110 active:scale-95 transition-all text-center flex items-center justify-center gap-1.5"
                                                >
                                                    <span>Daftar / Akses</span>
                                                    <span className="material-symbols-outlined text-sm">arrow_forward</span>
                                                </Link>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </div>
                        </div>
                    </div>
                )}

                {/* ========================================================= */}
                {/* TAB CONTENT 4: REWARD & PROGRAM LOYALTI SAUDAGAR          */}
                {/* ========================================================= */}
                {activeTab === 'REWARDS' && (
                    <div className="space-y-4 animate-fade-in">
                        {/* Reward Hero Banner */}
                        <div className="glass-panel rounded-2xl overflow-hidden border border-primary-container/40 relative min-h-[140px] p-5 flex flex-col justify-end bg-gradient-to-br from-obsidian-surface via-surface/60 to-primary-container/10">
                            <div className="relative z-10 space-y-1">
                                <span className="font-label-sm text-[10px] text-primary-container font-bold tracking-widest uppercase bg-primary-container/20 px-2 py-0.5 rounded border border-primary-container/30">
                                    HADIAH & PRESTASI
                                </span>
                                <h3 className="font-headline-md text-lg font-bold text-on-surface" style={{ fontFamily: "'Playfair Display', serif" }}>
                                    Pusat Reward Saudagar
                                </h3>
                                <p className="text-xs text-on-surface-variant">
                                    Klaim bonus komitmen, reward streak harian, dan milestone royalti jaringan Anda.
                                </p>
                            </div>
                        </div>

                        {/* 7-Day Checkin Streak Milestones */}
                        <div className="glass-panel rounded-xl p-4 border border-glass-stroke space-y-3">
                            <div className="flex justify-between items-center">
                                <h4 className="font-bold text-xs text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                                    <span className="material-symbols-outlined text-primary-container text-sm">local_fire_department</span>
                                    Milestone Streak Harian ({checkinStatus.streak_count}/7 Hari)
                                </h4>
                                <span className="text-[10px] text-primary-container font-bold font-mono">
                                    {checkinStatus.streak_count >= 7 ? 'Siklus Tercapai' : `${7 - (checkinStatus.streak_count % 7)} Hari Menuju Koin Emas`}
                                </span>
                            </div>

                            <div className="grid grid-cols-7 gap-1 text-center">
                                {[
                                    { day: 1, reward: '250 P', type: 'SILVER' },
                                    { day: 2, reward: '250 P', type: 'SILVER' },
                                    { day: 3, reward: '500 P', type: 'SILVER' },
                                    { day: 4, reward: '500 P', type: 'SILVER' },
                                    { day: 5, reward: '750 P', type: 'SILVER' },
                                    { day: 6, reward: '1.000 P', type: 'SILVER' },
                                    { day: 7, reward: '+1 EMAS', type: 'GOLD' },
                                ].map((step) => {
                                    const isReached = checkinStatus.streak_count >= step.day;
                                    const isCurrent = (checkinStatus.streak_count % 7) + 1 === step.day;
                                    return (
                                        <div
                                            key={step.day}
                                            className={`p-1.5 rounded-lg border flex flex-col items-center justify-between min-h-[58px] transition-all ${
                                                isReached
                                                    ? 'bg-primary-container/25 border-primary-container text-primary-container font-bold'
                                                    : isCurrent
                                                    ? 'bg-surface border-amber-400/60 text-on-surface ring-1 ring-amber-400/40'
                                                    : 'bg-surface/30 border-glass-stroke text-on-surface-variant/50'
                                            }`}
                                        >
                                            <span className="text-[9px] uppercase">H-{step.day}</span>
                                            <span className="material-symbols-outlined text-xs">
                                                {isReached ? 'check_circle' : step.type === 'GOLD' ? 'stars' : 'toll'}
                                            </span>
                                            <span className={`text-[8px] font-mono leading-tight ${step.type === 'GOLD' ? 'text-primary-container font-bold' : ''}`}>
                                                {step.reward}
                                            </span>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>

                        {/* Royalty Claim Card */}
                        <div className="glass-panel rounded-xl p-4 border border-glass-stroke space-y-3 bg-gradient-to-r from-surface/50 to-primary-container/10">
                            <div className="flex justify-between items-start">
                                <div>
                                    <span className="text-[10px] text-on-surface-variant uppercase tracking-wider block">Bagi Hasil Royalti Karavan</span>
                                    <strong className="text-xl font-bold font-mono text-primary-container">
                                        {formatCoin(passive_income)} Koin Emas
                                    </strong>
                                    <span className="text-[10px] text-on-surface-variant block mt-0.5">
                                        Akumulasi dari transaksi downline Level 1 & Level 2
                                    </span>
                                </div>
                                <span className="material-symbols-outlined text-3xl text-primary-container opacity-80">account_tree</span>
                            </div>

                            <button
                                onClick={() => showMsg(passive_income > 0 ? 'Klaim royalti berhasil dicairkan ke saldo Emas!' : 'Belum ada saldo royalti yang siap diklaim.')}
                                className="w-full py-2.5 rounded-lg bg-gradient-to-r from-amber-500 to-primary-container text-on-primary font-bold text-xs tracking-wider uppercase hover:brightness-110 active:scale-95 transition-all shadow-[0_0_12px_rgba(255,215,0,0.3)] flex items-center justify-center gap-1.5"
                            >
                                <span className="material-symbols-outlined text-sm">redeem</span>
                                <span>Klaim Royalti ke Saldo Emas</span>
                            </button>
                        </div>

                        {/* Ecosystem Vouchers */}
                        <div className="glass-panel rounded-xl p-4 border border-glass-stroke space-y-3">
                            <h4 className="font-bold text-xs text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                                <span className="material-symbols-outlined text-primary-container text-sm">confirmation_number</span>
                                Kupon & Manfaat Saudagar
                            </h4>

                            <div className="space-y-2 text-xs">
                                <div className="p-3 rounded-lg bg-surface/50 border border-glass-stroke flex justify-between items-center">
                                    <div>
                                        <strong className="block text-on-surface">Voucher Diskon 15% Unimart POS Pro</strong>
                                        <span className="text-[10px] text-on-surface-variant">Potongan biaya langganan bulanan software kasir</span>
                                    </div>
                                    <button onClick={() => showMsg('Kode voucher UNIPOS15 berhasil disalin!')} className="px-2.5 py-1 bg-primary-container/20 text-primary-container border border-primary-container/50 font-bold text-[10px] rounded-md hover:bg-primary-container hover:text-on-primary transition-colors">
                                        Salin
                                    </button>
                                </div>

                                <div className="p-3 rounded-lg bg-surface/50 border border-glass-stroke flex justify-between items-center">
                                    <div>
                                        <strong className="block text-on-surface">Diskon 20% Bimbel WaliKelas.id</strong>
                                        <span className="text-[10px] text-on-surface-variant">Berlaku untuk paket les & bank soal online</span>
                                    </div>
                                    <button onClick={() => showMsg('Kode voucher WALIKELAS20 berhasil disalin!')} className="px-2.5 py-1 bg-primary-container/20 text-primary-container border border-primary-container/50 font-bold text-[10px] rounded-md hover:bg-primary-container hover:text-on-primary transition-colors">
                                        Salin
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
                    </>
                )}

                {/* ========================================================= */}
                {/* VIEW 2: MUTASI TRANSAKSI & CATATAN LEDGER                 */}
                {/* ========================================================= */}
                {activeBottomNav === 'MUTASI' && (
                    <div className="space-y-4 animate-fade-in pt-3.5 md:pt-5">
                        {/* Summary Header Cards */}
                        <div className="grid grid-cols-2 gap-3 font-mono">
                            <div className="glass-panel p-3.5 rounded-xl border border-glass-stroke bg-surface/50">
                                <span className="text-[10px] text-on-surface-variant uppercase tracking-wider block">Saldo Perak Aktif</span>
                                <strong className="text-lg font-bold text-on-surface block mt-0.5">
                                    {formatCoin(wallet?.silver_balance || 0)}
                                </strong>
                                <span className="text-[9px] text-secondary">Terproteksi Double-Entry</span>
                            </div>
                            <div className="glass-panel p-3.5 rounded-xl border border-primary-container/30 bg-primary-container/5">
                                <span className="text-[10px] text-primary-container uppercase tracking-wider block">Saldo Emas Murni</span>
                                <strong className="text-lg font-bold text-primary-container block mt-0.5">
                                    {formatCoin(wallet?.gold_balance || 0)} Koin
                                </strong>
                                <span className="text-[9px] text-on-surface-variant">≈ {formatRupiah((wallet?.gold_balance || 0) * 1000)}</span>
                            </div>
                        </div>

                        {/* Filter Chips */}
                        <div className="flex gap-2 overflow-x-auto pb-1 no-scrollbar text-xs">
                            {[
                                { id: 'ALL', label: 'Semua Transaksi' },
                                { id: 'SILVER', label: 'Koin Perak' },
                                { id: 'GOLD', label: 'Koin Emas' },
                                { id: 'CREDIT', label: 'Dana Masuk (+)' },
                                { id: 'DEBIT', label: 'Dana Keluar (-)' },
                            ].map(filter => (
                                <button
                                    key={filter.id}
                                    onClick={() => setMutasiFilter(filter.id as any)}
                                    className={`px-3 py-1.5 rounded-lg whitespace-nowrap text-xs font-semibold transition-all ${
                                        mutasiFilter === filter.id
                                            ? 'bg-primary-container text-on-primary shadow-[0_0_10px_rgba(255,215,0,0.3)]'
                                            : 'bg-surface/50 text-on-surface-variant border border-glass-stroke hover:text-on-surface'
                                    }`}
                                >
                                    {filter.label}
                                </button>
                            ))}
                        </div>

                        {/* Mutation Items List */}
                        <div className="glass-panel rounded-xl p-4 border border-glass-stroke space-y-3">
                            <div className="flex justify-between items-center">
                                <h4 className="font-bold text-xs text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                                    <span className="material-symbols-outlined text-primary-container text-sm">receipt_long</span>
                                    Daftar Mutasi Rekening
                                </h4>
                                <span className="text-[10px] text-primary-container font-mono">Sync Ledger</span>
                            </div>

                            {/* Render Ledger Entries or Escrows */}
                            {ledger_entries && ledger_entries.length > 0 ? (
                                <div className="space-y-2">
                                    {ledger_entries
                                        .filter(e => {
                                            if (mutasiFilter === 'SILVER' && e.currency !== 'SILVER') return false;
                                            if (mutasiFilter === 'GOLD' && e.currency !== 'GOLD') return false;
                                            if (mutasiFilter === 'CREDIT' && e.type !== 'CREDIT') return false;
                                            if (mutasiFilter === 'DEBIT' && e.type !== 'DEBIT') return false;
                                            return true;
                                        })
                                        .map(entry => (
                                            <div key={entry.id} className="p-3 rounded-xl bg-surface/40 border border-glass-stroke flex justify-between items-center">
                                                <div className="space-y-0.5">
                                                    <strong className="block text-xs text-on-surface">
                                                        {entry.ledger?.description || 'Mutasi Saldo Transaksi'}
                                                    </strong>
                                                    <span className="text-[10px] font-mono text-on-surface-variant">
                                                        {new Date(entry.created_at).toLocaleString('id-ID')}
                                                    </span>
                                                </div>
                                                <div className="text-right">
                                                    <strong className={`font-mono text-xs font-bold ${entry.type === 'CREDIT' ? 'text-secondary' : 'text-error'}`}>
                                                        {entry.type === 'CREDIT' ? '+' : '-'}{formatCoin(entry.amount)} {entry.currency}
                                                    </strong>
                                                    <span className="block text-[9px] uppercase tracking-wider text-on-surface-variant">
                                                        {entry.type === 'CREDIT' ? 'KREDIT' : 'DEBIT'}
                                                    </span>
                                                </div>
                                            </div>
                                        ))}
                                </div>
                            ) : escrowList.length > 0 ? (
                                <div className="space-y-2">
                                    {escrowList.map(e => (
                                        <div key={e.id} className="p-3 rounded-xl bg-surface/40 border border-glass-stroke flex justify-between items-center">
                                            <div className="space-y-0.5">
                                                <strong className="block text-xs text-on-surface">
                                                    Staking Misi: {e.quest?.title || 'Ekspedisi Karavan'}
                                                </strong>
                                                <span className="text-[10px] font-mono text-on-surface-variant">
                                                    {new Date(e.created_at).toLocaleString('id-ID')}
                                                </span>
                                            </div>
                                            <div className="text-right">
                                                <strong className={`font-mono text-xs font-bold ${e.status === 'SUCCESS' ? 'text-secondary' : 'text-primary-container'}`}>
                                                    {e.status === 'SUCCESS' ? `+${e.quest?.reward_amount_gold || 0} EMAS` : `-${formatCoin(e.staked_silver)} PERAK`}
                                                </strong>
                                                <span className={`block text-[9px] uppercase tracking-wider ${e.status === 'SUCCESS' ? 'text-secondary' : 'text-primary-container'}`}>
                                                    {e.status === 'SUCCESS' ? 'REWARD CAIR' : 'STAKING ESCROW'}
                                                </span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center py-8 text-on-surface-variant space-y-2">
                                    <span className="material-symbols-outlined text-4xl opacity-40">receipt</span>
                                    <p className="text-xs">Belum ada catatan mutasi transaksi pada periode ini.</p>
                                </div>
                            )}
                        </div>

                        {/* Double Entry Ledger Security Guarantee */}
                        <div className="p-3 rounded-xl border border-primary-container/20 bg-primary-container/5 flex items-center gap-3">
                            <span className="material-symbols-outlined text-primary-container text-2xl">verified_user</span>
                            <div className="text-[10px] text-on-surface-variant leading-tight">
                                <strong className="text-on-surface block font-bold">Locomo Double-Entry Ledger Terverifikasi</strong>
                                Seluruh mutasi saldo dicatat menggunakan standar akuntansi perbankan ganda (Debit & Kredit berimbang) dan tercatat secara permanen.
                            </div>
                        </div>
                    </div>
                )}

                {/* ========================================================= */}
                {/* VIEW 3: WALLET & MANAJEMEN DOMPET DIGITAL                 */}
                {/* ========================================================= */}
                {activeBottomNav === 'WALLET' && (
                    <div className="space-y-4 animate-fade-in pt-3.5 md:pt-5">
                        {/* Elite Black Metal Sovereign Card */}
                        <div className="relative rounded-2xl p-5 overflow-hidden border border-primary-container/50 shadow-2xl bg-gradient-to-br from-obsidian-surface via-surface to-obsidian-surface text-on-surface">
                            <div className="absolute top-0 right-0 w-36 h-36 bg-primary-container/10 rounded-full blur-2xl pointer-events-none"></div>

                            <div className="flex justify-between items-start mb-6">
                                <div>
                                    <span className="text-[9px] font-mono tracking-widest text-primary-container uppercase block">SOVEREIGN WALLET</span>
                                    <h3 className="font-bold text-sm text-on-surface" style={{ fontFamily: "'Playfair Display', serif" }}>
                                        PitiCoin Digital Vault
                                    </h3>
                                </div>
                                <span className="material-symbols-outlined text-3xl text-primary-container">contactless</span>
                            </div>

                            <div className="space-y-2 mb-6">
                                <span className="text-[10px] text-on-surface-variant block">Total Estimasi Nilai Dompet</span>
                                <div className="text-2xl font-mono font-bold text-primary-container tracking-wider">
                                    {formatRupiah((wallet?.gold_balance || 0) * 1000 + (wallet?.silver_balance || 0) * 10)}
                                </div>
                                <div className="flex gap-4 text-xs font-mono text-on-surface-variant">
                                    <span>Perak: <strong className="text-on-surface">{formatCoin(wallet?.silver_balance || 0)}</strong></span>
                                    <span>•</span>
                                    <span>Emas: <strong className="text-primary-container">{formatCoin(wallet?.gold_balance || 0)}</strong></span>
                                </div>
                            </div>

                            <div className="flex justify-between items-end text-[10px] font-mono border-t border-glass-stroke pt-3 text-on-surface-variant">
                                <div>
                                    <span className="block text-[8px] uppercase">ID SAUDAGAR</span>
                                    <span className="text-on-surface font-bold">PTC-{currentUser.id.slice(0, 8).toUpperCase()}</span>
                                </div>
                                <div>
                                    <span className="block text-[8px] uppercase">STATUS KEAMANAN</span>
                                    <span className="text-secondary font-bold">LEDGER AKTIF</span>
                                </div>
                            </div>
                        </div>

                        {/* Quick Wallet Actions */}
                        <div className="grid grid-cols-4 gap-2 text-center text-[10px]">
                            <button
                                onClick={() => showMsg('Fitur Top-Up Koin Perak via QRIS / VA sedang diproses!')}
                                className="glass-panel p-3 rounded-xl flex flex-col items-center gap-1.5 hover:border-primary-container/50 active:scale-95 transition-all"
                            >
                                <span className="w-10 h-10 rounded-full bg-primary-container/20 text-primary-container flex items-center justify-center">
                                    <span className="material-symbols-outlined text-lg">add_circle</span>
                                </span>
                                <span className="font-bold text-on-surface">Top Up</span>
                            </button>

                            <button
                                onClick={() => setShowScanModal(true)}
                                className="glass-panel p-3 rounded-xl flex flex-col items-center gap-1.5 hover:border-primary-container/50 active:scale-95 transition-all"
                            >
                                <span className="w-10 h-10 rounded-full bg-surface-variant text-on-surface flex items-center justify-center">
                                    <span className="material-symbols-outlined text-lg">send</span>
                                </span>
                                <span className="font-bold text-on-surface">Transfer</span>
                            </button>

                            <Link
                                href="/digital-goods"
                                className="glass-panel p-3 rounded-xl flex flex-col items-center gap-1.5 hover:border-primary-container/50 active:scale-95 transition-all"
                            >
                                <span className="w-10 h-10 rounded-full bg-amber-500/20 text-primary-container flex items-center justify-center">
                                    <span className="material-symbols-outlined text-lg">currency_exchange</span>
                                </span>
                                <span className="font-bold text-on-surface">Lebur Emas</span>
                            </Link>

                            <Link
                                href="/auction"
                                className="glass-panel p-3 rounded-xl flex flex-col items-center gap-1.5 hover:border-primary-container/50 active:scale-95 transition-all"
                            >
                                <span className="w-10 h-10 rounded-full bg-secondary/20 text-secondary flex items-center justify-center">
                                    <span className="material-symbols-outlined text-lg">storefront</span>
                                </span>
                                <span className="font-bold text-on-surface">Bursa P2P</span>
                            </Link>
                        </div>

                        {/* Connected Bank Account */}
                        <div className="glass-panel rounded-xl p-4 border border-glass-stroke space-y-3">
                            <div className="flex justify-between items-center">
                                <h4 className="font-bold text-xs text-on-surface uppercase tracking-wider">Rekening Pencairan Terhubung</h4>
                                <span className="text-[10px] text-secondary font-bold flex items-center gap-1">
                                    <span className="material-symbols-outlined text-xs">verified</span> Terverifikasi
                                </span>
                            </div>

                            <div className="p-3 rounded-lg bg-surface/50 border border-glass-stroke flex items-center justify-between">
                                <div className="flex items-center gap-3">
                                    <div className="w-8 h-8 rounded bg-primary-container/20 border border-primary-container/40 flex items-center justify-center text-primary-container font-bold text-xs font-mono">
                                        BCA
                                    </div>
                                    <div>
                                        <strong className="block text-xs text-on-surface">{currentUser.name}</strong>
                                        <span className="text-[10px] font-mono text-on-surface-variant">8801 •••• •••• 921</span>
                                    </div>
                                </div>
                                <button onClick={() => showMsg('Rekening pencairan aktif.')} className="text-on-surface-variant hover:text-white">
                                    <span className="material-symbols-outlined text-base">edit</span>
                                </button>
                            </div>
                        </div>
                    </div>
                )}

                {/* ========================================================= */}
                {/* VIEW 4: PROFIL & PENGATURAN AKUN SAUDAGAR                */}
                {/* ========================================================= */}
                {activeBottomNav === 'PROFILE' && (
                    <div className="space-y-4 animate-fade-in pt-3.5 md:pt-5">
                        {/* Profile Identity Card */}
                        <div className="glass-panel rounded-2xl p-5 border border-primary-container/40 text-center space-y-3 relative overflow-hidden">
                            <div className="relative inline-block mx-auto">
                                <div className="w-20 h-20 rounded-full border-2 border-primary-container p-1 shadow-[0_0_15px_rgba(255,215,0,0.5)] mx-auto bg-surface overflow-hidden">
                                    <img
                                        alt="Avatar"
                                        className="w-full h-full object-cover rounded-full"
                                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuDi47Slkw4S4Y_KgVtZ0EISwFfTSIkSG4_2UXNIGz_TUpZPGsvQ9quu9zXxMlopsBUFeZOhn_yxJcMebklLuAHrdJXZOTxaanMZVSTn0-ghrNi4-HZtK5ZxRZPonkQg3bipaDJJjt0ghwB5GswyeN8IpGyxjzOyGGh3P6dz6mRoq-anKb_pf5frN99lP138Yfr7ElxVoaIt9Mds_TGIwKPmRA516YI1zc5MLi_Su07Z9ZLoMFO-KEQ"
                                    />
                                </div>
                                <span className="absolute bottom-0 right-0 bg-primary-container text-on-primary font-mono text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center border-2 border-obsidian-surface shadow">
                                    {currentUser.level}
                                </span>
                            </div>

                            <div>
                                <h3 className="font-bold text-base text-primary-container" style={{ fontFamily: "'Playfair Display', serif" }}>
                                    {currentUser.name}
                                </h3>
                                <span className="text-xs text-on-surface-variant font-mono block">
                                    @{currentUser.username || currentUser.whatsapp} • ID: {currentUser.id.slice(0, 8)}
                                </span>
                            </div>

                            <div className="flex justify-center gap-2">
                                <span className="px-2.5 py-0.5 rounded-full bg-primary-container/20 text-primary-container text-[10px] font-bold border border-primary-container/40">
                                    Lisensi {currentUser.license_type || 'PRATAMA'}
                                </span>
                                <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-bold border bg-surface/50 ${factionColorClass} border-glass-stroke`}>
                                    Faksi {currentUser.faction}
                                </span>
                            </div>
                        </div>

                        {/* Referral Link Quick Card */}
                        <div className="glass-panel rounded-xl p-4 border border-glass-stroke space-y-2">
                            <span className="text-[10px] text-on-surface-variant uppercase tracking-wider block">Tautan Undangan Saudagar Anda</span>
                            <div className="flex gap-2">
                                <input
                                    readOnly
                                    value={referralLink}
                                    className="flex-1 bg-surface/60 border border-glass-stroke rounded-lg px-3 py-2 text-xs font-mono text-on-surface outline-none"
                                />
                                <button
                                    onClick={() => {
                                        navigator.clipboard.writeText(referralLink);
                                        showMsg('Tautan referral berhasil disalin!');
                                    }}
                                    className="px-3 py-2 bg-primary-container text-on-primary font-bold text-xs rounded-lg active:scale-95 transition-all shadow"
                                >
                                    Salin
                                </button>
                            </div>
                        </div>

                        {/* Menu Options - Mapping Fitur Utama & Kepatuhan */}
                        <div className="glass-panel rounded-2xl overflow-hidden border border-glass-stroke divide-y divide-glass-stroke text-xs shadow-lg">
                            {/* 1. Profile Edit */}
                            <Link
                                href={route('profile.edit')}
                                className="w-full p-3.5 flex items-center justify-between hover:bg-white/5 transition-colors text-left group"
                            >
                                <div className="flex items-center gap-3">
                                    <div className="w-8 h-8 rounded-lg bg-primary-container/15 text-primary-container flex items-center justify-center border border-primary-container/30">
                                        <span className="material-symbols-outlined text-base">manage_accounts</span>
                                    </div>
                                    <div>
                                        <span className="font-semibold text-white group-hover:text-primary-container transition-colors block">
                                            Pengaturan Profil & Avatar Kustom
                                        </span>
                                        <span className="text-[10px] text-on-surface-variant block">
                                            Kustomisasi avatar, audio SFX, dan manajemen sesi login
                                        </span>
                                    </div>
                                </div>
                                <span className="material-symbols-outlined text-on-surface-variant text-sm group-hover:translate-x-1 transition-transform">chevron_right</span>
                            </Link>

                            {/* 2. Help Center */}
                            <Link
                                href={route('help.index')}
                                className="w-full p-3.5 flex items-center justify-between hover:bg-white/5 transition-colors text-left group"
                            >
                                <div className="flex items-center gap-3">
                                    <div className="w-8 h-8 rounded-lg bg-blue-500/15 text-blue-400 flex items-center justify-center border border-blue-500/30">
                                        <span className="material-symbols-outlined text-base">help_center</span>
                                    </div>
                                    <div>
                                        <span className="font-semibold text-white group-hover:text-blue-300 transition-colors block">
                                            Pusat Bantuan & FAQ Interaktif
                                        </span>
                                        <span className="text-[10px] text-on-surface-variant block">
                                            Panduan ekspedisi, tanya jawab, & konsultasi Saudagar
                                        </span>
                                    </div>
                                </div>
                                <span className="material-symbols-outlined text-on-surface-variant text-sm group-hover:translate-x-1 transition-transform">chevron_right</span>
                            </Link>

                            {/* 3. Baitul Mal */}
                            <Link
                                href={route('baitul-mal.index')}
                                className="w-full p-3.5 flex items-center justify-between hover:bg-white/5 transition-colors text-left group"
                            >
                                <div className="flex items-center gap-3">
                                    <div className="w-8 h-8 rounded-lg bg-amber-400/15 text-amber-400 flex items-center justify-center border border-amber-400/30">
                                        <span className="material-symbols-outlined text-base">volunteer_activism</span>
                                    </div>
                                    <div>
                                        <span className="font-semibold text-white group-hover:text-amber-300 transition-colors block">
                                            Kas Sosial Baitul Mal & Sedekah Emas
                                        </span>
                                        <span className="text-[10px] text-on-surface-variant block">
                                            Transparansi kas sosial, live wall muhsinin, & santunan mustahik
                                        </span>
                                    </div>
                                </div>
                                <span className="material-symbols-outlined text-on-surface-variant text-sm group-hover:translate-x-1 transition-transform">chevron_right</span>
                            </Link>

                            {/* 4. Pasar Lelang */}
                            <Link
                                href={route('auction.index')}
                                className="w-full p-3.5 flex items-center justify-between hover:bg-white/5 transition-colors text-left group"
                            >
                                <div className="flex items-center gap-3">
                                    <div className="w-8 h-8 rounded-lg bg-surface-container text-primary-container flex items-center justify-center border border-glass-stroke">
                                        <span className="material-symbols-outlined text-base">gavel</span>
                                    </div>
                                    <div>
                                        <span className="font-semibold text-white group-hover:text-primary-container transition-colors block">
                                            Pasar Lelang Digital (Bursa Nilai)
                                        </span>
                                        <span className="text-[10px] text-on-surface-variant block">
                                            Lelang aset langka, hak kontrak misi, & smart escrow
                                        </span>
                                    </div>
                                </div>
                                <span className="material-symbols-outlined text-on-surface-variant text-sm group-hover:translate-x-1 transition-transform">chevron_right</span>
                            </Link>

                            {/* 5. Upgrade Lisensi */}
                            <button
                                onClick={() => setShowUpgradeModal(true)}
                                className="w-full p-3.5 flex items-center justify-between hover:bg-white/5 transition-colors text-left group"
                            >
                                <div className="flex items-center gap-3">
                                    <div className="w-8 h-8 rounded-lg bg-secondary/15 text-secondary flex items-center justify-center border border-secondary/30">
                                        <span className="material-symbols-outlined text-base">stars</span>
                                    </div>
                                    <div>
                                        <span className="font-semibold text-white group-hover:text-secondary transition-colors block">
                                            Tingkatkan Lisensi Saudagar
                                        </span>
                                        <span className="text-[10px] text-on-surface-variant block">
                                            Buka komisi 2-level karavan & akses modul POS Unimart
                                        </span>
                                    </div>
                                </div>
                                <span className="material-symbols-outlined text-on-surface-variant text-sm group-hover:translate-x-1 transition-transform">chevron_right</span>
                            </button>

                            {/* 6. Syarat & Ketentuan */}
                            <Link
                                href={route('legal.terms')}
                                className="w-full p-3.5 flex items-center justify-between hover:bg-white/5 transition-colors text-left group"
                            >
                                <div className="flex items-center gap-3">
                                    <div className="w-8 h-8 rounded-lg bg-surface-container text-on-surface-variant flex items-center justify-center border border-glass-stroke">
                                        <span className="material-symbols-outlined text-base">description</span>
                                    </div>
                                    <div>
                                        <span className="font-semibold text-white group-hover:text-primary-container transition-colors block">
                                            Syarat & Ketentuan Layanan
                                        </span>
                                        <span className="text-[10px] text-on-surface-variant block">
                                            Ketentuan staking perak, reward emas, dan federasi Locomo
                                        </span>
                                    </div>
                                </div>
                                <span className="material-symbols-outlined text-on-surface-variant text-sm group-hover:translate-x-1 transition-transform">chevron_right</span>
                            </Link>

                            {/* 7. Kebijakan Privasi UU PDP */}
                            <Link
                                href={route('legal.privacy')}
                                className="w-full p-3.5 flex items-center justify-between hover:bg-white/5 transition-colors text-left group"
                            >
                                <div className="flex items-center gap-3">
                                    <div className="w-8 h-8 rounded-lg bg-surface-container text-emerald-400 flex items-center justify-center border border-glass-stroke">
                                        <span className="material-symbols-outlined text-base">policy</span>
                                    </div>
                                    <div>
                                        <span className="font-semibold text-white group-hover:text-emerald-300 transition-colors block">
                                            Kebijakan Privasi (UU PDP No. 27/2022)
                                        </span>
                                        <span className="text-[10px] text-on-surface-variant block">
                                            Hak subjek data, perlindungan WhatsApp, dan kuki
                                        </span>
                                    </div>
                                </div>
                                <span className="material-symbols-outlined text-on-surface-variant text-sm group-hover:translate-x-1 transition-transform">chevron_right</span>
                            </Link>
                        </div>

                        {/* Logout Button */}
                        <button
                            onClick={handleLogout}
                            className="w-full py-3 rounded-xl bg-error/15 border border-error/40 text-error font-bold text-xs uppercase tracking-wider hover:bg-error/25 active:scale-95 transition-all flex items-center justify-center gap-2"
                        >
                            <span className="material-symbols-outlined text-base">logout</span>
                            <span>Keluar dari Akun Saudagar</span>
                        </button>
                    </div>
                )}
            </main>

            {/* ========================================================= */}
            {/* 7. BOTTOM NAVIGATION BAR (DIGITAL WALLET FIXED DOCK)       */}
            {/* ========================================================= */}
            <nav className="bg-obsidian-surface/95 backdrop-blur-2xl text-primary-container font-label-sm text-[10px] uppercase tracking-widest fixed bottom-0 left-0 w-full z-40 flex justify-around items-center px-2 pb-5 pt-2 rounded-t-2xl border-t border-glass-stroke shadow-[0_-10px_35px_rgba(0,0,0,0.9)] max-w-lg mx-auto left-0 right-0">
                <button
                    onClick={() => setActiveBottomNav('HOME')}
                    className={`flex flex-col items-center justify-center flex-1 transition-all ${
                        activeBottomNav === 'HOME' ? 'text-primary-container scale-105 font-bold' : 'text-on-surface-variant/70 hover:text-on-surface'
                    }`}
                >
                    <span className="material-symbols-outlined mb-0.5 text-xl" style={{ fontVariationSettings: activeBottomNav === 'HOME' ? "'FILL' 1" : "'FILL' 0" }}>home</span>
                    <span className="text-[10px] font-semibold tracking-wider uppercase">Beranda</span>
                </button>

                <button
                    onClick={() => setActiveBottomNav('MUTASI')}
                    className={`flex flex-col items-center justify-center flex-1 transition-all ${
                        activeBottomNav === 'MUTASI' ? 'text-primary-container scale-105 font-bold' : 'text-on-surface-variant/70 hover:text-on-surface'
                    }`}
                >
                    <span className="material-symbols-outlined mb-0.5 text-xl" style={{ fontVariationSettings: activeBottomNav === 'MUTASI' ? "'FILL' 1" : "'FILL' 0" }}>receipt_long</span>
                    <span className="text-[10px] font-semibold tracking-wider uppercase">Mutasi</span>
                </button>

                {/* Prominent Digital Wallet Barcode / QR Scanner FAB */}
                <div className="flex flex-col items-center -mt-6 px-1">
                    <button
                        onClick={() => setShowScanModal(true)}
                        className="w-13 h-13 md:w-14 md:h-14 rounded-full bg-gradient-to-tr from-amber-600 via-primary-container to-yellow-300 text-on-primary flex items-center justify-center shadow-[0_0_20px_rgba(255,215,0,0.6)] border-4 border-obsidian-surface active:scale-90 hover:scale-105 transition-all group"
                        title="Pindai Barcode / QRIS"
                    >
                        <span className="material-symbols-outlined text-2xl font-bold group-hover:rotate-12 transition-transform">qr_code_scanner</span>
                    </button>
                    <span className="text-[9px] uppercase tracking-wider font-bold text-primary-container mt-0.5">Scan</span>
                </div>

                <button
                    onClick={() => setActiveBottomNav('WALLET')}
                    className={`flex flex-col items-center justify-center flex-1 transition-all ${
                        activeBottomNav === 'WALLET' ? 'text-primary-container scale-105 font-bold' : 'text-on-surface-variant/70 hover:text-on-surface'
                    }`}
                >
                    <span className="material-symbols-outlined mb-0.5 text-xl" style={{ fontVariationSettings: activeBottomNav === 'WALLET' ? "'FILL' 1" : "'FILL' 0" }}>account_balance_wallet</span>
                    <span className="text-[10px] font-semibold tracking-wider uppercase">Wallet</span>
                </button>

                <button
                    onClick={() => setActiveBottomNav('PROFILE')}
                    className={`flex flex-col items-center justify-center flex-1 transition-all ${
                        activeBottomNav === 'PROFILE' ? 'text-primary-container scale-105 font-bold' : 'text-on-surface-variant/70 hover:text-on-surface'
                    }`}
                >
                    <span className="material-symbols-outlined mb-0.5 text-xl" style={{ fontVariationSettings: activeBottomNav === 'PROFILE' ? "'FILL' 1" : "'FILL' 0" }}>person</span>
                    <span className="text-[10px] font-semibold tracking-wider uppercase">Profil</span>
                </button>
            </nav>

            {/* MODAL: CATAT TRANSAKSI KASIR CEPAT (POS) REMOVED */}

            {/* ========================================================= */}
            {/* 9. MODAL: UPGRADE LISENSI & BERLANGGANAN LAYANAN           */}
            {/* ========================================================= */}
            {showUpgradeModal && (
                <div className="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4">
                    <div className="glass-panel w-full max-w-md rounded-2xl overflow-hidden border border-primary-container/40 shadow-2xl p-5 space-y-4 animate-fade-in max-h-[90vh] overflow-y-auto">
                        <div className="flex justify-between items-center border-b border-glass-stroke pb-3">
                            <h3 className="font-bold text-sm text-primary-container flex items-center gap-2" style={{ fontFamily: "'Playfair Display', serif" }}>
                                <span className="material-symbols-outlined text-primary-container">stars</span>
                                Upgrade Tingkatan Lisensi Saudagar
                            </h3>
                            <button onClick={() => setShowUpgradeModal(false)} className="text-on-surface-variant hover:text-white">
                                <span className="material-symbols-outlined text-lg">close</span>
                            </button>
                        </div>

                        <p className="text-xs text-on-surface-variant leading-relaxed">
                            Tingkatkan lisensi Anda untuk membuka akses ke komisi Karavan Downline 2-Level, seluruh paket misi, dan modul Kasir POS Unimart Juragan Pro.
                        </p>

                        <div className="space-y-3 text-xs">
                            {/* Option 1: Petualang */}
                            <div className="p-3.5 rounded-xl border border-glass-stroke bg-surface/50 space-y-2">
                                <div className="flex justify-between items-center">
                                    <div>
                                        <strong className="text-sm font-bold text-on-surface">Paket Petualang</strong>
                                        <span className="block text-[10px] text-on-surface-variant">Komisi L1 25% + 100.000 Perak</span>
                                    </div>
                                    <div className="text-right">
                                        <span className="font-bold text-primary-container font-mono text-xs">Rp 100.000</span>
                                        <span className="text-[10px] text-on-surface-variant block font-mono">atau 80 Emas</span>
                                    </div>
                                </div>
                                <button
                                    onClick={() => handleUpgradeLicense('PETUALANG', 'GOLD')}
                                    disabled={loading}
                                    className="w-full py-2 rounded-lg bg-surface-variant hover:bg-primary-container hover:text-on-primary font-bold text-xs transition-colors"
                                >
                                    Upgrade Petualang (80 Koin Emas)
                                </button>
                            </div>

                            {/* Option 2: Saudagar (Max Tier) */}
                            <div className="p-3.5 rounded-xl border-2 border-primary-container bg-primary-container/10 space-y-2 shadow-[0_0_15px_rgba(255,215,0,0.15)]">
                                <div className="flex justify-between items-center">
                                    <div>
                                        <div className="flex items-center gap-1.5">
                                            <strong className="text-sm font-bold text-primary-container">Paket Saudagar Inti</strong>
                                            <span className="text-[9px] bg-primary-container text-on-primary px-1.5 py-0.2 rounded font-bold">MAX</span>
                                        </div>
                                        <span className="block text-[10px] text-on-surface-variant">Komisi L1 25% + L2 10% + 200k Perak + POS Pro</span>
                                    </div>
                                    <div className="text-right">
                                        <span className="font-bold text-primary-fixed-dim font-mono text-xs">Rp 200.000</span>
                                        <span className="text-[10px] text-primary-container block font-mono">atau 150 Emas (-25%)</span>
                                    </div>
                                </div>
                                <button
                                    onClick={() => handleUpgradeLicense('SAUDAGAR', 'GOLD')}
                                    disabled={loading}
                                    className="w-full py-2.5 rounded-lg bg-primary-container text-on-primary font-bold text-xs uppercase tracking-wider hover:brightness-110 active:scale-95 transition-all shadow-[0_0_12px_rgba(255,215,0,0.4)]"
                                >
                                    Upgrade Saudagar Inti (150 Koin Emas)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* ========================================================= */}
            {/* 10. MODAL: SCAN BARCODE & QRIS (DIGITAL WALLET UX)        */}
            {/* ========================================================= */}
            {showScanModal && (
                <div className="fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-4">
                    <div className="glass-panel w-full max-w-sm rounded-2xl overflow-hidden border border-primary-container/50 shadow-2xl p-5 space-y-4 animate-fade-in">
                        {/* Header with Close */}
                        <div className="flex justify-between items-center border-b border-glass-stroke pb-3">
                            <h3 className="font-bold text-sm text-primary-container flex items-center gap-2">
                                <span className="material-symbols-outlined text-primary-container">qr_code_scanner</span>
                                Pindai Barcode / QRIS
                            </h3>
                            <button onClick={() => setShowScanModal(false)} className="text-on-surface-variant hover:text-white">
                                <span className="material-symbols-outlined text-lg">close</span>
                            </button>
                        </div>

                        {/* Switcher Tab: Pindai Kode vs QR Saya */}
                        <div className="grid grid-cols-2 gap-1 bg-surface/60 p-1 rounded-xl border border-glass-stroke text-xs">
                            <button
                                onClick={() => setScanTab('SCANNER')}
                                className={`py-1.5 rounded-lg font-bold transition-all ${
                                    scanTab === 'SCANNER' ? 'bg-primary-container text-on-primary shadow' : 'text-on-surface-variant'
                                }`}
                            >
                                Pindai Kode
                            </button>
                            <button
                                onClick={() => setScanTab('MY_QR')}
                                className={`py-1.5 rounded-lg font-bold transition-all ${
                                    scanTab === 'MY_QR' ? 'bg-primary-container text-on-primary shadow' : 'text-on-surface-variant'
                                }`}
                            >
                                Barcode QR Saya
                            </button>
                        </div>

                        {scanTab === 'SCANNER' ? (
                            <div className="space-y-4 text-center">
                                {/* Camera Viewfinder Frame with glowing corners and laser line */}
                                <div className="relative w-56 h-56 mx-auto rounded-2xl bg-black/70 border border-glass-stroke overflow-hidden flex items-center justify-center shadow-inner">
                                    {/* 4 Corners */}
                                    <div className="absolute top-2 left-2 w-6 h-6 border-t-2 border-l-2 border-primary-container"></div>
                                    <div className="absolute top-2 right-2 w-6 h-6 border-t-2 border-r-2 border-primary-container"></div>
                                    <div className="absolute bottom-2 left-2 w-6 h-6 border-b-2 border-l-2 border-primary-container"></div>
                                    <div className="absolute bottom-2 right-2 w-6 h-6 border-b-2 border-r-2 border-primary-container"></div>

                                    {/* Animated Laser Beam */}
                                    <div className="absolute left-3 right-3 h-0.5 bg-gradient-to-r from-transparent via-amber-400 to-transparent shadow-[0_0_12px_#ffd700] animate-scan-laser"></div>

                                    {/* Center Target Icon */}
                                    <span className="material-symbols-outlined text-4xl text-white/20">crop_free</span>
                                </div>

                                <p className="text-xs text-on-surface-variant max-w-xs mx-auto">
                                    Arahkan kamera ke QRIS Merchant, Barcode Toko Unimart, atau QR Saudagar rekanan.
                                </p>

                                {/* Action Buttons */}
                                <div className="flex justify-center gap-3 text-xs">
                                    <button onClick={() => showMsg('Lampu kilat diaktifkan.')} className="px-3 py-1.5 rounded-lg bg-surface/50 border border-glass-stroke text-on-surface flex items-center gap-1.5 hover:bg-white/10 active:scale-95 transition-all">
                                        <span className="material-symbols-outlined text-sm">flash_on</span>
                                        <span>Flash</span>
                                    </button>
                                    <button onClick={() => showMsg('Pilih gambar barcode dari galeri.')} className="px-3 py-1.5 rounded-lg bg-surface/50 border border-glass-stroke text-on-surface flex items-center gap-1.5 hover:bg-white/10 active:scale-95 transition-all">
                                        <span className="material-symbols-outlined text-sm">photo_library</span>
                                        <span>Galeri</span>
                                    </button>
                                </div>
                            </div>
                        ) : (
                            <div className="space-y-4 text-center py-2">
                                <div className="bg-white p-4 rounded-2xl w-48 h-48 mx-auto flex flex-col items-center justify-center shadow-lg">
                                    <span className="material-symbols-outlined text-8xl text-black">qr_code_2</span>
                                    <span className="text-[10px] font-mono text-black font-bold tracking-wider">
                                        PTC-{currentUser.id.slice(0, 8).toUpperCase()}
                                    </span>
                                </div>
                                <div className="space-y-1">
                                    <h4 className="font-bold text-xs text-on-surface">{currentUser.name}</h4>
                                    <p className="text-[10px] text-on-surface-variant">
                                        Tunjukkan QR ini untuk menerima transfer saldo Perak atau Koin Emas.
                                    </p>
                                </div>
                                <button
                                    onClick={() => {
                                        navigator.clipboard.writeText(`PTC-${currentUser.id.slice(0, 8).toUpperCase()}`);
                                        showMsg('ID Saudagar berhasil disalin!');
                                    }}
                                    className="w-full py-2 bg-primary-container text-on-primary font-bold text-xs rounded-lg uppercase tracking-wider active:scale-95 transition-all"
                                >
                                    Salin ID Saudagar
                                </button>
                            </div>
                        )}
                    </div>
                </div>
            )}

            {/* ========================================================= */}
            {/* 11. MODAL: MISI AGEN UNIMART                              */}
            {/* ========================================================= */}
            {showMissionModal && (
                <div className="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4">
                    <div className="glass-panel w-full max-w-md rounded-2xl overflow-hidden border border-primary-container/40 shadow-2xl p-5 space-y-4 animate-fade-in">
                        <div className="flex justify-between items-center border-b border-glass-stroke pb-3">
                            <h3 className="font-bold text-sm text-primary-container flex items-center gap-2">
                                <span className="material-symbols-outlined text-primary-container">assignment_ind</span>
                                Misi Kemitraan Agen Unimart
                            </h3>
                            <button onClick={() => setShowMissionModal(false)} className="text-on-surface-variant hover:text-white">
                                <span className="material-symbols-outlined text-lg">close</span>
                            </button>
                        </div>

                        <p className="text-xs text-on-surface-variant leading-relaxed">
                            Daftarkan toko kelontong atau UMKM di sekitar Anda untuk menggunakan aplikasi kasir Unimart POS Juragan. Dapatkan komisi instan sebesar <strong>+50 Koin Emas</strong> dan <strong>100.000 Perak</strong> per toko yang berhasil terverifikasi!
                        </p>

                        <div className="p-3.5 rounded-xl border border-glass-stroke bg-surface/50 space-y-2 text-xs">
                            <div className="flex justify-between">
                                <span className="text-on-surface-variant">Reward Koin Emas:</span>
                                <strong className="text-primary-container font-mono">+50 Emas</strong>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-on-surface-variant">Reward Perak:</span>
                                <strong className="text-on-surface font-mono">+100.000 Perak</strong>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-on-surface-variant">Status Agen:</span>
                                <strong className="text-secondary">Terbuka untuk Semua Saudagar</strong>
                            </div>
                        </div>

                        <button
                            onClick={() => {
                                setShowMissionModal(false);
                                showMsg('Formulir pendaftaran kemitraan toko telah dibuka.');
                            }}
                            className="w-full py-2.5 rounded-lg bg-primary-container text-on-primary font-bold text-xs uppercase tracking-wider hover:brightness-110 active:scale-95 transition-all shadow"
                        >
                            Mulai Daftarkan Toko Mitra
                        </button>
                    </div>
                </div>
            )}

            {/* ========================================================= */}
            {/* 12. MODAL: KONFIRMASI PENGAMBILAN MISI & STAKING PERAK     */}
            {/* ========================================================= */}
            {confirmingQuest && (
                <div className="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4">
                    <div className="glass-panel w-full max-w-md rounded-2xl overflow-hidden border border-primary-container/40 shadow-2xl p-5 space-y-4 animate-fade-in">
                        {/* Header */}
                        <div className="flex justify-between items-center border-b border-glass-stroke pb-3">
                            <div className="flex items-center gap-2">
                                <span className="material-symbols-outlined text-primary-container text-xl">explore</span>
                                <h3 className="font-bold text-sm text-primary-container">
                                    Konfirmasi Pengambilan Misi
                                </h3>
                            </div>
                            <button
                                onClick={() => setConfirmingQuest(null)}
                                disabled={isStaking}
                                className="text-on-surface-variant hover:text-white transition-colors"
                            >
                                <span className="material-symbols-outlined text-lg">close</span>
                            </button>
                        </div>

                        {/* Quest Details */}
                        <div className="space-y-1.5">
                            <h4 className="font-bold text-sm text-on-surface">{confirmingQuest.title}</h4>
                            <p className="text-xs text-on-surface-variant leading-relaxed">{confirmingQuest.description}</p>
                        </div>

                        {/* Parameter Box */}
                        <div className="p-3.5 rounded-xl border border-glass-stroke bg-surface/50 space-y-2 text-xs">
                            <div className="flex justify-between items-center">
                                <span className="text-on-surface-variant">Batas Waktu:</span>
                                <strong className="text-on-surface font-mono">{confirmingQuest.time_limit_hours} Jam</strong>
                            </div>
                            <div className="flex justify-between items-center">
                                <span className="text-on-surface-variant">Hadiah Emas:</span>
                                <strong className="text-primary-container font-mono">+{confirmingQuest.reward_amount_gold} Koin Emas</strong>
                            </div>
                            <div className="flex justify-between items-center pt-2 border-t border-glass-stroke">
                                <span className="text-on-surface-variant">Syarat Staking Komitmen:</span>
                                <strong className="text-amber-300 font-mono">
                                    {confirmingQuest.stake_amount_silver > 0
                                        ? `${formatCoin(confirmingQuest.stake_amount_silver)} Perak`
                                        : 'Tanpa Stake (0 Perak)'}
                                </strong>
                            </div>
                        </div>

                        {/* Wallet Balance & Validation */}
                        <div className={`p-3 rounded-xl border text-xs space-y-1 ${
                            (wallet?.silver_balance || 0) < confirmingQuest.stake_amount_silver
                                ? 'border-error/40 bg-error/10 text-error'
                                : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300'
                        }`}>
                            <div className="flex justify-between items-center font-bold">
                                <span>Saldo Koin Perak Anda:</span>
                                <span className="font-mono">{formatCoin(wallet?.silver_balance || 0)} Perak</span>
                            </div>
                            {(wallet?.silver_balance || 0) < confirmingQuest.stake_amount_silver ? (
                                <p className="text-[11px] leading-tight text-red-300">
                                    ⚠️ Saldo Koin Perak tidak cukup (Kurang {formatCoin(confirmingQuest.stake_amount_silver - (wallet?.silver_balance || 0))} Perak).
                                </p>
                            ) : (
                                <p className="text-[11px] leading-tight text-emerald-400/90">
                                    ✓ Saldo Koin Perak mencukupi. Koin perak akan dikunci di Escrow hingga misi selesai diverifikasi.
                                </p>
                            )}
                        </div>

                        {/* Action Buttons */}
                        <div className="flex items-center gap-2.5 pt-2">
                            <button
                                type="button"
                                onClick={() => setConfirmingQuest(null)}
                                disabled={isStaking}
                                className="flex-1 py-2.5 rounded-xl border border-glass-stroke bg-surface-container-high hover:bg-surface-variant text-on-surface text-xs font-semibold transition-colors"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                onClick={handleConfirmStakeQuest}
                                disabled={isStaking || (wallet?.silver_balance || 0) < confirmingQuest.stake_amount_silver}
                                className="flex-1 py-2.5 rounded-xl bg-primary-container hover:brightness-110 active:scale-95 text-on-primary text-xs font-bold transition-all shadow-[0_0_15px_rgba(255,215,0,0.25)] disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center gap-1.5"
                            >
                                {isStaking ? (
                                    <>
                                        <span className="w-3.5 h-3.5 border-2 border-on-primary border-t-transparent rounded-full animate-spin"></span>
                                        <span>Memproses...</span>
                                    </>
                                ) : (
                                    <span>Setuju & Ambil Misi</span>
                                )}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Global Legal Compliance Footer */}
            <footer className="mt-12 mb-6 pt-6 border-t border-glass-stroke text-center text-xs text-on-surface-variant/70 space-y-2 px-4">
                <div className="flex justify-center items-center gap-3.5 flex-wrap font-mono text-[11px]">
                    <Link href={route('legal.terms')} className="hover:text-primary-container transition-colors">
                        Syarat & Ketentuan
                    </Link>
                    <span>•</span>
                    <Link href={route('legal.privacy')} className="hover:text-emerald-400 transition-colors">
                        Kebijakan Privasi (UU PDP)
                    </Link>
                    <span>•</span>
                    <Link href={route('help.index')} className="hover:text-blue-400 transition-colors">
                        Pusat Bantuan & FAQ
                    </Link>
                    <span>•</span>
                    <Link href={route('baitul-mal.index')} className="hover:text-amber-400 transition-colors">
                        Baitul Mal Sosial
                    </Link>
                </div>
                <p className="text-[10px] text-on-surface-variant/50">
                    © 2026 PitiCoin.id — Platform Ekosistem Gamifikasi & Gateway Bisnis Gunung Mas.
                </p>
            </footer>

            <CookieConsentBanner />
            <WebPushPermissionModal />
            <NotificationDrawer
                isOpen={showNotificationDrawer}
                onClose={() => setShowNotificationDrawer(false)}
            />

            {/* Custom Laser Scan Animation Style */}
            <style>{`
                @keyframes scanLaserAnim {
                    0% { top: 10%; opacity: 0.2; }
                    50% { top: 85%; opacity: 1; }
                    100% { top: 10%; opacity: 0.2; }
                }
                .animate-scan-laser {
                    animation: scanLaserAnim 2s ease-in-out infinite;
                }
            `}</style>
        </div>
    );
}
