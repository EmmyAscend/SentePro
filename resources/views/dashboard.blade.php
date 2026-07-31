<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('SentePro Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-500">Registered Businesses</p>
                    <p class="text-3xl font-semibold text-gray-900">{{ $businessCount }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-500">Payments</p>
                    <p class="text-3xl font-semibold text-gray-900">Coming soon</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-500">Staff Access</p>
                    <p class="text-3xl font-semibold text-gray-900">Role-based</p>
                </div>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Recent business onboarding</h3>
                    @if ($latestBusinesses->isEmpty())
                        <p>No business applications recorded yet.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach ($latestBusinesses as $business)
                                <li class="border-b pb-2">
                                    <strong>{{ $business->business_name }}</strong> — {{ $business->country }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
