<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php $this->load->view('courier_goshipping/layout/_topnav', ['cgs_active' => 'shipments']); ?>
<?php $this->load->view('courier_goshipping/cargo_manifest/_style'); ?>
<?php
$base = admin_url('courier_goshipping/cargo_manifest');
$qs   = http_build_query(array_filter($f, function ($v) { return $v !== '' && $v !== 0 && $v !== 'all' && $v !== 'none'; }));
?>
<div class="cm">

    <!-- 1. Manifest number on top -->
    <div class="cm-hero">
        <div>
            <h1><i class="fa fa-file-text-o"></i> Cargo Manifest</h1>
            <p>Pick the filters, review the shipments, then generate the manifest.</p>
        </div>
        <div class="cm-number">
            <small>Manifest Number</small>
            <strong><?php echo htmlspecialchars($next_number); ?></strong>
            <span><i class="fa fa-magic"></i> Assigned automatically when you generate</span>
        </div>
    </div>

    <!-- Filters -->
    <div class="cm-card">
        <div class="cm-card__head">
            <h3><i class="fa fa-filter"></i> Filters</h3>
            <a class="cm-btn cm-btn--ghost" href="<?php echo $base . '/history'; ?>"><i class="fa fa-history"></i> Generated manifests</a>
        </div>
        <div class="cm-card__body">
            <form method="get" action="<?php echo $base; ?>" id="cm-filter-form">
                <div class="cm-filters">
                    <div class="cm-field"><label>From</label><input type="date" name="date_from" value="<?php echo htmlspecialchars($f['date_from']); ?>"></div>
                    <div class="cm-field"><label>To</label><input type="date" name="date_to" value="<?php echo htmlspecialchars($f['date_to']); ?>"></div>

                    <div class="cm-field">
                        <label>Mode of transport</label>
                        <select name="type">
                            <option value="">All modes</option>
                            <?php foreach ($type_labels as $k => $lbl): ?>
                                <option value="<?php echo $k; ?>" <?php echo $f['type'] === $k ? 'selected' : ''; ?>><?php echo htmlspecialchars($lbl); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($level === 'global'): ?>
                    <div class="cm-field">
                        <label>Country</label>
                        <select name="country_id" onchange="document.getElementById('cm-filter-form').submit()">
                            <option value="0">All countries</option>
                            <?php foreach ($countries as $c): ?>
                                <option value="<?php echo (int) $c->country_id; ?>" <?php echo (int) $f['country_id'] === (int) $c->country_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($c->short_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <?php if ($level !== 'own'): ?>
                    <div class="cm-field">
                        <label>User</label>
                        <select name="user">
                            <option value="all" <?php echo $f['user'] === 'all' ? 'selected' : ''; ?>>All users</option>
                            <option value="mine" <?php echo $f['user'] === 'mine' ? 'selected' : ''; ?>>Users I created</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?php echo (int) $u->staffid; ?>" <?php echo (string) $f['user'] === (string) $u->staffid ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(trim($u->firstname . ' ' . $u->lastname)) . ($u->active ? '' : ' (inactive)'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="cm-field">
                        <label>Agent</label>
                        <select name="agent">
                            <option value="none" <?php echo $f['agent'] === 'none' ? 'selected' : ''; ?>>No agent filter</option>
                            <option value="all" <?php echo $f['agent'] === 'all' ? 'selected' : ''; ?>>All agents</option>
                            <option value="mine" <?php echo $f['agent'] === 'mine' ? 'selected' : ''; ?>>Agents I created</option>
                            <?php foreach ($agents as $a): ?>
                                <option value="<?php echo (int) $a->id; ?>" <?php echo (string) $f['agent'] === (string) $a->id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(trim($a->firstname . ' ' . $a->lastname) . ($a->company_name ? ' — ' . $a->company_name : '')); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="cm-actions">
                        <button type="submit" class="cm-btn cm-btn--primary"><i class="fa fa-search"></i> Apply</button>
                        <a class="cm-btn cm-btn--ghost" href="<?php echo $base; ?>">Reset</a>
                    </div>
                </div>
            </form>

            <div class="cm-scope">
                <?php if ($level === 'global'): ?>
                    <i class="fa fa-globe"></i> Showing <b><?php echo htmlspecialchars($labels['country']); ?></b>
                <?php elseif ($is_country_admin): ?>
                    <i class="fa fa-flag"></i> You administer <b><?php echo htmlspecialchars($labels['country']); ?></b> &mdash; every manifest, staff member and agent shown here belongs to your country.
                <?php elseif ($level === 'own'): ?>
                    <i class="fa fa-user"></i> Showing only the shipments you booked.
                <?php else: ?>
                    <i class="fa fa-building"></i> Showing your branch(es).
                <?php endif; ?>
                &nbsp;·&nbsp; <?php echo htmlspecialchars($labels['type']); ?> · <?php echo htmlspecialchars($labels['user']); ?> · <?php echo htmlspecialchars($labels['agent']); ?>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="cm-card">
        <div class="cm-card__head">
            <h3><i class="fa fa-table"></i> Shipments in this manifest
                <span class="cm-chips">
                    <span><b><?php echo (int) $totals['count']; ?></b> shipments</span>
                    <span><b><?php echo number_format($totals['kg'], 2); ?></b> kg</span>
                    <span><b><?php echo number_format($totals['cbm'], 3); ?></b> CBM</span>
                    <?php foreach ($totals['amounts'] as $cur => $amt): ?><span><b><?php echo htmlspecialchars($cur); ?> <?php echo number_format($amt, 2); ?></b></span><?php endforeach; ?>
                </span>
            </h3>
            <?php if ($can_generate && !empty($rows)): ?>
                <?php echo form_open($base . '/generate', ['onsubmit' => "return confirm('Generate a manifest with these " . (int) $totals['count'] . " shipments?');"]); ?>
                <?php foreach (['date_from', 'date_to', 'type', 'user', 'agent', 'country_id'] as $k): ?>
                    <input type="hidden" name="<?php echo $k; ?>" value="<?php echo htmlspecialchars((string) $f[$k]); ?>">
                <?php endforeach; ?>
                <button type="submit" class="cm-btn cm-btn--accent"><i class="fa fa-check-circle"></i> Generate Manifest</button>
                <?php echo form_close(); ?>
            <?php endif; ?>
        </div>
        <?php $this->load->view('courier_goshipping/cargo_manifest/_rows', ['rows' => $rows, 'totals' => $totals]); ?>
    </div>
</div>

    </div><!-- .cgs-page -->
</div><!-- #wrapper -->
<?php init_tail(); ?>
