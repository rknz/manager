<?php
// views/estimate-view.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$estimateId = (int)($_GET['id'] ?? 0);
if ($estimateId <= 0) {
    header('Location: ' . $basePath . '/estimates');
    exit;
}

$pageTitle = 'Estimate & Proposal View';
$activeNav = 'estimates';
include __DIR__ . '/../includes/header.php';
?>

<style>
@page {
  size: A4 portrait;
  margin: 0;
}

.view-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  background: #fff;
  padding: 14px 20px;
  border-radius: 12px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  margin-bottom: 20px;
}
.tab-pill-bar {
  display: flex;
  gap: 6px;
  background: #F1F5F9;
  padding: 4px;
  border-radius: 8px;
}
.tab-pill {
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  color: #64748B;
  border: none;
  background: transparent;
  cursor: pointer;
  transition: all 0.15s;
}
.tab-pill.active {
  background: #fff;
  color: #9C1F24;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* PAPER SHEETS STYLING (A4 Ready) */
.paper-container {
  max-width: 850px;
  margin: 0 auto 40px;
}
.paper-sheet {
  background: #fff;
  width: 100%;
  min-height: 1050px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  border: 1px solid #E2E8F0;
  border-radius: 4px;
  padding: 40px 48px;
  box-sizing: border-box;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  color: #1E293B;
  position: relative;
  font-variant-numeric: tabular-nums;
}

/* PROPOSAL LETTER COVER STYLES */
.letterhead-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 2.5px solid #9C1F24;
  padding-bottom: 12px;
  margin-bottom: 20px;
}
.letterhead-logo-wrap {
  display: flex;
  align-items: center;
  gap: 12px;
}
.letterhead-brand h1 {
  font-size: 26px;
  font-weight: 900;
  color: #9C1F24;
  letter-spacing: 0.5px;
  margin: 0;
  line-height: 1.1;
}
.letterhead-tagline {
  font-size: 12px;
  color: #E11D48;
  font-style: italic;
  font-weight: 600;
  margin-top: 2px;
}
.letterhead-contact {
  text-align: right;
  font-size: 11px;
  color: #475569;
  line-height: 1.5;
}
.proposal-title {
  text-align: center;
  font-size: 18px;
  font-weight: 800;
  text-decoration: underline;
  text-underline-offset: 4px;
  color: #0F172A;
  margin: 20px 0 24px;
  letter-spacing: 1px;
}
.proposal-meta-grid {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  margin-bottom: 16px;
  line-height: 1.5;
}
.proposal-subject {
  font-size: 13px;
  font-weight: 700;
  color: #0F172A;
  margin: 14px 0 16px;
  line-height: 1.4;
}
.proposal-body-text {
  font-size: 13px;
  line-height: 1.6;
  color: #334155;
  margin-bottom: 18px;
}
.summary-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 18px;
  font-size: 13px;
}
.summary-table th, .summary-table td {
  border: 1px solid #94A3B8;
  padding: 8px 12px;
}
.summary-table th {
  background: #F1F5F9;
  font-weight: 700;
  color: #0F172A;
}
.installment-table {
  width: 100%;
  border-collapse: collapse;
  margin: 12px 0 18px;
  font-size: 12px;
}
.installment-table th, .installment-table td {
  border: 1px solid #94A3B8;
  padding: 6px 10px;
}
.installment-table th {
  background: #F8FAFC;
  width: 160px;
  font-weight: 700;
}
.signature-row {
  display: flex;
  justify-content: space-between;
  margin-top: 40px;
  padding-top: 20px;
}
.sign-box {
  width: 250px;
  text-align: center;
  position: relative;
}
.sign-title {
  font-weight: 800;
  font-size: 13px;
  color: #0F172A;
}
.sign-sub {
  font-size: 11px;
  color: #64748B;
}
.letterhead-bottom-banner {
  margin-top: 40px;
  border-top: 1.5px solid #E2E8F0;
  padding-top: 8px;
  text-align: center;
  font-size: 11px;
  font-weight: 600;
  color: #9C1F24;
  letter-spacing: 0.5px;
}

/* Multi-page BOQ Container and Sheet Cards */
.boq-pages-container {
  display: flex;
  flex-direction: column;
  gap: 32px;
}
.boq-page-wrapper {
  position: relative;
  width: 100%;
}
.boq-page-tag {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 11.5px;
  font-weight: 700;
  color: #64748B;
  background: #F8FAFC;
  border: 1px solid #CBD5E1;
  padding: 4px 14px;
  border-radius: 6px 6px 0 0;
  margin-bottom: -1px;
}
.boq-page-sheet {
  min-height: 1050px;
  position: relative;
}

/* DETAILED BOQ TABLE STYLES */
.boq-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 11.5px;
  table-layout: fixed;
  margin-bottom: 12px;
}
.boq-table th, .boq-table td {
  border: 1px solid #64748B;
  padding: 5px 7px;
  vertical-align: top;
  box-sizing: border-box;
}
.boq-table th {
  background: #E2E8F0;
  font-weight: 800;
  color: #0F172A;
  text-align: center;
  font-size: 11px;
  letter-spacing: 0.3px;
}
.boq-section-header {
  background: #F1F5F9;
  font-weight: 900;
  font-size: 12px;
  color: #0F172A;
  letter-spacing: 0.5px;
}
.boq-subtotal-row {
  background: #F8FAFC;
  font-weight: 800;
  font-size: 11.5px;
}

