/**
 * app.js — Profix Global Application Logic
 * Handles: sidebar, search, notifications, toast, user menu, mobile
 */
(function () {
  'use strict';

  /* ---*/
  window.showToast = function (msg, type, duration) {
    type = type || 'info'; duration = duration ?? 3000;
    var container = document.getElementById('toastContainer');
    if (!container) return;
    var t = document.createElement('div');
    t.className = 'toast ' + type;
    var icons = { success: '&#10003;', error: '&#10007;', warning: '&#9888;', info: '&#8505;' };
    t.innerHTML = '<span>' + (icons[type] || '') + '</span><span>' + msg + '</span>';
    container.appendChild(t);
    if (duration > 0) {
      setTimeout(function () { t.style.opacity = '0'; t.style.transform = 'translateX(100%)'; t.style.transition = '.3s'; setTimeout(function () { t.remove(); }, 300); }, duration);
    }
    return t;
  };

  /* ---*/
  window.toggleMobileMenu = function () {
    var sb = document.getElementById('mainSidebar');
    if (sb && sb.classList.contains('open')) closeSidebar();
    else openSidebar();
  };

  window.openSidebar = function () {
    var sb = document.getElementById('mainSidebar');
    var ov = document.getElementById('sidebarOverlay');
    if (sb) sb.classList.add('open');
    if (ov) ov.classList.add('active');
    document.body.style.overflow = 'hidden';
  };
  window.closeSidebar = function () {
    var sb = document.getElementById('mainSidebar');
    var ov = document.getElementById('sidebarOverlay');
    if (sb) sb.classList.remove('open');
    if (ov) ov.classList.remove('active');
    document.body.style.overflow = '';
  };

  /* ---*/
  function updateMobileUI() {
    var btn = document.getElementById('mobileMenuBtn');
    if (!btn) return;
    if (window.innerWidth <= 768) {
      btn.style.display = 'flex';
    } else {
      btn.style.display = 'none';
      closeSidebar();
    }
  }
  window.addEventListener('resize', updateMobileUI);
  document.addEventListener('DOMContentLoaded', updateMobileUI);

  /* ---*/
  var searchInput = null;
  var searchDropdown = null;
  var searchTimeout = null;

  function initSearch() {
    searchInput    = document.getElementById('globalSearchInput');
    searchDropdown = document.getElementById('searchDropdown');
    if (!searchInput || !searchDropdown) return;

    searchInput.addEventListener('input', function () {
      clearTimeout(searchTimeout);
      var q = this.value.trim();
      if (q.length < 2) { searchDropdown.style.display = 'none'; return; }
      searchTimeout = setTimeout(function () { doSearch(q); }, 300);
    });

    searchInput.addEventListener('blur', function () {
      setTimeout(function () { if (searchDropdown) searchDropdown.style.display = 'none'; }, 200);
    });

    // Ctrl+K shortcut
    document.addEventListener('keydown', function (e) {
      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        if (searchInput) { searchInput.focus(); searchInput.select(); }
      }
    });
  }

  function doSearch(q) {
    fetch(BASE_PATH + '/api/index.php?action=global_search&q=' + encodeURIComponent(q))
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.success || !data.data.length) {
          searchDropdown.innerHTML = '<div style="padding:16px;text-align:center;color:var(--text-muted);font-size:13px;">No results found</div>';
        } else {
          var html = '';
          var types = { project: '&#128193;', contractor: '&#128736;', purchase: '&#128722;' };
          var colors = { project: 'var(--info-bg)', contractor: 'var(--warning-bg)', purchase: 'var(--success-bg)' };
          var last = '';
          data.data.forEach(function (item) {
            if (item.type !== last) {
              html += '<div class="search-dropdown-section">' + item.type.charAt(0).toUpperCase() + item.type.slice(1) + 's</div>';
              last = item.type;
            }
            var href = item.type === 'project' ? BASE_PATH + '/project-detail?id=' + item.id
                     : item.type === 'contractor' ? '/contractors'
                     : BASE_PATH + '/project-detail?id=' + (item.project_id || '');
            html += '<a href="' + href + '" class="search-dropdown-item">'
              + '<div class="search-icon" style="background:' + (colors[item.type] || '#eee') + '">' + (types[item.type] || '?') + '</div>'
              + '<div><div class="search-label">' + escHtml(item.label) + '</div>'
              + '<div class="search-sub">' + escHtml(item.sub || '') + '</div></div>'
              + '</a>';
          });
          searchDropdown.innerHTML = html;
        }
        searchDropdown.style.display = 'block';
      })
      .catch(function () { searchDropdown.style.display = 'none'; });
  }

  /* ---*/
  function loadNotifCount() {
    fetch(BASE_PATH + '/api/notifications.php?action=count_unread')
      .then(function (r) { return r.json(); })
      .then(function (d) {
        var badge = document.getElementById('notifBadge');
        if (!badge) return;
        if (d.count > 0) { badge.textContent = d.count; badge.style.display = 'flex'; }
        else { badge.style.display = 'none'; }
      }).catch(function () {});
  }

  /* ---*/
  function initUserMenu() {
    var btn = document.getElementById('userMenuBtn');
    if (!btn) return;
    btn.addEventListener('click', function () {
      var existing = document.getElementById('userMenuDropdown');
      if (existing) { existing.remove(); return; }
      var rect = btn.getBoundingClientRect();
      var dd = document.createElement('div');
      dd.id = 'userMenuDropdown';
      dd.style.cssText = 'position:fixed;top:' + (rect.bottom + 8) + 'px;right:' + (window.innerWidth - rect.right) + 'px;background:var(--card-bg);border-radius:var(--radius-md);box-shadow:var(--shadow-lg);border:1px solid var(--border);z-index:9999;min-width:180px;overflow:hidden;animation:fadeIn .2s ease;';
      dd.innerHTML = [
        '<a href="" + BASE_PATH + "/settings" style="display:flex;align-items:center;gap:10px;padding:12px 16px;font-size:13px;color:var(--text-primary);transition:background .15s;" onmouseover="this.style.background=\'var(--border-light)\'" onmouseout="this.style.background=\'\'">&#9881; Settings</a>',
        '<a href="" + BASE_PATH + "/backup"   style="display:flex;align-items:center;gap:10px;padding:12px 16px;font-size:13px;color:var(--text-primary);transition:background .15s;" onmouseover="this.style.background=\'var(--border-light)\'" onmouseout="this.style.background=\'\'">&#128274; Backup Data</a>',
        '<div style="height:1px;background:var(--border);"></div>',
        '<a href="#" onclick="doLogout();return false;" style="display:flex;align-items:center;gap:10px;padding:12px 16px;font-size:13px;color:var(--danger);transition:background .15s;" onmouseover="this.style.background=\'var(--danger-bg)\'" onmouseout="this.style.background=\'\'">&#10006; Logout</a>',
      ].join('');
      document.body.appendChild(dd);
      setTimeout(function () { document.addEventListener('click', function h(e) { if (!dd.contains(e.target) && e.target !== btn) { dd.remove(); document.removeEventListener('click', h); } }); }, 10);
    });
  }

  window.doLogout = function () {
    fetch(BASE_PATH + '/api/index.php?action=logout').then(function () { window.location = BASE_PATH + '/login'; });
  };

  /* ---*/
  function initQuickAdd() {
    var btn = document.getElementById('quickAddBtn');
    if (!btn) return;
    btn.addEventListener('click', function () {
      var existing = document.getElementById('quickAddDropdown');
      if (existing) { existing.remove(); return; }
      var rect = btn.getBoundingClientRect();
      var dd = document.createElement('div');
      dd.id = 'quickAddDropdown';
      dd.style.cssText = 'position:fixed;top:' + (rect.bottom + 8) + 'px;right:' + (window.innerWidth - rect.right) + 'px;background:var(--card-bg);border-radius:var(--radius-md);box-shadow:var(--shadow-lg);border:1px solid var(--border);z-index:9999;min-width:190px;overflow:hidden;animation:fadeIn .2s ease;';
      var items = [
        ['&#128722; Quick Purchase', '/quick-purchase'],
        ['&#128736; Add Labor',      '/daily-labor'],
        ['&#128197; Add Schedule',   '#schedule'],
        ['&#128193; New Project',    '/projects#new'],
      ];
      dd.innerHTML = '<div style="padding:8px 14px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-light);background:var(--border-light);">Quick Add</div>'
        + items.map(function (i) {
          return '<a href="' + i[1] + '" style="display:flex;align-items:center;gap:10px;padding:11px 16px;font-size:13px;color:var(--text-primary);transition:background .15s;" onmouseover="this.style.background=\'var(--border-light)\'" onmouseout="this.style.background=\'\'">' + i[0] + '</a>';
        }).join('');
      document.body.appendChild(dd);
      setTimeout(function () { document.addEventListener('click', function h(e) { if (!dd.contains(e.target) && e.target !== btn) { dd.remove(); document.removeEventListener('click', h); } }); }, 10);
    });
  }

  /* ---*/
  function escHtml(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  window.formatMoney = function (n) {
    return 'Tk. ' + parseFloat(n || 0).toLocaleString('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
  };

  window.formatDate = function (d) {
    if (!d) return '-';
    var dt = new Date(d);
    return isNaN(dt) ? d : dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
  };

  window.confirmDelete = function (msg, callback) {
    if (typeof PasswordConfirm !== 'undefined') {
      PasswordConfirm.require(msg || 'Delete this record', callback);
    } else if (confirm(msg || 'Are you sure you want to delete?')) {
      callback();
    }
  };

  window.openModal = function (id) {
    var m = document.getElementById(id);
    if (m) { m.classList.add('active'); document.body.style.overflow = 'hidden'; }
  };
  window.closeModal = function (id) {
    var m = document.getElementById(id);
    if (m) { m.classList.remove('active'); document.body.style.overflow = ''; }
  };
  // Close modal on overlay click
  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal-overlay')) {
      e.target.classList.remove('active');
      document.body.style.overflow = '';
    }
  });
  // Close modal on Escape
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay.active').forEach(function (m) {
        m.classList.remove('active');
      });
      document.body.style.overflow = '';
    }
  });

  /* ---*/
  document.addEventListener('DOMContentLoaded', function () {
    initSearch();
    initUserMenu();
    initQuickAdd();
    loadNotifCount();
    // Animate page content
    var main = document.getElementById('mainContent');
    if (main) main.classList.add('animate-fade-in');
  });
  /* --- SPA ROUTER (PJAX) --- */
  window.navigateTo = async function (url, push = true) {
    var main = document.getElementById('mainContent');
    if (!main) { window.location.href = url; return; }
    
    // Rescue right panel if it was moved inside mainContent (mobile dashboard)
    var rp = document.getElementById('rightPanel');
    if (rp && main.contains(rp)) {
      document.querySelector('.app-layout').appendChild(rp);
      rp.removeAttribute('style');
    }
    
    main.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:50vh;"><div class="spinner" style="width:40px;height:40px;border:3px solid var(--border);border-top-color:var(--primary);border-radius:50%;animation:spin 1s linear infinite;"></div></div>';
    
    try {
      const fetchUrl = url + (url.includes('?') ? '&' : '?') + '_pjax=' + Date.now();
      const res = await fetch(fetchUrl);
      const html = await res.text();
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      
      const newMain = doc.getElementById('mainContent');
      if (!newMain) { window.location.href = url; return; }
      
      main.innerHTML = newMain.innerHTML;
      main.className = newMain.className;
      
      document.title = doc.title;
      var newTitle = doc.getElementById('topbarTitle');
      if (newTitle) {
        var currentTitle = document.getElementById('topbarTitle');
        if (currentTitle) currentTitle.innerHTML = newTitle.innerHTML;
        var mobileTitle = document.querySelector('.mobile-greeting-title');
        if (mobileTitle) mobileTitle.innerHTML = newTitle.innerHTML;
      }
      
      document.querySelectorAll('.sidebar-nav-item').forEach(function(el) { el.classList.remove('active'); });
      var newActive = doc.querySelector('.sidebar-nav-item.active');
      if (newActive) {
        var activeHref = newActive.getAttribute('href');
        var currentLink = document.querySelector('.sidebar-nav-item[href="' + activeHref + '"]');
        if (currentLink) currentLink.classList.add('active');
      }
      
      var scripts = newMain.querySelectorAll('script');
      scripts.forEach(function(s) {
        var newScript = document.createElement('script');
        if (s.src) {
          newScript.src = s.src;
        } else {
          var content = s.textContent;
          // Intercept DOMContentLoaded so it runs immediately during PJAX load
          content = content.replace(/document\.addEventListener\(\s*['"]DOMContentLoaded['"]\s*,\s*/g, 'window._runPjaxInit(');
          // Convert const/let to var to prevent "Identifier already declared" SyntaxError on PJAX re-entry
          content = content.replace(/\bconst\s+/g, 'var ').replace(/\blet\s+/g, 'var ');
          newScript.textContent = content;
        }
        document.body.appendChild(newScript);
      });
      
      if (push) window.history.pushState({}, '', url);
      if (window.innerWidth <= 768) closeSidebar();
      
    } catch (e) {
      console.error('SPA Error:', e);
      window.location.href = url;
    }
  };

  // Helper to run DOMContentLoaded callbacks immediately during PJAX
  window._runPjaxInit = function(callback) {
    if (typeof callback === 'function') {
      setTimeout(callback, 10);
    }
  };

  document.addEventListener('click', function(e) {
    var link = e.target.closest('a');
    if (!link || !link.href) return;
    if (link.target === '_blank' || link.hasAttribute('download') || link.getAttribute('href').startsWith('#') || link.getAttribute('href').startsWith('javascript:')) return;
    if (e.ctrlKey || e.metaKey || e.shiftKey) return;
    
    var url = new URL(link.href);
    if (url.origin === window.location.origin && url.pathname.startsWith(BASE_PATH)) {
      e.preventDefault();
      navigateTo(url.pathname + url.search);
    }
  });

  window.addEventListener('popstate', function() {
    navigateTo(window.location.pathname + window.location.search, false);
  });

}());
