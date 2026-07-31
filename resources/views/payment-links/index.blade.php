<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Payment Links</h2>
                <p class="text-sm text-slate-500">Create shareable payment collection links for your business</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Create payment link</h3>
                    <form method="POST" action="{{ route('payment-links.store') }}" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Business</label>
                            <select name="business_id" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
                                @foreach ($paymentLinks as $link)
                                    <option value="{{ $link->business_id }}">{{ $link->business->business_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Title</label>
                            <input type="text" name="title" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Type</label>
                            <select name="type" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
                                <option value="donation">Donation</option>
                                <option value="invoice">Invoice</option>
                                <option value="product">Product</option>
                                <option value="service">Service</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Amount</label>
                            <input type="number" name="amount" step="0.01" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Custom amount</label>
                            <select name="custom_amount" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Expiry date</label>
                            <input type="date" name="expiry_date" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Description</label>
                            <textarea name="description" rows="4" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"></textarea>
                        </div>

                        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Create link</button>
                    </form>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Existing payment links</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($paymentLinks as $link)
                            <div class="rounded-xl bg-slate-50 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $link->title }}</p>
                                        <p class="text-sm text-slate-500">{{ $link->business->business_name }} • {{ ucfirst($link->type) }}</p>
                                    </div>
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ strtoupper($link->status) }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-slate-600">No payment links have been created yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
