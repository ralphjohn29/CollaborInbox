/**
 * WebSocketExample.js
 * Example implementation of WebSocket listeners with UI integration
 */

import WebSocketService from './WebSocketService';
import NotificationManager from './NotificationManager';

class WebSocketExample {
    constructor() {
        this.initialized = false;
        this.activeThreadId = null;
    }

    /**
     * Initialize WebSocket connections and listeners
     * @param {Object} config - Configuration with tenantId and userId
     */
    async init(config) {
        if (this.initialized) return;

        const { tenantId, userId, authToken } = config;

        if (!tenantId || !userId) {
            console.error('WebSocketExample: Missing required parameters');
            return;
        }

        // Initialize notification manager
        NotificationManager.init();

        try {
            // Initialize WebSocket service
            await WebSocketService.init({ tenantId, userId, authToken });
            
            // Subscribe to tenant-wide thread updates
            this.subscribeTenantThreads();
            
            // Subscribe to user notifications
            this.subscribeUserNotifications();
            
            this.initialized = true;
            console.log('WebSocket connections initialized successfully');
            
            // Show success notification
            NotificationManager.show({
                title: 'Connected',
                message: 'Real-time updates are now active',
                type: 'success',
                duration: 3000
            });
            
        } catch (error) {
            console.error('Failed to initialize WebSocket connections:', error);
            
            // Show error notification
            NotificationManager.show({
                title: 'Connection Error',
                message: 'Could not establish real-time connection. Some features may be unavailable.',
                type: 'error',
                duration: 8000
            });
        }
    }

    /**
     * Subscribe to tenant-wide thread updates
     */
    subscribeTenantThreads() {
        WebSocketService.subscribeToTenantThreads((data) => {
            console.log('Tenant thread update received:', data);
            
            // Update thread list UI if visible
            this.updateThreadListUI(data);
            
            // Show notification for important updates
            if (data.important) {
                NotificationManager.show({
                    title: 'Thread Updated',
                    message: `${data.thread.subject} has been updated`,
                    type: 'info',
                    threadId: data.thread.id
                });
            }
        });
    }

    /**
     * Subscribe to user-specific notifications
     */
    subscribeUserNotifications() {
        WebSocketService.subscribeToUserNotifications((data) => {
            console.log('User notification received:', data);
            
            // Show notification based on type
            switch (data.type) {
                case 'assignment':
                    NotificationManager.show({
                        title: 'Thread Assigned',
                        message: `You've been assigned to "${data.thread.subject}"`,
                        type: 'success',
                        threadId: data.thread.id
                    });
                    break;
                    
                case 'mention':
                    NotificationManager.show({
                        title: 'You were mentioned',
                        message: `${data.user.name} mentioned you in "${data.thread.subject}"`,
                        type: 'info',
                        threadId: data.thread.id
                    });
                    break;
                    
                case 'reply':
                    NotificationManager.show({
                        title: 'New Reply',
                        message: `New reply in "${data.thread.subject}"`,
                        type: 'info',
                        threadId: data.thread.id
                    });
                    break;
                    
                default:
                    NotificationManager.show({
                        title: 'Notification',
                        message: data.message || 'You have a new notification',
                        type: 'info',
                        threadId: data.thread?.id
                    });
            }
        });
    }

    /**
     * Set active thread and subscribe to thread-specific updates
     * @param {string} threadId - ID of the thread to activate
     */
    setActiveThread(threadId) {
        // Unsubscribe from previous thread if any
        if (this.activeThreadId && this.activeThreadId !== threadId) {
            WebSocketService.unsubscribeFromThread(this.activeThreadId);
        }
        
        this.activeThreadId = threadId;
        
        if (threadId) {
            // Subscribe to the new active thread
            WebSocketService.subscribeToThread(threadId, (eventType, data) => {
                console.log(`Thread ${threadId} event:`, eventType, data);
                
                switch (eventType) {
                    case 'update':
                        this.handleThreadUpdate(data);
                        break;
                        
                    case 'message':
                        this.handleNewMessage(data);
                        break;
                        
                    case 'assignment':
                        this.handleAssignmentChange(data);
                        break;
                }
            });
            
            console.log(`Subscribed to real-time updates for thread ${threadId}`);
        }
    }

    /**
     * Handle thread update events
     * @param {Object} data - Thread update data
     */
    handleThreadUpdate(data) {
        // Update thread details in UI
        const threadSubjectElement = document.querySelector('.thread-subject');
        if (threadSubjectElement) {
            threadSubjectElement.textContent = data.thread.subject;
        }
        
        // Update thread status if present
        const threadStatusElement = document.querySelector('.thread-status');
        if (threadStatusElement && data.thread.status) {
            threadStatusElement.textContent = data.thread.status;
            threadStatusElement.className = `thread-status status-${data.thread.status.toLowerCase()}`;
        }
        
        // Show subtle notification for updates
        NotificationManager.show({
            title: 'Thread Updated',
            message: 'This thread was just updated',
            type: 'info',
            duration: 3000
        });
    }

