<?php defined('BASEPATH') or exit('No direct script access allowed');
$base = admin_url('xetuu_hr');
$reviewer_type_colors = [
    'Self'        => ['#7c3aed','#f5f3ff'],
    'Peer'        => ['#2563eb','#eff6ff'],
    'Manager'     => ['#16a34a','#f0fdf4'],
    'Subordinate' => ['#ca8a04','#fef9c3'],
    'Client'      => ['#dc2626','#fef2f2'],
];
$csrf_token = $this->security->get_csrf_token_name();
$csrf_hash  = $this->security->get_csrf_hash();
?>
<?php init_head(); ?>
<?php $this->load->view('xetuu_hr/admin/layout/_topnav'); ?>
<style>
.pf-page{padding:24px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
.pf-two-col{display:grid;grid-template-columns:1fr 320px;gap:20px;}
@media(max-width:960px){.pf-two-col{grid-template-columns:1fr;}}
.pf-card{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 20px rgba(0,0,0,.06);overflow:hidden;margin-bottom:16px;}
.pf-chip{display:inline-block;padding:3px 10px;border-radius:999px;font-size:10px;font-weight:700;}
.pf-reviewer-row{display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid #f3f4f6;}
.pf-reviewer-row:last-child{border-bottom:none;}
.pf-q-row{padding:14px 20px;border-bottom:1px solid #f3f4f6;}
.pf-q-row:last-child{border-bottom:none;}
</style>
<div class="pf-page">
    <div style="font-size:11px;color:#6b7280;margin-bottom:12px;">
        <a href="<?php echo $base; ?>/performance" style="color:#6b7280;text-decoration:none;">Performance</a> /
        <a href="<?php echo $base; ?>/performance/feedback" style="color:#6b7280;text-decoration:none;">360° Feedback</a> / View
    </div>
    <div class="pf-two-col">
        <div>
            <!-- Hero -->
            <div class="pf-card">
                <div style="background:linear-gradient(135deg,#0f0f1a,#1e3a8a);padding:20px 24px;">
                    <div style="font-size:18px;font-weight:800;color:#fff;margin-bottom:4px;"><?php echo htmlspecialchars($fb->title); ?></div>
                    <div style="font-size:12px;color:rgba(255,255,255,.6);">
                        Appraisee: <strong><?php echo htmlspecialchars($fb->appraisee_name??'—'); ?></strong>
                        <?php if ($fb->anonymous): ?> · <span style="color:#a5b4fc;">Anonymous</span><?php endif; ?>
                        <?php if ($fb->deadline): ?> · Deadline: <?php echo date('d M Y',strtotime($fb->deadline)); ?><?php endif; ?>
                    </div>
                </div>
                <div style="padding:14px 20px;display:flex;gap:8px;">
                    <?php if ($fb->status === 'Draft'): ?>
                    <form method="post" action="<?php echo $base; ?>/performance/feedback/send/<?php echo $fb->id; ?>">
                        <?php echo form_hidden($csrf_token, $csrf_hash); ?>
                        <button type="submit" class="btn btn-primary" style="border-radius:8px;background:#2563eb;border-color:#2563eb;font-weight:700;display:flex;align-items:center;gap:6px;">
                            <span class="material-symbols-outlined" style="font-size:15px;">send</span> Send to Reviewers
                        </button>
                    </form>
                    <?php endif; ?>
                    <a href="<?php echo $base; ?>/performance/feedback" class="btn btn-default" style="border-radius:8px;">Back</a>
                </div>
            </div>

            <!-- Reviewers -->
            <div class="pf-card">
                <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">
                    <div style="font-size:14px;font-weight:700;color:#111827;">Reviewers (<?php echo count($fb->reviewers); ?>)</div>
                    <?php if ($fb->status === 'Draft'): ?>
                    <button onclick="document.getElementById('addReviewerPanel').style.display='block'" class="btn btn-xs btn-primary" style="border-radius:6px;background:#2563eb;border-color:#2563eb;font-size:11px;">+ Add Reviewer</button>
                    <?php endif; ?>
                </div>
                <?php if ($fb->status === 'Draft'): ?>
                <div id="addReviewerPanel" style="display:none;padding:16px 20px;background:#f9fafb;border-bottom:1px solid #f3f4f6;">
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:8px;align-items:end;flex-wrap:wrap;">
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#374151;display:block;margin-bottom:3px;">Type</label>
                            <select id="addRvType" class="form-control" style="height:32px;font-size:12px;padding:2px 6px;">
                                <?php foreach (['Self','Peer','Manager','Subordinate','Client'] as $t): ?>
                                <option value="<?php echo $t; ?>"><?php echo $t; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#374151;display:block;margin-bottom:3px;">Employee</label>
                            <select id="addRvEmp" class="form-control" style="height:32px;font-size:12px;padding:2px 6px;">
                                <option value="">— Select —</option>
                                <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo $emp->id; ?>"><?php echo htmlspecialchars($emp->full_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#374151;display:block;margin-bottom:3px;">Or External Email</label>
                            <input type="email" id="addRvEmail" class="form-control" placeholder="email@example.com" style="height:32px;font-size:12px;padding:2px 8px;">
                        </div>
                        <button onclick="addReviewer()" class="btn btn-primary" style="height:32px;border-radius:6px;background:#2563eb;border-color:#2563eb;font-size:11px;font-weight:700;">Add</button>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (empty($fb->reviewers)): ?>
                <div style="padding:24px;text-align:center;color:#9ca3af;font-size:12px;">No reviewers added yet.</div>
                <?php else: ?>
                <?php foreach ($fb->reviewers as $rv):
                    $tc = $reviewer_type_colors[$rv->reviewer_type] ?? ['#6b7280','#f3f4f6'];
                ?>
                <div class="pf-reviewer-row">
                    <span class="pf-chip" style="color:<?php echo $tc[0]; ?>;background:<?php echo $tc[1]; ?>;"><?php echo $rv->reviewer_type; ?></span>
                    <div style="flex:1;">
                        <div style="font-size:13px;font-weight:600;color:#111827;">
                            <?php echo htmlspecialchars($rv->employee_name ?? $rv->reviewer_name ?? '—'); ?>
                        </div>
                        <?php if ($rv->reviewer_email): ?>
                        <div style="font-size:11px;color:#9ca3af;"><?php echo htmlspecialchars($rv->reviewer_email); ?></div>
                        <?php endif; ?>
                    </div>
                    <span class="pf-chip" style="color:<?php echo $rv->submitted?'#16a34a':'#9ca3af'; ?>;background:<?php echo $rv->submitted?'#f0fdf4':'#f3f4f6'; ?>;">
                        <?php echo $rv->submitted ? 'Submitted' : 'Pending'; ?>
                    </span>
                    <?php if ($fb->status === 'Draft'): ?>
                    <button onclick="removeReviewer(<?php echo $rv->id; ?>, this)" style="background:none;border:none;cursor:pointer;color:#dc2626;padding:4px;">
                        <span class="material-symbols-outlined" style="font-size:16px;">delete</span>
                    </button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Questions -->
            <div class="pf-card">
                <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">
                    <div style="font-size:14px;font-weight:700;color:#111827;">Questions (<?php echo count($fb->questions); ?>)</div>
                    <?php if ($fb->status === 'Draft'): ?>
                    <button onclick="document.getElementById('addQPanel').style.display='block'" class="btn btn-xs btn-default" style="border-radius:6px;font-size:11px;">+ Add Question</button>
                    <?php endif; ?>
                </div>
                <?php if ($fb->status === 'Draft'): ?>
                <div id="addQPanel" style="display:none;padding:14px 20px;background:#f9fafb;border-bottom:1px solid #f3f4f6;">
                    <div style="display:flex;gap:8px;align-items:flex-end;">
                        <div style="flex:1;">
                            <label style="font-size:11px;font-weight:700;color:#374151;display:block;margin-bottom:3px;">Question</label>
                            <input type="text" id="addQText" class="form-control" placeholder="e.g. How effectively does this employee communicate?" style="font-size:12px;">
                        </div>
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#374151;display:block;margin-bottom:3px;">Type</label>
                            <select id="addQType" class="form-control" style="height:34px;font-size:12px;padding:2px 6px;width:100px;">
                                <option value="rating">Rating (1-5)</option>
                                <option value="text">Text</option>
                                <option value="yes_no">Yes/No</option>
                            </select>
                        </div>
                        <button onclick="addQuestion()" class="btn btn-default" style="height:34px;border-radius:6px;font-size:11px;font-weight:700;">Add</button>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (empty($fb->questions)): ?>
                <div style="padding:24px;text-align:center;color:#9ca3af;font-size:12px;">No questions yet.</div>
                <?php else: ?>
                <?php foreach ($fb->questions as $i => $q): ?>
                <div class="pf-q-row">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="font-size:11px;font-weight:800;color:#7c3aed;min-width:18px;"><?php echo $i+1; ?>.</span>
                        <div style="flex:1;font-size:13px;color:#111827;"><?php echo htmlspecialchars($q->question); ?></div>
                        <span class="pf-chip" style="color:#6b7280;background:#f3f4f6;"><?php echo $q->question_type; ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <?php
            $total = count($fb->reviewers);
            $submitted = count(array_filter($fb->reviewers, fn($r)=>$r->submitted));
            $pct = $total > 0 ? round($submitted/$total*100) : 0;
            ?>
            <div style="background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden;margin-bottom:16px;">
                <div style="background:linear-gradient(135deg,#0f0f1a,#1e3a8a);padding:16px 20px;">
                    <div style="font-size:11px;color:rgba(255,255,255,.5);">Response Rate</div>
                    <div style="font-size:36px;font-weight:900;color:#fff;"><?php echo $pct; ?>%</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.5);margin-top:2px;"><?php echo $submitted; ?> of <?php echo $total; ?> responded</div>
                    <div style="height:5px;border-radius:3px;background:rgba(255,255,255,.15);margin-top:8px;overflow:hidden;">
                        <div style="height:100%;width:<?php echo $pct; ?>%;background:#60a5fa;border-radius:3px;"></div>
                    </div>
                </div>
                <div style="padding:12px 16px;font-size:12px;color:#6b7280;">
                    <div style="margin-bottom:4px;"><strong style="color:#374151;">Status:</strong> <?php echo $fb->status; ?></div>
                    <div style="margin-bottom:4px;"><strong style="color:#374151;">Created:</strong> <?php echo date('d M Y',strtotime($fb->date_created)); ?></div>
                    <?php if ($fb->deadline): ?>
                    <div><strong style="color:#374151;">Deadline:</strong> <?php echo date('d M Y',strtotime($fb->deadline)); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Type breakdown -->
            <?php
            $by_type = [];
            foreach ($fb->reviewers as $rv) $by_type[$rv->reviewer_type] = ($by_type[$rv->reviewer_type]??0)+1;
            ?>
            <?php if (!empty($by_type)): ?>
            <div style="background:#fff;border-radius:14px;padding:14px 16px;box-shadow:0 1px 3px rgba(0,0,0,.05);margin-bottom:16px;">
                <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:10px;">By Reviewer Type</div>
                <?php foreach ($by_type as $t => $cnt):
                    $tc = $reviewer_type_colors[$t] ?? ['#6b7280','#f3f4f6'];
                ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:5px 0;font-size:12px;border-bottom:1px solid #f3f4f6;">
                    <span class="pf-chip" style="color:<?php echo $tc[0]; ?>;background:<?php echo $tc[1]; ?>;"><?php echo $t; ?></span>
                    <strong style="color:<?php echo $tc[0]; ?>"><?php echo $cnt; ?></strong>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
var FEEDBACK_ID = <?php echo $fb->id; ?>;
var CSRF_NAME   = '<?php echo $csrf_token; ?>';
var CSRF_HASH   = '<?php echo $csrf_hash; ?>';
var BASE        = '<?php echo $base; ?>';

function addReviewer(){
    var type  = document.getElementById('addRvType').value;
    var empId = document.getElementById('addRvEmp').value;
    var email = document.getElementById('addRvEmail').value;
    fetch(BASE + '/performance/feedback/add_reviewer/' + FEEDBACK_ID, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: CSRF_NAME+'='+CSRF_HASH+'&reviewer_type='+encodeURIComponent(type)+'&reviewer_employee_id='+encodeURIComponent(empId)+'&reviewer_email='+encodeURIComponent(email)
    }).then(r=>r.json()).then(d=>{ if(d.success) location.reload(); });
}

function removeReviewer(id, btn){
    if (!confirm('Remove this reviewer?')) return;
    fetch(BASE + '/performance/feedback/remove_reviewer/' + id, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: CSRF_NAME+'='+CSRF_HASH
    }).then(r=>r.json()).then(d=>{ if(d.success) btn.closest('.pf-reviewer-row').remove(); });
}

function addQuestion(){
    var q    = document.getElementById('addQText').value;
    var type = document.getElementById('addQType').value;
    if (!q.trim()) return;
    fetch(BASE + '/performance/feedback/add_question/' + FEEDBACK_ID, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: CSRF_NAME+'='+CSRF_HASH+'&question='+encodeURIComponent(q)+'&question_type='+encodeURIComponent(type)+'&sort_order=99'
    }).then(r=>r.json()).then(d=>{ if(d.success) location.reload(); });
}
</script>
<?php init_tail(); ?>
