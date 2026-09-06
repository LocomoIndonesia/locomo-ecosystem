<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasbor Staf - {{ $tenant ? $tenant->name : 'Unimart' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
    <style>
        :root {
            --bg: hsl(220, 28%, 7%);
            --surface: hsl(220, 24%, 11%);
            --surface-2: hsl(220, 22%, 16%);
            --surface-3: hsl(220, 20%, 22%);
            --primary: hsl(28, 90%, 52%);
            --primary-hover: hsl(28, 90%, 62%);
            --success: hsl(150, 64%, 42%);
            --danger: hsl(0, 72%, 52%);
            --warning: hsl(40, 90%, 52%);
            --text: hsl(220, 18%, 94%);
            --text-muted: hsl(220, 14%, 60%);
            --border: rgba(255,255,255,0.06);
            --radius: 14px;
            --radius-sm: 8px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body { background: var(--bg); color: var(--text); min-height: 100vh; }
        .app-layout { display: flex; min-height: 100vh; }
        .sidebar {
            width: 230px; min-height: 100vh; background: var(--surface);
            border-right: 1px solid var(--border); display: flex; flex-direction: column;
            position: sticky; top: 0; height: 100vh; overflow-y: auto; flex-shrink: 0;
        }
        .main-content { flex: 1; overflow: auto; min-width: 0; }
        .sidebar-header { padding: 1.4rem 1.2rem; border-bottom: 1px solid var(--border); }
        .sidebar-header h2 { font-size: 1rem; font-weight: 800; color: var(--primary); }
        .sidebar-header p { font-size: 0.78rem; color: var(--text-muted); margin-top: 0.15rem; }
        .role-chip { display: inline-block; margin-top: 0.5rem; padding: 0.15rem 0.6rem; background: rgba(230,115,0,0.15); border: 1px solid var(--primary); border-radius: 20px; font-size: 0.7rem; color: var(--primary); font-weight: 700; text-transform: uppercase; border-style: solid; }
        .nav-section { padding: 1rem 0.8rem; }
        .nav-section-label { font-size: 0.66rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.8px; font-weight: 700; padding: 0 0.4rem; margin-bottom: 0.4rem; }
        .nav-item { display: flex; align-items: center; gap: 0.6rem; padding: 0.6rem 0.8rem; border-radius: var(--radius-sm); cursor: pointer; transition: all 0.15s; font-size: 0.85rem; font-weight: 500; color: var(--text-muted); text-decoration: none; border: none; background: none; width: 100%; text-align: left; }
        .nav-item:hover { background: rgba(255,255,255,0.05); color: var(--text); }
        .nav-item.active { background: rgba(230,115,0,0.12); color: var(--primary); font-weight: 600; }
        .sidebar-footer { margin-top: auto; padding: 1rem 0.8rem; border-top: 1px solid var(--border); }
        .btn-logout { width: 100%; padding: 0.55rem; background: rgba(255,59,48,0.1); border: 1px solid rgba(255,59,48,0.3); color: var(--danger); border-radius: var(--radius-sm); font-weight: 600; cursor: pointer; font-size: 0.82rem; transition: all 0.15s; }
        .btn-logout:hover { background: var(--danger); color: white; }
        .topbar { display: flex; justify-content: space-between; align-items: center; padding: 0.9rem 1.8rem; border-bottom: 1px solid var(--border); background: var(--surface); position: sticky; top: 0; z-index: 10; }
        .topbar-title { font-size: 1rem; font-weight: 700; }
        .topbar-right { display: flex; align-items: center; gap: 0.8rem; font-size: 0.82rem; color: var(--text-muted); }
        .shift-badge { display: flex; align-items: center; gap: 0.4rem; padding: 0.3rem 0.7rem; background: rgba(40,167,69,0.12); border: 1px solid rgba(40,167,69,0.3); border-radius: 20px; font-size: 0.75rem; }
        .shift-badge.no-shift { background: rgba(255,59,48,0.1); border-color: rgba(255,59,48,0.3); color: var(--danger); }
        .shift-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--success); box-shadow: 0 0 6px var(--success); }
        .content { padding: 1.6rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(175px, 1fr)); gap: 1rem; margin-bottom: 1.8rem; }
        .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.2rem 1.4rem; }
        .stat-label { font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-value { font-size: 1.55rem; font-weight: 800; margin-top: 0.3rem; }
        .stat-sub { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.4rem; margin-bottom: 1.4rem; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem; padding-bottom: 0.8rem; border-bottom: 1px solid var(--border); }
        .card-header h3 { font-size: 0.95rem; font-weight: 700; }
        .btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; border-radius: var(--radius-sm); font-weight: 600; font-size: 0.82rem; cursor: pointer; transition: all 0.15s; border: none; text-decoration: none; }
        .btn-primary { background: var(--primary); color: var(--bg); }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-success { background: rgba(40,167,69,0.15); color: var(--success); border: 1px solid rgba(40,167,69,0.3); }
        .btn-success:hover { background: var(--success); color: white; }
        .btn-danger { background: rgba(255,59,48,0.1); color: var(--danger); border: 1px solid rgba(255,59,48,0.3); }
        .btn-danger:hover { background: var(--danger); color: white; }
        .btn-ghost { background: var(--surface-2); color: var(--text); border: 1px solid var(--border); }
        .btn-sm { padding: 0.28rem 0.6rem; font-size: 0.75rem; }
        .form-group { display: flex; flex-direction: column; gap: 0.3rem; }
        .form-group label { font-size: 0.75rem; color: var(--text-muted); font-weight: 600; }
        .form-control { padding: 0.55rem 0.9rem; background: var(--surface-2); border: 1px solid var(--border); border-radius: var(--radius-sm); color: var(--text); font-size: 0.88rem; outline: none; width: 100%; }
        .form-control:focus { border-color: var(--primary); }
        .form-control option { background: var(--surface-2); }
        .tabs { display: flex; gap: 0.3rem; border-bottom: 1px solid var(--border); margin-bottom: 1.5rem; overflow-x: auto; overflow-y: hidden; scrollbar-width: none; }
        .tabs::-webkit-scrollbar { display: none; }
        .tab { padding: 0.55rem 1.1rem; font-size: 0.82rem; font-weight: 600; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -1px; transition: all 0.15s; color: var(--text-muted); white-space: nowrap; }
        .tab.active { border-bottom-color: var(--primary); color: var(--primary); }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }
        .alert { padding: 0.8rem 1.2rem; border-radius: var(--radius-sm); margin-bottom: 1.2rem; font-size: 0.85rem; font-weight: 500; }
        .alert-success { background: rgba(40,167,69,0.12); border: 1px solid rgba(40,167,69,0.25); color: var(--success); }
        .alert-danger { background: rgba(255,59,48,0.12); border: 1px solid rgba(255,59,48,0.25); color: var(--danger); }
        
        /* Orders */
        .order-list { display: flex; flex-direction: column; gap: 0.7rem; }
        .order-card { background: var(--surface-2); border: 1px solid var(--border); border-radius: var(--radius-sm); overflow: hidden; }
        .order-card-header { display: flex; justify-content: space-between; align-items: center; padding: 0.85rem 1.1rem; cursor: pointer; transition: all 0.15s; }
        .order-card-header:hover { background: rgba(255,255,255,0.03); }
        .order-meta { display: flex; align-items: center; gap: 0.8rem; flex-wrap: wrap; }
        .order-number { font-weight: 700; font-size: 0.88rem; }
        .order-time { font-size: 0.72rem; color: var(--text-muted); }
        .order-label { font-size: 0.75rem; background: var(--surface-3); padding: 0.12rem 0.45rem; border-radius: 4px; color: var(--text-muted); }
        .order-customer { font-size: 0.78rem; color: var(--primary); font-weight: 600; }
        .badge { padding: 0.18rem 0.55rem; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .badge-unpaid { background: rgba(255,59,48,0.15); color: var(--danger); }
        .badge-partial { background: rgba(230,115,0,0.15); color: var(--primary); }
        .badge-paid { background: rgba(40,167,69,0.15); color: var(--success); }
        .badge-pending { background: rgba(200,200,200,0.1); color: var(--text-muted); }
        .badge-cooking { background: rgba(230,115,0,0.15); color: var(--warning); }
        .badge-ready { background: rgba(40,167,69,0.15); color: var(--success); }
        .badge-void { background: rgba(255,59,48,0.08); color: rgba(255,59,48,0.4); text-decoration: line-through; }
        .order-card-body { padding: 1rem 1.1rem; border-top: 1px solid var(--border); display: none; }
        .order-card-body.open { display: block; }
        .order-item-row { display: flex; justify-content: space-between; align-items: center; padding: 0.45rem 0; border-bottom: 1px solid var(--border); font-size: 0.82rem; }
        .order-item-row:last-child { border-bottom: none; }
        .order-total-row { display: flex; justify-content: space-between; align-items: center; padding: 0.65rem 0; border-top: 1px solid var(--border); font-weight: 700; font-size: 0.9rem; }
        .payment-row { display: flex; justify-content: space-between; font-size: 0.78rem; padding: 0.3rem 0; color: var(--text-muted); }
        
        /* POS Panel */
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 0.7rem; }
        .product-tile { background: var(--surface-2); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.75rem; cursor: pointer; transition: all 0.15s; text-align: center; }
        .product-tile:hover { border-color: var(--primary); transform: translateY(-2px); }
        .product-tile.added { border-color: var(--success); background: rgba(40,167,69,0.08); }
        .product-tile.out { opacity: 0.4; cursor: not-allowed; }
        .product-tile-name { font-size: 0.8rem; font-weight: 600; margin-bottom: 0.3rem; line-height: 1.2; }
        .product-tile-price { font-size: 0.75rem; color: var(--primary); font-weight: 700; }
        .product-tile-stock { font-size: 0.65rem; color: var(--text-muted); }
        .cart-box { background: var(--surface-2); border-radius: var(--radius-sm); padding: 0.8rem; min-height: 100px; }
        .cart-box-empty { color: var(--text-muted); font-size: 0.82rem; text-align: center; padding: 1.5rem 0; }
        .cart-row { display: flex; justify-content: space-between; align-items: center; padding: 0.45rem 0; border-bottom: 1px solid var(--border); font-size: 0.82rem; }
        .cart-qty { display: flex; align-items: center; gap: 0.35rem; }
        .qty-btn { width: 22px; height: 22px; border-radius: 50%; background: var(--surface-3); border: 1px solid var(--border); color: var(--text); cursor: pointer; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.75rem; transition: all 0.1s; }
        .qty-btn:hover { border-color: var(--primary); color: var(--primary); }
        
        /* Modals & Overlays */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(7, 9, 15, 0.75);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 1rem;
            animation: fadeIn 0.2s ease-out;
        }
        .modal-container {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            width: 100%;
            max-width: 520px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            overflow: hidden;
            animation: slideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        
        .modal-header {
            padding: 1.2rem 1.4rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h3 { font-size: 1.05rem; font-weight: 700; }
        .modal-body {
            padding: 1.4rem;
            max-height: 70vh;
            overflow-y: auto;
        }
        .modal-footer {
            padding: 1rem 1.4rem;
            border-top: 1px solid var(--border);
            background: var(--surface-2);
            display: flex;
            gap: 0.8rem;
            justify-content: flex-end;
        }
        
        /* Blur / faded style for pas bayar input */
        .blur-fade-input {
            opacity: 0.5;
            filter: blur(0.5px);
            pointer-events: none;
            transition: all 0.2s ease;
        }
        .suggestion-chip {
            padding: 0.4rem 0.8rem;
            background: var(--surface-3);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
            text-align: center;
        }
        .suggestion-chip:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(230,115,0,0.08);
        }
        
        /* Tables for Inventory / Stocks */
        .stock-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            font-size: 0.88rem;
        }
        .stock-table th, .stock-table td {
            padding: 0.8rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        .stock-table th {
            background: var(--surface-2);
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.72rem;
            letter-spacing: 0.5px;
        }
        .stock-table tr:hover {
            background: rgba(255,255,255,0.02);
        }
        
        /* Shift & Kas */
        .shift-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.7rem; margin-bottom: 1.2rem; }
        .shift-info-item { background: var(--surface-2); padding: 0.8rem 1rem; border-radius: var(--radius-sm); }
        .shift-info-label { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; }
        .shift-info-value { font-size: 1rem; font-weight: 700; margin-top: 0.2rem; }
        .check-label { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.82rem; color: var(--text-muted); margin-top: 0.4rem; }
        .check-label input { accent-color: var(--primary); width: 16px; height: 16px; }

        /* Mobile responsive adjustments */
        @media (max-width: 768px) {
            .app-layout { position: relative; }
            .sidebar {
                position: fixed !important;
                top: 0; left: 0; bottom: 0;
                width: 250px;
                height: 100vh !important;
                z-index: 1000 !important;
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                display: flex !important;
            }
            .sidebar.open-mobile {
                transform: translateX(0) !important;
            }
            .mobile-close-btn { display: block !important; }
            .hamburger-btn { display: block !important; }
            .content { padding: 1rem; }
            .shift-info-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr) !important; }
        }

        /* AI Assistant Welcome Banner & Chat Widget */
        .welcome-ai-banner {
            background: linear-gradient(135deg, rgba(230,115,0,0.15), rgba(15,23,30,0.4));
            border: 1px solid rgba(230,115,0,0.25);
            border-radius: var(--radius);
            padding: 1.2rem 1.4rem;
            margin-bottom: 1.6rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        
        .welcome-ai-banner::before {
            content: '';
            position: absolute;
            top: -50%; right: -20%;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(230,115,0,0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .chat-messages::-webkit-scrollbar {
            width: 4px;
        }
        .chat-messages::-webkit-scrollbar-thumb {
            background: var(--surface-3);
            border-radius: 4px;
        }
        
        .suggestion-chip {
            padding: 0.35rem 0.65rem;
            background: var(--surface-3);
            border: 1px solid var(--border);
            color: var(--text-muted);
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
            white-space: nowrap;
        }
        .suggestion-chip:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(230,115,0,0.08);
        }
        
        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1.0); }
        }
    </style>

