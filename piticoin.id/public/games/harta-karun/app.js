/**
 * Harta Karun Slot - Game Logic File (Mahjong Ways 2 Mechanics)
 * Uses Web Audio API for sound effects and CSS/JS for animations.
 */

// --- CONFIGURATION & STATE ---
const SYMBOLS = {
    CROWN: { id: 'CROWN', name: 'Mahkota Emas', char: '👑', payouts: { 3: 10, 4: 40, 5: 80 }, weight: 4 },
    RUBY: { id: 'RUBY', name: 'Batu Merah', char: '💎', payouts: { 3: 8, 4: 25, 5: 50 }, weight: 6 },
    EMERALD: { id: 'EMERALD', name: 'Batu Hijau', char: '🟢', payouts: { 3: 6, 4: 20, 5: 40 }, weight: 8 },
    SAPPHIRE: { id: 'SAPPHIRE', name: 'Batu Biru', char: '🔵', payouts: { 3: 5, 4: 15, 5: 30 }, weight: 10 },
    MAP: { id: 'MAP', name: 'Peta Karun', char: '🗺️', payouts: { 3: 3, 4: 8, 5: 15 }, weight: 14 },
    KEY: { id: 'KEY', name: 'Kunci Emas', char: '🗝️', payouts: { 3: 2, 4: 5, 5: 10 }, weight: 18 },
    COIN: { id: 'COIN', name: 'Koin Emas', char: '🪙', payouts: { 3: 1, 4: 2, 5: 5 }, weight: 24 },
    WILD: { id: 'WILD', name: 'Wild (Tengkorak)', char: '💀', payouts: {}, weight: 0 },
    SCATTER: { id: 'SCATTER', name: 'Scatter (Peti Harta)', char: '📦', payouts: {}, weight: 3 },
    LIGHTNING: { id: 'LIGHTNING', name: 'Petir Pengali', char: '⚡', payouts: {}, weight: 1.5 }
};

// Reel layout sizes (Reel 1: 5 rows, Reel 2-4: 6 rows, Reel 5: 5 rows - including top peek row)
const REEL_ROWS = [5, 6, 6, 6, 5];
const BASE_BET = 20;

const BET_SIZES = [0.05, 0.10, 0.25, 0.50, 1.00, 2.50];
const BET_LEVELS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

let gameState = {
    balance: 0,
    betSizeIndex: 2, // default 0.25
    betLevelIndex: 0, // default 1
    turboMode: false,
    autoSpinsLeft: 0,
    isSpinning: false,
    isCascading: false,
    soundEnabled: true,
    
    // Piticoin Integrations State
    goldAvailable: 0,
    walletSilver: 0,
    walletGold: 0,
    poolVal: 0,
    
    // Free Spins State
    inFreeSpins: false,
    freeSpinsTotal: 0,
    freeSpinsRemaining: 0,
    freeSpinsWin: 0,
    
    // Current Spin State
    currentMultiplierStep: 1, // 1, 2, 3, 5 (or 2, 4, 6, 10 in free spins)
    board: [], // 2D array of { symbolId, isGold, isWildTransformation }
    totalWinThisSpin: 0,
    pendingWildTransformations: [], // List of coordinate strings "col,row" to transform
    collectedMultipliers: [] // Store multipliers activated this round
};

// Web Audio API Context
let audioCtx = null;

// --- DOM ELEMENTS ---
const elBalance = document.getElementById('balance-val');
const elTotalBet = document.getElementById('total-bet-val');
const elWinAmount = document.getElementById('win-amount');
const elBetSize = document.getElementById('bet-size-val');
const elBetLevel = document.getElementById('bet-level-val');
const elFreeSpinsLeft = document.getElementById('free-spins-left');
const elFreeSpinCounter = document.getElementById('free-spin-counter');
const elNoticeBanner = document.getElementById('notice-banner');
const elNoticeText = document.getElementById('notice-text');
const elTurboBtn = document.getElementById('btn-turbo');
const elAutoBtn = document.getElementById('btn-auto');
const elAutoText = document.getElementById('auto-text');
const elSpinBtn = document.getElementById('btn-spin');
const elSoundBtn = document.getElementById('btn-sound');
const elSoundIcon = document.getElementById('sound-icon');
const elPaytableBtn = document.getElementById('btn-paytable');
const elModalOverlay = document.getElementById('modal-overlay');
const elCloseModalBtn = document.getElementById('btn-close-modal');
const elAutoModal = document.getElementById('auto-modal');
const elCloseAutoBtn = document.getElementById('btn-close-auto');

// --- PITICOIN INTEGRATION FUNCTIONS ---
async function fetchGameState() {
    try {
        const res = await fetch('/api/game/harta-karun/state');
        const data = await res.json();
        if (data.success) {
            gameState.balance = data.wallet.silver_balance;
            gameState.walletSilver = data.wallet.silver_balance;
            gameState.walletGold = data.wallet.gold_balance;
            gameState.poolVal = data.global_pool.total_fiat_in_bank;
            
            // Update DOM
            const elWalletGold = document.getElementById('wallet-gold-val');
            if (elWalletGold) elWalletGold.textContent = Number(gameState.walletGold).toLocaleString('id-ID');
            
            const elPool = document.getElementById('piticoin-pool-val');
            if (elPool) elPool.textContent = 'Rp ' + Number(gameState.poolVal).toLocaleString('id-ID');
            
            updateHUD();
            updateClaimButtonState();
        }
    } catch (err) {
        console.error('Gagal mengambil status game dari server:', err);
    }
}

