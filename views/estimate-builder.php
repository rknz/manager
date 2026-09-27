<?php
// views/estimate-builder.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$estimateId = (int)($_GET['id'] ?? 0);
$pageTitle = $estimateId > 0 ? 'Edit Estimate' : 'Create New Estimate';
$activeNav = 'estimates';
include __DIR__ . '/../includes/header.php';
?>

<style>
/* Executive Clean Estimate Builder - Matching Reference Design */
.builder-wrap {
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
  margin: 0 auto 40px auto;
}

/* 1. Top Action Bar */
.builder-top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid #E2E8F0;
  padding: 12px 18px;
  margin-bottom: 18px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  width: 100%;
  box-sizing: border-box;
}

.builder-title-group {
  display: flex;
  align-items: center;
  gap: 12px;
}

.builder-back-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 700;
  color: #475569;
  text-decoration: none;
  padding: 6px 10px;
  border-radius: 6px;
  transition: all 0.15s;
}
.builder-back-btn:hover {
  background: #F1F5F9;
  color: #0F172A;
}

.builder-doc-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: #EDE9FE;
  color: #7C3AED;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.builder-title-text h2 {
  font-size: 17px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
  line-height: 1.2;
}
.builder-title-text span {
  font-size: 12px;
  color: #B5182E;
  font-weight: 700;
}

.builder-actions-group {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.builder-catalog-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 700;
  color: #0F172A;
  text-decoration: none;
  padding: 6px 12px;
  border-radius: 6px;
  transition: all 0.15s;
}
.builder-catalog-link:hover {
  background: #F8FAFC;
  color: #B5182E;
}

.save-status-badge {
  font-size: 12px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn-save-draft {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  padding: 7px 14px;
  font-size: 13px;
  font-weight: 700;
  color: #0F172A;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.15s;
}
.btn-save-draft:hover {
  background: #F8FAFC;
  border-color: #CBD5E1;
}

.btn-view-proposal {
  background: #9C1F24;
  color: #ffffff;
  border-radius: 20px;
  padding: 8px 18px;
  font-size: 13px;
  font-weight: 700;
  border: none;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  box-shadow: 0 2px 6px rgba(156,31,36,0.25);
  transition: all 0.15s;
}
.btn-view-proposal:hover {
  background: #B5182E;
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(156,31,36,0.3);
}

/* 2. Metadata 4-Card Grid */
.metadata-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
  margin-bottom: 20px;
  width: 100%;
  box-sizing: border-box;
}
@media (max-width: 1100px) {
  .metadata-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
  .metadata-grid { grid-template-columns: 1fr; }
}

.meta-card {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.02);
  min-width: 0;
  box-sizing: border-box;
}
.meta-icon-circle {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: #EFF6FF;
  color: #2563EB;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.meta-field-body {
  flex: 1;
  min-width: 0;
}
.meta-label {
  font-size: 11.5px;
  font-weight: 700;
  color: #475569;
  margin-bottom: 4px;
  display: block;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.meta-input {
  width: 100%;
  border: 1px solid #E2E8F0;
  border-radius: 6px;
  padding: 6px 10px;
  font-size: 12.5px;
  color: #0F172A;
  background: #ffffff;
  outline: none;
  box-sizing: border-box;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.meta-input:focus {
  border-color: #B5182E;
  box-shadow: 0 0 0 2px rgba(181,24,46,0.1);
}

/* 3. Section Card */
.builder-section-card {
  background: #ffffff;
  border: 1.5px solid #E2E8F0;
  border-radius: 12px;
  margin-bottom: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.03);
  overflow: visible !important;
  width: 100%;
  box-sizing: border-box;
  position: relative;
  transition: min-height 0.25s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}

.builder-section-card.is-active {
  min-height: 520px;
  border-color: #B5182E;
  box-shadow: 0 8px 24px rgba(181, 24, 46, 0.08);
  z-index: 50;
}

.builder-section-card:not(.is-active) {
  min-height: auto;
  border-color: #E2E8F0;
}

.sec-items-scroll-wrap {
  width: 100%;
  box-sizing: border-box;
}

.sec-items-scroll-wrap::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.sec-items-scroll-wrap::-webkit-scrollbar-track {
  background: #F8FAFC;
}
.sec-items-scroll-wrap::-webkit-scrollbar-thumb {
  background: #CBD5E1;
  border-radius: 4px;
}
.sec-items-scroll-wrap::-webkit-scrollbar-thumb:hover {
  background: #94A3B8;
}

.sec-fast-entry-wrap {
  position: relative;
  overflow: visible !important;
  background: #FFFDFD;
  z-index: 60;
  border-top: 1px solid #FEE2E2;
  box-sizing: border-box;
}

@keyframes pulse {
  0% { transform: scale(0.95); opacity: 0.8; }
  50% { transform: scale(1.15); opacity: 1; }
  100% { transform: scale(0.95); opacity: 0.8; }
}

.section-card-header {
  padding: 12px 18px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #F1F5F9;
  background: #ffffff;
  flex-wrap: wrap;
  gap: 10px;
}

.section-header-left {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.sec-letter-badge {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: #B5182E;
  color: #ffffff;
  font-weight: 800;
  font-size: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.sec-title-labels {
  display: flex;
  flex-direction: column;
  line-height: 1.2;
}
.sec-title-labels .sec-main-lbl {
  font-weight: 800;
  font-size: 12.5px;
  color: #0F172A;
  letter-spacing: 0.5px;
}
.sec-title-labels .sec-sub-lbl {
  font-size: 11px;
  color: #94A3B8;
  font-weight: 600;
}

.sec-cat-select {
  border: 1.5px solid #DC2626;
  border-radius: 20px;
  padding: 5px 16px;
  font-weight: 700;
  color: #DC2626;
  background: #ffffff;
  font-size: 13px;
  outline: none;
  cursor: pointer;
}

.sec-new-cat-btn {
  background: #F8FAFC;
  border: 1px solid #CBD5E1;
  border-radius: 20px;
  padding: 5px 14px;
  font-size: 12px;
  font-weight: 700;
  color: #0F172A;
  cursor: pointer;
  transition: all 0.15s;
}
.sec-new-cat-btn:hover {
  background: #E2E8F0;
  color: #B5182E;
}

.section-header-right {
  display: flex;
  align-items: center;
  gap: 14px;
}

.sec-subtotal-text {
  font-size: 13px;
  font-weight: 600;
  color: #475569;
}
.sec-subtotal-text strong {
  color: #DC2626;
  font-size: 15px;
  font-weight: 800;
}

.sec-delete-btn {
  background: #FFF1F2;
  color: #E11D48;
  border: 1px solid #FECDD3;
  border-radius: 8px;
  padding: 6px 12px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: all 0.15s;
}
.sec-delete-btn:hover {
  background: #FFE4E6;
  color: #BE123C;
}

.sec-reorder-group {
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.sec-move-btn {
  background: #F8FAFC;
  border: 1px solid #CBD5E1;
  border-radius: 6px;
  width: 28px;
  height: 28px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-weight: 800;
  color: #334155;
  font-size: 11px;
  transition: all 0.15s ease;
  line-height: 1;
}
.sec-move-btn:hover {
  background: #0F172A;
  color: #ffffff;
  border-color: #0F172A;
  transform: translateY(-1px);
}

/* Section Items Table */
.sec-table {
  width: 100%;
  border-collapse: collapse;
}
.sec-table th {
  background: #F8FAFC;
  padding: 10px 14px;
  font-size: 11px;
  font-weight: 700;
  color: #64748B;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1px solid #E2E8F0;
  text-align: left;
}
.sec-table td {
  padding: 10px 14px;
  border-bottom: 1px solid #F1F5F9;
  vertical-align: top;
  font-size: 13px;
}

/* Inline Fast Entry Row */
.inline-entry-row {
  background: #FFFDFD;
  border-top: 1px solid #FEE2E2;
}
.inline-entry-row td {
  padding: 10px 14px;
  background: #FFFDFD;
}

.entry-item-input {
  width: 100%;
  border: 1px solid #E2E8F0;
  border-radius: 20px;
  padding: 7px 16px;
  font-size: 12.5px;
  color: #0F172A;
  background: #ffffff;
  outline: none;
  box-sizing: border-box;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.entry-item-input:focus {
  border-color: #B5182E;
  box-shadow: 0 0 0 2px rgba(181,24,46,0.1);
}

.entry-unit-select {
  border: 1px solid #E2E8F0;
  border-radius: 6px;
  padding: 6px 8px;
  font-size: 12px;
  text-align: center;
  background: #ffffff;
  outline: none;
  width: 75px;
}

.entry-num-input {
  border: 1px solid #E2E8F0;
  border-radius: 6px;
  padding: 6px 8px;
  font-size: 12.5px;
  text-align: right;
  font-weight: 700;
  background: #ffffff;
  outline: none;
}
.entry-num-input:focus {
  border-color: #B5182E;
}

.btn-add-item-pill {
  background: #B5182E;
  color: #ffffff;
  border-radius: 20px;
  font-weight: 700;
  font-size: 12px;
  padding: 6px 16px;
  border: none;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  box-shadow: 0 2px 5px rgba(181,24,46,0.25);
  transition: all 0.15s;
}
.btn-add-item-pill:hover {
  background: #9C1F24;
  transform: translateY(-1px);
}

/* 2b. Permanent Summary & Action Banner (Sticky below Topbar) */
.builder-summary-banner {
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
    border-radius: 12px;
    border: 1.5px solid #334155;
    padding: 16px 24px;
    margin-top: 14px;
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.28);
    width: 100%;
    box-sizing: border-box;
    position: sticky;
    top: -15px;
    z-index: 990;
}
.summary-banner-left {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.summary-banner-label {
  font-size: 11px;
  font-weight: 800;
  color: #94A3B8;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.summary-banner-amount {
  font-size: 26px;
  font-weight: 900;
  color: #34D399;
  letter-spacing: 0.5px;
  line-height: 1.2;
}
.summary-banner-words {
  font-size: 12px;
  font-weight: 600;
  color: #CBD5E1;
  margin-top: 2px;
}
.summary-banner-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.btn-save-draft-banner {
  background: #1E293B;
  color: #ffffff;
  border: 1.5px solid #475569;
  border-radius: 8px;
  padding: 9px 16px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.15s;
}
.btn-save-draft-banner:hover {
  background: #334155;
  border-color: #64748B;
}
.btn-view-proposal-banner {
  background: #B5182E;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  padding: 9px 18px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  box-shadow: 0 2px 8px rgba(181, 24, 46, 0.35);
  transition: all 0.15s;
}
.btn-view-proposal-banner:hover {
  background: #9C1F24;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(181, 24, 46, 0.45);
}

/* 4. Add Section by Category Container */
.add-section-panel {
  background: #ffffff;
  border: 2px dashed #EF4444;
  border-radius: 12px;
  padding: 18px 22px;
  margin-top: 20px;
  margin-bottom: 24px;
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.05);
  width: 100%;
  box-sizing: border-box;
}
.add-section-panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 14px;
}
.quick-category-buttons-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 10px;
  margin-bottom: 14px;
}
.btn-category-add-chip {
  background: #FFF5F5;
  border: 1.5px solid #FCA5A5;
  color: #B5182E;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.15s ease;
  box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}
.btn-category-add-chip:hover {
  background: #B5182E;
  color: #ffffff;
  border-color: #B5182E;
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(181, 24, 46, 0.2);
}
.quick-custom-section-row {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  padding-top: 12px;
  border-top: 1px dashed #FCA5A5;
}
.btn-add-custom-sec {
  background: #0F172A;
  color: #ffffff;
  border: none;
  border-radius: 6px;
  padding: 8px 16px;
  font-size: 12.5px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.15s;
}
.btn-add-custom-sec:hover {
  background: #1E293B;
}
.btn-new-category-link {
  background: #F1F5F9;
  border: 1px solid #CBD5E1;
  color: #0F172A;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s;
}
.btn-new-category-link:hover {
  background: #E2E8F0;
  color: #B5182E;
}

/* Autocomplete Suggestion Dropdown */
.typeahead-wrap {
  position: relative;
  width: 100%;
}
.typeahead-dropdown {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  background: #ffffff;
  border: 1.5px solid #B5182E;
  border-radius: 8px;
  box-shadow: 0 12px 36px rgba(0,0,0,0.18);
  max-height: 280px;
  overflow-y: auto;
  z-index: 1050;
  display: none;
  margin-top: 0;
}
.typeahead-dropdown::-webkit-scrollbar {
  width: 6px;
}
.typeahead-dropdown::-webkit-scrollbar-track {
  background: #F8FAFC;
}
.typeahead-dropdown::-webkit-scrollbar-thumb {
  background: #CBD5E1;
  border-radius: 3px;
}
.typeahead-item {
  padding: 10px 12px;
  cursor: pointer;
  border-bottom: 1px solid #F1F5F9;
  transition: background 0.12s;
}
.typeahead-item:hover, .typeahead-item.active-item {
  background: #FEE2E2;
}
.typeahead-item-title {
  font-weight: 700;
  color: #0F172A;
  font-size: 13px;
}
.typeahead-item-desc {
  font-size: 11px;
  color: #64748B;
  margin-top: 2px;
  line-height: 1.3;
}
.typeahead-item-meta {
  display: flex;
  justify-content: space-between;
  font-size: 11px;
  font-weight: 700;
  color: #B5182E;
  margin-top: 4px;
}



/* Custom Modal */
.custom-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.65);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  z-index: 99999;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 16px;
  overflow-y: auto;
}
.custom-modal-overlay.is-active {
  display: flex !important;
}
.custom-modal-card {
  background: #ffffff;
  border-radius: 16px;
  width: 100%;
  max-width: 460px;
  box-shadow: 0 25px 60px rgba(0,0,0,0.25);
  border: 1px solid #E2E8F0;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  margin: auto;
}
.custom-modal-header {
  padding: 16px 20px;
  border-bottom: 1px solid #F1F5F9;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #FAFBFD;
}
.custom-modal-header h3 {
  font-size: 16px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}
