<?php
/**
 * Reusable Attendance Entry modal (modular).
 *
 * Used by both the Daily Labor page and the Project Details > Attendance tab.
 * Pass before including:
 *   $attEntryWorkers  - array of ['id','name','trade','default_daily_rate']
 *   $attEntryProjects - array of ['id','name'] (optional; ignored when locked)
 *   $attEntryLocked   - int, fixed project id (project-detail context) or 0
 *   $attEntryNow      - string Y-m-d (optional, defaults to today)
 */
require_once __DIR__ . '/../includes/auth.php';
$attEntryNow   = $attEntryNow   ?? date('Y-m-d');
$attEntryLock  = intval($attEntryLocked ?? 0);
if (empty($attEntryWorkers) && isset($pdo)) {
    $attEntryWorkers = $pdo->query("SELECT id,name,trade,default_daily_rate FROM app_workers WHERE is_active=1 ORDER BY name")->fetchAll();
}
if (empty($attEntryProjects) && isset($pdo)) {
    $attEntryProjects = $pdo->query("SELECT id,name FROM app_projects WHERE is_deleted=0 ORDER BY name")->fetchAll();
}
$attEntryWorkers  = $attEntryWorkers ?? [];
$attEntryProjects = $attEntryProjects ?? [];
?>
<div class="modal-overlay" id="attEntryModal"><div class="modal" data-form-nav>
  <div class="modal-header"><h3>&#128203; Mark Attendance</h3><div class="modal-close" onclick="closeModal('attEntryModal')">&times;</div></div>
  <div class="modal-body">
    <input type="hidden" id="attEntryLockedProject" value="<?= $attEntryLock ?>">
    <div class="form-group" id="attEntryProjectGroup" style="<?= $attEntryLock ? 'display:none;' : '' ?>">
      <label class="form-label">Project <span class="required">*</span></label>
      <select id="attEntryProject" class="form-select">
        <option value="">-- Select Project --</option>
        <?php foreach($attEntryProjects as $ap): ?>
        <option value="<?=(int)$ap['id']?>" <?= ($attEntryLock == $ap['id']) ? 'selected' : '' ?>><?=htmlspecialchars($ap['name'])?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group"><label class="form-label">Worker <span class="required">*</span></label>
      <select id="attEntryWorker" class="form-select" onchange="fillAttEntryRate()">
        <option value="">-- Select Worker --</option>
        <?php foreach($attEntryWorkers as $aw): ?>
        <option value="<?=(int)$aw['id']?>" data-rate="<?=htmlspecialchars($aw['default_daily_rate']??'')?>"><?=htmlspecialchars($aw['name'])?> (<?=htmlspecialchars($aw['trade'])?>)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="two-col">
      <div class="form-group"><label class="form-label">Date</label><input type="text" id="attEntryDate" class="form-input smart-date" placeholder="<?=date('j/n/y')?>" data-date-target="attEntryDateHidden"><input type="hidden" id="attEntryDateHidden" value="<?=$attEntryNow?>"></div>
      <div class="form-group"><label class="form-label">Daily Rate (Tk)</label><input type="number" id="attEntryRate" class="form-input" placeholder="0" oninput="calcAttEntryEarned()"></div>
    </div>
    <div class="form-group"><label class="form-label">Attendance Type</label>
      <div class="multiplier-group">
        <button class="multiplier-btn active" data-val="1"   onclick="setAttEntryMult(this)">Full Day (1x)</button>
        <button class="multiplier-btn" data-val="0.5" onclick="setAttEntryMult(this)">Half Day (0.5x)</button>
        <button class="multiplier-btn" data-val="1.5" onclick="setAttEntryMult(this)">Overtime (1.5x)</button>
        <button class="multiplier-btn" data-val="2"   onclick="setAttEntryMult(this)">Double (2x)</button>
      </div><input type="hidden" id="attEntryMultiplier" value="1">
    </div>
    <div style="background:var(--success-bg);padding:12px;border-radius:var(--radius-md);display:flex;justify-content:space-between;margin-bottom:12px;"><span style="font-weight:600;color:var(--success);">Earned</span><span id="attEntryEarned" style="font-family:'Poppins','Noto Sans Bengali','Hind Siliguri','Nirmala UI','Vrinda','Shonar Bangla',sans-serif;font-weight:800;color:var(--success);">Tk. 0</span></div>
    <div class="form-group">
      <label class="form-checkbox" style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;font-weight:600;color:var(--text);margin-bottom:8px;">
        <input type="checkbox" id="attEntryEveryDay" onchange="toggleEveryDayEntry()" style="width:18px;height:18px;cursor:pointer;"> &#128197; Every day from this date (auto-add daily attendance)
      </label>
      <div id="attEntryEveryDayBox" style="display:none;">
        <div class="two-col">
          <div class="form-group"><label class="form-label">Start Date</label><input type="text" id="attEntryStartDate" class="form-input smart-date" data-date-target="attEntryStartDateHidden"><input type="hidden" id="attEntryStartDateHidden"></div>
          <div class="form-group"><label class="form-label">Number of Days</label><input type="number" id="attEntryDays" class="form-input" value="1" min="1" step="1"></div>
        </div>
        <p style="font-size:12px;color:var(--text-muted);margin:0;">Attendance will be auto-added for each day in this range. You can edit or remove any day later.</p>
      </div>
    </div>
    <div class="form-group"><label class="form-label">Notes</label><input type="text" id="attEntryNotes" class="form-input" placeholder="Optional"></div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('attEntryModal')">Cancel</button><button class="btn btn-primary" data-save-btn onclick="saveAttEntryAttendance()">Save</button></div>
</div></div>
