<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php load_courier_styles(); ?>
<?php
/* ── Template resolution ────────────────────────────────────────────────── */
$_cn_tpl       = get_option('courier_cn_template') ?: 'standard';
$_valid_cn_tpl = ['standard','shavan','corporate_blue','express_red','thermal'];
if (!in_array($_cn_tpl, $_valid_cn_tpl, true)) $_cn_tpl = 'standard';
$_tpl_path = dirname(__FILE__) . '/consignment_note_templates/' . $_cn_tpl . '.php';
if (!is_file($_tpl_path)) {
    $_tpl_path = dirname(__FILE__) . '/consignment_note_templates/standard.php';
}

/* ── Shared variables for all templates ─────────────────────────────────── */
$company_logo_file = get_option('company_logo_dark') ?: get_option('company_logo');
$company_logo_url  = !empty($company_logo_file) ? base_url('uploads/company/' . $company_logo_file) : '';
$_ci               = courier_get_invoice_info();
$logistic_company  = $_ci['name'] ?: 'Our Company';
$s                 = $shipment_details['shipment'];

$is_sender_ind = $shipment_details['sender_type'] === 'individual';
$sender        = $shipment_details['sender'];
$snd_name  = $is_sender_ind
    ? trim($sender->first_name . ' ' . $sender->last_name)
    : $sender->company_name . ' (' . $sender->contact_person_name . ')';
$snd_addr  = $is_sender_ind
    ? $sender->address . ', ' . $sender->zipcode
    : $sender->contact_address . ', ' . $sender->contact_zipcode;
$snd_phone    = $is_sender_ind ? $sender->phone_number : $sender->contact_person_phone_number;
$snd_email    = $is_sender_ind ? ($sender->email ?? '') : ($sender->contact_person_email ?? '');
$snd_country  = !empty($shipment_details['sender_country']) ? $shipment_details['sender_country']->short_name : '';

$is_recip_ind = $shipment_details['recipient_type'] === 'individual';
$recipient    = $shipment_details['recipient'];
$rec_name  = $is_recip_ind
    ? trim($recipient->first_name . ' ' . $recipient->last_name)
    : ($recipient->recipient_company_name ?? '') . (!empty($recipient->recipient_contact_person_name) ? ' (' . $recipient->recipient_contact_person_name . ')' : '');
$rec_addr  = $is_recip_ind
    ? ($recipient->address ?? '') . ', ' . ($recipient->zipcode ?? '')
    : ($recipient->recipient_contact_address ?? '') . ', ' . ($recipient->recipient_contact_zipcode ?? '');
$rec_phone    = $is_recip_ind ? ($recipient->phone_number ?? '') : ($recipient->recipient_contact_person_phone_number ?? '');
$rec_email    = $is_recip_ind ? ($recipient->email ?? '') : ($recipient->recipient_contact_person_email ?? '');
$rec_country  = !empty($shipment_details['recipient_country']) ? $shipment_details['recipient_country']->short_name : '';
$vat_on       = !empty($s->vat_applicable);
$waybill_back_url = admin_url('courier_logistic/shipments/waybill/' . $s->id);

include $_tpl_path;
return;
?>

