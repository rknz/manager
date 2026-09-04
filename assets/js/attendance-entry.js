// Shared modular Attendance Entry controller.
// Used by both the Daily Labor page and Project Details > Attendance tab.
// Depends on globals: TODAY, SmartDate, openModal, closeModal, showToast, esc, fmt.

var attEntryState = { locked: 0, onSaved: null };

function openAttendanceEntry(locked, onSaved) {
  attEntryState.locked = parseInt(locked || 0, 10) || 0;
  attEntryState.onSaved = (typeof onSaved === 'function') ? onSaved : null;

  var proj = document.getElementById('attEntryProject');
  var grp  = document.getElementById('attEntryProjectGroup');
  var dlProj = document.getElementById('dlProject');

  if (attEntryState.locked) {
    if (grp) grp.style.display = 'none';
    if (proj) proj.value = String(attEntryState.locked);
  } else {
    if (grp) grp.style.display = '';
    if (proj) {
      if (dlProj && dlProj.value) {
        proj.value = String(dlProj.value);
      } else if (!proj.value) {
        proj.value = '';
      }
    }
  }

  var workerSel = document.getElementById('attEntryWorker');
  if (workerSel) workerSel.value = '';
  var rateInput = document.getElementById('attEntryRate');
  if (rateInput) rateInput.value = '';
  var multInput = document.getElementById('attEntryMultiplier');
  if (multInput) multInput.value = '1';

  document.querySelectorAll('#attEntryModal .multiplier-btn').forEach(function (b) {
    b.classList.toggle('active', b.dataset.val === '1');
  });

  var everyDayCheck = document.getElementById('attEntryEveryDay');
  if (everyDayCheck) everyDayCheck.checked = false;
  toggleEveryDayEntry();

  var notesInput = document.getElementById('attEntryNotes');
  if (notesInput) notesInput.value = '';

  var curDate = (typeof TODAY !== 'undefined') ? TODAY : new Date().toISOString().split('T')[0];
  var dateHidden = document.getElementById('attEntryDateHidden');
  if (dateHidden) dateHidden.value = curDate;
  var dateInput = document.getElementById('attEntryDate');
  if (dateInput) {
    if (window.SmartDate && window.SmartDate.setDateValue) {
      window.SmartDate.setDateValue(dateInput, curDate);
    } else {
      dateInput.value = curDate;
    }
  }

  calcAttEntryEarned();
  if (typeof window.openModal === 'function') {
    window.openModal('attEntryModal');
  } else {
    var m = document.getElementById('attEntryModal');
    if (m) { m.classList.add('active'); document.body.style.overflow = 'hidden'; }
  }
}

function setAttEntryMult(btn) {
  document.querySelectorAll('#attEntryModal .multiplier-btn').forEach(function (b) { b.classList.remove('active'); });
  if (btn) {
    btn.classList.add('active');
    var multInput = document.getElementById('attEntryMultiplier');
    if (multInput) multInput.value = btn.dataset.val || '1';
  }
  calcAttEntryEarned();
}

function fillAttEntryRate() {
  var sel = document.getElementById('attEntryWorker');
  if (!sel) return;
  var opt = sel.options[sel.selectedIndex];
  var rate = opt ? (opt.dataset.rate || '') : '';
  var rateInput = document.getElementById('attEntryRate');
  if (rateInput) rateInput.value = rate;
  calcAttEntryEarned();
}

function calcAttEntryEarned() {
  var r = parseFloat(document.getElementById('attEntryRate')?.value) || 0;
  var m = parseFloat(document.getElementById('attEntryMultiplier')?.value) || 1;
  var el = document.getElementById('attEntryEarned');
  if (el) {
    el.textContent = 'Tk. ' + (r * m).toLocaleString('en-BD', { maximumFractionDigits: 0 });
  }
}

