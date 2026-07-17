<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pickups extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (!is_admin()
            && !has_permission('courier-pickups', '', 'view_all_pickups')
            && !has_permission('courier-pickups', '', 'view_own_pickups')) {
            access_denied('Courier - Pickups');
        }
        $this->load->helper('courier_goshipping/courier');
        // MX's model loader lowercases the whole path then only ucfirst()'s
        // the first letter before checking is_file(), so multi-capital
        // filenames like these never resolve on case-sensitive (Linux) fs.
        courier_load_model('PickupContact_model');
        $this->load->model('Pickup_model');
        $this->load->model('Shipment_model');
        $this->load->model('Driver_model');
        $this->load->library('form_validation');
    }

    public function main()
    {
        $group = $this->input->get('group', true) ?? 'dashboard';

        $staff_id   = get_staff_user_id();
        $branch_ids = $this->get_staff_branch_ids();
        $statuses   = ['pending', 'picked_up', 'delivered'];
        $pickup_counts = [];
        $user_role = $this->Driver_model->get_staff_role($staff_id);

        foreach ($statuses as $status) {
            if ($user_role === 'Fleet: Driver') {
                $pickup_counts[$status] = $this->Pickup_model->get_pickup_count_by_status($status, $staff_id, 'driver');
            } elseif (staff_can('view_all_pickups', 'courier-pickups')) {
                $pickup_counts[$status] = $this->Pickup_model->get(true, $status, null, null, $branch_ids);
            } else {
                $pickup_counts[$status] = $this->Pickup_model->get(true, $status, $staff_id, null, $branch_ids);
            }
        }


        $data['status_counts'] = $pickup_counts;
        $data['user_role'] = $user_role;
        $data['group'] = $group;

        switch ($group) {
            case 'dashboard':
                $data['title'] = _l('Dashboard');
                $data['group_content'] = $this->load->view('pickups/dashboard', $data, true);
                break;

            case 'create_pickup':
                // create.php is a standalone page with its own init_head/init_tail.
                redirect('admin/courier_goshipping/pickups/create');
                return;

            case 'list_pickups':
                // index.php is a standalone page with its own init_head/init_tail.
                redirect('admin/courier_goshipping/pickups/index');
                return;

            default:
                $data['group_content'] = $this->load->view('pickups.php', [], true);
                break;
        }

        if ($this->router->fetch_method() == 'main' && !$this->input->is_ajax_request()) {
            $this->load->view('pickups/main', $data);
        }
    }

    public function dashboard()
    {
        $this->load->view('pickups/dashboard');
    }

    public function index()
    {
        $staff_id   = get_staff_user_id();
        $branch_ids = $this->get_staff_branch_ids();
        $user_role  = $this->Driver_model->get_staff_role($staff_id);

        if (staff_can('view_all_pickups', 'courier-pickups')) {
            $data['pickups'] = $this->Pickup_model->get(false, null, null, null, $branch_ids);
        } elseif ($user_role === 'Fleet: Driver') {
            $data['pickups'] = $this->Pickup_model->get(false, null, $staff_id, 'driver');
        } else {
            $data['pickups'] = $this->Pickup_model->get(false, null, $staff_id, null, $branch_ids);
        }

        $this->load->view('pickups/index', $data);
    }

    public function create()
    {
        $data['drivers'] = $this->Driver_model->get();

        if (is_admin()) {
            $data['countries'] = $this->Shipment_model->get_countries();
        } else {
            $staff_id = get_staff_user_id();
            $country_id = $this->db->select('country_id')
                ->from(db_prefix() . '_courier_audit_logs')
                ->where('staff_id', $staff_id)
                ->get()
                ->row();
            if ($country_id) {
                $data['countries'] = $this->Shipment_model->get_countries($country_id->country_id);
            } else {
                $data['countries'] = $this->Shipment_model->get_countries();
            }
        }

        $this->load->view('pickups/create', $data);
    }

    private function set_validation_rules()
    {

        // Set validation rules for recipient data
        $this->form_validation->set_rules('contact_first_name', 'First Name', 'required');
        $this->form_validation->set_rules('contact_last_name', 'Last Name', 'required');
        $this->form_validation->set_rules('contact_phone_number', 'Phone Number', 'required');
        $this->form_validation->set_rules('contact_email', 'Email', 'trim|valid_email');

        // Set validation rules for pickup data
        $this->form_validation->set_rules('pickup_date', 'Pickup Date', 'required');
        $this->form_validation->set_rules('pickup_start_time', 'Pickup Start Time', 'required');
        $this->form_validation->set_rules('pickup_end_time', 'Pickup End Time', 'required');
        $this->form_validation->set_rules('country_id', 'Country', 'required');
        // state_id is optional: loaded via AJAX after page load
        $this->form_validation->set_rules('address', 'Address', 'required');
        // zipcode is optional
        $this->form_validation->set_rules('vehicle_type', 'Vehicle Type', 'required');
        $this->form_validation->set_rules('driver_id', 'Driver', 'required');
    }

    public function store()
    {

        $data['countries'] = $this->Pickup_model->get_countries();
        $data['drivers']   = $this->Driver_model->get();

        // Set validation rules
        $this->set_validation_rules();

        if ($this->form_validation->run() == FALSE) {

            // Validation failed; reload the form view with errors
            $data['errors'] = validation_errors();
            $this->load->view('pickups/create', $data);
        } else {

            //store contact person data
            $contact_person_data = [
                'first_name' => $this->input->post('contact_first_name'),
                'last_name' => $this->input->post('contact_last_name'),
                'phone_number' => $this->input->post('contact_phone_number'),
                'email' => $this->input->post('contact_email')
            ];
            $contact_id = $this->PickupContact_model->add($contact_person_data);

            if ($contact_id === false) {
                set_alert('danger', 'Failed to add contact person.');
                redirect('admin/courier_goshipping/pickups/create');
            }

            //store pickup data
            $pickup_data = [
                'pickup_date' => strtoupper($this->input->post('pickup_date')),
                'pickup_start_time' => strtoupper($this->input->post('pickup_start_time')),
                'pickup_end_time' => strtoupper($this->input->post('pickup_end_time')),
                'country_id' => $this->input->post('country_id'),
                'state_id' => $this->input->post('state_id'),
                'address' => $this->input->post('address'),
                'pickup_zip' => $this->input->post('zipcode'),
                'address_type' => $this->input->post('address_type'),
                'vehicle_type' => strtoupper($this->input->post('vehicle_type')),
                'contact_person_id' => $contact_id,
                'shipment_id' => !is_null($this->input->post('shipment_id')) ? $this->input->post('shipment_id') : NULL,
                'staff_id' => get_staff_user_id(),
                'driver_id' => $this->input->post('driver_id'),
                'source' => 'system',
                'created_at' => date('Y-m-d H:i:s')
            ];


            $pickup_id = $this->Pickup_model->add($pickup_data);

            if ($pickup_id === false) {
                set_alert('danger', 'Failed to add pickup.');
                redirect('admin/courier_goshipping/pickups/create');
            }

            set_alert('success', 'Pickup added successfully.');
            redirect('admin/courier_goshipping/pickups');
        }
    }

    public function edit()
    {
        $this->load->view('pickups/edit');
    }


    public function update_status()
    {
        $status     = $this->input->post('status');
        $pickup_id  = $this->input->post('pickup_id');
        $canvasData = $this->input->post('signature');
        $pickup     = $this->Pickup_model->get_pickup_by_id($pickup_id);

        // Statuses that don't require a signature can be saved directly
        $no_sig_statuses = ['cancelled'];

        if (!empty($canvasData)) {
            $canvasData = str_replace('data:image/png;base64,', '', $canvasData);
            $canvasData = str_replace(' ', '+', $canvasData);
            $imageData  = base64_decode($canvasData);
            $fileName   = uniqid() . '.png';
            $filePath   = FCPATH . 'modules/courier_goshipping/assets/pickups/signatures/' . $fileName;

            if (file_put_contents($filePath, $imageData)) {
                $update = ['status' => $status];
                if ($status == 'picked_up') {
                    $update['signature_url'] = 'assets/pickups/signatures/' . $fileName;
                    if (!empty($pickup['shipment_id'])) {
                        $this->updateShipmentStatus($pickup['shipment_id'], 2);
                    }
                } elseif ($status == 'delivered') {
                    $update['delivery_signature_url'] = 'assets/pickups/signatures/' . $fileName;
                    if (!empty($pickup['shipment_id'])) {
                        $this->updateShipmentStatus($pickup['shipment_id'], 3);
                    }
                }
                $this->Pickup_model->update($pickup_id, $update);
                set_alert('success', 'Status updated successfully.');
            } else {
                set_alert('danger', 'Failed to save the signature image.');
            }

        } elseif (in_array($status, $no_sig_statuses)) {
            // No signature needed — save directly
            $this->Pickup_model->update($pickup_id, ['status' => $status]);
            if ($status === 'cancelled' && !empty($pickup['shipment_id'])) {
                // Optionally mark the linked shipment as cancelled too (status id 9)
                $cancelled_row = $this->db->get_where(db_prefix() . '_shipment_statuses', ['status_name' => 'cancelled'])->row();
                if ($cancelled_row) {
                    $this->updateShipmentStatus($pickup['shipment_id'], $cancelled_row->id);
                }
            }
            set_alert('success', 'Status updated successfully.');

        } else {
            set_alert('danger', 'No signature data received.');
        }

        redirect(admin_url('courier_goshipping/pickups'));

    }

    public function updateShipmentStatus($shipment_id, $status_id)
    {

        $shipment_data = [
            'status_id' => $status_id
        ];

        $this->Shipment_model->update($shipment_id, $shipment_data);

        // Record the status change in the shipment_status_histories table
        $this->db->insert(db_prefix() . '_shipment_status_history', [
            'shipment_id' => $shipment_id,
            'status_id' => $this->input->post('status_id'),
            'changed_at' => date('Y-m-d H:i:s'),
        ]);

    }

    public function delete($id)
    {
        // Fetch the pickup to ensure it exists and get associated IDs
        $pickup = $this->Pickup_model->get($id);

        if (!$pickup) {
            // Pickup not found; redirect with error
            set_alert('danger', 'Pickup not found.');
            redirect('admin/courier_goshipping/pickups/index');
        }

        // Proceed with deletion
        $this->db->trans_start(); // Start transaction

        // Delete the pickup
        if (!$this->Pickup_model->delete($id)) {
            $this->db->trans_rollback(); // Rollback if deletion fails
            set_alert('danger', 'Failed to delete pickup.');
            redirect('admin/courier_goshipping/pickups/index');
        }

        // Delete associated driver
        if (!$this->PickupContact_model->delete($pickup->contact_person_id)) {
            $this->db->trans_rollback(); // Rollback if deletion fails
            set_alert('danger', 'Failed to delete contact person.');
            redirect('admin/courier_goshipping/pickups/index');
        }

        $this->db->trans_complete(); // Complete transaction

        if ($this->db->trans_status() === FALSE) {
            set_alert('danger', 'Failed to delete pickup and associated data.');
            redirect('admin/courier_goshipping/pickups/index');
        }

        // Success
        set_alert('success', 'Pickup and associated data deleted successfully.');
        redirect('admin/courier_goshipping/pickups/index');
    }

    public function bulk_delete()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        header('Content-Type: application/json');

        $ids = $this->input->post('ids');
        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['success' => false, 'message' => 'No IDs provided.']);
            return;
        }

        $deleted = 0;
        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id <= 0) continue;
            $pickup = $this->Pickup_model->get($id);
            if (!$pickup) continue;

            $this->db->trans_start();
            $this->Pickup_model->delete($id);
            if (!empty($pickup->contact_person_id)) {
                $this->PickupContact_model->delete($pickup->contact_person_id);
            }
            $this->db->trans_complete();
            if ($this->db->trans_status() !== FALSE) {
                $deleted++;
            }
        }

        echo json_encode(['success' => true, 'deleted' => $deleted]);
    }

    public function view($pickup_id)
    {
        $data['pickup_id'] = $pickup_id;
        $data['pickup'] = $this->Pickup_model->get_pickup_by_id($pickup_id);

        if (empty($data['pickup'])) {
            set_alert('danger', 'Pickup not found.');
            redirect('admin/courier_goshipping/pickups/main');
            return;
        }

        if (!is_admin() && !staff_can('view_all_pickups', 'courier-pickups')
            && (int)($data['pickup']['staff_id'] ?? 0) !== (int)get_staff_user_id()) {
            set_alert('danger', 'Access denied — this pickup does not belong to you.');
            redirect('admin/courier_goshipping/pickups/main');
            return;
        }

        $this->load->view('pickups/view', $data);
    }

    private function get_staff_branch_ids()
    {
        if (courier_staff_can_view_all_branches()) {
            return null;
        }

        return courier_get_staff_branch_ids();
    }

}
