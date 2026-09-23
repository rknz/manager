/**
 * date-parser.js
 * Smart date shorthand input utility with interactive calendar icon
 * Supports: 2/5/26 | 2-5-26 | 2.5.26 | 2,5,26 → "2 May 2026"
 * Plus clickable calendar picker icon
 * DB store format: YYYY-MM-DD
 */
(function (window) {
  'use strict';

  const MONTHS_EN = ['January','February','March','April','May','June',
                     'July','August','September','October','November','December'];
  const MONTHS_SHORT = ['Jan','Feb','Mar','Apr','May','Jun',
                        'Jul','Aug','Sep','Oct','Nov','Dec'];

  /**
   * Parse a shorthand date string
   * Returns: { day, month, year, dbValue, displayValue } or null if invalid
   */
  function parseShortDate(input) {
    if (!input) return null;
    const str = input.trim();

    // Match: digits [separator] digits [separator] digits
    const match = str.match(/^(\d{1,2})[\/\-\.,\s](\d{1,2})[\/\-\.,\s](\d{2,4})$/);
    if (!match) return null;

    let day   = parseInt(match[1], 10);
    let month = parseInt(match[2], 10);
    let year  = parseInt(match[3], 10);

    // 2-digit year: 26 → 2026
    if (year < 100) year += 2000;

    // Validate
    if (month < 1 || month > 12) return null;
    if (day < 1 || day > 31)     return null;

    // Check days in month
    const daysInMonth = new Date(year, month, 0).getDate();
    if (day > daysInMonth) return null;

    const monthName  = MONTHS_EN[month - 1];
    const monthShort = MONTHS_SHORT[month - 1];

    const dd = String(day).padStart(2, '0');
    const mm = String(month).padStart(2, '0');

    return {
      day, month, year,
      dbValue:      `${year}-${mm}-${dd}`,
      displayValue: `${day} ${monthShort} ${year}`,
      fullDisplay:  `${day} ${monthName} ${year}`
    };
  }

  /**
   * Attach smart date behavior and calendar icon to an input element
   * @param {HTMLInputElement} el
   */
  function attachSmartDate(el) {
    if (!el || el.dataset.smartDateAttached) return;
    el.dataset.smartDateAttached = 'true';

    // Wrap in smart-date-container if not already wrapped
    if (!el.parentElement || !el.parentElement.classList.contains('smart-date-container')) {
      const container = document.createElement('div');
      container.className = 'smart-date-container';
      container.style.position = 'relative';
      container.style.display = 'inline-flex';
      container.style.width = '100%';
      container.style.alignItems = 'center';
      if (el.style.maxWidth) {
        container.style.maxWidth = el.style.maxWidth;
      }
      if (el.style.width && el.style.width !== '100%') {
        container.style.width = el.style.width;
      }
      if (el.style.flex) {
        container.style.flex = el.style.flex;
      }
      el.parentNode.insertBefore(container, el);
      container.appendChild(el);
    }
    const container = el.parentElement;
    container.style.position = 'relative';
    container.style.display = 'inline-flex';
    container.style.width = '100%';
    container.style.alignItems = 'center';
    el.style.paddingRight = '38px';

    // Hidden native date picker (must never be visible on screen)
    let hiddenPicker = container.querySelector('.smart-date-hidden-picker');
    if (!hiddenPicker) {
      hiddenPicker = document.createElement('input');
      hiddenPicker.type = 'date';
      hiddenPicker.className = 'smart-date-hidden-picker';
      hiddenPicker.tabIndex = -1;
      hiddenPicker.setAttribute('aria-hidden', 'true');
      container.appendChild(hiddenPicker);
    }
    hiddenPicker.style.cssText = 'position:absolute!important;width:1px!important;height:1px!important;padding:0!important;margin:-1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;border:0!important;opacity:0!important;pointer-events:none!important;bottom:0!important;right:0!important;visibility:hidden!important;';
    el._hiddenPicker = hiddenPicker;

    // Calendar trigger icon button positioned inside the right side of the input
    let calBtn = container.querySelector('.smart-date-cal-btn');
    if (!calBtn) {
      calBtn = document.createElement('button');
      calBtn.type = 'button';
      calBtn.className = 'smart-date-cal-btn';
      calBtn.tabIndex = -1;
      calBtn.title = 'Select Date';
      calBtn.setAttribute('aria-label', 'Open Calendar');
      calBtn.innerHTML = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
      container.appendChild(calBtn);
    }
    calBtn.style.cssText = 'position:absolute;right:8px;top:50%;transform:translateY(-50%);display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;padding:0;border:none;background:transparent;color:#64748b;border-radius:6px;cursor:pointer;z-index:2;line-height:1;transition:color 0.15s ease,background-color 0.15s ease;';
    calBtn.onmouseenter = function() { this.style.color = '#9c1f24'; this.style.backgroundColor = 'rgba(156,31,36,0.08)'; };
    calBtn.onmouseleave = function() { this.style.color = '#64748b'; this.style.backgroundColor = 'transparent'; };

    calBtn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();

      let currentDb = el.dataset.parsedDb;
      if (!currentDb || !/^\d{4}-\d{2}-\d{2}$/.test(currentDb)) {
        const p = parseShortDate(el.value);
        if (p) currentDb = p.dbValue;
      }
      if (currentDb && /^\d{4}-\d{2}-\d{2}$/.test(currentDb)) {
        hiddenPicker.value = currentDb;
      } else {
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        const d = String(now.getDate()).padStart(2, '0');
        hiddenPicker.value = `${y}-${m}-${d}`;
      }

      try {
        if (typeof hiddenPicker.showPicker === 'function') {
          hiddenPicker.showPicker();
        } else {
          hiddenPicker.focus();
          hiddenPicker.click();
        }
      } catch (err) {
        hiddenPicker.focus();
      }
    });

    hiddenPicker.addEventListener('change', function () {
      if (hiddenPicker.value) {
        setDateValue(el, hiddenPicker.value);
        el.dispatchEvent(new Event('change', { bubbles: true }));
        el.dispatchEvent(new Event('input', { bubbles: true }));
      }
    });

    // Store the hidden DB value field ID
    const hiddenId = el.dataset.dateTarget || null;

    // Set placeholder if not already set
    if (!el.placeholder) el.placeholder = 'e.g. 2/5/26';

    function tryFormat() {
      const parsed = parseShortDate(el.value);
      if (parsed) {
        el.value = parsed.displayValue;
        el.style.borderColor = '';
        el.title = '';
        el.dataset.parsedDb = parsed.dbValue;

        // Update hidden field if linked
        if (hiddenId) {
          const hidden = document.getElementById(hiddenId);
          if (hidden) hidden.value = parsed.dbValue;
        }

        if (hiddenPicker) {
          hiddenPicker.value = parsed.dbValue;
        }

        // Fire custom event and change event so other scripts react
        el.dispatchEvent(new CustomEvent('dateChanged', {
          detail: parsed, bubbles: true
        }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
      } else if (el.value.trim() !== '') {
        // Check if already a standard display format — leave it
        const alreadyFormatted = /^\d{1,2}\s+[A-Za-z]{3,9}\s+\d{4}$/.test(el.value.trim());
        if (!alreadyFormatted) {
          el.style.borderColor = '#dc2626';
          el.title = 'Invalid date. Use format: day/month/year (e.g. 2/5/26)';
        }
      } else {
        el.style.borderColor = '';
        el.title = '';
        el.dataset.parsedDb = '';
        if (hiddenId) {
          const hidden = document.getElementById(hiddenId);
          if (hidden) hidden.value = '';
        }
        if (hiddenPicker) {
          hiddenPicker.value = '';
        }
      }
    }

    el.addEventListener('blur', tryFormat);

    // On focus: convert display back to shorthand for easy editing
    el.addEventListener('focus', function () {
      const db = el.dataset.parsedDb;
      if (db && /^\d{4}-\d{2}-\d{2}$/.test(db)) {
        const [y, m, d] = db.split('-');
        el.value = `${parseInt(d, 10)}/${parseInt(m, 10)}/${String(y).slice(2)}`;
      } else if (/^\d{1,2}\s+[A-Za-z]/.test(el.value)) {
        // Convert "2 May 2026" → "2/5/26"
        const d = new Date(el.value);
        if (!isNaN(d)) {
          el.value = `${d.getDate()}/${d.getMonth()+1}/${String(d.getFullYear()).slice(2)}`;
        }
      }
    });
  }

  /**
   * Get the DB value from a smart-date input
   */
  function getDbValue(el) {
    if (!el) return '';
    if (el.dataset.parsedDb) return el.dataset.parsedDb;
    const p = parseShortDate(el.value);
    if (p) return p.dbValue;
    if (/^\d{4}-\d{2}-\d{2}$/.test(el.value.trim())) return el.value.trim();
    return el.value;
  }

  /**
   * Set a smart-date input to a given YYYY-MM-DD value
   */
  function setDateValue(el, dbValue) {
    if (!el || !dbValue) return;
    el.dataset.parsedDb = dbValue;
    const parts = dbValue.split('-');
    if (parts.length === 3) {
      const y = parts[0];
      const m = parseInt(parts[1], 10);
      const d = parseInt(parts[2], 10);
      const monthShort = MONTHS_SHORT[m - 1] || '';
      el.value = `${d} ${monthShort} ${y}`;
      el.style.borderColor = '';
      el.title = '';
    }

    const hiddenId = el.dataset.dateTarget || null;
    if (hiddenId) {
      const hidden = document.getElementById(hiddenId);
      if (hidden) hidden.value = dbValue;
    }

    if (el._hiddenPicker) {
      el._hiddenPicker.value = dbValue;
    }

    el.dispatchEvent(new CustomEvent('dateChanged', {
      detail: { dbValue, displayValue: el.value },
      bubbles: true
    }));
  }

  /**
   * Auto-init: attach to all inputs with class .smart-date or data-smart-date
   */
  function initAll() {
    document.querySelectorAll('.smart-date, [data-smart-date]').forEach(attachSmartDate);
  }

  // Auto-run on DOM ready and on dynamic content
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }

  // MutationObserver for dynamically added elements (modals etc.)
  const observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (m) {
      m.addedNodes.forEach(function (node) {
        if (node.nodeType !== 1) return;
        if (node.matches && (node.matches('.smart-date') || node.matches('[data-smart-date]'))) {
          attachSmartDate(node);
        }
        node.querySelectorAll && node.querySelectorAll('.smart-date, [data-smart-date]').forEach(attachSmartDate);
      });
    });
  });
  observer.observe(document.body || document.documentElement, { childList: true, subtree: true });

  // Public API
  window.SmartDate = { parse: parseShortDate, attach: attachSmartDate, getDbValue, setDateValue, initAll };

}(window));
