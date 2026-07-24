<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Fulfilment extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (!has_permission('courier_goshipping', '', 'view')) {
            access_denied('Courier GoShipping');
        }

        $this->load->helper('courier_goshipping/courier');
        $this->load->model('shopify_connector/shopify_connector_model');

        if (!$this->db->table_exists(db_prefix() . 'shopify_orders')) {
            require_once(module_dir_path('shopify_connector', 'install.php'));
        }

        // courier_branch_id was referenced throughout this controller and
        // shopify_connector's webhook handler but was never actually added
        // to the schema — self-heal it here so both local and live pick it
        // up without needing a manual module reactivation.
        if (
            $this->db->table_exists(db_prefix() . 'shopify_product_mappings')
            && !$this->db->field_exists('courier_branch_id', db_prefix() . 'shopify_product_mappings')
        ) {
            $this->db->query('ALTER TABLE `' . db_prefix() . 'shopify_product_mappings` ADD `courier_branch_id` INT NULL;');
        }
    }

    public function index()
    {
        redirect(admin_url('courier_goshipping/fulfilment/dashboard'));
    }

    public function dashboard()
    {
        $data = $this->build_base_data('dashboard', 'Salibay Fulfilment Dashboard');
        $data['recent_orders'] = $this->db->select('id, shopify_order_number, customer_name, order_status, total_price, currency, gs_shipment_id, tracking_number, created_at')
            ->from(db_prefix() . 'shopify_orders')
            ->order_by('id', 'DESC')
            ->limit(8)
            ->get()
            ->result();

        $data['group_content'] = $this->load->view('courier_goshipping/fulfilment/dashboard', $data, true);
        $this->load->view('courier_goshipping/fulfilment/main', $data);
    }

    public function orders()
    {
        $data = $this->build_base_data('orders', 'Salibay Orders');
        $data['prefilter_status'] = $this->input->get('status', true) ?: '';
        $data['group_content'] = $this->load->view('courier_goshipping/fulfilment/orders', $data, true);
        $this->load->view('courier_goshipping/fulfilment/main', $data);
    }

    public function salibay_order_list()
    {
        $data = $this->build_base_data('orders', 'Salibay Order List');
        $data['salibay_orders'] = $this->get_salibay_orders_for_list();
        $data['group_content'] = $this->load->view('courier_goshipping/fulfilment/salibay_order_list', $data, true);
        $this->load->view('courier_goshipping/fulfilment/main', $data);
    }

    public function get_salibay_order_list_datatable()
    {
        $prefix = db_prefix();
        $start = (int) $this->input->post('start');
        $length = (int) $this->input->post('length');
        $search = $this->input->post('search')['value'] ?? '';
        $shopify_orders_has_branch = $this->db->field_exists('branch_id', "{$prefix}shopify_orders");
        $shipment_status_description = $this->db->field_exists('status_description', "{$prefix}_shipment_statuses")
            ? 'ss.status_description'
            : 'ss.description';

        $base_sql = "
            FROM {$prefix}shopify_orders so
            LEFT JOIN {$prefix}_shipments s ON s.id = so.gs_shipment_id
            LEFT JOIN {$prefix}_shipment_statuses ss ON ss.id = s.status_id
            LEFT JOIN {$prefix}_shipment_senders sender ON sender.id = s.sender_id
            LEFT JOIN {$prefix}_shipment_recipients recipient ON recipient.id = s.recipient_id
            WHERE 1=1
        ";
        $base_sql .= $this->branch_scope_sql_clause('so', $shopify_orders_has_branch);

        $where = '';
        $params = [];
        if ($search !== '') {
            $where = ' AND (
                so.shopify_order_number LIKE ?
                OR so.customer_email LIKE ?
                OR so.tracking_number LIKE ?
                OR so.order_status LIKE ?
                OR so.financial_status LIKE ?
                OR
                s.waybill_number LIKE ?
                OR s.tracking_id LIKE ?
                OR recipient.first_name LIKE ?
                OR recipient.last_name LIKE ?
                OR so.customer_name LIKE ?
            )';
            $like = '%' . $search . '%';
            $params = [$like, $like, $like, $like, $like, $like, $like, $like, $like, $like];
        }

        $total_records = (int) $this->db->query("SELECT COUNT(*) AS c {$base_sql}")->row()->c;
        $total_filtered = (int) $this->db->query("SELECT COUNT(*) AS c {$base_sql} {$where}", $params)->row()->c;

        $limit_sql = $length > -1 ? ' LIMIT ' . $start . ', ' . $length : '';
        $rows = $this->db->query("
            SELECT
                so.id AS order_id, so.shopify_order_number, so.customer_name, so.customer_email, so.order_status,
                so.financial_status, so.tracking_number AS order_tracking_number, so.created_at AS order_created_at,
                so.gs_shipment_id,
                s.id AS shipment_id, s.waybill_number, s.tracking_id, s.shipping_mode, s.created_at AS shipment_created_at,
                ss.status_name, {$shipment_status_description} AS status_description,
                sender.first_name AS sender_first_name, sender.last_name AS sender_last_name,
                recipient.first_name AS recipient_first_name, recipient.last_name AS recipient_last_name
            {$base_sql}
            {$where}
            ORDER BY so.created_at DESC
            {$limit_sql}
        ", $params)->result();

        $data = [];
        foreach ($rows as $row) {
            $order_url = 'javascript:void(0);';
            $order_number = '#' . htmlspecialchars((string) $row->shopify_order_number);

            $shipment_label = '<span class="text-muted">Not created</span>';
            $shipment_action = '<button type="button" class="btn btn-default btn-xs" onclick="createSalibayShipment(' . (int) $row->order_id . '); return false;"><i class="fa fa-truck"></i> Create Shipment</button>';
            if (!empty($row->shipment_id)) {
                $waybill = htmlspecialchars((string) ($row->waybill_number ?: $row->tracking_id ?: $row->order_tracking_number ?: ('#' . $row->shipment_id)));
                $waybill_url = admin_url('courier_goshipping/shipments/waybill/' . $row->shipment_id);
                $order_url = $waybill_url;
                $shipment_label = '<a href="' . $waybill_url . '" style="font-weight:700;">' . $waybill . '</a>';
                $shipment_action = '<a href="' . $waybill_url . '" class="btn btn-default btn-xs" title="Open Waybill"><i class="fa fa-eye"></i></a>';
            }

            $sender = trim((string) $row->sender_first_name . ' ' . (string) $row->sender_last_name) ?: 'Go Shipping Warehouse';
            $recipient = trim((string) $row->recipient_first_name . ' ' . (string) $row->recipient_last_name) ?: (string) $row->customer_name;

            $order_badge_class = $this->order_badge_class($row->order_status);
            $financial_badge_class = $this->financial_badge_class($row->financial_status);
            $shipment_badge_class = $this->shipment_badge_class($row->status_name);
            $shipment_status_text = (string) ($row->status_description ?: (!empty($row->shipment_id) ? 'Shipment Created' : 'Awaiting Shipment'));
            $shipment_status = '<span class="label label-' . $shipment_badge_class . '">' . htmlspecialchars($shipment_status_text) . '</span>';

            $tracking_number = (string) ($row->order_tracking_number ?: $row->tracking_id ?: '-');
            $tracking_cell = $tracking_number !== '-'
                ? '<span class="text-info">' . htmlspecialchars($tracking_number) . '</span>'
                : '<span class="text-muted">-</span>';

            $data[] = [
                '<span class="hide">' . htmlspecialchars((string) $row->order_id) . '</span><a href="' . $order_url . '" style="font-weight:700;">' . $order_number . '</a>',
                htmlspecialchars((string) $row->customer_name),
                '<span class="label label-' . $order_badge_class . '">' . ucfirst((string) $row->order_status) . '</span>',
                '<span class="label label-' . $financial_badge_class . '">' . ucfirst((string) ($row->financial_status ?: 'unknown')) . '</span>',
                $shipment_status,
                $shipment_label,
                htmlspecialchars($sender),
                htmlspecialchars($recipient),
                htmlspecialchars((string) ($row->shipping_mode ?: '-')),
                $tracking_cell,
                _dt($row->order_created_at),
                $shipment_action,
            ];
        }

        echo json_encode([
            'draw' => $this->input->post('draw'),
            'recordsTotal' => $total_records,
            'recordsFiltered' => $total_filtered,
            'data' => $data,
        ]);
    }

    public function inventory()
    {
        $data = $this->build_base_data('inventory', 'Virtual Warehouse Inventory');
        $data['group_content'] = $this->load->view('courier_goshipping/fulfilment/inventory', $data, true);
        $this->load->view('courier_goshipping/fulfilment/main', $data);
    }

    public function health()
    {
        $data = $this->build_base_data('health', 'Integration Health');
        $data['group_content'] = $this->load->view('courier_goshipping/fulfilment/health', $data, true);
        $this->load->view('courier_goshipping/fulfilment/main', $data);
    }

    public function riders()
    {
        $data = $this->build_base_data('riders', 'Riders');

        $prefix = db_prefix();
        if (!$this->db->table_exists($prefix . '_courier_riders')) {
            $data['riders'] = [];
            $data['unlinked_drivers'] = [];
            $data['group_content'] = $this->load->view('courier_goshipping/fulfilment/riders', $data, true);
            $this->load->view('courier_goshipping/fulfilment/main', $data);
            return;
        }

        $rows = $this->db->select('r.id, r.name, r.phone, r.status, r.staff_id, r.created_at, staff.firstname, staff.lastname, staff.active AS staff_active')
            ->from($prefix . '_courier_riders r')
            ->join($prefix . 'staff staff', 'staff.staffid = r.staff_id', 'left')
            ->order_by('r.created_at', 'desc')
            ->get()
            ->result();

        foreach ($rows as $row) {
            $row->linked = !empty($row->staff_id);
            $row->staff_display = $row->linked
                ? (trim((string) $row->firstname . ' ' . (string) $row->lastname) ?: ('Staff #' . $row->staff_id))
                : null;
        }

        $data['riders'] = $rows;
        $data['unlinked_drivers'] = $this->db->select('staff.staffid, staff.firstname, staff.lastname, staff.phonenumber')
            ->from($prefix . 'staff staff')
            ->join($prefix . 'roles roles', 'roles.roleid = staff.role')
            ->where('roles.name', 'Fleet: Driver')
            ->get()
            ->result();

        $data['group_content'] = $this->load->view('courier_goshipping/fulfilment/riders', $data, true);
        $this->load->view('courier_goshipping/fulfilment/main', $data);
    }

    public function link_rider($rider_id)
    {
        if (!$this->can_manage_fulfilment()) {
            access_denied('Salibay Fulfilment');
        }

        $staff_id = (int) $this->input->post('staff_id');
        if ($staff_id > 0) {
            $this->db->where('id', (int) $rider_id)->update(db_prefix() . '_courier_riders', ['staff_id' => $staff_id]);
            set_alert('success', 'Rider linked to driver profile.');
        }
        redirect(admin_url('courier_goshipping/fulfilment/riders'));
    }

    public function toggle_rider_status($rider_id)
    {
        if (!$this->can_manage_fulfilment()) {
            access_denied('Salibay Fulfilment');
        }

        $rider = $this->db->where('id', (int) $rider_id)->get(db_prefix() . '_courier_riders')->row();
        if ($rider) {
            $new_status = $rider->status === 'active' ? 'suspended' : 'active';
            $this->db->where('id', (int) $rider_id)->update(db_prefix() . '_courier_riders', ['status' => $new_status]);
            set_alert('success', 'Rider account ' . $new_status . '.');
        }
        redirect(admin_url('courier_goshipping/fulfilment/riders'));
    }

    public function settings()
    {
        if (!$this->can_manage_fulfilment()) {
            access_denied('Salibay Fulfilment Settings');
        }

        $data = $this->build_base_data('settings', 'Connector Settings');
        $data['store'] = $this->shopify_connector_model->get_store();
        $data['webhooks'] = [];
        if ($data['store']) {
            $data['webhooks'] = $this->db->where('store_id', $data['store']->id)->get(db_prefix() . 'shopify_webhooks')->result();
        }
        $data['staff_members'] = $this->db->where('active', 1)->order_by('firstname', 'asc')->get(db_prefix() . 'staff')->result();
        $data['advanced'] = [
            'auto_process_orders' => get_option('shopify_auto_process_orders'),
            'auto_create_shipments' => get_option('shopify_auto_create_shipments'),
            'auto_post_accounting' => get_option('shopify_auto_post_accounting'),
            'notification_sms' => get_option('shopify_notification_sms'),
            'notification_sms_sender' => get_option('shopify_notification_sms_sender'),
            'notification_email' => get_option('shopify_notification_email'),
            'notification_email_name' => get_option('shopify_notification_email_name'),
            'notification_email_address' => get_option('shopify_notification_email_address'),
            'inventory_sync_direction' => get_option('shopify_inventory_sync_direction'),
            'low_stock_threshold' => get_option('shopify_low_stock_threshold'),
            'default_staff_id' => get_option('shopify_default_staff_id'),
            'public_webhook_url' => get_option('shopify_public_webhook_url'),
            'virtual_warehouse_name' => get_option('shopify_virtual_warehouse_name') ?: 'Salibay Virtual Fulfilment Warehouse',
        ];
        $data['webhook_endpoint'] = $this->get_shopify_webhook_endpoint();
        $data['branches'] = $this->db->where('is_active', 1)->order_by('name', 'asc')->get(db_prefix() . '_courier_branches')->result();
        $data['shopify_locations'] = $this->db->order_by('name', 'asc')->get(db_prefix() . '_courier_shopify_locations')->result();

        $data['route_branch_map'] = [];
        if ($this->db->table_exists(db_prefix() . 'courier_route_branch_map')) {
            $data['route_branch_map'] = $this->db->select('m.id, m.route_tag, m.branch_id, b.name AS branch_name')
                ->from(db_prefix() . 'courier_route_branch_map m')
                ->join(db_prefix() . '_courier_branches b', 'b.id = m.branch_id', 'left')
                ->order_by('m.route_tag', 'asc')
                ->get()
                ->result();
        }

        $data['group_content'] = $this->load->view('courier_goshipping/fulfilment/settings', $data, true);
        $this->load->view('courier_goshipping/fulfilment/main', $data);
    }

    // ── Maps a sourcing-app "Route GSC-AE-DXB" style order tag to the Go
    // Shipping branch that should fulfil it (see Shopify_connector::
    // resolve_branch_from_route_tag()) ──────────────────────────────────────
    public function save_route_branch_map()
    {
        if (!$this->can_manage_fulfilment()) {
            access_denied('Salibay Fulfilment Settings');
        }

        $route_tag = trim((string) $this->input->post('route_tag'));
        $branch_id = (int) $this->input->post('branch_id');

        if ($route_tag === '' || !$branch_id) {
            set_alert('danger', 'Please enter a route tag and select a branch.');
            redirect(admin_url('courier_goshipping/fulfilment/settings#route_map'));
            return;
        }

        if (!$this->db->table_exists(db_prefix() . 'courier_route_branch_map')) {
            $this->db->query("CREATE TABLE `" . db_prefix() . "courier_route_branch_map` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `route_tag` VARCHAR(100) NOT NULL UNIQUE,
                `branch_id` INT NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $this->db->char_set . ";");
        }

        $existing = $this->db->where('route_tag', $route_tag)->get(db_prefix() . 'courier_route_branch_map')->row();
        if ($existing) {
            $this->db->where('id', $existing->id)->update(db_prefix() . 'courier_route_branch_map', ['branch_id' => $branch_id]);
        } else {
            $this->db->insert(db_prefix() . 'courier_route_branch_map', [
                'route_tag' => $route_tag,
                'branch_id' => $branch_id,
            ]);
        }

        set_alert('success', 'Route mapping saved.');
        redirect(admin_url('courier_goshipping/fulfilment/settings#route_map'));
    }

    public function delete_route_branch_map($id)
    {
        if (!$this->can_manage_fulfilment()) {
            access_denied('Salibay Fulfilment Settings');
        }

        $this->db->where('id', (int) $id)->delete(db_prefix() . 'courier_route_branch_map');
        set_alert('success', 'Route mapping removed.');
        redirect(admin_url('courier_goshipping/fulfilment/settings#route_map'));
    }

    public function save_settings()
    {
        if (!$this->can_manage_fulfilment()) {
            access_denied('Salibay Fulfilment Settings');
        }

        $post_data = $this->input->post(null, true);

        if (isset($post_data['store_settings'])) {
            $store_data = [
                'shop_domain' => trim($post_data['shop_domain']),
                'api_key' => trim($post_data['api_key']),
                'api_secret' => trim($post_data['api_secret']),
                'access_token' => trim($post_data['access_token']),
                'webhook_secret' => trim($post_data['webhook_secret']),
                'api_version' => $post_data['api_version'],
                'default_fulfillment_model' => $post_data['default_fulfillment_model'],
            ];
            $this->shopify_connector_model->save_store($store_data);
            update_option('shopify_virtual_warehouse_name', trim($post_data['virtual_warehouse_name'] ?? 'Salibay Virtual Fulfilment Warehouse'));
            $this->ensure_virtual_warehouse(trim($post_data['virtual_warehouse_name'] ?? 'Salibay Virtual Fulfilment Warehouse'));
            set_alert('success', 'Store settings saved successfully.');
        }

        if (isset($post_data['advanced_settings'])) {
            update_option('shopify_auto_process_orders', $post_data['auto_process_orders'] ?? 0);
            update_option('shopify_auto_create_shipments', $post_data['auto_create_shipments'] ?? 0);
            update_option('shopify_auto_post_accounting', $post_data['auto_post_accounting'] ?? 0);
            update_option('shopify_notification_sms', $post_data['notification_sms'] ?? 0);
            update_option('shopify_notification_sms_sender', $post_data['notification_sms_sender'] ?? '');
            update_option('shopify_notification_email', $post_data['notification_email'] ?? 0);
            update_option('shopify_notification_email_name', $post_data['notification_email_name'] ?? '');
            update_option('shopify_notification_email_address', $post_data['notification_email_address'] ?? '');
            update_option('shopify_inventory_sync_direction', $post_data['inventory_sync_direction'] ?? 'Disabled');
            update_option('shopify_low_stock_threshold', $post_data['low_stock_threshold'] ?? 10);
            update_option('shopify_default_staff_id', $post_data['default_staff_id'] ?? '');
            update_option('shopify_public_webhook_url', trim($post_data['public_webhook_url'] ?? ''));
            update_option('shopify_virtual_warehouse_name', trim($post_data['virtual_warehouse_name'] ?? 'Salibay Virtual Fulfilment Warehouse'));
            $this->ensure_virtual_warehouse(trim($post_data['virtual_warehouse_name'] ?? 'Salibay Virtual Fulfilment Warehouse'));
            set_alert('success', 'Advanced settings saved successfully.');
        }

        redirect(admin_url('courier_goshipping/fulfilment/settings'));
    }

    public function register_webhooks()
    {
        if (!$this->can_manage_fulfilment()) {
            access_denied('Salibay Fulfilment Settings');
        }

        $store = $this->shopify_connector_model->get_store();
        if (!$store) {
            set_alert('danger', 'Store credentials missing.');
            redirect(admin_url('courier_goshipping/fulfilment/settings'));
        }

        $topics = [
            'orders/create',
            'orders/updated',
            'orders/cancelled',
            'orders/paid',
            'refunds/create',
            'products/update',
            'inventory_items/update',
        ];

        $endpoint = $this->get_shopify_webhook_endpoint();
        if (stripos($endpoint, 'https://') !== 0 || preg_match('/\/\/(localhost|127\.0\.0\.1)(:|\/)/i', $endpoint)) {
            set_alert('danger', 'A public HTTPS webhook URL is required before registering webhooks.');
            redirect(admin_url('courier_goshipping/fulfilment/settings'));
        }

        $existing_topics = [];
        $url = "https://{$store->shop_domain}/admin/api/{$store->api_version}/webhooks.json";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "X-Shopify-Access-Token: {$store->access_token}",
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            $data = json_decode($response, true);
            foreach ($data['webhooks'] ?? [] as $webhook) {
                if (strpos($webhook['address'], 'shopify_connector/webhook') !== false) {
                    $existing_topics[] = $webhook['topic'];
                    $existing = $this->db->where('store_id', $store->id)
                        ->where('topic', $webhook['topic'])
                        ->get(db_prefix() . 'shopify_webhooks')
                        ->row();

                    $payload = [
                        'shopify_webhook_id' => $webhook['id'],
                        'address' => $webhook['address'],
                        'is_active' => 1,
                    ];

                    if ($existing) {
                        $this->db->where('id', $existing->id)->update(db_prefix() . 'shopify_webhooks', $payload);
                    } else {
                        $payload['store_id'] = $store->id;
                        $payload['topic'] = $webhook['topic'];
                        $payload['created_at'] = date('Y-m-d H:i:s');
                        $this->db->insert(db_prefix() . 'shopify_webhooks', $payload);
                    }
                }
            }
        }

        $success_count = 0;
        foreach ($topics as $topic) {
            if (in_array($topic, $existing_topics, true)) {
                $success_count++;
                continue;
            }

            $payload = json_encode([
                'webhook' => [
                    'topic' => $topic,
                    'address' => $endpoint,
                    'format' => 'json',
                ],
            ]);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                "X-Shopify-Access-Token: {$store->access_token}",
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code == 201 || $http_code == 200) {
                $res_data = json_decode($response, true);
                $webhook_id = $res_data['webhook']['id'] ?? null;
                if ($webhook_id) {
                    $this->db->insert(db_prefix() . 'shopify_webhooks', [
                        'store_id' => $store->id,
                        'shopify_webhook_id' => $webhook_id,
                        'topic' => $topic,
                        'address' => $endpoint,
                        'is_active' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
                $success_count++;
            }
        }

        set_alert($success_count > 0 ? 'success' : 'danger', "Registered/Synced {$success_count}/" . count($topics) . ' webhooks.');
        redirect(admin_url('courier_goshipping/fulfilment/settings'));
    }

    public function delete_webhooks()
    {
        if (!$this->can_manage_fulfilment()) {
            access_denied('Salibay Fulfilment Settings');
        }

        $store = $this->shopify_connector_model->get_store();
        if (!$store) {
            set_alert('danger', 'Store credentials missing.');
            redirect(admin_url('courier_goshipping/fulfilment/settings'));
        }

        $webhooks = $this->db->where('store_id', $store->id)->get(db_prefix() . 'shopify_webhooks')->result();
        foreach ($webhooks as $hook) {
            if (!empty($hook->shopify_webhook_id)) {
                $url = "https://{$store->shop_domain}/admin/api/{$store->api_version}/webhooks/{$hook->shopify_webhook_id}.json";
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "X-Shopify-Access-Token: {$store->access_token}",
                ]);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_exec($ch);
                curl_close($ch);
            }
        }

        $this->db->where('store_id', $store->id)->delete(db_prefix() . 'shopify_webhooks');
        set_alert('success', 'Webhooks deleted successfully.');
        redirect(admin_url('courier_goshipping/fulfilment/settings'));
    }

    public function test_connection()
    {
        if (!$this->can_manage_fulfilment()) {
            ajax_access_denied();
        }

        $store = $this->shopify_connector_model->get_store();
        if (!$store) {
            echo json_encode(['success' => false, 'message' => 'Store credentials not found.']);
            return;
        }

        $url = "https://{$store->shop_domain}/admin/api/{$store->api_version}/shop.json";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "X-Shopify-Access-Token: {$store->access_token}",
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            $data = json_decode($response, true);
            echo json_encode([
                'success' => true,
                'message' => 'Connected | ' . ($data['shop']['name'] ?? 'Unknown') . ' | Plan: ' . ($data['shop']['plan_name'] ?? 'Unknown'),
            ]);
            return;
        }

        if ($http_code == 0) {
            echo json_encode(['success' => false, 'message' => 'Failed to reach Shopify. cURL error: ' . ($curl_error ?: 'unknown connection failure')]);
            return;
        }

        $err = json_decode($response, true);
        $msg = isset($err['errors']) ? json_encode($err['errors']) : 'Failed to connect. HTTP ' . $http_code;
        echo json_encode(['success' => false, 'message' => $msg]);
    }

    public function ensure_virtual_warehouse_ajax()
    {
        if (!$this->can_manage_fulfilment()) {
            ajax_access_denied();
        }

        $warehouse = $this->ensure_virtual_warehouse();
        if ($warehouse) {
            echo json_encode([
                'success' => true,
                'message' => 'Virtual warehouse is ready.',
                'warehouse' => $warehouse,
            ]);
            return;
        }

        echo json_encode([
            'success' => false,
            'message' => 'Warehouse module table was not found, so the virtual warehouse could not be created.',
        ]);
    }

    public function get_product_mappings()
    {
        $store = $this->shopify_connector_model->get_store();
        if (!$store) {
            echo json_encode(['data' => []]);
            return;
        }

        $mappings = $this->shopify_connector_model->get_product_mappings($store->id);
        echo json_encode(['data' => $mappings]);
    }

    public function save_product_mapping()
    {
        if (!$this->can_manage_fulfilment()) {
            ajax_access_denied();
        }

        $store = $this->shopify_connector_model->get_store();
        if (!$store) {
            echo json_encode(['success' => false, 'message' => 'Store credentials are not configured yet.']);
            return;
        }

        $data = [
            'store_id' => $store->id,
            'shopify_product_id' => trim((string) $this->input->post('shopify_product_id', true)),
            'shopify_variant_id' => trim((string) $this->input->post('shopify_variant_id', true)),
            'gs_sku' => trim((string) $this->input->post('gs_sku', true)),
            'fulfillment_model' => trim((string) $this->input->post('fulfillment_model', true)),
            'supplier_id' => $this->input->post('supplier_id', true) ?: null,
        ];

        $mapping_id = $this->shopify_connector_model->save_product_mapping($data);
        $this->write_integration_log('info', 'inventory', 'Salibay product mapping saved from courier fulfilment.', [
            'mapping_id' => $mapping_id,
            'shopify_variant_id' => $data['shopify_variant_id'],
            'gs_sku' => $data['gs_sku'],
            'fulfillment_model' => $data['fulfillment_model'],
        ], $store->id);

        echo json_encode(['success' => true, 'id' => $mapping_id]);
    }

    public function delete_product_mapping($id)
    {
        if (!$this->can_manage_fulfilment()) {
            ajax_access_denied();
        }

        $success = $this->shopify_connector_model->delete_product_mapping((int) $id);
        echo json_encode(['success' => $success]);
    }

    public function import_shopify_products()
    {
        if (!$this->can_manage_fulfilment()) {
            ajax_access_denied();
        }

        set_time_limit(300);

        $store = $this->shopify_connector_model->get_store();
        if (!$store) {
            echo json_encode(['success' => false, 'message' => 'Configure Salibay store credentials in Settings first.']);
            return;
        }

        $this->write_integration_log('info', 'inventory', 'Salibay product import started from courier fulfilment.', [
            'store_id' => $store->id,
            'shop_domain' => $store->shop_domain,
        ], $store->id);

        $branch_id = $this->ensure_virtual_branch();
        if (!$branch_id) {
            echo json_encode(['success' => false, 'message' => 'Unable to prepare the Salibay virtual warehouse (pos_system module tables missing).']);
            return;
        }

        $this->load->library('shopify_connector/shopify_api', [
            'shop_domain' => $store->shop_domain,
            'access_token' => $store->access_token,
            'api_version' => $store->api_version,
        ]);

        $products_res = $this->shopify_api->list_products();
        if (empty($products_res['data']['products'])) {
            $message = $products_res['success']
                ? 'No products were returned from Salibay.'
                : 'Failed to fetch products from Salibay: ' . ($products_res['error'] ?? 'Unknown error');
            echo json_encode(['success' => $products_res['success'], 'message' => $message]);
            return;
        }

        $imported = 0;
        $updated = 0;
        $errors = 0;
        $inventory_item_map = [];

        foreach ($products_res['data']['products'] as $product) {
            $category_id = $this->ensure_pos_category($product['product_type'] ?? '');
            $variants = $product['variants'] ?? [];
            $multi_variant = count($variants) > 1;

            foreach ($variants as $variant) {
                try {
                    $gs_sku = trim((string) ($variant['sku'] ?? ''));
                    if ($gs_sku === '') {
                        $gs_sku = 'SLB-' . $product['id'] . '-' . $variant['id'];
                    }

                    $name = $product['title'] ?? 'Salibay Product';
                    if ($multi_variant && !empty($variant['title']) && $variant['title'] !== 'Default Title') {
                        $name .= ' - ' . $variant['title'];
                    }

                    $is_active = (($product['status'] ?? 'active') === 'active') ? 1 : 0;
                    $product_id = $this->upsert_pos_product($gs_sku, $name, $category_id, $variant, $is_active);

                    $mapping_data = [
                        'store_id' => $store->id,
                        'shopify_product_id' => $product['id'],
                        'shopify_variant_id' => $variant['id'],
                        'gs_sku' => $gs_sku,
                        'fulfillment_model' => $store->default_fulfillment_model ?: 'A',
                        'shopify_inventory_item_id' => $variant['inventory_item_id'] ?? null,
                        'is_active' => 1,
                    ];
                    $is_new_mapping = $this->upsert_product_mapping($store->id, $mapping_data);

                    if ($is_new_mapping) {
                        $imported++;
                    } else {
                        $updated++;
                    }

                    if (!empty($variant['inventory_item_id'])) {
                        $inventory_item_map[$variant['inventory_item_id']] = ['gs_sku' => $gs_sku, 'product_id' => $product_id];
                    }
                } catch (\Throwable $e) {
                    $errors++;
                    $this->write_integration_log('error', 'inventory', 'Failed to import a Salibay variant.', [
                        'product_id' => $product['id'] ?? null,
                        'variant_id' => $variant['id'] ?? null,
                        'error' => $e->getMessage(),
                    ], $store->id);
                }
            }
        }

        $stock_synced = 0;
        $scope_error = null;
        if (!empty($inventory_item_map)) {
            $this->pull_inventory_costs($inventory_item_map, $scope_error);
            $stock_synced = $this->pull_inventory_levels($store, $branch_id, $inventory_item_map, $scope_error);
        }

        $this->db->where('id', $store->id)->update(db_prefix() . 'shopify_stores', ['last_inventory_sync_at' => date('Y-m-d H:i:s')]);

        $message = "Salibay import complete: {$imported} new SKUs, {$updated} updated, {$stock_synced} stock levels synced" . ($errors > 0 ? ", {$errors} errors" : '') . '.';
        if ($scope_error !== null) {
            $message .= ' Warning: cost/stock could not be fetched from Salibay (' . $scope_error . '). Products imported without stock levels — grant the "read_inventory" and "read_locations" API scopes to the Salibay app and re-run the import.';
        }
        $this->write_integration_log('info', 'inventory', $message, [
            'imported' => $imported,
            'updated' => $updated,
            'stock_synced' => $stock_synced,
            'errors' => $errors,
        ], $store->id);

        echo json_encode([
            'success' => true,
            'message' => $message,
            'imported' => $imported,
            'updated' => $updated,
            'stock_synced' => $stock_synced,
            'errors' => $errors,
        ]);
    }

    private function upsert_pos_product($gs_sku, $name, $category_id, $variant, $is_active)
    {
        $data = [
            'category_id' => $category_id,
            'name' => $name,
            'sku' => $gs_sku,
            'item_code' => $gs_sku,
            'barcode' => !empty($variant['barcode']) ? $variant['barcode'] : null,
            'type' => 'simple',
            'selling_price' => (float) ($variant['price'] ?? 0),
            'track_inventory' => 1,
            'is_active' => $is_active,
        ];

        $existing = $this->db->where('item_code', $gs_sku)->get(db_prefix() . 'pos_products')->row();
        if ($existing) {
            $this->db->where('id', $existing->id)->update(db_prefix() . 'pos_products', $data);
            return (int) $existing->id;
        }

        $this->db->insert(db_prefix() . 'pos_products', $data);
        return (int) $this->db->insert_id();
    }

    /**
     * Fetches cost prices in batches via inventory_items.json (one call per ~50 items)
     * instead of one API round-trip per variant — a per-variant lookup would take
     * minutes/hours for a real catalog and blow past PHP's execution time limit.
     */
    private function pull_inventory_costs(array $inventory_item_map, &$scope_error = null)
    {
        $ids = array_keys($inventory_item_map);

        foreach (array_chunk($ids, 50) as $chunk) {
            $res = $this->shopify_api->get_inventory_items($chunk);
            if (empty($res['success'])) {
                if ($scope_error === null && !empty($res['error'])) {
                    $scope_error = $res['error'];
                }
                continue;
            }
            if (empty($res['data']['inventory_items'])) {
                continue;
            }

            foreach ($res['data']['inventory_items'] as $item) {
                $item_id = $item['id'] ?? null;
                if ($item_id === null || !isset($inventory_item_map[$item_id]) || !isset($item['cost']) || $item['cost'] === null) {
                    continue;
                }

                $this->db->where('id', $inventory_item_map[$item_id]['product_id'])
                    ->update(db_prefix() . 'pos_products', ['cost_price' => (float) $item['cost']]);
            }
        }
    }

    private function upsert_product_mapping($store_id, $data)
    {
        $existing = $this->db->where('store_id', $store_id)
            ->where('shopify_variant_id', $data['shopify_variant_id'])
            ->get(db_prefix() . 'shopify_product_mappings')
            ->row();

        if ($existing) {
            $this->db->where('id', $existing->id)->update(db_prefix() . 'shopify_product_mappings', $data);
            return false;
        }

        $this->db->insert(db_prefix() . 'shopify_product_mappings', $data);
        return true;
    }

    private function pull_inventory_levels($store, $branch_id, array $inventory_item_map, &$scope_error = null)
    {
        $synced = 0;
        $ids = array_keys($inventory_item_map);

        foreach (array_chunk($ids, 50) as $chunk) {
            $levels_res = $this->shopify_api->get_inventory_levels(['inventory_item_ids' => implode(',', $chunk)]);
            if (empty($levels_res['success'])) {
                if ($scope_error === null && !empty($levels_res['error'])) {
                    $scope_error = $levels_res['error'];
                }
                continue;
            }
            if (empty($levels_res['data']['inventory_levels'])) {
                continue;
            }

            foreach ($levels_res['data']['inventory_levels'] as $level) {
                $item_id = $level['inventory_item_id'] ?? null;
                if ($item_id === null || !isset($inventory_item_map[$item_id])) {
                    continue;
                }

                $product_id = $inventory_item_map[$item_id]['product_id'];
                $gs_sku = $inventory_item_map[$item_id]['gs_sku'];
                $qty = (int) ($level['available'] ?? 0);

                $existing_inv = $this->db->where('branch_id', $branch_id)
                    ->where('product_id', $product_id)
                    ->where('variation_id', 0)
                    ->get(db_prefix() . 'pos_inventory')
                    ->row();

                if ($existing_inv) {
                    $this->db->where('id', $existing_inv->id)->update(db_prefix() . 'pos_inventory', ['quantity' => $qty]);
                } else {
                    $this->db->insert(db_prefix() . 'pos_inventory', [
                        'branch_id' => $branch_id,
                        'product_id' => $product_id,
                        'variation_id' => 0,
                        'quantity' => $qty,
                        'reserved_qty' => 0,
                    ]);
                }

                $this->db->insert(db_prefix() . 'shopify_inventory_sync', [
                    'store_id' => $store->id,
                    'shopify_inventory_item_id' => $item_id,
                    'shopify_location_id' => $level['location_id'] ?? null,
                    'gs_sku' => $gs_sku,
                    'gs_qty_available' => $qty,
                    'shopify_qty_before' => $qty,
                    'shopify_qty_after' => $qty,
                    'sync_type' => 'pull',
                    'synced_at' => date('Y-m-d H:i:s'),
                    'success' => 1,
                ]);

                $this->resolve_product_mapping_branch($gs_sku, $level['location_id'] ?? null);

                $synced++;
            }
        }

        return $synced;
    }

    /**
     * Stamps shopify_product_mappings.courier_branch_id for a SKU once we know
     * which Shopify location its stock lives at, using the admin-maintained
     * tbl_courier_shopify_locations mapping. No-op until that location has been
     * paired to a branch (see sync_shopify_locations()/save_location_branch_map()).
     */
    private function resolve_product_mapping_branch($gs_sku, $shopify_location_id)
    {
        if (empty($gs_sku) || empty($shopify_location_id)) {
            return;
        }

        $location = $this->db->where('shopify_location_id', $shopify_location_id)
            ->where('branch_id IS NOT NULL')
            ->get(db_prefix() . '_courier_shopify_locations')
            ->row();

        if (!$location) {
            return;
        }

        $this->db->where('gs_sku', $gs_sku)->update(db_prefix() . 'shopify_product_mappings', [
            'courier_branch_id' => $location->branch_id,
        ]);
    }

    /**
     * Pulls the Salibay/Shopify store's location list so an admin can pair each
     * one to a Go Shipping branch (Settings page). Safe to re-run — upserts by
     * shopify_location_id.
     */
    public function sync_shopify_locations()
    {
        if (!$this->can_manage_fulfilment()) {
            ajax_access_denied();
        }

        $store = $this->shopify_connector_model->get_store();
        if (!$store) {
            echo json_encode(['success' => false, 'message' => 'Configure Salibay store credentials in Settings first.']);
            return;
        }

        $this->load->library('shopify_connector/shopify_api', [
            'shop_domain' => $store->shop_domain,
            'access_token' => $store->access_token,
            'api_version' => $store->api_version,
        ]);

        $res = $this->shopify_api->list_locations();
        if (empty($res['success']) || empty($res['data']['locations'])) {
            echo json_encode(['success' => false, 'message' => 'Unable to fetch locations from Salibay: ' . ($res['error'] ?? 'Unknown error')]);
            return;
        }

        $synced = 0;
        foreach ($res['data']['locations'] as $loc) {
            $existing = $this->db->where('shopify_location_id', $loc['id'])
                ->get(db_prefix() . '_courier_shopify_locations')
                ->row();

            $data = [
                'name' => $loc['name'] ?? null,
                'city' => $loc['city'] ?? null,
                'country_code' => $loc['country_code'] ?? null,
                'is_active' => !empty($loc['active']) ? 1 : 0,
                'last_synced_at' => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                $this->db->where('id', $existing->id)->update(db_prefix() . '_courier_shopify_locations', $data);
            } else {
                $data['shopify_location_id'] = $loc['id'];
                $this->db->insert(db_prefix() . '_courier_shopify_locations', $data);
            }
            $synced++;
        }

        echo json_encode(['success' => true, 'message' => "Synced {$synced} Salibay location(s). Map each to a branch below."]);
    }

    /**
     * Save the branch an admin has paired a Salibay location to.
     */
    public function save_location_branch_map()
    {
        if (!$this->can_manage_fulfilment()) {
            ajax_access_denied();
        }

        $id = (int) $this->input->post('id');
        $branch_id = $this->input->post('branch_id') ?: null;

        $this->db->where('id', $id)->update(db_prefix() . '_courier_shopify_locations', ['branch_id' => $branch_id]);
        echo json_encode(['success' => true]);
    }

    public function get_orders_datatable()
    {
        if (!$this->can_view_fulfilment()) {
            ajax_access_denied();
        }
        try {
        $prefix = db_prefix();
        $start = (int) $this->input->post('start');
        $length = (int) $this->input->post('length');
        $search = $this->input->post('search')['value'] ?? '';
        $status_filter = (array) ($this->input->post('status') ?? []);
        $model_filter = $this->input->post('model');
        $date_from = $this->input->post('date_from');
        $date_to = $this->input->post('date_to');

        // Fetch permissions and branch IDs BEFORE starting the query builder cache
        $can_view_all = courier_staff_can_view_all_branches();
        $assigned_branches = !$can_view_all ? courier_get_staff_branch_ids() : [];
        if (!$can_view_all && empty($assigned_branches)) {
            $assigned_branches = [0]; // fallback
        }

        $this->db->start_cache();
        $this->db->from("{$prefix}shopify_orders");

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('shopify_order_number', $search);
            $this->db->or_like('customer_name', $search);
            $this->db->or_like('customer_email', $search);
            $this->db->or_like('tracking_number', $search);
            $this->db->group_end();
        }

        $status_filter = array_values(array_filter($status_filter));
        if (!empty($status_filter)) {
            $this->db->where_in('order_status', $status_filter);
        }

        if (!empty($model_filter) && $model_filter !== 'All') {
            $this->db->where('fulfillment_model', $model_filter);
        }

        if (!empty($date_from)) {
            $this->db->where('DATE(created_at) >=', $date_from);
        }
        if (!empty($date_to)) {
            $this->db->where('DATE(created_at) <=', $date_to);
        }
        if (!$can_view_all) {
            $this->db->where_in('tblshopify_orders.branch_id', $assigned_branches);
        }
        $this->db->stop_cache();

        $this->db->db_debug = FALSE;

        $total_filtered = $this->db->count_all_results();
        $db_error = $this->db->error();
        if (!empty($db_error['message'])) {
            throw new \Exception('DB Error on count_filtered: ' . $db_error['message']);
        }
        if (!$can_view_all) {
            $total_records = (int) $this->db->where_in('tblshopify_orders.branch_id', $assigned_branches)->count_all_results("{$prefix}shopify_orders");
        } else {
            $total_records = $this->db->count_all("{$prefix}shopify_orders");
        }

        $this->db->select('*')->order_by('id', 'DESC');
        if ($length > -1) {
            $this->db->limit($length, $start);
        }

        $rows = $this->db->get()->result();
        $db_error = $this->db->error();
        if (!empty($db_error['message'])) {
            throw new \Exception('DB Error on get: ' . $db_error['message']);
        }
        $this->db->flush_cache();

        $fleet_trips_exists = $this->db->table_exists("{$prefix}fleet_trips");

        $data = [];
        foreach ($rows as $row) {
            $this->db->db_debug = FALSE;
            $item_count = (int) $this->db->where('shopify_order_id', $row->id)->count_all_results("{$prefix}shopify_order_items");
            $db_error = $this->db->error();
            if (!empty($db_error['message'])) {
                throw new \Exception('DB Error on item count: ' . $db_error['message']);
            }
            $badge_class = $this->order_badge_class($row->order_status);
            $shipment_cell = '-';
            if (!empty($row->gs_shipment_id)) {
                $shipment_cell = '<a href="' . admin_url('courier_goshipping/shipments/waybill/' . $row->gs_shipment_id) . '">#' . (int) $row->gs_shipment_id . '</a>';
            }

            $driver_cell = '-';
            if (!empty($row->gs_shipment_id) && $fleet_trips_exists) {
                $trip = $this->db->select("t.status, v.name as vehicle_name, CONCAT(dr.firstname,' ',dr.lastname) as driver_name", false)
                    ->from("{$prefix}fleet_trips t")
                    ->join("{$prefix}fleet_vehicles v", 'v.id = t.vehicle_id', 'left')
                    ->join("{$prefix}staff dr", 'dr.staffid = t.driver_id', 'left')
                    ->where('t.shipment_id', $row->gs_shipment_id)
                    ->order_by('t.created_at', 'DESC')
                    ->limit(1)
                    ->get()->row();

                if ($trip) {
                    $driver_name = trim((string) ($trip->driver_name ?? ''));
                    $vehicle_name = trim((string) ($trip->vehicle_name ?? ''));
                    $trip_status_class = ($trip->status ?? '') === 'completed' ? 'success' : (($trip->status ?? '') === 'cancelled' ? 'danger' : 'info');
                    $driver_cell = '<span class="label label-' . $trip_status_class . '">' . ucfirst(str_replace('_', ' ', $trip->status ?? 'booked')) . '</span>';
                    if ($driver_name !== '' || $vehicle_name !== '') {
                        $driver_cell .= '<br><span style="font-size:11px;color:#777;">' . htmlspecialchars($driver_name ?: '—') . ($vehicle_name !== '' ? ' · ' . htmlspecialchars($vehicle_name) : '') . '</span>';
                    }
                } elseif (!empty($row->gs_shipment_id)) {
                    $driver_cell = '<span class="text-muted" style="font-size:11px;">Not assigned</span>';
                }
            }

            $actions = '<div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li><a href="#" onclick="viewFulfilmentOrder(' . (int) $row->id . '); return false;">View Details</a></li>';

            if (empty($row->gs_shipment_id)) {
                $actions .= '<li><a href="#" onclick="createFulfilmentShipment(' . (int) $row->id . '); return false;">Create Shipment</a></li>';
            } else {
                $actions .= '<li><a href="' . admin_url('courier_goshipping/shipments/waybill/' . $row->gs_shipment_id) . '">Open Shipment</a></li>';
            }

            $actions .= '</ul></div>';

            $data[] = [
                $row->id,
                '#' . htmlspecialchars((string) $row->shopify_order_number),
                htmlspecialchars((string) $row->customer_name),
                $item_count,
                htmlspecialchars((string) $row->currency) . ' ' . number_format((float) $row->total_price, 2),
                htmlspecialchars((string) ($row->fulfillment_model ?: '-')),
                '<span class="label label-' . $badge_class . '">' . ucfirst((string) $row->order_status) . '</span>',
                $shipment_cell,
                $driver_cell,
                htmlspecialchars((string) ($row->tracking_number ?: '-')),
                _dt($row->created_at),
                $actions,
            ];
        }

        $json_out = json_encode([
            'draw' => $this->input->post('draw'),
            'recordsTotal' => $total_records,
            'recordsFiltered' => $total_filtered,
            'data' => $data,
        ]);
        echo $json_out;
        exit;
        } catch (\Throwable $e) {
            $this->write_integration_log('error', 'order', 'get_orders_datatable crashed: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine(), [
                'last_query' => $this->db->last_query(),
            ]);
            echo json_encode(['error' => $e->getMessage(), 'query' => $this->db->last_query()]);
        }
    }

    public function get_order_detail($id)
    {
        $prefix = db_prefix();
        $order = $this->db->where('id', (int) $id)->get("{$prefix}shopify_orders")->row();
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found.']);
            return;
        }

        $items = $this->db->where('shopify_order_id', (int) $id)->get("{$prefix}shopify_order_items")->result();
        $tracking = [];
        if (!empty($order->gs_shipment_id) && $this->db->table_exists(db_prefix() . '_shipment_status_history')) {
            $tracking = $this->db->select('h.*, s.status_description, s.status_name')
                ->from(db_prefix() . '_shipment_status_history h')
                ->join(db_prefix() . '_shipment_statuses s', 's.id = h.status_id', 'left')
                ->where('shipment_id', $order->gs_shipment_id)
                ->order_by('changed_at', 'DESC')
                ->get()
                ->result();
        }

        echo json_encode([
            'success' => true,
            'order' => $order,
            'items' => $items,
            'tracking' => $tracking,
            'raw_payload' => $order->raw_payload ? json_decode($order->raw_payload, true) : new stdClass(),
        ]);
    }

    public function create_shipment($order_id)
    {
        if (!$this->can_manage_fulfilment()) {
            ajax_access_denied();
        }

        try {
            $result = $this->create_courier_shipment((int) $order_id);
        } catch (\Throwable $e) {
            $this->write_integration_log('error', 'shipment', 'Manual shipment creation crashed: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine(), [
                'shopify_db_order_id' => (int) $order_id,
                'trace' => $e->getTraceAsString(),
            ]);
            $result = ['success' => false, 'error' => $e->getMessage() . ' @ line ' . $e->getLine()];
        }

        echo json_encode($result);
    }

    public function get_inventory_datatable()
    {
        $prefix = db_prefix();
        $start = (int) $this->input->post('start');
        $length = (int) $this->input->post('length');
        $search = $this->input->post('search')['value'] ?? '';
        $stock_status = $this->input->post('stock_status');

        $base_sql = "
            FROM {$prefix}shopify_product_mappings m
            LEFT JOIN {$prefix}pos_products p ON p.item_code = m.gs_sku
            LEFT JOIN {$prefix}pos_product_categories c ON c.id = p.category_id
            LEFT JOIN (
                SELECT product_id, SUM(quantity) AS total_qty, SUM(reserved_qty) AS reserved_qty
                FROM {$prefix}pos_inventory
                GROUP BY product_id
            ) i ON i.product_id = p.id
            LEFT JOIN (
                SELECT gs_sku, MAX(synced_at) AS last_synced
                FROM {$prefix}shopify_inventory_sync
                GROUP BY gs_sku
            ) sync_latest ON sync_latest.gs_sku = m.gs_sku
            WHERE m.is_active = 1
        ";

        $where = '';
        $params = [];
        if ($search !== '') {
            $where .= " AND (
                m.gs_sku LIKE ?
                OR m.shopify_product_id LIKE ?
                OR m.shopify_variant_id LIKE ?
                OR p.name LIKE ?
            )";
            $like = '%' . $search . '%';
            $params = array_merge($params, [$like, $like, $like, $like]);
        }

        $available_expr = '(COALESCE(i.total_qty, 0) - COALESCE(i.reserved_qty, 0))';
        if ($stock_status === 'out_of_stock') {
            $where .= " AND {$available_expr} <= 0";
        } elseif ($stock_status === 'low_stock') {
            $where .= " AND {$available_expr} > 0 AND p.reorder_point > 0 AND {$available_expr} <= p.reorder_point";
        } elseif ($stock_status === 'in_stock') {
            $where .= " AND {$available_expr} > 0 AND (p.reorder_point <= 0 OR p.reorder_point IS NULL OR {$available_expr} > p.reorder_point)";
        }

        $total_records = (int) $this->db->query("SELECT COUNT(*) AS c {$base_sql}")->row()->c;
        $total_filtered = (int) $this->db->query("SELECT COUNT(*) AS c {$base_sql} {$where}", $params)->row()->c;

        $limit_sql = $length > -1 ? ' LIMIT ' . $start . ', ' . $length : '';
        $query = $this->db->query("
            SELECT
                m.id,
                m.shopify_product_id,
                m.shopify_variant_id,
                m.gs_sku,
                m.fulfillment_model,
                m.supplier_id,
                p.name AS product_name,
                p.reorder_point,
                p.cost_price,
                p.selling_price,
                c.name AS category_name,
                COALESCE(i.total_qty, 0) AS total_qty,
                COALESCE(i.reserved_qty, 0) AS reserved_qty,
                COALESCE(i.total_qty, 0) - COALESCE(i.reserved_qty, 0) AS available_qty,
                sync_latest.last_synced
            {$base_sql}
            {$where}
            ORDER BY sync_latest.last_synced DESC, m.id DESC
            {$limit_sql}
        ", $params)->result();

        $data = [];
        foreach ($query as $row) {
            $status = 'In Stock';
            $status_class = 'success';
            if ((float) $row->available_qty <= 0) {
                $status = 'Out of Stock';
                $status_class = 'danger';
            } elseif ((float) $row->reorder_point > 0 && (float) $row->available_qty <= (float) $row->reorder_point) {
                $status = 'Low Stock';
                $status_class = 'warning';
            }

            $data[] = [
                htmlspecialchars((string) $row->gs_sku),
                htmlspecialchars((string) ($row->product_name ?: 'Mapped SKU')),
                htmlspecialchars((string) ($row->category_name ?: '—')),
                htmlspecialchars((string) $row->shopify_variant_id),
                htmlspecialchars((string) $row->fulfillment_model),
                number_format((float) $row->cost_price, 2),
                number_format((float) $row->selling_price, 2),
                number_format((float) $row->total_qty, 2),
                number_format((float) $row->reserved_qty, 2),
                number_format((float) $row->available_qty, 2),
                '<span class="label label-' . $status_class . '">' . $status . '</span>',
                $row->last_synced ? _dt($row->last_synced) : 'Never',
            ];
        }

        echo json_encode([
            'draw' => $this->input->post('draw'),
            'recordsTotal' => $total_records,
            'recordsFiltered' => $total_filtered,
            'data' => $data,
        ]);
    }

    public function run_inventory_sync()
    {
        if (!$this->can_manage_fulfilment()) {
            ajax_access_denied();
        }

        $store = $this->db->where('is_active', 1)->get(db_prefix() . 'shopify_stores')->row();
        if (!$store) {
            echo json_encode(['success' => false, 'message' => 'No active Salibay store found.']);
            return;
        }

        $result = $this->sync_all_inventory_to_shopify($store->id);
        echo json_encode([
            'success' => true,
            'message' => $result['message'] ?? 'Inventory sync finished.',
            'result' => $result,
        ]);
    }

    public function get_webhook_events_datatable()
    {
        $prefix = db_prefix();
        $start = (int) $this->input->post('start');
        $length = (int) $this->input->post('length');
        $search = $this->input->post('search')['value'] ?? '';

        $this->db->start_cache();
        $this->db->from("{$prefix}shopify_webhook_events");

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('topic', $search);
            $this->db->or_like('shopify_order_id', $search);
            $this->db->or_like('last_error', $search);
            $this->db->group_end();
        }
        $this->db->stop_cache();

        $total_filtered = $this->db->count_all_results();
        $total_records = $this->db->count_all("{$prefix}shopify_webhook_events");

        $this->db->order_by('id', 'DESC');
        if ($length > -1) {
            $this->db->limit($length, $start);
        }

        $rows = $this->db->get()->result();
        $this->db->flush_cache();

        $data = [];
        foreach ($rows as $row) {
            $badge_class = 'default';
            switch (strtolower((string) $row->status)) {
                case 'processing':
                case 'retrying':
                    $badge_class = 'warning';
                    break;
                case 'done':
                    $badge_class = 'success';
                    break;
                case 'failed':
                    $badge_class = 'danger';
                    break;
            }

            $actions = '<div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li><a href="#" onclick="viewFulfilmentPayload(' . (int) $row->id . ', \'webhook\'); return false;">View Payload</a></li>';

            if (in_array($row->status, ['failed', 'retrying'], true)) {
                $actions .= '<li><a href="#" onclick="requeueFulfilmentWebhook(' . (int) $row->id . '); return false;">Retry</a></li>';
            }

            $actions .= '</ul></div>';

            $data[] = [
                $row->id,
                htmlspecialchars((string) $row->topic),
                htmlspecialchars((string) $row->shopify_order_id),
                '<span class="label label-' . $badge_class . '">' . ucfirst((string) $row->status) . '</span>',
                (int) $row->attempts,
                _dt($row->created_at),
                $row->processed_at ? _dt($row->processed_at) : '-',
                '<span class="text-danger">' . htmlspecialchars((string) ($row->last_error ?: '')) . '</span>',
                $actions,
            ];
        }

        echo json_encode([
            'draw' => $this->input->post('draw'),
            'recordsTotal' => $total_records,
            'recordsFiltered' => $total_filtered,
            'data' => $data,
        ]);
    }

    public function get_logs_datatable()
    {
        $prefix = db_prefix();
        $start = (int) $this->input->post('start');
        $length = (int) $this->input->post('length');
        $search = $this->input->post('search')['value'] ?? '';
        $level = $this->input->post('level');
        $category = $this->input->post('category');
        $date_from = $this->input->post('date_from');
        $date_to = $this->input->post('date_to');

        $this->db->start_cache();
        $this->db->from("{$prefix}shopify_integration_logs");

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('message', $search);
            $this->db->or_like('context', $search);
            $this->db->group_end();
        }
        if (!empty($level) && $level !== 'All') {
            $this->db->where('log_level', $level);
        }
        if (!empty($category) && $category !== 'All') {
            $this->db->where('category', $category);
        }
        if (!empty($date_from)) {
            $this->db->where('DATE(created_at) >=', to_sql_date($date_from));
        }
        if (!empty($date_to)) {
            $this->db->where('DATE(created_at) <=', to_sql_date($date_to));
        }
        $this->db->stop_cache();

        $total_filtered = $this->db->count_all_results();
        $total_records = $this->db->count_all("{$prefix}shopify_integration_logs");

        $this->db->order_by('id', 'DESC');
        if ($length > -1) {
            $this->db->limit($length, $start);
        }

        $rows = $this->db->get()->result();
        $this->db->flush_cache();

        $data = [];
        foreach ($rows as $row) {
            $context_html = '';
            if (!empty($row->context)) {
                $context = json_decode($row->context, true);
                if (is_array($context)) {
                    foreach (array_slice(array_keys($context), 0, 3) as $key) {
                        $value = is_scalar($context[$key]) ? (string) $context[$key] : json_encode($context[$key]);
                        $context_html .= '<strong>' . htmlspecialchars((string) $key) . ':</strong> ' . htmlspecialchars(mb_strimwidth($value, 0, 30, '...')) . '<br>';
                    }
                }
            }

            $data[] = [
                $row->id,
                strtoupper((string) $row->log_level),
                ucfirst((string) $row->category),
                htmlspecialchars((string) $row->message),
                $context_html,
                _dt($row->created_at),
                '<button type="button" class="btn btn-default btn-xs" onclick="viewFulfilmentPayload(' . (int) $row->id . ', \'log\');">View Full Context</button>',
            ];
        }

        echo json_encode([
            'draw' => $this->input->post('draw'),
            'recordsTotal' => $total_records,
            'recordsFiltered' => $total_filtered,
            'data' => $data,
        ]);
    }

    public function requeue_webhook_event($id)
    {
        if (!$this->can_manage_fulfilment()) {
            ajax_access_denied();
        }

        $this->db->where('id', (int) $id)->update(db_prefix() . 'shopify_webhook_events', [
            'status' => 'pending',
            'last_error' => null,
        ]);

        echo json_encode(['success' => true, 'message' => 'Webhook event requeued.']);
    }

    public function retry_all_failed_webhooks()
    {
        if (!$this->can_manage_fulfilment()) {
            ajax_access_denied();
        }

        $this->db->where('status', 'failed')->update(db_prefix() . 'shopify_webhook_events', [
            'status' => 'pending',
            'last_error' => null,
        ]);

        echo json_encode(['success' => true, 'message' => 'All failed webhook events were requeued.']);
    }

    public function clear_done_events()
    {
        if (!$this->can_manage_fulfilment()) {
            ajax_access_denied();
        }

        $this->db->where('status', 'done')->delete(db_prefix() . 'shopify_webhook_events');
        echo json_encode(['success' => true, 'message' => 'Processed webhook events were cleared.']);
    }

    public function get_raw_data($type, $id)
    {
        $prefix = db_prefix();

        if ($type === 'webhook') {
            $row = $this->db->select('payload')->where('id', (int) $id)->get("{$prefix}shopify_webhook_events")->row();
            echo json_encode(['success' => true, 'data' => $row ? json_decode($row->payload) : []]);
            return;
        }

        if ($type === 'log') {
            $row = $this->db->select('context')->where('id', (int) $id)->get("{$prefix}shopify_integration_logs")->row();
            echo json_encode(['success' => true, 'data' => $row ? json_decode($row->context) : []]);
            return;
        }

        echo json_encode(['success' => false, 'message' => 'Unsupported payload type.']);
    }

    public function clear_logs()
    {
        if (!$this->can_manage_fulfilment()) {
            ajax_access_denied();
        }

        $this->db->truncate(db_prefix() . 'shopify_integration_logs');
        echo json_encode(['success' => true, 'message' => 'Integration logs cleared.']);
    }

    public function generate_test_log()
    {
        if (!$this->can_manage_fulfilment()) {
            ajax_access_denied();
        }

        $store = $this->shopify_connector_model->get_store();
        $this->write_integration_log('debug', 'diagnostic', 'Manual test log created from courier fulfilment.', [
            'created_from' => 'courier_fulfilment',
            'staff_id' => function_exists('get_staff_user_id') ? get_staff_user_id() : null,
            'module' => 'courier_goshipping',
        ], $store ? $store->id : null);

        echo json_encode(['success' => true, 'message' => 'Test log written successfully.']);
    }

    public function export_logs_csv()
    {
        $prefix = db_prefix();
        $level = $this->input->get('level');
        $category = $this->input->get('category');
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');

        $this->db->from("{$prefix}shopify_integration_logs");
        if (!empty($level) && $level !== 'All') {
            $this->db->where('log_level', $level);
        }
        if (!empty($category) && $category !== 'All') {
            $this->db->where('category', $category);
        }
        if (!empty($date_from)) {
            $this->db->where('DATE(created_at) >=', to_sql_date($date_from));
        }
        if (!empty($date_to)) {
            $this->db->where('DATE(created_at) <=', to_sql_date($date_to));
        }

        $rows = $this->db->get()->result_array();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=salibay_fulfilment_logs_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Level', 'Category', 'Message', 'Context', 'Created At']);
        foreach ($rows as $row) {
            fputcsv($output, [$row['id'], $row['log_level'], $row['category'], $row['message'], $row['context'], $row['created_at']]);
        }
        fclose($output);
        exit;
    }

    public function get_health_status()
    {
        $prefix = db_prefix();
        $store = $this->db->where('is_active', 1)->get("{$prefix}shopify_stores")->row();
        $pending = (int) $this->db->where('status', 'pending')->count_all_results("{$prefix}shopify_webhook_events");
        $failed = (int) $this->db->where('status', 'failed')->count_all_results("{$prefix}shopify_webhook_events");
        $processing = (int) $this->db->where('status', 'processing')->count_all_results("{$prefix}shopify_webhook_events");
        $done = (int) $this->db->where('status', 'done')->count_all_results("{$prefix}shopify_webhook_events");
        $last_sync_count = (int) $this->db->where('success', 1)->count_all_results("{$prefix}shopify_inventory_sync");

        $oldest = $this->db->select('created_at')
            ->where('status', 'pending')
            ->order_by('created_at', 'ASC')
            ->limit(1)
            ->get("{$prefix}shopify_webhook_events")
            ->row();

        $oldest_time = 'None';
        $oldest_warning = false;
        if ($oldest) {
            $oldest_time = time_ago($oldest->created_at);
            $oldest_warning = strtotime($oldest->created_at) < strtotime('-1 hour');
        }

        echo json_encode([
            'success' => true,
            'api' => [
                'status' => ($store && !empty($store->access_token)) ? 'Connected' : 'Not Connected',
                'last_success' => ($store && !empty($store->last_inventory_sync_at)) ? _dt($store->last_inventory_sync_at) : 'Never',
                'store' => $store ? $store->shop_domain : 'Not configured',
            ],
            'webhooks' => [
                'pending' => $pending,
                'failed' => $failed,
                'processing' => $processing,
                'done' => $done,
                'oldest' => $oldest_time,
                'oldest_warning' => $oldest_warning,
            ],
            'inventory' => [
                'last_sync' => ($store && !empty($store->last_inventory_sync_at)) ? _dt($store->last_inventory_sync_at) : 'Never',
                'in_sync' => $last_sync_count,
                'out_sync' => 0,
            ],
        ]);
    }

    private function sync_all_inventory_to_shopify($store_id)
    {
        $store = $this->shopify_connector_model->get_store();
        if (!$store || (int) $store->id !== (int) $store_id) {
            return ['updated' => 0, 'skipped' => 0, 'errors' => 1, 'message' => 'Active store not found.'];
        }

        $this->load->library('shopify_connector/shopify_api', [
            'shop_domain' => $store->shop_domain,
            'access_token' => $store->access_token,
        ]);

        $mappings = $this->db->where('store_id', $store_id)
            ->where('is_active', 1)
            ->where('shopify_inventory_item_id IS NOT NULL')
            ->get(db_prefix() . 'shopify_product_mappings')
            ->result();

        if (empty($mappings)) {
            return ['updated' => 0, 'skipped' => 0, 'errors' => 0, 'message' => 'No inventory mappings were found.'];
        }

        $location_id = $store->warehouse_location_id;
        if (empty($location_id)) {
            $loc_res = $this->shopify_api->list_locations();
            if ($loc_res['success'] && !empty($loc_res['data']['locations'])) {
                $location_id = $loc_res['data']['locations'][0]['id'];
                $this->db->where('id', $store_id)->update(db_prefix() . 'shopify_stores', ['warehouse_location_id' => $location_id]);
            } else {
                return ['updated' => 0, 'skipped' => 0, 'errors' => count($mappings), 'message' => 'Unable to load Salibay locations.'];
            }
        }

        $updated = 0;
        $skipped = 0;
        $errors = 0;
        foreach ($mappings as $map) {
            $result = $this->sync_item_to_shopify($map, $location_id);
            if ($result === 'updated') {
                $updated++;
            } elseif ($result === 'skipped') {
                $skipped++;
            } else {
                $errors++;
            }
        }

        $this->db->where('id', $store_id)->update(db_prefix() . 'shopify_stores', ['last_inventory_sync_at' => date('Y-m-d H:i:s')]);
        return [
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
            'message' => "Inventory sync complete: {$updated} updated, {$skipped} skipped, {$errors} errors.",
        ];
    }

    private function sync_item_to_shopify($map, $location_id)
    {
        $gs_qty = 0;
        $inv = $this->db->query("
            SELECT (i.quantity - i.reserved_qty) AS available
            FROM " . db_prefix() . "pos_inventory i
            JOIN " . db_prefix() . "pos_products p ON p.id = i.product_id
            WHERE p.item_code = ?
        ", [$map->gs_sku])->row();

        if ($inv) {
            $gs_qty = max(0, (int) $inv->available);
        }

        if ($gs_qty == 0 && in_array($map->fulfillment_model, ['B', 'C'], true)) {
            $gs_qty = 999;
        }

        $shop_inv = $this->shopify_api->get_inventory_levels([
            'inventory_item_ids' => $map->shopify_inventory_item_id,
            'location_ids' => $location_id,
        ]);
        if (!$shop_inv['success']) {
            return 'error';
        }

        $shopify_qty = 0;
        if (!empty($shop_inv['data']['inventory_levels'])) {
            $shopify_qty = (int) $shop_inv['data']['inventory_levels'][0]['available'];
        }

        if ($gs_qty === $shopify_qty) {
            return 'skipped';
        }

        $update_res = $this->shopify_api->set_inventory_level($map->shopify_inventory_item_id, $location_id, $gs_qty);

        $this->db->insert(db_prefix() . 'shopify_inventory_sync', [
            'store_id' => $map->store_id,
            'shopify_inventory_item_id' => $map->shopify_inventory_item_id,
            'shopify_location_id' => $location_id,
            'gs_sku' => $map->gs_sku,
            'gs_qty_available' => $gs_qty,
            'shopify_qty_before' => $shopify_qty,
            'shopify_qty_after' => $gs_qty,
            'sync_type' => 'push',
            'synced_at' => date('Y-m-d H:i:s'),
            'success' => $update_res['success'] ? 1 : 0,
            'error_message' => $update_res['success'] ? null : ($update_res['error'] ?? 'Unknown error'),
        ]);

        return $update_res['success'] ? 'updated' : 'error';
    }

    private function build_base_data($group, $title)
    {
        $store = $this->shopify_connector_model->get_store();
        $warehouse = $this->ensure_virtual_warehouse();
        $metrics = $this->get_fulfilment_metrics();

        return [
            'title' => $title,
            'group' => $group,
            'can_manage_fulfilment' => $this->can_manage_fulfilment(),
            'store' => $store,
            'virtual_warehouse' => $warehouse,
            'metrics' => $metrics,
        ];
    }

    private function get_fulfilment_metrics()
    {
        $prefix = db_prefix();
        $store = $this->shopify_connector_model->get_store();
        $branch_where = '';
        if (!courier_staff_can_view_all_branches()) {
            $ids = courier_get_staff_branch_ids();
            $ids = !empty($ids) ? $ids : [0];
            $branch_where = ' AND branch_id IN (' . implode(',', array_map('intval', $ids)) . ')';
        }

        $metrics = [
            'orders_total' => (int) $this->db->query("SELECT COUNT(*) AS c FROM {$prefix}shopify_orders WHERE 1=1{$branch_where}")->row()->c,
            'orders_today' => 0,
            'pending_dispatch' => 0,
            'linked_shipments' => 0,
            'tracked_skus' => 0,
            'mapped_vendors' => 0,
            'virtual_available_qty' => 0,
            'virtual_reserved_qty' => 0,
            'low_stock_skus' => 0,
            'webhook_pending' => 0,
            'webhook_failed' => 0,
            'last_sync' => ($store && !empty($store->last_inventory_sync_at)) ? $store->last_inventory_sync_at : null,
        ];

        $metrics['orders_today'] = (int) $this->db->query("SELECT COUNT(*) AS c FROM {$prefix}shopify_orders WHERE DATE(created_at)=CURDATE(){$branch_where}")->row()->c;
        $metrics['pending_dispatch'] = (int) $this->db->query("SELECT COUNT(*) AS c FROM {$prefix}shopify_orders WHERE order_status IN ('confirmed','processing'){$branch_where}")->row()->c;
        $metrics['linked_shipments'] = (int) $this->db->query("SELECT COUNT(*) AS c FROM {$prefix}shopify_orders WHERE gs_shipment_id IS NOT NULL{$branch_where}")->row()->c;
        $metrics['tracked_skus'] = (int) $this->db->query("SELECT COUNT(*) AS c FROM {$prefix}shopify_product_mappings WHERE is_active = 1")->row()->c;
        $metrics['mapped_vendors'] = (int) $this->db->query("SELECT COUNT(DISTINCT supplier_id) AS c FROM {$prefix}shopify_product_mappings WHERE is_active = 1 AND supplier_id IS NOT NULL")->row()->c;
        $metrics['webhook_pending'] = (int) $this->db->query("SELECT COUNT(*) AS c FROM {$prefix}shopify_webhook_events WHERE status='pending'")->row()->c;
        $metrics['webhook_failed'] = (int) $this->db->query("SELECT COUNT(*) AS c FROM {$prefix}shopify_webhook_events WHERE status='failed'")->row()->c;

        $inv = $this->db->query("
            SELECT
                COALESCE(SUM(quantity), 0) AS total_qty,
                COALESCE(SUM(reserved_qty), 0) AS reserved_qty
            FROM {$prefix}pos_inventory
        ")->row();
        if ($inv) {
            $metrics['virtual_reserved_qty'] = (float) $inv->reserved_qty;
            $metrics['virtual_available_qty'] = max(0, (float) $inv->total_qty - (float) $inv->reserved_qty);
        }

        $metrics['low_stock_skus'] = (int) $this->db->query("
            SELECT COUNT(*) AS c
            FROM {$prefix}shopify_product_mappings m
            JOIN {$prefix}pos_products p ON p.item_code = m.gs_sku
            LEFT JOIN (
                SELECT product_id, SUM(quantity) AS qty, SUM(reserved_qty) AS reserved
                FROM {$prefix}pos_inventory
                GROUP BY product_id
            ) i ON i.product_id = p.id
            WHERE m.is_active = 1
              AND p.reorder_point IS NOT NULL
              AND p.reorder_point > 0
              AND (COALESCE(i.qty, 0) - COALESCE(i.reserved, 0)) <= p.reorder_point
        ")->row()->c;

        return $metrics;
    }

    // Polled every few seconds by the fulfilment header so KPI cards update
    // live (new Salibay orders, dispatch counts) without a manual page refresh.
    public function get_dashboard_metrics_ajax()
    {
        if (!$this->can_view_fulfilment()) {
            ajax_access_denied();
        }

        $virtual_warehouse = $this->ensure_virtual_warehouse();
        echo json_encode([
            'success' => true,
            'metrics' => $this->get_fulfilment_metrics(),
            'virtual_warehouse_name' => $virtual_warehouse->warehouse_name ?? null,
        ]);
    }

    private function ensure_virtual_warehouse($name = null)
    {
        if (!$this->db->table_exists(db_prefix() . 'warehouse')) {
            return null;
        }

        $name = trim((string) ($name ?: get_option('shopify_virtual_warehouse_name') ?: 'Salibay Virtual Fulfilment Warehouse'));
        $this->ensure_virtual_branch($name);

        $warehouse_id = (int) get_option('shopify_virtual_warehouse_id');
        if ($warehouse_id > 0) {
            $existing = $this->db->where('warehouse_id', $warehouse_id)->get(db_prefix() . 'warehouse')->row();
            if ($existing) {
                if ($existing->warehouse_name !== $name) {
                    $this->db->where('warehouse_id', $warehouse_id)->update(db_prefix() . 'warehouse', ['warehouse_name' => $name]);
                    $existing->warehouse_name = $name;
                }
                return $existing;
            }
        }

        $existing = $this->db->where('warehouse_code', 'SALIBAY-VIRTUAL')->get(db_prefix() . 'warehouse')->row();
        if ($existing) {
            update_option('shopify_virtual_warehouse_id', $existing->warehouse_id);
            if ($existing->warehouse_name !== $name) {
                $this->db->where('warehouse_id', $existing->warehouse_id)->update(db_prefix() . 'warehouse', ['warehouse_name' => $name]);
                $existing->warehouse_name = $name;
            }
            return $existing;
        }

        $this->db->insert(db_prefix() . 'warehouse', [
            'warehouse_code' => 'SALIBAY-VIRTUAL',
            'warehouse_name' => $name,
            'warehouse_address' => 'Vendor stock mirrored virtually for Salibay fulfilment operations.',
            'order' => 999,
            'display' => 1,
            'note' => 'This warehouse represents Salibay vendor inventory that Go Shipping fulfils end-to-end after customer checkout.',
        ]);
        $warehouse_id = $this->db->insert_id();
        update_option('shopify_virtual_warehouse_id', $warehouse_id);
        update_option('shopify_virtual_warehouse_name', $name);

        return $this->db->where('warehouse_id', $warehouse_id)->get(db_prefix() . 'warehouse')->row();
    }

    /**
     * The actual stock ledger lives in tblpos_inventory, scoped by branch_id (FK into
     * tblpos_branches) — the tblwarehouse row above is just a label used by the settings UI.
     * This ensures a dedicated pos_branches row exists so Salibay vendor stock is isolated
     * from any physical branch's own inventory, and returns its id.
     */
    private function ensure_virtual_branch($name = null)
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_branches')) {
            return null;
        }

        $name = trim((string) ($name ?: get_option('shopify_virtual_warehouse_name') ?: 'Salibay Virtual Fulfilment Warehouse'));
        $branch_id = (int) get_option('shopify_virtual_branch_id');
        if ($branch_id > 0) {
            $existing = $this->db->where('id', $branch_id)->get(db_prefix() . 'pos_branches')->row();
            if ($existing) {
                if ($existing->name !== $name) {
                    $this->db->where('id', $branch_id)->update(db_prefix() . 'pos_branches', ['name' => $name]);
                }
                return $branch_id;
            }
        }

        $existing = $this->db->where('code', 'SALIBAY-VIRTUAL')->get(db_prefix() . 'pos_branches')->row();
        if ($existing) {
            update_option('shopify_virtual_branch_id', $existing->id);
            if ($existing->name !== $name) {
                $this->db->where('id', $existing->id)->update(db_prefix() . 'pos_branches', ['name' => $name]);
            }
            return (int) $existing->id;
        }

        $this->db->insert(db_prefix() . 'pos_branches', [
            'name' => $name,
            'code' => 'SALIBAY-VIRTUAL',
            'address' => 'Vendor-consigned stock mirrored virtually for Salibay fulfilment. Physical goods remain with the vendor until handed to Go Shipping.',
            'is_active' => 1,
        ]);
        $branch_id = $this->db->insert_id();
        update_option('shopify_virtual_branch_id', $branch_id);

        return $branch_id;
    }

    private function ensure_pos_category($name)
    {
        $name = trim((string) $name) ?: 'Salibay Import';
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-')) ?: 'salibay-import';

        $existing = $this->db->where('slug', $slug)->get(db_prefix() . 'pos_product_categories')->row();
        if ($existing) {
            return (int) $existing->id;
        }

        $this->db->insert(db_prefix() . 'pos_product_categories', [
            'name' => $name,
            'slug' => $slug,
            'is_active' => 1,
        ]);

        return (int) $this->db->insert_id();
    }

    private function can_view_fulfilment()
    {
        // Salibay Fulfilment is deliberately its OWN gate — not implied by any
        // courier-shipments capability. Agents (brokers on commission) get
        // view_own_shipments by default, which must never leak into Fulfilment;
        // an admin grants this explicitly per staff/role via Setup > Roles.
        return is_admin()
            || has_permission('shopify_connector', '', 'view_shopify_connector')
            || has_permission('shopify_connector', '', 'manage_shopify_connector');
    }

    private function can_manage_fulfilment()
    {
        return is_admin() || has_permission('shopify_connector', '', 'manage_shopify_connector');
    }

    private function write_integration_log($level, $category, $message, array $context = [], $store_id = null)
    {
        $this->db->insert(db_prefix() . 'shopify_integration_logs', [
            'store_id' => $store_id,
            'log_level' => $level,
            'category' => $category,
            'message' => $message,
            'context' => !empty($context) ? json_encode($context, JSON_UNESCAPED_SLASHES) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function get_shopify_webhook_endpoint()
    {
        $public_url = trim(get_option('shopify_public_webhook_url'));
        if ($public_url !== '') {
            return $public_url;
        }

        return admin_url('shopify_connector/webhook');
    }

    /**
     * Raw-SQL " AND {alias}.branch_id IN (...)" fragment for the free-form
     * queries in this controller, or '' when the staff can see all branches.
     */
    private function branch_scope_sql_clause($alias, $branch_column_available = true)
    {
        if (!$branch_column_available) {
            return '';
        }

        if (courier_staff_can_view_all_branches()) {
            return '';
        }

        $ids = courier_get_staff_branch_ids();
        $ids = !empty($ids) ? $ids : [0];

        return " AND {$alias}.branch_id IN (" . implode(',', array_map('intval', $ids)) . ')';
    }

    private function order_badge_class($status)
    {
        switch (strtolower((string) $status)) {
            case 'pending':
                return 'default';
            case 'confirmed':
                return 'info';
            case 'processing':
                return 'warning';
            case 'dispatched':
                return 'primary';
            case 'delivered':
                return 'success';
            case 'cancelled':
            case 'returned':
                return 'danger';
            default:
                return 'default';
        }
    }

    private function get_salibay_orders_for_list()
    {
        $prefix = db_prefix();
        $shipment_status_description = $this->db->field_exists('status_description', "{$prefix}_shipment_statuses")
            ? 'ss.status_description'
            : 'ss.description';
        $assigned_staff_supported = $this->db->field_exists('assigned_staff_id', "{$prefix}shopify_orders");
        $assigned_select = $assigned_staff_supported
            ? 'so.assigned_staff_id, owner.firstname AS owner_firstname, owner.lastname AS owner_lastname,'
            : 'NULL AS assigned_staff_id, NULL AS owner_firstname, NULL AS owner_lastname,';
        $assigned_join = $assigned_staff_supported
            ? "LEFT JOIN {$prefix}staff owner ON owner.staffid = so.assigned_staff_id"
            : '';
        $salibay_tags_supported = $this->db->field_exists('salibay_classification', "{$prefix}shopify_orders");
        $salibay_tags_select = $salibay_tags_supported
            ? 'so.salibay_classification, so.salibay_route_tag, so.needs_manual_review,'
            : "NULL AS salibay_classification, NULL AS salibay_route_tag, 0 AS needs_manual_review,";

        $rows = $this->db->query("
            SELECT
                so.id AS order_id,
                so.shopify_order_number,
                so.customer_name,
                so.customer_email,
                so.order_status,
                so.financial_status,
                so.total_price,
                so.currency,
                so.tracking_number AS order_tracking_number,
                so.created_at AS order_created_at,
                so.gs_shipment_id,
                {$assigned_select}
                {$salibay_tags_select}
                s.id AS shipment_id,
                s.waybill_number,
                s.tracking_id,
                {$shipment_status_description} AS status_description,
                ss.status_name,
                sender.first_name AS sender_first_name,
                sender.last_name AS sender_last_name,
                items.items_summary
            FROM {$prefix}shopify_orders so
            LEFT JOIN {$prefix}_shipments s ON s.id = so.gs_shipment_id
            LEFT JOIN {$prefix}_shipment_statuses ss ON ss.id = s.status_id
            LEFT JOIN {$prefix}_shipment_senders sender ON sender.id = s.sender_id
            {$assigned_join}
            LEFT JOIN (
                SELECT shopify_order_id,
                       GROUP_CONCAT(CONCAT(COALESCE(NULLIF(product_name, ''), 'Product'), ' (x', quantity, ')') SEPARATOR ', ') AS items_summary
                FROM {$prefix}shopify_order_items
                GROUP BY shopify_order_id
            ) items ON items.shopify_order_id = so.id
            ORDER BY so.created_at DESC
        ")->result();

        foreach ($rows as $row) {
            $row->financial_badge_class = $this->financial_badge_class($row->financial_status);
            $row->sender_display = trim((string) $row->sender_first_name . ' ' . (string) $row->sender_last_name) ?: 'Go Shipping Warehouse';
            $row->waybill_display = (string) ($row->waybill_number ?: $row->tracking_id ?: $row->order_tracking_number ?: '');
            $row->items_display = (string) ($row->items_summary ?: '-');
            $row->assigned_display = trim((string) $row->owner_firstname . ' ' . (string) $row->owner_lastname) ?: 'Admin';
            $row->total_display = ($row->total_price !== null)
                ? app_format_money((float) $row->total_price, $row->currency ?: get_base_currency()->name)
                : '-';

            $classification_labels = ['local' => 'Salibay Local', 'global' => 'Salibay Global', 'mixed' => 'Salibay Mixed', 'manual_review' => 'Manual Review'];
            $classification_badges = ['local' => 'success', 'global' => 'info', 'mixed' => 'warning', 'manual_review' => 'danger'];
            $row->classification_display = $classification_labels[$row->salibay_classification] ?? '-';
            $row->classification_badge_class = $classification_badges[$row->salibay_classification] ?? 'default';
            $row->needs_manual_review = !empty($row->needs_manual_review);

            // Sender column: show both the route code AND the branch it
            // actually resolved to (the shipment's real sender name) —
            // showing only the route code hid which physical branch entity
            // the order landed under, which is exactly what caused staff to
            // assign themselves to the wrong same-country branch (e.g. "UAE
            // Branch" instead of "Dubai Branch") when both exist separately.
            $row->sender_column_display = !empty($row->salibay_route_tag)
                ? $row->salibay_route_tag . ' — ' . $row->sender_display
                : $row->sender_display;

            $is_delivered = strtolower((string) $row->order_status) === 'delivered'
                || in_array(strtolower((string) $row->status_name), ['delivered', 'received'], true);

            if ($is_delivered) {
                $row->fulfilment_status_text = 'Fulfilled';
                $row->fulfilment_badge_class = 'success';
            } elseif (empty($row->shipment_id)) {
                $row->fulfilment_status_text = 'Not Fulfilled';
                $row->fulfilment_badge_class = 'default';
            } else {
                $row->fulfilment_status_text = (string) ($row->status_description ?: 'Shipment Created');
                $row->fulfilment_badge_class = $this->shipment_badge_class($row->status_name);
            }
        }

        return $rows;
    }

    private function financial_badge_class($status)
    {
        switch (strtolower((string) $status)) {
            case 'paid':
                return 'success';
            case 'pending':
                return 'warning';
            case 'refunded':
            case 'voided':
                return 'danger';
            case 'partially_paid':
            case 'partially_refunded':
                return 'info';
            default:
                return 'default';
        }
    }

    private function shipment_badge_class($status)
    {
        switch (strtolower((string) $status)) {
            case 'picked_up':
            case 'created':
                return 'info';
            case 'in_transit':
            case 'dispatched':
                return 'warning';
            case 'delivered':
            case 'received':
            case 'arrived_destination':
            case 'out_for_delivery':
                return 'success';
            default:
                return 'default';
        }
    }

    private function create_courier_shipment($order_id)
    {
        $this->db->where('id', $order_id);
        $order = $this->db->get(db_prefix() . 'shopify_orders')->row();
        if (!$order) {
            return ['success' => false, 'error' => 'Order not found.'];
        }

        if ($order->gs_shipment_id) {
            return [
                'success' => true,
                'message' => 'Shipment already exists.',
                'shipment_id' => $order->gs_shipment_id,
                'tracking_number' => $order->tracking_number,
            ];
        }

        $delivery_address = json_decode($order->delivery_address, true);
        if (!is_array($delivery_address)) {
            $delivery_address = [];
        }

        $items = $this->db->where('shopify_order_id', $order_id)
            ->where('fulfillment_model', 'A')
            ->get(db_prefix() . 'shopify_order_items')
            ->result();

        if (empty($items)) {
            return ['success' => false, 'error' => 'No Model A fulfilment items were found for this order.'];
        }

        $packages_data = [];
        foreach ($items as $item) {
            $weight = $this->get_item_weight($item->gs_sku);
            $packages_data[] = [
                'quantity' => $item->quantity,
                'description' => $item->product_name ?: 'Product',
                'length' => 10,
                'width' => 10,
                'height' => 10,
                'weight' => $weight,
                'weight_volume' => $weight,
                'chargeable_weight' => $weight,
                'unit_price' => $item->unit_price ?: 0,
            ];
        }

        $tracking_number = 'GS' . date('Ymd') . str_pad((string) $order_id, 6, '0', STR_PAD_LEFT);
        $branch_id = $this->resolve_order_branch_id($items);
        $sender_data = [
            'first_name' => 'Go Shipping',
            'last_name' => 'Warehouse',
            'phone_number' => get_option('company_phonenumber') ?: '000000000',
            'email' => get_option('smtp_email') ?: 'warehouse@example.com',
            'address' => trim(strip_tags(str_replace(['<br />', '<br/>', '<br>'], ', ', format_organization_info())), ', ') ?: 'Main Warehouse',
            'zipcode' => '00100',
            'address_type' => 'postal_code',
        ];

        $location = $this->resolve_shipping_location($delivery_address, $branch_id);
        // A confident Salibay classification tag overrides the geography
        // guess — see the same override in Shopify_connector::create_courier_shipment().
        if (($order->salibay_classification ?? null) === 'local') {
            $location['shipping_category'] = 'domestic';
        } elseif (($order->salibay_classification ?? null) === 'global') {
            $location['shipping_category'] = 'international';
        }
        $name_parts = explode(' ', $delivery_address['name'] ?? $order->customer_name ?? 'Customer', 2);
        $recipient_data = [
            'first_name' => $name_parts[0],
            'last_name' => $name_parts[1] ?? '',
            'phone_number' => $delivery_address['phone'] ?? $order->customer_phone ?? '000000000',
            'email' => $order->customer_email ?: 'no-reply@example.com',
            'address' => trim(($delivery_address['address1'] ?? '') . ' ' . ($delivery_address['address2'] ?? '')),
            'zipcode' => $delivery_address['zip'] ?? '00000',
            'address_type' => 'postal_code',
            'country_id' => $location['country_id'],
            'state_id' => $location['state_id'],
        ];

        $status_row = $this->db->where('status_name', 'created')->get(db_prefix() . '_shipment_statuses')->row();
        // See the matching comment in Shopify_connector::create_courier_shipment()
        // — "Salibay Global" orders need shipping_mode to be exactly
        // "COURIER (NONE)" so they show up under shipments?type=international
        // &mode=courier&mode_type=none.
        $is_salibay_global = ($order->salibay_classification ?? null) === 'global';
        $shipment_data = [
            'shipping_mode' => $location['shipping_category'] === 'international'
                ? ($is_salibay_global ? 'COURIER (NONE)' : 'AIR (INTERNATIONAL)')
                : 'Courier',
            'shipping_category' => $location['shipping_category'],
            'tracking_id' => $tracking_number,
            'waybill_number' => $tracking_number,
            'company_type' => 'company',
            'status_id' => $status_row ? $status_row->id : 1,
            // Unassigned (0), not get_default_staff_id() — see the matching
            // comment in Shopify_connector::create_courier_shipment(). Stays
            // visible to any branch-scoped staff until someone assigns it.
            'staff_id' => 0,
            'packaging_charges' => 0.00,
            'branch_id' => $branch_id,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $shipment_id = $this->shopify_connector_model->create_courier_shipment_db($shipment_data, $sender_data, $recipient_data, $packages_data);
        if (!$shipment_id) {
            return ['success' => false, 'error' => 'Shipment database creation failed.'];
        }

        $this->db->where('id', $order_id)->update(db_prefix() . 'shopify_orders', [
            'gs_shipment_id' => $shipment_id,
            'tracking_number' => $tracking_number,
            'order_status' => 'processing',
            'branch_id' => $branch_id,
        ]);

        $invoice_id = $this->create_shipment_invoice($shipment_id, $order, $recipient_data, $packages_data, $tracking_number);

        return [
            'success' => true,
            'message' => 'Shipment created successfully.',
            'shipment_id' => $shipment_id,
            'tracking_number' => $tracking_number,
            'invoice_id' => $invoice_id,
            'open_url' => admin_url('courier_goshipping/shipments/waybill/' . $shipment_id),
        ];
    }

    private function create_shipment_invoice($shipment_id, $order, $recipient_data, $packages_data, $tracking_number)
    {
        $this->load->model('invoices_model');
        $this->load->model('payments_model');
        $this->load->model('courier_goshipping/Client_model', 'courier_client_model');
        $this->load->model('courier_goshipping/Shipment_model', 'courier_shipment_model');

        $client_data = [
            'company' => trim($recipient_data['first_name'] . ' ' . $recipient_data['last_name']) ?: $order->customer_name,
            'phonenumber' => $recipient_data['phone_number'],
            'address' => $recipient_data['address'],
            'zip' => $recipient_data['zipcode'],
        ];
        $client_id = $this->courier_client_model->insert_client($client_data);
        if (!$client_id) {
            $db_error = $this->db->error();
            $this->write_integration_log('error', 'shipment', 'Invoice creation stopped: insert_client() failed - ' . ($db_error['message'] ?? 'unknown error'), [
                'shipment_id' => $shipment_id,
                'client_data' => $client_data,
            ]);
            return false;
        }

        $invoice_data = [
            'clientid' => $client_id,
            'number' => get_option('next_invoice_number'),
            'date' => date('Y-m-d'),
            'duedate' => date('Y-m-d', strtotime('+30 days')),
            'currency' => get_base_currency()->id,
            'subtotal' => 0,
            'total' => 0,
            'status' => Invoices_model::STATUS_UNPAID,
            'billing_street' => $recipient_data['address'],
            'billing_zip' => $recipient_data['zipcode'],
            'billing_country' => $recipient_data['country_id'] ?: 0,
        ];
        try {
            $invoice_id = $this->invoices_model->add($invoice_data);
        } catch (\Throwable $e) {
            $this->write_integration_log('error', 'shipment', 'Invoice creation stopped: invoices_model->add() threw ' . get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine(), [
                'shipment_id' => $shipment_id,
                'client_id' => $client_id,
                'invoice_data' => $invoice_data,
            ]);
            return false;
        }
        if (!$invoice_id) {
            $db_error = $this->db->error();
            $this->write_integration_log('error', 'shipment', 'Invoice creation stopped: invoices_model->add() returned falsy - ' . ($db_error['message'] ?? 'unknown error'), [
                'shipment_id' => $shipment_id,
                'client_id' => $client_id,
                'invoice_data' => $invoice_data,
            ]);
            return false;
        }

        $items_total = 0;
        foreach ($packages_data as $index => $pkg) {
            $items_total += $pkg['quantity'] * $pkg['unit_price'];
            $this->courier_shipment_model->add_invoice_item([
                'description' => 'WAYBILL - ' . strtoupper($tracking_number) . ' | ' . $pkg['description'],
                'long_description' => '',
                'qty' => (int) $pkg['quantity'],
                'rate' => $pkg['unit_price'],
                'item_order' => $index + 1,
                'rel_id' => $invoice_id,
                'rel_type' => 'invoice',
                'unit' => '',
            ]);
        }

        $shipping_fee = round(max(0, (float) $order->total_price - $items_total), 2);
        if ($shipping_fee > 0) {
            $this->courier_shipment_model->add_invoice_item([
                'description' => 'Shipping Fee',
                'long_description' => '',
                'qty' => 1,
                'rate' => $shipping_fee,
                'item_order' => count($packages_data) + 1,
                'rel_id' => $invoice_id,
                'rel_type' => 'invoice',
                'unit' => '',
            ]);
        }

        $invoice_total = $items_total + $shipping_fee;
        $this->courier_shipment_model->update_invoice($invoice_id, [
            'subtotal' => $invoice_total,
            'total' => $invoice_total,
        ]);
        $this->courier_shipment_model->update($shipment_id, ['invoice_id' => $invoice_id]);

        if ($order->payment_status === 'paid') {
            $this->payments_model->add([
                'invoiceid' => $invoice_id,
                'amount' => $invoice_total,
                'paymentmode' => $this->get_or_create_shopify_payment_mode(),
                'date' => date('Y-m-d'),
                'note' => 'Paid via Salibay checkout (order SHF-' . $order->shopify_order_id . ')',
                'transactionid' => 'SHF-' . $order->shopify_order_id,
            ]);
        }

        return $invoice_id;
    }

    private function get_or_create_shopify_payment_mode()
    {
        $existing = $this->db->where('name', 'Shopify')->get(db_prefix() . 'payment_modes')->row();
        if ($existing) {
            return $existing->id;
        }

        $this->db->insert(db_prefix() . 'payment_modes', [
            'name' => 'Shopify',
            'description' => 'Paid online at Salibay checkout',
            'show_on_pdf' => 1,
            'invoices_only' => 0,
            'expenses_only' => 0,
            'selected_by_default' => 0,
            'active' => 1,
        ]);

        return $this->db->insert_id();
    }

    /**
     * Resolves which Go Shipping branch should fulfil an order from its Model A
     * line items' shopify_product_mappings.courier_branch_id (majority vote —
     * an order split across multiple origin branches is a known limitation, not
     * solved here), falling back to the org-wide default branch when unresolved.
     */
    private function resolve_order_branch_id($items)
    {
        $skus = array_filter(array_map(function ($item) {
            return $item->gs_sku ?? null;
        }, $items));

        if (!empty($skus)) {
            $rows = $this->db->select('courier_branch_id')
                ->where_in('gs_sku', $skus)
                ->where('courier_branch_id IS NOT NULL')
                ->get(db_prefix() . 'shopify_product_mappings')
                ->result();

            if (!empty($rows)) {
                $counts = array_count_values(array_map(function ($r) {
                    return (int) $r->courier_branch_id;
                }, $rows));
                arsort($counts);
                return (int) array_key_first($counts);
            }
        }

        return courier_get_fallback_branch_id();
    }

    /**
     * Resolves the recipient's country/state, and whether this shipment is
     * domestic or international relative to the fulfilling branch's own
     * country (falling back to the global customer_default_country option
     * when no branch could be resolved).
     */
    private function resolve_shipping_location($delivery_address, $branch_id = null)
    {
        $result = ['country_id' => null, 'state_id' => null, 'shipping_category' => 'domestic'];
        $iso2 = $delivery_address['country_code'] ?? null;
        if (!$iso2) {
            return $result;
        }

        $country = $this->db->where('iso2', $iso2)->get(db_prefix() . 'countries')->row();
        if (!$country) {
            return $result;
        }

        $result['country_id'] = $country->country_id;
        $province = $delivery_address['province'] ?? null;
        if ($province) {
            $state = $this->db->where('country_id', $country->country_id)
                ->where('name', $province)
                ->get(db_prefix() . '_country_states')
                ->row();
            if ($state) {
                $result['state_id'] = $state->id;
            }
        }

        $home_country_id = null;
        if ($branch_id) {
            $branch = $this->db->where('id', $branch_id)->get(db_prefix() . '_courier_branches')->row();
            if ($branch && !empty($branch->country_id)) {
                $home_country_id = (int) $branch->country_id;
            }
        }
        if (empty($home_country_id)) {
            $home_country_id = get_option('customer_default_country');
        }

        if (!empty($home_country_id)) {
            $result['shipping_category'] = ((int) $home_country_id === (int) $country->country_id) ? 'domestic' : 'international';
        }

        return $result;
    }

    private function get_item_weight($gs_sku)
    {
        if ($gs_sku) {
            $product = $this->db->where('item_code', $gs_sku)->get(db_prefix() . 'pos_products')->row();
            if ($product && !empty($product->weight_per_unit) && $product->weight_per_unit > 0) {
                return (float) $product->weight_per_unit;
            }
        }

        return 1.0;
    }

    private function get_default_staff_id()
    {
        $configured = get_option('shopify_default_staff_id');
        if (!empty($configured)) {
            $exists = $this->db->where('staffid', $configured)->where('active', 1)->get(db_prefix() . 'staff')->row();
            if ($exists) {
                return (int) $configured;
            }
        }

        $first_active = $this->db->where('active', 1)->order_by('staffid', 'asc')->get(db_prefix() . 'staff')->row();
        return $first_active ? (int) $first_active->staffid : 1;
    }
}
