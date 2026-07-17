<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once dirname(__FILE__) . '/Pos_api.php';

/**
 * POST /pos_system/api/auth/login
 *   body: { email, password, branch_id, device_name? }
 *   â†’ { token, expires_at, staff, branch }
 *
 * POST /pos_system/api/auth/logout
 *   header: Authorization: Bearer <token>
 *   â†’ { message }
 *
 * GET /pos_system/api/auth/me
 *   header: Authorization: Bearer <token>
 *   â†’ { staff, branch, role }
 */
class Auth_api extends Pos_api
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['pos_system/Pos_branches_model']);
    }

    public function login()
    {
        if ($this->input->method() !== 'post') {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        $body = $this->json_body();

        $missing = $this->validate_required($body, ['email', 'password', 'branch_id']);
        if ($missing) {
            $this->json(['error' => 'Missing fields: ' . implode(', ', $missing)], 422);
        }

        // Authenticate against Perfex staff table
        $staff = $this->db
            ->where('email', $this->db->escape_str($body['email']))
            ->where('active', 1)
            ->get(db_prefix() . 'staff')
            ->row_array();

        if (!$staff || !_perfex_password_verify($body['password'], $staff['password'])) {
            $this->json(['error' => 'Invalid credentials'], 401);
        }

        $branch_id = (int) $body['branch_id'];

        if (!pos_staff_can_access_branch((int) $staff['staffid'], $branch_id)) {
            $this->json(['error' => 'You do not have access to this branch'], 403);
        }

        $branch = $this->pos_branches_model->get($branch_id);
        if (!$branch || !$branch['is_active']) {
            $this->json(['error' => 'Branch not found or inactive'], 404);
        }

        $token = $this->pos_auth->issue_token(
            (int) $staff['staffid'],
            $branch_id,
            $body['device_name'] ?? '',
            12   // 12-hour TTL
        );

        $role = $this->db
            ->select('role')
            ->where('staff_id', $staff['staffid'])
            ->where('branch_id', $branch_id)
            ->get(db_prefix() . 'pos_staff_branches')
            ->row();

        $this->json([
            'token'      => $token,
            'expires_at' => date('c', time() + 12 * 3600),
            'staff'      => [
                'id'         => $staff['staffid'],
                'name'       => $staff['firstname'] . ' ' . $staff['lastname'],
                'email'      => $staff['email'],
                'profile_image' => $staff['profile_image'] ?? null,
            ],
            'branch'     => $branch,
            'role'       => $role ? $role->role : 'cashier',
        ]);
    }

    public function logout()
    {
        $this->require_auth();
        // Extract raw token from header to revoke it
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/^Bearer\s+(.+)$/i', trim($header), $m)) {
            $this->pos_auth->revoke_token($m[1]);
        }
        $this->json(['message' => 'Logged out successfully']);
    }

    public function me()
    {
        $this->require_auth();

        $staff = $this->db
            ->select('staffid, firstname, lastname, email, profile_image')
            ->where('staffid', $this->auth_staff_id)
            ->get(db_prefix() . 'staff')
            ->row_array();

        $branch = $this->pos_branches_model->get($this->auth_branch_id);

        $this->json([
            'staff'  => $staff,
            'branch' => $branch,
            'role'   => $this->auth_role,
        ]);
    }
}
