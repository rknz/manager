<?php
// includes/header.php — Shared layout header
// Required vars: $pageTitle, $basePath, $activeNav
$username    = $_SESSION['username'] ?? 'Admin';
$role        = $_SESSION['role']    ?? 'user';
$userInitial = strtoupper(substr($username, 0, 1));
$today       = date('D, d M Y');
$hasRightPanel = $hasRightPanel ?? false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Lily Interiors') ?> — Profix</title>
  <meta name="description" content="Profix — Interior Project Management by Lily Interiors">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600&family=Noto+Sans+Bengali:wght@100..900&family=Hind+Siliguri:wght@300;400;500;600;700&family=Potta+One&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $basePath ?>/assets/css/style.css?v=2.7.5">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" referrerpolicy="no-referrer">
<script>const BASE_PATH = '<?= $basePath ?>';</script>
</head>
<body>
<div class="app-layout">

<!-- SIDEBAR OVERLAY (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<nav class="sidebar" id="mainSidebar">
  <!-- Sidebar Header -->
  <div class="sidebar-header hide-on-mobile">
    <!-- Desktop Logo -->
    <div class="sidebar-logo" style="display:flex; justify-content:center; padding:16px; border-bottom:1px solid var(--border-light);">
      <img src="<?= $basePath ?>/assets/img/logo-wide.png" alt="Lily Interiors" style="height:48px; object-fit:contain;">
    </div>
  </div>

  <!-- Nav -->
  <div class="sidebar-nav">
    <div class="sidebar-section-label">Main</div>

    <a href="<?= $basePath ?>/dashboard" class="sidebar-nav-item <?= ($activeNav==='dashboard') ? 'active':'' ?>">
      <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>
      Dashboard
    </a>
    <a href="<?= $basePath ?>/projects" class="sidebar-nav-item <?= ($activeNav==='projects') ? 'active':'' ?>">
      <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg></span>
      Projects
    </a>

    <div class="sidebar-section-label">Quick Entry</div>

    <a href="<?= $basePath ?>/quick-purchase" class="sidebar-nav-item <?= ($activeNav==='quick-purchase') ? 'active':'' ?>">
      <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.97-1.67L23 6H6"/></svg></span>
      Quick Purchase
    </a>
    <a href="<?= $basePath ?>/daily-labor" class="sidebar-nav-item <?= ($activeNav==='daily-labor') ? 'active':'' ?>">
      <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></span>
      Daily Labor
    </a>
    <a href="<?= $basePath ?>/payments" class="sidebar-nav-item <?= ($activeNav==='payments') ? 'active':'' ?>">
      <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></span>
      Payments
    </a>

    <div class="sidebar-section-label">Manage</div>

    <a href="<?= $basePath ?>/contractors" class="sidebar-nav-item <?= ($activeNav==='contractors') ? 'active':'' ?>">
      <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg></span>
      Contractors
    </a>
    <a href="<?= $basePath ?>/workers" class="sidebar-nav-item <?= ($activeNav==='workers') ? 'active':'' ?>">
      <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></span>
      Workers
    </a>
    <a href="<?= $basePath ?>/reports" class="sidebar-nav-item <?= ($activeNav==='reports') ? 'active':'' ?>">
      <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></span>
      Reports
    </a>
    <a href="<?= $basePath ?>/print-bill" class="sidebar-nav-item <?= ($activeNav==='print-bill') ? 'active':'' ?>">
      <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg></span>
      Print Bill
    </a>


    <div class="sidebar-section-label">System</div>

    <a href="<?= $basePath ?>/settings" class="sidebar-nav-item <?= ($activeNav==='settings') ? 'active':'' ?>">
      <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg></span>
      Settings
    </a>
    <a href="<?= $basePath ?>/backup" class="sidebar-nav-item <?= ($activeNav==='backup') ? 'active':'' ?>">
      <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></span>
      Backup
    </a>
  </div>

  <!-- Sidebar Bottom Bar with Close Button -->
  <div class="sidebar-bottom-bar hide-on-desktop" style="display:flex; justify-content:flex-end; align-items:center; padding:10px 16px; border-top:1px solid var(--border-light); background:transparent;">
    <button type="button" onclick="closeSidebar()" class="sidebar-close-btn" title="Close Sidebar" style="width:36px; height:36px; border-radius:50%; background:var(--card-bg); border:1.5px solid var(--border); display:flex; align-items:center; justify-content:center; color:var(--text-secondary); cursor:pointer; box-shadow:0 2px 6px rgba(0,0,0,0.08); transition:all 0.2s;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>

  <!-- Backup Banner -->
  <div class="sidebar-backup-banner hide-on-mobile">
    <h4>&#128274; Backup Data</h4>
    <p>Regularly backup to prevent data loss</p>
    <a href="<?= $basePath ?>/backup" class="btn-backup">Download Backup</a>
  </div>
</nav>

<!-- MAIN WRAPPER -->
<div class="main-wrapper">

<!-- TOPBAR -->
<header class="topbar">
  <!-- Mobile menu toggle -->
  <button onclick="toggleMobileMenu()" class="mobile-menu-btn hide-on-desktop" id="mobileMenuBtn">
    <svg id="menuIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
  </button>

  <!-- Mobile Logo -->
  <div class="topbar-mobile-logo hide-on-desktop" style="display:flex; justify-content:center;">
    <img src="<?= $basePath ?>/assets/img/logo-wide.png" alt="Lily Interiors" style="height:32px; object-fit:contain;">
  </div>

  <!-- Greeting (Desktop) -->
  <div class="topbar-greeting hide-on-mobile">
    <h2 id="topbarTitle"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h2>
    <p id="topbarDate">Good evening, <?= htmlspecialchars($username) ?></p>
  </div>

  <!-- Search -->
  <div class="topbar-search hide-on-mobile" id="globalSearchWrap">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input type="text" class="topbar-search-input" id="globalSearchInput" placeholder="Search projects, purchases..." autocomplete="off">
    <span class="search-kbd">Ctrl+K</span>
    <div class="search-dropdown" id="searchDropdown" style="display:none;"></div>
  </div>

  <!-- Actions -->
  <div class="topbar-actions">
    <!-- Quick add -->
    <button class="topbar-action-btn hide-on-mobile" id="quickAddBtn" title="Quick Add">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    </button>
    
    <!-- Date (Desktop) -->
    <div class="topbar-action-btn date-btn hide-on-mobile" id="topbarDateBadge">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      <span><?= date('d M Y') ?></span>
    </div>

    <!-- Notifications -->
    <button class="topbar-action-btn" id="notifBtn" title="Notifications">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
      <span class="badge-dot" id="notifBadge" style="display:none;"></span>
    </button>

    <!-- User -->
    <div class="topbar-user" id="userMenuBtn">
      <div class="topbar-user-info hide-on-mobile">
        <div class="user-name"><?= htmlspecialchars($username) ?></div>
        <div class="user-role"><?= ucfirst($role) ?></div>
      </div>
      <div class="topbar-user-avatar">
        <?= $userInitial ?>
        <label for="avatar-upload" class="avatar-upload-overlay" title="Upload Picture">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
        </label>
        <input type="file" id="avatar-upload" style="display:none;" accept="image/*">
      </div>
    </div>
  </div>
</header>

<!-- Mobile Greeting Bar (Sub-pages only) -->
<?php if (($activeNav ?? '') !== 'dashboard'): ?>
<div class="mobile-greeting-bar hide-on-desktop">
  <div>
    <h2 class="mobile-greeting-title"><?= htmlspecialchars($pageTitle ?? '') ?></h2>
    <p class="mobile-greeting-sub"><?= htmlspecialchars($username) ?></p>
  </div>
  <div class="mobile-date-badge">
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    <span><?= date('d M Y') ?></span>
  </div>
</div>
<?php endif; ?>

<!-- PAGE CONTENT START -->
<main class="main-content has-right-panel" id="mainContent">


