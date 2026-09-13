import React from 'react';
import { useForm } from '@inertiajs/react';
import { User, Phone, Lock, Sparkles, CheckCircle2, Star } from 'lucide-react';

interface UserData {
    id: string;
    name: string;
    username: string | null;
    whatsapp: string;
    role: string;
    faction: string;
    license_type: string;
    level: number;
    experience_points: number;
    rating: number;
    total_reviews: number;
}

interface UpdateProfileProps {
    user: UserData;
    status?: string;
}

export default function UpdateProfileInformationForm({ user, status }: UpdateProfileProps) {
    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
        name: user.name,
        username: user.username || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        patch(route('profile.update'), {
            preserveScroll: true,
        });
    };

    return (
        <div className="glass-panel p-6 sm:p-8 rounded-2xl border border-white/10 space-y-6">
            <div className="border-b border-white/10 pb-4">
                <h3 className="text-lg font-bold text-white flex items-center gap-2">
                    <User size={20} className="text-[#ffd700]" />
                    <span>Identitas Profil Saudagar</span>
                </h3>
                <p className="text-xs text-gray-400 mt-1">
                    Perbarui nama tampilan dan username unik Anda di dunia ekspedisi Gunung Mas.
                </p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-5">
                {/* Name */}
                <div className="space-y-1.5">
                    <label htmlFor="name" className="text-xs font-semibold text-gray-300">
                        Nama Lengkap / Gelar Saudagar
                    </label>
                    <input
                        id="name"
                        type="text"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                        className="w-full px-4 py-2.5 rounded-xl bg-black/40 border border-white/15 text-white text-sm focus:outline-none focus:border-[#ffd700] focus:ring-1 focus:ring-[#ffd700] transition-all"
                    />
                    {errors.name && <p className="text-xs text-red-400">{errors.name}</p>}
                </div>

                {/* Username */}
                <div className="space-y-1.5">
                    <label htmlFor="username" className="text-xs font-semibold text-gray-300">
                        Username Unik (@)
                    </label>
                    <div className="relative">
                        <span className="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-mono">@</span>
                        <input
                            id="username"
                            type="text"
                            value={data.username}
                            onChange={(e) => setData('username', e.target.value.toLowerCase().replace(/[^a-z0-9._]/g, ''))}
                            placeholder="nama_saudagar"
                            className="w-full pl-8 pr-4 py-2.5 rounded-xl bg-black/40 border border-white/15 text-white text-sm font-mono focus:outline-none focus:border-[#ffd700] focus:ring-1 focus:ring-[#ffd700] transition-all"
                        />
                    </div>
                    {errors.username && <p className="text-xs text-red-400">{errors.username}</p>}
                </div>

                {/* WhatsApp (Immutable / Readonly Binding) */}
                <div className="space-y-1.5">
                    <div className="flex items-center justify-between">
                        <label className="text-xs font-semibold text-gray-300 flex items-center gap-1.5">
                            <Phone size={14} className="text-emerald-400" />
                            <span>Nomor WhatsApp Terikat</span>
                        </label>
                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-[10px] font-semibold">
                            <CheckCircle2 size={11} />
                            Terverifikasi
                        </span>
                    </div>
                    <div className="relative">
                        <input
                            type="text"
                            value={user.whatsapp}
                            disabled
                            className="w-full px-4 py-2.5 rounded-xl bg-black/60 border border-white/10 text-gray-400 text-sm font-mono cursor-not-allowed"
                        />
                        <Lock size={14} className="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-500" />
                    </div>
                    <p className="text-[11px] text-gray-500">
                        Nomor WhatsApp terikat langsung dengan identitas SSO Mandeh.ID Anda dan tidak dapat diubah sembarangan demi keamanan finansial.
                    </p>
                </div>

                {/* Submit & Status Message */}
                <div className="flex items-center justify-between pt-2 border-t border-white/5">
                    {recentlySuccessful ? (
                        <span className="text-xs text-emerald-400 flex items-center gap-1">
                            <CheckCircle2 size={14} />
                            Perubahan tersimpan!
                        </span>
                    ) : <span />}

                    <button
                        type="submit"
                        disabled={processing}
                        className="px-5 py-2.5 rounded-xl text-xs font-bold bg-gradient-to-r from-[#ffd700] to-[#e6be00] text-black hover:opacity-90 transition-all shadow-md bloom-gold"
                    >
                        {processing ? 'Menyimpan...' : 'Simpan Perubahan'}
                    </button>
                </div>
            </form>
        </div>
    );
}
