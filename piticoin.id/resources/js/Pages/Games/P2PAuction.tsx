import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';

interface AuctionItem {
    id: string;
    name: string;
    seller_name: string;
    seller_id: string;
    current_bid: number;
    buyout_price: number;
    icon: string;
    description: string;
}

interface P2PAuctionProps {
    user: {
        id: string;
        name: string;
    };
    wallet: {
        silver_balance: number;
        gold_balance: number;
    } | null;
    auction_items: AuctionItem[];
}

export default function P2PAuction({ user, wallet, auction_items }: P2PAuctionProps) {
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState<{ text: string; type: 'success' | 'error' } | null>(null);

    const showMsg = (text: string, type: 'success' | 'error' = 'success') => {
        setMessage({ text, type });
        setTimeout(() => setMessage(null), 5000);
    };

    const handleBuyout = async (item: AuctionItem) => {
        if (loading) return;
        setLoading(true);

        try {
            const res = await fetch(route('auction.buyout'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({
                    item_name: item.name,
                    price: item.buyout_price,
                    seller_id: item.seller_id
                })
            });

            const data = await res.json();
            if (data.success) {
                showMsg(data.message);
                router.reload();
            } else {
                showMsg(data.error || 'Pembelian lelang gagal.', 'error');
            }
        } catch (err) {
            showMsg('Terjadi kesalahan koneksi server.', 'error');
        } finally {
            setLoading(false);
        }
    };

    const getCsrfToken = () => {
        const name = 'XSRF-TOKEN=';
        const decodedCookie = decodeURIComponent(document.cookie);
        const ca = decodedCookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return '';
    };

    return (
        <div className="bg-surface text-on-surface bg-mystic-gradient min-h-screen pb-24 md:pb-0 overflow-x-hidden font-body-md relative">
            <Head title="PitiCoin.id - Bursa Lelang" />

            {/* Dust Particles Overlay */}
            <div className="fixed inset-0 pointer-events-none z-0 opacity-30" style={{
                backgroundImage: 'radial-gradient(rgba(255, 215, 0, 0.1) 1px, transparent 1px)',
                backgroundSize: '20px 20px'
            }}></div>

            {/* TopAppBar */}
            <header className="fixed top-0 w-full z-50 flex items-center px-margin-mobile md:px-margin-desktop h-20 bg-surface/60 backdrop-blur-xl border-b border-glass-stroke shadow-[0_4px_15px_rgba(0,0,0,0.5)]">
                <div className="flex items-center justify-between w-full max-w-container-max mx-auto">
                    <div className="flex items-center gap-4">
                        <Link href="/dashboard" className="text-on-surface-variant hover:text-primary transition-colors flex items-center justify-center mr-2">
                            <span className="material-symbols-outlined" style={{ fontSize: '28px' }}>arrow_back</span>
                        </Link>
                        <div className="w-10 h-10 rounded-full overflow-hidden border-2 border-primary-container shadow-[0_0_10px_rgba(255,215,0,0.4)] hidden md:block">
                            <img className="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCyyqL8KV1lpPYFrcKOcyzFAyVcqcTdNAOsjVMGa8yBry6FVuBGZMZxdcL091o-beZphAZiRCxOymyLPdES8nJ25cVnO2j3LDADTjulYOymSgqpToc003CDHRA1iELfyt51WKSt-SimZRp6n5GvpyXqPzBukdAKigKNm3k9WfO8Q7jGsU3Og1hpzOwjZvFGzf6wP4hIzeXVwJtFTRdrdbbJwWE9J46mM-HqX3nkF3TyhvmagkRZOvw" alt="User Avatar" />
                        </div>
                        <h1 className="font-display-hero text-headline-md text-primary drop-shadow-[0_0_10px_rgba(255,215,0,0.4)]" style={{ fontFamily: "'Playfair Display', serif" }}>PitiCoin.id</h1>
                    </div>
                    <button className="text-on-surface-variant hover:text-primary transition-colors hover:scale-105 active:scale-95">
                        <span className="material-symbols-outlined" style={{ fontSize: '28px' }}>settings</span>
                    </button>
                </div>
            </header>

            {/* Main Content Canvas */}
            <main className="pt-24 px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto relative z-10 flex flex-col md:flex-row gap-gutter">
                
                {/* Desktop SideNav (Hidden on Mobile) */}
                <nav className="hidden md:flex flex-col w-64 shrink-0 gap-2 sticky top-28 self-start glass-panel rounded-xl p-4">
                    <h2 className="font-label-sm text-[12px] text-on-surface-variant uppercase mb-4 px-2 tracking-widest font-semibold">Navigation</h2>
                    <Link href="/dashboard" className="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:text-primary hover:bg-white/5 transition-colors group">
                        <span className="material-symbols-outlined group-hover:scale-110 transition-transform">account_balance_wallet</span>
                        <span className="font-body-md text-base">Vault</span>
                    </Link>
                    {/* Active Tab: Market / Bursa Lelang */}
                    <div className="flex items-center gap-3 px-4 py-3 rounded-lg bg-primary-container/20 text-primary border-l-4 border-primary filter drop-shadow-[0_0_8px_rgba(255,215,0,0.6)] group">
                        <span className="material-symbols-outlined" style={{ fontVariationSettings: "'FILL' 1" }}>storefront</span>
                        <span className="font-body-md text-base font-bold">Market</span>
                    </div>
                    <Link href="#" className="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:text-primary hover:bg-white/5 transition-colors group">
                        <span className="material-symbols-outlined group-hover:scale-110 transition-transform">military_tech</span>
                        <span className="font-body-md text-base">Quests</span>
                    </Link>
                    <Link href="#" className="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:text-primary hover:bg-white/5 transition-colors group">
                        <span className="material-symbols-outlined group-hover:scale-110 transition-transform">groups</span>
                        <span className="font-body-md text-base">Guild</span>
                    </Link>
                    <Link href="#" className="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:text-primary hover:bg-white/5 transition-colors group">
                        <span className="material-symbols-outlined group-hover:scale-110 transition-transform">person</span>
                        <span className="font-body-md text-base">Profile</span>
                    </Link>
                </nav>

                {/* Center Stage */}
                <div className="flex-1 flex flex-col gap-stack-md w-full min-w-0">
                    
                    {/* Sticky Sub-Header */}
                    <div className="sticky top-20 z-40 pt-4 pb-2 bg-surface/80 backdrop-blur-md -mx-margin-mobile px-margin-mobile md:mx-0 md:px-0">
                        <div className="glass-panel rounded-xl p-4 flex flex-col md:flex-row justify-between items-center gap-4 relative overflow-hidden">
                            {/* Mystic glow accent */}
                            <div className="absolute -right-10 -top-10 w-32 h-32 bg-[#4B0082] rounded-full blur-[50px] opacity-30"></div>
                            
                            <div className="flex items-center gap-4 z-10 w-full md:w-auto">
                                <div className="neumorphic-inset bg-surface-container p-3 rounded-lg flex items-center gap-2 border border-glass-stroke shadow-[inset_2px_2px_5px_rgba(0,0,0,0.5),inset_-2px_-2px_5px_rgba(255,255,255,0.05)]">
                                    <span className="material-symbols-outlined text-primary-container" style={{ fontVariationSettings: "'FILL' 1" }}>monetization_on</span>
                                    <div className="flex flex-col">
                                        <span className="font-label-sm text-[12px] text-on-surface-variant font-semibold">Saldo Dompet</span>
                                        <span className="font-headline-md text-xl text-primary font-bold tracking-tight">
                                            {wallet ? Number(wallet.silver_balance).toLocaleString('id-ID') : 0} Perak
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            {/* Marquee Banner */}
                            <div className="flex-1 w-full overflow-hidden border border-faction-android/30 bg-faction-android/10 rounded-lg p-2 flex items-center z-10 whitespace-nowrap">
                                <span className="material-symbols-outlined text-faction-android mr-2 text-sm">warning</span>
                                <div className="relative w-full overflow-hidden h-6">
                                    <p className="absolute font-label-sm text-[12px] text-error animate-marquee w-full font-bold">
                                        PAJAK LELANG SAAT INI: 5% — TRANSAKSI DI ATAS 100 PERAK DIKENAKAN BIAYA TAMBAHAN. PERDAGANGAN BERISIKO TINGGI.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {message && (
                        <div className={`p-4 rounded-xl border z-10 flex items-center gap-2 ${message.type === 'error' ? 'bg-error-container/20 border-error/50 text-error' : 'bg-secondary-container/20 border-secondary/50 text-secondary'}`}>
                            <span className="material-symbols-outlined">{message.type === 'error' ? 'error' : 'check_circle'}</span>
                            <span className="font-label-sm font-bold tracking-wide">{message.text}</span>
                        </div>
                    )}

                    {/* Page Title */}
                    <div className="flex items-center justify-between mt-stack-sm mb-stack-sm z-10">
                        <div>
                            <h2 className="font-headline-lg-mobile md:font-headline-lg text-[28px] md:text-[32px] text-primary mb-1" style={{ fontFamily: "'Playfair Display', serif" }}>Bursa Lelang</h2>
                            <p className="font-body-md text-base text-on-surface-variant">Rebut harta karun sebelum waktu habis.</p>
                        </div>
                        <div className="flex gap-2">
                            <button className="p-2 rounded-lg border border-glass-stroke text-on-surface-variant hover:text-primary hover:border-primary transition-all glass-panel">
                                <span className="material-symbols-outlined">filter_list</span>
                            </button>
                            <button className="p-2 rounded-lg border border-glass-stroke text-on-surface-variant hover:text-primary hover:border-primary transition-all glass-panel">
                                <span className="material-symbols-outlined">sort</span>
                            </button>
                        </div>
                    </div>

                    {/* Auction List View */}
                    <div className="flex flex-col gap-stack-md z-10">
                        {auction_items.length === 0 && (
                            <div className="text-center p-8 glass-panel rounded-xl text-on-surface-variant font-label-sm">
                                Tidak ada barang di Bursa Lelang saat ini.
                            </div>
                        )}
                        {auction_items.map((item) => (
                            <article key={item.id} className="glass-panel rounded-xl overflow-hidden group hover:border-primary-container/50 transition-colors duration-300 relative">
                                {/* Glow effect on hover */}
                                <div className="absolute inset-0 bg-primary-container/0 group-hover:bg-primary-container/5 transition-colors duration-300 pointer-events-none"></div>
                                
                                <div className="flex flex-col md:flex-row">
                                    {/* Thumbnail */}
                                    <div className="w-full md:w-48 h-48 md:h-auto shrink-0 relative bg-obsidian-surface border-b md:border-b-0 md:border-r border-glass-stroke flex items-center justify-center overflow-hidden">
                                        <div className="text-[100px] drop-shadow-[0_0_15px_rgba(255,215,0,0.5)] group-hover:scale-110 transition-transform duration-500">{item.icon}</div>
                                        <div className="absolute top-2 left-2 bg-faction-android text-on-error px-2 py-1 rounded font-label-sm text-[10px] uppercase font-bold tracking-wider shadow-lg backdrop-blur-md bg-opacity-80">Active</div>
                                    </div>
                                    
                                    {/* Details */}
                                    <div className="p-4 md:p-6 flex-1 flex flex-col justify-between z-10">
                                        <div>
                                            <div className="flex justify-between items-start mb-2">
                                                <h3 className="font-headline-md text-2xl text-primary group-hover:text-primary-container transition-colors" style={{ fontFamily: "'Playfair Display', serif" }}>{item.name}</h3>
                                                <div className="flex items-center gap-1 bg-surface-container-highest px-3 py-1 rounded-full border border-error-container">
                                                    <span className="material-symbols-outlined text-[16px] text-error animate-pulse">timer</span>
                                                    <span className="font-label-sm text-[12px] text-error animate-pulse font-mono font-bold">LIVE</span>
                                                </div>
                                            </div>
                                            <p className="font-label-sm text-[12px] text-on-surface-variant mb-4 flex items-center gap-2">
                                                <span className="material-symbols-outlined text-[14px]">person</span>
                                                Dijual oleh: <span className="text-secondary font-bold">{item.seller_name}</span>
                                            </p>
                                            
                                            {/* Price Info */}
                                            <div className="bg-surface-container-lowest/50 p-3 rounded-lg border border-glass-stroke mb-4 flex items-center justify-between">
                                                <div>
                                                    <p className="font-label-sm text-[10px] text-on-surface-variant mb-1 uppercase tracking-wider font-semibold">Harga Instan (Buyout)</p>
                                                    <p className="font-headline-md text-2xl text-primary-container font-bold" style={{ fontFamily: "'Playfair Display', serif" }}>{Number(item.buyout_price).toLocaleString('id-ID')} Perak</p>
                                                </div>
                                                <div className="text-right max-w-[50%]">
                                                    <p className="font-label-sm text-[10px] text-on-surface-variant mb-1 uppercase tracking-wider font-semibold">Deskripsi</p>
                                                    <p className="font-body-md text-[12px] text-primary-fixed-dim line-clamp-2">{item.description}</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {/* Actions */}
                                        <div className="flex flex-col sm:flex-row gap-3 mt-auto">
                                            <button 
                                                className="flex-1 py-3 px-4 rounded-lg bg-surface-variant text-on-surface font-label-sm text-[12px] font-semibold hover:bg-surface-container-highest transition-colors border border-outline-variant hover:border-outline flex items-center justify-center gap-2 group/btn cursor-not-allowed opacity-50"
                                                disabled
                                            >
                                                <span className="material-symbols-outlined text-[18px]">gavel</span>
                                                BID (SOON)
                                            </button>
                                            <button 
                                                onClick={() => handleBuyout(item)}
                                                disabled={loading || (wallet ? wallet.silver_balance < item.buyout_price : true)}
                                                className="flex-1 py-3 px-4 rounded-lg bg-primary-container text-on-primary-container font-label-sm text-[12px] font-bold uppercase tracking-wide hover:bg-primary transition-colors border border-primary-fixed shadow-[0_0_15px_rgba(255,215,0,0.3)] hover:shadow-[0_0_25px_rgba(255,215,0,0.5)] flex items-center justify-center gap-2 group/btn disabled:opacity-50 disabled:hover:bg-primary-container disabled:shadow-none disabled:cursor-not-allowed"
                                            >
                                                <span className="material-symbols-outlined text-[18px] group-hover/btn:scale-110 transition-transform">shopping_cart</span>
                                                {loading ? 'MEMPROSES...' : `BELI INSTAN`}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        ))}
                    </div>
                </div>
            </main>

            {/* BottomNavBar (Mobile Only) */}
            <nav className="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-4 pb-6 pt-3 bg-surface-container-lowest/80 backdrop-blur-2xl border-t border-glass-stroke shadow-[0_-5px_20px_rgba(0,0,0,0.6)] md:hidden rounded-t-xl">
                <Link href="/dashboard" className="flex flex-col items-center justify-center text-on-surface-variant opacity-70 hover:opacity-100 transition-opacity active:scale-90 duration-150 group">
                    <span className="material-symbols-outlined text-[24px] mb-1 group-hover:text-primary transition-colors">account_balance_wallet</span>
                    <span className="font-label-sm text-[10px] group-hover:text-primary transition-colors font-semibold">Vault</span>
                </Link>
                {/* Active Tab */}
                <div className="flex flex-col items-center justify-center text-primary filter drop-shadow-[0_0_8px_rgba(255,215,0,0.6)] active:scale-90 transition-all duration-150">
                    <span className="material-symbols-outlined text-[24px] mb-1" style={{ fontVariationSettings: "'FILL' 1" }}>storefront</span>
                    <span className="font-label-sm text-[10px] font-bold">Market</span>
                </div>
                <Link href="#" className="flex flex-col items-center justify-center text-on-surface-variant opacity-70 hover:opacity-100 transition-opacity active:scale-90 duration-150 group">
                    <span className="material-symbols-outlined text-[24px] mb-1 group-hover:text-primary transition-colors">military_tech</span>
                    <span className="font-label-sm text-[10px] group-hover:text-primary transition-colors font-semibold">Quests</span>
                </Link>
                <Link href="#" className="flex flex-col items-center justify-center text-on-surface-variant opacity-70 hover:opacity-100 transition-opacity active:scale-90 duration-150 group">
                    <span className="material-symbols-outlined text-[24px] mb-1 group-hover:text-primary transition-colors">groups</span>
                    <span className="font-label-sm text-[10px] group-hover:text-primary transition-colors font-semibold">Guild</span>
                </Link>
                <Link href="#" className="flex flex-col items-center justify-center text-on-surface-variant opacity-70 hover:opacity-100 transition-opacity active:scale-90 duration-150 group">
                    <span className="material-symbols-outlined text-[24px] mb-1 group-hover:text-primary transition-colors">person</span>
                    <span className="font-label-sm text-[10px] group-hover:text-primary transition-colors font-semibold">Profile</span>
                </Link>
            </nav>

            <style dangerouslySetInnerHTML={{__html: `
                .glass-panel {
                    background: rgba(26, 26, 26, 0.6);
                    backdrop-filter: blur(20px);
                    -webkit-backdrop-filter: blur(20px);
                    border-top: 1px solid rgba(255,255,255,0.2);
                    border-bottom: 1px solid transparent;
                    border-left: 1px solid rgba(255,255,255,0.1);
                    border-right: 1px solid rgba(255,255,255,0.1);
                }
                .bg-mystic-gradient {
                    background: radial-gradient(circle at top right, #4B0082 0%, #121212 50%);
                }
                @keyframes marquee {
                    0% { transform: translateX(100%); }
                    100% { transform: translateX(-100%); }
                }
                .animate-marquee {
                    animation: marquee 15s linear infinite;
                }
            `}} />
        </div>
    );
}
