<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\View\View;

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
}