    /**
     * Handle new message events
     * @param {Object} data - New message data
     */
    handleNewMessage(data) {
        // Add new message to the UI
        const messagesContainer = document.querySelector('.thread-messages');
        if (messagesContainer && data.message) {
            const messageElement = document.createElement('div');
            messageElement.className = 'message';
            messageElement.innerHTML = `
                <div class="message-header">
                    <span class="message-sender">${data.message.sender}</span>
                    <span class="message-time">${new Date(data.message.timestamp).toLocaleTimeString()}</span>
                </div>
                <div class="message-body">${data.message.body}</div>
            `;
            
            messagesContainer.appendChild(messageElement);
            
            // Scroll to the new message
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        // Play notification sound for new messages
        NotificationManager.playNotificationSound();
    }

    /**
     * Handle assignment change events
     * @param {Object} data - Assignment data
     */
    handleAssignmentChange(data) {
        // Update assignment display in UI
        const assigneeElement = document.querySelector('.thread-assignee');
        if (assigneeElement) {
            assigneeElement.textContent = data.assignee ? data.assignee.name : 'Unassigned';
        }
        
        // Show notification for assignment change
        NotificationManager.show({
            title: 'Assignment Changed',
            message: data.assignee ? 
                `This thread is now assigned to ${data.assignee.name}` : 
                'This thread is now unassigned',
            type: 'warning',
            duration: 5000
        });
    }

    /**
     * Update thread list UI based on real-time updates
     * @param {Object} data - Thread update data
     */
    updateThreadListUI(data) {
        // This is a simplified example - in a real app, you would likely use a framework's state management
        
        const threadListItem = document.querySelector(`.thread-list-item[data-id="${data.thread.id}"]`);
        
        if (threadListItem) {
            // Update existing thread list item
            const subjectElement = threadListItem.querySelector('.thread-list-subject');
            if (subjectElement) {
                subjectElement.textContent = data.thread.subject;
            }
            
            const statusElement = threadListItem.querySelector('.thread-list-status');
            if (statusElement && data.thread.status) {
                statusElement.textContent = data.thread.status;
                statusElement.className = `thread-list-status status-${data.thread.status.toLowerCase()}`;
            }
            
            // Move updated thread to top of list if this is a new message
            if (data.newMessage) {
                const threadList = threadListItem.parentNode;
                threadList.insertBefore(threadListItem, threadList.firstChild);
                
                // Highlight the updated thread
                threadListItem.classList.add('thread-updated');
                setTimeout(() => {
                    threadListItem.classList.remove('thread-updated');
                }, 3000);
            }
        } else {
            // This is a new thread, add it to the list
            // In a real app, you might fetch the full thread details or have them in the event data
            const threadList = document.querySelector('.thread-list');
            if (threadList) {
                const newThreadItem = document.createElement('div');
                newThreadItem.className = 'thread-list-item thread-new';
                newThreadItem.dataset.id = data.thread.id;
                newThreadItem.innerHTML = `
                    <div class="thread-list-subject">${data.thread.subject}</div>
                    <div class="thread-list-preview">${data.thread.preview || 'New thread'}</div>
                    <div class="thread-list-status status-${(data.thread.status || 'new').toLowerCase()}">${data.thread.status || 'New'}</div>
                `;
                
                // Add click handler to navigate to the thread
                newThreadItem.addEventListener('click', () => {
                    window.location.href = `/threads/${data.thread.id}`;
                });
                
                // Add to the top of the list
                threadList.insertBefore(newThreadItem, threadList.firstChild);
                
                // Remove 'new' highlight after a few seconds
                setTimeout(() => {
                    newThreadItem.classList.remove('thread-new');
                }, 5000);
            }
        }
    }

    /**
     * Clean up WebSocket connections on page unload
     */
    cleanup() {
        if (this.initialized) {
            WebSocketService.disconnect();
            this.initialized = false;
            console.log('WebSocket connections cleaned up');
        }
    }
}

// Usage example:
// const webSocketHandler = new WebSocketExample();
// 
// // Call this when the page loads (with user context)
// webSocketHandler.init({ 
//     tenantId: '123', 
//     userId: '456',
//     authToken: 'jwt-token-here'
// });
// 
// // Call this when viewing a specific thread
// webSocketHandler.setActiveThread('789');
// 
// // Call this when leaving the page
// window.addEventListener('beforeunload', () => {
//     webSocketHandler.cleanup();
// });

export default WebSocketExample; 