<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Pos_middleware – Branch isolation + permission enforcement.
 *
 * Attach to any model/controller that touches branch-scoped data.
 * Calling enforce() before every query ensures strict isolation.
 */
class Pos_middleware
{
    private $CI;
    private static ?self $instance = null;

    // Current authenticated context
    private int    $staff_id  = 0;
    private int    $branch_id = 0;
    private string $role      = 'cashier';
    private bool   $is_super  = false;   // Perfex super admin bypasses branch isolation

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ─── Context setters ─────────────────────────────────────────────────────

    public function set_context(int $staff_id, int $branch_id, string $role = 'cashier'): self
    {
        $this->staff_id  = $staff_id;
        $this->branch_id = $branch_id;
        $this->role      = $role;

        // Perfex super admins see all branches
        $this->is_super = is_admin();

        return $this;
    }

    // ─── Branch isolation ────────────────────────────────────────────────────

    /**
     * Apply branch_id WHERE clause to the CI db builder.
     * Super admins can see a specific branch or all (pass 0 to see all).
     */
    public function apply_branch_filter(string $table_alias = '', ?int $override_branch = null): void
    {
        $branch_id = $override_branch ?? $this->branch_id;
        $col       = $table_alias ? "{$table_alias}.branch_id" : 'branch_id';

        // Super admin requesting all branches
        if ($this->is_super && $branch_id === 0) {
            return;
        }

        $this->CI->db->where($col, $branch_id ?: $this->branch_id);
    }

    /**
     * Assert that a resource's branch_id matches the authenticated branch.
     * Throws 403 (via exit) if mismatch.
     */
    public function assert_branch_owns(array $record, string $field = 'branch_id'): void
    {
        if ($this->is_super) {
            return;
        }

        if ((int) ($record[$field] ?? 0) !== $this->branch_id) {
            $this->_forbidden("Resource does not belong to your branch");
        }
    }

    /**
     * Assert a staff member has the minimum role.
     */
    public function assert_role(string $minimum): void
    {
        $weights = ['cashier' => 1, 'supervisor' => 2, 'manager' => 3, 'admin' => 4];
        if (($weights[$this->role] ?? 0) < ($weights[$minimum] ?? 999)) {
            $this->_forbidden("Requires role: {$minimum}");
        }
    }

    /**
     * Check if staff has at least the given role.
     */
    public function has_role(string $minimum): bool
    {
        $weights = ['cashier' => 1, 'supervisor' => 2, 'manager' => 3, 'admin' => 4];
        return ($weights[$this->role] ?? 0) >= ($weights[$minimum] ?? 999);
    }

    /**
     * Build a safe SQL WHERE fragment for branch filtering.
     * Useful in raw queries: WHERE {$middleware->branch_where()}
     */
    public function branch_where(string $alias = ''): string
    {
        if ($this->is_super && $this->branch_id === 0) {
            return '1=1';
        }
        $col = $alias ? "{$alias}.branch_id" : 'branch_id';
        return "{$col} = " . (int) $this->branch_id;
    }

    // ─── Getters ─────────────────────────────────────────────────────────────

    public function branch_id(): int  { return $this->branch_id; }
    public function staff_id(): int   { return $this->staff_id; }
    public function role(): string    { return $this->role; }
    public function is_super(): bool  { return $this->is_super; }

    // ─── Activity log ─────────────────────────────────────────────────────────

    public function log(string $action, array $context = [], ?string $entity = null, ?int $entity_id = null): void
    {
        if (!$this->staff_id) {
            return;
        }

        $table = db_prefix() . 'pos_activity_logs';
        if (!$this->CI->db->table_exists($table)) {
            return;
        }

        $this->CI->db->insert($table, [
            'staff_id'   => $this->staff_id,
            'branch_id'  => $this->branch_id,
            'action'     => $action,
            'entity'     => $entity,
            'entity_id'  => $entity_id,
            'context'    => json_encode($context),
            'ip_address' => $this->_get_ip(),
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ─── Private ─────────────────────────────────────────────────────────────

    private function _forbidden(string $message): void
    {
        $this->CI->output
             ->set_status_header(403)
             ->set_content_type('application/json')
             ->set_output(json_encode(['success' => false, 'error' => ['code' => 'POS_403', 'message' => $message]]));
        $this->CI->output->_display();
        exit;
    }

    private function _get_ip(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $k) {
            if (!empty($_SERVER[$k])) {
                return explode(',', $_SERVER[$k])[0];
            }
        }
        return '0.0.0.0';
    }
}
