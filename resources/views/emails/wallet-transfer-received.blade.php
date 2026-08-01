<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Wallet transfer received</title>
</head>
<body style="font-family: sans-serif; background:#f8fafc; padding: 24px; color:#0f172a;">
    <div style="max-width: 480px; margin: 0 auto; background:#ffffff; border-radius: 16px; padding: 32px; border: 1px solid #e2e8f0;">
        <p style="text-transform: uppercase; letter-spacing: 0.2em; font-size: 12px; color:#059669; margin: 0 0 8px;">SentePro Wallet</p>
        <h1 style="font-size: 20px; margin: 0 0 16px;">{{ $walletTransfer->recipientBusiness->business_name }}</h1>

        <p style="margin: 0 0 4px; color:#475569;">You've received a wallet transfer from {{ $walletTransfer->senderBusiness->business_name }}.</p>

        <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color:#64748b;">Reference</td>
                <td style="padding: 8px 0; text-align: right; font-weight: 600;">{{ $walletTransfer->reference }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color:#64748b;">Amount</td>
                <td style="padding: 8px 0; text-align: right; font-weight: 600;">{{ number_format($walletTransfer->amount, 2) }}</td>
            </tr>
            @if ($walletTransfer->note)
                <tr>
                    <td style="padding: 8px 0; color:#64748b;">Note</td>
                    <td style="padding: 8px 0; text-align: right;">{{ $walletTransfer->note }}</td>
                </tr>
            @endif
        </table>
    </div>
</body>
</html>