/* DEFAULT BASE PRINT STYLES */
@media print {
  html, body {
    background: #ffffff !important;
    margin: 0 !important;
    padding: 0 !important;
    height: auto !important;
    min-height: 0 !important;
    max-height: none !important;
    overflow: visible !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  .sidebar, .topbar, .view-topbar, .bottom-nav, .right-panel, .hide-on-print,
  .mobile-greeting-bar, #mainSidebar, .topbar-greeting, header.topbar,
  #sidebarOverlay, .sidebar-bottom-bar, #padModeNotice, .boq-page-tag,
  .app-footer, .filter-bar, .modal-overlay, #toastContainer {
    display: none !important;
  }
  .app-layout, .main-wrapper, .main-content {
    display: block !important;
    position: static !important;
    width: 100% !important;
    height: auto !important;
    min-height: 0 !important;
    max-height: none !important;
    overflow: visible !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    box-shadow: none !important;
    float: none !important;
  }
  .paper-container {
    display: block !important;
    position: static !important;
    max-width: 100% !important;
    width: 100% !important;
    height: auto !important;
    min-height: 0 !important;
    max-height: none !important;
    overflow: visible !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    box-shadow: none !important;
  }
  .boq-pages-container {
    display: block !important;
    position: static !important;
    width: 100% !important;
    height: auto !important;
    min-height: 0 !important;
    max-height: none !important;
    overflow: visible !important;
    gap: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
  }
  .boq-page-wrapper {
    display: block !important;
    position: relative !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    page-break-after: always !important;
    break-after: page !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }
  .boq-page-wrapper:last-child {
    page-break-after: auto !important;
    break-after: auto !important;
  }
  .paper-sheet {
    box-shadow: none !important;
    border: none !important;
    border-radius: 0 !important;
    margin: 0 !important;
    width: 100% !important;
    min-height: 297mm !important;
    box-sizing: border-box !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  /* PRINT DISPLAY MODES */
  /* Default: active tab */
  #tabProposalPaper {
    display: block !important;
    page-break-after: auto !important;
    break-after: auto !important;
  }
  #tabBoqPaper {
    display: none !important;
  }

  /* Proposal only */
  body.print-proposal #tabProposalPaper {
    display: block !important;
    page-break-after: auto !important;
    break-after: auto !important;
  }
  body.print-proposal #tabBoqPaper {
    display: none !important;
  }

  /* All BOQ pages */
  body.print-boq #tabProposalPaper {
    display: none !important;
  }
  body.print-boq #tabBoqPaper {
    display: block !important;
  }
  body.print-boq .boq-page-wrapper {
    display: block !important;
    page-break-after: always !important;
    break-after: page !important;
  }
  body.print-boq .boq-page-wrapper:last-child {
    page-break-after: auto !important;
    break-after: auto !important;
  }

  /* Both Sheets (Proposal first, followed by all BOQ pages) */
  body.print-both #tabProposalPaper {
    display: block !important;
    page-break-after: always !important;
    break-after: page !important;
  }
  body.print-both #tabBoqPaper {
    display: block !important;
  }
  body.print-both .boq-page-wrapper {
    display: block !important;
    page-break-after: always !important;
    break-after: page !important;
  }
  body.print-both .boq-page-wrapper:last-child {
    page-break-after: auto !important;
    break-after: auto !important;
  }

  /* Single individual BOQ page */
  body.print-single-page #tabProposalPaper {
    display: none !important;
  }
  body.print-single-page #tabBoqPaper {
    display: block !important;
  }
  body.print-single-page .boq-page-wrapper {
    display: none !important;
  }
  body.print-single-page .boq-page-wrapper.print-active-single {
    display: block !important;
    page-break-after: auto !important;
    break-after: auto !important;
  }

  tr {
    break-inside: avoid !important;
    page-break-inside: avoid !important;
  }
  .signature-row {
    break-inside: avoid !important;
    page-break-inside: avoid !important;
  }
}
</style>
<style id="pageRules"></style>

