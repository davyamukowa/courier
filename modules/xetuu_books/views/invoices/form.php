<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
/* ── Page shell ─────────────────────────────────────────────── */
.xb-content-wrapper { padding: 0 !important; }
.xb-inv-page { display: flex; flex-direction: column; min-height: calc(100vh - 60px); background: #f3f4f6; }

/* ── Top header bar ─────────────────────────────────────────── */
.xb-inv-header {
    background: #fff;
    padding: 14px 24px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.xb-inv-header-left { display: flex; align-items: center; gap: 10px; }
.xb-inv-back-btn {
    display: flex; align-items: center; gap: 6px;
    color: #6b7280; font-size: 13px; font-weight: 500;
    text-decoration: none; padding: 6px 10px; border-radius: 6px;
    border: 1px solid #e5e7eb; background: #fff; transition: all .15s;
}
.xb-inv-back-btn:hover { background: #f9fafb; color: #374151; text-decoration: none; }
.xb-inv-header-title {
    display: flex; flex-direction: column; gap: 1px;
}
.xb-inv-breadcrumb { font-size: 11px; color: #9ca3af; }
.xb-inv-breadcrumb a { color: #9ca3af; text-decoration: none; }
.xb-inv-breadcrumb a:hover { color: #1a6b3a; }
.xb-inv-title { font-size: 18px; font-weight: 700; color: #111827; margin: 0; display: flex; align-items: center; gap: 8px; }
.xb-inv-header-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.xb-btn-primary {
    display: inline-flex; align-items: center; gap: 6px;
    background: #1a6b3a; color: #fff; border: none; border-radius: 7px;
    padding: 9px 18px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background .15s;
}
.xb-btn-primary:hover { background: #155a30; color: #fff; }
.xb-btn-outline {
    display: inline-flex; align-items: center; gap: 6px;
    background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 7px;
    padding: 8px 14px; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: none; transition: all .15s;
}
.xb-btn-outline:hover { background: #f9fafb; border-color: #9ca3af; color: #111827; text-decoration: none; }

/* ── Status badge ───────────────────────────────────────────── */
.xb-inv-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px;
    background: #f3f4f6; color: #6b7280;
}
.xb-inv-badge.sent     { background: #dbeafe; color: #1d4ed8; }
.xb-inv-badge.paid     { background: #dcfce7; color: #15803d; }
.xb-inv-badge.overdue  { background: #fee2e2; color: #b91c1c; }
.xb-inv-badge.draft    { background: #f3f4f6; color: #6b7280; }

/* ── KPI stats strip ────────────────────────────────────────── */
.xb-inv-stats-strip {
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    padding: 0 24px;
    display: flex;
    align-items: stretch;
    gap: 0;
    overflow-x: auto;
}
.xb-inv-stat {
    display: flex; flex-direction: column; justify-content: center;
    padding: 12px 24px 12px 0; margin-right: 24px;
    border-right: 1px solid #f0f0f0; gap: 2px; min-width: 120px;
    flex-shrink: 0;
}
.xb-inv-stat:last-child { border-right: none; }
.xb-stat-label { font-size: 10.5px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .05em; }
.xb-stat-val   { font-size: 20px; font-weight: 800; color: #111827; line-height: 1.1; }
.xb-stat-sub   { font-size: 11px; color: #6b7280; margin-top: 1px; }
.xb-stat-trend-up   { color: #16a34a; font-weight: 600; font-size: 11px; }
.xb-stat-trend-down { color: #dc2626; font-weight: 600; font-size: 11px; }

/* ── Main content layout ────────────────────────────────────── */
.xb-inv-body {
    display: flex; align-items: flex-start; flex: 1; min-height: 0;
}
.xb-inv-main {
    flex: 1; min-width: 0; padding: 20px 24px;
}

/* ── Right sidebar ──────────────────────────────────────────── */
.xb-inv-sidebar {
    width: 280px; flex-shrink: 0;
    background: #fff; border-left: 1px solid #e5e7eb;
    padding: 16px; min-height: calc(100vh - 140px);
    display: flex; flex-direction: column; gap: 14px;
}

/* Widget card */
.xb-sw { border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; }
.xb-sw-head {
    padding: 11px 14px;
    font-size: 13px; font-weight: 700; color: #111827;
    border-bottom: 1px solid #e5e7eb; background: #f9fafb;
    display: flex; align-items: center; justify-content: space-between;
}
.xb-sw-body { padding: 14px; }

/* Metric row (conversion rates) */
.xb-metric-row { margin-bottom: 14px; }
.xb-metric-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
.xb-metric-label { font-size: 12px; font-weight: 600; color: #374151; }
.xb-metric-pct { font-size: 13px; font-weight: 800; color: #111827; }
.xb-metric-track { height: 6px; background: #f3f4f6; border-radius: 4px; overflow: hidden; margin-bottom: 3px; }
.xb-metric-fill { height: 100%; border-radius: 4px; transition: width .4s ease; }
.xb-metric-fill.green  { background: #16a34a; }
.xb-metric-fill.blue   { background: #3b82f6; }
.xb-metric-sub { font-size: 10.5px; color: #9ca3af; }

/* Decline rate hero card */
.xb-decline-card {
    background: linear-gradient(135deg, #fff7ed 0%, #fff 100%);
    border: 1px solid #fed7aa; border-radius: 10px; padding: 14px;
    display: flex; align-items: center; justify-content: space-between;
}
.xb-decline-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; margin-bottom: 2px; }
.xb-decline-val   { font-size: 26px; font-weight: 800; color: #dc2626; line-height: 1; }
.xb-decline-trend { font-size: 11px; color: #16a34a; font-weight: 600; margin-top: 2px; }
.xb-decline-icon  { font-size: 28px; color: #fed7aa; }

/* Recent payments */
.xb-recent-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 0; border-bottom: 1px solid #f3f4f6;
}
.xb-recent-item:last-child { border-bottom: none; }
.xb-recent-left { display: flex; align-items: center; gap: 9px; }
.xb-avatar {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; flex-shrink: 0;
}
.xb-avatar.green  { background: #dcfce7; color: #166534; }
.xb-avatar.blue   { background: #dbeafe; color: #1e40af; }
.xb-avatar.purple { background: #ede9fe; color: #5b21b6; }
.xb-recent-name { font-size: 12.5px; font-weight: 600; color: #111827; }
.xb-recent-meta { font-size: 10.5px; color: #6b7280; }
.xb-recent-amt  { font-size: 13px; font-weight: 700; color: #16a34a; }

/* Setup grid */
.xb-setup-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 9px; }
.xb-setup-tile {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;
    padding: 12px 8px; text-align: center; cursor: pointer; transition: all .15s;
    text-decoration: none; display: block;
}
.xb-setup-tile:hover { border-color: #16a34a; box-shadow: 0 3px 8px rgba(22,163,74,.1); background: #f0fdf4; text-decoration: none; }
.xb-setup-tile i { font-size: 18px; color: #16a34a; display: block; margin-bottom: 5px; }
.xb-setup-tile span { font-size: 11px; font-weight: 600; color: #374151; }
.xb-setup-cta {
    width: 100%; background: #1a6b3a; color: #fff; border: none; border-radius: 7px;
    padding: 10px; font-size: 13px; font-weight: 600; margin-top: 10px;
    cursor: pointer; transition: background .15s; display: flex; align-items: center; justify-content: center; gap: 6px;
}
.xb-setup-cta:hover { background: #155a30; color: #fff; }

/* View-all btn */
.xb-view-all-btn {
    width: 100%; background: #fff; border: 1px solid #e5e7eb; border-radius: 6px;
    padding: 8px; font-size: 12px; font-weight: 600; color: #374151; margin-top: 10px;
    cursor: pointer; transition: all .15s; display: block; text-align: center; text-decoration: none;
}
.xb-view-all-btn:hover { border-color: #9ca3af; color: #111827; text-decoration: none; }

/* ── Invoice form overrides ─────────────────────────────────── */
.xb-inv-main .panel_s { border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.xb-inv-main .panel_s .panel-body { padding: 20px; }


</style>

<!-- ── Page shell ──────────────────────────────────────────── -->
<div class="xb-inv-page">

  <!-- Header -->
  <div class="xb-inv-header">
    <div class="xb-inv-header-left">
      <a href="<?php echo admin_url('xetuu_books/invoices'); ?>" class="xb-inv-back-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
        Invoices
      </a>
      <div class="xb-inv-header-title">
        <div class="xb-inv-breadcrumb">
          <a href="<?php echo admin_url('xetuu_books'); ?>">Xetuu Books</a>
          <span> / </span>
          <a href="<?php echo admin_url('xetuu_books/invoices'); ?>">Invoices</a>
          <span> / </span><?php echo isset($invoice) ? e(format_invoice_number($invoice)) : 'New'; ?>
        </div>
        <h1 class="xb-inv-title">
          <i class="fa fa-file-text-o" style="color:#16a34a;font-size:16px;"></i>
          <?php echo isset($invoice) ? e(format_invoice_number($invoice)) : _l('create_new_invoice'); ?>
          <?php if (isset($invoice)): ?>
            <?php
              $s = $invoice->status;
              $badge_map = [
                1 => ['draft','Draft'], 2 => ['sent','Sent'], 3 => ['paid','Paid'],
                4 => ['overdue','Overdue'], 5 => ['draft','Cancelled'], 6 => ['draft','Draft'],
              ];
              [$bc, $bl] = $badge_map[$s] ?? ['draft','Draft'];
            ?>
            <span class="xb-inv-badge <?php echo $bc; ?>"><?php echo $bl; ?></span>
          <?php else: ?>
            <span class="xb-inv-badge draft">New Draft</span>
          <?php endif; ?>
        </h1>
      </div>
    </div>
    <div class="xb-inv-header-actions">
      <?php if (isset($invoice)): ?>
        <?php
          $_tooltip = _l('invoice_sent_to_email_tooltip');
          $_tooltip_already_send = '';
          if ($invoice->sent == 1 && is_date($invoice->datesend)) {
              $_tooltip_already_send = _l('invoice_already_send_to_client_tooltip', time_ago($invoice->datesend));
          }
        ?>
        <!-- PDF Dropdown -->
        <div class="btn-group">
            <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding:8px 14px;border-radius:6px;display:inline-flex;align-items:center;gap:6px;">
                <i class="fa-regular fa-file-pdf"></i> PDF <span class="caret"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
                <li><a href="<?= admin_url('invoices/pdf/' . $invoice->id . '?output_type=I'); ?>"><?= _l('view_pdf'); ?></a></li>
                <li><a href="<?= admin_url('invoices/pdf/' . $invoice->id . '?output_type=I'); ?>" target="_blank"><?= _l('view_pdf_in_new_window'); ?></a></li>
                <li><a href="<?= admin_url('invoices/pdf/' . $invoice->id); ?>"><?= _l('download'); ?></a></li>
                <li><a href="<?= admin_url('invoices/pdf/' . $invoice->id . '?print=true'); ?>" target="_blank"><?= _l('print'); ?></a></li>
            </ul>
        </div>

        <!-- Send to Client -->
        <?php if (!empty($invoice->clientid)) { ?>
            <span <?php if ($invoice->status == Invoices_model::STATUS_CANCELLED) { echo 'data-toggle="tooltip" data-title="' . _l('invoice_cancelled_email_disabled') . '"'; } ?>>
                <a href="#" class="invoice-send-to-client btn btn-default <?php if ($invoice->status == Invoices_model::STATUS_CANCELLED) { echo 'disabled'; } ?>" data-toggle="tooltip" title="<?= e($_tooltip); ?>" data-placement="bottom" style="padding:8px 14px;border-radius:6px;display:inline-flex;align-items:center;gap:6px;">
                    <span data-toggle="tooltip" data-title="<?= e($_tooltip_already_send); ?>"><i class="fa-regular fa-envelope"></i></span>
                </a>
            </span>
        <?php } ?>

        <!-- Hook for e-Invoice -->
        <?php hooks()->do_action('before_invoice_preview_more_menu_button', $invoice); ?>

        <!-- More Dropdown -->
        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding:8px 14px;border-radius:6px;display:inline-flex;align-items:center;gap:6px;">
                <?= _l('more'); ?> <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right">
                <li><a href="<?= site_url('invoice/' . $invoice->id . '/' . $invoice->hash) ?>" target="_blank"><?= _l('view_invoice_as_customer_tooltip'); ?></a></li>
                <?php hooks()->do_action('after_invoice_view_as_client_link', $invoice); ?>
                
                <?php if (is_invoice_overdue($invoice) && is_invoices_overdue_reminders_enabled()) { ?>
                    <li><a href="<?= admin_url('invoices/send_overdue_notice/' . $invoice->id); ?>"><?= _l('send_overdue_notice_tooltip'); ?></a></li>
                <?php } ?>
                
                <?php if ($invoice->status != Invoices_model::STATUS_CANCELLED && staff_can('create', 'credit_notes') && !empty($invoice->clientid)) { ?>
                    <li><a href="<?= admin_url('credit_notes/credit_note_from_invoice/' . $invoice->id); ?>" id="invoice_create_credit_note" data-status="<?= e($invoice->status); ?>"><?= _l('create_credit_note'); ?></a></li>
                <?php } ?>
                
                <li><a href="#" data-toggle="modal" data-target="#sales_attach_file"><?= _l('invoice_attach_file'); ?></a></li>
                
                <?php if (staff_can('create', 'invoices')) { ?>
                    <li><a href="<?= admin_url('xetuu_books/xb_copy_invoice/' . $invoice->id); ?>"><?= _l('invoice_copy'); ?></a></li>
                <?php } ?>
                
                <?php if ($invoice->sent == 0) { ?>
                    <li><a href="<?= admin_url('invoices/mark_as_sent/' . $invoice->id); ?>"><?= _l('invoice_mark_as_sent'); ?></a></li>
                <?php } ?>
                
                <?php if (staff_can('edit', 'invoices') || staff_can('create', 'invoices')) { ?>
                    <li>
                        <?php if ($invoice->status != Invoices_model::STATUS_CANCELLED && $invoice->status != Invoices_model::STATUS_PAID && $invoice->status != Invoices_model::STATUS_PARTIALLY) { ?>
                            <a href="<?= admin_url('invoices/mark_as_cancelled/' . $invoice->id); ?>"><?= e(_l('invoice_mark_as', _l('invoice_status_cancelled'))); ?></a>
                        <?php } elseif ($invoice->status == Invoices_model::STATUS_CANCELLED) { ?>
                            <a href="<?= admin_url('invoices/unmark_as_cancelled/' . $invoice->id); ?>"><?= e(_l('invoice_unmark_as', _l('invoice_status_cancelled'))); ?></a>
                        <?php } ?>
                    </li>
                <?php } ?>
                
                <?php if (!in_array($invoice->status, [Invoices_model::STATUS_PAID, Invoices_model::STATUS_CANCELLED, Invoices_model::STATUS_DRAFT]) && staff_can('edit', 'invoices') && $invoice->duedate && is_invoices_overdue_reminders_enabled()) { ?>
                    <li>
                        <?php if ($invoice->cancel_overdue_reminders == 1) { ?>
                            <a href="<?= admin_url('invoices/resume_overdue_reminders/' . $invoice->id); ?>"><?= _l('resume_overdue_reminders'); ?></a>
                        <?php } else { ?>
                            <a href="<?= admin_url('invoices/pause_overdue_reminders/' . $invoice->id); ?>"><?= _l('pause_overdue_reminders'); ?></a>
                        <?php } ?>
                    </li>
                <?php } ?>
                
                <?php if ((get_option('delete_only_on_last_invoice') == 1 && is_last_invoice($invoice->id)) || (get_option('delete_only_on_last_invoice') == 0)) { ?>
                    <?php if (staff_can('delete', 'invoices')) { ?>
                        <li data-toggle="tooltip" data-title="<?= _l('delete_invoice_tooltip'); ?>">
                            <a href="<?= admin_url('xetuu_books/xb_delete_invoice/' . $invoice->id); ?>" class="text-danger delete-text _delete"><?= _l('delete_invoice'); ?></a>
                        </li>
                    <?php } ?>
                <?php } ?>
                <?php hooks()->do_action('after_invoice_preview_more_menu'); ?>
            </ul>
        </div>

        <!-- Payment Button -->
        <?php if (staff_can('create', 'payments') && abs($invoice->total) > 0) { ?>
            <a href="#" onclick="xb_record_payment(<?= e($invoice->id); ?>); return false;" class="btn btn-success <?php if ($invoice->status == Invoices_model::STATUS_PAID || $invoice->status == Invoices_model::STATUS_CANCELLED) { echo 'disabled'; } ?>" style="padding:8px 14px;border-radius:6px;display:inline-flex;align-items:center;gap:6px;">
                <i class="fa fa-plus"></i> <?= _l('payment'); ?>
            </a>
        <?php } ?>
      <?php endif; ?>
      
      <!-- Save Button -->
      <button type="submit" form="invoice-form" class="xb-btn-primary">
        <i class="fa fa-floppy-o"></i>
        <?php echo isset($invoice) ? _l('save') : _l('add'); ?>
      </button>
    </div>
  </div>

  <!-- Stats strip -->
  <div class="xb-inv-stats-strip">
    <div class="xb-inv-stat">
      <div class="xb-stat-label">Draft to Sent</div>
      <div class="xb-stat-val"><?php echo $xb_stat_draft_to_sent; ?>%</div>
      <div class="xb-stat-sub">All time conversion</div>
    </div>
    <div class="xb-inv-stat">
      <div class="xb-stat-label">Sent to Paid</div>
      <div class="xb-stat-val"><?php echo $xb_stat_sent_to_paid; ?>%</div>
      <div class="xb-stat-sub">All time conversion</div>
    </div>
    <div class="xb-inv-stat">
      <div class="xb-stat-label">Overdue Rate</div>
      <div class="xb-stat-val"><?php echo $xb_stat_overdue_rate; ?>%</div>
      <div class="xb-stat-sub">Active invoices</div>
    </div>
    <div class="xb-inv-stat">
      <div class="xb-stat-label">Avg. Days to Pay</div>
      <div class="xb-stat-val"><?php echo $xb_stat_avg_days; ?></div>
      <div class="xb-stat-sub">Across all payments</div>
    </div>
  </div>

  <!-- Body -->
  <div class="xb-inv-body">

    <!-- Main form area -->
    <div class="xb-inv-main">
      <?php echo form_open($this->uri->uri_string(), ['id' => 'invoice-form', 'class' => '_transaction_form invoice-form']); ?>
      <?php if (isset($invoice)) { echo form_hidden('isedit'); } ?>

      <?php /* ── Analytic Account (Cost Centre) at top ── */
        if (function_exists('xb_render_analytic_field')):
            $inv_id_for_analytic = isset($invoice) ? (int)$invoice->id : 0;
      ?>
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; gap:20px;">
          <!-- Analytic Field (reduced width) -->
          <div style="flex:0 0 50%; max-width: 400px;">
              <?php echo xb_render_analytic_field('invoice', $inv_id_for_analytic, 'Analytic Account (Cost Centre)'); ?>
          </div>
          
          <!-- Stamp -->
          <?php
            $stamp_enabled = function_exists('get_instance') ? get_instance()->xb_config->get_setting('xb_stamp_enabled') : '0';
            if ($stamp_enabled !== '0'):
                $top_text = function_exists('get_instance') ? get_instance()->xb_config->get_setting('xb_stamp_top_text') : 'OFFICE OF THE MUNICIPAL TREASURER';
                if (!$top_text) $top_text = 'OFFICE OF THE MUNICIPAL TREASURER';
                $bottom_text = function_exists('get_instance') ? get_instance()->xb_config->get_setting('xb_stamp_bottom_text') : 'MUNICIPALITY OF SAGNAY';
                if (!$bottom_text) $bottom_text = 'MUNICIPALITY OF SAGNAY';
                
                $base_stamp_color = function_exists('get_instance') ? get_instance()->xb_config->get_setting('xb_stamp_color') : '#2563eb';
                if (!$base_stamp_color) $base_stamp_color = '#2563eb';

                $stamp_date = '';
                $stamp_time = '';
                $stamp_by = '';
                $stamp_text = 'NEW';
                $status_color = '#9ca3af'; // gray

                if (isset($invoice)) {
                    $s = $invoice->status;
                    $stamp_text = 'UNPAID';
                    $status_color = '#ef4444'; // red
                    if ($s == Invoices_model::STATUS_PAID) {
                        $stamp_text = 'PAID';
                        $status_color = '#10b981'; // green
                    } elseif ($s == Invoices_model::STATUS_PARTIALLY) {
                        $stamp_text = 'PARTIAL';
                        $status_color = '#f59e0b'; // orange
                    } elseif ($s == Invoices_model::STATUS_CANCELLED) {
                        $stamp_text = 'CANCELLED';
                        $status_color = '#6b7280'; // gray
                    } elseif ($s == Invoices_model::STATUS_DRAFT) {
                        $stamp_text = 'DRAFT';
                        $status_color = '#6b7280'; // gray
                    }

                    if ($s == Invoices_model::STATUS_PAID || $s == Invoices_model::STATUS_PARTIALLY) {
                        $CI = &get_instance();
                        $CI->db->where('invoiceid', $invoice->id);
                        $CI->db->order_by('id', 'desc');
                        $CI->db->limit(1);
                        $last_payment = $CI->db->get(db_prefix() . 'invoicepaymentrecords')->row();
                        
                        if ($last_payment) {
                            $stamp_date = date('Y-m-d', strtotime($last_payment->date));
                            $stamp_time = date('H:i A', strtotime($last_payment->daterecorded));
                            
                            $CI->db->where('rel_id', $invoice->id);
                            $CI->db->where('rel_type', 'invoice');
                            $CI->db->where('description', 'invoice_activity_payment_made_by_staff');
                            $CI->db->order_by('id', 'desc');
                            $CI->db->limit(1);
                            $act = $CI->db->get(db_prefix() . 'sales_activity')->row();
                            if ($act && $act->staffid) {
                                $CI->db->where('staffid', $act->staffid);
                                $staff = $CI->db->get(db_prefix() . 'staff')->row();
                                if ($staff) {
                                    $stamp_by = $staff->firstname . ' ' . $staff->lastname;
                                }
                            } else {
                                $stamp_by = 'System';
                            }
                        }
                    }
                }
          ?>
          <div style="flex-shrink:0; margin-right:40px; margin-top:-10px; margin-bottom:-10px;">
              <svg viewBox="0 0 200 200" width="140" height="140" style="transform: rotate(-15deg); opacity: 0.85; filter: drop-shadow(0px 0px 1px rgba(0,0,0,0.1));">
                <!-- Outer borders -->
                <circle cx="100" cy="100" r="96" fill="none" stroke="<?php echo $base_stamp_color; ?>" stroke-width="3.5" />
                <circle cx="100" cy="100" r="90" fill="none" stroke="<?php echo $base_stamp_color; ?>" stroke-width="1.5" />
                <circle cx="100" cy="100" r="63" fill="none" stroke="<?php echo $base_stamp_color; ?>" stroke-width="1.5" />
                
                <!-- Paths for text -->
                <defs>
                  <!-- Top arc path (clockwise) -->
                  <path id="top-arc" d="M 23.5,100 A 76.5,76.5 0 0,1 176.5,100" />
                  <!-- Bottom arc path (counter-clockwise) -->
                  <path id="bottom-arc" d="M 176.5,100 A 76.5,76.5 0 0,1 23.5,100" />
                </defs>

                <!-- Top Text -->
                <text font-family="'Arial Black', 'Impact', sans-serif" font-size="12" font-weight="bold" fill="<?php echo $base_stamp_color; ?>" letter-spacing="0.5">
                  <textPath href="#top-arc" startOffset="50%" text-anchor="middle" lengthAdjust="spacingAndGlyphs"><?php echo e($top_text); ?></textPath>
                </text>
                
                <!-- Bottom Text -->
                <text font-family="'Arial Black', 'Impact', sans-serif" font-size="12" font-weight="bold" fill="<?php echo $base_stamp_color; ?>" letter-spacing="0.5">
                  <textPath href="#bottom-arc" startOffset="50%" text-anchor="middle" lengthAdjust="spacingAndGlyphs"><?php echo e($bottom_text); ?></textPath>
                </text>
                
                <!-- Left and Right Stars -->
                <text x="14" y="105" font-family="'Arial Black', 'Impact', sans-serif" font-size="16" fill="<?php echo $base_stamp_color; ?>">★</text>
                <text x="170" y="105" font-family="'Arial Black', 'Impact', sans-serif" font-size="16" fill="<?php echo $base_stamp_color; ?>">★</text>
                
                <!-- Center Text (Status, By, Date, Time) -->
                <!-- Status (Uses custom fixed status colors) -->
                <text x="100" y="85" font-family="'Arial Black', 'Impact', sans-serif" font-size="24" font-weight="900" fill="<?php echo $status_color; ?>" text-anchor="middle" letter-spacing="2"><?php echo $stamp_text; ?></text>
                
                <!-- By -->
                <text x="45" y="112" font-family="'Courier New', Courier, monospace" font-size="11" font-weight="bold" fill="<?php echo $base_stamp_color; ?>">By: </text>
                <text x="70" y="110" font-family="'Satisfy', 'Brush Script MT', cursive" font-size="13" fill="<?php echo $base_stamp_color; ?>"><?php echo substr(e($stamp_by), 0, 16); ?></text>
                <line x1="68" y1="114" x2="155" y2="114" stroke="<?php echo $base_stamp_color; ?>" stroke-width="1.5" />

                <!-- Date -->
                <text x="45" y="130" font-family="'Courier New', Courier, monospace" font-size="11" font-weight="bold" fill="<?php echo $base_stamp_color; ?>">Date: </text>
                <text x="82" y="129" font-family="'Courier New', Courier, monospace" font-size="11" font-weight="bold" fill="<?php echo $base_stamp_color; ?>"><?php echo e($stamp_date); ?></text>
                <line x1="80" y1="132" x2="155" y2="132" stroke="<?php echo $base_stamp_color; ?>" stroke-width="1.5" />

                <!-- Time -->
                <text x="45" y="148" font-family="'Courier New', Courier, monospace" font-size="11" font-weight="bold" fill="<?php echo $base_stamp_color; ?>">Time: </text>
                <text x="82" y="147" font-family="'Courier New', Courier, monospace" font-size="11" font-weight="bold" fill="<?php echo $base_stamp_color; ?>"><?php echo e($stamp_time); ?></text>
                <line x1="80" y1="150" x2="155" y2="150" stroke="<?php echo $base_stamp_color; ?>" stroke-width="1.5" />
              </svg>
          </div>
          <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php
        // Pre-load all active clients so Bootstrap Select shows the full list immediately
        $pre_clients = $this->db
            ->select('c.userid, IFNULL(NULLIF(c.company,""), CONCAT(ct.firstname," ",ct.lastname)) AS cname')
            ->from(db_prefix() . 'clients c')
            ->join(db_prefix() . 'contacts ct', 'ct.userid = c.userid AND ct.is_primary = 1', 'left')
            ->where('c.active', 1)
            ->order_by('cname', 'ASC')
            ->get()->result_array();

        $sel_val = isset($invoice) ? $invoice->clientid : ($customer_id ?? '');
        $client_opts = '<option value=""></option>';
        foreach ($pre_clients as $c) {
            $s = ($sel_val == $c['userid']) ? ' selected' : '';
            $client_opts .= '<option value="' . (int)$c['userid'] . '"' . $s . '>' . htmlspecialchars($c['cname'], ENT_QUOTES) . '</option>';
        }

        $invoice_template_data = [
            'invoice'               => isset($invoice) ? $invoice : null,
            'invoices_to_merge'     => isset($invoices_to_merge) && is_array($invoices_to_merge) ? $invoices_to_merge : [],
            'expenses_to_bill'      => isset($expenses_to_bill) && is_array($expenses_to_bill) ? $expenses_to_bill : [],
            'billable_tasks'        => isset($billable_tasks) && is_array($billable_tasks) ? $billable_tasks : [],
            'payment_modes'         => isset($payment_modes) && is_array($payment_modes) ? $payment_modes : [],
            'taxes'                 => isset($taxes) && is_array($taxes) ? $taxes : [],
            'ajaxItems'             => isset($ajaxItems) ? (bool) $ajaxItems : false,
            'items'                 => isset($items) && is_array($items) ? $items : [],
            'items_groups'          => isset($items_groups) && is_array($items_groups) ? $items_groups : [],
            'currencies'            => isset($currencies) && is_array($currencies) ? $currencies : [],
            'base_currency'         => isset($base_currency) ? $base_currency : null,
            'staff'                 => isset($staff) && is_array($staff) ? $staff : [],
            'customer_id'           => isset($customer_id) ? $customer_id : '',
            'template'              => isset($template) ? $template : null,
            'template_name'         => isset($template_name) ? $template_name : '',
            'template_system_name'   => isset($template_system_name) ? $template_system_name : '',
            'template_id'           => isset($template_id) ? $template_id : '',
            'template_disabled'     => isset($template_disabled) ? (bool) $template_disabled : false,
            'credits_available'     => isset($credits_available) ? $credits_available : 0,
            'open_credits'          => isset($open_credits) && is_array($open_credits) ? $open_credits : [],
            'customer_currency'     => isset($customer_currency) ? $customer_currency : null,
        ];

        $html = $this->load->view('admin/invoices/invoice_template', $invoice_template_data, true);

        // Change clientid from ajax-search to selectpicker so Bootstrap Select handles it
        $html = preg_replace('/(id="clientid"[^>]+class=")[^"]*"/', '$1selectpicker"', $html);

        // Replace the clientid select contents with our pre-loaded client options
        $html = preg_replace_callback(
            '/(<select[^>]+id="clientid"[^>]*>)(.*?)(<\/select>)/s',
            function ($m) use ($client_opts) { return $m[1] . $client_opts . $m[3]; },
            $html
        );

        echo $html;
      ?>
      <?php echo form_close(); ?>
      <?php $this->load->view('admin/invoice_items/item'); ?>
    </div>

    <!-- Right sidebar -->
    <div class="xb-inv-sidebar">

      <!-- Overdue Rate hero -->
      <div class="xb-decline-card">
        <div>
          <div class="xb-decline-label">Overdue Rate</div>
          <div class="xb-decline-val"><?php echo $xb_stat_overdue_rate; ?>%</div>
          <div class="xb-decline-trend">Overall overdue percentage</div>
        </div>
        <div class="xb-decline-icon"><i class="fa fa-exclamation-circle" style="color:#f97316;"></i></div>
      </div>

      <!-- Conversion metrics -->
      <div class="xb-sw">
        <div class="xb-sw-head">
          Invoice Velocity
          <span style="font-size:10px;font-weight:500;color:#9ca3af;">Last 30 days</span>
        </div>
        <div class="xb-sw-body">

          <div class="xb-metric-row">
            <div class="xb-metric-top">
              <span class="xb-metric-label">Draft to Sent</span>
              <span class="xb-stat-trend-up"></span>
            </div>
            <div class="xb-metric-track"><div class="xb-metric-fill green" style="width:<?php echo $xb_stat_draft_to_sent; ?>%"></div></div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <span class="xb-metric-sub">Percent converted to Sent</span>
              <span style="font-size:12px;font-weight:700;color:#111827;"><?php echo $xb_stat_draft_to_sent; ?>%</span>
            </div>
          </div>

          <div class="xb-metric-row" style="margin-bottom:0">
            <div class="xb-metric-top">
              <span class="xb-metric-label">Sent to Paid</span>
              <span class="xb-stat-trend-down"></span>
            </div>
            <div class="xb-metric-track"><div class="xb-metric-fill blue" style="width:<?php echo $xb_stat_sent_to_paid; ?>%"></div></div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <span class="xb-metric-sub">Percent Paid / Part Paid</span>
              <span style="font-size:12px;font-weight:700;color:#111827;"><?php echo $xb_stat_sent_to_paid; ?>%</span>
            </div>
          </div>

        </div>
      </div>

      <!-- Recent Payments -->
      <div class="xb-sw">
        <div class="xb-sw-head">
          Recent Payments
          <span style="font-size:10px;font-weight:500;color:#9ca3af;">Today</span>
        </div>
        <div class="xb-sw-body" style="padding-top:4px;padding-bottom:4px;">

          <?php if(empty($xb_recent_payments)): ?>
            <div style="padding:20px;text-align:center;font-size:12px;color:#9ca3af;">No recent payments recorded.</div>
          <?php else: ?>
            <?php 
              $colors = ['green','blue','purple']; 
              $idx = 0;
            ?>
            <?php foreach($xb_recent_payments as $rp): ?>
              <?php 
                $cc = $colors[$idx % 3]; $idx++;
                $name = empty($rp['company']) ? 'Unknown' : $rp['company'];
                $initials = strtoupper(substr($name,0,2));
              ?>
              <div class="xb-recent-item" style="cursor:pointer; transition: background 0.2s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'" onclick="window.location.href='<?php echo admin_url('xetuu_books/xb_payment/'.$rp['id']); ?>'">
                <div class="xb-recent-left">
                  <div class="xb-avatar <?php echo $cc; ?>"><?php echo $initials; ?></div>
                  <div>
                    <div class="xb-recent-name"><?php echo htmlspecialchars($name); ?></div>
                    <div class="xb-recent-meta"><?php echo $rp['prefix'].$rp['number']; ?> &bull; <?php echo _dt($rp['date']); ?></div>
                  </div>
                </div>
                <div class="tw-flex tw-items-center tw-gap-3">
                    <span class="xb-recent-amt">+<?php echo app_format_money($rp['amount'], $base_currency->name); ?></span>
                    <a href="<?php echo admin_url('payments/pdf/'.$rp['id']); ?>" target="_blank" onclick="event.stopPropagation();" class="btn btn-default btn-xs" style="padding:2px 6px;" title="Download PDF Receipt"><i class="fa-regular fa-file-pdf tw-text-red-500"></i> PDF</a>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

        </div>
        <div style="padding: 0 14px 12px;">
          <a href="<?php echo admin_url('xetuu_books/payments'); ?>" class="xb-view-all-btn">View All Activity</a>
        </div>
      </div>

      <!-- Invoice Setup -->
      <div class="xb-sw" style="background:#f9fafb;">
        <div class="xb-sw-head">Invoice Setup</div>
        <div class="xb-sw-body">
          <p style="font-size:12px;color:#6b7280;margin:0 0 12px;">Manage your billing terms and templates in one place.</p>
          <div class="xb-setup-grid">
            <a href="<?php echo admin_url('invoices/settings'); ?>" class="xb-setup-tile">
              <i class="fa fa-file-text-o"></i>
              <span>Templates</span>
            </a>
            <a href="<?php echo admin_url('invoices/settings'); ?>" class="xb-setup-tile">
              <i class="fa fa-cogs"></i>
              <span>Settings</span>
            </a>
            <a href="<?php echo admin_url('subscriptions'); ?>" class="xb-setup-tile">
              <i class="fa fa-refresh"></i>
              <span>Recurring</span>
            </a>
            <a href="<?php echo admin_url('payment_modes'); ?>" class="xb-setup-tile">
              <i class="fa fa-credit-card"></i>
              <span>Payments</span>
            </a>
          </div>
          <button class="xb-setup-cta" onclick="window.location='<?php echo admin_url('invoices/settings'); ?>'">
            <i class="fa fa-bolt"></i> Quick Settings
          </button>
        </div>
      </div>

    </div><!-- /sidebar -->
  </div><!-- /body -->
</div><!-- /page -->

<script>
$(function() {
    // Bootstrap Select is initialized by layout.php with all clients pre-loaded from PHP.
    
    // Initialize standard Perfex Invoice calculations and validations
    if(typeof validate_invoice_form === 'function') {
        validate_invoice_form();
    }
    if(typeof init_currency === 'function') {
        init_currency();
    }
    if(typeof init_ajax_project_search_by_customer_id === 'function') {
        init_ajax_project_search_by_customer_id();
    }
    if(typeof init_ajax_search === 'function' && $('#item_select').length) {
        init_ajax_search('items', '#item_select.ajax-search', undefined, admin_url + 'items/search');
    }
});

    // --- Xetuu Books: Intercept Native Invoice Actions ---
    var xb_toggle_selectors = [
        '.xb-inv-header-actions .dropdown-menu a[href*="mark_as_sent"]',
        '.xb-inv-header-actions .dropdown-menu a[href*="mark_as_cancelled"]',
        '.xb-inv-header-actions .dropdown-menu a[href*="unmark_as_cancelled"]',
        '.xb-inv-header-actions .dropdown-menu a[href*="pause_overdue_reminders"]',
        '.xb-inv-header-actions .dropdown-menu a[href*="resume_overdue_reminders"]',
        '.xb-inv-header-actions .dropdown-menu a[href*="send_overdue_notice"]'
    ].join(', ');
    
    $('body').on('click', xb_toggle_selectors, function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        $(this).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
        $.get(url).always(function() {
            window.location.reload();
        });
    });

    // Intercept payment modal form submission
    $('body').on('submit', '#record_payment_form', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
        $.post(form.attr('action'), form.serialize()).always(function() {
            window.location.reload();
        });
    });

    // Intercept send to client email modal form
    $('body').on('submit', '#send_invoice_form', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
        $.post(form.attr('action'), form.serialize()).always(function() {
            window.location.href = admin_url + 'xetuu_books/invoices';
        });
    });

    // Custom Payment Modal Wrapper
    function xb_record_payment(id) {
        if (typeof id == "undefined" || id === "") {
            return;
        }
        var url = admin_url + "invoices/record_invoice_payment_ajax/" + id;
        $.get(url, function(response) {
            var modalHtml = '<div class="modal fade" id="xb_payment_modal" tabindex="-1" role="dialog"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title">Record Payment</h4></div><div class="modal-body">' + response + '</div></div></div></div>';
            
            $('#xb_payment_modal').remove();
            $('body').append(modalHtml);
            
            // Clean up the injected Perfex panel since we are in a modal
            $('#xb_payment_modal .panel-heading').hide();
            $('#xb_payment_modal .panel-footer').hide();
            $('#xb_payment_modal .panel_s').removeClass('panel_s').css('margin-bottom', '0');
            
            // Add native modal footer
            $('#xb_payment_modal .modal-content').append('<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button><button type="submit" form="record_payment_form" class="btn btn-success">Save Payment</button></div>');
            
            $('#xb_payment_modal').modal('show');
        });
    }
</script>

<!-- Load Modals for Action Buttons -->
<?php $this->load->view('admin/invoices/invoice_send_to_client'); ?>
<?php $this->load->view('admin/credit_notes/apply_invoice_credits'); ?>
<?php $this->load->view('admin/credit_notes/invoice_create_credit_note_confirm'); ?>
<?php if (isset($invoice)) { ?>
    <div class="modal fade" id="sales_attach_file" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel"><?= _l('invoice_attach_file'); ?></h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?= form_open_multipart('admin/invoices/upload_file', ['id'=>'sales-upload', 'class'=>'dropzone']); ?>
                            <input type="file" name="file" multiple />
                            <?= form_close(); ?>
                            <div class="row mtop15" id="sales_uploaded_files_preview"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