</head>
<body x-data="staffDashboard()">
<div class="app-layout">
    <!-- Sidebar -->
    <aside class="sidebar" :class="mobileSidebarOpen ? 'open-mobile' : ''">
        <div class="sidebar-header" style="position: relative;">
            <button @click="mobileSidebarOpen = false" style="position: absolute; top: 1.1rem; right: 1rem; background: none; border: none; color: var(--text-muted); font-size: 1.25rem; cursor: pointer; display: none;" class="mobile-close-btn">✕</button>
            <h2>{{ $tenant ? $tenant->name : 'Unimart Caffe' }}</h2>
            <p>{{ $user->name }}</p>
            <span class="role-chip">{{ strtoupper(str_replace('_', ' ', $user->role)) }}</span>
            
            @if($user->isOwner())
                <div class="role-switcher" style="margin-top: 1rem; width: 100%; text-align: left;">
                    <label style="font-size: 0.65rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; display: block; margin-bottom: 0.3rem;">Peran Kerja Aktif</label>
                    <form action="{{ route('staff.switch-role') }}" method="POST" id="role-switch-form">
                        @csrf
                        <select name="role" class="form-input" style="font-size: 0.78rem; background: var(--surface-accent); border: 1px solid var(--border); padding: 0.4rem; color: #fff; width: 100%; border-radius: var(--radius-sm);" onchange="document.getElementById('role-switch-form').submit()">
                            <option value="owner" {{ $actingRole === 'owner' ? 'selected' : '' }}>Owner (Semua Akses)</option>
                            <option value="cashier" {{ $actingRole === 'cashier' ? 'selected' : '' }}>Kasir</option>
                            <option value="chef" {{ $actingRole === 'chef' ? 'selected' : '' }}>Koki (Dapur)</option>
                            <option value="admin_gudang" {{ $actingRole === 'admin_gudang' ? 'selected' : '' }}>Admin Gudang</option>
                            <option value="kurir" {{ $actingRole === 'kurir' ? 'selected' : '' }}>Kurir (Pengantar)</option>
                        </select>
                    </form>
                </div>
            @endif
        </div>
        <nav class="nav-section">
            <div class="nav-section-label">Menu Kerja</div>
            @if($actingRole === 'owner')
                <div class="nav-section-label" style="font-size:0.65rem; color:var(--primary); margin-top:0.4rem; margin-bottom:0.2rem;">Akses Kasir</div>
                <button class="nav-item" :class="activeTab==='new-order'?'active':''" @click="activeTab='new-order'"><span>➕</span> Pesanan</button>
                <button class="nav-item" :class="activeTab==='running-orders'?'active':''" @click="activeTab='running-orders'"><span>🔄</span> Pesanan Berjalan</button>
                <button class="nav-item" :class="activeTab==='history'?'active':''" @click="activeTab='history'"><span>📜</span> Riwayat</button>
                <button class="nav-item" :class="activeTab==='shift'?'active':''" @click="activeTab='shift'"><span>⏱</span> Shift & Kas</button>
                <button class="nav-item" :class="activeTab==='expense'?'active':''" @click="activeTab='expense'"><span>💸</span> Uang Keluar</button>
                <a href="{{ route('owner.pos', ['alias' => $alias]) }}" class="nav-item"><span>🖥</span> POS Kasir Penuh</a>
                
                <div class="nav-section-label" style="font-size:0.65rem; color:var(--primary); margin-top:0.8rem; margin-bottom:0.2rem;">Akses Dapur & Logistik</div>
                <button class="nav-item" :class="activeTab==='chef-kds'?'active':''" @click="activeTab='chef-kds'"><span>👨‍🍳</span> Monitor KDS</button>
                <a href="{{ route('owner.kds', ['alias' => $alias]) }}" class="nav-item"><span>🖥</span> KDS Dapur Penuh</a>
                <button class="nav-item" :class="activeTab==='stock-management'?'active':''" @click="activeTab='stock-management'"><span>📦</span> Kelola Stok</button>
                <button class="nav-item" :class="activeTab==='delivery-queue'?'active':''" @click="activeTab='delivery-queue'"><span>🛵</span> Antrean Pengiriman</button>
            @elseif($actingRole === 'cashier')
                <button class="nav-item" :class="activeTab==='new-order'?'active':''" @click="activeTab='new-order'"><span>➕</span> Pesanan</button>
                <button class="nav-item" :class="activeTab==='running-orders'?'active':''" @click="activeTab='running-orders'"><span>🔄</span> Pesanan Berjalan</button>
                <button class="nav-item" :class="activeTab==='history'?'active':''" @click="activeTab='history'"><span>📜</span> Riwayat</button>
                <button class="nav-item" :class="activeTab==='shift'?'active':''" @click="activeTab='shift'"><span>⏱</span> Shift & Kas</button>
                <button class="nav-item" :class="activeTab==='expense'?'active':''" @click="activeTab='expense'"><span>💸</span> Uang Keluar</button>
                <a href="{{ route('owner.pos', ['alias' => $alias]) }}" class="nav-item"><span>🖥</span> POS Kasir Penuh</a>
            @elseif($actingRole === 'chef')
                <button class="nav-item" :class="activeTab==='chef-kds'?'active':''" @click="activeTab='chef-kds'"><span>👨‍🍳</span> Monitor KDS</button>
                <a href="{{ route('owner.kds', ['alias' => $alias]) }}" class="nav-item"><span>🖥</span> KDS Dapur Penuh</a>
            @elseif($actingRole === 'admin_gudang')
                <button class="nav-item active" @click="activeTab='stock-management'"><span>📦</span> Kelola Stok</button>
            @elseif($actingRole === 'kurir')
                <button class="nav-item active" @click="activeTab='delivery-queue'"><span>🛵</span> Antrean Pengiriman</button>
            @endif
            <a href="{{ route('password.change') }}" class="nav-item"><span>🔒</span> Ganti Password</a>
        </nav>
        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">🚪 Keluar</button>
            </form>
        </div>
    </aside>

    <!-- Backdrop overlay for mobile sidebar -->
    <div class="sidebar-overlay" x-show="mobileSidebarOpen" @click="mobileSidebarOpen = false"
         style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 900; display: none;"
         x-transition.opacity></div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar" style="display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center;">
                <button class="hamburger-btn" @click="mobileSidebarOpen = true" 
                        style="background: none; border: none; font-size: 1.4rem; color: var(--text); cursor: pointer; display: none; margin-right: 0.5rem; outline: none;">☰</button>
                <span class="topbar-title">
                    @if($user->isOwner()) Owner Dashboard (Akses Penuh)
                @elseif($actingRole === 'cashier') Kasir Dashboard
                @elseif($actingRole === 'chef') Koki Dashboard
                @elseif($actingRole === 'admin_gudang') Admin Gudang Dashboard
                @elseif($actingRole === 'kurir') Kurir Dashboard
                @elseif($actingRole === 'waiter') Waiter Dashboard
                @else Staff Dashboard @endif
            </span>
            </div>
            <div class="topbar-right">
                <span>{{ now()->format('H:i, d M Y') }}</span>
                @if($user->isCashier())
                    @if(isset($shift) && $shift)
                        <span class="shift-badge"><span class="shift-dot"></span> Shift #{{ $shift->id }} Aktif</span>
                    @else
                        <span class="shift-badge no-shift">⚠ Belum Ada Shift</span>
                    @endif
                @endif
            </div>
        </div>

        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">✓ {{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">⚠ {{ $errors->first() }}</div>
            @endif

            <!-- Banner Sapaan Hangat Asisten Kasir Pintar -->
            <div class="welcome-ai-banner">
                <div style="font-size: 2.2rem; filter: drop-shadow(0 0 8px var(--primary));">🤖</div>
                <div>
                    <h4 style="font-size: 1.05rem; font-weight: 700; color: #fff; margin-bottom: 0.2rem;">
                        Halo Kak {{ Auth::user()->name }}! Selamat <span x-text="getGreetingTime()">hari</span>! 🌟
                    </h4>
                    <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.4; font-style: italic;">
                        "<span x-text="getAIQuote()">Tetap semangat melayani pelanggan ya!</span>"
                    </p>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- 1. CASHIER WORKSPACE                           -->
            <!-- ============================================== -->
            @if($actingRole === 'cashier' || $actingRole === 'owner')
                <div x-show="['new-order', 'running-orders', 'history', 'shift', 'expense'].includes(activeTab)">
                    <!-- KPI Stats -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-label">Total Omset Shift</div>
                            <div class="stat-value" style="color:var(--primary);" x-text="'Rp ' + Number(salesTotal).toLocaleString('id-ID')">Rp {{ number_format($sales_total ?? 0, 0, ',', '.') }}</div>
                            <div class="stat-sub" x-text="orders.filter(o => o.shift_id === {{ $shift->id ?? 0 }}).length + ' transaksi'">{{ $sales_count ?? 0 }} transaksi</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Saldo Laci (Sistem)</div>
                            <div class="stat-value" style="color:var(--success);" x-text="'Rp ' + Number(systemEndingCash).toLocaleString('id-ID')">Rp {{ number_format($shift->system_ending_cash ?? 0, 0, ',', '.') }}</div>
                            <div class="stat-sub">Modal: Rp {{ number_format($shift->starting_cash ?? 0, 0, ',', '.') }}</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Pengeluaran Kas</div>
                            <div class="stat-value" style="color:var(--danger);" x-text="'Rp ' + Number(expensesTotal).toLocaleString('id-ID')">Rp {{ number_format($expenses_total ?? 0, 0, ',', '.') }}</div>
                            <div class="stat-sub">Petty cash shift ini</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Belum Dibayar</div>
                            <div class="stat-value" style="color:var(--warning);" x-text="runningOrders.length">{{ isset($orders) ? $orders->whereIn('payment_status', ['unpaid', 'partial'])->count() : 0 }}</div>
                            <div class="stat-sub">pesanan tagihan terbuka</div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="tabs">
                    <div class="tab" :class="activeTab==='new-order'?'active':''" @click="activeTab='new-order'">➕ Pesanan</div>
                    <div class="tab" :class="activeTab==='running-orders'?'active':''" @click="activeTab='running-orders'">🔄 Pesanan Berjalan</div>
                    <div class="tab" :class="activeTab==='history'?'active':''" @click="activeTab='history'">📜 Riwayat</div>
                    <div class="tab" :class="activeTab==='shift'?'active':''" @click="activeTab='shift'">⏱ Shift & Kas</div>
                    <div class="tab" :class="activeTab==='expense'?'active':''" @click="activeTab='expense'">💸 Uang Keluar</div>
                </div>
                <div class="tab-panel" :class="activeTab==='new-order'?'active':''">
                    <div style="display:grid;grid-template-columns:1fr 320px;gap:1.4rem;align-items:start;">
                        <!-- Produk -->
                        <div>
                            <div style="font-weight:700;margin-bottom:1rem;">Pilih Menu</div>
                            <div class="product-grid">
                                @if(isset($products) && $products->count() > 0)
                                    @foreach($products as $prod)
                                        <div class="product-tile" :class="cartHasItem({{ $prod->id }}) ? 'added' : ''"
                                             @click="addToCart({{ $prod->id }}, '{{ addslashes($prod->name) }}', {{ $prod->price }}, {{ $prod->stock }})">
                                            <div class="product-tile-name">{{ $prod->name }}</div>
                                            <div class="product-tile-price">Rp {{ number_format($prod->price, 0, ',', '.') }}</div>
                                            <div class="product-tile-stock">Stok: {{ $prod->stock }}</div>
                                        </div>
                                    @endforeach
                                @else
                                    <div style="grid-column:span 4;text-align:center;color:var(--text-muted);padding:2rem;">Belum ada produk aktif.</div>
                                @endif
                            </div>
                        </div>

                        <!-- Cart Panel -->
                        <div style="position:sticky;top:70px;">
                            <div class="card" style="margin-bottom:0;">
                                <div class="card-header">
                                    <h3>Keranjang</h3>
                                    <button class="btn btn-ghost btn-sm" @click="clearCart()">Kosongkan</button>
                                </div>

                                <template x-if="editingOrderId !== null">
                                    <div style="background: rgba(230,115,0,0.15); border: 1px solid var(--primary); padding: 0.6rem; border-radius: var(--radius-sm); margin-bottom: 0.8rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem;">
                                        <div>
                                            <span style="font-weight: bold; color: var(--primary);">⚡ Mode Ubah Pesanan</span>
                                        </div>
                                        <button class="btn btn-danger btn-sm" style="padding: 0.2rem 0.5rem;" @click="cancelEdit()">Batal</button>
                                    </div>
                                </template>

                                <div class="cart-box">
                                    <template x-if="cart.length === 0">
                                        <div class="cart-box-empty">🛒 Pilih menu di sebelah kiri</div>
                                    </template>
                                    <template x-for="item in cart" :key="item.id">
                                        <div class="cart-row">
                                            <div>
                                                <div style="font-weight:600;font-size:0.82rem;" x-text="item.name"></div>
                                                <div style="font-size:0.73rem;color:var(--primary);" x-text="'Rp ' + (item.price * item.qty).toLocaleString('id-ID')"></div>
                                            </div>
                                            <div class="cart-qty">
                                                <button class="qty-btn" @click="changeQty(item.id, -1)">-</button>
                                                <span style="font-weight:700;min-width:18px;text-align:center;font-size:0.85rem;" x-text="item.qty"></span>
                                                <button class="qty-btn" @click="changeQty(item.id, 1)">+</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div style="padding:0.7rem 0;border-top:1px solid var(--border);margin-top:0.6rem;display:flex;justify-content:space-between;font-weight:800;font-size:0.95rem;">
                                    <span>Total</span>
                                    <span style="color:var(--primary);" x-text="'Rp ' + cartTotal.toLocaleString('id-ID')"></span>
                                </div>

                                <div style="display:flex;flex-direction:column;gap:0.65rem;margin-top:0.6rem;">
                                    <div class="form-group">
                                        <label>Nama Pelanggan / Alias</label>
                                        <input type="text" x-model="customerName" class="form-control"
                                               placeholder="Contoh: Pak Budi, Meja 3, atau Tamu 12..." required>
                                    </div>
                                    <div class="form-group">
                                        <label>Jenis Order</label>
                                        <select x-model="newOrderType" class="form-control">
                                            <option value="dine_in">🪑 Dine In (Meja)</option>
                                            <option value="takeaway">🛍 Takeaway</option>
                                        </select>
                                    </div>
                                    <div class="form-group" x-show="newOrderType === 'dine_in'">
                                        <label>Pilih Meja</label>
                                        <select x-model="newTableId" class="form-control">
                                            <option value="">-- Pilih Meja --</option>
                                            @foreach($tables ?? [] as $t)
                                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->status }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <button class="btn btn-primary" style="width:100%;margin-top:1.2rem;padding:0.7rem;"
                                        :disabled="cart.length === 0 || loading" @click="submitOrder()">
                                    <span x-show="!loading" x-text="editingOrderId ? '✓ Simpan Perubahan' : '✓ Terima Pesanan'"></span>
                                    <span x-show="loading">Memproses...</span>
                                </button>

                                <div x-show="statusMsg" style="margin-top:0.6rem;padding:0.6rem;border-radius:var(--radius-sm);text-align:center;font-size:0.82rem;font-weight:600;"
                                     :style="statusType==='success'?'background:rgba(40,167,69,0.12);color:var(--success);':'background:rgba(255,59,48,0.12);color:var(--danger);'"
                                     x-text="statusMsg"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB: 🔄 Pesanan Berjalan -->
                <div class="tab-panel" :class="activeTab==='running-orders'?'active':''">
                    <div style="font-weight:700;margin-bottom:1rem;">Daftar Pesanan Berjalan</div>
                    <div class="order-list">
                        <template x-if="runningOrders.length > 0">
                            <template x-for="order in runningOrders" :key="order.id">
                                <div class="order-card">
                                    <div class="order-card-header" @click="toggleOrder(order.id)">
                                        <div class="order-meta">
                                            <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                                                <div class="order-number" x-text="order.order_number"></div>
                                                <div class="order-time" x-text="formatTime(order.created_at)"></div>
                                            </div>
                                            <!-- Order Source / Delivery Badge -->
                                            <span class="order-label" 
                                                  :style="order.order_source === 'online' ? 'background:rgba(230,115,0,0.15); color:var(--primary); font-weight:700;' : (order.order_source === 'qr' ? 'background:rgba(0,122,255,0.15); color:#007aff; font-weight:700;' : 'background:rgba(255,255,255,0.06); color:var(--text-muted);')"
                                                  x-text="order.order_source === 'online' ? '🛵 Online' : (order.order_source === 'qr' ? '📱 via Unimart QR' : '🖥 POS Kasir')">
                                            </span>
                                            
                                            <span class="order-label" x-show="order.table" x-text="'🪑 ' + (order.table ? order.table.name : '')"></span>
                                            <span class="order-customer" x-show="order.customer_name" x-text="'👤 ' + order.customer_name"></span>
                                            
                                            <span class="badge" 
                                                  :style="order.kitchen_status === 'ready' ? 'background:var(--success); color:#fff;' : 'background:rgba(230,115,0,0.15); color:var(--warning);'"
                                                  x-text="'Dapur: ' + order.kitchen_status.toUpperCase()">
                                            </span>
                                        </div>
                                        <div style="display:flex;align-items:center;gap:0.4rem;" @click.stop>
                                            <span style="font-weight:700;font-size:0.88rem;margin-right:0.4rem;" x-text="'Rp ' + Number(order.total_amount).toLocaleString('id-ID')"></span>
                                            <button class="btn btn-ghost btn-sm" @click="editOrder(order)">✏️ Ubah</button>
                                            <button class="btn btn-success btn-sm" @click="openPaymentModal(order)">💳 Bayar</button>
                                            <span style="color:var(--text-muted);font-size:0.8rem;cursor:pointer;" @click="toggleOrder(order.id)" x-text="expandedOrder===order.id?'▲':'▼'"></span>
                                        </div>
                                    </div>

                                    <div class="order-card-body" :class="expandedOrder===order.id?'open':''">
                                        <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.5rem;">Item Pesanan</div>
                                        <template x-for="item in order.items" :key="item.id">
                                            <div class="order-item-row">
                                                <div style="display:flex;align-items:center;gap:0.5rem;">
                                                    <span :style="item.status==='void' ? 'text-decoration:line-through;opacity:0.4;' : ''" x-text="item.product ? item.product.name : (item.product_name || 'Menu')"></span>
                                                    <span class="badge" :class="'badge-' + item.status" x-text="item.status"></span>
                                                </div>
                                                <div style="display:flex;align-items:center;gap:0.7rem;">
                                                    <span style="color:var(--text-muted);" x-text="item.quantity + 'x Rp ' + Number(item.price).toLocaleString('id-ID')"></span>
                                                    <span style="font-weight:600;" x-text="'= Rp ' + (item.price * item.quantity).toLocaleString('id-ID')"></span>
                                                </div>
                                            </div>
                                        </template>
                                        
                                        <!-- Delivery Fee & Address details -->
                                        <template x-if="order.order_source === 'online'">
                                            <div style="margin-top:0.8rem; padding-top:0.8rem; border-top:1px dashed var(--border); font-size:0.8rem; display:flex; flex-direction:column; gap:0.3rem;">
                                                <div style="display:flex; justify-content:space-between; color:var(--text-muted);">
                                                    <span>Ongkir Delivery</span>
                                                    <span style="font-weight:600; color:var(--text);" x-text="'Rp ' + Number(order.delivery_fee).toLocaleString('id-ID')"></span>
                                                </div>
                                                <div style="color:var(--text-muted); font-size:0.75rem;">
                                                    <strong>Alamat Antar:</strong> <span style="color:var(--text);" x-text="order.delivery_address"></span>
                                                </div>
                                            </div>
                                        </template>

                                        <div class="order-total-row">
                                            <span>Total Tagihan</span>
                                            <span style="color:var(--primary);" x-text="'Rp ' + Number(order.total_amount).toLocaleString('id-ID')"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </template>
                        <template x-if="runningOrders.length === 0">
                            <div style="text-align:center;padding:3rem;color:var(--text-muted);">
                                <div style="font-size:2.5rem;margin-bottom:0.8rem;">🔄</div>
                                <p>Tidak ada pesanan berjalan.</p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- TAB: 📜 Riwayat          -->
                <div class="tab-panel" :class="activeTab==='history'?'active':''">
                    <div style="font-weight:700;margin-bottom:1rem;">Riwayat Transaksi Hari Ini</div>
                    <div class="order-list">
                        <template x-if="historyOrders.length > 0">
                            <template x-for="order in historyOrders" :key="order.id">
                                <div class="order-card">
                                    <div class="order-card-header" @click="toggleOrder(order.id)">
                                        <div class="order-meta">
                                            <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                                                <div class="order-number" x-text="order.order_number"></div>
                                                <div class="order-time" x-text="formatTime(order.created_at)"></div>
                                            </div>
                                            <!-- Order Source Badge -->
                                            <span class="order-label" 
                                                  :style="order.order_source === 'online' ? 'background:rgba(230,115,0,0.15); color:var(--primary); font-weight:700;' : (order.order_source === 'qr' ? 'background:rgba(0,122,255,0.15); color:#007aff; font-weight:700;' : 'background:rgba(255,255,255,0.06); color:var(--text-muted);')"
                                                  x-text="order.order_source === 'online' ? '🛵 Online' : (order.order_source === 'qr' ? '📱 via Unimart QR' : '🖥 POS Kasir')">
                                            </span>

                                            <span class="order-label" x-show="order.table" x-text="'🪑 ' + (order.table ? order.table.name : '')"></span>
                                            <span class="order-customer" x-show="order.customer_name" x-text="'👤 ' + order.customer_name"></span>
                                        </div>
                                        <div style="display:flex;align-items:center;gap:0.6rem;">
                                            <span style="font-weight:700;font-size:0.88rem;" x-text="'Rp ' + Number(order.total_amount).toLocaleString('id-ID')"></span>
                                            <span class="badge" :class="'badge-' + order.payment_status" x-text="order.payment_status === 'void' ? 'Batal' : (order.payment_status === 'paid' ? 'Lunas' : order.payment_status)"></span>
                                            <span style="color:var(--text-muted);font-size:0.8rem;" x-text="expandedOrder===order.id?'▲':'▼'"></span>
                                        </div>
                                    </div>

                                    <div class="order-card-body" :class="expandedOrder===order.id?'open':''">
                                        <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.5rem;">Item Pesanan</div>
                                        <template x-for="item in order.items" :key="item.id">
                                            <div class="order-item-row">
                                                <span x-text="item.product ? item.product.name : (item.product_name || 'Menu')"></span>
                                                <div style="display:flex;align-items:center;gap:0.7rem;">
                                                    <span style="color:var(--text-muted);" x-text="item.quantity + 'x Rp ' + Number(item.price).toLocaleString('id-ID')"></span>
                                                    <span style="font-weight:600;" x-text="'= Rp ' + (item.price * item.quantity).toLocaleString('id-ID')"></span>
                                                </div>
                                            </div>
                                        </template>

                                        <!-- Delivery details -->
                                        <template x-if="order.order_source === 'online'">
                                            <div style="margin-top:0.8rem; padding-top:0.8rem; border-top:1px dashed var(--border); font-size:0.8rem; display:flex; flex-direction:column; gap:0.3rem;">
                                                <div style="display:flex; justify-content:space-between; color:var(--text-muted);">
                                                    <span>Ongkir Delivery</span>
                                                    <span style="font-weight:600; color:var(--text);" x-text="'Rp ' + Number(order.delivery_fee).toLocaleString('id-ID')"></span>
                                                </div>
                                                <div style="color:var(--text-muted); font-size:0.75rem;">
                                                    <strong>Alamat Antar:</strong> <span style="color:var(--text);" x-text="order.delivery_address"></span>
                                                </div>
                                            </div>
                                        </template>

                                        <div class="order-total-row">
                                            <span>Total Transaksi</span>
                                            <span style="color:var(--primary);" x-text="'Rp ' + Number(order.total_amount).toLocaleString('id-ID')"></span>
                                        </div>

                                        <template x-if="order.payments && order.payments.length > 0">
                                            <div>
                                                <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin:0.8rem 0 0.4rem;">Detail Pembayaran</div>
                                                <template x-for="pay in order.payments" :key="pay.id">
                                                    <div class="payment-row">
                                                        <span x-text="(pay.payment_method === 'qris' ? 'QRIS Resmi Toko' : pay.payment_method.toUpperCase()) + ' — ' + formatTime(pay.created_at)"></span>
                                                        <span style="color:var(--success);font-weight:600;" x-text="'Rp ' + Number(pay.amount_paid).toLocaleString('id-ID')"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </template>
                        <template x-if="historyOrders.length === 0">
                            <div style="text-align:center;padding:3rem;color:var(--text-muted);">
                                <div style="font-size:2.5rem;margin-bottom:0.8rem;">📜</div>
                                <p>Belum ada riwayat transaksi hari ini.</p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- TAB: Shift & Kas        -->
                <div class="tab-panel" :class="activeTab==='shift'?'active':''">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;">
                        @if(isset($shift) && $shift)
                            <div class="card">
                                <div class="card-header"><h3>Status Shift Aktif</h3></div>
                                <div class="shift-info-grid">
                                    <div class="shift-info-item"><div class="shift-info-label">ID Shift</div><div class="shift-info-value">#{{ $shift->id }}</div></div>
                                    <div class="shift-info-item"><div class="shift-info-label">Mulai Kerja</div><div class="shift-info-value" style="font-size:0.85rem;">{{ $shift->opened_at->format('H:i, d M') }}</div></div>
                                    <div class="shift-info-item"><div class="shift-info-label">Modal Awal</div><div class="shift-info-value">Rp {{ number_format($shift->starting_cash, 0, ',', '.') }}</div></div>
                                    <div class="shift-info-item"><div class="shift-info-label">Saldo Sistem</div><div class="shift-info-value" style="color:var(--success);">Rp {{ number_format($shift->system_ending_cash, 0, ',', '.') }}</div></div>
                                </div>
                                <div style="background:rgba(230,115,0,0.06);border:1px solid rgba(230,115,0,0.2);padding:0.7rem;border-radius:var(--radius-sm);font-size:0.8rem;color:var(--text-muted);">
                                    ⚠️ Tutup shift pada akhir jam kerja untuk serah terima kas ke supervisor.
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header"><h3>Tutup Shift & Serahkan Kas</h3></div>
                                <form action="/staff/shift/close" method="POST">
                                    @csrf
                                    <div class="form-group" style="margin-bottom:0.8rem;">
                                        <label>Uang Fisik di Laci (Hitung Manual!)</label>
                                        <input type="number" name="actual_ending_cash" class="form-control" placeholder="Jumlah uang fisik di laci..." required>
                                    </div>
                                    <div class="form-group" style="margin-bottom:1rem;">
                                        <label>Catatan Serah Terima / Keterangan Selisih</label>
                                        <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Selisih Rp 5.000 karena kembalian pelanggan..."></textarea>
                                    </div>
                                    <button type="button" class="btn btn-danger" style="width:100%;" @click="confirmCloseShift($event)">🔒 Tutup Shift & Serahkan Kas</button>
                                </form>
                            </div>
                        @else
                            <div class="card" style="grid-column:span 2;text-align:center;">
                                <div style="font-size:3rem;margin-bottom:0.8rem;">⏸</div>
                                <h3 style="margin-bottom:0.5rem;">Shift Belum Dibuka</h3>
                                <p style="color:var(--text-muted);margin-bottom:1.5rem;font-size:0.85rem;">Buka shift untuk mulai menerima transaksi dan melacak pendapatan.</p>
                                <form action="/staff/shift/start" method="POST" style="max-width:360px;margin:0 auto;">
                                    @csrf
                                    <div class="form-group" style="margin-bottom:1rem;text-align:left;">
                                        <label>Modal Awal Laci Kasir (Rp)</label>
                                        <input type="number" name="starting_cash" class="form-control" value="500000" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary" style="width:100%;">▶ Mulai Shift Baru</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- TAB: Uang Keluar        -->
                <div class="tab-panel" :class="activeTab==='expense'?'active':''">
                    <div class="card">
                        <div class="card-header"><h3>Catat Pengeluaran Kas (Petty Cash)</h3></div>
                        @if(isset($shift) && $shift)
                            <form action="/staff/expense" method="POST">
                                @csrf
                                <div style="display:flex;gap:0.8rem;flex-wrap:wrap;align-items:flex-end;">
                                    <div class="form-group" style="min-width:180px;">
                                        <label>Nominal (Rp)</label>
                                        <input type="number" name="amount" class="form-control" placeholder="Contoh: 50000" min="1" required>
                                    </div>
                                    <div class="form-group" style="flex:1;min-width:260px;">
                                        <label>Keterangan / Keperluan</label>
                                        <input type="text" name="description" class="form-control" placeholder="Contoh: Beli air galon, beli plastik..." required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Catat Pengeluaran</button>
                                </div>
                            </form>
                            <div style="margin-top:1rem;padding:0.7rem;background:rgba(255,59,48,0.06);border:1px solid rgba(255,59,48,0.15);border-radius:var(--radius-sm);font-size:0.8rem;color:var(--text-muted);">
                                ⚠️ Setiap pengeluaran akan mengurangi saldo sistem laci. Pastikan uang fisik sudah dikeluarkan sebelum mencatat.
                            </div>
                        @else
                            <div style="text-align:center;color:var(--text-muted);padding:2rem;">Buka shift terlebih dahulu sebelum mencatat pengeluaran.</div>
                        @endif
                    </div>
                </div>

                <!-- MODAL 1: Pemberitahuan Pesanan Diterima (Confirm)-->
                <div class="modal-overlay" x-show="showConfirmModal" style="display:none;">
                    <div class="modal-container">
                        <div class="modal-header">
                            <h3 style="color:var(--success);">✓ Pesanan Berhasil Diterima</h3>
                            <button class="btn btn-ghost btn-sm" style="border:none;background:none;font-size:1.2rem;color:var(--text-muted);" @click="showConfirmModal = false; window.location.reload();">✕</button>
                        </div>
                        <div class="modal-body">
                            <div style="text-align:center;margin-bottom:1.2rem;">
                                <div style="font-size:1.6rem;font-weight:800;color:var(--primary);" x-text="createdOrder ? createdOrder.order_number : ''"></div>
                                <div style="font-size:0.85rem;color:var(--text-muted);margin-top:0.2rem;">
                                    Alias: <span style="font-weight:700;color:var(--text);" x-text="createdOrder ? createdOrder.customer_name : '-'"></span>
                                </div>
                            </div>
                            <div style="background:var(--surface-2);border-radius:var(--radius-sm);padding:0.9rem;margin-bottom:1rem;">
                                <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;margin-bottom:0.4rem;">Ringkasan Belanja</div>
                                <template x-if="createdOrder">
                                    <div>
                                        <template x-for="item in createdOrder.items">
                                            <div style="display:flex;justify-content:space-between;font-size:0.82rem;padding:0.25rem 0;">
                                                <span x-text="item.product ? item.product.name : 'Menu'"></span>
                                                <span style="font-weight:600;" x-text="item.quantity + 'x = Rp ' + (item.price * item.quantity).toLocaleString('id-ID')"></span>
                                            </div>
                                        </template>
                                        <div style="border-top:1px solid var(--border);margin-top:0.6rem;padding-top:0.4rem;display:flex;justify-content:space-between;font-weight:700;font-size:0.9rem;">
                                            <span>Total Tagihan</span>
                                            <span style="color:var(--primary);" x-text="'Rp ' + Number(createdOrder.total_amount).toLocaleString('id-ID')"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <p style="font-size:0.8rem;color:var(--text-muted);text-align:center;line-height:1.4;">
                                Silakan pilih apakah pelanggan ingin langsung membayar sekarang atau kirim pesanan ke dapur untuk dipersiapkan staf.
                            </p>
                        </div>
                        <div class="modal-footer" style="display:flex;justify-content:stretch;">
                            <button class="btn btn-ghost" style="flex:1;justify-content:center;padding:0.7rem;" @click="prepareCreatedOrder()">🍳 Siapkan Pesanan</button>
                            <button class="btn btn-primary" style="flex:1;justify-content:center;padding:0.7rem;" @click="payCreatedOrder()">💳 Bayar Sekarang</button>
                        </div>
                    </div>
                </div>

                <!-- MODAL 2: Modal Pembayaran (Universal) -->
                <div class="modal-overlay" x-show="showPaymentModal" style="display:none;" @click.self="showPaymentModal = false">
                    <div class="modal-container">
                        <div class="modal-header">
                            <h3>💰 Pembayaran Pesanan</h3>
                            <button class="btn btn-ghost btn-sm" style="border:none;background:none;font-size:1.2rem;color:var(--text-muted);" @click="showPaymentModal = false">✕</button>
                        </div>
                        <div class="modal-body">
                            <div style="display:flex;justify-content:space-between;margin-bottom:1rem;border-bottom:1px solid var(--border);padding-bottom:0.6rem;">
                                <div>
                                    <div style="font-size:0.9rem;font-weight:700;" x-text="paymentOrder ? paymentOrder.order_number : ''"></div>
                                    <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.15rem;">
                                        Alias: <span style="color:var(--primary);font-weight:600;" x-text="paymentOrder ? paymentOrder.customer_name : '-'"></span>
                                    </div>
                                </div>
                                <div style="text-align:right;">
                                    <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;">Total Tagihan</div>
                                    <div style="font-size:1.15rem;font-weight:800;color:var(--primary);" x-text="'Rp ' + (paymentRemaining ? paymentRemaining.toLocaleString('id-ID') : '0')"></div>
                                </div>
                            </div>

                            <!-- List Item Belanja -->
                            <div style="max-height:120px;overflow-y:auto;background:var(--surface-2);border-radius:var(--radius-sm);padding:0.7rem;margin-bottom:1rem;">
                                <template x-if="paymentOrder">
                                    <div>
                                        <template x-for="item in paymentOrder.items">
                                            <div style="display:flex;justify-content:space-between;font-size:0.78rem;padding:0.2rem 0;">
                                                <span x-text="item.product ? item.product.name : (item.product_name || 'Menu')"></span>
                                                <span style="color:var(--text-muted);" x-text="item.quantity + 'x = Rp ' + (item.price * item.quantity).toLocaleString('id-ID')"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            <form @submit.prevent="submitPayment()">
                                <div style="display:flex;flex-direction:column;gap:0.9rem;">
                                    <!-- Metode Pembayaran -->
                                    <div class="form-group">
                                        <label>Metode Pembayaran</label>
                                        <select x-model="paymentMethod" class="form-control" required>
                                            <option value="cash">💵 Tunai (Cash)</option>
                                            <option value="qris">📱 QRIS Resmi Toko</option>
                                            <option value="transfer">🏦 Transfer Bank</option>
                                        </select>
                                    </div>

                                    <!-- Uang Diterima -->
                                    <div class="form-group" x-show="paymentMethod === 'cash'">
                                        <div style="display:flex;justify-content:space-between;align-items:center;">
                                            <label>Uang Diterima (Rp)</label>
                                            <label class="check-label" style="margin-top:0;">
                                                <input type="checkbox" x-model="exactPay" @change="toggleExactPay()">
                                                Bayar dengan uang pas
                                            </label>
                                        </div>
                                        <input type="number" x-model="cashReceived" class="form-control" style="font-size:1.1rem;font-weight:700;"
                                               :class="exactPay ? 'blur-fade-input' : ''"
                                               :required="paymentMethod === 'cash'" min="1" placeholder="Nominal uang...">
                                        
                                        <!-- Suggestions -->
                                        <div x-show="!exactPay && roundingSuggestions.length > 0" style="margin-top:0.4rem;">
                                            <div style="font-size:0.7rem;color:var(--text-muted);margin-bottom:0.25rem;">Rekomendasi Uang Pecahan:</div>
                                            <div style="display:flex;gap:0.4rem;flex-wrap:wrap;">
                                                <template x-for="suggest in roundingSuggestions" :key="suggest">
                                                    <div class="suggestion-chip" @click="cashReceived = suggest" x-text="'Rp ' + suggest.toLocaleString('id-ID')"></div>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Kembalian -->
                                        <div class="change-box" style="margin-top:0.6rem;">
                                            <div class="change-row">
                                                <span>Kembalian</span>
                                                <span class="change-due" :class="(Number(cashReceived) - paymentRemaining) < 0 ? 'negative' : ''"
                                                      x-text="'Rp ' + paymentChange.toLocaleString('id-ID')"></span>
                                            </div>
                                            <template x-if="Number(cashReceived) < paymentRemaining">
                                                <div style="font-size:0.75rem;color:var(--danger);margin-top:0.25rem;font-weight:600;">⚠️ Uang yang diterima kurang!</div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer" style="justify-content:space-between;">
                            <div style="display:flex;gap:0.5rem;">
                                <button class="btn btn-danger" @click="cancelOrder()">❌ Batal</button>
                                <button class="btn btn-ghost" @click="editOrder(paymentOrder)">✏️ Ubah</button>
                            </div>
                            <div style="display:flex;gap:0.6rem;">
                                <button class="btn btn-ghost" @click="showPaymentModal = false">Tutup</button>
                                <button class="btn btn-success" @click="submitPayment()" :disabled="loading || (paymentMethod === 'cash' && Number(cashReceived) < paymentRemaining)">
                                    <span x-show="!loading">✓ Selesai</span>
                                    <span x-show="loading">Memproses...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- ============================================== -->
            <!-- 2. CHEF WORKSPACE                              -->
            <!-- ============================================== -->
            @if($actingRole === 'chef' || $actingRole === 'owner')
                <div x-show="activeTab === 'chef-kds'">
                    <div class="stats-grid">
                        <div class="stat-card"><div class="stat-label">Disiapkan Hari Ini</div><div class="stat-value" style="color:var(--success);">{{ $prepared_count ?? 0 }}</div><div class="stat-sub">item menu selesai</div></div>
                        <div class="stat-card"><div class="stat-label">Antrean Dapur</div><div class="stat-value" style="color:var(--warning);">{{ $pending_count ?? 0 }}</div><div class="stat-sub">item masih diproses</div></div>
                    </div>
                    <div class="card">
                        <div class="card-header"><h3>Aksi Operasional Dapur</h3></div>
                        <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:1.4rem;line-height:1.4;">Buka layar Monitor Dapur (Kitchen Display System) untuk melihat semua antrean pengerjaan pesanan kopi dan makanan secara real-time.</p>
                        <a href="{{ route('owner.kds', ['alias' => $alias]) }}" class="btn btn-primary" style="font-size:1rem;padding:0.8rem 2rem;">👨‍🍳 Buka Tampilan KDS Dapur</a>
                    </div>
                </div>
            @endif

            <!-- ============================================== -->
            <!-- 3. ADMIN GUDANG WORKSPACE                      -->
            <!-- ============================================== -->
            @if($actingRole === 'admin_gudang' || $actingRole === 'owner')
                <div x-show="activeTab === 'stock-management'">
                    <!-- KPI Stats -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-label">Total Jenis Produk</div>
                            <div class="stat-value" style="color:var(--primary);">{{ $total_products ?? 0 }}</div>
                            <div class="stat-sub">produk terdaftar</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Stok Menipis (<= 5)</div>
                            <div class="stat-value" style="color:var(--warning);">{{ $low_stock_count ?? 0 }}</div>
                            <div class="stat-sub">perlu restock segera</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Stok Habis (0)</div>
                            <div class="stat-value" style="color:var(--danger);">{{ $out_of_stock_count ?? 0 }}</div>
                            <div class="stat-sub">produk kosong</div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3>Daftar Stok Produk</h3>
                        </div>
                        <table class="stock-table">
                            <thead>
                                <tr>
                                    <th>Nama Produk</th>
                                    <th>Harga</th>
                                    <th>Status Stok</th>
                                    <th>Atur Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($warehouse_products) && $warehouse_products->count() > 0)
                                    @foreach($warehouse_products as $prod)
                                        <tr>
                                            <td style="font-weight:600;">{{ $prod->name }}</td>
                                            <td>Rp {{ number_format($prod->price, 0, ',', '.') }}</td>
                                            <td>
                                                @if($prod->stock === 0)
                                                    <span class="badge" style="background:rgba(255,59,48,0.15);color:var(--danger);">Habis</span>
                                                @elseif($prod->stock <= 5)
                                                    <span class="badge" style="background:rgba(230,115,0,0.15);color:var(--warning);">Sisa {{ $prod->stock }}</span>
                                                @else
                                                    <span class="badge" style="background:rgba(40,167,69,0.15);color:var(--success);">Tersedia ({{ $prod->stock }})</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div style="display:flex;gap:0.5rem;align-items:center;" x-data="{ tempStock: {{ $prod->stock }} }">
                                                    <input type="number" class="form-control" style="width:75px;padding:0.35rem 0.5rem;font-size:0.8rem;text-align:center;"
                                                           x-model="tempStock" min="0" required>
                                                    <button class="btn btn-primary btn-sm" @click="updateStock({{ $prod->id }}, tempStock, '{{ addslashes($prod->name) }}')">✓ Simpan</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" style="text-align:center;color:var(--text-muted);padding:2rem;">Belum ada produk terdaftar.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- ============================================== -->
            <!-- 4. KURIR WORKSPACE                             -->
            <!-- ============================================== -->
            @if($actingRole === 'kurir' || $actingRole === 'owner')
                <div x-show="activeTab === 'delivery-queue'">
                <!-- KPI Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-label">Menunggu Pengiriman</div>
                        <div class="stat-value" style="color:var(--warning);" x-text="pendingDeliveries">{{ $pending_deliveries ?? 0 }}</div>
                        <div class="stat-sub">siap diantar kurir</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Selesai Diantar Hari Ini</div>
                        <div class="stat-value" style="color:var(--success);" x-text="deliveredToday">{{ $delivered_today ?? 0 }}</div>
                        <div class="stat-sub">transaksi terkirim</div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Antrean Pengiriman</h3>
                    </div>
                    <div class="order-list">
                        <template x-if="orders.length > 0">
                            <template x-for="order in orders" :key="order.id">
                                <div class="order-card">
                                    <div class="order-card-header" style="cursor:default;">
                                        <div class="order-meta">
                                            <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                                                <div class="order-number" x-text="order.order_number"></div>
                                                <div class="order-time" x-text="formatTime(order.created_at)"></div>
                                            </div>
                                            <!-- Order Source Badge -->
                                            <span class="order-label" 
                                                  :style="order.order_source === 'online' ? 'background:rgba(230,115,0,0.15); color:var(--primary); font-weight:700;' : (order.order_source === 'qr' ? 'background:rgba(0,122,255,0.15); color:#007aff; font-weight:700;' : 'background:rgba(255,255,255,0.06); color:var(--text-muted);')"
                                                  x-text="order.order_source === 'online' ? '🛵 Online' : (order.order_source === 'qr' ? '📱 via Unimart QR' : '🖥 POS Kasir')">
                                            </span>

                                            <span class="order-label" x-show="order.table" x-text="'🪑 ' + (order.table ? order.table.name : '')"></span>
                                            <span class="order-customer" x-show="order.customer_name" x-text="'👤 ' + order.customer_name"></span>
                                        </div>
                                        <div style="display:flex;align-items:center;gap:0.8rem;">
                                            <span style="font-weight:700;font-size:0.88rem;" x-text="'Rp ' + Number(order.total_amount).toLocaleString('id-ID')"></span>
                                            <template x-if="order.kitchen_status === 'ready'">
                                                <button class="btn btn-primary btn-sm" @click="finishDelivery(order.id, order.order_number)">🛵 Selesai Antar</button>
                                            </template>
                                            <template x-if="order.kitchen_status !== 'ready'">
                                                <span class="badge badge-paid">✓ Terkirim</span>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="order-card-body open" style="border-top:1px dashed var(--border); padding-top:0.6rem; margin-top:0.4rem; font-size:0.8rem;" x-show="order.order_source === 'online'">
                                        <strong>Alamat Antar:</strong> <span x-text="order.delivery_address"></span>
                                    </div>
                                </div>
                            </template>
                        </template>
                        <template x-if="orders.length === 0">
                            <div style="text-align:center;padding:3rem;color:var(--text-muted);">
                                <div style="font-size:2.5rem;margin-bottom:0.8rem;">🛵</div>
                                <p>Tidak ada antrean pengiriman saat ini.</p>
                            </div>
                        </template>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

    <!-- Blur Backdrop Overlay for Dialog Modal -->
    <div id="dialog-overlay" class="modal-overlay" style="display: none; z-index: 999999; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
        <div class="modal-container" style="max-width: 400px; text-align: center; border: 1px solid rgba(255,255,255,0.08);">
            <div class="modal-body" style="padding: 2.2rem 1.8rem;">
                <div id="dialog-icon" style="font-size: 3.8rem; margin-bottom: 0.8rem;">⚠️</div>
                <h3 id="dialog-title" style="font-size: 1.4rem; font-weight: 800; color: #fff; margin-bottom: 0.8rem;">Pemberitahuan</h3>
                <p id="dialog-message" style="color: var(--text-muted); font-size: 0.92rem; line-height: 1.6; margin-bottom: 1.8rem; white-space: pre-line;"></p>
                <div style="display: flex; gap: 0.8rem; justify-content: center;">
                    <button id="dialog-btn-cancel" class="btn btn-ghost" style="flex: 1; padding: 0.75rem; font-weight: 700;">Batal</button>
                    <button id="dialog-btn-ok" class="btn btn-primary" style="flex: 1; padding: 0.75rem; font-weight: 700; background: var(--primary);">Paham</button>
                </div>
            </div>
        </div>
    </div>

