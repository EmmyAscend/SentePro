{{-- Placeholder wordmarks — swap for real logo image files whenever they're available. --}}
<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-center gap-x-10 gap-y-4']) }}>
    <span class="text-2xl font-black italic tracking-tight" style="color:#1A1F71" title="Visa">VISA</span>
    <span class="flex items-center gap-2" title="Mastercard">
        <span class="relative flex h-7 w-10 shrink-0 items-center">
            <span class="absolute left-0 h-7 w-7 rounded-full" style="background:#EB001B"></span>
            <span class="absolute right-0 h-7 w-7 rounded-full opacity-80" style="background:#F79E1B"></span>
        </span>
        <span class="text-base font-bold text-white">Mastercard</span>
    </span>
    <span class="text-2xl font-black tracking-wide" style="color:#FFCB05" title="MTN Mobile Money">MTN</span>
    <span class="text-2xl font-black tracking-wide" style="color:#ED1C24" title="Airtel Money">airtel</span>
</div>
