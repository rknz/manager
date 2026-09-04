/**
 * form-nav.js
 * Tab/Enter keyboard navigation for fast data entry
 * Enter key = advance to next field (like Tab)
 * Ctrl+Enter = save form from anywhere
 */
(function (window) {
  'use strict';

  /**
   * Get all focusable form fields in a container, in DOM order
   */
  function getFields(container) {
    return Array.from(container.querySelectorAll(
      'input:not([type=hidden]):not([disabled]):not([readonly]),' +
      'select:not([disabled]),' +
      'textarea:not([disabled]),' +
      'button[data-nav-stop]'
    )).filter(el => !el.closest('[style*="display: none"]') &&
                    !el.closest('[hidden]') &&
                    el.offsetParent !== null);
  }

  /**
   * Find active modal context, or return document body
   */
  function getActiveContext() {
    const modal = document.querySelector('.modal-overlay.active, .modal.show, [data-form-context="modal"]:not([hidden])');
    return modal || document.body;
  }

  /**
   * Move to next focusable field after current
   */
  function focusNext(current) {
    const context = getActiveContext();
    const fields  = getFields(context);
    const idx     = fields.indexOf(current);
    if (idx === -1) return false;

    for (let i = idx + 1; i < fields.length; i++) {
      const next = fields[i];
      if (next.type === 'submit' || next.dataset.navStop) continue;
      next.focus();
      if (next.select) next.select(); // select text for easy overwrite
      return true;
    }
    return false; // reached end
  }

  /**
   * Find and trigger the save/submit button for the active context
   */
  function triggerSave(context) {
    const ctx    = context || getActiveContext();
    const submit = ctx.querySelector('[data-save-btn], button[type=submit], input[type=submit]');
    if (submit) {
      submit.click();
      return true;
    }
    return false;
  }

  /**
   * Handle keydown on form elements
   */
  function onKeyDown(e) {
    const el  = e.target;
    const tag = el.tagName;
    const isInput    = tag === 'INPUT'    && el.type !== 'submit';
    const isSelect   = tag === 'SELECT';
    const isTextarea = tag === 'TEXTAREA';

    // Ctrl+Enter = Save from anywhere
    if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
      e.preventDefault();
      triggerSave();
      return;
    }

    // Enter on INPUT (not textarea) = advance to next field
    if (e.key === 'Enter' && isInput && !e.ctrlKey && !e.shiftKey) {
      // Only if inside a form-nav enabled container
      const container = el.closest('[data-form-nav], .form-nav, form[data-enter-nav]');
      if (!container) return;

      e.preventDefault();
      const moved = focusNext(el);
      if (!moved) {
        // Last field — trigger save
        triggerSave(container);
      }
      return;
    }

    // Enter on SELECT = advance to next (after selection)
    if (e.key === 'Enter' && isSelect) {
      const container = el.closest('[data-form-nav], .form-nav, form[data-enter-nav]');
      if (!container) return;
      e.preventDefault();
      focusNext(el);
      return;
    }
  }

  /**
   * Attach navigation to a specific container
   */
  function attachFormNav(container) {
    if (!container || container.dataset.formNavAttached) return;
    container.dataset.formNavAttached = 'true';
    container.addEventListener('keydown', onKeyDown);
  }

  /**
   * Auto-init all containers
   */
  function initAll() {
    document.querySelectorAll('[data-form-nav], .form-nav, form[data-enter-nav]')
      .forEach(attachFormNav);
  }

  // Auto-run
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }

  // Watch for dynamically added modals/forms
  const obs = new MutationObserver(function (mutations) {
    mutations.forEach(function (m) {
      m.addedNodes.forEach(function (node) {
        if (node.nodeType !== 1) return;
        if (node.matches && (node.matches('[data-form-nav]') || node.matches('form[data-enter-nav]'))) {
          attachFormNav(node);
        }
        node.querySelectorAll && node.querySelectorAll('[data-form-nav], form[data-enter-nav]')
          .forEach(attachFormNav);
      });
    });
  });
  obs.observe(document.documentElement, { childList: true, subtree: true });

  window.FormNav = { attach: attachFormNav, focusNext, triggerSave, initAll };

}(window));
