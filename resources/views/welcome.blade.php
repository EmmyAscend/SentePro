<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SentePro | Collect Payments. Settle Faster. Grow Your Business.</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-950 text-white">
        <div class="min-h-screen">
            <header class="sticky top-0 z-20 border-b border-white/10 bg-slate-950/80 backdrop-blur">
                <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <div class="text-xl font-semibold tracking-tight">SentePro</div>
                    <nav class="hidden gap-6 text-sm text-slate-300 md:flex">
                        <a href="#features" class="hover:text-white">Features</a>
                        <a href="#gateways" class="hover:text-white">Gateways</a>
                        <a href="#pricing" class="hover:text-white">Pricing</a>
                        <a href="/login" class="hover:text-white">Login</a>
                    </nav>
                    <a href="{{ route('business.register') }}" class="rounded-full bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">Start free onboarding</a>
                </div>
            </header>

            <main class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
                <section class="grid items-center gap-10 lg:grid-cols-[1.15fr_0.85fr] lg:py-10">
                    <div>
                        <p class="mb-4 inline-flex rounded-full border border-lime-400/40 bg-lime-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.25em] text-lime-300">East Africa payment infrastructure</p>
                        <h1 class="max-w-2xl text-4xl font-black tracking-tight text-white sm:text-5xl lg:text-6xl">{{ $headline }}</h1>
                        <p class="mt-5 max-w-2xl text-lg text-slate-300">Launch modern payment collection for your business without owning a gateway. SentePro gives you a secure collection layer, verified onboarding, and settlement-ready workflows.</p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ route('business.register') }}" class="rounded-xl bg-lime-400 px-5 py-3 font-semibold text-slate-950 shadow-lg shadow-lime-500/15 hover:bg-lime-300">Register your business</a>
                            <a href="/login" class="rounded-xl border border-white/15 px-5 py-3 font-semibold text-white hover:border-white/30 hover:bg-white/5">Log in to dashboard</a>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/40">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl bg-slate-900 p-4">
                                <div class="text-sm text-slate-400">Settlement rate</div>
                                <div class="mt-2 text-3xl font-bold text-white">1.8%</div>
                            </div>
                            <div class="rounded-2xl bg-slate-900 p-4">
                                <div class="text-sm text-slate-400">Supported channels</div>
                                <div class="mt-2 text-3xl font-bold text-white">8+</div>
                            </div>
                            <div class="rounded-2xl bg-slate-900 p-4 sm:col-span-2">
                                <div class="text-sm text-slate-400">Platform snapshot</div>
                                <ul class="mt-3 space-y-2 text-sm text-slate-200">
                                    <li>• Verified onboarding workflow</li>
                                    <li>• Multi-tenant staff administration</li>
                                    <li>• Queued settlement and payment orchestration</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="features" class="mt-20">
                    <div class="mb-8 max-w-2xl">
                        <h2 class="text-3xl font-bold">Built for growth-focused businesses</h2>
                        <p class="mt-2 text-slate-300">From payments to settlement visibility, SentePro gives teams a modern workflow to collect faster and serve customers confidently.</p>
                    </div>
                    <div class="grid gap-4 md:grid-cols-3">
                        <article class="rounded-2xl border border-white/10 bg-white/5 p-5">
                            <h3 class="text-lg font-semibold">Unified payment collection</h3>
                            <p class="mt-2 text-sm text-slate-300">Collect through one marketplace-ready flow without requiring each business to maintain its own gateway.</p>
                        </article>
                        <article class="rounded-2xl border border-white/10 bg-white/5 p-5">
                            <h3 class="text-lg font-semibold">Verified business onboarding</h3>
                            <p class="mt-2 text-sm text-slate-300">Capture business, owner, and documentation details under a production-safe verification pipeline.</p>
                        </article>
                        <article class="rounded-2xl border border-white/10 bg-white/5 p-5">
                            <h3 class="text-lg font-semibold">Role-aware access</h3>
                            <p class="mt-2 text-sm text-slate-300">Super admins, business admins, and staff all operate through structured, permission-based workflows.</p>
                        </article>
                    </div>
                </section>

                <section id="how-it-works" class="mt-20">
                    <h2 class="text-3xl font-bold">How it works</h2>
                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <article class="rounded-2xl border border-white/10 bg-white/5 p-5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-lime-400/10 text-sm font-semibold text-lime-300">1</div>
                            <h3 class="mt-3 text-lg font-semibold">Register your business</h3>
                            <p class="mt-2 text-sm text-slate-300">Submit your business details and get verified through our onboarding pipeline.</p>
                        </article>
                        <article class="rounded-2xl border border-white/10 bg-white/5 p-5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-lime-400/10 text-sm font-semibold text-lime-300">2</div>
                            <h3 class="mt-3 text-lg font-semibold">Connect a gateway</h3>
                            <p class="mt-2 text-sm text-slate-300">Enable Pesapal for card collections and Yo Payments for MTN/Airtel mobile money.</p>
                        </article>
                        <article class="rounded-2xl border border-white/10 bg-white/5 p-5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-lime-400/10 text-sm font-semibold text-lime-300">3</div>
                            <h3 class="mt-3 text-lg font-semibold">Collect and settle</h3>
                            <p class="mt-2 text-sm text-slate-300">Share payment links and QR codes, then request settlements straight to your bank or mobile wallet.</p>
                        </article>
                    </div>
                </section>

                <section id="gateways" class="mt-20">
                    <h2 class="text-3xl font-bold">Supported payment ecosystem</h2>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-center text-sm font-medium text-slate-200">Pesapal — cards</div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-center text-sm font-medium text-slate-200">Yo Payments — MTN &amp; Airtel mobile money</div>
                    </div>
                </section>

                <section id="pricing" class="mt-20">
                    <div class="grid gap-4 md:grid-cols-3">
                        <article class="rounded-2xl border border-white/10 bg-white/5 p-6">
                            <div class="text-sm text-slate-300">Starter</div>
                            <div class="mt-3 text-4xl font-bold">$0</div>
                            <p class="mt-4 text-sm text-slate-300">For early pilots and business onboarding evaluation.</p>
                        </article>
                        <article class="rounded-2xl border border-lime-400/40 bg-lime-400/10 p-6">
                            <div class="text-sm text-lime-200">Growth</div>
                            <div class="mt-3 text-4xl font-bold">$79</div>
                            <p class="mt-4 text-sm text-lime-50">For payment collection teams that need richer dashboards and multi-user workflows.</p>
                        </article>
                        <article class="rounded-2xl border border-white/10 bg-white/5 p-6">
                            <div class="text-sm text-slate-300">Enterprise</div>
                            <div class="mt-3 text-4xl font-bold">Custom</div>
                            <p class="mt-4 text-sm text-slate-300">For larger orgs with custom settlement, compliance, and support requirements.</p>
                        </article>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
