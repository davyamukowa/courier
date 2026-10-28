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
    .cgs-input-error,
    .custom-input.cgs-input-error,
    .custom-select.cgs-input-error,
    .custom-textarea.cgs-input-error,
    .select2-container.cgs-input-error .select2-selection {
        border: 2px solid #e74c3c !important;
        background-color: #fff9f9 !important;
        box-shadow: 0 0 4px rgba(231, 76, 60, 0.4) !important;
    }
    .cgs-field-error-msg {
        color: #e74c3c !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        margin-top: 4px !important;
        display: block !important;
    }
    .cgs-validation-alert {
        background: #fde8e8 !important;
        border-left: 5px solid #e74c3c !important;
        color: #9b1c1c !important;
        padding: 14px 18px !important;
        border-radius: 6px !important;
        margin-bottom: 20px !important;
        font-size: 14px !important;
        box-shadow: 0 2px 6px rgba(231,76,60,0.15) !important;
    }
    .cgs-validation-alert h5 {
        color: #9b1c1c !important;
        font-weight: 700 !important;
        margin: 0 0 6px 0 !important;
        font-size: 15px !important;
    }
    .cgs-validation-alert ul {
        margin: 6px 0 0 18px !important;
        padding: 0 !important;
    }
    .cgs-validation-alert li {
        margin-bottom: 3px !important;
    }
</style>

