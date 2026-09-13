import React, { useState } from 'react';
import { Volume2, VolumeX, Sparkles, CheckCircle2 } from 'lucide-react';

interface AudioPreferencesProps {
    initialEnabled: boolean;
}

export default function AudioPreferencesForm({ initialEnabled }: AudioPreferencesProps) {
    const [enabled, setEnabled] = useState(initialEnabled);
    const [saving, setSaving] = useState(false);
    const [savedNotice, setSavedNotice] = useState(false);

    const handleToggle = async (val: boolean) => {
        setEnabled(val);
        setSaving(true);
        try {
            const res = await fetch(route('profile.preferences'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                },
                body: JSON.stringify({ audio_sfx_enabled: val }),
            });
            const data = await res.json();
            if (data.success) {
                // Also update localStorage so client games/sound effects pick it up immediately
                localStorage.setItem('piticoin_audio_sfx', val ? '1' : '0');
                setSavedNotice(true);
                setTimeout(() => setSavedNotice(false), 3000);
            }
        } catch (err) {
            console.error('Failed to update audio preferences', err);
        } finally {
            setSaving(false);
        }
    };

    return (
        <div className="glass-panel p-6 sm:p-8 rounded-2xl border border-white/10 space-y-5">
            <div className="border-b border-white/10 pb-4">
                <h3 className="text-lg font-bold text-white flex items-center gap-2">
                    <Volume2 size={20} className="text-[#ffd700]" />
                    <span>Preferensi Suara & Audio Efek Ekspedisi</span>
                </h3>
                <p className="text-xs text-gray-400 mt-1">
                    Atur efek suara (SFX) putaran kartu KOA, gemerincing koin emas, dan notifikasi misi.
                </p>
            </div>

            <div className="p-4 rounded-xl bg-black/40 border border-white/10 flex items-center justify-between gap-4">
                <div className="flex items-center gap-3">
                    <div className={`p-3 rounded-xl border ${enabled ? 'bg-yellow-500/10 border-yellow-500/30 text-[#ffd700]' : 'bg-white/5 border-white/10 text-gray-400'}`}>
                        {enabled ? <Volume2 size={22} /> : <VolumeX size={22} />}
                    </div>
                    <div>
                        <span className="font-semibold text-sm text-white block">
                            Efek Suara Gamifikasi (SFX)
                        </span>
                        <span className="text-xs text-gray-400 block">
                            {enabled ? 'Audio aktif untuk spin KOA & reward misi' : 'Audio dimatikan (mode hening)'}
                        </span>
                    </div>
                </div>

                <div className="flex items-center gap-3">
                    {savedNotice && (
                        <span className="text-xs text-emerald-400 flex items-center gap-1">
                            <CheckCircle2 size={13} />
                            Tersimpan
                        </span>
                    )}
                    <button
                        type="button"
                        onClick={() => handleToggle(!enabled)}
                        disabled={saving}
                        className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none ${
                            enabled ? 'bg-[#ffd700]' : 'bg-white/20'
                        }`}
                    >
                        <span
                            className={`inline-block h-4 w-4 transform rounded-full bg-black transition-transform ${
                                enabled ? 'translate-x-6' : 'translate-x-1 bg-gray-300'
                            }`}
                        />
                    </button>
                </div>
            </div>
        </div>
    );
}
