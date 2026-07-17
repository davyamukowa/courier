<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Base class for Xetuu HR request handlers.
 *
 * Each handler receives the parent CI controller instance so it can access
 * $this->CI->db, $this->CI->hr, $this->CI->payroll, helpers, views, etc.
 * This keeps the main Xetuu_hr.php controller thin — it just routes.
 */
abstract class Xr_handler
{
    /** @var CI_Controller|Xetuu_hr */
    protected $CI;

    public function __construct($ci)
    {
        $this->CI = $ci;
    }

    // ── Convenience proxies ────────────────────────────────────────────────────

    protected function db()          { return $this->CI->db; }
    protected function p()           { return db_prefix(); }
    protected function post($key = null, $xss = true) { return $this->CI->input->post($key, $xss); }
    protected function get($key)     { return $this->CI->input->get($key); }
    protected function uri($seg)     { return $this->CI->uri->segment($seg); }
    protected function redirect($url){ redirect($url); }
    protected function base()        { return admin_url('xetuu_hr'); }
    protected function set_alert($type, $msg) { set_alert($type, $msg); }
    protected function csrf_field()  {
        return form_hidden(
            $this->CI->security->get_csrf_token_name(),
            $this->CI->security->get_csrf_hash()
        );
    }

    protected function view($path, $data = [])
    {
        $this->CI->load->view($path, $data);
    }

    protected function show404() { show_404(); }
}
