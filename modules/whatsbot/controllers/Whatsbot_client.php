<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Whatsbot_client extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
    }
    public function flow_response($id='')
    {
        $data['flow_response_data'] = get_flow_responses() ;
        $data['ticket_id'] = $id;
        $this->disableNavigation();
        $this->data($data);
        $this->view('flow_response/client_ticket_flow_response');
        $this->layout();
    }
}
