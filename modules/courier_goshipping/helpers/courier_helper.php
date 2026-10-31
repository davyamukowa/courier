<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('courier_load_model')) {
    /**
     * Loads a courier_goshipping model by its exact filename, bypassing MX's
     * model loader ($this->load->model('courier_goshipping/Xxx_model')) —
     * MX lowercases the whole path then only ucfirst()'s the first letter
     * before checking is_file(), so any model filename with more than one
     * internal capital (CourierBranch_model, CountryState_model, etc.) silently
     * fails to resolve on case-sensitive (Linux) filesystems, even though it
     * works by accident on case-insensitive Windows dev boxes.
     */
    function courier_load_model($model_name, $alias = null)
    {
        $CI = &get_instance();
        $alias = $alias ?: $model_name;

        if (!isset($CI->$alias)) {
            if (!class_exists($model_name, false)) {
                require_once(module_dir_path('courier_goshipping', 'models/' . $model_name . '.php'));
            }
            $CI->$alias = new $model_name();
        }
    }
}

if (!function_exists('load_courier_styles')) {
    function load_courier_styles()
    {
        $css_path = FCPATH . 'modules/courier_goshipping/assets/main.css';
        $v = file_exists($css_path) ? filemtime($css_path) : time();
        echo '<link rel="stylesheet" href="' . base_url('modules/courier_goshipping/assets/main.css') . '?v=' . $v . '">';
    }
}

if (!function_exists('load_courier_scripts')) {
    function load_courier_scripts()
    {
        $js_path = FCPATH . 'modules/courier_goshipping/assets/create_shipment.js';
        $v = file_exists($js_path) ? filemtime($js_path) : time();
        echo '<script src="' . base_url('modules/courier_goshipping/assets/create_shipment.js') . '?v=' . $v . '"></script>';
    }
}

/**
 * Whether the current (or given) staff member can see every branch's data,
 * bypassing branch isolation. Admins always bypass.
 */
if (!function_exists('courier_staff_can_view_all_branches')) {
    function courier_staff_can_view_all_branches($staff_id = '')
    {
        return is_admin($staff_id) || staff_can('view_all_branches', 'courier-branches', $staff_id);
    }
}

/**
 * Branch IDs assigned to a staff member via tbl_courier_staff_branches.
 * Returns an empty array if the staff has no branch assignments.
 */
if (!function_exists('courier_get_staff_branch_ids')) {
    function courier_get_staff_branch_ids($staff_id = null)
    {
        $CI = &get_instance();
        $staff_id = $staff_id ?: get_staff_user_id();

        $rows = $CI->db->select('branch_id')
            ->where('staff_id', (int) $staff_id)
            ->get(db_prefix() . '_courier_staff_branches')
            ->result_array();

        $ids = array_map('intval', array_column($rows, 'branch_id'));

        // Country admins also reach every branch in their country(ies).
        if (function_exists('courier_get_admin_country_ids') && !is_admin($staff_id)) {
            $country_branches = courier_get_country_branch_ids(courier_get_admin_country_ids($staff_id));
            if (!empty($country_branches)) {
                $ids = array_values(array_unique(array_merge($ids, $country_branches)));
            }
        }

        return $ids;
    }
}

/**
 * The staff member's default branch (used for stamping new records and as a
 * single-branch fallback), or null if none assigned.
 */
if (!function_exists('courier_get_default_staff_branch_id')) {
    function courier_get_default_staff_branch_id($staff_id = null)
    {
        $CI = &get_instance();
        $staff_id = $staff_id ?: get_staff_user_id();

        $row = $CI->db->where('staff_id', (int) $staff_id)
            ->where('is_default', 1)
            ->get(db_prefix() . '_courier_staff_branches')
            ->row();

        if (!$row) {
            $row = $CI->db->where('staff_id', (int) $staff_id)
                ->order_by('id', 'asc')
                ->limit(1)
                ->get(db_prefix() . '_courier_staff_branches')
                ->row();
        }

        return $row ? (int) $row->branch_id : null;
    }
}

/**
 * The branch a staff member is currently "operating as" this session — the
 * one they picked at login if they have multiple, otherwise their sole/default
 * branch. Used to stamp branch_id on records created during this session.
 */
if (!function_exists('courier_get_session_branch_id')) {
    function courier_get_session_branch_id()
    {
        $CI = &get_instance();
        $session_branch = (int) $CI->session->userdata('courier_active_branch_id');
        if ($session_branch > 0) {
            return $session_branch;
        }

        return courier_get_default_staff_branch_id();
    }
}

/**
 * The org-wide fallback branch (flagged is_default=1 in tbl_courier_branches)
 * used to route orders/shipments when no branch could otherwise be resolved.
 *
 * @param bool $prefer_international When true (an order already tagged
 *   "Salibay Global"/"Mixed" but with no route tag/SKU mapping match — see
 *   Shopify_connector::create_courier_shipment()), an active international
 *   branch is preferred over the plain org-wide default. Without this, such
 *   an order silently fell back to the local Head Office branch, so every
 *   document (waybill/invoice/manifest) printed Head Office as the sender
 *   even though the order was already known to be sourced from abroad.
 */
if (!function_exists('courier_get_fallback_branch_id')) {
    function courier_get_fallback_branch_id($prefer_international = false)
    {
        $CI = &get_instance();

        if ($prefer_international) {
            $international = $CI->db->where('branch_type', 'international')
                ->where('is_active', 1)
                ->order_by('is_default', 'DESC')
                ->order_by('id', 'ASC')
                ->limit(1)
                ->get(db_prefix() . '_courier_branches')
                ->row();
            if ($international) {
                return (int) $international->id;
            }
        }

        $row = $CI->db->where('is_default', 1)
            ->where('is_active', 1)
            ->limit(1)
            ->get(db_prefix() . '_courier_branches')
            ->row();

        return $row ? (int) $row->id : null;
    }
}

/**
 * Maps a "GSC-AE-DXB" style route tag to a Go Shipping branch, via the
 * staff-configured tbl_courier_route_branch_map (Fulfilment settings ->
 * Route Map). Shared by both the Shopify webhook auto-create path and the
 * manual "Create Shipment" button so branch resolution can't drift between
 * the two — a route tag, when present, always wins over the SKU-based guess.
 *
 * Route tags are "GSC-{country}-{port}" (e.g. GSC-US-ORD, GSC-US-EWR) — the
 * sourcing app can introduce a brand-new port code for an already-mapped
 * country at any time (a US order via Newark instead of Chicago, say), and
 * staff shouldn't have to notice and add a settings row before it routes
 * correctly. So when there's no exact match, fall back to any other mapped
 * tag sharing the same "GSC-{country}-" prefix and reuse its branch.
 */
if (!function_exists('courier_resolve_branch_from_route_tag')) {
    function courier_resolve_branch_from_route_tag($route_tag)
    {
        if (empty($route_tag)) {
            return null;
        }

        $CI = &get_instance();
        $table = db_prefix() . 'courier_route_branch_map';
        if (!$CI->db->table_exists($table)) {
            return null;
        }

        $map = $CI->db->where('route_tag', $route_tag)->get($table)->row();
        if ($map) {
            return (int) $map->branch_id;
        }

        if (preg_match('/^(GSC-[A-Za-z]{2}-)/', $route_tag, $m)) {
            $sibling = $CI->db->like('route_tag', $m[1], 'after')->get($table)->row();
            if ($sibling) {
                return (int) $sibling->branch_id;
            }
        }

        return null;
    }
}

/**
 * Customer-friendly wording for the client portal's journey timeline — the
 * international air-freight stages (10-13) in particular read like internal
 * ops jargon ("Arrived Destination Airport") rather than something a
 * customer waiting on a parcel wants to read, so they get a warmer
 * rewrite here. Everything else (normal domestic statuses, sourcing tags)
 * falls back to its existing description/status_name unchanged, since
 * those are already reasonably customer-appropriate.
 */
if (!function_exists('courier_customer_facing_status_label')) {
    function courier_customer_facing_status_label($status_id, $description, $status_name, $fallback_to_description = true)
    {
        $friendly = [
            10 => 'Your order has arrived at the airport and is being prepared for its flight.',
            11 => 'Your order has been shipped and is currently in the air, on its way to Kenya.',
            12 => 'Your order has landed in Kenya and is clearing customs.',
            13 => "Your order has arrived at our Local warehouse. We'll keep you updated as it heads to you.",
        ];

        if (isset($friendly[(int) $status_id])) {
            return $friendly[(int) $status_id];
        }

        // $fallback_to_description=false means "only the distinct friendly
        // message, or nothing" — used by courier_get_shipment_journey() to
        // tell "no extra message beyond the status title itself" apart from
        // "there IS one", since the title is now shown separately.
        if (!$fallback_to_description) {
            return null;
        }

        return $description ?: ucfirst(str_replace('_', ' ', (string) $status_name));
    }
}

/**
 * Resolves the customer-facing tracking link for a waybill — the tracker
 * page already auto-fills and auto-submits the tracking box when a `?track=`
 * query param is present (see tracking.php's auto-track JS), so this is the
 * one place both customer-notification emails build that link from.
 */
if (!function_exists('courier_customer_tracking_link')) {
    function courier_customer_tracking_link($waybill_number)
    {
        return site_url('courier_goshipping/track') . '?track=' . urlencode($waybill_number);
    }
}

/**
 * Loads a shipment's recipient row and returns [shipment, recipient_name,
 * email] — or null if there's no shipment, no recipient, or no usable email
 * (including the literal 'no-reply@example.com' placeholder stamped when a
 * Salibay order arrived with no customer email at all — never send to that).
 * Shared by both customer-notification senders below.
 */
if (!function_exists('courier_resolve_shipment_recipient_email')) {
    function courier_resolve_shipment_recipient_email($shipment_id)
    {
        $CI = &get_instance();
        $shipment = $CI->db->where('id', (int) $shipment_id)->get(db_prefix() . '_shipments')->row();
        if (!$shipment || empty($shipment->recipient_id)) {
            log_message('error', "Salibay notification email skipped for shipment #{$shipment_id}: shipment or recipient_id missing.");
            return null;
        }

        $recipient = $CI->db->where('id', $shipment->recipient_id)->get(db_prefix() . '_shipment_recipients')->row();
        if (!$recipient || empty($recipient->email) || $recipient->email === 'no-reply@example.com' || !filter_var($recipient->email, FILTER_VALIDATE_EMAIL)) {
            // Not an error — plenty of orders genuinely have no usable
            // email (phone-first checkout) — but log it so a "why didn't
            // my customer get an email" report can be told apart from an
            // actual send failure.
            log_message('error', "Salibay notification email skipped for shipment #{$shipment_id}: no usable recipient email (" . ($recipient->email ?? 'none') . ').');
            return null;
        }

        return [
            'shipment'       => $shipment,
            'recipient_name' => trim(($recipient->first_name ?? '') . ' ' . ($recipient->last_name ?? '')) ?: 'Customer',
            'email'          => $recipient->email,
        ];
    }
}

/**
 * Self-heals the two customer-facing Salibay notification email templates.
 * `courier_goshipping.php::register_email_templates()` already creates
 * these on every `admin_init` (idempotent — see `create_email_template()`),
 * but that only runs when a staff member loads an /admin page. Since a
 * plain-file-copy cron deploy (see CLAUDE.md's deployment pipeline notes)
 * can land a brand-new template slug in between staff logins, and orders
 * arrive via the public Shopify webhook where `admin_init` never fires at
 * all, call this right before actually needing the templates so the very
 * first notification after a deploy can't silently no-op on a missing row.
 */
