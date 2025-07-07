/**
 * NotificationHistoryComponent.js
 * 
 * UI component for displaying the notification history from AgentNotificationSystem
 */

import AgentNotificationSystem from './AgentNotificationSystem';

class NotificationHistoryComponent {
  constructor() {
    this.container = null;
    this.isVisible = false;
    this.unreadCount = 0;
    this.eventListeners = {};
    this.filterType = null;
    this.onlyUnread = false;
  }

  /**
   * Initialize and render the notification history component
   * 
   * @param {string|HTMLElement} container Container element or selector
   * @param {Object} options Component options
   * @returns {NotificationHistoryComponent} This instance for chaining
   */
  init(container, options = {}) {
    if (typeof container === 'string') {
      this.container = document.querySelector(container);
    } else {
      this.container = container;
    }

    if (!this.container) {
      console.error('NotificationHistoryComponent: Container not found');
      return this;
    }

    // Create trigger button if specified
    if (options.createTrigger) {
      this.createTriggerButton(options.triggerOptions);
    }
    
    // Create the panel (initially hidden)
    this.createPanel();

    // Attach event listeners
    this.attachEventListeners();

    return this;
  }

  /**
   * Create the notification history trigger button
   * 
   * @param {Object} options Trigger button options
   */
  createTriggerButton(options = {}) {
    const triggerButton = document.createElement('button');
    triggerButton.className = options.className || 'notification-trigger';
    triggerButton.innerHTML = options.icon || '<span class="notification-icon">🔔</span>';
    
    // Add badge for unread count
    const badge = document.createElement('span');
    badge.className = 'notification-badge';
    badge.style.display = 'none';
    triggerButton.appendChild(badge);
    
    // Add click handler
    triggerButton.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      this.togglePanel();
    });
    
    // Add to DOM
    this.container.appendChild(triggerButton);
    this.triggerButton = triggerButton;
    this.badge = badge;
    
    // Update badge count
    this.updateBadge();
  }

  /**
   * Create the notification history panel
   */
  createPanel() {
    // Create panel element
    const panel = document.createElement('div');
    panel.className = 'notification-history-panel';
    panel.style.display = 'none';
    
    // Create header
    const header = document.createElement('div');
    header.className = 'notification-history-header';
    header.innerHTML = `
      <span>Notifications</span>
      <div class="notification-history-actions">
        <span class="notification-history-action" data-action="mark-all-read">Mark all read</span>
        <span class="notification-history-action" data-action="filter">Filter</span>
      </div>
    `;
    
    // Create notifications list
    const notificationsList = document.createElement('div');
    notificationsList.className = 'notification-history-list';
    
    // Create preferences section
    const preferences = document.createElement('div');
    preferences.className = 'notification-preferences';
    preferences.innerHTML = `
      <div class="notification-preference-item">
        <div class="notification-preference-label">
          <span class="notification-preference-icon">🔔</span>
          <span class="notification-preference-title">In-app notifications</span>
        </div>
        <label class="notification-preference-toggle">
          <input type="checkbox" name="inApp" checked>
          <span class="notification-preference-slider"></span>
        </label>
      </div>
      <div class="notification-preference-item">
        <div class="notification-preference-label">
          <span class="notification-preference-icon">🔊</span>
          <span class="notification-preference-title">Sound alerts</span>
        </div>
        <label class="notification-preference-toggle">
          <input type="checkbox" name="sound" checked>
          <span class="notification-preference-slider"></span>
        </label>
      </div>
      <div class="notification-preference-item">
        <div class="notification-preference-label">
          <span class="notification-preference-icon">🖥️</span>
          <span class="notification-preference-title">Browser notifications</span>
        </div>
        <label class="notification-preference-toggle">
          <input type="checkbox" name="browser" checked>
          <span class="notification-preference-slider"></span>
        </label>
      </div>
    `;
    
    // Assemble panel
    panel.appendChild(header);
    panel.appendChild(notificationsList);
    panel.appendChild(preferences);
    
    // Add to DOM
    this.container.appendChild(panel);
    this.panel = panel;
    this.notificationsList = notificationsList;
    
    // Add initial notifications
    this.renderNotifications();
  }

  /**
   * Attach event listeners
   */
  attachEventListeners() {
    // Click outside to close
    document.addEventListener('click', (e) => {
      if (this.isVisible && !this.panel.contains(e.target) && 
          (!this.triggerButton || !this.triggerButton.contains(e.target))) {
        this.hidePanel();
      }
    });
    
    // Panel action buttons
    if (this.panel) {
      this.panel.addEventListener('click', (e) => {
        const action = e.target.closest('[data-action]');
        if (action) {
          const actionType = action.getAttribute('data-action');
          
          switch (actionType) {
            case 'mark-all-read':
              AgentNotificationSystem.markAllAsRead();
              this.renderNotifications();
              break;
              
            case 'filter':
              this.toggleFilter();
              break;
          }
        }
      });
    }
    
    // Preference toggles
    const toggles = this.panel.querySelectorAll('.notification-preference-toggle input');
    toggles.forEach(toggle => {
      toggle.addEventListener('change', () => {
        const preferences = {
          inApp: this.panel.querySelector('input[name="inApp"]').checked,
          sound: this.panel.querySelector('input[name="sound"]').checked,
          browser: this.panel.querySelector('input[name="browser"]').checked
        };
        
        AgentNotificationSystem.setPreferences(preferences);
      });
    });
    
    // Listen for notification events
    document.addEventListener('agentNotification:historyUpdated', (e) => {
      this.renderNotifications();
    });
    
    document.addEventListener('agentNotification:unreadCountChanged', (e) => {
      this.unreadCount = e.detail.count;
      this.updateBadge();
    });
    
    document.addEventListener('agentNotification:allNotificationsRead', () => {
      this.renderNotifications();
      this.updateBadge();
    });
  }

  /**
   * Render the notifications list
   */
  renderNotifications() {
    // Get notifications with filters
    const notifications = AgentNotificationSystem.getHistory({
      unreadOnly: this.onlyUnread,
      type: this.filterType
    });
    
    // Clear current list
    this.notificationsList.innerHTML = '';
    
    // If no notifications, show empty state
    if (notifications.length === 0) {
      const emptyState = document.createElement('div');
      emptyState.className = 'notification-history-empty';
      emptyState.textContent = 'No notifications';
      this.notificationsList.appendChild(emptyState);
      return;
    }
    
    // Add each notification
    notifications.forEach(notification => {
      const item = document.createElement('div');
      item.className = `notification-history-item ${notification.read ? '' : 'unread'}`;
      item.dataset.id = notification.id;
      
      // Format the timestamp
      let timeDisplay = 'Just now';
      const now = new Date();
      const timestamp = new Date(notification.timestamp);
      const timeDiff = now - timestamp;
      
      if (timeDiff < 60000) { // Less than 1 minute
        timeDisplay = 'Just now';
      } else if (timeDiff < 3600000) { // Less than 1 hour
        const minutes = Math.floor(timeDiff / 60000);
        timeDisplay = `${minutes}m ago`;
      } else if (timeDiff < 86400000) { // Less than 1 day
        const hours = Math.floor(timeDiff / 3600000);
        timeDisplay = `${hours}h ago`;
      } else { // More than 1 day
        timeDisplay = timestamp.toLocaleDateString();
      }
      
      item.innerHTML = `
        <div class="notification-history-title">${notification.title}</div>
        <div class="notification-history-message">${notification.message}</div>
        <div class="notification-history-time">${timeDisplay}</div>
      `;
      
      // Add click handler
      item.addEventListener('click', () => {
        // Mark as read
        AgentNotificationSystem.markAsRead(notification.id);
        
        // Navigate if threadId is available
        if (notification.data && notification.data.threadId) {
          window.location.href = `/threads/${notification.data.threadId}`;
        }
        
        // Hide panel
        this.hidePanel();
      });
      
      this.notificationsList.appendChild(item);
    });
  }

  /**
   * Toggle the notification filter
   */
  toggleFilter() {
    // Cycle through filters: all -> unread only -> info -> success -> warning -> error -> all
    if (!this.onlyUnread && !this.filterType) {
      this.onlyUnread = true;
      this.filterType = null;
    } else if (this.onlyUnread && !this.filterType) {
      this.onlyUnread = false;
      this.filterType = 'info';
    } else if (this.filterType === 'info') {
      this.filterType = 'success';
    } else if (this.filterType === 'success') {
      this.filterType = 'warning';
    } else if (this.filterType === 'warning') {
      this.filterType = 'error';
    } else {
      this.onlyUnread = false;
      this.filterType = null;
    }
    
    // Update filter indicator
    const filterButton = this.panel.querySelector('[data-action="filter"]');
    if (filterButton) {
      if (this.onlyUnread) {
        filterButton.textContent = 'Filter: Unread';
      } else if (this.filterType) {
        filterButton.textContent = `Filter: ${this.filterType}`;
      } else {
        filterButton.textContent = 'Filter';
      }
    }
    
    // Re-render with new filter
    this.renderNotifications();
  }

  /**
   * Update the notification badge
   */
  updateBadge() {
    if (!this.badge) return;
    
    if (this.unreadCount > 0) {
      this.badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
      this.badge.style.display = 'flex';
    } else {
      this.badge.style.display = 'none';
    }
  }

  /**
   * Toggle the notification panel visibility
   */
  togglePanel() {
    if (this.isVisible) {
      this.hidePanel();
    } else {
      this.showPanel();
    }
  }

  /**
   * Show the notification panel
   */
  showPanel() {
    if (!this.panel) return;
    
    this.panel.style.display = 'block';
    this.isVisible = true;
    
    // Set preference toggles to match current preferences
    const preferences = AgentNotificationSystem.preferences;
    
    if (preferences) {
      const toggles = this.panel.querySelectorAll('.notification-preference-toggle input');
      toggles.forEach(toggle => {
        const name = toggle.getAttribute('name');
        if (preferences[name] !== undefined) {
          toggle.checked = preferences[name];
        }
      });
    }
    
    // Re-render notifications
    this.renderNotifications();
  }

  /**
   * Hide the notification panel
   */
  hidePanel() {
    if (!this.panel) return;
    
    this.panel.style.display = 'none';
    this.isVisible = false;
  }
}

// Export as class (not singleton)
export default NotificationHistoryComponent; 