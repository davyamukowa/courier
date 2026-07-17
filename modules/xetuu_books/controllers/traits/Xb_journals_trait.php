<?php
defined('BASEPATH') or exit('No direct script access allowed');

trait Xb_journals_trait
{
    public function journal_entries()
    {
        if (!has_permission('accounting_journal_entry', '', 'view')) { access_denied('xetuu_books'); }

        $filters = [
            'journal_id' => $this->input->get('journal_id'),
            'state'      => $this->input->get('state'),
            'date_from'  => $this->input->get('date_from'),
            'date_to'    => $this->input->get('date_to'),
            'search'     => $this->input->get('search'),
        ];

        $data['title']    = 'Journal Entries';
        $data['entries']  = $this->xb_engine->get_moves_list(array_merge($filters, ['move_type' => 'entry']));
        $data['journals'] = $this->xb_journal->get_all();
        $data['filters']  = $filters;
        $data['xb_page']  = 'journal_entries';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/journals/list', $data, true),
        ]));
    }

    public function journal_entry($id)
    {
        if (!has_permission('accounting_journal_entry', '', 'view')) { access_denied('xetuu_books'); }

        $move = $this->xb_engine->get_move((int)$id);
        if (!$move) { show_404(); }

        $lines = $this->xb_engine->get_move_lines((int)$id);

        // Enrich each line with account code/name and partner name
        foreach ($lines as &$line) {
            $acc = $this->db->select('id,code,name')->where('id', $line->account_id)->get(db_prefix() . 'acc_accounts')->row();
            $line->account_code = $acc ? $acc->code : '';
            $line->account_name = $acc ? $acc->name : '';
            if (!empty($line->partner_id)) {
                $c = $this->db->select('company')->where('userid', $line->partner_id)->get('tblclients')->row();
                $line->partner_name = $c ? $c->company : '';
            } else {
                $line->partner_name = '';
            }
        }
        unset($line);

        $data['title']       = ($move->name ?? '#' . $id) . ' — Journal Entry';
        $data['entry']       = $move;
        $data['entry_lines'] = $lines;
        $data['xb_page']     = 'journal_entries';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/journals/view', $data, true),
        ]));
    }

    public function journal_entry_form($id = null)
    {
        if (!has_permission('accounting_journal_entry', '', 'view')) { access_denied('xetuu_books'); }

        if ($this->input->post()) {
            $post   = $this->input->post(null, true);
            $result = $this->xb_engine->save_entry($post, $id);
            if (is_array($result) && isset($result['id'])) {
                set_alert('success', 'Journal entry saved.');
                redirect(admin_url('xetuu_books/journal_entry_form/' . $result['id']));
            } else {
                set_alert('danger', is_string($result) ? $result : 'Failed to save entry.');
            }
        }

        $move = $id ? $this->xb_engine->get_move($id) : null;

        $data['title']          = $id ? 'Journal Entry' : 'New Journal Entry';
        $data['entry']          = $move;
        $data['entry_lines']    = $id ? $this->xb_engine->get_move_lines($id) : [];
        $data['all_journals']   = $this->xb_journal->get_all();
        $data['accounts']       = $this->xb_config->get_accounts();
        $data['currencies']     = $this->xb_config->get_currencies();
        $data['recent_entries'] = $this->xb_engine->get_moves_list(['move_type' => 'entry']);
        $data['xb_page']        = 'journal_entries';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/journals/form', $data, true),
        ]));
    }

    public function post_entry($id)
    {
        if (!has_permission('accounting_journal_entry', '', 'create')) { access_denied('xetuu_books'); }
        try {
            $this->xb_engine->post_move($id);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete_entry($id)
    {
        if (!has_permission('accounting_journal_entry', '', 'delete')) { access_denied('xetuu_books'); }
        $this->xb_engine->delete_move($id);
        set_alert('success', 'Journal entry deleted.');
        redirect(admin_url('xetuu_books/journal_entries'));
    }

    public function reconcile()
    {
        if (!has_permission('accounting_reconcile', '', 'view')) { access_denied('xetuu_books'); }

        $journal_id = (int)$this->input->get('journal_id');
        $journals   = $this->xb_journal->get_by_type(['Bank', 'Cash']);
        if (!$journal_id && !empty($journals)) { $journal_id = $journals[0]->id; }

        $data['title']            = 'Bank Reconciliation';
        $data['bank_journals']    = $journals;
        $data['selected_journal'] = $journal_id;
        $data['statement_lines']  = $journal_id ? $this->xb_engine->get_unreconciled_statement_lines($journal_id) : [];
        $data['unreconciled']     = $journal_id ? $this->xb_engine->get_unreconciled_move_lines($journal_id) : [];
        $data['xb_page']          = 'reconcile';

        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/reconcile/index', $data, true),
        ]));
    }

    public function do_reconcile()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        $post = $this->input->post(null, true);
        try {
            $this->xb_engine->reconcile_lines($post['debit_line_ids'], $post['credit_line_ids']);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function do_bank_reconcile()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }
        $post = $this->input->post(null, true);
        try {
            $this->xb_engine->reconcile_bank_statement($post['statement_line_ids'], $post['ledger_line_ids']);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function import_statement()
    {
        if (!$this->input->post() || !$this->input->is_ajax_request()) { show_404(); }
        $journal_id = (int)$this->input->post('journal_id');
        if (empty($_FILES['statement_file']['tmp_name'])) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded.']); return;
        }
        try {
            $count = $this->xb_engine->import_bank_statement($journal_id, $_FILES['statement_file']);
            echo json_encode(['success' => true, 'imported' => $count]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
