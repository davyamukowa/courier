<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/Xr_handler.php';

/**
 * Handles all /xetuu_hr/payroll/loans/* routes.
 */
class Loans_handler extends Xr_handler
{
    private $loans;
    private $hr;

    public function __construct($ci)
    {
        parent::__construct($ci);
        $ci->load->model('xetuu_hr/Xr_loans_model', 'loans_mdl');
        $this->loans = $ci->loans_mdl;
        $this->hr    = $ci->hr;
    }

    public function handle($action, $id, $base_data, $company_id, $p)
    {
        // AJAX: employee loans list for a given employee (used in contract edit)
        if ($action === 'employee_loans' && $this->CI->input->is_ajax_request()) {
            $emp_id = (int)$this->get('employee_id');
            $loans  = $emp_id ? $this->loans->get_employee_loans($emp_id) : [];
            echo json_encode(['success' => true, 'loans' => $loans]);
            exit;
        }

        // AJAX: compute EMI preview
        if ($action === 'compute_emi' && $this->CI->input->is_ajax_request()) {
            $principal = (float)$this->post('principal');
            $rate      = (float)$this->post('interest_rate');
            $months    = (int)$this->post('months');
            $emi       = $this->loans->compute_emi($principal, $rate, $months);
            $total     = $rate > 0
                ? $this->_compute_total_repayable($principal, $rate, $months)
                : $principal;
            echo json_encode(['success' => true, 'emi' => $emi, 'total_repayable' => round($total, 2)]);
            exit;
        }

        // Save (new or edit)
        if ($action === 'save') {
            $this->_save_loan($base_data, $company_id, $p);
            return;
        }

        // Suspend
        if (is_numeric($action) && $id === 'suspend') {
            $reason = $this->post('reason') ?: 'Employee request';
            $this->loans->suspend_loan((int)$action, $reason, get_staff_user_id());
            $this->set_alert('success', 'Loan suspended.');
            $this->redirect($this->base() . '/payroll/loans/' . (int)$action . '/statement');
            return;
        }

        // Reactivate
        if (is_numeric($action) && $id === 'reactivate') {
            $this->loans->reactivate_loan((int)$action);
            $this->set_alert('success', 'Loan reactivated.');
            $this->redirect($this->base() . '/payroll/loans/' . (int)$action . '/statement');
            return;
        }

        // Manual payment
        if (is_numeric($action) && $id === 'pay') {
            $amount = (float)$this->post('amount');
            $notes  = $this->post('notes');
            $type   = $this->post('repayment_type') ?: 'manual';
            if ($amount > 0) {
                $this->loans->record_repayment((int)$action, $amount, null, $type, $notes, get_staff_user_id());
                $this->set_alert('success', 'Payment of ' . number_format($amount, 2) . ' recorded.');
            } else {
                $this->set_alert('error', 'Invalid amount.');
            }
            $this->redirect($this->base() . '/payroll/loans/' . (int)$action . '/statement');
            return;
        }

        // Delete
        if (is_numeric($action) && $id === 'delete') {
            $loan = $this->loans->get_loan((int)$action);
            if ($loan && $loan->status !== 'active') {
                $this->loans->delete_loan((int)$action);
                $this->set_alert('success', 'Loan deleted.');
                $this->redirect($this->base() . '/payroll/loans');
            } else {
                $this->set_alert('error', 'Cannot delete an active loan. Suspend or write it off first.');
                $this->redirect($this->base() . '/payroll/loans');
            }
            return;
        }

        // Loan statement / detail view
        if (is_numeric($action) && ($id === 'statement' || !$id)) {
            $this->_view_statement((int)$action, $base_data, $company_id, $p);
            return;
        }

        // New / edit form
        if ($action === 'new' || (is_numeric($action) && $id === 'edit')) {
            $this->_form($action === 'new' ? null : (int)$action, $base_data, $company_id, $p);
            return;
        }

        // List (default)
        $this->_list($base_data, $company_id, $p);
    }

    // ── List ──────────────────────────────────────────────────────────────────
    private function _list($base_data, $company_id, $p)
    {
        $filters = ['company_id' => $company_id];
        if ($s = $this->get('status'))  { $filters['status']    = $s; }
        if ($t = $this->get('type'))    { $filters['loan_type'] = $t; }
        if ($e = $this->get('emp_id'))  { $filters['employee_id'] = (int)$e; }

        $this->view('xetuu_hr/admin/payroll/loans/index', array_merge($base_data, [
            'title'          => 'Employee Loans',
            'xhr_payroll_sub'=> 'loans',
            'loans'          => $this->loans->get_loans($filters),
            'filters'        => $filters,
            'employees'      => $this->hr->get_employees(),
        ]));
    }

