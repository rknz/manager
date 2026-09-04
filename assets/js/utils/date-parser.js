/**
 * date-parser.js
 * Smart date shorthand input utility
 * Supports: 2/5/26 | 2-5-26 | 2.5.26 | 2,5,26 → "2 May 2026"
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
   * Attach smart date behavior to an input element
   * @param {HTMLInputElement} el
   */
  function attachSmartDate(el) {
    if (!el || el.dataset.smartDateAttached) return;
    el.dataset.smartDateAttached = 'true';

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

        // Fire custom event so other scripts can react
        el.dispatchEvent(new CustomEvent('dateChanged', {
          detail: parsed, bubbles: true
        }));
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
      }
    }

    el.addEventListener('blur', tryFormat);

    // On focus: convert display back to shorthand for easy editing
    el.addEventListener('focus', function () {
      const db = el.dataset.parsedDb;
      if (db && /^\d{4}-\d{2}-\d{2}$/.test(db)) {
        const [y, m, d] = db.split('-');
        el.value = `${parseInt(d)}/${parseInt(m)}/${String(y).slice(2)}`;
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
    // If parsedDb is set, use it
    if (el.dataset.parsedDb) return el.dataset.parsedDb;
    // Try to parse current value
    const p = parseShortDate(el.value);
    if (p) return p.dbValue;
    // If value looks like YYYY-MM-DD already
    if (/^\d{4}-\d{2}-\d{2}$/.test(el.value.trim())) return el.value.trim();
    return el.value;
  }

  /**
   * Set a smart-date input to a given YYYY-MM-DD value
   */
  function setDateValue(el, dbValue) {
    if (!el || !dbValue) return;
    el.dataset.parsedDb = dbValue;
    const [y, m, d] = dbValue.split('-');
    const monthShort = MONTHS_SHORT[parseInt(m, 10) - 1];
    el.value = `${parseInt(d)} ${monthShort} ${y}`;
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