.custom-modal-close {
  background: none;
  border: none;
  font-size: 24px;
  line-height: 1;
  color: #94A3B8;
  cursor: pointer;
  padding: 4px;
}
.custom-modal-close:hover {
  color: #EF4444;
}
.custom-modal-body {
  padding: 20px;
}
.custom-modal-footer {
  padding: 14px 20px;
  background: #F8FAFC;
  border-top: 1px solid #F1F5F9;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
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
  min-width: 170px;
  z-index: 1000;
  display: none;
  padding: 6px 0;
  margin-top: 4px;
}
.action-dropdown.show {
  display: block;
}
.action-dropdown button,
.action-dropdown a {
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
  text-decoration: none;
  box-sizing: border-box;
}
.action-dropdown button:hover,
.action-dropdown a:hover {
  background: #F8FAFC;
  color: #0F172A;
}

/* Card 3: Add Section by Direct Category Dropdown */
.add-section-dropdown-card {
  background: #ffffff;
  border: 1.5px dashed #EF4444;
  border-radius: 14px;
  padding: 16px 20px;
  margin-top: 16px;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.04);
}
.btn-sparkle-new-cat {
  background: none;
  border: none;
  color: #0284C7;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: color 0.15s;
}
.btn-sparkle-new-cat:hover {
  color: #B5182E;
}
.btn-add-section-pill {
  background: #B5182E;
  color: #ffffff;
  border: none;
  border-radius: 20px;
  padding: 8px 18px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.15s;
  box-shadow: 0 2px 6px rgba(181, 24, 46, 0.25);
}
.btn-add-section-pill:hover {
  background: #9C1F24;
}
.sec-mobile-menu {
  display: none;
}
.builder-top-dots {
  display: none;
}
.builder-mobile-catalog-bar-link {
  display: none;
}
.builder-mobile-sticky-bottom {
  display: none;
}

