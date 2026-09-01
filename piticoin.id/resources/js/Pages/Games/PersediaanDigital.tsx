import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';

interface Product {
    id: string;
    name: string;
    cost_gold: number;
    provider: string;
}

interface PersediaanDigitalProps {
    user: {
        id: string;
        name: string;
    };
    wallet: {
        silver_balance: number;
        gold_balance: number;
    } | null;
    catalog: Product[];
}

export default function PersediaanDigital({ user, wallet, catalog }: PersediaanDigitalProps) {
    const [phoneNumber, setPhoneNumber] = useState('');
    const [selectedProduct, setSelectedProduct] = useState<Product | null>(null);
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState<{ text: string; type: 'success' | 'error' } | null>(null);

    const showMsg = (text: string, type: 'success' | 'error' = 'success') => {
        setMessage({ text, type });
        setTimeout(() => setMessage(null), 5000);
    };

    const handleRedeem = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedProduct) {
            showMsg('Silakan pilih produk terlebih dahulu.', 'error');
            return;
        }
        if (!phoneNumber || phoneNumber.length < 9) {
            showMsg('Masukkan nomor tujuan yang valid.', 'error');
            return;
        }

        setLoading(true);

        try {
            const res = await fetch(route('digital-goods.redeem'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({
                    product_name: selectedProduct.name,
                    cost_gold: selectedProduct.cost_gold,
                    phone_number: phoneNumber
                })
            });

            const data = await res.json();
            if (data.success) {
                showMsg(data.message);
                setPhoneNumber('');
                setSelectedProduct(null);
                router.reload();
            } else {
                showMsg(data.error || 'Penebusan koin gagal.', 'error');
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
        <div className="bg-background text-on-surface min-h-screen font-body-md relative overflow-x-hidden pb-24 md:pb-0" style={{ backgroundImage: 'radial-gradient(circle at 50% 0%, rgba(255, 215, 0, 0.08) 0%, transparent 70%)' }}>
            <Head title="Persediaan Digital - PitiCoin.id" />

            {/* TopAppBar */}
            <header className="fixed top-0 w-full z-50 flex items-center px-margin-mobile md:px-margin-desktop h-20 bg-surface-container-lowest/80 backdrop-blur-xl border-b border-glass-stroke shadow-xl">
                <div className="flex-1 flex items-center gap-4">
                    <Link href="/dashboard" className="text-on-surface-variant hover:text-primary transition-colors flex items-center justify-center">
                        <span className="material-symbols-outlined" style={{ fontSize: '28px' }}>arrow_back</span>
                    </Link>
                    <img alt="Adventurer Profile Avatar" className="w-10 h-10 rounded-full border border-primary-container/30 hidden md:block" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB68hr9mzR2T3qIKexDrzQU9oJsTuVANC8N1rITt-Zf9GC8tvTxfO48xsdMVbOwjeC_cYDezBXVoBUl4R15a5LojMbLCn2emLn4LHoo6qk3YOggZ75ndkqeMgcU3Q2hesTVN39_B3CwG6mjEa3_f9Tbo4p32JDRFCiJ_WKPn4jG634_ePk0MVgXC1VTb1Y4wFVTsoNDz_JBAz4mMFZl7w-sQHnTGuglfCP4eEezRyx-Rqs78oxwHfY"/>
                </div>
                <div className="flex-1 flex justify-center">
                    <h1 className="font-display-hero text-headline-md text-primary drop-shadow-[0_0_10px_rgba(255,215,0,0.4)]" style={{ fontFamily: "'Playfair Display', serif" }}>PitiCoin.id</h1>
                </div>
                <div className="flex-1 flex justify-end">
                    <button className="text-on-surface-variant hover:text-primary transition-colors">
                        <span className="material-symbols-outlined" style={{ fontSize: '28px' }}>settings</span>
                    </button>
                </div>
            </header>

            {/* Sticky Sub-Header (Balance & Marquee) */}
            <div className="sticky top-20 z-40 w-full glass-panel border-b border-glass-stroke py-3 px-margin-mobile md:px-margin-desktop shadow-lg">
                <div className="max-w-container-max mx-auto flex flex-col md:flex-row justify-between items-center gap-2">
                    <div className="flex items-center gap-2">
                        <span className="material-symbols-outlined text-primary-container" style={{ fontVariationSettings: "'FILL' 1" }}>monetization_on</span>
                        <span className="font-label-sm text-[12px] text-on-surface-variant uppercase font-semibold">Harta Karun:</span>
                        <span className="font-headline-md text-xl text-primary font-bold">
                            {wallet ? Number(wallet.gold_balance).toLocaleString('id-ID') : 0} <span className="text-sm font-normal text-on-surface-variant">Emas</span>
                        </span>
                    </div>
                    <div className="w-full md:w-1/2 bg-surface-container-highest/30 rounded-full py-1 px-3 border border-glass-stroke overflow-hidden flex items-center">
                        <div className="whitespace-nowrap animate-marquee inline-block font-label-sm text-[12px] text-secondary font-semibold tracking-wide">
                            <span className="material-symbols-outlined text-[14px] align-middle mr-1">campaign</span>
                            Pajak Guild saat ini: 2% | Harga Token Listrik sedang diskon 5% | Selesaikan Quest Harian untuk bonus Emas!
                        </div>
                    </div>
                </div>
            </div>

            {/* Main Canvas */}
            <main className="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-10 md:py-stack-lg pt-24">
                
                {message && (
                    <div className={`mb-6 p-4 rounded-xl border z-10 flex items-center gap-2 ${message.type === 'error' ? 'bg-error-container/20 border-error/50 text-error' : 'bg-secondary-container/20 border-secondary/50 text-secondary'}`}>
                        <span className="material-symbols-outlined">{message.type === 'error' ? 'error' : 'check_circle'}</span>
                        <span className="font-label-sm font-bold tracking-wide">{message.text}</span>
                    </div>
                )}

                {/* Section Header */}
                <div className="mb-stack-lg text-center">
                    <h2 className="font-headline-lg-mobile md:font-headline-lg text-[28px] md:text-[32px] text-primary mb-2" style={{ fontFamily: "'Playfair Display', serif" }}>Persediaan Digital</h2>
                    <p className="font-body-md text-base text-on-surface-variant max-w-2xl mx-auto">Tukarkan Emas Anda dengan utilitas dunia nyata. Pilih nominal dan lebur koin untuk menyelesaikan transaksi.</p>
                </div>

                {/* Tab Navigation */}
                <div className="flex gap-3 mb-stack-lg overflow-x-auto pb-2" style={{ scrollbarWidth: 'none' }}>
                    <button className="px-6 py-3 rounded-full border border-primary-container bg-primary-container/10 text-primary font-label-sm text-[12px] uppercase tracking-wider shadow-[0_0_20px_rgba(255,215,0,0.25)] flex items-center gap-2 shrink-0 font-bold">
                        <span className="material-symbols-outlined text-sm">offline_bolt</span>
                        Persediaan Digital
                    </button>
                    <button className="px-6 py-3 rounded-full border border-glass-stroke glass-panel text-on-surface-variant font-label-sm text-[12px] uppercase tracking-wider hover:border-primary-container/50 hover:text-primary transition-all flex items-center gap-2 shrink-0 font-semibold cursor-not-allowed opacity-50">
                        <span className="material-symbols-outlined text-sm">shopping_cart</span>
                        Item Game (Soon)
                    </button>
                    <button className="px-6 py-3 rounded-full border border-glass-stroke glass-panel text-on-surface-variant font-label-sm text-[12px] uppercase tracking-wider hover:border-primary-container/50 hover:text-primary transition-all flex items-center gap-2 shrink-0 font-semibold cursor-not-allowed opacity-50">
                        <span className="material-symbols-outlined text-sm">payments</span>
                        Transfer Fiat (Soon)
                    </button>
                </div>

                {/* Input Area */}
                <div className="glass-panel rounded-xl p-6 mb-stack-lg relative overflow-hidden border border-glass-stroke">
                    <div className="absolute inset-0 opacity-10 pointer-events-none" style={{ backgroundImage: 'radial-gradient(circle at 100% 100%, rgba(255, 215, 0, 0.2) 0%, transparent 50%)' }}></div>
                    <label className="block font-label-sm text-[12px] text-primary/80 mb-3 uppercase tracking-widest font-bold">Masukkan Nomor Meteran / HP Target</label>
                    <div className="relative">
                        <span className="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-primary-container/70">dialpad</span>
                        <input 
                            type="text"
                            value={phoneNumber}
                            onChange={(e) => setPhoneNumber(e.target.value)}
                            className="w-full shadow-[inset_4px_4px_8px_rgba(0,0,0,0.4),inset_-2px_-2px_4px_rgba(255,255,255,0.02)] bg-[#0c0c0c] rounded-lg py-4 pl-12 pr-4 text-primary font-body-lg text-lg focus:outline-none focus:ring-1 focus:ring-primary-container/50 border border-white/5 placeholder-on-surface-variant/40"
                            placeholder="Contoh: 081234567890"
                            disabled={loading}
                        />
                    </div>
                </div>

                {/* Grid of Options (Bento Grid) */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter mb-stack-lg">
                    {catalog.map((product) => {
                        const isSelected = selectedProduct?.id === product.id;
                        const canAfford = wallet ? wallet.gold_balance >= product.cost_gold : false;
                        
                        return (
                            <div 
                                key={product.id}
                                onClick={() => { if(canAfford) setSelectedProduct(product); }}
                                className={`glass-panel rounded-xl p-5 border relative overflow-hidden transition-all group ${
                                    isSelected
                                    ? 'border-primary-container bg-primary-container/5 shadow-[0_0_20px_rgba(255,215,0,0.25)]'
                                    : canAfford 
                                        ? 'border-glass-stroke cursor-pointer hover:border-primary-container/40 hover:bg-white/[0.02]' 
                                        : 'border-glass-stroke opacity-50 cursor-not-allowed grayscale'
                                }`}
                            >
                                {isSelected && (
                                    <div className="absolute -right-6 -top-6 w-24 h-24 bg-primary-container/10 rounded-full blur-2xl"></div>
                                )}
                                
                                {!canAfford && (
                                    <div className="absolute inset-0 bg-black/40 z-20 flex items-center justify-center backdrop-blur-[1px]">
                                        <span className="material-symbols-outlined text-on-surface-variant text-4xl opacity-60" style={{ fontVariationSettings: "'FILL' 1" }}>lock</span>
                                    </div>
                                )}
                                
                                <div className="flex items-start justify-between mb-4 relative z-10">
                                    <div className={`p-2 rounded-lg border transition-all ${
                                        isSelected 
                                        ? 'bg-primary-container border-primary-container shadow-[0_0_10px_rgba(255,215,0,0.3)] text-obsidian-surface' 
                                        : 'bg-surface-container-high border-glass-stroke group-hover:border-primary-container/30 text-primary-container'
                                    }`}>
                                        <span className="material-symbols-outlined">{product.provider.toLowerCase().includes('pln') ? 'bolt' : 'smartphone'}</span>
                                    </div>
                                    <span className={`font-label-sm text-[10px] px-2 py-1 rounded-md border flex items-center gap-1 font-bold ${
                                        isSelected 
                                        ? 'text-primary bg-primary-container/20 border-primary-container/30' 
                                        : canAfford 
                                            ? 'text-secondary bg-secondary/10 border-secondary/20' 
                                            : 'text-on-surface-variant bg-surface-container-high border-transparent'
                                    }`}>
                                        {isSelected && <span className="material-symbols-outlined text-[14px]">check_circle</span>}
                                        {isSelected ? 'Dipilih' : canAfford ? 'Tersedia' : 'Emas Kurang'}
                                    </span>
                                </div>
                                <h3 className="font-headline-md text-xl text-on-surface mb-1" style={{ fontFamily: "'Playfair Display', serif" }}>{product.name}</h3>
                                <div className="flex items-center gap-2 mt-3">
                                    <span className="font-label-sm text-[10px] text-on-surface-variant uppercase font-semibold">Harga:</span>
                                    <span className={`font-body-lg text-lg font-bold ${canAfford || isSelected ? 'text-primary' : 'text-error'}`}>{product.cost_gold} Emas</span>
                                </div>
                            </div>
                        );
                    })}
                </div>

                {/* Action Area */}
                <div className="flex flex-col md:flex-row justify-between items-center gap-6 mt-stack-lg pt-stack-md border-t border-glass-stroke">
                    <div className="text-center md:text-left">
                        <p className="font-label-sm text-[12px] text-on-surface-variant uppercase mb-1 tracking-widest font-semibold">Total Biaya Transaksi</p>
                        <p className="font-headline-lg text-[32px] text-primary drop-shadow-[0_0_8px_rgba(255,215,0,0.3)]" style={{ fontFamily: "'Playfair Display', serif" }}>
                            {selectedProduct ? selectedProduct.cost_gold : 0} <span className="text-lg font-normal text-on-surface-variant">Emas</span>
                        </p>
                    </div>
                    <button 
                        onClick={handleRedeem}
                        disabled={loading || !selectedProduct || !phoneNumber || (wallet ? wallet.gold_balance < (selectedProduct?.cost_gold || 0) : true)}
                        className="w-full md:w-auto px-10 py-5 bg-primary-container text-obsidian-surface font-body-lg text-lg font-bold rounded-lg shadow-[0_0_20px_rgba(255,215,0,0.25)] hover:bg-primary-fixed active:scale-[0.98] transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:active:scale-100 disabled:cursor-not-allowed disabled:shadow-none"
                    >
                        <span className="material-symbols-outlined font-bold" style={{ fontVariationSettings: "'FILL' 1" }}>local_fire_department</span>
                        {loading ? 'MEMPROSES...' : 'Bayar & Lebur Koin (Burn)'}
                    </button>
                </div>
            </main>

            {/* BottomNavBar (Mobile Only) */}
            <nav className="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-4 pb-6 pt-3 bg-surface-container-lowest/90 backdrop-blur-2xl border-t border-glass-stroke shadow-[0_-5px_20px_rgba(0,0,0,0.6)] rounded-t-2xl md:hidden">
                <Link href="/dashboard" className="flex flex-col items-center justify-center text-on-surface-variant opacity-60 hover:opacity-100 transition-all group">
                    <span className="material-symbols-outlined mb-1 group-hover:text-primary transition-colors">account_balance_wallet</span>
                    <span className="font-label-sm text-[10px] uppercase tracking-tighter font-semibold">Vault</span>
                </Link>
                {/* Active Tab */}
                <div className="flex flex-col items-center justify-center text-primary group">
                    <div className="relative flex flex-col items-center">
                        <span className="material-symbols-outlined mb-1 drop-shadow-[0_0_8px_rgba(255,215,0,0.5)]" style={{ fontVariationSettings: "'FILL' 1" }}>storefront</span>
                        <span className="font-label-sm text-[10px] uppercase tracking-tighter font-bold">Market</span>
                        <div className="absolute -bottom-2 w-1 h-1 bg-primary rounded-full shadow-[0_0_5px_rgba(255,215,0,0.8)]"></div>
                    </div>
                </div>
                <Link href="#" className="flex flex-col items-center justify-center text-on-surface-variant opacity-60 hover:opacity-100 transition-all group">
                    <span className="material-symbols-outlined mb-1 group-hover:text-primary transition-colors">military_tech</span>
                    <span className="font-label-sm text-[10px] uppercase tracking-tighter font-semibold">Quests</span>
                </Link>
                <Link href="#" className="flex flex-col items-center justify-center text-on-surface-variant opacity-60 hover:opacity-100 transition-all group">
                    <span className="material-symbols-outlined mb-1 group-hover:text-primary transition-colors">groups</span>
                    <span className="font-label-sm text-[10px] uppercase tracking-tighter font-semibold">Guild</span>
                </Link>
                <Link href="#" className="flex flex-col items-center justify-center text-on-surface-variant opacity-60 hover:opacity-100 transition-all group">
                    <span className="material-symbols-outlined mb-1 group-hover:text-primary transition-colors">person</span>
                    <span className="font-label-sm text-[10px] uppercase tracking-tighter font-semibold">Profile</span>
                </Link>
            </nav>

            <style dangerouslySetInnerHTML={{__html: `
                .glass-panel {
                    background-color: rgba(32, 31, 31, 0.7);
                    backdrop-filter: blur(16px);
                    -webkit-backdrop-filter: blur(16px);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    background-image: linear-gradient(180deg, rgba(255,255,255,0.03) 0%, transparent 100%);
                }
                @keyframes marquee {
                    0% { transform: translateX(100vw); }
                    100% { transform: translateX(-100%); }
                }
                .animate-marquee {
                    animation: marquee 20s linear infinite;
                }
            `}} />
        </div>
    );
}
