<?php
defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Payslip_pdf extends App_pdf
{
    private $payslip;
    private $lines;
    private $contract;
    private $template;

    private static $VALID_TEMPLATES = ['a4_standard','a4_modern','a4_minimal','thermal_80','thermal_58'];
    private static $THERMAL         = ['thermal_80','thermal_58'];

    public function __construct($payslip, $lines, $contract, $template = 'a4_standard')
    {
        $this->payslip  = $payslip;
        $this->lines    = $lines;
        $this->contract = $contract;
        // Set template BEFORE parent::__construct() so get_format_array() can use it
        $this->template = in_array($template, self::$VALID_TEMPLATES) ? $template : 'a4_standard';

        parent::__construct(); // calls $this->get_format_array() via overridden method below

        $this->SetTitle('Payslip - ' . ($this->payslip->employee_name ?? 'Employee'));
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
    }

    /**
     * Overrides App_pdf to inject custom page size for thermal templates.
     * Called by parent::__construct() before AddPage().
     */
    public function get_format_array()
    {
        if (in_array($this->template, self::$THERMAL)) {
            $width = ($this->template === 'thermal_58') ? 58 : 80;
            return ['orientation' => 'P', 'format' => [$width, 297]];
        }
        return parent::get_format_array();
    }

    public function prepare()
    {
        $this->set_view_vars([
            'payslip'  => $this->payslip,
            'lines'    => $this->lines,
            'contract' => $this->contract,
        ]);

        return $this->build();
    }

    protected function type()
    {
        return 'payslip';
    }

    protected function file_path()
    {
        return module_dir_path('xetuu_hr', 'views/admin/payroll/pdf/' . $this->template . '.php');
    }
}
