import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

export default function Guest({ children }: PropsWithChildren) {
    return (
        <div className="min-h-screen bg-background text-on-surface flex flex-col items-center justify-center relative overflow-hidden px-margin-mobile py-10 md:px-margin-desktop font-body-md">
            
            {/* Ambient Background Glow */}
            <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[80vw] h-[80vw] max-w-[600px] max-h-[600px] z-0 pointer-events-none opacity-30" style={{
                background: 'radial-gradient(circle, rgba(255, 215, 0, 0.1) 0%, transparent 70%)'
            }}></div>
            
            {/* Dust Particles Overlay */}
            <div className="fixed inset-0 pointer-events-none z-0 opacity-20" style={{
                backgroundImage: 'radial-gradient(rgba(255, 215, 0, 0.08) 1px, transparent 1px)',
                backgroundSize: '20px 20px'
            }}></div>

            {/* Brand Header */}
            <div className="flex flex-col items-center mb-8 z-10 relative">
                <div className="w-16 h-16 rounded-2xl bg-primary-container text-obsidian-surface flex items-center justify-center shadow-[0_0_20px_rgba(255,215,0,0.3)] mb-4">
                    <span className="material-symbols-outlined text-4xl" style={{ fontVariationSettings: "'FILL' 1" }}>toll</span>
                </div>
                <h1 className="font-display-hero text-[32px] font-bold tracking-widest text-primary drop-shadow-[0_0_10px_rgba(255,215,0,0.4)]" style={{ fontFamily: "'Playfair Display', serif" }}>
                    piticoin<span className="text-white">.id</span>
                </h1>
            </div>

            {/* Content Card */}
            <div className="w-full max-w-md bg-obsidian-surface/80 backdrop-blur-2xl border border-glass-stroke rounded-[24px] p-8 shadow-[0_8px_32px_rgba(0,0,0,0.6)] z-10 relative overflow-hidden">
                <div className="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
                {children}
            </div>
        </div>
    );
}
