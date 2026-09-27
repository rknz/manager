<?php
// views/estimate-catalog.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = 'Item & Rate Library (ক্যাটালগ)';
$activeNav = 'estimates';
include __DIR__ . '/../includes/header.php';
?>

<style>
.catalog-container {
  max-width: 1400px;
  margin: 0 auto 60px auto;
}

.catalog-header-bar {
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid #E2E8F0;
  padding: 16px 20px;
  margin-bottom: 18px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}

.catalog-layout {
  display: block;
  width: 100%;
}

/* Items Card & Table (Full Width) */
.items-main-card {
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid #E2E8F0;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.items-card-head {
  padding: 16px 20px;
  border-bottom: 1px solid #E2E8F0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  background: #FAFBFD;
}

.table-catalog {
  width: 100%;
  border-collapse: collapse;
}
.table-catalog th {
  background: #F8FAFC;
  padding: 11px 16px;
  font-size: 11px;
  font-weight: 800;
  color: #64748B;
  text-transform: uppercase;
  border-bottom: 1px solid #E2E8F0;
  text-align: left;
}
.table-catalog td {
  padding: 12px 16px;
  border-bottom: 1px solid #F1F5F9;
  font-size: 13px;
  vertical-align: top;
}
.table-catalog tr:hover td {
  background: #F8FAFC;
}

