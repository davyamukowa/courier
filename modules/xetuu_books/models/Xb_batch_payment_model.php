<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Xb_batch_payment_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('xetuu_books/Xb_payment_model', 'xb_payment');
        $this->_check_schema();
    }

    private function _check_schema()
    {
        $prefix = db_prefix();
        if (!$this->db->table_exists($prefix . 'acc_batch_payments')) {
            $this->db->query("
                CREATE TABLE `" . $prefix . "acc_batch_payments` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `name` VARCHAR(50) NOT NULL,
                    `date` DATE NOT NULL,
                    `journal_id` INT(11) NOT NULL,
                    `payment_type` VARCHAR(20) NOT NULL,
                    `amount` DECIMAL(15,4) DEFAULT '0.0000',
                    `state` VARCHAR(20) DEFAULT 'draft',
                    `created_by` INT(11) NOT NULL,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->db->field_exists('batch_id', $prefix . 'acc_payments')) {
            $this->db->query("ALTER TABLE `" . $prefix . "acc_payments` ADD COLUMN `batch_id` INT(11) NULL DEFAULT NULL AFTER `move_id`;");
        }
    }

    public function get_list($filters = [])
    {
        $this->db->select('acc_batch_payments.*, acc_journals.name as journal_name');
        $this->db->from('acc_batch_payments');
        $this->db->join('acc_journals', 'acc_journals.id = acc_batch_payments.journal_id', 'left');

        if (!empty($filters['payment_type'])) {
            $this->db->where('acc_batch_payments.payment_type', $filters['payment_type']);
        }
        
        $this->db->order_by('acc_batch_payments.date', 'DESC');
        $this->db->order_by('acc_batch_payments.id', 'DESC');
        return $this->db->get()->result();
    }

    public function get($id)
    {
        $this->db->select('acc_batch_payments.*, acc_journals.name as journal_name');
        $this->db->from('acc_batch_payments');
        $this->db->join('acc_journals', 'acc_journals.id = acc_batch_payments.journal_id', 'left');
        $this->db->where('acc_batch_payments.id', $id);
        $batch = $this->db->get()->row();

        if ($batch) {
            $this->db->select('p.*, IF(p.partner_type="vendor", COALESCE(pv.company,""), COALESCE(c.company,"")) as partner_name, am.name as invoice_number');
            $this->db->from('acc_payments p');
            $this->db->join('tblclients c',    'c.userid  = p.partner_id', 'left');
            $this->db->join('tblpur_vendor pv', 'pv.userid = p.partner_id', 'left');
            $this->db->join('acc_moves am', 'am.id = p.move_id', 'left');
            $this->db->where('p.batch_id', $id);
            $batch->payments = $this->db->get()->result();
        }

        return $batch;
    }

    public function create_batch($data, $invoice_ids)
    {
        if (empty($invoice_ids)) {
            throw new Exception("You must select at least one invoice/bill to create a batch payment.");
        }

        $date = $data['date'] ?? date('Y-m-d');
        $journal_id = (int)$data['journal_id'];
        $payment_type = $data['payment_type']; // 'inbound' or 'outbound'
        $partner_type = ($payment_type === 'inbound') ? 'customer' : 'vendor';

        // Get total amount
        $total_amount = 0;
        $this->db->where_in('id', $invoice_ids);
        $invoices = $this->db->get('acc_moves')->result();
        foreach ($invoices as $inv) {
            $total_amount += (float)$inv->amount_residual;
        }

        if ($total_amount <= 0) {
            throw new Exception("The selected invoices have zero residual amount.");
        }

        // Generate batch sequence
        $prefix = ($payment_type === 'inbound') ? 'BATCH/IN/' : 'BATCH/OUT/';
        $prefix .= date('Y', strtotime($date)) . '/';
        $this->db->like('name', $prefix, 'after')->order_by('id', 'DESC')->limit(1);
        $last = $this->db->get('acc_batch_payments')->row();
        if ($last && strpos($last->name, $prefix) === 0) {
            $n = (int)str_replace($prefix, '', $last->name);
            $batch_name = $prefix . str_pad($n + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $batch_name = $prefix . '0001';
        }

        $this->db->insert('acc_batch_payments', [
            'name' => $batch_name,
            'date' => $date,
            'journal_id' => $journal_id,
            'payment_type' => $payment_type,
            'amount' => $total_amount,
            'state' => 'posted',
            'created_by' => get_staff_user_id()
        ]);
        
        $batch_id = $this->db->insert_id();

        // Register individual payments
        foreach ($invoices as $inv) {
            if ((float)$inv->amount_residual <= 0) continue;

            $pay_data = [
                'partner_type' => $partner_type,
                'partner_id' => $inv->partner_id,
                'amount' => $inv->amount_residual,
                'journal_id' => $journal_id,
                'date' => $date,
                'memo' => 'Batch: ' . $batch_name,
                'move_id' => $inv->id
            ];

            $payment_id = $this->xb_payment->register_payment($pay_data);
            
            // Link to batch
            if ($payment_id) {
                $this->db->where('id', $payment_id)->update('acc_payments', ['batch_id' => $batch_id]);
            }
        }

        return $batch_id;
    }
}
