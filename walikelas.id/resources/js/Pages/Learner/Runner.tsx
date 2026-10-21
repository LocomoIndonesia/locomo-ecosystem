import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';

interface Step {
    step_key: string;
    component_type: string;
    props: any;
}

interface RunnerProps {
    raw_token: string;
    assignment: {
        id: string;
        status: string;
        requires_guardian: boolean;
    };
    learner: {
        display_name: string;
        avatar_id: string;
    };
    mission: {
        id: string;
        title: string;
        kind: string;
        estimated_minutes: number;
        steps: Step[];
    };
    attempt_id: string;
}

export default function Runner({ raw_token, assignment, learner, mission, attempt_id }: RunnerProps) {
    const steps = mission.steps || [];
    const [currentStepIndex, setCurrentStepIndex] = useState(0);
    const [secondsLeft, setSecondsLeft] = useState(mission.estimated_minutes * 60 || 600);
    const [revealedHints, setRevealedHints] = useState<number>(0);
    const [selectedOption, setSelectedOption] = useState<string | null>(null);
    const [textAnswer, setTextAnswer] = useState<string>('');
    const [isFinished, setIsFinished] = useState(false);
    const [safetyAlertTriggered, setSafetyAlertTriggered] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);

    // Timer countdown ramah anak (10 menit batas sesi aman layar)
    useEffect(() => {
        if (isFinished || safetyAlertTriggered) return;
        const timer = setInterval(() => {
            setSecondsLeft((prev) => (prev > 0 ? prev - 1 : 0));
        }, 1000);
        return () => clearInterval(timer);
    }, [isFinished, safetyAlertTriggered]);

    const formatTimer = (sec: number) => {
        const m = Math.floor(sec / 60);
        const s = sec % 60;
        return `${m}:${s < 10 ? '0' : ''}${s}`;
    };

    const currentStep = steps[currentStepIndex] || null;

    const handleNextStep = async () => {
        if (!currentStep) return;
        setIsSubmitting(true);

        const answerPayload = currentStep.component_type === 'SingleChoiceCard'
            ? { selected_option: selectedOption }
            : { text: textAnswer };

        try {
            const res = await fetch(`/runner/${raw_token}/step`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                },
                body: JSON.stringify({
                    attempt_id,
                    step_key: currentStep.step_key,
                    answer: answerPayload,
                    hints_used: revealedHints,
                }),
            });

            const data = await res.json();

            // TI-10 & TAT-16: Circuit Breaker Safeguarding Anak
            if (data.safety_flag) {
                setSafetyAlertTriggered(true);
                setIsSubmitting(false);
                return;
            }

            if (currentStepIndex + 1 < steps.length) {
                setCurrentStepIndex(currentStepIndex + 1);
                setSelectedOption(null);
                setTextAnswer('');
                setRevealedHints(0);
            } else {
                // Selesai seluruh langkah digital (TAT-13)
                await fetch(`/runner/${raw_token}/finish`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                    },
                    body: JSON.stringify({ attempt_id, active_seconds: 600 - secondsLeft }),
                });
                setIsFinished(true);
            }
        } catch (err) {
            console.error('Submission error:', err);
        } finally {
            setIsSubmitting(false);
        }
    };

    // Layar Safeguarding Darurat (TI-10, TAT-16)
    if (safetyAlertTriggered) {
        return (
            <div style={{ minHeight: '100vh', display: 'flex', alignItems: 'center', justifyContent: 'center', backgroundColor: '#f8fafc', padding: '1.5rem' }}>
                <Head title="Jeda Membaca Sejenak" />
                <div className="card" style={{ maxWidth: '480px', textAlign: 'center', padding: '2.5rem', borderLeft: '4px solid var(--accent-500)' }}>
                    <div style={{ fontSize: '3rem', marginBottom: '1rem' }}>🌱</div>
                    <h2 style={{ fontSize: '1.375rem', fontWeight: 800, color: 'var(--slate-900)', marginBottom: '0.75rem' }}>
                        Kegiatan Membaca Dijeda
                    </h2>
                    <p style={{ fontSize: '1rem', color: 'var(--slate-700)', lineHeight: 1.6, marginBottom: '1.5rem' }}>
                        Mari beristirahat dan berbincang dengan orang dewasa di dekatmu (Ibu, Ayah, atau Guru). Kamu anak yang hebat!
                    </p>
                    <div style={{ fontSize: '0.8125rem', color: 'var(--slate-500)' }}>
                        Pendidik telah diberitahu untuk mendampingimu.
                    </div>
                </div>
            </div>
        );
    }

    // Layar Selesai & Ajakan Jeda Layar (TAT-13: Anti infinite loop / eye comfort)
    if (isFinished) {
        return (
            <div style={{ minHeight: '100vh', display: 'flex', alignItems: 'center', justifyContent: 'center', backgroundColor: '#f0fdf4', padding: '1.5rem' }}>
                <Head title="Misi Membaca Selesai!" />
                <div className="card" style={{ maxWidth: '500px', textAlign: 'center', padding: '2.5rem', borderLeft: '4px solid var(--success-500)' }}>
                    <div style={{ fontSize: '3.5rem', marginBottom: '0.5rem' }}>🎉</div>
                    <h2 style={{ fontSize: '1.5rem', fontWeight: 800, color: 'var(--slate-900)', marginBottom: '0.5rem' }}>
                        Hebat Sekali, {learner.display_name}!
                    </h2>
                    <p style={{ fontSize: '1rem', color: 'var(--slate-600)', marginBottom: '1.75rem' }}>
                        Kamu telah menyelesaikan misi <strong>"{mission.title}"</strong> dengan sangat baik.
                    </p>

                    {assignment.requires_guardian && (
                        <div style={{ backgroundColor: '#fff', padding: '1rem', borderRadius: 'var(--radius-lg)', border: '1px solid var(--slate-200)', marginBottom: '1.5rem', textAlign: 'left' }}>
                            <div style={{ fontSize: '0.875rem', fontWeight: 700, color: 'var(--primary-700)', marginBottom: '0.25rem' }}>
                                🏠 Misi Rumah Bersama Keluarga:
                            </div>
                            <div style={{ fontSize: '0.8125rem', color: 'var(--slate-600)' }}>
                                Ceritakan kembali kisah ini kepada Ayah atau Ibu malam ini. Orang tua akan memberikan konfirmasi tanda bintang!
                            </div>
                        </div>
                    )}

                    <div style={{ backgroundColor: 'var(--accent-100)', padding: '1.25rem', borderRadius: 'var(--radius-lg)', marginBottom: '1.5rem' }}>
                        <div style={{ fontSize: '1.25rem', marginBottom: '0.25rem' }}>💧 👀</div>
                        <strong style={{ fontSize: '0.9375rem', color: 'var(--accent-600)' }}>Saatnya Istirahat Layar!</strong>
                        <p style={{ fontSize: '0.8125rem', color: 'var(--slate-700)', marginTop: '0.25rem' }}>
                            Yuk alihkan pandanganmu ke luar jendela, minum segelas air putih, dan regangkan badanmu.
                        </p>
                    </div>

                    <div style={{ fontSize: '0.8125rem', color: 'var(--slate-400)' }}>
                        Kamu boleh menutup halaman peramban ini sekarang.
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div style={{ minHeight: '100vh', backgroundColor: '#f8fafc', display: 'flex', flexDirection: 'column' }}>
            <Head title={`Membaca: ${mission.title}`} />

            {/* Runner Minimal Header (Anti-Distraction) */}
            <header style={{ backgroundColor: '#fff', borderBottom: '1px solid var(--slate-200)', padding: '0.75rem 1.25rem' }}>
                <div style={{ maxWidth: '780px', margin: '0 auto', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                        <div style={{ width: '38px', height: '38px', borderRadius: '50%', backgroundColor: 'var(--primary-100)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '1.25rem' }}>
                            🐾
                        </div>
                        <div>
                            <div style={{ fontWeight: 800, fontSize: '0.9375rem', color: 'var(--slate-900)' }}>
                                {learner.display_name}
                            </div>
                            <div style={{ fontSize: '0.75rem', color: 'var(--slate-500)' }}>
                                Langkah {currentStepIndex + 1} dari {steps.length}
                            </div>
                        </div>
                    </div>

                    {/* Progress dots & Timer */}
                    <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
                        <div style={{ display: 'flex', gap: '0.375rem' }}>
                            {steps.map((_, idx) => (
                                <div
                                    key={idx}
                                    style={{
                                        width: '10px',
                                        height: '10px',
                                        borderRadius: '50%',
                                        backgroundColor: idx <= currentStepIndex ? 'var(--primary-600)' : 'var(--slate-200)',
                                        transition: 'all 0.2s',
                                    }}
                                />
                            ))}
                        </div>

                        <div className="badge badge-accent" style={{ fontWeight: 700, fontSize: '0.8125rem' }}>
                            ⏱️ {formatTimer(secondsLeft)}
                        </div>
                    </div>
                </div>
            </header>

            {/* Runner Body */}
            <main style={{ flex: 1, padding: '2rem 1.25rem', display: 'flex', justifyContent: 'center' }}>
                <div style={{ width: '100%', maxWidth: '780px' }}>
                    {currentStep ? (
                        <div className="card" style={{ padding: '2rem', boxShadow: 'var(--shadow-lg)' }}>
                            {/* Passage Section */}
                            {currentStep.props.passage && (
                                <div style={{ backgroundColor: 'var(--slate-50)', padding: '1.25rem 1.5rem', borderRadius: 'var(--radius-lg)', border: '1px solid var(--slate-200)', marginBottom: '1.5rem', lineHeight: 1.8, fontSize: '1.0625rem', color: 'var(--slate-800)' }}>
                                    {currentStep.props.passage}
                                </div>
                            )}

                            {/* Question / Prompt */}
                            <h3 style={{ fontSize: '1.1875rem', fontWeight: 700, color: 'var(--slate-900)', marginBottom: '1.25rem' }}>
                                {currentStep.props.question || currentStep.props.prompt}
                            </h3>

                            {/* Render SDUI Component based on component_type */}
                            {currentStep.component_type === 'SingleChoiceCard' && (
                                <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem', marginBottom: '1.5rem' }}>
                                    {currentStep.props.options?.map((opt: any) => (
                                        <div
                                            key={opt.id}
                                            onClick={() => setSelectedOption(opt.id)}
                                            style={{
                                                padding: '1rem 1.25rem',
                                                borderRadius: 'var(--radius-lg)',
                                                border: `2px solid ${selectedOption === opt.id ? 'var(--primary-600)' : 'var(--slate-200)'}`,
                                                backgroundColor: selectedOption === opt.id ? 'var(--primary-50)' : '#fff',
                                                cursor: 'pointer',
                                                fontSize: '1rem',
                                                fontWeight: 600,
                                                color: selectedOption === opt.id ? 'var(--primary-700)' : 'var(--slate-800)',
                                                display: 'flex',
                                                alignItems: 'center',
                                                gap: '0.75rem',
                                                transition: 'all 0.15s ease',
                                            }}
                                        >
                                            <div style={{ width: '22px', height: '22px', borderRadius: '50%', border: `2px solid ${selectedOption === opt.id ? 'var(--primary-600)' : 'var(--slate-300)'}`, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                                {selectedOption === opt.id && (
                                                    <div style={{ width: '10px', height: '10px', borderRadius: '50%', backgroundColor: 'var(--primary-600)' }} />
                                                )}
                                            </div>
                                            <span>{opt.text}</span>
                                        </div>
                                    ))}
                                </div>
                            )}

                            {currentStep.component_type === 'ShortPromptReader' && (
                                <div style={{ marginBottom: '1.5rem' }}>
                                    <textarea
                                        value={textAnswer}
                                        onChange={(e) => setTextAnswer(e.target.value)}
                                        rows={4}
                                        placeholder="Ketik jawabanmu di sini dengan kalimatmu sendiri..."
                                        style={{
                                            width: '100%',
                                            padding: '1rem',
                                            borderRadius: 'var(--radius-lg)',
                                            border: '1px solid var(--slate-300)',
                                            fontSize: '1rem',
                                            fontFamily: 'var(--font-sans)',
                                        }}
                                    />
                                </div>
                            )}

                            {/* Stepped Hints Section */}
                            {currentStep.props.hints && currentStep.props.hints.length > 0 && (
                                <div style={{ marginBottom: '1.5rem', backgroundColor: 'var(--accent-100)', padding: '0.875rem 1.25rem', borderRadius: 'var(--radius-lg)' }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                        <span style={{ fontSize: '0.8125rem', fontWeight: 700, color: 'var(--accent-600)' }}>
                                            💡 Butuh Petunjuk ({revealedHints}/{currentStep.props.hints.length})?
                                        </span>
                                        {revealedHints < currentStep.props.hints.length && (
                                            <button
                                                type="button"
                                                onClick={() => setRevealedHints(revealedHints + 1)}
                                                className="btn"
                                                style={{ padding: '0.25rem 0.625rem', fontSize: '0.75rem', backgroundColor: '#fff', color: 'var(--accent-600)', border: '1px solid var(--accent-500)' }}
                                            >
                                                + Buka Petunjuk {revealedHints + 1}
                                            </button>
                                        )}
                                    </div>

                                    {revealedHints > 0 && (
                                        <div style={{ marginTop: '0.5rem', fontSize: '0.875rem', color: 'var(--slate-800)' }}>
                                            {currentStep.props.hints.slice(0, revealedHints).map((hint: string, hIdx: number) => (
                                                <div key={hIdx} style={{ marginTop: '0.25rem' }}>
                                                    • {hint}
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            )}

                            {/* Action Button */}
                            <div style={{ display: 'flex', justifyContent: 'flex-end' }}>
                                <button
                                    onClick={handleNextStep}
                                    disabled={
                                        isSubmitting ||
                                        (currentStep.component_type === 'SingleChoiceCard' && !selectedOption) ||
                                        (currentStep.component_type === 'ShortPromptReader' && textAnswer.trim().length === 0)
                                    }
                                    className="btn btn-primary"
                                    style={{ padding: '0.75rem 2rem', fontSize: '1rem' }}
                                >
                                    {isSubmitting
                                        ? 'Menyimpan...'
                                        : currentStepIndex + 1 === steps.length
                                        ? 'Selesai Membaca 🏁'
                                        : 'Langkah Berikutnya →'}
                                </button>
                            </div>
                        </div>
                    ) : (
                        <div className="card" style={{ padding: '2rem', textAlign: 'center' }}>
                            <p>Misi ini belum memiliki langkah aktivitas yang terdaftar.</p>
                        </div>
                    )}
                </div>
            </main>
        </div>
    );
}
