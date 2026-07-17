<?php defined('BASEPATH') or exit('No direct script access allowed');
// Shared date filter bar included by each report view
$report_name = $report_name ?? '';
?>
<div class="xb-report-filters">
  <form method="get" id="xb-report-filter-form" class="xb-filters">
    <input type="date" name="date_from" class="xb-input xb-input-sm" value="<?php echo htmlspecialchars($filters['date_from'] ?? date('Y-01-01')); ?>">
    <input type="date" name="date_to" class="xb-input xb-input-sm" value="<?php echo htmlspecialchars($filters['date_to'] ?? date('Y-m-d')); ?>">
    <button type="submit" class="xb-btn xb-btn-secondary xb-btn-sm">Apply</button>
    <a href="?date_from=<?php echo date('Y-01-01'); ?>&date_to=<?php echo date('Y-m-d'); ?>" class="xb-btn xb-btn-secondary xb-btn-sm">YTD</a>
    <a href="?date_from=<?php echo date('Y-m-01'); ?>&date_to=<?php echo date('Y-m-d'); ?>" class="xb-btn xb-btn-secondary xb-btn-sm">MTD</a>
    <a href="<?php echo admin_url('xetuu_books/report_export?report=' . $report_name . '&' . http_build_query($filters ?? [])); ?>" class="xb-btn xb-btn-secondary xb-btn-sm">Export CSV</a>
  </form>
</div>
