# CollaborInbox Frontend WebSocket Components

This directory contains frontend components for implementing real-time updates using WebSockets in the CollaborInbox application.

## Components

1. **WebSocketService**: Handles WebSocket connections and event listeners for real-time updates
2. **NotificationManager**: Manages UI notifications triggered by WebSocket events
3. **AgentNotificationSystem**: Provides agent-specific notification features including preferences, history, and browser notifications
4. **NotificationHistoryComponent**: UI component to display notification history with filtering and preference settings
5. **WebSocketExample**: Example implementation showing how to use the WebSocket service

## Installation

These components require the following dependencies:

```bash
npm install laravel-echo socket.io-client
```

## Usage

### Basic Setup

Import and initialize the WebSocketService in your main application file:

```javascript
import { WebSocketService, NotificationManager, AgentNotificationSystem } from './frontend';

// Initialize notification manager
NotificationManager.init();

// Initialize WebSocket service with tenant and user context
WebSocketService.init({
  tenantId: '123', // The current tenant ID
  userId: '456',   // The current user ID
  authToken: 'your-auth-token' // JWT token for authentication
})
.then(() => {
  console.log('WebSocket connection established');
})
.catch(error => {
  console.error('WebSocket connection failed:', error);
});
```

### Subscribing to Tenant-wide Updates

```javascript
WebSocketService.subscribeToTenantThreads((data) => {
  console.log('Tenant thread update received:', data);
  // Update your UI based on the thread update
});
```

### Subscribing to User Notifications

```javascript
WebSocketService.subscribeToUserNotifications((data) => {
  console.log('User notification received:', data);
  
  // For basic notifications
  NotificationManager.show({
    title: data.title || 'Notification',
    message: data.message,
    type: data.type || 'info',
    threadId: data.threadId
  });
  
  // Or use the enhanced agent notification system for more features
  AgentNotificationSystem.handleEvent({
    type: data.type,
    data: data
  });
});
```

### Subscribing to Thread-specific Updates

When viewing a specific thread:

```javascript
// When opening a thread view
WebSocketService.subscribeToThread('123', (eventType, data) => {
  console.log(`Thread event: ${eventType}`, data);
  
  switch (eventType) {
    case 'update':
      // Handle thread updates
      break;
      
    case 'message':
      // Handle new messages
      break;
      
    case 'assignment':
      // Handle assignment changes
      break;
  }
});

// When leaving the thread view
WebSocketService.unsubscribeFromThread('123');
```

### Showing Notifications

```javascript
NotificationManager.show({
  title: 'New Message',
  message: 'You have received a new message',
  type: 'info', // 'info', 'success', 'warning', 'error'
  threadId: '123', // Optional - clicking will navigate to this thread
  duration: 5000 // Optional - auto-close after 5 seconds
});
```

### Cleanup

Make sure to disconnect when the user leaves the application:

```javascript
// On page unload
window.addEventListener('beforeunload', () => {
  WebSocketService.disconnect();
});
```

## Channel Structure

The WebSocket implementation uses the following channel structure:

1. `private-tenant.{tenantId}.threads` - Broadcasts updates about all threads for a tenant
2. `private-tenant.{tenantId}.thread.{threadId}` - Broadcasts updates about a specific thread
3. `private-tenant.{tenantId}.user.{userId}` - Broadcasts notifications for a specific user

## Event Types

The following event types are supported:

1. `ThreadUpdatedEvent` - When a thread's details are updated
2. `NewMessageEvent` - When a new message is added to a thread
3. `ThreadAssignedEvent` - When a thread is assigned to an agent
4. `UserNotificationEvent` - When a user receives a notification

## CSS Customization

The components come with default styling in `websocket.css`. You can override these styles in your application's CSS to match your design system.

## Agent Notification System

The `AgentNotificationSystem` extends the basic `NotificationManager` with additional features:

### Initialization

```javascript
import { AgentNotificationSystem } from './frontend';

// Initialize with options
AgentNotificationSystem.init({
  position: 'top-right',
  maxVisible: 5,
  duration: 8000,
  soundUrl: '/sounds/notification.mp3',
  preferences: {
    email: true,
    inApp: true,
    sound: true,
    browser: true
  }
});
```

### Handling Different Event Types

```javascript
// Handle various notification types
AgentNotificationSystem.handleEvent({
  type: 'assignment', // 'assignment', 'message', 'update', 'mention', etc.
  data: {
    thread: {
      id: '123',
      subject: 'Customer inquiry'
    },
    assignee: {
      name: 'Jane Doe'
    },
    isYou: true // Whether current user is the assignee
  }
});

// Or use directly
AgentNotificationSystem.notify({
  title: 'Thread Assigned',
  message: 'A thread has been assigned to you',
  type: 'success', // 'info', 'success', 'warning', 'error'
  data: { threadId: '123' },
  important: true // Plays sound and shows browser notification
});
```

### Notification History and Preferences

```javascript
// Get notification history
const notifications = AgentNotificationSystem.getHistory({
  unreadOnly: true,
  type: 'warning'
});

// Mark as read
AgentNotificationSystem.markAsRead('notification-123');
AgentNotificationSystem.markAllAsRead();

// Update preferences
AgentNotificationSystem.setPreferences({
  sound: false,
  browser: true
});
```

## Notification History UI

The `NotificationHistoryComponent` provides a UI for viewing notification history and managing preferences:

```javascript
import { NotificationHistoryComponent } from './frontend';

// Create and initialize the component
const notificationHistory = new NotificationHistoryComponent();

// Initialize with a container element and options
notificationHistory.init(document.getElementById('notification-container'), {
  createTrigger: true,
  triggerOptions: {
    className: 'custom-notification-button',
    icon: '<i class="fas fa-bell"></i>'
  }
});

// Or initialize with a CSS selector
notificationHistory.init('#notification-container');
```

The component creates:
- A notification bell icon with unread badge counter
- A dropdown panel showing notification history
- Filter options for viewing different notification types
- Preference toggles for notification settings

This component works with the `AgentNotificationSystem` and automatically updates when new notifications arrive.

## Example

For a complete implementation example, see `WebSocketExample.js` which demonstrates how to integrate these components into a typical application. 