<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment received</title>
</head>
<body style="font-family: sans-serif; background:#f8fafc; padding: 24px; color:#0f172a;">
    <div style="max-width: 480px; margin: 0 auto; background:#ffffff; border-radius: 0; padding: 32px; border: 1px solid #e2e8f0;">
        <p style="text-transform: uppercase; letter-spacing: 0.2em; font-size: 12px; color:#059669; margin: 0 0 8px;">SentePro Payment</p>
        <h1 style="font-size: 20px; margin: 0 0 16px;">{{ $receipt->business->business_name }}</h1>

        <p style="margin: 0 0 4px; color:#475569;">You've received a new payment{{ $receipt->customer_name ? ' from '.$receipt->customer_name : '' }}.</p>

        <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color:#64748b;">Reference</td>
                <td style="padding: 8px 0; text-align: right; font-weight: 600;">{{ $receipt->reference_number }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color:#64748b;">Amount Paid</td>
                <td style="padding: 8px 0; text-align: right; font-weight: 600;">{{ $receipt->currency }} {{ number_format($receipt->amount, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color:#64748b;">Net Credited</td>
                <td style="padding: 8px 0; text-align: right;">{{ $receipt->currency }} {{ number_format($receipt->net_amount, 2) }}</td>
            </tr>
        </table>

        <a href="{{ route('receipts.show', $receipt) }}" style="display: inline-block; margin-top: 24px; background:#0f172a; color:#ffffff; text-decoration: none; padding: 12px 20px; border-radius: 0; font-size: 14px; font-weight: 600;">
            View receipt
        </a>
    </div>
</body>
</html>
