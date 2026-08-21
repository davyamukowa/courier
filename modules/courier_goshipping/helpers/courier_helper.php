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

        return array_map('intval', array_column($rows, 'branch_id'));
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
            'Your Parcel Is On Its Way — Waybill {waybill_number} - {company_name}',
            '<p>Dear {sender_name},</p>
<p>Thank you for shipping with {company_name}. Your parcel has been picked up and assigned waybill number <strong>{waybill_number}</strong>, on its way to {recipient_name}.</p>
<p>You can track its journey at any time using the button below.</p>
<div style="text-align:center;margin:28px 0;">
  <a href="{tracking_link}" style="display:inline-block;padding:18px 44px;background:#c62828;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:bold;font-size:20px;letter-spacing:0.5px;text-transform:uppercase;box-shadow:0 4px 14px rgba(198,40,40,0.4);">Track Your Shipment</a>
</div>
<p>Or copy and paste this link into your browser:<br>{tracking_link}</p>
<p>Thank you for choosing <strong>{company_name}</strong>.</p>',
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

            $mode = ucfirst(str_replace('_', ' ', $shipment->shipping_mode ?? 'road'));

            courier_ensure_notification_email_templates();

            $mail = mail_template('Courier_waybill_to_customer', 'courier_goshipping', $to_email, [
                '{recipient_name}' => $recip_name ?: 'Customer',
                '{sender_name}'    => $sender_name,
                '{waybill_number}' => $waybill,
                '{shipping_mode}'  => $mode,
                '{status}'         => $status_name,
                '{company_name}'   => $company_name,
                '{tracking_link}'  => $tracking_url,
            ]);

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
 * Renders a simple, self-contained TCPDF document from inline-styled HTML —
 * shared by the waybill/commercial-invoice PDF generators below. Deliberately
 * NOT a reuse of the admin waybill.php/commercial_invoice.php views: those are
 * full interactive admin pages (flexbox, box-shadow, watermark image, print
 * JS) that TCPDF's limited HTML/CSS renderer can't reproduce — this builds a
 * lighter, print-safe layout with the same underlying data instead.
 */
