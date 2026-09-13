import React, { useState, useRef } from 'react';
import { useForm, router } from '@inertiajs/react';
import { Camera, Upload, Sparkles, Check, Anchor, Compass, Shield, Store } from 'lucide-react';

interface PresetItem {
    key: string;
    label: string;
    faction: string;
    description: string;
    badge_color: string;
    icon: string;
}

interface AvatarCustomizerProps {
    currentAvatar: string | null;
    presets: PresetItem[];
}

export default function AvatarCustomizer({ currentAvatar, presets }: AvatarCustomizerProps) {
    const [activeTab, setActiveTab] = useState<'preset' | 'upload'>('preset');
    const [previewUrl, setPreviewUrl] = useState<string | null>(null);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const { data, setData, post, processing, errors, reset } = useForm<{
        avatar: File | null;
    }>({
        avatar: null,
    });

    const getPresetIcon = (iconName: string, color: string) => {
        switch (iconName) {
            case 'Anchor':
                return <Anchor size={24} style={{ color }} />;
            case 'Compass':
                return <Compass size={24} style={{ color }} />;
            case 'Shield':
                return <Shield size={24} style={{ color }} />;
            case 'Store':
                return <Store size={24} style={{ color }} />;
            default:
                return <Sparkles size={24} style={{ color }} />;
        }
    };

    const handleSelectPreset = (presetKey: string) => {
        router.post(route('profile.avatar.preset'), {
            preset: presetKey,
        }, {
            preserveScroll: true,
        });
    };

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setData('avatar', file);
            const reader = new FileReader();
            reader.onload = () => {
                setPreviewUrl(reader.result as string);
            };
            reader.readAsDataURL(file);
        }
    };

    const handleUploadSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!data.avatar) return;

        post(route('profile.avatar.update'), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setPreviewUrl(null);
            },
        });
    };

    return (
        <div className="glass-panel p-6 sm:p-8 rounded-2xl border border-white/10 space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-4">
                <div>
                    <h3 className="text-lg font-bold text-white flex items-center gap-2">
                        <Camera size={20} className="text-[#ffd700]" />
                        <span>Kustomisasi Avatar Karakter</span>
                    </h3>
                    <p className="text-xs text-gray-400 mt-1">
                        Pilih persona avatar resmi Gunung Mas atau unggah foto avatar kustom Anda.
                    </p>
                </div>

                {/* Tab Switcher */}
                <div className="flex items-center p-1 rounded-xl bg-black/40 border border-white/10 shrink-0">
                    <button
                        type="button"
                        onClick={() => setActiveTab('preset')}
                        className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all ${
                            activeTab === 'preset'
                                ? 'bg-[#ffd700] text-black shadow-md'
                                : 'text-gray-400 hover:text-white'
                        }`}
                    >
                        Galeri Persona
                    </button>
                    <button
                        type="button"
                        onClick={() => setActiveTab('upload')}
                        className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all ${
                            activeTab === 'upload'
                                ? 'bg-[#ffd700] text-black shadow-md'
                                : 'text-gray-400 hover:text-white'
                        }`}
                    >
                        Unggah Foto
                    </button>
                </div>
            </div>

            {/* Tab 1: Galeri Preset */}
            {activeTab === 'preset' && (
                <div className="space-y-4">
                    <div className="grid sm:grid-cols-2 gap-3.5">
                        {presets.map((preset) => {
                            const isSelected = currentAvatar === preset.key;

                            return (
                                <div
                                    key={preset.key}
                                    onClick={() => handleSelectPreset(preset.key)}
                                    className={`p-4 rounded-xl border transition-all cursor-pointer flex items-start gap-3.5 ${
                                        isSelected
                                            ? 'border-[#ffd700] bg-yellow-500/10 shadow-lg bloom-gold'
                                            : 'border-white/10 bg-black/30 hover:border-white/25 hover:bg-black/50'
                                    }`}
                                >
                                    <div
                                        className="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 border"
                                        style={{
                                            backgroundColor: `${preset.badge_color}15`,
                                            borderColor: `${preset.badge_color}40`,
                                        }}
                                    >
                                        {getPresetIcon(preset.icon, preset.badge_color)}
                                    </div>
                                    <div className="flex-1 space-y-1">
                                        <div className="flex items-center justify-between">
                                            <span className="font-bold text-sm text-white">{preset.label}</span>
                                            {isSelected && (
                                                <span className="p-0.5 rounded-full bg-[#ffd700] text-black">
                                                    <Check size={12} />
                                                </span>
                                            )}
                                        </div>
                                        <div className="text-[11px] font-mono uppercase tracking-wider" style={{ color: preset.badge_color }}>
                                            Faksi {preset.faction}
                                        </div>
                                        <p className="text-xs text-gray-400 leading-tight">
                                            {preset.description}
                                        </p>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            )}

            {/* Tab 2: Upload Kustom */}
            {activeTab === 'upload' && (
                <form onSubmit={handleUploadSubmit} className="space-y-4">
                    <div className="flex flex-col sm:flex-row items-center gap-6 p-5 rounded-xl bg-black/40 border border-white/10">
                        {/* Preview */}
                        <div className="relative w-24 h-24 rounded-2xl overflow-hidden border-2 border-dashed border-white/20 bg-black/60 flex items-center justify-center shrink-0">
                            {previewUrl ? (
                                <img src={previewUrl} alt="Preview" className="w-full h-full object-cover" />
                            ) : currentAvatar && !currentAvatar.startsWith('preset:') ? (
                                <img src={currentAvatar} alt="Current Avatar" className="w-full h-full object-cover" />
                            ) : (
                                <Camera size={32} className="text-gray-500" />
                            )}
                        </div>

                        {/* File Upload Trigger */}
                        <div className="space-y-2 text-center sm:text-left flex-1">
                            <input
                                type="file"
                                ref={fileInputRef}
                                onChange={handleFileChange}
                                accept="image/jpeg,image/png,image/webp"
                                className="hidden"
                            />
                            <button
                                type="button"
                                onClick={() => fileInputRef.current?.click()}
                                className="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-white text-xs font-semibold border border-white/10 inline-flex items-center gap-2 transition-all"
                            >
                                <Upload size={14} />
                                <span>Pilih Berkas Gambar (JPG, PNG, WebP)</span>
                            </button>
                            <p className="text-[11px] text-gray-400">
                                Ukuran maksimal 2 MB. Gambar akan disesuaikan otomatis menjadi rasio persegi 1:1.
                            </p>
                            {errors.avatar && (
                                <p className="text-xs text-red-400">{errors.avatar}</p>
                            )}
                        </div>
                    </div>

                    {data.avatar && (
                        <div className="flex justify-end gap-2">
                            <button
                                type="button"
                                onClick={() => {
                                    reset();
                                    setPreviewUrl(null);
                                }}
                                className="px-4 py-2 rounded-xl text-xs font-medium text-gray-400 hover:text-white"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                disabled={processing}
                                className="px-5 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-[#ffd700] to-[#e6be00] text-black hover:opacity-90 transition-all shadow-md bloom-gold"
                            >
                                {processing ? 'Mengunggah...' : 'Simpan Foto Avatar'}
                            </button>
                        </div>
                    )}
                </form>
            )}
        </div>
    );
}