<!-- ACTION & TOPBAR -->
<div class="view-topbar hide-on-print">
  <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
    <a href="<?= $basePath ?>/estimates" class="btn btn-sm btn-outline-secondary" data-no-pjax style="display:inline-flex;align-items:center;gap:6px;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      All Estimates
    </a>
    <a href="<?= $basePath ?>/estimate-builder?id=<?= $estimateId ?>" class="btn btn-sm btn-outline-primary" data-no-pjax style="display:inline-flex;align-items:center;gap:6px;">
      ✏️ Edit in Builder
    </a>
    <div class="tab-pill-bar">
      <button type="button" class="tab-pill active" id="pillProposal" onclick="switchTab('proposal')">
        📑 1. Proposal Cover (কভার পেপার)
      </button>
      <button type="button" class="tab-pill" id="pillBoq" onclick="switchTab('boq')">
        📊 2. Detailed BOQ Sheet (আইটেম শিট)
      </button>
    </div>
  </div>

  <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
    <!-- Pre-printed Pad Mode & Margins Controls -->
    <div class="pad-controls-wrap" style="display:inline-flex;align-items:center;gap:8px;background:#F8FAFC;border:1.5px solid #CBD5E1;padding:5px 12px;border-radius:8px;">
      <label style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:700;color:#0F172A;cursor:pointer;margin:0;user-select:none;" title="Enable for official Lily Interiors pre-printed pad paper">
        <input type="checkbox" id="chkPadMode" checked onchange="togglePadMode()" style="accent-color:#9C1F24;cursor:pointer;width:16px;height:16px;">
        <span>📄 Pre-printed Pad (প্যাড মোড)</span>
      </label>

      <div id="marginInputsWrap" style="display:flex;align-items:center;gap:6px;padding-left:10px;border-left:1.5px solid #E2E8F0;">
        <label style="font-size:11px;font-weight:600;color:#475569;margin:0;display:flex;align-items:center;gap:3px;" title="Top margin in inches (space for Lily Interiors letterhead logo banner)">
          Top:
          <input type="number" id="marginTop" value="2.17" step="0.01" style="width:55px;padding:3px 4px;border:1px solid #CBD5E1;border-radius:4px;font-size:11px;font-weight:700;color:#0F172A;text-align:center;" oninput="onMarginChange()">
          <span style="font-size:10px;color:#94A3B8;">in</span>
        </label>
        <label style="font-size:11px;font-weight:600;color:#475569;margin:0;display:flex;align-items:center;gap:3px;" title="Bottom margin in inches (space for Lily Interiors footer banner)">
          Btm:
          <input type="number" id="marginBottom" value="1.00" step="0.01" style="width:55px;padding:3px 4px;border:1px solid #CBD5E1;border-radius:4px;font-size:11px;font-weight:700;color:#0F172A;text-align:center;" oninput="onMarginChange()">
          <span style="font-size:10px;color:#94A3B8;">in</span>
        </label>
      </div>
    </div>

    <!-- Print Action Buttons -->
    <div style="display:inline-flex;align-items:center;gap:6px;">
      <!-- Dynamic Individual BOQ Page Print Shortcuts (visible when on BOQ tab) -->
      <span id="boqPageButtonsBar" style="display:none;align-items:center;gap:4px;"></span>

      <button type="button" class="btn btn-sm btn-primary" id="btnPrintCurrent" onclick="printCurrentTab()" style="display:inline-flex;align-items:center;gap:6px;background:#9C1F24;border-color:#9C1F24;font-weight:700;padding:7px 16px;box-shadow:0 2px 6px rgba(156,31,36,0.25);">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        <span id="btnPrintText">Print Proposal Cover</span>
      </button>

      <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printBothSheets()" title="Print Full Book (Both Sheets Together)" style="display:inline-flex;align-items:center;gap:4px;font-size:12px;padding:7px 12px;border-color:#CBD5E1;">
        <span>📑 Both</span>
      </button>
    </div>
  </div>
</div>

<!-- PAD MODE INFO NOTICE -->
<div id="padModeNotice" class="hide-on-print" style="max-width:850px;margin:0 auto 16px;padding:9px 16px;background:#FFF1F2;border:1px dashed #E11D48;border-radius:8px;display:flex;align-items:center;justify-content:space-between;color:#9F1239;font-size:12px;font-weight:600;">
  <div style="display:flex;align-items:center;gap:8px;">
    <span>📄</span>
    <span><strong>Pre-printed Pad Mode Active:</strong> 2.17" top &amp; 1.00" bottom margins reserved for Lily Interiors letterhead pad. Digital header &amp; footer are suppressed during printing.</span>
  </div>
  <button type="button" onclick="setPadMode(false)" style="background:none;border:none;color:#BE123C;font-size:11px;font-weight:700;text-decoration:underline;cursor:pointer;">
    Switch to Plain Paper
  </button>
</div>

