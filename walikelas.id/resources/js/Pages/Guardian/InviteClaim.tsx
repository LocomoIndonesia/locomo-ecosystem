import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';

interface InviteClaimProps {
    raw_token: string;
    learner_name: string;
    program_title: string;
}

export default function InviteClaim({ raw_token, learner_name, program_title }: InviteClaimProps) {
    const [parentName, setParentName] = useState('');
    const [phone, setPhone] = useState('+62');
    const [relationType, setRelationType] = useState('parent');
    const [consentAgreed, setConsentAgreed] = useState(true);
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);
        router.post(`/guardian/invite/${raw_token}`, {
            parent_name: parentName,
            phone_e164: phone,
            relation_type: relationType,
        }, {
            onFinish: () => setIsSubmitting(false),
        });
    };

    return (
        <div style={{ minHeight: '100vh', backgroundColor: '#f8fafc', display: 'flex', flexDirection: 'column' }}>
            <Head title={`Undangan Perwalian: ${learner_name}`} />

            {/* Header */}
            <header style={{ backgroundColor: '#fff', borderBottom: '1px solid var(--slate-200)', padding: '1rem' }}>
                <div style={{ maxWidth: '480px', margin: '0 auto', display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                    <div style={{ width: '32px', height: '32px', borderRadius: '8px', background: 'linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', fontWeight: 800 }}>
                        W
                    </div>
                    <span style={{ fontWeight: 800, fontSize: '1.125rem', color: 'var(--slate-900)' }}>WaliKelas.id</span>
                </div>
            </header>

            <main style={{ flex: 1, padding: '2rem 1rem', display: 'flex', justifyContent: 'center' }}>
                <div style={{ width: '100%', maxWidth: '480px' }}>
                    <div className="card" style={{ padding: '2rem', boxShadow: 'var(--shadow-md)' }}>
                        <div style={{ textAlign: 'center', marginBottom: '1.5rem' }}>
                            <div style={{ fontSize: '2.5rem', marginBottom: '0.5rem' }}>👨‍👩‍👧</div>
                            <h1 style={{ fontSize: '1.375rem', fontWeight: 800, color: 'var(--slate-900)' }}>
                                Undangan Perwalian Murid
                            </h1>
                            <p style={{ fontSize: '0.875rem', color: 'var(--slate-600)', marginTop: '0.25rem' }}>
                                Hubungkan akun Anda untuk mendampingi <strong>{learner_name}</strong> pada program <strong>"{program_title}"</strong>.
                            </p>
                        </div>

                        <form onSubmit={handleSubmit}>
                            <div style={{ marginBottom: '1.25rem' }}>
                                <label style={{ display: 'block', fontSize: '0.875rem', fontWeight: 600, color: 'var(--slate-700)', marginBottom: '0.375rem' }}>
                                    Nama Lengkap Orang Tua / Wali:
                                </label>
                                <input
                                    type="text"
                                    value={parentName}
                                    onChange={(e) => setParentName(e.target.value)}
                                    placeholder="Contoh: Ibu Ratna"
                                    required
                                    style={{ width: '100%', padding: '0.625rem', borderRadius: 'var(--radius-md)', border: '1px solid var(--slate-300)', fontSize: '0.9375rem' }}
                                />
                            </div>

                            <div style={{ marginBottom: '1.25rem' }}>
                                <label style={{ display: 'block', fontSize: '0.875rem', fontWeight: 600, color: 'var(--slate-700)', marginBottom: '0.375rem' }}>
                                    Nomor WhatsApp (E.164):
                                </label>
                                <input
                                    type="text"
                                    value={phone}
                                    onChange={(e) => setPhone(e.target.value)}
                                    placeholder="+628123456789"
                                    required
                                    style={{ width: '100%', padding: '0.625rem', borderRadius: 'var(--radius-md)', border: '1px solid var(--slate-300)', fontSize: '0.9375rem' }}
                                />
                            </div>

                            <div style={{ marginBottom: '1.25rem' }}>
                                <label style={{ display: 'block', fontSize: '0.875rem', fontWeight: 600, color: 'var(--slate-700)', marginBottom: '0.375rem' }}>
                                    Hubungan Keluarga:
                                </label>
                                <select
                                    value={relationType}
                                    onChange={(e) => setRelationType(e.target.value)}
                                    style={{ width: '100%', padding: '0.625rem', borderRadius: 'var(--radius-md)', border: '1px solid var(--slate-300)', fontSize: '0.875rem' }}
                                >
                                    <option value="parent">Orang Tua Kandung (Ayah/Ibu)</option>
                                    <option value="legal_guardian">Wali Sah / Keluarga</option>
                                    <option value="caregiver">Pengasuh / Pendamping Rumah</option>
                                </select>
                            </div>

                            {/* PDP Consent Checkbox */}
                            <div style={{ backgroundColor: 'var(--slate-50)', padding: '0.875rem', borderRadius: 'var(--radius-md)', border: '1px solid var(--slate-200)', marginBottom: '1.5rem' }}>
                                <label style={{ display: 'flex', alignItems: 'flex-start', gap: '0.5rem', cursor: 'pointer', fontSize: '0.8125rem', color: 'var(--slate-700)', lineHeight: 1.5 }}>
                                    <input
                                        type="checkbox"
                                        checked={consentAgreed}
                                        onChange={(e) => setConsentAgreed(e.target.checked)}
                                        style={{ marginTop: '2px' }}
                                    />
                                    <span>
                                        Saya menyetujui ananda berpartisipasi dalam misi membaca WaliKelas.id dan menerima laporan berkala perkembangan literasi (PDP Compliance v1.0).
                                    </span>
                                </label>
                            </div>

                            <button
                                type="submit"
                                disabled={isSubmitting || !consentAgreed || !parentName.trim()}
                                className="btn btn-primary"
                                style={{ width: '100%', padding: '0.875rem' }}
                            >
                                {isSubmitting ? 'Menghubungkan...' : 'Konfirmasi & Masuk Portal Orang Tua →'}
                            </button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    );
}
