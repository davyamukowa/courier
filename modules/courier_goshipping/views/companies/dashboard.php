<div class="cgs-stat-row">
    <div class="cgs-stat-card">
        <div class="cgs-stat-icon" style="background:#3a6ea5;"><i class="fa fa-bullseye" aria-hidden="true"></i></div>
        <div><div class="cgs-stat-val"><?= array_sum($type_counts) ?></div><div class="cgs-stat-lbl">Total Companies</div></div> <!-- Total count of all companies -->
    </div>
    <div class="cgs-stat-card">
        <div class="cgs-stat-icon" style="background:#ef6c00;"><i class="fa fa-building" aria-hidden="true"></i></div>
        <div><div class="cgs-stat-val"><?= $type_counts['internal'] ?></div><div class="cgs-stat-lbl">Internal Companies</div></div> <!-- Count of internal companies -->
    </div>
    <div class="cgs-stat-card">
        <div class="cgs-stat-icon" style="background:#c1272d;"><i class="fa fa-calculator" aria-hidden="true"></i></div>
        <div><div class="cgs-stat-val"><?= $type_counts['third_party'] ?></div><div class="cgs-stat-lbl">External Companies</div></div> <!-- Count of external companies -->
    </div>
</div>
