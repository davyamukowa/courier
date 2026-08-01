<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Salibay_order_shipment_created extends App_mail_template
{
    public $slug = 'salibay_order_shipment_created';

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
