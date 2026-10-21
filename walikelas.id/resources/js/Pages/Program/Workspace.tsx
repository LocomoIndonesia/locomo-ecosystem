import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';

interface RubricLevel {
    [key: string]: string;
}

interface Competency {
    id: string;
    code: string;
    domain: string;
    title: string;
    description: string;
    grade_band: string;
    rubrics?: Array<{ id: string; version: number; levels: RubricLevel }>;
}

interface PlanVersion {
    id: string;
    version_no: number;
    status: 'draft' | 'ready_for_review' | 'approved' | 'superseded';
    source_type: string;
    content: {
        title?: string;
        objectives?: string[];
        activities?: Array<{ session: number; name: string; duration_minutes: number; description: string }>;
        assessment_strategy?: { type: string; frequency: string; rubrics: string[] };
    };
    ambiguities: string[];
    created_at: string;
    approved_at?: string;
}

interface Enrollment {
    id: string;
    status: string;
    learner: {
        id: string;
        display_name: string;
        avatar_id: string;
    };
}

interface ProgramWorkspaceProps {
    program: {
        id: string;
        mode: 'school' | 'private';
        title: string;
        goal_summary: string | null;
        grade_level: number;
        status: string;
        organization?: { name: string; npsn?: string };
        active_plan_version?: PlanVersion;
        learning_plan?: {
            id: string;
            versions: PlanVersion[];
        };
        enrollments: Enrollment[];
        educators: Array<{ id: string; name: string; email: string }>;
    };
    competencies: Competency[];
    auth: {
        user: { id: string; name: string; email: string };
    };
}

