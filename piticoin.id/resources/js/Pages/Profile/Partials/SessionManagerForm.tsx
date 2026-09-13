import React, { useState, useRef } from 'react';
import { useForm } from '@inertiajs/react';
import { Shield, Monitor, Smartphone, Globe, LogOut, CheckCircle2, AlertTriangle, Key } from 'lucide-react';
import Modal from '@/Components/Modal';

interface SessionItem {
    id: string;
    ip_address: string;
    is_current_device: boolean;
    last_active: string;
    device_type: string;
    platform: string;
    browser: string;
}

interface SessionManagerProps {
    sessions: SessionItem[];
}

export default function SessionManagerForm({ sessions }: SessionManagerProps) {
    const [confirmingRevoke, setConfirmingRevoke] = useState(false);
    const passwordInputRef = useRef<HTMLInputElement>(null);

    const { data, setData, post, processing, reset, errors, clearErrors } = useForm({
        password: '',
    });

    const handleRevokeSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('profile.sessions.revoke-others'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => passwordInputRef.current?.focus(),
        });
    };

    const closeModal = () => {
        setConfirmingRevoke(false);
        clearErrors();
        reset();
    };

    const otherSessionsCount = sessions.filter((s) => !s.is_current_device).length;

    return (
        <div className="glass-panel p-6 sm:p-8 rounded-2xl border border-white/10 space-y-5">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-4">
                <div>
                    <h3 className="text-lg font-bold text-white flex items-center gap-2">
                        <Shield size={20} className="text-[#ffd700]" />
                        <span>Keamanan Sesi & Manajemen Perangkat Aktif</span>
                    </h3>
                    <p className="text-xs text-gray-400 mt-1">
                        Pantau riwayat sesi masuk dan putuskan akses dari perangkat lain yang tidak Anda kenali.
                    </p>
                </div>

                {otherSessionsCount > 0 && (
                    <button
                        type="button"
                        onClick={() => setConfirmingRevoke(true)}
                        className="px-3.5 py-2 rounded-xl bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 text-red-400 hover:text-red-300 text-xs font-semibold inline-flex items-center gap-1.5 transition-all self-start sm:self-auto"
                    >
                        <LogOut size={14} />
                        <span>Keluarkan Perangkat Lain ({otherSessionsCount})</span>
                    </button>
                )}
            </div>

            {/* Sessions List */}
            <div className="space-y-3">
                {sessions.length === 0 ? (
                    <div className="p-4 rounded-xl bg-black/40 border border-white/10 text-center text-xs text-gray-400">
                        Tidak ada riwayat sesi aktif yang tersimpan di database.
                    </div>
                ) : (
                    sessions.map((session) => (
                        <div
                            key={session.id}
                            className={`p-4 rounded-xl border transition-all flex items-center justify-between gap-4 ${
                                session.is_current_device
                                    ? 'bg-yellow-500/5 border-yellow-500/30'
                                    : 'bg-black/30 border-white/5 hover:border-white/15'
                            }`}
                        >
                            <div className="flex items-center gap-3.5">
                                <div
                                    className={`p-2.5 rounded-xl border ${
                                        session.is_current_device
                                            ? 'bg-yellow-500/10 border-yellow-500/30 text-[#ffd700]'
                                            : 'bg-white/5 border-white/10 text-gray-400'
                                    }`}
                                >
                                    {session.device_type === 'Mobile' ? (
                                        <Smartphone size={20} />
                                    ) : (
                                        <Monitor size={20} />
                                    )}
                                </div>
                                <div className="space-y-0.5">
                                    <div className="flex items-center gap-2">
                                        <span className="font-semibold text-sm text-white">
                                            {session.platform} • {session.browser}
                                        </span>
                                        {session.is_current_device && (
                                            <span className="px-2 py-0.5 rounded-md bg-yellow-500/20 text-[#ffd700] text-[10px] font-bold border border-yellow-500/30">
                                                Perangkat Ini
                                            </span>
                                        )}
                                    </div>
                                    <div className="text-xs text-gray-400 font-mono">
                                        IP: {session.ip_address || '127.0.0.1'} • Aktif: {session.last_active}
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))
                )}
            </div>

            {/* Revoke Modal */}
            <Modal show={confirmingRevoke} onClose={closeModal}>
                <form onSubmit={handleRevokeSubmit} className="p-6 bg-[#1A1A1A] text-[#e5e2e1] border border-white/10 rounded-2xl space-y-4">
                    <div className="flex items-center gap-2.5 text-red-400">
                        <AlertTriangle size={20} />
                        <h4 className="text-base font-bold text-white">
                            Keluarkan Dari Semua Perangkat Lain?
                        </h4>
                    </div>

                    <p className="text-xs text-gray-300 leading-relaxed">
                        Tindakan ini akan mengakhiri seluruh sesi login aktif di browser atau ponsel lain. Silakan masukkan kata sandi akun Anda untuk konfirmasi keamanan.
                    </p>

                    <div className="space-y-1.5">
                        <label className="text-xs font-semibold text-gray-300">
                            Kata Sandi Saat Ini
                        </label>
                        <input
                            type="password"
                            ref={passwordInputRef}
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            placeholder="••••••••"
                            required
                            className="w-full px-4 py-2.5 rounded-xl bg-black/50 border border-white/20 text-white text-sm focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500"
                        />
                        {errors.password && (
                            <p className="text-xs text-red-400">{errors.password}</p>
                        )}
                    </div>

                    <div className="flex justify-end gap-2 pt-2 border-t border-white/10">
                        <button
                            type="button"
                            onClick={closeModal}
                            className="px-4 py-2 rounded-xl text-xs font-medium text-gray-400 hover:text-white"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="px-4 py-2 rounded-xl text-xs font-bold bg-red-600 hover:bg-red-500 text-white transition-all shadow-md"
                        >
                            {processing ? 'Memproses...' : 'Keluarkan Perangkat Lain'}
                        </button>
                    </div>
                </form>
            </Modal>
        </div>
    );
}
