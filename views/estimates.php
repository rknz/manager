<?php
// views/estimates.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$pageTitle = 'Estimates & Quotations';
$activeNav = 'estimates';
include __DIR__ . '/../includes/header.php';
?>

<style>
/* Estimates Page Styles - Matching Executive Clean Reference */
.est-page-wrap {
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

/* 1. Top Header Bar */
.est-top-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 20px;
  width: 100%;
  box-sizing: border-box;
}

.est-catalog-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 700;
  color: #0F172A;
  text-decoration: none;
  box-shadow: 0 1px 2px rgba(0,0,0,0.02);
  transition: all 0.15s ease;
  white-space: nowrap;
}
.est-catalog-btn:hover {
  background: #F8FAFC;
  border-color: #CBD5E1;
  color: #B5182E;
}

.est-filter-group {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
  max-width: 820px;
  justify-content: center;
  flex-wrap: wrap;
}

.est-search-box {
  position: relative;
  min-width: 260px;
  max-width: 320px;
  flex: 1;
}
.est-search-box svg {
  position: absolute;
  left: 11px;
  top: 50%;
  transform: translateY(-50%);
  color: #94A3B8;
  width: 15px;
  height: 15px;
  pointer-events: none;
}
.est-search-box input {
  width: 100%;
  padding: 7px 12px 7px 34px;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  font-size: 12.5px;
  color: #0F172A;
  background: #ffffff;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
  box-sizing: border-box;
}
.est-search-box input:focus {
  border-color: #B5182E;
  box-shadow: 0 0 0 2px rgba(181,24,46,0.1);
}

.est-status-tabs {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: transparent;
  flex-wrap: wrap;
}
.est-tab-btn {
  background: transparent;
  border: none;
  outline: none;
  padding: 6px 14px;
  font-size: 12.5px;
  font-weight: 500;
  color: #475569;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
}
.est-tab-btn:hover {
  background: #F1F5F9;
  color: #0F172A;
}
.est-tab-btn.active {
  background: #B5182E;
  color: #ffffff;
  font-weight: 700;
}

.est-create-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #B5182E;
  color: #ffffff;
  font-size: 13px;
  font-weight: 700;
  padding: 8px 18px;
  border-radius: 8px;
  text-decoration: none;
  box-shadow: 0 2px 6px rgba(181,24,46,0.25);
  transition: all 0.15s ease;
  white-space: nowrap;
  border: none;
}
.est-create-btn:hover {
  background: #9C1F24;
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(181,24,46,0.3);
}

/* 2. Stat Cards Grid */
.est-stats-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 14px;
  margin-bottom: 20px;
  width: 100%;
  box-sizing: border-box;
}
@media (max-width: 1200px) {
  .est-stats-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 768px) {
  .est-stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
  .est-stats-grid { grid-template-columns: 1fr; }
}

.est-stat-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  border-radius: 12px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.02);
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.est-stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.est-stat-icon {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.est-stat-info {
  flex: 1;
  min-width: 0;
}
.est-stat-title {
  font-size: 11.5px;
  font-weight: 600;
  color: #64748B;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.est-stat-value {
  font-size: 22px;
  font-weight: 800;
  color: #0F172A;
  line-height: 1.2;
  margin: 2px 0;
}
.est-stat-desc {
  font-size: 11px;
  color: #94A3B8;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* 3. Table Card */
.est-table-card {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.03);
  overflow: hidden;
  width: 100%;
  box-sizing: border-box;
}

.est-table-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #F1F5F9;
  flex-wrap: wrap;
  gap: 12px;
}

.est-table-title-wrap {
  display: flex;
  align-items: center;
  gap: 12px;
}
.est-table-title-icon {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  background: #EDE9FE;
  color: #7C3AED;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.est-table-title-wrap h2 {
  font-size: 16px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
}
.est-table-title-wrap p {
  font-size: 12px;
  color: #64748B;
  margin: 2px 0 0 0;
}

.est-table-tools {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.est-mini-search {
  position: relative;
  width: 180px;
}
.est-mini-search svg {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #94A3B8;
  width: 14px;
  height: 14px;
}
.est-mini-search input {
  width: 100%;
  padding: 6px 10px 6px 30px;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  font-size: 12px;
  color: #0F172A;
  background: #ffffff;
  outline: none;
  box-sizing: border-box;
}
.est-mini-search input:focus {
  border-color: #B5182E;
}

.est-select-filter {
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  padding: 6px 12px;
  font-size: 12px;
  color: #334155;
  background: #ffffff;
  outline: none;
  cursor: pointer;
  font-weight: 500;
}
.est-select-filter:focus {
  border-color: #B5182E;
}

/* Data Table */
.est-data-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}
.est-data-table th {
  background: #ffffff;
  padding: 12px 18px;
  font-size: 11px;
  font-weight: 700;
  color: #64748B;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1px solid #E2E8F0;
}
.est-data-table td {
  padding: 14px 18px;
  border-bottom: 1px solid #F1F5F9;
  vertical-align: middle;
  font-size: 13px;
  transition: background 0.1s;
}
.est-data-table tr:hover td {
  background: #F8FAFC;
}

