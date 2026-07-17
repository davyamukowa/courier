<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once dirname(__FILE__) . '/Pos_api.php';

/**
 * GET  /api/config                    â†’ resolved profile for current session
 * GET  /api/config/profiles           â†’ list profiles for branch
 * GET  /api/config/profiles/{id}      â†’ single profile
 * POST /api/config/profiles           â†’ create profile (manager+)
 * POST /api/config/profiles/{id}      â†’ update profile (manager+)
 * POST /api/config/profiles/{id}/delete â†’ delete (admin)
 * POST /api/config/profiles/{id}/assign â†’ assign user to profile (manager+)
 * POST /api/config/profiles/{id}/unassign â†’ remove user from profile (manager+)
 * GET  /api/config/price-lists        â†’ list price lists
 * GET  /api/config/item-groups        â†’ list item groups
 * GET  /api/config/customer-groups    â†’ list customer groups
 * GET  /api/config/print-templates    â†’ list print templates
 * GET  /api/config/payment-methods    â†’ list payment methods with Perfex gateway info
 */
class Config_api extends Pos_api
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('pos_system/Pos_profiles_model', 'pos_profiles_model');
    }

    // â”€â”€â”€ Resolved config for current POS session â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function index()
    {
        $this->require_auth();

        $config = $this->pos_profiles_model->resolve(
            $this->auth_staff_id,
            $this->auth_branch_id
        );

        // Attach active payment methods for this branch
        $config['payment_methods'] = $this->_get_payment_methods($this->auth_branch_id);

        // Attach allowed item/customer groups
        if (!empty($config['id'])) {
            $config['item_groups']     = $this->_get_profile_item_groups((int)$config['id']);
            $config['customer_groups'] = $this->_get_profile_customer_groups((int)$config['id']);
        }

        $this->ok($config);
    }

    // â”€â”€â”€ Profile CRUD â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function profiles()
    {
        $this->require_auth();

        $filters = ['branch_id' => $this->auth_branch_id];
        if ($this->auth_role === 'admin') {
            $filters = [];
        }

        $this->ok($this->pos_profiles_model->get_all($filters));
    }

    public function profile_show(int $id)
    {
        $this->require_auth();
        $this->require_role('manager');

        $profile = $this->pos_profiles_model->get($id);
        if (!$profile) {
            $this->error('Profile not found', 404, self::ERR_NOT_FOUND);
        }

        $this->ok($profile);
    }

    public function profile_create()
    {
        $this->require_auth();
        $this->require_role('manager');

        $data = $this->json_body();
        $this->validate($data, ['name' => 'required|max:150']);

        $data['created_by'] = $this->auth_staff_id;

        // Non-admins can only create profiles for their branch
        if ($this->auth_role !== 'admin') {
            $data['branch_id'] = $this->auth_branch_id;
        }

        $id = $this->pos_profiles_model->create($data);
        $this->log_activity('profile.create', ['profile_id' => $id]);
        $this->ok($this->pos_profiles_model->get($id), 201);
    }

    public function profile_update(int $id)
    {
        $this->require_auth();
        $this->require_role('manager');

        $profile = $this->pos_profiles_model->get($id);
        if (!$profile) {
            $this->error('Profile not found', 404, self::ERR_NOT_FOUND);
        }

        $this->pos_profiles_model->update($id, $this->json_body());
        $this->log_activity('profile.update', ['profile_id' => $id]);
        $this->ok($this->pos_profiles_model->get($id));
    }

    public function profile_delete(int $id)
    {
        $this->require_auth();
        $this->require_role('admin');

        $this->pos_profiles_model->delete($id);
        $this->log_activity('profile.delete', ['profile_id' => $id]);
        $this->ok(['message' => 'Profile deactivated']);
    }

    public function profile_assign_user(int $id)
    {
        $this->require_auth();
        $this->require_role('manager');

        $body = $this->json_body();
        $this->validate($body, ['staff_id' => 'required|integer']);

        $this->pos_profiles_model->assign_user($id, (int)$body['staff_id']);
        $this->ok(['message' => 'User assigned to profile']);
    }

    public function profile_unassign_user(int $id)
    {
        $this->require_auth();
        $this->require_role('manager');

        $body = $this->json_body();
        $this->validate($body, ['staff_id' => 'required|integer']);

        $this->pos_profiles_model->remove_user($id, (int)$body['staff_id']);
        $this->ok(['message' => 'User removed from profile']);
    }

    // â”€â”€â”€ Supporting lists â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function price_lists()
    {
        $this->require_auth();
        $this->ok($this->pos_profiles_model->get_price_lists());
    }

    public function item_groups()
    {
        $this->require_auth();
        $this->ok($this->pos_profiles_model->get_item_groups());
    }

    public function customer_groups()
    {
        $this->require_auth();
        $this->ok($this->pos_profiles_model->get_customer_groups());
    }

    public function print_templates()
    {
        $this->require_auth();
        $this->ok($this->pos_profiles_model->get_print_templates());
    }

    public function payment_methods()
    {
        $this->require_auth();
        $this->ok($this->_get_payment_methods($this->auth_branch_id));
    }

    // â”€â”€â”€ Private helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    private function _get_payment_methods(int $branch_id): array
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_payment_methods')) {
            return [];
        }

        return $this->db
            ->group_start()
                ->where('branch_id', $branch_id)
                ->or_where('branch_id IS NULL')
            ->group_end()
            ->where('is_active', 1)
            ->order_by('sort_order', 'ASC')
            ->get(db_prefix() . 'pos_payment_methods')
            ->result_array();
    }

    private function _get_profile_item_groups(int $profile_id): array
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_profile_item_groups')) {
            return [];
        }

        return $this->db
            ->select('g.*')
            ->from(db_prefix() . 'pos_item_groups g')
            ->join(db_prefix() . 'pos_profile_item_groups pig', 'pig.group_id = g.id')
            ->where('pig.profile_id', $profile_id)
            ->where('g.is_active', 1)
            ->get()->result_array();
    }

    private function _get_profile_customer_groups(int $profile_id): array
    {
        if (!$this->db->table_exists(db_prefix() . 'pos_profile_customer_groups')) {
            return [];
        }

        return $this->db
            ->select('g.*')
            ->from(db_prefix() . 'pos_customer_groups g')
            ->join(db_prefix() . 'pos_profile_customer_groups pcg', 'pcg.group_id = g.id')
            ->where('pcg.profile_id', $profile_id)
            ->where('g.is_active', 1)
            ->get()->result_array();
    }
}
