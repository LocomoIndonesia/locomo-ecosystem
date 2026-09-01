/* Web Audio API Sound Synthesizer for Slot Kartu KOA */

const KOAAudio = {
    ctx: null,
    tensionInterval: null,
    freeSpinInterval: null,
    isPlayingTension: false,

    init() {
        // Initialize AudioContext on first user interaction to comply with browser autoplay policies
        if (!this.ctx) {
            this.ctx = new (window.AudioContext || window.webkitAudioContext)();
        }
    },

    resume() {
        this.init();
        if (this.ctx && this.ctx.state === 'suspended') {
            this.ctx.resume();
        }
    },

    // Synthesize a wood block crack / card impact sound
    playCardCrack() {
        this.resume();
        if (!this.ctx) return;

        const now = this.ctx.currentTime;
        
        // Triangle oscillator for the wood body resonance
        const osc = this.ctx.createOscillator();
        const gainNode = this.ctx.createGain();
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(150, now);
        osc.frequency.exponentialRampToValueAtTime(80, now + 0.15);

        // Noise buffer for the bright impact crack
        const bufferSize = this.ctx.sampleRate * 0.05; // 50ms noise
        const buffer = this.ctx.createBuffer(1, bufferSize, this.ctx.sampleRate);
        const data = buffer.getChannelData(0);
        for (let i = 0; i < bufferSize; i++) {
            data[i] = Math.random() * 2 - 1;
        }
        
        const noise = this.ctx.createBufferSource();
        noise.buffer = buffer;

        const noiseFilter = this.ctx.createBiquadFilter();
        noiseFilter.type = 'bandpass';
        noiseFilter.frequency.setValueAtTime(1200, now);
        noiseFilter.Q.setValueAtTime(3, now);

        const noiseGain = this.ctx.createGain();
        noiseGain.gain.setValueAtTime(0.3, now);
        noiseGain.gain.exponentialRampToValueAtTime(0.001, now + 0.04);

        // Connect noise
        noise.connect(noiseFilter);
        noiseFilter.connect(noiseGain);
        noiseGain.connect(this.ctx.destination);

        // Connect wood resonance oscillator
        gainNode.gain.setValueAtTime(0.4, now);
        gainNode.gain.exponentialRampToValueAtTime(0.001, now + 0.15);

        osc.connect(gainNode);
        gainNode.connect(this.ctx.destination);

        osc.start(now);
        osc.stop(now + 0.16);
        noise.start(now);
    },

    // Synthesize a Gandang Tasa (traditional drum) slap
    playGandangTasa(isHighTone = false) {
        this.resume();
        if (!this.ctx) return;

        const now = this.ctx.currentTime;

        // Drum body resonance (Sine sweep)
        const osc = this.ctx.createOscillator();
        const gainNode = this.ctx.createGain();
        osc.type = 'sine';
        
        const startFreq = isHighTone ? 320 : 180;
        const endFreq = isHighTone ? 180 : 110;
        osc.frequency.setValueAtTime(startFreq, now);
        osc.frequency.exponentialRampToValueAtTime(endFreq, now + 0.08);

        gainNode.gain.setValueAtTime(isHighTone ? 0.45 : 0.6, now);
        gainNode.gain.exponentialRampToValueAtTime(0.001, now + 0.12);

        // Drum skin slap noise (White noise + Bandpass)
        const bufferSize = this.ctx.sampleRate * 0.08;
        const buffer = this.ctx.createBuffer(1, bufferSize, this.ctx.sampleRate);
        const data = buffer.getChannelData(0);
        for (let i = 0; i < bufferSize; i++) {
            data[i] = Math.random() * 2 - 1;
        }

        const noise = this.ctx.createBufferSource();
        noise.buffer = buffer;

        const filter = this.ctx.createBiquadFilter();
        filter.type = 'bandpass';
        filter.frequency.setValueAtTime(isHighTone ? 1800 : 900, now);
        filter.Q.setValueAtTime(2, now);

        const noiseGain = this.ctx.createGain();
        noiseGain.gain.setValueAtTime(isHighTone ? 0.25 : 0.15, now);
        noiseGain.gain.exponentialRampToValueAtTime(0.001, now + 0.06);

        noise.connect(filter);
        filter.connect(noiseGain);
        noiseGain.connect(this.ctx.destination);

        osc.connect(gainNode);
        gainNode.connect(this.ctx.destination);

        osc.start(now);
        osc.stop(now + 0.13);
        noise.start(now);
    },

    // Synthesize spin release click
    playSpinClick() {
        this.resume();
        if (!this.ctx) return;

        const now = this.ctx.currentTime;
        const osc = this.ctx.createOscillator();
        const gainNode = this.ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(800, now);
        osc.frequency.setValueAtTime(300, now + 0.03);

        gainNode.gain.setValueAtTime(0.1, now);
        gainNode.gain.exponentialRampToValueAtTime(0.001, now + 0.04);

        osc.connect(gainNode);
        gainNode.connect(this.ctx.destination);

        osc.start(now);
        osc.stop(now + 0.05);
    },

    // Synthesize a win celebration sound sweep
    playWinSound() {
        this.resume();
        if (!this.ctx) return;

        const now = this.ctx.currentTime;
        const notes = [261.63, 329.63, 392.00, 523.25]; // C major chord

        notes.forEach((freq, idx) => {
            const osc = this.ctx.createOscillator();
            const gainNode = this.ctx.createGain();
            
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(freq, now + idx * 0.08);
            
            gainNode.gain.setValueAtTime(0, now + idx * 0.08);
            gainNode.gain.linearRampToValueAtTime(0.15, now + idx * 0.08 + 0.02);
            gainNode.gain.exponentialRampToValueAtTime(0.001, now + idx * 0.08 + 0.3);
            
            osc.connect(gainNode);
            gainNode.connect(this.ctx.destination);
            
            osc.start(now + idx * 0.08);
            osc.stop(now + idx * 0.08 + 0.32);
        });
    },

    // Start high-intensity Coki Mode (Tension) Gandang Tasa drumming
    startTensionRhythm() {
        this.resume();
        if (this.isPlayingTension) return;
        this.isPlayingTension = true;

        let beat = 0;
        // Fast traditional tempo: ~130ms per 16th note
        this.tensionInterval = setInterval(() => {
            // Gandang Tasa rhythmic pattern: Slap-Slap-Mute-Slap-Slap...
            if (beat % 4 === 0) {
                this.playGandangTasa(false); // Bass drum tone
            } else if (beat % 4 === 1 || beat % 4 === 3) {
                this.playGandangTasa(true);  // High slap tone
            }
            beat++;
        }, 130);
    },

    stopTensionRhythm() {
        if (this.tensionInterval) {
            clearInterval(this.tensionInterval);
            this.tensionInterval = null;
        }
        this.isPlayingTension = false;
    },

    // Start Free Spin (K.O.AA) premium drumming background loop
    startFreeSpinRhythm() {
        this.resume();
        if (this.freeSpinInterval) return;

        let beat = 0;
        this.freeSpinInterval = setInterval(() => {
            // Cheerful celebratory loop
            if (beat % 8 === 0 || beat % 8 === 4) {
                this.playGandangTasa(false); // heavy beat
            }
            if (beat % 8 === 2 || beat % 8 === 5 || beat % 8 === 7) {
                this.playGandangTasa(true);  // light slaps
            }
            beat++;
        }, 160);
    },

    stopFreeSpinRhythm() {
        if (this.freeSpinInterval) {
            clearInterval(this.freeSpinInterval);
            this.freeSpinInterval = null;
        }
    }
};

window.KOAAudio = KOAAudio;