if (!function_exists('courier_ensure_notification_email_templates')) {
    function courier_ensure_notification_email_templates()
    {
        $recipient_subject = 'Incoming Delivery Notification — Waybill {waybill_number} from {sender_name} | {company_name}';
        $recipient_message = '<div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;color:#333333;line-height:1.6;">
  <div style="background:#0a2a52;padding:24px;text-align:center;border-radius:8px 8px 0 0;">
    <h2 style="color:#ffffff;margin:0;font-size:22px;letter-spacing:0.5px;">📦 Incoming Delivery Notification</h2>
    <p style="color:#cfd8dc;margin:6px 0 0 0;font-size:14px;">A parcel is on its way to you</p>
  </div>
  <div style="padding:24px;border:1px solid #e0e0e0;border-top:none;border-radius:0 0 8px 8px;background:#ffffff;">
    <p style="font-size:16px;margin-top:0;">Dear <strong>{recipient_name}</strong>,</p>
    <p>Good news! A shipment sent by <strong>{sender_name}</strong> has been registered with <strong>{company_name}</strong> and is currently on its way to your destination.</p>
    <table style="width:100%;border-collapse:collapse;font-size:14px;margin:20px 0;border:1px solid #e0e0e0;">
      <tr style="background:#f8f9fa;">
        <td style="padding:10px 14px;border:1px solid #e0e0e0;font-weight:bold;width:38%;">Waybill / Tracking #</td>
        <td style="padding:10px 14px;border:1px solid #e0e0e0;font-size:16px;font-weight:bold;color:#0a2a52;">
          <a href="{tracking_link}" style="color:#0a2a52;text-decoration:none;">{waybill_number}</a>
        </td>
      </tr>
      <tr>
        <td style="padding:10px 14px;border:1px solid #e0e0e0;font-weight:bold;">Sender</td>
        <td style="padding:10px 14px;border:1px solid #e0e0e0;">{sender_name}</td>
      </tr>
      <tr style="background:#f8f9fa;">
        <td style="padding:10px 14px;border:1px solid #e0e0e0;font-weight:bold;">Delivery Destination</td>
        <td style="padding:10px 14px;border:1px solid #e0e0e0;">{destination}</td>
      </tr>
      <tr>
        <td style="padding:10px 14px;border:1px solid #e0e0e0;font-weight:bold;">Shipping Mode</td>
        <td style="padding:10px 14px;border:1px solid #e0e0e0;">{shipping_mode}</td>
      </tr>
      <tr style="background:#f8f9fa;">
        <td style="padding:10px 14px;border:1px solid #e0e0e0;font-weight:bold;">Current Status</td>
        <td style="padding:10px 14px;border:1px solid #e0e0e0;"><span style="display:inline-block;padding:3px 10px;background:#e3f2fd;color:#0d47a1;border-radius:12px;font-weight:bold;font-size:12px;">{status}</span></td>
      </tr>
    </table>
    <div style="background:#e8f4fd;border-left:4px solid #0288d1;padding:14px 18px;margin:22px 0;border-radius:4px;">
      <strong style="color:#01579b;display:block;margin-bottom:4px;">📌 Delivery Instructions:</strong>
      <span style="font-size:13.5px;color:#37474f;">Please ensure someone is available at the delivery address to receive the parcel. A valid identification document may be requested upon handover.</span>
    </div>
    <div style="text-align:center;margin:28px 0 20px;">
      <a href="{tracking_link}" style="display:inline-block;padding:15px 36px;background:#0a2a52;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;font-size:16px;box-shadow:0 3px 10px rgba(10,42,82,0.25);">Track Your Delivery</a>
    </div>
    <p style="text-align:center;font-size:12.5px;color:#78909c;margin:0 0 20px;">Or copy and paste this tracking link into your browser:<br><a href="{tracking_link}" style="color:#0a2a52;word-break:break-all;">{tracking_link}</a></p>
    <hr style="border:none;border-top:1px solid #eeeeee;margin:24px 0;">
    <p style="font-size:13px;color:#616161;margin-bottom:4px;">If you have any questions regarding this incoming delivery, please contact our team.</p>
    <p style="font-size:13px;color:#616161;margin-top:0;">Best regards,<br><strong>{company_name} Delivery Team</strong></p>
  </div>
</div>';

        $sender_subject = 'Shipment Confirmation — Waybill {waybill_number} to {recipient_name} | {company_name}';
        $sender_message = '<div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;color:#333333;line-height:1.6;">
  <div style="background:#1b5e20;padding:24px;text-align:center;border-radius:8px 8px 0 0;">
    <h2 style="color:#ffffff;margin:0;font-size:22px;letter-spacing:0.5px;">✅ Shipment Booking Confirmation</h2>
    <p style="color:#c8e6c9;margin:6px 0 0 0;font-size:14px;">Your package has been booked successfully</p>
  </div>
  <div style="padding:24px;border:1px solid #e0e0e0;border-top:none;border-radius:0 0 8px 8px;background:#ffffff;">
    <p style="font-size:16px;margin-top:0;">Dear <strong>{sender_name}</strong>,</p>
    <p>Thank you for shipping with <strong>{company_name}</strong>! Your shipment for <strong>{recipient_name}</strong> has been successfully booked and is now being processed.</p>
    <table style="width:100%;border-collapse:collapse;font-size:14px;margin:20px 0;border:1px solid #e0e0e0;">
      <tr style="background:#f8f9fa;">
        <td style="padding:10px 14px;border:1px solid #e0e0e0;font-weight:bold;width:38%;">Waybill Number</td>
        <td style="padding:10px 14px;border:1px solid #e0e0e0;font-size:16px;font-weight:bold;color:#2e7d32;">
          <a href="{tracking_link}" style="color:#2e7d32;text-decoration:none;">{waybill_number}</a>
        </td>
      </tr>
      <tr>
        <td style="padding:10px 14px;border:1px solid #e0e0e0;font-weight:bold;">Recipient</td>
        <td style="padding:10px 14px;border:1px solid #e0e0e0;">{recipient_name}</td>
      </tr>
      <tr style="background:#f8f9fa;">
        <td style="padding:10px 14px;border:1px solid #e0e0e0;font-weight:bold;">Destination</td>
        <td style="padding:10px 14px;border:1px solid #e0e0e0;">{destination}</td>
      </tr>
      <tr>
        <td style="padding:10px 14px;border:1px solid #e0e0e0;font-weight:bold;">Shipping Mode</td>
        <td style="padding:10px 14px;border:1px solid #e0e0e0;">{shipping_mode}</td>
      </tr>
      <tr style="background:#f8f9fa;">
        <td style="padding:10px 14px;border:1px solid #e0e0e0;font-weight:bold;">Current Status</td>
        <td style="padding:10px 14px;border:1px solid #e0e0e0;"><span style="display:inline-block;padding:3px 10px;background:#e8f5e9;color:#2e7d32;border-radius:12px;font-weight:bold;font-size:12px;">{status}</span></td>
      </tr>
    </table>
    <div style="background:#f1f8e9;border-left:4px solid #43a047;padding:14px 18px;margin:22px 0;border-radius:4px;">
      <strong style="color:#2e7d32;display:block;margin-bottom:4px;">📄 Waybill Attached:</strong>
      <span style="font-size:13.5px;color:#37474f;">Your official Waybill document (PDF) is attached to this email for your records and proof of consignment.</span>
    </div>
    <div style="text-align:center;margin:28px 0 20px;">
      <a href="{tracking_link}" style="display:inline-block;padding:15px 36px;background:#2e7d32;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;font-size:16px;box-shadow:0 3px 10px rgba(46,125,50,0.25);">Track Shipment Live</a>
    </div>
    <p style="text-align:center;font-size:12.5px;color:#78909c;margin:0 0 20px;">Or copy and paste this tracking link into your browser:<br><a href="{tracking_link}" style="color:#2e7d32;word-break:break-all;">{tracking_link}</a></p>
    <hr style="border:none;border-top:1px solid #eeeeee;margin:24px 0;">
    <p style="font-size:13px;color:#616161;margin-bottom:4px;">We will notify you again once your package has been delivered.</p>
    <p style="font-size:13px;color:#616161;margin-top:0;">Thank you for your business,<br><strong>{company_name} Customer Care</strong></p>
  </div>
</div>';

        create_email_template(
            $recipient_subject,
            $recipient_message,
            'courier',
            'Courier Waybill Notification to Customer',
            'courier_waybill_to_customer'
        );

        create_email_template(
            'Your order is on its way — Waybill {waybill_number} - {company_name}',
            '<p>Dear {recipient_name},</p>
<p>Great news — your order has been picked up by {company_name} and a waybill has been created for it.</p>
<p><strong>Waybill Number:</strong> {waybill_number}</p>
<p><a href="{tracking_link}" style="display:inline-block;padding:12px 20px;background:#0a2a52;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Track Your Order</a></p>
<p>You can also copy and paste this link into your browser — it will automatically load your tracking details:<br>{tracking_link}</p>
<p>We\'ll keep you updated as your order progresses.</p>
<p>Best regards,<br>{company_name}</p>',
            'courier',
            'Salibay: Order Shipment Created',
            'salibay_order_shipment_created'
        );

        create_email_template(
            'Your order is on its way for delivery — Waybill {waybill_number} - {company_name}',
            '<p>Dear {recipient_name},</p>
<p>Your order is now out with our rider and on its way to you.</p>
<p><strong>Waybill Number:</strong> {waybill_number}</p>
<p><a href="{tracking_link}" style="display:inline-block;padding:12px 20px;background:#0a2a52;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Track Your Order</a></p>
<p>You can also copy and paste this link into your browser — it will automatically load your tracking details:<br>{tracking_link}</p>
<p>Best regards,<br>{company_name}</p>',
            'courier',
            'Salibay: Shipment In Transit',
            'salibay_shipment_in_transit'
        );

        create_email_template(
            $sender_subject,
            $sender_message,
            'courier',
            'Courier: Sender Tracking Info',
            'courier_sender_tracking_info'
        );

        create_email_template(
            'Update on your order — {status_title} — Waybill {waybill_number} - {company_name}',
            '<p>Dear {recipient_name},</p>
<p>{status_message}</p>
<p><strong>Waybill Number:</strong> {waybill_number}</p>
<div style="text-align:center;margin:28px 0;">
  <a href="{tracking_link}" style="display:inline-block;padding:18px 44px;background:#0a2a52;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:bold;font-size:18px;">Track Your Shipment</a>
</div>
<p>Or copy and paste this link into your browser:<br>{tracking_link}</p>
<p>Best regards,<br>{company_name}</p>',
            'courier',
            'Salibay: International Leg Status Update',
            'salibay_international_leg_status_update'
        );

        // Self-heal: ensure existing database rows are upgraded to distinct templates
        $CI = &get_instance();
        if (get_option('courier_distinct_email_templates_v2') !== '1') {
            try {
                $templates_table = db_prefix() . 'emailtemplates';
                if ($CI->db->table_exists($templates_table)) {
                    $CI->db->where('slug', 'courier_waybill_to_customer')
                        ->update($templates_table, [
                            'subject' => $recipient_subject,
                            'message' => $recipient_message,
                        ]);

                    $CI->db->where('slug', 'courier_sender_tracking_info')
                        ->update($templates_table, [
                            'subject' => $sender_subject,
                            'message' => $sender_message,
                        ]);

                    update_option('courier_distinct_email_templates_v2', '1');
                }
            } catch (\Throwable $e) {
                log_message('error', 'Failed updating email templates in courier_ensure_notification_email_templates: ' . $e->getMessage());
            }
        }
    }
}

/**
 * Emails the customer their waybill/tracking link the moment a Salibay
 * order's shipment is created (called from both the Shopify webhook
 * auto-create path and the manual "Create Shipment" button — see
 * Shopify_connector::create_courier_shipment() / Fulfilment::create_courier_shipment()).
 * Silently no-ops if there's no usable recipient email — never blocks
 * shipment creation over a notification failing.
 */
if (!function_exists('courier_send_shipment_created_email')) {
    function courier_send_shipment_created_email($shipment_id)
    {
        $resolved = courier_resolve_shipment_recipient_email($shipment_id);
        if (!$resolved) {
            return false;
        }

        $waybill = $resolved['shipment']->waybill_number ?: $resolved['shipment']->tracking_id;

        try {
            courier_ensure_notification_email_templates();
            $sent = mail_template('Salibay_order_shipment_created', 'courier_goshipping', $resolved['email'], [
                '{recipient_name}' => $resolved['recipient_name'],
                '{waybill_number}' => $waybill,
                '{tracking_link}'  => courier_customer_tracking_link($waybill),
                '{company_name}'   => get_option('companyname'),
            ])->send();
            if (!$sent) {
                log_message('error', "Salibay shipment-created email to {$resolved['email']} for shipment #{$shipment_id} was not sent (see activity log for the reason — missing/inactive template or SMTP failure).");
            }
            return (bool) $sent;
        } catch (\Throwable $e) {
            log_message('error', 'Salibay shipment-created email crashed: ' . $e->getMessage());
            return false;
        }
    }
}

/**
 * Emails the customer once their parcel's domestic last-mile leg actually
 * starts moving — called when a rider hits "Start Delivery" in the Rider
 * PWA (Rider_api::delivery_start(), status_id -> 5/in_transit). Same
 * silent-no-op-on-failure contract as courier_send_shipment_created_email().
 */
if (!function_exists('courier_send_shipment_in_transit_email')) {
    function courier_send_shipment_in_transit_email($shipment_id)
    {
        // Only for Salibay-sourced shipments — this fires from the Rider
        // app's "Start Delivery" action, which a rider can tap on ANY
        // assigned shipment, general Go Shipping freight included. A plain
        // freight shipment has no linked shopify_orders row, so it's
        // silently skipped rather than getting a Salibay-branded email.
        $CI = &get_instance();
        if (!$CI->db->table_exists(db_prefix() . 'shopify_orders')
            || !$CI->db->where('gs_shipment_id', (int) $shipment_id)->get(db_prefix() . 'shopify_orders')->row()
        ) {
            return false;
        }

        $resolved = courier_resolve_shipment_recipient_email($shipment_id);
        if (!$resolved) {
            return false;
        }

        $waybill = $resolved['shipment']->waybill_number ?: $resolved['shipment']->tracking_id;

        try {
            courier_ensure_notification_email_templates();
            $sent = mail_template('Salibay_shipment_in_transit', 'courier_goshipping', $resolved['email'], [
                '{recipient_name}' => $resolved['recipient_name'],
                '{waybill_number}' => $waybill,
                '{tracking_link}'  => courier_customer_tracking_link($waybill),
                '{company_name}'   => get_option('companyname'),
            ])->send();
            if (!$sent) {
                log_message('error', "Salibay shipment-in-transit email to {$resolved['email']} for shipment #{$shipment_id} was not sent (see activity log for the reason — missing/inactive template or SMTP failure).");
            }
            return (bool) $sent;
        } catch (\Throwable $e) {
            log_message('error', 'Salibay shipment-in-transit email crashed: ' . $e->getMessage());
            return false;
        }
    }
}

/**
 * Emails the customer at each stage of the international air-freight leg
 * (international_status_id 10-13 — At Origin Airport, In Transit (Air),
 * Arrived Destination Airport, Arrived Go Shipping Warehouse). International
 * tracking only ever exists on a Salibay-sourced shipment (see the module's
 * own CLAUDE.md — international_status_id is NULL for every plain Go
 * Shipping shipment), but this still checks the shopify_orders link
 * explicitly rather than assuming, per the module's guard-every-call-site
 * convention (see courier_send_shipment_in_transit_email()'s own comment on
 * why relying on the caller alone isn't good enough).
 *
 * Called from every place international_status_id gets set: the initial
 * activation to 10 (Shopify_connector::activate_international_air_freight_leg(),
 * both create_courier_shipment() fallbacks, and
 * Fulfilment::self_heal_stuck_international_orders()), and staff progressing
 * the leg through Shipments::update_international_status() (11-13, or a
 * manual re-send of 10).
 */
if (!function_exists('courier_send_international_leg_status_email')) {
    function courier_send_international_leg_status_email($shipment_id, $international_status_id)
    {
        $international_status_id = (int) $international_status_id;

        $titles = [
            10 => 'At Origin Airport',
            11 => 'In Transit (Air)',
            12 => 'Arrived Destination Airport',
            13 => 'Arrived Go Shipping Warehouse',
        ];
        if (!isset($titles[$international_status_id])) {
            return false;
        }

        $CI = &get_instance();
        if (!$CI->db->table_exists(db_prefix() . 'shopify_orders')
            || !$CI->db->where('gs_shipment_id', (int) $shipment_id)->get(db_prefix() . 'shopify_orders')->row()
        ) {
            return false;
        }

        $resolved = courier_resolve_shipment_recipient_email($shipment_id);
        if (!$resolved) {
            return false;
        }

        $waybill = $resolved['shipment']->waybill_number ?: $resolved['shipment']->tracking_id;

        try {
            courier_ensure_notification_email_templates();
            $sent = mail_template('Salibay_international_leg_status_update', 'courier_goshipping', $resolved['email'], [
                '{recipient_name}' => $resolved['recipient_name'],
                '{status_title}'   => $titles[$international_status_id],
                '{status_message}' => courier_customer_facing_status_label($international_status_id, $titles[$international_status_id], $titles[$international_status_id]),
                '{waybill_number}' => $waybill,
                '{tracking_link}'  => courier_customer_tracking_link($waybill),
                '{company_name}'   => get_option('companyname'),
            ])->send();
            if (!$sent) {
                log_message('error', "Salibay international-leg-status email to {$resolved['email']} for shipment #{$shipment_id} (status {$international_status_id}) was not sent (see activity log for the reason — missing/inactive template or SMTP failure).");
            }
            return (bool) $sent;
        } catch (\Throwable $e) {
            log_message('error', 'Salibay international-leg-status email crashed: ' . $e->getMessage());
            return false;
        }
    }
}

