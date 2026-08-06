<?php

namespace App\Http\Controllers;

use App\Models\GatewayLog;
use App\Models\GatewayProvider;
use Illuminate\View\View;

class GatewayMonitoringController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage', GatewayProvider::class);

        $providers = GatewayProvider::query()->get();

        // Capped rather than paginated — same "200 most-recent rows is
        // plenty for a v1 log viewer" precedent as the audit log viewer.
        $logs = GatewayLog::query()
            ->with('gatewayProvider')
            ->latest()
            ->take(200)
            ->get();

        return view('admin.gateway-monitoring', compact('providers', 'logs'));
    }
}
