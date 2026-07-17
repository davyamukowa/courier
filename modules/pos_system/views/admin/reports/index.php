<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="page-header-v2 clearfix">
          <h3 class="no-margin"><?php echo _l('pos_reports'); ?></h3>
        </div>
      </div>
    </div>

    <!-- Date filter -->
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <form method="GET" class="form-inline">
              <div class="form-group">
                <label class="control-label">Branch</label>
                <select name="branch_id" class="form-control mleft5">
                  <?php foreach ($branches as $b): ?>
                    <option value="<?php echo $b['id']; ?>" <?php if ((int)$this->input->get('branch_id') == $b['id']) echo 'selected'; ?>>
                      <?php echo htmlspecialchars($b['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group mleft5">
                <label class="control-label">From</label>
                <input type="date" name="from" class="form-control mleft5" value="<?php echo $from; ?>">
              </div>
              <div class="form-group mleft5">
                <label class="control-label">To</label>
                <input type="date" name="to" class="form-control mleft5" value="<?php echo $to; ?>">
              </div>
              <button type="submit" class="btn btn-success mleft5" style="background:#16a34a;border-color:#16a34a">Apply</button>
              <a href="#" onclick="window.print()" class="btn btn-default mleft5"><i class="fa fa-print"></i> Print</a>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- P&L Summary Cards -->
    <?php if (!empty($report)): ?>
    <div class="row" id="pos-report-summary">
      <div class="col-md-3">
        <div class="panel_s"><div class="panel-body text-center">
          <div class="text-muted">Net Revenue</div>
          <h3 class="text-success"><?php echo pos_format_currency($report['net_revenue'] ?? 0); ?></h3>
        </div></div>
      </div>
      <div class="col-md-3">
        <div class="panel_s"><div class="panel-body text-center">
          <div class="text-muted">COGS</div>
          <h3 class="text-danger"><?php echo pos_format_currency($report['cogs'] ?? 0); ?></h3>
        </div></div>
      </div>
      <div class="col-md-3">
        <div class="panel_s"><div class="panel-body text-center">
          <div class="text-muted">Gross Profit</div>
          <h3 class="text-info"><?php echo pos_format_currency($report['gross_profit'] ?? 0); ?></h3>
        </div></div>
      </div>
      <div class="col-md-3">
        <div class="panel_s"><div class="panel-body text-center">
          <div class="text-muted">VAT Collected</div>
          <h3><?php echo pos_format_currency($report['tax_collected'] ?? 0); ?></h3>
        </div></div>
      </div>
    </div>

    <!-- Daily sales chart placeholder (rendered by Vue/Chart.js via API) -->
    <div class="row">
      <div class="col-md-8">
        <div class="panel_s">
          <div class="panel-heading"><h4 class="panel-title">Daily Sales Trend</h4></div>
          <div class="panel-body">
            <canvas id="dailySalesChart" height="120"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="panel_s">
          <div class="panel-heading"><h4 class="panel-title">Payment Methods</h4></div>
          <div class="panel-body">
            <canvas id="paymentMethodChart" height="180"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Products table -->
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-heading">
            <h4 class="panel-title">Top Products by Revenue</h4>
          </div>
          <div class="panel-body">
            <table class="table dt-table" id="top-products-table">
              <thead>
                <tr>
                  <th>#</th><th>Product</th><th>Category</th>
                  <th>Qty Sold</th><th>Revenue</th><th>COGS</th>
                  <th>Gross Profit</th><th>Margin %</th>
                </tr>
              </thead>
              <tbody id="top-products-body">
                <tr><td colspan="8" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(async function() {
  const apiUrl = '<?php echo $api_url ?? base_url('pos_system/api'); ?>';
  const token  = '<?php echo $api_token ?? ''; ?>';
  const from   = '<?php echo $from; ?>';
  const to     = '<?php echo $to; ?>';
  const branchId = '<?php echo $branch_id ?? 0; ?>';

  const headers = { Authorization: `Bearer ${token}` };

  // Daily sales chart
  const dailyResp = await fetch(`${apiUrl}/reports/daily-sales?from=${from}&to=${to}&branch_id=${branchId}`, { headers });
  const dailyData = (await dailyResp.json()).data ?? [];

  new Chart(document.getElementById('dailySalesChart'), {
    type: 'line',
    data: {
      labels:   dailyData.map(d => d.sale_date),
      datasets: [{
        label:           'Net Sales',
        data:            dailyData.map(d => parseFloat(d.net_sales)),
        borderColor:     '#1d6fd4',
        backgroundColor: 'rgba(29,111,212,0.1)',
        tension:         0.3,
        fill:            true,
      }],
    },
    options: { responsive: true, plugins: { legend: { display: false } } },
  });

  // Payment method pie chart
  const pmResp = await fetch(`${apiUrl}/reports/payments?from=${from}&to=${to}&branch_id=${branchId}`, { headers });
  const pmData = (await pmResp.json()).data ?? [];

  new Chart(document.getElementById('paymentMethodChart'), {
    type: 'doughnut',
    data: {
      labels:   pmData.map(d => d.method_name),
      datasets: [{ data: pmData.map(d => parseFloat(d.total_amount)), backgroundColor: ['#1d6fd4','#28a745','#ffc107','#dc3545','#6f42c1','#17a2b8','#fd7e14','#20c997'] }],
    },
    options: { responsive: true },
  });

  // Top products table
  const prodResp = await fetch(`${apiUrl}/reports/products?from=${from}&to=${to}&branch_id=${branchId}&limit=20`, { headers });
  const prodData = (await prodResp.json()).data ?? [];

  const tbody = document.getElementById('top-products-body');
  tbody.innerHTML = prodData.map((p, i) => `
    <tr>
      <td>${i+1}</td>
      <td><strong>${p.product_name}</strong><br><small class="text-muted">${p.sku||''}</small></td>
      <td>${p.category_name||'—'}</td>
      <td>${parseFloat(p.qty_sold).toFixed(0)}</td>
      <td>${parseFloat(p.revenue).toLocaleString()}</td>
      <td>${parseFloat(p.cogs).toLocaleString()}</td>
      <td class="text-success">${parseFloat(p.gross_profit).toLocaleString()}</td>
      <td><span class="label label-${parseFloat(p.margin_pct)>30?'success':'warning'}">${p.margin_pct}%</span></td>
    </tr>
  `).join('') || '<tr><td colspan="8" class="text-center text-muted">No data</td></tr>';
})();
</script>

<?php init_tail(); ?>