/**
 * Sends the actual formatted waybill — the same email behind the manual
 * "Send Waybill by Email" button on the waybill page (see
 * Shipments::send_waybill_email()) — to a shipment's recipient. Fired
 * automatically the instant ANY shipment is created, general Go Shipping
 * freight or Salibay-sourced alike, per explicit instruction that a Salibay
 * order gets exactly the same "waybill created -> emailed instantly"
 * treatment as a manually created shipment, not a separate/lesser
 * notification. Silently no-ops (never throws) on any missing data so a
 * notification failure can never block shipment creation.
 */
if (!function_exists('courier_send_shipment_waybill_email')) {
    function courier_send_shipment_waybill_email($shipment_id, $custom_email = null)
    {
        try {
            $CI = &get_instance();
            $CI->load->model('courier_goshipping/Shipment_model');

            $details = $CI->Shipment_model->get_shipment_details((int) $shipment_id);
            if (empty($details)) {
                return false;
            }

            $shipment  = $details['shipment'];
            $recipient = $details['recipient'];
            $sender    = $details['sender'];

            $to_email = null;
            if (!empty($recipient)) {
                $to_email = $recipient->email ?? $recipient->recipient_contact_person_email ?? null;
            }
            if ($custom_email && filter_var($custom_email, FILTER_VALIDATE_EMAIL)) {
                $to_email = $custom_email;
            }
            if (empty($to_email) || $to_email === 'no-reply@example.com' || !filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
                return false;
            }

            $invoice_info = courier_get_invoice_info($shipment->branch_id ?? null);
            $company_name = $invoice_info['name'] ?: 'Courier';
            $waybill_raw  = $shipment->waybill_number ?: $shipment->tracking_id;
            $waybill      = htmlspecialchars($waybill_raw);
            $status_name  = htmlspecialchars($shipment->status_name ?? 'Processing');
            $tracking_url = courier_customer_tracking_link($waybill_raw);

            $sender_name = 'N/A';
            if (!empty($sender)) {
                if (!empty($sender->first_name)) {
                    $sender_name = htmlspecialchars(trim($sender->first_name . ' ' . ($sender->last_name ?? '')));
                } elseif (!empty($sender->company_name)) {
                    $sender_name = htmlspecialchars($sender->company_name);
                }
            }

            $recip_name = 'N/A';
            if (!empty($recipient)) {
                if (!empty($recipient->first_name)) {
                    $recip_name = htmlspecialchars(trim($recipient->first_name . ' ' . ($recipient->last_name ?? '')));
                } elseif (!empty($recipient->recipient_contact_person_name)) {
                    $recip_name = htmlspecialchars($recipient->recipient_contact_person_name);
                } elseif (!empty($recipient->recipient_company_name)) {
                    $recip_name = htmlspecialchars($recipient->recipient_company_name);
                }
            }

            $dest_parts = [];
            if (!empty($recipient)) {
                if (!empty($recipient->address)) {
                    $dest_parts[] = $recipient->address;
                } elseif (!empty($recipient->recipient_contact_address)) {
                    $dest_parts[] = $recipient->recipient_contact_address;
                }
                if (!empty($recipient->city)) {
                    $dest_parts[] = $recipient->city;
                }
                if (!empty($details['recipient_country']->short_name)) {
                    $dest_parts[] = $details['recipient_country']->short_name;
                }
            }
            $destination = htmlspecialchars(implode(', ', array_filter($dest_parts)) ?: 'Customer Address');

            $mode = ucfirst(str_replace('_', ' ', $shipment->shipping_mode ?? 'road'));

            courier_ensure_notification_email_templates();

            $mail = mail_template('Courier_waybill_to_customer', 'courier_goshipping', $to_email, [
                '{recipient_name}' => $recip_name ?: 'Customer',
                '{sender_name}'    => $sender_name,
                '{waybill_number}' => $waybill,
                '{destination}'    => $destination,
                '{shipping_mode}'  => $mode,
                '{status}'         => $status_name,
                '{company_name}'   => $company_name,
                '{tracking_link}'  => $tracking_url,
            ]);

            // Receiver: waybill + commercial invoice (no courier invoice).
            courier_attach_waybill_pdfs($mail, (int) $shipment_id);

            $sent = $mail->send();

            if (!$sent) {
                log_message('error', "Waybill email to {$to_email} for shipment #{$shipment_id} was not sent.");
            }

            return (bool) $sent;
        } catch (\Throwable $e) {
            log_message('error', 'Waybill email crashed: ' . $e->getMessage());
            return false;
        }
    }
}

/**
 * Shared sender/recipient display-name + address resolution for the PDF
 * generators below — mirrors the exact same individual-vs-company branching
 * used in views/shipments/waybill.php and views/shipments/commercial_invoice.php,
 * so the emailed PDFs read the same as what staff see on-screen.
 */
if (!function_exists('_courier_pdf_party_lines')) {
    function _courier_pdf_party_lines($party, $type, $country)
    {
        $is_individual = $type === 'individual';

        if ($is_individual) {
            $name    = trim(($party->first_name ?? '') . ' ' . ($party->last_name ?? ''));
            $address = trim(($party->address ?? '') . ', ' . str_replace('_', ' ', $party->address_type ?? '') . ' ' . ($party->zipcode ?? ''));
            $phone   = $party->phone_number ?? '';
        } else {
            $company_name = $party->company_name ?? $party->recipient_company_name ?? '';
            $contact_name = $party->contact_person_name ?? $party->recipient_contact_person_name ?? '';
            $name    = trim('Company: ' . $company_name . ($contact_name ? ' (' . $contact_name . ')' : ''));
            $address = trim(($party->contact_address ?? $party->recipient_contact_address ?? '') . ', '
                . str_replace('_', ' ', $party->contact_address_type ?? $party->recipient_contact_address_type ?? '') . ' '
                . ($party->contact_zipcode ?? $party->recipient_contact_zipcode ?? ''));
            $phone   = $party->contact_person_phone_number ?? $party->recipient_contact_person_phone_number ?? '';
        }

        if (!empty($country->short_name)) {
            $address .= ', ' . $country->short_name;
        }

        return ['name' => $name ?: 'N/A', 'address' => trim($address, ', '), 'phone' => $phone ?: 'N/A'];
    }
}

/**
 * Renders a document as ONE full A4 page that mirrors the on-screen
 * waybill.php / commercial_invoice.php "card" (#waybill-section): a rounded
 * bordered container that fills the sheet, header + tables inside it, and
 * (optionally) the logo watermarked behind the content.
 *
 * TCPDF can't run the real pages' flexbox/CSS, so the card frame is drawn
 * natively (RoundedRect) at a fixed full-page size and the two HTML halves
 * are flowed into it:
 *   $top_html     - flows from the top of the card
 *   $bottom_html  - pinned to the bottom edge of the card (the waybill's
 *                   Terms & Conditions + footer), so the sheet is always
 *                   filled top-to-bottom like the printed original, however
 *                   few package rows there are. If the top half is so long
 *                   that both can't fit, the bottom half simply follows it.
 *
 * $opts: border_color [r,g,b], accent_color [r,g,b]|null (thicker top edge,
 *        waybill only), watermark_path string|null.
 */
if (!function_exists('_courier_render_card_pdf')) {
    function _courier_render_card_pdf($top_html, $bottom_html, $title, array $opts = [])
    {
        if (!class_exists('TCPDF', false)) {
            require_once(APPPATH . 'vendor/tecnickcom/tcpdf/tcpdf.php');
        }

        $cx = 8; $cy = 8; $cw = 194; $ch = 281;   // card = A4 minus an 8mm sheet margin
        $pad_x = 7; $pad_top = 7; $pad_bottom = 7;
        $border = $opts['border_color'] ?? [51, 51, 51];
        $accent = $opts['accent_color'] ?? null;
        $wm     = $opts['watermark_path'] ?? null;

        $build = function () use ($title, $cx, $cy, $cw, $pad_x, $pad_top, $pad_bottom) {
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('Go Shipping Cargo');
            $pdf->SetAuthor(get_option('companyname') ?: 'Go Shipping Cargo');
            $pdf->SetTitle($title);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins($cx + $pad_x, $cy + $pad_top, 210 - ($cx + $cw) + $pad_x);
            $pdf->SetAutoPageBreak(true, $cy + $pad_bottom);
            $pdf->SetFont('helvetica', '', 9);
            return $pdf;
        };

        // Measure both halves so the bottom one can be pinned to the card's foot.
        $m = $build();
        $m->AddPage();
        $m->SetY($cy + $pad_top);
        $m->writeHTML($top_html, true, false, true, false, '');
        $top_end = $m->GetY();
        $bottom_h = 0;
        if ($bottom_html !== '') {
            $m->writeHTML($bottom_html, true, false, true, false, '');
            $bottom_h = $m->GetY() - $top_end;
        }

        $pdf = $build();
        $pdf->AddPage();

        // Card frame
        $pdf->SetLineStyle(['width' => 0.5, 'color' => $border]);
        $pdf->RoundedRect($cx, $cy, $cw, $ch, 3, '1111', 'D');
        if ($accent) {
            $pdf->SetLineStyle(['width' => 1.2, 'color' => $accent, 'cap' => 'butt']);
            $pdf->Line($cx + 3, $cy, $cx + $cw - 3, $cy);
        }

        // Watermark: ~60% of the card width, centred in the card (matches
        // waybill.css .watermark), drawn before the text so it sits behind it.
        if ($wm && file_exists($wm)) {
            $size = @getimagesize($wm);
            $ratio = ($size && !empty($size[0])) ? $size[1] / $size[0] : 1;
            $wm_w = $cw * 0.6;
            $wm_h = min($wm_w * $ratio, $ch * 0.6);
            $wm_w = $wm_h / $ratio;
            $pdf->SetAlpha(0.07);
            $pdf->Image($wm, $cx + ($cw - $wm_w) / 2, $cy + ($ch - $wm_h) / 2, $wm_w, $wm_h, '', '', '', false, 300, '', false, false, 0);
            $pdf->SetAlpha(1);
        }

        $pdf->SetY($cy + $pad_top);
        $pdf->writeHTML($top_html, true, false, true, false, '');
        if ($bottom_html !== '') {
            $pin_y = $cy + $ch - $pad_bottom - $bottom_h - 1;
            if ($pin_y > $pdf->GetY()) {
                $pdf->SetY($pin_y);
            }
            $pdf->writeHTML($bottom_html, true, false, true, false, '');
        }

        return $pdf->Output($title . '.pdf', 'S');
    }
}

/**
 * Local filesystem path to the company logo (dark variant preferred, same
 * fallback order as waybill.php/commercial_invoice.php), or null if none is
 * set/the file is missing. A local path — not base_url() — is used for the
 * TCPDF <img> tags below so embedding never depends on an outbound HTTP
 * fetch back to this same server.
 */
if (!function_exists('_courier_pdf_logo_path')) {
    function _courier_pdf_logo_path()
    {
        $file = get_option('company_logo_dark') ?: get_option('company_logo');
        if (empty($file)) {
            return null;
        }
        $path = FCPATH . 'uploads/company/' . $file;
        return file_exists($path) ? $path : null;
    }
}

/**
 * Local filesystem path to a CODE_128 barcode PNG for $code, generating and
 * caching it under the same modules/courier_goshipping/assets/barcodes/
 * directory the admin waybill page's generate_barcode() uses — so a shipment
 * viewed in-admin and one only ever emailed share the same cached file
 * instead of generating it twice. Registers the Picqer barcode library's own
 * autoloader (copied from Shipments.php::barcodeAutoloader) since these
 * PDF generators can be called from contexts — Fulfilment.php,
 * Shopify_connector.php, the "waybill created" auto-email path — that never
 * instantiate Shipments and so never register it themselves. Returns null
 * (never throws) if the code can't be generated.
 */
if (!function_exists('_courier_pdf_barcode_path')) {
    function _courier_pdf_barcode_path($code)
    {
        if (empty($code)) {
            return null;
        }
        try {
            if (!class_exists('Picqer\\Barcode\\BarcodeGeneratorPNG', false)) {
                spl_autoload_register(function ($class) {
                    $base_dir = FCPATH . 'modules/courier_goshipping/libraries/php-barcode-generator-main/src/';
                    $file = $base_dir . str_replace(['Picqer\\Barcode\\', '\\'], ['', '/'], $class) . '.php';
                    if (file_exists($file)) {
                        require_once $file;
                    }
                });
            }
            if (!class_exists('Picqer\\Barcode\\BarcodeGeneratorPNG')) {
                return null;
            }

            $directory = FCPATH . 'modules/courier_goshipping/assets/barcodes/';
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            $safe_code = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $code);
            $file_path = $directory . $safe_code . '.png';

            if (!file_exists($file_path)) {
                $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                file_put_contents($file_path, $generator->getBarcode($code, $generator::TYPE_CODE_128));
            }

            return $file_path;
        } catch (\Throwable $e) {
            log_message('error', 'PDF barcode generation crashed: ' . $e->getMessage());
            return null;
        }
    }
}

/**
 * Static Terms & Conditions block for the waybill PDF — same wording as the
 * admin waybill.php view's .terms block, two columns under a heavy rule.
 */
if (!function_exists('_courier_pdf_terms_html')) {
    function _courier_pdf_terms_html()
    {
        return '
            <div style="border-top:2px solid #333;font-size:2pt;">&nbsp;</div>
            <div style="font-size:10pt;font-weight:bold;">Terms and Conditions</div>
            <table cellpadding="2" cellspacing="0" style="width:100%;">
                <tr>
                    <td style="width:50%;font-size:8pt;vertical-align:top;line-height:1.35;">
                        <b>1. General Conditions:</b> Use of our services implies acceptance of these terms and applicable laws.<br>
                        <b>2. Delivery Times:</b> We estimate delivery times but do not guarantee specific dates. Delays may occur.<br>
                        <b>3. Package Restrictions:</b> Ensure package contents comply with laws. Some items may be restricted or prohibited.<br>
                        <b>4. Shipping Charges:</b> Charges are based on weight, dimensions, and destination. Additional fees may apply.<br>
                        <b>5. Claims and Liability:</b> We are not liable for issues after delivery. Claims must be reported within a specified period.
                    </td>
                    <td style="width:50%;font-size:8pt;vertical-align:top;line-height:1.35;">
                        <b>6. Customs and Duties:</b> You are responsible for customs fees and taxes for international shipments.<br>
                        <b>7. Insurance:</b> Optional insurance covers package value up to a limit. Refer to our policy for details.<br>
                        <b>8. Address Accuracy:</b> Ensure correct address details to avoid delays or issues.<br>
                        <b>9. Changes to Terms:</b> Terms may be updated. Review regularly for any changes.<br>
                        <b>10. Contact Information:</b> For questions or concerns, contact our customer service.
                    </td>
                </tr>
            </table>
            <div style="font-size:8pt;">Thank you for using our services. We strive to provide reliable delivery solutions.</div>
        ';
    }
}

