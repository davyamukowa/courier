<?php init_head(); ?>
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="wrapper">
	<div class="content">
		<?php
		$update_errors = [];
		$latest_version = isset($update['latest_version']) ? $update['latest_version'] : '';
		$database_upgrade_is_required = $this->app_modules->is_database_upgrade_required($module['system_name']);
		?>

		<?php if (!empty($support)): ?>
			<div class="col-md-12">
				<div class="alert alert-<?= $support['type'] ?>">
					<div class="tw-flex tw-items-center tw-justify-between">
						<span class="bold tw-text-xl"><?= _l('support') ?></span>
						<span><?= _l('support_ticket_content') ?></span>
					</div>
					<div class="tw-flex tw-items-center tw-justify-between">
						<p class="mtop10"><?= $support['message'] ?></p>

						<a href="<?= $support['support_url'] ?>" class="btn btn-default tw-w-48" target="_blank"><i class="fa-solid fa-up-right-from-square mright5"></i><?= _l('create_support_ticket') ?></a>
					</div>
				</div>
			</div>
		<?php endif ?>
		<?php if (!$database_upgrade_is_required): ?>
			<div class="col-md-6">
				<?= form_open($submit_url, ['id' => 'update_module_version_form']); ?>
				<div class="panel">
					<div class="panel-heading">
						<h4 class="panel-title text-info"><?= _l('module_update') ?></h4>
					</div>
					<div class="panel-body">
						<div class="col-md-6 text-center">
							<div class="alert alert-<?= $latest_version > $module['installed_version'] ? 'danger' : 'info'; ?>">
								<h4 class="margin-bottom-5"><?= _l('your_version'); ?></h4>
								<p class="margin-bottom-0"><strong><?= wordwrap($module['installed_version'], 1, '.', true); ?></strong></p>
							</div>
						</div>
						<div class="col-md-6 text-center">
							<?php $alert = ($latest_version > $module['installed_version']) ? 'success' : ($latest_version == $module['installed_version'] ? 'info' : ''); ?>
							<div class="alert alert-<?= $alert ?>">
								<h4 class="margin-bottom-5"><?= _l('latest_version'); ?></h4>
								<p class="margin-bottom-0"><strong><?= wordwrap($latest_version, 1, '.', true); ?></strong></p>
								<?= form_hidden('latest_version', $latest_version); ?>
								<?= form_hidden('update_id', $update['update_id'] ?? '000'); ?>
								<?= form_hidden('has_sql', $update['has_sql_update'] ?? false); ?>
							</div>
						</div>
						<div class="clearfix"></div>
						<hr />
						<div class="col-md-12">
							<?= render_input('purchase_key', 'purchase_key', '', '', ['autocomplete' => 'off']); ?>
						</div>
						<div class="col-md-12">
							<?= render_input('username', 'username', '', '', ['autocomplete' => 'off']); ?>
						</div>
						<div class="col-md-12 text-center">
							<?php if ($module['installed_version'] != $latest_version && $latest_version > $module['installed_version']) { ?>
								<h3 class="text-center margin-bottom-20">
									<i class="fa-solid fa-bell fa-shake"></i> <?= _l('update_available'); ?>
								</h3>
								<div class="update_app_wrapper" data-wait-text="<?= _l('wait_text'); ?>"
									data-original-text="<?= _l('update_now'); ?>">
									<?php if (count($update_errors) == 0) { ?>
										<button type="submit" class="btn btn-success" id="download_files"><?= _l('download_files') ?></button>
									<?php } ?>
								</div>
								<?php if ($module['installed_version'] != $latest_version && $latest_version > $module['installed_version']) { ?>
									<div class="col-md-12 margin-top-20">
										<div class="alert alert-warning">
											<?= _l('update_warning'); ?>
										</div>
									</div>
								<?php } ?>
								<div id="update_messages" class="margin-top-25 text-left"></div>
							<?php } else { ?>
								<h3 class="text-success">
									<?= _l('using_latest_version'); ?>
								</h3>
							<?php } ?>
							<?php if (count($update_errors) > 0) { ?>
								<div class="margin-top-20">
									<p class="text-danger"><?= _l('fix_errors'); ?></p>
									<?php foreach ($update_errors as $error) { ?>
										<div class="alert alert-danger">
											<?= e($error); ?>
										</div>
									<?php } ?>
								</div>
							<?php } ?>
						</div>

					</div>
				</div>
				<?= form_close() ?>
			</div>
			<div class="col-md-6">
				<div class="panel ">
					<div class="panel-heading">
						<h4 class="panel-title">
							<?= _l('changelog') ?>
						</h4>
					</div>
					<div class="panel-body" style="max-height: 500px; overflow-y: auto;">
						<?php if (isset($versionLog['versions']) && count($versionLog['versions']) > 0): ?>
							<!-- Loading Indicator -->
							<div id="changelog-loading" class="text-center" style="display: none;">
								<i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
								<p class="text-muted">Loading changelog...</p>
							</div>

							<!-- Changelog Content -->
							<div id="changelog-content">
								<?php if (!empty($versionLog['versions'])): ?>
									<?php foreach ($versionLog['versions'] as $index => $version): ?>
										<div class="panel">
											<!-- Version Header -->
											<div class="panel-heading <?= $version['is_latest'] ? 'tw-bg-success-100/55' : '' ?> cursor-pointer" 
												 data-toggle="collapse" 
												 data-target="#version-<?= $index ?>" 
												 aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>">
												<div class="row">
													<div class="col-xs-10">
														<div class="media">
															<div class="media-left">
																<span class="<?= $version['is_latest'] ? 'text-success' : '' ?>">
																	<i class="fa fa-tag"></i>
																</span>
															</div>
															<div class="media-body">
																<h5 class="media-heading <?= $version['is_latest'] ? 'text-white' : 'text-dark' ?> margin-bottom-0">
																	<?= $version['version'] ?>
																	<?php if ($version['is_latest']): ?>
																		<span class="label label-warning">Latest</span>
																	<?php endif; ?>
																</h5>
																<small class="<?= $version['is_latest'] ? 'text-white' : 'text-muted' ?>">
																	<?= $version['date'] ?>
																</small>
															</div>
														</div>
													</div>
													<div class="col-xs-2 text-right">
														<i class="fa fa-chevron-down <?= $version['is_latest'] ? 'text-white' : 'text-muted' ?>"></i>
													</div>
												</div>
											</div>

											<!-- Change Items -->
											<div id="version-<?= $index ?>" class="panel-collapse collapse <?= $index === 0 ? 'in' : '' ?>">
												<div class="panel-body">
													<?php 
													$hasContentInFifTypes = false;
													foreach ($version['changes'] as $change) {
														if (in_array($change['type'], ['feature', 'improvement', 'bug']) && !empty($change['description'])) {
															$hasContentInFifTypes = true;
															break;
														}
													}
													?>

													<!-- Display feature, improvement, bug if they have content -->
													<?php foreach ($version['changes'] as $change): ?>
														<?php if (in_array($change['type'], ['feature', 'improvement', 'bug']) && !empty($change['description'])): ?>
															<div class="media margin-bottom-15">
																<?php if ($change['type'] === 'feature'): ?>
																	<div class="media-left">
																		<span class="label label-info">
																			<i class="fa fa-plus"></i>
																		</span>
																	</div>
																	<div class="media-body">
																		<h6 class="media-heading text-info">New Feature</h6>
																		<p class="text-muted"><?= $change['description'] ?></p>
																	</div>
																<?php elseif ($change['type'] === 'improvement'): ?>
																	<div class="media-left">
																		<span class="label label-primary">
																			<i class="fa fa-arrow-up"></i>
																		</span>
																	</div>
																	<div class="media-body">
																		<h6 class="media-heading text-primary">Improvement</h6>
																		<p class="text-muted"><?= $change['description'] ?></p>
																	</div>
																<?php elseif ($change['type'] === 'bug'): ?>
																	<div class="media-left">
																		<span class="label label-danger">
																			<i class="fa fa-bug"></i>
																		</span>
																	</div>
																	<div class="media-body">
																		<h6 class="media-heading text-danger">Bug Fix</h6>
																		<p class="text-muted"><?= $change['description'] ?></p>
																	</div>
																<?php endif; ?>
															</div>
														<?php endif; ?>
													<?php endforeach; ?>

													<!-- Only show changelog if none of the FIF types have content -->
													<?php if (!$hasContentInFifTypes): ?>
														<?php foreach ($version['changes'] as $change): ?>
															<?php if ($change['type'] === 'changelog'): ?>
																<div class="media">
																	<div class="media-body">
																		<p class="text-muted"><?= $change['description'] ?></p>
																	</div>
																</div>
															<?php endif; ?>
														<?php endforeach; ?>
													<?php endif; ?>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								<?php else: ?>
									<div class="well text-center">
										<i class="fa fa-info-circle fa-2x text-muted"></i>
										<p class="text-muted margin-top-10">No release information available</p>
									</div>
								<?php endif; ?>
							</div>
						<?php else: ?>
							<div class="well text-center">
								<i class="fa fa-info-circle fa-2x text-muted"></i>
								<p class="text-muted margin-top-10">No changelog available for this version.</p>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endif ?>

		<?php if ($database_upgrade_is_required): ?>
			<div class="col-md-6 col-md-offset-3 text-center">
				<div class="panel ">
					<div class="panel-heading bg-danger">
						<h4 class="panel-title text-white"><?= _l('database_upgrade_required') ?></h4>
					</div>
					<div class="panel-body">
						<p class="margin-top-20">
							<?= _l('update_content_1') ?>
							<?= _l('update_content_2') ?>
							<span class="text-success"><strong><?= wordwrap($module['headers']['version'], 1, '.', true); ?></strong></span>
							<?= _l('update_content_3') ?>
							<span class="text-danger"><strong><?= wordwrap($module['installed_version'], 1, '.', true); ?></strong></span>.
						</p>
						<p class="margin-top-15"><strong><?= _l('update_content_4') ?></strong></p>
						<div class="margin-top-20">
							<a href="<?= admin_url('modules/upgrade_database/' . $module['system_name']) ?>" class="btn btn-success btn-lg"><?= _l('upgrade_now') ?></a>
						</div>
						<p class="text-muted margin-top-20">
							<small><?= _l('update_content_5') ?></small>
						</p>
					</div>
				</div>
			</div>
		<?php endif ?>
