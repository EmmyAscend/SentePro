<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceiptController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Receipt::class);

        // Receipt is tenant-scoped, so this is already limited to the current
        // user's business for anyone who isn't a super admin.
        $receipts = Receipt::with('business')->latest()->get();

        return view('receipts.index', compact('receipts'));
    }

    public function show(Receipt $receipt): View
    {
        return view('receipts.show', compact('receipt'));
    }

    public function verify(Receipt $receipt): View
    {
        return view('receipts.verify', compact('receipt'));
    }

    /**
     * Renders the receipt's verification URL as a scannable SVG QR code —
     * the URL itself is already public/working (see verify()), this just
     * makes it scannable.
     */
    public function qrCode(Receipt $receipt): Response
    {
        $result = Builder::create()
            ->writer(new SvgWriter)
            ->data(route('receipts.verify', $receipt))
            ->size(240)
            ->margin(8)
            ->build();

        return response($result->getString(), 200, [
            'Content-Type' => $result->getMimeType(),
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function export(): StreamedResponse
    {
        $this->authorize('viewAny', Receipt::class);

        $receipts = Receipt::with('business')->latest()->get();

        return response()->streamDownload(function () use ($receipts) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Reference Number', 'Business', 'Customer Name', 'Customer Email',
                'Amount', 'Net Amount', 'Currency', 'Emailed At', 'Date',
            ]);

            foreach ($receipts as $receipt) {
                fputcsv($handle, [
                    $receipt->reference_number,
                    $receipt->business->business_name,
                    $receipt->customer_name,
                    $receipt->customer_email,
                    $receipt->amount,
                    $receipt->net_amount,
                    $receipt->currency,
                    $receipt->emailed_at?->toDateTimeString(),
                    $receipt->created_at->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, 'receipts-'.now()->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv']);
    }
}
