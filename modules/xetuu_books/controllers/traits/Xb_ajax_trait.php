<?php
defined('BASEPATH') or exit('No direct script access allowed');

trait Xb_ajax_trait
{
    public function ajax($action = '')
    {
        if (!$this->input->is_ajax_request() && !in_array($action, ['report_data'])) {
            show_404();
        }
        header('Content-Type: application/json');

        switch ($action) {
            case 'search_accounts':
                echo json_encode($this->xb_config->search_accounts($this->input->get('q')));
                break;

            case 'search_partners':
                echo json_encode($this->xb_config->search_partners(
                    $this->input->get('q'),
                    $this->input->get('type')
                ));
                break;

            case 'get_account_balance':
                echo json_encode($this->xb_engine->get_account_balance(
                    (int)$this->input->get('id'),
                    $this->input->get('date_from'),
                    $this->input->get('date_to')
                ));
                break;

            case 'compute_totals':
                echo json_encode($this->xb_invoice->compute_totals($this->input->post(null, true)));
                break;

            case 'get_tax_details':
                echo json_encode($this->xb_config->get_taxes_by_ids(
                    explode(',', $this->input->get('ids'))
                ));
                break;

            case 'get_payment_terms':
                echo json_encode($this->xb_engine->compute_payment_terms(
                    (float)$this->input->get('amount'),
                    (int)$this->input->get('id'),
                    $this->input->get('date')
                ));
                break;

            case 'save_account':
                $post   = $this->input->post(null, true);
                $id     = (int)($post['id'] ?? 0);
                $result = $this->xb_config->save_account($post, $id ?: null);
                echo json_encode(['success' => (bool)$result, 'id' => $result]);
                break;

            case 'delete_account':
                echo json_encode(['success' => $this->xb_config->delete_account(
                    (int)$this->input->post('id')
                )]);
                break;

            case 'seed_default_coa':
                $inserted = $this->xb_config->seed_default_coa();
                echo json_encode(['success' => true, 'inserted' => $inserted]);
                break;

            case 'generate_coa':
                try {
                    $inserted = $this->xb_config->generate_coa($this->input->post('template', true) ?: 'standard');
                    echo json_encode(['success' => true, 'inserted' => $inserted]);
                } catch (\Exception $e) {
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                }
                break;

            case 'get_account_ledger':
                $account_id = (int)$this->input->get('account_id');
                echo json_encode($account_id ? $this->xb_config->get_account_ledger($account_id) : []);
                break;

            case 'delete_legacy_flat_accounts':
                $deleted = $this->xb_config->delete_legacy_flat_accounts();
                echo json_encode(['success' => true, 'deleted' => $deleted]);
                break;

            case 'save_tax':
                $post   = $this->input->post(null, true);
                $id     = (int)($post['id'] ?? 0);
                $result = $this->xb_config->save_tax($post, $id ?: null);
                echo json_encode(['success' => (bool)$result, 'id' => $result]);
                break;

            case 'delete_tax':
                echo json_encode(['success' => $this->xb_config->delete_tax(
                    (int)$this->input->post('id')
                )]);
                break;

            case 'save_journal':
                $post   = $this->input->post(null, true);
                $id     = (int)($post['id'] ?? 0);
                $result = $this->xb_journal->save($post, $id ?: null);
                echo json_encode(['success' => (bool)$result, 'id' => $result]);
                break;

            case 'save_setting':
                echo json_encode(['success' => $this->xb_config->set_setting(
                    $this->input->post('key', true),
                    $this->input->post('value', true)
                )]);
                break;

            case 'save_settings':
                echo json_encode(['success' => $this->xb_config->save_settings(
                    $this->input->post(null, true)
                )]);
                break;

            case 'get_move_lines':
                echo json_encode($this->xb_engine->get_move_lines(
                    (int)$this->input->get('move_id')
                ));
                break;

            case 'register_payment':
                $post = $this->input->post(null, true);
                try {
                    $pid = $this->xb_payment->register_payment($post);
                    echo json_encode(['success' => true, 'payment_id' => $pid]);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                }
                break;

            case 'report_data':
                $date_from = $this->input->get('date_from') ?: date('Y-01-01');
                $date_to   = $this->input->get('date_to')   ?: date('Y-12-31');
                echo json_encode($this->xb_report->get_report_data(
                    $this->input->get('report'),
                    compact('date_from', 'date_to')
                ));
                break;

            case 'save_asset_model':
                $post = $this->input->post(null, true);
                $id   = (int)($post['id'] ?? 0);
                $model_data = [
                    'name'          => $post['name'],
                    'method'        => $post['method'] ?? 'linear',
                    'method_number' => (int)($post['method_number'] ?? 5),
                    'method_period' => (int)($post['method_period'] ?? 12),
                    'active'        => 1,
                ];
                if ($id) {
                    $this->db->where('id', $id)->update('acc_asset_models', $model_data);
                } else {
                    $this->db->insert('acc_asset_models', $model_data);
                    $id = $this->db->insert_id();
                }
                echo json_encode(['success' => true, 'id' => $id]);
                break;

            case 'save_followup_level':
                $post = $this->input->post(null, true);
                $id   = (int)($post['id'] ?? 0);
                $level_data = [
                    'name'          => $post['name'],
                    'delay'         => (int)($post['delay'] ?? 30),
                    'sequence'      => (int)($post['sequence'] ?? 10),
                    'description'   => $post['description'] ?? '',
                    'send_email'    => (int)($post['send_email'] ?? 0),
                    'send_letter'   => (int)($post['send_letter'] ?? 0),
                    'block_account' => (int)($post['block_account'] ?? 0),
                ];
                if ($id) {
                    $this->db->where('id', $id)->update('acc_followup_levels', $level_data);
                } else {
                    $this->db->insert('acc_followup_levels', $level_data);
                    $id = $this->db->insert_id();
                }
                echo json_encode(['success' => true, 'id' => $id]);
                break;

            case 'delete_followup_level':
                $id = (int)$this->input->post('id');
                echo json_encode(['success' => $id && $this->db->where('id', $id)->delete('acc_followup_levels')]);
                break;

            case 'save_payment_term':
                $post = $this->input->post(null, true);
                $id   = (int)($post['id'] ?? 0);
                $term_data = [
                    'name'     => $post['name'] ?? '',
                    'note'     => $post['note'] ?? '',
                    'active'   => (int)($post['active'] ?? 1),
                    'sequence' => (int)($post['sequence'] ?? 10),
                ];
                if (!$term_data['name']) {
                    echo json_encode(['success' => false, 'message' => 'Name is required']); break;
                }
                if ($id) {
                    $this->db->where('id', $id)->update('acc_payment_terms', $term_data);
                } else {
                    $this->db->insert('acc_payment_terms', $term_data);
                    $id = $this->db->insert_id();
                }
                // Save lines — delete existing, re-insert
                $this->db->where('payment_term_id', $id)->delete('acc_payment_term_lines');
                $lines = $post['lines'] ?? [];
                foreach ($lines as $line) {
                    $this->db->insert('acc_payment_term_lines', [
                        'payment_term_id' => $id,
                        'value'           => $line['value'] ?? 'balance',
                        'value_amount'    => (float)($line['value_amount'] ?? 0),
                        'delay_type'      => $line['delay_type'] ?? 'days_after',
                        'nb_days'         => (int)($line['nb_days'] ?? 0),
                    ]);
                }
                echo json_encode(['success' => true, 'id' => $id]);
                break;

            case 'delete_payment_term':
                $id = (int)$this->input->post('id');
                if ($id) {
                    $this->db->where('payment_term_id', $id)->delete('acc_payment_term_lines');
                    $this->db->where('id', $id)->delete('acc_payment_terms');
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false]);
                }
                break;

