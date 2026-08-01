<?php

namespace App\Http\Controllers;

use App\Enums\PaymentProvider;
use App\Http\Requests\StoreGatewayProviderRequest;
use App\Models\GatewayProvider;
use App\Services\PaymentGatewayManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GatewayProviderController extends Controller
{
    public function __construct(private readonly PaymentGatewayManager $gatewayManager) {}

    public function index(): View
    {
        $this->authorize('viewAny', GatewayProvider::class);

        $providers = GatewayProvider::with('business')->latest()->get();

        return view('gateways.index', compact('providers'));
    }

    public function store(StoreGatewayProviderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $businessId = $request->user()->isSuperAdmin()
            ? $validated['business_id']
            : $request->user()->business_id;

        $provider = GatewayProvider::create([
            ...$validated,
            'business_id' => $businessId,
            'credentials' => json_decode($validated['credentials'], true),
            'webhook_url' => 'pending', // filled in immediately below, once the record has an id
        ]);

        // webhook_url is our own receiving URL, not something the business
        // types in — it needs the record's id, so it's computed post-create.
        $provider->update([
            'webhook_url' => $provider->provider === PaymentProvider::YoPayments
                ? route('webhooks.yo-payments.success', $provider)
                : route('webhooks.pesapal.receive', $provider),
        ]);

        return redirect()->route('gateways.index')->with('status', 'Gateway provider configured successfully.');
    }

    public function test(GatewayProvider $gatewayProvider): RedirectResponse
    {
        $this->authorize('update', $gatewayProvider);

        $result = $this->gatewayManager->driver($gatewayProvider->provider)->ping($gatewayProvider);

        $status = $result['healthy']
            ? 'Connection healthy.'
            : 'Connection failed: '.($result['error'] ?? 'unknown error');

        return redirect()->route('gateways.index')->with('status', $status);
    }
}
