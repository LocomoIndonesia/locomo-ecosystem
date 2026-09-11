/**
 * Main Application Orchestrator
 * Langkisau Pangkas Online
 */

let selectedItemIds = [];

// Inisialisasi Daftar Item Starter Kit (Checklist Interaktif dengan Foto Perlengkapan)
function renderEquipmentList() {
    const listEl = document.getElementById('itemList');
    if (!listEl) return;
    listEl.innerHTML = '';

    APP_DATA.equipment.forEach(item => {
        const isSelected = selectedItemIds.includes(item.id);
        const card = document.createElement('div');
        card.className = `p-3.5 sm:p-4 rounded-2xl border equipment-item-card cursor-pointer flex items-start justify-between transition-all duration-200 ${
            isSelected 
                ? 'bg-amber-50/80 border-amber-600 shadow-md ring-1 ring-amber-500' 
                : 'bg-white border-stone-200 hover:border-amber-400 hover:bg-stone-50/80'
        }`;
        card.onclick = () => toggleSelectItem(item.id);

        card.innerHTML = `
            <div class="flex items-start space-x-3 sm:space-x-3.5 pr-2 flex-1">
                <input type="checkbox" ${isSelected ? 'checked' : ''} class="mt-2 w-5 h-5 accent-amber-700 rounded cursor-pointer pointer-events-none flex-shrink-0">
                
                <!-- Thumbnail Foto dari item_perlengkapan.jpg -->
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl overflow-hidden border border-stone-200 bg-stone-100 flex-shrink-0 relative group/thumb shadow-sm" onclick="event.stopPropagation(); openEquipmentImageModal();">
                    ${item.hasPhoto ? `
                        <div style="background-image: url('assets/images/item_perlengkapan.jpg'); background-size: 320% 210%; background-position: ${item.spritePosition}; width: 100%; height: 100%;" class="transition-transform duration-300 group-hover/thumb:scale-110" title="Klik untuk memperbesar foto ${item.name}"></div>
                        <div class="absolute inset-0 bg-black/25 opacity-0 group-hover/thumb:opacity-100 transition-opacity flex items-center justify-center text-[11px] text-white font-bold backdrop-blur-[1px]">🔍</div>
                    ` : `
                        <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-amber-50 to-amber-100 text-amber-800">
                            <span class="text-xl">📣</span>
                            <span class="text-[8px] font-bold mt-0.5">Media</span>
                        </div>
                    `}
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                        <h5 class="font-bold text-stone-900 text-sm sm:text-base leading-snug">${item.name}</h5>
                        <span class="text-[9px] sm:text-[10px] uppercase tracking-wider font-bold px-2 py-0.5 rounded-full ${
                            item.badge === 'Prioritas Utama' ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-stone-100 text-stone-700'
                        }">${item.badge}</span>
                    </div>
                    <p class="text-xs text-stone-600 mt-1 leading-relaxed">${item.desc}</p>
                </div>
            </div>

            <div class="text-right whitespace-nowrap pl-2 flex flex-col items-end justify-between self-stretch">
                <span class="font-extrabold text-amber-800 text-sm sm:text-base block">Rp ${item.price.toLocaleString('id-ID')}</span>
                <span class="text-[10px] sm:text-xs font-semibold ${isSelected ? 'text-amber-800 font-bold' : 'text-stone-400'}">
                    ${isSelected ? '✓ Terpilih' : '+ Pilih Item'}
                </span>
            </div>
        `;
        listEl.appendChild(card);
    });

    updateSelectedTotal();
}

function toggleSelectItem(id) {
    if (selectedItemIds.includes(id)) {
        selectedItemIds = selectedItemIds.filter(i => i !== id);
    } else {
        selectedItemIds.push(id);
    }
    renderEquipmentList();
}

