<footer class="border-t border-white/10 py-12">
    <div class="mx-auto grid max-w-6xl grid-cols-2 gap-x-6 gap-y-10 px-4 sm:px-6 lg:grid-cols-[1.2fr_1fr_1fr_1fr] lg:gap-10 lg:px-8">
        <div class="col-span-2 lg:col-span-1">
            <x-brand-mark class="text-2xl" />
            <p class="mt-3 max-w-xs text-sm text-slate-400">Payment collection infrastructure for East African businesses.</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Product</p>
            <ul class="mt-4 space-y-2 text-sm text-slate-300">
                <li><a href="/#requirements" class="hover:text-white">Requirements</a></li>
                <li><a href="/#features" class="hover:text-white">Features</a></li>
                <li><a href="/#gateways" class="hover:text-white">Supported Payments</a></li>
                <li><a href="/#faq" class="hover:text-white">FAQ</a></li>
            </ul>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Company</p>
            <ul class="mt-4 space-y-2 text-sm text-slate-300">
                <li><a href="/login" class="hover:text-white">Login</a></li>
                <li><a href="{{ route('business.register') }}" class="hover:text-white">Register your business</a></li>
            </ul>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Legal</p>
            <ul class="mt-4 space-y-2 text-sm text-slate-300">
                <li><a href="{{ route('legal.show', 'privacy-policy') }}" class="hover:text-white">Privacy Policy</a></li>
                <li><a href="{{ route('legal.show', 'terms-and-conditions') }}" class="hover:text-white">Terms and Conditions</a></li>
                <li><a href="{{ route('legal.show', 'refund-policy') }}" class="hover:text-white">Refund Policy</a></li>
            </ul>
        </div>
    </div>
    <div class="mx-auto mt-10 max-w-6xl border-t border-white/10 px-4 pt-6 text-sm text-slate-500 sm:px-6 lg:px-8">
        &copy; {{ date('Y') }} SentePro. All rights reserved.
    </div>
</footer>
