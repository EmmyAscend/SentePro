<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWebhookEventRequest;
use App\Models\PaymentTransaction;
use App\Models\WebhookEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WebhookController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', WebhookEvent::class);

        $events = WebhookEvent::with('business')->latest()->get();

        return view('webhooks.index', compact('events'));
    }

    public function store(StoreWebhookEventRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $businessId = $request->user()->isSuperAdmin()
            ? $validated['business_id']
            : $request->user()->business_id;

        $payload = json_decode($validated['payload'], true);

        WebhookEvent::create([
            'business_id' => $businessId,
            'provider' => $validated['provider'],
            'event' => $validated['event'],
            'payload' => $validated['payload'],
        ]);

        if (isset($payload['external_reference'])) {
            PaymentTransaction::query()
                ->where('business_id', $businessId)
                ->where('provider', $validated['provider'])
                ->where('external_reference', $payload['external_reference'])
                ->update([
                    'status' => $this->normalizeStatus($payload['status'] ?? $validated['event']),
                ]);
        }

        return redirect()->route('webhooks.index')->with('status', 'Webhook event captured successfully.');
    }

    protected function normalizeStatus(string $status): string
    {
        return match ($status) {
            'completed', 'success', 'paid' => 'completed',
            'failed', 'cancelled', 'canceled' => 'failed',
            default => 'processing',
        };
    }
}
