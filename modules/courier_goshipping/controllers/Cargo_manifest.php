<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Cargo manifest — redesigned.
 *
 *   index    filters + live table of matching shipments, "Generate Manifest"
 *   generate POST: snapshots the matching shipments under a new auto number
 *   view     a generated manifest document (print / CSV)
 *   history  every generated manifest the user may see
 *   csv      CSV download of one generated manifest
 *
 * Who sees what (same 3 tiers the module uses everywhere):
 *   super user / view_manifests  -> every country (+ a Country filter)
 *   view_branch_manifests        -> their branches; a COUNTRY ADMIN's branches
 *                                   are their whole country, so they see all
 *                                   manifests / staff / agents of that country
 *   view_own_manifests           -> only shipments they booked themselves
 */
class Cargo_manifest extends AdminController
{
    /** @var Manifest_doc_model */
    private $docs;

    public function __construct()
    {
        parent::__construct();
        if (!is_admin()
            && !has_permission('courier-manifests', '', 'view_manifests')
            && !has_permission('courier-manifests', '', 'view_branch_manifests')
            && !has_permission('courier-manifests', '', 'view_own_manifests')) {
            access_denied('Courier - Manifests');
        }
        $this->load->helper('courier_goshipping/courier');
        $this->load->model('Manifest_doc_model');
        $this->docs = $this->Manifest_doc_model;
    }

    private function can_generate()
    {
        // Own-scope users (e.g. agents) can generate a manifest of their OWN shipments —
        // the row query is already restricted to them in resolve().
        return is_admin() || staff_can('create_manifests', 'courier-manifests') || $this->scope()['level'] === 'own';
    }

    /** ['level' => global|branch|own, 'branch_ids' => null|int[]] for the current user. */
    private function scope()
    {
        $level = is_admin() ? 'global' : courier_resolve_visibility_scope('courier-manifests', 'view_branch_manifests', 'view_manifests');
        return [
            'level'      => $level,
            'branch_ids' => $level === 'global' ? null : (courier_get_staff_branch_ids() ?: [0]),
        ];
    }

    private function in_branches_sql($branch_ids)
    {
        return implode(',', array_map('intval', $branch_ids ?: [0]));
    }

    /** Non-agent staff the current user may filter by. */
    private function allowed_users($branch_ids)
    {
        $p = db_prefix();
        $this->db->select('s.staffid, s.firstname, s.lastname, s.active')->from($p . 'staff s')
            ->where("s.email NOT LIKE '%@riders.invalid'", null, false)
            ->where('s.staffid NOT IN (SELECT staff_id FROM ' . $p . '_agents)', null, false);
        if ($branch_ids !== null) {
            $this->db->where('s.staffid IN (SELECT staff_id FROM ' . $p . '_courier_staff_branches WHERE branch_id IN (' . $this->in_branches_sql($branch_ids) . '))', null, false);
        }
        return $this->db->order_by('s.firstname', 'asc')->get()->result();
    }

    /** Agents the current user may filter by. */
    private function allowed_agents($branch_ids)
    {
        $p = db_prefix();
        $this->db->select('a.id, a.staff_id, a.company_name, s.firstname, s.lastname', false)->from($p . '_agents a')
            ->join($p . 'staff s', 's.staffid = a.staff_id', 'left');
        if ($branch_ids !== null) {
            $in = $this->in_branches_sql($branch_ids);
            $this->db->where('(a.branch_id IN (' . $in . ') OR a.staff_id IN (SELECT staff_id FROM ' . $p . '_courier_staff_branches WHERE branch_id IN (' . $in . ')))', null, false);
        }
        return $this->db->order_by('s.firstname', 'asc')->get()->result();
    }

    private function created_by_me_ids()
    {
        $p = db_prefix();
        if (!$this->db->table_exists($p . '_courier_staff_creators')) {
            return [];
        }
        $rows = $this->db->select('staff_id')->where('created_by', (int) get_staff_user_id())->get($p . '_courier_staff_creators')->result_array();
        return array_map('intval', array_column($rows, 'staff_id'));
    }

