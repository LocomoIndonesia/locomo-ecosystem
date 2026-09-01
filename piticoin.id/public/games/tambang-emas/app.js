/**
 * Tambang Emas: Hammer Strike - Game Logic
 * Interactive 3x3 rock-breaking slot-like game with particle effects and Web Audio synth.
 */

// --- CONFIGURATION & STATE ---
const SYMBOLS = {
    DIAMOND: { id: 'DIAMOND', name: 'Berlian', char: '💎', multiplier: 50, weight: 3 },
    GOLD: { id: 'GOLD', name: 'Batangan Emas', char: '🏆', multiplier: 30, weight: 6 },
    PEARL: { id: 'PEARL', name: 'Mutiara', char: '🦪', multiplier: 20, weight: 9 },
    EMERALD: { id: 'EMERALD', name: 'Batu Zamrud', char: '🟢', multiplier: 10, weight: 13 },
    SAPPHIRE: { id: 'SAPPHIRE', name: 'Batu Safir', char: '🔵', multiplier: 5, weight: 17 },
    SILVER: { id: 'SILVER', name: 'Koin Perak', char: '🪙', multiplier: 2, weight: 22 },
    ROCK: { id: 'ROCK', name: 'Batu Biasa', char: '🪨', multiplier: 0, weight: 45 },
    BOMB: { id: 'BOMB', name: 'Bom Peledak', char: '💣', multiplier: 0, weight: 2.5 }
};

const BASE_BET = 20;
const BET_SIZES = [0.03, 0.05, 0.10, 0.20, 0.50, 0.90];
const BET_LEVELS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

let gameState = {
    balance: 10000.00,
    betSizeIndex: 2, // default 0.10
    betLevelIndex: 0, // default 1
    isStriking: false,
    soundEnabled: true,
    board: [] // Array of 9 symbols: { symbolId, isRevealed }
};

// Web Audio Context
let audioCtx = null;

// --- DOM ELEMENTS ---
const elBalance = document.getElementById('balance-val');
const elTotalBet = document.getElementById('total-bet-val');
const elWinAmount = document.getElementById('win-amount');
const elBetSize = document.getElementById('bet-size-val');
const elBetLevel = document.getElementById('bet-level-val');
const elAnnouncement = document.getElementById('announcement-banner');
const elAnnouncementText = document.getElementById('announcement-text');
const elHammer = document.getElementById('hammer-container');
const elGrid = document.getElementById('rock-grid');
const elStrikeBtn = document.getElementById('btn-strike');
const elSoundBtn = document.getElementById('btn-sound');
const elSoundIcon = document.getElementById('sound-icon');
const elInfoBtn = document.getElementById('btn-info');
const elModalOverlay = document.getElementById('modal-overlay');
const elCloseModalBtn = document.getElementById('btn-close-modal');

// --- INITIALIZATION ---
function init() {
    updateHUD();
    resetBoardState();
    setupEventListeners();
}

function resetBoardState() {
    gameState.board = [];
    const cells = document.querySelectorAll('.rock-cell');
    cells.forEach((cellEl, idx) => {
        // Reset visual state
        cellEl.className = 'rock-cell';
        const frontEl = cellEl.querySelector('.rock-front');
        const backEl = cellEl.querySelector('.rock-back');
        
        frontEl.textContent = '🪨';
        backEl.textContent = '';
        backEl.className = 'rock-back';
        
        gameState.board.push({ symbolId: 'ROCK', isRevealed: false });
    });
}

function updateHUD() {
    const betSize = BET_SIZES[gameState.betSizeIndex];
    const betLevel = BET_LEVELS[gameState.betLevelIndex];
    const totalBet = betSize * betLevel * BASE_BET;
    
    elBalance.textContent = gameState.balance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    elTotalBet.textContent = totalBet.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    elBetSize.textContent = betSize.toFixed(2);
    elBetLevel.textContent = betLevel;

    if (gameState.isStriking) {
        elStrikeBtn.classList.add('disabled');
    } else {
        elStrikeBtn.classList.remove('disabled');
    }
}

