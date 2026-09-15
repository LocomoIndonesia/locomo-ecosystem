import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { 
    Scale, 
    ShieldCheck, 
    AlertTriangle, 
    Gavel, 
    Coins, 
    Clock, 
    CheckCircle, 
    XCircle, 
    PlusCircle, 
    Eye, 
    ArrowLeft, 
    Sparkles, 
    Upload, 
    Lock, 
    UserX, 
    HelpCircle,
    Award,
    TrendingUp,
    Check
} from 'lucide-react';
import CookieConsentBanner from '@/Components/CookieConsentBanner';

interface VerificationJuror {
    id: string;
    juror_user_id: string;
    vote: 'PENDING' | 'VALID' | 'REJECT';
    stake_amount_silver: number;
    reward_amount_silver: number;
    is_slashed: boolean;
    voted_at: string | null;
    juror?: {
        name: string;
    };
}

interface VerificationItem {
    id: string;
    mission_title: string;
    proof_description: string;
    proof_image_url: string | null;
    reward_gold: number;
    reward_silver: number;
    status: 'IN_VOTING' | 'CONSENSUS_APPROVED' | 'CONSENSUS_REJECTED' | 'EXPIRED';
    valid_votes_count: number;
    reject_votes_count: number;
    total_jurors_count: number;
    consensus_reached_at: string | null;
    created_at: string;
    masked_submitter?: string;
    submitter?: {
        name: string;
    };
    quest?: {
        title: string;
    };
    jurors?: VerificationJuror[];
}

interface JurorAssignment {
    id: string;
    verification_id: string;
    juror_user_id: string;
    vote: 'PENDING' | 'VALID' | 'REJECT';
    stake_amount_silver: number;
    reward_amount_silver: number;
    is_slashed: boolean;
    voted_at: string | null;
    notes: string | null;
    created_at: string;
    verification: VerificationItem;
}

interface IndexProps {
    user: {
        id: string;
        name: string;
        phone: string;
        role: string;
        license_status: string;
        jury_banned_until: string | null;
        is_jury_banned: boolean;
        experience_points: number;
    };
    wallet: {
        silver_balance: number;
        gold_balance: number;
    };
    reputation_score: number;
    pending_tasks: JurorAssignment[];
    juror_history: JurorAssignment[];
    my_submissions: VerificationItem[];
    stats: {
        pending_count: number;
        completed_count: number;
        slashed_count: number;
    };
    flash?: {
        success?: string;
        error?: string;
        warning?: string;
    };
}

