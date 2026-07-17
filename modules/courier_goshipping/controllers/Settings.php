<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Settings extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        if (!is_admin() && !has_permission('courier-settings', '', 'view_settings')) {
            access_denied('Courier - Settings');
        }
        $this->load->helper('courier_goshipping/courier'); // Load the helper specific to the courier module
        // MX's model loader lowercases the whole path then only ucfirst()'s
        // the first letter before checking is_file(), so this multi-capital
        // filename never resolves on case-sensitive (Linux) fs.
        courier_load_model('DimensionalFactor_model');
        $this->load->library('form_validation');

    }

    public function main()
    {

        $group = $this->input->get('group', true) ?? 'customization';
        $data['group'] = $group;

        switch ($group) {
            case 'customization':
                $data['title'] = _l('Dashboard');
                $data['dimensional_factor'] = $this->DimensionalFactor_model->get();
                $data['courier_logistic_company'] = get_option('courier_logistic_company');
                $data['courier_waybill_prefix']   = get_option('courier_waybill_prefix');
                $data['courier_type']             = get_option('courier_type') ?: 'international';
                $data['group_content'] = $this->load->view('settings/customization', $data, true);
                break;

            case 'international_tariffs':
                $data['title'] = 'International Tariffs (Origin-Based)';
                $origin_tbl = db_prefix() . '_courier_origin_tariffs';
                $data['origin_tariff_origins'] = $this->db->table_exists($origin_tbl)
                    ? $this->db->select('origin_country, COUNT(*) as rate_count')
                        ->group_by('origin_country')
                        ->order_by('origin_country', 'ASC')
                        ->get($origin_tbl)->result_array()
                    : [];
                $data['group_content'] = $this->load->view('settings/international_tariffs', $data, true);
                break;

            case 'service_points':
                $data['title'] = 'Service Points';
                $data['service_points'] = $this->db
                    ->select('sp.*, c.short_name AS country_name')
                    ->from(db_prefix() . '_courier_service_points sp')
                    ->join('countries c', 'c.country_id = sp.country_id', 'left')
                    ->order_by('sp.sort_order', 'ASC')
                    ->order_by('sp.name', 'ASC')
                    ->get()->result_array();
                $data['countries'] = $this->db
                    ->order_by('short_name', 'ASC')
                    ->get('countries')->result();
                $data['group_content'] = $this->load->view('settings/service_points', $data, true);
                break;

            case 'invoice_info':
                $data['title'] = 'Invoice & Receipt Info';
                $data['group_content'] = $this->load->view('settings/invoice_info', $data, true);
                break;

            case 'appearance':
                $data['title'] = 'Appearance';
                $data['courier_theme_primary_color']   = get_option('courier_theme_primary_color')   ?: '#3a6ea5';
                $data['courier_theme_secondary_color'] = get_option('courier_theme_secondary_color') ?: '#c1272d';
                $data['group_content'] = $this->load->view('settings/appearance', $data, true);
                break;

            case 'tariff':
                $data['title'] = 'Tariff & Quotation';
                $data['courier_type'] = get_option('courier_type') ?: 'international';
                // Auto-migrate: add service_type column if missing
                $_rates_tbl = db_prefix() . '_courier_tariff_rates';
                if ($this->db->table_exists($_rates_tbl) && !$this->db->field_exists('service_type', $_rates_tbl)) {
                    $this->db->query("ALTER TABLE `{$_rates_tbl}` ADD COLUMN `service_type` VARCHAR(30) NULL DEFAULT NULL AFTER `id`");
                }
                $data['tariff_zones'] = $this->db
                    ->order_by('zone_code', 'ASC')
                    ->get(db_prefix() . '_courier_tariff_zones')
                    ->result_array();
                $data['tariff_rates'] = $this->db
                    ->order_by('service_type', 'ASC')
                    ->order_by('cargo_type', 'ASC')
                    ->order_by('weight_min', 'ASC')
                    ->get(db_prefix() . '_courier_tariff_rates')
                    ->result_array();
                $data['courier_parcel_rate_per_kg']  = get_option('courier_parcel_rate_per_kg');
                $data['courier_parcel_handling_fee'] = get_option('courier_parcel_handling_fee') ?: '100';
                $data['courier_parcel_vat_rate']     = get_option('courier_parcel_vat_rate')     ?: '16';
                // Origin-based tariff summary (distinct origins with rate counts)
                $origin_tbl = db_prefix() . '_courier_origin_tariffs';
                $data['origin_tariff_origins'] = $this->db->table_exists($origin_tbl)
                    ? $this->db->select('origin_country, COUNT(*) as rate_count')
                        ->group_by('origin_country')
                        ->order_by('origin_country', 'ASC')
                        ->get($origin_tbl)->result_array()
                    : [];
                // Domestic city-to-city tariff summary (international mode only)
                $domestic_tbl = db_prefix() . '_courier_domestic_tariffs';
                $data['domestic_tariff_origins'] = $this->db->table_exists($domestic_tbl)
                    ? $this->db->select('origin_city, COUNT(*) as rate_count')
                        ->group_by('origin_city')
                        ->order_by('origin_city', 'ASC')
                        ->get($domestic_tbl)->result_array()
                    : [];
                $data['service_point_cities'] = $this->db
                    ->select('name')
                    ->order_by('name', 'ASC')
                    ->get(db_prefix() . '_courier_service_points')
                    ->result_array();
                $data['group_content'] = $this->load->view('settings/tariff', $data, true);
                break;

            default:
                $data['group_content'] = $this->load->view('customization', [], true);
                break;
        }

        if ($this->router->fetch_method() == 'main' && !$this->input->is_ajax_request()) {
            $this->load->view('settings/main', $data);
        }

    }

    public function customization()
    {
        $this->load->view('settings/customization');
    }

    public function general()
    {
        $logistic_company = $this->input->post('courier_logistic_company');
        $waybill_prefix   = $this->input->post('courier_waybill_prefix');
        $courier_type     = $this->input->post('courier_type');

        if ($logistic_company !== null) {
            update_option('courier_logistic_company', trim($logistic_company));
        }
        if ($waybill_prefix !== null) {
            update_option('courier_waybill_prefix', strtoupper(trim($waybill_prefix)));
        }
        if (in_array($courier_type, ['local', 'international'])) {
            update_option('courier_type', $courier_type);
        }

        $color = $this->input->post('courier_invoice_color');
        if ($color && preg_match('/^#[0-9a-fA-F]{3,8}$/', $color)) {
            update_option('courier_invoice_color', $color);
        }

        $color_2 = $this->input->post('courier_invoice_color_2');
        if ($color_2 && preg_match('/^#[0-9a-fA-F]{3,8}$/', $color_2)) {
            update_option('courier_invoice_color_2', $color_2);
        }

        $cn_color = $this->input->post('courier_cn_color');
        if ($cn_color && preg_match('/^#[0-9a-fA-F]{3,8}$/', $cn_color)) {
            update_option('courier_cn_color', $cn_color);
        }

        $cn_tpl = $this->input->post('courier_cn_template');
        if (in_array($cn_tpl, ['standard','shavan','corporate_blue','express_red','thermal'], true)) {
            update_option('courier_cn_template', $cn_tpl);
        }

        $other_offices = $this->input->post('courier_other_offices');
        if ($other_offices !== null) {
            update_option('courier_other_offices', trim($other_offices));
        }
        $cn_special_notes = $this->input->post('courier_cn_special_notes');
        if ($cn_special_notes !== null) {
            update_option('courier_cn_special_notes', trim($cn_special_notes));
        }

        $manifest_tpl = $this->input->post('courier_manifest_template');
        $valid_tpls   = ['cargo_green','dubai_air','classic_table','modern_blue','compact_list'];
        if (in_array($manifest_tpl, $valid_tpls, true)) {
            update_option('courier_manifest_template', $manifest_tpl);
        }

        $manifest_color = $this->input->post('courier_manifest_color');
        if ($manifest_color && preg_match('/^#[0-9a-fA-F]{6}$/', $manifest_color)) {
            update_option('courier_manifest_color', $manifest_color);
        }

        $pt_mode = $this->input->post('courier_payment_terms_mode');
        if (in_array($pt_mode, ['manual', 'automatic'])) {
            update_option('courier_payment_terms_mode', $pt_mode);
        }
        $pt_default = $this->input->post('courier_default_payment_terms');
        if ($pt_default !== null) {
            update_option('courier_default_payment_terms', trim($pt_default));
        }

        set_alert('success', 'General settings updated successfully.');
        redirect('admin/courier/settings/main?group=customization');
    }

    public function save_parcel_rate()
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') { show_404(); }

        $rate        = $this->input->post('courier_parcel_rate_per_kg');
        $handling    = $this->input->post('courier_parcel_handling_fee');
        $vat         = $this->input->post('courier_parcel_vat_rate');

        if ($rate !== null && is_numeric($rate) && (float)$rate >= 0) {
            update_option('courier_parcel_rate_per_kg', number_format((float)$rate, 2, '.', ''));
        }
        if ($handling !== null && is_numeric($handling) && (float)$handling >= 0) {
            update_option('courier_parcel_handling_fee', number_format((float)$handling, 2, '.', ''));
        }
        if ($vat !== null && is_numeric($vat) && (float)$vat >= 0 && (float)$vat <= 100) {
            update_option('courier_parcel_vat_rate', number_format((float)$vat, 4, '.', ''));
        }

        echo json_encode(['success' => true, 'message' => 'Parcel rate settings saved.']);
    }

    public function save_stamp_info()
    {
        $fields = ['courier_stamp_name', 'courier_stamp_pobox', 'courier_stamp_phone'];
        foreach ($fields as $key) {
            $val = $this->input->post($key);
            if ($val !== null) {
                update_option($key, trim($val));
            }
        }
        set_alert('success', 'Stamp settings updated.');
        redirect('admin/courier/settings/main?group=invoice_info');
    }

    public function save_appearance()
    {
        $hex_pattern = '/^#[0-9a-fA-F]{6}$/';

        $primary = $this->input->post('courier_theme_primary_color');
        if ($primary !== null && preg_match($hex_pattern, $primary)) {
            update_option('courier_theme_primary_color', $primary);
        }

        $secondary = $this->input->post('courier_theme_secondary_color');
        if ($secondary !== null && preg_match($hex_pattern, $secondary)) {
            update_option('courier_theme_secondary_color', $secondary);
        }

        set_alert('success', 'Appearance settings updated.');
        redirect('admin/courier/settings/main?group=appearance');
    }

    public function save_invoice_info()
    {
        $fields = ['courier_inv_company_name', 'courier_inv_email', 'courier_inv_phone',
                   'courier_inv_address', 'courier_inv_website', 'courier_inv_pin', 'courier_inv_tagline'];
        foreach ($fields as $key) {
            $val = $this->input->post($key);
            if ($val !== null) {
                update_option($key, trim($val));
            }
        }
        set_alert('success', 'Invoice & Receipt info updated.');
        redirect('admin/courier/settings/main?group=invoice_info');
    }

    public function freight_rates()
    {
        $per_kg_modes = ['road', 'sea_lcl', 'sea_consolidation', 'air_freight', 'air_consolidation'];
        $fcl_types    = ['20dv', '40dv', '20hc', '40hc', '20rf', '40rf', '20fr', '40fr', 'roro'];

        foreach ($per_kg_modes as $mode) {
            $rate = $this->input->post('courier_rate_' . $mode);
            if ($rate !== null && is_numeric($rate) && (float)$rate >= 0) {
                update_option('courier_rate_' . $mode, number_format((float)$rate, 2, '.', ''));
            }
        }
        foreach ($fcl_types as $type) {
            $rate = $this->input->post('courier_rate_sea_fcl_' . $type);
            if ($rate !== null && is_numeric($rate) && (float)$rate >= 0) {
                update_option('courier_rate_sea_fcl_' . $type, number_format((float)$rate, 2, '.', ''));
            }
        }

        set_alert('success', 'Freight rates updated successfully.');
        redirect('admin/courier/settings/main?group=customization');
    }


    public function add_service_point()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $name       = trim($this->input->post('name'));
        $country_id = (int)$this->input->post('country_id') ?: null;

        if ($name === '') {
            echo json_encode(['success' => false, 'message' => 'Service point name is required.']);
            return;
        }

        // Prevent duplicates within the same country (case-insensitive)
        $q = $this->db->where('LOWER(name)', strtolower($name));
        if ($country_id) {
            $q->where('country_id', $country_id);
        } else {
            $q->where('country_id IS NULL');
        }
        if ($q->count_all_results(db_prefix() . '_courier_service_points') > 0) {
            echo json_encode(['success' => false, 'message' => 'This service point already exists for the selected country.']);
            return;
        }

        $max = (int)$this->db->select_max('sort_order')->get(db_prefix() . '_courier_service_points')->row()->sort_order;
        $this->db->insert(db_prefix() . '_courier_service_points', [
            'name'       => $name,
            'country_id' => $country_id,
            'sort_order' => $max + 1,
        ]);
        $id = $this->db->insert_id();

        // Return country name so the view can display it
        $country_name = '';
        if ($country_id) {
            $row = $this->db->where('country_id', $country_id)->get('countries')->row();
            $country_name = $row ? $row->short_name : '';
        }

        echo json_encode(['success' => true, 'id' => $id, 'name' => $name, 'country_name' => $country_name]);
    }

    public function service_points_json()
    {
        $country_id = (int)$this->input->get('country_id');
        $q = $this->db->order_by('sort_order', 'ASC')->order_by('name', 'ASC');
        if ($country_id) {
            $q->where('country_id', $country_id);
        }
        $rows = $q->get(db_prefix() . '_courier_service_points')->result_array();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => array_column($rows, 'name')]);
    }

    public function delete_service_point($id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $this->db->where('id', (int)$id)->delete(db_prefix() . '_courier_service_points');
        echo json_encode(['success' => $this->db->affected_rows() > 0]);
    }

    public function upload_service_points_csv()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        if (empty($_FILES['csv_file']['tmp_name'])) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
            return;
        }
        $ext = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'txt'])) {
            echo json_encode(['success' => false, 'message' => 'Please upload a CSV file.']);
            return;
        }
        $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
        if (!$handle) {
            echo json_encode(['success' => false, 'message' => 'Could not open file.']);
            return;
        }
        $country_id = (int)$this->input->post('country_id') ?: null;
        $inserted = 0;
        $skipped  = 0;
        $max = (int)$this->db->select_max('sort_order')->get(db_prefix() . '_courier_service_points')->row()->sort_order;
        while (($row = fgetcsv($handle)) !== false) {
            $name = trim($row[0] ?? '');
            if ($name === '' || strtolower($name) === 'name') continue; // skip header
            $q = $this->db->where('LOWER(name)', strtolower($name));
            if ($country_id) {
                $q->where('country_id', $country_id);
            } else {
                $q->where('country_id IS NULL');
            }
            if ($q->count_all_results(db_prefix() . '_courier_service_points') > 0) {
                $skipped++;
                continue;
            }
            $max++;
            $this->db->insert(db_prefix() . '_courier_service_points', [
                'name'       => $name,
                'country_id' => $country_id,
                'sort_order' => $max,
            ]);
            $inserted++;
        }
        fclose($handle);
        echo json_encode(['success' => true, 'inserted' => $inserted, 'skipped' => $skipped]);
    }

    public function download_tariff_template()
    {
        $service = trim($this->input->get('service') ?: 'courier');
        $valid_services = ['domestic','road','lcl','consolidation','air_freight','air_consolidation','courier','fcl'];
        if (!in_array($service, $valid_services)) $service = 'courier';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="tariff_template_' . $service . '.csv"');
        $out = fopen('php://output', 'w');

        if ($service === 'fcl') {
            // FCL: container type → flat rate (updates existing options)
            fputcsv($out, ['container_type', 'rate']);
            fputcsv($out, ['# Full Container Load — fixed rate per container type']);
            $containers = [
                '20dv' => "20' Dry Van",
                '40dv' => "40' Dry Van",
                '20hc' => "20' High Cube",
                '40hc' => "40' High Cube",
                '20rf' => "20' Reefer",
                '40rf' => "40' Reefer",
                '20fr' => "20' Flat Rack",
                '40fr' => "40' Flat Rack",
                'roro' => 'RoRo',
            ];
            foreach ($containers as $key => $label) {
                $rate = get_option('courier_rate_sea_fcl_' . $key) ?: '0.00';
                fputcsv($out, [$key, $rate]);
            }
        } else {
            $service_labels = [
                'domestic'          => 'Domestic (Local Delivery)',
                'road'              => 'Road Freight',
                'lcl'               => 'Sea LCL (Less than Container)',
                'consolidation'     => 'Sea Consolidation',
                'air_freight'       => 'Air Freight',
                'air_consolidation' => 'Air Consolidation',
                'courier'           => 'Courier (Standard Parcel)',
            ];
            $dim_factors = [
                'domestic'          => 5000,
                'road'              => 5000,
                'courier'           => 5000,
                'lcl'               => 1000,
                'consolidation'     => 1000,
                'air_freight'       => 6000,
                'air_consolidation' => 6000,
            ];
            $dim = $dim_factors[$service] ?? 5000;
            $unit = in_array($service, ['lcl','consolidation']) ? 'cbm' : 'kg';

            fputcsv($out, ['# Service: ' . ($service_labels[$service] ?? $service)]);
            fputcsv($out, ['# Invoice = Chargeable Weight x Rate | Chargeable Weight = max(actual, volumetric)']);
            fputcsv($out, ['# Volumetric Weight = L(cm) x W(cm) x H(cm) / ' . $dim]);
            fputcsv($out, ['# rate_type: per_kg = rate x chargeable weight | flat = fixed charge']);
            fputcsv($out, ['# Zone rates in KES — set 0 to show Contact Us on portal']);
            fputcsv($out, ['service_type', "weight_min_{$unit}", "weight_max_{$unit}", 'rate_type',
                           'zone_a', 'zone_b', 'zone_c', 'zone_d', 'zone_e', 'zone_f']);
            $examples = [
                [$service, '0.001', '50',   'per_kg', '100', '120', '140', '160', '180', '0'],
                [$service, '51',    '150',  'per_kg', '90',  '110', '130', '150', '170', '0'],
                [$service, '151',   '300',  'per_kg', '80',  '100', '120', '140', '160', '0'],
                [$service, '301',   '500',  'per_kg', '70',  '90',  '110', '130', '150', '0'],
                [$service, '501',   '1000', 'per_kg', '60',  '80',  '100', '120', '140', '0'],
            ];
            foreach ($examples as $row) fputcsv($out, $row);
        }
        fclose($out);
    }

    public function upload_tariff_csv()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        if (empty($_FILES['tariff_csv']['tmp_name'])) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
            return;
        }
        $ext = strtolower(pathinfo($_FILES['tariff_csv']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'txt'])) {
            echo json_encode(['success' => false, 'message' => 'Please upload a CSV file.']);
            return;
        }
        $handle = fopen($_FILES['tariff_csv']['tmp_name'], 'r');
        if (!$handle) {
            echo json_encode(['success' => false, 'message' => 'Could not open file.']);
            return;
        }

        // Auto-migrate: add service_type column if it does not exist yet
        $rates_table = db_prefix() . '_courier_tariff_rates';
        if ($this->db->table_exists($rates_table) && !$this->db->field_exists('service_type', $rates_table)) {
            $this->db->query("ALTER TABLE `{$rates_table}` ADD COLUMN `service_type` VARCHAR(30) NULL DEFAULT NULL AFTER `id`");
        }

        $valid_service_types = ['domestic','road','lcl','consolidation','air_freight','air_consolidation','courier'];
        $inserted = 0;
        $updated  = 0;
        $errors   = 0;
        $header   = null;

        while (($row = fgetcsv($handle)) !== false) {
            // Skip comment rows (start with #)
            if (isset($row[0]) && strpos(trim($row[0]), '#') === 0) continue;

            if (!$header) {
                $header = array_map('strtolower', array_map('trim', $row));
                continue;
            }
            if (count($row) < count($header)) {
                $errors++;
                continue;
            }
            $data = array_combine($header, $row);

            // Detect if this is a service-type CSV or a legacy cargo_type CSV
            $service_type = isset($data['service_type']) ? trim($data['service_type']) : null;
            $is_freight   = $service_type !== null && in_array($service_type, $valid_service_types);

            // Determine weight_min / weight_max (support both _kg and _cbm column names)
            $weight_min_key = isset($data['weight_min_kg']) ? 'weight_min_kg'
                : (isset($data['weight_min_cbm']) ? 'weight_min_cbm' : 'weight_min_kg');
            $weight_max_key = isset($data['weight_max_kg']) ? 'weight_max_kg'
                : (isset($data['weight_max_cbm']) ? 'weight_max_cbm' : 'weight_max_kg');

            $weight_min = (float)($data[$weight_min_key] ?? 0);
            $weight_max = (float)($data[$weight_max_key] ?? 0);
            $rate_type  = trim($data['rate_type'] ?? 'per_kg');

            if ($is_freight) {
                // Freight-mode row: cargo_type stored as service type slug, no cargo_type filter
                if ($weight_min <= 0 || $weight_max <= $weight_min) { $errors++; continue; }
                $row_data = [
                    'service_type' => $service_type,
                    'cargo_type'   => 'parcel', // sentinel so NOT NULL constraint satisfied
                    'weight_min'   => $weight_min,
                    'weight_max'   => $weight_max,
                    'rate_type'    => in_array($rate_type, ['flat','per_kg']) ? $rate_type : 'per_kg',
                    'zone_a'       => (float)($data['zone_a'] ?? 0),
                    'zone_b'       => (float)($data['zone_b'] ?? 0),
                    'zone_c'       => (float)($data['zone_c'] ?? 0),
                    'zone_d'       => (float)($data['zone_d'] ?? 0),
                    'zone_e'       => (float)($data['zone_e'] ?? 0),
                    'zone_f'       => (float)($data['zone_f'] ?? 0),
                ];
                $existing = $this->db
                    ->where('service_type', $service_type)
                    ->where('weight_min', $weight_min)
                    ->where('weight_max', $weight_max)
                    ->get($rates_table)->row_array();
            } else {
                // Legacy CSV: cargo_type (document/parcel), no service_type
                $cargo_type = trim($data['cargo_type'] ?? '');
                if (!in_array($cargo_type, ['document','parcel']) || $weight_min <= 0 || $weight_max <= $weight_min) {
                    $errors++;
                    continue;
                }
                $row_data = [
                    'service_type' => null,
                    'cargo_type'   => $cargo_type,
                    'weight_min'   => $weight_min,
                    'weight_max'   => $weight_max,
                    'rate_type'    => in_array($rate_type, ['flat','per_kg']) ? $rate_type : 'per_kg',
                    'zone_a'       => (float)($data['zone_a'] ?? 0),
                    'zone_b'       => (float)($data['zone_b'] ?? 0),
                    'zone_c'       => (float)($data['zone_c'] ?? 0),
                    'zone_d'       => (float)($data['zone_d'] ?? 0),
                    'zone_e'       => (float)($data['zone_e'] ?? 0),
                    'zone_f'       => (float)($data['zone_f'] ?? 0),
                ];
                $existing = $this->db
                    ->where('cargo_type', $cargo_type)
                    ->where('weight_min', $weight_min)
                    ->where('weight_max', $weight_max)
                    ->where('service_type IS NULL')
                    ->get($rates_table)->row_array();
            }

            if ($existing) {
                $this->db->where('id', $existing['id'])->update($rates_table, $row_data);
                $updated++;
            } else {
                $this->db->insert($rates_table, $row_data);
                $inserted++;
            }
        }
        fclose($handle);
        echo json_encode(['success' => true, 'inserted' => $inserted, 'updated' => $updated, 'errors' => $errors]);
    }

    public function upload_fcl_csv()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        if (empty($_FILES['fcl_csv']['tmp_name'])) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
            return;
        }
        $ext = strtolower(pathinfo($_FILES['fcl_csv']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'txt'])) {
            echo json_encode(['success' => false, 'message' => 'Please upload a CSV file.']);
            return;
        }
        $handle = fopen($_FILES['fcl_csv']['tmp_name'], 'r');
        if (!$handle) {
            echo json_encode(['success' => false, 'message' => 'Could not open file.']);
            return;
        }
        $valid_containers = ['20dv','40dv','20hc','40hc','20rf','40rf','20fr','40fr','roro'];
        $updated = 0; $errors = 0; $header = null;
        while (($row = fgetcsv($handle)) !== false) {
            if (isset($row[0]) && strpos(trim($row[0]), '#') === 0) continue;
            if (!$header) {
                $header = array_map('strtolower', array_map('trim', $row));
                continue;
            }
            $container = strtolower(trim($row[0] ?? ''));
            $rate      = (float)($row[1] ?? 0);
            if (!in_array($container, $valid_containers) || $rate < 0) { $errors++; continue; }
            update_option('courier_rate_sea_fcl_' . $container, number_format($rate, 2, '.', ''));
            $updated++;
        }
        fclose($handle);
        echo json_encode(['success' => true, 'updated' => $updated, 'errors' => $errors]);
    }

    public function delete_tariff_rate($id)
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        $this->db->where('id', (int)$id)->delete(db_prefix() . '_courier_tariff_rates');
        echo json_encode(['success' => $this->db->affected_rows() > 0]);
    }

    public function update_tariff_zone()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        $id           = (int)$this->input->post('id');
        $destinations = $this->input->post('destinations');
        $is_available = (int)$this->input->post('is_available');
        $this->db->where('id', $id)->update(db_prefix() . '_courier_tariff_zones', [
            'destinations' => trim($destinations),
            'is_available' => $is_available,
        ]);
        echo json_encode(['success' => true]);
    }

    public function download_origin_tariff_template()
    {
        $origin = trim($this->input->get('origin') ?: '');
        $safe_name = preg_replace('/[^a-zA-Z0-9_\- ]/', '', $origin) ?: 'origin';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="origin_tariff_template_' . str_replace(' ', '_', strtolower($safe_name)) . '.csv"');
        $out = fopen('php://output', 'w');

        $services = ['consolidation','air_freight','air_consolidation','road','lcl','courier'];
        fputcsv($out, ['# Origin-Based International Tariff Template']);
        fputcsv($out, ['# Origin Country: ' . ($origin ?: 'YOUR_ORIGIN_COUNTRY')]);
        fputcsv($out, ['# Columns: destination_country, service_type, weight_min_kg, weight_max_kg, rate_type (flat/per_kg), rate']);
        fputcsv($out, ['# rate_type per_kg: charge = rate x chargeable_weight | flat: fixed charge regardless of weight']);
        fputcsv($out, ['destination_country', 'service_type', 'weight_min_kg', 'weight_max_kg', 'rate_type', 'rate']);
        $examples = [
            ['Uganda',       'air_freight',       '0.001', '100',  'per_kg', '12.50'],
            ['Uganda',       'air_freight',       '101',   '500',  'per_kg', '10.00'],
            ['Tanzania',     'air_freight',       '0.001', '100',  'per_kg', '11.00'],
            ['UAE',          'air_consolidation', '0.001', '200',  'per_kg', '8.50'],
            ['China',        'consolidation',     '0.001', '1000', 'per_kg', '5.00'],
            ['United States','air_freight',       '0.001', '50',   'flat',   '2500.00'],
        ];
        foreach ($examples as $row) fputcsv($out, $row);
        fclose($out);
    }

    public function upload_origin_tariff_csv()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        if (empty($_FILES['origin_tariff_csv']['tmp_name'])) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
            return;
        }
        $origin_country = trim($this->input->post('origin_country'));
        if ($origin_country === '') {
            echo json_encode(['success' => false, 'message' => 'Please select an origin country.']);
            return;
        }
        $ext = strtolower(pathinfo($_FILES['origin_tariff_csv']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'txt'])) {
            echo json_encode(['success' => false, 'message' => 'Please upload a CSV file.']);
            return;
        }
        $handle = fopen($_FILES['origin_tariff_csv']['tmp_name'], 'r');
        if (!$handle) {
            echo json_encode(['success' => false, 'message' => 'Could not open file.']);
            return;
        }

        $origin_tbl = db_prefix() . '_courier_origin_tariffs';
        if (!$this->db->table_exists($origin_tbl)) {
            echo json_encode(['success' => false, 'message' => 'Origin tariff table not found. Please reload the admin panel.']);
            return;
        }

        $valid_service_types = ['domestic','road','lcl','consolidation','air_freight','air_consolidation','courier'];
        $inserted = 0; $updated = 0; $errors = 0; $header = null;

        while (($row = fgetcsv($handle)) !== false) {
            if (isset($row[0]) && strpos(trim($row[0]), '#') === 0) continue;
            if (!$header) {
                $header = array_map('strtolower', array_map('trim', $row));
                continue;
            }
            if (count($row) < count($header)) { $errors++; continue; }
            $data = array_combine($header, $row);

            $dest    = trim($data['destination_country'] ?? '');
            $svc     = trim($data['service_type'] ?? '');
            $wmin    = (float)($data['weight_min_kg'] ?? $data['weight_min'] ?? 0);
            $wmax    = (float)($data['weight_max_kg'] ?? $data['weight_max'] ?? 0);
            $rtype   = trim($data['rate_type'] ?? 'per_kg');
            $rate    = (float)($data['rate'] ?? 0);

            if ($dest === '' || !in_array($svc, $valid_service_types) || $wmin < 0 || $wmax <= $wmin || $rate < 0) {
                $errors++;
                continue;
            }

            $row_data = [
                'origin_country'      => $origin_country,
                'destination_country' => $dest,
                'service_type'        => $svc,
                'weight_min'          => $wmin,
                'weight_max'          => $wmax,
                'rate_type'           => in_array($rtype, ['flat','per_kg']) ? $rtype : 'per_kg',
                'rate'                => $rate,
            ];

            $existing = $this->db
                ->where('origin_country', $origin_country)
                ->where('destination_country', $dest)
                ->where('service_type', $svc)
                ->where('weight_min', $wmin)
                ->where('weight_max', $wmax)
                ->get($origin_tbl)->row_array();

            if ($existing) {
                $this->db->where('id', $existing['id'])->update($origin_tbl, $row_data);
                $updated++;
            } else {
                $this->db->insert($origin_tbl, $row_data);
                $inserted++;
            }
        }
        fclose($handle);
        echo json_encode(['success' => true, 'inserted' => $inserted, 'updated' => $updated, 'errors' => $errors, 'origin' => $origin_country]);
    }

    public function download_matrix_template()
    {
        $origin = trim($this->input->get('origin') ?: '');
        $service = trim($this->input->get('service') ?: '');
        $safe_name = preg_replace('/[^a-zA-Z0-9_\- ]/', '', $origin . '_' . $service) ?: 'matrix_template';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="tariff_matrix_' . str_replace(' ', '_', strtolower($safe_name)) . '.csv"');
        $out = fopen('php://output', 'w');

        fputcsv($out, ['# Origin-Based 2D Matrix Tariff Template']);
        fputcsv($out, ['# Origin Country: ' . ($origin ?: 'YOUR_ORIGIN_COUNTRY')]);
        fputcsv($out, ['# Service Type: ' . ($service ?: 'courier')]);
        fputcsv($out, ['# Instructions: Fill in your rates under the respective Destination Countries across the columns, and Weights/Container Types down the rows.']);
        
        $countries = $this->db->order_by('short_name', 'ASC')->get(db_prefix() . 'countries')->result_array();
        $destinations = [];
        foreach ($countries as $c) {
            $destinations[] = $c['short_name'];
        }
        
        // Header row
        $header = ['Weight_or_Container'];
        foreach ($destinations as $dest) $header[] = $dest;
        fputcsv($out, $header);

        // Example rows based on service
        if ($service === 'fcl') {
            $rows = ['20dv', '40dv', '20hc', '40hc', '20rf', '40rf', '20fr', '40fr', 'roro'];
            foreach ($rows as $r) {
                fputcsv($out, array_merge([$r], array_fill(0, count($destinations), '')));
            }
        } else {
            // Generate standard weight bands (0.5 to 70.0)
            $rows = [];
            for ($i = 0.5; $i <= 30.0; $i += 0.5) $rows[] = number_format($i, 1, '.', '');
            for ($i = 31.0; $i <= 70.0; $i += 1.0) $rows[] = number_format($i, 1, '.', '');
            
            foreach ($rows as $r) {
                fputcsv($out, array_merge([$r], array_fill(0, count($destinations), '')));
            }
        }
        fclose($out);
    }

    public function upload_matrix_csv()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        if (empty($_FILES['matrix_csv']['tmp_name'])) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
            return;
        }
        $origin_country = trim($this->input->post('origin_country'));
        $service_type = trim($this->input->post('service_type'));
        
        if ($origin_country === '' || $service_type === '') {
            echo json_encode(['success' => false, 'message' => 'Please select origin country and service type.']);
            return;
        }
        $ext = strtolower(pathinfo($_FILES['matrix_csv']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'txt'])) {
            echo json_encode(['success' => false, 'message' => 'Please upload a CSV file.']);
            return;
        }
        $handle = fopen($_FILES['matrix_csv']['tmp_name'], 'r');
        if (!$handle) {
            echo json_encode(['success' => false, 'message' => 'Could not open file.']);
            return;
        }

        $origin_tbl = db_prefix() . '_courier_origin_tariffs';
        if (!$this->db->table_exists($origin_tbl)) {
            echo json_encode(['success' => false, 'message' => 'Origin tariff table not found.']);
            return;
        }

        $inserted = 0; $updated = 0; $errors = 0; $destinations = [];
        $is_fcl = ($service_type === 'fcl');
        
        // We assume rate_type is 'flat' for FCL, 'per_kg' for others, unless handled otherwise in code.
        $rate_type = $is_fcl ? 'flat' : 'flat'; // Wait, in Courier, matrices are usually flat rates for that specific weight band!
        // Actually, matrices are typically flat for the weight band (e.g. up to 0.5kg = $20, up to 1.0kg = $30).
        
        while (($row = fgetcsv($handle)) !== false) {
            if (isset($row[0]) && strpos(trim($row[0]), '#') === 0) continue;
            
            // First non-comment row is the header (Destinations)
            if (empty($destinations)) {
                // Column 0 is 'Weight_or_Container'
                for ($i = 1; $i < count($row); $i++) {
                    $destinations[$i] = trim($row[$i]);
                }
                continue;
            }
            
            // Data rows
            if (empty($row[0])) continue;
            
            $weight_or_container = trim($row[0]);
            
            // Parse weight min and max from the row label.
            // Matrix usually means: row '1.0' means up to 1.0kg. 
            // Previous row was 0.5kg. So it's 0.501 to 1.0kg.
            // We'll store weight_max = row label. weight_min = 0 (or we can calculate it, but storing max is usually enough if we query by MIN(weight_max)).
            $wmax = (float)$weight_or_container;
            $wmin = 0; // Simplified
            
            for ($i = 1; $i < count($row); $i++) {
                if (!isset($destinations[$i])) continue;
                $dest = $destinations[$i];
                $rate = (float)trim($row[$i]);
                if ($dest === '' || $rate < 0) continue;
                
                $row_data = [
                    'origin_country'      => $origin_country,
                    'destination_country' => $dest,
                    'service_type'        => $service_type,
                    'weight_min'          => $wmin,
                    'weight_max'          => $wmax,
                    'rate_type'           => 'flat', // Matrix cells are typically flat price for that weight bracket
                    'rate'                => $rate
                ];

                // For FCL, rate_type is flat, weight is 0. 
                // We'll store container type in service_type or cargo_type? 
                // Currently origin_tariffs has service_type. If FCL, maybe we just use $weight_or_container as a custom weight mapping, 
                // but actually Origin-Based tariff table only has weight_min and weight_max. FCL isn't natively supported in origin_tariffs currently!
                // Wait, if it's FCL, we can just store the container name in 'rate_type' temporarily or it's not supported. 
                // Let's just stick to courier/air for weight bands since they excluded Domestic.

                $existing = $this->db->where([
                    'origin_country'      => $origin_country,
                    'destination_country' => $dest,
                    'service_type'        => $service_type,
                    'weight_max'          => $wmax
                ])->get($origin_tbl)->row();

                if ($existing) {
                    $this->db->where('id', $existing->id)->update($origin_tbl, $row_data);
                    $updated++;
                } else {
                    $this->db->insert($origin_tbl, $row_data);
                    $inserted++;
                }
            }
        }
        fclose($handle);
        echo json_encode(['success' => true, 'inserted' => $inserted, 'updated' => $updated, 'errors' => $errors]);
    }

    public function view_origin_rates()
    {
        $origin = trim($this->input->get('origin') ?: '');
        if (!$origin) {
            show_404();
        }

        $origin_tbl = db_prefix() . '_courier_origin_tariffs';
        
        // Fetch all distinct service types for this origin
        $services = $this->db->select('service_type')
                             ->where('origin_country', $origin)
                             ->group_by('service_type')
                             ->get($origin_tbl)
                             ->result_array();
                             
        $data['origin'] = $origin;
        $data['matrices'] = [];

        foreach ($services as $s) {
            $service = $s['service_type'];
            
            // Get all records for this service
            $records = $this->db->where('origin_country', $origin)
                                ->where('service_type', $service)
                                ->order_by('weight_max', 'ASC')
                                ->order_by('destination_country', 'ASC')
                                ->get($origin_tbl)
                                ->result_array();
                                
            $destinations = [];
            $weights = [];
            $matrix_data = [];
            
            foreach ($records as $r) {
                $dest = $r['destination_country'];
                $w = $r['weight_max'];
                $rate = $r['rate'];
                
                if (!in_array($dest, $destinations)) {
                    $destinations[] = $dest;
                }
                if (!in_array($w, $weights)) {
                    $weights[] = $w;
                }
                
                $matrix_data[$w][$dest] = $rate;
            }
            
            sort($destinations); // Alphabetical columns
            // Weights are already sorted by ASC from DB query if we just use them, but let's re-sort to be safe.
            usort($weights, function($a, $b) { return (float)$a <=> (float)$b; });
            
            $data['matrices'][] = [
                'service' => $service,
                'destinations' => $destinations,
                'weights' => $weights,
                'data' => $matrix_data
            ];
        }

        $data['title'] = 'View Rates: ' . $origin;
        $this->load->view('settings/view_origin_rates', $data);
    }

    public function delete_origin_tariff($origin_country)
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        $origin_country = urldecode($origin_country);
        $origin_tbl = db_prefix() . '_courier_origin_tariffs';
        $this->db->where('origin_country', $origin_country)->delete($origin_tbl);
        echo json_encode(['success' => $this->db->affected_rows() >= 0]);
    }

    public function bulk_delete_origin_tariffs()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        $origins = $this->input->post('origins');
        if (!empty($origins) && is_array($origins)) {
            $origin_tbl = db_prefix() . '_courier_origin_tariffs';
            $this->db->where_in('origin_country', $origins)->delete($origin_tbl);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    public function delete_origin_tariff_rate($id)
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        $origin_tbl = db_prefix() . '_courier_origin_tariffs';
        $this->db->where('id', (int)$id)->delete($origin_tbl);
        echo json_encode(['success' => $this->db->affected_rows() > 0]);
    }

    public function origin_tariff_rates_json()
    {
        header('Content-Type: application/json');
        $origin = trim($this->input->get('origin') ?: '');
        if ($origin === '') { echo json_encode(['success' => false, 'data' => []]); return; }
        $origin_tbl = db_prefix() . '_courier_origin_tariffs';
        if (!$this->db->table_exists($origin_tbl)) { echo json_encode(['success' => true, 'data' => []]); return; }
        $rows = $this->db->where('origin_country', $origin)
            ->order_by('destination_country', 'ASC')
            ->order_by('service_type', 'ASC')
            ->order_by('weight_min', 'ASC')
            ->get($origin_tbl)->result_array();
        echo json_encode(['success' => true, 'data' => $rows]);
    }

    public function download_domestic_tariff_template()
    {
        $origin = trim($this->input->get('origin') ?: '');
        $safe_name = $origin ?: 'origin';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="domestic_tariff_template_' . str_replace(' ', '_', strtolower($safe_name)) . '.csv"');
        $out = fopen('php://output', 'w');

        fputcsv($out, ['# Domestic City-to-City Tariff Template']);
        fputcsv($out, ['# Origin City: ' . ($origin ?: 'YOUR_ORIGIN_CITY')]);
        fputcsv($out, ['# Columns: destination_city, weight_min_kg, weight_max_kg, rate_type (flat/per_kg), rate']);
        fputcsv($out, ['# rate_type flat: fixed charge per route regardless of weight | per_kg: charge = rate x chargeable_weight']);
        fputcsv($out, ['destination_city', 'weight_min_kg', 'weight_max_kg', 'rate_type', 'rate']);
        $examples = [
            ['Nakuru',  '0', '999999', 'flat', '1500.00'],
            ['Mombasa', '0', '999999', 'flat', '2000.00'],
        ];
        foreach ($examples as $row) fputcsv($out, $row);
        fclose($out);
    }

    public function upload_domestic_tariff_csv()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        if (empty($_FILES['domestic_tariff_csv']['tmp_name'])) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
            return;
        }
        $origin_city = trim($this->input->post('origin_city'));
        if ($origin_city === '') {
            echo json_encode(['success' => false, 'message' => 'Please select an origin city.']);
            return;
        }
        $ext = strtolower(pathinfo($_FILES['domestic_tariff_csv']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'txt'])) {
            echo json_encode(['success' => false, 'message' => 'Please upload a CSV file.']);
            return;
        }
        $handle = fopen($_FILES['domestic_tariff_csv']['tmp_name'], 'r');
        if (!$handle) {
            echo json_encode(['success' => false, 'message' => 'Could not open file.']);
            return;
        }

        $domestic_tbl = db_prefix() . '_courier_domestic_tariffs';
        if (!$this->db->table_exists($domestic_tbl)) {
            echo json_encode(['success' => false, 'message' => 'Domestic tariff table not found. Please reload the admin panel.']);
            return;
        }

        $inserted = 0; $updated = 0; $errors = 0; $header = null;

        while (($row = fgetcsv($handle)) !== false) {
            if (isset($row[0]) && strpos(trim($row[0]), '#') === 0) continue;
            if (!$header) {
                $header = array_map('strtolower', array_map('trim', $row));
                continue;
            }
            if (count($row) < count($header)) { $errors++; continue; }
            $data = array_combine($header, $row);

            $dest  = trim($data['destination_city'] ?? '');
            $wmin  = (float)($data['weight_min_kg'] ?? $data['weight_min'] ?? 0);
            $wmax  = (float)($data['weight_max_kg'] ?? $data['weight_max'] ?? 999999);
            $rtype = trim($data['rate_type'] ?? 'flat');
            $rate  = (float)($data['rate'] ?? 0);

            if ($dest === '' || $wmin < 0 || $wmax <= $wmin || $rate < 0) {
                $errors++;
                continue;
            }

            $row_data = [
                'origin_city'      => $origin_city,
                'destination_city' => $dest,
                'weight_min'       => $wmin,
                'weight_max'       => $wmax,
                'rate_type'        => in_array($rtype, ['flat','per_kg']) ? $rtype : 'flat',
                'rate'             => $rate,
            ];

            $existing = $this->db
                ->where('origin_city', $origin_city)
                ->where('destination_city', $dest)
                ->where('weight_min', $wmin)
                ->where('weight_max', $wmax)
                ->get($domestic_tbl)->row_array();

            if ($existing) {
                $this->db->where('id', $existing['id'])->update($domestic_tbl, $row_data);
                $updated++;
            } else {
                $this->db->insert($domestic_tbl, $row_data);
                $inserted++;
            }
        }
        fclose($handle);
        echo json_encode(['success' => true, 'inserted' => $inserted, 'updated' => $updated, 'errors' => $errors, 'origin' => $origin_city]);
    }

    public function delete_domestic_tariff($origin_city)
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        $origin_city = urldecode($origin_city);
        $domestic_tbl = db_prefix() . '_courier_domestic_tariffs';
        $this->db->where('origin_city', $origin_city)->delete($domestic_tbl);
        echo json_encode(['success' => $this->db->affected_rows() >= 0]);
    }

    public function delete_domestic_tariff_rate($id)
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        $domestic_tbl = db_prefix() . '_courier_domestic_tariffs';
        $this->db->where('id', (int)$id)->delete($domestic_tbl);
        echo json_encode(['success' => $this->db->affected_rows() > 0]);
    }

    public function domestic_tariff_rates_json()
    {
        header('Content-Type: application/json');
        $origin = trim($this->input->get('origin') ?: '');
        if ($origin === '') { echo json_encode(['success' => false, 'data' => []]); return; }
        $domestic_tbl = db_prefix() . '_courier_domestic_tariffs';
        if (!$this->db->table_exists($domestic_tbl)) { echo json_encode(['success' => true, 'data' => []]); return; }
        $rows = $this->db->where('origin_city', $origin)
            ->order_by('destination_city', 'ASC')
            ->order_by('weight_min', 'ASC')
            ->get($domestic_tbl)->result_array();
        echo json_encode(['success' => true, 'data' => $rows]);
    }

    public function dimensional_factor()
    {



        $this->form_validation->set_rules('default', 'Domestic/Courier', 'required');
        $this->form_validation->set_rules('air_consolidation', 'Air Consolidation', 'required');
        $this->form_validation->set_rules('air_freight', 'Air Freight', 'required');
        $this->form_validation->set_rules('sea_lcl', 'Sea Consolidation', 'required');


        if ($this->form_validation->run() === FALSE) {

            $data['dimensional_factor'] = $this->DimensionalFactor_model->get();
            $this->load->view('settings/main',$data);

        } else {

            if ($this->input->post('default')) {
                $this->DimensionalFactor_model->update_by_name('default', [
                    'value' => $this->input->post('default')
                ]);
            }

            if ($this->input->post('air_consolidation')) {
                $this->DimensionalFactor_model->update_by_name('air_consolidation', [
                    'value' => $this->input->post('air_consolidation')
                ]);
            }

            if ($this->input->post('air_freight')) {
                $this->DimensionalFactor_model->update_by_name('air_freight', [
                    'value' => $this->input->post('air_freight')
                ]);
            }

            if ($this->input->post('sea_consolidation')) {
                $this->DimensionalFactor_model->update_by_name('sea_consolidation', [
                    'value' => $this->input->post('sea_consolidation')
                ]);
            }

            set_alert('success', 'Dimensional Factors updated successfully.');
            redirect('admin/courier/settings/main');

        }
    }

}