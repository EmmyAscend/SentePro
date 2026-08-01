<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settlement update</title>
</head>
<body style="font-family: sans-serif; background:#f8fafc; padding: 24px; color:#0f172a;">
    <div style="max-width: 480px; margin: 0 auto; background:#ffffff; border-radius: 16px; padding: 32px; border: 1px solid #e2e8f0;">
        <p style="text-transform: uppercase; letter-spacing: 0.2em; font-size: 12px; color:#059669; margin: 0 0 8px;">SentePro Settlement</p>
        <h1 style="font-size: 20px; margin: 0 0 16px;">{{ $settlement->business->business_name }}</h1>

        @if ($event === 'completed')
            <p style="margin: 0 0 4px; color:#475569;">Your settlement has been completed and the net amount has been credited to your settlement balance.</p>
        @elseif ($event === 'rejected')
            <p style="margin: 0 0 4px; color:#475569;">Your settlement request was rejected and the reserved amount has been returned to your available balance.</p>
        @elseif ($event === 'reversed')
            <p style="margin: 0 0 4px; color:#475569;">Your settlement has been reversed and the amount has been returned to your available balance.</p>
        @else
            <p style="margin: 0 0 4px; color:#475569;">Your settlement request is being retried.</p>
        @endif

        <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color:#64748b;">Amount</td>
                <td style="padding: 8px 0; text-align: right; font-weight: 600;">{{ number_format($settlement->amount, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color:#64748b;">Net Amount</td>
                <td style="padding: 8px 0; text-align: right;">{{ number_format($settlement->net_amount, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color:#64748b;">Status</td>
                <td style="padding: 8px 0; text-align: right;">{{ $settlement->status->label() }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
