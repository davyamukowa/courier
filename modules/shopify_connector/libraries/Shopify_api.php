<?php
/**
 * c:\wamp64\www\perfex_crm\modules\shopify_connector\libraries\Shopify_api.php
 *
 * Usage example:
 * $this->load->library('shopify_connector/shopify_api', [
 *     'shop_domain' => 'salibay.myshopify.com', 
 *     'access_token' => 'shpat_xxxxx'
 * ]);
 * 
 * $shop_response = $this->shopify_api->get_shop();
 * if ($shop_response['success']) {
 *     $shop = $shop_response['data']['shop'];
 * }
 */

defined('BASEPATH') or exit('No direct script access allowed');

class Shopify_api
{
    private $shop_domain;
    private $access_token;
    private $api_version;
    private $base_url;

    /**
     * Constructor
     * Works with both direct instantiation and CodeIgniter's load->library (which passes an array)
     */
    public function __construct($shop_domain = '', $access_token = null, $api_version = '2024-01')
    {
        if (is_array($shop_domain)) {
            $params = $shop_domain;
            $this->shop_domain = $params['shop_domain'] ?? '';
            $this->access_token = $params['access_token'] ?? '';
            $this->api_version = $params['api_version'] ?? '2024-01';
        } else {
            $this->shop_domain = $shop_domain;
            $this->access_token = $access_token;
            $this->api_version = $api_version;
        }

        $this->base_url = rtrim("https://{$this->shop_domain}/admin/api/{$this->api_version}/", '/') . '/';
    }

