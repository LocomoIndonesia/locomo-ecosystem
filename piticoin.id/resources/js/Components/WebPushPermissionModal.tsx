import React, { useState, useEffect } from 'react';
import { Bell, ShieldAlert, CheckCircle2, AlertCircle, ArrowRight, Zap, RefreshCw, X } from 'lucide-react';
import {
    isPushNotificationSupported,
    isSecureContextEnvironment,
    getNotificationPermission,
    subscribeUserToPush,
    getCurrentPushSubscription
} from '../Services/webPush';

interface WebPushPermissionModalProps {
    forceOpen?: boolean;
    onClose?: () => void;
}

export default function WebPushPermissionModal({ forceOpen = false, onClose }: WebPushPermissionModalProps) {
    const [isOpen, setIsOpen] = useState(false);
    const [isSecure, setIsSecure] = useState(true);
    const [isSupported, setIsSupported] = useState(true);
    const [permission, setPermission] = useState<NotificationPermission | 'unsupported'>('default');
    const [loading, setLoading] = useState(false);
    const [statusMessage, setStatusMessage] = useState<{ text: string; type: 'success' | 'error' | 'warning' } | null>(null);

    useEffect(() => {
        const secure = isSecureContextEnvironment();
        const supported = isPushNotificationSupported();
        const perm = getNotificationPermission();

        setIsSecure(secure);
        setIsSupported(supported);
        setPermission(perm);

        // Jika dipaksa buka oleh pengguna melalui tombol
        if (forceOpen) {
            setIsOpen(true);
            return;
        }

        // Cek apakah sudah pernah ditolak atau disetujui sebelumnya
        const dismissedUntil = localStorage.getItem('piticoin_webpush_dismissed_until');
        const isDismissedRecently = dismissedUntil && Number(dismissedUntil) > Date.now();

        // Munculkan otomatis jika izin masih 'default' dan belum di-dismiss baru-baru ini
        if (perm === 'default' && !isDismissedRecently) {
            // Beri sedikit jeda 1 detik setelah dashboard termuat agar transisi mulus
            const timer = setTimeout(() => {
                setIsOpen(true);
            }, 800);
            return () => clearTimeout(timer);
        }
    }, [forceOpen]);

    const handleAllow = async () => {
        setLoading(true);
        setStatusMessage(null);

        try {
            const res = await subscribeUserToPush();
            if (res.success) {
                setStatusMessage({
                    text: '✅ Izin notifikasi berhasil diaktifkan! Anda kini terhubung ke real-time alert.',
                    type: 'success'
                });
                setPermission('granted');
                setTimeout(() => {
                    handleClose();
                }, 1500);
            } else {
                setPermission(getNotificationPermission());
                setStatusMessage({
                    text: res.message || 'Gagal mengaktifkan notifikasi.',
                    type: 'error'
                });
            }
        } catch (err: any) {
            setStatusMessage({
                text: err.message || 'Terjadi kesalahan sistem saat meminta izin.',
                type: 'error'
            });
        } finally {
            setLoading(false);
        }
    };

    const handleDismiss = () => {
        // Ingat pilihan lewati selama 2 jam
        localStorage.setItem('piticoin_webpush_dismissed_until', String(Date.now() + 2 * 60 * 60 * 1000));
        handleClose();
    };

    const handleClose = () => {
        setIsOpen(false);
        if (onClose) onClose();
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md animate-fade-in">
            <div className="relative w-full max-w-lg overflow-hidden rounded-2xl bg-[#13131a] border border-amber-500/30 shadow-2xl shadow-amber-500/10 text-white">
                
                {/* Glowing Top Accent Bar */}
                <div className="h-1.5 w-full bg-gradient-to-r from-amber-600 via-amber-400 to-yellow-300 shadow-[0_0_15px_rgba(255,191,0,0.5)]" />

                {/* Close Button */}
                <button
                    onClick={handleClose}
                    className="absolute top-4 right-4 p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-white/10 transition-colors"
                >
                    <X size={18} />
                </button>

                <div className="p-6 md:p-8 space-y-6">

                    {/* Icon & Title Header */}
                    <div className="flex items-center gap-4">
                        <div className="p-3.5 rounded-2xl bg-gradient-to-br from-amber-500/20 to-amber-600/5 border border-amber-500/40 text-amber-400 shadow-inner">
                            <Bell size={28} className="animate-pulse" />
                        </div>
                        <div>
                            <div className="flex items-center gap-2">
                                <span className="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                    Pemberitahuan Ekspedisi
                                </span>
                            </div>
                            <h2 className="text-xl font-bold text-white mt-1">
                                Aktifkan Notifikasi Peramban
                            </h2>
                        </div>
                    </div>

                    {/* Description Body */}
                    <p className="text-sm text-gray-300 leading-relaxed">
                        Agar pengalaman ekspedisi dan transaksi di <strong className="text-amber-400">PitiCoin.id</strong> optimal, aktifkan notifikasi peramban untuk menerima peringatan seketika saat:
                    </p>

                    {/* Feature Highlight Pills */}
                    <div className="grid gap-2.5 text-xs">
                        <div className="p-3 rounded-xl bg-white/[0.03] border border-white/5 flex items-start gap-3">
                            <Zap size={16} className="text-amber-400 shrink-0 mt-0.5" />
                            <div>
                                <span className="font-semibold text-gray-200">Tawaran Lelang Terlampaui (Outbid Alert)</span>
                                <p className="text-gray-400 text-[11px] mt-0.5">Ketahui segera ketika tawaran Koin Emas Anda dilampaui penawar lain.</p>
                            </div>
                        </div>

                        <div className="p-3 rounded-xl bg-white/[0.03] border border-white/5 flex items-start gap-3">
                            <Bell size={16} className="text-amber-400 shrink-0 mt-0.5" />
                            <div>
                                <span className="font-semibold text-gray-200">Panggilan Tugas Juri Misi & Hadiah</span>
                                <p className="text-gray-400 text-[11px] mt-0.5">Dapatkan Koin Perak saat terpilih sebagai salah satu dari 5 juri verifikator.</p>
                            </div>
                        </div>
                    </div>

                    {/* HTTP Non-Secure Context Detection Banner */}
                    {!isSecure && (
                        <div className="p-3.5 rounded-xl bg-amber-950/40 border border-amber-500/40 text-amber-200 text-xs space-y-1.5 flex items-start gap-2.5">
                            <ShieldAlert size={18} className="text-amber-400 shrink-0 mt-0.5" />
                            <div className="leading-relaxed">
                                <div className="font-bold text-amber-300">Peringatan Protokol Browser:</div>
                                <p>
                                    Browser mewajibkan <strong>Secure Context</strong> untuk WebPush. Jika peramban memblokir izin di <code className="bg-black/40 px-1 py-0.5 rounded text-amber-300">piticoin.localhost</code>, silakan akses melalui <a href="http://localhost:8008/dashboard" className="underline font-bold text-white hover:text-amber-300">http://localhost:8008</a> atau aktifkan SSL/HTTPS.
                                </p>
                            </div>
                        </div>
                    )}

                    {/* Status Alert Message */}
                    {statusMessage && (
                        <div className={`p-3.5 rounded-xl border text-xs leading-relaxed flex items-start gap-2.5 ${
                            statusMessage.type === 'success'
                                ? 'bg-emerald-950/50 border-emerald-500/50 text-emerald-300'
                                : 'bg-red-950/50 border-red-500/50 text-red-300'
                        }`}>
                            {statusMessage.type === 'success' ? <CheckCircle2 size={16} className="shrink-0 mt-0.5" /> : <AlertCircle size={16} className="shrink-0 mt-0.5" />}
                            <span>{statusMessage.text}</span>
                        </div>
                    )}

                    {/* Action Buttons */}
                    <div className="pt-2 flex flex-col sm:flex-row items-center justify-end gap-3">
                        <button
                            type="button"
                            onClick={handleDismiss}
                            className="w-full sm:w-auto px-4 py-2.5 rounded-xl text-xs font-semibold text-gray-400 hover:text-white hover:bg-white/5 transition-colors text-center"
                        >
                            Lewati untuk Sementara
                        </button>
                        <button
                            type="button"
                            onClick={handleAllow}
                            disabled={loading || permission === 'granted'}
                            className="w-full sm:w-auto px-6 py-2.5 rounded-xl text-xs font-bold bg-gradient-to-r from-amber-500 to-yellow-400 hover:from-amber-400 hover:to-yellow-300 text-black shadow-lg shadow-amber-500/25 flex items-center justify-center gap-2 transition-all duration-200 disabled:opacity-50"
                        >
                            {loading ? (
                                <>
                                    <RefreshCw size={14} className="animate-spin" />
                                    <span>Mengaktifkan...</span>
                                </>
                            ) : permission === 'granted' ? (
                                <>
                                    <CheckCircle2 size={14} />
                                    <span>Sudah Diaktifkan</span>
                                </>
                            ) : (
                                <>
                                    <span>Izinkan Notifikasi</span>
                                    <ArrowRight size={14} />
                                </>
                            )}
                        </button>
                    </div>

                </div>

            </div>
        </div>
    );
}
