// rekap-perkara.js — SIPP

document.addEventListener('DOMContentLoaded', function () {

    // ========================
    // Persist filter ke localStorage
    // ========================
    const FILTER_KEY = 'sipp_rekap_filter';

    if (typeof _rekap !== 'undefined' && _rekap.filterState) {
        localStorage.setItem(FILTER_KEY, JSON.stringify(_rekap.filterState));
    }

    // ========================
    // Mode tabs
    // ========================
    const modeInput = document.getElementById('modeInput');
    document.querySelectorAll('.mode-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            const mode = this.dataset.mode;
            document.querySelectorAll('.mode-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            if (modeInput) modeInput.value = mode;
            document.querySelectorAll('.filter-input-group').forEach(p => p.style.display = 'none');
            const panel = document.getElementById('panel-' + mode);
            if (panel) panel.style.display = '';
        });
    });

    // Auto-submit per hari/bulan
    document.querySelectorAll('.filter-input[type="date"], .filter-input[type="month"]').forEach(function (el) {
        el.addEventListener('change', function () {
            const mode = modeInput ? modeInput.value : '';
            if (mode === 'hari' || mode === 'bulan') {
                document.getElementById('filterForm')?.submit();
            }
        });
    });

    // ========================
    // Charts
    // ========================
    if (typeof Chart === 'undefined' || typeof _rekap === 'undefined') return;

    const donutOpts = (labels, data, colors) => ({
        type: 'doughnut',
        data: {
            labels,
            datasets: [{ data, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 10 }, padding: 8, boxWidth: 10 }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.parsed.toLocaleString('id')}`
                    }
                }
            }
        }
    });

    // Chart 1 — Jenis perkara (horizontal bar compact)
    const ctx1 = document.getElementById('chartJenis');
    if (ctx1 && _rekap.jenisData && _rekap.jenisData.length > 0) {
        const total = _rekap.total || 1;
        const labels = _rekap.jenisData.map(d => d.label);
        const values = _rekap.jenisData.map(d => d.jumlah);
        const colors = labels.map((_, i) => `rgba(10,122,94,${1 - (i/labels.length)*0.55})`);

        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderRadius: 4,
                    borderWidth: 0,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.parsed.x.toLocaleString('id')} (${Math.round(ctx.parsed.x/total*100)}%)`
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#f5f5f5' },
                        ticks: { font: { size: 9 }, color: '#aaa', precision: 0 },
                        border: { display: false }
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 9 },
                            color: '#555',
                            callback: v => {
                                const l = labels[v] ?? '';
                                return l.length > 18 ? l.slice(0, 18) + '…' : l;
                            }
                        },
                        border: { display: false }
                    }
                }
            }
        });
    }

    // Chart 2 — Cara daftar (donut)
    const ctx2 = document.getElementById('chartCara');
    if (ctx2 && (_rekap.eCourt + _rekap.manual) > 0) {
        new Chart(ctx2, donutOpts(
            ['E-Court', 'Manual'],
            [_rekap.eCourt, _rekap.manual],
            ['#f39c12', '#95a5a6']
        ));
    }

    // Chart 3 — Status (donut)
    const ctx3 = document.getElementById('chartStatus');
    if (ctx3 && (_rekap.putus + _rekap.minutasi + _rekap.dalamProses) > 0) {
        new Chart(ctx3, donutOpts(
            ['Putus', 'Minutasi', 'Dalam Proses'],
            [_rekap.putus, _rekap.minutasi, _rekap.dalamProses],
            ['#1D9E75', '#3498db', '#e74c3c']
        ));
    }
    // ========================
    // Preserve scroll position saat search
    // ========================
    const SCROLL_KEY = 'sipp_scroll_pos';

    // Restore scroll position setelah load
    const savedScroll = sessionStorage.getItem(SCROLL_KEY);
    if (savedScroll) {
        window.scrollTo(0, parseInt(savedScroll));
        sessionStorage.removeItem(SCROLL_KEY);
    }

    // Simpan posisi scroll sebelum form submit
    document.querySelectorAll('.table-search-bar, .detail-search-form').forEach(function(form) {
        form.addEventListener('submit', function() {
            sessionStorage.setItem(SCROLL_KEY, window.scrollY);
        });
    });

    // Simpan juga untuk per-page select
    document.querySelectorAll('.per-page-select').forEach(function(sel) {
        sel.addEventListener('change', function() {
            sessionStorage.setItem(SCROLL_KEY, window.scrollY);
        });
    });
});