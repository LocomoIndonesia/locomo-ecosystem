import { useState, useEffect } from 'react';
import { Head, router, Link } from '@inertiajs/react';

interface ConquerorProps {
    user: {
        id: string;
        name: string;
        whatsapp: string;
        role: string;
        faction: string;
        role_class: string;
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
    wallet,
    quests,
    escrows,
    global_pool,
    downlines,
    passive_income,
}: ConquerorProps) {
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState<{ text: string; type: 'success' | 'error' } | null>(null);
    const [now, setNow] = useState(new Date());
    const [isMounted, setIsMounted] = useState(false);
    const [animatedFiat, setAnimatedFiat] = useState<number>(
        Number(global_pool?.total_fiat_in_bank || 500000) - Number(global_pool?.total_gold_in_circulation || 0)
    );

    // Agent Mission states
    const [showMissionModal, setShowMissionModal] = useState(false);
    const [showSurveyModal, setShowSurveyModal] = useState(false);
    const [agentMission, setAgentMission] = useState<any>(null);
    const [surveyData, setSurveyData] = useState({
        business_name: '',
        owner_name: '',
        owner_email: '',
        owner_phone: '',
        business_type: 'FnB/Café',
    });

    useEffect(() => {
        // Fetch active agent mission
        fetch('/api/agent-missions/active')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.mission) {
                    setAgentMission(data.mission);
                }
            })
            .catch(err => console.error("Error fetching agent mission:", err));
    }, []);

    useEffect(() => {
        setIsMounted(true);
        const interval = setInterval(() => setNow(new Date()), 1000);
        return () => clearInterval(interval);
    }, []);

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

    const handleStakeQuest = (questId: string) => {
        setLoading(true);
        router.post(route('quests.stake'), { quest_id: questId }, {
            onFinish: () => setLoading(false),
            onSuccess: () => showMsg('Quest berhasil distake! Ekspedisi dimulai.'),
            onError: (errors: any) => showMsg(errors.error || 'Gagal melakukan stake', 'error')
        });
    };

    const handleResolveQuest = async (escrowId: string, isSuccess: boolean) => {
        setLoading(true);
        try {
            const res = await fetch(route('quests.resolve'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'x-admin-key': 'piticoin_admin_secret',
                },
                body: JSON.stringify({ escrow_id: escrowId, is_success: isSuccess }),
            });
            const data = await res.json();
            if (data.success) {
                showMsg(`Ekspedisi diselesaikan: ${isSuccess ? 'SUKSES' : 'GAGAL'}`);
                router.reload();
            } else {
                showMsg(data.error || 'Resolusi gagal', 'error');
            }
        } catch (err) {
            showMsg('Gagal menyinkronkan resolusi quest', 'error');
        } finally {
            setLoading(false);
        }
    };

    const getRemainingTime = (expiresAtStr: string) => {
        const expiresAt = new Date(expiresAtStr);
        const diffMs = expiresAt.getTime() - now.getTime();
        if (diffMs <= 0) return { label: 'Kedaluwarsa', expired: true };

        const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
        const diffMins = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
        const diffSecs = Math.floor((diffMs % (1000 * 60)) / 1000);
        
        const pad = (n: number) => n.toString().padStart(2, '0');
        return {
            label: `${pad(diffHours)}:${pad(diffMins)}:${pad(diffSecs)}`,
            expired: false,
        };
    };

    const getMissionRemainingTime = (expiresAtStr: string) => {
        if (!expiresAtStr) return { label: '00:00:00', expired: true };
        const expiresAt = new Date(expiresAtStr);
        const diffMs = expiresAt.getTime() - now.getTime();
        if (diffMs <= 0) return { label: 'Kedaluwarsa', expired: true };

        const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
        const diffMins = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
        const diffSecs = Math.floor((diffMs % (1000 * 60)) / 1000);
        
        const pad = (n: number) => n.toString().padStart(2, '0');
        return {
            label: `${pad(diffHours)}:${pad(diffMins)}:${pad(diffSecs)}`,
            expired: false,
        };
    };

    const handleTakeAgentMission = async () => {
        setLoading(true);
        try {
            const res = await fetch('/api/agent-missions/take', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
                }
            });
            const data = await res.json();
            if (data.success) {
                setAgentMission(data.mission);
                showMsg(data.message);
                router.reload();
            } else {
                showMsg(data.error || 'Gagal mengambil misi.', 'error');
            }
        } catch (err) {
            showMsg('Koneksi terputus dengan server.', 'error');
        } finally {
            setLoading(false);
        }
    };

    const handleSubmitSurvey = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        try {
            const res = await fetch('/api/agent-missions/submit-lead', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
                },
                body: JSON.stringify(surveyData)
            });
            const data = await res.json();
            if (data.success) {
                setAgentMission(data.mission);
                setShowSurveyModal(false);
                showMsg(data.message);
                router.reload();
            } else {
                showMsg(data.error || 'Gagal mengirim pendaftaran.', 'error');
            }
        } catch (err) {
            showMsg('Server offline atau tidak dapat dihubungi.', 'error');
        } finally {
            setLoading(false);
        }
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
    }

    const xpThreshold = user.level * 1000;
    const xpPercentage = Math.min((user.experience_points / xpThreshold) * 100, 100);

    const getFactionColorClass = (fac: string) => {
        switch (fac) {
            case 'NISNAS': return 'text-faction-nisnas';
            case 'HUMAN': return 'text-faction-human';
            case 'ANDROID': return 'text-faction-android';
            default: return 'text-primary-container';
        }
    };

    const factionColorClass = getFactionColorClass(user.faction);

    const totalDownlines = downlines.level_1.length + downlines.level_2.length;
    const transferUnlocked = totalDownlines >= 5;

    return (
        <div className="bg-background text-on-background min-h-screen relative overflow-x-hidden font-body-md text-body-md pb-[120px]">
            <Head title="The Conqueror - Dashboard" />

            {/* Shared Component: TopAppBar */}
            <header className="bg-transparent text-primary-container w-full px-margin-mobile pt-stack-md h-16 z-50 flex justify-between items-center transition-colors">
                <button onClick={handleLogout} className="text-on-surface-variant hover:text-primary-fixed-dim transition-colors flex items-center justify-center p-2 rounded-full hover:bg-white/5 active:scale-95 duration-200">
                    <span className="material-symbols-outlined" style={{ fontVariationSettings: "'FILL' 1" }}>logout</span>
                </button>
                <h1 className="font-headline-lg-mobile text-headline-lg-mobile md:font-headline-md md:text-headline-md font-bold text-primary-container tracking-widest text-center flex-1" style={{ fontFamily: "'Playfair Display', serif" }}>
                    PITICOIN.ID
                </h1>
                <button className="text-on-surface-variant hover:text-primary-fixed-dim transition-colors w-10 h-10 rounded-full overflow-hidden border-2 border-glass-stroke active:scale-95 duration-200">
                    <img alt="Explorer Rank Icon" className="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDtnNnM6GYAk06weL4J1x3GacDiwegweSKP84SJLeHNza0vvwlFwOUmt95Zp-S_X6zG6qkVVm-b90g6cINFPfuTa-nBO-3nCvwowsn452NwPmp6pNvXqVPhxwzPz2t4xwLoiP_EH1vm9bu1Q57t2jjgzGpaiXRk_NOuzg4GfBJ4rpt0dZrV5lN1gNXxvmoF9bCGXy8lMrSt6FfvwopiDszDU135FTdSvBO4XcYORkyfoUAviuDfWCA"/>
                </button>
            </header>

            <main className="relative z-10 w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop flex flex-col gap-stack-lg">
                
                {message && (
                    <div className={`mt-4 rounded-xl p-3 border text-sm font-bold tracking-wide ${message.type === 'error' ? 'bg-error-container/20 border-error/50 text-error' : 'bg-secondary-container/20 border-secondary/50 text-secondary'}`}>
                        {message.text}
                    </div>
                )}

                {/* Header (The Ledger Pool) */}
                <section className="flex flex-col items-center justify-center text-center mt-stack-md">
                    <p className="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest mb-2 flex items-center gap-2">
                        <span className="w-2 h-2 rounded-full bg-primary-container animate-pulse"></span>
                        The Ledger Pool
                    </p>
                    <h2 className="font-display-hero text-[40px] md:text-display-hero text-primary-fixed-dim drop-shadow-[0_0_15px_rgba(233,196,0,0.4)] transition-all duration-300" style={{ fontFamily: "'Playfair Display', serif" }}>
                        {formatRupiah(animatedFiat)}
                    </h2>
                    <div className="mt-4 glass-panel rounded-full px-6 py-2 inline-flex items-center gap-2 border-t-primary-container/30">
                        <span className="material-symbols-outlined text-secondary-fixed-dim text-sm" style={{ fontVariationSettings: "'FILL' 1" }}>trending_up</span>
                        <p className="font-label-sm text-label-sm text-secondary-fixed-dim">
                            Omset Pasukan Anda: +{formatCoin(passive_income)} Koin
                        </p>
                    </div>
                </section>

                {/* Core Object: The Golden Mountain & Menus (Bento Grid) */}
                <section className="grid grid-cols-2 md:grid-cols-4 gap-4 mt-stack-sm">
                    {/* Centerpiece: Golden Mountain */}
                    <div className="col-span-2 md:col-span-2 row-span-2 glass-panel rounded-xl overflow-hidden relative group border-t-[1px] border-t-primary-container/40 bloom-gold transition-all duration-500 hover:border-primary-container min-h-[220px]">
                        <div className="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105 opacity-80 mix-blend-luminosity group-hover:mix-blend-normal" style={{ backgroundImage: "url('https://lh3.googleusercontent.com/aida-public/AB6AXuBomh5nZA8r1Hn37i86UlZRJTJTTqOgkVNgNClIrvnNsVm4FLdwG9A0BDaTt6JAO5HC40cuBByuI0cZIKQlibhdE-wSEcUYxFpIVpnIE5oluyoS6p2D2oVA-bhdf7xwLMViQqzNhFLLWoMm1QYKM9ewl_zkzE9_BOmajzRCLR0LcohF_qm9Y0-9T2CHRM1TDxtTVc4bsCxF6IjL5tC0gVPDIoJPeWDOiCMaDwJIR03J4opa5EOpKgk')" }}></div>
                        <div className="absolute inset-0 bg-gradient-to-t from-obsidian-surface via-transparent to-transparent"></div>
                        <div className="absolute bottom-0 left-0 w-full p-6 flex flex-col items-start z-10">
                            <div className="bg-primary-container/20 backdrop-blur-md rounded-full px-3 py-1 border border-primary-container/30 mb-2">
                                <span className="font-label-sm text-[10px] text-primary-container font-bold tracking-widest">MARKAS BESAR</span>
                            </div>
                            <h3 className="font-headline-md text-headline-md text-on-background font-bold mb-1 group-hover:text-primary-container transition-colors" style={{ fontFamily: "'Playfair Display', serif" }}>The Golden Vault</h3>
                            <p className="font-body-md text-body-md text-on-surface-variant/80 text-sm">Pusat komando kekayaan Anda.</p>
                        </div>
                        <div className="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite] pointer-events-none"></div>
                    </div>
                    
                    {/* Menu 1 */}
                    <Link href="/auction" className="glass-panel rounded-xl p-4 flex flex-col items-center justify-center gap-3 relative hover:border-primary-container/50 hover:bg-surface-container-high/50 transition-all active:scale-95 group">
                        <div className="w-12 h-12 rounded-full neumorphic-inset flex items-center justify-center group-hover:shadow-[inset_0_0_10px_rgba(255,215,0,0.2)] transition-shadow">
                            <span className="material-symbols-outlined text-on-surface-variant group-hover:text-primary-container" style={{ fontVariationSettings: "'FILL' 1" }}>storefront</span>
                        </div>
                        <span className="font-label-sm text-label-sm text-on-surface text-center">Bursa P2P</span>
                    </Link>
                    
                    {/* Menu 2 */}
                    <Link href="/digital-goods" className="glass-panel rounded-xl p-4 flex flex-col items-center justify-center gap-3 hover:border-primary-container/50 hover:bg-surface-container-high/50 transition-all active:scale-95 group">
                        <div className="absolute -top-2 -right-2 bg-primary-container text-on-primary-container font-label-sm text-[10px] w-5 h-5 flex items-center justify-center rounded-full border border-surface shadow-[0_0_10px_rgba(255,215,0,0.5)] z-10">
                            !
                        </div>
                        <div className="w-12 h-12 rounded-full neumorphic-inset flex items-center justify-center group-hover:shadow-[inset_0_0_10px_rgba(255,215,0,0.2)] transition-shadow">
                            <span className="material-symbols-outlined text-on-surface-variant group-hover:text-primary-container" style={{ fontVariationSettings: "'FILL' 1" }}>phone_iphone</span>
                        </div>
                        <span className="font-label-sm text-label-sm text-on-surface text-center">Lebur Koin</span>
                    </Link>
                    
                    {/* Menu 3 */}
                    <a href="/games/harta-karun/index.html" target="_blank" className="glass-panel rounded-xl p-4 flex flex-col items-center justify-center gap-3 hover:border-primary-container/50 hover:bg-surface-container-high/50 transition-all active:scale-95 group">
                        <div className="w-12 h-12 rounded-full neumorphic-inset flex items-center justify-center group-hover:shadow-[inset_0_0_10px_rgba(255,215,0,0.2)] transition-shadow">
                            <span className="material-symbols-outlined text-on-surface-variant group-hover:text-primary-container" style={{ fontVariationSettings: "'FILL' 1" }}>casino</span>
                        </div>
                        <span className="font-label-sm text-label-sm text-on-surface text-center">Harta Karun</span>
                    </a>
                    
                    {/* Menu 4: Misi Agen */}
                    <button onClick={() => setShowMissionModal(true)} className="glass-panel rounded-xl p-4 flex flex-col items-center justify-center gap-3 hover:border-primary-container/50 hover:bg-surface-container-high/50 transition-all active:scale-95 group relative">
                        {agentMission && (agentMission.status === 'active' || agentMission.status === 'completed') && (
                            <div className="absolute -top-2 -right-2 bg-secondary text-on-secondary font-label-sm text-[10px] w-5 h-5 flex items-center justify-center rounded-full border border-surface shadow-[0_0_10px_rgba(102,221,139,0.5)] z-10 animate-pulse">
                                1
                            </div>
                        )}
                        <div className="w-12 h-12 rounded-full neumorphic-inset flex items-center justify-center group-hover:shadow-[inset_0_0_10px_rgba(255,215,0,0.2)] transition-shadow">
                            <span className="material-symbols-outlined text-on-surface-variant group-hover:text-primary-container" style={{ fontVariationSettings: "'FILL' 1" }}>assignment_ind</span>
                        </div>
                        <span className="font-label-sm text-label-sm text-on-surface text-center">Misi Agen</span>
                    </button>
                </section>

                {/* Campaign Board (Active Escrows) */}
                <section className="mt-stack-md flex flex-col gap-4">
                    <div className="flex justify-between items-end border-b border-glass-stroke pb-2">
                        <h3 className="font-headline-md text-headline-md text-on-surface flex items-center gap-2" style={{ fontFamily: "'Playfair Display', serif" }}>
                            <span className={`material-symbols-outlined ${factionColorClass}`} style={{ fontVariationSettings: "'FILL' 1" }}>explore</span>
                            Campaign Board
                        </h3>
                        <span className={`font-label-sm text-label-sm ${factionColorClass} border border-current/30 bg-current/10 px-2 py-1 rounded tracking-widest`}>AKTIF & MENDESAK</span>
                    </div>
                    
                    {escrows.filter(e => e.status === 'LOCKED').length === 0 ? (
                        <div className="text-center text-sm text-on-surface-variant py-8 border border-dashed border-glass-stroke rounded-xl">
                            Tidak ada ekspedisi aktif. Silakan pilih misi di bawah.
                        </div>
                    ) : (
                        escrows.filter((e) => e.status === 'LOCKED').map((escrow) => {
                            const timer = getRemainingTime(escrow.expires_at);
                            return (
                                <div key={escrow.id} className={`relative glass-panel rounded-xl overflow-hidden ${factionColorClass === 'text-faction-nisnas' ? 'border-faction-nisnas/50 bloom-emerald' : 'border-primary-container/50 bloom-gold'} p-1`}>
                                    <div className="absolute inset-0 bg-cover bg-center opacity-20" style={{ backgroundImage: "url('https://lh3.googleusercontent.com/aida-public/AB6AXuCIOydMCIt9iFaAtXxV3L1XRjCJpm6wYNae7NplRSqtjhwfxVQFycTahm1vcMZgqfliwLkYtWP69XnaoeKAoH6VW6JTm8OrNx-gFItdnAGgBaxIdbf09D8_6vd0GMAhWk1A29mTDDqHOWH-23zsNzGYj-cA-0TmTU1C4xbyVCNa9Ek_R05OtihKnVUM8fQV1OnekAFuMi45hR2yXUnMnS4gRUfez1LqvreSTfmezU2n4ZQ9yQXNW8Q')" }}></div>
                                    <div className="relative z-10 bg-surface-container-lowest/80 backdrop-blur-md rounded-lg p-5 flex flex-col gap-4">
                                        <div className="flex justify-between items-start">
                                            <div>
                                                <h4 className="font-headline-lg-mobile text-[22px] md:text-headline-lg text-on-background font-bold mb-1" style={{ fontFamily: "'Playfair Display', serif" }}>{escrow.quest.title}</h4>
                                                <p className="font-body-md text-body-md text-on-surface-variant flex items-center gap-1 text-sm">
                                                    <span className="material-symbols-outlined text-sm">location_on</span> Zona Sektor Ekspedisi
                                                </p>
                                            </div>
                                            <div className="text-right">
                                                <p className="font-label-sm text-label-sm text-on-surface-variant mb-1">Staked</p>
                                                <p className="font-label-sm text-label-sm text-primary-container bg-primary-container/10 px-3 py-1 rounded border border-primary-container/20 inline-block">{formatCoin(escrow.staked_silver)} Perak</p>
                                            </div>
                                        </div>
                                        
                                        <div className="bg-surface/50 rounded-lg p-3 border border-glass-stroke flex justify-between items-center">
                                            <span className="font-label-sm text-label-sm text-on-surface-variant">Sisa Waktu</span>
                                            <span className={`font-label-sm text-label-sm font-bold tracking-wider flex items-center gap-1 ${timer.expired ? 'text-error' : 'animate-blink-red'}`}>
                                                <span className="material-symbols-outlined text-sm">timer</span>
                                                {isMounted ? timer.label : '--:--:--'}
                                            </span>
                                        </div>

                                        <div className="flex gap-2 w-full mt-2">
                                            <button
                                                onClick={() => handleResolveQuest(escrow.id, true)}
                                                disabled={loading || timer.expired}
                                                className="flex-1 py-3 rounded-lg bg-primary-container text-on-primary font-label-sm text-label-sm font-bold tracking-widest bloom-gold-intense hover:bg-primary-fixed-dim hover:scale-[1.02] transition-all active:scale-95 disabled:opacity-50"
                                            >
                                                [LAPORKAN SUKSES]
                                            </button>
                                            <button
                                                onClick={() => handleResolveQuest(escrow.id, false)}
                                                disabled={loading}
                                                className="flex-1 py-3 rounded-lg bg-surface-variant text-error font-label-sm text-label-sm font-bold tracking-widest border border-error/30 hover:bg-error/20 hover:scale-[1.02] transition-all active:scale-95 disabled:opacity-50"
                                            >
                                                [GAGAL]
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            );
                        })
                    )}
                </section>

                {/* Available Quests */}
                <section className="mt-stack-md flex flex-col gap-4">
                    <div className="flex justify-between items-end border-b border-glass-stroke pb-2">
                        <h3 className="font-headline-md text-headline-md text-on-surface flex items-center gap-2" style={{ fontFamily: "'Playfair Display', serif" }}>
                            <span className="material-symbols-outlined text-on-surface-variant" style={{ fontVariationSettings: "'FILL' 1" }}>map</span>
                            Misi Harian Tersedia
                        </h3>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {quests.map((quest) => (
                            <div key={quest.id} className="glass-panel rounded-xl p-5 hover:border-primary-container/30 transition-colors flex flex-col justify-between">
                                <div>
                                    <h4 className="font-body-lg text-lg font-bold text-on-surface mb-2">{quest.title}</h4>
                                    <p className="font-body-md text-sm text-on-surface-variant mb-4">{quest.description}</p>
                                    
                                    <div className="flex justify-between items-center bg-surface-container-lowest/50 rounded-lg p-3 border border-glass-stroke mb-4">
                                        <div className="text-center">
                                            <span className="font-label-sm text-[10px] text-on-surface-variant block mb-1">STAKE</span>
                                            <span className="font-bold text-sm text-on-surface">{formatCoin(quest.stake_amount_silver)} Perak</span>
                                        </div>
                                        <div className="text-center">
                                            <span className="font-label-sm text-[10px] text-primary-fixed-dim block mb-1">REWARD</span>
                                            <span className="font-bold text-sm text-primary-container">+{formatCoin(quest.reward_amount_gold)} Emas</span>
                                        </div>
                                        <div className="text-center">
                                            <span className="font-label-sm text-[10px] text-on-surface-variant block mb-1">LIMIT</span>
                                            <span className="font-bold text-sm text-on-surface">{quest.time_limit_hours} Jam</span>
                                        </div>
                                    </div>
                                </div>
                                
                                {wallet && Number(wallet.silver_balance) < Number(quest.stake_amount_silver) ? (
                                    <button disabled className="w-full py-3 rounded-lg bg-surface-variant text-on-surface-variant font-label-sm text-sm font-bold tracking-widest cursor-not-allowed">
                                        SALDO PERAK KURANG
                                    </button>
                                ) : (
                                    <button onClick={() => handleStakeQuest(quest.id)} disabled={loading} className="w-full py-3 rounded-lg bg-primary-container text-on-primary font-label-sm text-sm font-bold tracking-widest hover:bg-primary-fixed-dim hover:scale-[1.02] transition-all active:scale-95">
                                        AMBIL MISI INI
                                    </button>
                                )}
                            </div>
                        ))}
                    </div>
                </section>

                {/* Processing Deep (Mini-games / Features) */}
                <section className="mt-stack-sm grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="glass-panel rounded-xl p-5 border-l-4 border-l-faction-human">
                        <div className="flex items-center gap-3 mb-4">
                            <div className="w-10 h-10 rounded-full bg-faction-human/20 flex items-center justify-center text-faction-human border border-faction-human/30">
                                <span className="material-symbols-outlined" style={{ fontVariationSettings: "'FILL' 1" }}>hub</span>
                            </div>
                            <div>
                                <h4 className="font-body-lg text-body-lg font-bold text-on-surface">Jaringan Afiliasi</h4>
                                <p className="font-label-sm text-[10px] text-on-surface-variant">Sistem stabil. Total Downline: {totalDownlines}</p>
                            </div>
                        </div>
                        <div className="w-full h-2 bg-surface-container-highest rounded-full overflow-hidden">
                            <div className="h-full bg-faction-human relative" style={{ width: '100%' }}>
                                <div className="absolute inset-0 bg-white/20 animate-[shimmer_2s_infinite]"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div className={`glass-panel rounded-xl p-5 border-l-4 ${transferUnlocked ? 'border-l-secondary opacity-100' : 'border-l-surface-variant opacity-80'}`}>
                        <div className="flex items-center gap-3 mb-4">
                            <div className={`w-10 h-10 rounded-full flex items-center justify-center border ${transferUnlocked ? 'bg-secondary/20 text-secondary border-secondary/30' : 'bg-surface-variant/50 text-on-surface-variant border-glass-stroke'}`}>
                                <span className="material-symbols-outlined" style={{ fontVariationSettings: transferUnlocked ? "'FILL' 1" : "'FILL' 0" }}>{transferUnlocked ? 'check_circle' : 'lock'}</span>
                            </div>
                            <div className="flex-1">
                                <h4 className="font-body-lg text-body-lg font-bold text-on-surface-variant flex items-center gap-2">
                                    Fitur Transfer 
                                    {!transferUnlocked && <span className="font-label-sm text-[10px] text-error bg-error/10 px-2 py-0.5 rounded border border-error/20">Terkunci</span>}
                                    {transferUnlocked && <span className="font-label-sm text-[10px] text-secondary bg-secondary/10 px-2 py-0.5 rounded border border-secondary/20">Terbuka</span>}
                                </h4>
                                {!transferUnlocked && <p className="font-label-sm text-[10px] text-on-surface-variant/70 mt-1">Butuh {Math.max(0, 5 - totalDownlines)} Downline lagi</p>}
                            </div>
                        </div>
                        {/* Progress Bar */}
                        <div className="w-full flex flex-col gap-1">
                            <div className="flex justify-between font-label-sm text-[10px] text-on-surface-variant">
                                <span>Progress</span>
                                <span>{Math.min(totalDownlines, 5)}/5</span>
                            </div>
                            <div className="w-full h-2 bg-surface-container-highest rounded-full overflow-hidden flex gap-1 p-[1px]">
                                {[...Array(5)].map((_, i) => (
                                    <div key={i} className={`h-full flex-1 rounded-sm ${i < totalDownlines ? (i === totalDownlines - 1 ? 'bg-primary-fixed/50 shadow-[0_0_5px_rgba(255,225,109,0.5)]' : 'bg-primary-fixed/50') : 'bg-transparent border border-glass-stroke'}`}></div>
                                ))}
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            {/* Footer Status (User Info) - Sticky above nav */}
            <section className="fixed bottom-[72px] md:bottom-[88px] left-0 w-full z-40 px-margin-mobile md:px-margin-desktop pointer-events-none">
                <div className="max-w-container-max mx-auto pointer-events-auto">
                    <div className="glass-panel rounded-xl p-3 md:p-4 flex items-center justify-between border-t border-t-primary-container/20 shadow-[0_-10px_30px_rgba(0,0,0,0.5)] bg-obsidian-surface/90">
                        <div className="flex items-center gap-3 md:gap-4">
                            {/* Avatar with glowing frame */}
                            <div className="relative w-12 h-12 md:w-14 md:h-14 rounded-lg border-2 border-primary-container p-0.5 bloom-gold bg-surface">
                                <img alt="User Avatar" className="w-full h-full object-cover rounded-md grayscale-[20%]" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDi47Slkw4S4Y_KgVtZ0EISwFfTSIkSG4_2UXNIGz_TUpZPGsvQ9quu9zXxMlopsBUFeZOhn_yxJcMebklLuAHrdJXZOTxaanMZVSTn0-ghrNi4-HZtK5ZxRZPonkQg3bipaDJJjt0ghwB5GswyeN8IpGyxjzOyGGh3P6dz6mRoq-anKb_pf5frN99lP138Yfr7ElxVoaIt9Mds_TGIwKPmRA516YI1zc5MLi_Su07Z9ZLoMFO-KEQ"/>
                                <div className="absolute -bottom-2 -right-2 bg-obsidian-surface border border-primary-container text-primary-container font-label-sm text-[10px] w-6 h-6 flex items-center justify-center rounded-full shadow-[0_0_5px_rgba(255,215,0,0.5)] z-10">
                                    {user.level}
                                </div>
                            </div>
                            <div className="flex flex-col">
                                <h3 className="font-headline-md text-[16px] md:text-[20px] text-primary-container font-bold leading-tight" style={{ fontFamily: "'Playfair Display', serif" }}>{user.name}</h3>
                                <p className="font-label-sm text-[10px] md:text-xs text-on-surface-variant/80 uppercase tracking-widest">{user.role}</p>
                                {/* EXP Bar */}
                                <div className="mt-1 w-32 md:w-48 h-1.5 bg-surface-container-highest rounded-full overflow-hidden relative">
                                    <div className="h-full bg-secondary relative shadow-[0_0_5px_rgba(102,221,139,0.5)]" style={{ width: `${xpPercentage}%` }}>
                                        <div className="absolute inset-0 bg-gradient-to-r from-transparent to-white/30"></div>
                                    </div>
                                </div>
                                <p className="font-label-sm text-[8px] md:text-[10px] text-on-surface-variant mt-0.5 text-right w-32 md:w-48 tracking-widest">EXP: {user.experience_points}/{xpThreshold}</p>
                            </div>
                        </div>
                        {/* Balances */}
                        <div className="flex flex-col gap-2 items-end">
                            <div className="bg-surface-container-lowest/80 border border-glass-stroke rounded px-2 py-1 flex items-center gap-1 min-w-[100px] justify-end">
                                <span className="font-label-sm text-sm text-on-surface">{wallet ? formatCoin(wallet.silver_balance) : 0}</span>
                                <span className="material-symbols-outlined text-surface-variant text-[14px]" style={{ fontVariationSettings: "'FILL' 1" }}>circle</span>
                            </div>
                            <div className="bg-primary-container/10 border border-primary-container/30 rounded px-2 py-1 flex items-center gap-1 min-w-[100px] justify-end shadow-[inset_0_0_5px_rgba(255,215,0,0.1)]">
                                <span className="font-label-sm text-sm text-primary-fixed-dim font-bold">{wallet ? formatCoin(wallet.gold_balance) : 0}</span>
                                <span className="material-symbols-outlined text-primary-fixed text-[14px]" style={{ fontVariationSettings: "'FILL' 1" }}>monetization_on</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Shared Component: BottomNavBar */}
            <nav className="bg-obsidian-surface/60 backdrop-blur-xl text-primary-container font-label-sm text-[10px] uppercase tracking-widest fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-4 pb-6 pt-2 rounded-t-xl border-t border-glass-stroke shadow-[0_-4px_20px_rgba(255,215,0,0.1)]">
                <Link href="/dashboard" className="flex flex-col items-center justify-center text-primary-container scale-110 transition-transform hover:text-primary active:scale-95 duration-300 ease-out">
                    <span className="material-symbols-outlined mb-1" style={{ fontVariationSettings: "'FILL' 1" }}>explore</span>
                    <span>Quest</span>
                </Link>
                <Link href="/auction" className="flex flex-col items-center justify-center text-on-surface-variant/70 hover:text-primary active:scale-95 duration-300 ease-out transition-all">
                    <span className="material-symbols-outlined mb-1" style={{ fontVariationSettings: "'FILL' 0" }}>storefront</span>
                    <span>Bursa</span>
                </Link>
                <Link href="/digital-goods" className="flex flex-col items-center justify-center text-on-surface-variant/70 hover:text-primary active:scale-95 duration-300 ease-out transition-all">
                    <span className="material-symbols-outlined mb-1" style={{ fontVariationSettings: "'FILL' 0" }}>phone_iphone</span>
                    <span>Lebur</span>
                </Link>
                <div className="flex flex-col items-center justify-center text-on-surface-variant/70 hover:text-primary active:scale-95 duration-300 ease-out transition-all cursor-pointer">
                    <span className="material-symbols-outlined mb-1" style={{ fontVariationSettings: "'FILL' 0" }}>person</span>
                    <span>Profile</span>
                </div>
            </nav>

            {/* Modal: Misi Agen Unimart */}
            {showMissionModal && (
                <div className="fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4">
                    <div className="glass-panel w-full max-w-lg rounded-2xl overflow-hidden border border-glass-stroke shadow-2xl relative animate-fade-in flex flex-col max-h-[90vh]">
                        {/* Header */}
                        <div className="p-5 border-b border-glass-stroke flex justify-between items-center bg-obsidian-surface/60">
                            <h3 className="font-headline-md text-xl font-bold text-primary-container flex items-center gap-2" style={{ fontFamily: "'Playfair Display', serif" }}>
                                <span className="material-symbols-outlined text-primary-container">assignment_ind</span>
                                Misi Agen Unimart
                            </h3>
                            <button onClick={() => setShowMissionModal(false)} className="text-on-surface-variant hover:text-white transition-colors">
                                <span className="material-symbols-outlined">close</span>
                            </button>
                        </div>

                        {/* Content */}
                        <div className="p-6 overflow-y-auto space-y-5 flex-grow">
                            {/* License check */}
                            {user.license_type !== 'PETUALANG' && user.license_type !== 'SAUDAGAR' ? (
                                <div className="text-center py-6 space-y-4">
                                    <span className="material-symbols-outlined text-6xl text-error drop-shadow-[0_0_15px_rgba(235,87,87,0.4)] animate-pulse">lock</span>
                                    <h4 className="text-lg font-bold text-on-surface">Akses Misi Terkunci!</h4>
                                    <p className="text-sm text-on-surface-variant max-w-sm mx-auto leading-relaxed">
                                        Misi rujukan bisnis kasir Unimart hanya tersedia untuk Agen dengan tingkatan lisensi minimal 100 (**Petualang** atau **Saudagar**).
                                    </p>
                                    <p className="text-xs text-primary-container/80">Lisensi Anda saat ini: <strong className="underline">{user.license_type || 'NONE'}</strong></p>
                                </div>
                            ) : (
                                <>
                                    <div className="space-y-2">
                                        <h4 className="font-bold text-lg text-on-surface">Misi: Registrasi Bisnis Kasir Unimart</h4>
                                        <p className="text-sm text-on-surface-variant leading-relaxed">
                                            Daftarkan pemilik bisnis / usaha (Cafe, FnB, Grosiran, Hotel, atau Jasa) di sekitar Anda untuk mengelola keuangan dan kasir penjualan toko mereka menggunakan aplikasi Unimart.group.
                                        </p>
                                    </div>

                                    {/* Video / Marketing Material Mockup */}
                                    <div className="glass-panel rounded-xl overflow-hidden border border-glass-stroke relative group h-[180px] bg-black flex items-center justify-center">
                                        <div className="absolute inset-0 bg-cover bg-center opacity-60 group-hover:scale-105 transition-transform" style={{ backgroundImage: "url('https://lh3.googleusercontent.com/aida-public/AB6AXuDTxY0_r8Qx2aGfPjXwXw6a_hL7J4kR9eL2d9U6Yh9_lMcR8k')" }}></div>
                                        <div className="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent"></div>
                                        <span className="material-symbols-outlined text-5xl text-primary-container drop-shadow-[0_0_10px_rgba(255,215,0,0.6)] cursor-pointer z-10 hover:scale-110 transition-transform">play_circle</span>
                                        <div className="absolute bottom-3 left-3 z-10">
                                            <span className="font-label-sm text-[10px] text-white/90 bg-black/60 px-2 py-0.5 rounded border border-white/20">Video Promosi Kasir Unimart (2:15)</span>
                                        </div>
                                    </div>

                                    {/* Marketing Materials Link */}
                                    <div className="bg-surface-container-lowest/50 border border-glass-stroke rounded-lg p-3 flex justify-between items-center text-xs">
                                        <span className="text-on-surface-variant flex items-center gap-1">
                                            <span className="material-symbols-outlined text-sm">download</span>
                                            Brosur & Kit Marketing PDF (12 MB)
                                        </span>
                                        <a href="#" className="text-primary-container font-bold hover:underline">Unduh</a>
                                    </div>

                                    {/* Requirements / Economics */}
                                    <div className="grid grid-cols-2 gap-3 bg-surface-container-lowest/30 rounded-xl p-4 border border-glass-stroke text-sm">
                                        <div>
                                            <span className="text-[10px] text-on-surface-variant uppercase block mb-1">Jaminan Staking</span>
                                            <strong className="text-on-surface">2.500 Perak</strong>
                                        </div>
                                        <div>
                                            <span className="text-[10px] text-primary-fixed-dim uppercase block mb-1">Reward Komisi Agen</span>
                                            <strong className="text-primary-container">25% Koin Emas (Sub Bulanan)</strong>
                                        </div>
                                    </div>

                                    {/* Active Mission Details */}
                                    {agentMission && (
                                        <div className="border border-glass-stroke rounded-xl p-4 space-y-3 bg-surface-container-lowest/50">
                                            <div className="flex justify-between items-center text-xs border-b border-glass-stroke pb-2">
                                                <span className="text-on-surface-variant">Status Misi</span>
                                                <span className={`font-bold uppercase tracking-widest px-2 py-0.5 rounded ${
                                                    agentMission.status === 'active' ? 'text-primary-container bg-primary-container/10 border border-primary-container/20 animate-pulse' :
                                                    agentMission.status === 'completed' ? 'text-secondary bg-secondary/10 border border-secondary/20' :
                                                    'text-primary-fixed bg-primary-fixed/20 border border-primary-fixed/30'
                                                }`}>
                                                    {agentMission.status === 'active' ? 'Aktif' :
                                                     agentMission.status === 'completed' ? 'Misi Sukses' : 'Reward Cair'}
                                                </span>
                                            </div>

                                            {agentMission.status === 'active' && (
                                                <div className="flex justify-between items-center text-sm">
                                                    <span className="text-on-surface-variant">Batas Waktu Misi</span>
                                                    <strong className="text-error tracking-wider animate-blink-red">
                                                        {isMounted ? getMissionRemainingTime(agentMission.expires_at).label : '--:--:--'}
                                                    </strong>
                                                </div>
                                            )}

                                            <div className="text-xs text-on-surface-variant space-y-1">
                                                <p>• Staking Perak Terkunci: <strong>2.500 Perak</strong></p>
                                                {agentMission.status !== 'active' && (
                                                    <p>• Target Bisnis: <strong>{agentMission.business_name}</strong></p>
                                                )}
                                                {agentMission.status === 'released' && (
                                                    <p>• Reward Emas Didapat: <strong className="text-primary-container">+{agentMission.reward_gold_amount} Emas</strong></p>
                                                )}
                                            </div>
                                        </div>
                                    )}

                                    {/* Actions */}
                                    <div className="pt-2">
                                        {!agentMission ? (
                                            <button
                                                onClick={handleTakeAgentMission}
                                                disabled={Boolean(loading || (wallet && wallet.silver_balance < 2500))}
                                                className="w-full py-4 rounded-xl bg-primary-container text-on-primary font-label-sm text-sm font-bold tracking-widest hover:bg-primary-fixed-dim transition-transform hover:scale-[1.02] active:scale-95 disabled:opacity-50"
                                            >
                                                [ AMBIL MISI & STAKE 2.500 PERAK ]
                                            </button>
                                        ) : agentMission.status === 'active' ? (
                                            <button
                                                onClick={() => {
                                                    setShowSurveyModal(true);
                                                    setShowMissionModal(false);
                                                }}
                                                className="w-full py-4 rounded-xl bg-secondary text-on-secondary font-label-sm text-sm font-bold tracking-widest hover:bg-secondary-fixed hover:scale-[1.02] transition-transform active:scale-95 shadow-[0_0_15px_rgba(102,221,139,0.3)]"
                                            >
                                                [ DAFTARKAN BISNIS SEKARANG ]
                                            </button>
                                        ) : agentMission.status === 'completed' ? (
                                            <div className="text-center text-xs text-on-surface-variant py-2">
                                                Menunggu konfirmasi pendaftaran & pembayaran pertama pemilik bisnis untuk mencairkan jaminan perak dan reward komisi emas.
                                            </div>
                                        ) : (
                                            <button
                                                onClick={handleTakeAgentMission}
                                                disabled={Boolean(loading || (wallet && wallet.silver_balance < 2500))}
                                                className="w-full py-4 rounded-xl bg-primary-container text-on-primary font-label-sm text-sm font-bold tracking-widest hover:bg-primary-fixed-dim transition-transform hover:scale-[1.02] active:scale-95 disabled:opacity-50"
                                            >
                                                [ AMBIL MISI BARU ]
                                            </button>
                                        )}
                                    </div>
                                </>
                            )}
                        </div>
                    </div>
                </div>
            )}

            {/* Modal: Kuesioner Survei Bisnis */}
            {showSurveyModal && (
                <div className="fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4">
                    <div className="glass-panel w-full max-w-md rounded-2xl overflow-hidden border border-glass-stroke shadow-2xl animate-fade-in flex flex-col max-h-[90vh]">
                        {/* Header */}
                        <div className="p-5 border-b border-glass-stroke flex justify-between items-center bg-obsidian-surface/60">
                            <h3 className="font-headline-md text-lg font-bold text-primary-container flex items-center gap-2" style={{ fontFamily: "'Playfair Display', serif" }}>
                                <span className="material-symbols-outlined text-primary-container">rate_review</span>
                                Survei Bisnis Baru
                            </h3>
                            <button onClick={() => {
                                setShowSurveyModal(false);
                                setShowMissionModal(true);
                            }} className="text-on-surface-variant hover:text-white transition-colors">
                                <span className="material-symbols-outlined">close</span>
                            </button>
                        </div>

                        {/* Form */}
                        <form onSubmit={handleSubmitSurvey} className="p-6 overflow-y-auto space-y-4 flex-grow text-sm">
                            <div className="space-y-1">
                                <label className="block text-xs font-bold text-on-surface-variant uppercase">Nama Bisnis / Usaha</label>
                                <input
                                    type="text"
                                    required
                                    placeholder="Contoh: Kedai Kopi Minang"
                                    value={surveyData.business_name}
                                    onChange={e => setSurveyData({...surveyData, business_name: e.target.value})}
                                    className="w-full p-3 rounded-lg bg-surface border border-glass-stroke text-on-surface focus:border-primary-container focus:outline-none"
                                />
                            </div>

                            <div className="space-y-1">
                                <label className="block text-xs font-bold text-on-surface-variant uppercase">Alamat Bisnis</label>
                                <input
                                    type="text"
                                    required
                                    placeholder="Contoh: Jl. Sudirman No. 45"
                                    value={surveyData.owner_name} // Reusing field for location/address or simple text
                                    onChange={e => setSurveyData({...surveyData, owner_name: e.target.value})}
                                    className="w-full p-3 rounded-lg bg-surface border border-glass-stroke text-on-surface focus:border-primary-container focus:outline-none"
                                />
                            </div>

                            <div className="space-y-1">
                                <label className="block text-xs font-bold text-on-surface-variant uppercase">Jenis Bisnis</label>
                                <select
                                    value={surveyData.business_type}
                                    onChange={e => setSurveyData({...surveyData, business_type: e.target.value})}
                                    className="w-full p-3 rounded-lg bg-surface border border-glass-stroke text-on-surface focus:border-primary-container focus:outline-none"
                                >
                                    <option value="FnB/Café">Café / FnB</option>
                                    <option value="PnD/Grosiran">Grosiran / PnD</option>
                                    <option value="Hotel/Wisma">Hotel / Wisma</option>
                                    <option value="TokoJasa">Toko / Jasa Lainnya</option>
                                </select>
                            </div>

                            <div className="border-t border-glass-stroke my-3 pt-3">
                                <h5 className="font-bold text-xs text-primary-container uppercase mb-2">Informasi Pemilik</h5>
                            </div>

                            <div className="space-y-1">
                                <label className="block text-xs font-bold text-on-surface-variant uppercase">Nama Pemilik</label>
                                <input
                                    type="text"
                                    required
                                    placeholder="Contoh: Datuk Mudo"
                                    value={surveyData.owner_name}
                                    onChange={e => setSurveyData({...surveyData, owner_name: e.target.value})}
                                    className="w-full p-3 rounded-lg bg-surface border border-glass-stroke text-on-surface focus:border-primary-container focus:outline-none"
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div className="space-y-1">
                                    <label className="block text-xs font-bold text-on-surface-variant uppercase">Umur Pemilik</label>
                                    <input
                                        type="number"
                                        required
                                        placeholder="35"
                                        className="w-full p-3 rounded-lg bg-surface border border-glass-stroke text-on-surface focus:border-primary-container focus:outline-none"
                                    />
                                </div>
                                <div className="space-y-1">
                                    <label className="block text-xs font-bold text-on-surface-variant uppercase">Jenis Kelamin</label>
                                    <select className="w-full p-3 rounded-lg bg-surface border border-glass-stroke text-on-surface focus:border-primary-container focus:outline-none">
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                            </div>

                            <div className="space-y-1">
                                <label className="block text-xs font-bold text-on-surface-variant uppercase">Email Pemilik</label>
                                <input
                                    type="email"
                                    required
                                    placeholder="pemilik@bisnis.com"
                                    value={surveyData.owner_email}
                                    onChange={e => setSurveyData({...surveyData, owner_email: e.target.value})}
                                    className="w-full p-3 rounded-lg bg-surface border border-glass-stroke text-on-surface focus:border-primary-container focus:outline-none"
                                />
                            </div>

                            <div className="space-y-1">
                                <label className="block text-xs font-bold text-on-surface-variant uppercase">No. WhatsApp / Kontak</label>
                                <input
                                    type="tel"
                                    required
                                    placeholder="081234567890"
                                    value={surveyData.owner_phone}
                                    onChange={e => setSurveyData({...surveyData, owner_phone: e.target.value})}
                                    className="w-full p-3 rounded-lg bg-surface border border-glass-stroke text-on-surface focus:border-primary-container focus:outline-none"
                                />
                            </div>

                            <div className="space-y-1">
                                <label className="block text-xs font-bold text-on-surface-variant uppercase">Upload QRIS Payment (Opsional)</label>
                                <input
                                    type="file"
                                    accept="image/*"
                                    className="w-full text-xs text-on-surface-variant file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-primary-container file:text-on-primary hover:file:bg-primary-fixed-dim"
                                />
                            </div>

                            <button
                                type="submit"
                                disabled={loading}
                                className="w-full mt-4 py-4 rounded-xl bg-secondary text-on-secondary font-label-sm text-sm font-bold tracking-widest hover:bg-secondary-fixed hover:scale-[1.02] transition-transform active:scale-95 shadow-[0_0_15px_rgba(102,221,139,0.3)]"
                            >
                                [ KIRIM PENDAFTARAN BISNIS ]
                            </button>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
