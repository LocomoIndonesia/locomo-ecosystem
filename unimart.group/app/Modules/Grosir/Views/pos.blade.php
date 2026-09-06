<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Kasir Grosir - {{ $tenant->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --primary: hsl(38, 95%, 55%);
            --primary-light: hsl(38, 95%, 65%);
            --background: hsl(210, 30%, 8%);
            --surface: hsl(210, 24%, 12%);
            --surface-accent: hsl(210, 24%, 18%);
            --text-main: hsl(210, 20%, 96%);
            --text-muted: hsl(210, 14%, 70%);
            --border: rgba(255, 255, 255, 0.08);
            --danger: hsl(0, 75%, 58%);
            --success: hsl(150, 65%, 42%);
            --radius-md: 14px;
            --radius-sm: 8px;
            --transition: all 0.25s ease;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body { background: var(--background); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; }
        header { background: rgba(15, 23, 30, 0.9); backdrop-filter: blur(12px); border-bottom: 1px solid var(--border); padding: 0.8rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .pos-container { display: grid; grid-template-columns: 1fr 420px; gap: 1.5rem; padding: 1.5rem; max-width: 1400px; width: 100%; margin: 0 auto; flex-grow: 1; }
        @media (max-width: 900px) { .pos-container { grid-template-columns: 1fr; } }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.2rem; }
        .prod-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
        .prod-card { background: var(--surface-accent); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 1rem; cursor: pointer; transition: var(--transition); }
        .prod-card:hover { border-color: var(--primary); transform: translateY(-2px); }
        .btn-primary { background: var(--primary); color: #000; font-weight: 700; border: none; padding: 0.75rem 1.2rem; border-radius: var(--radius-sm); cursor: pointer; width: 100%; font-size: 1rem; }
        .form-control { width: 100%; background: var(--surface-accent); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.55rem 0.8rem; color: #fff; outline: none; margin-top: 0.3rem; }
        .cart-item { display: flex; justify-content: space-between; align-items: center; padding: 0.6rem 0; border-bottom: 1px solid var(--border); font-size: 0.85rem; }
    </style>
</head>
<body x-data="grosirPos()">

    <header>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="font-size: 1.2rem; font-weight: 800; color: var(--primary);">📦 POS Grosir & Distributor</div>
            <div style="font-size: 0.85rem; color: var(--text-muted);">Toko: {{ $tenant->name }}</div>
        </div>
        <div>
            <a href="{{ route('owner.dashboard') }}" style="color: var(--text-muted); text-decoration: none; font-size: 0.85rem;">← Kembali ke Dasbor</a>
        </div>
    </header>

    <div class="pos-container">
        <!-- Kolom Kiri: Katalog Barang Grosir -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 style="font-size: 1.2rem; font-weight: 700;">Katalog Barang Gudang</h2>
                <a href="{{ route('owner.grosir.tiers.index') }}" style="color: var(--primary); font-size: 0.85rem; text-decoration: none;">🏷️ Atur Tier Harga →</a>
            </div>

            <div class="prod-grid">
                @foreach($products as $prod)
                    <div class="prod-card" @click="addToCart({{ $prod->id }}, '{{ addslashes($prod->name) }}', {{ $prod->price }}, {{ $prod->stock }})">
                        <div style="font-size: 0.7rem; color: var(--primary); font-weight: 700; text-transform: uppercase;">{{ $prod->category ?? 'Item' }}</div>
                        <h4 style="font-size: 0.95rem; margin: 0.3rem 0; color: #fff;">{{ $prod->name }}</h4>
                        <div style="font-size: 0.85rem; font-weight: 800; color: #fff;">Rp {{ number_format($prod->price, 0, ',', '.') }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem;">Stok: {{ $prod->stock }} pcs</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Kolom Kanan: Kasir Keranjang & Checkout -->
        <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h3 style="font-size: 1.1rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
                    🛒 Faktur Pembelian Grosir
                </h3>

                <!-- Pilih Pelanggan (Wajib jika tempo) -->
                <div style="margin-bottom: 1rem;">
                    <label style="font-size: 0.8rem; color: var(--text-muted);">Pilih Pelanggan / Toko Mitra</label>
                    <select class="form-control" x-model="customerId">
                        <option value="">-- Pelanggan Umum (Tanpa Akun) --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} (Utang: Rp {{ number_format($c->current_debt, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Daftar Item Keranjang -->
                <div style="max-height: 280px; overflow-y: auto; margin-bottom: 1rem;">
                    <template x-if="cart.length === 0">
                        <div style="text-align: center; padding: 2rem 0; color: var(--text-muted); font-size: 0.85rem;">
                            Keranjang grosir masih kosong. Klik barang di sebelah kiri.
                        </div>
                    </template>

                    <template x-for="(item, idx) in cart" :key="item.id">
                        <div class="cart-item">
                            <div style="flex-grow: 1;">
                                <div style="font-weight: 700; color: #fff;" x-text="item.name"></div>
                                <div style="display: flex; gap: 0.5rem; align-items: center; margin-top: 0.2rem;">
                                    <input type="number" class="form-control" style="width: 70px; padding: 0.2rem 0.4rem; font-size: 0.8rem;" x-model.number="item.qty" min="1" @change="recalculatePrices()">
                                    <span style="font-size: 0.75rem; color: var(--text-muted);" x-text="'@ Rp ' + item.unitPrice.toLocaleString('id-ID')"></span>
                                </div>
                            </div>
                            <div style="text-align: right; margin-left: 0.5rem;">
                                <div style="font-weight: 700; color: var(--primary);" x-text="'Rp ' + (item.qty * item.unitPrice).toLocaleString('id-ID')"></div>
                                <button type="button" @click="removeItem(idx)" style="background: transparent; border: none; color: var(--danger); font-size: 0.75rem; cursor: pointer; margin-top: 0.2rem;">Hapus</button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Pilihan Pembayaran -->
                <div style="margin-bottom: 1rem;">
                    <label style="font-size: 0.8rem; color: var(--text-muted);">Metode Pembayaran</label>
                    <select class="form-control" x-model="paymentMethod">
                        <option value="cash">Tunai (Kas Toko)</option>
                        <option value="bank_transfer">Transfer Bank</option>
                        <option value="debt">Tempo / Bon (Masuk Buku Piutang)</option>
                    </select>
                </div>

                <div x-show="paymentMethod === 'debt'" style="margin-bottom: 1rem;">
                    <label style="font-size: 0.8rem; color: var(--danger);">Batas Jatuh Tempo Bon Pelanggan</label>
                    <input type="date" class="form-control" x-model="dueDate">
                </div>
            </div>

            <!-- Total & Tombol Proses -->
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 800; border-top: 1px solid var(--border); padding-top: 1rem; margin-bottom: 1rem;">
                    <span>Total Faktur:</span>
                    <span style="color: var(--primary);" x-text="'Rp ' + grandTotal().toLocaleString('id-ID')"></span>
                </div>
                <button type="button" class="btn-primary" @click="processCheckout()" :disabled="cart.length === 0">
                    Selesaikan Transaksi Grosir
                </button>
            </div>
        </div>
    </div>

    <script>
        function grosirPos() {
            return {
                cart: [],
                customerId: '',
                paymentMethod: 'cash',
                dueDate: '{{ date('Y-m-d', strtotime('+30 days')) }}',
                tiersData: @json($tiers),

                addToCart(id, name, basePrice, stock) {
                    let existing = this.cart.find(item => item.id === id);
                    if (existing) {
                        existing.qty++;
                    } else {
                        this.cart.push({ id, name, basePrice, stock, qty: 1, unitPrice: basePrice });
                    }
                    this.recalculatePrices();
                },

                removeItem(idx) {
                    this.cart.splice(idx, 1);
                },

                recalculatePrices() {
                    this.cart.forEach(item => {
                        let tiers = this.tiersData[item.id] || [];
                        let matched = null;
                        tiers.forEach(t => {
                            if (item.qty >= t.min_quantity) {
                                if (!matched || t.min_quantity > matched.min_quantity) {
                                    matched = t;
                                }
                            }
                        });
                        item.unitPrice = matched ? matched.price_per_unit : item.basePrice;
                    });
                },

                grandTotal() {
                    return this.cart.reduce((acc, cur) => acc + (cur.qty * cur.unitPrice), 0);
                },

                async processCheckout() {
                    if (this.cart.length === 0) return;
                    if (this.paymentMethod === 'debt' && !this.customerId) {
                        alert('Pilih pelanggan terlebih dahulu untuk transaksi tempo / bon!');
                        return;
                    }

                    const payload = {
                        items: this.cart.map(c => ({ product_id: c.id, quantity: c.qty })),
                        payment_method: this.paymentMethod,
                        customer_id: this.customerId || null,
                        due_date: this.paymentMethod === 'debt' ? this.dueDate : null,
                        _token: '{{ csrf_token() }}'
                    };

                    try {
                        const res = await fetch('{{ route('grosir.pos.checkout', ['alias' => $alias]) }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if (data.success) {
                            alert('✅ Faktur Grosir ' + data.order_number + ' Berhasil Diproses!');
                            this.cart = [];
                        } else {
                            alert('⚠️ Gagal: ' + data.message);
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan jaringan.');
                    }
                }
            }
        }
    </script>

</body>
</html>