/* Modal Overlay & Dialog */
.catalog-modal-overlay {
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
.catalog-modal-overlay.is-active {
  display: flex !important;
}

.catalog-modal-box {
  background: #ffffff;
  border-radius: 16px;
  width: 100%;
  max-width: 580px;
  box-shadow: 0 25px 60px rgba(0,0,0,0.25);
  border: 1px solid #E2E8F0;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  animation: modalPopIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  margin: auto;
}
@keyframes modalPopIn {
  from { transform: scale(0.96) translateY(10px); opacity: 0; }
  to { transform: scale(1) translateY(0); opacity: 1; }
}

.catalog-modal-head {
  padding: 16px 22px;
  border-bottom: 1px solid #F1F5F9;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #FAFBFD;
}
.catalog-modal-head h3 {
  font-size: 16px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}
.catalog-modal-close-btn {
  background: none;
  border: none;
  font-size: 24px;
  line-height: 1;
  color: #94A3B8;
  cursor: pointer;
  padding: 4px;
}
.catalog-modal-close-btn:hover {
  color: #EF4444;
}

.catalog-modal-body {
  padding: 20px 22px;
}

.catalog-modal-foot {
  padding: 14px 22px;
  background: #F8FAFC;
  border-top: 1px solid #F1F5F9;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

/* Catalog Mobile Cards Feed */
.catalog-desktop-table {
  display: block;
}
.catalog-mobile-cards-feed {
  display: none;
}
.catalog-mobile-category-strip {
  display: none;
}
.catalog-mobile-fab {
  display: none;
}

@media (max-width: 768px) {
  .catalog-container {
    margin-bottom: calc(var(--bottom-nav-height, 64px) + 30px) !important;
  }
  .catalog-header-bar {
    padding: 12px 14px !important;
    border-radius: 14px !important;
    gap: 10px !important;
  }
  .catalog-header-bar > div:first-child {
    width: 100%;
    justify-content: space-between;
  }
  .catalog-header-bar h2 {
    font-size: 15px !important;
  }
  .catalog-header-bar span {
    display: none;
  }
  .catalog-header-bar > div:last-child {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px !important;
  }
  .catalog-header-bar > div:last-child > div:first-child {
    display: none !important; /* category select hidden on mobile, replaced by pill strip */
  }
  .catalog-header-bar .btn {
    font-size: 11.5px !important;
    padding: 6px 10px !important;
    flex: 1;
    text-align: center;
    justify-content: center;
  }

  /* Mobile Category Pills Strip */
  .catalog-mobile-category-strip {
    display: flex !important;
    align-items: center;
    gap: 8px;
    overflow-x: auto;
    padding: 4px 2px 10px 2px;
    margin-bottom: 12px;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
  }
  .catalog-mobile-category-strip::-webkit-scrollbar {
    display: none;
  }
  .cat-strip-pill {
    background: #ffffff;
    border: 1px solid #E2E8F0;
    padding: 7px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    flex-shrink: 0;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.15s;
  }
  .cat-strip-pill.active {
    background: #9C1F24 !important;
    color: #ffffff !important;
    border-color: #9C1F24 !important;
  }

  .items-main-card {
    border-radius: 16px !important;
  }
  .items-card-head {
    padding: 12px 14px !important;
    flex-direction: column !important;
    align-items: stretch !important;
    gap: 10px !important;
  }
  .items-card-head > div:last-child {
    min-width: 100% !important;
  }
  #catalogSearch {
    width: 100% !important;
    box-sizing: border-box;
    border-radius: 10px !important;
  }

  /* Desktop Table Hidden on Mobile */
  .catalog-desktop-table {
    display: none !important;
  }

  /* Mobile Cards Feed Displayed */
  .catalog-mobile-cards-feed {
    display: flex !important;
    flex-direction: column;
    gap: 12px;
    padding: 14px;
  }
  .catalog-mobile-item-card {
    background: #ffffff;
    border: 1px solid #F1F5F9;
    border-radius: 14px;
    padding: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  .cat-mob-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
  }
  .cat-mob-pill {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    background: #F1F5F9;
    color: #475569;
  }
  .cat-mob-rate {
    font-size: 14.5px;
    font-weight: 800;
    color: #9C1F24;
  }
  .cat-mob-unit {
    font-size: 11.5px;
    font-weight: 600;
    color: #64748B;
  }
  .cat-mob-name {
    font-size: 14px;
    font-weight: 800;
    color: #0F172A;
    line-height: 1.3;
  }
  .cat-mob-specs {
    font-size: 12px;
    color: #64748B;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  .cat-mob-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 8px;
    border-top: 1px solid #F8FAFC;
    gap: 8px;
    margin-top: 2px;
  }
  .cat-mob-unit-badge {
    font-size: 11.5px;
    color: #64748B;
  }
  .cat-mob-actions {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .cat-mob-btn-edit {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 6px;
    padding: 4px 10px;
    font-size: 11.5px;
    font-weight: 700;
    color: #0F172A;
    cursor: pointer;
  }
  .cat-mob-btn-delete {
    background: #FFF1F2;
    border: 1px solid #FECDD3;
    border-radius: 6px;
    padding: 4px 10px;
    font-size: 11.5px;
    font-weight: 700;
    color: #E11D48;
    cursor: pointer;
  }

  /* Mobile FAB */
  .catalog-mobile-fab {
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
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(181,24,46,0.35);
    z-index: 999;
    transition: transform 0.15s;
  }
  .catalog-mobile-fab:active {
    transform: scale(0.92);
  }
}
</style>

<div class="catalog-container">
  <!-- Top Bar -->
  <div class="catalog-header-bar">
    <div style="display:flex;align-items:center;gap:12px;">
      <a href="<?= $basePath ?>/estimates" class="btn btn-sm btn-outline-secondary" style="padding:6px 12px;font-weight:700;">
        &#8592; Back to Estimates
      </a>
      <div>
        <h2 style="font-size:18px;font-weight:800;color:#0F172A;margin:0;">
          📚 Item &amp; Rate Library (আইটেম ও রেট ক্যাটালগ)
        </h2>
        <span style="font-size:12px;color:#64748B;">
          এখানে ক্যাটাগরি অনুযায়ী আইটেম ও স্পেসিফিকেশন রেট সংরক্ষণ করুন। এস্টিমেটে টাইপ করলে সাথে সাথে চলে আসবে।
        </span>
      </div>
    </div>

    <!-- Category Filter & Actions Toolbar -->
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
      <div style="display:flex;align-items:center;gap:8px;">
        <label for="catalogCategorySelect" style="font-weight:800;font-size:13px;color:#0F172A;letter-spacing:0.5px;display: block;/* align-items:center; */gap:6px;width: 9rem">
          📁 Category:
        </label>
        <select id="catalogCategorySelect" class="form-select" onchange="onCategorySelectChange(this.value)" style="min-width:210px;font-weight:700;color:#9C1F24;padding:7px 12px;border:1.5px solid #CBD5E1;border-radius:8px;">
          <option value="All">All Categories (সব আইটেম)</option>
        </select>
      </div>

      <button type="button" class="btn btn-outline-primary" onclick="openAddCategoryModal()" style="font-weight:700;padding:7px 14px;">
        ➕ New Category (+ নতুন ক্যাটাগরি)
      </button>
      <button type="button" class="btn btn-primary" onclick="openAddItemModal()" style="background:#9C1F24;border-color:#9C1F24;font-weight:800;padding:7px 16px;">
        ➕ Add New Item (+ নতুন আইটেম)
      </button>
    </div>
  </div>

  <!-- Mobile Category Filter Strip (Horizontal scroll pills) -->
  <div class="catalog-mobile-category-strip" id="catalogMobileCategoryStrip">
    <!-- Populated by JS -->
  </div>

  <!-- Full Width Items Main Table Card -->
  <div class="catalog-layout">
    <div class="items-main-card" style="width:100%;">
      <div class="items-card-head">
        <div style="display:flex;align-items:center;gap:10px;">
          <h3 style="margin:0;font-size:15px;font-weight:800;color:#0F172A;" id="activeCategoryHeading">
            All Items
          </h3>
          <span style="font-size:12px;color:#64748B;" id="filteredCountLabel">0 items</span>
        </div>

        <div style="display:flex;align-items:center;gap:10px;min-width:300px;">
          <input type="text" id="catalogSearch" class="form-input" placeholder="Search item, category, or specs..." oninput="onSearchInput()" style="font-size:13px;padding:7px 14px;">
        </div>
      </div>

      <div id="catalogTableWrapper">
        <div style="padding:40px;text-align:center;color:#64748B;">
          Loading items...
        </div>
      </div>
    </div>
  </div>

  <!-- Mobile Floating Action Button (FAB) -->
  <button type="button" class="catalog-mobile-fab" id="catalogMobileFab" onclick="openAddItemModal()" title="Add New Item">+</button>
</div>

<!-- ============================================================ -->
<!-- 1. ADD NEW CATEGORY MODAL -->
<!-- ============================================================ -->
<div class="catalog-modal-overlay" id="addCategoryModal">
  <div class="catalog-modal-box" style="max-width:440px;">
    <div class="catalog-modal-head">
      <h3>📁 Add New Category (নতুন ক্যাটাগরি)</h3>
      <button type="button" class="catalog-modal-close-btn" onclick="closeCatalogModal('addCategoryModal')">&times;</button>
    </div>
    <div class="catalog-modal-body">
      <div class="form-group">
        <label class="form-label" style="font-weight:700;color:#0F172A;">
          Category Name <span style="color:#EF4444;">*</span>
        </label>
        <input type="text" id="inpNewCategoryName" class="form-input" placeholder="e.g. Living Room, Gypsum False Ceiling..." onkeydown="if(event.key==='Enter') submitNewCategory()">
      </div>
    </div>
    <div class="catalog-modal-foot">
      <button type="button" class="btn btn-outline-secondary" onclick="closeCatalogModal('addCategoryModal')">Cancel</button>
      <button type="button" class="btn btn-primary" onclick="submitNewCategory()" id="btnSaveCategory">
        💾 Save Category (সংরক্ষণ করুন)
      </button>
    </div>
  </div>
</div>

<!-- ============================================================ -->
<!-- 2. ADD / EDIT ITEM MODAL -->
<!-- ============================================================ -->
<div class="catalog-modal-overlay" id="itemModal">
  <div class="catalog-modal-box">
    <div class="catalog-modal-head">
      <h3 id="modalItemTitle">➕ Add New Item to Library</h3>
      <button type="button" class="catalog-modal-close-btn" onclick="closeCatalogModal('itemModal')">&times;</button>
    </div>
    <div class="catalog-modal-body">
      <input type="hidden" id="modalItemId" value="0">

      <div class="two-col" style="margin-bottom:14px;">
        <div class="form-group">
          <label class="form-label" style="font-weight:700;">Category <span style="color:#EF4444;">*</span></label>
          <div style="display:flex;gap:6px;">
            <select id="modalCategorySelect" class="form-select" style="font-weight:600;">
              <!-- Dynamically populated -->
            </select>
            <button type="button" class="btn btn-outline-secondary" onclick="openAddCategoryModal()" title="Create New Category" style="padding:6px 12px;font-weight:800;">
              +
            </button>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" style="font-weight:700;">Item Short Name <span style="color:#EF4444;">*</span></label>
          <input type="text" id="modalItemName" class="form-input" placeholder="e.g. Plain Particle Ceiling, Wardrobe...">
        </div>
      </div>

      <div class="form-group" style="margin-bottom:14px;">
        <label class="form-label" style="font-weight:700;">Full Technical Specification / Description (বিবরণ) <span style="color:#EF4444;">*</span></label>
        <textarea id="modalSpecs" class="form-input" rows="3" placeholder="Detailed materials, board type, thickness, hardware, framing, and finish..."></textarea>
      </div>

      <div class="two-col">
        <div class="form-group">
          <label class="form-label" style="font-weight:700;">Unit of Measurement</label>
          <select id="modalUnit" class="form-select" style="font-weight:600;">
            <option value="S.ft">S.ft (Square Feet)</option>
            <option value="nos">nos (Numbers / Pcs)</option>
            <option value="per.">per. (Per Person / Unit)</option>
            <option value="LS">LS (Lump Sum)</option>
            <option value="job">job (Job / Service)</option>
            <option value="RFT">RFT (Running Feet)</option>
            <option value="mtr.">mtr. (Meters)</option>
            <option value="coil">coil (Coil)</option>
            <option value="set">set (Complete Set)</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" style="font-weight:700;">Standard Rate (দর TK) <span style="color:#EF4444;">*</span></label>
          <input type="number" step="any" id="modalRate" class="form-input" placeholder="e.g. 520" style="font-weight:800;color:#9C1F24;text-align:right;">
        </div>
      </div>
    </div>
    <div class="catalog-modal-foot">
      <button type="button" class="btn btn-outline-secondary" onclick="closeCatalogModal('itemModal')">Cancel</button>
      <button type="button" class="btn btn-primary" onclick="submitCatalogItem()" id="btnSaveItem">
        💾 Save Item (সংরক্ষণ করুন)
      </button>
    </div>
  </div>
</div>

<script>
var allItems = [];
var availableCategories = ['Ceiling', 'Furniture', 'Wall & Floor', 'Paint', 'Electrical'];
var currentCategory = 'All';

// -------------------------------------------------------------
// 1. INITIALIZATION & DATA LOADING
// -------------------------------------------------------------
async function initCatalog() {
  await loadCategories();
  await loadItems();
}

async function loadCategories() {
  try {
    var res = await fetch(BASE_PATH + '/api/estimates.php?action=categories');
    var d = await res.json();
    if (d.success && d.data && d.data.length) {
      availableCategories = d.data;
    }
  } catch(e) {
    console.error('Error loading categories', e);
  }
}

async function loadItems() {
  try {
    var res = await fetch(BASE_PATH + '/api/estimates.php?action=catalog');
    var d = await res.json();
    if (d.success) {
      allItems = d.data || [];
      renderCategoryDropdown();
      renderItemsTable();
      populateModalCategories();
    }
  } catch(e) {
    console.error('Error loading items', e);
    document.getElementById('catalogTableWrapper').innerHTML = '<div style="padding:30px;color:#EF4444;text-align:center;">Failed to load items.</div>';
  }
}

// -------------------------------------------------------------
// 2. CATEGORY DROPDOWN RENDERING & SELECTION
// -------------------------------------------------------------
function renderCategoryDropdown() {
  var sel = document.getElementById('catalogCategorySelect');
  var mobStrip = document.getElementById('catalogMobileCategoryStrip');
  if (!sel && !mobStrip) return;

  var counts = {};
  allItems.forEach(function(it) {
    var cat = (it.category || 'Other').trim();
    counts[cat] = (counts[cat] || 0) + 1;
  });

  // Unique list of all categories from DB + any in items
  var uniqueCats = Array.from(new Set(availableCategories.concat(Object.keys(counts)))).filter(Boolean).sort();

  if (sel) {
    var html = '<option value="All" ' + (currentCategory === 'All' ? 'selected' : '') + '>All Categories (' + allItems.length + ' items)</option>';
    uniqueCats.forEach(function(cat) {
      var count = counts[cat] || 0;
      var isSel = (currentCategory.toLowerCase() === cat.toLowerCase()) ? 'selected' : '';
      html += '<option value="' + escAttr(cat) + '" ' + isSel + '>' + esc(cat) + ' (' + count + ')</option>';
    });
    sel.innerHTML = html;
  }

  if (mobStrip) {
    var stripHtml = '<button type="button" class="cat-strip-pill ' + (currentCategory === 'All' ? 'active' : '') + '" onclick="selectCategory(\'All\')">All (' + allItems.length + ')</button>';
    uniqueCats.forEach(function(cat) {
      var count = counts[cat] || 0;
      var isSel = (currentCategory.toLowerCase() === cat.toLowerCase()) ? 'active' : '';
      stripHtml += '<button type="button" class="cat-strip-pill ' + isSel + '" onclick="selectCategory(\'' + escAttr(cat) + '\')">' + esc(cat) + ' (' + count + ')</button>';
    });
    mobStrip.innerHTML = stripHtml;
  }
}

function onCategorySelectChange(cat) {
  currentCategory = cat || 'All';
  // Clear search input on category change so user sees all items in that category
  var searchInput = document.getElementById('catalogSearch');
  if (searchInput) searchInput.value = '';
  renderCategoryDropdown();
  renderItemsTable();
}

function selectCategory(cat) {
  onCategorySelectChange(cat);
}

function onSearchInput() {
  renderItemsTable();
}

// -------------------------------------------------------------
// 3. RENDER ITEMS TABLE (WITH CLEAN FILTERING)
// -------------------------------------------------------------
function renderItemsTable() {
  var qInput = document.getElementById('catalogSearch');
  var q = qInput ? qInput.value.trim().toLowerCase() : '';
  var heading = document.getElementById('activeCategoryHeading');
  var countLabel = document.getElementById('filteredCountLabel');
  var wrap = document.getElementById('catalogTableWrapper');
  if (!wrap) return;

  if (heading) heading.textContent = (currentCategory === 'All') ? 'All Items' : currentCategory;

  var filtered = allItems.filter(function(it) {
    var itemCat = (it.category || '').trim();
    var matchCat = (currentCategory === 'All') || (itemCat.toLowerCase() === currentCategory.toLowerCase());
    var matchQ = !q || (
      (it.item_name && it.item_name.toLowerCase().indexOf(q) !== -1) || 
      (it.specifications && it.specifications.toLowerCase().indexOf(q) !== -1) ||
      (it.category && it.category.toLowerCase().indexOf(q) !== -1)
    );
    return matchCat && matchQ;
  });

  if (countLabel) countLabel.textContent = filtered.length + ' items';

  if (!filtered.length) {
    wrap.innerHTML = 
      '<div style="padding:60px 20px;text-align:center;color:#64748B;">' +
        '<div style="font-size:38px;margin-bottom:8px;">📁</div>' +
        '<h4 style="font-size:15px;color:#0F172A;margin-bottom:6px;">No items found</h4>' +
        '<p style="font-size:13px;max-width:420px;margin:0 auto 18px;line-height:1.5;">' +
          (currentCategory !== 'All' ? '"' + esc(currentCategory) + '" ক্যাটাগরিতে এখনো কোনো আইটেম যোগ করা হয়নি।' : 'সার্চের সাথে কোনো আইটেম মিল পাওয়া যায়নি।') +
        '</p>' +
        '<button type="button" class="btn btn-primary" onclick="openAddItemModal(\'' + escAttr(currentCategory) + '\')">' +
          '➕ Add New Item to ' + (currentCategory === 'All' ? 'Library' : esc(currentCategory)) +
        '</button>' +
      '</div>';
    return;
  }

  var html = 
    '<div class="table-responsive catalog-desktop-table">' +
      '<table class="table-catalog">' +
        '<thead>' +
          '<tr>' +
            '<th style="width:140px;">Category</th>' +
            '<th style="width:220px;">Item Name</th>' +
            '<th>Technical Specification (বিবরণ)</th>' +
            '<th style="width:75px;text-align:center;">Unit</th>' +
            '<th style="width:120px;text-align:right;">Default Rate</th>' +
            '<th style="width:90px;text-align:center;">Actions</th>' +
          '</tr>' +
        '</thead>' +
        '<tbody>';

  filtered.forEach(function(it) {
    var rateNum = Number(it.default_rate || 0);
    html += 
      '<tr>' +
        '<td style="font-weight:600;color:#64748B;">' +
          '<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#F1F5F9;font-size:11px;">' +
            esc(it.category) +
          '</span>' +
        '</td>' +
        '<td style="font-weight:700;color:#0F172A;font-size:13px;">' +
          esc(it.item_name) +
        '</td>' +
        '<td style="font-size:12px;color:#334155;line-height:1.5;">' +
          esc(it.specifications) +
        '</td>' +
        '<td style="text-align:center;font-weight:600;color:#475569;">' +
          esc(it.unit) +
        '</td>' +
        '<td style="text-align:right;font-weight:800;color:#9C1F24;font-size:13px;">' +
          '৳ ' + Math.round(rateNum).toLocaleString('en-IN') +
        '</td>' +
        '<td style="text-align:center;">' +
          '<div style="display:flex;align-items:center;justify-content:center;gap:6px;">' +
            '<button type="button" class="btn-icon" onclick="openEditItemModal(' + it.id + ')" title="Edit Item" style="padding:4px 6px;font-size:14px;background:none;border:none;cursor:pointer;">' +
              '✏️' +
            '</button>' +
            '<button type="button" class="btn-icon text-danger" onclick="deleteCatalogItemRow(' + it.id + ', \'' + escAttr(it.item_name) + '\')" title="Delete Item" style="padding:4px 6px;font-size:14px;background:none;border:none;cursor:pointer;">' +
              '🗑️' +
            '</button>' +
          '</div>' +
        '</td>' +
      '</tr>';
  });

  html += '</tbody></table></div>';

  /* Mobile Cards Feed for Mobile screens <= 768px */
  html += '<div class="catalog-mobile-cards-feed">';
  filtered.forEach(function(it) {
    var rateNum = Number(it.default_rate || 0);
    html += 
      '<div class="catalog-mobile-item-card">' +
        '<div class="cat-mob-top">' +
          '<span class="cat-mob-pill">' + esc(it.category) + '</span>' +
          '<div class="cat-mob-rate">৳ ' + Math.round(rateNum).toLocaleString('en-IN') + ' <span class="cat-mob-unit">/ ' + esc(it.unit) + '</span></div>' +
        '</div>' +
        '<div class="cat-mob-name">' + esc(it.item_name) + '</div>' +
        (it.specifications ? '<div class="cat-mob-specs">' + esc(it.specifications) + '</div>' : '') +
        '<div class="cat-mob-footer">' +
          '<span class="cat-mob-unit-badge">Unit: <strong>' + esc(it.unit) + '</strong></span>' +
          '<div class="cat-mob-actions">' +
            '<button type="button" class="cat-mob-btn-edit" onclick="openEditItemModal(' + it.id + ')" title="Edit Item">✏️ Edit</button>' +
            '<button type="button" class="cat-mob-btn-delete" onclick="deleteCatalogItemRow(' + it.id + ', \'' + escAttr(it.item_name) + '\')" title="Delete Item">🗑️ Delete</button>' +
          '</div>' +
        '</div>' +
      '</div>';
  });
  html += '</div>';

  wrap.innerHTML = html;
}

// -------------------------------------------------------------
// 4. MODAL HELPERS & ACTION CONTROLLERS
// -------------------------------------------------------------
function openCatalogModal(id) {
  var m = document.getElementById(id);
  if (m) {
    m.classList.add('is-active');
    document.body.style.overflow = 'hidden';
  }
}

function closeCatalogModal(id) {
  var m = document.getElementById(id);
  if (m) {
    m.classList.remove('is-active');
    document.body.style.overflow = '';
  }
}

// Close on overlay click
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('catalog-modal-overlay')) {
    e.target.classList.remove('is-active');
    document.body.style.overflow = '';
  }
});

