<?php

use Picqer\Barcode\BarcodeGeneratorHTML;
use Picqer\Barcode\BarcodeGeneratorPNG;

defined('BASEPATH') or exit('No direct script access allowed');

class Shipments extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $method = $this->router->fetch_method();
        $invoice_methods = ['courier_invoice', 'commercial_invoice', 'list_commercial_invoices'];
        if (!in_array($method, $invoice_methods)) {
            if (!is_admin()
                && !has_permission('courier-shipments', '', 'view_all_shipments')
                && !has_permission('courier-shipments', '', 'view_own_shipments')) {
                access_denied('Courier - Shipments');
            }
        }
        $this->load->helper('courier_goshipping/courier'); // Load the helper specific to the courier module
        // MX's model loader lowercases the whole path then only ucfirst()'s
        // the first letter before checking is_file(), so multi-capital
        // filenames below never resolve on case-sensitive (Linux) fs.
        $this->load->model('Shipment_model');
        courier_load_model('ShipmentRecipient_model');
        courier_load_model('ShipmentSender_model');
        $this->load->model('Client_model');
        courier_load_model('ShipmentPackage_model');
        courier_load_model('CourierCompany_model');
        courier_load_model('ShipmentCompany_model');
        courier_load_model('ShipmentRecipientCompany_model');
        courier_load_model('ShipmentFCLPackage_model');
        courier_load_model('CommercialValueItems_model');
        courier_load_model('ShipmentStatus_model');
        courier_load_model('PickupContact_model');
        $this->load->model('Pickup_model');
        courier_load_model('ShipmentStop_model');
        $this->load->model('Delivery_model');
        $this->load->model('Manifest_model');
        courier_load_model('CountryState_model');
        courier_load_model('DimensionalFactor_model');
        $this->load->model('Driver_model');
        courier_load_model('DestinationOffice_model');
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('Agent_model');

        spl_autoload_register([$this, 'barcodeAutoloader']);

    }


    public function main()
    {

        $group = $this->input->get('group', true) ?? 'dashboard';
        $data['group'] = $group;

        $staff_id   = get_staff_user_id();
        $branch_ids = $this->get_staff_branch_ids();
        $can_all    = staff_can('view_all_shipments', 'courier-shipments');

        // Single GROUP BY query for all status counts
        $this->db->select('s.status_id, COUNT(*) as cnt', false);
        $this->db->from(db_prefix() . '_shipments s');
        if (!$can_all)      { $this->db->where('s.staff_id', $staff_id); }
        if ($branch_ids !== null) { $this->db->where_in('s.branch_id', !empty($branch_ids) ? $branch_ids : [0]); }
        $this->db->group_by('s.status_id');
        $counts_raw = $this->db->get()->result();
        $shipment_counts = [];
        foreach ($counts_raw as $row) {
            $shipment_counts[(string)$row->status_id] = (int)$row->cnt;
        }
        // Ensure all known status IDs are present (default 0)
        foreach (['1','2','3','4','5','6','7','8','9'] as $sid) {
            $shipment_counts[$sid] = $shipment_counts[$sid] ?? 0;
        }

        $data['shipment_counts']        = $shipment_counts;
        $data['pickup_counts']          = $this->db->count_all(db_prefix() . '_pickups');
        $data['courier_company_counts'] = $this->db->count_all(db_prefix() . '_courier_companies');

        switch ($group) {
            case 'dashboard':
                $data['title'] = 'Dashboard';
                $data['group_content'] = $this->load->view('shipments/dashboard', $data, true);
                break;

            case 'create_shipment':
                // create.php is a standalone page with its own init_head/init_tail.
                // Loading it as group_content inside main.php would double the HTML structure.
                $qs = http_build_query(array_filter([
                    'type'      => $this->input->get('type', true),
                    'mode'      => $this->input->get('mode', true),
                    'mode_type' => $this->input->get('mode_type', true),
                ]));
                redirect(admin_url('courier_goshipping/shipments/create') . ($qs ? '?' . $qs : ''));
                return;

            case 'list_shipments':
                // index.php is a standalone page with its own init_head/init_tail.
                $qs = http_build_query(array_filter([
                    'type'      => $this->input->get('type', true),
                    'mode'      => $this->input->get('mode', true),
                    'mode_type' => $this->input->get('mode_type', true),
                ]));
                redirect(admin_url('courier_goshipping/shipments') . ($qs ? '?' . $qs : ''));
                return;

            case 'manifests':
                if (!is_admin()
                    && !staff_can('view_manifests', 'courier-manifests')
                    && !staff_can('view_own_manifests', 'courier-manifests')) {
                    access_denied('Courier - Manifests');
                }
                $mf_can_all = is_admin() || staff_can('view_manifests', 'courier-manifests');

                $data['title'] = 'Manifests';

                $mf_date_from     = $this->input->get('date_from')       ?: date('Y-m-01');
                $mf_date_to       = $this->input->get('date_to')         ?: date('Y-m-d');
                $mf_driver_id     = $mf_can_all ? ($this->input->get('driver_id') ?: null) : $staff_id;
                $mf_route_id      = (int)($this->input->get('route_id')  ?: 0);
                $mf_svc_points    = $this->input->get('service_points')  ?: [];

                $data['filter_date_from']     = $mf_date_from;
                $data['filter_date_to']       = $mf_date_to;
                $data['filter_driver_id']     = $mf_driver_id;
                $data['filter_route_id']      = $mf_route_id;
                $data['filter_service_points'] = (array)$mf_svc_points;
                $data['manifest_rows']        = $this->_get_manifest_rows($mf_date_from, $mf_date_to, $mf_driver_id, (array)$mf_svc_points);
                $data['all_drivers']          = $this->Driver_model->get();
                $data['all_routes']           = $this->db->get(db_prefix() . '_courier_routes')->result();
                $data['all_service_points']   = $this->db
                    ->order_by('sort_order', 'ASC')->order_by('name', 'ASC')
                    ->get(db_prefix() . '_courier_service_points')->result_array();
                // Manifest renders as a full standalone page — no group_content needed
                break;

            default:
                $data['title'] = 'Dashboard';
                $data['group_content'] = $this->load->view('shipments/dashboard', $data, true);
                break;
        }

        if ($this->router->fetch_method() == 'main' && !$this->input->is_ajax_request()) {
            if ($group === 'manifests') {
                // Full-page standalone — bypasses the sidebar layout in main.php
                $this->load->view('shipments/manifest', $data);
            } else {
                $this->load->view('shipments/main', $data);
            }
        }

    }

    /**
     * AJAX: send manifest as HTML email
     * POST params: to_email, subject, date_from, date_to, driver_id
     */
    public function send_manifest_email()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $to_email   = trim($this->input->post('to_email'));
        $subject    = trim($this->input->post('subject')) ?: 'Cargo Manifest';
        $date_from  = $this->input->post('date_from') ?: date('Y-m-01');
        $date_to    = $this->input->post('date_to')   ?: date('Y-m-d');
        $driver_id  = $this->input->post('driver_id') ?: null;

        if (!filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
            return;
        }

        $rows = $this->_get_manifest_rows($date_from, $date_to, $driver_id);

        // Company info — spans multiple shipments/branches, so use whichever
        // branch the staff generating this is currently operating as.
        $_ci_s    = courier_get_invoice_info(courier_get_session_branch_id());
        $company  = $_ci_s['name']    ?: '';
        $phone    = $_ci_s['phone']   ?: '';
        $address  = $_ci_s['address'] ?: '';
        $from_lbl = date('d M Y', strtotime($date_from));
        $to_lbl   = date('d M Y', strtotime($date_to));

        // Build HTML email body
        $total_weight  = 0;
        $total_charges = 0;
        $total_amount  = 0;
        foreach ($rows as $_r) {
            $total_weight  += (float)$_r['pkg_weight'];
            $total_charges += (float)$_r['charges'];
            $total_amount  += (float)$_r['total'];
        }

        $html  = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
        $html .= '<style>body{font-family:Arial,sans-serif;font-size:12px;color:#111;}';
        $html .= 'h2{margin:0;font-size:16px;}';
        $html .= 'table{width:100%;border-collapse:collapse;margin-top:14px;}';
        $html .= 'th{background:#2e7d32;color:#fff;padding:7px 6px;text-align:left;font-size:11px;}';
        $html .= 'td{padding:6px;border-bottom:1px solid #ddd;font-size:11px;}';
        $html .= 'tr:nth-child(even) td{background:#f9fbe7;}';
        $html .= '.tfoot td{background:#1b5e20;color:#fff;font-weight:700;}';
        $html .= '</style></head><body>';
        $html .= '<div style="background:#2e7d32;padding:14px 20px;border-radius:6px 6px 0 0;">';
        $html .= '<h2 style="color:#fff;">' . htmlspecialchars($company) . ' — CARGO MANIFEST</h2>';
        $html .= '<p style="color:rgba(255,255,255,.85);margin:4px 0 0;font-size:11px;">';
        $html .= $from_lbl . ' to ' . $to_lbl;
        if ($phone) $html .= ' | ' . htmlspecialchars($phone);
        $html .= '</p></div>';
        $html .= '<div style="background:#e8f5e9;padding:10px 20px;border:1px solid #c8e6c9;">';
        $html .= '<strong>' . count($rows) . ' shipments</strong> &nbsp;|&nbsp; ';
        $html .= 'Total weight: <strong>' . number_format($total_weight, 2) . ' kg</strong> &nbsp;|&nbsp; ';
        $html .= 'Grand total: <strong>' . number_format($total_amount, 2) . '</strong>';
        $html .= '</div>';
        $html .= '<table>';
        $html .= '<thead><tr>';
        $html .= '<th>#</th><th>Date</th><th>AWB / Waybill</th>';
        $html .= '<th>Sender</th><th>Sender Phone</th><th>Pickup Point</th>';
        $html .= '<th>Receiver</th><th>Receiver Phone</th><th>Delivery Point</th>';
        $html .= '<th>Driver</th><th>Weight (kg)</th><th>Charges</th><th>VAT</th><th>Total</th><th>Status</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($rows as $i => $r) {
            $html .= '<tr>';
            $html .= '<td>' . ($i + 1) . '</td>';
            $html .= '<td>' . date('d-m-Y', strtotime($r['date'])) . '</td>';
            $html .= '<td><strong>' . htmlspecialchars($r['waybill']) . '</strong></td>';
            $html .= '<td>' . htmlspecialchars($r['sender_name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($r['sender_phone']) . '</td>';
            $html .= '<td>' . htmlspecialchars($r['sender_addr']) . '</td>';
            $html .= '<td>' . htmlspecialchars($r['recv_name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($r['recv_phone']) . '</td>';
            $html .= '<td>' . htmlspecialchars($r['recv_addr']) . '</td>';
            $html .= '<td>' . htmlspecialchars($r['driver_name']) . '</td>';
            $html .= '<td>' . number_format((float)$r['pkg_weight'], 2) . '</td>';
            $html .= '<td>' . number_format((float)$r['charges'], 2) . '</td>';
            $html .= '<td>' . number_format((float)$r['vat'], 2) . '</td>';
            $html .= '<td><strong>' . number_format((float)$r['total'], 2) . '</strong></td>';
            $html .= '<td>' . htmlspecialchars($r['status']) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody><tfoot><tr class="tfoot">';
        $html .= '<td colspan="10" style="text-align:right;background:#1b5e20;color:#fff;font-weight:700;">TOTALS</td>';
        $html .= '<td style="background:#1b5e20;color:#fff;font-weight:700;">' . number_format($total_weight, 2) . '</td>';
        $html .= '<td style="background:#1b5e20;color:#fff;font-weight:700;">' . number_format($total_charges, 2) . '</td>';
        $html .= '<td style="background:#1b5e20;color:#fff;font-weight:700;"></td>';
        $html .= '<td style="background:#1b5e20;color:#fff;font-weight:700;">' . number_format($total_amount, 2) . '</td>';
        $html .= '<td style="background:#1b5e20;color:#fff;"></td>';
        $html .= '</tr></tfoot></table>';
        $html .= '<p style="margin-top:16px;font-size:10px;color:#aaa;">Generated ' . date('d M Y, H:i') . ' — ' . htmlspecialchars($company) . '</p>';
        $html .= '</body></html>';

        // Send via CI email library
        $this->load->library('email');
        $from_email = get_option('smtp_email') ?: get_option('company_email') ?: 'noreply@' . parse_url(base_url(), PHP_URL_HOST);
        $this->email->from($from_email, $company);
        $this->email->to($to_email);
        $this->email->subject($subject . ' — ' . $from_lbl . ' to ' . $to_lbl);
        $this->email->message($html);

        if ($this->email->send()) {
            echo json_encode(['success' => true, 'message' => 'Manifest sent to ' . $to_email]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send email. Check SMTP settings.']);
        }
    }

    public function dashboard()
    {
        $this->load->view('shipments/dashboard');
    }

    /**
     * AJAX: save logistic company name to Perfex options table.
     * Called from the "+" button modal on the Create Shipment form.
     */
    public function save_logistic_company()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $name = trim($this->input->post('company_name'));

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Company name cannot be empty.']);
            return;
        }

        update_option('courier_logistic_company', $name);

        echo json_encode([
            'success' => true,
            'message' => 'Logistic company saved successfully.',
            'name'    => $name,
        ]);
    }

    public function clear_filters()
    {
        // Clear the session data
        $this->session->unset_userdata('shipment_details');
        $this->session->unset_userdata('filterDateRange');
        $this->session->unset_userdata('status_id');
        $this->session->unset_userdata('staff_id');
        $this->session->unset_userdata('no_shipments');


        if ($this->input->is_ajax_request()) {
            echo json_encode([]);
            return;
        }

    }


    public function filter_shipments()
    {

        // Remove the shipment details from the session
        $this->session->unset_userdata('shipment_details');

        // Get filter inputs
        $type = $this->input->post('type');
        $this->session->set_userdata('type', $type);

        $mode = $this->input->post('mode'); // Changed to POST
        $mode_type = $this->input->post('mode_type'); // Changed to POST

        $data = [];

        if (empty($this->input->post('filterDateRange')) &&
            ($this->input->post('status_id') == '0') &&
            ($this->input->post('staff_id') == '0')) {

            $this->clear_filters();

            if ($type !== 'domestic') {
                redirect(admin_url('courier_goshipping/shipments') . '?type=' . $type . '&mode=' . $mode . '&mode_type=' . $mode_type);
            } else {
                redirect(admin_url('courier_goshipping/shipments') . '?type=' . $type);
            }
        }


        if (!empty($this->input->post('filterDateRange')) || $this->input->post('status_id') !== '0' || $this->input->post('staff_id') !== '0') {

            // Handle the date range filter
            $startDate = null;
            $endDate = null;

            if (!empty($this->input->post('filterDateRange'))) {
                $dateRange = $this->input->post('filterDateRange');
                $dates = explode(" to ", $dateRange);
                $startDate = $dates[0];
                $endDate = isset($dates[1]) ? $dates[1] : $dates[0];
            }

            // Handle status and other parameters
            $staff_id    = get_staff_user_id();
            $branch_ids  = $this->get_staff_branch_ids();
            $status_id   = $this->input->post('status_id');
            $filter_staff_id = $this->input->post('staff_id');
            $is_view_all = staff_can('view_all_shipments', 'courier-shipments');
            $staff_id_param = $is_view_all ? null : $staff_id;

            // Filter shipment details
            $data['shipment_details'] = $this->Shipment_model->filter_shipment_details(
                $staff_id_param,
                !empty($status_id) && $status_id != '0' ? $status_id : null,
                !empty($filter_staff_id) && $filter_staff_id != '0' ? $filter_staff_id : null,
                $startDate,
                $endDate,
                $type,
                $mode,
                $mode_type,
                $branch_ids
            );

            $this->session->set_userdata('filterDateRange', $this->input->post('filterDateRange'));
            $this->session->set_userdata('status_id', $this->input->post('status_id'));
            $this->session->set_userdata('staff_id', $this->input->post('staff_id'));


            if (empty($data['shipment_details'])) {
                $this->session->set_userdata('no_shipments', true);
            } else {

                $this->session->set_userdata('no_shipments', false);
                $this->session->set_userdata('shipment_details', $data['shipment_details']);
            }


            if ($type !== 'domestic') {
                redirect(admin_url('courier_goshipping/shipments') . '?type=' . $type . '&mode=' . $mode . '&mode_type=' . $mode_type);
            } else {
                redirect(admin_url('courier_goshipping/shipments') . '?type=' . $type);
            }

        } else {

            $url = 'courier_goshipping/shipments?type=';

            // Set session data
            $type = $this->session->userdata('type');

            $url = $url . $type;

            if ($this->session->userdata('mode') !== null) {
                $mode = $this->session->userdata('mode');
                $mode_type = $this->session->userdata('mode_type');
                $url = $url . '&mode=' . $mode . '&mode_type=' . $mode_type;
            }

            $this->session->set_userdata('shipment_details', $data['shipment_details']);

            set_alert('danger', 'Please select at least one filter');
            redirect('admin/' . $url);
        }
    }


    public function index()
    {

        // Set session data
        $type = $this->input->get('type');
        $this->session->set_userdata('type', $type);

        $mode = null;
        $mode_type = null;

        if ($this->input->get('mode') !== null) {
            $mode = $this->input->get('mode');
            $this->session->set_userdata('mode', $mode);
            $this->session->set_userdata('mode_type', $this->input->get('mode_type'));
        }

        if ($this->input->get('mode_type') !== 'none') {
            $mode_type = $this->input->get('mode_type');
        }

        $staff_id   = get_staff_user_id();
        $branch_ids = $this->get_staff_branch_ids();

        // Handle GET-based status pre-filter (from dashboard stat card clicks)
        $pre_status = $this->input->get('status');
        if ($pre_status !== null) {
            $this->session->set_userdata('status_id', $pre_status ?: '0');
            $this->session->set_userdata('staff_id', '0');
            $this->session->set_userdata('filterDateRange', '');
            $this->session->unset_userdata('shipment_details');
            $this->session->unset_userdata('no_shipments');
        }

        $sess_status   = $this->session->userdata('status_id');
        $has_status    = !empty($sess_status) && $sess_status !== '0';
        $has_active_filter = !empty($this->session->userdata('filterDateRange'))
            || $has_status
            || ($this->session->userdata('staff_id') != '0' && !empty($this->session->userdata('staff_id')));

        if ($has_active_filter && !empty($this->session->userdata('shipment_details'))) {
            $data['shipment_details'] = $this->session->userdata('shipment_details');
        } elseif ($has_status) {
            // Status filter set (from card click or previous filter) but no cached results
            $staff_param = staff_can('view_all_shipments', 'courier-shipments') ? null : $staff_id;
            $data['shipment_details'] = $this->Shipment_model->filter_shipment_details(
                $staff_param, $sess_status, null, null, null, $type, null, null, $branch_ids
            );
        } else {
            if (staff_can('view_all_shipments', 'courier-shipments')) {
                $data['shipment_details'] = $this->Shipment_model->get_shipments_details(null, $type, $mode, $mode_type, $branch_ids);
            } else {
                $data['shipment_details'] = $this->Shipment_model->get_shipments_details($staff_id, $type, $mode, $mode_type, $branch_ids);
            }
        }

        // Check if no shipments were found
        $data['no_shipments'] = $this->session->userdata('no_shipments') ?? false;

        $data['agents'] = $this->Agent_model->get();

        $data['countries'] = $this->Shipment_model->get_countries();

        $this->load->model('payment_modes_model');
        $data['offline_modes']   = $this->payment_modes_model->get('', [], false);
        $data['online_gateways'] = $this->payment_modes_model->get_payment_gateways(false);

	// Sort newest first
        if (!empty($data['shipment_details'])) {
            usort($data['shipment_details'], function($a, $b) {
                return strtotime($b['shipment']->created_at) - strtotime($a['shipment']->created_at);
            });
        }

        $this->load->view('shipments/index', $data);
    }

    public function create()
    {
        $data['drivers'] = $this->Driver_model->get();
        $data['dimensional_factor'] = $this->DimensionalFactor_model->get();
        $data['countries'] = $this->filterSenderCountries();
        $data['currencies'] = $this->Shipment_model->get_currencies();
        $data['type'] = $this->input->get('type', true) ?? 'international';
        $data['mode'] = $this->input->get('mode', true) ?? 'none';
        $data['mode_type'] = $this->input->get('mode_type', true) ?? 'none';
        $data['recipient_countries'] = $this->Shipment_model->get_countries();
        $data['user_country'] = $this->getStaffCountry();
        $data['courier_companies'] = $this->CourierCompany_model->get();

        // Logistic company name — prefer Courier Settings, fall back to Perfex company name
        $_lc_raw = get_option('courier_logistic_company');
        $data['courier_logistic_company'] = (!empty($_lc_raw) && $_lc_raw !== 'GO Shipping')
            ? $_lc_raw
            : (get_option('companyname') ?: '');

        $data['courier_type']               = get_option('courier_type') ?: 'international';
        $data['courier_payment_terms_mode'] = get_option('courier_payment_terms_mode') ?: 'manual';

        // Service points for POD dropdown
        $data['service_points'] = $this->db
            ->order_by('sort_order', 'ASC')
            ->order_by('name', 'ASC')
            ->get(db_prefix() . '_courier_service_points')
            ->result_array();

        // Current logged-in staff details for pickup contact autofill
        $current_staff_id = get_staff_user_id();
        $data['current_staff'] = $this->db
            ->select('firstname, lastname, email, phonenumber')
            ->from(db_prefix() . 'staff')
            ->where('staffid', $current_staff_id)
            ->get()->row();

        $this->load->view('shipments/create', $data);

    }

    private function validatePickup()
    {
        if ($this->input->post('hasPickup') !== null) {
            $this->form_validation->set_rules('pickup_contact_first_name', 'First Name', 'required');
            $this->form_validation->set_rules('pickup_contact_last_name', 'Last Name', 'required');
            // Phone and email are optional — auto-filled from staff profile
            // pickup_contact_phone_number: no rule needed (always accepted)
            $this->form_validation->set_rules('pickup_contact_email', 'Email', 'callback__optional_email');
            $this->form_validation->set_rules('pickup_date', 'Pickup Date', 'required');
            $this->form_validation->set_rules('pickup_start_time', 'Pickup Start Time', 'required');
            $this->form_validation->set_rules('pickup_end_time', 'Pickup End Time', 'required');
            $this->form_validation->set_rules('pickup_country_id', 'Country', 'required');
            // pickup_state_id: optional (Kenya uses custom service-point list, not a strict required state)
            // $this->form_validation->set_rules('pickup_state_id', 'State', 'required');
            $this->form_validation->set_rules('pickup_address', 'Address', 'required');
            // pickup_zipcode: optional, no rule needed
            $this->form_validation->set_rules('pickup_vehicle_type', 'Vehicle Type', 'required');
            $this->form_validation->set_rules('pickup_driver_id', 'Driver', 'required');
        }
    }

    /**
     * Callback: allow empty value; if non-empty, must be a valid email address.
     */
    public function _optional_email($str)
    {
        $str = trim($str);
        if ($str === '') {
            return true;
        }
        if (!filter_var($str, FILTER_VALIDATE_EMAIL)) {
            $this->form_validation->set_message('_optional_email', 'The {field} field must contain a valid email address.');
            return false;
        }
        return true;
    }


    private function validateCompany()
    {
        if ($this->input->post('sender_type') === 'company') {
            $this->form_validation->set_rules('company_name', 'Company Name', 'required');
            $this->form_validation->set_rules('contact_name', 'Contact Name', 'required');
            $this->form_validation->set_rules('contact_phone', 'Contact Person Phone Number', 'required');
            $this->form_validation->set_rules('contact_email', 'Contact Person Email', 'required');
            $this->form_validation->set_rules('contact_address', 'Address', 'required');
            // contact_zipcode is optional — no rule needed

            if ($this->input->post('type') === 'international') {
                $this->form_validation->set_rules('contact_state_id', 'Contact Person State ', 'required');
                $this->form_validation->set_rules('contact_country_id', 'Contact Person Country', 'required');
            }

        }

        if ($this->input->post('recipient_type') === 'company') {
            $this->form_validation->set_rules('recipient_company_name', 'Company Name', 'required');
            $this->form_validation->set_rules('recipient_contact_name', 'Contact Name', 'required');
            $this->form_validation->set_rules('recipient_contact_phone', 'Contact Person Phone Number', 'required');
            $this->form_validation->set_rules('recipient_contact_email', 'Contact Person Email', 'required');
            $this->form_validation->set_rules('recipient_contact_address', 'Address', 'required');
            // recipient_contact_zipcode is optional — no rule needed

            if ($this->input->post('type') === 'international') {
                $this->form_validation->set_rules('recipient_contact_state_id', 'Contact Person State ', 'required');
                $this->form_validation->set_rules('recipient_contact_country_id', 'Contact Person Country', 'required');
            }

        }

    }


    private function validateSender()
    {
        if ($this->input->post('sender_type') === 'individual') {

            $this->form_validation->set_rules('sender_first_name', 'Sender First Name', 'required');
            $this->form_validation->set_rules('sender_last_name', 'Sender Last Name', 'required');
            $this->form_validation->set_rules('sender_phone_number', 'Sender Phone Number', 'required');
            $this->form_validation->set_rules('sender_email', 'Sender Email', 'required|valid_email');
            $this->form_validation->set_rules('sender_address', 'Sender Address', 'required');
            // sender_zipcode is optional — no rule needed

            if ($this->input->post('type') === 'international') {
                $this->form_validation->set_rules('sender_country_id', 'Sender Country', 'required');
                $this->form_validation->set_rules('sender_state_id', 'Sender State', 'required');
            }

        }

    }

    private function validateRecipient()
    {
        if ($this->input->post('recipient_type') === 'individual') {

            $this->form_validation->set_rules('recipient_first_name', 'Recipient First Name', 'required');
            $this->form_validation->set_rules('recipient_last_name', 'Recipient Last Name', 'required');
            $this->form_validation->set_rules('recipient_phone_number', 'Recipient Phone Number', 'required');
            $this->form_validation->set_rules('recipient_email', 'Recipient Email', 'required|valid_email');
            $this->form_validation->set_rules('recipient_address', 'Recipient Address', 'required');
            // recipient_zipcode is optional — no rule needed

            if ($this->input->post('type') === 'international') {
                $this->form_validation->set_rules('recipient_country_id', 'Recipient Country', 'required');
                $this->form_validation->set_rules('recipient_state_id', 'Recipient State', 'required');
            }

        }
    }

    private function validateFCLPackages()
    {
        $itemCount = count(set_value('amount', []));

        for ($i = 0; $i < $itemCount; $i++) {
            $this->form_validation->set_rules("amount[$i]", 'Quantity', 'required|numeric');
            $this->form_validation->set_rules("package_description[$i]", 'Package Description', 'required');
            $this->form_validation->set_rules("fcl_options[$i]", 'FCL Option', 'required');
        }
    }

    private function validateNonFCLPackages()
    {
        // Get the count of the amounts input; you can replace this with the actual source of data
        $amountCount = count(set_value('amount', [])) ?: 1;

        for ($i = 0; $i < $amountCount; $i++) {
            $this->form_validation->set_rules("amount[$i]", 'Amount', 'required|numeric');
            $this->form_validation->set_rules("package_description[$i]", 'Package Description', 'required');
            $this->form_validation->set_rules("weight[$i]", 'Weight', 'required|numeric');
            $this->form_validation->set_rules("length[$i]", 'Length', 'required|numeric');
            $this->form_validation->set_rules("width[$i]", 'Width', 'required|numeric');
            $this->form_validation->set_rules("height[$i]", 'Height', 'required|numeric');
            $this->form_validation->set_rules("weight_vol[$i]", 'Weight Volume', 'required|numeric');
            $this->form_validation->set_rules("chargeable_weight[$i]", 'Chargeable Weight', 'required|numeric');
        }
    }


    private function validateCommercialValueItems()
    {

        if ($this->input->post('hasCommercialInvoiceAttachment') !== null) {
            if (empty($_FILES['commercial_invoice_file']['name'])) {
                $this->form_validation->set_rules('commercial_invoice_file', 'Attachment', 'required');
            } else {
                if ($_FILES['commercial_invoice_file']['error'] !== UPLOAD_ERR_OK) {
                    $this->form_validation->set_rules('commercial_invoice_file', 'Attachment', 'required');
                }
            }
        } else {

            $itemCount = count(set_value('commodity_quantity', []));

            for ($i = 0; $i < $itemCount; $i++) {
                $this->form_validation->set_rules("commodity_quantity[$i]", 'Quantity', 'required|numeric');
                $this->form_validation->set_rules("commodity_description[$i]", 'Item Description', 'required');
                $this->form_validation->set_rules("declared_value[$i]", 'Declared Value', 'required|numeric');
            }

        }

    }

    private function validateShipment()
    {
        $this->form_validation->set_rules('shipping_mode', 'Shipping Mode', 'required');
        // courier_company_id is optional; numeric check skipped (CI3 lacks permit_empty)
    }

    // Controller method to handle recipient data
    public function store_recipient_data()
    {
        $recipient_id = $this->add_recipient();

        if ($recipient_id === false) {
            set_alert('danger', 'Failed to add recipient.');
            redirect(admin_url('courier_goshipping/pickups/create'));
        }

        return $recipient_id;
    }

// New private method to add recipient
    private function add_recipient()
    {
        // Determine address type
        $address_type = $this->input->post('recipient_address_type') === 'zip_code' ? 'zip_code' : 'postal_code';

        // Prepare recipient data
        $recipient_data = [
            'first_name' => $this->input->post('recipient_first_name'),
            'last_name' => $this->input->post('recipient_last_name'),
            'phone_number' => $this->input->post('recipient_country_code') . $this->input->post('recipient_phone_number'),
            'email' => $this->input->post('recipient_email'),
            'address' => $this->input->post('recipient_address'),
            'zipcode' => $this->input->post('recipient_zipcode'),
            'address_type' => $address_type,
            'state_id' => $this->input->post('recipient_state_id') ?: NULL,
            'country_id' => $this->input->post('recipient_country_id') ?: NULL,
            'id_number' => $this->input->post('recipient_id_number') ?: NULL,
        ];

        // Store recipient data
        return $this->ShipmentRecipient_model->add($recipient_data);
    }

    // Controller method to handle sender data
    public function store_sender_data()
    {
        $sender_id = $this->add_sender();

        if ($sender_id === false) {
            set_alert('danger', 'Failed to add sender.');
            redirect(admin_url('courier_goshipping/pickups/create'));
        }

        return $sender_id;
    }

    // New private method to add sender
    private function add_sender()
    {
        // Determine address type
        $address_type = $this->input->post('sender_address_type') === 'zip_code' ? 'zip_code' : 'postal_code';

        // Prepare sender data
        $sender_data = [
            'first_name' => $this->input->post('sender_first_name'),
            'last_name' => $this->input->post('sender_last_name'),
            'phone_number' => $this->input->post('sender_country_code') . $this->input->post('sender_phone_number'),
            'email' => $this->input->post('sender_email'),
            'address' => $this->input->post('sender_address'),
            'zipcode' => $this->input->post('sender_zipcode'),
            'address_type' => $address_type,
            'state_id' => $this->input->post('sender_state_id') ?: NULL,
            'country_id' => $this->input->post('sender_country_id') ?: NULL,
            'id_number' => $this->input->post('sender_id_number') ?: NULL,
            'kra_pin'   => $this->input->post('sender_kra_pin')   ?: NULL,
        ];

        // Store sender data
        return $this->ShipmentSender_model->add($sender_data);
    }

    // Controller method to handle client data
    public function store_client_data()
    {
        $client_id = $this->add_client();

        if ($client_id === false) {
            set_alert('danger', 'Failed to add client.');
            redirect(admin_url('courier_goshipping/pickups/create'));
        }

        return $client_id;
    }


    // Custom autoloader for the Barcode library
    private function barcodeAutoloader($class)
    {
        // Base directory for the Barcode library
        $base_dir = FCPATH . 'modules/courier_goshipping/libraries/php-barcode-generator-main/src/';

        // Replace namespace prefix and backslashes, then append with .php
        $file = $base_dir . str_replace(['Picqer\\Barcode\\', '\\'], ['', '/'], $class) . '.php';

        // Include the file if it exists
        if (file_exists($file)) {
            require_once $file;
        }
    }


    public function generate_barcode($code)
    {
        // Instantiate the Barcode Generator
        $generator = new BarcodeGeneratorPNG();

        // Generate the Barcode PNG
        $barcode = $generator->getBarcode($code, $generator::TYPE_CODE_128);

        // Define the directory path where you want to store the barcode
        $directory = FCPATH . 'modules/courier_goshipping/assets/barcodes/';

        // Ensure the directory exists
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Sanitize tracking code for filename safety (e.g. replace slashes/backslashes)
        $safeCode = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $code);

        // Define the file path
        $filePath = $directory . $safeCode . '.png';

        // Save the barcode image to the file
        file_put_contents($filePath, $barcode);

        // Return the relative URL to the barcode image
        return base_url('modules/courier_goshipping/assets/barcodes/' . $safeCode . '.png');
    }

    private function add_client()
    {

        if (!empty($this->input->post('sender_first_name'))) {
            // Prepare client data
            $client_data = array(
                'company' => $this->input->post('sender_first_name') . ' ' . $this->input->post('sender_last_name'),
                'phonenumber' => $this->input->post('sender_phone_number'),
                'address' => $this->input->post('sender_address'),
                'zip' => $this->input->post('sender_zipcode'),
            );
        } else {

            $client_data = array(
                'company' => $this->input->post('company_name'),
                'phonenumber' => $this->input->post('contact_phone'),
                'address' => $this->input->post('contact_address'),
                'zip' => $this->input->post('contact_zipcode'),
            );

        }

        // Insert client data into the database
        return $this->Client_model->insert_client($client_data);
    }


    // Controller method to handle shipment data
    public function store_shipment_data($client_id, $sender_id, $recipient_id, $waybill_number, $recipient_company_id)
    {

        // Prepare shipment data
        $shipment_id = $this->add_shipment($client_id, $sender_id, $recipient_id, $waybill_number, $recipient_company_id);

        if ($shipment_id === false) {
            set_alert('danger', 'Failed to add shipment.');
            redirect(admin_url('courier_goshipping/pickups/create'));
        }

        return $shipment_id;
    }


    private function add_shipment($client_id, $sender_id, $recipient_id, $waybill_number, $recipient_company_id)
    {


        // Get shipping mode and convert to lowercase
        $shipping_mode_lower = strtolower($this->input->post('shipping_mode'));

        $packaging_charges = 0;

        if (!empty($this->input->post('packaging_charges'))) {
            $packaging_charges = $this->input->post('packaging_charges');
        }

        // VAT calculation
        $vat_applicable = $this->input->post('vat_applicable') ? 1 : 0;
        $vat_rate       = (float) ($this->input->post('vat_rate') ?: get_option('courier_vat_rate') ?: 16);
        $vat_amount     = 0.00;
        if ($vat_applicable) {
            $vat_amount = round($packaging_charges * ($vat_rate / 100), 2);
        }

        // Prepare shipment data
        $shipment_data = [
            'status_id' => 1,
            'export' => $this->input->post('export_import') === 'export' ? 1 : 0,
            'import' => $this->input->post('export_import') === 'import' ? 1 : 0,
            'shipping_mode' => $this->input->post('shipping_mode'),
            'shipping_category' => $this->input->post('type'),
            'tracking_id' => $waybill_number,
            'waybill_number' => $waybill_number,
            'courier_company_id' => ((int)$this->input->post('courier_company_id') > 0)
                ? (int)$this->input->post('courier_company_id')
                : NULL,
            'sender_id' => $this->input->post('sender_type') === 'individual' ? $sender_id : NULL,
            'fcl_shipment' => str_contains($shipping_mode_lower, 'sea') && str_contains($shipping_mode_lower, 'fcl') ? 1 : 0,
            'recipient_id' => $this->input->post('recipient_type') === 'individual' ? $recipient_id : NULL,
            'company_id' => $this->input->post('sender_type') === 'company' ? $client_id : NULL,
            'recipient_company_id' => $this->input->post('recipient_type') === 'company' ? $recipient_company_id : NULL,
            'staff_id' => get_staff_user_id(),
            'packaging_charges' => $packaging_charges,
            'vat_applicable' => $vat_applicable,
            'vat_rate' => $vat_applicable ? $vat_rate : 0,
            'vat_amount' => $vat_amount,
            'is_round_trip' => $this->input->post('is_round_trip') ? 1 : 0,
            'payment_terms' => (get_option('courier_payment_terms_mode') === 'automatic')
                ? (get_option('courier_default_payment_terms') ?: null)
                : ($this->input->post('payment_terms') ?: null),
            'created_at' => $this->_parse_shipment_date() . ' ' . date('H:i:s'),
            'company_type' => $this->input->post('company_type'),
            'branch_id' => courier_get_session_branch_id(),
        ];

        if ($this->db->field_exists('goods_declared_value', db_prefix() . '_shipments')) {
            $shipment_data['goods_declared_value'] = (float) ($this->input->post('goods_declared_value') ?: 0);
        }

        $special_instructions = trim($this->input->post('special_instructions') ?: '');
        if ($special_instructions !== '' && $this->db->field_exists('special_instructions', db_prefix() . '_shipments')) {
            $shipment_data['special_instructions'] = $special_instructions;
        }

        $shipment_id = $this->Shipment_model->add($shipment_data);

        // Record the status change in the shipment_status_histories table
        $this->db->insert(db_prefix() . '_shipment_status_history', [
            'shipment_id' => $shipment_id,
            'status_id' => 1,
            'changed_at' => $this->_parse_shipment_date() . ' ' . date('H:i:s'),
        ]);

        // Insert shipment data into the database
        return $shipment_id;
    }

    // Controller method to handle pickup data
    public function store_pickup_data($shipment_id)
    {
        if ($this->input->post('hasPickup') !== null) {
            // Store contact person data
            $contact_person_data = [
                'first_name' => $this->input->post('pickup_contact_first_name'),
                'last_name' => $this->input->post('pickup_contact_last_name'),
                'phone_number' => $this->input->post('pickup_country_code') . $this->input->post('pickup_contact_phone_number'),
                'email' => $this->input->post('pickup_contact_email')
            ];

            $contact_id = $this->PickupContact_model->add($contact_person_data);

            if ($contact_id === false) {
                set_alert('danger', 'Failed to add pickup contact person.');
                redirect(admin_url('courier_goshipping/pickups/create'));
            }

            // Store pickup data
            $pickup_data = [
                'pickup_date'       => strtoupper($this->input->post('pickup_date')),
                'pickup_start_time' => strtoupper($this->input->post('pickup_start_time')),
                'pickup_end_time'   => strtoupper($this->input->post('pickup_end_time')),
                'country_id'        => $this->input->post('pickup_country_id'),
                'branch_id'         => courier_get_session_branch_id(),
                'state_id'          => $this->input->post('pickup_state_id'),
                'address'           => $this->input->post('pickup_address'),
                'pickup_zip'        => $this->input->post('pickup_zipcode'),
                'address_type'      => $this->input->post('pickup_address_type'),
                'vehicle_type'      => strtoupper($this->input->post('pickup_vehicle_type')),
                'contact_person_id' => $contact_id,
                'shipment_id'       => $shipment_id,
                'staff_id'          => get_staff_user_id(),
                'driver_id'         => $this->input->post('pickup_driver_id'),
                'source'            => 'shipment',
                'created_at'        => date('Y-m-d H:i:s'),
            ];

            $pickup_id = $this->Pickup_model->add($pickup_data);

            if ($pickup_id === false) {
                set_alert('danger', 'Failed to add pickup.');
                redirect(admin_url('courier_goshipping/pickups/create'));
            }
        }
    }

    private function process_invoice_and_packages($shipment_id, $waybill_number, $shipping_mode, $client_id, $mode_type, $data, $commercial_value_data = null)
    {

        $data['sender_address_type'] = str_replace('_', ' ', $this->input->post('sender_address_type'));
        $data['recipient_address_type'] = str_replace('_', ' ', $this->input->post('recipient_address_type'));

        if ($this->input->post('sender_type') === 'company') {
            $data['sender_address'] = $this->input->post('contact_address');
            $data['sender_address_type'] = str_replace('_', ' ', $this->input->post('contact_address_type'));
            $data['sender_zipcode'] = $this->input->post('contact_zipcode');
            $data['sender_country_id'] = !is_null($this->input->post('contact_country_id')) ? $this->input->post('contact_country_id') : 0;
        }

        if ($this->input->post('recipient_type') === 'company') {
            $data['recipient_address'] = $this->input->post('recipient_contact_address');
            $data['recipient_address_type'] = str_replace('_', ' ', $this->input->post('recipient_contact_address_type'));
            $data['recipient_zipcode'] = $this->input->post('recipient_contact_zipcode');
            $data['recipient_country_id'] = !is_null($this->input->post('recipient_contact_country_id')) ? $this->input->post('recipient_contact_country_id') : 0;
        }

        $posted_currency = (int)$this->input->post('currency_id');
        $currency_id = $posted_currency > 0 ? $posted_currency : get_base_currency()->id;

        // Create invoice header with placeholder totals (updated at the end)
        $shipment_date = $this->_parse_shipment_date();
        $due_days      = max(0, (int)($this->input->post('invoice_due_days') ?: 30));
        $invoice_data = [
            'clientid'        => $client_id,
            'number'          => get_option('next_invoice_number'),
            'date'            => $shipment_date,
            'duedate'         => date('Y-m-d', strtotime($shipment_date . ' +' . $due_days . ' days')),
            'currency'        => $currency_id,
            'subtotal'        => 0,
            'total'           => 0,
            'status'          => 1,
            'billing_street'  => $data['sender_address'],
            'billing_zip'     => $data['sender_zipcode'],
            'billing_country' => !is_null($data['sender_country_id']) ? $data['sender_country_id'] : 0,
        ];

        $sender_country_id = !is_null($data['sender_country_id']) ? $data['sender_country_id'] : 0;
        $receiver_country_id = !is_null($data['recipient_country_id']) ? $data['recipient_country_id'] : 0;

        $sender_country = $this->CountryState_model->get_country_name_by_id($sender_country_id) ?? '';
        $receiver_country = $this->CountryState_model->get_country_name_by_id($receiver_country_id) ?? '';


        $invoice_id = $this->invoices_model->add($invoice_data);

        $is_local = (get_option('courier_type') === 'local')
                 || (get_option('courier_type') !== 'local' && $this->input->post('type') === 'domestic');

        // Rate lookup for international non-FCL modes
        $rate_map = [
            'lcl'               => 'courier_rate_sea_lcl',
            'sea_consolidation' => 'courier_rate_sea_consolidation',
            'air_freight'       => 'courier_rate_air_freight',
            'air_consolidation' => 'courier_rate_air_consolidation',
        ];
        $rate_option = $rate_map[$mode_type] ?? 'courier_rate_road';
        $mode_rate   = (float)(get_option($rate_option) ?: 1);

        $total_amount     = 0;  // monetary total for invoice
        $total_chargeable = 0;  // chargeable weight/CBM (used as invoice line qty)

        // ── Process packages ─────────────────────────────────────────────────
        foreach ($data['quantities'] as $i => $quantity) {

            if ($mode_type === 'fcl') {
                $fcl_opt        = $data['fcl_options'][$i] ?? '';
                $fcl_opt_key    = strtolower(str_replace(["'", " "], "", $fcl_opt));
                $container_rate = (float)(get_option('courier_rate_sea_fcl_' . $fcl_opt_key) ?: 1);
                $total_amount  += (int)$quantity * $container_rate;

                $this->ShipmentFCLPackage_model->add([
                    'shipment_id' => $shipment_id,
                    'quantity'    => $quantity,
                    'description' => $data['descriptions'][$i],
                    'fcl_option'  => $fcl_opt,
                ]);

            } elseif ($is_local) {
                $unit_price    = isset($data['unit_prices'][$i]) ? (float)$data['unit_prices'][$i] : 0;
                $total_amount += (int)$quantity * $unit_price;

                $this->ShipmentPackage_model->add([
                    'shipment_id'       => $shipment_id,
                    'quantity'          => $quantity,
                    'description'       => $data['descriptions'][$i],
                    'weight'            => $data['weights'][$i],
                    'length'            => $data['lengths'][$i],
                    'width'             => $data['widths'][$i],
                    'height'            => $data['heights'][$i],
                    'weight_volume'     => $data['weight_volumes'][$i],
                    'chargeable_weight' => $data['chargeable_weights'][$i],
                    'unit_price'        => $unit_price,
                    'pod'               => isset($data['pods'][$i]) ? trim($data['pods'][$i]) : null,
                ]);

            } else {
                // International non-FCL: JS already stored chargeable_weight × quantity
                $total_chargeable += (float)($data['chargeable_weights'][$i] ?? 0);

                $this->ShipmentPackage_model->add([
                    'shipment_id'       => $shipment_id,
                    'quantity'          => $quantity,
                    'description'       => $data['descriptions'][$i],
                    'weight'            => $data['weights'][$i],
                    'length'            => $data['lengths'][$i],
                    'width'             => $data['widths'][$i],
                    'height'            => $data['heights'][$i],
                    'weight_volume'     => $data['weight_volumes'][$i],
                    'chargeable_weight' => $data['chargeable_weights'][$i],
                    'unit_price'        => null,
                    'pod'               => isset($data['pods'][$i]) ? trim($data['pods'][$i]) : null,
                ]);
            }
        }

        // For international non-FCL, convert total chargeable weight to monetary amount
        if (!$is_local && $mode_type !== 'fcl') {
            $total_amount = $total_chargeable * $mode_rate;
        }

        // ── Build shared route description ───────────────────────────────────
        $route_long_desc = '<strong>SHIPPING MODE - </strong>' . strtoupper($shipping_mode) . "\n"
            . '<strong>FROM:</strong> ' . strtoupper($sender_country) . ' ' . strtoupper($data['sender_address']) . ', ' . ucfirst($data['sender_address_type']) . ' ' . strtoupper($data['sender_zipcode']) . " &nbsp;|&nbsp; "
            . '<strong>TO:</strong> '  . strtoupper($receiver_country) . ' ' . strtoupper($data['recipient_address']) . ', ' . ucfirst($data['recipient_address_type']) . ' ' . strtoupper($data['recipient_zipcode']);

        // ── Create invoice line items ─────────────────────────────────────────
        if ($is_local) {
            // Local courier: one line per package — every item gets full route + its POD
            $local_mode_line = '<strong>SHIPPING MODE - </strong>' . strtoupper($shipping_mode);
            $local_from_line = '<strong>FROM:</strong> ' . strtoupper($data['sender_address']);
            $local_to_line   = '<strong>TO:</strong> ' . strtoupper($data['recipient_address']);

            foreach ($data['quantities'] as $i => $pkg_qty) {
                $pkg_price = isset($data['unit_prices'][$i]) ? (float)$data['unit_prices'][$i] : 0;
                $pkg_desc  = isset($data['descriptions'][$i]) ? $data['descriptions'][$i] : 'Package ' . ($i + 1);
                $pkg_pod   = isset($data['pods'][$i]) ? trim($data['pods'][$i]) : '';
                // Every package item carries route + its own POD (so all deliveries are visible)
                $long_desc = $local_mode_line . " &nbsp;|&nbsp; " . $local_from_line . " &nbsp;|&nbsp; " . $local_to_line;
                if ($pkg_pod !== '') {
                    $long_desc .= " &nbsp;|&nbsp; <strong>POD:</strong> " . strtoupper($pkg_pod);
                }
                $this->Shipment_model->add_invoice_item([
                    'description'      => 'WAYBILL - ' . strtoupper($waybill_number) . ' | ' . $pkg_desc,
                    'long_description' => $long_desc,
                    'qty'              => (int)$pkg_qty,
                    'rate'             => $pkg_price,
                    'item_order'       => $i + 1,
                    'rel_id'           => $invoice_id,
                    'rel_type'         => 'invoice',
                    'unit'             => '',
                ]);
            }

        } elseif ($mode_type === 'fcl') {
            // Sea FCL: one invoice line per container row
            foreach ($data['quantities'] as $i => $fcl_qty) {
                $fcl_opt        = $data['fcl_options'][$i] ?? '';
                $fcl_opt_key    = strtolower(str_replace(["'", " "], "", $fcl_opt));
                $container_rate = (float)(get_option('courier_rate_sea_fcl_' . $fcl_opt_key) ?: 1);
                $pkg_desc       = isset($data['descriptions'][$i]) ? $data['descriptions'][$i] : '';
                $this->Shipment_model->add_invoice_item([
                    'description'      => 'WAYBILL - ' . strtoupper($waybill_number) . ' | ' . $fcl_opt . ($pkg_desc ? ' — ' . $pkg_desc : ''),
                    'long_description' => ($i === 0) ? $route_long_desc : '',
                    'qty'              => (int)$fcl_qty,
                    'rate'             => $container_rate,
                    'item_order'       => $i + 1,
                    'rel_id'           => $invoice_id,
                    'rel_type'         => 'invoice',
                    'unit'             => 'containers',
                ]);
            }

        } else {
            // International non-FCL: single line — qty = total chargeable weight, rate = mode rate
            $unit_label = ($mode_type === 'sea_consolidation') ? 'CBM' : 'kgs';
            $this->Shipment_model->add_invoice_item([
                'description'      => 'WAYBILL - ' . strtoupper($waybill_number) . "\n\n",
                'long_description' => $route_long_desc,
                'qty'              => $total_chargeable,
                'rate'             => $mode_rate,
                'item_order'       => 1,
                'rel_id'           => $invoice_id,
                'rel_type'         => 'invoice',
                'unit'             => $unit_label,
            ]);
        }

        // ── Packaging charges ────────────────────────────────────────────────
        $packaging_charges = (float)$this->input->post('packaging_charges');
        if ($packaging_charges > 0) {
            $pkg_order = ($mode_type === 'fcl') ? count($data['quantities']) + 1 : 2;
            $this->Shipment_model->add_invoice_item([
                'description'      => 'PACKAGING',
                'long_description' => 'Packaging Charges',
                'qty'              => 1,
                'rate'             => $packaging_charges,
                'item_order'       => $pkg_order,
                'rel_id'           => $invoice_id,
                'rel_type'         => 'invoice',
                'unit'             => '',
            ]);
        }

        // ── Commercial invoice items ─────────────────────────────────────────
        if (is_array($commercial_value_data) && !empty($commercial_value_data['commodity_quantity'])) {
            foreach ($commercial_value_data['commodity_quantity'] as $i => $quantity) {
                $this->CommercialValueItems_model->add([
                    'shipment_id'    => $shipment_id,
                    'quantity'       => $quantity,
                    'description'    => $commercial_value_data['commodity_description'][$i],
                    'declared_value' => $commercial_value_data['declared_value'][$i],
                ]);
            }
        } else {
            $commercial_invoice_url = $this->upload_commercial_invoice_attachment();
            $this->Shipment_model->update($shipment_id, [
                'commercial_invoice_url' => $commercial_invoice_url,
            ]);
        }

        // ── Update invoice totals ────────────────────────────────────────────
        $invoice_total = $total_amount + $packaging_charges;
        $this->Shipment_model->update_invoice($invoice_id, [
            'subtotal' => $invoice_total,
            'total'    => $invoice_total,
        ]);

        $this->Shipment_model->update($shipment_id, ['invoice_id' => $invoice_id]);

        return $invoice_id;
    }


    // Function to store company details if necessary
    private function store_company_if_needed($sender_type)
    {
        // Initialize company ID
        $company_id = '';

        // Store company details if the sender is a company
        if ($sender_type === 'company') {
            $company_data = [
                'company_name' => $this->input->post('company_name'),
                'contact_person_name' => $this->input->post('contact_name'),
                'contact_person_phone_number' => $this->input->post('contact_country_code') . $this->input->post('contact_phone'),
                'contact_person_email' => $this->input->post('contact_email'),
                'contact_state_id' => $this->input->post('contact_state_id'),
                'contact_country_id' => $this->input->post('contact_country_id'),
                'contact_address_type' => $this->input->post('contact_address_type'),
                'contact_address' => $this->input->post('contact_address'),
                'contact_zipcode' => $this->input->post('contact_zipcode'),
                'kra_pin' => $this->input->post('company_kra_pin'),
            ];

            $company_id = $this->ShipmentCompany_model->add($company_data);

        }


        return $company_id;
    }


    // Function to store recipient company details if necessary
    private function store_recipient_company_if_needed($recipient_type)
    {
        // Initialize company ID
        $recipient_company_id = '';

        // Store company details if the sender is a company
        if ($recipient_type === 'company') {
            $company_data = [
                'recipient_company_name' => $this->input->post('recipient_company_name'),
                'recipient_contact_person_name' => $this->input->post('recipient_contact_name'),
                'recipient_contact_person_phone_number' => $this->input->post('recipient_contact_country_code') . $this->input->post('recipient_contact_phone'),
                'recipient_contact_person_email' => $this->input->post('recipient_contact_email'),
                'recipient_contact_state_id' => $this->input->post('recipient_contact_state_id'),
                'recipient_contact_country_id' => $this->input->post('recipient_contact_country_id'),
                'recipient_contact_address_type' => $this->input->post('recipient_contact_address_type'),
                'recipient_contact_address' => $this->input->post('recipient_contact_address'),
                'recipient_contact_zipcode' => $this->input->post('recipient_contact_zipcode'),
            ];

            $recipient_company_id = $this->ShipmentRecipientCompany_model->add($company_data);

        }

        return $recipient_company_id;
    }


    public function upload_commercial_invoice_attachment()
    {
        $upload_path = FCPATH . 'modules/courier_goshipping/assets/commercial_invoices/';

        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $file_name = time() . '_' . $_FILES['commercial_invoice_file']['name'];
        $file_path = $upload_path . $file_name;

        if (move_uploaded_file($_FILES['commercial_invoice_file']['tmp_name'], $file_path)) {
            return 'modules/courier_goshipping/assets/commercial_invoices/' . $file_name;
        } else {
            set_alert('danger', 'File upload failed.');
        }

    }


    public function filterSenderCountries()
    {
        $countries = [];

        if (is_admin()) {
            $countries = $this->Shipment_model->get_countries();
        } else {
            $staff_id = get_staff_user_id();
            $country_id = $this->db->select('country_id')
                ->from(db_prefix() . '_courier_audit_logs')
                ->where('staff_id', $staff_id)
                ->get()
                ->row();
            if ($country_id) {
                $countries = $this->Shipment_model->get_countries($country_id->country_id);
            } else {
                $countries = $this->Shipment_model->get_countries();
            }
        }

        return $countries;
    }

    public function getStaffCountry()
    {

        $staff_id = get_staff_user_id();
        $this->db->select('email');
        $this->db->from(db_prefix() . 'staff');
        $this->db->where('staffid', $staff_id);
        $query = $this->db->get();
        $email = $query->row()->email;


        $this->db->select('value');
        $this->db->from(db_prefix() . 'staff s');
        $this->db->join(db_prefix() . 'customfieldsvalues c', 's.staffid = c.relid');
        $this->db->where('s.email', $email);

        $query = $this->db->get();

        // Check if we got a result
        if ($query->num_rows() > 0) {
            $row = $query->row();
            $short_name = $row->value;

            $this->db->from(db_prefix() . 'countries');
            $this->db->where('short_name', $short_name);
            $country_query = $this->db->get();

            if ($country_query->num_rows() > 0) {
                return $country_query->row();
            } else {
                return null;
            }
        } else {
            return null;
        }


    }

    private function prepare_shipment_data($company_id, $sender_id, $recipient_id, $waybill_number, $recipient_company_id)
    {

        if (!empty($company_id) && !empty($recipient_company_id)) {
            return $this->store_shipment_data(
                $company_id,
                NULL,
                $recipient_id,
                $waybill_number,
                $recipient_company_id
            );
        }


        if (!empty($company_id) && empty($recipient_company_id)) {
            return $this->store_shipment_data(
                $company_id,
                NULL,
                $recipient_id,
                $waybill_number,
                NULL
            );
        }

        if (empty($company_id) && !empty($recipient_company_id)) {
            return $this->store_shipment_data(
                NULL,
                $sender_id,
                $recipient_id,
                $waybill_number,
                $recipient_company_id
            );
        }

        if (empty($company_id) && empty($recipient_company_id)) {
            return $this->store_shipment_data(
                NULL,
                $sender_id,
                $recipient_id,
                $waybill_number,
                NULL
            );
        }

    }


    /**
     * @throws Exception
     */
    public function store()
    {

        $this->validatePickup();
        $this->validateCompany();
        $this->validateSender();
        $this->validateRecipient();

        if ($this->input->post('mode_type') === 'fcl') {
            $this->validateFCLPackages();
        } else {
            $this->validateNonFCLPackages();
        }


        $this->validateCommercialValueItems();
        $this->validateShipment();


        if ($this->form_validation->run() === FALSE) {

            // Resolve logistic company with fallback (same logic as create())
            $_lc_raw_s = get_option('courier_logistic_company');
            $_lc_s     = (!empty($_lc_raw_s) && $_lc_raw_s !== 'GO Shipping')
                ? $_lc_raw_s
                : (get_option('companyname') ?: '');

            $data = [
                'currencies'        => $this->Shipment_model->get_currencies(),
                'countries'         => $this->filterSenderCountries(),
                'type'              => $this->input->post('type'),
                'mode'              => $this->input->post('mode'),
                'mode_type'         => $this->input->post('mode_type'),
                'show_pickup_section'                        => $this->input->post('hasPickup') !== null,
                'show_company_section'                       => $this->input->post('sender_type') === 'company',
                'show_recipient_company_section'             => $this->input->post('recipient_type') === 'company',
                'dimensional_factor'                         => $this->DimensionalFactor_model->get(),
                'show_commercial_value_attachment_section'   => $this->input->post('hasCommercialInvoiceAttachment') !== null,

                // Variables required by the view that were missing on re-render
                'courier_logistic_company' => $_lc_s,
                'user_country'             => $this->getStaffCountry(),
                'courier_companies'        => $this->CourierCompany_model->get(),
                'current_staff'            => $this->db
                    ->select('firstname, lastname, email, phonenumber')
                    ->from(db_prefix() . 'staff')
                    ->where('staffid', get_staff_user_id())
                    ->get()->row(),

                // Pass selected country values
                'recipient_country_id'         => $this->input->post('recipient_country_id'),
                'recipient_contact_country_id' => $this->input->post('recipient_contact_country_id'),
                'sender_country_id'            => $this->input->post('sender_country_id'),
                'contact_country_id'           => $this->input->post('contact_country_id'),
                'pickup_country_id'            => $this->input->post('pickup_country_id'),

                // Pass selected state values
                'recipient_state_id'         => $this->input->post('recipient_state_id'),
                'sender_state_id'            => $this->input->post('sender_state_id'),
                'contact_state_id'           => $this->input->post('contact_state_id'),
                'recipient_contact_state_id' => $this->input->post('recipient_contact_state_id'),
                'pickup_state_id'            => $this->input->post('pickup_state_id'),
                'drivers'                       => $this->Driver_model->get(),
                'recipient_countries'           => $this->Shipment_model->get_countries(),
                'courier_payment_terms_mode'    => get_option('courier_payment_terms_mode') ?: 'manual',
            ];

            $this->load->view('shipments/create', $data);

        } else {

            // Attempt to insert data
            try {

                $company_id = $this->store_company_if_needed($this->input->post('sender_type'));
                $recipient_company_id = $this->store_recipient_company_if_needed($this->input->post('recipient_type'));
                $recipient_id = $this->store_recipient_data();
                $sender_id = $this->store_sender_data();
                $client_id = $this->store_client_data();
                $waybill_number = $this->generateWaybillNumber();
                $shipment_id = $this->prepare_shipment_data($company_id, $sender_id, $recipient_id, $waybill_number, $recipient_company_id);
                $this->store_pickup_data($shipment_id);

                // Collect POST data
                $data = [
                    'sender_address' => $this->input->post('sender_address'),
                    'sender_zipcode' => $this->input->post('sender_zipcode'),
                    'sender_country_id' => $this->input->post('sender_country_id'),
                    'recipient_address' => $this->input->post('recipient_address'),
                    'recipient_zipcode' => $this->input->post('recipient_zipcode'),
                    'recipient_country_id' => $this->input->post('recipient_country_id'),
                    'quantities' => $this->input->post('amount'),
                    'descriptions' => $this->input->post('package_description'),
                ];

                $commercial_value_data = null;

                //commercial Value Data
                if ($this->input->post('hasCommercialInvoiceAttachment') === null) {
                    $commercial_value_data['commodity_quantity'] = $this->input->post('commodity_quantity');
                    $commercial_value_data['commodity_description'] = $this->input->post('commodity_description');
                    $commercial_value_data['declared_value'] = $this->input->post('declared_value');
                }

                //Package Data
                $data['unit_prices'] = $this->input->post('unit_price') ?: [];
                $data['pods']        = $this->input->post('pod') ?: [];
                if ($this->input->post('mode_type') === 'fcl') {
                    $data['fcl_options'] = $this->input->post('fcl_options');
                    $invoice_id = $this->process_invoice_and_packages($shipment_id, $waybill_number, $this->input->post('shipping_mode'), $client_id, 'fcl', $data, $commercial_value_data);
                } else {
                    $data['weights'] = $this->input->post('weight');
                    $data['lengths'] = $this->input->post('length');
                    $data['widths'] = $this->input->post('width');
                    $data['heights'] = $this->input->post('height');
                    $data['weight_volumes'] = $this->input->post('weight_vol');
                    $data['chargeable_weights'] = $this->input->post('chargeable_weight');
                    $actual_mode_type = $this->input->post('mode_type') ?: 'none';
                    $invoice_id = $this->process_invoice_and_packages($shipment_id, $waybill_number, $this->input->post('shipping_mode'), $client_id, $actual_mode_type, $data, $commercial_value_data);
                }

                set_alert('success', 'Shipment added successfully.');

                $type = $this->input->post('type');
                $mode = $this->input->post('mode');
                $mode_type = $this->input->post('mode_type');

		$this->session->unset_userdata('shipment_details');
		$this->session->unset_userdata('no_shipments');


                if ($type !== 'domestic') {
                    redirect(admin_url('courier_goshipping/shipments') . '?type=' . $type . '&mode=' . $mode . '&mode_type=' . $mode_type);
                } else {
                    redirect(admin_url('courier_goshipping/shipments') . '?type=' . $type);
                }


            } catch (Exception $e) {


                $data = [
                    'currencies' => $this->Shipment_model->get_currencies(),
                    'type' => $this->input->post('type'),
                    'countries' => $this->Shipment_model->get_countries(),
                    'mode' => $this->input->post('mode'),
                    'mode_type' => $this->input->post('mode_type'),
                    'show_pickup_section' => $this->input->post('hasPickup') !== null,
                    'show_company_section' => $this->input->post('sender_type') === 'company',
                    'show_recipient_company_section' => $this->input->post('recipient_type') === 'company',
                ];

                // Log the error message
                log_message('error', $e->getMessage());

                $error_code = $this->db->error()['code'];
                $error_message = ($error_code == 1062)
                    ? 'This email address already exists.'
                    : 'An error occurred: ' . $e->getMessage();

                set_alert('danger', $error_message);

                redirect(admin_url('courier_goshipping/shipments/create'), $data);

            }
        }

    }


    private function generateWaybillNumber(): string
    {
        $prefix = get_option('courier_waybill_prefix');

        if (empty($prefix)) {
            // Try the logistic company name from General Settings first
            $logistic_company = get_option('courier_logistic_company');
            if (!empty($logistic_company)) {
                $prefix = strtoupper(substr(str_replace(' ', '', $logistic_company), 0, 4));
            } else {
                // Legacy fallback: derive from the courier company record
                $company_id = (int) $this->input->post('courier_company_id');
                if ($company_id > 0) {
                    $courierCompany = $this->CourierCompany_model->get_by_id($company_id);
                    if ($courierCompany) {
                        $prefix = strtoupper(substr(str_replace(' ', '', $courierCompany->company_name), 0, 4));
                    }
                }
                if (empty($prefix)) {
                    $prefix = 'WAY'; // safe last-resort default
                }
            }
        }

        do {
            $randomNumber   = random_int(100000, 999999);
            $waybill_number = $prefix . $randomNumber;
            $existingWaybill = $this->db->get_where(db_prefix() . '_shipments', ['waybill_number' => $waybill_number])->row();
        } while ($existingWaybill);

        return $waybill_number;
    }


    public function list_invoices()
    {
        if (!is_admin()
            && !staff_can('view_invoices', 'courier-invoices')
            && !staff_can('view_own_invoices', 'courier-invoices')) {
            access_denied('Courier - Invoices');
        }

        $can_all = is_admin() || staff_can('view_invoices', 'courier-invoices');
        $staff_id = $can_all ? null : get_staff_user_id();

        $data['invoices'] = $this->Shipment_model->get_invoices_by_shipment_invoice_ids($staff_id);
        $this->load->view('shipments/invoices', $data);
    }

    /**
     * List all shipments that have commercial invoice data
     * (either commercial_values_items rows OR a commercial_invoice_url on file).
     */
    public function list_commercial_invoices()
    {
        $sql = 'SELECT DISTINCT
                    s.id, s.waybill_number, s.tracking_id, s.shipping_mode,
                    s.shipping_category, s.commercial_invoice_url, s.created_at,
                    ss.description AS status_description,
                    sr.first_name AS recip_first, sr.last_name AS recip_last,
                    rc.recipient_company_name AS recip_company,
                    sn.first_name AS sender_first, sn.last_name AS sender_last,
                    sc.company_name AS sender_company,
                    s.sender_id, s.recipient_id
                FROM ' . db_prefix() . '_shipments s
                LEFT JOIN ' . db_prefix() . '_shipment_statuses ss  ON ss.id  = s.status_id
                LEFT JOIN ' . db_prefix() . '_shipment_recipients sr ON sr.id  = s.recipient_id
                LEFT JOIN ' . db_prefix() . '_recipient_companies rc ON rc.id  = s.recipient_company_id
                LEFT JOIN ' . db_prefix() . '_shipment_senders sn   ON sn.id  = s.sender_id
                LEFT JOIN ' . db_prefix() . '_shipment_companies sc  ON sc.id  = s.company_id
                WHERE EXISTS (
                    SELECT 1 FROM ' . db_prefix() . '_commercial_values_items ci
                    WHERE ci.shipment_id = s.id
                )
                OR s.commercial_invoice_url IS NOT NULL
                ORDER BY s.created_at DESC';

        $data['commercial_invoices'] = $this->db->query($sql)->result();
        $this->load->view('shipments/list_commercial_invoices', $data);
    }

    // ── Helper: load invoice line items for a shipment ───────────────────────
    private function get_invoice_items($invoice_id)
    {
        if (empty($invoice_id)) return [];
        return $this->db->get_where(db_prefix() . 'itemable', [
            'rel_id'   => $invoice_id,
            'rel_type' => 'invoice',
        ])->result();
    }

    // ── Helper: enforce agent ownership ─────────────────────────────────────
    /**
     * Branch isolation for single-record document endpoints (waybill,
     * invoice, manifest, etc.) — enforced independently of the staff-creator
     * check below and of any "view all shipments" permission, so an agent
     * with broad view permissions still can't open another branch's
     * document just by guessing/reusing its numeric ID.
     */
    private function _assert_branch_ownership($details)
    {
        if (courier_staff_can_view_all_branches()) {
            return true;
        }
        $shipment_branch = (int) ($details['shipment']->branch_id ?? 0);
        if ($shipment_branch <= 0) {
            return true; // legacy/unscoped shipment predating branch isolation
        }
        if (!in_array($shipment_branch, courier_get_staff_branch_ids(), true)) {
            set_alert('danger', 'Access denied — this shipment belongs to another branch.');
            redirect(admin_url('courier_goshipping/shipments/main'));
            return false;
        }
        return true;
    }

    private function _assert_ownership($details)
    {
        if (!$this->_assert_branch_ownership($details)) {
            return false;
        }
        if (is_admin() || staff_can('view_all_shipments', 'courier-shipments')) {
            return true;
        }
        if ((int)$details['shipment']->staff_id !== (int)get_staff_user_id()) {
            set_alert('danger', 'Access denied — this shipment does not belong to you.');
            redirect(admin_url('courier_goshipping/shipments/main'));
            return false;
        }
        return true;
    }

    // ── Helper: shared redirect guard ────────────────────────────────────────
    private function load_shipment_or_redirect($id, $ignore_ownership = false)
    {
        $details = $this->Shipment_model->get_shipment_details((int)$id);
        if (empty($details)) {
            set_alert('danger', 'Shipment not found.');
            redirect(admin_url('courier_goshipping/shipments/main'));
            return null;
        }
        // Branch isolation is never skippable, even when $ignore_ownership
        // (staff-creator check) is bypassed for staff with broad view permissions.
        if (!$this->_assert_branch_ownership($details)) {
            return null;
        }
        if (!$ignore_ownership && !$this->_assert_ownership($details)) {
            return null;
        }
        return $details;
    }

    /**
     * Printable Quotation document for a shipment.
     */
    public function quotation($id)
    {
        $details = $this->load_shipment_or_redirect($id);
        if (!$details) return;

        $data['shipment_details'] = $details;
        $data['invoice_items']    = $this->get_invoice_items($details['shipment']->invoice_id);
        $data['current_date']     = date('F j, Y');
        $data['valid_until']      = date('F j, Y', strtotime('+30 days'));
        $this->load->view('shipments/quotation', $data);
    }

    /**
     * Printable Courier Invoice document for a shipment.
     */
    public function courier_invoice($id)
    {
        $can_view_all = is_admin() || staff_can('view_invoices', 'courier-invoices');
        if (!$can_view_all
            && !staff_can('view_own_invoices', 'courier-invoices')) {
            access_denied('Courier - Invoices');
        }
        $details = $this->load_shipment_or_redirect($id, $can_view_all);
        if (!$details) return;

        $this->load->model('payment_modes_model');
        $this->load->model('payments_model');
        $data['offline_modes']   = $this->payment_modes_model->get('', [], false);
        $data['online_gateways'] = $this->payment_modes_model->get_payment_gateways(false);

        $data['shipment_details'] = $details;
        $data['invoice_items']    = $this->get_invoice_items($details['shipment']->invoice_id);

        // Use the Perfex invoice's actual date (set at shipment creation) rather than today
        $inv_id = (int)($details['shipment']->invoice_id ?? 0);
        $inv_row = $inv_id > 0
            ? $this->db->get_where(db_prefix() . 'invoices', ['id' => $inv_id])->row()
            : null;
        $base_date = ($inv_row && !empty($inv_row->date) && $inv_row->date !== '0000-00-00')
            ? $inv_row->date
            : ($details['shipment']->created_at ?? date('Y-m-d'));
        $data['current_date'] = date('F j, Y', strtotime($base_date));
        $data['due_date'] = ($inv_row && !empty($inv_row->duedate) && $inv_row->duedate !== '0000-00-00')
            ? date('F j, Y', strtotime($inv_row->duedate))
            : date('F j, Y', strtotime($base_date . ' +14 days'));

        // All payments already recorded against this invoice (for reprint)
        $inv_id_for_pmts = (int)($details['shipment']->invoice_id ?? 0);
        $data['invoice_payments'] = $inv_id_for_pmts > 0
            ? $this->payments_model->get_invoice_payments($inv_id_for_pmts)
            : [];

        // Issued by: name of staff who created the shipment
        $staff_row = $this->db->get_where(db_prefix() . 'staff', ['staffid' => $details['shipment']->staff_id])->row();
        $data['issued_by'] = $staff_row ? trim($staff_row->firstname . ' ' . $staff_row->lastname) : 'N/A';

        // Station: agent station linked to that staff member
        $agent_row = $this->db->get_where(db_prefix() . '_agents', ['staff_id' => $details['shipment']->staff_id])->row();
        $data['agent_station'] = ($agent_row && !empty($agent_row->station)) ? $agent_row->station : '';

        $this->load->view('shipments/courier_invoice', $data);
    }

    /**
     * Record a payment against the courier shipment's invoice.
     * Expects AJAX POST with: amount, paymentmode, payment_date, note
     */
    public function record_courier_payment($shipment_id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->load->model('invoices_model');
        $this->load->model('payments_model');
        $this->load->model('payment_modes_model');

        if (!is_admin() && !staff_can('generate_payment', 'courier-invoices')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied.']);
            return;
        }

        $details = $this->Shipment_model->get_shipment_details((int)$shipment_id);
        if (!$details || empty($details['shipment']->invoice_id)) {
            echo json_encode(['success' => false, 'message' => 'Shipment or invoice not found.']);
            return;
        }

        if (!$this->_assert_ownership($details)) {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            return;
        }

        $invoice_id  = (int)$details['shipment']->invoice_id;
        $paymentmode = $this->input->post('paymentmode');
        $amount      = (float)$this->input->post('amount');
        $date        = $this->input->post('payment_date') ?: date('Y-m-d');
        $note        = $this->input->post('note');

        // Online gateway: redirect to gateway payment page
        if (!is_numeric($paymentmode)) {
            $invoice = $this->invoices_model->get($invoice_id);
            if ($paymentmode === 'pesapal') {
                echo json_encode([
                    'success'  => true,
                    'redirect' => site_url('pesapal/make_payment/' . $invoice_id . '/' . $invoice->hash),
                ]);
                return;
            }
            // Generic online gateway redirect via Perfex gateway system
            echo json_encode([
                'success'  => true,
                'redirect' => site_url('clients/invoice/' . $invoice->hash),
            ]);
            return;
        }

        // Offline payment
        $payment_data = [
            'invoiceid'     => $invoice_id,
            'amount'        => $amount,
            'paymentmode'   => (int)$paymentmode,
            'date'          => to_sql_date($date),
            'note'          => $note,
            'transactionid' => 'COR-' . strtoupper(substr(uniqid(), -8)),
        ];

        $insert_id = $this->payments_model->add($payment_data);

        if (!$insert_id) {
            echo json_encode(['success' => false, 'message' => 'Failed to record payment. Please try again.']);
            return;
        }

        $invoice  = $this->invoices_model->get($invoice_id);
        $payment  = $this->payments_model->get($insert_id);
        $balance  = get_invoice_total_left_to_pay($invoice_id, $invoice->total);
        $mode_obj  = $this->payment_modes_model->get($paymentmode);
        $staff_row = $this->db->get_where(db_prefix() . 'staff', ['staffid' => $details['shipment']->staff_id])->row();
        $issued_by = $staff_row ? trim($staff_row->firstname . ' ' . $staff_row->lastname) : 'N/A';
        $agent_row = $this->db->get_where(db_prefix() . '_agents', ['staff_id' => $details['shipment']->staff_id])->row();
        $station   = ($agent_row && !empty($agent_row->station)) ? $agent_row->station : '';

        echo json_encode([
            'success'           => true,
            'payment_id'        => $insert_id,
            'invoice_status'    => (int)$invoice->status,
            'amount_paid'       => number_format($amount, 2),
            'balance'           => number_format(max(0, $balance), 2),
            'payment_mode_name' => $mode_obj ? $mode_obj->name : 'Cash',
            'payment_date'      => date('d M Y', strtotime($date)),
            'waybill'           => $details['shipment']->waybill_number,
            'issued_by'         => $issued_by,
            'station'           => $station,
        ]);
    }

    /**
     * Printable Consignment Note for a shipment.
     */
    public function consignment_note($id)
    {
        $details = $this->load_shipment_or_redirect($id);
        if (!$details) return;

        $data['shipment_details'] = $details;
        $data['current_date']     = date('F j, Y');
        $this->load->view('shipments/consignment_note', $data);
    }

    public function edit()
    {
        $this->load->view('shipments/edit');
    }

    /**
     * Return shipment + package data for the portal confirm modal.
     */
    public function portal_request_data($id)
    {
        header('Content-Type: application/json');

        $shipment = $this->Shipment_model->get((int)$id);
        if (!$shipment || empty($shipment->is_portal_request)) {
            echo json_encode(['status' => 'error', 'message' => 'Not found.']);
            return;
        }

        $sender    = $this->db->get_where(db_prefix() . '_shipment_senders',    ['id' => $shipment->sender_id])->row_array();
        $recipient = $this->db->get_where(db_prefix() . '_shipment_recipients', ['id' => $shipment->recipient_id])->row_array();
        $packages  = $this->db->get_where(db_prefix() . '_shipment_packages',   ['shipment_id' => $id])->result_array();

        echo json_encode([
            'status' => 'success',
            'shipment' => [
                'id'            => $shipment->id,
                'tracking_id'   => $shipment->tracking_id,
                'shipping_mode' => $shipment->shipping_mode,
                'quoted_amount' => $shipment->quoted_amount ?? 0,
                'invoice_id'    => $shipment->invoice_id,
            ],
            'sender'    => $sender,
            'recipient' => $recipient,
            'packages'  => $packages,
        ]);
    }

    /**
     * Admin confirms a portal shipment request: creates invoice + proper waybill.
     */
    public function confirm_portal_request($id)
    {
        header('Content-Type: application/json');

        $shipment = $this->Shipment_model->get((int)$id);
        $is_portal = !empty($shipment->is_portal_request) || (isset($shipment->staff_id) && (int)$shipment->staff_id === 0);
        if (!$shipment || !$is_portal || $shipment->invoice_id > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid or already confirmed request.']);
            return;
        }

        $unit_price = (float)$this->input->post('unit_price');
        $apply_vat  = (int)$this->input->post('apply_vat');

        if ($unit_price <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Please enter a valid unit price.']);
            return;
        }

        $sender    = $this->db->get_where(db_prefix() . '_shipment_senders',    ['id' => $shipment->sender_id])->row();
        $recipient = $this->db->get_where(db_prefix() . '_shipment_recipients', ['id' => $shipment->recipient_id])->row();
        $packages  = $this->db->get_where(db_prefix() . '_shipment_packages',   ['shipment_id' => $id])->result();

        // Create a minimal Perfex client for invoice linkage
        $this->db->insert(db_prefix() . 'clients', [
            'company'     => trim(($sender->first_name ?? '') . ' ' . ($sender->last_name ?? '')),
            'phonenumber' => $sender->phone_number ?? '',
            'country'     => 0,
            'city'        => '',
            'zip'         => $sender->zipcode ?? '',
            'address'     => $sender->address ?? '',
            'datecreated' => date('Y-m-d H:i:s'),
            'active'      => 1,
        ]);
        $client_id = $this->db->insert_id();

        // Generate proper waybill number
        $waybill_number = $this->generateWaybillNumber();

        // Calculate totals
        $subtotal = 0;
        if (!empty($packages)) {
            foreach ($packages as $pkg) {
                $subtotal += $unit_price * $pkg->quantity;
            }
        } else {
            $subtotal = $unit_price;
        }

        $vat_rate   = $apply_vat ? (float)(get_option('courier_parcel_vat_rate') ?: 16) : 0;
        $vat_amount = round($subtotal * ($vat_rate / 100), 2);
        $total      = round($subtotal + $vat_amount, 2);

        $currency_id = get_base_currency()->id ?? 1;
        $next_num    = get_option('next_invoice_number');

        // Create invoice
        $this->db->insert(db_prefix() . 'invoices', [
            'clientid'        => $client_id,
            'number'          => $next_num,
            'date'            => date('Y-m-d'),   // portal shipments always use today
            'duedate'         => date('Y-m-d', strtotime('+30 days')),
            'currency'        => $currency_id,
            'subtotal'        => $subtotal,
            'total'           => $total,
            'status'          => 1,
            'billing_street'  => $sender->address ?? '',
            'billing_zip'     => $sender->zipcode ?? '',
            'billing_country' => 0,
            'hash'            => app_generate_hash(),
        ]);
        $invoice_id = $this->db->insert_id();
        update_option('next_invoice_number', $next_num + 1);

        // Build route description for long_description
        $recip_addr = $recipient->address ?? '';
        $long_desc  = '<strong>SHIPPING MODE - </strong>' . strtoupper($shipment->shipping_mode)
            . " &nbsp;|&nbsp; <strong>FROM:</strong> " . strtoupper($sender->address ?? '') . " &nbsp;|&nbsp; <strong>TO:</strong> " . strtoupper($recip_addr);

        // Add invoice line items
        if (!empty($packages)) {
            foreach ($packages as $i => $pkg) {
                $current_long_desc = $long_desc;
                if (!empty($pkg->pod)) {
                    $current_long_desc .= " &nbsp;|&nbsp; <strong>POD:</strong> " . strtoupper($pkg->pod);
                }

                $this->Shipment_model->add_invoice_item([
                    'description'      => 'WAYBILL - ' . strtoupper($waybill_number) . ' | ' . $pkg->description,
                    'long_description' => $current_long_desc,
                    'qty'              => $pkg->quantity,
                    'rate'             => $unit_price,
                    'item_order'       => $i + 1,
                    'rel_id'           => $invoice_id,
                    'rel_type'         => 'invoice',
                    'unit'             => 'kg',
                ]);
            }
        } else {
            $this->Shipment_model->add_invoice_item([
                'description'      => 'WAYBILL - ' . strtoupper($waybill_number),
                'long_description' => $long_desc,
                'qty'              => 1,
                'rate'             => $unit_price,
                'item_order'       => 1,
                'rel_id'           => $invoice_id,
                'rel_type'         => 'invoice',
                'unit'             => '',
            ]);
        }

        // Link invoice, VAT, and upgrade waybill number on the shipment
        $this->Shipment_model->update((int)$id, [
            'invoice_id'      => $invoice_id,
            'waybill_number'  => $waybill_number,
            'tracking_id'     => $waybill_number,
            'vat_applicable'  => $apply_vat ? 1 : 0,
            'vat_rate'        => $apply_vat ? $vat_rate : 0,
            'vat_amount'      => $vat_amount,
            'packaging_charges' => $subtotal,
        ]);

        // Handle Commercial Invoice Items if provided
        $commodity_quantity = $this->input->post('commodity_quantity');
        $commodity_description = $this->input->post('commodity_description');
        $declared_value = $this->input->post('declared_value');

        if (!empty($commodity_quantity) && is_array($commodity_quantity)) {
            courier_load_model('CommercialValueItems_model');
            foreach ($commodity_quantity as $i => $quantity) {
                if (!empty($quantity) && !empty($commodity_description[$i]) && !empty($declared_value[$i])) {
                    $this->CommercialValueItems_model->add([
                        'shipment_id'    => $id,
                        'quantity'       => $quantity,
                        'description'    => $commodity_description[$i],
                        'declared_value' => $declared_value[$i],
                    ]);
                }
            }
        }

        // Send email to Sender
        $this->load->model('emails_model');
        if (!empty($sender->email)) {
            $subj = "Waybill Generated - " . $waybill_number;
            $msg  = "Hello " . htmlspecialchars($sender->first_name) . ",<br><br>";
            $msg .= "Your shipment request has been confirmed and a Waybill has been generated.<br>";
            $msg .= "Waybill Number: <b>" . $waybill_number . "</b><br>";
            $msg .= "Total Amount: <b>" . $total . "</b><br><br>";
            $msg .= "Thank you for shipping with us.";
            $this->emails_model->send_simple_email($sender->email, $subj, $msg);
        }

        // Send email to Recipient
        if (!empty($recipient->email)) {
            $subj = "Incoming Shipment Waybill - " . $waybill_number;
            $msg  = "Hello " . htmlspecialchars($recipient->first_name) . ",<br><br>";
            $msg .= "A Waybill has been generated for an incoming shipment from " . htmlspecialchars($sender->first_name) . ".<br>";
            $msg .= "Waybill Number: <b>" . $waybill_number . "</b><br><br>";
            $msg .= "We will notify you once it's out for delivery.";
            $this->emails_model->send_simple_email($recipient->email, $subj, $msg);
        }

        // Send email to Admins
        $admins = $this->db->where('admin', 1)->get(db_prefix() . 'staff')->result();
        if (!empty($admins)) {
            $subj = "Portal Shipment Confirmed - " . $waybill_number;
            $msg  = "A portal shipment request has been confirmed.<br>";
            $msg .= "Waybill Number: <b>" . $waybill_number . "</b><br>";
            $msg .= "Total Amount: <b>" . $total . "</b><br><br>";
            $msg .= "Please log in to the admin portal to review.";
            foreach ($admins as $admin) {
                if (!empty($admin->email)) {
                    $this->emails_model->send_simple_email($admin->email, $subj, $msg);
                }
            }
        }

        echo json_encode([
            'status'         => 'success',
            'message'        => 'Invoice and waybill created successfully.',
            'invoice_id'     => $invoice_id,
            'waybill_number' => $waybill_number,
        ]);
    }


    public function waybill($id)
    {
        $data['shipment_details'] = $this->Shipment_model->get_shipment_details($id);

        if (empty($data['shipment_details'])) {
            set_alert('danger', 'Shipment not found.');
            redirect(admin_url('courier_goshipping/shipments/main'));
            return;
        }

        if (!$this->_assert_ownership($data['shipment_details'])) {
            return;
        }

        $tracking_id = $data['shipment_details']['shipment']->tracking_id ?? '';
        $data['barcode'] = $tracking_id ? $this->generate_barcode($tracking_id) : '';
        $data['statuses'] = $this->ShipmentStatus_model->get();
        $data['current_date'] = date('F j, Y');
        $data['fleet_report'] = $this->get_shipment_fleet_report((int) $id);

        // Load active staff members and agents for the assignment dropdown
        $data['staff_members'] = $this->db->select('s.staffid, s.firstname, s.lastname, a.id as agent_id')
            ->from(db_prefix() . 'staff s')
            ->join(db_prefix() . '_agents a', 'a.staff_id = s.staffid', 'left')
            ->where('s.active', 1)
            ->order_by('s.firstname', 'asc')
            ->get()
            ->result();

        $this->load->view('shipments/waybill', $data);
    }

    private function get_shipment_fleet_report(int $shipment_id): array
    {
        $empty = [
            'summary' => [
                'total_trips' => 0,
                'completed_trips' => 0,
                'active_trips' => 0,
                'total_distance' => 0.0,
                'total_fuel' => 0.0,
                'latest_vehicle' => '',
                'latest_driver' => '',
            ],
            'trips' => [],
        ];

        if (!file_exists(module_dir_path('fleet'))) {
            return $empty;
        }

        $this->load->model('fleet/Fleet_trips_model');
        $trips = $this->Fleet_trips_model->get_all(['shipment_id' => $shipment_id]);
        if (empty($trips)) {
            return $empty;
        }

        $report_trips = [];
        $completed = 0;
        $active = 0;
        $total_distance = 0.0;
        $total_fuel = 0.0;
        $latest_vehicle = '';
        $latest_driver = '';

        foreach ($trips as $trip_row) {
            $trip = $this->Fleet_trips_model->get($trip_row->id);
            if (!$trip) {
                continue;
            }

            $offloading = $this->Fleet_trips_model->get_offloading((int) $trip->id);
            $assignment = $this->db
                ->select('a.*, v.name as vehicle_name, v.license_plate, CONCAT(st.firstname, " ", st.lastname) as driver_name', false)
                ->from(db_prefix() . 'fleet_vehicle_assignments a')
                ->join(db_prefix() . 'fleet_vehicles v', 'v.id = a.vehicle_id', 'left')
                ->join(db_prefix() . 'staff st', 'st.staffid = a.driver_id', 'left')
                ->like('a.trip_notes', 'Trip #' . (int) $trip->id, 'after')
                ->order_by('a.id', 'DESC')
                ->limit(1)
                ->get()
                ->row();

            $distance = 0.0;
            $start_odo = null;
            $end_odo = null;
            if ($assignment) {
                $start_odo = is_numeric($assignment->starting_odometer ?? null) ? (float) $assignment->starting_odometer : null;
                $end_odo = is_numeric($assignment->ending_odometer ?? null) ? (float) $assignment->ending_odometer : null;
            }
            if ($start_odo === null && is_numeric($trip->start_odometer ?? null)) {
                $start_odo = (float) $trip->start_odometer;
            }
            if ($end_odo === null && is_numeric($trip->end_odometer ?? null)) {
                $end_odo = (float) $trip->end_odometer;
            }
            if ($start_odo !== null && $end_odo !== null && $end_odo >= $start_odo) {
                $distance = $end_odo - $start_odo;
            }

            $fuel_qty = is_numeric($trip->fuel_gallons ?? null) ? (float) $trip->fuel_gallons : 0.0;
            $offload_count = is_array($offloading) ? count($offloading) : 0;

            if ($trip->status === 'completed') {
                $completed++;
            } elseif ($trip->status !== 'cancelled') {
                $active++;
            }

            $total_distance += $distance;
            $total_fuel += $fuel_qty;
            $latest_vehicle = $trip->vehicle_name ?: $latest_vehicle;
            $latest_driver = $trip->driver_name ?: $latest_driver;

            $report_trips[] = [
                'trip' => $trip,
                'assignment' => $assignment,
                'offloading' => $offloading,
                'distance' => $distance,
                'fuel_qty' => $fuel_qty,
                'offload_count' => $offload_count,
                'start_odo' => $start_odo,
                'end_odo' => $end_odo,
            ];
        }

        usort($report_trips, static function ($a, $b) {
            return strtotime((string) ($b['trip']->created_at ?? $b['trip']->trip_date ?? '')) <=> strtotime((string) ($a['trip']->created_at ?? $a['trip']->trip_date ?? ''));
        });

        return [
            'summary' => [
                'total_trips' => count($report_trips),
                'completed_trips' => $completed,
                'active_trips' => $active,
                'total_distance' => $total_distance,
                'total_fuel' => $total_fuel,
                'latest_vehicle' => $latest_vehicle,
                'latest_driver' => $latest_driver,
            ],
            'trips' => $report_trips,
        ];
    }

    public function assign_agent($id)
    {
        if (!is_admin() && !staff_can('edit_shipments', 'courier-shipments')) {
            access_denied('Assign Agent/Staff');
        }

        if ($this->input->post()) {
            $assigned_staff_id = (int)$this->input->post('assigned_staff_id');
            if ($assigned_staff_id > 0) {
                $this->db->where('id', (int)$id)->update(db_prefix() . '_shipments', ['staff_id' => $assigned_staff_id]);
                log_activity('Shipment Assigned [ID: ' . $id . ', Staff ID: ' . $assigned_staff_id . ']');
                set_alert('success', 'Shipment assigned successfully.');
            } else {
                set_alert('danger', 'Invalid agent/staff selected.');
            }
        }
        redirect(admin_url('courier_goshipping/shipments/waybill/' . $id));
    }

    /**
     * Send the waybill summary by email to the recipient.
     * Called via POST from the waybill page.
     */
    public function send_waybill_email($id)
    {
        if (!$this->input->is_ajax_request()) {
            redirect(admin_url('courier_goshipping/shipments/waybill/' . $id));
            return;
        }

        header('Content-Type: application/json');

        $details = $this->Shipment_model->get_shipment_details((int)$id);
        if (empty($details)) {
            echo json_encode(['success' => false, 'message' => 'Shipment not found.']);
            return;
        }

        $shipment  = $details['shipment'];
        $recipient = $details['recipient'];
        $sender    = $details['sender'];
        $packages  = $details['packages'] ?? [];

        // Resolve recipient email
        $to_email = null;
        if (!empty($recipient)) {
            $to_email = $recipient->email
                ?? $recipient->recipient_contact_person_email
                ?? null;
        }

        // Allow staff to override with a custom email from the POST
        $custom_email = trim($this->input->post('email') ?? '');
        if ($custom_email && filter_var($custom_email, FILTER_VALIDATE_EMAIL)) {
            $to_email = $custom_email;
        }

        if (empty($to_email) || !filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'No valid recipient email address found. Please enter one manually.']);
            return;
        }

        $_ci_e         = courier_get_invoice_info($shipment->branch_id ?? null);
        $company_name  = $_ci_e['name'] ?: 'Courier';
        $waybill       = htmlspecialchars($shipment->waybill_number ?? $shipment->tracking_id);
        $status_name   = htmlspecialchars($shipment->status_name ?? 'Processing');
        $tracking_url  = base_url('courier_goshipping/tracking') . '?track=' . urlencode($shipment->waybill_number ?? $shipment->tracking_id);

        // Sender name
        $sender_name = 'N/A';
        if (!empty($sender)) {
            if (!empty($sender->first_name)) {
                $sender_name = htmlspecialchars(trim($sender->first_name . ' ' . ($sender->last_name ?? '')));
            } elseif (!empty($sender->sender_company_name)) {
                $sender_name = htmlspecialchars($sender->sender_company_name);
            }
        }

        // Recipient name
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

        // Package summary
        $pkg_rows = '';
        foreach ($packages as $pkg) {
            $pkg_rows .= '<tr>'
                . '<td style="padding:6px 10px;border:1px solid #ddd;">' . htmlspecialchars($pkg->description ?? '-') . '</td>'
                . '<td style="padding:6px 10px;border:1px solid #ddd;text-align:center;">' . (int)($pkg->quantity ?? 1) . '</td>'
                . '<td style="padding:6px 10px;border:1px solid #ddd;text-align:center;">' . (isset($pkg->weight) ? number_format($pkg->weight, 2) . ' kg' : '-') . '</td>'
                . '</tr>';
        }
        $packages_table = $pkg_rows ? '
        <h3 style="color:#2e7d32;margin:20px 0 8px;">Package Details</h3>
        <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <thead>
                <tr style="background:#f5f5f5;">
                    <th style="padding:8px 10px;border:1px solid #ddd;text-align:left;">Description</th>
                    <th style="padding:8px 10px;border:1px solid #ddd;text-align:center;">Qty</th>
                    <th style="padding:8px 10px;border:1px solid #ddd;text-align:center;">Weight</th>
                </tr>
            </thead>
            <tbody>' . $pkg_rows . '</tbody>
        </table>' : '';

        $mode = ucfirst(str_replace('_', ' ', $shipment->shipping_mode ?? 'road'));

        $merge = [
            'recipient_name' => $recip_name ?: 'Customer',
            'sender_name'    => $sender_name,
            'waybill_number' => $waybill,
            'shipping_mode'  => $mode,
            'status'         => $status_name,
            'company_name'   => $company_name,
        ];

        $sent = mail_template('Courier_waybill_to_customer', 'courier', $to_email, $merge)->send();
        if ($sent) {
            echo json_encode(['success' => true, 'message' => 'Waybill sent successfully to ' . $to_email]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send email. Check Setup > Email Settings and ensure the "Courier Waybill Notification to Customer" template is active.']);
        }
    }

    /**
     * AJAX: email the courier invoice to a specified address.
     * POST: email (required)
     */
    public function send_invoice_email($shipment_id)
    {
        if (!$this->input->is_ajax_request()) { show_404(); }

        $to_email = trim($this->input->post('email') ?? '');
        if (!filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
            return;
        }

        $details = $this->load_shipment_or_redirect($shipment_id);
        if (!$details) {
            echo json_encode(['success' => false, 'message' => 'Shipment not found.']);
            return;
        }

        $s = $details['shipment'];
        $invoice_items = $this->get_invoice_items($s->invoice_id);

        $subtotal = 0;
        foreach ($invoice_items as $it) {
            $subtotal += (float)$it->qty * (float)$it->rate;
        }
        $vat_on  = !empty($s->vat_applicable);
        $vat_amt = $vat_on ? round($subtotal * ((float)$s->vat_rate / 100), 2) : 0;
        $total   = $subtotal + $vat_amt;

        $recipient = $details['recipient'];
        $rec_name  = '';
        if (!empty($recipient->first_name)) {
            $rec_name = trim($recipient->first_name . ' ' . ($recipient->last_name ?? ''));
        } elseif (!empty($recipient->recipient_contact_person_name)) {
            $rec_name = $recipient->recipient_contact_person_name;
        } elseif (!empty($recipient->recipient_company_name)) {
            $rec_name = $recipient->recipient_company_name;
        }

        $_lc_raw      = get_option('courier_logistic_company');
        $company_name = (!empty($_lc_raw) && $_lc_raw !== 'GO Shipping') ? $_lc_raw : (get_option('companyname') ?: 'Our Company');

        $merge = [
            'recipient_name'  => $rec_name ?: 'Customer',
            'waybill_number'  => $s->waybill_number,
            'invoice_number'  => 'INV-' . strtoupper($s->waybill_number),
            'total_amount'    => number_format($total, 2),
            'due_date'        => date('d M Y', strtotime('+14 days')),
            'company_name'    => $company_name,
        ];

        $sent = mail_template('Courier_invoice_to_customer', 'courier', $to_email, $merge)->send();
        if ($sent) {
            echo json_encode(['success' => true, 'message' => 'Invoice emailed to ' . $to_email]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Email could not be sent. Check Setup > Email Settings.']);
        }
    }

    /**
     * AJAX: email a specific payment receipt.
     * POST: email (required), payment_id (required)
     */
    public function send_payment_receipt_email($shipment_id)
    {
        if (!$this->input->is_ajax_request()) { show_404(); }

        $to_email   = trim($this->input->post('email') ?? '');
        $payment_id = (int)$this->input->post('payment_id');

        if (!filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
            return;
        }

        $details = $this->load_shipment_or_redirect($shipment_id);
        if (!$details) {
            echo json_encode(['success' => false, 'message' => 'Shipment not found.']);
            return;
        }

        $s = $details['shipment'];
        $invoice_items = $this->get_invoice_items($s->invoice_id);

        $subtotal = 0;
        foreach ($invoice_items as $it) {
            $subtotal += (float)$it->qty * (float)$it->rate;
        }
        $vat_on  = !empty($s->vat_applicable);
        $vat_amt = $vat_on ? round($subtotal * ((float)$s->vat_rate / 100), 2) : 0;
        $total   = $subtotal + $vat_amt;

        $this->load->model('payments_model');
        $all_payments = $this->payments_model->get_invoice_payments((int)($s->invoice_id ?? 0));

        $pmt = null;
        $running = 0;
        foreach ($all_payments as $p) {
            $running += (float)$p['amount'];
            if ((int)$p['paymentid'] === $payment_id) {
                $pmt     = $p;
                $balance = max(0, $total - $running);
            }
        }

        if (!$pmt) {
            echo json_encode(['success' => false, 'message' => 'Payment record not found.']);
            return;
        }

        $recipient = $details['recipient'];
        $rec_name  = '';
        if (!empty($recipient->first_name)) {
            $rec_name = trim($recipient->first_name . ' ' . ($recipient->last_name ?? ''));
        } elseif (!empty($recipient->recipient_contact_person_name)) {
            $rec_name = $recipient->recipient_contact_person_name;
        } elseif (!empty($recipient->recipient_company_name)) {
            $rec_name = $recipient->recipient_company_name;
        }

        $_lc_raw      = get_option('courier_logistic_company');
        $company_name = (!empty($_lc_raw) && $_lc_raw !== 'GO Shipping') ? $_lc_raw : (get_option('companyname') ?: 'Our Company');

        $merge = [
            'recipient_name' => $rec_name ?: 'Customer',
            'waybill_number' => $s->waybill_number,
            'receipt_number' => 'RCP-' . str_pad($payment_id, 6, '0', STR_PAD_LEFT),
            'amount_paid'    => number_format((float)$pmt['amount'], 2),
            'payment_mode'   => !empty($pmt['name']) ? $pmt['name'] : 'Cash',
            'payment_date'   => date('d M Y', strtotime($pmt['date'])),
            'balance_due'    => number_format($balance, 2),
            'company_name'   => $company_name,
        ];

        $sent = mail_template('Courier_payment_receipt_to_customer', 'courier', $to_email, $merge)->send();
        if ($sent) {
            echo json_encode(['success' => true, 'message' => 'Receipt emailed to ' . $to_email]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Email could not be sent. Check Setup > Email Settings.']);
        }
    }

    public function quote_calculator()
    {
        courier_load_model('DimensionalFactor_model');
        // Build dimensional factors for the quote calculator
        $factors_raw = $this->DimensionalFactor_model->get();
        $data['dim_factors'] = [
            'ground' => 5000,
            'air'    => 6000,
            'sea'    => 1000,
        ];
        foreach ($factors_raw as $f) {
            if ($f->name === 'default')           $data['dim_factors']['ground'] = (int) $f->value;
            if ($f->name === 'air_freight')       $data['dim_factors']['air']    = (int) $f->value;
            if ($f->name === 'air_consolidation') $data['dim_factors']['air']    = (int) $f->value;
            if ($f->name === 'sea_lcl')           $data['dim_factors']['sea']    = (int) $f->value;
        }

        // Bootstrap tariff zones for the self-service quote calculator
        $data['tariff_zones'] = [];
        if ($this->db->table_exists(db_prefix() . '_courier_tariff_zones')) {
            $data['tariff_zones'] = $this->db
                ->order_by('zone_code', 'ASC')
                ->get(db_prefix() . '_courier_tariff_zones')
                ->result_array();
        }

        $data['parcel_rate_per_kg']  = (float)get_option('courier_parcel_rate_per_kg');
        $data['parcel_handling_fee'] = (float)(get_option('courier_parcel_handling_fee') ?: 100);
        $data['parcel_vat_rate']     = (float)(get_option('courier_parcel_vat_rate')     ?: 16);

        $data['title'] = 'Quote Calculator';
        $this->load->view('shipments/quote_calculator', $data);
    }

    public function commercial_invoice($id)
    {

        $data['type'] = $this->session->userdata('type');

        if ($this->session->userdata('mode') !== null) {
            $data['mode'] = $this->session->userdata('mode');
            $data['mode_type'] = $this->session->userdata('mode_type');
        }

        $data['shipment_details'] = $this->Shipment_model->get_shipment_details($id);
        $data['statuses'] = $this->ShipmentStatus_model->get();
        $data['current_date'] = date('F j, Y'); // Format: August 8, 2024

        $this->load->view('shipments/commercial_invoice', $data);
    }

    /**
     * Send an email notification to the shipment recipient on key status transitions.
     *
     * Called after a successful status update commit.
     *
     * @param int    $shipment_id
     * @param int    $new_status_id   Status IDs: 4 = dispatched, 8 = delivered
     */
    private function _send_shipment_notification(int $shipment_id, int $new_status_id): void
    {
        // Only notify on dispatch (4) and delivery (8)
        if (!in_array($new_status_id, [4, 8])) {
            return;
        }

        $details = $this->Shipment_model->get_shipment_details($shipment_id);
        if (empty($details)) {
            return;
        }

        $shipment  = $details['shipment'];
        $recipient = $details['recipient'];

        // Determine recipient email
        $to_email = null;
        if (!empty($recipient)) {
            if (!empty($recipient->email)) {
                $to_email = $recipient->email;
            } elseif (!empty($recipient->recipient_contact_person_email)) {
                $to_email = $recipient->recipient_contact_person_email;
            }
        }

        if (empty($to_email) || !filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $company_name = get_option('courier_logistic_company') ?: get_option('companyname') ?: 'Courier';
        $waybill      = $shipment->waybill_number ?? $shipment->tracking_id;

        if ($new_status_id === 4) {
            $subject = 'Your shipment #' . $waybill . ' has been dispatched';
            $body    = '<p>Dear Customer,</p>'
                . '<p>We are pleased to inform you that your shipment with waybill number <strong>'
                . htmlspecialchars($waybill) . '</strong> has been <strong>dispatched</strong> and is on its way to you.</p>'
                . '<p>You can track its progress using the waybill number above.</p>'
                . '<p>Thank you for choosing ' . htmlspecialchars($company_name) . '.</p>';
        } else {
            // status 8 = delivered
            $subject = 'Your shipment #' . $waybill . ' has been delivered';
            $body    = '<p>Dear Customer,</p>'
                . '<p>Your shipment with waybill number <strong>'
                . htmlspecialchars($waybill) . '</strong> has been successfully <strong>delivered</strong>.</p>'
                . '<p>Thank you for choosing ' . htmlspecialchars($company_name) . '. We hope to serve you again!</p>';
        }

        try {
            $this->load->library('email');
            $this->email->initialize([
                'mailtype' => 'html',
                'charset'  => 'utf-8',
            ]);
            $this->email->clear(true);
            $this->email->from(
                get_option('smtp_email') ?: get_option('email'),
                $company_name
            );
            $this->email->to($to_email);
            $this->email->subject($subject);

            $header  = get_option('email_header') ?: '';
            $footer  = get_option('email_footer')  ?: '';
            $this->email->message($header . $body . $footer);
            $this->email->send();
        } catch (\Exception $e) {
            log_message('error', 'Courier shipment notification failed: ' . $e->getMessage());
        }
    }

    public function update_status($id)
    {
        // Start a database transaction
        $this->db->trans_begin();

        try {
            $new_status_id = (int) $this->input->post('status_id');
            $shipment_data = [
                'status_id' => $new_status_id,
            ];

            // Update the shipment status
            $this->Shipment_model->update($id, $shipment_data);

            // Record the status change in the shipment_status_histories table
            $this->db->insert(db_prefix() . '_shipment_status_history', [
                'shipment_id' => $id,
                'status_id'   => $new_status_id,
                'changed_at'  => date('Y-m-d H:i:s'),
            ]);

            $departure_points = $this->input->post('departure_points');

            if (!empty($departure_points) && array_filter($departure_points)) {
                foreach ($departure_points as $i => $departure_point) {
                    if (!empty($departure_point)) {
                        $shipment_stop = [
                            'shipment_id' => $id,
                            'departure_point' => $departure_point,
                            'destination_point' => $this->input->post('destination_points')[$i],
                            'description' => $this->input->post('description')[$i],
                        ];

                        $this->ShipmentStop_model->add($shipment_stop);
                    }
                }
            }

            if ($new_status_id === 8) {
                // Validation for signature fields
                $this->form_validation->set_rules('first_name', 'First Name', 'required');
                $this->form_validation->set_rules('last_name', 'Last Name', 'required');
                $this->form_validation->set_rules('signature', 'Signature', 'required');

                if ($this->form_validation->run() === FALSE) {
                    throw new Exception('Please fill all the details');
                }

                $first_name = $this->input->post('first_name');
                $last_name = $this->input->post('last_name');
                $canvasData = $this->input->post('signature');

                if (!empty($canvasData)) {
                    $canvasData = str_replace('data:image/png;base64,', '', $canvasData);
                    $canvasData = str_replace(' ', '+', $canvasData);
                    $imageData = base64_decode($canvasData);

                    $fileName = uniqid() . '.png';
                    $filePath = FCPATH . 'modules/courier_goshipping/assets/deliveries/signatures/' . $fileName;

                    if (file_put_contents($filePath, $imageData)) {
                        $this->Delivery_model->add([
                            'shipment_id' => $id,
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'signature_url' => 'assets/pickups/signatures/' . $fileName,
                        ]);
                    } else {
                        throw new Exception('There was an error while saving the signature');
                    }
                } else {
                    throw new Exception('Please include a signature');
                }
            }

            // Mirror "Delivered" back onto the linked Salibay order, if any —
            // otherwise it stays stuck showing as Pending Dispatch forever,
            // since nothing else ever advances shopify_orders.order_status
            // past 'processing' once a shipment exists.
            if ($new_status_id === 8 && $this->db->table_exists(db_prefix() . 'shopify_orders')) {
                $this->db->where('gs_shipment_id', (int) $id)->update(db_prefix() . 'shopify_orders', [
                    'order_status' => 'delivered',
                ]);
            }

            // Commit the transaction if everything is successful
            $this->db->trans_commit();

            // Send email notification to recipient on dispatch or delivery (outside transaction)
            $this->_send_shipment_notification((int)$id, $new_status_id);

            set_alert('success', 'Status updated successfully.');
            redirect(admin_url('courier_goshipping/shipments/waybill/' . $id));

        } catch (Exception $exception) {
            // Rollback the transaction if any error occurs
            $this->db->trans_rollback();

            set_alert('danger', 'An error occurred: ' . $exception->getMessage());
            redirect(admin_url('courier_goshipping/shipments/waybill/' . $id));
            log_message('error', $exception->getMessage());
        }
    }


    public function generate_manifest()
    {

        // Set validation rules
        $this->form_validation->set_rules('dateRange', 'Date Range', 'required');
        $this->form_validation->set_rules('company_name', 'Company Name', 'required');
        $this->form_validation->set_rules('location', 'Location', 'required');
        $this->form_validation->set_rules('street_address', 'Street Address', 'required');
        $this->form_validation->set_rules('landmark', 'Landmark', 'required');
        $this->form_validation->set_rules('phone_number', 'Phone Number', 'required|min_length[10]');

        if ($this->form_validation->run() == FALSE) {

            $this->session->set_flashdata('show_modal', true);

            $this->session->set_flashdata('form_errors', validation_errors());

            $url = 'courier_goshipping/shipments?type=';

            // Set session data
            $type = $this->session->userdata('type');

            $url = $url . $type;

            if ($this->session->userdata('mode') !== null) {
                $mode = $this->session->userdata('mode');
                $mode_type = $this->session->userdata('mode_type');
                $url = $url . '&mode=' . $mode . '&mode_type=' . $mode_type;
            }

            redirect('admin/' . $url);

        } else {

            $dateRange = $this->input->post('dateRange');

            $dates = explode(" to ", $dateRange);

            $startDate = $dates[0];
            $endDate = isset($dates[1]) ? $dates[1] : $dates[0];

            $type = $this->input->post('shipment_type');
            $mode = $this->input->post('shipment_mode') !== null ? $this->input->post('shipment_mode') : null;
            $mode_type = $this->input->post('shipment_mode_type') !== 'none' ? $this->input->post('shipment_mode_type') : null;
            $form_submitted = $this->input->post('form_submitted');
            $countryId = $this->input->post('country_id');
            $destination_office = $this->DestinationOffice_model->add([
                'company_name' => $this->input->post('company_name'),
                'location' => $this->input->post('location'),
                'street_address' => $this->input->post('street_address'),
                'landmark' => $this->input->post('landmark'),
                'phone_number' => $this->input->post('phone_number')
            ]);

            $data['shipment_details'] = $this->Shipment_model->get_shipment_details_by_date_range($startDate, $endDate, $type, $mode, $mode_type, $countryId);
            $data['start_date'] = $startDate;
            $data['end_date'] = $endDate;
            $data['destination_office'] = $destination_office;
            $data['user_country'] = $this->getStaffCountry();

            $latestManifest = $this->Manifest_model->get_latest_manifest_number();
            $latestFlight = $this->Manifest_model->get_latest_flight_number();

            if ($form_submitted) {
                if (empty($latestManifest)) {
                    $data['manifest_number'] = 26000026;
                } else {
                    $data['manifest_number'] = $latestManifest + 1;
                }

                if (empty($latestFlight)) {
                    $data['flight_number'] = 26;
                } else {
                    $data['flight_number'] = $latestFlight + 1;
                }

            }

            set_alert('success', 'Manifest created successfully.');
            $this->load->view('shipments/manifest', $data);

        }
    }


    public function delete()
    {

        if (!is_admin() && !staff_can('delete_shipments', 'courier-shipments')) {
            access_denied('Courier - Shipments');
        }

        $shipment_id = $this->input->post('shipment_id');

        $url = 'courier_goshipping/shipments?type=';

        // Set session data
        $type = $this->session->userdata('type');

        $url = $url . $type;

        if ($this->session->userdata('mode') !== null) {
            $mode = $this->session->userdata('mode');
            $mode_type = $this->session->userdata('mode_type');
            $url = $url . '&mode=' . $mode . '&mode_type=' . $mode_type;
        }


        $this->db->trans_start(); // Start a transaction

        // Delete packages associated with the shipment
        $this->db->where('shipment_id', $shipment_id);
        $this->db->delete(db_prefix() . '_shipment_packages');

        // Delete fcl/packages associated with the shipment
        $this->db->where('shipment_id', $shipment_id);
        $this->db->delete(db_prefix() . '_shipment_fcl_packages');

        // Get shipment data to delete associated sender, recipient, and company, commercial
        // values, shipment history, shipment stops and pickups if needed
        $shipment = $this->db->get_where(db_prefix() . '_shipments', ['id' => $shipment_id])->row();

        if ($shipment) {

            // Delete sender
            $this->db->where('id', $shipment->sender_id);
            $this->db->delete(db_prefix() . '_shipment_senders');

            // Delete recipient
            $this->db->where('id', $shipment->recipient_id);
            $this->db->delete(db_prefix() . '_shipment_recipients');

            // Delete company
            $this->db->where('id', $shipment->company_id);
            $this->db->delete(db_prefix() . '_shipment_companies');

            // Delete commercial_values_items if applicable
            $this->db->where('shipment_id', $shipment_id);
            $this->db->delete(db_prefix() . '_commercial_values_items');

            // Delete shipment_stops if applicable
            $this->db->where('shipment_id', $shipment_id);
            $this->db->delete(db_prefix() . '_shipment_stops');

            // Delete shipment_status_history if applicable
            $this->db->where('shipment_id', $shipment_id);
            $this->db->delete(db_prefix() . '_shipment_status_history');

            // Delete pickups if applicable
            $this->db->where('shipment_id', $shipment_id);
            $this->db->delete(db_prefix() . '_pickups');

            // Finally, delete the shipment itself
            $this->db->where('id', $shipment_id);
            $this->db->delete(db_prefix() . '_shipments');
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            set_alert('danger', 'There was an error while deleting shipment');
            redirect('admin/' . $url);
        } else {
            set_alert('success', 'Shipment and related data deleted successfully');
            redirect('admin/' . $url);
        }
    }


    // -----------------------------------------------------------------------
    // ── Routes CRUD (AJAX) ────────────────────────────────────────────────────

    /** Routes are shared master data — read requires any manifest view permission, write requires edit_manifests. */
    private function _can_view_manifests()
    {
        return is_admin()
            || staff_can('view_manifests', 'courier-manifests')
            || staff_can('view_own_manifests', 'courier-manifests');
    }

    private function _can_manage_routes()
    {
        return is_admin() || staff_can('edit_manifests', 'courier-manifests');
    }

    /** GET: all routes as JSON */
    public function get_routes()
    {
        if (!$this->input->is_ajax_request()) show_404();
        if (!$this->_can_view_manifests()) {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            return;
        }
        $routes = $this->db->get(db_prefix() . '_courier_routes')->result();
        echo json_encode(['success' => true, 'data' => $routes]);
    }

    /** GET: stops for a specific route */
    public function get_route_stops($route_id)
    {
        if (!$this->input->is_ajax_request()) show_404();
        if (!$this->_can_view_manifests()) {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            return;
        }
        $stops = $this->db->order_by('sort_order', 'ASC')
                          ->where('route_id', (int)$route_id)
                          ->get(db_prefix() . '_courier_route_stops')->result();
        echo json_encode(['success' => true, 'data' => $stops]);
    }

    /** POST: save a route (create or update) */
    public function save_route()
    {
        if (!$this->input->is_ajax_request()) show_404();
        if (!$this->_can_manage_routes()) {
            echo json_encode(['success' => false, 'message' => 'Access denied — you do not have permission to manage routes.']);
            return;
        }
        $id   = (int)$this->input->post('id');
        $name = trim($this->input->post('name'));
        $desc = trim($this->input->post('description'));
        if (!$name) { echo json_encode(['success' => false, 'message' => 'Route name required.']); return; }

        if ($id) {
            $this->db->update(db_prefix() . '_courier_routes', ['name' => $name, 'description' => $desc], ['id' => $id]);
        } else {
            $this->db->insert(db_prefix() . '_courier_routes', ['name' => $name, 'description' => $desc]);
            $id = $this->db->insert_id();
        }
        echo json_encode(['success' => true, 'id' => $id]);
    }

    /** POST: delete a route */
    public function delete_route($id)
    {
        if (!$this->input->is_ajax_request()) show_404();
        if (!$this->_can_manage_routes()) {
            echo json_encode(['success' => false, 'message' => 'Access denied — you do not have permission to manage routes.']);
            return;
        }
        $this->db->delete(db_prefix() . '_courier_routes', ['id' => (int)$id]);
        echo json_encode(['success' => true]);
    }

    /** POST: save stops for a route (replaces existing) */
    public function save_route_stops($route_id)
    {
        if (!$this->input->is_ajax_request()) show_404();
        if (!$this->_can_manage_routes()) {
            echo json_encode(['success' => false, 'message' => 'Access denied — you do not have permission to manage routes.']);
            return;
        }
        $route_id = (int)$route_id;
        $stops    = $this->input->post('stops') ?: [];

        $this->db->delete(db_prefix() . '_courier_route_stops', ['route_id' => $route_id]);
        foreach (array_values($stops) as $order => $stop_name) {
            $stop_name = trim($stop_name);
            if ($stop_name === '') continue;
            $this->db->insert(db_prefix() . '_courier_route_stops', [
                'route_id'   => $route_id,
                'stop_name'  => $stop_name,
                'sort_order' => $order,
            ]);
        }
        echo json_encode(['success' => true]);
    }

    // Private helper: build manifest rows with full sender/recipient/package data
    // -----------------------------------------------------------------------
    private function _get_manifest_rows($date_from, $date_to, $staff_id = null, array $service_points = [])
    {
        $select = 's.id, s.waybill_number, s.tracking_id, s.shipping_mode, s.shipping_category,'
                . ' s.created_at, s.packaging_charges, s.staff_id,'
                . ' COALESCE(s.vat_amount,0) AS vat_amount,'
                . ' COALESCE(ss.status_name,"Pending") AS status_name,'
                . ' CONCAT(st.firstname," ",st.lastname) AS driver_name,'
                . ' sr.first_name AS sender_first, sr.last_name AS sender_last,'
                . ' sr.phone_number AS sender_phone, sr.address AS sender_address,'
                . ' sc.contact_person_name AS co_sender_name,'
                . ' sc.contact_person_phone_number AS co_sender_phone,'
                . ' sc.contact_address AS co_sender_addr,'
                . ' rr.first_name AS recv_first, rr.last_name AS recv_last,'
                . ' rr.phone_number AS recv_phone, rr.address AS recv_address,'
                . ' rc.recipient_contact_person_name AS co_recv_name,'
                . ' rc.recipient_contact_person_phone_number AS co_recv_phone,'
                . ' rc.recipient_contact_address AS co_recv_addr';

        $this->db->select($select, false);
        $this->db->from(db_prefix() . '_shipments s');
        $this->db->join(db_prefix() . '_shipment_statuses ss',   'ss.id = s.status_id',             'left');
        $this->db->join(db_prefix() . 'staff st',                'st.staffid = s.staff_id',          'left');
        $this->db->join(db_prefix() . '_shipment_senders sr',    'sr.id = s.sender_id',              'left');
        $this->db->join(db_prefix() . '_shipment_companies sc',  'sc.id = s.company_id',             'left');
        $this->db->join(db_prefix() . '_shipment_recipients rr', 'rr.id = s.recipient_id',           'left');
        $this->db->join(db_prefix() . '_recipient_companies rc', 'rc.id = s.recipient_company_id',   'left');
        $this->db->where('DATE(s.created_at) >=', $date_from);
        $this->db->where('DATE(s.created_at) <=', $date_to);

        if ($staff_id) {
            $this->db->where('s.staff_id', (int)$staff_id);
        }

        courier_apply_branch_scope('s.branch_id');

        $this->db->order_by('s.created_at', 'ASC');
        $shipments = $this->db->get()->result();

        if (empty($shipments)) {
            return [];
        }

        // Normalise service_points filter for case-insensitive matching
        $filter_pods = [];
        foreach ($service_points as $sp) {
            $sp = trim($sp);
            if ($sp !== '') $filter_pods[] = strtolower($sp);
        }

        $rows = [];
        foreach ($shipments as $s) {
            // Packages for this shipment (include pod)
            $this->db->select('description, quantity, chargeable_weight, weight, pod, unit_price');
            $this->db->where('shipment_id', $s->id);
            $pkgs = $this->db->get(db_prefix() . '_shipment_packages')->result();

            // Resolve recipient
            if (!empty($s->recv_first)) {
                $recv_name  = trim($s->recv_first . ' ' . $s->recv_last);
                $recv_phone = $s->recv_phone    ?? '';
                $recv_addr  = $s->recv_address  ?? '';
            } else {
                $recv_name  = $s->co_recv_name  ?? '';
                $recv_phone = $s->co_recv_phone ?? '';
                $recv_addr  = $s->co_recv_addr  ?? '';
            }

            // If service_points filter is active, only include shipments that have
            // at least one package whose POD matches a selected service point
            if (!empty($filter_pods)) {
                $matched = false;
                foreach ((array)$pkgs as $p) {
                    $p_pod = !empty($p->pod) ? trim($p->pod) : ($recv_addr ?: '-');
                    if (in_array(strtolower($p_pod), $filter_pods)) {
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) continue;
            }

            // Resolve sender
            if (!empty($s->sender_first)) {
                $sender_name  = trim($s->sender_first . ' ' . $s->sender_last);
                $sender_phone = $s->sender_phone ?? '';
                $sender_addr  = $s->sender_address ?? '';
            } else {
                $sender_name  = $s->co_sender_name  ?? '';
                $sender_phone = $s->co_sender_phone ?? '';
                $sender_addr  = $s->co_sender_addr  ?? '';
            }

            // Resolve recipient
            if (!empty($s->recv_first)) {
                $recv_name  = trim($s->recv_first . ' ' . $s->recv_last);
                $recv_phone = $s->recv_phone    ?? '';
                $recv_addr  = $s->recv_address  ?? '';
            } else {
                $recv_name  = $s->co_recv_name  ?? '';
                $recv_phone = $s->co_recv_phone ?? '';
                $recv_addr  = $s->co_recv_addr  ?? '';
            }

            // Package aggregation — build per-POD lines for multi-POD shipments
            $pkg_descs    = [];
            $total_qty    = 0;
            $total_weight = 0.0;
            $pod_lines    = []; // [ ['pod'=>'Kisumu','desc'=>'...','qty'=>2,'weight'=>100] ]
            foreach ((array)$pkgs as $p) {
                $p_pod = !empty($p->pod) ? trim($p->pod) : ($recv_addr ?: '-');
                // If service_points filter active, skip packages not in the filter
                if (!empty($filter_pods) && !in_array(strtolower($p_pod), $filter_pods)) continue;
                if (!empty($p->description)) $pkg_descs[] = $p->description;
                $total_qty    += (int)$p->quantity;
                $total_weight += (float)($p->weight ?: $p->chargeable_weight);
                $pod_lines[]   = [
                    'pod'    => $p_pod,
                    'desc'   => $p->description ?? '',
                    'qty'    => (int)$p->quantity,
                    'weight' => (float)($p->weight ?: $p->chargeable_weight),
                ];
            }

            $charges = (float)($s->packaging_charges ?? 0);
            $vat     = (float)($s->vat_amount        ?? 0);

            $rows[] = [
                'id'           => $s->id,
                'date'         => $s->created_at,
                'waybill'      => $s->waybill_number ?: $s->tracking_id,
                'tracking_id'  => $s->tracking_id,
                'mode'         => $s->shipping_mode,
                'sender_name'  => $sender_name  ?: 'N/A',
                'sender_phone' => $sender_phone ?: '-',
                'sender_addr'  => $sender_addr  ?: '-',
                'recv_name'    => $recv_name    ?: 'N/A',
                'recv_phone'   => $recv_phone   ?: '-',
                'recv_addr'    => $recv_addr    ?: '-',
                'driver_name'  => trim($s->driver_name ?? '') ?: 'Unassigned',
                'pkg_desc'     => implode('; ', $pkg_descs) ?: '-',
                'pkg_qty'      => $total_qty,
                'pkg_weight'   => $total_weight,
                'pod_lines'    => $pod_lines,
                'charges'      => $charges,
                'vat'          => $vat,
                'total'        => $charges + $vat,
                'status'       => $s->status_name ?: 'Pending',
            ];
        }

        return $rows;
    }

    /**
     * Return the branch_ids assigned to the currently logged-in staff member
     * for filtering list queries, or NULL if unrestricted (admin / holds
     * view_all_branches).
     */
    private function get_staff_branch_ids()
    {
        if (courier_staff_can_view_all_branches()) {
            return null;
        }

        return courier_get_staff_branch_ids();
    }

    /**
     * Parse the posted shipment_date, reject future dates, fall back to today.
     * Returns a Y-m-d string safe for use in created_at and invoice date fields.
     */
    private function _parse_shipment_date(): string
    {
        $posted = trim($this->input->post('shipment_date') ?? '');
        if ($posted && preg_match('/^\d{4}-\d{2}-\d{2}$/', $posted)) {
            $ts = strtotime($posted);
            if ($ts !== false && $ts <= strtotime('today')) {
                return date('Y-m-d', $ts);
            }
        }
        return date('Y-m-d');
    }

}