/* Card 5 Signatories styles (Desktop Default) */
.signatories-card-wrap {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-left: 4px solid #B5182E;
  border-radius: 12px;
  padding: 18px 20px;
  margin-bottom: 28px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  box-sizing: border-box;
  width: 100%;
}
.signatories-card-header {
  font-size: 13.5px;
  font-weight: 800;
  color: #0F172A;
  margin-bottom: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.signatories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 14px;
  width: 100%;
  box-sizing: border-box;
}
.signatory-field-box {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;
}
.signatory-label {
  font-size: 11.5px;
  font-weight: 700;
  color: #475569;
  display: block;
}
.signatory-input {
  width: 100%;
  border: 1px solid #E2E8F0;
  border-radius: 6px;
  padding: 6px 10px;
  font-size: 12.5px;
  color: #0F172A;
  background: #ffffff;
  outline: none;
  box-sizing: border-box;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.signatory-input:focus {
  border-color: #B5182E;
  box-shadow: 0 0 0 2px rgba(181,24,46,0.1);
}

/* Mobile Optimizations - Matching Reference Image 2 */
@media (max-width: 768px) {
  .builder-wrap {
    padding-bottom: calc(var(--bottom-nav-height, 64px) + 75px) !important;
  }
  .builder-top-bar {
    background: #0B192C !important;
    border: none !important;
    border-radius: 14px !important;
    padding: 10px 14px !important;
    margin-bottom: 12px !important;
  }
  .builder-mobile-catalog-bar-link {
    display: flex !important;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: #ffffff;
    border: 1.5px solid #FECDD3;
    border-radius: 14px;
    padding: 10px 14px;
    margin-bottom: 14px;
    text-decoration: none;
    box-shadow: 0 2px 6px rgba(181, 24, 46, 0.04);
    transition: all 0.15s ease;
    box-sizing: border-box;
  }
  .builder-mobile-catalog-bar-link:active {
    background: #FFF1F2;
    transform: scale(0.99);
  }
  .builder-mob-cat-left {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
  }
  .builder-mob-cat-icon {
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
  .builder-mob-cat-title {
    font-size: 13px;
    font-weight: 800;
    color: #0F172A;
    line-height: 1.2;
  }
  .builder-mob-cat-sub {
    font-size: 11px;
    color: #64748B;
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .builder-mob-cat-action {
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
  .builder-back-btn {
    color: #ffffff !important;
    padding: 4px 6px !important;
  }
  .builder-back-btn .back-text {
    display: none !important;
  }
  .builder-doc-icon {
    display: none !important;
  }
  .builder-title-text h2 {
    color: #ffffff !important;
    font-size: 14px !important;
  }
  .builder-title-text span {
    color: #94A3B8 !important;
    font-size: 11px !important;
  }
  .builder-catalog-link {
    display: none !important;
  }
  .save-status-badge {
    display: none !important;
  }
  .btn-save-draft {
    background: transparent !important;
    color: #ffffff !important;
    border: 1px solid rgba(255,255,255,0.25) !important;
    padding: 6px 12px !important;
    font-size: 12px !important;
    border-radius: 8px !important;
  }
  .btn-view-proposal {
    display: none !important;
  }
  .builder-top-dots {
    display: inline-block !important;
  }
  .builder-top-dots .btn-dots {
    color: #ffffff !important;
    font-size: 18px !important;
    padding: 4px 6px !important;
  }

  /* Metadata Card on Mobile (Card 1) */
  .metadata-grid {
    background: #ffffff !important;
    border: 1.5px solid #FECDD3 !important;
    border-radius: 16px !important;
    padding: 14px !important;
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 12px !important;
    margin-bottom: 16px !important;
    box-shadow: 0 2px 8px rgba(181, 24, 46, 0.04) !important;
  }
  .meta-card {
    background: transparent !important;
    border: none !important;
    border-radius: 0 !important;
    padding: 0 !important;
    box-shadow: none !important;
    display: flex !important;
    align-items: flex-start !important;
    gap: 8px !important;
  }
  .meta-card:nth-child(1),
  .meta-card:nth-child(2) {
    grid-column: span 1 !important;
  }
  .meta-card:nth-child(3),
  .meta-card:nth-child(4) {
    grid-column: span 2 !important;
  }
  .meta-icon-circle {
    width: 28px !important;
    height: 28px !important;
    border-radius: 8px !important;
    margin-top: 2px !important;
  }
  .meta-icon-circle svg {
    width: 14px !important;
    height: 14px !important;
  }
  .meta-field-body {
    flex: 1;
    min-width: 0;
  }
  .meta-label {
    font-size: 11px !important;
    margin-bottom: 4px !important;
  }
  .meta-input {
    font-size: 12px !important;
    padding: 7px 10px !important;
    border-radius: 8px !important;
  }

  /* Docked Summary Banner hidden on mobile; sticky button used instead */
  .builder-summary-banner {
    display: none !important;
  }

  /* Section Card (Card 2) on Mobile */
  .builder-section-card {
    border: 1.5px solid #FECDD3 !important;
    border-radius: 16px !important;
    margin-bottom: 16px !important;
    overflow: hidden !important;
  }
  .section-card-header {
    padding: 10px 14px !important;
    gap: 8px !important;
  }
  .sec-letter-badge {
    width: 28px !important;
    height: 28px !important;
    font-size: 13.5px !important;
    border-radius: 7px !important;
  }
  .sec-title-labels {
    font-size: 11px !important;
  }
  .sec-main-lbl {
    font-size: 11.5px !important;
  }
  .sec-sub-lbl {
    display: none !important;
  }
  .sec-cat-select {
    padding: 4px 10px !important;
    font-size: 12px !important;
  }
  .sec-new-cat-btn {
    background: #B5182E !important;
    color: #ffffff !important;
    border: none !important;
    padding: 4px 10px !important;
    font-size: 11px !important;
    border-radius: 16px !important;
  }
  .sec-active-indicator {
    display: none !important;
  }
  .sec-subtotal-text {
    font-size: 11.5px !important;
  }
  .sec-subtotal-text strong {
    font-size: 13px !important;
  }
  .sec-delete-btn,
  .sec-reorder-group {
    display: none !important;
  }
  .sec-mobile-menu {
    display: inline-block !important;
    margin-left: auto;
  }

  /* Section Items Table */
  .sec-items-scroll-wrap {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
  }
  .sec-table {
    min-width: 540px !important;
  }

  /* Fast Inline Entry (Dashed Box in Card 2) */
  .sec-fast-entry-wrap {
    border: 1.5px dashed #EF4444 !important;
    border-radius: 14px !important;
    background: #FFFDFD !important;
    padding: 12px 14px !important;
    margin: 12px 14px !important;
  }
  .sec-fast-entry-wrap table {
    display: block !important;
    width: 100% !important;
  }
  .sec-fast-entry-wrap colgroup {
    display: none !important;
  }
  .sec-fast-entry-wrap tbody {
    display: block !important;
    width: 100% !important;
  }
  .sec-fast-entry-wrap .inline-entry-row {
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    gap: 8px !important;
    background: transparent !important;
    padding: 0 !important;
    border: none !important;
  }
  .sec-fast-entry-wrap .inline-entry-row td {
    padding: 0 !important;
    background: transparent !important;
    border: none !important;
  }
  .sec-fast-entry-wrap .inline-entry-row td:nth-child(1) {
    /* + icon */
    width: 18px !important;
    font-size: 18px !important;
    font-weight: 800 !important;
    color: #B5182E !important;
  }
  .sec-fast-entry-wrap .inline-entry-row td:nth-child(2) {
    /* Item Input */
    flex: 1 1 180px !important;
    min-width: 140px !important;
  }
  .sec-fast-entry-wrap .inline-entry-row td:nth-child(3) {
    /* Unit select */
    width: 65px !important;
  }
  .sec-fast-entry-wrap .inline-entry-row td:nth-child(4) {
    /* Qty input */
    width: 50px !important;
  }
  .sec-fast-entry-wrap .inline-entry-row td:nth-child(5) {
    /* Rate input */
    width: 70px !important;
  }
  .sec-fast-entry-wrap .inline-entry-row td:nth-child(6) {
    /* Live subtotal */
    flex: 1 1 auto !important;
    text-align: right !important;
    font-size: 13.5px !important;
    font-weight: 800 !important;
    color: #0F172A !important;
    padding-top: 4px !important;
  }
  .sec-fast-entry-wrap .inline-entry-row td:nth-child(7) {
    /* + Add button */
    flex: 0 0 auto !important;
    padding-top: 4px !important;
  }

  /* Card 3: Direct Category Dropdown Box */
  .add-section-dropdown-card {
    border: 1.5px dashed #EF4444 !important;
    border-radius: 16px !important;
    padding: 14px 16px !important;
    margin-bottom: 16px !important;
  }

  /* Card 4: Quick Category Chips Panel */
  .add-section-panel {
    border: 1.5px solid #FECDD3 !important;
    border-radius: 16px !important;
    padding: 14px 16px !important;
    margin-bottom: 16px !important;
  }
  .quick-category-buttons-grid {
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 8px !important;
  }
  .btn-category-add-chip {
    padding: 8px 10px !important;
    font-size: 11.5px !important;
  }
  .quick-custom-section-row {
    flex-direction: column !important;
    align-items: stretch !important;
    gap: 8px !important;
  }
  .quick-custom-section-row input {
    max-width: 100% !important;
  }

  /* Card 5: Signatories & Authority Card on Mobile */
  .signatories-card-wrap {
    display: block !important;
    background: #ffffff !important;
    border: 1.5px solid #FECDD3 !important;
    border-left: 1.5px solid #FECDD3 !important;
    border-radius: 16px !important;
    padding: 16px 14px !important;
    margin-bottom: 24px !important;
    box-shadow: 0 2px 8px rgba(181, 24, 46, 0.04) !important;
    width: 100% !important;
    box-sizing: border-box !important;
  }
  .signatories-card-header {
    font-size: 13px !important;
    font-weight: 800 !important;
    color: #0F172A !important;
    margin-bottom: 12px !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding-bottom: 8px !important;
    border-bottom: 1px dashed #FECDD3 !important;
  }
  .signatories-grid {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 10px !important;
    width: 100% !important;
    box-sizing: border-box !important;
  }
  .signatory-field-box {
    display: flex !important;
    flex-direction: column !important;
    gap: 4px !important;
    width: 100% !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
  }
  .signatory-label {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #475569 !important;
    display: block !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
  }
  .signatory-input {
    width: 100% !important;
    border: 1px solid #E2E8F0 !important;
    border-radius: 8px !important;
    padding: 8px 10px !important;
    font-size: 12.5px !important;
    color: #0F172A !important;
    background: #ffffff !important;
    outline: none !important;
    box-sizing: border-box !important;
  }
  .signatory-input:focus {
    border-color: #B5182E !important;
    box-shadow: 0 0 0 2px rgba(181, 24, 46, 0.1) !important;
  }

  /* Sticky Bottom Save Button Bar (Fixed right above mobile bottom-nav) */
  .builder-mobile-sticky-bottom {
    display: flex !important;
    position: fixed;
    bottom: var(--bottom-nav-height, 64px) !important;
    left: 0;
    right: 0;
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    padding: 10px 16px;
    border-top: 1px solid #E2E8F0;
    box-shadow: 0 -4px 16px rgba(0,0,0,0.08);
    z-index: 995;
    box-sizing: border-box;
  }
  .btn-mobile-save-estimate {
    width: 100%;
    background: #9C1F24;
    color: #ffffff;
    border: none;
    border-radius: 12px;
    padding: 13px 20px;
    font-size: 14.5px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(156, 31, 36, 0.35);
  }
}

@media (max-width: 480px) {
  .signatories-grid {
    grid-template-columns: 1fr !important;
    gap: 10px !important;
  }
}
</style>

<div class="builder-wrap">
  <!-- 1. Top Action & Navigation Bar -->
  <div class="builder-top-bar">
    <div class="builder-title-group">
      <a href="<?= $basePath ?>/estimates" class="builder-back-btn" data-no-pjax>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        <span class="back-text">All Estimates</span>
      </a>

      <div class="builder-doc-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      </div>

      <div class="builder-title-text">
        <h2 id="pageHeadingTitle"><?= $estimateId > 0 ? 'Edit Estimate' : 'New Estimate Builder' ?></h2>
        <span id="estimateNoLabel"><?= $estimateId > 0 ? ('EST-' . $estimateId) : 'EST-NEW' ?></span>
      </div>
    </div>

    <div class="builder-actions-group">
      <a href="<?= $basePath ?>/estimate-catalog" target="_blank" class="builder-catalog-link" title="Open Item Library in new tab">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
          <rect x="3" y="3" width="7" height="7" rx="1.5" fill="#3B82F6"/>
          <rect x="14" y="3" width="7" height="7" rx="1.5" fill="#10B981"/>
          <rect x="14" y="14" width="7" height="7" rx="1.5" fill="#F59E0B"/>
          <rect x="3" y="14" width="7" height="7" rx="1.5" fill="#EF4444"/>
        </svg>
        Item Library (ক্যাটালগ)
      </a>

      <span id="saveStatusIndicator" class="save-status-badge" style="color:#10B981;">
        <span style="width:8px;height:8px;border-radius:50%;background:#10B981;"></span> Ready
      </span>

      <button type="button" class="btn-save-draft" onclick="saveEstimateData(false)">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Save Draft
      </button>

      <button type="button" class="btn-view-proposal" onclick="saveAndGoView()">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        View &amp; Print Proposal
      </button>

      <!-- 3-dots dropdown menu for mobile extra actions -->
      <div class="action-menu-wrap builder-top-dots">
        <button type="button" class="btn-dots" onclick="toggleActionMenu(event, 'top_bar_menu')">⋮</button>
        <div class="action-dropdown" id="actionMenu_top_bar_menu">
          <a href="<?= $basePath ?>/estimate-catalog" target="_blank">
            📚 Item Library (ক্যাটালগ)
          </a>
          <button type="button" onclick="saveAndGoView()">
            👁️ View &amp; Print Proposal
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Mobile Catalog Shortcut Banner (Visible on mobile <= 768px) -->
  <a href="<?= $basePath ?>/estimate-catalog" target="_blank" class="builder-mobile-catalog-bar-link">
    <div class="builder-mob-cat-left">
      <div class="builder-mob-cat-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
          <rect x="3" y="3" width="7" height="7" rx="1.5" fill="#3B82F6"/>
          <rect x="14" y="3" width="7" height="7" rx="1.5" fill="#10B981"/>
          <rect x="14" y="14" width="7" height="7" rx="1.5" fill="#F59E0B"/>
          <rect x="3" y="14" width="7" height="7" rx="1.5" fill="#EF4444"/>
        </svg>
      </div>
      <div>
        <div class="builder-mob-cat-title">Item Library &amp; Rates (ক্যাটালগ)</div>
        <div class="builder-mob-cat-sub">আইটেম ও রেট শিট দেখুন বা নতুন আইটেম যোগ করুন</div>
      </div>
    </div>
    <div class="builder-mob-cat-action">
      <span>ওপেন</span>
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
    </div>
  </a>

  <!-- 2. Metadata 4-Card Grid (Matching Image 2 Card 1) -->
  <div class="metadata-grid">
    <!-- Client / Company Name -->
    <div class="meta-card">
      <div class="meta-icon-circle" style="background:#FEE2E2;color:#B5182E;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </div>
      <div class="meta-field-body">
        <label class="meta-label">Client / Company Name <span style="color:#EF4444;">*</span></label>
        <input type="text" id="inpClientName" class="meta-input" placeholder="e.g. ASIABIZ Technology" oninput="markDirty()">
      </div>
    </div>

    <!-- Subject / Project Title -->
    <div class="meta-card">
      <div class="meta-icon-circle" style="background:#E0F2FE;color:#0284C7;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
      </div>
      <div class="meta-field-body">
        <label class="meta-label">Subject / Project Title <span style="color:#EF4444;">*</span></label>
        <input type="text" id="inpSubject" class="meta-input" placeholder="e.g. Commercial Interior..." oninput="markDirty()">
      </div>
    </div>

    <!-- Site Location / Address -->
    <div class="meta-card">
      <div class="meta-icon-circle" style="background:#F1F5F9;color:#475569;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      </div>
      <div class="meta-field-body">
        <label class="meta-label">Site Location / Address</label>
        <input type="text" id="inpAddress" class="meta-input" placeholder="e.g. Multiplan Center, Elephant Road, Dhaka" oninput="markDirty()">
      </div>
    </div>

    <!-- Status -->
    <div class="meta-card">
      <div class="meta-icon-circle" style="background:#F1F5F9;color:#475569;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
      </div>
      <div class="meta-field-body">
        <label class="meta-label">Status</label>
        <select id="inpStatus" class="meta-input" onchange="markDirty()" style="cursor:pointer;font-weight:600;">
          <option value="draft">🟡 Draft (In Progress)</option>
          <option value="sent">🔵 Sent to Client</option>
          <option value="approved">🟢 Approved</option>
          <option value="converted">🟣 Converted to Project</option>
        </select>
      </div>
    </div>
  </div>

  <!-- 2b. Permanent Summary & Action Banner (Docked permanently below Metadata Grid) -->
  <div class="builder-summary-banner">
    <div class="summary-banner-left">
      <div class="summary-banner-label">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        TOTAL ESTIMATE AMOUNT (সর্বমোট বাজেট)
      </div>
      <div class="summary-banner-amount" id="summaryGrandTotal">
        ৳ 0.00
      </div>
      <div class="summary-banner-words" id="summaryInWords">
        In Word: Zero Taka Only.
      </div>
    </div>

    <div class="summary-banner-actions">
      <button type="button" class="btn-save-draft-banner" onclick="saveEstimateData(false)">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Save Draft
      </button>
      <button type="button" class="btn-view-proposal-banner" onclick="saveAndGoView()">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        View &amp; Print Proposal (Lily Letterhead)
      </button>
    </div>
  </div>

  <!-- 3. Sections Container -->
  <div id="sectionsContainer">
    <!-- Rendered dynamically by JavaScript -->
  </div>

  <!-- 3b. Add Section by Direct Category Dropdown (Card 3 in Image 2) -->
  <div class="add-section-dropdown-card">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
      <div style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:18px;">📁</span>
        <span style="font-weight:800;font-size:13.5px;color:#0F172A;">
          সেকশন যুক্ত করুন:
        </span>
      </div>
      <button type="button" class="btn-sparkle-new-cat" onclick="openCreateCategoryModalInsideBuilder(-1)">
        ✨ নতুন ক্যাটাগরি তৈরি করুন (New Category)
      </button>
    </div>
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
      <select id="selQuickAddCategory" class="sec-cat-select" style="min-width:200px;"></select>
      <button type="button" class="btn-add-section-pill" onclick="addSectionFromQuickBar()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        Add Section (+ সেকশন যোগ করুন)
      </button>
    </div>
  </div>

  <!-- 4. Add Section by Category Container (Card 4 in Image 2: Chips + Custom Input) -->
  <div class="add-section-panel">
    <div class="add-section-panel-header">
      <div style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:17px;color:#F59E0B;font-weight:900;">✚</span>
        <span style="font-weight:800;font-size:14px;color:#0F172A;">
          Add New Section by Category (ক্যাটাগরি অনুযায়ী সেকশন যুক্ত করুন):
        </span>
      </div>
      <button type="button" class="btn-new-category-link" onclick="openCreateCategoryModalInsideBuilder(-1)">
        ✨ নতুন ক্যাটাগরি তৈরি করুন (New Category)
      </button>
    </div>

    <!-- Quick Category Buttons Grid (Rendered dynamically) -->
    <div class="quick-category-buttons-grid" id="quickCategoryButtonsGrid">
      <!-- Populated with category chips e.g. + Ceiling, + Furniture, + Wall & Floor, etc. -->
    </div>

    <!-- Custom Section Name Row -->
    <div class="quick-custom-section-row">
      <span style="font-size:12.5px;font-weight:700;color:#475569;">বা কাস্টম সেকশন নাম:</span>
      <input type="text" id="inpQuickCustomSec" class="meta-input" placeholder="e.g. Master Bedroom Work, Conference Room..." style="max-width:320px;border-color:#EF4444;" onkeydown="if(event.key==='Enter') addSectionFromQuickBar()">
      <button type="button" class="btn-add-custom-sec" onclick="addSectionFromQuickBar()">
        + Add Section (+ সেকশন যোগ করুন)
      </button>
    </div>
  </div>

  <!-- Card 5: Signatories & Authority Details Card (Matching Image 2) -->
  <div class="signatories-card-wrap">
    <div class="signatories-card-header">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#B5182E" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      <span>Signatories &amp; Authority Details (স্বাক্ষর ও অনুমোদনকারী বিবরণী)</span>
    </div>
    <div class="signatories-grid">
      <div class="signatory-field-box">
        <label class="signatory-label">Prepared By (Name / প্রস্তুতকারক)</label>
        <input type="text" id="inpPreparedBy" class="signatory-input" placeholder="e.g. Md. Rukonuzzaman" oninput="markDirty()">
      </div>
      <div class="signatory-field-box">
        <label class="signatory-label">Prepared By (Designation / পদবী)</label>
        <input type="text" id="inpPreparedByDesig" class="signatory-input" placeholder="e.g. Interior Designer" oninput="markDirty()">
      </div>
      <div class="signatory-field-box">
        <label class="signatory-label">Authorized By (Name / অনুমোদনকারী)</label>
        <input type="text" id="inpApprovedBy" class="signatory-input" placeholder="e.g. Md. Mustafizur Rahman" oninput="markDirty()">
      </div>
      <div class="signatory-field-box">
        <label class="signatory-label">Authorized By (Designation / পদবী)</label>
        <input type="text" id="inpApprovedByDesig" class="signatory-input" placeholder="e.g. Managing Director" oninput="markDirty()">
      </div>
    </div>
  </div>

  <!-- Mobile Sticky Bottom Save Button (Matching Image 2) -->
  <div class="builder-mobile-sticky-bottom" id="builderMobileStickyBottom">
    <button type="button" class="btn-mobile-save-estimate" onclick="saveEstimateData(false)">
      💾 Save Estimate ➔
    </button>
  </div>
</div>

<!-- Modal: Create New Category -->
<div class="custom-modal-overlay" id="createCategoryModal">
  <div class="custom-modal-card">
    <div class="custom-modal-header">
      <h3>📁 Create New Category (নতুন ক্যাটাগরি)</h3>
      <button type="button" class="custom-modal-close" onclick="closeCustomModal('createCategoryModal')">&times;</button>
    </div>
    <div class="custom-modal-body">
      <div class="form-group">
        <label style="font-weight:700;font-size:13px;color:#0F172A;display:block;margin-bottom:6px;">Category Name <span style="color:#EF4444;">*</span></label>
        <input type="text" id="inpBuilderNewCat" class="meta-input" placeholder="e.g. Living Room, Gypsum False Ceiling..." onkeydown="if(event.key==='Enter') submitBuilderCategory()">
        <span style="font-size:12px;color:#64748B;margin-top:6px;display:block;">
          ক্যাটাগরিটি পার্মানেন্টভাবে ডাটাবেজে সেভ হবে এবং সাথে সাথে এই এস্টিমেটে সেকশন হিসেবে যুক্ত হবে।
        </span>
      </div>
    </div>
    <div class="custom-modal-footer">
      <button type="button" class="btn-save-draft" onclick="closeCustomModal('createCategoryModal')">Cancel</button>
      <button type="button" class="btn-view-proposal" onclick="submitBuilderCategory()" id="btnSaveBuilderCat">
        💾 Save &amp; Add Section
      </button>
    </div>
  </div>
</div>



<script>
var ESTIMATE_ID = <?= $estimateId ?>;
var estimateData = {
  id: ESTIMATE_ID,
  client_name: '',
  client_company: '',
  subject: '',
  client_address: '',
  status: 'draft',
  sections: []
};

var allCatalogItems = [];
var availableCategories = ['Ceiling', 'Furniture', 'Wall & Floor', 'Paint', 'Electrical'];
var activeSectionIdx = 0;
var isModified = false;
var currentTargetSectionIdxForNewCat = -1;
var isBuilderInitialized = false;

function markDirty() {
  isModified = true;
  var ind = document.getElementById('saveStatusIndicator');
  if (ind) {
    ind.style.color = '#F59E0B';
    ind.innerHTML = '<span style="width:8px;height:8px;border-radius:50%;background:#F59E0B;"></span> Unsaved changes...';
  }
}

function markClean() {
  isModified = false;
  var ind = document.getElementById('saveStatusIndicator');
  if (ind) {
    var t = new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
    ind.style.color = '#10B981';
    ind.innerHTML = '<span style="width:8px;height:8px;border-radius:50%;background:#10B981;"></span> Saved (' + t + ')';
  }
}

// 1. INITIALIZE BUILDER
async function initBuilder() {
  if (isBuilderInitialized) return;
  isBuilderInitialized = true;

  try {
    await loadCategoriesAndCatalog();
  } catch(e) {
    console.error('Failed to load categories/catalog', e);
  }

  if (ESTIMATE_ID > 0) {
    await loadExistingEstimate();
  } else {
    // Set default signatories
    var prepByInp = document.getElementById('inpPreparedBy');
    if (prepByInp && !prepByInp.value) prepByInp.value = 'Md. Rukonuzzaman';
    var prepDesigInp = document.getElementById('inpPreparedByDesig');
    if (prepDesigInp && !prepDesigInp.value) prepDesigInp.value = 'Interior Designer';
    var appByInp = document.getElementById('inpApprovedBy');
    if (appByInp && !appByInp.value) appByInp.value = 'Md. Mustafizur Rahman';
    var appDesigInp = document.getElementById('inpApprovedByDesig');
    if (appDesigInp && !appDesigInp.value) appDesigInp.value = 'Managing Director';

    // Start fresh with standard 'Ceiling' section if no sections present
    if (!estimateData.sections || !estimateData.sections.length) {
      addSectionDirectly('Ceiling', false);
    }
  }

  updateQuickAddCategorySelect();
  renderQuickCategoryButtons();
  recalcGrandTotal();
}

async function loadCategoriesAndCatalog() {
  try {
    var resCats = await fetch(BASE_PATH + '/api/estimates.php?action=categories');
    var dCats = await resCats.json();
    if (dCats.success && dCats.data && dCats.data.length) {
      availableCategories = dCats.data;
    }

    var resItems = await fetch(BASE_PATH + '/api/estimates.php?action=catalog');
    var dItems = await resItems.json();
    if (dItems.success && dItems.data) {
      allCatalogItems = dItems.data;
    }
  } catch(e) {
    console.error('Failed loading categories/catalog', e);
  }
}

async function loadExistingEstimate() {
  try {
    var res = await fetch(BASE_PATH + '/api/estimates.php?action=get&id=' + ESTIMATE_ID);
    var d = await res.json();
    if (d.success && d.data) {
      var est = d.data.estimate || d.data;
      estimateData.id = est.id || ESTIMATE_ID;
      estimateData.client_name = est.client_name || '';
      estimateData.client_company = est.client_company || est.client_name || '';
      estimateData.subject = est.subject || '';
      estimateData.client_address = est.client_address || '';
      estimateData.status = est.status || 'draft';
      estimateData.sections = d.data.sections || [];

      var clientInp = document.getElementById('inpClientName');
      if (clientInp) clientInp.value = estimateData.client_name;
      var subjInp = document.getElementById('inpSubject');
      if (subjInp) subjInp.value = estimateData.subject;
      var addrInp = document.getElementById('inpAddress');
      if (addrInp) addrInp.value = estimateData.client_address;
      var statInp = document.getElementById('inpStatus');
      if (statInp) statInp.value = estimateData.status;

      var prepByInp = document.getElementById('inpPreparedBy');
      if (prepByInp) prepByInp.value = est.prepared_by || 'Md. Rukonuzzaman';
      var prepDesigInp = document.getElementById('inpPreparedByDesig');
      if (prepDesigInp) prepDesigInp.value = est.prepared_by_designation || 'Interior Designer';
      var appByInp = document.getElementById('inpApprovedBy');
      if (appByInp) appByInp.value = est.approved_by || 'Md. Mustafizur Rahman';
      var appDesigInp = document.getElementById('inpApprovedByDesig');
      if (appDesigInp) appDesigInp.value = est.approved_by_designation || 'Managing Director';

      var noLabel = document.getElementById('estimateNoLabel');
      if (noLabel) noLabel.textContent = est.estimate_no || ('EST-' + ESTIMATE_ID);
      var titleLabel = document.getElementById('pageHeadingTitle');
      if (titleLabel) titleLabel.textContent = 'Edit Estimate (' + (est.estimate_no || '') + ')';

      activeSectionIdx = 0;

      // Ensure sections have all their category names registered
      if (estimateData.sections && estimateData.sections.length) {
        estimateData.sections.forEach(function(sec) {
          if (sec.name && !availableCategories.includes(sec.name)) {
            availableCategories.push(sec.name);
          }
        });
      } else {
        addSectionDirectly('Ceiling', false);
      }

      renderAllSections();
    }
  } catch(e) {
    console.error(e);
    alert('Failed to load estimate data.');
  }
}

// 2. SECTION MANAGEMENT
function setActiveSection(sIdx) {
  if (activeSectionIdx === sIdx) return;
  preservePendingEntries();
  activeSectionIdx = sIdx;
  renderAllSections();
  restorePendingEntries();
  setTimeout(function() {
    var inp = document.getElementById('entryItem_' + sIdx);
    if (inp) inp.focus();
  }, 50);
}

function onSectionCardClick(event, sIdx) {
  if (event.target.closest('.sec-delete-btn') || 
      event.target.closest('button[onclick*="deleteLineItem"]') || 
      event.target.closest('.sec-new-cat-btn') ||
      event.target.closest('.sec-cat-select')) {
    return;
  }
  if (activeSectionIdx !== sIdx) {
    setActiveSection(sIdx);
  }
}

function renderQuickCategoryButtons() {
  var grid = document.getElementById('quickCategoryButtonsGrid');
  if (!grid) return;

  var html = availableCategories.map(function(cat) {
    var icon = '📁';
    var cLower = (cat || '').toLowerCase();
    if (cLower.includes('ceiling')) icon = '🏠';
    else if (cLower.includes('furniture')) icon = '🪑';
    else if (cLower.includes('wall') || cLower.includes('floor')) icon = '🧱';
    else if (cLower.includes('paint')) icon = '🎨';
    else if (cLower.includes('electric')) icon = '⚡';
    else if (cLower.includes('glass')) icon = '🪟';
    else if (cLower.includes('civil') || cLower.includes('tile')) icon = '🏗️';

    return '<button type="button" class="btn-category-add-chip" onclick="addSectionDirectly(\'' + escAttr(cat) + '\', true)" title="Click to add \'' + escAttr(cat) + '\' section">' +
      '<span>' + icon + '</span>' +
      '<span>+ ' + esc(cat) + '</span>' +
    '</button>';
  }).join('');

  grid.innerHTML = html;
}

function updateQuickAddCategorySelect() {
  var sel = document.getElementById('selQuickAddCategory');
  if (!sel) return;

  var usedCats = (estimateData.sections || []).map(function(s) { 
    return (s.name || '').toLowerCase(); 
  });
  
  var firstUnused = availableCategories.find(function(c) {
    return !usedCats.includes((c || '').toLowerCase());
  }) || availableCategories[0] || 'Ceiling';

  sel.innerHTML = availableCategories.map(function(c) {
    return '<option value="' + escAttr(c) + '" ' + (c === firstUnused ? 'selected' : '') + '>' + esc(c) + '</option>';
  }).join('');
}

function addSectionFromQuickBar() {
  var customInp = document.getElementById('inpQuickCustomSec');
  var customVal = customInp ? customInp.value.trim() : '';
  var sel = document.getElementById('selQuickAddCategory');
  var cat = customVal || (sel ? sel.value : '') || (availableCategories[0] || 'Ceiling');

  if (customVal) {
    customInp.value = '';
    if (!availableCategories.includes(customVal)) {
      availableCategories.push(customVal);
      renderQuickCategoryButtons();
      // Persist in background
      fetch(BASE_PATH + '/api/estimates.php?action=add_category', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: customVal })
      }).catch(function(e) { console.error(e); });
    }
  }

  addSectionDirectly(cat, true);
}

function addSectionDirectly(catName, shouldMarkDirty) {
  var cat = catName || (availableCategories[estimateData.sections.length % availableCategories.length] || 'General Work');

  preservePendingEntries();

  estimateData.sections.push({
    name: cat,
    order: estimateData.sections.length + 1,
    items: []
  });

  activeSectionIdx = estimateData.sections.length - 1;

  renderAllSections();
  restorePendingEntries();
  updateQuickAddCategorySelect();
  renderQuickCategoryButtons();

  if (shouldMarkDirty) {
    markDirty();
  }

  setTimeout(function() {
    var secIdx = estimateData.sections.length - 1;
    var block = document.getElementById('sectionCard_' + secIdx);
    if (block) {
      block.scrollIntoView({ behavior: 'smooth', block: 'center' });
      var inp = document.getElementById('entryItem_' + secIdx);
      if (inp) inp.focus();
    }
  }, 100);
}

function removeSection(secIdx) {
  var sec = estimateData.sections[secIdx];
  if (!confirm('Are you sure you want to delete section "' + (sec.name || 'Section') + '" and all its items?')) return;

  preservePendingEntries();
  estimateData.sections.splice(secIdx, 1);

  if (!estimateData.sections.length) {
    activeSectionIdx = 0;
    addSectionDirectly('Ceiling', true);
  } else {
    if (activeSectionIdx >= estimateData.sections.length) {
      activeSectionIdx = Math.max(0, estimateData.sections.length - 1);
    }
    renderAllSections();
    restorePendingEntries();
  }

  updateQuickAddCategorySelect();
  markDirty();
}

function moveSectionUp(e, sIdx) {
  if (e) e.stopPropagation();
  if (sIdx <= 0) return;
  preservePendingEntries();
  autoCommitPendingEntries();

  var temp = estimateData.sections[sIdx];
  estimateData.sections[sIdx] = estimateData.sections[sIdx - 1];
  estimateData.sections[sIdx - 1] = temp;

  estimateData.sections.forEach(function(sec, idx) {
    sec.order = idx + 1;
  });

  activeSectionIdx = sIdx - 1;
  renderAllSections();
  restorePendingEntries();
  markDirty();

  setTimeout(function() {
    var el = document.getElementById('sectionCard_' + (sIdx - 1));
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }, 50);
}

function moveSectionDown(e, sIdx) {
  if (e) e.stopPropagation();
  if (sIdx >= estimateData.sections.length - 1) return;
  preservePendingEntries();
  autoCommitPendingEntries();

  var temp = estimateData.sections[sIdx];
  estimateData.sections[sIdx] = estimateData.sections[sIdx + 1];
  estimateData.sections[sIdx + 1] = temp;

  estimateData.sections.forEach(function(sec, idx) {
    sec.order = idx + 1;
  });

  activeSectionIdx = sIdx + 1;
  renderAllSections();
  restorePendingEntries();
  markDirty();

  setTimeout(function() {
    var el = document.getElementById('sectionCard_' + (sIdx + 1));
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }, 50);
}

function changeSectionCategory(secIdx, newCat) {
  if (!estimateData.sections[secIdx]) return;
  estimateData.sections[secIdx].name = newCat;
  markDirty();
  
  preservePendingEntries();
  renderAllSections();
  restorePendingEntries();
  updateQuickAddCategorySelect();

  setTimeout(function() {
    var inp = document.getElementById('entryItem_' + secIdx);
    if (inp) inp.focus();
  }, 50);
}

function openCreateCategoryModalInsideBuilder(targetSecIdx) {
  currentTargetSectionIdxForNewCat = typeof targetSecIdx === 'number' ? targetSecIdx : -1;
  var inp = document.getElementById('inpBuilderNewCat');
  if (inp) inp.value = '';
  openCustomModal('createCategoryModal');
  setTimeout(function() {
    if (inp) inp.focus();
  }, 100);
}

async function submitBuilderCategory() {
  var inp = document.getElementById('inpBuilderNewCat');
  var name = inp ? inp.value.trim() : '';
  if (!name) {
    alert('Please enter a category name (ক্যাটাগরির নাম লিখুন).');
    if (inp) inp.focus();
    return;
  }

  var btn = document.getElementById('btnSaveBuilderCat');
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Saving...';
  }

  try {
    var res = await fetch(BASE_PATH + '/api/estimates.php?action=add_category', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name: name })
    });
    var d = await res.json();
    if (d.success) {
      closeCustomModal('createCategoryModal');
      await loadCategoriesAndCatalog();

      if (currentTargetSectionIdxForNewCat >= 0) {
        changeSectionCategory(currentTargetSectionIdxForNewCat, name);
      } else {
        addSectionDirectly(name, true);
      }
      updateQuickAddCategorySelect();
      renderQuickCategoryButtons();
    } else {
      alert(d.message || 'Failed to save category.');
    }
  } catch(e) {
    console.error(e);
    alert('Network error while saving category.');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.textContent = '💾 Save & Add Section';
    }
  }
}