            case 'save_incoterm':
                $post = $this->input->post(null, true);
                $id   = (int)($post['id'] ?? 0);
                $data = [
                    'code'        => strtoupper(trim($post['code'] ?? '')),
                    'name'        => $post['name'] ?? '',
                    'description' => $post['description'] ?? '',
                    'sort_order'  => (int)($post['sort_order'] ?? 10),
                    'active'      => (int)($post['active'] ?? 1),
                ];
                if (!$data['code'] || !$data['name']) {
                    echo json_encode(['success' => false, 'message' => 'Code and Name are required']); break;
                }
                if ($id) {
                    $this->db->where('id', $id)->update('acc_incoterms', $data);
                } else {
                    $this->db->insert('acc_incoterms', $data);
                    $id = $this->db->insert_id();
                }
                echo json_encode(['success' => true, 'id' => $id]);
                break;

            case 'delete_incoterm':
                $id = (int)$this->input->post('id');
                echo json_encode(['success' => $id && $this->db->where('id', $id)->delete('acc_incoterms')]);
                break;

            case 'get_currency_rate':
                $cur_id = (int)$this->input->get('id');
                if ($cur_id) {
                    $cur = $this->db->select('id,name,symbol,rate')->where('id', $cur_id)->get('acc_currencies')->row();
                    echo json_encode($cur ?: ['error' => 'Currency not found']);
                } else {
                    $rows = $this->db->select('id,name,symbol,rate')->get('acc_currencies')->result();
                    echo json_encode($rows);
                }
                break;