.est-icon-badge {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

/* Action Dropdown Menu */
.action-menu-wrap {
  position: relative;
  display: inline-block;
}
.btn-dots {
  background: none;
  border: none;
  font-size: 16px;
  font-weight: bold;
  color: #64748B;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 6px;
  transition: all 0.15s;
}
.btn-dots:hover {
  background: #F1F5F9;
  color: #0F172A;
}

.action-dropdown {
  position: absolute;
  right: 0;
  top: 100%;
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  min-width: 160px;
  z-index: 1000;
  display: none;
  padding: 6px 0;
  margin-top: 4px;
}
.action-dropdown.show {
  display: block;
}
.action-dropdown button {
  width: 100%;
  text-align: left;
  background: none;
  border: none;
  padding: 8px 14px;
  font-size: 12.5px;
  color: #334155;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
}
.action-dropdown button:hover {
  background: #F8FAFC;
  color: #0F172A;
}

/* Mobile Optimizations - Matching Reference UI Design */
.est-desktop-table {
  display: block;
}
.est-mobile-cards-feed {
  display: none;
}
.est-mobile-header-banner {
  display: none;
}
.est-mobile-catalog-bar-link {
  display: none;
}
.est-mobile-filter-strip {
  display: none;
}
.est-mobile-search-row {
  display: none;
}
.est-mobile-fab {
  display: none;
}

@media (max-width: 768px) {
  .est-top-bar {
    display: none !important;
  }
  .est-mobile-catalog-bar-link {
    display: flex !important;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: #ffffff;
    border: 1.5px solid #E2E8F0;
    border-radius: 14px;
    padding: 10px 14px;
    margin-bottom: 12px;
    text-decoration: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    transition: all 0.15s ease;
    box-sizing: border-box;
  }
  .est-mobile-catalog-bar-link:active {
    background: #F8FAFC;
    border-color: #CBD5E1;
  }
  .est-mob-cat-left {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
  }
  .est-mob-cat-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .est-mob-cat-title {
    font-size: 13px;
    font-weight: 800;
    color: #0F172A;
    line-height: 1.2;
  }
  .est-mob-cat-sub {
    font-size: 11px;
    color: #64748B;
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .est-mob-cat-action {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11.5px;
    font-weight: 700;
    color: #B5182E;
    flex-shrink: 0;
    background: #FFF1F2;
    padding: 6px 10px;
    border-radius: 8px;
    border: 1px solid #FECDD3;
  }
  .est-mobile-header-banner {
    display: flex !important;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
    border: 1px solid #E2E8F0;
    border-radius: 16px;
    padding: 14px 16px;
    margin-bottom: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    gap: 12px;
  }
  .est-mobile-banner-left {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .est-mobile-banner-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: #F3E8FF;
    color: #7C3AED;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .est-mobile-banner-title {
    font-size: 15px;
    font-weight: 800;
    color: #0F172A;
    line-height: 1.2;
  }
  .est-mobile-banner-sub {
    font-size: 11.5px;
    color: #64748B;
    margin-top: 2px;
    line-height: 1.3;
  }
  .est-mobile-banner-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #B5182E;
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    padding: 8px 12px;
    border-radius: 8px;
    text-decoration: none;
    white-space: nowrap;
    box-shadow: 0 2px 6px rgba(181,24,46,0.25);
    flex-shrink: 0;
  }
  .est-mobile-filter-strip {
    display: flex !important;
    align-items: center;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 6px;
    margin-bottom: 12px;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
  }
  .est-mobile-filter-strip::-webkit-scrollbar {
    display: none;
  }
  .est-mobile-filter-strip .est-tab-btn {
    background: #ffffff;
    border: 1px solid #E2E8F0;
    padding: 7px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    flex-shrink: 0;
  }
  .est-mobile-filter-strip .est-tab-btn.active {
    background: #9C1F24 !important;
    color: #ffffff !important;
    border-color: #9C1F24 !important;
  }
  .est-mobile-search-row {
    display: flex !important;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
  }
  .est-mobile-search-input-wrap {
    flex: 1;
    position: relative;
  }
  .est-mobile-search-input-wrap svg {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94A3B8;
    width: 15px;
    height: 15px;
  }
  .est-mobile-search-input-wrap input {
    width: 100%;
    padding: 9px 12px 9px 36px;
    border: 1px solid #E2E8F0;
    border-radius: 10px;
    font-size: 12.5px;
    background: #ffffff;
    box-sizing: border-box;
    outline: none;
    color: #0F172A;
  }
  .est-mobile-search-input-wrap input:focus {
    border-color: #9C1F24;
    box-shadow: 0 0 0 2px rgba(156,31,36,0.1);
  }
  .est-stats-grid {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 10px !important;
    margin-bottom: 16px !important;
  }
  .est-stat-card {
    padding: 12px 14px !important;
    border-radius: 14px !important;
    gap: 10px !important;
  }
  .est-stat-card:nth-child(5) {
    grid-column: span 2 !important;
  }
  .est-stat-icon {
    width: 38px !important;
    height: 38px !important;
  }
  .est-stat-title {
    font-size: 11px !important;
  }
  .est-stat-value {
    font-size: 20px !important;
  }
  .est-table-card {
    border-radius: 16px !important;
    border: 1px solid #E2E8F0 !important;
  }
  .est-table-header-row {
    padding: 14px 16px !important;
  }
  .est-table-title-wrap h2 {
    font-size: 15px !important;
  }
  .est-desktop-table {
    display: none !important;
  }
  .est-mobile-cards-feed {
    display: flex !important;
    flex-direction: column;
    gap: 12px;
    padding: 14px 16px 20px;
  }
  .est-mobile-card {
    background: #ffffff;
    border: 1px solid #F1F5F9;
    border-radius: 16px;
    padding: 14px 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
  .est-mobile-card-top {
    display: flex;
    align-items: flex-start;
    gap: 12px;
  }
  .est-mobile-card-badge {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .est-mobile-card-main-info {
    flex: 1;
    min-width: 0;
  }
  .est-mobile-card-header-line {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
  }
  .est-mobile-card-id {
    font-size: 13.5px;
    font-weight: 800;
    color: #B5182E;
    text-decoration: none;
  }
  .est-mobile-card-total {
    font-size: 14px;
    font-weight: 800;
    color: #0F172A;
  }
  .est-mobile-card-client {
    font-size: 13px;
    font-weight: 700;
    color: #0F172A;
    margin-top: 2px;
  }
  .est-mobile-card-subject {
    font-size: 12px;
    color: #64748B;
    margin-top: 2px;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  .est-mobile-card-meta-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 6px;
    flex-wrap: wrap;
  }
  .est-mobile-pill-type {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    background: #F1F5F9;
    color: #475569;
    text-transform: capitalize;
  }
  .est-mobile-meta-text {
    font-size: 11px;
    color: #94A3B8;
  }
  .est-mobile-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 10px;
    border-top: 1px solid #F8FAFC;
    gap: 8px;
    flex-wrap: wrap;
  }
  .est-mobile-footer-left {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }
  .est-mobile-date-text {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11.5px;
    color: #64748B;
  }
  .est-mobile-footer-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-left: auto;
  }
  .est-mobile-action-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    font-weight: 600;
    color: #0F172A;
    text-decoration: none;
  }
  .est-mobile-action-link.edit {
    color: #B5182E;
  }
  .est-mobile-fab {
    display: flex !important;
    position: fixed;
    bottom: calc(var(--bottom-nav-height, 64px) + 16px) !important;
    right: 20px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #B5182E, #9C1F24);
    color: #ffffff;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    font-weight: 300;
    text-decoration: none;
    box-shadow: 0 4px 16px rgba(181,24,46,0.35);
    z-index: 999;
    transition: transform 0.15s;
  }
  .est-mobile-fab:active {
    transform: scale(0.92);
  }
}
</style>

