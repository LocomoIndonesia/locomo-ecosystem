<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="theme-color" content="#0B0B14">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>KDS Dapur — {{ $tenant->name ?? 'Unimart' }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/canvas.css') }}">
  <style>
    /* KDS-specific overrides — fullscreen, no bottom nav */
    body { overflow: auto; }
    .kds-timer { font-variant-numeric: tabular-nums; }
  </style>
</head>
<body class="canvas-shell">

  <div class="kds-page">

    {{-- ── KDS Header ──────────────────────────────────── --}}
    <div class="kds-header">
      <div>
        <div class="kds-title">🍳 Kitchen Display</div>
        <div style="font-size:13px;color:var(--c-text-muted)">{{ $tenant->name }} — <span id="kds-clock"></span></div>
      </div>
      <div style="display:flex;gap:8px;align-items:center">
        <div style="font-size:13px;color:var(--c-text-secondary)">
          <span id="kds-order-count" style="font-size:20px;font-weight:700;color:var(--c-accent)">{{ count($orders) }}</span> pesanan aktif
        </div>
        <a href="/canvas" class="btn btn-outline" style="width:auto;padding:0 16px;font-size:13px">← Kembali</a>
      </div>
    </div>

    {{-- ── KDS Grid ─────────────────────────────────────── --}}
    <div class="kds-grid" id="kds-grid">
      @forelse($orders as $order)
        @php
          $ageMinutes = $order->created_at ? now()->diffInMinutes($order->created_at) : 0;
          $isUrgent   = $ageMinutes >= 15;
        @endphp
        <div class="kds-ticket {{ $isUrgent ? 'urgent' : '' }}"
          id="kds-ticket-{{ $order->id }}"
          data-created="{{ $order->created_at?->timestamp }}">

          {{-- Ticket Header --}}
          <div class="kds-ticket-header">
            <div>
              <div class="kds-ticket-id">#{{ $order->id }}</div>
              <div class="kds-ticket-table">{{ $order->table?->name ?? 'Take Away' }}</div>
            </div>
            <div class="kds-ticket-timer {{ $isUrgent ? 'urgent' : '' }}"
              data-start="{{ $order->created_at?->timestamp }}">
              {{ $ageMinutes }}:00
            </div>
          </div>

          {{-- Items --}}
          <div class="kds-ticket-items">
            @forelse($order->items as $item)
              <div class="kds-item">
                <span class="kds-item-qty">{{ $item->quantity }}×</span>
                <span>{{ $item->product?->name ?? '-' }}</span>
                @if($item->note)
                  <span class="kds-item-note">({{ $item->note }})</span>
                @endif
              </div>
            @empty
              <div style="color:var(--c-text-muted);font-size:13px">Tidak ada item</div>
            @endforelse
          </div>

          {{-- Complete Button --}}
          <button class="kds-done-btn"
            id="kds-done-{{ $order->id }}"
            onclick="kdsComplete({{ $order->id }})">
            ✓ Selesai
          </button>
        </div>
      @empty
        {{-- Empty State --}}
        <div style="grid-column:1/-1" class="canvas-empty" style="padding:80px 24px">
          <div class="canvas-empty-icon">🍳</div>
          <div class="canvas-empty-title">Tidak ada pesanan aktif</div>
          <div class="canvas-empty-desc">Pesanan baru akan muncul di sini secara otomatis.</div>
        </div>
      @endforelse
    </div>

  </div>{{-- .kds-page --}}

  {{-- Toast --}}
  <div id="canvas-toast-stack" aria-live="assertive"></div>

  <script>
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // ── Clock ──────────────────────────────────────────────────────────────
    function updateClock() {
      const el = document.getElementById('kds-clock');
      if (el) el.textContent = new Date().toLocaleTimeString('id-ID', {
        hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:false
      });
    }
    updateClock();
    setInterval(updateClock, 1000);

    // ── Live Timers ────────────────────────────────────────────────────────
    function updateTimers() {
      document.querySelectorAll('[data-start]').forEach(el => {
        const start = parseInt(el.dataset.start, 10);
        if (!start) return;
        const elapsed = Math.floor(Date.now() / 1000) - start;
        const min = Math.floor(elapsed / 60);
        const sec = elapsed % 60;
        el.textContent = `${min}:${String(sec).padStart(2, '0')}`;
        if (min >= 15) {
          el.classList.add('urgent');
          el.closest('.kds-ticket')?.classList.add('urgent');
        }
      });
    }
    setInterval(updateTimers, 1000);

    // ── Complete Order ─────────────────────────────────────────────────────
    async function kdsComplete(orderId) {
      const btn    = document.getElementById(`kds-done-${orderId}`);
      const ticket = document.getElementById(`kds-ticket-${orderId}`);
      if (!btn) return;

      btn.disabled = true;
      btn.textContent = '⏳ Memproses...';

      try {
        const res = await fetch(`/canvas/api/kds/${orderId}/complete`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': CSRF,
          },
          body: JSON.stringify({}),
        });
        const result = await res.json();

        if (result.ok) {
          // Animate out and remove ticket
          if (ticket) {
            ticket.style.transition = 'all 0.4s ease-out';
            ticket.style.transform  = 'scale(0.9)';
            ticket.style.opacity    = '0';
            setTimeout(() => {
              ticket.remove();
              updateOrderCount();
            }, 400);
          }
          showToast('✅ Pesanan selesai!', 'success');
        } else {
          btn.disabled = false;
          btn.textContent = '✓ Selesai';
          showToast(result.message || 'Gagal', 'error');
        }
      } catch {
        btn.disabled = false;
        btn.textContent = '✓ Selesai';
        showToast('Koneksi bermasalah', 'error');
      }
    }

    function updateOrderCount() {
      const count = document.querySelectorAll('.kds-ticket').length;
      const el = document.getElementById('kds-order-count');
      if (el) el.textContent = count;

      if (count === 0) {
        const grid = document.getElementById('kds-grid');
        if (grid) grid.innerHTML = `
          <div style="grid-column:1/-1;text-align:center;padding:80px 24px">
            <div style="font-size:56px">🍳</div>
            <div style="font-size:18px;font-weight:600;color:var(--c-text-secondary);margin-top:16px">Dapur bersih!</div>
            <div style="font-size:13px;color:var(--c-text-muted);margin-top:8px">Tidak ada pesanan aktif saat ini.</div>
          </div>`;
      }
    }

    // ── Live Polling — Auto-refresh setiap 10 detik ────────────────────────
    let lastOrderIds = new Set({{ collect($orders)->pluck('id')->toJson() }});

    setInterval(async () => {
      try {
        const res = await fetch('/canvas/api/orders?status=pending,preparing', {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) return;
        const data = await res.json();
        const currentIds = new Set(data.orders?.map(o => o.id) || []);

        // Cek order baru
        currentIds.forEach(id => {
          if (!lastOrderIds.has(id)) {
            showToast('🔔 Pesanan baru masuk!', 'info');
            // Force refresh halaman untuk load ticket baru
            window.location.reload();
          }
        });

        lastOrderIds = currentIds;
      } catch {}
    }, 10000);

    // ── Toast ──────────────────────────────────────────────────────────────
    function showToast(message, type = 'info') {
      const stack = document.getElementById('canvas-toast-stack');
      if (!stack) return;
      const icons = { success:'✅', error:'❌', warning:'⚠️', info:'ℹ️' };
      const toast = document.createElement('div');
      toast.className = `canvas-toast ${type}`;
      toast.innerHTML = `<span>${icons[type]}</span><span>${message}</span>`;
      stack.prepend(toast);
      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
      }, 3500);
    }
  </script>

</body>
</html>
