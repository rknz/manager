<?php
// views/forgot-password.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password &mdash; Lily Interiors Profix</title>
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
      width: 500px;
      height: 500px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(156, 31, 36, 0.18) 0%, rgba(156, 31, 36, 0) 70%);
      top: -120px;
      left: -120px;
      pointer-events: none;
      filter: blur(40px);
    }

    .ambient-glow-2 {
      position: fixed;
      width: 450px;
      height: 450px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(156, 31, 36, 0.14) 0%, rgba(156, 31, 36, 0) 70%);
      bottom: -100px;
      right: -100px;
      pointer-events: none;
      filter: blur(40px);
    }

    .fp-wrap {
      width: 100%;
      max-width: 400px;
      position: relative;
      z-index: 10;
    }

    .fp-card {
      background: #FFFFFF;
      border-radius: 12px;
      box-shadow: 
        0 24px 48px -12px rgba(0, 0, 0, 0.45),
        0 0 0 1px rgba(255, 255, 255, 0.1),
        0 4px 12px rgba(0, 0, 0, 0.15);
      padding: 36px 32px 32px;
      text-align: center;
      position: relative;
      overflow: hidden;
      animation: cardEntrance 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    .fp-card::before {
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

    .fp-logo {
      width: 76px;
      height: 76px;
      margin: 0 auto 16px;
      border-radius: 12px;
      background: #FFFFFF;
      border: 1px solid #E2E8F0;
      box-shadow: 0 8px 20px rgba(156, 31, 36, 0.14);
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      padding: 4px;
    }

    .fp-logo img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      border-radius: 8px;
    }

    .fp-card h2 {
      font-family: 'Potta One', cursive, sans-serif !important;
      font-size: 22px;
      font-weight: 400;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #1E293B;
      margin: 0 0 10px;
    }

    .fp-card p {
      font-size: 13.5px;
      color: #64748B;
      margin: 0 0 24px;
      line-height: 1.6;
    }

    .fp-back {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
      padding: 12px 20px;
      background: linear-gradient(135deg, #9C1F24 0%, #7A1A1E 100%);
      color: #FFFFFF;
      border-radius: 8px;
      font-size: 14.5px;
      font-weight: 600;
      font-family: 'Poppins', 'Inter', sans-serif;
      text-decoration: none;
      box-shadow: 0 4px 14px rgba(156, 31, 36, 0.3);
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .fp-back:hover {
      background: linear-gradient(135deg, #A82228 0%, #851C21 100%);
      transform: translateY(-1px);
      box-shadow: 0 8px 20px rgba(156, 31, 36, 0.4);
    }

    .fp-back svg {
      width: 16px;
      height: 16px;
      transition: transform 0.2s ease;
    }

    .fp-back:hover svg {
      transform: translateX(-3px);
    }
    </style>
</head>
<body>
<div class="ambient-glow-1"></div>
<div class="ambient-glow-2"></div>

<div class="fp-wrap">
    <div class="fp-card">
        <div class="fp-logo">
          <img src="<?= $basePath ?>/assets/img/<?= rawurlencode('Lily Interiors logo.png') ?>" alt="Lily Interiors logo">
        </div>
        <h2>Password Reset</h2>
        <p>Please contact the System Administrator or Owner to reset your account password.</p>
        <a href="<?= $basePath ?>/login" class="fp-back">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
          <span>Back to Sign In</span>
        </a>
    </div>
</div>
</body>
</html>