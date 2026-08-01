<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password — Go Shipping Rider</title>
    <style>
        :root {
            --navy: #0a2a52; --navy-deep: #071d3b; --bg: #f4f6f9; --surface: #ffffff;
            --border: #e2e7ee; --ink: #1a2433; --ink-soft: #5b6b82; --ink-faint: #93a1b5;
        }
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg); color: var(--ink); margin: 0; min-height: 100vh;
            display: flex; align-items: center; justify-content: center; padding: 20px;
        }
        .card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 14px;
            padding: 28px 24px; width: 100%; max-width: 380px; text-align: center;
        }
        .brand-badge {
            width: 52px; height: 52px; border-radius: 14px; margin: 0 auto 16px;
            background: var(--navy); display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 800; color: #fff;
        }
        h1 { font-size: 18px; margin: 0 0 8px; }
        p.sub { color: var(--ink-soft); font-size: 13px; margin: 0 0 20px; line-height: 1.5; }
        label { display: block; font-size: 12.5px; font-weight: 600; color: var(--ink-soft); margin: 14px 0 6px; text-align: left; }
        input[type="password"] {
            width: 100%; padding: 12px 14px; border-radius: 10px; border: 1.5px solid var(--border);
            font-size: 15px; font-family: inherit; background: var(--surface); color: var(--ink);
        }
        input:focus { outline: none; border-color: var(--navy); }
        button {
            width: 100%; margin-top: 16px; padding: 13px 20px; border-radius: 10px; border: none;
            background: var(--navy); color: #fff; font-size: 14.5px; font-weight: 700; cursor: pointer;
        }
        button:disabled { opacity: .6; }
        .msg { display: none; margin-top: 14px; padding: 11px 14px; border-radius: 8px; font-size: 13px; line-height: 1.5; text-align: left; }
        .msg.error { background: #fdecec; color: #9b2d26; }
        .msg.success { background: #eef6ee; color: #2c6e3f; }
        a { color: var(--navy); font-weight: 600; }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand-badge">GS</div>
        <h1 id="title">Reset Your Password</h1>
        <p class="sub" id="subtitle">Choose a new password for your Go Shipping Rider account.</p>

        <form id="reset_form">
            <label for="new_password">New password</label>
            <input type="password" id="new_password" placeholder="At least 4 characters" required>
            <label for="confirm_password">Confirm password</label>
            <input type="password" id="confirm_password" placeholder="Re-enter new password" required>
            <button type="submit" id="submit_btn">Reset Password</button>
        </form>

        <div class="msg error" id="error_box"></div>
        <div class="msg success" id="success_box"></div>
    </div>

<script>
    var TOKEN = <?php echo json_encode($token); ?>;
    var RESET_URL = <?php echo json_encode(site_url('courier_goshipping/rider-api/reset_password')); ?>;

    var form = document.getElementById('reset_form');
    var errBox = document.getElementById('error_box');
    var okBox = document.getElementById('success_box');

    if (!TOKEN) {
        document.getElementById('subtitle').textContent = 'This reset link is invalid.';
        form.style.display = 'none';
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        errBox.style.display = 'none';
        okBox.style.display = 'none';

        var newPassword = document.getElementById('new_password').value;
        var confirmPassword = document.getElementById('confirm_password').value;
        if (newPassword.length < 4) {
            errBox.textContent = 'Password must be at least 4 characters.';
            errBox.style.display = 'block';
            return;
        }
        if (newPassword !== confirmPassword) {
            errBox.textContent = 'Passwords do not match.';
            errBox.style.display = 'block';
            return;
        }

        var btn = document.getElementById('submit_btn');
        btn.disabled = true; btn.textContent = 'Resetting...';

        fetch(RESET_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ token: TOKEN, new_password: newPassword }).toString()
        }).then(function (r) { return r.json(); }).then(function (data) {
            btn.disabled = false; btn.textContent = 'Reset Password';
            if (!data.success) {
                errBox.textContent = data.message || 'Could not reset your password.';
                errBox.style.display = 'block';
                return;
            }
            form.style.display = 'none';
            document.getElementById('title').textContent = 'Password Updated';
            document.getElementById('subtitle').textContent = 'You can now log in to the Rider app with your new password.';
            okBox.innerHTML = '<a href="<?php echo site_url('courier_goshipping/rider'); ?>">Go to Login</a>';
            okBox.style.display = 'block';
        }).catch(function () {
            btn.disabled = false; btn.textContent = 'Reset Password';
            errBox.textContent = 'Network error — please check your connection and try again.';
            errBox.style.display = 'block';
        });
    });
</script>
</body>
</html>
