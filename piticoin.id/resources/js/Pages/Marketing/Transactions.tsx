import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';

interface OrderItem {
    id: string;
    order_number: string;
    service_id: string;
    user_id: string;
    registered_by_user_id: string;
    payment_type: 'FULL' | 'INSTALLMENT';
    currency: string;
    total_amount_gold: number;
    total_amount_fiat: number;
    paid_amount_gold: number;
    paid_amount_fiat: number;
    remaining_amount_gold: number;
    remaining_amount_fiat: number;
    status: string;
    created_at: string;
    service?: {
        name: string;
        code: string;
    };
    user?: {
        name: string;
        username: string;
        whatsapp: string;
    };
    registered_by?: {
        name: string;
        username: string;
    };
    installments?: Array<{
        id: string;
        installment_number: number;
        currency: string;
        amount_gold: number;
        amount_fiat: number;
        status: string;
        paid_at: string;
        reference_note?: string;
    }>;
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

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface TransactionsProps {
    orders: PaginatedData<OrderItem>;
    commissions: PaginatedData<CommissionItem>;
}

export default function Transactions({ orders, commissions }: TransactionsProps) {
    const [activeTab, setActiveTab] = useState<'ORDERS' | 'COMMISSIONS'>('ORDERS');
    const [selectedOrder, setSelectedOrder] = useState<OrderItem | null>(null);

    const formatRupiah = (val: number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(val || 0);
    };

    const formatDate = (dateString: string) => {
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        } catch {
            return dateString;
        }
    };