// 3. PRESERVE & RESTORE DRAFT ENTRY INPUTS
var pendingEntries = {};

function preservePendingEntries() {
  pendingEntries = {};
  if (!estimateData.sections) return;
  estimateData.sections.forEach(function(sec, sIdx) {
    var inpItem = document.getElementById('entryItem_' + sIdx);
    if (inpItem) {
      var wrapSpecs = document.getElementById('entrySpecsWrap_' + sIdx);
      pendingEntries[sIdx] = {
        item: inpItem.value || '',
        specs: (document.getElementById('entrySpecs_' + sIdx) || {}).value || '',
        specsDisplay: wrapSpecs ? wrapSpecs.style.display : 'none',
        unit: (document.getElementById('entryUnit_' + sIdx) || {}).value || 'S.ft',
        qty: (document.getElementById('entryQty_' + sIdx) || {}).value || '1',
        rate: (document.getElementById('entryRate_' + sIdx) || {}).value || ''
      };
    }
  });
}

function restorePendingEntries() {
  Object.keys(pendingEntries).forEach(function(sIdxStr) {
    var sIdx = parseInt(sIdxStr, 10);
    var p = pendingEntries[sIdx];
    if (!p) return;
    var inpItem = document.getElementById('entryItem_' + sIdx);
    var inpSpecs = document.getElementById('entrySpecs_' + sIdx);
    var wrapSpecs = document.getElementById('entrySpecsWrap_' + sIdx);
    var inpUnit = document.getElementById('entryUnit_' + sIdx);
    var inpQty = document.getElementById('entryQty_' + sIdx);
    var inpRate = document.getElementById('entryRate_' + sIdx);

    if (inpItem && p.item) inpItem.value = p.item;
    if (inpSpecs && p.specs) inpSpecs.value = p.specs;
    if (wrapSpecs && p.specsDisplay) wrapSpecs.style.display = p.specsDisplay;
    if (inpUnit && p.unit) inpUnit.value = p.unit;
    if (inpQty && p.qty) inpQty.value = p.qty;
    if (inpRate && p.rate) inpRate.value = p.rate;
    if (p.qty && p.rate) onEntryCalcLive(sIdx);
  });
}

