<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Faktur Kulakan / Pengadaan - Unimart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --primary: hsl(28, 90%, 50%);
            --primary-light: hsl(28, 95%, 62%);
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
        header.dashboard-header { background: rgba(15, 23, 30, 0.85); backdrop-filter: blur(12px); border-bottom: 1px solid var(--border); padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 20; }
        .logo { font-family: 'Playfair Display', serif; font-size: 1.3rem; font-weight: 800; color: var(--primary); text-decoration: none; }
        .logo span { font-family: 'Outfit', sans-serif; font-size: 0.75rem; font-weight: 700; background: var(--primary); color: var(--background); padding: 0.1rem 0.4rem; border-radius: 20px; margin-left: 0.2rem; }
        main { padding: 1.5rem; max-width: 1000px; width: 100%; margin: 0 auto; flex-grow: 1; }
        
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 1.5rem; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--text-muted); }
        .form-control { width: 100%; background: var(--surface-accent); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.65rem 0.85rem; color: #fff; font-size: 0.9rem; outline: none; }
        .form-control:focus { border-color: var(--primary); }
        
        .items-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .items-table th { background: var(--surface-accent); padding: 0.75rem 1rem; font-size: 0.8rem; text-align: left; color: var(--text-muted); }
        .items-table td { padding: 0.75rem 1rem; border-top: 1px solid var(--border); }
        
        .btn-primary { background: var(--primary); color: #000; font-weight: 700; border: none; padding: 0.75rem 1.5rem; border-radius: var(--radius-sm); cursor: pointer; transition: var(--transition); display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none; font-size: 0.95rem; }
        .btn-primary:hover { background: var(--primary-light); transform: translateY(-2px); }
        .btn-secondary { background: var(--surface-accent); border: 1px solid var(--border); color: #fff; padding: 0.4rem 0.8rem; border-radius: 6px; cursor: pointer; font-size: 0.85rem; }
        .btn-danger { background: rgba(239, 68, 68, 0.2); border: 1px solid var(--danger); color: var(--danger); padding: 0.3rem 0.6rem; border-radius: 6px; cursor: pointer; font-size: 0.8rem; }
    </style>
</head>
<body x-data="purchaseForm()">

    <header class="dashboard-header">
        <a href="/" class="logo">Unimart<span>restock</span></a>
        <div style="font-size: 0.85rem; color: var(--text-muted);">Toko: <strong style="color: #fff;">{{ $tenant->name }}</strong></div>
    </header>

    <main>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div>
                <h1 style="font-size: 1.6rem; font-weight: 700;">📦 Input Faktur Pengadaan Stok</h1>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.2rem;">Stok produk bertambah otomatis dan HPP dihitung ulang dengan Moving Average.</p>
            </div>
            <a href="{{ route('owner.purchases.index') }}" class="btn-secondary">← Kembali ke Riwayat</a>
        </div>

        @if(session('error'))
            <div style="padding: 0.8rem 1rem; background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); border-radius: var(--radius-sm); color: var(--danger); margin-bottom: 1.2rem; font-size: 0.9rem;">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('owner.purchases.store') }}" method="POST">
            @csrf
            <div class="card">
                <h3 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--primary);">1. Informasi Pemasok & Transaksi</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Pilih Pemasok (Supplier) *</label>
                        <select name="supplier_id" class="form-control" required>
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }} (Utang: Rp {{ number_format($supplier->current_debt, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nomor Nota / Surat Jalan Supplier</label>
                        <input type="text" name="supplier_invoice_no" class="form-control" placeholder="Contoh: INV/2026/09/8812">
                    </div>
                    <div class="form-group">
                        <label>Tanggal Pembelian *</label>
                        <input type="date" name="purchase_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Metode Pembayaran *</label>
                        <select name="payment_method" class="form-control" x-model="paymentMethod" required>
                            <option value="cash">Tunai (Kas Toko Keluar Langsung)</option>
                            <option value="bank_transfer">Transfer Bank (Non-Tunai Toko)</option>
                            <option value="debt">Tempo / Utang (Masuk Buku Utang Toko)</option>
                        </select>
                    </div>
                    <div class="form-group" x-show="paymentMethod === 'debt'">
                        <label style="color: var(--danger);">Batas Jatuh Tempo Utang *</label>
                        <input type="date" name="due_date" class="form-control" :required="paymentMethod === 'debt'" value="{{ date('Y-m-d', strtotime('+14 days')) }}">
                    </div>
                    <div class="form-group">
                        <label>Catatan Tambahan</label>
                        <input type="text" name="notes" class="form-control" placeholder="Misal: Stok untuk promo weekend">
                    </div>
                </div>
            </div>

            <!-- Rincian Produk yang Dibeli -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="font-size: 1.1rem; color: var(--primary);">2. Rincian Barang Kulakan (Restock)</h3>
                    <button type="button" @click="addItem()" class="btn-secondary">+ Tambah Baris Item</button>
                </div>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Produk</th>
                            <th style="width: 15%;">Jumlah (Qty)</th>
                            <th style="width: 20%;">Harga Beli Modal (Satuan)</th>
                            <th style="width: 20%;">Subtotal</th>
                            <th style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td>
                                    <select :name="'items[' + index + '][product_id]'" class="form-control" required x-model="item.product_id">
                                        <option value="">-- Pilih Produk --</option>
                                        @foreach($products as $prod)
                                            <option value="{{ $prod->id }}">{{ $prod->name }} (Stok saat ini: {{ $prod->stock }}, HPP: Rp {{ number_format($prod->cost_price, 0, ',', '.') }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" :name="'items[' + index + '][quantity]'" class="form-control" min="1" x-model.number="item.quantity" required>
                                </td>
                                <td>
                                    <input type="number" :name="'items[' + index + '][buy_price]'" class="form-control" min="0" step="500" x-model.number="item.buy_price" required>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: #fff;" x-text="'Rp ' + (item.quantity * item.buy_price).toLocaleString('id-ID')"></div>
                                </td>
                                <td>
                                    <button type="button" @click="removeItem(index)" class="btn-danger" x-show="items.length > 1">✕</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <div style="display: flex; justify-content: flex-end; align-items: center; gap: 2rem; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border);">
                    <div style="text-align: right;">
                        <div style="font-size: 0.85rem; color: var(--text-muted);">Total Biaya Pengadaan:</div>
                        <div style="font-size: 1.8rem; font-weight: 800; color: var(--primary);" x-text="'Rp ' + grandTotal().toLocaleString('id-ID')"></div>
                    </div>
                    <button type="submit" class="btn-primary">
                        💾 Simpan & Update Stok
                    </button>
                </div>
            </div>
        </form>
    </main>

    <script>
        function purchaseForm() {
            return {
                paymentMethod: 'cash',
                items: [
                    { product_id: '', quantity: 1, buy_price: 10000 }
                ],
                addItem() {
                    this.items.push({ product_id: '', quantity: 1, buy_price: 10000 });
                },
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },
                grandTotal() {
                    return this.items.reduce((acc, cur) => acc + (cur.quantity * cur.buy_price || 0), 0);
                }
            }
        }
    </script>
</body>
</html>
