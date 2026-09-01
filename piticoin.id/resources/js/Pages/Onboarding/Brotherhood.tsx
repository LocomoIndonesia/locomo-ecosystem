import { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';

interface BrotherhoodProps {
    user: {
        id: string;
        name: string;
    };
    errors?: {
        upline_whatsapp?: string;
    };
}

export default function Brotherhood({ user, errors }: BrotherhoodProps) {
    const [whatsapp, setWhatsapp] = useState('');
    const [loading, setLoading] = useState(false);
    const [isScanning, setIsScanning] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!whatsapp) {
            handleSkip();
            return;
        }
        
        setLoading(true);
        setIsScanning(true);

        setTimeout(() => {
            setIsScanning(false);
            router.post('/onboarding/brotherhood', { upline_whatsapp: whatsapp }, {
                onFinish: () => setLoading(false)
            });
        }, 2000);
    };

    const handleSkip = () => {
        setLoading(true);
        router.post('/onboarding/brotherhood', { upline_whatsapp: null }, {
            onFinish: () => setLoading(false)
        });
    };

    return (
        <div className="bg-background text-on-background font-body-md text-body-md min-h-screen overflow-hidden relative flex flex-col">
            <Head title="The Brotherhood - Scanner" />

            {/* Environmental Effects */}
            <div className="absolute inset-0 z-0 pointer-events-none opacity-20" style={{
                backgroundSize: '50px 50px',
                backgroundImage: 'linear-gradient(to right, rgba(255, 255, 255, 0.05) 1px, transparent 1px), linear-gradient(to bottom, rgba(255, 255, 255, 0.05) 1px, transparent 1px)'
            }}></div>
            
            {/* Ambient Glow */}
            <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[80vw] h-[80vw] max-w-[600px] max-h-[600px] z-0 pointer-events-none opacity-40" style={{
                background: 'radial-gradient(circle, rgba(80, 200, 120, 0.15) 0%, transparent 70%)'
            }}></div>

            {/* TopAppBar */}
            <header className="relative z-50 flex justify-between items-center w-full px-margin-mobile pt-stack-md h-16 bg-transparent">
                <div className="text-primary-container flex items-center justify-center p-2 rounded-full opacity-0 pointer-events-none">
                    <span className="material-symbols-outlined text-2xl" style={{ fontVariationSettings: "'FILL' 0" }}>menu</span>
                </div>
                <div className="font-headline-lg-mobile text-[24px] md:text-headline-lg-mobile font-bold text-primary-container tracking-widest text-center" style={{ fontFamily: "'Playfair Display', serif" }}>
                    PITICOIN.ID
                </div>
                <div className="text-primary-container flex items-center justify-center">
                    <span className="material-symbols-outlined text-3xl opacity-50" style={{ fontVariationSettings: "'FILL' 0" }}>account_circle</span>
                </div>
            </header>

            {/* Main Content Area: Center Focused Scanner */}
            <main className="flex-1 relative z-10 flex flex-col items-center justify-center px-margin-mobile md:px-margin-desktop w-full max-w-container-max mx-auto py-10">
                
                {/* Scanner Interface Wrapper */}
                <div className="relative w-full max-w-md group">
                    
                    {/* Target Reticle Decoration (Corners) */}
                    <div className="absolute -inset-4 border-2 border-transparent border-t-faction-nisnas/40 border-l-faction-nisnas/40 rounded-tl-xl w-8 h-8 pointer-events-none transition-colors duration-500"></div>
                    <div className="absolute -inset-4 border-2 border-transparent border-t-faction-nisnas/40 border-r-faction-nisnas/40 rounded-tr-xl w-8 h-8 right-0 left-auto pointer-events-none transition-colors duration-500"></div>
                    <div className="absolute -inset-4 border-2 border-transparent border-b-faction-nisnas/40 border-l-faction-nisnas/40 rounded-bl-xl w-8 h-8 bottom-0 top-auto pointer-events-none transition-colors duration-500"></div>
                    <div className="absolute -inset-4 border-2 border-transparent border-b-faction-nisnas/40 border-r-faction-nisnas/40 rounded-br-xl w-8 h-8 bottom-0 top-auto right-0 left-auto pointer-events-none transition-colors duration-500"></div>
                    
                    {/* Glass Panel */}
                    <div className="bg-obsidian-surface/80 backdrop-blur-2xl border border-faction-nisnas/30 rounded-xl p-8 shadow-[0_8px_32px_rgba(0,0,0,0.5)] flex flex-col items-center text-center relative overflow-hidden">
                        
                        {/* Internal Scan Line Effect */}
                        {isScanning && (
                            <div className="absolute inset-0 bg-gradient-to-b from-transparent via-faction-nisnas/20 to-transparent h-[100%] w-full animate-[scan_1.5s_linear_infinite] pointer-events-none z-20"></div>
                        )}

                        {/* Header */}
                        <div className="mb-8 relative z-10">
                            <span className={`material-symbols-outlined text-5xl mb-2 block ${isScanning ? 'text-faction-nisnas animate-pulse' : 'text-faction-nisnas'}`} style={{ fontVariationSettings: "'FILL' 0" }}>radar</span>
                            <h1 className="font-headline-md text-[24px] text-on-background" style={{ fontFamily: "'Playfair Display', serif" }}>The Brotherhood</h1>
                            <p className="font-body-md text-sm text-on-surface-variant mt-2">Identifikasi Jaringan Karavan</p>
                        </div>

                        {/* Form */}
                        <form onSubmit={handleSubmit} className="w-full relative z-10 flex flex-col items-center">
                            
                            <div className="w-full relative mb-4">
                                <input 
                                    type="text"
                                    value={whatsapp}
                                    onChange={(e) => setWhatsapp(e.target.value)}
                                    placeholder="No. WhatsApp Upline"
                                    className="w-full bg-surface-dim border-2 border-faction-nisnas text-faction-nisnas font-headline-md text-[20px] text-center py-4 rounded-lg focus:outline-none focus:border-faction-nisnas focus:shadow-[0_0_15px_rgba(80,200,120,0.3),inset_0_0_10px_rgba(80,200,120,0.1)] uppercase tracking-widest placeholder:text-on-surface-variant/30 placeholder:text-[14px]"
                                    disabled={loading}
                                    style={{ fontFamily: "'Playfair Display', serif" }}
                                />
                                {whatsapp && (
                                    <div className="absolute right-4 top-1/2 -translate-y-1/2 flex items-center justify-center bg-faction-nisnas/20 rounded-full p-1 pointer-events-none">
                                        <span className="material-symbols-outlined text-faction-nisnas" style={{ fontVariationSettings: "'FILL' 1" }}>check_circle</span>
                                    </div>
                                )}
                            </div>

                            {/* Validation Error Message */}
                            {errors?.upline_whatsapp && (
                                <div className="flex items-center gap-2 mb-6 text-error bg-error/10 px-4 py-2 rounded-full border border-error/20 w-full justify-center">
                                    <span className="material-symbols-outlined text-lg" style={{ fontVariationSettings: "'FILL' 1" }}>error</span>
                                    <span className="font-label-sm text-xs tracking-wide">{errors.upline_whatsapp}</span>
                                </div>
                            )}

                            {/* Primary Action Button */}
                            <button 
                                type="submit"
                                disabled={loading}
                                className="w-full bg-primary-container text-on-primary-container font-label-sm text-sm font-bold tracking-widest py-4 rounded-xl shadow-[0_0_15px_rgba(255,215,0,0.4)] hover:bg-primary-fixed hover:scale-[1.02] active:scale-95 transition-all duration-300 flex items-center justify-center gap-2 mt-4 disabled:opacity-50 disabled:hover:scale-100 disabled:cursor-not-allowed"
                            >
                                <span className="material-symbols-outlined" style={{ fontVariationSettings: "'FILL' 1" }}>login</span>
                                {isScanning ? 'MEMINDAI...' : (whatsapp ? 'GABUNG KARAVAN' : 'JALAN SOLO (LEWATI)')}
                            </button>
                        </form>
                    </div>
                </div>

                {/* Contextual Help Text */}
                <p className="font-body-md text-[12px] text-on-surface-variant mt-8 text-center max-w-sm opacity-70">
                    Kosongkan dan klik "Jalan Solo" jika Anda tidak memiliki Upline. Bergabung dengan karavan tidak dapat dibatalkan selama siklus saat ini.
                </p>
            </main>

            <style dangerouslySetInnerHTML={{__html: `
                @keyframes scan {
                    0% { transform: translateY(-100%); opacity: 0; }
                    20% { opacity: 1; }
                    80% { opacity: 1; }
                    100% { transform: translateY(100%); opacity: 0; }
                }
            `}} />
        </div>
    );
}
