<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once(APP_MODULES_PATH . '/pesapal/libraries/OAuth.php');

class Pesapal extends App_Controller
{
    // ─────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────

    /** Return live/demo consumer key & secret based on test-mode setting */
    private function _get_credentials()
    {
        $test = $this->pesapal_gateway->getSetting('test_mode_enabled') == '1';
        return [
            'key'    => $test ? $this->pesapal_gateway->getSetting('consumer_key_demo')    : $this->pesapal_gateway->getSetting('consumer_key'),
            'secret' => $test ? $this->pesapal_gateway->getSetting('consumer_secret_demo') : $this->pesapal_gateway->getSetting('consumer_secret'),
        ];
    }

    /**
     * Query Pesapal QueryPaymentDetails API.
     *
     * Pesapal response format:
     *   pesapal_response_data=TRACKING_ID,PAYMENT_METHOD,STATUS,MERCHANT_REF
     *
     * Returns an array: ['status' => 'COMPLETED', 'method' => 'MPESAKE']
     */
    private function _query_payment_details($merchant_reference, $tracking_id)
    {
        $creds  = $this->_get_credentials();
        $apiUrl = $this->pesapal_gateway->get_action_url() . 'QueryPaymentDetails';

        $token  = $params = null;
        $consumer         = new OAuthConsumer($creds['key'], $creds['secret']);
        $signature_method = new OAuthSignatureMethod_HMAC_SHA1();

        $req = OAuthRequest::from_consumer_and_token($consumer, $token, 'GET', $apiUrl, $params);
        $req->set_parameter('pesapal_merchant_reference',      $merchant_reference);
        $req->set_parameter('pesapal_transaction_tracking_id', $tracking_id);
        $req->sign_request($signature_method, $consumer, $token);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $req);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER,         1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response    = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $body = trim(substr($response, $header_size));

        // Strip query-string wrapper: pesapal_response_data=VALUE
        // VALUE is comma-separated: TRACKING_ID,PAYMENT_METHOD,STATUS,MERCHANT_REF
        $value = $body;
        if (strpos($body, 'pesapal_response_data=') !== false) {
            parse_str($body, $parsed);
            $value = isset($parsed['pesapal_response_data']) ? $parsed['pesapal_response_data'] : $body;
        }

        $parts  = explode(',', $value);
        $status = isset($parts[2]) ? strtoupper(trim($parts[2])) : 'INVALID';
        $method = isset($parts[1]) ? trim($parts[1])             : 'Pesapal';