function autoCommitPendingEntries() {
  if (!estimateData.sections) return;
  estimateData.sections.forEach(function(sec, sIdx) {
    var inpItem = document.getElementById('entryItem_' + sIdx);
    var itemName = inpItem ? inpItem.value.trim() : '';
    if (itemName) {
      var inpSpecs = document.getElementById('entrySpecs_' + sIdx);
      var specs = (inpSpecs && inpSpecs.value.trim()) ? inpSpecs.value.trim() : itemName;
      var unit = (document.getElementById('entryUnit_' + sIdx) || {}).value || 'S.ft';
      var qty = Number((document.getElementById('entryQty_' + sIdx) || {}).value || 1);
      var rate = Number((document.getElementById('entryRate_' + sIdx) || {}).value || 0);
      var amt = Math.round(qty * rate);

      sec.items.push({
        id: 0,
        description: specs,
        unit: unit,
        quantity: qty,
        unit_price: rate,
        amount: amt
      });

      inpItem.value = '';
      if (inpSpecs) inpSpecs.value = '';
      var wrapSpecs = document.getElementById('entrySpecsWrap_' + sIdx);
      if (wrapSpecs) wrapSpecs.style.display = 'none';
      var inpRate = document.getElementById('entryRate_' + sIdx);
      if (inpRate) inpRate.value = '';
    }
  });
}