// Close on Escape
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.catalog-modal-overlay.is-active').forEach(function(m) {
      m.classList.remove('is-active');
    });
    document.body.style.overflow = '';
  }
});

// -------------------------------------------------------------
// 5. ADD CATEGORY FUNCTIONALITY
// -------------------------------------------------------------
function openAddCategoryModal() {
  document.getElementById('inpNewCategoryName').value = '';
  openCatalogModal('addCategoryModal');
  setTimeout(function() {
    document.getElementById('inpNewCategoryName').focus();
  }, 100);
}

async function submitNewCategory() {
  var name = document.getElementById('inpNewCategoryName').value.trim();
  if (!name) {
    alert('Please enter a category name.');
    document.getElementById('inpNewCategoryName').focus();
    return;
  }

  var btn = document.getElementById('btnSaveCategory');
  btn.disabled = true;
  btn.textContent = 'Saving...';

  try {
    var res = await fetch(BASE_PATH + '/api/estimates.php?action=add_category', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name: name })
    });
    var d = await res.json();
    if (d.success) {
      closeCatalogModal('addCategoryModal');
      if (typeof window.showToast === 'function') {
        window.showToast('Category "' + name + '" created!', 'success');
      }
      await loadCategories();
      selectCategory(name);
    } else {
      alert(d.message || 'Failed to save category.');
    }
  } catch(e) {
    console.error(e);
    alert('Server error while saving category.');
  } finally {
    btn.disabled = false;
    btn.textContent = '💾 Save Category (সংরক্ষণ করুন)';
  }
}

