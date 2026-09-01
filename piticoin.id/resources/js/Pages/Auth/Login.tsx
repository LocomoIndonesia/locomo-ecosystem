import { useEffect, FormEventHandler } from 'react';
import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Lock, Phone, ArrowRight } from 'lucide-react';

export default function Login({ status }: { status?: string }) {
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
            <Head title="Masuk Dasbor" />

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
                            type="password"
                            name="password"
                            value={data.password}
                            className="bg-transparent border-none w-full text-on-surface font-body-md text-sm py-3.5 px-4 focus:ring-0 placeholder-on-surface-variant/30"
                            autoComplete="current-password"
                            required
                            onChange={(e) => setData('password', e.target.value)}
                            placeholder="Masukkan password Anda"
                        />
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
                            className="rounded border-glass-stroke bg-[#0c0c0c] text-primary-container focus:ring-0 focus:ring-offset-0"
                        />
                        <span className="font-body-md text-xs text-on-surface-variant">
                            Ingat saya
                        </span>
                    </label>
                </div>

                {/* Submit button */}
                <button
                    type="submit"
                    className="w-full bg-primary-container text-obsidian-surface font-label-sm text-xs font-bold uppercase tracking-widest py-4 rounded-xl shadow-[0_0_15px_rgba(255,215,0,0.3)] hover:shadow-[0_0_25px_rgba(255,215,0,0.5)] hover:scale-[1.02] active:scale-95 transition-all duration-300 flex justify-center items-center gap-2 border border-transparent hover:border-white/20 disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled={processing}
                >
                    <span>MASUK DASBOR</span>
                    <ArrowRight size={16} />
                </button>

                {/* Footer link */}
                <div className="text-center mt-4 text-xs font-body-md space-y-1">
                    <p className="text-on-surface-variant">Belum terdaftar sebagai Saudagar?</p>
                    <Link href={route('register')} className="text-white hover:text-primary-container underline transition-colors font-bold">
                        Daftar Akun Baru
                    </Link>
                </div>
            </form>
        </GuestLayout>
    );
}
