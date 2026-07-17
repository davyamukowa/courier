<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$report_types = isset($report_cards) ? $report_cards : [
    'summary'       => ['Summary Report',       'Gross, deductions, net pay per employee', 'fa-table',     '#2563eb'],
    'bank_transfer' => ['Bank Transfer List',   'Account numbers with net pay amounts',    'fa-bank',      '#16a34a'],
    'cost_centre'   => ['Cost Centre Report',   'Payroll costs grouped by department',     'fa-sitemap',   '#d97706'],
    'variance'      => ['Variance Report',      'Month-over-month salary changes',         'fa-line-chart','#9333ea'],
    'ytd'           => ['Year-to-Date (YTD)',   'Cumulative earnings and tax for the year','fa-calendar',  '#0891b2'],
];
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<div class="xhr-setup-page" style="padding:20px;">

    <!-- Page title + filter bar in one row -->
    <div style="display:flex; align-items:flex-end; gap:12px; flex-wrap:wrap; margin-bottom:16px;">
        <div style="margin-right:8px;">
            <div style="font-size:11px; color:#6b7280; margin-bottom:2px;">
                <a href="<?php echo $base.'/payroll'; ?>" style="color:#6b7280; text-decoration:none;">Payroll</a> / Reports
            </div>
            <h1 style="font-size:18px; font-weight:700; color:#111827; margin:0;">Payroll Reports</h1>
        </div>
        <div>
            <label style="font-size:10px; font-weight:600; color:#374151; text-transform:uppercase; display:block; margin-bottom:3px;">Company</label>
            <select id="report-company" class="form-control" style="min-width:160px; font-size:12px; height:30px; padding:2px 8px;">
                <?php foreach ($companies as $co): ?>
                <option value="<?php echo $co->id; ?>"><?php echo htmlspecialchars($co->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size:10px; font-weight:600; color:#374151; text-transform:uppercase; display:block; margin-bottom:3px;">From</label>
            <input type="date" id="report-from" class="form-control" style="font-size:12px; height:30px; padding:2px 8px;" value="<?php echo date('Y-m-01'); ?>">
        </div>
        <div>
            <label style="font-size:10px; font-weight:600; color:#374151; text-transform:uppercase; display:block; margin-bottom:3px;">To</label>
            <input type="date" id="report-to" class="form-control" style="font-size:12px; height:30px; padding:2px 8px;" value="<?php echo date('Y-m-t'); ?>">
        </div>
        <div>
            <label style="font-size:10px; font-weight:600; color:#374151; text-transform:uppercase; display:block; margin-bottom:3px;">Format</label>
            <select id="report-format" class="form-control" style="font-size:12px; height:30px; padding:2px 8px;">
                <option value="html">Preview</option>
                <option value="csv">CSV Export</option>
                <option value="pdf">PDF</option>
            </select>
        </div>
    </div>

    <!-- Report Cards -->
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:10px; margin-bottom:16px;">
        <?php foreach ($report_types as $key => [$title, $desc, $icon, $color]): ?>
        <div id="card-<?php echo $key; ?>"
             onclick="runReport('<?php echo $key; ?>')"
             style="background:#fff; border:1.5px solid #e5e7eb; border-top:3px solid <?php echo $color; ?>;
                    border-radius:8px; padding:12px 14px; cursor:pointer; transition:all .15s;">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:5px;">
                <div style="width:28px; height:28px; background:<?php echo $color; ?>18; border-radius:6px;
                            display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <i class="fa <?php echo $icon; ?>" style="color:<?php echo $color; ?>; font-size:13px;"></i>
                </div>
                <span style="font-size:12px; font-weight:700; color:#111827; line-height:1.2;"><?php echo htmlspecialchars($title); ?></span>
            </div>
            <div style="font-size:11px; color:#6b7280; line-height:1.4;"><?php echo htmlspecialchars($desc); ?></div>
            <div style="margin-top:8px;">
                <span style="font-size:10px; font-weight:700; color:<?php echo $color; ?>; text-transform:uppercase; letter-spacing:.04em;">
                    ▶ Run Report
                </span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Report Output Area -->
    <div id="report-output" style="display:none; background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div id="report-output-title" style="font-size:14px; font-weight:700; color:#111827;"></div>
            <div style="display:flex; gap:6px;">
                <button onclick="downloadReport('pdf')" class="btn btn-xs btn-default" style="border-radius:4px; font-size:11px;">
                    <i class="fa fa-file-pdf-o"></i> PDF
                </button>
                <button onclick="downloadReport('csv')" class="btn btn-xs btn-default" style="border-radius:4px; font-size:11px;">
                    <i class="fa fa-download"></i> CSV
                </button>
            </div>
        </div>
        <div id="report-content" style="font-size:12px;"></div>
    </div>
</div>

<style>
#report-output .table th, #report-output .table td { padding: 6px 10px; font-size: 12px; }
[id^="card-"] { user-select: none; }
[id^="card-"]:hover { border-color: #9ca3af !important; background: #f9fafb !important; box-shadow: 0 2px 8px rgba(0,0,0,.07); }
[id^="card-"].active-card { background: var(--card-bg, #eff6ff) !important; box-shadow: 0 2px 12px rgba(0,0,0,.1); }
</style>

<script>
var currentReport = null;
var CARD_COLORS = <?php
    $cc = [];
    foreach ($report_types as $key => $card) { $cc[$key] = $card[3]; }
    echo json_encode($cc);
?>;

function runReport(type) {
    currentReport = type;
    // Visual feedback on the active card
    document.querySelectorAll('[id^="card-"]').forEach(function(btn) {
        btn.classList.remove('active-card');
        btn.style.removeProperty('--card-color');
        btn.style.removeProperty('--card-bg');
    });
    var activeBtn = document.getElementById('card-' + type);
    if (activeBtn && CARD_COLORS[type]) {
        activeBtn.classList.add('active-card');
        activeBtn.style.setProperty('--card-color', CARD_COLORS[type]);
        activeBtn.style.setProperty('--card-bg', CARD_COLORS[type] + '18');
    }

    var company = document.getElementById('report-company').value;
    var from    = document.getElementById('report-from').value;
    var to      = document.getElementById('report-to').value;
    var format  = document.getElementById('report-format').value;
    var base    = '<?php echo $base; ?>';

    if (format === 'html') {
        var out = document.getElementById('report-output');
        out.style.display = 'block';
        document.getElementById('report-output-title').textContent = 'Loading…';
        document.getElementById('report-content').innerHTML =
            '<div style="text-align:center;padding:24px;color:#9ca3af;"><i class="fa fa-spinner fa-spin fa-2x"></i></div>';
        out.scrollIntoView({ behavior: 'smooth', block: 'start' });

        fetch(base + '/payroll/reporting/' + type + '?company_id=' + company + '&date_from=' + from + '&date_to=' + to + '&format=html', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.text(); })
        .then(function(html) {
            var titleEl = document.getElementById('report-output-title');
            // Format the type name nicely for the title
            var titleMap = <?php
                $tm = [];
                foreach ($report_types as $key => $card) { $tm[$key] = $card[0]; }
                echo json_encode($tm);
            ?>;
            titleEl.textContent = titleMap[type] || type.replace(/_/g,' ').toUpperCase();
            document.getElementById('report-content').innerHTML = html;
        })
        .catch(function(e) {
            document.getElementById('report-content').innerHTML =
                '<div style="color:#dc2626;padding:16px;">Error loading report: ' + e.message + '</div>';
        });
    } else {
        window.location = base + '/payroll/reporting/' + type +
            '?company_id=' + company + '&date_from=' + from + '&date_to=' + to + '&format=' + format;
    }
}

function downloadReport(format) {
    if (!currentReport) return;
    var company = document.getElementById('report-company').value;
    var from    = document.getElementById('report-from').value;
    var to      = document.getElementById('report-to').value;
    window.location = '<?php echo $base; ?>/payroll/reporting/' + currentReport +
        '?company_id=' + company + '&date_from=' + from + '&date_to=' + to + '&format=' + format;
}
</script>
<?php init_tail(); ?>
