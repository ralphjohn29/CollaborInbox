/**
 * Frontend Components for CollaborInbox
 * 
 * Main entry point for frontend JavaScript components.
 * Re-exports all available components and services.
 */

// Role & Permission Management
export { default as RoleManagement } from './RoleManagement.js';
export { default as RoleManager } from './RoleManager.js';
export { default as RoleSelector } from './RoleSelector.js';
export { default as PermissionManager } from './PermissionManager.js';
export { default as PermissionDirective } from './PermissionDirective.js';
export { default as TeamManagementPanel } from './TeamManagementPanel.js';
export { default as UserProfileManager } from './UserProfileManager.js';

// WebSocket Components
export { default as WebSocketService } from './WebSocketService.js';
export { default as NotificationManager } from './NotificationManager.js';
export { default as AgentNotificationSystem } from './AgentNotificationSystem.js';
export { default as NotificationHistoryComponent } from './NotificationHistoryComponent.js';
export { default as WebSocketExample } from './WebSocketExample.js';

// Authentication Components
export { default as AuthService } from './AuthService.js';
export { default as LoginPage } from './pages/LoginPage.js';

// Initialize global styles
import './role-management.css';
import './websocket.css';
import './auth.css'; 