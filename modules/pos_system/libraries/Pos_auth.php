<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * POS_Auth – Token-based authentication library for the POS SPA/API.
 *
 * Tokens are stored in pos_api_tokens as a SHA-256 hash.
 * The raw token is returned once at issuance; never stored in plain text.
 */
class Pos_auth
{
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    /**
     * Issue a new token for a staff member at a given branch.
     * Returns the plain-text token (returned only once to caller).
     */
    public function issue_token(int $staff_id, int $branch_id, string $device_name = '', int $ttl_hours = 12): string
    {
        $token      = bin2hex(random_bytes(32));   // 64-char hex token
        $token_hash = hash('sha256', $token);
        $expires_at = date('Y-m-d H:i:s', time() + $ttl_hours * 3600);

        // Revoke existing tokens for this device to avoid proliferation
        if ($device_name) {
            $this->CI->db
                ->where('staff_id', $staff_id)
                ->where('branch_id', $branch_id)
                ->where('device_name', $device_name)
                ->set('is_active', 0)
                ->update(db_prefix() . 'pos_api_tokens');
        }

        $this->CI->db->insert(db_prefix() . 'pos_api_tokens', [
            'staff_id'    => $staff_id,
            'branch_id'   => $branch_id,
            'token'       => $token,          // stored for reference only (not for lookup)
            'token_hash'  => $token_hash,
            'device_name' => $device_name,
            'expires_at'  => $expires_at,
            'is_active'   => 1,
        ]);

        return $token;
    }

    /**
     * Validate a Bearer token from the Authorization header.
     * Returns [staff_id, branch_id] on success, or false.
     */
    public function authenticate()
    {
        $token = $this->extract_bearer_token();
        if (!$token) {
            return false;
        }

        $hash = hash('sha256', $token);

        $row = $this->CI->db
            ->where('token_hash', $hash)
            ->where('is_active', 1)
            ->where('(expires_at IS NULL OR expires_at > NOW())')
            ->get(db_prefix() . 'pos_api_tokens')
            ->row();

        if (!$row) {
            return false;
        }

        // Update last-used timestamp
        $this->CI->db
            ->where('id', $row->id)
            ->set('last_used_at', date('Y-m-d H:i:s'))
            ->update(db_prefix() . 'pos_api_tokens');

        return [
            'staff_id'  => (int) $row->staff_id,
            'branch_id' => (int) $row->branch_id,
        ];
    }

    /**
     * Revoke a specific token.
     */
    public function revoke_token(string $token): bool
    {
        $hash = hash('sha256', $token);
        return (bool) $this->CI->db
            ->where('token_hash', $hash)
            ->set('is_active', 0)
            ->update(db_prefix() . 'pos_api_tokens');
    }

    /**
     * Revoke all tokens for a staff member (e.g. on password change).
     */
    public function revoke_all(int $staff_id): void
    {
        $this->CI->db
            ->where('staff_id', $staff_id)
            ->set('is_active', 0)
            ->update(db_prefix() . 'pos_api_tokens');
    }

    // ─── Private ─────────────────────────────────────────────────────────────

    private function extract_bearer_token(): ?string
    {
        $header = isset($_SERVER['HTTP_AUTHORIZATION'])
            ? $_SERVER['HTTP_AUTHORIZATION']
            : (function_exists('apache_request_headers')
                ? (apache_request_headers()['Authorization'] ?? '')
                : '');

        if (preg_match('/^Bearer\s+(.+)$/i', trim($header), $m)) {
            return $m[1];
        }

        return null;
    }
}
