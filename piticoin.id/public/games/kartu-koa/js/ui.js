/* Redesigned UI Manager - KOA Slot (Mobile-First 5x5 Grid Version) */

const KOAUI = {
    // Current state in UI (in Perak, displayed scaled by 1,000)
    currentBet: 20,
    betOptions: [20, 50, 100, 500, 1000, 5000, 10000, 18000],
    turboModeActive: false,
    autoSpinActive: false,

    // DOM Elements
    elements: {
        // Balances
        personalSilverBalance: null,
        personalSilverSub: null,
        globalGoldPool: null,
        personalGoldMined: null,
        personalGoldQuota: null,
        
        // Bet Selection
        btnBetMinus: null,
        btnBetPlus: null,
        valTotalBet: null,
        btnMaxBet: null,

        // Actions
        btnTurbo: null,
        btnAuto: null,
        btnSpin: null,
        btnPayTable: null,

        // Modals & Toast
        modalContainer: null,
        modalTitle: null,
        modalMessage: null,
        btnModalClose: null,
        toastContainer: null,

        // Claim Gold Modal
        modalClaimGold: null,
        modalGoldQuotaVal: null,
        modalSilverNeededVal: null,
        btnClaimGoldConfirm: null,
        btnClaimClose: null
    },

    init() {
        // Cache DOM elements
        this.elements.personalSilverBalance = document.getElementById("personal-silver-balance");
        this.elements.personalSilverSub = document.getElementById("personal-silver-sub");
        this.elements.globalGoldPool = document.getElementById("global-gold-pool");
        this.elements.personalGoldMined = document.getElementById("personal-gold-balance");
        this.elements.personalGoldQuota = document.getElementById("personal-gold-quota");
        
        this.elements.btnBetMinus = document.getElementById("btn-bet-minus");
        this.elements.btnBetPlus = document.getElementById("btn-bet-plus");
        this.elements.valTotalBet = document.getElementById("val-total-bet");
        this.elements.btnMaxBet = document.getElementById("btn-max-bet");

        this.elements.btnTurbo = document.getElementById("btn-turbo");
        this.elements.btnAuto = document.getElementById("btn-auto");
        this.elements.btnSpin = document.getElementById("btn-spin");
        this.elements.btnPayTable = document.getElementById("btn-pay-table");

        this.elements.modalContainer = document.getElementById("modal-container");
        this.elements.modalTitle = document.getElementById("modal-title");
        this.elements.modalMessage = document.getElementById("modal-message");
        this.elements.btnModalClose = document.getElementById("btn-modal-close");
        this.elements.toastContainer = document.getElementById("toast-container");

        // Claim Gold Modal elements
        this.elements.modalClaimGold = document.getElementById("claim-gold-modal");
        this.elements.modalGoldQuotaVal = document.getElementById("modal-gold-quota-val");
        this.elements.modalSilverNeededVal = document.getElementById("modal-silver-needed-val");
        this.elements.btnClaimGoldConfirm = document.getElementById("btn-claim-gold-confirm");
        this.elements.btnClaimClose = document.getElementById("btn-claim-close");

        // Event Listeners
        this.setupEventListeners();

        // Start dynamic pool fluctuation simulation
        this.startGlobalPoolFluctuation();

        // Fetch User state and balance from server
        this.fetchUserState();
    },

    async fetchUserState() {
        try {
            const response = await fetch('/api/game/koa/state', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                if (response.status === 403) {
                    // Access Denied: Unlicensed or inactive license
                    this.showModal("AKSES DITOLAK", "Akses ditolak: Hanya untuk member aktif dengan lisensi. Silakan aktifkan lisensi Anda di dashboard utama.");
                    if (this.elements.btnSpin) this.elements.btnSpin.disabled = true;
                    return;
                }
                throw new Error(`State fetch failed: status ${response.status}`);
            }

            const data = await response.json();
            if (data.success) {
                const engine = window.KOAEngine;
                if (engine) {
                    // Sync backend balances
                    engine.userSilver = data.wallet.silver_balance;
                    engine.userGoldVault = data.wallet.gold_balance;
                    engine.liquidityPool = data.global_pool.total_fiat_in_bank;

                    // Sync game configs dynamically
                    if (data.game_config) {
                        if (data.game_config.bet_options) this.betOptions = data.game_config.bet_options;
                        if (data.game_config.card_multipliers) engine.cardMultipliers = data.game_config.card_multipliers;
                        if (data.game_config.multipliers_normal) engine.multipliersNormal = data.game_config.multipliers_normal;
                        if (data.game_config.multipliers_free_spins) engine.multipliersFreeSpins = data.game_config.multipliers_free_spins;
                        if (data.game_config.conversion_rate) engine.goldToSilverRate = data.game_config.conversion_rate;
                    }

                    engine.updateWinCap();
                }

                // Update CSRF token dynamically in HTML header
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                if (csrfMeta && data.csrf_token) {
                    csrfMeta.setAttribute('content', data.csrf_token);
                }

                this.updateDashboard();
            }
        } catch (err) {
            console.warn("Failed to fetch server state. Falling back to local offline mode:", err.message);
            this.showToast("Menjalankan dalam Mode Offline (Simulasi)", "warning");
            this.updateDashboard();
        }
    },

    setupEventListeners() {
        // Minus Bet
        this.elements.btnBetMinus.addEventListener("click", () => {
            const idx = this.betOptions.indexOf(this.currentBet);
            if (idx > 0) {
                this.currentBet = this.betOptions[idx - 1];
                this.updateBetDisplay();
            }
        });

        // Plus Bet
        this.elements.btnBetPlus.addEventListener("click", () => {
            const idx = this.betOptions.indexOf(this.currentBet);
            if (idx < this.betOptions.length - 1) {
                this.currentBet = this.betOptions[idx + 1];
                this.updateBetDisplay();
            }
        });

        // Max Bet
        this.elements.btnMaxBet.addEventListener("click", () => {
            this.currentBet = this.betOptions[this.betOptions.length - 1];
            this.updateBetDisplay();
            this.showToast(`Taruhan disetel ke maksimum: ${(this.currentBet * 1000).toLocaleString("id-ID")} Koin`, "success");
        });

        // Mock Deposit / Buy credits trigger
        const mockDeposit = document.getElementById("btn-mock-deposit");
        if (mockDeposit) {
            mockDeposit.addEventListener("click", () => {
                const engine = window.KOAEngine;
                if (engine) {
                    engine.userSilver += 5000; // +5,000 Perak (+5,000,000 coins)
                    this.updateDashboard();
                    this.showToast("Top-up simulasi: +5.000.000 Koin perak ditambahkan!", "success");
                }
            });
        }

        const mockGoldDeposit = document.getElementById("btn-mock-deposit-gold");
        if (mockGoldDeposit) {
            mockGoldDeposit.addEventListener("click", () => {
                const engine = window.KOAEngine;
                if (engine) {
                    engine.userSilver += 5000;
                    this.updateDashboard();
                    this.showToast("Top-up perak berhasil!", "success");
                }
            });
        }

        // Turbo toggle
        this.elements.btnTurbo.addEventListener("click", () => {
            this.turboModeActive = !this.turboModeActive;
            if (this.turboModeActive) {
                this.elements.btnTurbo.classList.add("active");
                this.showToast("Turbo Mode Aktif (Animasi Cepat)", "success");
            } else {
                this.elements.btnTurbo.classList.remove("active");
                this.showToast("Turbo Mode Nonaktif", "success");
            }
        });

        // Auto spin toggle
        this.elements.btnAuto.addEventListener("click", () => {
            this.autoSpinActive = !this.autoSpinActive;
            if (this.autoSpinActive) {
                this.elements.btnAuto.classList.add("active");
                this.showToast("Auto Spin Aktif", "success");
                if (window.KOAGame && !window.KOAGame.btnSpin.disabled) {
                    window.KOAGame.spin();
                }
            } else {
                this.elements.btnAuto.classList.remove("active");
                this.showToast("Auto Spin Dinonaktifkan", "success");
            }
        });

        // Pay Table Button
        this.elements.btnPayTable.addEventListener("click", () => {
            this.showPayTableModal();
        });

        // Test Free Spins Cheat Trigger
        const btnTestFS = document.getElementById("btn-test-free-spins");
        if (btnTestFS) {
            btnTestFS.addEventListener("click", () => {
                window.forceFreeSpinsTrigger = true;
                this.showToast("⚡ Pemicu Free Spins Aktif! Memulai spin dramatis...", "success");
                if (window.KOAGame && !window.KOAGame.btnSpin.disabled) {
                    window.KOAGame.spin();
                }
            });
        }

        // Hamburger Menu Dialog
        const btnMenu = document.getElementById("btn-menu");
        const menuModal = document.getElementById("hamburger-menu-modal");
        const btnMenuClose = document.getElementById("btn-menu-close");

        if (btnMenu && menuModal) {
            btnMenu.addEventListener("click", () => {
                menuModal.classList.remove("hidden");
            });
        }

        if (btnMenuClose && menuModal) {
            btnMenuClose.addEventListener("click", () => {
                menuModal.classList.add("hidden");
            });
        }

        // Setup options inside hamburger side modal
        const setupSideOpt = (id, action) => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener("click", () => {
                    if (menuModal) menuModal.classList.add("hidden");
                    action();
                });
            }
        };

        setupSideOpt("menu-opt-home-side", () => this.showToast("Kembali ke Lobi Utama PitiCoin", "success"));
        setupSideOpt("menu-opt-rank-side", () => this.showRankModal());
        setupSideOpt("menu-opt-misi-side", () => this.showMisiModal());
        setupSideOpt("menu-opt-inbox-side", () => this.showInboxModal());
        setupSideOpt("menu-opt-toko-side", () => this.showTokoModal());

        // Modal Close
        this.elements.btnModalClose.addEventListener("click", () => {
            this.elements.modalContainer.classList.add("hidden");
        });

        // Setup Claim Gold Modal listeners
        if (this.elements.btnClaimClose) {
            this.elements.btnClaimClose.addEventListener("click", () => {
                this.elements.modalClaimGold.classList.add("hidden");
            });
        }

        if (this.elements.btnClaimGoldConfirm) {
            this.elements.btnClaimGoldConfirm.addEventListener("click", () => {
                this.handleClaimGold();
            });
        }

        // Click HUD area or Chest to trigger claim modal
        const chest = document.getElementById("chest-box");
        if (chest) {
            chest.addEventListener("click", () => this.showClaimGoldModal());
        }

        if (this.elements.personalGoldMined) {
            this.elements.personalGoldMined.style.cursor = "pointer";
            this.elements.personalGoldMined.addEventListener("click", () => this.showClaimGoldModal());
        }

        const quotaTrigger = document.getElementById("personal-gold-quota-trigger");
        if (quotaTrigger) {
            quotaTrigger.style.cursor = "pointer";
            quotaTrigger.addEventListener("click", (e) => {
                // Don't double-fire if the KLAIM button itself was clicked
                if (e.target.id === "btn-claim-hud") return;
                this.showClaimGoldModal();
            });
        }

        // Dedicated KLAIM HUD button
        const btnClaimHud = document.getElementById("btn-claim-hud");
        if (btnClaimHud) {
            btnClaimHud.addEventListener("click", (e) => {
                e.stopPropagation();
                this.showClaimGoldModal();
            });
        }
    },

    showClaimGoldModal() {
        const engine = window.KOAEngine;
        if (!engine) return;

        const goldQuota = Math.floor(engine.userGoldQuota);
        if (goldQuota <= 0) {
            this.showToast("Hasil Koin Emas bulat minimal 1 untuk dapat diklaim.", "info");
            return;
        }

        const silverNeeded = goldQuota * engine.goldToSilverRate;
        
        if (this.elements.modalGoldQuotaVal) {
            this.elements.modalGoldQuotaVal.textContent = `${goldQuota.toLocaleString("id-ID")} Emas`;
        }
        if (this.elements.modalSilverNeededVal) {
            this.elements.modalSilverNeededVal.textContent = `${silverNeeded.toLocaleString("id-ID")} Perak`;
        }

        if (this.elements.modalClaimGold) {
            this.elements.modalClaimGold.classList.remove("hidden");
        }
    },

    async handleClaimGold() {
        const engine = window.KOAEngine;
        if (!engine) return;

        const goldQuota = Math.floor(engine.userGoldQuota);
        if (goldQuota <= 0) {
            this.showToast("Tidak ada Hasil Koin Emas bulat yang dapat diklaim.", "error");
            return;
        }

        const silverNeeded = goldQuota * engine.goldToSilverRate;
        if (engine.userSilver < silverNeeded) {
            this.showToast(`Dompet Perak Anda kurang. Dibutuhkan: ${silverNeeded.toLocaleString("id-ID")} Perak.`, "error");
            return;
        }

        // Lock button
        this.elements.btnClaimGoldConfirm.classList.add("disabled");
        this.elements.btnClaimGoldConfirm.disabled = true;

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

        try {
            const response = await fetch('/api/game/koa/claim', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    gold_amount: goldQuota
                })
            });

            if (!response.ok) {
                const errData = await response.json();
                throw new Error(errData.error || `Klaim gagal dengan status ${response.status}`);
            }

            const data = await response.json();
            if (data.success) {
                this.showToast(data.message || `Klaim sukses! ${goldQuota} Emas ditransfer ke Brankas.`, "success");

                // Reset quota and sync new balances
                engine.userGoldQuota -= goldQuota;
                engine.userSilver = data.wallet.silver_balance;
                engine.userGoldVault = data.wallet.gold_balance;
                engine.liquidityPool = data.global_pool.total_fiat_in_bank;
                engine.updateWinCap();

                this.updateDashboard();
                
                // Hide modal
                if (this.elements.modalClaimGold) {
                    this.elements.modalClaimGold.classList.add("hidden");
                }
            }
        } catch (err) {
            console.warn("Backend claim failed, falling back to local offline simulation:", err.message);
            // Local fallback simulation
            engine.userSilver -= silverNeeded;
            engine.userGoldVault += goldQuota;
            engine.userGoldQuota -= goldQuota;
            engine.liquidityPool -= goldQuota;
            engine.updateWinCap();

            this.showToast(`Mode Offline: Berhasil mengklaim ${goldQuota.toLocaleString("id-ID")} Emas & membakar ${silverNeeded.toLocaleString("id-ID")} Perak.`, "success");
            this.updateDashboard();

            // Hide modal
            if (this.elements.modalClaimGold) {
                this.elements.modalClaimGold.classList.add("hidden");
            }
        } finally {
            this.elements.btnClaimGoldConfirm.classList.remove("disabled");
            this.elements.btnClaimGoldConfirm.disabled = false;
        }
    },

    updateBetDisplay() {
        this.elements.valTotalBet.textContent = this.currentBet.toLocaleString("id-ID");
    },

    updateDashboard() {
        const engine = window.KOAEngine;
        if (!engine) return;

        // Display balances directly in raw coin units
        const displaySilver = engine.userSilver.toLocaleString("id-ID");
        const displayGold = engine.userGoldVault.toLocaleString("id-ID");
        const displayPool = engine.liquidityPool.toLocaleString("id-ID");
        const displayQuota = engine.userGoldQuota.toLocaleString("id-ID", { minimumFractionDigits: 0, maximumFractionDigits: 2 });

        if (this.elements.personalSilverBalance) this.elements.personalSilverBalance.textContent = displaySilver;
        if (this.elements.personalSilverSub) this.elements.personalSilverSub.textContent = displaySilver;
        if (this.elements.personalGoldMined) this.elements.personalGoldMined.textContent = displayGold;
        if (this.elements.globalGoldPool) this.elements.globalGoldPool.textContent = displayPool;
        if (this.elements.personalGoldQuota) this.elements.personalGoldQuota.textContent = displayQuota;

        this.updateBetDisplay();
    },

    startGlobalPoolFluctuation() {
        // Slowly fluctuate global gold pool to simulate live multiplayer spins
        setInterval(() => {
            const engine = window.KOAEngine;
            if (!engine) return;
            
            // Random change between -25 and +35
            const delta = Math.floor(Math.random() * 60) - 25;
            engine.liquidityPool += delta;
            
            if (engine.liquidityPool < 50000) engine.liquidityPool = 125000;
            this.updateDashboard();
        }, 6000);
    },

    showModal(title, msg) {
        if (!this.elements.modalContainer) return;
        this.elements.modalTitle.textContent = title;
        this.elements.modalMessage.innerHTML = msg;
        this.elements.modalContainer.classList.remove("hidden");
    },

    showToast(message, type = "success") {
        if (!this.elements.toastContainer) return;
        const toast = document.createElement("div");
        toast.className = `toast ${type}`;
        toast.textContent = message;
        this.elements.toastContainer.appendChild(toast);
        setTimeout(() => toast.remove(), 2500);
    },

    showRankModal() {
        const listHtml = `
            <div class="paytable-grid">
                <div class="paytable-row" style="font-weight:bold; color:var(--gold-primary);">
                    <span>RANK - USER</span>
                    <span>TOTAL EMAS</span>
                </div>
                <div class="paytable-row">
                    <span>🏆 1. Pandawa88 (VIP 5)</span>
                    <span>125.000.000</span>
                </div>
                <div class="paytable-row">
                    <span>🥈 2. Sultan_Coki</span>
                    <span>89.200.000</span>
                </div>
                <div class="paytable-row">
                    <span>🥉 3. MinangSlot_99</span>
                    <span>54.100.000</span>
                </div>
                <div class="paytable-row">
                    <span>4. Piticoin_Lord</span>
                    <span>32.000.000</span>
                </div>
            </div>
        `;
        this.showModal("RANKING TOP KOA GOLD", listHtml);
    },

    showMisiModal() {
        const misiHtml = `
            <div class="misi-list">
                <div class="misi-item">
                    <span>✔️</span>
                    <div>
                        <strong style="color:var(--gold-light);">Picu Mode Ceki (Tension):</strong><br>
                        Dapatkan 2 KOA Scatter di grid untuk memicu Gandang Tasa. (Hadiah: +200.000 koin)
                    </div>
                </div>
                <div class="misi-item">
                    <span>⏳</span>
                    <div>
                        <strong style="color:var(--gold-light);">Taklukkan K.O.AA! Free Spins:</strong><br>
                        Dapatkan 3+ KOA Scatter untuk memulai bonus Free Spin. (Hadiah: +1.000.000 koin)
                    </div>
                </div>
                <div class="misi-item">
                    <span>⏳</span>
                    <div>
                        <strong style="color:var(--gold-light);">Akumulasi Cascading x4:</strong><br>
                        Capai runtuhan cascading level 3 dalam satu spin. (Hadiah: +500.000 koin)
                    </div>
                </div>
            </div>
        `;
        this.showModal("MISI HARIAN KOA", misiHtml);
    },

    showInboxModal() {
        const inboxHtml = `
            <div class="paytable-grid">
                <div class="paytable-row" style="border-bottom:1px solid rgba(255,255,255,0.08);">
                    <div>
                        <strong style="color:var(--gold-light);">Sistem Piticoin:</strong> Pemeliharaan Selesai.<br>
                        <small>17 Juli 2026</small>
                    </div>
                </div>
                <div class="paytable-row" style="border-bottom:1px solid rgba(255,255,255,0.08);">
                    <div>
                        <strong style="color:var(--gold-light);">Kompensasi K.O.AA:</strong> Klaim gratis +500.000 koin perak!<br>
                        <small>16 Juli 2026</small>
                    </div>
                </div>
            </div>
        `;
        this.showModal("KOTAK MASUK", inboxHtml);
    },

    showTokoModal() {
        const tokoHtml = `
            <div class="paytable-grid">
                <div class="paytable-row" style="padding: 10px 0;">
                    <span>📦 Paket Pemula: 5.000.000 Koin</span>
                    <button class="btn-modal-close" style="padding: 4px 10px; font-size:0.7rem;">BELI</button>
                </div>
                <div class="paytable-row" style="padding: 10px 0;">
                    <span>📦 Paket VIP: 25.000.000 Koin</span>
                    <button class="btn-modal-close" style="padding: 4px 10px; font-size:0.7rem;">BELI</button>
                </div>
                <div class="paytable-row" style="padding: 10px 0;">
                    <span>📦 Paket Sultan: 100.000.000 Koin</span>
                    <button class="btn-modal-close" style="padding: 4px 10px; font-size:0.7rem;">BELI</button>
                </div>
            </div>
        `;
        this.showModal("TOKO KOA COINS", tokoHtml);
    },

    showPayTableModal() {
        const payHtml = `
            <div class="paytable-grid">
                <div class="paytable-row" style="font-weight:bold; color:var(--gold-primary);">
                    <span>JENIS KARTU</span>
                    <span>PENGALI UTAMA</span>
                </div>
                <div class="paytable-row">
                    <span>🃏 WILD (Mandan)</span>
                    <span>0.5x Taruhan</span>
                </div>
                <div class="paytable-row">
                    <span>👑 High Tier (21 - 30)</span>
                    <span>0.3x Taruhan</span>
                </div>
                <div class="paytable-row">
                    <span>🏹 Medium Tier (11 - 20)</span>
                    <span>0.2x Taruhan</span>
                </div>
                <div class="paytable-row">
                    <span>🌱 Low Tier (1 - 10)</span>
                    <span>0.1x Taruhan</span>
                </div>
                <div class="paytable-row">
                    <span>🌟 KOA (Scatter)</span>
                    <span>Pemicu Ceki & Free Spins (3+)</span>
                </div>
            </div>
        `;
        this.showModal("PAY TABLE & RULE", payHtml);
    }
};

document.addEventListener("DOMContentLoaded", () => {
    KOAUI.init();
});

window.KOAUI = KOAUI;
