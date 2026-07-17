<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Webhook Service
 * 
 * Manages webhook delivery and logging
 */
class Api_Webhook_Service
{
    private $CI;
    
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }
    
    /**
     * Send webhook for an event
     * 
     * @param string $event Event name
     * @param array $data Event data
     * @return void
     */
    public function triggerWebhooks($event, $data)
    {
        // Get active webhooks for this event
        $webhooks = $this->getWebhooksForEvent($event);
        
        foreach ($webhooks as $webhook) {
            $this->sendWebhook($webhook, $event, $data);
        }
    }
    
    /**
     * Get webhooks that should be triggered for an event
     * 
     * @param string $event Event name
     * @return array
     */
    private function getWebhooksForEvent($event)
    {
        $this->CI->db->where('active', 1);
        $webhooks = $this->CI->db->get(db_prefix() . 'api_webhooks')->result_array();
        
        $matching = [];
        foreach ($webhooks as $webhook) {
            $events = explode(',', $webhook['events']);
            $events = array_map('trim', $events);
            
            // Support wildcard matching
            if (in_array('*', $events) || in_array($event, $events)) {
                $matching[] = $webhook;
            }
        }
        
        return $matching;
    }
    
    /**
     * Send a webhook
     * 
     * @param array $webhook Webhook configuration
     * @param string $event Event name
     * @param array $data Event data
     * @return bool
     */
    public function sendWebhook($webhook, $event, $data)
    {
        $payload = [
            'event' => $event,
            'timestamp' => time(),
            'data' => $data
        ];
        
        // Add webhook signature if secret is set
        if (!empty($webhook['secret'])) {
            $payload['signature'] = $this->generateSignature($payload, $webhook['secret']);
        }
        
        // Parse custom headers
        $headers = [];
        if (!empty($webhook['headers'])) {
            $customHeaders = json_decode($webhook['headers'], true);
            if (is_array($customHeaders)) {
                $headers = $customHeaders;
            }
        }
        $headers['Content-Type'] = 'application/json';
        $headers['User-Agent'] = 'Perfex-CRM-API/1.0';
        
        // Log webhook attempt
        $logId = $this->logWebhookAttempt($webhook['id'], $event, $webhook['url'], $payload);
        
        // Send webhook
        $success = false;
        $responseCode = null;
        $responseBody = null;
        $errorMessage = null;
        
        try {
            // Use cURL directly since CodeIgniter's curl library may not be available
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $webhook['url']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->formatHeaders($headers));
            curl_setopt($ch, CURLOPT_TIMEOUT, isset($webhook['timeout']) ? (int)$webhook['timeout'] : 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $responseBody = curl_exec($ch);
            $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_errno($ch)) {
                $errorMessage = curl_error($ch);
            } else {
                // Consider 2xx status codes as success
                $success = ($responseCode >= 200 && $responseCode < 300);
                
                if (!$success) {
                    $errorMessage = "HTTP {$responseCode}: " . substr($responseBody, 0, 500);
                }
            }
            
            curl_close($ch);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }
        
        // Update log
        $this->updateWebhookLog($logId, $success, $responseCode, $responseBody, $errorMessage);
        
        // Update webhook statistics
        $this->updateWebhookStats($webhook['id'], $success);
        
        return $success;
    }
    
    /**
     * Format headers for cURL
     * 
     * @param array $headers Headers array
     * @return array Formatted headers
     */
    private function formatHeaders($headers)
    {
        $formatted = [];
        foreach ($headers as $key => $value) {
            $formatted[] = $key . ': ' . $value;
        }
        return $formatted;
    }
    
    /**
     * Generate webhook signature
     * 
     * @param array $payload Payload data
     * @param string $secret Secret key
     * @return string
     */
    private function generateSignature($payload, $secret)
    {
        $payloadString = json_encode($payload);
        return hash_hmac('sha256', $payloadString, $secret);
    }
    
    /**
     * Log webhook attempt
     * 
     * @param int $webhookId Webhook ID
     * @param string $event Event name
     * @param string $url Webhook URL
     * @param array $payload Payload data
     * @return int Log ID
     */
    private function logWebhookAttempt($webhookId, $event, $url, $payload)
    {
        $this->CI->db->insert(db_prefix() . 'api_webhook_logs', [
            'webhook_id' => $webhookId,
            'event' => $event,
            'url' => $url,
            'payload' => json_encode($payload),
            'status' => 'pending',
            'attempt_number' => 1
        ]);
        
        return $this->CI->db->insert_id();
    }
    
    /**
     * Update webhook log
     * 
     * @param int $logId Log ID
     * @param bool $success Success status
     * @param int $responseCode HTTP response code
     * @param string $responseBody Response body
     * @param string $errorMessage Error message
     * @return void
     */
    private function updateWebhookLog($logId, $success, $responseCode, $responseBody, $errorMessage)
    {
        $this->CI->db->where('id', $logId);
        $this->CI->db->update(db_prefix() . 'api_webhook_logs', [
            'status' => $success ? 'success' : 'failed',
            'response_code' => $responseCode,
            'response_body' => $responseBody ? substr($responseBody, 0, 1000) : null,
            'error_message' => $errorMessage
        ]);
    }
    
    /**
     * Update webhook statistics
     * 
     * @param int $webhookId Webhook ID
     * @param bool $success Success status
     * @return void
     */
    private function updateWebhookStats($webhookId, $success)
    {
        $field = $success ? 'success_count' : 'failure_count';
        $this->CI->db->set($field, $field . ' + 1', false);
        $this->CI->db->set('last_triggered', date('Y-m-d H:i:s'));
        $this->CI->db->where('id', $webhookId);
        $this->CI->db->update(db_prefix() . 'api_webhooks');
    }
}
