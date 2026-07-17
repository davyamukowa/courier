<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Courier_payment_receipt_to_customer extends App_mail_template
{
    public $slug = 'courier_payment_receipt_to_customer';

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
