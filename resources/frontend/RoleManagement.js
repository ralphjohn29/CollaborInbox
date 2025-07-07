/**
 * RoleManagement Component
 * 
 * A comprehensive component for managing roles and permissions.
 * Provides an interface for administrators to manage roles and their associated permissions.
 */
import roleManager from './RoleManager.js';
import PermissionManager from './PermissionManager.js';
import RoleSelector from './RoleSelector.js';

class RoleManagement {
    /**
     * Constructor
     * 
     * @param {Object} options Configuration options
     * @param {string} options.containerId ID of the container element
     */
    constructor(options = {}) {
        this.containerId = options.containerId || 'role-management';
        this.container = document.getElementById(this.containerId);
        this.currentRole = null;
        this.isLoading = false;
        this.permissionManager = null;
        
        if (!this.container) {
            console.error(`Container element with ID "${this.containerId}" not found.`);
            return;
        }
        
        this.init();
    }
    
    /**
     * Initialize the component
     */
    async init() {
        this.isLoading = true;
        this.render();
        
        try {
            await roleManager.init();
            
            // Check user permissions for role management
            const canManageRoles = await roleManager.hasPermission('edit roles');
            if (!canManageRoles) {
                this.container.innerHTML = '<div class="alert alert-warning">You do not have permission to manage roles.</div>';
                return;
            }
            
            // Set initially selected role to admin if exists
            const roles = await roleManager.getRoles();
            if (roles && roles.length > 0) {
                const adminRole = roles.find(role => role.name === 'admin');
                this.currentRole = adminRole ? adminRole.id : roles[0].id;
            }
            
            this.isLoading = false;
            this.render();
            this.setupComponents();
            this.setupEventListeners();
        } catch (error) {
            console.error('Failed to initialize RoleManagement:', error);
            this.isLoading = false;
            this.container.innerHTML = '<div class="alert alert-danger">Failed to load role management. Please try again later.</div>';
        }
    }
    
    /**
     * Render the component
     */
    render() {
        if (this.isLoading) {
            this.container.innerHTML = '<div class="loading-spinner mb-2">Loading role management...</div>';
            return;
        }
        
        this.container.innerHTML = `
            <div class="role-management">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Role Management</h1>
                    <p class="text-gray-500">
                        Manage roles and their associated permissions for your organization.
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <!-- Role selection sidebar -->
                    <div class="md:col-span-4 lg:col-span-3 border rounded-md p-4 bg-white">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Roles</h2>
                        <div id="role-selection-container"></div>
                    </div>
                    
                    <!-- Permission management main area -->
                    <div class="md:col-span-8 lg:col-span-9 border rounded-md p-4 bg-white">
                        <div id="permission-manager-container"></div>
                    </div>
                </div>
            </div>
        `;
    }
    
    /**
     * Set up role and permission management components
     */
    setupComponents() {
        const roleSelectionContainer = this.container.querySelector('#role-selection-container');
        const permissionManagerContainer = this.container.querySelector('#permission-manager-container');
        
        if (!roleSelectionContainer || !permissionManagerContainer) {
            return;
        }
        
        // Create role selector container
        roleSelectionContainer.innerHTML = `
            <div id="role-selector-container" class="mb-4"></div>
        `;
        
        // Initialize the role selector
        new RoleSelector({
            containerId: 'role-selector-container',
            selectedRoleId: this.currentRole,
            onChange: (roleId) => {
                this.currentRole = roleId;
                
                // Update the permission manager
                if (this.permissionManager) {
                    this.permissionManager.updateRoleId(roleId);
                }
            }
        });
        
        // Initialize the permission manager
        if (this.currentRole) {
            this.permissionManager = new PermissionManager({
                containerId: 'permission-manager-container',
                roleId: this.currentRole,
                onSave: (permissions) => {
                    console.log('Permissions saved:', permissions);
                }
            });
        } else {
            permissionManagerContainer.innerHTML = '<div class="alert alert-info">Please select a role to manage permissions.</div>';
        }
    }
    
    /**
     * Set up event listeners
     */
    setupEventListeners() {
        // No additional event listeners needed here as they're handled in the child components
    }
}

// Make it available globally for easy initialization
window.RoleManagement = RoleManagement;

export default RoleManagement; 