// 4. RENDER ALL SECTIONS (DYNAMIC ACTIVE HEIGHT & SCROLLABLE ITEMS)
function renderAllSections() {
  var container = document.getElementById('sectionsContainer');
  if (!container) return;

  var grandTotal = 0;
  var html = '';

  if (activeSectionIdx >= estimateData.sections.length) {
    activeSectionIdx = Math.max(0, estimateData.sections.length - 1);
  }

  estimateData.sections.forEach(function(sec, sIdx) {
    var secSubtotal = 0;
    var isActive = (sIdx === activeSectionIdx);
    var hasItems = sec.items && sec.items.length > 0;
    var needsScroll = sec.items && sec.items.length > 2;

    // Render existing item rows
    var itemRowsHtml = '';
    (sec.items || []).forEach(function(it, itIdx) {
      var amt = Number(it.quantity || 0) * Number(it.unit_price || 0);
      secSubtotal += amt;

      itemRowsHtml += 
        '<tr class="row-item" style="transition:background 0.1s;">' +
          '<td style="text-align:center;font-weight:700;color:#94A3B8;padding-top:14px;">' +
            (itIdx + 1) +
          '</td>' +
          '<td>' +
            '<textarea class="meta-input" style="min-height:46px;line-height:1.4;resize:vertical;" onfocus="setActiveSection(' + sIdx + ')" oninput="updateLineItem(' + sIdx + ', ' + itIdx + ', \'description\', this.value)" placeholder="Description & specification...">' + esc(it.description) + '</textarea>' +
          '</td>' +
          '<td style="text-align:center;">' +
            '<input type="text" class="meta-input" style="text-align:center;font-weight:600;padding:6px;" value="' + esc(it.unit) + '" onfocus="setActiveSection(' + sIdx + ')" oninput="updateLineItem(' + sIdx + ', ' + itIdx + ', \'unit\', this.value)">' +
          '</td>' +
          '<td style="text-align:right;">' +
            '<input type="number" step="any" class="meta-input" style="text-align:right;font-weight:700;padding:6px;" value="' + it.quantity + '" onfocus="setActiveSection(' + sIdx + ')" oninput="updateLineItem(' + sIdx + ', ' + itIdx + ', \'quantity\', this.value)">' +
          '</td>' +
          '<td style="text-align:right;">' +
            '<input type="number" step="any" class="meta-input" style="text-align:right;font-weight:600;padding:6px;" value="' + it.unit_price + '" onfocus="setActiveSection(' + sIdx + ')" oninput="updateLineItem(' + sIdx + ', ' + itIdx + ', \'unit_price\', this.value)">' +
          '</td>' +
          '<td style="text-align:right;font-weight:800;color:#0F172A;padding-top:14px;white-space:nowrap;" id="itemAmtDisplay_' + sIdx + '_' + itIdx + '">' +
            '৳ ' + Math.round(amt).toLocaleString('en-IN') +
          '</td>' +
          '<td style="text-align:center;padding-top:12px;">' +
            '<button type="button" onclick="deleteLineItem(' + sIdx + ', ' + itIdx + ')" title="Delete Line" style="background:none;border:none;cursor:pointer;font-size:16px;color:#EF4444;">' +
              '🗑️' +
            '</button>' +
          '</td>' +
        '</tr>';
    });

    grandTotal += secSubtotal;

    // Build Category options
    var catOptions = availableCategories.map(function(c) {
      return '<option value="' + escAttr(c) + '" ' + (c.toLowerCase() === (sec.name || '').toLowerCase() ? 'selected':'') + '>' + esc(c) + '</option>';
    }).join('');

    var letter = String.fromCharCode(65 + sIdx);

    html += 
      '<div class="builder-section-card' + (isActive ? ' is-active' : '') + '" id="sectionCard_' + sIdx + '" onclick="onSectionCardClick(event, ' + sIdx + ')">' +
        '<!-- Section Header -->' +
        '<div class="section-card-header">' +
          '<div class="section-header-left">' +
            '<div class="sec-letter-badge">' + letter + '</div>' +
            '<div class="sec-title-labels">' +
              '<span class="sec-main-lbl">SECTION ' + letter + ':</span>' +
              '<span class="sec-sub-lbl">' + letter + ':</span>' +
            '</div>' +
            '<select class="sec-cat-select" onchange="changeSectionCategory(' + sIdx + ', this.value)" onfocus="setActiveSection(' + sIdx + ')">' +
              catOptions +
            '</select>' +
            '<button type="button" class="sec-new-cat-btn" onclick="openCreateCategoryModalInsideBuilder(' + sIdx + ')" title="Add new custom category">' +
              '+ New Cat' +
            '</button>' +
            '<div class="action-menu-wrap sec-mobile-menu">' +
              '<button type="button" class="btn-dots" onclick="toggleActionMenu(event, \'sec_' + sIdx + '\')" title="Section Options">⋮</button>' +
              '<div class="action-dropdown" id="actionMenu_sec_' + sIdx + '">' +
                (sIdx > 0 ? '<button type="button" onclick="moveSectionUp(event, ' + sIdx + ')">▲ Move Section Up</button>' : '') +
                (sIdx < estimateData.sections.length - 1 ? '<button type="button" onclick="moveSectionDown(event, ' + sIdx + ')">▼ Move Section Down</button>' : '') +
                '<button type="button" onclick="removeSection(' + sIdx + ')" style="color:#EF4444;">🗑️ Delete Section</button>' +
              '</div>' +
            '</div>' +
            (isActive 
              ? '<span class="sec-active-indicator" style="background:#FEE2E2;color:#B5182E;font-size:11px;font-weight:700;padding:4px 10px;border-radius:12px;display:inline-flex;align-items:center;gap:6px;"><span style="width:7px;height:7px;border-radius:50%;background:#B5182E;animation:pulse 1.5s infinite;"></span> Active Section (কাজ চলমান)</span>' 
              : '<button type="button" onclick="setActiveSection(' + sIdx + ')" style="background:#F1F5F9;border:1px solid #CBD5E1;color:#475569;font-size:11px;font-weight:700;padding:4px 10px;border-radius:12px;cursor:pointer;display:inline-flex;align-items:center;gap:4px;">🔍 Click to Edit (ক্লিক করে বড় করুন)</button>'
            ) +
          '</div>' +

          '<div class="section-header-right">' +
            '<div class="sec-subtotal-text">' +
              'Section Subtotal: <strong id="secSubtotalDisplay_' + sIdx + '">৳ ' + Math.round(secSubtotal).toLocaleString('en-IN') + '</strong>' +
            '</div>' +
            '<div class="sec-reorder-group" style="display:inline-flex;align-items:center;gap:4px;margin-right:4px;">' +
              (sIdx > 0 ? '<button type="button" class="sec-move-btn" onclick="moveSectionUp(event, ' + sIdx + ')" title="Move Section Up (সেকশন উপরে নিন)">▲</button>' : '') +
              (sIdx < estimateData.sections.length - 1 ? '<button type="button" class="sec-move-btn" onclick="moveSectionDown(event, ' + sIdx + ')" title="Move Section Down (সেকশন নিচে নিন)">▼</button>' : '') +
            '</div>' +
            '<button type="button" class="sec-delete-btn" onclick="removeSection(' + sIdx + ')" title="Delete section">' +
              '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>' +
              'Delete Section' +
            '</button>' +
          '</div>' +
        '</div>' +

        '<!-- Existing Items Container (shows 2 rows, scrolls if >2) -->' +
        '<div class="sec-items-scroll-wrap" style="' + (needsScroll ? 'max-height: 165px; overflow-y: auto; overflow-x: hidden; border-bottom: 1px solid #E2E8F0;' : (hasItems ? 'border-bottom: 1px solid #F1F5F9;' : '')) + '">' +
          '<table class="sec-table" style="width:100%;table-layout:fixed;border-collapse:collapse;">' +
            '<colgroup>' +
              '<col style="width:40px;">' +
              '<col>' +
              '<col style="width:85px;">' +
              '<col style="width:110px;">' +
              '<col style="width:120px;">' +
              '<col style="width:130px;">' +
              '<col style="width:80px;">' +
            '</colgroup>' +
            '<thead>' +
              '<tr>' +
                '<th style="text-align:center;">#</th>' +
                '<th>ITEM DESCRIPTION &amp; TECHNICAL SPECIFICATION</th>' +
                '<th style="text-align:center;">UNIT</th>' +
                '<th style="text-align:right;">QUANTITY</th>' +
                '<th style="text-align:right;">RATE (TK)</th>' +
                '<th style="text-align:right;">TOTAL AMOUNT</th>' +
                '<th style="text-align:center;">ACTION</th>' +
              '</tr>' +
            '</thead>' +
            '<tbody id="secTableBody_' + sIdx + '">' +
              (hasItems ? itemRowsHtml : '<tr><td colspan="7" style="text-align:center;padding:16px;color:#94A3B8;font-size:12px;font-style:italic;">No items added yet in this section. Use the fast entry bar below to add items.</td></tr>') +
            '</tbody>' +
          '</table>' +
        '</div>' +

        '<!-- Fast Inline Entry Row (Unclipped & Anchored) -->' +
        '<div class="sec-fast-entry-wrap">' +
          '<table style="width:100%;table-layout:fixed;border-collapse:collapse;">' +
            '<colgroup>' +
              '<col style="width:40px;">' +
              '<col>' +
              '<col style="width:85px;">' +
              '<col style="width:110px;">' +
              '<col style="width:120px;">' +
              '<col style="width:130px;">' +
              '<col style="width:80px;">' +
            '</colgroup>' +
            '<tbody>' +
              '<tr class="inline-entry-row" style="background:transparent;">' +
                '<td style="text-align:center;font-weight:800;color:#B5182E;font-size:18px;vertical-align:middle;">' +
                  '+' +
                '</td>' +
                '<td style="vertical-align:top;padding:10px 14px;">' +
                  '<div class="typeahead-wrap">' +
                    '<input type="text" id="entryItem_' + sIdx + '" class="entry-item-input" placeholder="Type item name to select from \'' + escAttr(sec.name) + '\'..." autocomplete="off" oninput="onEntryTypeaheadInput(' + sIdx + ')" onfocus="setActiveSection(' + sIdx + '); onEntryTypeaheadFocus(' + sIdx + ')" onkeydown="onEntryKeyDown(event, ' + sIdx + ')">' +
                    '<div class="typeahead-dropdown" id="typeaheadDrop_' + sIdx + '"></div>' +
                  '</div>' +
                  '<div id="entrySpecsWrap_' + sIdx + '" style="margin-top:6px;display:none;">' +
                    '<textarea id="entrySpecs_' + sIdx + '" class="meta-input" rows="2" placeholder="Full specification (editable)..." style="font-size:12px;"></textarea>' +
                  '</div>' +
                '</td>' +
                '<td style="text-align:center;vertical-align:top;padding:10px 8px;">' +
                  '<select id="entryUnit_' + sIdx + '" class="entry-unit-select" onfocus="setActiveSection(' + sIdx + ')">' +
                    '<option value="S.ft">S.ft</option>' +
                    '<option value="nos">nos</option>' +
                    '<option value="per.">per.</option>' +
                    '<option value="LS">LS</option>' +
                    '<option value="job">job</option>' +
                    '<option value="RFT">RFT</option>' +
                    '<option value="mtr.">mtr.</option>' +
                    '<option value="coil">coil</option>' +
                    '<option value="set">set</option>' +
                  '</select>' +
                '</td>' +
                '<td style="text-align:right;vertical-align:top;padding:10px 8px;">' +
                  '<input type="number" step="any" id="entryQty_' + sIdx + '" class="entry-num-input" style="width:100%;box-sizing:border-box;" placeholder="Qty" value="1" onfocus="setActiveSection(' + sIdx + ')" oninput="onEntryCalcLive(' + sIdx + ')" onkeydown="if(event.key===\'Enter\') addEntryLineToSection(' + sIdx + ')">' +
                '</td>' +
                '<td style="text-align:right;vertical-align:top;padding:10px 8px;">' +
                  '<input type="number" step="any" id="entryRate_' + sIdx + '" class="entry-num-input" style="width:100%;box-sizing:border-box;color:#B5182E;" placeholder="Rate" onfocus="setActiveSection(' + sIdx + ')" oninput="onEntryCalcLive(' + sIdx + ')" onkeydown="if(event.key===\'Enter\') addEntryLineToSection(' + sIdx + ')">' +
                '</td>' +
                '<td style="text-align:right;vertical-align:top;font-weight:800;color:#0F172A;padding:18px 8px 10px 8px;white-space:nowrap;" id="entryLiveTotal_' + sIdx + '">' +
                  '৳ 0.00' +
                '</td>' +
                '<td style="text-align:center;vertical-align:top;padding:14px 8px 10px 8px;">' +
                  '<button type="button" class="btn-add-item-pill" onclick="addEntryLineToSection(' + sIdx + ')" title="Add this line">' +
                    '+ Add' +
                  '</button>' +
                '</td>' +
              '</tr>' +
            '</tbody>' +
          '</table>' +
        '</div>' +
      '</div>';
  });

  container.innerHTML = html;
  recalcGrandTotal();
}

