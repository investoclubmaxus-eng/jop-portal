<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_RECRUITER = 'recruiter';
    public const ROLE_HR = 'hr_manager';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'last_login_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user account is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Check if user has administrative privileges.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN], true);
    }

    /**
     * Check if user matches any of the given roles.
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles, true);
        }

        return $this->role === $roles;
    }

    /**
     * Role and permission mapping.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $permissionsByRole = [
            self::ROLE_ADMIN => [
                'view_dashboard',
                'manage_jobs',
                'manage_applications',
                'view_admins',
                'manage_admins',
                'view_reports',
            ],
            self::ROLE_RECRUITER => [
                'view_dashboard',
                'manage_jobs',
                'manage_applications',
            ],
            self::ROLE_HR => [
                'view_dashboard',
                'manage_applications',
            ],
        ];

        $allowed = $permissionsByRole[$this->role] ?? [];
        return in_array($permission, $allowed, true);
    }

    /**
     * Record login timestamp.
     */
    public function recordLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Scopes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    public function scopeRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    /**
     * Accessor for role display label.
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_RECRUITER => 'Recruiter',
            self::ROLE_HR => 'HR Manager',
            default => ucfirst(str_replace('_', ' ', $this->role ?? 'Admin')),
        };
    }

    /**
     * Relationships
     */
    public function createdJobOpenings(): HasMany
    {
        return $this->hasMany(JobOpening::class, 'created_by');
    }

    public function applicationStatusChanges(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class, 'changed_by');
    }
}
