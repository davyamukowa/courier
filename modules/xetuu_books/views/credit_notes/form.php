<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
/* ── Page shell ─────────────────────────────────────────────── */
.xb-content-wrapper { padding: 0 !important; }
.xb-inv-page { display: flex; flex-direction: column; min-height: calc(100vh - 60px); background: #f3f4f6; font-family: 'Inter', sans-serif; }

/* ── Top header bar ─────────────────────────────────────────── */
.xb-inv-header {
    background: #fff; padding: 14px 24px; border-bottom: 1px solid #e5e7eb;
    display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
}
.xb-inv-header-left { display: flex; align-items: center; gap: 10px; }
.xb-inv-back-btn {
    display: flex; align-items: center; gap: 6px; color: #6b7280; font-size: 13px; font-weight: 500;
    text-decoration: none; padding: 6px 10px; border-radius: 6px; border: 1px solid #e5e7eb; background: #fff; transition: all .15s;
}
.xb-inv-back-btn:hover { background: #f9fafb; color: #374151; text-decoration: none; }
.xb-inv-header-title { display: flex; flex-direction: column; gap: 1px; }
.xb-inv-breadcrumb { font-size: 11px; color: #9ca3af; }
.xb-inv-breadcrumb a { color: #9ca3af; text-decoration: none; }
.xb-inv-breadcrumb a:hover { color: #1a6b3a; }
.xb-inv-title { font-size: 18px; font-weight: 700; color: #111827; margin: 0; display: flex; align-items: center; gap: 8px; }
.xb-inv-header-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.xb-btn-primary {
    display: inline-flex; align-items: center; gap: 6px; background: #1a6b3a; color: #fff; border: none; border-radius: 7px;
    padding: 9px 18px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background .15s;
}
.xb-btn-primary:hover { background: #155a30; color: #fff; }
.xb-btn-outline {
    display: inline-flex; align-items: center; gap: 6px; background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 7px;
    padding: 8px 14px; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: none; transition: all .15s;
}
.xb-btn-outline:hover { background: #f9fafb; border-color: #9ca3af; color: #111827; text-decoration: none; }

/* ── Status badge ───────────────────────────────────────────── */
.xb-inv-badge {
    display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px;
    background: #f3f4f6; color: #6b7280;
}
.xb-inv-badge.sent     { background: #dbeafe; color: #1d4ed8; }
.xb-inv-badge.paid     { background: #dcfce7; color: #15803d; }
.xb-inv-badge.overdue  { background: #fee2e2; color: #b91c1c; }
.xb-inv-badge.draft    { background: #f3f4f6; color: #6b7280; }

/* ── Stats Strip ───────────────────────────────────────────── */
.xb-inv-stats-strip {
    display: flex; background: #fff; border-bottom: 1px solid #e5e7eb; padding: 16px 24px; gap: 40px;
}
.xb-inv-stat { display: flex; flex-direction: column; gap: 2px; }
.xb-stat-label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
.xb-stat-val { font-size: 20px; font-weight: 800; color: #111827; line-height: 1.1; }
.xb-stat-sub { font-size: 11px; color: #9ca3af; }

/* ── Main content layout ────────────────────────────────────── */
.xb-inv-body { display: flex; align-items: flex-start; flex: 1; min-height: 0; }
.xb-inv-main { flex: 1; min-width: 0; padding: 20px 24px; position: relative; }
.xb-inv-sidebar {
    width: 280px; flex-shrink: 0; background: #fff; border-left: 1px solid #e5e7eb;
    padding: 16px; min-height: calc(100vh - 140px); display: flex; flex-direction: column; gap: 14px;
}
.xb-inv-main .panel_s { border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.06); border: none; }
.xb-inv-main .panel_s .panel-body { padding: 20px; }

/* ── Right Sidebar Widgets ──────────────────────────────────── */
.xb-decline-card {
    background: #fff8f1; border: 1px solid #fed7aa; border-radius: 8px; padding: 14px;
    display: flex; justify-content: space-between; align-items: flex-start;
}
.xb-decline-label { font-size: 11px; font-weight: 700; color: #9a3412; text-transform: uppercase; letter-spacing: 0.5px; }
.xb-decline-val { font-size: 24px; font-weight: 800; color: #c2410c; margin: 4px 0 2px; }
.xb-decline-trend { font-size: 11px; color: #9a3412; }
.xb-sw { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
.xb-sw-head { padding: 12px 16px; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: 13px; color: #111827; display: flex; justify-content: space-between; align-items: center; }
.xb-sw-body { padding: 16px; }
.xb-metric-row { margin-bottom: 14px; }
.xb-metric-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
.xb-metric-label { font-size: 13px; font-weight: 600; color: #374151; }
.xb-metric-track { height: 6px; background: #f3f4f6; border-radius: 3px; overflow: hidden; margin-bottom: 6px; }
.xb-metric-fill { height: 100%; border-radius: 3px; }
.xb-metric-fill.green { background: #16a34a; }
.xb-metric-fill.blue { background: #2563eb; }
.xb-metric-sub { font-size: 11px; color: #6b7280; }
.xb-recent-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 16px; border-bottom: 1px solid #f3f4f6; }
.xb-recent-item:last-child { border-bottom: none; }
.xb-recent-left { display: flex; align-items: center; gap: 10px; }
.xb-avatar { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; }
.xb-avatar.green { background: #dcfce7; color: #166534; }
.xb-avatar.blue { background: #dbeafe; color: #1e40af; }
.xb-avatar.purple { background: #f3e8ff; color: #6b21a8; }
.xb-recent-name { font-size: 13px; font-weight: 600; color: #111827; line-height: 1.2; }
.xb-recent-meta { font-size: 11px; color: #6b7280; margin-top: 2px; }
.xb-recent-amt { font-size: 13px; font-weight: 700; color: #059669; }
.xb-view-all-btn { display: block; width: 100%; text-align: center; font-size: 12px; font-weight: 600; color: #4b5563; padding: 8px 0; background: #f9fafb; border-radius: 6px; text-decoration: none; margin-top: 8px; }
.xb-view-all-btn:hover { background: #f3f4f6; color: #111827; }
</style>

<!-- ── Page shell ──────────────────────────────────────────── -->
<div class="xb-inv-page">

  <!-- Header -->
  <div class="xb-inv-header">
    <div class="xb-inv-header-left">
      <a href="<?php echo admin_url('xetuu_books/credit_notes'); ?>" class="xb-inv-back-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
        Credit Notes
      </a>
      <div class="xb-inv-header-title">
        <div class="xb-inv-breadcrumb">
          <a href="<?php echo admin_url('xetuu_books'); ?>">Xetuu Books</a>
          <span> / </span>
          <a href="<?php echo admin_url('xetuu_books/credit_notes'); ?>">Credit Notes</a>
          <span> / </span><?php echo isset($credit_note) ? e(format_credit_note_number($credit_note->id)) : 'New'; ?>
        </div>
        <h1 class="xb-inv-title">
          <i class="fa fa-file-text-o" style="color:#16a34a;font-size:16px;"></i>
          <?php echo isset($credit_note) ? e(format_credit_note_number($credit_note->id)) : _l('new_credit_note'); ?>
          <?php if (isset($credit_note)): ?>
            <?php
              $s = $credit_note->status;
              $badge_map = [
                1 => ['draft','Open'], 2 => ['paid','Closed'], 3 => ['overdue','Void']
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
      <?php if (isset($credit_note)): ?>
        <!-- PDF Dropdown -->
        <div class="btn-group">
            <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding:8px 14px;border-radius:6px;display:inline-flex;align-items:center;gap:6px;">
                <i class="fa-regular fa-file-pdf"></i> PDF <span class="caret"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
                <li><a href="<?= admin_url('credit_notes/pdf/' . $credit_note->id . '?output_type=I'); ?>"><?= _l('view_pdf'); ?></a></li>
                <li><a href="<?= admin_url('credit_notes/pdf/' . $credit_note->id . '?output_type=I'); ?>" target="_blank"><?= _l('view_pdf_in_new_window'); ?></a></li>
                <li><a href="<?= admin_url('credit_notes/pdf/' . $credit_note->id); ?>"><?= _l('download'); ?></a></li>
                <li><a href="<?= admin_url('credit_notes/pdf/' . $credit_note->id . '?print=true'); ?>" target="_blank"><?= _l('print'); ?></a></li>
            </ul>
        </div>

        <!-- Send to Client -->
        <?php if (!empty($credit_note->clientid)) { ?>
            <a href="#" class="btn btn-default credit-note-send-to-client" style="padding:8px 14px;border-radius:6px;display:inline-flex;align-items:center;gap:6px;">
                <i class="fa-regular fa-envelope"></i>
            </a>
        <?php } ?>

        <!-- Refund / Apply -->
        <?php if ($credit_note->status == 1 && staff_can('edit', 'credit_notes')): ?>
            <a href="#" data-toggle="modal" data-target="#apply_credits" class="xb-btn-outline">
                <i class="fa fa-exchange"></i> <?= _l('apply_to_invoice'); ?>
            </a>
            <a href="#" onclick="xb_refund_credit_note(<?= $credit_note->id; ?>); return false;" class="xb-btn-outline">
                <i class="fa fa-reply"></i> <?= _l('credit_note_refund'); ?>
            </a>
        <?php endif; ?>

        <!-- More Dropdown -->
        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding:8px 14px;border-radius:6px;display:inline-flex;align-items:center;gap:6px;">
                <?= _l('more'); ?> <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right">
                <?php if ($credit_note->status == 1) { ?>
                    <li><a href="<?= admin_url('credit_notes/mark_action_status/2/' . $credit_note->id); ?>"><?= _l('credit_note_mark_as_open'); ?></a></li>
                <?php } ?>
                
                <?php if (staff_can('delete', 'credit_notes')) { ?>
                    <li>
                        <a href="<?= admin_url('credit_notes/delete/' . $credit_note->id); ?>" class="text-danger delete-text _delete"><?= _l('delete_credit_note'); ?></a>
                    </li>
                <?php } ?>
            </ul>
        </div>
      <?php endif; ?>
      
      <!-- Save Button -->
      <button type="button" onclick="$('#credit-note-form').submit();" class="xb-btn-primary">
        <i class="fa fa-floppy-o"></i>
        <?php echo isset($credit_note) ? _l('save') : _l('add'); ?>
      </button>
    </div>
  </div>

  <!-- Stats strip -->
  <div class="xb-inv-stats-strip">
    <div class="xb-inv-stat">
      <div class="xb-stat-label">Credit Notes Created</div>
      <div class="xb-stat-val"><?php echo $xb_stat_draft_to_sent; ?>%</div>
      <div class="xb-stat-sub">Of total issued</div>
    </div>
    <div class="xb-inv-stat">
      <div class="xb-stat-label">Closed Rate</div>
      <div class="xb-stat-val"><?php echo $xb_stat_sent_to_paid; ?>%</div>
      <div class="xb-stat-sub">Refunded/Applied</div>
    </div>
    <div class="xb-inv-stat">
      <div class="xb-stat-label">Void Rate</div>
      <div class="xb-stat-val"><?php echo $xb_stat_overdue_rate; ?>%</div>
      <div class="xb-stat-sub">Overall void percentage</div>
    </div>
  </div>

  <div class="xb-inv-body">
    <div class="xb-inv-main">
        <?= form_open(admin_url('xetuu_books/credit_note_form/' . (isset($credit_note) ? $credit_note->id : '')), ['id' => 'credit-note-form', 'class' => '_transaction_form credit-note-form']);
if (isset($credit_note)) {
    echo form_hidden('isedit');
}
?>
          <?php /* ── Analytic Account (Cost Centre) at top ── */
            if (function_exists('xb_render_analytic_field')):
                $cn_id_for_analytic = isset($credit_note) ? (int)$credit_note->id : 0;
          ?>
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; gap:20px;">
              <!-- Analytic Field -->
              <div style="flex:0 0 50%; max-width: 400px; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 14px; border-radius: 8px;">
                  <?php echo xb_render_analytic_field('credit_note', $cn_id_for_analytic, 'Analytic Account (Cost Centre)'); ?>
                  <p style="font-size:11px; color:#15803d; margin:4px 0 0;">Track this document\'s spend against a cost centre or project.</p>
              </div>
              
              <!-- Stamp -->
              <?php
                $stamp_enabled = function_exists('get_instance') ? get_instance()->xb_config->get_setting('xb_stamp_enabled') : '0';
                if ($stamp_enabled !== '0' && isset($credit_note)):
                    $top_text = function_exists('get_instance') ? get_instance()->xb_config->get_setting('xb_stamp_top_text') : 'OFFICE OF THE MUNICIPAL TREASURER';
                    if (!$top_text) $top_text = 'OFFICE OF THE MUNICIPAL TREASURER';
                    $bottom_text = function_exists('get_instance') ? get_instance()->xb_config->get_setting('xb_stamp_bottom_text') : 'MUNICIPALITY OF SAGNAY';
                    if (!$bottom_text) $bottom_text = 'MUNICIPALITY OF SAGNAY';
                    
                    $base_stamp_color = function_exists('get_instance') ? get_instance()->xb_config->get_setting('xb_stamp_color') : '#2563eb';
                    if (!$base_stamp_color) $base_stamp_color = '#2563eb';

                    $stamp_date = '';
                    $stamp_by = '';
                    $stamp_text = 'OPEN';
                    $status_color = '#6b7280'; // gray

                    $s = $credit_note->status;
                    if ($s == 2) { // Closed
                        $stamp_text = 'CLOSED';
                        $status_color = '#10b981'; // green
                        $stamp_date = date('Y-m-d', strtotime($credit_note->date));
                        $stamp_by = 'System';
                    } elseif ($s == 3) { // Void
                        $stamp_text = 'VOID';
                        $status_color = '#ef4444'; // red
                    }
              ?>
              <div style="flex-shrink:0; margin-right:40px; margin-top:-10px; margin-bottom:-10px; pointer-events:none;">
                  <svg viewBox="0 0 200 200" width="140" height="140" style="transform: rotate(-15deg); opacity: 0.85; filter: drop-shadow(0px 0px 1px rgba(0,0,0,0.1));">
                    <circle cx="100" cy="100" r="96" fill="none" stroke="<?php echo $base_stamp_color; ?>" stroke-width="3.5" />
                    <circle cx="100" cy="100" r="90" fill="none" stroke="<?php echo $base_stamp_color; ?>" stroke-width="1.5" />
                    <circle cx="100" cy="100" r="63" fill="none" stroke="<?php echo $base_stamp_color; ?>" stroke-width="1.5" />
                    
                    <defs>
                      <path id="top-arc" d="M 23.5,100 A 76.5,76.5 0 0,1 176.5,100" />
                      <path id="bottom-arc" d="M 176.5,100 A 76.5,76.5 0 0,1 23.5,100" />
                    </defs>

                    <text font-family="'Arial Black', 'Impact', sans-serif" font-size="12" font-weight="bold" fill="<?php echo $base_stamp_color; ?>" letter-spacing="0.5">
                      <textPath href="#top-arc" startOffset="50%" text-anchor="middle" lengthAdjust="spacingAndGlyphs"><?php echo e($top_text); ?></textPath>
                    </text>
                    
                    <text font-family="'Arial Black', 'Impact', sans-serif" font-size="12" font-weight="bold" fill="<?php echo $base_stamp_color; ?>" letter-spacing="0.5">
                      <textPath href="#bottom-arc" startOffset="50%" text-anchor="middle" lengthAdjust="spacingAndGlyphs"><?php echo e($bottom_text); ?></textPath>
                    </text>
                    
                    <text x="14" y="105" font-family="'Arial Black', 'Impact', sans-serif" font-size="16" fill="<?php echo $base_stamp_color; ?>">★</text>
                    <text x="170" y="105" font-family="'Arial Black', 'Impact', sans-serif" font-size="16" fill="<?php echo $base_stamp_color; ?>">★</text>
                    
                    <text x="100" y="85" font-family="'Arial Black', 'Impact', sans-serif" font-size="24" font-weight="900" fill="<?php echo $status_color; ?>" text-anchor="middle" letter-spacing="2"><?php echo $stamp_text; ?></text>
                    
                    <?php if($s == 2): ?>
                    <text x="45" y="125" font-family="'Courier New', Courier, monospace" font-size="11" font-weight="bold" fill="<?php echo $base_stamp_color; ?>">Date: </text>
                    <text x="82" y="124" font-family="'Courier New', Courier, monospace" font-size="11" font-weight="bold" fill="<?php echo $base_stamp_color; ?>"><?php echo e($stamp_date); ?></text>
                    <line x1="80" y1="127" x2="155" y2="127" stroke="<?php echo $base_stamp_color; ?>" stroke-width="1.5" />
                    <?php endif; ?>
                  </svg>
              </div>
              <?php endif; ?>
          </div>
          <?php endif; ?>
            <div class="col-md-12">
                
                <div class="panel_s credit_note accounting-template">
                    <div class="additional"></div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="f_client_id">
                                    <div class="form-group select-placeholder">
                                        <label for="clientid"
                                            class="control-label"><?= _l('client'); ?></label>
                                        <?php
// Pre-load all active clients so Bootstrap Select shows the full list immediately
$pre_clients = $this->db
    ->select('c.userid, IFNULL(NULLIF(c.company,""), CONCAT(ct.firstname," ",ct.lastname)) AS cname')
    ->from(db_prefix() . 'clients c')
    ->join(db_prefix() . 'contacts ct', 'ct.userid = c.userid AND ct.is_primary = 1', 'left')
    ->where('c.active', 1)
    ->order_by('cname', 'ASC')
    ->get()->result_array();

$sel_val = isset($credit_note) ? $credit_note->clientid : ($customer_id ?? '');
?>
<select id="clientid" name="clientid" data-width="100%" class="selectpicker" data-live-search="true" data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
    <option value=""></option>
    <?php foreach ($pre_clients as $c): ?>
        <option value="<?php echo (int)$c['userid']; ?>" <?php echo ($sel_val == $c['userid']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($c['cname'], ENT_QUOTES); ?>
        </option>
    <?php endforeach; ?>
</select>
                                    </div>
                                </div>
                                <div class="form-group projects-wrapper<?php if ((! isset($credit_note)) || (isset($credit_note) && ! customer_has_projects($credit_note->clientid))) {
                                    echo ' hide';
                                } ?>">
                                    <label
                                        for="project_id"><?= _l('project'); ?></label>
                                    <div id="project_ajax_search_wrapper">
                                        <select name="project_id" id="project_id" class="projects ajax-search"
                                            data-live-search="true" data-width="100%"
                                            data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                                            <?php
        if (isset($credit_note) && $credit_note->project_id) {
            echo '<option value="' . $credit_note->project_id . '" selected>' . e(get_project_name_by_id($credit_note->project_id)) . '</option>';
        }
?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <hr class="hr-10" />
                                        <a href="#" class="edit_shipping_billing_info" data-toggle="modal"
                                            data-target="#billing_and_shipping_details"><i
                                                class="fa-regular fa-pen-to-square"></i></a>
                                        <?php include_once APPPATH . 'views/admin/credit_notes/billing_and_shipping_template.php'; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="bold">
                                            <?= _l('credit_note_bill_to'); ?>
                                        </p>
                                        <address>
                                            <span class="billing_street">
                                                <?php $billing_street = (isset($credit_note) ? $credit_note->billing_street : '--'); ?>
                                                <?php $billing_street = ($billing_street == '' ? '--' : $billing_street); ?>
                                                <?= process_text_content_for_display($billing_street); ?></span><br>
                                            <span class="billing_city">
                                                <?php $billing_city = (isset($credit_note) ? $credit_note->billing_city : '--'); ?>
                                                <?php $billing_city = ($billing_city == '' ? '--' : $billing_city); ?>
                                                <?= e($billing_city); ?></span>,
                                            <span class="billing_state">
                                                <?php $billing_state = (isset($credit_note) ? $credit_note->billing_state : '--'); ?>
                                                <?php $billing_state = ($billing_state == '' ? '--' : $billing_state); ?>
                                                <?= e($billing_state); ?></span>
                                            <br />
                                            <span class="billing_country">
                                                <?php $billing_country = (isset($credit_note) ? get_country_short_name($credit_note->billing_country) : '--'); ?>
                                                <?php $billing_country = ($billing_country == '' ? '--' : $billing_country); ?>
                                                <?= e($billing_country); ?></span>,
                                            <span class="billing_zip">
                                                <?php $billing_zip = (isset($credit_note) ? $credit_note->billing_zip : '--'); ?>
                                                <?php $billing_zip = ($billing_zip == '' ? '--' : $billing_zip); ?>
                                                <?= e($billing_zip); ?></span>
                                        </address>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="bold">
                                            <?= _l('ship_to'); ?>
                                        </p>
                                        <address>
                                            <span class="shipping_street">
                                                <?php $shipping_street = (isset($credit_note) ? $credit_note->shipping_street : '--'); ?>
                                                <?php $shipping_street = ($shipping_street == '' ? '--' : $shipping_street); ?>
                                                <?= process_text_content_for_display($shipping_street); ?></span><br>
                                            <span class="shipping_city">
                                                <?php $shipping_city = (isset($credit_note) ? $credit_note->shipping_city : '--'); ?>
                                                <?php $shipping_city = ($shipping_city == '' ? '--' : $shipping_city); ?>
                                                <?= e($shipping_city); ?></span>,
                                            <span class="shipping_state">
                                                <?php $shipping_state = (isset($credit_note) ? $credit_note->shipping_state : '--'); ?>
                                                <?php $shipping_state = ($shipping_state == '' ? '--' : $shipping_state); ?>
                                                <?= e($shipping_state); ?></span>
                                            <br />
                                            <span class="shipping_country">
                                                <?php $shipping_country = (isset($credit_note) ? get_country_short_name($credit_note->shipping_country) : '--'); ?>
                                                <?php $shipping_country = ($shipping_country == '' ? '--' : $shipping_country); ?>
                                                <?= e($shipping_country); ?></span>,
                                            <span class="shipping_zip">
                                                <?php $shipping_zip = (isset($credit_note) ? $credit_note->shipping_zip : '--'); ?>
                                                <?php $shipping_zip = ($shipping_zip == '' ? '--' : $shipping_zip); ?>
                                                <?= e($shipping_zip); ?></span>
                                        </address>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php $value = (isset($credit_note) ? _d($credit_note->date) : _d(date('Y-m-d'))); ?>
                                        <?= render_date_input('date', 'credit_note_date', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php
             $next_credit_note_number = get_option('next_credit_note_number');
$format                               = get_option('credit_note_number_format');
$prefix                               = get_option('credit_note_prefix');

if (isset($credit_note)) {
    $format = $credit_note->number_format;
}

if ($format == 1) {
    $__number = $next_credit_note_number;
    if (isset($credit_note)) {
        $__number = e($credit_note->number);
        $prefix   = '<span id="prefix">' . e($credit_note->prefix) . '</span>';
    }
} elseif ($format == 2) {
    if (isset($credit_note)) {
        $__number = e($credit_note->number);
        $prefix   = $credit_note->prefix;
        $prefix   = '<span id="prefix">' . e($prefix) . '</span><span id="prefix_year">' . date('Y', strtotime(e($credit_note->date))) . '</span>/';
    } else {
        $__number = $next_credit_note_number;
        $prefix   = $prefix . '<span id="prefix_year">' . date('Y') . '</span>/';
    }
} elseif ($format == 3) {
    if (isset($credit_note)) {
        $yy       = date('y', strtotime($credit_note->date));
        $__number = e($credit_note->number);
        $prefix   = '<span id="prefix">' . e($credit_note->prefix) . '</span>';
    } else {
        $yy       = date('y');
        $__number = $next_credit_note_number;
    }
} elseif ($format == 4) {
    if (isset($credit_note)) {
        $yyyy     = date('Y', strtotime($credit_note->date));
        $mm       = date('m', strtotime($credit_note->date));
        $__number = e($credit_note->number);
        $prefix   = '<span id="prefix">' . e($credit_note->prefix) . '</span>';
    } else {
        $yyyy     = date('Y');
        $mm       = date('m');
        $__number = $next_credit_note_number;
    }
}
$_credit_note_number  = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
$isedit               = isset($credit_note) ? 'true' : 'false';
$data_original_number = isset($credit_note) ? $credit_note->number : 'false';
?>
                                        <div class="form-group">
                                            <label
                                                for="number"><?= _l('credit_note_number'); ?></label>
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    <?php if (isset($credit_note)) { ?>
                                                    <a href="#" onclick="return false;" data-toggle="popover"
                                                        data-container='._transaction_form' data-html="true"
                                                        data-content="<label class='control-label'><?= _l('credit_note_prefix'); ?></label><div class='input-group'><input name='s_prefix' type='text' class='form-control' value='<?= e($credit_note->prefix); ?>'></div><button type='button' onclick='save_sales_number_settings(this); return false;' data-url='<?= admin_url('credit_notes/update_number_settings/' . $credit_note->id); ?>' class='btn btn-primary btn-block mtop15'><?= _l('submit'); ?></button>"><i
                                                            class="fa fa-cog"></i></a>
                                                    <?php } ?>
                                                    <?= $prefix; ?></span>
                                                <input type="text" name="number" class="form-control"
                                                    value="<?= e($_credit_note_number); ?>"
                                                    data-isedit="<?= e($isedit); ?>"
                                                    data-original-number="<?= e($data_original_number); ?>">
                                                <?php if ($format == 3) { ?>
                                                <span class="input-group-addon">
                                                    <span id="prefix_year"
                                                        class="format-n-yy"><?= e($yy); ?></span>
                                                </span>
                                                <?php } elseif ($format == 4) { ?>
                                                <span class="input-group-addon">
                                                    <span id="prefix_month"
                                                        class="format-mm-yyyy"><?= e($mm); ?></span>
                                                    /
                                                    <span id="prefix_year"
                                                        class="format-mm-yyyy"><?= e($yyyy); ?></span>
                                                </span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="tw-ml-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php

         $credit_note_currency_attr = ['disabled' => true, 'data-show-subtext' => true];
$credit_note_currency_attr          = apply_filters_deprecated('credit_note_currency_disabled', [$credit_note_currency_attr], '2.3.0', 'credit_note_currency_attributes');

foreach ($currencies as $currency) {
    if ($currency['isdefault'] == 1) {
        $credit_note_currency_attr['data-base'] = $currency['id'];
    }
    if (isset($credit_note)) {
        if ($currency['id'] == $credit_note->currency) {
            $selected = $currency['id'];
        }
    } else {
        if ($currency['isdefault'] == 1) {
            $selected = $currency['id'];
        }
    }
}
$credit_note_currency_attr = hooks()->apply_filters('credit_note_currency_attributes', $credit_note_currency_attr);
?>
                                            <?= render_select('currency', $currencies, ['id', 'name', 'symbol'], 'currency', $selected, $credit_note_currency_attr); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group select-placeholder">
                                                <label for="discount_type"
                                                    class="control-label"><?= _l('discount_type'); ?></label>
                                                <select name="discount_type" class="selectpicker" data-width="100%"
                                                    data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                                                    <option value="" selected>
                                                        <?= _l('no_discount'); ?>
                                                    </option>
                                                    <option value="before_tax" <?php
 if (isset($credit_note)) {
     if ($credit_note->discount_type == 'before_tax') {
         echo 'selected';
     }
 } ?>><?= _l('discount_type_before_tax'); ?>
                                                    </option>
                                                    <option value="after_tax" <?php if (isset($credit_note)) {
                                                        if ($credit_note->discount_type == 'after_tax') {
                                                            echo 'selected';
                                                        }
                                                    } ?>><?= _l('discount_type_after_tax'); ?>
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <?php $value = (isset($credit_note) ? $credit_note->reference_no : ''); ?>
                                    <?= render_input('reference_no', 'reference_no', $value); ?>
                                    <?php $value = (isset($credit_note) ? $credit_note->adminnote : ''); ?>
                                    <?= render_textarea('adminnote', 'credit_note_admin_note', $value); ?>
                                    <?php $rel_id = (isset($credit_note) ? $credit_note->id : false); ?>
                                    <?= render_custom_fields('credit_note', $rel_id); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="hr-panel-separator" />
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-4">
                                <?php $this->load->view('admin/invoice_items/item_select'); ?>
                            </div>
                            <div class="col-md-8 text-right show_quantity_as_wrapper">
                                <div class="mtop10">
                                    <span><?= _l('show_quantity_as'); ?>
                                    </span>
                                    <div class="radio radio-primary radio-inline">
                                        <input type="radio" value="1" id="sq_1" name="show_quantity_as"
                                            data-text="<?= _l('credit_note_table_quantity_heading'); ?>"
                                            <?php if (isset($credit_note) && $credit_note->show_quantity_as == 1) {
                                                echo 'checked';
                                            } elseif (! isset($hours_quantity) && ! isset($qty_hrs_quantity)) {
                                                echo 'checked';
                                            } ?>>
                                        <label
                                            for="sq_1"><?= _l('quantity_as_qty'); ?></label>
                                    </div>
                                    <div class="radio radio-primary radio-inline">
                                        <input type="radio" value="2" id="sq_2" name="show_quantity_as"
                                            data-text="<?= _l('credit_note_table_hours_heading'); ?>"
                                            <?php if (isset($credit_note) && $credit_note->show_quantity_as == 2 || isset($hours_quantity)) {
                                                echo 'checked';
                                            } ?>>
                                        <label
                                            for="sq_2"><?= _l('quantity_as_hours'); ?></label>
                                    </div>
                                    <div class="radio radio-primary radio-inline">
                                        <input type="radio" value="3" id="sq_3" name="show_quantity_as"
                                            data-text="<?= _l('credit_note_table_quantity_heading'); ?>/<?= _l('credit_note_table_hours_heading'); ?>"
                                            <?php if (isset($credit_note) && $credit_note->show_quantity_as == 3 || isset($qty_hrs_quantity)) {
                                                echo 'checked';
                                            } ?>>
                                        <label
                                            for="sq_3"><?= _l('credit_note_table_quantity_heading'); ?>/<?= _l('credit_note_table_hours_heading'); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive s_table">
                            <table
                                class="table credite-note-items-table items table-main-credit-note-edit has-calculations no-mtop">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th width="20%" align="left"><i class="fa-solid fa-circle-exclamation tw-mr-1"
                                                aria-hidden="true" data-toggle="tooltip"
                                                data-title="<?= _l('item_description_new_lines_notice'); ?>"></i>
                                            <?= _l('credit_note_table_item_heading'); ?>
                                        </th>
                                        <th width="25%" align="left">
                                            <?= _l('credit_note_table_item_description'); ?>
                                        </th>
                                        <?php
    $custom_fields = get_custom_fields('items');

foreach ($custom_fields as $cf) {
    echo '<th width="15%" align="left" class="custom_field">' . e($cf['name']) . '</th>';
}
$qty_heading = _l('credit_note_table_quantity_heading');
if (isset($credit_note) && $credit_note->show_quantity_as == 2 || isset($hours_quantity)) {
    $qty_heading = _l('credit_note_table_hours_heading');
} elseif (isset($credit_note) && $credit_note->show_quantity_as == 3) {
    $qty_heading = _l('credit_note_table_quantity_heading') . '/' . _l('credit_note_table_hours_heading');
}
?>
                                        <th width="10%" class="qty" align="right">
                                            <?= e($qty_heading); ?>
                                        </th>
                                        <th width="15%" align="right">
                                            <?= _l('credit_note_table_rate_heading'); ?>
                                        </th>
                                        <th width="20%" align="right">
                                            <?= _l('credit_note_table_tax_heading'); ?>
                                        </th>
                                        <th width="10%" align="right">
                                            <?= _l('credit_note_table_amount_heading'); ?>
                                        </th>
                                        <th align="center"><i class="fa fa-cog"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="main">
                                        <td></td>
                                        <td>
                                            <textarea name="description" class="form-control" rows="4"
                                                placeholder="<?= _l('item_description_placeholder'); ?>"></textarea>
                                        </td>
                                        <td>
                                            <textarea name="long_description" rows="4" class="form-control"
                                                placeholder="<?= _l('item_long_description_placeholder'); ?>"></textarea>
                                        </td>
                                        <?= render_custom_fields_items_table_add_edit_preview(); ?>
                                        <td>
                                            <input type="number" name="quantity" min="0" value="1" class="form-control"
                                                placeholder="<?= _l('item_quantity_placeholder'); ?>">
                                            <input type="text"
                                                placeholder="<?= _l('unit'); ?>"
                                                data-toggle="tooltip" 612 data-title="e.q kg, lots, packs" name="unit"
                                                class="form-control input-transparent text-right">
                                        </td>
                                        <td>
                                            <input type="number" name="rate" class="form-control"
                                                placeholder="<?= _l('item_rate_placeholder'); ?>">
                                        </td>
                                        <td>
                                            <?php
 $default_tax = unserialize(get_option('default_tax'));
$select       = '<select class="selectpicker display-block tax main-tax" data-width="100%" name="taxname" multiple data-none-selected-text="' . _l('no_tax') . '">';

foreach ($taxes as $tax) {
    $selected = '';
    if (is_array($default_tax)) {
        if (in_array($tax['name'] . '|' . $tax['taxrate'], $default_tax)) {
            $selected = ' selected ';
        }
    }
    $select .= '<option value="' . $tax['name'] . '|' . $tax['taxrate'] . '"' . $selected . 'data-taxrate="' . $tax['taxrate'] . '" data-taxname="' . $tax['name'] . '" data-subtext="' . $tax['name'] . '">' . $tax['taxrate'] . '%</option>';
}
$select .= '</select>';
echo $select;
?>
                                        </td>
                                        <td></td>
                                        <td>
                                            <?php
$new_item = 'undefined';
if (isset($credit_note)) {
    $new_item = true;
}
?>
                                            <button type="button"
                                                onclick="add_item_to_table('undefined','undefined',<?= e($new_item); ?>); return false;"
                                                class="btn btn-primary pull-right "><i class="fa fa-check"></i></button>
                                        </td>
                                    </tr>
                                    <?php if (isset($credit_note) || isset($add_items)) {
                                        $i               = 1;
                                        $items_indicator = 'newitems';
                                        if (isset($credit_note)) {
                                            $add_items       = $credit_note->items;
                                            $items_indicator = 'items';
                                        }

                                        foreach ($add_items as $item) {
                                            $manual    = false;
                                            $table_row = '<tr class="sortable item">';
                                            $table_row .= '<td class="dragger">';
                                            if (! is_numeric($item['qty'])) {
                                                $item['qty'] = 1;
                                            }
                                            $credit_note_item_taxes = get_credit_note_item_taxes($item['id']);
                                            // passed like string
                                            if ($item['id'] == 0) {
                                                $credit_note_item_taxes = $item['taxname'];
                                                $manual                 = true;
                                            }
                                            $table_row .= form_hidden('' . $items_indicator . '[' . $i . '][itemid]', $item['id']);
                                            $amount = $item['rate'] * $item['qty'];
                                            $amount = app_format_number($amount);
                                            // order input
                                            $table_row .= '<input type="hidden" class="order" name="' . $items_indicator . '[' . $i . '][order]">';
                                            $table_row .= '</td>';
                                            $table_row .= '<td class="bold description"><textarea name="' . $items_indicator . '[' . $i . '][description]" class="form-control" rows="5">' . clear_textarea_breaks($item['description']) . '</textarea></td>';
                                            $table_row .= '<td><textarea name="' . $items_indicator . '[' . $i . '][long_description]" class="form-control" rows="5">' . clear_textarea_breaks($item['long_description']) . '</textarea></td>';
                                            $table_row .= render_custom_fields_items_table_in($item, $items_indicator . '[' . $i . ']');
                                            $table_row .= '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" data-quantity name="' . $items_indicator . '[' . $i . '][qty]" value="' . $item['qty'] . '" class="form-control">';
                                            $unit_placeholder = '';
                                            if (! $item['unit']) {
                                                $unit_placeholder = _l('unit');
                                                $item['unit']     = '';
                                            }
                                            $table_row .= '<input type="text" placeholder="' . $unit_placeholder . '" name="' . $items_indicator . '[' . $i . '][unit]" class="form-control input-transparent text-right" value="' . $item['unit'] . '">';
                                            $table_row .= '</td>';
                                            $table_row .= '<td class="rate"><input type="number" data-toggle="tooltip" title="' . _l('numbers_not_formatted_while_editing') . '" onblur="calculate_total();" onchange="calculate_total();" name="' . $items_indicator . '[' . $i . '][rate]" value="' . $item['rate'] . '" class="form-control"></td>';
                                            $table_row .= '<td class="taxrate">' . $this->misc_model->get_taxes_dropdown_template('' . $items_indicator . '[' . $i . '][taxname][]', $credit_note_item_taxes, 'credit_note', $item['id'], true, $manual) . '</td>';
                                            $table_row .= '<td class="amount" align="right">' . $amount . '</td>';
                                            $table_row .= '<td><a href="#" class="btn btn-danger pull-left !tw-px-3" onclick="delete_item(this,' . $item['id'] . '); return false;"><i class="fa fa-times"></i></a></td>';
                                            $table_row .= '</tr>';
                                            echo $table_row;
                                            $i++;
                                        }
                                    }
?>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-8 col-md-offset-4">
                            <table class="table text-right">
                                <tbody>
                                    <tr id="subtotal">
                                        <td><span
                                                class="bold tw-text-neutral-700"><?= _l('credit_note_subtotal'); ?>
                                                :</span>
                                        </td>
                                        <td class="subtotal">
                                        </td>
                                    </tr>
                                    <tr id="discount_area">
                                        <td>
                                            <div class="row">
                                                <div class="col-md-7">
                                                    <span
                                                        class="bold tw-text-neutral-700"><?= _l('credit_note_discount'); ?></span>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="input-group" id="discount-total">

                                                        <input type="number"
                                                            value="<?= isset($credit_note) ? $credit_note->discount_percent : 0; ?>"
                                                            class="form-control pull-left input-discount-percent<?php if (isset($credit_note) && ! is_sale_discount($credit_note, 'percent') && is_sale_discount_applied($credit_note)) {
                                                                echo ' hide';
                                                            } ?>" min="0" max="100" name="discount_percent">

                                                        <input type="number" data-toggle="tooltip"
                                                            data-title="<?= _l('numbers_not_formatted_while_editing'); ?>"
                                                            value="<?= isset($credit_note) ? $credit_note->discount_total : 0; ?>"
                                                            class="form-control pull-left input-discount-fixed<?php if (! isset($credit_note) || (isset($credit_note) && ! is_sale_discount($credit_note, 'fixed'))) {
                                                                echo ' hide';
                                                            } ?>" min="0" name="discount_total">

                                                        <div class="input-group-addon">
                                                            <div class="dropdown">
                                                                <a class="dropdown-toggle" href="#"
                                                                    id="dropdown_menu_tax_total_type"
                                                                    data-toggle="dropdown" aria-haspopup="true"
                                                                    aria-expanded="true">
                                                                    <span class="discount-total-type-selected">
                                                                        <?php if (! isset($credit_note) || isset($credit_note) && (is_sale_discount($credit_note, 'percent') || ! is_sale_discount_applied($credit_note))) {
                                                                            echo '%';
                                                                        } else {
                                                                            echo _l('discount_fixed_amount');
                                                                        }
?>
                                                                    </span>
                                                                    <span class="caret"></span>
                                                                </a>
                                                                <ul class="dropdown-menu"
                                                                    id="discount-total-type-dropdown"
                                                                    aria-labelledby="dropdown_menu_tax_total_type">
                                                                    <li>
                                                                        <a href="#" class="discount-total-type discount-type-percent<?php if (! isset($credit_note) || (isset($credit_note) && is_sale_discount($credit_note, 'percent')) || (isset($credit_note) && ! is_sale_discount_applied($credit_note))) {
                                                                            echo ' selected';
                                                                        } ?>">%</a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="#" class="discount-total-type discount-type-fixed<?php if (isset($credit_note) && is_sale_discount($credit_note, 'fixed')) {
                                                                            echo ' selected';
                                                                        } ?>">
                                                                            <?= _l('discount_fixed_amount'); ?>
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="discount-total"></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-7">
                                                    <span
                                                        class="bold tw-text-neutral-700"><?= _l('credit_note_adjustment'); ?></span>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="number" data-toggle="tooltip"
                                                        data-title="<?= _l('numbers_not_formatted_while_editing'); ?>"
                                                        value="<?php if (isset($credit_note)) {
                                                            echo $credit_note->adjustment;
                                                        } else {
                                                            echo 0;
                                                        } ?>" class="form-control pull-left" name="adjustment">
                                                </div>
                                            </div>
                                        </td>
                                        <td class="adjustment"></td>
                                    </tr>
                                    <tr>
                                        <td><span
                                                class="bold tw-text-neutral-700"><?= _l('credit_note_total'); ?>
                                                :</span>
                                        </td>
                                        <td class="total">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div id="removed-items"></div>
                    </div>
                    <hr class="hr-panel-separator" />
                    <div class="panel-body">
                        <?php $value = (isset($credit_note) ? $credit_note->clientnote : get_option('predefined_clientnote_credit_note')); ?>
                        <?= render_textarea('clientnote', 'credit_note_add_edit_client_note', $value); ?>
                        <?php $value = (isset($credit_note) ? $credit_note->terms : get_option('predefined_terms_credit_note')); ?>
                        <?= render_textarea('terms', 'terms_and_conditions', $value, [], [], 'mtop15'); ?>
                    </div>
                </div>

                <div class="btn-bottom-pusher"></div>
                <div class="btn-bottom-toolbar btn-t2oolbar-container-out text-right">
                    <button type="button"
                        class="btn-tr btn btn-default mleft10 credit-note-form-submit save-and-send transaction-submit">
                        <?= _l('save_and_send'); ?>
                    </button>
                    <button class="btn-tr btn btn-primary mleft5 text-right credit-note-form-submit transaction-submit">
                        <?= _l('submit'); ?>
                    </button>
                </div>
            </div>
                        <?= form_close(); ?>
            <?php $this->load->view('admin/invoice_items/item'); ?>
        </div> <!-- /xb-inv-main -->
    
    <!-- Right sidebar -->
    <div class="xb-inv-sidebar">

      <!-- Void Rate hero -->
      <div class="xb-decline-card">
        <div>
          <div class="xb-decline-label">Void Rate</div>
          <div class="xb-decline-val"><?php echo $xb_stat_overdue_rate; ?>%</div>
          <div class="xb-decline-trend">Overall void percentage</div>
        </div>
        <div class="xb-decline-icon"><i class="fa fa-exclamation-circle" style="color:#f97316;"></i></div>
      </div>

      <!-- Conversion metrics -->
      <div class="xb-sw">
        <div class="xb-sw-head">
          Credit Note Velocity
          <span style="font-size:10px;font-weight:500;color:#9ca3af;">All Time</span>
        </div>
        <div class="xb-sw-body">

          <div class="xb-metric-row">
            <div class="xb-metric-top">
              <span class="xb-metric-label">Created</span>
            </div>
            <div class="xb-metric-track"><div class="xb-metric-fill green" style="width:<?php echo $xb_stat_draft_to_sent; ?>%"></div></div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <span class="xb-metric-sub">Percent Open/Closed</span>
              <span style="font-size:12px;font-weight:700;color:#111827;"><?php echo $xb_stat_draft_to_sent; ?>%</span>
            </div>
          </div>

          <div class="xb-metric-row" style="margin-bottom:0">
            <div class="xb-metric-top">
              <span class="xb-metric-label">Closed</span>
            </div>
            <div class="xb-metric-track"><div class="xb-metric-fill blue" style="width:<?php echo $xb_stat_sent_to_paid; ?>%"></div></div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <span class="xb-metric-sub">Percent Closed</span>
              <span style="font-size:12px;font-weight:700;color:#111827;"><?php echo $xb_stat_sent_to_paid; ?>%</span>
            </div>
          </div>

        </div>
      </div>

      <!-- Recent Refunds -->
      <div class="xb-sw">
        <div class="xb-sw-head">
          Recent Refunds
          <span style="font-size:10px;font-weight:500;color:#9ca3af;">Latest</span>
        </div>
        <div class="xb-sw-body" style="padding-top:4px;padding-bottom:4px;">

          <?php if(empty($xb_recent_payments)): ?>
            <div style="padding:20px;text-align:center;font-size:12px;color:#9ca3af;">No recent refunds recorded.</div>
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
              <div class="xb-recent-item">
                <div class="xb-recent-left">
                  <div class="xb-avatar <?php echo $cc; ?>"><?php echo $initials; ?></div>
                  <div>
                    <div class="xb-recent-name"><?php echo htmlspecialchars($name); ?></div>
                    <div class="xb-recent-meta"><?php echo _dt($rp['date']); ?></div>
                  </div>
                </div>
                <div class="tw-flex tw-items-center tw-gap-3">
                    <span class="xb-recent-amt" style="color:#ef4444">-<?php echo app_format_money($rp['amount'], $base_currency->name); ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

        </div>
      </div>

      <!-- Invoice Setup -->
      <div class="xb-sw" style="background:#f9fafb;">
        <div class="xb-sw-head">Credit Note Setup</div>
        <div class="xb-sw-body">
          <p style="font-size:12px;color:#6b7280;margin:0 0 12px;">Manage your billing terms and templates in one place.</p>
          <button class="xb-setup-cta" style="width:100%; border:1px solid #d1d5db; background:#fff; padding:8px; border-radius:6px; font-weight:500;" onclick="window.location='<?php echo admin_url('invoices/settings'); ?>'">
            <i class="fa fa-cogs"></i> Configuration
          </button>
        </div>
      </div>
    </div> <!-- /xb-inv-sidebar -->
  </div> <!-- /xb-inv-body -->
</div> <!-- /xb-inv-page -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        try {
            xbInitCustomerPicker();
        } catch(e) {
            console.error(e);
        }
        
        validate_credit_note_form();
        // Init accountacy currency symbol
        init_currency();
        init_ajax_project_search_by_customer_id();
        // Maybe items ajax search
        init_ajax_search('items', '#item_select.ajax-search', undefined, admin_url + 'items/search');
    });
</script><style>
/* â”€â”€ Xetuu Books Customer Picker â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.xb-cp-wrap { position: relative; }
.xb-cp-trigger {
    display: flex; align-items: center; justify-content: space-between;
    padding: 6px 12px; border: 1px solid #ccd0d8; border-radius: 4px;
    background: #fff; cursor: pointer; min-height: 34px;
    transition: border-color .15s, box-shadow .15s;
}
.xb-cp-trigger:hover, .xb-cp-wrap.open .xb-cp-trigger {
    border-color: #1a6b3a; box-shadow: 0 0 0 3px rgba(26,107,58,.1);
}
.xb-cp-display { flex: 1; font-size: 14px; color: #333; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.xb-cp-display.placeholder { color: #aaa; }
.xb-cp-icons { display: flex; align-items: center; gap: 4px; margin-left: 8px; flex-shrink: 0; }
.xb-cp-clear-btn {
    display: none; width: 18px; height: 18px; border-radius: 50%; background: #d1d5db;
    color: #6b7280; font-size: 11px; align-items: center; justify-content: center;
    line-height: 1; cursor: pointer; border: none; padding: 0; transition: background .15s;
}
.xb-cp-clear-btn:hover { background: #ef4444; color: #fff; }
.xb-cp-wrap.has-val .xb-cp-clear-btn { display: flex; }
.xb-cp-caret { color: #9ca3af; font-size: 10px; transition: transform .2s; }
.xb-cp-wrap.open .xb-cp-caret { transform: rotate(180deg); }

.xb-cp-drop {
    display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0;
    background: #fff; border: 1px solid #d1d5db; border-radius: 8px;
    box-shadow: 0 12px 28px rgba(0,0,0,.13); z-index: 9999; overflow: hidden;
}
.xb-cp-wrap.open .xb-cp-drop { display: block; }

.xb-cp-search-row {
    padding: 8px 8px 6px; border-bottom: 1px solid #f3f4f6; background: #fafafa;
}
.xb-cp-search-row input {
    width: 100%; border: 1px solid #e5e7eb; border-radius: 5px;
    padding: 6px 10px 6px 32px; font-size: 13px; outline: none; background: #fff;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='%23aaa'%3E%3Cpath d='M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: 10px center;
}
.xb-cp-search-row input:focus { border-color: #1a6b3a; }

.xb-cp-list { max-height: 210px; overflow-y: auto; }
.xb-cp-item {
    display: flex; align-items: center; padding: 8px 14px;
    font-size: 13px; color: #374151; cursor: pointer;
    transition: background .1s; gap: 6px;
}
.xb-cp-item:hover { background: #f0fdf4; }
.xb-cp-item.xb-cp-sel { background: #dcfce7; font-weight: 600; color: #166534; }
.xb-cp-item-email { font-size: 11px; color: #9ca3af; margin-left: auto; }
.xb-cp-item.xb-cp-sel .xb-cp-item-email { color: #6ee7b7; }
.xb-cp-check { color: #16a34a; font-size: 11px; margin-left: auto; }
.xb-cp-empty { padding: 14px; text-align: center; font-size: 13px; color: #aaa; display: none; }
.xb-cp-footer { border-top: 1px solid #f0f0f0; }
.xb-cp-create-btn {
    display: flex; align-items: center; gap: 8px; width: 100%; border: none; background: none;
    padding: 9px 14px; font-size: 13px; font-weight: 600; color: #16a34a; cursor: pointer;
    transition: background .15s;
}
.xb-cp-create-btn:hover { background: #f0fdf4; }
.xb-cp-create-btn .xb-spin {
    display: none; width: 14px; height: 14px; border: 2px solid #a7f3d0;
    border-top-color: #16a34a; border-radius: 50%;
    animation: xbspin .6s linear infinite;
}
.xb-cp-create-btn.xb-creating .xb-spin { display: inline-block; }
.xb-cp-create-btn.xb-creating .xb-plus-icon { display: none; }
@keyframes xbspin { to { transform: rotate(360deg); } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Replace the ajax-search customer select with our custom picker first!
    // This prevents any errors in native Perfex JS from halting execution.
    try {
        xbInitCustomerPicker();
    } catch(e) {
        console.error(e);
    }

    // Init form validation and all other Perfex invoice JS
    try { validate_invoice_form(); } catch(e) {}
    try { init_currency(); } catch(e) {}
    try { init_ajax_project_search_by_customer_id(); } catch(e) {}
    try { init_ajax_search('items', '#item_select.ajax-search', undefined, admin_url + 'items/search'); } catch(e) {}
});

function xbInitCustomerPicker() {
    var $orig     = $('#clientid');
    var allList   = [];         // full customer list loaded once
    var selId     = $orig.val() || '';
    var selName   = selId ? ($orig.find('option:selected').text() || '') : '';

    // â”€â”€ 1. Kill bootstrap-select / ajax-bootstrap-select â”€â”€
    $orig.removeClass('ajax-search'); // PREVENT main.js from finding it!
    try { $orig.selectpicker('destroy'); } catch(e) {}
    $orig.closest('.form-group').find('.bootstrap-select').remove();
    $orig.hide();           // keep hidden for form submission

    // â”€â”€ 2. Inject picker HTML â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    var html =
        '<div class="xb-cp-wrap' + (selId ? ' has-val' : '') + '" id="xb-cp-wrap">' +
            '<div class="xb-cp-trigger" id="xb-cp-trigger">' +
                '<span class="xb-cp-display' + (selId ? '' : ' placeholder') + '" id="xb-cp-disp">' +
                    (selId ? _esc(selName) : 'Select and begin typing') +
                '</span>' +
                '<span class="xb-cp-icons">' +
                    '<button type="button" class="xb-cp-clear-btn" id="xb-cp-clear" title="Clear">&times;</button>' +
                    '<span class="xb-cp-caret">&#9660;</span>' +
                '</span>' +
            '</div>' +
            '<div class="xb-cp-drop" id="xb-cp-drop">' +
                '<div class="xb-cp-search-row">' +
                    '<input type="text" id="xb-cp-q" placeholder="Type to search customers..." autocomplete="off">' +
                '</div>' +
                '<div class="xb-cp-list" id="xb-cp-list">' +
                    '<div class="xb-cp-item" style="color:#bbb;pointer-events:none">' +
                        '<i class="fa fa-spinner fa-spin"></i>&nbsp;Loading&hellip;' +
                    '</div>' +
                '</div>' +
                '<div class="xb-cp-empty" id="xb-cp-empty">No customers match</div>' +
                '<div class="xb-cp-footer" id="xb-cp-footer" style="display:none">' +
                    '<button type="button" class="xb-cp-create-btn" id="xb-cp-create">' +
                        '<span class="xb-spin"></span>' +
                        '<i class="fa fa-plus-circle xb-plus-icon"></i>' +
                        '<span>Create &ldquo;<span id="xb-cp-cname"></span>&rdquo;</span>' +
                    '</button>' +
                '</div>' +
            '</div>' +
        '</div>';

    $orig.closest('.form-group').prepend(html);

    // â”€â”€ 3. Load all customers on init â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    $.ajax({
        url: admin_url + 'xetuu_books/ajax/get_customers',
        type: 'GET',
        success: function(res) {
            var data;
            try {
                if (typeof res === 'string') {
                    var jsonStart = res.indexOf('[');
                    if(jsonStart > -1) {
                        data = JSON.parse(res.substring(jsonStart));
                    } else {
                        data = JSON.parse(res);
                    }
                } else {
                    data = res;
                }
            } catch(e) {
                console.error("JSON Parse Error:", e);
                $('#xb-cp-list').html('<div class="xb-cp-item" style="color:#ef4444">Failed to load (Parse Error)</div>');
                return;
            }
            allList = data || [];
            _render(allList, '');
        },
        error: function() {
            $('#xb-cp-list').html('<div class="xb-cp-item" style="color:#ef4444">Failed to load</div>');
        }
    });

    // â”€â”€ 4. Helper functions â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function _esc(s) { return $('<div>').text(String(s || '')).html(); }

    function _render(items, q) {
        var $list = $('#xb-cp-list').empty();
        $('#xb-cp-empty').hide();
        $('#xb-cp-footer').hide();

        if (!items.length) {
            $('#xb-cp-empty').show();
            if (q.trim()) { _showCreate(q); }
            return;
        }

        $.each(items, function(_, c) {
            var isSel = (String(c.id) === String(selId));
            var $item = $(
                '<div class="xb-cp-item' + (isSel ? ' xb-cp-sel' : '') + '" data-id="' + _esc(c.id) + '" data-name="' + _esc(c.name) + '">' +
                    '<span>' + _esc(c.name) + '</span>' +
                    (c.email ? '<span class="xb-cp-item-email">' + _esc(c.email) + '</span>' : '') +
                    (isSel ? '<i class="fa fa-check xb-cp-check"></i>' : '') +
                '</div>'
            );
            $list.append($item);
        });

        // Show "+ Create" if typed text doesn't exactly match any name
        if (q.trim()) {
            var exact = items.some(function(c) {
                return c.name.toLowerCase() === q.toLowerCase();
            });
            if (!exact) { _showCreate(q); }
        }
    }

    function _showCreate(q) {
        $('#xb-cp-cname').text(q.trim());
        $('#xb-cp-footer').show();
    }

    function _filter(q) {
        if (!q) { _render(allList, ''); return; }
        var ql = q.toLowerCase();
        var hits = allList.filter(function(c) {
            return c.name.toLowerCase().indexOf(ql) > -1 ||
                   (c.email && c.email.toLowerCase().indexOf(ql) > -1);
        });
        _render(hits, q);
    }

    function _pick(id, name) {
        selId   = id;
        selName = name;

        // Update hidden select (main.js reads this for billing info AJAX)
        $orig.find('option[value!=""]').remove();
        $orig.append('<option value="' + _esc(id) + '" selected>' + _esc(name) + '</option>');
        $orig.val(id);

        // Update display
        $('#xb-cp-disp').text(name).removeClass('placeholder');
        $('#xb-cp-wrap').addClass('has-val');

        _close();
        $orig.trigger('change');   // fires client_change_data AJAX in main.js
    }

    function _clear() {
        selId = selName = '';
        $orig.val('').find('option[value!=""]').remove();
        $('#xb-cp-disp').text('Select and begin typing').addClass('placeholder');
        $('#xb-cp-wrap').removeClass('has-val');
        _render(allList, '');
        $orig.trigger('change');
    }

    function _open() {
        $('#xb-cp-wrap').addClass('open');
        setTimeout(function() { $('#xb-cp-q').val('').focus(); _render(allList, ''); }, 10);
    }

    function _close() {
        $('#xb-cp-wrap').removeClass('open');
        $('#xb-cp-q').val('');
    }

    // â”€â”€ 5. Events â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // Toggle dropdown
    $('#xb-cp-trigger').on('click', function(e) {
        if ($(e.target).closest('#xb-cp-clear').length) return;
        $('#xb-cp-wrap').hasClass('open') ? _close() : _open();
    });

    // Clear
    $('#xb-cp-clear').on('click', function(e) { e.stopPropagation(); _clear(); });

    // Live search
    $('#xb-cp-q').on('input', function() { _filter($(this).val()); });
    $('#xb-cp-q').on('keydown', function(e) { if (e.key === 'Escape') _close(); });

    // Pick a customer
    $(document).on('click', '#xb-cp-list .xb-cp-item', function() {
        var id = $(this).data('id'), name = $(this).data('name');
        if (id) _pick(String(id), String(name));
    });

    // Create customer inline
    $('#xb-cp-create').on('click', function() {
        var name = $('#xb-cp-cname').text().trim();
        if (!name) return;
        var $btn = $(this).addClass('xb-creating').prop('disabled', true);

        $.ajax({
            url: admin_url + 'xetuu_books/ajax/quick_create_customer',
            type: 'POST',
            data: { company: name },
            success: function(res) {
                $btn.removeClass('xb-creating').prop('disabled', false);
                try {
                    if (typeof res === 'string') {
                        var jsonStart = res.indexOf('{');
                        if(jsonStart > -1) {
                            res = JSON.parse(res.substring(jsonStart));
                        } else {
                            res = JSON.parse(res);
                        }
                    }
                } catch(e) {
                    console.error("Parse error on quick create:", e);
                    alert('An error occurred. Check the console.');
                    return;
                }

                if (res.success) {
                    var newC = { id: String(res.id), name: res.name, email: '' };
                    allList.unshift(newC);
                    _pick(newC.id, newC.name);

                    // Brief green flash on the display
                    $('#xb-cp-disp').animate({ backgroundColor: '#dcfce7' }, 200, function() {
                        $(this).delay(700).animate({ backgroundColor: '#ffffff' }, 400);
                    });
                } else {
                    alert(res.error || 'Failed to create customer');
                }
            },
            error: function() {
                $btn.removeClass('xb-creating').prop('disabled', false);
                alert('Network error â€” please try again');
            }
        });
    });

    // Close on outside click
    $(document).on('click.xbcp', function(e) {
        if (!$(e.target).closest('#xb-cp-wrap').length) _close();
    });
}
</script>

<script>
    function xb_refund_credit_note(id) {
        if (typeof id == "undefined" || id === "") {
            return;
        }
        var url = admin_url + "credit_notes/refund/" + id;
        $.get(url, function(response) {
            var modalHtml = '<div class="modal fade" id="xb_refund_modal" tabindex="-1" role="dialog"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title">Record Refund</h4></div><div class="modal-body">' + response + '</div></div></div></div>';
            
            $('#xb_refund_modal').remove();
            $('body').append(modalHtml);
            
            $('#xb_refund_modal').modal('show');
            
            if (typeof appValidateForm !== 'undefined') {
                 appValidateForm($('#credit_note_refund_form'),{amount:'required',refunded_on:'required', payment_mode: 'required'});
            }
        });
    }
</script>

<?php if (isset($credit_note)): ?>
    <?php $this->load->view('admin/credit_notes/apply_credits_to_invoices'); ?>
<?php endif; ?>