// --- AUDIO SYNTHESIS ENGINE ---
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
        case 'swing': {
            // Whoosh sound
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(150, now);
            osc.frequency.exponentialRampToValueAtTime(40, now + 0.3);
            
            gain.gain.setValueAtTime(0.15, now);
            gain.gain.linearRampToValueAtTime(0, now + 0.3);
            
            osc.start(now);
            osc.stop(now + 0.3);
            break;
        }
        case 'impact': {
            // Heavy metallic crash + rumble
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(90, now);
            osc.frequency.exponentialRampToValueAtTime(25, now + 0.45);
            
            gain.gain.setValueAtTime(0.4, now);
            gain.gain.exponentialRampToValueAtTime(0.01, now + 0.5);
            
            osc.start(now);
            osc.stop(now + 0.5);
            
            // Add a metallic ping
            const ping = audioCtx.createOscillator();
            const pingGain = audioCtx.createGain();
            ping.connect(pingGain);
            pingGain.connect(audioCtx.destination);
            ping.type = 'sine';
            ping.frequency.setValueAtTime(480, now);
            pingGain.gain.setValueAtTime(0.1, now);
            pingGain.gain.exponentialRampToValueAtTime(0.001, now + 0.15);
            ping.start(now);
            ping.stop(now + 0.15);
            break;
        }
        case 'crumble': {
            // White noise crackle ticks
            const bufferSize = audioCtx.sampleRate * 0.15;
            const buffer = audioCtx.createBuffer(1, bufferSize, audioCtx.sampleRate);
            const data = buffer.getChannelData(0);
            for (let i = 0; i < bufferSize; i++) {
                data[i] = Math.random() * 2 - 1;
            }
            
            const noise = audioCtx.createBufferSource();
            noise.buffer = buffer;
            
            const filter = audioCtx.createBiquadFilter();
            filter.type = 'bandpass';
            filter.frequency.setValueAtTime(400, now);
            
            const gain = audioCtx.createGain();
            gain.gain.setValueAtTime(0.08, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.15);
            
            noise.connect(filter);
            filter.connect(gain);
            gain.connect(audioCtx.destination);
            
            noise.start(now);
            noise.stop(now + 0.15);
            break;
        }
        case 'explosion': {
            // Massive bomb blast
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(120, now);
            osc.frequency.exponentialRampToValueAtTime(10, now + 0.8);
            
            gain.gain.setValueAtTime(0.5, now);
            gain.gain.linearRampToValueAtTime(0, now + 0.85);
            
            osc.start(now);
            osc.stop(now + 0.85);
            break;
        }
        case 'win': {
            // Golden metal chime chime
            playNote(587.33, 0.12, now); // D5
            playNote(659.25, 0.12, now + 0.1); // E5
            playNote(783.99, 0.12, now + 0.2); // G5
            playNote(880.00, 0.25, now + 0.3); // A5
            break;
        }
        case 'lose': {
            // Low disappointment sound
            playNote(196.00, 0.18, now); // G3
            playNote(146.83, 0.35, now + 0.18); // D3
            break;
        }
        case 'click': {
            playNote(440, 0.05, now);
            break;
        }
    }
}

function playNote(freq, duration, time) {
    const osc = audioCtx.createOscillator();
    const gain = audioCtx.createGain();
    osc.connect(gain);
    gain.connect(audioCtx.destination);
    
    osc.type = 'triangle';
    osc.frequency.setValueAtTime(freq, time);
    
    gain.gain.setValueAtTime(0.08, time);
    gain.gain.exponentialRampToValueAtTime(0.001, time + duration);
    
    osc.start(time);
    osc.stop(time + duration);
}

// --- EVENT LISTENERS ---
function setupEventListeners() {
    // Bet sizes adjusters
    document.getElementById('btn-bet-size-minus').addEventListener('click', () => {
        if (gameState.isStriking) return;
        if (gameState.betSizeIndex > 0) {
            gameState.betSizeIndex--;
            updateHUD();
            playSound('click');
        }
    });
    document.getElementById('btn-bet-size-plus').addEventListener('click', () => {
        if (gameState.isStriking) return;
        if (gameState.betSizeIndex < BET_SIZES.length - 1) {
            gameState.betSizeIndex++;
            updateHUD();
            playSound('click');
        }
    });

    // Bet levels adjusters
    document.getElementById('btn-bet-level-minus').addEventListener('click', () => {
        if (gameState.isStriking) return;
        if (gameState.betLevelIndex > 0) {
            gameState.betLevelIndex--;
            updateHUD();
            playSound('click');
        }
    });
    document.getElementById('btn-bet-level-plus').addEventListener('click', () => {
        if (gameState.isStriking) return;
        if (gameState.betLevelIndex < BET_LEVELS.length - 1) {
            gameState.betLevelIndex++;
            updateHUD();
            playSound('click');
        }
    });

    // Strike trigger
    elStrikeBtn.addEventListener('click', () => {
        triggerStrikeCycle();
    });

    window.addEventListener('keydown', (e) => {
        if (e.code === 'Space') {
            e.preventDefault();
            triggerStrikeCycle();
        }
    });

    // Sound toggle
    elSoundBtn.addEventListener('click', () => {
        gameState.soundEnabled = !gameState.soundEnabled;
        elSoundIcon.textContent = gameState.soundEnabled ? '🔊' : '🔇';
        if (gameState.soundEnabled) {
            initAudio();
            playSound('click');
        }
    });

    // Info modals
    elInfoBtn.addEventListener('click', () => {
        elModalOverlay.classList.remove('hidden');
        playSound('click');
    });

    elCloseModalBtn.addEventListener('click', () => {
        elModalOverlay.classList.add('hidden');
        playSound('click');
    });
}

