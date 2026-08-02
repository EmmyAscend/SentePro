@php $content = \App\Models\LandingPageContent::current(); @endphp

<footer class="border-t border-white/10 py-12">
    <div class="mx-auto grid max-w-6xl grid-cols-2 gap-x-6 gap-y-10 px-4 sm:px-6 lg:grid-cols-[1.2fr_1fr_1fr_1fr_1fr] lg:gap-10 lg:px-8">
        <div class="col-span-2 lg:col-span-1">
            <x-brand-mark class="text-2xl" />
            <p class="mt-3 max-w-xs text-xs text-slate-400">{{ $content->footer_tagline }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Product</p>
            <ul class="mt-4 space-y-2 text-xs text-slate-300">
                <li><a href="/#requirements" class="hover:text-white">Requirements</a></li>
                <li><a href="/#features" class="hover:text-white">Features</a></li>
                <li><a href="/#gateways" class="hover:text-white">Supported Payments</a></li>
                <li><a href="/#faq" class="hover:text-white">FAQ</a></li>
            </ul>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Company</p>
            <ul class="mt-4 space-y-2 text-xs text-slate-300">
                <li><a href="/login" class="hover:text-white">Login</a></li>
                <li><a href="{{ route('business.register') }}" class="hover:text-white">Register your business</a></li>
            </ul>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Legal</p>
            <ul class="mt-4 space-y-2 text-xs text-slate-300">
                <li><a href="{{ route('legal.show', 'privacy-policy') }}" class="hover:text-white">Privacy Policy</a></li>
                <li><a href="{{ route('legal.show', 'terms-and-conditions') }}" class="hover:text-white">Terms and Conditions</a></li>
                <li><a href="{{ route('legal.show', 'refund-policy') }}" class="hover:text-white">Refund Policy</a></li>
            </ul>
        </div>
        @if ($content->contact_location || $content->contact_phone)
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Contact</p>
                <ul class="mt-4 space-y-2 text-xs text-slate-300">
                    @if ($content->contact_location)
                        <li>{{ $content->contact_location }}</li>
                    @endif
                    @if ($content->contact_phone)
                        <li><a href="tel:{{ preg_replace('/[^0-9+]/', '', $content->contact_phone) }}" class="hover:text-white">{{ $content->contact_phone }}</a></li>
                    @endif
                </ul>
            </div>
        @endif
    </div>
    <div class="mx-auto mt-10 flex max-w-6xl flex-col border-t border-white/10 px-4 pt-6 text-xs text-slate-500 sm:flex-row sm:items-center sm:gap-1 sm:px-6 lg:px-8">
        <span>Powered by <a href="https://razertechnology.com" target="_blank" rel="noopener noreferrer" class="font-medium text-slate-400 hover:text-white">RAZERTECH</a> &middot;</span>
        <span>&copy; {{ date('Y') }} SentePro. All rights reserved.</span>
    </div>
</footer>
