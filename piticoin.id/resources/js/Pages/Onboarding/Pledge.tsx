import { useState, useRef } from 'react';
import { Head, router } from '@inertiajs/react';
import { 
    Coins, 
    Compass, 
    Crown, 
    Check, 
    FileText, 
    X, 
    AlertTriangle, 
    ArrowRight, 
    Lock, 
    ShieldCheck, 
    ScrollText, 
    Clock, 
    Scale,
    Sparkles
} from 'lucide-react';

interface PledgeProps {
    user: {
        id: string;
        name: string;
    };
}

export default function Pledge({ user }: PledgeProps) {
    const [selectedLicense, setSelectedLicense] = useState<string>('PETUALANG');
    const [loading, setLoading] = useState(false);
    
    // Legal Terms Modal State
    const [showTermsModal, setShowTermsModal] = useState(false);
    const [hasScrolledToBottom, setHasScrolledToBottom] = useState(false);
    const [scrollProgress, setScrollProgress] = useState(0);
    const [isAgreed, setIsAgreed] = useState(false);
    const termsContainerRef = useRef<HTMLDivElement>(null);

    const licenses = [
        {
            id: 'PEMULA',
            name: 'Paket Pemula',
            price: 'Rp 50.000',
            numericPrice: 50000,
            reward: '50.000 Koin Perak',
            xp: '+300 XP',
            icon: Coins,
            color: '#A0A0A0',
            benefits: [
                'Bekal 50.000 Koin Perak untuk Modal Awal',
                'Lisensi Dasar Mendaftarkan Saudagar Baru (Upline)',
                'Akses Peta Ekspedisi Gunung Mas',
                'Klaim Angpao Koin Perak 1x Setiap Jam',
                'Alokasi 25% (Rp 12.500) Disetor ke Pool Likuiditas'
            ],
            recommended: false
        },
        {
            id: 'PETUALANG',
            name: 'Paket Petualang',
            price: 'Rp 100.000',
            numericPrice: 100000,
            reward: '100.000 Koin Perak',
            xp: '+600 XP',
            icon: Compass,
            color: '#50C878',
            benefits: [
                'Bekal 100.000 Koin Perak untuk Modal Awal',
                'Hak Komisi Jaringan Karavan 2-Level (L1 25%, L2 10%)',
                'Prioritas Pengambilan Misi Lapangan Harian',
                'Klaim Angpao Koin Perak dengan Multiplier x1.5',
                'Alokasi 25% (Rp 25.000) Disetor ke Pool Likuiditas',
                'Akses Penuh Seluruh Mini-Game (Slot KOA & Ekspedisi)'
            ],
            recommended: true
        },
        {
            id: 'SAUDAGAR',
            name: 'Paket Saudagar VIP',
            price: 'Rp 200.000',
            numericPrice: 200000,
            reward: '200.000 Koin Perak',
            xp: '+1.200 XP',
            icon: Crown,
            color: '#FFD700',
            benefits: [
                'Bekal 200.000 Koin Perak untuk Modal Maksimal',
                'Hak Komisi Jaringan Karavan Penuh & Status Saudagar Utama',
                'Diskon Biaya Sewa Landing Page & POS di Unimart.group',
                'Akses Fitur Bursa Lelang P2P & Persediaan Digital',
                'Alokasi 25% (Rp 50.000) Disetor ke Pool Likuiditas',
                'Prioritas Utama Verifikasi Pencairan Brankas Emas'
            ],
            recommended: false
        }
    ];

    const handleScrollTerms = (e: React.UIEvent<HTMLDivElement>) => {
        const { scrollTop, scrollHeight, clientHeight } = e.currentTarget;
        const totalScrollable = scrollHeight - clientHeight;
        if (totalScrollable <= 0) {
            setHasScrolledToBottom(true);
            setScrollProgress(100);
            return;
        }

        const currentProgress = Math.min(100, Math.round((scrollTop / totalScrollable) * 100));
        setScrollProgress(currentProgress);

        if (scrollHeight - scrollTop - clientHeight < 40) {
            setHasScrolledToBottom(true);
        }
    };

    const handleOpenTerms = () => {
        if (!selectedLicense) return;
        setShowTermsModal(true);
    };

    const handleConfirmPayment = () => {
        if (!selectedLicense || !isAgreed) return;
        setLoading(true);
        router.post('/onboarding/pledge', { license_type: selectedLicense }, {
            onFinish: () => setLoading(false)
        });
    };

    const selectedPkg = licenses.find(l => l.id === selectedLicense) || licenses[1];

    return (
        <div className="bg-obsidian-surface text-on-surface min-h-screen font-body-md flex flex-col relative overflow-x-hidden pb-14">
            <Head title="Sumpah Saudagar & Piagam Lisensi - PITICOIN.ID" />

            {/* Ambient Background */}
            <div className="fixed inset-0 z-0 pointer-events-none">
                <div className="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,215,0,0.12)_0%,_transparent_60%)]" />
                <div className="absolute inset-0 bg-[#0e0e0e] opacity-40 mix-blend-overlay" />
            </div>

            {/* Header Section */}
            <header className="relative z-10 pt-10 md:pt-14 px-margin-mobile md:px-margin-desktop text-center">
                <div className="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-primary-container/10 border border-primary-container/30 text-xs font-bold tracking-widest uppercase mb-3 text-primary-container">
                    <Sparkles size={13} className="animate-pulse" />
                    <span>TAHAP 4 DARI 5: PERBEKALAN & SUMPAH SAUDAGAR</span>
                </div>
                <h1 className="font-display-hero text-2xl md:text-4xl text-white tracking-widest mb-2 font-bold" style={{ fontFamily: "'Playfair Display', serif" }}>
                    Paket Starter Kit & Piagam Lisensi
                </h1>
                <p className="font-body-md text-xs md:text-sm text-on-surface-variant max-w-2xl mx-auto px-4">
                    Pilih lisensi penjelajahan Anda untuk mengaktifkan akun saudagar. Pembelian paket starter kit menyuntikkan koin perak perdana dan menyalurkan 25% dana ke cadangan likuiditas riil Gunung Mas.
                </p>
            </header>

            {/* Pricing Cards Grid */}
            <main className="flex-grow flex flex-col items-center justify-center relative z-10 w-full max-w-5xl mx-auto px-margin-mobile md:px-margin-desktop mt-6 gap-6">
                <div className="w-full grid grid-cols-1 md:grid-cols-3 gap-5 items-stretch">
                    {licenses.map((l) => {
                        const isSelected = selectedLicense === l.id;
                        const IconComponent = l.icon;

                        return (
                            <div 
                                key={l.id}
                                onClick={() => setSelectedLicense(l.id)}
                                className={`rounded-2xl p-6 md:p-7 flex flex-col justify-between cursor-pointer transition-all duration-300 relative border ${
                                    isSelected 
                                        ? 'border-primary-container bg-gradient-to-b from-white/[0.08] to-[#161616] shadow-[0_0_35px_rgba(255,215,0,0.25)] scale-[1.02] z-10' 
                                        : 'border-white/10 bg-[#141414]/90 hover:border-white/20 hover:bg-[#181818]'
                                }`}
                            >
                                {l.recommended && (
                                    <div className="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary-container text-obsidian-surface font-label-sm text-[10px] px-3.5 py-0.5 rounded-full shadow-lg font-bold tracking-widest uppercase">
                                        Paling Populer
                                    </div>
                                )}
                                
                                <div>
                                    <div className="flex items-center justify-between mb-4">
                                        <div 
                                            className="w-12 h-12 rounded-xl flex items-center justify-center shadow-md"
                                            style={{ backgroundColor: `${l.color}20`, border: `1px solid ${l.color}50` }}
                                        >
                                            <IconComponent size={24} style={{ color: l.color }} />
                                        </div>
                                        <span className="text-xs font-bold px-2.5 py-1 rounded-full bg-white/5 border border-white/10 text-primary-container">
                                            {l.xp}
                                        </span>
                                    </div>

                                    <h3 className="text-xl font-bold mb-1 text-white" style={{ fontFamily: "'Playfair Display', serif" }}>
                                        {l.name}
                                    </h3>
                                    
                                    <div className="text-2xl md:text-3xl font-bold text-primary-container mb-4">
                                        {l.price}
                                    </div>

                                    <div className="p-3 rounded-xl bg-black/40 border border-white/5 mb-5 flex items-center gap-2 text-xs text-white font-semibold">
                                        <Coins size={16} className="text-primary-container shrink-0" />
                                        <span>Bekal: {l.reward}</span>
                                    </div>

                                    <ul className="space-y-2.5 text-xs text-on-surface-variant text-left border-t border-white/10 pt-4">
                                        {l.benefits.map((b, i) => (
                                            <li key={i} className="flex items-start gap-2 leading-relaxed">
                                                <Check size={14} className="mt-0.5 shrink-0 text-primary-container stroke-[3]" />
                                                <span className={isSelected ? 'text-on-surface' : ''}>{b}</span>
                                            </li>
                                        ))}
                                    </ul>
                                </div>

                                <div className="mt-6 pt-4 border-t border-white/5">
                                    <div className={`w-full py-2.5 rounded-xl text-center text-xs font-bold uppercase tracking-wider transition-colors ${
                                        isSelected 
                                            ? 'bg-primary-container text-obsidian-surface' 
                                            : 'bg-white/5 text-on-surface-variant hover:text-white'
                                    }`}>
                                        {isSelected ? '✓ Paket Terpilih' : 'Pilih Paket Ini'}
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>

                {/* Call to Action Button */}
                <div className="w-full max-w-md mt-4 flex flex-col items-center gap-2.5">
                    <button 
                        type="button"
                        onClick={handleOpenTerms}
                        disabled={!selectedLicense}
                        className="w-full bg-primary-container text-obsidian-surface font-label-sm text-sm font-bold py-4 px-6 rounded-xl shadow-[0_0_20px_rgba(255,215,0,0.3)] hover:shadow-[0_0_30px_rgba(255,215,0,0.5)] flex items-center justify-center gap-2.5 transition-all hover:scale-[1.02] active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed uppercase tracking-wider cursor-pointer"
                    >
                        <ScrollText size={18} />
                        <span>Tandatangani Kontrak ({selectedPkg.name})</span>
                    </button>
                    <p className="text-[11px] text-on-surface-variant text-center">
                        Setelah menandatangani kontrak dan menyetujui syarat & ketentuan, rincian pembayaran resmi akan ditampilkan.
                    </p>
                </div>
            </main>

            {/* ============================================================ */}
            {/* POPUP MODAL: SYARAT, KETENTUAN, & PRIVACY POLICY (LEGAL)     */}
            {/* ============================================================ */}
            {showTermsModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-3 md:p-6 bg-black/85 backdrop-blur-lg">
                    <div className="w-full max-w-2xl bg-[#141414] border border-white/15 rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                        
                        {/* Modal Header */}
                        <div className="p-5 border-b border-white/10 flex items-center justify-between bg-[#181818] flex-shrink-0">
                            <div className="flex items-center gap-3">
                                <div className="w-9 h-9 rounded-lg bg-primary-container/10 border border-primary-container/30 flex items-center justify-center text-primary-container">
                                    <Scale size={18} />
                                </div>
                                <div>
                                    <h3 className="text-base font-bold text-white leading-tight" style={{ fontFamily: "'Playfair Display', serif" }}>
                                        Perjanjian Penggunaan & Kebijakan Privasi
                                    </h3>
                                    <p className="text-[11px] text-on-surface-variant">PitiCoin.id & Federated Ecosystem Locomo.id</p>
                                </div>
                            </div>
                            <button
                                type="button"
                                onClick={() => setShowTermsModal(false)}
                                className="p-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-white/70 hover:text-white transition-colors"
                            >
                                <X size={18} />
                            </button>
                        </div>

                        {/* Reading Timer / Calculation Status Bar */}
                        <div className="px-5 py-2.5 bg-black/40 border-b border-white/5 flex items-center justify-between text-[11px] flex-shrink-0">
                            <div className="flex items-center gap-1.5 text-on-surface-variant">
                                <Clock size={13} className="text-primary-container" />
                                <span>Estimasi Waktu Baca: <strong>~3 Menit</strong> (Wajib Dibaca Seluruhnya)</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <span className="text-primary-container font-semibold">{scrollProgress}% Terbaca</span>
                                <div className="w-20 bg-white/10 h-1.5 rounded-full overflow-hidden">
                                    <div 
                                        className="h-full bg-primary-container transition-all duration-150" 
                                        style={{ width: `${scrollProgress}%` }}
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Modal Scrollable Legal Content */}
                        <div 
                            ref={termsContainerRef}
                            onScroll={handleScrollTerms}
                            className="p-5 md:p-6 overflow-y-auto space-y-5 text-xs text-on-surface-variant leading-relaxed select-none"
                        >
                            <div className="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-200 text-xs flex items-start gap-2.5">
                                <AlertTriangle size={18} className="shrink-0 mt-0.5 text-amber-400" />
                                <div>
                                    <strong className="text-white block mb-0.5">Pernyataan Hukum Wajib:</strong>
                                    Dokumen ini merupakan perjanjian berkekuatan hukum tetap antara Anda (sebagai Calon Saudagar) dan Pengelola Ekosistem PitiCoin.id. Anda wajib menggulir dokumen hingga bagian akhir untuk mengaktifkan tombol persetujuan.
                                </div>
                            </div>

                            <article className="space-y-2">
                                <h4 className="text-sm font-bold text-white uppercase tracking-wider">
                                    Pasal 1: Definisi & Ruang Lingkup Layanan
                                </h4>
                                <p>
                                    1.1. <strong>PitiCoin.id</strong> adalah platform simulasi perburuan harta karun digital, loyalitas poin gamifikasi, dan gerbang integrasi ekonomi komunitas nagari yang beroperasi di bawah naungan <strong>Federated Ecosystem Locomo.id</strong> (meliputi Mandeh.ID, Mandeh.Online, Unimart.group, dan Tapak Pandeka).
                                </p>
                                <p>
                                    1.2. <strong>Koin Perak</strong> adalah token utilitas in-game yang digunakan semata-mata sebagai instrumen komitmen aktivitas, staking misi harian, biaya partisipasi petualangan, dan media permainan di dalam platform PitiCoin.id. Koin Perak bukan merupakan alat pembayaran yang sah berdasarkan peraturan Bank Indonesia.
                                </p>
                                <p>
                                    1.3. <strong>Koin Emas</strong> adalah poin loyalitas hasil aktivitas nyata yang dipatok dengan nilai konversi referensi Rp 1.000,- (Satu Ribu Rupiah) per koin, dan didukung oleh cadangan likuiditas riil perbankan perputaran bisnis ekosistem (*Liquidity Pool Gunung Mas*).
                                </p>
                            </article>

                            <article className="space-y-2">
                                <h4 className="text-sm font-bold text-white uppercase tracking-wider">
                                    Pasal 2: Penafian Bukan Instrumen Investasi Spekulatif
                                </h4>
                                <p>
                                    2.1. <strong>Penafian Anti-Ponzi & Anti-Investasi Ilegal</strong>: Pengguna dengan ini secara tegas mengakui bahwa PitiCoin.id BUKAN merupakan lembaga keuangan, perbankan, penghimpunan dana masyarakat, sekuritas, bursa komoditas berjangka, maupun skema piramida/skema Ponzi.
                                </p>
                                <p>
                                    2.2. Platform <strong>tidak pernah menjanjikan imbal hasil pasti (*fixed return*)</strong>, bunga pasif, atau penggandaan modal tanpa partisipasi kerja nyata. Seluruh perolehan Koin Emas hanya dapat diperoleh melalui penyelesaian misi terverifikasi, omzet perdagangan nyata, dan pembangunan jaringan karavan produktif.
                                </p>
                            </article>

                            <article className="space-y-2">
                                <h4 className="text-sm font-bold text-white uppercase tracking-wider">
                                    Pasal 3: Pembelian Starter Kit & Kebijakan Tanpa Pengembalian (Non-Refundable)
                                </h4>
                                <p>
                                    3.1. Pembayaran biaya Starter Kit (Paket Pemula Rp 50.000, Petualang Rp 100.000, atau Saudagar VIP Rp 200.000) merupakan biaya aktivasi hak keanggotaan lisensi niaga dan perbekalan digital perdana.
                                </p>
                                <p>
                                    3.2. <strong>Ketentuan Non-Refundable</strong>: Segala pembayaran yang telah disetorkan dan diverifikasi oleh Admin bersifat <strong>FINAL dan TIDAK DAPAT DITARIK KEMBALI ATAU DIREFUND</strong> dengan alasan apa pun. Pengguna memahami bahwa 25% dari nilai lisensi secara seketika dialokasikan ke cadangan kas likuiditas bersama (*Gunung Mas Pool*) demi menjamin stabilitas ekosistem.
                                </p>
                            </article>

                            <article className="space-y-2">
                                <h4 className="text-sm font-bold text-white uppercase tracking-wider">
                                    Pasal 4: Mekanisme Staking Misi & Penerimaan Risiko Hangus (Burn)
                                </h4>
                                <p>
                                    4.1. Dalam mengambil misi (harian, event, ekspedisi), Pengguna diwajibkan melakukan penjaminan komitmen (*staking*) sejumlah Koin Perak ke rekening Escrow terisolasi.
                                </p>
                                <p>
                                    4.2. Jika misi diselesaikan sesuai kriteria dan batas waktu, Koin Perak yang distake akan dikembalikan ke saldo dompet (dipotong 5% gas fee deflasi mikro sistem) dan Pengguna berhak atas hadiah Koin Emas.
                                </p>
                                <p>
                                    4.3. <strong>Pelepasan Hak Kerugian Misi Gagal</strong>: Apabila Pengguna gagal menyelesaikan misi atau batas waktu terlampaui (*expired*), maka 100% Koin Perak yang distake akan dihancurkan (*burned*) selamanya dari sistem. Pengguna membebaskan Pengelola dari segala tuntutan ganti rugi atas koin yang hangus akibat kegagalan pelaksanaan misi.
                                </p>
                            </article>

                            <article className="space-y-2">
                                <h4 className="text-sm font-bold text-white uppercase tracking-wider">
                                    Pasal 5: Kebijakan Privasi & Pemrosesan Data Pribadi (UU PDP No. 27/2022)
                                </h4>
                                <p>
                                    5.1. Pengelola menghargai dan melindungi data pribadi Pengguna sesuai ketentuan Undang-Undang Republik Indonesia Nomor 27 Tahun 2022 tentang Perlindungan Data Pribadi (UU PDP).
                                </p>
                                <p>
                                    5.2. Data yang dikumpulkan meliputi Nama Lengkap, Nomor WhatsApp, log transaksi, serta data federasi identitas akun tunggal (Mandeh.ID). Data ini digunakan semata-mata untuk verifikasi pembayaran, otentikasi login, koordinasi aliansi saudagar, dan pencegahan kecurangan.
                                </p>
                                <p>
                                    5.3. Pengelola tidak akan menjual, menyewakan, atau memindahtangankan data pribadi Pengguna kepada pihak ketiga non-afiliasi tanpa persetujuan eksplisit dari Pengguna.
                                </p>
                            </article>

                            <article className="space-y-2">
                                <h4 className="text-sm font-bold text-white uppercase tracking-wider">
                                    Pasal 6: Pembatasan Tanggung Jawab Hukum & Force Majeure
                                </h4>
                                <p>
                                    6.1. Pengelola dibebaskan dari segala pertanggungjawaban hukum atas kerugian immaterial maupun material yang timbul akibat kelalaian Pengguna sendiri, termasuk namun tidak terbatas pada kelalaian menjaga kerahasiaan kata sandi, transaksi di luar rekening resmi platform, atau kegagalan perangkat komunikasi milik Pengguna.
                                </p>
                                <p>
                                    6.2. Platform tidak bertanggung jawab atas keterlambatan konfirmasi atau gangguan layanan yang diakibatkan oleh keadaan kahar (*Force Majeure*), bencana alam, kebijakan moneter pemerintah, atau gangguan massal jaringan telekomunikasi nasional.
                                </p>
                            </article>

                            <article className="space-y-2">
                                <h4 className="text-sm font-bold text-white uppercase tracking-wider">
                                    Pasal 7: Penyelesaian Sengketa & Yurisdiksi Hukum
                                </h4>
                                <p>
                                    7.1. Perjanjian ini ditafsirkan dan diatur berdasarkan hukum positif Negara Kesatuan Republik Indonesia.
                                </p>
                                <p>
                                    7.2. Setiap sengketa atau perselisihan yang timbul sehubungan dengan penafsiran atau pelaksanaan Perjanjian ini akan diselesaikan secara musyawarah untuk mencapai mufakat. Apabila musyawarah tidak mencapai mufakat dalam waktu 30 (tiga puluh) hari kalender, maka sengketa akan diselesaikan melalui Pengadilan Negeri domisili hukum Pengelola di wilayah Republik Indonesia.
                                </p>
                            </article>

                            <div className="p-4 rounded-xl bg-white/[0.03] border border-white/10 text-center text-xs text-white">
                                <strong>--- AKHIR DOKUMEN PERJANJIAN RESMI PITICOIN.ID ---</strong>
                            </div>
                        </div>

                        {/* Modal Footer (Enforced Scroll-To-Bottom Check) */}
                        <div className="p-5 border-t border-white/10 bg-[#161616] flex-shrink-0 space-y-3">
                            {!hasScrolledToBottom && (
                                <div className="text-[11px] text-amber-400 font-semibold flex items-center justify-center gap-1.5 animate-pulse">
                                    <AlertTriangle size={14} />
                                    <span>Harap gulir dokumen sampai bagian paling bawah untuk mengaktifkan persetujuan.</span>
                                </div>
                            )}

                            <label className={`flex items-start gap-2.5 cursor-pointer text-xs ${hasScrolledToBottom ? 'text-white' : 'text-on-surface-variant/40 pointer-events-none'}`}>
                                <input
                                    type="checkbox"
                                    disabled={!hasScrolledToBottom}
                                    checked={isAgreed}
                                    onChange={(e) => setIsAgreed(e.target.checked)}
                                    className="mt-0.5 rounded border-white/20 bg-black text-primary-container focus:ring-primary-container"
                                />
                                <span className="leading-snug">
                                    Saya telah membaca seluruh isi syarat, ketentuan, dan kebijakan privasi di atas secara seksama, memahami hak dan kewajiban saya, serta secara sadar menyetujui tanpa paksaan.
                                </span>
                            </label>

                            <div className="flex items-center gap-3 pt-1">
                                <button
                                    type="button"
                                    onClick={() => setShowTermsModal(false)}
                                    className="w-1/3 py-3 rounded-xl bg-white/5 hover:bg-white/10 text-xs font-semibold text-on-surface transition-colors"
                                >
                                    Batalkan
                                </button>
                                <button
                                    type="button"
                                    onClick={handleConfirmPayment}
                                    disabled={!hasScrolledToBottom || !isAgreed || loading}
                                    className="w-2/3 py-3 rounded-xl bg-primary-container text-obsidian-surface text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg transition-all hover:scale-[1.01] active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:scale-100"
                                >
                                    <span>{loading ? 'MEMPROSES INVOICE...' : 'SETUJUI & LANJUT KE PEMBAYARAN'}</span>
                                    <ArrowRight size={14} />
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            )}
        </div>
    );
}
