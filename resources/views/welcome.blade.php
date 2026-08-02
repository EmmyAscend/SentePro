<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SentePro | Collect Payments. Settle Faster. Grow Your Business.</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=pacifico:400&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-950 text-white">
        <div class="min-h-screen">
            <header class="sticky top-0 z-20 border-b border-white/10 bg-slate-950/80 backdrop-blur">
                <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <x-brand-mark class="text-2xl" />
                    <nav class="hidden gap-6 text-sm text-slate-300 md:flex">
                        <a href="#features" class="hover:text-white">Features</a>
                        <a href="#gateways" class="hover:text-white">Gateways</a>
                        <a href="#faq" class="hover:text-white">FAQ</a>
                        <a href="/login" class="hover:text-white">Login</a>
                    </nav>
                    <a href="{{ route('business.register') }}" class="rounded-full bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">Start free onboarding</a>
                </div>
            </header>

            {{-- Hero --}}
            <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
                <section class="grid items-center gap-10 lg:grid-cols-[1.15fr_0.85fr] lg:py-10">
                    <div>
                        <p class="mb-4 inline-flex border border-lime-400/40 bg-lime-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.25em] text-lime-300">{{ $content->hero_badge_text }}</p>
                        <h1 class="max-w-2xl text-4xl font-black tracking-tight text-white sm:text-5xl lg:text-6xl">{{ $content->hero_headline }}</h1>
                        <p class="mt-5 max-w-2xl text-lg text-slate-300">{{ $content->hero_subtext }}</p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ route('business.register') }}" class="rounded-xl bg-lime-400 px-5 py-3 font-semibold text-slate-950 shadow-lg shadow-lime-500/15 hover:bg-lime-300">Register your business</a>
                            <a href="/login" class="rounded-xl border border-white/15 px-5 py-3 font-semibold text-white hover:border-white/30 hover:bg-white/5">Log in to dashboard</a>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-white/10 bg-white/5 p-2 shadow-2xl shadow-slate-950/40">
                        <div class="flex items-center gap-1.5 rounded-t-2xl bg-slate-900 px-4 py-3">
                            <span class="h-2.5 w-2.5 rounded-full bg-rose-400/70"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-400/70"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-lime-400/70"></span>
                            <span class="ml-3 truncate rounded-md bg-slate-800 px-2 py-0.5 text-xs text-slate-400">app.sentepro.io/dashboard</span>
                        </div>
                        <div class="rounded-b-2xl bg-slate-900 p-4">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl bg-slate-800/60 p-4">
                                    <div class="text-sm text-slate-400">{{ $content->stat_1_label }}</div>
                                    <div class="mt-2 text-3xl font-bold text-white">{{ $content->stat_1_value }}</div>
                                </div>
                                <div class="rounded-2xl bg-slate-800/60 p-4">
                                    <div class="text-sm text-slate-400">{{ $content->stat_2_label }}</div>
                                    <div class="mt-2 text-3xl font-bold text-white">{{ $content->stat_2_value }}</div>
                                </div>
                                <div class="rounded-2xl bg-slate-800/60 p-4 sm:col-span-2">
                                    <div class="text-sm text-slate-400">Platform snapshot</div>
                                    <ul class="mt-3 space-y-2 text-sm text-slate-200">
                                        <li>• Verified onboarding workflow</li>
                                        <li>• Multi-tenant staff administration</li>
                                        <li>• Queued settlement and payment orchestration</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Trust strip --}}
            <section class="border-y border-white/10 bg-white/[0.02] py-6">
                <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                    <x-payment-method-logos />
                </div>
            </section>

            <div class="mx-auto max-w-6xl space-y-20 px-4 py-20 sm:px-6 lg:px-8">
                {{-- For business / for customers --}}
                <section class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-3xl bg-slate-900 p-8 ring-1 ring-white/10">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">For your business</p>
                        <h3 class="mt-2 text-2xl font-bold text-white">Run your payment operations from one dashboard</h3>
                        <ul class="mt-5 space-y-3 text-sm text-slate-300">
                            <li>• Track every settlement, transaction, and payment link in real time</li>
                            <li>• Invite staff with role-based permissions</li>
                            <li>• Export reports and reconcile fees automatically</li>
                        </ul>
                        <a href="{{ route('business.register') }}" class="mt-6 inline-flex rounded-xl bg-lime-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-lime-300">Register your business</a>
                    </div>
                    <div class="rounded-3xl border border-lime-400/40 bg-lime-400/10 p-8">
                        <p class="text-xs font-semibold uppercase tracking-widest text-lime-300">For your customers</p>
                        <h3 class="mt-2 text-2xl font-bold text-white">A fast, familiar checkout</h3>
                        <ul class="mt-5 space-y-3 text-sm text-lime-50">
                            <li>• Pay by card via Pesapal or mobile money via MTN/Airtel</li>
                            <li>• Get an instant receipt by email, with a scannable verification QR code</li>
                            <li>• No account or app download required</li>
                        </ul>
                    </div>
                </section>

                {{-- Features --}}
                <section id="features">
                    <div class="mb-8 max-w-2xl">
                        <h2 class="text-3xl font-bold">Why SentePro?</h2>
                        <p class="mt-2 text-slate-300">Fast, flexible, and secure payment collection for growing businesses.</p>
                    </div>
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($content->features as $i => $feature)
                            @php $icon = ['link', 'check', 'users', 'clipboard'][$i] ?? 'link'; @endphp
                            <div>
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-lime-400/10 text-lime-300">
                                    <x-sidebar-icon :name="$icon" class="h-5 w-5" />
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
                        <h2 class="text-3xl font-bold">One dashboard for every balance</h2>
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
                        <p class="inline-flex border border-lime-400/40 bg-lime-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-lime-300">Payment links &amp; QR codes</p>
                        <h2 class="mt-4 text-3xl font-bold text-white">Share a link or QR code, get paid instantly</h2>
                        <p class="mt-3 text-slate-300">Every payment link comes with a scannable QR code and a copyable checkout URL — no integration work required to start collecting.</p>
                        <a href="{{ route('business.register') }}" class="mt-6 inline-flex rounded-xl border border-white/15 px-5 py-3 font-semibold text-white hover:border-white/30 hover:bg-white/5">Get started</a>
                    </div>
                    <div class="order-1 flex justify-center lg:order-2">
                        <div class="rounded-3xl bg-slate-800/60 p-6 ring-1 ring-white/10">
                            <div class="mx-auto grid h-40 w-40 grid-cols-5 gap-1 rounded-2xl bg-white p-3">
                                @for ($i = 0; $i < 25; $i++)
                                    <span class="{{ in_array($i, [0, 1, 3, 4, 5, 9, 10, 14, 15, 19, 20, 21, 23, 24, 12, 7, 17, 2, 22]) ? 'bg-slate-950' : 'bg-white' }} rounded-[2px]"></span>
                                @endfor
                            </div>
                            <p class="mt-4 text-center text-sm text-slate-400">app.sentepro.io/pay/…</p>
                        </div>
                    </div>
                </div>
            </section>

            <div class="mx-auto max-w-6xl space-y-20 px-4 py-20 sm:px-6 lg:px-8">
                {{-- How it works --}}
                <section id="how-it-works" class="grid gap-10 lg:grid-cols-2 lg:items-center">
                    <div class="rounded-3xl bg-slate-900 p-6 ring-1 ring-white/10">
                        <p class="text-sm font-semibold text-white">Create your SentePro account</p>
                        <div class="mt-4 space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-slate-500">First name</div>
                                <div class="rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-slate-500">Last name</div>
                            </div>
                            <div class="rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-slate-500">Business name</div>
                            <div class="rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-slate-500">Email address</div>
                            <div class="rounded-xl bg-lime-400 px-3 py-2 text-center text-sm font-semibold text-slate-950">Register</div>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold">It's simple to start using SentePro</h2>
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
                    <h2 class="text-3xl font-bold">Supported payment ecosystem</h2>
                    <p class="mt-2 text-slate-300">Pesapal for cards, Yo Payments for mobile money.</p>
                    <div class="mt-6 rounded-2xl border border-white/10 bg-white/5 p-6">
                        <x-payment-method-logos />
                    </div>
                </section>

                {{-- FAQ --}}
                <section id="faq" class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
                    <div>
                        <h2 class="text-3xl font-bold">Common questions</h2>
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
                    <h2 class="text-3xl font-bold text-white sm:text-4xl">{{ $content->cta_banner_heading }}</h2>
                    <p class="max-w-xl text-slate-300">{{ $content->cta_banner_subtext }}</p>
                    <a href="{{ route('business.register') }}" class="rounded-xl bg-lime-400 px-6 py-3 font-semibold text-slate-950 hover:bg-lime-300">Register now</a>
                </div>
            </section>

            {{-- Footer --}}
            <footer class="border-t border-white/10 py-12">
                <div class="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-[1.2fr_1fr_1fr] lg:px-8">
                    <div>
                        <x-brand-mark class="text-2xl" />
                        <p class="mt-3 max-w-xs text-sm text-slate-400">Payment collection infrastructure for East African businesses.</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Product</p>
                        <ul class="mt-4 space-y-2 text-sm text-slate-300">
                            <li><a href="#features" class="hover:text-white">Features</a></li>
                            <li><a href="#gateways" class="hover:text-white">Gateways</a></li>
                            <li><a href="#faq" class="hover:text-white">FAQ</a></li>
                        </ul>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Company</p>
                        <ul class="mt-4 space-y-2 text-sm text-slate-300">
                            <li><a href="/login" class="hover:text-white">Login</a></li>
                            <li><a href="{{ route('business.register') }}" class="hover:text-white">Register your business</a></li>
                        </ul>
                    </div>
                </div>
                <div class="mx-auto mt-10 max-w-6xl border-t border-white/10 px-4 pt-6 text-sm text-slate-500 sm:px-6 lg:px-8">
                    &copy; {{ date('Y') }} SentePro. All rights reserved.
                </div>
            </footer>
        </div>
    </body>
</html>
