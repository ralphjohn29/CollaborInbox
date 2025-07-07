/**
 * Debug utility functions for CollaborInbox
 * This file provides debugging tools that can be used during development
 */

const CollaborInboxDebug = {
    /**
     * Enable debug mode with verbose console logging
     */
    isDebugMode: false,
    
    /**
     * Initialize debug tools
     */
    init() {
        // Check if debug mode is enabled in localStorage
        this.isDebugMode = localStorage.getItem('collaborinbox_debug_mode') === 'true';
        
        if (this.isDebugMode) {
            console.log('CollaborInbox Debug Mode Enabled');
        }
        
        // Add debug controls to page if in development
        if (process.env.NODE_ENV !== 'production') {
            this.addDebugControls();
        }
    },
    
    /**
     * Log a debug message (only if debug mode is enabled)
     * @param {string} context - The context/component name
     * @param {string} message - The debug message
     * @param {any} data - Optional data to log
     */
    log(context, message, data = null) {
        if (!this.isDebugMode) return;
        
        if (data) {
            console.log(`[${context}] ${message}`, data);
        } else {
            console.log(`[${context}] ${message}`);
        }
    },
    
    /**
     * Toggle debug mode on/off
     */
    toggleDebugMode() {
        this.isDebugMode = !this.isDebugMode;
        localStorage.setItem('collaborinbox_debug_mode', this.isDebugMode);
        console.log(`Debug mode ${this.isDebugMode ? 'enabled' : 'disabled'}`);
        
        // Reload page to apply debug settings
        window.location.reload();
    },
    
    /**
     * Add debug controls to the page
     */
    addDebugControls() {
        // Only add controls if not already present
        if (document.getElementById('debug-controls')) return;
        
        const controlsDiv = document.createElement('div');
        controlsDiv.id = 'debug-controls';
        controlsDiv.style.position = 'fixed';
        controlsDiv.style.bottom = '10px';
        controlsDiv.style.right = '10px';
        controlsDiv.style.zIndex = '9999';
        controlsDiv.style.background = '#f1f1f1';
        controlsDiv.style.padding = '5px';
        controlsDiv.style.borderRadius = '5px';
        controlsDiv.style.boxShadow = '0 0 5px rgba(0,0,0,0.2)';
        
        const debugButton = document.createElement('button');
        debugButton.innerText = `Debug: ${this.isDebugMode ? 'ON' : 'OFF'}`;
        debugButton.onclick = () => this.toggleDebugMode();
        
        controlsDiv.appendChild(debugButton);
        document.body.appendChild(controlsDiv);
    }
};

// Initialize debug tools
document.addEventListener('DOMContentLoaded', () => {
    CollaborInboxDebug.init();
});

// Make debug tools available globally
window.CollaborInboxDebug = CollaborInboxDebug; 