</div>
</div>

<?php init_tail(); ?>

<style>
.cursor-pointer {
	cursor: pointer;
}
.margin-bottom-0 {
	margin-bottom: 0;
}
.margin-bottom-5 {
	margin-bottom: 5px;
}
.margin-bottom-15 {
	margin-bottom: 15px;
}
.margin-bottom-20 {
	margin-bottom: 20px;
}
.margin-top-10 {
	margin-top: 10px;
}
.margin-top-15 {
	margin-top: 15px;
}
.margin-top-20 {
	margin-top: 20px;
}
.margin-top-25 {
	margin-top: 25px;
}
.margin-right-5 {
	margin-right: 5px;
}
</style>

<script type="text/javascript">
	appValidateForm($('#update_module_version_form'), {
		purchase_key: 'required',
		username: 'required'
	}, update_module_version);

	function update_module_version(form) {
		$("#download_files").prop('disabled', true).prepend('<i class="fa fa-spinner fa-pulse"></i> ');
		$.post(form.action, $(form).serialize()).done(function(response) {
			var response = $.parseJSON(response);
			alert_float(response.type, response.message);
			if (response.type == 'success') {
				window.location.href = response.url;
			}
			$("#download_files").prop('disabled', false).find('i').remove();
		});
	}

	// Enhance collapse functionality
	$(document).ready(function() {
		$('[data-toggle="collapse"]').on('click', function() {
			var target = $(this).data('target');
			var icon = $(this).find('.fa-chevron-down');
			
			$(target).on('shown.bs.collapse', function() {
				icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
			});
			
			$(target).on('hidden.bs.collapse', function() {
				icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
			});
		});
	});
</script>