<div class="est-page-wrap">
  <!-- Mobile Header Banner (Visible on mobile <= 768px) -->
  <div class="est-mobile-header-banner">
    <div class="est-mobile-banner-left">
      <div class="est-mobile-banner-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      </div>
      <div>
        <div class="est-mobile-banner-title">Estimates</div>
        <div class="est-mobile-banner-sub">Manage and track all your estimates in one place.</div>
      </div>
    </div>
    <a href="<?= $basePath ?>/estimate-builder" class="est-mobile-banner-btn" data-no-pjax>+ Create New Estimate</a>
  </div>

  <!-- Mobile Catalog Shortcut Banner (Visible on mobile <= 768px) -->
  <a href="<?= $basePath ?>/estimate-catalog" class="est-mobile-catalog-bar-link" data-no-pjax>
    <div class="est-mob-cat-left">
      <div class="est-mob-cat-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
          <rect x="3" y="3" width="7" height="7" rx="1.5" fill="#3B82F6"/>
          <rect x="14" y="3" width="7" height="7" rx="1.5" fill="#10B981"/>
          <rect x="14" y="14" width="7" height="7" rx="1.5" fill="#F59E0B"/>
          <rect x="3" y="14" width="7" height="7" rx="1.5" fill="#EF4444"/>
        </svg>
      </div>
      <div>
        <div class="est-mob-cat-title">Item Library &amp; Rates (ক্যাটালগ)</div>
        <div class="est-mob-cat-sub">আইটেম ও রেট লাইব্রেরি ব্রাউজ বা পরিবর্তন করুন</div>
      </div>
    </div>
    <div class="est-mob-cat-action">
      <span>ওপেন</span>
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
    </div>
  </a>

  <!-- Mobile Filter Pills (Visible on mobile <= 768px) -->
  <div class="est-mobile-filter-strip">
    <button class="est-tab-btn active" data-status="" onclick="setEstStatus(this, '')">All</button>
    <button class="est-tab-btn" data-status="draft" onclick="setEstStatus(this, 'draft')">Drafts (In Progress)</button>
    <button class="est-tab-btn" data-status="sent" onclick="setEstStatus(this, 'sent')">Sent to Client</button>
    <button class="est-tab-btn" data-status="approved" onclick="setEstStatus(this, 'approved')">Approved</button>
    <button class="est-tab-btn" data-status="converted" onclick="setEstStatus(this, 'converted')">Converted</button>
  </div>

  <!-- Mobile Search Row (Visible on mobile <= 768px) -->
  <div class="est-mobile-search-row">
    <div class="est-mobile-search-input-wrap">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      <input type="text" id="mobileEstimateSearch" placeholder="Search by Client, Subject, or Estimate No..." oninput="onMobileSearchInput(this.value)">
    </div>
  </div>

  <!-- 1. Top Action & Filter Bar (Desktop View >= 769px) -->
  <div class="est-top-bar">
    <a href="<?= $basePath ?>/estimate-catalog" class="est-catalog-btn">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <rect x="3" y="3" width="7" height="7" rx="1.5" fill="#3B82F6"/>
        <rect x="14" y="3" width="7" height="7" rx="1.5" fill="#10B981"/>
        <rect x="14" y="14" width="7" height="7" rx="1.5" fill="#F59E0B"/>
        <rect x="3" y="14" width="7" height="7" rx="1.5" fill="#EF4444"/>
      </svg>
      Item Library &amp; Rates (ক্যাটালগ)
    </a>

    <div class="est-filter-group">
      <div class="est-search-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" id="estimateSearch" placeholder="Search by Client, Subject, or Estimate No..." oninput="filterEstimates()">
      </div>

      <div class="est-status-tabs">
        <button class="est-tab-btn active" data-status="" onclick="setEstStatus(this, '')">All</button>
        <button class="est-tab-btn" data-status="draft" onclick="setEstStatus(this, 'draft')">Drafts (In Progress)</button>
        <button class="est-tab-btn" data-status="sent" onclick="setEstStatus(this, 'sent')">Sent to Client</button>
        <button class="est-tab-btn" data-status="approved" onclick="setEstStatus(this, 'approved')">Approved</button>
        <button class="est-tab-btn" data-status="converted" onclick="setEstStatus(this, 'converted')">Converted to Project</button>
      </div>
    </div>

    <a href="<?= $basePath ?>/estimate-builder" class="est-create-btn" data-no-pjax>
      + Create New Estimate
    </a>
  </div>

  <!-- 2. Five Metric Stat Cards -->
  <div class="est-stats-grid">
    <!-- Total Estimates -->
    <div class="est-stat-card" style="background:#F0F7FF; border:1px solid #DBEAFE;">
      <div class="est-stat-icon" style="background:#DBEAFE; color:#2563EB;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      </div>
      <div class="est-stat-info">
        <div class="est-stat-title">Total Estimates</div>
        <div class="est-stat-value" id="statTotal">0</div>
        <div class="est-stat-desc">All estimates</div>
      </div>
    </div>

    <!-- In Progress -->
    <div class="est-stat-card" style="background:#FEFCE8; border:1px solid #FEF08A;">
      <div class="est-stat-icon" style="background:#FEF08A; color:#CA8A04;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
      <div class="est-stat-info">
        <div class="est-stat-title">In Progress</div>
        <div class="est-stat-value" id="statDraft">0</div>
        <div class="est-stat-desc">Draft / In Progress</div>
      </div>
    </div>

    <!-- Sent to Client -->
    <div class="est-stat-card" style="background:#F0FDF4; border:1px solid #BBF7D0;">
      <div class="est-stat-icon" style="background:#DCFCE7; color:#16A34A;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
      </div>
      <div class="est-stat-info">
        <div class="est-stat-title">Sent to Client</div>
        <div class="est-stat-value" id="statSent">0</div>
        <div class="est-stat-desc">Awaiting Response</div>
      </div>
    </div>

    <!-- Approved -->
    <div class="est-stat-card" style="background:#FAF5FF; border:1px solid #E9D5FF;">
      <div class="est-stat-icon" style="background:#EDE9FE; color:#7C3AED;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
      <div class="est-stat-info">
        <div class="est-stat-title">Approved</div>
        <div class="est-stat-value" id="statApproved">0</div>
        <div class="est-stat-desc">Completed</div>
      </div>
    </div>

    <!-- Converted to Project -->
    <div class="est-stat-card" style="background:#FFF1F2; border:1px solid #FECDD3;">
      <div class="est-stat-icon" style="background:#FFE4E6; color:#E11D48;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
      </div>
      <div class="est-stat-info">
        <div class="est-stat-title">Converted to Project</div>
        <div class="est-stat-value" id="statConverted">0</div>
        <div class="est-stat-desc">Project Created</div>
      </div>
    </div>
  </div>

  <!-- 3. Estimates Table Card -->
  <div class="est-table-card">
    <div class="est-table-header-row">
      <div class="est-table-title-wrap">
        <div class="est-table-title-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        </div>
        <div>
          <h2>Estimates</h2>
          <p>Manage and track all your estimates in one place.</p>
        </div>
      </div>

      <div class="est-table-tools">
        <div class="est-mini-search">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input type="text" id="tableSearchInp" placeholder="Search estimates..." oninput="onMiniSearch(this.value)">
        </div>

        <select id="tableStatusSel" class="est-select-filter" onchange="onStatusDropdownChange(this.value)">
          <option value="">All Status</option>
          <option value="draft">Draft (In Progress)</option>
          <option value="sent">Sent to Client</option>
          <option value="approved">Approved</option>
          <option value="converted">Converted to Project</option>
        </select>

        <select id="tableDateSel" class="est-select-filter" onchange="onDateFilterChange(this.value)">
          <option value="">📅 All Dates</option>
          <option value="today">Today</option>
          <option value="week">This Week</option>
          <option value="month">This Month</option>
          <option value="year">This Year</option>
        </select>
      </div>
    </div>

    <!-- Table Container -->
    <div id="estimatesContainer">
      <div class="loading-state" style="padding:40px;text-align:center;">
        <div class="spinner" style="margin:0 auto 12px;"></div>
        <p style="color:#64748B;">Loading estimates &amp; drafts...</p>
      </div>
    </div>

    <!-- Empty State -->
    <div id="estimatesEmpty" class="empty-state" style="display:none;padding:60px 20px;text-align:center;">
      <div style="width:64px;height:64px;border-radius:50%;background:#FEE2E2;color:#9C1F24;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;">
        &#128221;
      </div>
      <h3 style="font-size:18px;color:#0F172A;margin-bottom:6px;">No estimates found</h3>
      <p style="color:#64748B;max-width:400px;margin:0 auto 20px;">Start building a commercial or residential interior estimate with pre-saved Lily Interiors templates.</p>
      <a href="<?= $basePath ?>/estimate-builder" class="est-create-btn" data-no-pjax>+ Create First Estimate</a>
    </div>
  </div>

  <!-- Mobile Floating Action Button (FAB) -->
  <a href="<?= $basePath ?>/estimate-builder" class="est-mobile-fab" id="estMobileFab" data-no-pjax title="Create New Estimate">+</a>
