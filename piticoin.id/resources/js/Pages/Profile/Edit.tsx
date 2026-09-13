import React from 'react';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Shield,
    Star,
    Award,
    Sparkles,
    CheckCircle2,
    Anchor,
    Compass,
    Store,
    Users,
    ChevronRight,
} from 'lucide-react';
import AvatarCustomizer from './Partials/AvatarCustomizer';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
import AudioPreferencesForm from './Partials/AudioPreferencesForm';
import SessionManagerForm from './Partials/SessionManagerForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import DeleteUserForm from './Partials/DeleteUserForm';
import CookieConsentBanner from '@/Components/CookieConsentBanner';

interface EditProps {
    user: {
        id: string;
        name: string;
        username: string | null;
        whatsapp: string;
        role: string;
        faction: string;
        role_class: string;
        license_type: string;
        license_status: string;
        level: number;
        experience_points: number;
        rating: number;
        total_reviews: number;
        avatar_url: string | null;
        audio_sfx_enabled: boolean;
        reputation?: {
            total_score: number;
            current_status: string;
            network_score: number;
            transaction_score: number;
            checkin_score: number;
            mission_score: number;
        };
    };
    presets: Array<{
        key: string;
        label: string;
        faction: string;
        description: string;
        badge_color: string;
        icon: string;
    }>;
    sessions: Array<{
        id: string;
        ip_address: string;
        is_current_device: boolean;
        last_active: string;
        device_type: string;
        platform: string;
        browser: string;
    }>;
    status?: string;
}