        return ['status' => $status, 'method' => $method];
    }

    /**
     * Mark invoice as paid via Perfex addPayment and update pesapal_txn record.
     */
    private function _mark_invoice_paid($merchant_reference, $tracking_id, $payment_method = 'Pesapal')
    {
        $this->db->where('reference_code', $merchant_reference);
        $txn = $this->db->get(db_prefix() . 'pesapal_txn')->row();

        if (!$txn || empty($txn->invoiceid) || empty($txn->amount)) {
            return false;
        }

        // Avoid double-payment
        if ($txn->txn_status === 'PAID') {
            return true;
        }

        // Update pesapal_txn record
        $this->db->where('reference_code', $merchant_reference);
        $this->db->update(db_prefix() . 'pesapal_txn', [
            'tracking_id'       => $tracking_id,
            'txn_ipn_date'      => date('Y-m-d H:i:s'),
            'notification_type' => 'COMPLETED',
            'txn_status'        => 'PAID',
            'flag'              => 1,
        ]);

        // Add payment to Perfex invoice
        $this->load->model('invoices_model');
        $invoice = $this->invoices_model->get($txn->invoiceid);
        if ($invoice) {
            load_client_language($invoice->clientid);
        }

        $this->pesapal_gateway->addPayment([
            'amount'        => $txn->amount,
            'invoiceid'     => $txn->invoiceid,
            'transactionid' => $merchant_reference,
            'paymentmethod' => $payment_method,
        ]);

        return true;
    }

    // ─────────────────────────────────────────────────────────────
    // PUBLIC CONTROLLER METHODS
    // ─────────────────────────────────────────────────────────────

    public function make_payment($invoiceid, $hash)
    {
        check_invoice_restrictions($invoiceid, $hash);

        $this->load->model('invoices_model');
        $invoice = $this->invoices_model->get($invoiceid);
        load_client_language($invoice->clientid);

        $data['invoice'] = $invoice;
        $data['total']   = $this->session->userdata('pesapal_total');

        $creds           = $this->_get_credentials();
        $consumer_key    = $creds['key'];
        $consumer_secret = $creds['secret'];
        $currency        = $this->pesapal_gateway->getSetting('currencies');

        $token  = $params = null;
        $signature_method = new OAuthSignatureMethod_HMAC_SHA1();
        $iframelink       = $this->pesapal_gateway->get_action_url() . 'PostPesapalDirectOrderV4';

        // Resolve contact
        $contact = null;
        if (is_client_logged_in()) {
            $contact = $this->clients_model->get_contact(get_contact_user_id());
        } elseif (total_rows(db_prefix() . 'contacts', ['userid' => $invoice->clientid]) > 0) {
            $contact = $this->clients_model->get_contact(get_primary_contact_user_id($invoice->clientid));
        }

        $data['firstname']   = '';
        $data['lastname']    = '';
        $data['email']       = '';
        $data['phonenumber'] = '';

        if (!empty($contact)) {
            $data['firstname']   = $contact->firstname;
            $data['lastname']    = $contact->lastname;
            $data['email']       = $contact->email;
            $data['phonenumber'] = $contact->phonenumber;
        }

        if (!empty($invoice)) {
            // Format amount — no thousands separator, 2 decimal places
            $amount      = number_format((float) $data['total'], 2, '.', '');
            $desc        = substr('Payment for Invoice: #' . format_invoice_number($invoice->id), 0, 100);
            $type        = $this->pesapal_gateway->getSetting('type');
            $reference   = $this->pesapal_gateway->gen_transaction_id();
            $first_name  = substr(trim($data['firstname']) ?: 'Customer', 0, 30);
            $last_name   = substr(trim($data['lastname'])  ?: 'N/A',      0, 30);
            $email       = trim($data['email']);
            // Strip spaces/dashes/parentheses from phone — keep digits and leading +
            $phonenumber = preg_replace('/[^\d+]/', '', $data['phonenumber']);

            $callback_url = site_url('pesapal/success/' . $invoice->id . '/' . $invoice->hash);

            $post_xml = '<?xml version="1.0" encoding="utf-8"?>'
                . '<PesapalDirectOrderInfo'
                . ' xmlns:xsi="http://www.w3.org/2001/XMLSchemainstance"'
                . ' xmlns:xsd="http://www.w3.org/2001/XMLSchema"'
                . ' Amount="' . $amount . '"'
                . ' Description="' . htmlspecialchars($desc, ENT_XML1) . '"'
                . ' Type="' . $type . '"'
                . ' Reference="' . $reference . '"'
                . ' FirstName="' . htmlspecialchars($first_name, ENT_XML1) . '"'
                . ' LastName="' . htmlspecialchars($last_name, ENT_XML1) . '"'
                . ' Email="' . htmlspecialchars($email, ENT_XML1) . '"'
                . ' PhoneNumber="' . $phonenumber . '"'
                . ' Currency="' . $currency . '"'
                . ' xmlns="http://www.pesapal.com" />';
            $post_xml = htmlentities($post_xml);

            $consumer   = new OAuthConsumer($consumer_key, $consumer_secret);
            $iframe_src = OAuthRequest::from_consumer_and_token($consumer, $token, 'GET', $iframelink, $params);
            $iframe_src->set_parameter('oauth_callback',        $callback_url);
            $iframe_src->set_parameter('pesapal_request_data',  $post_xml);
            $iframe_src->sign_request($signature_method, $consumer, $token);

            $data['iframe_src'] = $iframe_src;
        }

        echo $this->get_html($data);
    }

    public function get_html($data)
    {
        ob_start(); ?>
        <?php echo payment_gateway_head(_l('payment_for_invoice') . ' ' . format_invoice_number($data['invoice']->id)); ?>
        <body class="gateway-pesapal">
          <div class="container">
            <div class="col-md-8 col-md-offset-2 mtop30">
              <div class="mbot30 text-center">
                <?php echo payment_gateway_logo(); ?>
              </div>
              <div class="row">
                <div class="panel_s">
                  <div class="panel-body">
                    <h3 class="no-margin">
                      <b><?php echo _l('payment_for_invoice'); ?> </b>
                      <a href="<?php echo site_url('invoice/' . $data['invoice']->id . '/' . $data['invoice']->hash); ?>">
                        <b><?php echo format_invoice_number($data['invoice']->id); ?></b>
                      </a>
                    </h3>
                    <h4><?php echo _l('payment_total', app_format_money($data['total'], $data['invoice']->currency_name)); ?></h4>
                    <hr />
                    <?php if (!empty($data['iframe_src'])) { ?>
                      <iframe src="<?php echo $data['iframe_src']; ?>" width="100%" height="700px" scrolling="no" frameBorder="0">
                        <p>Browser unable to load iFrame</p>
                      </iframe>
                    <?php } else { ?>
                      <div class="alert alert-warning">Sorry, an error occurred while processing your request. We are working to fix it as soon as we can.</div>
                    <?php } ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php echo payment_gateway_scripts(); ?>
          <?php echo payment_gateway_footer(); ?>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }

    /**
     * Pesapal redirects the customer here after payment.
     * We actively query payment status and mark invoice paid immediately.
     * IPN (below) acts as a server-side backup.
     *
     * URL: /pesapal/success/{invoiceid}/{hash}
     */
    public function success($invoiceid, $hash)
    {
        check_invoice_restrictions($invoiceid, $hash);

        $reference   = $this->input->get('pesapal_merchant_reference',      TRUE);
        $tracking_id = $this->input->get('pesapal_transaction_tracking_id', TRUE);

        if (!empty($reference)) {
            // Store/update the transaction record
            $exists = total_rows(db_prefix() . 'pesapal_txn', ['reference_code' => $reference]);

            if ($exists == 0) {
                $this->db->insert(db_prefix() . 'pesapal_txn', [
                    'reference_code' => $reference,
                    'tracking_id'    => $tracking_id,
                    'txn_date'       => date('Y-m-d H:i:s'),
                    'txn_status'     => 'WAITING',
                    'invoiceid'      => $invoiceid,
                    'amount'         => $this->session->userdata('pesapal_total'),
                ]);
            } elseif (!empty($tracking_id)) {
                // Update tracking_id if we now have one
                $this->db->where('reference_code', $reference);
                $this->db->update(db_prefix() . 'pesapal_txn', ['tracking_id' => $tracking_id]);
            }

            // Actively query Pesapal for current payment status
            if (!empty($tracking_id)) {
                $details = $this->_query_payment_details($reference, $tracking_id);
                $status  = $details['status'];   // e.g. COMPLETED, PENDING, FAILED
                $method  = $details['method'];   // e.g. MPESAKE, AIRTEL, VISA

                if (in_array($status, ['COMPLETED', 'PAID'])) {
                    $marked = $this->_mark_invoice_paid($reference, $tracking_id, $method);
                    if ($marked) {
                        set_alert('success', _l('online_payment_recorded_success'));
                    } else {
                        set_alert('warning', 'Payment received but could not update invoice. Please contact support.');
                    }
                } elseif ($status === 'PENDING') {
                    set_alert('warning', 'Your payment is pending. Your invoice will be updated once payment is confirmed.');
                } else {
                    set_alert('danger', 'Payment could not be confirmed (' . $status . '). Please try again or contact support.');
                }
            } else {
                // No tracking ID yet — IPN will handle update when Pesapal confirms
                set_alert('warning', 'Payment initiated. Your invoice will be updated once the payment is confirmed.');
            }
        } else {
            set_alert('danger', _l('online_payment_recorded_success_fail_database'));
        }

        $this->session->unset_userdata('pesapal_total');
        redirect(site_url('invoice/' . $invoiceid . '/' . $hash));
    }

    /**
     * IPN (Instant Payment Notification) — Pesapal calls this server-to-server.
     * Register this URL in your Pesapal merchant dashboard:
     *   https://xetuu.com/pesapal/ipn
     *
     * URL: /pesapal/ipn
     */
    public function ipn()
    {
        $notification_type  = $this->input->get('pesapal_notification_type',      TRUE);
        $tracking_id        = $this->input->get('pesapal_transaction_tracking_id', TRUE);
        $merchant_reference = $this->input->get('pesapal_merchant_reference',      TRUE);
        $payment_method     = $this->input->get('payment_method',                  TRUE) ?: 'Pesapal';

        if ($notification_type === 'CHANGE' && !empty($tracking_id) && !empty($merchant_reference)) {
            $details = $this->_query_payment_details($merchant_reference, $tracking_id);
            $status  = $details['status'];
            $method  = $details['method'] ?: $payment_method;

            if (in_array($status, ['COMPLETED', 'PAID'])) {
                $this->_mark_invoice_paid($merchant_reference, $tracking_id, $method);
            }

            // Acknowledge the IPN to Pesapal (required)
            $resp = "pesapal_notification_type={$notification_type}"
                  . "&pesapal_transaction_tracking_id={$tracking_id}"
                  . "&pesapal_merchant_reference={$merchant_reference}";
            ob_start();
            echo $resp;
            ob_flush();
            exit;
        }
    }

    public function failure()
    {
        $invoiceid = $this->input->get('invoiceid', TRUE);
        $hash      = $this->input->get('hash',      TRUE);

        check_invoice_restrictions($invoiceid, $hash);
        set_alert('warning', _l('invalid_transaction'));
        redirect(site_url('invoice/' . $invoiceid . '/' . $hash));
    }
}