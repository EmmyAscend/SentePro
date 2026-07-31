<?php

namespace App\Services;

use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function create(Business $business, array $data): User
    {
        $staff = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'staff_role' => $data['staff_role'] ?? null,
            'permissions' => $data['permissions'] ?? [],
            'business_id' => $business->id,
            'email_verified_at' => now(),
        ]);

        $this->auditLog->record('staff.created', $staff, ['role' => $data['role']]);

        return $staff;
    }
}
