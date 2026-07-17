<?php defined('BASEPATH') or exit('No direct script access allowed');

class Trips extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('fleet/Fleet_trips_model');
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

        $odometer = (float)$this->input->post('odometer');

        $this->Fleet_trips_model->update($trip_id, [
            'status'         => 'started',
            'start_time'     => date('Y-m-d H:i:s'),
            'start_odometer' => $odometer ?: $trip->start_odometer,
        ]);

        $this->db->insert(db_prefix() . 'fleet_vehicle_assignments', [
            'driver_id'           => $trip->driver_id ?: get_staff_user_id(),
            'vehicle_id'          => $trip->vehicle_id,
            'start_time'          => date('Y-m-d H:i:s'),
            'starting_odometer'   => $odometer,
            'addedfrom'           => get_staff_user_id(),
            'courier_shipment_id' => $trip->shipment_id,
            'trip_notes'          => 'Trip #' . $trip_id . ($trip->track_type === 'double' ? ' (Double Track)' : ''),
        ]);

        echo json_encode(['success' => true]);
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
