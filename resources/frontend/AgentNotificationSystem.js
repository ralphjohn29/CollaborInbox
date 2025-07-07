/**
 * AgentNotificationSystem.js
 * 
 * Extends the base NotificationManager with agent-specific notification features
 * Handles notification preferences, sound alerts, browser notifications, and history
 */

import NotificationManager from './NotificationManager';

class AgentNotificationSystem {
  constructor() {
    this.notificationHistory = [];
    this.unreadCount = 0;
    this.maxHistorySize = 100;
    this.preferences = {
      email: true,
      inApp: true,
      sound: true,
      browser: true
    };
    this.initialized = false;
    this.notificationSound = null;
    this.browserNotificationsEnabled = false;
  }

  /**
   * Initialize the agent notification system
   * 
   * @param {Object} options Configuration options
   * @returns {AgentNotificationSystem} This instance for chaining
   */
  init(options = {}) {
    if (this.initialized) return this;
    
    // Set initial preferences from options or localStorage
    this.loadPreferences();
    
    if (options.preferences) {
      this.setPreferences(options.preferences);
    }
    
    // Configure NotificationManager
    NotificationManager.setOptions({
      position: options.position || 'top-right',
      maxVisible: options.maxVisible || 5,
      duration: options.duration || 8000
    });
    
    // Initialize notification sound
    this.initNotificationSound(options.soundUrl);
    
    // Request browser notification permissions if enabled
    if (this.preferences.browser) {
      this.requestBrowserPermissions();
    }
    
    // Add event listener for page visibility to reset unread counter
    document.addEventListener('visibilitychange', () => {
      if (!document.hidden) {
        this.resetUnreadCounter();
      }
    });
    
    this.initialized = true;
    return this;
  }

  /**
   * Load saved preferences from localStorage
   */
  loadPreferences() {
    try {
      const savedPrefs = localStorage.getItem('agent-notification-preferences');
      if (savedPrefs) {
        this.preferences = JSON.parse(savedPrefs);
      }
    } catch (error) {
      console.error('Failed to load notification preferences:', error);
    }
  }

  /**
   * Save preferences to localStorage
   */
  savePreferences() {
    try {
      localStorage.setItem('agent-notification-preferences', JSON.stringify(this.preferences));
    } catch (error) {
      console.error('Failed to save notification preferences:', error);
    }
  }

  /**
   * Initialize notification sound
   * 
   * @param {string} soundUrl URL to the notification sound file
   */
  initNotificationSound(soundUrl = '/sounds/notification.mp3') {
    try {
      this.notificationSound = new Audio(soundUrl);
      this.notificationSound.preload = 'auto';
      this.notificationSound.load();
      
      // Automatically retry loading sound if it fails
      this.notificationSound.addEventListener('error', (e) => {
        console.warn('Error loading notification sound, retrying in 3 seconds', e);
        setTimeout(() => {
          this.notificationSound.load();
        }, 3000);
      });
    } catch (error) {
      console.error('Failed to initialize notification sound:', error);
    }
  }

  /**
   * Request browser notification permissions
   */
  requestBrowserPermissions() {
    if (!('Notification' in window)) {
      console.warn('This browser does not support desktop notifications');
      this.browserNotificationsEnabled = false;
      return;
    }
    
    if (Notification.permission === 'granted') {
      this.browserNotificationsEnabled = true;
    } else if (Notification.permission !== 'denied') {
      Notification.requestPermission().then(permission => {
        this.browserNotificationsEnabled = permission === 'granted';
      });
    }
  }

  /**
   * Set notification preferences
   * 
   * @param {Object} preferences Notification preferences
   * @param {boolean} preferences.email Enable email notifications
   * @param {boolean} preferences.inApp Enable in-app notifications
   * @param {boolean} preferences.sound Enable sound alerts
   * @param {boolean} preferences.browser Enable browser notifications
   */
  setPreferences(preferences) {
    this.preferences = {
      ...this.preferences,
      ...preferences
    };
    
    // If browser notifications are enabled, request permissions
    if (this.preferences.browser) {
      this.requestBrowserPermissions();
    }
    
    // Save updated preferences
    this.savePreferences();
    
    return this;
  }

