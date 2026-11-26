/* JobLink Rwanda — progressive enhancement. The app works without JavaScript. */
(function () {
  'use strict';

  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  /* ---------- Theme (light / dark) ---------- */
  const stored = localStorage.getItem('joblink-theme');
  const theme = stored || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  document.documentElement.setAttribute('data-theme', theme);
  $$('.theme-toggle').forEach(btn => btn.addEventListener('click', () => {
    const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('joblink-theme', next);
    updateThemeIcons();
  }));
  function updateThemeIcons() {
    const dark = document.documentElement.getAttribute('data-theme') === 'dark';
    $$('.theme-toggle').forEach(btn => {
      btn.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
      btn.innerHTML = dark
        ? '<svg class="icon" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>'
        : '<svg class="icon" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 14A8 8 0 0 1 10 4a8 8 0 1 0 10 10Z"/></svg>';
    });
  }
  updateThemeIcons();

  /* ---------- Responsive navigation ---------- */
  const toggle = $('.nav-toggle');
  if (toggle) toggle.addEventListener('click', () => $('.nav-links')?.classList.toggle('open'));

  /* ---------- Toasts from server flash messages ---------- */
  const stack = $('.flash-stack');
  if (stack) {
    $$('.flash', stack).forEach(el => setTimeout(() => {
      el.style.opacity = '0';
      el.style.transform = 'translateX(20px)';
      setTimeout(() => el.remove(), 250);
    }, 6000));
  }
  function toast(message, type = 'success') {
    if (!stack) return;
    const el = document.createElement('div');
    el.className = 'flash ' + type;
    el.textContent = message;
    stack.appendChild(el);
    setTimeout(() => el.remove(), 5000);
  }

  /* ---------- Confirmation dialogs ---------- */
  document.addEventListener('submit', event => {
    const form = event.target;
    if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
      event.preventDefault();
    }
  });
  $$('[data-confirm-link]').forEach(el => el.addEventListener('click', e => {
    if (!window.confirm(el.dataset.confirmLink)) e.preventDefault();
  }));

  /* ---------- Notifications ---------- */
  const notifBtn = $('#notif-toggle');
  const notifPanel = $('#notif-panel');
  if (notifBtn && notifPanel) {
    notifBtn.addEventListener('click', async e => {
      e.preventDefault();
      const opening = notifPanel.classList.contains('hide');
      notifPanel.classList.toggle('hide');
      if (opening && !notifPanel.dataset.loaded) {
        try {
          const response = await fetch(METABASE + '/api/notifications', { headers: { 'Accept': 'application/json' } });
          const data = await response.json();
          $('#notif-list').innerHTML = data.notifications.length
            ? data.notifications.map(n => `<a class="notif-item ${n.is_read ? '' : 'unread'}" href="${esc(n.link || '#')}"><strong>${esc(n.title)}</strong><span>${esc(n.body || '')}</span></a>`).join('')
            : '<div class="small muted" style="padding:.8rem">You are all caught up.</div>';
          notifPanel.dataset.loaded = '1';
        } catch (err) { toast('Could not load notifications.', 'error'); }
      }
    });
    document.addEventListener('click', e => {
      if (!notifPanel.contains(e.target) && e.target !== notifBtn) notifPanel.classList.add('hide');
    });
  }

  function esc(value) {
    return String(value ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  }

  /* ---------- Save job without a page reload ---------- */
  $$('.save-btn[data-save-url]').forEach(btn => btn.addEventListener('click', async e => {
    e.preventDefault();
    try {
      const body = new FormData();
      body.append('_csrf', META_CSRF);
      const response = await fetch(btn.dataset.saveUrl, { method: 'POST', body });
      const data = await response.json();
      if (data.ok) {
        btn.classList.toggle('saved', data.saved);
        btn.setAttribute('aria-pressed', String(data.saved));
        toast(data.saved ? 'Job saved.' : 'Job removed from saved jobs.');
      } else if (data.loginRequired) {
        window.location.href = METABASE + '/login';
      }
    } catch (err) { toast('Could not update saved jobs.', 'error'); }
  }));

  /* ---------- Live job filtering (progressive enhancement) ---------- */
  const jobResults = $('#job-results');
  if (jobResults && jobResults.dataset.url) {
    let timer;
    const load = () => {
      const params = new URLSearchParams();
      $$('#job-filter-form input, #job-filter-form select').forEach(field => {
        if (field.name && field.value.trim()) params.set(field.name, field.value.trim());
      });
      fetch(jobResults.dataset.url + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
          jobResults.innerHTML = data.html;
          const count = $('#result-count');
          if (count) count.textContent = data.total;
        })
        .catch(() => {});
    };
    $$('#job-filter-form input, #job-filter-form select').forEach(field => {
      field.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(load, 350); });
      field.addEventListener('change', load);
    });
  }

  /* ---------- CV builder repeatable sections ---------- */
  $$('[data-repeater]').forEach(root => {
    const template = $('template', root);
    const container = $('[data-repeater-items]', root);
    $('[data-repeater-add]', root)?.addEventListener('click', () => {
      if (!template || !container) return;
      const frag = template.content.cloneNode(true);
      container.appendChild(frag);
    });
    container?.addEventListener('click', e => {
      const remove = e.target.closest('[data-repeater-remove]');
      if (remove) { e.preventDefault(); remove.closest('.repeater-item')?.remove(); }
    });
  });

  /* ---------- Share / copy links ---------- */
  $$('[data-copy]').forEach(btn => btn.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(btn.dataset.copy);
      toast('Link copied to clipboard.');
    } catch (err) { toast('Copy failed — select and copy the URL manually.', 'error'); }
  }));

  /* ---------- Search suggestions ---------- */
  const suggestionInput = $('[data-suggestions]');
  if (suggestionInput) {
    const box = $('#suggestion-box');
    let timer;
    suggestionInput.addEventListener('input', () => {
      clearTimeout(timer);
      timer = setTimeout(async () => {
        if (suggestionInput.value.length < 2) { box?.classList.add('hide'); return; }
        try {
          const res = await fetch(METABASE + '/api/jobs?q=' + encodeURIComponent(suggestionInput.value) + '&limit=5', { headers: { 'Accept': 'application/json' } });
          const data = await res.json();
          if (!box) return;
          box.innerHTML = (data.rows || []).map(j => `<a href="${METABASE}/jobs/${j.id}/${encodeURIComponent(j.slug)}">${esc(j.title)}</a>`).join('');
          box.classList.toggle('hide', !(data.rows || []).length);
        } catch (err) { /* suggestions are optional */ }
      }, 250);
    });
  }

  /* ---------- PWA ---------- */
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => navigator.serviceWorker.register(METABASE + '/sw.js').catch(() => {}));
  }
})();
