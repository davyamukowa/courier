<?php
$list_base = admin_url('courier_logistic/shipments?type=domestic&status=');
$total_all = array_sum($shipment_counts ?? []);

$status_cards = [
    ['id'=>'1', 'label'=>'Created',              'icon'=>'fa-box',             'color'=>'courier-stat-cyan',   ],
    ['id'=>'2', 'label'=>'Picked Up',             'icon'=>'fa-truck',           'color'=>'courier-stat-blue',   ],
    ['id'=>'3', 'label'=>'Received',              'icon'=>'fa-inbox',           'color'=>'courier-stat-teal',   ],
    ['id'=>'4', 'label'=>'Dispatched',            'icon'=>'fa-truck-loading',   'color'=>'courier-stat-orange', ],
    ['id'=>'5', 'label'=>'In Transit',            'icon'=>'fa-shipping-fast',   'color'=>'courier-stat-purple', ],
    ['id'=>'6', 'label'=>'Arrived Destination',   'icon'=>'fa-map-pin',         'color'=>'courier-stat-indigo', ],
    ['id'=>'7', 'label'=>'Out for Delivery',      'icon'=>'fa-map-marker-alt',  'color'=>'courier-stat-yellow', ],
    ['id'=>'8', 'label'=>'Delivered',             'icon'=>'fa-check-circle',    'color'=>'courier-stat-green',  ],
    ['id'=>'9', 'label'=>'Cancelled',             'icon'=>'fa-times-circle',    'color'=>'courier-stat-red',    ],
];
?>
<?php $this->load->view('courier_logistic/shipments/_page_header', ['header_context' => 'dashboard', 'shipment_counts' => $shipment_counts ?? []]); ?>
<style>
.sh-overview .btn,
.sh-overview .btn.btn-primary,
.sh-overview .btn.btn-default {
    border-radius: 10px;
    transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
}

.sh-overview .btn,
.sh-overview .btn.btn-primary {
    background: #c62828 !important;
    border-color: #c62828 !important;
    color: #fff !important;
    box-shadow: 0 8px 18px rgba(198, 40, 40, 0.18);
}

.sh-overview .btn:hover,
.sh-overview .btn:focus,
.sh-overview .btn.btn-primary:hover,
.sh-overview .btn.btn-primary:focus {
    background: #a61f1f !important;
    border-color: #a61f1f !important;
    color: #fff !important;
    box-shadow: 0 10px 22px rgba(166, 31, 31, 0.24);
}

.sh-overview .btn.btn-default {
    background: #fff1f1 !important;
    border-color: #d94a4a !important;
    color: #b42323 !important;
}

.sh-overview .btn.btn-default:hover,
.sh-overview .btn.btn-default:focus {
    background: #c62828 !important;
    border-color: #c62828 !important;
    color: #fff !important;
}

.courier-stat-yellow { background: linear-gradient(135deg,#f9a825,#f57f17); }
.courier-stat-red    { background: linear-gradient(135deg,#e53935,#b71c1c); }
.sh-overview {
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    border: 1px solid #dce6f2;
    border-radius: 18px;
    box-shadow: 0 10px 28px rgba(16, 24, 40, .08);
    padding: 22px 24px 24px;
}
.sh-total-bar {
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:16px;
    background: linear-gradient(135deg, #eef4fb 0%, #f7faff 100%);
    border: 1px solid #dde7f2;
    border-radius: 16px;
    padding: 18px 20px;
    margin-bottom: 22px;
}
.sh-total-bar__eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #476a91;
    margin-bottom: 8px;
}
.sh-total-bar__title {
    font-size: 26px;
    line-height: 1.1;
    font-weight: 800;
    color: #17324d;
}
.sh-total-bar__title strong {
    color:#2f6fb2;
    font-size: 34px;
    margin-left: 4px;
}
.sh-total-bar__meta {
    margin-top: 6px;
    font-size: 13px;
    color: #64748b;
}
.sh-total-bar .btn {
    border-radius: 10px;
    padding: 8px 14px;
    font-weight: 700;
}
.courier-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 18px;
}
.courier-stat-card {
    position: relative;
    background: #fff;
    border: 1px solid #e3ebf4;
    border-radius: 16px;
    min-height: 120px;
    padding: 18px 18px 18px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 8px 22px rgba(15, 23, 42, .06);
    overflow: hidden;
}
.courier-stat-card:before {
    content: "";
    position: absolute;
    left: 0;
    top: 16px;
    bottom: 16px;
    width: 4px;
    border-radius: 999px;
    background: linear-gradient(180deg, #30b45a 0%, #1d8f42 100%);
}
.courier-stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 22px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.18);
}
.courier-stat-body {
    min-width: 0;
}
.courier-stat-count {
    font-size: 24px;
    line-height: 1;
    font-weight: 800;
    color: #18283a;
    margin-bottom: 8px;
}
.courier-stat-label {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: #6b7b8f;
    white-space: normal;
}
@media (max-width: 767px) {
    .sh-overview {
        padding: 16px;
    }
    .sh-total-bar {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<div class="sh-overview">
    <div class="sh-total-bar">
        <div>
            <div class="sh-total-bar__eyebrow"><i class="fa fa-globe"></i><span>Shipment Operations</span></div>
            <div class="sh-total-bar__title">All Shipments:<strong><?php echo $total_all; ?></strong></div>
            <div class="sh-total-bar__meta">Live operational snapshot across all current shipment statuses.</div>
        </div>
        <a href="<?php echo admin_url('courier_logistic/shipments?type=domestic'); ?>" class="btn btn-primary">View All</a>
    </div>

    <div class="courier-stats-grid">
    <?php foreach ($status_cards as $card): ?>
        <a href="<?php echo $list_base . $card['id']; ?>" style="text-decoration:none;">
            <div class="courier-stat-card" style="transition:transform .15s,box-shadow .15s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 12px 28px rgba(15,23,42,.12)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                <div class="courier-stat-icon <?php echo $card['color']; ?>">
                    <i class="fa <?php echo $card['icon']; ?>"></i>
                </div>
                <div class="courier-stat-body">
                    <div class="courier-stat-count"><?php echo (int)($shipment_counts[$card['id']] ?? 0); ?></div>
                    <div class="courier-stat-label"><?php echo $card['label']; ?></div>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
    </div>
</div>