    private function request($method, $endpoint, $data = null)
    {
        $url = $this->base_url . ltrim($endpoint, '/');
        
        $ch = curl_init();
        
        $headers = [
            "X-Shopify-Access-Token: {$this->access_token}",
            "Content-Type: application/json",
            "Accept: application/json"
        ];
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true); // Required for parsing Link headers for pagination
        
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data !== null) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data !== null) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'GET':
                if (!empty($data) && is_array($data)) {
                    $url .= '?' . http_build_query($data);
                }
                break;
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        
        $attempts = 0;
        $max_attempts = 4; // 1 initial + up to 3 retries
        
        do {
            $attempts++;
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $curl_error = curl_error($ch);
            
            if ($http_code == 429 && $attempts < $max_attempts) {
                $this->log_api_call('warning', "Rate limit hit (429) on {$endpoint}. Retrying...", ['attempt' => $attempts]);
                usleep(500000); // Sleep for 0.5 seconds
                continue;
            }
            break; // Break if not 429 or max attempts reached
        } while (true);
        
        curl_close($ch);

        $header_str = substr($response, 0, $header_size);
        $body_str = substr($response, $header_size);
        
        $success = ($http_code >= 200 && $http_code < 300);
        $decoded_body = json_decode($body_str, true);
        $error_msg = null;
        
        if (!$success) {
            $error_msg = $curl_error ?: ($decoded_body['errors'] ?? "HTTP Error {$http_code}");
            if (is_array($error_msg)) {
                $error_msg = json_encode($error_msg);
            }
            $this->log_api_call('error', "API Request failed: {$method} {$endpoint}", [
                'http_code' => $http_code,
                'error' => $error_msg,
                'body' => $body_str
            ]);
        } else {
            // Log successful calls at debug level
            $this->log_api_call('debug', "API Request success: {$method} {$endpoint}", ['http_code' => $http_code]);
        }
        
        // Parse link headers for pagination
        $link_header = null;
        if (preg_match('/Link: (.*)/i', $header_str, $matches)) {
            $link_header = trim($matches[1]);
        }
        
        return [
            'success'   => $success,
            'data'      => $decoded_body,
            'http_code' => $http_code,
            'error'     => $error_msg,
            'link_header' => $link_header
        ];
    }

    private function parse_next_page_info($link_header)
    {
        if (!$link_header) {
            return null;
        }
        
        $links = explode(',', $link_header);
        foreach ($links as $link) {
            if (strpos($link, 'rel="next"') !== false) {
                // Extract URL between < and >
                if (preg_match('/<([^>]+)>/', $link, $matches)) {
                    $url = $matches[1];
                    $parts = parse_url($url);
                    if (isset($parts['query'])) {
                        parse_str($parts['query'], $query);
                        return $query['page_info'] ?? null;
                    }
                }
            }
        }
        return null;
    }

    // -------------------------------------------------------------
    // PUBLIC METHODS — SHOP
    // -------------------------------------------------------------
    public function get_shop()
    {
        return $this->request('GET', 'shop.json');
    }

    // -------------------------------------------------------------
    // PUBLIC METHODS — ORDERS
    // -------------------------------------------------------------
    public function get_order($order_id)
    {
        return $this->request('GET', "orders/{$order_id}.json");
    }

    public function list_orders($params = [])
    {
        return $this->request('GET', 'orders.json', $params);
    }

    public function get_order_count($params = [])
    {
        return $this->request('GET', 'orders/count.json', $params);
    }

    // -------------------------------------------------------------
    // PUBLIC METHODS — FULFILLMENTS
    // -------------------------------------------------------------
    public function create_fulfillment($order_id, $data)
    {
        return $this->request('POST', "orders/{$order_id}/fulfillments.json", $data);
    }

    public function update_fulfillment($order_id, $fulfillment_id, $data)
    {
        return $this->request('PUT', "orders/{$order_id}/fulfillments/{$fulfillment_id}.json", $data);
    }

    public function get_fulfillment_orders($order_id)
    {
        return $this->request('GET', "orders/{$order_id}/fulfillment_orders.json");
    }

    // -------------------------------------------------------------
    // PUBLIC METHODS — PRODUCTS
    // -------------------------------------------------------------
    public function list_products($params = ['limit' => 250])
    {
        $all_products = [];
        $page_info = null;
        
        do {
            $current_params = $params;
            if ($page_info) {
                // When passing page_info, Shopify requires we only pass limit alongside it
                $current_params = [
                    'limit' => $params['limit'] ?? 250, 
                    'page_info' => $page_info
                ];
            }
            
            $result = $this->request('GET', 'products.json', $current_params);
            
            if (!$result['success']) {
                // If it fails, return the accumulated so far and the error
                return [
                    'success' => false,
                    'data' => ['products' => $all_products],
                    'error' => $result['error']
                ];
            }
            
            if (!empty($result['data']['products'])) {
                $all_products = array_merge($all_products, $result['data']['products']);
            }
            
            $page_info = $this->parse_next_page_info($result['link_header']);
            
        } while ($page_info);
        
        return [
            'success' => true,
            'data' => ['products' => $all_products]
        ];
    }

    public function get_product($product_id)
    {
        return $this->request('GET', "products/{$product_id}.json");
    }

    public function get_variant($variant_id)
    {
        return $this->request('GET', "variants/{$variant_id}.json");
    }

    // -------------------------------------------------------------
    // PUBLIC METHODS — INVENTORY
    // -------------------------------------------------------------
    public function get_inventory_levels($params = [])
    {
        return $this->request('GET', 'inventory_levels.json', $params);
    }

    public function set_inventory_level($inventory_item_id, $location_id, $qty)
    {
        $data = [
            'location_id' => $location_id,
            'inventory_item_id' => $inventory_item_id,
            'available' => $qty
        ];
        return $this->request('POST', 'inventory_levels/set.json', $data);
    }

    public function adjust_inventory_level($inventory_item_id, $location_id, $delta)
    {
        $data = [
            'location_id' => $location_id,
            'inventory_item_id' => $inventory_item_id,
            'available_adjustment' => $delta
        ];
        return $this->request('POST', 'inventory_levels/adjust.json', $data);
    }

    public function list_locations()
    {
        return $this->request('GET', 'locations.json');
    }

    public function get_inventory_item($id)
    {
        return $this->request('GET', "inventory_items/{$id}.json");
    }

    public function get_inventory_items($ids)
    {
        $ids = is_array($ids) ? implode(',', $ids) : $ids;
        return $this->request('GET', 'inventory_items.json', ['ids' => $ids]);
    }

    // -------------------------------------------------------------
    // PUBLIC METHODS — WEBHOOKS
    // -------------------------------------------------------------
    public function list_webhooks()
    {
        return $this->request('GET', 'webhooks.json');
    }

    public function create_webhook($topic, $address)
    {
        $data = [
            'webhook' => [
                'topic' => $topic,
                'address' => $address,
                'format' => 'json'
            ]
        ];
        return $this->request('POST', 'webhooks.json', $data);
    }

    public function delete_webhook($webhook_id)
    {
        return $this->request('DELETE', "webhooks/{$webhook_id}.json");
    }

    // -------------------------------------------------------------
    // PUBLIC METHODS — REFUNDS
    // -------------------------------------------------------------
    public function get_refunds($order_id)
    {
        return $this->request('GET', "orders/{$order_id}/refunds.json");
    }

    public function create_refund($order_id, $data)
    {
        return $this->request('POST', "orders/{$order_id}/refunds.json", $data);
    }

    // -------------------------------------------------------------
    // STATIC UTILITIES
    // -------------------------------------------------------------
    public static function validate_webhook_hmac($secret, $raw_body, $hmac_header)
    {
        $calculated_hmac = base64_encode(hash_hmac('sha256', $raw_body, $secret, true));
        return hash_equals($calculated_hmac, $hmac_header);
    }

    // -------------------------------------------------------------
    // PRIVATE LOGGER
    // -------------------------------------------------------------
    private function log_api_call($level, $message, $context = [])
    {
        if (class_exists('CI_Controller')) {
            $CI = get_instance();
            if (isset($CI->db)) {
                $CI->db->insert(db_prefix() . 'shopify_integration_logs', [
                    'log_level'  => $level,
                    'category'   => 'api_request',
                    'message'    => $message,
                    'context'    => json_encode($context),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }
}
