<x-public-layout title="SentePro | Collect Payments. Settle Faster. Grow Your Business.">
    {{-- Hero --}}
    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <section class="grid items-center gap-10 lg:grid-cols-[1.15fr_0.85fr] lg:py-10">
            <div>
                <p class="mb-4 inline-flex px-3 py-1 text-xs font-semibold uppercase tracking-[0.25em] text-lime-300">{{ $content->hero_badge_text }}</p>
                <h1 class="max-w-2xl font-black tracking-tight text-white" style="font-size: {{ $content->headingSize('hero') }}">{{ $content->hero_headline }}</h1>
                <p class="mt-5 max-w-2xl text-lg text-slate-300">{{ $content->hero_subtext }}</p>
                <div class="mt-8 flex flex-wrap gap-2 sm:gap-3">
                    <a href="{{ route('business.register') }}" class="flex-1 whitespace-nowrap rounded-xl bg-lime-400 px-3 py-2.5 text-center text-[11px] font-semibold tracking-tight text-slate-950 shadow-lg shadow-lime-500/15 hover:bg-lime-300 sm:px-5 sm:py-3 sm:text-base sm:tracking-normal">Register your business</a>
                    <a href="/login" class="flex-1 whitespace-nowrap rounded-xl border border-white/15 px-3 py-2.5 text-center text-[11px] font-semibold tracking-tight text-white hover:border-white/30 hover:bg-white/5 sm:px-5 sm:py-3 sm:text-base sm:tracking-normal">Log in to dashboard</a>
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-white/10 bg-slate-900 shadow-2xl shadow-slate-950/40">
                @if ($content->hero_image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($content->hero_image_path) }}" alt="A shopkeeper collecting a payment" class="aspect-[4/3] w-full object-cover">
                @else
                    <x-illustration-shop-payment class="aspect-[4/3] w-full" />
                @endif
            </div>
        </section>

        <x-payment-method-logos :logos="$content->payment_logos" class="mt-14" />
    </div>

    <div class="mx-auto max-w-6xl space-y-20 px-4 py-20 sm:px-6 lg:px-8">
        {{-- For business / for customers --}}
        <section class="grid gap-6 md:grid-cols-2">
            <div class="rounded-3xl bg-slate-900 p-8 ring-1 ring-white/10">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">For your business</p>
                <h3 class="mt-2 font-bold text-white" style="font-size: {{ $content->headingSize('for_business') }}">Run your payment operations from one dashboard</h3>
                <ul class="mt-5 space-y-3 text-sm text-slate-300">
                    <li>• Track every settlement, transaction, and payment link in real time</li>
                    <li>• Invite staff with role-based permissions</li>
                    <li>• Export reports and reconcile fees automatically</li>
                </ul>
                <a href="{{ route('business.register') }}" class="mt-6 inline-flex rounded-xl bg-lime-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-lime-300">Register your business</a>
            </div>
            <div class="rounded-3xl border border-lime-400/40 bg-lime-400/10 p-8">
                <p class="text-xs font-semibold uppercase tracking-widest text-lime-300">For your customers</p>
                <h3 class="mt-2 font-bold text-white" style="font-size: {{ $content->headingSize('for_customers') }}">A fast, familiar checkout</h3>
                <ul class="mt-5 space-y-3 text-sm text-lime-50">
                    <li>• Pay by card via Pesapal or mobile money via MTN/Airtel</li>
                    <li>• Get an instant receipt by email, with a scannable verification QR code</li>
                    <li>• No account or app download required</li>
                </ul>
            </div>
        </section>

        {{-- Requirements --}}
        <section id="requirements">
            <div class="mb-8 max-w-2xl">
                <h2 class="font-bold" style="font-size: {{ $content->headingSize('requirements') }}">Who can use SentePro?</h2>
                <p class="mt-2 text-slate-300">Whatever kind of organization you run, here's what you'll need to get verified.</p>
            </div>
            <div class="grid gap-6 sm:grid-cols-3">
                @foreach (($content->requirements ?? []) as $requirement)
                    <div class="rounded-3xl bg-slate-900 p-6 ring-1 ring-white/10">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-lime-400/10 text-lime-300">
                            <x-sidebar-icon :name="$requirement['icon'] ?? 'shield'" class="h-5 w-5" />
                        </div>
                        <h3 class="mt-4 font-semibold text-white">{{ $requirement['title'] }}</h3>
                        <p class="mt-1 text-sm text-slate-400">{{ $requirement['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Features --}}
        <section id="features">
            <div class="mb-8 max-w-2xl">
                <h2 class="font-bold" style="font-size: {{ $content->headingSize('features') }}">Why SentePro?</h2>
                <p class="mt-2 text-slate-300">Fast, flexible, and secure payment collection for growing businesses.</p>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($content->features as $feature)
                    <div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-lime-400/10 text-lime-300">
                            <x-sidebar-icon :name="$feature['icon'] ?? 'link'" class="h-5 w-5" />
                        </div>
                        <h3 class="mt-4 font-semibold text-white">{{ $feature['title'] }}</h3>
                        <p class="mt-1 text-sm text-slate-400">{{ $feature['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Wallet balances --}}
        <section class="grid gap-10 lg:grid-cols-2 lg:items-center">
            <div>
                <h2 class="font-bold" style="font-size: {{ $content->headingSize('balances') }}">One dashboard for every balance</h2>
                <p class="mt-3 text-slate-300">See exactly where your money is — available to withdraw, reserved for settlement, or already paid out.</p>
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
    <section class="bg-slate-900 py-20">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:items-center lg:px-8">
            <div class="order-2 lg:order-1">
                <p class="inline-flex px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-lime-300">Payment links &amp; QR codes</p>
                <h2 class="mt-4 font-bold text-white" style="font-size: {{ $content->headingSize('payment_links') }}">Share a link or QR code, get paid instantly</h2>
                <p class="mt-3 text-slate-300">Every payment link comes with a scannable QR code and a copyable checkout URL — no integration work required to start collecting.</p>
                <a href="{{ route('business.register') }}" class="mt-6 inline-flex rounded-xl border border-white/15 px-5 py-3 font-semibold text-white hover:border-white/30 hover:bg-white/5">Get started</a>
            </div>
            <div class="order-1 flex justify-center lg:order-2">
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
        </div>
    </section>

    <div class="mx-auto max-w-6xl space-y-20 px-4 py-20 sm:px-6 lg:px-8">
        {{-- How it works --}}
        <section id="how-it-works" class="grid gap-10 lg:grid-cols-2 lg:items-center">
            <div class="overflow-hidden rounded-3xl border border-white/10 bg-slate-900">
                @if ($content->how_it_works_image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($content->how_it_works_image_path) }}" alt="Completing the SentePro signup form" class="aspect-[4/3] w-full object-cover">
                @else
                    <x-illustration-register class="aspect-[4/3] w-full" />
                @endif
            </div>
            <div>
                <h2 class="font-bold" style="font-size: {{ $content->headingSize('how_it_works') }}">It's simple to start using SentePro</h2>
                <ol class="mt-6 space-y-5">
                    <li class="flex gap-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-lime-400/10 text-sm font-semibold text-lime-300">1</span>
                        <div>
                            <p class="font-semibold text-white">Register your business</p>
                            <p class="text-sm text-slate-400">Submit your business and owner details in one form.</p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-lime-400/10 text-sm font-semibold text-lime-300">2</span>
                        <div>
                            <p class="font-semibold text-white">Get verified</p>
                            <p class="text-sm text-slate-400">A super admin reviews and approves your business.</p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-lime-400/10 text-sm font-semibold text-lime-300">3</span>
                        <div>
                            <p class="font-semibold text-white">Connect a gateway</p>
                            <p class="text-sm text-slate-400">Enable Pesapal for cards, Yo Payments for mobile money.</p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-lime-400/10 text-sm font-semibold text-lime-300">4</span>
                        <div>
                            <p class="font-semibold text-white">Collect &amp; settle</p>
                            <p class="text-sm text-slate-400">Share payment links and request settlements to your bank or wallet.</p>
                        </div>
                    </li>
                </ol>
                <a href="{{ route('business.register') }}" class="mt-6 inline-flex rounded-xl bg-lime-400 px-5 py-3 font-semibold text-slate-950 hover:bg-lime-300">Get started now</a>
            </div>
        </section>

        {{-- Gateways --}}
        <section id="gateways">
            <h2 class="font-bold" style="font-size: {{ $content->headingSize('gateways') }}">Supported payment ecosystem</h2>
            <p class="mt-2 text-slate-300">Pesapal for cards, Yo Payments for mobile money.</p>
            <x-payment-method-logos align="start" :logos="$content->payment_logos" class="mt-8" />
        </section>

        {{-- FAQ --}}
        <section id="faq" class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
            <div>
                <h2 class="font-bold" style="font-size: {{ $content->headingSize('faq') }}">Common questions</h2>
                <p class="mt-3 text-slate-300">Find answers to frequently asked questions about SentePro.</p>
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
    <section class="relative overflow-hidden bg-slate-900 py-20">
        <span class="pointer-events-none absolute -right-6 -top-10 select-none text-[10rem] font-black leading-none text-lime-400/10 sm:text-[14rem]">$0</span>
        <div class="relative mx-auto flex max-w-6xl flex-col items-center gap-6 px-4 text-center sm:px-6 lg:px-8">
            <h2 class="font-bold text-white" style="font-size: {{ $content->headingSize('cta') }}">{{ $content->cta_banner_heading }}</h2>
            <p class="max-w-xl text-slate-300">{{ $content->cta_banner_subtext }}</p>
            <a href="{{ route('business.register') }}" class="rounded-xl bg-lime-400 px-6 py-3 font-semibold text-slate-950 hover:bg-lime-300">Register now</a>
        </div>
    </section>
</x-public-layout>
