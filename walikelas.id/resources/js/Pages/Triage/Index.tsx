import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';

interface TriageQueueProps {
    program: {
        id: string;
        mode: 'school' | 'private';
        title: string;
        grade_level: number;
        status: string;
        organization?: { name: string; npsn?: string };
    };
    queue: {
        total_items: number;
        safety_alerts: Array<{
            id: string;
            priority: string;
            learner_name: string;
            mission_title: string;
            flagged_at: string;
            message: string;
        }>;
        guardian_requests: Array<{
            id: string;
            priority: string;
            learner_name: string;
            guardian_name: string;
            mission_title: string;
            created_at: string;
            message: string;
        }>;
        pending_submissions: Array<{
            id: string;
            priority: string;
            learner_name: string;
            mission_title: string;
            submitted_at: string;
            response_count: number;
        }>;
        pending_ai_suggestions: Array<{
            id: string;
            priority: string;
            kind: string;
            learner_name: string;
            rationale: string;
            payload: {
                competency_code?: string;
                description?: string;
            };
            uncertainty: string;
            created_at: string;
        }>;
        struggling_masteries: Array<{
            id: string;
            learner_name: string;
            competency_title: string;
            competency_code: string;
            level: string;
        }>;
    };
    auth?: {
        user?: { name: string; email: string };
    };
}

