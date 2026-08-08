<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #10b981; }
        .header h1 { font-size: 24px; color: #10b981; margin-bottom: 5px; }
        .header p { color: #666; font-size: 11px; }
        .section { margin-bottom: 20px; }
        .section h2 { font-size: 14px; color: #10b981; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; font-weight: bold; color: #333; }
        tr:hover { background-color: #f8f9fa; }
        .kpi-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
        .kpi-box { flex: 1; min-width: 150px; background: #f8f9fa; padding: 10px; border-radius: 5px; border-left: 3px solid #10b981; }
        .kpi-box .label { font-size: 10px; color: #666; }
        .kpi-box .value { font-size: 16px; font-weight: bold; color: #333; }
        .footer { text-align: center; margin-top: 20px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 10px; color: #666; }
        @page { margin: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Period: {{ $period }}</p>
        <p>Generated: {{ $generated_at }}</p>
    </div>

    <div class="section">
        <h2>Key Performance Indicators</h2>
        <div class="kpi-grid">
            <div class="kpi-box">
                <div class="label">Total Revenue</div>
                <div class="value">Rp {{ number_format($kpi['total_revenue'], 0, ',', '.') }}</div>
            </div>
            <div class="kpi-box">
                <div class="label">Total Orders</div>
                <div class="value">{{ number_format($kpi['total_orders']) }}</div>
            </div>
            <div class="kpi-box">
                <div class="label">Completed Orders</div>
                <div class="value">{{ number_format($kpi['completed_orders']) }}</div>
            </div>
            <div class="kpi-box">
                <div class="label">Pending Orders</div>
                <div class="value">{{ number_format($kpi['pending_orders']) }}</div>
            </div>
            <div class="kpi-box">
                <div class="label">Active Customers</div>
                <div class="value">{{ number_format($kpi['active_customers']) }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Top Selling Products</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Quantity Sold</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topProducts as $index => $product)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $product['name'] }}</td>
                    <td>{{ $product['category'] }}</td>
                    <td>{{ number_format($product['quantity']) }}</td>
                    <td>Rp {{ number_format($product['revenue'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Financial Summary</h2>
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Gross Revenue</td>
                    <td>Rp {{ number_format($financialSummary['gross_revenue'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Discount Total</td>
                    <td>Rp {{ number_format($financialSummary['discount_total'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Promotion Cost</td>
                    <td>Rp {{ number_format($financialSummary['promotion_cost'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Shipping Revenue</td>
                    <td>Rp {{ number_format($financialSummary['shipping_revenue'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Net Revenue</strong></td>
                    <td><strong>Rp {{ number_format($financialSummary['net_revenue'], 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>AbodaFarm Analytics System - {{ now()->format('d M Y H:i:s') }}</p>
    </div>
</body>
</html>