async function claimGold() {
    const claimableInt = Math.floor(gameState.goldAvailable);
    if (claimableInt <= 0) return;
    
    const btnClaim = document.getElementById('btn-claim-gold');
    if (!btnClaim) return;
    
    btnClaim.disabled = true;
    btnClaim.textContent = 'MEMPROSES...';
    btnClaim.classList.add('disabled');
    
    try {
        const res = await fetch('/api/game/harta-karun/claim', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ gold_amount: claimableInt })
        });
        const data = await res.json();
        if (data.success) {
            // Subtract claimed portion and keep decimal remainder
            gameState.goldAvailable = gameState.goldAvailable - claimableInt;
            gameState.balance = data.wallet.silver_balance;
            gameState.walletSilver = data.wallet.silver_balance;
            gameState.walletGold = data.wallet.gold_balance;
            gameState.poolVal = data.global_pool.total_fiat_in_bank;
            
            // Play effects
            playSound('trigger');
            spawnCoinsFloatingEffect();
            
            alert(data.message || 'Klaim koin emas berhasil!');
            
            // Update DOM
            document.getElementById('gold-available-val').textContent = '🪙 ' + gameState.goldAvailable.toFixed(2);
            document.getElementById('wallet-gold-val').textContent = Number(gameState.walletGold).toLocaleString('id-ID');
            document.getElementById('piticoin-pool-val').textContent = 'Rp ' + Number(gameState.poolVal).toLocaleString('id-ID');
            updateHUD();
        } else {
            alert(data.error || 'Gagal mengklaim koin emas.');
        }
    } catch (err) {
        console.error('Gagal mengklaim koin emas:', err);
        alert('Terjadi kesalahan koneksi server saat klaim.');
    } finally {
        btnClaim.disabled = false;
        btnClaim.textContent = 'KLAIM EMAS';
        btnClaim.classList.remove('disabled');
        updateClaimButtonState();
    }
}

function getCsrfToken() {
    const name = 'XSRF-TOKEN=';
    const decodedCookie = decodeURIComponent(document.cookie);
    const ca = decodedCookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return '';
}

function updateClaimButtonState() {
    const btnClaim = document.getElementById('btn-claim-gold');
    if (!btnClaim) return;
    
    if (Math.floor(gameState.goldAvailable) >= 1) {
        btnClaim.classList.remove('disabled');
    } else {
        btnClaim.classList.add('disabled');
    }
}

// --- INITIALIZATION ---
function init() {
    updateHUD();
    generateInitialBoard();
    renderBoard();
    setupEventListeners();
    fetchGameState();
}

// Generate a random symbol based on weights
function getRandomSymbol(colIndex, forFreeSpins = false) {
    // If in Free Spins, Reel 3 (colIndex === 2) has 100% Gold plated symbols (excluding Wild/Scatter)
    const isReel3FS = forFreeSpins && colIndex === 2;

    const totalWeight = Object.values(SYMBOLS).reduce((acc, sym) => acc + sym.weight, 0);
    let randomNum = Math.random() * totalWeight;
    
    let chosenSym = SYMBOLS.COIN; // fallback
    for (const sym of Object.values(SYMBOLS)) {
        randomNum -= sym.weight;
        if (randomNum <= 0) {
            chosenSym = sym;
            break;
        }
    }

    // Determine gold plating (only on Reels 2, 3, 4)
    let isGold = false;
    if (colIndex >= 1 && colIndex <= 3) {
        if (chosenSym.id !== 'WILD' && chosenSym.id !== 'SCATTER' && chosenSym.id !== 'LIGHTNING') {
            if (isReel3FS) {
                isGold = true;
            } else {
                isGold = Math.random() < 0.18; // 18% chance of gold plated
            }
        }
    }

    let multiplier = 0;
    if (chosenSym.id === 'LIGHTNING') {
        const r = Math.random();
        if (r < 0.003) multiplier = 1000;
        else if (r < 0.01) multiplier = 500;
        else if (r < 0.03) multiplier = 250;
        else if (r < 0.07) multiplier = 100;
        else if (r < 0.13) multiplier = 50;
        else if (r < 0.22) multiplier = 25;
        else if (r < 0.40) multiplier = 10;
        else if (r < 0.65) multiplier = 5;
        else multiplier = 2;
    }

    return { symbolId: chosenSym.id, isGold: isGold, multiplier: multiplier };
}

// Generate the initial screen board (static/no wins or random)
function generateInitialBoard() {
    gameState.board = [];
    for (let col = 0; col < 5; col++) {
        const colSymbols = [];
        const rows = REEL_ROWS[col];
        for (let row = 0; row < rows; row++) {
            // Generate symbols, avoid scatter blocks or high wins on load
            let cell = getRandomSymbol(col, false);
            // Don't let load state look too cluttered
            if (cell.symbolId === 'SCATTER') cell.symbolId = 'COIN';
            colSymbols.push(cell);
        }
        gameState.board.push(colSymbols);
    }
}

