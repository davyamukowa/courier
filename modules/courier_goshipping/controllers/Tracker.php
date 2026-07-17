<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Tracker extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('courier_goshipping/courier');
        $this->load->model('Shipment_model');
        // MX's model loader lowercases the whole path then only ucfirst()'s
        // the first letter before checking is_file(), so multi-capital
        // filenames like these never resolve on case-sensitive (Linux) fs.
        courier_load_model('ShipmentStatus_model');
        courier_load_model('DestinationOffice_model');
        courier_load_model('DimensionalFactor_model');
    }

    public function tracking($tab = 'track')
    {
        $valid_tabs = ['track', 'quote', 'pickup', 'service', 'shipment', 'call'];
        $data['active_tab'] = in_array($tab, $valid_tabs, true) ? $tab : 'track';

        $sp_table = db_prefix() . '_courier_service_points';
        $data['service_points'] = $this->db->table_exists($sp_table)
            ? $this->db->order_by('sort_order', 'ASC')->order_by('name', 'ASC')->get($sp_table)->result_array()
            : [];

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

        // Courier type controls which quote services are shown on the portal
        $data['courier_type'] = get_option('courier_type') ?: 'international';

        // FCL container rates for the portal quote (needed when courier_type = international)
        $data['fcl_rates'] = [
            '20dv' => (float)(get_option('courier_rate_sea_fcl_20dv') ?: 0),
            '40dv' => (float)(get_option('courier_rate_sea_fcl_40dv') ?: 0),
            '20hc' => (float)(get_option('courier_rate_sea_fcl_20hc') ?: 0),
            '40hc' => (float)(get_option('courier_rate_sea_fcl_40hc') ?: 0),
            '20rf' => (float)(get_option('courier_rate_sea_fcl_20rf') ?: 0),
            '40rf' => (float)(get_option('courier_rate_sea_fcl_40rf') ?: 0),
            '20fr' => (float)(get_option('courier_rate_sea_fcl_20fr') ?: 0),
            '40fr' => (float)(get_option('courier_rate_sea_fcl_40fr') ?: 0),
            'roro' => (float)(get_option('courier_rate_sea_fcl_roro') ?: 0),
        ];

        $this->load->view('tracking', $data);
    }

    // Alias so both /courier/tracking and /courier/portal load the same view
    public function portal()
    {
        $this->tracking();
    }

    public function shipment_info()
    {
        header('Content-Type: application/json');
        $tracking_number = $this->input->post('tracking_number');
        $shipment        = $this->Shipment_model->get_shipment_by_tracking_number($tracking_number);
        $statuses        = $this->ShipmentStatus_model->get();

        if ($shipment) {
            echo json_encode([
                'status' => 'success',
                'data'   => [
                    'shipment_details' => $shipment,
                    'statuses'         => $statuses,
                ],
            ]);
        } else {
            echo json_encode([
                'status'          => 'error',
                'message'         => 'Shipment not found.',
                'tracking_number' => $tracking_number,
            ]);
        }
    }

    public function service_points()
    {
        header('Content-Type: application/json');
        $points = $this->DestinationOffice_model->get();
        echo json_encode(['status' => 'success', 'data' => $points]);
    }

    public function quote()
    {
        redirect('courier_goshipping/get-a-quote');
    }

    public function schedule_pickup()
    {
        redirect('courier_goshipping/schedule-delivery');
    }

    /**
     * Handle public (no-login) pickup request form submission.
     */
    public function store_pickup()
    {
        header('Content-Type: application/json');

        $contact_first = trim($this->input->post('contact_first_name'));
        $contact_last  = trim($this->input->post('contact_last_name'));
        $contact_phone = trim($this->input->post('contact_phone'));
        $contact_email = trim($this->input->post('contact_email'));
        $pickup_date   = trim($this->input->post('pickup_date'));
        $time_window   = trim($this->input->post('time_window'));
        $address       = trim($this->input->post('pickup_address'));
        $description   = trim($this->input->post('package_description'));

        if (!$contact_first || !$contact_last || !$contact_phone || !$contact_email || !$pickup_date || !$address) {
            echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
            return;
        }

        // Store as a public pickup enquiry using the existing pickups table
        // We use staff_id = 0 / NULL to denote a public request
        $times = explode('-', $time_window ?: '08:00-12:00');

        $contact_data = [
            'first_name'   => $contact_first,
            'last_name'    => $contact_last,
            'phone_number' => $contact_phone,
            'email'        => $contact_email,
        ];
        courier_load_model('PickupContact_model');
        $contact_id = $this->PickupContact_model->add($contact_data);

        if (!$contact_id) {
            echo json_encode(['status' => 'error', 'message' => 'Could not save request. Please try again.']);
            return;
        }

        $pickup_data = [
            'pickup_date'       => $pickup_date,
            'pickup_start_time' => isset($times[0]) ? trim($times[0]) : '08:00',
            'pickup_end_time'   => isset($times[1]) ? trim($times[1]) : '12:00',
            'address'           => $address . ($description ? ' | Notes: ' . $description : ''),
            'contact_person_id' => $contact_id,
            'status'            => 'pending',
            'source'            => 'portal',
            'created_at'        => date('Y-m-d H:i:s'),
        ];

        $this->load->model('Pickup_model');
        $pickup_id = $this->Pickup_model->add($pickup_data);

        if ($pickup_id) {
            echo json_encode(['status' => 'success', 'message' => 'Pickup request submitted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Could not save request. Please try again.']);
        }
    }

    /**
     * Handle public (no-login) shipment request submission.
     */
    public function store_public_shipment()
    {
        header('Content-Type: application/json');

        $sender_name    = trim($this->input->post('sender_name'));
        $sender_phone   = trim($this->input->post('sender_phone'));
        $sender_email   = trim($this->input->post('sender_email'));
        $sender_address = trim($this->input->post('sender_address'));
        $recip_name     = trim($this->input->post('recipient_name'));
        $recip_phone    = trim($this->input->post('recipient_phone'));
        $recip_email    = trim($this->input->post('recipient_email'));
        $recip_address  = trim($this->input->post('recipient_address'));
        $raw_mode       = trim($this->input->post('shipping_mode'));
        $description    = trim($this->input->post('package_description'));
        $instructions   = trim($this->input->post('special_instructions'));
        $vehicle_type   = trim($this->input->post('vehicle_type'));
        $weight         = (float)$this->input->post('weight');
        $length         = (float)$this->input->post('length');
        $width          = (float)$this->input->post('width');
        $height         = (float)$this->input->post('height');
        $cargo_type     = trim($this->input->post('cargo_type')) ?: 'parcel';
        $zone_code      = strtoupper(trim($this->input->post('zone_code')));
        $quoted_amount  = (float)$this->input->post('quoted_amount');

        if (!$sender_name || !$sender_phone || !$sender_address || !$recip_name || !$recip_phone || !$recip_address || !$description) {
            echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
            return;
        }

        // Portal creates domestic shipments — use clean mode values (no mode_type suffix)
        $shipping_mode = 'ROAD';
        if (stripos($raw_mode, 'Air') !== false) {
            $shipping_mode = 'AIR';
        } elseif (stripos($raw_mode, 'LCL') !== false) {
            $shipping_mode = 'SEA (LCL)';
        } elseif (stripos($raw_mode, 'FCL') !== false) {
            $shipping_mode = 'SEA (FCL)';
        }

        // Store sender
        $this->load->model('ShipmentSender_model');
        $sender_id = $this->ShipmentSender_model->add([
            'first_name'   => $sender_name,
            'last_name'    => '',
            'phone_number' => $sender_phone,
            'email'        => $sender_email,
            'address'      => $sender_address,
            'zipcode'      => '',
        ]);

        // Store recipient
        $this->load->model('ShipmentRecipient_model');
        $recipient_id = $this->ShipmentRecipient_model->add([
            'first_name'   => $recip_name,
            'last_name'    => '',
            'phone_number' => $recip_phone,
            'email'        => $recip_email,
            'address'      => $recip_address,
            'zipcode'      => '',
        ]);

        if (!$sender_id || !$recipient_id) {
            echo json_encode(['status' => 'error', 'message' => 'Could not save request. Please try again.']);
            return;
        }

        // Generate a temporary reference number
        $prefix  = get_option('courier_waybill_prefix') ?: 'REQ';
        $ref     = strtoupper($prefix) . 'PUB' . random_int(10000, 99999);

        // Handle Commercial Invoice Upload
        $commercial_invoice_file = '';
        if (isset($_FILES['commercial_invoice']) && $_FILES['commercial_invoice']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = FCPATH . 'uploads/courier/commercial_invoices/';
            if (!is_dir($upload_dir)) {
                @mkdir($upload_dir, 0755, true);
            }
            $file_ext = pathinfo($_FILES['commercial_invoice']['name'], PATHINFO_EXTENSION);
            $new_filename = 'ci_' . time() . '_' . random_int(1000, 9999) . '.' . $file_ext;
            if (move_uploaded_file($_FILES['commercial_invoice']['tmp_name'], $upload_dir . $new_filename)) {
                $commercial_invoice_file = $new_filename;
            }
        }

        $shipment_data = [
            'status_id'         => 1,
            'shipping_mode'     => $shipping_mode,
            'shipping_category' => 'domestic',
            'company_type'      => 'individual',
            'tracking_id'       => $ref,
            'waybill_number'    => $ref,
            'sender_id'         => $sender_id,
            'recipient_id'      => $recipient_id,
            'invoice_id'        => 0,
            'company_id'        => NULL,
            'packaging_charges' => 0,
            'staff_id'          => 0,
            'commercial_invoice_file' => $commercial_invoice_file,
            'created_at'        => date('Y-m-d H:i:s'),
        ];

        if ($this->db->field_exists('is_portal_request', db_prefix() . '_shipments')) {
            $shipment_data['is_portal_request'] = 1;
        }
        if ($this->db->field_exists('quoted_amount', db_prefix() . '_shipments') && $quoted_amount > 0) {
            $shipment_data['quoted_amount'] = $quoted_amount;
        }

        // Attach special instructions if the column exists
        if ($this->db->field_exists('special_instructions', db_prefix() . '_shipments') && $instructions !== '') {
            $shipment_data['special_instructions'] = $instructions;
        }

        $this->load->model('Shipment_model');
        $shipment_id = $this->Shipment_model->add($shipment_data);

        if ($shipment_id) {
            // Log the status creation
            $this->db->insert(db_prefix() . '_shipment_status_history', [
                'shipment_id' => $shipment_id,
                'status_id'   => 1,
                'changed_at'  => date('Y-m-d H:i:s'),
            ]);

            // Save package details so consignment note shows them
            if ($description) {
                $vol_weight    = ($length > 0 && $width > 0 && $height > 0) ? round(($length * $width * $height) / 6000, 3) : 0;
                $chargeable    = max($weight, $vol_weight);
                $this->db->insert(db_prefix() . '_shipment_packages', [
                    'shipment_id'        => $shipment_id,
                    'quantity'           => 1,
                    'description'        => $description,
                    'weight'             => $weight,
                    'length'             => $length,
                    'width'              => $width,
                    'height'             => $height,
                    'weight_volume'      => $vol_weight,
                    'chargeable_weight'  => $chargeable > 0 ? $chargeable : $weight,
                ]);
            }

            // Save vehicle type into pickup
            if ($vehicle_type) {
                $this->db->insert(db_prefix() . '_pickups', [
                    'pickup_date'       => date('Y-m-d'),
                    'pickup_start_time' => '08:00',
                    'pickup_end_time'   => '17:00',
                    'address'           => $sender_address,
                    'pickup_zip'        => '',
                    'address_type'      => 'Residential',
                    'vehicle_type'      => $vehicle_type,
                    'shipment_id'       => $shipment_id,
                    'staff_id'          => 0,
                    'driver_id'         => 0,
                    'created_at'        => date('Y-m-d H:i:s'),
                ]);
            }

            // Send Emails
            $this->load->model('emails_model');

            // Send email to Customer (Sender)
            if ($sender_email) {
                $subj = "Shipment Request Received - " . $ref;
                $msg  = "Hello " . htmlspecialchars($sender_name) . ",<br><br>";
                $msg .= "We have successfully received your shipment request.<br>";
                $msg .= "Your Tracking / Waybill Number is: <b>" . $ref . "</b><br>";
                $msg .= "Our team will review your request and contact you shortly with pricing details.<br><br>";
                $msg .= "Thank you for choosing us.";
                $this->emails_model->send_simple_email($sender_email, $subj, $msg);
            }

            // Send email to Recipient
            if ($recip_email) {
                $subj = "Incoming Shipment Notification - " . $ref;
                $msg  = "Hello " . htmlspecialchars($recip_name) . ",<br><br>";
                $msg .= "A shipment request has been created by " . htmlspecialchars($sender_name) . " intended for you.<br>";
                $msg .= "Tracking / Waybill Number: <b>" . $ref . "</b><br>";
                $msg .= "You will be updated once the shipment is processed and dispatched.<br><br>";
                $msg .= "Thank you.";
                $this->emails_model->send_simple_email($recip_email, $subj, $msg);
            }

            // Send email to Admins
            $admins = $this->db->where('admin', 1)->get(db_prefix() . 'staff')->result();
            if (!empty($admins)) {
                $subj = "New Shipment Request - " . $ref;
                $msg  = "A new shipment request has been created via the customer portal.<br><br>";
                $msg .= "Sender: " . htmlspecialchars($sender_name) . "<br>";
                $msg .= "Recipient: " . htmlspecialchars($recip_name) . "<br>";
                $msg .= "Tracking: " . $ref . "<br>";
                $msg .= "Vehicle Type: " . htmlspecialchars($vehicle_type) . "<br>";
                $msg .= "<br>Please log in to the admin portal to review.";
                foreach ($admins as $admin) {
                    if (!empty($admin->email)) {
                        $this->emails_model->send_simple_email($admin->email, $subj, $msg);
                    }
                }
            }

            echo json_encode([
                'status'          => 'success',
                'message'         => 'Shipment request submitted.',
                'tracking_number' => $ref,
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Could not save shipment. Please try again.']);
        }
    }

    public function public_create_shipment()
    {
        redirect('courier_goshipping/create-shipment');
    }

    public function tariff_zones()
    {
        header('Content-Type: application/json');
        $zones = $this->db->order_by('zone_code', 'ASC')->get(db_prefix() . '_courier_tariff_zones')->result_array();
        echo json_encode(['status' => 'success', 'data' => $zones]);
    }

    /**
     * Return all countries from the Perfex countries table, ordered alphabetically.
     */
    public function get_countries()
    {
        header('Content-Type: application/json');
        $countries = $this->db
            ->select('country_id AS id, short_name AS name, iso2')
            ->order_by('short_name', 'ASC')
            ->get('countries')
            ->result_array();
        echo json_encode(['status' => 'success', 'data' => $countries]);
    }

    /**
     * List of local towns/cities used for domestic (city-to-city) quote pickers.
     * Sourced from the existing service-points list (no country API call needed).
     */
    public function get_domestic_cities()
    {
        header('Content-Type: application/json');
        $cities = $this->db
            ->select('name')
            ->order_by('name', 'ASC')
            ->get(db_prefix() . '_courier_service_points')
            ->result_array();
        echo json_encode(['status' => 'success', 'data' => $cities]);
    }

    /**
     * Proxy endpoint: fetch cities/towns for a given country name.
     * Uses countriesnow.space API (free, no key required).
     * Falls back to empty list on failure.
     */
    public function get_cities()
    {
        header('Content-Type: application/json');

        $country = trim($this->input->post('country') ?: $this->input->get('country'));
        if ($country === '') {
            echo json_encode(['status' => 'error', 'message' => 'Country name required.']);
            return;
        }

        // Cache key: cities_{md5 of country}
        $cache_key = 'courier_cities_' . md5(strtolower($country));
        @$this->load->driver('cache', ['adapter' => 'file']);
        
        $cached = false;
        if (isset($this->cache)) {
            $cached = @$this->cache->get($cache_key);
        }

        if ($cached !== false) {
            if (ob_get_length()) ob_clean();
            echo json_encode(['status' => 'success', 'data' => $cached]);
            return;
        }

        $cities = [];
        $api_url = 'https://countriesnow.space/api/v0.1/countries/cities/q?country=' . urlencode($country);

        if (function_exists('curl_init')) {
            $ch = curl_init($api_url);
            curl_setopt_array($ch, [
                CURLOPT_HTTPHEADER     => ['Accept: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $raw   = curl_exec($ch);
            $errno = curl_errno($ch);
            curl_close($ch);

            if (!$errno && $raw) {
                $parsed = json_decode($raw, true);
                if (!empty($parsed) && empty($parsed['error']) && !empty($parsed['data'])) {
                    $cities = $parsed['data'];
                    sort($cities);
                }
            }
        } else {
            // fallback: file_get_contents with stream context
            $ctx = stream_context_create([
                'http' => [
                    'method'  => 'GET',
                    'header'  => "Accept: application/json\r\n",
                    'timeout' => 10,
                ],
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            ]);
            $raw = @file_get_contents($api_url, false, $ctx);
            if ($raw) {
                $parsed = json_decode($raw, true);
                if (!empty($parsed) && empty($parsed['error']) && !empty($parsed['data'])) {
                    $cities = $parsed['data'];
                    sort($cities);
                }
            }
        }

        // Cache for 24 hours
        if (!empty($cities) && isset($this->cache)) {
            @$this->cache->save($cache_key, $cities, 86400);
        }

        if (ob_get_length()) ob_clean();
        echo json_encode(['status' => 'success', 'data' => $cities]);
    }

    private function _save_quote_and_respond($response_array)
    {
        if ($response_array['status'] === 'success' && !empty($response_array['data'])) {
            $name    = trim($this->input->post('contact_name') ?: '');
            $company = trim($this->input->post('contact_company') ?: '');
            $email   = trim($this->input->post('contact_email') ?: '');
            $phone   = trim($this->input->post('contact_phone') ?: '');
            
            if ($name !== '' || $company !== '' || $email !== '' || $phone !== '') {
                $this->db->insert('tblcourier_client_quotes', [
                    'name'          => $name,
                    'email'         => $email,
                    'phone'         => $phone,
                    'company'       => $company,
                    'quote_details' => json_encode($response_array['data']),
                    'created_at'    => date('Y-m-d H:i:s')
                ]);
            }
        }
        echo json_encode($response_array);
    }

    public function calculate_quote()
    {
        header('Content-Type: application/json');

        $cargo_type      = trim($this->input->post('cargo_type')); // 'document' or 'parcel'
        $zone_code       = strtolower(trim($this->input->post('zone')));  // a-f
        $weight          = (float)$this->input->post('weight');
        $length          = (float)$this->input->post('length');
        $width           = (float)$this->input->post('width');
        $height          = (float)$this->input->post('height');
        $service_type    = trim($this->input->post('service_type') ?: ''); // freight service mode
        $container       = strtolower(trim($this->input->post('container') ?: '')); // FCL container type
        $origin_country  = trim($this->input->post('origin_country') ?: '');
        $dest_country    = trim($this->input->post('country') ?: '');

        $valid_service_types = ['domestic','road','lcl','consolidation','air_freight','air_consolidation','courier','fcl'];

        // --- FCL path: fixed rate per container, no weight/zone needed ---
        if ($service_type === 'fcl') {
            $valid_containers = ['20dv','40dv','20hc','40hc','20rf','40rf','20fr','40fr','roro'];
            if (!in_array($container, $valid_containers)) {
                echo json_encode(['status' => 'error', 'message' => 'Please select a valid container type.']);
                return;
            }
            $rate = (float)(get_option('courier_rate_sea_fcl_' . $container) ?: 0);
            if ($rate <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'FCL rate for this container type is not configured. Please contact us for a quote.']);
                return;
            }
            $handling = (float)(get_option('courier_parcel_handling_fee') ?: 100);
            $vat_rate = (float)(get_option('courier_parcel_vat_rate') ?: 16) / 100;
            $subtotal = $rate + $handling;
            $vat      = round($subtotal * $vat_rate, 2);
            $total    = round($subtotal + $vat, 2);
            $container_labels = [
                '20dv' => "20' Dry Van", '40dv' => "40' Dry Van",
                '20hc' => "20' High Cube", '40hc' => "40' High Cube",
                '20rf' => "20' Reefer", '40rf' => "40' Reefer",
                '20fr' => "20' Flat Rack", '40fr' => "40' Flat Rack",
                'roro' => 'RoRo',
            ];
            $this->_save_quote_and_respond([
                'status' => 'success',
                'data'   => [
                    'service_type'      => 'fcl',
                    'cargo_type'        => 'fcl',
                    'container'         => strtoupper($container),
                    'container_label'   => $container_labels[$container] ?? strtoupper($container),
                    'zone_code'         => '',
                    'zone_name'         => 'FCL Shipment',
                    'actual_weight'     => 0,
                    'volumetric_weight' => 0,
                    'chargeable_weight' => 0,
                    'rate_type'         => 'flat',
                    'rate'              => $rate,
                    'transport'         => round($rate, 2),
                    'handling'          => $handling,
                    'subtotal'          => round($subtotal, 2),
                    'vat_rate'          => round($vat_rate * 100, 2),
                    'vat_amount'        => $vat,
                    'total'             => $total,
                ],
            ]);
            return;
        }

        // --- Standard path (all non-FCL services) ---
        if (!in_array($cargo_type, ['document', 'parcel'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid cargo type.']);
            return;
        }
        if (!empty($zone_code) && !in_array($zone_code, ['a','b','c','d','e','f'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid zone selected.']);
            return;
        }

        // Determine dimensional factor based on service type
        $dim_factors = [
            'domestic'          => $this->DimensionalFactor_model->get_value_by_name('default')       ?: 5000,
            'road'              => $this->DimensionalFactor_model->get_value_by_name('default')       ?: 5000,
            'courier'           => $this->DimensionalFactor_model->get_value_by_name('default')       ?: 5000,
            'lcl'               => $this->DimensionalFactor_model->get_value_by_name('sea_lcl')       ?: 1000,
            'consolidation'     => $this->DimensionalFactor_model->get_value_by_name('sea_lcl')       ?: 1000,
            'air_freight'       => $this->DimensionalFactor_model->get_value_by_name('air_freight')   ?: 6000,
            'air_consolidation' => $this->DimensionalFactor_model->get_value_by_name('air_consolidation') ?: 6000,
        ];
        $dim_factor = (in_array($service_type, $valid_service_types) && isset($dim_factors[$service_type]))
            ? (int)$dim_factors[$service_type]
            : 6000; // default to air factor

        // Volumetric weight using service-appropriate dimensional factor
        $volumetric = ($length > 0 && $width > 0 && $height > 0)
            ? ($length * $width * $height) / $dim_factor
            : 0;

        // Chargeable = max(actual, volumetric)
        $chargeable = max($weight ?: 0, $volumetric);

        // Documents are capped at 350 g
        if ($cargo_type === 'document') {
            if ($chargeable <= 0) $chargeable = 0.35;
            $chargeable = min($chargeable, 0.35);
            $weight     = $weight ?: 0.35;
        }

        if ($chargeable <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Please enter a valid weight.']);
            return;
        }

        // Zone F availability check
        if ($zone_code === 'f') {
            $zone = $this->db->where('zone_code', 'F')->get(db_prefix() . '_courier_tariff_zones')->row_array();
            if (!empty($zone) && empty($zone['is_available'])) {
                echo json_encode(['status' => 'error', 'message' => 'Zone F is not available online. Please contact us directly for a quote.']);
                return;
            }
        }

        // Read configurable fee/VAT settings
        $handling = (float)(get_option('courier_parcel_handling_fee') ?: 100);
        $vat_rate = (float)(get_option('courier_parcel_vat_rate')     ?: 16) / 100;

        // --- Path D: domestic city-to-city (international/freight mode only) ---
        if ($service_type === 'domestic' && get_option('courier_type') === 'international') {
            $origin_city = trim($this->input->post('origin_city') ?: '');
            $dest_city   = trim($this->input->post('destination_city') ?: '');
            if ($origin_city !== '' && $dest_city !== '') {
                $domestic_tbl = db_prefix() . '_courier_domestic_tariffs';
                if ($this->db->table_exists($domestic_tbl)) {
                    $domestic_row = $this->db
                        ->where('origin_city', $origin_city)
                        ->where('destination_city', $dest_city)
                        ->where('weight_min <=', $chargeable)
                        ->where('weight_max >=', $chargeable)
                        ->get($domestic_tbl)
                        ->row_array();

                    if ($domestic_row) {
                        $rate = (float)$domestic_row['rate'];
                        if ($rate <= 0) {
                            echo json_encode(['status' => 'error', 'message' => 'Rate not configured for ' . $origin_city . ' → ' . $dest_city . '. Please contact us.']);
                            return;
                        }
                        $transport = ($domestic_row['rate_type'] === 'per_kg') ? $rate * $chargeable : $rate;
                        $subtotal  = $transport + $handling;
                        $vat       = round($subtotal * $vat_rate, 2);
                        $total     = round($subtotal + $vat, 2);

                        $this->_save_quote_and_respond([
                            'status' => 'success',
                            'data'   => [
                                'service_type'      => 'domestic',
                                'cargo_type'        => $cargo_type,
                                'zone_code'         => '',
                                'zone_name'         => $origin_city . ' - ' . $dest_city,
                                'actual_weight'     => round($weight, 3),
                                'volumetric_weight' => round($volumetric, 3),
                                'chargeable_weight' => round($chargeable, 3),
                                'dim_factor'        => $dim_factor,
                                'rate_type'         => $domestic_row['rate_type'],
                                'rate'              => $rate,
                                'transport'         => round($transport, 2),
                                'handling'          => $handling,
                                'subtotal'          => round($subtotal, 2),
                                'vat_rate'          => round($vat_rate * 100, 2),
                                'vat_amount'        => $vat,
                                'total'             => $total,
                            ],
                        ]);
                        return;
                    }
                    // No exact city-pair match — fall through to zone-based paths below
                }
            }
        }

        // Zone label
        $zone_obj  = $this->db->where('zone_code', strtoupper($zone_code))->get(db_prefix() . '_courier_tariff_zones')->row_array();
        $zone_name = $zone_obj ? $zone_obj['name'] : 'Zone ' . strtoupper($zone_code);

        if (empty($zone_code)) {
            if ($service_type === 'domestic') {
                echo json_encode(['status' => 'error', 'message' => 'Please select a delivery zone.']);
                return;
            } elseif ($service_type !== 'fcl') {
                // For Air/Road/International, use a flat rate (defaulting to Zone A column)
                $zone_code = 'a';
                $zone_name = 'Standard International';
            }
        }
        
        $rates_table = db_prefix() . '_courier_tariff_rates';
        $zone_col    = 'zone_' . $zone_code;

        // --- Path A0: origin-based international tariff (highest priority for freight) ---
        $origin_tbl = db_prefix() . '_courier_origin_tariffs';
        if ($origin_country !== '' && $dest_country !== '' && $service_type && in_array($service_type, $valid_service_types) && $service_type !== 'fcl' && $service_type !== 'domestic') {
            if ($this->db->table_exists($origin_tbl)) {
                $origin_row = $this->db
                    ->where('origin_country', $origin_country)
                    ->where('destination_country', $dest_country)
                    ->where('service_type', $service_type)
                    ->where('weight_min <=', $chargeable)
                    ->where('weight_max >=', $chargeable)
                    ->order_by('weight_max', 'ASC')
                    ->get($origin_tbl)
                    ->row_array();

                if ($origin_row) {
                    $rate = (float)$origin_row['rate'];
                    if ($rate <= 0) {
                        echo json_encode(['status' => 'error', 'message' => 'Rate not configured for ' . $origin_country . ' → ' . $dest_country . ' (' . $service_type . '). Please contact us.']);
                        return;
                    }
                    $transport = ($origin_row['rate_type'] === 'per_kg') ? $rate * $chargeable : $rate;
                    $subtotal  = $transport + $handling;
                    $vat       = round($subtotal * $vat_rate, 2);
                    $total     = round($subtotal + $vat, 2);

                    $this->_save_quote_and_respond([
                        'status' => 'success',
                        'data'   => [
                            'service_type'      => $service_type,
                            'cargo_type'        => $cargo_type,
                            'zone_code'         => '',
                            'zone_name'         => $origin_country . ' → ' . $dest_country,
                            'actual_weight'     => round($weight, 3),
                            'volumetric_weight' => round($volumetric, 3),
                            'chargeable_weight' => round($chargeable, 3),
                            'dim_factor'        => $dim_factor,
                            'rate_type'         => $origin_row['rate_type'],
                            'rate'              => $rate,
                            'transport'         => round($transport, 2),
                            'handling'          => $handling,
                            'subtotal'          => round($subtotal, 2),
                            'vat_rate'          => round($vat_rate * 100, 2),
                            'vat_amount'        => $vat,
                            'total'             => $total,
                        ],
                    ]);
                    return;
                }
                // No origin row found for this exact route — fall through to zone-based paths
            }
        }

        // --- Path A: freight service_type lookup (service-type-specific tariff rows) ---
        if ($service_type && in_array($service_type, $valid_service_types) && $service_type !== 'fcl') {
            if ($this->db->table_exists($rates_table) && $this->db->field_exists('service_type', $rates_table)) {
                $rate_row = $this->db
                    ->where('service_type', $service_type)
                    ->where('weight_min <=', $chargeable)
                    ->where('weight_max >=', $chargeable)
                    ->get($rates_table)
                    ->row_array();

                if ($rate_row) {
                    $rate = (float)($rate_row[$zone_col] ?? 0);
                    if ($rate <= 0) {
                        echo json_encode(['status' => 'error', 'message' => 'This zone is not available for ' . $service_type . ' online. Please contact us.']);
                        return;
                    }
                    $transport = ($rate_row['rate_type'] === 'per_kg') ? $rate * $chargeable : $rate;
                    $subtotal  = $transport + $handling;
                    $vat       = round($subtotal * $vat_rate, 2);
                    $total     = round($subtotal + $vat, 2);

                    $this->_save_quote_and_respond([
                        'status' => 'success',
                        'data'   => [
                            'service_type'      => $service_type,
                            'cargo_type'        => $cargo_type,
                            'zone_code'         => strtoupper($zone_code),
                            'zone_name'         => $zone_name,
                            'actual_weight'     => round($weight, 3),
                            'volumetric_weight' => round($volumetric, 3),
                            'chargeable_weight' => round($chargeable, 3),
                            'dim_factor'        => $dim_factor,
                            'rate_type'         => $rate_row['rate_type'],
                            'rate'              => $rate,
                            'transport'         => round($transport, 2),
                            'handling'          => $handling,
                            'subtotal'          => round($subtotal, 2),
                            'vat_rate'          => round($vat_rate * 100, 2),
                            'vat_amount'        => $vat,
                            'total'             => $total,
                        ],
                    ]);
                    return;
                }
                // No service-type rows found — fall through to default paths below
            }
        }

        // --- Path B: settings-based per-kg rate for parcels (quick override) ---
        $settings_rate_per_kg = (float)get_option('courier_parcel_rate_per_kg');
        if ($cargo_type === 'parcel' && $settings_rate_per_kg > 0 && (!$service_type || $service_type === 'domestic')) {
            $transport = round($settings_rate_per_kg * $chargeable, 2);
            $subtotal  = $transport + $handling;
            $vat       = round($subtotal * $vat_rate, 2);
            $total     = round($subtotal + $vat, 2);

            $this->_save_quote_and_respond([
                'status' => 'success',
                'data'   => [
                    'service_type'      => $service_type ?: 'courier',
                    'cargo_type'        => $cargo_type,
                    'zone_code'         => strtoupper($zone_code),
                    'zone_name'         => $zone_name,
                    'actual_weight'     => round($weight, 3),
                    'volumetric_weight' => round($volumetric, 3),
                    'chargeable_weight' => round($chargeable, 3),
                    'dim_factor'        => $dim_factor,
                    'rate_type'         => 'per_kg',
                    'rate'              => $settings_rate_per_kg,
                    'transport'         => $transport,
                    'handling'          => $handling,
                    'subtotal'          => round($subtotal, 2),
                    'vat_rate'          => round($vat_rate * 100, 2),
                    'vat_amount'        => $vat,
                    'total'             => $total,
                ],
            ]);
            return;
        }

        // --- Path C: zone tariff table fallback (legacy cargo_type lookup) ---
        if (!$this->db->table_exists($rates_table)) {
            echo json_encode(['status' => 'error', 'message' => 'Tariff rates have not been configured yet. Please contact us for a quote.']);
            return;
        }

        $rate_row = $this->db
            ->where('cargo_type', $cargo_type)
            ->where('weight_min <=', $chargeable)
            ->where('weight_max >=', $chargeable)
            ->get($rates_table)
            ->row_array();

        if (!$rate_row) {
            $msg = $service_type
                ? "No rate configured for {$service_type} at this weight range. Please contact us."
                : 'No rate found for this weight. Please contact us for a custom quote.';
            echo json_encode(['status' => 'error', 'message' => $msg]);
            return;
        }

        $rate = (float)($rate_row[$zone_col] ?? 0);
        if ($rate <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'This zone is not available for online quotation. Please contact us directly.']);
            return;
        }

        $transport = ($rate_row['rate_type'] === 'per_kg') ? $rate * $chargeable : $rate;
        $subtotal  = $transport + $handling;
        $vat       = round($subtotal * $vat_rate, 2);
        $total     = round($subtotal + $vat, 2);

        $this->_save_quote_and_respond([
            'status' => 'success',
            'data'   => [
                'service_type'      => $service_type ?: 'courier',
                'cargo_type'        => $cargo_type,
                'zone_code'         => strtoupper($zone_code),
                'zone_name'         => $zone_name,
                'actual_weight'     => round($weight, 3),
                'volumetric_weight' => round($volumetric, 3),
                'chargeable_weight' => round($chargeable, 3),
                'dim_factor'        => $dim_factor,
                'rate_type'         => $rate_row['rate_type'],
                'rate'              => $rate,
                'transport'         => round($transport, 2),
                'handling'          => $handling,
                'subtotal'          => round($subtotal, 2),
                'vat_rate'          => round($vat_rate * 100, 2),
                'vat_amount'        => $vat,
                'total'             => $total,
            ],
        ]);
    }

    public function send_quote_email()
    {
        header('Content-Type: application/json');

        $email      = trim($this->input->post('email'));
        $quote_json = $this->input->post('quote_data');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
            return;
        }

        $q = json_decode($quote_json, true);
        if (!$q || empty($q['total'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid quote data. Please recalculate and try again.']);
            return;
        }

        $_ci           = courier_get_invoice_info();
        $company_name  = $_ci['name']  ?: 'Courier Services';
        $company_email = $_ci['email'] ?: get_option('email');
        $company_phone = $_ci['phone'] ?: '';
        $ref           = 'QT-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

        $cargo_label = ucfirst($q['cargo_type'] ?? 'parcel');
        $zone_name   = htmlspecialchars($q['zone_name'] ?? ('Zone ' . ($q['zone_code'] ?? '')));
        $country     = htmlspecialchars(trim($q['country'] ?? ''));
        $city        = htmlspecialchars(trim($q['city'] ?? ''));
        $destination = implode(', ', array_filter([$city, $country])) ?: $zone_name;

        $fmt = function($n) { return 'KES ' . number_format((float)$n, 2); };

        $body = '
<div style="font-family:Arial,sans-serif;max-width:620px;margin:0 auto;border:1px solid #e0e0e0;">
  <div style="background:#1a5276;padding:22px 28px;">
    <h2 style="color:#fff;margin:0;font-size:20px;">' . htmlspecialchars($company_name) . '</h2>
    <p style="color:#aed6f1;margin:4px 0 0;font-size:12px;">Shipment Quotation</p>
  </div>
  <div style="padding:24px 28px;background:#fff;">
    <p style="margin:0 0 4px;font-size:12px;color:#888;">Reference Number</p>
    <p style="margin:0 0 20px;font-size:22px;font-weight:bold;color:#1a5276;">' . $ref . '</p>
    <table style="width:100%;border-collapse:collapse;font-size:14px;">
      <tr style="background:#f4f6f7;">
        <td style="padding:9px 13px;border:1px solid #e0e0e0;font-weight:bold;width:44%;">Cargo Type</td>
        <td style="padding:9px 13px;border:1px solid #e0e0e0;">' . $cargo_label . '</td>
      </tr>
      <tr>
        <td style="padding:9px 13px;border:1px solid #e0e0e0;font-weight:bold;">Destination</td>
        <td style="padding:9px 13px;border:1px solid #e0e0e0;">' . $destination . ' &mdash; ' . $zone_name . '</td>
      </tr>
      <tr style="background:#f4f6f7;">
        <td style="padding:9px 13px;border:1px solid #e0e0e0;font-weight:bold;">Actual Weight</td>
        <td style="padding:9px 13px;border:1px solid #e0e0e0;">' . number_format((float)($q['actual_weight'] ?? 0), 3) . ' kg</td>
      </tr>';

        if (!empty($q['volumetric_weight']) && $q['volumetric_weight'] > 0) {
            $body .= '
      <tr>
        <td style="padding:9px 13px;border:1px solid #e0e0e0;font-weight:bold;">Volumetric Weight</td>
        <td style="padding:9px 13px;border:1px solid #e0e0e0;">' . number_format((float)$q['volumetric_weight'], 3) . ' kg</td>
      </tr>';
        }

        $body .= '
      <tr style="background:#f4f6f7;">
        <td style="padding:9px 13px;border:1px solid #e0e0e0;font-weight:bold;">Chargeable Weight</td>
        <td style="padding:9px 13px;border:1px solid #e0e0e0;font-weight:bold;">' . number_format((float)($q['chargeable_weight'] ?? 0), 3) . ' kg</td>
      </tr>
      <tr>
        <td style="padding:9px 13px;border:1px solid #e0e0e0;font-weight:bold;">Transport Charge</td>
        <td style="padding:9px 13px;border:1px solid #e0e0e0;">' . $fmt($q['transport'] ?? 0) . '</td>
      </tr>
      <tr style="background:#f4f6f7;">
        <td style="padding:9px 13px;border:1px solid #e0e0e0;font-weight:bold;">Handling Fee</td>
        <td style="padding:9px 13px;border:1px solid #e0e0e0;">' . $fmt($q['handling'] ?? 100) . '</td>
      </tr>
      <tr>
        <td style="padding:9px 13px;border:1px solid #e0e0e0;font-weight:bold;">Subtotal</td>
        <td style="padding:9px 13px;border:1px solid #e0e0e0;">' . $fmt($q['subtotal'] ?? 0) . '</td>
      </tr>
      <tr style="background:#f4f6f7;">
        <td style="padding:9px 13px;border:1px solid #e0e0e0;font-weight:bold;">VAT (16%)</td>
        <td style="padding:9px 13px;border:1px solid #e0e0e0;">' . $fmt($q['vat_amount'] ?? 0) . '</td>
      </tr>
      <tr style="background:#eafaf1;">
        <td style="padding:12px 13px;border:1px solid #a9dfbf;font-weight:bold;font-size:16px;">TOTAL (VAT Inclusive)</td>
        <td style="padding:12px 13px;border:1px solid #a9dfbf;font-weight:bold;font-size:16px;color:#1e8449;">' . $fmt($q['total'] ?? 0) . '</td>
      </tr>
    </table>
    <p style="margin:18px 0 0;font-size:11px;color:#888;line-height:1.7;">
      * This quotation is valid for 7 days from date of issue.<br>
      * Final price may vary subject to actual weight verification at our office.<br>
      * Chargeable weight = max(actual weight, volumetric weight). Volumetric weight = L × W × H ÷ 6000.
    </p>
  </div>
  <div style="background:#f4f6f7;padding:12px 28px;border-top:1px solid #e0e0e0;">
    <p style="margin:0;font-size:12px;color:#555;">
      <strong>' . htmlspecialchars($company_name) . '</strong>'
      . ($company_phone ? ' &nbsp;|&nbsp; Tel: ' . htmlspecialchars($company_phone) : '')
      . ($company_email ? ' &nbsp;|&nbsp; Email: ' . htmlspecialchars($company_email) : '') . '
    </p>
  </div>
</div>';

        $this->load->model('emails_model');
        $subject = "Shipment Quotation {$ref} - {$company_name}";
        
        if ($this->emails_model->send_simple_email($email, $subject, $body)) {
            echo json_encode(['status' => 'success', 'message' => "Quotation sent to {$email}. Please check your inbox."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send email. Please check the system SMTP settings.']);
        }
    }
}
