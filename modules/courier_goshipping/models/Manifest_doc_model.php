<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Cargo manifests built from shipments (the redesigned manifest).
 *
 * A manifest is a numbered document: when generated, the shipments matching
 * the chosen filters are snapshotted (rows_json) so the document stays stable
 * even if a shipment's status changes later. Number format MF-YYYY-NNNNNN,
 * derived from the row id so it is unique and race-safe.
 *
 * Not to be confused with Manifest_model / tbl_manifests (the older raw
 * air-cargo AWB manifests), which are left untouched.
 */
class Manifest_doc_model extends App_Model
{
    private $docs;

    public function __construct()
    {
        parent::__construct();
        $this->docs = db_prefix() . '_courier_manifest_docs';
        $this->ensure_tables();
    }

    private function ensure_tables()
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        if (!$this->db->table_exists($this->docs)) {
            $this->db->query('CREATE TABLE IF NOT EXISTS `' . $this->docs . '` (
                `id`             INT NOT NULL AUTO_INCREMENT,
                `manifest_number` VARCHAR(30) NOT NULL DEFAULT "",
                `created_by`     INT NOT NULL,
                `branch_id`      INT NULL,
                `country_id`     INT NULL,
                `date_from`      DATE NULL,
                `date_to`        DATE NULL,
                `filters_json`   TEXT NULL,
                `shipment_count` INT NOT NULL DEFAULT 0,
                `rows_json`      LONGTEXT NULL,
                `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_cmd_number` (`manifest_number`),
                INDEX `idx_cmd_branch` (`branch_id`),
                INDEX `idx_cmd_country` (`country_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
        }
        if (!$this->db->table_exists(db_prefix() . '_courier_staff_creators')) {
            $this->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . '_courier_staff_creators` (
                `staff_id`   INT NOT NULL,
                `created_by` INT NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`staff_id`),
                INDEX `idx_csc_creator` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
        }
    }

    /** The number the next generated manifest will get (display only; the real one is fixed on save). */
    public function peek_next_number()
    {
        $row = $this->db->select_max('id')->get($this->docs)->row();
        return $this->format_number(((int) ($row->id ?? 0)) + 1);
    }

    public function format_number($id, $year = null)
    {
        return 'MF-' . ($year ?: date('Y')) . '-' . str_pad((string) (int) $id, 6, '0', STR_PAD_LEFT);
    }

    /** Human label for a shipment's type, from the stored category/mode. */
    public static function type_key($category, $mode, $fcl)
    {
        $m = strtoupper((string) $mode);
        if ((int) $fcl === 1 || strpos($m, 'FCL') !== false) {
            return 'fcl';
        }
        if (strpos($m, 'LCL') !== false) {
            return 'lcl';
        }
        if ($category === 'domestic') {
            return 'local';
        }
        if (strpos($m, 'COURIER') !== false) {
            return 'intl_courier';
        }
        if (strpos($m, 'AIR') !== false) {
            return 'air';
        }
        if (strpos($m, 'SEA') !== false) {
            return 'sea';
        }
        if (strpos($m, 'ROAD') !== false) {
            return 'road';
        }
        return 'intl_other';
    }

    public static function type_labels()
    {
        return [
            'local'        => 'Local',
            'intl_courier' => 'International Courier',
            'lcl'          => 'LCL',
            'fcl'          => 'FCL',
            'air'          => 'Air Freight',
            'sea'          => 'Sea Freight',
            'road'         => 'Road Freight',
            'intl_other'   => 'International',
        ];
    }

    /**
     * Shipment rows for the manifest table.
     *
     * $f: date_from, date_to, type, staff_ids (int[]|null), branch_ids (int[]|null)
     * ($staff_ids / $branch_ids: null = no restriction).
     */
    public function get_rows(array $f)
    {
        $p = db_prefix();

        $this->db->select(
            's.id, s.waybill_number, s.tracking_id, s.shipping_mode, s.shipping_category, s.fcl_shipment, s.created_at,'
            . ' s.staff_id, s.packaging_charges, s.vat_amount, s.branch_id,'
            . ' COALESCE(ss.description, ss.status_name, "Pending") AS status_name,'
            . ' inv.total AS inv_total, cu.name AS cur_name,'
            . ' sr.first_name AS sf, sr.last_name AS sl, sc.company_name AS s_company, sc.contact_person_name AS s_contact,'
            . ' rr.first_name AS rf, rr.last_name AS rl, rc.recipient_company_name AS r_company, rc.recipient_contact_person_name AS r_contact,'
            . ' COALESCE(co_s.short_name, co_sc.short_name) AS origin_country,'
            . ' COALESCE(co_r.short_name, co_rc.short_name) AS dest_country,'
            . ' COALESCE(st_r.name, st_rc.name) AS dest_town',
            false
        );
        $this->db->from($p . '_shipments s');
        $this->db->join($p . '_shipment_statuses ss', 'ss.id = s.status_id', 'left');
        $this->db->join($p . 'invoices inv', 'inv.id = s.invoice_id AND s.invoice_id > 0', 'left');
        $this->db->join($p . 'currencies cu', 'cu.id = inv.currency', 'left');
        $this->db->join($p . '_shipment_senders sr', 'sr.id = s.sender_id', 'left');
        $this->db->join($p . '_shipment_companies sc', 'sc.id = s.company_id', 'left');
        $this->db->join($p . '_shipment_recipients rr', 'rr.id = s.recipient_id', 'left');
        $this->db->join($p . '_recipient_companies rc', 'rc.id = s.recipient_company_id', 'left');
        $this->db->join($p . 'countries co_s', 'co_s.country_id = sr.country_id', 'left');
        $this->db->join($p . 'countries co_sc', 'co_sc.country_id = sc.contact_country_id', 'left');
        $this->db->join($p . 'countries co_r', 'co_r.country_id = rr.country_id', 'left');
        $this->db->join($p . 'countries co_rc', 'co_rc.country_id = rc.recipient_contact_country_id', 'left');
        $this->db->join($p . '_country_states st_r', 'st_r.id = rr.state_id', 'left');
        $this->db->join($p . '_country_states st_rc', 'st_rc.id = rc.recipient_contact_state_id', 'left');

        $this->db->where('DATE(s.created_at) >=', $f['date_from']);
        $this->db->where('DATE(s.created_at) <=', $f['date_to']);
        if ($f['branch_ids'] !== null) {
            $this->db->where_in('s.branch_id', !empty($f['branch_ids']) ? array_map('intval', $f['branch_ids']) : [0]);
        }
        if ($f['staff_ids'] !== null) {
            $this->db->where_in('s.staff_id', !empty($f['staff_ids']) ? array_map('intval', $f['staff_ids']) : [0]);
        }
        $this->db->order_by('s.created_at', 'ASC');
        $shipments = $this->db->get()->result();
        if (empty($shipments)) {
            return [];
        }

        // Package totals (kg / CBM) and FCL containers, in one query each.
        $ids = array_map(function ($s) { return (int) $s->id; }, $shipments);
        $pk  = [];
        foreach (array_chunk($ids, 500) as $chunk) {
            $q = $this->db->select(
                'shipment_id, SUM(quantity) AS qty,'
                . ' SUM(COALESCE(NULLIF(weight,0), chargeable_weight, 0)) AS kg,'
                . ' SUM(COALESCE(chargeable_weight,0)) AS ckg,'
                . ' SUM(COALESCE(length,0) * COALESCE(width,0) * COALESCE(height,0) * quantity) / 1000000 AS cbm',
                false
            )->where_in('shipment_id', $chunk)->group_by('shipment_id')->get($p . '_shipment_packages')->result();
            foreach ($q as $r) {
                $pk[(int) $r->shipment_id] = $r;
            }
        }
        $fcl = [];
        if ($this->db->table_exists($p . '_shipment_fcl_packages')) {
            foreach (array_chunk($ids, 500) as $chunk) {
                $q = $this->db->select('shipment_id, SUM(quantity) AS qty, GROUP_CONCAT(DISTINCT fcl_option SEPARATOR ", ") AS opts', false)
                    ->where_in('shipment_id', $chunk)->group_by('shipment_id')->get($p . '_shipment_fcl_packages')->result();
                foreach ($q as $r) {
                    $fcl[(int) $r->shipment_id] = $r;
                }
            }
        }

        $want = $f['type'] ?? '';
        $rows = [];
        foreach ($shipments as $s) {
            $key = self::type_key($s->shipping_category, $s->shipping_mode, $s->fcl_shipment);
            if ($want !== '' && $want !== 'all' && $want !== $key) {
                continue;
            }
            $shipper = trim(($s->sf ?? '') . ' ' . ($s->sl ?? ''));
            if ($shipper === '') {
                $shipper = trim(($s->s_company ?? '') . ($s->s_contact ? ' (' . $s->s_contact . ')' : ''));
            }
            $consignee = trim(($s->rf ?? '') . ' ' . ($s->rl ?? ''));
            if ($consignee === '') {
                $consignee = trim(($s->r_company ?? '') . ($s->r_contact ? ' (' . $s->r_contact . ')' : ''));
            }
            $amount = $s->inv_total !== null ? (float) $s->inv_total : ((float) $s->packaging_charges + (float) $s->vat_amount);

            $kg = $cbm = $ckg = 0.0;
            $qty = 0;
            $extra = '';
            if (isset($pk[(int) $s->id])) {
                $kg  = (float) $pk[(int) $s->id]->kg;
                $ckg = (float) $pk[(int) $s->id]->ckg;
                $cbm = (float) $pk[(int) $s->id]->cbm;
                $qty = (int) $pk[(int) $s->id]->qty;
            }
            if (isset($fcl[(int) $s->id])) {
                $qty   += (int) $fcl[(int) $s->id]->qty;
                $extra  = $fcl[(int) $s->id]->opts;
            }

            $rows[] = [
                'id'        => (int) $s->id,
                'waybill'   => $s->waybill_number ?: $s->tracking_id,
                'shipper'   => $shipper ?: 'N/A',
                'consignee' => $consignee ?: 'N/A',
                'origin'    => $s->origin_country ?: '-',
                'dest'      => $s->dest_country ?: '-',
                'dest_town' => $s->dest_town ?: '',
                'type_key'  => $key,
                'type'      => self::type_labels()[$key],
                'amount'    => round($amount, 2),
                'currency'  => $s->cur_name ?: '',
                'kg'        => round($kg, 2),
                'ckg'       => round($ckg, 2),
                'cbm'       => round($cbm, 3),
                'qty'       => $qty,
                'fcl'       => $extra,
                'status'    => $s->status_name,
                'staff_id'  => (int) $s->staff_id,
                'date'      => $s->created_at,
            ];
        }
        return $rows;
    }

    public function save(array $meta, array $rows)
    {
        $this->db->insert($this->docs, [
            'manifest_number' => '',
            'created_by'      => (int) $meta['created_by'],
            'branch_id'       => $meta['branch_id'] ?: null,
            'country_id'      => $meta['country_id'] ?: null,
            'date_from'       => $meta['date_from'],
            'date_to'         => $meta['date_to'],
            'filters_json'    => json_encode($meta['filters']),
            'shipment_count'  => count($rows),
            'rows_json'       => json_encode($rows),
            'created_at'      => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insert_id();
        if (!$id) {
            return false;
        }
        $number = $this->format_number($id);
        $this->db->where('id', $id)->update($this->docs, ['manifest_number' => $number]);
        return $id;
    }

    public function get($id)
    {
        $p = db_prefix();
        return $this->db->select('d.*, CONCAT(st.firstname, " ", st.lastname) AS creator_name, b.name AS branch_name, c.short_name AS country_name', false)
            ->from($this->docs . ' d')
            ->join($p . 'staff st', 'st.staffid = d.created_by', 'left')
            ->join($p . '_courier_branches b', 'b.id = d.branch_id', 'left')
            ->join($p . 'countries c', 'c.country_id = d.country_id', 'left')
            ->where('d.id', (int) $id)->get()->row();
    }

    /** $branch_ids null = all; $own_staff_id restricts to docs that staff member generated. */
    public function list_docs($branch_ids = null, $own_staff_id = null)
    {
        $p = db_prefix();
        $this->db->select('d.id, d.manifest_number, d.created_by, d.date_from, d.date_to, d.shipment_count, d.created_at, d.filters_json, CONCAT(st.firstname, " ", st.lastname) AS creator_name, b.name AS branch_name, c.short_name AS country_name', false)
            ->from($this->docs . ' d')
            ->join($p . 'staff st', 'st.staffid = d.created_by', 'left')
            ->join($p . '_courier_branches b', 'b.id = d.branch_id', 'left')
            ->join($p . 'countries c', 'c.country_id = d.country_id', 'left');
        if ($branch_ids !== null) {
            $this->db->where_in('d.branch_id', !empty($branch_ids) ? array_map('intval', $branch_ids) : [0]);
        }
        if ($own_staff_id) {
            $this->db->where('d.created_by', (int) $own_staff_id);
        }
        return $this->db->order_by('d.id', 'DESC')->limit(300)->get()->result();
    }
}