// --- GAME LOGIC CYCLE ---

function getRandomSymbol() {
    const totalWeight = Object.values(SYMBOLS).reduce((acc, sym) => acc + sym.weight, 0);
    let r = Math.random() * totalWeight;
    
    let chosenSym = SYMBOLS.ROCK;
    for (const sym of Object.values(SYMBOLS)) {
        r -= sym.weight;
        if (r <= 0) {
            chosenSym = sym;
            break;
        }
    }
    return chosenSym;
}

function triggerStrikeCycle() {
    if (gameState.isStriking) return;
    
    // Check balance
    const betSize = BET_SIZES[gameState.betSizeIndex];
    const betLevel = BET_LEVELS[gameState.betLevelIndex];
    const totalBet = betSize * betLevel * BASE_BET;
    
    if (gameState.balance < totalBet) {
        alert("Saldo koin perak Anda tidak cukup!");
        return;
    }
    
    // Set status
    gameState.isStriking = true;
    gameState.balance -= totalBet;
    elWinAmount.textContent = "0.00";
    elAnnouncement.className = 'hidden';
    updateHUD();
    
    // Reset board visually & in memory
    resetBoardState();
    
    // 1. Prepare Swing: Lift hammer
    setTimeout(() => {
        playSound('swing');
        elHammer.className = 'ready';
        
        // 2. Smash down
        setTimeout(() => {
            elHammer.className = 'strike';
            
            // Impact moment!
            setTimeout(() => {
                executeImpactAndCrumble();
            }, 60);
            
        }, 350);
        
    }, 100);
}

function executeImpactAndCrumble() {
    playSound('impact');
    
    // Screen shake
    elGrid.className = 'shake';
    
    // Populate grid backend state
    const cells = document.querySelectorAll('.rock-cell');
    
    cells.forEach((cellEl, idx) => {
        const sym = getRandomSymbol();
        gameState.board[idx] = { symbolId: sym.id, isRevealed: true, multiplier: sym.multiplier };
        
        const frontEl = cellEl.querySelector('.rock-front');
        const backEl = cellEl.querySelector('.rock-back');
        
        // Populate front with cracking lines
        frontEl.textContent = '💥';
        
        // Populate back element
        backEl.textContent = sym.char;
        
        if (sym.id !== 'ROCK' && sym.id !== 'BOMB') {
            backEl.classList.add('gem-glow');
        } else if (sym.id === 'BOMB') {
            backEl.classList.add('bomb-struck');
        }

        // Trigger rock crumble shards
        setTimeout(() => {
            playSound('crumble');
            spawnRockShards(cellEl);
            cellEl.classList.add('shattered');
        }, idx * 60 + Math.random() * 40); // slightly staggered shatter
    });

    // Pull hammer back slowly
    setTimeout(() => {
        elHammer.className = 'rebound';
        elGrid.className = '';
        
        // Clean up hammer class state
        setTimeout(() => {
            elHammer.className = '';
        }, 300);
    }, 250);

    // Delay evaluation of results to wait for shatter animations
    setTimeout(() => {
        evaluateMineResults();
    }, 1100);
}

// Spawn flying rock shard particles
function spawnRockShards(cellEl) {
    const shardCount = 8;
    for (let i = 0; i < shardCount; i++) {
        const shard = document.createElement('div');
        shard.className = 'rock-shard';
        
        // Random offsets for fly path
        const xOffset = (Math.random() - 0.5) * 160;
        const yOffset = (Math.random() - 0.5) * 160;
        
        shard.style.setProperty('--x-offset', `${xOffset}px`);
        shard.style.setProperty('--y-offset', `${yOffset}px`);
        shard.style.left = '45%';
        shard.style.top = '45%';
        
        cellEl.appendChild(shard);
        
        // clean up
        setTimeout(() => {
            shard.remove();
        }, 600);
    }
}

