/* Engine (Deterministic RNG Simulation) - Slot Kartu KOA (Ceki) - 5x5 Grid Version */

const KOAEngine = {
    // Local Economic Simulation State (Dynamic fallback config)
    liquidityPool: 125000, 
    userSilver: 15000,    
    userGoldQuota: 0,
    userGoldVault: 0, 
    maxWinCap: 43750, // 35% of liquidityPool

    // Conversion rate: 1 Gold = 100 Silver
    goldToSilverRate: 100,

    // Free Spins State
    freeSpinsLeft: 0,
    freeSpinsTotal: 12,
    freeSpinsActive: false,
    freeSpinsWinAccumulated: 0, // In Silver equivalent

    // Dynamic Multipliers & Card Settings
    cardMultipliers: {
        200: 0.5, // Wild (Mandan)
        // High Tier (21 - 30)
        21: 0.3, 22: 0.3, 23: 0.3, 24: 0.3, 25: 0.3,
        26: 0.3, 27: 0.3, 28: 0.3, 29: 0.3, 30: 0.3,
        // Medium Tier (11 - 20)
        11: 0.2, 12: 0.2, 13: 0.2, 14: 0.2, 15: 0.2,
        16: 0.2, 17: 0.2, 18: 0.2, 19: 0.2, 20: 0.2,
        // Low Tier (1 - 10)
        1: 0.1, 2: 0.1, 3: 0.1, 4: 0.1, 5: 0.1,
        6: 0.1, 7: 0.1, 8: 0.1, 9: 0.1, 10: 0.1
    },
    multipliersNormal: [1, 2, 4, 5],
    multipliersFreeSpins: [2, 4, 6, 10],

    getCardMultiplier(cardId) {
        if (this.cardMultipliers[cardId] !== undefined) {
            return this.cardMultipliers[cardId];
        }
        return 0;
    },

    updateWinCap() {
        this.maxWinCap = Math.floor(this.liquidityPool * 0.35);
    },

    /**
     * Finds matching clusters in the 5x5 grid using DFS/Flood-fill.
     * @param {Array} grid - Array of 25 card IDs
     * @returns {Object} - Matched indices and detailed match info
     */
    findMatches(grid) {
        const matchedIndices = new Set();
        const detailedMatches = [];

        // Check regular cards (1 to 30)
        for (let cardType = 1; cardType <= 30; cardType++) {
            const visited = new Array(25).fill(false);

            for (let i = 0; i < 25; i++) {
                const cell = grid[i];
                if ((cell === cardType || cell === 200) && !visited[i]) {
                    // Start BFS/DFS
                    const cluster = [];
                    const queue = [i];
                    visited[i] = true;

                    while (queue.length > 0) {
                        const curr = queue.shift();
                        cluster.push(curr);

                        const r = Math.floor(curr / 5);
                        const c = curr % 5;

                        // Check 4-way neighbors
                        const neighbors = [];
                        if (r > 0) neighbors.push((r - 1) * 5 + c);
                        if (r < 4) neighbors.push((r + 1) * 5 + c);
                        if (c > 0) neighbors.push(r * 5 + c - 1);
                        if (c < 4) neighbors.push(r * 5 + c + 1);

                        for (const n of neighbors) {
                            const nVal = grid[n];
                            if ((nVal === cardType || nVal === 200) && !visited[n]) {
                                visited[n] = true;
                                queue.push(n);
                            }
                        }
                    }

                    // Must have size >= 3 and contain at least one non-Wild card
                    if (cluster.length >= 3) {
                        const hasRegularCard = cluster.some(idx => grid[idx] === cardType);
                        if (hasRegularCard) {
                            cluster.forEach(idx => matchedIndices.add(idx));
                            detailedMatches.push({
                                cardType: cardType,
                                indices: cluster
                            });
                        }
                    }
                }
            }
        }

        // Check for pure Wild matches (3 or more adjacent Wilds)
        const wildVisited = new Array(25).fill(false);
        for (let i = 0; i < 25; i++) {
            if (grid[i] === 200 && !wildVisited[i]) {
                const cluster = [];
                const queue = [i];
                wildVisited[i] = true;

                while (queue.length > 0) {
                    const curr = queue.shift();
                    cluster.push(curr);

                    const r = Math.floor(curr / 5);
                    const c = curr % 5;

                    const neighbors = [];
                    if (r > 0) neighbors.push((r - 1) * 5 + c);
                    if (r < 4) neighbors.push((r + 1) * 5 + c);
                    if (c > 0) neighbors.push(r * 5 + c - 1);
                    if (c < 4) neighbors.push(r * 5 + c + 1);

                    for (const n of neighbors) {
                        if (grid[n] === 200 && !wildVisited[n]) {
                            wildVisited[n] = true;
                            queue.push(n);
                        }
                    }
                }

                if (cluster.length >= 3) {
                    cluster.forEach(idx => matchedIndices.add(idx));
                    detailedMatches.push({
                        cardType: 200,
                        indices: cluster
                    });
                }
            }
        }

        return {
            matchedIndices: Array.from(matchedIndices),
            detailedMatches: detailedMatches
        };
    },

    /**
     * Generates a 5x5 grid with NO matches present.
     */
    generateNoWinGrid() {
        const grid = new Array(25);
        for (let r = 0; r < 5; r++) {
            for (let c = 0; c < 5; c++) {
                const index = r * 5 + c;
                let cardId;
                let attempts = 0;
                do {
                    cardId = Math.floor(Math.random() * 30) + 1;
                    attempts++;
                } while (
                    ((c > 0 && grid[index - 1] === cardId) || (r > 0 && grid[index - 5] === cardId)) && 
                    attempts < 100
                );
                grid[index] = cardId;
            }
        }
        return grid;
    },

    /**
     * Simulates cascading results starting from an initial grid (5x5 version)
     */
    simulateCascade(initialGrid, betAmount, isFreeSpin) {
        const grids = [JSON.parse(JSON.stringify(initialGrid))];
        const matches = [];
        const payouts = [];
        const multipliers = [];
        let currentGrid = JSON.parse(JSON.stringify(initialGrid));
        let step = 0;
        let totalWin = 0;
        let injectScatterNext = false;

        const activeSubDeck = [];
        while (activeSubDeck.length < 10) {
            const id = Math.floor(Math.random() * 30) + 1;
            if (!activeSubDeck.includes(id)) activeSubDeck.push(id);
        }

        const getNewCardId = () => {
            const rng = Math.random();
            if (rng < 0.02) return 200; // Reduced to 2% chance of natural Wild spawn
            return activeSubDeck[Math.floor(Math.random() * activeSubDeck.length)];
        };

        while (step < 10) { // Limit to 10 cascade levels max
            const matchInfo = this.findMatches(currentGrid);
            if (matchInfo.matchedIndices.length === 0) {
                break;
            }

            // Determine cascading multipliers from normal multipliers: [1, 2, 4, 5], Free spins: [2, 4, 6, 10]
            const multScale = isFreeSpin ? this.multipliersFreeSpins : this.multipliersNormal;
            const mult = multScale[Math.min(step, multScale.length - 1)];

            // Calculate payout
            let stepWin = 0;
            for (const match of matchInfo.detailedMatches) {
                const cardMult = this.getCardMultiplier(match.cardType);
                stepWin += betAmount * cardMult * match.indices.length * mult;
            }

            payouts.push(stepWin);
            multipliers.push(mult);
            matches.push(matchInfo);
            totalWin += stepWin;

            // Trigger drop Scatter: Pecah ke-3 (step === 2) AND Pecah ke-4 (step === 3)
            if (step === 2 || step === 3) {
                injectScatterNext = true;
            }

            const nextGrid = JSON.parse(JSON.stringify(currentGrid));
            const handIndices = [5, 9, 11, 13, 17, 21, 22, 23, 12, 16, 18];

            // Mark matched cells: Gold-framed cards have a 30% chance to transform to WILD (200) unless on column 0 (leftmost)
            matchInfo.matchedIndices.forEach(idx => {
                const col = idx % 5;
                const isHand = handIndices.includes(idx);
                if (isHand && col > 0 && Math.random() < 0.30) {
                    nextGrid[idx] = 200; // Mahjong Ways style transformation into WILD!
                } else {
                    nextGrid[idx] = null; // Standard break & clear
                }
            });

            // Slide cards down for 5x5 structure
            for (let c = 0; c < 5; c++) {
                const colCards = [];
                for (let r = 4; r >= 0; r--) {
                    const idx = r * 5 + c;
                    if (nextGrid[idx] !== null) {
                        colCards.push(nextGrid[idx]);
                    }
                }

                while (colCards.length < 5) {
                    if (injectScatterNext) {
                        colCards.push(100); // Inject Scatter
                        injectScatterNext = false;
                    } else {
                        colCards.push(getNewCardId());
                    }
                }

                colCards.reverse();
                for (let r = 0; r < 5; r++) {
                    nextGrid[r * 5 + c] = colCards[r];
                }
            }

            grids.push(nextGrid);
            currentGrid = nextGrid;
            step++;
        }

        return {
            grids,
            matches,
            payouts,
            multipliers,
            totalWin
        };
    },

    processSpin(betAmount) {
        this.updateWinCap();
        const isFreeSpin = this.freeSpinsActive && this.freeSpinsLeft > 0;

        if (!isFreeSpin) {
            if (this.userSilver < betAmount) {
                return {
                    error: true,
                    message: "Saldo Perak Anda tidak mencukupi."
                };
            }
            this.userSilver -= betAmount;
        } else {
            this.freeSpinsLeft--;
        }

        let selectedRound = null;

        if (window.forceFreeSpinsTrigger) {
            window.forceFreeSpinsTrigger = false;
            const initialGrid = this.generateWinningGrid(true);
            let scatterIndices = [];
            while (scatterIndices.length < 3) {
                const idx = Math.floor(Math.random() * 25);
                if (!scatterIndices.includes(idx)) {
                    scatterIndices.push(idx);
                    initialGrid[idx] = 100;
                }
            }
            selectedRound = this.simulateCascade(initialGrid, betAmount, isFreeSpin);
            selectedRound.scattersCount = 3;
            selectedRound.triggerCeki = true;
            selectedRound.triggerFreeSpins = true;
        } else {
            let attempts = 0;
            const maxAttempts = 30;

            while (attempts < maxAttempts) {
                let initialGrid;
                const winRoll = Math.random();
                
                if (isFreeSpin) {
                    initialGrid = (winRoll < 0.60) ? this.generateWinningGrid(false) : this.generateNoWinGrid();
                } else if (winRoll < 0.60) {
                    // 60% chance of a clean non-winning grid
                    initialGrid = this.generateNoWinGrid();
                } else {
                    // 40% chance of starting with some matches
                    initialGrid = this.generateWinningGrid(false);
                }

                const result = this.simulateCascade(initialGrid, betAmount, isFreeSpin);
                const maxWinInSilver = this.maxWinCap * this.goldToSilverRate;

                if (result.totalWin <= maxWinInSilver) {
                    selectedRound = result;
                    const finalGrid = result.grids[result.grids.length - 1];
                    const scatterCount = finalGrid.filter(cell => cell === 100).length;

                    selectedRound.scattersCount = scatterCount;
                    selectedRound.triggerCeki = (scatterCount >= 2);
                    selectedRound.triggerFreeSpins = (scatterCount >= 3);
                    break;
                }
                attempts++;
            }
        }

        if (!selectedRound) {
            const fallbackGrid = this.generateNoWinGrid();
            selectedRound = {
                grids: [fallbackGrid],
                matches: [],
                payouts: [],
                multipliers: [],
                totalWin: 0,
                scattersCount: 0,
                triggerCeki: false,
                triggerFreeSpins: false
            };
        }

        // Apply balances: bet and win convert to Gold Quota
        let goldWin = selectedRound.totalWin / this.goldToSilverRate;
        let silverWin = 0; // Winnings are not paid in Silver

        if (!isFreeSpin) {
            this.userGoldQuota -= (betAmount / this.goldToSilverRate);
        }
        this.userGoldQuota += goldWin;
        this.userGoldQuota = Math.max(0.0, this.userGoldQuota);

        if (isFreeSpin) {
            this.freeSpinsWinAccumulated += selectedRound.totalWin;
            const maxWinInSilver = this.maxWinCap * this.goldToSilverRate;
            if (this.freeSpinsWinAccumulated >= maxWinInSilver) {
                selectedRound.maxWinReached = true;
                this.freeSpinsLeft = 0;
                this.freeSpinsActive = false;
            }
        }

        // Adjust liquidity pool
        if (!isFreeSpin) {
            this.liquidityPool += ((betAmount - selectedRound.totalWin) / this.goldToSilverRate);
        } else {
            this.liquidityPool -= goldWin;
        }
        
        if (this.liquidityPool < 10000) this.liquidityPool = 10000;
        this.updateWinCap();

        if (selectedRound.triggerFreeSpins && !isFreeSpin) {
            this.freeSpinsActive = true;
            this.freeSpinsLeft = 12;
            this.freeSpinsWinAccumulated = 0;
        }

        return {
            success: true,
            isFreeSpin,
            freeSpinsLeft: this.freeSpinsLeft,
            grids: selectedRound.grids,
            matches: selectedRound.matches,
            payouts: selectedRound.payouts,
            multipliers: selectedRound.multipliers,
            totalWin: selectedRound.totalWin,
            goldWin: goldWin,
            silverWin: silverWin,
            triggerCeki: selectedRound.triggerCeki,
            triggerFreeSpins: selectedRound.triggerFreeSpins,
            maxWinReached: selectedRound.maxWinReached || false,
            userSilver: this.userSilver,
            liquidityPool: Math.floor(this.liquidityPool),
            userGoldQuota: this.userGoldQuota,
            userGoldVault: this.userGoldVault
        };
    },

    generateWinningGrid(forceScatters) {
        const grid = this.generateNoWinGrid();

        if (forceScatters) {
            const indices = [];
            while (indices.length < 3) {
                const idx = Math.floor(Math.random() * 25);
                if (!indices.includes(idx)) {
                    indices.push(idx);
                    grid[idx] = 100;
                }
            }
        } else {
            const matchCardType = Math.floor(Math.random() * 30) + 1;
            const startPos = Math.floor(Math.random() * 3); // 0..2
            const row = Math.floor(Math.random() * 5);
            grid[row * 5 + startPos] = matchCardType;
            grid[row * 5 + startPos + 1] = matchCardType;
            grid[row * 5 + startPos + 2] = matchCardType;

            if (Math.random() < 0.15) {
                const wildIdx = row * 5 + startPos + (Math.random() < 0.5 ? -1 : 3);
                if (wildIdx >= 0 && wildIdx < 25) {
                    grid[wildIdx] = 200;
                }
            }
        }
        return grid;
    }
};

window.KOAEngine = KOAEngine;
