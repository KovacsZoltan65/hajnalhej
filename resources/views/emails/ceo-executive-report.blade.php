@php
    $summary = $dashboard['summary'] ?? [];
    $insights = $dashboard['kpi_insights'] ?? [];
    $security = $dashboard['security_alerts'] ?? [];
    $audit = $dashboard['audit_highlights'] ?? [];

    $huf = static fn ($value) => number_format((float) $value, 0, ',', ' ') . ' Ft';
    $pct = static fn ($value) => number_format((float) $value, 2, ',', ' ') . '%';
    $signedPct = static fn ($value) => (($value ?? 0) >= 0 ? '+' : '') . number_format((float) $value, 2, ',', ' ') . '%';
@endphp
<!doctype html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Hajnalhej CEO riport</title>
</head>
<body style="font-family: Arial, sans-serif; color: #2b1f18; line-height: 1.5;">
    <h1 style="margin-bottom: 4px;">Hajnalhej - CEO executive report</h1>
    <p style="margin-top: 0; color: #6a4a3a;">
        Idoszak: {{ (int) ($dashboard['period_days'] ?? 30) }} nap · Datum: {{ now()->format('Y.m.d H:i') }}
    </p>

    <h2>Fo KPI-k</h2>
    <table cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; border: 1px solid #d3c4b6;">
        <thead>
            <tr style="background: #f6efe8;">
                <th align="left">Mutato</th>
                <th align="right">Ertek</th>
                <th align="right">WoW</th>
                <th align="right">MoM</th>
                <th align="left">Allapot</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Bevetel</td>
                <td align="right">{{ $huf($summary['revenue'] ?? 0) }}</td>
                <td align="right">{{ $signedPct(data_get($insights, 'revenue.wow.percent', 0)) }}</td>
                <td align="right">{{ $signedPct(data_get($insights, 'revenue.mom.percent', 0)) }}</td>
                <td>{{ strtoupper((string) data_get($insights, 'revenue.rag', 'amber')) }}</td>
            </tr>
            <tr>
                <td>Becsult profit</td>
                <td align="right">{{ $huf($summary['estimated_profit'] ?? 0) }}</td>
                <td align="right">{{ $signedPct(data_get($insights, 'estimated_profit.wow.percent', 0)) }}</td>
                <td align="right">{{ $signedPct(data_get($insights, 'estimated_profit.mom.percent', 0)) }}</td>
                <td>{{ strtoupper((string) data_get($insights, 'estimated_profit.rag', 'amber')) }}</td>
            </tr>
            <tr>
                <td>Checkout konverzio</td>
                <td align="right">{{ $pct($summary['checkout_conversion_rate'] ?? 0) }}</td>
                <td align="right">{{ $signedPct(data_get($insights, 'checkout_conversion_rate.wow.percent', 0)) }}</td>
                <td align="right">{{ $signedPct(data_get($insights, 'checkout_conversion_rate.mom.percent', 0)) }}</td>
                <td>{{ strtoupper((string) data_get($insights, 'checkout_conversion_rate.rag', 'amber')) }}</td>
            </tr>
            <tr>
                <td>Visszatero vasarloi arany</td>
                <td align="right">{{ $pct($summary['repeat_customer_rate'] ?? 0) }}</td>
                <td align="right">{{ $signedPct(data_get($insights, 'repeat_customer_rate.wow.percent', 0)) }}</td>
                <td align="right">{{ $signedPct(data_get($insights, 'repeat_customer_rate.mom.percent', 0)) }}</td>
                <td>{{ strtoupper((string) data_get($insights, 'repeat_customer_rate.rag', 'amber')) }}</td>
            </tr>
            <tr>
                <td>LTV</td>
                <td align="right">{{ $huf($summary['ltv'] ?? 0) }}</td>
                <td align="right">{{ $signedPct(data_get($insights, 'ltv.wow.percent', 0)) }}</td>
                <td align="right">{{ $signedPct(data_get($insights, 'ltv.mom.percent', 0)) }}</td>
                <td>{{ strtoupper((string) data_get($insights, 'ltv.rag', 'amber')) }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Biztonsagi jelzesek</h2>
    <ul>
        <li>Kritikus riasztasok: {{ (int) ($security['critical_alerts'] ?? 0) }}</li>
        <li>Arva jogosultsagok: {{ (int) ($security['orphan_permissions'] ?? 0) }}</li>
        <li>Veszelyes jogosultsagok: {{ (int) ($security['dangerous_permissions'] ?? 0) }}</li>
        <li>Magas kockazatu felhasznalok: {{ (int) ($security['high_risk_users'] ?? 0) }}</li>
    </ul>

    <h2>Audit highlights</h2>
    @if (count($audit) === 0)
        <p>Nincs kritikus audit esemeny a valasztott idoszakban.</p>
    @else
        <ul>
            @foreach ($audit as $item)
                <li>
                    <strong>{{ $item['label'] ?? 'Audit esemeny' }}</strong>
                    ({{ $item['log_name'] ?? 'n/a' }} - {{ $item['severity'] ?? 'info' }})<br>
                    <span>{{ $item['summary'] ?? '' }}</span>
                </li>
            @endforeach
        </ul>
    @endif

    <p style="margin-top: 24px; color: #6a4a3a;">
        Hajnalhej Bakery - automatizalt executive report
    </p>
</body>
</html>
