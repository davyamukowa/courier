<?php defined('BASEPATH') or exit('No direct script access allowed');

$header_context = $header_context ?? 'list';
$header_type = $header_type ?? ($this->input->get('type') ?: $this->session->userdata('type') ?: 'domestic');
$header_mode = $header_mode ?? ($this->input->get('mode') ?: $this->session->userdata('mode'));
$header_mode_type = $header_mode_type ?? ($this->input->get('mode_type') ?: $this->session->userdata('mode_type'));
$header_shipment_counts = $shipment_counts ?? [];
$header_shipment_details = $shipment_details ?? [];

$header_title = 'Shipment Dashboard';
$header_subtitle = 'Track the active area, follow the shipment flow, and keep the team oriented at a glance.';
$header_crumbs = [
    ['label' => 'Courier GoShipping', 'url' => admin_url('courier_goshipping/shipments/main')],
    ['label' => 'Shipments', 'url' => admin_url('courier_goshipping/shipments/main')],
];

if ($header_context === 'dashboard') {
    $header_crumbs[] = ['label' => 'Shipment Dashboard', 'url' => null];
} else {
    $header_titles = [
        'domestic' => 'Domestic Shipment',
        'international|courier|' => 'International Courier',
        'international|road|' => 'International Road',
        'international|air|air_freight' => 'Air Freight',
        'international|air|air_consolidation' => 'Air Consolidation',
        'international|sea|fcl' => 'FCL',
        'international|sea|lcl' => 'LCL',
        'international|sea|sea_consolidation' => 'Consolidation',
    ];

    $header_key = $header_type . '|' . ($header_mode ?: '') . '|' . (($header_mode_type && $header_mode_type !== 'none') ? $header_mode_type : '');
    $header_title = $header_titles[$header_key] ?? ($header_titles[$header_type] ?? 'Shipment List');
    $header_subtitle = 'You are currently working inside the ' . $header_title . ' page. The summary below reflects the visible shipments on this screen.';
    $header_crumbs[] = ['label' => ($header_type === 'international' ? 'International' : 'Domestic'), 'url' => null];
    if (!empty($header_mode)) {
        $header_crumbs[] = ['label' => ucfirst($header_mode), 'url' => null];
    }
    if (!empty($header_mode_type) && $header_mode_type !== 'none') {
        $header_crumbs[] = ['label' => ucwords(str_replace('_', ' ', $header_mode_type)), 'url' => null];
    }
    $header_crumbs[] = ['label' => $header_title, 'url' => null];
}

$summary_cards = [];

