@php
    $selectedType = old('business_type', request()->query('type'));
    $selectedType = in_array($selectedType, ['individual', 'business', 'ngo'], true) ? $selectedType : '';
    $typeTitles = [
        'individual' => $content->register_individual_title,
        'business' => $content->register_business_title,
        'ngo' => $content->register_ngo_title,
    ];
    $initialTitle = $typeTitles[$selectedType] ?? 'Business';
@endphp

<x-public-layout title="{{ $initialTitle }} Registration | SentePro">
    <div class="px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/40 sm:p-8" x-data="{ type: '{{ $selectedType }}', typeTitles: { individual: @js($content->register_individual_title), business: @js($content->register_business_title), ngo: @js($content->register_ngo_title) } }">
            <div class="mb-8">
                <p class="text-sm uppercase tracking-[0.25em] text-lime-300" x-text="(type ? typeTitles[type] : 'Business') + ' onboarding'">{{ $initialTitle }} onboarding</p>
                <h1 class="mt-2 text-[clamp(1.25rem,4.5vw,2.25rem)] font-bold" x-text="(type ? typeTitles[type] : 'Business') + ' Registration'">{{ $initialTitle }} Registration</h1>
            </div>

            @if (session('status'))
                <div class="mb-6 rounded-2xl border border-lime-400/40 bg-lime-400/10 px-4 py-3 text-sm text-lime-100">
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

            <div x-show="! type" @if ($selectedType) style="display: none;" @endif>
                <p class="mb-4 text-sm font-medium text-slate-200">{{ $content->register_prompt_heading }}</p>
                <div class="grid gap-4 sm:grid-cols-3">
                    <button type="button" @click="type = 'individual'" class="group relative rounded-2xl border border-white/10 bg-slate-900 p-6 text-left hover:border-lime-400/40 hover:bg-lime-400/5">
                        <span class="absolute right-4 top-4 h-5 w-5 rounded-full border-2 border-white/20 group-hover:border-lime-400/60"></span>
                        <span class="block pr-8 font-semibold text-white">{{ $content->register_individual_title }}</span>
                        <p class="mt-1 pr-8 text-sm text-slate-400">{{ $content->register_individual_description }}</p>
                    </button>
                    <button type="button" @click="type = 'business'" class="group relative rounded-2xl border border-white/10 bg-slate-900 p-6 text-left hover:border-lime-400/40 hover:bg-lime-400/5">
                        <span class="absolute right-4 top-4 h-5 w-5 rounded-full border-2 border-white/20 group-hover:border-lime-400/60"></span>
                        <span class="block pr-8 font-semibold text-white">{{ $content->register_business_title }}</span>
                        <p class="mt-1 pr-8 text-sm text-slate-400">{{ $content->register_business_description }}</p>
                    </button>
                    <button type="button" @click="type = 'ngo'" class="group relative rounded-2xl border border-white/10 bg-slate-900 p-6 text-left hover:border-lime-400/40 hover:bg-lime-400/5">
                        <span class="absolute right-4 top-4 h-5 w-5 rounded-full border-2 border-white/20 group-hover:border-lime-400/60"></span>
                        <span class="block pr-8 font-semibold text-white">{{ $content->register_ngo_title }}</span>
                        <p class="mt-1 pr-8 text-sm text-slate-400">{{ $content->register_ngo_description }}</p>
                    </button>
                </div>
            </div>

            <div x-show="type" @unless ($selectedType) style="display: none;" @endunless>
                <button type="button" @click="type = ''" class="mb-4 inline-flex text-sm font-medium text-lime-300 hover:text-lime-200">&larr; Choose a different type</button>

                <form action="{{ route('business.register.store') }}" method="POST" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    <input type="hidden" name="business_type" :value="type">

                    <div class="md:col-span-2">
                        <p class="text-sm uppercase tracking-[0.25em] text-lime-300">Your account</p>
                    </div>
                    <label class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-slate-200">Your Name</span>
                        <input type="text" name="owner_name" value="{{ old('owner_name') }}" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-lime-400" placeholder="Your Name" required>
                    </label>
                    <label class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-slate-200">Your Email</span>
                        <input type="email" name="owner_email" value="{{ old('owner_email') }}" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-lime-400" placeholder="Your Email" required>
                    </label>
                    <label class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-slate-200">Password</span>
                        <input type="password" name="owner_password" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-lime-400" placeholder="Password" required>
                    </label>
                    <label class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-slate-200">Confirm Password</span>
                        <input type="password" name="owner_password_confirmation" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-lime-400" placeholder="Confirm Password" required>
                    </label>

                    <div class="md:col-span-2 mt-2">
                        <p class="text-sm uppercase tracking-[0.25em] text-lime-300" x-text="type === 'business' ? 'Business details' : (type === 'ngo' ? 'Organization details' : 'Your details')"></p>
                    </div>
                    <label class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-slate-200" x-text="type === 'business' ? 'Business Name' : (type === 'ngo' ? 'Organization Name' : 'Full Name')"></span>
                        <input type="text" name="business_name" value="{{ old('business_name') }}" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-lime-400" placeholder="Name" required>
                    </label>

                    <div x-show="type === 'business'" class="contents" @if ($selectedType !== 'business') style="display: none;" @endif>
                        <label class="flex flex-col gap-2">
                            <span class="text-sm font-medium text-slate-200">Trading Name</span>
                            <input type="text" name="trading_name" value="{{ old('trading_name') }}" :required="type === 'business'" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-lime-400" placeholder="Trading Name">
                        </label>
                        <label class="flex flex-col gap-2">
                            <span class="text-sm font-medium text-slate-200">Industry</span>
                            <input type="text" name="industry" value="{{ old('industry') }}" :required="type === 'business'" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-lime-400" placeholder="Industry">
                        </label>
                        <label class="flex flex-col gap-2">
                            <span class="text-sm font-medium text-slate-200">Expected Monthly Volume</span>
                            <input type="text" name="expected_monthly_volume" value="{{ old('expected_monthly_volume') }}" :required="type === 'business'" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-lime-400" placeholder="Expected Monthly Volume">
                        </label>
                    </div>

                    <label class="flex flex-col gap-2" x-show="type === 'business' || type === 'ngo'" @unless ($selectedType === 'business' || $selectedType === 'ngo') style="display: none;" @endunless>
                        <span class="text-sm font-medium text-slate-200" x-text="type === 'ngo' ? 'Registration Certificate Number' : 'Registration Number'"></span>
                        <input type="text" name="registration_number" value="{{ old('registration_number') }}" :required="type === 'business' || type === 'ngo'" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-lime-400" placeholder="Registration Number">
                    </label>

                    <label class="flex flex-col gap-2" x-show="type === 'individual'" @if ($selectedType !== 'individual') style="display: none;" @endif>
                        <span class="text-sm font-medium text-slate-200">National ID / Passport Number</span>
                        <input type="text" name="id_number" value="{{ old('id_number') }}" :required="type === 'individual'" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-lime-400" placeholder="National ID / Passport Number">
                    </label>

                    <label class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-slate-200">Country</span>
                        <input type="text" name="country" value="{{ old('country') }}" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-lime-400" placeholder="Country" required>
                    </label>
                    <label class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-slate-200">Phone</span>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-lime-400" placeholder="Phone" required>
                    </label>
                    <label class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-slate-200">Email</span>
                        <input type="email" name="email" value="{{ old('email') }}" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-lime-400" placeholder="Email" required>
                    </label>
                    <label class="flex flex-col gap-2 md:col-span-2">
                        <span class="text-sm font-medium text-slate-200" x-text="type === 'business' ? 'Business Description' : (type === 'ngo' ? 'Organization Description' : 'About you')"></span>
                        <textarea name="business_description" rows="4" class="rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none ring-0 placeholder:text-slate-500 focus:border-lime-400" placeholder="Tell us about your payment goals">{{ old('business_description') }}</textarea>
                    </label>

                    <div class="md:col-span-2">
                        <button type="submit" class="rounded-xl bg-lime-400 px-6 py-3 font-semibold text-slate-950 hover:bg-lime-300">Submit registration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-public-layout>
