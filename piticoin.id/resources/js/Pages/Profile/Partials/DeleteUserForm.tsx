import React, { useState, useRef } from 'react';
import { useForm } from '@inertiajs/react';
import { Trash2, AlertTriangle, ShieldCheck } from 'lucide-react';
import Modal from '@/Components/Modal';

export default function DeleteUserForm() {
    const [confirmingUserDeletion, setConfirmingUserDeletion] = useState(false);
    const passwordInput = useRef<HTMLInputElement>(null);

    const {
        data,
        setData,
        delete: destroy,
        processing,
        reset,
        errors,
        clearErrors,
    } = useForm({
        password: '',
    });

    const confirmUserDeletion = () => {
        setConfirmingUserDeletion(true);
    };

    const deleteUser = (e: React.FormEvent) => {
        e.preventDefault();

        destroy(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => passwordInput.current?.focus(),
            onFinish: () => reset(),
        });
    };

    const closeModal = () => {
        setConfirmingUserDeletion(false);
        clearErrors();
        reset();
    };

    return (
        <div className="glass-panel p-6 sm:p-8 rounded-2xl border border-red-500/20 bg-red-500/[0.02] space-y-5">
            <div className="border-b border-white/10 pb-4 flex items-center justify-between">
                <div>
                    <h3 className="text-lg font-bold text-red-400 flex items-center gap-2">
                        <Trash2 size={20} />
                        <span>Penutupan Akun (Hak Penghapusan Data UU PDP)</span>
                    </h3>
                    <p className="text-xs text-gray-400 mt-1">
                        Sesuai Pasal 8 UU Perlindungan Data Pribadi No. 27 Tahun 2022, Anda berhak mengakhiri akun dan meminta pemusnahan identitas pribadi Anda.
                    </p>
                </div>

                <button
                    type="button"
                    onClick={confirmUserDeletion}
                    className="px-4 py-2 rounded-xl bg-red-600/20 hover:bg-red-600/30 border border-red-500/40 text-red-400 text-xs font-semibold transition-all shrink-0"
                >
                    Tutup & Hapus Akun
                </button>
            </div>

            <p className="text-xs text-gray-400 leading-relaxed">
                Setelah akun Anda ditutup, seluruh saldo koin yang tersisa akan dibekukan dan catatan transaksi ledger akan dianonimisasi demi kepatuhan audit finansial negara.
            </p>

            {/* Confirmation Modal */}
            <Modal show={confirmingUserDeletion} onClose={closeModal}>
                <form onSubmit={deleteUser} className="p-6 bg-[#1A1A1A] text-[#e5e2e1] border border-red-500/30 rounded-2xl space-y-4">
                    <div className="flex items-center gap-2.5 text-red-400">
                        <AlertTriangle size={22} />
                        <h4 className="text-base font-bold text-white">
                            Konfirmasi Penutupan Akun Permanen
                        </h4>
                    </div>

                    <p className="text-xs text-gray-300 leading-relaxed">
                        Apakah Anda yakin ingin menghapus akun ini secara permanen? Masukkan kata sandi akun Anda untuk mengonfirmasi permintaan penghapusan.
                    </p>

                    <div className="space-y-1.5">
                        <label className="text-xs font-semibold text-gray-300">
                            Kata Sandi Akun
                        </label>
                        <input
                            type="password"
                            ref={passwordInput}
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            placeholder="••••••••"
                            required
                            className="w-full px-4 py-2.5 rounded-xl bg-black/50 border border-white/20 text-white text-sm focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500"
                        />
                        {errors.password && (
                            <p className="text-xs text-red-400">{errors.password}</p>
                        )}
                    </div>

                    <div className="flex justify-end gap-2 pt-2 border-t border-white/10">
                        <button
                            type="button"
                            onClick={closeModal}
                            className="px-4 py-2 rounded-xl text-xs font-medium text-gray-400 hover:text-white"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="px-4 py-2 rounded-xl text-xs font-bold bg-red-600 hover:bg-red-500 text-white transition-all shadow-md"
                        >
                            {processing ? 'Menghapus...' : 'Ya, Tutup Akun Saya'}
                        </button>
                    </div>
                </form>
            </Modal>
        </div>
    );
}
