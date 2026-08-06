<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white">Payment Links</h2>
                <p class="text-sm text-slate-400">Create shareable payment collection links for your business</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Create payment link</h3>
                    <form method="POST" action="{{ route('payment-links.store') }}" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Business</label>
                            <select name="business_id" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white">
                                @foreach ($businesses as $business)
                                    <option value="{{ $business->id }}">{{ $business->business_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Title</label>
                            <input type="text" name="title" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Type</label>
                            <select name="type" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white">
                                <option value="donation">Donation</option>
                                <option value="invoice">Invoice</option>
                                <option value="product">Product</option>
                                <option value="service">Service</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-[1fr_auto] gap-2">
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Amount</label>
                                <input type="number" name="amount" step="0.01" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Currency</label>
                                <select name="currency" class="mt-1 rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white">
                                    <option value="UGX">UGX</option>
                                    <option value="KES">KES</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Custom amount</label>
                            <select name="custom_amount" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Expiry date</label>
                            <input type="date" name="expiry_date" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Description</label>
                            <textarea name="description" rows="4" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Extra checkout fields</label>
                            <p class="text-xs text-slate-400">Ask the customer for anything beyond name, email, and phone.</p>
                            <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                                @foreach (\App\Models\PaymentLink::STANDARD_FIELDS as $key => $label)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="standard_fields[]" value="{{ $key }}" class="rounded border-white/10 bg-slate-950 text-white">
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Custom field labels</label>
                            <p class="text-xs text-slate-400">One per line, e.g. "T-Shirt Size" — up to 10.</p>
                            <textarea name="custom_field_labels" rows="3" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white"></textarea>
                        </div>

                        <button type="submit" class="rounded-xl bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">Create link</button>
                    </form>
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Existing payment links</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($paymentLinks as $link)
                            <div x-data="{
                                    open: false,
                                    style: 'pill',
                                    theme: 'dark',
                                    size: 'medium',
                                    checkoutUrl: '{{ route('checkout.show', $link) }}',
                                    scriptUrl: '{{ url('/sentepro-checkout.js') }}',
                                    title: {{ \Illuminate\Support\Js::from($link->title) }},
                                    widgetMode: 'popup',
                                    sizes: { small: 'padding:8px 16px;font-size:12px;', medium: 'padding:12px 24px;font-size:14px;', large: 'padding:16px 32px;font-size:16px;' },
                                    themes: { dark: 'background:#0f172a;color:#ffffff;', light: 'background:#ffffff;color:#0f172a;border:1px solid #0f172a;', brand: 'background:#059669;color:#ffffff;' },
                                    shapes: { pill: 'border-radius:9999px;', rounded: 'border-radius:8px;', square: 'border-radius:0;' },
                                    buttonStyle() {
                                        return 'display:inline-block;font-family:sans-serif;font-weight:600;text-decoration:none;' + this.sizes[this.size] + this.themes[this.theme] + this.shapes[this.style];
                                    },
                                    buttonHtml() {
                                        return '<a href=\'' + this.checkoutUrl + '\' style=\'' + this.buttonStyle() + '\'>Pay ' + this.title + '</a>';
                                    },
                                    widgetSnippet() {
                                        return '<script src=\'' + this.scriptUrl + '\' defer></script>\n<button data-sentepro-checkout data-checkout-url=\'' + this.checkoutUrl + '\' data-mode=\'' + this.widgetMode + '\'>Pay ' + this.title + '</button>';
                                    },
                                }" class="rounded-xl bg-slate-800/60 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-white">{{ $link->title }}</p>
                                        <p class="text-sm text-slate-400">{{ $link->business->business_name }} • {{ ucfirst($link->type) }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-300">{{ strtoupper($link->status) }}</span>
                                        <button type="button" @click="open = !open" class="rounded-full border border-white/10 px-3 py-1 text-xs font-semibold text-slate-300 hover:bg-white/5">
                                            Share
                                        </button>
                                    </div>
                                </div>

                                <div x-show="open" x-cloak class="mt-4 space-y-4 border-t border-white/10 pt-4">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
                                        <img src="{{ route('checkout.qr-code', $link) }}" alt="Scan to pay {{ $link->title }}" width="128" height="128" class="h-32 w-32 shrink-0 rounded-lg bg-white p-1 ring-1 ring-white/10">

                                        <div class="flex-1 space-y-3">
                                            <div>
                                                <label class="text-xs font-medium text-slate-400">Payment link</label>
                                                <div class="mt-1 flex gap-2">
                                                    <input type="text" readonly value="{{ route('checkout.show', $link) }}" class="flex-1 rounded-xl border border-white/10 bg-slate-950 px-3 py-1.5 text-sm text-white" onclick="this.select()">
                                                    <button type="button" class="rounded-xl border border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-white/5" onclick="navigator.clipboard.writeText(this.previousElementSibling.value); this.textContent='Copied!'">Copy</button>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="text-xs font-medium text-slate-400">Payment button (HTML embed)</label>
                                                <div class="mt-1 grid grid-cols-3 gap-2">
                                                    <select x-model="size" class="rounded-xl border border-white/10 bg-slate-950 px-2 py-1 text-xs text-white">
                                                        <option value="small">Small</option>
                                                        <option value="medium">Medium</option>
                                                        <option value="large">Large</option>
                                                    </select>
                                                    <select x-model="theme" class="rounded-xl border border-white/10 bg-slate-950 px-2 py-1 text-xs text-white">
                                                        <option value="dark">Dark</option>
                                                        <option value="light">Light</option>
                                                        <option value="brand">Brand</option>
                                                    </select>
                                                    <select x-model="style" class="rounded-xl border border-white/10 bg-slate-950 px-2 py-1 text-xs text-white">
                                                        <option value="pill">Pill</option>
                                                        <option value="rounded">Rounded</option>
                                                        <option value="square">Square</option>
                                                    </select>
                                                </div>

                                                <div class="mt-2 rounded-xl border border-dashed border-slate-300 bg-white p-3">
                                                    <a href="javascript:void(0)" :style="buttonStyle()" onclick="return false;">Pay <span x-text="title"></span></a>
                                                </div>

                                                <div class="mt-2 flex gap-2">
                                                    <textarea readonly rows="2" :value="buttonHtml()" class="flex-1 rounded-xl border border-white/10 bg-slate-950 px-3 py-1.5 font-mono text-xs text-white" onclick="this.select()"><a href="{{ route('checkout.show', $link) }}" style="display:inline-block;padding:12px 24px;background:#0f172a;color:#ffffff;font-family:sans-serif;font-weight:600;border-radius:9999px;text-decoration:none;">Pay {{ $link->title }}</a></textarea>
                                                    <button type="button" class="self-start rounded-xl border border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-white/5" onclick="navigator.clipboard.writeText(this.previousElementSibling.value); this.textContent='Copied!'">Copy</button>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="text-xs font-medium text-slate-400">Embedded/popup checkout widget</label>
                                                <select x-model="widgetMode" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-2 py-1 text-xs text-white">
                                                    <option value="popup">Popup window</option>
                                                    <option value="modal">In-page modal</option>
                                                </select>
                                                <div class="mt-2 flex gap-2">
                                                    <textarea readonly rows="3" :value="widgetSnippet()" class="flex-1 rounded-xl border border-white/10 bg-slate-950 px-3 py-1.5 font-mono text-xs text-white" onclick="this.select()"><script src="{{ url('/sentepro-checkout.js') }}" defer></script>
<button data-sentepro-checkout data-checkout-url="{{ route('checkout.show', $link) }}" data-mode="popup">Pay {{ $link->title }}</button></textarea>
                                                    <button type="button" class="self-start rounded-xl border border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-white/5" onclick="navigator.clipboard.writeText(this.previousElementSibling.value); this.textContent='Copied!'">Copy</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-slate-400">No payment links have been created yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
