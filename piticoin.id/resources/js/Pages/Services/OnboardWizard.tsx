import { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';

interface WizardField {
    name: string;
    label: string;
    type: 'text' | 'textarea' | 'select' | 'tel' | 'number';
    required: boolean;
    placeholder?: string;
    options?: string[];
}

interface WizardStep {
    id: string;
    title: string;
    description?: string;
    fields: WizardField[];
}

interface OnboardWizardProps {
    service: {
        id: string;
        name: string;
        code: string;
        icon: string;
        category: string;
        service_type?: 'INTERNAL' | 'EXTERNAL';
        description: string;
        wizard_schema?: {
            steps?: WizardStep[];
        };
        price_gold: number;
        price_fiat: number;
        service_url?: string;
        allows_installments?: boolean;
        min_down_payment_gold?: number;
        min_down_payment_fiat?: number;
    };
    auth_user: {
        id: string;
        name: string;
        username?: string;
        whatsapp: string;
        level: number;
        faction: string;
    };
    gold_balance: number;
}

export default function OnboardWizard({
    service,
    auth_user,
    gold_balance,
}: OnboardWizardProps) {
    const steps: WizardStep[] = service.wizard_schema?.steps || [];
    const totalCustomSteps = steps.length;
    // Total steps = 1 (Beneficiary Identifier) + totalCustomSteps + 1 (Payment & Review)
    const totalStepsCount = 1 + totalCustomSteps + 1;

    const [currentStep, setCurrentStep] = useState(1);
    const [loading, setLoading] = useState(false);
    const [errorMsg, setErrorMsg] = useState<string | null>(null);

    // Step 1: Beneficiary Identity state
    const [identifier, setIdentifier] = useState(auth_user.whatsapp || '');
    const [beneficiaryUser, setBeneficiaryUser] = useState<any>(null);
    const [isExistingUser, setIsExistingUser] = useState<boolean | null>(null);
    const [newUserName, setNewUserName] = useState('');
    const [checkingUser, setCheckingUser] = useState(false);

    // Dynamic Form Data state for step 2..N
    const [formData, setFormData] = useState<Record<string, any>>({});

    // Payment step state (STRICT TOKENOMICS: GOLD or FIAT only, no SILVER)
    const [paymentMethod, setPaymentMethod] = useState<'GOLD' | 'FIAT'>('GOLD');
    const [paymentType, setPaymentType] = useState<'FULL' | 'INSTALLMENT'>('FULL');
    const [customDownPayment, setCustomDownPayment] = useState<string>('');
    const [isSuccess, setIsSuccess] = useState(false);
    const [generatedLicense, setGeneratedLicense] = useState<any>(null);
    const [redirectUrl, setRedirectUrl] = useState<string | null>(null);

    // Package tier resolution (dynamic price adjustment if package selected)
    const resolveTierPrices = () => {
        const tier = formData.package_tier;
        if (!tier) return { gold: service.price_gold, fiat: service.price_fiat, name: 'Standard' };
        if (typeof tier === 'string') {
            if (tier.includes('Basic') || tier.includes('40')) {
                return { gold: 40, fiat: 50000, name: 'Basic Starter' };
            }
            if (tier.includes('Enterprise') || tier.includes('VIP') || tier.includes('160')) {
                return { gold: 160, fiat: 200000, name: 'Enterprise VIP' };
            }
            if (tier.includes('Bisnis') || tier.includes('80')) {
                return { gold: 80, fiat: 100000, name: 'Bisnis Pro' };
            }
        }
        return { gold: service.price_gold, fiat: service.price_fiat, name: 'Standard' };
    };

    const currentPricing = resolveTierPrices();

    // Installment DP calculations
    const minDpGold = service.min_down_payment_gold || Math.round(currentPricing.gold * 0.2);
    const minDpFiat = service.min_down_payment_fiat || Math.round(currentPricing.fiat * 0.2);
    const minDp = paymentMethod === 'GOLD' ? minDpGold : minDpFiat;
    const isInstallment = paymentType === 'INSTALLMENT' && !!service.allows_installments;
    const effectiveDp = customDownPayment !== '' ? Math.max(minDp, Number(customDownPayment) || minDp) : minDp;
    const currentCharge = isInstallment ? effectiveDp : (paymentMethod === 'GOLD' ? currentPricing.gold : currentPricing.fiat);
    const remainingBalance = isInstallment
        ? Math.max(0, (paymentMethod === 'GOLD' ? currentPricing.gold : currentPricing.fiat) - currentCharge)
        : 0;

    // Live verification lookup for identifier
    useEffect(() => {
        if (!identifier || identifier.trim().length < 4) {
            setBeneficiaryUser(null);
            setIsExistingUser(null);
            return;
        }

        const timeout = setTimeout(() => {
            setCheckingUser(true);
            fetch('/api/services/check-beneficiary', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                },
                body: JSON.stringify({ identifier: identifier.trim() }),
            })
                .then((res) => res.json())
                .then((data) => {
                    setCheckingUser(false);
                    if (data.exists) {
                        setIsExistingUser(true);
                        setBeneficiaryUser(data.user);
                    } else {
                        setIsExistingUser(false);
                        setBeneficiaryUser(null);
                    }
                })
                .catch(() => {
                    setCheckingUser(false);
                });
        }, 500);

        return () => clearTimeout(timeout);
    }, [identifier]);

    const formatRupiah = (val: number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(val);
    };

    const isSelf = beneficiaryUser?.id === auth_user.id;

    // Check if step 1 is valid
    const isStep1Valid = () => {
        if (!identifier.trim()) return false;
        if (isExistingUser === true) return true;
        if (isExistingUser === false && newUserName.trim().length >= 3) return true;
        return false;
    };

    // Check if current custom step is valid
    const isCustomStepValid = (stepIndex: number) => {
        const stepData = steps[stepIndex];
        if (!stepData) return true;
        for (const field of stepData.fields) {
            if (field.required && (!formData[field.name] || formData[field.name].toString().trim() === '')) {
                return false;
            }
        }
        return true;
    };

    const handleNext = () => {
        setErrorMsg(null);
        if (currentStep === 1 && !isStep1Valid()) {
            setErrorMsg('Mohon lengkapi identitas penerima lisensi terlebih dahulu.');
            return;
        }
        if (currentStep > 1 && currentStep <= 1 + totalCustomSteps) {
            const customIdx = currentStep - 2;
            if (!isCustomStepValid(customIdx)) {
                setErrorMsg('Mohon isi semua field wajib bertanda bintang (*).');
                return;
            }
        }
        setCurrentStep((prev) => Math.min(prev + 1, totalStepsCount));
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const handleBack = () => {
        setErrorMsg(null);
        setCurrentStep((prev) => Math.max(prev - 1, 1));
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const handleSubmitOrder = async () => {
        setLoading(true);
        setErrorMsg(null);
        try {
            const res = await fetch(`/services/${service.code}/submit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                },
                body: JSON.stringify({
                    beneficiary_identifier: identifier,
                    beneficiary_name: newUserName,
                    payment_method: paymentMethod,
                    payment_type: isInstallment ? 'INSTALLMENT' : 'FULL',
                    down_payment_amount: isInstallment ? currentCharge : undefined,
                    form_data: formData,
                }),
            });

            const data = await res.json();
            if (data.success) {
                setGeneratedLicense(data.license);
                if (data.redirect_url) {
                    setRedirectUrl(data.redirect_url);
                }
                setIsSuccess(true);
            } else {
                setErrorMsg(data.message || 'Gagal memproses pendaftaran lisensi.');
            }
        } catch (err) {
            setErrorMsg('Terjadi kendala jaringan saat memproses transaksi.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="bg-background text-on-background min-h-screen relative overflow-x-hidden font-body-md select-none pb-20">
            <Head title={`Pendaftaran ${service.name} - Hub Layanan PitiCoin.id`} />

            {/* Top Navigation Bar */}
            <header className="bg-obsidian-surface/90 backdrop-blur-xl border-b border-glass-stroke px-4 py-3 sticky top-0 z-30 flex items-center justify-between">
                <Link
                    href="/dashboard"
                    className="p-2 rounded-full hover:bg-white/5 text-on-surface-variant hover:text-primary-container transition-colors flex items-center gap-1"
                >
                    <span className="material-symbols-outlined text-xl">arrow_back</span>
                    <span className="text-xs font-semibold uppercase tracking-wider">Dashboard</span>
                </Link>
                <div className="text-center">
                    <span className="text-[10px] text-primary-container uppercase font-mono tracking-widest block">
                        HUB LAYANAN EKOSISTEM
                    </span>
                    <h2 className="text-xs md:text-sm font-bold text-on-surface truncate max-w-[200px] md:max-w-xs">
                        {service.name}
                    </h2>
                </div>
                <div className="w-10 flex justify-end">
                    <span className="material-symbols-outlined text-primary-container text-xl">{service.icon}</span>
                </div>
            </header>

            <main className="max-w-xl mx-auto px-4 py-5 space-y-5">
                {/* Service Header Card */}
                <div className="glass-panel p-4 md:p-5 rounded-2xl border border-primary-container/30 space-y-2 bg-gradient-to-br from-obsidian-surface via-surface/60 to-primary-container/10 relative overflow-hidden">
                    <div className="flex items-start justify-between gap-3">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-xl bg-primary-container/20 border border-primary-container/40 flex items-center justify-center text-primary-container shadow">
                                <span className="material-symbols-outlined text-2xl">{service.icon}</span>
                            </div>
                            <div>
                                <span className="text-[9px] px-2 py-0.5 rounded bg-primary-container/20 text-primary-container font-mono border border-primary-container/30 uppercase font-bold">
                                    {service.category}
                                </span>
                                <h1 className="text-base md:text-lg font-bold text-on-surface mt-0.5" style={{ fontFamily: "'Playfair Display', serif" }}>
                                    {service.name}
                                </h1>
                            </div>
                        </div>
                        <div className="text-right font-mono">
                            <span className="text-xs md:text-sm font-bold text-primary-container block">
                                {service.price_gold} Koin Emas
                            </span>
                            <span className="text-[10px] text-on-surface-variant block">
                                atau {formatRupiah(service.price_fiat)}
                            </span>
                        </div>
                    </div>
                    <p className="text-xs text-on-surface-variant leading-relaxed">
                        {service.description}
                    </p>
                    <div className="pt-2 border-t border-glass-stroke flex items-center justify-between text-[10px] text-on-surface-variant font-mono">
                        <span>Pemberi Lisensi: <strong>PitiCoin Hub</strong></span>
                        <span className="text-secondary font-bold">Terverifikasi & Diawasi</span>
                    </div>
                </div>

                {/* Error Banner */}
                {errorMsg && (
                    <div className="p-3.5 rounded-xl bg-error/20 border border-error/50 text-error text-xs font-semibold animate-fade-in flex items-center gap-2">
                        <span className="material-symbols-outlined text-base">error</span>
                        <span>{errorMsg}</span>
                    </div>
                )}

                {/* Success Screen */}
                {isSuccess && generatedLicense ? (
                    <div className="glass-panel p-6 rounded-2xl border border-secondary/50 text-center space-y-4 animate-fade-in bg-gradient-to-b from-secondary/10 to-transparent shadow-2xl">
                        <div className="w-16 h-16 rounded-full bg-secondary/20 border border-secondary/40 text-secondary flex items-center justify-center mx-auto shadow-[0_0_20px_rgba(76,175,80,0.4)]">
                            <span className="material-symbols-outlined text-4xl">verified</span>
                        </div>

                        <div className="space-y-1">
                            <span className="text-[10px] font-mono uppercase tracking-widest text-secondary font-bold">
                                LISENSI RESMI TERBIT
                            </span>
                            <h2 className="text-lg font-bold text-on-surface" style={{ fontFamily: "'Playfair Display', serif" }}>
                                Pendaftaran Berhasil Diaktifkan!
                            </h2>
                            <p className="text-xs text-on-surface-variant max-w-sm mx-auto">
                                Lisensi untuk <strong>{service.name}</strong> telah aktif dan terdaftar secara permanen di bawah pengawasan PitiCoin Hub.
                            </p>
                        </div>

                        <div className="p-4 rounded-xl bg-surface/70 border border-glass-stroke font-mono text-xs space-y-2">
                            <div className="flex justify-between">
                                <span className="text-on-surface-variant">KODE LISENSI:</span>
                                <strong className="text-primary-container font-bold tracking-wider">{generatedLicense.license_code}</strong>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-on-surface-variant">STATUS:</span>
                                <strong className="text-secondary">{generatedLicense.status}</strong>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-on-surface-variant">TIPE KEPEMILIKAN:</span>
                                <strong>{generatedLicense.target_type === 'SELF' ? 'Pribadi' : 'Mitra Jaringan'}</strong>
                            </div>
                        </div>

                        {/* Unimart Provisioned Store Card */}
                        {generatedLicense.metadata?.unimart_store_url && (
                            <div className="p-4 rounded-xl bg-surface/90 border border-emerald-500/40 text-left space-y-2 font-mono text-xs shadow-md">
                                <div className="flex items-center gap-2 text-emerald-400 font-bold text-xs">
                                    <span className="material-symbols-outlined text-base">storefront</span>
                                    <span>Toko Unimart Siap Digunakan!</span>
                                </div>
                                <div className="space-y-1 text-[11px]">
                                    <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1">
                                        <span className="text-on-surface-variant">URL Toko (Otonom):</span>
                                        <a
                                            href={generatedLicense.metadata.unimart_store_url}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="text-primary-container font-bold underline truncate"
                                        >
                                            {generatedLicense.metadata.unimart_store_url}
                                        </a>
                                    </div>
                                    {generatedLicense.metadata.unimart_login_url && (
                                        <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1 border-t border-glass-stroke pt-1">
                                            <span className="text-on-surface-variant">Dasbor Pemilik:</span>
                                            <a
                                                href={generatedLicense.metadata.unimart_login_url}
                                                target="_blank"
                                                rel="noreferrer"
                                                className="text-on-surface underline truncate"
                                            >
                                                {generatedLicense.metadata.unimart_login_url}
                                            </a>
                                        </div>
                                    )}
                                    {generatedLicense.metadata.unimart_initial_password && (
                                        <div className="flex justify-between items-center border-t border-glass-stroke pt-1 text-[10px]">
                                            <span className="text-on-surface-variant">Password Default:</span>
                                            <code className="text-amber-400 font-bold select-all bg-black/40 px-1.5 py-0.5 rounded">
                                                {generatedLicense.metadata.unimart_initial_password}
                                            </code>
                                        </div>
                                    )}
                                </div>
                            </div>
                        )}

                        <div className="space-y-2 pt-2">
                            {(redirectUrl || service.service_url) && (
                                <a
                                    href={redirectUrl || `${service.service_url}?license=${generatedLicense.license_code}`}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="w-full py-3 rounded-xl bg-gradient-to-r from-amber-500 via-primary-container to-amber-400 text-on-primary font-bold text-xs uppercase tracking-wider hover:brightness-110 active:scale-95 transition-all shadow-[0_0_15px_rgba(255,215,0,0.5)] flex items-center justify-center gap-2"
                                >
                                    <span>Buka Toko / Layanan Sekarang</span>
                                    <span className="material-symbols-outlined text-sm">open_in_new</span>
                                </a>
                            )}

                            <Link
                                href="/dashboard"
                                className="w-full py-3 rounded-xl bg-surface-variant text-on-surface hover:text-white font-bold text-xs uppercase tracking-wider transition-colors block"
                            >
                                Kembali ke Dashboard PitiCoin
                            </Link>
                        </div>
                    </div>
                ) : (
                    /* Multi-Step Wizard Content */
                    <div className="space-y-4">
                        {/* Stepper Progress Indicator */}
                        <div className="glass-panel p-3 rounded-xl border border-glass-stroke flex items-center justify-between font-mono text-[10px]">
                            <span className="text-on-surface-variant">
                                Langkah <strong>{currentStep}</strong> dari <strong>{totalStepsCount}</strong>
                            </span>
                            <div className="flex items-center gap-1">
                                {Array.from({ length: totalStepsCount }).map((_, idx) => (
                                    <div
                                        key={idx}
                                        className={`h-1.5 rounded-full transition-all duration-300 ${
                                            idx + 1 === currentStep
                                                ? 'w-6 bg-primary-container'
                                                : idx + 1 < currentStep
                                                ? 'w-3 bg-secondary'
                                                : 'w-2 bg-surface-variant/40'
                                        }`}
                                    />
                                ))}
                            </div>
                        </div>

                        {/* STEP 1: IDENTITAS PENERIMA LISENSI (SMART LOOKUP GATE) */}
                        {currentStep === 1 && (
                            <div className="glass-panel p-5 rounded-2xl border border-glass-stroke space-y-4 animate-fade-in">
                                <div className="border-b border-glass-stroke pb-3">
                                    <span className="text-[10px] text-primary-container font-mono uppercase font-bold tracking-wider">
                                        LANGKAH 1: IDENTITAS PENERIMA
                                    </span>
                                    <h3 className="text-sm font-bold text-on-surface mt-0.5">
                                        Untuk Siapa Layanan Ini Didaftarkan?
                                    </h3>
                                    <p className="text-xs text-on-surface-variant mt-0.5">
                                        Masukkan nomor WhatsApp Anda untuk aktivasi pribadi, atau masukkan nomor WhatsApp rekan bisnis Anda.
                                    </p>
                                </div>

                                <div className="space-y-3">
                                    <div>
                                        <label className="block text-xs font-semibold text-on-surface mb-1">
                                            Nomor WhatsApp / User ID Penerima <span className="text-primary-container">*</span>
                                        </label>
                                        <div className="relative">
                                            <input
                                                type="text"
                                                value={identifier}
                                                onChange={(e) => setIdentifier(e.target.value)}
                                                placeholder="Contoh: 08123456789"
                                                className="w-full bg-surface/60 border border-glass-stroke rounded-xl px-3.5 py-2.5 text-xs text-on-surface font-mono outline-none focus:border-primary-container transition-colors"
                                            />
                                            {checkingUser && (
                                                <span className="material-symbols-outlined text-primary-container text-sm animate-spin absolute right-3 top-3">
                                                    sync
                                                </span>
                                            )}
                                        </div>
                                        <div className="flex gap-2 mt-1.5">
                                            <button
                                                type="button"
                                                onClick={() => setIdentifier(auth_user.whatsapp)}
                                                className="text-[10px] text-primary-container hover:underline font-mono"
                                            >
                                                Gunakan No. WhatsApp Saya Sendiri
                                            </button>
                                        </div>
                                    </div>

                                    {/* Verification Status Card */}
                                    {isExistingUser === true && beneficiaryUser && (
                                        <div className="p-3.5 rounded-xl border border-secondary/40 bg-secondary/10 space-y-1.5 animate-fade-in text-xs">
                                            <div className="flex items-center justify-between">
                                                <span className="text-[10px] font-bold text-secondary uppercase font-mono flex items-center gap-1">
                                                    <span className="material-symbols-outlined text-xs">check_circle</span>
                                                    Akun PitiCoin Terverifikasi
                                                </span>
                                                <span className="text-[9px] px-2 py-0.2 rounded font-mono font-bold bg-secondary/20 text-secondary">
                                                    {isSelf ? 'Pendaftaran Diri Sendiri' : 'Pendaftaran Rekan Jaringan'}
                                                </span>
                                            </div>
                                            <div className="flex items-center gap-3">
                                                <div className="w-10 h-10 rounded-lg bg-surface border border-secondary/30 flex items-center justify-center text-secondary font-bold text-sm">
                                                    {beneficiaryUser.name.charAt(0)}
                                                </div>
                                                <div>
                                                    <strong className="block text-on-surface">{beneficiaryUser.name}</strong>
                                                    <span className="text-[10px] font-mono text-on-surface-variant">
                                                        @{beneficiaryUser.username || beneficiaryUser.whatsapp} • Level {beneficiaryUser.level}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    )}

                                    {isExistingUser === false && (
                                        <div className="p-3.5 rounded-xl border border-amber-500/40 bg-amber-500/10 space-y-3 animate-fade-in text-xs">
                                            <div className="space-y-1">
                                                <span className="text-[10px] font-bold text-primary-container uppercase font-mono flex items-center gap-1">
                                                    <span className="material-symbols-outlined text-xs">person_add</span>
                                                    Calon Mitra Baru Terdeteksi
                                                </span>
                                                <p className="text-[11px] text-on-surface-variant leading-relaxed">
                                                    Nomor ini belum terdaftar di PitiCoin.id. Sistem akan <strong>otomatis membuatkan akun</strong> dan mendaftarkannya sebagai <strong>Downline Level 1</strong> di bawah jaringan Anda!
                                                </p>
                                            </div>

                                            <div>
                                                <label className="block text-xs font-semibold text-on-surface mb-1">
                                                    Nama Lengkap Pemilik / Mitra <span className="text-primary-container">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    value={newUserName}
                                                    onChange={(e) => setNewUserName(e.target.value)}
                                                    placeholder="Nama lengkap penerima lisensi..."
                                                    className="w-full bg-surface/70 border border-amber-500/40 rounded-lg px-3 py-2 text-xs text-on-surface outline-none focus:border-primary-container"
                                                />
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>
                        )}

                        {/* STEP 2..N: DYNAMIC FORM STEPS (FROM JSON SCHEMA) */}
                        {currentStep > 1 && currentStep <= 1 + totalCustomSteps && (() => {
                            const stepIdx = currentStep - 2;
                            const customStep = steps[stepIdx];
                            if (!customStep) return null;

                            return (
                                <div className="glass-panel p-5 rounded-2xl border border-glass-stroke space-y-4 animate-fade-in">
                                    <div className="border-b border-glass-stroke pb-3">
                                        <span className="text-[10px] text-primary-container font-mono uppercase font-bold tracking-wider">
                                            LANGKAH {currentStep}: {customStep.title}
                                        </span>
                                        {customStep.description && (
                                            <p className="text-xs text-on-surface-variant mt-0.5">
                                                {customStep.description}
                                            </p>
                                        )}
                                    </div>

                                    <div className="space-y-3.5">
                                        {customStep.fields.map((field) => (
                                            <div key={field.name}>
                                                <label className="block text-xs font-semibold text-on-surface mb-1">
                                                    {field.label} {field.required && <span className="text-primary-container">*</span>}
                                                </label>

                                                {field.type === 'select' ? (
                                                    <select
                                                        value={formData[field.name] || ''}
                                                        onChange={(e) => setFormData({ ...formData, [field.name]: e.target.value })}
                                                        className="w-full bg-surface/70 border border-glass-stroke rounded-xl px-3 py-2.5 text-xs text-on-surface outline-none focus:border-primary-container transition-colors"
                                                    >
                                                        <option value="">Pilih opsi...</option>
                                                        {field.options?.map((opt) => (
                                                            <option key={opt} value={opt} className="bg-surface text-on-surface">
                                                                {opt}
                                                            </option>
                                                        ))}
                                                    </select>
                                                ) : field.type === 'textarea' ? (
                                                    <textarea
                                                        rows={3}
                                                        value={formData[field.name] || ''}
                                                        onChange={(e) => setFormData({ ...formData, [field.name]: e.target.value })}
                                                        placeholder={field.placeholder || ''}
                                                        className="w-full bg-surface/70 border border-glass-stroke rounded-xl px-3 py-2 text-xs text-on-surface outline-none focus:border-primary-container transition-colors"
                                                    />
                                                ) : (
                                                    <input
                                                        type={field.type}
                                                        value={formData[field.name] || ''}
                                                        onChange={(e) => setFormData({ ...formData, [field.name]: e.target.value })}
                                                        placeholder={field.placeholder || ''}
                                                        className="w-full bg-surface/70 border border-glass-stroke rounded-xl px-3.5 py-2.5 text-xs text-on-surface outline-none focus:border-primary-container transition-colors"
                                                    />
                                                )}

                                                {field.name === 'domain' && (
                                                    <div className="mt-1.5 p-2 rounded-lg bg-surface/90 border border-primary-container/30 text-[11px] text-on-surface-variant flex items-center gap-1.5 font-mono">
                                                        <span className="material-symbols-outlined text-primary-container text-xs">link</span>
                                                        <span>URL Toko Otonom:</span>
                                                        <span className="text-primary-container font-bold">
                                                            unimart.group.localhost/s/{formData.domain ? formData.domain.toLowerCase().replace(/[^a-z0-9-]/g, '-') : 'nama-toko'}
                                                        </span>
                                                    </div>
                                                )}
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            );
                        })()}

                        {/* FINAL STEP: REVIEW & PEMBAYARAN (STRICT TOKENOMICS) */}
                        {currentStep === totalStepsCount && (
                            <div className="glass-panel p-5 rounded-2xl border border-glass-stroke space-y-5 animate-fade-in">
                                <div className="border-b border-glass-stroke pb-3">
                                    <span className="text-[10px] text-primary-container font-mono uppercase font-bold tracking-wider">
                                        LANGKAH AKHIR: REVIEW & AKTIVASI LISENSI
                                    </span>
                                    <h3 className="text-sm font-bold text-on-surface mt-0.5">
                                        Pilih Metode Pembayaran
                                    </h3>
                                    <p className="text-xs text-on-surface-variant mt-0.5">
                                        Selesaikan pembayaran aktivasi menggunakan Koin Emas atau Rupiah.
                                    </p>
                                </div>

                                {/* Order Summary Card */}
                                <div className="p-4 rounded-xl bg-surface/60 border border-glass-stroke space-y-2 text-xs font-mono">
                                    <div className="flex justify-between">
                                        <span className="text-on-surface-variant">Penerima Lisensi:</span>
                                        <strong className="text-on-surface">
                                            {isExistingUser ? beneficiaryUser?.name : newUserName} ({identifier})
                                        </strong>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-on-surface-variant">Status Jaringan:</span>
                                        <span className="text-secondary font-bold">
                                            {isSelf ? 'Akun Anda Sendiri' : 'Mitra Downline Level 1'}
                                        </span>
                                    </div>
                                    <div className="flex justify-between border-t border-glass-stroke pt-2">
                                        <span className="text-on-surface-variant">Layanan:</span>
                                        <span className="text-primary-container font-bold">
                                            {service.name} {formData.package_tier ? `(${currentPricing.name})` : ''}
                                        </span>
                                    </div>
                                </div>

                                {/* Scheme Selector: FULL vs INSTALLMENT (if enabled) */}
                                {service.allows_installments && (
                                    <div className="space-y-2">
                                        <span className="text-xs font-bold text-on-surface uppercase tracking-wider block">
                                            Skema Pembayaran
                                        </span>
                                        <div className="grid grid-cols-2 gap-3">
                                            <button
                                                type="button"
                                                onClick={() => setPaymentType('FULL')}
                                                className={`p-3 rounded-xl border text-left transition-all ${
                                                    paymentType === 'FULL'
                                                        ? 'bg-primary-container/20 border-primary-container text-white'
                                                        : 'bg-surface/50 border-glass-stroke text-on-surface-variant hover:bg-white/5'
                                                }`}
                                            >
                                                <div className="flex items-center justify-between mb-1">
                                                    <span className="text-xs font-bold text-white flex items-center gap-1.5">
                                                        <span className="material-symbols-outlined text-sm text-emerald-400">check_circle</span>
                                                        Bayar Lunas
                                                    </span>
                                                    <span className="text-[9px] px-1.5 py-0.2 rounded font-mono bg-emerald-500/20 text-emerald-300">
                                                        100%
                                                    </span>
                                                </div>
                                                <p className="text-[10px] text-on-surface-variant">
                                                    Pelunasan penuh sekaligus. Lisensi & status langsung 100% tuntas.
                                                </p>
                                            </button>

                                            <button
                                                type="button"
                                                onClick={() => setPaymentType('INSTALLMENT')}
                                                className={`p-3 rounded-xl border text-left transition-all ${
                                                    paymentType === 'INSTALLMENT'
                                                        ? 'bg-indigo-500/20 border-indigo-500 text-white'
                                                        : 'bg-surface/50 border-glass-stroke text-on-surface-variant hover:bg-white/5'
                                                }`}
                                            >
                                                <div className="flex items-center justify-between mb-1">
                                                    <span className="text-xs font-bold text-white flex items-center gap-1.5">
                                                        <span className="material-symbols-outlined text-sm text-indigo-400">schedule</span>
                                                        Bayar Bertahap
                                                    </span>
                                                    <span className="text-[9px] px-1.5 py-0.2 rounded font-mono bg-indigo-500/20 text-indigo-300">
                                                        CICILAN
                                                    </span>
                                                </div>
                                                <p className="text-[10px] text-on-surface-variant">
                                                    Mulai dengan Uang Muka (DP). Sisa dapat dicicil kapan saja.
                                                </p>
                                            </button>
                                        </div>
                                    </div>
                                )}

                                {/* Payment Methods Selector: GOLD vs FIAT only */}
                                <div className="space-y-3">
                                    <span className="text-xs font-bold text-on-surface uppercase tracking-wider block">
                                        Metode Pembayaran Resmi
                                    </span>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        {/* Option 1: Koin Emas */}
                                        <div
                                            onClick={() => setPaymentMethod('GOLD')}
                                            className={`p-4 rounded-xl border cursor-pointer transition-all ${
                                                paymentMethod === 'GOLD'
                                                    ? 'bg-primary-container/20 border-primary-container shadow-[0_0_15px_rgba(255,215,0,0.25)]'
                                                    : 'bg-surface/50 border-glass-stroke hover:bg-white/5'
                                            }`}
                                        >
                                            <div className="flex justify-between items-center mb-1.5">
                                                <strong className="text-xs font-bold text-primary-container flex items-center gap-1.5">
                                                    <span className="material-symbols-outlined text-base">toll</span>
                                                    Koin Emas PitiCoin
                                                </strong>
                                                <span className="text-[9px] bg-primary-container text-on-primary px-1.5 py-0.2 rounded font-bold">
                                                    HEMAT 20%
                                                </span>
                                            </div>
                                            <div className="text-sm font-mono font-bold text-on-surface">
                                                {isInstallment ? `${currentCharge.toLocaleString('id-ID')} Emas (DP)` : `${currentPricing.gold} Koin Emas`}
                                            </div>
                                            <div className="text-[10px] text-on-surface-variant mt-1">
                                                Saldo Emas Anda: <strong className="font-mono text-primary-container">{gold_balance} Emas</strong>
                                            </div>
                                        </div>

                                        {/* Option 2: Rupiah (QRIS / Transfer) */}
                                        <div
                                            onClick={() => setPaymentMethod('FIAT')}
                                            className={`p-4 rounded-xl border cursor-pointer transition-all ${
                                                paymentMethod === 'FIAT'
                                                    ? 'bg-primary-container/20 border-primary-container shadow-[0_0_15px_rgba(255,215,0,0.25)]'
                                                    : 'bg-surface/50 border-glass-stroke hover:bg-white/5'
                                            }`}
                                        >
                                            <div className="flex justify-between items-center mb-1.5">
                                                <strong className="text-xs font-bold text-on-surface flex items-center gap-1.5">
                                                    <span className="material-symbols-outlined text-base">qr_code_2</span>
                                                    Rupiah (QRIS / Transfer)
                                                </strong>
                                                <span className="text-[9px] text-on-surface-variant font-mono">
                                                    INSTAN
                                                </span>
                                            </div>
                                            <div className="text-sm font-mono font-bold text-on-surface">
                                                {isInstallment ? `${formatRupiah(currentCharge)} (DP)` : formatRupiah(currentPricing.fiat)}
                                            </div>
                                            <div className="text-[10px] text-on-surface-variant mt-1">
                                                QRIS Dinamis & Virtual Account Bank
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Custom Down Payment Input (If Installment) */}
                                {isInstallment && (
                                    <div className="p-4 bg-indigo-500/10 border border-indigo-500/30 rounded-xl space-y-3">
                                        <div className="flex items-center justify-between">
                                            <label className="text-xs font-bold text-indigo-300 uppercase tracking-wider flex items-center gap-1.5">
                                                <span className="material-symbols-outlined text-sm">payments</span>
                                                Nominal Uang Muka (DP Termin 1)
                                            </label>
                                            <span className="text-[10px] font-mono text-indigo-300">
                                                Min. {paymentMethod === 'GOLD' ? `${minDp} Emas` : formatRupiah(minDp)}
                                            </span>
                                        </div>

                                        <input
                                            type="number"
                                            step="any"
                                            value={customDownPayment}
                                            onChange={(e) => setCustomDownPayment(e.target.value)}
                                            placeholder={`Minimal ${minDp}`}
                                            className="w-full px-3.5 py-2.5 bg-black/40 border border-indigo-500/40 rounded-xl text-white font-mono text-sm focus:outline-none focus:border-indigo-400"
                                        />

                                        <div className="flex justify-between text-[11px] font-mono border-t border-indigo-500/20 pt-2 text-indigo-200">
                                            <span>Sisa Tagihan Berikutnya:</span>
                                            <span className="font-bold text-white">
                                                {paymentMethod === 'GOLD'
                                                    ? `${remainingBalance.toLocaleString('id-ID')} Emas`
                                                    : formatRupiah(remainingBalance)}
                                            </span>
                                        </div>

                                        <p className="text-[10px] text-indigo-300/80 leading-relaxed">
                                            💡 Komisi afiliasi untuk jaringan sponsor Anda akan <b>langsung dibagikan secara proporsional</b> dari pembayaran uang muka ini.
                                        </p>
                                    </div>
                                )}

                                {paymentMethod === 'GOLD' && gold_balance < currentCharge && (
                                    <div className="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-xs text-on-surface space-y-1">
                                        <strong className="text-primary-container block">Saldo Koin Emas Kurang</strong>
                                        <p className="text-[11px] text-on-surface-variant">
                                            Anda memiliki {gold_balance} Emas, dibutuhkan {currentCharge} Emas. Silakan pilih pembayaran Rupiah atau lebur koin / ikuti misi terlebih dahulu.
                                        </p>
                                    </div>
                                )}

                                <button
                                    onClick={handleSubmitOrder}
                                    disabled={loading || (paymentMethod === 'GOLD' && gold_balance < currentCharge)}
                                    className="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-500 via-primary-container to-amber-400 text-on-primary font-bold text-xs uppercase tracking-wider hover:brightness-110 active:scale-95 transition-all shadow-[0_0_15px_rgba(255,215,0,0.4)] disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer"
                                >
                                    {loading ? (
                                        <>
                                            <span className="material-symbols-outlined text-base animate-spin">sync</span>
                                            <span>Memproses Transaksi...</span>
                                        </>
                                    ) : (
                                        <>
                                            <span className="material-symbols-outlined text-base">check_circle</span>
                                            <span>
                                                {isInstallment
                                                    ? `Bayar DP & Mulai Paket Sekarang (${paymentMethod === 'GOLD' ? `${currentCharge} Emas` : formatRupiah(currentCharge)})`
                                                    : 'Bayar & Terbitkan Lisensi Sekarang'}
                                            </span>
                                        </>
                                    )}
                                </button>
                            </div>
                        )}

                        {/* Stepper Navigation Buttons */}
                        <div className="flex justify-between items-center gap-3 pt-2">
                            {currentStep > 1 && (
                                <button
                                    type="button"
                                    onClick={handleBack}
                                    disabled={loading}
                                    className="px-4 py-2.5 rounded-xl bg-surface/60 border border-glass-stroke text-on-surface font-semibold text-xs hover:bg-white/5 active:scale-95 transition-all flex items-center gap-1.5"
                                >
                                    <span className="material-symbols-outlined text-base">chevron_left</span>
                                    <span>Sebelumnya</span>
                                </button>
                            )}

                            <div className="flex-1" />

                            {currentStep < totalStepsCount && (
                                <button
                                    type="button"
                                    onClick={handleNext}
                                    disabled={currentStep === 1 && !isStep1Valid()}
                                    className="px-6 py-2.5 rounded-xl bg-primary-container text-on-primary font-bold text-xs uppercase tracking-wider hover:brightness-110 active:scale-95 transition-all shadow disabled:opacity-40 flex items-center gap-1.5"
                                >
                                    <span>Lanjut ke Langkah {currentStep + 1}</span>
                                    <span className="material-symbols-outlined text-base">chevron_right</span>
                                </button>
                            )}
                        </div>
                    </div>
                )}
            </main>
        </div>
    );
}
