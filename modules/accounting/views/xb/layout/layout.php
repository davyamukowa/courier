<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <link rel="stylesheet" href="<?php echo module_dir_url('accounting', 'assets/css/xb_accounting.css'); ?>">
    <?php $this->load->view('accounting/xb/layout/nav'); ?>
    <div class="xb-page-wrap">
      <?php if (!empty($xb_breadcrumb)): ?>
      <div class="xb-breadcrumb">
        <a href="<?php echo admin_url('xetuu_books'); ?>">Xetuu Books</a>
        <?php foreach ($xb_breadcrumb as $label => $url): ?>
          <span class="xb-bc-sep">/</span>
          <?php if ($url): ?><a href="<?php echo $url; ?>"><?php echo htmlspecialchars($label); ?></a>
          <?php else: ?><span class="xb-bc-active"><?php echo htmlspecialchars($label); ?></span><?php endif; ?>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <?php if (!empty($page_title)): ?>
      <div class="xb-page-header">
        <h1 class="xb-page-title"><?php echo htmlspecialchars($page_title); ?></h1>
        <?php if (!empty($page_actions)): ?>
        <div class="xb-page-actions"><?php echo $page_actions; ?></div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <?php
        $flash = $this->session->flashdata('xb_success');
        if ($flash): ?>
      <div class="xb-alert xb-alert-success"><?php echo htmlspecialchars($flash); ?></div>
      <?php endif;
        $flash_err = $this->session->flashdata('xb_error');
        if ($flash_err): ?>
      <div class="xb-alert xb-alert-danger"><?php echo htmlspecialchars($flash_err); ?></div>
      <?php endif; ?>
      <div class="xb-content">
        <?php echo $xb_content ?? $content_view ?? ''; ?>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<script src="<?php echo module_dir_url('accounting', 'assets/js/xb_accounting.js'); ?>"></script>
