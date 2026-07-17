<?php
defined('BASEPATH') or exit('No direct script access allowed');

trait Xb_transactions_trait
{
    public function receipts()
    {
        if (!has_permission('xb_invoices', '', 'view')) { access_denied('xetuu_books'); }

        $filters = [
            'state'     => $this->input->get('state'),
            'date_from' => $this->input->get('date_from'),
            'date_to'   => $this->input->get('date_to'),
            'search'    => $this->input->get('search'),
        ];

        $data['title']       = 'Sales Receipts';
        $data['moves']       = $this->xb_invoice->get_list('out_receipt', $filters);
        $data['totals']      = $this->xb_invoice->get_list_totals('out_receipt', $filters);
        $data['filters']     = $filters;
        $data['move_type']   = 'out_receipt';
        $data['xb_page']     = 'receipts';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/receipts/list', $data, true),
        ]));
    }

    public function receipt($id = '')
    {
        if (!has_permission('xb_invoices', '', 'view')) { access_denied('xetuu_books'); }

        if ($this->input->post()) {
            if (!has_permission('xb_invoices', '', 'create')) { access_denied('xetuu_books'); }
            
            $post_data = $this->input->post();
            $analytic_id = isset($post_data['xb_analytic_account_id']) ? (int)$post_data['xb_analytic_account_id'] : 0;
            if (isset($post_data['xb_analytic_account_id'])) {
                unset($post_data['xb_analytic_account_id']);
            }
            
            $res = $this->xb_invoice->save_receipt($post_data, 'out_receipt');
            if (is_array($res) && isset($res['id'])) {
                if ($analytic_id > 0 && function_exists('xb_save_analytic_assignment')) {
                    xb_save_analytic_assignment('receipt', $res['id'], $analytic_id);
                }
                set_alert('success', 'Receipt saved successfully');
                if (isset($post_data['save_and_send_later'])) {
                    $this->session->set_userdata('send_later', true);
                }
                redirect(admin_url('xetuu_books/receipt/' . $res['id']));
            } else {
                set_alert('warning', is_string($res) ? $res : 'Failed to save receipt');
            }
        }

        $data['title']     = $id ? 'View Receipt' : 'New Sales Receipt';
        $data['move']      = $id ? $this->xb_invoice->get($id) : null;
        $data['lines']     = $id ? $this->xb_invoice->get_lines($id) : [];
        $data['move_type']           = 'out_receipt';
        $data['xb_page']             = 'receipt_form';
        $data['bodyclass']           = 'invoice';
        $data['xb_hide_page_header'] = true;

        $this->load->model('payment_modes_model');
        $this->load->model('taxes_model');
        $this->load->model('invoice_items_model');
        $this->load->model('currencies_model');
        $this->load->model('staff_model');

        $data['invoices_to_merge'] = [];
        $data['expenses_to_bill']  = [];
        $data['billable_tasks']    = [];
        $data['payment_modes']     = $this->payment_modes_model->get('', ['expenses_only !=' => 1]);
        $data['taxes']             = $this->taxes_model->get();

        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups']  = $this->invoice_items_model->get_groups();
        $data['currencies']    = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['staff']         = $this->staff_model->get('', ['active' => 1]);

        $this->db->where('type', 'bank')->or_where('type', 'cash');
        $data['payment_journals'] = $this->db->get('acc_journals')->result();

        $data['xb_stat_draft_to_sent'] = 0;
        $data['xb_stat_sent_to_paid']  = 0;
        $data['xb_stat_overdue_rate']  = 0;
        $data['xb_stat_avg_days']      = 0;
        $data['xb_recent_payments']    = [];

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/receipts/form', $data, true),
        ]));
    }

    public function vendor_receipts()
    {
        if (!has_permission('xb_invoices', '', 'view')) { access_denied('xetuu_books'); }

        $filters = [
            'state'     => $this->input->get('state'),
            'date_from' => $this->input->get('date_from'),
            'date_to'   => $this->input->get('date_to'),
            'search'    => $this->input->get('search'),
        ];

        $data['title']       = 'Purchase Receipts';
        $data['moves']       = $this->xb_invoice->get_list('in_receipt', $filters);
        $data['totals']      = $this->xb_invoice->get_list_totals('in_receipt', $filters);
        $data['filters']     = $filters;
        $data['move_type']   = 'in_receipt';
        $data['xb_page']     = 'vendor_receipts';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/receipts/list', $data, true),
        ]));
    }

    public function vendor_receipt($id = '')
    {
        if (!has_permission('xb_invoices', '', 'view')) { access_denied('xetuu_books'); }

        if ($this->input->post()) {
            if (!has_permission('xb_invoices', '', 'create')) { access_denied('xetuu_books'); }
            
            $post_data = $this->input->post();
            $analytic_id = isset($post_data['xb_analytic_account_id']) ? (int)$post_data['xb_analytic_account_id'] : 0;
            if (isset($post_data['xb_analytic_account_id'])) {
                unset($post_data['xb_analytic_account_id']);
            }

            $res = $this->xb_invoice->save_receipt($post_data, 'in_receipt');
            if (is_array($res) && isset($res['id'])) {
                if ($analytic_id > 0 && function_exists('xb_save_analytic_assignment')) {
                    xb_save_analytic_assignment('vendor_receipt', $res['id'], $analytic_id);
                }
                set_alert('success', 'Purchase receipt saved and posted.');
                redirect(admin_url('xetuu_books/vendor_receipt/' . $res['id']));
            } else {
                set_alert('danger', is_string($res) ? $res : 'Failed to save receipt.');
            }
        }

        $move = $id ? $this->xb_invoice->get($id) : null;
        if ($id && !$move) { show_404(); }

        $data['title']         = $id ? ('Purchase Receipt #' . ($move->name ?? 'Draft')) : 'New Purchase Receipt';
        $data['move']          = $move;
        $data['invoice_lines'] = $id ? $this->xb_invoice->get_lines($id) : [];
        $data['journal_items'] = ($id && $move && $move->state == 'posted') ? $this->xb_invoice->get_all_lines($id) : [];
        $data['move_type']     = 'in_receipt';
        $data['xb_page']       = 'vendor_receipts';

        $data['payment_journals'] = $this->xb_journal->get_by_type(['bank', 'cash']);
        $data['taxes']            = $this->xb_config->get_taxes('purchase');
        $data['accounts']         = $this->xb_config->get_accounts();

        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }

        $def_acc_setting = $this->xb_config->get_setting('purchase_expense_account');
        $def_acc_id = '';
        if ($def_acc_setting) {
            foreach ($data['accounts'] as $acc) {
                if ($acc->id == $def_acc_setting || $acc->code == $def_acc_setting) {
                    $def_acc_id = $acc->id;
                    break;
                }
            }
        }
        $data['default_purchase_account'] = $def_acc_id;

        $this->db->select("userid, CASE company WHEN '' THEN (SELECT CONCAT(firstname, ' ', lastname) FROM " . db_prefix() . "pur_contacts WHERE " . db_prefix() . "pur_contacts.userid = " . db_prefix() . "pur_vendor.userid AND is_primary = 1) ELSE company END as company");
        $this->db->order_by('company', 'asc');
        $data['vendors'] = $this->db->get(db_prefix() . 'pur_vendor')->result();
        
        $this->db->where('type', 'bank')->or_where('type', 'cash');
        $data['payment_journals'] = $this->db->get('acc_journals')->result();

        $data['xb_stat_draft_to_sent'] = 0;
        $data['xb_stat_sent_to_paid']  = 0;
        $data['xb_stat_overdue_rate']  = 0;
        $data['xb_stat_avg_days']      = 0;
        $data['xb_recent_payments']    = [];

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/receipts/vendor_form', $data, true),
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CUSTOMER INVOICES
    // ─────────────────────────────────────────────────────────────────────────
    public function invoices()
    {
        if (staff_cant('view', 'invoices') && staff_cant('view_own', 'invoices')) {
            access_denied('xetuu_books');
        }

        $this->load->model('invoices_model');

        $this->db->select(
            db_prefix().'invoices.id, '.
            db_prefix().'invoices.number, '.
            db_prefix().'invoices.prefix, '.
            db_prefix().'invoices.number_format, '.
            db_prefix().'invoices.status, '.
            db_prefix().'invoices.date, '.
            db_prefix().'invoices.duedate, '.
            db_prefix().'invoices.total, '.
            db_prefix().'invoices.subtotal, '.
            db_prefix().'invoices.total_tax, '.
            db_prefix().'invoices.clientid, '.
            db_prefix().'clients.company AS client_name, '.
            db_prefix().'currencies.name AS currency_name, '.
            db_prefix().'currencies.symbol AS currency_symbol'
        );
        $this->db->from(db_prefix().'invoices');
        $this->db->join(db_prefix().'clients', db_prefix().'clients.userid = '.db_prefix().'invoices.clientid', 'left');
        $this->db->join(db_prefix().'currencies', db_prefix().'currencies.id = '.db_prefix().'invoices.currency', 'left');
        $this->db->order_by(db_prefix().'invoices.number', 'desc');
        $data['invoices'] = $this->db->get()->result_array();

        $data['title']   = 'Customer Invoices';
        $data['xb_page'] = 'invoices';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/invoices/list', $data, true),
        ]));
    }

    public function invoice_form($id = null)
    {
        if (staff_cant('view', 'invoices') && staff_cant('view_own', 'invoices')) {
            access_denied('xetuu_books');
        }

        $this->load->model('invoices_model');
        $this->load->model('invoice_items_model');
        $this->load->model('currencies_model');
        $this->load->model('taxes_model');
        $this->load->model('payment_modes_model');
        $this->load->model('staff_model');

        // ── Handle POST (save / update) ────────────────────────────────────────
        if ($this->input->post()) {
            $invoice_data = $this->input->post();
            // Handled separately by xb_save_analytic_after_invoice hook; not a tblinvoices column
            unset($invoice_data['xb_analytic_account_id']);

            if (!$id) {
                if (staff_cant('create', 'invoices')) { access_denied('xetuu_books'); }

                if (hooks()->apply_filters('validate_invoice_number', true)) {
                    $number = ltrim($invoice_data['number'] ?? '', '0');
                    if (total_rows('invoices', [
                        'YEAR(date)' => (int) date('Y', strtotime(to_sql_date($invoice_data['date']))),
                        'number'     => $number,
                        'status !='  => Invoices_model::STATUS_DRAFT,
                    ])) {
                        set_alert('warning', _l('invoice_number_exists'));
                        redirect(admin_url('xetuu_books/invoice_form'));
                    }
                }

                $id = $this->invoices_model->add($invoice_data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('invoice')));
                    $send_later = isset($invoice_data['save_and_send_later']);
                    if (isset($invoice_data['save_and_record_payment'])) {
                        $this->session->set_userdata('record_payment', true);
                    } elseif ($send_later) {
                        $this->session->set_userdata('send_later', true);
                    }
                    redirect($send_later ? admin_url('xetuu_books/invoices') : admin_url('xetuu_books/invoice_form/' . $id));
                }
            } else {
                if (staff_cant('edit', 'invoices')) { access_denied('xetuu_books'); }

                if (hooks()->apply_filters('validate_invoice_number', true) && isset($invoice_data['number'])) {
                    $number = trim(ltrim($invoice_data['number'], '0'));
                    if (total_rows('invoices', [
                        'YEAR(date)' => (int) date('Y', strtotime(to_sql_date($invoice_data['date']))),
                        'number'     => $number,
                        'status !='  => Invoices_model::STATUS_DRAFT,
                        'id !='      => $id,
                    ])) {
                        set_alert('warning', _l('invoice_number_exists'));
                        redirect(admin_url('xetuu_books/invoice_form/' . $id));
                    }
                }

                $success = $this->invoices_model->update($invoice_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('invoice')));
                }
                redirect(isset($invoice_data['save_and_send_later']) ? admin_url('xetuu_books/invoices') : admin_url('xetuu_books/invoice_form/' . $id));
            }
        }

        // ── Build view data (same structure as Invoices::invoice()) ───────────
        if ($id) {
            $invoice = $this->invoices_model->get($id);
            if (!$invoice || !user_can_view_invoice($id)) {
                blank_page(_l('invoice_not_found'));
            }
            $data['invoice']           = $invoice;
            $data['invoices_to_merge'] = $this->invoices_model->check_for_merge_invoice($invoice->clientid, $invoice->id);
            $data['expenses_to_bill']  = $this->invoices_model->get_expenses_to_bill($invoice->clientid);
            $data['billable_tasks']    = $this->tasks_model->get_billable_tasks(
                $invoice->clientid,
                !empty($invoice->project_id) ? $invoice->project_id : ''
            );
            $data['title']    = _l('edit', _l('invoice')) . ' - ' . format_invoice_number($invoice->id);
        } else {
            $data['invoices_to_merge'] = [];
            $data['expenses_to_bill']  = [];
            $data['billable_tasks']    = [];
            $data['title']             = _l('create_new_invoice');
        }

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }

        $data['payment_modes'] = $this->payment_modes_model->get('', ['expenses_only !=' => 1]);
        $data['taxes']         = $this->taxes_model->get();

        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups']  = $this->invoice_items_model->get_groups();

        $data['currencies']    = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['staff']         = $this->staff_model->get('', ['active' => 1]);

        // --- Analytics Calculation ---
        $total_invoices = total_rows('invoices');
        $draft_invoices = total_rows('invoices', ['status' => 6]); // STATUS_DRAFT
        $non_draft = $total_invoices - $draft_invoices;
        
        $data['xb_stat_draft_to_sent'] = $total_invoices > 0 ? round(($non_draft / $total_invoices) * 100) : 0;
        
        $paid_invoices = total_rows('invoices', 'status IN (2, 3)'); // Paid or partially paid
        $data['xb_stat_sent_to_paid'] = $non_draft > 0 ? round(($paid_invoices / $non_draft) * 100) : 0;
        
        $overdue_invoices = total_rows('invoices', ['status' => 4]); // STATUS_OVERDUE
        $data['xb_stat_overdue_rate'] = $non_draft > 0 ? round(($overdue_invoices / $non_draft) * 100, 1) : 0;
        
        $avg_query = $this->db->query("SELECT AVG(DATEDIFF(p.date, i.date)) as avg_days FROM ".db_prefix()."invoicepaymentrecords p JOIN ".db_prefix()."invoices i ON i.id = p.invoiceid")->row();
        $data['xb_stat_avg_days'] = ($avg_query && $avg_query->avg_days) ? round($avg_query->avg_days) : 0;
        
        $this->db->select('p.id, p.amount, p.date, c.company, i.prefix, i.number');
        $this->db->from(db_prefix().'invoicepaymentrecords p');
        $this->db->join(db_prefix().'invoices i', 'i.id = p.invoiceid');
        $this->db->join(db_prefix().'clients c', 'c.userid = i.clientid', 'left');
        $this->db->order_by('p.id', 'DESC');
        $this->db->limit(5);
        $data['xb_recent_payments'] = $this->db->get()->result_array();
        // -----------------------------

        $data['xb_page']             = 'invoices';
        $data['xb_hide_page_header'] = true;
        $data['bodyclass']           = 'invoice';

        // Load email template variables for the Send to Client modal
        $this->load->model('emails_model');
        $data['template']             = $this->emails_model->get(['slug' => 'invoice-send-to-client', 'language' => 'english']);
        $data['template_name']        = 'invoice-send-to-client';
        $data['template_system_name'] = isset($data['template']->name) ? $data['template']->name : '';
        $data['template_id']          = isset($data['template']->emailtemplateid) ? $data['template']->emailtemplateid : '';
        $data['template_disabled']    = (isset($data['template']->active) && $data['template']->active == 0) ? 1 : 0;

        // Load credit note variables for apply_invoice_credits modal
        if (isset($invoice) && credits_can_be_applied_to_invoice($invoice->status)) {
            $this->load->model('credit_notes_model');
            $data['credits_available'] = $this->credit_notes_model->total_remaining_credits_by_customer($invoice->clientid);
            if ($data['credits_available'] > 0) {
                $data['open_credits'] = $this->credit_notes_model->get_open_credits($invoice->clientid);
            } else {
                $data['open_credits'] = [];
            }
            $customer_currency = $this->clients_model->get_customer_default_currency($invoice->clientid);
            $data['customer_currency'] = $customer_currency ? $this->currencies_model->get($customer_currency) : $this->currencies_model->get_base_currency();
        } else {
            $data['credits_available'] = 0;
            $data['open_credits']      = [];
            $data['customer_currency'] = $this->currencies_model->get_base_currency();
        }

        $data['xb_view']             = 'xetuu_books/invoices/form';
        $this->load->view('xetuu_books/layout/layout', $data);
    }

    public function post_invoice($id)
    {
        if (!has_permission('accounting_transaction', '', 'create')) { access_denied('xetuu_books'); }
        try {
            $this->xb_engine->post_move($id);
            $move = $this->xb_invoice->get($id);
            echo json_encode(['success' => true, 'name' => $move ? $move->name : '']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function cancel_invoice($id)
    {
        if (!has_permission('accounting_transaction', '', 'edit')) { access_denied('xetuu_books'); }
        $this->xb_engine->cancel_move($id);
        set_alert('success', 'Invoice cancelled.');
        redirect(admin_url('xetuu_books/invoice_form/' . $id));
    }

    public function reset_invoice($id)
    {
        if (!has_permission('accounting_transaction', '', 'edit')) { access_denied('xetuu_books'); }
        $this->xb_engine->reset_move_to_draft($id);
        set_alert('success', 'Invoice reset to draft.');
        redirect(admin_url('xetuu_books/invoice_form/' . $id));
    }

    public function delete_invoice($id)
    {
        if (staff_cant('delete', 'invoices')) { access_denied('xetuu_books'); }
        $this->load->model('invoices_model');
        $this->invoices_model->delete($id);
        set_alert('success', _l('deleted', _l('invoice')));
        redirect(admin_url('xetuu_books/invoices'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VENDOR BILLS
    // ─────────────────────────────────────────────────────────────────────────
    public function bills()
    {
        if (!has_permission('accounting_bills', '', 'view')) { access_denied('xetuu_books'); }

        $filters = [
            'state'      => $this->input->get('state'),
            'partner_id' => $this->input->get('partner_id'),
            'date_from'  => $this->input->get('date_from'),
            'date_to'    => $this->input->get('date_to'),
            'search'     => $this->input->get('search'),
        ];

        $data['title']            = 'Vendor Bills';
        $data['moves']            = $this->xb_invoice->get_list('in_invoice', $filters);
        $data['list_totals']      = $this->xb_invoice->get_list_totals('in_invoice', $filters);
        $data['filters']          = $filters;
        $data['payment_journals'] = $this->xb_journal->get_by_type('bank');
        $data['journals']         = $this->xb_journal->get_by_type('purchase');
        $data['xb_page']          = 'bills';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/bills/list', $data, true),
        ]));
    }

    public function bill_form($id = null)
    {
        if (!has_permission('accounting_bills', '', 'view')) { access_denied('xetuu_books'); }

        if ($this->input->post()) {
            $post   = $this->input->post(null, true);
            $result = $this->xb_invoice->save_invoice($post, $id, 'in_invoice');
            if (is_array($result) && isset($result['id'])) {
                set_alert('success', $id ? 'Bill updated.' : 'Bill created.');
                redirect(admin_url('xetuu_books/bill_form/' . $result['id']));
            } else {
                set_alert('danger', is_string($result) ? $result : 'Failed to save bill.');
            }
        }

        $move = $id ? $this->xb_invoice->get($id) : null;
        if ($id && !$move) { show_404(); }

        $data['title']            = $id ? ('Bill #' . ($move->name ?? 'Draft')) : 'New Bill';
        $data['move']             = $move;
        $data['invoice_lines']    = $id ? $this->xb_invoice->get_lines($id) : [];
        $data['journal_items']    = ($id && $move->state == 'posted') ? $this->xb_invoice->get_all_lines($id) : [];
        $data['journals']         = $this->xb_journal->get_by_type('purchase');
        $data['payment_journals'] = $this->xb_journal->get_by_type('bank');
        $data['payment_terms']    = $this->xb_config->get_payment_terms();
        $data['taxes']            = $this->xb_config->get_taxes('purchase');
        $data['accounts']         = $this->xb_config->get_accounts();
        $data['currencies']       = $this->xb_config->get_currencies();
        $data['payments']         = $id ? $this->xb_payment->get_for_move($id) : [];

        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups']  = $this->invoice_items_model->get_groups();
        
        $def_acc_setting = $this->xb_config->get_setting('purchase_expense_account');
        $def_acc_id = '';
        if ($def_acc_setting) {
            foreach ($data['accounts'] as $acc) {
                if ($acc->id == $def_acc_setting || $acc->code == $def_acc_setting) {
                    $def_acc_id = $acc->id;
                    break;
                }
            }
        }
        $data['default_purchase_account'] = $def_acc_id;

        // Analytic plans + accounts for the distribution widget
        $data['analytic_plans']    = $this->xb_analytic->get_all_plans(true);
        $data['analytic_accounts'] = $this->xb_analytic->get_all_accounts_flat();

        $data['xb_page']          = 'bills';

        $this->db->select("userid, CASE company WHEN '' THEN (SELECT CONCAT(firstname, ' ', lastname) FROM " . db_prefix() . "pur_contacts WHERE " . db_prefix() . "pur_contacts.userid = " . db_prefix() . "pur_vendor.userid AND is_primary = 1) ELSE company END as company");
        $this->db->order_by('company', 'asc');
        $data['vendors'] = $this->db->get(db_prefix() . 'pur_vendor')->result();

        // Credit notes applied against this bill
        $data['credit_notes'] = [];
        if ($id) {
            $this->db->select('id, name, date, amount_total, state, payment_state');
            $this->db->where('source_move_id', $id)->where('move_type', 'in_refund');
            $this->db->order_by('date', 'DESC');
            $data['credit_notes'] = $this->db->get('acc_moves')->result();
        }

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/bills/form', $data, true),
        ]));
    }

    public function post_bill($id)
    {
        if (!has_permission('accounting_bills', '', 'create')) { access_denied('xetuu_books'); }
        try {
            $this->xb_engine->post_move($id);
            $move = $this->xb_invoice->get($id);
            echo json_encode(['success' => true, 'name' => $move ? $move->name : '']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete_bill($id)
    {
        if (!has_permission('accounting_bills', '', 'delete')) { access_denied('xetuu_books'); }
        $this->xb_invoice->delete($id);
        set_alert('success', 'Bill deleted.');
        redirect(admin_url('xetuu_books/bills'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CREDIT NOTES (Customer)
    // ─────────────────────────────────────────────────────────────────────────
    public function credit_notes($id = '')
    {
        if (staff_cant('view', 'credit_notes') && staff_cant('view_own', 'credit_notes')) {
            access_denied('xetuu_books');
        }

        $this->load->model('credit_notes_model');

        // Analytics: aggregate from tblcreditnotes
        $cn_stats = $this->db->query("
            SELECT
                COUNT(*) as total,
                COALESCE(SUM(total), 0) as total_amount,
                COALESCE(SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END), 0) as open_count,
                COALESCE(SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END), 0) as closed_count,
                COALESCE(SUM(CASE WHEN status = 1 THEN total ELSE 0 END), 0) as open_amount,
                COALESCE(SUM(CASE WHEN status = 2 THEN total ELSE 0 END), 0) as closed_amount
            FROM " . db_prefix() . "creditnotes
        ")->row();

        $data['years']          = $this->credit_notes_model->get_credits_years();
        $data['statuses']       = $this->credit_notes_model->get_statuses();
        $data['credit_note_id'] = $id;
        $data['title']          = _l('credit_notes');
        $data['table']          = App_table::find('credit_notes');
        $data['xb_page']        = 'credit_notes';
        $data['cn_stats']       = $cn_stats;

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/credit_notes/list', $data, true),
        ]));
    }

    public function credit_note_form($id = null)
    {
        if (staff_cant('view', 'credit_notes') && staff_cant('view_own', 'credit_notes')) {
            access_denied('xetuu_books');
        }
        $this->load->model('credit_notes_model');

        if ($this->input->post()) {
            $credit_note_data = $this->input->post();
            $analytic_id = isset($credit_note_data['xb_analytic_account_id']) ? (int)$credit_note_data['xb_analytic_account_id'] : 0;
            if (isset($credit_note_data['xb_analytic_account_id'])) {
                unset($credit_note_data['xb_analytic_account_id']);
            }
            if ($id == '') {
                if (staff_cant('create', 'credit_notes')) {
                    access_denied('xetuu_books');
                }
                $id = $this->credit_notes_model->add($credit_note_data);
                if ($id) {
                    if ($analytic_id > 0 && function_exists('xb_save_analytic_assignment')) {
                        xb_save_analytic_assignment('xb_credit_note', $id, $analytic_id);
                    }
                    set_alert('success', _l('added_successfully', _l('credit_note')));
                    redirect(admin_url('xetuu_books/credit_note_form/' . $id));
                }
            } else {
                if (staff_cant('edit', 'credit_notes')) {
                    access_denied('xetuu_books');
                }
                $success = $this->credit_notes_model->update($credit_note_data, $id);
                if ($success) {
                    if ($analytic_id > 0 && function_exists('xb_save_analytic_assignment')) {
                        xb_save_analytic_assignment('xb_credit_note', $id, $analytic_id);
                    }
                    set_alert('success', _l('updated_successfully', _l('credit_note')));
                }
                redirect(admin_url('xetuu_books/credit_note_form/' . $id));
            }
        }
        
        if ($id == '') {
            $title = _l('add_new', _l('credit_note'));
        } else {
            $credit_note = $this->credit_notes_model->get($id);

            if (!$credit_note || (staff_cant('view', 'credit_notes') && $credit_note->addedfrom != get_staff_user_id())) {
                blank_page(_l('credit_note_not_found'), 'danger');
            }

            $data['credit_note'] = $credit_note;
            $data['available_creditable_invoices'] = $this->credit_notes_model->get_available_creditable_invoices($id);
            $data['edit']        = true;
            $title               = _l('edit', _l('credit_note')) . ' - ' . format_credit_note_number($credit_note->id);
        }

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }

        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $this->load->model('invoice_items_model');

        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }

        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $data['title']     = $title;
        $data['bodyclass'] = 'credit-note';
        $data['xb_page']   = 'credit_notes';
        $data['xb_hide_page_header'] = true;

        // --- Analytics Calculation for Credit Notes ---
        $total_cn = total_rows('creditnotes');
        $open_cn = total_rows('creditnotes', ['status' => 1]); // 1 = Open
        $closed_cn = total_rows('creditnotes', ['status' => 2]); // 2 = Closed
        $void_cn = total_rows('creditnotes', ['status' => 3]); // 3 = Void

        $non_void = $total_cn - $void_cn;
        
        $data['xb_stat_draft_to_sent'] = $total_cn > 0 ? round((($open_cn + $closed_cn) / $total_cn) * 100) : 0;
        $data['xb_stat_sent_to_paid'] = $non_void > 0 ? round(($closed_cn / $non_void) * 100) : 0;
        $data['xb_stat_overdue_rate'] = $total_cn > 0 ? round(($void_cn / $total_cn) * 100, 1) : 0;
        
        $data['xb_stat_avg_days'] = 0; // Credit notes don't usually have "days to pay", we'll just show 0 or hide it

        // We will fetch recent refunds or applied credits instead of payments
        $this->db->select('cr.id, cr.amount, cr.refunded_on as date, c.company, "" as prefix, "" as number');
        $this->db->from(db_prefix().'creditnote_refunds cr');
        $this->db->join(db_prefix().'creditnotes cn', 'cn.id = cr.credit_note_id');
        $this->db->join(db_prefix().'clients c', 'c.userid = cn.clientid', 'left');
        $this->db->order_by('cr.id', 'DESC');
        $this->db->limit(5);
        $data['xb_recent_payments'] = $this->db->get()->result_array();

        // Template disabled flag for email modal
        $this->load->model('emails_model');
        $data['template']             = $this->emails_model->get(['slug' => 'credit-note-send-to-client', 'language' => 'english']);
        $data['template_name']        = 'credit-note-send-to-client';
        $data['template_system_name'] = isset($data['template']->name) ? $data['template']->name : '';
        $data['template_id']          = isset($data['template']->emailtemplateid) ? $data['template']->emailtemplateid : '';
        $data['template_disabled']    = (isset($data['template']->active) && $data['template']->active == 0) ? 1 : 0;
        // -----------------------------

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/credit_notes/form', $data, true),
        ]));
    }

    public function post_credit_note($id)
    {
        if (!has_permission('accounting_transaction', '', 'create')) { access_denied('xetuu_books'); }
        try {
            $this->xb_engine->post_move($id);
            $move = $this->xb_invoice->get($id);
            echo json_encode(['success' => true, 'name' => $move ? $move->name : '']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete_credit_note($id)
    {
        if (!has_permission('accounting_transaction', '', 'delete')) { access_denied('xetuu_books'); }
        $this->xb_invoice->delete($id);
        set_alert('success', 'Credit note deleted.');
        redirect(admin_url('xetuu_books/credit_notes'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VENDOR REFUNDS
    // ─────────────────────────────────────────────────────────────────────────
    public function refunds()
    {
        if (!has_permission('accounting_bills', '', 'view')) { access_denied('xetuu_books'); }

        $filters = [
            'state'     => $this->input->get('state'),
            'date_from' => $this->input->get('date_from'),
            'date_to'   => $this->input->get('date_to'),
            'search'    => $this->input->get('search'),
        ];

        $data['title']       = 'Vendor Credit Notes';
        $data['moves']       = $this->xb_invoice->get_list('in_refund', $filters);
        $data['list_totals'] = $this->xb_invoice->get_list_totals('in_refund', $filters);
        $data['filters']     = $filters;
        $data['xb_page']     = 'refunds';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/refunds/list', $data, true),
        ]));
    }

    public function refund_form($id = null)
    {
        if (!has_permission('accounting_bills', '', 'view')) { access_denied('xetuu_books'); }

        if ($this->input->post()) {
            $post   = $this->input->post(null, true);
            $result = $this->xb_invoice->save_invoice($post, $id, 'in_refund');
            if (is_array($result) && isset($result['id'])) {
                set_alert('success', $id ? 'Vendor refund updated.' : 'Vendor refund created.');
                redirect(admin_url('xetuu_books/refund_form/' . $result['id']));
            } else {
                set_alert('danger', is_string($result) ? $result : 'Failed to save refund.');
            }
        }

        $move = $id ? $this->xb_invoice->get($id) : null;
        if ($id && !$move) { show_404(); }

        $data['title']            = $id ? ('Credit Note #' . ($move->name ?? 'Draft')) : 'New Vendor Credit Note';
        $data['move']             = $move;
        $data['invoice_lines']    = $id ? $this->xb_invoice->get_lines($id) : [];
        $data['journal_items']    = ($id && isset($move) && $move->state == 'posted') ? $this->xb_invoice->get_all_lines($id) : [];
        $data['journals']         = $this->xb_journal->get_by_type('purchase');
        $data['payment_journals'] = $this->xb_journal->get_by_type('bank');
        $data['payment_terms']    = $this->xb_config->get_payment_terms();
        $data['taxes']            = $this->xb_config->get_taxes('purchase');
        $data['accounts']         = $this->xb_config->get_accounts();
        $data['currencies']       = $this->xb_config->get_currencies();
        $data['payments']         = $id ? $this->xb_payment->get_for_move($id) : [];
        $data['doc_type']         = 'refund';
        $data['xb_page']          = 'refunds';

        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $def_acc_setting = $this->xb_config->get_setting('purchase_expense_account');
        $def_acc_id = '';
        if ($def_acc_setting) {
            foreach ($data['accounts'] as $acc) {
                if ($acc->id == $def_acc_setting || $acc->code == $def_acc_setting) {
                    $def_acc_id = $acc->id;
                    break;
                }
            }
        }
        $data['default_purchase_account'] = $def_acc_id;

        $this->db->select("userid, CASE company WHEN '' THEN (SELECT CONCAT(firstname, ' ', lastname) FROM " . db_prefix() . "pur_contacts WHERE " . db_prefix() . "pur_contacts.userid = " . db_prefix() . "pur_vendor.userid AND is_primary = 1) ELSE company END as company");
        $this->db->order_by('company', 'asc');
        $data['vendors'] = $this->db->get(db_prefix() . 'pur_vendor')->result();

        // Bills for the already-linked source bill vendor (used to pre-populate dropdown on load)
        $source_bill = null;
        if ($move && !empty($move->source_move_id)) {
            $source_bill = $this->xb_invoice->get($move->source_move_id);
        }
        $data['source_bill'] = $source_bill;

        // Bills for the selected vendor (all confirmed+unpaid bills for the dropown)
        $vendor_id_for_bills = $move ? $move->partner_id : null;
        $data['vendor_bills'] = [];
        if ($vendor_id_for_bills) {
            $this->db->select('id, name, date, amount_total, amount_residual, payment_state');
            $this->db->where('move_type', 'in_invoice');
            $this->db->where('state', 'posted');
            $this->db->where('partner_id', (int)$vendor_id_for_bills);
            $this->db->where('payment_state !=', 'paid');
            $this->db->order_by('date', 'DESC');
            $data['vendor_bills'] = $this->db->get('acc_moves')->result();
        }

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/refunds/form', $data, true),
        ]));
    }

    // AJAX: return confirmed unpaid bills for a vendor (used by credit note form)
    public function get_vendor_bills($vendor_id = 0)
    {
        if (!has_permission('accounting_bills', '', 'view')) { show_404(); }
        $this->db->select('id, name, date, amount_total, amount_residual, payment_state');
        $this->db->where('move_type', 'in_invoice');
        $this->db->where('state', 'posted');
        $this->db->where('partner_id', (int)$vendor_id);
        $this->db->where('payment_state !=', 'paid');
        $this->db->order_by('date', 'DESC');
        $bills = $this->db->get('acc_moves')->result();
        echo json_encode(['success' => true, 'data' => $bills]);
    }

    // AJAX: return product lines for a bill (used to auto-fill credit note lines)
    public function get_bill_lines_ajax($bill_id = 0)
    {
        if (!has_permission('accounting_bills', '', 'view')) { show_404(); }
        $lines = $this->xb_invoice->get_lines((int)$bill_id);
        $bill  = $this->xb_invoice->get((int)$bill_id);
        echo json_encode(['success' => true, 'lines' => $lines, 'bill' => $bill]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EMPLOYEE EXPENSES
    // ─────────────────────────────────────────────────────────────────────────
    public function expenses()
    {
        if (!has_permission('accounting_bills', '', 'view')) { access_denied('xetuu_books'); }

        $filters = [
            'state'     => $this->input->get('state'),
            'date_from' => $this->input->get('date_from'),
            'date_to'   => $this->input->get('date_to'),
            'search'    => $this->input->get('search'),
        ];

        $data['title']       = 'Employee Expenses';
        $data['moves']       = $this->xb_invoice->get_list('in_invoice', array_merge($filters, ['journal_type' => 'General']));
        $data['list_totals'] = $this->xb_invoice->get_list_totals('in_invoice', $filters);
        $data['filters']     = $filters;
        $data['xb_page']     = 'expenses';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/expenses/list', $data, true),
        ]));
    }

    public function expense_form($id = null)
    {
        if (!has_permission('accounting_bills', '', 'view')) { access_denied('xetuu_books'); }

        if ($this->input->post()) {
            $post   = $this->input->post(null, true);
            $result = $this->xb_invoice->save_invoice($post, $id, 'in_invoice');
            if (is_array($result) && isset($result['id'])) {
                set_alert('success', $id ? 'Expense updated.' : 'Expense created.');
                redirect(admin_url('xetuu_books/expense_form/' . $result['id']));
            } else {
                set_alert('danger', is_string($result) ? $result : 'Failed to save expense.');
            }
        }

        $move = $id ? $this->xb_invoice->get($id) : null;
        if ($id && !$move) { show_404(); }

        $data['title']            = $id ? ('Expense #' . ($move->name ?? 'Draft')) : 'New Expense';
        $data['move']             = $move;
        $data['invoice_lines']    = $id ? $this->xb_invoice->get_lines($id) : [];
        $data['journals']         = $this->xb_journal->get_all();
        $data['payment_journals'] = $this->xb_journal->get_by_type('bank');
        $data['payment_terms']    = $this->xb_config->get_payment_terms();
        $data['taxes']            = $this->xb_config->get_taxes('purchase');
        $data['accounts']         = $this->xb_config->get_accounts();
        $data['currencies']       = $this->xb_config->get_currencies();
        $data['payments']         = $id ? $this->xb_payment->get_for_move($id) : [];
        $data['expense_accounts'] = $this->xb_config->get_accounts(['type' => 'Expense']);
        $data['xb_page']          = 'expenses';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/expenses/form', $data, true),
        ]));
    }

    public function post_expense($id)
    {
        if (!has_permission('accounting_bills', '', 'create')) { access_denied('xetuu_books'); }
        try {
            $this->xb_engine->post_move($id);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete_expense($id)
    {
        if (!has_permission('accounting_bills', '', 'delete')) { access_denied('xetuu_books'); }
        $this->xb_invoice->delete($id);
        set_alert('success', 'Expense deleted.');
        redirect(admin_url('xetuu_books/expenses'));
    }

    public function xb_copy_invoice($id)
    {
        if (staff_cant('create', 'invoices')) {
            access_denied('xetuu_books');
        }
        if (!$id) {
            redirect(admin_url('xetuu_books/invoices'));
        }
        $new_id = $this->invoices_model->copy($id);
        if ($new_id) {
            set_alert('success', _l('invoice_copied_successfully'));
            redirect(admin_url('xetuu_books/invoice_form/' . $new_id));
        }
        set_alert('danger', _l('invoice_copy_fail'));
        redirect(admin_url('xetuu_books/invoice_form/' . $id));
    }

    public function xb_delete_invoice($id)
    {
        if (staff_cant('delete', 'invoices')) {
            access_denied('xetuu_books');
        }
        if (!$id) {
            redirect(admin_url('xetuu_books/invoices'));
        }
        $success = $this->invoices_model->delete($id);
        if ($success) {
            set_alert('success', _l('deleted', _l('invoice')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('invoice_lowercase')));
        }
        redirect(admin_url('xetuu_books/invoices'));
    }
    public function xb_payment($id = '')
    {
        if (staff_cant('view', 'payments')
            && staff_cant('view_own', 'invoices')
            && get_option('allow_staff_view_invoices_assigned') == '0') {
            access_denied('xetuu_books');
        }

        if (!$id) {
            redirect(admin_url('xetuu_books/invoices'));
        }

        if ($this->input->post()) {
            if (staff_cant('edit', 'payments')) {
                access_denied('Update Payment');
            }
            $success = $this->payments_model->update($this->input->post(), $id);
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('payment')));
            }
            redirect(admin_url('xetuu_books/xb_payment/' . $id));
        }

        $this->load->model('payments_model');
        $payment = $this->payments_model->get($id);

        if (!$payment) {
            show_404();
        }

        $this->load->model('invoices_model');
        $payment->invoice = $this->invoices_model->get($payment->invoiceid);
        $template_name    = 'invoice_payment_recorded_to_customer';

        $data = prepare_mail_preview_data($template_name, $payment->invoice->clientid);

        $data['payment'] = $payment;
        $this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [], true, true);

        $i = 0;
        foreach ($data['payment_modes'] as $mode) {
            if ($mode['active'] == 0 && $data['payment']->paymentmode != $mode['id']) {
                unset($data['payment_modes'][$i]);
            }
            $i++;
        }

        $data['title'] = _l('payment_receipt') . ' - ' . format_invoice_number($payment->invoiceid);
        
        $data['xb_page'] = 'invoices';
        $data['xb_view'] = 'xetuu_books/payments/payment';
        
        $this->load->view('xetuu_books/layout/layout', $data);
    }
}
