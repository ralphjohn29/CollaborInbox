/**
 * Debug helper script for CollaborInbox authentication
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Debug script loaded');
    
    // Check auth status - safely parse JSON data
    const token = localStorage.getItem('auth_token');
    let user = null;
    let tenant = null;
    
    // Safely parse user data
    try {
        user = JSON.parse(localStorage.getItem('user') || 'null');
    } catch (e) {
        console.warn('Error parsing user data:', e);
    }
    
    // Safely parse tenant data
    try {
        tenant = JSON.parse(localStorage.getItem('tenant') || 'null');
    } catch (e) {
        console.warn('Error parsing tenant data:', e);
    }
    
    console.log('Auth status:', {
        hasToken: !!token,
        tokenValue: token ? token.substring(0, 10) + '...' : null,
        hasUser: !!user,
        userData: user,
        hasTenant: !!tenant,
        tenantData: tenant,
        currentPath: window.location.pathname,
        currentUrl: window.location.href,
        host: window.location.host
    });
    
    // Add debug controls
    const debugControls = document.createElement('div');
    debugControls.style.position = 'fixed';
    debugControls.style.top = '10px';
    debugControls.style.right = '10px';
    debugControls.style.zIndex = '9999';
    debugControls.style.background = 'rgba(0,0,0,0.7)';
    debugControls.style.color = 'white';
    debugControls.style.padding = '10px';
    debugControls.style.borderRadius = '5px';
    debugControls.style.fontFamily = 'monospace';
    
    debugControls.innerHTML = `
        <div>Debug Tools</div>
        <button id="debug-go-dashboard">Go to Dashboard</button>
        <button id="debug-clear-auth">Clear Auth Data</button>
    `;
    
    document.body.appendChild(debugControls);
    
    // Add event listeners
    document.getElementById('debug-go-dashboard').addEventListener('click', function() {
        window.location.href = '/dashboard';
    });
    
    document.getElementById('debug-clear-auth').addEventListener('click', function() {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        localStorage.removeItem('tenant');
        alert('Auth data cleared. Refreshing page...');
        window.location.reload();
    });
}); 