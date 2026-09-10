import { useState } from 'react';
import { Head, router, Link } from '@inertiajs/react';
import { 
    Clock, 
    CreditCard, 
    Smartphone, 
    MessageCircle, 
    RefreshCw, 
    Copy, 
    LogOut, 
    Coins, 
    ExternalLink,
    Lock,
    Check,
    Building2,
    Wallet
} from 'lucide-react';

interface PendingProps {
    user: {
        id: string;
        name: string;
        whatsapp: string;
        license_type: string;
        license_status: string;
        faction?: string;
        role_class?: string;
    };
    invoice: {
        code: string;
        package_name: string;
        amount: number;
        formatted_amount: string;
        silver_reward: string;
        bank_accounts: {
            bank: string;
            account_number: string;
            account_name: string;
        }[];
        ewallet_accounts?: {
            name: string;
            account_number: string;
            account_name: string;
        }[];
        admin_contacts?: {
            label: string;
            whatsapp: string;
            formatted: string;
        }[];
        admin_whatsapp: string;
    };
}

export default function Pending({ user, invoice }: PendingProps) {
    const [copiedText, setCopiedText] = useState<string | null>(null);
    const [checking, setChecking] = useState(false);
    const [activeTab, setActiveTab] = useState<'bank' | 'ewallet'>('bank');

    const handleCopy = (text: string, label: string) => {
        navigator.clipboard.writeText(text);
        setCopiedText(label);
        setTimeout(() => setCopiedText(null), 2500);
    };

    const handleCheckStatus = () => {
        setChecking(true);
        router.reload({
            onFinish: () => setChecking(false)
        });
    };

    // Bank accounts list
    const bankAccounts = invoice.bank_accounts || [
        { bank: 'Bank BCA', account_number: '2170436132', account_name: 'Ronal Dwi Putra' },
        { bank: 'Bank Mandiri', account_number: '1110022659904', account_name: 'Ronal Dwi Putra' },
        { bank: 'Bank BRI', account_number: '547701042535534', account_name: 'Ronal Dwi Putra' }
    ];

    // E-wallet accounts list
    const ewalletAccounts = invoice.ewallet_accounts || [
        { name: 'GoPay', account_number: '082385327396', account_name: 'PitiCoin / Locomo' },
        { name: 'OVO', account_number: '085163518215', account_name: 'PitiCoin / Locomo' }
    ];

    // Admin contacts list
    const adminContacts = invoice.admin_contacts || [
        { label: 'Admin 1', whatsapp: '6283130422715', formatted: '+62 831 3042 2715' },
        { label: 'Admin 2', whatsapp: '62895329429118', formatted: '+62 895 3294 29118' }
    ];

    const createWaUrl = (phone: string) => {
        const waMessage = encodeURIComponent(
            `Halo Admin PitiCoin.id, saya telah melakukan transfer pembayaran Starter Kit:\n\n` +
            `• No. Invoice: ${invoice.code}\n` +
            `• Nama: ${user.name}\n` +
            `• No. WhatsApp: ${user.whatsapp}\n` +
            `• Paket: ${invoice.package_name} (${invoice.formatted_amount})\n` +
            `• Injeksi Koin Perak: ${invoice.silver_reward}\n\n` +
            `Berikut saya lampirkan bukti transfer. Mohon bantu konfirmasi & aktivasi lisensi saudagar saya. Terima kasih!`
        );
        return `https://wa.me/${phone}?text=${waMessage}`;
    };

    return (
        <div className="bg-obsidian-surface text-on-surface min-h-screen font-body-md flex flex-col justify-between relative overflow-x-hidden p-4 md:p-8">
            <Head title="Menunggu Konfirmasi Pembayaran - PITICOIN.ID" />

            {/* Ambient Background */}
            <div className="fixed inset-0 z-0 pointer-events-none">
                <div className="absolute inset-0 bg-[radial-gradient(circle_at_center,_rgba(255,215,0,0.08)_0%,_transparent_65%)]" />
                <div className="absolute inset-0 bg-[#0c0c0c] opacity-50 mix-blend-overlay" />
            </div>

            {/* Top Navigation */}
            <header className="relative z-10 w-full max-w-4xl mx-auto flex items-center justify-between pb-5 border-b border-white/10">
                <div className="flex items-center gap-2.5">
                    <span className="text-xl font-bold text-primary-container tracking-widest" style={{ fontFamily: "'Playfair Display', serif" }}>
                        PITICOIN.ID
                    </span>
                    <span className="text-[10px] px-2 py-0.5 rounded bg-amber-500/10 border border-amber-500/30 text-amber-300 font-bold uppercase tracking-wider">
                        Invoice Pembayaran
                    </span>
                </div>

                <div className="flex items-center gap-2">
                    <button
                        onClick={() => router.post(route('logout'))}
                        className="flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-red-950/40 hover:bg-red-900/60 text-red-200 border border-red-800/50 text-xs font-semibold transition-all shadow-sm hover:border-red-600/60 cursor-pointer"
                        title="Keluar dari sesi ini untuk login akun lain"
                    >
                        <LogOut size={14} />
                        <span>Keluar Akun</span>
                    </button>
                </div>
            </header>

            {/* Main Content: Pending Invoice Card */}
            <main className="relative z-10 w-full max-w-4xl mx-auto my-auto py-6 space-y-6">
                
                {/* Status Hero Banner */}
                <div className="p-5 md:p-6 rounded-2xl bg-amber-500/[0.08] border border-amber-500/30 text-center space-y-2.5 backdrop-blur-md">
                    <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-xs font-bold uppercase tracking-wider">
                        <Clock size={14} className="animate-spin" style={{ animationDuration: '4s' }} />
                        <span>MENUNGGU KONFIRMASI PEMBAYARAN OLEH ADMIN</span>
                    </div>
                    <h1 className="text-2xl md:text-3xl font-bold text-white tracking-wide" style={{ fontFamily: "'Playfair Display', serif" }}>
                        Akun Sedang Dalam Antrean Verifikasi
                    </h1>
                    <p className="text-xs md:text-sm text-on-surface-variant max-w-xl mx-auto leading-relaxed">
                        Silakan selesaikan transfer pembayaran Starter Kit Anda ke rekening bank atau e-wallet resmi kami di bawah ini. Setelah Anda mengirimkan bukti transfer ke WhatsApp admin, akun dan bekal Koin Perak akan langsung diaktifkan.
                    </p>
                </div>

                {/* Grid Container: Rincian Tagihan & Metode Pembayaran */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-5 items-stretch">
                    
                    {/* Kolom 1: Rincian Tagihan */}
                    <div className="p-5 md:p-6 rounded-2xl bg-[#141414] border border-white/10 flex flex-col justify-between shadow-xl">
                        <div className="space-y-4">
                            <div className="flex items-center justify-between border-b border-white/10 pb-3">
                                <h2 className="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-1.5">
                                    <Coins size={15} className="text-primary-container" />
                                    <span>Rincian Tagihan</span>
                                </h2>
                                <span className="text-xs font-mono text-primary-container bg-primary-container/10 px-2 py-0.5 rounded border border-primary-container/20">
                                    {invoice.code}
                                </span>
                            </div>

                            <div className="space-y-2.5 text-xs">
                                <div className="flex justify-between py-1 border-b border-white/5">
                                    <span className="text-on-surface-variant">Nama Calon Saudagar:</span>
                                    <span className="font-semibold text-white">{user.name}</span>
                                </div>
                                <div className="flex justify-between py-1 border-b border-white/5">
                                    <span className="text-on-surface-variant">Nomor WhatsApp:</span>
                                    <span className="font-semibold text-white">{user.whatsapp}</span>
                                </div>
                                <div className="flex justify-between py-1 border-b border-white/5">
                                    <span className="text-on-surface-variant">Paket Starter Kit:</span>
                                    <span className="font-semibold text-primary-container">{invoice.package_name}</span>
                                </div>
                                <div className="flex justify-between py-1 border-b border-white/5">
                                    <span className="text-on-surface-variant">Injeksi Bekal Koin Perak:</span>
                                    <span className="font-semibold text-emerald-400">{invoice.silver_reward}</span>
                                </div>
                                <div className="flex justify-between py-2 items-baseline">
                                    <span className="text-sm font-bold text-white">Total Tagihan:</span>
                                    <span className="text-2xl font-bold text-primary-container">{invoice.formatted_amount}</span>
                                </div>
                            </div>
                        </div>

                        {/* Tombol Salin Nominal */}
                        <div className="pt-4 border-t border-white/5">
                            <button
                                type="button"
                                onClick={() => handleCopy(invoice.amount.toString(), 'amount')}
                                className="w-full py-2.5 px-3 rounded-xl bg-white/5 hover:bg-white/10 text-xs text-on-surface-variant hover:text-white flex items-center justify-center gap-1.5 transition-colors border border-white/10 font-medium"
                            >
                                <Copy size={13} />
                                <span>{copiedText === 'amount' ? 'Nominal Berhasil Disalin!' : `Salin Nominal (${invoice.formatted_amount})`}</span>
                            </button>
                        </div>
                    </div>

                    {/* Kolom 2: Rekening Tujuan Pembayaran */}
                    <div className="p-5 md:p-6 rounded-2xl bg-[#141414] border border-white/10 flex flex-col justify-between shadow-xl">
                        <div>
                            {/* Tab Switcher: Transfer Bank vs E-Wallet */}
                            <div className="flex items-center justify-between border-b border-white/10 pb-3 mb-3">
                                <h2 className="text-sm font-bold text-white uppercase tracking-wider">
                                    Tujuan Transfer
                                </h2>
                                <div className="flex gap-1 p-0.5 bg-black/40 rounded-lg border border-white/10">
                                    <button
                                        type="button"
                                        onClick={() => setActiveTab('bank')}
                                        className={`px-3 py-1 rounded-md text-[11px] font-bold transition-all flex items-center gap-1 ${
                                            activeTab === 'bank' 
                                                ? 'bg-primary-container text-obsidian-surface shadow-sm' 
                                                : 'text-on-surface-variant hover:text-white'
                                        }`}
                                    >
                                        <Building2 size={12} />
                                        <span>Bank</span>
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setActiveTab('ewallet')}
                                        className={`px-3 py-1 rounded-md text-[11px] font-bold transition-all flex items-center gap-1 ${
                                            activeTab === 'ewallet' 
                                                ? 'bg-primary-container text-obsidian-surface shadow-sm' 
                                                : 'text-on-surface-variant hover:text-white'
                                        }`}
                                    >
                                        <Smartphone size={12} />
                                        <span>E-Wallet</span>
                                    </button>
                                </div>
                            </div>

                            {/* Daftar Rekening Bank */}
                            {activeTab === 'bank' && (
                                <div className="space-y-2.5">
                                    {bankAccounts.map((acc, idx) => (
                                        <div key={idx} className="p-3 rounded-xl bg-black/40 border border-white/5 space-y-1">
                                            <div className="flex items-center justify-between">
                                                <span className="text-xs font-bold text-primary-container">{acc.bank}</span>
                                                <button
                                                    type="button"
                                                    onClick={() => handleCopy(acc.account_number, `bank_${idx}`)}
                                                    className="text-[10px] text-on-surface-variant hover:text-white flex items-center gap-1 px-2 py-0.5 rounded bg-white/5 border border-white/10"
                                                >
                                                    <Copy size={11} />
                                                    <span>{copiedText === `bank_${idx}` ? 'Tersalin!' : 'Salin No. Rekening'}</span>
                                                </button>
                                            </div>
                                            <div className="text-base font-mono font-bold text-white tracking-wider">
                                                {acc.account_number}
                                            </div>
                                            <div className="text-[10px] text-on-surface-variant">
                                                a.n. {acc.account_name}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}

                            {/* Daftar E-Wallet */}
                            {activeTab === 'ewallet' && (
                                <div className="space-y-2.5">
                                    {ewalletAccounts.map((ew, idx) => (
                                        <div key={idx} className="p-3 rounded-xl bg-black/40 border border-white/5 space-y-1">
                                            <div className="flex items-center justify-between">
                                                <span className="text-xs font-bold text-emerald-400">{ew.name}</span>
                                                <button
                                                    type="button"
                                                    onClick={() => handleCopy(ew.account_number, `ew_${idx}`)}
                                                    className="text-[10px] text-on-surface-variant hover:text-white flex items-center gap-1 px-2 py-0.5 rounded bg-white/5 border border-white/10"
                                                >
                                                    <Copy size={11} />
                                                    <span>{copiedText === `ew_${idx}` ? 'Tersalin!' : 'Salin No. HP'}</span>
                                                </button>
                                            </div>
                                            <div className="text-base font-mono font-bold text-white tracking-wider">
                                                {ew.account_number}
                                            </div>
                                            <div className="text-[10px] text-on-surface-variant">
                                                a.n. {ew.account_name}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>

                        <p className="text-[10px] text-on-surface-variant mt-3 text-center">
                            Pastikan nominal dan nama rekening penerima sesuai sebelum melakukan transfer.
                        </p>
                    </div>
                </div>

                {/* Section Konfirmasi Pembayaran Melalui WhatsApp Admin */}
                <div className="p-5 md:p-6 rounded-2xl bg-[#161616] border border-white/10 space-y-4 shadow-xl">
                    <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-2 border-b border-white/10 pb-3">
                        <div>
                            <h3 className="text-sm font-bold text-white flex items-center gap-2">
                                <MessageCircle size={16} className="text-emerald-400" />
                                <span>Konfirmasi Pembayaran ke Admin WhatsApp</span>
                            </h3>
                            <p className="text-xs text-on-surface-variant mt-0.5">
                                Pilih salah satu nomor admin resmi di bawah ini untuk mengirimkan bukti transfer:
                            </p>
                        </div>

                        <button
                            type="button"
                            onClick={handleCheckStatus}
                            disabled={checking}
                            className="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-xs flex items-center gap-1.5 transition-colors border border-white/10 shrink-0"
                        >
                            <RefreshCw size={13} className={checking ? 'animate-spin' : ''} />
                            <span>{checking ? 'Memeriksa...' : 'Cek Status Aktivasi'}</span>
                        </button>
                    </div>

                    {/* Tombol WhatsApp Kontak Admin 1 & Admin 2 */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        {adminContacts.map((adm, idx) => (
                            <a
                                key={idx}
                                href={createWaUrl(adm.whatsapp)}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="p-3.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 hover:border-emerald-500/50 flex items-center justify-between transition-all group shadow-md"
                            >
                                <div className="flex items-center gap-3">
                                    <div className="w-10 h-10 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-sm">
                                        WA
                                    </div>
                                    <div className="text-left">
                                        <div className="text-xs font-bold text-white flex items-center gap-1.5">
                                            <span>{adm.label}</span>
                                            <span className="text-[10px] text-emerald-400 font-normal">Online</span>
                                        </div>
                                        <div className="text-xs font-mono text-emerald-300 font-semibold">
                                            {adm.formatted}
                                        </div>
                                    </div>
                                </div>

                                <div className="flex items-center gap-1 text-xs font-bold text-emerald-400 group-hover:translate-x-0.5 transition-transform">
                                    <span>Kirim Bukti</span>
                                    <ExternalLink size={13} />
                                </div>
                            </a>
                        ))}
                    </div>
                </div>

                {/* Security Lock Reminder Card */}
                <div className="p-4 rounded-xl bg-black/50 border border-white/5 flex items-center gap-3 text-xs text-on-surface-variant">
                    <Lock size={18} className="text-primary-container shrink-0" />
                    <span className="leading-relaxed">
                        Akun Anda berada dalam status aman <strong>PENDING</strong>. Halaman ini akan terbuka secara otomatis begitu admin menyelesaikan verifikasi transfer Anda.
                    </span>
                </div>

                {/* Logout & Switch Account Card */}
                <div className="p-4 rounded-2xl bg-[#141418] border border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
                    <div className="flex items-center gap-3 text-on-surface-variant">
                        <div className="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 shrink-0">
                            <LogOut size={16} />
                        </div>
                        <div>
                            <span className="text-white font-semibold block">Ingin Masuk ke Akun Lain?</span>
                            <span className="text-[11px] text-gray-400">Anda dapat keluar untuk mendaftar akun saudagar baru atau login ke akun Administrator.</span>
                        </div>
                    </div>
                    <div className="flex items-center gap-2 w-full sm:w-auto shrink-0">
                        <button
                            onClick={() => router.post(route('logout'))}
                            className="w-full sm:w-auto px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-200 hover:text-white border border-white/15 font-semibold text-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
                        >
                            <LogOut size={13} />
                            <span>Keluar dari Sesi</span>
                        </button>
                    </div>
                </div>
            </main>

            {/* Footer */}
            <footer className="relative z-10 text-center text-[11px] text-on-surface-variant pt-4 border-t border-white/5">
                © 2026 PitiCoin.id • Bagian dari Federated Ecosystem Locomo.id
            </footer>
        </div>
    );
}
