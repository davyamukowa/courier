<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/Courier.php';

class Courier_goshipping extends Courier
{
    /**
     * Linux-safe module entry controller.
     * Allows /admin/courier_goshipping/dashboard to resolve without
     * depending on custom global application routes.
     */
    public function index()
    {
        $this->dashboard();
    }
}
