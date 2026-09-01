/* Atlas Loader - Slot Kartu KOA (Ceki) - Multi-Atlas Pipeline Version */

const KOAAtlas = {
    cardsAtlas: null,
    coinTreasureAtlas: null,
    ornamenBgAtlas: null,
    uiAtlas: null,
    typografiAtlas: null,

    async load() {
        try {
            // Load all atlas json files. PixiJS automatically loads webp/png sheet textures relative to them.
            const [cards, coin, ornamen, ui, typo] = await Promise.all([
                PIXI.Assets.load('assets/atlas/cards.json'),
                PIXI.Assets.load('assets/atlas/coin_treasure-atlas.json'),
                PIXI.Assets.load('assets/atlas/ornamen_bacjgriund_atlas.json'),
                PIXI.Assets.load('assets/atlas/UI_Atlas.json'),
                PIXI.Assets.load('assets/atlas/typografi_atlas.json')
            ]);

            this.cardsAtlas = cards;
            this.coinTreasureAtlas = coin;
            this.ornamenBgAtlas = ornamen;
            this.uiAtlas = ui;
            this.typografiAtlas = typo;

            console.log("All texture atlases loaded successfully into the pipeline!");
        } catch (error) {
            console.error("Failed to load texture atlases:", error);
        }
    },

    /**
     * Gets a card or symbol texture by its ID
     */
    getCardTexture(cardId) {
        if (!this.cardsAtlas) {
            console.warn("Cards atlas not loaded yet, returning empty texture");
            return PIXI.Texture.EMPTY;
        }

        if (cardId === 100) {
            return this.cardsAtlas.textures['sym_koa'] || PIXI.Texture.EMPTY;
        } else if (cardId === 200) {
            return this.cardsAtlas.textures['sym_wild'] || PIXI.Texture.EMPTY;
        } else if (cardId === 300) {
            return this.cardsAtlas.textures['sym_lock'] || PIXI.Texture.EMPTY;
        }
        return this.cardsAtlas.textures[`card_${cardId}`] || PIXI.Texture.EMPTY;
    },

    /**
     * Gets any texture from a specific loaded atlas
     * @param {string} atlasName - 'cards', 'coin_treasure', 'ornamen', 'ui', 'typografi'
     * @param {string} frameName - Name of the frame key inside the atlas json
     */
    getTexture(atlasName, frameName) {
        const atlasMap = {
            'cards': this.cardsAtlas,
            'coin_treasure': this.coinTreasureAtlas,
            'ornamen': this.ornamenBgAtlas,
            'ui': this.uiAtlas,
            'typografi': this.typografiAtlas
        };
        const atlas = atlasMap[atlasName];
        if (!atlas || !atlas.textures) {
            return PIXI.Texture.EMPTY;
        }
        return atlas.textures[frameName] || PIXI.Texture.EMPTY;
    }
};

window.KOAAtlas = KOAAtlas;
