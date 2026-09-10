import { useState, FormEventHandler } from 'react';
import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Lock, Phone, ArrowRight, Eye, EyeOff } from 'lucide-react';

export default function Login({ status }: { status?: string }) {
    const [showPassword, setShowPassword] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        whatsapp: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Masuk Dasbor - PITICOIN.ID" />

            {/* Header Titles */}
            <div className="text-center mb-6">
                <h2 className="font-headline-md text-2xl font-bold text-white mb-2" style={{ fontFamily: "'Playfair Display', serif" }}>
                    Selamat Datang Kembali
                </h2>
                <p className="font-body-md text-sm text-on-surface-variant">
                    Masuk untuk melanjutkan ke Ekspedisi Gunung Mas
                </p>
            </div>

            {status && (
                <div className="mb-6 p-4 rounded-xl border bg-secondary-container/20 border-secondary/50 text-secondary text-xs font-bold">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="flex flex-col gap-4">
                {/* Input: WhatsApp */}
                <div className="flex flex-col gap-2">
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
                            className="bg-transparent border-none w-full text-on-surface font-body-md text-sm py-3.5 px-4 focus:ring-0 placeholder-on-surface-variant/30"
                            autoComplete="username"
                            required
                            onChange={(e) => setData('whatsapp', e.target.value)}
                            placeholder="Contoh: 08123456782"
                        />
                    </div>
                    <InputError message={errors.whatsapp} className="mt-1 text-xs text-error font-semibold" />
                </div>

                {/* Input: Password */}
                <div className="flex flex-col gap-2">
                    <label htmlFor="password" className="font-label-sm text-[11px] text-primary font-bold tracking-widest uppercase flex items-center gap-1.5">
                        <Lock size={14} className="text-primary-container" />
                        Password
                    </label>
                    <div className="relative flex items-center bg-[#0c0c0c] border border-glass-stroke rounded-lg focus-within:border-primary-container focus-within:shadow-[0_0_12px_rgba(255,215,0,0.15)] transition-all duration-300 shadow-[inset_2px_2px_5px_rgba(0,0,0,0.7),inset_-1px_-1px_3px_rgba(255,255,255,0.05)] overflow-hidden">
                        <input
                            id="password"
                            type={showPassword ? 'text' : 'password'}
                            name="password"
                            value={data.password}
                            className="bg-transparent border-none w-full text-on-surface font-body-md text-sm py-3.5 px-4 pr-11 focus:ring-0 placeholder-on-surface-variant/30"
                            autoComplete="current-password"
                            required
                            onChange={(e) => setData('password', e.target.value)}
                            placeholder="Masukkan password Anda"
                        />
                        <button
                            type="button"
                            tabIndex={-1}
                            onClick={() => setShowPassword(!showPassword)}
                            className="absolute right-3.5 text-on-surface-variant/50 hover:text-primary transition-colors focus:outline-none"
                        >
                            {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                        </button>
                    </div>
                    <InputError message={errors.password} className="mt-1 text-xs text-error font-semibold" />
                </div>

                {/* Remember Me */}
                <div className="flex items-center justify-between my-2">
                    <label className="flex items-center gap-2 cursor-pointer">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                        />
                        <span className="font-body-md text-xs text-on-surface-variant">Ingat saya</span>
                    </label>
                </div>

                {/* Submit Action */}
                <button
                    type="submit"
                    disabled={processing}
                    className="w-full relative overflow-hidden group bg-gradient-to-r from-[#e5a93c] via-[#ffd700] to-[#b38329] text-[#121212] font-label-md font-bold text-sm tracking-wider uppercase py-4 rounded-xl shadow-[0_4px_20px_rgba(255,215,0,0.3)] hover:shadow-[0_6px_25px_rgba(255,215,0,0.5)] transition-all duration-300 active:scale-[0.98] disabled:opacity-50 flex items-center justify-center gap-2 mt-2"
                >
                    <span className="relative z-10 flex items-center gap-2">
                        {processing ? 'Memproses...' : 'Masuk Dasbor'}
                        {!processing && <ArrowRight size={16} className="group-hover:translate-x-1 transition-transform" />}
                    </span>
                    <div className="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300" />
                </button>
            </form>

            {/* Footer Referral / Register */}
            <div className="text-center mt-6">
                <p className="font-body-md text-xs text-on-surface-variant">
                    Belum terdaftar sebagai Saudagar?{' '}
                    <Link
                        href={route('register')}
                        className="text-white font-bold hover:text-primary-container transition-colors underline underline-offset-4"
                    >
                        Daftar Akun Baru
                    </Link>
                </p>
            </div>
        </GuestLayout>
    );
}
