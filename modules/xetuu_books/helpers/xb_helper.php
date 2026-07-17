<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('xb_format_money')) {
    function xb_format_money($amount, $currency = null)
    {
        $symbol = $currency ? $currency->symbol : 'KES';
        return $symbol . ' ' . number_format((float)$amount, 2);
    }
}

/**
 * Canonical enterprise status badge.
 * Use everywhere instead of Bootstrap label classes so colours never drift.
 *
 * @param string $state          Primary state: draft|posted|cancel|paid|partial|overdue|…
 * @param string $payment_state  Optional payment_state to compute compound status.
 */
if (!function_exists('xb_status_badge')) {
    function xb_status_badge($state, $payment_state = null)
    {
        // Compute effective state
        $effective = $state;
        if ($state === 'posted' && $payment_state) {
            if ($payment_state === 'paid')       $effective = 'paid';
            elseif ($payment_state === 'partial') $effective = 'partial';
            elseif ($payment_state === 'not_paid') {
                // Check overdue via invoice due date — caller must pass 'overdue' explicitly
                $effective = 'posted';
            }
        }
        if ($state === 'cancel') $effective = 'cancelled';

        $map = [
            'draft'      => ['xb-status-draft',      'Draft'],
            'submitted'  => ['xb-status-submitted',  'Submitted'],
            'approved'   => ['xb-status-approved',   'Approved'],
            'posted'     => ['xb-status-posted',      'Posted'],
            'cancelled'  => ['xb-status-cancelled',  'Cancelled'],
            'cancel'     => ['xb-status-cancelled',  'Cancelled'],
            'paid'       => ['xb-status-paid',        'Paid'],
            'partial'    => ['xb-status-partial',     'Partially Paid'],
            'overdue'    => ['xb-status-overdue',     'Overdue'],
            'reconciled' => ['xb-status-reconciled', 'Reconciled'],
            'not_paid'   => ['xb-status-not_paid',   'Unpaid'],
            'in_payment' => ['xb-status-in_payment', 'In Payment'],
            'confirmed'  => ['xb-status-confirmed',  'Confirmed'],
            'open'       => ['xb-status-open',        'Open'],
            'closed'     => ['xb-status-closed',      'Closed'],
        ];

        [$cls, $label] = $map[$effective] ?? ['xb-status-draft', ucfirst($effective)];
        return '<span class="xb-status-badge ' . $cls . '">' . htmlspecialchars($label) . '</span>';
    }
}

/** @deprecated Use xb_status_badge() */
if (!function_exists('xb_state_label')) {
    function xb_state_label($state)
    {
        return xb_status_badge($state);
    }
}

if (!function_exists('xb_get_account_name')) {
    function xb_get_account_name($account_id)
    {
        $CI = &get_instance();
        $acc = $CI->db->where('id', (int)$account_id)->get('acc_accounts')->row();
        return $acc ? $acc->code . ' - ' . $acc->name : 'Unknown';
    }
}

if (!function_exists('xb_get_partner_name')) {
    function xb_get_partner_name($partner_id, $type = 'customer')
    {
        if (!$partner_id) return '';
        $CI = &get_instance();
        if ($type === 'vendor') {
            $row = $CI->db->select('company')->where('userid', (int)$partner_id)->get('pur_vendor')->row();
            if ($row && $row->company) return $row->company;
            // Fallback: primary contact name
            $contact = $CI->db->select("CONCAT(firstname,' ',lastname) as name")->where('userid', (int)$partner_id)->where('is_primary', 1)->get('pur_contacts')->row();
            return $contact ? $contact->name : 'Vendor #' . $partner_id;
        }
        $row = $CI->db->select('company')->where('userid', (int)$partner_id)->get('clients')->row();
        return $row ? $row->company : 'Partner #' . $partner_id;
    }
}

if (!function_exists('xb_get_currency_symbol')) {
    function xb_get_currency_symbol($currency_id)
    {
        static $cache = [];
        if (!isset($cache[$currency_id])) {
            $CI = &get_instance();
            $cur = $CI->db->select('symbol')->where('id', (int)$currency_id)->get('acc_currencies')->row();
            $cache[$currency_id] = $cur ? $cur->symbol : 'KSh';
        }
        return $cache[$currency_id];
    }
}

if (!function_exists('xb_get_currency_code')) {
    function xb_get_currency_code($currency_id)
    {
        static $cache = [];
        if (!isset($cache[$currency_id])) {
            $CI = &get_instance();
            $cur = $CI->db->select('name')->where('id', (int)$currency_id)->get('acc_currencies')->row();
            $cache[$currency_id] = $cur ? $cur->name : 'KES';
        }
        return $cache[$currency_id];
    }
}

if (!function_exists('xb_file_icon')) {
    function xb_file_icon($filename)
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $map = [
            'pdf'  => 'fa-file-pdf-o',
            'doc'  => 'fa-file-word-o', 'docx' => 'fa-file-word-o',
            'xls'  => 'fa-file-excel-o', 'xlsx' => 'fa-file-excel-o',
            'png'  => 'fa-file-image-o', 'jpg'  => 'fa-file-image-o',
            'jpeg' => 'fa-file-image-o', 'xml'  => 'fa-file-code-o',
        ];
        return $map[$ext] ?? 'fa-file-o';
    }
}
