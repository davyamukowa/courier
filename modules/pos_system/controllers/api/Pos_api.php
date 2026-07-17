<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Pos_api – Refactored base controller with:
 *  - Unified JSON responses with envelope format
 *  - Request rate limiting (sliding window)
 *  - Response caching for read endpoints
 *  - Branch isolation enforcement
 *  - Structured error codes
 *  - Activity logging
 */
class Pos_api extends App_Controller
{
    // Authenticated context (populated by require_auth)
    protected int    $auth_staff_id  = 0;
    protected int    $auth_branch_id = 0;
    protected string $auth_role      = 'cashier';

    // Cache TTL in seconds per endpoint class
    protected int $cache_ttl = 0;

    // Error code constants
    const ERR_UNAUTHORIZED   = 'POS_401';
    const ERR_FORBIDDEN      = 'POS_403';
    const ERR_NOT_FOUND      = 'POS_404';
    const ERR_VALIDATION     = 'POS_422';
    const ERR_CONFLICT       = 'POS_409';
    const ERR_SERVER         = 'POS_500';
    const ERR_GATEWAY        = 'POS_502';
    const ERR_RATE_LIMITED   = 'POS_429';

    // Role hierarchy weights
    private const ROLE_WEIGHTS = [
        'cashier'    => 1,
        'supervisor' => 2,
        'manager'    => 3,
        'admin'      => 4,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->library('pos_system/Pos_auth', 'pos_auth');
        require_once POS_SYSTEM_PATH . 'helpers/pos_helper.php';

        $this->_set_cors_headers();

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    // ─── Authentication ───────────────────────────────────────────────────────

    protected function require_auth(): void
    {
        // Rate limit before authenticating (prevents brute force)
        $this->_check_rate_limit($this->_client_ip(), 120, 60); // 120 req/min

        $identity = $this->pos_auth->authenticate();
        if (!$identity) {
            $this->error('Authentication required', 401, self::ERR_UNAUTHORIZED);
        }

        $this->auth_staff_id  = $identity['staff_id'];
        $this->auth_branch_id = $identity['branch_id'];

        $row = $this->db
            ->select('role')
            ->where('staff_id', $this->auth_staff_id)
            ->where('branch_id', $this->auth_branch_id)
            ->get(db_prefix() . 'pos_staff_branches')
            ->row();

        $this->auth_role = $row ? $row->role : 'cashier';

        // Perfex super admins always get full POS admin access regardless of branch role
        $is_perfex_admin = $this->db->select('admin')
                                    ->where('staffid', $this->auth_staff_id)
                                    ->get(db_prefix() . 'staff')->row();
        if ($is_perfex_admin && $is_perfex_admin->admin == '1') {
            $this->auth_role = 'admin';
        }
    }

    protected function require_role(string $minimum): void
    {
        $user  = self::ROLE_WEIGHTS[$this->auth_role]  ?? 0;
        $min   = self::ROLE_WEIGHTS[$minimum] ?? 999;
        if ($user < $min) {
            $this->error("Requires role: {$minimum}", 403, self::ERR_FORBIDDEN);
        }
    }

    // ─── Response helpers ─────────────────────────────────────────────────────

    /**
     * Success envelope: { success: true, data: ..., meta: ... }
     */
    protected function ok($data, int $status = 200, array $meta = []): void
    {
        $envelope = ['success' => true, 'data' => $data];
        if ($meta) {
            $envelope['meta'] = $meta;
        }
        $this->_emit_json($envelope, $status);
    }

    /**
     * Paginated success: wraps data with pagination meta.
     */
    protected function paginated(array $data, int $total, int $page, int $per_page): void
    {
        $this->ok($data, 200, [
            'total'        => $total,
            'page'         => $page,
            'per_page'     => $per_page,
            'total_pages'  => (int) ceil($total / max(1, $per_page)),
            'has_next'     => ($page * $per_page) < $total,
        ]);
    }

    /**
     * Error envelope: { success: false, error: { code, message, details? } }
     */
    protected function error(string $message, int $status = 400, string $code = 'POS_400', array $details = []): void
    {
        $payload = ['success' => false, 'error' => ['code' => $code, 'message' => $message]];
        if ($details) {
            $payload['error']['details'] = $details;
        }
        $this->_emit_json($payload, $status);
    }

    /**
     * Validate required fields; calls $this->error() and exits on failure.
     */
    protected function validate(array $data, array $rules): void
    {
        $missing = [];
        $invalid = [];

        foreach ($rules as $field => $rule) {
            $parts    = is_string($rule) ? explode('|', $rule) : $rule;
            $required = in_array('required', $parts);
            $present  = isset($data[$field]) && $data[$field] !== '';

            if ($required && !$present) {
                $missing[] = $field;
                continue;
            }

            if (!$present) {
                continue;
            }

            foreach ($parts as $part) {
                if ($part === 'integer' && !is_numeric($data[$field])) {
                    $invalid[] = "{$field} must be an integer";
                }
                if ($part === 'positive' && (float) $data[$field] <= 0) {
                    $invalid[] = "{$field} must be positive";
                }
                if (strpos($part, 'max:') === 0 && strlen($data[$field]) > (int) substr($part, 4)) {
                    $invalid[] = "{$field} is too long";
                }
                if ($part === 'email' && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $invalid[] = "{$field} must be a valid email";
                }
            }
        }

        if ($missing || $invalid) {
            $this->error(
                'Validation failed',
                422,
                self::ERR_VALIDATION,
                array_merge(
                    $missing ? ['missing_fields' => $missing] : [],
                    $invalid ? ['invalid_fields' => $invalid] : []
                )
            );
        }
    }

    /**
     * Decode JSON body; exits with 400 on malformed JSON.
     */
    protected function json_body(): array
    {
        $raw     = file_get_contents('php://input');
        $decoded = json_decode($raw ?: '{}', true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON body', 400, 'POS_400');
        }
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Read pagination params from query string.
     */
    protected function pagination(int $default_per_page = 50, int $max_per_page = 200): array
    {
        $page     = max(1, (int) ($this->input->get('page') ?? 1));
        $per_page = min($max_per_page, max(1, (int) ($this->input->get('per_page') ?? $default_per_page)));
        return ['limit' => $per_page, 'offset' => ($page - 1) * $per_page, 'page' => $page, 'per_page' => $per_page];
    }

    /**
     * Read & sanitize common filters from query string.
     */
    protected function read_filters(array $allowed): array
    {
        $out = [];
        foreach ($allowed as $key) {
            $val = $this->input->get($key);
            if ($val !== null && $val !== '') {
                $out[$key] = htmlspecialchars(strip_tags(trim($val)));
            }
        }
        return $out;
    }

    // ─── Caching ──────────────────────────────────────────────────────────────

    protected function cache_get(string $key): mixed
    {
        if (!$this->cache_ttl) {
            return null;
        }
        $file = APPPATH . 'cache/pos_' . md5($key);
        if (file_exists($file) && (time() - filemtime($file)) < $this->cache_ttl) {
            return json_decode(file_get_contents($file), true);
        }
        return null;
    }

    protected function cache_set(string $key, $data): void
    {
        if (!$this->cache_ttl) {
            return;
        }
        $file = APPPATH . 'cache/pos_' . md5($key);
        file_put_contents($file, json_encode($data), LOCK_EX);
    }

    protected function cache_bust(string $prefix): void
    {
        $hash_prefix = APPPATH . 'cache/pos_';
        foreach (glob($hash_prefix . '*') as $f) {
            // Bust all cache files for safety on writes
            unlink($f);
        }
    }

    // ─── Activity logging ─────────────────────────────────────────────────────

    protected function log_activity(string $action, array $context = []): void
    {
        if (!$this->auth_staff_id) {
            return;
        }
        $exists = $this->db->table_exists(db_prefix() . 'pos_activity_logs');
        if (!$exists) {
            return;
        }
        $this->db->insert(db_prefix() . 'pos_activity_logs', [
            'staff_id'   => $this->auth_staff_id,
            'branch_id'  => $this->auth_branch_id,
            'action'     => $action,
            'context'    => json_encode($context),
            'ip_address' => $this->_client_ip(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ─── Compatibility helpers ────────────────────────────────────────────────

    /**
     * Raw JSON output — no envelope. Kept for backward-compat with older controllers.
     */
    protected function json($data, int $status = 200): void
    {
        $this->_emit_json($data, $status);
    }

    /**
     * Return array of missing field names (empty = all present).
     * Does NOT auto-exit; callers handle the error themselves.
     */
    protected function validate_required(array $data, array $required): array
    {
        $missing = [];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $missing[] = $field;
            }
        }
        return $missing;
    }

    // ─── Private internals ────────────────────────────────────────────────────

    private function _emit_json($data, int $status): void
    {
        $this->output
             ->set_status_header($status)
             ->set_content_type('application/json')
             ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->output->_display();
        exit;
    }

    private function _set_cors_headers(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        header("Access-Control-Allow-Origin: {$origin}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With, X-Branch-Id');
        header('Vary: Origin');
    }

    private function _client_ip(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                return explode(',', $_SERVER[$key])[0];
            }
        }
        return '0.0.0.0';
    }

    private function _check_rate_limit(string $ip, int $max_requests, int $window_seconds): void
    {
        $cache_key = 'ratelimit_' . md5($ip);
        $file      = APPPATH . 'cache/pos_rl_' . md5($ip);

        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            $now  = time();

            // Sliding window — purge old entries
            $data = array_filter($data, fn($t) => ($now - $t) < $window_seconds);

            if (count($data) >= $max_requests) {
                header('Retry-After: ' . $window_seconds);
                $this->_emit_json([
                    'success' => false,
                    'error'   => ['code' => self::ERR_RATE_LIMITED, 'message' => 'Rate limit exceeded'],
                ], 429);
            }

            $data[] = $now;
        } else {
            $data = [time()];
        }

        file_put_contents($file, json_encode($data), LOCK_EX);
    }
}
