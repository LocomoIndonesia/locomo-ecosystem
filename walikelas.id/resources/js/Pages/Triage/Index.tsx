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
        user?: { name: string };
    };
}

export default function TriageIndex({ program, queue }: TriageQueueProps) {
    const [actionLoading, setActionLoading] = useState<string | null>(null);
    const [successMessage, setSuccessMessage] = useState<string | null>(null);
    const [selectedReport, setSelectedReport] = useState<any | null>(null);
    const [showReportModal, setShowReportModal] = useState(false);

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
        // Fetch first enrollment if available or simulated
        fetch(`/programs/${program.id}/reports/generate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({
                enrollment_id: 'default',
                auto_approve: true,
            }),
        })
            .then((res) => res.json())
            .then((data) => {
                setActionLoading(null);
                if (data.report) {
                    setSelectedReport(data.report);
                    setShowReportModal(true);
                }
            })
            .catch(() => setActionLoading(null));
    };

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100 p-4 md:p-8">
            <Head title={`Triage Guru - ${program.title}`} />

            <div className="max-w-6xl mx-auto space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800 pb-5">
                    <div>
                        <div className="flex items-center gap-3">
                            <Link
                                href={`/programs/${program.id}`}
                                className="text-xs px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-md transition"
                            >
                                ← Ruang Kerja Kelas
                            </Link>
                            <span className="text-xs uppercase tracking-wider font-semibold px-2 py-0.5 rounded bg-indigo-900/50 text-indigo-300 border border-indigo-700/50">
                                {program.mode === 'school' ? 'Sekolah' : 'Privat'} • Kelas {program.grade_level}
                            </span>
                        </div>
                        <h1 className="text-2xl md:text-3xl font-extrabold text-white mt-2">
                            Inbox Triage & Analisis Intervensi Guru
                        </h1>
                        <p className="text-sm text-slate-400 mt-1">
                            Antrean prioritas siswa yang memerlukan perhatian, penelaahan tugas, dan pengesahan intervensi AI.
                        </p>
                    </div>

                    <div className="flex items-center gap-3">
                        <div className="bg-slate-900 px-4 py-2 rounded-xl border border-slate-800 text-center">
                            <div className="text-2xl font-black text-amber-400">{queue.total_items}</div>
                            <div className="text-xs text-slate-400">Kasus Butuh Aksi</div>
                        </div>
                    </div>
                </div>

                {successMessage && (
                    <div className="p-4 bg-emerald-950/60 border border-emerald-600/60 text-emerald-200 rounded-xl flex items-center justify-between">
                        <span>{successMessage}</span>
                        <button onClick={() => setSuccessMessage(null)} className="text-emerald-400 font-bold ml-4">
                            ✕
                        </button>
                    </div>
                )}

                {/* 1. Urgent Safeguarding Alerts (TI-10) */}
                {queue.safety_alerts.length > 0 && (
                    <div className="bg-rose-950/40 border-2 border-rose-600/70 rounded-2xl p-5 shadow-lg shadow-rose-950/30">
                        <div className="flex items-center gap-2 text-rose-300 font-bold text-lg mb-3">
                            <span className="text-xl">🚨</span>
                            <span>Peringatan Keamanan Siswa (Safeguarding Circuit Breaker)</span>
                            <span className="text-xs bg-rose-600 text-white px-2 py-0.5 rounded-full uppercase ml-auto">
                                Prioritas Darurat
                            </span>
                        </div>
                        <div className="space-y-3">
                            {queue.safety_alerts.map((alert) => (
                                <div
                                    key={alert.id}
                                    className="bg-slate-900/80 p-4 rounded-xl border border-rose-800/40 flex flex-col md:flex-row md:items-center justify-between gap-4"
                                >
                                    <div>
                                        <div className="font-semibold text-white">
                                            {alert.learner_name} —{' '}
                                            <span className="text-slate-400 text-sm">{alert.mission_title}</span>
                                        </div>
                                        <p className="text-sm text-rose-200 mt-1">{alert.message}</p>
                                    </div>
                                    <div className="flex gap-2">
                                        <button className="px-3 py-1.5 bg-rose-700 hover:bg-rose-600 text-white text-xs font-semibold rounded-lg transition">
                                            Tinjau Respons & Kontak Wali
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {/* 2. Guardian Help Requests (Offline Checkpoint need_help) */}
                {queue.guardian_requests.length > 0 && (
                    <div className="bg-amber-950/30 border border-amber-600/50 rounded-2xl p-5">
                        <div className="flex items-center gap-2 text-amber-300 font-bold text-lg mb-3">
                            <span className="text-xl">👨‍👩‍👧</span>
                            <span>Permintaan Bantuan Wali Murid (Tugas Rumah)</span>
                        </div>
                        <div className="space-y-3">
                            {queue.guardian_requests.map((req) => (
                                <div
                                    key={req.id}
                                    className="bg-slate-900/80 p-4 rounded-xl border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4"
                                >
                                    <div>
                                        <div className="font-semibold text-white">
                                            Siswa: <span className="text-amber-300">{req.learner_name}</span> (Wali: {req.guardian_name})
                                        </div>
                                        <div className="text-xs text-slate-400 mt-0.5">Aktivitas: {req.mission_title}</div>
                                        <p className="text-sm text-slate-300 mt-2">{req.message}</p>
                                    </div>
                                    <div className="flex gap-2">
                                        <button className="px-3 py-1.5 bg-amber-600 hover:bg-amber-500 text-slate-950 font-semibold text-xs rounded-lg transition">
                                            Kirim Panduan Tambahan
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {/* 3. AI Intervention Suggestions Awaiting Educator Decision (TI-05) */}
                <div className="bg-slate-900/90 border border-slate-800 rounded-2xl p-6">
                    <div className="flex items-center justify-between mb-4">
                        <div className="flex items-center gap-2">
                            <span className="text-xl">💡</span>
                            <h2 className="text-lg font-bold text-white">Usulan Intervensi Copilot (Menunggu Putusan Guru)</h2>
                        </div>
                        <span className="text-xs bg-indigo-900/60 text-indigo-300 border border-indigo-700/50 px-2 py-0.5 rounded">
                            Human-in-the-Loop (TI-05)
                        </span>
                    </div>

                    {queue.pending_ai_suggestions.length === 0 ? (
                        <div className="p-6 text-center text-slate-400 border border-dashed border-slate-800 rounded-xl">
                            Belum ada usulan intervensi AI yang pending. Seluruh saran telah ditinjau.
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {queue.pending_ai_suggestions.map((sugg) => (
                                <div
                                    key={sugg.id}
                                    className="p-5 bg-slate-950/80 border border-indigo-950 rounded-xl space-y-3"
                                >
                                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-2 border-b border-slate-800/80 pb-3">
                                        <div>
                                            <span className="text-xs font-semibold text-indigo-400 uppercase tracking-wider">
                                                Sasaran Kompetensi: {sugg.payload.competency_code || 'LIT-B-EXP-01'}
                                            </span>
                                            <h3 className="font-bold text-white text-base mt-0.5">
                                                Siswa: {sugg.learner_name}
                                            </h3>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <span className="text-xs px-2 py-0.5 rounded bg-slate-800 text-slate-300">
                                                Tingkat Kepastian AI: {sugg.uncertainty}
                                            </span>
                                        </div>
                                    </div>

                                    <div>
                                        <p className="text-sm font-medium text-slate-200">
                                            {sugg.payload.description || sugg.rationale}
                                        </p>
                                        <p className="text-xs text-slate-400 mt-1 italic">
                                            Alasan Pedagogis: "{sugg.rationale}"
                                        </p>
                                    </div>

                                    <div className="flex items-center gap-2 pt-2 border-t border-slate-900">
                                        <button
                                            disabled={actionLoading === sugg.id}
                                            onClick={() => handleDecision(sugg.id, 'approve')}
                                            className="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs rounded-lg transition disabled:opacity-50"
                                        >
                                            ✓ Setujui Intervensi (1-Klik)
                                        </button>
                                        <button
                                            disabled={actionLoading === sugg.id}
                                            onClick={() => handleDecision(sugg.id, 'reject')}
                                            className="px-4 py-2 bg-slate-800 hover:bg-rose-900/60 text-slate-300 hover:text-rose-200 font-semibold text-xs rounded-lg transition disabled:opacity-50"
                                        >
                                            ✕ Tolak Saran
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {/* 4. Submissions Awaiting Review & Rubric Scoring */}
                <div className="bg-slate-900/90 border border-slate-800 rounded-2xl p-6">
                    <div className="flex items-center justify-between mb-4">
                        <div className="flex items-center gap-2">
                            <span className="text-xl">📝</span>
                            <h2 className="text-lg font-bold text-white">Tugas Masuk & Penilaian Rubrik Otomatis</h2>
                        </div>
                        <span className="text-xs text-slate-400">
                            {queue.pending_submissions.length} tugas terkumpul
                        </span>
                    </div>

                    {queue.pending_submissions.length === 0 ? (
                        <div className="p-6 text-center text-slate-400 border border-dashed border-slate-800 rounded-xl">
                            Semua tugas siswa telah dinilai atau belum ada submisi baru.
                        </div>
                    ) : (
                        <div className="space-y-3">
                            {queue.pending_submissions.map((sub) => (
                                <div
                                    key={sub.id}
                                    className="p-4 bg-slate-950/80 border border-slate-800 rounded-xl flex flex-col md:flex-row md:items-center justify-between gap-4"
                                >
                                    <div>
                                        <div className="font-semibold text-white">{sub.learner_name}</div>
                                        <div className="text-xs text-slate-400">
                                            {sub.mission_title} • {sub.response_count} respons tersimpan
                                        </div>
                                    </div>

                                    <div className="flex gap-2">
                                        <button className="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-lg transition">
                                            Penilaian Rubrik Berbantuan Gemini
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {/* 5. 3-Layer Report Publishing Showcase */}
                <div className="bg-gradient-to-r from-slate-900 to-indigo-950/40 border border-indigo-800/40 rounded-2xl p-6">
                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <span className="text-xs uppercase tracking-wider text-indigo-400 font-bold">
                                Transparansi Progres Siswa (TI-12)
                            </span>
                            <h2 className="text-xl font-extrabold text-white mt-1">
                                Laporan Perkembangan 3-Lapis (Fakta, Temuan, Rekomendasi)
                            </h2>
                            <p className="text-xs text-slate-400 mt-1 max-w-2xl">
                                Menyajikan fakta kehadiran/misi, temuan rubrik dengan bukti eksplisit, serta rekomendasi tindak lanjut bagi orang tua dengan tautan web aman.
                            </p>
                        </div>
                        <button
                            onClick={handleGenerateDemoReport}
                            className="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg transition"
                        >
                            + Terbitkan Laporan 3-Lapis
                        </button>
                    </div>
                </div>
            </div>

            {/* Modal Laporan 3-Lapis */}
            {showReportModal && selectedReport && (
                <div className="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4">
                    <div className="bg-slate-900 border border-slate-700 rounded-2xl max-w-2xl w-full p-6 space-y-5 max-h-[90vh] overflow-y-auto">
                        <div className="flex items-center justify-between border-b border-slate-800 pb-3">
                            <h3 className="text-lg font-bold text-white">Laporan Perkembangan Siswa (3-Lapis)</h3>
                            <button onClick={() => setShowReportModal(false)} className="text-slate-400 hover:text-white">
                                ✕
                            </button>
                        </div>

                        {/* Lapis 1: Fakta */}
                        <div className="p-4 bg-slate-950 rounded-xl border border-slate-800">
                            <div className="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-2">
                                Lapisan 1: Fakta Partisipasi & Kehadiran
                            </div>
                            <div className="grid grid-cols-2 gap-3 text-sm">
                                <div>Sesi Dihadiri: {selectedReport.facts_layer?.sessions_attended || 0}</div>
                                <div>Misi Selesai: {selectedReport.facts_layer?.missions_completed || 0}</div>
                                <div>Tingkat Penyelesaian: {selectedReport.facts_layer?.completion_rate_percentage || 100}%</div>
                            </div>
                        </div>

                        {/* Lapis 2: Temuan */}
                        <div className="p-4 bg-slate-950 rounded-xl border border-slate-800">
                            <div className="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-2">
                                Lapisan 2: Temuan Bukti Asesmen & Rubrik
                            </div>
                            <p className="text-xs text-slate-300">
                                Berdasarkan evaluasi terhadap kriteria rubrik terkurasi, siswa menunjukkan pemahaman baik pada teks naratif fabel.
                            </p>
                        </div>

                        {/* Lapis 3: Rekomendasi */}
                        <div className="p-4 bg-slate-950 rounded-xl border border-slate-800">
                            <div className="text-xs font-bold text-amber-400 uppercase tracking-wider mb-2">
                                Lapisan 3: Rekomendasi & Tindak Lanjut Rumah
                            </div>
                            <p className="text-xs text-slate-300">
                                Ajak anak menceritakan kembali fabel favorit selama 5 menit saat santap malam bersama keluarga.
                            </p>
                        </div>

                        <div className="flex justify-end gap-3 pt-3 border-t border-slate-800">
                            <button
                                onClick={() => {
                                    alert('Tautan laporan aman disalin ke clipboard!');
                                    setShowReportModal(false);
                                }}
                                className="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs rounded-xl transition"
                            >
                                Salin Tautan WhatsApp Wali
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