// Render board to DOM
function renderBoard(isCascade = false) {
    for (let col = 0; col < 5; col++) {
        const reelEl = document.getElementById(`reel-${col + 1}`);
        const windowEl = reelEl.querySelector('.reel-window');
        windowEl.innerHTML = ''; // clear
        
        const stripEl = document.createElement('div');
        stripEl.className = 'symbols-strip';
        
        const rows = REEL_ROWS[col];
        for (let row = 0; row < rows; row++) {
            const cell = gameState.board[col][row];
            const cellEl = createSymbolCellDOM(cell, col, row);
            
            // Add staggered falling animation on cascades for visible play rows (row >= 1)
            if (isCascade && row >= 1) {
                cellEl.classList.add('falling');
                // Calculate delay: bottom rows fall first, slight delay left-to-right
                const delay = ((rows - row) * 45) + (col * 30);
                cellEl.style.animationDelay = `${delay}ms`;
            }
            
            stripEl.appendChild(cellEl);
        }
        windowEl.appendChild(stripEl);
    }
}

// Create individual cell DOM element
function createSymbolCellDOM(cell, col, row) {
    const cellEl = document.createElement('div');
    cellEl.className = 'symbol-cell';
    cellEl.dataset.col = col;
    cellEl.dataset.row = row;
    
    const symInfo = SYMBOLS[cell.symbolId];
    cellEl.textContent = symInfo.char;
    
    if (cell.symbolId === 'WILD') {
        cellEl.classList.add('wild');
    } else if (cell.symbolId === 'SCATTER') {
        cellEl.classList.add('scatter');
    } else if (cell.symbolId === 'LIGHTNING') {
        cellEl.classList.add('lightning');
        if (cell.multiplier) {
            const badgeEl = document.createElement('div');
            badgeEl.className = 'lightning-badge';
            badgeEl.textContent = `x${cell.multiplier}`;
            cellEl.appendChild(badgeEl);
        }
    }
    
    if (cell.isGold) {
        cellEl.classList.add('gold-plated');
    }

    if (cell.isWildTransformation) {
        cellEl.classList.add('transform-to-wild');
    }
    
    return cellEl;
}

// Update UI values
function updateHUD() {
    const betSize = BET_SIZES[gameState.betSizeIndex];
    const betLevel = BET_LEVELS[gameState.betLevelIndex];
    const totalBet = betSize * betLevel * BASE_BET;
    
    elBalance.textContent = Math.floor(gameState.balance).toLocaleString('id-ID');
    elTotalBet.textContent = Math.floor(totalBet).toLocaleString('id-ID');
    elBetSize.textContent = betSize.toFixed(2);
    elBetLevel.textContent = betLevel;
    
    // Free Spins Indicator
    if (gameState.inFreeSpins) {
        elFreeSpinCounter.style.display = 'flex';
        elFreeSpinsLeft.textContent = gameState.freeSpinsRemaining;
        elSpinBtn.classList.add('disabled');
    } else {
        elFreeSpinCounter.style.display = 'none';
        elSpinBtn.classList.remove('disabled');
    }
    
    // Multiplier indicators active styling
    const multiBar = document.getElementById('multiplier-bar');
    const indicators = multiBar.querySelectorAll('.multi-indicator');
    
    // Base Multipliers: Normal vs Free Spins
    const multipliers = gameState.inFreeSpins ? [2, 4, 6, 10] : [1, 2, 3, 5];
    
    indicators.forEach((ind, index) => {
        const val = multipliers[index];
        ind.textContent = `x${val}`;
        ind.className = 'multi-indicator'; // clear active class
        
        let currentActiveMulti = multipliers[gameState.currentMultiplierStep - 1] || multipliers[3];
        
        if (val === currentActiveMulti) {
            ind.classList.add(gameState.inFreeSpins ? 'fs-active' : 'active');
        }
    });
}

