<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    public function record(string $action, ?Model $subject = null, array $changes = []): AuditLog
    {
        /** @var User|null $actor */
        $actor = Auth::user();

        return AuditLog::create([
            'user_id' => $actor?->id,
            'business_id' => $actor?->business_id,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'changes' => $changes,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
