/**
 * password-confirm.js
 * Reusable password confirmation modal for destructive actions
 * Usage: PasswordConfirm.require('Delete this project?', callback)
 */
(function (window) {
  'use strict';

  let attempts = 0;
  let lockUntil = 0;
  let pendingCallback = null;

  // Create modal DOM once
  function buildModal() {
    if (document.getElementById('pwd-confirm-overlay')) return;

    const html = `
    <div id="pwd-confirm-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:9999;align-items:center;justify-content:center;">
      <div id="pwd-confirm-box" style="background:#fff;border-radius:16px;padding:32px;width:90%;max-width:400px;box-shadow:0 8px 40px rgba(0,0,0,0.18);">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
          <div style="width:40px;height:40px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="#dc2626"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 4l1 1v4l-1 1-1-1V6l1-1zm0 8a1.5 1.5 0 110 3 1.5 1.5 0 010-3z"/></svg>
          </div>
          <div>
            <div style="font-family:Poppins,sans-serif;font-weight:600;font-size:16px;color:#1a1a2e;">Security Confirmation</div>
            <div id="pwd-confirm-action" style="font-size:13px;color:#6b7280;margin-top:2px;"></div>
          </div>
        </div>
        <p style="font-size:13px;color:#374151;margin:12px 0 6px;font-family:Inter,sans-serif;">Enter your password to continue:</p>
        <input id="pwd-confirm-input" type="text" placeholder="Your password"
          autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true"
          style="-webkit-text-security: disc; font-family: text-security-disc, sans-serif; width:100%;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:14px;outline:none;box-sizing:border-box;transition:border-color .2s;"
          onfocus="this.style.borderColor='#9c1f24'" onblur="this.style.borderColor='#e5e7eb'">
        <div id="pwd-confirm-error" style="color:#dc2626;font-size:12px;margin-top:6px;min-height:18px;font-family:Inter,sans-serif;"></div>
        <div id="pwd-confirm-lock" style="display:none;color:#f59e0b;font-size:13px;margin-top:6px;font-family:Inter,sans-serif;"></div>
        <div style="display:flex;gap:10px;margin-top:20px;">
          <button id="pwd-confirm-cancel" style="flex:1;padding:10px;border:1.5px solid #e5e7eb;border-radius:10px;background:#fff;color:#6b7280;font-size:14px;cursor:pointer;font-family:Inter,sans-serif;">Cancel</button>
          <button id="pwd-confirm-submit" data-save-btn style="flex:1;padding:10px;border:none;border-radius:10px;background:linear-gradient(135deg,#9c1f24,#6b1518);color:#fff;font-size:14px;font-weight:600;cursor:pointer;font-family:Poppins,sans-serif;">Confirm</button>
        </div>
      </div>
    </div>`;

    document.body.insertAdjacentHTML('beforeend', html);

    document.getElementById('pwd-confirm-cancel').onclick  = close;
    document.getElementById('pwd-confirm-submit').onclick  = submit;
    document.getElementById('pwd-confirm-input').addEventListener('keydown', function(e){
      if (e.key === 'Enter') { e.preventDefault(); submit(); }
    });
    document.getElementById('pwd-confirm-overlay').addEventListener('click', function(e){
      if (e.target === this) close();
    });
  }

  function show(actionLabel) {
    const overlay = document.getElementById('pwd-confirm-overlay');
    overlay.style.display = 'flex';
    document.getElementById('pwd-confirm-action').textContent = actionLabel || '';
    document.getElementById('pwd-confirm-input').value = '';
    document.getElementById('pwd-confirm-error').textContent = '';
    document.getElementById('pwd-confirm-lock').style.display = 'none';
    attempts = 0;
    setTimeout(() => document.getElementById('pwd-confirm-input').focus(), 80);
  }

  function close() {
    document.getElementById('pwd-confirm-overlay').style.display = 'none';
    pendingCallback = null;
  }

  function submit() {
    // Check lock
    if (Date.now() < lockUntil) {
      const rem = Math.ceil((lockUntil - Date.now()) / 1000);
      document.getElementById('pwd-confirm-lock').textContent = `Too many attempts. Wait ${rem}s.`;
      document.getElementById('pwd-confirm-lock').style.display = 'block';
      return;
    }

    const pwd = document.getElementById('pwd-confirm-input').value;
    if (!pwd) {
      document.getElementById('pwd-confirm-error').textContent = 'Please enter your password.';
      return;
    }

    const btn = document.getElementById('pwd-confirm-submit');
    btn.textContent = 'Verifying...';
    btn.disabled = true;

    fetch('api/index.php?action=verify_password', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'password=' + encodeURIComponent(pwd)
    })
    .then(r => r.json())
    .then(data => {
      btn.textContent = 'Confirm';
      btn.disabled = false;

      if (data.success) {
        attempts = 0;
        const cb = pendingCallback;
        close();
        if (cb) cb();
      } else {
        attempts++;
        if (attempts >= 3) {
          lockUntil = Date.now() + 30000;
          document.getElementById('pwd-confirm-lock').textContent = 'Too many attempts. Locked for 30 seconds.';
          document.getElementById('pwd-confirm-lock').style.display = 'block';
          document.getElementById('pwd-confirm-error').textContent = '';
        } else {
          document.getElementById('pwd-confirm-error').textContent = 'Incorrect password. Attempts: ' + attempts + '/3';
        }
        document.getElementById('pwd-confirm-input').value = '';
        document.getElementById('pwd-confirm-input').focus();
      }
    })
    .catch(() => {
      btn.textContent = 'Confirm';
      btn.disabled = false;
      document.getElementById('pwd-confirm-error').textContent = 'Connection error. Try again.';
    });
  }

  /**
   * Public API
   * PasswordConfirm.require('Action description', function() { /* proceed *\/ });
   */
  function require(actionLabel, callback) {
    buildModal();
    pendingCallback = callback;
    show(actionLabel);
  }

  window.PasswordConfirm = { require };

}(window));