<script>
function showAlertDialog(title, message, type = 'warning') {
    return new Promise((resolve) => {
        const overlay = document.getElementById('dialog-overlay');
        const iconEl = document.getElementById('dialog-icon');
        const titleEl = document.getElementById('dialog-title');
        const messageEl = document.getElementById('dialog-message');
        const btnOk = document.getElementById('dialog-btn-ok');
        const btnCancel = document.getElementById('dialog-btn-cancel');

        if (type === 'success') {
            iconEl.textContent = '✓';
            iconEl.style.color = 'var(--success)';
        } else if (type === 'error') {
            iconEl.textContent = '❌';
            iconEl.style.color = 'var(--danger)';
        } else {
            iconEl.textContent = '⚠️';
            iconEl.style.color = 'var(--warning)';
        }

        titleEl.textContent = title;
        messageEl.innerHTML = message;

        btnCancel.style.display = 'none';
        btnOk.textContent = 'Paham';
        btnOk.style.flex = '1';

        overlay.style.display = 'flex';

        btnOk.onclick = () => {
            overlay.style.display = 'none';
            resolve(true);
        };
    });
}

function showConfirmDialog(title, message, type = 'warning') {
    return new Promise((resolve) => {
        const overlay = document.getElementById('dialog-overlay');
        const iconEl = document.getElementById('dialog-icon');
        const titleEl = document.getElementById('dialog-title');
        const messageEl = document.getElementById('dialog-message');
        const btnOk = document.getElementById('dialog-btn-ok');
        const btnCancel = document.getElementById('dialog-btn-cancel');

        iconEl.textContent = '❓';
        iconEl.style.color = 'var(--primary)';

        titleEl.textContent = title;
        messageEl.innerHTML = message;

        btnCancel.style.display = 'block';
        btnCancel.textContent = 'Batal';
        btnOk.textContent = 'Ya, Lanjutkan';

        overlay.style.display = 'flex';

        btnOk.onclick = () => {
            overlay.style.display = 'none';
            resolve(true);
        };

        btnCancel.onclick = () => {
            overlay.style.display = 'none';
            resolve(false);
        };
    });
}