</div>

<script>
var currentStatus = '';
var currentDateFilter = '';
var searchTimeout = null;
var cachedEstimates = [];

function setEstStatus(btn, status) {
  document.querySelectorAll('.est-tab-btn').forEach(b => {
    if (b.getAttribute('data-status') === status) {
      b.classList.add('active');
    } else {
      b.classList.remove('active');
    }
  });
  currentStatus = status;
  var sel = document.getElementById('tableStatusSel');
  if (sel) sel.value = status;
  loadEstimates();
}

function onStatusDropdownChange(val) {
  currentStatus = val;
  document.querySelectorAll('.est-tab-btn').forEach(b => {
    if (b.getAttribute('data-status') === val) {
      b.classList.add('active');
    } else {
      b.classList.remove('active');
    }
  });
  loadEstimates();
}

function onDateFilterChange(val) {
  currentDateFilter = val;
  renderEstimatesTable(cachedEstimates);
}

function onMiniSearch(val) {
  var topInp = document.getElementById('estimateSearch');
  if (topInp) topInp.value = val;
  var mobInp = document.getElementById('mobileEstimateSearch');
  if (mobInp) mobInp.value = val;
  filterEstimates();
}

function onMobileSearchInput(val) {
  var topInp = document.getElementById('estimateSearch');
  if (topInp) topInp.value = val;
  var tblInp = document.getElementById('tableSearchInp');
  if (tblInp) tblInp.value = val;
  filterEstimates();
}

