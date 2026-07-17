<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content" style="padding-top:0">
<?php $this->load->view('pos_system/admin/restaurant/_nav', ['rest_section'=>'index']); ?>
<div style="padding:24px">

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
  <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;padding:18px;display:flex;align-items:center;gap:12px">
    <div style="width:42px;height:42px;background:#dcfce7;color:#16a34a;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px"><i class="fa fa-chair"></i></div>
    <div><div style="font-size:22px;font-weight:800;color:#1a2332"><?php echo count($tables); ?></div><div style="font-size:11px;color:#94a3b8;text-transform:uppercase">Tables</div></div>
  </div>
  <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;padding:18px;display:flex;align-items:center;gap:12px">
    <div style="width:42px;height:42px;background:#fef9c3;color:#ca8a04;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px"><i class="fa fa-fire-alt"></i></div>
    <div><div style="font-size:22px;font-weight:800;color:#1a2332"><?php echo count($areas); ?></div><div style="font-size:11px;color:#94a3b8;text-transform:uppercase">Production Areas</div></div>
  </div>
  <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;padding:18px;display:flex;align-items:center;gap:12px">
    <div style="width:42px;height:42px;background:#fde8e8;color:#dc2626;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px"><i class="fa fa-fire"></i></div>
    <div><div style="font-size:22px;font-weight:800;color:#1a2332"><?php echo count($open_kots); ?></div><div style="font-size:11px;color:#94a3b8;text-transform:uppercase">Open KOTs</div></div>
  </div>
  <div style="background:#fff;border:1px solid #e4e9f0;border-radius:10px;padding:18px;display:flex;align-items:center;justify-content:center">
    <a href="<?php echo admin_url('pos_system/restaurant/kitchen'); ?>" class="btn btn-primary" target="_blank" style="width:100%;padding:10px">
      <i class="fa fa-tv"></i> Open Kitchen Display
    </a>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

  <!-- Tables Overview -->
  <div style="background:#fff;border:1px solid #e4e9f0;border-radius:12px;overflow:hidden">
    <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between">
      <strong><i class="fa fa-chair" style="color:#16a34a;margin-right:6px"></i> Tables — <?php echo count($tables); ?> total</strong>
      <a href="<?php echo admin_url('pos_system/restaurant/tables'); ?>" class="btn btn-default btn-xs"><i class="fa fa-cog"></i> Manage</a>
    </div>
    <div style="padding:16px 20px;display:flex;flex-wrap:wrap;gap:8px">
      <?php if (empty($tables)): ?>
        <span style="color:#94a3b8;font-size:13px">No tables configured yet. <a href="<?php echo admin_url('pos_system/restaurant/tables'); ?>">Add tables</a>.</span>
      <?php else: foreach ($tables as $t):
        $colors = ['free'=>'#dcfce7;color:#14532d','occupied'=>'#fde8e8;color:#dc2626','reserved'=>'#fef9c3;color:#854d0e'];
        $c = $colors[$t['status']] ?? '#f1f5f9;color:#475569';
      ?>
        <div style="background:<?php echo $c; ?>;border-radius:8px;padding:6px 12px;font-size:12px;font-weight:700;text-align:center;min-width:52px">
          <div><?php echo htmlspecialchars($t['table_number']); ?></div>
          <div style="font-size:10px;opacity:.8"><?php echo ucfirst($t['status']); ?></div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <!-- Open KOTs -->
  <div style="background:#fff;border:1px solid #e4e9f0;border-radius:12px;overflow:hidden">
    <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between">
      <strong><i class="fa fa-fire" style="color:#dc2626;margin-right:6px"></i> Open Kitchen Orders</strong>
      <a href="<?php echo admin_url('pos_system/restaurant/kitchen'); ?>" class="btn btn-danger btn-xs" target="_blank"><i class="fa fa-tv"></i> Kitchen View</a>
    </div>
    <?php if (empty($open_kots)): ?>
      <div style="padding:24px;text-align:center;color:#94a3b8;font-size:13px"><i class="fa fa-check-circle" style="color:#16a34a;font-size:20px;display:block;margin-bottom:8px"></i> No pending orders</div>
    <?php else: ?>
      <div style="max-height:260px;overflow-y:auto">
      <?php foreach ($open_kots as $kot): ?>
        <div style="padding:10px 20px;border-bottom:1px solid #f8fafc;display:flex;align-items:center;gap:10px">
          <span style="font-weight:700;font-size:12px;color:#1e293b"><?php echo htmlspecialchars($kot['kot_number']); ?></span>
          <span style="font-size:11px;color:#64748b">Table <?php echo htmlspecialchars($kot['table_number'] ?? '—'); ?></span>
          <span style="font-size:11px;color:#64748b">· <?php echo htmlspecialchars($kot['waiter_name'] ?? ''); ?></span>
          <span style="margin-left:auto;font-size:11px;padding:2px 8px;border-radius:10px;font-weight:600;
            <?php echo $kot['status']==='pending' ? 'background:#fef9c3;color:#854d0e' : 'background:#dcfce7;color:#14532d'; ?>">
            <?php echo ucfirst($kot['status']); ?>
          </span>
        </div>
      <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div>
</div>
</div>
</div>
<?php init_tail(); ?>
