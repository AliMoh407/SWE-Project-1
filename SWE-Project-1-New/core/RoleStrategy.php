<?php

/**
 * Role Strategy Interface
 * Defines the contract for role-based behavior strategies
 */
interface RoleStrategy
{
    /**
     * Check if user can access a resource
     */
    public function canAccess(string $resource): bool;
    
    /**
     * Get allowed actions for this role
     */
    public function getAllowedActions(): array;
    
    /**
     * Get role name
     */
    public function getRoleName(): string;
}

/**
 * Admin Role Strategy
 */
class AdminRoleStrategy implements RoleStrategy
{
    public function canAccess(string $resource): bool
    {
        // Admin can access everything
        return true;
    }
    
    public function getAllowedActions(): array
    {
        return [
            'view_users',
            'create_user',
            'edit_user',
            'delete_user',
            'view_reports',
            'view_activity_logs',
            'manage_inventory',
            'approve_requests',
            'view_notifications'
        ];
    }
    
    public function getRoleName(): string
    {
        return ROLE_ADMIN;
    }
}

/**
 * Doctor Role Strategy
 */
class DoctorRoleStrategy implements RoleStrategy
{
    public function canAccess(string $resource): bool
    {
        $allowedResources = [
            'dashboard',
            'request_items',
            'view_request_history',
            'view_notifications'
        ];
        
        return in_array($resource, $allowedResources);
    }
    
    public function getAllowedActions(): array
    {
        return [
            'view_dashboard',
            'request_items',
            'view_request_history',
            'view_notifications'
        ];
    }
    
    public function getRoleName(): string
    {
        return ROLE_DOCTOR;
    }
}

/**
 * Pharmacist Role Strategy
 */
class PharmacistRoleStrategy implements RoleStrategy
{
    public function canAccess(string $resource): bool
    {
        $allowedResources = [
            'dashboard',
            'manage_inventory',
            'view_notifications',
            'approve_requests'
        ];
        
        return in_array($resource, $allowedResources);
    }
    
    public function getAllowedActions(): array
    {
        return [
            'view_dashboard',
            'manage_inventory',
            'add_inventory',
            'edit_inventory',
            'adjust_stock',
            'approve_requests',
            'reject_requests',
            'view_notifications'
        ];
    }
    
    public function getRoleName(): string
    {
        return ROLE_PHARMACIST;
    }
}

/**
 * Role Strategy Factory
 * Creates the appropriate strategy based on role
 */
class RoleStrategyFactory
{
    public static function create(string $role): RoleStrategy
    {
        return match($role) {
            ROLE_ADMIN => new AdminRoleStrategy(),
            ROLE_DOCTOR => new DoctorRoleStrategy(),
            ROLE_PHARMACIST => new PharmacistRoleStrategy(),
            default => throw new Exception("Unknown role: {$role}")
        };
    }
    
    /**
     * Get strategy for current logged-in user
     */
    public static function createForCurrentUser(): ?RoleStrategy
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        global $userModel;
        if (!isset($userModel)) {
            return null;
        }
        
        $user = $userModel->findById($_SESSION['user_id']);
        if (!$user || !isset($user['role'])) {
            return null;
        }
        
        return self::create($user['role']);
    }
}

