import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';

interface CommissionLevel {
    level: number;
    percentage: number;
}

interface ServiceData {
    id?: string;
    name?: string;
    code?: string;
    icon?: string;
    category?: string;
    service_type?: 'INTERNAL' | 'EXTERNAL';
    description?: string;
    price_gold?: number;
    price_fiat?: number;
    service_url?: string;
    webhook_url?: string;
    allows_installments?: boolean;
    min_down_payment_gold?: number;
    min_down_payment_fiat?: number;
    commission_rules?: {
        depth?: number;
        levels?: CommissionLevel[];
    };
    is_active?: boolean;
}

interface ServiceFormProps {
    service?: ServiceData | null;
    is_edit: boolean;
}

export default function ServiceForm({ service, is_edit }: ServiceFormProps) {
    // Default 3 levels if empty
    const initialDepth = service?.commission_rules?.depth || 3;
    const initialLevels: CommissionLevel[] = service?.commission_rules?.levels?.length
        ? service.commission_rules.levels
        : [
              { level: 1, percentage: 10 },
              { level: 2, percentage: 5 },
              { level: 3, percentage: 2.5 },
          ];

    const { data, setData, post, put, processing, errors } = useForm({
        name: service?.name || '',
        category: service?.category || 'INTERNAL',
        service_type: service?.service_type || 'INTERNAL',
        description: service?.description || '',
        icon: service?.icon || (service?.service_type === 'INTERNAL' ? 'card_travel' : 'hub'),
        price_gold: service?.price_gold || 0,
        price_fiat: service?.price_fiat || 0,
        service_url: service?.service_url || '',
        webhook_url: service?.webhook_url || '',
        allows_installments: service?.allows_installments ?? false,
        min_down_payment_gold: service?.min_down_payment_gold || 0,
        min_down_payment_fiat: service?.min_down_payment_fiat || 0,
        commission_rules: {
            depth: initialDepth,
            levels: initialLevels,
        },
        is_active: service?.is_active ?? true,
    });

    const [depth, setDepth] = useState<number>(initialDepth);

    const handleDepthChange = (newDepth: number) => {
        const d = Math.max(1, Math.min(10, newDepth));
        setDepth(d);

        const currentLevels = [...(data.commission_rules.levels || [])];
        const updatedLevels: CommissionLevel[] = [];

        for (let i = 1; i <= d; i++) {
            const existing = currentLevels.find((l) => l.level === i);
            if (existing) {
                updatedLevels.push(existing);
            } else {
                // Default fallback percentage depending on level
                const fallbackPct = i === 1 ? 10 : i === 2 ? 5 : i === 3 ? 2.5 : 1;
                updatedLevels.push({ level: i, percentage: fallbackPct });
            }
        }

        setData('commission_rules', {
            depth: d,
            levels: updatedLevels,
        });
    };

    const handleLevelPercentChange = (levelNum: number, percent: number) => {
        const updated = (data.commission_rules.levels || []).map((lvl) => {
            if (lvl.level === levelNum) {
                return { ...lvl, percentage: Math.max(0, Math.min(100, percent)) };
            }
            return lvl;
        });

        setData('commission_rules', {
            depth: data.commission_rules.depth,
            levels: updated,
        });
    };

    const totalCommissionPercent = (data.commission_rules.levels || []).reduce(
        (sum, item) => sum + (Number(item.percentage) || 0),
        0
    );

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (is_edit && service?.id) {
            put(`/marketing/services/${service.id}`);
        } else {
            post('/marketing/services');
        }
    };

    const formatRupiah = (val: number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(val || 0);
    };

    return (
        <div className="bg-background text-on-background min-h-screen relative overflow-x-hidden font-body-md select-none pb-24">
            <Head title={is_edit ? `Edit ${service?.name} - Admin Marketing` : 'Tambah Produk Afiliasi Baru - Admin Marketing'} />

            {/* Header */}
            <header className="bg-obsidian-surface/90 backdrop-blur-xl border-b border-glass-stroke px-4 md:px-8 py-4 sticky top-0 z-30 flex items-center justify-between">
                <div className="flex items-center gap-3">
                    <Link
                        href="/marketing"
                        className="p-2 rounded-full hover:bg-white/5 text-on-surface-variant hover:text-primary-container transition-colors flex items-center gap-1.5"
                    >
                        <span className="material-symbols-outlined text-xl">arrow_back</span>
                        <span className="text-xs font-semibold uppercase tracking-wider hidden sm:inline">Dasbor Marketing</span>
                    </Link>
                    <div className="h-4 w-px bg-glass-stroke" />
                    <div>
                        <h1 className="text-sm md:text-base font-bold text-white flex items-center gap-2">
                            <span className="material-symbols-outlined text-primary-container text-lg">
                                {is_edit ? 'edit_note' : 'add_business'}
                            </span>
                            {is_edit ? `Edit Produk: ${service?.name}` : 'Konfigurasi Produk & Afiliasi Baru'}
                        </h1>
                        <p className="text-[11px] text-on-surface-variant font-mono">
                            {is_edit ? `ID: ${service?.id} (${service?.code})` : 'PitiCoin Dynamic Affiliate Engine'}
                        </p>
                    </div>
                </div>

                <div className="flex items-center gap-2">
                    <Link
                        href="/marketing"
                        className="px-4 py-2 rounded-xl text-xs font-semibold text-on-surface-variant hover:text-white hover:bg-white/5 border border-glass-stroke transition-all"
                    >
                        Batal
                    </Link>
                    <button
                        type="button"
                        onClick={handleSubmit}
                        disabled={processing}
                        className="px-5 py-2 rounded-xl bg-gradient-to-r from-primary to-primary-container hover:brightness-110 text-primary-on text-xs font-bold shadow-lg shadow-primary-container/20 flex items-center gap-2 transition-all disabled:opacity-50 cursor-pointer"
                    >
                        {processing ? (
                            <>
                                <span className="material-symbols-outlined animate-spin text-sm">progress_activity</span>
                                Menyimpan...
                            </>
                        ) : (
                            <>
                                <span className="material-symbols-outlined text-sm">save</span>
                                {is_edit ? 'Simpan Perubahan' : 'Terbitkan Produk'}
                            </>
                        )}
                    </button>
                </div>
            </header>

            {/* Main Form Container */}
            <main className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <form onSubmit={handleSubmit} className="space-y-8">
                    {/* SECTION 1: Service Type & Basic Info */}
                    <div className="bg-obsidian-surface/60 border border-glass-stroke rounded-2xl p-6 md:p-8 backdrop-blur-md shadow-2xl relative overflow-hidden">
                        <div className="absolute top-0 right-0 w-64 h-64 bg-primary-container/5 rounded-full blur-3xl pointer-events-none" />

                        <div className="flex items-center gap-3 mb-6">
                            <div className="w-10 h-10 rounded-xl bg-primary-container/10 border border-primary-container/30 flex items-center justify-center text-primary-container">
                                <span className="material-symbols-outlined text-xl">category</span>
                            </div>
                            <div>
                                <h2 className="text-base font-bold text-white">Informasi Dasar & Tipe Layanan</h2>
                                <p className="text-xs text-on-surface-variant">
                                    Tentukan apakah produk berupa paket pendaftaran internal (Umroh/Produk) atau SaaS eksternal.
                                </p>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {/* Service Type Selection Cards */}
                            <div className="md:col-span-2">
                                <label className="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-3">
                                    Tipe Layanan / Produk <span className="text-red-400">*</span>
                                </label>
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div
                                        onClick={() => {
                                            setData('service_type', 'INTERNAL');
                                            if (!data.icon || data.icon === 'hub') setData('icon', 'card_travel');
                                        }}
                                        className={`cursor-pointer p-4 rounded-xl border transition-all ${
                                            data.service_type === 'INTERNAL'
                                                ? 'bg-primary-container/10 border-primary-container shadow-md shadow-primary-container/10'
                                                : 'bg-obsidian-surface border-glass-stroke hover:border-white/20'
                                        }`}
                                    >
                                        <div className="flex items-center justify-between mb-2">
                                            <div className="flex items-center gap-2">
                                                <span className="material-symbols-outlined text-primary-container text-2xl">
                                                    card_travel
                                                </span>
                                                <span className="text-sm font-bold text-white">INTERNAL PRODUCT</span>
                                            </div>
                                            {data.service_type === 'INTERNAL' && (
                                                <span className="material-symbols-outlined text-primary-container text-lg">
                                                    check_circle
                                                </span>
                                            )}
                                        </div>
                                        <p className="text-xs text-on-surface-variant leading-relaxed">
                                            Paket pendaftaran produk internal (seperti <b>Umroh, Suplemen/Herbal, Kemitraan Agency</b>). Tidak butuh webhook eksternal, hanya pembayaran & pencatatan pohon jaringan.
                                        </p>
                                    </div>

                                    <div
                                        onClick={() => {
                                            setData('service_type', 'EXTERNAL');
                                            if (data.icon === 'card_travel') setData('icon', 'hub');
                                        }}
                                        className={`cursor-pointer p-4 rounded-xl border transition-all ${
                                            data.service_type === 'EXTERNAL'
                                                ? 'bg-cyan-500/10 border-cyan-500 shadow-md shadow-cyan-500/10'
                                                : 'bg-obsidian-surface border-glass-stroke hover:border-white/20'
                                        }`}
                                    >
                                        <div className="flex items-center justify-between mb-2">
                                            <div className="flex items-center gap-2">
                                                <span className="material-symbols-outlined text-cyan-400 text-2xl">
                                                    hub
                                                </span>
                                                <span className="text-sm font-bold text-white">EXTERNAL SAAS HUB</span>
                                            </div>
                                            {data.service_type === 'EXTERNAL' && (
                                                <span className="material-symbols-outlined text-cyan-400 text-lg">
                                                    check_circle
                                                </span>
                                            )}
                                        </div>
                                        <p className="text-xs text-on-surface-variant leading-relaxed">
                                            Layanan platform SaaS eksternal (seperti <b>Unimart, Walikelas</b>). Memerlukan webhook sinkronisasi otomatis dan aktivasi lisensi toko.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Service Name */}
                            <div>
                                <label className="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">
                                    Nama Produk / Layanan <span className="text-red-400">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Contoh: Paket Umroh Barokah 2026, Unimart Premium"
                                    className="w-full px-4 py-3 bg-obsidian-surface border border-glass-stroke rounded-xl text-white text-sm focus:outline-none focus:border-primary-container transition-colors"
                                    required
                                />
                                {errors.name && <p className="text-red-400 text-xs mt-1">{errors.name}</p>}
                            </div>

                            {/* Category */}
                            <div>
                                <label className="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">
                                    Kategori Produk <span className="text-red-400">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={data.category}
                                    onChange={(e) => setData('category', e.target.value.toUpperCase())}
                                    placeholder="Contoh: UMROH, SAAS, HERBAL, KEMITRAAN, EDC"
                                    className="w-full px-4 py-3 bg-obsidian-surface border border-glass-stroke rounded-xl text-white text-sm font-mono focus:outline-none focus:border-primary-container transition-colors"
                                    required
                                />
                                {errors.category && <p className="text-red-400 text-xs mt-1">{errors.category}</p>}
                            </div>

                            {/* Icon selection */}
                            <div>
                                <label className="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">
                                    Ikon (Google Material Symbol)
                                </label>
                                <div className="flex items-center gap-3">
                                    <div className="w-11 h-11 rounded-xl bg-obsidian-surface border border-glass-stroke flex items-center justify-center text-primary-container">
                                        <span className="material-symbols-outlined text-2xl">{data.icon || 'star'}</span>
                                    </div>
                                    <input
                                        type="text"
                                        value={data.icon}
                                        onChange={(e) => setData('icon', e.target.value)}
                                        placeholder="card_travel, storefront, fitness_center, workspace_premium, hub"
                                        className="flex-1 px-4 py-3 bg-obsidian-surface border border-glass-stroke rounded-xl text-white text-sm font-mono focus:outline-none focus:border-primary-container transition-colors"
                                    />
                                </div>
                                <p className="text-[11px] text-on-surface-variant mt-1">
                                    Pilih nama icon dari Google Fonts Material Symbols.
                                </p>
                            </div>

                            {/* Active Status (if edit) */}
                            {is_edit && (
                                <div className="flex items-center justify-between p-4 bg-obsidian-surface border border-glass-stroke rounded-xl">
                                    <div>
                                        <span className="text-sm font-bold text-white block">Status Publikasi</span>
                                        <span className="text-xs text-on-surface-variant">Tampilkan layanan ini di Tab Layanan aplikasi</span>
                                    </div>
                                    <label className="relative inline-flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            checked={data.is_active}
                                            onChange={(e) => setData('is_active', e.target.checked)}
                                            className="sr-only peer"
                                        />
                                        <div className="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-container"></div>
                                    </label>
                                </div>
                            )}

                            {/* Description */}
                            <div className="md:col-span-2">
                                <label className="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">
                                    Deskripsi Lengkap & Ketentuan Produk <span className="text-red-400">*</span>
                                </label>
                                <textarea
                                    rows={4}
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Jelaskan fasilitas paket, benefit peserta, dan petunjuk aktivasi..."
                                    className="w-full px-4 py-3 bg-obsidian-surface border border-glass-stroke rounded-xl text-white text-sm focus:outline-none focus:border-primary-container transition-colors"
                                    required
                                />
                                {errors.description && <p className="text-red-400 text-xs mt-1">{errors.description}</p>}
                            </div>
                        </div>
                    </div>

                    {/* SECTION 2: Pricing Strategy & Dynamic Installments */}
                    <div className="bg-obsidian-surface/60 border border-glass-stroke rounded-2xl p-6 md:p-8 backdrop-blur-md shadow-2xl relative overflow-hidden">
                        <div className="flex items-center gap-3 mb-6">
                            <div className="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
                                <span className="material-symbols-outlined text-xl">payments</span>
                            </div>
                            <div>
                                <h2 className="text-base font-bold text-white">Penetapan Harga Dinamis & Opsi Cicilan</h2>
                                <p className="text-xs text-on-surface-variant">
                                    Harga dapat diubah sewaktu-waktu oleh Admin Marketing tanpa merusak histori transaksi lama.
                                </p>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            {/* Price Gold */}
                            <div className="p-4 bg-obsidian-surface border border-amber-500/30 rounded-xl">
                                <div className="flex items-center justify-between mb-2">
                                    <label className="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                                        <span className="material-symbols-outlined text-sm">monetization_on</span>
                                        Harga Paket (Koin Emas) <span className="text-red-400">*</span>
                                    </label>
                                    <span className="text-[11px] font-mono text-on-surface-variant">Metode Utama</span>
                                </div>
                                <input
                                    type="number"
                                    step="any"
                                    value={data.price_gold}
                                    onChange={(e) => setData('price_gold', parseFloat(e.target.value) || 0)}
                                    className="w-full px-4 py-3 bg-black/40 border border-glass-stroke rounded-xl text-amber-400 font-mono font-bold text-lg focus:outline-none focus:border-amber-500 transition-colors"
                                    required
                                />
                                <p className="text-[11px] text-on-surface-variant mt-1.5">
                                    Nominal koin emas yang akan didebit dari wallet peserta saat pembayaran penuh.
                                </p>
                            </div>

                            {/* Price Fiat */}
                            <div className="p-4 bg-obsidian-surface border border-emerald-500/30 rounded-xl">
                                <div className="flex items-center justify-between mb-2">
                                    <label className="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                                        <span className="material-symbols-outlined text-sm">local_atm</span>
                                        Harga Paket (Fiat IDR / QRIS) <span className="text-red-400">*</span>
                                    </label>
                                    <span className="text-[11px] font-mono text-on-surface-variant">Fallback QRIS</span>
                                </div>
                                <input
                                    type="number"
                                    step="any"
                                    value={data.price_fiat}
                                    onChange={(e) => setData('price_fiat', parseFloat(e.target.value) || 0)}
                                    className="w-full px-4 py-3 bg-black/40 border border-glass-stroke rounded-xl text-emerald-400 font-mono font-bold text-lg focus:outline-none focus:border-emerald-500 transition-colors"
                                    required
                                />
                                <p className="text-[11px] text-on-surface-variant mt-1.5 font-mono">
                                    Setara {formatRupiah(Number(data.price_fiat))}
                                </p>
                            </div>
                        </div>

                        {/* Staged Payments / Installments Toggle */}
                        <div className="p-5 bg-obsidian-surface/80 border border-glass-stroke rounded-xl space-y-4">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-3">
                                    <div className="w-9 h-9 rounded-lg bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-center text-indigo-400">
                                        <span className="material-symbols-outlined text-lg">schedule</span>
                                    </div>
                                    <div>
                                        <span className="text-sm font-bold text-white block">
                                            Aktifkan Fitur Pembayaran Bertahap (Cicilan / DP Awal)
                                        </span>
                                        <span className="text-xs text-on-surface-variant">
                                            Izinkan peserta mendaftar dengan uang muka (DP) dan mencicil sisanya kapan saja.
                                        </span>
                                    </div>
                                </div>

                                <label className="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={data.allows_installments}
                                        onChange={(e) => setData('allows_installments', e.target.checked)}
                                        className="sr-only peer"
                                    />
                                    <div className="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-500"></div>
                                </label>
                            </div>

                            {data.allows_installments && (
                                <div className="pt-4 border-t border-glass-stroke grid grid-cols-1 sm:grid-cols-2 gap-4 animate-in fade-in duration-300">
                                    <div>
                                        <label className="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">
                                            Minimal DP Awal (Koin Emas)
                                        </label>
                                        <input
                                            type="number"
                                            step="any"
                                            value={data.min_down_payment_gold}
                                            onChange={(e) => setData('min_down_payment_gold', parseFloat(e.target.value) || 0)}
                                            placeholder="Contoh: 1000"
                                            className="w-full px-3.5 py-2.5 bg-black/40 border border-glass-stroke rounded-xl text-amber-400 font-mono text-sm focus:outline-none focus:border-indigo-500"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1.5">
                                            Minimal DP Awal (Rupiah / IDR)
                                        </label>
                                        <input
                                            type="number"
                                            step="any"
                                            value={data.min_down_payment_fiat}
                                            onChange={(e) => setData('min_down_payment_fiat', parseFloat(e.target.value) || 0)}
                                            placeholder="Contoh: 1000000"
                                            className="w-full px-3.5 py-2.5 bg-black/40 border border-glass-stroke rounded-xl text-emerald-400 font-mono text-sm focus:outline-none focus:border-indigo-500"
                                        />
                                    </div>

                                    <div className="sm:col-span-2 p-3 bg-indigo-500/10 border border-indigo-500/20 rounded-lg text-xs text-indigo-300 flex items-start gap-2">
                                        <span className="material-symbols-outlined text-sm mt-0.5">info</span>
                                        <span>
                                            <b>Mekanisme Pembagian Komisi Bertahap:</b> Setiap kali peserta melakukan pembayaran cicilan (baik DP awal maupun pelunasan berikutnya), komisi upline afiliasi akan <b>langsung dibagikan secara proporsional</b> sesuai nominal yang disetor saat itu juga!
                                        </span>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* SECTION 3: Dynamic Multi-Level Affiliate Commission Builder */}
                    <div className="bg-obsidian-surface/60 border border-glass-stroke rounded-2xl p-6 md:p-8 backdrop-blur-md shadow-2xl relative overflow-hidden">
                        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                            <div className="flex items-center gap-3">
                                <div className="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center text-purple-400">
                                    <span className="material-symbols-outlined text-xl">account_tree</span>
                                </div>
                                <div>
                                    <h2 className="text-base font-bold text-white">
                                        Konfigurasi Pohon Afiliasi & Profit Sharing
                                    </h2>
                                    <p className="text-xs text-on-surface-variant">
                                        Tentukan kedalaman jaringan sponsor (Level 1..N) dan persentase komisi per level.
                                    </p>
                                </div>
                            </div>

                            {/* Depth Selector */}
                            <div className="flex items-center gap-3 bg-obsidian-surface px-4 py-2 rounded-xl border border-glass-stroke">
                                <span className="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                                    Kedalaman:
                                </span>
                                <div className="flex items-center gap-2">
                                    <button
                                        type="button"
                                        onClick={() => handleDepthChange(depth - 1)}
                                        disabled={depth <= 1}
                                        className="w-7 h-7 rounded-lg bg-white/5 hover:bg-white/10 disabled:opacity-30 text-white font-bold flex items-center justify-center text-sm transition-colors"
                                    >
                                        -
                                    </button>
                                    <span className="font-mono font-bold text-white text-sm w-8 text-center">
                                        {depth} Lvl
                                    </span>
                                    <button
                                        type="button"
                                        onClick={() => handleDepthChange(depth + 1)}
                                        disabled={depth >= 10}
                                        className="w-7 h-7 rounded-lg bg-white/5 hover:bg-white/10 disabled:opacity-30 text-white font-bold flex items-center justify-center text-sm transition-colors"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>

                        {/* Interactive Level Slider & Inputs */}
                        <div className="space-y-3.5 mb-6">
                            {(data.commission_rules.levels || []).map((lvl) => {
                                const levelGoldSimulation = ((data.price_gold * lvl.percentage) / 100).toFixed(2);
                                const levelFiatSimulation = formatRupiah((data.price_fiat * lvl.percentage) / 100);

                                return (
                                    <div
                                        key={lvl.level}
                                        className="p-4 bg-obsidian-surface border border-glass-stroke rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-purple-500/40 transition-colors"
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-300 font-mono font-bold text-xs flex items-center justify-center border border-purple-500/30">
                                                L{lvl.level}
                                            </div>
                                            <div>
                                                <span className="text-sm font-bold text-white block">
                                                    {lvl.level === 1
                                                        ? 'Level 1 (Sponsor Langsung)'
                                                        : `Level ${lvl.level} (Upline Generasi ${lvl.level})`}
                                                </span>
                                                <span className="text-[11px] text-on-surface-variant font-mono">
                                                    Est. Payout: {levelGoldSimulation} Koin Emas / {levelFiatSimulation}
                                                </span>
                                            </div>
                                        </div>

                                        <div className="flex items-center gap-4">
                                            <input
                                                type="range"
                                                min="0"
                                                max="50"
                                                step="0.5"
                                                value={lvl.percentage}
                                                onChange={(e) =>
                                                    handleLevelPercentChange(lvl.level, parseFloat(e.target.value) || 0)
                                                }
                                                className="w-32 sm:w-48 accent-purple-400 cursor-pointer"
                                            />
                                            <div className="flex items-center gap-1 w-24">
                                                <input
                                                    type="number"
                                                    step="0.1"
                                                    min="0"
                                                    max="100"
                                                    value={lvl.percentage}
                                                    onChange={(e) =>
                                                        handleLevelPercentChange(
                                                            lvl.level,
                                                            parseFloat(e.target.value) || 0
                                                        )
                                                    }
                                                    className="w-16 px-2 py-1.5 bg-black/40 border border-glass-stroke rounded-lg text-purple-300 font-mono font-bold text-right text-xs focus:outline-none focus:border-purple-500"
                                                />
                                                <span className="text-xs font-bold text-purple-400">%</span>
                                            </div>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>

                        {/* Summary Card */}
                        <div className="p-5 bg-gradient-to-br from-purple-950/40 via-obsidian-surface to-black/60 border border-purple-500/30 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div className="flex items-center gap-3">
                                <span className="material-symbols-outlined text-purple-400 text-3xl">verified</span>
                                <div>
                                    <span className="text-xs uppercase tracking-wider text-purple-300 font-bold block">
                                        Total Alokasi Margin Komisi Afiliasi
                                    </span>
                                    <span className="text-2xl font-black font-mono text-white">
                                        {totalCommissionPercent.toFixed(1)}%
                                    </span>
                                    <span className="text-xs text-on-surface-variant block mt-0.5">
                                        Sisa margin omzet platform: {(100 - totalCommissionPercent).toFixed(1)}%
                                    </span>
                                </div>
                            </div>

                            <div className="text-right sm:border-l sm:border-glass-stroke sm:pl-6">
                                <span className="text-[11px] text-on-surface-variant uppercase font-mono block">
                                    Simulasi Total Payout per Transaksi
                                </span>
                                <span className="text-sm font-bold font-mono text-amber-400 block">
                                    {((data.price_gold * totalCommissionPercent) / 100).toFixed(2)} Koin Emas
                                </span>
                                <span className="text-xs font-mono text-emerald-400 block">
                                    {formatRupiah((data.price_fiat * totalCommissionPercent) / 100)}
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* SECTION 4: External Technical Integration (Only for EXTERNAL) */}
                    {data.service_type === 'EXTERNAL' && (
                        <div className="bg-obsidian-surface/60 border border-glass-stroke rounded-2xl p-6 md:p-8 backdrop-blur-md shadow-2xl relative overflow-hidden animate-in fade-in duration-300">
                            <div className="flex items-center gap-3 mb-6">
                                <div className="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400">
                                    <span className="material-symbols-outlined text-xl">integration_instructions</span>
                                </div>
                                <div>
                                    <h2 className="text-base font-bold text-white">Pengaturan Integrasi SaaS Eksternal</h2>
                                    <p className="text-xs text-on-surface-variant">
                                        Konfigurasi URL sistem dan webhook sinkronisasi real-time.
                                    </p>
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">
                                        Service URL (Frontend Portal)
                                    </label>
                                    <input
                                        type="url"
                                        value={data.service_url}
                                        onChange={(e) => setData('service_url', e.target.value)}
                                        placeholder="http://unimart.group.localhost atau https://unimart.id"
                                        className="w-full px-4 py-3 bg-obsidian-surface border border-glass-stroke rounded-xl text-white text-sm font-mono focus:outline-none focus:border-cyan-500"
                                    />
                                    <p className="text-[11px] text-on-surface-variant mt-1">
                                        URL tujuan ketika pengguna mengklik Masuk Layanan.
                                    </p>
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">
                                        Webhook URL (Sinkronisasi Onboarding)
                                    </label>
                                    <input
                                        type="url"
                                        value={data.webhook_url}
                                        onChange={(e) => setData('webhook_url', e.target.value)}
                                        placeholder="http://unimart.group.localhost/api/v1/piticoin/provision"
                                        className="w-full px-4 py-3 bg-obsidian-surface border border-glass-stroke rounded-xl text-white text-sm font-mono focus:outline-none focus:border-cyan-500"
                                    />
                                    <p className="text-[11px] text-on-surface-variant mt-1">
                                        Endpoint yang dipanggil otomatis saat pembayaran pendaftaran sukses.
                                    </p>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Bottom Sticky Submit Bar */}
                    <div className="flex items-center justify-end gap-3 pt-4">
                        <Link
                            href="/marketing"
                            className="px-6 py-3 rounded-xl text-xs font-bold text-on-surface-variant hover:text-white hover:bg-white/5 border border-glass-stroke transition-all"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className="px-8 py-3 rounded-xl bg-gradient-to-r from-primary to-primary-container hover:brightness-110 text-primary-on text-sm font-bold shadow-xl shadow-primary-container/20 flex items-center gap-2 transition-all disabled:opacity-50 cursor-pointer"
                        >
                            {processing ? (
                                <>
                                    <span className="material-symbols-outlined animate-spin text-base">progress_activity</span>
                                    Menyimpan Data...
                                </>
                            ) : (
                                <>
                                    <span className="material-symbols-outlined text-base">check_circle</span>
                                    {is_edit ? 'Perbarui Pengaturan Produk' : 'Terbitkan Produk & Aktifkan Afiliasi'}
                                </>
                            )}
                        </button>
                    </div>
                </form>
            </main>
        </div>
    );
}
