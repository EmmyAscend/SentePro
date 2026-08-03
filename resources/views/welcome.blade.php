<x-public-layout title="SentePro | Collect Payments. Settle Faster. Grow Your Business.">
    {{-- Hero: full-bleed photo, gradient scrim, floating badge --}}
    <section class="relative min-h-[30rem] overflow-hidden lg:min-h-[38rem]">
        @if ($content->hero_image_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($content->hero_image_path) }}" alt="A shopkeeper collecting a payment" class="absolute inset-0 h-full w-full object-cover">
        @else
            <div class="absolute inset-0 bg-slate-900">
                <x-illustration-shop-payment class="h-full w-full opacity-70" />
            </div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-950/40 to-slate-950/10"></div>

        <div class="absolute inset-0 flex flex-col justify-end px-4 pb-10 sm:px-6 lg:px-16 lg:pb-16">
            <h1 class="max-w-2xl font-display text-[length:var(--sh-mobile)] uppercase leading-[0.95] text-white lg:text-[length:var(--sh-desktop)]" style="--sh-mobile: {{ $content->headingSize('hero') }}; --sh-desktop: {{ $content->section_heading_size_px }}px;">{{ $content->hero_headline }}</h1>
            <a href="{{ route('business.register') }}" class="mt-6 inline-flex w-fit items-center gap-2 rounded-full bg-lime-400 px-5 py-3 text-sm font-semibold text-slate-950 hover:bg-lime-300">
                {{ $content->hero_cta_text }}
                <x-sidebar-icon name="chevron" class="h-4 w-4" />
            </a>
            <p class="mt-4 max-w-sm text-sm text-slate-200 lg:text-[length:var(--sd-desktop)]" style="--sd-desktop: {{ $content->section_description_size_px }}px;">{{ $content->hero_subtext }}</p>
        </div>

        <div class="absolute bottom-6 right-4 rounded-2xl bg-white/95 px-5 py-4 shadow-lg sm:right-6 lg:right-16">
            <p class="font-display text-lg leading-tight text-slate-900">/{{ $content->hero_badge_text }}</p>
        </div>
    </section>

    {{-- About: repurposes the Features intro as a mission-statement block --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="grid gap-10 lg:grid-cols-2 lg:items-start lg:gap-16">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-lime-600">/About Us</p>
                <h2 class="mt-4 font-display text-3xl uppercase leading-[0.95] text-slate-900 sm:text-4xl">{{ $content->features_heading }}</h2>
                <p class="mt-4 max-w-md text-slate-600">{{ $content->features_subtext }}</p>
                <a href="{{ route('business.register') }}" class="mt-6 inline-flex rounded-full bg-lime-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-lime-300">{{ $content->hero_cta_text }}</a>
            </div>
            <div class="overflow-hidden rounded-[2rem]">
                @if ($content->hero_image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($content->hero_image_path) }}" alt="{{ $content->features_heading }}" class="aspect-[4/3] w-full object-cover">
                @else
                    <x-illustration-shop-payment class="aspect-[4/3] w-full bg-slate-900" />
                @endif
            </div>
        </div>

        <div class="mt-10 flex flex-col items-center gap-6 border-t border-slate-200 pt-10 sm:flex-row sm:justify-between">
            <div class="h-24 w-24 shrink-0 overflow-hidden rounded-2xl sm:h-28 sm:w-28">
                @if ($content->how_it_works_image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($content->how_it_works_image_path) }}" alt="" class="h-full w-full object-cover">
                @else
                    <x-illustration-register class="h-full w-full bg-slate-900" />
                @endif
            </div>
            <p class="max-w-xs text-center font-display text-xl uppercase leading-tight text-slate-900 sm:text-left">{{ $content->gateways_heading }}</p>
            <div class="text-center sm:text-right">
                <p class="font-display text-3xl text-slate-900">/2</p>
                <p class="text-xs uppercase tracking-wide text-slate-500">Gateways supported</p>
            </div>
        </div>
    </section>

    {{-- Payment logos strip --}}
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <x-payment-method-logos :logos="$content->payment_logos" desktop-align="start" />
    </div>

    {{-- Requirements: who can use SentePro, alternating image/text with type-specific register links --}}
    <div class="mx-auto max-w-6xl px-4 pt-10 sm:px-6 lg:px-8" id="requirements">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-lime-600">/Requirements</p>
        <h2 class="mt-2 max-w-2xl font-display text-3xl uppercase leading-[0.95] text-slate-900 sm:text-4xl">{{ $content->requirements_heading }}</h2>
        <p class="mt-3 max-w-2xl text-slate-600">{{ $content->requirements_subtext }}</p>
    </div>
    <div class="mx-auto max-w-6xl space-y-10 px-4 py-10 sm:px-6 lg:px-8">
        @foreach (($content->requirements ?? []) as $requirement)
            @php
                $typeLabel = \App\Models\LandingPageContent::REQUIREMENT_TYPE_OPTIONS[$requirement['type'] ?? ''] ?? '';
                $registerUrl = route('business.register', ($requirement['type'] ?? '') ? ['type' => $requirement['type']] : []);
                $imageFirst = $loop->iteration % 2 !== 0;
            @endphp
            <div class="grid gap-6 lg:grid-cols-2 lg:items-center lg:gap-12">
                <div class="{{ $imageFirst ? 'lg:order-1' : 'lg:order-2' }} overflow-hidden rounded-[2rem] border border-slate-800 bg-slate-900">
                    @if (! empty($requirement['image_path']))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($requirement['image_path']) }}" alt="{{ $requirement['title'] }}" class="aspect-[4/3] w-full object-contain">
                    @elseif ($content->hero_image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($content->hero_image_path) }}" alt="{{ $requirement['title'] }}" class="aspect-[4/3] w-full object-contain">
                    @else
                        <x-illustration-shop-payment class="aspect-[4/3] w-full" />
                    @endif
                </div>
                <div class="{{ $imageFirst ? 'lg:order-2' : 'lg:order-1' }}">
                    <h3 class="font-display text-2xl uppercase text-slate-900 lg:text-[length:var(--sh-desktop)]" style="--sh-desktop: {{ $content->section_heading_size_px }}px;">{{ $requirement['title'] }}</h3>
                    <p class="mt-2 max-w-md text-slate-600 lg:text-[length:var(--sd-desktop)]" style="--sd-desktop: {{ $content->section_description_size_px }}px;">{{ $requirement['description'] }}</p>
                    <a href="{{ $registerUrl }}" class="mt-5 inline-flex rounded-full bg-lime-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-lime-300">{{ $typeLabel ? 'Register as '.$typeLabel : 'Register now' }}</a>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Proven results: real settlement copy, not invented stats --}}
    <section class="relative overflow-hidden bg-slate-900 py-16 lg:py-24">
        <div class="pointer-events-none absolute inset-0 opacity-[0.07]" style="background-image:linear-gradient(#fff 1px,transparent 1px),linear-gradient(90deg,#fff 1px,transparent 1px);background-size:2.5rem 2.5rem;"></div>
        <div class="relative mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-center font-display text-2xl uppercase leading-tight text-white sm:text-3xl" style="font-size: {{ $content->headingSize('balances') }}">{{ $content->balances_heading }}</h2>
            <div class="mt-10 grid items-center gap-6 lg:grid-cols-3">
                <div class="rounded-[2rem] bg-lime-400 p-6">
                    <p class="font-display text-lg leading-snug text-slate-950">{{ $content->balances_subtext }}</p>
                </div>
                <div class="mx-auto w-full overflow-hidden rounded-[2rem]">
                    @if ($content->payment_links_image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($content->payment_links_image_path) }}" alt="" class="aspect-[3/4] w-full object-cover">
                    @else
                        <x-illustration-shop-payment class="aspect-[3/4] w-full bg-slate-800" />
                    @endif
                </div>
                <div class="rounded-[2rem] bg-white p-6">
                    <ul class="space-y-3 text-sm font-semibold text-slate-900">
                        <li>Request a settlement the moment funds are available</li>
                        <li>Fees are calculated and locked in upfront</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- Features, as a hover-highlighted services list --}}
    <section id="features" class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-lime-600">/Gain</p>
                <h2 class="mt-2 font-display text-3xl uppercase leading-[0.95] text-slate-900 sm:text-4xl">{{ $content->features_heading }}</h2>
            </div>
            <a href="{{ route('business.register') }}" class="inline-flex w-fit items-center gap-2 rounded-full bg-lime-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-lime-300">
                {{ $content->hero_cta_text }}
                <x-sidebar-icon name="chevron" class="h-4 w-4" />
            </a>
        </div>
        <p class="mt-3 max-w-xl text-slate-600">{{ $content->features_subtext }}</p>

        <div class="mt-10 border-t border-slate-200" x-data="{ active: 0 }">
            @foreach ($content->features as $i => $feature)
                <div
                    class="flex flex-col gap-2 border-b border-slate-200 px-4 py-5 transition-colors sm:flex-row sm:items-center sm:justify-between sm:gap-6 sm:px-6"
                    :class="active === {{ $i }} ? 'bg-lime-400' : ''"
                    @mouseenter="active = {{ $i }}"
                >
                    <span class="font-display text-lg uppercase sm:text-xl" :class="active === {{ $i }} ? 'text-slate-950' : 'text-slate-900'">
                        {{ $feature['title'] }}
                        <x-sidebar-icon name="chevron" class="ml-2 inline h-4 w-4 -rotate-45" x-show="active === {{ $i }}" style="display: none;" />
                    </span>
                    <span class="max-w-sm text-sm" :class="active === {{ $i }} ? 'text-slate-800' : 'text-slate-500'">{{ $feature['description'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Payment links & QR spotlight --}}
    <section class="bg-slate-900 lg:grid lg:min-h-[28rem] lg:grid-cols-2 lg:items-stretch">
        <div class="order-2 px-4 py-16 sm:px-6 lg:order-1 lg:flex lg:flex-col lg:justify-center lg:px-16 lg:py-0">
            <p class="inline-flex text-xs font-semibold uppercase tracking-[0.2em] text-lime-400">Payment links &amp; QR codes</p>
            <h2 class="mt-4 font-display text-[length:var(--sh-mobile)] uppercase leading-[0.95] text-white lg:text-[length:var(--sh-desktop)]" style="--sh-mobile: {{ $content->headingSize('payment_links') }}; --sh-desktop: {{ $content->section_heading_size_px }}px;">{{ $content->payment_links_heading }}</h2>
            <p class="mt-3 max-w-md text-slate-300 lg:text-[length:var(--sd-desktop)]" style="--sd-desktop: {{ $content->section_description_size_px }}px;">{{ $content->payment_links_subtext }}</p>
            <a href="{{ route('business.register') }}" class="mt-6 inline-flex w-fit rounded-full border border-white/20 px-5 py-3 font-semibold text-white hover:border-white/40 hover:bg-white/5">Get started</a>
        </div>
        <div class="order-1 flex justify-center px-4 py-10 sm:px-6 lg:order-2 lg:items-center lg:px-0">
            @if ($content->payment_links_image_path)
                <div class="overflow-hidden rounded-[1.5rem] ring-1 ring-white/10">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($content->payment_links_image_path) }}" alt="Payment links and QR codes" class="aspect-square w-64 object-cover">
                </div>
            @else
                <div class="rounded-[1.5rem] bg-slate-800/60 p-6 ring-1 ring-white/10">
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
    <section id="how-it-works" class="bg-slate-900 lg:grid lg:min-h-[28rem] lg:grid-cols-2 lg:items-stretch">
        <div class="bg-slate-900 px-4 pt-20 sm:px-6 lg:flex lg:h-full lg:items-center lg:justify-center lg:px-0 lg:pt-0">
            <div class="overflow-hidden rounded-[2rem] border border-slate-800 bg-slate-900 lg:h-3/4 lg:w-3/4">
                @if ($content->how_it_works_image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($content->how_it_works_image_path) }}" alt="Completing the SentePro signup form" class="aspect-[4/3] w-full object-cover lg:aspect-auto lg:h-full lg:w-full">
                @else
                    <x-illustration-register class="aspect-[4/3] w-full lg:aspect-auto lg:h-full lg:w-full" />
                @endif
            </div>
        </div>
        <div class="bg-slate-900 mt-6 px-4 py-10 sm:px-6 lg:mt-0 lg:flex lg:flex-col lg:justify-center lg:px-16 lg:py-0">
            <h2 class="font-display text-[length:var(--sh-mobile)] uppercase leading-[0.95] text-white lg:text-[length:var(--sh-desktop)]" style="--sh-mobile: {{ $content->headingSize('how_it_works') }}; --sh-desktop: {{ $content->section_heading_size_px }}px;">{{ $content->how_it_works_heading }}</h2>
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
            <a href="{{ route('business.register') }}" class="mt-6 inline-flex w-fit rounded-full bg-lime-400 px-5 py-3 font-semibold text-slate-950 hover:bg-lime-300">{{ $content->how_it_works_cta_text }}</a>
        </div>
    </section>

    <div class="mx-auto max-w-6xl space-y-16 px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        {{-- Gateways --}}
        <section id="gateways">
            <h2 class="font-display text-3xl uppercase leading-[0.95] text-slate-900 sm:text-4xl" style="font-size: {{ $content->headingSize('gateways') }}">{{ $content->gateways_heading }}</h2>
            <p class="mt-2 text-slate-600">{{ $content->gateways_subtext }}</p>
            <x-payment-method-logos align="start" :logos="$content->payment_logos" class="mt-8" />
        </section>

        {{-- FAQ --}}
        <section id="faq" class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
            <div>
                <h2 class="font-display text-3xl uppercase leading-[0.95] text-slate-900 sm:text-4xl" style="font-size: {{ $content->headingSize('faq') }}">{{ $content->faq_heading }}</h2>
                <p class="mt-3 text-slate-600">{{ $content->faq_subtext }}</p>
                <a href="/login" class="mt-4 inline-flex text-sm font-semibold text-lime-700 hover:text-lime-600">Have another question? Log in and open a support ticket →</a>
            </div>
            <div x-data="{ open: 0 }" class="space-y-3">
                @foreach ($content->faqs as $i => $faq)
                    <div class="rounded-[1.5rem] bg-white shadow-sm ring-1 ring-slate-900/5">
                        <button type="button" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left" @click="open = open === {{ $i }} ? null : {{ $i }}">
                            <span class="font-medium text-slate-900">{{ $faq['question'] }}</span>
                            <svg class="h-5 w-5 shrink-0 text-slate-400 transition-transform" :class="{ 'rotate-180': open === {{ $i }} }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <div x-show="open === {{ $i }}" x-transition class="px-5 pb-4 text-sm text-slate-600">{{ $faq['answer'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    {{-- CTA banner --}}
    <section class="relative overflow-hidden bg-lime-400 py-16 lg:py-24">
        <div class="pointer-events-none absolute -right-6 top-8 grid grid-cols-4 gap-2 opacity-40 sm:right-10 lg:right-16">
            @for ($i = 0; $i < 16; $i++)
                <span class="h-3 w-3 rounded-sm {{ $i % 2 === 0 ? 'bg-slate-950' : 'bg-transparent' }}"></span>
            @endfor
        </div>
        <div class="relative mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <h2 class="max-w-lg font-display text-3xl uppercase leading-[0.95] text-slate-950 sm:text-4xl" style="font-size: {{ $content->headingSize('cta') }}">{{ $content->cta_banner_heading }}</h2>
            <p class="mt-4 max-w-md text-slate-800">{{ $content->cta_banner_subtext }}</p>
            <a href="{{ route('business.register') }}" class="mt-6 inline-flex rounded-full bg-slate-950 px-6 py-3 font-semibold text-white hover:bg-slate-800">Register now</a>
        </div>
    </section>
</x-public-layout>
