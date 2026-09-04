<?php
// views/login.php
require_once __DIR__ . '/../includes/auth.php';
if (isLoggedIn()) { header('Location: ' . $basePath . '/dashboard'); exit; }

$error = '';
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
       || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT id,username,password_hash,role FROM app_users WHERE username=? AND is_active=1");
    $stmt->execute([$u]);
    $user = $stmt->fetch();
    if ($user && password_verify($p, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'redirect' => $basePath . '/dashboard']);
            exit;
        }
        header('Location: ' . $basePath . '/dashboard');
        exit;
    } else {
        $error = 'Invalid username or password.';
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $error]);
            exit;
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login &mdash; Lily Interiors Profix</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Potta+One&family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&family=Noto+Sans+Bengali:wght@400;600;700&family=Hind+Siliguri:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $basePath ?>/assets/css/style.css">
<style>
:root {
  --brand-crimson: #9C1F24;
  --brand-crimson-dark: #7A1A1E;
  --brand-crimson-glow: rgba(156, 31, 36, 0.18);
  --brand-crimson-soft: rgba(156, 31, 36, 0.08);
  --bg-main: #0B0F19;
  --text-dark: #0F172A;
  --text-muted: #64748B;
  --border-color: #E2E8F0;
}

*, *::before, *::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

html, body {
  min-height: 100vh;
  font-family: 'Inter', 'Noto Sans Bengali', 'Hind Siliguri', sans-serif;
  background-color: var(--bg-main);
  background-image: 
    radial-gradient(at 0% 0%, rgba(156, 31, 36, 0.22) 0px, transparent 50%),
    radial-gradient(at 100% 100%, rgba(156, 31, 36, 0.16) 0px, transparent 50%),
    radial-gradient(at 50% 50%, rgba(30, 41, 59, 0.35) 0px, transparent 100%),
    linear-gradient(135deg, #090D16 0%, #0F172A 50%, #0D111D 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px 16px;
  position: relative;
  overflow-x: hidden;
}

/* Subtle modern mesh & grid backdrop */
body::before {
  content: '';
  position: fixed;
  inset: 0;
  pointer-events: none;
  background-image: 
    linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
  background-size: 36px 36px;
  mask-image: radial-gradient(circle at center, black 40%, transparent 85%);
  -webkit-mask-image: radial-gradient(circle at center, black 40%, transparent 85%);
}

.ambient-glow-1 {
  position: fixed;
  width: 520px;
  height: 520px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(156, 31, 36, 0.18) 0%, rgba(156, 31, 36, 0) 70%);
  top: -120px;
  left: -120px;
  pointer-events: none;
  filter: blur(40px);
}

.ambient-glow-2 {
  position: fixed;
  width: 480px;
  height: 480px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(156, 31, 36, 0.14) 0%, rgba(156, 31, 36, 0) 70%);
  bottom: -100px;
  right: -100px;
  pointer-events: none;
  filter: blur(40px);
}

.login-wrap {
  width: 100%;
  max-width: 420px;
  position: relative;
  z-index: 10;
}

.login-card {
  background: #FFFFFF;
  border-radius: 12px;
  box-shadow: 
    0 24px 48px -12px rgba(0, 0, 0, 0.45),
    0 0 0 1px rgba(255, 255, 255, 0.1),
    0 4px 12px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  position: relative;
  animation: cardEntrance 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
}

/* Crimson accent highlight line on card top */
.login-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #9C1F24 0%, #D32F2F 50%, #9C1F24 100%);
}

