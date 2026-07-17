<?php defined('BASEPATH') or exit('No direct script access allowed');
$report_name = 'balance_sheet';
$as_of  = $filters['date_to'] ?? date('Y-m-d');
$data   = $report_data ?? ['assets' => [], 'liabilities' => [], 'equity' => [], 'totals' => []];
?>
<div class="xb-report-filters">
  <form method="get" class="xb-filters">
    <label>As of date:</label>
    <input type="date" name="date_to" class="xb-input xb-input-sm" value="<?php echo htmlspecialchars($as_of); ?>">
    <button type="submit" class="xb-btn xb-btn-secondary xb-btn-sm">Apply</button>
    <a href="<?php echo admin_url('xetuu_books/report_export?report=balance_sheet&date_to=' . $as_of); ?>" class="xb-btn xb-btn-secondary xb-btn-sm">Export CSV</a>
  </form>
</div>

<div class="xb-report xb-report-bs">
  <h3 class="xb-report-heading">Balance Sheet — As of <?php echo _d($as_of); ?></h3>

  <?php
  function xb_render_section($title, $items, $total, $is_credit = false) {
    echo '<div class="xb-bs-section">';
    echo '<h4 class="xb-bs-section-title">' . htmlspecialchars($title) . '</h4>';
    echo '<table class="xb-table xb-report-table">';
    echo '<tbody>';
    foreach ($items as $row) {
        $b = $is_credit ? -$row['balance'] : $row['balance'];
        echo '<tr>';
        echo '<td>' . ($row['code'] ? '<span class="xb-muted">' . htmlspecialchars($row['code']) . '</span> ' : '') . htmlspecialchars($row['name']) . '</td>';
        echo '<td class="xb-num ' . ($b < 0 ? 'xb-text-danger' : '') . '">' . number_format(abs($b), 2) . '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '<tfoot><tr class="xb-report-total"><th>Total ' . htmlspecialchars($title) . '</th><th class="xb-num">' . number_format(abs($total), 2) . '</th></tr></tfoot>';
    echo '</table></div>';
  }
  ?>

  <div class="xb-bs-cols">
    <div class="xb-bs-col">
      <?php xb_render_section('Assets', $data['assets'], $data['totals']['total_assets'] ?? 0); ?>
    </div>
    <div class="xb-bs-col">
      <?php xb_render_section('Liabilities', $data['liabilities'], $data['totals']['total_liabilities'] ?? 0, true); ?>
      <?php xb_render_section('Equity', $data['equity'], $data['totals']['total_equity'] ?? 0, true); ?>
      <div class="xb-bs-check">
        <?php
        $ta  = $data['totals']['total_assets']      ?? 0;
        $tl  = $data['totals']['total_liabilities'] ?? 0;
        $te  = $data['totals']['total_equity']       ?? 0;
        $balanced = abs($ta - ($tl + $te)) < 0.01;
        ?>
        <span class="xb-badge <?php echo $balanced ? 'xb-badge-success' : 'xb-badge-danger'; ?>">
          <?php echo $balanced ? 'Balanced' : 'Out of balance by ' . number_format(abs($ta - ($tl + $te)), 2); ?>
        </span>
      </div>
    </div>
  </div>
</div>
