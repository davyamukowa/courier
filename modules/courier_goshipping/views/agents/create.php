<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
// Find Kenya's country_id (used as default selection)
$kenya_id = '';
foreach ($countries as $c) {
    if (strcasecmp(trim($c->short_name), 'Kenya') === 0) {
        $kenya_id = $c->country_id;
        break;
    }
}
?>
<!-- Record Agent -->
<style>
    .req { color: #e74c3c; margin-left: 2px; }
</style>

<section id="creat_agent_section" style="display: block;" class="custom-section">
    <?php echo form_open('admin/courier_goshipping/agents/store', [
            'id' => 'create-agent-form',
            'enctype' => 'multipart/form-data'
    ]); ?>
    <div class="custom-container">
        <div class="custom-form-grid">
            <div style="margin-top:-10px;" class="row section-container">
                <div class="col-md-12 col-sm-12">
                    <div class="custom-form-group">
                        <?php
                        $show_company = $this->session->userdata('show_company_section') ?? false;
                        ?>
                        <label for="type" class="custom-label">Agent Type <span class="req">*</span></label>
                        <select id="type" name="type" onchange="toggleAgentType()" class="custom-select">
                            <option value="individual">Individual</option>
                            <option value="company" <?php echo $show_company === true ? 'selected' : ''; ?>>Company</option>
                        </select>
                        <?php if ($this->session->flashdata('type_error')): ?>
                            <div class="text-danger"><?php echo $this->session->flashdata('type_error'); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- INDIVIDUAL SECTION -->
                <div id="individualContent">
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="first_name" class="custom-label">First Name <span class="req">*</span></label>
                                <?php echo form_input(['id' => 'first_name', 'name' => 'first_name', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'First Name', 'value' => $this->session->flashdata('first_name') ?: '']); ?>
                                <?php if ($this->session->flashdata('first_name_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('first_name_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="last_name" class="custom-label">Last Name <span class="req">*</span></label>
                                <?php echo form_input(['id' => 'last_name', 'name' => 'last_name', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Last Name', 'value' => $this->session->flashdata('last_name') ?: '']); ?>
                                <?php if ($this->session->flashdata('last_name_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('last_name_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="email" class="custom-label">Email <span class="req">*</span></label>
                                <?php echo form_input(['id' => 'email', 'name' => 'email', 'type' => 'email', 'class' => 'custom-input', 'placeholder' => 'Email', 'value' => $this->session->flashdata('email') ?: '']); ?>
                                <?php if ($this->session->flashdata('email_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('email_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="phone_number" class="custom-label">Phone <span class="req">*</span></label>
                                <?php echo form_input(['id' => 'phone_number', 'name' => 'phone_number', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Phone Number', 'value' => $this->session->flashdata('phone_number') ?: '']); ?>
                                <?php if ($this->session->flashdata('phone_number_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('phone_number_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="id_file">Upload ID <span class="req">*</span></label>
                                <input class="custom-input" type="file" name="id_file" id="id_file">
                                <?php if ($this->session->flashdata('id_file_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('id_file_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="kra_file">Upload KRA PIN <span class="req">*</span></label>
                                <input class="custom-input" type="file" name="kra_file" id="kra_file">
                                <?php if ($this->session->flashdata('kra_file_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('kra_file_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="kra_pin" class="custom-label">KRA PIN No.</label>
                                <?php echo form_input(['id' => 'kra_pin', 'name' => 'kra_pin', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'e.g. A001234567B', 'value' => $this->session->flashdata('kra_pin') ?: '']); ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="address" class="custom-label">Address <span class="req">*</span></label>
                                <textarea id="address" name="address" class="custom-textarea" rows="2"
                                          autocomplete="off"
                                          placeholder="Enter address here..."><?php echo $this->session->flashdata('address') ?: ''; ?></textarea>
                                <?php if ($this->session->flashdata('address_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('address_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="username" class="custom-label">Username <span class="req">*</span></label>
                                <?php echo form_input(['id' => 'username', 'name' => 'username', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Username', 'value' => $this->session->flashdata('username') ?: '']); ?>
                                <?php if ($this->session->flashdata('username_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('username_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="password" class="custom-label">Password <span class="req">*</span></label>
                                <?php echo form_input(['id' => 'password', 'name' => 'password', 'type' => 'password', 'class' => 'custom-input', 'placeholder' => 'Password', 'value' => $this->session->flashdata('password') ?: '']); ?>
                                <?php if ($this->session->flashdata('password_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('password_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="country_id" class="custom-label">Country <span class="req">*</span></label>
                                <?php echo form_dropdown('country_id', array_column($countries, 'short_name', 'country_id'), set_value('country_id', $kenya_id), ['id' => 'country_id', 'class' => 'custom-select']); ?>
                                <?php echo form_error('country_id', '<div class="error-message">', '</div>'); ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="station" class="custom-label">Service Point <span class="req">*</span></label>
                                <select id="station" name="station" class="custom-select">
                                    <option value="">-- Select Service Point --</option>
                                </select>
                                <input type="hidden" name="state_id" id="state_id" value="">
                                <?php echo form_error('station', '<div class="error-message">', '</div>'); ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="unique_number" class="custom-label">Agent Number</label>
                                <?php echo form_input(['id' => 'unique_number', 'name' => 'unique_number', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Agent Number', 'value' => $this->session->flashdata('unique_number') ?: '', 'readonly' => 'readonly']); ?>
                                <?php if ($this->session->flashdata('unique_number_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('unique_number_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COMPANY SECTION -->
                <div style="display:none;" id="companyContent">
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="company_name" class="custom-label">Company Name <span class="req">*</span></label>
                                <?php echo form_input(['id' => 'company_name', 'name' => 'company_name', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Company Name', 'value' => $this->session->flashdata('company_name') ?: '']); ?>
                                <?php if ($this->session->flashdata('company_name_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('company_name_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="contact_name" class="custom-label">Contact Person Name <span class="req">*</span></label>
                                <?php echo form_input(['id' => 'contact_name', 'name' => 'contact_name', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Contact Person Name', 'value' => $this->session->flashdata('contact_name') ?: '']); ?>
                                <?php if ($this->session->flashdata('contact_name_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('contact_name_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="contact_phone_number" class="custom-label">Contact Person Phone <span class="req">*</span></label>
                                <?php echo form_input(['id' => 'contact_phone_number', 'name' => 'contact_phone_number', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Contact Person Phone Number', 'value' => $this->session->flashdata('contact_phone_number') ?: '']); ?>
                                <?php if ($this->session->flashdata('contact_phone_number_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('contact_phone_number_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="contact_email" class="custom-label">Contact Person Email <span class="req">*</span></label>
                                <?php echo form_input(['id' => 'contact_email', 'name' => 'contact_email', 'type' => 'email', 'class' => 'custom-input', 'placeholder' => 'Contact Person Email', 'value' => $this->session->flashdata('contact_email') ?: '']); ?>
                                <?php if ($this->session->flashdata('contact_email_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('contact_email_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="company_id_file">Upload ID <span class="req">*</span></label>
                                <input class="custom-input" type="file" name="company_id_file" id="company_id_file">
                                <?php if ($this->session->flashdata('company_id_file_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('company_id_file_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="company_kra_file">Upload KRA PIN <span class="req">*</span></label>
                                <input class="custom-input" type="file" name="company_kra_file" id="company_kra_file">
                                <?php if ($this->session->flashdata('company_kra_file_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('company_kra_file_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="company_kra_pin" class="custom-label">KRA PIN No.</label>
                                <?php echo form_input(['id' => 'company_kra_pin', 'name' => 'company_kra_pin', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'e.g. P051234567A', 'value' => $this->session->flashdata('company_kra_pin') ?: '']); ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="company_address" class="custom-label">Company Address <span class="req">*</span></label>
                                <textarea id="company_address" name="company_address" class="custom-textarea" rows="2"
                                          autocomplete="off"
                                          placeholder="Enter company address here..."><?php echo $this->session->flashdata('company_address') ?: ''; ?></textarea>
                                <?php if ($this->session->flashdata('company_address_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('company_address_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="corporation_certificate_file">Upload Certificate of Corporation <span class="req">*</span></label>
                                <input class="custom-input" type="file" name="corporation_certificate_file" id="corporation_certificate_file">
                                <?php if ($this->session->flashdata('corporation_certificate_file_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('corporation_certificate_file_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="company_username" class="custom-label">Username <span class="req">*</span></label>
                                <?php echo form_input(['id' => 'company_username', 'name' => 'company_username', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Company Username', 'value' => $this->session->flashdata('company_username') ?: '']); ?>
                                <?php if ($this->session->flashdata('company_username_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('company_username_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="company_password" class="custom-label">Password <span class="req">*</span></label>
                                <?php echo form_input(['id' => 'company_password', 'name' => 'company_password', 'type' => 'password', 'class' => 'custom-input', 'placeholder' => 'Company Password', 'value' => $this->session->flashdata('company_password') ?: '']); ?>
                                <?php if ($this->session->flashdata('company_password_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('company_password_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="company_country_id" class="custom-label">Country <span class="req">*</span></label>
                                <?php echo form_dropdown(
                                        'company_country_id',
                                        array_column($countries, 'short_name', 'country_id'),
                                        set_value('company_country_id', $kenya_id),
                                        ['id' => 'company_country_id', 'class' => 'custom-select', 'style' => 'width: 100%;']
                                ); ?>
                                <?php echo form_error('company_country_id', '<div class="error-message">', '</div>'); ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="company_station" class="custom-label">Service Point <span class="req">*</span></label>
                                <select id="company_station" name="company_station" class="custom-select">
                                    <option value="">-- Select Service Point --</option>
                                </select>
                                <input type="hidden" name="company_state_id" id="company_state_id" value="">
                                <?php echo form_error('company_station', '<div class="error-message">', '</div>'); ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="company_unique_number" class="custom-label">Agent Number</label>
                                <?php echo form_input(['id' => 'company_unique_number', 'name' => 'company_unique_number', 'type' => 'text', 'class' => 'custom-input', 'placeholder' => 'Agent Number', 'value' => $this->session->flashdata('company_unique_number') ?: '', 'readonly' => 'readonly']); ?>
                                <?php if ($this->session->flashdata('company_unique_number_error')): ?>
                                    <div class="text-danger"><?php echo $this->session->flashdata('company_unique_number_error'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="padding-left:15px; padding-right:15px;" class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="custom-form-group">
                            <label for="role_id" class="custom-label">Role <span class="req">*</span></label>
                            <select id="role_id" name="role_id" class="custom-select">
                                <?php if (!empty($roles)): foreach ($roles as $role): ?>
                                    <option value="<?php echo $role->roleid; ?>" <?php echo (isset($courier_agent_role_id) && $role->roleid == $courier_agent_role_id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($role->name); ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                            <p class="help-block" style="font-size:11px;color:#888;margin-top:4px;">Default is "Courier: Agent" — the admin can change per-agent from the agent detail page.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="custom-form-group">
                            <label for="agent_branch_id" class="custom-label">Branch / Office <span class="req">*</span></label>
                            <select id="agent_branch_id" name="agent_branch_id" class="custom-select" required>
                                <option value="">-- Select Branch / Office --</option>
                                <?php if (!empty($branches)): foreach ($branches as $b): ?>
                                    <option value="<?php echo (int) $b->id; ?>" <?php echo (!empty($default_branch_id) && (int) $default_branch_id === (int) $b->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($b->name); ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                            <p class="help-block" style="font-size:11px;color:#888;margin-top:4px;">This agent's shipments, pickups, and documents are scoped to this branch — required since as an admin you don't have one assigned to yourself.</p>
                            <?php echo form_error('agent_branch_id', '<div class="error-message">', '</div>'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="cgs-btn cgs-btn--primary">Add Agent</button>
    </div>
    <?php echo form_close(); ?>
</section>
