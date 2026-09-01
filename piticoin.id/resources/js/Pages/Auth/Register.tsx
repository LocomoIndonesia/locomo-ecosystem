import { FormEventHandler } from 'react';
import InputError from '@/Components/InputError';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Lock, Phone, User, KeyRound, ArrowRight } from 'lucide-react';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        whatsapp: '',
        referral_code: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
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
                            className="bg-transparent border-none w-full text-on-surface font-body-md text-sm py-3 px-4 focus:ring-0 placeholder-on-surface-variant/30"
                            autoComplete="new-password"
                            required
                            onChange={(e) => setData('password', e.target.value)}
                            placeholder="Minimal 8 karakter"
                        />
                    </div>
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
                            type="password"
                            name="password_confirmation"
                            value={data.password_confirmation}
                            className="bg-transparent border-none w-full text-on-surface font-body-md text-sm py-3 px-4 focus:ring-0 placeholder-on-surface-variant/30"
                            autoComplete="new-password"
                            required
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            placeholder="Ulangi password Anda"
                        />
                    </div>
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