// Audio Engine Synthesizer (Web Audio API)
function initAudio() {
    if (!audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
}

function playSound(type) {
    if (!gameState.soundEnabled) return;
    initAudio();
    if (audioCtx.state === 'suspended') {
        audioCtx.resume();
    }
    
    const now = audioCtx.currentTime;
    
    switch (type) {
        case 'spin': {
            // Rhythmic click sounds
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(80, now);
            osc.frequency.exponentialRampToValueAtTime(30, now + 0.3);
            
            gain.gain.setValueAtTime(0.08, now);
            gain.gain.linearRampToValueAtTime(0, now + 0.3);
            
            osc.start(now);
            osc.stop(now + 0.3);
            break;
        }
        case 'stop': {
            // Mechanical click
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(220, now);
            gain.gain.setValueAtTime(0.05, now);
            gain.gain.linearRampToValueAtTime(0, now + 0.05);
            
            osc.start(now);
            osc.stop(now + 0.05);
            break;
        }
        case 'win_small': {
            // Simple double-beep chime
            playNote(523.25, 0.1, now); // C5
            playNote(659.25, 0.2, now + 0.1); // E5
            break;
        }
        case 'win_big': {
            // Quick arpeggio
            const notes = [523.25, 659.25, 783.99, 1046.50]; // C5, E5, G5, C6
            notes.forEach((freq, idx) => {
                playNote(freq, 0.15, now + idx * 0.1);
            });
            break;
        }
        case 'explosion': {
            // Noise decay sound for exploding symbols
            const bufferSize = audioCtx.sampleRate * 0.25; // 0.25s duration
            const buffer = audioCtx.createBuffer(1, bufferSize, audioCtx.sampleRate);
            const data = buffer.getChannelData(0);
            for (let i = 0; i < bufferSize; i++) {
                data[i] = Math.random() * 2 - 1;
            }
            
            const noise = audioCtx.createBufferSource();
            noise.buffer = buffer;
            
            const filter = audioCtx.createBiquadFilter();
            filter.type = 'lowpass';
            filter.frequency.setValueAtTime(300, now);
            filter.frequency.exponentialRampToValueAtTime(50, now + 0.2);
            
            const gain = audioCtx.createGain();
            gain.gain.setValueAtTime(0.2, now);
            gain.gain.exponentialRampToValueAtTime(0.01, now + 0.25);
            
            noise.connect(filter);
            filter.connect(gain);
            gain.connect(audioCtx.destination);
            
            noise.start(now);
            noise.stop(now + 0.25);
            break;
        }
        case 'thunder': {
            // High energy sound + low rumble thunder
            const bufferSize = audioCtx.sampleRate * 0.8; // 0.8s duration
            const buffer = audioCtx.createBuffer(1, bufferSize, audioCtx.sampleRate);
            const data = buffer.getChannelData(0);
            for (let i = 0; i < bufferSize; i++) {
                data[i] = Math.random() * 2 - 1;
            }
            
            const noise = audioCtx.createBufferSource();
            noise.buffer = buffer;
            
            const filter = audioCtx.createBiquadFilter();
            filter.type = 'bandpass';
            filter.frequency.setValueAtTime(100, now);
            filter.Q.setValueAtTime(1.0, now);
            filter.frequency.exponentialRampToValueAtTime(30, now + 0.6);
            
            const gain = audioCtx.createGain();
            gain.gain.setValueAtTime(0.35, now);
            gain.gain.linearRampToValueAtTime(0.5, now + 0.05);
            gain.gain.exponentialRampToValueAtTime(0.01, now + 0.8);
            
            noise.connect(filter);
            filter.connect(gain);
            gain.connect(audioCtx.destination);
            
            noise.start(now);
            noise.stop(now + 0.8);

            // Synthesize high-frequency electric crackle
            const osc = audioCtx.createOscillator();
            const oscGain = audioCtx.createGain();
            osc.connect(oscGain);
            oscGain.connect(audioCtx.destination);
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(600, now);
            osc.frequency.setValueAtTime(300, now + 0.05);
            osc.frequency.setValueAtTime(900, now + 0.1);
            oscGain.gain.setValueAtTime(0.12, now);
            oscGain.gain.exponentialRampToValueAtTime(0.001, now + 0.25);
            osc.start(now);
            osc.stop(now + 0.25);
            break;
        }
        case 'scatter': {
            // Rising magical tone
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            
            osc.type = 'sine';
            osc.frequency.setValueAtTime(440, now);
            osc.frequency.exponentialRampToValueAtTime(880, now + 0.3);
            
            gain.gain.setValueAtTime(0.12, now);
            gain.gain.linearRampToValueAtTime(0, now + 0.3);
            
            osc.start(now);
            osc.stop(now + 0.3);
            break;
        }
        case 'trigger': {
            // Retro celebratory siren
            for (let i = 0; i < 4; i++) {
                playNote(880 + (i % 2) * 220, 0.1, now + i * 0.1);
            }
            break;
        }
    }
}

function playNote(freq, duration, time) {
    const osc = audioCtx.createOscillator();
    const gain = audioCtx.createGain();
    osc.connect(gain);
    gain.connect(audioCtx.destination);
    
    osc.type = 'sine';
    osc.frequency.setValueAtTime(freq, time);
    
    gain.gain.setValueAtTime(0.08, time);
    gain.gain.exponentialRampToValueAtTime(0.001, time + duration);
    
    osc.start(time);
    osc.stop(time + duration);
}

// --- EVENT LISTENERS ---
function setupEventListeners() {
    // Bet size controllers
    document.getElementById('btn-bet-size-minus').addEventListener('click', () => {
        if (gameState.isSpinning) return;
        if (gameState.betSizeIndex > 0) {
            gameState.betSizeIndex--;
            updateHUD();
            playSound('stop');
        }
    });
    document.getElementById('btn-bet-size-plus').addEventListener('click', () => {
        if (gameState.isSpinning) return;
        if (gameState.betSizeIndex < BET_SIZES.length - 1) {
            gameState.betSizeIndex++;
            updateHUD();
            playSound('stop');
        }
    });

    // Bet level controllers
    document.getElementById('btn-bet-level-minus').addEventListener('click', () => {
        if (gameState.isSpinning) return;
        if (gameState.betLevelIndex > 0) {
            gameState.betLevelIndex--;
            updateHUD();
            playSound('stop');
        }
    });
    document.getElementById('btn-bet-level-plus').addEventListener('click', () => {
        if (gameState.isSpinning) return;
        if (gameState.betLevelIndex < BET_LEVELS.length - 1) {
            gameState.betLevelIndex++;
            updateHUD();
            playSound('stop');
        }
    });

    // Turbo & Sound toggles
    elTurboBtn.addEventListener('click', () => {
        gameState.turboMode = !gameState.turboMode;
        elTurboBtn.classList.toggle('active', gameState.turboMode);
        playSound('stop');
    });

    elSoundBtn.addEventListener('click', () => {
        gameState.soundEnabled = !gameState.soundEnabled;
        elSoundIcon.textContent = gameState.soundEnabled ? '🔊' : '🔇';
        if (gameState.soundEnabled) {
            initAudio();
            playSound('stop');
        }
    });

    // Spin trigger
    elSpinBtn.addEventListener('click', () => {
        triggerNormalSpin();
    });

    // Claim Gold trigger
    const btnClaim = document.getElementById('btn-claim-gold');
    if (btnClaim) {
        btnClaim.addEventListener('click', () => {
            claimGold();
        });
    }

    // Keyboard Space trigger
    window.addEventListener('keydown', (e) => {
        if (e.code === 'Space') {
            e.preventDefault();
            // If already spinning and not cascading, skip spin animation
            if (gameState.isSpinning && !gameState.isCascading) {
                skipSpinAnimation();
            } else if (!gameState.isSpinning) {
                triggerNormalSpin();
            }
        }
    });

    // Modals
    elPaytableBtn.addEventListener('click', () => {
        elModalOverlay.classList.remove('hidden');
        playSound('stop');
    });

    elCloseModalBtn.addEventListener('click', () => {
        elModalOverlay.classList.add('hidden');
        playSound('stop');
    });

    // Auto spin modal triggers
    elAutoBtn.addEventListener('click', () => {
        if (gameState.isSpinning) {
            // Cancel current auto spins
            gameState.autoSpinsLeft = 0;
            updateAutoButtonState();
            playSound('stop');
            return;
        }
        elAutoModal.classList.remove('hidden');
        playSound('stop');
    });

    elCloseAutoBtn.addEventListener('click', () => {
        elAutoModal.classList.add('hidden');
        playSound('stop');
    });

    document.querySelectorAll('.auto-opt').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            const rounds = parseInt(btn.dataset.rounds);
            gameState.autoSpinsLeft = rounds;
            elAutoModal.classList.add('hidden');
            updateAutoButtonState();
            playSound('stop');
            triggerNormalSpin();
        });
    });
}

