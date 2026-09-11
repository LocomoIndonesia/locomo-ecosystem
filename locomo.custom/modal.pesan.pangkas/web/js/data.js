/**
 * Data Source - Langkisau Pangkas Online
 * Berisi data starter kit perlengkapan, skema akad, info rekening, & data multimedia
 */

const APP_DATA = {
    version: "20260905.1705",
    founder: {
        name: "Pangeran Indra Sakti",
        role: "Inisiator & Founder Langkisau Pangkas Online",
        location: "Painan, Pesisir Selatan, Sumatera Barat",
        phones: [
            { label: "Nomor Utama (WhatsApp)", number: "081378053239", formatted: "0813-7805-3239" },
            { label: "Nomor Alternatif (WhatsApp)", number: "0895329429118", formatted: "0895-3294-29118" }
        ],
        quote: "Di setiap helai rambut yang dirapikan, ada doa dan marwah keluarga yang sedang saya perjuangkan secara terhormat dan halal."
    },

    // 7 Item Starter Kit Standar Barbershop Profesional (Total: Rp 4.500.000)
    equipment: [
        {
            id: 1,
            name: "Clipper WAHL Magic Clip Cordless (Original)",
            category: "Mesin Utama",
            price: 1700000,
            icon: "💈",
            badge: "Prioritas Utama",
            hasPhoto: true,
            spritePosition: "0% 98%",
            desc: "Mesin cukur cordless premium dengan motor berkekuatan tinggi dan bilah stagger-tooth untuk fade yang sangat halus dan presisi cepat."
        },
        {
            id: 2,
            name: "Trimmer WAHL Detailer (Original)",
            category: "Mesin Ukir & Garis",
            price: 1000000,
            icon: "✂️",
            badge: "Presisi Tinggi",
            hasPhoto: true,
            spritePosition: "100% 2%",
            desc: "Mesin detailer T-Blade khusus untuk merapikan garis rambut, kumis, jenggot, dan ukiran hairline dengan ketajaman tingkat tinggi."
        },
        {
            id: 3,
            name: "Shaver WAHL Micro Power / Finale",
            category: "Finishing Klimis",
            price: 600000,
            icon: "🪒",
            badge: "Higienis & Aman",
            hasPhoto: true,
            spritePosition: "50% 2%",
            desc: "Pencukur ultra-close finish pengganti silet konvensional. Anti-iritasi, tidak melukai kulit pelanggan, dan jauh lebih higienis."
        },
        {
            id: 4,
            name: "Tas Barberpack Khusus Mobile",
            category: "Mobilitas & Proteksi",
            price: 350000,
            icon: "🎒",
            badge: "Home Service",
            hasPhoto: true,
            spritePosition: "0% 2%",
            desc: "Ransel khusus anti-air dengan kompartemen busa peredam guncangan untuk membawa seluruh mesin aman saat kunjungan ke rumah pelanggan."
        },
        {
            id: 5,
            name: "Set Gunting Rambut Kasho Japan",
            category: "Gunting Potong & Penipis",
            price: 300000,
            icon: "🗡️",
            badge: "Baja Jepang",
            hasPhoto: true,
            spritePosition: "100% 98%",
            desc: "Sepasang gunting potong lurus & gunting sasak penipis berbahan stainless steel Jepang presisi tinggi yang awet dan tidak menjepit rambut."
        },
        {
            id: 6,
            name: "Celemek (Apron) Barber Canvas/Leather",
            category: "Citra & Kebersihan",
            price: 150000,
            icon: "🦺",
            badge: "Standar Pro",
            hasPhoto: true,
            spritePosition: "50% 98%",
            desc: "Apron stylish berdaya tahan tinggi untuk menjaga higienitas kerja sekaligus memperkuat branding profesional saat melayani konsumen."
        },
        {
            id: 7,
            name: "Brosur Warna & Materi Promosi Digital",
            category: "Pemasaran & Edukasi",
            price: 400000,
            icon: "📣",
            badge: "Akusisi Pasar",
            hasPhoto: false,
            spritePosition: null,
            desc: "Cetak brosur warna, kartu nama layanan panggilan, dan anggaran promosi terarah media sosial untuk menjangkau keluarga & perkantoran."
        }
    ],

    // Data Rekening Donasi / Pinjaman Kebaikan
    bankAccounts: [
        {
            bankName: "Bank Negara Indonesia (BNI)",
            accountNumber: "2021806943",
            holderName: "Pangeran Indra Sakti",
            badge: "Bank Transfer",
            icon: "🏦"
        },
        {
            bankName: "Bank Rakyat Indonesia (BRI)",
            accountNumber: "548301045144533",
            holderName: "Pangeran Indra Sakti",
            badge: "Bank Transfer",
            icon: "🏛️"
        },
        {
            bankName: "SeaBank",
            accountNumber: "901458844863",
            holderName: "Pangeran Indra Sakti",
            badge: "Digital Banking",
            icon: "💳"
        },
        {
            bankName: "DANA (e-Wallet)",
            accountNumber: "081270362459",
            holderName: "Pangeran Indra Sakti",
            badge: "e-Wallet Instan",
            icon: "📱"
        },
        {
            bankName: "GoPay (e-Wallet)",
            accountNumber: "0895329429118",
            holderName: "Pangeran Indra Sakti",
            badge: "e-Wallet Instan",
            icon: "⚡"
        }
    ],

    // Galeri Media & Dokumentasi
    mediaGallery: [
        {
            id: 1,
            title: "Layanan Door-to-Door & Home Visit",
            category: "Layanan Langsung",
            desc: "Melayani potong rambut di rumah, kantor, atau acara keluarga dengan standar higienis dan profesional.",
            gradient: "from-blue-600 to-indigo-900",
            icon: "🏠"
        },
        {
            id: 2,
            title: "Standar Perlengkapan Barbershop Pro",
            category: "Perangkat Kerja",
            desc: "Peralatan terawat prima, mesin WAHL USA Original untuk hasil gradasi pangkas rambut yang rapi dan halus.",
            gradient: "from-amber-600 to-amber-950",
            icon: "💈"
        },
        {
            id: 3,
            title: "Pemberdayaan Pemuda Pesisir Selatan",
            category: "Cetak Biru Pelatihan",
            desc: "Membuka jalan kemandirian bagi pemuda yang ingin memiliki keahlian dan tidak berhenti di tengah jalan.",
            gradient: "from-emerald-700 to-teal-950",
            icon: "🌱"
        }
    ],

    // Suara Inisiator
    voiceNote: {
        title: "Pesan Tulus Dari Hati Inisiator",
        speaker: "Pangeran Indra Sakti",
        duration: "02:15",
        summary: "Ungkapan rasa syukur dan permohonan doa restu kepada sanak famili, kerabat di rantau, dan para dermawan agar ikhtiar ini dimudahkan Allah SWT."
    }
};
