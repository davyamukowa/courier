<?php
defined('BASEPATH') or exit('No direct script access allowed');

trait Xb_reports_trait
{
    public function reports($report = 'balance_sheet')
    {
        if (!has_permission('accounting_report', '', 'view')) { access_denied('xetuu_books'); }

        $date_from  = $this->input->get('date_from')  ?: date('Y-01-01');
        $date_to    = $this->input->get('date_to')    ?: date('Y-12-31');
        $show_zero  = (bool)$this->input->get('show_zero');
        $compare    = (bool)$this->input->get('compare');
        $partner_id = (int)$this->input->get('partner_id');
        $account_id = (int)$this->input->get('account_id');

        $params = compact('date_from', 'date_to', 'show_zero', 'compare', 'partner_id', 'account_id');

        $valid = ['balance_sheet','profit_loss','profit_and_loss','general_ledger','trial_balance',
                  'aged_receivable','aged_payable','tax_report','cash_flow','partner_ledger',
                  'journal_report','executive_summary','depreciation_schedule','invoice_analysis'];
        if (!in_array($report, $valid)) { show_404(); }

        // Normalize URL alias to model key
        $report_key = ($report === 'profit_and_loss') ? 'profit_loss' : $report;

        $data = $this->xb_report->get_report_data($report_key, $params);
        if (in_array($report_key, ['profit_loss', 'balance_sheet', 'journal_report'])) {
            $gl_data    = $this->xb_report->get_report_data('general_ledger', $params);
            $data['gl'] = $gl_data['ledger'] ?? [];
            if ($report_key === 'journal_report') {
                $data['ledger'] = $gl_data['ledger'] ?? [];
            }
        }
        $data['report']       = $report;
        $data['params']       = $params;
        $data['xb_page']      = 'reports';
        $data['title']        = $this->_report_title($report_key);
        $data['fiscal_years'] = $this->xb_config->get_fiscal_years();

        // Use profit_loss view for both URL slugs
        $view_name = $report_key;
        $this->load->view('xetuu_books/layout/layout', array_merge($data, [
            'xb_content' => $this->load->view('xetuu_books/reports/' . $view_name, $data, true),
        ]));
    }

    public function report_export()
    {
        if (!has_permission('accounting_report', '', 'view')) { access_denied('xetuu_books'); }
        $report    = $this->input->get('report');
        $format    = $this->input->get('format'); // excel | csv | pdf
        $date_from = $this->input->get('date_from') ?: date('Y-01-01');
        $date_to   = $this->input->get('date_to')   ?: date('Y-12-31');
        $params    = compact('date_from', 'date_to');
        $this->xb_report->export_report($report, $format, $params);
    }

    private function _report_title($report)
    {
        $titles = [
            'balance_sheet'         => 'Balance Sheet',
            'profit_loss'           => 'Profit and Loss',
            'general_ledger'        => 'General Ledger',
            'trial_balance'         => 'Trial Balance',
            'aged_receivable'       => 'Aged Receivable',
            'aged_payable'          => 'Aged Payable',
            'tax_report'            => 'Tax Report',
            'cash_flow'             => 'Cash Flow Statement',
            'partner_ledger'        => 'Partner Ledger',
            'journal_report'        => 'Journal Report',
            'executive_summary'     => 'Executive Summary',
            'depreciation_schedule' => 'Depreciation Schedule',
            'invoice_analysis'      => 'Invoice Analysis',
        ];
        return $titles[$report] ?? ucwords(str_replace('_', ' ', $report));
    }
}