// Evaluate wins (Scatter Pays)
function evaluateMineResults() {
    // 1. Check for bomb explosion
    const hasBomb = gameState.board.some(c => c.symbolId === 'BOMB');
    if (hasBomb) {
        triggerBombBlast();
        return;
    }
    
    processPayouts();
}

// Bomb explosion sequence
function triggerBombBlast() {
    playSound('explosion');
    
    elAnnouncementText.textContent = '💣 BOMB MELEDAK! MERUNTUHKAN GUNUNG EMAS!';
    elAnnouncement.className = 'bomb-blast';
    
    // Visual flash screen shake
    elGrid.classList.add('shake');
    
    const cells = document.querySelectorAll('.rock-cell');
    
    // Find all BOMB index
    gameState.board.forEach((cell, idx) => {
        if (cell.symbolId === 'BOMB') {
            const cellEl = cells[idx];
            spawnRockShards(cellEl); // extra shards
        }
    });

    // After 600ms, transform all ROCK (🪨) and BOMB (💣) cells into high value gems
    setTimeout(() => {
        elGrid.classList.remove('shake');
        
        const highValueSymbols = ['DIAMOND', 'GOLD', 'PEARL', 'EMERALD'];
        
        gameState.board.forEach((cell, idx) => {
            if (cell.symbolId === 'ROCK' || cell.symbolId === 'BOMB') {
                // Random high value
                const randIndex = Math.floor(Math.random() * highValueSymbols.length);
                const symId = highValueSymbols[randIndex];
                const sym = SYMBOLS[symId];
                
                gameState.board[idx] = { symbolId: symId, isRevealed: true, multiplier: sym.multiplier };
                
                const backEl = cells[idx].querySelector('.rock-back');
                backEl.textContent = sym.char;
                backEl.className = 'rock-back gem-glow';
                
                // Explode visually
                spawnRockShards(cells[idx]);
            }
        });
        
        // Evaluate winnings after transformation
        setTimeout(() => {
            elAnnouncement.className = 'hidden';
            processPayouts();
        }, 800);
        
    }, 600);
}

// Process scatter payouts
function processPayouts() {
    // Count symbols
    const counts = {};
    Object.keys(SYMBOLS).forEach(key => counts[key] = 0);
    
    gameState.board.forEach((cell) => {
        if (cell.symbolId !== 'ROCK' && cell.symbolId !== 'BOMB') {
            counts[cell.symbolId]++;
        }
    });
    
    let totalMultiplier = 0;
    const wins = [];
    
    Object.keys(counts).forEach((symId) => {
        const count = counts[symId];
        if (count >= 3) {
            const multiplier = SYMBOLS[symId].multiplier * (count - 2);
            totalMultiplier += multiplier;
            wins.push({ name: SYMBOLS[symId].name, count: count, multiplier: multiplier });
        }
    });
    
    const betSize = BET_SIZES[gameState.betSizeIndex];
    const betLevel = BET_LEVELS[gameState.betLevelIndex];
    const totalBet = betSize * betLevel * BASE_BET;
    
    const winAmount = totalMultiplier * totalBet;
    
    if (winAmount > 0) {
        playSound('win');
        gameState.balance += winAmount;
        elWinAmount.textContent = winAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        
        // Generate floating coins
        spawnFloatingCoins();
        
        // Update banner text
        const winListText = wins.map(w => `${w.count}x ${w.name}`).join(', ');
        elAnnouncementText.textContent = `🏆 MENANG: ${winListText}! (+x${totalMultiplier})`;
        elAnnouncement.className = ''; // remove hidden
        
    } else {
        playSound('lose');
        elAnnouncementText.textContent = '❌ BATU BIASA! COBA PUKUL LAGI!';
        elAnnouncement.className = '';
    }
    
    gameState.isStriking = false;
    updateHUD();
}

function spawnFloatingCoins() {
    const container = document.getElementById('game-container');
    for (let i = 0; i < 15; i++) {
        const coin = document.createElement('div');
        coin.className = 'coin-particle';
        coin.textContent = '🪙';
        coin.style.left = `${35 + Math.random() * 30}%`;
        coin.style.bottom = '20%';
        coin.style.setProperty('--x-offset', `${(Math.random() - 0.5) * 120}px`);
        
        container.appendChild(coin);
        setTimeout(() => coin.remove(), 800);
    }
}

// Start Game
init();