export default function VerificationIndex({
    user,
    wallet,
    reputation_score,
    pending_tasks,
    juror_history,
    my_submissions,
    stats,
    flash,
}: IndexProps) {
    const [activeTab, setActiveTab] = useState<'pending' | 'history' | 'my_submissions'>('pending');
    const [showSubmitModal, setShowSubmitModal] = useState(false);
    const [imagePreview, setImagePreview] = useState<string | null>(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        mission_title: '',
        proof_description: '',
        reward_gold: 50,
        reward_silver: 500,
        proof_image: null as File | null,
    });

    const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            setData('proof_image', file);
            setImagePreview(URL.createObjectURL(file));
        }
    };

    const handleOpenSubmitModal = () => {
        reset();
        setImagePreview(null);
        setShowSubmitModal(true);
    };

    const handleSubmitMissionProof = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('verifications.submit'), {
            onSuccess: () => {
                setShowSubmitModal(false);
                reset();
                setImagePreview(null);
                setActiveTab('my_submissions');
            },
        });
    };

    return (
        <div className="min-h-screen bg-[#0a0a0f] text-gray-100 flex flex-col font-sans selection:bg-amber-400 selection:text-black">
            <Head title="Konsensus Juri Misi (5 Juri & Honeypot) | PitiCoin.id" />

            {/* Top Navigation Bar */}
            <header className="sticky top-0 z-40 bg-[#0f0f17]/90 backdrop-blur-md border-b border-white/10 px-4 py-3">
                <div className="max-w-6xl mx-auto flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Link
                            href={route('dashboard')}
                            className="w-9 h-9 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 flex items-center justify-center text-gray-300 hover:text-white transition-all active:scale-95"
                            title="Kembali ke Dashboard"
                        >
                            <ArrowLeft className="w-5 h-5" />
                        </Link>
                        <div>
                            <div className="flex items-center gap-2">
                                <h1 className="text-base font-bold text-white tracking-wide flex items-center gap-1.5">
                                    <Scale className="w-4 h-4 text-amber-400" />
                                    Konsensus Juri Verifikasi
                                </h1>
                                <span className="text-[10px] uppercase font-mono px-2 py-0.5 rounded-full bg-amber-400/10 text-amber-400 border border-amber-400/30 font-semibold">
                                    Fase 4
                                </span>
                            </div>
                            <p className="text-[11px] text-gray-400">
                                Validasi terdesentralisasi 5 juri independen & sistem anti-penipuan honeypot
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        <button
                            onClick={handleOpenSubmitModal}
                            className="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-black font-semibold text-xs transition-all shadow-[0_0_15px_rgba(245,158,11,0.25)] active:scale-95"
                        >
                            <PlusCircle className="w-3.5 h-3.5" />
                            <span className="hidden sm:inline">Ajukan Bukti Misi</span>
                            <span className="sm:hidden">Ajukan</span>
                        </button>
                    </div>
                </div>
            </header>

            {/* Flash Banners */}
            <div className="max-w-6xl mx-auto w-full px-4 pt-4">
                {flash?.success && (
                    <div className="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center gap-2">
                        <CheckCircle className="w-4 h-4 flex-shrink-0" />
                        <span>{flash.success}</span>
                    </div>
                )}
                {flash?.warning && (
                    <div className="mb-4 p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs flex items-center gap-2">
                        <AlertTriangle className="w-4 h-4 flex-shrink-0" />
                        <span>{flash.warning}</span>
                    </div>
                )}
                {flash?.error && (
                    <div className="mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs flex items-center gap-2">
                        <XCircle className="w-4 h-4 flex-shrink-0" />
                        <span>{flash.error}</span>
                    </div>
                )}
                {user.is_jury_banned && (
                    <div className="mb-4 p-3.5 rounded-xl bg-rose-500/15 border border-rose-500/40 text-rose-300 text-xs flex items-start gap-2.5 shadow-lg shadow-rose-950/20">
                        <UserX className="w-5 h-5 flex-shrink-0 text-rose-400 mt-0.5" />
                        <div>
                            <strong className="font-bold text-rose-200 block">Status Juri Sedang Dibekukan</strong>
                            <span>
                                Anda terbukti menyetujui misi jebakan (Honeypot Trap). Hak juri ditangguhkan hingga{' '}
                                <span className="font-mono font-semibold text-white">
                                    {new Date(user.jury_banned_until!).toLocaleString('id-ID')}
                                </span>.
                            </span>
                        </div>
                    </div>
                )}
            </div>

            {/* Main Content Area */}
            <main className="max-w-6xl mx-auto w-full px-4 py-4 space-y-6 flex-1">
                
                {/* Juror Profile & Reputation Hero Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    {/* Card 1: Juror Status */}
                    <div className="p-4 rounded-2xl bg-gradient-to-br from-[#151522] to-[#0d0d15] border border-white/10 shadow-lg relative overflow-hidden">
                        <div className="flex items-center justify-between">
                            <span className="text-[11px] font-mono text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                <Gavel className="w-3.5 h-3.5 text-amber-400" />
                                Kredensial Juri
                            </span>
                            {user.is_jury_banned ? (
                                <span className="text-[10px] px-2 py-0.5 rounded-md bg-rose-500/20 text-rose-400 border border-rose-500/30 font-bold">
                                    DIBEKUKAN
                                </span>
                            ) : (
                                <span className="text-[10px] px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 font-bold">
                                    AKTIF & SAH
                                </span>
                            )}
                        </div>
                        <div className="mt-3 flex items-baseline gap-2">
                            <span className="text-xl font-bold text-white tracking-tight">{user.name}</span>
                        </div>
                        <div className="mt-2 flex items-center gap-3 text-[11px] text-gray-400">
                            <span>Pasak per Vote: <strong className="text-white font-mono">50 Perak</strong></span>
                            <span>•</span>
                            <span>Dividen: <strong className="text-emerald-400 font-mono">+25 Perak</strong></span>
                        </div>
                    </div>

                    {/* Card 2: Reputation Score */}
                    <div className="p-4 rounded-2xl bg-gradient-to-br from-[#151522] to-[#0d0d15] border border-white/10 shadow-lg relative overflow-hidden">
                        <div className="flex items-center justify-between">
                            <span className="text-[11px] font-mono text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                <TrendingUp className="w-3.5 h-3.5 text-sky-400" />
                                Skor Reputasi
                            </span>
                            <span className="text-[10px] px-2 py-0.5 rounded-md bg-sky-500/10 text-sky-400 border border-sky-500/30 font-mono">
                                Min. 250 PTS
                            </span>
                        </div>
                        <div className="mt-3 flex items-baseline gap-2">
                            <span className="text-2xl font-black text-sky-300 font-mono">{reputation_score}</span>
                            <span className="text-xs text-gray-400">PTS</span>
                        </div>
                        <div className="mt-2 text-[11px] text-gray-400">
                            {reputation_score >= 250 ? (
                                <span className="text-emerald-400 flex items-center gap-1">
                                    <CheckCircle className="w-3 h-3" /> Memenuhi syarat prioritas juri independen
                                </span>
                            ) : (
                                <span className="text-amber-400 flex items-center gap-1">
                                    <AlertTriangle className="w-3 h-3" /> Skor rendah, prioritas juri berkurang
                                </span>
                            )}
                        </div>
                    </div>

                    {/* Card 3: Wallet Staking Balance */}
                    <div className="p-4 rounded-2xl bg-gradient-to-br from-[#151522] to-[#0d0d15] border border-white/10 shadow-lg relative overflow-hidden">
                        <div className="flex items-center justify-between">
                            <span className="text-[11px] font-mono text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                <Coins className="w-3.5 h-3.5 text-amber-400" />
                                Saldo Kas Staking
                            </span>
                            <span className="text-[10px] px-2 py-0.5 rounded-md bg-amber-400/10 text-amber-300 border border-amber-400/20 font-mono">
                                Proof-of-Stake
                            </span>
                        </div>
                        <div className="mt-3 flex items-baseline gap-2">
                            <span className="text-2xl font-black text-amber-300 font-mono">
                                {wallet.silver_balance.toLocaleString('id-ID')}
                            </span>
                            <span className="text-xs text-gray-400">Perak</span>
                        </div>
                        <div className="mt-2 text-[11px] text-gray-400 flex items-center justify-between">
                            <span>Koin Emas: <strong className="text-amber-400 font-mono">{wallet.gold_balance}</strong></span>
                            <span>{stats.completed_count} Suara Selesai</span>
                        </div>
                    </div>
                </div>

                {/* Honeypot System Awareness Alert */}
                <div className="p-3.5 rounded-2xl bg-gradient-to-r from-amber-500/10 via-surface to-rose-500/10 border border-amber-500/20 flex items-center justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <div className="w-9 h-9 rounded-xl bg-amber-400/10 text-amber-400 flex items-center justify-center border border-amber-400/30 flex-shrink-0">
                            <AlertTriangle className="w-4 h-4" />
                        </div>
                        <div>
                            <strong className="text-xs font-bold text-white block">
                                Peringatan Honeypot Trap Engine (Anti-Kolusi & Bot)
                            </strong>
                            <p className="text-[11px] text-gray-400 leading-tight">
                                Sistem menyisipkan bukti misi fiktif acak. Juri yang lalai atau ceroboh memvalidasi honeypot akan kehilangan pasak, dipotong 100 reputasi, dan diskors 7 hari.
                            </p>
                        </div>
                    </div>
                    <div className="hidden sm:flex items-center gap-1 text-[11px] font-mono text-amber-400/90 whitespace-nowrap bg-black/40 px-2.5 py-1 rounded-lg border border-amber-400/20">
                        <Lock className="w-3 h-3 text-amber-400" />
                        Blind Validation (Nama Submitter Disamarkan)
                    </div>
                </div>

                {/* Navigation Tabs */}
                <div className="border-b border-white/10 flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                    <button
                        onClick={() => setActiveTab('pending')}
                        className={`flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap ${
                            activeTab === 'pending'
                                ? 'bg-amber-400 text-black shadow-[0_0_15px_rgba(245,158,11,0.3)]'
                                : 'text-gray-400 hover:text-white hover:bg-white/5'
                        }`}
                    >
                        <Gavel className="w-4 h-4" />
                        <span>Panggilan Juri Aktif</span>
                        {pending_tasks.length > 0 && (
                            <span className={`px-1.5 py-0.5 rounded-full text-[10px] font-mono ${
                                activeTab === 'pending' ? 'bg-black text-amber-400' : 'bg-amber-400/20 text-amber-400'
                            }`}>
                                {pending_tasks.length}
                            </span>
                        )}
                    </button>

                    <button
                        onClick={() => setActiveTab('history')}
                        className={`flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap ${
                            activeTab === 'history'
                                ? 'bg-amber-400 text-black shadow-[0_0_15px_rgba(245,158,11,0.3)]'
                                : 'text-gray-400 hover:text-white hover:bg-white/5'
                        }`}
                    >
                        <Clock className="w-4 h-4" />
                        <span>Riwayat Suara Konsensus</span>
                        <span className="text-[10px] font-mono opacity-80">({juror_history.length})</span>
                    </button>

                    <button
                        onClick={() => setActiveTab('my_submissions')}
                        className={`flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap ${
                            activeTab === 'my_submissions'
                                ? 'bg-amber-400 text-black shadow-[0_0_15px_rgba(245,158,11,0.3)]'
                                : 'text-gray-400 hover:text-white hover:bg-white/5'
                        }`}
                    >
                        <ShieldCheck className="w-4 h-4" />
                        <span>Pengajuan Misi Saya</span>
                        <span className="text-[10px] font-mono opacity-80">({my_submissions.length})</span>
                    </button>
                </div>

                {/* Tab 1: Panggilan Juri Aktif */}
                {activeTab === 'pending' && (
                    <div className="space-y-3">
                        {pending_tasks.length === 0 ? (
                            <div className="p-8 rounded-2xl bg-[#11111a] border border-white/10 text-center space-y-3">
                                <div className="w-12 h-12 rounded-2xl bg-white/5 text-amber-400 flex items-center justify-center mx-auto border border-white/10">
                                    <Check className="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 className="font-bold text-sm text-white">Semua Panggilan Juri Selesai</h3>
                                    <p className="text-xs text-gray-400 max-w-md mx-auto mt-1">
                                        Saat ini tidak ada misi yang menunggu penilaian Anda. Sistem akan mengirim notifikasi WebPush seketika algoritma 5 juri memilih akun Anda.
                                    </p>
                                </div>
                            </div>
                        ) : (
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                {pending_tasks.map((task) => (
                                    <div
                                        key={task.id}
                                        className="p-4 rounded-2xl bg-[#12121c] border border-amber-400/30 hover:border-amber-400/60 transition-all shadow-lg flex flex-col justify-between space-y-3 relative group"
                                    >
                                        <div className="flex items-start justify-between gap-2">
                                            <div>
                                                <div className="flex items-center gap-2 mb-1">
                                                    <span className="text-[9px] font-mono font-bold px-2 py-0.5 rounded-md bg-amber-400/15 text-amber-300 border border-amber-400/30">
                                                        STAKE 50 PERAK
                                                    </span>
                                                    <span className="text-[9px] font-mono text-gray-400">
                                                        Tugas #{task.id.substring(0, 8)}
                                                    </span>
                                                </div>
                                                <h3 className="text-sm font-bold text-white group-hover:text-amber-300 transition-colors">
                                                    {task.verification?.mission_title}
                                                </h3>
                                            </div>
                                            <span className="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping flex-shrink-0" />
                                        </div>

                                        <p className="text-xs text-gray-400 line-clamp-2 leading-relaxed">
                                            {task.verification?.proof_description}
                                        </p>

                                        <div className="pt-2 border-t border-white/5 flex items-center justify-between text-xs">
                                            <div className="text-[11px] text-gray-400">
                                                <span>Submitter: </span>
                                                <span className="font-mono text-gray-200">
                                                    {task.verification?.masked_submitter || 'Anonim'}
                                                </span>
                                            </div>
                                            <Link
                                                href={route('verifications.show', task.verification_id)}
                                                className="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-black font-bold text-xs transition-all active:scale-95 shadow"
                                            >
                                                <Eye className="w-3.5 h-3.5" />
                                                <span>Validasi Misi</span>
                                            </Link>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                )}

                {/* Tab 2: Riwayat Suara Konsensus */}
                {activeTab === 'history' && (
                    <div className="space-y-3">
                        {juror_history.length === 0 ? (
                            <div className="p-8 rounded-2xl bg-[#11111a] border border-white/10 text-center text-xs text-gray-400">
                                Belum ada riwayat suara juri yang Anda berikan.
                            </div>
                        ) : (
                            <div className="space-y-2">
                                {juror_history.map((item) => {
                                    const isWin = item.reward_amount_silver > 0;
                                    const isSlashed = item.is_slashed;

                                    return (
                                        <div
                                            key={item.id}
                                            className="p-3.5 rounded-xl bg-[#11111a] border border-white/10 hover:border-white/20 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                                        >
                                            <div className="space-y-1">
                                                <div className="flex items-center gap-2">
                                                    <span className={`text-[10px] font-mono font-bold px-2 py-0.5 rounded ${
                                                        item.vote === 'VALID'
                                                            ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
                                                            : item.vote === 'REJECT'
                                                            ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30'
                                                            : 'bg-gray-500/20 text-gray-400'
                                                    }`}>
                                                        SUARA: {item.vote}
                                                    </span>
                                                    <span className="text-xs text-white font-semibold">
                                                        {item.verification?.mission_title}
                                                    </span>
                                                </div>
                                                <div className="text-[11px] text-gray-400 flex items-center gap-2">
                                                    <span>Status Konsensus: <strong className="text-gray-200">{item.verification?.status}</strong></span>
                                                    {item.voted_at && (
                                                        <span>• Memilih: {new Date(item.voted_at).toLocaleDateString('id-ID')}</span>
                                                    )}
                                                </div>
                                            </div>

                                            <div className="flex items-center justify-between sm:justify-end gap-3 pt-2 sm:pt-0 border-t sm:border-t-0 border-white/5">
                                                {isSlashed ? (
                                                    <div className="text-right">
                                                        <span className="text-[11px] font-mono font-bold text-rose-400 block">
                                                            HANGUS (SLASHED)
                                                        </span>
                                                        <span className="text-[10px] text-gray-500">-50 Perak Pasak</span>
                                                    </div>
                                                ) : isWin ? (
                                                    <div className="text-right">
                                                        <span className="text-[11px] font-mono font-bold text-emerald-400 block">
                                                            MENANG KONSENSUS
                                                        </span>
                                                        <span className="text-[10px] text-emerald-300/80">+{item.reward_amount_silver} Perak Dividen</span>
                                                    </div>
                                                ) : item.vote === 'PENDING' ? (
                                                    <span className="text-[11px] text-amber-400 font-mono">Belum Memilih</span>
                                                ) : (
                                                    <span className="text-[11px] text-gray-400 font-mono">Menunggu Konsensus</span>
                                                )}

                                                <Link
                                                    href={route('verifications.show', item.verification_id)}
                                                    className="px-2.5 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-gray-300 text-xs font-semibold"
                                                >
                                                    Detail
                                                </Link>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                    </div>
                )}

                {/* Tab 3: Pengajuan Misi Saya */}
                {activeTab === 'my_submissions' && (
                    <div className="space-y-3">
                        <div className="flex items-center justify-between">
                            <span className="text-xs text-gray-400">
                                Daftar bukti misi yang Anda kirimkan untuk penilaian konsensus juri
                            </span>
                            <button
                                onClick={handleOpenSubmitModal}
                                className="text-xs font-bold text-amber-400 hover:text-amber-300 flex items-center gap-1"
                            >
                                <PlusCircle className="w-3.5 h-3.5" />
                                Ajukan Bukti Baru
                            </button>
                        </div>

                        {my_submissions.length === 0 ? (
                            <div className="p-8 rounded-2xl bg-[#11111a] border border-white/10 text-center space-y-3">
                                <div className="w-12 h-12 rounded-2xl bg-white/5 text-gray-400 flex items-center justify-center mx-auto border border-white/10">
                                    <ShieldCheck className="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 className="font-bold text-sm text-white">Belum Ada Pengajuan Misi</h3>
                                    <p className="text-xs text-gray-400 max-w-sm mx-auto mt-1">
                                        Selesaikan misi penjelajah atau saudagar, lalu kirimkan bukti foto untuk divalidasi oleh 5 juri independen.
                                    </p>
                                </div>
                                <button
                                    onClick={handleOpenSubmitModal}
                                    className="px-4 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-black font-bold text-xs"
                                >
                                    Mulai Ajukan Sekarang
                                </button>
                            </div>
                        ) : (
                            <div className="space-y-2">
                                {my_submissions.map((sub) => {
                                    const isApproved = sub.status === 'CONSENSUS_APPROVED';
                                    const isRejected = sub.status === 'CONSENSUS_REJECTED';
                                    const isVoting = sub.status === 'IN_VOTING';

                                    return (
                                        <div
                                            key={sub.id}
                                            className="p-4 rounded-xl bg-[#11111a] border border-white/10 hover:border-white/20 transition-all flex flex-col md:flex-row md:items-center justify-between gap-3"
                                        >
                                            <div className="space-y-1">
                                                <div className="flex items-center gap-2">
                                                    <span className={`text-[10px] font-mono font-bold px-2 py-0.5 rounded ${
                                                        isApproved
                                                            ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
                                                            : isRejected
                                                            ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30'
                                                            : 'bg-amber-400/20 text-amber-400 border border-amber-400/30'
                                                    }`}>
                                                        {sub.status}
                                                    </span>
                                                    <h4 className="text-xs font-bold text-white">{sub.mission_title}</h4>
                                                </div>
                                                <p className="text-xs text-gray-400 line-clamp-1">{sub.proof_description}</p>
                                                <div className="text-[11px] text-gray-500 flex items-center gap-3">
                                                    <span>Hadiah: <strong className="text-amber-400 font-mono">+{sub.reward_silver} Perak</strong> & <strong className="text-amber-300 font-mono">+{sub.reward_gold} Emas</strong></span>
                                                    <span>• Diajukan: {new Date(sub.created_at).toLocaleDateString('id-ID')}</span>
                                                </div>
                                            </div>

                                            <div className="flex items-center justify-between md:justify-end gap-3 pt-2 md:pt-0 border-t md:border-t-0 border-white/5">
                                                <div className="text-right">
                                                    <span className="text-[11px] font-mono text-gray-300 block">
                                                        Suara: <strong className="text-emerald-400">{sub.valid_votes_count} Valid</strong> / <strong className="text-rose-400">{sub.reject_votes_count} Reject</strong>
                                                    </span>
                                                    <span className="text-[10px] text-gray-500 font-mono">
                                                        Total 5 Juri Independen
                                                    </span>
                                                </div>
                                                <Link
                                                    href={route('verifications.show', sub.id)}
                                                    className="px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-gray-300 text-xs font-semibold"
                                                >
                                                    Tinjau
                                                </Link>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                    </div>
                )}
            </main>

            {/* Modal: Ajukan Bukti Misi Baru */}
            {showSubmitModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
                    <div className="w-full max-w-lg rounded-2xl bg-[#12121c] border border-white/10 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                        <div className="p-4 border-b border-white/10 flex items-center justify-between bg-white/[0.02]">
                            <div className="flex items-center gap-2">
                                <Scale className="w-4 h-4 text-amber-400" />
                                <h3 className="font-bold text-sm text-white">Ajukan Bukti Misi Baru</h3>
                            </div>
                            <button
                                onClick={() => setShowSubmitModal(false)}
                                className="w-7 h-7 rounded-lg bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white flex items-center justify-center text-xs"
                            >
                                ✕
                            </button>
                        </div>

                        <form onSubmit={handleSubmitMissionProof} className="p-4 space-y-4 overflow-y-auto">
                            <div>
                                <label className="block text-xs font-bold text-gray-300 mb-1">
                                    Judul Misi / Aktivitas Ekspedisi
                                </label>
                                <input
                                    type="text"
                                    required
                                    value={data.mission_title}
                                    onChange={(e) => setData('mission_title', e.target.value)}
                                    placeholder="Contoh: Transaksi POS Merchant Pasar Bawah & Edukasi PitiCoin"
                                    className="w-full px-3 py-2 rounded-xl bg-black/40 border border-white/10 focus:border-amber-400 focus:outline-none text-xs text-white placeholder-gray-500"
                                />
                                {errors.mission_title && (
                                    <p className="text-rose-400 text-[11px] mt-1">{errors.mission_title}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-300 mb-1">
                                    Deskripsi & Narasi Pelaksanaan Misi
                                </label>
                                <textarea
                                    required
                                    rows={3}
                                    value={data.proof_description}
                                    onChange={(e) => setData('proof_description', e.target.value)}
                                    placeholder="Jelaskan secara detail waktu pelaksanaan, pihak yang terlibat, dan hasil nyata dari misi ini..."
                                    className="w-full px-3 py-2 rounded-xl bg-black/40 border border-white/10 focus:border-amber-400 focus:outline-none text-xs text-white placeholder-gray-500"
                                />
                                {errors.proof_description && (
                                    <p className="text-rose-400 text-[11px] mt-1">{errors.proof_description}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-300 mb-1">
                                    Unggah Foto Bukti Otentik (Maks. 5MB)
                                </label>
                                <div className="mt-1 flex justify-center px-4 pt-4 pb-4 border-2 border-dashed border-white/15 rounded-xl hover:border-amber-400/50 transition-colors bg-black/20">
                                    {imagePreview ? (
                                        <div className="space-y-2 text-center">
                                            <img
                                                src={imagePreview}
                                                alt="Preview"
                                                className="max-h-40 mx-auto rounded-lg object-cover border border-white/10"
                                            />
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    setImagePreview(null);
                                                    setData('proof_image', null);
                                                }}
                                                className="text-[11px] text-rose-400 hover:underline"
                                            >
                                                Ganti Gambar
                                            </button>
                                        </div>
                                    ) : (
                                        <div className="space-y-1 text-center">
                                            <Upload className="mx-auto h-8 w-8 text-gray-400" />
                                            <div className="flex text-xs text-gray-400">
                                                <label className="relative cursor-pointer rounded-md font-bold text-amber-400 hover:underline">
                                                    <span>Pilih Berkas Foto</span>
                                                    <input
                                                        type="file"
                                                        accept="image/*"
                                                        onChange={handleImageChange}
                                                        className="sr-only"
                                                    />
                                                </label>
                                                <p className="pl-1">atau seret ke sini</p>
                                            </div>
                                            <p className="text-[10px] text-gray-500">PNG, JPG, JPEG hingga 5MB</p>
                                        </div>
                                    )}
                                </div>
                                {errors.proof_image && (
                                    <p className="text-rose-400 text-[11px] mt-1">{errors.proof_image}</p>
                                )}
                            </div>

                            <div className="p-3 rounded-xl bg-amber-400/10 border border-amber-400/20 text-[11px] text-amber-300/90 leading-tight flex items-start gap-2">
                                <Lock className="w-4 h-4 flex-shrink-0 text-amber-400 mt-0.5" />
                                <div>
                                    <strong className="font-bold block text-white">Protokol Blind Consensus</strong>
                                    Identitas Anda akan disamarkan secara otomatis di depan 5 juri independen untuk memastikan penilaian yang murni obyektif.
                                </div>
                            </div>

                            <div className="pt-2 border-t border-white/10 flex items-center justify-end gap-2">
                                <button
                                    type="button"
                                    onClick={() => setShowSubmitModal(false)}
                                    className="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 text-xs font-semibold"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="px-5 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-black text-xs font-bold transition-all disabled:opacity-50 flex items-center gap-1.5"
                                >
                                    {processing ? 'Mengirim...' : 'Kirim Bukti Misi'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            <CookieConsentBanner />
        </div>
    );
}
