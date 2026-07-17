<?php defined('BASEPATH') or exit('No direct script access allowed');

class Fleet_trips_model extends CI_Model
{
    public function get_all($filters = [])
    {
        $this->db->select('t.*, v.name as vehicle_name, v.license_plate,
            fp.name as from_point_name,
            tp.name as to_point_name,
            s.waybill_number,
            c.company as customer_name,
            CONCAT(dr.firstname," ",dr.lastname) AS driver_name', false);
        $this->db->from(db_prefix() . 'fleet_trips t');
        $this->db->join(db_prefix() . 'fleet_vehicles v',           'v.id = t.vehicle_id',           'left');
        $this->db->join(db_prefix() . '_courier_service_points fp', 'fp.id = t.picking_point_id',    'left');
        $this->db->join(db_prefix() . '_courier_service_points tp', 'tp.id = t.to_point_id',         'left');
        $this->db->join(db_prefix() . '_shipments s',               's.id = t.shipment_id',           'left');
        $this->db->join(db_prefix() . 'clients c',                  'c.userid = t.customer_id',       'left');
        $this->db->join(db_prefix() . 'staff dr',                   'dr.staffid = t.driver_id',       'left');
        if (!empty($filters['status']))     { $this->db->where('t.status',     $filters['status']); }
        if (!empty($filters['vehicle_id'])) { $this->db->where('t.vehicle_id', $filters['vehicle_id']); }
        if (!empty($filters['driver_id']))  { $this->db->where('t.driver_id',  $filters['driver_id']); }
        if (!empty($filters['shipment_id'])) { $this->db->where('t.shipment_id',  $filters['shipment_id']); }
        $this->db->order_by('t.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get($id)
    {
        $this->db->select('t.*, v.name as vehicle_name, v.license_plate, v.fuel_type,
            v.odometer as vehicle_odometer,
            fp.name as from_point_name,
            tp.name as to_point_name,
            s.waybill_number, s.tracking_id, s.shipping_mode,
            c.company as customer_name,
            CONCAT(dr.firstname," ",dr.lastname) AS driver_name,
            fh.gallons as fuel_gallons, fh.fuel_type as fuel_type_requested,
            fh.price as fuel_cost, fh.notes as fuel_notes', false);
        $this->db->from(db_prefix() . 'fleet_trips t');
        $this->db->join(db_prefix() . 'fleet_vehicles v',           'v.id = t.vehicle_id',           'left');
        $this->db->join(db_prefix() . '_courier_service_points fp', 'fp.id = t.picking_point_id',    'left');
        $this->db->join(db_prefix() . '_courier_service_points tp', 'tp.id = t.to_point_id',         'left');
        $this->db->join(db_prefix() . '_shipments s',               's.id = t.shipment_id',           'left');
        $this->db->join(db_prefix() . 'clients c',                  'c.userid = t.customer_id',       'left');
        $this->db->join(db_prefix() . 'staff dr',                   'dr.staffid = t.driver_id',       'left');
        $this->db->join(db_prefix() . 'fleet_fuel_history fh',      'fh.id = t.fuel_request_id',      'left');
        $this->db->where('t.id', (int)$id);
        return $this->db->get()->row();
    }

    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();
        $this->db->insert(db_prefix() . 'fleet_trips', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', (int)$id);
        return $this->db->update(db_prefix() . 'fleet_trips', $data);
    }

    public function delete($id)
    {
        $this->db->where('trip_id', (int)$id);
        $this->db->delete(db_prefix() . 'fleet_trip_offloading');
        $this->db->where('id', (int)$id);
        return $this->db->delete(db_prefix() . 'fleet_trips');
    }

    public function get_offloading($trip_id)
    {
        $this->db->select('o.*, sp.name as point_name', false);
        $this->db->from(db_prefix() . 'fleet_trip_offloading o');
        $this->db->join(db_prefix() . '_courier_service_points sp', 'sp.id = o.service_point_id', 'left');
        $this->db->where('o.trip_id', (int)$trip_id);
        $this->db->order_by('o.recorded_at', 'ASC');
        return $this->db->get()->result();
    }

    public function add_offloading($data)
    {
        $data['recorded_at'] = date('Y-m-d H:i:s');
        $data['recorded_by'] = get_staff_user_id();
        $this->db->insert(db_prefix() . 'fleet_trip_offloading', $data);
        return $this->db->insert_id();
    }

    public function get_vehicles()
    {
        $this->db->order_by('name', 'ASC');
        return $this->db->get(db_prefix() . 'fleet_vehicles')->result();
    }

    public function get_service_points()
    {
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('name', 'ASC');
        return $this->db->get(db_prefix() . '_courier_service_points')->result();
    }

    // Kept for backwards compatibility
    public function get_picking_points()
    {
        return $this->get_service_points();
    }

    public function get_drivers()
    {
        // Use the "Fleet: Driver" role defined by the fleet module
        $this->db->where('name', 'Fleet: Driver');
        $role = $this->db->get(db_prefix() . 'roles')->row();

        $this->db->select('staffid, firstname, lastname, email, phonenumber');
        $this->db->where('active', 1);

        if ($role) {
            $this->db->where('role', $role->roleid);
        }

        $drivers = $this->db->get(db_prefix() . 'staff')->result();

        // Fall back to all active staff if no drivers are registered yet
        if (empty($drivers)) {
            $this->db->select('staffid, firstname, lastname, email, phonenumber');
            $this->db->where('active', 1);
            $this->db->order_by('firstname', 'ASC');
            $drivers = $this->db->get(db_prefix() . 'staff')->result();
        }

        return $drivers;
    }

    public function get_customers()
    {
        $this->db->select('userid, company, CONCAT(COALESCE(vat,"")," ",company) as label', false);
        $this->db->where('active', 1);
        $this->db->order_by('company', 'ASC');
        return $this->db->get(db_prefix() . 'clients')->result();
    }

    public function get_shipment($id)
    {
        $this->db->select('s.id, s.waybill_number, s.tracking_id, s.shipping_mode,
            CONCAT(sn.first_name," ",sn.last_name) AS sender_name,
            CONCAT(rp.first_name," ",rp.last_name) AS recipient_name,
            rp.address AS recipient_address,
            i.clientid AS client_id', false);
        $this->db->from(db_prefix() . '_shipments s');
        $this->db->join(db_prefix() . '_shipment_senders sn',    'sn.id = s.sender_id',    'left');
        $this->db->join(db_prefix() . '_shipment_recipients rp', 'rp.id = s.recipient_id', 'left');
        $this->db->join(db_prefix() . 'invoices i', 'i.id = s.invoice_id', 'left');
        $this->db->where('s.id', (int)$id);
        return $this->db->get()->row();
    }

    public function add_fuel_request($data)
    {
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['addedfrom']   = get_staff_user_id();
        $data['fuel_time']   = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'fleet_fuel_history', $data);
        return $this->db->insert_id();
    }

    public function get_fuel_request($id)
    {
        return $this->db->get_where(db_prefix() . 'fleet_fuel_history', ['id' => (int)$id])->row();
    }

    public function get_staff()
    {
        $this->db->select('staffid, firstname, lastname');
        $this->db->where('active', 1);
        $this->db->order_by('firstname', 'ASC');
        return $this->db->get(db_prefix() . 'staff')->result();
    }

    public function get_status_counts()
    {
        $this->db->select('status, COUNT(*) as cnt', false);
        $this->db->group_by('status');
        $rows   = $this->db->get(db_prefix() . 'fleet_trips')->result();
        $counts = ['booked'=>0,'fuel_requested'=>0,'started'=>0,'offloading'=>0,'completed'=>0,'cancelled'=>0];
        foreach ($rows as $r) { $counts[$r->status] = (int)$r->cnt; }
        return $counts;
    }
}
