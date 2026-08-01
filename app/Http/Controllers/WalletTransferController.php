<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWalletTransferRequest;
use App\Models\WalletTransfer;
use App\Services\WalletTransferService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class WalletTransferController extends Controller
{
    public function __construct(private readonly WalletTransferService $walletTransferService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', WalletTransfer::class);

        $user = $request->user();

        $transfers = WalletTransfer::query()
            ->with(['senderBusiness', 'recipientBusiness'])
            ->when(! $user->isSuperAdmin(), fn ($query) => $query->where(function ($query) use ($user) {
                $query->where('sender_business_id', $user->business_id)
                    ->orWhere('recipient_business_id', $user->business_id);
            }))
            ->latest()
            ->get();

        return view('wallet-transfers.index', compact('transfers'));
    }

    public function store(StoreWalletTransferRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $recipient = $this->walletTransferService->resolveRecipient($validated['recipient']);

        $this->walletTransferService->transfer(
            $request->user()->business,
            $recipient,
            (float) $validated['amount'],
            $validated['note'] ?? null,
            $request->user(),
        );

        return redirect()->route('wallet-transfers.index')->with('status', 'Transfer sent successfully.');
    }

    /**
     * Renders this business's "receive money" QR code — a business scans it
     * (already logged into their own SentePro session) and lands on their own
     * transfer form with the recipient pre-filled. Authenticated, not public,
     * unlike the receipt/checkout QR codes — only a logged-in business user
     * can act on the pre-filled form it points to.
     */
    public function receiveQrCode(Request $request): Response
    {
        $result = Builder::create()
            ->writer(new SvgWriter)
            ->data(route('wallet-transfers.index', ['recipient' => $request->user()->business_id]))
            ->size(240)
            ->margin(8)
            ->build();

        return response($result->getString(), 200, [
            'Content-Type' => $result->getMimeType(),
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
