<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dispute update</title>
</head>
<body style="font-family: sans-serif; background:#f8fafc; padding: 24px; color:#0f172a;">
    <div style="max-width: 480px; margin: 0 auto; background:#ffffff; border-radius: 0; padding: 32px; border: 1px solid #e2e8f0;">
        <p style="text-transform: uppercase; letter-spacing: 0.2em; font-size: 12px; color:#059669; margin: 0 0 8px;">SentePro Dispute</p>
        <h1 style="font-size: 20px; margin: 0 0 16px;">{{ $dispute->reason }}</h1>

        @if ($event === 'opened')
            <p style="margin: 0 0 4px; color:#475569;">{{ $dispute->business->business_name }} has raised a dispute against a transaction.</p>
        @elseif ($event === 'resolved')
            <p style="margin: 0 0 4px; color:#475569;">Your dispute has been resolved.</p>
        @else
            <p style="margin: 0 0 4px; color:#475569;">Your dispute has been rejected.</p>
        @endif

        <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
            @if ($dispute->description)
                <tr>
                    <td style="padding: 8px 0; color:#64748b;">Description</td>
                    <td style="padding: 8px 0; text-align: right;">{{ $dispute->description }}</td>
                </tr>
            @endif
            @if ($dispute->resolution_notes)
                <tr>
                    <td style="padding: 8px 0; color:#64748b;">Notes</td>
                    <td style="padding: 8px 0; text-align: right;">{{ $dispute->resolution_notes }}</td>
                </tr>
            @endif
        </table>
    </div>
</body>
</html>
