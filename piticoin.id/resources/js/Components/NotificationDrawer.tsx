import React, { useState, useEffect } from 'react';
import { Bell, BellOff, CheckCircle2, AlertCircle, Send, X, ExternalLink, ShieldCheck, Zap } from 'lucide-react';
import {
    isPushNotificationSupported,
    getNotificationPermission,
    getCurrentPushSubscription,
    subscribeUserToPush,
    unsubscribeUserFromPush,
    sendTestWebPushNotification
} from '../Services/webPush';

interface NotificationDrawerProps {
    isOpen: boolean;
    onClose: () => void;
}

export default function NotificationDrawer({ isOpen, onClose }: NotificationDrawerProps) {
    const [supported, setSupported] = useState(false);
    const [permission, setPermission] = useState<NotificationPermission | 'unsupported'>('default');
    const [isSubscribed, setIsSubscribed] = useState(false);
    const [loading, setLoading] = useState(false);
    const [statusMessage, setStatusMessage] = useState<{ text: string; type: 'success' | 'error' | 'info' } | null>(null);

    useEffect(() => {
        const isSupp = isPushNotificationSupported();
        setSupported(isSupp);
        if (isSupp) {
            setPermission(getNotificationPermission());
            checkCurrentSubscription();
        }
    }, [isOpen]);

    const checkCurrentSubscription = async () => {
        try {
            const sub = await getCurrentPushSubscription();
            setIsSubscribed(!!sub);
        } catch (err) {
            console.error('Error checking push subscription:', err);
        }
    };

    const handleToggleSubscription = async () => {
        setLoading(true);
        setStatusMessage(null);

        try {
            if (isSubscribed) {
                const res = await unsubscribeUserFromPush();
                if (res.success) {
                    setIsSubscribed(false);
                    setStatusMessage({ text: 'Notifikasi peramban dinonaktifkan.', type: 'info' });
                } else {
                    setStatusMessage({ text: res.message, type: 'error' });
                }
            } else {
                const res = await subscribeUserToPush();
                if (res.success) {
                    setIsSubscribed(true);
                    setPermission(Notification.permission);
                    setStatusMessage({ text: '✅ Notifikasi peramban berhasil diaktifkan!', type: 'success' });
                } else {
                    setPermission(Notification.permission);
                    setStatusMessage({ text: res.message, type: 'error' });
                }
            }
        } catch (err: any) {
            setStatusMessage({ text: err.message || 'Gagal memproses permintaan.', type: 'error' });
        } finally {
            setLoading(false);
        }
    };

    const handleSendTestPush = async () => {
        setLoading(true);
        setStatusMessage(null);

        try {
            const res = await sendTestWebPushNotification();
            if (res.success) {
                setStatusMessage({ text: '🚀 Notifikasi uji coba telah dikirim ke perangkat Anda!', type: 'success' });
            } else {
                setStatusMessage({ text: res.message, type: 'error' });
            }
        } catch (err: any) {
            setStatusMessage({ text: err.message || 'Gagal mengirim push test.', type: 'error' });
        } finally {
            setLoading(false);
        }
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 overflow-hidden">
            {/* Backdrop */}
            <div 
                className="absolute inset-0 bg-black/75 backdrop-blur-sm transition-opacity"
                onClick={onClose}
            />

            <div className="fixed inset-y-0 right-0 max-w-full flex pl-10">
                <div className="w-screen max-w-md bg-[#121217] border-l border-amber-500/20 text-white shadow-2xl flex flex-col">
                    
                    {/* Drawer Header */}
                    <div className="p-5 border-b border-white/10 flex items-center justify-between bg-gradient-to-r from-[#181822] to-[#121217]">
                        <div className="flex items-center gap-3">
                            <div className="p-2 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400">
                                <Bell size={20} />
                            </div>
                            <div>
                                <h2 className="text-base font-bold text-amber-300">Pusat Notifikasi & WebPush</h2>
                                <p className="text-xs text-gray-400">Peringatan Real-Time Ekspedisi PitiCoin</p>
                            </div>
                        </div>
                        <button 
                            onClick={onClose}
                            className="p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-white/10 transition-colors"
                        >
                            <X size={18} />
                        </button>
                    </div>

                    {/* Drawer Body */}
                    <div className="flex-1 overflow-y-auto p-5 space-y-5">

                        {/* Status Message Toast */}
                        {statusMessage && (
                            <div className={`p-3.5 rounded-xl border text-xs leading-relaxed flex items-start gap-2.5 ${
                                statusMessage.type === 'success' 
                                    ? 'bg-emerald-950/40 border-emerald-500/40 text-emerald-300' 
                                    : statusMessage.type === 'error'
                                    ? 'bg-red-950/40 border-red-500/40 text-red-300'
                                    : 'bg-amber-950/30 border-amber-500/30 text-amber-300'
                            }`}>
                                {statusMessage.type === 'success' ? <CheckCircle2 size={16} className="shrink-0 mt-0.5" /> : <AlertCircle size={16} className="shrink-0 mt-0.5" />}
                                <span>{statusMessage.text}</span>
                            </div>
                        )}

                        {/* WebPush Subscription Card */}
                        <div className="p-4 rounded-xl bg-gradient-to-b from-[#1a1a24] to-[#14141c] border border-amber-500/25 space-y-4">
                            <div className="flex items-start justify-between gap-3">
                                <div>
                                    <div className="flex items-center gap-2">
                                        <h3 className="text-sm font-semibold text-white">Notifikasi Peramban (VAPID)</h3>
                                        {isSubscribed ? (
                                            <span className="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                                Aktif
                                            </span>
                                        ) : (
                                            <span className="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-gray-700/50 text-gray-400 border border-white/10">
                                                Nonaktif
                                            </span>
                                        )}
                                    </div>
                                    <p className="text-xs text-gray-400 mt-1 leading-relaxed">
                                        Dapatkan notifikasi instan langsung ke perangkat Anda saat terjadi peristiwa penting pada ekspedisi Gunung Mas.
                                    </p>
                                </div>
                            </div>

                            {!supported ? (
                                <div className="p-3 rounded-lg bg-yellow-950/30 border border-yellow-500/30 text-xs text-yellow-300">
                                    Browser Anda saat ini tidak mendukung Web Push Notifications standar.
                                </div>
                            ) : permission === 'denied' ? (
                                <div className="p-3 rounded-lg bg-red-950/30 border border-red-500/30 text-xs text-red-300 space-y-1">
                                    <div className="font-semibold">Izin Notifikasi Diblokir</div>
                                    <p>Anda telah memblokir notifikasi di pengaturan browser. Harap reset izin situs untuk mengaktifkan kembali.</p>
                                </div>
                            ) : (
                                <div className="pt-2 border-t border-white/10 flex items-center justify-between">
                                    <span className="text-xs text-gray-300 font-medium">Status Langganan Perangkat:</span>
                                    <button
                                        onClick={handleToggleSubscription}
                                        disabled={loading}
                                        className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 ${
                                            isSubscribed
                                                ? 'bg-red-500/20 text-red-300 border border-red-500/30 hover:bg-red-500/30'
                                                : 'bg-amber-500 text-black hover:bg-amber-400 font-bold shadow-lg shadow-amber-500/20'
                                        }`}
                                    >
                                        {loading ? (
                                            'Memproses...'
                                        ) : isSubscribed ? (
                                            <>
                                                <BellOff size={13} />
                                                <span>Matikan</span>
                                            </>
                                        ) : (
                                            <>
                                                <Bell size={13} />
                                                <span>Aktifkan Sekarang</span>
                                            </>
                                        )}
                                    </button>
                                </div>
                            )}

                            {/* Tombol Test Push */}
                            {isSubscribed && (
                                <div className="pt-3 border-t border-white/10 flex justify-end">
                                    <button
                                        onClick={handleSendTestPush}
                                        disabled={loading}
                                        className="text-xs text-amber-400 hover:text-amber-300 font-medium flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/5 border border-amber-500/20 hover:bg-white/10 transition-colors"
                                    >
                                        <Send size={12} />
                                        <span>Kirim Notifikasi Uji Coba</span>
                                    </button>
                                </div>
                            )}
                        </div>

                        {/* Use Cases Info */}
                        <div className="space-y-3">
                            <h4 className="text-xs font-semibold uppercase tracking-wider text-gray-400">
                                Skenario Peringatan Real-Time
                            </h4>

                            <div className="grid gap-2.5 text-xs">
                                <div className="p-3 rounded-lg bg-white/[0.03] border border-white/5 flex items-start gap-3">
                                    <Zap size={15} className="text-amber-400 shrink-0 mt-0.5" />
                                    <div>
                                        <div className="font-semibold text-gray-200">Bursa Lelang (Outbid Alert)</div>
                                        <p className="text-gray-400 text-[11px] mt-0.5">Pemberitahuan seketika saat tawaran Koin Emas/Perak Anda dilampaui penawar lain.</p>
                                    </div>
                                </div>

                                <div className="p-3 rounded-lg bg-white/[0.03] border border-white/5 flex items-start gap-3">
                                    <ShieldCheck size={15} className="text-amber-400 shrink-0 mt-0.5" />
                                    <div>
                                        <div className="font-semibold text-gray-200">Juri Misi 5 Verifikator</div>
                                        <p className="text-gray-400 text-[11px] mt-0.5">Panggilan tugas verifikasi acak berhadiah Koin Perak saat sistem memilih Anda sebagai juri.</p>
                                    </div>
                                </div>

                                <div className="p-3 rounded-lg bg-white/[0.03] border border-white/5 flex items-start gap-3">
                                    <Bell size={15} className="text-amber-400 shrink-0 mt-0.5" />
                                    <div>
                                        <div className="font-semibold text-gray-200">Angpao Per Jam & Harta Karun</div>
                                        <p className="text-gray-400 text-[11px] mt-0.5">Pengingat ketika jendela waktu klaim Koin Emas/Perak gratis siap dipanen.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {/* Drawer Footer */}
                    <div className="p-4 border-t border-white/10 bg-[#0d0d12] flex items-center justify-between text-[11px] text-gray-500">
                        <span>Standar VAPID RFC 8292</span>
                        <span className="text-amber-500/70 font-mono">PitiCoin Engine v2.0</span>
                    </div>

                </div>
            </div>
        </div>
    );
}