export default function Edit({ user, presets, sessions, status }: EditProps) {
    const getFactionColor = (faction: string) => {
        switch (faction?.toUpperCase()) {
            case 'NISNAS':
                return { text: '#50C878', bg: 'rgba(80, 200, 120, 0.15)', border: 'rgba(80, 200, 120, 0.3)' };
            case 'ANDROID':
                return { text: '#DC143C', bg: 'rgba(220, 20, 60, 0.15)', border: 'rgba(220, 20, 60, 0.3)' };
            case 'HUMAN':
            default:
                return { text: '#4682B4', bg: 'rgba(70, 130, 180, 0.15)', border: 'rgba(70, 130, 180, 0.3)' };
        }
    };

    const factionStyle = getFactionColor(user.faction);

    // Calculate XP progress for next level
    const xpForNextLevel = user.level * 1000;
    const currentLevelProgress = Math.min(100, Math.round((user.experience_points % 1000) / 10));

    const renderHeroAvatar = () => {
        if (user.avatar_url && !user.avatar_url.startsWith('preset:')) {
            return (
                <img
                    src={user.avatar_url}
                    alt={user.name}
                    className="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border-2 border-[#ffd700] shadow-xl"
                />
            );
        }

        if (user.avatar_url?.startsWith('preset:')) {
            const key = user.avatar_url.replace('preset:', '');
            const matched = presets.find((p) => p.key === user.avatar_url || p.key.endsWith(key));
            return (
                <div
                    className="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl flex items-center justify-center border-2 shadow-xl shrink-0"
                    style={{
                        backgroundColor: matched ? `${matched.badge_color}20` : '#1A1A1A',
                        borderColor: matched?.badge_color || '#ffd700',
                    }}
                >
                    {matched?.icon === 'Anchor' && <Anchor size={36} style={{ color: matched.badge_color }} />}
                    {matched?.icon === 'Compass' && <Compass size={36} style={{ color: matched.badge_color }} />}
                    {matched?.icon === 'Shield' && <Shield size={36} style={{ color: matched.badge_color }} />}
                    {matched?.icon === 'Store' && <Store size={36} style={{ color: matched.badge_color }} />}
                    {!matched && <Sparkles size={36} className="text-[#ffd700]" />}
                </div>
            );
        }

        // Fallback initials
        return (
            <div className="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-tr from-[#ffd700] to-[#b8860b] flex items-center justify-center text-black font-extrabold text-2xl border-2 border-white/20 shadow-xl shrink-0">
                {user.name.charAt(0).toUpperCase()}
            </div>
        );
    };

    return (
        <div className="min-h-screen bg-[#131313] text-[#e5e2e1] font-sans antialiased selection:bg-[#ffd700] selection:text-black">
            <Head title="Pusat Profil & Identitas Karakter" />

            {/* Sticky Navigation Bar */}
            <nav className="sticky top-0 z-40 backdrop-blur-md bg-[#131313]/90 border-b border-white/10 px-4 sm:px-8 py-3.5 flex items-center justify-between">
                <div className="flex items-center gap-3">
                    <Link
                        href="/dashboard"
                        className="p-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white transition-all border border-white/10"
                    >
                        <ArrowLeft size={18} />
                    </Link>
                    <div>
                        <span className="text-xs tracking-wider uppercase text-[#ffd700] font-semibold font-mono">
                            IDENTITAS & KEDAULATAN
                        </span>
                        <h1 className="text-base sm:text-lg font-bold text-white leading-tight">
                            Pusat Profil Saudagar
                        </h1>
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <Link
                        href="/help"
                        className="text-xs px-3 py-1.5 rounded-lg border border-white/10 hover:border-[#ffd700]/50 text-gray-300 hover:text-[#ffd700] transition-colors"
                    >
                        Bantuan
                    </Link>
                    <Link
                        href="/dashboard"
                        className="text-xs px-3 py-1.5 rounded-lg bg-yellow-500/10 border border-yellow-500/30 text-[#ffd700] hover:bg-yellow-500/20 transition-colors"
                    >
                        Markas
                    </Link>
                </div>
            </nav>

            {/* Main Content Container */}
            <main className="max-w-4xl mx-auto px-4 sm:px-6 py-8 space-y-8">
                {/* Status Flash Alert */}
                {status && (
                    <div className="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold flex items-center gap-2 animate-fade-in shadow-lg">
                        <CheckCircle2 size={16} />
                        <span>{status}</span>
                    </div>
                )}

                {/* Hero Profile Identity Card */}
                <div className="glass-panel p-6 sm:p-8 rounded-2xl border border-white/10 relative overflow-hidden bg-gradient-to-b from-[#1A1A1A] to-[#141414]">
                    <div className="absolute top-0 right-0 w-64 h-64 bg-yellow-500/5 rounded-full blur-3xl pointer-events-none" />

                    <div className="flex flex-col sm:flex-row items-center sm:items-start gap-6 relative z-10 text-center sm:text-left">
                        {renderHeroAvatar()}

                        <div className="space-y-2 flex-1">
                            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <h2 className="text-2xl font-bold text-white tracking-tight">
                                        {user.name}
                                    </h2>
                                    <span className="text-xs font-mono text-gray-400">
                                        @{user.username || 'saudagar_gunungmas'}
                                    </span>
                                </div>

                                <div className="flex items-center justify-center sm:justify-end gap-2 flex-wrap">
                                    <span
                                        className="px-2.5 py-1 rounded-lg text-xs font-bold border"
                                        style={{
                                            backgroundColor: factionStyle.bg,
                                            borderColor: factionStyle.border,
                                            color: factionStyle.text,
                                        }}
                                    >
                                        FAKSI {user.faction || 'HUMAN'}
                                    </span>
                                    <span className={`px-2.5 py-1 rounded-lg text-xs font-bold border ${
                                        user.role === 'MERCHANT'
                                            ? 'bg-yellow-500/15 border-yellow-500/30 text-[#ffd700]'
                                            : 'bg-white/5 border-white/10 text-gray-400'
                                    }`}>
                                        {user.role === 'MERCHANT' ? 'Merchant Tier' : 'Free Tier'}
                                    </span>
                                </div>
                            </div>

                            {/* Badges and Rating */}
                            <div className="flex items-center justify-center sm:justify-start gap-4 pt-1 flex-wrap text-xs text-gray-300">
                                <div className="flex items-center gap-1.5 text-yellow-400">
                                    <Star size={14} className="fill-yellow-400" />
                                    <span className="font-bold">{user.rating.toFixed(1)}</span>
                                    <span className="text-gray-500">({user.total_reviews} ulasan)</span>
                                </div>
                                <span className="text-gray-600">•</span>
                                <div className="flex items-center gap-1 text-[#ffd700]">
                                    <Award size={14} />
                                    <span>Level {user.level}</span>
                                </div>
                                <span className="text-gray-600">•</span>
                                <div className="text-gray-400">
                                    Kelas: <span className="text-white font-semibold">{user.role_class}</span>
                                </div>
                            </div>

                            {/* Level XP Progress Bar */}
                            <div className="pt-2 max-w-md">
                                <div className="flex justify-between text-[11px] text-gray-400 mb-1">
                                    <span>Kemajuan Level {user.level}</span>
                                    <span className="font-mono text-[#ffd700]">{user.experience_points} / {xpForNextLevel} XP</span>
                                </div>
                                <div className="h-2 rounded-full bg-black/60 border border-white/10 overflow-hidden">
                                    <div
                                        className="h-full bg-gradient-to-r from-[#ffd700] to-[#e6be00] rounded-full transition-all duration-500 shadow-sm"
                                        style={{ width: `${currentLevelProgress}%` }}
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Section: Respect Status & Social Standing */}
                {user.reputation && (
                    <div className="glass-panel p-6 sm:p-8 rounded-2xl border border-white/10 relative overflow-hidden bg-[#131313]">
                        <div className="flex flex-col sm:flex-row items-center gap-6 justify-between mb-6">
                            <div>
                                <h3 className="text-lg font-bold text-white flex items-center gap-2">
                                    <Shield className="text-[#ffd700]" size={20} />
                                    Reputasi & Status Sosial
                                </h3>
                                <p className="text-xs text-gray-400 mt-1">Status dan pandangan masyarakat ekosistem terhadap Anda.</p>
                            </div>
                            <div className="text-center sm:text-right">
                                <div className="text-3xl font-black bg-clip-text text-transparent bg-gradient-to-r from-[#ffd700] to-[#ffaa00]">
                                    {user.reputation.total_score} <span className="text-sm text-[#ffd700]">RESPECT</span>
                                </div>
                                <div className={`text-xs font-bold uppercase tracking-wider mt-1 px-3 py-1 rounded-full border inline-block ${
                                    user.reputation.current_status === 'DIASINGKAN' ? 'bg-red-500/20 border-red-500/40 text-red-400' :
                                    user.reputation.current_status === 'DIBENCI' ? 'bg-orange-500/20 border-orange-500/40 text-orange-400' :
                                    user.reputation.current_status === 'DICINTAI' ? 'bg-pink-500/20 border-pink-500/40 text-pink-400' :
                                    'bg-yellow-500/10 border-yellow-500/30 text-[#ffd700]'
                                }`}>
                                    Status: {user.reputation.current_status}
                                </div>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            {/* Variable: Network */}
                            <div className="space-y-1">
                                <div className="flex justify-between text-xs font-semibold text-gray-300">
                                    <span>Jaringan & Apresiasi</span>
                                    <span className="text-[#ffd700]">{user.reputation.network_score} Poin</span>
                                </div>
                                <div className="h-2 rounded-full bg-black/50 border border-white/5 overflow-hidden">
                                    <div className="h-full bg-blue-500 rounded-full" style={{ width: `${Math.min(100, (user.reputation.network_score / 100) * 100)}%` }} />
                                </div>
                            </div>

                            {/* Variable: Transaction */}
                            <div className="space-y-1">
                                <div className="flex justify-between text-xs font-semibold text-gray-300">
                                    <span>Integritas Transaksi</span>
                                    <span className="text-[#ffd700]">{user.reputation.transaction_score} Poin</span>
                                </div>
                                <div className="h-2 rounded-full bg-black/50 border border-white/5 overflow-hidden">
                                    <div className="h-full bg-emerald-500 rounded-full" style={{ width: `${Math.min(100, (user.reputation.transaction_score / 100) * 100)}%` }} />
                                </div>
                            </div>

                            {/* Variable: Check-in */}
                            <div className="space-y-1">
                                <div className="flex justify-between text-xs font-semibold text-gray-300">
                                    <span>Konsistensi Kehadiran</span>
                                    <span className="text-[#ffd700]">{user.reputation.checkin_score} Poin</span>
                                </div>
                                <div className="h-2 rounded-full bg-black/50 border border-white/5 overflow-hidden">
                                    <div className="h-full bg-purple-500 rounded-full" style={{ width: `${Math.min(100, (user.reputation.checkin_score / 100) * 100)}%` }} />
                                </div>
                            </div>

                            {/* Variable: Mission */}
                            <div className="space-y-1">
                                <div className="flex justify-between text-xs font-semibold text-gray-300">
                                    <span>Keberhasilan Misi</span>
                                    <span className="text-[#ffd700]">{user.reputation.mission_score} Poin</span>
                                </div>
                                <div className="h-2 rounded-full bg-black/50 border border-white/5 overflow-hidden">
                                    <div className="h-full bg-amber-500 rounded-full" style={{ width: `${Math.min(100, (user.reputation.mission_score / 100) * 100)}%` }} />
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* Section 1: Avatar Customizer */}
                <AvatarCustomizer currentAvatar={user.avatar_url} presets={presets} />

                {/* Section 2: Update Profile Information */}
                <UpdateProfileInformationForm user={user} status={status} />

                {/* Section 3: Audio Preferences */}
                <AudioPreferencesForm initialEnabled={user.audio_sfx_enabled} />

                {/* Section 4: Session Security & Multi-Device Control */}
                <SessionManagerForm sessions={sessions} />

                {/* Section 5: Password Update */}
                <UpdatePasswordForm />

                {/* Section 6: Right to Erasure / Delete Account */}
                <DeleteUserForm />

                {/* Footer Quick Links */}
                <footer className="pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-400">
                    <p>© 2026 PitiCoin.id — Kedaulatan Identitas & Privasi Digital.</p>
                    <div className="flex items-center gap-4">
                        <Link href="/terms" className="hover:text-[#ffd700] transition-colors">
                            Syarat & Ketentuan
                        </Link>
                        <span>•</span>
                        <Link href="/privacy" className="hover:text-[#ffd700] transition-colors">
                            Kebijakan Privasi (UU PDP)
                        </Link>
                        <span>•</span>
                        <Link href="/help" className="hover:text-[#ffd700] transition-colors">
                            Pusat Bantuan
                        </Link>
                    </div>
                </footer>
            </main>

            <CookieConsentBanner />
        </div>
    );
}
