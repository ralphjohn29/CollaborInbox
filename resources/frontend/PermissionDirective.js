/**
 * PermissionDirective
 * 
 * A utility that provides permission-based element visibility.
 * Allows for declarative permission checks in HTML using data attributes.
 */
import roleManager from './RoleManager.js';

class PermissionDirective {
    /**
     * Initialize the permission directive
     */
    static init() {
        // Initialize role manager
        roleManager.init().then(() => {
            // Process elements with permission requirements
            PermissionDirective.processElements();
            
            // Set up a mutation observer to handle dynamically added elements
            PermissionDirective.setupMutationObserver();
        }).catch(error => {
            console.error('Failed to initialize PermissionDirective:', error);
        });
    }
    
    /**
     * Process elements with permission attributes
     * 
     * @param {Element} rootElement The root element to process, defaults to document.body
     */
    static processElements(rootElement = document.body) {
        // Process requires-permission elements
        const permissionElements = rootElement.querySelectorAll('[data-requires-permission]');
        permissionElements.forEach(element => {
            const permission = element.getAttribute('data-requires-permission');
            if (!permission) return;
            
            roleManager.hasPermission(permission).then(hasPermission => {
                if (!hasPermission) {
                    // Handle lack of permission based on the action attribute
                    const action = element.getAttribute('data-permission-action') || 'hide';
                    PermissionDirective.applyAction(element, action);
                }
            });
        });
        
        // Process requires-role elements
        const roleElements = rootElement.querySelectorAll('[data-requires-role]');
        roleElements.forEach(element => {
            const role = element.getAttribute('data-requires-role');
            if (!role) return;
            
            roleManager.hasRole(role).then(hasRole => {
                if (!hasRole) {
                    // Handle lack of role based on the action attribute
                    const action = element.getAttribute('data-role-action') || 'hide';
                    PermissionDirective.applyAction(element, action);
                }
            });
        });
    }
    
    /**
     * Apply the specified action to an element
     * 
     * @param {Element} element The element to apply the action to
     * @param {string} action The action to apply ('hide', 'remove', 'disable')
     */
    static applyAction(element, action) {
        switch (action.toLowerCase()) {
            case 'hide':
                element.style.display = 'none';
                break;
            case 'remove':
                element.parentNode.removeChild(element);
                break;
            case 'disable':
                element.disabled = true;
                if (element.tagName === 'A') {
                    element.style.pointerEvents = 'none';
                    element.style.opacity = '0.5';
                    // Prevent default to disable link
                    element.addEventListener('click', (e) => e.preventDefault());
                }
                // Add disabled class
                element.classList.add('disabled');
                break;
            default:
                console.warn('Unknown permission action:', action);
                element.style.display = 'none';
        }
    }
    
    /**
     * Set up a mutation observer to watch for new elements
     */
    static setupMutationObserver() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(node => {
                        // Check if the node is an element
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // Process the new element
                            PermissionDirective.processElements(node);
                            
                            // Also process any permission elements inside it
                            if (node.querySelectorAll) {
                                PermissionDirective.processElements(node);
                            }
                        }
                    });
                }
            });
        });
        
        // Start observing the body for changes
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
}

// Initialize when the document is ready
document.addEventListener('DOMContentLoaded', () => {
    PermissionDirective.init();
});

export default PermissionDirective; 