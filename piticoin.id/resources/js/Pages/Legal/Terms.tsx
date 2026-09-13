import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, ShieldCheck, Scale, AlertTriangle, Coins, FileText, CheckCircle2 } from 'lucide-react';
import CookieConsentBanner from '@/Components/CookieConsentBanner';

interface TermsProps {
    appName: string;
    lastUpdated: string;
}

export default function Terms({ appName, lastUpdated }: TermsProps) {
    return (
        <div className="min-h-screen bg-[#131313] text-[#e5e2e1] font-sans antialiased selection:bg-[#ffd700] selection:text-black">
            <Head title="Syarat & Ketentuan Layanan" />

            {/* Sticky Navigation Bar */}
            <nav className="sticky top-0 z-40 backdrop-blur-md bg-[#131313]/90 border-b border-white/10 px-4 sm:px-8 py-3.5 flex items-center justify-between">
                <div className="flex items-center gap-3">
                    <Link
                        href="/"
                        className="p-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white transition-all border border-white/10"
                    >
                        <ArrowLeft size={18} />
                    </Link>
                    <div>
                        <span className="text-xs tracking-wider uppercase text-[#ffd700] font-semibold font-mono">
                            LEGAL & COMPLIANCE
                        </span>
                        <h1 className="text-base sm:text-lg font-bold text-white leading-tight">
                            Syarat & Ketentuan Layanan
                        </h1>
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <Link
                        href="/privacy"
                        className="text-xs px-3 py-1.5 rounded-lg border border-white/10 hover:border-[#ffd700]/50 text-gray-300 hover:text-[#ffd700] transition-colors"
                    >
                        Kebijakan Privasi
                    </Link>
                    <Link
                        href="/help"
                        className="text-xs px-3 py-1.5 rounded-lg bg-yellow-500/10 border border-yellow-500/30 text-[#ffd700] hover:bg-yellow-500/20 transition-colors"
                    >
                        Pusat Bantuan
                    </Link>
                </div>
            </nav>

            {/* Main Content Container */}
            <main className="max-w-4xl mx-auto px-4 sm:px-6 py-10 space-y-10">
                {/* Hero Header */}
                <header className="text-center space-y-4 pt-4 pb-6 border-b border-white/10">
                    <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-yellow-500/10 border border-yellow-500/30 text-[#ffd700] text-xs font-semibold">
                        <Scale size={14} />
                        <span>Tata Kelola Hukum Platform Digital</span>
                    </div>
                    <h1 className="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Syarat dan Ketentuan Layanan {appName}
                    </h1>
                    <p className="text-sm text-gray-400 max-w-2xl mx-auto leading-relaxed">
                        Harap membaca ketentuan ini secara seksama sebelum melakukan staking perak, transaksi pembayaran, atau bergabung dalam karavan ekspedisi ekosistem Locomo.id.
                    </p>
                    <div className="text-xs text-gray-500 font-mono">
                        Terakhir Diperbarui: <span className="text-gray-300 font-medium">{lastUpdated}</span>
                    </div>
                </header>

                {/* Terms Body Sections */}
                <div className="space-y-8 text-sm leading-relaxed text-gray-300">
                    {/* Section 1 */}
                    <section className="glass-panel p-6 sm:p-8 rounded-2xl border border-white/10 space-y-4">
                        <h2 className="text-lg font-bold text-[#ffd700] flex items-center gap-2.5">
                            <span className="flex items-center justify-center w-7 h-7 rounded-lg bg-yellow-500/10 text-xs font-mono font-bold text-[#ffd700] border border-yellow-500/30">1</span>
                            Definisi & Kedudukan Platform
                        </h2>
                        <p>
                            <strong>PitiCoin.id</strong> adalah platform gerbang pembayaran sentral (payment gateway), pencatat jaringan referral, dan pusat gamifikasi simulasi ekspedisi Gunung Mas yang beroperasi di bawah naungan federasi digital <strong>Locomo.id</strong>.
                        </p>
                        <p>
                            PitiCoin bertindak sebagai penghubung teknologis dan finansial antar-entitas layanan pihak ketiga (seperti POS Kasir <em>Unimart.group</em>, portal pendidik <em>WaliKelas.id</em>, dan forum karya <em>Tapak Pandeka</em>).
                        </p>
                    </section>

                    {/* Section 2 */}
                    <section className="glass-panel p-6 sm:p-8 rounded-2xl border border-white/10 space-y-4">
                        <h2 className="text-lg font-bold text-[#ffd700] flex items-center gap-2.5">
                            <span className="flex items-center justify-center w-7 h-7 rounded-lg bg-yellow-500/10 text-xs font-mono font-bold text-[#ffd700] border border-yellow-500/30">2</span>
                            Instrumen Utilitas: Koin Perak & Koin Emas
                        </h2>
                        <div className="grid sm:grid-cols-2 gap-4 pt-2">
                            <div className="p-4 rounded-xl bg-black/40 border border-white/5 space-y-2">
                                <div className="flex items-center gap-2 text-gray-200 font-bold">
                                    <Coins size={16} className="text-gray-400" />
                                    <span>Koin Perak (Silver)</span>
                                </div>
                                <p className="text-xs text-gray-400 leading-relaxed">
                                    Merupakan <em>internal utility simulation token</em> yang berfungsi murni sebagai jaminan staking misi di dalam aplikasi. Koin Perak <strong>bukan alat pembayaran fiat resmi</strong> di luar ekosistem dan tidak dapat ditarik langsung menjadi Rupiah tunai.
                                </p>
                            </div>
                            <div className="p-4 rounded-xl bg-yellow-500/5 border border-yellow-500/20 space-y-2">
                                <div className="flex items-center gap-2 text-[#ffd700] font-bold">
                                    <Coins size={16} className="text-[#ffd700]" />
                                    <span>Koin Emas (Gold)</span>
                                </div>
                                <p className="text-xs text-gray-400 leading-relaxed">
                                    Merupakan poin reward loyalitas yang diperoleh dari keberhasilan penyelesaian misi atau komisi transaksi. Koin Emas didukung oleh cadangan likuiditas riil (Liquidity Pool) dan dapat ditukarkan ke Rupiah atau dibelanjakan untuk langganan layanan.
                                </p>
                            </div>
                        </div>
                    </section>

                    {/* Section 3 */}
                    <section className="glass-panel p-6 sm:p-8 rounded-2xl border border-yellow-500/20 bg-yellow-500/[0.02] space-y-4">
                        <div className="flex items-center gap-2 text-[#ffd700]">
                            <AlertTriangle size={20} />
                            <h2 className="text-lg font-bold">3. Pelepasan Tanggung Jawab Staking (Loss Disclaimer)</h2>
                        </div>
                        <p>
                            Dengan mengambil misi ekspedisi dan mengunci Koin Perak ke dalam <strong>Smart Escrow</strong>, Pengguna menyatakan telah memahami dan menyetujui seluruh risiko permainan simulasi:
                        </p>
                        <ul className="space-y-2.5 list-none pl-0">
                            <li className="flex items-start gap-2.5 text-xs text-gray-300">
                                <CheckCircle2 size={15} className="text-[#ffd700] shrink-0 mt-0.5" />
                                <span>Jika misi berhasil diverifikasi dalam jangka waktu yang ditentukan, Koin Perak dikembalikan utuh 100% disertai hadiah Koin Emas.</span>
                            </li>
                            <li className="flex items-start gap-2.5 text-xs text-gray-300">
                                <CheckCircle2 size={15} className="text-[#ffd700] shrink-0 mt-0.5" />
                                <span>Jika batas waktu kedaluwarsa atau misi dinyatakan gagal oleh sistem evaluasi kuorum, Koin Perak yang di-stake akan hangus secara permanen ke kas ekspedisi tanpa tuntutan ganti rugi apa pun.</span>
                            </li>
                        </ul>
                    </section>

                    {/* Section 4 */}
                    <section className="glass-panel p-6 sm:p-8 rounded-2xl border border-white/10 space-y-4">
                        <h2 className="text-lg font-bold text-[#ffd700] flex items-center gap-2.5">
                            <span className="flex items-center justify-center w-7 h-7 rounded-lg bg-yellow-500/10 text-xs font-mono font-bold text-[#ffd700] border border-yellow-500/30">4</span>
                            Integritas Jaringan & Larangan Serangan Sybil (Anti-Fraud)
                        </h2>
                        <p>
                            Untuk menjamin keadilan bagi seluruh Saudagar dan Penjelajah Gunung Mas, platform memberlakukan toleransi nol terhadap kecurangan:
                        </p>
                        <ul className="space-y-2 list-disc pl-5 text-xs text-gray-400">
                            <li>Dilarang membuat nomor WhatsApp tiruan, virtual, atau bot untuk manipulasi struktur referral karavan.</li>
                            <li>Dilarang melakukan transaksi fiktif (wash trading) untuk mengeksploitasi sistem komisi berjenjang.</li>
                            <li>Akun yang terdeteksi melakukan manipulasi akan dibekukan secara permanen oleh <em>Admin Root Level 0</em>, dan seluruh saldo koin yang diperoleh secara curang akan disita ke pool komunitas.</li>
                        </ul>
                    </section>

                    {/* Section 5 */}
                    <section className="glass-panel p-6 sm:p-8 rounded-2xl border border-white/10 space-y-4">
                        <h2 className="text-lg font-bold text-[#ffd700] flex items-center gap-2.5">
                            <span className="flex items-center justify-center w-7 h-7 rounded-lg bg-yellow-500/10 text-xs font-mono font-bold text-[#ffd700] border border-yellow-500/30">5</span>
                            Pemisahan Entitas Layanan Terfederasi
                        </h2>
                        <p>
                            PitiCoin.id tidak bertanggung jawab secara langsung atas ketersediaan fisik stok barang dagangan, kualitas pelayanan kasir di toko fisik Unimart, atau materi ajar kursus di platform WaliKelas.id. Keluhan operasional barang/jasa wajib dialamatkan langsung kepada entitas mitra bersangkutan.
                        </p>
                    </section>

                    {/* Section 6 */}
                    <section className="glass-panel p-6 sm:p-8 rounded-2xl border border-white/10 space-y-4">
                        <h2 className="text-lg font-bold text-[#ffd700] flex items-center gap-2.5">
                            <span className="flex items-center justify-center w-7 h-7 rounded-lg bg-yellow-500/10 text-xs font-mono font-bold text-[#ffd700] border border-yellow-500/30">6</span>
                            Hukum yang Berlaku & Kontak Resmi
                        </h2>
                        <p>
                            Syarat dan Ketentuan ini tunduk pada hukum dan peraturan perundang-undangan Negara Kesatuan Republik Indonesia. Segala sengketa yang timbul diutamakan diselesaikan melalui musyawarah mufakat atau kanal konsultasi resmi internal Saudagar.
                        </p>
                    </section>
                </div>

                {/* Footer Quick Action */}
                <footer className="pt-8 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-400">
                    <p>© 2026 {appName} — Bagian dari Ekosistem Mandeh & Locomo.id.</p>
                    <div className="flex items-center gap-4">
                        <Link href="/privacy" className="hover:text-[#ffd700] transition-colors">
                            Kebijakan Privasi
                        </Link>
                        <span>•</span>
                        <Link href="/help" className="hover:text-[#ffd700] transition-colors">
                            Pusat Bantuan
                        </Link>
                        <span>•</span>
                        <Link href="/dashboard" className="text-[#ffd700] font-semibold hover:underline">
                            Kembali ke Markas
                        </Link>
                    </div>
                </footer>
            </main>

            <CookieConsentBanner />
        </div>
    );
}
