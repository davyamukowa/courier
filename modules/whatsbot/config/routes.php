<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['whatsbot/get_webhook/(:num)/(:any)'] = 'get_webhook/index/$1/$2';
