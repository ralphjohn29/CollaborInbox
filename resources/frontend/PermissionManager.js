/**
 * PermissionManager Component
 * 
 * A component for managing permissions assigned to roles.
 * Provides an interface for viewing and editing permissions.
 */
import roleManager from './RoleManager.js';

class PermissionManager {
    /**
     * Constructor
     * 
     * @param {Object} options Configuration options
     * @param {string} options.containerId ID of the container element
     * @param {number} options.roleId ID of the role to manage permissions for
     * @param {Function} options.onSave Callback function when permissions are saved
     */
    constructor(options = {}) {
        this.containerId = options.containerId || 'permission-manager';
        this.roleId = options.roleId || null;
        this.onSave = options.onSave || null;
        this.container = document.getElementById(this.containerId);
        this.permissions = [];
        this.selectedPermissions = [];
        this.role = null;
        this.isLoading = false;
        
        if (!this.container) {
            console.error(`Container element with ID "${this.containerId}" not found.`);
            return;
        }
        
        if (!this.roleId) {
            this.container.innerHTML = '<div class="alert alert-warning">No role selected.</div>';
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
            
            // Check user permissions for permission management
            const canManagePermissions = await roleManager.hasPermission('edit roles');
            if (!canManagePermissions) {
                this.container.innerHTML = '<div class="alert alert-warning">You do not have permission to manage permissions.</div>';
                return;
            }
            
            // Get all permissions
            this.permissions = await roleManager.getPermissions();
            
            // Get the role and its permissions
            const roles = await roleManager.getRoles();
            this.role = roles.find(role => role.id === this.roleId);
            
            if (!this.role) {
                this.container.innerHTML = '<div class="alert alert-warning">Role not found.</div>';
                return;
            }
            
            // Set initially selected permissions
            if (this.role.permissions) {
                this.selectedPermissions = this.role.permissions.map(p => p.id);
            }
            
            this.isLoading = false;
            this.render();
            this.setupEventListeners();
        } catch (error) {
            console.error('Failed to initialize PermissionManager:', error);
            this.isLoading = false;
            this.container.innerHTML = '<div class="alert alert-danger">Failed to load permissions. Please try again later.</div>';
        }
    }
    
    /**
     * Render the component
     */
    render() {
        if (this.isLoading) {
            this.container.innerHTML = '<div class="loading-spinner mb-2">Loading permissions...</div>';
            return;
        }
        
        const permissionGroups = this.groupPermissionsByCategory();
        let permissionsHtml = '';
        
        // Generate HTML for permission categories
        Object.keys(permissionGroups).forEach(category => {
            const permissions = permissionGroups[category];
            permissionsHtml += `
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">${this.formatCategory(category)}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                        ${permissions.map(permission => {
                            const isChecked = this.selectedPermissions.includes(permission.id);
                            return `
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input
                                            id="permission-${permission.id}"
                                            type="checkbox"
                                            value="${permission.id}"
                                            class="permission-checkbox h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                            ${isChecked ? 'checked' : ''}
                                        >
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="permission-${permission.id}" class="font-medium text-gray-700">
                                            ${permission.name}
                                        </label>
                                        ${permission.description ? `<p class="text-gray-500">${permission.description}</p>` : ''}
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            `;
        });
        
        this.container.innerHTML = `
            <div class="permission-manager">
                <div class="mb-4">
                    <h2 class="text-xl font-bold text-gray-900">
                        Permissions for ${this.role ? this.role.name : 'Role'}
                    </h2>
                    <p class="text-sm text-gray-500">
                        Select the permissions to assign to this role.
                    </p>
                </div>
                
                <div class="p-4 border rounded-md bg-gray-50">
                    ${permissionsHtml || '<p>No permissions available.</p>'}
                </div>
                
                <div class="mt-4 flex justify-end">
                    <button id="save-permissions-btn" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Save Permissions
                    </button>
                </div>
                
                <div id="permission-error" class="mt-2 text-sm text-red-600 hidden"></div>
                <div id="permission-success" class="mt-2 text-sm text-green-600 hidden"></div>
            </div>
        `;
    }
    
    /**
     * Set up event listeners
     */
    setupEventListeners() {
        const saveBtn = this.container.querySelector('#save-permissions-btn');
        const checkboxes = this.container.querySelectorAll('.permission-checkbox');
        
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.savePermissions());
        }
        
        if (checkboxes) {
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', (e) => {
                    const permissionId = parseInt(e.target.value);
                    
                    if (e.target.checked) {
                        if (!this.selectedPermissions.includes(permissionId)) {
                            this.selectedPermissions.push(permissionId);
                        }
                    } else {
                        this.selectedPermissions = this.selectedPermissions.filter(id => id !== permissionId);
                    }
                });
            });
        }
    }
    
    /**
     * Group permissions by category based on name prefix (e.g., "view users" -> "users")
     * 
     * @returns {Object} Permissions grouped by category
     */
    groupPermissionsByCategory() {
        const groups = {};
        
        this.permissions.forEach(permission => {
            const nameParts = permission.name.split(' ');
            if (nameParts.length < 2) {
                if (!groups['general']) groups['general'] = [];
                groups['general'].push(permission);
                return;
            }
            
            // Last word is usually the category (e.g., "view users" -> "users")
            const category = nameParts[nameParts.length - 1];
            if (!groups[category]) groups[category] = [];
            groups[category].push(permission);
        });
        
        return groups;
    }
    
    /**
     * Format a category name for display
     * 
     * @param {string} category The category name to format
     * @returns {string} Formatted category name
     */
    formatCategory(category) {
        return category.charAt(0).toUpperCase() + category.slice(1);
    }
    
    /**
     * Save the selected permissions
     */
    async savePermissions() {
        const errorEl = this.container.querySelector('#permission-error');
        const successEl = this.container.querySelector('#permission-success');
        const saveBtn = this.container.querySelector('#save-permissions-btn');
        
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.add('hidden');
        }
        
        if (successEl) {
            successEl.textContent = '';
            successEl.classList.add('hidden');
        }
        
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';
        }
        
        try {
            const success = await roleManager.updateRolePermissions(this.roleId, this.selectedPermissions);
            
            if (success) {
                if (successEl) {
                    successEl.textContent = 'Permissions updated successfully!';
                    successEl.classList.remove('hidden');
                    
                    // Hide success message after 3 seconds
                    setTimeout(() => {
                        successEl.classList.add('hidden');
                    }, 3000);
                }
                
                if (this.onSave) {
                    this.onSave(this.selectedPermissions);
                }
            } else {
                if (errorEl) {
                    errorEl.textContent = 'Failed to update permissions. Please try again.';
                    errorEl.classList.remove('hidden');
                }
            }
        } catch (error) {
            console.error('Error saving permissions:', error);
            
            if (errorEl) {
                errorEl.textContent = 'An error occurred while saving permissions.';
                errorEl.classList.remove('hidden');
            }
        } finally {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Permissions';
            }
        }
    }
    
    /**
     * Get the currently selected permissions
     * 
     * @returns {Array<number>} Array of selected permission IDs
     */
    getSelectedPermissions() {
        return [...this.selectedPermissions];
    }
    
    /**
     * Update the role ID and refresh the component
     * 
     * @param {number} roleId The new role ID
     */
    updateRoleId(roleId) {
        this.roleId = roleId;
        this.init();
    }
}

export default PermissionManager; 