            case 'save_currency':
                $cur_id = (int)$this->input->post('id');
                $rate   = (float)$this->input->post('rate');
                if (!$cur_id || $rate <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid id or rate']); break;
                }
                $now = date('Y-m-d H:i:s');
                $this->db->where('id', $cur_id)->update('acc_currencies', [
                    'rate'       => $rate,
                    'updated_at' => $now,
                ]);
                // Sync symbol update to Perfex native tblcurrencies (rate is not stored there, but symbol should match)
                $ac = $this->db->where('id', $cur_id)->get('acc_currencies')->row();
                if ($ac) {
                    $pf = $this->db->where('name', strtoupper($ac->name))->get(db_prefix() . 'currencies')->row();
                    if (!$pf) {
                        // Currency exists in acc_currencies but not in tblcurrencies — add it
                        $this->db->insert(db_prefix() . 'currencies', [
                            'name'               => strtoupper($ac->name),
                            'symbol'             => $ac->symbol ?? $ac->name,
                            'decimal_separator'  => '.',
                            'thousand_separator' => ',',
                            'placement'          => $ac->position ?? 'before',
                            'isdefault'          => 0,
                        ]);
                    }
                    // Note: tblcurrencies does not store exchange rates, so no rate sync needed there.
                }
                echo json_encode(['success' => true, 'updated_at' => $now]);
                break;

            case 'fetch_exchange_rates':
                if (!has_permission('accounting', '', 'edit')) {
                    echo json_encode(['success' => false, 'message' => 'Insufficient permissions.']); break;
                }
                $result = $this->xb_config->fetch_exchange_rates();
                echo json_encode(array_merge(['success' => $result['error'] === null], $result));
                break;

            case 'seed_world_currencies':
                if (!has_permission('accounting', '', 'edit')) {
                    echo json_encode(['success' => false, 'message' => 'Insufficient permissions.']); break;
                }
                $result = $this->xb_config->seed_world_currencies();
                echo json_encode(['success' => true, 'acc_added' => $result['acc_added'], 'perfex_added' => $result['perfex_added'],
                    'message' => 'Added ' . $result['acc_added'] . ' currencies to Xetuu Books and ' . $result['perfex_added'] . ' to Perfex. Refresh to see all.']);
                break;

            case 'sync_from_perfex':
                if (!has_permission('accounting', '', 'edit')) {
                    echo json_encode(['success' => false, 'message' => 'Insufficient permissions.']); break;
                }
                $r1 = $this->xb_config->sync_from_perfex_currencies();
                $r2 = $this->xb_config->sync_to_perfex_currencies();
                echo json_encode(['success' => true,
                    'acc_added' => $r1['added'], 'acc_updated' => $r1['updated'],
                    'perfex_added' => $r2['added'],
                    'message' => 'Sync complete. Xetuu Books: +' . $r1['added'] . ' added, ' . $r1['updated'] . ' updated. Perfex: +' . $r2['added'] . ' added.']);
                break;