  /**
   * Show a notification through all enabled channels
   * 
   * @param {Object} options Notification options
   * @param {string} options.title Notification title
   * @param {string} options.message Notification message
   * @param {string} options.type Type of notification (info, success, warning, error)
   * @param {Object} options.data Additional data about the notification
   * @param {boolean} options.important Whether this notification is important
   * @returns {string} Notification ID
   */
  notify(options) {
    if (!this.initialized) {
      console.error('AgentNotificationSystem: Not initialized. Call init() first.');
      return null;
    }
    
    const { title, message, type = 'info', data = {}, important = false } = options;
    
    // Add to notification history
    const notificationId = this.addToHistory({
      id: `notification-${Date.now()}`,
      title,
      message,
      type,
      data,
      timestamp: new Date(),
      read: false
    });
    
    // Update unread counter if page not visible
    if (document.hidden) {
      this.unreadCount++;
      this.updateUnreadBadge();
    }
    
    // Show in-app notification if enabled
    if (this.preferences.inApp) {
      NotificationManager.show({
        title,
        message,
        type,
        data
      });
    }
    
    // Play sound for important notifications if enabled
    if (this.preferences.sound && (important || type === 'warning' || type === 'error')) {
      this.playNotificationSound();
    }
    
    // Show browser notification if enabled
    if (this.preferences.browser && this.browserNotificationsEnabled && 
        (document.hidden || important)) {
      this.showBrowserNotification(title, message, data);
    }
    
    // Trigger events for parent components to handle
    this.triggerEvent('notification', { id: notificationId, title, message, type, data });
    
    return notificationId;
  }

  /**
   * Play notification sound
   */
  playNotificationSound() {
    if (!this.notificationSound || !this.preferences.sound) return;
    
    try {
      // Reset the sound to the beginning
      this.notificationSound.currentTime = 0;
      
      // Play the sound
      this.notificationSound.play().catch(error => {
        console.warn('Failed to play notification sound:', error);
      });
    } catch (error) {
      console.error('Error playing notification sound:', error);
    }
  }

  /**
   * Show browser notification
   * 
   * @param {string} title Notification title
   * @param {string} message Notification message
   * @param {Object} data Additional notification data
   */
  showBrowserNotification(title, message, data = {}) {
    if (!this.browserNotificationsEnabled || !this.preferences.browser) return;
    
    try {
      const notification = new Notification(title, {
        body: message,
        icon: data.icon || '/images/logo-icon.png',
        tag: data.threadId || 'general',
        requireInteraction: data.important || false
      });
      
      // Handle notification click
      notification.onclick = () => {
        // Focus on the window
        window.focus();
        
        // Navigate to thread if threadId is provided
        if (data.threadId) {
          window.location.href = `/threads/${data.threadId}`;
        }
        
        // Close the notification
        notification.close();
      };
      
      // Auto close after 10 seconds if not important
      if (!data.important) {
        setTimeout(() => {
          notification.close();
        }, 10000);
      }
    } catch (error) {
      console.error('Error showing browser notification:', error);
    }
  }

  /**
   * Add notification to history
   * 
   * @param {Object} notification Notification object
   * @returns {string} Notification ID
   */
  addToHistory(notification) {
    // Add to beginning of array
    this.notificationHistory.unshift(notification);
    
    // Limit history size
    if (this.notificationHistory.length > this.maxHistorySize) {
      this.notificationHistory = this.notificationHistory.slice(0, this.maxHistorySize);
    }
    
    // Trigger history updated event
    this.triggerEvent('historyUpdated', { 
      history: this.notificationHistory,
      unreadCount: this.unreadCount
    });
    
    return notification.id;
  }

  /**
   * Get notification history
   * 
   * @param {Object} options Filter options
   * @param {boolean} options.unreadOnly Get only unread notifications
   * @param {string} options.type Filter by notification type
   * @returns {Array} Filtered notification history
   */
  getHistory(options = {}) {
    let filtered = [...this.notificationHistory];
    
    // Apply filters
    if (options.unreadOnly) {
      filtered = filtered.filter(notification => !notification.read);
    }
    
    if (options.type) {
      filtered = filtered.filter(notification => notification.type === options.type);
    }
    
    return filtered;
  }

  /**
   * Mark a notification as read
   * 
   * @param {string} id Notification ID
   * @returns {boolean} Success status
   */
  markAsRead(id) {
    const notification = this.notificationHistory.find(n => n.id === id);
    
    if (notification && !notification.read) {
      notification.read = true;
      
      // Update unread counter
      this.recalculateUnreadCount();
      
      // Trigger event
      this.triggerEvent('notificationRead', { id });
      
      return true;
    }
    
    return false;
  }

  /**
   * Mark all notifications as read
   */
  markAllAsRead() {
    this.notificationHistory.forEach(notification => {
      notification.read = true;
    });
    
    // Reset unread counter
    this.unreadCount = 0;
    this.updateUnreadBadge();
    
    // Trigger event
    this.triggerEvent('allNotificationsRead');
  }

