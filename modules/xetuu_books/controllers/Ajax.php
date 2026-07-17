<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ajax extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_customers()
    {
        $this->load->model('clients_model');
        $q = trim($this->input->get('q') ?? '');
        if ($q) {
            $esc = $this->db->escape_like_str($q);
            $this->db->where("(company LIKE '%{$esc}%' ESCAPE '!' OR email LIKE '%{$esc}%' ESCAPE '!')");
        }
        $this->db->select('userid as id, IFNULL(NULLIF(company,""), CONCAT(firstname," ",lastname)) as name, email');
        $this->db->join(db_prefix() . 'contacts', db_prefix() . 'contacts.userid = ' . db_prefix() . 'clients.userid AND ' . db_prefix() . 'contacts.is_primary = 1', 'left');
        $this->db->where(db_prefix() . 'clients.active', 1);
        $this->db->order_by('name', 'asc');
        $clients = $this->db->get(db_prefix() . 'clients')->result_array();
        echo json_encode($clients);
    }

    public function quick_create_customer()
    {
        $company = $this->input->post('company');
        if ($company) {
            $this->load->model('clients_model');
            $id = $this->clients_model->add(['company' => $company]);
            if ($id) {
                echo json_encode(['success' => true, 'id' => $id, 'name' => $company]);
                return;
            }
        }
        echo json_encode(['success' => false, 'error' => 'Failed to insert into database']);
    }

    public function get_currency_rate()
    {
        header('Content-Type: application/json');
        $cur_id = (int)$this->input->get('id');
        if ($cur_id) {
            $cur = $this->db->select('id,name,symbol,rate')->where('id', $cur_id)->get('acc_currencies')->row();
            echo json_encode($cur ?: ['error' => 'Not found']);
        } else {
            $rows = $this->db->select('id,name,symbol,rate')->get('acc_currencies')->result();
            echo json_encode($rows);
        }
    }

    /**
     * Save a currency exchange rate (POST).
     * Also syncs the currency to tblcurrencies if it doesn't exist there yet.
     */
    public function save_currency()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        header('Content-Type: application/json');

        $cur_id = (int)$this->input->post('id');
        $rate   = (float)$this->input->post('rate');
        if (!$cur_id || $rate <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid id or rate']); return;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->where('id', $cur_id)->update('acc_currencies', [
            'rate'       => $rate,
            'updated_at' => $now,
        ]);

        // Sync to Perfex native tblcurrencies: add currency if it doesn't exist there
        $ac = $this->db->where('id', $cur_id)->get('acc_currencies')->row();
        if ($ac) {
            $pf = $this->db->where('name', strtoupper($ac->name))->get(db_prefix() . 'currencies')->row();
            if (!$pf) {
                $this->db->insert(db_prefix() . 'currencies', [
                    'name'               => strtoupper($ac->name),
                    'symbol'             => $ac->symbol ?? $ac->name,
                    'decimal_separator'  => '.',
                    'thousand_separator' => ',',
                    'placement'          => $ac->position ?? 'before',
                    'isdefault'          => 0,
                ]);
            }
        }

        echo json_encode(['success' => true, 'updated_at' => $now, 'csrf_hash' => $this->security->get_csrf_hash()]);
    }

    /**
     * Fetch live exchange rates from external API and update acc_currencies.
     */
    public function fetch_exchange_rates()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        if (!has_permission('accounting', '', 'edit')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Insufficient permissions.']); return;
        }
        header('Content-Type: application/json');
        $this->load->model('xetuu_books/Xb_config_model', 'xb_config');
        $result = $this->xb_config->fetch_exchange_rates();
        echo json_encode(array_merge(['success' => $result['error'] === null], $result));
    }

    /**
     * Seed all ISO 4217 world currencies into both acc_currencies and tblcurrencies.
     */
    public function seed_world_currencies()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        if (!has_permission('accounting', '', 'edit')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Insufficient permissions.']); return;
        }
        header('Content-Type: application/json');
        $this->load->model('xetuu_books/Xb_config_model', 'xb_config');
        $result = $this->xb_config->seed_world_currencies();
        echo json_encode(['success' => true,
            'acc_added'    => $result['acc_added'],
            'perfex_added' => $result['perfex_added'],
            'message'      => 'Added ' . $result['acc_added'] . ' currencies to Xetuu Books and ' . $result['perfex_added'] . ' to Perfex CRM. Refresh the page to see them all.',
        ]);
    }

    /**
     * Sync currencies between tblcurrencies ↔ acc_currencies bidirectionally.
     */
    public function generate_coa()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        header('Content-Type: application/json');
        $this->load->model('xetuu_books/Xb_config_model', 'xb_config');
        try {
            $inserted = $this->xb_config->generate_coa($this->input->post('template', true) ?: 'standard');
            echo json_encode(['success' => true, 'inserted' => $inserted]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function save_settings()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        header('Content-Type: application/json');
        $this->load->model('xetuu_books/Xb_config_model', 'xb_config');
        echo json_encode(['success' => $this->xb_config->save_settings(
            $this->input->post(null, true)
        )]);
    }

    public function sync_from_perfex()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        if (!has_permission('accounting', '', 'edit')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Insufficient permissions.']); return;
        }
        header('Content-Type: application/json');
        $this->load->model('xetuu_books/Xb_config_model', 'xb_config');
        $r1 = $this->xb_config->sync_from_perfex_currencies();
        $r2 = $this->xb_config->sync_to_perfex_currencies();
        echo json_encode([
            'success'      => true,
            'acc_added'    => $r1['added'],
            'acc_updated'  => $r1['updated'],
            'perfex_added' => $r2['added'],
            'message'      => 'Sync complete. Xetuu Books: +' . $r1['added'] . ' added, ' . $r1['updated'] . ' updated. Perfex CRM: +' . $r2['added'] . ' added.',
        ]);
    }

    /**
     * Intelligent full re-sync: scans all Perfex CRM data sources and posts them
     * into the accounting ledger. Safe to run multiple times — existing moves are
     * reset and re-posted. Covers: native invoices, payments, expenses, purchase
     * module invoices/payments, and warehouse goods receipts.
     */
    public function resync_all()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        if (!has_permission('accounting', '', 'edit')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Insufficient permissions.']); return;
        }
        header('Content-Type: application/json');

        set_time_limit(300);

        $this->load->model('xetuu_books/Xb_engine_model', 'xb_engine');

        $counts = [
            'invoices'          => 0,
            'payments'          => 0,
            'expenses'          => 0,
            'purchase_invoices' => 0,
            'purchase_payments' => 0,
            'goods_receipts'    => 0,
        ];

        // 1. Native Perfex CRM Invoices
        $invoices = $this->db->select('id')->get('tblinvoices')->result();
        foreach ($invoices as $row) {
            try { $this->xb_engine->sync_invoice_to_journal($row->id); $counts['invoices']++; }
            catch (\Throwable $e) { log_message('error', '[xb resync] invoice ' . $row->id . ': ' . $e->getMessage()); }
        }

        // 2. Invoice Payments
        $payments = $this->db->select('id')->get('tblinvoicepaymentrecords')->result();
        foreach ($payments as $row) {
            try { $this->xb_engine->sync_payment_to_journal($row->id); $counts['payments']++; }
            catch (\Throwable $e) { log_message('error', '[xb resync] payment ' . $row->id . ': ' . $e->getMessage()); }
        }

        // 3. Native Perfex CRM Expenses
        $expenses = $this->db->select('id')->get('tblexpenses')->result();
        foreach ($expenses as $row) {
            try { $this->xb_engine->sync_expense_to_journal($row->id); $counts['expenses']++; }
            catch (\Throwable $e) { log_message('error', '[xb resync] expense ' . $row->id . ': ' . $e->getMessage()); }
        }

        // 4. Purchase Module Invoices (tblpur_invoices)
        if ($this->db->table_exists('tblpur_invoices')) {
            $pur_invoices = $this->db->select('id')->get('tblpur_invoices')->result();
            foreach ($pur_invoices as $row) {
                try { $this->xb_engine->sync_purchase_invoice_to_journal($row->id); $counts['purchase_invoices']++; }
                catch (\Throwable $e) { log_message('error', '[xb resync] pur_invoice ' . $row->id . ': ' . $e->getMessage()); }
            }
        }

        // 5. Purchase Module Payments (tblpur_invoice_payment)
        if ($this->db->table_exists('tblpur_invoice_payment')) {
            $pur_payments = $this->db->select('id')->get('tblpur_invoice_payment')->result();
            foreach ($pur_payments as $row) {
                try { $this->xb_engine->sync_purchase_payment_to_journal($row->id); $counts['purchase_payments']++; }
                catch (\Throwable $e) { log_message('error', '[xb resync] pur_payment ' . $row->id . ': ' . $e->getMessage()); }
            }
        }

        // 6. Warehouse Goods Receipts (approved only)
        if ($this->db->table_exists('tblgoods_receipt')) {
            $receipts = $this->db->select('id')->where('approval', 1)->get('tblgoods_receipt')->result();
            foreach ($receipts as $row) {
                try { $this->xb_engine->sync_goods_receipt_to_journal($row->id); $counts['goods_receipts']++; }
                catch (\Throwable $e) { log_message('error', '[xb resync] grn ' . $row->id . ': ' . $e->getMessage()); }
            }
        }

        $parts = [];
        if ($counts['invoices'])          $parts[] = $counts['invoices'] . ' invoices';
        if ($counts['payments'])          $parts[] = $counts['payments'] . ' payments';
        if ($counts['expenses'])          $parts[] = $counts['expenses'] . ' expenses';
        if ($counts['purchase_invoices']) $parts[] = $counts['purchase_invoices'] . ' purchase bills';
        if ($counts['purchase_payments']) $parts[] = $counts['purchase_payments'] . ' purchase payments';
        if ($counts['goods_receipts'])    $parts[] = $counts['goods_receipts'] . ' goods receipts';

        $summary = empty($parts) ? 'No data found to sync.' : 'Synced: ' . implode(', ', $parts) . '.';

        echo json_encode(['success' => true, 'message' => $summary, 'counts' => $counts]);
    }
}
