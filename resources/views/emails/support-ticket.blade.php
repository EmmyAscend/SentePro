<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Support ticket update</title>
</head>
<body style="font-family: sans-serif; background:#f8fafc; padding: 24px; color:#0f172a;">
    <div style="max-width: 480px; margin: 0 auto; background:#ffffff; border-radius: 16px; padding: 32px; border: 1px solid #e2e8f0;">
        <p style="text-transform: uppercase; letter-spacing: 0.2em; font-size: 12px; color:#059669; margin: 0 0 8px;">SentePro Support</p>
        <h1 style="font-size: 20px; margin: 0 0 16px;">{{ $ticket->subject }}</h1>

        @if ($event === 'opened')
            <p style="margin: 0 0 4px; color:#475569;">A new support ticket has been opened by {{ $ticket->business->business_name }}.</p>
        @else
            <p style="margin: 0 0 4px; color:#475569;">There's a new reply on this support ticket.</p>
        @endif

        <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color:#64748b;">Message</td>
                <td style="padding: 8px 0; text-align: right;">{{ $ticketMessage->body }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
