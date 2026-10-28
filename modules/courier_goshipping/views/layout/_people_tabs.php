<?php defined('BASEPATH') or exit('No direct script access allowed');
// Users / Riders / Agents switcher. $cgs_people_active: 'users' | 'riders' | 'agents'
$cgs_people_active = $cgs_people_active ?? 'users';
$cgs_people_tabs   = [
    'users'  => ['Users',  'fa-user',       admin_url('staff')],
    'riders' => ['Riders', 'fa-motorcycle', admin_url('courier_goshipping/fulfilment/riders')],
    'agents' => ['Agents', 'fa-handshake-o', admin_url('courier_goshipping/agents/main')],
];
?>
<div class="cgs-people-tabs" style="margin-bottom:15px;display:flex;gap:8px;flex-wrap:wrap;">
    <?php foreach ($cgs_people_tabs as $key => $tab) { ?>
        <a href="<?php echo $tab[2]; ?>"
           class="btn <?php echo $key === $cgs_people_active ? 'btn-primary' : 'btn-default'; ?>">
            <i class="fa <?php echo $tab[1]; ?>"></i> <?php echo $tab[0]; ?>
        </a>
    <?php } ?>
</div>
