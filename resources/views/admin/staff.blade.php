<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white">Staff Management</h2>
                <p class="text-sm text-slate-400">Create and assign staff roles within each business</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Create staff member</h3>
                    <form method="POST" action="{{ route('admin.staff.store') }}" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Name</label>
                            <input type="text" name="name" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Email</label>
                            <input type="email" name="email" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Password</label>
                            <input type="password" name="password" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                        </div>

                        @if (auth()->user()->isSuperAdmin())
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Business</label>
                                <select name="business_id" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2">
                                    @foreach ($businesses as $business)
                                        <option value="{{ $business->id }}">{{ $business->business_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Role</label>
                            <select name="role" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2">
                                <option value="business_admin">Business Admin</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Staff Title (optional)</label>
                            <input type="text" name="staff_role" placeholder="e.g. Manager, Cashier, Accountant" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2">
                        </div>

                        <button type="submit" class="rounded-xl bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">Create staff</button>
                    </form>
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Assigned staff</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($staff as $member)
                            <div class="rounded-xl bg-slate-800/60 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-white">{{ $member->name }}</p>
                                        <p class="text-sm text-slate-400">{{ $member->email }}</p>
                                    </div>
                                    <span class="rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-300">
                                        {{ $member->role->label() }}{{ $member->staff_role ? ' • '.$member->staff_role : '' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-slate-400">No staff members have been assigned yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
