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

        $gatewayProvider->update([
            ...$validated,
            'credentials' => json_decode($validated['credentials'], true),
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