/**
 * Builds the waybill A4 PDF from already-resolved shipment details. Pure
 * (no DB/CI access beyond the helpers it is handed) so it can be exercised
 * on its own. $o: logo_path, barcode_path, company, agent_html, date.
 * Mirrors waybill.php's #waybill-section row for row: barcode (top right),
 * header (logo | Waybill Number | date), sender/receiver info table,
 * tracking + level/mode, courier-company row, Package Details, notes,
 * agent, round-trip badge, Terms & Conditions, copyright.
 */
if (!function_exists('_courier_build_waybill_pdf')) {
    function _courier_build_waybill_pdf(array $details, array $o)
    {
        $shipment  = $details['shipment'];
        $sender    = $details['sender'] ?? null;
        $recipient = $details['recipient'] ?? null;

        $sender_lines    = _courier_pdf_party_lines($sender, $details['sender_type'] ?? 'individual', $details['sender_country'] ?? null);
        $recipient_lines = _courier_pdf_party_lines($recipient, $details['recipient_type'] ?? 'individual', $details['recipient_country'] ?? null);
        // Same Kenya-local phone formatting as waybill.php.
        $sender_lines['phone']    = str_replace('+254', '0', $sender_lines['phone']);
        $recipient_lines['phone'] = str_replace('+254', '0', $recipient_lines['phone']);

        $company  = $o['company'];
        $tracking = htmlspecialchars((string) ($shipment->tracking_id ?: $shipment->waybill_number));

        $th = 'style="background-color:#f0f0f0;border:1px solid #333;font-size:9pt;font-weight:bold;"';
        $td = 'style="border:1px solid #333;font-size:9pt;"';
        $ph = 'style="background-color:#f0f0f0;border:1px solid #333;font-size:8pt;font-weight:bold;text-align:center;"';
        $pd = 'style="border:1px solid #333;font-size:8.5pt;text-align:center;"';

        $is_fcl = (int) ($shipment->fcl_shipment ?? 0) === 1;
        $rows = '';
        $n = 1;
        foreach (($details['packages'] ?? []) as $p) {
            if ($is_fcl) {
                $rows .= '<tr><td ' . $pd . ' width="8%">' . $n . '</td>'
                    . '<td ' . $pd . ' width="15%">' . htmlspecialchars((string) ($p->quantity ?? '')) . '</td>'
                    . '<td ' . $pd . ' width="52%">' . htmlspecialchars((string) ($p->description ?? '-')) . '</td>'
                    . '<td ' . $pd . ' width="25%">' . htmlspecialchars((string) ($p->fcl_option ?? '-')) . '</td></tr>';
            } else {
                $rows .= '<tr><td ' . $pd . ' width="5%">' . $n . '</td>'
                    . '<td ' . $pd . ' width="9%">' . htmlspecialchars((string) ($p->quantity ?? '')) . '</td>'
                    . '<td ' . $pd . ' width="10%">' . htmlspecialchars((string) ($p->length ?? '-')) . '</td>'
                    . '<td ' . $pd . ' width="10%">' . htmlspecialchars((string) ($p->width ?? '-')) . '</td>'
                    . '<td ' . $pd . ' width="10%">' . htmlspecialchars((string) ($p->height ?? '-')) . '</td>'
                    . '<td ' . $pd . ' width="18%">' . htmlspecialchars((string) ($p->weight_volume ?? '-')) . '</td>'
                    . '<td ' . $pd . ' width="19%">' . htmlspecialchars((string) ($p->weight ?? '-')) . '</td>'
                    . '<td ' . $pd . ' width="19%">' . htmlspecialchars((string) ($p->chargeable_weight ?? '-')) . '</td></tr>';
            }
            $n++;
        }
        if ($rows === '') {
            $rows = '<tr><td ' . $pd . ' colspan="' . ($is_fcl ? 4 : 8) . '">No package details recorded.</td></tr>';
        }
        $pkg_head = $is_fcl
            ? '<tr><th ' . $ph . ' width="8%">#</th><th ' . $ph . ' width="15%">Quantity</th><th ' . $ph . ' width="52%">Description</th><th ' . $ph . ' width="25%">FCL Option</th></tr>'
            : '<tr><th ' . $ph . ' width="5%">#</th><th ' . $ph . ' width="9%">Quantity</th><th ' . $ph . ' width="10%">Length (cm)</th><th ' . $ph . ' width="10%">Width (cm)</th><th ' . $ph . ' width="10%">Height (cm)</th><th ' . $ph . ' width="18%">Volumetric Weight (kg)</th><th ' . $ph . ' width="19%">Gross Weight (kg)</th><th ' . $ph . ' width="19%">Chargeable Weight (kg)</th></tr>';

        $si = ($details['sender_type'] ?? 'individual') === 'individual';
        $ri = ($details['recipient_type'] ?? 'individual') === 'individual';

        $logo = $o['logo_path']
            ? '<img src="' . $o['logo_path'] . '" height="60" width="60">'
            : '<span style="font-size:14pt;font-weight:bold;">' . htmlspecialchars($company) . '</span>';
        $barcode = $o['barcode_path'] ? '<img src="' . $o['barcode_path'] . '" height="38" width="190">' : '';

        $notes = trim((string) ($shipment->special_instructions ?? ''));
        $notes_html = $notes !== '' ? nl2br(htmlspecialchars($notes)) : '<span style="color:#aaaaaa;">&mdash;</span>';

        $agent_row = $o['agent_html'] !== ''
            ? '<tr><th ' . $th . ' width="22%">Shipped By (Agent)</th><td ' . $td . ' width="78%">' . $o['agent_html'] . '</td></tr>'
            : '';

        $round_trip = '';
        if (!empty($shipment->is_round_trip)) {
            $round_trip = '<div style="font-size:4pt;">&nbsp;</div>'
                . '<table cellpadding="5" cellspacing="0" style="width:100%;"><tr>'
                . '<th style="background-color:#f5f5f5;border:1px solid #333;font-size:9pt;font-weight:bold;">SHIPMENT TYPE &nbsp;<span style="background-color:#28a745;color:#ffffff;font-size:8pt;"> Round Trip </span></th>'
                . '</tr></table>';
        }

        $top = '
            <table cellpadding="0" cellspacing="0" style="width:100%;"><tr><td align="right">' . $barcode . '</td></tr></table>
            <table cellpadding="3" cellspacing="0" style="width:100%;"><tr>
                <td width="25%" valign="middle" style="border-bottom:1px solid #000;">' . $logo . '</td>
                <td width="50%" align="center" valign="middle" style="border-bottom:1px solid #000;font-size:12pt;font-weight:bold;">Waybill Number: ' . $tracking . '</td>
                <td width="25%" align="right" valign="middle" style="border-bottom:1px solid #000;font-size:12pt;font-weight:bold;">' . htmlspecialchars($o['date']) . '</td>
            </tr></table>
            <div style="font-size:8pt;">&nbsp;</div>
            <table cellpadding="6" cellspacing="0" style="width:100%;">
                <tr>
                    <th ' . $th . ' width="17%">' . ($si ? 'Sender Name' : 'Sender') . '</th>
                    <td ' . $td . ' width="33%">' . htmlspecialchars($sender_lines['name']) . '</td>
                    <th ' . $th . ' width="17%">' . ($ri ? 'Receiver Name' : 'Receiver') . '</th>
                    <td ' . $td . ' width="33%">' . htmlspecialchars($recipient_lines['name']) . '</td>
                </tr>
                <tr>
                    <th ' . $th . '>' . ($si ? 'Sender Address' : 'Contact person Address') . '</th>
                    <td ' . $td . '>' . htmlspecialchars($sender_lines['address']) . '</td>
                    <th ' . $th . '>' . ($ri ? 'Recipient Address' : 'Contact person Address') . '</th>
                    <td ' . $td . '>' . htmlspecialchars($recipient_lines['address']) . '</td>
                </tr>
                <tr>
                    <th ' . $th . '>' . ($si ? 'Sender Number' : 'Contact Person Number') . '</th>
                    <td ' . $td . '>' . htmlspecialchars($sender_lines['phone']) . '</td>
                    <th ' . $th . '>' . ($ri ? 'Receiver Number' : 'Contact Person Number') . '</th>
                    <td ' . $td . '>' . htmlspecialchars($recipient_lines['phone']) . '</td>
                </tr>
                <tr>
                    <th ' . $th . '>Tracking Number</th>
                    <td ' . $td . ' colspan="3">' . $tracking . '</td>
                </tr>
                <tr>
                    <th ' . $th . '>Shipping Level</th>
                    <td ' . $td . '>' . htmlspecialchars(strtoupper((string) ($shipment->shipping_category ?? '-'))) . '</td>
                    <th ' . $th . '>Shipping Mode</th>
                    <td ' . $td . '>' . htmlspecialchars((string) ($shipment->shipping_mode ?? '-')) . '</td>
                </tr>
            </table>
            <div style="font-size:10pt;">&nbsp;</div>
            <table cellpadding="6" cellspacing="0" style="width:100%;">
                <tr>
                    <th ' . $th . ' width="22%">' . (!empty($shipment->company_type) ? htmlspecialchars($shipment->company_type) : 'Courier Company') . '</th>
                    <td ' . $td . ' width="78%">' . htmlspecialchars($company) . '</td>
                </tr>
            </table>
            <div style="font-size:5pt;">&nbsp;</div>
            <div style="font-size:11pt;font-weight:bold;">Package Details</div>
            <div style="font-size:2pt;">&nbsp;</div>
            <table cellpadding="5" cellspacing="0" style="width:100%;">' . $pkg_head . $rows . '</table>
            <div style="font-size:4pt;">&nbsp;</div>
            <table cellpadding="6" cellspacing="0" style="width:100%;">
                <tr><th ' . $th . ' width="22%">Shipping Notes</th><td ' . $td . ' width="78%">' . $notes_html . '</td></tr>
                ' . $agent_row . '
            </table>
            ' . $round_trip;

        $bottom = _courier_pdf_terms_html()
            . '<div style="font-size:4pt;">&nbsp;</div>'
            . '<div style="text-align:center;font-size:10pt;">&copy; ' . date('Y') . ' ' . htmlspecialchars($company) . '. All rights reserved.</div>';

        return _courier_render_card_pdf($top, $bottom, 'Waybill-' . ($shipment->waybill_number ?: $shipment->tracking_id), [
            'border_color'   => [13, 71, 161],   // #0d47a1
            'accent_color'   => [198, 40, 40],   // #c62828
            'watermark_path' => $o['logo_path'],
        ]);
    }
}

/**
 * Generates the waybill as a PDF binary string — used by the "waybill
 * created" emails, the manual "Send by Email" button and the "Download PDF"
 * button, so all three produce the identical document. Returns null (never
 * throws) on any missing data so a PDF failure can never block an email.
 */