function updateAutoButtonState() {
    if (gameState.autoSpinsLeft > 0) {
        elAutoBtn.classList.add('active');
        elAutoText.textContent = gameState.autoSpinsLeft === 999 ? 'AUTO (∞)' : `AUTO (${gameState.autoSpinsLeft})`;
    } else {
        elAutoBtn.classList.remove('active');
        elAutoText.textContent = 'AUTO';
    }
}

// --- GAMEPLAY LOOPS & SLOTS ENGINE ---

function triggerNormalSpin() {
    if (gameState.isSpinning) return;
    
    // Check balance
    const betSize = BET_SIZES[gameState.betSizeIndex];
    const betLevel = BET_LEVELS[gameState.betLevelIndex];
    const totalBet = betSize * betLevel * BASE_BET;
    
    if (gameState.balance < totalBet && !gameState.inFreeSpins) {
        alert("Saldo koin perak Anda tidak cukup!");
        gameState.autoSpinsLeft = 0;
        updateAutoButtonState();
        return;
    }
    
    gameState.isSpinning = true;
    gameState.isCascading = false;
    gameState.currentMultiplierStep = 1;
    gameState.totalWinThisSpin = 0;
    
    if (!gameState.inFreeSpins) {
        gameState.balance -= totalBet;
        elWinAmount.textContent = "0.00";
    }

    updateHUD();
    
    // Animate reels spinning
    startSpinReelsAnimation();
}

let activeReelIntervals = [];
let reelTimeoutIds = [];

function startSpinReelsAnimation() {
    playSound('spin');
    
    const spinDurations = gameState.turboMode ? [150, 300, 450, 600, 750] : [500, 800, 1100, 1400, 1700];
    
    activeReelIntervals = [];
    reelTimeoutIds = [];
    
    for (let col = 0; col < 5; col++) {
        const reelEl = document.getElementById(`reel-${col + 1}`);
        const stripEl = reelEl.querySelector('.symbols-strip');
        
        stripEl.classList.add('spinning');
        
        // Populate with long strip of random symbols to simulate rolling
        let rollSymbols = [];
        const targetSymbolsCount = 30;
        for (let i = 0; i < targetSymbolsCount; i++) {
            rollSymbols.push(getRandomSymbol(col, gameState.inFreeSpins));
        }
        
        // Keep final actual destination symbols at the bottom of the strip
        const destSymbols = [];
        const rows = REEL_ROWS[col];
        for (let r = 0; r < rows; r++) {
            const symObj = getRandomSymbol(col, gameState.inFreeSpins);
            destSymbols.push(symObj);
        }
        
        // Store destination state in gameState
        for (let r = 0; r < rows; r++) {
            gameState.board[col][r] = destSymbols[r];
        }
        
        // Render large strip
        stripEl.innerHTML = '';
        rollSymbols.concat(destSymbols).forEach((sym) => {
            const cellEl = createSymbolCellDOM(sym, col, -1);
            stripEl.appendChild(cellEl);
        });
        
        // Animate translateY
        const cellPixels = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--cell-size')) || 80;
        const offset = - (rollSymbols.length * cellPixels) - (cellPixels * 0.65);
        stripEl.style.transform = `translateY(${offset}px)`;
        stripEl.style.transition = 'none';
        
        // Trigger reflow
        stripEl.offsetHeight;
        
        // Set transition animation
        stripEl.style.transition = `transform ${spinDurations[col]}ms cubic-bezier(0.12, 0.8, 0.15, 1)`;
        stripEl.style.transform = 'translateY(calc(var(--cell-size) * -0.65))';
        
        const timeoutId = setTimeout(() => {
            stopReelAnimation(col);
        }, spinDurations[col]);
        
        reelTimeoutIds.push(timeoutId);
    }
}