<!-- PAPER SHEETS WRAPPER -->
<div class="paper-container">
  
  <!-- TAB 1: OFFICIAL PROPOSAL & AGREEMENT LETTER -->
  <div id="tabProposalPaper" class="paper-sheet">
    <!-- Header Letterhead -->
    <div class="letterhead-top">
      <div class="letterhead-logo-wrap">
        <div style="width:48px;height:48px;background:#9C1F24;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900;font-size:22px;">
          L
        </div>
        <div class="letterhead-brand">
          <h1>LILY INTERIORS</h1>
          <div class="letterhead-tagline">in the touch of modernity</div>
        </div>
      </div>
      <div class="letterhead-contact">
        <div>📍 36, Bir Uttam C. R. Dutta Road (3rd Floor), Hatirpool, Dhaka-1205</div>
        <div>📞 +88 02 44612456, +88 01734 182694</div>
        <div>✉️ info@lilyinteriorsbd.com | 🌐 www.lilyinteriorsbd.com</div>
      </div>
    </div>

    <!-- Title -->
    <div class="proposal-title">PROPOSAL LETTER</div>

    <!-- Meta / Addressee -->
    <div class="proposal-meta-grid">
      <div>
        <strong>To,</strong><br>
        <strong id="viewClientCompany" style="font-size:14px;color:#0F172A;">-</strong><br>
        <span id="viewClientDesignation">-</span><br>
        <span id="viewClientAddress">-</span><br>
        <span id="viewClientPhone" style="color:#64748B;">-</span>
      </div>
      <div style="text-align:right;">
        <strong>Date:</strong> <span id="viewEstDate"><?= date('d/m/Y') ?></span><br>
        <strong>Estimate No:</strong> <span id="viewEstNo" style="color:#9C1F24;font-weight:700;">-</span>
      </div>
    </div>

    <!-- Subject -->
    <div class="proposal-subject">
      <span>SUBJECT: </span><span id="viewEstSubject">-</span>
    </div>

    <!-- Body Salutation -->
    <div class="proposal-body-text">
      It's our pleasure to enclose herewith your offer for the above mentioned <strong>Estimated Amount</strong> equipment for your valued consideration along with the necessary enclosure as stated below.
    </div>

    <!-- Summary of Works Table -->
    <table class="summary-table">
      <thead>
        <tr>
          <th style="width:60px;text-align:center;">SL</th>
          <th>Description of works</th>
          <th style="width:160px;text-align:right;">Amount (TK)</th>
        </tr>
      </thead>
      <tbody id="viewSummaryTableBody">
        <tr><td colspan="3" style="text-align:center;">Loading summary...</td></tr>
      </tbody>
      <tfoot>
        <tr style="background:#E2E8F0;font-weight:900;font-size:13px;border-top:1.5px solid #64748B;">
          <td colspan="2" style="text-align:right;padding:8px 12px;font-weight:900;color:#0F172A;">Total Amount:</td>
          <td style="text-align:right;padding:8px 12px;font-weight:900;color:#0F172A;font-size:13px;" id="viewSummaryGrandTotal">৳ 0</td>
        </tr>
      </tfoot>
    </table>

    <!-- In Words -->
    <div style="font-size:13px;font-weight:700;margin-bottom:18px;">
      <u>In word:</u> <span id="viewInWords">-</span>
    </div>

    <!-- Note & Conditions -->
    <div style="font-size:12px;margin-bottom:16px;">
      <strong style="text-decoration:underline;">Note:</strong>
      <div id="viewNotesBody" style="margin-top:4px;line-height:1.6;white-space:pre-line;color:#334155;">
        1. Lily Interiors will not be responsible for any unauthorized damages.
        2. We will try to finish the decoration works within scheduled working days.
        3. If the stipulated amount is not paid within the scheduled time, the work will be stopped.
      </div>
    </div>

    <!-- Payment Mood / Schedule -->
    <div style="font-size:12px;margin-bottom:24px;">
      <strong style="text-decoration:underline;">Payment Mood:</strong>
      <table class="installment-table">
        <tr>
          <th>1<sup>ST</sup> INSTALLMENT</th>
          <td><strong id="viewAdvPct">60%</strong> will be paid Advance with Work Order.</td>
        </tr>
        <tr>
          <th>2<sup>ND</sup> INSTALLMENT</th>
          <td><strong id="viewRunPct">30%</strong> will be paid within (80%) of Work.</td>
        </tr>
        <tr>
          <th>3<sup>RD</sup> INSTALLMENT</th>
          <td>Rest <strong id="viewFinPct">10%</strong> will be paid within 07 days from the Complete date of Work.</td>
        </tr>
      </table>
    </div>

    <!-- Closing Courtesy -->
    <div style="font-size:12px;line-height:1.5;color:#475569;margin-bottom:30px;">
      We look forward to learning more about your vision for your dream project, and working together to create something you'll absolutely love. We are always ready to attend your query.
    </div>

    <!-- Signatures -->
    <div class="signature-row">
      <div class="sign-box" style="text-align:left;">
        <div style="height:48px;"></div>
        <div class="sign-title" id="proposalAuthorizedName">MD. MUSTAFIZUR RAHMAN</div>
        <div class="sign-sub" id="proposalAuthorizedDesig">Managing Director</div>
        <div style="font-size:11px;font-weight:700;color:#9C1F24;margin-top:2px;">LILY INTERIORS</div>
      </div>

      <div class="sign-box" style="text-align:right;">
        <div style="height:48px;"></div>
        <div class="sign-title" id="viewClientSignTitle">CLIENT SIGNATURE</div>
        <div class="sign-sub" id="viewClientSignSub">Authorized Signatory</div>
      </div>
    </div>

    <!-- Bottom Banner -->
    <div class="letterhead-bottom-banner">
      All kinds of Interior Solutions in Residential and Commercial Space
    </div>
  </div>

  <!-- TAB 2: DETAILED BOQ SHEET (Multi-Page Paginated A4 Sheets) -->
  <div id="tabBoqPaper" class="boq-pages-container" style="display:none;">
    <!-- Rendered dynamically into multiple discrete A4 pages by renderBoqPaper(data) -->
    <div style="text-align:center;padding:40px;color:#64748B;font-weight:600;">
      Loading detailed BOQ pages...
    </div>
  </div>

</div>

<script>
const ESTIMATE_ID = <?= $estimateId ?>;
let estimateData = null;

let currentTab = 'proposal';
let boqTotalPages = 1;
let marginDebounceTimer = null;

function onMarginChange() {
  clearTimeout(marginDebounceTimer);
  marginDebounceTimer = setTimeout(() => {
    updatePrintRules();
    if (estimateData) {
      renderBoqPaper(estimateData);
    }
  }, 300);
}

function switchTab(tab) {
  currentTab = tab;
  document.getElementById('pillProposal').classList.toggle('active', tab === 'proposal');
  document.getElementById('pillBoq').classList.toggle('active', tab === 'boq');
  document.getElementById('tabProposalPaper').style.display = (tab === 'proposal') ? 'block' : 'none';
  document.getElementById('tabBoqPaper').style.display = (tab === 'boq') ? 'block' : 'none';

  const printText = document.getElementById('btnPrintText');
  const boqButtonsBar = document.getElementById('boqPageButtonsBar');

  if (tab === 'proposal') {
    if (printText) printText.textContent = 'Print Proposal Cover';
    if (boqButtonsBar) boqButtonsBar.style.display = 'none';
  } else {
    if (printText) printText.textContent = boqTotalPages > 1 ? `Print All BOQ Pages (${boqTotalPages})` : 'Print BOQ Sheet';
    if (boqButtonsBar) boqButtonsBar.style.display = (boqTotalPages > 1) ? 'inline-flex' : 'none';
  }
}

