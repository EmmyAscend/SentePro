<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Business review update</title>
</head>
<body style="font-family: sans-serif; background:#f8fafc; padding: 24px; color:#0f172a;">
    <div style="max-width: 480px; margin: 0 auto; background:#ffffff; border-radius: 16px; padding: 32px; border: 1px solid #e2e8f0;">
        <p style="text-transform: uppercase; letter-spacing: 0.2em; font-size: 12px; color:#059669; margin: 0 0 8px;">SentePro</p>
        <h1 style="font-size: 20px; margin: 0 0 16px;">{{ $business->business_name }}</h1>

        @if ($status === 'approved')
            <p style="margin: 0 0 4px; color:#475569;">Good news — your business has been approved. You can now accept payments on SentePro.</p>
        @elseif ($status === 'suspended')
            <p style="margin: 0 0 4px; color:#475569;">Your business account has been suspended. Please contact support for more information.</p>
        @else
            <p style="margin: 0 0 4px; color:#475569;">Your business application was not approved.</p>
        @endif

        @if ($business->review_notes)
            <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color:#64748b;">Notes</td>
                    <td style="padding: 8px 0; text-align: right;">{{ $business->review_notes }}</td>
                </tr>
            </table>
        @endif
    </div>
</body>
</html>
