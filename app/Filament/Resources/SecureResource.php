<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource as BaseResource;
use Illuminate\Database\Eloquent\Model;

abstract class SecureResource extends BaseResource
{
    /**
     * The permission prefix for this resource
     * Override this in child classes
     */
    protected static ?string $permissionPrefix = null;

    /**
     * Determine if the navigation item should be visible
     */
    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    /**
     * Check if user can view any records
     */
    public static function canViewAny(): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Super Admin bypass
        if ($user->is_super_admin) {
            return true;
        }

        // Check specific permission
        if (static::$permissionPrefix) {
            return $user->can(static::$permissionPrefix . '.view');
        }

        return true; // Default to allow if no permission prefix set
    }

    /**
     * Check if user can create new records
     */
    public static function canCreate(): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        if ($user->is_super_admin) {
            return true;
        }

        if (static::$permissionPrefix) {
            return $user->can(static::$permissionPrefix . '.create');
        }

        return true;
    }

    /**
     * Check if user can edit a record
     */
    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        if ($user->is_super_admin) {
            return true;
        }

        if (static::$permissionPrefix) {
            return $user->can(static::$permissionPrefix . '.edit');
        }

        return true;
    }

    /**
     * Check if user can delete a record
     */
    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        if ($user->is_super_admin) {
            return true;
        }

        if (static::$permissionPrefix) {
            return $user->can(static::$permissionPrefix . '.delete');
        }

        return true;
    }

    /**
     * Check if user can delete any records
     */
    public static function canDeleteAny(): bool
    {
        return static::canDelete(new (static::getModel()));
    }
}
