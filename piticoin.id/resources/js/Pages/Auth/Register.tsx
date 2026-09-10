import { FormEventHandler, useState, useMemo } from 'react';
import InputError from '@/Components/InputError';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Lock, Phone, User, KeyRound, ArrowRight, Eye, EyeOff, ShieldCheck, Check, X } from 'lucide-react';

export default function Register() {
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        whatsapp: '',
        referral_code: '',
        password: '',
        password_confirmation: '',
    });

    // Password security strength checklist
    const passwordChecks = useMemo(() => {
        const pwd = data.password || '';
        return {
            length: pwd.length >= 8,
            hasLettersAndNumbers: /[a-zA-Z]/.test(pwd) && /[0-9]/.test(pwd),
            mixed: /[a-z]/.test(pwd) && /[A-Z]/.test(pwd),
            symbol: /[^a-zA-Z0-9]/.test(pwd),
        };
    }, [data.password]);

    const strengthScore = useMemo(() => {
        return Object.values(passwordChecks).filter(Boolean).length;
    }, [passwordChecks]);

    const strengthText = useMemo(() => {
        if (!data.password) return { label: '', color: 'bg-transparent', text: '' };
        if (strengthScore <= 1) return { label: 'Sangat Lemah', color: 'bg-red-500', text: 'text-red-400' };
        if (strengthScore === 2) return { label: 'Cukup', color: 'bg-amber-500', text: 'text-amber-400' };
        if (strengthScore === 3) return { label: 'Kuat (Standar Saudagar)', color: 'bg-yellow-400', text: 'text-yellow-300' };
        return { label: 'Maksimal (Super Aman)', color: 'bg-emerald-400', text: 'text-emerald-400' };
    }, [data.password, strengthScore]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            preserveScroll: true,
        });
    };

    return (
        <GuestLayout>
            <Head title="Daftar Saudagar Baru" />

            {/* Header Titles */}
            <div className="text-center mb-6">
                <h2 className="font-headline-md text-2xl font-bold text-white mb-2" style={{ fontFamily: "'Playfair Display', serif" }}>
                    Daftar Saudagar Baru
                </h2>
                <p className="font-body-md text-xs text-on-surface-variant">
                    Bergabung ke dalam jaringan kemitraan dagang piticoin.id
                </p>
            </div>

            <form onSubmit={submit} className="flex flex-col gap-4">
                {/* Input: Nama Lengkap */}
                <div className="flex flex-col gap-1.5">
                    <label htmlFor="name" className="font-label-sm text-[11px] text-primary font-bold tracking-widest uppercase flex items-center gap-1.5">
                        <User size={14} className="text-primary-container" />
                        Nama Lengkap
                    </label>
                    <div className="relative flex items-center bg-[#0c0c0c] border border-glass-stroke rounded-lg focus-within:border-primary-container focus-within:shadow-[0_0_12px_rgba(255,215,0,0.15)] transition-all duration-300 shadow-[inset_2px_2px_5px_rgba(0,0,0,0.7),inset_-1px_-1px_3px_rgba(255,255,255,0.05)] overflow-hidden">
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value={data.name}
                            className="bg-transparent border-none w-full text-on-surface font-body-md text-sm py-3 px-4 focus:ring-0 placeholder-on-surface-variant/30"
                            autoComplete="name"
                            required
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder="Contoh: Buyung Merchant"
                        />
                    </div>
                    <InputError message={errors.name} className="mt-1 text-xs text-error font-semibold" />
                </div>

                {/* Input: Nomor WhatsApp */}
                <div className="flex flex-col gap-1.5">
                    <label htmlFor="whatsapp" className="font-label-sm text-[11px] text-primary font-bold tracking-widest uppercase flex items-center gap-1.5">
                        <Phone size={14} className="text-primary-container" />
                        Nomor WhatsApp
                    </label>
                    <div className="relative flex items-center bg-[#0c0c0c] border border-glass-stroke rounded-lg focus-within:border-primary-container focus-within:shadow-[0_0_12px_rgba(255,215,0,0.15)] transition-all duration-300 shadow-[inset_2px_2px_5px_rgba(0,0,0,0.7),inset_-1px_-1px_3px_rgba(255,255,255,0.05)] overflow-hidden">
                        <input
                            id="whatsapp"
                            type="text"
                            name="whatsapp"
                            value={data.whatsapp}
                            className="bg-transparent border-none w-full text-on-surface font-body-md text-sm py-3 px-4 focus:ring-0 placeholder-on-surface-variant/30"
                            required
                            onChange={(e) => setData('whatsapp', e.target.value)}
                            placeholder="Contoh: 08123456782"
                        />
                    </div>
                    <InputError message={errors.whatsapp} className="mt-1 text-xs text-error font-semibold" />
                </div>

                {/* Input: Kode Referral */}
                <div className="flex flex-col gap-1.5">
                    <label htmlFor="referral_code" className="font-label-sm text-[11px] text-primary font-bold tracking-widest uppercase flex items-center gap-1.5">
                        <KeyRound size={14} className="text-primary-container" />
                        Kode Referral (WhatsApp Upline - Opsional)
                    </label>
                    <div className="relative flex items-center bg-[#0c0c0c] border border-glass-stroke rounded-lg focus-within:border-primary-container focus-within:shadow-[0_0_12px_rgba(255,215,0,0.15)] transition-all duration-300 shadow-[inset_2px_2px_5px_rgba(0,0,0,0.7),inset_-1px_-1px_3px_rgba(255,255,255,0.05)] overflow-hidden">
                        <input
                            id="referral_code"
                            type="text"
                            name="referral_code"
                            value={data.referral_code}
                            className="bg-transparent border-none w-full text-on-surface font-body-md text-sm py-3 px-4 focus:ring-0 placeholder-on-surface-variant/30"
                            onChange={(e) => setData('referral_code', e.target.value)}
                            placeholder="Masukkan nomor WhatsApp upline"
                        />
                    </div>
                    <InputError message={errors.referral_code} className="mt-1 text-xs text-error font-semibold" />
                </div>

                {/* Input: Password */}
                <div className="flex flex-col gap-1.5">
                    <div className="flex items-center justify-between">
                        <label htmlFor="password" className="font-label-sm text-[11px] text-primary font-bold tracking-widest uppercase flex items-center gap-1.5">
                            <Lock size={14} className="text-primary-container" />
                            Password Keamanan
                        </label>
                        {data.password && (
                            <span className={`text-[10px] font-semibold tracking-wider ${strengthText.text}`}>
                                {strengthText.label}
                            </span>
                        )}
                    </div>
                    <div className="relative flex items-center bg-[#0c0c0c] border border-glass-stroke rounded-lg focus-within:border-primary-container focus-within:shadow-[0_0_12px_rgba(255,215,0,0.15)] transition-all duration-300 shadow-[inset_2px_2px_5px_rgba(0,0,0,0.7),inset_-1px_-1px_3px_rgba(255,255,255,0.05)] overflow-hidden">
                        <input
                            id="password"
                            type={showPassword ? 'text' : 'password'}
                            name="password"
                            value={data.password}
                            className="bg-transparent border-none w-full text-on-surface font-body-md text-sm py-3 pl-4 pr-12 focus:ring-0 placeholder-on-surface-variant/30"
                            autoComplete="new-password"
                            required
                            onChange={(e) => setData('password', e.target.value)}
                            placeholder="Minimal 8 karakter (kombinasi huruf, angka, simbol)"
                        />
                        <button
                            type="button"
                            onClick={() => setShowPassword(!showPassword)}
                            className="absolute right-3 p-1.5 text-on-surface-variant/60 hover:text-primary-container transition-colors focus:outline-none"
                            aria-label={showPassword ? 'Sembunyikan password' : 'Lihat password'}
                            title={showPassword ? 'Sembunyikan password' : 'Lihat password'}
                        >
                            {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                        </button>
                    </div>

                    {/* Password Strength Meter & Security Standards */}
                    {data.password && (
                        <div className="mt-1 p-2.5 rounded-lg bg-[#141414]/80 border border-white/5 space-y-2">
                            {/* Strength Progress Bar */}
                            <div className="w-full bg-white/10 h-1.5 rounded-full overflow-hidden flex gap-1">
                                {[1, 2, 3, 4].map((level) => (
                                    <div
                                        key={level}
                                        className={`h-full flex-1 transition-all duration-300 rounded-full ${
                                            strengthScore >= level ? strengthText.color : 'bg-white/5'
                                        }`}
                                    />
                                ))}
                            </div>

                            {/* Security Requirements Checklist */}
                            <div className="grid grid-cols-2 gap-1.5 pt-1 text-[10px]">
                                <div className={`flex items-center gap-1.5 ${passwordChecks.length ? 'text-emerald-400 font-semibold' : 'text-on-surface-variant/50'}`}>
                                    {passwordChecks.length ? <Check size={12} className="stroke-[3]" /> : <X size={12} />}
                                    <span>Min. 8 karakter (Wajib)</span>
                                </div>
                                <div className={`flex items-center gap-1.5 ${passwordChecks.hasLettersAndNumbers ? 'text-emerald-400 font-semibold' : 'text-on-surface-variant/50'}`}>
                                    {passwordChecks.hasLettersAndNumbers ? <Check size={12} className="stroke-[3]" /> : <X size={12} />}
                                    <span>Huruf & angka (Wajib)</span>
                                </div>
                                <div className={`flex items-center gap-1.5 ${passwordChecks.mixed ? 'text-emerald-400' : 'text-on-surface-variant/50'}`}>
                                    {passwordChecks.mixed ? <Check size={12} className="stroke-[3]" /> : <X size={12} />}
                                    <span>Huruf besar & kecil</span>
                                </div>
                                <div className={`flex items-center gap-1.5 ${passwordChecks.symbol ? 'text-emerald-400' : 'text-on-surface-variant/50'}`}>
                                    {passwordChecks.symbol ? <Check size={12} className="stroke-[3]" /> : <X size={12} />}
                                    <span>Simbol khusus (Opsional)</span>
                                </div>
                            </div>
                        </div>
                    )}
                    <InputError message={errors.password} className="mt-1 text-xs text-error font-semibold" />
                </div>

                {/* Input: Konfirmasi Password */}
                <div className="flex flex-col gap-1.5">
                    <label htmlFor="password_confirmation" className="font-label-sm text-[11px] text-primary font-bold tracking-widest uppercase flex items-center gap-1.5">
                        <Lock size={14} className="text-primary-container" />
                        Konfirmasi Password
                    </label>
                    <div className="relative flex items-center bg-[#0c0c0c] border border-glass-stroke rounded-lg focus-within:border-primary-container focus-within:shadow-[0_0_12px_rgba(255,215,0,0.15)] transition-all duration-300 shadow-[inset_2px_2px_5px_rgba(0,0,0,0.7),inset_-1px_-1px_3px_rgba(255,255,255,0.05)] overflow-hidden">
                        <input
                            id="password_confirmation"
                            type={showConfirmPassword ? 'text' : 'password'}
                            name="password_confirmation"
                            value={data.password_confirmation}
                            className="bg-transparent border-none w-full text-on-surface font-body-md text-sm py-3 pl-4 pr-12 focus:ring-0 placeholder-on-surface-variant/30"
                            autoComplete="new-password"
                            required
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            placeholder="Ulangi password Anda"
                        />
                        <button
                            type="button"
                            onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                            className="absolute right-3 p-1.5 text-on-surface-variant/60 hover:text-primary-container transition-colors focus:outline-none"
                            aria-label={showConfirmPassword ? 'Sembunyikan password' : 'Lihat password'}
                            title={showConfirmPassword ? 'Sembunyikan password' : 'Lihat password'}
                        >
                            {showConfirmPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                        </button>
                    </div>
                    {data.password_confirmation && (
                        data.password === data.password_confirmation ? (
                            <p className="text-[10px] text-emerald-400 font-medium mt-1 flex items-center gap-1">
                                <Check size={12} className="stroke-[3]" /> Password dan konfirmasi cocok.
                            </p>
                        ) : (
                            <p className="text-[10px] text-amber-400 font-medium mt-1 flex items-center gap-1">
                                <X size={12} /> Konfirmasi password belum sama dengan password di atas.
                            </p>
                        )
                    )}
                    <InputError message={errors.password_confirmation} className="mt-1 text-xs text-error font-semibold" />
                </div>

                {/* Submit button */}
                <button
                    type="submit"
                    className="w-full bg-primary-container text-obsidian-surface font-label-sm text-xs font-bold uppercase tracking-widest py-4 rounded-xl shadow-[0_0_15px_rgba(255,215,0,0.3)] hover:shadow-[0_0_25px_rgba(255,215,0,0.5)] hover:scale-[1.02] active:scale-95 transition-all duration-300 flex justify-center items-center gap-2 border border-transparent hover:border-white/20 disabled:opacity-50 disabled:cursor-not-allowed mt-4"
                    disabled={processing}
                >
                    <span>DAFTAR SEBAGAI SAUDAGAR</span>
                    <ArrowRight size={16} />
                </button>

                {/* Footer link */}
                <div className="text-center mt-4 text-xs font-body-md space-y-1">
                    <p className="text-on-surface-variant">Sudah memiliki akun?</p>
                    <Link href={route('login')} className="text-white hover:text-primary-container underline transition-colors font-bold">
                        Masuk Ke Dasbor
                    </Link>
                </div>
            </form>
        </GuestLayout>
    );
}
