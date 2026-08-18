<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Inventory_log extends AdminController
{
    private $table;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('courier_logistic/courier');

        if (!has_permission('courier-shipments', '', 'view_all_shipments') && !is_admin()) {
            access_denied('Inventory Log Book');
        }

        $this->table = db_prefix() . '_courier_inventory_log_books';

        // Self-heal: this module's tables never re-run install.php on this
        // server after the first activation (deploys are plain file copies —
        // see CLAUDE.md's migration gotcha), so create it here too.
        if (!$this->db->table_exists($this->table)) {
            $this->db->query('CREATE TABLE IF NOT EXISTS `' . $this->table . '` (
                `id`            INT NOT NULL AUTO_INCREMENT,
                `company_name`  VARCHAR(255) NOT NULL DEFAULT "",
                `log_date`      DATE NULL,
                `log_time`      TIME NULL,
                `am_pm`         VARCHAR(2) NOT NULL DEFAULT "",
                `counted_by`    VARCHAR(255) NOT NULL DEFAULT "",
                `sheet_number`  VARCHAR(100) NOT NULL DEFAULT "",
                `items_json`    LONGTEXT NULL,
                `staff_id`      INT NULL DEFAULT NULL,
                `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`    DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
        }
        if (!$this->db->field_exists('issued_by', $this->table)) {
            $this->db->query('ALTER TABLE `' . $this->table . '` ADD COLUMN `issued_by` VARCHAR(255) NOT NULL DEFAULT ""');
        }
        if (!$this->db->field_exists('received_by', $this->table)) {
            $this->db->query('ALTER TABLE `' . $this->table . '` ADD COLUMN `received_by` VARCHAR(255) NOT NULL DEFAULT ""');
        }
        if (!$this->db->field_exists('issued_by_signature', $this->table)) {
            $this->db->query('ALTER TABLE `' . $this->table . '` ADD COLUMN `issued_by_signature` VARCHAR(255) NOT NULL DEFAULT ""');
        }
        if (!$this->db->field_exists('received_by_signature', $this->table)) {
            $this->db->query('ALTER TABLE `' . $this->table . '` ADD COLUMN `received_by_signature` VARCHAR(255) NOT NULL DEFAULT ""');
        }
    }

    public function index()
    {
        $data['title'] = 'Inventory Log Book';
        $data['logs'] = $this->db->order_by('id', 'DESC')->get($this->table)->result_array();
        $this->load->view('inventory_log/index', $data);
    }

    public function create()
    {
        $data['title'] = 'New Inventory Log Book';
        $data['log'] = null;
        $data['items'] = [];
        $this->load->view('inventory_log/form', $data);
    }

    public function edit($id)
    {
        $log = $this->db->where('id', (int) $id)->get($this->table)->row_array();
        if (!$log) {
            show_404();
        }
        $data['title'] = 'Edit Inventory Log Book';
        $data['log'] = $log;
        $data['items'] = json_decode($log['items_json'] ?: '[]', true) ?: [];
        $this->load->view('inventory_log/form', $data);
    }

    public function store()
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            show_404();
        }

        $id = (int) $this->input->post('id');

        $item_sku    = (array) $this->input->post('item_sku');
        $description = (array) $this->input->post('description');
        $qty         = (array) $this->input->post('qty');
        $location    = (array) $this->input->post('location');
        $notes       = (array) $this->input->post('notes');

        $items = [];
        $rows = max(count($item_sku), count($description), count($qty), count($location), count($notes));
        for ($i = 0; $i < $rows; $i++) {
            $row = [
                'item_sku'    => trim($item_sku[$i]    ?? ''),
                'description' => trim($description[$i] ?? ''),
                'qty'         => trim($qty[$i]         ?? ''),
                'location'    => trim($location[$i]    ?? ''),
                'notes'       => trim($notes[$i]       ?? ''),
            ];
            // Skip fully blank rows
            if ($row['item_sku'] === '' && $row['description'] === '' && $row['qty'] === '' && $row['location'] === '' && $row['notes'] === '') {
                continue;
            }
            $items[] = $row;
        }

        $log_date = trim($this->input->post('log_date') ?? '');
        $log_time = trim($this->input->post('log_time') ?? '');

        $data = [
            'company_name' => trim($this->input->post('company_name') ?? ''),
            'log_date'     => $log_date !== '' ? date('Y-m-d', strtotime($log_date)) : null,
            'log_time'     => $log_time !== '' ? date('H:i:s', strtotime($log_time)) : null,
            'am_pm'        => in_array($this->input->post('am_pm'), ['AM', 'PM'], true) ? $this->input->post('am_pm') : '',
            'counted_by'   => trim($this->input->post('counted_by') ?? ''),
            'sheet_number' => trim($this->input->post('sheet_number') ?? ''),
            'items_json'   => json_encode($items),
            'issued_by'    => trim($this->input->post('issued_by') ?? ''),
            'received_by'  => trim($this->input->post('received_by') ?? ''),
        ];

        // Signatures: canvases only populate their hidden data-URI field when
        // the staff member actually drew something (see form.php), so an
        // empty post here means "leave whatever was saved before untouched"
        // rather than wiping out a signature already on file.
        $issued_sig   = $this->_save_signature('issued_by_signature_data', 'issued');
        $received_sig = $this->_save_signature('received_by_signature_data', 'received');
        if ($issued_sig !== null) {
            $data['issued_by_signature'] = $issued_sig;
        }
        if ($received_sig !== null) {
            $data['received_by_signature'] = $received_sig;
        }

        if ($id) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('id', $id)->update($this->table, $data);
            set_alert('success', 'Inventory Log Book updated successfully.');
        } else {
            $data['staff_id']   = get_staff_user_id();
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert($this->table, $data);
            $id = $this->db->insert_id();
            set_alert('success', 'Inventory Log Book saved successfully.');
        }

        redirect(admin_url('courier_logistic/inventory_log/view/' . $id));
    }

    /**
     * Decodes a data:image/png;base64,... string from a signature_pad canvas
     * and saves it as a PNG. Returns the relative path to store in the DB,
     * or null if there was nothing to save (canvas left untouched).
     *
     * Reads straight from $_POST rather than $this->input->post() — Perfex
     * has `global_xss_filtering` on, which runs CI's xss_clean() over every
     * post() value, and xss_clean() rewrites long base64 blobs (it's a text
     * filter, not meant for binary-ish data), silently corrupting the PNG.
     * The decoded bytes are still validated against the PNG signature below
     * before anything is written to disk, so this doesn't skip validation —
     * it just avoids a filter that isn't appropriate for this field.
     */
    private function _save_signature($field_name, $prefix)
    {
        $canvas_data = $_POST[$field_name] ?? '';
        if (empty($canvas_data)) {
            return null;
        }
        $canvas_data = preg_replace('#^data:image/png;base64,#', '', $canvas_data);
        $image_data  = base64_decode($canvas_data, true);

        // Real PNGs always start with this 8-byte signature — reject anything
        // else rather than writing a corrupt file.
        if (!$image_data || substr($image_data, 0, 8) !== "\x89PNG\x0d\x0a\x1a\x0a") {
            return null;
        }

        $dir = FCPATH . 'modules/courier_logistic/assets/inventory_log_signatures/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file_name = $prefix . '_' . uniqid() . '.png';
        if (!file_put_contents($dir . $file_name, $image_data)) {
            return null;
        }

        return 'modules/courier_logistic/assets/inventory_log_signatures/' . $file_name;
    }

    public function view($id)
    {
        $log = $this->db->where('id', (int) $id)->get($this->table)->row_array();
        if (!$log) {
            show_404();
        }
        $data['title'] = 'Inventory Log Book';
        $data['log'] = $log;
        $data['items'] = json_decode($log['items_json'] ?: '[]', true) ?: [];
        $data['company'] = $this->_company_header_data();
        $this->load->view('inventory_log/view', $data);
    }

    /**
     * Company header (logo + phone/P.O. box/email) shown on the view and
     * print pages — same source the rest of this module's printed documents
     * (waybill, courier invoice) already pull from, so it stays in sync with
     * Settings → Invoice & Receipt Info / the round stamp settings.
     */
    private function _company_header_data()
    {
        $logo_file = get_option('company_logo_dark') ?: get_option('company_logo');
        $info      = courier_get_invoice_info();

        return [
            'logo_url' => !empty($logo_file) ? base_url('uploads/company/' . $logo_file) : '',
            'name'     => $info['name'],
            'phone'    => $info['phone'],
            'email'    => $info['email'],
            'pobox'    => get_option('courier_stamp_pobox'),
        ];
    }

    public function delete($id)
    {
        $this->db->where('id', (int) $id)->delete($this->table);
        set_alert('success', 'Inventory Log Book deleted.');
        redirect(admin_url('courier_logistic/inventory_log'));
    }

    /**
     * Printable page — blank template when no $id, or a filled sheet for a
     * saved log book when $id is given. Same layout either way; staff use
     * the browser's Print → Save as PDF for a PDF copy (matches how
     * waybills/invoices are "PDF'd" elsewhere in this module).
     */
    public function print_form($id = null)
    {
        $data['log'] = null;
        $data['items'] = [];
        if ($id) {
            $log = $this->db->where('id', (int) $id)->get($this->table)->row_array();
            if (!$log) {
                show_404();
            }
            $data['log'] = $log;
            $data['items'] = json_decode($log['items_json'] ?: '[]', true) ?: [];
        }
        $data['default_company_name'] = get_option('companyname');
        $data['company'] = $this->_company_header_data();
        $this->load->view('inventory_log/print', $data);
    }
}
