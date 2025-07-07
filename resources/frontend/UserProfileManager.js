/**
 * UserProfileManager Component
 * 
 * A component for managing user profiles including profile information,
 * password changes, and profile picture uploads.
 */
class UserProfileManager {
    /**
     * Constructor
     * 
     * @param {Object} options Configuration options
     * @param {string} options.containerId ID of the container element
     * @param {Function} options.onProfileUpdated Callback when profile is updated
     */
    constructor(options = {}) {
        this.containerId = options.containerId || 'profile-manager';
        this.onProfileUpdated = options.onProfileUpdated || null;
        this.container = document.getElementById(this.containerId);
        this.user = null;
        this.hasProfileImage = false;
        this.isLoading = false;
        this.activeTab = 'profile'; // 'profile' or 'password'
        
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
            // Fetch user profile data
            const response = await fetch('/api/profile', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`Failed to fetch profile: ${response.status}`);
            }
            
            const data = await response.json();
            this.user = data.user;
            this.hasProfileImage = data.has_profile_image;
            
            this.isLoading = false;
            this.render();
            this.setupEventListeners();
        } catch (error) {
            console.error('Failed to initialize UserProfileManager:', error);
            this.isLoading = false;
            this.container.innerHTML = '<div class="alert alert-danger">Failed to load profile. Please try again later.</div>';
        }
    }
    
    /**
     * Render the component
     */
    render() {
        if (this.isLoading) {
            this.container.innerHTML = '<div class="loading-spinner mb-2">Loading profile...</div>';
            return;
        }
        
        // Create tabs
        const tabsHtml = `
            <div class="profile-tabs mb-4">
                <ul class="flex border-b">
                    <li class="-mb-px mr-1">
                        <a class="profile-tab ${this.activeTab === 'profile' ? 'bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 text-blue-700 font-semibold' : 'inline-block py-2 px-4 text-blue-500 hover:text-blue-800 font-semibold'}" 
                          data-tab="profile" href="#">
                            Profile Information
                        </a>
                    </li>
                    <li class="mr-1">
                        <a class="profile-tab ${this.activeTab === 'password' ? 'bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 text-blue-700 font-semibold' : 'inline-block py-2 px-4 text-blue-500 hover:text-blue-800 font-semibold'}" 
                          data-tab="password" href="#">
                            Change Password
                        </a>
                    </li>
                </ul>
            </div>
        `;
        
        // Profile information tab content
        const profileTabHtml = `
            <div id="profile-tab-content" class="${this.activeTab === 'profile' ? '' : 'hidden'}">
                <div class="flex flex-wrap -mx-3 mb-4">
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <div class="profile-image-container mb-4">
                            <div class="w-32 h-32 rounded-full overflow-hidden border-2 border-gray-200 mx-auto">
                                <img src="${this.hasProfileImage ? `/api/profile/picture?v=${new Date().getTime()}` : '/images/default-avatar.jpg'}" 
                                     alt="Profile" class="w-full h-full object-cover" id="profile-image">
                            </div>
                            <div class="text-center mt-2">
                                <label for="profile-upload" class="cursor-pointer inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                    Change Photo
                                </label>
                                <input type="file" id="profile-upload" class="hidden" accept="image/*">
                                ${this.hasProfileImage ? `
                                <button id="delete-photo-btn" class="mt-2 px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition">
                                    Delete Photo
                                </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-2/3 px-3">
                        <form id="profile-form">
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                                    Name
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                                       id="name" type="text" value="${this.user ? this.user.name : ''}" placeholder="Full Name">
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                                    Email
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                                       id="email" type="email" value="${this.user ? this.user.email : ''}" placeholder="Email Address">
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">
                                    Role
                                </label>
                                <div class="py-2 px-3 bg-gray-100 rounded text-gray-700">
                                    ${this.user && this.user.role ? this.user.role.name : 'No role assigned'}
                                </div>
                            </div>
                            <div class="flex items-center justify-end">
                                <button id="save-profile-btn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                        <div id="profile-message" class="mt-4 hidden"></div>
                    </div>
                </div>
            </div>
        `;
        
        // Password change tab content
        const passwordTabHtml = `
            <div id="password-tab-content" class="${this.activeTab === 'password' ? '' : 'hidden'}">
                <form id="password-form" class="max-w-md mx-auto">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="current-password">
                            Current Password
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                               id="current-password" type="password" placeholder="Current Password">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="new-password">
                            New Password
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                               id="new-password" type="password" placeholder="New Password">
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="new-password-confirm">
                            Confirm New Password
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                               id="new-password-confirm" type="password" placeholder="Confirm New Password">
                    </div>
                    <div class="flex items-center justify-end">
                        <button id="change-password-btn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                            Change Password
                        </button>
                    </div>
                </form>
                <div id="password-message" class="mt-4 hidden"></div>
            </div>
        `;
        
        this.container.innerHTML = `
            <div class="user-profile-manager">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Your Profile</h2>
                ${tabsHtml}
                ${profileTabHtml}
                ${passwordTabHtml}
            </div>
        `;
    }
    
    /**
     * Set up event listeners
     */
    setupEventListeners() {
        // Tab switching
        const tabs = this.container.querySelectorAll('.profile-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                this.activeTab = tab.dataset.tab;
                this.render();
                this.setupEventListeners();
            });
        });
        
        // Profile update form
        const profileForm = this.container.querySelector('#profile-form');
        if (profileForm) {
            profileForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.updateProfile();
            });
        }
        
        // Password change form
        const passwordForm = this.container.querySelector('#password-form');
        if (passwordForm) {
            passwordForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.changePassword();
            });
        }
        
        // Profile picture upload
        const profileUpload = this.container.querySelector('#profile-upload');
        if (profileUpload) {
            profileUpload.addEventListener('change', (e) => {
                if (e.target.files && e.target.files[0]) {
                    this.uploadProfilePicture(e.target.files[0]);
                }
            });
        }
        
        // Delete profile picture
        const deletePhotoBtn = this.container.querySelector('#delete-photo-btn');
        if (deletePhotoBtn) {
            deletePhotoBtn.addEventListener('click', () => {
                this.deleteProfilePicture();
            });
        }
    }
    
    /**
     * Update user profile
     */
    async updateProfile() {
        const nameInput = this.container.querySelector('#name');
        const emailInput = this.container.querySelector('#email');
        const messageEl = this.container.querySelector('#profile-message');
        const saveBtn = this.container.querySelector('#save-profile-btn');
        
        if (!nameInput || !emailInput || !messageEl || !saveBtn) {
            return;
        }
        
        const name = nameInput.value.trim();
        const email = emailInput.value.trim();
        
        if (!name) {
            this.showMessage(messageEl, 'Name is required.', 'error');
            return;
        }
        
        if (!email) {
            this.showMessage(messageEl, 'Email is required.', 'error');
            return;
        }
        
        // Disable save button
        saveBtn.disabled = true;
        saveBtn.innerHTML = 'Saving...';
        
        try {
            const response = await fetch('/api/profile', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({ name, email }),
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (response.ok) {
                this.user = data.user;
                this.showMessage(messageEl, 'Profile updated successfully!', 'success');
                
                if (this.onProfileUpdated) {
                    this.onProfileUpdated(this.user);
                }
            } else {
                this.showMessage(messageEl, data.message || 'Failed to update profile.', 'error');
            }
        } catch (error) {
            console.error('Failed to update profile:', error);
            this.showMessage(messageEl, 'An error occurred. Please try again later.', 'error');
        } finally {
            // Re-enable save button
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Save Changes';
        }
    }
    
    /**
     * Change user password
     */
    async changePassword() {
        const currentPasswordInput = this.container.querySelector('#current-password');
        const newPasswordInput = this.container.querySelector('#new-password');
        const confirmPasswordInput = this.container.querySelector('#new-password-confirm');
        const messageEl = this.container.querySelector('#password-message');
        const changeBtn = this.container.querySelector('#change-password-btn');
        
        if (!currentPasswordInput || !newPasswordInput || !confirmPasswordInput || !messageEl || !changeBtn) {
            return;
        }
        
        const currentPassword = currentPasswordInput.value;
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (!currentPassword || !newPassword || !confirmPassword) {
            this.showMessage(messageEl, 'All password fields are required.', 'error');
            return;
        }
        
        if (newPassword.length < 8) {
            this.showMessage(messageEl, 'New password must be at least 8 characters long.', 'error');
            return;
        }
        
        if (newPassword !== confirmPassword) {
            this.showMessage(messageEl, 'New passwords do not match.', 'error');
            return;
        }
        
        // Disable change button
        changeBtn.disabled = true;
        changeBtn.innerHTML = 'Changing...';
        
        try {
            const response = await fetch('/api/profile/change-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    current_password: currentPassword,
                    new_password: newPassword,
                    new_password_confirmation: confirmPassword
                }),
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (response.ok) {
                this.showMessage(messageEl, 'Password changed successfully!', 'success');
                
                // Clear the password fields
                currentPasswordInput.value = '';
                newPasswordInput.value = '';
                confirmPasswordInput.value = '';
            } else {
                this.showMessage(messageEl, data.message || 'Failed to change password.', 'error');
            }
        } catch (error) {
            console.error('Failed to change password:', error);
            this.showMessage(messageEl, 'An error occurred. Please try again later.', 'error');
        } finally {
            // Re-enable change button
            changeBtn.disabled = false;
            changeBtn.innerHTML = 'Change Password';
        }
    }
    
    /**
     * Upload profile picture
     * 
     * @param {File} file The image file to upload
     */
    async uploadProfilePicture(file) {
        if (!file) {
            return;
        }
        
        const messageEl = this.container.querySelector('#profile-message');
        if (!messageEl) {
            return;
        }
        
        // Check file type and size
        if (!file.type.startsWith('image/')) {
            this.showMessage(messageEl, 'Please select an image file.', 'error');
            return;
        }
        
        if (file.size > 2 * 1024 * 1024) { // 2MB
            this.showMessage(messageEl, 'Image size should not exceed 2MB.', 'error');
            return;
        }
        
        // Show loading message
        this.showMessage(messageEl, 'Uploading profile picture...', 'info');
        
        try {
            const formData = new FormData();
            formData.append('profile_picture', file);
            
            const response = await fetch('/api/profile/picture', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: formData,
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (response.ok) {
                this.hasProfileImage = true;
                this.showMessage(messageEl, 'Profile picture uploaded successfully!', 'success');
                
                // Update the profile image
                const profileImage = this.container.querySelector('#profile-image');
                if (profileImage) {
                    profileImage.src = `/api/profile/picture?v=${new Date().getTime()}`;
                }
                
                // Re-render to show delete button
                this.render();
                this.setupEventListeners();
            } else {
                this.showMessage(messageEl, data.message || 'Failed to upload profile picture.', 'error');
            }
        } catch (error) {
            console.error('Failed to upload profile picture:', error);
            this.showMessage(messageEl, 'An error occurred. Please try again later.', 'error');
        }
    }
    
    /**
     * Delete profile picture
     */
    async deleteProfilePicture() {
        const messageEl = this.container.querySelector('#profile-message');
        if (!messageEl) {
            return;
        }
        
        // Confirm deletion
        if (!confirm('Are you sure you want to delete your profile picture?')) {
            return;
        }
        
        // Show loading message
        this.showMessage(messageEl, 'Deleting profile picture...', 'info');
        
        try {
            const response = await fetch('/api/profile/picture', {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (response.ok) {
                this.hasProfileImage = false;
                this.showMessage(messageEl, 'Profile picture deleted successfully!', 'success');
                
                // Update the profile image
                const profileImage = this.container.querySelector('#profile-image');
                if (profileImage) {
                    profileImage.src = '/images/default-avatar.jpg';
                }
                
                // Re-render to hide delete button
                this.render();
                this.setupEventListeners();
            } else {
                this.showMessage(messageEl, data.message || 'Failed to delete profile picture.', 'error');
            }
        } catch (error) {
            console.error('Failed to delete profile picture:', error);
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

export default UserProfileManager; 