    // ── Form (new / edit) ──────────────────────────────────────────────────────
    private function _form($loan_id, $base_data, $company_id, $p)
    {
        $loan      = $loan_id ? $this->loans->get_loan($loan_id) : null;
        $employees = $this->hr->get_employees();
        // Pre-select employee from GET
        $preselect_emp = (int)$this->get('employee_id');

        $this->view('xetuu_hr/admin/payroll/loans/form', array_merge($base_data, [
            'title'          => $loan_id ? 'Edit Loan' : 'New Loan',
            'xhr_payroll_sub'=> 'loans',
            'loan'           => $loan,
            'employees'      => $employees,
            'preselect_emp'  => $preselect_emp ?: ($loan ? $loan->employee_id : 0),
        ]));
    }

    // ── Save ──────────────────────────────────────────────────────────────────
    private function _save_loan($base_data, $company_id, $p)
    {
        $id = (int)$this->post('loan_id');

        $principal = (float)$this->post('principal_amount');
        $rate      = (float)$this->post('interest_rate');
        $months    = (int)$this->post('months');
        $emi       = $this->loans->compute_emi($principal, $rate, $months);
        $total     = $rate > 0
            ? $this->_compute_total_repayable($principal, $rate, $months)
            : $principal;

        $data = [
            'employee_id'          => (int)$this->post('employee_id'),
            'company_id'           => $company_id ?: (int)$this->post('company_id'),
            'loan_type'            => $this->post('loan_type'),
            'loan_reference'       => $this->post('loan_reference'),
            'description'          => $this->post('description'),
            'principal_amount'     => $principal,
            'interest_rate'        => $rate,
            'monthly_installment'  => $emi,
            'total_repayable'      => round($total, 2),
            'balance_remaining'    => $id ? null : $principal, // on edit, keep existing balance
            'disbursement_date'    => $this->post('disbursement_date') ?: null,
            'start_deduction_date' => $this->post('start_deduction_date') ?: null,
            'expected_end_date'    => $this->post('expected_end_date') ?: null,
            'notes'                => $this->post('notes'),
            'created_by'           => get_staff_user_id(),
        ];

        // On edit, don't overwrite balance_remaining
        if ($id) { unset($data['balance_remaining']); }

        $saved_id = $this->loans->save_loan($data, $id ?: null);
        $this->set_alert('success', $id ? 'Loan updated.' : 'Loan created.');
        $this->redirect($this->base() . '/payroll/loans/' . $saved_id . '/statement');
    }

    // ── Statement ─────────────────────────────────────────────────────────────
    private function _view_statement($loan_id, $base_data, $company_id, $p)
    {
        $loan = $this->loans->get_loan($loan_id);
        if (!$loan) {
            $this->set_alert('error', 'Loan not found.');
            $this->redirect($this->base() . '/payroll/loans');
            return;
        }
        $repayments = $this->loans->get_loan_repayments($loan_id);
        $schedule   = $this->loans->get_amortization_schedule(
            $loan->principal_amount,
            $loan->interest_rate,
            $this->_loan_months($loan),
            $loan->start_deduction_date ?: $loan->disbursement_date
        );

        $this->view('xetuu_hr/admin/payroll/loans/statement', array_merge($base_data, [
            'title'          => 'Loan Statement',
            'xhr_payroll_sub'=> 'loans',
            'loan'           => $loan,
            'repayments'     => $repayments,
            'schedule'       => $schedule,
        ]));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function _compute_total_repayable($principal, $annual_rate, $months)
    {
        $emi = $this->loans->compute_emi($principal, $annual_rate, $months);
        return $emi * $months;
    }

    private function _loan_months($loan)
    {
        if ($loan->disbursement_date && $loan->expected_end_date) {
            $d1 = new DateTime($loan->disbursement_date);
            $d2 = new DateTime($loan->expected_end_date);
            $diff = $d1->diff($d2);
            return max(1, $diff->y * 12 + $diff->m);
        }
        // Fallback: derive from principal / monthly_installment
        if ($loan->monthly_installment > 0) {
            return (int)ceil($loan->principal_amount / $loan->monthly_installment);
        }
        return 12;
    }
}
