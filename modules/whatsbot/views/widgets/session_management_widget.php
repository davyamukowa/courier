<?php

defined('BASEPATH') || exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('whatsbot/whatsbot_model');

$enabled = (get_option('enable_session_management') == '1' ? true : false);

// Get session management stats
$today = date('Y-m-d H:i:s', strtotime('today'));
$tomorrow = date('Y-m-d H:i:s', strtotime('tomorrow'));

// Get count of sessions reset today
$CI->db->where('time_sent >=', $today);
$CI->db->where('time_sent <', $tomorrow);
$CI->db->where('is_system', 1);
$resetToday = $CI->db->count_all_results(db_prefix() . 'wtc_interaction_messages');

// Get count of active sessions
$hoursThreshold = intval(get_option('session_expiry_hours'));
$expiryTimestamp = date('Y-m-d H:i:s', strtotime("-{$hoursThreshold} hours"));

$CI->db->where('last_msg_time >=', $expiryTimestamp);
$CI->db->where('session_reset_sent', 0);
$activeSessions = $CI->db->count_all_results(db_prefix() . 'wtc_interactions');

// Get count of expiring sessions
$CI->db->where('last_msg_time <', $expiryTimestamp);
$CI->db->where('last_msg_time >=', date('Y-m-d H:i:s', strtotime('-24 hours')));
$CI->db->where('session_reset_sent', 0);
$expiringSessions = $CI->db->count_all_results(db_prefix() . 'wtc_interactions');

// Get count of sessions reset in the last 7 days
$weekAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
$CI->db->where('time_sent >=', $weekAgo);
$CI->db->where('is_system', 1);
$resetWeek = $CI->db->count_all_results(db_prefix() . 'wtc_interaction_messages');
?>

<div class="widget" id="widget-<?php echo create_widget_id(); ?>">
    <div class="panel_s">
        <div class="panel-body padding-10">
            <div class="widget-dragger ui-sortable-handle"></div>
            <div class="tw-flex tw-justify-between tw-items-center tw-p-1.5">
                <p class="tw-font-semibold tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse"> <i class="fa-brands fa-whatsapp fa-lg"></i> <span class="tw-text-neutral-600"><?php echo _l('whatsapp_session_management'); ?></span> </p>
            </div>
            <hr class="-tw-mx-3 tw-mt-2 tw-mb-4">
            <?php if ($enabled) : ?>
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <div class="panel_s">
                            <div class="panel-body" style="border-radius: 4px; background-color: #fff; padding: 15px; margin: 0 5px 15px 5px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <h3 style="font-size: 28px; margin: 0; font-weight: 600; color: #5bc0de;"><?php echo $activeSessions; ?></h3>
                                        <span style="color: #777;"><?php echo _l('active_sessions'); ?></span>
                                    </div>
                                    <div style="background-color: #5bc0de; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-comments fa-lg" style="color: #fff;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-6">
                        <div class="panel_s">
                            <div class="panel-body" style="border-radius: 4px; background-color: #fff; padding: 15px; margin: 0 5px 15px 5px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <h3 style="font-size: 28px; margin: 0; font-weight: 600; color: #f0ad4e;"><?php echo $expiringSessions; ?></h3>
                                        <span style="color: #777;"><?php echo _l('expiring_sessions'); ?></span>
                                    </div>
                                    <div style="background-color: #f0ad4e; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-clock fa-lg" style="color: #fff;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <div class="panel_s">
                            <div class="panel-body" style="border-radius: 4px; background-color: #fff; padding: 15px; margin: 0 5px 15px 5px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <h3 style="font-size: 28px; margin: 0; font-weight: 600; color: #33b86b;"><?php echo $resetToday; ?></h3>
                                        <span style="color: #777;"><?php echo _l('sessions_reset_today'); ?></span>
                                    </div>
                                    <div style="background-color: #33b86b; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-refresh fa-lg" style="color: #fff;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-6">
                        <div class="panel_s">
                            <div class="panel-body" style="border-radius: 4px; background-color: #fff; padding: 15px; margin: 0 5px 15px 5px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <h3 style="font-size: 28px; margin: 0; font-weight: 600; color: #9c59b6;"><?php echo $resetWeek; ?></h3>
                                        <span style="color: #777;"><?php echo _l('sessions_reset_week'); ?></span>
                                    </div>
                                    <div style="background-color: #9c59b6; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-calendar fa-lg" style="color: #fff;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center" style="padding: 20px;">
                    <i class="fa fa-toggle-off text-muted" style="font-size: 32px; margin-bottom: 10px;"></i>
                    <p class="text-muted"><?php echo _l('session_management_disabled'); ?></p>
                    <a href="<?php echo admin_url('settings?group=whatsbot&tab=session_management'); ?>" class="btn btn-sm btn-primary" style="box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                        <i class="fa fa-cog" style="margin-right: 5px;"></i> <?php echo _l('enable_now'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>