import { useState, useEffect } from 'react';
import { Head, router, Link } from '@inertiajs/react';

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
}: ConquerorProps) {
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState<{ text: string; type: 'success' | 'error' } | null>(null);
    const [now, setNow] = useState(new Date());
    const [isMounted, setIsMounted] = useState(false);
    const [isScrolled, setIsScrolled] = useState(false);
    const [activeTab, setActiveTab] = useState<'QUESTS' | 'NETWORK' | 'MARKET'>('QUESTS');

    // Live state
    const [wallet, setWallet] = useState(initialWallet);
    const [currentUser, setCurrentUser] = useState(user);
    const [checkinStatus, setCheckinStatus] = useState(initialCheckinStatus);
    const [animatedFiat, setAnimatedFiat] = useState<number>(
        Number(global_pool?.total_fiat_in_bank || 500000) - Number(global_pool?.total_gold_in_circulation || 0)
    );

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

    // Stake Quest Handler
    const handleStakeQuest = (questId: string) => {
        setLoading(true);
        router.post(route('quests.stake'), { quest_id: questId }, {
            onFinish: () => setLoading(false),
            onSuccess: () => showMsg('Misi berhasil di-stake! Komitmen Anda terkunci di escrow.'),
            onError: (errors: any) => showMsg(errors.error || 'Gagal melakukan staking', 'error')
        });
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
                            <div className="flex items-center gap-1.5">
                                <span className="font-label-sm text-[9px] md:text-[10px] text-on-surface-variant uppercase tracking-wider truncate">
                                    {currentUser.role} • {currentUser.persona}
                                </span>
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
                <button
                    onClick={handleLogout}
                    title="Keluar Akun"
                    className="text-on-surface-variant hover:text-primary-fixed-dim transition-colors flex items-center justify-center p-2 rounded-full hover:bg-white/5 active:scale-95 duration-200"
                >
                    <span className="material-symbols-outlined text-xl" style={{ fontVariationSettings: "'FILL' 1" }}>logout</span>
                </button>
                <h1
                    className="font-headline-lg-mobile text-headline-lg-mobile md:font-headline-md md:text-headline-md font-bold text-primary-container tracking-widest text-center flex-1"
                    style={{ fontFamily: "'Playfair Display', serif" }}
                >
                    PITICOIN.ID
                </h1>
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
                {/* 3. HERO: THE LEDGER POOL (LAMPIRAN 1)                     */}
                {/* Tetap berada di header atas dashboard                     */}
                {/* ========================================================= */}
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
                            Omset Pasukan Anda: +{formatCoin(passive_income)} Koin
                        </p>
                    </div>

                    {/* Prominent Upgrade Button if not maximum tier */}
                    {currentUser.license_type !== 'SAUDAGAR' && (
                        <button
                            onClick={() => setShowUpgradeModal(true)}
                            className="mt-3 inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-gradient-to-r from-amber-500/20 via-primary-container/30 to-amber-500/20 border border-primary-container text-primary-container text-xs font-bold uppercase tracking-wider hover:scale-105 active:scale-95 transition-transform shadow-[0_0_15px_rgba(255,215,0,0.3)] animate-pulse"
                        >
                            <span className="material-symbols-outlined text-sm">stars</span>
                            <span>Upgrade Lisensi Saudagar</span>
                        </button>
                    )}
                </section>

                {/* Persona Switcher Removed: Users do not change personas arbitrarily */}

                {/* ========================================================= */}
                {/* 5. MANDATORY DAILY CHECK-IN: KOMPAS PETUALANGAN           */}
                {/* Gatekeeper untuk membuka misi & retensi harian             */}
                {/* ========================================================= */}
                <section className="glass-panel rounded-2xl p-4 border border-primary-container/30 relative overflow-hidden bg-gradient-to-br from-surface-container-high/40 via-surface/60 to-surface-container-lowest/90">
                    <div className="flex items-center justify-between border-b border-glass-stroke pb-3 mb-3">
                        <div className="flex items-center gap-2.5">
                            <div className={`w-9 h-9 rounded-xl flex items-center justify-center ${checkinStatus.has_checked_in ? 'bg-secondary/20 text-secondary border border-secondary/30' : 'bg-primary-container/20 text-primary-container border border-primary-container/30'}`}>
                                <span className="material-symbols-outlined text-lg" style={{ fontVariationSettings: "'FILL' 1" }}>explore</span>
                            </div>
                            <div>
                                <h3 className="font-bold text-sm text-on-surface flex items-center gap-1.5">
                                    Kompas Harian & Absensi
                                    {checkinStatus.has_checked_in && (
                                        <span className="font-mono text-[10px] text-secondary bg-secondary/10 px-2 py-0.5 rounded border border-secondary/20">
                                            AKTIF
                                        </span>
                                    )}
                                </h3>
                                <p className="text-[11px] text-on-surface-variant">
                                    {time_details?.label || 'Horizon Niaga'} • {time_details?.time_range || '05:00 - 23:59 WIB'}
                                </p>
                            </div>
                        </div>

                        {/* Streak Counter Pill */}
                        <div className="text-right">
                            <span className="text-[10px] text-on-surface-variant block uppercase">Streak</span>
                            <span className="font-mono text-sm font-bold text-primary-container flex items-center justify-end gap-1">
                                🔥 {checkinStatus.streak_count} Hari
                            </span>
                        </div>
                    </div>

                    {/* Time Window Buff Banner */}
                    <div className="bg-primary-container/10 border border-primary-container/25 rounded-xl p-2.5 flex items-center justify-between gap-2 mb-3">
                        <div className="flex items-center gap-2 min-w-0">
                            <span className="material-symbols-outlined text-primary-container text-base">{time_details?.icon || 'wb_sunny'}</span>
                            <span className="text-[11px] text-on-surface-variant truncate">
                                {time_details?.buff || 'Buff Waktu Aktif'}
                            </span>
                        </div>
                        <span className="font-mono text-[10px] font-bold text-primary-container bg-primary-container/20 px-2 py-0.5 rounded whitespace-nowrap">
                            {time_details?.buff_badge || '+10% XP'}
                        </span>
                    </div>

                    {/* Action Button: Check-in / Status */}
                    {!checkinStatus.has_checked_in ? (
                        <button
                            onClick={handleDailyCheckin}
                            disabled={loading}
                            className="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-500 via-primary-container to-amber-400 text-on-primary font-bold text-xs md:text-sm tracking-wider uppercase hover:brightness-110 active:scale-95 transition-all shadow-[0_0_20px_rgba(255,215,0,0.5)] flex items-center justify-center gap-2"
                        >
                            <span className="material-symbols-outlined text-lg">touch_app</span>
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
                                <strong className="text-primary-container font-mono text-xs">+1 Koin Emas</strong>
                            </div>
                        </div>
                    )}
                </section>

                {/* ========================================================= */}
                {/* 6. TAB SELECTOR NAVIGATION (MOBILE-FIRST ERGONOMICS)      */}
                {/* ========================================================= */}
                <div className="grid grid-cols-4 gap-1.5 bg-surface-container-lowest/80 p-1.5 rounded-xl border border-glass-stroke">
                    {[
                        { id: 'QUESTS', label: 'Misi', icon: 'assignment' },
                        { id: 'NETWORK', label: 'Jaringan', icon: 'diversity_3' },
                        { id: 'MARKET', label: 'Gateway & Pasar', icon: 'storefront' },
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
                        {escrows.filter(e => e.status === 'LOCKED').length > 0 && (
                            <div className="space-y-3">
                                <h3 className="font-bold text-xs uppercase tracking-wider text-primary-container flex items-center gap-1.5">
                                    <span className="w-2 h-2 rounded-full bg-primary-container animate-pulse"></span>
                                    Ekspedisi Berjalan (Escrow Terkunci)
                                </h3>
                                {escrows.filter(e => e.status === 'LOCKED').map((escrow) => (
                                    <div key={escrow.id} className="glass-panel rounded-xl p-4 border border-primary-container/40 space-y-2">
                                        <div className="flex justify-between items-start">
                                            <h4 className="font-bold text-sm text-on-surface">{escrow.quest.title}</h4>
                                            <span className="font-mono text-xs text-error font-bold">
                                                {isMounted ? getRemainingTime(escrow.expires_at).label : '--:--:--'}
                                            </span>
                                        </div>
                                        <div className="flex justify-between text-xs text-on-surface-variant pt-1 border-t border-glass-stroke">
                                            <span>Staking: <strong>{formatCoin(escrow.staked_silver)} Perak</strong></span>
                                            <span className="text-primary-container font-bold">+{formatCoin(escrow.quest.reward_amount_gold)} Emas</span>
                                        </div>
                                    </div>
                                ))}
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
                                        className={`glass-panel rounded-xl p-3.5 border border-glass-stroke flex items-center justify-between gap-3 ${
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
                                            onClick={() => handleStakeQuest(quest.id)}
                                            disabled={loading || !checkinStatus.has_checked_in}
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
                                        className={`glass-panel rounded-xl p-3.5 border border-primary-container/30 flex items-center justify-between gap-3 ${
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
                                            onClick={() => handleStakeQuest(quest.id)}
                                            disabled={loading || !checkinStatus.has_checked_in}
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
                                        className={`glass-panel rounded-xl p-3.5 border border-glass-stroke flex items-center justify-between gap-3 ${
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
                                            onClick={() => handleStakeQuest(quest.id)}
                                            disabled={loading || !checkinStatus.has_checked_in}
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
                {/* TAB CONTENT 4: PASAR, GAME & LAYANAN BERLANGGANAN         */}
                {/* ========================================================= */}
                {activeTab === 'MARKET' && (
                    <div className="space-y-4 animate-fade-in">
                        {/* Golden Vault Banner */}
                        <div className="glass-panel rounded-2xl overflow-hidden border border-primary-container/40 relative min-h-[160px] p-5 flex flex-col justify-end">
                            <div className="absolute inset-0 bg-cover bg-center opacity-40 mix-blend-luminosity" style={{ backgroundImage: "url('https://lh3.googleusercontent.com/aida-public/AB6AXuBomh5nZA8r1Hn37i86UlZRJTJTTqOgkVNgNClIrvnNsVm4FLdwG9A0BDaTt6JAO5HC40cuBByuI0cZIKQlibhdE-wSEcUYxFpIVpnIE5oluyoS6p2D2oVA-bhdf7xwLMViQqzNhFLLWoMm1QYKM9ewl_zkzE9_BOmajzRCLR0LcohF_qm9Y0-9T2CHRM1TDxtTVc4bsCxF6IjL5tC0gVPDIoJPeWDOiCMaDwJIR03J4opa5EOpKgk')" }}></div>
                            <div className="absolute inset-0 bg-gradient-to-t from-obsidian-surface via-obsidian-surface/60 to-transparent"></div>
                            <div className="relative z-10 space-y-1">
                                <span className="font-label-sm text-[10px] text-primary-container font-bold tracking-widest uppercase bg-primary-container/20 px-2 py-0.5 rounded border border-primary-container/30">MARKAS BESAR</span>
                                <h3 className="font-headline-md text-lg font-bold text-on-surface" style={{ fontFamily: "'Playfair Display', serif" }}>The Golden Vault & Ekosistem</h3>
                                <p className="text-xs text-on-surface-variant">Pusat bursa lelang P2P, lebur koin, dan layanan berlangganan.</p>
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

                        {/* Subscription Services Catalog Preview */}
                        <div className="glass-panel rounded-xl p-4 border border-glass-stroke space-y-3">
                            <div className="flex justify-between items-center">
                                <h4 className="font-bold text-xs text-on-surface uppercase tracking-wider">Layanan Ekosistem Berlangganan</h4>
                                <span className="text-[10px] text-primary-container font-bold">Diskon 15% via Koin Emas</span>
                            </div>

                            <div className="space-y-2 text-xs">
                                <div className="p-3 rounded-lg bg-surface/50 border border-glass-stroke flex justify-between items-center">
                                    <div>
                                        <strong className="block text-on-surface">Unimart POS Juragan Pro</strong>
                                        <span className="text-[10px] text-on-surface-variant">Kasir Cloud, Stok, QRIS Dinamis & Akuntansi</span>
                                    </div>
                                    <button onClick={() => setShowUpgradeModal(true)} className="px-3 py-1 bg-primary-container text-on-primary font-bold text-[10px] rounded-lg">
                                        Langgan
                                    </button>
                                </div>

                                <div className="p-3 rounded-lg bg-surface/50 border border-glass-stroke flex justify-between items-center">
                                    <div>
                                        <strong className="block text-on-surface">WaliKelas.id LMS & Tutor</strong>
                                        <span className="text-[10px] text-on-surface-variant">Booking Jadwal Les & Bank Soal Digital</span>
                                    </div>
                                    <button onClick={() => setShowUpgradeModal(true)} className="px-3 py-1 bg-surface-variant text-on-surface font-bold text-[10px] rounded-lg">
                                        Langgan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </main>

            {/* ========================================================= */}
            {/* 7. BOTTOM NAVIGATION BAR (FIXED DOCK)                      */}
            {/* ========================================================= */}
            <nav className="bg-obsidian-surface/90 backdrop-blur-xl text-primary-container font-label-sm text-[10px] uppercase tracking-widest fixed bottom-0 left-0 w-full z-40 flex justify-around items-center px-4 pb-5 pt-2 rounded-t-2xl border-t border-glass-stroke shadow-[0_-8px_30px_rgba(0,0,0,0.8)]">
                <button
                    onClick={() => setActiveTab('QUESTS')}
                    className={`flex flex-col items-center justify-center transition-all ${
                        activeTab === 'QUESTS' ? 'text-primary-container scale-105 font-bold' : 'text-on-surface-variant/70 hover:text-on-surface'
                    }`}
                >
                    <span className="material-symbols-outlined mb-0.5 text-xl" style={{ fontVariationSettings: "'FILL' 1" }}>explore</span>
                    <span>Misi</span>
                </button>

                <button
                    onClick={() => setActiveTab('NETWORK')}
                    className={`flex flex-col items-center justify-center transition-all ${
                        activeTab === 'NETWORK' ? 'text-primary-container scale-105 font-bold' : 'text-on-surface-variant/70 hover:text-on-surface'
                    }`}
                >
                    <span className="material-symbols-outlined mb-0.5 text-xl" style={{ fontVariationSettings: "'FILL' 1" }}>diversity_3</span>
                    <span>Jaringan</span>
                </button>

                <button
                    onClick={() => setActiveTab('MARKET')}
                    className={`flex flex-col items-center justify-center transition-all ${
                        activeTab === 'MARKET' ? 'text-primary-container scale-105 font-bold' : 'text-on-surface-variant/70 hover:text-on-surface'
                    }`}
                >
                    <span className="material-symbols-outlined mb-0.5 text-xl" style={{ fontVariationSettings: "'FILL' 1" }}>storefront</span>
                    <span>Layanan</span>
                </button>

                <button
                    onClick={() => setActiveTab('MARKET')}
                    className={`flex flex-col items-center justify-center transition-all ${
                        activeTab === 'MARKET' ? 'text-primary-container scale-105 font-bold' : 'text-on-surface-variant/70 hover:text-on-surface'
                    }`}
                >
                    <span className="material-symbols-outlined mb-0.5 text-xl" style={{ fontVariationSettings: "'FILL' 1" }}>storefront</span>
                    <span>Pasar</span>
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
        </div>
    );
}
