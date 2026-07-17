<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once dirname(__FILE__) . '/Pos_api.php';

/**
 * GET    /pos_system/api/branches          â†’ list all branches (admin)
 * GET    /pos_system/api/branches/{id}     â†’ single branch
 * POST   /pos_system/api/branches          â†’ create branch (admin)
 * PUT    /pos_system/api/branches/{id}     â†’ update branch (admin)
 * DELETE /pos_system/api/branches/{id}     â†’ soft-delete (admin)
 * GET    /pos_system/api/branches/{id}/staff â†’ list staff
 * POST   /pos_system/api/branches/{id}/staff â†’ assign staff
 * DELETE /pos_system/api/branches/{id}/staff/{staff_id} â†’ remove staff
 */
class Branches_api extends Pos_api
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('pos_system/Pos_branches_model', 'pos_branches_model');
    }

    public function index()
    {
        $this->require_auth();
        $this->require_role('manager');

        $filters  = ['is_active' => $this->input->get('is_active') !== null ? (int) $this->input->get('is_active') : null];
        $branches = $this->pos_branches_model->get_all(array_filter($filters, fn($v) => $v !== null));
        $this->json(['data' => $branches]);
    }

    public function show(int $id)
    {
        $this->require_auth();

        // Managers can see any branch; others only their own
        if ($this->auth_role === 'cashier' || $this->auth_role === 'supervisor') {
            if ($id !== $this->auth_branch_id) {
                $this->json(['error' => 'Access denied'], 403);
            }
        }

        $branch = $this->pos_branches_model->get($id);
        if (!$branch) {
            $this->json(['error' => 'Branch not found'], 404);
        }

        $this->json($branch);
    }

    public function create()
    {
        $this->require_auth();
        $this->require_role('admin');

        $data    = $this->json_body();
        $missing = $this->validate_required($data, ['name', 'code']);
        if ($missing) {
            $this->json(['error' => 'Missing: ' . implode(', ', $missing)], 422);
        }

        // Ensure code is unique
        $existing = $this->db->where('code', strtoupper($data['code']))->get(db_prefix() . 'pos_branches')->row();
        if ($existing) {
            $this->json(['error' => 'Branch code already in use'], 409);
        }

        $id = $this->pos_branches_model->create($data);
        $this->json($this->pos_branches_model->get($id), 201);
    }

    public function update(int $id)
    {
        $this->require_auth();
        $this->require_role('manager');

        $branch = $this->pos_branches_model->get($id);
        if (!$branch) {
            $this->json(['error' => 'Branch not found'], 404);
        }

        $this->pos_branches_model->update($id, $this->json_body());
        $this->json($this->pos_branches_model->get($id));
    }

    public function delete(int $id)
    {
        $this->require_auth();
        $this->require_role('admin');

        $branch = $this->pos_branches_model->get($id);
        if (!$branch) {
            $this->json(['error' => 'Branch not found'], 404);
        }

        $this->pos_branches_model->delete($id);
        $this->json(['message' => 'Branch deactivated']);
    }

    public function staff(int $id)
    {
        $this->require_auth();
        $this->require_role('manager');

        $branch = $this->pos_branches_model->get($id);
        if (!$branch) {
            $this->json(['error' => 'Branch not found'], 404);
        }

        $this->json(['data' => $this->pos_branches_model->get_staff($id)]);
    }

    public function assign_staff(int $id)
    {
        $this->require_auth();
        $this->require_role('manager');

        $data    = $this->json_body();
        $missing = $this->validate_required($data, ['staff_id']);
        if ($missing) {
            $this->json(['error' => 'Missing: staff_id'], 422);
        }

        $this->pos_branches_model->assign_staff(
            $id,
            (int) $data['staff_id'],
            $data['role']       ?? 'cashier',
            (bool) ($data['is_default'] ?? false)
        );

        $this->json(['message' => 'Staff assigned', 'data' => $this->pos_branches_model->get_staff($id)]);
    }

    public function remove_staff(int $branch_id, int $staff_id)
    {
        $this->require_auth();
        $this->require_role('manager');

        $this->pos_branches_model->remove_staff($branch_id, $staff_id);
        $this->json(['message' => 'Staff removed from branch']);
    }
}
