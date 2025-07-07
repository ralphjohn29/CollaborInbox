/**
 * NotificationManager.js
 * 
 * Manages notifications from WebSocket events
 * Provides methods for displaying and handling various types of notifications
 */

class NotificationManager {
    constructor() {
        this.notificationContainer = null;
        this.defaultOptions = {
            duration: 5000,      // Default display duration in milliseconds
            position: 'top-right', // Default position
            closable: true,      // Allow closing notifications
            autoClose: true,     // Automatically close after duration
            maxVisible: 5,       // Maximum number of visible notifications
            cssClasses: {
                container: 'notification-container',
                notification: 'notification',
                info: 'notification-info',
                success: 'notification-success',
                warning: 'notification-warning',
                error: 'notification-error',
                closeBtn: 'notification-close'
            }
        };
        
        this.activeNotifications = [];
        this._initContainer();
    }

    /**
     * Initialize the notification container
     * @private
     */
    _initContainer() {
        // Check if container already exists
        this.notificationContainer = document.querySelector(`.${this.defaultOptions.cssClasses.container}`);
        
        if (!this.notificationContainer) {
            // Create container if it doesn't exist
            this.notificationContainer = document.createElement('div');
            this.notificationContainer.className = this.defaultOptions.cssClasses.container;
            this.notificationContainer.setAttribute('role', 'alert');
            
            // Position the container
            this.notificationContainer.style.position = 'fixed';
            this.notificationContainer.style.zIndex = '9999';
            
            // Set position based on default option
            switch (this.defaultOptions.position) {
                case 'top-right':
                    this.notificationContainer.style.top = '20px';
                    this.notificationContainer.style.right = '20px';
                    break;
                case 'top-left':
                    this.notificationContainer.style.top = '20px';
                    this.notificationContainer.style.left = '20px';
                    break;
                case 'bottom-right':
                    this.notificationContainer.style.bottom = '20px';
                    this.notificationContainer.style.right = '20px';
                    break;
                case 'bottom-left':
                    this.notificationContainer.style.bottom = '20px';
                    this.notificationContainer.style.left = '20px';
                    break;
                default:
                    this.notificationContainer.style.top = '20px';
                    this.notificationContainer.style.right = '20px';
            }
            
            document.body.appendChild(this.notificationContainer);
        }
    }

