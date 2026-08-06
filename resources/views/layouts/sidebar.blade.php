@php
$navGroups = [
    [
        'label' => null,
        'links' => [
            ['route' => 'dashboard', 'pattern' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
        ],
    ],
    [
        'label' => 'Money',
        'links' => [
            ['route' => 'settlements.index', 'pattern' => 'settlements.*', 'label' => 'Settlements', 'icon' => 'banknotes'],
            ['route' => 'payment-links.index', 'pattern' => 'payment-links.*', 'label' => 'Payment Links', 'icon' => 'link'],
            ['route' => 'transactions.index', 'pattern' => 'transactions.*', 'label' => 'Transactions', 'icon' => 'chart'],
            ['route' => 'receipts.index', 'pattern' => 'receipts.*', 'label' => 'Receipts', 'icon' => 'receipt'],
            ['route' => 'wallet-transfers.index', 'pattern' => 'wallet-transfers.*', 'label' => 'Wallet Transfers', 'icon' => 'transfer'],
        ],
    ],
    [
        'label' => 'Support',
        'links' => [
            ['route' => 'support-tickets.index', 'pattern' => 'support-tickets.*', 'label' => 'Support', 'icon' => 'chat'],
            ['route' => 'disputes.index', 'pattern' => 'disputes.*', 'label' => 'Disputes', 'icon' => 'flag'],
            ['route' => 'knowledge-base.index', 'pattern' => 'knowledge-base.*', 'label' => 'Knowledge Base', 'icon' => 'book'],
            ['route' => 'announcements.index', 'pattern' => 'announcements.*', 'label' => 'Announcements', 'icon' => 'megaphone'],
        ],
    ],
    [
        'label' => 'Webhooks',
        'links' => [
            ['route' => 'webhooks.index', 'pattern' => 'webhooks.*', 'label' => 'Webhooks', 'icon' => 'webhook'],
        ],
    ],
    [
        'label' => 'Reporting',
        'links' => [
            ['route' => 'fee-breakdowns.index', 'pattern' => 'fee-breakdowns.*', 'label' => 'Fees', 'icon' => 'clipboard'],
            ['route' => 'analytics.index', 'pattern' => 'analytics.*', 'label' => 'Analytics', 'icon' => 'chart'],
        ],
    ],
];

if (auth()->user()->isBusinessAdmin()) {
    $navGroups[4]['links'][] = ['route' => 'api-keys.index', 'pattern' => 'api-keys.*', 'label' => 'API Keys', 'icon' => 'key'];
}

if (auth()->user()->isBusinessAdmin() || auth()->user()->isSuperAdmin()) {
    $navGroups[] = [
        'label' => 'Team',
        'links' => [
            ['route' => 'admin.staff', 'pattern' => 'admin.staff', 'label' => 'Staff', 'icon' => 'users'],
        ],
    ];
}

if (auth()->user()->isSuperAdmin()) {
    $navGroups[] = [
        'label' => 'Admin',
        'links' => [
            ['route' => 'admin.settlement-methods', 'pattern' => 'admin.settlement-methods', 'label' => 'Settlement Methods', 'icon' => 'banknotes'],
            ['route' => 'admin.fee-structures', 'pattern' => 'admin.fee-structures', 'label' => 'Fee Structures', 'icon' => 'clipboard'],
            ['route' => 'admin.businesses.index', 'pattern' => 'admin.businesses.index', 'label' => 'Businesses', 'icon' => 'users'],
            ['route' => 'admin.wallet-monitoring', 'pattern' => 'admin.wallet-monitoring', 'label' => 'Wallet Monitoring', 'icon' => 'wallet'],
            ['route' => 'admin.gateway-providers', 'pattern' => 'admin.gateway-providers*', 'label' => 'Payment Gateways', 'icon' => 'server'],
            ['route' => 'admin.gateway-monitoring', 'pattern' => 'admin.gateway-monitoring', 'label' => 'Gateway Monitoring', 'icon' => 'server'],
            ['route' => 'admin.audit-logs', 'pattern' => 'admin.audit-logs', 'label' => 'Audit Logs', 'icon' => 'shield'],
            ['route' => 'admin.landing-page.edit', 'pattern' => 'admin.landing-page.*', 'label' => 'Landing Page', 'icon' => 'link'],
            ['route' => 'admin.legal-pages', 'pattern' => 'admin.legal-pages*', 'label' => 'Legal Pages', 'icon' => 'clipboard'],
        ],
    ];
}
@endphp

<div class="flex h-full flex-col">
    <div class="flex items-center gap-2 px-5 py-5">
        <a href="{{ route('dashboard') }}">
            <x-brand-mark class="text-xl" />
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 pb-4 space-y-6">
        @foreach ($navGroups as $group)
            <div>
                @if ($group['label'])
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $group['label'] }}</p>
                @endif
                <div class="mt-1 space-y-0.5">
                    @foreach ($group['links'] as $link)
                        @php $active = request()->routeIs($link['pattern']); @endphp
                        <a href="{{ route($link['route']) }}"
                           @class([
                               'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                               'bg-lime-400/10 text-lime-300' => $active,
                               'text-slate-400 hover:bg-white/5 hover:text-slate-200' => ! $active,
                           ])>
                            <x-sidebar-icon :name="$link['icon']" />
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    <div class="border-t border-white/10 px-3 py-4">
        <div class="flex items-center gap-3 rounded-lg px-2 py-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-lime-400/10 text-sm font-semibold text-lime-300">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                <p class="truncate text-xs text-slate-500">{{ Auth::user()->email }}</p>
            </div>
        </div>
    </div>
</div>
