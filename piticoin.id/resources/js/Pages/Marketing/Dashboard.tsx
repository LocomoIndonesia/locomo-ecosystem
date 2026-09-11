import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';

interface MetricData {
    total_income_gold: number;
    total_income_fiat: number;
    total_payout_gold: number;
    total_payout_fiat: number;
    total_orders: number;
    active_installments: number;
}

interface ServiceItem {
    id: string;
    name: string;
    code: string;
    icon?: string;
    category: string;
    service_type: 'INTERNAL' | 'EXTERNAL';
    description?: string;
    price_gold: number;
    price_fiat: number;
    allows_installments: boolean;
    min_down_payment_gold?: number;
    min_down_payment_fiat?: number;
    commission_rules?: {
        depth?: number;
        levels?: Array<{ level: number; percentage: number }>;
    };
    is_active: boolean;
    orders_count: number;
    licenses_count: number;
}

interface CommissionItem {
    id: string;
    level: number;
    percentage: number;
    amount_gold: number;
    amount_fiat: number;
    currency: string;
    status: string;
    created_at: string;
    upline_user?: {
        name: string;
        username: string;
        whatsapp: string;
    };
    beneficiary_user?: {
        name: string;
        username: string;
    };
    service?: {
        name: string;
        code: string;
    };
}

interface DashboardProps {
    metrics: MetricData;
    services: ServiceItem[];
    recent_commissions: CommissionItem[];
    is_super_admin: boolean;
}