function selectAllItems() {
    if (selectedItemIds.length === APP_DATA.equipment.length) {
        selectedItemIds = [];
    } else {
        selectedItemIds = APP_DATA.equipment.map(i => i.id);
    }
    renderEquipmentList();
}

function updateSelectedTotal() {
    const total = selectedItemIds.reduce((sum, id) => {
        const item = APP_DATA.equipment.find(e => e.id === id);
        return sum + (item ? item.price : 0);
    }, 0);

    const totalEl = document.getElementById('selectedTotal');
    const countEl = document.getElementById('selectedCount');
    if (totalEl) totalEl.innerText = `Rp ${total.toLocaleString('id-ID')}`;
    if (countEl) countEl.innerText = `${selectedItemIds.length} item dipilih`;
}

function applySelectedSponsorship() {
    const total = selectedItemIds.reduce((sum, id) => {
        const item = APP_DATA.equipment.find(e => e.id === id);
        return sum + (item ? item.price : 0);
    }, 0);

    if (total === 0) {
        showToast('Silakan centang minimal 1 item alat di atas terlebih dahulu.');
        return;
    }

    const itemNames = selectedItemIds.map(id => {
        const item = APP_DATA.equipment.find(e => e.id === id);
        return item ? item.name : '';
    }).join(', ');

    const schemeInput = document.getElementById('donorScheme');
    const amountInput = document.getElementById('donorAmount');
    const messageInput = document.getElementById('donorMessage');

    if (schemeInput) schemeInput.value = 'Sponsor Item Alat Tertentu';
    if (amountInput) amountInput.value = total.toLocaleString('id-ID');
    if (messageInput) messageInput.value = `Saya ingin mensponsori item: ${itemNames}. Semoga ikhtiar ini berkah dan membangkitkan kemandirian pemuda Pesisir Selatan.`;

    const target = document.getElementById('kontribusi');
    if (target) target.scrollIntoView({ behavior: 'smooth' });

    showToast(`Berhasil memilih ${selectedItemIds.length} item. Formulir telah disesuaikan!`);
}