function stopReelAnimation(col) {
    const reelEl = document.getElementById(`reel-${col + 1}`);
    const stripEl = reelEl.querySelector('.symbols-strip');
    stripEl.classList.remove('spinning');
    playSound('stop');
    
    // Rerender specific reel with clean static state
    const windowEl = reelEl.querySelector('.reel-window');
    windowEl.innerHTML = '';
    
    const cleanStrip = document.createElement('div');
    cleanStrip.className = 'symbols-strip';
    
    const rows = REEL_ROWS[col];
    for (let r = 0; r < rows; r++) {
        const cell = gameState.board[col][r];
        const cellEl = createSymbolCellDOM(cell, col, r);
        cleanStrip.appendChild(cellEl);
    }
    windowEl.appendChild(cleanStrip);
    
    // Check if this was the last reel
    if (col === 4) {
        evaluateBoardState();
    }
}

function skipSpinAnimation() {
    // Clear timeouts
    reelTimeoutIds.forEach((id) => clearTimeout(id));
    reelTimeoutIds = [];
    
    // Stop all reels immediately
    for (let col = 0; col < 5; col++) {
        stopReelAnimation(col);
    }
}

// --- MATHEMATICAL MATH ENGINE (WAYS TO WIN) ---

function evaluateBoardState() {
    gameState.isCascading = true;
    
    // 1. Identify all winning combinations
    const wins = findWinningCombinations();
    
    // 2. Count Scatters for Free Spins (only on original spin start, not in subsequent cascades)
    const scatterCount = countScatterSymbols();
    
    if (wins.length > 0) {
        // We have a win! Show payouts and trigger cascading cascade loop
        processWinningCombinations(wins, scatterCount);
    } else {
        // No wins on this board state
        endCascadingCycle(scatterCount);
    }
}

// Count total scatters visible on current board (excluding peek row index 0)
function countScatterSymbols() {
    let count = 0;
    for (let col = 0; col < 5; col++) {
        for (let row = 1; row < REEL_ROWS[col]; row++) {
            if (gameState.board[col][row].symbolId === 'SCATTER') {
                count++;
            }
        }
    }
    return count;
}

// Search ways to win
function findWinningCombinations() {
    const winningCombinations = [];
    const board = gameState.board;
    
    // Get unique paytable symbols (excluding Wild, Scatter, and Lightning)
    const paySymbols = Object.keys(SYMBOLS).filter(key => key !== 'WILD' && key !== 'SCATTER' && key !== 'LIGHTNING');
    
    paySymbols.forEach((symId) => {
        // Check if symbol exists on Reel 1
        const reel1Cells = getCoordinatesOfSymbol(0, symId);
        if (reel1Cells.length === 0) return;
        
        // Find succession length
        let len = 1;
        const matchingCoords = [reel1Cells]; // Store matching coords per reel column
        
        for (let col = 1; col < 5; col++) {
            const colCells = getCoordinatesOfSymbol(col, symId);
            if (colCells.length > 0) {
                len++;
                matchingCoords.push(colCells);
            } else {
                break;
            }
        }
        
        // Mahjong Ways pays for 3, 4, or 5 consecutive matching symbols
        if (len >= 3) {
            // Count number of paths/ways
            let ways = 1;
            const allWinCells = [];
            
            matchingCoords.forEach((coords) => {
                ways *= coords.length;
                coords.forEach(coord => allWinCells.push(coord));
            });
            
            const basePayout = SYMBOLS[symId].payouts[len];
            winningCombinations.push({
                symbolId: symId,
                length: len,
                ways: ways,
                basePayout: basePayout,
                cells: allWinCells // Array of {col, row}
            });
        }
    });
    
    return winningCombinations;
}

// Returns coordinates of target symbol or WILD (excluding peek row index 0)
function getCoordinatesOfSymbol(col, targetSymbolId) {
    const coords = [];
    for (let row = 1; row < REEL_ROWS[col]; row++) {
        const symId = gameState.board[col][row].symbolId;
        if (symId === targetSymbolId || symId === 'WILD') {
            coords.push({ col: col, row: row });
        }
    }
    return coords;
}

