<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentTransactionResource;
use App\Models\PaymentTransaction;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentTransactionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', PaymentTransaction::class);

        return PaymentTransactionResource::collection(
            PaymentTransaction::query()->latest()->paginate(25)
        );
    }

    public function show(PaymentTransaction $transaction): PaymentTransactionResource
    {
        $this->authorize('view', $transaction);

        return new PaymentTransactionResource($transaction);
    }
}