    /** Turns request params into validated filters + the dropdown data + the final query restrictions. */
    private function resolve($src)
    {
        $scope = $this->scope();
        $me    = (int) get_staff_user_id();
        $p     = db_prefix();

        $valid_date = function ($d, $fallback) {
            return (is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) ? $d : $fallback;
        };
        $f = [
            'date_from' => $valid_date($src['date_from'] ?? '', date('Y-m-d', strtotime('-90 days'))),
            'date_to'   => $valid_date($src['date_to'] ?? '', date('Y-m-d')),
            'type'      => isset($src['type'], Manifest_doc_model::type_labels()[$src['type']]) ? $src['type'] : '',
            'user'      => (string) ($src['user'] ?? 'all'),
            'agent'     => (string) ($src['agent'] ?? 'none'),
            'country_id' => 0,
        ];

        // Branch scope (+ the super user's optional country narrowing)
        $branch_ids = $scope['branch_ids'];
        $countries  = [];
        if ($scope['level'] === 'global') {
            $countries = $this->db->select('c.country_id, c.short_name')->from($p . '_courier_branches b')
                ->join($p . 'countries c', 'c.country_id = b.country_id')->group_by('c.country_id')
                ->order_by('c.short_name', 'asc')->get()->result();
            $f['country_id'] = (int) ($src['country_id'] ?? 0);
            if ($f['country_id'] > 0) {
                $branch_ids = courier_get_country_branch_ids([$f['country_id']]) ?: [0];
            }
        }

        $users  = $this->allowed_users($branch_ids);
        $agents = $this->allowed_agents($branch_ids);
        $user_ids        = array_map('intval', array_column(array_map(function ($u) { return (array) $u; }, $users), 'staffid'));
        $agent_staff_ids = array_map('intval', array_column(array_map(function ($a) { return (array) $a; }, $agents), 'staff_id'));
        $mine            = $this->created_by_me_ids();

        $staff_ids = null;
        $restrict  = function ($set) use (&$staff_ids) {
            $staff_ids = $staff_ids === null ? array_values($set) : array_values(array_intersect($staff_ids, $set));
        };

        if ($scope['level'] === 'own') {
            $restrict([$me]);
        }

        // user filter: all | mine | <staff id>
        $user_label = 'All users';
        if ($f['user'] === 'mine') {
            $restrict(array_intersect($mine, $user_ids));
            $user_label = 'Users I created';
        } elseif (ctype_digit($f['user']) && in_array((int) $f['user'], $user_ids, true)) {
            $restrict([(int) $f['user']]);
            foreach ($users as $u) {
                if ((int) $u->staffid === (int) $f['user']) {
                    $user_label = trim($u->firstname . ' ' . $u->lastname);
                }
            }
        } else {
            $f['user'] = 'all';
        }

        // agent filter: none | all | mine | <agent id>
        $agent_label = 'No agent filter';
        if ($f['agent'] === 'all') {
            $restrict($agent_staff_ids);
            $agent_label = 'All agents';
        } elseif ($f['agent'] === 'mine') {
            $restrict(array_intersect($mine, $agent_staff_ids));
            $agent_label = 'Agents I created';
        } elseif (ctype_digit($f['agent'])) {
            $match = null;
            foreach ($agents as $a) {
                if ((int) $a->id === (int) $f['agent']) {
                    $match = $a;
                }
            }
            if ($match) {
                $restrict([(int) $match->staff_id]);
                $agent_label = trim($match->firstname . ' ' . $match->lastname);
            } else {
                $f['agent'] = 'none';
            }
        } else {
            $f['agent'] = 'none';
        }

        $country_label = 'All countries';
        if ($f['country_id'] > 0) {
            foreach ($countries as $c) {
                if ((int) $c->country_id === $f['country_id']) {
                    $country_label = $c->short_name;
                }
            }
        }
        if ($scope['level'] !== 'global') {
            $country_label = '';
            if (courier_is_country_admin()) {
                $ids = courier_get_admin_country_ids();
                $names = $this->db->select('short_name')->where_in('country_id', $ids)->get($p . 'countries')->result_array();
                $country_label = implode(', ', array_column($names, 'short_name'));
            }
        }

        return [
            'f'          => $f,
            'scope'      => $scope,
            'query'      => [
                'date_from'  => $f['date_from'],
                'date_to'    => $f['date_to'],
                'type'       => $f['type'],
                'branch_ids' => $branch_ids,
                'staff_ids'  => $staff_ids,
            ],
            'users'      => $users,
            'agents'     => $agents,
            'countries'  => $countries,
            'labels'     => [
                'type'    => $f['type'] ? Manifest_doc_model::type_labels()[$f['type']] : 'All modes',
                'user'    => $user_label,
                'agent'   => $agent_label,
                'country' => $country_label,
            ],
        ];
    }

    private function totals(array $rows)
    {
        $t = ['count' => count($rows), 'kg' => 0.0, 'cbm' => 0.0, 'qty' => 0, 'amounts' => []];
        foreach ($rows as $r) {
            $t['kg']  += (float) $r['kg'];
            $t['cbm'] += (float) $r['cbm'];
            $t['qty'] += (int) $r['qty'];
            $cur = $r['currency'] ?: '—';
            $t['amounts'][$cur] = ($t['amounts'][$cur] ?? 0) + (float) $r['amount'];
        }
        return $t;
    }

