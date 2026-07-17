<?php
defined('BASEPATH') or exit('No direct script access allowed');

trait Xb_payments_trait
{
    public function payments()
    {
        $data['title']    = 'Customer Payments';
        $data['payments'] = $this->xb_payment->get_list(['partner_type' => 'customer']);
        $data['xb_page']  = 'payments';
        $data['partner_type'] = 'customer';
        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/payments/list', $data, true),
        ]));
    }

    public function payment($id = '')
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['partner_type'] = 'customer';
            $payment_id = $this->xb_payment->register_payment($data);
            set_alert('success', 'Customer Payment recorded.');
            redirect(admin_url('xetuu_books/payments'));
        }

        $data['title']        = 'New Customer Payment';
        $data['partner_type'] = 'customer';
        $data['xb_page']      = 'payment_form';
        $data['bodyclass']    = 'invoice';
        $data['xb_hide_page_header'] = true;

        $data['partners'] = $this->db->where('active', 1)->get(db_prefix() . 'clients')->result();

        $this->db->where('type', 'bank')->or_where('type', 'cash');
        $data['payment_journals'] = $this->db->get('acc_journals')->result();

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/payments/form', $data, true),
        ]));
    }

    public function vendor_payments()
    {
        $data['title']    = 'Vendor Payments';
        $data['payments'] = $this->xb_payment->get_list(['partner_type' => 'vendor']);
        $data['xb_page']  = 'vendor_payments';
        $data['partner_type'] = 'vendor';
        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/payments/list', $data, true),
        ]));
    }

    public function vendor_payment($id = '')
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['partner_type'] = 'vendor';
            $payment_id = $this->xb_payment->register_payment($data);
            set_alert('success', 'Vendor Payment recorded.');
            redirect(admin_url('xetuu_books/vendor_payments'));
        }

        $data['title']        = 'New Vendor Payment';
        $data['partner_type'] = 'vendor';
        $data['xb_page']      = 'vendor_payment_form';
        $data['bodyclass']    = 'invoice';
        $data['xb_hide_page_header'] = true;

        $this->load->model('xetuu_books/xb_config_model', 'xb_config');
        $data['partners'] = $this->db->get('pur_vendor')->result();

        $this->db->where('type', 'bank')->or_where('type', 'cash');
        $data['payment_journals'] = $this->db->get('acc_journals')->result();

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/payments/form', $data, true),
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BATCH PAYMENTS
    // ─────────────────────────────────────────────────────────────────────────

    public function batch_payments()
    {
        $data['title']   = 'Customer Batch Payments';
        $data['batches'] = $this->xb_batch->get_list(['payment_type' => 'inbound']);
        $data['xb_page'] = 'batch_payments';
        $data['payment_type'] = 'inbound';
        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/batch_payments/list', $data, true),
        ]));
    }

    public function vendor_batch_payments()
    {
        $data['title']   = 'Vendor Batch Payments';
        $data['batches'] = $this->xb_batch->get_list(['payment_type' => 'outbound']);
        $data['xb_page'] = 'vendor_batch_payments';
        $data['payment_type'] = 'outbound';
        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/batch_payments/list', $data, true),
        ]));
    }

    public function batch_payment($id = '')
    {
        $this->_handle_batch_payment_form($id, 'inbound', 'Customer Batch Payment');
    }

    public function vendor_batch_payment($id = '')
    {
        $this->_handle_batch_payment_form($id, 'outbound', 'Vendor Batch Payment');
    }

    private function _handle_batch_payment_form($id, $payment_type, $title)
    {
        if ($id) {
            $data['batch'] = $this->xb_batch->get($id);
            if (!$data['batch']) show_404();
            $data['title'] = $data['batch']->name;
        } else {
            if ($this->input->post()) {
                $post = $this->input->post();
                $invoice_ids = $this->input->post('invoice_ids'); // Array of IDs
                $post['payment_type'] = $payment_type;
                
                try {
                    $batch_id = $this->xb_batch->create_batch($post, $invoice_ids);
                    set_alert('success', 'Batch payment created successfully.');
                    $redirect_url = ($payment_type === 'inbound') ? 'batch_payments' : 'vendor_batch_payments';
                    redirect(admin_url('xetuu_books/' . $redirect_url));
                } catch (Exception $e) {
                    set_alert('danger', $e->getMessage());
                }
            }
            $data['title'] = 'New ' . $title;
        }

        $data['payment_type'] = $payment_type;
        $data['xb_page']      = 'batch_payment_form';
        $data['bodyclass']    = 'invoice';
        $data['xb_hide_page_header'] = true;

        $this->db->where('type', 'bank')->or_where('type', 'cash');
        $data['payment_journals'] = $this->db->get('acc_journals')->result();

        // Get unpaid invoices/bills
        $move_type = ($payment_type === 'inbound') ? 'out_invoice' : 'in_invoice';
        
        if ($payment_type === 'inbound') {
            $this->db->select('am.*, COALESCE(c.company, "") as partner_name');
            $this->db->from('acc_moves am');
            $this->db->join('tblclients c', 'c.userid = am.partner_id', 'left');
        } else {
            $this->db->select('am.*, COALESCE(pv.company, "") as partner_name');
            $this->db->from('acc_moves am');
            $this->db->join('tblpur_vendor pv', 'pv.userid = am.partner_id', 'left');
        }

        $this->db->where('am.move_type', $move_type);
        $this->db->where('am.state', 'posted');
        $this->db->where('am.payment_state !=', 'paid');
        $this->db->where('am.amount_residual >', 0);
        $data['unpaid_invoices'] = $this->db->get()->result();

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/batch_payments/form', $data, true),
        ]));
    }

    public function register_payment()
    {
        if (!$this->input->post() || !$this->input->is_ajax_request()) { show_404(); }
        $post = $this->input->post(null, true);
        try {
            $payment_id = $this->xb_payment->register_payment($post);
            echo json_encode(['success' => true, 'payment_id' => $payment_id]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
