<?php 

defined('BASEPATH') || exit('No direct script access allowed');

require_once __DIR__.'/../vendor/autoload.php';

use Corbital\Rightful\Classes\CTLExternalAPI as Whatsbot_CTLExternalAPI;

class Whatsbot_aeiou {

    private $wb_lcb;

    public function __construct() 
    {
        $this->wb_lcb = new Whatsbot_CTLExternalAPI();
    }

    public function checkUpdate($module)
    {
        return;
    }

    public function downloadUpdate($module, $data)
    {
        echo json_encode(['type' => 'danger', 'message' => 'Auto-update is disabled.']);
    }

    public function checkUpdateStatus($module_name)
    {
        return false;
    }

    public function validatePurchase($module_name) {
        return true;
    }

    public function checkLicenseStatus($module_name)
    {
        return;
    }
}
