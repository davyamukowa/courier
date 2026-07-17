<?php
$list_base = admin_url('courier/shipments?type=domestic&status=');
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
<style>
.courier-stat-yellow { background: linear-gradient(135deg,#f9a825,#f57f17); }
.courier-stat-red    { background: linear-gradient(135deg,#e53935,#b71c1c); }
.sh-total-bar { display:flex; align-items:center; justify-content:space-between; background:#f5f5f5; border-radius:6px; padding:10px 16px; margin-bottom:18px; font-size:13px; }
.sh-total-bar strong { font-size:22px; }
</style>

<!-- Total bar -->
<div class="sh-total-bar">
    <span><i class="fa fa-globe" style="color:#1976d2;margin-right:6px;"></i> All Shipments: <strong style="color:#1976d2;"><?php echo $total_all; ?></strong></span>
    <a href="<?php echo admin_url('courier/shipments?type=domestic'); ?>" class="btn btn-xs btn-primary">View All</a>
</div>

<!-- Stat grid -->
<div class="courier-stats-grid">
<?php foreach ($status_cards as $card): ?>
    <a href="<?php echo $list_base . $card['id']; ?>" style="text-decoration:none;">
        <div class="courier-stat-card" style="transition:transform .15s,box-shadow .15s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 6px 18px rgba(0,0,0,.15)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
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