function updateBoqPageButtons(totalPages) {
  boqTotalPages = totalPages;
  const bar = document.getElementById('boqPageButtonsBar');
  if (!bar) return;
  if (totalPages <= 1) {
    bar.innerHTML = '';
    bar.style.display = 'none';
    const printText = document.getElementById('btnPrintText');
    if (printText && currentTab === 'boq') printText.textContent = 'Print BOQ Sheet';
    return;
  }
  let html = '<span style="font-size:11px;font-weight:700;color:#64748B;margin-right:2px;">Page:</span>';
  for (let i = 0; i < totalPages; i++) {
    html += `
      <button type="button" class="btn btn-xs btn-outline-secondary" onclick="printSingleBoqPage(${i})" title="Print Page ${i + 1} only" style="padding:3px 7px;font-size:11px;font-weight:700;border-color:#CBD5E1;background:#fff;border-radius:4px;color:#0F172A;line-height:1.2;">
        ${i + 1}
      </button>
    `;
  }
  bar.innerHTML = html;
  if (currentTab === 'boq') {
    bar.style.display = 'inline-flex';
    const printText = document.getElementById('btnPrintText');
    if (printText) printText.textContent = `Print All BOQ Pages (${totalPages})`;
  }
}

function togglePadMode() {
  const isPad = document.getElementById('chkPadMode').checked;
  setPadMode(isPad);
}

function setPadMode(on) {
  document.getElementById('chkPadMode').checked = !!on;
  const marginInputsWrap = document.getElementById('marginInputsWrap');
  const padNotice = document.getElementById('padModeNotice');
  if (marginInputsWrap) marginInputsWrap.style.display = on ? 'flex' : 'none';
  if (padNotice) padNotice.style.display = on ? 'flex' : 'none';

  try {
    localStorage.setItem('estimate_print_pad_mode', on ? '1' : '0');
  } catch(e){}
  updatePrintRules();
  if (estimateData) {
    renderBoqPaper(estimateData);
  }
}

function updatePrintRules() {
  const isPadMode = document.getElementById('chkPadMode') ? document.getElementById('chkPadMode').checked : true;
  const topIn = parseFloat(document.getElementById('marginTop')?.value) || 2.17;
  const bottomIn = parseFloat(document.getElementById('marginBottom')?.value) || 1.00;

  try {
    localStorage.setItem('estimate_print_top_margin', topIn);
    localStorage.setItem('estimate_print_bottom_margin', bottomIn);
  } catch(e){}

  let css = '';
  if (isPadMode) {
    css = `
      .is-pad-mode .paper-sheet {
        padding-top: ${topIn}in !important;
        padding-bottom: ${bottomIn}in !important;
        padding-left: 14mm !important;
        padding-right: 14mm !important;
      }
      .is-pad-mode .letterhead-top,
      .is-pad-mode .letterhead-bottom-banner {
        display: none !important;
      }
      .is-pad-mode .proposal-title {
        margin-top: 0 !important;
        padding-top: 0 !important;
      }
      @media print {
        .letterhead-top, .letterhead-bottom-banner {
          display: none !important;
        }
        .paper-sheet {
          padding-top: ${topIn}in !important;
          padding-bottom: ${bottomIn}in !important;
          padding-left: 14mm !important;
          padding-right: 14mm !important;
        }
        .proposal-title {
          margin-top: 0 !important;
          padding-top: 0 !important;
        }
      }
    `;
  } else {
    css = `
      .paper-sheet {
        padding: 15mm 14mm !important;
      }
      .letterhead-top {
        display: flex !important;
      }
      .letterhead-bottom-banner {
        display: block !important;
      }
      @media print {
        .letterhead-top {
          display: flex !important;
        }
        .letterhead-bottom-banner {
          display: block !important;
        }
        .paper-sheet {
          padding: 15mm 14mm !important;
        }
      }
    `;
  }

  let styleEl = document.getElementById('pageRules');
  if (!styleEl) {
    styleEl = document.createElement('style');
    styleEl.id = 'pageRules';
    document.head.appendChild(styleEl);
  }
  styleEl.textContent = css;

  document.body.classList.toggle('is-pad-mode', isPadMode);
}

function initPrintSettings() {
  try {
    const savedPadMode = localStorage.getItem('estimate_print_pad_mode');
    const savedTop = localStorage.getItem('estimate_print_top_margin');
    const savedBottom = localStorage.getItem('estimate_print_bottom_margin');

    if (savedTop && !isNaN(parseFloat(savedTop)) && document.getElementById('marginTop')) {
      document.getElementById('marginTop').value = parseFloat(savedTop);
    }
    if (savedBottom && !isNaN(parseFloat(savedBottom)) && document.getElementById('marginBottom')) {
      document.getElementById('marginBottom').value = parseFloat(savedBottom);
    }
    if (savedPadMode !== null) {
      setPadMode(savedPadMode === '1');
    } else {
      setPadMode(true);
    }
  } catch(e) {
    updatePrintRules();
  }
}

function printSingleBoqPage(pageIdx) {
  updatePrintRules();
  document.body.classList.remove('print-proposal', 'print-boq', 'print-both', 'print-single-page');
  document.body.classList.add('print-single-page');

  document.querySelectorAll('.boq-page-wrapper').forEach((w, i) => {
    if (i === pageIdx) {
      w.classList.add('print-active-single');
    } else {
      w.classList.remove('print-active-single');
    }
  });

  requestAnimationFrame(() => {
    window.print();
  });
}

function printCurrentTab() {
  updatePrintRules();
  document.body.classList.remove('print-proposal', 'print-boq', 'print-both', 'print-single-page');
  document.querySelectorAll('.boq-page-wrapper').forEach(w => w.classList.remove('print-active-single'));

  if (currentTab === 'proposal') {
    document.body.classList.add('print-proposal');
  } else {
    document.body.classList.add('print-boq');
  }

  requestAnimationFrame(() => {
    window.print();
  });
}