function staffDashboard() {
    return {
        activeTab: 'new-order',
        mobileSidebarOpen: false,
        getGreetingTime() {
            const hrs = new Date().getHours();
            if (hrs >= 5 && hrs < 11) return 'Pagi';
            if (hrs >= 11 && hrs < 15) return 'Siang';
            if (hrs >= 15 && hrs < 18) return 'Sore';
            return 'Malam';
        },
        getAIQuote() {
            const quotes = [
                "Setiap senyuman ramah kepada pelanggan adalah langkah kecil menuju kesuksesan besar tokomu!",
                "Semoga hari ini kafe kita ramai pengunjung dan jualan laris manis! Tetap semangat ya!",
                "Dapur harum, kopi nikmat, pelanggan senang. Ayo buat hari ini luar biasa!",
                "Kerja tim hari ini adalah kunci sukses hari esok. Semangat melayani, Kak!"
            ];
            const day = new Date().getDate();
            return quotes[day % quotes.length];
        },
        expandedOrder: null,
        cart: [],
        customerName: '',
        newOrderType: 'dine_in',
        newTableId: '',
        loading: false,
        statusMsg: '',
        statusType: '',
        editingOrderId: null,

        // Modals State
        showConfirmModal: false,
        showPaymentModal: false,
        createdOrder: null,
        paymentOrder: null,

        // Payment detail calculation
        paymentMethod: 'cash',
        exactPay: true,
        cashReceived: 0,
        paymentRemaining: 0,
        roundingSuggestions: [],

        // Real-time properties
        orders: @json($orders ?? []),
        salesTotal: {{ $sales_total ?? 0 }},
        salesCount: {{ $sales_count ?? 0 }},
        expensesTotal: {{ $expenses_total ?? 0 }},
        systemEndingCash: {{ isset($shift) ? ($shift->starting_cash + ($sales_total ?? 0) - ($expenses_total ?? 0)) : 0 }},
        preparedCount: {{ $prepared_count ?? 0 }},
        pendingCount: {{ $pending_count ?? 0 }},
        deliveredCount: {{ $delivered_count ?? 0 }},
        pendingDeliveries: {{ $pending_deliveries ?? 0 }},
        deliveredToday: {{ $delivered_today ?? 0 }},

        get runningOrders() {
            return this.orders.filter(o => ['unpaid', 'partial'].includes(o.payment_status) && o.kitchen_status !== 'void');
        },
        get historyOrders() {
            return this.orders.filter(o => ['paid', 'void'].includes(o.payment_status) || o.kitchen_status === 'void');
        },

        formatTime(dateString) {
            if (!dateString) return '';
            const d = new Date(dateString);
            const hrs = String(d.getHours()).padStart(2, '0');
            const mins = String(d.getMinutes()).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const month = months[d.getMonth()];
            return `${hrs}:${mins}, ${day} ${month}`;
        },

        fetchOrdersData() {
            fetch('/staff/orders-data')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.orders = data.orders;
                        this.salesTotal = data.sales_total;
                        this.expensesTotal = data.expenses_total;
                        this.systemEndingCash = data.system_ending_cash;
                        this.preparedCount = data.prepared_count;
                        this.pendingCount = data.pending_count;
                        this.deliveredCount = data.delivered_count;
                        this.pendingDeliveries = data.pending_deliveries;
                        this.deliveredToday = data.delivered_today;
                    }
                })
                .catch(err => console.error('Failed to fetch real-time data:', err));
        },

        init() {
            // Set activeTab depending on user role
            @if($user->isCashier())
                this.activeTab = 'new-order';
                @if(!isset($shift) || !$shift)
                    this.activeTab = 'shift';
                @endif
            @elseif($user->isAdminGudang())
                this.activeTab = 'stock-management';
            @elseif($user->isKurir())
                this.activeTab = 'delivery-queue';
            @endif

            // Setup Socket.io Gateway
            const tenantId = '{{ $tenant ? $tenant->id : 0 }}';
            if (tenantId !== '0') {
                try {
                    const gatewayUrl = '{{ \App\Services\GatewayService::getClientSocketUrl() }}';
                    this.socket = io(gatewayUrl);
                    this.socket.on('connect', () => {
                        this.socket.emit('join', 'caffe-' + tenantId + '-kitchen');
                    });
                    this.socket.on('new-order', (data) => {
                        this.fetchOrdersData();
                    });
                    this.socket.on('order-ready', (data) => {
                        this.fetchOrdersData();
                    });
                    this.socket.on('order-updated', (data) => {
                        this.fetchOrdersData();
                    });
                } catch (e) {
                    console.error('Socket connection error:', e);
                }
            }
        },

        toggleOrder(id) { this.expandedOrder = this.expandedOrder === id ? null : id; },
        cartHasItem(id) { return this.cart.some(i => i.id === id); },
        addToCart(id, name, price, stock) {
            const existing = this.cart.find(i => i.id === id);
            if (existing) { if (existing.qty < stock) existing.qty++; }
            else { this.cart.push({ id, name, price, qty: 1, stock }); }
        },
        changeQty(id, delta) {
            const item = this.cart.find(i => i.id === id);
            if (!item) return;
            item.qty += delta;
            if (item.qty <= 0) this.cart = this.cart.filter(i => i.id !== id);
            else if (item.qty > item.stock) item.qty = item.stock;
        },
        clearCart() { this.cart = []; this.customerName = ''; },
        get cartTotal() { return this.cart.reduce((sum, i) => sum + (i.price * i.qty), 0); },

        editOrder(order) {
            this.editingOrderId = order.id;
            this.cart = order.items.map(item => {
                return {
                    id: item.product_id,
                    name: item.product ? item.product.name : (item.product_name || 'Menu'),
                    price: item.price,
                    qty: item.quantity,
                    stock: item.product ? (item.product.stock + item.quantity) : (item.quantity + 10)
                };
            });
            this.customerName = order.customer_name || '';
            this.newOrderType = order.order_type || 'dine_in';
            this.newTableId = order.table_id || '';
            this.showPaymentModal = false;
            this.showConfirmModal = false;
            this.activeTab = 'new-order';
        },
        cancelEdit() {
            this.editingOrderId = null;
            this.clearCart();
            this.newTableId = '';
        },
        async confirmCloseShift(event) {
            const ok = await showConfirmDialog('Tutup Shift', 'Apakah Anda yakin ingin menutup shift sekarang? Kas akan diserahkan dan shift ini akan diakhiri.', 'warning');
            if (ok) {
                event.target.closest('form').submit();
            }
        },

        // Terima / Simpan Perubahan Pesanan
        async submitOrder() {
            if (this.cart.length === 0) {
                await showAlertDialog('Keranjang Kosong', 'Keranjang belanja masih kosong. Silakan pilih menu terlebih dahulu.', 'warning');
                return;
            }

            // Validasi nama alias pelanggan
            if (!this.customerName || this.customerName.trim() === '') {
                this.statusType = 'error';
                this.statusMsg = '⚠️ Nama Pelanggan / Alias wajib diisi agar kasir & koki tahu pesanan milik siapa.';
                await showAlertDialog('Kolom Wajib Diisi', 'Mohon masukkan nama pelanggan atau alias terlebih dahulu pada input "Alias Pelanggan" di atas keranjang.', 'warning');
                return;
            }

            // Validasi nomor meja jika tipe dine_in
            if (this.newOrderType === 'dine_in' && !this.newTableId) {
                this.statusType = 'error';
                this.statusMsg = '⚠️ Silakan pilih Nomor Meja terlebih dahulu untuk pesanan makan di tempat (Dine In).';
                await showAlertDialog('Meja Belum Dipilih', 'Untuk tipe makan di tempat (Dine In), Anda wajib memilih nomor meja pada pilihan "Nomor Meja" di atas keranjang.', 'warning');
                return;
            }

            this.loading = true;
            this.statusMsg = '';

            const payload = {
                items: this.cart.map(i => ({ id: i.id, qty: i.qty })),
                customer_name: this.customerName || null,
                order_type: this.newOrderType,
                table_id: this.newOrderType === 'dine_in' ? this.newTableId : null,
                _token: '{{ csrf_token() }}'
            };

            const url = this.editingOrderId 
                ? `/staff/order/${this.editingOrderId}/update` 
                : `/caffe/{{ $alias }}/pos/checkout`;

            if (!this.editingOrderId) {
                payload.payment_method = 'later';
            }

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(async res => {
                let data;
                try {
                    data = await res.json();
                } catch (e) {
                    data = {};
                }

                if (res.ok && (data.success || res.status === 200)) {
                    if (this.editingOrderId) {
                        await showAlertDialog('Berhasil', 'Pesanan berhasil diperbarui.', 'success');
                        this.cancelEdit();
                        this.activeTab = 'running-orders';
                        this.fetchOrdersData();
                    } else {
                        this.createdOrder = data.order;
                        this.clearCart();
                        this.newTableId = '';
                        this.showConfirmModal = true;
                    }
                } else {
                    const errorMsg = data.message || 'Gagal menyimpan pesanan. Silakan periksa kembali data Anda.';
                    this.statusType = 'error';
                    this.statusMsg = errorMsg;
                    await showAlertDialog('Terjadi Kendala', errorMsg, 'error');
                }
            })
            .catch(async (err) => {
                this.statusType = 'error';
                this.statusMsg = 'Terjadi kesalahan jaringan atau server.';
                await showAlertDialog('Kesalahan Jaringan', 'Gagal terhubung ke server. Pastikan koneksi internet Anda stabil.', 'error');
            })
            .finally(() => { this.loading = false; });
        },

        // Siapkan Pesanan (send order to kitchen, KDS)
        async prepareCreatedOrder() {
            if (!this.createdOrder) return;
            this.loading = true;
            fetch(`/staff/order/${this.createdOrder.id}/prepare`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ _token: '{{ csrf_token() }}' })
            })
            .then(async res => {
                const data = await res.json();
                if (res.ok && data.success) {
                    this.showConfirmModal = false;
                    this.activeTab = 'running-orders';
                    this.fetchOrdersData();
                } else {
                    await showAlertDialog('Gagal Mengirim', data.message || 'Gagal mengirim pesanan ke dapur.', 'error');
                }
            })
            .catch(async () => {
                await showAlertDialog('Kesalahan Jaringan', 'Gagal menghubungkan ke server.', 'error');
            })
            .finally(() => { this.loading = false; });
        },

        // Bayar Sekarang from Confirm Modal
        payCreatedOrder() {
            const orderToPay = this.createdOrder;
            this.showConfirmModal = false;
            this.openPaymentModal(orderToPay);
        },

        // Open Payment Modal
        openPaymentModal(order) {
            this.paymentOrder = order;
            this.paymentMethod = 'cash';
            this.exactPay = true;

            // Hitung sisa pembayaran
            const totalPaid = order.payments ? order.payments.reduce((sum, p) => sum + Number(p.amount_paid), 0) : 0;
            this.paymentRemaining = order.total_amount - totalPaid;
            this.cashReceived = this.paymentRemaining;

            this.updateSuggestions();
            this.showPaymentModal = true;
        },

        // Pembulatan Rupiah otomatis
        updateSuggestions() {
            const total = this.paymentRemaining;
            if (total <= 0) {
                this.roundingSuggestions = [];
                return;
            }
            const suggestions = [];
            const denominations = [1000, 2000, 5000, 10000, 20000, 50000, 100000];
            denominations.forEach(denom => {
                let val = Math.ceil(total / denom) * denom;
                if (val > total && !suggestions.includes(val)) {
                    suggestions.push(val);
                }
            });
            suggestions.sort((a, b) => a - b);
            this.roundingSuggestions = suggestions.slice(0, 4);
        },

        toggleExactPay() {
            if (this.exactPay) {
                this.cashReceived = this.paymentRemaining;
            } else {
                if (this.roundingSuggestions.length > 0) {
                    this.cashReceived = this.roundingSuggestions[0];
                } else {
                    this.cashReceived = this.paymentRemaining;
                }
            }
        },

        get paymentChange() {
            return Math.max(0, Number(this.cashReceived) - this.paymentRemaining);
        },

        async submitPayment() {
            if (this.paymentMethod === 'cash' && Number(this.cashReceived) < this.paymentRemaining) {
                await showAlertDialog('Pembayaran Kurang', 'Uang diterima kurang dari sisa tagihan!', 'warning');
                return;
            }
            this.loading = true;
            fetch(`/staff/order/${this.paymentOrder.id}/payment`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    payment_method: this.paymentMethod,
                    amount_paid: this.exactPay && this.paymentMethod === 'cash' ? this.paymentRemaining : Number(this.cashReceived),
                    _token: '{{ csrf_token() }}'
                })
            })
            .then(async res => {
                const data = await res.json();
                if (res.ok && data.success) {
                    this.showPaymentModal = false;
                    this.fetchOrdersData();
                } else {
                    await showAlertDialog('Gagal Menyimpan', data.message || 'Gagal menyimpan pembayaran.', 'error');
                }
            })
            .catch(async () => {
                await showAlertDialog('Kesalahan Jaringan', 'Gagal memproses pembayaran.', 'error');
            })
            .finally(() => this.loading = false);
        },

        async cancelOrder() {
            const ok = await showConfirmDialog('Batalkan Pesanan', 'Apakah Anda yakin ingin membatalkan pesanan ini? Semua stok produk akan dikembalikan.', 'warning');
            if (!ok) {
                return;
            }
            this.loading = true;
            fetch(`/staff/order/${this.paymentOrder.id}/cancel`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ _token: '{{ csrf_token() }}' })
            })
            .then(async res => {
                const data = await res.json();
                if (res.ok && data.success) {
                    this.showPaymentModal = false;
                    this.fetchOrdersData();
                } else {
                    await showAlertDialog('Gagal Membatalkan', data.message || 'Gagal membatalkan pesanan.', 'error');
                }
            })
            .catch(async () => {
                await showAlertDialog('Kesalahan Jaringan', 'Gagal membatalkan pesanan.', 'error');
            })
            .finally(() => this.loading = false);
        },

        // Update Stock (Admin Gudang)
        async updateStock(productId, stockValue, productName) {
            if (stockValue === '' || isNaN(stockValue) || Number(stockValue) < 0) {
                await showAlertDialog('Stok Tidak Valid', 'Masukkan jumlah stok yang valid.', 'warning');
                return;
            }
            this.loading = true;
            fetch(`/staff/product/${productId}/update-stock`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    stock: Number(stockValue),
                    _token: '{{ csrf_token() }}'
                })
            })
            .then(async res => {
                const data = await res.json();
                if (res.ok && data.success) {
                    await showAlertDialog('Stok Diperbarui', `Stok <strong>${productName}</strong> berhasil diperbarui menjadi <strong>${stockValue}</strong>.`, 'success');
                    window.location.reload();
                } else {
                    await showAlertDialog('Gagal Update Stok', data.message || 'Gagal memperbarui stok.', 'error');
                }
            })
            .catch(async () => {
                await showAlertDialog('Kesalahan Jaringan', 'Gagal memperbarui stok.', 'error');
            })
            .finally(() => this.loading = false);
        },

        // Selesai Antar (Kurir)
        async finishDelivery(orderId, orderNumber) {
            this.loading = true;
            fetch(`/staff/order/${orderId}/deliver`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ _token: '{{ csrf_token() }}' })
            })
            .then(async res => {
                const data = await res.json();
                if (res.ok && data.success) {
                    await showAlertDialog('Pengiriman Selesai', `Pengantaran pesanan <strong>${orderNumber}</strong> berhasil diselesaikan.`, 'success');
                    this.fetchOrdersData();
                } else {
                    await showAlertDialog('Gagal', data.message || 'Gagal menyelesaikan pengiriman.', 'error');
                }
            })
            .catch(async () => {
                await showAlertDialog('Kesalahan Jaringan', 'Gagal memproses pengiriman.', 'error');
            })
            .finally(() => this.loading = false);
        }
    }
}

