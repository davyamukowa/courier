<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Salibay_international_leg_status_update extends App_mail_template
{
    public $slug = 'salibay_international_leg_status_update';

    private $_merge;

    public function __construct($email, array $merge_fields = [])
    {
        parent::__construct();
        $this->send_to = $email;
        $this->_merge  = $merge_fields;
    }

    public function build()
    {
        $this->to($this->send_to)
             ->set_merge_fields($this->_merge);
    }
}