function filterEstimates() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(loadEstimates, 300);
}

function formatTk(num) {
  return '৳ ' + Math.round(Number(num || 0)).toLocaleString('en-IN');
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr;
  return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

async function loadEstimates() {
  const q = document.getElementById('estimateSearch').value.trim();
  const url = `${BASE_PATH}/api/estimates.php?action=list&status=${encodeURIComponent(currentStatus)}&q=${encodeURIComponent(q)}`;
  
  try {
    const res = await fetch(url);
    const data = await res.json();
    
    if (data.stats) {
      updateStatCounters(data.stats);
    }

    if (!data.success || !data.data || data.data.length === 0) {
      cachedEstimates = [];
      document.getElementById('estimatesContainer').innerHTML = '';
      document.getElementById('estimatesEmpty').style.display = 'block';
      return;
    }

    cachedEstimates = data.data;
    renderEstimatesTable(cachedEstimates);
  } catch (err) {
    console.error(err);
    document.getElementById('estimatesContainer').innerHTML = `
      <div class="empty-state" style="padding:30px;color:#EF4444;text-align:center;">
        <p>Failed to load estimates. Please try refreshing the page.</p>
      </div>
    `;
  }
}

function updateStatCounters(stats) {
  const elTotal = document.getElementById('statTotal');
  const elDraft = document.getElementById('statDraft');
  const elSent = document.getElementById('statSent');
  const elApproved = document.getElementById('statApproved');
  const elConverted = document.getElementById('statConverted');

  if (elTotal) elTotal.textContent = stats.total || 0;
  if (elDraft) elDraft.textContent = stats.draft || 0;
  if (elSent) elSent.textContent = stats.sent || 0;
  if (elApproved) elApproved.textContent = stats.approved || 0;
  if (elConverted) elConverted.textContent = stats.converted || 0;
}

function filterByDate(items) {
  if (!currentDateFilter) return items;
  const now = new Date();
  
  return items.filter(it => {
    const dStr = it.updated_at || it.created_at;
    if (!dStr) return true;
    const d = new Date(dStr);
    if (isNaN(d.getTime())) return true;

    if (currentDateFilter === 'today') {
      return d.toDateString() === now.toDateString();
    } else if (currentDateFilter === 'week') {
      const diffDays = (now - d) / (1000 * 60 * 60 * 24);
      return diffDays <= 7;
    } else if (currentDateFilter === 'month') {
      return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
    } else if (currentDateFilter === 'year') {
      return d.getFullYear() === now.getFullYear();
    }
    return true;
  });
}

function renderEstimatesTable(items) {
  const container = document.getElementById('estimatesContainer');
  const emptyState = document.getElementById('estimatesEmpty');

  const filtered = filterByDate(items);

  if (!filtered || filtered.length === 0) {
    container.innerHTML = '';
    emptyState.style.display = 'block';
    return;
  }
  emptyState.style.display = 'none';

  let html = `
    <div class="table-responsive est-desktop-table" style="width:100%;overflow-x:auto;">
      <table class="est-data-table">
        <thead>
          <tr>
            <th style="white-space:nowrap;padding-left:20px;">Estimate No</th>
            <th>Client &amp; Subject</th>
            <th>Type &amp; Scope</th>
            <th style="white-space:nowrap;">Grand Total</th>
            <th style="white-space:nowrap;min-width:160px;">Status</th>
            <th style="white-space:nowrap;">Updated</th>
            <th style="text-align:right;white-space:nowrap;padding-right:20px;">Actions</th>
          </tr>
        </thead>
        <tbody>
  `;

  filtered.forEach((est, idx) => {
    let badgeHtml = '';
    switch (est.status) {
      case 'draft':
        badgeHtml = `<span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;font-size:11.5px;font-weight:600;background:#FEF3C7;color:#92400E;border:1px solid #FDE68A;"><span style="width:7px;height:7px;border-radius:50%;background:#F59E0B;"></span> Draft (In Progress)</span>`;
        break;
      case 'sent':
        badgeHtml = `<span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;font-size:11.5px;font-weight:600;background:#DBEAFE;color:#1E40AF;border:1px solid #BFDBFE;"><span style="width:7px;height:7px;border-radius:50%;background:#2563EB;"></span> Sent to Client</span>`;
        break;
      case 'approved':
        badgeHtml = `<span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;font-size:11.5px;font-weight:600;background:#D1FAE5;color:#065F46;border:1px solid #A7F3D0;"><span style="width:7px;height:7px;border-radius:50%;background:#10B981;"></span> Approved</span>`;
        break;
      case 'converted':
        badgeHtml = `<span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;font-size:11.5px;font-weight:600;background:#EDE9FE;color:#5B21B6;border:1px solid #DDD6FE;"><span style="width:7px;height:7px;border-radius:50%;background:#8B5CF6;"></span> Converted to Project</span>`;
        break;
      default:
        badgeHtml = `<span style="padding:4px 10px;border-radius:20px;font-size:11px;background:#F1F5F9;color:#475569;">${esc(est.status)}</span>`;
    }

    // Pick iconic colored badge
    const badgeColors = [
      { bg: '#FEE2E2', color: '#DC2626' }, // pink/red
      { bg: '#DBEAFE', color: '#2563EB' }, // blue
      { bg: '#DCFCE7', color: '#16A34A' }, // green
      { bg: '#EDE9FE', color: '#7C3AED' }  // purple
    ];
    const colorPick = badgeColors[idx % badgeColors.length];

    const clientDisplay = est.client_company 
      ? `<strong style="color:#0F172A;">${esc(est.client_company)}</strong> <span style="color:#64748B;">(${esc(est.client_name)})</span>`
      : `<strong style="color:#0F172A;">${esc(est.client_name)}</strong>`;

    html += `
      <tr>
        <td style="padding-left:20px;white-space:nowrap;">
          <div style="display:flex;align-items:center;gap:10px;">
            <div class="est-icon-badge" style="background:${colorPick.bg};color:${colorPick.color};">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <a href="${BASE_PATH}/estimate-view?id=${est.id}" style="color:#B5182E;font-weight:800;text-decoration:none;font-size:13.5px;">
              ${esc(est.estimate_no)}
            </a>
          </div>
        </td>
        <td>
          <div style="font-size:13px;color:#0F172A;">${clientDisplay}</div>
          <div style="font-size:12px;color:#64748B;margin-top:2px;">${esc(est.subject)}</div>
          ${est.client_address ? `<div style="font-size:11px;color:#94A3B8;margin-top:2px;">${esc(est.client_address)}</div>` : ''}
        </td>
        <td>
          <span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;background:#F1F5F9;color:#475569;text-transform:capitalize;">
            ${esc(est.project_type || 'Commercial')}
          </span>
          <div style="font-size:11px;color:#94A3B8;margin-top:4px;">${est.item_count || 0} items in ${est.section_count || 0} sections</div>
        </td>
        <td style="font-weight:800;color:#0F172A;font-size:14px;white-space:nowrap;">
          ${formatTk(est.grand_total)}
        </td>
        <td style="white-space:nowrap;">
          ${badgeHtml}
        </td>
        <td style="font-size:12px;color:#64748B;white-space:nowrap;">
          <div style="display:flex;align-items:center;gap:6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span>${formatDate(est.updated_at || est.created_at)}</span>
          </div>
        </td>
        <td style="text-align:right;white-space:nowrap;padding-right:20px;">
          <div style="display:inline-flex;align-items:center;gap:14px;">
            <a href="${BASE_PATH}/estimate-view?id=${est.id}" data-no-pjax style="display:inline-flex;align-items:center;gap:4px;color:#0F172A;text-decoration:none;font-weight:600;font-size:12.5px;" onmouseover="this.style.color='#B5182E'" onmouseout="this.style.color='#0F172A'">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              View
            </a>
            <a href="${BASE_PATH}/estimate-builder?id=${est.id}" data-no-pjax style="display:inline-flex;align-items:center;gap:4px;color:#B5182E;text-decoration:none;font-weight:600;font-size:12.5px;" onmouseover="this.style.opacity='0.75'" onmouseout="this.style.opacity='1'">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
              Edit
            </a>
            <div class="action-menu-wrap">
              <button type="button" class="btn-dots" onclick="toggleActionMenu(event, ${est.id})">⋮</button>
              <div class="action-dropdown" id="actionMenu_${est.id}">
                <button type="button" onclick="duplicateEstimate(${est.id})">
                  📋 Duplicate Copy
                </button>
                ${est.status !== 'converted' 
                  ? `<button type="button" onclick="convertToProject(${est.id}, '${esc(est.estimate_no)}')">
                       🔄 Convert to Project
                     </button>` 
                  : `<div style="padding:6px 14px;font-size:11px;color:#94A3B8;">✓ Already Converted</div>`
                }
                <button type="button" onclick="deleteEstimate(${est.id}, '${esc(est.estimate_no)}')" style="color:#EF4444;">
                  🗑️ Delete Estimate
                </button>
              </div>
            </div>
          </div>
        </td>
      </tr>
    `;
  });

  html += `
        </tbody>
      </table>
    </div>
  `;

  /* Mobile Cards Feed for Mobile screens <= 768px (Matching Image 3 Reference) */
  html += '<div class="est-mobile-cards-feed">';
  filtered.forEach((est, idx) => {
    let mobBadgeHtml = '';
    switch (est.status) {
      case 'draft':
        mobBadgeHtml = `<span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:14px;font-size:11px;font-weight:600;background:#FEF3C7;color:#92400E;border:1px solid #FDE68A;"><span style="width:6px;height:6px;border-radius:50%;background:#F59E0B;"></span> Draft (In Progress)</span>`;
        break;
      case 'sent':
        mobBadgeHtml = `<span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:14px;font-size:11px;font-weight:600;background:#DBEAFE;color:#1E40AF;border:1px solid #BFDBFE;"><span style="width:6px;height:6px;border-radius:50%;background:#2563EB;"></span> Sent to Client</span>`;
        break;
      case 'approved':
        mobBadgeHtml = `<span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:14px;font-size:11px;font-weight:600;background:#D1FAE5;color:#065F46;border:1px solid #A7F3D0;"><span style="width:6px;height:6px;border-radius:50%;background:#10B981;"></span> Approved</span>`;
        break;
      case 'converted':
        mobBadgeHtml = `<span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:14px;font-size:11px;font-weight:600;background:#EDE9FE;color:#5B21B6;border:1px solid #DDD6FE;"><span style="width:6px;height:6px;border-radius:50%;background:#8B5CF6;"></span> Converted</span>`;
        break;
      default:
        mobBadgeHtml = `<span style="padding:3px 8px;border-radius:12px;font-size:11px;background:#F1F5F9;color:#475569;">${esc(est.status)}</span>`;
    }

    const badgeColors = [
      { bg: '#FEE2E2', color: '#DC2626' },
      { bg: '#DBEAFE', color: '#2563EB' },
      { bg: '#DCFCE7', color: '#16A34A' },
      { bg: '#EDE9FE', color: '#7C3AED' }
    ];
    const colorPick = badgeColors[idx % badgeColors.length];

    const clientDisplay = est.client_company 
      ? `${esc(est.client_company)} <span style="font-weight:normal;color:#64748B;">(${esc(est.client_name)})</span>`
      : `${esc(est.client_name)}`;

    html += `
      <div class="est-mobile-card">
        <div class="est-mobile-card-top">
          <div class="est-mobile-card-badge" style="background:${colorPick.bg};color:${colorPick.color};">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
          </div>
          <div class="est-mobile-card-main-info">
            <div class="est-mobile-card-header-line">
              <a href="${BASE_PATH}/estimate-view?id=${est.id}" class="est-mobile-card-id" data-no-pjax>
                ${esc(est.estimate_no)}
              </a>
              <div class="est-mobile-card-total">
                ${formatTk(est.grand_total)}
              </div>
            </div>
            <div class="est-mobile-card-client">
              ${clientDisplay}
            </div>
            ${est.subject ? `<div class="est-mobile-card-subject">${esc(est.subject)}</div>` : ''}
            <div class="est-mobile-card-meta-row">
              <span class="est-mobile-pill-type">${esc(est.project_type || 'Commercial')}</span>
              <span class="est-mobile-meta-text">⏱ ${est.item_count || 0} items in ${est.section_count || 0} sections</span>
            </div>
          </div>
        </div>

        <div class="est-mobile-card-footer">
          <div class="est-mobile-footer-left">
            ${mobBadgeHtml}
            <div class="est-mobile-date-text">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              <span>${formatDate(est.updated_at || est.created_at)}</span>
            </div>
          </div>
          <div class="est-mobile-footer-actions">
            <a href="${BASE_PATH}/estimate-view?id=${est.id}" data-no-pjax class="est-mobile-action-link" title="View Proposal">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              View
            </a>
            <a href="${BASE_PATH}/estimate-builder?id=${est.id}" data-no-pjax class="est-mobile-action-link edit" title="Edit Estimate">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
              Edit
            </a>
            <div class="action-menu-wrap">
              <button type="button" class="btn-dots" onclick="toggleActionMenu(event, 'mob_${est.id}')" title="More Actions">⋮</button>
              <div class="action-dropdown" id="actionMenu_mob_${est.id}">
                <button type="button" onclick="duplicateEstimate(${est.id})">
                  📋 Duplicate Copy
                </button>
                ${est.status !== 'converted' 
                  ? `<button type="button" onclick="convertToProject(${est.id}, '${esc(est.estimate_no)}')">
                       🔄 Convert to Project
                     </button>` 
                  : `<div style="padding:6px 14px;font-size:11px;color:#94A3B8;">✓ Already Converted</div>`
                }
                <button type="button" onclick="deleteEstimate(${est.id}, '${esc(est.estimate_no)}')" style="color:#EF4444;">
                  🗑️ Delete Estimate
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
  });
  html += '</div>';

  container.innerHTML = html;
}

function toggleActionMenu(e, id) {
  e.stopPropagation();
  const all = document.querySelectorAll('.action-dropdown');
  const target = document.getElementById('actionMenu_' + id);
  if (!target) return;
  const isOpen = target.classList.contains('show');
  all.forEach(d => d.classList.remove('show'));
  if (!isOpen) {
    target.classList.add('show');
  }
}

document.addEventListener('click', function(e) {
  if (!e.target.closest('.action-dropdown') && !e.target.closest('.btn-dots')) {
    document.querySelectorAll('.action-dropdown').forEach(d => d.classList.remove('show'));
  }
});

async function duplicateEstimate(id) {
  if (!confirm('Duplicate this estimate to create a new draft copy?')) return;
  try {
    const res = await fetch(`${BASE_PATH}/api/estimates.php?action=duplicate`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id=' + id
    });
    const d = await res.json();
    if (d.success) {
      window.location = `${BASE_PATH}/estimate-builder?id=${d.new_id}`;
    } else {
      alert(d.message || 'Could not duplicate.');
    }
  } catch(e) {
    alert('Network error while duplicating.');
  }
}

async function deleteEstimate(id, estNo) {
  if (!confirm(`Are you sure you want to delete estimate ${estNo}? This cannot be undone.`)) return;
  try {
    const res = await fetch(`${BASE_PATH}/api/estimates.php?action=delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id=' + id
    });
    const d = await res.json();
    if (d.success) {
      loadEstimates();
    } else {
      alert(d.message || 'Could not delete.');
    }
  } catch(e) {
    alert('Network error while deleting.');
  }
}

async function convertToProject(id, estNo) {
  if (!confirm(`Convert estimate "${estNo}" into an active project in Profixapp?\n\nThe project budget will be automatically set to the estimated grand total.`)) return;

  try {
    const res = await fetch(`${BASE_PATH}/api/estimates.php?action=convert_to_project`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id=' + id
    });
    const d = await res.json();
    if (d.success) {
      alert(d.message || 'Successfully converted to active project!');
      if (d.project_id) {
        window.location.href = `${BASE_PATH}/project-detail?id=${d.project_id}`;
      } else {
        loadEstimates();
      }
    } else {
      alert(d.message || 'Could not convert to project.');
    }
  } catch(e) {
    alert('Network error while converting estimate.');
  }
}

function esc(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function ensureMobileEstFabFixed() {
  if (window.innerWidth <= 768) {
    var fab = document.getElementById('estMobileFab');
    if (fab && fab.parentElement !== document.body) {
      document.body.appendChild(fab);
    }
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function() {
    ensureMobileEstFabFixed();
    loadEstimates();
  });
} else {
  ensureMobileEstFabFixed();
  loadEstimates();
}
window.addEventListener('resize', ensureMobileEstFabFixed);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
