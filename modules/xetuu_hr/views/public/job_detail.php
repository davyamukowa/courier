<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $o = $opening; ?>

<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0">

<style>
/* ── Back bar ── */
.jd-back{display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:#475569;text-decoration:none;margin-bottom:22px;padding:7px 14px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;transition:border-color .15s;}
.jd-back:hover{border-color:#16a34a;color:#16a34a;}
.jd-back-icon{font-family:'Material Symbols Outlined';font-size:16px;}

/* ── Hero ── */
.jd-hero{background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);border-radius:14px;padding:30px 28px;margin-bottom:24px;}
.jd-breadcrumb{display:flex;align-items:center;gap:5px;font-size:11.5px;color:rgba(255,255,255,.45);margin-bottom:16px;}
.jd-breadcrumb a{color:rgba(255,255,255,.45);text-decoration:none;}
.jd-breadcrumb a:hover{color:rgba(255,255,255,.75);}
.jd-breadcrumb-sep{font-family:'Material Symbols Outlined';font-size:13px;}
.jd-status{display:inline-flex;align-items:center;gap:5px;background:#16a34a;color:#fff;font-size:10.5px;font-weight:700;padding:3px 10px;border-radius:20px;margin-bottom:10px;}
.jd-status::before{content:'';width:5px;height:5px;background:#86efac;border-radius:50%;}
.jd-title{font-size:clamp(22px,3.5vw,34px);font-weight:900;color:#fff;margin:0 0 14px;line-height:1.2;}
.jd-chips{display:flex;flex-wrap:wrap;gap:7px;}
.jd-dchip{display:inline-flex;align-items:center;gap:4px;font-size:12px;color:rgba(255,255,255,.8);background:rgba(255,255,255,.1);padding:4px 11px;border-radius:16px;border:1px solid rgba(255,255,255,.12);}
.jd-dchip-icon{font-family:'Material Symbols Outlined';font-size:13px;color:#4ade80;}

/* ── Layout ── */
.jd-layout{display:grid;grid-template-columns:1fr 320px;gap:22px;align-items:start;}
@media(max-width:768px){.jd-layout{grid-template-columns:1fr;}}

/* ── Left cards ── */
.jd-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;margin-bottom:18px;}
.jd-card-section{padding:22px 26px;border-bottom:1px solid #f1f5f9;}
.jd-card-section:last-child{border-bottom:none;}
.jd-section-label{font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#16a34a;margin-bottom:14px;display:flex;align-items:center;gap:5px;}
.jd-section-label-icon{font-family:'Material Symbols Outlined';font-size:14px;}
.jd-summary-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.jd-summary-item{background:#f8fafc;border:1px solid #e2e8f0;border-radius:9px;padding:12px 14px;}
.jd-summary-lbl{font-size:10.5px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;}
.jd-summary-val{font-size:15px;font-weight:800;color:#0f172a;}
.jd-desc{font-size:14px;line-height:1.8;color:#334155;}
.jd-desc h1,.jd-desc h2,.jd-desc h3{font-weight:700;color:#0f172a;margin:18px 0 8px;}
.jd-desc ul,.jd-desc ol{padding-left:20px;margin:10px 0;}
.jd-desc li{margin-bottom:5px;}
.jd-desc p{margin-bottom:12px;}

/* ── Right: Apply card ── */
.jd-apply-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;position:sticky;top:76px;}
.jd-apply-head{background:linear-gradient(135deg,#14532d,#16a34a);padding:18px 22px;}
.jd-apply-head h3{font-size:15px;font-weight:800;color:#fff;margin:0 0 2px;}
.jd-apply-head p{font-size:11.5px;color:rgba(255,255,255,.75);margin:0;}
.jd-apply-body{padding:18px 20px;display:flex;flex-direction:column;gap:12px;}

/* Flash alerts */
.jd-alert{padding:10px 14px;border-radius:8px;font-size:12.5px;font-weight:500;display:flex;gap:7px;align-items:flex-start;}
.jd-alert.success{background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;}
.jd-alert.danger{background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;}
.jd-alert-icon{font-family:'Material Symbols Outlined';font-size:15px;flex-shrink:0;margin-top:1px;}

/* Form */
.jd-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.jd-field label{display:block;font-size:10.5px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;}
.jd-field label span{color:#ef4444;}
.jd-field input,.jd-field textarea{width:100%;padding:8px 11px;border:1.5px solid #e2e8f0;border-radius:7px;font-size:13px;font-family:inherit;color:#0f172a;outline:none;transition:border-color .15s,box-shadow .15s;background:#fff;}
.jd-field input:focus,.jd-field textarea:focus{border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.08);}
.jd-field input[type="file"]{padding:7px;font-size:12px;cursor:pointer;}
.jd-field .help{font-size:10.5px;color:#94a3b8;margin-top:3px;}
.jd-divider{font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;padding-top:2px;border-top:1px solid #f1f5f9;}
.jd-submit{width:100%;padding:12px;border:none;border-radius:9px;background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;font-size:13.5px;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;font-family:inherit;transition:transform .15s,box-shadow .15s;}
.jd-submit:hover{transform:translateY(-1px);box-shadow:0 5px 18px rgba(22,163,74,.32);}
.jd-submit-icon{font-family:'Material Symbols Outlined';font-size:17px;}
.jd-form-note{font-size:10.5px;color:#94a3b8;text-align:center;}

/* Share card */
.jd-share-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px;margin-top:14px;}
.jd-share-label{font-size:10.5px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.07em;margin-bottom:10px;}
.jd-share-btns{display:flex;gap:7px;}
.jd-share-btn{flex:1;display:flex;align-items:center;justify-content:center;padding:8px;border-radius:7px;border:1.5px solid #e2e8f0;font-size:11.5px;font-weight:600;text-decoration:none;cursor:pointer;font-family:inherit;transition:border-color .15s;}
.jd-share-btn:hover{border-color:#16a34a;}
</style>

<!-- Back -->
<a href="<?php echo site_url('xetuu_hr/jobs'); ?>" class="jd-back">
    <span class="jd-back-icon">arrow_back</span> All Jobs
</a>

<!-- Hero -->
<div class="jd-hero">
    <div class="jd-breadcrumb">
        <a href="<?php echo site_url('xetuu_hr/jobs'); ?>">Careers</a>
        <span class="jd-breadcrumb-sep">chevron_right</span>
        <span><?php echo htmlspecialchars($o->title); ?></span>
    </div>
    <div class="jd-status">Actively Hiring</div>
    <h1 class="jd-title"><?php echo htmlspecialchars($o->title); ?></h1>
    <div class="jd-chips">
        <?php if ($o->department_name): ?>
        <span class="jd-dchip"><span class="jd-dchip-icon">corporate_fare</span><?php echo htmlspecialchars($o->department_name); ?></span>
        <?php endif; ?>
        <?php if ($o->designation_name): ?>
        <span class="jd-dchip"><span class="jd-dchip-icon">badge</span><?php echo htmlspecialchars($o->designation_name); ?></span>
        <?php endif; ?>
        <span class="jd-dchip"><span class="jd-dchip-icon">group</span><?php echo (int)$o->no_of_positions; ?> position<?php echo $o->no_of_positions != 1 ? 's' : ''; ?></span>
        <?php if ($o->close_date): ?>
        <span class="jd-dchip"><span class="jd-dchip-icon">event</span>Closes <?php echo date('d M Y', strtotime($o->close_date)); ?></span>
        <?php endif; ?>
        <?php if ($o->expected_salary): ?>
        <span class="jd-dchip"><span class="jd-dchip-icon">payments</span><?php echo number_format($o->expected_salary, 0); ?>/mo</span>
        <?php endif; ?>
    </div>
</div>

<!-- Layout -->
<div class="jd-layout">

    <!-- LEFT: Details -->
    <div>
        <!-- Summary grid -->
        <div class="jd-card">
            <div class="jd-card-section">
                <div class="jd-section-label"><span class="jd-section-label-icon">info</span> Role Summary</div>
                <div class="jd-summary-grid">
                    <div class="jd-summary-item">
                        <div class="jd-summary-lbl">Department</div>
                        <div class="jd-summary-val"><?php echo htmlspecialchars($o->department_name ?: '—'); ?></div>
                    </div>
                    <div class="jd-summary-item">
                        <div class="jd-summary-lbl">Level</div>
                        <div class="jd-summary-val"><?php echo htmlspecialchars($o->designation_name ?: '—'); ?></div>
                    </div>
                    <div class="jd-summary-item">
                        <div class="jd-summary-lbl">Open Positions</div>
                        <div class="jd-summary-val"><?php echo (int)$o->no_of_positions; ?></div>
                    </div>
                    <div class="jd-summary-item">
                        <div class="jd-summary-lbl">Deadline</div>
                        <div class="jd-summary-val"><?php echo $o->close_date ? date('d M Y', strtotime($o->close_date)) : 'Open'; ?></div>
                    </div>
                    <?php if ($o->expected_salary): ?>
                    <div class="jd-summary-item" style="grid-column:span 2;">
                        <div class="jd-summary-lbl">Salary Range</div>
                        <div class="jd-summary-val" style="color:#16a34a;"><?php echo number_format($o->expected_salary, 0); ?> / month</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Job description -->
        <?php if (!empty($o->description)): ?>
        <div class="jd-card">
            <div class="jd-card-section">
                <div class="jd-section-label"><span class="jd-section-label-icon">description</span> Job Description</div>
                <div class="jd-desc"><?php echo html_entity_decode($o->description); ?></div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- RIGHT: Apply -->
    <div>
        <div class="jd-apply-card">
            <div class="jd-apply-head">
                <h3>Apply for this role</h3>
                <p>Takes less than 2 minutes</p>
            </div>

            <?php
            // Read Perfex flash alerts
            $_ci  = get_instance();
            $_s   = $_ci->session->flashdata('message-success');
            $_d   = $_ci->session->flashdata('message-danger');
            if ($_s): ?>
            <div style="padding:12px 20px 0;">
                <div class="jd-alert success"><span class="jd-alert-icon">check_circle</span><?php echo htmlspecialchars($_s); ?></div>
            </div>
            <?php elseif ($_d): ?>
            <div style="padding:12px 20px 0;">
                <div class="jd-alert danger"><span class="jd-alert-icon">error</span><?php echo htmlspecialchars($_d); ?></div>
            </div>
            <?php endif; ?>

            <form action="<?php echo site_url('xetuu_hr/jobs/apply'); ?>" method="post" enctype="multipart/form-data">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                <input type="hidden" name="job_opening_id" value="<?php echo (int)$o->id; ?>">

                <div class="jd-apply-body">
                    <div class="jd-row">
                        <div class="jd-field">
                            <label>First Name <span>*</span></label>
                            <input type="text" name="first_name" required placeholder="Jane">
                        </div>
                        <div class="jd-field">
                            <label>Last Name <span>*</span></label>
                            <input type="text" name="last_name" required placeholder="Doe">
                        </div>
                    </div>
                    <div class="jd-field">
                        <label>Email Address <span>*</span></label>
                        <input type="email" name="email" required placeholder="jane@example.com">
                    </div>
                    <div class="jd-field">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" placeholder="+254 700 000 000">
                    </div>
                    <div class="jd-divider">Resume / CV</div>
                    <div class="jd-field">
                        <label>Upload Resume</label>
                        <input type="file" name="resume" accept=".pdf,.doc,.docx">
                        <span class="help">PDF, DOC or DOCX — max 5 MB</span>
                    </div>
                    <button type="submit" class="jd-submit">
                        <span class="jd-submit-icon">send</span> Submit Application
                    </button>
                    <p class="jd-form-note">We review every application and respond within 5–7 business days.</p>
                </div>
            </form>
        </div>

        <!-- Share -->
        <div class="jd-share-card">
            <div class="jd-share-label">Share this role</div>
            <div class="jd-share-btns">
                <?php $su = urlencode(current_url()); $st = urlencode('Check out: ' . $o->title); ?>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $su; ?>" target="_blank" rel="noopener"
                   class="jd-share-btn" style="color:#0a66c2;">LinkedIn</a>
                <a href="https://twitter.com/intent/tweet?text=<?php echo $st; ?>&url=<?php echo $su; ?>" target="_blank" rel="noopener"
                   class="jd-share-btn" style="color:#0f172a;">Twitter</a>
                <button onclick="navigator.clipboard.writeText(location.href).then(function(){this.textContent='Copied!';}.bind(this))"
                   class="jd-share-btn" style="color:#374151;background:#fff;">Copy Link</button>
            </div>
        </div>
    </div>

</div>
