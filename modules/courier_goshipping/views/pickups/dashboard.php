<div class="cgs-stat-row">
    <div class="cgs-stat-card">
        <div class="cgs-stat-icon" style="background:#ef6c00;">
            <i class="fa fa-clock"></i>
        </div>
        <div>
            <div class="cgs-stat-val"><?= $status_counts['pending'] ?? 0 ?></div>
            <div class="cgs-stat-lbl">Pending</div>
        </div>
    </div>
    <div class="cgs-stat-card">
        <div class="cgs-stat-icon" style="background:var(--cgs-primary,#3a6ea5);">
            <i class="fa fa-truck"></i>
        </div>
        <div>
            <div class="cgs-stat-val"><?= $status_counts['picked_up'] ?? 0 ?></div>
            <div class="cgs-stat-lbl">Picked Up</div>
        </div>
    </div>
    <div class="cgs-stat-card">
        <div class="cgs-stat-icon" style="background:#2e7d32;">
            <i class="fa fa-handshake"></i>
        </div>
        <div>
            <div class="cgs-stat-val"><?= $status_counts['delivered'] ?? 0 ?></div>
            <div class="cgs-stat-lbl">Delivered</div>
        </div>
    </div>
</div>
