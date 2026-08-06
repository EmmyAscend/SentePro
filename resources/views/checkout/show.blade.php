<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $paymentLink->title }} | SentePro</title>
    <link href="https://fonts.bunny.net/css?family=syne:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-slate-950 text-white">
    <div class="mx-auto flex min-h-screen max-w-4xl items-center justify-center px-4 py-12">
        <div class="w-full rounded-3xl bg-slate-900 p-8 shadow-2xl ring-1 ring-white/10">
            <div class="mb-6">
                <p class="text-sm uppercase tracking-[0.3em] text-lime-300">SentePro Checkout</p>
                <h1 class="mt-3 text-3xl font-bold text-white">{{ $paymentLink->title }}</h1>
                <p class="mt-2 text-slate-300">{{ $paymentLink->description }}</p>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl bg-slate-800 p-5 ring-1 ring-white/10">
                    <p class="text-sm text-slate-400">Business</p>
                    <p class="mt-2 text-xl font-semibold text-white">{{ $paymentLink->business->business_name }}</p>
                    <p class="mt-4 text-sm text-slate-300">Payment type: {{ ucfirst($paymentLink->type) }}</p>
                    <p class="mt-2 text-sm text-slate-300">Amount: {{ number_format($paymentLink->amount, 2) }}</p>
                    <p class="mt-2 text-sm text-slate-300">Expiry: {{ $paymentLink->expiry_date?->format('d M Y') }}</p>
                </div>

                <form method="POST" action="{{ route('checkout.store', $paymentLink) }}" class="rounded-2xl bg-lime-400 p-5 text-slate-950">
                    @csrf
                    <p class="text-sm font-semibold">Ready to pay</p>
                    <p class="mt-3 text-3xl font-extrabold">{{ number_format($paymentLink->amount, 2) }}</p>

                    @if ($errors->any())
                        <div class="mt-4 rounded-lg bg-rose-100 px-3 py-2 text-xs text-rose-700">
                            <ul class="list-inside list-disc space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mt-4 space-y-3 text-left text-sm">
                        <label class="block">
                            <span class="font-medium">Customer name</span>
                            <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="mt-1 w-full rounded-lg border-0 px-3 py-2 text-slate-900" required>
                        </label>
                        <label class="block">
                            <span class="font-medium">Customer email</span>
                            <input type="email" name="customer_email" value="{{ old('customer_email') }}" class="mt-1 w-full rounded-lg border-0 px-3 py-2 text-slate-900" required>
                        </label>
                        <label class="block">
                            <span class="font-medium">Phone number (for mobile money)</span>
                            <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="256712345678" class="mt-1 w-full rounded-lg border-0 px-3 py-2 text-slate-900">
                        </label>
                        @foreach ($paymentLink->fields ?? [] as $field)
                            <label class="block">
                                <span class="font-medium">{{ $field['label'] }}</span>
                                <input type="text" name="custom_fields[{{ $field['key'] }}]" value="{{ old('custom_fields.'.$field['key']) }}" class="mt-1 w-full rounded-lg border-0 px-3 py-2 text-slate-900">
                            </label>
                        @endforeach
                        <label class="block">
                            <span class="font-medium">Payment method</span>
                            @php
                                $cardAvailable = $cardProvider->status === 'active';
                                $mobileMoneyAvailable = $mobileMoneyProvider->status === 'active';
                            @endphp
                            <select name="payment_method" class="mt-1 w-full rounded-lg border-0 px-3 py-2 text-slate-900" required @disabled(! $cardAvailable && ! $mobileMoneyAvailable)>
                                @if (! $cardAvailable && ! $mobileMoneyAvailable)
                                    <option value="" disabled selected>No payment methods available</option>
                                @endif
                                @if ($cardAvailable)
                                    <option value="card">Debit or Credit Card</option>
                                @endif
                                @if ($mobileMoneyAvailable)
                                    <option value="mobile_money">Mobile Money (MTN / Airtel)</option>
                                @endif
                            </select>
                        </label>
                        <label class="block">
                            <span class="font-medium">Currency</span>
                            <select name="currency" class="mt-1 w-full rounded-lg border-0 px-3 py-2 text-slate-900" required>
                                <option value="UGX">UGX</option>
                                <option value="KES">KES</option>
                            </select>
                        </label>
                    </div>

                    <button type="submit" class="mt-5 w-full rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white" @disabled(! $cardAvailable && ! $mobileMoneyAvailable)>Proceed to payment</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
