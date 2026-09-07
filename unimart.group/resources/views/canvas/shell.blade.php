<!DOCTYPE html>
<html lang="id" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="theme-color" content="#0B0B14">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="Unimart Canvas — Ruang Kendali Bisnis Anda">

  <title>Canvas — {{ $tenant->name ?? 'Unimart' }}</title>

  {{-- Google Fonts --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">

  {{-- PWA Manifest --}}
  <link rel="manifest" href="/canvas-manifest.json">

  {{-- Canvas Design System --}}
  <link rel="stylesheet" href="{{ asset('css/canvas.css') }}?v={{ filemtime(public_path('css/canvas.css')) }}">

  {{-- Boot Data (embedded, no API round-trip on first render) --}}
  <script>
    window.CANVAS_BOOT = @json($canvasBoot);
  </script>
</head>
<body class="canvas-shell">

  {{-- ═══════════════════════════════════════════════════════
       TOAST STACK (notifications)
       ═══════════════════════════════════════════════════════ --}}
  <div id="canvas-toast-stack" aria-live="assertive" aria-atomic="true"></div>

  {{-- ═══════════════════════════════════════════════════════
       TOP APP BAR (dinamis diisi oleh canvas.js)
       ═══════════════════════════════════════════════════════ --}}
  <header id="canvas-topbar" role="banner">
    {{-- Dirender oleh Canvas.Topbar.render() --}}
  </header>

  {{-- ═══════════════════════════════════════════════════════
       MAIN CONTENT AREA (diisi oleh LayoutLoader)
       ═══════════════════════════════════════════════════════ --}}
  <main id="canvas-content" role="main" aria-label="Konten Utama">
    {{-- Skeleton loader awal sebelum canvas.js boot --}}
    <div class="canvas-screen">
      <div class="skeleton skeleton-card" style="height:120px"></div>
      <div class="metric-row">
        <div class="skeleton skeleton-card" style="height:80px"></div>
        <div class="skeleton skeleton-card" style="height:80px"></div>
        <div class="skeleton skeleton-card" style="height:80px"></div>
        <div class="skeleton skeleton-card" style="height:80px"></div>
      </div>
      <div style="display:flex;flex-direction:column;gap:8px">
        <div class="skeleton skeleton-card"></div>
        <div class="skeleton skeleton-card"></div>
        <div class="skeleton skeleton-card"></div>
      </div>
    </div>
  </main>

  {{-- ═══════════════════════════════════════════════════════
       BOTTOM NAVIGATION / SIDEBAR
       ═══════════════════════════════════════════════════════ --}}
  <nav id="canvas-nav" role="navigation" aria-label="Navigasi Utama">
    {{-- Dirender oleh Canvas.Nav.render() --}}
  </nav>

  {{-- ═══════════════════════════════════════════════════════
       COMMAND BAR (sticky bottom)
       ═══════════════════════════════════════════════════════ --}}
  <div id="canvas-cmd-bar" role="search" aria-label="Command Bar">
    <button id="cmd-help-btn"
      aria-label="Lihat daftar perintah"
      title="Ketik /help atau klik untuk melihat semua perintah">
      /help
    </button>

    <input
      id="cmd-input"
      type="text"
      placeholder="Ketik perintah... (kasir, pesanan, laporan)"
      autocomplete="off"
      autocorrect="off"
      autocapitalize="none"
      spellcheck="false"
      enterkeyhint="send"
      aria-label="Command input">

    <button id="cmd-submit" aria-label="Jalankan perintah">
      ↑
    </button>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       HELP PANEL (slide-up dari command bar)
       ═══════════════════════════════════════════════════════ --}}
  <div id="canvas-help-panel"
    role="dialog"
    aria-label="Daftar Perintah"
    aria-modal="true"
    hidden>
    {{-- Diisi oleh Canvas.HelpPanel.loadCommands() --}}
  </div>

  {{-- ═══════════════════════════════════════════════════════
       BOTTOM SHEET LAYER
       ═══════════════════════════════════════════════════════ --}}
  <div id="canvas-sheet-layer" role="dialog" aria-modal="true" aria-label="Panel Detail">
    <div class="canvas-sheet-backdrop" aria-hidden="true"></div>

    <div id="canvas-sheet">
      <div class="canvas-sheet-handle" aria-hidden="true"></div>
      {{-- Header diisi oleh Sheet.open() --}}
      <div id="canvas-sheet-content">
        {{-- Konten diisi secara dinamis --}}
      </div>
    </div>
  </div>

  {{-- Canvas Engine --}}
  <script src="{{ asset('js/canvas.js') }}?v={{ filemtime(public_path('js/canvas.js')) }}" defer></script>

</body>
</html>