function aiAssistantWidget() {
    return {
        isOpen: false,
        userInput: '',
        isTyping: false,
        messages: [],
        toggleChat() {
            this.isOpen = !this.isOpen;
        },
        askQuestion(question) {
            this.userInput = question;
            this.sendMessage();
        },
        sendMessage() {
            if (!this.userInput.trim()) return;
            const userMsg = this.userInput;
            this.messages.push({ sender: 'user', text: userMsg });
            this.userInput = '';
            this.isTyping = true;

            setTimeout(() => {
                const el = document.querySelector('.chat-messages');
                if (el) el.scrollTop = el.scrollHeight;
            }, 50);

            setTimeout(() => {
                let reply = '';
                const query = userMsg.toLowerCase();
                if (query.includes('omset') || query.includes('penjualan') || query.includes('naik')) {
                    reply = "<b>Tips Meningkatkan Penjualan Kafe:</b><br>1. <b>Bundling Menu</b>: Gabungkan kopi terlaris dengan cemilan kecil dengan diskon 10%.<br>2. <b>Promo QR Menu</b>: Buat diskon khusus 5% bagi pelanggan yang melakukan <i>self-order</i> langsung dari meja.<br>3. <b>Upselling</b>: Latih kasir untuk menawarkan tambahan toping atau ukuran <i>large</i> saat mencatat pesanan.";
                } else if (query.includes('shift') || query.includes('tutup') || query.includes('laci')) {
                    reply = "<b>Cara Tutup Shift Kasir (Blind Close):</b><br>1. Masuk ke tab <b>Shift & Kas</b> di dasbor.<br>2. Hitung uang tunai fisik yang ada di laci kasir secara manual.<br>3. Input nominal uang fisik tersebut di kolom input <i>Blind Close</i> lalu tekan tombol <b>Tutup Kasir</b>.<br>4. Sistem akan membandingkan uang fisik dengan hitungan sistem dan mencatat selisihnya demi menghindari kebocoran.";
                } else if (query.includes('kds') || query.includes('dapur') || query.includes('koki')) {
                    reply = "<b>Panduan Kitchen Display System (KDS):</b><br>1. Layar KDS akan berbunyi (beep) secara otomatis jika ada pesanan baru masuk.<br>2. Koki memantau antrean pesanan. Jika pesanan belum selesai dimasak dalam waktu <b>15 menit</b>, kartu pesanan akan berkedip merah (Over SLA).<br>3. Klik tombol <b>Selesai</b> untuk mengubah status makanan menjadi <i>Ready</i> agar pelayan bisa langsung mengantar.";
                } else if (query.includes('motivasi') || query.includes('semangat') || query.includes('kata')) {
                    const quotes = [
                        "<b>Kata Motivasi Hari Ini:</b><br>\"Kesuksesan bukan tentang seberapa banyak kopi yang kita buat, melainkan tentang senyum kebahagiaan pelanggan saat meminumnya. Semangat melayani hari ini!\" ☕✨",
                        "<b>Kata Motivasi Hari Ini:</b><br>\"Kerja keras kita hari ini adalah fondasi bagi perkembangan bisnis Unimart kita di masa depan. Mari layani pelanggan dengan sepenuh hati!\" 💪🌟",
                        "<b>Kata Motivasi Hari Ini:</b><br>\"Setiap tantangan di toko adalah kesempatan untuk belajar menjadi lebih baik. Tetap ceria dan beri pelayanan terbaik!\" 🚀🔥",
                        "<b>Kata Motivasi Hari Ini:</b><br>\"Kafe yang ramai diawali dari tim dapur yang solid dan kasir yang ramah. Semangat melayani, Kak!\" 🍔😊"
                    ];
                    reply = quotes[Math.floor(Math.random() * quotes.length)];
                } else {
                    reply = "Asisten Kasir Pintar siap membantu! Kakak bisa bertanya seputar tips operasional POS kasir, manajemen shift, atau cara kerja layar KDS dapur. Tetap semangat melayani ya! 🌟";
                }
                this.messages.push({ sender: 'assistant', text: reply });
                this.isTyping = false;

                setTimeout(() => {
                    const el = document.querySelector('.chat-messages');
                    if (el) el.scrollTop = el.scrollHeight;
                }, 50);
            }, 1000);
        }
    }
}
</script>

    <!-- Floating AI Chat Widget -->
    <div class="floating-ai-widget" x-data="aiAssistantWidget()" style="position: fixed; bottom: 2rem; right: 2rem; z-index: 9999;">
        <!-- Floating Trigger Button -->
        <button class="ai-trigger-btn" @click="toggleChat()"
                style="width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), #ffa500); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(230,115,0,0.4); transition: transform 0.2s; position: relative;">
            <span style="font-size: 1.6rem; color: var(--bg);">💬</span>
            <span style="position: absolute; top: 0; right: 0; width: 12px; height: 12px; background: var(--success); border: 2px solid var(--bg); border-radius: 50%; box-shadow: 0 0 6px var(--success);"></span>
        </button>

        <!-- Chat Panel Window -->
        <div class="ai-chat-panel" x-show="isOpen" x-transition.scale.origin.bottom.right style="display: none; position: absolute; bottom: 70px; right: 0; width: 340px; height: 420px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow); display: flex; flex-direction: column; overflow: hidden;" x-cloak>
            <!-- Header -->
            <div class="chat-header" style="padding: 0.9rem 1.2rem; background: var(--surface-accent); border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 1.3rem;">🤖</span>
                    <div style="text-align: left;">
                        <h4 style="font-size: 0.88rem; font-weight: 700; color: #fff;">Asisten Kasir Pintar</h4>
                        <span style="font-size: 0.65rem; color: var(--success); font-weight: bold;">● Online (Gemini AI)</span>
                    </div>
                </div>
                <button @click="isOpen = false" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.1rem;">✕</button>
            </div>

            <!-- Messages Area -->
            <div class="chat-messages" style="flex-grow: 1; padding: 1rem; overflow-y: auto; display: flex; flex-direction: column; gap: 0.8rem;">
                <div style="align-self: flex-start; text-align: left; max-width: 85%; background: var(--surface-2); padding: 0.7rem 0.9rem; border-radius: 12px 12px 12px 0; border: 1px solid var(--border); font-size: 0.82rem; line-height: 1.4;">
                    Halo Kak {{ Auth::user()->name }}! Ada yang bisa Asisten bantu hari ini untuk mengelola kafe <strong>{{ $tenant->name }}</strong>? 😊
                </div>

                <template x-for="msg in messages">
                    <div :style="msg.sender === 'user' ? 'align-self: flex-end; text-align: right; max-width: 85%; background: var(--primary); color: var(--bg); padding: 0.7rem 0.9rem; border-radius: 12px 12px 0 12px; font-weight: 500;' : 'align-self: flex-start; text-align: left; max-width: 85%; background: var(--surface-2); padding: 0.7rem 0.9rem; border-radius: 12px 12px 12px 0; border: 1px solid var(--border);'" 
                         style="font-size: 0.82rem; line-height: 1.4;" x-html="msg.text">
                    </div>
                </template>

                <div x-show="isTyping" style="align-self: flex-start; background: var(--surface-2); padding: 0.6rem 0.9rem; border-radius: 12px; display: flex; gap: 0.3rem; align-items: center;">
                    <span style="width: 6px; height: 6px; background: var(--text-muted); border-radius: 50%; animation: bounce 1.4s infinite both;"></span>
                    <span style="width: 6px; height: 6px; background: var(--text-muted); border-radius: 50%; animation: bounce 1.4s infinite both 0.2s;"></span>
                    <span style="width: 6px; height: 6px; background: var(--text-muted); border-radius: 50%; animation: bounce 1.4s infinite both 0.4s;"></span>
                </div>
            </div>

            <!-- Quick Suggestions Chips -->
            <div class="chat-suggestions" x-show="messages.length === 0"
                 style="padding: 0.5rem 0.8rem; display: flex; flex-wrap: wrap; gap: 0.4rem; border-top: 1px solid var(--border); background: rgba(0,0,0,0.15);">
                <span class="suggestion-chip" @click="askQuestion('Bagaimana meningkatkan penjualan?')">🚀 Naikkan Omset</span>
                <span class="suggestion-chip" @click="askQuestion('Bagaimana cara tutup shift kasir?')">⏱ Cara Tutup Shift</span>
                <span class="suggestion-chip" @click="askQuestion('Bagaimana cara Koki menggunakan KDS dapur?')">🍳 Cara Kerja KDS</span>
                <span class="suggestion-chip" @click="askQuestion('Berikan saya kata motivasi hari ini!')">✨ Motivasi Hari Ini</span>
            </div>

            <!-- Input Box -->
            <form @submit.prevent="sendMessage()" style="padding: 0.8rem; border-top: 1px solid var(--border); display: flex; gap: 0.5rem; background: var(--surface-2);">
                <input type="text" x-model="userInput" placeholder="Tanya sesuatu..." 
                       style="flex-grow: 1; padding: 0.45rem 0.8rem; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); color: #fff; font-size: 0.82rem; outline: none;">
                <button type="submit" style="background: var(--primary); border: none; padding: 0.45rem 0.8rem; border-radius: var(--radius-sm); color: var(--bg); font-weight: bold; cursor: pointer; font-size: 0.82rem;">Kirim</button>
            </form>
        </div>
    </div>
</body>
</html>