@keyframes cardEntrance {
  from {
    opacity: 0;
    transform: translateY(22px) scale(0.98);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.login-brand {
  padding: 36px 32px 20px;
  text-align: center;
}

.login-logo {
  width: 84px;
  height: 84px;
  margin: 0 auto 16px;
  border-radius: 12px;
  background: #FFFFFF;
  border: 1px solid #E2E8F0;
  box-shadow: 0 8px 20px rgba(156, 31, 36, 0.12);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  padding: 6px;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.login-logo:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 24px rgba(156, 31, 36, 0.18);
}

.login-logo img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

/* H1 heading in Potta One font with uppercase text */
.login-brand h1 {
  font-family: 'Potta One', cursive, sans-serif !important;
  font-size: 24px;
  font-weight: 400;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #1E293B;
  margin: 0 0 6px;
  line-height: 1.25;
}

.login-brand .brand-badge {
  display: inline-block;
  font-family: 'Inter', sans-serif;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--brand-crimson);
  background: var(--brand-crimson-soft);
  padding: 4px 12px;
  border-radius: 20px;
  border: 1px solid rgba(156, 31, 36, 0.15);
}

.login-divider {
  height: 1px;
  background: linear-gradient(90deg, transparent, #E2E8F0 25%, #E2E8F0 75%, transparent);
  margin: 0 32px;
}

.login-body {
  padding: 24px 32px 32px;
}

.login-error {
  background: #FEF2F2;
  color: #991B1B;
  border: 1px solid #FECACA;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 13px;
  font-weight: 500;
  margin-bottom: 18px;
  display: flex;
  align-items: center;
  gap: 10px;
  animation: shakeAlert 0.3s ease;
}

@keyframes shakeAlert {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}

.login-error svg {
  flex-shrink: 0;
}

.login-field {
  margin-bottom: 18px;
}

.login-label-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 6px;
}

.login-label {
  display: block;
  font-size: 12.5px;
  font-weight: 600;
  color: #334155;
  letter-spacing: 0.01em;
}

.forgot-link {
  font-size: 12px;
  font-weight: 500;
  color: var(--brand-crimson);
  text-decoration: none;
  transition: color 0.15s ease, text-decoration 0.15s ease;
}

.forgot-link:hover {
  color: var(--brand-crimson-dark);
  text-decoration: underline;
}

/* Modern input wrap with correctly positioned & centered icons */
.login-input-wrap {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
}

.login-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  color: #94A3B8;
  pointer-events: none;
  transition: color 0.2s ease;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
}

.login-input {
  width: 100%;
  padding: 12px 42px 12px 42px;
  border: 1.5px solid var(--border-color);
  border-radius: 8px;
  font-size: 14px;
  font-family: 'Inter', 'Noto Sans Bengali', 'Hind Siliguri', sans-serif;
  color: var(--text-dark);
  background: #F8FAFC;
  outline: none;
  transition: all 0.2s ease;
  box-sizing: border-box;
}

.login-input::placeholder {
  color: #94A3B8;
  font-size: 13.5px;
}

.login-input:hover {
  border-color: #CBD5E1;
  background: #FFFFFF;
}

.login-input:focus {
  border-color: var(--brand-crimson);
  background: #FFFFFF;
  box-shadow: 0 0 0 3.5px var(--brand-crimson-glow);
}

.login-input-wrap:focus-within .login-icon {
  color: var(--brand-crimson);
}

/* Password Toggle Visibility Button */
.toggle-password {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  padding: 6px;
  cursor: pointer;
  color: #94A3B8;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: color 0.15s ease, background 0.15s ease;
  z-index: 3;
}

.toggle-password:hover {
  color: #334155;
  background: #F1F5F9;
}

.toggle-password svg {
  width: 18px;
  height: 18px;
}

