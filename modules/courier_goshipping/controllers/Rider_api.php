<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * JSON API behind the Rider PWA (Rider_app.php / views/rider_app/*). Fully
 * public — auth is a bearer-style opaque token (tbl_courier_rider_tokens),
 * accepted as a `token` GET/POST field rather than an Authorization header
 * to keep every call a plain fetch() with no extra header wiring, matching
 * the token-in-body convention the fleet driver_gps page already uses.
 */
class Rider_api extends App_Controller
{
    private $rider = null;

    public function __construct()
    {
        parent::__construct();
        header('Content-Type: application/json');
        $this->load->model('Rider_model');
        $this->load->helper('courier_goshipping/courier');

        if ($this->db->table_exists(db_prefix() . '_shipments') && !$this->db->field_exists('cancel_reason', db_prefix() . '_shipments')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . '_shipments` ADD COLUMN `cancel_reason` TEXT NULL DEFAULT NULL');
        }
        if ($this->db->table_exists(db_prefix() . '_shipment_status_history') && !$this->db->field_exists('notes', db_prefix() . '_shipment_status_history')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . '_shipment_status_history` ADD COLUMN `notes` TEXT NULL DEFAULT NULL');
        }

        // Self-heal: this module's install.php migration only actually runs
        // when the module is (re)activated via Setup > Modules — a plain
        // file-copy deploy (like this app's cron-based sync) never triggers
        // that, so these tables may not exist yet on first use.
        if (!$this->db->table_exists(db_prefix() . '_courier_riders')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . '_courier_riders` (
                `id`            INT NOT NULL AUTO_INCREMENT,
                `name`          VARCHAR(255) NOT NULL,
                `phone`         VARCHAR(30) NOT NULL,
                `password_hash` VARCHAR(255) NOT NULL,
                `staff_id`      INT NULL DEFAULT NULL,
                `status`        ENUM(\'active\',\'suspended\') NOT NULL DEFAULT \'active\',
                `created_at`    DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `phone` (`phone`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $this->db->char_set . ';');
        }
        if (!$this->db->table_exists(db_prefix() . '_courier_rider_tokens')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . '_courier_rider_tokens` (
                `id`           INT NOT NULL AUTO_INCREMENT,
                `rider_id`     INT NOT NULL,
                `token_hash`   VARCHAR(64) NOT NULL,
                `created_at`   DATETIME NOT NULL,
                `last_used_at` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `token_hash` (`token_hash`),
                FOREIGN KEY (`rider_id`) REFERENCES `' . db_prefix() . '_courier_riders`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $this->db->char_set . ';');
        }
        if (!$this->db->table_exists(db_prefix() . '_shipment_locations')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . '_shipment_locations` (
                `id`          INT NOT NULL AUTO_INCREMENT,
                `shipment_id` INT NOT NULL,
                `latitude`    DECIMAL(10,7) NOT NULL,
                `longitude`   DECIMAL(10,7) NOT NULL,
                `accuracy`    DECIMAL(10,2) NULL DEFAULT NULL,
                `speed`       DECIMAL(10,2) NULL DEFAULT NULL,
                `recorded_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `shipment_id` (`shipment_id`),
                KEY `shipment_recorded` (`shipment_id`, `recorded_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $this->db->char_set . ';');
        }
    }

    private function respond($data)
    {
        echo json_encode($data);
    }

    private function fail($message, $code = 400)
    {
        http_response_code($code);
        $this->respond(['success' => false, 'message' => $message]);
    }

    /** Loads $this->rider from the request token, or fails the request and returns false. */
    private function require_rider()
    {
        $token = $this->input->get_post('token');
        $rider = $this->Rider_model->authenticate_token($token);
        if (!$rider) {
            $this->fail('Your session has expired. Please log in again.', 401);
            return false;
        }
        $this->rider = $rider;
        return true;
    }

    private function rider_public($rider)
    {
        return [
            'id'     => (int) $rider->id,
            'name'   => $rider->name,
            'phone'  => $rider->phone,
            'linked' => !empty($rider->staff_id),
        ];
    }

    // ── Auth ────────────────────────────────────────────────────────────────
    public function register()
    {
        $result = $this->Rider_model->register(
            $this->input->post('name'),
            $this->input->post('phone'),
            $this->input->post('password')
        );
        if (!$result['success']) {
            $this->fail($result['message']);
            return;
        }

        $token = $this->Rider_model->issue_token($result['rider']->id);
        $this->respond(['success' => true, 'token' => $token, 'rider' => $this->rider_public($result['rider'])]);
    }

    public function login()
    {
        $result = $this->Rider_model->login($this->input->post('phone'), $this->input->post('password'));
        if (!$result['success']) {
            $this->fail($result['message'], 401);
            return;
        }

        $token = $this->Rider_model->issue_token($result['rider']->id);
        $this->respond(['success' => true, 'token' => $token, 'rider' => $this->rider_public($result['rider'])]);
    }

    public function logout()
    {
        $this->Rider_model->revoke_token($this->input->post('token'));
        $this->respond(['success' => true]);
    }

    public function me()
    {
        if (!$this->require_rider()) {
            return;
        }
        $this->respond(['success' => true, 'rider' => $this->rider_public($this->rider)]);
    }

    // ── Deliveries (shipments assigned to this rider — Salibay and any
    // other shipment an admin assigned directly via "Assign Agent/Staff") ──
    public function deliveries()
    {
        if (!$this->require_rider()) {
            return;
        }

        if (empty($this->rider->staff_id)) {
            $this->respond(['success' => true, 'linked' => false, 'deliveries' => [], 'trips' => []]);
            return;
        }

        $staff_id = (int) $this->rider->staff_id;

        $rows = $this->db->select('s.id, s.waybill_number, s.tracking_id, s.status_id, ss.status_name, ' .
                ($this->db->field_exists('status_description', db_prefix() . '_shipment_statuses') ? 'ss.status_description' : 'ss.description') . ' AS status_description, ' .
                'r.first_name AS recipient_first_name, r.last_name AS recipient_last_name, r.phone_number AS recipient_phone, r.address AS recipient_address')
            ->from(db_prefix() . '_shipments s')
            ->join(db_prefix() . '_shipment_statuses ss', 'ss.id = s.status_id', 'left')
            ->join(db_prefix() . '_shipment_recipients r', 'r.id = s.recipient_id', 'left')
            ->where('s.staff_id', $staff_id)
            ->where_not_in('s.status_id', [8, 9])
            ->order_by('s.created_at', 'asc')
            ->get()
            ->result();

        $deliveries = [];
        foreach ($rows as $row) {
            $order = $this->db->table_exists(db_prefix() . 'shopify_orders')
                ? $this->db->where('gs_shipment_id', $row->id)->get(db_prefix() . 'shopify_orders')->row()
                : null;

            $items_summary = '-';
            if ($order && $this->db->table_exists(db_prefix() . 'shopify_order_items')) {
                $items = $this->db->select("GROUP_CONCAT(CONCAT(COALESCE(NULLIF(product_name, ''), 'Product'), ' (x', quantity, ')') SEPARATOR ', ') AS items_summary")
                    ->where('shopify_order_id', $order->id)
                    ->get(db_prefix() . 'shopify_order_items')
                    ->row();
                $items_summary = $items && $items->items_summary ? $items->items_summary : '-';
            }

            $deliveries[] = [
                'id'                 => (int) $row->id,
                'waybill_number'     => $row->waybill_number ?: $row->tracking_id,
                'status_id'          => (int) $row->status_id,
                'status_text'        => $row->status_description ?: $row->status_name,
                'is_salibay'         => (bool) $order,
                'items_summary'      => $items_summary,
                'recipient_name'     => trim((string) $row->recipient_first_name . ' ' . (string) $row->recipient_last_name),
                'recipient_phone'    => $row->recipient_phone,
                'recipient_address'  => $row->recipient_address,
            ];
        }

        // Fleet trips (real courier freight, own vehicles) still run through
        // the full trip page — surfaced here just as a pointer into it,
        // rather than re-implementing odometer/fuel/offload in this app.
        $trips = [];
        if ($this->db->table_exists(db_prefix() . 'fleet_trips')) {
            $trip_rows = $this->db->select('id, tracking_token, status, shipment_id')
                ->where('driver_id', $staff_id)
                ->where_not_in('status', ['completed', 'cancelled'])
                ->order_by('id', 'asc')
                ->get(db_prefix() . 'fleet_trips')
                ->result();
            foreach ($trip_rows as $trip) {
                $trips[] = [
                    'id'     => (int) $trip->id,
                    'status' => $trip->status,
                    'url'    => !empty($trip->tracking_token) ? site_url('admin/fleet/trips/driver_gps/' . $trip->tracking_token) : null,
                ];
            }
        }

        $this->respond(['success' => true, 'linked' => true, 'deliveries' => $deliveries, 'trips' => $trips]);
    }

    private function owned_shipment($shipment_id)
    {
        $shipment = $this->db->where('id', (int) $shipment_id)->get(db_prefix() . '_shipments')->row();
        if (!$shipment || empty($this->rider->staff_id) || (int) $shipment->staff_id !== (int) $this->rider->staff_id) {
            return null;
        }
        return $shipment;
    }

    private function advance_shipment_status($shipment_id, $new_status_id)
    {
        $shipment = $this->db->select('id, status_id')->where('id', $shipment_id)->get(db_prefix() . '_shipments')->row();
        if (!$shipment || (int) $shipment->status_id >= $new_status_id) {
            return;
        }
        $this->db->where('id', $shipment_id)->update(db_prefix() . '_shipments', ['status_id' => $new_status_id]);
        $this->db->insert(db_prefix() . '_shipment_status_history', [
            'shipment_id' => $shipment_id,
            'status_id'   => $new_status_id,
            'changed_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    private function mirror_salibay_order_status($shipment_id, $order_status)
    {
        if ($this->db->table_exists(db_prefix() . 'shopify_orders')) {
            $this->db->where('gs_shipment_id', $shipment_id)->update(db_prefix() . 'shopify_orders', ['order_status' => $order_status]);
        }
    }

    /**
     * Pushes the real Shopify fulfillment/tracking update, not just our own
     * local order_status mirror — must never block the rider's action if
     * Shopify's API hiccups.
     */
    private function push_shopify_status($shipment_id, $status_id)
    {
        try {
            $this->load->model('shopify_connector/shopify_connector_model');
            $this->shopify_connector_model->push_shopify_fulfillment_status($shipment_id, $status_id);
        } catch (\Throwable $e) {
            log_message('error', 'Shopify fulfillment push crashed: ' . $e->getMessage());
        }
    }

    public function delivery_start($id)
    {
        if (!$this->require_rider()) {
            return;
        }
        $shipment = $this->owned_shipment($id);
        if (!$shipment) {
            $this->fail('Delivery not found.', 404);
            return;
        }

        $this->advance_shipment_status($shipment->id, 5); // in_transit
        $this->mirror_salibay_order_status($shipment->id, 'processing');
        $this->push_shopify_status($shipment->id, 5);
        $this->respond(['success' => true]);
    }

    // ── Receive a GPS ping from the rider app while a delivery is in
    // progress (public POST, token-authenticated + ownership-checked) ──────
    public function location_ping($id)
    {
        if (!$this->require_rider()) {
            return;
        }
        $shipment = $this->owned_shipment($id);
        if (!$shipment) {
            $this->fail('Delivery not found.', 404);
            return;
        }

        $lat = (float) $this->input->post('lat');
        $lng = (float) $this->input->post('lng');
        if ($lat === 0.0 && $lng === 0.0) {
            $this->fail('Missing coordinates.');
            return;
        }

        $this->db->insert(db_prefix() . '_shipment_locations', [
            'shipment_id' => $shipment->id,
            'latitude'    => $lat,
            'longitude'   => $lng,
            'accuracy'    => (float) $this->input->post('accuracy') ?: null,
            'speed'       => is_numeric($this->input->post('speed')) ? (float) $this->input->post('speed') : null,
            'recorded_at' => date('Y-m-d H:i:s'),
        ]);

        $this->respond(['success' => true]);
    }

    public function delivery_deliver($id)
    {
        if (!$this->require_rider()) {
            return;
        }
        $shipment = $this->owned_shipment($id);
        if (!$shipment) {
            $this->fail('Delivery not found.', 404);
            return;
        }

        $first_name = trim((string) $this->input->post('first_name'));
        $last_name  = trim((string) $this->input->post('last_name'));
        $signature  = $this->input->post('signature');
        if ($first_name === '' || $last_name === '' || empty($signature)) {
            $this->fail("Please enter the customer's name and have them sign.");
            return;
        }

        $canvas_data = str_replace(' ', '+', str_replace('data:image/png;base64,', '', $signature));
        $image_data  = base64_decode($canvas_data);
        if ($image_data === false) {
            $this->fail('Could not read the signature. Please try again.');
            return;
        }

        $signatures_dir = FCPATH . 'modules/courier_goshipping/assets/deliveries/signatures/';
        if (!is_dir($signatures_dir)) {
            @mkdir($signatures_dir, 0755, true);
        }
        $file_name = uniqid('rider_delivery_') . '.png';
        if (!file_put_contents($signatures_dir . $file_name, $image_data)) {
            $this->fail('Could not save the signature. Please try again.');
            return;
        }

        courier_load_model('Delivery_model');
        $this->Delivery_model->add([
            'shipment_id'   => $shipment->id,
            'first_name'    => $first_name,
            'last_name'     => $last_name,
            'signature_url' => 'assets/deliveries/signatures/' . $file_name,
        ]);

        $this->advance_shipment_status($shipment->id, 8); // delivered
        $this->mirror_salibay_order_status($shipment->id, 'delivered');
        $this->push_shopify_status($shipment->id, 8);
        $this->respond(['success' => true]);
    }

    public function delivery_cancel($id)
    {
        if (!$this->require_rider()) {
            return;
        }
        $shipment = $this->owned_shipment($id);
        if (!$shipment) {
            $this->fail('Delivery not found.', 404);
            return;
        }

        $reason = trim((string) $this->input->post('reason'));
        if ($reason === '') {
            $this->fail('Please enter a reason for cancelling.');
            return;
        }

        $cancelled_status    = $this->db->where('status_name', 'cancelled')->get(db_prefix() . '_shipment_statuses')->row();
        $cancelled_status_id = $cancelled_status ? (int) $cancelled_status->id : 9;

        $this->db->where('id', $shipment->id)->update(db_prefix() . '_shipments', [
            'status_id'     => $cancelled_status_id,
            'cancel_reason' => $reason,
        ]);
        $this->db->insert(db_prefix() . '_shipment_status_history', [
            'shipment_id' => $shipment->id,
            'status_id'   => $cancelled_status_id,
            'notes'       => $reason,
            'changed_at'  => date('Y-m-d H:i:s'),
        ]);
        $this->mirror_salibay_order_status($shipment->id, 'cancelled');
        $this->push_shopify_status($shipment->id, $cancelled_status_id);
        $this->respond(['success' => true]);
    }

    // ── Pickups (collections from the sender/warehouse — driver_id on
    // tbl_pickups already matches this rider's linked staff_id) ────────────
    public function pickups()
    {
        if (!$this->require_rider()) {
            return;
        }
        if (empty($this->rider->staff_id)) {
            $this->respond(['success' => true, 'linked' => false, 'pickups' => []]);
            return;
        }

        $rows = $this->db->select('p.*, c.first_name AS contact_first_name, c.last_name AS contact_last_name, c.phone_number AS contact_phone_number')
            ->from(db_prefix() . '_pickups p')
            ->join(db_prefix() . '_pickup_contacts c', 'p.contact_person_id = c.id', 'left')
            ->where('p.driver_id', (int) $this->rider->staff_id)
            ->where('p.status !=', 'delivered')
            ->order_by('p.pickup_date', 'asc')
            ->get()
            ->result();

        $pickups = [];
        foreach ($rows as $row) {
            $pickups[] = [
                'id'            => (int) $row->id,
                'status'        => $row->status,
                'pickup_date'   => $row->pickup_date,
                'address'       => $row->address,
                'contact_name'  => trim((string) $row->contact_first_name . ' ' . (string) $row->contact_last_name),
                'contact_phone' => $row->contact_phone_number,
                'shipment_id'   => $row->shipment_id,
            ];
        }

        $this->respond(['success' => true, 'linked' => true, 'pickups' => $pickups]);
    }

    public function pickup_update($id)
    {
        if (!$this->require_rider()) {
            return;
        }

        $pickup = $this->db->where('id', (int) $id)->get(db_prefix() . '_pickups')->row_array();
        if (!$pickup || empty($this->rider->staff_id) || (int) $pickup['driver_id'] !== (int) $this->rider->staff_id) {
            $this->fail('Pickup not found.', 404);
            return;
        }

        $status    = $this->input->post('status'); // picked_up | delivered
        $signature = $this->input->post('signature');
        if (!in_array($status, ['picked_up', 'delivered'], true) || empty($signature)) {
            $this->fail('Please capture a signature before confirming.');
            return;
        }

        $canvas_data = str_replace(' ', '+', str_replace('data:image/png;base64,', '', $signature));
        $image_data  = base64_decode($canvas_data);
        if ($image_data === false) {
            $this->fail('Could not read the signature. Please try again.');
            return;
        }

        $signatures_dir = FCPATH . 'modules/courier_goshipping/assets/pickups/signatures/';
        if (!is_dir($signatures_dir)) {
            @mkdir($signatures_dir, 0755, true);
        }
        $file_name = uniqid('rider_pickup_') . '.png';
        if (!file_put_contents($signatures_dir . $file_name, $image_data)) {
            $this->fail('Could not save the signature. Please try again.');
            return;
        }

        $update = ['status' => $status];
        $update[$status === 'picked_up' ? 'signature_url' : 'delivery_signature_url'] = 'assets/pickups/signatures/' . $file_name;
        $this->db->where('id', $id)->update(db_prefix() . '_pickups', $update);

        if (!empty($pickup['shipment_id'])) {
            // picked_up = collected from sender, delivered = handed to the
            // warehouse for processing — matches Pickups::update_status().
            $this->advance_shipment_status($pickup['shipment_id'], $status === 'picked_up' ? 2 : 3);
        }

        $this->respond(['success' => true]);
    }
}