    return (
        <div className="bg-background text-on-background min-h-screen relative overflow-x-hidden font-body-md select-none pb-24">
            <Head title="Buku Besar Transaksi & Payout Komisi - Admin Marketing" />

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
                            <span className="material-symbols-outlined text-amber-400 text-lg">receipt_long</span>
                            Buku Besar Transaksi & Payout Komisi
                        </h1>
                        <p className="text-[11px] text-on-surface-variant font-mono">
                            Audit Trail Double-Entry Ledger Finansial Ekosistem
                        </p>
                    </div>
                </div>

                <div className="flex items-center gap-2">
                    <Link
                        href="/marketing/services/create"
                        className="px-3.5 py-2 rounded-xl bg-primary-container/10 border border-primary-container/30 text-primary-container text-xs font-bold hover:bg-primary-container/20 flex items-center gap-1.5 transition-all"
                    >
                        <span className="material-symbols-outlined text-sm">add_circle</span>
                        <span className="hidden sm:inline">Tambah Produk</span>
                    </Link>
                </div>
            </header>

            <main className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
                {/* Navigation Tabs */}
                <div className="flex items-center gap-3 border-b border-glass-stroke pb-3">
                    <button
                        onClick={() => setActiveTab('ORDERS')}
                        className={`px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer ${
                            activeTab === 'ORDERS'
                                ? 'bg-primary-container text-primary-on shadow-lg shadow-primary-container/20'
                                : 'text-on-surface-variant hover:text-white hover:bg-white/5'
                        }`}
                    >
                        <span className="material-symbols-outlined text-base">shopping_cart</span>
                        Pesanan & Cicilan
                        <span className="px-1.5 py-0.5 rounded-full text-[10px] font-mono bg-black/30">
                            {orders.total}
                        </span>
                    </button>

                    <button
                        onClick={() => setActiveTab('COMMISSIONS')}
                        className={`px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer ${
                            activeTab === 'COMMISSIONS'
                                ? 'bg-purple-600 text-white shadow-lg shadow-purple-600/20'
                                : 'text-on-surface-variant hover:text-white hover:bg-white/5'
                        }`}
                    >
                        <span className="material-symbols-outlined text-base">account_tree</span>
                        Distribusi Payout Komisi
                        <span className="px-1.5 py-0.5 rounded-full text-[10px] font-mono bg-black/30">
                            {commissions.total}
                        </span>
                    </button>
                </div>

                {/* TAB 1: ORDERS & INSTALLMENTS */}
                {activeTab === 'ORDERS' && (
                    <div className="space-y-4">
                        <div className="bg-obsidian-surface/60 border border-glass-stroke rounded-2xl overflow-hidden backdrop-blur-md shadow-2xl">
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-xs">
                                    <thead>
                                        <tr className="bg-black/30 text-on-surface-variant border-b border-glass-stroke font-mono uppercase text-[10px]">
                                            <th className="px-4 py-3.5">Order No / Waktu</th>
                                            <th className="px-4 py-3.5">Layanan / Produk</th>
                                            <th className="px-4 py-3.5">Peserta (User)</th>
                                            <th className="px-4 py-3.5">Metode Bayar</th>
                                            <th className="px-4 py-3.5 text-right">Total Tagihan</th>
                                            <th className="px-4 py-3.5 text-right">Sudah Dibayar</th>
                                            <th className="px-4 py-3.5 text-center">Status</th>
                                            <th className="px-4 py-3.5 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-glass-stroke/50">
                                        {orders.data.length === 0 ? (
                                            <tr>
                                                <td colSpan={8} className="px-4 py-12 text-center text-on-surface-variant">
                                                    <span className="material-symbols-outlined text-3xl mb-1 block opacity-40">
                                                        inbox
                                                    </span>
                                                    Belum ada pesanan terdaftar pada produk Anda.
                                                </td>
                                            </tr>
                                        ) : (
                                            orders.data.map((order) => {
                                                const isInstallment = order.payment_type === 'INSTALLMENT';
                                                const isCompleted = order.status === 'COMPLETED';

                                                return (
                                                    <tr
                                                        key={order.id}
                                                        className="hover:bg-white/[0.02] transition-colors"
                                                    >
                                                        <td className="px-4 py-3.5">
                                                            <span className="font-mono font-bold text-white block">
                                                                {order.order_number}
                                                            </span>
                                                            <span className="text-[10px] text-on-surface-variant">
                                                                {formatDate(order.created_at)}
                                                            </span>
                                                        </td>
                                                        <td className="px-4 py-3.5">
                                                            <span className="font-semibold text-white block">
                                                                {order.service?.name || '-'}
                                                            </span>
                                                            <span className="text-[10px] font-mono text-primary-container">
                                                                {order.service?.code}
                                                            </span>
                                                        </td>
                                                        <td className="px-4 py-3.5">
                                                            <span className="font-semibold text-white block">
                                                                {order.user?.name || '-'}
                                                            </span>
                                                            <span className="text-[10px] text-on-surface-variant font-mono">
                                                                @{order.user?.username || order.user?.whatsapp}
                                                            </span>
                                                        </td>
                                                        <td className="px-4 py-3.5">
                                                            <span
                                                                className={`px-2 py-0.5 rounded text-[10px] font-bold font-mono inline-block ${
                                                                    isInstallment
                                                                        ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30'
                                                                        : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'
                                                                }`}
                                                            >
                                                                {isInstallment ? 'CICILAN' : 'LUNAS (FULL)'}
                                                            </span>
                                                        </td>
                                                        <td className="px-4 py-3.5 text-right font-mono">
                                                            {order.currency === 'GOLD' ? (
                                                                <span className="text-amber-400 font-bold">
                                                                    {Number(order.total_amount_gold).toLocaleString('id-ID')} Emas
                                                                </span>
                                                            ) : (
                                                                <span className="text-emerald-400 font-bold">
                                                                    {formatRupiah(Number(order.total_amount_fiat))}
                                                                </span>
                                                            )}
                                                        </td>
                                                        <td className="px-4 py-3.5 text-right font-mono">
                                                            {order.currency === 'GOLD' ? (
                                                                <span className="text-white font-bold">
                                                                    {Number(order.paid_amount_gold).toLocaleString('id-ID')} Emas
                                                                </span>
                                                            ) : (
                                                                <span className="text-white font-bold">
                                                                    {formatRupiah(Number(order.paid_amount_fiat))}
                                                                </span>
                                                            )}
                                                            {isInstallment && !isCompleted && (
                                                                <span className="text-[10px] text-red-400 block font-mono">
                                                                    Sisa:{' '}
                                                                    {order.currency === 'GOLD'
                                                                        ? `${Number(order.remaining_amount_gold).toLocaleString('id-ID')} Emas`
                                                                        : formatRupiah(Number(order.remaining_amount_fiat))}
                                                                </span>
                                                            )}
                                                        </td>
                                                        <td className="px-4 py-3.5 text-center">
                                                            <span
                                                                className={`px-2 py-0.5 rounded-full text-[10px] font-bold font-mono uppercase ${
                                                                    isCompleted
                                                                        ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                                                                        : order.status === 'PARTIALLY_PAID'
                                                                        ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
                                                                        : 'bg-white/10 text-on-surface-variant'
                                                                }`}
                                                            >
                                                                {order.status}
                                                            </span>
                                                        </td>
                                                        <td className="px-4 py-3.5 text-center">
                                                            <button
                                                                onClick={() => setSelectedOrder(order)}
                                                                className="px-2.5 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-on-surface-variant hover:text-white border border-glass-stroke text-[11px] font-semibold transition-all cursor-pointer inline-flex items-center gap-1"
                                                            >
                                                                <span className="material-symbols-outlined text-sm">visibility</span>
                                                                Detail
                                                            </button>
                                                        </td>
                                                    </tr>
                                                );
                                            })
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination Links */}
                            {orders.links.length > 3 && (
                                <div className="p-4 border-t border-glass-stroke flex items-center justify-between text-xs">
                                    <span className="text-on-surface-variant">
                                        Menampilkan halaman {orders.current_page} dari {orders.last_page}
                                    </span>
                                    <div className="flex items-center gap-1">
                                        {orders.links.map((lnk, i) => (
                                            <Link
                                                key={i}
                                                href={lnk.url || '#'}
                                                className={`px-3 py-1.5 rounded-lg border text-xs font-mono transition-colors ${
                                                    lnk.active
                                                        ? 'bg-primary-container text-primary-on border-primary-container font-bold'
                                                        : lnk.url
                                                        ? 'bg-obsidian-surface border-glass-stroke text-on-surface-variant hover:text-white'
                                                        : 'opacity-30 pointer-events-none border-transparent'
                                                }`}
                                                dangerouslySetInnerHTML={{ __html: lnk.label }}
                                            />
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                )}

                {/* TAB 2: COMMISSIONS PAYOUT LEDGER */}
                {activeTab === 'COMMISSIONS' && (
                    <div className="space-y-4">
                        <div className="bg-obsidian-surface/60 border border-glass-stroke rounded-2xl overflow-hidden backdrop-blur-md shadow-2xl">
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-xs">
                                    <thead>
                                        <tr className="bg-black/30 text-on-surface-variant border-b border-glass-stroke font-mono uppercase text-[10px]">
                                            <th className="px-4 py-3.5">Waktu Payout</th>
                                            <th className="px-4 py-3.5">Penerima Komisi (Upline)</th>
                                            <th className="px-4 py-3.5">Sumber Peserta (Downline)</th>
                                            <th className="px-4 py-3.5">Layanan / Produk</th>
                                            <th className="px-4 py-3.5 text-center">Generasi</th>
                                            <th className="px-4 py-3.5 text-center">Rate (%)</th>
                                            <th className="px-4 py-3.5 text-right">Nominal Komisi</th>
                                            <th className="px-4 py-3.5 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-glass-stroke/50">
                                        {commissions.data.length === 0 ? (
                                            <tr>
                                                <td colSpan={8} className="px-4 py-12 text-center text-on-surface-variant">
                                                    <span className="material-symbols-outlined text-3xl mb-1 block opacity-40">
                                                        account_tree
                                                    </span>
                                                    Belum ada komisi afiliasi yang didistribusikan.
                                                </td>
                                            </tr>
                                        ) : (
                                            commissions.data.map((comm) => (
                                                <tr
                                                    key={comm.id}
                                                    className="hover:bg-white/[0.02] transition-colors"
                                                >
                                                    <td className="px-4 py-3.5 font-mono text-[11px] text-on-surface-variant">
                                                        {formatDate(comm.created_at)}
                                                    </td>
                                                    <td className="px-4 py-3.5">
                                                        <span className="font-semibold text-white block">
                                                            {comm.upline_user?.name || '-'}
                                                        </span>
                                                        <span className="text-[10px] text-on-surface-variant font-mono">
                                                            @{comm.upline_user?.username || comm.upline_user?.whatsapp}
                                                        </span>
                                                    </td>
                                                    <td className="px-4 py-3.5">
                                                        <span className="font-semibold text-white block">
                                                            {comm.beneficiary_user?.name || '-'}
                                                        </span>
                                                        <span className="text-[10px] text-on-surface-variant font-mono">
                                                            @{comm.beneficiary_user?.username}
                                                        </span>
                                                    </td>
                                                    <td className="px-4 py-3.5">
                                                        <span className="font-semibold text-white block">
                                                            {comm.service?.name || '-'}
                                                        </span>
                                                    </td>
                                                    <td className="px-4 py-3.5 text-center">
                                                        <span className="px-2 py-0.5 rounded bg-purple-500/20 text-purple-300 font-mono font-bold text-[10px] border border-purple-500/30">
                                                            Level {comm.level}
                                                        </span>
                                                    </td>
                                                    <td className="px-4 py-3.5 text-center font-mono font-bold text-purple-300">
                                                        {comm.percentage}%
                                                    </td>
                                                    <td className="px-4 py-3.5 text-right font-mono">
                                                        {comm.currency === 'GOLD' ? (
                                                            <span className="text-amber-400 font-bold">
                                                                +{Number(comm.amount_gold).toFixed(2)} Emas
                                                            </span>
                                                        ) : (
                                                            <span className="text-emerald-400 font-bold">
                                                                +{formatRupiah(Number(comm.amount_fiat))}
                                                            </span>
                                                        )}
                                                    </td>
                                                    <td className="px-4 py-3.5 text-center">
                                                        <span className="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                            {comm.status}
                                                        </span>
                                                    </td>
                                                </tr>
                                            ))
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination Links */}
                            {commissions.links.length > 3 && (
                                <div className="p-4 border-t border-glass-stroke flex items-center justify-between text-xs">
                                    <span className="text-on-surface-variant">
                                        Menampilkan halaman {commissions.current_page} dari {commissions.last_page}
                                    </span>
                                    <div className="flex items-center gap-1">
                                        {commissions.links.map((lnk, i) => (
                                            <Link
                                                key={i}
                                                href={lnk.url || '#'}
                                                className={`px-3 py-1.5 rounded-lg border text-xs font-mono transition-colors ${
                                                    lnk.active
                                                        ? 'bg-purple-600 text-white border-purple-600 font-bold'
                                                        : lnk.url
                                                        ? 'bg-obsidian-surface border-glass-stroke text-on-surface-variant hover:text-white'
                                                        : 'opacity-30 pointer-events-none border-transparent'
                                                }`}
                                                dangerouslySetInnerHTML={{ __html: lnk.label }}
                                            />
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                )}
            </main>

            {/* Modal Detail Cicilan / Installment History */}
            {selectedOrder && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="bg-obsidian-surface border border-glass-stroke rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5">
                        <div className="flex items-center justify-between border-b border-glass-stroke pb-3">
                            <div>
                                <h3 className="text-sm font-bold text-white flex items-center gap-2">
                                    <span className="material-symbols-outlined text-primary-container text-base">
                                        receipt
                                    </span>
                                    Riwayat Pembayaran Cicilan
                                </h3>
                                <p className="text-[11px] text-on-surface-variant font-mono">
                                    {selectedOrder.order_number} ({selectedOrder.service?.name})
                                </p>
                            </div>
                            <button
                                onClick={() => setSelectedOrder(null)}
                                className="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 text-on-surface-variant hover:text-white flex items-center justify-center transition-colors cursor-pointer"
                            >
                                <span className="material-symbols-outlined text-lg">close</span>
                            </button>
                        </div>

                        {/* Order Summary */}
                        <div className="p-4 bg-black/40 border border-glass-stroke rounded-xl grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <span className="text-[10px] uppercase font-mono text-on-surface-variant block">
                                    Peserta
                                </span>
                                <span className="font-semibold text-white block">{selectedOrder.user?.name}</span>
                                <span className="text-[10px] text-on-surface-variant font-mono">
                                    {selectedOrder.user?.whatsapp}
                                </span>
                            </div>
                            <div className="text-right">
                                <span className="text-[10px] uppercase font-mono text-on-surface-variant block">
                                    Skema Pembayaran
                                </span>
                                <span className="font-bold text-primary-container block">
                                    {selectedOrder.payment_type === 'INSTALLMENT' ? 'BERTAHAP (CICILAN)' : 'BAYAR LUNAS'}
                                </span>
                                <span className="text-[10px] font-mono text-emerald-400">
                                    Status: {selectedOrder.status}
                                </span>
                            </div>
                        </div>

                        {/* Installments List */}
                        <div>
                            <span className="text-xs font-bold text-on-surface-variant uppercase tracking-wider block mb-2">
                                Daftar Transaksi Pembayaran ({selectedOrder.installments?.length || 0})
                            </span>
                            <div className="space-y-2 max-h-60 overflow-y-auto pr-1">
                                {(selectedOrder.installments || []).map((inst) => (
                                    <div
                                        key={inst.id}
                                        className="p-3 bg-white/[0.02] border border-glass-stroke rounded-xl flex items-center justify-between text-xs"
                                    >
                                        <div>
                                            <span className="font-bold text-white block">
                                                Cicilan #{inst.installment_number}
                                            </span>
                                            <span className="text-[10px] text-on-surface-variant font-mono">
                                                {formatDate(inst.paid_at || '')}
                                            </span>
                                        </div>
                                        <div className="text-right font-mono">
                                            {inst.currency === 'GOLD' ? (
                                                <span className="text-amber-400 font-bold block">
                                                    {Number(inst.amount_gold).toLocaleString('id-ID')} Emas
                                                </span>
                                            ) : (
                                                <span className="text-emerald-400 font-bold block">
                                                    {formatRupiah(Number(inst.amount_fiat))}
                                                </span>
                                            )}
                                            <span className="text-[10px] text-emerald-400">BERHASIL DIBAGIKAN</span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="pt-2 flex justify-end">
                            <button
                                onClick={() => setSelectedOrder(null)}
                                className="px-5 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-white text-xs font-bold transition-all cursor-pointer"
                            >
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
