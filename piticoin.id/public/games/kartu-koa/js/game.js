/* PixiJS Gamification Controller - KOA Slot - 5x5 Grid with Peeking Row & Unified Header */

const KOAGame = {
    // 30 Cards in traditional Minangkabau KOA deck
    cardsData: [
        { id: 1, name: "Angsa" },
        { id: 2, name: "Bejana" },
        { id: 3, name: "Bendera" },
        { id: 4, name: "Bulan" },
        { id: 5, name: "Bunga" },
        { id: 6, name: "Burung" },
        { id: 7, name: "Gajah" },
        { id: 8, name: "Gunung" },
        { id: 9, name: "Harimau" },
        { id: 10, name: "Ikan" },
        { id: 11, name: "Kalajengking" },
        { id: 12, name: "Kapal" },
        { id: 13, name: "Kitab" },
        { id: 14, name: "Kuda" },
        { id: 15, name: "Kunci" },
        { id: 16, name: "Kurakura" },
        { id: 17, name: "Lonceng" },
        { id: 18, name: "Mahkota" },
        { id: 19, name: "Matahari" },
        { id: 20, name: "Merak" },
        { id: 21, name: "Naga" },
        { id: 22, name: "Panah" },
        { id: 23, name: "Payung" },
        { id: 24, name: "Pedang" },
        { id: 25, name: "Peti" },
        { id: 26, name: "Pohon" },
        { id: 27, name: "Rumah" },
        { id: 28, name: "Tikus" },
        { id: 29, name: "Tombak" },
        { id: 30, name: "Ular" }
    ],

    // PixiJS Application variables
    app: null,
    cardSprites: [],
    slotContainers: [],
    lockOverlays: [],
    multiplierSprites: [],
    gridContainer: null,
    gridCardsContainer: null,
    chestSprite: null,
    
    // Grid settings (440x652.5 Canvas: 180px top for Header, 60px left for Multipliers, 380px for 5x5 card grid)
    canvasWidth: 440,
    canvasHeight: 652.5,
    cellW: 76,  // 380 / 5
    cellH: 90,  // Taller vertical cards

    // V-Shape Hand pattern indices in 5x5 grid (shifted by 5 for 30 slot items mapping)
    handIndices: [5, 9, 11, 13, 17, 21, 22, 23, 12, 16, 18],
    currentPeekingRow: [],

    async init() {
        this.btnSpin = document.getElementById("btn-spin");
        this.fsIndicator = document.getElementById("fs-indicator");
        this.fsCurrent = document.getElementById("fs-current");
        this.containerEl = document.getElementById("game-canvas-container");
        this.chestEl = document.getElementById("chest-box");

        if (!this.containerEl) return;

        // Initialize Audio context on click
        this.containerEl.addEventListener("click", () => {
            if (window.KOAAudio) window.KOAAudio.resume();
        });

        // 1. Initialize PixiJS Application
        this.app = new PIXI.Application({
            width: this.canvasWidth,
            height: this.canvasHeight,
            backgroundAlpha: 0, 
            resolution: window.devicePixelRatio || 1,
            autoDensity: true
        });

        this.containerEl.appendChild(this.app.view);

        // 2. Load Atlas textures
        if (window.KOAAtlas) {
            await window.KOAAtlas.load();
        }

        // 3. Setup Board and Multipliers
        this.setupBoard();
        this.setupGameListeners();
    },

    setupBoard() {
        this.gridContainer = new PIXI.Container();
        this.app.stage.addChild(this.gridContainer);

        // Setup Header components from texture atlas
        this.setupHeader();

        // Cards grid container (masked to clip peeking cards at grid top boundary)
        this.gridCardsContainer = new PIXI.Container();
        this.gridContainer.addChild(this.gridCardsContainer);

        // Viewport mask for the 5x5 grid (clips anything above y = 180px grid boundary)
        const mask = new PIXI.Graphics();
        mask.beginFill(0xffffff);
        mask.drawRect(60, 180, 380, 472.5);
        mask.endFill();
        this.gridCardsContainer.mask = mask;
        this.gridContainer.addChild(mask);

        // Setup Multipliers vertical indicators in canvas (positioned on the left column)
        this.setupMultipliers();

        // Render 30 slots (Row -1 is indices 0..4 (peeking row), Rows 0..4 are indices 5..29 (active playing grid))
        for (let i = 0; i < 30; i++) {
            const row = Math.floor(i / 5) - 1; // Row ranges from -1 to 4
            const col = i % 5;

            const slotGraphics = new PIXI.Graphics();
            
            // Only draw background border graphics for active rows (row >= 0)
            if (row >= 0) {
                const activeIndex = row * 5 + col;
                const isHand = this.handIndices.includes(activeIndex);

                slotGraphics.beginFill(0x0f1118, 0.45);
                slotGraphics.lineStyle(1.0, 0x1d2130, 0.5); // Subtle dark outline, no gold
                slotGraphics.drawRoundedRect(3, 3, this.cellW - 6, this.cellH - 6, 6);
                slotGraphics.endFill();
            }

            slotGraphics.x = 60 + col * this.cellW; // Shifted right by 60px
            slotGraphics.y = 180 + 22.5 + row * this.cellH; // Shifted down by 180px header offset!

            this.gridCardsContainer.addChild(slotGraphics);
            this.slotContainers[i] = slotGraphics;

            // Setup Lock Overlays for active cells (mapped to 0..24)
            if (row >= 0) {
                const lockSprite = new PIXI.Sprite(PIXI.Texture.EMPTY);
                lockSprite.width = this.cellW - 8;
                lockSprite.height = this.cellH - 8;
                lockSprite.x = 4;
                lockSprite.y = 4;
                lockSprite.alpha = 0;
                slotGraphics.addChild(lockSprite);
                this.lockOverlays[row * 5 + col] = lockSprite;
            }
        }

        // Initial board state
        const initialGrid = Array.from({ length: 30 }, () => Math.floor(Math.random() * 30) + 1);
        this.renderGridState(initialGrid);
    },

    setupHeader() {
        // We temporarily remove the wrong atlas element sprites (element_0, etc.)
        // because they were rendering the entire spritesheet as the background.
        // We will wait for the user to define the exact element IDs from inspector.html
        
        // 4. Draw transparent chest box on the right side for click events until we have the real sprite
        const chestGraphics = new PIXI.Graphics();
        chestGraphics.beginFill(0x000000, 0); // Transparent
        chestGraphics.drawRect(0, 0, 75, 65);
        chestGraphics.endFill();
        chestGraphics.x = 345;
        chestGraphics.y = 35;
        chestGraphics.interactive = true;
        chestGraphics.cursor = 'pointer';
        chestGraphics.on('pointerdown', () => {
            if (window.KOAUI) window.KOAUI.showClaimGoldModal();
        });
        this.gridContainer.addChild(chestGraphics);
        this.chestSprite = chestGraphics;
    },

    setupMultipliers() {
        const engine = window.KOAEngine;
        const activeVals = (engine && engine.freeSpinsActive) ? [2, 4, 6, 10] : [1, 2, 4, 5];
        const yPositions = [587.5, 497.5, 407.5, 317.5]; // Shifted down by 180px

        for (let i = 0; i < 4; i++) {
            const container = new PIXI.Container();
            container.x = 30; // Centered inside the 60px multiplier column
            container.y = yPositions[i];

            const bg = new PIXI.Graphics();
            bg.beginFill(0x1e0505);
            bg.lineStyle(2, 0x721c1c);
            bg.drawCircle(0, 0, 22);
            bg.endFill();
            container.addChild(bg);
            container.bg = bg;

            const style = new PIXI.TextStyle({
                fontFamily: 'Outfit',
                fontSize: 15,
                fontWeight: '800',
                fill: '#ffd8a8'
            });
            const text = new PIXI.Text(`${activeVals[i]}x`, style);
            text.anchor.set(0.5);
            container.addChild(text);
            container.text = text;

            this.gridContainer.addChild(container);
            this.multiplierSprites[i] = container;
        }
    },

    createCardSprite(cardId, index) {
        const texture = window.KOAAtlas ? window.KOAAtlas.getCardTexture(cardId) : PIXI.Texture.EMPTY;
        const sprite = new PIXI.Sprite(texture);
        
        sprite.anchor.set(0.5);
        sprite.x = this.cellW / 2;
        sprite.y = this.cellH / 2;

        const padding = 10;
        const targetW = this.cellW - padding;
        const targetH = this.cellH - padding;
        const scale = Math.min(targetW / sprite.texture.width, targetH / sprite.texture.height);
        
        sprite.scale.set(scale);
        sprite.cardId = cardId;

        // Apply alpha
        if (index < 5) {
            sprite.alpha = 0.5;
        } else {
            const activeIndex = index - 5;
            const isHand = this.handIndices.includes(activeIndex);
            sprite.alpha = isHand ? 1.0 : 0.45;
        }

        return sprite;
    },

    createCardSprite(cardId, index) {
        const texture = window.KOAAtlas ? window.KOAAtlas.getCardTexture(cardId) : PIXI.Texture.EMPTY;
        const sprite = new PIXI.Sprite(texture);
        
        sprite.width = this.cellW - 8;
        sprite.height = this.cellH - 8;
        sprite.x = 4;
        sprite.y = 4;

        if (index < 5) {
            sprite.alpha = 0.6; // Peeking row
        } else {
            // ALL cards in playing grid are fully bright (1.0)! No dimming!
            sprite.alpha = 1.0;
        }

        // Apply special visual effects for high-value cards
        this.applyCardSpecialEffects(sprite, cardId);

        return sprite;
    },

    applyCardSpecialEffects(sprite, cardId) {
        if (!sprite || !sprite.parent) return;

        // KOA Card (100) - Golden Aura Glow
        if (cardId === 100) {
            const aura = new PIXI.Graphics();
            aura.lineStyle(2.5, 0xffd700, 0.9);
            aura.drawRoundedRect(-2, -2, sprite.width + 4, sprite.height + 4, 8);
            sprite.addChild(aura);

            // Pulsating glow animation
            let t = 0;
            const pulse = (delta) => {
                t += delta * 0.08;
                aura.alpha = 0.5 + Math.sin(t) * 0.4;
            };
            this.app.ticker.add(pulse);
        }
        // Wild Card (200) - Fiery Crimson Highlight
        else if (cardId === 200) {
            const wildBorder = new PIXI.Graphics();
            wildBorder.lineStyle(2.5, 0xff3838, 0.95);
            wildBorder.beginFill(0xff3838, 0.2);
            wildBorder.drawRoundedRect(-1, -1, sprite.width + 2, sprite.height + 2, 6);
            wildBorder.endFill();
            sprite.addChild(wildBorder);
        }
        // Lock / Scatter Card (300) - Purple Magic Ring
        else if (cardId === 300) {
            const ring = new PIXI.Graphics();
            ring.lineStyle(2, 0xb23ab2, 0.9);
            ring.drawCircle(sprite.width / 2, sprite.height / 2, sprite.width * 0.4);
            sprite.addChild(ring);
        }
    },

    spawnFloatingText(textStr, x, y) {
        const style = new PIXI.TextStyle({
            fontFamily: 'Outfit',
            fontSize: 20,
            fontWeight: '900',
            fill: ['#ffffff', '#ffd700'], // Bright gold gradient
            stroke: '#0c0e14',
            strokeThickness: 4,
            dropShadow: true,
            dropShadowColor: '#000000',
            dropShadowBlur: 4,
            dropShadowDistance: 2
        });
        const text = new PIXI.Text(textStr, style);
        text.anchor.set(0.5);
        text.x = x;
        text.y = y;
        text.zIndex = 40;
        this.gridContainer.addChild(text);

        let t = 0;
        const textTicker = (delta) => {
            t += delta;
            text.y -= 1.4 * delta;
            text.alpha = Math.max(0, 1 - t / 35);
            if (t >= 35) {
                this.gridContainer.removeChild(text);
                this.app.ticker.remove(textTicker);
            }
        };
        this.app.ticker.add(textTicker);
    },

    renderGridState(grid, matchedIndices = [], isDropAnim = false, oldGrid = null) {
        let fullGrid = [...grid];
        if (fullGrid.length === 25) {
            if (!this.currentPeekingRow || this.currentPeekingRow.length === 0) {
                this.currentPeekingRow = Array.from({ length: 5 }, () => Math.floor(Math.random() * 30) + 1);
            }
            fullGrid = [...this.currentPeekingRow, ...fullGrid];
        }

        for (let i = 0; i < 30; i++) {
            const cardId = fullGrid[i];
            const slot = this.slotContainers[i];
            
            if (this.cardSprites[i]) {
                slot.removeChild(this.cardSprites[i]);
            }

            const cardSprite = this.createCardSprite(cardId, i);
            slot.addChild(cardSprite);
            this.cardSprites[i] = cardSprite;

            // Elastic drop animation from top (realistic slot drop physics)
            if (isDropAnim) {
                const targetY = 4;
                let startY = -120 - Math.random() * 60;

                if (oldGrid && oldGrid.length > 0) {
                    let fullOldGrid = [...oldGrid];
                    if (fullOldGrid.length === 25) {
                        fullOldGrid = [...(this.currentPeekingRow || []), ...fullOldGrid];
                    }
                    
                    const col = i % 5;
                    const row = Math.floor(i / 5) - 1;
                    
                    // Look up in same column in oldGrid to see where this card came from
                    let foundOldRow = -2;
                    for (let r = row; r >= -1; r--) {
                        const oldIdx = (r + 1) * 5 + col;
                        if (fullOldGrid[oldIdx] === cardId) {
                            foundOldRow = r;
                            fullOldGrid[oldIdx] = null; // Mark as mapped
                            break;
                        }
                    }
                    
                    if (foundOldRow >= -1) {
                        startY = (foundOldRow - row) * this.cellH + 4;
                    } else {
                        // Brand new card dropping from top
                        startY = -150 - Math.random() * 80;
                    }
                }

                cardSprite.y = startY;
                
                let velY = 12;
                const dropTicker = (delta) => {
                    cardSprite.y += velY * delta;
                    velY += 1.4 * delta; // Gravity
                    
                    if (cardSprite.y >= targetY) {
                        cardSprite.y = targetY;
                        this.app.ticker.remove(dropTicker);
                        
                        // Subtle bounce spring on land
                        let bounceT = 0;
                        const springTicker = (bDelta) => {
                            bounceT += bDelta * 0.35;
                            cardSprite.y = targetY + Math.sin(bounceT) * 4 * Math.exp(-bounceT * 0.5);
                            if (bounceT > 5) {
                                cardSprite.y = targetY;
                                this.app.ticker.remove(springTicker);
                            }
                        };
                        this.app.ticker.add(springTicker);
                    }
                };
                this.app.ticker.add(dropTicker);
            }

            // Highlight matches cleanly without distorted scaling/tilting
            if (i >= 5) {
                const activeIndex = i - 5;
                if (matchedIndices.includes(activeIndex)) {
                    slot.lineStyle(2, 0xffd700, 1.0);
                    slot.drawRoundedRect(3, 3, this.cellW - 6, this.cellH - 6, 6);
                    
                    // Golden flash overlay instead of distorted scaling
                    const flashGlow = new PIXI.Graphics();
                    flashGlow.beginFill(0xffffff, 0.7);
                    flashGlow.drawRoundedRect(0, 0, cardSprite.width, cardSprite.height, 6);
                    flashGlow.endFill();
                    cardSprite.addChild(flashGlow);

                    let flashT = 0;
                    const flashTicker = (delta) => {
                        flashT += delta * 0.15;
                        flashGlow.alpha = Math.max(0, 0.7 - flashT);
                        if (flashT >= 0.7) {
                            cardSprite.removeChild(flashGlow);
                            this.app.ticker.remove(flashTicker);
                        }
                    };
                    this.app.ticker.add(flashTicker);
                }
            }
        }
    },

    // Vertical Reel Spin Routine (Smooth, controlled, natural reel speed)
    showTensionGlowOnColumn(col, duration) {
        const glowGraphics = new PIXI.Graphics();
        glowGraphics.lineStyle(3, 0xff5533, 0.85); // Intense red-orange glow
        glowGraphics.drawRoundedRect(60 + col * this.cellW + 2, 180 + 22.5 + 2, this.cellW - 4, this.cellH * 5 - 4, 8);
        this.gridCardsContainer.addChild(glowGraphics);

        let t = 0;
        const pulseTicker = (delta) => {
            t += delta * 0.15;
            glowGraphics.alpha = 0.5 + Math.sin(t) * 0.4;
        };
        this.app.ticker.add(pulseTicker);

        setTimeout(() => {
            this.app.ticker.remove(pulseTicker);
            this.gridCardsContainer.removeChild(glowGraphics);
        }, duration);
    },

    // Vertical Reel Spin Routine (Smooth, controlled, natural reel speed)
    spinReels(finalFirstGrid, isSlowDrop, onComplete) {
        const isTurbo = window.KOAUI && window.KOAUI.turboModeActive;
        let fullGrid = [...finalFirstGrid];
        if (fullGrid.length === 25) {
            this.currentPeekingRow = Array.from({ length: 5 }, () => Math.floor(Math.random() * 30) + 1);
            fullGrid = [...this.currentPeekingRow, ...fullGrid];
        }

        const spinningCols = [true, true, true, true, true];
        let tickCounter = 0;

        // Controlled, natural vertical texture cycling (smooth & clear cadence)
        const spinTicker = () => {
            tickCounter++;
            if (tickCounter % 3 !== 0) return; // Update texture every 3 ticks for smooth natural look

            for (let i = 0; i < 30; i++) {
                const col = i % 5;
                if (spinningCols[col]) {
                    const randomCardId = Math.floor(Math.random() * 30) + 1;
                    const slot = this.slotContainers[i];
                    if (this.cardSprites[i]) {
                        slot.removeChild(this.cardSprites[i]);
                    }
                    const tempSprite = this.createCardSprite(randomCardId, i);
                    tempSprite.y = (tickCounter * 4) % 12 - 6; // Natural motion offset
                    slot.addChild(tempSprite);
                    this.cardSprites[i] = tempSprite;
                }
            }
        };

        this.app.ticker.add(spinTicker);

        let scatterCountSoFar = 0;
        let tensionActive = false;

        const stopColumn = (col) => {
            spinningCols[col] = false;
            
            if (window.KOAAudio) window.KOAAudio.playSpinClick();

            let scattersInCol = 0;
            // Lock final textures for this column & trigger drop bounce
            for (let row = -1; row <= 4; row++) {
                const idx = (row + 1) * 5 + col;
                const cardId = fullGrid[idx];
                const slot = this.slotContainers[idx];

                if (cardId === 100 && row >= 0) {
                    scattersInCol++;
                }

                if (this.cardSprites[idx]) {
                    slot.removeChild(this.cardSprites[idx]);
                }

                const finalSprite = this.createCardSprite(cardId, idx);
                slot.addChild(finalSprite);
                this.cardSprites[idx] = finalSprite;

                // Column stop bounce physics
                const targetY = 4;
                finalSprite.y = -18;
                let bT = 0;
                const stopBounce = (delta) => {
                    bT += delta * 0.35;
                    finalSprite.y = targetY + Math.sin(bT * 2) * 5 * Math.exp(-bT * 0.5);
                    if (bT > 4) {
                        finalSprite.y = targetY;
                        this.app.ticker.remove(stopBounce);
                    }
                };
                this.app.ticker.add(stopBounce);
            }

            scatterCountSoFar += scattersInCol;

            if (col === 4) {
                this.app.ticker.remove(spinTicker);
                if (window.KOAAudio) window.KOAAudio.stopTensionRhythm();
                setTimeout(onComplete, isTurbo ? 50 : 150);
                return;
            }

            // Determine delay for the next column stopping sequence
            let nextDelay = isTurbo ? 100 : 250;

            if (!isTurbo) {
                // If 2 or more scatters have landed, activate tension spin on next column!
                if (scatterCountSoFar >= 2) {
                    tensionActive = true;
                    nextDelay = 1500; // Extend next column spin delay for slow motion!
                    
                    if (window.KOAAudio) {
                        window.KOAAudio.startTensionRhythm();
                    }
                    this.showTensionGlowOnColumn(col + 1, 1500);
                }
            }

            setTimeout(() => stopColumn(col + 1), nextDelay);
        };

        const initialDelay = isTurbo ? 120 : 350;
        setTimeout(() => stopColumn(0), initialDelay);
    },

    animateCascade(grids, matches, payouts, multipliers, currentStep, isSlowDrop, callback) {
        if (currentStep >= grids.length) {
            callback();
            return;
        }

        const grid = grids[currentStep];
        const matchInfo = matches[currentStep];
        const ui = window.KOAUI;
        const isTurbo = ui && ui.turboModeActive;
        const engine = window.KOAEngine;

        if (engine) {
            if (currentStep > 0 && multipliers && multipliers[currentStep - 1] !== undefined) {
                this.updateMultiplierBar(currentStep, engine.freeSpinsActive, multipliers[currentStep - 1]);
            } else {
                this.updateMultiplierBar(currentStep, engine.freeSpinsActive, engine.freeSpinsActive ? 2 : 1);
            }
        }

        const oldGrid = currentStep > 0 ? grids[currentStep - 1] : null;
        this.renderGridState(grid, matchInfo ? matchInfo.matchedIndices : [], currentStep > 0, oldGrid);

        if (matchInfo && matchInfo.matchedIndices.length > 0) {
            if (window.KOAAudio) window.KOAAudio.playCardCrack();

            // Spawn floating text showing the score payout at the center of the matching cluster
            let sumX = 0;
            let sumY = 0;
            matchInfo.matchedIndices.forEach(idx => {
                const col = idx % 5;
                const row = Math.floor(idx / 5);
                sumX += (60 + col * this.cellW + this.cellW / 2);
                sumY += (180 + 22.5 + row * this.cellH + this.cellH / 2);
            });
            const avgX = sumX / matchInfo.matchedIndices.length;
            const avgY = sumY / matchInfo.matchedIndices.length;

            const winAmount = payouts[currentStep] || 0;
            if (winAmount > 0) {
                this.spawnFloatingText(`+${winAmount.toLocaleString("id-ID")}`, avgX, avgY);
            }

            const delayBeforeBreak = isTurbo ? 100 : 450;
            const delayAfterBreak = isTurbo ? 80 : 300;

            setTimeout(() => {
                matchInfo.matchedIndices.forEach(idx => {
                    const sprite = this.cardSprites[idx + 5]; // mapped to 30-slot grid
                    if (sprite) {
                        // Explosion & Dissolve effect
                        this.app.ticker.add((delta) => {
                            if (sprite.alpha > 0) {
                                sprite.alpha -= 0.15 * delta;
                                sprite.scale.x += 0.08 * delta; // Expansion pop
                                sprite.scale.y += 0.08 * delta;
                                sprite.rotation += 0.05 * delta;
                            }
                        });
                    }
                });

                setTimeout(() => {
                    this.animateCascade(grids, matches, payouts, multipliers, currentStep + 1, isSlowDrop, callback);
                }, delayAfterBreak);

            }, delayBeforeBreak);
        } else {
            const delayEnd = isTurbo ? 80 : 250;
            setTimeout(() => {
                callback();
            }, delayEnd);
        }
    },

    async spin() {
        const ui = window.KOAUI;
        const engine = window.KOAEngine;
        const audio = window.KOAAudio;
        if (!ui || !engine) return;

        if (audio) {
            audio.resume();
            audio.playSpinClick();
        }

        document.body.classList.remove("ceki-active");

        // Lock button
        this.btnSpin.disabled = true;

        // Reset multipliers bar
        this.updateMultiplierBar(0, engine.freeSpinsActive, engine.freeSpinsActive ? 2 : 1);

        // Reset lock overlays
        this.lockOverlays.forEach(overlay => {
            overlay.alpha = 0;
            overlay.texture = PIXI.Texture.EMPTY;
        });

        const bet = ui.currentBet;

        // CSRF Token
        let csrfToken = '';
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) {
            csrfToken = csrfMeta.getAttribute('content');
        }

        let result;
        try {
            const response = await fetch('/api/game/koa/spin', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    bet_amount: bet,
                    free_spins_active: engine.freeSpinsActive,
                    free_spins_left: engine.freeSpinsLeft,
                    free_spins_win_accumulated: engine.freeSpinsWinAccumulated,
                    user_gold_quota: engine.userGoldQuota,
                    force_free_spins: window.forceFreeSpinsTrigger || false
                })
            });
            window.forceFreeSpinsTrigger = false;

            if (!response.ok) {
                const errData = await response.json();
                throw new Error(errData.error || `Server returned status ${response.status}`);
            }

            result = await response.json();

            // Sync server balances
            engine.userSilver = result.userSilver;
            engine.userGoldQuota = result.userGoldQuota;
            engine.userGoldVault = result.userGoldVault;
            engine.liquidityPool = result.liquidityPool;
            engine.freeSpinsActive = result.freeSpinsActive;
            engine.freeSpinsLeft = result.freeSpinsLeft;
            engine.freeSpinsWinAccumulated = result.freeSpinsWinAccumulated;

        } catch (err) {
            console.warn("Backend spin failed, falling back to local simulation:", err.message);
            result = engine.processSpin(bet);
            if (result && result.error) {
                ui.showToast(result.message, "error");
                this.btnSpin.disabled = false;
                ui.autoSpinActive = false;
                const btnAuto = document.getElementById("btn-auto");
                if (btnAuto) btnAuto.classList.remove("active");
                return;
            }
        }

        const isSlowDrop = result.triggerCeki;
        ui.updateDashboard();

        if (isSlowDrop) {
            document.body.classList.add("ceki-active");
            ui.showToast("TENSION: MODE CEKI! Menanti Scatter ketiga...", "success");
            if (audio) audio.startTensionRhythm();
        }

        // Trigger staggered vertical slot reel spin & stop sequence
        this.spinReels(result.grids[0], isSlowDrop, () => {
            this.animateCascade(
                result.grids,
                result.matches,
                result.payouts,
                result.multipliers,
                0,
                isSlowDrop,
                () => {
                    this.postSpinCleanup(result);
                }
            );
        });
    },

    postSpinCleanup(result) {
        const ui = window.KOAUI;
        const engine = window.KOAEngine;
        const audio = window.KOAAudio;
        
        document.body.classList.remove("ceki-active");
        if (audio) {
            audio.stopTensionRhythm();
        }

        ui.updateDashboard();

        if (result.totalWin > 0) {
            ui.showToast(`Menang +${result.totalWin.toLocaleString("id-ID")} Koin!`, "success");
            if (audio) audio.playWinSound();
        }

        this.renderGridState(result.grids[result.grids.length - 1]);

        // Coki Mode Lock Reels
        if (result.triggerCeki) {
            const finalGrid = result.grids[result.grids.length - 1];
            const lockedCols = new Set();
            for (let i = 0; i < 25; i++) {
                if (finalGrid[i] === 100) {
                    lockedCols.add(i % 5);
                }
            }

            for (let i = 0; i < 25; i++) {
                const col = i % 5;
                if (lockedCols.has(col)) {
                    const lockOverlay = this.lockOverlays[i];
                    if (lockOverlay) {
                        lockOverlay.texture = window.KOAAtlas ? window.KOAAtlas.getCardTexture(300) : PIXI.Texture.EMPTY;
                        lockOverlay.alpha = 0.65;
                    }
                }
            }
        }

        this.updateFreeSpinsUI();

        // Chest bounce and modal for Free Spins
        if (result.triggerFreeSpins && result.freeSpinsLeft === 12) {
            if (this.chestSprite) {
                // Bounce chest sprite in PixiJS!
                let t = 0;
                this.app.ticker.add((delta) => {
                    if (t < 60) {
                        this.chestSprite.scale.x = 1.0 + Math.sin(t * 0.15) * 0.15;
                        this.chestSprite.scale.y = 1.0 + Math.sin(t * 0.15) * 0.15;
                        t += delta;
                    } else {
                        this.chestSprite.scale.set(1.0);
                    }
                });
            }
            ui.showModal("K.O.AA!", "Selamat! Anda memicu 12 Free Spins. Pengali meningkat drastis: x2, x4, x6, x10!");
            if (audio) audio.startFreeSpinRhythm();
        }

        if (result.maxWinReached) {
            ui.showModal("MAX WIN!", "MAX WIN: GUNUNG MAS DITAKLUKKAN! Pengumpulan hasil tambang telah mencapai batas maksimum 35%.");
        }

        // Auto spin triggering
        if (ui.autoSpinActive && engine.userSilver >= ui.currentBet) {
            setTimeout(() => {
                if (ui.autoSpinActive) {
                    this.spin();
                } else {
                    this.btnSpin.disabled = false;
                }
            }, 800);
        } else {
            if (ui.autoSpinActive) {
                ui.autoSpinActive = false;
                const btnAuto = document.getElementById("btn-auto");
                if (btnAuto) btnAuto.classList.remove("active");
                ui.showToast("Auto Spin dinonaktifkan: Saldo habis.", "error");
            }
            this.btnSpin.disabled = false;
        }
    },

    updateFreeSpinsUI() {
        const engine = window.KOAEngine;
        if (engine.freeSpinsActive && engine.freeSpinsLeft > 0) {
            this.fsIndicator.classList.remove("hidden");
            this.fsCurrent.textContent = engine.freeSpinsLeft;
            document.querySelector(".game-container").classList.add("free-spins-active-layout");
        } else {
            this.fsIndicator.classList.add("hidden");
            document.querySelector(".game-container").classList.remove("free-spins-active-layout");
        }
    },

    updateMultiplierBar(step, isFreeSpin, currentMultiplier) {
        const activeVals = isFreeSpin ? [2, 4, 6, 10] : [1, 2, 4, 5];
        this.multiplierSprites.forEach((container, idx) => {
            const val = activeVals[idx];
            container.text.text = `${val}x`;

            container.bg.clear();
            if (val === currentMultiplier) {
                container.bg.beginFill(0x8a6508);
                container.bg.lineStyle(3.5, 0xf1c40f);
                container.bg.drawCircle(0, 0, 24);
                container.bg.endFill();
                container.text.style.fill = '#ffffff';
            } else {
                container.bg.beginFill(0x1e0505);
                container.bg.lineStyle(2.2, 0x721c1c);
                container.bg.drawCircle(0, 0, 22);
                container.bg.endFill();
                container.text.style.fill = '#ffd8a8';
            }
        });
    },

    setupGameListeners() {
        if (this.btnSpin) {
            this.btnSpin.addEventListener("click", () => {
                this.spin();
            });
        }
    }
};

// Start Game
document.addEventListener("DOMContentLoaded", () => {
    KOAGame.init();
});

window.KOAGame = KOAGame;
