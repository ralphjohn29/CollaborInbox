/**
 * RoleManager Service
 * 
 * Handles management of roles and permissions for the application.
 * This service provides methods for retrieving, assigning, and managing
 * user roles and their associated permissions.
 */
class RoleManager {
    constructor() {
        this.roles = null;
        this.permissions = null;
        this.currentUserRole = null;
        this.initialized = false;
    }

    /**
     * Initialize the role manager
     * Fetches all roles and permissions from the API
     * 
     * @returns {Promise<boolean>} True if initialization successful
     */
    async init() {
        if (this.initialized) {
            return true;
        }

        try {
            // Fetch all roles and their permissions
            const response = await fetch('/api/roles', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`Failed to fetch roles: ${response.status}`);
            }

            const data = await response.json();
            this.roles = data.roles;
            this.permissions = data.permissions;
            
            // Get current user's role
            const userResponse = await fetch('/api/user', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (userResponse.ok) {
                const userData = await userResponse.json();
                this.currentUserRole = userData.role;
            }

            this.initialized = true;
            return true;
        } catch (error) {
            console.error('Failed to initialize RoleManager:', error);
            return false;
        }
    }

    /**
     * Get all available roles
     * 
     * @returns {Array} List of roles
     */
    async getRoles() {
        if (!this.initialized) {
            await this.init();
        }
        return this.roles;
    }

    /**
     * Get all available permissions
     * 
     * @returns {Array} List of permissions
     */
    async getPermissions() {
        if (!this.initialized) {
            await this.init();
        }
        return this.permissions;
    }

    /**
     * Check if the current user has a specific role
     * 
     * @param {string} roleName The role name to check
     * @returns {boolean} True if user has the role
     */
    async hasRole(roleName) {
        if (!this.initialized) {
            await this.init();
        }
        
        if (!this.currentUserRole) {
            return false;
        }
        
        return this.currentUserRole.name === roleName;
    }

    /**
     * Check if the current user has a specific permission
     * 
     * @param {string} permissionName The permission name to check
     * @returns {boolean} True if user has the permission
     */
    async hasPermission(permissionName) {
        if (!this.initialized) {
            await this.init();
        }
        
        if (!this.currentUserRole || !this.currentUserRole.permissions) {
            return false;
        }
        
        return this.currentUserRole.permissions.some(permission => 
            permission.name === permissionName
        );
    }

    /**
     * Assign a role to a user
     * 
     * @param {number} userId The user ID
     * @param {number} roleId The role ID to assign
     * @returns {Promise<boolean>} True if successful
     */
    async assignRole(userId, roleId) {
        try {
            const response = await fetch(`/api/users/${userId}/role`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                credentials: 'same-origin',
                body: JSON.stringify({ role_id: roleId })
            });

            return response.ok;
        } catch (error) {
            console.error('Failed to assign role:', error);
            return false;
        }
    }

    /**
     * Update permissions for a role
     * 
     * @param {number} roleId The role ID
     * @param {Array<number>} permissionIds Array of permission IDs
     * @returns {Promise<boolean>} True if successful
     */
    async updateRolePermissions(roleId, permissionIds) {
        try {
            const response = await fetch(`/api/roles/${roleId}/permissions`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                credentials: 'same-origin',
                body: JSON.stringify({ permissions: permissionIds })
            });

            if (response.ok) {
                // Update local cache if the role exists
                const roleIndex = this.roles?.findIndex(role => role.id === roleId);
                if (roleIndex !== -1 && roleIndex !== undefined) {
                    this.roles[roleIndex].permissions = permissionIds;
                }
                return true;
            }
            
            return false;
        } catch (error) {
            console.error('Failed to update role permissions:', error);
            return false;
        }
    }
}

// Create a singleton instance
const roleManager = new RoleManager();

export default roleManager; 