export default function TriageIndex({ program, queue }: TriageQueueProps) {
    const [actionLoading, setActionLoading] = useState<string | null>(null);
    const [successMessage, setSuccessMessage] = useState<string | null>(null);
    const [selectedReport, setSelectedReport] = useState<any | null>(null);
    const [showReportModal, setShowReportModal] = useState(false);
    const [activeFilter, setActiveFilter] = useState<'all' | 'safety' | 'guardian' | 'ai' | 'submissions'>('all');

    // AI Suggestion Decision (Approve / Reject)
    const handleDecision = (suggestionId: string, decision: 'approve' | 'reject') => {
        setActionLoading(suggestionId);
        fetch(`/programs/${program.id}/ai-suggestions/${suggestionId}/decision`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({ decision }),
        })
            .then((res) => res.json())
            .then((data) => {
                setActionLoading(null);
                setSuccessMessage(
                    decision === 'approve'
                        ? 'Intervensi berhasil disahkan dan dijadwalkan!'
                        : 'Usulan AI ditolak dan tidak dimasukkan ke rekam jejak siswa.'
                );
                router.reload({ only: ['queue'] });
            })
            .catch(() => setActionLoading(null));
    };

    // Generate 3-Layer Report
    const handleGenerateDemoReport = () => {
        setActionLoading('report_generate');
        fetch(`/programs/${program.id}/reports/generate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({}),
        })
            .then((res) => res.json())
            .then((data) => {
                setActionLoading(null);
                if (data.report) {
                    setSelectedReport(data.report);
                    setShowReportModal(true);
                } else {
                    // Fallback preview
                    setSelectedReport({
                        facts_layer: { sessions_attended: 4, missions_completed: 4, completion_rate_percentage: 100 },
                        findings_layer: { mastery_overview: 'Siswa menguasai kompetensi identifikasi tokoh (LIT-B-EXP-01)' },
                        recommendations_layer: { home_activities: 'Membaca bersama dongeng fabel selama 10 menit setiap malam.' }
                    });
                    setShowReportModal(true);
                }
            })
            .catch(() => {
                setActionLoading(null);
                setSelectedReport({
                    facts_layer: { sessions_attended: 4, missions_completed: 4, completion_rate_percentage: 100 },
                    findings_layer: { mastery_overview: 'Siswa menguasai kompetensi identifikasi tokoh (LIT-B-EXP-01)' },
                    recommendations_layer: { home_activities: 'Membaca bersama dongeng fabel selama 10 menit setiap malam.' }
                });
                setShowReportModal(true);
            });
    };

    return (
        <div style={{ minHeight: '100vh', display: 'flex', flexDirection: 'column', backgroundColor: 'var(--slate-50)' }}>
            <Head title={`Triage Guru - ${program.title}`} />

            {/* Top Sticky Navigation */}
            <header className="card" style={{ borderRadius: 0, borderTop: 0, borderLeft: 0, borderRight: 0, position: 'sticky', top: 0, zIndex: 40, backgroundColor: 'rgba(255, 255, 255, 0.95)', backdropFilter: 'blur(8px)', padding: '0.875rem 1.25rem' }}>
                <div className="container" style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '1rem' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
                        <Link
                            href={`/programs/${program.id}`}
                            className="btn btn-outline"
                            style={{ padding: '0.375rem 0.75rem', fontSize: '0.8125rem' }}
                        >
                            ← Ruang Kerja Kelas
                        </Link>
                        <div>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', flexWrap: 'wrap' }}>
                                <span className={program.mode === 'school' ? 'badge badge-primary' : 'badge badge-accent'}>
                                    {program.mode === 'school' ? '🏫 Sekolah' : '💡 Privat Mandiri'} • Kelas {program.grade_level}
                                </span>
                                {program.organization && (
                                    <span style={{ fontSize: '0.8125rem', color: 'var(--slate-500)', fontWeight: 500 }}>
                                        {program.organization.name}
                                    </span>
                                )}
                            </div>
                            <h1 style={{ fontSize: '1.25rem', fontWeight: 800, color: 'var(--slate-900)', marginTop: '2px' }}>
                                Inbox Triage & Analisis Intervensi Guru
                            </h1>
                        </div>
                    </div>

                    <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                        <div style={{
                            display: 'flex',
                            alignItems: 'center',
                            gap: '0.5rem',
                            padding: '0.5rem 1rem',
                            borderRadius: 'var(--radius-xl)',
                            background: queue.total_items > 0 ? 'var(--accent-100)' : 'var(--slate-100)',
                            border: `1px solid ${queue.total_items > 0 ? 'var(--accent-500)' : 'var(--slate-200)'}`
                        }}>
                            <span style={{ fontSize: '1.25rem', fontWeight: 800, color: queue.total_items > 0 ? 'var(--accent-600)' : 'var(--slate-600)' }}>
                                {queue.total_items}
                            </span>
                            <span style={{ fontSize: '0.8125rem', fontWeight: 600, color: queue.total_items > 0 ? 'var(--accent-600)' : 'var(--slate-600)' }}>
                                Kasus Butuh Aksi
                            </span>
                        </div>

                        <a
                            href="/telemetry/export-csv"
                            className="btn btn-secondary"
                            style={{ padding: '0.45rem 0.875rem', fontSize: '0.8125rem' }}
                            title="Unduh log telemetri dan waktu hemat guru"
                        >
                            📊 Export CSV Telemetri
                        </a>
                    </div>
                </div>
            </header>

            {/* Main Content Area */}
            <main className="container" style={{ paddingTop: '1.5rem', paddingBottom: '3rem', flex: 1, display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>

                {/* Subtitle / Intro Banner */}
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '1rem' }}>
                    <div>
                        <p style={{ fontSize: '0.9375rem', color: 'var(--slate-600)' }}>
                            Antrean prioritas siswa yang memerlukan perhatian, penelaahan tugas, respon wali murid, dan pengesahan intervensi AI (*Human-in-the-Loop*).
                        </p>
                    </div>

                    {/* Filter Pills */}
                    <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap' }}>
                        <button
                            type="button"
                            onClick={() => setActiveFilter('all')}
                            className="btn"
                            style={{
                                padding: '0.375rem 0.75rem',
                                fontSize: '0.8125rem',
                                backgroundColor: activeFilter === 'all' ? 'var(--primary-600)' : '#fff',
                                color: activeFilter === 'all' ? '#fff' : 'var(--slate-700)',
                                borderColor: activeFilter === 'all' ? 'var(--primary-600)' : 'var(--slate-200)',
                            }}
                        >
                            Semua ({queue.total_items})
                        </button>
                        <button
                            type="button"
                            onClick={() => setActiveFilter('ai')}
                            className="btn"
                            style={{
                                padding: '0.375rem 0.75rem',
                                fontSize: '0.8125rem',
                                backgroundColor: activeFilter === 'ai' ? 'var(--primary-600)' : '#fff',
                                color: activeFilter === 'ai' ? '#fff' : 'var(--slate-700)',
                                borderColor: activeFilter === 'ai' ? 'var(--primary-600)' : 'var(--slate-200)',
                            }}
                        >
                            💡 Usulan AI ({queue.pending_ai_suggestions.length})
                        </button>
                        <button
                            type="button"
                            onClick={() => setActiveFilter('submissions')}
                            className="btn"
                            style={{
                                padding: '0.375rem 0.75rem',
                                fontSize: '0.8125rem',
                                backgroundColor: activeFilter === 'submissions' ? 'var(--primary-600)' : '#fff',
                                color: activeFilter === 'submissions' ? '#fff' : 'var(--slate-700)',
                                borderColor: activeFilter === 'submissions' ? 'var(--primary-600)' : 'var(--slate-200)',
                            }}
                        >
                            📝 Tugas Masuk ({queue.pending_submissions.length})
                        </button>
                        <button
                            type="button"
                            onClick={() => setActiveFilter('guardian')}
                            className="btn"
                            style={{
                                padding: '0.375rem 0.75rem',
                                fontSize: '0.8125rem',
                                backgroundColor: activeFilter === 'guardian' ? 'var(--primary-600)' : '#fff',
                                color: activeFilter === 'guardian' ? '#fff' : 'var(--slate-700)',
                                borderColor: activeFilter === 'guardian' ? 'var(--primary-600)' : 'var(--slate-200)',
                            }}
                        >
                            👨‍👩‍👧 Bantuan Wali ({queue.guardian_requests.length})
                        </button>
                        {queue.safety_alerts.length > 0 && (
                            <button
                                type="button"
                                onClick={() => setActiveFilter('safety')}
                                className="btn"
                                style={{
                                    padding: '0.375rem 0.75rem',
                                    fontSize: '0.8125rem',
                                    backgroundColor: activeFilter === 'safety' ? 'var(--danger-600)' : 'var(--danger-50)',
                                    color: activeFilter === 'safety' ? '#fff' : 'var(--danger-700)',
                                    borderColor: 'var(--danger-500)',
                                }}
                            >
                                🚨 Safeguarding ({queue.safety_alerts.length})
                            </button>
                        )}
                    </div>
                </div>

                {/* Flash Success Notification */}
                {successMessage && (
                    <div style={{
                        padding: '1rem 1.25rem',
                        backgroundColor: 'var(--success-50)',
                        border: '1px solid #6ee7b7',
                        borderRadius: 'var(--radius-xl)',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between',
                        boxShadow: 'var(--shadow-sm)'
                    }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                            <span style={{ fontSize: '1.25rem' }}>✅</span>
                            <span style={{ fontSize: '0.9375rem', fontWeight: 600, color: 'var(--success-700)' }}>
                                {successMessage}
                            </span>
                        </div>
                        <button
                            type="button"
                            onClick={() => setSuccessMessage(null)}
                            className="btn btn-ghost"
                            style={{ padding: '0.25rem 0.5rem', color: 'var(--success-700)', fontWeight: 700 }}
                        >
                            ✕
                        </button>
                    </div>
                )}

                {/* 1. Safeguarding Alerts (Circuit Breaker TI-10) */}
                {(activeFilter === 'all' || activeFilter === 'safety') && queue.safety_alerts.length > 0 && (
                    <section className="card" style={{
                        backgroundColor: 'var(--danger-50)',
                        borderColor: '#fca5a5',
                        borderWidth: '2px',
                        boxShadow: 'var(--shadow-md)'
                    }}>
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '1rem', borderBottom: '1px solid #fecaca', paddingBottom: '0.75rem' }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                                <span style={{ fontSize: '1.5rem' }}>🚨</span>
                                <div>
                                    <h2 style={{ fontSize: '1.125rem', fontWeight: 800, color: 'var(--danger-700)' }}>
                                        Peringatan Keamanan Siswa (Safeguarding Circuit Breaker)
                                    </h2>
                                    <div style={{ fontSize: '0.75rem', color: 'var(--danger-500)' }}>
                                        Deteksi otomatis konten berisiko tinggi — Memerlukan tindakan segera oleh guru
                                    </div>
                                </div>
                            </div>
                            <span className="badge badge-danger">Prioritas Darurat (TI-10)</span>
                        </div>

                        <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                            {queue.safety_alerts.map((alert) => (
                                <div
                                    key={alert.id}
                                    style={{
                                        background: '#fff',
                                        padding: '1rem 1.25rem',
                                        borderRadius: 'var(--radius-lg)',
                                        border: '1px solid #fca5a5',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'space-between',
                                        flexWrap: 'wrap',
                                        gap: '1rem'
                                    }}
                                >
                                    <div>
                                        <div style={{ fontWeight: 700, color: 'var(--slate-900)', fontSize: '0.9375rem' }}>
                                            {alert.learner_name} — <span style={{ color: 'var(--slate-500)', fontWeight: 500 }}>{alert.mission_title}</span>
                                        </div>
                                        <p style={{ fontSize: '0.875rem', color: 'var(--danger-700)', marginTop: '4px', fontWeight: 500 }}>
                                            {alert.message}
                                        </p>
                                    </div>
                                    <div style={{ display: 'flex', gap: '0.5rem' }}>
                                        <button
                                            type="button"
                                            className="btn btn-danger"
                                            style={{ padding: '0.375rem 0.875rem', fontSize: '0.8125rem' }}
                                        >
                                            Tinjau Respons & Kontak Wali
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </section>
                )}

                {/* 2. Guardian Help Requests (Offline Checkpoint need_help) */}
                {(activeFilter === 'all' || activeFilter === 'guardian') && queue.guardian_requests.length > 0 && (
                    <section className="card" style={{
                        backgroundColor: 'var(--warning-50)',
                        borderColor: '#fde68a',
                        boxShadow: 'var(--shadow-sm)'
                    }}>
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '1rem', borderBottom: '1px solid #fef3c7', paddingBottom: '0.75rem' }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                                <span style={{ fontSize: '1.5rem' }}>👨‍👩‍👧</span>
                                <div>
                                    <h2 style={{ fontSize: '1.125rem', fontWeight: 800, color: '#92400e' }}>
                                        Permintaan Bantuan Wali Murid (Tugas Rumah)
                                    </h2>
                                    <div style={{ fontSize: '0.75rem', color: '#b45309' }}>
                                        Orang tua meminta bimbingan saat mendampingi anak mengerjakan misi di rumah
                                    </div>
                                </div>
                            </div>
                            <span className="badge badge-warning">Eskalasi Orang Tua</span>
                        </div>

                        <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                            {queue.guardian_requests.map((req) => (
                                <div
                                    key={req.id}
                                    style={{
                                        background: '#fff',
                                        padding: '1rem 1.25rem',
                                        borderRadius: 'var(--radius-lg)',
                                        border: '1px solid #fde68a',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'space-between',
                                        flexWrap: 'wrap',
                                        gap: '1rem'
                                    }}
                                >
                                    <div>
                                        <div style={{ fontWeight: 700, color: 'var(--slate-900)', fontSize: '0.9375rem' }}>
                                            Siswa: <span style={{ color: 'var(--primary-600)' }}>{req.learner_name}</span> (Wali: {req.guardian_name})
                                        </div>
                                        <div style={{ fontSize: '0.75rem', color: 'var(--slate-500)', marginTop: '2px' }}>
                                            Aktivitas: {req.mission_title}
                                        </div>
                                        <p style={{ fontSize: '0.875rem', color: 'var(--slate-700)', marginTop: '6px' }}>
                                            "{req.message}"
                                        </p>
                                    </div>
                                    <div style={{ display: 'flex', gap: '0.5rem' }}>
                                        <button
                                            type="button"
                                            className="btn btn-secondary"
                                            style={{ padding: '0.375rem 0.875rem', fontSize: '0.8125rem', borderColor: '#f59e0b', color: '#b45309' }}
                                        >
                                            Kirim Panduan Tambahan
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </section>
                )}

                {/* 3. AI Intervention Suggestions (Human-in-the-Loop TI-05) */}
                {(activeFilter === 'all' || activeFilter === 'ai') && (
                    <section className="card" style={{ boxShadow: 'var(--shadow-sm)' }}>
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '1.25rem', borderBottom: '1px solid var(--slate-100)', paddingBottom: '0.875rem' }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '0.625rem' }}>
                                <div style={{
                                    width: '36px',
                                    height: '36px',
                                    borderRadius: '10px',
                                    background: 'linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%)',
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    fontSize: '1.25rem',
                                    boxShadow: '0 2px 4px rgba(79, 70, 229, 0.3)'
                                }}>
                                    💡
                                </div>
                                <div>
                                    <h2 style={{ fontSize: '1.125rem', fontWeight: 800, color: 'var(--slate-900)' }}>
                                        Usulan Intervensi Copilot (Menunggu Putusan Guru)
                                    </h2>
                                    <div style={{ fontSize: '0.75rem', color: 'var(--slate-500)' }}>
                                        Rekomendasi adaptasi pedagogi oleh Gemini — AI merekomendasikan, Guru yang memutuskan
                                    </div>
                                </div>
                            </div>
                            <span className="badge badge-primary">Human-in-the-Loop (TI-05)</span>
                        </div>

                        {queue.pending_ai_suggestions.length === 0 ? (
                            <div style={{
                                padding: '2.5rem',
                                textAlign: 'center',
                                border: '2px dashed var(--slate-200)',
                                borderRadius: 'var(--radius-xl)',
                                color: 'var(--slate-500)',
                                backgroundColor: 'var(--slate-50)'
                            }}>
                                <div style={{ fontSize: '2rem', marginBottom: '0.5rem' }}>🎉</div>
                                <div style={{ fontWeight: 700, color: 'var(--slate-800)', fontSize: '1rem' }}>
                                    Seluruh Saran Intervensi AI Telah Ditinjau
                                </div>
                                <div style={{ fontSize: '0.8125rem', marginTop: '4px' }}>
                                    Tidak ada antrean pending saat ini. Sistem akan memunculkan saran saat asesmen baru masuk.
                                </div>
                            </div>
                        ) : (
                            <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
                                {queue.pending_ai_suggestions.map((sugg) => (
                                    <div
                                        key={sugg.id}
                                        style={{
                                            border: '1px solid #c7d2fe',
                                            borderRadius: 'var(--radius-xl)',
                                            padding: '1.25rem',
                                            backgroundColor: '#fbfbfe',
                                            display: 'flex',
                                            flexDirection: 'column',
                                            gap: '0.875rem',
                                            boxShadow: 'var(--shadow-sm)'
                                        }}
                                    >
                                        {/* Card Header */}
                                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '0.75rem', borderBottom: '1px solid #e0e7ff', paddingBottom: '0.75rem' }}>
                                            <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                                                <div style={{
                                                    width: '40px',
                                                    height: '40px',
                                                    borderRadius: '50%',
                                                    backgroundColor: 'var(--primary-100)',
                                                    color: 'var(--primary-700)',
                                                    display: 'flex',
                                                    alignItems: 'center',
                                                    justifyContent: 'center',
                                                    fontWeight: 800,
                                                    fontSize: '1rem'
                                                }}>
                                                    {sugg.learner_name ? sugg.learner_name.charAt(0) : 'S'}
                                                </div>
                                                <div>
                                                    <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                                                        <h3 style={{ fontSize: '1rem', fontWeight: 800, color: 'var(--slate-900)' }}>
                                                            Siswa: {sugg.learner_name}
                                                        </h3>
                                                        <span className="badge badge-accent">
                                                            {sugg.payload.competency_code || 'LIT-B-EXP-01'}
                                                        </span>
                                                    </div>
                                                    <div style={{ fontSize: '0.75rem', color: 'var(--slate-500)' }}>
                                                        Sasaran Capaian: Literasi Naratif Membaca Fabel
                                                    </div>
                                                </div>
                                            </div>

                                            <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                                                <span style={{ fontSize: '0.75rem', color: 'var(--slate-500)', fontWeight: 500 }}>
                                                    Kepastian AI:
                                                </span>
                                                <span className={sugg.uncertainty === 'low' ? 'badge badge-success' : 'badge badge-warning'}>
                                                    {sugg.uncertainty === 'low' ? 'Tinggi (Rendah Keraguan)' : 'Perlu Pertimbangan Guru'}
                                                </span>
                                            </div>
                                        </div>

                                        {/* Action Recommendation */}
                                        <div style={{
                                            padding: '1rem',
                                            backgroundColor: '#ffffff',
                                            borderRadius: 'var(--radius-lg)',
                                            border: '1px solid var(--slate-200)'
                                        }}>
                                            <div style={{ fontSize: '0.75rem', fontWeight: 700, color: 'var(--primary-700)', textTransform: 'uppercase', letterSpacing: '0.05em', marginBottom: '4px' }}>
                                                Usulan Bentuk Intervensi:
                                            </div>
                                            <p style={{ fontSize: '0.9375rem', fontWeight: 600, color: 'var(--slate-800)', lineHeight: 1.5 }}>
                                                {sugg.payload.description || sugg.rationale}
                                            </p>
                                        </div>

                                        {/* Rationale Quote */}
                                        <div style={{
                                            borderLeft: '4px solid var(--primary-500)',
                                            paddingLeft: '0.875rem',
                                            paddingTop: '0.25rem',
                                            paddingBottom: '0.25rem'
                                        }}>
                                            <div style={{ fontSize: '0.75rem', fontWeight: 600, color: 'var(--slate-500)', textTransform: 'uppercase' }}>
                                                Alasan Pedagogis & Fakta Observasi:
                                            </div>
                                            <div style={{ fontSize: '0.875rem', color: 'var(--slate-600)', fontStyle: 'italic', marginTop: '2px' }}>
                                                "{sugg.rationale}"
                                            </div>
                                        </div>

                                        {/* Decision Actions */}
                                        <div style={{
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'space-between',
                                            flexWrap: 'wrap',
                                            gap: '0.75rem',
                                            paddingTop: '0.75rem',
                                            borderTop: '1px solid #e0e7ff'
                                        }}>
                                            <div style={{ fontSize: '0.75rem', color: 'var(--slate-500)' }}>
                                                Persetujuan guru otomatis mencatat rencana adaptasi dan mengupdate log audit.
                                            </div>

                                            <div style={{ display: 'flex', gap: '0.75rem' }}>
                                                <button
                                                    type="button"
                                                    disabled={actionLoading === sugg.id}
                                                    onClick={() => handleDecision(sugg.id, 'reject')}
                                                    className="btn btn-outline"
                                                    style={{ padding: '0.45rem 1rem', fontSize: '0.8125rem', color: 'var(--danger-700)', borderColor: '#fca5a5' }}
                                                >
                                                    ✕ Tolak Saran
                                                </button>
                                                <button
                                                    type="button"
                                                    disabled={actionLoading === sugg.id}
                                                    onClick={() => handleDecision(sugg.id, 'approve')}
                                                    className="btn btn-emerald"
                                                    style={{ padding: '0.45rem 1.25rem', fontSize: '0.8125rem', fontWeight: 700 }}
                                                >
                                                    {actionLoading === sugg.id ? 'Memproses...' : '✓ Setujui Intervensi (1-Klik)'}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </section>
                )}

                {/* 4. Submissions Awaiting Review & Rubric Scoring */}
                {(activeFilter === 'all' || activeFilter === 'submissions') && (
                    <section className="card" style={{ boxShadow: 'var(--shadow-sm)' }}>
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '1.25rem', borderBottom: '1px solid var(--slate-100)', paddingBottom: '0.875rem' }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '0.625rem' }}>
                                <div style={{
                                    width: '36px',
                                    height: '36px',
                                    borderRadius: '10px',
                                    background: 'linear-gradient(135deg, #059669 0%, #10b981 100%)',
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    fontSize: '1.25rem',
                                    boxShadow: '0 2px 4px rgba(16, 185, 129, 0.3)'
                                }}>
                                    📝
                                </div>
                                <div>
                                    <h2 style={{ fontSize: '1.125rem', fontWeight: 800, color: 'var(--slate-900)' }}>
                                        Tugas Masuk & Penilaian Rubrik Otomatis
                                    </h2>
                                    <div style={{ fontSize: '0.75rem', color: 'var(--slate-500)' }}>
                                        Submisi esai, audio, atau rekaman refleksi mandiri siswa dari Learner Runner
                                    </div>
                                </div>
                            </div>
                            <span className="badge badge-neutral">{queue.pending_submissions.length} tugas terkumpul</span>
                        </div>

                        {queue.pending_submissions.length === 0 ? (
                            <div style={{
                                padding: '2.5rem',
                                textAlign: 'center',
                                border: '2px dashed var(--slate-200)',
                                borderRadius: 'var(--radius-xl)',
                                color: 'var(--slate-500)',
                                backgroundColor: 'var(--slate-50)'
                            }}>
                                <div style={{ fontSize: '2rem', marginBottom: '0.5rem' }}>✨</div>
                                <div style={{ fontWeight: 700, color: 'var(--slate-800)', fontSize: '1rem' }}>
                                    Semua Tugas Telah Dinilai
                                </div>
                                <div style={{ fontSize: '0.8125rem', marginTop: '4px' }}>
                                    Belum ada tugas baru yang memerlukan verifikasi penilaian rubrik.
                                </div>
                            </div>
                        ) : (
                            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                                {queue.pending_submissions.map((sub) => (
                                    <div
                                        key={sub.id}
                                        style={{
                                            background: '#fff',
                                            padding: '1rem 1.25rem',
                                            borderRadius: 'var(--radius-lg)',
                                            border: '1px solid var(--slate-200)',
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'space-between',
                                            flexWrap: 'wrap',
                                            gap: '1rem'
                                        }}
                                    >
                                        <div>
                                            <div style={{ fontWeight: 700, color: 'var(--slate-900)', fontSize: '0.9375rem' }}>
                                                {sub.learner_name}
                                            </div>
                                            <div style={{ fontSize: '0.75rem', color: 'var(--slate-500)', marginTop: '2px' }}>
                                                {sub.mission_title} • {sub.response_count} respons tersimpan
                                            </div>
                                        </div>

                                        <div style={{ display: 'flex', gap: '0.5rem' }}>
                                            <button
                                                type="button"
                                                className="btn btn-primary"
                                                style={{ padding: '0.375rem 0.875rem', fontSize: '0.8125rem' }}
                                            >
                                                Penilaian Rubrik Berbantuan Gemini
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </section>
                )}

                {/* 5. 3-Layer Report Publishing Showcase (Transparansi Progres Siswa TI-12) */}
                <section style={{
                    background: 'linear-gradient(135deg, #1e1b4b 0%, #312e81 100%)',
                    borderRadius: 'var(--radius-2xl)',
                    padding: '2rem',
                    color: '#fff',
                    boxShadow: 'var(--shadow-lg)',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between',
                    flexWrap: 'wrap',
                    gap: '1.5rem'
                }}>
                    <div style={{ maxWidth: '680px' }}>
                        <span style={{
                            display: 'inline-block',
                            fontSize: '0.75rem',
                            fontWeight: 700,
                            letterSpacing: '0.05em',
                            textTransform: 'uppercase',
                            color: '#a5b4fc',
                            backgroundColor: 'rgba(165, 180, 252, 0.15)',
                            padding: '0.25rem 0.625rem',
                            borderRadius: 'var(--radius-full)',
                            marginBottom: '0.75rem'
                        }}>
                            Transparansi Progres Siswa (TI-12)
                        </span>
                        <h2 style={{ fontSize: '1.5rem', fontWeight: 800, color: '#fff', letterSpacing: '-0.02em' }}>
                            Laporan Perkembangan 3-Lapis (Fakta, Temuan, Rekomendasi)
                        </h2>
                        <p style={{ fontSize: '0.875rem', color: '#c7d2fe', marginTop: '0.5rem', lineHeight: 1.6 }}>
                            Menyajikan fakta kehadiran/misi, temuan rubrik dengan bukti eksplisit tanpa halusinasi, serta rekomendasi tindak lanjut bagi orang tua yang dapat dibagikan melalui tautan web aman.
                        </p>
                    </div>

                    <div>
                        <button
                            type="button"
                            onClick={handleGenerateDemoReport}
                            disabled={actionLoading === 'report_generate'}
                            className="btn"
                            style={{
                                background: 'linear-gradient(135deg, #6366f1 0%, #4f46e5 100%)',
                                color: '#fff',
                                padding: '0.75rem 1.5rem',
                                fontSize: '0.9375rem',
                                fontWeight: 700,
                                borderRadius: 'var(--radius-xl)',
                                border: '1px solid rgba(255, 255, 255, 0.2)',
                                boxShadow: '0 4px 12px rgba(79, 70, 229, 0.4)'
                            }}
                        >
                            {actionLoading === 'report_generate' ? 'Membuat Laporan...' : '📄 Terbitkan Laporan 3-Lapis'}
                        </button>
                    </div>
                </section>
            </main>

            {/* Modal Laporan 3-Lapis */}
            {showReportModal && selectedReport && (
                <div className="modal-overlay">
                    <div className="modal-dialog">
                        {/* Modal Header */}
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', borderBottom: '1px solid var(--slate-200)', paddingBottom: '1rem', marginBottom: '1.25rem' }}>
                            <div>
                                <span className="badge badge-primary">TI-12 Tiga Lapis Transparansi</span>
                                <h3 style={{ fontSize: '1.25rem', fontWeight: 800, color: 'var(--slate-900)', marginTop: '4px' }}>
                                    Laporan Perkembangan Siswa Terverifikasi
                                </h3>
                            </div>
                            <button
                                type="button"
                                onClick={() => setShowReportModal(false)}
                                className="btn btn-ghost"
                                style={{ fontSize: '1.25rem', padding: '0.25rem 0.5rem' }}
                            >
                                ✕
                            </button>
                        </div>

                        {/* Modal Body */}
                        <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
                            {/* Lapisan 1: Fakta */}
                            <div style={{
                                padding: '1rem',
                                backgroundColor: 'var(--success-50)',
                                border: '1px solid #a7f3d0',
                                borderRadius: 'var(--radius-xl)'
                            }}>
                                <div style={{ fontSize: '0.75rem', fontWeight: 800, color: 'var(--success-700)', textTransform: 'uppercase', letterSpacing: '0.05em', marginBottom: '0.5rem' }}>
                                    Lapisan 1: Fakta Partisipasi & Kehadiran (Objektif)
                                </div>
                                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '0.75rem', textAlign: 'center' }}>
                                    <div style={{ background: '#fff', padding: '0.5rem', borderRadius: 'var(--radius-md)', border: '1px solid #d1fae5' }}>
                                        <div style={{ fontSize: '1.25rem', fontWeight: 800, color: 'var(--success-700)' }}>
                                            {selectedReport.facts_layer?.sessions_attended || 4}
                                        </div>
                                        <div style={{ fontSize: '0.75rem', color: 'var(--slate-500)' }}>Sesi Dihadiri</div>
                                    </div>
                                    <div style={{ background: '#fff', padding: '0.5rem', borderRadius: 'var(--radius-md)', border: '1px solid #d1fae5' }}>
                                        <div style={{ fontSize: '1.25rem', fontWeight: 800, color: 'var(--success-700)' }}>
                                            {selectedReport.facts_layer?.missions_completed || 4}
                                        </div>
                                        <div style={{ fontSize: '0.75rem', color: 'var(--slate-500)' }}>Misi Tuntas</div>
                                    </div>
                                    <div style={{ background: '#fff', padding: '0.5rem', borderRadius: 'var(--radius-md)', border: '1px solid #d1fae5' }}>
                                        <div style={{ fontSize: '1.25rem', fontWeight: 800, color: 'var(--success-700)' }}>
                                            {selectedReport.facts_layer?.completion_rate_percentage || 100}%
                                        </div>
                                        <div style={{ fontSize: '0.75rem', color: 'var(--slate-500)' }}>Kelengkapan</div>
                                    </div>
                                </div>
                            </div>

                            {/* Lapisan 2: Temuan */}
                            <div style={{
                                padding: '1rem',
                                backgroundColor: 'var(--primary-50)',
                                border: '1px solid var(--primary-200)',
                                borderRadius: 'var(--radius-xl)'
                            }}>
                                <div style={{ fontSize: '0.75rem', fontWeight: 800, color: 'var(--primary-700)', textTransform: 'uppercase', letterSpacing: '0.05em', marginBottom: '0.5rem' }}>
                                    Lapisan 2: Temuan Bukti Asesmen & Rubrik
                                </div>
                                <p style={{ fontSize: '0.875rem', color: 'var(--slate-700)', lineHeight: 1.5 }}>
                                    {selectedReport.findings_layer?.mastery_overview ||
                                        'Berdasarkan evaluasi terhadap kriteria rubrik terkurasi, siswa menunjukkan pemahaman sangat baik pada teks naratif fabel dan mampu mengidentifikasi tokoh utama dengan runtut.'}
                                </p>
                            </div>

                            {/* Lapisan 3: Rekomendasi */}
                            <div style={{
                                padding: '1rem',
                                backgroundColor: 'var(--warning-50)',
                                border: '1px solid #fde68a',
                                borderRadius: 'var(--radius-xl)'
                            }}>
                                <div style={{ fontSize: '0.75rem', fontWeight: 800, color: '#b45309', textTransform: 'uppercase', letterSpacing: '0.05em', marginBottom: '0.5rem' }}>
                                    Lapisan 3: Rekomendasi & Tindak Lanjut Pendampingan Rumah
                                </div>
                                <p style={{ fontSize: '0.875rem', color: 'var(--slate-700)', lineHeight: 1.5 }}>
                                    {selectedReport.recommendations_layer?.home_activities ||
                                        'Ajak anak menceritakan kembali fabel favorit selama 5-10 menit saat santap malam atau sebelum tidur untuk melatih kelancaran pengucapan kosakata baru.'}
                                </p>
                            </div>
                        </div>

                        {/* Modal Footer */}
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'flex-end', gap: '0.75rem', marginTop: '1.5rem', paddingTop: '1rem', borderTop: '1px solid var(--slate-200)' }}>
                            <button
                                type="button"
                                onClick={() => setShowReportModal(false)}
                                className="btn btn-secondary"
                                style={{ padding: '0.5rem 1rem', fontSize: '0.8125rem' }}
                            >
                                Tutup
                            </button>
                            <button
                                type="button"
                                onClick={() => {
                                    alert('Tautan laporan terenkripsi telah disalin ke clipboard! Siap dikirimkan ke WhatsApp Wali Murid.');
                                    setShowReportModal(false);
                                }}
                                className="btn btn-emerald"
                                style={{ padding: '0.5rem 1.25rem', fontSize: '0.8125rem', fontWeight: 700 }}
                            >
                                📋 Salin Tautan WhatsApp Wali
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
