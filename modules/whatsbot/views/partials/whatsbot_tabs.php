<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php
$tabs = $tabs ?? [];
$active_tab = $active_tab ?? '';

if (!empty($tabs)) {
    $tab_keys = array_column($tabs, 'key');
    if (($active_tab === '' || !in_array($active_tab, $tab_keys, true)) && !empty($tab_keys)) {
        $active_tab = $tab_keys[0];
    }
}
?>
<?php if (!empty($tabs)) { ?>
<div class="horizontal-scrollable-tabs tw-min-h-0 tw-px-3">
    <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
    <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
    <div class="horizontal-tabs -tw-mx-[calc(theme(spacing.3)-1px)]">
        <ul class="nav nav-tabs nav-tabs-segmented nav-tabs-horizontal project-tabs" role="tablist">
            <?php foreach ($tabs as $tab) { 
                if(isset($tab['visible']) && !$tab['visible']){
                    continue;
                }
                ?>
                <li class="<?php echo $active_tab === $tab['key'] ? 'active ' : ''; ?>project_tab_<?php echo e($tab['key']); ?>">
                    <a data-group="<?php echo e($tab['key']); ?>" role="tab" href="<?php echo e($tab['url']); ?>">
                        <?php if (!empty($tab['icon'])) { ?>
                            <i class="<?php echo e($tab['icon']); ?> menu-icon" aria-hidden="true"></i>
                        <?php } ?>
                        <?php echo e($tab['label']); ?>
                        <?php if (isset($tab['badge'], $tab['badge']['value']) && !empty($tab['badge'])) { ?>
                            <span class="badge pull-right mleft5 <?= isset($tab['badge']['type']) && $tab['badge']['type'] != '' ? "bg-{$tab['badge']['type']}" : 'bg-info' ?>"
                                <?=(isset($tab['badge']['type']) && $tab['badge']['type'] == '') || isset($tab['badge']['color']) ? "style='background-color: {$tab['badge']['color']}'" : '' ?>>
                                <?= e($tab['badge']['value']) ?>
                            </span>
                        <?php } ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>
<?php } ?>
