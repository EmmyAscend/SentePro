<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} | SentePro</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=pacifico:400&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-white">
    <div class="min-h-screen px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl">
            <div class="mb-8 flex items-center justify-between gap-4">
                <a href="/">
                    <x-brand-mark class="text-2xl" />
                </a>
                <a href="/" class="rounded-full border border-white/15 px-4 py-2 text-sm font-semibold hover:bg-white/5">Back to home</a>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/40 sm:p-8">
                <h1 class="text-3xl font-bold">{{ $page->title }}</h1>
                <p class="mt-1 text-sm text-slate-400">Last updated {{ $page->updated_at->format('F j, Y') }}</p>
                <div class="mt-6 whitespace-pre-line text-sm leading-relaxed text-slate-300">{{ $page->body }}</div>
            </div>
        </div>
    </div>
</body>
</html>
