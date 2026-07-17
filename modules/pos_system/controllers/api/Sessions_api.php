<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once dirname(__FILE__) . '/Pos_api.php';

/**
 * GET  /pos_system/api/sessions/current          â†’ current open session
 * POST /pos_system/api/sessions/open             â†’ open session
 * POST /pos_system/api/sessions/{id}/close       â†’ close session
 * POST /pos_system/api/sessions/{id}/cash-in     â†’ add cash to drawer
 * POST /pos_system/api/sessions/{id}/cash-out    â†’ remove cash from drawer
 * GET  /pos_system/api/sessions                  â†’ list sessions (supervisor+)
 * GET  /pos_system/api/sessions/{id}             â†’ session detail
 */
class Sessions_api extends Pos_api
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('pos_system/Pos_sessions_model', 'pos_sessions_model');
        $this->load->model('pos_system/Pos_payments_model', 'pos_payments_model');
    }

    public function current()
    {
        $this->require_auth();

        $session = $this->pos_sessions_model->get_open_session(
            $this->auth_staff_id,
            $this->auth_branch_id
        );

        if (!$session) {
            $this->json(['session' => null]);
        }

        $session['payment_summary'] = $this->pos_payments_model->get_session_summary((int) $session['id']);
        $this->json(['session' => $session]);
    }

    public function index()
    {
        $this->require_auth();
        $this->require_role('supervisor');

        $pg       = $this->pagination();
        $sessions = $this->pos_sessions_model->get_branch_sessions(
            $this->auth_branch_id,
            $pg['limit'],
            $pg['offset']
        );

        $this->json(['data' => $sessions, 'page' => $pg['page']]);
    }

    public function show(int $id)
    {
        $this->require_auth();
        $this->require_role('supervisor');

        $session = $this->pos_sessions_model->get($id);
        if (!$session || (int) $session['branch_id'] !== $this->auth_branch_id) {
            $this->json(['error' => 'Session not found'], 404);
        }

        $session['payment_summary'] = $this->pos_payments_model->get_session_summary($id);
        $this->json($session);
    }

    public function open()
    {
        $this->require_auth();

        $body    = $this->json_body();
        $float   = max(0, (float) ($body['opening_float'] ?? 0));
        $terminal = $body['terminal_id'] ?? '';

        $result = $this->pos_sessions_model->open(
            $this->auth_branch_id,
            $this->auth_staff_id,
            $float,
            $terminal
        );

        $this->json($result, $result['success'] ? 201 : 409);
    }

    public function close(int $id)
    {
        $this->require_auth();

        $session = $this->pos_sessions_model->get($id);
        if (!$session || (int) $session['staff_id'] !== $this->auth_staff_id) {
            if ($this->auth_role === 'cashier') {
                $this->json(['error' => 'Session not found or not yours'], 404);
            }
        }

        $body   = $this->json_body();
        $result = $this->pos_sessions_model->close(
            $id,
            (float) ($body['actual_cash'] ?? 0),
            $body['notes'] ?? ''
        );

        $this->json($result, $result['success'] ? 200 : 409);
    }

    public function cash_in(int $id)
    {
        $this->require_auth();
        $this->_cash_movement($id, 'in');
    }

    public function cash_out(int $id)
    {
        $this->require_auth();
        $this->_cash_movement($id, 'out');
    }

    private function _cash_movement(int $session_id, string $type): void
    {
        $session = $this->pos_sessions_model->get($session_id);
        if (!$session || $session['status'] !== 'open' || (int) $session['branch_id'] !== $this->auth_branch_id) {
            $this->json(['error' => 'Session not found or not open'], 404);
        }

        $body    = $this->json_body();
        $missing = $this->validate_required($body, ['amount', 'reason']);
        if ($missing) {
            $this->json(['error' => 'Missing: ' . implode(', ', $missing)], 422);
        }

        $movement_id = $this->pos_sessions_model->add_cash_movement(
            $session_id,
            $this->auth_branch_id,
            $this->auth_staff_id,
            $type,
            abs((float) $body['amount']),
            $body['reason'],
            $body['notes'] ?? ''
        );

        $this->json(['message' => 'Cash movement recorded', 'id' => $movement_id], 201);
    }
}