// -------------------------------------------------------------
// 6. ADD / EDIT ITEM FUNCTIONALITY
// -------------------------------------------------------------
function populateModalCategories() {
  var sel = document.getElementById('modalCategorySelect');
  if (!sel) return;
  var uniqueCats = Array.from(new Set(availableCategories.concat(allItems.map(function(i){ return i.category; })))).filter(Boolean).sort();
  sel.innerHTML = uniqueCats.map(function(cat) {
    return '<option value="' + escAttr(cat) + '">' + esc(cat) + '</option>';
  }).join('');
}

function openAddItemModal(preferredCategory) {
  document.getElementById('modalItemId').value = '0';
  document.getElementById('modalItemTitle').textContent = '➕ Add New Item to Library';
  document.getElementById('modalItemName').value = '';
  document.getElementById('modalSpecs').value = '';
  document.getElementById('modalRate').value = '';
  document.getElementById('modalUnit').value = 'S.ft';

  populateModalCategories();

  var targetCat = preferredCategory || (currentCategory !== 'All' ? currentCategory : (availableCategories[0] || 'Ceiling'));
  document.getElementById('modalCategorySelect').value = targetCat;

  openCatalogModal('itemModal');
  setTimeout(function() {
    document.getElementById('modalItemName').focus();
  }, 100);
}