/* Submit Button */
.login-btn {
  width: 100%;
  padding: 12px 20px;
  background: linear-gradient(135deg, #9C1F24 0%, #7A1A1E 100%);
  color: #FFFFFF;
  border: none;
  border-radius: 8px;
  font-size: 14.5px;
  font-weight: 600;
  font-family: 'Poppins', 'Inter', sans-serif;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  margin-top: 8px;
  box-shadow: 0 4px 14px rgba(156, 31, 36, 0.3);
}

.login-btn:hover {
  background: linear-gradient(135deg, #A82228 0%, #851C21 100%);
  transform: translateY(-1px);
  box-shadow: 0 8px 20px rgba(156, 31, 36, 0.4);
}

.login-btn:active {
  transform: translateY(0);
  box-shadow: 0 3px 10px rgba(156, 31, 36, 0.25);
}

.login-btn:disabled {
  opacity: 0.75;
  cursor: not-allowed;
  transform: none;
}

.login-btn svg {
  width: 16px;
  height: 16px;
  transition: transform 0.2s ease;
}

.login-btn:hover svg {
  transform: translateX(3px);
}

/* Footer note */
.login-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  text-align: center;
  font-size: 12px;
  color: #94A3B8;
  margin-top: 22px;
  padding-top: 16px;
  border-top: 1px dashed #E2E8F0;
}

.login-footer svg {
  width: 13px;
  height: 13px;
  color: #64748B;
}

@media (max-width: 480px) {
  .login-card {
    border-radius: 10px;
  }
  .login-brand {
    padding: 28px 20px 16px;
  }
  .login-brand h1 {
    font-size: 21px;
  }
  .login-body {
    padding: 20px 20px 24px;
  }
}
</style>
</head>
<body>
<div class="ambient-glow-1"></div>
<div class="ambient-glow-2"></div>

<div class="login-wrap">
  <div class="login-card">
    <div class="login-brand">
      <div class="login-logo">
        <img src="<?= $basePath ?>/assets/img/<?= rawurlencode('Lily Interiors logo.png') ?>" alt="Lily Interiors logo">
      </div>
      <h1>LILY INTERIORS</h1>
      <div class="brand-badge">PROFIX &middot; PROJECT MANAGEMENT SYSTEM</div>
    </div>
    
    <div class="login-divider"></div>
    
    <div class="login-body">
      <div id="errorContainer">
        <?php if ($error): ?>
        <div class="login-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['expired'])): ?>
        <div class="login-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          <span>Session expired. Please login again.</span>
        </div>
        <?php endif; ?>
      </div>

      <form method="POST" action="<?= $basePath ?>/login" id="loginForm" autocomplete="off">
        <div class="login-field">
          <div class="login-label-row">
            <label class="login-label" for="username">Username</label>
          </div>
          <div class="login-input-wrap">
            <span class="login-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            <input class="login-input" type="text" name="username" id="username" placeholder="Enter username" autofocus autocomplete="off" required>
          </div>
        </div>

        <div class="login-field">
          <div class="login-label-row">
            <label class="login-label" for="password">Password</label>
            <a href="<?= $basePath ?>/forgot-password" class="forgot-link">Forgot password?</a>
          </div>
          <div class="login-input-wrap">
            <span class="login-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input class="login-input" type="password" name="password" id="password" placeholder="Enter password" autocomplete="new-password" required>
            <button type="button" class="toggle-password" id="togglePasswordBtn" aria-label="Toggle password visibility" title="Show/Hide Password" tabindex="-1">
              <svg id="eyeOpenIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg id="eyeCloseIcon" style="display:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>
        </div>

        <button type="submit" class="login-btn" id="submitBtn">
          <span>Sign In</span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </button>
      </form>

      <div class="login-footer">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <span>Secured Enterprise Access &bull; Lily Interiors</span>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('loginForm');
  const btn = document.getElementById('submitBtn');
  const errorContainer = document.getElementById('errorContainer');
  const toggleBtn = document.getElementById('togglePasswordBtn');
  const passwordInput = document.getElementById('password');
  const usernameInput = document.getElementById('username');
  const eyeOpen = document.getElementById('eyeOpenIcon');
  const eyeClose = document.getElementById('eyeCloseIcon');

  // Password visibility toggle
  if (toggleBtn && passwordInput) {
    toggleBtn.addEventListener('click', function(e) {
      e.preventDefault();
      const isPassword = passwordInput.type === 'password';
      passwordInput.type = isPassword ? 'text' : 'password';
      if (eyeOpen && eyeClose) {
        eyeOpen.style.display = isPassword ? 'none' : 'block';
        eyeClose.style.display = isPassword ? 'block' : 'none';
      }
      passwordInput.focus();
    });
  }

  // Smooth AJAX submission to suppress Chrome's native breached password popup
  if (form) {
    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      const u = usernameInput ? usernameInput.value.trim() : '';
      const p = passwordInput ? passwordInput.value : '';

      if (!u || !p) return;

      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span>Signing in...</span>';
      }

      try {
        const response = await fetch('<?= $basePath ?>/login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          body: new URLSearchParams({ username: u, password: p })
        });

        const data = await response.json();
        if (data && data.success) {
          // Instantly wipe password value from DOM before redirecting to avoid Chrome interception
          if (passwordInput) passwordInput.value = '';
          window.location.href = data.redirect;
        } else {
          if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<span>Sign In</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>`;
          }
          showError((data && data.error) ? data.error : 'Invalid username or password.');
        }
      } catch (err) {
        // Fallback to standard submit
        form.submit();
      }
    });
  }

  function showError(msg) {
    if (!errorContainer) return;
    errorContainer.innerHTML = `
      <div class="login-error" role="alert">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        <span>${msg}</span>
      </div>
    `;
  }
});
</script>
</body>
</html>