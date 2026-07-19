<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Rider_model extends App_Model
{
    private $riders_table;
    private $tokens_table;

    public function __construct()
    {
        parent::__construct();
        $this->riders_table = db_prefix() . '_courier_riders';
        $this->tokens_table = db_prefix() . '_courier_rider_tokens';
    }

    private function normalize_phone($phone)
    {
        $phone = preg_replace('/[^0-9+]/', '', (string) $phone);
        return $phone;
    }

    public function find_by_phone($phone)
    {
        return $this->db->where('phone', $this->normalize_phone($phone))->get($this->riders_table)->row();
    }

    public function find($id)
    {
        return $this->db->where('id', (int) $id)->get($this->riders_table)->row();
    }

    /**
     * @return array{success:bool, message?:string, rider?:object}
     */
    public function register($name, $phone, $password)
    {
        $name  = trim((string) $name);
        $phone = $this->normalize_phone($phone);

        if ($name === '' || $phone === '' || strlen((string) $password) < 4) {
            return ['success' => false, 'message' => 'Please fill in your name, phone number, and a password of at least 4 characters.'];
        }

        if ($this->find_by_phone($phone)) {
            return ['success' => false, 'message' => 'An account with this phone number already exists. Please log in instead.'];
        }

        $this->db->insert($this->riders_table, [
            'name'          => $name,
            'phone'         => $phone,
            'password_hash' => app_hash_password($password),
            'status'        => 'active',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $rider_id = $this->db->insert_id();
        if (!$rider_id) {
            return ['success' => false, 'message' => 'Could not create your account. Please try again.'];
        }

        $rider = $this->find($rider_id);
        $this->try_link_staff($rider);

        return ['success' => true, 'rider' => $this->find($rider_id)];
    }

    /**
     * @return array{success:bool, message?:string, rider?:object}
     */
    public function login($phone, $password)
    {
        $rider = $this->find_by_phone($phone);
        if (!$rider || !app_hasher()->CheckPassword((string) $password, $rider->password_hash)) {
            return ['success' => false, 'message' => 'Incorrect phone number or password.'];
        }
        if ($rider->status !== 'active') {
            return ['success' => false, 'message' => 'This account has been suspended. Please contact your dispatcher.'];
        }

        // Staff accounts (and their phone numbers) can be created after a
        // rider already registered — re-check on every login so a rider
        // isn't stuck unlinked forever just because of ordering.
        if (empty($rider->staff_id)) {
            $this->try_link_staff($rider);
            $rider = $this->find($rider->id);
        }

        return ['success' => true, 'rider' => $rider];
    }

    /**
     * Links a rider to a 'Fleet: Driver' staff record, so the existing
     * driver_id/staff_id assignment fields (shipments, pickups, fleet
     * trips) recognize them — first by matching an existing staff account
     * with the same phone number (an admin already created it the normal
     * way), otherwise by auto-provisioning one, so the whole flow works
     * with zero admin prep.
     *
     * Auto-provisioned staff rows are created with `active = 0` on
     * purpose: Authentication_model blocks login (and password reset) for
     * inactive staff, and Staff::index()'s member list only shows
     * `active = 1` rows — so riders never appear in Setup > Staff and can
     * never log into /admin, while still being fully visible to every
     * driver-assignment dropdown (Driver_model's query has no active
     * filter). Rider auth stays entirely inside tbl_courier_riders/
     * tbl_courier_rider_tokens; this staff row exists only so the rest of
     * the app's driver_id/staff_id plumbing has something to point at.
     */
    public function try_link_staff($rider)
    {
        if (!$rider || !empty($rider->staff_id)) {
            return;
        }

        $staff = $this->db->select('staff.staffid')
            ->from(db_prefix() . 'staff staff')
            ->join(db_prefix() . 'roles roles', 'roles.roleid = staff.role')
            ->where('roles.name', 'Fleet: Driver')
            ->where('staff.phonenumber', $rider->phone)
            ->where('staff.active', 1)
            ->get()
            ->row();

        $staff_id = $staff ? (int) $staff->staffid : $this->auto_provision_staff($rider);

        if ($staff_id) {
            $this->db->where('id', $rider->id)->update($this->riders_table, ['staff_id' => $staff_id]);
        }
    }

    /**
     * Creates a login-blocked 'Fleet: Driver' staff row for a rider who has
     * no matching staff account yet. Returns the new staffid, or null if
     * provisioning failed for any reason (e.g. schema drift) — callers
     * treat that the same as "not linked yet", never a hard failure, since
     * an admin can always link the rider manually later.
     */
    private function auto_provision_staff($rider)
    {
        $role = $this->db->where('name', 'Fleet: Driver')->get(db_prefix() . 'roles')->row();
        if (!$role) {
            return null;
        }

        $name_parts = preg_split('/\s+/', trim($rider->name), 2);
        $firstname  = $name_parts[0] !== '' ? $name_parts[0] : 'Rider';
        $lastname   = $name_parts[1] ?? $rider->id;

        try {
            $this->load->model('staff_model');
            $staff_id = $this->staff_model->add([
                'firstname'   => $firstname,
                'lastname'    => $lastname,
                // Synthetic and unreachable on purpose — this staff row is
                // never meant to be logged into; a real address would just
                // be a dangling password-reset target.
                'email'       => 'rider-' . $rider->id . '-' . $rider->phone . '@riders.invalid',
                'phonenumber' => $rider->phone,
                'password'    => bin2hex(random_bytes(32)),
                'role'        => $role->roleid,
                'active'      => 0,
            ]);
            return $staff_id ?: null;
        } catch (\Throwable $e) {
            log_message('error', 'Rider auto-provision staff failed: ' . $e->getMessage());
            return null;
        }
    }

    public function issue_token($rider_id)
    {
        $token = bin2hex(random_bytes(32));
        $this->db->insert($this->tokens_table, [
            'rider_id'   => $rider_id,
            'token_hash' => hash('sha256', $token),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return $token;
    }

    /**
     * @return object|null the rider row, or null if the token is invalid/expired
     */
    public function authenticate_token($token)
    {
        if (empty($token)) {
            return null;
        }

        $token_row = $this->db->where('token_hash', hash('sha256', $token))->get($this->tokens_table)->row();
        if (!$token_row) {
            return null;
        }

        $this->db->where('id', $token_row->id)->update($this->tokens_table, ['last_used_at' => date('Y-m-d H:i:s')]);

        $rider = $this->find($token_row->rider_id);
        return ($rider && $rider->status === 'active') ? $rider : null;
    }

    public function revoke_token($token)
    {
        if (empty($token)) {
            return;
        }
        $this->db->where('token_hash', hash('sha256', $token))->delete($this->tokens_table);
    }
}
