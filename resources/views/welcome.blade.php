<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SentePro</title>
    </head>
    <body>
        <main>
            <h1>{{ $headline }}</h1>
            <p>Collect payments online with a secure, modern platform built for businesses in East Africa.</p>
            <a href="{{ route('business.register') }}">Start Business Registration</a>
        </main>
    </body>
</html>