            case 'get_customers':
                $q = trim($this->input->get('q') ?? '');
                $where = db_prefix() . 'clients.active = 1';
                if ($q) {
                    $esc = $this->db->escape_like_str($q);
                    $where .= ' AND (company LIKE "%' . $esc . '%" ESCAPE \'!\' OR CONCAT(firstname," ",lastname) LIKE "%' . $esc . '%" ESCAPE \'!\' OR email LIKE "%' . $esc . '%" ESCAPE \'!\')';
                }
                $rows = $this->db->select('userid AS id, IFNULL(NULLIF(company,""), CONCAT(firstname," ",lastname)) AS name, email')
                    ->from(db_prefix() . 'clients')
                    ->join(db_prefix() . 'contacts', db_prefix() . 'contacts.userid = ' . db_prefix() . 'clients.userid AND ' . db_prefix() . 'contacts.is_primary = 1', 'left')
                    ->where($where)
                    ->order_by('name', 'asc')
                    ->get()->result_array();
                echo json_encode($rows);
                break;

            case 'quick_create_customer':
                if (!$this->input->post()) { http_response_code(400); echo json_encode(['success' => false]); break; }
                $company = strip_tags(trim($this->input->post('company', true)));
                if (!$company) { echo json_encode(['success' => false, 'error' => 'Name required']); break; }
                $this->load->model('clients_model');
                $id = $this->clients_model->add(['company' => $company, 'active' => 1]);
                if ($id) {
                    echo json_encode(['success' => true, 'id' => $id, 'name' => $company]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to create customer']);
                }
                break;

            case 'save_reconcil_model':
                if (!has_permission('accounting_setting', '', 'edit')) { http_response_code(403); echo json_encode(['success' => false]); break; }
                $post = $this->input->post(null, true);
                $id   = isset($post['id']) ? (int)$post['id'] : 0;
                $row  = [
                    'name'             => trim($post['name'] ?? ''),
                    'rule_type'        => $post['rule_type'] ?? 'writeoff_button',
                    'sequence'         => (int)($post['sequence'] ?? 10),
                    'active'           => isset($post['active']) ? 1 : 0,
                    'match_nature'     => $post['match_nature'] ?? 'both',
                    'match_amount_type'=> $post['match_amount_type'] ?? 'any',
                    'match_amount_min' => strlen($post['match_amount_min'] ?? '') ? (float)$post['match_amount_min'] : null,
                    'match_amount_max' => strlen($post['match_amount_max'] ?? '') ? (float)$post['match_amount_max'] : null,
                    'match_label_type' => $post['match_label_type'] ?? 'any',
                    'match_label_param'=> trim($post['match_label_param'] ?? '') ?: null,
                    'account_id'       => ($post['account_id'] ?? '') ? (int)$post['account_id'] : null,
                    'journal_id'       => ($post['journal_id'] ?? '') ? (int)$post['journal_id'] : null,
                    'writeoff_label'   => trim($post['writeoff_label'] ?? '') ?: null,
                ];
                if (!$row['name']) { echo json_encode(['success' => false, 'message' => 'Name is required']); break; }
                if ($id) {
                    $this->db->where('id', $id)->update('acc_reconcil_models', $row);
                } else {
                    $this->db->insert('acc_reconcil_models', $row);
                    $id = $this->db->insert_id();
                }
                echo json_encode(['success' => true, 'id' => $id]);
                break;

            case 'delete_reconcil_model':
                if (!has_permission('accounting_setting', '', 'delete')) { http_response_code(403); echo json_encode(['success' => false]); break; }
                $id = (int)($this->input->post('id') ?? 0);
                if ($id) { $this->db->where('id', $id)->delete('acc_reconcil_models'); }
                echo json_encode(['success' => true]);
                break;

            case 'toggle_reconcil_model':
                if (!has_permission('accounting_setting', '', 'edit')) { http_response_code(403); echo json_encode(['success' => false]); break; }
                $id  = (int)($this->input->post('id') ?? 0);
                $row = $id ? $this->db->where('id', $id)->get('acc_reconcil_models')->row() : null;
                if ($row) {
                    $this->db->where('id', $id)->update('acc_reconcil_models', ['active' => $row->active ? 0 : 1]);
                    echo json_encode(['success' => true, 'active' => !$row->active]);
                } else {
                    echo json_encode(['success' => false]);
                }
                break;

            default:
                http_response_code(404);
                echo json_encode(['error' => 'Unknown action']);
        }
    }
}
