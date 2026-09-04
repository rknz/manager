<?php
// views/daily-labor.php â€” Daily attendance entry
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$pageTitle = 'Daily Labor';
$activeNav = 'daily-labor';
$projects = $pdo->query("SELECT id,name FROM app_projects WHERE is_deleted=0 AND status='Ongoing' ORDER BY name")->fetchAll();
$workers  = $pdo->query("SELECT id,name,trade,default_daily_rate FROM app_workers WHERE is_active=1 ORDER BY name")->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="two-col" style="align-items:start;">
  <!-- LEFT: Attendance Entry -->
  <div class="card mb-4">
    <div class="card-header"><h3>&#128203; Mark Attendance</h3></div>
    <div class="card-body" data-form-nav>
      <div class="form-group">
        <label class="form-label">Project</label>
        <select id="dlProject" class="form-select" onchange="loadWorkerSummary()">
          <option value="">-- Select Project --</option>
          <?php foreach($projects as $p): ?><option value="<?=$p['id']?>"><?=htmlspecialchars($p['name'])?></option><?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-primary btn-full" data-save-btn onclick="openAttendanceEntry(0, afterLaborAttendanceSave)">&#128203; Mark Attendance</button>
      <p style="font-size:12px;color:var(--text-muted);margin-top:8px;">Pick a project, worker, and mark full/half/OT days (or auto-add daily in a date range).</p>
    </div>
  </div>


  <!-- RIGHT: Worker Summary -->
  <div>
    <div class="card mb-4">
      <div class="card-header"><h3>&#128200; Worker Summary</h3><span id="dlSummaryProject" class="text-sm text-muted">Select project</span></div>
      <div id="workerSummaryList" style="padding:8px;">
        <div class="empty-state" style="padding:24px;"><p>Select a project</p></div>
      </div>
    </div>
    <!-- Payment Entry -->
    <div class="card">
      <div class="card-header"><h3>&#128176; Pay Worker</h3></div>
      <div class="card-body" data-form-nav>
        <div class="form-group">
          <label class="form-label">Worker</label>
          <select id="payWorker" class="form-select">
            <option value="">-- Select Worker --</option>
            <?php foreach($workers as $w): ?><option value="<?=$w['id']?>"><?=htmlspecialchars($w['name'])?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="two-col">
          <div class="form-group">
            <label class="form-label">Amount (Tk) <span class="required">*</span></label>
            <input type="number" id="payAmount" class="form-input" placeholder="0">
          </div>
          <div class="form-group">
            <label class="form-label">Date</label>
            <input type="text" id="payDate" class="form-input smart-date" placeholder="<?=date('j/n/y')?>" data-date-target="payDateHidden">
            <input type="hidden" id="payDateHidden" value="<?=date('Y-m-d')?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Who Paid</label>
          <input type="text" id="payWho" class="form-input" value="<?=htmlspecialchars($_SESSION['username']??'')?>">
        </div>
        <button class="btn btn-success btn-full" data-save-btn onclick="savePayment()">&#128176; Record Payment</button>
      </div>
    </div>
  </div>
</div>

<script>
var TODAY = '<?=date('Y-m-d')?>';
function afterLaborAttendanceSave(){ loadWorkerSummary(); }
async function savePayment() {
  const pid = document.getElementById('dlProject').value;
  const wid = document.getElementById('payWorker').value;
  const amt = parseFloat(document.getElementById('payAmount').value)||0;
  if (!pid||!wid||amt<=0) { showToast('Project, worker and amount required','warning'); return; }
  const fd = new FormData();
  fd.append('project_id',  pid);
  fd.append('worker_id',   wid);
  fd.append('amount',      amt);
  fd.append('payment_date',document.getElementById('payDateHidden').value||TODAY);
  fd.append('who_paid',    document.getElementById('payWho').value);
  fd.append('payment_method','Cash');
  try {
    const r = await fetch(BASE_PATH + '/api/attendance.php?action=add_payment',{method:'POST',body:fd});
    const d = await r.json();
    if (d.success) { showToast('Payment recorded!','success'); document.getElementById('payAmount').value=''; loadWorkerSummary(); }
    else { showToast(d.message||'Error','error'); }
  } catch(e) { showToast('Connection error','error'); }
}
async function loadWorkerSummary() {
  const pid = document.getElementById('dlProject').value;
  if (!pid) return;
  const sel = document.getElementById('dlProject');
  document.getElementById('dlSummaryProject').textContent = sel.options[sel.selectedIndex]?.text || '';
  const r = await fetch(BASE_PATH + '/api/attendance.php?action=get_summary&project_id='+pid);
  const d = await r.json();
  const list = document.getElementById('workerSummaryList');
  if (!d.success||!d.data.length) { list.innerHTML='<div class="empty-state" style="padding:16px;"><p>No labor data yet</p></div>'; return; }
  list.innerHTML = d.data.map(w=>`
    <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid var(--border-light);">
      <div style="flex:1;min-width:0;"><div style="font-size:13px;font-weight:600;">${esc(w.worker_name)}</div><div style="font-size:11px;color:var(--text-muted);">${w.total_days} days earned</div></div>
      <div style="text-align:right;">
        <div style="font-size:12px;color:var(--text-muted);">Earned: <strong>Tk.${parseFloat(w.total_earned).toLocaleString()}</strong></div>
        <div style="font-size:12px;color:${w.balance_due>0?'var(--danger)':'var(--success)'};">Due: <strong>Tk.${parseFloat(w.balance_due).toLocaleString()}</strong></div>
      </div>
</div>`).join('');
}
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')}
function fmtDate(d){if(!d)return'-';var dt=new Date(d);return isNaN(dt)?d:dt.toLocaleDateString('en-GB',{day:'2-digit',month:'short'});}
document.addEventListener('DOMContentLoaded',function(){
  SmartDate.initAll();
  SmartDate.setDateValue(document.getElementById('payDate'),TODAY);
});
</script>
<?php
$attEntryWorkers  = $workers;
$attEntryProjects = $projects;
$attEntryLocked   = 0;
include __DIR__ . '/../includes/attendance_entry.php';
$extraScripts = array_merge($extraScripts ?? [], ['attendance-entry.js']);
?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
