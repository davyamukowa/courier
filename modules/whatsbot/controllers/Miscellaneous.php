<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Miscellaneous extends AdminController {
    public function __construct() {
        parent::__construct();
        $this->app_modules->is_inactive('whatsbot') ? access_denied() : '';
    }

    public function index() {
        redirect(admin_url('whatsbot/custom_label'));
    }
}
