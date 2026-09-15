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
    ArrowLeft, 
    Lock, 
    Award, 
    FileText, 
    Image as ImageIcon,
    ZoomIn,
    HelpCircle
} from 'lucide-react';
import CookieConsentBanner from '@/Components/CookieConsentBanner';

interface VerificationShowProps {
    user: {
        id: string;
        name: string;
        role: string;
        is_jury_banned: boolean;
    };
    wallet: {
        silver_balance: number;
    };
    verification: {
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
        is_resolved: boolean;
        consensus_reached_at: string | null;
        created_at: string;
        submitter_name: string;
        is_blind: boolean;
    };
    my_assignment: {
        id: string;
        vote: 'PENDING' | 'VALID' | 'REJECT';
        stake_amount_silver: number;
        reward_amount_silver: number;
        is_slashed: boolean;
        voted_at: string | null;
        notes: string | null;
    } | null;
    is_submitter: boolean;
    flash?: {
        success?: string;
        error?: string;
        warning?: string;
    };
}

export default function VerificationShow({
    user,
    wallet,
    verification,
    my_assignment,
    is_submitter,
    flash,
}: VerificationShowProps) {
    const [zoomImage, setZoomImage] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        vote: '' as 'VALID' | 'REJECT' | '',
        notes: '',
    });

    const handleVote = (voteType: 'VALID' | 'REJECT') => {
        if (!confirm(`Apakah Anda yakin memilih "${voteType}"? Pasak sebesar 50 Koin Perak akan dipertaruhkan.`)) {
            return;
        }

        post(route('verifications.vote', {
            id: verification.id,
            vote: voteType,
            notes: data.notes,
        }));
    };

    const hasEnoughSilver = wallet.silver_balance >= 50;
    const isVotingClosed = verification.is_resolved;
    const hasVoted = my_assignment && my_assignment.vote !== 'PENDING';

    const consensusThreshold = 3;
    const totalVotesCast = verification.valid_votes_count + verification.reject_votes_count;

    return (
        <div className="min-h-screen bg-[#0a0a0f] text-gray-100 flex flex-col font-sans selection:bg-amber-400 selection:text-black">
            <Head title={`Validasi Misi: ${verification.mission_title} | PitiCoin.id`} />

            {/* Top Navigation */}
            <header className="sticky top-0 z-40 bg-[#0f0f17]/90 backdrop-blur-md border-b border-white/10 px-4 py-3">
                <div className="max-w-5xl mx-auto flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Link
                            href={route('verifications.index')}
                            className="w-9 h-9 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 flex items-center justify-center text-gray-300 hover:text-white transition-all active:scale-95"
                            title="Kembali ke Konsensus Juri"
                        >
                            <ArrowLeft className="w-5 h-5" />
                        </Link>
                        <div>
                            <div className="flex items-center gap-2">
                                <h1 className="text-sm sm:text-base font-bold text-white tracking-wide truncate max-w-xs sm:max-w-md">
                                    {verification.mission_title}
                                </h1>
                                <span className={`text-[10px] font-mono px-2 py-0.5 rounded-full font-bold uppercase ${
                                    verification.status === 'CONSENSUS_APPROVED'
                                        ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
                                        : verification.status === 'CONSENSUS_REJECTED'
                                        ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30'
                                        : 'bg-amber-400/10 text-amber-400 border border-amber-400/30'
                                }`}>
                                    {verification.status}
                                </span>
                            </div>
                            <span className="text-[11px] text-gray-400 font-mono">
                                Sesi ID #{verification.id.substring(0, 13)}...
                            </span>
                        </div>
                    </div>

                    <div className="text-right">
                        <span className="text-[10px] text-gray-400 block font-mono">Saldo Kas Perak</span>
                        <span className="text-xs font-bold text-amber-400 font-mono">
                            {wallet.silver_balance} Perak
                        </span>
                    </div>
                </div>
            </header>

            {/* Flash Banners */}
            <div className="max-w-5xl mx-auto w-full px-4 pt-4">
                {flash?.success && (
                    <div className="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center gap-2">
                        <CheckCircle className="w-4 h-4 flex-shrink-0" />
                        <span>{flash.success}</span>
                    </div>
                )}
                {flash?.warning && (
                    <div className="mb-4 p-3.5 rounded-xl bg-amber-500/15 border border-amber-500/40 text-amber-300 text-xs flex items-start gap-2.5">
                        <AlertTriangle className="w-5 h-5 flex-shrink-0 text-amber-400 mt-0.5" />
                        <span>{flash.warning}</span>
                    </div>
                )}
                {flash?.error && (
                    <div className="mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs flex items-center gap-2">
                        <XCircle className="w-4 h-4 flex-shrink-0" />
                        <span>{flash.error}</span>
                    </div>
                )}
            </div>

            {/* Main Content Area */}
            <main className="max-w-5xl mx-auto w-full px-4 py-4 space-y-6 flex-1">
                
                {/* Protocol Header & Blind Validator Banner */}
                <div className="p-4 rounded-2xl bg-gradient-to-br from-[#151522] to-[#0e0e17] border border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-lg">
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-amber-400/10 text-amber-400 flex items-center justify-center border border-amber-400/30 flex-shrink-0">
                            <Scale className="w-5 h-5" />
                        </div>
                        <div>
                            <div className="flex items-center gap-2">
                                <span className="text-xs text-gray-400">Pengaju Misi:</span>
                                <span className="text-xs font-mono font-bold text-white bg-white/5 px-2 py-0.5 rounded border border-white/10">
                                    {verification.submitter_name}
                                </span>
                                {verification.is_blind && (
                                    <span className="text-[10px] text-amber-400/90 font-mono flex items-center gap-1 bg-amber-400/10 px-1.5 py-0.5 rounded border border-amber-400/20">
                                        <Lock className="w-2.5 h-2.5" /> Blind Validation
                                    </span>
                                )}
                            </div>
                            <span className="text-[11px] text-gray-500 block mt-0.5">
                                Diajukan pada: {new Date(verification.created_at).toLocaleString('id-ID')}
                            </span>
                        </div>
                    </div>

                    <div className="flex items-center gap-3 text-xs">
                        <div className="p-2 px-3 rounded-xl bg-black/40 border border-white/10 text-center">
                            <span className="text-[10px] text-gray-400 block font-mono">Hadiah Perak</span>
                            <span className="text-xs font-bold text-amber-300 font-mono">
                                +{verification.reward_silver} Perak
                            </span>
                        </div>
                        <div className="p-2 px-3 rounded-xl bg-black/40 border border-white/10 text-center">
                            <span className="text-[10px] text-gray-400 block font-mono">Hadiah Emas</span>
                            <span className="text-xs font-bold text-amber-400 font-mono">
                                +{verification.reward_gold} Emas
                            </span>
                        </div>
                    </div>
                </div>

                {/* Proof Details Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {/* Left: Narrative Description */}
                    <div className="p-5 rounded-2xl bg-[#11111a] border border-white/10 space-y-3">
                        <div className="flex items-center gap-2 text-xs font-bold text-gray-300">
                            <FileText className="w-4 h-4 text-amber-400" />
                            <span>Deskripsi Bukti Ekspedisi</span>
                        </div>
                        <div className="p-4 rounded-xl bg-black/30 border border-white/5 text-xs text-gray-300 whitespace-pre-wrap leading-relaxed min-h-[140px]">
                            {verification.proof_description}
                        </div>
                        <div className="text-[11px] text-gray-500 italic">
                            * Teliti kesesuaian antara narasi misi dengan bukti visual di sebelah kanan.
                        </div>
                    </div>

                    {/* Right: Photographic Evidence */}
                    <div className="p-5 rounded-2xl bg-[#11111a] border border-white/10 space-y-3 flex flex-col justify-between">
                        <div className="flex items-center justify-between text-xs font-bold text-gray-300">
                            <div className="flex items-center gap-2">
                                <ImageIcon className="w-4 h-4 text-sky-400" />
                                <span>Foto Bukti Otentik</span>
                            </div>
                            {verification.proof_image_url && (
                                <button
                                    onClick={() => setZoomImage(true)}
                                    className="text-[11px] text-sky-400 hover:text-sky-300 flex items-center gap-1"
                                >
                                    <ZoomIn className="w-3.5 h-3.5" />
                                    Perbesar
                                </button>
                            )}
                        </div>

                        <div className="flex-1 flex items-center justify-center p-2 rounded-xl bg-black/40 border border-white/5 overflow-hidden min-h-[140px]">
                            {verification.proof_image_url ? (
                                <img
                                    src={verification.proof_image_url}
                                    alt="Bukti Misi"
                                    onClick={() => setZoomImage(true)}
                                    className="max-h-56 rounded-lg object-contain cursor-pointer hover:scale-105 transition-transform"
                                />
                            ) : (
                                <div className="text-center p-6 space-y-1 text-gray-500">
                                    <ImageIcon className="w-8 h-8 mx-auto opacity-40" />
                                    <p className="text-xs">Pengaju tidak menyertakan foto lampiran fisik.</p>
                                </div>
                            )}
                        </div>

                        <div className="text-[11px] text-gray-500">
                            Bukti visual disimpan pada klaster storage terenkripsi PitiCoin.
                        </div>
                    </div>
                </div>

                {/* 5-Juror Consensus Progress Gauge */}
                <div className="p-5 rounded-2xl bg-[#11111a] border border-white/10 space-y-4">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 className="text-xs font-bold text-white uppercase tracking-wider font-mono flex items-center gap-1.5">
                                <Gavel className="w-4 h-4 text-amber-400" />
                                Status Akumulasi Konsensus (Target: 3 dari 5 Juri)
                            </h3>
                            <p className="text-[11px] text-gray-400">
                                Ambang batas mayoritas mutlak 60% (3 suara sepadan) untuk mengesahkan atau menggugurkan misi.
                            </p>
                        </div>
                        <span className="text-xs font-mono text-gray-300 bg-white/5 px-2.5 py-1 rounded-lg border border-white/10 self-start sm:self-auto">
                            Total Suara Masuk: <strong className="text-white">{totalVotesCast} / 5</strong>
                        </span>
                    </div>

                    {/* Progress Bar */}
                    <div className="space-y-1.5">
                        <div className="h-3.5 w-full bg-black/60 rounded-full overflow-hidden flex border border-white/10 p-0.5">
                            <div
                                style={{ width: `${(verification.valid_votes_count / 5) * 100}%` }}
                                className="bg-emerald-500 h-full rounded-l-full transition-all duration-500"
                                title={`Suara Valid: ${verification.valid_votes_count}`}
                            />
                            <div
                                style={{ width: `${(verification.reject_votes_count / 5) * 100}%` }}
                                className="bg-rose-500 h-full rounded-r-full transition-all duration-500"
                                title={`Suara Reject: ${verification.reject_votes_count}`}
                            />
                        </div>
                        <div className="flex items-center justify-between text-xs font-mono">
                            <span className="text-emerald-400 flex items-center gap-1">
                                <CheckCircle className="w-3.5 h-3.5" />
                                {verification.valid_votes_count} Suara VALID
                            </span>
                            <span className="text-rose-400 flex items-center gap-1">
                                <XCircle className="w-3.5 h-3.5" />
                                {verification.reject_votes_count} Suara REJECT
                            </span>
                        </div>
                    </div>
                </div>

                {/* Staking & Voting Action Section for Jurors */}
                {my_assignment ? (
                    <div className="p-5 rounded-2xl bg-gradient-to-br from-[#171726] to-[#0f0f18] border border-amber-400/40 shadow-xl space-y-4">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-2">
                                <Scale className="w-5 h-5 text-amber-400" />
                                <h3 className="font-bold text-sm text-white">Panel Keputusan Juri Anda</h3>
                            </div>
                            <span className="text-xs font-mono font-bold px-2 py-0.5 rounded bg-amber-400/20 text-amber-300 border border-amber-400/40">
                                STAKE: 50 PERAK
                            </span>
                        </div>

                        {hasVoted ? (
                            <div className="p-4 rounded-xl bg-black/40 border border-white/10 space-y-2">
                                <div className="flex items-center justify-between">
                                    <span className="text-xs text-gray-300">Suara yang Anda Berikan:</span>
                                    <span className={`text-xs font-bold font-mono px-3 py-1 rounded-md ${
                                        my_assignment.vote === 'VALID'
                                            ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40'
                                            : 'bg-rose-500/20 text-rose-400 border border-rose-500/40'
                                    }`}>
                                        VOTE: {my_assignment.vote}
                                    </span>
                                </div>
                                <div className="text-[11px] text-gray-400 flex items-center justify-between pt-2 border-t border-white/5">
                                    <span>Waktu Pemberian Suara:</span>
                                    <span className="font-mono">
                                        {my_assignment.voted_at ? new Date(my_assignment.voted_at).toLocaleString('id-ID') : '-'}
                                    </span>
                                </div>
                                {my_assignment.reward_amount_silver > 0 && (
                                    <div className="p-2.5 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between">
                                        <span>🎉 Selamat! Anda Memenangkan Konsensus:</span>
                                        <strong className="font-mono font-bold">
                                            +50 Perak Pasak Dikembalikan & +25 Perak Dividen (+15 XP)
                                        </strong>
                                    </div>
                                )}
                                {my_assignment.is_slashed && (
                                    <div className="p-2.5 rounded-lg bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs flex items-center justify-between">
                                        <span>⚠️ Pasak Anda Hangus (Slashed):</span>
                                        <strong className="font-mono font-bold">
                                            -50 Perak (Kalah Konsensus / Pelanggaran Honeypot)
                                        </strong>
                                    </div>
                                )}
                            </div>
                        ) : isVotingClosed ? (
                            <div className="p-4 rounded-xl bg-black/40 border border-white/10 text-xs text-gray-400 text-center">
                                Sesi pemungutan suara untuk misi ini telah ditutup karena konsensus mayoritas telah tercapai.
                            </div>
                        ) : user.is_jury_banned ? (
                            <div className="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-xs text-rose-300">
                                Anda tidak dapat memberikan suara karena akun juri Anda sedang dibekukan akibat pelanggaran honeypot trap sebelumnya.
                            </div>
                        ) : !hasEnoughSilver ? (
                            <div className="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-xs text-amber-300 flex items-center justify-between">
                                <span>Saldo Perak Anda ({wallet.silver_balance} Perak) tidak mencukupi untuk pasak 50 Perak.</span>
                                <Link
                                    href={route('dashboard')}
                                    className="px-3 py-1 bg-amber-400 text-black font-bold rounded-lg text-xs"
                                >
                                    Isi Kas Perak
                                </Link>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-300 mb-1">
                                        Catatan / Alasan Putusan Anda (Opsional)
                                    </label>
                                    <textarea
                                        rows={2}
                                        value={data.notes}
                                        onChange={(e) => setData('notes', e.target.value)}
                                        placeholder="Tuliskan catatan teknis atau alasan verifikasi bukti ini..."
                                        className="w-full px-3 py-2 rounded-xl bg-black/40 border border-white/10 focus:border-amber-400 focus:outline-none text-xs text-white placeholder-gray-500"
                                    />
                                </div>

                                <div className="p-3 rounded-xl bg-amber-400/10 border border-amber-400/20 text-[11px] text-amber-300/90 leading-tight flex items-start gap-2">
                                    <AlertTriangle className="w-4 h-4 flex-shrink-0 text-amber-400 mt-0.5" />
                                    <div>
                                        <strong className="font-bold block text-white">
                                            Peringatan Integritas Pasak & Honeypot Trap:
                                        </strong>
                                        Setiap suara membutuhkan <strong>pasak staking 50 Koin Perak</strong>. Jika pilihan Anda sejalan dengan konsensus mayoritas, pasak dikembalikan beserta bonus <strong>+25 Perak Dividen</strong> & <strong>+15 XP</strong>. Namun, jika Anda menyetujui misi palsu jebakan sistem, pasak Anda hangus dan reputasi dipotong 100 PTS!
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-3 pt-2">
                                    <button
                                        type="button"
                                        disabled={processing}
                                        onClick={() => handleVote('VALID')}
                                        className="py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-950/40 border border-emerald-400/40 transition-all disabled:opacity-50"
                                    >
                                        <CheckCircle className="w-4 h-4" />
                                        <span>SETUJU (VALID)</span>
                                    </button>

                                    <button
                                        type="button"
                                        disabled={processing}
                                        onClick={() => handleVote('REJECT')}
                                        className="py-3 px-4 rounded-xl bg-rose-600 hover:bg-rose-500 active:scale-95 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-rose-950/40 border border-rose-400/40 transition-all disabled:opacity-50"
                                    >
                                        <XCircle className="w-4 h-4" />
                                        <span>TOLAK (REJECT)</span>
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                ) : is_submitter ? (
                    <div className="p-5 rounded-2xl bg-[#11111a] border border-white/10 text-center space-y-2">
                        <ShieldCheck className="w-8 h-8 text-amber-400 mx-auto" />
                        <h4 className="font-bold text-sm text-white">Anda Adalah Pembuat Pengajuan Misi Ini</h4>
                        <p className="text-xs text-gray-400 max-w-md mx-auto">
                            Misi Anda saat ini sedang dinilai secara independen oleh 5 juri acak. Anda akan menerima notifikasi otomatis begitu konsensus tercapai.
                        </p>
                    </div>
                ) : null}
            </main>

            {/* Modal Zoom Gambar */}
            {zoomImage && verification.proof_image_url && (
                <div
                    onClick={() => setZoomImage(false)}
                    className="fixed inset-0 z-50 p-4 bg-black/90 backdrop-blur-md flex items-center justify-center cursor-pointer"
                >
                    <div className="relative max-w-4xl max-h-[90vh]">
                        <img
                            src={verification.proof_image_url}
                            alt="Zoom Bukti"
                            className="max-w-full max-h-[85vh] rounded-xl object-contain shadow-2xl border border-white/20"
                        />
                        <button
                            onClick={() => setZoomImage(false)}
                            className="absolute top-2 right-2 w-8 h-8 rounded-full bg-black/70 text-white flex items-center justify-center font-bold text-sm"
                        >
                            ✕
                        </button>
                    </div>
                </div>
            )}

            <CookieConsentBanner />
        </div>
    );
}
