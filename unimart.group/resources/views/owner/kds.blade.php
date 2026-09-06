<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tenant->name }} - Monitor Dapur KDS</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Playfair+Display:ital,wght=0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- Alpine.js & Socket.io -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>

    <!-- Custom CSS (Mobile-First & Responsive) -->
    <style>
        :root {
            --primary: hsl(28, 90%, 50%);
            --primary-light: hsl(28, 95%, 62%);
            --background: hsl(210, 30%, 6%);
            --surface: hsl(210, 24%, 10%);
            --surface-accent: hsl(210, 24%, 15%);
            --text-main: hsl(210, 20%, 95%);
            --text-muted: hsl(210, 14%, 65%);
            --border: rgba(255, 255, 255, 0.08);
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            --shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
            --radius-lg: 24px;
            --radius-md: 16px;
            --radius-sm: 8px;
            --accent-red: #ff3b30;
            --accent-green: #28a745;
            --accent-amber: #ffcc00;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--background);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Header */
        header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 1rem 1.2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.2rem;
        }

        .logo span {
            font-family: 'Outfit', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            background: var(--primary);
            color: var(--background);
            padding: 0.1rem 0.4rem;
            border-radius: 20px;
        }

        .header-info {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 0.8rem;
        }

        .status-badge {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.75rem;
            font-weight: 700;
            background: rgba(255,255,255,0.05);
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            border: 1px solid var(--border);
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--accent-red);
        }

        .status-dot.online {
            background: var(--accent-green);
            box-shadow: 0 0 6px var(--accent-green);
        }

        /* Workspace Antrean KDS */
        main {
            padding: 1.2rem 1rem;
            flex-grow: 1;
            overflow-y: auto;
        }

        /* KDS Grid - Mobile First: 1 Kolom */
        .kds-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.2rem;
        }

        /* Card Pesanan */
        .order-card {
            background: var(--surface);
            border: 2px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1.2rem;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 200px;
            transition: var(--transition);
        }

        .order-card.ready {
            border-color: var(--accent-green);
            background: rgba(40, 167, 69, 0.03);
        }

        .order-card.sla-alert {
            border-color: var(--accent-red);
            animation: pulse-red-border 1.5s infinite;
        }

        @keyframes pulse-red-border {
            0% { border-color: var(--border); box-shadow: none; }
            50% { border-color: var(--accent-red); box-shadow: 0 0 12px rgba(255,59,48,0.4); }
            100% { border-color: var(--border); box-shadow: none; }
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.6rem;
            margin-bottom: 0.8rem;
        }

        .table-badge {
            font-size: 1rem;
            font-weight: 800;
            color: var(--primary);
        }

        .timer-badge {
            font-size: 0.8rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
        }

        .timer-badge.alert {
            background: rgba(255,59,48,0.2);
            color: #ff453a;
            border-color: rgba(255,59,48,0.4);
        }

        .card-body {
            flex-grow: 1;
            margin-bottom: 1rem;
        }

        .item-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.95rem;
        }

        .item-qty {
            font-weight: 800;
            color: var(--primary);
            background: rgba(230,115,0,0.1);
            padding: 0.05rem 0.35rem;
            border-radius: 4px;
            font-size: 0.85rem;
        }

        .btn-action {
            width: 100%;
            background: var(--accent-green);
            color: white;
            border: none;
            padding: 0.65rem;
            border-radius: var(--radius-sm);
            font-weight: 700;
            cursor: pointer;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .btn-action:hover {
            opacity: 0.9;
        }

        .btn-action.done {
            background: var(--surface-accent);
            color: var(--text-muted);
            border: 1px solid var(--border);
            cursor: not-allowed;
        }

        /* Tablet & Desktop Layouts (>= 768px) */
        @media (min-width: 768px) {
            main {
                padding: 2rem;
            }

            .logo {
                font-size: 1.5rem;
            }

            .header-info {
                font-size: 0.9rem;
                gap: 1.5rem;
            }

            .kds-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 1.5rem;
            }

            .order-card {
                min-height: 240px;
            }
        }

        @media (min-width: 1200px) {
            .kds-grid {
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
                gap: 2rem;
            }
        }
    </style>
