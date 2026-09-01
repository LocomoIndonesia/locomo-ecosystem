<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak QR Code - {{ $business->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background: white;
            color: #333;
        }
        .container {
            border: 2px solid #333;
            border-radius: 20px;
            padding: 40px;
            display: inline-block;
            max-width: 400px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0284c7;
            margin-bottom: 20px;
        }
        .qr-image {
            width: 250px;
            height: 250px;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 10px;
        }
        .biz-name {
            font-size: 22px;
            font-weight: bold;
            margin: 20px 0 10px 0;
        }
        .biz-type {
            font-size: 14px;
            text-transform: uppercase;
            color: #666;
            letter-spacing: 1px;
            margin-bottom: 30px;
        }
        .footer {
            font-size: 12px;
            color: #999;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 30px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; font-weight: bold; background: #0284c7; color: white; border: none; border-radius: 8px; cursor: pointer;">🖨️ Cetak QR Code</button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 16px; margin-left: 10px; background: #ef4444; color: white; border: none; border-radius: 8px; cursor: pointer;">Tutup Halaman</button>
    </div>

    <div class="container">
        <div class="logo">Mandeh.Online</div>
        @if($business->qr_code_path)
            <img class="qr-image" src="{{ asset('storage/' . $business->qr_code_path) }}" alt="QR Code">
        @else
            <div style="width: 250px; height: 250px; line-height: 250px; border: 1px dashed #ccc; margin: auto;">Belum Ada QR</div>
        @endif
        <div class="biz-name">{{ $business->name }}</div>
        <div class="biz-type">{{ $business->type }}</div>
        <div class="footer">Pindai QR untuk Check-in dan Ulasan Destinasi</div>
    </div>

    <script>
        // Trigger print automatically
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
