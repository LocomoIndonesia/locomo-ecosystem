/**
 * Unimart Canvas Engine
 * Single Canvas Shell — JSON-Driven Layout & Command System
 */

'use strict';

const Canvas = (() => {

  // ─── State ───────────────────────────────────────────────────────────────
  let state = {
    boot: null,
    currentScreen: 'home',
    cartItems: [],
    cartTotal: 0,
    pollTimer: null,
    lastOrderCount: 0,
    sheetOpen: false,
    helpOpen: false,
  };

  // ─── DOM Refs ─────────────────────────────────────────────────────────────
  const $ = id => document.getElementById(id);
  const $$ = sel => document.querySelectorAll(sel);

  // ═══════════════════════════════════════════════════════════════════════════
  // MODULE: Boot
  // ═══════════════════════════════════════════════════════════════════════════
  const Boot = {
    init() {
      state.boot = window.CANVAS_BOOT || {};
      const cfg = state.boot.config || {};

      Topbar.render();
      Nav.render();
      CmdBar.init();
      HelpPanel.init();
      Sheet.init();
      Toast.init();

      // Load initial screen
      const initial = cfg.initial_screen || 'home';
      if (initial === 'kds_redirect') {
        window.location.href = '/canvas/kds';
        return;
      }
      LayoutLoader.load(initial);

      // Start order polling
      if (cfg.poll_interval_ms) {
        Poll.start(cfg.poll_interval_ms);
      }

      // Push notification registration
      Push.init(cfg.push_vapid_key);
    }
  };

  // ═══════════════════════════════════════════════════════════════════════════
  // MODULE: Topbar
  // ═══════════════════════════════════════════════════════════════════════════
  const Topbar = {
    render() {
      const el = $('canvas-topbar');
      if (!el) return;
      const { user, tenant } = state.boot;
      el.innerHTML = `
        <div class="topbar-brand">
          <div class="topbar-logo">${tenant?.logo
            ? `<img src="${tenant.logo}" alt="${tenant.name}">`
            : '🏪'}</div>
          <div>
            <div class="topbar-store-name">${tenant?.name || 'Unimart'}</div>
          </div>
        </div>
        <div class="topbar-actions">
          <button class="topbar-icon-btn" id="topbar-notif-btn" aria-label="Notifikasi"
            onclick="Canvas.cmd('notifikasi')">
            🔔
            <span class="notif-badge" id="notif-badge" style="display:none"></span>
          </button>
          <button class="topbar-icon-btn" aria-label="Profil" style="font-size:14px;font-weight:600;background:var(--c-primary-dim);color:var(--c-primary-light);border-radius:50%;width:40px;height:40px">
            ${(user?.name || 'U')[0].toUpperCase()}
          </button>
        </div>
      `;
    }
  };

  // ═══════════════════════════════════════════════════════════════════════════
  // MODULE: Nav (Bottom Navigation / Sidebar)
  // ═══════════════════════════════════════════════════════════════════════════
  const Nav = {
    tabs: {
      owner: [
        { screen: 'home',      icon: '🏠', label: 'Beranda' },
        { screen: 'orders',    icon: '📋', label: 'Pesanan',  badge: 'orders_pending' },
        { screen: 'pos',       icon: '💳', label: 'Kasir' },
        { screen: 'dashboard', icon: '📊', label: 'Laporan' },
        { screen: 'settings',  icon: '⚙️', label: 'Lainnya' },
      ],
      staff: [
        { screen: 'home',   icon: '🏠', label: 'Beranda' },
        { screen: 'orders', icon: '📋', label: 'Pesanan', badge: 'orders_pending' },
        { screen: 'pos',    icon: '💳', label: 'Kasir' },
        { screen: 'shift',  icon: '🔓', label: 'Shift' },
      ],
    },

    render() {
      const el = $('canvas-nav');
      if (!el) return;
      const role = state.boot?.user?.role || 'staff';
      const tabs = this.tabs[role] || this.tabs.staff;

      el.innerHTML = tabs.map(t => `
        <button class="nav-tab ${state.currentScreen === t.screen ? 'active' : ''}"
          data-screen="${t.screen}"
          onclick="Canvas.loadScreen('${t.screen}')"
          aria-label="${t.label}">
          <span class="nav-tab-icon">${t.icon}</span>
          <span class="nav-tab-label">${t.label}</span>
          ${t.badge ? `<span class="nav-tab-badge" id="nav-badge-${t.badge}" style="display:none">0</span>` : ''}
        </button>
      `).join('');
    },

    setActive(screen) {
      $$('.nav-tab').forEach(t => {
        t.classList.toggle('active', t.dataset.screen === screen);
      });
    },

    updateBadge(key, count) {
      const el = $(`nav-badge-${key}`);
      if (!el) return;
      el.textContent = count > 0 ? (count > 99 ? '99+' : count) : '';
      el.style.display = count > 0 ? 'flex' : 'none';
    }
  };

  // ═══════════════════════════════════════════════════════════════════════════
  // MODULE: LayoutLoader — Fetch JSON layout & render widgets
  // ═══════════════════════════════════════════════════════════════════════════
  const LayoutLoader = {
    cache: {},

    async load(screen, params = {}) {
      state.currentScreen = screen;
      Nav.setActive(screen);
      document.title = `${screen.charAt(0).toUpperCase() + screen.slice(1)} — ${state.boot?.tenant?.name || 'Unimart'}`;

      const content = $('canvas-content');
      if (!content) return;

      // Show skeleton
      content.innerHTML = `<div class="canvas-screen">${this.skeleton()}</div>`;

      try {
        // Fetch layout definition
        const layout = await this.fetchLayout(screen);
        // Fetch data
        const data = layout.data_api
          ? await DataFetcher.get(layout.data_api, params)
          : {};

        // Render
        content.innerHTML = `<div class="canvas-screen" id="screen-${screen}"></div>`;
        const screenEl = content.querySelector('.canvas-screen');
        await WidgetRenderer.renderAll(layout.widgets || [], data, screenEl, layout);

        // FAB
        this.renderFAB(layout.fab);

        // Start screen-level polling if needed
        if (layout.poll_interval_ms) {
          Poll.startScreen(screen, layout.poll_interval_ms, layout.poll_widgets, data);
        }

        // Auto-open sheet if specified
        if (params.auto_open_sheet && layout.sheets?.[params.auto_open_sheet]) {
          Sheet.open(params.auto_open_sheet, layout.sheets[params.auto_open_sheet], data);
        }

      } catch (err) {
        console.error('LayoutLoader error:', err);
        content.innerHTML = `<div class="canvas-screen">
          <div class="canvas-empty">
            <div class="canvas-empty-icon">⚠️</div>
            <div class="canvas-empty-title">Gagal memuat halaman</div>
            <div class="canvas-empty-desc">${err.message}</div>
            <button class="btn btn-outline" style="width:auto;margin-top:8px"
              onclick="Canvas.loadScreen('${screen}')">Coba Lagi</button>
          </div>
        </div>`;
      }
    },

    async fetchLayout(screen) {
      if (this.cache[screen]) return this.cache[screen];
      const res = await fetch(`/canvas/api/layout?screen=${screen}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!res.ok) throw new Error(`Layout '${screen}' tidak ditemukan`);
      const layout = await res.json();
      this.cache[screen] = layout;
      return layout;
    },

    renderFAB(fab) {
      const existing = document.querySelector('.canvas-fab');
      if (existing) existing.remove();
      if (!fab) return;
      const btn = document.createElement('button');
      btn.className = 'canvas-fab';
      btn.setAttribute('aria-label', fab.label || 'Tambah');
      btn.innerHTML = fab.icon || '+';
      btn.onclick = () => CommandParser.execute(fab.action || '');
      document.body.appendChild(btn);
    },

    skeleton() {
      return `
        <div class="skeleton skeleton-card" style="height:120px;margin-bottom:0"></div>
        <div class="metric-row">
          ${Array(4).fill('<div class="skeleton skeleton-card" style="height:80px"></div>').join('')}
        </div>
        <div style="display:flex;flex-direction:column;gap:8px">
          ${Array(3).fill('<div class="skeleton skeleton-card"></div>').join('')}
        </div>
      `;
    }
  };

  // ═══════════════════════════════════════════════════════════════════════════
  // MODULE: WidgetRenderer — Factory pattern per widget type
  // ═══════════════════════════════════════════════════════════════════════════
  const WidgetRenderer = {
    async renderAll(widgets, data, container, layout) {
      for (const widget of widgets) {
        const el = await this.render(widget, data, layout);
        if (el) container.appendChild(el);
      }
    },

    async render(widget, data, layout) {
      const widgetData = widget.data_key ? (widget.data_key === 'root' ? data : data[widget.data_key]) : data;

      try {
        switch (widget.type) {
          case 'greeting_card':   return this.greeting(data);
          case 'metric_row':      return this.metricRow(widget, widgetData);
          case 'kpi_carousel':    return this.kpiCarousel(widget, widgetData);
          case 'command_pills':   return this.commandPills(widget);
          case 'activity_feed':   return this.activityFeed(widget, widgetData);
          case 'chart_sparkline': return this.sparkline(widget, widgetData);
          case 'ranked_list':     return this.rankedList(widget, widgetData);
          case 'search_input':    return this.searchInput(widget);
          case 'filter_chips':    return this.filterChips(widget, data);
          case 'product_grid':    return this.productGrid(widget, widgetData, layout);
          case 'sticky_cart_bar': return this.cartBar(widget);
          case 'order_card_list': return this.orderCardList(widget, widgetData, layout);
          case 'menu_card_list':  return this.menuCardList(widget, widgetData, layout);
          case 'pnl_waterfall':   return this.pnlWaterfall(widget, widgetData);
          case 'stat_row':        return this.statRow(widget, widgetData);
          case 'alert_banner':    return this.alertBanner(widget, widgetData);
          case 'action_row':      return this.actionRow(widget);
          case 'date_range_picker': return this.datePicker(widget);
          case 'push_permission_card': return this.pushPermission(widget);
          case 'shift_status_card': return this.shiftStatus(widget, widgetData);
          case 'table_status_grid': return this.tableGrid(widget, widgetData, layout);
          case 'staff_card_list': return this.staffCardList(widget, widgetData, layout);
          case 'supplier_card_list': return this.supplierCardList(widget, widgetData, layout);
          case 'inventory_card_list': return this.inventoryCardList(widget, widgetData, layout);
          case 'purchase_card_list': return this.purchaseCardList(widget, widgetData, layout);
          case 'payable_card_list': return this.payableCardList(widget, widgetData, layout);
          case 'receivable_card_list': return this.receivableCardList(widget, widgetData, layout);
          case 'notification_list': return this.notificationList(widget, widgetData);
          default:
            console.warn(`Widget type '${widget.type}' belum diimplementasikan`);
            return null;
        }
      } catch (e) {
        console.error(`Widget '${widget.type}' error:`, e);
        return null;
      }
    },

    // ── Greeting ──────────────────────────────────────────────────────
    greeting(data) {
      const { user, tenant } = state.boot;
      const hour = new Date().getHours();
      const salam = hour < 11 ? 'Selamat Pagi' : hour < 15 ? 'Selamat Siang' : hour < 18 ? 'Selamat Sore' : 'Selamat Malam';
      const dateStr = new Date().toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long' });
      const el = document.createElement('div');
      el.className = 'greeting-card';
      el.innerHTML = `
        <div class="greeting-time">☀️ ${salam}, ${dateStr}</div>
        <div class="greeting-name">${user?.name || 'Halo!'} 👋</div>
        <div class="greeting-store">🏪 ${tenant?.name || ''}</div>
      `;
      return el;
    },

    // ── Metric Row ────────────────────────────────────────────────────
    metricRow(widget, data) {
      const el = document.createElement('div');
      el.className = 'metric-row';
      if (!data) { el.innerHTML = '<p class="text-muted">Data tidak tersedia</p>'; return el; }
      el.innerHTML = (widget.metrics || []).map(m => `
        <div class="metric-card">
          <div class="metric-card-icon">${m.icon || ''}</div>
          <div class="metric-card-value">${Fmt.value(data[m.key], m.format)}</div>
          <div class="metric-card-label">${m.label}</div>
          ${m.badge && data[m.key] > 0 ? `<span class="metric-card-badge ${m.badge}">${data[m.key]}</span>` : ''}
        </div>
      `).join('');
      return el;
    },

    // ── KPI Carousel ──────────────────────────────────────────────────
    kpiCarousel(widget, data) {
      const el = document.createElement('div');
      el.className = 'kpi-carousel';
      if (!data) return el;
      el.innerHTML = (widget.metrics || []).map(m => `
        <div class="kpi-card">
          <div class="kpi-card-icon-wrap" style="background:${m.color}22">
            <span>${m.icon}</span>
          </div>
          <div class="kpi-card-value">${Fmt.value(data[m.key], m.format)}</div>
          <div class="kpi-card-label">${m.label}</div>
        </div>
      `).join('');
      return el;
    },

    // ── Command Pills ─────────────────────────────────────────────────
    commandPills(widget) {
      const wrap = document.createElement('div');
      if (widget.title) {
        const title = document.createElement('div');
        title.className = 'section-title';
        title.style.marginBottom = '8px';
        title.textContent = widget.title;
        wrap.appendChild(title);
      }
      const grid = document.createElement('div');
      grid.className = `command-pills-grid ${widget.layout || 'grid_2x2'}`;
      grid.innerHTML = (widget.commands || []).map(c => `
        <button class="cmd-pill" onclick="Canvas.cmd('${c.cmd}')">
          <div class="cmd-pill-icon" style="background:${c.color}22">${c.icon}</div>
          <div class="cmd-pill-label">${c.label}</div>
        </button>
      `).join('');
      wrap.appendChild(grid);
      return wrap;
    },

    // ── Activity Feed ─────────────────────────────────────────────────
    activityFeed(widget, data) {
      const el = document.createElement('div');
      el.className = 'activity-feed';
      el.id = `widget-${widget.id}`;
      if (widget.title) {
        el.innerHTML = `<div class="activity-feed-title">${widget.title}</div>`;
      }
      if (!data || data.length === 0) {
        el.innerHTML += `<div class="canvas-empty" style="padding:24px">
          <div class="canvas-empty-icon">📭</div>
          <div class="canvas-empty-desc">${widget.empty_label || 'Belum ada aktivitas.'}</div>
        </div>`;
        return el;
      }
      el.innerHTML += (Array.isArray(data) ? data : []).map((item, i) => `
        <div class="activity-bubble" style="animation-delay:${i * 50}ms">
          <span class="activity-bubble-icon">${item.icon || '📌'}</span>
          <div class="activity-bubble-body">
            <div class="activity-bubble-label">${item.label}</div>
            <div class="activity-bubble-meta">
              <span class="activity-bubble-amount">${item.amount ? Fmt.currency(item.amount) : ''}</span>
              <span class="activity-bubble-time">${item.time || ''}</span>
            </div>
          </div>
        </div>
      `).join('');
      return el;
    },

    // ── Sparkline Chart (Canvas 2D) ───────────────────────────────────
    sparkline(widget, data) {
      const wrap = document.createElement('div');
      wrap.innerHTML = `<div class="section-header"><div class="section-title">${widget.title}</div></div>`;
      const canvas = document.createElement('canvas');
      canvas.height = 80;
      canvas.style.width = '100%';
      wrap.appendChild(canvas);

      if (!data || !data.length) return wrap;

      requestAnimationFrame(() => {
        const ctx = canvas.getContext('2d');
        if (!ctx) return;
        canvas.width = canvas.offsetWidth || 300;
        const W = canvas.width, H = canvas.height;
        const vals = data.map(d => d[widget.y_key] || 0);
        const max = Math.max(...vals, 1);
        const step = W / (vals.length - 1);
        const pts = vals.map((v, i) => ({ x: i * step, y: H - (v / max) * (H - 8) - 4 }));

        // Gradient fill
        const grad = ctx.createLinearGradient(0, 0, 0, H);
        grad.addColorStop(0, 'rgba(108,58,232,0.3)');
        grad.addColorStop(1, 'rgba(108,58,232,0)');

        ctx.beginPath();
        ctx.moveTo(pts[0].x, pts[0].y);
        pts.slice(1).forEach((p, i) => {
          const cp = pts[i];
          ctx.bezierCurveTo((cp.x + p.x)/2, cp.y, (cp.x + p.x)/2, p.y, p.x, p.y);
        });
        ctx.lineTo(W, H); ctx.lineTo(0, H); ctx.closePath();
        ctx.fillStyle = grad;
        ctx.fill();

        // Line
        ctx.beginPath();
        ctx.moveTo(pts[0].x, pts[0].y);
        pts.slice(1).forEach((p, i) => {
          const cp = pts[i];
          ctx.bezierCurveTo((cp.x + p.x)/2, cp.y, (cp.x + p.x)/2, p.y, p.x, p.y);
        });
        ctx.strokeStyle = widget.color || '#6C3AE8';
        ctx.lineWidth = 2.5;
        ctx.stroke();
      });

      return wrap;
    },

    // ── Ranked List ───────────────────────────────────────────────────
    rankedList(widget, data) {
      const el = document.createElement('div');
      el.innerHTML = `<div class="section-header"><div class="section-title">${widget.title}</div></div>`;
      if (!data || !data.length) {
        el.innerHTML += `<div class="text-muted" style="padding:12px;font-size:13px">Belum ada data</div>`;
        return el;
      }
      const list = document.createElement('div');
      list.style.cssText = 'display:flex;flex-direction:column;gap:6px';
      list.innerHTML = data.map((item, i) => `
        <div style="display:flex;align-items:center;gap:12px;padding:10px 12px;background:var(--c-surface);border:1px solid var(--c-border);border-radius:var(--r-lg)">
          <div style="font-weight:700;color:var(--c-text-muted);font-size:13px;min-width:20px">#${i+1}</div>
          <div style="flex:1;font-size:14px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${item[widget.key_label] || '-'}</div>
          <div style="font-size:13px;color:var(--c-text-secondary)">${item[widget.key_value] || 0}x</div>
          ${widget.key_secondary ? `<div style="font-size:13px;font-weight:600;color:var(--c-success)">${Fmt.value(item[widget.key_secondary], widget.format_secondary)}</div>` : ''}
        </div>
      `).join('');
      el.appendChild(list);
      return el;
    },

    // ── Search Input ──────────────────────────────────────────────────
    searchInput(widget) {
      const el = document.createElement('div');
      el.className = 'canvas-search';
      el.innerHTML = `
        <span class="canvas-search-icon">🔍</span>
        <input class="canvas-search-input" type="search"
          placeholder="${widget.placeholder || 'Cari...'}"
          id="search-${widget.id}"
          autocomplete="off"
          ${widget.auto_focus ? 'autofocus' : ''}>
      `;
      el.querySelector('input').addEventListener('input', Util.debounce(e => {
        DataFetcher.refreshScreen({ [widget.filter_param]: e.target.value });
      }, 350));
      return el;
    },

    // ── Filter Chips ──────────────────────────────────────────────────
    filterChips(widget, data) {
      const el = document.createElement('div');
      el.className = 'filter-chips';
      el.id = `chips-${widget.id}`;

      let options = widget.options || [];

      // Dynamic from data (e.g., categories)
      if (widget.data_source && data[widget.data_source]) {
        options = [
          { value: '', label: widget.all_label || 'Semua' },
          ...data[widget.data_source].map(c => ({ value: c, label: c }))
        ];
      }

      el.innerHTML = options.map((opt, i) => `
        <button class="filter-chip ${i === 0 ? 'active' : ''}"
          data-value="${opt.value}"
          onclick="Canvas._filterChipClick(this, '${widget.filter_param || 'filter'}', '${widget.id}')">
          ${opt.label}
          ${opt.badge_key ? `<span class="filter-chip-badge" id="chip-badge-${opt.value}">0</span>` : ''}
        </button>
      `).join('');

      return el;
    },

    // ── Product Grid ──────────────────────────────────────────────────
    productGrid(widget, data, layout) {
      const el = document.createElement('div');
      el.className = 'product-grid';
      el.id = `widget-${widget.id}`;
      if (!data || !data.length) {
        el.innerHTML = `<div class="canvas-empty" style="grid-column:1/-1">
          <div class="canvas-empty-icon">🍽️</div>
          <div class="canvas-empty-desc">Belum ada produk</div>
        </div>`;
        return el;
      }
      el.innerHTML = data.map(p => `
        <button class="product-card ${!p.is_available ? 'unavailable' : ''}"
          onclick="Canvas._addToCart(${JSON.stringify(p).replace(/"/g, '&quot;')})">
          <div class="product-card-img">${p.image
            ? `<img src="${p.image}" alt="${p.name}" loading="lazy" style="width:100%;height:100%;object-fit:cover">`
            : '<span style="font-size:36px">🍽️</span>'}</div>
          <div class="product-card-body">
            <div class="product-card-name">${p.name}</div>
            <div class="product-card-price">${Fmt.currency(p.price)}</div>
          </div>
          ${!p.is_available ? '<div class="product-unavailable-badge">HABIS</div>' : ''}
        </button>
      `).join('');
      return el;
    },

    // ── Cart Bar ──────────────────────────────────────────────────────
    cartBar(widget) {
      const existing = document.querySelector('.cart-bar-sticky');
      if (existing) existing.remove();
      const el = document.createElement('button');
      el.className = 'cart-bar-sticky';
      el.id = 'canvas-cart-bar';
      el.setAttribute('aria-live', 'polite');
      el.onclick = () => Sheet.openCart();
      el.innerHTML = `
        <div class="cart-bar-info">
          <div class="cart-bar-count" id="cart-count">0 item</div>
          <div class="cart-bar-total" id="cart-total">Rp 0</div>
        </div>
        <div class="cart-bar-action">Bayar →</div>
      `;
      document.body.appendChild(el);
      Cart.updateBar();
      return null; // appended directly to body
    },

    // ── Order Card List ───────────────────────────────────────────────
    orderCardList(widget, data, layout) {
      const el = document.createElement('div');
      el.id = `widget-${widget.id}`;
      el.style.cssText = 'display:flex;flex-direction:column;gap:8px';
      if (!data || !data.length) {
        el.innerHTML = `<div class="canvas-empty">
          <div class="canvas-empty-icon">📭</div>
          <div class="canvas-empty-title">Tidak ada pesanan</div>
        </div>`;
        return el;
      }
      el.innerHTML = data.map(o => `
        <div class="order-card" onclick="Canvas._openOrderDetail(${o.id}, ${JSON.stringify(layout?.sheets || {}).replace(/"/g,'&quot;')})">
          <div class="order-card-header">
            <div>
              <div class="order-card-id">#${o.id}</div>
              <div class="order-card-table">${o.table || 'Take Away'}</div>
            </div>
            <span class="status-badge ${o.status}">${Fmt.status(o.status)}</span>
          </div>
          <div class="order-card-items">${(o.items||[]).map(i=>`${i.qty}x ${i.name}`).join(', ')}</div>
          <div class="order-card-footer">
            <div class="order-card-total">${Fmt.currency(o.total)}</div>
            <div class="order-card-timer">⏱ ${o.duration_minutes || 0} menit</div>
          </div>
        </div>
      `).join('');
      return el;
    },

    // ── Menu Card List ────────────────────────────────────────────────
    menuCardList(widget, data, layout) {
      const el = document.createElement('div');
      el.id = `widget-${widget.id}`;
      el.style.cssText = 'display:flex;flex-direction:column;gap:8px';
      if (!data || !data.length) {
        el.innerHTML = `<div class="canvas-empty"><div class="canvas-empty-icon">🍽️</div><div class="canvas-empty-title">Belum ada menu</div></div>`;
        return el;
      }
      el.innerHTML = data.map(p => `
        <div style="display:flex;align-items:center;gap:12px;padding:12px;background:var(--c-surface);border:1px solid var(--c-border);border-radius:var(--r-xl);cursor:pointer"
          onclick="Canvas._openMenuEdit(${p.id}, '${p.name}')">
          <div style="width:56px;height:56px;border-radius:var(--r-md);background:var(--c-surface-3);display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0">
            ${p.image ? `<img src="${p.image}" style="width:100%;height:100%;object-fit:cover;border-radius:inherit">` : '🍽️'}
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-weight:600;font-size:14px">${p.name}</div>
            <div style="font-size:13px;color:var(--c-text-secondary)">${p.category || ''}</div>
            <div style="font-size:14px;font-weight:700;color:var(--c-primary-light)">${Fmt.currency(p.price)}</div>
          </div>
          <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px">
            <div style="font-size:12px;color:var(--c-text-muted)">Stok: ${p.stock ?? '∞'}</div>
            <button onclick="event.stopPropagation();Canvas._toggleMenu(${p.id}, this)"
              style="width:44px;height:24px;border-radius:12px;background:${p.is_available ? 'var(--c-success)' : 'var(--c-surface-3)'};border:none;cursor:pointer;transition:background 0.2s;position:relative">
              <div style="width:20px;height:20px;border-radius:50%;background:#fff;position:absolute;top:2px;transition:left 0.2s;left:${p.is_available ? '20px' : '2px'}"></div>
            </button>
          </div>
        </div>
      `).join('');
      return el;
    },

    // ── P&L Waterfall ─────────────────────────────────────────────────
    pnlWaterfall(widget, data) {
      const el = document.createElement('div');
      el.className = 'pnl-waterfall';
      if (!data) { el.innerHTML = '<div class="text-muted" style="padding:16px">Data keuangan tidak tersedia</div>'; return el; }
      el.innerHTML = (widget.rows || []).map(r => `
        <div class="pnl-row ${r.type}">
          <div class="pnl-row-label">${r.label}</div>
          <div class="pnl-row-value">${Fmt.value(data[r.key], r.format)}</div>
        </div>
      `).join('');
      return el;
    },

    // ── Stat Row ──────────────────────────────────────────────────────
    statRow(widget, data) {
      const el = document.createElement('div');
      el.style.cssText = 'display:grid;grid-template-columns:repeat(3,1fr);gap:8px';
      if (!data) return el;
      el.innerHTML = (widget.stats || []).map(s => `
        <div style="background:var(--c-surface);border:1px solid var(--c-border);border-radius:var(--r-lg);padding:12px 8px;text-align:center">
          <div style="font-size:20px">${s.icon || ''}</div>
          <div style="font-size:18px;font-weight:700;margin:4px 0">${Fmt.value(data[s.key], s.format)}</div>
          <div style="font-size:11px;color:var(--c-text-muted);font-weight:500">${s.label}</div>
        </div>
      `).join('');
      return el;
    },

    // ── Alert Banner ──────────────────────────────────────────────────
    alertBanner(widget, data) {
      const el = document.createElement('div');
      const colors = { danger: '#EF4444', info: '#3B82F6', warning: '#F59E0B', success: '#10B981' };
      const c = colors[widget.color] || colors.info;
      el.style.cssText = `padding:12px 16px;border-radius:var(--r-lg);background:${c}15;border:1px solid ${c}40;font-size:14px;font-weight:600;color:${c}`;
      el.textContent = (widget.template || '{value}').replace('{value}', Fmt.value(data, widget.format));
      return el;
    },

    // ── Action Row ────────────────────────────────────────────────────
    actionRow(widget) {
      const el = document.createElement('div');
      el.style.cssText = 'display:flex;flex-direction:column;gap:8px';
      el.innerHTML = (widget.actions || []).map(a => `
        <button class="btn btn-${a.variant || 'primary'}"
          onclick="Canvas._handleAction('${a.action || ''}', '${a.api || ''}')">
          ${a.label}
        </button>
      `).join('');
      return el;
    },

    // ── Date Range Picker ─────────────────────────────────────────────
    datePicker(widget) {
      const el = document.createElement('div');
      el.className = 'filter-chips';
      el.innerHTML = (widget.options || []).map((opt, i) => `
        <button class="filter-chip ${opt.value === (widget.default || 'today') ? 'active' : ''}"
          data-value="${opt.value}"
          onclick="Canvas._datePickerClick(this, '${widget.filter_param}')">
          ${opt.label}
        </button>
      `).join('');
      return el;
    },

    // ── Push Permission Card ──────────────────────────────────────────
    pushPermission(widget) {
      const el = document.createElement('div');
      el.className = 'push-permission-card';
      const granted = Notification.permission === 'granted';
      el.innerHTML = `
        <div class="push-permission-icon">🔔</div>
        <div class="push-permission-title">${widget.title}</div>
        <div class="push-permission-desc">${widget.description}</div>
        <button class="btn ${granted ? 'btn-outline' : 'btn-primary'}" style="width:auto;padding:0 24px"
          onclick="Canvas._togglePush(this)">
          ${granted ? (widget.button_disable || 'Nonaktifkan') : (widget.button_enable || 'Aktifkan')}
        </button>
      `;
      return el;
    },

    // ── Shift Status Card ─────────────────────────────────────────────
    shiftStatus(widget, data) {
      const el = document.createElement('div');
      el.style.cssText = 'background:var(--c-surface);border:1px solid var(--c-border);border-radius:var(--r-xl);padding:16px;display:flex;align-items:center;justify-content:space-between';
      el.innerHTML = `
        <div>
          <div style="font-size:13px;color:var(--c-text-muted)">Status Shift</div>
          <div style="font-size:16px;font-weight:700;margin-top:4px">
            ${data?.active_staff > 0 ? '🟢 Shift Aktif' : '🔴 Shift Belum Dibuka'}
          </div>
        </div>
        <button class="btn btn-primary" style="width:auto;padding:0 16px"
          onclick="Canvas.cmd('${data?.active_staff > 0 ? 'tutup shift' : 'buka shift'}')">
          ${data?.active_staff > 0 ? '🔒 Tutup' : '🔓 Buka'}
        </button>
      `;
      return el;
    },

    // ── Table Status Grid ─────────────────────────────────────────────
    tableGrid(widget, data, layout) {
      const el = document.createElement('div');
      el.style.cssText = `display:grid;grid-template-columns:repeat(${widget.columns || 3},1fr);gap:8px`;
      if (!data || !data.length) {
        el.innerHTML = '<div class="text-muted">Belum ada meja</div>';
        return el;
      }
      el.innerHTML = data.map(t => `
        <button style="padding:12px 8px;background:${t.status === 'occupied' ? 'var(--c-danger-dim)' : 'var(--c-success-dim)'};border:2px solid ${t.status === 'occupied' ? 'var(--c-danger)' : 'var(--c-success)'}33;border-radius:var(--r-xl);display:flex;flex-direction:column;align-items:center;gap:6px;cursor:pointer"
          onclick="Canvas._openTableDetail(${t.id})">
          <span style="font-size:24px">🪑</span>
          <span style="font-size:13px;font-weight:600">${t.name}</span>
          <span style="font-size:10px;color:${t.status === 'occupied' ? 'var(--c-danger)' : 'var(--c-success)'};font-weight:600">${t.status === 'occupied' ? 'TERISI' : 'KOSONG'}</span>
        </button>
      `).join('');
      return el;
    },

    // ── Generic card lists (Staff, Supplier, Inventory, etc.) ─────────
    staffCardList(widget, data) {
      return this._genericCardList(data || [], item => `
        <div style="display:flex;align-items:center;gap:12px">
          <div style="width:44px;height:44px;border-radius:50%;background:var(--c-primary-dim);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--c-primary-light);font-size:16px;flex-shrink:0">
            ${(item.name || 'S')[0].toUpperCase()}
          </div>
          <div style="flex:1">
            <div style="font-weight:600;font-size:14px">${item.name}</div>
            <div style="font-size:12px;color:var(--c-text-muted)">${item.email}</div>
          </div>
          <span class="status-badge ${item.role}">${item.role}</span>
        </div>
      `);
    },

    supplierCardList(widget, data) {
      return this._genericCardList(data || [], item => `
        <div style="display:flex;align-items:center;justify-content:space-between">
          <div>
            <div style="font-weight:600;font-size:14px">${item.name}</div>
            <div style="font-size:12px;color:var(--c-text-muted)">${item.phone || ''}</div>
          </div>
          ${item.total_debt > 0 ? `<span style="padding:4px 10px;background:var(--c-danger-dim);color:var(--c-danger);border-radius:var(--r-full);font-size:12px;font-weight:600">Utang: ${Fmt.currency(item.total_debt)}</span>` : '<span style="color:var(--c-success);font-size:13px">✓ Lunas</span>'}
        </div>
      `);
    },

    inventoryCardList(widget, data) {
      return this._genericCardList(data || [], item => `
        <div style="display:flex;align-items:center;justify-content:space-between">
          <div>
            <div style="font-weight:600;font-size:14px">${item.name}</div>
            <div style="font-size:12px;color:var(--c-text-muted)">${item.category || ''}</div>
          </div>
          <div style="text-align:right">
            <div style="font-size:16px;font-weight:700;color:${item.stock_status === 'out' ? 'var(--c-danger)' : item.stock_status === 'low' ? 'var(--c-accent)' : 'var(--c-text-primary)'}">${item.stock}</div>
            <div style="font-size:10px;color:var(--c-text-muted)">stok</div>
          </div>
        </div>
      `);
    },

    purchaseCardList(widget, data) {
      return this._genericCardList(data || [], item => `
        <div>
          <div style="display:flex;justify-content:space-between;align-items:center">
            <div style="font-weight:600;font-size:14px">PO #${item.id}</div>
            <div style="font-weight:700;font-size:15px;color:var(--c-primary-light)">${Fmt.currency(item.total)}</div>
          </div>
          <div style="font-size:12px;color:var(--c-text-muted);margin-top:4px">${item.supplier} · ${item.date} · ${item.payment_type}</div>
        </div>
      `);
    },

    payableCardList(widget, data) {
      return this._genericCardList(data || [], item => `
        <div>
          <div style="display:flex;justify-content:space-between;align-items:flex-start">
            <div>
              <div style="font-weight:600">${item.supplier}</div>
              <div style="font-size:12px;color:${item.is_overdue ? 'var(--c-danger)' : 'var(--c-text-muted)'}">
                ${item.is_overdue ? '⚠️ Jatuh Tempo: ' : 'Tempo: '}${item.due_date || '-'}
              </div>
            </div>
            <div style="text-align:right">
              <div style="font-weight:700;color:var(--c-danger)">${Fmt.currency(item.remaining)}</div>
              <div style="font-size:11px;color:var(--c-text-muted)">dari ${Fmt.currency(item.total)}</div>
            </div>
          </div>
        </div>
      `);
    },

    receivableCardList(widget, data) {
      return this._genericCardList(data || [], item => `
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div>
            <div style="font-weight:600">${item.customer}</div>
            <div style="font-size:12px;color:var(--c-text-muted)">${item.created}</div>
          </div>
          <div style="font-weight:700;color:var(--c-info)">${Fmt.currency(item.remaining)}</div>
        </div>
      `);
    },

    notificationList(widget, data) {
      if (!data || !data.length) {
        const el = document.createElement('div');
        el.className = 'canvas-empty';
        el.innerHTML = `<div class="canvas-empty-icon">🔔</div><div class="canvas-empty-title">${widget.empty_label}</div>`;
        return el;
      }
      return this._genericCardList(data, item => `<div>${item.message}</div>`);
    },

    _genericCardList(data, renderItem) {
      const el = document.createElement('div');
      el.style.cssText = 'display:flex;flex-direction:column;gap:8px';
      if (!data.length) {
        el.innerHTML = '<div class="canvas-empty" style="padding:24px"><div class="canvas-empty-icon">📭</div><div class="canvas-empty-title">Belum ada data</div></div>';
        return el;
      }
      el.innerHTML = data.map(item => `
        <div style="background:var(--c-surface);border:1px solid var(--c-border);border-radius:var(--r-xl);padding:14px 16px;cursor:pointer"
          onclick="Canvas._genericTap(${item.id || 0})">
          ${renderItem(item)}
        </div>
      `).join('');
      return el;
    }
  };

  // ═══════════════════════════════════════════════════════════════════════════
  // MODULE: CommandParser
  // ═══════════════════════════════════════════════════════════════════════════
  const CommandParser = {
    async execute(input) {
      if (!input || !input.trim()) return;
      const cmdInput = $('cmd-input');
      if (cmdInput) cmdInput.value = '';

      // Direct screen shortcuts (no server round-trip)
      const localMap = {
        'help': () => HelpPanel.toggle(),
        '/help': () => HelpPanel.toggle(),
        'bantuan': () => HelpPanel.toggle(),
        'kds': () => { window.location.href = '/canvas/kds'; },
        'dapur': () => { window.location.href = '/canvas/kds'; },
        'layar dapur': () => { window.location.href = '/canvas/kds'; },
      };

      const normalized = input.trim().toLowerCase();
      if (localMap[normalized]) { localMap[normalized](); return; }

      // Server-side parse
      try {
        const res = await fetch('/canvas/api/cmd', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': Util.csrf(),
          },
          body: JSON.stringify({ input }),
        });
        const result = await res.json();

        switch (result.action) {
          case 'load_screen':
            HelpPanel.close();
            LayoutLoader.load(result.screen, result.params || {});
            break;
          case 'open_sheet':
            Sheet.openNamed(result.sheet);
            break;
          case 'open_help':
            HelpPanel.open();
            break;
          case 'redirect':
            window.location.href = result.url;
            break;
          case 'error':
            Toast.show(result.message, 'warning');
            if (result.suggestion) {
              setTimeout(() => Toast.show(`💡 Mungkin maksud Anda: "${result.suggestion}"?`, 'info'), 800);
            }
            break;
        }
      } catch (e) {
        Toast.show('Gagal memproses perintah.', 'error');
      }
    }
  };

  // ═══════════════════════════════════════════════════════════════════════════
  // MODULE: CmdBar
  // ═══════════════════════════════════════════════════════════════════════════
  const CmdBar = {
    init() {
      const input   = $('cmd-input');
      const submit  = $('cmd-submit');
      const helpBtn = $('cmd-help-btn');

      if (!input) return;

      input.addEventListener('keydown', e => {
        if (e.key === 'Enter') CommandParser.execute(input.value);
      });

      if (submit) submit.addEventListener('click', () => CommandParser.execute(input.value));
      if (helpBtn) helpBtn.addEventListener('click', () => HelpPanel.toggle());
    }
  };

  // ═══════════════════════════════════════════════════════════════════════════
  // MODULE: HelpPanel
  // ═══════════════════════════════════════════════════════════════════════════
  const HelpPanel = {
    loaded: false,

    init() {
      const panel = $('canvas-help-panel');
      if (!panel) return;
      panel.innerHTML = `
        <div class="help-panel-header">
          <div class="help-panel-title">Daftar Perintah</div>
          <button class="help-close-btn" onclick="Canvas.HelpPanel.close()">✕</button>
        </div>
        <div id="help-panel-body" style="display:flex;align-items:center;justify-content:center;padding:32px">
          <div class="skeleton" style="width:100%;height:200px;border-radius:12px"></div>
        </div>
      `;

      // Close on backdrop click (outside panel)
      document.addEventListener('click', e => {
        if (this.isOpen() && !panel.contains(e.target) && e.target.id !== 'cmd-help-btn') {
          this.close();
        }
      });
    },

    async open() {
      const panel = $('canvas-help-panel');
      if (!panel) return;
      panel.removeAttribute('hidden');
      requestAnimationFrame(() => panel.classList.add('open'));
      state.helpOpen = true;

      if (!this.loaded) {
        await this.loadCommands();
        this.loaded = true;
      }
    },

    close() {
      const panel = $('canvas-help-panel');
      if (!panel) return;
      panel.classList.remove('open');
      state.helpOpen = false;
      setTimeout(() => panel.setAttribute('hidden', ''), 400);
    },

    toggle() {
      state.helpOpen ? this.close() : this.open();
    },

    isOpen() { return state.helpOpen; },

    async loadCommands() {
      try {
        const res = await fetch('/canvas/api/help', {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const help = await res.json();
        const body = $('help-panel-body');
        if (!body) return;
        body.innerHTML = (help.groups || []).map(g => `
          <div class="help-group">
            <div class="help-group-label">${g.icon || ''} ${g.label}</div>
            <div class="help-commands-grid">
              ${(g.commands || []).map(c => `
                <button class="help-cmd-btn" onclick="Canvas.HelpPanel._triggerCmd('${c.cmd}')">
                  <div class="help-cmd-icon" style="background:${c.color || 'var(--c-surface-3)'}22">
                    ${c.icon}
                  </div>
                  <div class="help-cmd-label">${c.label}</div>
                </button>
              `).join('')}
            </div>
          </div>
        `).join('');
      } catch (e) {
        console.error('HelpPanel load error:', e);
      }
    },

    _triggerCmd(cmd) {
      const input = $('cmd-input');
      if (input) input.value = cmd;
      this.close();
      setTimeout(() => CommandParser.execute(cmd), 200);
    }
  };

  // ═══════════════════════════════════════════════════════════════════════════
  // MODULE: Sheet (Bottom Sheet)
  // ═══════════════════════════════════════════════════════════════════════════
  const Sheet = {
    init() {
      const layer    = $('canvas-sheet-layer');
      const backdrop = layer?.querySelector('.canvas-sheet-backdrop');
      const handle   = layer?.querySelector('.canvas-sheet-handle');
      const closeBtn = document.createElement('button');
      closeBtn.className = 'canvas-sheet-close';
      closeBtn.innerHTML = '✕';
      closeBtn.onclick   = () => this.close();

      if (backdrop) backdrop.addEventListener('click', () => this.close());

      // Swipe to close
      let startY = 0;
      const sheet = $('canvas-sheet');
      if (sheet) {
        sheet.addEventListener('touchstart', e => { startY = e.touches[0].clientY; }, { passive: true });
        sheet.addEventListener('touchmove', e => {
          const dy = e.touches[0].clientY - startY;
          if (dy > 0) sheet.style.transform = `translateY(${dy}px)`;
        }, { passive: true });
        sheet.addEventListener('touchend', e => {
          const dy = e.changedTouches[0].clientY - startY;
          sheet.style.transform = '';
          if (dy > 80) this.close();
        });
      }
    },

    open(content, title = '') {
      const layer   = $('canvas-sheet-layer');
      const sheetEl = $('canvas-sheet');
      const sheetContent = $('canvas-sheet-content');
      if (!layer || !sheetContent) return;

      // Header
      let header = sheetEl.querySelector('.canvas-sheet-header');
      if (!header) {
        header = document.createElement('div');
        header.className = 'canvas-sheet-header';
        sheetEl.insertBefore(header, sheetContent);
      }
      header.innerHTML = `
        <div class="canvas-sheet-title">${title}</div>
        <button class="canvas-sheet-close" onclick="Canvas.Sheet.close()">✕</button>
      `;

      sheetContent.innerHTML = content;
      layer.classList.add('open');
      document.querySelector('.canvas-sheet-backdrop').style.background = 'rgba(0,0,0,0.6)';
      state.sheetOpen = true;
    },

    close() {
      const layer = $('canvas-sheet-layer');
      if (!layer) return;
      layer.classList.remove('open');
      state.sheetOpen = false;
    },

    openCart() {
      if (!state.cartItems.length) { Toast.show('Keranjang masih kosong', 'warning'); return; }
      this.open(Cart.renderSheet(), '🛒 Keranjang Pesanan');
    },

    openNamed(sheetName) {
      const sheetTitles = {
        shift_open:    { title: '🔓 Buka Shift Kasir',   content: Sheet.shiftOpenForm() },
        shift_close:   { title: '🔒 Tutup Shift Kasir',  content: Sheet.shiftCloseForm() },
        expense_form:  { title: '💸 Catat Pengeluaran',  content: Sheet.expenseForm() },
      };
      const s = sheetTitles[sheetName];
      if (s) this.open(s.content, s.title);
    },

    shiftOpenForm() {
      return `<div class="canvas-form">
        <div class="canvas-field">
          <label class="canvas-label">Modal Awal (Rp)</label>
          <input class="canvas-input" type="number" id="shift-opening-cash" placeholder="0" inputmode="numeric">
        </div>
        <button class="btn btn-primary" onclick="Canvas._submitShiftOpen()">🔓 Buka Shift</button>
      </div>`;
    },

    shiftCloseForm() {
      return `<div class="canvas-form">
        <div class="canvas-field">
          <label class="canvas-label">Hitung Uang Fisik di Laci (Rp)</label>
          <input class="canvas-input" type="number" id="shift-closing-cash" placeholder="0" inputmode="numeric">
        </div>
        <button class="btn btn-danger" onclick="Canvas._submitShiftClose()">🔒 Tutup Shift</button>
      </div>`;
    },

    expenseForm() {
      return `<div class="canvas-form">
        <div class="canvas-field">
          <label class="canvas-label">Jumlah (Rp)</label>
          <input class="canvas-input" type="number" id="expense-amount" placeholder="0" inputmode="numeric">
        </div>
        <div class="canvas-field">
          <label class="canvas-label">Keterangan</label>
          <input class="canvas-input" type="text" id="expense-desc" placeholder="Beli es batu, gas...">
        </div>
        <button class="btn btn-primary" onclick="Canvas._submitExpense()">💾 Catat Pengeluaran</button>
      </div>`;
    }
  };

  // ═══════════════════════════════════════════════════════════════════════════
  // MODULE: Cart (POS Cart State)
  // ═══════════════════════════════════════════════════════════════════════════
  const Cart = {
    updateBar() {
      const bar = $('canvas-cart-bar');
      const countEl = $('cart-count');
      const totalEl = $('cart-total');
      if (!bar) return;
      const count = state.cartItems.reduce((s, i) => s + i.qty, 0);
      state.cartTotal = state.cartItems.reduce((s, i) => s + i.price * i.qty, 0);
      if (countEl) countEl.textContent = `${count} item`;
      if (totalEl) totalEl.textContent = Fmt.currency(state.cartTotal);
      bar.classList.toggle('visible', count > 0);
    },

    add(product) {
      const existing = state.cartItems.find(i => i.id === product.id);
      if (existing) {
        existing.qty++;
      } else {
        state.cartItems.push({ ...product, qty: 1 });
      }
      this.updateBar();
      Toast.show(`✓ ${product.name} ditambahkan`, 'success');
    },

    renderSheet() {
      if (!state.cartItems.length) return '<div class="canvas-empty"><div class="canvas-empty-icon">🛒</div><div class="canvas-empty-title">Keranjang kosong</div></div>';
      const itemsHtml = state.cartItems.map((item, i) => `
        <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--c-border)">
          <div style="flex:1">
            <div style="font-weight:600;font-size:14px">${item.name}</div>
            <div style="font-size:13px;color:var(--c-primary-light)">${Fmt.currency(item.price)}</div>
          </div>
          <div style="display:flex;align-items:center;gap:8px">
            <button onclick="Canvas._cartQty(${i}, -1)" style="width:32px;height:32px;border-radius:50%;background:var(--c-surface-3);font-size:18px;display:flex;align-items:center;justify-content:center">−</button>
            <span style="min-width:24px;text-align:center;font-weight:700">${item.qty}</span>
            <button onclick="Canvas._cartQty(${i}, 1)"  style="width:32px;height:32px;border-radius:50%;background:var(--c-primary-dim);color:var(--c-primary-light);font-size:18px;display:flex;align-items:center;justify-content:center">+</button>
          </div>
        </div>
      `).join('');
      return `
        ${itemsHtml}
        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;font-weight:700;font-size:16px">
          <span>Total</span><span style="color:var(--c-primary-light)">${Fmt.currency(state.cartTotal)}</span>
        </div>
        <button class="btn btn-primary" onclick="Canvas._openCheckout()">Lanjut Bayar →</button>
      `;
    }
  };

  // ═══════════════════════════════════════════════════════════════════════════
  // MODULE: DataFetcher
  // ═══════════════════════════════════════════════════════════════════════════
  const DataFetcher = {
    async get(url, params = {}) {
      const qs = new URLSearchParams(params).toString();
      const fullUrl = qs ? `${url}?${qs}` : url;
      const res = await fetch(fullUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      });
      if (!res.ok) throw new Error(`API ${url} gagal (${res.status})`);
      return res.json();
    },

    async post(url, body = {}) {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': Util.csrf(),
        },
        body: JSON.stringify(body),
      });
      return res.json();
    },

    refreshScreen(params) {
      LayoutLoader.load(state.currentScreen, params);
    }
  };

  // ═══════════════════════════════════════════════════════════════════════════
  // MODULE: Poll (Live Order Updates)
  // ═══════════════════════════════════════════════════════════════════════════
  const Poll = {
    start(intervalMs) {
      clearInterval(state.pollTimer);
      state.pollTimer = setInterval(async () => {
        try {
          const data = await DataFetcher.get('/canvas/api/orders');
          const pending  = data.counts?.pending || 0;
          const total    = (data.counts?.pending || 0) + (data.counts?.preparing || 0);

          Nav.updateBadge('orders_pending', pending);

          // New order notification
          if (total > state.lastOrderCount && state.lastOrderCount >= 0) {
            const diff = total - state.lastOrderCount;
            Toast.show(`🔔 ${diff} pesanan baru masuk!`, 'info');
            const badge = $('notif-badge');
            if (badge) { badge.style.display = 'block'; }
          }
          state.lastOrderCount = total;

          // Refresh orders screen if currently active
          if (state.currentScreen === 'orders') {
            const listEl = document.querySelector('[id^="widget-orders_list"]');
            if (listEl && data.orders) {
              // Soft refresh: just update badges
            }
          }
        } catch { /* polling errors are silent */ }
      }, intervalMs);
    },

    startScreen(screen, ms, widgetIds) {
      // Per-screen refresh handled by global poll above
    }
  };

  // ═══════════════════════════════════════════════════════════════════════════
  // MODULE: Push (Web Push Notifications)
  // ═══════════════════════════════════════════════════════════════════════════
  const Push = {
    init(vapidKey) {
      if (!('serviceWorker' in navigator) || !vapidKey) return;
      navigator.serviceWorker.register('/sw-canvas.js').catch(() => {});
    }
  };

  // ═══════════════════════════════════════════════════════════════════════════
  // MODULE: Toast
  // ═══════════════════════════════════════════════════════════════════════════
  const Toast = {
    init() { /* stack is in DOM */ },
    show(message, type = 'info', duration = 3500) {
      const stack = $('canvas-toast-stack');
      if (!stack) return;
      const icons = { success:'✅', error:'❌', warning:'⚠️', info:'ℹ️' };
      const toast = document.createElement('div');
      toast.className = `canvas-toast ${type}`;
      toast.innerHTML = `<span>${icons[type] || ''}</span><span>${message}</span>`;
      stack.prepend(toast);
      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-8px)';
        toast.style.transition = 'all 0.3s';
        setTimeout(() => toast.remove(), 300);
      }, duration);
    }
  };

  // ═══════════════════════════════════════════════════════════════════════════
  // MODULE: Fmt (Formatters)
  // ═══════════════════════════════════════════════════════════════════════════
  const Fmt = {
    currency(v) {
      if (!v && v !== 0) return 'Rp -';
      return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);
    },
    number(v) { return new Intl.NumberFormat('id-ID').format(v || 0); },
    value(v, format) {
      if (format === 'currency') return this.currency(v);
      if (format === 'number')   return this.number(v);
      return v ?? '-';
    },
    status(s) {
      const map = { pending:'Pending', preparing:'Diproses', ready:'Siap Antar',
                    served:'Diantar', completed:'Selesai', cancelled:'Dibatal' };
      return map[s] || s;
    }
  };

  // ═══════════════════════════════════════════════════════════════════════════
  // MODULE: Util
  // ═══════════════════════════════════════════════════════════════════════════
  const Util = {
    csrf() {
      return document.querySelector('meta[name="csrf-token"]')?.content || '';
    },
    debounce(fn, ms) {
      let t;
      return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
    }
  };

  // ═══════════════════════════════════════════════════════════════════════════
  // PUBLIC API (exposed as window.Canvas.*)
  // ═══════════════════════════════════════════════════════════════════════════
  return {
    // Expose modules for inline onclick
    Sheet, HelpPanel, Boot,

    // Shorthand actions
    cmd:        (input) => CommandParser.execute(input),
    loadScreen: (screen, params) => LayoutLoader.load(screen, params),

    // Cart actions
    _addToCart: (product) => Cart.add(product),
    _cartQty(index, delta) {
      if (!state.cartItems[index]) return;
      state.cartItems[index].qty += delta;
      if (state.cartItems[index].qty <= 0) state.cartItems.splice(index, 1);
      Cart.updateBar();
      Sheet.openCart();
    },
    _openCheckout() {
      const methods = ['tunai','qris','transfer','kasbon'];
      const methodsHtml = methods.map(m => `
        <button class="payment-method-btn" id="pay-${m}" onclick="Canvas._selectPayMethod('${m}')">
          <span class="payment-method-icon">${{tunai:'💵',qris:'📱',transfer:'🏦',kasbon:'📝'}[m]}</span>
          ${m.charAt(0).toUpperCase()+m.slice(1)}
        </button>
      `).join('');
      Sheet.open(`
        <div class="canvas-form">
          <div class="payment-methods">${methodsHtml}</div>
          <div id="cash-input-wrap" class="canvas-field" style="display:none">
            <label class="canvas-label">Uang Diterima (Rp)</label>
            <input class="canvas-input" type="number" id="cash-received" inputmode="numeric" placeholder="0"
              oninput="Canvas._calcChange(this.value)">
            <div id="change-display" style="font-size:14px;color:var(--c-success);font-weight:600;margin-top:4px"></div>
          </div>
          <div style="display:flex;justify-content:space-between;padding:12px;background:var(--c-surface-2);border-radius:var(--r-lg)">
            <span>Total</span><strong style="color:var(--c-primary-light)">${Fmt.currency(state.cartTotal)}</strong>
          </div>
          <button class="btn btn-primary" id="pay-btn" onclick="Canvas._submitCheckout()">✓ Proses Pembayaran</button>
        </div>
      `, '💳 Pembayaran');
    },
    _selectPayMethod(method) {
      document.querySelectorAll('.payment-method-btn').forEach(b => b.classList.remove('selected'));
      const btn = document.getElementById(`pay-${method}`);
      if (btn) btn.classList.add('selected');
      const cashWrap = $('cash-input-wrap');
      if (cashWrap) cashWrap.style.display = method === 'tunai' ? 'flex' : 'none';
    },
    _calcChange(received) {
      const change = Number(received) - state.cartTotal;
      const el = $('change-display');
      if (el) el.textContent = change >= 0 ? `Kembalian: ${Fmt.currency(change)}` : `Kurang: ${Fmt.currency(Math.abs(change))}`;
    },
    async _submitCheckout() {
      const selectedMethod = document.querySelector('.payment-method-btn.selected')?.id?.replace('pay-','') || 'tunai';
      try {
        const result = await DataFetcher.post('/canvas/api/pos/checkout', {
          items: state.cartItems,
          payment_method: selectedMethod,
          total: state.cartTotal,
        });
        if (result.ok) {
          state.cartItems = [];
          state.cartTotal = 0;
          Cart.updateBar();
          Sheet.close();
          Toast.show('✅ Pembayaran berhasil!', 'success');
          LayoutLoader.load('orders');
        } else {
          Toast.show(result.message || 'Pembayaran gagal', 'error');
        }
      } catch { Toast.show('Gagal memproses pembayaran', 'error'); }
    },

    // Order actions
    async _openOrderDetail(orderId, sheets) {
      try {
        const data = await DataFetcher.get('/canvas/api/orders');
        const order = data.orders?.find(o => o.id === orderId);
        if (!order) return;
        Sheet.open(`
          <div class="canvas-form">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
              <div><strong>Pesanan #${order.id}</strong><br><small style="color:var(--c-text-muted)">${order.table}</small></div>
              <span class="status-badge ${order.status}">${Fmt.status(order.status)}</span>
            </div>
            ${(order.items||[]).map(i => `<div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--c-border)"><span>${i.qty}x ${i.name}</span>${i.note?`<small style="color:var(--c-accent)">${i.note}</small>`:''}</div>`).join('')}
            <div style="display:flex;justify-content:space-between;padding:12px 0;font-weight:700"><span>Total</span><span style="color:var(--c-primary-light)">${Fmt.currency(order.total)}</span></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
              <button class="btn btn-primary" onclick="Canvas._orderAction(${orderId},'prepare')">🔵 Proses</button>
              <button class="btn btn-success" onclick="Canvas._orderAction(${orderId},'deliver')">✈️ Antar</button>
              <button class="btn btn-outline" onclick="Canvas._orderAction(${orderId},'complete')">✅ Selesai</button>
              <button class="btn btn-danger" onclick="Canvas._orderAction(${orderId},'cancel')">❌ Batal</button>
            </div>
          </div>
        `, `Pesanan #${orderId}`);
      } catch (e) { Toast.show('Gagal memuat detail pesanan', 'error'); }
    },

    async _orderAction(id, action) {
      try {
        const result = await DataFetcher.post(`/canvas/api/orders/${id}/action`, { action });
        if (result.ok) {
          Sheet.close();
          Toast.show(`✅ Status pesanan diperbarui`, 'success');
          LayoutLoader.load('orders');
        }
      } catch { Toast.show('Gagal memperbarui status', 'error'); }
    },

    // Menu actions
    _openMenuEdit(id, name) {
      Toast.show(`Edit ${name} — segera hadir`, 'info');
    },

    async _toggleMenu(id, btn) {
      try {
        const result = await DataFetcher.post(`/canvas/api/menu/${id}/toggle`, {});
        if (result.ok) {
          const available = result.is_available;
          btn.style.background = available ? 'var(--c-success)' : 'var(--c-surface-3)';
          const dot = btn.querySelector('div');
          if (dot) dot.style.left = available ? '20px' : '2px';
          Toast.show(available ? '✓ Menu diaktifkan' : 'Menu dinonaktifkan', 'success');
        }
      } catch { Toast.show('Gagal mengubah status menu', 'error'); }
    },

    // Filter chip click
    _filterChipClick(btn, param, widgetId) {
      const chips = btn.closest('.filter-chips');
      if (chips) chips.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
      btn.classList.add('active');
      DataFetcher.refreshScreen({ [param]: btn.dataset.value });
    },

    // Date picker click
    _datePickerClick(btn, param) {
      const chips = btn.closest('.filter-chips');
      if (chips) chips.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
      btn.classList.add('active');
      DataFetcher.refreshScreen({ [param]: btn.dataset.value });
    },

    // Action handler
    async _handleAction(action, api) {
      if (!action && !api) return;
      if (action === 'share_whatsapp') {
        Toast.show('Fitur WhatsApp share segera hadir', 'info');
        return;
      }
      if (action === 'export_pdf') {
        Toast.show('Fitur PDF export segera hadir', 'info');
        return;
      }
      if (api) {
        try {
          const [method, url] = api.split(':');
          const result = method === 'POST'
            ? await DataFetcher.post(url, {})
            : await DataFetcher.get(url);
          if (result.ok !== false) Toast.show('✅ Berhasil disimpan', 'success');
          else Toast.show(result.message || 'Gagal', 'error');
        } catch { Toast.show('Terjadi kesalahan', 'error'); }
      }
    },

    // Shift & Expense
    async _submitShiftOpen() {
      const cash = $('shift-opening-cash')?.value || 0;
      const result = await DataFetcher.post('/canvas/api/shift/open', { opening_cash: cash });
      if (result.ok) { Sheet.close(); Toast.show('✅ Shift dibuka', 'success'); LayoutLoader.load('home'); }
      else Toast.show(result.message, 'error');
    },

    async _submitShiftClose() {
      const cash = $('shift-closing-cash')?.value || 0;
      const result = await DataFetcher.post('/canvas/api/shift/close', { closing_cash: cash });
      if (result.ok) { Sheet.close(); Toast.show('✅ Shift ditutup. Laporan tersedia di halaman Finance.', 'success'); LayoutLoader.load('home'); }
      else Toast.show(result.message, 'error');
    },

    async _submitExpense() {
      const amount = $('expense-amount')?.value;
      const desc   = $('expense-desc')?.value;
      if (!amount || !desc) { Toast.show('Isi jumlah dan keterangan', 'warning'); return; }
      const result = await DataFetcher.post('/canvas/api/expense', { amount, description: desc });
      if (result.ok) { Sheet.close(); Toast.show('✅ Pengeluaran dicatat', 'success'); }
      else Toast.show(result.message, 'error');
    },

    async _togglePush(btn) {
      if (Notification.permission === 'granted') {
        Toast.show('Notifikasi sudah aktif', 'info');
      } else {
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
          btn.textContent = 'Nonaktifkan';
          btn.className = 'btn btn-outline';
          Push.init(state.boot?.config?.push_vapid_key);
          Toast.show('✅ Notifikasi diaktifkan!', 'success');
        } else {
          Toast.show('Izin notifikasi ditolak', 'warning');
        }
      }
    },

    _openTableDetail(id) { Toast.show(`Detail meja #${id} — segera hadir`, 'info'); },
    _genericTap(id)       { /* generic tap handler placeholder */ },
  };
})();

// ─── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => Canvas.Boot.init());