export default function MarketingDashboard({
    metrics,
    services = [],
    recent_commissions = [],
    is_super_admin,
}: DashboardProps) {
    const [filterType, setFilterType] = useState<'ALL' | 'INTERNAL' | 'EXTERNAL'>('ALL');

    const filteredServices = services.filter((s) => {
        if (filterType === 'ALL') return true;
        return s.service_type === filterType;
    });

    const formatRupiah = (val: number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(val);
    };

    return (
        <div className="bg-background text-on-background min-h-screen relative overflow-x-hidden font-body-md select-none pb-20">
            <Head title="Portal Admin Marketing & Affiliate - PitiCoin.id" />

            {/* Header Navigation */}
            <header className="bg-obsidian-surface/90 backdrop-blur-xl border-b border-glass-stroke px-4 md:px-8 py-3.5 sticky top-0 z-30 flex items-center justify-between">
                <div className="flex items-center gap-3">
                    <Link
                        href="/dashboard"
                        className="p-2 rounded-full hover:bg-white/5 text-on-surface-variant hover:text-primary-container transition-colors flex items-center gap-1.5"
                    >
                        <span className="material-symbols-outlined text-xl">arrow_back</span>
                        <span className="text-xs font-semibold uppercase tracking-wider hidden sm:inline">Dasbor Utama</span>
                    </Link>
                    <div className="h-4 w-px bg-glass-stroke" />
                    <div>
                        <div className="flex items-center gap-2">
                            <span className="text-[10px] text-primary-container uppercase font-mono font-bold tracking-widest bg-primary-container/20 px-2 py-0.5 rounded border border-primary-container/30">
                                OWNER AFFILIATE PORTAL
                            </span>
                            {is_super_admin && (
                                <span className="text-[9px] bg-secondary/20 text-secondary border border-secondary/30 px-1.5 py-0.2 rounded font-mono font-bold">
                                    SUPERADMIN
                                </span>
                            )}
                        </div>
                        <h1 className="text-sm md:text-base font-bold text-on-surface mt-0.5" style={{ fontFamily: "'Playfair Display', serif" }}>
                            Dasbor Admin Marketing & Produk
                        </h1>
                    </div>
                </div>

                <div className="flex items-center gap-2.5">
                    <Link
                        href="/marketing/transactions"
                        className="px-3 py-2 rounded-xl bg-surface/70 hover:bg-surface-variant text-on-surface text-xs font-semibold border border-glass-stroke transition-all flex items-center gap-1.5"
                    >
                        <span className="material-symbols-outlined text-sm">receipt_long</span>
                        <span className="hidden sm:inline">Riwayat Transaksi</span>
                    </Link>

                    <Link
                        href="/marketing/services/create"
                        className="px-3.5 py-2 rounded-xl bg-gradient-to-r from-amber-500 via-primary-container to-amber-400 text-on-primary text-xs font-bold shadow-md hover:brightness-110 active:scale-95 transition-all flex items-center gap-1.5"
                    >
                        <span className="material-symbols-outlined text-sm">add_circle</span>
                        <span>Tambah Produk / Layanan</span>
                    </Link>
                </div>
            </header>

            <main className="max-w-6xl mx-auto px-4 md:px-8 py-6 space-y-6">
                {/* 4 Financial & Operational Metric Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {/* Metric 1: Total Omzet Terkumpul */}
                    <div className="glass-panel p-4 rounded-2xl border border-glass-stroke bg-surface/50 relative overflow-hidden space-y-2">
                        <div className="flex justify-between items-center text-on-surface-variant">
                            <span className="text-[11px] font-bold uppercase tracking-wider">Total Omzet / Income</span>
                            <span className="material-symbols-outlined text-primary-container text-xl">payments</span>
                        </div>
                        <div className="space-y-0.5">
                            <div className="text-lg md:text-xl font-bold font-mono text-primary-container">
                                {metrics.total_income_gold.toLocaleString('id-ID')} EMAS
                            </div>
                            <div className="text-xs font-mono text-on-surface-variant">
                                + {formatRupiah(metrics.total_income_fiat)}
                            </div>
                        </div>
                        <span className="text-[10px] text-on-surface-variant block border-t border-glass-stroke pt-1.5">
                            Akumulasi pembayaran seluruh pesanan
                        </span>
                    </div>

                    {/* Metric 2: Total Profit Sharing Dibagikan */}
                    <div className="glass-panel p-4 rounded-2xl border border-glass-stroke bg-surface/50 relative overflow-hidden space-y-2">
                        <div className="flex justify-between items-center text-on-surface-variant">
                            <span className="text-[11px] font-bold uppercase tracking-wider">Total Payout Komisi</span>
                            <span className="material-symbols-outlined text-secondary text-xl">share</span>
                        </div>
                        <div className="space-y-0.5">
                            <div className="text-lg md:text-xl font-bold font-mono text-secondary">
                                {metrics.total_payout_gold.toLocaleString('id-ID')} EMAS
                            </div>
                            <div className="text-xs font-mono text-on-surface-variant">
                                + {formatRupiah(metrics.total_payout_fiat)}
                            </div>
                        </div>
                        <span className="text-[10px] text-on-surface-variant block border-t border-glass-stroke pt-1.5">
                            Profit sharing terdistribusi ke upline
                        </span>
                    </div>

                    {/* Metric 3: Total Transaksi Berhasil */}
                    <div className="glass-panel p-4 rounded-2xl border border-glass-stroke bg-surface/50 relative overflow-hidden space-y-2">
                        <div className="flex justify-between items-center text-on-surface-variant">
                            <span className="text-[11px] font-bold uppercase tracking-wider">Transaksi Sukses</span>
                            <span className="material-symbols-outlined text-amber-400 text-xl">shopping_bag</span>
                        </div>
                        <div className="text-2xl font-bold font-mono text-on-surface">
                            {metrics.total_orders} <span className="text-xs font-normal text-on-surface-variant">Pesanan</span>
                        </div>
                        <span className="text-[10px] text-on-surface-variant block border-t border-glass-stroke pt-1.5">
                            Pendaftaran & aktivasi produk aktif
                        </span>
                    </div>

                    {/* Metric 4: Cicilan Berjalan */}
                    <div className="glass-panel p-4 rounded-2xl border border-glass-stroke bg-surface/50 relative overflow-hidden space-y-2">
                        <div className="flex justify-between items-center text-on-surface-variant">
                            <span className="text-[11px] font-bold uppercase tracking-wider">Cicilan Berjalan</span>
                            <span className="material-symbols-outlined text-sky-400 text-xl">hourglass_top</span>
                        </div>
                        <div className="text-2xl font-bold font-mono text-sky-400">
                            {metrics.active_installments} <span className="text-xs font-normal text-on-surface-variant">Akun</span>
                        </div>
                        <span className="text-[10px] text-on-surface-variant block border-t border-glass-stroke pt-1.5">
                            Member dalam tahap pembayaran bertahap
                        </span>
                    </div>
                </div>

                {/* Section 1: Katalog Produk / Layanan */}
                <div className="glass-panel rounded-2xl border border-glass-stroke p-5 space-y-4">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-glass-stroke pb-3">
                        <div>
                            <h2 className="text-base font-bold text-on-surface flex items-center gap-2">
                                <span className="material-symbols-outlined text-primary-container">inventory_2</span>
                                <span>Katalog Produk & Skema Komisi</span>
                            </h2>
                            <p className="text-xs text-on-surface-variant mt-0.5">
                                Atur harga jual dinamis, skema kedalaman sharing profit, dan opsi pembayaran bertahap.
                            </p>
                        </div>

                        {/* Filter Tabs */}
                        <div className="flex items-center gap-1.5 bg-surface/70 p-1 rounded-xl border border-glass-stroke text-xs self-start sm:self-auto">
                            {(['ALL', 'INTERNAL', 'EXTERNAL'] as const).map((t) => (
                                <button
                                    key={t}
                                    onClick={() => setFilterType(t)}
                                    className={`px-3 py-1 rounded-lg font-semibold transition-all ${
                                        filterType === t
                                            ? 'bg-primary-container text-on-primary shadow'
                                            : 'text-on-surface-variant hover:text-on-surface'
                                    }`}
                                >
                                    {t === 'ALL' ? 'Semua' : t === 'INTERNAL' ? 'Internal (Produk)' : 'External (SaaS)'}
                                </button>
                            ))}
                        </div>
                    </div>

                    {filteredServices.length === 0 ? (
                        <div className="p-8 text-center text-xs text-on-surface-variant space-y-2">
                            <span className="material-symbols-outlined text-3xl text-outline">inventory_2</span>
                            <p>Belum ada produk atau layanan yang terdaftar pada kategori ini.</p>
                            <Link
                                href="/marketing/services/create"
                                className="inline-block px-4 py-2 bg-primary-container text-on-primary font-bold rounded-xl text-xs mt-2"
                            >
                                Daftarkan Produk Sekarang
                            </Link>
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {filteredServices.map((svc) => (
                                <div
                                    key={svc.id}
                                    className="p-4 rounded-xl bg-surface/40 border border-glass-stroke hover:border-primary-container/40 transition-all flex flex-col justify-between gap-3 group"
                                >
                                    <div className="space-y-2.5">
                                        <div className="flex items-start justify-between gap-2">
                                            <div className="flex items-center gap-2.5">
                                                <div className="w-10 h-10 rounded-xl bg-primary-container/10 border border-primary-container/30 flex items-center justify-center text-primary-container shrink-0">
                                                    <span className="material-symbols-outlined text-xl">{svc.icon || 'hub'}</span>
                                                </div>
                                                <div>
                                                    <div className="flex items-center gap-1.5 flex-wrap">
                                                        <strong className="text-xs md:text-sm text-on-surface font-bold">
                                                            {svc.name}
                                                        </strong>
                                                        <span
                                                            className={`text-[9px] font-mono font-bold px-1.5 py-0.2 rounded border ${
                                                                svc.service_type === 'INTERNAL'
                                                                    ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30'
                                                                    : 'bg-sky-500/20 text-sky-400 border-sky-500/30'
                                                            }`}
                                                        >
                                                            {svc.service_type}
                                                        </span>
                                                    </div>
                                                    <span className="text-[10px] text-on-surface-variant">
                                                        Kategori: <strong>{svc.category}</strong>
                                                    </span>
                                                </div>
                                            </div>

                                            <span
                                                className={`text-[9px] font-bold px-2 py-0.5 rounded-full ${
                                                    svc.is_active
                                                        ? 'bg-emerald-500/20 text-emerald-400'
                                                        : 'bg-surface-variant text-on-surface-variant'
                                                }`}
                                            >
                                                {svc.is_active ? 'AKTIF' : 'NONAKTIF'}
                                            </span>
                                        </div>

                                        <p className="text-[11px] text-on-surface-variant line-clamp-2 leading-relaxed">
                                            {svc.description}
                                        </p>

                                        {/* Pricing & Installment Badges */}
                                        <div className="flex items-center gap-2 flex-wrap text-xs font-mono pt-1">
                                            <div className="px-2 py-1 rounded bg-surface/80 border border-glass-stroke text-primary-container font-bold">
                                                {svc.price_gold.toLocaleString('id-ID')} EMAS
                                            </div>
                                            <div className="px-2 py-1 rounded bg-surface/80 border border-glass-stroke text-on-surface-variant">
                                                {formatRupiah(svc.price_fiat)}
                                            </div>
                                            {svc.allows_installments && (
                                                <span className="text-[9px] bg-amber-500/20 text-amber-300 border border-amber-500/30 px-2 py-0.5 rounded font-bold">
                                                    Bisa Dicicil (DP: {svc.min_down_payment_gold || 0} Emas)
                                                </span>
                                            )}
                                        </div>

                                        {/* Commission Multi-Level Summary */}
                                        <div className="p-2.5 rounded-lg bg-surface/80 border border-glass-stroke text-[11px] font-mono space-y-1">
                                            <div className="flex justify-between items-center text-on-surface-variant text-[10px]">
                                                <span>KEDALAMAN AFFILIATE:</span>
                                                <strong className="text-secondary">
                                                    {svc.commission_rules?.depth || svc.commission_rules?.levels?.length || 1} Level
                                                </strong>
                                            </div>
                                            <div className="flex items-center gap-1.5 flex-wrap pt-0.5">
                                                {svc.commission_rules?.levels?.map((lvl) => (
                                                    <span key={lvl.level} className="text-[9px] bg-secondary/15 text-secondary px-1.5 py-0.5 rounded border border-secondary/20 font-bold">
                                                        L{lvl.level}: {lvl.percentage}%
                                                    </span>
                                                ))}
                                            </div>
                                        </div>
                                    </div>

                                    {/* Action Buttons */}
                                    <div className="flex items-center justify-between border-t border-glass-stroke pt-3 mt-1">
                                        <div className="text-[10px] text-on-surface-variant font-mono">
                                            <span>{svc.orders_count || 0} Terjual</span>
                                        </div>

                                        <div className="flex items-center gap-2">
                                            <Link
                                                href={`/services/${svc.code}/onboard`}
                                                className="px-2.5 py-1 rounded-lg bg-surface/60 hover:bg-white/5 text-on-surface text-xs font-semibold border border-glass-stroke transition-all"
                                            >
                                                Pratinjau
                                            </Link>
                                            <Link
                                                href={`/marketing/services/${svc.id}/edit`}
                                                className="px-3 py-1 rounded-lg bg-primary-container text-on-primary text-xs font-bold hover:brightness-110 active:scale-95 transition-all flex items-center gap-1"
                                            >
                                                <span className="material-symbols-outlined text-xs">edit</span>
                                                <span>Edit Harga & Komisi</span>
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {/* Section 2: Real-time Profit Sharing Log */}
                <div className="glass-panel rounded-2xl border border-glass-stroke p-5 space-y-4">
                    <div className="flex justify-between items-center border-b border-glass-stroke pb-3">
                        <div>
                            <h2 className="text-base font-bold text-on-surface flex items-center gap-2">
                                <span className="material-symbols-outlined text-secondary">account_tree</span>
                                <span>Distribusi Profit Sharing Terakhir (Real-Time)</span>
                            </h2>
                            <p className="text-xs text-on-surface-variant mt-0.5">
                                Log mutasi komisi yang otomatis ditransfer ke dompet para saudagar di jaringan.
                            </p>
                        </div>
                        <Link
                            href="/marketing/transactions"
                            className="text-xs text-primary-container hover:underline font-bold"
                        >
                            Lihat Semua →
                        </Link>
                    </div>

                    {recent_commissions.length === 0 ? (
                        <div className="p-6 text-center text-xs text-on-surface-variant">
                            Belum ada pembagian komisi profit sharing yang tercatat.
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-xs font-mono">
                                <thead>
                                    <tr className="border-b border-glass-stroke text-on-surface-variant text-[10px] uppercase">
                                        <th className="py-2 px-3">Upline Penerima</th>
                                        <th className="py-2 px-3">Mitra Pendaftar</th>
                                        <th className="py-2 px-3">Layanan</th>
                                        <th className="py-2 px-3">Level</th>
                                        <th className="py-2 px-3">Porsi</th>
                                        <th className="py-2 px-3">Nominal Komisi</th>
                                        <th className="py-2 px-3">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {recent_commissions.map((c) => (
                                        <tr key={c.id} className="border-b border-glass-stroke/50 hover:bg-white/5 transition-colors">
                                            <td className="py-2.5 px-3">
                                                <strong className="text-on-surface block">{c.upline_user?.name || 'Upline'}</strong>
                                                <span className="text-[10px] text-on-surface-variant">{c.upline_user?.whatsapp}</span>
                                            </td>
                                            <td className="py-2.5 px-3 text-on-surface">
                                                {c.beneficiary_user?.name || 'Mitra'}
                                            </td>
                                            <td className="py-2.5 px-3">
                                                <span className="text-primary-container font-semibold">{c.service?.name}</span>
                                            </td>
                                            <td className="py-2.5 px-3">
                                                <span className="bg-secondary/20 text-secondary px-1.5 py-0.5 rounded font-bold text-[10px]">
                                                    Level {c.level}
                                                </span>
                                            </td>
                                            <td className="py-2.5 px-3 text-on-surface-variant">
                                                {c.percentage}%
                                            </td>
                                            <td className="py-2.5 px-3">
                                                {c.currency === 'GOLD' ? (
                                                    <strong className="text-primary-container">{c.amount_gold} EMAS</strong>
                                                ) : (
                                                    <strong className="text-emerald-400">{formatRupiah(c.amount_fiat)}</strong>
                                                )}
                                            </td>
                                            <td className="py-2.5 px-3 text-on-surface-variant text-[10px]">
                                                {new Date(c.created_at).toLocaleDateString('id-ID', {
                                                    day: 'numeric',
                                                    month: 'short',
                                                    hour: '2-digit',
                                                    minute: '2-digit',
                                                })}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </main>
        </div>
    );
}
