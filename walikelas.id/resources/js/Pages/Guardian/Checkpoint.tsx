import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';

interface CheckpointProps {
    assignment: {
        id: string;
        status: string;
        mission_title: string;
        learner_name: string;
        program_title: string;
        offline_guide?: {
            title?: string;
            instructions?: string;
            guardian_response_options?: Array<{ code: string; label: string; description: string }>;
        };
    };
}

export default function Checkpoint({ assignment }: CheckpointProps) {
    const [selectedResponse, setSelectedResponse] = useState<'done' | 'not_yet' | 'need_help'>('done');
    const [note, setNote] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isConfirmed, setIsConfirmed] = useState(assignment.status === 'submitted');

    const options = assignment.offline_guide?.guardian_response_options || [
        { code: 'done', label: 'Selesai', description: 'Anak berhasil menceritakan alur cerita dengan antusias.' },
        { code: 'not_yet', label: 'Belum Selesai', description: 'Belum sempat malam ini, jadwalkan kembali besok.' },
        { code: 'need_help', label: 'Butuh Bantuan Guru', description: 'Anak kesulitan mengingat tokoh cerita, mohon arahan guru.' },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);
        router.post(`/guardian/checkpoint/${assignment.id}`, {
            response: selectedResponse,
            note,
        }, {
            onFinish: () => {
                setIsSubmitting(false);
                setIsConfirmed(true);
            }
        });
    };

    return (
        <div style={{ minHeight: '100vh', backgroundColor: '#f8fafc', display: 'flex', flexDirection: 'column' }}>
            <Head title={`Konfirmasi Tugas Rumah: ${assignment.learner_name}`} />

            {/* Mobile Header */}
            <header style={{ backgroundColor: '#fff', borderBottom: '1px solid var(--slate-200)', padding: '1rem' }}>
                <div style={{ maxWidth: '480px', margin: '0 auto', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                        <div style={{ width: '32px', height: '32px', borderRadius: '8px', background: 'linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', fontWeight: 800 }}>
                            W
                        </div>
                        <span style={{ fontWeight: 800, fontSize: '1.125rem', color: 'var(--slate-900)' }}>WaliKelas.id</span>
                    </div>
                    <span className="badge badge-accent">Portal Orang Tua</span>
                </div>
            </header>

            {/* Content Container */}
            <main style={{ flex: 1, padding: '1.5rem 1rem', display: 'flex', justifyContent: 'center' }}>
                <div style={{ width: '100%', maxWidth: '480px' }}>
                    <div className="card" style={{ padding: '1.5rem', boxShadow: 'var(--shadow-md)' }}>
                        <div style={{ marginBottom: '1.25rem' }}>
                            <span style={{ fontSize: '0.8125rem', color: 'var(--slate-500)', fontWeight: 600 }}>
                                {assignment.program_title}
                            </span>
                            <h1 style={{ fontSize: '1.25rem', fontWeight: 800, color: 'var(--slate-900)', marginTop: '0.25rem' }}>
                                Konfirmasi Tugas Rumah: {assignment.learner_name}
                            </h1>
                        </div>

                        {/* Guide Card */}
                        <div style={{ backgroundColor: 'var(--slate-50)', padding: '1rem', borderRadius: 'var(--radius-md)', border: '1px solid var(--slate-200)', marginBottom: '1.5rem' }}>
                            <strong style={{ fontSize: '0.9375rem', color: 'var(--primary-700)', display: 'block', marginBottom: '0.25rem' }}>
                                📖 {assignment.mission_title}
                            </strong>
                            <p style={{ fontSize: '0.875rem', color: 'var(--slate-600)', lineHeight: 1.6 }}>
                                {assignment.offline_guide?.instructions ||
                                    'Dengarkan ananda menceritakan kembali kisah fabel yang dipelajari di kelas selama 5 menit santai saat santap malam atau sebelum tidur.'}
                            </p>
                        </div>

                        {isConfirmed ? (
                            <div style={{ textAlign: 'center', padding: '1.5rem 0' }}>
                                <div style={{ fontSize: '3rem', marginBottom: '0.5rem' }}>✅</div>
                                <h3 style={{ fontSize: '1.125rem', fontWeight: 700, color: 'var(--success-700)', marginBottom: '0.5rem' }}>
                                    Konfirmasi Berhasil Tercatat
                                </h3>
                                <p style={{ fontSize: '0.875rem', color: 'var(--slate-600)' }}>
                                    Terima kasih telah mendampingi ananda belajar di rumah. Pendidik dapat melihat catatan ini di ruang kerja.
                                </p>
                            </div>
                        ) : (
                            <form onSubmit={handleSubmit}>
                                <label style={{ display: 'block', fontSize: '0.875rem', fontWeight: 700, color: 'var(--slate-800)', marginBottom: '0.75rem' }}>
                                    Bagaimana hasil cerita ananda malam ini?
                                </label>

                                <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem', marginBottom: '1.5rem' }}>
                                    {options.map((opt) => (
                                        <div
                                            key={opt.code}
                                            onClick={() => setSelectedResponse(opt.code as any)}
                                            style={{
                                                padding: '0.875rem 1rem',
                                                borderRadius: 'var(--radius-lg)',
                                                border: `2px solid ${selectedResponse === opt.code ? (opt.code === 'done' ? 'var(--success-500)' : opt.code === 'not_yet' ? 'var(--accent-500)' : 'var(--danger-500)') : 'var(--slate-200)'}`,
                                                backgroundColor: selectedResponse === opt.code ? '#fff' : 'var(--slate-50)',
                                                cursor: 'pointer',
                                                transition: 'all 0.15s ease',
                                            }}
                                        >
                                            <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', marginBottom: '0.25rem' }}>
                                                <span style={{ fontSize: '1.125rem' }}>
                                                    {opt.code === 'done' ? '🟢' : opt.code === 'not_yet' ? '🟡' : '🔴'}
                                                </span>
                                                <strong style={{ fontSize: '0.9375rem', color: 'var(--slate-900)' }}>
                                                    {opt.label}
                                                </strong>
                                            </div>
                                            <div style={{ fontSize: '0.8125rem', color: 'var(--slate-600)', paddingLeft: '1.625rem' }}>
                                                {opt.description}
                                            </div>
                                        </div>
                                    ))}
                                </div>

                                <div style={{ marginBottom: '1.5rem' }}>
                                    <label style={{ display: 'block', fontSize: '0.8125rem', fontWeight: 600, color: 'var(--slate-700)', marginBottom: '0.375rem' }}>
                                        Catatan untuk Guru (Opsional):
                                    </label>
                                    <textarea
                                        value={note}
                                        onChange={(e) => setNote(e.target.value)}
                                        rows={2}
                                        placeholder="Contoh: Rian sangat antusias mengingat tokoh Tupai..."
                                        style={{
                                            width: '100%',
                                            padding: '0.625rem',
                                            borderRadius: 'var(--radius-md)',
                                            border: '1px solid var(--slate-300)',
                                            fontSize: '0.875rem',
                                        }}
                                    />
                                </div>

                                <button
                                    type="submit"
                                    disabled={isSubmitting}
                                    className="btn btn-primary"
                                    style={{ width: '100%', padding: '0.875rem' }}
                                >
                                    {isSubmitting ? 'Menyimpan...' : 'Kirim Konfirmasi ke Guru ✓'}
                                </button>
                            </form>
                        )}
                    </div>
                </div>
            </main>
        </div>
    );
}