    /**
     * Show a notification
     * 
     * @param {Object} options Notification options
     * @param {string} options.title Notification title
     * @param {string} options.message Notification message
     * @param {string} options.type Notification type (info, success, warning, error)
     * @param {number} options.duration Duration in milliseconds
     * @param {boolean} options.closable Whether notification can be closed
     * @param {Object} options.data Additional data for the notification
     * @returns {string} Notification ID
     */
    show({ title, message, type = 'info', duration, closable, data = {} }) {
        // Check if container exists, create if not
        if (!this.notificationContainer) {
            this._initContainer();
        }
        
        // Generate unique ID for this notification
        const id = `notification-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `${this.defaultOptions.cssClasses.notification} ${this.defaultOptions.cssClasses[type] || this.defaultOptions.cssClasses.info}`;
        notification.id = id;
        notification.dataset.type = type;
        
        // Create title element if provided
        if (title) {
            const titleElement = document.createElement('div');
            titleElement.className = 'notification-title';
            titleElement.textContent = title;
            notification.appendChild(titleElement);
        }
        
        // Create message element
        const messageElement = document.createElement('div');
        messageElement.className = 'notification-message';
        messageElement.textContent = message;
        notification.appendChild(messageElement);
        
        // Add close button if closable
        if (closable !== false && this.defaultOptions.closable) {
            const closeBtn = document.createElement('button');
            closeBtn.className = this.defaultOptions.cssClasses.closeBtn;
            closeBtn.innerHTML = '&times;';
            closeBtn.setAttribute('aria-label', 'Close notification');
            closeBtn.onclick = () => this.close(id);
            notification.appendChild(closeBtn);
        }
        
        // Store reference to notification data
        this.activeNotifications.push({
            id,
            element: notification,
            type,
            data,
            timeoutId: null
        });
        
        // Limit number of visible notifications
        this._enforceMaxVisible();
        
        // Add to DOM
        this.notificationContainer.appendChild(notification);
        
        // Set auto-close timer
        const notificationDuration = duration || this.defaultOptions.duration;
        if (this.defaultOptions.autoClose && notificationDuration) {
            const timeoutId = setTimeout(() => {
                this.close(id);
            }, notificationDuration);
            
            // Store timeout ID to cancel if needed
            const index = this.activeNotifications.findIndex(n => n.id === id);
            if (index >= 0) {
                this.activeNotifications[index].timeoutId = timeoutId;
            }
        }
        
        return id;
    }

    /**
     * Close a notification by ID
     * 
     * @param {string} id Notification ID
     */
    close(id) {
        const index = this.activeNotifications.findIndex(n => n.id === id);
        
        if (index >= 0) {
            const notification = this.activeNotifications[index];
            
            // Cancel timeout if exists
            if (notification.timeoutId) {
                clearTimeout(notification.timeoutId);
            }
            
            // Add closing animation class
            notification.element.classList.add('notification-closing');
            
            // Remove from DOM after animation
            setTimeout(() => {
                if (notification.element.parentNode) {
                    notification.element.parentNode.removeChild(notification.element);
                }
                
                // Remove from active notifications
                this.activeNotifications.splice(index, 1);
            }, 300); // Animation duration
        }
    }

    /**
     * Close all active notifications
     */
    closeAll() {
        // Create a copy of the array since we'll be modifying it during iteration
        const notificationIds = [...this.activeNotifications].map(n => n.id);
        
        notificationIds.forEach(id => {
            this.close(id);
        });
    }

    /**
     * Show an info notification
     * 
     * @param {string} message Notification message
     * @param {string} title Optional notification title
     * @param {Object} options Additional options
     * @returns {string} Notification ID
     */
    info(message, title = '', options = {}) {
        return this.show({
            title,
            message,
            type: 'info',
            ...options
        });
    }

    /**
     * Show a success notification
     * 
     * @param {string} message Notification message
     * @param {string} title Optional notification title
     * @param {Object} options Additional options
     * @returns {string} Notification ID
     */
    success(message, title = '', options = {}) {
        return this.show({
            title,
            message,
            type: 'success',
            ...options
        });
    }

    /**
     * Show a warning notification
     * 
     * @param {string} message Notification message
     * @param {string} title Optional notification title
     * @param {Object} options Additional options
     * @returns {string} Notification ID
     */
    warning(message, title = '', options = {}) {
        return this.show({
            title,
            message,
            type: 'warning',
            ...options
        });
    }

    /**
     * Show an error notification
     * 
     * @param {string} message Notification message
     * @param {string} title Optional notification title
     * @param {Object} options Additional options
     * @returns {string} Notification ID
     */
    error(message, title = '', options = {}) {
        return this.show({
            title,
            message,
            type: 'error',
            ...options
        });
    }

    /**
     * Enforce maximum number of visible notifications
     * @private
     */
    _enforceMaxVisible() {
        if (this.activeNotifications.length > this.defaultOptions.maxVisible) {
            // Close oldest notifications
            const toRemove = this.activeNotifications.length - this.defaultOptions.maxVisible;
            
            for (let i = 0; i < toRemove; i++) {
                this.close(this.activeNotifications[i].id);
            }
        }
    }

    /**
     * Set default options
     * 
     * @param {Object} options Options to override defaults
     */
    setOptions(options) {
        this.defaultOptions = {
            ...this.defaultOptions,
            ...options
        };
        
        // Reinitialize container if position changed
        if (options.position) {
            if (this.notificationContainer) {
                // Remove existing container
                if (this.notificationContainer.parentNode) {
                    this.notificationContainer.parentNode.removeChild(this.notificationContainer);
                }
                this.notificationContainer = null;
            }
            
            this._initContainer();
        }
    }
}

// Export as singleton
export default new NotificationManager(); 