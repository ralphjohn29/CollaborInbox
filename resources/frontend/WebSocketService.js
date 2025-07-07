/**
 * WebSocketService.js
 * 
 * Service for managing WebSocket connections and channels using Laravel Echo
 * Provides methods for subscribing to tenant and thread events
 */

class WebSocketService {
  constructor() {
    this.echo = null;
    this.tenantId = null;
    this.userId = null;
    this.isConnected = false;
    this.activeSubscriptions = {
      tenant: null,
      threads: {},
      user: null
    };
  }

  /**
   * Initialize the WebSocket connection with the Echo server
   * 
   * @param {Object} options Configuration options
   * @param {string} options.tenantId The current tenant ID
   * @param {string} options.userId The current user ID
   * @param {string} options.authToken JWT auth token
   * @returns {Promise} Promise resolving when connection is established
   */
  async init({ tenantId, userId, authToken }) {
    // Validate required parameters
    if (!tenantId || !userId) {
      throw new Error('tenantId and userId are required');
    }
    
    this.tenantId = tenantId;
    this.userId = userId;

    return new Promise(async (resolve, reject) => {
      try {
        // Dynamically import Echo and Socket.io-client
        const { default: Echo } = await import('laravel-echo');
        const { io } = await import('socket.io-client');
        
        window.io = io; // Required for Echo
        
        // Initialize Laravel Echo
        this.echo = new Echo({
          broadcaster: 'socket.io',
          host: window.location.hostname + ':6001', // Default Laravel Echo Server port
          auth: {
            headers: {
              Authorization: `Bearer ${authToken}`,
            },
          },
        });
        
        this.isConnected = true;
        console.log('WebSocket connection established');
        resolve();
      } catch (error) {
        console.error('Failed to connect to WebSocket server:', error);
        reject(error);
      }
    });
  }

  /**
   * Subscribe to all tenant thread updates
   * 
   * @param {Function} callback Function to call when updates are received
   * @returns {boolean} Success status
   */
  subscribeToTenantThreads(callback) {
    if (!this.isConnected || !this.echo) {
      console.error('WebSocket not connected. Call init() first.');
      return false;
    }
    
    try {
      // Unsubscribe from existing subscription if any
      if (this.activeSubscriptions.tenant) {
        this.activeSubscriptions.tenant.unsubscribe();
        this.activeSubscriptions.tenant = null;
      }
      
      // Create new subscription
      const channel = `tenant.${this.tenantId}.threads`;
      this.activeSubscriptions.tenant = this.echo.private(channel)
        .listen('ThreadUpdatedEvent', (data) => {
          callback('update', data);
        })
        .listen('NewMessageEvent', (data) => {
          callback('message', data);
        })
        .listen('ThreadAssignedEvent', (data) => {
          callback('assignment', data);
        });
      
      console.log(`Subscribed to tenant threads: ${channel}`);
      return true;
    } catch (error) {
      console.error('Failed to subscribe to tenant threads:', error);
      return false;
    }
  }

  /**
   * Subscribe to a specific thread's updates
   * 
   * @param {string} threadId The thread ID to subscribe to
   * @param {Function} callback Function to call when updates are received
   * @returns {boolean} Success status
   */
  subscribeToThread(threadId, callback) {
    if (!this.isConnected || !this.echo) {
      console.error('WebSocket not connected. Call init() first.');
      return false;
    }
    
    if (!threadId) {
      console.error('threadId is required');
      return false;
    }
    
    try {
      // Unsubscribe from existing subscription for this thread if any
      if (this.activeSubscriptions.threads[threadId]) {
        this.activeSubscriptions.threads[threadId].unsubscribe();
        delete this.activeSubscriptions.threads[threadId];
      }
      
      // Create new subscription
      const channel = `tenant.${this.tenantId}.thread.${threadId}`;
      this.activeSubscriptions.threads[threadId] = this.echo.private(channel)
        .listen('ThreadUpdatedEvent', (data) => {
          callback('update', data);
        })
        .listen('NewMessageEvent', (data) => {
          callback('message', data);
        })
        .listen('ThreadAssignedEvent', (data) => {
          callback('assignment', data);
        });
      
      console.log(`Subscribed to thread: ${channel}`);
      return true;
    } catch (error) {
      console.error(`Failed to subscribe to thread ${threadId}:`, error);
      return false;
    }
  }

  /**
   * Unsubscribe from a specific thread's updates
   * 
   * @param {string} threadId The thread ID to unsubscribe from
   * @returns {boolean} Success status
   */
  unsubscribeFromThread(threadId) {
    if (!this.activeSubscriptions.threads[threadId]) {
      return false;
    }
    
    try {
      this.activeSubscriptions.threads[threadId].unsubscribe();
      delete this.activeSubscriptions.threads[threadId];
      console.log(`Unsubscribed from thread: ${threadId}`);
      return true;
    } catch (error) {
      console.error(`Failed to unsubscribe from thread ${threadId}:`, error);
      return false;
    }
  }

  /**
   * Subscribe to user-specific notifications
   * 
   * @param {Function} callback Function to call when notifications are received
   * @returns {boolean} Success status
   */
  subscribeToUserNotifications(callback) {
    if (!this.isConnected || !this.echo) {
      console.error('WebSocket not connected. Call init() first.');
      return false;
    }
    
    try {
      // Unsubscribe from existing subscription if any
      if (this.activeSubscriptions.user) {
        this.activeSubscriptions.user.unsubscribe();
        this.activeSubscriptions.user = null;
      }
      
      // Create new subscription
      const channel = `tenant.${this.tenantId}.user.${this.userId}`;
      this.activeSubscriptions.user = this.echo.private(channel)
        .listen('UserNotificationEvent', (data) => {
          callback(data);
        });
      
      console.log(`Subscribed to user notifications: ${channel}`);
      return true;
    } catch (error) {
      console.error('Failed to subscribe to user notifications:', error);
      return false;
    }
  }

  /**
   * Disconnect from all channels and close the WebSocket connection
   */
  disconnect() {
    if (!this.isConnected || !this.echo) {
      return;
    }
    
    try {
      // Unsubscribe from tenant updates
      if (this.activeSubscriptions.tenant) {
        this.activeSubscriptions.tenant.unsubscribe();
        this.activeSubscriptions.tenant = null;
      }
      
      // Unsubscribe from all thread updates
      Object.keys(this.activeSubscriptions.threads).forEach(threadId => {
        this.activeSubscriptions.threads[threadId].unsubscribe();
        delete this.activeSubscriptions.threads[threadId];
      });
      
      // Unsubscribe from user notifications
      if (this.activeSubscriptions.user) {
        this.activeSubscriptions.user.unsubscribe();
        this.activeSubscriptions.user = null;
      }
      
      // Disconnect Echo
      if (this.echo.disconnect) {
        this.echo.disconnect();
      }
      
      this.echo = null;
      this.isConnected = false;
      console.log('WebSocket connection closed');
    } catch (error) {
      console.error('Error during WebSocket disconnect:', error);
    }
  }

  /**
   * Check if currently connected to WebSocket server
   * 
   * @returns {boolean} Connection status
   */
  isConnected() {
    return this.isConnected && this.echo !== null;
  }
}

// Export as singleton
export default new WebSocketService(); 