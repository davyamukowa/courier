/* ══════════════════════════════════════════════════════════
   Xetuu Books — Global JS helpers
   ══════════════════════════════════════════════════════════ */

'use strict';

// ── Utility ──────────────────────────────────────────────────────────────────

function xbFmt(n) {
    return parseFloat(n || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function xbPost(url, data) {
    return fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    }).then(r => r.json());
}

function xbGet(url) {
    return fetch(url, {
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    }).then(r => r.json());
}

// ── Nav dropdown keyboard support ────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {
    // Close dropdowns on outside click
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.xb-has-dropdown')) {
            document.querySelectorAll('.xb-dropdown').forEach(d => d.style.display = '');
        }
    });

    // Keyboard: open dropdown on Enter
    document.querySelectorAll('.xb-nav-dropdown-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const dropdown = this.nextElementSibling;
            if (!dropdown) return;
            const isVisible = dropdown.style.display === 'block';
            document.querySelectorAll('.xb-dropdown').forEach(d => d.style.display = '');
            if (!isVisible) dropdown.style.display = 'block';
        });
    });

    // Flash messages: auto-dismiss after 5 s
    document.querySelectorAll('.xb-alert').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        }, 5000);
    });

    // Payment modal: amount cap to residual
    document.addEventListener('input', function (e) {
        if (e.target.id === 'pay_amount') {
            const max = parseFloat(e.target.dataset.max || 0);
            if (max && parseFloat(e.target.value) > max) {
                e.target.value = max.toFixed(2);
            }
        }
    });
});

// ── Invoice form: compute totals (live) ──────────────────────────────────────

function xbComputeLineTotals() {
    let untaxed = 0;
    const taxMap = {};

    document.querySelectorAll('.xb-line-row').forEach(function (row) {
        const qty     = parseFloat(row.querySelector('.xb-line-qty')?.value) || 0;
        const price   = parseFloat(row.querySelector('.xb-line-price')?.value) || 0;
        const disc    = parseFloat(row.querySelector('.xb-line-discount')?.value) || 0;
        const taxSel  = row.querySelector('.xb-line-tax');
        const rate    = parseFloat(taxSel?.selectedOptions[0]?.dataset.rate || 0);
        const incl    = taxSel?.selectedOptions[0]?.dataset.incl === '1';
        const taxName = taxSel?.selectedOptions[0]?.text || '';

        let sub = qty * price * (1 - disc / 100);
        let taxAmt = 0;

        if (rate > 0) {
            if (incl) {
                taxAmt = sub - sub / (1 + rate / 100);
                sub    = sub - taxAmt;
            } else {
                taxAmt = sub * rate / 100;
            }
        }

        untaxed += sub;
        if (taxName && taxAmt > 0) {
            taxMap[taxName] = (taxMap[taxName] || 0) + taxAmt;
        }

        const subtotalCell = row.querySelector('.xb-line-subtotal');
        if (subtotalCell) subtotalCell.textContent = xbFmt(sub);
    });

    const totalTax = Object.values(taxMap).reduce((a, b) => a + b, 0);

    const untaxedEl = document.getElementById('xb-total-untaxed');
    const amountEl  = document.getElementById('xb-total-amount');
    const taxLines  = document.getElementById('xb-tax-lines');

    if (untaxedEl) untaxedEl.textContent = xbFmt(untaxed);
    if (amountEl)  amountEl.textContent  = xbFmt(untaxed + totalTax);

    if (taxLines) {
        let html = '';
        for (const [name, amt] of Object.entries(taxMap)) {
            html += `<div class="xb-totals-row"><span>${name}</span><span>${xbFmt(amt)}</span></div>`;
        }
        taxLines.innerHTML = html;
    }
}

// ── Journal entry: balance checker ───────────────────────────────────────────

function xbCheckJEBalance() {
    let totalDebit = 0, totalCredit = 0;

    document.querySelectorAll('.xb-je-line-row').forEach(function (row) {
        totalDebit  += parseFloat(row.querySelector('.xb-je-debit')?.value) || 0;
        totalCredit += parseFloat(row.querySelector('.xb-je-credit')?.value) || 0;
    });

    const diffEl  = document.getElementById('xb-je-diff-msg');
    const diffRow = document.getElementById('xb-je-diff-row');
    const debEl   = document.getElementById('xb-je-total-debit');
    const crEl    = document.getElementById('xb-je-total-credit');

    if (debEl) debEl.innerHTML  = `<strong>${xbFmt(totalDebit)}</strong>`;
    if (crEl)  crEl.innerHTML   = `<strong>${xbFmt(totalCredit)}</strong>`;

    const diff = Math.abs(totalDebit - totalCredit);
    if (diffRow) diffRow.style.display = diff > 0.001 ? '' : 'none';
    if (diffEl)  diffEl.textContent    = `Difference: ${xbFmt(diff)} — entry must be balanced to post.`;

    return diff <= 0.001;
}

// ── Dashboard: mini chart (sparkline) if canvas present ──────────────────────

function xbRenderSparklines() {
    const canvases = document.querySelectorAll('.xb-sparkline');
    if (!canvases.length || !window.Chart) return;

    canvases.forEach(function (canvas) {
        const data   = JSON.parse(canvas.dataset.values || '[]');
        const labels = JSON.parse(canvas.dataset.labels || '[]');
        new window.Chart(canvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    borderColor: '#1a6b3a',
                    backgroundColor: 'rgba(26,107,58,.08)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 0
                }]
            },
            options: {
                plugins: {legend: {display: false}},
                scales: {x: {display: false}, y: {display: false}},
                elements: {line: {}},
                animation: false
            }
        });
    });
}

// ── Report filter: keyboard submit ───────────────────────────────────────────

document.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && e.target.closest('#xb-report-filter-form')) {
        e.target.closest('form').submit();
    }
});

// ── Global: close modal on Escape ────────────────────────────────────────────

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.xb-modal').forEach(m => m.style.display = 'none');
    }
});

// ── Init ─────────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {
    xbRenderSparklines();

    // Trigger line totals on any input in a line table
    document.addEventListener('change', function (e) {
        if (e.target.closest('.xb-line-row')) xbComputeLineTotals();
        if (e.target.closest('.xb-je-line-row')) xbCheckJEBalance();
    });
    document.addEventListener('input', function (e) {
        if (e.target.closest('.xb-line-row')) xbComputeLineTotals();
        if (e.target.closest('.xb-je-line-row')) xbCheckJEBalance();
    });
});
