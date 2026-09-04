<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$pageTitle = 'Backup & Restore';
$activeNav = 'backup';
$projects = $pdo->query("SELECT id,name FROM app_projects WHERE is_deleted=0 ORDER BY name")->fetchAll();
include __DIR__ . '/../includes/header.php';
?>

<div class="two-col" style="align-items:start;">
  <!-- BACKUP -->
  <div class="card">
    <div class="card-header"><h3>&#11015; Download Backup</h3></div>
    <div class="card-body">
      <div style="padding:24px;background:var(--stat-blue-bg);border-radius:var(--radius-md);text-align:center;margin-bottom:20px;">
        <div style="font-size:48px;margin-bottom:8px;">&#128230;</div>
        <h3 style="font-size:16px;font-weight:700;margin-bottom:4px;">Full Database Backup</h3>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">Downloads all projects, purchases, payments, schedules as JSON.</p>
        <button class="btn btn-primary btn-lg btn-full" onclick="downloadFullBackup()">
          &#11015; Download Full Backup
        </button>
      </div>
      <div class="divider"></div>
      <h4 style="font-size:14px;font-weight:700;margin-bottom:12px;">&#128193; Project Backup</h4>
      <p style="font-size:13px;color:var(--text-muted);margin-bottom:12px;">Download a single project's data.</p>
      <div class="form-group">
        <label class="form-label">Select Project</label>
        <select id="backupProject" class="form-select">
          <option value="">-- Select Project --</option>
          <?php foreach($projects as $p): ?><option value="<?=$p['id']?>"><?=htmlspecialchars($p['name'])?></option><?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-outline btn-full" onclick="downloadProjectBackup()">&#11015; Download Project Backup</button>
    </div>
  </div>

  <!-- RESTORE -->
  <div class="card">
    <div class="card-header"><h3>&#11014; Restore Data</h3></div>
    <div class="card-body">
      <div style="padding:16px;background:var(--danger-bg);border-radius:var(--radius-md);margin-bottom:20px;border:1px solid #fca5a5;">
        <strong style="color:var(--danger);">&#9888; Warning</strong>
        <p style="font-size:12px;color:#7f1d1d;margin-top:4px;">Restoring will add data from the backup file. Duplicate projects will be skipped automatically to prevent data loss. This cannot be undone.</p>
      </div>
      <div class="form-group">
        <label class="form-label">Select Backup File (.json)</label>
        <input type="file" id="restoreFile" class="form-input" accept=".json" onchange="previewRestore()">
      </div>
      <div id="restorePreview" style="display:none;background:var(--border-light);border-radius:var(--radius-md);padding:16px;margin-bottom:16px;">
        <h4 style="font-size:13px;font-weight:700;margin-bottom:8px;">Preview</h4>
        <div id="restorePreviewContent"></div>
      </div>
      <button class="btn btn-danger btn-full" id="restoreBtn" style="display:none;" onclick="doRestore()">
        &#11014; Restore Now
      </button>

      <div class="divider"></div>

      <div id="restoreLog" style="display:none;">
        <h4 style="font-size:13px;font-weight:700;margin-bottom:8px;">Restore Log</h4>
        <div id="restoreLogContent" style="background:#000;color:#0f0;font-family:monospace;font-size:11px;padding:12px;border-radius:var(--radius-sm);max-height:200px;overflow-y:auto;"></div>
      </div>
    </div>
  </div>
</div>

<script>
var restoreData = null;

async function downloadFullBackup() {
  confirmDelete('Enter admin password to download full backup:', async function() {
    showToast('Preparing full backup...', 'info', 2000);
    window.location = BASE_PATH + '/api/backup.php?action=full_backup';
  });
}

async function downloadProjectBackup() {
  const pid = document.getElementById('backupProject').value;
  if (!pid) { showToast('Please select a project', 'warning'); return; }
  confirmDelete('Enter admin password to download project backup:', async function() {
    showToast('Preparing project backup...', 'info', 2000);
    window.location = BASE_PATH + '/api/backup.php?action=project_backup&project_id=' + pid;
  });
}

async function previewRestore() {
  const file = document.getElementById('restoreFile').files[0];
  if (!file) return;
  const text = await file.text();
  try {
    restoreData = JSON.parse(text);
    if (!restoreData.version) throw new Error('Invalid backup file');
    const r = await fetch(BASE_PATH + '/api/backup.php?action=check_restore', { method:'POST', headers:{'Content-Type':'application/json'}, body: text });
    const d = await r.json();
    const preview = document.getElementById('restorePreviewContent');
    let html = '<div style="font-size:12px;"><strong>Type:</strong> ' + (restoreData.type||'?').toUpperCase() + '<br>';
    html += '<strong>Created:</strong> ' + (restoreData.created_at||'?') + '<br>';
    if (d.data && d.data.conflicts.length) {
      html += '<div style="color:var(--warning);margin-top:6px;"><strong>&#9888; Will Skip (duplicates):</strong><br>' + d.data.conflicts.map(c=>'• '+c.name).join('<br>') + '</div>';
    }
    if (d.data && d.data.new_records.length) {
      html += '<div style="color:var(--success);margin-top:6px;"><strong>&#10003; Will Import:</strong><br>' + d.data.new_records.map(c=>'• '+c.name).join('<br>') + '</div>';
    }
    html += '</div>';
    preview.innerHTML = html;
    document.getElementById('restorePreview').style.display = 'block';
    document.getElementById('restoreBtn').style.display = 'block';
  } catch(e) { showToast('Invalid backup file: ' + e.message, 'error'); restoreData = null; }
}

async function doRestore() {
  if (!restoreData) return;
  confirmDelete('Restore data from backup file?', async function() {
    const action = restoreData.type === 'project' ? 'restore_project' : 'restore_project';
    const logBox = document.getElementById('restoreLog');
    const logContent = document.getElementById('restoreLogContent');
    logBox.style.display = 'block';
    logContent.textContent = 'Restoring...';
    try {
      const r = await fetch(BASE_PATH + '/api/backup.php?action='+action, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(restoreData) });
      const d = await r.json();
      if (d.success) {
        showToast('Restore successful!', 'success');
        logContent.textContent = (d.log||[]).join('\n');
      } else {
        showToast(d.message || 'Restore failed', 'error');
        logContent.textContent = 'Error: ' + d.message;
      }
    } catch(e) { showToast('Connection error', 'error'); }
  });
}
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
