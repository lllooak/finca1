/* xbt.co.il - front-end behaviour */
(function () {
  'use strict';

  // ---------- Global search autocomplete ----------
  const search = document.getElementById('globalSearch');
  const results = document.getElementById('searchResults');
  let timer = null;

  function doSearch(q) {
    fetch('/api/search?q=' + encodeURIComponent(q))
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!results) return;
        const items = data.results || [];
        if (!items.length) {
          results.innerHTML = '<div class="sr-item"><span>לא נמצאו תוצאות</span></div>';
        } else {
          results.innerHTML = items.map(function (it) {
            return '<a class="sr-item" href="' + it.url + '">' +
              '<span><strong>' + escapeHtml(it.label) + '</strong> ' +
              (it.ticker ? '<span class="text-soft">' + escapeHtml(it.ticker) + '</span>' : '') + '</span>' +
              '<span class="sr-type">' + escapeHtml(it.type) + '</span></a>';
          }).join('');
        }
        results.classList.add('show');
      })
      .catch(function () {});
  }

  function escapeHtml(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  if (search) {
    search.addEventListener('input', function () {
      const q = this.value.trim();
      clearTimeout(timer);
      if (q.length < 2) { results.classList.remove('show'); return; }
      timer = setTimeout(function () { doSearch(q); }, 220);
    });
    document.addEventListener('click', function (e) {
      if (results && !results.contains(e.target) && e.target !== search) {
        results.classList.remove('show');
      }
    });
  }

  // ---------- Top ticker tape ----------
  const ticker = document.getElementById('topTicker');
  if (ticker) {
    fetch('/api/market/movers')
      .then(function (r) { return r.json(); })
      .then(function (data) {
        const items = (data.ticker || []);
        ticker.innerHTML = items.map(function (t) {
          const cls = t.change >= 0 ? 'text-up' : 'text-down';
          const arrow = t.change >= 0 ? '▲' : '▼';
          return '<span class="tk-item"><strong>' + escapeHtml(t.symbol) + '</strong> ' +
            escapeHtml(t.price) + ' <span class="' + cls + '">' + arrow + ' ' +
            Math.abs(t.change).toFixed(2) + '%</span></span>';
        }).join('');
      })
      .catch(function () {});
  }

  // ---------- Charts ----------
  window.xbtChart = function (canvasId, labels, data, label, color) {
    const el = document.getElementById(canvasId);
    if (!el || typeof Chart === 'undefined') return;
    const ctx = el.getContext('2d');
    const grad = ctx.createLinearGradient(0, 0, 0, 280);
    color = color || '#1457e6';
    grad.addColorStop(0, color + '40');
    grad.addColorStop(1, color + '00');
    return new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: label || '',
          data: data,
          borderColor: color,
          backgroundColor: grad,
          borderWidth: 2,
          fill: true,
          tension: 0.25,
          pointRadius: 0,
          pointHoverRadius: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } },
          y: { position: 'left', grid: { color: '#eef1f6' } }
        }
      }
    });
  };

  window.xbtDoughnut = function (canvasId, labels, data) {
    const el = document.getElementById(canvasId);
    if (!el || typeof Chart === 'undefined') return;
    const palette = ['#1457e6', '#00b386', '#e0a106', '#d23047', '#7b61ff', '#0fb5c9', '#ff7a45', '#52c41a', '#eb2f96', '#8c8c8c'];
    return new Chart(el.getContext('2d'), {
      type: 'doughnut',
      data: { labels: labels, datasets: [{ data: data, backgroundColor: palette, borderWidth: 1 }] },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } } }
      }
    });
  };

  // ---------- Cookie consent ----------
  (function () {
    var banner = document.getElementById('cookieConsent');
    if (!banner) return;
    var KEY = 'xbt_cookie_consent';
    var stored;
    try { stored = localStorage.getItem(KEY); } catch (e) { stored = 'accepted'; }
    if (!stored) {
      banner.hidden = false;
      // allow CSS transition on next frame
      requestAnimationFrame(function () { banner.classList.add('show'); });
    }
    banner.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-cookie]');
      if (!btn) return;
      try { localStorage.setItem(KEY, btn.getAttribute('data-cookie') === 'accept' ? 'accepted' : 'declined'); } catch (err) {}
      banner.classList.remove('show');
      setTimeout(function () { banner.hidden = true; }, 300);
    });
  })();

  // Auto-init charts declared via data attributes
  document.querySelectorAll('[data-chart]').forEach(function (el) {
    try {
      const cfg = JSON.parse(el.getAttribute('data-chart'));
      if (cfg.type === 'doughnut') {
        window.xbtDoughnut(el.id, cfg.labels, cfg.data);
      } else {
        window.xbtChart(el.id, cfg.labels, cfg.data, cfg.label, cfg.color);
      }
    } catch (e) {}
  });
})();