  /**
   * Recalculate the unread notification count
   */
  recalculateUnreadCount() {
    this.unreadCount = this.notificationHistory.filter(n => !n.read).length;
    this.updateUnreadBadge();
  }

  /**
   * Reset unread counter
   */
  resetUnreadCounter() {
    this.unreadCount = 0;
    this.updateUnreadBadge();
  }

  /**
   * Update unread badge count in UI
   */
  updateUnreadBadge() {
    // Update badge in UI (document title)
    if (this.unreadCount > 0) {
      document.title = `(${this.unreadCount}) ${document.title.replace(/^\(\d+\)\s/, '')}`;
    } else {
      document.title = document.title.replace(/^\(\d+\)\s/, '');
    }
    
    // Dispatch event for other components to listen to
    this.triggerEvent('unreadCountChanged', { count: this.unreadCount });
  }

  /**
   * Clear all notifications
   */
  clearAll() {
    this.notificationHistory = [];
    this.unreadCount = 0;
    this.updateUnreadBadge();
    
    // Clear in-app notifications
    NotificationManager.closeAll();
    
    // Trigger event
    this.triggerEvent('notificationsCleared');
  }

  /**
   * Handle different notification event types
   * 
   * @param {Object} event Event data
   * @returns {string|null} Notification ID if shown
   */
  handleEvent(event) {
    if (!event || !event.type) return null;
    
    switch (event.type) {
      case 'assignment':
        return this.notify({
          title: 'Thread Assigned',
          message: event.data.isYou ? 
            `Thread "${event.data.thread.subject}" has been assigned to you` : 
            `Thread "${event.data.thread.subject}" assigned to ${event.data.assignee.name}`,
          type: 'success',
          data: { 
            threadId: event.data.thread.id,
            important: event.data.isYou
          },
          important: event.data.isYou
        });
        
      case 'message':
        return this.notify({
          title: 'New Message',
          message: `New message in thread "${event.data.thread.subject}"`,
          type: 'info',
          data: { 
            threadId: event.data.thread.id,
            important: event.data.isYour
          },
          important: event.data.isYour
        });
        
      case 'update':
        return this.notify({
          title: 'Thread Updated',
          message: `Thread "${event.data.thread.subject}" has been updated`,
          type: 'info',
          data: { 
            threadId: event.data.thread.id 
          }
        });
        
      case 'mention':
        return this.notify({
          title: 'You were mentioned',
          message: `${event.data.user.name} mentioned you in "${event.data.thread.subject}"`,
          type: 'warning',
          data: { 
            threadId: event.data.thread.id,
            important: true
          },
          important: true
        });
        
      default:
        // For generic notifications
        return this.notify({
          title: event.title || 'Notification',
          message: event.message || '',
          type: event.priority || 'info',
          data: event.data || {},
          important: event.important || false
        });
    }
  }

  /**
   * Group similar notifications to prevent notification spam
   * 
   * @param {Array} notifications Array of notification objects
   * @returns {Array} Grouped notifications
   */
  groupNotifications(notifications) {
    const grouped = [];
    const groups = {};
    
    notifications.forEach(notification => {
      // Create group key based on type and threadId if exists
      const key = `${notification.type}-${notification.data?.threadId || 'general'}`;
      
      if (groups[key]) {
        groups[key].count++;
        
        // Update the message to show count
        if (groups[key].count === 2) {
          groups[key].notification.message = `${notification.message} and 1 more notification`;
        } else {
          groups[key].notification.message = `${notification.message} and ${groups[key].count - 1} more notifications`;
        }
      } else {
        groups[key] = {
          notification: { ...notification },
          count: 1
        };
        grouped.push(groups[key].notification);
      }
    });
    
    return grouped;
  }

  /**
   * Trigger custom event
   * 
   * @param {string} eventName Event name
   * @param {Object} data Event data
   */
  triggerEvent(eventName, data = {}) {
    const event = new CustomEvent(`agentNotification:${eventName}`, {
      detail: data,
      bubbles: true,
      cancelable: true
    });
    
    document.dispatchEvent(event);
  }
  
  /**
   * Clean up resources when no longer needed
   */
  cleanup() {
    // Clear notification sound
    if (this.notificationSound) {
      this.notificationSound.pause();
      this.notificationSound.src = '';
      this.notificationSound = null;
    }
    
    // Clear event listeners
    document.removeEventListener('visibilitychange', this.resetUnreadCounter);
    
    // Clear notification history (optional)
    // this.clearAll();
    
    this.initialized = false;
  }
}

// Export as singleton
export default new AgentNotificationSystem(); 