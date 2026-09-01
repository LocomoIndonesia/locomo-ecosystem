import InputError from '@/Components/InputError';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { Mail, ArrowRight } from 'lucide-react';

export default function ForgotPassword({ status }: { status?: string }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('password.email'));
    };

    return (
        <GuestLayout>
            <Head title="Lupa Password" />

            <div className="text-center mb-6">
                <h2 className="font-headline-md text-2xl font-bold text-white mb-2" style={{ fontFamily: "'Playfair Display', serif" }}>
                    Lupa Password
                </h2>
                <p className="font-body-md text-xs text-on-surface-variant leading-relaxed">
                    Masukkan alamat email Anda untuk mendapatkan tautan pemulihan kata sandi baru.
                </p>
            </div>

            {status && (
                <div className="mb-6 p-4 rounded-xl border bg-secondary-container/20 border-secondary/50 text-secondary text-xs font-bold">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="flex flex-col gap-4">
                <div className="flex flex-col gap-1.5">
                    <label htmlFor="email" className="font-label-sm text-[11px] text-primary font-bold tracking-widest uppercase flex items-center gap-1.5">
                        <Mail size={14} className="text-primary-container" />
                        Alamat Email
                    </label>
                    <div className="relative flex items-center bg-[#0c0c0c] border border-glass-stroke rounded-lg focus-within:border-primary-container focus-within:shadow-[0_0_12px_rgba(255,215,0,0.15)] transition-all duration-300 shadow-[inset_2px_2px_5px_rgba(0,0,0,0.7),inset_-1px_-1px_3px_rgba(255,255,255,0.05)] overflow-hidden">
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            className="bg-transparent border-none w-full text-on-surface font-body-md text-sm py-3.5 px-4 focus:ring-0 placeholder-on-surface-variant/30"
                            required
                            onChange={(e) => setData('email', e.target.value)}
                            placeholder="koordinat@email.com"
                        />
                    </div>
                    <InputError message={errors.email} className="mt-1 text-xs text-error font-semibold" />
                </div>

                <button
                    type="submit"
                    className="w-full bg-primary-container text-obsidian-surface font-label-sm text-xs font-bold uppercase tracking-widest py-4 rounded-xl shadow-[0_0_15px_rgba(255,215,0,0.3)] hover:shadow-[0_0_25px_rgba(255,215,0,0.5)] hover:scale-[1.02] active:scale-95 transition-all duration-300 flex justify-center items-center gap-2 border border-transparent hover:border-white/20 disabled:opacity-50 disabled:cursor-not-allowed mt-2"
                    disabled={processing}
                >
                    <span>KIRIM TAUTAN RESET</span>
                    <ArrowRight size={16} />
                </button>

                <div className="text-center mt-4">
                    <Link href={route('login')} className="text-xs text-on-surface-variant hover:text-white underline font-bold transition-colors">
                        Kembali ke Halaman Masuk
                    </Link>
                </div>
            </form>
        </GuestLayout>
    );
}
