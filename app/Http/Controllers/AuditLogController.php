<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manage', AuditLog::class);

        // Capped rather than paginated — this app has no pagination pattern
        // anywhere yet, and 200 most-recent rows is plenty for a v1 log viewer.
        $logs = AuditLog::query()
            ->with(['user', 'business'])
            ->when($request->filled('business_id'), fn ($query) => $query->where('business_id', $request->input('business_id')))
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->input('action')))
            ->latest()
            ->take(200)
            ->get();

        $businesses = Business::query()->orderBy('business_name')->get();

        $actions = AuditLog::query()->distinct()->orderBy('action')->pluck('action');

        return view('admin.audit-logs', compact('logs', 'businesses', 'actions'));
    }
}