<section id="creat_agent_section" style="display: block;" class="custom-section">
    <?php echo form_open('admin/courier_goshipping/agents/store', [
            'id' => 'create-agent-form',
            'enctype' => 'multipart/form-data'
    ]); ?>
    <div class="custom-container">
        <?php
        $agent_validation_errors = $this->session->flashdata('agent_validation_errors');
        ?>
        <?php if (!empty($agent_validation_errors) && is_array($agent_validation_errors)): ?>
            <div id="cgs-agent-server-validation-alert" class="cgs-validation-alert">
                <h5><i class="fa fa-exclamation-triangle"></i> Please correct the following errors:</h5>
                <ul>
                    <?php foreach ($agent_validation_errors as $err): ?>
                        <li><?php echo htmlspecialchars(strip_tags($err)); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div id="cgs-agent-client-validation-alert" class="cgs-validation-alert" style="display:none;">
            <h5><i class="fa fa-exclamation-triangle"></i> Please fix the following highlighted errors before submitting:</h5>
            <ul id="cgs-agent-client-error-list"></ul>
        </div>
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
                            <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($this->session->flashdata('type_error')); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- INDIVIDUAL SECTION -->
                <div id="individualContent">
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="first_name" class="custom-label">First Name <span class="req">*</span></label>
                                <?php $fn_err = $this->session->flashdata('first_name_error'); ?>
                                <?php echo form_input(['id' => 'first_name', 'name' => 'first_name', 'type' => 'text', 'class' => 'custom-input' . ($fn_err ? ' cgs-input-error' : ''), 'placeholder' => 'First Name', 'value' => $this->session->flashdata('first_name') ?: '']); ?>
                                <?php if ($fn_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($fn_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="last_name" class="custom-label">Last Name <span class="req">*</span></label>
                                <?php $ln_err = $this->session->flashdata('last_name_error'); ?>
                                <?php echo form_input(['id' => 'last_name', 'name' => 'last_name', 'type' => 'text', 'class' => 'custom-input' . ($ln_err ? ' cgs-input-error' : ''), 'placeholder' => 'Last Name', 'value' => $this->session->flashdata('last_name') ?: '']); ?>
                                <?php if ($ln_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($ln_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="email" class="custom-label">Email <span class="req">*</span></label>
                                <?php $em_err = $this->session->flashdata('email_error'); ?>
                                <?php echo form_input(['id' => 'email', 'name' => 'email', 'type' => 'email', 'class' => 'custom-input' . ($em_err ? ' cgs-input-error' : ''), 'placeholder' => 'Email', 'value' => $this->session->flashdata('email') ?: '']); ?>
                                <?php if ($em_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($em_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="phone_number" class="custom-label">Phone <span class="req">*</span></label>
                                <?php $ph_err = $this->session->flashdata('phone_number_error'); ?>
                                <?php echo form_input(['id' => 'phone_number', 'name' => 'phone_number', 'type' => 'text', 'class' => 'custom-input' . ($ph_err ? ' cgs-input-error' : ''), 'placeholder' => 'Phone Number', 'value' => $this->session->flashdata('phone_number') ?: '']); ?>
                                <?php if ($ph_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($ph_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="id_file">Upload ID <span class="req">*</span></label>
                                <?php $idf_err = $this->session->flashdata('id_file_error'); ?>
                                <input class="custom-input <?php echo $idf_err ? 'cgs-input-error' : ''; ?>" type="file" name="id_file" id="id_file">
                                <?php if ($idf_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($idf_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="kra_file">Upload KRA PIN <span class="req">*</span></label>
                                <?php $kraf_err = $this->session->flashdata('kra_file_error'); ?>
                                <input class="custom-input <?php echo $kraf_err ? 'cgs-input-error' : ''; ?>" type="file" name="kra_file" id="kra_file">
                                <?php if ($kraf_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($kraf_err); ?></div>
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
                                <?php $addr_err = $this->session->flashdata('address_error'); ?>
                                <textarea id="address" name="address" class="custom-textarea <?php echo $addr_err ? 'cgs-input-error' : ''; ?>" rows="2"
                                          autocomplete="off"
                                          placeholder="Enter address here..."><?php echo $this->session->flashdata('address') ?: ''; ?></textarea>
                                <?php if ($addr_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($addr_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="username" class="custom-label">Username <span class="req">*</span></label>
                                <?php $usr_err = $this->session->flashdata('username_error'); ?>
                                <?php echo form_input(['id' => 'username', 'name' => 'username', 'type' => 'text', 'class' => 'custom-input' . ($usr_err ? ' cgs-input-error' : ''), 'placeholder' => 'Username', 'value' => $this->session->flashdata('username') ?: '']); ?>
                                <?php if ($usr_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($usr_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="password" class="custom-label">Password <span class="req">*</span></label>
                                <?php $pwd_err = $this->session->flashdata('password_error'); ?>
                                <?php echo form_input(['id' => 'password', 'name' => 'password', 'type' => 'password', 'class' => 'custom-input' . ($pwd_err ? ' cgs-input-error' : ''), 'placeholder' => 'Password', 'value' => $this->session->flashdata('password') ?: '']); ?>
                                <?php if ($pwd_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($pwd_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="country_id" class="custom-label">Country <span class="req">*</span></label>
                                <?php $cid_err = $this->session->flashdata('country_id_error'); ?>
                                <?php echo form_dropdown('country_id', array_column($countries, 'short_name', 'country_id'), $this->session->flashdata('country_id') ?: $kenya_id, ['id' => 'country_id', 'class' => 'custom-select' . ($cid_err ? ' cgs-input-error' : '')]); ?>
                                <?php if ($cid_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($cid_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="station" class="custom-label">Service Point <span class="req">*</span></label>
                                <?php $stn_err = $this->session->flashdata('state_id_error') ?: $this->session->flashdata('station_error'); ?>
                                <select id="station" name="station" class="custom-select <?php echo $stn_err ? 'cgs-input-error' : ''; ?>">
                                    <option value="">-- Select Service Point --</option>
                                </select>
                                <input type="hidden" name="state_id" id="state_id" value="<?php echo htmlspecialchars($this->session->flashdata('state_id') ?: ''); ?>">
                                <?php if ($stn_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($stn_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="unique_number" class="custom-label">Agent Number</label>
                                <?php $un_err = $this->session->flashdata('unique_number_error'); ?>
                                <?php echo form_input(['id' => 'unique_number', 'name' => 'unique_number', 'type' => 'text', 'class' => 'custom-input' . ($un_err ? ' cgs-input-error' : ''), 'placeholder' => 'Agent Number', 'value' => $this->session->flashdata('unique_number') ?: '', 'readonly' => 'readonly']); ?>
                                <?php if ($un_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($un_err); ?></div>
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
                                <?php $cn_err = $this->session->flashdata('company_name_error'); ?>
                                <?php echo form_input(['id' => 'company_name', 'name' => 'company_name', 'type' => 'text', 'class' => 'custom-input' . ($cn_err ? ' cgs-input-error' : ''), 'placeholder' => 'Company Name', 'value' => $this->session->flashdata('company_name') ?: '']); ?>
                                <?php if ($cn_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($cn_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="contact_name" class="custom-label">Contact Person Name <span class="req">*</span></label>
                                <?php $ctn_err = $this->session->flashdata('contact_name_error'); ?>
                                <?php echo form_input(['id' => 'contact_name', 'name' => 'contact_name', 'type' => 'text', 'class' => 'custom-input' . ($ctn_err ? ' cgs-input-error' : ''), 'placeholder' => 'Contact Person Name', 'value' => $this->session->flashdata('contact_name') ?: '']); ?>
                                <?php if ($ctn_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($ctn_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="contact_phone_number" class="custom-label">Contact Person Phone <span class="req">*</span></label>
                                <?php $cph_err = $this->session->flashdata('contact_phone_number_error'); ?>
                                <?php echo form_input(['id' => 'contact_phone_number', 'name' => 'contact_phone_number', 'type' => 'text', 'class' => 'custom-input' . ($cph_err ? ' cgs-input-error' : ''), 'placeholder' => 'Contact Person Phone Number', 'value' => $this->session->flashdata('contact_phone_number') ?: '']); ?>
                                <?php if ($cph_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($cph_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="contact_email" class="custom-label">Contact Person Email <span class="req">*</span></label>
                                <?php $cem_err = $this->session->flashdata('contact_email_error'); ?>
                                <?php echo form_input(['id' => 'contact_email', 'name' => 'contact_email', 'type' => 'email', 'class' => 'custom-input' . ($cem_err ? ' cgs-input-error' : ''), 'placeholder' => 'Contact Person Email', 'value' => $this->session->flashdata('contact_email') ?: '']); ?>
                                <?php if ($cem_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($cem_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="company_id_file">Upload ID <span class="req">*</span></label>
                                <?php $cidf_err = $this->session->flashdata('company_id_file_error'); ?>
                                <input class="custom-input <?php echo $cidf_err ? 'cgs-input-error' : ''; ?>" type="file" name="company_id_file" id="company_id_file">
                                <?php if ($cidf_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($cidf_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="company_kra_file">Upload KRA PIN <span class="req">*</span></label>
                                <?php $ckraf_err = $this->session->flashdata('company_kra_file_error'); ?>
                                <input class="custom-input <?php echo $ckraf_err ? 'cgs-input-error' : ''; ?>" type="file" name="company_kra_file" id="company_kra_file">
                                <?php if ($ckraf_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($ckraf_err); ?></div>
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
                                <?php $caddr_err = $this->session->flashdata('company_address_error'); ?>
                                <textarea id="company_address" name="company_address" class="custom-textarea <?php echo $caddr_err ? 'cgs-input-error' : ''; ?>" rows="2"
                                          autocomplete="off"
                                          placeholder="Enter company address here..."><?php echo $this->session->flashdata('company_address') ?: ''; ?></textarea>
                                <?php if ($caddr_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($caddr_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="corporation_certificate_file">Upload Certificate of Corporation <span class="req">*</span></label>
                                <?php $ccert_err = $this->session->flashdata('corporation_certificate_file_error'); ?>
                                <input class="custom-input <?php echo $ccert_err ? 'cgs-input-error' : ''; ?>" type="file" name="corporation_certificate_file" id="corporation_certificate_file">
                                <?php if ($ccert_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($ccert_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="company_username" class="custom-label">Username <span class="req">*</span></label>
                                <?php $cuser_err = $this->session->flashdata('company_username_error'); ?>
                                <?php echo form_input(['id' => 'company_username', 'name' => 'company_username', 'type' => 'text', 'class' => 'custom-input' . ($cuser_err ? ' cgs-input-error' : ''), 'placeholder' => 'Company Username', 'value' => $this->session->flashdata('company_username') ?: '']); ?>
                                <?php if ($cuser_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($cuser_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="company_password" class="custom-label">Password <span class="req">*</span></label>
                                <?php $cpwd_err = $this->session->flashdata('company_password_error'); ?>
                                <?php echo form_input(['id' => 'company_password', 'name' => 'company_password', 'type' => 'password', 'class' => 'custom-input' . ($cpwd_err ? ' cgs-input-error' : ''), 'placeholder' => 'Company Password', 'value' => $this->session->flashdata('company_password') ?: '']); ?>
                                <?php if ($cpwd_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($cpwd_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="company_country_id" class="custom-label">Country <span class="req">*</span></label>
                                <?php $ccid_err = $this->session->flashdata('company_country_id_error'); ?>
                                <?php echo form_dropdown(
                                        'company_country_id',
                                        array_column($countries, 'short_name', 'country_id'),
                                        $this->session->flashdata('company_country_id') ?: $kenya_id,
                                        ['id' => 'company_country_id', 'class' => 'custom-select' . ($ccid_err ? ' cgs-input-error' : ''), 'style' => 'width: 100%;']
                                ); ?>
                                <?php if ($ccid_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($ccid_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="padding-left:15px; padding-right:15px;" class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="company_station" class="custom-label">Service Point <span class="req">*</span></label>
                                <?php $cstn_err = $this->session->flashdata('company_state_id_error') ?: $this->session->flashdata('company_station_error'); ?>
                                <select id="company_station" name="company_station" class="custom-select <?php echo $cstn_err ? 'cgs-input-error' : ''; ?>">
                                    <option value="">-- Select Service Point --</option>
                                </select>
                                <input type="hidden" name="company_state_id" id="company_state_id" value="<?php echo htmlspecialchars($this->session->flashdata('company_state_id') ?: ''); ?>">
                                <?php if ($cstn_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($cstn_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="custom-form-group">
                                <label for="company_unique_number" class="custom-label">Agent Number</label>
                                <?php $cun_err = $this->session->flashdata('company_unique_number_error'); ?>
                                <?php echo form_input(['id' => 'company_unique_number', 'name' => 'company_unique_number', 'type' => 'text', 'class' => 'custom-input' . ($cun_err ? ' cgs-input-error' : ''), 'placeholder' => 'Agent Number', 'value' => $this->session->flashdata('company_unique_number') ?: '', 'readonly' => 'readonly']); ?>
                                <?php if ($cun_err): ?>
                                    <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($cun_err); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="padding-left:15px; padding-right:15px;" class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="custom-form-group">
                            <label for="role_id" class="custom-label">Role <span class="req">*</span></label>
                            <?php
                            $selected_role = $this->session->flashdata('role_id') ?: ($courier_agent_role_id ?? '');
                            ?>
                            <select id="role_id" name="role_id" class="custom-select">
                                <?php if (!empty($roles)): foreach ($roles as $role): ?>
                                    <option value="<?php echo $role->roleid; ?>" <?php echo ((string)$selected_role === (string)$role->roleid) ? 'selected' : ''; ?>>
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
                            <?php
                            $selected_branch = $this->session->flashdata('agent_branch_id') ?: (!empty($default_branch_id) ? $default_branch_id : '');
                            $ab_err = $this->session->flashdata('agent_branch_id_error');
                            ?>
                            <select id="agent_branch_id" name="agent_branch_id" class="custom-select <?php echo $ab_err ? 'cgs-input-error' : ''; ?>" required>
                                <option value="">-- Select Branch / Office --</option>
                                <?php if (!empty($branches)): foreach ($branches as $b): ?>
                                    <option value="<?php echo (int) $b->id; ?>" <?php echo ((string)$selected_branch === (string)$b->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($b->name); ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                            <p class="help-block" style="font-size:11px;color:#888;margin-top:4px;">This agent's shipments, pickups, and documents are scoped to this branch — required since as an admin you don't have one assigned to yourself.</p>
                            <?php if ($ab_err): ?>
                                <div class="cgs-field-error-msg"><i class="fa fa-exclamation-circle"></i> <?php echo strip_tags($ab_err); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="cgs-btn cgs-btn--primary">Add Agent</button>
    </div>
    <?php echo form_close(); ?>
</section>