function printBothSheets() {
  updatePrintRules();
  document.body.classList.remove('print-proposal', 'print-boq', 'print-both', 'print-single-page');
  document.querySelectorAll('.boq-page-wrapper').forEach(w => w.classList.remove('print-active-single'));

  document.body.classList.add('print-both');

  requestAnimationFrame(() => {
    window.print();
  });
}

window.addEventListener('afterprint', () => {
  document.body.classList.remove('print-proposal', 'print-boq', 'print-both', 'print-single-page');
  document.querySelectorAll('.boq-page-wrapper').forEach(w => w.classList.remove('print-active-single'));
  switchTab(currentTab);
});

async function loadEstimateData() {
  initPrintSettings();
  try {
    const res = await fetch(`${BASE_PATH}/api/estimates.php?action=get&id=${ESTIMATE_ID}`);
    const d = await res.json();
    if (!d.success || !d.data) {
      alert('Could not load estimate.');
      return;
    }

    estimateData = d.data;
    renderProposalPaper(d.data);
    renderBoqPaper(d.data);
  } catch(e) {
    console.error(e);
  }
}

function renderProposalPaper(data) {
  const est = data.estimate;
  const sections = data.sections || [];

  document.getElementById('viewClientCompany').textContent = est.client_company || est.client_name;
  document.getElementById('viewClientDesignation').textContent = est.client_designation || 'Managing Director';
  document.getElementById('viewClientAddress').textContent = est.client_address || '';
  document.getElementById('viewClientPhone').textContent = est.client_phone ? `Phone: ${est.client_phone}` : '';
  document.getElementById('viewEstSubject').textContent = est.subject;
  document.getElementById('viewEstNo').textContent = est.estimate_no;
  
  if (est.created_at) {
    const d = new Date(est.created_at);
    document.getElementById('viewEstDate').textContent = `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()}`;
  }

  document.getElementById('viewAdvPct').textContent = `${Number(est.advance_pct)}%`;
  document.getElementById('viewRunPct').textContent = `${Number(est.running_pct)}%`;
  document.getElementById('viewFinPct').textContent = `${Number(est.final_pct)}%`;

  if (est.notes) document.getElementById('viewNotesBody').textContent = est.notes;

  // Render Summary Table of Works
  const summaryTbody = document.getElementById('viewSummaryTableBody');
  summaryTbody.innerHTML = sections.map((sec, idx) => {
    const letter = String.fromCharCode(65 + idx);
    const cleanName = esc(sec.name).replace(/^[A-Z0-9]+[\.\:\s\-]+/i, '');
    return `
      <tr>
        <td style="text-align:center;font-weight:700;color:#9C1F24;">${letter}</td>
        <td style="font-weight:600;color:#0F172A;">${letter}. ${cleanName || esc(sec.name)}</td>
        <td style="text-align:right;font-weight:700;">৳ ${Math.round(Number(sec.subtotal || 0)).toLocaleString('en-IN')}</td>
      </tr>
    `;
  }).join('');

  const grandTotal = Math.round(Number(est.grand_total || 0));
  document.getElementById('viewSummaryGrandTotal').textContent = '৳ ' + grandTotal.toLocaleString('en-IN');
  const inWords = numberToWordsBD(grandTotal);
  document.getElementById('viewInWords').textContent = inWords;

  const authName = (est.approved_by || 'Md. Mustafizur Rahman').toUpperCase();
  const authDesig = est.approved_by_designation || 'Managing Director';

  if (document.getElementById('proposalAuthorizedName')) document.getElementById('proposalAuthorizedName').textContent = authName;
  if (document.getElementById('proposalAuthorizedDesig')) document.getElementById('proposalAuthorizedDesig').textContent = authDesig;

  document.getElementById('viewClientSignTitle').textContent = (est.client_company || est.client_name).toUpperCase();
  document.getElementById('viewClientSignSub').textContent = est.client_designation || 'Authorized Signatory';
}

function estimateRowHeight(desc) {
  if (!desc) return 24;
  const explicitLines = desc.split(/\r\n|\r|\n/).length;
  const charLines = Math.ceil(desc.length / 65);
  const totalLines = Math.max(explicitLines, charLines);
  if (totalLines <= 1) return 24;
  return 24 + (totalLines - 1) * 16;
}