// 5. TYPEAHEAD & AUTOCOMPLETE
function onEntryTypeaheadFocus(sIdx) {
  var inp = document.getElementById('entryItem_' + sIdx);
  showTypeaheadSuggestions(sIdx, inp ? inp.value.trim() : '');
}

function onEntryTypeaheadInput(sIdx) {
  var inp = document.getElementById('entryItem_' + sIdx);
  var text = inp ? inp.value.trim() : '';
  showTypeaheadSuggestions(sIdx, text);
  var specs = document.getElementById('entrySpecs_' + sIdx);
  if (specs) specs.value = text;
}

function showTypeaheadSuggestions(sIdx, query) {
  var sec = estimateData.sections[sIdx];
  if (!sec) return;

  var dropdown = document.getElementById('typeaheadDrop_' + sIdx);
  if (!dropdown) return;

  var currentCat = (sec.name || '').trim().toLowerCase();

  // Primary: Match items belonging strictly to this section's category
  var matched = allCatalogItems.filter(function(i) {
    return (i.category || '').trim().toLowerCase() === currentCat;
  });

  // If search query is typed, filter within the category
  if (query) {
    var lq = query.toLowerCase();
    matched = matched.filter(function(i) {
      return (i.item_name && i.item_name.toLowerCase().includes(lq)) || 
             (i.specifications && i.specifications.toLowerCase().includes(lq));
    });
  }

  // Fallback: If query typed and no match in this category, search all categories
  if (!matched.length && query) {
    var lqAll = query.toLowerCase();
    matched = allCatalogItems.filter(function(i) {
      return (i.item_name && i.item_name.toLowerCase().includes(lqAll)) || 
             (i.specifications && i.specifications.toLowerCase().includes(lqAll));
    });
  }

  if (!matched.length) {
    dropdown.style.display = 'none';
    return;
  }

  renderDropdownHtml(sIdx, matched.slice(0, 10), dropdown);
}

function renderDropdownHtml(sIdx, items, dropdown) {
  dropdown.innerHTML = items.map(function(it) {
    return '<div class="typeahead-item" onclick="selectTypeaheadItem(' + sIdx + ', ' + it.id + ')">' +
      '<div class="typeahead-item-title">' + esc(it.item_name) + '</div>' +
      '<div class="typeahead-item-desc">' + esc(it.specifications ? it.specifications.substring(0, 110) + '...' : '') + '</div>' +
      '<div class="typeahead-item-meta">' +
        '<span>Unit: ' + esc(it.unit) + '</span>' +
        '<strong>৳ ' + Math.round(Number(it.default_rate || 0)).toLocaleString('en-IN') + '</strong>' +
      '</div>' +
    '</div>';
  }).join('');

  dropdown.style.display = 'block';
}

function selectTypeaheadItem(sIdx, itemId) {
  var item = allCatalogItems.find(function(i) { return i.id == itemId; });
  if (!item) return;

  var inpItem = document.getElementById('entryItem_' + sIdx);
  var inpSpecs = document.getElementById('entrySpecs_' + sIdx);
  var wrapSpecs = document.getElementById('entrySpecsWrap_' + sIdx);
  var inpUnit = document.getElementById('entryUnit_' + sIdx);
  var inpRate = document.getElementById('entryRate_' + sIdx);

  if (inpItem) inpItem.value = item.item_name;
  if (inpSpecs) inpSpecs.value = item.specifications || item.item_name;
  if (wrapSpecs) wrapSpecs.style.display = 'block';
  if (inpUnit) inpUnit.value = item.unit || 'S.ft';
  if (inpRate) inpRate.value = item.default_rate || 0;

  var drop = document.getElementById('typeaheadDrop_' + sIdx);
  if (drop) drop.style.display = 'none';

  onEntryCalcLive(sIdx);

  var qtyInput = document.getElementById('entryQty_' + sIdx);
  if (qtyInput) {
    qtyInput.focus();
    qtyInput.select();
  }
}