export default function Workspace({ program, competencies, auth }: ProgramWorkspaceProps) {
    const [activeTab, setActiveTab] = useState<'plan' | 'learners' | 'curriculum'>('plan');
    const [showImportModal, setShowImportModal] = useState(false);
    const [showWizardModal, setShowWizardModal] = useState(false);
    const [rawText, setRawText] = useState('');
    const [isProcessing, setIsProcessing] = useState(false);

    // Wizard state
    const [wizardTitle, setWizardTitle] = useState(program.title + ' - Kurikulum Literasi');
    const [selectedCodes, setSelectedCodes] = useState<string[]>(['LIT-B-EXP-01']);
    const [sessionCount, setSessionCount] = useState<number>(4);

    const activeVersion = program.active_plan_version;
    const allVersions = program.learning_plan?.versions || [];
    const draftVersion = allVersions.find((v) => v.status === 'draft');

    const handleImportSyllabus = (e: React.FormEvent) => {
        e.preventDefault();
        setIsProcessing(true);
        router.post(`/programs/${program.id}/plans/import`, {
            raw_text: rawText,
        }, {
            onFinish: () => {
                setIsProcessing(false);
                setShowImportModal(false);
                setRawText('');
            }
        });
    };

    const handleWizardSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setIsProcessing(true);
        router.post(`/programs/${program.id}/plans/wizard`, {
            title: wizardTitle,
            competency_codes: selectedCodes,
            session_count: sessionCount,
        }, {
            onFinish: () => {
                setIsProcessing(false);
                setShowWizardModal(false);
            }
        });
    };

    const handleApprove = (versionId: string) => {
        if (!confirm('Apakah Anda yakin ingin mengesahkan versi rencana ajar ini sebagai acuan aktif penugasan?')) return;
        router.post(`/programs/${program.id}/plans/${versionId}/approve`);
    };

    const handleRevise = (version: PlanVersion) => {
        if (!confirm('Buat draf revisi baru (Versi ' + (version.version_no + 1) + ')? Penugasan yang sedang berjalan tetap aman merujuk versi lama hingga draf baru disahkan.')) return;
        router.post(`/programs/${program.id}/plans/${version.id}/revise`, {
            content: version.content,
        });
    };

    const prefillSampleText = () => {
        setRawText(
            "Silabus Literasi Kelas 3 SD Semester 1.\n" +
            "Fokus: Membaca teks fabel dan memahami karakter tokoh (Kancil, Tupai, Berang-berang).\n" +
            "Siswa mampu mengidentifikasi tokoh utama, urutan kejadian awal-tengah-akhir, dan kosakata sukar.\n" +
            "Rencana 4 kali pertemuan tatap muka dan tugas rumah menceritakan kembali cerita ke orang tua."
        );
    };

    return (
        <div style={{ minHeight: '100vh', display: 'flex', flexDirection: 'column' }}>
            <Head title={`Ruang Kerja: ${program.title}`} />

            {/* Top Navigation */}
            <header className="card" style={{ borderRadius: 0, borderTop: 0, borderLeft: 0, borderRight: 0, position: 'sticky', top: 0, zIndex: 30, backgroundColor: 'rgba(255, 255, 255, 0.95)', backdropFilter: 'blur(8px)' }}>
                <div className="container" style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '0.875rem 1.25rem' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
                        <Link href="/" className="btn btn-outline" style={{ padding: '0.375rem 0.75rem', fontSize: '0.8125rem' }}>
                            ← Kembali ke Dashboard
                        </Link>
                        <div>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                                <h1 style={{ fontSize: '1.25rem', fontWeight: 800, color: 'var(--slate-900)' }}>
                                    {program.title}
                                </h1>
                                <span className={program.mode === 'school' ? 'badge badge-primary' : 'badge badge-accent'}>
                                    {program.mode === 'school' ? '🏫 Sekolah' : '💡 Privat Mandiri'}
                                </span>
                            </div>
                            {program.organization && (
                                <div style={{ fontSize: '0.75rem', color: 'var(--slate-500)' }}>
                                    🏛️ {program.organization.name}
                                </div>
                            )}
                        </div>
                    </div>

                    <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                        <span className="badge badge-neutral">Pendidik: {auth.user.name}</span>
                    </div>
                </div>
            </header>

            {/* Sub-nav Tabs */}
            <div style={{ backgroundColor: '#fff', borderBottom: '1px solid var(--slate-200)' }}>
                <div className="container" style={{ display: 'flex', gap: '1.5rem', padding: '0 1.25rem' }}>
                    <button
                        onClick={() => setActiveTab('plan')}
                        style={{
                            padding: '1rem 0.25rem',
                            fontWeight: 700,
                            fontSize: '0.9375rem',
                            border: 'none',
                            background: 'none',
                            cursor: 'pointer',
                            color: activeTab === 'plan' ? 'var(--primary-600)' : 'var(--slate-500)',
                            borderBottom: activeTab === 'plan' ? '3px solid var(--primary-600)' : '3px solid transparent',
                        }}
                    >
                        📋 Rencana Ajar & Copilot
                    </button>
                    <button
                        onClick={() => setActiveTab('learners')}
                        style={{
                            padding: '1rem 0.25rem',
                            fontWeight: 700,
                            fontSize: '0.9375rem',
                            border: 'none',
                            background: 'none',
                            cursor: 'pointer',
                            color: activeTab === 'learners' ? 'var(--primary-600)' : 'var(--slate-500)',
                            borderBottom: activeTab === 'learners' ? '3px solid var(--primary-600)' : '3px solid transparent',
                        }}
                    >
                        👥 Siswa & Wali ({program.enrollments.length})
                    </button>
                    <button
                        onClick={() => setActiveTab('curriculum')}
                        style={{
                            padding: '1rem 0.25rem',
                            fontWeight: 700,
                            fontSize: '0.9375rem',
                            border: 'none',
                            background: 'none',
                            cursor: 'pointer',
                            color: activeTab === 'curriculum' ? 'var(--primary-600)' : 'var(--slate-500)',
                            borderBottom: activeTab === 'curriculum' ? '3px solid var(--primary-600)' : '3px solid transparent',
                        }}
                    >
                        🎯 Pustaka Kompetensi & Rubrik ({competencies.length})
                    </button>
                </div>
            </div>

            {/* Main Content Area */}
            <main className="container" style={{ flex: 1, padding: '2rem 1.25rem' }}>
                {activeTab === 'plan' && (
                    <div>
                        {/* Status Banners */}
                        {draftVersion && (
                            <div className="card" style={{ padding: '1.5rem', marginBottom: '1.5rem', borderLeft: '4px solid var(--accent-500)', backgroundColor: 'var(--accent-100)' }}>
                                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', flexWrap: 'wrap', gap: '1rem' }}>
                                    <div>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', marginBottom: '0.25rem' }}>
                                            <span style={{ fontSize: '1rem' }}>⚠️</span>
                                            <h3 style={{ fontSize: '1.0625rem', fontWeight: 700, color: 'var(--slate-900)' }}>
                                                Draf Rencana Versi {draftVersion.version_no} Menunggu Pengesahan Anda (TI-04)
                                            </h3>
                                        </div>
                                        <p style={{ fontSize: '0.875rem', color: 'var(--slate-700)', maxWidth: '640px' }}>
                                            Copilot telah mengekstraksi sasaran dan alur belajar. Tinjau ambiguitas di bawah ini sebelum mengesahkan rencana.
                                        </p>

                                        {draftVersion.ambiguities && draftVersion.ambiguities.length > 0 && (
                                            <div style={{ marginTop: '0.75rem', backgroundColor: '#fff', padding: '0.75rem 1rem', borderRadius: 'var(--radius-md)', border: '1px solid var(--slate-300)' }}>
                                                <div style={{ fontSize: '0.75rem', fontWeight: 700, color: 'var(--accent-600)', textTransform: 'uppercase', marginBottom: '0.25rem' }}>
                                                    Catatan Ambiguitas Terdeteksi (TAT-18):
                                                </div>
                                                <ul style={{ paddingLeft: '1.25rem', fontSize: '0.8125rem', color: 'var(--slate-600)' }}>
                                                    {draftVersion.ambiguities.map((amb, idx) => (
                                                        <li key={idx}>{amb}</li>
                                                    ))}
                                                </ul>
                                            </div>
                                        )}
                                    </div>

                                    <button
                                        onClick={() => handleApprove(draftVersion.id)}
                                        className="btn btn-primary"
                                        style={{ backgroundColor: 'var(--success-700)', borderColor: 'var(--success-700)' }}
                                    >
                                        ✓ Sahkan Versi {draftVersion.version_no} (1-Click Approve)
                                    </button>
                                </div>
                            </div>
                        )}

                        {activeVersion && (
                            <div className="card" style={{ padding: '1.5rem', marginBottom: '1.5rem', borderLeft: '4px solid var(--success-500)', backgroundColor: 'var(--success-50)' }}>
                                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '1rem' }}>
                                    <div>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                                            <span style={{ fontSize: '1rem' }}>🟢</span>
                                            <h3 style={{ fontSize: '1.0625rem', fontWeight: 700, color: 'var(--success-700)' }}>
                                                Rencana Ajar Versi {activeVersion.version_no} Sedang Aktif
                                            </h3>
                                        </div>
                                        <p style={{ fontSize: '0.875rem', color: 'var(--slate-600)', marginTop: '0.25rem' }}>
                                            Tugas misi belajar dapat diterbitkan ke siswa. Seluruh penugasan terikat kekal pada versi ini.
                                        </p>
                                    </div>

                                    <button
                                        onClick={() => handleRevise(activeVersion)}
                                        className="btn btn-outline"
                                        style={{ borderColor: 'var(--success-700)', color: 'var(--success-700)' }}
                                    >
                                        ✏️ Buat Revisi (Versi {activeVersion.version_no + 1})
                                    </button>
                                </div>
                            </div>
                        )}

                        {/* Top Action Bar when no plan */}
                        {allVersions.length === 0 && (
                            <div className="card" style={{ padding: '2.5rem', textAlign: 'center', marginBottom: '2rem' }}>
                                <div style={{ fontSize: '2.5rem', marginBottom: '0.75rem' }}>📋</div>
                                <h2 style={{ fontSize: '1.375rem', fontWeight: 800, color: 'var(--slate-900)', marginBottom: '0.5rem' }}>
                                    Program Belum Memiliki Rencana Pembelajaran
                                </h2>
                                <p style={{ color: 'var(--slate-600)', fontSize: '0.9375rem', maxWidth: '600px', margin: '0 auto 1.5rem auto' }}>
                                    Pilih salah satu metode perancangan di bawah untuk memulai. Penugasan baru dapat diterbitkan setelah rencana disahkan (TI-11).
                                </p>

                                <div style={{ display: 'flex', justifyContent: 'center', gap: '1rem', flexWrap: 'wrap' }}>
                                    <button
                                        onClick={() => setShowImportModal(true)}
                                        className="btn btn-primary"
                                    >
                                        ✨ Impor Bebas via Copilot Gemini
                                    </button>
                                    <button
                                        onClick={() => setShowWizardModal(true)}
                                        className="btn btn-outline"
                                    >
                                        🧭 Susun via Wizard 5 Langkah
                                    </button>
                                </div>
                            </div>
                        )}

                        {/* Details of Current Active or Latest Plan */}
                        {(activeVersion || draftVersion) && (
                            <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: '1.5rem', alignItems: 'flex-start' }}>
                                {/* Content Section */}
                                <div className="card" style={{ padding: '1.75rem' }}>
                                    <h3 style={{ fontSize: '1.25rem', fontWeight: 700, color: 'var(--slate-900)', marginBottom: '1rem' }}>
                                        {(activeVersion || draftVersion)?.content?.title || 'Rencana Pembelajaran Literasi'}
                                    </h3>

                                    <div style={{ marginBottom: '1.5rem' }}>
                                        <h4 style={{ fontSize: '0.875rem', fontWeight: 700, textTransform: 'uppercase', color: 'var(--primary-600)', marginBottom: '0.5rem', letterSpacing: '0.05em' }}>
                                            Sasaran Capaian Pembelajaran:
                                        </h4>
                                        <ul style={{ paddingLeft: '1.25rem', color: 'var(--slate-700)', fontSize: '0.9375rem', lineHeight: 1.7 }}>
                                            {(activeVersion || draftVersion)?.content?.objectives?.map((obj, i) => (
                                                <li key={i}>{obj}</li>
                                            )) || <li>Belum ada tujuan terdaftar.</li>}
                                        </ul>
                                    </div>

                                    <div>
                                        <h4 style={{ fontSize: '0.875rem', fontWeight: 700, textTransform: 'uppercase', color: 'var(--primary-600)', marginBottom: '0.75rem', letterSpacing: '0.05em' }}>
                                            Alur Sesi & Aktivitas Belajar:
                                        </h4>
                                        <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                                            {(activeVersion || draftVersion)?.content?.activities?.map((act, i) => (
                                                <div key={i} style={{ border: '1px solid var(--slate-200)', borderRadius: 'var(--radius-md)', padding: '0.875rem', backgroundColor: 'var(--slate-50)' }}>
                                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '0.25rem' }}>
                                                        <strong style={{ fontSize: '0.9375rem', color: 'var(--slate-900)' }}>
                                                            Sesi {act.session}: {act.name}
                                                        </strong>
                                                        <span className="badge badge-neutral" style={{ fontSize: '0.75rem' }}>
                                                            ⏱️ {act.duration_minutes} menit
                                                        </span>
                                                    </div>
                                                    <p style={{ fontSize: '0.8125rem', color: 'var(--slate-600)' }}>
                                                        {act.description}
                                                    </p>
                                                </div>
                                            )) || (
                                                <div style={{ fontSize: '0.875rem', color: 'var(--slate-500)' }}>
                                                    Aktivitas terstandarisasi 4 pertemuan mingguan.
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                {/* Plan Version History Timeline (TI-04, TAT-23) */}
                                <div className="card" style={{ padding: '1.5rem' }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1rem' }}>
                                        <h4 style={{ fontSize: '1rem', fontWeight: 700, color: 'var(--slate-900)' }}>
                                            Riwayat Versi Rencana
                                        </h4>
                                        <button
                                            onClick={() => setShowImportModal(true)}
                                            className="btn btn-outline"
                                            style={{ padding: '0.25rem 0.5rem', fontSize: '0.75rem' }}
                                        >
                                            + Impor Baru
                                        </button>
                                    </div>

                                    <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                                        {allVersions.map((v) => (
                                            <div
                                                key={v.id}
                                                style={{
                                                    border: `1px solid ${v.id === activeVersion?.id ? 'var(--success-500)' : 'var(--slate-200)'}`,
                                                    borderRadius: 'var(--radius-md)',
                                                    padding: '0.75rem',
                                                    backgroundColor: v.id === activeVersion?.id ? 'var(--success-50)' : '#fff',
                                                }}
                                            >
                                                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                                    <strong style={{ fontSize: '0.875rem', color: 'var(--slate-900)' }}>
                                                        Versi {v.version_no}
                                                    </strong>
                                                    <span className={
                                                        v.status === 'approved' ? 'badge badge-primary' :
                                                        v.status === 'draft' ? 'badge badge-accent' : 'badge badge-neutral'
                                                    } style={{ fontSize: '0.6875rem' }}>
                                                        {v.status === 'approved' ? 'Aktif' : v.status === 'draft' ? 'Draf' : 'Tergantikan'}
                                                    </span>
                                                </div>
                                                <div style={{ fontSize: '0.75rem', color: 'var(--slate-500)', marginTop: '0.25rem' }}>
                                                    Sumber: {v.source_type}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                )}

                {activeTab === 'learners' && (
                    <div className="card" style={{ padding: '1.75rem' }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1.5rem' }}>
                            <div>
                                <h3 style={{ fontSize: '1.25rem', fontWeight: 700, color: 'var(--slate-900)' }}>
                                    Daftar Siswa Terdaftar
                                </h3>
                                <p style={{ fontSize: '0.875rem', color: 'var(--slate-500)' }}>
                                    Data minimalis: hanya nama panggilan anak & perwalian sah terverifikasi (PDP).
                                </p>
                            </div>
                        </div>

                        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(260px, 1fr))', gap: '1rem' }}>
                            {program.enrollments.map((enr) => (
                                <div key={enr.id} style={{ border: '1px solid var(--slate-200)', borderRadius: 'var(--radius-lg)', padding: '1rem', display: 'flex', alignItems: 'center', gap: '0.875rem', backgroundColor: '#fff' }}>
                                    <div style={{ width: '44px', height: '44px', borderRadius: '50%', backgroundColor: 'var(--primary-100)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '1.25rem' }}>
                                        🐾
                                    </div>
                                    <div>
                                        <div style={{ fontWeight: 700, fontSize: '0.9375rem', color: 'var(--slate-900)' }}>
                                            {enr.learner.display_name}
                                        </div>
                                        <div style={{ fontSize: '0.75rem', color: 'var(--slate-500)' }}>
                                            Status: <span style={{ color: 'var(--success-700)', fontWeight: 600 }}>Aktif</span>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {activeTab === 'curriculum' && (
                    <div>
                        <div style={{ marginBottom: '1.5rem' }}>
                            <h3 style={{ fontSize: '1.25rem', fontWeight: 700, color: 'var(--slate-900)' }}>
                                Pustaka Standar Kurikulum Merdeka (Fase B Kelas 3)
                            </h3>
                            <p style={{ fontSize: '0.875rem', color: 'var(--slate-500)' }}>
                                3 kompetensi membaca terkurasi nasional lengkap dengan kriteria rubrik 3 tingkat capaian.
                            </p>
                        </div>

                        <div style={{ display: 'flex', flexDirection: 'column', gap: '1.25rem' }}>
                            {competencies.map((comp) => (
                                <div key={comp.id} className="card" style={{ padding: '1.5rem' }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '0.5rem' }}>
                                        <div>
                                            <span className="badge badge-primary" style={{ marginRight: '0.5rem' }}>
                                                {comp.code}
                                            </span>
                                            <span className="badge badge-neutral">{comp.domain}</span>
                                            <h4 style={{ fontSize: '1.125rem', fontWeight: 700, color: 'var(--slate-900)', marginTop: '0.375rem' }}>
                                                {comp.title}
                                            </h4>
                                        </div>
                                    </div>
                                    <p style={{ fontSize: '0.875rem', color: 'var(--slate-600)', marginBottom: '1rem' }}>
                                        {comp.description}
                                    </p>

                                    {comp.rubrics && comp.rubrics[0] && (
                                        <div style={{ backgroundColor: 'var(--slate-50)', padding: '1rem', borderRadius: 'var(--radius-md)', border: '1px solid var(--slate-200)' }}>
                                            <div style={{ fontSize: '0.75rem', fontWeight: 700, color: 'var(--slate-500)', textTransform: 'uppercase', marginBottom: '0.5rem' }}>
                                                Kriteria Penilaian Rubrik Terstandar:
                                            </div>
                                            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: '0.75rem' }}>
                                                <div style={{ borderLeft: '3px solid var(--danger-500)', paddingLeft: '0.5rem' }}>
                                                    <span style={{ fontSize: '0.75rem', fontWeight: 700, color: 'var(--danger-700)' }}>Perlu Pendampingan</span>
                                                    <p style={{ fontSize: '0.75rem', color: 'var(--slate-600)', marginTop: '2px' }}>
                                                        {comp.rubrics[0].levels['NEEDS_SUPPORT'] || '-'}
                                                    </p>
                                                </div>
                                                <div style={{ borderLeft: '3px solid var(--accent-500)', paddingLeft: '0.5rem' }}>
                                                    <span style={{ fontSize: '0.75rem', fontWeight: 700, color: 'var(--accent-600)' }}>Berkembang</span>
                                                    <p style={{ fontSize: '0.75rem', color: 'var(--slate-600)', marginTop: '2px' }}>
                                                        {comp.rubrics[0].levels['DEVELOPING'] || '-'}
                                                    </p>
                                                </div>
                                                <div style={{ borderLeft: '3px solid var(--success-500)', paddingLeft: '0.5rem' }}>
                                                    <span style={{ fontSize: '0.75rem', fontWeight: 700, color: 'var(--success-700)' }}>Cakap Mandiri</span>
                                                    <p style={{ fontSize: '0.75rem', color: 'var(--slate-600)', marginTop: '2px' }}>
                                                        {comp.rubrics[0].levels['INDICATED_MASTERY'] || '-'}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </main>

            {/* Modal Import Silabus Bebas */}
            {showImportModal && (
                <div style={{ position: 'fixed', inset: 0, backgroundColor: 'rgba(15, 23, 42, 0.6)', backdropFilter: 'blur(4px)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 50, padding: '1rem' }}>
                    <div className="card" style={{ width: '100%', maxWidth: '640px', padding: '2rem' }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1.25rem' }}>
                            <h2 style={{ fontSize: '1.25rem', fontWeight: 800, color: 'var(--slate-900)' }}>
                                ✨ Impor Silabus / RPP Bebas via Copilot
                            </h2>
                            <button
                                onClick={() => setShowImportModal(false)}
                                style={{ background: 'none', border: 'none', fontSize: '1.5rem', cursor: 'pointer', color: 'var(--slate-400)' }}
                            >
                                ×
                            </button>
                        </div>

                        <p style={{ fontSize: '0.875rem', color: 'var(--slate-600)', marginBottom: '1rem' }}>
                            Tempelkan teks silabus, catatan RPP, atau ringkasan topik ajar. Copilot Gemini akan mengekstraksi sasaran ajar dan menandai ambiguitas secara otomatis (TI-04).
                        </p>

                        <form onSubmit={handleImportSyllabus}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '0.5rem' }}>
                                <label style={{ fontSize: '0.8125rem', fontWeight: 600, color: 'var(--slate-700)' }}>
                                    Teks Silabus:
                                </label>
                                <button
                                    type="button"
                                    onClick={prefillSampleText}
                                    style={{ background: 'none', border: 'none', color: 'var(--primary-600)', fontSize: '0.75rem', fontWeight: 600, cursor: 'pointer' }}
                                >
                                    + Isi Contoh Silabus Fabel
                                </button>
                            </div>

                            <textarea
                                value={rawText}
                                onChange={(e) => setRawText(e.target.value)}
                                rows={7}
                                placeholder="Tempel silabus di sini..."
                                required
                                style={{
                                    width: '100%',
                                    padding: '0.75rem',
                                    borderRadius: 'var(--radius-md)',
                                    border: '1px solid var(--slate-300)',
                                    fontSize: '0.875rem',
                                    fontFamily: 'var(--font-sans)',
                                    marginBottom: '1.25rem',
                                    resize: 'vertical',
                                }}
                            />

                            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '0.75rem' }}>
                                <button
                                    type="button"
                                    onClick={() => setShowImportModal(false)}
                                    className="btn btn-outline"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={isProcessing || rawText.trim().length < 10}
                                    className="btn btn-primary"
                                >
                                    {isProcessing ? 'Copilot Sedang Mengekstraksi...' : '✨ Ekstraksi dengan Copilot'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Modal Wizard 5 Langkah */}
            {showWizardModal && (
                <div style={{ position: 'fixed', inset: 0, backgroundColor: 'rgba(15, 23, 42, 0.6)', backdropFilter: 'blur(4px)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 50, padding: '1rem' }}>
                    <div className="card" style={{ width: '100%', maxWidth: '580px', padding: '2rem' }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1.25rem' }}>
                            <h2 style={{ fontSize: '1.25rem', fontWeight: 800, color: 'var(--slate-900)' }}>
                                🧭 Wizard Penyusunan Rencana Ajar
                            </h2>
                            <button
                                onClick={() => setShowWizardModal(false)}
                                style={{ background: 'none', border: 'none', fontSize: '1.5rem', cursor: 'pointer', color: 'var(--slate-400)' }}
                            >
                                ×
                            </button>
                        </div>

                        <form onSubmit={handleWizardSubmit}>
                            <div style={{ marginBottom: '1.25rem' }}>
                                <label style={{ display: 'block', fontSize: '0.875rem', fontWeight: 600, color: 'var(--slate-700)', marginBottom: '0.375rem' }}>
                                    Judul Rencana:
                                </label>
                                <input
                                    type="text"
                                    value={wizardTitle}
                                    onChange={(e) => setWizardTitle(e.target.value)}
                                    required
                                    style={{ width: '100%', padding: '0.625rem', borderRadius: 'var(--radius-md)', border: '1px solid var(--slate-300)', fontSize: '0.875rem' }}
                                />
                            </div>

                            <div style={{ marginBottom: '1.25rem' }}>
                                <label style={{ display: 'block', fontSize: '0.875rem', fontWeight: 600, color: 'var(--slate-700)', marginBottom: '0.5rem' }}>
                                    Pilih Kompetensi yang Ingin Dicapai:
                                </label>
                                <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
                                    {competencies.map((c) => (
                                        <label key={c.id} style={{ display: 'flex', alignItems: 'flex-start', gap: '0.5rem', fontSize: '0.875rem', color: 'var(--slate-800)', cursor: 'pointer' }}>
                                            <input
                                                type="checkbox"
                                                checked={selectedCodes.includes(c.code)}
                                                onChange={(e) => {
                                                    if (e.target.checked) {
                                                        setSelectedCodes([...selectedCodes, c.code]);
                                                    } else {
                                                        setSelectedCodes(selectedCodes.filter((code) => code !== c.code));
                                                    }
                                                }}
                                                style={{ marginTop: '3px' }}
                                            />
                                            <div>
                                                <strong>{c.code}</strong> - {c.title}
                                            </div>
                                        </label>
                                    ))}
                                </div>
                            </div>

                            <div style={{ marginBottom: '1.5rem' }}>
                                <label style={{ display: 'block', fontSize: '0.875rem', fontWeight: 600, color: 'var(--slate-700)', marginBottom: '0.375rem' }}>
                                    Jumlah Pertemuan / Sesi:
                                </label>
                                <select
                                    value={sessionCount}
                                    onChange={(e) => setSessionCount(Number(e.target.value))}
                                    style={{ width: '100%', padding: '0.625rem', borderRadius: 'var(--radius-md)', border: '1px solid var(--slate-300)', fontSize: '0.875rem' }}
                                >
                                    <option value={4}>4 Pertemuan (1 Bulan Intensif)</option>
                                    <option value={8}>8 Pertemuan (2 Bulan Reguler)</option>
                                    <option value={12}>12 Pertemuan (1 Semester)</option>
                                </select>
                            </div>

                            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '0.75rem' }}>
                                <button
                                    type="button"
                                    onClick={() => setShowWizardModal(false)}
                                    className="btn btn-outline"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={isProcessing || selectedCodes.length === 0}
                                    className="btn btn-primary"
                                >
                                    {isProcessing ? 'Menyusun...' : 'Susun Rencana Ajar'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
