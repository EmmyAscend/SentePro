<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentLinkRequest;
use App\Http\Resources\PaymentLinkResource;
use App\Models\PaymentLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentLinkController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', PaymentLink::class);

        return PaymentLinkResource::collection(PaymentLink::query()->latest()->get());
    }

    public function show(PaymentLink $paymentLink): PaymentLinkResource
    {
        $this->authorize('view', $paymentLink);

        return new PaymentLinkResource($paymentLink);
    }

    public function store(StorePaymentLinkRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $paymentLink = PaymentLink::create([
            'business_id' => $request->user()->business_id,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'custom_amount' => $validated['custom_amount'],
            'expiry_date' => $validated['expiry_date'],
            'description' => $validated['description'] ?? null,
            'fields' => PaymentLink::buildFieldsFromInput($validated['standard_fields'] ?? null, $validated['custom_field_labels'] ?? null),
            'status' => 'active',
        ]);

        return (new PaymentLinkResource($paymentLink))->response()->setStatusCode(201);
    }
}