function renderBoqPaper(data) {
  const est = data.estimate;
  const sections = data.sections || [];
  const container = document.getElementById('tabBoqPaper');
  if (!container) return;

  const isPadMode = document.getElementById('chkPadMode') ? document.getElementById('chkPadMode').checked : true;
  const topIn = parseFloat(document.getElementById('marginTop')?.value) || 2.17;
  const bottomIn = parseFloat(document.getElementById('marginBottom')?.value) || 1.00;

  // 96 DPI: 297mm = 1123px.
  // In pad mode, subtract pad top and bottom clearances (e.g. 2.17in and 1.00in = 304px) + 16px safety buffer
  const maxContentHeight = isPadMode 
    ? (1123 - Math.round((topIn + bottomIn) * 96) - 16) 
    : (1123 - 140);

  const page1HeaderOverhead = 68;
  const contHeaderOverhead = 46;
  const footerOverhead = 140;

  const estProjectTitle = `FINAL BILL / ESTIMATE OF INTERIOR DECORATION WORK — ${est.client_company || est.client_name}`;
  const estLocation = est.client_address ? est.client_address.toUpperCase() : 'LILY INTERIORS PROJECT';

  // 1. Flatten all sections and items into atomic printable blocks
  const blocks = [];
  sections.forEach((sec, sIdx) => {
    const letter = String.fromCharCode(65 + sIdx);
    const cleanName = esc(sec.name).replace(/^[A-Z0-9]+[\.\:\s\-]+/i, '');
    const displayName = `${letter}. ${cleanName || esc(sec.name)}`;

    // Section header block
    blocks.push({
      type: 'section_header',
      secDisplayName: displayName,
      height: 24,
      title: displayName
    });

    // Items in this section
    (sec.items || []).forEach((it, itIdx) => {
      const sl = it.sl_no || (itIdx + 1);
      const h = estimateRowHeight(it.description);
      blocks.push({
        type: 'item',
        secDisplayName: displayName,
        sl: sl,
        desc: it.description,
        unit: it.unit,
        qty: it.quantity,
        rate: it.unit_price,
        amt: it.amount,
        height: h
      });
    });

    // Section subtotal block
    blocks.push({
      type: 'section_subtotal',
      secDisplayName: displayName,
      height: 24,
      subtotal: sec.subtotal || 0
    });
  });

  // 2. Paginate blocks into discrete A4 pages
  const pages = [];
  let currentPage = {
    pageNum: 1,
    blocks: [],
    hasSignatures: false
  };
  let currentHeight = page1HeaderOverhead;

  for (let i = 0; i < blocks.length; i++) {
    const b = blocks[i];
    const isLast = (i === blocks.length - 1);
    let needed = b.height + (isLast ? footerOverhead : 0);

    // Prevent orphan section header at the bottom of a page
    if (b.type === 'section_header' && blocks[i + 1]) {
      needed = Math.max(needed, b.height + blocks[i + 1].height);
    }

    // If block doesn't fit, start a new page
    if ((currentHeight + needed > maxContentHeight) && currentPage.blocks.length > 0) {
      pages.push(currentPage);
      currentPage = {
        pageNum: pages.length + 1,
        blocks: [],
        hasSignatures: false
      };
      currentHeight = contHeaderOverhead;

      // If section was split across page break, add (Continued) header row
      if (b.type === 'item') {
        const contBlock = {
          type: 'section_header_cont',
          secDisplayName: b.secDisplayName,
          height: 26,
          title: `${b.secDisplayName} (Continued)`
        };
        currentPage.blocks.push(contBlock);
        currentHeight += 26;
      }
    }

    currentPage.blocks.push(b);
    currentHeight += b.height;
  }

  currentPage.hasSignatures = true;
  pages.push(currentPage);

  const totalPages = pages.length;
  updateBoqPageButtons(totalPages);

  // 3. Render HTML for all pages
  const grandTotal = Math.round(Number(est.grand_total || 0));
  const grandTotalFormatted = grandTotal.toLocaleString('en-IN');
  const inWords = numberToWordsBD(grandTotal);

  const authName = est.approved_by || 'Md. Mustafizur Rahman';
  const authDesig = est.approved_by_designation || 'Managing Director';
  const prepName = est.prepared_by || 'Md. Rukonuzzaman';
  const prepDesig = est.prepared_by_designation || 'Interior Designer';

  let fullHtml = '';

  pages.forEach((p, pIdx) => {
    let rowsHtml = '';
    p.blocks.forEach(b => {
      if (b.type === 'section_header') {
        rowsHtml += `
          <tr>
            <td colspan="6" class="boq-section-header" style="background:#E2E8F0;font-weight:900;font-size:12px;color:#0F172A;letter-spacing:0.5px;">
              ${esc(b.title)}
            </td>
          </tr>
        `;
      } else if (b.type === 'section_header_cont') {
        rowsHtml += `
          <tr>
            <td colspan="6" class="boq-section-header" style="background:#F1F5F9;font-weight:900;font-size:11.5px;color:#475569;letter-spacing:0.4px;border-bottom:1.5px solid #94A3B8;">
              ${esc(b.title)}
            </td>
          </tr>
        `;
      } else if (b.type === 'item') {
        rowsHtml += `
          <tr>
            <td style="text-align:center;font-weight:600;">${b.sl}</td>
            <td style="line-height:1.4;">${esc(b.desc)}</td>
            <td style="text-align:center;font-weight:600;">${esc(b.unit)}</td>
            <td style="text-align:right;font-weight:600;">${Number(b.qty).toLocaleString()}</td>
            <td style="text-align:right;">${Math.round(Number(b.rate || 0)).toLocaleString()}</td>
            <td style="text-align:right;font-weight:700;">${Math.round(Number(b.amt || 0)).toLocaleString('en-IN')}</td>
          </tr>
        `;
      } else if (b.type === 'section_subtotal') {
        rowsHtml += `
          <tr class="boq-subtotal-row">
            <td colspan="5" style="text-align:right;padding-right:12px;font-weight:800;">${esc(b.secDisplayName)} Sub Total:</td>
            <td style="text-align:right;color:#9C1F24;font-weight:900;">৳ ${Math.round(Number(b.subtotal || 0)).toLocaleString('en-IN')}</td>
          </tr>
        `;
      }
    });

    fullHtml += `
      <div class="boq-page-wrapper" data-page-index="${pIdx}">
        <!-- Screen Tag Banner (Hidden in Print) -->
        <div class="boq-page-tag">
          <div style="display:flex;align-items:center;gap:8px;">
            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#9C1F24;"></span>
            <span>📄 BOQ Sheet — Page ${p.pageNum} of ${totalPages}</span>
          </div>
          <div style="display:flex;align-items:center;gap:8px;">
            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="printSingleBoqPage(${pIdx})" style="padding:3px 12px;font-size:11px;font-weight:700;border-color:#CBD5E1;background:#fff;border-radius:4px;color:#0F172A;">
              🖨️ Print Page ${p.pageNum}
            </button>
          </div>
        </div>

        <!-- Discrete A4 Sheet -->
        <div class="paper-sheet boq-page-sheet" data-page="${p.pageNum}">
          ${p.pageNum === 1 ? `
            <!-- Page 1 Header Title -->
            <div style="text-align:center;margin-bottom:14px;border-bottom:2px solid #9C1F24;padding-bottom:8px;">
              <h2 style="font-size:15px;font-weight:900;color:#0F172A;margin:0;letter-spacing:0.5px;">
                ${esc(estProjectTitle)}
              </h2>
              <div style="font-size:11px;font-weight:700;color:#64748B;margin-top:3px;">
                ${esc(estLocation)}
              </div>
            </div>
          ` : `
            <!-- Continuation Page Sub-Header -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;border-bottom:1px solid #CBD5E1;padding-bottom:4px;font-size:11px;color:#64748B;font-weight:700;">
              <span>${esc(est.subject || 'ESTIMATE / BOQ')} (Continuation)</span>
              <span>Page ${p.pageNum} of ${totalPages}</span>
            </div>
          `}

          <!-- Mandatory Table Header Row Repeated on Every Single Page -->
          <table class="boq-table">
            <thead>
              <tr>
                <th style="width:38px;text-align:center;">SL.</th>
                <th>DESCRIPTION OF WORK</th>
                <th style="width:65px;text-align:center;">UNIT</th>
                <th style="width:75px;text-align:right;">QNTY.</th>
                <th style="width:85px;text-align:right;">UNIT PRICE</th>
                <th style="width:110px;text-align:right;">AMOUNT (TK)</th>
              </tr>
            </thead>
            <tbody>
              ${rowsHtml}
            </tbody>
            ${p.hasSignatures ? `
              <tfoot>
                <tr class="boq-grand-total-row" style="background:#E2E8F0;font-weight:900;font-size:12.5px;border-top:2px solid #64748B;border-bottom:2px solid #64748B;">
                  <td colspan="5" style="text-align:right;padding:8px 12px;font-weight:900;color:#0F172A;">Grand Total Amount:</td>
                  <td style="text-align:right;padding:8px 12px;font-weight:900;color:#0F172A;font-size:13px;">৳ ${grandTotalFormatted}</td>
                </tr>
              </tfoot>
            ` : ''}
          </table>

          ${p.hasSignatures ? `
            <!-- In Words -->
            <div style="font-size:12.5px;font-weight:700;margin-top:10px;margin-bottom:18px;">
              <u>In word:</u> <span>${esc(inWords)}</span>
            </div>

            <!-- BOQ Signatures Block -->
            <div class="signature-row" style="margin-top:32px;page-break-inside:avoid;break-inside:avoid;">
              <div class="sign-box" style="text-align:left;">
                <div style="font-size:11.5px;color:#475569;margin-bottom:25px;font-style:italic;">Thanking You</div>
                <div class="sign-title">${esc(prepName)}</div>
                <div class="sign-sub">${esc(prepDesig)}</div>
                <div style="font-size:10.5px;font-weight:700;color:#9C1F24;margin-top:2px;">LILY INTERIORS</div>
              </div>

              <div class="sign-box" style="text-align:right;">
                <div style="height:38px;"></div>
                <div class="sign-title">${esc(authName)}</div>
                <div class="sign-sub">${esc(authDesig)}</div>
                <div style="font-size:10.5px;font-weight:700;color:#9C1F24;margin-top:2px;">LILY INTERIORS</div>
              </div>
            </div>
          ` : ''}
        </div>
      </div>
    `;
  });

  container.innerHTML = fullHtml;
}

