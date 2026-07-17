<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="container" style="max-width: 800px; margin: 40px auto; padding: 0 15px;">
    
    <!-- Top Card -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 24px;">
        <div style="background: #16a34a; padding: 24px 32px; color: #ffffff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <h1 style="margin: 0; font-size: 24px; font-weight: 700; color:#ffffff;">Appointment Letter</h1>
                <p style="margin: 4px 0 0 0; font-size: 14px; opacity: 0.9; color: #ffffff;"><?php echo htmlspecialchars($letter->letter_number); ?></p>
            </div>
            <div>
                <span style="display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; background: <?php echo $letter->status === 'Signed' ? '#dcfce7' : '#fff7ed'; ?>; color: <?php echo $letter->status === 'Signed' ? '#16a34a' : '#c2410c'; ?>;">
                    Status: <?php echo htmlspecialchars($letter->status); ?>
                </span>
            </div>
        </div>

        <div style="padding: 32px; color: #334155; font-size: 15px; line-height: 1.6;">
            <!-- Intro -->
            <div style="margin-bottom: 30px;">
                <p style="font-weight: 600; font-size: 16px; color: #0f172a; margin-bottom: 8px;">Dear <?php echo htmlspecialchars($letter->applicant_name); ?>,</p>
                <div style="color: #475569;"><?php echo $letter->introduction; ?></div>
            </div>

            <!-- Terms & Conditions -->
            <?php if (!empty($letter_terms)): ?>
            <div style="margin-bottom: 30px;">
                <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 16px;">Terms & Conditions</h3>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <?php foreach ($letter_terms as $lt): ?>
                        <div style="display: flex; gap: 12px; align-items: flex-start;">
                            <div style="display: flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: #f0fdf4; color: #16a34a; font-weight: 600; font-size: 12px; flex-shrink: 0; margin-top: 2px;">
                                <i class="fa fa-check" style="font-size:10px;"></i>
                            </div>
                            <div>
                                <h4 style="margin: 0 0 4px 0; font-size: 14px; font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($lt->title); ?></h4>
                                <p style="margin: 0; font-size: 13px; color: #6b7280;"><?php echo htmlspecialchars($lt->description); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Closing -->
            <div style="margin-bottom: 40px; color: #475569;">
                <?php echo $letter->closing_statement; ?>
            </div>

            <!-- Signatures Display & Form -->
            <form method="POST" id="signature-form">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                <input type="hidden" name="applicant_signature" id="applicant_signature_val">
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
                    <!-- HR Signature -->
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; background: #f8fafc;">
                        <h4 style="margin: 0 0 16px 0; font-size: 13px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">HR Representative</h4>
                        <div style="background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 6px; height: 120px; display: flex; align-items: center; justify-content: center;">
                            <?php if (!empty($letter->hr_signature)): ?>
                                <img src="<?php echo $letter->hr_signature; ?>" style="max-height: 100%; max-width: 100%;">
                            <?php else: ?>
                                <span style="color: #94a3b8; font-size: 13px;">Awaiting HR signature</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Applicant Signature -->
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; background: #f8fafc;">
                        <h4 style="margin: 0 0 16px 0; font-size: 13px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">Candidate Signature</h4>
                        
                        <?php if (!empty($letter->applicant_signature)): ?>
                            <div style="background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 6px; height: 120px; display: flex; align-items: center; justify-content: center;">
                                <img src="<?php echo $letter->applicant_signature; ?>" style="max-height: 100%; max-width: 100%;">
                            </div>
                        <?php else: ?>
                            <div style="position: relative; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; height: 120px; overflow: hidden;">
                                <canvas id="applicant-sig-canvas" style="width: 100%; height: 100%; touch-action: none; cursor: crosshair;"></canvas>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-top: 12px;">
                                <button type="button" onclick="clearApplicantSig()" style="background: none; border: none; color: #64748b; font-size: 13px; cursor: pointer; text-decoration: underline;">Clear</button>
                                <button type="submit" class="btn btn-success" style="background: #16a34a; border-color: #16a34a; font-weight: 600; padding: 6px 16px; border-radius: 6px; font-size: 13px;">Sign & Accept</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var canvas = document.getElementById('applicant-sig-canvas');
var ctx = canvas ? canvas.getContext('2d') : null;
var isDrawing = false;

function resizeCanvas(c) {
    var ratio = Math.max(window.devicePixelRatio || 1, 1);
    c.width = c.offsetWidth * ratio;
    c.height = c.offsetHeight * ratio;
    c.getContext("2d").scale(ratio, ratio);
}

if (canvas) {
    resizeCanvas(canvas);
    window.addEventListener('resize', function() { resizeCanvas(canvas); });

    function getPos(touchOrEvent) {
        var rect = canvas.getBoundingClientRect();
        var cx = touchOrEvent.touches ? touchOrEvent.touches[0].clientX : touchOrEvent.clientX;
        var cy = touchOrEvent.touches ? touchOrEvent.touches[0].clientY : touchOrEvent.clientY;
        return { x: cx - rect.left, y: cy - rect.top };
    }

    canvas.addEventListener('mousedown', function(e) {
        isDrawing = true;
        var p = getPos(e);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
    });

    canvas.addEventListener('mousemove', function(e) {
        if (!isDrawing) return;
        var p = getPos(e);
        ctx.lineTo(p.x, p.y);
        ctx.strokeStyle = '#1e293b';
        ctx.lineWidth = 2.5;
        ctx.stroke();
    });

    canvas.addEventListener('mouseup', function() {
        isDrawing = false;
        saveSig();
    });

    canvas.addEventListener('touchstart', function(e) {
        isDrawing = true;
        var p = getPos(e);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
        e.preventDefault();
    });

    canvas.addEventListener('touchmove', function(e) {
        if (!isDrawing) return;
        var p = getPos(e);
        ctx.lineTo(p.x, p.y);
        ctx.strokeStyle = '#1e293b';
        ctx.lineWidth = 2.5;
        ctx.stroke();
        e.preventDefault();
    });

    canvas.addEventListener('touchend', function(e) {
        isDrawing = false;
        saveSig();
        e.preventDefault();
    });
}

function saveSig() {
    document.getElementById('applicant_signature_val').value = canvas.toDataURL();
}

function clearApplicantSig() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    document.getElementById('applicant_signature_val').value = '';
}

document.getElementById('signature-form').addEventListener('submit', function(e) {
    var val = document.getElementById('applicant_signature_val').value;
    if (!val) {
        e.preventDefault();
        alert('Please draw your signature before accepting.');
    }
});
</script>
