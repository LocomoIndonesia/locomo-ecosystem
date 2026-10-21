import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';

interface Program {
    id: string;
    mode: 'school' | 'private';
    title: string;
    goal_summary: string | null;
    grade_level: number;
    status: string;
    enrollments_count: number;
    organization?: { name: string; npsn?: string };
    active_plan_version?: { version_no: number; status: string };
}

interface DashboardProps {
    auth: {
        user: {
            id: string;
            name: string;
            email: string;
            profile?: { display_name: string; bio: string };
        } | null;
    };
    programs: Program[];
    metrics: {
        school_count: number;
        private_count: number;
        total_learners: number;
    };
}

export default function Dashboard({ auth, programs, metrics }: DashboardProps) {
    const [selectedTab, setSelectedTab] = useState<'all' | 'school' | 'private'>('all');
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [newMode, setNewMode] = useState<'school' | 'private'>('school');
    const [title, setTitle] = useState('');
    const [goal, setGoal] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);

    const filteredPrograms = programs.filter((p) => {
        if (selectedTab === 'all') return true;
        return p.mode === selectedTab;
    });

    const handleCreateProgram = (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);
        router.post('/programs', {
            mode: newMode,
            title,
            goal_summary: goal,
            grade_level: 3,
        }, {
            onFinish: () => {
                setIsSubmitting(false);
                setShowCreateModal(false);
                setTitle('');
                setGoal('');
            }
        });
    };

    const handleSwitchUser = (email: string) => {
        router.post('/auth/switch', { email });
    };

    return (
        <div style={{ minHeight: '100vh', display: 'flex', flexDirection: 'column' }}>
            <Head title="Dashboard Pendidik" />

            {/* Top Navigation */}
            <header className="card" style={{ borderRadius: 0, borderTop: 0, borderLeft: 0, borderRight: 0, position: 'sticky', top: 0, zIndex: 40, backdropFilter: 'blur(8px)', backgroundColor: 'rgba(255, 255, 255, 0.9)' }}>
                <div className="container" style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '0.875rem 1.25rem' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                            <div style={{ width: '38px', height: '38px', borderRadius: '10px', background: 'linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', fontWeight: 800, fontSize: '1.25rem', boxShadow: 'var(--shadow-md)' }}>
                                W
                            </div>
                            <div>
                                <span style={{ fontWeight: 800, fontSize: '1.25rem', color: 'var(--slate-900)', letterSpacing: '-0.02em' }}>WaliKelas</span>
                                <span style={{ fontSize: '0.8125rem', fontWeight: 700, color: 'var(--primary-600)', marginLeft: '4px' }}>MVP 2.0</span>
                            </div>
                        </div>
                        <span className="badge badge-accent" style={{ marginLeft: '0.5rem' }}>Fase B Kelas 3 SD</span>
                    </div>

                    {/* User profile & Dev quick switch */}
                    <div style={{ display: 'flex', alignItems: 'center', gap: '1.25rem' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', backgroundColor: 'var(--slate-100)', padding: '0.375rem 0.75rem', borderRadius: 'var(--radius-lg)' }}>
                            <span style={{ fontSize: '0.8125rem', color: 'var(--slate-500)', fontWeight: 500 }}>Ganti Akun:</span>
                            <button
                                type="button"
                                onClick={() => handleSwitchUser('guru.budi@walikelas.id')}
                                className="btn"
                                style={{
                                    padding: '0.25rem 0.5rem',
                                    fontSize: '0.75rem',
                                    backgroundColor: auth.user?.email === 'guru.budi@walikelas.id' ? '#fff' : 'transparent',
                                    boxShadow: auth.user?.email === 'guru.budi@walikelas.id' ? 'var(--shadow-sm)' : 'none',
                                    color: auth.user?.email === 'guru.budi@walikelas.id' ? 'var(--primary-700)' : 'var(--slate-600)',
                                }}
                            >
                                🏫 Pak Budi (Sekolah)
                            </button>
                            <button
                                type="button"
                                onClick={() => handleSwitchUser('tutor.siti@walikelas.id')}
                                className="btn"
                                style={{
                                    padding: '0.25rem 0.5rem',
                                    fontSize: '0.75rem',
                                    backgroundColor: auth.user?.email === 'tutor.siti@walikelas.id' ? '#fff' : 'transparent',
                                    boxShadow: auth.user?.email === 'tutor.siti@walikelas.id' ? 'var(--shadow-sm)' : 'none',
                                    color: auth.user?.email === 'tutor.siti@walikelas.id' ? 'var(--primary-700)' : 'var(--slate-600)',
                                }}
                            >
                                💡 Bu Siti (Privat)
                            </button>
                        </div>

                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                            <div style={{ width: '36px', height: '36px', borderRadius: '50%', backgroundColor: 'var(--primary-100)', color: 'var(--primary-700)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontWeight: 700, fontSize: '0.875rem' }}>
                                {auth.user?.name.charAt(0) || 'G'}
                            </div>
                            <div style={{ textAlign: 'left', lineHeight: 1.2 }}>
                                <div style={{ fontSize: '0.875rem', fontWeight: 700, color: 'var(--slate-900)' }}>{auth.user?.name}</div>
                                <div style={{ fontSize: '0.75rem', color: 'var(--slate-500)' }}>{auth.user?.profile?.display_name || 'Pendidik'}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {/* Main Content */}
            <main className="container" style={{ flex: 1, padding: '2rem 1.25rem' }}>
                {/* Hero / Header Section */}
                <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', marginBottom: '2rem', flexWrap: 'wrap', gap: '1rem' }}>
                    <div>
                        <h1 style={{ fontSize: '1.875rem', fontWeight: 800, color: 'var(--slate-900)', marginBottom: '0.375rem' }}>
                            Ruang Kerja Pendidik
                        </h1>
                        <p style={{ color: 'var(--slate-600)', fontSize: '0.9375rem', maxWidth: '640px' }}>
                            Kelola program literasi membaca, perancangan ajar berbasis Google Gemini Copilot, dan pantau pertumbuhan belajar siswa sekolah maupun les privat.
                        </p>
                    </div>

                    <button
                        onClick={() => setShowCreateModal(true)}
                        className="btn btn-primary"
                        style={{ boxShadow: 'var(--shadow-md)' }}
                    >
                        <span>+ Program Baru</span>
                    </button>
                </div>

                {/* Metrics Stats */}
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: '1rem', marginBottom: '2rem' }}>
                    <div className="card" style={{ padding: '1.25rem' }}>
                        <div style={{ fontSize: '0.8125rem', fontWeight: 600, color: 'var(--slate-500)', textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                            Total Program
                        </div>
                        <div style={{ fontSize: '1.875rem', fontWeight: 800, color: 'var(--slate-900)', marginTop: '0.25rem' }}>
                            {programs.length}
                        </div>
                        <div style={{ fontSize: '0.8125rem', color: 'var(--slate-500)', marginTop: '0.25rem' }}>
                            Program pembelajaran terkelola
                        </div>
                    </div>

                    <div className="card" style={{ padding: '1.25rem', borderLeft: '4px solid var(--primary-600)' }}>
                        <div style={{ fontSize: '0.8125rem', fontWeight: 600, color: 'var(--primary-600)', textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                            Mode Sekolah
                        </div>
                        <div style={{ fontSize: '1.875rem', fontWeight: 800, color: 'var(--slate-900)', marginTop: '0.25rem' }}>
                            {metrics.school_count}
                        </div>
                        <div style={{ fontSize: '0.8125rem', color: 'var(--slate-500)', marginTop: '0.25rem' }}>
                            Terhubung NPSN Satuan Pendidikan
                        </div>
                    </div>

                    <div className="card" style={{ padding: '1.25rem', borderLeft: '4px solid var(--accent-500)' }}>
                        <div style={{ fontSize: '0.8125rem', fontWeight: 600, color: 'var(--accent-600)', textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                            Mode Privat Mandiri
                        </div>
                        <div style={{ fontSize: '1.875rem', fontWeight: 800, color: 'var(--slate-900)', marginTop: '0.25rem' }}>
                            {metrics.private_count}
                        </div>
                        <div style={{ fontSize: '0.8125rem', color: 'var(--slate-500)', marginTop: '0.25rem' }}>
                            Bebas institusi / Les privat mandiri
                        </div>
                    </div>

                    <div className="card" style={{ padding: '1.25rem', borderLeft: '4px solid var(--success-500)' }}>
                        <div style={{ fontSize: '0.8125rem', fontWeight: 600, color: 'var(--success-700)', textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                            Siswa Terdaftar
                        </div>
                        <div style={{ fontSize: '1.875rem', fontWeight: 800, color: 'var(--slate-900)', marginTop: '0.25rem' }}>
                            {metrics.total_learners}
                        </div>
                        <div style={{ fontSize: '0.8125rem', color: 'var(--slate-500)', marginTop: '0.25rem' }}>
                            Data minimalis aman anak
                        </div>
                    </div>

                    <div className="card" style={{ padding: '1.25rem', borderLeft: '4px solid #8b5cf6' }}>
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                            <div style={{ fontSize: '0.8125rem', fontWeight: 600, color: '#7c3aed', textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                                Telemetri Efisiensi (VC)
                            </div>
                            <span className="badge badge-accent" style={{ fontSize: '0.6875rem' }}>80%+ Saved</span>
                        </div>
                        <div style={{ marginTop: '0.5rem' }}>
                            <a
                                href="/telemetry/export-csv"
                                download
                                className="btn btn-outline"
                                style={{ padding: '0.375rem 0.625rem', fontSize: '0.75rem', fontWeight: 700, borderColor: '#8b5cf6', color: '#7c3aed', display: 'inline-block' }}
                            >
                                📊 Unduh CSV Data Room
                            </a>
                        </div>
                        <div style={{ fontSize: '0.8125rem', color: 'var(--slate-500)', marginTop: '0.375rem' }}>
                            Audit net time saved penelaahan tugas guru
                        </div>
                    </div>
                </div>

                {/* Filter Tabs */}
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '1.25rem', borderBottom: '1px solid var(--slate-200)', paddingBottom: '0.75rem' }}>
                    <div style={{ display: 'flex', gap: '0.5rem' }}>
                        <button
                            onClick={() => setSelectedTab('all')}
                            className="btn"
                            style={{
                                padding: '0.5rem 1rem',
                                fontSize: '0.875rem',
                                backgroundColor: selectedTab === 'all' ? 'var(--primary-600)' : '#fff',
                                color: selectedTab === 'all' ? '#fff' : 'var(--slate-700)',
                            }}
                        >
                            Semua ({programs.length})
                        </button>
                        <button
                            onClick={() => setSelectedTab('school')}
                            className="btn"
                            style={{
                                padding: '0.5rem 1rem',
                                fontSize: '0.875rem',
                                backgroundColor: selectedTab === 'school' ? 'var(--primary-600)' : '#fff',
                                color: selectedTab === 'school' ? '#fff' : 'var(--slate-700)',
                            }}
                        >
                            🏫 Sekolah ({metrics.school_count})
                        </button>
                        <button
                            onClick={() => setSelectedTab('private')}
                            className="btn"
                            style={{
                                padding: '0.5rem 1rem',
                                fontSize: '0.875rem',
                                backgroundColor: selectedTab === 'private' ? 'var(--primary-600)' : '#fff',
                                color: selectedTab === 'private' ? '#fff' : 'var(--slate-700)',
                            }}
                        >
                            💡 Privat ({metrics.private_count})
                        </button>
                    </div>

                    <span style={{ fontSize: '0.8125rem', color: 'var(--slate-500)' }}>
                        Menampilkan {filteredPrograms.length} program
                    </span>
                </div>

                {/* Programs Grid */}
                {filteredPrograms.length === 0 ? (
                    <div className="card" style={{ padding: '3rem', textAlign: 'center' }}>
                        <div style={{ fontSize: '2.5rem', marginBottom: '1rem' }}>📚</div>
                        <h3 style={{ fontSize: '1.25rem', fontWeight: 700, color: 'var(--slate-800)', marginBottom: '0.5rem' }}>
                            Belum Ada Program di Kategori Ini
                        </h3>
                        <p style={{ color: 'var(--slate-500)', fontSize: '0.9375rem', marginBottom: '1.5rem' }}>
                            Klik tombol di bawah untuk membuat program pembelajaran baru.
                        </p>
                        <button onClick={() => setShowCreateModal(true)} className="btn btn-primary">
                            + Buat Program Sekarang
                        </button>
                    </div>
                ) : (
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(340px, 1fr))', gap: '1.25rem' }}>
                        {filteredPrograms.map((program) => (
                            <div key={program.id} className="card" style={{ display: 'flex', flexDirection: 'column', justifyContent: 'space-between', padding: '1.5rem' }}>
                                <div>
                                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '0.75rem' }}>
                                        <span className={program.mode === 'school' ? 'badge badge-primary' : 'badge badge-accent'}>
                                            {program.mode === 'school' ? '🏫 Sekolah' : '💡 Privat Mandiri'}
                                        </span>
                                        <span className="badge badge-neutral">
                                            Fase B (Kelas {program.grade_level})
                                        </span>
                                    </div>

                                    <h3 style={{ fontSize: '1.1875rem', fontWeight: 700, color: 'var(--slate-900)', marginBottom: '0.5rem' }}>
                                        {program.title}
                                    </h3>

                                    <p style={{ fontSize: '0.875rem', color: 'var(--slate-600)', marginBottom: '1rem', minHeight: '2.6em' }}>
                                        {program.goal_summary || 'Fokus penguatan membaca pemahaman, fonik kontekstual, dan urutan kronologi peristiwa.'}
                                    </p>

                                    {program.organization && (
                                        <div style={{ fontSize: '0.8125rem', color: 'var(--slate-500)', marginBottom: '0.75rem', display: 'flex', alignItems: 'center', gap: '0.375rem' }}>
                                            <span>🏛️ {program.organization.name}</span>
                                            {program.organization.npsn && (
                                                <span style={{ color: 'var(--slate-400)' }}>(NPSN: {program.organization.npsn})</span>
                                            )}
                                        </div>
                                    )}

                                    {/* Active Plan Version Status */}
                                    <div style={{ backgroundColor: 'var(--slate-50)', padding: '0.625rem 0.875rem', borderRadius: 'var(--radius-md)', marginBottom: '1rem', border: '1px solid var(--slate-200)', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                                        <div style={{ fontSize: '0.8125rem' }}>
                                            <span style={{ color: 'var(--slate-500)' }}>Rencana Ajar: </span>
                                            {program.active_plan_version ? (
                                                <strong style={{ color: 'var(--success-700)' }}>Versi {program.active_plan_version.version_no} Aktif</strong>
                                            ) : (
                                                <span style={{ color: 'var(--accent-600)', fontWeight: 600 }}>Draf / Belum Disahkan</span>
                                            )}
                                        </div>
                                        <span style={{ fontSize: '0.75rem', color: 'var(--slate-400)' }}>
                                            {program.enrollments_count} Siswa
                                        </span>
                                    </div>
                                </div>

                                <Link
                                    href={`/programs/${program.id}`}
                                    className="btn btn-outline"
                                    style={{ width: '100%', marginTop: '0.5rem' }}
                                >
                                    Buka Ruang Kerja Program →
                                </Link>
                            </div>
                        ))}
                    </div>
                )}
            </main>

            {/* Create Program Modal */}
            {showCreateModal && (
                <div style={{ position: 'fixed', inset: 0, backgroundColor: 'rgba(15, 23, 42, 0.6)', backdropFilter: 'blur(4px)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 50, padding: '1rem' }}>
                    <div className="card" style={{ width: '100%', maxWidth: '520px', padding: '2rem', animation: 'scaleUp 0.15s ease-out' }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1.25rem' }}>
                            <h2 style={{ fontSize: '1.25rem', fontWeight: 800, color: 'var(--slate-900)' }}>
                                Buat Program Pembelajaran
                            </h2>
                            <button
                                onClick={() => setShowCreateModal(false)}
                                style={{ background: 'none', border: 'none', fontSize: '1.5rem', cursor: 'pointer', color: 'var(--slate-400)' }}
                            >
                                ×
                            </button>
                        </div>

                        <form onSubmit={handleCreateProgram}>
                            {/* Mode Picker (TI-01) */}
                            <div style={{ marginBottom: '1.25rem' }}>
                                <label style={{ display: 'block', fontSize: '0.875rem', fontWeight: 600, color: 'var(--slate-700)', marginBottom: '0.5rem' }}>
                                    Pilih Moda Program:
                                </label>
                                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0.75rem' }}>
                                    <div
                                        onClick={() => setNewMode('school')}
                                        style={{
                                            border: `2px solid ${newMode === 'school' ? 'var(--primary-600)' : 'var(--slate-200)'}`,
                                            borderRadius: 'var(--radius-lg)',
                                            padding: '0.875rem',
                                            cursor: 'pointer',
                                            backgroundColor: newMode === 'school' ? 'var(--primary-50)' : '#fff',
                                        }}
                                    >
                                        <div style={{ fontWeight: 700, fontSize: '0.9375rem', color: 'var(--slate-900)' }}>🏫 Sekolah</div>
                                        <div style={{ fontSize: '0.75rem', color: 'var(--slate-500)', marginTop: '2px' }}>Terkait institusi & kelas resmi</div>
                                    </div>

                                    <div
                                        onClick={() => setNewMode('private')}
                                        style={{
                                            border: `2px solid ${newMode === 'private' ? 'var(--accent-500)' : 'var(--slate-200)'}`,
                                            borderRadius: 'var(--radius-lg)',
                                            padding: '0.875rem',
                                            cursor: 'pointer',
                                            backgroundColor: newMode === 'private' ? 'var(--accent-100)' : '#fff',
                                        }}
                                    >
                                        <div style={{ fontWeight: 700, fontSize: '0.9375rem', color: 'var(--slate-900)' }}>💡 Privat Mandiri</div>
                                        <div style={{ fontSize: '0.75rem', color: 'var(--slate-500)', marginTop: '2px' }}>Bebas NPSN untuk tutor privat</div>
                                    </div>
                                </div>
                            </div>

                            <div style={{ marginBottom: '1.25rem' }}>
                                <label style={{ display: 'block', fontSize: '0.875rem', fontWeight: 600, color: 'var(--slate-700)', marginBottom: '0.375rem' }}>
                                    Judul Program:
                                </label>
                                <input
                                    type="text"
                                    value={title}
                                    onChange={(e) => setTitle(e.target.value)}
                                    placeholder={newMode === 'school' ? 'Contoh: Kelas 3B - Literasi Narasi' : 'Contoh: Les Privat Membaca Cepat'}
                                    required
                                    style={{
                                        width: '100%',
                                        padding: '0.625rem 0.875rem',
                                        borderRadius: 'var(--radius-md)',
                                        border: '1px solid var(--slate-300)',
                                        fontSize: '0.9375rem',
                                    }}
                                />
                            </div>

                            <div style={{ marginBottom: '1.5rem' }}>
                                <label style={{ display: 'block', fontSize: '0.875rem', fontWeight: 600, color: 'var(--slate-700)', marginBottom: '0.375rem' }}>
                                    Tujuan Singkat / Catatan:
                                </label>
                                <textarea
                                    value={goal}
                                    onChange={(e) => setGoal(e.target.value)}
                                    placeholder="Fokus pada penguasaan tokoh, alur cerita, dan kosakata fabel..."
                                    rows={3}
                                    style={{
                                        width: '100%',
                                        padding: '0.625rem 0.875rem',
                                        borderRadius: 'var(--radius-md)',
                                        border: '1px solid var(--slate-300)',
                                        fontSize: '0.9375rem',
                                        resize: 'vertical',
                                    }}
                                />
                            </div>

                            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '0.75rem' }}>
                                <button
                                    type="button"
                                    onClick={() => setShowCreateModal(false)}
                                    className="btn btn-outline"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={isSubmitting || !title.trim()}
                                    className="btn btn-primary"
                                >
                                    {isSubmitting ? 'Menyimpan...' : 'Simpan Program'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
