<?php defined('BASEPATH') or exit('No direct script access allowed');

class Xr_loans_model extends App_Model
{
    private function p() { return db_prefix(); }

    // ── Loan CRUD ──────────────────────────────────────────────────────────────

    public function get_loans($filters = [])
    {
        $p = $this->p();
        $this->db->select('l.*, CONCAT(e.first_name," ",e.last_name) AS employee_name,
                           e.employee_number, d.name AS department_name', false)
                 ->from($p . 'hr_loans l')
                 ->join($p . 'hr_employees e', 'e.id = l.employee_id', 'left')
                 ->join($p . 'hr_departments d', 'd.id = e.department_id', 'left');

        if (!empty($filters['company_id'])) {
            $this->db->where('l.company_id', (int)$filters['company_id']);
        }
        if (!empty($filters['employee_id'])) {
            $this->db->where('l.employee_id', (int)$filters['employee_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('l.status', $filters['status']);
        }
        if (!empty($filters['loan_type'])) {
            $this->db->where('l.loan_type', $filters['loan_type']);
        }
        return $this->db->order_by('l.date_created', 'DESC')->get()->result();
    }

    public function get_loan($id)
    {
        $p = $this->p();
        return $this->db->select('l.*, CONCAT(e.first_name," ",e.last_name) AS employee_name,
                                  e.employee_number, e.email AS employee_email,
                                  d.name AS department_name', false)
                        ->from($p . 'hr_loans l')
                        ->join($p . 'hr_employees e', 'e.id = l.employee_id', 'left')
                        ->join($p . 'hr_departments d', 'd.id = e.department_id', 'left')
                        ->where('l.id', (int)$id)
                        ->get()->row();
    }

    public function save_loan($data, $id = null)
    {
        $allowed = [
            'employee_id', 'company_id', 'loan_type', 'loan_reference', 'description',
            'principal_amount', 'interest_rate', 'monthly_installment', 'total_repayable',
            'balance_remaining', 'disbursement_date', 'start_deduction_date',
            'expected_end_date', 'status', 'notes', 'created_by',
        ];
        $row = array_intersect_key($data, array_flip($allowed));

        if ($id) {
            $this->db->where('id', (int)$id)->update($this->p() . 'hr_loans', $row);
            return $id;
        }

        $row['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert($this->p() . 'hr_loans', $row);
        return $this->db->insert_id();
    }

    public function delete_loan($id)
    {
        $this->db->where('id', (int)$id)->delete($this->p() . 'hr_loans');
    }

    // ── Employee-scoped queries ────────────────────────────────────────────────

    public function get_employee_loans($employee_id, $status = null)
    {
        $this->db->where('employee_id', (int)$employee_id);
        if ($status) {
            $this->db->where('status', $status);
        }
        return $this->db->order_by('date_created', 'DESC')
                        ->get($this->p() . 'hr_loans')->result();
    }

    /**
     * Returns active loans eligible for deduction in a given payroll period.
     * A loan is eligible when: status=active AND start_deduction_date <= period_date
     */
    public function get_active_loans_for_payroll($employee_id, $company_id, $period_date)
    {
        return $this->db->where('employee_id', (int)$employee_id)
                        ->where('company_id', (int)$company_id)
                        ->where('status', 'active')
                        ->where('start_deduction_date <=', $period_date)
                        ->where('balance_remaining >', 0)
                        ->order_by('date_created', 'ASC')
                        ->get($this->p() . 'hr_loans')->result();
    }

    // ── Repayments ────────────────────────────────────────────────────────────

    public function get_loan_repayments($loan_id)
    {
        return $this->db->where('loan_id', (int)$loan_id)
                        ->order_by('repayment_date', 'ASC')
                        ->get($this->p() . 'hr_loan_repayments')->result();
    }

    public function get_repayments_by_payslip($payslip_id)
    {
        return $this->db->where('payslip_id', (int)$payslip_id)
                        ->get($this->p() . 'hr_loan_repayments')->result();
    }

    /**
     * Record a repayment and update the loan balance.
     * Returns the repayment id, or false on failure.
     */
    public function record_repayment($loan_id, $amount, $payslip_id = null, $type = 'payroll', $notes = '', $staff_id = null)
    {
        $loan = $this->db->where('id', (int)$loan_id)->get($this->p() . 'hr_loans')->row();
        if (!$loan) { return false; }

        // Cap at remaining balance
        $amount = min((float)$amount, (float)$loan->balance_remaining);
        if ($amount <= 0) { return false; }

        // Compute interest portion for this payment (reducing-balance monthly)
        $monthly_rate = ((float)$loan->interest_rate / 100) / 12;
        $interest_portion = round((float)$loan->balance_remaining * $monthly_rate, 2);
        $principal_portion = round($amount - $interest_portion, 2);
        if ($principal_portion < 0) {
            $interest_portion = $amount;
            $principal_portion = 0;
        }

        $balance_before = (float)$loan->balance_remaining;
        $balance_after  = round($balance_before - $principal_portion, 2);
        if ($balance_after < 0) { $balance_after = 0; }

        $rep = [
            'loan_id'           => (int)$loan_id,
            'employee_id'       => $loan->employee_id,
            'payslip_id'        => $payslip_id ? (int)$payslip_id : null,
            'amount'            => $amount,
            'principal_portion' => $principal_portion,
            'interest_portion'  => $interest_portion,
            'balance_before'    => $balance_before,
            'balance_after'     => $balance_after,
            'repayment_date'    => date('Y-m-d'),
            'repayment_type'    => $type,
            'notes'             => $notes,
            'created_by'        => $staff_id,
            'date_created'      => date('Y-m-d H:i:s'),
        ];
        $this->db->insert($this->p() . 'hr_loan_repayments', $rep);
        $rep_id = $this->db->insert_id();

        // Update loan balance and status
        $new_status = $balance_after <= 0 ? 'paid' : 'active';
        $this->db->where('id', (int)$loan_id)
                 ->update($this->p() . 'hr_loans', [
                     'balance_remaining' => $balance_after,
                     'status'            => $new_status,
                 ]);

        return $rep_id;
    }

    /**
     * Reverse all payroll repayments linked to a payslip (on payslip reset).
     */
    public function reverse_payslip_repayments($payslip_id)
    {
        $reps = $this->get_repayments_by_payslip($payslip_id);
        foreach ($reps as $rep) {
            // Restore balance
            $this->db->where('id', $rep->loan_id)
                      ->set('balance_remaining', 'balance_remaining + ' . (float)$rep->principal_portion, false)
                      ->set('status', 'active')
                      ->update($this->p() . 'hr_loans');
        }
        // Delete the repayment records
        $this->db->where('payslip_id', (int)$payslip_id)
                 ->delete($this->p() . 'hr_loan_repayments');
    }

    // ── EMI / Amortization ────────────────────────────────────────────────────

    /**
     * Compute fixed monthly EMI using reducing balance formula.
     * If interest_rate is 0 → simple division.
     */
    public function compute_emi($principal, $annual_rate, $months)
    {
        if ($months <= 0) { return 0; }
        if ($annual_rate <= 0) {
            return round($principal / $months, 2);
        }
        $r = ($annual_rate / 100) / 12;
        $emi = $principal * $r * pow(1 + $r, $months) / (pow(1 + $r, $months) - 1);
        return round($emi, 2);
    }

    /**
     * Generate amortization schedule array.
     * Each row: ['month', 'opening', 'emi', 'principal', 'interest', 'closing']
     */
    public function get_amortization_schedule($principal, $annual_rate, $months, $start_date = null)
    {
        $emi      = $this->compute_emi($principal, $annual_rate, $months);
        $r        = ($annual_rate / 100) / 12;
        $balance  = (float)$principal;
        $schedule = [];
        $date     = $start_date ? new DateTime($start_date) : new DateTime();

        for ($i = 1; $i <= $months && $balance > 0; $i++) {
            $interest  = round($balance * $r, 2);
            $principal_p = round($emi - $interest, 2);
            if ($principal_p > $balance) { $principal_p = $balance; }
            $closing   = round($balance - $principal_p, 2);

            $schedule[] = [
                'month'     => $i,
                'due_date'  => $date->format('Y-m-d'),
                'opening'   => $balance,
                'emi'       => round($emi, 2),
                'principal' => $principal_p,
                'interest'  => $interest,
                'closing'   => max($closing, 0),
            ];

            $balance = $closing;
            $date->modify('+1 month');
        }
        return $schedule;
    }

    // ── Suspension ────────────────────────────────────────────────────────────

    public function suspend_loan($id, $reason, $staff_id)
    {
        $this->db->where('id', (int)$id)->update($this->p() . 'hr_loans', [
            'status'           => 'suspended',
            'suspension_reason'=> $reason,
            'suspended_by'     => (int)$staff_id,
            'suspended_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    public function reactivate_loan($id)
    {
        $this->db->where('id', (int)$id)->update($this->p() . 'hr_loans', [
            'status'           => 'active',
            'suspension_reason'=> null,
            'suspended_by'     => null,
            'suspended_at'     => null,
        ]);
    }
}
