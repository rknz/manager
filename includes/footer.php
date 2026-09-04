</main><!-- /main-content -->
</div><!-- /main-wrapper -->

<!-- RIGHT PANEL (Global) -->
<aside class="right-panel" id="rightPanel">
  <!-- Calendar -->
  <div class="right-panel-section">
    <h3>
      <span>&#128197; Schedule</span>
      <a href="#" onclick="showAddScheduleModal();return false;">+ Add</a>
    </h3>
    <div id="calendarWidget"></div>
  </div>
  <!-- Today's Schedule -->
  <div class="right-panel-section" id="todayScheduleSection">
    <h3><span>&#9201; Today</span></h3>
    <div class="schedule-list" id="todayScheduleList">
      <div class="loading-state"><div class="spinner" style="width:20px;height:20px;"></div></div>
    </div>
  </div>
  <!-- Quick Summary -->
  <div class="right-panel-section">
    <h3><span>&#128200; Today Summary</span></h3>
    <div class="quick-summary" id="quickSummaryBox">
      <div class="skeleton" style="height:20px;margin-bottom:6px;"></div>
      <div class="skeleton" style="height:20px;margin-bottom:6px;"></div>
      <div class="skeleton" style="height:20px;"></div>
    </div>
  </div>
</aside>

<!-- BOTTOM NAV (mobile) -->
<nav class="bottom-nav" id="bottomNav">
  <a href="<?= $basePath ?>/dashboard" class="bottom-nav-item <?= ($activeNav==='dashboard')?'active':'' ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
    Home
  </a>
  <a href="<?= $basePath ?>/projects" class="bottom-nav-item <?= ($activeNav==='projects')?'active':'' ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
    Projects
  </a>
  <button class="bottom-nav-fab" id="fabBtn" onclick="window.location='<?= $basePath ?>/quick-purchase'">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
  </button>
  <a href="<?= $basePath ?>/daily-labor" class="bottom-nav-item <?= ($activeNav==='daily-labor')?'active':'' ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    Labor
  </a>
  <a href="<?= $basePath ?>/payments" class="bottom-nav-item <?= ($activeNav==='payments')?'active':'' ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
    Pay
  </a>
</nav>

<!-- ADD GLOBAL SCHEDULE MODAL -->
<div class="modal-overlay" id="addScheduleModal"><div class="modal modal-sm" data-form-nav>
  <div class="modal-header"><h3>&#128197; Add Schedule</h3><div class="modal-close" onclick="closeModal('addScheduleModal')">&times;</div></div>
  <div class="modal-body">
    <div class="form-group"><label class="form-label">Date</label><input type="text" id="globalSchDate" class="form-input smart-date" placeholder="<?= date('j/n/y', strtotime('+1 day')) ?>" data-date-target="globalSchDateHidden"><input type="hidden" id="globalSchDateHidden" value="<?= date('Y-m-d', strtotime('+1 day')) ?>"></div>
    <div class="form-group"><label class="form-label">Category</label><select id="globalSchCategory" class="form-select"><option value="">General</option><option>Board</option><option>Paint</option><option>Glass</option><option>Electric</option><option>Payment</option></select></div>
    <div class="form-group"><label class="form-label">Description <span class="required">*</span></label><textarea id="globalSchDesc" class="form-textarea" rows="2" placeholder="What needs to be done?"></textarea></div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('addScheduleModal')">Cancel</button><button class="btn btn-primary" data-save-btn onclick="saveSchedule()">Save</button></div>
</div></div>

<!-- TOAST CONTAINER -->
<div class="toast-container" id="toastContainer"></div>

</div><!-- /app-layout -->

<!-- CORE SCRIPTS -->
<script src="<?= $basePath ?>/assets/js/utils/date-parser.js"></script>
<script src="<?= $basePath ?>/assets/js/utils/form-nav.js"></script>
<script src="<?= $basePath ?>/assets/js/utils/password-confirm.js"></script>
<script src="<?= $basePath ?>/assets/js/right-panel.js?v=2.1.0"></script>
<script src="<?= $basePath ?>/assets/js/attendance-entry.js?v=2.1.0"></script>
<script src="<?= $basePath ?>/assets/js/app.js?v=2.1.0"></script>
<?php if (!empty($extraScripts)) foreach ($extraScripts as $s): ?>
<?php if ($s !== 'attendance-entry.js'): ?>
<script src="<?= $basePath ?>/assets/js/<?= $s ?>?v=2.1.0"></script>
<?php endif; ?>
<?php endforeach; ?>
<?php if (!empty($inlineScript)): ?>
<script><?= $inlineScript ?></script>
<?php endif; ?>

</body>
</html>
