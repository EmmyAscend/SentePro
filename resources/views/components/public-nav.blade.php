<header class="sticky top-0 z-20 border-b border-white/10 bg-slate-950/80 backdrop-blur" x-data="{ mobileOpen: false }">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <a href="/">
            <x-brand-mark class="text-2xl" />
        </a>
        <nav class="hidden gap-6 text-xs uppercase tracking-wide text-slate-300 md:flex">
            <a href="/#requirements" class="hover:text-white">Requirements</a>
            <a href="/#features" class="hover:text-white">Features</a>
            <a href="/#gateways" class="hover:text-white">Supported Payments</a>
            <a href="/#faq" class="hover:text-white">FAQ</a>
            <a href="/login" class="hover:text-white">Login</a>
        </nav>
        <div class="flex items-center gap-3">
            <a href="{{ route('business.register') }}" class="rounded-full bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">Start free onboarding</a>
            <button type="button" class="text-slate-300 hover:text-white md:hidden" @click="mobileOpen = ! mobileOpen" aria-label="Toggle menu">
                <x-sidebar-icon name="menu" class="h-6 w-6" x-show="! mobileOpen" />
                <x-sidebar-icon name="x" class="h-6 w-6" x-show="mobileOpen" style="display: none;" />
            </button>
        </div>
    </div>

    <div x-show="mobileOpen" style="display: none;" x-transition class="border-t border-white/10 md:hidden">
        <nav class="mx-auto flex max-w-6xl flex-col gap-1 px-4 py-4 text-xs uppercase tracking-wide text-slate-300 sm:px-6">
            <a href="/#requirements" class="rounded-lg px-3 py-2 hover:bg-white/5 hover:text-white" @click="mobileOpen = false">Requirements</a>
            <a href="/#features" class="rounded-lg px-3 py-2 hover:bg-white/5 hover:text-white" @click="mobileOpen = false">Features</a>
            <a href="/#gateways" class="rounded-lg px-3 py-2 hover:bg-white/5 hover:text-white" @click="mobileOpen = false">Supported Payments</a>
            <a href="/#faq" class="rounded-lg px-3 py-2 hover:bg-white/5 hover:text-white" @click="mobileOpen = false">FAQ</a>
            <a href="/login" class="rounded-lg px-3 py-2 hover:bg-white/5 hover:text-white" @click="mobileOpen = false">Login</a>
        </nav>
    </div>
</header>
