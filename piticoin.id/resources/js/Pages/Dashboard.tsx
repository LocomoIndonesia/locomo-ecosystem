import { useState, useEffect } from 'react';
import { Head, router, Link } from '@inertiajs/react';
import { Coins, Flame, Map, Users, ArrowRight, Shield, Award, LogOut, MessageSquare, Send, X, RefreshCw, User as UserIcon, HelpCircle, Heart, Gavel, Bell } from 'lucide-react';
import CookieConsentBanner from '@/Components/CookieConsentBanner';
import NotificationDrawer from '@/Components/NotificationDrawer';

interface DashboardProps {
    user: {
        id: string;
        name: string;
        whatsapp: string;
        role: string;
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

export default function Dashboard({
    user,
    wallet,
    quests,
    escrows,
    global_pool,
    downlines,
    passive_income,
}: DashboardProps) {
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState<{ text: string; type: 'success' | 'error' } | null>(null);
    const [now, setNow] = useState(new Date());
    const [isMounted, setIsMounted] = useState(false);
    const [animatedFiat, setAnimatedFiat] = useState<number>(
        Number(global_pool?.total_fiat_in_bank || 500000) - Number(global_pool?.total_gold_in_circulation || 0)
    );

    // Notification Drawer & WebPush State
    const [showNotificationDrawer, setShowNotificationDrawer] = useState(false);

    // Consultation & Q&A States
    const [showConsultModal, setShowConsultModal] = useState(false);
    const [consultMessages, setConsultMessages] = useState<any[]>([]);
    const [consultLoading, setConsultLoading] = useState(false);
    const [consultInput, setConsultInput] = useState('');
    const [sendingConsult, setSendingConsult] = useState(false);

    const openConsultation = async () => {
        setShowConsultModal(true);
        setConsultLoading(true);
        try {
            const res = await fetch(route('consultation.my'));
            const data = await res.json();
            setConsultMessages(data.messages || []);
        } catch (err) {
            console.error('Failed to load consultation messages', err);
        } finally {
            setConsultLoading(false);
        }
    };

    const handleSendConsultation = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!consultInput.trim()) return;

        setSendingConsult(true);
        try {
            const res = await fetch(route('consultation.reply'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                },
                body: JSON.stringify({
                    message: consultInput.trim(),
                    subject: 'Konsultasi Saudagar',
                }),
            });
            const data = await res.json();
            if (data.success && data.message) {
                setConsultMessages(prev => [...prev, data.message]);
                setConsultInput('');
            }
        } catch (err) {
            console.error('Error sending message', err);
        } finally {
            setSendingConsult(false);
        }
    };

    // Client mount detection to avoid hydration mismatches
    useEffect(() => {
        setIsMounted(true);
    }, []);

    // Update current time for countdowns
    useEffect(() => {
        const interval = setInterval(() => {
            setNow(new Date());
        }, 1000);
        return () => clearInterval(interval);
    }, []);

    // available gold pool capacity
    const poolCapacity = Number(global_pool?.total_fiat_in_bank || 500000) - Number(global_pool?.total_gold_in_circulation || 0);

    // Gunung Mas ticker animation (up and down)
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

    // Logout
    const handleLogout = () => {
        router.post(route('logout'));
    };

    // Logic 1: Simulate Payment Webhook (Rp 100k)
    const simulateUpgradePayment = async () => {
        setLoading(true);
        try {
            const res = await fetch(route('payment.webhook'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ userId: user.id }),
            });
            const data = await res.json();
            if (data.success) {
                showMsg('Simulasi pembayaran Rp 100.000 sukses! Akun diupgrade & Perak ditambahkan.');
                router.reload();
            } else {
                showMsg(data.error || 'Simulasi gagal', 'error');
            }
        } catch (err) {
            showMsg('Gagal memproses simulasi pembayaran', 'error');
        } finally {
            setLoading(false);
        }
    };

    // Logic 2: Stake Quest
    const handleStakeQuest = async (questId: string) => {
        setLoading(true);
        try {
            const res = await fetch('/api/quests/stake', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
                },
                body: JSON.stringify({ quest_id: questId })
            });
            const data = await res.json();
            if (data.success) {
                showMsg(data.message || 'Quest berhasil distake! Ekspedisi dimulai.');
                router.reload();
            } else {
                showMsg(data.error || 'Gagal melakukan stake', 'error');
            }
        } catch (err) {
            showMsg('Terjadi kesalahan saat memproses stake', 'error');
        } finally {
            setLoading(false);
        }
    };

    // Admin resolution: Resolve Success or Fail
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
                showMsg(`Ekspedisi diselesaikan dengan status: ${isSuccess ? 'SUKSES' : 'GAGAL'}`);
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

    // Timer calculation helper
    const getRemainingTime = (expiresAtStr: string) => {
        const expiresAt = new Date(expiresAtStr);
        const diffMs = expiresAt.getTime() - now.getTime();
        if (diffMs <= 0) return { label: 'Kedaluwarsa', expired: true };

        const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
        const diffMins = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
        const diffSecs = Math.floor((diffMs % (1000 * 60)) / 1000);

        return {
            label: `${diffHours}j ${diffMins}m ${diffSecs}d`,
            expired: false,
        };
    };

    const formatRupiah = (val: number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(val);
    };

    return (
        <div className="dashboard-container">
            <Head title="Dasbor Utama" />

            {/* Ticker at Top: The Mountain (Gunung Mas) */}
            <section className="mountain-section">
                <div className="mountain-glow"></div>
                <h2 className="mountain-title">Gunung Mas (Global Liquidity Pool)</h2>
                <div className="mountain-value">{formatRupiah(animatedFiat)}</div>
                <p className="mountain-subtitle">
                    Cadangan Likuiditas Tersedia (Koin Emas beredar: {formatRupiah(global_pool?.total_gold_in_circulation || 0)})
                </p>
            </section>

            {/* Notifications */}
            {message && (
                <div className={`notification ${message.type === 'error' ? 'notification-error' : 'notification-success'}`}>
                    {message.text}
                </div>
            )}

            {/* Dual-Wallet Card */}
            <div className="wallet-card">
                <div className="wallet-user-info">
                    <div>
                        <span className="username">{user.name}</span>
                        <div style={{ fontSize: '0.65rem', color: '#a0aec0' }}>WA: {user.whatsapp}</div>
                    </div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '0.4rem', flexWrap: 'wrap' }}>
                        <Link
                            href={route('profile.edit')}
                            className="btn btn-secondary"
                            style={{ fontSize: '0.7rem', padding: '0.35rem 0.6rem', display: 'flex', alignItems: 'center', gap: '0.3rem', borderColor: 'rgba(255,255,255,0.2)', color: '#fff' }}
                        >
                            <UserIcon size={13} className="text-[#ffd700]" />
                            <span>Profil</span>
                        </Link>
                        <Link
                            href={route('baitul-mal.index')}
                            className="btn btn-secondary"
                            style={{ fontSize: '0.7rem', padding: '0.35rem 0.6rem', display: 'flex', alignItems: 'center', gap: '0.3rem', borderColor: 'rgba(255,215,0,0.35)', color: '#ffd700' }}
                        >
                            <Heart size={13} className="text-amber-400" />
                            <span>Baitul Mal</span>
                        </Link>
                        <Link
                            href={route('auction.index')}
                            className="btn btn-secondary"
                            style={{ fontSize: '0.7rem', padding: '0.35rem 0.6rem', display: 'flex', alignItems: 'center', gap: '0.3rem', borderColor: 'rgba(255,255,255,0.2)', color: '#fff' }}
                        >
                            <Gavel size={13} className="text-amber-400" />
                            <span>Lelang</span>
                        </Link>
                        <Link
                            href={route('help.index')}
                            className="btn btn-secondary"
                            style={{ fontSize: '0.7rem', padding: '0.35rem 0.6rem', display: 'flex', alignItems: 'center', gap: '0.3rem', borderColor: 'rgba(255,255,255,0.2)', color: '#fff' }}
                        >
                            <HelpCircle size={13} className="text-[#ffd700]" />
                            <span>Bantuan</span>
                        </Link>
                        <button
                            onClick={openConsultation}
                            className="btn btn-secondary"
                            style={{ fontSize: '0.7rem', padding: '0.35rem 0.65rem', display: 'flex', alignItems: 'center', gap: '0.35rem', borderColor: 'rgba(255,215,0,0.4)', color: '#ffd700' }}
                        >
                            <MessageSquare size={13} />
                            <span>Konsultasi</span>
                        </button>
                        <button
                            onClick={() => setShowNotificationDrawer(true)}
                            className="btn btn-secondary"
                            style={{ fontSize: '0.7rem', padding: '0.35rem 0.65rem', display: 'flex', alignItems: 'center', gap: '0.35rem', borderColor: 'rgba(255,215,0,0.4)', color: '#ffd700' }}
                            title="Pusat Notifikasi & WebPush"
                        >
                            <Bell size={13} />
                            <span>Notifikasi</span>
                        </button>
                        <span className={`user-badge ${user.role === 'MERCHANT' ? 'badge-merchant' : 'badge-guest'}`}>
                            {user.role === 'MERCHANT' ? 'Merchant Tier' : 'Free Tier'}
                        </span>
                    </div>
                </div>

                <div className="balances-grid">
                    <div className="balance-item balance-silver">
                        <span className="balance-label balance-label-silver">Koin Perak (Silver)</span>
                        <span className="balance-val">{wallet ? Number(wallet.silver_balance).toLocaleString('id-ID') : 0}</span>
                        <span className="balance-desc">Staking / Non-Withdrawal</span>
                    </div>
                    <div className="balance-item balance-gold">
                        <span className="balance-label balance-label-gold">Koin Emas (Gold)</span>
                        <span className="balance-val">{wallet ? Number(wallet.gold_balance).toLocaleString('id-ID') : 0}</span>
                        <span className="balance-desc">Withdrawable Fiat</span>
                    </div>
                </div>

                {/* If GUEST, show Upgrade / Starter Kit simulator */}
                {user.role === 'GUEST' ? (
                    <div style={{ marginTop: '0.5rem' }}>
                        <p style={{ fontSize: '0.7rem', color: '#a0aec0', marginBottom: '0.75rem', lineHeight: '1.4' }}>
                            Anda berada pada tier <strong>GUEST</strong>. Upgrade ke <strong>MERCHANT</strong> seharga <strong>Rp 100.000</strong> untuk membuka Staking Quests dan mendapatkan 100.000 Perak!
                        </p>
                        <button onClick={simulateUpgradePayment} disabled={loading} className="btn btn-primary">
                            <Coins size={14} />
                            <span>Simulasi Beli Starter Kit (Upgrade)</span>
                        </button>
                    </div>
                ) : (
                    <div style={{ display: 'flex', gap: '0.5rem', marginTop: '0.5rem' }}>
                        <button onClick={simulateUpgradePayment} disabled={loading} className="btn btn-secondary" style={{ flex: 1, fontSize: '0.7rem', padding: '0.5rem' }}>
                            Simulasi Beli Koin Perak (+100k Perak)
                        </button>
                        <button onClick={handleLogout} className="btn btn-secondary" style={{ width: '80px', fontSize: '0.7rem', padding: '0.5rem' }}>
                            <LogOut size={12} style={{ marginRight: '0.25rem' }} />
                            Keluar
                        </button>
                    </div>
                )}
            </div>

            {/* Game Launcher Card */}
            <div className="card game-launcher-card" style={{
                background: 'linear-gradient(135deg, rgba(20, 22, 33, 0.95) 0%, rgba(45, 34, 18, 0.95) 100%)',
                border: '1.5px solid rgba(255, 189, 20, 0.3)',
                padding: '1.25rem',
                borderRadius: '16px',
                marginTop: '1rem',
                marginBottom: '1rem',
                boxShadow: '0 8px 30px rgba(0, 0, 0, 0.4), inset 0 1px 1px rgba(255, 255, 255, 0.05)',
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                gap: '1rem'
            }}>
                <div style={{ flex: 1 }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', marginBottom: '0.25rem' }}>
                        <Award size={18} color="#ffbd14" />
                        <strong style={{ fontSize: '0.95rem', color: '#fff', letterSpacing: '0.5px' }}>Buru Harta Karun: Gunung Mas</strong>
                    </div>
                    <p style={{ fontSize: '0.72rem', color: '#a0aec0', lineHeight: '1.4', margin: 0 }}>
                        Putar slot menggunakan Koin Perak dan dapatkan Koin Emas dengan turn over 1x (1 Emas = 100 Perak)!
                    </p>
                </div>
                <a
                    href="/games/harta-karun/index.html"
                    target="_blank"
                    rel="noopener noreferrer"
                    className="btn btn-primary"
                    style={{
                        background: 'linear-gradient(135deg, #ffe066 0%, #d4af37 100%)',
                        color: '#000',
                        fontWeight: 800,
                        fontSize: '0.75rem',
                        padding: '0.6rem 1rem',
                        borderRadius: '8px',
                        textDecoration: 'none',
                        textAlign: 'center',
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '0.35rem',
                        whiteSpace: 'nowrap',
                        boxShadow: '0 0 15px rgba(255, 189, 20, 0.3)'
                    }}
                >
                    <span>Main Sekarang</span>
                    <ArrowRight size={14} />
                </a>
            </div>

            {/* Active Missions (Escrows) */}
            <div>
                <h3 className="section-title">
                    <Flame size={16} color="#e2b842" />
                    <span>Ekspedisi Berjalan (Active Escrows)</span>
                </h3>
                <div className="escrow-list">
                    {escrows.filter((e) => e.status === 'LOCKED').length === 0 ? (
                        <div style={{ textAlign: 'center', fontSize: '0.75rem', color: '#4a5568', padding: '1rem', border: '1px dashed rgba(255,255,255,0.05)', borderRadius: '12px' }}>
                            Tidak ada ekspedisi yang sedang berjalan.
                        </div>
                    ) : (
                        escrows.filter((e) => e.status === 'LOCKED').map((escrow) => {
                            const timer = getRemainingTime(escrow.expires_at);
                            return (
                                <div key={escrow.id} className="card escrow-card">
                                    <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '0.5rem' }}>
                                        <span style={{ fontWeight: 700, fontSize: '0.9rem' }}>{escrow.quest.title}</span>
                                        <span className="escrow-status-tag tag-locked">LOCKED</span>
                                    </div>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.7rem', color: '#a0aec0', marginBottom: '0.85rem' }}>
                                        <span>Staked Perak: {Number(escrow.staked_silver).toLocaleString('id-ID')}</span>
                                        <span>Potensi Emas: {Number(escrow.quest.reward_amount_gold).toLocaleString('id-ID')}</span>
                                    </div>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                        <div className={`timer-box ${timer.expired ? 'timer-expired' : ''}`}>
                                            <span>Sisa Waktu:</span>
                                            <strong>{isMounted ? timer.label : '--:--:--'}</strong>
                                        </div>
                                        <div style={{ display: 'flex', gap: '0.25rem' }}>
                                            <button
                                                onClick={() => handleResolveQuest(escrow.id, true)}
                                                disabled={loading || timer.expired}
                                                className="btn btn-primary"
                                                style={{ padding: '0.35rem 0.65rem', fontSize: '0.65rem', width: 'auto' }}
                                            >
                                                Sukses
                                            </button>
                                            <button
                                                onClick={() => handleResolveQuest(escrow.id, false)}
                                                disabled={loading}
                                                className="btn btn-secondary"
                                                style={{ padding: '0.35rem 0.65rem', fontSize: '0.65rem', width: 'auto', border: '1px solid rgba(239,68,68,0.2)', color: '#ef4444' }}
                                            >
                                                Gagal
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            );
                        })
                    )}
                </div>
            </div>

            {/* Quest Board */}
            <div>
                <h3 className="section-title">
                    <Map size={16} color="#e2b842" />
                    <span>Peta Ekspedisi (Quest Board)</span>
                </h3>
                <div className="quest-list">
                    {quests.map((quest) => (
                        <div key={quest.id} className="card">
                            <div className="quest-card-header">
                                <span className="quest-title">{quest.title}</span>
                                <span style={{ fontSize: '0.7rem', color: '#a0aec0', background: 'rgba(255,255,255,0.05)', padding: '0.2rem 0.4rem', borderRadius: '4px' }}>
                                    Limit: {quest.time_limit_hours} Jam
                                </span>
                            </div>
                            <p className="quest-desc">{quest.description}</p>
                            <div className="quest-meta">
                                <div>
                                    <span style={{ color: '#718096' }}>Stake Koin Perak: </span>
                                    <strong className="quest-cost">{Number(quest.stake_amount_silver).toLocaleString('id-ID')}</strong>
                                </div>
                                <div>
                                    <span style={{ color: '#718096' }}>Reward Emas: </span>
                                    <strong className="quest-reward">+{Number(quest.reward_amount_gold).toLocaleString('id-ID')}</strong>
                                </div>
                            </div>

                            {user.role !== 'MERCHANT' ? (
                                <button className="btn btn-disabled" disabled>
                                    BUTUH MERCHANT TIER
                                </button>
                            ) : wallet && Number(wallet.silver_balance) < Number(quest.stake_amount_silver) ? (
                                <button className="btn btn-disabled" disabled>
                                    SALDO PERAK KURANG
                                </button>
                            ) : (
                                <button onClick={() => handleStakeQuest(quest.id)} disabled={loading} className="btn btn-primary">
                                    <span>AMBIL EKSPEDISI (STAKE NOW)</span>
                                    <ArrowRight size={14} />
                                </button>
                            )}
                        </div>
                    ))}
                </div>
            </div>

            {/* Referral Network tree */}
            <div className="network-card">
                <h3 className="section-title">
                    <Users size={16} color="#e2b842" />
                    <span>Jaringan Saudagar (Referrals)</span>
                </h3>
                <div className="network-summary">
                    <span style={{ fontSize: '0.75rem', color: '#a0aec0' }}>Total Passive Income:</span>
                    <span className="network-income-val">{Number(passive_income).toLocaleString('id-ID')} Emas</span>
                </div>
                <div style={{ fontSize: '0.7rem', color: '#718096', marginBottom: '0.5rem' }}>
                    * Komisi Level 1: 25% | Komisi Level 2: 10% (dari reward emas downline)
                </div>

                <div style={{ display: 'flex', flexDirection: 'column', gap: '0.85rem', marginTop: '1rem' }}>
                    <div>
                        <strong style={{ fontSize: '0.75rem', color: '#e2b842', display: 'block', marginBottom: '0.35rem' }}>
                            Downline Langsung (Level 1 — {downlines.level_1.length} Anggota)
                        </strong>
                        <div className="downline-list">
                            {downlines.level_1.length === 0 ? (
                                <div style={{ fontSize: '0.7rem', color: '#4a5568' }}>Belum memiliki downline langsung.</div>
                            ) : (
                                downlines.level_1.map((d) => (
                                    <div key={d.id} className="downline-item">
                                        <span className="downline-name">{d.name}</span>
                                        <span style={{ color: '#a0aec0' }}>WA: {d.whatsapp}</span>
                                        <span className="downline-level">{d.role}</span>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>

                    <div>
                        <strong style={{ fontSize: '0.75rem', color: '#e2b842', display: 'block', marginBottom: '0.35rem' }}>
                            Downline Tidak Langsung (Level 2 — {downlines.level_2.length} Anggota)
                        </strong>
                        <div className="downline-list">
                            {downlines.level_2.length === 0 ? (
                                <div style={{ fontSize: '0.7rem', color: '#4a5568' }}>Belum memiliki downline Level 2.</div>
                            ) : (
                                downlines.level_2.map((d) => (
                                    <div key={d.id} className="downline-item">
                                        <span className="downline-name">{d.name}</span>
                                        <span style={{ color: '#a0aec0' }}>WA: {d.whatsapp}</span>
                                        <span className="downline-level">{d.role}</span>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Expedition History */}
            <div>
                <h3 className="section-title">
                    <Award size={16} color="#e2b842" />
                    <span>Riwayat Ekspedisi</span>
                </h3>
                <div className="escrow-list">
                    {escrows.filter((e) => e.status !== 'LOCKED').length === 0 ? (
                        <div style={{ textAlign: 'center', fontSize: '0.75rem', color: '#4a5568', padding: '1rem', border: '1px dashed rgba(255,255,255,0.05)', borderRadius: '12px' }}>
                            Belum ada riwayat ekspedisi diselesaikan.
                        </div>
                    ) : (
                        escrows.filter((e) => e.status !== 'LOCKED').map((escrow) => (
                            <div key={escrow.id} className={`card escrow-card ${escrow.status === 'SUCCESS' ? 'success' : 'failed'}`}>
                                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '0.25rem' }}>
                                    <span style={{ fontWeight: 700, fontSize: '0.85rem' }}>{escrow.quest.title}</span>
                                    <span className={`escrow-status-tag ${escrow.status === 'SUCCESS' ? 'tag-success' : 'tag-failed'}`}>
                                        {escrow.status}
                                    </span>
                                </div>
                                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.65rem', color: '#a0aec0' }}>
                                    <span>Stake: {Number(escrow.staked_silver).toLocaleString('id-ID')} Perak</span>
                                    <span>Tanggal: {isMounted ? new Date(escrow.created_at).toLocaleDateString('id-ID') : '...'}</span>
                                </div>
                            </div>
                        ))
                    )}
                </div>
            </div>

            {/* Consultation Modal for Saudagar */}
            {showConsultModal && (
                <div style={{
                    position: 'fixed',
                    top: 0,
                    left: 0,
                    right: 0,
                    bottom: 0,
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    backdropFilter: 'blur(4px)',
                    zIndex: 9999,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    padding: '1rem'
                }}>
                    <div style={{
                        backgroundColor: '#121216',
                        border: '1px solid rgba(255,215,0,0.3)',
                        borderRadius: '16px',
                        width: '100%',
                        maxWidth: '550px',
                        maxHeight: '85vh',
                        display: 'flex',
                        flexDirection: 'column',
                        overflow: 'hidden',
                        boxShadow: '0 10px 40px rgba(0,0,0,0.8)'
                    }}>
                        {/* Header */}
                        <div style={{
                            padding: '1rem',
                            borderBottom: '1px solid rgba(255,255,255,0.1)',
                            backgroundColor: '#16161e',
                            display: 'flex',
                            justifyContent: 'space-between',
                            alignItems: 'center'
                        }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                                <MessageSquare size={18} color="#ffd700" />
                                <div>
                                    <h3 style={{ margin: 0, fontSize: '0.9rem', color: '#fff', fontWeight: 'bold' }}>Konsultasi & Q&A Admin Platform</h3>
                                    <span style={{ fontSize: '0.65rem', color: '#a0aec0' }}>Jalur pesan pribadi dengan Administrator Level 0</span>
                                </div>
                            </div>
                            <button
                                onClick={() => setShowConsultModal(false)}
                                style={{ background: 'none', border: 'none', color: '#a0aec0', cursor: 'pointer', padding: '0.25rem' }}
                            >
                                <X size={18} />
                            </button>
                        </div>

                        {/* Message list */}
                        <div style={{
                            flex: 1,
                            padding: '1rem',
                            overflowY: 'auto',
                            minHeight: '260px',
                            maxHeight: '380px',
                            backgroundColor: '#0a0a0c',
                            display: 'flex',
                            flexDirection: 'column',
                            gap: '0.75rem'
                        }}>
                            {consultLoading ? (
                                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', height: '100%', color: '#a0aec0', fontSize: '0.8rem', gap: '0.5rem' }}>
                                    <RefreshCw size={14} className="animate-spin" />
                                    <span>Memuat pesan konsultasi...</span>
                                </div>
                            ) : consultMessages.length === 0 ? (
                                <div style={{ textAlign: 'center', margin: 'auto', color: '#718096', fontSize: '0.75rem' }}>
                                    <MessageSquare size={28} style={{ margin: '0 auto 0.5rem auto', opacity: 0.5 }} />
                                    <p style={{ margin: 0 }}>Belum ada pesan konsultasi.</p>
                                    <p style={{ margin: '0.25rem 0 0 0', fontSize: '0.7rem' }}>Ajukan pertanyaan atau konsultasi seputar strategi ekspedisi dan lisensi Anda.</p>
                                </div>
                            ) : (
                                consultMessages.map((msg) => {
                                    const isAdmin = msg.sender_role === 'ADMIN';
                                    return (
                                        <div
                                            key={msg.id}
                                            style={{
                                                alignSelf: isAdmin ? 'flex-start' : 'flex-end',
                                                maxWidth: '85%',
                                                display: 'flex',
                                                flexDirection: 'column',
                                                alignItems: isAdmin ? 'flex-start' : 'flex-end'
                                            }}
                                        >
                                            <div style={{ fontSize: '0.65rem', color: '#a0aec0', marginBottom: '0.2rem' }}>
                                                {isAdmin ? '👑 Administrator Platform' : 'Anda'} • {new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}
                                            </div>
                                            <div style={{
                                                backgroundColor: isAdmin ? '#1e1c12' : '#232332',
                                                color: isAdmin ? '#ffd700' : '#fff',
                                                border: isAdmin ? '1px solid rgba(255,215,0,0.3)' : '1px solid rgba(255,255,255,0.1)',
                                                borderRadius: '12px',
                                                padding: '0.65rem 0.85rem',
                                                fontSize: '0.75rem',
                                                lineHeight: 1.5,
                                                whiteSpace: 'pre-wrap'
                                            }}>
                                                {msg.subject && (
                                                    <div style={{ fontWeight: 'bold', fontSize: '0.7rem', marginBottom: '0.25rem', color: isAdmin ? '#f6ad55' : '#a0aec0' }}>
                                                        {msg.subject}
                                                    </div>
                                                )}
                                                {msg.message}
                                            </div>
                                        </div>
                                    );
                                })
                            )}
                        </div>

                        {/* Input form */}
                        <form onSubmit={handleSendConsultation} style={{
                            padding: '0.75rem',
                            borderTop: '1px solid rgba(255,255,255,0.1)',
                            backgroundColor: '#16161e',
                            display: 'flex',
                            gap: '0.5rem'
                        }}>
                            <input
                                type="text"
                                value={consultInput}
                                onChange={(e) => setConsultInput(e.target.value)}
                                placeholder="Ketik pertanyaan atau konsultasi untuk Admin..."
                                style={{
                                    flex: 1,
                                    backgroundColor: '#0a0a0c',
                                    border: '1px solid rgba(255,255,255,0.15)',
                                    borderRadius: '8px',
                                    padding: '0.5rem 0.75rem',
                                    fontSize: '0.75rem',
                                    color: '#fff',
                                    outline: 'none'
                                }}
                            />
                            <button
                                type="submit"
                                disabled={sendingConsult || !consultInput.trim()}
                                style={{
                                    backgroundColor: '#ffd700',
                                    color: '#000',
                                    fontWeight: 'bold',
                                    border: 'none',
                                    borderRadius: '8px',
                                    padding: '0.5rem 0.85rem',
                                    fontSize: '0.75rem',
                                    cursor: 'pointer',
                                    display: 'flex',
                                    alignItems: 'center',
                                    gap: '0.35rem',
                                    opacity: (!consultInput.trim() || sendingConsult) ? 0.5 : 1
                                }}
                            >
                                <Send size={13} />
                                <span>Kirim</span>
                            </button>
                        </form>
                    </div>
                </div>
            )}

            {/* Dashboard Global Footer & Compliance Links */}
            <footer style={{ marginTop: '3rem', paddingTop: '1.5rem', borderTop: '1px solid rgba(255, 255, 255, 0.1)', textAlign: 'center', fontSize: '0.75rem', color: '#718096' }}>
                <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '1.25rem', marginBottom: '0.75rem', flexWrap: 'wrap' }}>
                    <Link href={route('legal.terms')} style={{ color: '#a0aec0', textDecoration: 'none' }}>
                        Syarat & Ketentuan
                    </Link>
                    <span>•</span>
                    <Link href={route('legal.privacy')} style={{ color: '#a0aec0', textDecoration: 'none' }}>
                        Kebijakan Privasi (UU PDP)
                    </Link>
                    <span>•</span>
                    <Link href={route('help.index')} style={{ color: '#a0aec0', textDecoration: 'none' }}>
                        Pusat Bantuan & FAQ
                    </Link>
                    <span>•</span>
                    <Link href={route('baitul-mal.index')} style={{ color: '#ffd700', textDecoration: 'none' }}>
                        Baitul Mal Sosial
                    </Link>
                </div>
                <p>© 2026 PitiCoin.id — Gerbang Pembayaran Sentral & Ekosistem Gunung Mas.</p>
            </footer>

            <CookieConsentBanner />
            <NotificationDrawer
                isOpen={showNotificationDrawer}
                onClose={() => setShowNotificationDrawer(false)}
            />
        </div>
    );
}