// Process wins, trigger animation, compute payout
function processWinningCombinations(wins, initialScatterCount) {
    let payoutThisCascade = 0;
    
    const betSize = BET_SIZES[gameState.betSizeIndex];
    const betLevel = BET_LEVELS[gameState.betLevelIndex];
    
    // Highlight winning cells
    wins.forEach((win) => {
        const cashValue = win.basePayout * betSize * betLevel * win.ways;
        payoutThisCascade += cashValue;
        
        // Tag symbols for flashes/animations
        win.cells.forEach((coord) => {
            const cellEl = document.querySelector(`.symbol-cell[data-col="${coord.col}"][data-row="${coord.row}"]`);
            if (cellEl) cellEl.classList.add('winning');
        });
    });

    // Multiplier step logic
    const multipliers = gameState.inFreeSpins ? [2, 4, 6, 10] : [1, 2, 3, 5];
    const currentMulti = multipliers[gameState.currentMultiplierStep - 1] || multipliers[3];
    
    const finalCascadeWin = payoutThisCascade * currentMulti;
    gameState.totalWinThisSpin += finalCascadeWin;
    
    if (gameState.inFreeSpins) {
        gameState.freeSpinsWin += finalCascadeWin;
    }
    
    // Spawn win sound
    if (finalCascadeWin > 100) {
        playSound('win_big');
        spawnCoinsFloatingEffect();
    } else {
        playSound('win_small');
    }
    
    // Update win indicators
    elWinAmount.textContent = gameState.totalWinThisSpin.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
    // Check for Lightning Multiplier activation on wins (excluding peek row index 0)
    let foundLightning = false;
    for (let col = 0; col < 5; col++) {
        for (let row = 1; row < REEL_ROWS[col]; row++) {
            const cell = gameState.board[col][row];
            if (cell.symbolId === 'LIGHTNING') {
                if (cell.multiplier && !cell.used) {
                    cell.used = true; // Mark as collected so it won't be counted again in subsequent cascades
                    foundLightning = true;
                    gameState.collectedMultipliers.push(cell.multiplier);
                    // Flash the lightning cell
                    const cellEl = document.querySelector(`.symbol-cell[data-col="${col}"][data-row="${row}"]`);
                    if (cellEl) cellEl.classList.add('winning');
                }
            }
        }
    }
    
    if (foundLightning) {
        playSound('thunder');
        const boardEl = document.getElementById('reels-board');
        boardEl.classList.remove('lightning-flash');
        boardEl.offsetHeight; // trigger reflow
        boardEl.classList.add('lightning-flash');
        setTimeout(() => {
            boardEl.classList.remove('lightning-flash');
        }, 500);
    }

    // Tag gold plated symbols involved in win for Wild transformation
    gameState.pendingWildTransformations = [];
    
    wins.forEach((win) => {
        win.cells.forEach((coord) => {
            const cell = gameState.board[coord.col][coord.row];
            if (cell.isGold) {
                const key = `${coord.col},${coord.row}`;
                if (!gameState.pendingWildTransformations.includes(key)) {
                    gameState.pendingWildTransformations.push(key);
                }
            }
        });
    });

    // Set delay for explosions
    const delayTime = gameState.turboMode ? 400 : 700;
    setTimeout(() => {
        executeExplodeAndCascade(wins, initialScatterCount);
    }, delayTime);
}

// Generate animated floating coins on screen
function spawnCoinsFloatingEffect() {
    const container = document.getElementById('game-container');
    const particleCount = 20;
    for (let i = 0; i < particleCount; i++) {
        const coin = document.createElement('div');
        coin.className = 'coin-particle';
        coin.textContent = '🪙';
        coin.style.left = `${30 + Math.random() * 40}%`;
        coin.style.bottom = '20%';
        coin.style.setProperty('--x-offset', `${(Math.random() - 0.5) * 150}px`);
        
        container.appendChild(coin);
        
        // clean up
        setTimeout(() => {
            coin.remove();
        }, 800);
    }
}

// Explode winning cells, cascade symbols down, pull down replacements
function executeExplodeAndCascade(wins, initialScatterCount) {
    playSound('explosion');
    
    // Mark elements as exploding in DOM
    const winCoordinates = [];
    wins.forEach(w => w.cells.forEach(c => winCoordinates.push(`${c.col},${c.row}`)));
    
    winCoordinates.forEach((coordStr) => {
        const [col, row] = coordStr.split(',').map(Number);
        const cellEl = document.querySelector(`.symbol-cell[data-col="${col}"][data-row="${row}"]`);
        if (cellEl) {
            cellEl.classList.remove('winning');
            
            // Check if transforming to Wild
            if (gameState.pendingWildTransformations.includes(coordStr)) {
                cellEl.classList.add('transform-to-wild');
                cellEl.textContent = SYMBOLS.WILD.char;
                cellEl.className = 'symbol-cell wild transform-to-wild';
            } else {
                cellEl.classList.add('exploding');
            }
        }
    });
    
    // Set delay for grid shifting logic
    setTimeout(() => {
        shiftGridDown();
        
        // Increment Multiplier step for next cascade
        if (gameState.currentMultiplierStep < 4) {
            gameState.currentMultiplierStep++;
        }
        updateHUD();
        
        // Check next cascade state
        setTimeout(() => {
            evaluateBoardState();
        }, gameState.turboMode ? 250 : 450);
        
    }, 300);
}

// Cascading gravity math logic
function shiftGridDown() {
    const nextBoard = [];
    
    for (let col = 0; col < 5; col++) {
        const rows = REEL_ROWS[col];
        const nextCol = [];
        
        // Read cells from bottom (rows-1) to top (0)
        for (let row = rows - 1; row >= 0; row--) {
            const cell = gameState.board[col][row];
            const coordKey = `${col},${row}`;
            
            if (gameState.pendingWildTransformations.includes(coordKey)) {
                // Transform to wild instead of deleting
                nextCol.unshift({ symbolId: 'WILD', isGold: false, isWildTransformation: true });
            } else {
                const isWinning = isCellInWinList(col, row);
                if (!isWinning) {
                    nextCol.unshift(cell);
                }
            }
        }
        
        // Fill empty top cells with newly spawned symbols
        const missingCount = rows - nextCol.length;
        for (let i = 0; i < missingCount; i++) {
            nextCol.unshift(getRandomSymbol(col, gameState.inFreeSpins));
        }
        
        nextBoard.push(nextCol);
    }
    
    // Update memory
    gameState.board = nextBoard;
    gameState.pendingWildTransformations = [];
    
    // Re-render board cleanly with cascading gravity animations
    renderBoard(true);
}