async function convertToProject(id) {
  if (!confirm('Convert this approved estimate into an active project in Profixapp? Project budget will be automatically set to the estimated grand total.')) return;
  
  try {
    const res = await fetch(`${BASE_PATH}/api/estimates.php?action=convert_to_project`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id=' + id
    });
    const d = await res.json();
    if (d.success) {
      alert('Successfully converted to active project!');
      window.location = `${BASE_PATH}/project-detail?id=${d.project_id}`;
    } else {
      alert(d.message || 'Could not convert to project.');
    }
  } catch(e) {
    alert('Network error while converting.');
  }
}

function numberToWordsBD(num) {
  if (!num || isNaN(num) || num <= 0) return 'Zero Taka Only.';
  const integer = Math.floor(num);
  
  const a = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
  const b = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

  function convert999(n) {
    let str = '';
    if (n >= 100) {
      str += a[Math.floor(n / 100)] + ' Hundred ';
      n %= 100;
    }
    if (n >= 20) {
      str += b[Math.floor(n / 10)] + (n % 10 ? ' ' + a[n % 10] : '');
    } else if (n > 0) {
      str += a[n];
    }
    return str.trim();
  }

  let words = '';
  let crore = Math.floor(integer / 10000000);
  let rem = integer % 10000000;
  let lac = Math.floor(rem / 100000);
  rem %= 100000;
  let thousand = Math.floor(rem / 1000);
  let hundreds = rem % 1000;

  if (crore > 0) words += convert999(crore) + ' Crore ';
  if (lac > 0) words += convert999(lac) + ' Lac, ';
  if (thousand > 0) words += convert999(thousand) + ' Thousand and ';
  if (hundreds > 0) words += convert999(hundreds);

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

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', loadEstimateData);
} else {
  loadEstimateData();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