// Inisialisasi Kartu Rekening Bank
function renderBankAccounts() {
    const container = document.getElementById('bankAccountsList');
    if (!container) return;
    container.innerHTML = '';

    APP_DATA.bankAccounts.forEach(acc => {
        const card = document.createElement('div');
        card.className = 'bank-card bg-white p-4 sm:p-5 rounded-2xl shadow-sm space-y-3';
        card.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-2xl">${acc.icon}</span>
                    <div>
                        <h5 class="font-bold text-stone-900 text-sm sm:text-base leading-snug">${acc.bankName}</h5>
                        <span class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full font-semibold border border-emerald-200">${acc.badge}</span>
                    </div>
                </div>
            </div>
            
            <div class="bg-stone-50 p-3 rounded-xl border border-stone-200 flex items-center justify-between">
                <div>
                    <span class="text-[11px] text-stone-500 block">Nomor Rekening / Akun:</span>
                    <span class="font-mono font-bold text-stone-900 text-base sm:text-lg tracking-wider">${acc.accountNumber}</span>
                </div>
                <button onclick="copyBankAccount('${acc.accountNumber}', '${acc.bankName}')" class="bg-ocean-800 hover:bg-ocean-900 text-white text-xs font-semibold px-3 py-2 rounded-lg flex items-center space-x-1.5 transition-all shadow-sm active:scale-95">
                    <span>📋</span>
                    <span>Salin</span>
                </button>
            </div>

            <div class="text-xs text-stone-600 flex justify-between">
                <span>Atas Nama:</span>
                <span class="font-bold text-stone-900">${acc.holderName}</span>
            </div>
        `;
        container.appendChild(card);
    });
}

// Salin Nomor Rekening
function copyBankAccount(accountNumber, bankName) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(accountNumber).then(() => {
            showToast(`Nomor rekening ${bankName} (${accountNumber}) berhasil disalin!`);
        }).catch(() => {
            fallbackCopyText(accountNumber, bankName);
        });
    } else {
        fallbackCopyText(accountNumber, bankName);
    }
}

function fallbackCopyText(text, bankName) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        document.execCommand('copy');
        showToast(`Nomor rekening ${bankName} (${text}) berhasil disalin!`);
    } catch (err) {
        showToast(`Nomor rekening: ${text}`);
    }
    document.body.removeChild(textArea);
}

// Toast Notifikasi
function showToast(message) {
    const toast = document.getElementById('copyToast');
    const toastText = document.getElementById('toastMessage');
    if (!toast || !toastText) return;

    toastText.innerText = message;
    toast.classList.remove('hidden');
    toast.classList.add('toast-show');

    setTimeout(() => {
        toast.classList.add('hidden');
        toast.classList.remove('toast-show');
    }, 3500);
}

// Switch Skema Kerjasama Syariah
function switchScheme(scheme) {
    const schemes = ['qardh', 'donasi', 'csr'];
    schemes.forEach(s => {
        const content = document.getElementById(`content-${s}`);
        const tab = document.getElementById(`tab-${s}`);
        if (content) content.classList.add('hidden');
        if (tab) {
            tab.classList.remove('bg-ocean-800', 'text-white', 'shadow');
            tab.classList.add('bg-white', 'text-stone-700');
        }
    });

    const activeContent = document.getElementById(`content-${scheme}`);
    const activeTab = document.getElementById(`tab-${scheme}`);

    if (activeContent) {
        activeContent.classList.remove('hidden');
        activeContent.classList.add('animate-fade-in');
    }
    if (activeTab) {
        activeTab.classList.remove('bg-white', 'text-stone-700');
        activeTab.classList.add('bg-ocean-800', 'text-white', 'shadow');
    }
}

// WhatsApp Pledge Form Submission
function handleFormSubmit(e) {
    e.preventDefault();
    const name = document.getElementById('donorName').value.trim();
    const scheme = document.getElementById('donorScheme').value;
    const amount = document.getElementById('donorAmount').value.trim();
    const phone = document.getElementById('donorPhone').value.trim();
    const message = document.getElementById('donorMessage').value.trim();
    const selectedTargetPhone = document.getElementById('targetPhoneSelector') ? document.getElementById('targetPhoneSelector').value : '6281378053239';

    if (!name) {
        showToast('Mohon cantumkan nama Anda.');
        return;
    }

    const text = `Assalamu'alaikum / Halo Saudaraku Pangeran Indra Sakti (Langkisau Pangkas Online),\n\nSaya ingin bersilaturahmi dan menyampaikan dukungan untuk ikhtiar *Kemandirian SDM & Bantuan Modal Usaha*:\n\n👤 *Nama:* ${name}\n🤝 *Skema Dukungan:* ${scheme}\n💰 *Nominal/Item:* Rp ${amount || 'Sukarela'}\n📱 *No. Kontak/WA:* ${phone || '-'}\n💬 *Pesan/Harapan:* ${message || 'Semoga usaha pangkas online ini sukses dan membawa barakah untuk keluarga dan pemuda Pesisir Selatan.'}\n\nMohon informasi koordinasi selanjutnya. Terima kasih banyak!`;

    const encodedText = encodeURIComponent(text);
    const waUrl = `https://wa.me/${selectedTargetPhone}?text=${encodedText}`;
    window.open(waUrl, '_blank');
}

// Switch Mode Formulir Utama: Dukungan Donasi vs Pesan Pangkas Online
function switchMainActionTab(mode) {
    const formDonate = document.getElementById('pledgeForm');
    const formBook = document.getElementById('mainBookingForm');
    const btnDonate = document.getElementById('mainBtnTabDonate');
    const btnBook = document.getElementById('mainBtnTabBook');

    if (!formDonate || !formBook) return;

    if (mode === 'donate') {
        formDonate.classList.remove('hidden');
        formBook.classList.add('hidden');
        if (btnDonate) btnDonate.className = 'flex-1 py-3 rounded-2xl font-bold text-xs sm:text-sm bg-gradient-to-r from-amberbrand-700 to-amberbrand-800 text-white shadow-md transition-all flex items-center justify-center space-x-2';
        if (btnBook) btnBook.className = 'flex-1 py-3 rounded-2xl font-semibold text-xs sm:text-sm text-ocean-200 hover:text-white transition-all flex items-center justify-center space-x-2';
    } else {
        formDonate.classList.add('hidden');
        formBook.classList.remove('hidden');
        if (btnDonate) btnDonate.className = 'flex-1 py-3 rounded-2xl font-semibold text-xs sm:text-sm text-ocean-200 hover:text-white transition-all flex items-center justify-center space-x-2';
        if (btnBook) btnBook.className = 'flex-1 py-3 rounded-2xl font-bold text-xs sm:text-sm bg-gradient-to-r from-emerald-600 to-teal-700 text-white shadow-md transition-all flex items-center justify-center space-x-2';
    }
}

// Navigasi Cepat Floating Button ke Formulir Pesan Pangkas Online
function goToBookingPangkas() {
    switchMainActionTab('book');
    const target = document.getElementById('kontribusi');
    if (target) {
        target.scrollIntoView({ behavior: 'smooth' });
        setTimeout(() => {
            const input = document.getElementById('mainOrderClientName');
            if (input) {
                input.focus();
            }
        }, 400);
    }
}

// WhatsApp Order Booking Home Visit Form Submission
function handleOrderBookingSubmit(e) {
    e.preventDefault();
    const name = document.getElementById('mainOrderClientName').value.trim();
    const count = document.getElementById('mainOrderClientCount').value;
    const time = document.getElementById('mainOrderClientTime').value.trim();
    const address = document.getElementById('mainOrderClientAddress').value.trim();
    const notes = document.getElementById('mainOrderClientNotes').value.trim();
    const selectedTargetPhone = document.getElementById('mainOrderTargetPhone') ? document.getElementById('mainOrderTargetPhone').value : '6281378053239';

    if (!name || !address) {
        showToast('Mohon lengkapi nama pemesan dan alamat kunjungan.');
        return;
    }

    const text = `Assalamu'alaikum / Halo Langkisau Pangkas Online,\n\nSaya ingin memesan *Layanan Pangkas Rambut Home Visit (Panggilan)*:\n\n✂️ *Nama Pemesan:* ${name}\n👥 *Jumlah Pelanggan:* ${count}\n📅 *Rencana Waktu/Jam:* ${time || 'Konfirmasi via WhatsApp'}\n📍 *Alamat / Lokasi Panggilan:* ${address}\n📝 *Catatan Khusus:* ${notes || 'Mohon hadir tepat waktu'}\n\nApakah jadwal ini dapat dilayani? Terima kasih banyak!`;

    const encodedText = encodeURIComponent(text);
    const waUrl = `https://wa.me/${selectedTargetPhone}?text=${encodedText}`;
    window.open(waUrl, '_blank');
}

// Scroll Progress Tracker
window.addEventListener('scroll', () => {
    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const scrolled = (winScroll / height) * 100;
    const progressBar = document.getElementById('reading-progress');
    if (progressBar) progressBar.style.width = `${scrolled}%`;
});

// Inisialisasi Saat DOM Siap
document.addEventListener('DOMContentLoaded', () => {
    renderEquipmentList();
    renderBankAccounts();
    initBudgetChart();
    initROIChart();
    updateROICalculations();

    const weekdaySlider = document.getElementById('weekdayInput');
    const weekendSlider = document.getElementById('weekendInput');
    if (weekdaySlider) weekdaySlider.addEventListener('input', updateROICalculations);
    if (weekendSlider) weekendSlider.addEventListener('input', updateROICalculations);
});
