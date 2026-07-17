<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .xb-dash-container {
        padding-bottom: 50px;
    }
    .xb-stat-card {
        background: linear-gradient(135deg, #ffffff 0%, #fcfdfd 100%);
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.04);
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
        margin-bottom: 25px;
    }
    .xb-stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.08);
    }
    .xb-stat-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 4px; height: 100%;
    }
    .xb-stat-ar::before { background: #10b981; }
    .xb-stat-ap::before { background: #ef4444; }
    .xb-stat-bank::before { background: #3b82f6; }
    .xb-stat-inv::before { background: #f59e0b; }

    .xb-stat-title {
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    .xb-stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }
    .xb-chart-container {
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.04);
        margin-bottom: 25px;
    }
    .xb-chart-header {
        font-size: 16px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>

<div class="xb-dash-container">
    <div class="row">
        <div class="col-md-12">
            <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">Financial Overview</h4>
        </div>
    </div>

    <!-- Key Metrics Row -->
    <div class="row">
        <div class="col-md-3">
            <div class="xb-stat-card xb-stat-bank">
                <div class="xb-stat-title">Cash & Bank Balance</div>
                <h3 class="xb-stat-value"><?php echo app_format_money($financials['bank'], get_base_currency()); ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="xb-stat-card xb-stat-ar">
                <div class="xb-stat-title">Total Receivables (AR)</div>
                <h3 class="xb-stat-value"><?php echo app_format_money($financials['ar'], get_base_currency()); ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="xb-stat-card xb-stat-ap">
                <div class="xb-stat-title">Total Payables (AP)</div>
                <h3 class="xb-stat-value"><?php echo app_format_money($financials['ap'], get_base_currency()); ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="xb-stat-card xb-stat-inv">
                <div class="xb-stat-title">Draft Invoices</div>
                <h3 class="xb-stat-value"><?php echo isset($inv_stats['to_validate']['count']) ? $inv_stats['to_validate']['count'] : 0; ?></h3>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Chart -->
        <div class="col-md-8">
            <div class="xb-chart-container">
                <div class="xb-chart-header">
                    <span>Income vs Expense (<?php echo date('Y'); ?>)</span>
                    <span class="badge bg-primary">YTD</span>
                </div>
                <canvas id="incomeExpenseChart" height="100"></canvas>
            </div>
        </div>

        <!-- Recent Activity / Bank Journals -->
        <div class="col-md-4">
            <div class="xb-chart-container">
                <div class="xb-chart-header">
                    <span>Bank Journals</span>
                </div>
                <?php if(empty($bank_journals)): ?>
                    <p class="text-muted">No bank journals found.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach($bank_journals as $j): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="border-left: 3px solid #3b82f6; margin-bottom: 8px; border-radius: 4px;">
                            <?php echo htmlspecialchars($j->name); ?>
                            <span class="badge" style="background:#e2e8f0; color:#475569;"><?php echo htmlspecialchars($j->code); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('incomeExpenseChart').getContext('2d');
    
    // Gradients for premium look
    let incomeGradient = ctx.createLinearGradient(0, 0, 0, 400);
    incomeGradient.addColorStop(0, 'rgba(16, 185, 129, 0.8)'); // emerald
    incomeGradient.addColorStop(1, 'rgba(16, 185, 129, 0.1)');
    
    let expenseGradient = ctx.createLinearGradient(0, 0, 0, 400);
    expenseGradient.addColorStop(0, 'rgba(239, 68, 68, 0.8)'); // red
    expenseGradient.addColorStop(1, 'rgba(239, 68, 68, 0.1)');

    const chartData = {
        labels: <?php echo json_encode($financials['chart_labels']); ?>,
        datasets: [
            {
                label: 'Income',
                data: <?php echo json_encode($financials['chart_income']); ?>,
                backgroundColor: incomeGradient,
                borderColor: '#10b981',
                borderWidth: 2,
                borderRadius: 4,
                fill: true,
                tension: 0.4
            },
            {
                label: 'Expense',
                data: <?php echo json_encode($financials['chart_expense']); ?>,
                backgroundColor: expenseGradient,
                borderColor: '#ef4444',
                borderWidth: 2,
                borderRadius: 4,
                fill: true,
                tension: 0.4
            }
        ]
    };

    new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleFont: { size: 13, family: 'Inter' },
                    bodyFont: { size: 13, family: 'Inter' },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false }
                },
                x: {
                    grid: { display: false, drawBorder: false }
                }
            }
        }
    });
});
</script>