if (!function_exists('_courier_render_pdf')) {
    function _courier_render_pdf($html, $title)
    {
        if (!class_exists('TCPDF', false)) {
            require_once(APPPATH . 'vendor/tecnickcom/tcpdf/tcpdf.php');
        }

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Go Shipping Cargo');
        $pdf->SetAuthor(get_option('companyname') ?: 'Go Shipping Cargo');
        $pdf->SetTitle($title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');

        return $pdf->Output($title . '.pdf', 'S');
    }
}

/**
 * Generates the waybill as a PDF binary string, for attaching to the
 * "waybill created" customer emails (courier_send_shipment_waybill_email())
 * and the manual "Send by Email" button (Shipments::send_waybill_email()).
 * Returns null (never throws) on any missing data so a PDF failure can never
 * block the email itself from sending without an attachment.
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
            $sender_lines    = _courier_pdf_party_lines($details['sender'] ?? null, $details['sender_type'] ?? 'individual', $details['sender_country'] ?? null);
            $recipient_lines = _courier_pdf_party_lines($details['recipient'] ?? null, $details['recipient_type'] ?? 'individual', $details['recipient_country'] ?? null);

            $invoice_info     = courier_get_invoice_info($shipment->branch_id ?? null);
            $logistic_company = $invoice_info['name'] ?: (get_option('companyname') ?: 'Go Shipping Cargo');
            $waybill_number   = htmlspecialchars($shipment->waybill_number ?: $shipment->tracking_id);

            $th = 'style="background:#f5f5f5;border:1px solid #ccc;padding:5px 6px;font-size:9px;font-weight:bold;text-align:left;width:22%;"';
            $td = 'style="border:1px solid #ccc;padding:5px 6px;font-size:9px;width:28%;"';

            $packages_rows = '';
            $counter = 1;
            foreach (($details['packages'] ?? []) as $package) {
                $packages_rows .= '<tr>'
                    . '<td ' . $td . '>' . $counter . '</td>'
                    . '<td ' . $td . '>' . htmlspecialchars((string) ($package->quantity ?? '')) . '</td>'
                    . '<td ' . $td . '>' . htmlspecialchars((string) ($package->description ?? '-')) . '</td>'
                    . '<td ' . $td . '>' . (isset($package->weight) ? number_format((float) $package->weight, 2) . ' kg' : '-') . '</td>'
                    . '</tr>';
                $counter++;
            }
            if ($packages_rows === '') {
                $packages_rows = '<tr><td ' . $td . ' colspan="4">No package details recorded.</td></tr>';
            }

            $html = '
                <div style="text-align:center;margin-bottom:8px;">
                    <span style="font-size:16px;font-weight:bold;">' . htmlspecialchars($logistic_company) . '</span><br>
                    <span style="font-size:13px;font-weight:bold;">WAYBILL</span><br>
                    <span style="font-size:10px;color:#555;">Waybill Number: ' . $waybill_number . '</span>
                </div>
                <table cellpadding="4" style="width:100%;border-collapse:collapse;margin-top:6px;">
                    <tr>
                        <th ' . $th . '>Sender</th>
                        <td ' . $td . '>' . htmlspecialchars($sender_lines['name']) . '</td>
                        <th ' . $th . '>Receiver</th>
                        <td ' . $td . '>' . htmlspecialchars($recipient_lines['name']) . '</td>
                    </tr>
                    <tr>
                        <th ' . $th . '>Sender Address</th>
                        <td ' . $td . '>' . htmlspecialchars($sender_lines['address']) . '</td>
                        <th ' . $th . '>Receiver Address</th>
                        <td ' . $td . '>' . htmlspecialchars($recipient_lines['address']) . '</td>
                    </tr>
                    <tr>
                        <th ' . $th . '>Sender Phone</th>
                        <td ' . $td . '>' . htmlspecialchars($sender_lines['phone']) . '</td>
                        <th ' . $th . '>Receiver Phone</th>
                        <td ' . $td . '>' . htmlspecialchars($recipient_lines['phone']) . '</td>
                    </tr>
                    <tr>
                        <th ' . $th . '>Tracking Number</th>
                        <td ' . $td . ' colspan="3">' . $waybill_number . '</td>
                    </tr>
                    <tr>
                        <th ' . $th . '>Shipping Level</th>
                        <td ' . $td . '>' . htmlspecialchars(strtoupper($shipment->shipping_category ?? '-')) . '</td>
                        <th ' . $th . '>Shipping Mode</th>
                        <td ' . $td . '>' . htmlspecialchars((string) ($shipment->shipping_mode ?? '-')) . '</td>
                    </tr>
                    <tr>
                        <th ' . $th . '>Status</th>
                        <td ' . $td . ' colspan="3">' . htmlspecialchars($shipment->status_description ?? $shipment->status_name ?? '-') . '</td>
                    </tr>
                </table>
                <div style="margin-top:12px;font-size:11px;font-weight:bold;">Package Details</div>
                <table cellpadding="4" style="width:100%;border-collapse:collapse;margin-top:4px;">
                    <tr>
                        <th ' . $th . ' width="8%">#</th>
                        <th ' . $th . '>Quantity</th>
                        <th ' . $th . '>Description</th>
                        <th ' . $th . '>Weight</th>
                    </tr>
                    ' . $packages_rows . '
                </table>
            ';

            return _courier_render_pdf($html, 'Waybill-' . ($shipment->waybill_number ?: $shipment->tracking_id));
        } catch (\Throwable $e) {
            log_message('error', 'Waybill PDF generation crashed: ' . $e->getMessage());
            return null;
        }
    }
}

/**
 * Generates the commercial invoice as a PDF binary string — attached
 * alongside the waybill PDF on a plain Go Shipping shipment's email only.
 * Salibay/Shopify orders never get this attachment: those are marketplace
 * orders with no customs "commercial value" declaration of our own to send,
 * per explicit instruction (see modules/courier_goshipping/CLAUDE.md — Salibay
 * shipments only ever get the waybill). Returns null (never throws) on any
 * missing data.
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
            $sender_lines    = _courier_pdf_party_lines($details['sender'] ?? null, $details['sender_type'] ?? 'individual', $details['sender_country'] ?? null);
            $recipient_lines = _courier_pdf_party_lines($details['recipient'] ?? null, $details['recipient_type'] ?? 'individual', $details['recipient_country'] ?? null);

            $invoice_info     = courier_get_invoice_info($shipment->branch_id ?? null);
            $logistic_company = $invoice_info['name'] ?: (get_option('companyname') ?: 'Go Shipping Cargo');
            $waybill_number   = htmlspecialchars($shipment->waybill_number ?: $shipment->tracking_id);

            $currency_symbol = 'USD';
            if (!empty($shipment->invoice_id)) {
                $inv = $CI->db->select('currency')->where('id', $shipment->invoice_id)->get(db_prefix() . 'invoices')->row();
                if ($inv && $inv->currency && ($currency_obj = get_currency($inv->currency))) {
                    $currency_symbol = $currency_obj->name;
                }
            } elseif (!empty($shipment->currency_name)) {
                $currency_symbol = $shipment->currency_name;
            } elseif (function_exists('get_base_currency') && ($base_currency = get_base_currency())) {
                $currency_symbol = $base_currency->name;
            }

            $th = 'style="background:#f5f5f5;border:1px solid #ccc;padding:5px 6px;font-size:9px;font-weight:bold;text-align:left;"';
            $td = 'style="border:1px solid #ccc;padding:5px 6px;font-size:9px;"';

            $items = !empty($details['commercial_details']) ? $details['commercial_details'] : ($details['packages'] ?? []);
            $rows  = '';
            $counter = 1;
            $total   = 0;
            foreach ($items as $item) {
                $qty  = $item->quantity ?? 1;
                $desc = $item->description ?? '';
                $val  = $item->declared_value ?? ($item->price ?? '-');
                $rows .= '<tr>'
                    . '<td ' . $td . '>' . $counter . '</td>'
                    . '<td ' . $td . '>' . htmlspecialchars((string) $qty) . '</td>'
                    . '<td ' . $td . '>' . htmlspecialchars((string) $desc) . '</td>'
                    . '<td ' . $td . ' align="right">' . htmlspecialchars((string) $val) . '</td>'
                    . '</tr>';
                if (is_numeric($val)) {
                    $total += (float) $val;
                }
                $counter++;
            }
            if ($rows === '') {
                $rows = '<tr><td ' . $td . ' colspan="4">No commercial value items recorded.</td></tr>';
            }

            $html = '
                <div style="text-align:center;margin-bottom:8px;">
                    <span style="font-size:13px;font-weight:bold;">COMMERCIAL INVOICE</span><br>
                    <span style="font-size:10px;color:#555;">' . htmlspecialchars($logistic_company) . '</span><br>
                    <span style="font-size:10px;color:#555;">Waybill Number: ' . $waybill_number . '</span>
                </div>
                <table cellpadding="4" style="width:100%;border-collapse:collapse;margin-top:6px;">
                    <tr>
                        <th ' . $th . ' width="50%">SENDER</th>
                        <th ' . $th . ' width="50%">RECEIVER</th>
                    </tr>
                    <tr>
                        <td ' . $td . '>' . htmlspecialchars($sender_lines['name']) . '</td>
                        <td ' . $td . '>' . htmlspecialchars($recipient_lines['name']) . '</td>
                    </tr>
                    <tr>
                        <td ' . $td . '>' . htmlspecialchars($sender_lines['address']) . '</td>
                        <td ' . $td . '>' . htmlspecialchars($recipient_lines['address']) . '</td>
                    </tr>
                    <tr>
                        <td ' . $td . '>Tel: ' . htmlspecialchars($sender_lines['phone']) . '</td>
                        <td ' . $td . '>Tel: ' . htmlspecialchars($recipient_lines['phone']) . '</td>
                    </tr>
                </table>
                <table cellpadding="4" style="width:100%;border-collapse:collapse;margin-top:10px;">
                    <tr>
                        <th ' . $th . ' width="8%">#</th>
                        <th ' . $th . ' width="15%">Qty</th>
                        <th ' . $th . '>Description</th>
                        <th ' . $th . ' width="20%">Amount (' . htmlspecialchars($currency_symbol) . ')</th>
                    </tr>
                    ' . $rows . '
                    <tr>
                        <td ' . $td . '></td>
                        <td ' . $td . ' style="font-weight:bold;">TOTAL</td>
                        <td ' . $td . '></td>
                        <td ' . $td . ' align="right" style="font-weight:bold;">' . number_format($total, 2) . '</td>
                    </tr>
                </table>
                <div style="margin-top:14px;font-size:9px;color:#333;">
                    <strong>DECLARATION:</strong> I declare that this invoice shows the actual value/price of the
                    goods described and that all particulars are true and correct, and that the goods are of no
                    commercial value — the value used is only for customs purposes.
                </div>
            ';

            return _courier_render_pdf($html, 'Commercial-Invoice-' . ($shipment->waybill_number ?: $shipment->tracking_id));
        } catch (\Throwable $e) {
            log_message('error', 'Commercial invoice PDF generation crashed: ' . $e->getMessage());
            return null;
        }
    }
}

/**
 * Attaches the waybill PDF (always) and, only for a plain non-Salibay
 * shipment, the commercial invoice PDF too, onto a not-yet-sent
 * App_mail_template instance. Salibay/Shopify orders are marketplace orders
 * with no commercial-value declaration of our own — see
 * modules/courier_goshipping/CLAUDE.md's "How to tell Salibay shipment from
 * general shipment" section for why the gs_shipment_id existence check is
 * the only correct way to make that call. Never throws: a PDF generation
 * failure just means the email goes out without that attachment, exactly
 * like every other notification helper in this file.
 */
if (!function_exists('courier_attach_waybill_pdfs')) {
    function courier_attach_waybill_pdfs($mail, $shipment_id)
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

            if (!$is_salibay) {
                $commercial_invoice_pdf = courier_generate_commercial_invoice_pdf($shipment_id);
                if ($commercial_invoice_pdf !== null) {
                    $mail->add_attachment([
                        'attachment' => $commercial_invoice_pdf,
                        'filename'   => 'Commercial-Invoice-' . $shipment_id . '.pdf',
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
            $shipment = $resolved['shipment'];

            $recipient_name = 'the recipient';
            if (!empty($shipment->recipient_id)) {
                $r = $CI->db->where('id', $shipment->recipient_id)->get(db_prefix() . '_shipment_recipients')->row();
                if ($r) {
                    $recipient_name = trim(($r->first_name ?? '') . ' ' . ($r->last_name ?? '')) ?: $recipient_name;
                }
            } elseif (!empty($shipment->recipient_company_id)) {
                $rc = $CI->db->where('id', $shipment->recipient_company_id)->get(db_prefix() . '_recipient_companies')->row();
                if ($rc) {
                    $recipient_name = $rc->recipient_company_name ?? $recipient_name;
                }
            }

            $waybill = $shipment->waybill_number ?: $shipment->tracking_id;

            courier_ensure_notification_email_templates();

            $sent = mail_template('Courier_sender_tracking_info', 'courier_goshipping', $resolved['email'], [
                '{sender_name}'    => $resolved['sender_name'] ?: 'Customer',
                '{recipient_name}' => $recipient_name,
                '{waybill_number}' => $waybill,
                '{tracking_link}'  => courier_customer_tracking_link($waybill),
                '{company_name}'   => get_option('companyname'),
            ])->send();

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






