/**
 * Multimedia & Interactive Document Reader Module
 * Langkisau Pangkas Online
 */

let isAudioPlaying = false;
let audioTimer = null;
let audioCurrentSeconds = 0;
const audioDurationSeconds = 135; // 2:15

// Audio Voice Note Simulation & Controls
function toggleVoiceNote() {
    const playBtn = document.getElementById('voicePlayBtn');
    const playIcon = document.getElementById('voicePlayIcon');
    const waveformBox = document.getElementById('waveformContainer');
    const statusText = document.getElementById('voiceStatusText');

    isAudioPlaying = !isAudioPlaying;

    if (isAudioPlaying) {
        if (playIcon) playIcon.innerText = '⏸️';
        if (waveformBox) waveformBox.classList.add('audio-playing');
        if (statusText) statusText.innerText = 'Sedang Memutar Pesan Suara...';

        // Start timer simulation
        audioTimer = setInterval(() => {
            audioCurrentSeconds++;
            if (audioCurrentSeconds >= audioDurationSeconds) {
                resetVoiceNote();
                return;
            }
            updateAudioProgress();
        }, 1000);
    } else {
        if (playIcon) playIcon.innerText = '▶️';
        if (waveformBox) waveformBox.classList.remove('audio-playing');
        if (statusText) statusText.innerText = 'Pesan Suara Dijeda';
        clearInterval(audioTimer);
    }
}

function updateAudioProgress() {
    const progressEl = document.getElementById('audioProgressBar');
    const timeCurrentEl = document.getElementById('audioCurrentTime');

    const pct = (audioCurrentSeconds / audioDurationSeconds) * 100;
    if (progressEl) progressEl.style.width = `${pct}%`;

    const mins = Math.floor(audioCurrentSeconds / 60);
    const secs = audioCurrentSeconds % 60;
    if (timeCurrentEl) {
        timeCurrentEl.innerText = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
}

function resetVoiceNote() {
    clearInterval(audioTimer);
    isAudioPlaying = false;
    audioCurrentSeconds = 0;
    const playIcon = document.getElementById('voicePlayIcon');
    const waveformBox = document.getElementById('waveformContainer');
    const statusText = document.getElementById('voiceStatusText');
    const progressEl = document.getElementById('audioProgressBar');
    const timeCurrentEl = document.getElementById('audioCurrentTime');

    if (playIcon) playIcon.innerText = '▶️';
    if (waveformBox) waveformBox.classList.remove('audio-playing');
    if (statusText) statusText.innerText = 'Dengarkan Kembali Pesan Suara';
    if (progressEl) progressEl.style.width = '0%';
    if (timeCurrentEl) timeCurrentEl.innerText = '00:00';
}

// Media Tab Switcher (Foto, Audio, Video, Dokumen)
function switchMediaTab(tabId) {
    const tabs = ['tab-media-photo', 'tab-media-audio', 'tab-media-video', 'tab-media-doc'];
    const contents = ['content-media-photo', 'content-media-audio', 'content-media-video', 'content-media-doc'];

    tabs.forEach(t => {
        const el = document.getElementById(t);
        if (el) {
            el.classList.remove('bg-ocean-800', 'text-white', 'shadow-md');
            el.classList.add('bg-stone-100', 'text-stone-700');
        }
    });

    contents.forEach(c => {
        const el = document.getElementById(c);
        if (el) el.classList.add('hidden');
    });

    const activeTab = document.getElementById(`tab-media-${tabId}`);
    const activeContent = document.getElementById(`content-media-${tabId}`);

    if (activeTab) {
        activeTab.classList.remove('bg-stone-100', 'text-stone-700');
        activeTab.classList.add('bg-ocean-800', 'text-white', 'shadow-md');
    }
    if (activeContent) {
        activeContent.classList.remove('hidden');
        activeContent.classList.add('animate-fade-in');
    }
}

// PDF Document Modal Viewer
function openPdfModal() {
    const modal = document.getElementById('pdfModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
}

function closePdfModal() {
    const modal = document.getElementById('pdfModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }
}

// Equipment Image Lightbox Modal Viewer
function openEquipmentImageModal() {
    const modal = document.getElementById('equipmentImageModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
}

function closeEquipmentImageModal() {
    const modal = document.getElementById('equipmentImageModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }
}

// Close modal on Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closePdfModal();
        closeEquipmentImageModal();
    }
});