if (!function_exists('courier_generate_waybill_pdf')) {
    function courier_generate_waybill_pdf($shipment_id)
    {
        try {
            $CI = &get_instance();
            $CI->load->model('courier_goshipping/Shipment_model');

            $details = $CI->Shipment_model->get_shipment_details((int) $shipment_id);
            if (empty($details) || empty($details['shipment'])) {
                return null;
            }
            $shipment = $details['shipment'];

            $invoice_info = courier_get_invoice_info($shipment->branch_id ?? null);

            // Agent line — same lookup as waybill.php's "Shipped By (Agent)" block
            $agent_html = '';
            if (!empty($shipment->staff_id)) {
                $agent_row = $CI->db
                    ->select('CONCAT(st.firstname," ",st.lastname) AS agent_name, a.phone_number AS agent_phone')
                    ->from(db_prefix() . '_agents a')
                    ->join(db_prefix() . 'staff st', 'st.staffid = a.staff_id', 'left')
                    ->where('a.staff_id', $shipment->staff_id)
                    ->get()->row();
                if ($agent_row) {
                    $agent_html = htmlspecialchars($agent_row->agent_name)
                        . (!empty($agent_row->agent_phone) ? ' &mdash; ' . htmlspecialchars($agent_row->agent_phone) : '');
                }
            }

            return _courier_build_waybill_pdf($details, [
                'company'      => $invoice_info['name'] ?: (get_option('companyname') ?: 'Go Shipping Cargo'),
                'logo_path'    => _courier_pdf_logo_path(),
                'barcode_path' => _courier_pdf_barcode_path($shipment->tracking_id ?: $shipment->waybill_number),
                'agent_html'   => $agent_html,
                'date'         => date('F j, Y'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Waybill PDF generation crashed: ' . $e->getMessage());
            return null;
        }
    }
}

/**
 * Builds the commercial invoice A4 PDF. Mirrors commercial_invoice.php:
 * just the "COMMERCIAL INVOICE" title and date in the header (no company
 * address / contact / PIN lines), sender/receiver table, items table with
 * total, and the declaration. $o: currency, date.
 */
if (!function_exists('_courier_build_commercial_invoice_pdf')) {
    function _courier_build_commercial_invoice_pdf(array $details, array $o)
    {
        $shipment  = $details['shipment'];
        $sender    = $details['sender'] ?? null;
        $recipient = $details['recipient'] ?? null;

        // Names/addresses exactly as commercial_invoice.php shows them
        // (company parties show the contact person only; no country in the
        // address — origin/destination get their own row).
        $sender_lines    = _courier_pdf_party_lines($sender, $details['sender_type'] ?? 'individual', null);
        $recipient_lines = _courier_pdf_party_lines($recipient, $details['recipient_type'] ?? 'individual', null);
        $sender_name = ($details['sender_type'] ?? 'individual') === 'individual'
            ? trim(($sender->first_name ?? '') . ' ' . ($sender->last_name ?? ''))
            : (string) ($sender->contact_person_name ?? '');
        $recipient_name = ($details['recipient_type'] ?? 'individual') === 'individual'
            ? trim(($recipient->first_name ?? '') . ' ' . ($recipient->last_name ?? ''))
            : (string) ($recipient->recipient_contact_person_name ?? '');

        $origin      = strtoupper($details['sender_country']->short_name ?? '');
        $destination = strtoupper($details['recipient_country']->short_name ?? '');

        $th = 'style="background-color:#f0f0f0;border:1px solid #333;font-size:10pt;font-weight:bold;"';
        $td = 'style="border:1px solid #333;font-size:10pt;"';
        $tb = 'style="border:1px solid #333;font-size:10pt;font-weight:bold;"';

        $items = !empty($details['commercial_details']) ? $details['commercial_details'] : ($details['packages'] ?? []);
        $rows = '';
        $n = 1;
        $total = 0;
        foreach ($items as $item) {
            $val = $item->declared_value ?? ($item->price ?? '-');
            $rows .= '<tr>'
                . '<td ' . $td . ' width="8%">' . $n . '</td>'
                . '<td ' . $td . ' width="16%">' . htmlspecialchars((string) ($item->quantity ?? 1)) . '</td>'
                . '<td ' . $td . ' width="54%">' . htmlspecialchars((string) ($item->description ?? '')) . '</td>'
                . '<td ' . $td . ' width="22%">' . htmlspecialchars((string) $val) . '</td>'
                . '</tr>';
            $total += is_numeric($val) ? (float) $val : 0;
            $n++;
        }
        if ($rows === '') {
            $rows = '<tr><td ' . $td . ' colspan="4">No commercial value items recorded.</td></tr>';
        }

        $top = '
            <table cellpadding="3" cellspacing="0" style="width:100%;"><tr>
                <td width="25%" style="border-bottom:1px solid #000;">&nbsp;</td>
                <td width="50%" align="center" valign="middle" style="border-bottom:1px solid #000;font-size:15pt;font-weight:bold;">COMMERCIAL INVOICE</td>
                <td width="25%" align="right" valign="middle" style="border-bottom:1px solid #000;font-size:12pt;font-weight:bold;">' . htmlspecialchars($o['date']) . '</td>
            </tr></table>
            <div style="font-size:12pt;">&nbsp;</div>
            <table cellpadding="7" cellspacing="0" style="width:100%;">
                <tr><th ' . $th . ' width="50%">SENDER</th><th ' . $th . ' width="50%">RECEIVER</th></tr>
                <tr><td ' . $tb . '>' . htmlspecialchars(strtoupper($sender_name)) . '</td><td ' . $tb . '>' . htmlspecialchars(strtoupper($recipient_name)) . '</td></tr>
                <tr><td ' . $td . '>' . htmlspecialchars($sender_lines['address']) . '</td><td ' . $td . '>' . htmlspecialchars($recipient_lines['address']) . '</td></tr>
                <tr><td ' . $td . '>TEL : ' . htmlspecialchars($sender_lines['phone']) . '</td><td ' . $td . '>TEL : ' . htmlspecialchars($recipient_lines['phone']) . '</td></tr>
                ' . (($origin !== '' || $destination !== '')
                    ? '<tr><td ' . $tb . '>' . ($origin !== '' ? 'ORIGIN COUNTRY : ' . htmlspecialchars($origin) : '') . '</td>'
                        . '<td ' . $tb . '>' . ($destination !== '' ? 'DESTINATION COUNTRY : ' . htmlspecialchars($destination) : '') . '</td></tr>'
                    : '') . '
            </table>
            <div style="font-size:14pt;">&nbsp;</div>
            <table cellpadding="7" cellspacing="0" style="width:100%;">
                <tr>
                    <th ' . $th . ' width="8%">#</th>
                    <th ' . $th . ' width="16%">QUANTITY</th>
                    <th ' . $th . ' width="54%">DESCRIPTION</th>
                    <th ' . $th . ' width="22%">AMOUNT (' . htmlspecialchars($o['currency']) . ')</th>
                </tr>
                ' . $rows . '
                <tr>
                    <td ' . $td . '></td>
                    <td ' . $tb . '>TOTAL</td>
                    <td ' . $td . '></td>
                    <td ' . $td . '>' . htmlspecialchars((string) $total) . '</td>
                </tr>
            </table>
            <div style="font-size:8pt;">&nbsp;</div>
            <div style="font-size:12pt;font-weight:bold;text-decoration:underline;">DECLARATION</div>
            <div style="font-size:12pt;line-height:1.35;">I declare that this invoice shows the actual value/price of the goods described and that all particulars are true and correct and that the goods are of no commercial value, the value used is only for commercial purposes.</div>
        ';

        return _courier_render_card_pdf($top, '', 'Commercial-Invoice-' . ($shipment->waybill_number ?: $shipment->tracking_id), [
            'border_color' => [51, 51, 51],
        ]);
    }
}

/**
 * Generates the commercial invoice as a PDF binary string. Only ever sent to
 * the SENDER (and offered to staff for download) — never to the receiver.
 * Salibay/Shopify orders get no commercial invoice attachment (see
 * modules/courier_goshipping/CLAUDE.md). Returns null (never throws).
 */
if (!function_exists('courier_generate_commercial_invoice_pdf')) {
    function courier_generate_commercial_invoice_pdf($shipment_id)
    {
        try {
            $CI = &get_instance();
            $CI->load->model('courier_goshipping/Shipment_model');

            $details = $CI->Shipment_model->get_shipment_details((int) $shipment_id);
            if (empty($details) || empty($details['shipment'])) {
                return null;
            }
            $shipment = $details['shipment'];

            $currency = 'USD';
            if (!empty($shipment->invoice_id)) {
                $inv = $CI->db->select('currency')->where('id', $shipment->invoice_id)->get(db_prefix() . 'invoices')->row();
                if ($inv && $inv->currency && ($currency_obj = get_currency($inv->currency))) {
                    $currency = $currency_obj->name;
                }
            } elseif (!empty($shipment->currency_name)) {
                $currency = $shipment->currency_name;
            } elseif (function_exists('get_base_currency') && ($base_currency = get_base_currency())) {
                $currency = $base_currency->name;
            }

            return _courier_build_commercial_invoice_pdf($details, [
                'currency' => $currency,
                'date'     => date('F j, Y'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Commercial invoice PDF generation crashed: ' . $e->getMessage());
            return null;
        }
    }
}

/**
 * Builds the courier (billing) invoice A4 PDF — mirrors courier_invoice.php:
 * header (logo/company | INVOICE + VAT badge | invoice meta), Bill To / From,
 * shipment reference, line items, totals, amount due, payment terms and the
 * Issued By / Received By signature lines. $o: items, info (invoice info
 * array), logo_path, currency, inv_number, date, due, total_paid.
 * SENDER-only document — never attached to the receiver's email.
 */
if (!function_exists('_courier_build_courier_invoice_pdf')) {
    function _courier_build_courier_invoice_pdf(array $details, array $o)
    {
        $s    = $details['shipment'];
        $info = $o['info'];
        $cur  = htmlspecialchars($o['currency']);
        $e    = function ($v) { return htmlspecialchars((string) $v); };

        $items = array_values(array_filter($o['items'], function ($it) {
            return !((float) $it->rate == 0 && strtoupper(trim((string) $it->description)) === 'PACKAGING');
        }));
        $subtotal = 0;
        foreach ($items as $it) {
            $subtotal += (float) $it->qty * (float) $it->rate;
        }
        $vat_on   = !empty($s->vat_applicable);
        $vat_rate = $vat_on ? (float) $s->vat_rate : 0;
        $vat_amt  = $vat_on ? round($subtotal * ($vat_rate / 100), 2) : 0;
        $total    = $subtotal + $vat_amt;
        $paid     = (float) $o['total_paid'];
        $balance  = round(max(0, $total - $paid), 2);

        $sender = $details['sender'] ?? null;
        $si     = ($details['sender_type'] ?? 'individual') === 'individual';
        $bill_name = $si
            ? trim(($sender->first_name ?? '') . ' ' . ($sender->last_name ?? ''))
            : ($sender->company_name ?? '') . ' (' . ($sender->contact_person_name ?? '') . ')';
        $zip_raw   = $si ? ($sender->zipcode ?? '') : ($sender->contact_zipcode ?? '');
        $zip       = preg_match('/^\+\d+$/', trim($zip_raw)) ? '' : trim($zip_raw);
        $addr_b    = $si ? trim($sender->address ?? '') : trim($sender->contact_address ?? '');
        $bill_addr = $addr_b . ($zip ? ', ' . $zip : '');
        $snd_country = $details['sender_country']->short_name ?? '';
        $bill_phone  = $si ? ($sender->phone_number ?? '') : ($sender->contact_person_phone_number ?? '');
        $bill_email  = $si ? ($sender->email ?? '') : ($sender->contact_person_email ?? '');
        $bill_pin    = $sender->kra_pin ?? '';

        $recipient   = $details['recipient'] ?? null;
        $ri          = ($details['recipient_type'] ?? 'individual') === 'individual';
        $rec_addr    = trim($ri ? ($recipient->address ?? '') : ($recipient->recipient_contact_address ?? ''));
        $rec_country = $details['recipient_country']->short_name ?? '';

        $th = 'style="background-color:#f0f0f0;border:1px solid #333;font-size:9pt;font-weight:bold;"';
        $td = 'style="border:1px solid #333;font-size:9pt;"';

        $rows = '';
        $n = 1;
        foreach ($items as $it) {
            $ld = trim(strip_tags(html_entity_decode(str_replace(['&nbsp;', '&NBSP;'], ' ', (string) ($it->long_description ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            $rows .= '<tr>'
                . '<td ' . $td . ' width="6%">' . $n++ . '</td>'
                . '<td ' . $td . ' width="44%">' . $e($it->description) . ($ld !== '' ? '<br><span style="font-size:7.5pt;color:#777777;">' . nl2br($e($ld)) . '</span>' : '') . '</td>'
                . '<td ' . $td . ' width="14%" align="right">' . number_format((float) $it->qty, 2) . '</td>'
                . '<td ' . $td . ' width="16%" align="right">' . number_format((float) $it->rate, 2) . '</td>'
                . '<td ' . $td . ' width="20%" align="right"><b>' . number_format((float) $it->qty * (float) $it->rate, 2) . '</b></td>'
                . '</tr>';
        }
        if ($rows === '') {
            $rows = '<tr><td ' . $td . ' colspan="5" align="center">Shipping service &mdash; ' . $e($s->shipping_mode ?? '') . '</td></tr>';
        }

        $from = '';
        foreach (['name' => '<b>%s</b>', 'address' => '%s', 'phone' => 'Tel: %s', 'email' => '%s', 'website' => '%s', 'pin' => 'PIN: %s'] as $k => $fmt) {
            if (!empty($info[$k])) {
                $from .= sprintf($fmt, nl2br($e(trim($info[$k])))) . '<br>';
            }
        }
        $bill = '<b>' . $e($bill_name) . '</b><br>'
            . ($bill_addr !== '' ? $e($bill_addr) . ($snd_country ? ', ' . $e($snd_country) : '') . '<br>' : '')
            . ($bill_phone ? 'Tel: ' . $e($bill_phone) . '<br>' : '')
            . ($bill_email ? $e($bill_email) . '<br>' : '')
            . ($bill_pin ? '<b>KRA PIN:</b> ' . $e($bill_pin) . '<br>' : '');

        $status = $paid > 0 ? ($balance <= 0 ? 'PAID' : 'PARTIALLY PAID') : '';
        $logo   = $o['logo_path'] ? '<img src="' . $o['logo_path'] . '" height="50" width="50"><br>' : '';
        $terms  = trim((string) ($s->payment_terms ?? ''));
        $name   = $info['name'] ?: 'Our Company';

        $top = '
            <table cellpadding="3" cellspacing="0" style="width:100%;"><tr>
                <td width="32%" valign="middle">' . $logo . '<b style="font-size:11pt;">' . $e($name) . '</b></td>
                <td width="30%" align="center" valign="middle"><span style="font-size:20pt;font-weight:bold;">INVOICE</span><br>
                    <span style="font-size:8pt;font-weight:bold;">' . ($vat_on ? 'VAT INCLUSIVE (' . $vat_rate . '%)' : 'VAT EXEMPT') . '</span></td>
                <td width="38%" align="right" valign="middle" style="font-size:9pt;line-height:1.5;">
                    <b>Invoice #:</b> ' . $e($o['inv_number']) . '<br>
                    <b>Invoice Date:</b> ' . $e($o['date']) . '<br>
                    <b>Due Date:</b> ' . $e($o['due']) . '<br>
                    <b>AWB / Waybill:</b> ' . $e($s->waybill_number)
                    . ($status ? '<br><b style="color:#c62828;">' . $status . '</b>' : '') . '</td>
            </tr></table>
            <div style="border-top:1px solid #000;font-size:2pt;">&nbsp;</div>
            <table cellpadding="7" cellspacing="0" style="width:100%;"><tr>
                <td width="50%" style="border:1px solid #333;font-size:9pt;line-height:1.5;"><span style="font-size:8pt;font-weight:bold;color:#555555;">BILL TO</span><br>' . $bill . '</td>
                <td width="50%" style="border:1px solid #333;font-size:9pt;line-height:1.5;"><span style="font-size:8pt;font-weight:bold;color:#555555;">FROM</span><br>' . $from . '</td>
            </tr></table>
            <div style="font-size:8pt;">&nbsp;</div>
            <table cellpadding="5" cellspacing="0" style="width:100%;">
                <tr><th ' . $th . ' colspan="4">Shipment Reference</th></tr>
                <tr><td ' . $td . ' width="18%"><b>Shipping Mode</b></td><td ' . $td . ' width="32%">' . $e(strtoupper((string) ($s->shipping_mode ?? ''))) . '</td>
                    <td ' . $td . ' width="18%"><b>Category</b></td><td ' . $td . ' width="32%">' . $e(strtoupper((string) ($s->shipping_category ?? ''))) . '</td></tr>
                <tr><td ' . $td . '><b>Origin</b></td><td ' . $td . '>' . $e($addr_b . ($snd_country ? ', ' . $snd_country : '')) . '</td>
                    <td ' . $td . '><b>Destination</b></td><td ' . $td . '>' . $e($rec_addr . ($rec_country ? ', ' . $rec_country : '')) . '</td></tr>
            </table>
            <div style="font-size:8pt;">&nbsp;</div>
            <table cellpadding="6" cellspacing="0" style="width:100%;">
                <tr><th ' . $th . ' width="6%">#</th><th ' . $th . ' width="44%">Description</th><th ' . $th . ' width="14%" align="right">Qty / Wt</th>
                    <th ' . $th . ' width="16%" align="right">Unit Rate</th><th ' . $th . ' width="20%" align="right">Amount (' . $cur . ')</th></tr>
                ' . $rows . '
            </table>
            <div style="font-size:6pt;">&nbsp;</div>
            <table cellpadding="0" cellspacing="0" style="width:100%;"><tr><td width="55%"></td><td width="45%">
                <table cellpadding="5" cellspacing="0" style="width:100%;">
                    <tr><td ' . $td . '>Subtotal</td><td ' . $td . ' align="right">' . number_format($subtotal, 2) . '</td></tr>
                    <tr><td ' . $td . '>' . ($vat_on ? 'VAT @ ' . $vat_rate . '%' : 'VAT') . '</td><td ' . $td . ' align="right">' . ($vat_on ? number_format($vat_amt, 2) : 'Exempt') . '</td></tr>
                    ' . ($paid > 0 ? '<tr><td ' . $td . '>Paid</td><td ' . $td . ' align="right">' . number_format($paid, 2) . '</td></tr>' : '') . '
                    <tr><td style="border:1px solid #333;font-size:10pt;font-weight:bold;background-color:#f0f0f0;">' . ($paid > 0 ? 'BALANCE DUE' : 'AMOUNT DUE') . '</td>
                        <td style="border:1px solid #333;font-size:10pt;font-weight:bold;background-color:#f0f0f0;" align="right">' . $cur . ' ' . number_format($paid > 0 ? $balance : $total, 2) . '</td></tr>
                </table></td></tr></table>
            <div style="font-size:8pt;">&nbsp;</div>
            <table cellpadding="8" cellspacing="0" style="width:100%;"><tr><td align="center" style="border:1px solid #333;">
                <span style="font-size:9pt;color:#555555;">Total Amount Due by ' . $e($o['due']) . '</span><br>
                <b style="font-size:18pt;">' . $cur . ' ' . number_format($total, 2) . '</b>
                <span style="font-size:8pt;color:#888888;"> ' . ($vat_on ? '(includes VAT ' . $vat_rate . '%)' : '(VAT exempt)') . '</span>
            </td></tr></table>'
            . ($terms !== '' ? '<div style="font-size:6pt;">&nbsp;</div><table cellpadding="7" cellspacing="0" style="width:100%;"><tr><td style="background-color:#f5f5f5;font-size:8.5pt;"><b>Payment Terms:</b> ' . nl2br($e($terms)) . '</td></tr></table>' : '');

        $bottom = '
            <table cellpadding="4" cellspacing="0" style="width:100%;"><tr>
                <td width="46%" align="center" style="border-top:1px solid #333;font-size:9pt;"><b>Issued By</b><br><span style="font-size:7.5pt;color:#999999;">Authorized Signature &amp; Stamp</span></td>
                <td width="8%"></td>
                <td width="46%" align="center" style="border-top:1px solid #333;font-size:9pt;"><b>Received By</b><br><span style="font-size:7.5pt;color:#999999;">Name, Signature &amp; Date</span></td>
            </tr></table>
            <div style="text-align:center;font-size:8pt;color:#999999;">&copy; ' . date('Y') . ' ' . $e($name) . ' &mdash; All rights reserved.</div>';

        return _courier_render_card_pdf($top, $bottom, 'Invoice-' . ($s->waybill_number ?: $s->tracking_id), [
            'border_color'   => [51, 51, 51],
            'watermark_path' => $o['logo_path'],
        ]);
    }
}

/**
 * Generates the courier invoice PDF (null if the shipment has no invoice or
 * on any failure — never throws). Sender-only attachment.
 */
if (!function_exists('courier_generate_courier_invoice_pdf')) {
    function courier_generate_courier_invoice_pdf($shipment_id)
    {
        try {
            $CI = &get_instance();
            $CI->load->model('courier_goshipping/Shipment_model');
            $details = $CI->Shipment_model->get_shipment_details((int) $shipment_id);
            if (empty($details) || empty($details['shipment']) || empty($details['shipment']->invoice_id)) {
                return null;
            }
            $s      = $details['shipment'];
            $inv_id = (int) $s->invoice_id;

            $items = $CI->db->get_where(db_prefix() . 'itemable', ['rel_id' => $inv_id, 'rel_type' => 'invoice'])->result();
            $inv   = $CI->db->get_where(db_prefix() . 'invoices', ['id' => $inv_id])->row();

            $base_date = ($inv && !empty($inv->date) && $inv->date !== '0000-00-00') ? $inv->date : ($s->created_at ?? date('Y-m-d'));
            $due_date  = ($inv && !empty($inv->duedate) && $inv->duedate !== '0000-00-00') ? $inv->duedate : date('Y-m-d', strtotime($base_date . ' +14 days'));

            $currency = 'USD';
            if ($inv && $inv->currency && ($c = get_currency($inv->currency))) {
                $currency = $c->name;
            }

            $paid_row = $CI->db->select_sum('amount')->where('invoiceid', $inv_id)->get(db_prefix() . 'invoicepaymentrecords')->row();

            return _courier_build_courier_invoice_pdf($details, [
                'items'      => $items,
                'info'       => courier_get_invoice_info($s->branch_id ?? null),
                'logo_path'  => _courier_pdf_logo_path(),
                'currency'   => $currency,
                'inv_number' => 'INV-' . strtoupper($s->waybill_number),
                'date'       => date('F j, Y', strtotime($base_date)),
                'due'        => date('F j, Y', strtotime($due_date)),
                'total_paid' => (float) ($paid_row->amount ?? 0),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Courier invoice PDF generation crashed: ' . $e->getMessage());
            return null;
        }
    }
}

/**
 * Attaches the shipment PDFs onto a not-yet-sent App_mail_template instance:
 *  - waybill PDF (always)
 *  - commercial invoice PDF (plain non-Salibay shipments) — goes to BOTH the
 *    receiver and the sender
 *  - courier (billing) invoice PDF — only when $include_courier_invoice is
 *    true, i.e. the SENDER's email; the receiver never gets it
 * Salibay/Shopify orders only ever get the waybill (marketplace orders with no
 * commercial-value declaration or billing invoice of our own — see
 * modules/courier_goshipping/CLAUDE.md). Never throws.
 */
if (!function_exists('courier_attach_waybill_pdfs')) {
    function courier_attach_waybill_pdfs($mail, $shipment_id, $include_courier_invoice = false)
    {
        try {
            $waybill_pdf = courier_generate_waybill_pdf($shipment_id);
            if ($waybill_pdf !== null) {
                $mail->add_attachment([
                    'attachment' => $waybill_pdf,
                    'filename'   => 'Waybill-' . $shipment_id . '.pdf',
                    'type'       => 'application/pdf',
                ]);
            }

            $CI = &get_instance();
            $is_salibay = $CI->db->table_exists(db_prefix() . 'shopify_orders')
                && $CI->db->where('gs_shipment_id', (int) $shipment_id)->get(db_prefix() . 'shopify_orders')->row();
            if ($is_salibay) {
                return;
            }

            $commercial_invoice_pdf = courier_generate_commercial_invoice_pdf($shipment_id);
            if ($commercial_invoice_pdf !== null) {
                $mail->add_attachment([
                    'attachment' => $commercial_invoice_pdf,
                    'filename'   => 'Commercial-Invoice-' . $shipment_id . '.pdf',
                    'type'       => 'application/pdf',
                ]);
            }

            if ($include_courier_invoice) {
                $invoice_pdf = courier_generate_courier_invoice_pdf($shipment_id);
                if ($invoice_pdf !== null) {
                    $mail->add_attachment([
                        'attachment' => $invoice_pdf,
                        'filename'   => 'Invoice-' . $shipment_id . '.pdf',
                        'type'       => 'application/pdf',
                    ]);
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'courier_attach_waybill_pdfs crashed for shipment #' . $shipment_id . ': ' . $e->getMessage());
        }
    }
}

/**
 * Loads a shipment's sender's usable email + display name — mirrors
 * courier_resolve_shipment_recipient_email() but for the sender side
 * (individual shipment_senders row, or shipment_companies row when the
 * sender was entered as a company). Returns null if there's no usable email.
 */
if (!function_exists('courier_resolve_shipment_sender_email')) {
    function courier_resolve_shipment_sender_email($shipment_id)
    {
        $CI = &get_instance();
        $shipment = $CI->db->where('id', (int) $shipment_id)->get(db_prefix() . '_shipments')->row();
        if (!$shipment) {
            return null;
        }

        $email = null;
        $name  = 'Customer';

        if (!empty($shipment->sender_id)) {
            $sender = $CI->db->where('id', $shipment->sender_id)->get(db_prefix() . '_shipment_senders')->row();
            if ($sender) {
                $email = $sender->email ?? null;
                $name  = trim(($sender->first_name ?? '') . ' ' . ($sender->last_name ?? '')) ?: $name;
            }
        } elseif (!empty($shipment->company_id)) {
            $company = $CI->db->where('id', $shipment->company_id)->get(db_prefix() . '_shipment_companies')->row();
            if ($company) {
                $email = $company->contact_person_email ?? null;
                $name  = $company->company_name ?? $name;
            }
        }

        if (empty($email) || $email === 'no-reply@example.com' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return ['shipment' => $shipment, 'sender_name' => $name, 'email' => $email];
    }
}

/**
 * Emails the PERSON WHO SENT THE PARCEL (not the recipient) a "here's how to
 * track your shipment" notice, the moment a shipment is created — the
 * recipient-facing waybill email (courier_send_shipment_waybill_email())
 * only ever reaches the recipient, so without this the sender has no way to
 * follow their own parcel's progress. Same silent-no-op contract as the
 * other notification senders in this file.
 */
if (!function_exists('courier_send_sender_tracking_email')) {
    function courier_send_sender_tracking_email($shipment_id)
    {
        try {
            $resolved = courier_resolve_shipment_sender_email($shipment_id);
            if (!$resolved) {
                return false;
            }

            $CI = &get_instance();
            $CI->load->model('courier_goshipping/Shipment_model');
            $details = $CI->Shipment_model->get_shipment_details((int) $shipment_id);

            $shipment  = $details['shipment'] ?? $resolved['shipment'];
            $recipient = $details['recipient'] ?? null;
            $sender    = $details['sender'] ?? null;

            $sender_name = $resolved['sender_name'] ?: 'Customer';
            if (!empty($sender)) {
                if (!empty($sender->first_name)) {
                    $sender_name = htmlspecialchars(trim($sender->first_name . ' ' . ($sender->last_name ?? '')));
                } elseif (!empty($sender->company_name)) {
                    $sender_name = htmlspecialchars($sender->company_name);
                }
            }

            $recip_name = 'Customer';
            if (!empty($recipient)) {
                if (!empty($recipient->first_name)) {
                    $recip_name = htmlspecialchars(trim($recipient->first_name . ' ' . ($recipient->last_name ?? '')));
                } elseif (!empty($recipient->recipient_contact_person_name)) {
                    $recip_name = htmlspecialchars($recipient->recipient_contact_person_name);
                } elseif (!empty($recipient->recipient_company_name)) {
                    $recip_name = htmlspecialchars($recipient->recipient_company_name);
                }
            }

            $dest_parts = [];
            if (!empty($recipient)) {
                if (!empty($recipient->address)) {
                    $dest_parts[] = $recipient->address;
                } elseif (!empty($recipient->recipient_contact_address)) {
                    $dest_parts[] = $recipient->recipient_contact_address;
                }
                if (!empty($recipient->city)) {
                    $dest_parts[] = $recipient->city;
                }
                if (!empty($details['recipient_country']->short_name)) {
                    $dest_parts[] = $details['recipient_country']->short_name;
                }
            }
            $destination = htmlspecialchars(implode(', ', array_filter($dest_parts)) ?: 'Recipient Address');

            $invoice_info = courier_get_invoice_info($shipment->branch_id ?? null);
            $company_name = $invoice_info['name'] ?: (get_option('companyname') ?: 'Courier');
            $waybill_raw  = $shipment->waybill_number ?: $shipment->tracking_id;
            $waybill      = htmlspecialchars($waybill_raw);
            $status_name  = htmlspecialchars($shipment->status_name ?? 'Processing');
            $tracking_url = courier_customer_tracking_link($waybill_raw);
            $mode         = ucfirst(str_replace('_', ' ', $shipment->shipping_mode ?? 'road'));

            courier_ensure_notification_email_templates();

            $mail = mail_template('Courier_sender_tracking_info', 'courier_goshipping', $resolved['email'], [
                '{sender_name}'    => $sender_name,
                '{recipient_name}' => $recip_name,
                '{waybill_number}' => $waybill,
                '{destination}'    => $destination,
                '{shipping_mode}'  => $mode,
                '{status}'         => $status_name,
                '{tracking_link}'  => $tracking_url,
                '{company_name}'   => $company_name,
            ]);

            // Sender: waybill + commercial invoice + courier invoice.
            courier_attach_waybill_pdfs($mail, (int) $shipment_id, true);

            $sent = $mail->send();

            if (!$sent) {
                log_message('error', "Sender tracking-info email to {$resolved['email']} for shipment #{$shipment_id} was not sent.");
            }

            return (bool) $sent;
        } catch (\Throwable $e) {
            log_message('error', 'Sender tracking-info email crashed: ' . $e->getMessage());
            return false;
        }
    }
}

/**
 * Builds the full sourcing-to-doorstep journey for a shipment, for the
 * client portal tracker: the external sourcing pipeline's own progress tags
 * (tbl_courier_sourcing_events, captured by
 * Shopify_connector::record_sourcing_milestone_tags()) merged with Go
 * Shipping's own shipment status history — one waybill throughout, both the
 * domestic status_id track and the independent international_status_id
 * track (see run_db_upgrades_v41()/v43()) log into the same
 * _shipment_status_history table, so this just reads that one shipment's
 * full history and doesn't need to resolve any linked/sibling shipment.
 *
 * Returns a flat array of ['title' => string, 'message' => string|null,
 * 'changed_at' => 'Y-m-d H:i:s'] sorted newest-first, ready to hand straight
 * to the tracker view as the single unified journey list (latest update at
 * the top) — title bold, message (when present) a smaller/faded line below
 * it, same two-line layout as the admin waybill's History panel.
 */
if (!function_exists('courier_get_shipment_journey')) {
    function courier_get_shipment_journey($shipment_id)
    {
        $CI = &get_instance();
        $shipments_table = db_prefix() . '_shipments';

        $shipment = $CI->db->where('id', (int) $shipment_id)->get($shipments_table)->row();
        if (!$shipment) {
            return [];
        }

        $leg_ids = [(int) $shipment_id];
        if (!empty($shipment->parent_shipment_id)) {
            $leg_ids[] = (int) $shipment->parent_shipment_id;
        }
        $child = $CI->db->where('parent_shipment_id', (int) $shipment_id)->get($shipments_table)->row();
        if ($child) {
            $leg_ids[] = (int) $child->id;
        }
        $leg_ids = array_values(array_unique($leg_ids));

        $events = [];

        $status_desc_col = $CI->db->field_exists('status_description', db_prefix() . '_shipment_statuses')
            ? 'ss.status_description'
            : 'ss.description';
        $notes_col = $CI->db->field_exists('notes', db_prefix() . '_shipment_status_history')
            ? 'h.notes'
            : 'NULL AS notes';

        $history = $CI->db->select("h.status_id, h.changed_at, {$notes_col}, ss.status_name, {$status_desc_col} AS status_description")
            ->from(db_prefix() . '_shipment_status_history h')
            ->join(db_prefix() . '_shipment_statuses ss', 'ss.id = h.status_id', 'left')
            ->where_in('h.shipment_id', $leg_ids)
            ->get()
            ->result();

        // Who actually signed for the parcel — same _deliveries row the
        // admin waybill's "Proof of Delivery" card reads (Shipments::
        // update_status()/Rider_api::delivery_complete()/Salibay_delivery
        // all write into it when status 8 is reached). Only fetched if this
        // shipment's history actually has a "delivered" row, so a shipment
        // that's still in progress never pays for this query.
        $delivered_status = $CI->db->where('status_name', 'delivered')->get(db_prefix() . '_shipment_statuses')->row();
        $delivered_status_id = $delivered_status ? (int) $delivered_status->id : null;
        $delivery_recipient_name = null;
        if ($delivered_status_id !== null
            && $CI->db->table_exists(db_prefix() . '_deliveries')
            && array_reduce($history, function ($found, $row) use ($delivered_status_id) {
                return $found || (int) $row->status_id === $delivered_status_id;
            }, false)
        ) {
            $delivery = $CI->db->where_in('shipment_id', $leg_ids)->order_by('id', 'desc')->get(db_prefix() . '_deliveries')->row();
            if ($delivery) {
                $delivery_recipient_name = trim(($delivery->first_name ?? '') . ' ' . ($delivery->last_name ?? ''));
            }
        }

        foreach ($history as $row) {
            // Every step shows its backend status title (bold), then — only
            // when there's something more specific to say than the title
            // itself (a per-event note like "Your order has been assigned to
            // Rider {name}...", the international leg's friendly messages,
            // or who signed for a delivered parcel) — a separate, smaller/
            // faded description line below it, same two-line layout as the
            // admin History panel. A step with nothing extra to add
            // (Created, Received, ...) just shows its title, no description.
            $title = $row->status_description ?: ucfirst(str_replace('_', ' ', (string) $row->status_name));
            $message = !empty($row->notes)
                ? $row->notes
                : courier_customer_facing_status_label((int) $row->status_id, null, null, false);

            if ($message === null && $delivered_status_id !== null && (int) $row->status_id === $delivered_status_id && !empty($delivery_recipient_name)) {
                $message = "The order has been received by {$delivery_recipient_name}.";
            }

            if ($message === $title) {
                $message = null;
            }

            $events[] = [
                'title'      => $title,
                'message'    => $message,
                'changed_at' => $row->changed_at,
                'status_id'  => (int) $row->status_id,
            ];
        }

        // update_international_status() silently bumps the domestic status
        // to 3 ("Received") as a side effect of the international leg
        // reaching 13 ("Arrived Go Shipping Warehouse") — same real-world
        // event as the international entry, so showing both is a confusing
        // duplicate. Drop the domestic one when it lands within a couple of
        // minutes of the international-13 event.
        $arrival_ts = null;
        foreach ($events as $event) {
            if ($event['status_id'] === 13) {
                $arrival_ts = strtotime($event['changed_at']);
                break;
            }
        }
        if ($arrival_ts !== null) {
            $events = array_values(array_filter($events, function ($event) use ($arrival_ts) {
                return !($event['status_id'] === 3 && abs(strtotime($event['changed_at']) - $arrival_ts) <= 120);
            }));
        }

        if ($CI->db->table_exists(db_prefix() . 'shopify_orders')) {
            $order = $CI->db->where_in('gs_shipment_id', $leg_ids)->get(db_prefix() . 'shopify_orders')->row();
            if ($order && $CI->db->table_exists(db_prefix() . 'courier_sourcing_events')) {
                $sourcing_events = $CI->db->where('shopify_order_id', $order->id)
                    ->get(db_prefix() . 'courier_sourcing_events')
                    ->result();
                foreach ($sourcing_events as $row) {
                    $events[] = [
                        'title'      => $row->tag,
                        'message'    => null,
                        'changed_at' => $row->changed_at,
                        'status_id'  => null,
                    ];
                }
            }
        }

        // Newest first — the latest update belongs at the top of the page,
        // so a customer checking in sees what just happened without
        // scrolling.
        //
        // "Created" is always the very last/oldest thing in the list, even
        // though the external sourcing pipeline's own tags (e.g. "Salibay
        // Needs Sourcing") can legitimately carry a timestamp that ties
        // with, or even edges out, the shipment's own creation moment (both
        // get written within the same request when an order is caught up
        // via the self-heal/re-import path — see
        // self_heal_stuck_international_orders() in Fulfilment.php). Pin it
        // as the oldest entry unconditionally rather than trusting raw
        // timestamp comparison for this one anchor event.
        $created_status = $CI->db->where('status_name', 'created')->get(db_prefix() . '_shipment_statuses')->row();
        $created_status_id = $created_status ? (int) $created_status->id : null;

        usort($events, function ($a, $b) use ($created_status_id) {
            $a_is_created = $created_status_id !== null && ($a['status_id'] ?? null) === $created_status_id;
            $b_is_created = $created_status_id !== null && ($b['status_id'] ?? null) === $created_status_id;
            if ($a_is_created && !$b_is_created) {
                return 1;
            }
            if ($b_is_created && !$a_is_created) {
                return -1;
            }
            return strcmp($b['changed_at'], $a['changed_at']);
        });

        return $events;
    }
}

/**
 * Applies branch isolation to the current CI query builder: restricts to the
 * staff's assigned branches unless they're an admin or hold 'view_all_branches'.
 *
 * CAUTION — do not call this in the middle of building another query (i.e.
 * after you've already called ->select()/->from()/->join() on $this->db for
 * a DIFFERENT query that hasn't been ->get() yet). CI3's query builder is one
 * mutable object; courier_get_staff_branch_ids() (called internally here)
 * runs its OWN select()->where()->get() on that same object, which flushes
 * and corrupts whatever query you were still assembling — the two queries'
 * select/from/join fragments merge into one broken query. This exact bug
 * shipped once already (see _get_manifest_rows()'s branch_scope_ids
 * pre-resolution pattern for the fix: resolve the staff's branch IDs into a
 * plain PHP array via courier_get_staff_branch_ids() BEFORE calling
 * ->select()/->from()/->join() on your own query, then apply
 * ->where_in('branch_id_column', $ids) directly — no nested DB call mid-chain).
 * Only safe to call this function directly when it's the FIRST thing you do
 * with $this->db in the current query (nothing pending before it).
 */
if (!function_exists('courier_apply_branch_scope')) {
    function courier_apply_branch_scope($column = 'branch_id')
    {
        if (courier_staff_can_view_all_branches()) {
            return;
        }

        $CI = &get_instance();
        $ids = courier_get_staff_branch_ids();
        // No branches assigned at all — must not see any branch-scoped rows.
        $CI->db->where_in($column, !empty($ids) ? $ids : [0]);
    }
}

/**
 * Resolves which of the 3 visibility tiers a staff member has for a
 * courier_goshipping feature that registers the view_own_X / view_branch_X /
 * view_all_X (or the legacy un-suffixed view_X "global" name used by
 * manifests/invoices) capability triplet — 'global' (see every branch),
 * 'branch' (see everything in their assigned branch(es)), or 'own' (see only
 * records created-by/assigned-to them specifically). Global wins if granted
 * (staff_can() already returns true for admins); branch wins over own if
 * both are somehow granted; defaults to 'own' — the most restrictive tier —
 * if neither branch nor global is granted, matching this module's existing
 * default-deny-outward posture elsewhere.
 *
 * Per explicit instruction: 'own' means literally "staff_id = me", nothing
 * more — unlike the old 2-tier model, unassigned (staff_id = 0) records are
 * NOT included under 'own' any more (they still show up under 'branch',
 * since every shipment has a real branch_id stamped on it regardless of
 * assignment — see Shipments::store()). Callers must therefore apply
 * EXACTLY ONE of a strict staff_id filter (own) or a branch_id filter
 * (branch) or no filter at all (global) — never combine staff_id and
 * branch_id filtering together the way the old code accidentally did.
 */
if (!function_exists('courier_resolve_visibility_scope')) {
    function courier_resolve_visibility_scope($feature, $branch_cap, $global_cap, $staff_id = null)
    {
        if (staff_can($global_cap, $feature, $staff_id)) {
            return 'global';
        }
        if (staff_can($branch_cap, $feature, $staff_id)) {
            return 'branch';
        }
        return 'own';
    }
}

/**
 * Returns the company info to stamp on courier invoices, receipts, quotations,
 * waybills, and manifests.
 *
 * Courier-specific settings (courier_inv_*) take priority; Perfex global
 * settings are used as fallback so nothing is ever blank out of the box.
 */
if (!function_exists('courier_get_invoice_info')) {
    function courier_get_invoice_info($branch_id = null)
    {
        // A branch's own name/address/phone/email (set on Branches / Offices)
        // takes priority over the global letterhead settings, so a Dubai
        // shipment's documents show Dubai's office details instead of
        // whichever branch happens to be configured globally.
        $branch = null;
        if (!empty($branch_id)) {
            $CI = &get_instance();
            if ($CI->db->table_exists(db_prefix() . '_courier_branches')) {
                $branch = $CI->db->where('id', (int) $branch_id)->get(db_prefix() . '_courier_branches')->row();
            }
        }

        // Resolve company name: branch override → courier override → courier_logistic_company → Perfex company name
        $_lc_raw = get_option('courier_logistic_company');
        $_lc     = (!empty($_lc_raw) && $_lc_raw !== 'GO Shipping') ? $_lc_raw : get_option('companyname');

        $name    = ($branch->name ?? '')    ?: (get_option('courier_inv_company_name') ?: ($_lc ?: ''));
        $email   = ($branch->email ?? '')   ?: (get_option('courier_inv_email')        ?: (get_option('email') ?: get_option('company_email') ?: ''));
        $phone   = ($branch->phone ?? '')   ?: (get_option('courier_inv_phone')        ?: (get_option('invoice_company_phonenumber') ?: get_option('phonenumber') ?: ''));
        $address = ($branch->address ?? '') ?: (get_option('courier_inv_address')      ?: (get_option('company_address') ?: ''));
        $website = get_option('courier_inv_website')      ?: '';
        $pin     = get_option('courier_inv_pin')          ?: '';
        $tagline = get_option('courier_inv_tagline')      ?: '';

        return compact('name', 'email', 'phone', 'address', 'website', 'pin', 'tagline');
    }
}

/**
 * Resolves the currency a branch should invoice in — e.g. Dubai/China
 * offices billing in USD while the Kenya head office (and every branch that
 * hasn't been given an explicit override) keeps using the org-wide base
 * currency (KES). Same override-with-fallback shape as
 * courier_get_invoice_info() above. NULL/0 $branch_id (no branch resolved,
 * e.g. an admin with no branch assignment) also falls back to base currency.
 * Never throws — a missing/invalid branch currency_id just falls back.
 */
if (!function_exists('courier_get_branch_currency_id')) {
    function courier_get_branch_currency_id($branch_id = null)
    {
        if (!empty($branch_id)) {
            $CI = &get_instance();
            if ($CI->db->table_exists(db_prefix() . '_courier_branches') && $CI->db->field_exists('currency_id', db_prefix() . '_courier_branches')) {
                $branch = $CI->db->select('currency_id')->where('id', (int) $branch_id)->get(db_prefix() . '_courier_branches')->row();
                if (!empty($branch->currency_id)) {
                    return (int) $branch->currency_id;
                }
            }
        }

        $base_currency = get_base_currency();
        return $base_currency ? (int) $base_currency->id : 1;
    }
}

/**
 * Minimal, dependency-free .xlsx reader (no PhpSpreadsheet / Composer vendor
 * required) — deliberately hand-rolled because this module deploys to
 * production purely by `modules/*` folders being git-pulled by a cPanel cron
 * (see root CLAUDE.md); any composer vendor/ directory is normally
 * .gitignore'd (as modules/surveys/vendor is) and would silently never reach
 * the live server, so a "PhpSpreadsheet is available locally" assumption
 * would work in dev and 500 in production. An .xlsx is just a zip of XML
 * parts, so ZipArchive + SimpleXML is enough for the flat rate-sheet shape
 * this module needs (shared strings + a single worksheet, no styles/merges).
 *
 * Returns [row_number => [col_letter => raw_value]] (values are strings —
 * numeric cells come back as their plain numeric string, since Excel stores
 * thousands separators as display formatting only, never in the cell value),
 * or false if the file can't be read as an xlsx (missing ZipArchive, not a
 * zip, no worksheet found, etc).
 */
if (!function_exists('courier_parse_xlsx_rows')) {
    function courier_parse_xlsx_rows($file_path)
    {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open($file_path) !== true) {
            return false;
        }

        // Shared strings table (string cells are stored as an index into this).
        $shared = [];
        $ss_xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ss_xml !== false) {
            $ss = @simplexml_load_string($ss_xml);
            if ($ss !== false && isset($ss->si)) {
                foreach ($ss->si as $si) {
                    if (isset($si->t)) {
                        $shared[] = (string) $si->t;
                    } else {
                        // Rich text run (bold/italic spans) — concatenate the runs.
                        $text = '';
                        if (isset($si->r)) {
                            foreach ($si->r as $r) {
                                $text .= (string) $r->t;
                            }
                        }
                        $shared[] = $text;
                    }
                }
            }
        }

        // First worksheet — sheet1.xml if present, else the first sheetN.xml found.
        $sheet_xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheet_xml === false) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (preg_match('#^xl/worksheets/sheet\d+\.xml$#', $name)) {
                    $sheet_xml = $zip->getFromName($name);
                    break;
                }
            }
        }
        $zip->close();

        if ($sheet_xml === false) {
            return false;
        }

        $xml = @simplexml_load_string($sheet_xml);
        if ($xml === false || !isset($xml->sheetData)) {
            return false;
        }

        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $r = (int) $row['r'];
            if ($r <= 0) {
                continue;
            }
            foreach ($row->c as $c) {
                $ref = (string) $c['r'];
                if (!preg_match('/^([A-Z]+)\d+$/', $ref, $m)) {
                    continue;
                }
                $col  = $m[1];
                $type = (string) $c['t'];

                if ($type === 's') {
                    $idx = isset($c->v) ? (int) $c->v : -1;
                    $value = $shared[$idx] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = isset($c->is->t) ? (string) $c->is->t : '';
                } else {
                    $value = isset($c->v) ? (string) $c->v : null;
                }

                $rows[$r][$col] = $value;
            }
        }

        return $rows;
    }
}

/**
 * Shared origin-tariff matrix lookup — Kenya (or any configured origin) →
 * destination country, keyed by service_type, weight-banded. Used both by
 * the client-portal quote calculator's own inline query (Tracker::calculate_quote)
 * and by admin shipment creation (Shipments::process_invoice_and_packages) so
 * both places agree on the same rate for the same route/weight. Cells are
 * "flat" (a fixed price for that whole weight band — how the uploaded rate-sheet
 * Excels are structured) or "per_kg" (used for the >70kg tail band, where the
 * sheet gives a per-kg overage rate instead of one more flat cell).
 *
 * Returns ['rate_type' => .., 'unit_rate' => .., 'amount' => ..] or null if no
 * row covers this route/weight (caller should fall back to its own default).
 */
if (!function_exists('courier_lookup_origin_tariff')) {
    function courier_lookup_origin_tariff($origin_country, $destination_country, $service_type, $chargeable_weight)
    {
        if ($origin_country === '' || $destination_country === '' || $service_type === '' || $chargeable_weight <= 0) {
            return null;
        }

        $CI = &get_instance();
        $origin_tbl = db_prefix() . '_courier_origin_tariffs';
        if (!$CI->db->table_exists($origin_tbl)) {
            return null;
        }

        $row = $CI->db
            ->where('origin_country', $origin_country)
            ->where('destination_country', $destination_country)
            ->where('service_type', $service_type)
            ->where('weight_min <=', $chargeable_weight)
            ->where('weight_max >=', $chargeable_weight)
            ->order_by('weight_max', 'ASC')
            ->get($origin_tbl)
            ->row_array();

        if (!$row) {
            return null;
        }

        $rate = (float) $row['rate'];
        if ($rate <= 0) {
            return null;
        }

        $amount = ($row['rate_type'] === 'per_kg') ? $rate * $chargeable_weight : $rate;

        return [
            'rate_type'  => $row['rate_type'],
            'unit_rate'  => $rate,
            'amount'     => round($amount, 2),
        ];
    }
}

/**
 * FCL counterpart of courier_lookup_origin_tariff() — FCL is priced per
 * container, not per weight, so it's matched by container_type (e.g.
 * "20dv") instead of a weight band (see run_db_upgrades_v53()). Used by
 * Shipments::process_invoice_and_packages() so an uploaded China -> World
 * FCL rate sheet actually prices an FCL shipment instead of the flat
 * per-container option under Settings -> Customization (courier_rate_sea_fcl_*)
 * always winning regardless of destination.
 *
 * $container_type must already be normalized the same way Shipments.php
 * derives it from the fcl_options[] dropdown ("20'DV" -> "20dv").
 *
 * Returns the flat container rate (float) or null if no matrix row covers
 * this route/container (caller should fall back to the flat option).
 */
if (!function_exists('courier_lookup_origin_fcl_rate')) {
    function courier_lookup_origin_fcl_rate($origin_country, $destination_country, $container_type)
    {
        if ($origin_country === '' || $destination_country === '' || $container_type === '') {
            return null;
        }

        $CI = &get_instance();
        $origin_tbl = db_prefix() . '_courier_origin_tariffs';
        if (!$CI->db->table_exists($origin_tbl) || !$CI->db->field_exists('container_type', $origin_tbl)) {
            return null;
        }

        $row = $CI->db
            ->where('origin_country', $origin_country)
            ->where('destination_country', $destination_country)
            ->where('service_type', 'fcl')
            ->where('container_type', $container_type)
            ->get($origin_tbl)
            ->row_array();

        if (!$row) {
            return null;
        }

        $rate = (float) $row['rate'];
        return $rate > 0 ? $rate : null;
    }
}






/**
 * Pickup charges: a pickup can carry any number of staff-entered, custom
 * charge lines (description + amount). Stored as JSON in tbl_pickups.charges
 * with the sum in charges_total. Columns are created lazily (deploys are a
 * plain file copy — install.php never re-runs), guarded by an option flag.
 */
if (!function_exists('courier_ensure_pickup_charge_columns')) {
    function courier_ensure_pickup_charge_columns()
    {
        if (get_option('courier_pickup_charges_cols_done')) {
            return;
        }
        $CI    = &get_instance();
        $table = db_prefix() . '_pickups';
        if (!$CI->db->table_exists($table)) {
            return;
        }
        if (!$CI->db->field_exists('charges', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD `charges` TEXT NULL DEFAULT NULL");
        }
        if (!$CI->db->field_exists('charges_total', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD `charges_total` DECIMAL(15,2) NOT NULL DEFAULT 0");
        }
        if ($CI->db->field_exists('charges', $table) && $CI->db->field_exists('charges_total', $table)) {
            if (get_option('courier_pickup_charges_cols_done') === '') {
                add_option('courier_pickup_charges_cols_done', '1');
            } else {
                update_option('courier_pickup_charges_cols_done', '1');
            }
        }
    }
}

/**
 * Turns the posted pickup_charge_desc[] / pickup_charge_amount[] arrays into
 * ['rows' => [['description' => , 'amount' => ], ...], 'json' => string|null,
 * 'total' => float]. Fully blank lines are dropped; a line with an amount but
 * no description is kept under a generic label. Amounts are validated by the
 * callers' form_validation rule, so non-numeric values are skipped here.
 */
if (!function_exists('courier_parse_pickup_charges')) {
    function courier_parse_pickup_charges($descs, $amounts)
    {
        $rows  = [];
        $total = 0.0;
        if (is_array($descs) || is_array($amounts)) {
            $descs   = is_array($descs) ? $descs : [];
            $amounts = is_array($amounts) ? $amounts : [];
            $count   = max(count($descs), count($amounts));
            $descs   = array_values($descs);
            $amounts = array_values($amounts);
            for ($i = 0; $i < $count && count($rows) < 30; $i++) {
                $desc = trim((string) ($descs[$i] ?? ''));
                $raw  = trim((string) ($amounts[$i] ?? ''));
                if ($desc === '' && $raw === '') {
                    continue;
                }
                if ($raw !== '' && !is_numeric($raw)) {
                    continue;
                }
                $amount = $raw === '' ? 0.0 : round((float) $raw, 2);
                if ($amount < 0) {
                    continue;
                }
                $rows[] = [
                    'description' => mb_substr($desc !== '' ? $desc : 'Pickup charge', 0, 120),
                    'amount'      => $amount,
                ];
                $total += $amount;
            }
        }
        return [
            'rows'  => $rows,
            'json'  => $rows ? json_encode($rows) : null,
            'total' => round($total, 2),
        ];
    }
}

/**
 * Towns / cities for a country, from the free countriesnow.space API (the
 * same service the module already uses for POD towns), fetched server-side
 * and cached for 30 days per country so the shipment form isn't slowed down
 * or blocked by CORS. Returns a sorted array of names ([] on any failure —
 * failures are cached only briefly so a temporary outage self-heals).
 */
if (!function_exists('courier_get_country_towns')) {
    function courier_get_country_towns($country_id)
    {
        $CI = &get_instance();
        $country = $CI->db->get_where(db_prefix() . 'countries', ['country_id' => (int) $country_id])->row();
        if (!$country || trim((string) $country->short_name) === '') {
            return [];
        }
        $name = trim($country->short_name);

        @$CI->load->driver('cache', ['adapter' => 'file']);
        $cache_key = 'courier_towns_' . md5(strtolower($name));
        if (isset($CI->cache)) {
            $cached = @$CI->cache->get($cache_key);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $towns = [];
        $ok    = false;
        if (function_exists('curl_init')) {
            $ch = curl_init('https://countriesnow.space/api/v0.1/countries/cities/q?country=' . urlencode($name));
            curl_setopt_array($ch, [
                CURLOPT_HTTPHEADER     => ['Accept: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 8,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $raw  = curl_exec($ch);
            curl_close($ch);
            $json = $raw ? json_decode($raw, true) : null;
            if (is_array($json) && empty($json['error']) && isset($json['data']) && is_array($json['data'])) {
                $ok = true;
                foreach ($json['data'] as $t) {
                    $t = trim((string) $t);
                    if ($t !== '') {
                        $towns[$t] = $t;
                    }
                }
                $towns = array_values($towns);
                sort($towns, SORT_NATURAL | SORT_FLAG_CASE);
            }
        }

        if (isset($CI->cache)) {
            @$CI->cache->save($cache_key, $towns, $ok ? 30 * 86400 : 600);
        }
        return $towns;
    }
}

/**
 * The shipment form's "State / Town" dropdowns offer API towns alongside the
 * local states, posted as "town:<Name>". tbl_*.state_id columns are INTs, so
 * before validation/saving each such value is swapped for the id of a
 * matching tbl_country_states row for that country — created on first use.
 * Every existing flow then keeps working unchanged. Call before validation.
 */
if (!function_exists('courier_resolve_posted_towns')) {
    function courier_resolve_posted_towns(array $pairs)
    {
        $CI    = &get_instance();
        $table = db_prefix() . '_country_states';
        foreach ($pairs as $country_field => $state_field) {
            $val = $CI->input->post($state_field);
            if (!is_string($val) || strpos($val, 'town:') !== 0) {
                continue;
            }
            $town       = trim(mb_substr($val, 5));
            $country_id = (int) $CI->input->post($country_field);
            if ($town === '' || $country_id <= 0) {
                $_POST[$state_field] = '';
                continue;
            }
            $row = $CI->db->where('country_id', $country_id)->where('name', $town)->get($table)->row();
            if ($row) {
                $id = (int) $row->id;
            } else {
                $c = $CI->db->get_where(db_prefix() . 'countries', ['country_id' => $country_id])->row();
                $CI->db->insert($table, [
                    'name'         => $town,
                    'country_id'   => $country_id,
                    'country_code' => (string) ($c->iso2 ?? ''),
                ]);
                $id = (int) $CI->db->insert_id();
            }
            $_POST[$state_field] = $id ?: '';
        }
    }
}

/* ───────────────────────────────────────────────────────────────────────────
 * COUNTRY ADMINS
 *
 * Tiers: the super user (a Perfex administrator) sees every country; a
 * *country admin* is an ordinary (non-administrator) staff member linked to
 * one or more countries in tbl_courier_country_admins. Their reach is the set
 * of branches (tbl_courier_branches.country_id) in those countries, and it is
 * enforced by the SAME branch isolation the module already has:
 * courier_get_staff_branch_ids() includes every branch of their country, so
 * shipments/pickups/manifests/agents/... are scoped with no per-page changes.
 * Country admins can add staff + agents for their country only (Country_team /
 * Agents controllers) and never get Perfex admin rights.
 * ─────────────────────────────────────────────────────────────────────────── */

if (!function_exists('courier_ensure_country_admins_table')) {
    function courier_ensure_country_admins_table()
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;
        $CI    = &get_instance();
        $table = db_prefix() . '_courier_country_admins';
        if (!$CI->db->table_exists($table)) {
            $CI->db->query('CREATE TABLE IF NOT EXISTS `' . $table . '` (
                `id`         INT NOT NULL AUTO_INCREMENT,
                `staff_id`   INT NOT NULL,
                `country_id` INT NOT NULL,
                `created_by` INT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_cca` (`staff_id`, `country_id`),
                INDEX `idx_cca_country` (`country_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
        }
    }
}

/** Country ids this staff member administers ([] for everyone else; never throws). */
if (!function_exists('courier_get_admin_country_ids')) {
    function courier_get_admin_country_ids($staff_id = null)
    {
        static $cache = [];
        $staff_id = (int) ($staff_id ?: get_staff_user_id());
        if ($staff_id <= 0) {
            return [];
        }
        if (isset($cache[$staff_id])) {
            return $cache[$staff_id];
        }
        $CI = &get_instance();
        courier_ensure_country_admins_table();
        $ids = [];
        if ($CI->db->table_exists(db_prefix() . '_courier_country_admins')) {
            $rows = $CI->db->select('country_id')->where('staff_id', $staff_id)
                ->get(db_prefix() . '_courier_country_admins')->result_array();
            $ids = array_map('intval', array_column($rows, 'country_id'));
        }
        return $cache[$staff_id] = $ids;
    }
}

/** True for a country admin who is NOT a super user. */
if (!function_exists('courier_is_country_admin')) {
    function courier_is_country_admin($staff_id = null)
    {
        $staff_id = $staff_id ?: get_staff_user_id();
        return !is_admin($staff_id) && !empty(courier_get_admin_country_ids($staff_id));
    }
}

/** Branch ids located in the given countries. */
if (!function_exists('courier_get_country_branch_ids')) {
    function courier_get_country_branch_ids(array $country_ids)
    {
        if (empty($country_ids)) {
            return [];
        }
        $CI   = &get_instance();
        $rows = $CI->db->select('id')->where_in('country_id', array_map('intval', $country_ids))
            ->get(db_prefix() . '_courier_branches')->result_array();
        return array_map('intval', array_column($rows, 'id'));
    }
}

/** Whether a staff member is inside the current country admin's reach (has a branch in their country). */
if (!function_exists('courier_staff_in_admin_scope')) {
    function courier_staff_in_admin_scope($target_staff_id)
    {
        $branches = courier_get_country_branch_ids(courier_get_admin_country_ids());
        if (empty($branches)) {
            return false;
        }
        $CI = &get_instance();
        return $CI->db->where('staff_id', (int) $target_staff_id)->where_in('branch_id', $branches)
            ->count_all_results(db_prefix() . '_courier_staff_branches') > 0;
    }
}

/** Roles a country admin may assign when creating staff/agents. */
if (!function_exists('courier_country_admin_allowed_roles')) {
    function courier_country_admin_allowed_roles()
    {
        $CI    = &get_instance();
        $roles = $CI->db->get(db_prefix() . 'roles')->result();
        return array_values(array_filter($roles, function ($r) {
            $n = (string) $r->name;
            return $n !== 'Courier: Country Admin' && (strpos($n, 'Courier: ') === 0 || strpos($n, 'Fleet: ') === 0);
        }));
    }
}

/** The "Courier: Country Admin" role (created on first use). */
if (!function_exists('courier_ensure_country_admin_role')) {
    function courier_ensure_country_admin_role()
    {
        $CI   = &get_instance();
        $role = $CI->db->where('name', 'Courier: Country Admin')->get(db_prefix() . 'roles')->row();
        if ($role) {
            return (int) $role->roleid;
        }
        $perms = [
            'courier-shipments'           => ['view_branch_shipments', 'create_shipments', 'edit_shipments'],
            'courier-pickups'             => ['view_branch_pickups', 'create_pickups', 'edit_pickups'],
            'courier-waybills'            => ['view_own_waybills'],
            'courier-commercial-invoices' => ['view_branch_commercial_invoices'],
            'courier-manifests'           => ['view_branch_manifests', 'create_manifests', 'edit_manifests'],
            'courier-invoices'            => ['view_branch_invoices', 'view_receipts', 'generate_payment'],
            'courier-agents'              => ['view_agents', 'create_agents', 'edit_agents', 'delete_agents'],
            'courier-companies'           => ['view_companies'],
        ];
        $CI->db->insert(db_prefix() . 'roles', ['name' => 'Courier: Country Admin', 'permissions' => serialize($perms)]);
        return (int) $CI->db->insert_id();
    }
}

/** Copies a role's permission set onto a staff member (additive, never removes). */
if (!function_exists('courier_grant_role_permissions')) {
    function courier_grant_role_permissions($staff_id, $role_id)
    {
        $CI    = &get_instance();
        $role  = $CI->db->where('roleid', (int) $role_id)->get(db_prefix() . 'roles')->row();
        $perms = ($role && $role->permissions) ? unserialize($role->permissions) : [];
        foreach ($perms as $feature => $caps) {
            foreach ($caps as $cap) {
                $exists = $CI->db->where(['staff_id' => (int) $staff_id, 'feature' => $feature, 'capability' => $cap])
                    ->count_all_results(db_prefix() . 'staff_permissions');
                if (!$exists) {
                    $CI->db->insert(db_prefix() . 'staff_permissions', ['staff_id' => (int) $staff_id, 'feature' => $feature, 'capability' => $cap]);
                }
            }
        }
    }
}
