<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-center gap-4']) }}>
    <div class="flex h-10 items-center rounded-lg bg-white px-4" title="Visa">
        <span class="text-lg font-black italic tracking-tight" style="color:#1A1F71">VISA</span>
    </div>
    <div class="flex h-10 items-center gap-2 rounded-lg bg-white px-3" title="Mastercard">
        <span class="relative flex h-5 w-8 shrink-0 items-center">
            <span class="absolute left-0 h-5 w-5 rounded-full" style="background:#EB001B"></span>
            <span class="absolute right-0 h-5 w-5 rounded-full opacity-80" style="background:#F79E1B"></span>
        </span>
        <span class="text-xs font-bold text-slate-800">Mastercard</span>
    </div>
    <div class="flex h-10 items-center rounded-lg px-4" style="background:#FFCB05" title="MTN Mobile Money">
        <span class="text-sm font-black tracking-wide text-black">MTN</span>
    </div>
    <div class="flex h-10 items-center rounded-lg px-4" style="background:#ED1C24" title="Airtel Money">
        <span class="text-sm font-black tracking-wide text-white">airtel</span>
    </div>
</div>