function onEntryCalcLive(sIdx) {
  var qty = Number((document.getElementById('entryQty_' + sIdx) || {}).value || 0);
  var rate = Number((document.getElementById('entryRate_' + sIdx) || {}).value || 0);
  var total = qty * rate;

  var disp = document.getElementById('entryLiveTotal_' + sIdx);
  if (disp) {
    disp.textContent = '৳ ' + Math.round(total).toLocaleString('en-IN');
  }
}

function onEntryKeyDown(e, sIdx) {
  var dropdown = document.getElementById('typeaheadDrop_' + sIdx);
  if (e.key === 'Enter') {
    e.preventDefault();
    if (dropdown && dropdown.style.display === 'block') {
      var first = dropdown.querySelector('.typeahead-item');
      if (first) {
        first.click();
        return;
      }
    }
    var qtyInp = document.getElementById('entryQty_' + sIdx);
    if (qtyInp) qtyInp.focus();
  } else if (e.key === 'Escape') {
    if (dropdown) dropdown.style.display = 'none';
  }
}

document.addEventListener('click', function(e) {
  if (!e.target.closest('.typeahead-wrap')) {
    document.querySelectorAll('.typeahead-dropdown').forEach(function(d) { d.style.display = 'none'; });
  }
});

// 6. ADD LINE ITEM INTO SECTION
function addEntryLineToSection(sIdx) {
  var inpItem = document.getElementById('entryItem_' + sIdx);
  var itemName = inpItem ? inpItem.value.trim() : '';
  var inpSpecs = document.getElementById('entrySpecs_' + sIdx);
  var specs = (inpSpecs && inpSpecs.value.trim()) ? inpSpecs.value.trim() : itemName;
  var unit = (document.getElementById('entryUnit_' + sIdx) || {}).value || 'S.ft';
  var qty = Number((document.getElementById('entryQty_' + sIdx) || {}).value || 1);
  var rate = Number((document.getElementById('entryRate_' + sIdx) || {}).value || 0);

  if (!itemName) {
    alert('Please enter or select an Item Name (আইটেমের নাম লিখুন বা সিলেক্ট করুন).');
    if (inpItem) inpItem.focus();
    return;
  }

  var amt = Math.round(qty * rate);

  estimateData.sections[sIdx].items.push({
    id: 0,
    description: specs,
    unit: unit,
    quantity: qty,
    unit_price: rate,
    amount: amt
  });

  renderAllSections();
  markDirty();

  setTimeout(function() {
    var inp = document.getElementById('entryItem_' + sIdx);
    if (inp) inp.focus();
    var scrollWrap = document.querySelector('#sectionCard_' + sIdx + ' .sec-items-scroll-wrap');
    if (scrollWrap) {
      scrollWrap.scrollTop = scrollWrap.scrollHeight;
    }
  }, 50);
}

// 7. EDITING & DELETING LINE ITEMS
function updateLineItem(sIdx, itIdx, field, value) {
  var item = estimateData.sections[sIdx].items[itIdx];
  if (!item) return;

  item[field] = (field === 'quantity' || field === 'unit_price') ? Number(value || 0) : value;
  item.amount = Math.round(Number(item.quantity || 0) * Number(item.unit_price || 0));

  var cell = document.getElementById('itemAmtDisplay_' + sIdx + '_' + itIdx);
  if (cell) {
    cell.textContent = '৳ ' + Math.round(item.amount).toLocaleString('en-IN');
  }

  var secSub = 0;
  estimateData.sections[sIdx].items.forEach(function(i) { secSub += Number(i.amount || 0); });
  var subDisp = document.getElementById('secSubtotalDisplay_' + sIdx);
  if (subDisp) {
    subDisp.textContent = '৳ ' + Math.round(secSub).toLocaleString('en-IN');
  }

  recalcGrandTotal();
  markDirty();
}

function deleteLineItem(sIdx, itIdx) {
  estimateData.sections[sIdx].items.splice(itIdx, 1);
  renderAllSections();
  markDirty();
}

function recalcGrandTotal() {
  var total = 0;
  (estimateData.sections || []).forEach(function(sec) {
    (sec.items || []).forEach(function(it) {
      total += Number(it.quantity || 0) * Number(it.unit_price || 0);
    });
  });

  var totalDisp = document.getElementById('summaryGrandTotal');
  if (totalDisp) {
    totalDisp.textContent = '৳ ' + Math.round(total).toLocaleString('en-IN');
  }
  var wordDisp = document.getElementById('summaryInWords');
  if (wordDisp) {
    wordDisp.textContent = 'In Word: ' + numberToWordsBD(total);
  }
}

// 8. SAVE ESTIMATE DATA (AJAX)
async function saveEstimateData(silent) {
  var clientInp = document.getElementById('inpClientName');
  var clientName = clientInp ? clientInp.value.trim() : (estimateData.client_name || '');
  if (!clientName) {
    if (!silent) alert('Please enter Client / Company Name (ক্লায়েন্টের নাম লিখুন).');
    if (clientInp) clientInp.focus();
    return false;
  }

  autoCommitPendingEntries();

  var subjInp = document.getElementById('inpSubject');
  var addrInp = document.getElementById('inpAddress');
  var statInp = document.getElementById('inpStatus');

  var prepByInp = document.getElementById('inpPreparedBy');
  var prepDesigInp = document.getElementById('inpPreparedByDesig');
  var appByInp = document.getElementById('inpApprovedBy');
  var appDesigInp = document.getElementById('inpApprovedByDesig');

  var payload = {
    id: estimateData.id || ESTIMATE_ID,
    client_name: clientName,
    client_company: clientName,
    client_address: addrInp ? addrInp.value.trim() : '',
    subject: (subjInp && subjInp.value.trim()) ? subjInp.value.trim() : ('Interior Decoration Estimate for ' + clientName),
    status: statInp ? statInp.value : 'draft',
    prepared_by: prepByInp ? prepByInp.value.trim() : 'Md. Rukonuzzaman',
    prepared_by_designation: prepDesigInp ? prepDesigInp.value.trim() : 'Interior Designer',
    approved_by: appByInp ? appByInp.value.trim() : 'Md. Mustafizur Rahman',
    approved_by_designation: appDesigInp ? appDesigInp.value.trim() : 'Managing Director',
    sections: estimateData.sections
  };

  try {
    var res = await fetch(BASE_PATH + '/api/estimates.php?action=save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    var d = await res.json();
    if (d.success && d.data) {
      estimateData.id = d.data.id;
      ESTIMATE_ID = d.data.id;

      if (d.data.estimate_no) {
        var noLabel = document.getElementById('estimateNoLabel');
        if (noLabel) noLabel.textContent = d.data.estimate_no;
        var heading = document.getElementById('pageHeadingTitle');
        if (heading) heading.textContent = 'Edit Estimate (' + d.data.estimate_no + ')';
      }

      window.history.replaceState(null, '', BASE_PATH + '/estimate-builder?id=' + d.data.id);
      markClean();

      if (typeof window.showToast === 'function') {
        window.showToast('Estimate saved successfully!', 'success');
      } else if (!silent) {
        alert('Estimate saved successfully!');
      }

      return d.data.id;
    } else {
      if (!silent) alert((d && d.message) ? d.message : 'Save failed.');
      return false;
    }
  } catch(e) {
    console.error(e);
    if (!silent) alert('Network error while saving estimate.');
    return false;
  }
}

async function saveAndGoView() {
  var savedId = await saveEstimateData(true);
  if (savedId) {
    window.location.href = BASE_PATH + '/estimate-view?id=' + savedId;
  }
}

// 9. MODAL HELPERS
function openCustomModal(id) {
  var m = document.getElementById(id);
  if (m) {
    m.classList.add('is-active');
    document.body.style.overflow = 'hidden';
  }
}

function closeCustomModal(id) {
  var m = document.getElementById(id);
  if (m) {
    m.classList.remove('is-active');
    document.body.style.overflow = '';
  }
}

document.addEventListener('click', function(e) {
  if (e.target.classList.contains('custom-modal-overlay')) {
    e.target.classList.remove('is-active');
    document.body.style.overflow = '';
  }
});

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.custom-modal-overlay.is-active').forEach(function(m) {
      m.classList.remove('is-active');
    });
    document.body.style.overflow = '';
  }
});

function numberToWordsBD(num) {
  num = Math.round(Number(num || 0));
  if (num <= 0) return 'Zero Taka Only.';

  var ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
    'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
  var tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

  function convertTwoDigits(n) {
    if (n < 20) return ones[n];
    return tens[Math.floor(n / 10)] + (n % 10 !== 0 ? ' ' + ones[n % 10] : '');
  }

  function convertThreeDigits(n) {
    var str = '';
    if (Math.floor(n / 100) > 0) {
      str += ones[Math.floor(n / 100)] + ' Hundred ';
      n %= 100;
    }
    if (n > 0) {
      str += convertTwoDigits(n);
    }
    return str.trim();
  }

  var crore = Math.floor(num / 10000000);
  num %= 10000000;
  var lakh = Math.floor(num / 100000);
  num %= 100000;
  var thousand = Math.floor(num / 1000);
  num %= 1000;
  var hundred = num;

  var words = '';
  if (crore > 0) words += convertThreeDigits(crore) + ' Crore ';
  if (lakh > 0) words += convertThreeDigits(lakh) + ' Lakh ';
  if (thousand > 0) words += convertThreeDigits(thousand) + ' Thousand ';
  if (hundred > 0) words += convertThreeDigits(hundred) + ' ';

  return words.trim() + ' Taka Only.';
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

function escAttr(str) {
  if (!str) return '';
  return String(str)
    .replace(/\\/g, '\\\\')
    .replace(/'/g, "\\'")
    .replace(/"/g, '&quot;');
}

function toggleActionMenu(e, id) {
  e.stopPropagation();
  var all = document.querySelectorAll('.action-dropdown');
  var target = document.getElementById('actionMenu_' + id);
  if (!target) return;
  var isOpen = target.classList.contains('show');
  all.forEach(function(d) { d.classList.remove('show'); });
  if (!isOpen) {
    target.classList.add('show');
  }
}

document.addEventListener('click', function(e) {
  if (!e.target.closest('.action-dropdown') && !e.target.closest('.btn-dots')) {
    document.querySelectorAll('.action-dropdown').forEach(function(d) { d.classList.remove('show'); });
  }
});

function ensureMobileStickySaveFixed() {
  if (window.innerWidth <= 768) {
    var sb = document.getElementById('builderMobileStickyBottom');
    if (sb && sb.parentElement !== document.body) {
      document.body.appendChild(sb);
    }
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function() {
    ensureMobileStickySaveFixed();
    initBuilder();
  });
} else {
  ensureMobileStickySaveFixed();
  initBuilder();
}
window.addEventListener('resize', ensureMobileStickySaveFixed);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
