/**
 * TeamManagementPanel Component
 * 
 * A component for team leads to manage their team members.
 * Provides an interface for viewing team members and managing their roles.
 */
import roleManager from './RoleManager.js';

class TeamManagementPanel {
    /**
     * Constructor
     * 
     * @param {Object} options Configuration options
     * @param {string} options.containerId ID of the container element
     */
    constructor(options = {}) {
        this.containerId = options.containerId || 'team-management-panel';
        this.container = document.getElementById(this.containerId);
        this.isLoading = false;
        this.teamMembers = [];
        this.roles = [];
        this.isTeamLead = false;
        
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
            
            // Check if user is a team lead
            this.isTeamLead = await roleManager.hasRole('team lead');
            
            if (!this.isTeamLead) {
                this.container.innerHTML = '<div class="alert alert-warning">You do not have permission to access team management.</div>';
                return;
            }
            
            // Get available roles for assignment
            const allRoles = await roleManager.getRoles();
            
            // Filter roles to only agent and team lead (if user has proper permissions)
            this.roles = await Promise.all(allRoles.map(async role => {
                if (role.name === 'agent') return role;
                if (role.name === 'team lead' && await roleManager.hasPermission('assign team lead')) return role;
                return null;
            })).then(roles => roles.filter(role => role !== null));
            
            // Fetch team members
            const response = await fetch('/api/team-members', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`Failed to fetch team members: ${response.status}`);
            }
            
            const data = await response.json();
            this.teamMembers = data.team_members;
            