function openEditItemModal(id) {
  var it = allItems.find(function(i) { return i.id == id; });
  if (!it) return;

  document.getElementById('modalItemId').value = it.id;
  document.getElementById('modalItemTitle').textContent = '✏️ Edit Catalog Item';
  populateModalCategories();

  document.getElementById('modalCategorySelect').value = it.category;
  document.getElementById('modalItemName').value = it.item_name;
  document.getElementById('modalSpecs').value = it.specifications || '';
  document.getElementById('modalUnit').value = it.unit || 'S.ft';
  document.getElementById('modalRate').value = it.default_rate || 0;

  openCatalogModal('itemModal');
}

async function submitCatalogItem() {
  var id = Number(document.getElementById('modalItemId').value || 0);
  var cat = document.getElementById('modalCategorySelect').value.trim();
  var itemName = document.getElementById('modalItemName').value.trim();
  var specs = document.getElementById('modalSpecs').value.trim() || itemName;
  var unit = document.getElementById('modalUnit').value.trim();
  var rate = Number(document.getElementById('modalRate').value || 0);

  if (!cat) {
    alert('Please select or create a category.');
    return;
  }
  if (!itemName) {
    alert('Please enter an item name.');
    document.getElementById('modalItemName').focus();
    return;
  }
  if (rate <= 0) {
    alert('Please enter a valid rate.');
    document.getElementById('modalRate').focus();
    return;
  }

  var payload = {
    id: id,
    category: cat,
    item_name: itemName,
    specifications: specs,
    unit: unit,
    default_rate: rate
  };

  var btn = document.getElementById('btnSaveItem');
  btn.disabled = true;
  btn.textContent = 'Saving...';

  var res, d;
  try {
    res = await fetch(BASE_PATH + '/api/estimates.php?action=save_catalog_item', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    d = await res.json();
  } catch(netErr) {
    console.error('Fetch error:', netErr);
    alert('Server connection error. Please try again.');
    btn.disabled = false;
    btn.textContent = '💾 Save Item (সংরক্ষণ করুন)';
    return;
  }

  btn.disabled = false;
  btn.textContent = '💾 Save Item (সংরক্ষণ করুন)';

  if (d && d.success) {
    closeCatalogModal('itemModal');
    if (typeof window.showToast === 'function') {
      window.showToast(id > 0 ? 'Item updated!' : 'Item saved to library!', 'success');
    }
    // Refresh catalog lists safely
    try {
      await loadCategories();
      await loadItems();
      selectCategory(cat);
    } catch(uiErr) {
      console.error('UI refresh error:', uiErr);
    }
  } else {
    alert((d && d.message) ? d.message : 'Failed to save item.');
  }
}

async function deleteCatalogItemRow(id, name) {
  if (!confirm('Are you sure you want to delete "' + name + '" from the library?')) return;

  try {
    var res = await fetch(BASE_PATH + '/api/estimates.php?action=delete_catalog_item&id=' + id, {
      method: 'POST'
    });
    var d = await res.json();
    if (d.success) {
      if (typeof window.showToast === 'function') {
        window.showToast('Item deleted.', 'info');
      }
      await loadItems();
    } else {
      alert(d.message || 'Delete failed.');
    }
  } catch(e) {
    console.error(e);
    alert('Server error while deleting item.');
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

function escAttr(str) {
  if (!str) return '';
  return String(str)
    .replace(/\\/g, '\\\\')
    .replace(/'/g, "\\'")
    .replace(/"/g, '&quot;');
}

function ensureMobileCatalogFabFixed() {
  if (window.innerWidth <= 768) {
    var fab = document.getElementById('catalogMobileFab');
    if (fab && fab.parentElement !== document.body) {
      document.body.appendChild(fab);
    }
  }
}

// Ensure initCatalog runs whether page loaded directly or via PJAX
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function() {
    ensureMobileCatalogFabFixed();
    initCatalog();
  });
} else {
  ensureMobileCatalogFabFixed();
  initCatalog();
}
window.addEventListener('resize', ensureMobileCatalogFabFixed);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