if ($header_context === 'dashboard') {
    $header_total = array_sum($header_shipment_counts);
    $header_created = (int) ($header_shipment_counts['1'] ?? 0);
    $header_in_transit = (int) (($header_shipment_counts['4'] ?? 0) + ($header_shipment_counts['5'] ?? 0) + ($header_shipment_counts['6'] ?? 0) + ($header_shipment_counts['7'] ?? 0));
    $header_delivered = (int) ($header_shipment_counts['8'] ?? 0);
    $header_cancelled = (int) ($header_shipment_counts['9'] ?? 0);

    $summary_cards = [
        ['label' => 'Total Shipments', 'value' => $header_total, 'tone' => 'blue', 'icon' => 'fa-cubes'],
        ['label' => 'Created', 'value' => $header_created, 'tone' => 'teal', 'icon' => 'fa-plus-circle'],
        ['label' => 'In Progress', 'value' => $header_in_transit, 'tone' => 'amber', 'icon' => 'fa-truck'],
        ['label' => 'Delivered', 'value' => $header_delivered, 'tone' => 'green', 'icon' => 'fa-check-circle'],
        ['label' => 'Cancelled', 'value' => $header_cancelled, 'tone' => 'red', 'icon' => 'fa-times-circle'],
    ];
} else {
    $header_total = count($header_shipment_details);
    $header_delivered = 0;
    $header_in_progress = 0;
    $header_created = 0;
    $header_cancelled = 0;
    $header_unique_agents = [];

    foreach ($header_shipment_details as $header_detail) {
        $header_status = strtolower((string) ($header_detail['shipment']->status_name ?? ''));
        $header_staff_id = (string) ($header_detail['shipment']->staff_id ?? '');
        if ($header_staff_id !== '') {
            $header_unique_agents[$header_staff_id] = true;
        }

        if ($header_status === 'delivered') {
            $header_delivered++;
        } elseif ($header_status === 'cancelled') {
            $header_cancelled++;
        } elseif ($header_status === 'created') {
            $header_created++;
        } else {
            $header_in_progress++;
        }
    }

    $summary_cards = [
        ['label' => 'Visible Shipments', 'value' => $header_total, 'tone' => 'blue', 'icon' => 'fa-list'],
        ['label' => 'Created', 'value' => $header_created, 'tone' => 'teal', 'icon' => 'fa-plus-circle'],
        ['label' => 'In Progress', 'value' => $header_in_progress, 'tone' => 'amber', 'icon' => 'fa-random'],
        ['label' => 'Delivered', 'value' => $header_delivered, 'tone' => 'green', 'icon' => 'fa-check-circle'],
        ['label' => 'Cancelled', 'value' => $header_cancelled, 'tone' => 'red', 'icon' => 'fa-times-circle'],
        ['label' => 'Assigned Agents', 'value' => count($header_unique_agents), 'tone' => 'slate', 'icon' => 'fa-user'],
    ];
}
?>
<style>
.cgs-page-header {
    margin: 0 0 20px;
    padding: 22px 24px;
    background: linear-gradient(135deg, #f7fbff 0%, #eef6ff 48%, #ffffff 100%);
    border: 1px solid #dbe7f3;
    border-radius: 18px;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
}
.cgs-page-header__breadcrumbs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    margin-bottom: 14px;
    font-size: 12px;
    font-weight: 700;
    color: #5f7288;
    text-transform: uppercase;
    letter-spacing: .06em;
}
.cgs-page-header__breadcrumbs a {
    color: #2f6fb2;
    text-decoration: none;
}
.cgs-page-header__breadcrumbs span.is-current {
    color: #0f2740;
}
.cgs-page-header__title-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 18px;
    margin-bottom: 16px;
}
.cgs-page-header__title {
    margin: 0;
    font-size: 29px;
    line-height: 1.1;
    font-weight: 800;
    color: #102a43;
}
.cgs-page-header__subtitle {
    margin: 8px 0 0;
    max-width: 820px;
    font-size: 14px;
    line-height: 1.55;
    color: #5f7288;
}
.cgs-page-header__badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 12px;
    background: #ffffff;
    border: 1px solid #d8e4ef;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    color: #2f6fb2;
    white-space: nowrap;
}
.cgs-page-header__stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 12px;
}
.cgs-page-header__stat {
    background: #ffffff;
    border: 1px solid #e3ebf3;
    border-radius: 14px;
    padding: 14px 15px;
    min-height: 88px;
}
.cgs-page-header__stat-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.cgs-page-header__stat-icon {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
}
.cgs-page-header__stat-value {
    font-size: 28px;
    line-height: 1;
    font-weight: 800;
    color: #102a43;
}
.cgs-page-header__stat-label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #6b7c93;
}
.cgs-page-header__stat--blue .cgs-page-header__stat-icon { background: #e8f1ff; color: #2f6fb2; }
.cgs-page-header__stat--teal .cgs-page-header__stat-icon { background: #e6f7f4; color: #0f8b7b; }
.cgs-page-header__stat--amber .cgs-page-header__stat-icon { background: #fff5df; color: #d17b00; }
.cgs-page-header__stat--green .cgs-page-header__stat-icon { background: #e8f8ec; color: #218c4f; }
.cgs-page-header__stat--red .cgs-page-header__stat-icon { background: #fdeaea; color: #c0392b; }
.cgs-page-header__stat--slate .cgs-page-header__stat-icon { background: #eef2f6; color: #52606d; }
@media (max-width: 767px) {
    .cgs-page-header {
        padding: 18px 16px;
    }
    .cgs-page-header__title-row {
        flex-direction: column;
    }
    .cgs-page-header__title {
        font-size: 24px;
    }
}
</style>

<div class="cgs-page-header">
    <div class="cgs-page-header__breadcrumbs">
        <?php foreach ($header_crumbs as $header_index => $header_crumb): ?>
            <?php if (!empty($header_crumb['url'])): ?>
                <a href="<?php echo $header_crumb['url']; ?>"><?php echo htmlspecialchars($header_crumb['label']); ?></a>
            <?php else: ?>
                <span class="<?php echo $header_index === count($header_crumbs) - 1 ? 'is-current' : ''; ?>"><?php echo htmlspecialchars($header_crumb['label']); ?></span>
            <?php endif; ?>
            <?php if ($header_index < count($header_crumbs) - 1): ?>
                <span><i class="fa fa-angle-right"></i></span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="cgs-page-header__title-row">
        <div>
            <h1 class="cgs-page-header__title"><?php echo htmlspecialchars($header_title); ?></h1>
            <p class="cgs-page-header__subtitle"><?php echo htmlspecialchars($header_subtitle); ?></p>
        </div>
        <div class="cgs-page-header__badge">
            <i class="fa fa-map-marker"></i>
            <span>Current Page: <?php echo htmlspecialchars($header_title); ?></span>
        </div>
    </div>

    <div class="cgs-page-header__stats">
        <?php foreach ($summary_cards as $summary_card): ?>
            <div class="cgs-page-header__stat cgs-page-header__stat--<?php echo $summary_card['tone']; ?>">
                <div class="cgs-page-header__stat-top">
                    <span class="cgs-page-header__stat-icon"><i class="fa <?php echo $summary_card['icon']; ?>"></i></span>
                    <span class="cgs-page-header__stat-value"><?php echo (int) $summary_card['value']; ?></span>
                </div>
                <div class="cgs-page-header__stat-label"><?php echo htmlspecialchars($summary_card['label']); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
