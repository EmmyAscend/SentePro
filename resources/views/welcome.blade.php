<x-public-layout title="SentePro | Collect Payments. Settle Faster. Grow Your Business.">
    {{-- Hero --}}
    <section class="lg:grid lg:min-h-[32rem] lg:grid-cols-2 lg:items-stretch">
        <div class="px-4 py-12 sm:px-6 lg:flex lg:flex-col lg:justify-center lg:px-16 lg:py-16">
            <p class="mb-4 inline-flex px-3 py-1 text-xs font-semibold uppercase tracking-[0.25em] text-lime-300">{{ $content->hero_badge_text }}</p>
            <h1 class="max-w-2xl text-[length:var(--sh-mobile)] font-black tracking-tight text-white lg:text-[length:var(--sh-desktop)]" style="--sh-mobile: {{ $content->headingSize('hero') }}; --sh-desktop: {{ $content->section_heading_size_px }}px;">{{ $content->hero_headline }}</h1>
            <p class="mt-5 max-w-2xl text-lg text-slate-300 lg:text-[length:var(--sd-desktop)]" style="--sd-desktop: {{ $content->section_description_size_px }}px;">{{ $content->hero_subtext }}</p>
            <div class="mt-8 flex flex-wrap gap-2 sm:gap-3">
                <a href="{{ route('business.register') }}" class="flex-1 whitespace-nowrap rounded-xl bg-lime-400 px-3 py-2.5 text-center text-[11px] font-semibold tracking-tight text-slate-950 shadow-lg shadow-lime-500/15 hover:bg-lime-300 sm:px-5 sm:py-3 sm:text-base sm:tracking-normal">{{ $content->hero_cta_text }}</a>
                <a href="/login" class="flex-1 whitespace-nowrap rounded-xl border border-white/15 px-3 py-2.5 text-center text-[11px] font-semibold tracking-tight text-white hover:border-white/30 hover:bg-white/5 sm:px-5 sm:py-3 sm:text-base sm:tracking-normal">Log in to dashboard</a>
            </div>
        </div>

        <div class="mt-8 px-4 sm:px-6 lg:mt-0 lg:flex lg:h-full lg:items-center lg:justify-center lg:px-0">
            <div class="overflow-hidden rounded-3xl border border-white/10 bg-slate-900 shadow-2xl shadow-slate-950/40 lg:h-3/4 lg:w-3/4 lg:rounded-none lg:border-0 lg:shadow-none">
                @if ($content->hero_image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($content->hero_image_path) }}" alt="A shopkeeper collecting a payment" class="aspect-[4/3] w-full object-cover lg:aspect-auto lg:h-full lg:w-full">
                @else
                    <x-illustration-shop-payment class="aspect-[4/3] w-full lg:aspect-auto lg:h-full lg:w-full" />
                @endif
            </div>
        </div>
    </section>

    <div class="mx-auto mt-[1cm] max-w-6xl px-4 sm:px-6 lg:px-8">
        <x-payment-method-logos :logos="$content->payment_logos" desktop-align="start" />
    </div>

    {{-- Requirements: who can use SentePro, each as its own alternating full-bleed image/text section --}}
    <div class="mx-auto mt-[1cm] max-w-6xl px-4 pb-10 sm:px-6 lg:px-8" id="requirements">
        <div class="max-w-2xl">
            <h2 class="font-bold" style="font-size: {{ $content->headingSize('requirements') }}">{{ $content->requirements_heading }}</h2>
            <p class="mt-2 text-slate-300">{{ $content->requirements_subtext }}</p>
        </div>
    </div>
    <div class="space-y-[1cm]">
        @foreach (($content->requirements ?? []) as $requirement)
            @php
                $typeLabel = \App\Models\LandingPageContent::REQUIREMENT_TYPE_OPTIONS[$requirement['type'] ?? ''] ?? '';
                $registerUrl = route('business.register', ($requirement['type'] ?? '') ? ['type' => $requirement['type']] : []);
                $imageFirst = $loop->iteration % 2 !== 0;
            @endphp
            <section class="lg:grid lg:min-h-[15rem] lg:grid-cols-2 lg:items-stretch">
                <div class="{{ $imageFirst ? 'lg:order-1' : 'lg:order-2' }} px-4 sm:px-6 lg:flex lg:h-full lg:items-center lg:justify-center lg:px-0">
                    <div class="overflow-hidden rounded-3xl border border-white/10 bg-slate-900 lg:h-3/4 lg:w-3/4 lg:rounded-none lg:border-0">
                        @if (! empty($requirement['image_path']))
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($requirement['image_path']) }}" alt="{{ $requirement['title'] }}" class="aspect-[4/3] w-full object-contain lg:aspect-auto lg:h-full lg:w-full">
                        @elseif ($content->hero_image_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($content->hero_image_path) }}" alt="{{ $requirement['title'] }}" class="aspect-[4/3] w-full object-contain lg:aspect-auto lg:h-full lg:w-full">
                        @else
                            <x-illustration-shop-payment class="aspect-[4/3] w-full lg:aspect-auto lg:h-full lg:w-full" />
                        @endif
                    </div>
                </div>
                <div class="{{ $imageFirst ? 'lg:order-2' : 'lg:order-1' }} mt-6 px-4 sm:px-6 lg:mt-0 lg:flex lg:flex-col lg:justify-center lg:bg-slate-900 lg:px-16">
                    <h3 class="font-semibold text-white lg:text-[length:var(--sh-desktop)]" style="--sh-desktop: {{ $content->section_heading_size_px }}px;">{{ $requirement['title'] }}</h3>
                    <p class="mt-1 text-slate-400 lg:text-[length:var(--sd-desktop)]" style="--sd-desktop: {{ $content->section_description_size_px }}px;">{{ $requirement['description'] }}</p>
                    <a href="{{ $registerUrl }}" class="mt-6 inline-flex rounded-xl bg-lime-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-lime-300 lg:self-start">{{ $typeLabel ? 'Register as '.$typeLabel : 'Register now' }}</a>
                </div>
            </section>
        @endforeach
    </div>

    <div class="mx-auto mt-[1cm] max-w-6xl space-y-[1cm] px-4 sm:px-6 lg:px-8">
        {{-- Features --}}
        <section id="features">
            <div class="mb-8 max-w-2xl">
                <h2 class="font-bold" style="font-size: {{ $content->headingSize('features') }}">{{ $content->features_heading }}</h2>
                <p class="mt-2 text-slate-300">{{ $content->features_subtext }}</p>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($content->features as $feature)
                    <div class="rounded-3xl bg-slate-900 p-6 ring-1 ring-white/10">
                        <h3 class="font-semibold text-white">{{ $feature['title'] }}</h3>
                        <p class="mt-1 text-sm text-slate-400">{{ $feature['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Wallet balances --}}
        <section class="grid gap-10 lg:grid-cols-2 lg:items-center">
            <div>
                <h2 class="font-bold" style="font-size: {{ $content->headingSize('balances') }}">{{ $content->balances_heading }}</h2>
                <p class="mt-3 text-slate-300">{{ $content->balances_subtext }}</p>
                <ul class="mt-5 space-y-2 text-sm text-slate-300">
                    <li>• Request a settlement the moment funds are available</li>
                    <li>• Fees are calculated and locked in upfront</li>
                    <li>• Full transaction and settlement history, exportable to CSV</li>
                </ul>
            </div>
            <div class="rounded-3xl bg-slate-900 p-6 ring-1 ring-white/10">
                <div class="space-y-3">
                    <div class="flex items-center justify-between rounded-xl bg-slate-800/60 px-4 py-3">
                        <span class="text-sm text-slate-300">Available balance</span>
                        <span class="text-sm font-semibold text-white">Ready to settle</span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-slate-800/60 px-4 py-3">
                        <span class="text-sm text-slate-300">Pending balance</span>
                        <span class="text-sm font-semibold text-white">Awaiting settlement</span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-slate-800/60 px-4 py-3">
                        <span class="text-sm text-slate-300">Settlement balance</span>
                        <span class="text-sm font-semibold text-white">Paid out</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Payment links & QR spotlight --}}
    <section class="mt-[1cm] lg:grid lg:min-h-[30rem] lg:grid-cols-2 lg:items-stretch">
        <div class="order-2 bg-slate-900 px-4 py-16 sm:px-6 lg:order-1 lg:flex lg:flex-col lg:justify-center lg:px-16 lg:py-0">
            <p class="inline-flex px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-lime-300">Payment links &amp; QR codes</p>
            <h2 class="mt-4 text-[length:var(--sh-mobile)] font-bold text-white lg:text-[length:var(--sh-desktop)]" style="--sh-mobile: {{ $content->headingSize('payment_links') }}; --sh-desktop: {{ $content->section_heading_size_px }}px;">{{ $content->payment_links_heading }}</h2>
            <p class="mt-3 text-slate-300 lg:text-[length:var(--sd-desktop)]" style="--sd-desktop: {{ $content->section_description_size_px }}px;">{{ $content->payment_links_subtext }}</p>
            <a href="{{ route('business.register') }}" class="mt-6 inline-flex rounded-xl border border-white/15 px-5 py-3 font-semibold text-white hover:border-white/30 hover:bg-white/5 lg:self-start">Get started</a>
        </div>
        <div class="order-1 flex justify-center bg-slate-900 px-4 py-10 sm:px-6 lg:order-2 lg:h-full lg:items-center lg:px-0 lg:py-0">
            @if ($content->payment_links_image_path)
                <div class="overflow-hidden rounded-3xl ring-1 ring-white/10">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($content->payment_links_image_path) }}" alt="Payment links and QR codes" class="aspect-square w-64 object-cover">
                </div>
            @else
                <div class="rounded-3xl bg-slate-800/60 p-6 ring-1 ring-white/10">
                    <div class="mx-auto grid h-40 w-40 grid-cols-5 gap-1 rounded-2xl bg-white p-3">
                        @for ($i = 0; $i < 25; $i++)
                            <span class="{{ in_array($i, [0, 1, 3, 4, 5, 9, 10, 14, 15, 19, 20, 21, 23, 24, 12, 7, 17, 2, 22]) ? 'bg-slate-950' : 'bg-white' }}"></span>
                        @endfor
                    </div>
                    <p class="mt-4 text-center text-sm text-slate-400">app.sentepro.io/pay/…</p>
                </div>
            @endif
        </div>
    </section>

    {{-- How it works --}}
    <section id="how-it-works" class="mt-[1cm] lg:grid lg:min-h-[30rem] lg:grid-cols-2 lg:items-stretch">
        <div class="px-4 pt-20 sm:px-6 lg:flex lg:h-full lg:items-center lg:justify-center lg:px-0 lg:pt-0">
            <div class="overflow-hidden rounded-3xl border border-white/10 bg-slate-900 lg:h-3/4 lg:w-3/4 lg:rounded-none lg:border-0">
                @if ($content->how_it_works_image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($content->how_it_works_image_path) }}" alt="Completing the SentePro signup form" class="aspect-[4/3] w-full object-cover lg:aspect-auto lg:h-full lg:w-full">
                @else
                    <x-illustration-register class="aspect-[4/3] w-full lg:aspect-auto lg:h-full lg:w-full" />
                @endif
            </div>
        </div>
        <div class="mt-6 px-4 py-10 sm:px-6 lg:mt-0 lg:flex lg:flex-col lg:justify-center lg:bg-slate-900 lg:px-16 lg:py-0">
            <h2 class="text-[length:var(--sh-mobile)] font-bold lg:text-[length:var(--sh-desktop)]" style="--sh-mobile: {{ $content->headingSize('how_it_works') }}; --sh-desktop: {{ $content->section_heading_size_px }}px;">{{ $content->how_it_works_heading }}</h2>
            <ol class="mt-6 space-y-5">
                @foreach (($content->how_it_works_steps ?? []) as $i => $step)
                    <li class="flex gap-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-lime-400/10 text-sm font-semibold text-lime-300">{{ $i + 1 }}</span>
                        <div>
                            <p class="font-semibold text-white">{{ $step['title'] }}</p>
                            <p class="text-sm text-slate-400">{{ $step['description'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
            <a href="{{ route('business.register') }}" class="mt-6 inline-flex rounded-xl bg-lime-400 px-5 py-3 font-semibold text-slate-950 hover:bg-lime-300 lg:self-start">{{ $content->how_it_works_cta_text }}</a>
        </div>
    </section>

    <div class="mx-auto mt-[1cm] max-w-6xl space-y-[1cm] px-4 sm:px-6 lg:px-8">
        {{-- Gateways --}}
        <section id="gateways">
            <h2 class="font-bold" style="font-size: {{ $content->headingSize('gateways') }}">{{ $content->gateways_heading }}</h2>
            <p class="mt-2 text-slate-300">{{ $content->gateways_subtext }}</p>
            <x-payment-method-logos align="start" :logos="$content->payment_logos" class="mt-8" />
        </section>

        {{-- FAQ --}}
        <section id="faq" class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
            <div>
                <h2 class="font-bold" style="font-size: {{ $content->headingSize('faq') }}">{{ $content->faq_heading }}</h2>
                <p class="mt-3 text-slate-300">{{ $content->faq_subtext }}</p>
                <a href="/login" class="mt-4 inline-flex text-sm font-semibold text-lime-300 hover:text-lime-200">Have another question? Log in and open a support ticket →</a>
            </div>
            <div x-data="{ open: 0 }" class="space-y-3">
                @foreach ($content->faqs as $i => $faq)
                    <div class="rounded-2xl border border-white/10 bg-white/5">
                        <button type="button" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left" @click="open = open === {{ $i }} ? null : {{ $i }}">
                            <span class="font-medium text-white">{{ $faq['question'] }}</span>
                            <svg class="h-5 w-5 shrink-0 text-slate-400 transition-transform" :class="{ 'rotate-180': open === {{ $i }} }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <div x-show="open === {{ $i }}" x-transition class="px-5 pb-4 text-sm text-slate-300">{{ $faq['answer'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    {{-- CTA banner --}}
    <section class="relative mt-[1cm] overflow-hidden bg-slate-900 py-20">
        <span class="pointer-events-none absolute -right-6 -top-10 select-none text-[10rem] font-black leading-none text-lime-400/10 sm:text-[14rem]">$0</span>
        <div class="relative mx-auto flex max-w-6xl flex-col items-center gap-6 px-4 text-center sm:px-6 lg:px-8">
            <h2 class="font-bold text-white" style="font-size: {{ $content->headingSize('cta') }}">{{ $content->cta_banner_heading }}</h2>
            <p class="max-w-xl text-slate-300">{{ $content->cta_banner_subtext }}</p>
            <a href="{{ route('business.register') }}" class="rounded-xl bg-lime-400 px-6 py-3 font-semibold text-slate-950 hover:bg-lime-300">Register now</a>
        </div>
    </section>
</x-public-layout>
