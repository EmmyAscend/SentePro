<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Registration | SentePro</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-white">
    <div class="min-h-screen px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/40 sm:p-8">
            <div class="mb-8 flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-300">Business onboarding</p>
                    <h1 class="mt-2 text-3xl font-bold">Business Registration</h1>
                </div>
                <a href="/" class="rounded-full border border-white/15 px-4 py-2 text-sm font-semibold hover:bg-white/5">Back to home</a>
            </div>

            @if (session('status'))
                <div class="mb-6 rounded-2xl border border-emerald-400/40 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-100">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-400/40 bg-rose-400/10 px-4 py-3 text-sm text-rose-100">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('business.register.store') }}" method="POST" class="grid gap-4 md:grid-cols-2">
                @csrf

                <div class="md:col-span-2">
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-300">Your account</p>
                </div>
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-medium text-slate-200">Your Name</span>
                    <input type="text" name="owner_name" value="{{ old('owner_name') }}" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-emerald-400" placeholder="Your Name" required>
                </label>
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-medium text-slate-200">Your Email</span>
                    <input type="email" name="owner_email" value="{{ old('owner_email') }}" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-emerald-400" placeholder="Your Email" required>
                </label>
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-medium text-slate-200">Password</span>
                    <input type="password" name="owner_password" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-emerald-400" placeholder="Password" required>
                </label>
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-medium text-slate-200">Confirm Password</span>
                    <input type="password" name="owner_password_confirmation" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-emerald-400" placeholder="Confirm Password" required>
                </label>

                <div class="md:col-span-2 mt-2">
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-300">Business details</p>
                </div>
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-medium text-slate-200">Business Name</span>
                    <input type="text" name="business_name" value="{{ old('business_name') }}" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-emerald-400" placeholder="Business Name" required>
                </label>
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-medium text-slate-200">Trading Name</span>
                    <input type="text" name="trading_name" value="{{ old('trading_name') }}" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-emerald-400" placeholder="Trading Name" required>
                </label>
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-medium text-slate-200">Registration Number</span>
                    <input type="text" name="registration_number" value="{{ old('registration_number') }}" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-emerald-400" placeholder="Registration Number" required>
                </label>
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-medium text-slate-200">Country</span>
                    <input type="text" name="country" value="{{ old('country') }}" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-emerald-400" placeholder="Country" required>
                </label>
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-medium text-slate-200">Phone</span>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-emerald-400" placeholder="Phone" required>
                </label>
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-medium text-slate-200">Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-emerald-400" placeholder="Email" required>
                </label>
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-medium text-slate-200">Industry</span>
                    <input type="text" name="industry" value="{{ old('industry') }}" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-emerald-400" placeholder="Industry" required>
                </label>
                <label class="flex flex-col gap-2">
                    <span class="text-sm font-medium text-slate-200">Expected Monthly Volume</span>
                    <input type="text" name="expected_monthly_volume" value="{{ old('expected_monthly_volume') }}" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-emerald-400" placeholder="Expected Monthly Volume" required>
                </label>
                <label class="flex flex-col gap-2 md:col-span-2">
                    <span class="text-sm font-medium text-slate-200">Business Description</span>
                    <textarea name="business_description" rows="4" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-emerald-400" placeholder="Tell us about your business and payment goals">{{ old('business_description') }}</textarea>
                </label>

                <div class="md:col-span-2">
                    <button type="submit" class="rounded-xl bg-emerald-400 px-6 py-3 font-semibold text-slate-950 hover:bg-emerald-300">Submit registration</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
