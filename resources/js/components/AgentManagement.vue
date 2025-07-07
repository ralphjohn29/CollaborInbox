<template>
  <div class="agent-management">
    <div class="page-header">
      <h1>Agent Management</h1>
      <div class="actions">
        <button @click="showCreateAgentModal" class="btn btn-primary" v-if="canCreateAgents">Add New Agent</button>
      </div>
    </div>

    <!-- Search and Filters -->
    <div class="filters">
      <div class="search-box">
        <input 
          type="text" 
          v-model="search" 
          @input="debounceSearch" 
          placeholder="Search by name or email"
          class="form-control"
        />
      </div>
      
      <div class="filter-selects">
        <select v-model="roleFilter" @change="loadAgents" class="form-select">
          <option value="">All Roles</option>
          <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
        </select>
        
        <select v-model="statusFilter" @change="loadAgents" class="form-select">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="alert alert-danger">
      {{ error }}
    </div>

    <!-- Agents Table -->
    <table v-if="!loading && agents.length > 0" class="table">
      <thead>
        <tr>
          <th v-if="canEditAgents || canDeleteAgents">
            <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" />
          </th>
          <th @click="sortBy('name')">
            Name
            <span v-if="sortColumn === 'name'" class="sort-indicator">
              {{ sortDirection === 'asc' ? '▲' : '▼' }}
            </span>
          </th>
          <th @click="sortBy('email')">
            Email
            <span v-if="sortColumn === 'email'" class="sort-indicator">
              {{ sortDirection === 'asc' ? '▲' : '▼' }}
            </span>
          </th>
          <th @click="sortBy('role.name')">
            Role
            <span v-if="sortColumn === 'role.name'" class="sort-indicator">
              {{ sortDirection === 'asc' ? '▲' : '▼' }}
            </span>
          </th>
          <th @click="sortBy('is_active')">
            Status
            <span v-if="sortColumn === 'is_active'" class="sort-indicator">
              {{ sortDirection === 'asc' ? '▲' : '▼' }}
            </span>
          </th>
          <th @click="sortBy('created_at')">
            Created
            <span v-if="sortColumn === 'created_at'" class="sort-indicator">
              {{ sortDirection === 'asc' ? '▲' : '▼' }}
            </span>
          </th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="agent in agents" :key="agent.id">
          <td v-if="canEditAgents || canDeleteAgents">
            <input type="checkbox" v-model="selectedAgents" :value="agent.id" />
          </td>
          <td>{{ agent.name }}</td>
          <td>{{ agent.email }}</td>
          <td>{{ agent.role ? agent.role.name : 'No Role' }}</td>
          <td>
            <span :class="agent.is_active ? 'badge bg-success' : 'badge bg-danger'">
              {{ agent.is_active ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td>{{ formatDate(agent.created_at) }}</td>
          <td class="actions">
            <button @click="viewAgentDetails(agent)" class="btn btn-sm btn-info">View</button>
            <button v-if="canEditAgents" @click="showEditAgentModal(agent)" class="btn btn-sm btn-primary">Edit</button>
            <button v-if="canEditAgents" @click="toggleAgentStatus(agent)" class="btn btn-sm" :class="agent.is_active ? 'btn-warning' : 'btn-success'">
              {{ agent.is_active ? 'Deactivate' : 'Activate' }}
            </button>
            <button v-if="canDeleteAgents" @click="confirmDeleteAgent(agent)" class="btn btn-sm btn-danger">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- No Results Message -->
    <div v-if="!loading && agents.length === 0" class="no-results">
      <p>No agents found. Try clearing filters or adding a new agent.</p>
    </div>

    <!-- Pagination -->
    <div v-if="!loading && totalPages > 1" class="pagination">
      <button @click="prevPage" :disabled="currentPage === 1" class="btn btn-secondary">Previous</button>
      <span>Page {{ currentPage }} of {{ totalPages }}</span>
      <button @click="nextPage" :disabled="currentPage === totalPages" class="btn btn-secondary">Next</button>
    </div>

    <!-- Bulk Actions -->
    <div v-if="selectedAgents.length > 0 && (canEditAgents || canDeleteAgents)" class="bulk-actions">
      <select v-model="bulkAction" class="form-select">
        <option value="">Bulk Actions</option>
        <option v-if="canEditAgents" value="activate">Activate</option>
        <option v-if="canEditAgents" value="deactivate">Deactivate</option>
        <option v-if="canEditAgents" value="change_role">Change Role</option>
        <option v-if="canDeleteAgents" value="delete">Delete</option>
      </select>
      
      <select v-if="bulkAction === 'change_role'" v-model="bulkRoleId" class="form-select">
        <option value="">Select Role</option>
        <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
      </select>
      
      <button @click="applyBulkAction" class="btn btn-primary" :disabled="!canApplyBulkAction">
        Apply ({{ selectedAgents.length }} selected)
      </button>
    </div>

    <!-- Create/Edit Agent Modal -->
    <div v-if="showModal" class="modal-backdrop" @click="closeModal"></div>
    <div v-if="showModal" class="modal show" tabindex="-1" style="display: block;">
      <div class="modal-dialog" @click.stop>
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditMode ? 'Edit Agent' : 'Create New Agent' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitAgentForm">
              <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" v-model="agentForm.name" required>
                <div v-if="formErrors.name" class="form-error">{{ formErrors.name[0] }}</div>
              </div>
              
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" v-model="agentForm.email" required>
                <div v-if="formErrors.email" class="form-error">{{ formErrors.email[0] }}</div>
              </div>
              
              <div class="mb-3">
                <label for="password" class="form-label">
                  Password {{ isEditMode ? '(Leave blank to keep current)' : '' }}
                </label>
                <input type="password" class="form-control" id="password" v-model="agentForm.password" :required="!isEditMode">
                <div v-if="formErrors.password" class="form-error">{{ formErrors.password[0] }}</div>
              </div>
              
              <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" v-model="agentForm.role_id" required>
                  <option value="">Select Role</option>
                  <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                </select>
                <div v-if="formErrors.role_id" class="form-error">{{ formErrors.role_id[0] }}</div>
              </div>
              
              <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_active" v-model="agentForm.is_active">
                <label class="form-check-label" for="is_active">Active</label>
              </div>
              
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
                <button type="submit" class="btn btn-primary" :disabled="formSubmitting">
                  <span v-if="formSubmitting" class="spinner-border spinner-border-sm" role="status"></span>
                  {{ isEditMode ? 'Update Agent' : 'Create Agent' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- View Agent Details Modal -->
    <div v-if="showViewModal" class="modal-backdrop" @click="closeViewModal"></div>
    <div v-if="showViewModal" class="modal show" tabindex="-1" style="display: block;">
      <div class="modal-dialog" @click.stop>
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Agent Details</h5>
            <button type="button" class="btn-close" @click="closeViewModal"></button>
          </div>
          <div class="modal-body">
            <div v-if="selectedAgent">
              <div class="agent-detail">
                <strong>Name:</strong> {{ selectedAgent.name }}
              </div>
              <div class="agent-detail">
                <strong>Email:</strong> {{ selectedAgent.email }}
              </div>
              <div class="agent-detail">
                <strong>Role:</strong> {{ selectedAgent.role ? selectedAgent.role.name : 'No Role' }}
              </div>
              <div class="agent-detail">
                <strong>Status:</strong> 
                <span :class="selectedAgent.is_active ? 'badge bg-success' : 'badge bg-danger'">
                  {{ selectedAgent.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
              <div class="agent-detail">
                <strong>Created:</strong> {{ formatDate(selectedAgent.created_at) }}
              </div>
              <div class="agent-detail">
                <strong>Updated:</strong> {{ formatDate(selectedAgent.updated_at) }}
              </div>
              <div class="agent-detail" v-if="agentStats">
                <strong>Assigned Threads:</strong> {{ agentStats.assigned_threads_count || 0 }}
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeViewModal">Close</button>
            <button v-if="canEditAgents" type="button" class="btn btn-primary" @click="showEditAgentModal(selectedAgent)">Edit</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-backdrop" @click="closeDeleteModal"></div>
    <div v-if="showDeleteModal" class="modal show" tabindex="-1" style="display: block;">
      <div class="modal-dialog" @click.stop>
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirm Delete</h5>
            <button type="button" class="btn-close" @click="closeDeleteModal"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete {{ selectedAgent ? selectedAgent.name : 'this agent' }}?</p>
            <p class="text-danger">This action cannot be undone.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="deleteAgent" :disabled="deleteInProgress">
              <span v-if="deleteInProgress" class="spinner-border spinner-border-sm" role="status"></span>
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bulk Action Confirmation Modal -->
    <div v-if="showBulkModal" class="modal-backdrop" @click="closeBulkModal"></div>
    <div v-if="showBulkModal" class="modal show" tabindex="-1" style="display: block;">
      <div class="modal-dialog" @click.stop>
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirm Bulk Action</h5>
            <button type="button" class="btn-close" @click="closeBulkModal"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to {{ bulkActionText }} {{ selectedAgents.length }} agents?</p>
            <p v-if="bulkAction === 'delete'" class="text-danger">This action cannot be undone.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeBulkModal">Cancel</button>
            <button type="button" class="btn" :class="bulkAction === 'delete' ? 'btn-danger' : 'btn-primary'" @click="executeBulkAction" :disabled="bulkActionInProgress">
              <span v-if="bulkActionInProgress" class="spinner-border spinner-border-sm" role="status"></span>
              Confirm
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AgentManagement',
  data() {
    return {
      // Data fetching state
      loading: true,
      error: null,
      
      // Agents and roles data
      agents: [],
      roles: [],
      
      // Pagination
      totalAgents: 0,
      currentPage: 1,
      perPage: 15,
      totalPages: 1,
      
      // Sorting
      sortColumn: 'created_at',
      sortDirection: 'desc',
      
      // Filtering
      search: '',
      searchTimeout: null,
      roleFilter: '',
      statusFilter: '',
      
      // Selection
      selectedAgents: [],
      selectAll: false,
      
      // Bulk actions
      bulkAction: '',
      bulkRoleId: '',
      bulkActionInProgress: false,
      showBulkModal: false,
      
      // Agent form
      showModal: false,
      isEditMode: false,
      agentForm: {
        name: '',
        email: '',
        password: '',
        role_id: '',
        is_active: true
      },
      formErrors: {},
      formSubmitting: false,
      
      // Agent details view
      showViewModal: false,
      selectedAgent: null,
      agentStats: null,
      
      // Delete confirmation
      showDeleteModal: false,
      deleteInProgress: false
    };
  },
  computed: {
    // Permissions checks (implement based on your auth system)
    canViewAgents() {
      return true; // Replace with your permission check
    },
    canCreateAgents() {
      return true; // Replace with your permission check
    },
    canEditAgents() {
      return true; // Replace with your permission check
    },
    canDeleteAgents() {
      return true; // Replace with your permission check
    },
    
    // Bulk action helpers
    canApplyBulkAction() {
      if (!this.bulkAction) return false;
      if (this.bulkAction === 'change_role' && !this.bulkRoleId) return false;
      return this.selectedAgents.length > 0;
    },
    bulkActionText() {
      switch (this.bulkAction) {
        case 'activate': return 'activate';
        case 'deactivate': return 'deactivate';
        case 'change_role': return 'change the role of';
        case 'delete': return 'delete';
        default: return '';
      }
    }
  },
  created() {
    this.loadAgents();
  },
  methods: {
    // Data loading
    async loadAgents() {
      this.loading = true;
      this.error = null;
      
      try {
        const params = {
          page: this.currentPage,
          per_page: this.perPage,
          sort_by: this.sortColumn,
          sort_dir: this.sortDirection
        };
        
        if (this.search) params.search = this.search;
        if (this.roleFilter) params.role = this.roleFilter;
        if (this.statusFilter) params.status = this.statusFilter;
        
        const response = await axios.get('/api/agents', { params });
        
        this.agents = response.data.agents.data;
        this.roles = response.data.roles;
        this.totalAgents = response.data.agents.total;
        this.totalPages = Math.ceil(this.totalAgents / this.perPage);
        
        // Reset selection when data changes
        this.selectedAgents = [];
        this.selectAll = false;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to load agents';
        console.error('Error loading agents:', error);
      } finally {
        this.loading = false;
      }
    },
    
    // Search and filters
    debounceSearch() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.currentPage = 1; // Reset to first page
        this.loadAgents();
      }, 500);
    },
    
    // Sorting
    sortBy(column) {
      if (this.sortColumn === column) {
        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortColumn = column;
        this.sortDirection = 'asc';
      }
      this.loadAgents();
    },
    
    // Pagination
    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
        this.loadAgents();
      }
    },
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
        this.loadAgents();
      }
    },
    
    // Selection methods
    toggleSelectAll() {
      if (this.selectAll) {
        this.selectedAgents = this.agents.map(agent => agent.id);
      } else {
        this.selectedAgents = [];
      }
    },
    
    // Bulk actions
    applyBulkAction() {
      if (!this.canApplyBulkAction) return;
      this.showBulkModal = true;
    },
    
    async executeBulkAction() {
      this.bulkActionInProgress = true;
      
      try {
        let payload = {
          ids: this.selectedAgents,
          action: this.bulkAction
        };
        
        if (this.bulkAction === 'change_role') {
          payload.role_id = this.bulkRoleId;
        }
        
        const response = await axios.post('/api/agents/bulk', payload);
        
        // Show success message
        alert(response.data.message);
        
        // Reload data
        await this.loadAgents();
        
        // Reset bulk action form
        this.bulkAction = '';
        this.bulkRoleId = '';
        this.selectedAgents = [];
        this.showBulkModal = false;
      } catch (error) {
        alert(error.response?.data?.message || 'Failed to perform bulk action');
        console.error('Bulk action error:', error);
      } finally {
        this.bulkActionInProgress = false;
      }
    },
    
    closeBulkModal() {
      this.showBulkModal = false;
    },
    
    // Agent details view
    async viewAgentDetails(agent) {
      this.selectedAgent = agent;
      this.showViewModal = true;
      
      try {
        const response = await axios.get(`/api/agents/${agent.id}`);
        this.agentStats = response.data.stats;
      } catch (error) {
        console.error('Error loading agent details:', error);
      }
    },
    
    closeViewModal() {
      this.showViewModal = false;
      this.selectedAgent = null;
      this.agentStats = null;
    },
    
    // Create/Edit agent
    showCreateAgentModal() {
      this.isEditMode = false;
      this.agentForm = {
        name: '',
        email: '',
        password: '',
        role_id: '',
        is_active: true
      };
      this.formErrors = {};
      this.showModal = true;
    },
    
    showEditAgentModal(agent) {
      this.isEditMode = true;
      this.selectedAgent = agent;
      this.agentForm = {
        name: agent.name,
        email: agent.email,
        password: '',
        role_id: agent.role_id,
        is_active: agent.is_active
      };
      this.formErrors = {};
      this.showModal = true;
      this.showViewModal = false; // Close view modal if open
    },
    
    closeModal() {
      this.showModal = false;
      this.isEditMode = false;
      this.agentForm = {
        name: '',
        email: '',
        password: '',
        role_id: '',
        is_active: true
      };
      this.formErrors = {};
    },
    
    async submitAgentForm() {
      this.formSubmitting = true;
      this.formErrors = {};
      
      try {
        let response;
        
        if (this.isEditMode) {
          response = await axios.put(`/api/agents/${this.selectedAgent.id}`, this.agentForm);
        } else {
          response = await axios.post('/api/agents', this.agentForm);
        }
        
        // Show success message
        alert(response.data.message);
        
        // Reload data
        await this.loadAgents();
        
        // Close modal
        this.closeModal();
      } catch (error) {
        if (error.response?.data?.errors) {
          this.formErrors = error.response.data.errors;
        } else {
          alert(error.response?.data?.message || 'Failed to save agent');
        }
        console.error('Form submission error:', error);
      } finally {
        this.formSubmitting = false;
      }
    },
    
    // Toggle agent status
    async toggleAgentStatus(agent) {
      try {
        const response = await axios.patch(`/api/agents/${agent.id}/toggle-status`);
        
        // Update agent in the list
        const index = this.agents.findIndex(a => a.id === agent.id);
        if (index !== -1) {
          this.agents[index].is_active = !agent.is_active;
        }
        
        // Show success message
        alert(response.data.message);
      } catch (error) {
        alert(error.response?.data?.message || 'Failed to toggle agent status');
        console.error('Toggle status error:', error);
      }
    },
    
    // Delete agent
    confirmDeleteAgent(agent) {
      this.selectedAgent = agent;
      this.showDeleteModal = true;
    },
    
    closeDeleteModal() {
      this.showDeleteModal = false;
      this.selectedAgent = null;
    },
    
    async deleteAgent() {
      if (!this.selectedAgent) return;
      
      this.deleteInProgress = true;
      
      try {
        const response = await axios.delete(`/api/agents/${this.selectedAgent.id}`);
        
        // Show success message
        alert(response.data.message);
        
        // Reload data
        await this.loadAgents();
        
        // Close modal
        this.closeDeleteModal();
      } catch (error) {
        alert(error.response?.data?.message || 'Failed to delete agent');
        console.error('Delete error:', error);
      } finally {
        this.deleteInProgress = false;
      }
    },
    
    // Utility methods
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleString();
    }
  }
};
</script>

<style scoped>
.agent-management {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.filters {
  display: flex;
  margin-bottom: 20px;
  gap: 10px;
}

.search-box {
  flex: 1;
}

.filter-selects {
  display: flex;
  gap: 10px;
}

.filter-selects select {
  min-width: 150px;
}

.loading {
  display: flex;
  justify-content: center;
  padding: 40px;
}

.no-results {
  text-align: center;
  padding: 40px;
  background: #f9f9f9;
  border-radius: 4px;
}

.table {
  width: 100%;
  margin-bottom: 20px;
}

.table th {
  cursor: pointer;
  position: relative;
}

.sort-indicator {
  margin-left: 5px;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  margin: 20px 0;
}

.actions {
  display: flex;
  gap: 5px;
}

.bulk-actions {
  display: flex;
  gap: 10px;
  margin-top: 20px;
  align-items: center;
}

.form-error {
  color: #dc3545;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.agent-detail {
  margin-bottom: 10px;
}

/* Modal Styles */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1040;
}

.modal {
  z-index: 1050;
}
</style> 