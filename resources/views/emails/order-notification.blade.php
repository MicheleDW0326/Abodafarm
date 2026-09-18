<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #10b981;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #10b981;
        }
        .logo span {
            color: #059669;
        }
        .notification-icon {
            font-size: 48px;
            text-align: center;
            margin: 20px 0;
        }
        .notification-title {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
        }
        .notification-message {
            font-size: 16px;
            text-align: center;
            margin-bottom: 25px;
            color: #555;
        }
        .order-details {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #6b7280;
            font-size: 14px;
        }
        .detail-value {
            font-weight: 600;
            color: #374151;
        }
        .cta-button {
            display: inline-block;
            background-color: #10b981;
            color: white;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            margin: 10px 0;
        }
        .cta-button:hover {
            background-color: #059669;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 12px;
        }
        .social-links {
            margin: 15px 0;
        }
        .social-links a {
            color: #10b981;
            text-decoration: none;
            margin: 0 10px;
        }
        /* Type-specific colors */
        .type-success {
            color: #10b981;
        }
        .type-warning {
            color: #f59e0b;
        }
        .type-error {
            color: #ef4444;
        }
        .type-info {
            color: #3b82f6;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">Aboda<span>Farm</span></div>
        </div>

        <div class="notification-icon">
            @switch($type)
                @case('booking_approved')
                    &#9989;
                @case('booking_rejected')
                    &#10060;
                @case('payment_success')
                    &#9989;
                @case('payment_failed')
                    &#10060;
                @case('payment_refunded')
                    &#129045;
                @case('order_shipped')
                    &#128230;
                @case('order_delivered')
                    &#127873;
                @case('order_completed')
                    &#10024;
                @default
                    &#128276;
            @endswitch
        </div>

        <div class="notification-title {{ $type === 'payment_failed' || $type === 'booking_rejected' ? 'type-error' : 'type-success' }}">
            {{ $title }}
        </div>

        <div class="notification-message">
            Halo {{ $userName }},
            <br><br>
            {{ $bodyText }}
        </div>

        @if($orderNumber)
        <div class="order-details">
            <div class="detail-row">
                <span class="detail-label">Nomor Pesanan</span>
                <span class="detail-value">{{ $orderNumber }}</span>
            </div>
            @if(isset($data['amount']))
            <div class="detail-row">
                <span class="detail-label">Jumlah</span>
                <span class="detail-value">{{ $data['amount'] }}</span>
            </div>
            @endif
            @if(isset($data['item_count']))
            <div class="detail-row">
                <span class="detail-label">Jumlah Item</span>
                <span class="detail-value">{{ $data['item_count'] }} item</span>
            </div>
            @endif
        </div>
        @endif

        <div style="text-align: center;">
            @switch($type)
                @case('booking_approved')
                @case('payment_success')
                @case('order_delivered')
                @case('order_completed')
                    <a href="{{ config('app.url') }}/akun-saya/orders" class="cta-button">Lihat Pesanan</a>
                    @break
                @case('booking_rejected')
                @case('payment_failed')
                    <a href="{{ config('app.url') }}" class="cta-button">Kembali ke Beranda</a>
                    @break
                @case('order_shipped')
                    <a href="{{ config('app.url') }}/akun-saya/orders" class="cta-button">Lacak Pesanan</a>
                    @break
                @default
                    <a href="{{ config('app.url') }}/akun-saya/orders" class="cta-button">Lihat Detail</a>
            @endswitch
        </div>

        <div class="footer">
            <p>Terima kasih telah berbelanja di AbodaFarm!</p>
            <div class="social-links">
                <a href="#">Facebook</a> |
                <a href="#">Instagram</a> |
                <a href="#">WhatsApp</a>
            </div>
            <p>
                Anda menerima email ini karena telah melakukan transaksi di AbodaFarm.<br>
                AbodaFarm - Peternakan Modern Indonesia<br>
                {{ date('Y') }} AbodaFarm. Hak cipta dilindungi.
            </p>
        </div>
    </div>
</body>
</html>
