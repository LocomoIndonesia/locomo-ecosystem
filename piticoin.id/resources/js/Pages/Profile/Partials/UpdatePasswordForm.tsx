import React, { useRef } from 'react';
import { useForm } from '@inertiajs/react';
import { KeyRound, CheckCircle2 } from 'lucide-react';

export default function UpdatePasswordForm() {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);

    const {
        data,
        setData,
        errors,
        put,
        reset,
        processing,
        recentlySuccessful,
    } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const updatePassword = (e: React.FormEvent) => {
        e.preventDefault();

        put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => reset(),
            onError: (errs) => {
                if (errs.password) {
                    reset('password', 'password_confirmation');
                    passwordInput.current?.focus();
                }
                if (errs.current_password) {
                    reset('current_password');
                    currentPasswordInput.current?.focus();
                }
            },
        });
    };

    return (
        <div className="glass-panel p-6 sm:p-8 rounded-2xl border border-white/10 space-y-6">
            <div className="border-b border-white/10 pb-4">
                <h3 className="text-lg font-bold text-white flex items-center gap-2">
                    <KeyRound size={20} className="text-[#ffd700]" />
                    <span>Perbarui Kata Sandi</span>
                </h3>
                <p className="text-xs text-gray-400 mt-1">
                    Pastikan akun Anda menggunakan kombinasi kata sandi yang panjang dan aman.
                </p>
            </div>

            <form onSubmit={updatePassword} className="space-y-4 max-w-xl">
                {/* Current Password */}
                <div className="space-y-1.5">
                    <label htmlFor="current_password" className="text-xs font-semibold text-gray-300">
                        Kata Sandi Saat Ini
                    </label>
                    <input
                        id="current_password"
                        ref={currentPasswordInput}
                        value={data.current_password}
                        onChange={(e) => setData('current_password', e.target.value)}
                        type="password"
                        autoComplete="current-password"
                        className="w-full px-4 py-2.5 rounded-xl bg-black/40 border border-white/15 text-white text-sm focus:outline-none focus:border-[#ffd700] focus:ring-1 focus:ring-[#ffd700] transition-all"
                    />
                    {errors.current_password && (
                        <p className="text-xs text-red-400">{errors.current_password}</p>
                    )}
                </div>

                {/* New Password */}
                <div className="space-y-1.5">
                    <label htmlFor="password" className="text-xs font-semibold text-gray-300">
                        Kata Sandi Baru
                    </label>
                    <input
                        id="password"
                        ref={passwordInput}
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        type="password"
                        autoComplete="new-password"
                        className="w-full px-4 py-2.5 rounded-xl bg-black/40 border border-white/15 text-white text-sm focus:outline-none focus:border-[#ffd700] focus:ring-1 focus:ring-[#ffd700] transition-all"
                    />
                    {errors.password && (
                        <p className="text-xs text-red-400">{errors.password}</p>
                    )}
                </div>

                {/* Confirm Password */}
                <div className="space-y-1.5">
                    <label htmlFor="password_confirmation" className="text-xs font-semibold text-gray-300">
                        Konfirmasi Kata Sandi Baru
                    </label>
                    <input
                        id="password_confirmation"
                        value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        type="password"
                        autoComplete="new-password"
                        className="w-full px-4 py-2.5 rounded-xl bg-black/40 border border-white/15 text-white text-sm focus:outline-none focus:border-[#ffd700] focus:ring-1 focus:ring-[#ffd700] transition-all"
                    />
                    {errors.password_confirmation && (
                        <p className="text-xs text-red-400">{errors.password_confirmation}</p>
                    )}
                </div>

                {/* Submit & Status */}
                <div className="flex items-center justify-between pt-2 border-t border-white/5">
                    {recentlySuccessful ? (
                        <span className="text-xs text-emerald-400 flex items-center gap-1">
                            <CheckCircle2 size={14} />
                            Kata sandi berhasil diganti!
                        </span>
                    ) : <span />}

                    <button
                        type="submit"
                        disabled={processing}
                        className="px-5 py-2.5 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 border border-white/20 text-white transition-all shadow-md"
                    >
                        {processing ? 'Menyimpan...' : 'Perbarui Kata Sandi'}
                    </button>
                </div>
            </form>
        </div>
    );
}