function toggleEveryDayEntry() {
  var everyDayCheck = document.getElementById('attEntryEveryDay');
  var on = everyDayCheck ? everyDayCheck.checked : false;
  var box = document.getElementById('attEntryEveryDayBox');
  if (box) box.style.display = on ? 'block' : 'none';
  if (on) {
    var curDate = (typeof TODAY !== 'undefined') ? TODAY : new Date().toISOString().split('T')[0];
    var start = document.getElementById('attEntryDateHidden')?.value || curDate;
    var startHidden = document.getElementById('attEntryStartDateHidden');
    if (startHidden) startHidden.value = start;
    var startInput = document.getElementById('attEntryStartDate');
    if (startInput) {
      if (window.SmartDate && window.SmartDate.setDateValue) {
        window.SmartDate.setDateValue(startInput, start);
      } else {
        startInput.value = start;
      }
    }
    var daysInput = document.getElementById('attEntryDays');
    if (daysInput) daysInput.value = 1;
  }
}

function attEntryResolveProject() {
  var pid = attEntryState.locked;
  if (!pid) {
    var sel = document.getElementById('attEntryProject');
    if (sel && sel.value) pid = sel.value;
  }
  if (!pid) {
    var dlProj = document.getElementById('dlProject');
    if (dlProj && dlProj.value) pid = dlProj.value;
  }
  return pid;
}

async function saveAttEntryAttendance() {
  var pid = attEntryResolveProject();
  var wid = document.getElementById('attEntryWorker')?.value;
  var rate = parseFloat(document.getElementById('attEntryRate')?.value) || 0;
  var mult = parseFloat(document.getElementById('attEntryMultiplier')?.value) || 1;

  if (!pid) { showToast('Please select a project', 'warning'); return; }
  if (!wid) { showToast('Please select a worker', 'warning'); return; }
  if (rate <= 0) { showToast('Please enter daily rate', 'warning'); return; }

  var curDate = (typeof TODAY !== 'undefined') ? TODAY : new Date().toISOString().split('T')[0];
  var everyDayCheck = document.getElementById('attEntryEveryDay');
  var everyDay = everyDayCheck ? everyDayCheck.checked : false;
  var days = everyDay ? (parseInt(document.getElementById('attEntryDays')?.value, 10) || 1) : 1;
  
  var dateEl = document.getElementById('attEntryDate');
  var dateHidden = document.getElementById('attEntryDateHidden');
  var workDate = dateHidden ? (dateHidden.value || curDate) : curDate;
  if (everyDay) {
    var startHidden = document.getElementById('attEntryStartDateHidden');
    if (startHidden && startHidden.value) workDate = startHidden.value;
  }

  var fd = new FormData();
  fd.append('project_id',            pid);
  fd.append('worker_id',             wid);
  fd.append('work_date',             workDate);
  fd.append('daily_rate',            rate);
  fd.append('attendance_multiplier', mult);
  fd.append('days',                  days);
  fd.append('notes',                 document.getElementById('attEntryNotes')?.value || '');

  try {
    var r = await fetch(BASE_PATH + '/api/attendance.php?action=add_attendance', { method: 'POST', body: fd });
    var d = await r.json();
    if (d.success) {
      if (d.created === 0 && d.skipped > 0) {
        showToast('Attendance already exists for this date!', 'warning');
      } else {
        var msg = everyDay
          ? ('Attendance added for ' + (d.created || 0) + ' day(s)' + (d.skipped ? ' (' + d.skipped + ' already existed)' : '') + '! Earned: Tk.' + parseFloat(d.total_earned || (rate * mult)).toLocaleString())
          : ('Attendance marked! Earned: Tk.' + parseFloat(rate * mult).toLocaleString());
        showToast(msg, 'success');
      }
      if (everyDayCheck) everyDayCheck.checked = false;
      toggleEveryDayEntry();
      if (typeof window.closeModal === 'function') {
        window.closeModal('attEntryModal');
      } else {
        var m = document.getElementById('attEntryModal');
        if (m) { m.classList.remove('active'); document.body.style.overflow = ''; }
      }
      if (attEntryState.onSaved) attEntryState.onSaved(pid);
    } else {
      showToast(d.message || 'Error saving attendance', 'error');
    }
  } catch (e) {
    showToast('Connection error', 'error');
  }
}

// Global Exports
window.openAttendanceEntry = openAttendanceEntry;
window.setAttEntryMult = setAttEntryMult;
window.fillAttEntryRate = fillAttEntryRate;
window.calcAttEntryEarned = calcAttEntryEarned;
window.toggleEveryDayEntry = toggleEveryDayEntry;
window.saveAttEntryAttendance = saveAttEntryAttendance;
