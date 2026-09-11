/**
 * Financial Feasibility & Qardhul Hasan Calculator Module
 * Langkisau Pangkas Online
 */

let budgetChartInstance = null;
let roiChartInstance = null;

// Format Angka ke Format Rupiah Indonesia
function formatRupiah(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(amount).replace('IDR', 'Rp');
}

// Inisialisasi Donut Chart Alokasi Anggaran Starter Kit (Rp 4.500.000)
function initBudgetChart() {
    const canvas = document.getElementById('budgetChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    const labels = APP_DATA.equipment.map(item => {
        return item.name.length > 20 ? item.name.substring(0, 18) + '...' : item.name;
    });
    const data = APP_DATA.equipment.map(item => item.price);

    const bgColors = [
        '#075985', // Ocean Deep
        '#0284c7', // Ocean Mid
        '#0ea5e9', // Ocean Light
        '#b45309', // Amber Brown
        '#047857', // Emerald
        '#64748b', // Slate
        '#d97706'  // Warm Gold
    ];

    budgetChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: bgColors,
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 8,
                        font: {
                            family: 'Plus Jakarta Sans',
                            size: 11
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const val = context.raw || 0;
                            const total = 4500000;
                            const pct = ((val / total) * 100).toFixed(1);
                            return ` ${context.label}: Rp ${val.toLocaleString('id-ID')} (${pct}%)`;
                        }
                    }
                }
            }
        }
    });
}

// Inisialisasi Grafik Amortisasi & Pengembalian Modal Qardhul Hasan
function initROIChart() {
    const canvas = document.getElementById('roiChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    roiChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Bulan 1', 'Bulan 2', 'Bulan 3', 'Bulan 4', 'Bulan 5', 'Bulan 6'],
            datasets: [
                {
                    label: 'Akumulasi Pengembalian Modal (Rp)',
                    data: [1000000, 2000000, 3000000, 4000000, 4500000, 4500000],
                    borderColor: '#047857',
                    backgroundColor: 'rgba(4, 120, 87, 0.12)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 3,
                    pointBackgroundColor: '#047857',
                    pointRadius: 4
                },
                {
                    label: 'Sisa Pinjaman Qardhul Hasan (Rp)',
                    data: [3500000, 2500000, 1500000, 500000, 0, 0],
                    borderColor: '#b45309',
                    borderDash: [5, 5],
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#b45309',
                    pointRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            family: 'Plus Jakarta Sans',
                            size: 11
                        },
                        padding: 10
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ` ${context.dataset.label}: Rp ${context.raw.toLocaleString('id-ID')}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + (value / 1000000).toFixed(1) + ' Jt';
                        },
                        font: { size: 10 }
                    },
                    grid: {
                        color: 'rgba(231, 229, 228, 0.6)'
                    }
                },
                x: {
                    grid: { display: false },
                    font: { size: 11 }
                }
            }
        }
    });
}

// Update Perhitungan Interaktif saat Slider Berubah
function updateROICalculations() {
    const weekdayInput = document.getElementById('weekdayInput');
    const weekendInput = document.getElementById('weekendInput');
    if (!weekdayInput || !weekendInput) return;

    const weekdayVal = parseInt(weekdayInput.value, 10);
    const weekendVal = parseInt(weekendInput.value, 10);

    const weekdayDisplay = document.getElementById('weekdayVal');
    const weekendDisplay = document.getElementById('weekendVal');
    if (weekdayDisplay) weekdayDisplay.innerText = `${weekdayVal} Orang / Hari`;
    if (weekendDisplay) weekendDisplay.innerText = `${weekendVal} Orang / Hari`;

    // Tarif Jasa Pangkas Panggilan: Rp 30.000 / Orang
    const tariff = 30000;
    const weekdayMonthly = weekdayVal * 22 * tariff; // 22 Hari Kerja
    const weekendMonthly = weekendVal * 8 * tariff;  // 8 Hari Weekend
    const totalMonthlyRev = weekdayMonthly + weekendMonthly;

    // Estimasi Biaya Operasional (Bensin, Sanitasi, Kuota Booking): ~15%
    const operationalCost = Math.round(totalMonthlyRev * 0.15);
    const grossProfit = totalMonthlyRev - operationalCost;

    // Pinjaman Modal Awal Rp 4.500.000
    const totalLoan = 4500000;
    // Alokasi Cicilan Pengembalian Bulanan Rp 1.000.000
    const monthlyRepayment = 1000000;
    const paybackMonths = (totalLoan / monthlyRepayment).toFixed(1);

    const monthlyRevEl = document.getElementById('monthlyRevenue');
    const paybackMonthsEl = document.getElementById('paybackMonths');
    const netIncomeEl = document.getElementById('netIncome');

    if (monthlyRevEl) monthlyRevEl.innerText = `Rp ${totalMonthlyRev.toLocaleString('id-ID')}`;
    if (paybackMonthsEl) paybackMonthsEl.innerText = `${paybackMonths} Bulan (Cepat & Rasional)`;
    if (netIncomeEl) netIncomeEl.innerText = `Rp ${(grossProfit - monthlyRepayment).toLocaleString('id-ID')}`;

    // Update Chart Sisa Pinjaman & Akumulasi
    if (roiChartInstance) {
        let remainingLoan = totalLoan;
        let cumRepaid = 0;
        const repaidArr = [];
        const remainingArr = [];

        for (let m = 1; m <= 6; m++) {
            const payThisMonth = Math.min(monthlyRepayment, remainingLoan);
            cumRepaid += payThisMonth;
            remainingLoan -= payThisMonth;

            repaidArr.push(cumRepaid);
            remainingArr.push(remainingLoan);
        }

        roiChartInstance.data.datasets[0].data = repaidArr;
        roiChartInstance.data.datasets[1].data = remainingArr;
        roiChartInstance.update();
    }
}
