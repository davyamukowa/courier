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
        ];

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
