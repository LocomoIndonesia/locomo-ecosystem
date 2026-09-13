import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, ShieldCheck, Lock, Database, Trash2, Eye, FileText, CheckCircle2 } from 'lucide-react';
import CookieConsentBanner from '@/Components/CookieConsentBanner';

interface PrivacyProps {
    appName: string;
    lastUpdated: string;
}

export default function Privacy({ appName, lastUpdated }: PrivacyProps) {
    return (
        <div className="min-h-screen bg-[#131313] text-[#e5e2e1] font-sans antialiased selection:bg-[#ffd700] selection:text-black">
            <Head title="Kebijakan Privasi & UU PDP" />

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
                            KEPATUHAN UU PDP 27/2022
                        </span>
                        <h1 className="text-base sm:text-lg font-bold text-white leading-tight">
                            Kebijakan Privasi
                        </h1>
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <Link
                        href="/terms"
                        className="text-xs px-3 py-1.5 rounded-lg border border-white/10 hover:border-[#ffd700]/50 text-gray-300 hover:text-[#ffd700] transition-colors"
                    >
                        Syarat & Ketentuan
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
                    <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold">
                        <ShieldCheck size={14} />
                        <span>Kepatuhan UU No. 27 Tahun 2022 (UU PDP)</span>
                    </div>
                    <h1 className="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Kebijakan Privasi Data Pribadi {appName}
                    </h1>
                    <p className="text-sm text-gray-400 max-w-2xl mx-auto leading-relaxed">
                        Kami menghormati dan melindungi kedaulatan data pribadi Anda sesuai regulasi perlindungan data yang berlaku di Republik Indonesia.
                    </p>
                    <div className="text-xs text-gray-500 font-mono">
                        Berlaku Efektif: <span className="text-gray-300 font-medium">{lastUpdated}</span>
                    </div>
                </header>

                {/* Privacy Body Sections */}
                <div className="space-y-8 text-sm leading-relaxed text-gray-300">
                    {/* Section 1 */}
                    <section className="glass-panel p-6 sm:p-8 rounded-2xl border border-white/10 space-y-4">
                        <h2 className="text-lg font-bold text-[#ffd700] flex items-center gap-2.5">
                            <span className="flex items-center justify-center w-7 h-7 rounded-lg bg-yellow-500/10 text-xs font-mono font-bold text-[#ffd700] border border-yellow-500/30">1</span>
                            Data Pribadi yang Kami Himpun
                        </h2>
                        <p>
                            Dalam pengoperasian platform PitiCoin.id, jenis data pribadi yang kami proses meliputi:
                        </p>
                        <div className="grid sm:grid-cols-2 gap-3 pt-2 text-xs">
                            <div className="p-3.5 rounded-xl bg-black/40 border border-white/5 space-y-1">
                                <span className="font-semibold text-white">Data Identitas Pengguna</span>
                                <p className="text-gray-400">Nama lengkap, nama pengguna (username unik), faksi persona, dan foto avatar karakter.</p>
                            </div>
                            <div className="p-3.5 rounded-xl bg-black/40 border border-white/5 space-y-1">
                                <span className="font-semibold text-white">Data Kontak & Autentikasi</span>
                                <p className="text-gray-400">Nomor WhatsApp aktif untuk pengiriman kode verifikasi OTP dan notifikasi karavan.</p>
                            </div>
                            <div className="p-3.5 rounded-xl bg-black/40 border border-white/5 space-y-1">
                                <span className="font-semibold text-white">Data Transaksi & Finansial</span>
                                <p className="text-gray-400">Catatan ledger staking escrow Koin Perak, mutasi reward Koin Emas, dan nomor rekening penarikan bank.</p>
                            </div>
                            <div className="p-3.5 rounded-xl bg-black/40 border border-white/5 space-y-1">
                                <span className="font-semibold text-white">Data Lokasi Geografis (GPS)</span>
                                <p className="text-gray-400">Hanya diminta saat Saudagar mendaftarkan titik lokasi merchant fisik warung/toko UMKM baru.</p>
                            </div>
                        </div>
                    </section>

                    {/* Section 2 */}
                    <section className="glass-panel p-6 sm:p-8 rounded-2xl border border-white/10 space-y-4">
                        <h2 className="text-lg font-bold text-[#ffd700] flex items-center gap-2.5">
                            <span className="flex items-center justify-center w-7 h-7 rounded-lg bg-yellow-500/10 text-xs font-mono font-bold text-[#ffd700] border border-yellow-500/30">2</span>
                            Tujuan Pemrosesan Data Pribadi
                        </h2>
                        <ul className="space-y-2 list-disc pl-5 text-xs text-gray-300">
                            <li>Memfasilitasi autentikasi tunggal (SSO) terfederasi melalui gateway <strong>Mandeh.ID</strong>.</li>
                            <li>Menghitung komisi referral berjenjang pada struktur karavan upline & downline secara akurat.</li>
                            <li>Mencegah serangan akun ganda palsu (Sybil attacks) dan memastikan keaslian transaksi finansial.</li>
                            <li>Memfasilitasi saluran konsultasi pribadi dan tanya-jawab antara Pengguna dengan Saudagar Pembina atau Admin.</li>
                        </ul>
                    </section>

                    {/* Section 3 */}
                    <section className="glass-panel p-6 sm:p-8 rounded-2xl border border-white/10 space-y-4">
                        <h2 className="text-lg font-bold text-[#ffd700] flex items-center gap-2.5">
                            <span className="flex items-center justify-center w-7 h-7 rounded-lg bg-yellow-500/10 text-xs font-mono font-bold text-[#ffd700] border border-yellow-500/30">3</span>
                            Keamanan & Enkripsi Penyimpanan
                        </h2>
                        <div className="flex items-start gap-4 pt-1">
                            <div className="p-3 rounded-xl bg-yellow-500/10 text-[#ffd700] shrink-0">
                                <Lock size={24} />
                            </div>
                            <div className="space-y-2 text-xs text-gray-300">
                                <p>
                                    Kata sandi Anda disimpan menggunakan algoritma hashing standar industri <strong>Argon2id / Bcrypt</strong> dengan salt acak yang tidak dapat didekripsi kembali.
                                </p>
                                <p>
                                    Seluruh lalu lintas komunikasi data antara peramban Anda dengan server PitiCoin dienkripsi dengan standar <strong>Transport Layer Security (TLS 1.3)</strong>.
                                </p>
                            </div>
                        </div>
                    </section>

                    {/* Section 4 (Cookies) */}
                    <section id="cookies" className="glass-panel p-6 sm:p-8 rounded-2xl border border-yellow-500/30 bg-yellow-500/[0.02] space-y-4 scroll-mt-20">
                        <h2 className="text-lg font-bold text-[#ffd700] flex items-center gap-2.5">
                            <span className="flex items-center justify-center w-7 h-7 rounded-lg bg-yellow-500/10 text-xs font-mono font-bold text-[#ffd700] border border-yellow-500/30">4</span>
                            Kebijakan Kuki (Cookies) & Penyimpanan Lokal
                        </h2>
                        <p className="text-xs text-gray-300">
                            Kami membagi penggunaan kuki dan penyimpanan lokal (LocalStorage) menjadi dua kategori:
                        </p>
                        <div className="space-y-3 pt-1 text-xs">
                            <div className="p-3.5 rounded-xl bg-black/40 border border-white/10 space-y-1">
                                <span className="font-semibold text-emerald-400">1. Kuki Esensial (Wajib Aktif)</span>
                                <p className="text-gray-400">
                                    Menyimpan token sesi Laravel terenkripsi dan token CSRF untuk mencegah serangan pemalsuan permintaan antar-situs. Kuki ini sangat penting agar Anda dapat tetap masuk secara aman.
                                </p>
                            </div>
                            <div className="p-3.5 rounded-xl bg-black/40 border border-white/10 space-y-1">
                                <span className="font-semibold text-[#ffd700]">2. Kuki Preferensi & Kinerja (Dapat Disesuaikan)</span>
                                <p className="text-gray-400">
                                    Menyimpan status suara audio efek ekspedisi (SFX), riwayat dialog modal yang telah ditutup, serta status persetujuan spanduk privasi.
                                </p>
                            </div>
                        </div>
                    </section>

                    {/* Section 5 (Rights) */}
                    <section className="glass-panel p-6 sm:p-8 rounded-2xl border border-white/10 space-y-4">
                        <h2 className="text-lg font-bold text-[#ffd700] flex items-center gap-2.5">
                            <span className="flex items-center justify-center w-7 h-7 rounded-lg bg-yellow-500/10 text-xs font-mono font-bold text-[#ffd700] border border-yellow-500/30">5</span>
                            Hak Subjek Data (Sesuai Bab IV UU PDP 27/2022)
                        </h2>
                        <p className="text-xs text-gray-300">
                            Sebagai pemilik data pribadi, Anda memiliki hak-hak yang dilindungi oleh undang-undang:
                        </p>
                        <div className="grid sm:grid-cols-3 gap-3 pt-2 text-xs">
                            <div className="p-4 rounded-xl bg-black/30 border border-white/5 space-y-2">
                                <Eye size={18} className="text-[#ffd700]" />
                                <h4 className="font-semibold text-white">Hak Akses & Salinan</h4>
                                <p className="text-gray-400">Hak untuk meminta informasi dan salinan data profil yang tersimpan di sistem.</p>
                            </div>
                            <div className="p-4 rounded-xl bg-black/30 border border-white/5 space-y-2">
                                <Database size={18} className="text-[#ffd700]" />
                                <h4 className="font-semibold text-white">Hak Koreksi Data</h4>
                                <p className="text-gray-400">Hak untuk memperbarui nama, avatar, dan identitas melalui menu Pusat Profil.</p>
                            </div>
                            <div className="p-4 rounded-xl bg-black/30 border border-red-500/20 space-y-2">
                                <Trash2 size={18} className="text-red-400" />
                                <h4 className="font-semibold text-white">Hak Penghapusan (Erasure)</h4>
                                <p className="text-gray-400">Hak untuk menutup akun dan meminta penghapusan identitas pribadi secara permanen.</p>
                            </div>
                        </div>
                    </section>

                    {/* Section 6 (Contact) */}
                    <section className="glass-panel p-6 sm:p-8 rounded-2xl border border-white/10 space-y-4">
                        <h2 className="text-lg font-bold text-[#ffd700] flex items-center gap-2.5">
                            <span className="flex items-center justify-center w-7 h-7 rounded-lg bg-yellow-500/10 text-xs font-mono font-bold text-[#ffd700] border border-yellow-500/30">6</span>
                            Pejabat Perlindungan Data Pribadi (DPO)
                        </h2>
                        <p className="text-xs text-gray-300">
                            Untuk mengajukan permohonan pemenuhan hak subjek data atau menyampaikan keberatan atas pemrosesan data pribadi, Anda dapat menghubungi Petugas Perlindungan Data kami melalui kanal resmi:
                        </p>
                        <div className="p-4 rounded-xl bg-black/40 border border-white/5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                            <div>
                                <span className="font-semibold text-white">Data Protection Officer (DPO) Locomo</span>
                                <div className="text-gray-400">Email: dpo@locomo.id • WhatsApp: +62 812-3456-7890</div>
                            </div>
                            <Link
                                href="/help"
                                className="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white font-medium transition-all"
                            >
                                Hubungi Kami
                            </Link>
                        </div>
                    </section>
                </div>

                {/* Footer Quick Action */}
                <footer className="pt-8 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-400">
                    <p>© 2026 {appName} — Kepatuhan Penuh Terhadap UU PDP No. 27/2022.</p>
                    <div className="flex items-center gap-4">
                        <Link href="/terms" className="hover:text-[#ffd700] transition-colors">
                            Syarat & Ketentuan
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
