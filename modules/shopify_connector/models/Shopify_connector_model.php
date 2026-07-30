<?php
/**
 * c:\wamp64\www\perfex_crm\modules\shopify_connector\models\Shopify_connector_model.php
 */
defined('BASEPATH') or exit('No direct script access allowed');

class Shopify_connector_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    private function encrypt($data)
    {
        if (empty($data)) return $data;
        $key = defined('APP_ENC_KEY') ? APP_ENC_KEY : 'fallback_key';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', substr(hash('sha256', $key), 0, 32), 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    private function decrypt($data)
    {
        if (empty($data)) return $data;
        $key = defined('APP_ENC_KEY') ? APP_ENC_KEY : 'fallback_key';
        $decoded = base64_decode($data);
        if (strpos($decoded, '::') !== false) {
            list($encrypted, $iv) = explode('::', $decoded, 2);
            $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', substr(hash('sha256', $key), 0, 32), 0, $iv);
            return $decrypted !== false ? $decrypted : $data;
        }
        return $data;
    }

    public function get_store()
    {
        $this->db->where('is_active', 1);
        $store = $this->db->get(db_prefix() . 'shopify_stores')->row();
        if ($store) {
            $store->access_token = $this->decrypt($store->access_token);
            $store->api_key = $this->decrypt($store->api_key);
            $store->api_secret = $this->decrypt($store->api_secret);
            $store->webhook_secret = $this->decrypt($store->webhook_secret);
        }
        return $store;
    }

    public function save_store($data)
    {
        $store = $this->get_store();
        
        $db_data = [
            'shop_domain' => $data['shop_domain'],
            'api_version' => $data['api_version'],
            'default_fulfillment_model' => $data['default_fulfillment_model'],
            'is_active' => 1
        ];

        // Only update secrets if they are provided (not empty)
        if (!empty($data['access_token'])) {
            $db_data['access_token'] = $this->encrypt($data['access_token']);
        }
        if (!empty($data['api_key'])) {
            $db_data['api_key'] = $this->encrypt($data['api_key']);
        }
        if (!empty($data['api_secret'])) {
            $db_data['api_secret'] = $this->encrypt($data['api_secret']);
        }
        if (!empty($data['webhook_secret'])) {
            $db_data['webhook_secret'] = $this->encrypt($data['webhook_secret']);
        }

        if ($store) {
            $this->db->where('id', $store->id);
            $this->db->update(db_prefix() . 'shopify_stores', $db_data);
            return $store->id;
        } else {
            $this->db->insert(db_prefix() . 'shopify_stores', $db_data);
            return $this->db->insert_id();
        }
    }

    public function get_product_mappings($store_id)
    {
        $this->db->where('store_id', $store_id);
        $this->db->where('is_active', 1);
        return $this->db->get(db_prefix() . 'shopify_product_mappings')->result_array();
    }

    public function save_product_mapping($data)
    {
        $existing = $this->get_mapping_by_variant($data['store_id'], $data['shopify_variant_id']);
        if ($existing) {
            $this->db->where('id', $existing->id);
            $this->db->update(db_prefix() . 'shopify_product_mappings', $data);
            return $existing->id;
        } else {
            $this->db->insert(db_prefix() . 'shopify_product_mappings', $data);
            return $this->db->insert_id();
        }
    }

    public function get_mapping_by_variant($store_id, $variant_id)
    {
        $this->db->where('store_id', $store_id);
        $this->db->where('shopify_variant_id', $variant_id);
        return $this->db->get(db_prefix() . 'shopify_product_mappings')->row();
    }

    public function delete_product_mapping($id)
    {
        $this->db->where('id', $id);
        // Soft delete
        $this->db->update(db_prefix() . 'shopify_product_mappings', ['is_active' => 0]);
        return $this->db->affected_rows() > 0;
    }

    public function get_product_by_sku($gs_sku)
    {
        // Go Shipping items table
        $this->db->where('item_code', $gs_sku);
        return $this->db->get(db_prefix() . 'pos_products')->row();
    }

    public function get_inventory_levels($product_id)
    {
        // Fetch current stock from Go Shipping inventory table
        $this->db->where('product_id', $product_id);
        return $this->db->get(db_prefix() . 'pos_inventory')->row();
    }

    public function update_inventory_reservation($product_id, $qty_to_add)
    {
        $this->db->where('product_id', $product_id);
        $this->db->set('reserved_qty', "reserved_qty + ({$qty_to_add})", FALSE);
        $this->db->update(db_prefix() . 'pos_inventory');
        $err = $this->db->error();
        if ($err['code']) {
            log_activity("DB Error in update_inventory_reservation: " . $err['message']);
        }
        return $this->db->affected_rows() > 0;
    }

    public function commit_inventory_deduction($product_id, $qty_to_deduct, $unit_cost, $reference)
    {
        // Get current inventory to calculate before/after
        $this->db->where('product_id', $product_id);
        $inventory = $this->db->get(db_prefix() . 'pos_inventory')->row();
        if (!$inventory) return false;
        
        $qty_before = $inventory->quantity;
        $qty_after = $qty_before - abs($qty_to_deduct);

        // Deduct from on-hand and release the reserved qty
        $this->db->where('product_id', $product_id);
        $this->db->set('quantity', "quantity - (" . abs($qty_to_deduct) . ")", FALSE);
        $this->db->set('reserved_qty', "reserved_qty - (" . abs($qty_to_deduct) . ")", FALSE);
        $this->db->update(db_prefix() . 'pos_inventory');

        // Insert movement log
        $this->db->insert(db_prefix() . 'pos_inventory_movements', [
            'branch_id' => $inventory->branch_id,
            'product_id' => $product_id,
            'qty_before' => $qty_before,
            'qty_change' => -abs($qty_to_deduct),
            'qty_after' => $qty_after,
            'type' => 'sale',
            'reference_type' => 'shopify_order',
            'notes' => $reference,
            'unit_cost' => $unit_cost
        ]);
        return true;
    }

    public function create_courier_shipment_db($shipment_data, $sender_data, $recipient_data, $packages_data)
    {
        $this->db->trans_start();

        // 1. Insert Sender
        $this->db->insert(db_prefix() . '_shipment_senders', $sender_data);
        $sender_id = $this->db->insert_id();

        // 2. Insert Recipient
        $this->db->insert(db_prefix() . '_shipment_recipients', $recipient_data);
        $recipient_id = $this->db->insert_id();

        // 3. Insert Shipment
        $shipment_data['sender_id'] = $sender_id;
        $shipment_data['recipient_id'] = $recipient_id;
        $this->db->insert(db_prefix() . '_shipments', $shipment_data);
        $shipment_id = $this->db->insert_id();

        // 4. Insert Packages
        foreach ($packages_data as $package) {
            $package['shipment_id'] = $shipment_id;
            $this->db->insert(db_prefix() . '_shipment_packages', $package);
        }

        // 5. Insert Status History
        $this->db->insert(db_prefix() . '_shipment_status_history', [
            'shipment_id' => $shipment_id,
            'status_id' => $shipment_data['status_id'],
            'changed_at' => date('Y-m-d H:i:s')
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return false;
        }

        return $shipment_id;
    }

    /**
     * Once an international air-freight leg reaches "Arrived at Destination"
     * (status_id 6 — see Shipments::update_status()), the parcel has cleared
     * Nairobi and needs a separate local courier leg for last-mile delivery
     * to the customer. That's a genuinely different physical handoff (own
     * branch, own staff/rider, own vehicle) so it gets its own tbl_shipments
     * row and waybill number rather than mutating the international one —
     * `parent_shipment_id` links the two for the waybill UI to show both legs.
     *
     * Only ever acts on shipments that are actually linked to a Salibay/
     * Shopify order (found via shopify_orders.gs_shipment_id) — this must
     * never fire for the module's ordinary freight-forwarding air shipments,
     * which have no such link and aren't part of this two-leg flow.
     *
     * Idempotent: a second call for the same international shipment is a
     * no-op if a domestic leg already exists for it.
     */
    public function create_domestic_leg_from_international($international_shipment_id)
    {
        $order = $this->db->where('gs_shipment_id', $international_shipment_id)->get(db_prefix() . 'shopify_orders')->row();
        if (!$order) {
            return false;
        }

        $existing_child = $this->db->where('parent_shipment_id', $international_shipment_id)->get(db_prefix() . '_shipments')->row();
        if ($existing_child) {
            return false;
        }

        $intl_shipment = $this->db->where('id', $international_shipment_id)->get(db_prefix() . '_shipments')->row();
        if (!$intl_shipment || empty($intl_shipment->recipient_id)) {
            return false;
        }

        $CI = &get_instance();
        $CI->load->helper('courier_goshipping/courier');

        // Last-mile is always handled from the local/Kenya branch — the
        // org-wide default branch (is_default=1) IS that branch, per
        // courier_get_fallback_branch_id()'s existing "domestic fallback"
        // role used everywhere else in this module.
        $local_branch_id = courier_get_fallback_branch_id();
        $branch_info = courier_get_invoice_info($local_branch_id);
        $branch_name_parts = explode(' ', trim($branch_info['name'] ?: 'Go Shipping Cargo'), 2);

        $sender_data = [
            'first_name'   => $branch_name_parts[0],
            'last_name'    => $branch_name_parts[1] ?? '',
            'phone_number' => $branch_info['phone'] ?: (get_option('company_phonenumber') ?: '000000000'),
            'email'        => $branch_info['email'] ?: (get_option('smtp_email') ?: 'warehouse@example.com'),
            'address'      => trim(strip_tags(str_replace(['<br />', '<br/>', '<br>'], ', ', $branch_info['address'] ?: format_organization_info())), ', ') ?: 'Main Warehouse',
            'zipcode'      => '00100',
            'address_type' => 'postal_code',
        ];

        // Same customer as the international leg — copy the recipient
        // fields across (a fresh row, since create_courier_shipment_db()
        // always inserts one; the two legs don't share a recipient_id).
        $recipient_data = $this->db->where('id', $intl_shipment->recipient_id)->get(db_prefix() . '_shipment_recipients')->row_array();
        unset($recipient_data['id']);
        if (empty($recipient_data)) {
            return false;
        }

        // "D" suffix keeps this waybill visually distinct from — and never
        // colliding with — the international leg's number, which is built
        // from the same date + order id.
        $tracking_number = 'GS' . date('Ymd') . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) . 'D';

        $status_row = $this->db->where('status_name', 'created')->get(db_prefix() . '_shipment_statuses')->row();

        $shipment_data = [
            'shipping_mode'     => 'Courier',
            'shipping_category' => 'domestic',
            'tracking_id'       => $tracking_number,
            'waybill_number'    => $tracking_number,
            'company_type'      => 'company',
            'status_id'         => $status_row ? (int) $status_row->id : 1,
            'staff_id'          => 0,
            'packaging_charges' => 0.00,
            'created_at'        => date('Y-m-d H:i:s'),
            'parent_shipment_id' => (int) $international_shipment_id,
        ];
        if ($this->db->field_exists('branch_id', db_prefix() . '_shipments')) {
            $shipment_data['branch_id'] = $local_branch_id;
        }

        $packages_data = array_map(function ($p) {
            // A fresh leg starts with no proof-of-delivery of its own — the
            // international leg's POD (if any) was for arrival at Nairobi,
            // not final customer delivery.
            unset($p['id'], $p['shipment_id'], $p['pod']);
            return $p;
        }, $this->db->where('shipment_id', $international_shipment_id)->get(db_prefix() . '_shipment_packages')->result_array());

        $new_shipment_id = $this->create_courier_shipment_db($shipment_data, $sender_data, $recipient_data, $packages_data);

        if ($new_shipment_id) {
            // gs_shipment_id now tracks the active/current leg — status
            // pushes to Shopify (push_shopify_fulfillment_status()) key off
            // it, and the real "Delivered" event belongs to this domestic
            // leg (the international leg only got the parcel to Nairobi).
            $this->db->where('id', $order->id)->update(db_prefix() . 'shopify_orders', [
                'gs_shipment_id' => $new_shipment_id,
            ]);
            log_activity("Domestic last-mile leg #{$tracking_number} created from international shipment #{$international_shipment_id} for Salibay order SHF-{$order->shopify_order_id}");
        }

        return $new_shipment_id;
    }

    public function create_reservation_record($data)
    {
        $this->db->insert(db_prefix() . 'shopify_stock_reservations', $data);
        return $this->db->insert_id();
    }

    public function get_inventory_sync_report($store_id)
    {
        // Get the latest sync status per sku
        $sql = "
            SELECT s1.* 
            FROM " . db_prefix() . "shopify_inventory_sync s1
            JOIN (
                SELECT gs_sku, MAX(synced_at) as max_sync 
                FROM " . db_prefix() . "shopify_inventory_sync 
                WHERE store_id = ? 
                GROUP BY gs_sku
            ) s2 ON s1.gs_sku = s2.gs_sku AND s1.synced_at = s2.max_sync
            WHERE s1.store_id = ?
            ORDER BY s1.synced_at DESC
        ";
        return $this->db->query($sql, [$store_id, $store_id])->result_array();
    }

    public function get_active_reservations_for_order($shopify_db_order_id)
    {
        $this->db->where('shopify_order_id', $shopify_db_order_id);
        $this->db->where('status', 'active');
        return $this->db->get(db_prefix() . 'shopify_stock_reservations')->result();
    }

    public function update_reservation_status($reservation_id, $status)
    {
        $this->db->where('id', $reservation_id);
        $data = ['status' => $status];
        if ($status == 'released') {
            $data['released_at'] = date('Y-m-d H:i:s');
        }
        $this->db->update(db_prefix() . 'shopify_stock_reservations', $data);
        return $this->db->affected_rows() > 0;
    }

    // ── Outbound fulfillment sync (courier side -> Shopify) ────────────────
    //
    // Called from every place a shipment's status changes (staff Update
    // Status, the rider PWA, the Salibay short delivery link, fleet trips),
    // so a Shopify order never shows "Unfulfilled" while our own system
    // already shows "Delivered". Every entry point wraps this in try/catch
    // and swallows failures — a Shopify API hiccup must never block the
    // courier-side action that's already real and already happened.

    private function get_shopify_api_for_store($store)
    {
        if (!$store || empty($store->shop_domain) || empty($store->access_token)) {
            return null;
        }
        $CI = &get_instance();
        $CI->load->library('shopify_connector/shopify_api', [
            'shop_domain'  => $store->shop_domain,
            'access_token' => $store->access_token,
            'api_version'  => $store->api_version ?: '2024-01',
        ], 'shopify_api_out');
        return $CI->shopify_api_out;
    }

    /**
     * Self-heal: install.php's migration only runs on module
     * (re)activation, which a plain file-copy deploy never triggers, so
     * these may not exist yet on first use.
     */
    private function ensure_fulfillment_schema()
    {
        if (!$this->db->table_exists(db_prefix() . 'shopify_fulfillment_updates')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'shopify_fulfillment_updates` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `shopify_order_id` INT NOT NULL,
                `status` VARCHAR(50) NOT NULL,
                `tracking_number` VARCHAR(100),
                `tracking_url` VARCHAR(500),
                `shopify_response` TEXT,
                `pushed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `success` TINYINT(1) DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $this->db->char_set . ';');
        }
        if (!$this->db->field_exists('shopify_fulfillment_id', db_prefix() . 'shopify_orders')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'shopify_orders` ADD COLUMN `shopify_fulfillment_id` VARCHAR(50) NULL DEFAULT NULL');
        }
    }

    private function log_fulfillment_push($shopify_db_order_id, $status, $tracking_number, $response, $success)
    {
        $this->db->insert(db_prefix() . 'shopify_fulfillment_updates', [
            'shopify_order_id' => $shopify_db_order_id,
            'status'           => $status,
            'tracking_number'  => $tracking_number,
            'tracking_url'     => $tracking_number ? (site_url('courier_goshipping/track') . '?track=' . urlencode($tracking_number)) : site_url('courier_goshipping/track'),
            'shopify_response' => json_encode($response),
            'success'          => $success ? 1 : 0,
        ]);

        // Also mirror into the integration log staff already check under
        // Salibay Fulfilment > Health & Logs — tblshopify_fulfillment_updates
        // has no viewer UI of its own, so a failure logged only there was
        // effectively invisible.
        if ($this->db->table_exists(db_prefix() . 'shopify_integration_logs')) {
            $this->db->insert(db_prefix() . 'shopify_integration_logs', [
                'store_id'   => null,
                'log_level'  => $success ? 'info' : 'error',
                'category'   => 'shipment',
                'message'    => ($success ? 'Shopify fulfillment push succeeded' : 'Shopify fulfillment push FAILED') . " ({$status})",
                'context'    => json_encode([
                    'shopify_db_order_id' => $shopify_db_order_id,
                    'status'              => $status,
                    'tracking_number'     => $tracking_number,
                    'shopify_response'    => $response,
                ]),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Creates the Shopify fulfillment for an order — this is what flips the
     * order from "Unfulfilled" to "Fulfilled" in Shopify and shows the
     * tracking link to the customer. Called on-demand by
     * push_shopify_fulfillment_status() once the shipment is actually
     * delivered, not earlier. Safe to call more than once: no-ops if a
     * fulfillment already exists for this order.
     */
    public function create_shopify_fulfillment($shipment_id)
    {
        $this->ensure_fulfillment_schema();

        $order = $this->db->where('gs_shipment_id', $shipment_id)->get(db_prefix() . 'shopify_orders')->row();
        if (!$order || !empty($order->shopify_fulfillment_id)) {
            return false;
        }

        $shipment = $this->db->select('waybill_number, tracking_id')->where('id', $shipment_id)->get(db_prefix() . '_shipments')->row();
        $tracking_number = $shipment ? ($shipment->waybill_number ?: $shipment->tracking_id) : $order->tracking_number;
        if (!$tracking_number) {
            $this->log_fulfillment_push($order->id, 'create_failed_no_tracking', null, null, false);
            return false;
        }

        $store = $this->get_store();
        $api = $this->get_shopify_api_for_store($store);
        if (!$api) {
            $this->log_fulfillment_push($order->id, 'create_failed_no_store_credentials', $tracking_number, null, false);
            return false;
        }

        $fo_result = $api->get_fulfillment_orders($order->shopify_order_id);
        if (!$fo_result['success'] || empty($fo_result['data']['fulfillment_orders'])) {
            $this->log_fulfillment_push($order->id, 'create_failed_no_fulfillment_orders', $tracking_number, $fo_result, false);
            return false;
        }

        // Fulfill every still-open fulfillment order on the Shopify order
        // (normally just one, for a single-vendor Salibay order).
        $any_success = false;
        $last_response = null;
        foreach ($fo_result['data']['fulfillment_orders'] as $fo) {
            if (($fo['status'] ?? '') === 'closed' || ($fo['status'] ?? '') === 'cancelled') {
                continue;
            }

            $result = $api->create_fulfillment_v2([
                'fulfillment' => [
                    'line_items_by_fulfillment_order' => [
                        ['fulfillment_order_id' => $fo['id']],
                    ],
                    'tracking_info' => [
                        'number'  => $tracking_number,
                        // ?track= auto-fills and auto-submits the tracking
                        // form (see tracking.php's auto-track JS) — so
                        // clicking this link in Shopify lands straight on
                        // the result, no manual copy-paste of the waybill.
                        'url'     => site_url('courier_goshipping/track') . '?track=' . urlencode($tracking_number),
                        'company' => 'Go Shipping Cargo',
                    ],
                    'notify_customer' => true,
                ],
            ]);
            $last_response = $result;

            if ($result['success'] && !empty($result['data']['fulfillment']['id'])) {
                $any_success = true;
                $this->db->where('id', $order->id)->update(db_prefix() . 'shopify_orders', [
                    'shopify_fulfillment_id' => $result['data']['fulfillment']['id'],
                ]);
            }
        }

        $this->log_fulfillment_push($order->id, 'created', $tracking_number, $last_response, $any_success);
        return $any_success;
    }

    /**
     * Pushes a tracking milestone or cancellation onto the Shopify order's
     * fulfillment.
     *
     * Per Salibay's own ops semantics (confirmed by their team): "Fulfilled"
     * means the goods have left the warehouse and are on the way — i.e. our
     * status_id 5/6 (in_transit). So the Shopify fulfillment is created
     * on-demand here the first time status reaches in_transit (not earlier,
     * at rider assignment — Salibay isn't watching a finer-grained "assigned
     * but not moving" state, so there's no point creating it sooner). Once
     * created, the "delivered" event later marks it Delivered too. Anything
     * before in_transit (Created/Picked up/Received/Dispatched) is simply
     * skipped — nothing in Shopify to attach an event to yet.
     */
    public function push_shopify_fulfillment_status($shipment_id, $status_id)
    {
        $this->ensure_fulfillment_schema();

        $order = $this->db->where('gs_shipment_id', $shipment_id)->get(db_prefix() . 'shopify_orders')->row();
        if (!$order) {
            return false;
        }

        if (empty($order->shopify_fulfillment_id)) {
            if ((int) $status_id === 9) {
                // Nothing was ever fulfilled — there's nothing to cancel.
                return false;
            }
            if (!in_array((int) $status_id, [5, 6, 7, 8], true)) {
                // Created / Picked up / Received / Dispatched — too early;
                // no fulfillment worth creating yet.
                return false;
            }
            $this->create_shopify_fulfillment($shipment_id);
            $order = $this->db->where('id', $order->id)->get(db_prefix() . 'shopify_orders')->row();
            if (empty($order->shopify_fulfillment_id)) {
                return false;
            }
        }

        $store = $this->get_store();
        $api = $this->get_shopify_api_for_store($store);
        if (!$api) {
            $this->log_fulfillment_push($order->id, 'event_push_failed_no_store_credentials', $order->tracking_number, null, false);
            return false;
        }

        // Our internal shipment_statuses id -> Shopify's fulfillment event
        // vocabulary. Statuses with no clean Shopify equivalent (Created,
        // Picked up, Received) are simply not pushed as events.
        $event_map = [
            5 => 'in_transit',
            6 => 'in_transit',
            7 => 'out_for_delivery',
            8 => 'delivered',
        ];

        if ((int) $status_id === 9) {
            $result = $api->cancel_fulfillment($order->shopify_fulfillment_id);
            $this->log_fulfillment_push($order->id, 'cancelled', $order->tracking_number, $result, $result['success']);
            return $result['success'];
        }

        if (!isset($event_map[(int) $status_id])) {
            return false;
        }

        $shopify_status = $event_map[(int) $status_id];
        $result = $api->create_fulfillment_event($order->shopify_fulfillment_id, $shopify_status);
        $this->log_fulfillment_push($order->id, $shopify_status, $order->tracking_number, $result, $result['success']);
        return $result['success'];
    }
}
