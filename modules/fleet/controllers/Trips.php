<?php defined('BASEPATH') or exit('No direct script access allowed');

class Trips extends AdminController
{
    // Public, token-authenticated actions — no staff login. A driver opens
    // these from a link on their own phone, so they can't go through the
    // normal admin auth flow.
    private $public_actions = ['driver_gps', 'record_location', 'manifest', 'sw', 'icon', 'driver_start_trip', 'driver_deliver_shipment', 'driver_cancel_shipment'];

    public function __construct()
    {
        $uri = load_class('URI', 'core');
        if (in_array($uri->segment(3), $this->public_actions, true)) {
            App_Controller::__construct();
        } else {
            parent::__construct();
        }

        $this->load->model('fleet/Fleet_trips_model');

        // Self-heal: these are referenced by code below but may not exist yet
        // if this module was deployed by copying files rather than through
        // the official Setup > Modules reactivation flow.
        if (!$this->db->field_exists('tracking_token', db_prefix() . 'fleet_trips')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'fleet_trips` ADD COLUMN `tracking_token` VARCHAR(64) NULL DEFAULT NULL AFTER `id`, ADD UNIQUE KEY `tracking_token` (`tracking_token`)');
        }
        // Same self-heal reasoning as tracking_token above — these belong to
        // the courier_goshipping module's tables but the driver_deliver/cancel
        // actions below (fired from this module's public trip page) need them
        // to exist regardless of whether that module's own install.php has
        // been re-run since this deploy.
        if ($this->db->table_exists(db_prefix() . '_shipments') && !$this->db->field_exists('cancel_reason', db_prefix() . '_shipments')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . '_shipments` ADD COLUMN `cancel_reason` TEXT NULL DEFAULT NULL');
        }
        if ($this->db->table_exists(db_prefix() . '_shipment_status_history') && !$this->db->field_exists('notes', db_prefix() . '_shipment_status_history')) {
            $this->db->query('ALTER TABLE `' . db_prefix() . '_shipment_status_history` ADD COLUMN `notes` TEXT NULL DEFAULT NULL');
        }

        if (!$this->db->table_exists(db_prefix() . 'fleet_trip_locations')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . "fleet_trip_locations` (
                `id`          INT(11)        NOT NULL AUTO_INCREMENT,
                `trip_id`     INT(11)        NOT NULL,
                `latitude`    DECIMAL(10,7)  NOT NULL,
                `longitude`   DECIMAL(10,7)  NOT NULL,
                `accuracy`    DECIMAL(10,2)  NULL DEFAULT NULL,
                `speed`       DECIMAL(10,2)  NULL DEFAULT NULL,
                `recorded_at` DATETIME       NOT NULL,
                PRIMARY KEY (`id`),
                KEY `trip_id` (`trip_id`),
                KEY `trip_recorded` (`trip_id`, `recorded_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $this->db->char_set . ';');
        }
    }

    // ── List ───────────────────────────────────────────────────────────────────
    public function index()
    {
        $data['title']  = 'Trip Bookings';
        $data['trips']  = $this->Fleet_trips_model->get_all();
        $data['counts'] = $this->Fleet_trips_model->get_status_counts();
        $this->load->view('fleet/trips/list', $data);
    }

    // ── Create form ────────────────────────────────────────────────────────────
    public function create($shipment_id = null)
    {
        $data['title']          = 'Book a Trip';
        $data['vehicles']       = $this->Fleet_trips_model->get_vehicles();
        $data['drivers']        = $this->Fleet_trips_model->get_drivers();
        $data['service_points'] = $this->Fleet_trips_model->get_service_points();
        $data['customers']      = $this->Fleet_trips_model->get_customers();
        $data['shipment_id']    = (int)($shipment_id ?: $this->input->get('shipment_id'));
        $data['shipment']       = $data['shipment_id'] ? $this->Fleet_trips_model->get_shipment($data['shipment_id']) : null;
        $data['parent_trip_id'] = (int)$this->input->get('parent');
        $data['parent_trip']    = $data['parent_trip_id'] ? $this->Fleet_trips_model->get($data['parent_trip_id']) : null;
        $this->load->view('fleet/trips/create', $data);
    }

    // ── Save booking (AJAX POST) ───────────────────────────────────────────────
    public function store()
    {
        header('Content-Type: application/json');

        $vehicle_id       = (int)$this->input->post('vehicle_id');
        $driver_id        = (int)$this->input->post('driver_id') ?: null;
        $customer_id      = (int)$this->input->post('customer_id') ?: null;
        $track_type       = in_array($this->input->post('track_type'), ['single','double']) ? $this->input->post('track_type') : 'single';
        $shipment_id      = (int)$this->input->post('shipment_id') ?: null;
        $from_point_id    = (int)$this->input->post('from_point_id') ?: null;
        $to_point_id      = (int)$this->input->post('to_point_id') ?: null;
        $loading_point_id = (int)$this->input->post('loading_point_id') ?: $from_point_id;
        $vehicle_status   = $this->input->post('vehicle_status') ?: 'empty';
        $load_type        = $this->input->post('load_type') ?: 'full';
        $parent_trip_id   = (int)$this->input->post('parent_trip_id') ?: null;
        $trip_date        = $this->input->post('trip_date') ?: date('Y-m-d H:i:s');
        $notes            = $this->input->post('notes');

        if (!$vehicle_id) {
            echo json_encode(['success' => false, 'message' => 'Please select a vehicle.']);
            return;
        }
        if (!$from_point_id || !$to_point_id) {
            echo json_encode(['success' => false, 'message' => 'Please select origin and destination.']);
            return;
        }

        $trip_id = $this->Fleet_trips_model->create([
            'shipment_id'      => $shipment_id,
            'tracking_token'   => bin2hex(random_bytes(24)),
            'trip_date'        => $trip_date,
            'vehicle_id'       => $vehicle_id,
            'driver_id'        => $driver_id,
            'customer_id'      => $customer_id,
            'track_type'       => $track_type,
            'picking_point_id' => $loading_point_id,  // loading point
            'to_point_id'      => $to_point_id,
            'vehicle_status'   => $vehicle_status,
            'load_type'        => $load_type,
            'parent_trip_id'   => $parent_trip_id,
            'status'           => 'booked',
            'notes'            => $notes,
        ]);

        if ($trip_id) {
            // Assigning a driver/vehicle to a shipment means it's now dispatched —
            // advance the linked courier shipment's status to reflect that.
            if ($shipment_id) {
                $this->_advance_shipment_status($shipment_id, 4); // 'dispatched'
            }

            echo json_encode(['success' => true, 'trip_id' => $trip_id,
                'redirect' => admin_url('fleet/trips/detail/' . $trip_id)]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create booking.']);
        }
    }

    /**
     * Advances a linked courier shipment's status_id forward only (never
     * regresses a shipment that's already further along), mirroring what
     * Shipments::update_status() does — so dispatch progress made via Fleet
     * trip actions (booking, starting a trip) shows up consistently on the
     * shipment/waybill and Salibay order views without staff re-entering it.
     */
    private function _advance_shipment_status($shipment_id, $new_status_id)
    {
        $shipments_table = db_prefix() . '_shipments';
        if (!$this->db->table_exists($shipments_table)) {
            return;
        }

        $shipment = $this->db->select('id, status_id')->where('id', (int) $shipment_id)->get($shipments_table)->row();
        if (!$shipment || (int) $shipment->status_id >= $new_status_id) {
            return;
        }

        $this->db->where('id', $shipment_id)->update($shipments_table, ['status_id' => $new_status_id]);
        $this->db->insert(db_prefix() . '_shipment_status_history', [
            'shipment_id' => $shipment_id,
            'status_id'   => $new_status_id,
            'changed_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    // ── Detail ─────────────────────────────────────────────────────────────────
    public function detail($id)
    {
        $trip = $this->Fleet_trips_model->get($id);
        if (!$trip) {
            set_alert('danger', 'Trip not found.');
            redirect(admin_url('fleet/trips'));
        }
        $data['title']          = 'Trip #' . $id;
        $data['trip']           = $trip;
        $data['offloading']     = $this->Fleet_trips_model->get_offloading($id);
        $data['fuel_request']   = $trip->fuel_request_id ? $this->Fleet_trips_model->get_fuel_request($trip->fuel_request_id) : null;
        $data['shipment']       = $trip->shipment_id ? $this->Fleet_trips_model->get_shipment($trip->shipment_id) : null;
        $data['service_points'] = $this->Fleet_trips_model->get_service_points();
        $data['staff']          = $this->Fleet_trips_model->get_staff();
        $data['map_provider']   = get_option('courier_map_provider') ?: 'leaflet';
        $data['google_api_key'] = get_option('google_api_key');
        $data['driver_gps_url'] = !empty($trip->tracking_token)
            ? site_url('admin/fleet/trips/driver_gps/' . $trip->tracking_token)
            : null;
        $this->load->view('fleet/trips/detail', $data);
    }

    // ── Request fuel (AJAX POST) ───────────────────────────────────────────────
    public function request_fuel($trip_id)
    {
        header('Content-Type: application/json');
        $trip = $this->Fleet_trips_model->get($trip_id);
        if (!$trip) { echo json_encode(['success' => false, 'message' => 'Trip not found.']); return; }

        $odometer = (float)$this->input->post('odometer');
        $approved_by    = (int)$this->input->post('approved_by') ?: null;
        $declined_by    = (int)$this->input->post('declined_by') ?: null;
        $checked_by     = (int)$this->input->post('checked_by')  ?: null;
        $decline_reason = $this->input->post('decline_reason');

        $fuel_id  = $this->Fleet_trips_model->add_fuel_request([
            'vehicle_id'     => $trip->vehicle_id,
            'gallons'        => (float)$this->input->post('quantity'),
            'price'          => (float)$this->input->post('cost') ?: 0,
            'fuel_type'      => $this->input->post('fuel_type') ?: ($trip->fuel_type ?: 'diesel'),
            'notes'          => $this->input->post('notes'),
            'trip_type'      => 'pre_trip',
            'odometer'       => $odometer,
            'assignment_id'  => null,
            'approved_by'    => $approved_by,
            'declined_by'    => $declined_by,
            'checked_by'     => $checked_by,
            'decline_reason' => $decline_reason ?: null,
        ]);

        $this->Fleet_trips_model->update($trip_id, [
            'fuel_request_id' => $fuel_id,
            'start_odometer'  => $odometer ?: $trip->start_odometer,
            'status'          => 'fuel_requested',
        ]);

        echo json_encode(['success' => true, 'message' => 'Fuel request submitted.']);
    }

    // ── Start trip (AJAX POST) ─────────────────────────────────────────────────
    public function start_trip($trip_id)
    {
        header('Content-Type: application/json');
        $trip = $this->Fleet_trips_model->get($trip_id);
        if (!$trip) { echo json_encode(['success' => false]); return; }

        $odometer = (float) $this->input->post('odometer');
        $this->_start_trip_core($trip, $odometer, get_staff_user_id());

        echo json_encode(['success' => true]);
    }

    // ── Driver starts their own trip from the public tracker page ─────────────
    public function driver_start_trip()
    {
        header('Content-Type: application/json');

        $token = $this->input->post('token');
        $trip = $token ? $this->db->where('tracking_token', $token)->get(db_prefix() . 'fleet_trips')->row() : null;
        if (!$trip) {
            echo json_encode(['success' => false, 'message' => 'Invalid tracking link.']);
            return;
        }
        if ($trip->status === 'started' || $trip->status === 'offloading' || $trip->status === 'completed') {
            echo json_encode(['success' => true, 'already_started' => true]);
            return;
        }

        $odometer = (float) $this->input->post('odometer');
        if ($odometer <= 0) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid odometer reading.']);
            return;
        }

        $this->_start_trip_core($trip, $odometer, $trip->driver_id);

        echo json_encode(['success' => true]);
    }

    // ── Driver marks the shipment delivered, with the customer's on-screen
    // signature, from the public tracker page (public POST) ───────────────────
    public function driver_deliver_shipment()
    {
        header('Content-Type: application/json');

        $token = $this->input->post('token');
        $trip = $token ? $this->db->where('tracking_token', $token)->get(db_prefix() . 'fleet_trips')->row() : null;
        if (!$trip || !$trip->shipment_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid tracking link.']);
            return;
        }

        $first_name = trim((string) $this->input->post('first_name'));
        $last_name  = trim((string) $this->input->post('last_name'));
        $signature  = $this->input->post('signature');

        if ($first_name === '' || $last_name === '' || empty($signature)) {
            echo json_encode(['success' => false, 'message' => "Please enter the customer's name and have them sign."]);
            return;
        }

        $canvas_data = str_replace(' ', '+', str_replace('data:image/png;base64,', '', $signature));
        $image_data  = base64_decode($canvas_data);
        if ($image_data === false) {
            echo json_encode(['success' => false, 'message' => 'Could not read the signature. Please try again.']);
            return;
        }

        // Same folder the staff-side "Update Status" delivery signature uses
        // (modules/courier_goshipping/controllers/Shipments.php), so both
        // paths land in one place and render through the same views.
        $signatures_dir = FCPATH . 'modules/courier_goshipping/assets/deliveries/signatures/';
        if (!is_dir($signatures_dir)) {
            @mkdir($signatures_dir, 0755, true);
        }
        $file_name = uniqid('delivery_') . '.png';
        if (!file_put_contents($signatures_dir . $file_name, $image_data)) {
            echo json_encode(['success' => false, 'message' => 'Could not save the signature. Please try again.']);
            return;
        }

        $this->load->model('courier_goshipping/Delivery_model');
        $this->Delivery_model->add([
            'shipment_id'   => $trip->shipment_id,
            'first_name'    => $first_name,
            'last_name'     => $last_name,
            'signature_url' => 'assets/deliveries/signatures/' . $file_name,
        ]);

        $this->_advance_shipment_status($trip->shipment_id, 8); // 'delivered'

        if ($this->db->table_exists(db_prefix() . 'shopify_orders')) {
            $this->db->where('gs_shipment_id', (int) $trip->shipment_id)->update(db_prefix() . 'shopify_orders', [
                'order_status' => 'delivered',
            ]);
        }

        // Delivery ends the trip — close it out the same way the staff-side
        // "End Trip" action does, so a delivered shipment doesn't leave an
        // open trip/vehicle assignment behind.
        $this->Fleet_trips_model->update($trip->id, [
            'status'   => 'completed',
            'end_time' => date('Y-m-d H:i:s'),
        ]);
        $this->db->where('courier_shipment_id', $trip->shipment_id)
            ->where('vehicle_id', $trip->vehicle_id)
            ->where('end_time IS NULL', null, false)
            ->update(db_prefix() . 'fleet_vehicle_assignments', ['end_time' => date('Y-m-d H:i:s')]);

        echo json_encode(['success' => true]);
    }

    // ── Driver cancels the shipment with a reason, from the public tracker
    // page (public POST) ────────────────────────────────────────────────────
    public function driver_cancel_shipment()
    {
        header('Content-Type: application/json');

        $token = $this->input->post('token');
        $trip = $token ? $this->db->where('tracking_token', $token)->get(db_prefix() . 'fleet_trips')->row() : null;
        if (!$trip || !$trip->shipment_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid tracking link.']);
            return;
        }

        $reason = trim((string) $this->input->post('reason'));
        if ($reason === '') {
            echo json_encode(['success' => false, 'message' => 'Please enter a reason for cancelling.']);
            return;
        }

        $cancelled_status = $this->db->where('status_name', 'cancelled')->get(db_prefix() . '_shipment_statuses')->row();
        $cancelled_status_id = $cancelled_status ? (int) $cancelled_status->id : 9;

        $this->db->where('id', (int) $trip->shipment_id)->update(db_prefix() . '_shipments', [
            'status_id'     => $cancelled_status_id,
            'cancel_reason' => $reason,
        ]);
        $this->db->insert(db_prefix() . '_shipment_status_history', [
            'shipment_id' => $trip->shipment_id,
            'status_id'   => $cancelled_status_id,
            'notes'       => $reason,
            'changed_at'  => date('Y-m-d H:i:s'),
        ]);

        if ($this->db->table_exists(db_prefix() . 'shopify_orders')) {
            $this->db->where('gs_shipment_id', (int) $trip->shipment_id)->update(db_prefix() . 'shopify_orders', [
                'order_status' => 'cancelled',
            ]);
        }

        $this->Fleet_trips_model->update($trip->id, ['status' => 'cancelled']);
        $this->db->where('courier_shipment_id', $trip->shipment_id)
            ->where('vehicle_id', $trip->vehicle_id)
            ->where('end_time IS NULL', null, false)
            ->update(db_prefix() . 'fleet_vehicle_assignments', ['end_time' => date('Y-m-d H:i:s')]);

        echo json_encode(['success' => true]);
    }

    /**
     * Shared by both the staff-side "Start Trip" button (Trip detail page)
     * and the driver's own "Start Trip" button on their public tracker page —
     * same effect either way: marks the trip started, opens a vehicle
     * assignment record, and advances the linked shipment to 'in_transit'.
     */
    private function _start_trip_core($trip, $odometer, $actor_staff_id)
    {
        $this->Fleet_trips_model->update($trip->id, [
            'status'         => 'started',
            'start_time'     => date('Y-m-d H:i:s'),
            'start_odometer' => $odometer ?: $trip->start_odometer,
        ]);

        $this->db->insert(db_prefix() . 'fleet_vehicle_assignments', [
            'driver_id'           => $trip->driver_id ?: $actor_staff_id,
            'vehicle_id'          => $trip->vehicle_id,
            'start_time'          => date('Y-m-d H:i:s'),
            'starting_odometer'   => $odometer,
            'addedfrom'           => $actor_staff_id,
            'courier_shipment_id' => $trip->shipment_id,
            'trip_notes'          => 'Trip #' . $trip->id . ($trip->track_type === 'double' ? ' (Double Track)' : ''),
        ]);

        if ($trip->shipment_id) {
            $this->_advance_shipment_status($trip->shipment_id, 5); // 'in_transit'
        }
    }

    // ── Offload (AJAX POST) ────────────────────────────────────────────────────
    public function offload($trip_id)
    {
        header('Content-Type: application/json');
        $trip = $this->Fleet_trips_model->get($trip_id);
        if (!$trip) { echo json_encode(['success' => false]); return; }

        $offload_type    = $this->input->post('offload_type');   // full / partial / parts
        $service_point_id = (int)$this->input->post('service_point_id') ?: null;
        $packages        = $this->input->post('packages_offloaded');
        $notes           = $this->input->post('notes');
        $odometer        = (float)$this->input->post('odometer');

        $this->Fleet_trips_model->add_offloading([
            'trip_id'            => $trip_id,
            'service_point_id'   => $service_point_id,
            'offload_type'       => $offload_type ?: 'full',
            'packages_offloaded' => is_array($packages) ? json_encode($packages) : ($packages ?: null),
            'notes'              => $notes,
            'odometer'           => $odometer ?: null,
        ]);

        // Full offload → truck empty → awaiting end-trip
        // Partial/top-up → trip continues (stays 'started')
        $new_status = ($offload_type === 'full') ? 'offloading' : 'started';
        $this->Fleet_trips_model->update($trip_id, ['status' => $new_status]);

        echo json_encode([
            'success'      => true,
            'offload_type' => $offload_type,
            'message'      => $offload_type === 'full'
                ? 'Full offload recorded. You can now end the trip.'
                : 'Partial offload / top-up recorded. Trip continues.',
        ]);
    }

    // ── End trip (AJAX POST) ───────────────────────────────────────────────────
    public function end_trip($trip_id)
    {
        header('Content-Type: application/json');
        $trip = $this->Fleet_trips_model->get($trip_id);
        if (!$trip) { echo json_encode(['success' => false]); return; }

        $end_odometer = (float)$this->input->post('end_odometer');

        $this->Fleet_trips_model->update($trip_id, [
            'status'       => 'completed',
            'end_time'     => date('Y-m-d H:i:s'),
            'end_odometer' => $end_odometer ?: null,
        ]);

        // Close the fleet vehicle assignment
        if ($trip->shipment_id) {
            $this->db->where('courier_shipment_id', $trip->shipment_id)
                     ->where('vehicle_id', $trip->vehicle_id)
                     ->where('end_time IS NULL', null, false)
                     ->update(db_prefix() . 'fleet_vehicle_assignments', [
                         'end_time'        => date('Y-m-d H:i:s'),
                         'ending_odometer' => $end_odometer ?: null,
                     ]);
        }

        echo json_encode(['success' => true, 'message' => 'Trip ended successfully.']);
    }

    // ── Driver GPS page (public, token-authenticated, no staff login) ─────────
    public function driver_gps($token)
    {
        $trip = $this->db->where('tracking_token', $token)->get(db_prefix() . 'fleet_trips')->row();
        if (!$trip) {
            echo 'Invalid or expired tracking link.';
            return;
        }

        $data['title'] = 'Live Trip Tracking';
        $data['trip']  = $trip;
        $data['token'] = $token;
        $this->load->view('fleet/trips/driver_gps', $data);
    }

    // ── Receive a GPS ping from the driver page (public POST) ─────────────────
    public function record_location()
    {
        header('Content-Type: application/json');

        $token = $this->input->post('token');
        $trip = $token ? $this->db->where('tracking_token', $token)->get(db_prefix() . 'fleet_trips')->row() : null;
        if (!$trip) {
            echo json_encode(['success' => false, 'message' => 'Invalid tracking link.']);
            return;
        }

        $lat = (float) $this->input->post('lat');
        $lng = (float) $this->input->post('lng');
        if ($lat === 0.0 && $lng === 0.0) {
            echo json_encode(['success' => false, 'message' => 'Missing coordinates.']);
            return;
        }

        $this->db->insert(db_prefix() . 'fleet_trip_locations', [
            'trip_id'     => $trip->id,
            'latitude'    => $lat,
            'longitude'   => $lng,
            'accuracy'    => (float) $this->input->post('accuracy') ?: null,
            'speed'       => is_numeric($this->input->post('speed')) ? (float) $this->input->post('speed') : null,
            'recorded_at' => date('Y-m-d H:i:s'),
        ]);

        echo json_encode(['success' => true]);
    }

    // ── Latest location for the staff-facing live map (AJAX GET) ──────────────
    public function latest_location($trip_id)
    {
        header('Content-Type: application/json');

        $latest = $this->db->where('trip_id', (int) $trip_id)
            ->order_by('recorded_at', 'DESC')
            ->limit(1)
            ->get(db_prefix() . 'fleet_trip_locations')
            ->row();

        if (!$latest) {
            echo json_encode(['success' => false, 'message' => 'No location reported yet.']);
            return;
        }

        // A short recent trail so the map can draw the path travelled, not just a dot.
        $trail = $this->db->select('latitude, longitude, recorded_at')
            ->where('trip_id', (int) $trip_id)
            ->order_by('recorded_at', 'DESC')
            ->limit(50)
            ->get(db_prefix() . 'fleet_trip_locations')
            ->result();

        echo json_encode([
            'success' => true,
            'latitude' => (float) $latest->latitude,
            'longitude' => (float) $latest->longitude,
            'speed' => $latest->speed !== null ? (float) $latest->speed : null,
            'recorded_at' => $latest->recorded_at,
            'trail' => array_reverse($trail),
        ]);
    }

    // ── PWA manifest for the driver page (public, per-token so start_url
    // reopens straight into this trip's tracker when launched from the
    // home-screen icon) ────────────────────────────────────────────────────
    public function manifest($token)
    {
        header('Content-Type: application/manifest+json');

        $start_url = site_url('admin/fleet/trips/driver_gps/' . $token);

        echo json_encode([
            'name'             => 'Trip Tracker',
            'short_name'       => 'Trip Tracker',
            'start_url'        => $start_url,
            'scope'            => site_url('admin/fleet/trips/driver_gps/'),
            'display'          => 'standalone',
            'orientation'      => 'portrait',
            'background_color' => '#0d1b2a',
            'theme_color'      => '#0d1b2a',
            'icons'            => [
                ['src' => site_url('admin/fleet/trips/icon/192'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
                ['src' => site_url('admin/fleet/trips/icon/512'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ],
        ]);
    }

    // ── Service worker (public, fixed path so its default scope covers
    // every driver_gps/{token} URL beneath admin/fleet/trips/) ────────────
    public function sw()
    {
        header('Content-Type: application/javascript');
        header('Service-Worker-Allowed: /');

        $cache_name = 'trip-tracker-v1';
        echo <<<JS
const CACHE_NAME = '{$cache_name}';

self.addEventListener('install', function (event) {
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (keys) {
            return Promise.all(keys.filter(function (k) { return k !== CACHE_NAME; }).map(function (k) { return caches.delete(k); }));
        }).then(function () { return self.clients.claim(); })
    );
});

// App-shell caching: the driver page itself is cached on first load so it
// still opens (from the home-screen icon) even with a momentarily flaky
// connection. GPS-ping POST requests are never cached — those go straight
// to the network, since a queued location is only useful sent live.
self.addEventListener('fetch', function (event) {
    if (event.request.method !== 'GET') {
        return;
    }
    event.respondWith(
        caches.match(event.request).then(function (cached) {
            var fetchPromise = fetch(event.request).then(function (response) {
                if (response && response.status === 200) {
                    var clone = response.clone();
                    caches.open(CACHE_NAME).then(function (cache) { cache.put(event.request, clone); });
                }
                return response;
            }).catch(function () { return cached; });
            return cached || fetchPromise;
        })
    );
});
JS;
    }

    // ── Simple generated map-pin icon (public) — avoids shipping binary
    // asset files just for PWA install-ability; drawn on the fly with GD. ──
    public function icon($size = 192)
    {
        $size = (int) $size;
        if (!in_array($size, [192, 512], true)) {
            $size = 192;
        }

        if (!function_exists('imagecreatetruecolor')) {
            // GD isn't available on this PHP build — a 1x1 transparent GIF is
            // valid with no GD dependency at all, unlike a "broken image" PNG.
            header('Content-Type: image/gif');
            echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBTAA7');
            return;
        }

        header('Content-Type: image/png');

        $img = imagecreatetruecolor($size, $size);
        imagesavealpha($img, true);
        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $transparent);

        $bg = imagecolorallocate($img, 13, 27, 42); // matches the driver page background
        imagefilledellipse($img, (int) ($size / 2), (int) ($size / 2), $size, $size, $bg);

        $white = imagecolorallocate($img, 255, 255, 255);
        $cx = $size / 2;
        $pin_top = $size * 0.22;
        $pin_r = $size * 0.20;

        imagefilledellipse($img, (int) $cx, (int) ($pin_top + $pin_r), (int) ($pin_r * 2), (int) ($pin_r * 2), $white);

        $tail = [
            (int) ($cx - $pin_r * 0.75), (int) ($pin_top + $pin_r * 1.4),
            (int) ($cx + $pin_r * 0.75), (int) ($pin_top + $pin_r * 1.4),
            (int) $cx,                   (int) ($size * 0.82),
        ];
        imagefilledpolygon($img, $tail, 3, $white);

        $hole_r = $pin_r * 0.45;
        imagefilledellipse($img, (int) $cx, (int) ($pin_top + $pin_r), (int) ($hole_r * 2), (int) ($hole_r * 2), $bg);

        imagepng($img);
        imagedestroy($img);
    }

    // ── Cancel (AJAX POST) ─────────────────────────────────────────────────────
    public function cancel($trip_id)
    {
        header('Content-Type: application/json');
        $this->Fleet_trips_model->update($trip_id, ['status' => 'cancelled']);
        echo json_encode(['success' => true]);
    }

    // ── Delete ─────────────────────────────────────────────────────────────────
    public function delete_trip($trip_id)
    {
        $this->Fleet_trips_model->delete($trip_id);
        set_alert('success', 'Trip deleted.');
        redirect(admin_url('fleet/trips'));
    }

    // ── Book return trip ───────────────────────────────────────────────────────
    public function book_return($parent_id)
    {
        $parent = $this->Fleet_trips_model->get($parent_id);
        if (!$parent) { show_404(); }
        redirect(admin_url('fleet/trips/create?parent=' . $parent_id));
    }
}