function isCellInWinList(col, row) {
    const wins = findWinningCombinations();
    for (const w of wins) {
        for (const c of w.cells) {
            if (c.col === col && c.row === row) return true;
        }
    }
    return false;
}

// End of cascading cycle
function endCascadingCycle(scatterCount) {
    // 1. Apply collected lightning multipliers if any
    if (gameState.collectedMultipliers.length > 0) {
        const totalMult = gameState.collectedMultipliers.reduce((acc, m) => acc + m, 0);
        gameState.totalWinThisSpin *= totalMult;
        
        if (gameState.inFreeSpins) {
            // Adjust free spins total win with multiplied amount difference
            const diff = (gameState.totalWinThisSpin) - (gameState.totalWinThisSpin / totalMult);
            gameState.freeSpinsWin += diff;
        }

        // Show multiplier strike banner
        elNoticeText.textContent = `⚡ PETIR MENYAMBAR: x${totalMult} MULTIPLIER! ⚡`;
        elNoticeBanner.className = 'free-spins'; // use bright notice theme
        
        setTimeout(() => {
            elNoticeBanner.className = 'hidden';
            resolvePayoutAndLoop(scatterCount);
        }, 2000);
        
        gameState.collectedMultipliers = [];
    } else {
        resolvePayoutAndLoop(scatterCount);
    }
}

function resolvePayoutAndLoop(scatterCount) {
    // Convert perak winnings to gold (100 perak = 1 gold)
    const goldWon = gameState.totalWinThisSpin / 100;
    gameState.goldAvailable += goldWon;
    
    const elGoldAvail = document.getElementById('gold-available-val');
    if (elGoldAvail) elGoldAvail.textContent = '🪙 ' + gameState.goldAvailable.toFixed(2);
    updateClaimButtonState();

    updateHUD();
    elWinAmount.textContent = gameState.totalWinThisSpin.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // 1. Process Scatter Free Spin triggers (Must gather >= 3 scatter chests)
    if (scatterCount >= 3 && !gameState.inFreeSpins) {
        triggerFreeSpinsMode(scatterCount);
        return;
    }
    
    // If already in Free Spins, check for retriggers
    if (scatterCount >= 3 && gameState.inFreeSpins) {
        retriggerFreeSpins(scatterCount);
        return;
    }
    
    // 2. Loop Free Spins count
    if (gameState.inFreeSpins) {
        gameState.freeSpinsRemaining--;
        if (gameState.freeSpinsRemaining <= 0) {
            // Free spins ended
            endFreeSpinsMode();
            return;
        } else {
            // Spin next free spin automatically
            setTimeout(() => {
                triggerFreeSpinCycle();
            }, gameState.turboMode ? 800 : 1500);
            return;
        }
    }
    
    // 3. Loop Auto Spins
    if (gameState.autoSpinsLeft > 0) {
        gameState.autoSpinsLeft--;
        updateAutoButtonState();
        
        // Reset spin state so the next normal spin can be triggered
        gameState.isSpinning = false;
        
        if (gameState.autoSpinsLeft > 0) {
            setTimeout(() => {
                if (gameState.autoSpinsLeft > 0) triggerNormalSpin();
            }, gameState.turboMode ? 800 : 1500);
        }
    } else {
        gameState.isSpinning = false;
    }
}

// --- FREE SPINS FEATURES SYSTEM ---

function triggerFreeSpinsMode(scatterCount) {
    playSound('trigger');
    
    gameState.inFreeSpins = true;
    const spinsAwarded = 10 + (scatterCount - 3) * 2;
    gameState.freeSpinsTotal = spinsAwarded;
    gameState.freeSpinsRemaining = spinsAwarded;
    gameState.freeSpinsWin = 0;
    
    elNoticeText.textContent = `FREE SPINS DIPULAI: +${spinsAwarded} SPINS!`;
    elNoticeBanner.className = 'free-spins'; // remove hidden
    
    setTimeout(() => {
        elNoticeBanner.className = 'hidden';
        triggerFreeSpinCycle();
    }, 2500);
}

function retriggerFreeSpins(scatterCount) {
    playSound('trigger');
    const spinsAwarded = 10 + (scatterCount - 3) * 2;
    gameState.freeSpinsRemaining += spinsAwarded;
    gameState.freeSpinsTotal += spinsAwarded;
    
    elNoticeText.textContent = `RE-TRIGGER! +${spinsAwarded} FREE SPINS!`;
    elNoticeBanner.className = 'free-spins';
    
    setTimeout(() => {
        elNoticeBanner.className = 'hidden';
        triggerFreeSpinCycle();
    }, 2500);
}

function triggerFreeSpinCycle() {
    gameState.currentMultiplierStep = 1;
    gameState.totalWinThisSpin = 0;
    updateHUD();
    
    // Auto-spin reels
    startSpinReelsAnimation();
}

function endFreeSpinsMode() {
    playSound('trigger');
    
    const goldWonFS = gameState.freeSpinsWin / 100;
    elNoticeText.textContent = `FS BERHASIL: +${goldWonFS.toFixed(2)} EMAS!`;
    elNoticeBanner.className = ''; // remove hidden, default theme
    
    gameState.inFreeSpins = false;
    
    setTimeout(() => {
        elNoticeBanner.className = 'hidden';
        gameState.isSpinning = false;
        updateHUD();
        
        // Resume Auto Spins if any
        if (gameState.autoSpinsLeft > 0) {
            triggerNormalSpin();
        }
    }, 3000);
}

// Start Game
init();
