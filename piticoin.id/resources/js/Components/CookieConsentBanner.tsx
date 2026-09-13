import React, { useState, useEffect } from 'react';
import { ShieldCheck, Cookie, ChevronRight, X } from 'lucide-react';
import { Link } from '@inertiajs/react';

export default function CookieConsentBanner() {
    const [isVisible, setIsVisible] = useState(false);

    useEffect(() => {
        const consent = localStorage.getItem('piticoin_cookie_consent');
        if (!consent) {
            // Small delay for smooth entry animation
            const timer = setTimeout(() => setIsVisible(true), 800);
            return () => clearTimeout(timer);
        }
    }, []);

    const handleAcceptAll = () => {
        localStorage.setItem('piticoin_cookie_consent', 'all');
        setIsVisible(false);
    };

    const handleAcceptEssential = () => {
        localStorage.setItem('piticoin_cookie_consent', 'essential');
        setIsVisible(false);
    };

    if (!isVisible) return null;

    return (
        <div className="fixed bottom-4 left-4 right-4 md:left-auto md:right-6 md:max-w-md z-50 animate-fade-in">
            <div className="glass-panel p-5 rounded-2xl border border-yellow-500/30 shadow-2xl bg-[#1A1A1A]/95 text-[#e5e2e1] backdrop-blur-xl">
                <div className="flex items-start gap-3">
                    <div className="p-2.5 rounded-xl bg-yellow-500/10 border border-yellow-500/20 text-[#ffd700] shrink-0">
                        <Cookie size={22} />
                    </div>
                    <div className="space-y-1.5 flex-1">
                        <div className="flex items-center justify-between">
                            <h4 className="text-sm font-semibold text-[#ffd700] flex items-center gap-1.5">
                                <ShieldCheck size={16} />
                                <span>Privasi & Preferensi Kuki</span>
                            </h4>
                            <button
                                onClick={handleAcceptEssential}
                                className="text-gray-400 hover:text-white transition-colors"
                                title="Tutup dan gunakan hanya esensial"
                            >
                                <X size={16} />
                            </button>
                        </div>
                        <p className="text-xs text-gray-300 leading-relaxed">
                            Kami menggunakan kuki esensial untuk menjaga sesi autentikasi dan keamanan transaksi Anda di ekosistem PitiCoin.id, serta kuki preferensi untuk menyimpan status audio SFX dan tampilan visual.
                        </p>
                    </div>
                </div>

                <div className="mt-4 pt-3 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-2.5">
                    <a
                        href="/privacy#cookies"
                        className="text-[11px] text-gray-400 hover:text-[#ffd700] underline flex items-center gap-0.5 transition-colors"
                    >
                        <span>Kebijakan Privasi</span>
                        <ChevronRight size={12} />
                    </a>

                    <div className="flex items-center gap-2 w-full sm:w-auto justify-end">
                        <button
                            type="button"
                            onClick={handleAcceptEssential}
                            className="px-3 py-1.5 rounded-lg text-xs font-medium border border-white/20 hover:border-white/40 text-gray-300 hover:text-white transition-all w-full sm:w-auto text-center"
                        >
                            Hanya Esensial
                        </button>
                        <button
                            type="button"
                            onClick={handleAcceptAll}
                            className="px-4 py-1.5 rounded-lg text-xs font-semibold bg-gradient-to-r from-[#ffd700] to-[#e6be00] text-black hover:opacity-90 transition-all shadow-md bloom-gold w-full sm:w-auto text-center"
                        >
                            Setujui Semua
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
