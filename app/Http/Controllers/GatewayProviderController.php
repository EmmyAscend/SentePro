<?php

namespace App\Http\Controllers;

use App\Enums\PaymentProvider;
use App\Http\Requests\GatewayProviderRequest;
use App\Models\GatewayProvider;
use App\Services\PaymentGatewayManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GatewayProviderController extends Controller
{
    public function __construct(private readonly PaymentGatewayManager $gatewayManager) {}

    public function index(): View
    {
        $this->authorize('manage', GatewayProvider::class);

        $providers = collect(PaymentProvider::cases())
            ->map(fn (PaymentProvider $provider) => GatewayProvider::byProvider($provider));

        return view('admin.gateway-providers', compact('providers'));
    }

    public function update(GatewayProviderRequest $request, GatewayProvider $gatewayProvider): RedirectResponse
    {
        $validated = $request->validated();

        $newCredentials = $gatewayProvider->provider === PaymentProvider::YoPayments
            ? ['api_username' => $validated['api_username'], 'api_password' => $validated['api_password']]
            : ['consumer_key' => $validated['consumer_key'], 'consumer_secret' => $validated['consumer_secret']];

        $gatewayProvider->update([
            'status' => $validated['status'],
            'environment' => $validated['environment'],
            'supported_currencies' => $validated['supported_currencies'],
            // Merged, not replaced — preserves fields a driver writes back
            // on its own (e.g. PesapalDriver caching the ipn_id it gets on
            // first use) across an admin edit of just the key/secret pair.
            'credentials' => [...($gatewayProvider->credentials ?? []), ...$newCredentials],
        ]);

        return redirect()->route('admin.gateway-providers')->with('status', 'Gateway provider updated.');
    }

    public function test(GatewayProvider $gatewayProvider): RedirectResponse
    {
        $this->authorize('manage', GatewayProvider::class);

        $result = $this->gatewayManager->driver($gatewayProvider->provider)->ping($gatewayProvider);

        $status = $result['healthy']
            ? 'Connection healthy.'
            : 'Connection failed: '.($result['error'] ?? 'unknown error');

        return redirect()->route('admin.gateway-providers')->with('status', $status);
    }
}
