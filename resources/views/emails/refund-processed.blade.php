<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Refund processed</title>
</head>
<body style="font-family: sans-serif; background:#f8fafc; padding: 24px; color:#0f172a;">
    <div style="max-width: 480px; margin: 0 auto; background:#ffffff; border-radius: 16px; padding: 32px; border: 1px solid #e2e8f0;">
        <p style="text-transform: uppercase; letter-spacing: 0.2em; font-size: 12px; color:#059669; margin: 0 0 8px;">SentePro Refund</p>
        <h1 style="font-size: 20px; margin: 0 0 16px;">{{ $refund->business->business_name }}</h1>

        @if ($refund->paymentTransaction->status === \App\Enums\PaymentTransactionStatus::PartiallyRefunded)
            <p style="margin: 0 0 4px; color:#475569;">Part of your payment has been refunded.</p>
        @else
            <p style="margin: 0 0 4px; color:#475569;">Your payment has been refunded in full.</p>
        @endif

        <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color:#64748b;">Amount Refunded</td>
                <td style="padding: 8px 0; text-align: right; font-weight: 600;">{{ number_format($refund->amount, 2) }}</td>
            </tr>
            @if ($refund->reason)
                <tr>
                    <td style="padding: 8px 0; color:#64748b;">Reason</td>
                    <td style="padding: 8px 0; text-align: right;">{{ $refund->reason }}</td>
                </tr>
            @endif
        </table>
    </div>
</body>
</html>
