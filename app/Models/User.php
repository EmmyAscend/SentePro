<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'staff_role',
        'permissions',
        'business_id',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'permissions' => 'array',
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isBusinessAdmin(): bool
    {
        return $this->role === UserRole::BusinessAdmin;
    }

    public function isStaff(): bool
    {
        return $this->role === UserRole::Staff;
    }

    /**
     * Whether this user is allowed to perform the given ability within their own business.
     * Super admins and business admins implicitly hold every ability; staff need it explicitly granted.
     */
    public function hasPermission(string $ability): bool
    {
        if ($this->isSuperAdmin() || $this->isBusinessAdmin()) {
            return true;
        }

        return in_array($ability, $this->permissions ?? [], true);
    }
}
