<header class="sticky top-0 z-20 border-b border-white/10 bg-slate-950/80 backdrop-blur">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <a href="/">
            <x-brand-mark class="text-2xl" />
        </a>
        <nav class="hidden gap-6 text-sm text-slate-300 md:flex">
            <a href="/#features" class="hover:text-white">Features</a>
            <a href="/#gateways" class="hover:text-white">Supported Payments</a>
            <a href="/#faq" class="hover:text-white">FAQ</a>
            <a href="/login" class="hover:text-white">Login</a>
        </nav>
        <a href="{{ route('business.register') }}" class="rounded-full bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">Start free onboarding</a>
    </div>
</header>
