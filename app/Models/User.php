<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $guarded = [];
    
    /**
     * Override fillable name attribute for Filament compatibility
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['full_name'] = $value;
    }
    
    public function getNameAttribute(): ?string
    {
        return $this->attributes['full_name'] ?? null;
    }

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
            'last_login_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'locked_until' => 'datetime',
            'password' => 'hashed',
            'hire_date' => 'date',
            'is_active' => 'boolean',
            'is_super_admin' => 'boolean',
            'is_online' => 'boolean',
            'login_attempts' => 'integer',
            'current_points' => 'decimal:2',
            'total_points_earned' => 'decimal:2',
            'total_points_deducted' => 'decimal:2',
            'streak_count' => 'integer',
            'last_latitude' => 'decimal:7',
            'last_longitude' => 'decimal:7',
            'preferences' => 'array',
            'custom_schedule' => 'array',
            'permissions' => 'array',
            'visible_modules' => 'array',
        ];
    }

    /**
     * Determine if user can access Filament panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && ($this->is_super_admin || ($this->role && $this->role->is_active));
    }

    /**
     * Get the role of this user
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the branch this user belongs to
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the manager of this user
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'managed_by');
    }

    /**
     * Get users managed by this user
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class, 'managed_by');
    }

    /**
     * Get the department this user belongs to
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the team this user belongs to
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the job title of this user
     */
    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class);
    }

    /**
     * Get the user's current active work shift
     * Returns the shift from the most recent active assignment
     */
    public function workShift()
    {
        return $this->hasOneThrough(
            WorkShift::class,
            UserShiftAssignment::class,
            'user_id',    // Foreign key on user_shift_assignments
            'id',         // Foreign key on work_shifts
            'id',         // Local key on users
            'shift_id'    // Local key on user_shift_assignments
        )->where('user_shift_assignments.effective_from', '<=', now())
         ->where(function($query) {
             $query->whereNull('user_shift_assignments.effective_until')
                   ->orWhere('user_shift_assignments.effective_until', '>=', now());
         })
         ->orderBy('user_shift_assignments.effective_from', 'desc')
         ->limit(1);
    }

    /**
     * Check if user has a specific permission
     */
    public function can($permission, $arguments = []): bool
    {
        // Super Admin bypass
        if ($this->is_super_admin) {
            return true;
        }

        $allPermissions = $this->getAllPermissions();
        return in_array($permission, $allPermissions);
    }

    /**
     * Check if user has ANY of the given permissions
     * Custom method - different from Laravel's canAny
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        $allPermissions = $this->getAllPermissions();
        
        foreach ($permissions as $permission) {
            if (in_array($permission, $allPermissions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has ALL of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        $allPermissions = $this->getAllPermissions();
        
        foreach ($permissions as $permission) {
            if (!in_array($permission, $allPermissions)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all permissions for this user (role + custom)
     */
    public function getAllPermissions(): array
    {
        $permissions = [];

        // Get role permissions
        if ($this->role) {
            $rolePermissions = $this->role->permissions;
            if (is_string($rolePermissions)) {
                $rolePermissions = json_decode($rolePermissions, true) ?? [];
            }
            $permissions = array_merge($permissions, $rolePermissions ?? []);
        }

        // Get custom user permissions
        $userPermissions = $this->permissions ?? [];
        if (is_string($userPermissions)) {
            $userPermissions = json_decode($userPermissions, true) ?? [];
        }
        $permissions = array_merge($permissions, $userPermissions ?? []);

        return array_unique($permissions);
    }

    /**
     * Check if user has minimum role level
     */
    public function hasRoleLevel(int $minLevel): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        return $this->role && $this->role->role_level >= $minLevel;
    }
}

