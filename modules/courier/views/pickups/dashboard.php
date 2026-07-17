<div class="courier-stats-grid">
    <div class="courier-stat-card">
        <div class="courier-stat-icon courier-stat-orange">
            <i class="fa fa-clock"></i>
        </div>
        <div class="courier-stat-body">
            <div class="courier-stat-count"><?= $status_counts['pending'] ?? 0 ?></div>
            <div class="courier-stat-label">Pending</div>
        </div>
    </div>
    <div class="courier-stat-card">
        <div class="courier-stat-icon courier-stat-blue">
            <i class="fa fa-truck"></i>
        </div>
        <div class="courier-stat-body">
            <div class="courier-stat-count"><?= $status_counts['picked_up'] ?? 0 ?></div>
            <div class="courier-stat-label">Picked Up</div>
        </div>
    </div>
    <div class="courier-stat-card">
        <div class="courier-stat-icon courier-stat-green">
            <i class="fa fa-handshake"></i>
        </div>
        <div class="courier-stat-body">
            <div class="courier-stat-count"><?= $status_counts['delivered'] ?? 0 ?></div>
            <div class="courier-stat-label">Delivered</div>
        </div>
    </div>
</div>