</head>
<body x-data="kdsApp()">

    <!-- Header -->
    <header>
        <div class="logo">
            <a href="{{ auth()->user()->isOwner() ? route('owner.dashboard') : route('staff.dashboard') }}" class="logo">{{ $tenant->name }}<span>KDS</span></a>
        </div>
        <div class="header-info">
            <span style="font-weight: 600; color: var(--accent-amber);">Monitor Dapur</span>
            <div class="status-badge">
                <span class="status-dot" :class="socketConnected ? 'online' : ''"></span>
                <span x-text="socketConnected ? 'Real-time' : 'Offline'">Offline</span>
            </div>
            <a href="{{ auth()->user()->isOwner() ? route('owner.dashboard') : route('staff.dashboard') }}" style="color: var(--text-muted); text-decoration: none;">Dasbor</a>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div x-show="orders.length === 0" style="text-align: center; margin-top: 8rem; color: var(--text-muted);">
            <h2 style="font-size: 1.25rem;">Tidak ada antrean pesanan.</h2>
            <p style="margin-top: 0.5rem; font-size: 0.9rem;">Pesanan baru akan muncul instan.</p>
        </div>

        <div class="kds-grid">
            <template x-for="order in orders" :key="order.id">
                <div class="order-card" :class="order.status === 'ready' ? 'ready' : (order.elapsedTime > 900 ? 'sla-alert' : '')">
                    
                    <!-- Card Header -->
                    <div class="card-header">
                        <div>
                            <span class="table-badge" x-text="order.table_number"></span>
                            <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.1rem;" x-text="order.order_number"></div>
                        </div>
                        <div class="timer-badge" :class="order.elapsedTime > 900 ? 'alert' : ''">
                            <span x-text="formatTime(order.elapsedTime)"></span>
                            <span x-show="order.elapsedTime > 900" style="font-size: 0.6rem; display: block; font-weight: 800; text-align: center;">OVER SLA!</span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body">
                        <ul class="item-list">
                            <template x-for="item in order.items" :key="item.name">
                                <li class="item-row">
                                    <span x-text="item.name" style="font-size: 0.9rem;"></span>
                                    <span class="item-qty" x-text="item.qty + 'x'"></span>
                                </li>
                            </template>
                        </ul>
                    </div>

                    <!-- Card Action Button -->
                    <div>
                        <button class="btn-action" 
                                :class="order.status === 'ready' ? 'done' : ''"
                                @click="completeOrder(order)"
                                :disabled="order.status === 'ready' || order.completing">
                            <span x-show="order.status === 'cooking' && !order.completing">Selesai Memasak</span>
                            <span x-show="order.status === 'ready'">Selesai & Disajikan</span>
                            <span x-show="order.completing">Memproses...</span>
                        </button>
                    </div>

                </div>
            </template>
        </div>
    </main>

    <!-- Script KDS App -->
    <script>
        function kdsApp() {
            return {
                tenantId: {{ $tenant->id }},
                alias: '{{ $alias }}',
                orders: [],
                socketConnected: false,
                socket: null,

                init() {
                    const initialOrders = @json($orders);
                    this.orders = initialOrders.map(o => {
                        const createdTime = new Date(o.created_at).getTime();
                        const nowTime = new Date().getTime();
                        return {
                            id: o.id,
                            order_number: o.order_number,
                            table_number: o.table_number,
                            status: o.status,
                            created_at: o.created_at,
                            elapsedTime: Math.floor((nowTime - createdTime) / 1000),
                            completing: false,
                            items: o.items.map(item => ({
                                name: item.product.name,
                                qty: item.quantity
                            }))
                        };
                    });

                    setInterval(() => {
                        this.orders.forEach(o => {
                            if (o.status === 'cooking') {
                                o.elapsedTime++;
                            }
                        });
                    }, 1000);

                    // Sambungkan ke WebSocket Gateway Node.js
                    const gatewayUrl = '{{ \App\Services\GatewayService::getClientSocketUrl() }}';
                    this.socket = io(gatewayUrl);

                    this.socket.on('connect', () => {
                        this.socketConnected = true;
                        this.socket.emit('join', 'caffe-' + this.tenantId + '-kitchen');
                    });

                    this.socket.on('disconnect', () => {
                        this.socketConnected = false;
                    });

                    this.socket.on('new-order', (data) => {
                        console.log('Pesanan baru diterima via WebSocket:', data);
                        const exist = this.orders.find(o => o.id === data.id);
                        if (!exist) {
                            this.orders.push({
                                id: data.id,
                                order_number: data.order_number,
                                table_number: data.table_number,
                                status: data.status,
                                created_at: data.created_at,
                                elapsedTime: 0,
                                completing: false,
                                items: data.items
                            });
                            this.playBeep();
                        }
                    });
                },

                formatTime(seconds) {
                    const minutes = Math.floor(seconds / 60);
                    const secs = seconds % 60;
                    return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                },

                completeOrder(order) {
                    order.completing = true;

                    fetch(`/caffe/${this.alias}/kds/complete/${order.id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            order.status = 'ready';
                            setTimeout(() => {
                                this.orders = this.orders.filter(o => o.id !== order.id);
                            }, 3000);
                        } else {
                            alert(data.message || 'Gagal menyelesaikan pesanan.');
                        }
                    })
                    .catch(err => {
                        alert('Terjadi kesalahan jaringan.');
                    })
                    .finally(() => {
                        order.completing = false;
                    });
                },

                playBeep() {
                    try {
                        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = audioContext.createOscillator();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(880, audioContext.currentTime);
                        osc.connect(audioContext.destination);
                        osc.start();
                        osc.stop(audioContext.currentTime + 0.3);
                    } catch (e) {
                        console.log('Audio playback blocked by browser.');
                    }
                }
            }
        }
    </script>
</body>
</html>
