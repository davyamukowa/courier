<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Audit_cli extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Skip auth check for CLI
        if (!is_cli()) {
            die('CLI only');
        }
    }

    public function run()
    {
        echo "=== STARTING E2E AUDIT SIMULATION ===\n";
        
        $this->load->model('xetuu_books/xb_engine_model');
        $this->load->model('xetuu_books/xb_invoice_model');
        $this->load->model('xetuu_books/xb_payment_model');
        
        // Ensure KCB is mapped to 96
        $this->db->query("UPDATE tblacc_journals SET account_id = 96 WHERE id = 2");

        try {
            // SCENARIO 1: Customer Invoice (100,000 + 16% VAT)
            $inv_data = [
                'move_type'  => 'out_invoice',
                'partner_id' => 1,
                'date'       => date('Y-m-d'),
                'ref'        => 'AUDIT-INV-001',
            ];
            $inv_lines = [
                ['account_id' => 4, 'name' => 'Audit Services', 'quantity' => 1, 'price_unit' => 100000, 'tax_id' => 1] // 4 = 40000 Income
            ];
            $inv_id = $this->xb_invoice_model->save_invoice($inv_data, $inv_lines);
            $this->xb_engine_model->post_move($inv_id);
            echo "Scenario 1: Customer Invoice Posted (ID: $inv_id)\n";

            // SCENARIO 2: Customer Payment
            $pay_data = [
                'move_id'      => $inv_id,
                'payment_type' => 'inbound',
                'partner_id'   => 1,
                'journal_id'   => 2, // Bank KCB
                'amount'       => 116000, 
                'date'         => date('Y-m-d'),
                'ref'          => 'AUDIT-PAY-001'
            ];
            $pay_id = $this->xb_payment_model->register_payment($pay_data);
            echo "Scenario 2: Customer Payment Registered (ID: $pay_id)\n";

            // SCENARIO 3: Vendor Bill
            $bill_data = [
                'move_type'  => 'in_invoice',
                'partner_id' => 2,
                'date'       => date('Y-m-d'),
                'ref'        => 'AUDIT-BILL-001',
            ];
            $bill_lines = [
                ['account_id' => 71, 'name' => 'Audit Software License', 'quantity' => 1, 'price_unit' => 50000, 'tax_id' => 1] // 71 = 52220 Misc Expense
            ];
            $bill_id = $this->xb_invoice_model->save_invoice($bill_data, $bill_lines);
            $this->xb_engine_model->post_move($bill_id);
            echo "Scenario 3: Vendor Bill Posted (ID: $bill_id)\n";

            // SCENARIO 4: Vendor Payment
            $vpay_data = [
                'move_id'      => $bill_id,
                'payment_type' => 'outbound',
                'partner_id'   => 2,
                'journal_id'   => 2, // Bank KCB
                'amount'       => 58000, 
                'date'         => date('Y-m-d'),
                'ref'          => 'AUDIT-VPAY-001'
            ];
            $vpay_id = $this->xb_payment_model->register_payment($vpay_data);
            echo "Scenario 4: Vendor Payment Registered (ID: $vpay_id)\n";

            echo "Simulation complete! Checking Trial Balance integrity...\n";

            $res = $this->db->query("SELECT SUM(debit) as d, SUM(credit) as c FROM tblacc_move_lines");
            $row = $res->row_array();
            $d = round($row['d'], 4);
            $c = round($row['c'], 4);
            echo "Total Debit: " . $d . " | Total Credit: " . $c . "\n";
            if ($d === $c) {
                echo "Trial Balance is PERFECTLY BALANCED.\n";
            } else {
                echo "CRITICAL FINDING: Trial Balance is unbalanced! Diff: " . abs($d - $c) . "\n";
            }

        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        }
    }
}
