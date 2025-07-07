/**
 * RoleSelector Component
 * 
 * A reusable component for selecting and assigning user roles.
 * Provides a dropdown interface for role selection with validation.
 */
import roleManager from './RoleManager.js';

class RoleSelector {
    /**
     * Constructor
     * 
     * @param {Object} options Configuration options
     * @param {string} options.containerId ID of the container element
     * @param {Function} options.onChange Callback function when role changes
     * @param {number|null} options.userId User ID if in edit mode
     * @param {number|null} options.selectedRoleId Initially selected role ID
     */
    constructor(options = {}) {
        this.containerId = options.containerId || 'role-selector';
        this.onChange = options.onChange || null;
        this.userId = options.userId || null;
        this.selectedRoleId = options.selectedRoleId || null;
        this.roles = [];
        this.isLoading = false;
        this.container = document.getElementById(this.containerId);
        
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
            this.roles = await roleManager.getRoles();
            
            // Check user permissions for role management
            const canManageRoles = await roleManager.hasPermission('edit roles');
            if (!canManageRoles) {
                this.container.innerHTML = '<div class="alert alert-warning">You do not have permission to manage roles.</div>';
                return;
            }
            
            this.isLoading = false;
            this.render();
            this.setupEventListeners();
        } catch (error) {
            console.error('Failed to initialize RoleSelector:', error);
            this.isLoading = false;
            this.container.innerHTML = '<div class="alert alert-danger">Failed to load roles. Please try again later.</div>';
        }
    }
    
    /**
     * Render the component
     */
    render() {
        if (this.isLoading) {
            this.container.innerHTML = '<div class="loading-spinner mb-2">Loading roles...</div>';
            return;
        }
        
        const roleOptions = this.roles.map(role => {
            const selected = this.selectedRoleId === role.id ? 'selected' : '';
            return `<option value="${role.id}" ${selected}>${role.name} - ${role.description || ''}</option>`;
        }).join('');
        
        this.container.innerHTML = `
            <div class="role-selector-component">
                <div class="form-group">
                    <label for="role-select" class="font-medium text-gray-700">User Role</label>
                    <select id="role-select" class="block w-full px-3 py-2 mt-1 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select a role</option>
                        ${roleOptions}
                    </select>
                    <div id="role-description" class="mt-1 text-sm text-gray-500"></div>
                </div>
                ${this.userId ? `
                <div class="mt-3">
                    <button id="save-role-btn" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Save Role
                    </button>
                </div>
                ` : ''}
                <div id="role-error" class="mt-2 text-sm text-red-600 hidden"></div>
            </div>
        `;
    }
    
    /**
     * Set up event listeners
     */
    setupEventListeners() {
        const roleSelect = this.container.querySelector('#role-select');
        const saveRoleBtn = this.container.querySelector('#save-role-btn');
        
        if (roleSelect) {
            roleSelect.addEventListener('change', (e) => {
                this.selectedRoleId = e.target.value ? parseInt(e.target.value) : null;
                this.updateRoleDescription();
                
                if (this.onChange) {
                    this.onChange(this.selectedRoleId);
                }
            });
            
            // Trigger to show initial description
            this.updateRoleDescription();
        }
        
        if (saveRoleBtn && this.userId) {
            saveRoleBtn.addEventListener('click', () => this.saveRole());
        }
    }
    
    /**
     * Update the role description text
     */
    updateRoleDescription() {
        const descriptionEl = this.container.querySelector('#role-description');
        if (!descriptionEl) return;
        
        if (!this.selectedRoleId) {
            descriptionEl.textContent = '';
            return;
        }
        
        const selectedRole = this.roles.find(role => role.id === this.selectedRoleId);
        if (selectedRole) {
            descriptionEl.textContent = selectedRole.description || '';
            
            // Add permission list if available
            if (selectedRole.permissions && selectedRole.permissions.length) {
                const permissionsList = selectedRole.permissions.map(p => 
                    `<li class="text-xs inline-block mr-2 bg-gray-100 px-2 py-1 rounded">${p.name}</li>`
                ).join('');
                
                descriptionEl.innerHTML += `
                    <div class="mt-2">
                        <p class="text-xs font-medium">Permissions:</p>
                        <ul class="mt-1 flex flex-wrap gap-1">${permissionsList}</ul>
                    </div>
                `;
            }
        } else {
            descriptionEl.textContent = '';
        }
    }
    
    /**
     * Save the selected role
     */
    async saveRole() {
        if (!this.userId || !this.selectedRoleId) {
            this.showError('Please select a valid role.');
            return;
        }
        
        const errorEl = this.container.querySelector('#role-error');
        const saveBtn = this.container.querySelector('#save-role-btn');
        
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';
        }
        
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.add('hidden');
        }
        
        try {
            const success = await roleManager.assignRole(this.userId, this.selectedRoleId);
            
            if (success) {
                // Add success message
                this.container.insertAdjacentHTML('beforeend', `
                    <div class="mt-2 text-sm text-green-600">Role updated successfully!</div>
                `);
                
                // Remove success message after 3 seconds
                setTimeout(() => {
                    const successMsg = this.container.querySelector('.text-green-600');
                    if (successMsg) {
                        successMsg.remove();
                    }
                }, 3000);
            } else {
                this.showError('Failed to update role. Please try again.');
            }
        } catch (error) {
            console.error('Error saving role:', error);
            this.showError('An error occurred while saving the role.');
        } finally {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Role';
            }
        }
    }
    
    /**
     * Show an error message
     * 
     * @param {string} message The error message to display
     */
    showError(message) {
        const errorEl = this.container.querySelector('#role-error');
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
        }
    }
    
    /**
     * Get the currently selected role ID
     * 
     * @returns {number|null} The selected role ID or null
     */
    getSelectedRoleId() {
        return this.selectedRoleId;
    }
}

export default RoleSelector; 