            this.isLoading = false;
            this.render();
            this.setupEventListeners();
        } catch (error) {
            console.error('Failed to initialize TeamManagementPanel:', error);
            this.isLoading = false;
            this.container.innerHTML = '<div class="alert alert-danger">Failed to load team management. Please try again later.</div>';
        }
    }
    
    /**
     * Render the component
     */
    render() {
        if (this.isLoading) {
            this.container.innerHTML = '<div class="loading-spinner mb-2">Loading team management...</div>';
            return;
        }
        
        if (!this.isTeamLead) {
            return;
        }
        
        let teamMembersHtml = '';
        
        if (this.teamMembers.length === 0) {
            teamMembersHtml = '<tr><td colspan="5" class="text-center py-4">No team members found.</td></tr>';
        } else {
            teamMembersHtml = this.teamMembers.map(member => {
                return `
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 border-b">${member.name}</td>
                        <td class="py-3 px-4 border-b">${member.email}</td>
                        <td class="py-3 px-4 border-b">
                            <span class="inline-block px-2 py-1 text-xs font-semibold ${member.role.name === 'team lead' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'} rounded-full">
                                ${member.role.name}
                            </span>
                        </td>
                        <td class="py-3 px-4 border-b">
                            <span class="inline-block px-2 py-1 text-xs font-semibold ${member.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'} rounded-full">
                                ${member.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                        <td class="py-3 px-4 border-b text-right">
                            <div class="flex justify-end items-center">
                                ${this.canManageUser(member) ? `
                                <select class="role-select mr-2 p-1 border rounded text-sm" data-user-id="${member.id}">
                                    ${this.roles.map(role => `
                                        <option value="${role.id}" ${member.role.id === role.id ? 'selected' : ''}>
                                            ${role.name}
                                        </option>
                                    `).join('')}
                                </select>
                                <button class="toggle-status-btn px-2 py-1 text-xs rounded ${member.is_active ? 'bg-red-100 text-red-800 hover:bg-red-200' : 'bg-green-100 text-green-800 hover:bg-green-200'}" data-user-id="${member.id}">
                                    ${member.is_active ? 'Deactivate' : 'Activate'}
                                </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }
        
        this.container.innerHTML = `
            <div class="team-management-panel">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Manage Your Team</h2>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600">
                        As a team lead, you can manage the agents in your team including their roles and activation status.
                    </p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b">Name</th>
                                <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b">Email</th>
                                <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b">Role</th>
                                <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b">Status</th>
                                <th class="py-3 px-4 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider border-b">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${teamMembersHtml}
                        </tbody>
                    </table>
                </div>
                
                <div id="team-management-message" class="mt-4 hidden"></div>
            </div>
        `;
    }
    
    /**
     * Set up event listeners
     */
    setupEventListeners() {
        // Role select change event
        const roleSelects = this.container.querySelectorAll('.role-select');
        roleSelects.forEach(select => {
            select.addEventListener('change', (e) => {
                const userId = e.target.dataset.userId;
                const roleId = e.target.value;
                this.updateUserRole(userId, roleId);
            });
        });
        
        // Toggle status buttons
        const toggleBtns = this.container.querySelectorAll('.toggle-status-btn');
        toggleBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const userId = e.target.dataset.userId;
                this.toggleUserStatus(userId);
            });
        });
    }
    
    /**
     * Check if the current user can manage the specified user
     * 
     * @param {Object} user The user to check
     * @returns {boolean} True if the current user can manage the specified user
     */
    canManageUser(user) {
        // Team leads can't modify admin accounts
        if (user.role.name === 'admin') {
            return false;
        }
        
        // Team leads can't modify themselves
        if (user.id === roleManager.currentUserRole?.id) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Update a user's role
     * 
     * @param {string} userId The user ID
     * @param {string} roleId The new role ID
     */
    async updateUserRole(userId, roleId) {
        const messageEl = this.container.querySelector('#team-management-message');
        if (!messageEl) {
            return;
        }
        
        try {
            this.showMessage(messageEl, 'Updating user role...', 'info');
            
            const response = await fetch(`/api/users/${userId}/role`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({ role_id: roleId }),
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (response.ok) {
                // Update the local team member data
                const memberIndex = this.teamMembers.findIndex(member => member.id === parseInt(userId));
                if (memberIndex !== -1) {
                    const newRole = this.roles.find(role => role.id === parseInt(roleId));
                    if (newRole) {
                        this.teamMembers[memberIndex].role = newRole;
                    }
                }
                
                this.showMessage(messageEl, 'User role updated successfully!', 'success');
                this.render();
                this.setupEventListeners();
            } else {
                this.showMessage(messageEl, data.message || 'Failed to update user role.', 'error');
            }
        } catch (error) {
            console.error('Failed to update user role:', error);
            this.showMessage(messageEl, 'An error occurred. Please try again later.', 'error');
        }
    }
    
    /**
     * Toggle a user's active status
     * 
     * @param {string} userId The user ID
     */
    async toggleUserStatus(userId) {
        const messageEl = this.container.querySelector('#team-management-message');
        if (!messageEl) {
            return;
        }
        
        try {
            this.showMessage(messageEl, 'Updating user status...', 'info');
            
            const response = await fetch(`/api/agents/${userId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (response.ok) {
                // Update the local team member data
                const memberIndex = this.teamMembers.findIndex(member => member.id === parseInt(userId));
                if (memberIndex !== -1) {
                    this.teamMembers[memberIndex].is_active = !this.teamMembers[memberIndex].is_active;
                }
                
                const statusText = this.teamMembers[memberIndex].is_active ? 'activated' : 'deactivated';
                this.showMessage(messageEl, `User ${statusText} successfully!`, 'success');
                this.render();
                this.setupEventListeners();
            } else {
                this.showMessage(messageEl, data.message || 'Failed to update user status.', 'error');
            }
        } catch (error) {
            console.error('Failed to toggle user status:', error);
            this.showMessage(messageEl, 'An error occurred. Please try again later.', 'error');
        }
    }
    
    /**
     * Show a message in the specified element
     * 
     * @param {HTMLElement} element The element to show the message in
     * @param {string} message The message to show
     * @param {string} type The type of message ('success', 'error', 'info')
     */
    showMessage(element, message, type = 'info') {
        if (!element) {
            return;
        }
        
        // Set appropriate classes based on message type
        element.className = 'mt-4 p-3 rounded';
        
        switch (type) {
            case 'success':
                element.classList.add('bg-green-100', 'text-green-700', 'border', 'border-green-400');
                break;
            case 'error':
                element.classList.add('bg-red-100', 'text-red-700', 'border', 'border-red-400');
                break;
            case 'info':
            default:
                element.classList.add('bg-blue-100', 'text-blue-700', 'border', 'border-blue-400');
                break;
        }
        
        element.textContent = message;
        element.classList.remove('hidden');
        
        // Hide message after 5 seconds for success messages
        if (type === 'success') {
            setTimeout(() => {
                element.classList.add('hidden');
            }, 5000);
        }
    }
}

export default TeamManagementPanel; 