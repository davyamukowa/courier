<?php
defined('BASEPATH') or exit('No direct script access allowed');

trait Xb_vendors_trait
{
    public function vendors()
    {
        if (!has_permission('purchase_vendors', '', 'view')) { access_denied('xetuu_books'); }

        $this->db->select("userid, CASE company WHEN '' THEN (SELECT CONCAT(firstname, ' ', lastname) FROM " . db_prefix() . "pur_contacts WHERE " . db_prefix() . "pur_contacts.userid = " . db_prefix() . "pur_vendor.userid AND is_primary = 1) ELSE company END as company, phonenumber, vat, website, datecreated");
        $this->db->order_by('company', 'asc');
        $vendors = $this->db->get(db_prefix() . 'pur_vendor')->result();

        $data['title']   = 'Vendors';
        $data['vendors'] = $vendors;
        $data['xb_page'] = 'vendors';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content'          => $this->load->view('xetuu_books/vendors/list', $data, true),
            'xb_hide_page_header' => true,
        ]));
    }

    public function vendor_form($id = null)
    {
        if (!has_permission('purchase_vendors', '', 'view')) { access_denied('xetuu_books'); }

        if ($this->input->post()) {
            $post = $this->input->post(null, true);

            $category = '';
            if (!empty($post['category']) && is_array($post['category'])) {
                $category = implode(',', $post['category']);
            }

            $row = [
                'vendor_code'      => $post['vendor_code'] ?? '',
                'company'          => $post['company'] ?? '',
                'vat'              => $post['vat'] ?? '',
                'phonenumber'      => $post['phonenumber'] ?? '',
                'website'          => $post['website'] ?? '',
                'category'         => $category,
                'default_currency' => !empty($post['default_currency']) ? (int)$post['default_currency'] : 0,
                'default_language' => $post['default_language'] ?? '',
                'address'          => $post['address'] ?? '',
                'city'             => $post['city'] ?? '',
                'state'            => $post['state'] ?? '',
                'zip'              => $post['zip'] ?? '',
                'country'          => !empty($post['country']) ? (int)$post['country'] : 0,
                'bank_detail'      => $post['bank_detail'] ?? '',
                'payment_terms'    => $post['payment_terms'] ?? '',
                // Billing
                'billing_street'   => $post['billing_street'] ?? '',
                'billing_city'     => $post['billing_city'] ?? '',
                'billing_state'    => $post['billing_state'] ?? '',
                'billing_zip'      => $post['billing_zip'] ?? '',
                'billing_country'  => !empty($post['billing_country']) ? (int)$post['billing_country'] : 0,
                // Shipping
                'shipping_street'  => $post['shipping_street'] ?? '',
                'shipping_city'    => $post['shipping_city'] ?? '',
                'shipping_state'   => $post['shipping_state'] ?? '',
                'shipping_zip'     => $post['shipping_zip'] ?? '',
                'shipping_country' => !empty($post['shipping_country']) ? (int)$post['shipping_country'] : 0,
                // Return policies
                'return_within_day' => !empty($post['return_within_day']) ? (int)$post['return_within_day'] : null,
                'return_order_fee'  => $post['return_order_fee'] ?? '',
                'return_policies'   => $post['return_policies'] ?? '',
            ];

            if ($id) {
                $this->db->where('userid', $id)->update(db_prefix() . 'pur_vendor', $row);
                set_alert('success', 'Vendor updated successfully.');
                redirect(admin_url('xetuu_books/vendor_form/' . $id));
            } else {
                $row['datecreated'] = date('Y-m-d H:i:s');
                $row['addedfrom']   = get_staff_user_id();
                $this->db->insert(db_prefix() . 'pur_vendor', $row);
                $new_id = $this->db->insert_id();
                set_alert('success', 'Vendor created successfully.');
                redirect(admin_url('xetuu_books/vendor_form/' . $new_id));
            }
        }

        $vendor = null;
        if ($id) {
            $vendor = $this->db->where('userid', $id)->get(db_prefix() . 'pur_vendor')->row();
            if (!$vendor) { show_404(); }
        }

        $data['title']             = $id ? 'Edit Vendor' : 'New Vendor';
        $data['vendor']            = $vendor;
        $data['countries']         = $this->db->order_by('short_name', 'asc')->get(db_prefix() . 'countries')->result();
        $data['currencies']        = $this->db->get(db_prefix() . 'currencies')->result();
        $data['vendor_categories'] = $this->db->get(db_prefix() . 'pur_vendor_cate')->result_array();
        $data['languages']         = $this->app->get_available_languages();
        $data['xb_page']           = 'vendors';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content'          => $this->load->view('xetuu_books/vendors/form', $data, true),
            'xb_hide_page_header' => true,
        ]));
    }

    public function delete_vendor($id)
    {
        if (!has_permission('purchase_vendors', '', 'delete')) { access_denied('xetuu_books'); }
        $this->db->where('userid', $id)->delete(db_prefix() . 'pur_vendor');
        set_alert('success', 'Vendor deleted.');
        redirect(admin_url('xetuu_books/vendors'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PURCHASE REQUESTS
    // ─────────────────────────────────────────────────────────────────────────
    public function purchase_requests()
    {
        if (!has_permission('purchase_request', '', 'view')) { access_denied('xetuu_books'); }

        $this->db->select('pr.id, pr.pur_rq_code, pr.pur_rq_name, pr.status, pr.total, pr.request_date,
            CONCAT(s.firstname," ",s.lastname) as requester_name')
            ->from(db_prefix() . 'pur_request pr')
            ->join(db_prefix() . 'staff s', 's.staffid = pr.requester', 'left')
            ->order_by('pr.request_date', 'DESC');

        $data['title']    = 'Purchase Requests';
        $data['requests'] = $this->db->get()->result();
        $data['xb_page']  = 'purchase_requests';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/purchase/requests_list', $data, true),
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // QUOTATIONS
    // ─────────────────────────────────────────────────────────────────────────
    public function purchase_quotations()
    {
        if (!has_permission('purchase_quotations', '', 'view')) { access_denied('xetuu_books'); }

        $this->db->select('pe.id, pe.number, pe.date, pe.expirydate, pe.status, pe.total, pe.currency,
            v.company as vendor_name, pr.pur_rq_code as pr_code')
            ->from(db_prefix() . 'pur_estimates pe')
            ->join(db_prefix() . 'pur_vendor v', 'v.userid = pe.vendor', 'left')
            ->join(db_prefix() . 'pur_request pr', 'pr.id = pe.pur_request', 'left')
            ->order_by('pe.date', 'DESC');

        $data['title']      = 'Purchase Quotations';
        $data['quotations'] = $this->db->get()->result();
        $data['xb_page']    = 'purchase_quotations';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/purchase/quotations_list', $data, true),
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PURCHASE ORDERS
    // ─────────────────────────────────────────────────────────────────────────
    public function purchase_orders()
    {
        if (!has_permission('purchase_orders', '', 'view')) { access_denied('xetuu_books'); }

        $pfx = $this->db->dbprefix;
        $this->db->select('po.id, po.pur_order_number, po.order_date, po.status, po.approve_status,
            po.delivery_status, po.total, po.currency,
            v.company as vendor_name,
            (SELECT COUNT(*) FROM ' . $pfx . 'pur_invoices pi WHERE pi.pur_order = po.id) as invoice_count')
            ->from(db_prefix() . 'pur_orders po')
            ->join(db_prefix() . 'pur_vendor v', 'v.userid = po.vendor', 'left')
            ->order_by('po.order_date', 'DESC');

        $data['title']  = 'Purchase Orders';
        $data['orders'] = $this->db->get()->result();
        $data['xb_page'] = 'purchase_orders';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/purchase/orders_list', $data, true),
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PURCHASE INVOICES (from purchase module — synced to GL)
    // ─────────────────────────────────────────────────────────────────────────
    public function purchase_invoices()
    {
        if (!has_permission('purchase_invoices', '', 'view')) { access_denied('xetuu_books'); }

        $pfx = $this->db->dbprefix;
        $this->db->select('pi.id, pi.number, pi.invoice_date, pi.duedate, pi.payment_status,
            pi.total, pi.currency,
            v.company as vendor_name,
            po.pur_order_number,
            am.state as gl_state, am.name as gl_ref')
            ->from(db_prefix() . 'pur_invoices pi')
            ->join(db_prefix() . 'pur_vendor v', 'v.userid = pi.vendor', 'left')
            ->join(db_prefix() . 'pur_orders po', 'po.id = pi.pur_order', 'left')
            ->join(db_prefix() . 'acc_moves am',
                'am.ref = CONCAT("PURINV-", pi.id) AND am.move_type = "in_invoice"', 'left')
            ->order_by('pi.invoice_date', 'DESC');

        $data['title']    = 'Purchase Invoices';
        $data['invoices'] = $this->db->get()->result();
        $data['xb_page']  = 'purchase_invoices';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/purchase/invoices_list', $data, true),
        ]));
    }

    // Record payment for a purchase invoice — writes to BOTH pur_invoice_payment AND acc GL
    public function purchase_invoice_payment($invoice_id)
    {
        if (!has_permission('purchase_invoices', '', 'edit')) { access_denied('xetuu_books'); }
        if (!$this->input->post()) { show_404(); }

        $invoice = $this->db->where('id', $invoice_id)->get(db_prefix() . 'pur_invoices')->row();
        if (!$invoice) { show_404(); }

        $amount  = (float)$this->input->post('amount');
        $date    = $this->input->post('date') ?: date('Y-m-d');
        $mode    = $this->input->post('paymentmode') ?: 'Bank';
        $note    = $this->input->post('note') ?: '';

        // 1. Write to purchase module payment table
        $this->db->insert(db_prefix() . 'pur_invoice_payment', [
            'pur_invoice'  => $invoice_id,
            'amount'       => $amount,
            'paymentmode'  => $mode,
            'date'         => $date,
            'note'         => $note,
            'requester'    => get_staff_user_id(),
        ]);

        // 2. Update invoice payment_status
        $paid = (float)$this->db->select('SUM(amount) as paid')
            ->where('pur_invoice', $invoice_id)->get(db_prefix() . 'pur_invoice_payment')->row()->paid;
        $total = (float)$invoice->total;
        $status = $paid >= $total ? 'paid' : ($paid > 0 ? 'partially_paid' : 'unpaid');
        $this->db->where('id', $invoice_id)->update(db_prefix() . 'pur_invoices', ['payment_status' => $status]);

        // 3. Post to accounting journal via sync
        try {
            $this->load->model('xetuu_books/Xb_engine_model', 'xb_engine');
            $this->xb_engine->sync_purchase_invoice_to_journal($invoice_id);
        } catch (\Throwable $e) {
            log_message('error', '[xb pur_invoice_payment] ' . $e->getMessage());
        }

        set_alert('success', 'Payment of ' . number_format($amount, 2) . ' recorded and posted to ledger.');
        redirect(admin_url('xetuu_books/purchase_invoices'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONTRACTS (with signature)
    // ─────────────────────────────────────────────────────────────────────────
    public function purchase_contracts()
    {
        if (!has_permission('purchase_contracts', '', 'view')) { access_denied('xetuu_books'); }

        $this->db->select('c.id, c.contract_number, c.contract_name, c.contract_value,
            c.start_date, c.end_date, c.signed, c.signed_status, c.signed_date,
            v.company as vendor_name,
            po.pur_order_number,
            CONCAT(s.firstname," ",s.lastname) as signer_name')
            ->from(db_prefix() . 'pur_contracts c')
            ->join(db_prefix() . 'pur_vendor v', 'v.userid = c.vendor', 'left')
            ->join(db_prefix() . 'pur_orders po', 'po.id = c.pur_order', 'left')
            ->join(db_prefix() . 'staff s', 's.staffid = c.signer', 'left')
            ->order_by('c.start_date', 'DESC');

        $data['title']     = 'Purchase Contracts';
        $data['contracts'] = $this->db->get()->result();
        $data['xb_page']   = 'purchase_contracts';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/purchase/contracts_list', $data, true),
        ]));
    }

    // Sign a contract
    public function sign_purchase_contract($id)
    {
        if (!has_permission('purchase_contracts', '', 'edit')) { access_denied('xetuu_books'); }
        $contract = $this->db->where('id', $id)->get(db_prefix() . 'pur_contracts')->row();
        if (!$contract) { show_404(); }

        $this->db->where('id', $id)->update(db_prefix() . 'pur_contracts', [
            'signed'        => 1,
            'signed_status' => 'signed',
            'signed_date'   => date('Y-m-d H:i:s'),
            'signer'        => get_staff_user_id(),
        ]);

        // Also reflect in purchase module
        hooks()->do_action('contract_signed', $id);

        set_alert('success', 'Contract signed successfully.');
        redirect(admin_url('xetuu_books/purchase_contracts'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DEBIT NOTES (vendor returns / credit memos)
    // ─────────────────────────────────────────────────────────────────────────
    public function purchase_debit_notes()
    {
        if (!has_permission('purchase_debit_notes', '', 'view')) { access_denied('xetuu_books'); }

        $this->db->select('dn.id, dn.number, dn.date, dn.status, dn.total, dn.currency,
            v.company as vendor_name')
            ->from(db_prefix() . 'pur_debit_notes dn')
            ->join(db_prefix() . 'pur_vendor v', 'v.userid = dn.vendorid', 'left')
            ->order_by('dn.date', 'DESC');

        $data['title']       = 'Debit Notes';
        $data['debit_notes'] = $this->db->get()->result();
        $data['xb_page']     = 'purchase_debit_notes';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/purchase/debit_notes_list', $data, true),
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ORDER RETURNS
    // ─────────────────────────────────────────────────────────────────────────
    public function purchase_order_returns()
    {
        if (!has_permission('purchase_orders', '', 'view')) { access_denied('xetuu_books'); }

        // Table may not exist if purchase module is not installed / old version
        if (!$this->db->table_exists(db_prefix() . 'pur_order_return')) {
            $data['title']   = 'Order Returns';
            $data['returns'] = [];
            $data['xb_page'] = 'purchase_order_returns';
        } else {
            $this->db->select('r.id, r.return_code, r.return_name, r.status, r.total, r.datecreated,
                v.company as vendor_name, po.pur_order_number')
                ->from(db_prefix() . 'pur_order_return r')
                ->join(db_prefix() . 'pur_vendor v', 'v.userid = r.vendor', 'left')
                ->join(db_prefix() . 'pur_orders po', 'po.id = r.pur_order', 'left')
                ->order_by('r.datecreated', 'DESC');

            $data['title']   = 'Order Returns';
            $data['returns'] = $this->db->get()->result();
            $data['xb_page'] = 'purchase_order_returns';
        }

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/purchase/order_returns_list', $data, true),
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS: shared data for purchase forms
    // ─────────────────────────────────────────────────────────────────────────
    private function _pur_form_data()
    {
        return [
            'vendors'        => $this->db->get(db_prefix() . 'pur_vendor')->result(),
            'staff'          => $this->db->get(db_prefix() . 'staff')->result(),
            'taxes'          => $this->db->get(db_prefix() . 'taxes')->result(),
            'currencies'     => $this->db->get(db_prefix() . 'currencies')->result(),
            'purchase_orders'=> $this->db->select('id, pur_order_number')->get(db_prefix() . 'pur_orders')->result(),
            'quotations'     => $this->db->select('pe.id, pe.number, v.company as vendor_name')
                ->from(db_prefix() . 'pur_estimates pe')
                ->join(db_prefix() . 'pur_vendor v', 'v.userid = pe.vendor', 'left')
                ->get()->result(),
        ];
    }

    private function _auto_code($prefix, $table, $field)
    {
        $last = $this->db->select_max($field)->get(db_prefix() . $table)->row();
        $num  = $last && $last->$field ? ((int)preg_replace('/\D/', '', $last->$field) + 1) : 1;
        return $prefix . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PURCHASE REQUEST FORM
    // ─────────────────────────────────────────────────────────────────────────
    public function purchase_request_form($id = 0)
    {
        if (!has_permission('purchase_request', '', 'view')) { access_denied('xetuu_books'); }

        if ($this->input->post()) {
            redirect(admin_url('xetuu_books/save_purchase_request/' . $id));
        }

        $data = $this->_pur_form_data();
        if ($id) {
            $req = $this->db->select('r.*, CONCAT(s.firstname," ",s.lastname) as requester_name')
                ->from(db_prefix() . 'pur_request r')
                ->join(db_prefix() . 'staff s', 's.staffid = r.requester', 'left')
                ->where('r.id', $id)->get()->row();
            if (!$req) { show_404(); }
            $data['request']    = $req;
            $data['line_items'] = $this->db->where('pur_request', $id)->get(db_prefix() . 'pur_request_detail')->result();
        }
        $data['title']   = $id ? 'Edit Purchase Request' : 'New Purchase Request';
        $data['xb_page'] = 'purchase_requests';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/purchase/request_form', $data, true),
        ]));
    }

    public function save_purchase_request($id = 0)
    {
        if (!has_permission('purchase_request', '', 'edit')) { access_denied('xetuu_books'); }

        $row = [
            'pur_rq_name'    => $this->input->post('pur_rq_name'),
            'rq_description' => $this->input->post('rq_description'),
            'requester'      => $this->input->post('requester') ?: get_staff_user_id(),
            'request_date'   => $this->input->post('request_date') ?: date('Y-m-d'),
            'status'         => $this->input->post('status') ?: 1,
            'subtotal'       => (float)$this->input->post('subtotal'),
            'total_tax'      => (float)$this->input->post('total_tax'),
            'total'          => (float)$this->input->post('total'),
        ];

        if ($id) {
            $this->db->where('id', $id)->update(db_prefix() . 'pur_request', $row);
        } else {
            $row['pur_rq_code'] = $this->_auto_code('PRQ-', 'pur_request', 'pur_rq_code');
            $this->db->insert(db_prefix() . 'pur_request', $row);
            $id = $this->db->insert_id();
        }

        // Save line items
        $lines = $this->input->post('lines') ?: [];
        $this->db->where('pur_request', $id)->delete(db_prefix() . 'pur_request_detail');
        foreach ($lines as $l) {
            if (empty($l['item_text'])) continue;
            $this->db->insert(db_prefix() . 'pur_request_detail', [
                'pur_request' => $id,
                'item_text'   => $l['item_text'],
                'quantity'    => (float)($l['quantity'] ?? 1),
                'unit_price'  => (float)($l['unit_price'] ?? 0),
                'tax_rate'    => (float)($l['tax_rate'] ?? 0),
                'total'       => (float)($l['total'] ?? 0),
            ]);
        }

        set_alert('success', 'Purchase request saved.');
        redirect(admin_url('xetuu_books/purchase_request_form/' . $id));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PURCHASE ORDER FORM
    // ─────────────────────────────────────────────────────────────────────────
    public function purchase_order_form($id = 0)
    {
        if (!has_permission('purchase_orders', '', 'view')) { access_denied('xetuu_books'); }

        $data = $this->_pur_form_data();
        if ($id) {
            $ord = $this->db->select('po.*, v.company as vendor_name')
                ->from(db_prefix() . 'pur_orders po')
                ->join(db_prefix() . 'pur_vendor v', 'v.userid = po.vendor', 'left')
                ->where('po.id', $id)->get()->row();
            if (!$ord) { show_404(); }
            $data['order']      = $ord;
            $data['line_items'] = $this->db->where('pur_order', $id)->get(db_prefix() . 'pur_order_detail')->result();
        }
        $data['title']   = $id ? 'Edit Purchase Order' : 'New Purchase Order';
        $data['xb_page'] = 'purchase_orders';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/purchase/order_form', $data, true),
        ]));
    }

    public function save_purchase_order($id = 0)
    {
        if (!has_permission('purchase_orders', '', 'edit')) { access_denied('xetuu_books'); }

        $row = [
            'vendor'         => $this->input->post('vendor'),
            'estimate'       => $this->input->post('estimate') ?: null,
            'order_date'     => $this->input->post('order_date') ?: date('Y-m-d'),
            'delivery_date'  => $this->input->post('delivery_date') ?: null,
            'approve_status' => (int)$this->input->post('approve_status'),
            'vendornote'     => $this->input->post('vendornote'),
            'terms'          => $this->input->post('terms'),
            'subtotal'       => (float)$this->input->post('subtotal'),
            'total_tax'      => (float)$this->input->post('total_tax'),
            'total'          => (float)$this->input->post('total'),
        ];

        if ($id) {
            $this->db->where('id', $id)->update(db_prefix() . 'pur_orders', $row);
        } else {
            $row['pur_order_number'] = $this->_auto_code('PO-', 'pur_orders', 'pur_order_number');
            $row['datecreated']      = date('Y-m-d H:i:s');
            $row['addedfrom']        = get_staff_user_id();
            $this->db->insert(db_prefix() . 'pur_orders', $row);
            $id = $this->db->insert_id();
        }

        $lines = $this->input->post('lines') ?: [];
        $this->db->where('pur_order', $id)->delete(db_prefix() . 'pur_order_detail');
        foreach ($lines as $l) {
            if (empty($l['description'])) continue;
            $qty   = (float)($l['quantity'] ?? 1);
            $price = (float)($l['unit_price'] ?? 0);
            $tax   = (float)($l['tax_rate'] ?? 0);
            $sub   = $qty * $price;
            $this->db->insert(db_prefix() . 'pur_order_detail', [
                'pur_order'   => $id,
                'description' => $l['description'],
                'item_name'   => $l['description'],
                'quantity'    => $qty,
                'unit_price'  => $price,
                'tax_rate'    => $tax,
                'tax_value'   => $sub * $tax / 100,
                'into_money'  => $sub,
                'total'       => (float)($l['total'] ?? 0),
                'total_money' => (float)($l['total'] ?? 0),
            ]);
        }

        set_alert('success', 'Purchase order saved.');
        redirect(admin_url('xetuu_books/purchase_order_form/' . $id));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PURCHASE INVOICE FORM
    // ─────────────────────────────────────────────────────────────────────────
    public function purchase_invoice_form($id = 0)
    {
        if (!has_permission('purchase_invoices', '', 'view')) { access_denied('xetuu_books'); }

        $pfx  = $this->db->dbprefix;
        $data = $this->_pur_form_data();

        if ($id) {
            $inv = $this->db->select('pi.*, v.company as vendor_name, po.pur_order_number,
                    am.state as gl_state, am.name as gl_ref')
                ->from(db_prefix() . 'pur_invoices pi')
                ->join(db_prefix() . 'pur_vendor v', 'v.userid = pi.vendor', 'left')
                ->join(db_prefix() . 'pur_orders po', 'po.id = pi.pur_order', 'left')
                ->join(db_prefix() . 'acc_moves am',
                    'am.ref = CONCAT("PURINV-", pi.id) AND am.move_type = "in_invoice"', 'left')
                ->where('pi.id', $id)->get()->row();
            if (!$inv) { show_404(); }
            $data['invoice']    = $inv;
            $data['line_items'] = $this->db->where('pur_invoice', $id)->get(db_prefix() . 'pur_invoice_details')->result();
        }
        $data['title']   = $id ? 'Edit Purchase Invoice' : 'New Purchase Invoice';
        $data['xb_page'] = 'purchase_invoices';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/purchase/invoice_form', $data, true),
        ]));
    }

    public function save_purchase_invoice($id = 0)
    {
        if (!has_permission('purchase_invoices', '', 'edit')) { access_denied('xetuu_books'); }

        $subtotal = (float)$this->input->post('subtotal');
        $tax      = (float)$this->input->post('tax');
        $total    = (float)$this->input->post('total');

        $row = [
            'vendor'               => $this->input->post('vendor'),
            'vendor_invoice_number'=> $this->input->post('vendor_invoice_number'),
            'invoice_date'         => $this->input->post('invoice_date') ?: date('Y-m-d'),
            'duedate'              => $this->input->post('duedate') ?: null,
            'pur_order'            => $this->input->post('pur_order') ?: null,
            'currency'             => $this->input->post('currency') ?: 'KES',
            'terms'                => $this->input->post('terms'),
            'vendor_note'          => $this->input->post('vendor_note'),
            'adminnote'            => $this->input->post('adminnote'),
            'subtotal'             => $subtotal,
            'tax'                  => $tax,
            'total'                => $total,
            'payment_status'       => 'unpaid',
        ];

        if ($id) {
            $this->db->where('id', $id)->update(db_prefix() . 'pur_invoices', $row);
        } else {
            $row['number']    = $this->_auto_code('PINV-', 'pur_invoices', 'number');
            $row['date_add']  = date('Y-m-d H:i:s');
            $row['add_from']  = get_staff_user_id();
            $this->db->insert(db_prefix() . 'pur_invoices', $row);
            $id = $this->db->insert_id();
        }

        // Save line items
        $lines = $this->input->post('lines') ?: [];
        $this->db->where('pur_invoice', $id)->delete(db_prefix() . 'pur_invoice_details');
        foreach ($lines as $l) {
            if (empty($l['description'])) continue;
            $qty   = (float)($l['quantity'] ?? 1);
            $price = (float)($l['unit_price'] ?? 0);
            $taxr  = (float)($l['tax_rate'] ?? 0);
            $sub   = $qty * $price;
            $taxv  = $sub * $taxr / 100;
            $this->db->insert(db_prefix() . 'pur_invoice_details', [
                'pur_invoice'  => $id,
                'item_name'    => $l['description'],
                'description'  => $l['description'],
                'quantity'     => $qty,
                'unit_price'   => $price,
                'tax_rate'     => $taxr,
                'tax_value'    => $taxv,
                'into_money'   => $sub,
                'total'        => (float)($l['total'] ?? 0),
                'total_money'  => (float)($l['total'] ?? 0),
            ]);
        }

        // Post to accounting GL — creates/updates a draft vendor bill in acc_moves
        try {
            $this->_sync_pur_invoice_to_gl($id, $total, $tax);
        } catch (\Throwable $e) {
            log_message('error', '[xb save_pur_invoice GL] ' . $e->getMessage());
        }

        set_alert('success', 'Purchase invoice saved and posted to GL.');
        redirect(admin_url('xetuu_books/purchase_invoice_form/' . $id));
    }

    private function _sync_pur_invoice_to_gl($inv_id, $total, $tax)
    {
        $inv = $this->db->where('id', $inv_id)->get(db_prefix() . 'pur_invoices')->row();
        if (!$inv) return;
        $ref = 'PURINV-' . $inv_id;

        // Find or create acc_move
        $move = $this->db->where('ref', $ref)->where('move_type', 'in_invoice')->get(db_prefix() . 'acc_moves')->row();
        $move_data = [
            'name'       => $inv->number,
            'ref'        => $ref,
            'move_type'  => 'in_invoice',
            'state'      => 'draft',
            'partner_id' => $inv->vendor,
            'invoice_date'    => $inv->invoice_date,
            'invoice_date_due'=> $inv->duedate,
            'amount_untaxed'  => $inv->subtotal,
            'amount_tax'      => $tax,
            'amount_total'    => $total,
            'currency_id'     => 1,
        ];
        if ($move) {
            $this->db->where('id', $move->id)->update(db_prefix() . 'acc_moves', $move_data);
        } else {
            $move_data['date'] = $inv->invoice_date ?: date('Y-m-d');
            $this->db->insert(db_prefix() . 'acc_moves', $move_data);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PURCHASE CONTRACT FORM
    // ─────────────────────────────────────────────────────────────────────────
    public function purchase_contract_form($id = 0)
    {
        if (!has_permission('purchase_contracts', '', 'view')) { access_denied('xetuu_books'); }

        $data = $this->_pur_form_data();
        if ($id) {
            $con = $this->db->select('c.*, v.company as vendor_name, po.pur_order_number,
                    CONCAT(s.firstname," ",s.lastname) as signer_name')
                ->from(db_prefix() . 'pur_contracts c')
                ->join(db_prefix() . 'pur_vendor v', 'v.userid = c.vendor', 'left')
                ->join(db_prefix() . 'pur_orders po', 'po.id = c.pur_order', 'left')
                ->join(db_prefix() . 'staff s', 's.staffid = c.signer', 'left')
                ->where('c.id', $id)->get()->row();
            if (!$con) { show_404(); }
            $data['contract'] = $con;
        }
        $data['title']   = $id ? 'Edit Contract' : 'New Purchase Contract';
        $data['xb_page'] = 'purchase_contracts';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/purchase/contract_form', $data, true),
        ]));
    }

    public function save_purchase_contract_form($id = 0)
    {
        if (!has_permission('purchase_contracts', '', 'edit')) { access_denied('xetuu_books'); }

        // Prevent editing signed contracts
        if ($id) {
            $c = $this->db->where('id', $id)->get(db_prefix() . 'pur_contracts')->row();
            if ($c && (int)$c->signed === 1) {
                set_alert('warning', 'Signed contracts cannot be edited.');
                redirect(admin_url('xetuu_books/purchase_contract_form/' . $id));
                return;
            }
        }

        $row = [
            'contract_name'  => $this->input->post('contract_name'),
            'vendor'         => $this->input->post('vendor'),
            'contract_value' => (float)$this->input->post('contract_value'),
            'start_date'     => $this->input->post('start_date') ?: null,
            'end_date'       => $this->input->post('end_date') ?: null,
            'pur_order'      => $this->input->post('pur_order') ?: null,
            'payment_terms'  => $this->input->post('payment_terms'),
            'content'        => $this->input->post('content'),
            'note'           => $this->input->post('note'),
        ];

        if ($id) {
            $this->db->where('id', $id)->update(db_prefix() . 'pur_contracts', $row);
        } else {
            $row['contract_number'] = $this->_auto_code('CON-', 'pur_contracts', 'contract_number');
            $row['add_from']        = get_staff_user_id();
            $this->db->insert(db_prefix() . 'pur_contracts', $row);
            $id = $this->db->insert_id();
        }

        set_alert('success', 'Contract saved.');
        redirect(admin_url('xetuu_books/purchase_contract_form/' . $id));
    }
}