    public function index()
    {
        $ctx  = $this->resolve($this->input->get() ?: []);
        $rows = $this->docs->get_rows($ctx['query']);

        $data = [
            'title'         => 'Cargo Manifest',
            'f'             => $ctx['f'],
            'users'         => $ctx['users'],
            'agents'        => $ctx['agents'],
            'countries'     => $ctx['countries'],
            'labels'        => $ctx['labels'],
            'level'         => $ctx['scope']['level'],
            'is_country_admin' => courier_is_country_admin(),
            'type_labels'   => Manifest_doc_model::type_labels(),
            'rows'          => $rows,
            'totals'        => $this->totals($rows),
            'next_number'   => $this->docs->peek_next_number(),
            'can_generate'  => $this->can_generate(),
        ];
        $this->load->view('cargo_manifest/index', $data);
    }

    public function generate()
    {
        if (!$this->input->post()) {
            redirect(admin_url('courier_goshipping/cargo_manifest'));
        }
        if (!$this->can_generate()) {
            access_denied('Courier - Manifests');
        }
        $post = $this->input->post();
        $ctx  = $this->resolve($post);
        $rows = $this->docs->get_rows($ctx['query']);

        $qs = http_build_query(array_filter($ctx['f'], function ($v) { return $v !== '' && $v !== 0; }));
        if (empty($rows)) {
            set_alert('warning', 'No shipments match these filters, so no manifest was generated.');
            redirect(admin_url('courier_goshipping/cargo_manifest?' . $qs));
        }

        $branch_id = courier_get_session_branch_id();
        $country_id = $ctx['f']['country_id'] ?: null;
        if (!$country_id && $branch_id) {
            $b = $this->db->select('country_id')->where('id', (int) $branch_id)->get(db_prefix() . '_courier_branches')->row();
            $country_id = $b ? (int) $b->country_id : null;
        }

        $id = $this->docs->save([
            'created_by' => get_staff_user_id(),
            'branch_id'  => $branch_id,
            'country_id' => $country_id,
            'date_from'  => $ctx['f']['date_from'],
            'date_to'    => $ctx['f']['date_to'],
            'filters'    => $ctx['labels'],
        ], $rows);

        if (!$id) {
            set_alert('danger', 'Could not save the manifest.');
            redirect(admin_url('courier_goshipping/cargo_manifest?' . $qs));
        }
        set_alert('success', 'Manifest generated.');
        redirect(admin_url('courier_goshipping/cargo_manifest/view/' . $id));
    }

    /** Doc the current user may open, or redirects away. */
    private function authorized_doc($id)
    {
        $doc = $this->docs->get($id);
        $scope = $this->scope();
        $ok = (bool) $doc;
        if ($ok && $scope['level'] !== 'global') {
            $mine = (int) $doc->created_by === (int) get_staff_user_id();
            $ok = $mine || ($scope['level'] === 'branch' && in_array((int) $doc->branch_id, $scope['branch_ids'], true));
        }
        if (!$ok) {
            set_alert('danger', 'Manifest not found or outside your access.');
            redirect(admin_url('courier_goshipping/cargo_manifest/history'));
        }
        return $doc;
    }

    public function view($id)
    {
        $doc  = $this->authorized_doc($id);
        $rows = json_decode($doc->rows_json, true) ?: [];
        $info = courier_get_invoice_info($doc->branch_id);
        $data = [
            'title'   => $doc->manifest_number,
            'doc'     => $doc,
            'rows'    => $rows,
            'totals'  => $this->totals($rows),
            'filters' => json_decode($doc->filters_json, true) ?: [],
            'company' => $info['name'] ?: (get_option('companyname') ?: ''),
            'logo'    => (get_option('company_logo_dark') ?: get_option('company_logo')) ? base_url('uploads/company/' . (get_option('company_logo_dark') ?: get_option('company_logo'))) : '',
        ];
        $this->load->view('cargo_manifest/view', $data);
    }

    public function history()
    {
        $scope = $this->scope();
        $own   = $scope['level'] === 'own' ? get_staff_user_id() : null;
        $data  = [
            'title' => 'Generated manifests',
            'docs'  => $this->docs->list_docs($scope['branch_ids'], $own),
        ];
        $this->load->view('cargo_manifest/history', $data);
    }

    public function csv($id)
    {
        $doc  = $this->authorized_doc($id);
        $rows = json_decode($doc->rows_json, true) ?: [];
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9._-]/', '_', $doc->manifest_number) . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Manifest', $doc->manifest_number]);
        fputcsv($out, ['#', 'Waybill / BL', 'Shipper', 'Consignee', 'Origin Country', 'Destination', 'Shipment Type', 'Amount', 'Currency', 'Weight (kg)', 'CBM', 'Status']);
        foreach ($rows as $i => $r) {
            fputcsv($out, [$i + 1, $r['waybill'], $r['shipper'], $r['consignee'], $r['origin'],
                trim(($r['dest_town'] ? $r['dest_town'] . ', ' : '') . $r['dest']), $r['type'], $r['amount'], $r['currency'], $r['kg'], $r['cbm'], $r['status']]);
        }
        fclose($out);
        exit;
    }
}
